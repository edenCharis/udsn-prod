<?php
// traitement_bulk.php
include '../php/connexion.php';
include '../php/lib.php';
session_start();

if(!isset($_SESSION['id']) || $_SESSION['role'] != "anonymat") {
    header("location: ../login");
    exit;
}

$userIP = $_SERVER['REMOTE_ADDR'];

$ecue = $_POST['ecue'];
$classe = $_POST['classe'];
$semestre = $_POST['semestre'];
$examen = $_POST['examen'];
$annee = $_POST['annee'];
$nature = $_POST['nature'];
$etab = $_SESSION['etablissement'];

// Get selected students (if any were unchecked)
// Si pas de sélection manuelle, on récupère TOUS les étudiants éligibles depuis la BD
$selected_students = isset($_POST['selected_students']) ? $_POST['selected_students'] : [];

if(empty($selected_students)) {
    // Session de Rappel : jointures supplémentaires pour filtrer les éligibles
    $rappel_join_pre  = '';
    $rappel_where_pre = '';

    if ($examen === 'Session de Rappel') {
        $res_ue2 = $connexion->query(
            "SELECT code_ue FROM ecue WHERE code_ecue='$ecue' AND etab='$etab' LIMIT 1"
        );
        if ($res_ue2 && ($row_ue2 = $res_ue2->fetch_assoc()) && !empty($row_ue2['code_ue'])) {
            $code_ue2 = $connexion->real_escape_string($row_ue2['code_ue']);
            $rappel_join_pre = "
                INNER JOIN notation n ON n.inscription = i.id
                    AND n.code_ecue = '$ecue'
                    AND n.semestre  = '$semestre'
                    AND n.annee     = '$annee'
                    AND n.etab      = '$etab'
                INNER JOIN vue_moyenne_ue vu ON vu.inscription = i.id
                    AND vu.ue       = '$code_ue2'
                    AND vu.semestre = '$semestre'
                    AND vu.annee    = '$annee'
                    AND vu.etab     = '$etab'";
            $rappel_where_pre = "AND (n.moyGen < 6 OR vu.moyenneUE < 10)";
        }
    }

    $sql_all = "SELECT DISTINCT i.id
                FROM inscription i
                $rappel_join_pre
                WHERE i.etab  = '$etab'
                AND i.annee   = '$annee'
                AND i.classe  = '$classe'
                $rappel_where_pre";

    $res_all = $connexion->query($sql_all);

    if (!$res_all || $res_all->num_rows === 0) {
        header("location: index?erreur=" . urlencode("Aucun étudiant trouvé pour ces critères"));
        exit;
    }

    while ($row_all = $res_all->fetch_assoc()) {
        $selected_students[] = $row_all['id'];
    }
}
// Nombre total d'étudiants éligibles (tous pour Session Ordinaire, rattrapage pour Session de Rappel)
$count_students_sql = "SELECT COUNT(DISTINCT i.id) AS total_students
                       FROM inscription i
                       $rappel_join_pre
                       WHERE i.etab   = '$etab'
                       AND i.annee    = '$annee'
                       AND i.classe   = '$classe'
                       $rappel_where";
$count_result = $connexion->query($count_students_sql);
$count_row = $count_result->fetch_assoc();
$total_students_in_class = $count_row['total_students'];

// Check how many students ALREADY HAVE codes for these criteria
$check_sql = "SELECT COUNT(*) as count, GROUP_CONCAT(etudiant) as existing_students
              FROM anonymat
              WHERE code_ecue='$ecue'
              AND classe='$classe'
              AND semestre='$semestre'
              AND type='$examen'
              AND nature='$nature'
              AND annee='$annee'
              AND etab='$etab'";
$check_result = $connexion->query($check_sql);
$row = $check_result->fetch_assoc();
$existing_count = $row['count'];
$existing_student_ids = $row['existing_students'] ? explode(',', $row['existing_students']) : [];

// If ALL eligible students already have codes, block the operation
if($existing_count >= $total_students_in_class && $total_students_in_class > 0) {
    $label = ($examen === 'Session de Rappel') ? 'étudiants en rattrapage' : 'étudiants';
    header("location: index?erreur=Tous les $label ($total_students_in_class) ont déjà des codes anonymes pour ces critères");
    exit;
}

// Filter out students who ALREADY have codes (to avoid duplicates)
$students_to_process = [];
$already_have_codes = [];

foreach($selected_students as $student_id) {
    if(in_array($student_id, $existing_student_ids)) {
        $already_have_codes[] = $student_id;
    } else {
        $students_to_process[] = $student_id;
    }
}

// If no NEW students to process
if(empty($students_to_process)) {
    $count_skipped = count($already_have_codes);
    header("location: index?erreur=Tous les étudiants sélectionnés ($count_skipped) ont déjà des codes pour ces critères");
    exit;
}

// Get ECUE details for specialite and libelle
$sql_ecue = "SELECT specialite, e.libelle FROM ecue e JOIN ue u ON e.code_ue = u.code
             WHERE e.code_ecue = '$ecue' AND e.etab = '$etab'";
$result_ecue = $connexion->query($sql_ecue);
$ecue_data = $result_ecue->fetch_assoc();
$specialite   = $ecue_data['specialite'];
$ecue_libelle = $ecue_data['libelle'] ?? $ecue;

// Get existing codes to generate new ones (avoid conflicts)
$sql_codes = "SELECT numero FROM anonymat
              WHERE code_ecue = '$ecue'
              AND classe = '$classe'
              AND semestre = '$semestre'
              AND type = '$examen'
              AND annee = '$annee'
              AND nature = '$nature'
              AND etab = '$etab'";
$resultat_codes = $connexion->query($sql_codes);
$existing_codes = [];
while($code_row = $resultat_codes->fetch_assoc()) {
    $existing_codes[] = $code_row['numero'];
}

// Generate available codes (excluding existing ones)
$available_codes = generateAnonymousCodes($existing_codes);

if(count($available_codes) < count($students_to_process)) {
    header("location: index?erreur=Pas assez de codes disponibles. Maximum 260 étudiants par ECUE/Examen. Codes restants: " . count($available_codes));
    exit;
}

// Insert anonymized records ONLY for students who DON'T have codes yet
$success_count = 0;
$error_count = 0;
$code_index = 0;
$user = $_SESSION["id_user"];

foreach($students_to_process as $student_id) {
    $anonymous_code = $available_codes[$code_index];
    
    $sql = "INSERT INTO anonymat (etudiant, ecue, code_ecue, classe, semestre, specialite, numero, type, annee, nature, etab, user)
            VALUES ('$student_id', '$ecue_libelle', '$ecue', '$classe', '$semestre', '$specialite', '$anonymous_code', '$examen', '$annee', '$nature', '$etab', $user)";
    
    if($connexion->query($sql)) {
        $success_count++;
    } else {
        $error_count++;
    }
    
    $code_index++;
}

// Log the action
logUserAction(
    $connexion,
    $_SESSION['id_user'],
    "Anonymisation en masse de $success_count copies",
    date("Y-m-d H:i:s"),
    $userIP,
    "ECUE: $ecue, Classe: $classe, Semestre: $semestre, Examen: $examen, Année: $annee, Nature: $nature"
);

// Prepare success/info message
$message = "$success_count étudiants anonymisés avec succès";
if(count($already_have_codes) > 0) {
    $message .= ". " . count($already_have_codes) . " étudiant(s) ignoré(s) (codes existants)";
}
if($error_count > 0) {
    $message .= ". $error_count erreur(s)";
}

header("location: index?sucess=" . urlencode($message));

/**
 * Generate anonymous codes in format: Letter(A-Z) + Number(0-9)
 */
function generateAnonymousCodes($existing_codes) {
    $codes = [];
    
    for($letter = ord('A'); $letter <= ord('Z'); $letter++) {
        for($number = 0; $number <= 9; $number++) {
            $code = chr($letter) . $number;
            if(!in_array($code, $existing_codes)) {
                $codes[] = $code;
            }
        }
    }
    
    return $codes;
}
?>
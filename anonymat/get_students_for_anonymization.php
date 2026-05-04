<?php
// get_students_for_anonymization.php
include '../php/connexion.php';
include '../php/lib.php';
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['id']) || $_SESSION['role'] != 'anonymat') {
    echo json_encode(['success' => false, 'message' => 'Non autorisé']);
    exit;
}

$ecue     = $connexion->real_escape_string($_POST['ecue']);
$classe   = $connexion->real_escape_string($_POST['classe']);
$semestre = $connexion->real_escape_string($_POST['semestre']);
$examen   = $connexion->real_escape_string($_POST['examen']);
$annee    = $connexion->real_escape_string($_POST['annee']);
$nature   = $connexion->real_escape_string($_POST['nature']);
$etab     = $connexion->real_escape_string($_SESSION['etablissement']);

// --- Pour Session de Rappel : récupérer le code UE de l'ECUE ---
$code_ue     = '';
$rappel_join  = '';
$rappel_where = '';

if ($examen === 'Session de Rappel') {
    $res_ue = $connexion->query(
        "SELECT code_ue FROM ecue WHERE code_ecue='$ecue' AND etab='$etab' LIMIT 1"
    );
    if ($res_ue && $row_ue = $res_ue->fetch_assoc()) {
        $code_ue = $connexion->real_escape_string($row_ue['code_ue']);
    }

    if ($code_ue !== '') {
        $rappel_join = "
            INNER JOIN notation n ON n.inscription = i.id
                AND n.code_ecue = '$ecue'
                AND n.semestre  = '$semestre'
                AND n.annee     = '$annee'
                AND n.etab      = '$etab'
            INNER JOIN vue_moyenne_ue vu ON vu.inscription = i.id
                AND vu.ue       = '$code_ue'
                AND vu.semestre = '$semestre'
                AND vu.annee    = '$annee'
                AND vu.etab     = '$etab'";
        $rappel_where = "AND n.moyGen IS NOT NULL AND n.moyGen < 6 AND vu.moyenneUE IS NOT NULL AND vu.moyenneUE < 10";
    }
}

// --- Nombre d'étudiants éligibles (rattrapage ou tous) ---
$count_sql = "SELECT COUNT(DISTINCT i.id) AS total
              FROM inscription i
              $rappel_join
              WHERE i.etab  = '$etab'
              AND i.annee   = '$annee'
              AND i.classe  = '$classe'
              $rappel_where";
$count_result = $connexion->query($count_sql);
$total_eligible = ($count_result && $row_c = $count_result->fetch_assoc()) ? (int)$row_c['total'] : 0;

// --- Nombre de ceux qui ont déjà des codes pour cet examen ---
$check_sql = "SELECT COUNT(*) AS total_with_codes
              FROM anonymat
              WHERE ecue     = '$ecue'
              AND classe     = '$classe'
              AND semestre   = '$semestre'
              AND type       = '$examen'
              AND annee      = '$annee'
              AND nature     = '$nature'
              AND etab       = '$etab'";
$check_result   = $connexion->query($check_sql);
$total_with_codes = ($check_result && $row_cc = $check_result->fetch_assoc()) ? (int)$row_cc['total_with_codes'] : 0;

if ($total_with_codes >= $total_eligible && $total_eligible > 0) {
    $label = ($examen === 'Session de Rappel') ? 'étudiants en rattrapage' : 'étudiants de la classe';
    echo json_encode([
        'success' => false,
        'message' => "Les codes anonymes existent déjà pour tous les $label ($total_eligible). Veuillez vérifier ou supprimer l'anonymisation existante."
    ]);
    exit;
}

// --- Récupérer les étudiants éligibles sans code encore ---
$sql = "SELECT DISTINCT i.id, i.candidat, i.annee
        FROM inscription i
        $rappel_join
        WHERE i.etab  = '$etab'
        AND i.annee   = '$annee'
        AND i.classe  = '$classe'
        AND EXISTS (SELECT 1 FROM ecue e WHERE e.code_ecue = '$ecue' AND e.etab = '$etab')
        $rappel_where
        AND i.id NOT IN (
            SELECT etudiant FROM anonymat
            WHERE ecue     = '$ecue'
            AND classe     = '$classe'
            AND semestre   = '$semestre'
            AND type       = '$examen'
            AND annee      = '$annee'
            AND etab       = '$etab'
            AND nature     = '$nature'
        )
        ORDER BY i.candidat";

$resultat = $connexion->query($sql);
if (!$resultat) {
    echo json_encode(['success' => false, 'message' => $connexion->error]);
    exit;
}

// --- Codes existants pour éviter les conflits ---
$sql_codes = "SELECT numero FROM anonymat
              WHERE ecue     = '$ecue'
              AND classe     = '$classe'
              AND semestre   = '$semestre'
              AND type       = '$examen'
              AND annee      = '$annee'
              AND nature     = '$nature'
              AND etab       = '$etab'";
$resultat_codes = $connexion->query($sql_codes);
$existing_codes = [];
while ($row = $resultat_codes->fetch_assoc()) {
    $existing_codes[] = $row['numero'];
}

$available_codes = generateAnonymousCodes($existing_codes);

$students = [];
$code_index = 0;
$students_without_codes = 0;

while ($student = $resultat->fetch_assoc()) {
    $students_without_codes++;
    $nom_prenom = obtenirNomPrenom($student['candidat'], $student['annee'], $connexion);

    $anonymous_code = isset($available_codes[$code_index]) ? $available_codes[$code_index] : 'MAX';

    $students[] = [
        'id'          => $student['id'],
        'matricule'   => $student['candidat'],
        'nom_prenom'  => str_replace('+', "'", $nom_prenom),
        'code_anonyme' => $anonymous_code,
    ];
    $code_index++;
}

$message = '';
if ($total_with_codes > 0) {
    $message = "$total_with_codes étudiants ont déjà des codes. ";
}
if ($examen === 'Session de Rappel') {
    $message .= "$students_without_codes étudiant(s) en rattrapage restant(s) à anonymiser sur $total_eligible éligibles.";
} else {
    $message .= "$students_without_codes étudiants restants à anonymiser sur $total_eligible au total.";
}

echo json_encode([
    'success'  => true,
    'students' => $students,
    'total'    => count($students),
    'info'     => [
        'total_eligible'   => $total_eligible,
        'already_have_codes' => $total_with_codes,
        'remaining'        => $students_without_codes,
        'is_rappel'        => ($examen === 'Session de Rappel'),
    ],
    'message'  => $message,
]);

function generateAnonymousCodes($existing_codes) {
    $codes = [];
    for ($letter = ord('A'); $letter <= ord('Z'); $letter++) {
        for ($number = 0; $number <= 9; $number++) {
            $code = chr($letter) . $number;
            if (!in_array($code, $existing_codes)) {
                $codes[] = $code;
            }
        }
    }
    return $codes;
}

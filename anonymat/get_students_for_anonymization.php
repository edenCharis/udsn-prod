<?php
// get_students_for_anonymization.php
include '../php/connexion.php';
include '../php/lib.php';
session_start();
header('Content-Type: application/json');

if(!isset($_SESSION['id']) || $_SESSION['role'] != "anonymat") {
    echo json_encode(['success' => false, 'message' => 'Non autorisé']);
    exit;
}

$ecue = $_POST['ecue'];
$classe = $_POST['classe'];
$semestre = $_POST['semestre'];
$examen = $_POST['examen'];
$annee = $_POST['annee'];
$etab = $_SESSION['etablissement'];
$nature = $_POST['nature'];

// First, get the TOTAL number of students in this class for this year
$count_students_sql = "SELECT COUNT(DISTINCT i.id) AS total_students
                       FROM inscription i
                       WHERE i.etab = '$etab' 
                       AND i.annee = '$annee'
                       AND i.classe = '$classe'";
$count_result = $connexion->query($count_students_sql);
$count_row = $count_result->fetch_assoc();
$total_students_in_class = $count_row['total_students'];

// Then, check how many ALREADY have anonymous codes for this specific exam
$check_sql = "SELECT COUNT(*) AS total_with_codes
              FROM anonymat
              WHERE ecue = '$ecue'
              AND classe = '$classe'
              AND semestre = '$semestre'
              AND type = '$examen'
              AND annee = '$annee'
              AND nature = '$nature'
              AND etab = '$etab'";
$check_result = $connexion->query($check_sql);
$check_row = $check_result->fetch_assoc();
$total_with_codes = $check_row['total_with_codes'];

// Compare: if all students already have codes, stop here
if ($total_with_codes >= $total_students_in_class && $total_students_in_class > 0) {
    echo json_encode([
        'success' => false,
        'message' => "Les codes anonymes existent déjà pour tous les étudiants de cette classe ($total_students_in_class étudiants). Veuillez vérifier ou supprimer l'anonymisation existante."
    ]);
    exit;
}

// Get students who DON'T have codes yet
$sql = "SELECT DISTINCT i.id, i.candidat, i.annee 
        FROM inscription i
        WHERE i.etab = '$etab' 
        AND i.annee = '$annee'
        AND i.classe = '$classe'
        AND EXISTS (
            SELECT 1 FROM ecue e 
            WHERE e.code_ecue = '$ecue' 
            AND e.etab = '$etab'
        )
        AND i.id NOT IN (
            SELECT etudiant FROM anonymat 
            WHERE ecue = '$ecue' 
            AND classe = '$classe' 
            AND semestre = '$semestre' 
            AND type = '$examen' 
            AND annee = '$annee'
            AND etab = '$etab'
            AND nature='$nature'
        )
        ORDER BY i.candidat";
$resultat = $connexion->query($sql);

if(!$resultat) {
    echo json_encode(['success' => false, 'message' => $connexion->error]);
    exit;
}

// Get existing codes for this exam to avoid conflicts
$sql_codes = "SELECT numero FROM anonymat 
              WHERE ecue = '$ecue' 
              AND classe = '$classe' 
              AND semestre ='$semestre' 
              AND type = '$examen' 
              AND annee = '$annee'
              AND nature='$nature'
              AND etab = '$etab'";
$resultat_codes = $connexion->query($sql_codes);
$existing_codes = [];
while($row = $resultat_codes->fetch_assoc()) {
    $existing_codes[] = $row['numero'];
}

// Generate available anonymous codes (A0-Z9 format)
$available_codes = generateAnonymousCodes($existing_codes);

$students = [];
$code_index = 0;
$students_without_codes = 0;

while($student = $resultat->fetch_assoc()) {
    $students_without_codes++;
    $nom_prenom = obtenirNomPrenom($student['candidat'], $student['annee'], $connexion);
    
    // Get next available code
    $anonymous_code = isset($available_codes[$code_index]) ? $available_codes[$code_index] : 'MAX';
    
    $students[] = [
        'id' => $student['id'],
        'matricule' => $student['candidat'],
        'nom_prenom' => str_replace("+", "'", $nom_prenom),
        'code_anonyme' => $anonymous_code
    ];
    
    $code_index++;
}

// Prepare informative message
$message = '';
if ($total_with_codes > 0) {
    $message = "$total_with_codes étudiants ont déjà des codes. ";
}
$message .= "$students_without_codes étudiants restants à anonymiser sur $total_students_in_class au total.";

echo json_encode([
    'success' => true, 
    'students' => $students,
    'total' => count($students),
    'info' => [
        'total_students' => $total_students_in_class,
        'already_have_codes' => $total_with_codes,
        'remaining' => $students_without_codes
    ],
    'message' => $message
]);

/**
 * Generate anonymous codes in format: Letter(A-Z) + Number(0-9)
 * Total possible: 260 codes (A0-Z9)
 */
function generateAnonymousCodes($existing_codes) {
    $codes = [];
    
    // Generate A0-Z9 format (260 combinations)
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
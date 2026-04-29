<?php
include '../php/connexion.php';
include '../php/lib.php';
session_start();

// Verify teacher session
if($_SESSION['id'] != session_id() || $_SESSION['role'] != "enseignant"){
    echo json_encode(['error' => 'Session invalide']);
    exit();
}

if(!isset($_POST['classe']) || !isset($_POST['ecue']) || !isset($_POST['semestre']) || !isset($_POST['annee']) || !isset($_POST['type_examen'])){
    echo json_encode(['error' => 'Paramètres manquants']);
    exit();
}

$classe = $_POST['classe'];
$ecue = $_POST['ecue'];
$semestre = $_POST['semestre'];
$annee = $_POST['annee'];
$type_examen = $_POST['type_examen'];

// Verify teacher has access to this class and ECUE
$verify_sql = "SELECT COUNT(*) as count FROM repartition_enseignant 
               WHERE code='".$_SESSION["code_enseignant"]."' 
               AND classe='$classe' 
               AND code_ecue='$ecue'";
$verify_result = $connexion->query($verify_sql);
$verify_row = $verify_result->fetch_assoc();

if($verify_row['count'] == 0){
    echo json_encode(['error' => 'Vous n\'avez pas accès à cette classe/ECUE']);
    exit();
}

// Get all students with anonymat codes for this class and semester
$sql = "SELECT DISTINCT a.numero as anonymat, a.classe 
        FROM anonymat a 
        WHERE a.classe = '$classe' 
        AND a.semestre = '$semestre'
        ORDER BY a.numero";

$result = $connexion->query($sql);

if(!$result){
    echo json_encode(['error' => 'Erreur de requête: ' . $connexion->error]);
    exit();
}

$students = [];
while($row = $result->fetch_assoc()){
    $anonymat = $row['anonymat'];
    
    // Check if grade already exists
    $grade_sql = "SELECT note FROM ligne1 
                  WHERE anonymat='$anonymat' 
                  AND code_ecue='$ecue' 
                  AND type_examen='$type_examen' 
                  AND annee='$annee'
                  AND user_id=".$_SESSION['id_user'];
    
    $grade_result = $connexion->query($grade_sql);
    $existing_note = null;
    
    if($grade_result && $grade_result->num_rows > 0){
        $grade_row = $grade_result->fetch_assoc();
        $existing_note = $grade_row['note'];
    }
    
    $students[] = [
        'anonymat' => $anonymat,
        'classe' => $row['classe'],
        'existing_note' => $existing_note
    ];
}

echo json_encode([
    'students' => $students,
    'count' => count($students)
]);
?>
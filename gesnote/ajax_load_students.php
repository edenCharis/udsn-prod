<?php
include '../php/connexion.php';
include '../php/lib.php';
session_start();

// Verify teacher session
if($_SESSION['id'] != session_id() || $_SESSION['role'] != "gesnote"){
    echo json_encode(['error' => 'Session invalide']);
    exit();
}

if(!isset($_POST['classe']) || !isset($_POST['ecue']) || !isset($_POST['semestre']) || !isset($_POST['nature']) || !isset($_POST['annee']) || !isset($_POST['type_examen'])){
    
    
    echo json_encode(['error' => 'Paramètres manquants']);
    exit();
}

$classe = $_POST['classe'];
$ecue = $_POST['ecue'];
$semestre = $_POST['semestre'];
$annee = $_POST['annee'];
$type_examen = $_POST['type_examen'];
$nature = $_POST['nature'];



// Get all students with anonymat codes for this class and semester
$sql = "SELECT DISTINCT a.numero as anonymat, a.classe 
        FROM anonymat a 
        WHERE a.classe = '$classe' 
        AND a.semestre = '$semestre'
        AND a.annee = '$annee'
        AND a.ecue = '$ecue'
        AND a.nature='$nature'
        AND a.type='$type_examen'
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
                  AND nature='$nature'
                  AND semestre='$semestre'";
    
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
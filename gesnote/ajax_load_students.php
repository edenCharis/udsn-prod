<?php
include '../php/connexion.php';
include '../php/lib.php';
session_start();

if($_SESSION['id'] != session_id() || $_SESSION['role'] != "gesnote"){
    echo json_encode(['error' => 'Session invalide']);
    exit();
}

if(!isset($_POST['classe']) || !isset($_POST['ecue']) || !isset($_POST['semestre']) || !isset($_POST['nature']) || !isset($_POST['annee']) || !isset($_POST['type_examen'])){
    echo json_encode(['error' => 'Paramètres manquants']);
    exit();
}

$classe      = $_POST['classe'];
$ecue        = $_POST['ecue'];
$semestre    =$_POST['semestre'];
$annee       =$_POST['annee'];
$type_examen = $_POST['type_examen'];
$nature      = $_POST['nature'];
$etab        = $_SESSION['etablissement'];

// Single query with LEFT JOIN to get existing notes — eliminates N+1
$sql = "SELECT a.numero AS anonymat, a.classe, l.note AS existing_note
        FROM anonymat a
        LEFT JOIN ligne1 l ON l.anonymat   = a.numero
                          AND l.code_ecue   = a.code_ecue
                          AND l.type_examen = a.type
                          AND l.nature      = a.nature
                          AND l.semestre    = a.semestre
                          AND l.annee       = a.annee
                          AND l.etab        = a.etab
        WHERE a.classe    = '$classe'
          AND a.semestre  = '$semestre'
          AND a.annee     = '$annee'
          AND a.code_ecue = '$ecue'
          AND a.nature    = '$nature'
          AND a.type      = '$type_examen'
          AND a.etab      = '$etab'
        ORDER BY a.numero";

$result = $connexion->query($sql);

if(!$result){
    echo json_encode(['error' => 'Erreur de requête: ' . $connexion->error]);
    exit();
}

$students = [];
while($row = $result->fetch_assoc()){
    $students[] = [
        'anonymat'      => $row['anonymat'],
        'classe'        => $row['classe'],
        'existing_note' => $row['existing_note'],
    ];
}

echo json_encode(['students' => $students, 'count' => count($students)]);
?>

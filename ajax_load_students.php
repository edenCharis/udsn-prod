<?php
include '../php/connexion.php';
include '../php/lib.php';
session_start();

if($_SESSION['id'] != session_id() || $_SESSION['role'] != "enseignant"){
    echo json_encode(['error' => 'Session invalide']);
    exit();
}

if(!isset($_POST['classe']) || !isset($_POST['ecue']) || !isset($_POST['semestre']) || !isset($_POST['annee']) || !isset($_POST['type_examen'])){
    echo json_encode(['error' => 'Paramètres manquants']);
    exit();
}

$classe      = $connexion->real_escape_string($_POST['classe']);
$ecue        = $connexion->real_escape_string($_POST['ecue']);
$semestre    = $connexion->real_escape_string($_POST['semestre']);
$annee       = $connexion->real_escape_string($_POST['annee']);
$type_examen = $connexion->real_escape_string($_POST['type_examen']);
$nature      = $connexion->real_escape_string($_POST['nature'] ?? 'Examen Theorique');
$etab        = $connexion->real_escape_string($_SESSION['etablissement']);
$code_ens    = $connexion->real_escape_string($_SESSION['code_enseignant'] ?? '');

// Verify teacher has access to this ECUE
$verify = $connexion->query(
    "SELECT COUNT(*) AS c FROM repartition_enseignant
     WHERE code='$code_ens' AND classe='$classe' AND code_ecue='$ecue'"
);
if(!$verify || $verify->fetch_assoc()['c'] == 0){
    echo json_encode(['error' => "Vous n'avez pas accès à cette classe/ECUE"]);
    exit();
}

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

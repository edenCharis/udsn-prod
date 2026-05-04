<?php
include '../php/connexion.php';
include '../php/lib.php';
session_start();

header('Content-Type: application/json');

if (!isset($_SESSION['id']) || $_SESSION['role'] !== 'anonymat') {
    echo json_encode(['error' => 'unauthorized']);
    exit;
}

$ecue     = $_POST['ecue']     ?? '';
$classe   = $_POST['classe']   ?? '';
$semestre = $_POST['semestre'] ?? '';
$etab     = $_SESSION['etablissement'];

if (!$ecue || !$classe) {
    echo json_encode(['valid' => null]);
    exit;
}

$valid_classe   = verifierEcueClasse($ecue, $classe, $etab, $connexion);
$valid_semestre = true;
if ($semestre) {
    $valid_semestre = verifierEcueClasseSemestre($ecue, $classe, $semestre, $etab, $connexion);
}

echo json_encode([
    'valid'          => $valid_classe && $valid_semestre,
    'valid_classe'   => $valid_classe,
    'valid_semestre' => $valid_semestre,
]);

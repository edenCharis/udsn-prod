<?php
include '../php/connexion.php';
include '../php/lib.php';

session_start();

header('Content-Type: application/json');

if ($_SESSION['id'] != session_id() || $_SESSION['role'] != "gesnote") {
    echo json_encode(['success' => false, 'message' => 'Non autorisé']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Méthode invalide']);
    exit;
}

$id_note = intval($_POST['id_note']);
$moyDev  = $_POST['moyDev'] !== '' ? floatval($_POST['moyDev']) : null;
$moyEx   = $_POST['moyEx']  !== '' ? floatval($_POST['moyEx'])  : null;
$rappel  = $_POST['rappel'] !== '' ? floatval($_POST['rappel']) : null;

// Calcul moyenne générale
$moyGen = null;
if ($moyDev !== null && $moyEx !== null) {
    $moyGen = round(($moyDev + $moyEx) / 2, 2);
}

// Mise à jour
$sql = "UPDATE notation SET 
            moyDev = " . ($moyDev !== null ? $moyDev : "NULL") . ",
            moyEx  = " . ($moyEx  !== null ? $moyEx  : "NULL") . ",
            session_rappel = " . ($rappel !== null ? $rappel : "NULL") . ",
            moyGen = " . ($moyGen !== null ? $moyGen : "NULL") . "
        WHERE id = $id_note 
          AND etab = '" . $connexion->real_escape_string($_SESSION['etablissement']) . "'";

if ($connexion->query($sql)) {
    // Log de l'action
    logUserAction($connexion, $_SESSION['id_user'], "modification d'une note",
        date("Y-m-d H:i:s"), $_SERVER['REMOTE_ADDR'],
        "note id=$id_note modifiée : moyDev=$moyDev, moyEx=$moyEx, rappel=$rappel");

    echo json_encode([
        'success' => true,
        'message' => 'Note modifiée avec succès',
        'data'    => [
            'moyDev'         => $moyDev !== null ? round($moyDev, 2) : null,
            'moyEx'          => $moyEx  !== null ? round($moyEx,  2) : null,
            'session_rappel' => $rappel !== null ? round($rappel, 2) : null,
            'moyGen'         => $moyGen !== null ? round($moyGen, 2) : null,
        ]
    ]);
} else {
    echo json_encode([
        'success' => false,
        'message' => $connexion->error
    ]);
}
?>
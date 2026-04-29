<?php
/**
 * ajax_correction.php
 * Met à jour notation.moyEx et notation.session_rappel
 * avec les valeurs calculées depuis ligne1.
 *
 * POST params :
 *   action        = 'single' | 'bulk'
 *   --- single ---
 *   notation_id   = id de la ligne dans notation
 *   moy_ex        = nouvelle valeur calculée (ou '' si nulle)
 *   sess_rap      = nouvelle valeur rappel calculée (ou '' si nulle)
 *   --- bulk ---
 *   corrections   = JSON array [{notation_id, moy_ex, sess_rap}, ...]
 */

set_error_handler(function($errno, $errstr, $errfile, $errline) {
    ob_clean(); header('Content-Type: application/json; charset=utf-8');
    echo json_encode(['error' => "PHP [$errno]: $errstr dans $errfile:$errline"]); exit();
});
set_exception_handler(function($e) {
    ob_clean(); header('Content-Type: application/json; charset=utf-8');
    echo json_encode(['error' => "Exception: " . $e->getMessage()]); exit();
});

ob_start();
include '../php/connexion.php';
include '../php/lib.php';
session_start();

ob_clean();
header('Content-Type: application/json; charset=utf-8');

$rolesAutorises = ['gesnote', 'admin', 'direction'];
if (!isset($_SESSION['id']) || !($_SESSION['id'] == session_id() && in_array($_SESSION['role'], $rolesAutorises))) {
    echo json_encode(['error' => 'Non autorisé — seuls admin, gesnote et direction peuvent corriger.']); exit();
}
if (!isset($connexion) || !$connexion) {
    echo json_encode(['error' => 'Connexion BD échouée']); exit();
}

$etab   = $_SESSION['etablissement'] ?? '';
$action = trim($_POST['action'] ?? 'single');

// ── Fonction de correction d'une ligne ────────────────────────────────────────
function correctLine($connexion, $etab, $notation_id, $moy_ex_raw, $sess_rap_raw) {
    $notation_id = (int)$notation_id;
    if (!$notation_id) return ['error' => 'notation_id invalide'];

    $moy_ex  = ($moy_ex_raw  !== '' && $moy_ex_raw  !== null) ? (float)$moy_ex_raw  : null;
    $sess_rap= ($sess_rap_raw !== '' && $sess_rap_raw !== null) ? (float)$sess_rap_raw : null;

    // Vérifier que la ligne appartient bien à cet établissement
    $check = $connexion->prepare("SELECT id FROM notation WHERE id = ? AND etab = ?");
    $check->bind_param('is', $notation_id, $etab);
    $check->execute();
    if (!$check->get_result()->fetch_assoc()) {
        return ['error' => "notation_id $notation_id introuvable pour cet établissement"];
    }

    // Construire la mise à jour dynamiquement selon ce qui doit changer
    $sets   = [];
    $params = [];
    $types  = '';

    if ($moy_ex !== null) {
        $sets[]   = 'moyEx = ?';
        $params[] = $moy_ex;
        $types   .= 'd';
    } else {
        // ne pas écraser avec NULL si rien de calculé
    }
    if ($sess_rap !== null) {
        $sets[]   = 'session_rappel = ?';
        $params[] = $sess_rap;
        $types   .= 'd';
    }

    if (empty($sets)) return ['skipped' => true, 'notation_id' => $notation_id];

    $params[] = $notation_id;
    $types   .= 'i';

    $sql  = "UPDATE notation SET " . implode(', ', $sets) . " WHERE id = ?";
    $stmt = $connexion->prepare($sql);
    if (!$stmt) return ['error' => 'prepare() : ' . $connexion->error];

    $stmt->bind_param($types, ...$params);
    if (!$stmt->execute()) return ['error' => 'execute() : ' . $stmt->error];

    return ['success' => true, 'notation_id' => $notation_id, 'affected' => $stmt->affected_rows];
}

// ── Traitement ─────────────────────────────────────────────────────────────────
if ($action === 'single') {

    $notation_id  = $_POST['notation_id']  ?? '';
    $moy_ex_raw   = $_POST['moy_ex']       ?? '';
    $sess_rap_raw = $_POST['sess_rap']      ?? '';

    $result = correctLine($connexion, $etab, $notation_id, $moy_ex_raw, $sess_rap_raw);
    echo json_encode($result);

} elseif ($action === 'bulk') {

    $raw = $_POST['corrections'] ?? '[]';
    $corrections = json_decode($raw, true);

    if (!is_array($corrections)) {
        echo json_encode(['error' => 'Paramètre corrections invalide']); exit();
    }

    $results   = [];
    $corrected = 0;
    $errors    = 0;

    foreach ($corrections as $c) {
        $res = correctLine(
            $connexion,
            $etab,
            $c['notation_id'] ?? '',
            $c['moy_ex']      ?? '',
            $c['sess_rap']    ?? ''
        );
        $results[] = $res;
        if (!empty($res['success']))  $corrected++;
        if (!empty($res['error']))    $errors++;
    }

    echo json_encode([
        'success'   => true,
        'corrected' => $corrected,
        'errors'    => $errors,
        'details'   => $results
    ]);

} else {
    echo json_encode(['error' => 'Action inconnue : ' . $action]);
}
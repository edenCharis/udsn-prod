<?php
include '../php/connexion.php';
include '../php/lib.php';
session_start();

function jsonError($msg, $code = 400) {
    http_response_code($code);
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'error' => $msg]);
    exit();
}
function jsonSuccess($payload) {
    header('Content-Type: application/json');
    echo json_encode(array_merge(['success' => true], $payload));
    exit();
}

if ($_SESSION['id'] != session_id() || $_SESSION['role'] != 'gesnote')
    jsonError('Session invalide', 401);

$input = json_decode(file_get_contents('php://input'), true);
if (!$input) jsonError('Payload invalide');

$classe      = $input['classe']      ?? '';
$ecue        = $input['ecue']        ?? '';
$semestre    = $input['semestre']    ?? '';
$annee       = $input['annee']       ?? '';
$type_examen = $input['type_examen'] ?? '';
$nature      = $input['nature']      ?? '';
$etab        = $_SESSION['etablissement'];
$user_id     = (int)$_SESSION['id_user'];

if (!$classe || !$ecue || !$semestre || !$annee || !$type_examen)
    jsonError('Champs manquants');

// ── Récupérer tous les anonymats de cette session dans ligne1 ─────────────
$stmt = $connexion->prepare("
    SELECT DISTINCT a.etudiant
    FROM ligne1 l
    JOIN anonymat a ON a.numero    = l.anonymat
                   AND a.code_ecue = l.code_ecue
                   AND a.annee     = l.annee
                   AND a.semestre  = l.semestre
                   AND a.etab      = l.etab
                   AND a.nature    = l.nature
                   AND a.type      = l.type_examen
    WHERE l.code_ecue   = ?
      AND l.semestre    = ?
      AND l.annee       = ?
      AND l.type_examen = ?
      AND l.nature      = ?
      AND l.etab        = ?
");
$stmt->bind_param('ssssss', $ecue, $semestre, $annee, $type_examen, $nature, $etab);
$stmt->execute();
$rows = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();
$errors  = [];

if (empty($rows)) {
    jsonError("Aucun étudiant trouvé dans ligne1 pour ces paramètres");
 $errors[] =("Aucun étudiant trouvé dans ligne1 pour ces paramètres");
          
}

$updated = 0;




$connexion->begin_transaction();

try {
    // Déterminer le champ à mettre à jour
    $champ = ($type_examen === "Session Ordinaire") ? "moyEx" : "session_rappel";

    foreach ($rows as $row) {
        $etudiant_id = (int)$row['etudiant'];

        // ── 1. Calculer la moyenne ────────────────────────────────────────
        $avgStmt = $connexion->prepare("
            SELECT ROUND(
                CASE 
                    WHEN COUNT(*) = 2 THEN SUM(l.note) / 2
                    ELSE MAX(l.note)
                END
            , 2) AS moyenne
            FROM ligne1 l
            JOIN anonymat a ON a.numero    = l.anonymat
                           AND a.code_ecue = l.code_ecue
                           AND a.annee     = l.annee
                           and a.type = l.type_examen
                           AND a.semestre  = l.semestre
                           AND a.etab      = l.etab
            WHERE l.code_ecue   = ?
              AND l.semestre    = ?
              AND l.annee       = ?
              AND l.type_examen = ?
              AND l.etab        = ?
              AND a.etudiant    = ?
        ");
        $avgStmt->bind_param('sssssi', $ecue, $semestre, $annee, $type_examen, $etab, $etudiant_id);
        $avgStmt->execute();
        $avg = $avgStmt->get_result()->fetch_assoc()['moyenne'] ?? null;
        $avgStmt->close();

        if ($avg === null) {
            $errors[] = "Moyenne null pour étudiant $etudiant_id";
            continue;
        }

        // ── 2. Vérifier si une ligne existe dans notation ─────────────────
        $checkStmt = $connexion->prepare("
            SELECT id FROM notation
            WHERE inscription = ?
              AND code_ecue   = ?
              AND semestre    = ?
              AND annee       = ?
              and classe=?
              AND etab        = ?
            LIMIT 1
        ");
        $checkStmt->bind_param('isssss', $etudiant_id, $ecue, $semestre, $annee,$classe, $etab);
        $checkStmt->execute();
        $checkRow = $checkStmt->get_result()->fetch_assoc();
        $checkStmt->close();

        if ($checkRow) {
            // ── 3a. UPDATE ────────────────────────────────────────────────
            $updStmt = $connexion->prepare("UPDATE notation SET $champ = ? WHERE id = ?");
            $updStmt->bind_param('di', $avg, $checkRow['id']);
            $updStmt->execute();
            $updStmt->close();
            $notation_id = $checkRow['id'];

        } else {
            // ── 3b. INSERT ────────────────────────────────────────────────
            $ecueStmt = $connexion->prepare("SELECT libelle FROM ecue WHERE code_ecue = ? AND etab = ? LIMIT 1");
            $ecueStmt->bind_param('ss', $ecue, $etab);
            $ecueStmt->execute();
            $ecueLibelle = $ecueStmt->get_result()->fetch_assoc()['libelle'] ?? '';
            $ecueStmt->close();

            $moyEx_val      = ($type_examen === "Session Ordinaire") ? $avg : null;
            $session_rappel = ($type_examen === "Session Ordinaire") ? null : $avg;

            $insStmt = $connexion->prepare("
                INSERT INTO notation
                    (inscription, classe, ecue, code_ecue, annee, moyEx, session_rappel, semestre, etab, user_id)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
            ");
            $insStmt->bind_param(
                'issssddssi',
                $etudiant_id, $classe, $ecueLibelle, $ecue, $annee,
                $moyEx_val, $session_rappel, $semestre, $etab, $user_id
            );
            $insStmt->execute();
            $insStmt->close();
            $notation_id = (int)$connexion->insert_id;
        }

        // notation.moyDev and moyGen are kept in sync by DB triggers on ligne1/ligne2
        $updated++;
    }

    $connexion->commit();
    jsonSuccess([
        'message' => "$updated ligne(s) notation mises à jour",
        'errors'  => $errors
    ]);

} catch (Exception $e) {
    $connexion->rollback();
    jsonError('Erreur: ' . $e->getMessage(), 500);
}
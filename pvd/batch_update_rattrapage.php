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

if ($_SESSION['id'] != session_id() || $_SESSION['role'] != 'pvd')
    jsonError('Session invalide', 401);

$input = json_decode(file_get_contents('php://input'), true);
if (!$input) jsonError('Payload invalide');

$semestre    = $input['semestre']    ?? '';
$annee       = $input['annee']       ?? '';
$specialite  = $input['specialite']  ?? '';
$niveau      = $input['niveau']      ?? '';
$classe      = $input['classe']      ?? '';
$examen_mode = $input['examen']    ; // "Session Ordinaire" ou "Session de rappel"
$etab        = $_SESSION['etablissement'];
$user_id     = (int)$_SESSION['id_user'];

// type_examen pour ligne1 / anonymat → utiliser directement
$examen_mode = $input['examen']      ;       // "ordinaire" ou "rattrapage"
$type_examen = $input['type_examen'] ?? ''; 

$type_examen = ($type_examen === "Session Rattrapage") ? "Session de Rappel" : "Session Ordinaire";

// Pour recap
$examen_recap = ($examen_mode === 'rattrapage') ? 'rattrapage' : 'ordinaire';

// Champ notation
$champ_notation = ($examen_mode === 'rattrapage') ? 'session_rappel' : 'moyEx';

if (!$semestre || !$annee || !$specialite || !$niveau || !$classe)
    jsonError('Champs manquants (semestre, annee, specialite, niveau, classe)');

$errors  = [];
$updated = 0;

$connexion->begin_transaction();

try {

    // ── ÉTAPE 1 : Récupérer les étudiants de la classe ───────────────────────
    $stmtEtu = $connexion->prepare("
        SELECT i.id AS etudiant_id
        FROM inscription i
        WHERE i.classe = ?
          AND i.annee  = ?
        GROUP BY i.id
    ");
    $stmtEtu->bind_param('ss', $classe, $annee);
    $stmtEtu->execute();
    $etudiants = $stmtEtu->get_result()->fetch_all(MYSQLI_ASSOC);
    $stmtEtu->close();

    if (empty($etudiants))
        jsonError("Aucun étudiant trouvé pour classe=$classe / annee=$annee");

    // ── ÉTAPE 2 : Filtrage selon le mode examen ──────────────────────────────
    if ($examen_mode === 'rattrapage') {
        $etudiantsATraiter = [];
        foreach ($etudiants as $e) {
            $s = $connexion->prepare("
                SELECT etudiant FROM recap
                WHERE etudiant = ?
                  AND semestre = ?
                  AND annee    = ?
                  AND etab     = ?
                  AND examen   = 'ordinaire'
                  AND decision <> 'Validé'
                LIMIT 1
            ");
            $s->bind_param('isss', $e['etudiant_id'], $semestre, $annee, $etab);
            $s->execute();
            $s->store_result();
            $found = ($s->num_rows > 0);
            $s->close();

            $errors[] = "Etudiant {$e['etudiant_id']} → recap trouvé: " . ($found ? 'OUI' : 'NON')
                      . " | type_examen=$type_examen | examen_recap=$examen_recap";

            if ($found) $etudiantsATraiter[] = $e;
        }
    } else {
        // Session Ordinaire : tous les étudiants
        $etudiantsATraiter = $etudiants;
    }

    if (empty($etudiantsATraiter))
        jsonError("Aucun étudiant à traiter pour classe=$classe / examen=$examen_mode | " . implode(' | ', $errors));

    // ── ÉTAPE 3 : Récupérer les UE ───────────────────────────────────────────
    $stmtUE = $connexion->prepare("
        SELECT code AS ue_code
        FROM ue
        WHERE etab       = ?
          AND specialite = ?
          AND semestre   = ?
          AND niveau     = ?
    ");
    $stmtUE->bind_param('ssss', $etab, $specialite, $semestre, $niveau);
    $stmtUE->execute();
    $ues = $stmtUE->get_result()->fetch_all(MYSQLI_ASSOC);
    $stmtUE->close();

    if (empty($ues))
        jsonError("Aucune UE trouvée pour specialite=$specialite / semestre=$semestre / niveau=$niveau");

    // ── ÉTAPE 4 : Boucle UE → ECUE → Étudiants ──────────────────────────────
    foreach ($ues as $ue) {

        $stmtECUE = $connexion->prepare("
            SELECT code_ecue, libelle FROM ecue
            WHERE code_ue = ? AND etab = ?
        ");
        $stmtECUE->bind_param('ss', $ue['ue_code'], $etab);
        $stmtECUE->execute();
        $ecues = $stmtECUE->get_result()->fetch_all(MYSQLI_ASSOC);
        $stmtECUE->close();

        foreach ($ecues as $ecue) {

            $code_ecue    = $ecue['code_ecue'];
            $ecue_libelle = $ecue['libelle'];

            foreach ($etudiantsATraiter as $etu) {

                $etudiant_id = (int)$etu['etudiant_id'];

                // ── Calcul moyenne (théorique + pratique) ─────────────────
                $avgStmt = $connexion->prepare("
                    SELECT ROUND(
                        CASE
                            WHEN COUNT(*) = 2 THEN SUM(l.note) / 2
                            ELSE MAX(l.note)
                        END
                    , 2) AS moyenne
                    FROM ligne1 l
                    JOIN anonymat a ON a.numero    = l.anonymat
                                   AND a.ecue      = l.code_ecue
                                   AND a.annee     = l.annee
                                   AND a.type      = l.type_examen
                                   AND a.semestre  = l.semestre
                                   AND a.etab      = l.etab
                    WHERE l.code_ecue   = ?
                      AND l.semestre    = ?
                      AND l.annee       = ?
                      AND l.type_examen = ?
                      AND l.etab        = ?
                      AND a.etudiant    = ?
                ");
                $avgStmt->bind_param('sssssi', $code_ecue, $semestre, $annee, $type_examen, $etab, $etudiant_id);
                $avgStmt->execute();
                $avg = $avgStmt->get_result()->fetch_assoc()['moyenne'] ?? null;
                $avgStmt->close();

                if ($avg === null) {
                    $errors[] = "Pas de note : etudiant=$etudiant_id / ecue=$code_ecue / type=$type_examen";
                    continue;
                }

                // ── Vérifier si notation existe ───────────────────────────
                $checkStmt = $connexion->prepare("
                    SELECT id FROM notation
                    WHERE inscription = ?
                      AND code_ecue   = ?
                      AND semestre    = ?
                      AND annee       = ?
                      AND classe      = ?
                      AND etab        = ?
                    LIMIT 1
                ");
                $checkStmt->bind_param('isssss', $etudiant_id, $code_ecue, $semestre, $annee, $classe, $etab);
                $checkStmt->execute();
                $checkRow = $checkStmt->get_result()->fetch_assoc();
                $checkStmt->close();

                if ($checkRow) {
                    // ── UPDATE ────────────────────────────────────────────
                    $updStmt = $connexion->prepare("
                        UPDATE notation SET $champ_notation = ? WHERE id = ?
                    ");
                    $updStmt->bind_param('di', $avg, $checkRow['id']);
                    $updStmt->execute();
                    $updStmt->close();
                } else {
                    // ── INSERT ────────────────────────────────────────────
                    $moyEx          = ($type_examen === 'Session Ordinaire') ? $avg : null;
                    $session_rappel = ($type_examen === 'Session de rappel') ? $avg : null;

                    $insStmt = $connexion->prepare("
                        INSERT INTO notation
                            (inscription, classe, ecue, code_ecue, annee,
                             moyEx, session_rappel, semestre, etab, user_id)
                        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
                    ");
                    $insStmt->bind_param('issssddssi',
                        $etudiant_id, $classe, $ecue_libelle, $code_ecue, $annee,
                        $moyEx, $session_rappel, $semestre, $etab, $user_id
                    );
                    $insStmt->execute();
                    $insStmt->close();
                }

                $updated++;
            }
        }
    }

    $connexion->commit();
    jsonSuccess([
        'message' => "$updated notation(s) [$champ_notation] mises à jour",
        'errors'  => $errors
    ]);

} catch (Exception $e) {
    $connexion->rollback();
    jsonError('Erreur transaction : ' . $e->getMessage(), 500);
}
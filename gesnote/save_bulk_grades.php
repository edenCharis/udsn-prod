<?php
include '../php/connexion.php';
include '../php/lib.php';
session_start();

// ── Helpers ──────────────────────────────────────────────────────────────────
function jsonError(string $msg, int $code = 400): never {
    http_response_code($code);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode(['success' => false, 'error' => $msg]);
    exit();
}

function jsonSuccess(array $payload): never {
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode(array_merge(['success' => true], $payload));
    exit();
}

// ── Vérifications préalables ──────────────────────────────────────────────────
if ($_SESSION['id'] != session_id() || $_SESSION['role'] != 'gesnote') {
    jsonError('Session invalide', 401);
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    jsonError('Méthode non autorisée', 405);
}

// ── Lecture du payload JSON ───────────────────────────────────────────────────
// Le JS envoie Content-Type: application/json → $_POST est vide, il faut lire php://input
$raw = file_get_contents('php://input');
$input = json_decode($raw, true);

if (!$input || !isset($input['grades']) || !is_array($input['grades'])) {
    jsonError('Payload JSON invalide ou grades manquants');
}

// ── Extraction des champs ─────────────────────────────────────────────────────
$classe      = trim($input['classe']      ?? '');
$ecue        = trim($input['ecue']        ?? '');
$semestre    = trim($input['semestre']    ?? '');
$annee       = trim($input['annee']       ?? '');
$type_examen = trim($input['type_examen'] ?? '');
$ecue_libelle= trim($input['ecue_libelle']?? '');
$nature      = trim($input['nature']      ?? '');
$grades      = $input['grades'];

if (!$classe || !$ecue || !$semestre || !$annee || !$type_examen || !$nature) {
    jsonError('Champs obligatoires manquants');
}

$etablissement = $_SESSION['etablissement'];
$user_id       = (int) $_SESSION['id_user'];

$success_count = 0;
$skip_count    = 0;
$error_count   = 0;
$errors        = [];

// ── Transaction ───────────────────────────────────────────────────────────────
$connexion->begin_transaction();

try {
    foreach ($grades as $anonymat => $rawNote) {

        $anonymat = trim((string) $anonymat);
        $rawNote  = trim((string) $rawNote);

        // ── 1. Ignorer les cases vides (pas une erreur) ───────────────────────
        if ($rawNote === '' || $rawNote === null) {
            $skip_count++;
            continue;                          // ← BUG #2 corrigé
        }

        // ── 2. Validation numérique ───────────────────────────────────────────
        if (!is_numeric($rawNote)) {
            $errors[] = "Note non numérique pour $anonymat : '$rawNote'";
            $error_count++;
            continue;
        }

        $note = (float) $rawNote;

        if ($note < 0 || $note > 20) {
            $errors[] = "Note hors plage pour $anonymat : $note (doit être 0-20)";
            $error_count++;
            continue;
        }

        // ── 3. Upsert dans ligne1 ─────────────────────────────────────────────
        $check = $connexion->prepare(
            "SELECT id FROM ligne1
             WHERE anonymat = ? AND code_ecue = ? AND type_examen = ?
               AND annee = ? AND nature = ? AND semestre = ? AND etab = ?"
        );
        $check->bind_param('sssssss', $anonymat, $ecue, $type_examen, $annee, $nature, $semestre, $etablissement);
        $check->execute();
        $checkResult = $check->get_result();

        if ($checkResult->num_rows > 0) {
            $existingRow = $checkResult->fetch_assoc();
            $upd = $connexion->prepare(
                "UPDATE ligne1 SET note = ?, user_id = ? WHERE id = ?"
            );
            $upd->bind_param('dii', $note, $user_id, $existingRow['id']);
            if ($upd->execute()) {
                $success_count++;
            } else {
                $errors[] = "Erreur UPDATE ligne1 pour $anonymat : " . $upd->error;
                $error_count++;
                $upd->close(); $check->close();
                continue;
            }
            $upd->close();
        } else {
            $ins = $connexion->prepare(
                "INSERT INTO ligne1
                    (nature, anonymat, code_ecue, ecue, semestre, type_examen, note, annee, user_id, etab)
                 VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)"
            );
            $ins->bind_param('ssssssdsis',
                $nature, $anonymat, $ecue, $ecue_libelle,
                $semestre, $type_examen, $note, $annee, $user_id, $etablissement
            );
            if ($ins->execute()) {
                $success_count++;
            } else {
                $errors[] = "Erreur INSERT ligne1 pour $anonymat : " . $ins->error;
                $error_count++;
                $ins->close(); $check->close();
                continue;
            }
            $ins->close();
        }
        $check->close();

        // ── 4. Récupération de l'inscription étudiant ────────────────────────
        $etudiant_id = getIdInscriptionFromAnonymat(
            $nature, $type_examen, $anonymat, $annee, $ecue, $semestre,
            $connexion, $etablissement
        );

        if (!$etudiant_id) {
            // DEBUG : on inclut tous les paramètres pour comprendre pourquoi introuvable
            $errors[] = "[notation] getIdInscriptionFromAnonymat a retourné null "
                      . "— anonymat=$anonymat | nature=$nature | type=$type_examen "
                      . "| annee=$annee | ecue=$ecue | semestre=$semestre | etab=$etablissement";
            $error_count++;
            continue;
        }

        // ── 5. Calcul de la moyenne réelle (TP + Théorie) ────────────────────
        $avgStmt = $connexion->prepare(
            "SELECT ROUND(AVG(l.note), 2) AS moyenne
             FROM ligne1 l
             JOIN anonymat a
               ON l.anonymat = a.numero
              AND l.semestre  = a.semestre
              AND l.annee     = a.annee
              AND l.etab      = a.etab
              AND l.code_ecue = a.ecue
             WHERE l.semestre    = ?
               AND l.annee       = ?
               AND l.type_examen = ?
               AND l.etab        = ?
               AND l.code_ecue   = ?
               AND a.etudiant    = ?"
        );
        $avgStmt->bind_param('sssssi',
            $semestre, $annee, $type_examen, $etablissement, $ecue, $etudiant_id
        );
        $avgStmt->execute();
        $avgRow = $avgStmt->get_result()->fetch_assoc();
        $avgStmt->close();

        $avg = $avgRow['moyenne'] ?? null;

        if ($avg === null) {
            // DEBUG : la requête AVG n'a rien trouvé — données ou jointure incorrectes
            $errors[] = "[notation] AVG = null pour etudiant_id=$etudiant_id "
                      . "| anonymat=$anonymat | ecue=$ecue | semestre=$semestre "
                      . "| type=$type_examen | etab=$etablissement";
            $error_count++;
            continue;
        }

        /* ── 6. Mise à jour de la table notation 
        
        $id_notation = verifierInscriptionNotation(
            $connexion, $etudiant_id, $ecue, $semestre, $classe, $annee
        );

        if ($id_notation === null) {
            // DEBUG : verifierInscriptionNotation n'a pas trouvé de ligne
            $errors[] = "[notation] verifierInscriptionNotation = null "
                      . "→ tentative INSERT — etudiant_id=$etudiant_id | ecue=$ecue "
                      . "| semestre=$semestre | classe=$classe | annee=$annee";
        }

        mettreAJourNotationExamen(
            $connexion, $etudiant_id, $ecue, $semestre,
            $classe, $annee, $type_examen, $avg, $user_id, $etablissement
        );*/
    }

    // ── Logger l'action ───────────────────────────────────────────────────────
    logUserAction(
        $connexion, $user_id,
        "Saisie en masse - $success_count notes",
        date('Y-m-d H:i:s'),
        $_SERVER['REMOTE_ADDR'],
        "Classe: $classe, ECUE: $ecue, Type: $type_examen, Nature: $nature"
    );

    $connexion->commit();

    // ── Réponse JSON ──────────────────────────────────────────────────────────
    $message = "$success_count note(s) enregistrée(s)";
    if ($skip_count  > 0) $message .= ", $skip_count ignorée(s) (vide)";
    if ($error_count > 0) $message .= ", $error_count erreur(s)";

    jsonSuccess([
        'message'       => $message,
        'success_count' => $success_count,
        'skip_count'    => $skip_count,
        'error_count'   => $error_count,
        'errors'        => $errors,
    ]);

} catch (Exception $e) {
    $connexion->rollback();
    error_log('save_bulk_grades error: ' . $e->getMessage());
    jsonError('Erreur serveur : ' . $e->getMessage(), 500);
}
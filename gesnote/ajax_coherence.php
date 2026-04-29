<?php
/**
 * ajax_coherence.php
 * Retourne par étudiant/ECUE (regroupé, sans split par nature) :
 *  - anonymats + notes théorique ET pratique (Ord et Rappel)
 *  - moyenne calculée = (theo + prat) / 2  →  à comparer à notation.moyEx / session_rappel
 *  - valeurs actuelles de notation
 *  - flag incoherent
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

$rolesAutorises = ['gesnote', 'admin', 'anonymat', 'direction'];
if (!isset($_SESSION['id']) || !($_SESSION['id'] == session_id() && in_array($_SESSION['role'], $rolesAutorises))) {
    echo json_encode(['error' => 'Non autorisé']); exit();
}
if (!isset($connexion) || !$connexion) {
    echo json_encode(['error' => 'Connexion BD échouée']); exit();
}

$classe   = trim($_POST['classe']   ?? '');
$ecue     = trim($_POST['ecue']     ?? '');
$semestre = trim($_POST['semestre'] ?? '');
$annee    = trim($_POST['annee']    ?? '');
$etab     = $_SESSION['etablissement'] ?? '';

if (!$etab) { echo json_encode(['error' => 'Établissement introuvable']); exit(); }

$whereConditions = ["i.etab = ?"];
$params = [$etab];
$types  = 's';

if ($ecue)     { $whereConditions[] = 'ord_grp.ecue = ?';     $params[] = $ecue;     $types .= 's'; }
if ($classe)   { $whereConditions[] = 'ord_grp.classe = ?';   $params[] = $classe;   $types .= 's'; }
if ($semestre) { $whereConditions[] = 'ord_grp.semestre = ?'; $params[] = $semestre; $types .= 's'; }
if ($annee)    { $whereConditions[] = 'i.annee = ?';          $params[] = $annee;    $types .= 's'; }

$whereClause = 'WHERE ' . implode(' AND ', $whereConditions);

/*
 * Pivot Ordinaire et Rappel via sous-requêtes groupées :
 * MAX(CASE WHEN nature='...' THEN ...) pour ramener theo+prat sur une ligne.
 */
$sql = "
    SELECT
        i.id                                         AS inscription_id,
        i.candidat                                   AS matricule,
        CONCAT(c.nom, ' ', c.prenom)                 AS nom_prenom,
        ord_grp.ecue                                 AS code_ecue,
        e.libelle                                    AS libelle_ecue,
        ord_grp.classe,
        ord_grp.semestre,
        i.annee,

        /* ── Ordinaire ────────────────────────────────── */
        ord_grp.anon_th          AS anon_ord_th,
        ord_grp.note_th          AS note_ord_th,
        ord_grp.anon_pr          AS anon_ord_pr,
        ord_grp.note_pr          AS note_ord_pr,

        /* ── Rappel ───────────────────────────────────── */
        rap_grp.anon_th          AS anon_rap_th,
        rap_grp.note_th          AS note_rap_th,
        rap_grp.anon_pr          AS anon_rap_pr,
        rap_grp.note_pr          AS note_rap_pr,

        /* ── Table notation actuelle ─────────────────── */
        n.id                     AS notation_id,
        n.moyDev                 AS not_moy_dev,
        n.moyEx                  AS not_moy_ex,
        n.session_rappel         AS not_session_rappel,
        n.moyGen                 AS not_moy_gen,
        n.moyenGenRattrapage     AS not_moy_gen_rat

    FROM inscription i
    JOIN candidat c ON c.code = i.candidat

    /* ── Pivot Session Ordinaire ──────────────────────────────────── */
    JOIN (
        SELECT
            a.etudiant, a.ecue, a.classe, a.semestre, a.annee, a.etab,
            MAX(CASE WHEN a.nature = 'Examen Theorique' THEN a.numero END) AS anon_th,
            MAX(CASE WHEN a.nature = 'Examen Pratique'  THEN a.numero END) AS anon_pr,
            MAX(CASE WHEN a.nature = 'Examen Theorique' THEN l.note  END) AS note_th,
            MAX(CASE WHEN a.nature = 'Examen Pratique'  THEN l.note  END) AS note_pr
        FROM anonymat a
        LEFT JOIN ligne1 l ON  l.anonymat    = a.numero
                           AND l.code_ecue   = a.ecue
                           AND l.type_examen = 'Session Ordinaire'
                           AND l.etab        = a.etab
        WHERE a.type = 'Session Ordinaire'
        GROUP BY a.etudiant, a.ecue, a.classe, a.semestre, a.annee, a.etab
    ) ord_grp ON ord_grp.etudiant = i.id
             AND ord_grp.annee    = i.annee
             AND ord_grp.etab     = i.etab

    JOIN ecue e ON e.code_ecue = ord_grp.ecue AND e.etab = i.etab

    /* ── Pivot Session de Rappel ──────────────────────────────────── */
    LEFT JOIN (
        SELECT
            a.etudiant, a.ecue, a.semestre, a.annee, a.etab,
            MAX(CASE WHEN a.nature = 'Examen Theorique' THEN a.numero END) AS anon_th,
            MAX(CASE WHEN a.nature = 'Examen Pratique'  THEN a.numero END) AS anon_pr,
            MAX(CASE WHEN a.nature = 'Examen Theorique' THEN l.note  END) AS note_th,
            MAX(CASE WHEN a.nature = 'Examen Pratique'  THEN l.note  END) AS note_pr
        FROM anonymat a
        LEFT JOIN ligne1 l ON  l.anonymat    = a.numero
                           AND l.code_ecue   = a.ecue
                           AND l.type_examen = 'Session de Rappel'
                           AND l.etab        = a.etab
        WHERE a.type = 'Session de Rappel'
        GROUP BY a.etudiant, a.ecue, a.semestre, a.annee, a.etab
    ) rap_grp ON rap_grp.etudiant = i.id
             AND rap_grp.ecue     = ord_grp.ecue
             AND rap_grp.semestre = ord_grp.semestre
             AND rap_grp.annee    = i.annee
             AND rap_grp.etab     = i.etab

    /* ── notation ─────────────────────────────────────────────────── */
    LEFT JOIN notation n ON  n.inscription = i.id
                         AND n.code_ecue   = ord_grp.ecue
                         AND n.annee       = i.annee
                         AND n.semestre    = ord_grp.semestre
                         AND n.etab        = i.etab

    $whereClause
    ORDER BY ord_grp.classe, ord_grp.ecue, c.nom, c.prenom
";

$stmt = $connexion->prepare($sql);
if (!$stmt) {
    echo json_encode(['error' => 'prepare() : ' . $connexion->error, 'sql' => $sql]); exit();
}
$stmt->bind_param($types, ...$params);
if (!$stmt->execute()) {
    echo json_encode(['error' => 'execute() : ' . $stmt->error]); exit();
}
$result = $stmt->get_result();
if ($result === false) {
    echo json_encode(['error' => 'get_result() : ' . $stmt->error]); exit();
}

$rows = [];
$incoherentCount = 0;

while ($row = $result->fetch_assoc()) {
    $row['libelle_ecue'] = str_replace('+', "'", $row['libelle_ecue']);
    $row['nom_prenom']   = str_replace('+', "'", $row['nom_prenom']);

    // ── Calcul moyEx depuis les notes ligne1 ──────────────────────────────────
    $nth = ($row['note_ord_th'] !== null && $row['note_ord_th'] !== '') ? (float)$row['note_ord_th'] : null;
    $npr = ($row['note_ord_pr'] !== null && $row['note_ord_pr'] !== '') ? (float)$row['note_ord_pr'] : null;

    if ($nth !== null && $npr !== null)      { $moyEx_calc = round(($nth + $npr) / 2, 2); $hasBoth = true; }
    elseif ($nth !== null)                   { $moyEx_calc = $nth;   $hasBoth = false; }
    elseif ($npr !== null)                   { $moyEx_calc = $npr;   $hasBoth = false; }
    else                                     { $moyEx_calc = null;   $hasBoth = false; }

    $row['moy_ex_calc']    = $moyEx_calc;
    $row['has_two_natures'] = $hasBoth;

    // ── Calcul session_rappel depuis les notes ligne1 ─────────────────────────
    $nth_r = ($row['note_rap_th'] !== null && $row['note_rap_th'] !== '') ? (float)$row['note_rap_th'] : null;
    $npr_r = ($row['note_rap_pr'] !== null && $row['note_rap_pr'] !== '') ? (float)$row['note_rap_pr'] : null;

    if ($nth_r !== null && $npr_r !== null)  $sessRap_calc = round(($nth_r + $npr_r) / 2, 2);
    elseif ($nth_r !== null)                 $sessRap_calc = $nth_r;
    elseif ($npr_r !== null)                 $sessRap_calc = $npr_r;
    else                                     $sessRap_calc = null;

    $row['sess_rap_calc'] = $sessRap_calc;

    // ── Détection incohérence ─────────────────────────────────────────────────
    $notMoyEx   = ($row['not_moy_ex']         !== null && $row['not_moy_ex']         !== '') ? (float)$row['not_moy_ex']         : null;
    $notSessRap = ($row['not_session_rappel'] !== null && $row['not_session_rappel'] !== '') ? (float)$row['not_session_rappel'] : null;

    $incoherent = false;
    if ($moyEx_calc   !== null && ($notMoyEx   === null || abs($moyEx_calc   - $notMoyEx)   > 0.009)) $incoherent = true;
    if ($sessRap_calc !== null && ($notSessRap === null || abs($sessRap_calc - $notSessRap) > 0.009)) $incoherent = true;

    $row['incoherent'] = $incoherent;
    if ($incoherent) $incoherentCount++;

    $rows[] = $row;
}

echo json_encode([
    'rows'             => $rows,
    'count'            => count($rows),
    'incoherent_count' => $incoherentCount
]);
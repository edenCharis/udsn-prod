<?php
/**
 * ajax_stats_classe.php
 * ─────────────────────────────────────────────────────────────────────────────
 * RÈGLE D'INCOHÉRENCE (unique) :
 *
 *   Depuis ligne1 (vraies notes des copies) :
 *     si note_th ET note_pr existent  → moy_calc = round((note_th + note_pr)/2, 2)
 *     si seulement note_th            → moy_calc = note_th
 *     si seulement note_pr            → moy_calc = note_pr
 *
 *   Ordinaire  : moy_calc ≠ notation.moyEx           → INCOHÉRENT
 *   Rattrapage : moy_calc ≠ notation.session_rappel  → INCOHÉRENT
 *
 *   notation.moyEx IS NULL          → ignoré (pas une erreur)
 *   notation.session_rappel IS NULL → ignoré (pas une erreur)
 *   Pas de note dans ligne1         → ignoré
 *
 * action=summary → stats agrégées par classe
 * action=detail  → liste étudiants/ECUE pour une classe
 * ─────────────────────────────────────────────────────────────────────────────
 */

set_error_handler(function ($errno, $errstr, $errfile, $errline) {
    ob_clean(); header('Content-Type: application/json; charset=utf-8');
    echo json_encode(['error' => "PHP [$errno]: $errstr in $errfile:$errline"]); exit();
});
set_exception_handler(function ($e) {
    ob_clean(); header('Content-Type: application/json; charset=utf-8');
    echo json_encode(['error' => 'Exception: ' . $e->getMessage()]); exit();
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

$action   = trim($_POST['action']   ?? 'summary');
$annee    = trim($_POST['annee']    ?? '');
$semestre = trim($_POST['semestre'] ?? '');
$classe   = trim($_POST['classe']   ?? '');
$etab     = $_SESSION['etablissement'] ?? '';

if (!$etab) { echo json_encode(['error' => 'Établissement introuvable']); exit(); }

/* ─────────────────────────────────────────────────────────────────────────────
   SOUS-REQUÊTE PIVOT — calcule moy_calc depuis ligne1 par étudiant/ECUE
   Utilisée identiquement pour ordinaire et rattrapage (seul le type change).
   ───────────────────────────────────────────────────────────────────────────── */
function pivotLigne1($typeSession) {
    $t = mysqli_real_escape_string($GLOBALS['connexion'], $typeSession);
    return "
        SELECT
            a.etudiant,
            a.ecue,
            a.classe,
            a.semestre,
            a.annee,
            a.etab,
            /* note théorique et pratique */
            MAX(CASE WHEN a.nature = 'Examen Theorique' THEN l.note END) AS note_th,
            MAX(CASE WHEN a.nature = 'Examen Pratique'  THEN l.note END) AS note_pr,
            /* moy_calc : (th+pr)/2 si les deux, sinon l'un ou l'autre */
            CASE
                WHEN MAX(CASE WHEN a.nature='Examen Theorique' THEN l.note END) IS NOT NULL
                 AND MAX(CASE WHEN a.nature='Examen Pratique'  THEN l.note END) IS NOT NULL
                THEN ROUND((
                        MAX(CASE WHEN a.nature='Examen Theorique' THEN l.note END) +
                        MAX(CASE WHEN a.nature='Examen Pratique'  THEN l.note END)
                     ) / 2, 2)
                WHEN MAX(CASE WHEN a.nature='Examen Theorique' THEN l.note END) IS NOT NULL
                THEN MAX(CASE WHEN a.nature='Examen Theorique' THEN l.note END)
                WHEN MAX(CASE WHEN a.nature='Examen Pratique'  THEN l.note END) IS NOT NULL
                THEN MAX(CASE WHEN a.nature='Examen Pratique'  THEN l.note END)
                ELSE NULL
            END AS moy_calc
        FROM anonymat a
        LEFT JOIN ligne1 l
            ON  l.anonymat    = a.numero
            AND l.code_ecue   = a.ecue
            AND l.type_examen = '$t'
            AND l.etab        = a.etab
        WHERE a.type = '$t'
        GROUP BY a.etudiant, a.ecue, a.classe, a.semestre, a.annee, a.etab
    ";
}

/* ─────────────────────────────────────────────────────────────────────────────
   REQUÊTE PRINCIPALE — commune à summary et detail
   Filtrée ensuite par classe si besoin.
   ───────────────────────────────────────────────────────────────────────────── */
function buildSQL($etab, $annee, $semestre, $classe, &$params, &$types) {
    $pivOrd = pivotLigne1('Session Ordinaire');
    $pivRap = pivotLigne1('Session de Rappel');

    $where  = ["i.etab = ?"];
    $params = [$etab];
    $types  = 's';

    if ($annee)    { $where[] = "i.annee = ?";          $params[] = $annee;    $types .= 's'; }
    if ($semestre) { $where[] = "ord.semestre = ?";     $params[] = $semestre; $types .= 's'; }
    if ($classe)   { $where[] = "ord.classe = ?";       $params[] = $classe;   $types .= 's'; }

    $whereClause = 'WHERE ' . implode(' AND ', $where);

    return "
        SELECT
            i.candidat                           AS matricule,
            CONCAT(c.nom, ' ', c.prenom)         AS nom_prenom,
            ord.classe,
            ord.semestre,
            i.annee,
            ord.ecue                             AS code_ecue,
            e.libelle                            AS libelle_ecue,

            /* ── Ligne1 ordinaire ──────────────────────────── */
            ord.note_th                          AS ord_note_th,
            ord.note_pr                          AS ord_note_pr,
            ord.moy_calc                         AS ord_moy_calc,

            /* ── Ligne1 rattrapage ─────────────────────────── */
            rap.note_th                          AS rap_note_th,
            rap.note_pr                          AS rap_note_pr,
            rap.moy_calc                         AS rap_moy_calc,

            /* ── Notation stockée ──────────────────────────── */
            n.id                                 AS notation_id,
            n.moyEx                              AS not_moy_ex,
            n.session_rappel                     AS not_session_rappel

        FROM inscription i
        JOIN candidat c ON c.code = i.candidat

        /* pivot ordinaire */
        JOIN ($pivOrd) ord
            ON  ord.etudiant = i.id
            AND ord.annee    = i.annee
            AND ord.etab     = i.etab

        JOIN ecue e
            ON  e.code_ecue = ord.ecue
            AND e.etab      = i.etab

        /* pivot rattrapage (optionnel) */
        LEFT JOIN ($pivRap) rap
            ON  rap.etudiant = i.id
            AND rap.ecue     = ord.ecue
            AND rap.semestre = ord.semestre
            AND rap.annee    = i.annee
            AND rap.etab     = i.etab

        /* notation */
        LEFT JOIN notation n
            ON  n.inscription = i.id
            AND n.code_ecue   = ord.ecue
            AND n.annee       = i.annee
            AND n.semestre    = ord.semestre
            AND n.etab        = i.etab

        $whereClause
        ORDER BY ord.classe, c.nom, c.prenom, ord.ecue
    ";
}

/* ─────────────────────────────────────────────────────────────────────────────
   HELPER — évalue le statut d'une ligne
   Retourne un tableau :
     ord_status : 'ok' | 'bad' | null (ignoré)
     rap_status : 'ok' | 'bad' | null (ignoré)
     incoherent : true si au moins un 'bad'
   ───────────────────────────────────────────────────────────────────────────── */
function evalRow($r) {
    // ── Ordinaire ────────────────────────────────────────────────────────────
    $ordCalc  = ($r['ord_moy_calc'] !== null && $r['ord_moy_calc'] !== '')
                ? (float)$r['ord_moy_calc'] : null;
    $notMoyEx = ($r['not_moy_ex']   !== null && $r['not_moy_ex']   !== '')
                ? (float)$r['not_moy_ex']   : null;

    if ($ordCalc === null || $notMoyEx === null) {
        // pas de note calculée ou notation NULL → ignoré
        $ordStatus = null;
    } elseif (abs($ordCalc - $notMoyEx) <= 0.009) {
        $ordStatus = 'ok';
    } else {
        $ordStatus = 'bad';
    }

    // ── Rattrapage ───────────────────────────────────────────────────────────
    $rapCalc  = ($r['rap_moy_calc']         !== null && $r['rap_moy_calc']         !== '')
                ? (float)$r['rap_moy_calc']         : null;
    $notSessR = ($r['not_session_rappel']   !== null && $r['not_session_rappel']   !== '')
                ? (float)$r['not_session_rappel']   : null;

    if ($rapCalc === null || $notSessR === null) {
        $rapStatus = null;
    } elseif (abs($rapCalc - $notSessR) <= 0.009) {
        $rapStatus = 'ok';
    } else {
        $rapStatus = 'bad';
    }

    return [
        'ord_status' => $ordStatus,
        'rap_status' => $rapStatus,
        'incoherent' => ($ordStatus === 'bad' || $rapStatus === 'bad'),
    ];
}

/* ═══════════════════════════════════════════════════════════════════════════
   ACTION : SUMMARY
   ═══════════════════════════════════════════════════════════════════════════ */
if ($action === 'summary') {

    $params = []; $types = '';
    $sql = buildSQL($etab, $annee, $semestre, '' /* toutes classes */, $params, $types);

    $stmt = $connexion->prepare($sql);
    if (!$stmt) { echo json_encode(['error' => 'prepare: ' . $connexion->error]); exit(); }
    $stmt->bind_param($types, ...$params);
    if (!$stmt->execute()) { echo json_encode(['error' => 'execute: ' . $stmt->error]); exit(); }
    $res = $stmt->get_result();

    $classes = [];

    while ($r = $res->fetch_assoc()) {
        $cl = $r['classe'];
        if (!isset($classes[$cl])) {
            $classes[$cl] = [
                'classe'       => $cl,
                'etudiants'    => [],
                'nb_lignes'    => 0,
                'ord_ok'       => 0,
                'ord_bad'      => 0,
                'rap_ok'       => 0,
                'rap_bad'      => 0,
            ];
        }
        $c = &$classes[$cl];
        $c['nb_lignes']++;
        $c['etudiants'][$r['matricule']] = true;

        $ev = evalRow($r);
        if ($ev['ord_status'] === 'ok')  $c['ord_ok']++;
        if ($ev['ord_status'] === 'bad') $c['ord_bad']++;
        if ($ev['rap_status'] === 'ok')  $c['rap_ok']++;
        if ($ev['rap_status'] === 'bad') $c['rap_bad']++;
    }

    $result = [];
    foreach ($classes as $cl => $c) {
        $tot_ord = $c['ord_ok'] + $c['ord_bad'];
        $tot_rap = $c['rap_ok'] + $c['rap_bad'];

        $result[] = [
            'classe'        => $cl,
            'nb_etudiants'  => count($c['etudiants']),
            'nb_lignes'     => $c['nb_lignes'],
            'ord_ok'        => $c['ord_ok'],
            'ord_bad'       => $c['ord_bad'],
            'acc_ord'       => $tot_ord > 0 ? round($c['ord_ok'] / $tot_ord * 100, 1) : null,
            'rap_ok'        => $c['rap_ok'],
            'rap_bad'       => $c['rap_bad'],
            'acc_rap'       => $tot_rap > 0 ? round($c['rap_ok'] / $tot_rap * 100, 1) : null,
            'has_rap'       => ($tot_rap > 0),
        ];
    }

    usort($result, fn($a,$b) => strcmp($a['classe'], $b['classe']));
    echo json_encode(['rows' => $result, 'count' => count($result)]);
    exit();
}

/* ═══════════════════════════════════════════════════════════════════════════
   ACTION : DETAIL
   ═══════════════════════════════════════════════════════════════════════════ */
if ($action === 'detail') {

    if (!$classe) { echo json_encode(['error' => 'Paramètre classe manquant']); exit(); }

    $params = []; $types = '';
    $sql = buildSQL($etab, $annee, $semestre, $classe, $params, $types);

    $stmt = $connexion->prepare($sql);
    if (!$stmt) { echo json_encode(['error' => 'prepare detail: ' . $connexion->error]); exit(); }
    $stmt->bind_param($types, ...$params);
    if (!$stmt->execute()) { echo json_encode(['error' => 'execute detail: ' . $stmt->error]); exit(); }
    $res = $stmt->get_result();

    $rows = []; $incoherent_count = 0;

    while ($r = $res->fetch_assoc()) {
        $r['nom_prenom']   = str_replace('+', "'", $r['nom_prenom']);
        $r['libelle_ecue'] = str_replace('+', "'", $r['libelle_ecue']);

        $ev = evalRow($r);
        $r['ord_status'] = $ev['ord_status'];
        $r['rap_status'] = $ev['rap_status'];
        $r['incoherent'] = $ev['incoherent'];
        if ($ev['incoherent']) $incoherent_count++;

        $rows[] = $r;
    }

    echo json_encode([
        'rows'             => $rows,
        'count'            => count($rows),
        'incoherent_count' => $incoherent_count,
    ]);
    exit();
}

echo json_encode(['error' => 'Action inconnue : ' . $action]);
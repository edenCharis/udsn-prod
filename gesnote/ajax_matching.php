<?php
/**
 * ajax_matching.php
 * Une ligne par étudiant/ECUE :
 *   code_anonyme_ord | note_ordinaire | code_anonyme_rap | note_rappel
 */

set_error_handler(function($errno, $errstr, $errfile, $errline) {
    ob_clean();
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode(['error' => "Erreur PHP [$errno]: $errstr dans $errfile ligne $errline"]);
    exit();
});

set_exception_handler(function($e) {
    ob_clean();
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode(['error' => "Exception: " . $e->getMessage()]);
    exit();
});

ob_start();

include '../php/connexion.php';
include '../php/lib.php';
session_start();

ob_clean();
header('Content-Type: application/json; charset=utf-8');

$rolesAutorises = ['gesnote', 'admin', 'anonymat', 'direction'];
if (!isset($_SESSION['id']) || !($_SESSION['id'] == session_id() && in_array($_SESSION['role'], $rolesAutorises))) {
    echo json_encode(['error' => 'Non autorisé']);
    exit();
}

if (!isset($connexion) || !$connexion) {
    echo json_encode(['error' => 'Connexion base de données échouée']);
    exit();
}

$classe   = trim($_POST['classe']   ?? '');
$ecue     = trim($_POST['ecue']     ?? '');
$semestre = trim($_POST['semestre'] ?? '');
$annee    = trim($_POST['annee']    ?? '');
$nature   = trim($_POST['nature']   ?? '');
$etab     = $_SESSION['etablissement'] ?? '';

if (!$etab) {
    echo json_encode(['error' => 'Établissement introuvable dans la session']);
    exit();
}

// ── Filtres dynamiques (sur inscription) ─────────────────────────────────────
$whereConditions = ["i.etab = ?"];
$params = [$etab];
$types  = 's';

if ($ecue)     { $whereConditions[] = 'a_ord.ecue = ?';     $params[] = $ecue;     $types .= 's'; }
if ($classe)   { $whereConditions[] = 'a_ord.classe = ?';   $params[] = $classe;   $types .= 's'; }
if ($semestre) { $whereConditions[] = 'a_ord.semestre = ?'; $params[] = $semestre; $types .= 's'; }
if ($annee)    { $whereConditions[] = 'i.annee = ?';        $params[] = $annee;    $types .= 's'; }
if ($nature)   { $whereConditions[] = 'a_ord.nature = ?';   $params[] = $nature;   $types .= 's'; }

$whereClause = 'WHERE ' . implode(' AND ', $whereConditions);

// ── Requête : pivot via deux LEFT JOIN sur anonymat ───────────────────────────
// a_ord = anonymat Session Ordinaire (base de la liste)
// a_rap = anonymat Session de Rappel (peut ne pas exister)
// l_ord = note Session Ordinaire
// l_rap = note Session de Rappel
$sql = "
    SELECT
        i.candidat                         AS matricule,
        CONCAT(c.nom, ' ', c.prenom)        AS nom_prenom,
        a_ord.ecue                          AS code_ecue,
        e.libelle                           AS libelle_ecue,
        a_ord.classe                        AS classe,
        a_ord.semestre                      AS semestre,
        a_ord.nature                        AS nature,
        i.annee                             AS annee,
        a_ord.numero                        AS code_anonyme_ord,
        l_ord.note                          AS note_ordinaire,
        a_rap.numero                        AS code_anonyme_rap,
        l_rap.note                          AS note_rappel
    FROM inscription i
    JOIN candidat c     ON c.code      = i.candidat
    -- Anonymat Session Ordinaire
    JOIN anonymat a_ord ON a_ord.etudiant = i.id
                       AND a_ord.annee    = i.annee
                       AND a_ord.etab     = i.etab
                       AND a_ord.type     = 'Session Ordinaire'
    -- ECUE libellé
    JOIN ecue e         ON e.code_ecue = a_ord.ecue
                       AND e.etab      = i.etab
    -- Note Session Ordinaire
    LEFT JOIN ligne1 l_ord ON l_ord.anonymat   = a_ord.numero
                          AND l_ord.code_ecue  = a_ord.ecue
                          AND l_ord.annee      = a_ord.annee
                          AND l_ord.nature     = a_ord.nature
                          AND l_ord.semestre=a_ord.semestre
                          AND l_ord.type_examen = 'Session Ordinaire'
                          AND l_ord.etab       = i.etab
    -- Anonymat Session de Rappel (même étudiant, même ECUE, même année, même nature)
    LEFT JOIN anonymat a_rap ON a_rap.etudiant = i.id
                            AND a_rap.annee    = i.annee
                            AND a_rap.ecue     = a_ord.ecue
                            AND a_rap.nature   = a_ord.nature
                            AND a_rap.semestre=a_ord.semestre
                            AND a_rap.etab     = i.etab
                            AND a_rap.type     = 'Session de Rappel'
    -- Note Session de Rappel
    LEFT JOIN ligne1 l_rap ON l_rap.anonymat    = a_rap.numero
                          AND l_rap.code_ecue   = a_rap.ecue
                          AND l_rap.annee       = a_rap.annee
                          AND l_rap.nature      = a_rap.nature
                          AND l_rap.semestre = a_rap.semestre
                          AND l_rap.type_examen = 'Session de Rappel'
                          AND l_rap.etab        = i.etab
    $whereClause
    ORDER BY a_ord.classe, a_ord.ecue, c.nom, c.prenom
";

$stmt = $connexion->prepare($sql);
if (!$stmt) {
    echo json_encode(['error' => 'Erreur prepare() : ' . $connexion->error, 'sql' => $sql]);
    exit();
}

$stmt->bind_param($types, ...$params);

if (!$stmt->execute()) {
    echo json_encode(['error' => 'Erreur execute() : ' . $stmt->error]);
    exit();
}

$result = $stmt->get_result();
if ($result === false) {
    echo json_encode(['error' => 'Erreur get_result() : ' . $stmt->error]);
    exit();
}

$rows = [];
while ($row = $result->fetch_assoc()) {
    $row['libelle_ecue'] = str_replace('+', "'", $row['libelle_ecue']);
    $row['nom_prenom']   = str_replace('+', "'", $row['nom_prenom']);
    $rows[] = $row;
}

echo json_encode(['rows' => $rows, 'count' => count($rows)]);
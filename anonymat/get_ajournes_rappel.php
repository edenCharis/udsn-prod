<?php
/**
 * get_ajournes_rappel.php
 * Retourne les étudiants ajournés par ECUE pour la Session de Rappel
 * ── Utilise EXACTEMENT la même logique que generer_pv.php ──
 *    → calcul_moyenne(), verifierEliminatoire(), statutSoutenance()
 */

include '../php/connexion.php';
include '../php/lib.php';
session_start();

header('Content-Type: application/json');

if (!($_SESSION['id'] == session_id() && $_SESSION['role'] == "anonymat")) {
    echo json_encode(['success' => false, 'message' => 'Accès refusé']);
    exit;
}

$etablissement = $_SESSION['etablissement'];

$classe   = trim($_POST['classe']   ?? '');
$semestre = trim($_POST['semestre'] ?? '');
$annee    = trim($_POST['annee']    ?? '');
$nature   = trim($_POST['nature']   ?? 'Examen Theorique');

if (!$classe || !$semestre || !$annee) {
    echo json_encode(['success' => false, 'message' => 'Paramètres manquants']);
    exit;
}

$classe_sql   = mysqli_real_escape_string($connexion, $classe);
$semestre_sql = mysqli_real_escape_string($connexion, $semestre);
$annee_sql    = mysqli_real_escape_string($connexion, $annee);

// ── Spécialité de la classe ──
$res_spec   = $connexion->query("SELECT specialite FROM classe WHERE libelle='$classe_sql' LIMIT 1");
$specialite = '';
if ($res_spec && $row = $res_spec->fetch_assoc()) {
    $specialite = $row['specialite'];
}
$specialite_sql = mysqli_real_escape_string($connexion, $specialite);

// ── Requête UE (identique à generer_pv.php) ──
$sql_ue = "
    SELECT DISTINCT ue.code, ue.libelle
    FROM ue
    WHERE ue.etab       = '$etablissement'
      AND ue.specialite = '$specialite_sql'
      AND ue.semestre   = '$semestre_sql'
      AND ue.code IN (SELECT code_ue FROM ecue)
";

$r_ue_count = $connexion->query($sql_ue);
$nb_ue      = $r_ue_count ? $r_ue_count->num_rows : 0;

// ── Étudiants inscrits dans la classe ──
$sql_etudiants = "
    SELECT
        candidat.nom       AS nom,
        candidat.prenom    AS prenom,
        candidat.code AS matricule,
        inscription.id     AS insc_id
    FROM inscription
    JOIN candidat ON candidat.code      = inscription.candidat
    JOIN classe   ON inscription.classe = classe.libelle
    WHERE classe.libelle    = '$classe_sql'
      AND inscription.annee = '$annee_sql'
    GROUP BY candidat.nom, candidat.prenom
    ORDER BY LOWER(candidat.nom), LOWER(candidat.prenom)
";

$res_et    = $connexion->query($sql_etudiants);
$etudiants = [];
if ($res_et) {
    while ($et = $res_et->fetch_assoc()) {
        $etudiants[] = $et;
    }
}

if (empty($etudiants)) {
    echo json_encode(['success' => false, 'message' => 'Aucun étudiant inscrit dans cette classe']);
    exit;
}

// ════════════════════════════════════════════════════════
//  BOUCLE PRINCIPALE — même logique que generer_pv.php
// ════════════════════════════════════════════════════════
$ecues_map = [];

foreach ($etudiants as $et) {

    $insc_id = $et['insc_id'];

    // ── 1. Moyenne générale ──
    $tt = calcul_moyenne($insc_id, $semestre_sql, $annee_sql, $etablissement, $connexion,$_SESSION["etablissement"]);
    if ($tt === "-") continue;

    // ── 2. Collecter toutes les notes + tableau éliminatoires
    //        (copie exacte de generer_pv.php) ──
    $eliminatoires  = [];
    $notes_par_ecue = [];

    $r_ue2 = $connexion->query($sql_ue);
    if ($r_ue2) {
        while ($ue_row = $r_ue2->fetch_object()) {

            $sql_ecue = "SELECT * FROM ecue WHERE code_ue='"
                      . mysqli_real_escape_string($connexion, $ue_row->code) . "'";
            $r_ecue   = $connexion->query($sql_ecue);
            if (!$r_ecue) continue;

            while ($ecue = $r_ecue->fetch_object()) {

                $a = getEtudiantCC(
                    $insc_id, $connexion, $etablissement,
                    $semestre_sql, $ecue->libelle, $annee_sql
                );
                $b = getEtudiantEXT(
                    $insc_id, $connexion, $etablissement,
                    $semestre_sql, $ecue->libelle, $annee_sql
                );

                if ($a !== "-" && $b !== "-") {
                    $note             = round(($a + $b) / 2, 2);
                    $eliminatoires[]  = $note;      // sert à verifierEliminatoire()
                    $notes_par_ecue[$ecue->code_ecue] = [
                        'libelle' => str_replace('+', "'", $ecue->libelle),
                        'note'    => $note,
                    ];
                }
            }
        }
    }

    // ── 3. Décision globale du jury (copie exacte generer_pv.php) ──
    $decision_globale = "-";
    if (!empty($eliminatoires)) {
        if (verifierEliminatoire($eliminatoires) == false) {
            if ($nb_ue - 1 >= 1) {
                $decision_globale = statutSoutenance(round($tt, 2));
            }
        } else {
            $decision_globale = "Note Eliminatoire";
        }
    }

    // ── 4. Étudiant validé → pas de rappel ──
    $est_valide = (
        $decision_globale !== "-" &&
        $decision_globale !== "Note Eliminatoire" &&
        $decision_globale !== ""
    );
    if ($est_valide)                    continue;
    if ($decision_globale === "-")      continue;

    // ── 5. Par ECUE : note < 10 → repasse en rappel ──
    foreach ($notes_par_ecue as $code_ecue => $infos) {

        if ($infos['note'] >= 10) continue;

        // Code anonyme déjà généré ?
        $ec_sql = mysqli_real_escape_string($connexion, $code_ecue);
        $cl_sql = mysqli_real_escape_string($connexion, $classe);
        $na_sql = mysqli_real_escape_string($connexion, $nature);

        $sql_code = "
            SELECT numero FROM anonymat
            WHERE etudiant = '$insc_id'
              AND ecue     = '$ec_sql'
              AND classe   = '$cl_sql'
              AND semestre = '$semestre_sql'
              AND type     = 'Session de Rappel'
              AND nature   = '$na_sql'
              AND annee    = '$annee_sql'
              AND etab     = '$etablissement'
            LIMIT 1
        ";
        $res_code      = $connexion->query($sql_code);
        $code_existant = null;
        if ($res_code && $row_code = $res_code->fetch_assoc()) {
            $code_existant = $row_code['numero'];
        }

        // Afficher "Note Eliminatoire" si c'est la décision globale,
        // sinon montrer la note insuffisante de l'ECUE
        $decision_affichee = ($decision_globale === "Note Eliminatoire")
            ? "Note Eliminatoire"
            : "Insuffisant (" . number_format($infos['note'], 2, ',', '') . "/20)";

        if (!isset($ecues_map[$code_ecue])) {
            $ecues_map[$code_ecue] = [
                'code'      => $code_ecue,
                'libelle'   => $infos['libelle'],
                'etudiants' => [],
            ];
        }

        $ecues_map[$code_ecue]['etudiants'][] = [
            'insc_id'       => $insc_id,
            'nom'           => str_replace('+', "'", strtoupper($et['nom'])),
            'prenom'        => str_replace('+', "'", ucwords(strtolower($et['prenom']))),
            'matricule'     => $et['matricule'] ?? 'N/A',
            'moyenne'       => number_format($infos['note'], 2, ',', ''),
            'decision'      => $decision_affichee,
            'code_existant' => $code_existant,
        ];
    }
}

// ── Tri étudiants par note croissante, ECUE par code ──
$result_ecues = array_values($ecues_map);
foreach ($result_ecues as &$ecue) {
    usort($ecue['etudiants'], fn($a, $b) =>
        floatval(str_replace(',', '.', $a['moyenne'])) <=>
        floatval(str_replace(',', '.', $b['moyenne']))
    );
}
unset($ecue);
usort($result_ecues, fn($a, $b) => strcmp($a['code'], $b['code']));

echo json_encode([
    'success'  => true,
    'ecues'    => $result_ecues,
    'classe'   => $classe,
    'semestre' => $semestre,
    'annee'    => $annee,
]);
exit;
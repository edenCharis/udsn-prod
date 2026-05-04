<?php
/**
 * releve_notes.php  –  VERSION MODIFIÉE (amendements visuels + édition année + matricule + dept Licence 1/2/3)
 * Changements :
 *  1. Colonne "NOTES C.c+Ex.T" affiche le résultat calculé (CC+Ex)/2 au lieu de "CC + Ex"
 *  2. Impression limitée à UN seul semestre (passé via $_GET['semestre'])
 *  3. Département affiché "Licence 1/2/3" selon niveau (1ère, 2ème, 3ème année)
 *  4. Libellé "Crédits" en écriture verticale
 *  5. Nom de l'établissement retiré de l'en-tête
 * ── Amendements visuels (v2) ──
 *  6. Code UE aligné à gauche (non centré)
 *  7. En-tête "Unité d'Enseignement (UE)" sur une seule ligne
 *  8. Bordure complète sur la colonne Crédits
 *  9. "Session" avec S majuscule dans les en-têtes
 * 10. "Année académique" sur la ligne récap, sur une seule ligne
 * 11. Bloc signature correctement aligné à gauche
 * 12. Possibilité d'éditer l'année de première inscription (via GET ou bouton inline)
 * 13. Possibilité d'éditer le matricule (via GET ou bouton inline)
 */

include '../php/connexion.php';
include '../php/lib.php';
session_start();

error_reporting(E_ALL);
ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');

// Correctif ucwords pour les accents (UTF-8)
function ucwords_utf8($str) {
    // Met tout en format "Titre"
    $str = mb_convert_case($str, MB_CASE_TITLE, 'UTF-8');

    // Liste des mots à forcer en minuscule
    $exceptions = ['De', 'Des', 'Du', 'La', 'Le', 'Les', 'Et'];

    foreach ($exceptions as $word) {
        $str = preg_replace('/\b' . $word . '\b/u', mb_strtolower($word, 'UTF-8'), $str);
    }

    return $str;
}

// ── Fonction utilitaire : récupère moy + decision depuis recap ──
function obtenirMoyenne1($connexion, $etudiant, $semestre, $examen, $annee, $etablissement) {
    $requete = "SELECT moy, decision FROM recap
                WHERE etudiant = ? AND semestre = ? AND examen = ? AND annee = ? AND etab = ?";
    $stmt = $connexion->prepare($requete);
    $stmt->bind_param("issss", $etudiant, $semestre, $examen, $annee, $etablissement);
    $stmt->execute();
    $resultat = $stmt->get_result();
    $row = $resultat->fetch_assoc();
    $stmt->close();
    return $row ?? null;
}

// ── Sécurité session ──
if (!($_SESSION['id'] == session_id() && $_SESSION['role'] == "gesnote")) {
    header("location: ../login");
    exit;
}

if (!isset($_GET["ins_"])) {
    header("location: ../login");
    exit;
}

// ── Récupération du semestre sélectionné ──
$semestre_filtre = isset($_GET['semestre']) ? trim($_GET['semestre']) : null;

// ── Récupération des données étudiant ──
$id_etudiant     = $_GET["ins_"];
$etablissement   = $_SESSION["etablissement"];
$annee           = getAnneeInscription($connexion, $id_etudiant, $etablissement);
$code_etudiant   = getCandidatCodeByInscription($id_etudiant, $connexion);
$nom_etudiant    = getNomEtudiant($code_etudiant, $connexion, $_SESSION["lib_etab"]);
$prenom_etudiant = getPrenomEtudiant($code_etudiant, $connexion, $_SESSION["lib_etab"]);
$date_naissance  = getDateNaissanceCandidat($code_etudiant, $_SESSION["lib_etab"], $connexion);
$lieu_naissance  = getLieuNaissanceCandidat($code_etudiant, $_SESSION["lib_etab"], $connexion);
$specialite      = getSpecialitetudiant($id_etudiant, $etablissement, $connexion);


$niveau          = getNiveauEtudiant($id_etudiant, $etablissement, $connexion);
$classe          = getClasseByInscription($id_etudiant, $connexion);
$parcours        = getParcours($specialite, $connexion);
$etab_libelle    = $_SESSION["lib_etab"];
$univ            = $_SESSION["univ"] ?? "UNIVERSITE DENIS SASSOU-N'GUESSO";
$etab            = $_SESSION['lib_etab'] ?? $etablissement;

// ── Département : "Licence" pour 1ère/2ème/3ème année (comme l'original) ──
$dept_display = $niveau;
if (preg_match('/^(Première|Deuxième|Troisième)\s+année$/i', trim($niveau))) {
    $dept_display = 'Licence';
}

// ── Niveau : Licence 1 / 2 / 3 selon niveau ──
$map_licence = [
    'première'  => 'Licence 1',
    'premiere'  => 'Licence 1',
    'deuxième'  => 'Licence 2',
    'deuxieme'  => 'Licence 2',
    'troisième' => 'Licence 3',
    'troisieme' => 'Licence 3',
];
$niveau_display = $niveau;
if (preg_match('/^(Première|Deuxième|Troisième)\s+année$/i', trim($niveau), $m)) {
    $cle      = strtolower($m[1]);
    $cle_norm = str_replace(['è','é','ê','ë'], ['e','e','e','e'], $cle);
    $niveau_display = $map_licence[$cle] ?? ($map_licence[$cle_norm] ?? $niveau);
}

// ── Récupérer la série BAC et l'année BAC ──
$sql_bac  = "SELECT bac, anneebac FROM candidat WHERE code = ?";
$stmt_bac = $connexion->prepare($sql_bac);
$stmt_bac->bind_param("s", $code_etudiant);
$stmt_bac->execute();
$res_bac = $stmt_bac->get_result()->fetch_assoc();
$stmt_bac->close();
$serie_bac = $res_bac['bac']      ?? '-';
$annee_bac = $res_bac['anneebac'] ?? '-';

// ── Récupérer l'année de première inscription (DB) ──
$sql_premiere = "SELECT MIN(annee) AS premiere FROM inscription WHERE candidat = ? AND etab = ?";
$stmt_pre     = $connexion->prepare($sql_premiere);
$stmt_pre->bind_param("ss", $code_etudiant, $etablissement);
$stmt_pre->execute();
$res_pre    = $stmt_pre->get_result()->fetch_assoc();
$stmt_pre->close();
$annee_premiere_db = $res_pre['premiere'] ?? $annee;

// ── Override année de première inscription via GET ──
$annee_premiere_inscription = (isset($_GET['annee_premiere']) && trim($_GET['annee_premiere']) !== '')
    ? trim($_GET['annee_premiere'])
    : $annee_premiere_db;

// ── Override matricule via GET ──
$matricule_display = (isset($_GET['matricule_override']) && trim($_GET['matricule_override']) !== '')
    ? trim($_GET['matricule_override'])
    : strtoupper($code_etudiant);
    
    
// ── Spécialité affichée selon le niveau ──
if (preg_match('/^Première\s+année$/i', trim($niveau))) {
    // 1ère année : toujours Tronc Commun
    $specialite_display = 'Tronc Commun';

} elseif (preg_match('/^Deuxième\s+année$/i', trim($niveau))) {
    // 2ème année : Tronc Commun si Physique, sinon spécialité réelle
    if (stripos(trim($specialite), 'physique') !== false) {
        $specialite_display = 'Tronc Commun';
    } else {
        $specialite_display = $specialite;
    }

} else {
    // 3ème année et autres : spécialité réelle
    $specialite_display = $specialite;
}

?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Relevé de Notes – <?php echo htmlspecialchars(strtoupper($nom_etudiant) . ' ' . strtoupper($prenom_etudiant)); ?></title>
    <link rel="icon" type="image/png" sizes="16x16"
          href="../administrateur/<?php echo $_SESSION['logo_univ'] ?? ''; ?>">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }

        body {
            font-family: "Times New Roman", Times, serif;
            font-size: 11pt;
            color: #000;
            background: #fff;
        }

        /* ── Bouton impression ── */
        .no-print {
            text-align: center;
            padding: 10px;
            background: #f0f0f0;
            border-bottom: 1px solid #ccc;
        }
        .btn-print {
            background: #2c3e50;
            color: #fff;
            border: none;
            padding: 8px 28px;
            font-size: 13px;
            cursor: pointer;
            border-radius: 4px;
        }
        .btn-print:hover { background: #1a252f; }

        /* ── Barre d'édition ── */
        .edit-bar {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            padding: 8px 14px;
            background: #fff8e1;
            border-bottom: 1px solid #f0c040;
            font-size: 12px;
            font-family: Arial, sans-serif;
        }
        .edit-bar label { font-weight: bold; color: #555; }
        .edit-bar input {
            border: 1px solid #aaa;
            border-radius: 3px;
            padding: 3px 8px;
            font-size: 12px;
            width: 140px;
        }
        .btn-edit-annee {
            background: #e67e22;
            color: #fff;
            border: none;
            padding: 4px 14px;
            border-radius: 3px;
            font-size: 12px;
            cursor: pointer;
        }
        .btn-edit-annee:hover { background: #ca6f1e; }

        /* ── Page ── */
        .page {
            width: 21cm;
            min-height: 29.7cm;
            margin: 0 auto;
            padding: 1.5cm 2cm 2cm 2cm;
            background: #fff;
        }

        /* ── En-tête 3 colonnes ── */
        .header-row {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 4px;
        }
        .header-left  { flex: 1; text-align: left; }
        .header-center{ flex: 0 0 auto; text-align: center; padding: 0 14px; }
        .header-right { flex: 1; text-align: right; }

        .h-univ  { font-size: 11pt; font-weight: bold; text-transform: uppercase; margin-bottom: 2px; }
        .h-sub   { font-size: 9.5pt; margin-bottom: 2px; }
        .h-devise{ font-size: 11pt; font-weight: bold; }

        .header-line {
            border: none;
            border-top: 2px solid #000;
            margin: 6px 0 10px 0;
        }

        /* ── Titre ── */
        .titre-releve {
            text-align: center;
            font-size: 14pt;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 1px;
            margin-bottom: 10px;
        }

        /* ── Infos étudiant ── */
        .info-etudiant {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 12px;
            font-size: 10.5pt;
        }
        .info-etudiant td { padding: 2px 6px; vertical-align: top; }
        .info-etudiant td:first-child { font-weight: normal; white-space: nowrap; width: 220px; }
        .info-etudiant td:last-child  { font-weight: bold; }

        /* ── Tableau des notes ── */
        .table-notes {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 4px;
            font-size: 9.5pt;
        }
        .table-notes th,
        .table-notes td {
            border: 1px solid #000;
            padding: 4px 6px;
            text-align: center;
            vertical-align: middle;
        }
        .table-notes td.libelle,
        .table-notes th.libelle { text-align: left; }

        /* ── AMENDEMENT 6 : Code UE aligné à gauche ── */
        .table-notes td.code-ue {
            text-align: left;
            font-weight: bold;
        }

        /* Ligne récap semestre */
        .tr-recap td {
            background: #f0f0f0;
            font-weight: bold;
            font-size: 9pt;
        }

        /* ── Cellule "Semestre X" en rotation verticale ── */
        .sem-label {
            writing-mode: vertical-rl;
            transform: rotate(180deg);
            text-align: center;
            font-weight: bold;
            font-size: 9pt;
            white-space: nowrap;
            width: 22px;
            padding: 4px 2px;
            background: #fff;
            border: 1px solid #000;
        }

        /* ── Cellule "Crédits" en rotation verticale ── */
        .credit-label {
            writing-mode: vertical-rl;
            transform: rotate(180deg);
            text-align: center;
            font-weight: bold;
            font-size: 9pt;
            white-space: nowrap;
            width: 28px;
            padding: 4px 2px;
            background: #d9d9d9;
            border: 1px solid #000 !important;
        }
        /* Colonne crédits bien fermée */
.table-notes td.col-credit,
.table-notes th.credit-label {
    border-left: 1px solid #000 !important;
    border-right: 1px solid #000 !important;
}

.credit-label {
    writing-mode: vertical-rl;
    transform: rotate(180deg);
    border-left: 1px solid #000 !important;
    border-right: 1px solid #000 !important;
    border-top: 1px solid #000 !important;
    border-bottom: 1px solid #000 !important;
}

        /* ── AMENDEMENT 11 : Bloc signature ── */
        .signature-bloc {
            margin-top: 20px;
            margin-left: 52%;
            text-align: left;
            font-size: 11pt;
        }
        .signature-bloc p { margin-bottom: 6px; }

        /* ── Impression ── */
        @media print {
            @page { size: A4 portrait; margin: 12mm 15mm; }
            .no-print  { display: none !important; }
            .edit-bar  { display: none !important; }
            .page { width: 100%; margin: 0; padding: 0; min-height: auto; }
            body { font-size: 9.5pt; }
            .table-notes thead tr {
                background: #d9d9d9 !important;
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }
          
    .table-notes td.col-credit,
    .table-notes th.credit-label {
        border-left: 1px solid #000 !important;
        border-right: 1px solid #000 !important;
        border-top: 1px solid #000 !important;
        border-bottom: 1px solid #000 !important;
    }
.table-notes {
    border-collapse: collapse;
    border-spacing: 0;
}
            .tr-recap td {
                background: #f0f0f0 !important;
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }
            .credit-label {
                background: #d9d9d9 !important;
                border: 1px solid #000 !important;
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }
        }
    </style>
</head>
<body>

<!-- ── Barre impression ── -->
<div class="no-print">
    <button class="btn-print" onclick="window.print()">🖨️ Imprimer / Enregistrer en PDF</button>
</div>

<!-- ── Barre d'édition : année de première inscription ── -->
<div class="edit-bar no-print">
    <label for="inp-annee-premiere">✏️ Corriger l'année de première inscription :</label>
    <input type="text"
           id="inp-annee-premiere"
           placeholder="ex. 2023-2024"
           value="<?php echo htmlspecialchars($annee_premiere_inscription); ?>">
    <button class="btn-edit-annee" onclick="appliquerAnnee()">Appliquer</button>
    <small style="color:#888;">(Valeur système : <b><?php echo htmlspecialchars($annee_premiere_db); ?></b>)</small>
</div>

<!-- ── Barre d'édition : matricule ── -->
<div class="edit-bar no-print">
    <label for="inp-matricule">✏️ Corriger le matricule :</label>
    <input type="text"
           id="inp-matricule"
           placeholder="ex. L23-0042"
           value="<?php echo htmlspecialchars($matricule_display); ?>">
    <button class="btn-edit-annee" onclick="appliquerMatricule()">Appliquer</button>
    <small style="color:#888;">(Valeur système : <b><?php echo htmlspecialchars(strtoupper($code_etudiant)); ?></b>)</small>
</div>

<div class="page">

    <!-- EN-TÊTE -->
    <div class="header-row">
        <div class="header-left">
            <div class="h-univ">UNIVERSITE DENIS SASSOU-N'GUESSO</div>
            <div class="h-dir">VICE-PRESIDENCE</div>
            <div class="h-dir">Direction de la scolarité et des examens</div>
            <div class="h-serv">Service de la scolarité et des examens</div>
        </div>
        <div class="header-center">
            <img src="../images/univ.png" alt="Logo UDSN" style="max-height: 90px;">
        </div>
        <div class="header-right">
            <div class="h-devise">Rigueur – Excellence – Lumières</div>
        </div>
    </div>

    <!-- TITRE -->
    <div class="titre-releve">Relevé de Notes</div>

    <!-- INFOS ÉTUDIANT -->
    <table class="info-etudiant">
        <tr>
            <td>Nom (s) et Prénom (s)</td>
            <td>:&nbsp; <?php echo htmlspecialchars(strtoupper($nom_etudiant) . '  ' . ucwords(strtolower($prenom_etudiant))); ?></td>
        </tr>
        <tr>
            <td>Date et lieu de naissance</td>
            <td>:&nbsp;
                <?php
                    $date_fmt = (!empty($date_naissance)) ? date('d/m/Y', strtotime($date_naissance)) : $date_naissance;
                    echo htmlspecialchars($date_fmt . ' à ' . ucwords(strtolower($lieu_naissance)));
                ?>
            </td>
        </tr>
        <tr>
            <td>Etablissement</td>
           <td>:&nbsp; <?php echo htmlspecialchars(ucwords_utf8(strtolower($etab_libelle))); ?></td>
        </tr>
        <tr>
            <td>Numéro matricule</td>
            <td id="cell-matricule">:&nbsp; <b><?php echo htmlspecialchars($matricule_display); ?></b></td>
        </tr>
        <tr>
            <td>Parcours</td>
            <td>:&nbsp; <?php echo htmlspecialchars($parcours); ?></td>
        </tr>
   

<tr>
    <td>Spécialité</td>
   <td>:&nbsp; <?php echo htmlspecialchars($specialite_display); ?></td>
</tr>

        <tr>
            <td>Département</td>
            <td>:&nbsp; <?php echo htmlspecialchars($dept_display); ?></td>
        </tr>
        <tr>
            <td>Niveau</td>
            <td>:&nbsp; <?php echo htmlspecialchars($niveau_display); ?></td>
        </tr>
        <tr>
            <td>Année de la première inscription</td>
            <td id="cell-annee-premiere">:&nbsp; <b><?php echo htmlspecialchars($annee_premiere_inscription); ?></b></td>
        </tr>
    </table>

    <!-- ════════════════════════════════════════════════════════
         TABLEAU DES NOTES
    ════════════════════════════════════════════════════════ -->
    <?php
    $where_semestre = "";
    if (!empty($semestre_filtre)) {
        $semestre_esc   = mysqli_real_escape_string($connexion, $semestre_filtre);
        $where_semestre = " AND semestre = '$semestre_esc'";
    }

    $sql_semestres = "SELECT DISTINCT semestre FROM ue
                      WHERE niveau     = '$niveau'
                        AND specialite = '" . mysqli_real_escape_string($connexion, $specialite) . "'
                        AND etab       = '$etablissement'
                        $where_semestre
                      ORDER BY semestre";
    $res_semestres = $connexion->query($sql_semestres);

    while ($sem = $res_semestres->fetch_object()):
        $semestre_lib = $sem->semestre;

        // ── Moyennes & décisions depuis recap ──
        $recap_ord  = obtenirMoyenne1($connexion, $id_etudiant, $semestre_lib, 'ordinaire',  $annee, $etablissement);
        $recap_ratt = obtenirMoyenne1($connexion, $id_etudiant, $semestre_lib, 'rattrapage', $annee, $etablissement);

        $moy_ord  = $recap_ord  ? $recap_ord['moy']      : null;
        $dec_ord  = $recap_ord  ? $recap_ord['decision']  : null;
        $moy_ratt = $recap_ratt ? $recap_ratt['moy']      : null;
        $dec_ratt = $recap_ratt ? $recap_ratt['decision']  : null;

        if ($dec_ord === 'Validé') {
            $moy_finale = $moy_ord;   $dec_finale = $dec_ord;
        } elseif ($moy_ratt !== null) {
            $moy_finale = $moy_ratt;  $dec_finale = $dec_ratt;
        } else {
            $moy_finale = $moy_ord;   $dec_finale = $dec_ord;
        }

        $mention_finale = ($moy_finale !== null) ? mentionParmoyenne($moy_finale, 2) : '-';
        $moy_ord_fmt    = ($moy_ord  !== null && $moy_ord  !== '') ? number_format(floatval($moy_ord),  2, ',', '') : 'X';
        $moy_ratt_fmt   = ($moy_ratt !== null && $moy_ratt !== '') ? number_format(floatval($moy_ratt), 2, ',', '') : 'X';

        // ── Récupérer les UEs ──
        $sql_ue = "
            SELECT ue.code,
                   ue.libelle,
                   (SELECT SUM(e2.credit)
                    FROM ecue e2
                    WHERE e2.code_ue = ue.code) AS credit
            FROM ue
            JOIN vue_repartition ON ue.code = vue_repartition.ue
            WHERE vue_repartition.classe   = '$classe'
              AND ue.niveau                = '$niveau'
              AND vue_repartition.semestre = '" . mysqli_real_escape_string($connexion, $semestre_lib) . "'
              AND ue.etab                  = '$etablissement'
            GROUP BY ue.code, ue.libelle
        ";
        $res_ue  = $connexion->query($sql_ue);
        $nb_ues  = $res_ue ? $res_ue->num_rows : 0;

        $total_rows = 3 + $nb_ues + 1;
        if ($res_ue) $res_ue->data_seek(0);
    ?>

    <table class="table-notes" style="margin-bottom:10px;">
        <tbody>

            <!-- Ligne 1 : Semestre (rowspan) + Code + UE + Crédits + "Résultats obtenus" -->
            <tr>
                <th rowspan="<?php echo ($total_rows + 1); ?>" class="sem-label">
                    <?php echo htmlspecialchars($semestre_lib); ?>
                </th>
                <th rowspan="3" style="background:#d9d9d9;">Code</th>
                <th rowspan="3" class="libelle" style="background:#d9d9d9; text-align:center; vertical-align:middle; white-space:nowrap;">
                    Unité d'Enseignement (UE)
                </th>
                <th rowspan="3" class="credit-label">Crédits</th>
                <th colspan="2" style="background:#d9d9d9; font-size:8.5pt;">Résultats obtenus</th>
            </tr>

            <!-- Ligne 2 : labels session -->
            <tr>
                <th style="background:#d9d9d9; font-size:8pt;">Session<br>ordinaire</th>
                <th style="background:#d9d9d9; font-size:8pt;">Session de<br>rattrapage</th>
            </tr>

            <!-- Ligne 3 : labels notes -->
            <tr>
                <th style="background:#d9d9d9; font-size:6.5pt;">
                    <span style="white-space:nowrap;">NOTES C.c + Ex.T</span><br>sur 20
                </th>
                <th style="background:#d9d9d9; font-size:6.5pt;">
                    <span style="white-space:nowrap;">NOTES C.c + Ex.T</span><br>sur 20
                </th>
            </tr>

        <?php
        if ($res_ue && $nb_ues > 0):
            while ($ue = $res_ue->fetch_object()):
                $lib_ue = str_replace("+", "'", $ue->libelle);
                $credit = $ue->credit ?? '-';

                // ── Calcul : (CC+Ex)/2 par ECUE, puis moyenne des ECUEs ──
                $sql_ecues = "SELECT code_ecue FROM ecue
                              WHERE code_ue = '" . mysqli_real_escape_string($connexion, $ue->code) . "'";
                $res_ecues = $connexion->query($sql_ecues);
                $vals_ord  = [];
                $vals_ratt = [];

                while ($ecue = $res_ecues->fetch_object()) {
                    $cc  = getEtudiantCC($id_etudiant, $connexion, $etablissement, $semestre_lib, $ecue->code_ecue, $annee);
                    $ex  = getEtudiantEXT($id_etudiant, $connexion, $etablissement, $semestre_lib, $ecue->code_ecue, $annee);
                    $rat = getEtudiantRattrapage($id_etudiant, $connexion, $etablissement, $semestre_lib, $ecue->code_ecue, $annee);

                    if ($cc !== '-' && $ex  !== '-') $vals_ord[]  = ($cc + $ex)  / 2;
                    elseif ($cc !== '-')             $vals_ord[]  = floatval($cc);
                    elseif ($ex !== '-')             $vals_ord[]  = floatval($ex);

                    if ($cc !== '-' && $rat !== '-') $vals_ratt[] = ($cc + $rat) / 2;
                    elseif ($rat !== '-')            $vals_ratt[] = floatval($rat);
                }

                $note_ue_ord  = count($vals_ord)  > 0
                    ? number_format(array_sum($vals_ord)  / count($vals_ord),  2, ',', '') : 'X';
                $note_ue_ratt = count($vals_ratt) > 0
                    ? number_format(array_sum($vals_ratt) / count($vals_ratt), 2, ',', '') : 'X';

                if ($note_ue_ratt !== 'X') $note_ue_ord = 'X';
        ?>
            <tr>
                <td class="code-ue"><?php echo htmlspecialchars($ue->code); ?></td>
                <td class="libelle"><?php echo htmlspecialchars($lib_ue); ?></td>
           <td class="col-credit"><?php echo htmlspecialchars($credit); ?></td>
                <td><?php echo $note_ue_ord; ?></td>
                <td><?php echo $note_ue_ratt; ?></td>
            </tr>
        <?php endwhile; else: ?>
            <tr>
                <td colspan="5" style="text-align:center; font-style:italic;">
                    Aucune unité d'enseignement trouvée.
                </td>
            </tr>
        <?php endif; ?>

            <!-- Ligne récap semestre -->
            <tr class="tr-recap">
                <td colspan="4" style="text-align:left; font-size:8.5pt;">
                    Moyenne du <?php echo htmlspecialchars($semestre_lib); ?> :
                    <b>
                    <?php
                        if ($dec_ord === 'Validé')  echo $moy_ord_fmt;
                        elseif ($moy_ratt !== null) echo $moy_ratt_fmt;
                        else echo ($moy_finale !== null ? number_format(floatval($moy_finale), 2, ',', '') : '-');
                    ?>
                    </b>
                    &nbsp;&nbsp;&nbsp;
                    Décision du jury :
                    <b><?php echo htmlspecialchars($dec_finale ?? '-'); ?></b>
                    &nbsp;&nbsp;&nbsp;
                    Mention :
                    <b><?php echo htmlspecialchars($mention_finale); ?></b>
                </td>
                <td style="text-align:center; font-size:8pt; white-space:nowrap;">
                    Année académique<br><b><?php echo htmlspecialchars($annee); ?></b>
                </td>
            </tr>

        </tbody>
    </table>

    <?php endwhile; ?>

    <!-- ── Bloc signature ── -->
    <div class="signature-bloc">
        <p>Fait à Kintélé, le &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</p>
        <p>Le Directeur de la Scolarité et des Examens,</p>
        <br><br><br><br>
        <p><b>Professeur Cyr Jonas MORABANDZA.</b></p>
    </div>

</div><!-- /page -->

<script>
    // ── Appliquer l'année de première inscription ──
    function appliquerAnnee() {
        var val = document.getElementById('inp-annee-premiere').value.trim();
        if (!val) { alert('Veuillez saisir une année valide.'); return; }
        var url = new URL(window.location.href);
        url.searchParams.set('annee_premiere', val);
        window.location.href = url.toString();
    }
    document.getElementById('inp-annee-premiere').addEventListener('keydown', function(e) {
        if (e.key === 'Enter') appliquerAnnee();
    });

    // ── Appliquer le matricule ──
    function appliquerMatricule() {
        var val = document.getElementById('inp-matricule').value.trim();
        if (!val) { alert('Veuillez saisir un matricule valide.'); return; }
        var url = new URL(window.location.href);
        url.searchParams.set('matricule_override', val);
        window.location.href = url.toString();
    }
    document.getElementById('inp-matricule').addEventListener('keydown', function(e) {
        if (e.key === 'Enter') appliquerMatricule();
    });

    // ── Auto-print ──
    <?php if (!isset($_GET['no_autoprint'])): ?>
    window.addEventListener('load', function () {
        setTimeout(function () { window.print(); }, 400);
    });
    <?php endif; ?>
</script>

</body>
</html>
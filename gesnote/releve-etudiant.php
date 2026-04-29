<?php
/**
 * releve_notes.php  –  VERSION MODIFIÉE
 * Changements :
 *  1. Colonne "NOTES C.c+Ex.T" affiche le résultat calculé (CC+Ex)/2 au lieu de "CC + Ex"
 *  2. Impression limitée à UN seul semestre (passé via $_GET['semestre'])
 */

include '../php/connexion.php';
include '../php/lib.php';
session_start();

error_reporting(E_ALL);
ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');

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

// ── Récupération du semestre sélectionné (NOUVEAU) ──
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

// ── Récupérer la série BAC et l'année BAC ──
$sql_bac  = "SELECT bac, anneebac FROM candidat WHERE code = ?";
$stmt_bac = $connexion->prepare($sql_bac);
$stmt_bac->bind_param("s", $code_etudiant);
$stmt_bac->execute();
$res_bac = $stmt_bac->get_result()->fetch_assoc();
$stmt_bac->close();
$serie_bac = $res_bac['bac']      ?? '-';
$annee_bac = $res_bac['anneebac'] ?? '-';

// ── Récupérer l'année de première inscription ──
$sql_premiere = "SELECT MIN(annee) AS premiere FROM inscription WHERE candidat = ? AND etab = ?";
$stmt_pre     = $connexion->prepare($sql_premiere);
$stmt_pre->bind_param("ss", $code_etudiant, $etablissement);
$stmt_pre->execute();
$res_pre    = $stmt_pre->get_result()->fetch_assoc();
$stmt_pre->close();
$annee_premiere_inscription = $res_pre['premiere'] ?? $annee;

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
        .table-notes thead tr {
            background: #d9d9d9;
            font-weight: bold;
        }
        .table-notes td.libelle,
        .table-notes th.libelle { text-align: left; }

        /* Ligne récap semestre */
        .tr-recap td {
            background: #f0f0f0;
            font-weight: bold;
            font-size: 9pt;
        }

        /* ── Titre de semestre ── */
        .semestre-titre {
            font-weight: bold;
            font-size: 11pt;
            text-decoration: underline;
            text-transform: uppercase;
            margin: 10px 0 4px 0;
        }

        /* ── Signature ── */
        .signature-bloc {
            margin-top: 20px;
            text-align: right;
            font-size: 11pt;
        }
        .signature-bloc p { margin-bottom: 6px; }

        /* ── Impression ── */
        @media print {
            @page { size: A4 portrait; margin: 12mm 15mm; }
            .no-print { display: none !important; }
            .page { width: 100%; margin: 0; padding: 0; min-height: auto; }
            body { font-size: 9.5pt; }
            .table-notes thead tr {
                background: #d9d9d9 !important;
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }
            .tr-recap td {
                background: #f0f0f0 !important;
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }
        }
    </style>
</head>
<body>

<div class="no-print">
    <button class="btn-print" onclick="window.print()">🖨️ Imprimer / Enregistrer en PDF</button>
</div>

<div class="page">

    <!-- EN-TÊTE -->
    <div class="header-row">
        <div class="header-left">
            <div class="h-univ"><?php echo htmlspecialchars($univ); ?></div>
            <div class="h-sub">PRESIDENCE</div>
            <div class="h-sub">VICE-PRESIDENCE</div>
            <div class="h-sub">DIRECTION DE LA SCOLARITE ET DES EXAMENS</div>
            <div class="h-sub">Service de la Scolarité et des Examens</div>
        </div>
        <div class="header-center">
            <img src="../images/univ.png" alt="Logo UDSN" style="max-height: 90px;">
        </div>
        <div class="header-right">
            <div class="h-devise">Rigueur – Excellence – Lumières</div>
        </div>
    </div>
    <hr class="header-line">

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
            <td>:&nbsp; <?php echo htmlspecialchars($etab_libelle); ?></td>
        </tr>
        <tr>
            <td>Numéro matricule</td>
            <td>:&nbsp; <?php echo htmlspecialchars(strtoupper($code_etudiant)); ?></td>
        </tr>
        <tr>
            <td>Parcours</td>
            <td>:&nbsp; <?php echo htmlspecialchars($parcours); ?></td>
        </tr>
        <tr>
            <td>Spécialité</td>
            <td>:&nbsp; <?php echo htmlspecialchars($specialite); ?></td>
        </tr>
        <tr>
            <td>Département</td>
            <td>:&nbsp; <?php echo htmlspecialchars($niveau); ?></td>
        </tr>
        <tr>
            <td>Année de la première inscription</td>
            <td>:&nbsp; <?php echo htmlspecialchars($annee_premiere_inscription); ?></td>
        </tr>
    </table>

    <!-- ════════════════════════════════════════
         TABLEAU DES NOTES
         MODIFIÉ :
           - filtre sur $semestre_filtre (1 seul semestre si passé en GET)
           - colonne "NOTES C.c+Ex.T" affiche (CC+Ex)/2 directement
    ═════════════════════════════════════════ -->
    <?php
    // Construction de la clause WHERE pour le filtre semestre
    $where_semestre = "";
    if (!empty($semestre_filtre)) {
        $semestre_esc = mysqli_real_escape_string($connexion, $semestre_filtre);
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

        // Priorité ordinaire → rattrapage
        $recap_ord  = obtenirMoyenne1($connexion, $id_etudiant, $semestre_lib, 'ordinaire',  $annee, $etablissement);
        $recap_ratt = obtenirMoyenne1($connexion, $id_etudiant, $semestre_lib, 'rattrapage', $annee, $etablissement);

        $moy_ord  = $recap_ord  ? $recap_ord['moy']     : null;
        $dec_ord  = $recap_ord  ? $recap_ord['decision'] : null;
        $moy_ratt = $recap_ratt ? $recap_ratt['moy']     : null;
        $dec_ratt = $recap_ratt ? $recap_ratt['decision'] : null;

        if ($dec_ord === 'Validé') {
            $moy_finale = $moy_ord;
            $dec_finale = $dec_ord;
        } elseif ($moy_ratt !== null) {
            $moy_finale = $moy_ratt;
            $dec_finale = $dec_ratt;
        } else {
            $moy_finale = $moy_ord;
            $dec_finale = $dec_ord;
        }

        $mention_finale = ($moy_finale !== null) ? mentionParmoyenne($moy_finale, 2) : '-';
        $moy_ord_fmt  = ($moy_ord  !== null && $moy_ord  !== '') ? number_format(floatval($moy_ord),  2, ',', '') : 'X';
        $moy_ratt_fmt = ($moy_ratt !== null && $moy_ratt !== '') ? number_format(floatval($moy_ratt), 2, ',', '') : 'X';

        // UEs du semestre
        $sql_ue = "
            SELECT ue.code, ue.libelle, SUM(ecue.credit) AS credit
            FROM ue
            JOIN ecue ON ue.code = ecue.code_ue
            JOIN vue_repartition ON ue.code = vue_repartition.ue
            WHERE classe = '$classe'
              AND ue.niveau = '$niveau'
              AND vue_repartition.semestre = '" . mysqli_real_escape_string($connexion, $semestre_lib) . "'
              AND ue.etab = '$etablissement'
            GROUP BY ue.code, ue.libelle
        ";
        $res_ue = $connexion->query($sql_ue);
    ?>

    <div class="semestre-titre"><?php echo htmlspecialchars(strtoupper($semestre_lib)); ?></div>

    <table class="table-notes">
        <thead>
            <tr>
                <th rowspan="2">Code</th>
                <th rowspan="2" class="libelle">Unité d'Enseignement (UE)</th>
                <th rowspan="2">Crédits</th>
                <th colspan="2" style="border-bottom:1px solid #999;">
                    Résultats obtenus<br>session ordinaire
                </th>
                <th colspan="2" style="border-bottom:1px solid #999;">
                    Résultats obtenus<br>session de rattrapage
                </th>
            </tr>
            <tr>
                <th style="font-size:8.5pt;">NOTES C.c + Ex.T<br>sur 20</th>
                <th style="font-size:8.5pt;">MOY.<br>UE</th>
                <th style="font-size:8.5pt;">NOTES C.c + Ex.T<br>sur 20</th>
                <th style="font-size:8.5pt;">MOY.<br>UE</th>
            </tr>
        </thead>
        <tbody>
        <?php
        if ($res_ue && $res_ue->num_rows > 0):
            while ($ue = $res_ue->fetch_object()):
                $lib_ue = str_replace("+", "'", $ue->libelle);
                $credit = $ue->credit ?? '-';

                // ── Moy UE session ordinaire (existant) ──
                $moy_ue_ord = getMoyenneUE($connexion, $id_etudiant, $semestre_lib, $annee, $ue->code, $etablissement);
                $moy_ue_ord_fmt = ($moy_ue_ord !== null && $moy_ue_ord !== '')
                    ? number_format(floatval($moy_ue_ord), 2, ',', '')
                    : 'X';

                // ── Moy UE session rattrapage ──
                $sql_ecue_ratt = "SELECT * FROM ecue WHERE code_ue = '" . mysqli_real_escape_string($connexion, $ue->code) . "'";
                $res_ecue_ratt = $connexion->query($sql_ecue_ratt);
                $moyennes_ratt = [];
                while ($ecue_r = $res_ecue_ratt->fetch_object()) {
                    $cc_r  = getEtudiantCC($id_etudiant, $connexion, $etablissement, $semestre_lib, $ecue_r->code_ecue, $annee);
                    $ext_r = getEtudiantRattrapage($id_etudiant, $connexion, $etablissement, $semestre_lib, $ecue_r->code_ecue, $annee);
                    if ($cc_r !== '-' && $ext_r !== '-') {
                        $moyennes_ratt[] = round(($cc_r + $ext_r) / 2, 2);
                    }
                }
                $moy_ue_ratt = (count($moyennes_ratt) > 0)
                    ? number_format(array_sum($moyennes_ratt) / count($moyennes_ratt), 2, ',', '')
                    : 'X';

                // ══════════════════════════════════════════════════════
                // MODIFICATION : calcul direct (CC + Ex) / 2
                //   → affiche le résultat numérique au lieu de "CC + Ex"
                // ══════════════════════════════════════════════════════

                // Notes ordinaires calculées
                $sql_ecue_ord = "SELECT * FROM ecue WHERE code_ue = '" . mysqli_real_escape_string($connexion, $ue->code) . "'";
                $res_ecue_ord = $connexion->query($sql_ecue_ord);
                $notes_ord_values = [];
                while ($ecue2 = $res_ecue_ord->fetch_object()) {
                    $cc2  = getEtudiantCC($id_etudiant, $connexion, $etablissement, $semestre_lib, $ecue2->code_ecue, $annee);
                    $ext2 = getEtudiantEXT($id_etudiant, $connexion, $etablissement, $semestre_lib, $ecue2->code_ecue, $annee);
                    if ($cc2 !== '-' && $ext2 !== '-') {
                        // Calcul (CC + Ex) / 2 et formatage
                        $notes_ord_values[] = number_format(round(($cc2 + $ext2) / 2, 2), 2, ',', '');
                    } elseif ($cc2 !== '-') {
                        $notes_ord_values[] = number_format(floatval($cc2), 2, ',', '');
                    } elseif ($ext2 !== '-') {
                        $notes_ord_values[] = number_format(floatval($ext2), 2, ',', '');
                    }
                }
                $notes_ord_str = !empty($notes_ord_values) ? implode('<br>', $notes_ord_values) : 'X';

                // Notes rattrapage calculées
                $sql_ecue_r2 = "SELECT * FROM ecue WHERE code_ue = '" . mysqli_real_escape_string($connexion, $ue->code) . "'";
                $res_ecue_r2 = $connexion->query($sql_ecue_r2);
                $notes_ratt_values = [];
                while ($ecue3 = $res_ecue_r2->fetch_object()) {
                    $cc3  = getEtudiantCC($id_etudiant, $connexion, $etablissement, $semestre_lib, $ecue3->code_ecue, $annee);
                    $ext3 = getEtudiantRattrapage($id_etudiant, $connexion, $etablissement, $semestre_lib, $ecue3->code_ecue, $annee);
                    if ($cc3 !== '-' && $ext3 !== '-') {
                        $notes_ratt_values[] = number_format(round(($cc3 + $ext3) / 2, 2), 2, ',', '');
                    } elseif ($cc3 !== '-') {
                        $notes_ratt_values[] = number_format(floatval($cc3), 2, ',', '');
                    } elseif ($ext3 !== '-') {
                        $notes_ratt_values[] = number_format(floatval($ext3), 2, ',', '');
                    }
                }
                $notes_ratt_str = !empty($notes_ratt_values) ? implode('<br>', $notes_ratt_values) : 'X';
        ?>
            <tr>
                <td><b><?php echo htmlspecialchars($ue->code); ?></b></td>
                <td class="libelle"><?php echo htmlspecialchars($lib_ue); ?></td>
                <td><?php echo htmlspecialchars($credit); ?></td>
                <td><?php echo $notes_ord_str; ?></td>
                <td><?php echo $moy_ue_ord_fmt; ?></td>
                <td><?php echo $notes_ratt_str; ?></td>
                <td><?php echo $moy_ue_ratt; ?></td>
            </tr>
        <?php endwhile; ?>
        <?php else: ?>
            <tr>
                <td colspan="7" style="text-align:center; font-style:italic;">
                    Aucune unité d'enseignement trouvée pour ce semestre.
                </td>
            </tr>
        <?php endif; ?>

            <!-- Ligne récapitulative du semestre -->
            <tr class="tr-recap">
                <td colspan="2" style="text-align:left;">
                    Décision du jury :
                    <b><?php echo htmlspecialchars($dec_finale ?? '-'); ?></b>
                </td>
                <td colspan="3">
                    Moyenne du <?php echo htmlspecialchars($semestre_lib); ?> :
                    <b>
                    <?php
                        if ($dec_ord === 'Validé') {
                            echo $moy_ord_fmt;
                        } elseif ($moy_ratt !== null) {
                            echo $moy_ratt_fmt;
                        } else {
                            echo ($moy_finale !== null ? number_format(floatval($moy_finale), 2, ',', '') : '-');
                        }
                    ?>
                    </b>
                </td>
                <td colspan="2">
                    Mention : <b><?php echo htmlspecialchars($mention_finale); ?></b>
                </td>
            </tr>
            <tr class="tr-recap">
                <td colspan="5" style="text-align:left;">
                    Moy. ordinaire : <b><?php echo $moy_ord_fmt; ?></b>
                    &nbsp;&nbsp;&nbsp;
                    Moy. rattrapage : <b><?php echo $moy_ratt_fmt; ?></b>
                </td>
                <td colspan="2">
                    Année académique <b><?php echo htmlspecialchars($annee); ?></b>
                </td>
            </tr>
        </tbody>
    </table>

    <?php endwhile; ?>

    <!-- SIGNATURE -->
    <div class="signature-bloc">
        <p>Fait à Kintélé, le &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</p>
        <p>Le Directeur de la Scolarité et des Examens,</p>
        <br><br>
        <p><b>Professeur Cyr Jonas MORABANDZA.</b></p>
    </div>

</div><!-- /page -->

<script>
    window.addEventListener('load', function () {
        setTimeout(function () { window.print(); }, 400);
    });
</script>

</body>
</html>
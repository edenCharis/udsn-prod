<?php
include '../php/connexion.php';
include '../php/lib.php';
session_start();

if (!($_SESSION['id'] == session_id() && $_SESSION['role'] == "scolarité")) {
    header("location: ../login");
    exit;
}

if (!isset($_GET['semestre']) || !isset($_GET['specialite']) || !isset($_GET['annee']) || !isset($_GET['examen']) || !isset($_GET['classe'])) {
    header("location: pvd");
    exit;
}

$semestre    = urldecode($_GET["semestre"]);
$specialite  = $_GET["specialite"];
$annee       = urldecode($_GET["annee"]);
$examen      = urldecode($_GET["examen"]);
$niveau      = NiveauParSemestre($semestre);
$classe      = urldecode($_GET["classe"]);
$parcours    = getParcours($specialite, $connexion);

$semestre    = mysqli_real_escape_string($connexion, $semestre);
$specialite  = str_replace("'", "+", $specialite);
$annee       = mysqli_real_escape_string($connexion, $annee);
$examen      = mysqli_real_escape_string($connexion, $examen);
$niveau      = mysqli_real_escape_string($connexion, $niveau);
$classe      = mysqli_real_escape_string($connexion, $classe);
$parcours    = mysqli_real_escape_string($connexion, ($parcours ?? ''));

$etablissement         = $_SESSION['etablissement'];
$etablissement_libelle = $_SESSION['lib_etab'];
$etablissement         = mysqli_real_escape_string($connexion, $etablissement);

// ================================================================
// HELPER : détermine si un étudiant doit passer en rattrapage
// ================================================================
function etudiantDoitRattraper($etudiant_id, $semestre, $annee, $etablissement, $connexion) {
    $sql = "SELECT etudiant
            FROM recap
            WHERE etudiant = ?
              AND semestre = ?
              AND annee    = ?
              AND etab     = ?
              AND examen   = 'ordinaire'
              AND decision <> 'Validé'
            LIMIT 1";
    $stmt = $connexion->prepare($sql);
    $stmt->bind_param("isss", $etudiant_id, $semestre, $annee, $etablissement);
    $stmt->execute();
    $stmt->store_result();
    $doit_rattraper = ($stmt->num_rows > 0);
    $stmt->close();
    return $doit_rattraper;
}

// Requête UE — réutilisée plusieurs fois
$sql_ue = "SELECT DISTINCT ue.code, libelle
           FROM ue
           WHERE ue.etab   = '$etablissement'
             AND specialite = '$specialite'
             AND semestre   = '$semestre'
             AND niveau     = '$niveau'";

// Compter le nombre total d'UE
$result_ue_count_total = $connexion->query($sql_ue);
$nb_ue_total = $result_ue_count_total->num_rows;
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $_SESSION['univ']; ?> - Scolarité de <?php echo $_SESSION['etablissement']; ?></title>
    <style>
        th, td {
            border: 1px solid black;
            padding: 8px;
            text-align: center;
        }
        table {
            border-collapse: collapse;
            width: 100%;
        }
        .small-column { width: 40px; }
        th { background-color: #f2f2f2; }

        /* ═══════════════════════════════════════
           LÉGENDE DE COULEURS
        ═══════════════════════════════════════ */
        .legende-container {
            display: flex; flex-wrap: wrap; gap: 12px; align-items: center;
            padding: 12px 16px; background: #fff;
            border: 1px solid #dee2e6; border-radius: 6px; margin-bottom: 16px;
        }
        .legende-titre { font-weight: 700; font-size: 13px; color: #343a40; margin-right: 8px; }
        .legende-item  { display: flex; align-items: center; gap: 7px; font-size: 12px; color: #495057; }
        .legende-puce  { width: 22px; height: 22px; border-radius: 4px; border: 1px solid rgba(0,0,0,0.12); flex-shrink: 0; }
        .legende-puce.acquise      { background-color: #e8f5e9; }
        .legende-puce.rattrapage   { background-color: #fff3e0; }
        .legende-puce.eliminatoire { background-color: #fdecea; }
        .legende-puce.normale      { background-color: #f2f2f2; }

        /* Styles ECUE */
        .ecue-acquise      { background-color: #e8f5e9; }
        .ecue-rattrapage   { background-color: #fff3e0; }
        .ecue-eliminatoire { background-color: #fdecea; }

        @media print {
            @page { size: A4 portrait; margin: 8mm; }
            body * { visibility: hidden; }
            #printable-area, #printable-area * { visibility: visible; }
            #printable-area { position: absolute; left: 0; top: 0; width: 100%; }
            .no-print { display: none !important; }
            table  { font-size: 7px !important; }
            th, td { padding: 2px !important; font-size: 7px !important; }
            th     { font-size: 6px !important; font-weight: bold; }
            .small-column { width: 25px !important; }
            .ecue-acquise      { background-color: #e8f5e9 !important; -webkit-print-color-adjust: exact; print-color-adjust: exact; }
            .ecue-rattrapage   { background-color: #fff3e0 !important; -webkit-print-color-adjust: exact; print-color-adjust: exact; }
            .ecue-eliminatoire { background-color: #fdecea !important; -webkit-print-color-adjust: exact; print-color-adjust: exact; }
        }
    </style>
    <link rel="stylesheet" href="../vendor/bootstrap-select/dist/css/bootstrap-select.min.css">
    <link rel="stylesheet" href="../css/style.css">
    <link rel="stylesheet" href="../css/skin.css">
    <link href="../vendor/datatables/css/jquery.dataTables.min.css" rel="stylesheet">
</head>
<body>

<div id="main-wrapper">

<?php include "header.php"; ?>
<?php include 'nav.html'; ?>

<div class="content-body">
    <div class="container-fluid">

        <div class="row page-titles mx-0">
            <div class="col-sm-6 p-md-0">
                <div class="welcome-text">
                    <h4 class="card-title">Procès verbal de délibération</h4>
                    <button type="button" class="btn btn-info mt-2 no-print" onclick="window.print()">
                        <i class="fa fa-print"></i> Imprimer
                    </button>
                    <button type="button" class="btn btn-primary mt-2 ml-2 no-print" id="btnPublier">
                        <i class="fa fa-paper-plane"></i> Publier les résultats
                    </button>
                </div>
            </div>
            <div class="col-sm-6 p-md-0 justify-content-sm-end mt-2 mt-sm-0 d-flex">
                <ol class="breadcrumb no-print">
                    <li class="breadcrumb-item"><a href="index">Tableau de Bord</a></li>
                    <li class="breadcrumb-item"><a href="pvd">PVD</a></li>
                </ol>
            </div>
        </div>

        <div id="printable-area">
            <div class="row">
                <div class="col-lg-12">
                    <div class="row tab-content">
                        <div id="list-view" class="tab-pane fade active show col-lg-12">
                            <div class="card">
                                <div class="card-header">
                                    <h4 class="card-title">Spécialité : <?php echo str_replace("+", "'", $specialite); ?></h4>
                                    <h4 class="card-title">Semestre : <?php echo ucfirst($semestre); ?></h4>
                                    <h4 class="card-title">Année universitaire : <?php echo $annee; ?></h4>
                                    <h4 class="card-title">Type de session : <?php echo ($examen == 'ordinaire') ? 'Session Ordinaire' : 'Session de Rattrapage'; ?></h4>
                                    <h4 class="card-title">Classe : <?php echo $classe; ?></h4>
                                </div>
                                <div class="card-body">
                                    <!-- LÉGENDE -->
                                    <div class="legende-container">
                                        <span class="legende-titre"><i class="fa fa-paint-brush"></i> Légende :</span>
                                        <div class="legende-item">
                                            <div class="legende-puce normale"></div>
                                            <span>ECUE ordinaire (note saisie)</span>
                                        </div>
                                        <?php if ($examen == "rattrapage") { ?>
                                        <div class="legende-item">
                                            <div class="legende-puce acquise"></div>
                                            <span>ECUE acquise — moy. ECUE ≥ 10</span>
                                        </div>
                                        <div class="legende-item">
                                            <div class="legende-puce rattrapage"></div>
                                            <span>ECUE en rattrapage — moy. ECUE &lt; 10</span>
                                        </div>
                                        <?php } ?>
                                        <div class="legende-item">
                                            <div class="legende-puce eliminatoire"></div>
                                            <span>Note éliminatoire — moy. ECUE &lt; 6</span>
                                        </div>
                                    </div>
                                    <!-- FIN LÉGENDE -->

                                    <div class="table-responsive">
                                    <table id="example3" class="display">
                                        <thead>
                                            <!-- LIGNE 1 : Nom + UE + colonnes finales -->
                                            <tr>
                                                <th rowspan="3">N°</th>
                                                <th rowspan="3" data-type="nom">Nom(s) et prénom(s)</th>

                                                <?php
                                                $result_ue = $connexion->query($sql_ue);
                                                $ue_index  = 0;
                                                while ($data = $result_ue->fetch_object()) {
                                                    $ue_index++;
                                                    $sql_ecue    = "SELECT * FROM ecue WHERE code_ue='" . $data->code . "'";
                                                    $result_ecue = $connexion->query($sql_ecue);
                                                    $nb_ecue     = $result_ecue->num_rows;
                                                    $colspan = ($nb_ecue > 0) ? ($nb_ecue * 3) + 1 : 1;
                                                ?>
                                                <th colspan="<?php echo $colspan; ?>"
                                                    data-type="ue"
                                                    data-ue-index="<?php echo $ue_index; ?>">
                                                    <?php echo str_replace("+", "'", $data->libelle); ?>
                                                </th>
                                                <?php } ?>

                                                <th rowspan="3" data-type="final">UE validées sur <?php echo $nb_ue_total; ?></th>
                                                <th rowspan="3" data-type="final">Moyenne Générale</th>
                                                <th rowspan="3" data-type="final">Appréciation</th>
                                                <th rowspan="3" data-type="final">Décision du jury</th>
                                            </tr>

                                            <!-- LIGNE 2 : libellés ECUE -->
                                            <tr>
                                            <?php
                                            $result_ue = $connexion->query($sql_ue);
                                            $ue_index  = 0;
                                            while ($data = $result_ue->fetch_object()) {
                                                $ue_index++;
                                                $sql_ecue    = "SELECT * FROM ecue WHERE code_ue='" . $data->code . "'";
                                                $result_ecue = $connexion->query($sql_ecue);
                                                $nb_ecue     = $result_ecue->num_rows;
                                                $i = 0;
                                                while ($ecue = $result_ecue->fetch_object()) {
                                                    $i++;
                                            ?>
                                                <th colspan="3" data-type="ue" data-ue-index="<?php echo $ue_index; ?>">
                                                    <?php echo str_replace("+", "'", $ecue->libelle); ?>
                                                </th>
                                                <?php if ($i == $nb_ecue) { ?>
                                                <th rowspan="2" data-type="ue" data-ue-index="<?php echo $ue_index; ?>">
                                                    Moy UE
                                                </th>
                                                <?php } } } ?>
                                            </tr>

                                            <!-- LIGNE 3 : CC / EX.T / MOY ECUE -->
                                            <tr>
                                            <?php
                                            $result_ue = $connexion->query($sql_ue);
                                            $ue_index  = 0;
                                            while ($data = $result_ue->fetch_object()) {
                                                $ue_index++;
                                                $sql_ecue    = "SELECT * FROM ecue WHERE code_ue='" . $data->code . "'";
                                                $result_ecue = $connexion->query($sql_ecue);
                                                while ($ecue = $result_ecue->fetch_object()) {
                                            ?>
                                                <th class="small-column" data-type="ue" data-ue-index="<?php echo $ue_index; ?>">CC</th>
                                                <th class="small-column" data-type="ue" data-ue-index="<?php echo $ue_index; ?>">EX.T</th>
                                                <th class="small-column" data-type="ue" data-ue-index="<?php echo $ue_index; ?>">MOY. ECUE</th>
                                            <?php } } ?>
                                            </tr>
                                        </thead>
                                        <tbody>
                                        <?php
                                        $sql_etudiants = "SELECT
                                                             inscription.id,
                                                             candidat.code AS candidat,
                                                             candidat.nom  AS nom,
                                                             candidat.prenom AS prenom
                                                         FROM inscription
                                                         JOIN candidat   ON candidat.code      = inscription.candidat
                                                         JOIN classe     ON inscription.classe  = classe.libelle
                                                         JOIN specialite ON classe.specialite   = specialite.libelle
                                                         JOIN parcours   ON specialite.parcours = parcours.libelle
                                                         WHERE classe.libelle   = '$classe'
                                                           AND parcours.libelle = '$parcours'
                                                           AND inscription.annee = '$annee'
                                                         GROUP BY candidat.nom, candidat.prenom
                                                         ORDER BY LOWER(nom), LOWER(prenom)";

                                        $r   = $connexion->query($sql_etudiants);
                                        $num = 0;

                                        while ($etudiant = $r->fetch_object()) {

                                            // ── FILTRE RATTRAPAGE ──────────────────────────────────────
                                            if ($examen == "rattrapage") {
                                                if (!etudiantDoitRattraper(
                                                        $etudiant->id, $semestre,
                                                        $annee, $etablissement, $connexion
                                                    )) {
                                                    continue;
                                                }
                                            }

                                            $num++;

                                            // Réinitialisation pour CHAQUE étudiant
                                            $toutes_moyennes_ue          = [];
                                            $ue_validees_count           = 0;
                                            $a_note_eliminatoire_globale = false;
                                        ?>
                                        <tr>
                                            <th><?php echo $num; ?></th>
                                            <th data-type="nom">
                                            <?php
                                            echo mettrePremieresLettresMajuscules(
                                                getNomEtudiant(
                                                    getCandidatCodeByInscription($etudiant->id, $connexion),
                                                    $connexion,
                                                    $etablissement_libelle
                                                ) . "  " .
                                                getPrenomEtudiant(
                                                    getCandidatCodeByInscription($etudiant->id, $connexion),
                                                    $connexion,
                                                    $etablissement_libelle
                                                )
                                            );
                                            ?>
                                            </th>

                                            <?php
                                            $result_ue = $connexion->query($sql_ue);
                                            $ue_index  = 0;

                                            while ($data = $result_ue->fetch_object()) {
                                                $ue_index++;
                                                $sql_ecue    = "SELECT * FROM ecue WHERE code_ue='" . $data->code . "'";
                                                $result_ecue = $connexion->query($sql_ecue);
                                                $nb_ecue     = $result_ecue->num_rows;
                                                $i = 0;

                                                $moyennes_ecue_ue       = [];
                                                $a_note_eliminatoire_ue = false;

                                                while ($ecue = $result_ecue->fetch_object()) {
                                                    $i++;

                                                    // Note CC
                                                    $a = getEtudiantCC(
                                                        $etudiant->id, $connexion,
                                                        $etablissement, $semestre,
                                                        $ecue->code_ecue, $annee
                                                    );

                                                    // Note EX (ordinaire ou rattrapage avec fallback)
                                                    if ($examen == "rattrapage") {
                                                        $note_ratt = getEtudiantRattrapage(
                                                            $etudiant->id, $connexion,
                                                            $etablissement, $semestre,
                                                            $ecue->code_ecue, $annee
                                                        );
                                                        $b = ($note_ratt !== "-" && $note_ratt !== null && $note_ratt !== "")
                                                            ? $note_ratt
                                                            : getEtudiantEXT(
                                                                $etudiant->id, $connexion,
                                                                $etablissement, $semestre,
                                                                $ecue->code_ecue, $annee
                                                              );
                                                    } else {
                                                        $b = getEtudiantEXT(
                                                            $etudiant->id, $connexion,
                                                            $etablissement, $semestre,
                                                            $ecue->code_ecue, $annee
                                                        );
                                                    }

                                                    // Calcul moyenne ECUE
                                                    $moy_ecue = 0;
                                                    $has_data = ($a !== "-" && $a !== null && $a !== ""
                                                              && $b !== "-" && $b !== null && $b !== "");

                                                    if ($has_data) {
                                                        $moy_ecue = round(($a + $b) / 2, 2);
                                                        $moyennes_ecue_ue[] = $moy_ecue;
                                                    }

                                                    // Couleur de la cellule selon le résultat
                                                    $class_cc = 'small-column'; // CC toujours en noir
                                                    $class_ex = 'small-column'; // EX toujours en noir
                                                    if ($has_data && $moy_ecue < 6) {
                                                        $a_note_eliminatoire_ue      = true;
                                                        $a_note_eliminatoire_globale = true;
                                                        $class_cell = 'ecue-eliminatoire';
                                                        $class_moy  = 'small-column text-danger font-weight-bold';
                                                    } elseif ($has_data && $moy_ecue >= 10) {
                                                        $class_cell = 'ecue-acquise';
                                                        $class_moy  = 'small-column text-success font-weight-bold';
                                                    } elseif ($has_data) {
                                                        $class_cell = 'ecue-rattrapage';
                                                        $class_moy  = 'small-column text-warning font-weight-bold';
                                                    } else {
                                                        $class_cell = '';
                                                        $class_moy  = 'small-column';
                                                    }
                                                    ?>

                                                    <th class="<?php echo $class_cc; ?>"
                                                        data-type="ue" data-ue-index="<?php echo $ue_index; ?>">
                                                        <?php echo ($a !== "-" && $a !== null && $a !== "") ? $a : "-"; ?>
                                                    </th>

                                                    <th class="<?php echo $class_ex; ?>"
                                                        data-type="ue" data-ue-index="<?php echo $ue_index; ?>">
                                                        <?php echo ($b !== "-" && $b !== null && $b !== "") ? $b : "-"; ?>
                                                    </th>

                                                    <th class="<?php echo $class_moy; ?>"
                                                        data-type="ue" data-ue-index="<?php echo $ue_index; ?>">
                                                        <?php echo $has_data ? $moy_ecue : "-"; ?>
                                                    </th>

                                                <?php
                                                    // Fin de la dernière ECUE : calculer moy UE
                                                    if ($i == $nb_ecue) {
                                                        $ue_moy = "-";
                                                        if (count($moyennes_ecue_ue) > 0) {
                                                            $ue_moy = round(
                                                                array_sum($moyennes_ecue_ue) / count($moyennes_ecue_ue),
                                                                2
                                                            );
                                                            if ($ue_moy >= 10 && !$a_note_eliminatoire_ue) {
                                                                $ue_validees_count++;
                                                            }
                                                            $toutes_moyennes_ue[] = $ue_moy;
                                                        }
                                                ?>
                                                    <th class="small-column text-secondary"
                                                        data-type="ue" data-ue-index="<?php echo $ue_index; ?>">
                                                        <?php echo $ue_moy; ?>
                                                    </th>
                                                <?php } // fin if dernière ECUE ?>
                                                <?php } // fin while ECUE ?>
                                            <?php } // fin while UE ?>

                                            <!-- UE validées -->
                                            <th class="small-column" data-type="final">
                                                <?php echo $ue_validees_count; ?>
                                            </th>

                                            <!-- Moyenne générale -->
                                            <?php
                                            $color_moy = 'text-dark';
                                            $tt        = "-";

                                            if (count($toutes_moyennes_ue) > 0) {
                                                $tt = round(
                                                    array_sum($toutes_moyennes_ue) / count($toutes_moyennes_ue),
                                                    2
                                                );
                                                if ($tt >= 10) {
                                                    $color_moy = 'text-success';
                                                } elseif ($tt < 6) {
                                                    $color_moy = 'text-danger';
                                                } else {
                                                    $color_moy = 'text-warning';
                                                }
                                            }

                                            // Taux de complétude
                                            $total_ecues     = 0;
                                            $ecues_with_data = 0;
                                            $result_ue_compl = $connexion->query($sql_ue);
                                            while ($data_compl = $result_ue_compl->fetch_object()) {
                                                $sql_ecue_compl    = "SELECT * FROM ecue WHERE code_ue='" . $data_compl->code . "'";
                                                $result_ecue_compl = $connexion->query($sql_ecue_compl);
                                                while ($ecue_compl = $result_ecue_compl->fetch_object()) {
                                                    $total_ecues++;
                                                    $cc_c = getEtudiantCC(
                                                        $etudiant->id, $connexion,
                                                        $etablissement, $semestre,
                                                        $ecue_compl->code_ecue, $annee
                                                    );
                                                    if ($examen == "ordinaire") {
                                                        $ex_c = getEtudiantEXT(
                                                            $etudiant->id, $connexion,
                                                            $etablissement, $semestre,
                                                            $ecue_compl->code_ecue, $annee
                                                        );
                                                    } else {
                                                        $ex_c_ratt = getEtudiantRattrapage(
                                                            $etudiant->id, $connexion,
                                                            $etablissement, $semestre,
                                                            $ecue_compl->code_ecue, $annee
                                                        );
                                                        $ex_c = ($ex_c_ratt !== "-" && $ex_c_ratt !== null && $ex_c_ratt !== "")
                                                            ? $ex_c_ratt
                                                            : getEtudiantEXT(
                                                                $etudiant->id, $connexion,
                                                                $etablissement, $semestre,
                                                                $ecue_compl->code_ecue, $annee
                                                              );
                                                    }
                                                    if ($cc_c !== "-" && $cc_c !== null && $cc_c !== ""
                                                     && $ex_c !== "-" && $ex_c !== null && $ex_c !== "") {
                                                        $ecues_with_data++;
                                                    }
                                                }
                                            }
                                            $percentage_complete = ($total_ecues > 0)
                                                ? ($ecues_with_data / $total_ecues) * 100
                                                : 0;
                                            ?>
                                            <th class="small-column <?php echo $color_moy; ?>" data-type="final">
                                            <?php
                                            if ($tt !== "-") {
                                                echo $tt;
                                                if ($percentage_complete < 100) {
                                                    echo '<br><small style="font-size:9px;">(' . round($percentage_complete) . '%)</small>';
                                                }
                                            } else {
                                                echo "-";
                                            }
                                            ?>
                                            </th>

                                            <!-- Appréciation -->
                                            <th class="small-column text-primary" data-type="final">
                                            <?php echo ($tt !== "-") ? mentionParmoyenne($tt, 2) : "-"; ?>
                                            </th>

                                            <!-- Décision du jury -->
                                            <th class="small-column" data-type="final">
                                            <?php
                                            $result_decision = "-";
                                            if ($tt !== "-") {
                                                if ($tt >= 10 && $a_note_eliminatoire_globale) {
                                                    $result_decision = "<span class='badge badge-danger'>Note Éliminatoire</span>";
                                                } elseif ($tt >= 10) {
                                                    $result_decision = "<span class='badge badge-success'>Validé</span>";
                                                } else {
                                                    $result_decision = "<span class='badge badge-warning'>Ajourné</span>";
                                                }
                                            }
                                            echo $result_decision;
                                            ?>
                                            </th>
                                        </tr>
                                        <?php } // fin while étudiants ?>
                                        </tbody>
                                    </table>
                                    </div><!-- /.table-responsive -->
                                </div><!-- /.card-body -->
                            </div><!-- /.card -->
                        </div>
                    </div>
                </div>
            </div>
        </div><!-- /#printable-area -->

    </div><!-- /.container-fluid -->
</div><!-- /.content-body -->

</div><!-- /#main-wrapper -->

<div class="modal no-print" id="messageModal" tabindex="-1" role="dialog"
     aria-labelledby="messageModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="messageModalLabel"><?php echo $_SESSION['univ']; ?></h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body" id="messageBody"></div>
        </div>
    </div>
</div>

<script src="../vendor/global/global.min.js"></script>
<script src="../vendor/bootstrap-select/dist/js/bootstrap-select.min.js"></script>
<script src="../js/custom.min.js"></script>
<script src="../vendor/raphael/raphael.min.js"></script>
<script src="../vendor/morris/morris.min.js"></script>
<script src="../vendor/select2/js/select2.full.min.js"></script>
<script src="../js/plugins-init/select2-init.js"></script>
<script src="../vendor/peity/jquery.peity.min.js"></script>
<script src="../js/dashboard/dashboard-2.js"></script>
<script src="../vendor/svganimation/vivus.min.js"></script>
<script src="../vendor/svganimation/svg.animation.js"></script>
<script src="../vendor/datatables/js/jquery.dataTables.min.js"></script>
<script src="../js/plugins-init/datatables.init.js"></script>

<script>
$(document).ready(function() {
    var urlParams = new URLSearchParams(window.location.search);
    var erreur  = urlParams.get('erreur');
    var success = urlParams.get('sucess');
    if (erreur || success) {
        var message = erreur ? "Erreur : " + erreur : "Message : " + success;
        $('#messageBody').text(message);
        $('#messageModal').modal('show');
        window.history.replaceState({}, document.title, window.location.pathname);
    }

    $('#btnPublier').click(function() {
        if (!confirm('Publier les résultats pour la classe <?php echo addslashes($classe); ?> ?\nLes étudiants pourront les consulter.')) return;

        var $btn = $(this);
        $btn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> Publication en cours...');

        $.ajax({
            url      : '../pvd/publier_resultats.php',
            type     : 'POST',
            data     : {
                semestre      : '<?php echo addslashes($semestre); ?>',
                specialite    : '<?php echo addslashes(str_replace("+", "'", $specialite)); ?>',
                annee         : '<?php echo addslashes($annee); ?>',
                examen        : '<?php echo addslashes($examen); ?>',
                niveau        : '<?php echo addslashes($niveau); ?>',
                classe        : '<?php echo addslashes($classe); ?>',
                etablissement : '<?php echo addslashes($etablissement); ?>'
            },
            dataType : 'json',
            success  : function(response) {
                var msg = response.success
                    ? response.message
                    : 'Erreur : ' + response.message;
                $('#messageBody').text(msg);
                $('#messageModal').modal('show');
                if (response.success) {
                    $btn.html('<i class="fa fa-check"></i> Résultats publiés');
                } else {
                    $btn.prop('disabled', false).html('<i class="fa fa-paper-plane"></i> Publier les résultats');
                }
            },
            error    : function(xhr) {
                $('#messageBody').text('Erreur serveur : ' + xhr.responseText.substring(0, 200));
                $('#messageModal').modal('show');
                $btn.prop('disabled', false).html('<i class="fa fa-paper-plane"></i> Publier les résultats');
            }
        });
    });
});
</script>

</body>
</html>

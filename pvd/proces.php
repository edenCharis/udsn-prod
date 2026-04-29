<?php 

error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
include '../php/connexion.php';
include '../php/lib.php';

session_start();

if( $_SESSION['id'] == session_id() and  $_SESSION['role']=="pvd"){

   if(isset($_GET['semestre']) and isset($_GET['specialite']) and isset($_GET['annee']) and isset($_GET['examen']))
   {
        $semestre    = urldecode($_GET["semestre"]);
        $specialite  = $_GET["specialite"];
        $annee       = urldecode($_GET["annee"]);
        $examen      = urldecode($_GET["examen"]);
        $niveau      = urldecode($_GET["niveau"]);
        $classe      = urldecode($_GET["classe"]);
        $parcours    = $_SESSION["parcours"];

        $semestre   = mysqli_real_escape_string($connexion, $semestre);
        $specialite = str_replace("'", "+", $specialite);
        $annee      = mysqli_real_escape_string($connexion, $annee);
        $examen     = mysqli_real_escape_string($connexion, $examen);
        $niveau     = mysqli_real_escape_string($connexion, $niveau);
        $classe     = mysqli_real_escape_string($connexion, $classe);

        $etablissement         = $_GET["etablissement"];
        $etablissement_libelle = getLibelleEtablissement($etablissement, $connexion);
        $etablissement         = mysqli_real_escape_string($connexion, $etablissement);

        // ================================================================
        // HELPER : détermine si un étudiant doit passer en rattrapage
        // ================================================================
        function etudiantDoitRattraper($etudiant_id, $semestre, $annee, $etablissement, $connexion) {
            $sql = "SELECT etudiant
                    FROM recap 
                    WHERE etudiant = ? 
                      AND semestre = ? 
                      AND annee = ? 
                      AND etab = ?
                      AND examen = 'ordinaire'
                      AND decision <> 'Validé'
                    LIMIT 1";

            $stmt = $connexion->prepare($sql);
            $stmt->bind_param("isss", $etudiant_id, $semestre, $annee, $etablissement);
            $stmt->execute();
            $stmt->store_result();
            $doit_rattraper = ($stmt->num_rows > 0);
            $stmt->close();

            return ($doit_rattraper);
        }
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $_SESSION['univ'];?> - Scolarité de <?php echo $_SESSION['etablissement'];?></title>
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

        .small-column {
            width: 40px;
        }

        th {
            background-color: #f2f2f2;
        }

        input[type="number"] {
            width: 50px;
            text-align: center;
        }

        .print-page {
            display: none;
        }

        /* ═══════════════════════════════════════
           LÉGENDE DE COULEURS
        ═══════════════════════════════════════ */
        .legende-container {
            display: flex;
            flex-wrap: wrap;
            gap: 12px;
            align-items: center;
            padding: 12px 16px;
            background: #fff;
            border: 1px solid #dee2e6;
            border-radius: 6px;
            margin-bottom: 16px;
        }

        .legende-titre {
            font-weight: 700;
            font-size: 13px;
            color: #343a40;
            margin-right: 8px;
        }

        .legende-item {
            display: flex;
            align-items: center;
            gap: 7px;
            font-size: 12px;
            color: #495057;
        }

        .legende-puce {
            width: 22px;
            height: 22px;
            border-radius: 4px;
            border: 1px solid rgba(0,0,0,0.12);
            flex-shrink: 0;
        }

        .legende-puce.acquise      { background-color: #e8f5e9; }
        .legende-puce.rattrapage   { background-color: #fff3e0; }
        .legende-puce.eliminatoire { background-color: #fdecea; }
        .legende-puce.normale      { background-color: #f2f2f2; }

        /* Styles ECUE */
        .ecue-acquise      { background-color: #e8f5e9; }
        .ecue-rattrapage   { background-color: #fff3e0; }
        .ecue-eliminatoire { background-color: #fdecea; }

        /* Styles pour l'impression */
        @media print {
            @page { size: A4 portrait; margin: 10mm; }

            body * { visibility: hidden; }

            .print-container, .print-container * { visibility: visible; }

            .print-container {
                position: absolute;
                left: 0;
                top: 0;
                width: 100%;
            }

            .no-print { display: none !important; }

            .print-page {
                display: block !important;
                page-break-after: always;
            }

            .print-page:last-child { page-break-after: auto; }

            .page-info {
                text-align: center;
                font-weight: bold;
                padding: 5px;
                background: #e9ecef !important;
                margin-bottom: 10px;
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }

            table    { font-size: 8px !important; }
            th, td   { padding: 2px !important; font-size: 8px !important; }
            th       { font-size: 7px !important; font-weight: bold; }
            .small-column { width: 25px !important; font-size: 7px !important; }
            h4       { font-size: 10px !important; margin: 2px 0 !important; }
            .badge   { font-size: 6px !important; padding: 1px 2px !important; }

            .legende-container { padding: 6px 10px; gap: 8px; margin-bottom: 8px; }
            .legende-titre     { font-size: 9px; }
            .legende-item      { font-size: 8px; }
            .legende-puce      { width: 14px; height: 14px; }

            .ecue-acquise {
                background-color: #e8f5e9 !important;
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }
            .ecue-rattrapage {
                background-color: #fff3e0 !important;
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }
            .ecue-eliminatoire {
                background-color: #fdecea !important;
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }
        }
    </style>
    <link rel="stylesheet" href="../vendor/bootstrap-select/dist/css/bootstrap-select.min.css">
    <link rel="stylesheet" href="../css/style.css">
    <link rel="stylesheet" href="../css/skin.css">
    <link href="../vendor/datatables/css/jquery.dataTables.min.css" rel="stylesheet">
</head>
<body>

<!-- Conteneur pour l'impression -->
<div id="print-container" class="print-container" style="display: none;"></div>

<div id="main-wrapper">

    <?php include "header.php"; ?>
    <?php include 'nav.html'; ?>

    <div class="content-body">
        <div class="container-fluid">

            <div class="row page-titles mx-0">
                <div class="col-sm-6 p-md-0">
                    <div class="welcome-text">
                        <h4 class="card-title">Procès verbal de délibération</h4>
                        <button type="button" class="btn btn-primary mt-2 no-print" id="publierResultats">
                            <i class="fa fa-paper-plane"></i> Publier les résultats
                        </button>
                        <button type="button" class="btn btn-info mt-2 no-print" onclick="imprimerMultiPages()">
                            <i class="fa fa-print"></i> Imprimer
                        </button>
                        
                        <!-- Bouton déclencheur -->
<button type="button" class="btn btn-warning mt-2 no-print" id="btnBatchNotation">
    <i class="fa fa-refresh"></i>
    <?php echo ($examen == "rattrapage") ? "Recharger les moyennes" : "Recharger les moyennes"; ?>
</button>
                    </div>
                </div>
                <div class="col-sm-6 p-md-0 justify-content-sm-end mt-2 mt-sm-0 d-flex">
                    <ol class="breadcrumb no-print">
                        <li class="breadcrumb-item"><a href="index">Tableau de Bord</a></li>
                        <li class="breadcrumb-item"><a href="recap">Recap</a></li>
                    </ol>
                </div>
            </div>

            <!-- Contrôles impression -->
            <div class="row no-print">
                <div class="col-lg-12">
                    <div class="alert alert-success">
                        <h5><i class="fa fa-info-circle"></i> Impression multi-pages</h5>
                        <div style="margin-top: 10px;">
                            <label style="margin-right: 15px; font-weight: bold;">Nombre d'UE par page :</label>
                            <input type="number" id="ueParPage" value="2" min="1" max="10"
                                   style="width: 80px; padding: 5px; margin-right: 10px;">
                            <small class="text-muted">(Ajustez selon la largeur souhaitée)</small>
                        </div>
                        <div style="margin-top: 10px;">
                            <small class="text-muted">
                                💡 <strong>Astuce :</strong> Cliquez sur "Imprimer", puis assemblez les pages
                                horizontalement pour reconstituer le tableau complet.
                            </small>
                        </div>
                    </div>
                </div>
            </div>

            <div id="printable-area">
                <div class="row">
                    <div class="col-lg-12">
                        <h4 class="card-title">
                            Président du Jury :
                            <?php echo getNomUtilisateurParId($connexion, $_SESSION["id_user"]); ?>
                        </h4>
                        
                        <!-- Bouton déclencheur -->

                    </div>

                    <div class="col-lg-12">
                        <div class="row tab-content">
                            <div id="list-view" class="tab-pane fade active show col-lg-12">
                                <div class="card">
                                    <div class="card-header">
                                        <h4 class="card-title">Specialité : <?php echo str_replace("+","'",$specialite); ?></h4>
                                        <h4 class="card-title">Semestre : <?php echo $semestre; ?></h4>
                                        <h4 class="card-title">Année universitaire : <?php echo $annee; ?></h4>
                                        <h4 class="card-title">Examen : <?php echo $examen; ?></h4>
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

                                        <?php
                                        // Requête UE — réutilisée plusieurs fois
                                        $sql_ue = "SELECT DISTINCT ue.code, libelle
                                                   FROM ue
                                                   WHERE ue.etab='" . $etablissement . "'
                                                     AND specialite='" . $specialite . "'
                                                     AND semestre='$semestre'
                                                     AND niveau='$niveau'";

                                        // Compter le nombre total d'UE pour le header
                                        $result_ue_count_total = $connexion->query($sql_ue);
                                        $nb_ue_total = $result_ue_count_total->num_rows;
                                        ?>

                                        <div class="table-responsive">
                                        <table id="example3" class="display">
                                            <thead>
                                                <!-- LIGNE 1 : Nom + UE + colonnes finales -->
                                                <tr>
                                                    <th rowspan="3" data-type="nom">Nom(s) et prénom(s)</th>

                                                    <?php
                                                    $result_ue = $connexion->query($sql_ue);
                                                    $ue_index  = 0;
                                                    while ($data = $result_ue->fetch_object()) {
                                                        $ue_index++;
                                                        $sql_ecue    = "SELECT * FROM ecue WHERE code_ue='" . $data->code . "'";
                                                        $result_ecue = $connexion->query($sql_ecue);
                                                        $nb_ecue     = $result_ecue->num_rows;
                                                        // CC + EX + MOY par ECUE, puis 1 colonne Moy UE
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

                                                // ══════════════════════════════════════════════════════════
                                                // BUG CORRIGÉ : réinitialisation pour CHAQUE étudiant
                                                // Sans ça, $toutes_moyennes_ue s'accumule entre étudiants
                                                // et fausse la moyenne générale à partir du 2ème étudiant.
                                                // ══════════════════════════════════════════════════════════
                                                $toutes_moyennes_ue          = [];
                                                $ue_validees_count           = 0;
                                                $a_note_eliminatoire_globale = false;
                                            ?>
                                            <tr>
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

                                                        // Note EX (ordinaire ou rattrapage)
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
                                                        if ($has_data && $moy_ecue < 6) {
                                                            $a_note_eliminatoire_ue      = true;
                                                            $a_note_eliminatoire_globale = true;
                                                            $class_cell = 'ecue-eliminatoire';
                                                            $class_cc   = 'small-column text-danger font-weight-bold';
                                                            $class_ex   = 'small-column text-danger font-weight-bold';
                                                            $class_moy  = 'small-column text-danger font-weight-bold';
                                                        } elseif ($has_data && $moy_ecue >= 10) {
                                                            $class_cell = 'ecue-acquise';
                                                            $class_cc   = 'small-column text-success';
                                                            $class_ex   = 'small-column text-success';
                                                            $class_moy  = 'small-column text-success font-weight-bold';
                                                        } else {
                                                            $class_cell = 'ecue-rattrapage';
                                                            $class_cc   = 'small-column text-warning font-weight-bold';
                                                            $class_ex   = 'small-column text-warning font-weight-bold';
                                                            $class_moy  = 'small-column text-warning font-weight-bold';
                                                        }
                                                        ?>

                                                        <th class="<?php echo $class_cc; ?> <?php echo $class_cell; ?>"
                                                            data-type="ue" data-ue-index="<?php echo $ue_index; ?>">
                                                            <?php echo ($a !== "-" && $a !== null && $a !== "") ? $a : "-"; ?>
                                                        </th>

                                                        <th class="<?php echo $class_ex; ?> <?php echo $class_cell; ?>"
                                                            data-type="ue" data-ue-index="<?php echo $ue_index; ?>">
                                                            <?php echo ($b !== "-" && $b !== null && $b !== "") ? $b : "-"; ?>
                                                        </th>

                                                        <th class="<?php echo $class_moy; ?> <?php echo $class_cell; ?>"
                                                            data-type="ue" data-ue-index="<?php echo $ue_index; ?>">
                                                            <?php echo $has_data ? $moy_ecue : "-"; ?>
                                                        </th>

                                                    <?php
                                                        // ── Fin de la dernière ECUE de l'UE : calculer moy UE ──
                                                        if ($i == $nb_ecue) {
                                                            $ue_moy = "-";
                                                            if (count($moyennes_ecue_ue) > 0) {
                                                                $ue_moy = round(
                                                                    array_sum($moyennes_ecue_ue) / count($moyennes_ecue_ue),
                                                                    2
                                                                );
                                                                // UE validée si moy ≥ 10 et aucune note éliminatoire
                                                                if ($ue_moy >= 10 && !$a_note_eliminatoire_ue) {
                                                                    $ue_validees_count++;
                                                                }
                                                                // Ajouter à la liste des moyennes UE de l'étudiant
                                                                $toutes_moyennes_ue[] = $ue_moy;
                                                            }
                                                            $ue_moy_class = $a_note_eliminatoire_ue
                                                                ? 'ecue-eliminatoire'
                                                                : (isset($class_cell) ? $class_cell : '');
                                                    ?>
                                                        <th class="small-column text-secondary <?php echo $ue_moy_class; ?>"
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
                                                // BUG CORRIGÉ : $color_moy initialisé par défaut
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

                                                // Calcul du taux de complétude (pour info)
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
                                                <?php
                                                echo ($tt !== "-") ? mentionParmoyenne($tt, 2) : "-";
                                                ?>
                                                </th>

                                                <!-- Décision du jury -->
                                                <th class="small-column" data-type="final">
                                                <?php
                                                $result_decision = "-";
                                                if ($tt !== "-") {
                                                    $statut = "";
                                                    if ($nb_ue_total >= 1) {
                                                        $statut = statutSoutenance(round($tt, 2));
                                                    }

                                                    if ($examen == "rattrapage") {
                                                        $ecues_elim_ratt = rechercher_notes_eliminatoires_rattrapage(
                                                            $etudiant->id, $semestre, $annee, $connexion
                                                        );
                                                        if (!empty($ecues_elim_ratt)) {
                                                            $result_decision = "<span class='badge badge-danger'>Note Éliminatoire</span>";
                                                        } elseif ($a_note_eliminatoire_globale) {
                                                            $result_decision = "<span class='badge badge-danger'>Note Éliminatoire</span>";
                                                        } else {
                                                            $result_decision = $statut;
                                                        }
                                                    } else {
                                                        if ($a_note_eliminatoire_globale) {
                                                            if (stripos($statut, 'Validé') !== false) {
                                                                $result_decision = "<span class='badge badge-danger'>Note Éliminatoire</span>";
                                                            } elseif (stripos($statut, 'Ajourné') !== false || stripos($statut, 'Ajourne') !== false) {
                                                                $result_decision = $statut;
                                                            } else {
                                                                $result_decision = "<span class='badge badge-danger'>Note Éliminatoire</span>";
                                                            }
                                                        } else {
                                                            $result_decision = $statut;
                                                        }
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

<!-- Modal messages -->
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
function imprimerMultiPages() {
    var ueParPage     = parseInt(document.getElementById('ueParPage').value) || 2;
    var tableOriginal = document.getElementById('example3');

    if (!tableOriginal) { alert('Tableau non trouvé'); return; }

    var nbUE = 0;
    tableOriginal.querySelector('thead tr:first-child')
        .querySelectorAll('th[data-type="ue"]')
        .forEach(function() { nbUE++; });

    var nbPages = Math.ceil(nbUE / ueParPage);

    if (!confirm('Le tableau sera divisé en ' + nbPages + ' pages (' + ueParPage +
                 ' UE par page).\nVoulez-vous continuer ?')) return;

    var printContainer = document.getElementById('print-container');
    printContainer.innerHTML = '';

    var legendeOriginal = document.querySelector('.legende-container');

    for (var p = 0; p < nbPages; p++) {
        var startUE      = p * ueParPage + 1;
        var endUE        = Math.min((p + 1) * ueParPage, nbUE);
        var dernierePage = (p === nbPages - 1);

        var pageDiv = document.createElement('div');
        pageDiv.className = 'print-page';

        var pageInfo = document.createElement('div');
        pageInfo.className = 'page-info';
        pageInfo.innerHTML = '<strong>PAGE ' + (p + 1) + ' / ' + nbPages +
                             '</strong> — UE ' + startUE + ' à ' + endUE +
                             ' — <em>À coller horizontalement</em>';
        pageDiv.appendChild(pageInfo);

        var headers  = '<h4>Président du Jury : <?php echo addslashes(getNomUtilisateurParId($connexion,$_SESSION["id_user"])); ?></h4>';
            headers += '<h4>Spécialité : <?php echo addslashes(str_replace("+","'",$specialite)); ?></h4>';
            headers += '<h4>Semestre : <?php echo addslashes($semestre); ?></h4>';
            headers += '<h4>Année universitaire : <?php echo addslashes($annee); ?></h4>';
            headers += '<h4>Examen : <?php echo addslashes($examen); ?></h4>';
            headers += '<h4>Classe : <?php echo addslashes($classe); ?></h4>';
        pageDiv.innerHTML += headers;

        if (legendeOriginal) {
            pageDiv.appendChild(legendeOriginal.cloneNode(true));
        }

        var newTable = tableOriginal.cloneNode(true);

        [newTable.querySelector('thead'), newTable.querySelector('tbody')].forEach(function(section) {
            section.querySelectorAll('tr').forEach(function(row) {
                var cellsToRemove = [];
                row.querySelectorAll('th, td').forEach(function(cell) {
                    var type    = cell.getAttribute('data-type');
                    var ueIndex = parseInt(cell.getAttribute('data-ue-index'));
                    if (type === 'ue') {
                        if (ueIndex < startUE || ueIndex > endUE) cellsToRemove.push(cell);
                    } else if (type === 'final') {
                        if (!dernierePage) cellsToRemove.push(cell);
                    }
                });
                cellsToRemove.forEach(function(cell) { cell.remove(); });
            });
        });

        pageDiv.appendChild(newTable);
        printContainer.appendChild(pageDiv);
    }

    printContainer.style.display = 'block';
    setTimeout(function() {
        window.print();
        setTimeout(function() { printContainer.style.display = 'none'; }, 100);
    }, 200);
}

function supprimerParametreUrl(nomParametre) {
    var url = window.location.href;
    if (url.indexOf('?') !== -1) {
        var queryString = url.split('?')[1];
        var params      = queryString.split('&');
        var newParams   = params.filter(function(param) {
            return param.split('=')[0] !== nomParametre;
        });
        var newUrl = url.split('?')[0] + (newParams.length > 0 ? '?' + newParams.join('&') : '');
        window.history.replaceState({}, document.title, newUrl);
    }
}

$(document).ready(function() {
    var urlParams = new URLSearchParams(window.location.search);
    var erreur    = urlParams.get('erreur');
    var success   = urlParams.get('sucess');

    if (erreur || success) {
        var message = erreur ? "Erreur : " + erreur : "Message : " + success;
        $('#messageBody').text(message);
        $('#messageModal').modal('show');
        supprimerParametreUrl("erreur");
        supprimerParametreUrl("sucess");
    }

    $('#publierResultats').click(function() {
        if (confirm('Êtes-vous sûr de vouloir publier ces résultats ? Les étudiants pourront les consulter.')) {
            $(this).prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> Publication en cours...');

            $.ajax({
                url: 'publier_resultats.php',
                type: 'POST',
                data: {
                    semestre      : '<?php echo $semestre; ?>',
                    specialite    : '<?php echo $specialite; ?>',
                    annee         : '<?php echo $annee; ?>',
                    examen        : '<?php echo $examen; ?>',
                    niveau        : '<?php echo $niveau; ?>',
                    classe        : '<?php echo $classe; ?>',
                    etablissement : '<?php echo $etablissement; ?>'
                },
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        alert(response.message);
                        $('#publierResultats').prop('disabled', false)
                            .html('<i class="fa fa-check"></i> Résultats publiés');
                    } else {
                        alert('Erreur : ' + response.message);
                        $('#publierResultats').prop('disabled', false)
                            .html('<i class="fa fa-paper-plane"></i> Publier les résultats');
                    }
                },
                error: function(xhr, status, error) {
                    alert('Erreur lors de la publication des résultats\n\nStatut: ' + status +
                          '\nErreur: ' + error + '\n\nDétails (voir console): ' +
                          xhr.responseText.substring(0, 200));
                    $('#publierResultats').prop('disabled', false)
                        .html('<i class="fa fa-paper-plane"></i> Publier les résultats');
                }
            });
        }
    });
});
</script>


<script>// ══════════════════════════════════════════════
// BATCH UPDATE NOTATION (ordinaire ou rattrapage)
// ══════════════════════════════════════════════
$('#btnBatchNotation').click(function() {

    var typeExamen = '<?php echo ($examen == "rattrapage") ? "Session Rattrapage" : "Session Ordinaire"; ?>';
    var libelle    = '<?php echo ($examen == "rattrapage") ? "rattrapage" : "session ordinaire"; ?>';

    if (!confirm('Calculer et enregistrer toutes les moyennes de ' + libelle +
                 ' pour la classe <?php echo addslashes($classe); ?> ?\n\nCela va mettre à jour la table notation.')) return;

    $(this).prop('disabled', true)
           .html('<i class="fa fa-spinner fa-spin"></i> Calcul en cours...');

    $.ajax({
        url      : 'batch_update_rattrapage.php',
        type     : 'POST',
        contentType: 'application/json',
        data     : JSON.stringify({
            semestre    : '<?php echo $semestre; ?>',
            annee       : '<?php echo $annee; ?>',
            nature      : '',
            type_examen : typeExamen,
            classe      : '<?php echo $classe; ?>',
            specialite  : '<?php echo $specialite; ?>',
            niveau      : '<?php echo $niveau; ?>',
            examen      : '<?php echo $examen; ?>'
        }),
        dataType : 'json',
        success  : function(response) {
            if (response.success) {
                var msg = '✅ ' + response.message +
                          '\nClasses traitées : '      + response.classes_traitees +
                          '\nClasses sans étudiant : ' + response.classes_sans_ratt;
                if (response.errors && response.errors.length > 0) {
                    msg += '\n\n⚠️ Avertissements (' + response.errors.length + ') :\n' +
                           response.errors.slice(0, 5).join('\n');
                    if (response.errors.length > 5)
                        msg += '\n... (' + (response.errors.length - 5) + ' autres)';
                }
                alert(msg);
            } else {
                alert('❌ Erreur : ' + response.error);
            }
        },
        error    : function(xhr) {
            alert('❌ Erreur serveur :\n' + xhr.responseText.substring(0, 300));
        },
        complete : function() {
            var icon  = '<?php echo ($examen == "rattrapage") ? "Recharger les moyennes" : "Recharger les moyennes"; ?>';
            $('#btnBatchNotation').prop('disabled', false)
                .html('<i class="fa fa-database"></i> ' + icon);
        }
    });
});</script>

</body>
</html>
<?php
    } // fin isset GET
} else {
    header("location: ../login");
}
?>
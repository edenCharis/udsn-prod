<?php 
include '../php/connexion.php';
include '../php/lib.php';

session_start();

if( $_SESSION['id'] == session_id() and  $_SESSION['role']=="gesnote"){

    // Récupération des filtres
    $classe_filter = isset($_GET['classe_filter']) ? $connexion->real_escape_string($_GET['classe_filter']) : '';
    $semestre_filter = isset($_GET['semestre_filter']) ? $connexion->real_escape_string($_GET['semestre_filter']) : '';
    
    // Année par défaut = année la plus récente
    if(isset($_GET['annee_filter'])){
        $annee_filter = $connexion->real_escape_string($_GET['annee_filter']);
    } else {
        $sql_annee_defaut = "SELECT libelle FROM annee ORDER BY libelle DESC LIMIT 1";
        $result_annee_defaut = $connexion->query($sql_annee_defaut);
        if($result_annee_defaut && $result_annee_defaut->num_rows > 0){
            $annee_defaut = $result_annee_defaut->fetch_assoc();
            $annee_filter = $annee_defaut['libelle'];
        } else {
            $annee_filter = date('Y');
        }
    }

?>

<!DOCTYPE html>
<html lang="en">

<head>
	
	<meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title><?php echo $_SESSION['univ'];?> - Scolarité de  <?php echo $_SESSION['etablissement'];?> </title>
    <!-- Favicon icon -->
    <link rel="icon" type="image/png" sizes="16x16" href="../administrateur/<?php echo  $_SESSION['logo_univ']?>">
	<link rel="stylesheet" href="../vendor/bootstrap-select/dist/css/bootstrap-select.min.css">
    <link rel="stylesheet" href="../vendor/select2/css/select2.min.css">
    <link rel="stylesheet" href="../css/style.css">
	<link rel="stylesheet" href="../css/skin.css">

    <style>
        .ecue-card {
            margin-bottom: 15px;
            border-radius: 8px;
            transition: transform 0.2s;
        }
        .ecue-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }
        .status-badge {
            font-size: 0.85rem;
            padding: 5px 10px;
            border-radius: 20px;
        }
        .progress-custom {
            height: 8px;
            border-radius: 10px;
        }
        .stat-number {
            font-size: 2rem;
            font-weight: bold;
        }
        .classe-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 20px;
            border-radius: 10px;
            margin-bottom: 20px;
        }
        .legend-item {
            display: inline-block;
            margin-right: 20px;
            margin-bottom: 10px;
        }
        .legend-color {
            display: inline-block;
            width: 20px;
            height: 20px;
            border-radius: 4px;
            margin-right: 5px;
            vertical-align: middle;
        }
    </style>

</head>
<body>

    <!--*******************
        Preloader start
    ********************-->
    <div id="preloader">
        <div class="sk-three-bounce">
            <div class="sk-child sk-bounce1"></div>
            <div class="sk-child sk-bounce2"></div>
            <div class="sk-child sk-bounce3"></div>
        </div>
    </div>
    <!--*******************
        Preloader end
    ********************-->

    <!--**********************************
        Main wrapper start
    ***********************************-->
    <div id="main-wrapper">

        <!--**********************************
            Nav header start
        ***********************************-->
    
        <!--**********************************
            Nav header end
        ***********************************-->

        <!--**********************************
            Header start
        ***********************************-->
        <?php include "header.php" ;?>
        <!--**********************************
            Header end ti-comment-alt
        ***********************************-->

        <!--**********************************
            Sidebar start
        ***********************************-->
       <?php 
         include 'nav.html';
       ?>
        <!--**********************************
            Sidebar end
        ***********************************-->
		
		<!--**********************************
            Content body start
        ***********************************-->
        <div class="content-body">
            <!-- row -->
            <div class="container-fluid">
				
				<div class="row page-titles mx-0">
                    <div class="col-sm-6 p-md-0">
                        <div class="welcome-text">
                            <h3>Suivi de la saisie des notes</h3>
                            <p>Visualisez l'état d'avancement de la saisie des notes par classe</p>
                        </div>
                    </div>
                    <div class="col-sm-6 p-md-0 justify-content-sm-end mt-2 mt-sm-0 d-flex">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="../scolarite/">Scolarité</a></li>
                            <li class="breadcrumb-item"><a href="notation">Notes</a></li>
                            <li class="breadcrumb-item active"><a href="javascript:void(0);">Suivi</a></li>
                        </ol>
                    </div>
                </div>

                <!-- Filtres -->
                <div class="row mb-4">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-body">
                                <form method="GET" action="">
                                    <div class="row">
                                        <div class="col-md-3">
                                            <div class="form-group">
                                                <label>Classe</label>
                                                <select name="classe_filter" class="form-control disabling-options">
                                                    <option value="">Toutes les classes</option>
                                                    <?php 
                                                        $sql_classes = "SELECT DISTINCT classe FROM vue_repartition WHERE etab='".$connexion->real_escape_string($_SESSION['etablissement'])."' ORDER BY classe";
                                                        $result_classes = $connexion->query($sql_classes);
                                                        if($result_classes){
                                                            while($classe = $result_classes->fetch_assoc()){
                                                                $selected = (isset($_GET['classe_filter']) && $_GET['classe_filter'] == $classe['classe']) ? 'selected' : '';
                                                                echo "<option value='".$classe['classe']."' $selected>".str_replace("+","'",$classe['classe'])."</option>";
                                                            }
                                                        }
                                                    ?>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="form-group">
                                                <label>Semestre</label>
                                                <select name="semestre_filter" class="form-control disabling-options">
                                                    <option value="">Tous les semestres</option>
                                                    <?php 
                                                        $sql_semestres = "SELECT DISTINCT semestre FROM vue_repartition WHERE etab='".$connexion->real_escape_string($_SESSION['etablissement'])."' ORDER BY semestre";
                                                        $result_semestres = $connexion->query($sql_semestres);
                                                        if($result_semestres){
                                                            while($semestre = $result_semestres->fetch_assoc()){
                                                                $selected = (isset($_GET['semestre_filter']) && $_GET['semestre_filter'] == $semestre['semestre']) ? 'selected' : '';
                                                                echo "<option value='".$semestre['semestre']."' $selected>".str_replace("+","'",$semestre['semestre'])."</option>";
                                                            }
                                                        }
                                                    ?>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="form-group">
                                                <label>Année académique</label>
                                                <select name="annee_filter" class="form-control disabling-options">
                                                    <?php 
                                                        $sql_annees = "SELECT * FROM annee ORDER BY libelle DESC";
                                                        $result_annees = $connexion->query($sql_annees);
                                                        if($result_annees){
                                                            while($annee = $result_annees->fetch_assoc()){
                                                                $selected = ($annee_filter == $annee['libelle']) ? 'selected' : '';
                                                                echo "<option value='".$annee['libelle']."' $selected>".str_replace("+","'",$annee['libelle'])."</option>";
                                                            }
                                                        }
                                                    ?>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="form-group">
                                                <label>&nbsp;</label>
                                                <button type="submit" class="btn btn-primary btn-block">Filtrer</button>
                                            </div>
                                        </div>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Légende -->
                <div class="row mb-3">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-body">
                                <h5 class="mb-3">Légende</h5>
                                <div class="legend-item">
                                    <span class="legend-color bg-success"></span>
                                    <span>Complet (Devoir + Examen)</span>
                                </div>
                                <div class="legend-item">
                                    <span class="legend-color bg-warning"></span>
                                    <span>Partiel (Devoir OU Examen)</span>
                                </div>
                                <div class="legend-item">
                                    <span class="legend-color bg-danger"></span>
                                    <span>Aucune note</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <?php
                // Construction de la requête avec filtres
                $where_conditions = "etab='".$connexion->real_escape_string($_SESSION['etablissement'])."'";
                if(!empty($classe_filter)){
                    $where_conditions .= " AND classe='$classe_filter'";
                }
                if(!empty($semestre_filter)){
                    $where_conditions .= " AND semestre='$semestre_filter'";
                }

                // Requête pour obtenir les classes avec LIMIT pour éviter le timeout
                $sql_classes_list = "SELECT DISTINCT classe, niveau, semestre 
                                     FROM vue_repartition 
                                     WHERE $where_conditions 
                                     ORDER BY classe, semestre 
                                     LIMIT 50";
                $result_classes_list = $connexion->query($sql_classes_list);

                if($result_classes_list && $result_classes_list->num_rows > 0){
                    while($classe_data = $result_classes_list->fetch_assoc()){
                        $classe = $connexion->real_escape_string($classe_data['classe']);
                        $semestre = $connexion->real_escape_string($classe_data['semestre']);
                ?>

                <!-- Section par classe -->
                <div class="row">
                    <div class="col-12">
                        <div class="classe-header">
                            <h4 class="mb-0"><?php echo str_replace("+","'",$classe); ?> - <?php echo str_replace("+","'",$semestre); ?></h4>
                        </div>
                    </div>
                </div>

                <?php
                    // Récupération des ECUE pour cette classe et semestre
                    $sql_ecues = "SELECT DISTINCT code_ecue, ecue, ue, libelle_ue 
                                  FROM vue_repartition 
                                  WHERE classe='$classe' 
                                  AND semestre='$semestre' 
                                  AND etab='".$connexion->real_escape_string($_SESSION['etablissement'])."'
                                  ORDER BY ecue
                                  LIMIT 20";
                    $result_ecues = $connexion->query($sql_ecues);

                    // Statistiques globales pour cette classe
                    $total_ecues = 0;
                    $ecues_complets = 0;
                    $ecues_partiels = 0;
                    $ecues_vides = 0;

                    if($result_ecues){
                        $total_ecues = $result_ecues->num_rows;
                ?>

                <div class="row mb-4">
                    <?php
                    while($ecue_data = $result_ecues->fetch_assoc()){
                        $code_ecue = $ecue_data['code_ecue'];
                        $ecue = $connexion->real_escape_string($ecue_data['ecue']);
                        $ue = $ecue_data['ue'];
                        $libelle_ue = $ecue_data['libelle_ue'];

                        // Compter le nombre total d'étudiants dans cette classe pour cette année
                        $sql_total_etudiants = "SELECT COUNT(DISTINCT i.id) as total 
                                                FROM inscription i 
                                                WHERE i.classe='$classe' 
                                                AND i.etab='".$connexion->real_escape_string($_SESSION['etablissement'])."'
                                                AND i.annee='$annee_filter'";
                        $result_total = $connexion->query($sql_total_etudiants);
                        $total_etudiants = 0;
                        if($result_total){
                            $row_total = $result_total->fetch_assoc();
                            $total_etudiants = $row_total['total'];
                        }

                        // Compter les notes avec devoir ET examen
                        $sql_complet = "SELECT COUNT(DISTINCT inscription) as nb 
                                       FROM notation
                                       WHERE classe='$classe' 
                                       AND ecue='$ecue' 
                                       AND semestre='$semestre'
                                       AND annee='$annee_filter'
                                       AND etab='".$connexion->real_escape_string($_SESSION['etablissement'])."'
                                       AND moyDev IS NOT NULL 
                                       AND moyEx IS NOT NULL";
                        $result_complet = $connexion->query($sql_complet);
                        $nb_complet = 0;
                        if($result_complet){
                            $row_complet = $result_complet->fetch_assoc();
                            $nb_complet = $row_complet['nb'];
                        }

                        // Compter les notes avec devoir OU examen (mais pas les deux)
                        $sql_partiel = "SELECT COUNT(DISTINCT inscription) as nb 
                                       FROM notation
                                       WHERE classe='$classe' 
                                       AND ecue='$ecue' 
                                       AND semestre='$semestre'
                                       AND annee='$annee_filter'
                                       AND etab='".$connexion->real_escape_string($_SESSION['etablissement'])."'
                                       AND ((moyDev IS NOT NULL AND moyEx IS NULL) 
                                            OR (moyDev IS NULL AND moyEx IS NOT NULL))";
                        $result_partiel = $connexion->query($sql_partiel);
                        $nb_partiel = 0;
                        if($result_partiel){
                            $row_partiel = $result_partiel->fetch_assoc();
                            $nb_partiel = $row_partiel['nb'];
                        }

                        // Calculer le nombre sans notes
                        $nb_vide = $total_etudiants - $nb_complet - $nb_partiel;
                        if($nb_vide < 0) $nb_vide = 0;

                        // Déterminer le statut et la couleur
                        if($total_etudiants > 0){
                            $pourcentage_complet = ($nb_complet / $total_etudiants) * 100;
                            $pourcentage_partiel = ($nb_partiel / $total_etudiants) * 100;
                            $pourcentage_vide = ($nb_vide / $total_etudiants) * 100;
                        } else {
                            $pourcentage_complet = 0;
                            $pourcentage_partiel = 0;
                            $pourcentage_vide = 100;
                        }

                        if($nb_complet == $total_etudiants && $total_etudiants > 0){
                            $statut = "Complet";
                            $badge_class = "badge-success";
                            $card_border = "border-success";
                            $ecues_complets++;
                        } elseif($nb_complet > 0 || $nb_partiel > 0){
                            $statut = "Partiel";
                            $badge_class = "badge-warning";
                            $card_border = "border-warning";
                            $ecues_partiels++;
                        } else {
                            $statut = "Aucune note";
                            $badge_class = "badge-danger";
                            $card_border = "border-danger";
                            $ecues_vides++;
                        }
                    ?>

                    <div class="col-xl-4 col-lg-6 col-md-6">
                        <div class="card ecue-card <?php echo $card_border; ?>" style="border-left: 4px solid;">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-start mb-3">
                                    <div>
                                        <h5 class="mb-1"><?php echo str_replace("+","'",$ecue); ?></h5>
                                        <p class="text-muted mb-0" style="font-size: 0.85rem;">
                                            UE: <?php echo str_replace("+","'",$libelle_ue); ?>
                                        </p>
                                    </div>
                                    <span class="badge <?php echo $badge_class; ?> status-badge"><?php echo $statut; ?></span>
                                </div>

                                <div class="mb-3">
                                    <div class="row text-center">
                                        <div class="col-4">
                                            <div class="p-2" style="background-color: rgba(40, 167, 69, 0.1); border-radius: 8px;">
                                                <h4 class="mb-0 text-success"><?php echo $nb_complet; ?></h4>
                                                <small>Complet</small>
                                            </div>
                                        </div>
                                        <div class="col-4">
                                            <div class="p-2" style="background-color: rgba(255, 193, 7, 0.1); border-radius: 8px;">
                                                <h4 class="mb-0 text-warning"><?php echo $nb_partiel; ?></h4>
                                                <small>Partiel</small>
                                            </div>
                                        </div>
                                        <div class="col-4">
                                            <div class="p-2" style="background-color: rgba(220, 53, 69, 0.1); border-radius: 8px;">
                                                <h4 class="mb-0 text-danger"><?php echo $nb_vide; ?></h4>
                                                <small>Vide</small>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="mb-2">
                                    <div class="d-flex justify-content-between mb-1">
                                        <small>Progression</small>
                                        <small><strong><?php echo number_format($pourcentage_complet, 1); ?>%</strong></small>
                                    </div>
                                    <div class="progress progress-custom">
                                        <div class="progress-bar bg-success" style="width: <?php echo $pourcentage_complet; ?>%"></div>
                                        <div class="progress-bar bg-warning" style="width: <?php echo $pourcentage_partiel; ?>%"></div>
                                    </div>
                                </div>

                                <div class="text-muted" style="font-size: 0.85rem;">
                                    <i class="fa fa-users"></i> <?php echo $total_etudiants; ?> étudiant(s)
                                </div>

                                <div class="mt-3">
                                    <a href="notation?classe=<?php echo urlencode($classe); ?>&ecue=<?php echo urlencode($ecue); ?>&semestre=<?php echo urlencode($semestre); ?>" 
                                       class="btn btn-sm btn-outline-primary btn-block">
                                        <i class="fa fa-edit"></i> Gérer les notes
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>

                    <?php } ?>

                    <!-- Carte résumé pour la classe -->
                    <div class="col-12">
                        <div class="card bg-light">
                            <div class="card-body">
                                <div class="row text-center">
                                    <div class="col-md-3">
                                        <h3 class="mb-0"><?php echo $total_ecues; ?></h3>
                                        <small>Total ECUE</small>
                                    </div>
                                    <div class="col-md-3">
                                        <h3 class="mb-0 text-success"><?php echo $ecues_complets; ?></h3>
                                        <small>Complets</small>
                                    </div>
                                    <div class="col-md-3">
                                        <h3 class="mb-0 text-warning"><?php echo $ecues_partiels; ?></h3>
                                        <small>Partiels</small>
                                    </div>
                                    <div class="col-md-3">
                                        <h3 class="mb-0 text-danger"><?php echo $ecues_vides; ?></h3>
                                        <small>Sans notes</small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <?php 
                    } // fin if $result_ecues
                    } // fin while classes
                } else {
                    // Aucune donnée trouvée
                    echo '<div class="row"><div class="col-12"><div class="alert alert-info">Aucune donnée disponible pour les critères sélectionnés.</div></div></div>';
                }
                ?>

            </div>
        </div>
        <!--**********************************
            Content body end
        ***********************************-->

        <!--**********************************
            Footer start
        ***********************************-->
         <div class="footer">
            <div class="copyright">
                <p>Copyright © Designed &amp; Developpé  par <a href="htpps:/www.cet-up.com" target="_blank">CETUP</a> 2023</p>
            </div>
        </div>
        <!--**********************************
            Footer end
        ***********************************-->

		<!--**********************************
           Support ticket button start
        ***********************************-->

        <!--**********************************
           Support ticket button end
        ***********************************-->

                                                        </div>
    
    <!--**********************************
        Main wrapper end
    ***********************************-->

    <!--**********************************
        Scripts
    ***********************************-->
    <!-- Required vendors -->
    <script src="../vendor/global/global.min.js"></script>
	<script src="../vendor/bootstrap-select/dist/js/bootstrap-select.min.js"></script>
	<script src="../js/custom.min.js"></script>
		
    <!-- Chart Morris plugin files -->
    <script src="../vendor/raphael/raphael.min.js"></script>
    <script src="../vendor/morris/morris.min.js"></script>
		
    <script src="../vendor/select2/js/select2.full.min.js"></script>
    <script src="../js/plugins-init/select2-init.js"></script>
	<!-- Chart piety plugin files -->
    <script src="../vendor/peity/jquery.peity.min.js"></script>
	
	<!-- Demo scripts -->
    <script src="../js/dashboard/dashboard-2.js"></script>
	
	<!-- Svganimation scripts -->
    <script src="../vendor/svganimation/vivus.min.js"></script>
    <script src="../vendor/svganimation/svg.animation.js"></script>
	
</body>
</html>

<?php 
}else{
    header("location: ../login");
}
?>
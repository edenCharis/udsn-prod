<?php 
include '../php/connexion.php';
include '../php/lib.php';

session_start();

if( $_SESSION['id'] == session_id() and  $_SESSION['role']=="gesnote"){

    // Récupération de l'année académique active
    $sql_annee_defaut = "SELECT libelle FROM annee ORDER BY libelle DESC LIMIT 1";
    $result_annee_defaut = $connexion->query($sql_annee_defaut);
    if($result_annee_defaut && $result_annee_defaut->num_rows > 0){
        $annee_defaut = $result_annee_defaut->fetch_assoc();
        $annee_active = $annee_defaut['libelle'];
    } else {
        $annee_active = date('Y');
    }

    $annee_filter = isset($_GET['annee_filter']) ? $connexion->real_escape_string($_GET['annee_filter']) : $annee_active;
    $etab = $connexion->real_escape_string($_SESSION['etablissement']);

    // KPI 1: Nombre total d'étudiants
    $sql_total_etudiants = "SELECT COUNT(DISTINCT id) as total FROM inscription WHERE etab='$etab' AND annee='$annee_filter'";
    $result_total_etudiants = $connexion->query($sql_total_etudiants);
    $total_etudiants = $result_total_etudiants ? $result_total_etudiants->fetch_assoc()['total'] : 0;

    // KPI 2: Taux de réussite global (moyenne >= 10)
    $sql_reussite = "SELECT COUNT(DISTINCT inscription) as reussis 
                     FROM notation
                     WHERE etab='$etab' 
                     AND annee='$annee_filter' 
                     AND moyGen >= 10 
                     AND moyGen IS NOT NULL";
    $result_reussite = $connexion->query($sql_reussite);
    $nb_reussis = $result_reussite ? $result_reussite->fetch_assoc()['reussis'] : 0;

    $sql_total_notes = "SELECT COUNT(DISTINCT inscription) as total 
                        FROM notation
                        WHERE etab='$etab' 
                        AND annee='$annee_filter' 
                        AND moyGen IS NOT NULL";
    $result_total_notes = $connexion->query($sql_total_notes);
    $total_avec_notes = $result_total_notes ? $result_total_notes->fetch_assoc()['total'] : 0;
    
    $taux_reussite = $total_avec_notes > 0 ? ($nb_reussis / $total_avec_notes) * 100 : 0;

    // KPI 3: Étudiants en difficulté (moyenne < 10)
    $sql_difficulte = "SELECT COUNT(DISTINCT inscription) as difficulte 
                       FROM notation 
                       WHERE etab='$etab' 
                       AND annee='$annee_filter' 
                       AND moyGen < 10 
                       AND moyGen IS NOT NULL";
    $result_difficulte = $connexion->query($sql_difficulte);
    $nb_difficulte = $result_difficulte ? $result_difficulte->fetch_assoc()['difficulte'] : 0;

    // KPI 4: Progression saisie des notes
    $sql_notes_completes = "SELECT COUNT(*) as complet 
                           FROM notation
                           WHERE etab='$etab' 
                           AND annee='$annee_filter' 
                           AND moyDev IS NOT NULL 
                           AND moyEx IS NOT NULL";
    $result_notes_completes = $connexion->query($sql_notes_completes);
    $notes_completes = $result_notes_completes ? $result_notes_completes->fetch_assoc()['complet'] : 0;

    $sql_total_notes_attendues = "SELECT COUNT(*) as total FROM notation WHERE etab='$etab' AND annee='$annee_filter'";
    $result_total_attendues = $connexion->query($sql_total_notes_attendues);
    $total_attendues = $result_total_attendues ? $result_total_attendues->fetch_assoc()['total'] : 1;
    
    $progression_saisie = ($notes_completes / $total_attendues) * 100;

    // Moyenne générale de l'établissement
    $sql_moyenne_etab = "SELECT AVG(moyGen) as moyenne FROM notation WHERE etab='$etab' AND annee='$annee_filter' AND moyGen IS NOT NULL";
    $result_moyenne_etab = $connexion->query($sql_moyenne_etab);
    $moyenne_etab = $result_moyenne_etab ? $result_moyenne_etab->fetch_assoc()['moyenne'] : 0;

    // Distribution des notes (pour graphique)
    $sql_distribution = "SELECT 
                         SUM(CASE WHEN moyGen >= 16 THEN 1 ELSE 0 END) as excellent,
                         SUM(CASE WHEN moyGen >= 14 AND moyGen < 16 THEN 1 ELSE 0 END) as tres_bien,
                         SUM(CASE WHEN moyGen >= 12 AND moyGen < 14 THEN 1 ELSE 0 END) as bien,
                         SUM(CASE WHEN moyGen >= 10 AND moyGen < 12 THEN 1 ELSE 0 END) as assez_bien,
                         SUM(CASE WHEN moyGen < 10 THEN 1 ELSE 0 END) as insuffisant
                         FROM notation 
                         WHERE etab='$etab' AND annee='$annee_filter' AND moyGen IS NOT NULL";
    $result_distribution = $connexion->query($sql_distribution);
    $distribution = $result_distribution ? $result_distribution->fetch_assoc() : array();

    // Top 5 classes par moyenne
    $sql_top_classes = "SELECT classe, AVG(moyGen) as moyenne, COUNT(DISTINCT inscription) as nb_etudiants
                        FROM notation 
                        WHERE etab='$etab' AND annee='$annee_filter' AND moyGen IS NOT NULL
                        GROUP BY classe 
                        ORDER BY moyenne DESC 
                        LIMIT 5";
    $result_top_classes = $connexion->query($sql_top_classes);

    // Top 10 étudiants
    $sql_top_etudiants = "SELECT inscription, AVG(moyGen) as moyenne
                          FROM notation
                          WHERE etab='$etab' AND annee='$annee_filter' AND moyGen IS NOT NULL
                          GROUP BY inscription 
                          ORDER BY moyenne DESC 
                          LIMIT 10";
    $result_top_etudiants = $connexion->query($sql_top_etudiants);

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
    
    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js@3.9.1/dist/chart.min.js"></script>

    <style>
        .stat-card {
            border-radius: 10px;
            padding: 20px;
            margin-bottom: 20px;
            color: white;
            position: relative;
            overflow: hidden;
        }
        .stat-card .icon {
            position: absolute;
            right: 20px;
            top: 20px;
            font-size: 3rem;
            opacity: 0.3;
        }
        .stat-number {
            font-size: 2.5rem;
            font-weight: bold;
            margin: 10px 0;
        }
        .stat-label {
            font-size: 0.9rem;
            opacity: 0.9;
        }
        .chart-container {
            position: relative;
            height: 300px;
            margin-bottom: 20px;
        }
        .ranking-item {
            padding: 10px;
            border-radius: 5px;
            margin-bottom: 10px;
            background: #f8f9fa;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .ranking-position {
            width: 30px;
            height: 30px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
            color: white;
        }
        .position-1 { background: #FFD700; }
        .position-2 { background: #C0C0C0; }
        .position-3 { background: #CD7F32; }
        .position-other { background: #6c757d; }
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
                            <h3>Tableau de Bord Analytique</h3>
                            <p>Vue d'ensemble des performances académiques</p>
                        </div>
                    </div>
                    <div class="col-sm-6 p-md-0 justify-content-sm-end mt-2 mt-sm-0 d-flex">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="../gesnote/">Accueil</a></li>
                            <li class="breadcrumb-item active"><a href="javascript:void(0);">Dashboard</a></li>
                        </ol>
                    </div>
                </div>

                <!-- Filtre année -->
                <div class="row mb-4">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-body">
                                <form method="GET" action="">
                                    <div class="row">
                                        <div class="col-md-3">
                                            <div class="form-group">
                                                <label>Année académique</label>
                                                <select name="annee_filter" class="form-control" onchange="this.form.submit()">
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
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- KPIs -->
                <div class="row">
                    <div class="col-xl-3 col-lg-6 col-md-6">
                        <div class="stat-card" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
                            <i class="la la-users icon"></i>
                            <div class="stat-label">Total Étudiants</div>
                            <div class="stat-number"><?php echo number_format($total_etudiants); ?></div>
                        </div>
                    </div>
                    
                    <div class="col-xl-3 col-lg-6 col-md-6">
                        <div class="stat-card" style="background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);">
                            <i class="la la-chart-line icon"></i>
                            <div class="stat-label">Taux de Réussite</div>
                            <div class="stat-number"><?php echo number_format($taux_reussite, 1); ?>%</div>
                            <small><?php echo $nb_reussis; ?> / <?php echo $total_avec_notes; ?> étudiants</small>
                        </div>
                    </div>
                    
                    <div class="col-xl-3 col-lg-6 col-md-6">
                        <div class="stat-card" style="background: linear-gradient(135deg, #fa709a 0%, #fee140 100%);">
                            <i class="la la-exclamation-triangle icon"></i>
                            <div class="stat-label">En Difficulté</div>
                            <div class="stat-number"><?php echo number_format($nb_difficulte); ?></div>
                            <small>Moyenne &lt; 10/20</small>
                        </div>
                    </div>
                    
                    <div class="col-xl-3 col-lg-6 col-md-6">
                        <div class="stat-card" style="background: linear-gradient(135deg, #30cfd0 0%, #330867 100%);">
                            <i class="la la-check-circle icon"></i>
                            <div class="stat-label">Saisie des Notes</div>
                            <div class="stat-number"><?php echo number_format($progression_saisie, 1); ?>%</div>
                            <small><?php echo $notes_completes; ?> / <?php echo $total_attendues; ?> notes</small>
                        </div>
                    </div>
                </div>

                <!-- Moyenne générale -->
                <div class="row">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-body text-center" style="background: linear-gradient(135deg, #a8edea 0%, #fed6e3 100%); border-radius: 10px;">
                                <h5>Moyenne Générale de l'Établissement</h5>
                                <h1 style="font-size: 4rem; font-weight: bold; color: #333;"><?php echo number_format($moyenne_etab, 2); ?>/20</h1>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Graphiques -->
                <div class="row">
                    <div class="col-lg-6">
                        <div class="card">
                            <div class="card-header">
                                <h4 class="card-title">Distribution des Notes</h4>
                            </div>
                            <div class="card-body">
                                <div class="chart-container">
                                    <canvas id="distributionChart"></canvas>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-lg-6">
                        <div class="card">
                            <div class="card-header">
                                <h4 class="card-title">Répartition Réussite/Échec</h4>
                            </div>
                            <div class="card-body">
                                <div class="chart-container">
                                    <canvas id="reussiteChart"></canvas>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Top 5 Classes et Top 10 Étudiants -->
                <div class="row">
                    <div class="col-lg-6">
                        <div class="card">
                            <div class="card-header">
                                <h4 class="card-title">🏆 Top 5 Classes par Moyenne</h4>
                            </div>
                            <div class="card-body">
                                <?php 
                                if($result_top_classes && $result_top_classes->num_rows > 0){
                                    $position = 1;
                                    while($classe = $result_top_classes->fetch_assoc()){
                                        $position_class = $position <= 3 ? "position-$position" : "position-other";
                                ?>
                                <div class="ranking-item">
                                    <div class="d-flex align-items-center">
                                        <div class="ranking-position <?php echo $position_class; ?>">
                                            <?php echo $position; ?>
                                        </div>
                                        <div class="ml-3">
                                            <strong><?php echo str_replace("+","'",$classe['classe']); ?></strong>
                                            <div class="text-muted" style="font-size: 0.85rem;">
                                                <?php echo $classe['nb_etudiants']; ?> étudiant(s)
                                            </div>
                                        </div>
                                    </div>
                                    <div>
                                        <span class="badge badge-primary" style="font-size: 1.1rem;">
                                            <?php echo number_format($classe['moyenne'], 2); ?>/20
                                        </span>
                                    </div>
                                </div>
                                <?php 
                                        $position++;
                                    }
                                } else {
                                    echo '<p class="text-muted">Aucune donnée disponible</p>';
                                }
                                ?>
                            </div>
                        </div>
                    </div>

                    <div class="col-lg-6">
                        <div class="card">
                            <div class="card-header">
                                <h4 class="card-title">⭐ Top 10 Meilleurs Étudiants</h4>
                            </div>
                            <div class="card-body">
                                <?php 
                                if($result_top_etudiants && $result_top_etudiants->num_rows > 0){
                                    $position = 1;
                                    while($etudiant = $result_top_etudiants->fetch_assoc()){
                                        $position_class = $position <= 3 ? "position-$position" : "position-other";
                                        $code_candidat = getCandidatCodeByInscription($etudiant['inscription'], $connexion);
                                        $nom = obtenirNomPrenom($code_candidat, $annee_filter, $connexion);
                                        
                                        // Récupérer la classe de l'étudiant
                                        $sql_classe_etudiant = "SELECT classe FROM inscription WHERE id='".$etudiant['inscription']."' LIMIT 1";
                                        $result_classe_etudiant = $connexion->query($sql_classe_etudiant);
                                        $classe_etudiant = "";
                                        if($result_classe_etudiant && $result_classe_etudiant->num_rows > 0){
                                            $row_classe = $result_classe_etudiant->fetch_assoc();
                                            $classe_etudiant = $row_classe['classe'];
                                        }
                                ?>
                                <div class="ranking-item">
                                    <div class="d-flex align-items-center">
                                        <div class="ranking-position <?php echo $position_class; ?>">
                                            <?php echo $position; ?>
                                        </div>
                                        <div class="ml-3">
                                            <strong><?php echo str_replace("+","'",$nom); ?></strong>
                                            <div class="text-muted" style="font-size: 0.85rem;">
                                                <?php echo str_replace("+","'",$classe_etudiant); ?>
                                            </div>
                                        </div>
                                    </div>
                                    <div>
                                        <span class="badge badge-success" style="font-size: 1.1rem;">
                                            <?php echo number_format($etudiant['moyenne'], 2); ?>/20
                                        </span>
                                    </div>
                                </div>
                                <?php 
                                        $position++;
                                    }
                                } else {
                                    echo '<p class="text-muted">Aucune donnée disponible</p>';
                                }
                                ?>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Étudiants en Difficulté -->
                <div class="row">
                    <div class="col-12">
                        <div class="card border-danger">
                            <div class="card-header bg-danger text-white">
                                <h4 class="card-title mb-0">⚠️ Étudiants en Difficulté (Moyenne &lt; 10/20)</h4>
                            </div>
                            <div class="card-body">
                                <?php 
                                // Liste des étudiants en difficulté
                                $sql_difficulte_details = "SELECT inscription, AVG(moyGen) as moyenne
                                                          FROM notation
                                                          WHERE etab='$etab' 
                                                          AND annee='$annee_filter' 
                                                          AND moyGen IS NOT NULL
                                                          GROUP BY inscription 
                                                          HAVING moyenne < 10
                                                          ORDER BY moyenne ASC 
                                                          LIMIT 20";
                                $result_difficulte_details = $connexion->query($sql_difficulte_details);
                                
                                if($result_difficulte_details && $result_difficulte_details->num_rows > 0){
                                ?>
                                <div class="table-responsive">
                                    <table class="table table-hover">
                                        <thead>
                                            <tr>
                                                <th>#</th>
                                                <th>Étudiant</th>
                                                <th>Classe</th>
                                                <th>Moyenne</th>
                                                <th>Statut</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php 
                                            $num = 1;
                                            while($etudiant_diff = $result_difficulte_details->fetch_assoc()){
                                                $code_candidat = getCandidatCodeByInscription($etudiant_diff['inscription'], $connexion);
                                                $nom = obtenirNomPrenom($code_candidat, $annee_filter, $connexion);
                                                
                                                // Récupérer la classe
                                                $sql_classe = "SELECT classe FROM inscription WHERE id='".$etudiant_diff['inscription']."' LIMIT 1";
                                                $result_classe = $connexion->query($sql_classe);
                                                $classe_etud = "";
                                                if($result_classe && $result_classe->num_rows > 0){
                                                    $row_c = $result_classe->fetch_assoc();
                                                    $classe_etud = $row_c['classe'];
                                                }
                                                
                                                // Déterminer le niveau de difficulté
                                                $moyenne = $etudiant_diff['moyenne'];
                                                if($moyenne < 5){
                                                    $badge_diff = "badge-danger";
                                                    $statut = "Critique";
                                                } elseif($moyenne < 7){
                                                    $badge_diff = "badge-warning";
                                                    $statut = "Très difficile";
                                                } else {
                                                    $badge_diff = "badge-info";
                                                    $statut = "En difficulté";
                                                }
                                            ?>
                                            <tr>
                                                <td><?php echo $num; ?></td>
                                                <td><strong><?php echo str_replace("+","'",$nom); ?></strong></td>
                                                <td><?php echo str_replace("+","'",$classe_etud); ?></td>
                                                <td><strong><?php echo number_format($moyenne, 2); ?>/20</strong></td>
                                                <td><span class="badge <?php echo $badge_diff; ?>"><?php echo $statut; ?></span></td>
                                            </tr>
                                            <?php 
                                                $num++;
                                            }
                                            ?>
                                        </tbody>
                                    </table>
                                </div>
                                <?php 
                                } else {
                                    echo '<div class="alert alert-success">🎉 Aucun étudiant en difficulté ! Tous les étudiants ont une moyenne ≥ 10/20</div>';
                                }
                                ?>
                            </div>
                        </div>
                    </div>
                </div>

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

    <script>
        // Graphique de distribution des notes
        const distributionCtx = document.getElementById('distributionChart').getContext('2d');
        const distributionChart = new Chart(distributionCtx, {
            type: 'bar',
            data: {
                labels: ['Excellent (≥16)', 'Très Bien (14-16)', 'Bien (12-14)', 'Assez Bien (10-12)', 'Insuffisant (<10)'],
                datasets: [{
                    label: 'Nombre d\'étudiants',
                    data: [
                        <?php echo $distribution['excellent'] ?? 0; ?>,
                        <?php echo $distribution['tres_bien'] ?? 0; ?>,
                        <?php echo $distribution['bien'] ?? 0; ?>,
                        <?php echo $distribution['assez_bien'] ?? 0; ?>,
                        <?php echo $distribution['insuffisant'] ?? 0; ?>
                    ],
                    backgroundColor: [
                        'rgba(40, 167, 69, 0.8)',
                        'rgba(23, 162, 184, 0.8)',
                        'rgba(255, 193, 7, 0.8)',
                        'rgba(255, 152, 0, 0.8)',
                        'rgba(220, 53, 69, 0.8)'
                    ],
                    borderColor: [
                        'rgba(40, 167, 69, 1)',
                        'rgba(23, 162, 184, 1)',
                        'rgba(255, 193, 7, 1)',
                        'rgba(255, 152, 0, 1)',
                        'rgba(220, 53, 69, 1)'
                    ],
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: false
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true
                    }
                }
            }
        });

        // Graphique réussite/échec
        const reussiteCtx = document.getElementById('reussiteChart').getContext('2d');
        const reussiteChart = new Chart(reussiteCtx, {
            type: 'doughnut',
            data: {
                labels: ['Réussis', 'En Difficulté'],
                datasets: [{
                    data: [
                        <?php echo $nb_reussis; ?>,
                        <?php echo $nb_difficulte; ?>
                    ],
                    backgroundColor: [
                        'rgba(40, 167, 69, 0.8)',
                        'rgba(220, 53, 69, 0.8)'
                    ],
                    borderColor: [
                        'rgba(40, 167, 69, 1)',
                        'rgba(220, 53, 69, 1)'
                    ],
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom'
                    }
                }
            }
        });
    </script>
	
</body>
</html>

<?php 
}else{
    header("location: ../login");
}
?>
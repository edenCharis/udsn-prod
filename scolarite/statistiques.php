<?php 
include '../php/connexion.php';
include '../php/lib.php';

session_start();

if($_SESSION['id'] == session_id() && $_SESSION['role'] == "scolarité"){

// Récupérer les filtres
$classe_filtre = isset($_GET['classe']) ? $_GET['classe'] : '';
$semestre_filtre = isset($_GET['semestre']) ? $_GET['semestre'] : '';
$annee_filtre = isset($_GET['annee']) ? $_GET['annee'] : '';
$examen_filtre = isset($_GET['examen']) ? $_GET['examen'] : '';

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

    <link href="../vendor/datatables/css/jquery.dataTables.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.2.3/css/buttons.dataTables.min.css">
    
    <style>
        .etudiant-row {
            transition: background-color 0.2s;
        }
        .etudiant-row:hover {
            background-color: #f0f8ff !important;
        }
        .clickable-row {
            cursor: pointer;
        }
        .info-icon {
            margin-left: 5px;
            color: #007bff;
            font-size: 12px;
        }
        
        /* Modal pour afficher le PV */
        #pvModal .modal-dialog {
            max-width: 98%;
            margin: 1rem auto;
        }
        
        #pvModal .modal-body {
            max-height: 75vh;
            overflow-y: auto;
            padding: 20px;
        }
        
        #pvContent table {
            font-size: 10px;
        }
        
        #pvContent th, #pvContent td {
            padding: 4px;
            border: 1px solid #dee2e6;
        }
        
        /* DataTables dans le modal */
        #pvModal .dataTables_wrapper {
            padding: 10px 0;
        }
        
        #pvModal .dataTables_filter input {
            margin-left: 10px;
            padding: 5px;
        }
        
        #pvModal .dataTables_length select {
            padding: 5px;
            margin: 0 10px;
        }
        
        #pvModal .dt-buttons {
            margin-bottom: 10px;
        }
        
        #pvModal .dt-button {
            margin-right: 5px;
            padding: 5px 10px;
            font-size: 12px;
        }
        
        /* Scrolling horizontal pour grandes tables */
        #pvModal .dataTables_scroll {
            overflow-x: auto;
        }
        
        #pvModal table.dataTable {
            margin-top: 0 !important;
            margin-bottom: 10px !important;
        }
        
        .loading-spinner {
            text-align: center;
            padding: 50px;
        }
        
        .loading-spinner i {
            font-size: 48px;
            color: #007bff;
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
                            <h3>Statistiques de Publication</h3>
                        </div>
                    </div>
                    <div class="col-sm-6 p-md-0 justify-content-sm-end mt-2 mt-sm-0 d-flex">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="../scolarite/">Scolarité</a></li>
                            <li class="breadcrumb-item active">Statistiques</li>
                        </ol>
                    </div>
                </div>

                <!-- FILTRES -->
				<div class="row">
					<div class="col-lg-12">
						<div class="card">
							<div class="card-header">
								<h4 class="card-title">Filtres de recherche</h4>
							</div>
							<div class="card-body">
								<form method="GET" action="">
									<div class="row">
										<div class="col-md-3">
											<div class="form-group">
												<label>Classe</label>
												<select name="classe" class="form-control">
													<option value="">-- Toutes --</option>
													<?php 
													$sql_classes = "SELECT DISTINCT libelle FROM classe WHERE etab='".$_SESSION['etablissement']."' ORDER BY libelle";
													$result_classes = $connexion->query($sql_classes);
													while($classe = $result_classes->fetch_object()){
													?>
														<option value="<?php echo $classe->libelle; ?>" <?php echo ($classe_filtre == $classe->libelle) ? 'selected' : ''; ?>>
															<?php echo $classe->libelle; ?>
														</option>
													<?php } ?>
												</select>
											</div>
										</div>
										
										<div class="col-md-3">
											<div class="form-group">
												<label>Semestre</label>
												<select name="semestre" class="form-control">
													<option value="">-- Tous --</option>
													<?php 
													for($i = 1; $i <= 6; $i++){
														$sem = "Semestre $i";
													?>
														<option value="<?php echo $sem; ?>" <?php echo ($semestre_filtre == $sem) ? 'selected' : ''; ?>>
															<?php echo $sem; ?>
														</option>
													<?php } ?>
												</select>
											</div>
										</div>
										
										<div class="col-md-3">
											<div class="form-group">
												<label>Année Universitaire</label>
												<select name="annee" class="form-control">
													<option value="">-- Toutes --</option>
													<?php 
													$sql_annees = "SELECT DISTINCT libelle FROM annee ORDER BY libelle DESC";
													$result_annees = $connexion->query($sql_annees);
													while($annee = $result_annees->fetch_object()){
													?>
														<option value="<?php echo $annee->libelle; ?>" <?php echo ($annee_filtre == $annee->libelle) ? 'selected' : ''; ?>>
															<?php echo $annee->libelle; ?>
														</option>
													<?php } ?>
												</select>
											</div>
										</div>
										
										<div class="col-md-3">
											<div class="form-group">
												<label>Type d'Examen</label>
												<select name="examen" class="form-control">
													<option value="">-- Tous --</option>
													<option value="ordinaire" <?php echo ($examen_filtre == 'ordinaire') ? 'selected' : ''; ?>>Ordinaire</option>
													<option value="rattrapage" <?php echo ($examen_filtre == 'rattrapage') ? 'selected' : ''; ?>>Rattrapage</option>
												</select>
											</div>
										</div>
									</div>
									
									<div class="row">
										<div class="col-md-12">
											<button type="submit" class="btn btn-primary">
												<i class="fa fa-search"></i> Rechercher
											</button>
											<a href="statistiques.php" class="btn btn-secondary">
												<i class="fa fa-refresh"></i> Réinitialiser
											</a>
										</div>
									</div>
								</form>
							</div>
						</div>
					</div>
				</div>

                <?php 
                // Construire la requête avec filtres
                $where_conditions = ["inscription.etab='".$_SESSION['etablissement']."'"];
                
                if(!empty($classe_filtre)){
                    $where_conditions[] = "inscription.classe='".mysqli_real_escape_string($connexion, $classe_filtre)."'";
                }
                if(!empty($annee_filtre)){
                    $where_conditions[] = "inscription.annee='".mysqli_real_escape_string($connexion, $annee_filtre)."'";
                }
                
                $where_clause = "WHERE " . implode(" AND ", $where_conditions);
                
                // Récupérer tous les étudiants inscrits
                $sql_etudiants = "SELECT 
                    inscription.id as inscription_id,
                    inscription.classe,
                    inscription.annee,
                    candidat.code,
                    candidat.nom,
                    candidat.prenom
                FROM inscription
                JOIN candidat ON candidat.code = inscription.candidat
                $where_clause
                GROUP BY candidat.code, inscription.annee, inscription.classe
                ORDER BY inscription.classe, candidat.nom, candidat.prenom";
                
                $result_etudiants = $connexion->query($sql_etudiants);
                
                $stats_par_classe = [];
                $etudiants_non_publies = [];
                
                while($etudiant = $result_etudiants->fetch_object()){
                    $classe_key = $etudiant->classe . " - " . $etudiant->annee;
                    
                    if(!isset($stats_par_classe[$classe_key])){
                        $stats_par_classe[$classe_key] = [
                            'total' => 0,
                            'publies' => 0,
                            'non_publies' => 0,
                            'classe' => $etudiant->classe,
                            'annee' => $etudiant->annee
                        ];
                    }
                    
                    $stats_par_classe[$classe_key]['total']++;
                    
                    // Vérifier si l'étudiant a des résultats publiés
                    $sql_check = "SELECT COUNT(*) as count FROM recap 
                                  WHERE etudiant = ".$etudiant->inscription_id."
                                  AND etab = '".$_SESSION['etablissement']."'";
                    
                    if(!empty($semestre_filtre)){
                        $sql_check .= " AND semestre = '".mysqli_real_escape_string($connexion, $semestre_filtre)."'";
                    }
                    if(!empty($examen_filtre)){
                        $sql_check .= " AND examen = '".mysqli_real_escape_string($connexion, $examen_filtre)."'";
                    }
                    
                    $result_check = $connexion->query($sql_check);
                    $row_check = $result_check->fetch_object();
                    
                    if($row_check->count > 0){
                        $stats_par_classe[$classe_key]['publies']++;
                    } else {
                        $stats_par_classe[$classe_key]['non_publies']++;
                        $etudiants_non_publies[] = [
                            'classe' => $etudiant->classe,
                            'annee' => $etudiant->annee,
                            'code' => $etudiant->code,
                            'nom' => $etudiant->nom,
                            'prenom' => $etudiant->prenom,
                            'inscription_id' => $etudiant->inscription_id
                        ];
                    }
                }
                
                // Calculer les totaux globaux
                $total_global = 0;
                $publies_global = 0;
                $non_publies_global = 0;
                
                foreach($stats_par_classe as $stats){
                    $total_global += $stats['total'];
                    $publies_global += $stats['publies'];
                    $non_publies_global += $stats['non_publies'];
                }
                
                $pourcentage_publies = ($total_global > 0) ? round(($publies_global / $total_global) * 100, 1) : 0;
                ?>

                <!-- STATISTIQUES GLOBALES -->
                <div class="row">
                    <div class="col-lg-4">
                        <div class="card">
                            <div class="card-body text-center">
                                <h2 class="text-primary"><?php echo $total_global; ?></h2>
                                <p class="mb-0">Total Étudiants</p>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-lg-4">
                        <div class="card">
                            <div class="card-body text-center">
                                <h2 class="text-success"><?php echo $publies_global; ?></h2>
                                <p class="mb-0">Résultats Publiés (<?php echo $pourcentage_publies; ?>%)</p>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-lg-4">
                        <div class="card">
                            <div class="card-body text-center">
                                <h2 class="text-danger"><?php echo $non_publies_global; ?></h2>
                                <p class="mb-0">Non Publiés (<?php echo (100 - $pourcentage_publies); ?>%)</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- STATISTIQUES PAR CLASSE -->
				<div class="row">
					<div class="col-lg-12">
						<div class="card">
							<div class="card-header">
								<h4 class="card-title">Statistiques par Classe</h4>
							</div>
							<div class="card-body">
								<div class="table-responsive">
									<table id="example3" class="display" style="min-width: 845px">
										<thead>
											<tr>
												<th>Classe</th>
												<th>Année</th>
												<th>Total Étudiants</th>
												<th>Publiés</th>
												<th>Non Publiés</th>
												<th>Taux %</th>
											</tr>
										</thead>
										<tbody>
											<?php foreach($stats_par_classe as $classe_key => $stats){ 
												$taux = ($stats['total'] > 0) ? round(($stats['publies'] / $stats['total']) * 100, 1) : 0;
											?>
											<tr>
												<td><?php echo $stats['classe']; ?></td>
												<td><?php echo $stats['annee']; ?></td>
												<td><span class="badge badge-primary"><?php echo $stats['total']; ?></span></td>
												<td><span class="badge badge-success"><?php echo $stats['publies']; ?></span></td>
												<td><span class="badge badge-danger"><?php echo $stats['non_publies']; ?></span></td>
												<td><?php echo $taux; ?>%</td>
											</tr>
											<?php } ?>
											
											<?php if(empty($stats_par_classe)){ ?>
											<tr>
												<td colspan="6" class="text-center">Aucune donnée disponible</td>
											</tr>
											<?php } ?>
										</tbody>
									</table>
								</div>
							</div>
						</div>
					</div>
				</div>

                <!-- LISTE DES ÉTUDIANTS NON PUBLIÉS -->
                <?php if(!empty($etudiants_non_publies)){ ?>
				<div class="row">
					<div class="col-lg-12">
						<div class="card">
							<div class="card-header bg-danger">
								<h4 class="card-title text-white">
									Étudiants sans Résultats Publiés (<?php echo count($etudiants_non_publies); ?>)
									<i class="fa fa-info-circle info-icon" data-toggle="tooltip" title="Cliquez sur une ligne pour voir le tableau de délibération"></i>
								</h4>
							</div>
							<div class="card-body">
								<div class="table-responsive">
									<table id="example4" class="display" style="min-width: 845px">
										<thead>
											<tr>
												<th>N°</th>
												<th>Matricule</th>
												<th>Nom</th>
												<th>Prénom</th>
												<th>Classe</th>
												<th>Année</th>
												<th>Action</th>
											</tr>
										</thead>
										<tbody>
											<?php 
											$num = 1;
											foreach($etudiants_non_publies as $etudiant){ 
												// Récupérer les informations nécessaires
												$sql_info = "SELECT 
													classe.specialite,
													classe.niveau
												
												FROM classe
												WHERE classe.libelle = '".mysqli_real_escape_string($connexion, $etudiant['classe'])."'
												AND classe.etab = '".$_SESSION['etablissement']."'
												LIMIT 1";
												
												$result_info = $connexion->query($sql_info);
												$info = $result_info->fetch_object();
											?>
											<tr class="etudiant-row clickable-row" 
												data-classe="<?php echo htmlspecialchars($etudiant['classe']); ?>"
												data-annee="<?php echo htmlspecialchars($etudiant['annee']); ?>"
												data-examen="<?php echo htmlspecialchars(!empty($examen_filtre) ? $examen_filtre : 'ordinaire'); ?>"
												<?php if($info){ ?>
												data-specialite="<?php echo htmlspecialchars($info->specialite); ?>"
												data-niveau="<?php echo htmlspecialchars($info->niveau); ?>"
												data-semestre="<?php echo isset($semestre_filtre)? $semestre_filtre : ""; ?>"
												<?php } ?>
												data-etablissement="<?php echo htmlspecialchars($_SESSION['etablissement']); ?>">
												<td><?php echo $num++; ?></td>
												<td><?php echo $etudiant['code']; ?></td>
												<td><?php echo strtoupper($etudiant['nom']); ?></td>
												<td><?php echo mettrePremieresLettresMajuscules($etudiant['prenom']); ?></td>
												<td><?php echo $etudiant['classe']; ?></td>
												<td><?php echo $etudiant['annee']; ?></td>
												<td>
													<button class="btn btn-primary btn-sm btn-voir-pv">
														<i class="fa fa-eye"></i> Voir PV
													</button>
												</td>
											</tr>
											<?php } ?>
										</tbody>
									</table>
								</div>
								<div class="alert alert-info mt-3">
									<i class="fa fa-info-circle"></i> 
									<strong>Astuce :</strong> Cliquez sur n'importe quelle ligne ou sur le bouton "Voir PV" pour afficher le tableau de délibération de la classe correspondante dans une fenêtre modale.
								</div>
							</div>
						</div>
					</div>
				</div>
                <?php } ?>
				
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

    </div>
    
    <!--**********************************
        Main wrapper end
    ***********************************-->

    <!-- Modal pour afficher le PV -->
    <div class="modal fade" id="pvModal" tabindex="-1" role="dialog" aria-labelledby="pvModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-xl" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="pvModalLabel">Procès Verbal de Délibération</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body" id="pvContent">
                    <div class="loading-spinner">
                        <i class="fa fa-spinner fa-spin"></i>
                        <p>Chargement du procès verbal...</p>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Fermer</button>
                    <button type="button" class="btn btn-primary" id="btnPublierResultatsModal">
                        <i class="fa fa-paper-plane"></i> Publier les résultats
                    </button>
                </div>
            </div>
        </div>
    </div>

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
		
	
	<!-- Chart piety plugin files -->
    <script src="../vendor/peity/jquery.peity.min.js"></script>
	
		<!-- Demo scripts -->
    <script src="../js/dashboard/dashboard-2.js"></script>
    		
    <script src="../vendor/select2/js/select2.full.min.js"></script>
    <script src="../js/plugins-init/select2-init.js"></script>
	
	<!-- Svganimation scripts -->
    <script src="../vendor/svganimation/vivus.min.js"></script>
    <script src="../vendor/svganimation/svg.animation.js"></script>
    <script src="../vendor/datatables/js/jquery.dataTables.min.js"></script>
    <script src="../js/plugins-init/datatables.init.js"></script>
    
    <!-- DataTables Buttons -->
    <script src="https://cdn.datatables.net/buttons/2.2.3/js/dataTables.buttons.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.1.3/jszip.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/pdfmake.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/vfs_fonts.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.2.3/js/buttons.html5.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.2.3/js/buttons.print.min.js"></script>
    
    <script>
    $(document).ready(function() {
        // Initialiser les tooltips
        $('[data-toggle="tooltip"]').tooltip();
        
        // Variable globale pour stocker les paramètres du PV actuel
        var currentPVParams = {};
        
        // Fonction pour charger le PV via AJAX
        function chargerPV(params) {
            // Stocker les paramètres pour la publication
            currentPVParams = params;
            
            // Afficher le modal avec le spinner
            $('#pvModal').modal('show');
            $('#pvContent').html('<div class="loading-spinner"><i class="fa fa-spinner fa-spin"></i><p>Chargement du procès verbal...</p></div>');
            
            // Requête AJAX
            $.ajax({
                url: 'get_pv_deliberation.php',
                type: 'POST',
                data: params,
                success: function(response) {
                    $('#pvContent').html(response);
                    
                    // Initialiser DataTables après le chargement du contenu
                    setTimeout(function() {
                        if($('#pvTable').length > 0) {
                            $('#pvTable').DataTable({
                                "language": {
                                    "url": "//cdn.datatables.net/plug-ins/1.10.24/i18n/French.json",
                                    "search": "Rechercher :",
                                    "lengthMenu": "Afficher _MENU_ étudiants",
                                    "info": "Affichage de _START_ à _END_ sur _TOTAL_ étudiants",
                                    "infoEmpty": "Aucun étudiant",
                                    "infoFiltered": "(filtré de _MAX_ étudiants au total)",
                                    "zeroRecords": "Aucun étudiant trouvé",
                                    "emptyTable": "Aucune donnée disponible"
                                },
                                "pageLength": 50,
                                "lengthMenu": [[10, 25, 50, 100, -1], [10, 25, 50, 100, "Tous"]],
                                "ordering": true,
                                "order": [[0, 'asc']], // Tri par nom par défaut
                                "searching": true,
                                "info": true,
                                "paging": true,
                                "autoWidth": false,
                                "scrollX": true,
                                "scrollCollapse": true,
                                "columnDefs": [
                                    {
                                        "targets": 0,
                                        "width": "200px",
                                        "className": "text-left"
                                    }
                                ],
                                "dom": '<"row"<"col-sm-12 col-md-6"l><"col-sm-12 col-md-6"f>>' +
                                       '<"row"<"col-sm-12"B>>' +
                                       '<"row"<"col-sm-12"tr>>' +
                                       '<"row"<"col-sm-12 col-md-5"i><"col-sm-12 col-md-7"p>>',
                                "buttons": [
                                    {
                                        extend: 'excel',
                                        text: '<i class="fa fa-file-excel-o"></i> Excel',
                                        className: 'btn btn-success btn-sm',
                                        exportOptions: {
                                            columns: ':visible'
                                        }
                                    },
                                    {
                                        extend: 'pdf',
                                        text: '<i class="fa fa-file-pdf-o"></i> PDF',
                                        className: 'btn btn-danger btn-sm',
                                        orientation: 'landscape',
                                        pageSize: 'A3',
                                        exportOptions: {
                                            columns: ':visible'
                                        },
                                        customize: function(doc) {
                                            doc.styles.tableHeader.fontSize = 8;
                                            doc.defaultStyle.fontSize = 7;
                                        }
                                    },
                                    {
                                        extend: 'print',
                                        text: '<i class="fa fa-print"></i> Imprimer',
                                        className: 'btn btn-info btn-sm',
                                        exportOptions: {
                                            columns: ':visible'
                                        }
                                    },
                                    {
                                        extend: 'colvis',
                                        text: '<i class="fa fa-columns"></i> Colonnes',
                                        className: 'btn btn-secondary btn-sm'
                                    }
                                ]
                            });
                        }
                    }, 100);
                },
                error: function(xhr, status, error) {
                    $('#pvContent').html(
                        '<div class="alert alert-danger">' +
                        '<h5><i class="fa fa-exclamation-triangle"></i> Erreur de chargement</h5>' +
                        '<p>Impossible de charger le procès verbal. Veuillez réessayer.</p>' +
                        '<p><small>Erreur technique : ' + error + '</small></p>' +
                        '</div>'
                    );
                }
            });
        }
        
        // Gestion du clic sur les lignes d'étudiants
        $('.clickable-row').on('click', function(e) {
            // Ne pas déclencher si on clique sur le bouton
            if($(e.target).closest('.btn-voir-pv').length > 0) {
                return;
            }
            
            var params = {
                classe: $(this).data('classe'),
                annee: $(this).data('annee'),
                examen: $(this).data('examen'),
                specialite: $(this).data('specialite'),
                niveau: $(this).data('niveau'),
                semestre: $(this).data('semestre'),
                etablissement: $(this).data('etablissement')
            };
            
            // Vérifier que toutes les données nécessaires sont présentes
            if(!params.specialite || !params.niveau || !params.semestre) {
                alert('Informations de classe incomplètes. Impossible de charger le PV.');
                return;
            }
            
            chargerPV(params);
        });
        
        // Gestion du clic sur le bouton "Voir PV"
        $('.btn-voir-pv').on('click', function(e) {
            e.stopPropagation();
            
            var row = $(this).closest('tr');
            var params = {
                classe: row.data('classe'),
                annee: row.data('annee'),
                examen: row.data('examen'),
                specialite: row.data('specialite'),
                niveau: row.data('niveau'),
                semestre: row.data('semestre'),
                etablissement: row.data('etablissement')
            };
            
            // Vérifier que toutes les données nécessaires sont présentes
            if(!params.specialite || !params.niveau || !params.semestre) {
                alert('Informations de classe incomplètes. Impossible de charger le PV.');
                return;
            }
            
            chargerPV(params);
        });
        
        // Gestion de la publication des résultats depuis le modal
        $('#btnPublierResultatsModal').on('click', function() {
            if(!currentPVParams.classe) {
                alert('Aucun PV chargé');
                return;
            }
            
            if(!confirm('Êtes-vous sûr de vouloir publier ces résultats ? Les étudiants pourront les consulter.')) {
                return;
            }
            
            $(this).prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> Publication en cours...');
            
            $.ajax({
                url: '../pvd/publier_resultats.php',
                type: 'POST',
                data: currentPVParams,
                dataType: 'json',
                success: function(response) {
                    if(response.success) {
                        alert(response.message);
                        $('#btnPublierResultatsModal').prop('disabled', false).html('<i class="fa fa-check"></i> Résultats publiés');
                        // Recharger la page pour mettre à jour les statistiques
                        setTimeout(function() {
                            location.reload();
                        }, 1500);
                    } else {
                        alert('Erreur : ' + response.message);
                        $('#btnPublierResultatsModal').prop('disabled', false).html('<i class="fa fa-paper-plane"></i> Publier les résultats');
                    }
                },
                error: function(xhr, status, error) {
                    console.log('XHR:', xhr);
                    console.log('Status:', status);
                    console.log('Error:', error);
                    console.log('Response Text:', xhr.responseText);
                    
                    alert('Erreur lors de la publication des résultats\n\n' + 
                          'Statut: ' + status + '\n' +
                          'Erreur: ' + error);
                    
                    $('#btnPublierResultatsModal').prop('disabled', false).html('<i class="fa fa-paper-plane"></i> Publier les résultats');
                }
            });
        });
        
        // Réinitialiser le contenu du modal lors de sa fermeture
        $('#pvModal').on('hidden.bs.modal', function() {
            // Détruire DataTable s'il existe
            if($.fn.DataTable.isDataTable('#pvTable')) {
                $('#pvTable').DataTable().destroy();
            }
            
            $('#pvContent').html('<div class="loading-spinner"><i class="fa fa-spinner fa-spin"></i><p>Chargement du procès verbal...</p></div>');
            $('#btnPublierResultatsModal').prop('disabled', false).html('<i class="fa fa-paper-plane"></i> Publier les résultats');
        });
    });
    </script>

	
</body>
</html>

<?php 

}else{
    header("location: ../login");
}?>
<?php 
include '../php/connexion.php';
include '../php/lib.php';

session_start();

if($_SESSION['id'] == session_id() && $_SESSION['role'] == "scolarité"){

// Récupérer les filtres
$annee_filtre = isset($_GET['annee']) ? $_GET['annee'] : '';
$semestre_filtre = isset($_GET['semestre']) ? $_GET['semestre'] : '';
$examen_filtre = isset($_GET['examen']) ? $_GET['examen'] : 'ordinaire';

?>

<!DOCTYPE html>
<html lang="fr">

<head>
	
	<meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title><?php echo $_SESSION['univ'];?> - Statistiques détaillées</title>
    <!-- Favicon icon -->
    <link rel="icon" type="image/png" sizes="16x16" href="../administrateur/<?php echo  $_SESSION['logo_univ']?>">
	<link rel="stylesheet" href="../vendor/bootstrap-select/dist/css/bootstrap-select.min.css">
    <link rel="stylesheet" href="../vendor/select2/css/select2.min.css">
    <link rel="stylesheet" href="../css/style.css">
	<link rel="stylesheet" href="../css/skin.css">
    <link href="../vendor/datatables/css/jquery.dataTables.min.css" rel="stylesheet">

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
            Header start
        ***********************************-->
        <?php include "header.php" ;?>
        <!--**********************************
            Header end
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
				
				<div class="row page-titles mx-0 no-print">
                    <div class="col-sm-6 p-md-0">
                        <div class="welcome-text">
                            <h3>Statistiques Détaillées des Examens</h3>
                        </div>
                    </div>
                    <div class="col-sm-6 p-md-0 justify-content-sm-end mt-2 mt-sm-0 d-flex">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="../scolarite/">Scolarité</a></li>
                            <li class="breadcrumb-item active">Statistiques Détaillées</li>
                        </ol>
                    </div>
                </div>

                <!-- FILTRES -->
				<div class="row no-print">
					<div class="col-lg-12">
						<div class="card">
							<div class="card-header">
								<h4 class="card-title">Paramètres du rapport</h4>
							</div>
							<div class="card-body">
								<form method="GET" action="">
									<div class="row">
										<div class="col-md-4">
											<div class="form-group">
												<label>Année Universitaire *</label>
												<select name="annee" class="form-control" required>
													<option value="">-- Sélectionner --</option>
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
										
										<div class="col-md-4">
											<div class="form-group">
												<label>Semestre(s)</label>
												<select name="semestre" class="form-control">
													<option value="">-- Tous les semestres impairs --</option>
													<option value="Semestre 1" <?php echo ($semestre_filtre == 'Semestre 1') ? 'selected' : ''; ?>>Semestre 1</option>
													<option value="Semestre 3" <?php echo ($semestre_filtre == 'Semestre 3') ? 'selected' : ''; ?>>Semestre 3</option>
													<option value="Semestre 5" <?php echo ($semestre_filtre == 'Semestre 5') ? 'selected' : ''; ?>>Semestre 5</option>
												</select>
											</div>
										</div>
										
										<div class="col-md-4">
											<div class="form-group">
												<label>Type d'Examen</label>
												<select name="examen" class="form-control">
													<option value="ordinaire" <?php echo ($examen_filtre == 'ordinaire') ? 'selected' : ''; ?>>Ordinaire</option>
													<option value="rattrapage" <?php echo ($examen_filtre == 'rattrapage') ? 'selected' : ''; ?>>Rattrapage</option>
												</select>
											</div>
										</div>
									</div>
									
									<div class="row">
										<div class="col-md-12">
											<button type="submit" class="btn btn-primary">
												<i class="fa fa-bar-chart"></i> Générer les statistiques
											</button>
											<a href="stats_detaillees.php" class="btn btn-secondary">
												<i class="fa fa-refresh"></i> Réinitialiser
											</a>
											<?php if(!empty($annee_filtre)){ ?>
											<button type="button" class="btn btn-success" onclick="exporterExcel()">
												<i class="fa fa-file-excel-o"></i> Exporter Excel
											</button>
											<button type="button" class="btn btn-info" onclick="window.print()">
												<i class="fa fa-print"></i> Imprimer
											</button>
											<?php } ?>
										</div>
									</div>
								</form>
							</div>
						</div>
					</div>
				</div>

                <?php if(!empty($annee_filtre)){ 
                    
                    // Déterminer les semestres à traiter
                    $semestres_a_traiter = [];
                    if(empty($semestre_filtre)){
                        $semestres_a_traiter = ['Semestre 1', 'Semestre 3', 'Semestre 5'];
                    } else {
                        $semestres_a_traiter = [$semestre_filtre];
                    }
                    
                    $semestres_str = implode(', ', $semestres_a_traiter);
                    
                ?>

                <!-- TITRE DU RAPPORT -->
                <div class="row">
                    <div class="col-lg-12">
                        <div class="card">
                            <div class="card-body text-center">
                                <h4><strong>RÉSULTATS STATISTIQUES DES EXAMENS DE LA SESSION <?php echo strtoupper($examen_filtre); ?></strong></h4>
                                <h5><?php echo $semestres_str; ?></h5>
                                <p>Année académique <?php echo $annee_filtre; ?></p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- TABLEAU DES STATISTIQUES -->
				<div class="row">
					<div class="col-lg-12">
						<div class="card">
							<div class="card-body">
								<div class="table-responsive">
									<table id="statsTable" class="table table-bordered table-striped" style="width: 100%;">
										<thead>
											<tr>
												<th rowspan="2" class="align-middle text-center">Niveau</th>
												<th colspan="3" class="text-center">Inscrits</th>
												<th colspan="3" class="text-center">Présents</th>
												<th colspan="3" class="text-center">Admis</th>
												<th colspan="3" class="text-center">Ajournés</th>
												<th colspan="3" class="text-center">% Admis</th>
											</tr>
											<tr>
												<th class="text-center">F</th>
												<th class="text-center">G</th>
												<th class="text-center">T</th>
												<th class="text-center">F</th>
												<th class="text-center">G</th>
												<th class="text-center">T</th>
												<th class="text-center">F</th>
												<th class="text-center">G</th>
												<th class="text-center">T</th>
												<th class="text-center">F</th>
												<th class="text-center">G</th>
												<th class="text-center">T</th>
												<th class="text-center">F</th>
												<th class="text-center">G</th>
												<th class="text-center">T</th>
											</tr>
										</thead>
										<tbody>
											<?php 
											// Récupérer toutes les classes groupées par niveau
											$sql_classes = "SELECT DISTINCT 
											    classe.libelle as classe,
											    classe.niveau,
											    classe.semestre,
											    classe.specialite
											FROM classe
											WHERE classe.etab = '".$_SESSION['etablissement']."'
											ORDER BY classe.niveau, classe.specialite";
											
											$result_classes = $connexion->query($sql_classes);
											
											$stats_par_niveau = [];
											$total_general = [
											    'inscrits_f' => 0, 'inscrits_g' => 0, 'inscrits_t' => 0,
											    'presents_f' => 0, 'presents_g' => 0, 'presents_t' => 0,
											    'admis_f' => 0, 'admis_g' => 0, 'admis_t' => 0,
											    'ajournes_f' => 0, 'ajournes_g' => 0, 'ajournes_t' => 0
											];
											
											while($classe_row = $result_classes->fetch_object()){
											    $classe = $classe_row->classe;
											    $niveau = $classe_row->niveau;
											    $semestre = $classe_row->semestre;
											    $specialite = $classe_row->specialite;
											    
											    // Vérifier si le semestre est dans la liste
											    if(!in_array($semestre, $semestres_a_traiter)){
											        continue;
											    }
											    
											    // Initialiser le niveau s'il n'existe pas
											    $niveau_key = $niveau . ' - ' . $specialite;
											    if(!isset($stats_par_niveau[$niveau_key])){
											        $stats_par_niveau[$niveau_key] = [
											            'niveau' => $niveau,
											            'specialite' => $specialite,
											            'inscrits_f' => 0, 'inscrits_g' => 0, 'inscrits_t' => 0,
											            'presents_f' => 0, 'presents_g' => 0, 'presents_t' => 0,
											            'admis_f' => 0, 'admis_g' => 0, 'admis_t' => 0,
											            'ajournes_f' => 0, 'ajournes_g' => 0, 'ajournes_t' => 0
											        ];
											    }
											    
											    // Récupérer les étudiants inscrits dans cette classe
											    $sql_inscrits = "SELECT 
											        candidat.sexe,
											        inscription.id as inscription_id
											    FROM inscription
											    JOIN candidat ON candidat.code = inscription.candidat
											    WHERE inscription.classe = '".mysqli_real_escape_string($connexion, $classe)."'
											    AND inscription.annee = '".mysqli_real_escape_string($connexion, $annee_filtre)."'
											    AND inscription.etab = '".$_SESSION['etablissement']."'";
											    
											    $result_inscrits = $connexion->query($sql_inscrits);
											    
											    while($etudiant = $result_inscrits->fetch_object()){
											        $sexe = strtoupper($etudiant->sexe);
											        
											        // Inscrits
											        if($sexe == 'F' || $sexe == 'FEMININ' || $sexe == 'FÉMININ'){
											            $stats_par_niveau[$niveau_key]['inscrits_f']++;
											            $total_general['inscrits_f']++;
											        } else {
											            $stats_par_niveau[$niveau_key]['inscrits_g']++;
											            $total_general['inscrits_g']++;
											        }
											        $stats_par_niveau[$niveau_key]['inscrits_t']++;
											        $total_general['inscrits_t']++;
											        
											        // Vérifier si l'étudiant a des résultats (donc présent)
											        $sql_check_present = "SELECT COUNT(*) as count FROM recap 
											            WHERE etudiant = ".$etudiant->inscription_id."
											            AND semestre = '".mysqli_real_escape_string($connexion, $semestre)."'
											            AND annee = '".mysqli_real_escape_string($connexion, $annee_filtre)."'
											            AND examen = '".mysqli_real_escape_string($connexion, $examen_filtre)."'
											            AND etab = '".$_SESSION['etablissement']."'";
											        
											        $result_present = $connexion->query($sql_check_present);
											        $row_present = $result_present->fetch_object();
											        
											        if($row_present->count > 0){
											            // Présent
											            if($sexe == 'F' || $sexe == 'FEMININ' || $sexe == 'FÉMININ'){
											                $stats_par_niveau[$niveau_key]['presents_f']++;
											                $total_general['presents_f']++;
											            } else {
											                $stats_par_niveau[$niveau_key]['presents_g']++;
											                $total_general['presents_g']++;
											            }
											            $stats_par_niveau[$niveau_key]['presents_t']++;
											            $total_general['presents_t']++;
											            
											            // Vérifier si admis ou ajourné
											            $sql_decision = "SELECT decision FROM recap 
											                WHERE etudiant = ".$etudiant->inscription_id."
											                AND semestre = '".mysqli_real_escape_string($connexion, $semestre)."'
											                AND annee = '".mysqli_real_escape_string($connexion, $annee_filtre)."'
											                AND examen = '".mysqli_real_escape_string($connexion, $examen_filtre)."'
											                AND etab = '".$_SESSION['etablissement']."'
											                LIMIT 1";
											            
											            $result_decision = $connexion->query($sql_decision);
											            $row_decision = $result_decision->fetch_object();
											            
											            if($row_decision){
											                $decision = $row_decision->decision;
											                
											                if(stripos($decision, 'Admis') !== false){
											                    // Admis
											                    if($sexe == 'F' || $sexe == 'FEMININ' || $sexe == 'FÉMININ'){
											                        $stats_par_niveau[$niveau_key]['admis_f']++;
											                        $total_general['admis_f']++;
											                    } else {
											                        $stats_par_niveau[$niveau_key]['admis_g']++;
											                        $total_general['admis_g']++;
											                    }
											                    $stats_par_niveau[$niveau_key]['admis_t']++;
											                    $total_general['admis_t']++;
											                } else {
											                    // Ajourné
											                    if($sexe == 'F' || $sexe == 'FEMININ' || $sexe == 'FÉMININ'){
											                        $stats_par_niveau[$niveau_key]['ajournes_f']++;
											                        $total_general['ajournes_f']++;
											                    } else {
											                        $stats_par_niveau[$niveau_key]['ajournes_g']++;
											                        $total_general['ajournes_g']++;
											                    }
											                    $stats_par_niveau[$niveau_key]['ajournes_t']++;
											                    $total_general['ajournes_t']++;
											                }
											            }
											        }
											    }
											}
											
											// Afficher les statistiques par niveau
											$current_niveau = '';
											$subtotals = [];
											
											foreach($stats_par_niveau as $key => $stats){
											    // Calculer les pourcentages
											    $pct_f = ($stats['presents_f'] > 0) ? round(($stats['admis_f'] / $stats['presents_f']) * 100, 1) : 0;
											    $pct_g = ($stats['presents_g'] > 0) ? round(($stats['admis_g'] / $stats['presents_g']) * 100, 1) : 0;
											    $pct_t = ($stats['presents_t'] > 0) ? round(($stats['admis_t'] / $stats['presents_t']) * 100, 1) : 0;
											    
											    // Déterminer le niveau principal (L1, L2, L3)
											    $niveau_principal = '';
											    if(stripos($stats['niveau'], 'Première') !== false || stripos($stats['niveau'], '1') !== false){
											        $niveau_principal = 'Licence 1';
											    } elseif(stripos($stats['niveau'], 'Deuxième') !== false || stripos($stats['niveau'], '2') !== false){
											        $niveau_principal = 'Licence 2';
											    } elseif(stripos($stats['niveau'], 'Troisième') !== false || stripos($stats['niveau'], '3') !== false){
											        $niveau_principal = 'Licence 3';
											    }
											    
											    // Initialiser le sous-total si nécessaire
											    if(!isset($subtotals[$niveau_principal])){
											        $subtotals[$niveau_principal] = [
											            'inscrits_f' => 0, 'inscrits_g' => 0, 'inscrits_t' => 0,
											            'presents_f' => 0, 'presents_g' => 0, 'presents_t' => 0,
											            'admis_f' => 0, 'admis_g' => 0, 'admis_t' => 0,
											            'ajournes_f' => 0, 'ajournes_g' => 0, 'ajournes_t' => 0
											        ];
											    }
											    
											    // Ajouter au sous-total
											    foreach(['inscrits', 'presents', 'admis', 'ajournes'] as $type){
											        foreach(['f', 'g', 't'] as $sexe){
											            $subtotals[$niveau_principal][$type.'_'.$sexe] += $stats[$type.'_'.$sexe];
											        }
											    }
											    
											    // Afficher la ligne
											    ?>
											    <tr>
											        <td class="text-left"><?php echo $niveau_principal . ' ' . $stats['specialite']; ?></td>
											        <td class="text-center"><?php echo $stats['inscrits_f']; ?></td>
											        <td class="text-center"><?php echo $stats['inscrits_g']; ?></td>
											        <td class="text-center"><?php echo $stats['inscrits_t']; ?></td>
											        <td class="text-center"><?php echo $stats['presents_f']; ?></td>
											        <td class="text-center"><?php echo $stats['presents_g']; ?></td>
											        <td class="text-center"><?php echo $stats['presents_t']; ?></td>
											        <td class="text-center"><?php echo $stats['admis_f']; ?></td>
											        <td class="text-center"><?php echo $stats['admis_g']; ?></td>
											        <td class="text-center"><?php echo $stats['admis_t']; ?></td>
											        <td class="text-center"><?php echo $stats['ajournes_f']; ?></td>
											        <td class="text-center"><?php echo $stats['ajournes_g']; ?></td>
											        <td class="text-center"><?php echo $stats['ajournes_t']; ?></td>
											        <td class="text-center"><?php echo $pct_f; ?>%</td>
											        <td class="text-center"><?php echo $pct_g; ?>%</td>
											        <td class="text-center"><?php echo $pct_t; ?>%</td>
											    </tr>
											    <?php
											}
											
											// Afficher les sous-totaux par niveau
											foreach($subtotals as $niveau => $subtotal){
											    $pct_f = ($subtotal['presents_f'] > 0) ? round(($subtotal['admis_f'] / $subtotal['presents_f']) * 100, 1) : 0;
											    $pct_g = ($subtotal['presents_g'] > 0) ? round(($subtotal['admis_g'] / $subtotal['presents_g']) * 100, 1) : 0;
											    $pct_t = ($subtotal['presents_t'] > 0) ? round(($subtotal['admis_t'] / $subtotal['presents_t']) * 100, 1) : 0;
											    ?>
											    <tr class="table-success">
											        <td class="text-left"><strong>Sous-total (<?php echo $niveau; ?>)</strong></td>
											        <td class="text-center"><strong><?php echo $subtotal['inscrits_f']; ?></strong></td>
											        <td class="text-center"><strong><?php echo $subtotal['inscrits_g']; ?></strong></td>
											        <td class="text-center"><strong><?php echo $subtotal['inscrits_t']; ?></strong></td>
											        <td class="text-center"><strong><?php echo $subtotal['presents_f']; ?></strong></td>
											        <td class="text-center"><strong><?php echo $subtotal['presents_g']; ?></strong></td>
											        <td class="text-center"><strong><?php echo $subtotal['presents_t']; ?></strong></td>
											        <td class="text-center"><strong><?php echo $subtotal['admis_f']; ?></strong></td>
											        <td class="text-center"><strong><?php echo $subtotal['admis_g']; ?></strong></td>
											        <td class="text-center"><strong><?php echo $subtotal['admis_t']; ?></strong></td>
											        <td class="text-center"><strong><?php echo $subtotal['ajournes_f']; ?></strong></td>
											        <td class="text-center"><strong><?php echo $subtotal['ajournes_g']; ?></strong></td>
											        <td class="text-center"><strong><?php echo $subtotal['ajournes_t']; ?></strong></td>
											        <td class="text-center"><strong><?php echo $pct_f; ?>%</strong></td>
											        <td class="text-center"><strong><?php echo $pct_g; ?>%</strong></td>
											        <td class="text-center"><strong><?php echo $pct_t; ?>%</strong></td>
											    </tr>
											    <?php
											}
											
											// Total général
											$pct_total_f = ($total_general['presents_f'] > 0) ? round(($total_general['admis_f'] / $total_general['presents_f']) * 100, 1) : 0;
											$pct_total_g = ($total_general['presents_g'] > 0) ? round(($total_general['admis_g'] / $total_general['presents_g']) * 100, 1) : 0;
											$pct_total_t = ($total_general['presents_t'] > 0) ? round(($total_general['admis_t'] / $total_general['presents_t']) * 100, 1) : 0;
											?>
											<tr class="table-info">
											    <td class="text-left"><strong>TOTAL GÉNÉRAL</strong></td>
											    <td class="text-center"><strong><?php echo $total_general['inscrits_f']; ?></strong></td>
											    <td class="text-center"><strong><?php echo $total_general['inscrits_g']; ?></strong></td>
											    <td class="text-center"><strong><?php echo $total_general['inscrits_t']; ?></strong></td>
											    <td class="text-center"><strong><?php echo $total_general['presents_f']; ?></strong></td>
											    <td class="text-center"><strong><?php echo $total_general['presents_g']; ?></strong></td>
											    <td class="text-center"><strong><?php echo $total_general['presents_t']; ?></strong></td>
											    <td class="text-center"><strong><?php echo $total_general['admis_f']; ?></strong></td>
											    <td class="text-center"><strong><?php echo $total_general['admis_g']; ?></strong></td>
											    <td class="text-center"><strong><?php echo $total_general['admis_t']; ?></strong></td>
											    <td class="text-center"><strong><?php echo $total_general['ajournes_f']; ?></strong></td>
											    <td class="text-center"><strong><?php echo $total_general['ajournes_g']; ?></strong></td>
											    <td class="text-center"><strong><?php echo $total_general['ajournes_t']; ?></strong></td>
											    <td class="text-center"><strong><?php echo $pct_total_f; ?>%</strong></td>
											    <td class="text-center"><strong><?php echo $pct_total_g; ?>%</strong></td>
											    <td class="text-center"><strong><?php echo $pct_total_t; ?>%</strong></td>
											</tr>
										</tbody>
									</table>
								</div>
							</div>
						</div>
					</div>
				</div>

                <?php } else { ?>
                <!-- MESSAGE SI AUCUN FILTRE -->
                <div class="row">
                    <div class="col-lg-12">
                        <div class="alert alert-info">
                            <h5><i class="fa fa-info-circle"></i> Information</h5>
                            <p>Veuillez sélectionner une année universitaire pour générer les statistiques.</p>
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

    <!--**********************************
        Scripts
    ***********************************-->
    <!-- Required vendors -->
    <script src="../vendor/global/global.min.js"></script>
	<script src="../vendor/bootstrap-select/dist/js/bootstrap-select.min.js"></script>
	<script src="../js/custom.min.js"></script>
    <script src="../vendor/datatables/js/jquery.dataTables.min.js"></script>
    
    <!-- SheetJS pour l'export Excel -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.18.5/xlsx.full.min.js"></script>
    
    <script>
    function exporterExcel() {
        // Récupérer le tableau
        var table = document.getElementById('statsTable');
        
        // Convertir en workbook
        var wb = XLSX.utils.table_to_book(table, {sheet: "Statistiques"});
        
        // Générer le nom du fichier
        var filename = 'Statistiques_<?php echo str_replace([' ', '/'], ['_', '-'], $annee_filtre); ?>_<?php echo $examen_filtre; ?>.xlsx';
        
        // Télécharger
        XLSX.writeFile(wb, filename);
    }
    </script>

	
</body>
</html>

<?php 

}else{
    header("location: ../login");
}?>
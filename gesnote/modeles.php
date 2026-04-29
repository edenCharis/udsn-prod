<?php 
include '../php/connexion.php';
include '../php/lib.php';

session_start();

if($_SESSION['id'] == session_id() && $_SESSION['role'] == "gesnote"){

 // Récupérer les filtres
$annee_filtre = isset($_GET['annee']) ? $_GET['annee'] : '';
$semestre_filtre = isset($_GET['semestre']) ? $_GET['semestre'] : '';
$examen_filtre = isset($_GET['examen']) ? $_GET['examen'] : 'ordinaire';

?>
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
    
    <style>
        /* Styles pour les en-têtes de parcours */
        .parcours-header {
            background-color: #f8f9fa !important;
            font-weight: bold;
            font-style: italic;
            border-left: 4px solid #007bff !important;
        }
        
        .niveau-header {
            background-color: #e9ecef !important;
            font-weight: bold;
            font-size: 1.1em;
        }
        
        .specialite-row td:first-child {
            padding-left: 30px !important;
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
													<option value="">-- Tous les semestres --</option>
													<option value="impairs" <?php echo ($semestre_filtre == 'impairs') ? 'selected' : ''; ?>>Semestres Impairs (1, 3, 5)</option>
													<option value="pairs" <?php echo ($semestre_filtre == 'pairs') ? 'selected' : ''; ?>>Semestres Pairs (2, 4, 6)</option>
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
											<a href="modeles.php" class="btn btn-secondary">
												<i class="fa fa-refresh"></i> Réinitialiser
											</a>
											<?php if(!empty($annee_filtre)){ ?>
											<button type="button" class="btn btn-danger" onclick="ouvrirImpression()">
												<i class="fa fa-print"></i> Imprimer le Rapport
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
                    
                    // Déterminer les semestres à traiter selon le filtre
                    $semestres_a_traiter = [];
                    if(empty($semestre_filtre)){
                        // Par défaut : tous les semestres (ou impairs selon votre logique)
                        $semestres_a_traiter = ['semestre 1', 'semestre 3', 'semestre 5'];
                        $semestres_libelle = "Semestres Impairs (1, 3, 5)";
                    } elseif($semestre_filtre == 'impairs'){
                        $semestres_a_traiter = ['semestre 1', 'semestre 3', 'semestre 5'];
                        $semestres_libelle = "Semestres Impairs (1, 3, 5)";
                    } elseif($semestre_filtre == 'pairs'){
                        $semestres_a_traiter = ['semestre 2', 'semestre 4', 'semestre 6'];
                        $semestres_libelle = "Semestres Pairs (2, 4, 6)";
                    }
                    
                ?>

                <!-- TITRE DU RAPPORT -->
                <div class="row">
                    <div class="col-lg-12">
                        <div class="card">
                            <div class="card-body text-center">
                                <h4><strong>RÉSULTATS STATISTIQUES DES EXAMENS DE LA SESSION <?php echo strtoupper($examen_filtre); ?></strong></h4>
                                <h5><?php echo $semestres_libelle; ?></h5>
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
									<table id="statsTable" class="table table-bordered table-striped" style="min-width: 845px">
										<thead>
											<tr>
												<th rowspan="2" class="align-middle text-center">Niveau / Parcours / Spécialité</th>
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
											// Créer la liste des semestres pour la clause IN
											$semestres_in = "'" . implode("', '", $semestres_a_traiter) . "'";
											
											// Récupérer toutes les classes avec parcours
											$sql_classes = "SELECT  
											    c.libelle as classe,
											    c.niveau as niveau,
											    c.specialite as specialite,
											    p.libelle as parcours
											FROM classe c  
											LEFT JOIN  specialite s on c.specialite=s.libelle join parcours p ON s.parcours = p.libelle
											WHERE c.etab = '".$_SESSION['etablissement']."'
											GROUP BY c.libelle, c.niveau, c.specialite, p.libelle
											ORDER BY c.niveau, p.libelle, c.specialite";
											
											$result_classes = $connexion->query($sql_classes);
											
											$stats_par_niveau_parcours = [];
											$total_general = [
											    'inscrits_f' => 0, 'inscrits_g' => 0, 'inscrits_t' => 0,
											    'presents_f' => 0, 'presents_g' => 0, 'presents_t' => 0,
											    'admis_f' => 0, 'admis_g' => 0, 'admis_t' => 0,
											    'ajournes_f' => 0, 'ajournes_g' => 0, 'ajournes_t' => 0
											];
											
											while($classe_row = $result_classes->fetch_object()){
											    $classe = $classe_row->classe;
											    $niveau = $classe_row->niveau;
											    $specialite = $classe_row->specialite;
											    $parcours = $classe_row->parcours ? $classe_row->parcours : 'Sans parcours';
											    
											    $niveau_key = $niveau;
											    $parcours_key = $parcours;
											    
											    if(!isset($stats_par_niveau_parcours[$niveau_key])){
											        $stats_par_niveau_parcours[$niveau_key] = [];
											    }
											    
											    if(!isset($stats_par_niveau_parcours[$niveau_key][$parcours_key])){
											        $stats_par_niveau_parcours[$niveau_key][$parcours_key] = [];
											    }
											    
											    if(!isset($stats_par_niveau_parcours[$niveau_key][$parcours_key][$specialite])){
											        $stats_par_niveau_parcours[$niveau_key][$parcours_key][$specialite] = [
											            'niveau' => $niveau,
											            'parcours' => $parcours,
											            'specialite' => $specialite,
											            'inscrits_f' => 0, 'inscrits_g' => 0, 'inscrits_t' => 0,
											            'presents_f' => 0, 'presents_g' => 0, 'presents_t' => 0,
											            'admis_f' => 0, 'admis_g' => 0, 'admis_t' => 0,
											            'ajournes_f' => 0, 'ajournes_g' => 0, 'ajournes_t' => 0
											        ];
											    }
											    
											    // INSCRITS
											    $sql_inscrits = "SELECT 
											        candidat.sexe,
											        COUNT(DISTINCT inscription.id) as nb
											    FROM inscription
											    JOIN candidat ON candidat.code = inscription.candidat
											    WHERE inscription.classe = '".mysqli_real_escape_string($connexion, $classe)."'
											    AND inscription.annee = '".mysqli_real_escape_string($connexion, $annee_filtre)."'
											    AND inscription.etab = '".$_SESSION['etablissement']."'
											    GROUP BY candidat.sexe";
											    
											    $result_inscrits = $connexion->query($sql_inscrits);
											    while($inscrit = $result_inscrits->fetch_object()){
											        $sexe = strtoupper($inscrit->sexe);
											        if($sexe == 'F' || $sexe == 'FEMININ' || $sexe == 'FÉMININ'){
											            $stats_par_niveau_parcours[$niveau_key][$parcours_key][$specialite]['inscrits_f'] += $inscrit->nb;
											            $total_general['inscrits_f'] += $inscrit->nb;
											        } else {
											            $stats_par_niveau_parcours[$niveau_key][$parcours_key][$specialite]['inscrits_g'] += $inscrit->nb;
											            $total_general['inscrits_g'] += $inscrit->nb;
											        }
											        $stats_par_niveau_parcours[$niveau_key][$parcours_key][$specialite]['inscrits_t'] += $inscrit->nb;
											        $total_general['inscrits_t'] += $inscrit->nb;
											    }
											    
											    // PRÉSENTS, ADMIS, AJOURNÉS
											    $sql_resultats = "SELECT 
											        candidat.sexe,
											        recap.decision
											    FROM recap
											    JOIN inscription ON inscription.id = recap.etudiant
											    JOIN candidat ON candidat.code = inscription.candidat
											    WHERE inscription.classe = '".mysqli_real_escape_string($connexion, $classe)."'
											    AND recap.annee = '".mysqli_real_escape_string($connexion, $annee_filtre)."'
											    AND recap.examen = '".mysqli_real_escape_string($connexion, $examen_filtre)."'
											    AND recap.semestre IN ($semestres_in)
											    AND recap.etab = '".$_SESSION['etablissement']."'";
											    
											    $result_resultats = $connexion->query($sql_resultats);
											    while($resultat = $result_resultats->fetch_object()){
											        $sexe = strtoupper($resultat->sexe);
											        $decision = $resultat->decision;
											        
											        // PRÉSENTS
											        if($sexe == 'F' || $sexe == 'FEMININ' || $sexe == 'FÉMININ'){
											            $stats_par_niveau_parcours[$niveau_key][$parcours_key][$specialite]['presents_f']++;
											            $total_general['presents_f']++;
											        } else {
											            $stats_par_niveau_parcours[$niveau_key][$parcours_key][$specialite]['presents_g']++;
											            $total_general['presents_g']++;
											        }
											        $stats_par_niveau_parcours[$niveau_key][$parcours_key][$specialite]['presents_t']++;
											        $total_general['presents_t']++;
											        
											        // ADMIS ou AJOURNÉS
											        if(stripos($decision, 'Validé') !== false && stripos($decision, 'éliminatoire') === false && stripos($decision, 'eliminatoire') === false){
											            if($sexe == 'F' || $sexe == 'FEMININ' || $sexe == 'FÉMININ'){
											                $stats_par_niveau_parcours[$niveau_key][$parcours_key][$specialite]['admis_f']++;
											                $total_general['admis_f']++;
											            } else {
											                $stats_par_niveau_parcours[$niveau_key][$parcours_key][$specialite]['admis_g']++;
											                $total_general['admis_g']++;
											            }
											            $stats_par_niveau_parcours[$niveau_key][$parcours_key][$specialite]['admis_t']++;
											            $total_general['admis_t']++;
											        } else {
											            if($sexe == 'F' || $sexe == 'FEMININ' || $sexe == 'FÉMININ'){
											                $stats_par_niveau_parcours[$niveau_key][$parcours_key][$specialite]['ajournes_f']++;
											                $total_general['ajournes_f']++;
											            } else {
											                $stats_par_niveau_parcours[$niveau_key][$parcours_key][$specialite]['ajournes_g']++;
											                $total_general['ajournes_g']++;
											            }
											            $stats_par_niveau_parcours[$niveau_key][$parcours_key][$specialite]['ajournes_t']++;
											            $total_general['ajournes_t']++;
											        }
											    }
											}
											
											// Organiser par licence et trier
											$data_par_licence = [
											    'Licence 1' => [],
											    'Licence 2' => [],
											    'Licence 3' => []
											];
											
											$subtotals = [];
											
											foreach($stats_par_niveau_parcours as $niveau => $parcours_data){
											    $niveau_principal = '';
											    if(stripos($niveau, 'Première') !== false || stripos($niveau, '1') !== false){
											        $niveau_principal = 'Licence 1';
											    } elseif(stripos($niveau, 'Deuxième') !== false || stripos($niveau, '2') !== false){
											        $niveau_principal = 'Licence 2';
											    } elseif(stripos($niveau, 'Troisième') !== false || stripos($niveau, '3') !== false){
											        $niveau_principal = 'Licence 3';
											    }
											    
											    if(!isset($data_par_licence[$niveau_principal])){
											        $data_par_licence[$niveau_principal] = [];
											    }
											    
											    foreach($parcours_data as $parcours => $specialites){
											        // Trier les spécialités par ordre alphabétique
											        ksort($specialites);
											        
											        if(!isset($data_par_licence[$niveau_principal][$parcours])){
											            $data_par_licence[$niveau_principal][$parcours] = [
											                'niveau' => $niveau,
											                'parcours' => $parcours,
											                'specialites' => []
											            ];
											        }
											        
											        foreach($specialites as $specialite => $stats){
											            $data_par_licence[$niveau_principal][$parcours]['specialites'][] = $stats;
											        }
											    }
											    
											    // Calculer le sous-total par niveau
											    if(!isset($subtotals[$niveau_principal])){
											        $subtotals[$niveau_principal] = [
											            'inscrits_f' => 0, 'inscrits_g' => 0, 'inscrits_t' => 0,
											            'presents_f' => 0, 'presents_g' => 0, 'presents_t' => 0,
											            'admis_f' => 0, 'admis_g' => 0, 'admis_t' => 0,
											            'ajournes_f' => 0, 'ajournes_g' => 0, 'ajournes_t' => 0
											        ];
											    }
											    
											    foreach($parcours_data as $specialites){
											        foreach($specialites as $stats){
											            foreach(['inscrits', 'presents', 'admis', 'ajournes'] as $type){
											                foreach(['f', 'g', 't'] as $sexe){
											                    $subtotals[$niveau_principal][$type.'_'.$sexe] += $stats[$type.'_'.$sexe];
											                }
											            }
											        }
											    }
											}
											
											// Afficher les données
											foreach(['Licence 1', 'Licence 2', 'Licence 3'] as $licence){
											    if(empty($data_par_licence[$licence])) continue;
											    
											    // En-tête de niveau
											    ?>
											    <tr class="niveau-header">
											        <td colspan="16" class="text-left">
											            <strong><?php echo $licence; ?></strong>
											        </td>
											    </tr>
											    <?php
											    
											    // Pour Licence 1, on affiche directement les spécialités sans parcours
											    if($licence == 'Licence 1'){
											        // Collecter toutes les spécialités de tous les parcours
											        $toutes_specialites = [];
											        foreach($data_par_licence[$licence] as $parcours_data){
											            foreach($parcours_data['specialites'] as $spec){
											                $toutes_specialites[$spec['specialite']] = $spec;
											            }
											        }
											        // Trier par ordre alphabétique
											        ksort($toutes_specialites);
											        
											        // Afficher les spécialités
											        foreach($toutes_specialites as $stats){
											            $pct_f = ($stats['presents_f'] > 0) ? round(($stats['admis_f'] / $stats['presents_f']) * 100, 1) : 0;
											            $pct_g = ($stats['presents_g'] > 0) ? round(($stats['admis_g'] / $stats['presents_g']) * 100, 1) : 0;
											            $pct_t = ($stats['presents_t'] > 0) ? round(($stats['admis_t'] / $stats['presents_t']) * 100, 1) : 0;
											            ?>
											            <tr class="specialite-row">
											                <td class="text-left"><?php echo str_replace('+',"'",$stats['specialite']); ?></td>
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
											    } else {
											        // Pour Licence 2 et 3, afficher les parcours
											        foreach($data_par_licence[$licence] as $parcours_data){
											            ?>
											            <!-- En-tête de parcours -->
											            <tr class="parcours-header">
											                <td colspan="16" class="text-left">
											                    <em><?php echo $parcours_data['parcours']; ?></em>
											                </td>
											            </tr>
											            <?php
											            
											            // Spécialités (triées alphabétiquement)
											            foreach($parcours_data['specialites'] as $stats){
											                $pct_f = ($stats['presents_f'] > 0) ? round(($stats['admis_f'] / $stats['presents_f']) * 100, 1) : 0;
											                $pct_g = ($stats['presents_g'] > 0) ? round(($stats['admis_g'] / $stats['presents_g']) * 100, 1) : 0;
											                $pct_t = ($stats['presents_t'] > 0) ? round(($stats['admis_t'] / $stats['presents_t']) * 100, 1) : 0;
											                ?>
											                <tr class="specialite-row">
											                    <td class="text-left"><?php echo str_replace('+',"'",$stats['specialite']); ?></td>
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
											        }
											    }
											    
											    // Sous-total
											    $subtotal = $subtotals[$licence];
											    $pct_f = ($subtotal['presents_f'] > 0) ? round(($subtotal['admis_f'] / $subtotal['presents_f']) * 100, 1) : 0;
											    $pct_g = ($subtotal['presents_g'] > 0) ? round(($subtotal['admis_g'] / $subtotal['presents_g']) * 100, 1) : 0;
											    $pct_t = ($subtotal['presents_t'] > 0) ? round(($subtotal['admis_t'] / $subtotal['presents_t']) * 100, 1) : 0;
											    ?>
											    <tr class="table-success">
											        <td class="text-left"><strong>Sous-total (<?php echo $licence; ?>)</strong></td>
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
    <script src="../vendor/datatables/js/jquery.dataTables.min.js"></script>
    <script src="../js/plugins-init/datatables.init.js"></script>
    
    <script>
    /**
     * Ouvre la page d'impression des statistiques dans une nouvelle fenêtre
     */
    function ouvrirImpression() {
        // Récupérer les paramètres actuels
        var params = new URLSearchParams(window.location.search);
        var url = 'generer_stats_impression.php?' + params.toString();
        
        // Ouvrir dans une nouvelle fenêtre
        window.open(url, '_blank', 'width=1200,height=800');
    }
    </script>

	
</body>
</html>

<?php 

}else{
    header("location: ../login");
}?>
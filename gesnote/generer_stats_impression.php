<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once '../php/connexion.php';
require_once '../php/lib.php';

session_start();

if($_SESSION['id'] != session_id() || $_SESSION['role'] != "gesnote"){
    die("Accès non autorisé");
}

// Récupérer les paramètres
$annee_filtre = isset($_GET['annee']) ? $_GET['annee'] : '';
$semestre_filtre = isset($_GET['semestre']) ? $_GET['semestre'] : '';
$examen_filtre = isset($_GET['examen']) ? $_GET['examen'] : 'ordinaire';

if(empty($annee_filtre)){
    die("Veuillez sélectionner une année universitaire");
}

// Déterminer les semestres
$semestres_a_traiter = [];
if(empty($semestre_filtre) || $semestre_filtre == 'impairs'){
    $semestres_a_traiter = ['semestre 1', 'semestre 3', 'semestre 5'];
    $semestres_libelle = "SEMESTRES 1, 3 et 5";
} elseif($semestre_filtre == 'pairs'){
    $semestres_a_traiter = ['semestre 2', 'semestre 4', 'semestre 6'];
    $semestres_libelle = "SEMESTRES 2, 4 et 6";
}

// RÉCUPÉRATION DES DONNÉES AVEC PARCOURS
$semestres_in = "'" . implode("', '", $semestres_a_traiter) . "'";

$sql_classes = "SELECT  
    c.libelle as classe,
    c.niveau as niveau,
    c.specialite as specialite,
    p.libelle as parcours
FROM classe c  
LEFT JOIN specialite s on c.specialite=s.libelle join parcours p ON s.parcours = p.libelle
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

// Organiser par licence et trier les spécialités alphabétiquement
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
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Statistiques Examens - <?php echo $annee_filtre; ?></title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: "Times New Roman", Times, serif;
            font-size: 10pt;
            line-height: 1.3;
            padding: 20px;
        }
        
        .container {
            max-width: 297mm;
            margin: 0 auto;
            position: relative;
        }
        
        /* Logo en filigrane */
        .container::before {
            content: '';
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            width: 400px;
            height: 400px;
            background-image: url('../images/univ.png');
            background-size: contain;
            background-repeat: no-repeat;
            background-position: center;
            opacity: 0.2;
            z-index: -1;
        }
        
        /* EN-TÊTE */
        .header {
            margin-bottom: 12px;
            font-family: "Times New Roman", Times, serif;
        }
        
        .header-row {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 8px;
        }
        
        .header-left {
            flex: 1;
            text-align: left;
        }
        
        .header-center {
            flex: 0 0 auto;
            text-align: center;
            padding: 0 20px;
        }
        
        .header-right {
            flex: 1;
            text-align: right;
        }
        
        .univ-name {
            font-size: 14pt;
            font-weight: bold;
            margin-bottom: 5px;
        }
        
        .direction {
            font-size: 12pt;
            margin-bottom: 3px;
        }
        
        .service {
            font-size: 10pt;
            margin-bottom: 5px;
        }
        
        .devise {
            font-size: 14pt;
            font-weight: normal;
            margin-bottom: 5px;
        }
        
        .etablissement {
            font-size: 10pt;
            margin-bottom: 3px;
        }
        
        .vice-decanat {
            font-size: 10pt;
            margin-top: 5px;
        }
        
        .titre {
            font-size: 16pt;
            font-weight: bold;
            text-align: center;
            border: 2px solid #000;
            padding: 8px;
            margin: 10px 0 8px 0;
            font-family: "Times New Roman", Times, serif;
        }
        
        /* TABLEAU */
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
            font-size: 8pt;
            font-family: "Times New Roman", Times, serif;
        }
        
        th, td {
            border: 1px solid #000;
            padding: 4px 2px;
            text-align: center;
        }
        
        th {
            background-color: #d0d0d0;
            font-weight: bold;
        }
        
        .niveau-header {
            background-color: #fff;
            font-weight: bold;
            text-align: left;
            padding-left: 5px;
            border-bottom: none;
        }
        
        .parcours-header {
            background-color: #f5f5f5;
            font-weight: bold;
            text-align: left;
            padding-left: 10px;
            font-style: italic;
        }
        
        .specialite-row td:first-child {
            text-align: left;
            padding-left: 20px;
        }
        
        .subtotal-row {
            background-color: #e8e8e8;
            font-weight: bold;
        }
        
        .total-row {
            background-color: #c0c0c0;
            font-weight: bold;
            font-size: 9pt;
        }
        
        /* SIGNATURE */
        .signature {
            margin-top: 50px;
            text-align: right;
            padding-right: 50px;
        }
        
        .signature-line {
            margin: 15px 0;
            font-size: 11pt;
            min-height: 50px;
        }
        
        .signature-name {
            margin-top: 20px;
            font-weight: bold;
            font-size: 13pt;
        }
        
        /* BOUTON IMPRIMER */
        .no-print {
            text-align: center;
            margin: 20px 0;
        }
        
        .btn-print {
            background-color: #007bff;
            color: white;
            border: none;
            padding: 12px 30px;
            font-size: 14pt;
            cursor: pointer;
            border-radius: 5px;
        }
        
        .btn-print:hover {
            background-color: #0056b3;
        }
        
        /* STYLES POUR L'IMPRESSION */
        @media print {
            body {
                padding: 0;
                margin: 0;
            }
            
            .container {
                max-width: 100%;
                padding-top: 5mm;
            }
            
            .no-print {
                display: none !important;
            }
            
            @page {
                size: A4 landscape;
                margin: 8mm;
            }
            
            table {
                page-break-inside: avoid;
            }
            
            .subtotal-row, .total-row {
                page-break-inside: avoid;
            }
        }
    </style>
</head>
<body>
    <div class="no-print">
        <button class="btn-print" onclick="window.print()">
            🖨️ IMPRIMER CE DOCUMENT
        </button>
    </div>
    
    <div class="container">
        <!-- EN-TÊTE -->
        <div class="header">
            <div class="header-row">
                <div class="header-left">
                    <div class="univ-name">UNIVERSITE DENIS SASSOU-N'GUESSO</div>
                    <div class="direction">DIRECTION DE LA SCOLARITE ET DES EXAMENS</div>
                    <div class="service">SERVICE DE LA SCOLARITE ET DES EXAMENS</div>
                </div>
                
                <div class="header-center">
                    <img src="../images/univ.png" alt="Logo" style="max-height: 100px;">
                </div>
                
                <div class="header-right">
                    <div class="devise">Rigueur-Excellence-Lumieres</div>
                    <div class="etablissement"><?php echo mb_strtoupper($_SESSION['lib_etab']); ?></div>
                    <?php if(typeEtablissement($_SESSION["lib_etab"],$connexion) == "faculté"): ?>
                        <div class="vice-decanat">VICE-DECANAT</div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        
        <!-- TITRE -->
        <div class="titre">
            RESULTATS STATISTIQUES DES EXAMENS DE LA SESSION <?php echo strtoupper($examen_filtre); ?><br>
            <?php echo $semestres_libelle; ?>
        </div>
        
        <div style="text-align: center; font-size: 11pt; font-weight: bold;">
            Année Universitaire : <?php echo $annee_filtre; ?>
        </div>
        
        <!-- TABLEAU -->
        <table>
            <thead>
                <tr>
                    <th rowspan="2">Niveau / Parcours / Spécialité</th>
                    <th colspan="3">Inscrits</th>
                    <th colspan="3">Présents</th>
                    <th colspan="3">Admis</th>
                    <th colspan="3">Ajournés</th>
                    <th colspan="3">% admis</th>
                </tr>
                <tr>
                    <?php for($i = 0; $i < 5; $i++): ?>
                        <th>F</th>
                        <th>G</th>
                        <th>T</th>
                    <?php endfor; ?>
                </tr>
            </thead>
            <tbody>
                <?php foreach(['Licence 1', 'Licence 2', 'Licence 3'] as $licence): ?>
                    <?php if(empty($data_par_licence[$licence])) continue; ?>
                    
                    <!-- En-tête de niveau -->
                    <tr>
                        <td colspan="16" class="niveau-header">
                            <?php echo $licence; ?>
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
                        foreach($toutes_specialites as $stats): 
                            $pct_f = ($stats['presents_f'] > 0) ? round(($stats['admis_f'] / $stats['presents_f']) * 100, 1) : 0;
                            $pct_g = ($stats['presents_g'] > 0) ? round(($stats['admis_g'] / $stats['presents_g']) * 100, 1) : 0;
                            $pct_t = ($stats['presents_t'] > 0) ? round(($stats['admis_t'] / $stats['presents_t']) * 100, 1) : 0;
                        ?>
                        <tr class="specialite-row">
                            <td><?php echo str_replace('+',"'",$stats['specialite']); ?></td>
                            <td><?php echo $stats['inscrits_f']; ?></td>
                            <td><?php echo $stats['inscrits_g']; ?></td>
                            <td><?php echo $stats['inscrits_t']; ?></td>
                            <td><?php echo $stats['presents_f']; ?></td>
                            <td><?php echo $stats['presents_g']; ?></td>
                            <td><?php echo $stats['presents_t']; ?></td>
                            <td><?php echo $stats['admis_f']; ?></td>
                            <td><?php echo $stats['admis_g']; ?></td>
                            <td><?php echo $stats['admis_t']; ?></td>
                            <td><?php echo $stats['ajournes_f']; ?></td>
                            <td><?php echo $stats['ajournes_g']; ?></td>
                            <td><?php echo $stats['ajournes_t']; ?></td>
                            <td><?php echo $pct_f; ?></td>
                            <td><?php echo $pct_g; ?></td>
                            <td><?php echo $pct_t; ?></td>
                        </tr>
                        <?php endforeach; ?>
                    <?php } else { 
                        // Pour Licence 2 et 3, on garde le groupement par parcours
                        foreach($data_par_licence[$licence] as $parcours_data): ?>
                        <!-- En-tête de parcours -->
                        <tr>
                            <td colspan="16" class="parcours-header">
                                <?php echo $parcours_data['parcours']; ?>
                            </td>
                        </tr>
                        
                        <!-- Spécialités (triées alphabétiquement) -->
                        <?php foreach($parcours_data['specialites'] as $stats): 
                            $pct_f = ($stats['presents_f'] > 0) ? round(($stats['admis_f'] / $stats['presents_f']) * 100, 1) : 0;
                            $pct_g = ($stats['presents_g'] > 0) ? round(($stats['admis_g'] / $stats['presents_g']) * 100, 1) : 0;
                            $pct_t = ($stats['presents_t'] > 0) ? round(($stats['admis_t'] / $stats['presents_t']) * 100, 1) : 0;
                        ?>
                        <tr class="specialite-row">
                            <td><?php echo str_replace('+',"'",$stats['specialite']); ?></td>
                            <td><?php echo $stats['inscrits_f']; ?></td>
                            <td><?php echo $stats['inscrits_g']; ?></td>
                            <td><?php echo $stats['inscrits_t']; ?></td>
                            <td><?php echo $stats['presents_f']; ?></td>
                            <td><?php echo $stats['presents_g']; ?></td>
                            <td><?php echo $stats['presents_t']; ?></td>
                            <td><?php echo $stats['admis_f']; ?></td>
                            <td><?php echo $stats['admis_g']; ?></td>
                            <td><?php echo $stats['admis_t']; ?></td>
                            <td><?php echo $stats['ajournes_f']; ?></td>
                            <td><?php echo $stats['ajournes_g']; ?></td>
                            <td><?php echo $stats['ajournes_t']; ?></td>
                            <td><?php echo $pct_f; ?></td>
                            <td><?php echo $pct_g; ?></td>
                            <td><?php echo $pct_t; ?></td>
                        </tr>
                        <?php endforeach; ?>
                    <?php endforeach; } ?>
                    
                    <!-- Sous-total -->
                    <?php 
                    $subtotal = $subtotals[$licence];
                    $pct_f = ($subtotal['presents_f'] > 0) ? round(($subtotal['admis_f'] / $subtotal['presents_f']) * 100, 1) : 0;
                    $pct_g = ($subtotal['presents_g'] > 0) ? round(($subtotal['admis_g'] / $subtotal['presents_g']) * 100, 1) : 0;
                    $pct_t = ($subtotal['presents_t'] > 0) ? round(($subtotal['admis_t'] / $subtotal['presents_t']) * 100, 1) : 0;
                    ?>
                    <tr class="subtotal-row">
                        <td>Sous total <?php echo substr($licence, -1); ?> (<?php echo $licence; ?>)</td>
                        <td><?php echo $subtotal['inscrits_f']; ?></td>
                        <td><?php echo $subtotal['inscrits_g']; ?></td>
                        <td><?php echo $subtotal['inscrits_t']; ?></td>
                        <td><?php echo $subtotal['presents_f']; ?></td>
                        <td><?php echo $subtotal['presents_g']; ?></td>
                        <td><?php echo $subtotal['presents_t']; ?></td>
                        <td><?php echo $subtotal['admis_f']; ?></td>
                        <td><?php echo $subtotal['admis_g']; ?></td>
                        <td><?php echo $subtotal['admis_t']; ?></td>
                        <td><?php echo $subtotal['ajournes_f']; ?></td>
                        <td><?php echo $subtotal['ajournes_g']; ?></td>
                        <td><?php echo $subtotal['ajournes_t']; ?></td>
                        <td><?php echo $pct_f; ?></td>
                        <td><?php echo $pct_g; ?></td>
                        <td><?php echo $pct_t; ?></td>
                    </tr>
                <?php endforeach; ?>
                
                <!-- TOTAL GÉNÉRAL -->
                <?php 
                $pct_total_f = ($total_general['presents_f'] > 0) ? round(($total_general['admis_f'] / $total_general['presents_f']) * 100, 1) : 0;
                $pct_total_g = ($total_general['presents_g'] > 0) ? round(($total_general['admis_g'] / $total_general['presents_g']) * 100, 1) : 0;
                $pct_total_t = ($total_general['presents_t'] > 0) ? round(($total_general['admis_t'] / $total_general['presents_t']) * 100, 1) : 0;
                ?>
                <tr class="total-row">
                    <td>Total étudiants <?php echo $_SESSION['etablissement']; ?></td>
                    <td><?php echo $total_general['inscrits_f']; ?></td>
                    <td><?php echo $total_general['inscrits_g']; ?></td>
                    <td><?php echo $total_general['inscrits_t']; ?></td>
                    <td><?php echo $total_general['presents_f']; ?></td>
                    <td><?php echo $total_general['presents_g']; ?></td>
                    <td><?php echo $total_general['presents_t']; ?></td>
                    <td><?php echo $total_general['admis_f']; ?></td>
                    <td><?php echo $total_general['admis_g']; ?></td>
                    <td><?php echo $total_general['admis_t']; ?></td>
                    <td><?php echo $total_general['ajournes_f']; ?></td>
                    <td><?php echo $total_general['ajournes_g']; ?></td>
                    <td><?php echo $total_general['ajournes_t']; ?></td>
                    <td><?php echo $pct_total_f; ?></td>
                    <td><?php echo $pct_total_g; ?></td>
                    <td><?php echo $pct_total_t; ?></td>
                </tr>
            </tbody>
        </table>
        
        <!-- SIGNATURE -->
        <div class="signature">
            <div class="signature-line">Kintélé le,</div>
            <div class="signature-line">Le Doyen,</div>
            <div class="signature-name">
                <?php echo getNomUtilisateurParId($connexion, $_SESSION["id_user"]); ?>
            </div>
        </div>
    </div>
</body>
</html>
<?php 
include '../php/connexion.php';
include '../php/lib.php';
session_start();

// Initialiser les variables de filtre
$semestre = isset($_GET['semestre']) ? $_GET['semestre'] : '';
$classe = isset($_GET['classe']) ? $_GET['classe'] : '';
$annee = isset($_GET['annee']) ? $_GET['annee'] : '';
$examen = isset($_GET['examen']) ? $_GET['examen'] : '';
$specialite = '';
$parcours = '';
$niveau = '';

// Si tous les paramètres requis sont présents
$afficher_resultats = false;
if(!empty($semestre) && !empty($classe) && !empty($annee) && !empty($examen)) {
    $niveau = NiveauParSemestre($semestre);
    $specialite = getSpecialiteClasse($connexion,$classe);
    $parcours = getParcours($specialite,$connexion);
    $afficher_resultats = true;
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $_SESSION['univ'];?> - Scolarité de  <?php echo $_SESSION['etablissement'];?> </title>
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
        
        .stats-container {
            display: flex;
            justify-content: space-around;
            margin: 20px 0;
            flex-wrap: wrap;
        }
        
        .stat-card {
            background: #fff;
            border-radius: 8px;
            padding: 20px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            min-width: 200px;
            margin: 10px;
            text-align: center;
        }
        
        .stat-number {
            font-size: 36px;
            font-weight: bold;
            margin: 10px 0;
        }
        
        .stat-label {
            font-size: 14px;
            color: #666;
            text-transform: uppercase;
        }
        
        .stat-success {
            color: #28a745;
        }
        
        .stat-danger {
            color: #dc3545;
        }
        
        .stat-warning {
            color: #ffc107;
        }
        
        .stat-info {
            color: #17a2b8;
        }
        
        .filter-card {
            background: #f8f9fa;
            border-radius: 8px;
            padding: 20px;
            margin-bottom: 20px;
        }
        
        .filter-row {
            display: flex;
            flex-wrap: wrap;
            gap: 15px;
            margin-bottom: 15px;
        }
        
        .filter-group {
            flex: 1;
            min-width: 200px;
        }
        
        .filter-group label {
            display: block;
            margin-bottom: 5px;
            font-weight: 500;
            color: #333;
        }
        
        .filter-group select,
        .filter-group input {
            width: 100%;
            padding: 8px 12px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 14px;
        }
        
        .filter-actions {
            display: flex;
            gap: 10px;
            margin-top: 15px;
        }
        
        .btn-filter {
            padding: 10px 20px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 14px;
            font-weight: 500;
        }
        
        .btn-primary {
            background: #007bff;
            color: white;
        }
        
        .btn-secondary {
            background: #6c757d;
            color: white;
        }
        
        .btn-filter:hover {
            opacity: 0.9;
        }
    </style>
   	<link rel="stylesheet" href="../vendor/bootstrap-select/dist/css/bootstrap-select.min.css">
	    <link rel="stylesheet" href="../vendor/select2/css/select2.min.css">
    <link rel="stylesheet" href="../css/style.css">
	<link rel="stylesheet" href="../css/skin.css">
    <link href="../vendor/datatables/css/jquery.dataTables.min.css" rel="stylesheet">


</head>
<body>

<div id="main-wrapper">

<?php include "header.php" ;?>

<?php include 'nav.html'; ?>

<div class="content-body">
    <div class="container-fluid">
        
        <div class="row page-titles mx-0">
            <div class="col-sm-6 p-md-0">
                <div class="welcome-text">
                    <h4>Consultation des moyennes des unités d'enseignements</h4>
                </div>
            </div>
            <div class="col-sm-6 p-md-0 justify-content-sm-end mt-2 mt-sm-0 d-flex">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="index">Tableau de Bord</a></li>
                    <li class="breadcrumb-item active">Consultation UE</li>
                </ol>
            </div>
        </div>
        
        <!-- Filtres -->
        <div class="col-lg-12">
            <div class="card filter-card">
                <h5 class="mb-3"><i class="fa fa-filter"></i> Filtres de recherche</h5>
                <form method="GET" action="">
                    <div class="filter-row">
                        <div class="filter-group">
                            <label for="parcours_filter">Parcours</label>
                            <select name="parcours_filter" id="parcours_filter" class="form-control">
                                <option value="">-- Sélectionner un parcours --</option>
                                <?php
                                $sql_parcours = "SELECT DISTINCT libelle FROM parcours  where etab='".$_SESSION["lib_etab"]."' ORDER BY libelle";
                                $result_parcours = $connexion->query($sql_parcours);
                                while($row = $result_parcours->fetch_object()) {
                                    echo "<option value='{$row->libelle}'>{$row->libelle}</option>";
                                }
                                ?>
                            </select>
                        </div>
                        
                        <div class="filter-group">
                            <label for="specialite_filter">Spécialité</label>
                            <select name="specialite_filter" id="specialite_filter" class="disabling-options">
                                 <option value=""></option>
                                <?php
                                $sql_parcours = "SELECT DISTINCT libelle FROM specialite where etab='".$_SESSION["lib_etab"]."' ORDER BY libelle";
                                $result_parcours = $connexion->query($sql_parcours);
                                while($row = $result_parcours->fetch_object()) {
                                    echo "<option value='{$row->libelle}'>{$row->libelle}</option>";
                                }
                                ?>
                            </select>
                        </div>
                        
                        <div class="filter-group">
                            <label for="classe">Classe *</label>
                            <select name="classe" id="classe" class="disabling-options" required>
                                <option value="">-- Sélectionner une classe --</option>
                                <?php
                                $sql_parcours = "SELECT DISTINCT libelle FROM classe  where etab='".$_SESSION["etablissement"]."' ORDER BY libelle";
                                $result_parcours = $connexion->query($sql_parcours);
                                while($row = $result_parcours->fetch_object()) {
                                    echo "<option value='{$row->libelle}'>{$row->libelle}</option>";
                                }
                                ?>
                            </select>
                        </div>
                    </div>
                    
                    <div class="filter-row">
                        <div class="filter-group">
                            <label for="semestre">Semestre *</label>
                            <select name="semestre" id="semestre" class="form-control" required>
                                <option value="">-- Sélectionner --</option>
                                <option  <?php echo $semestre == 'semestre 1' ? 'selected' : ''; ?>>semestre 1</option>
                                <option  <?php echo $semestre == 'semestre 2' ? 'selected' : ''; ?>>semestre 2</option>
                                <option <?php echo $semestre == 'semestre 3' ? 'selected' : ''; ?>>semestre 3</option>
                                <option  <?php echo $semestre == 'semestre 4' ? 'selected' : ''; ?>>semestre 4</option>
                                <option  <?php echo $semestre == 'semestre 5' ? 'selected' : ''; ?>>semestre 5</option>
                                <option  <?php echo $semestre == 'semestre 6' ? 'selected' : ''; ?>>semestre 6</option>
                            </select>
                        </div>
                        
                        <div class="filter-group">
                            <label for="annee">Année Académique *</label>
                            <select name="annee" id="annee" class="form-control disabling-options" required>
                                <option value="">-- Sélectionner --</option>
                                <?php
                                $sql_annees = "SELECT DISTINCT annee FROM inscription ORDER BY annee DESC";
                                $result_annees = $connexion->query($sql_annees);
                                while($row = $result_annees->fetch_object()) {
                                    $selected = ($row->annee == $annee) ? 'selected' : '';
                                    echo "<option value='{$row->annee}' {$selected}>{$row->annee}</option>";
                                }
                                ?>
                            </select>
                        </div>
                        
                        <div class="filter-group">
                            <label for="examen">Type d'examen *</label>
                            <select name="examen" id="examen" class="form-control" required>
                                <option value="">-- Sélectionner --</option>
                                <option value="Session ordinaire" <?php echo $examen == 'Session ordinaire' ? 'selected' : ''; ?>>Session ordinaire</option>
                                <option value="Session de rappel" <?php echo $examen == 'Session de rappel' ? 'selected' : ''; ?>>Session de rappel</option>
                                   </select>
                        </div>
                    </div>
                    
                    <div class="filter-actions">
                        <button type="submit" class="btn-filter btn-primary">
                            <i class="fa fa-search"></i> Rechercher
                        </button>
                        <button type="button" class="btn-filter btn-secondary" onclick="resetFilters()">
                            <i class="fa fa-refresh"></i> Réinitialiser
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <?php if($afficher_resultats): ?>
        
        <div class="col-lg-12">
            <div class="row tab-content">
                <div id="list-view" class="tab-pane fade active show col-lg-12">
                    <div class="card">
                        <div class="card-header">
                            <div class="row">
                                <div class="col-md-6 card-title">
                                    <h4><span class="text-primary">Parcours</span>: <?php echo $parcours; ?></h4>
                                    <h4><span class="text-primary">Spécialité</span>: <?php echo $specialite; ?></h4>
                                    <h4><span class="text-primary">Classe</span>: <?php echo $classe; ?></h4>
                                </div>
                                <div class="col-md-6">
                                    <h4><span class="text-primary">Semestre</span>: <?php echo $semestre; ?></h4>
                                    <h4><span class="text-primary">Examen</span>: <?php echo $examen; ?></h4>
                                    <h4><span class="text-primary">Année-académique</span>: <?php echo $annee; ?></h4>
                                    <h4>
                                        <form method="POST" action="imprimer_ue.php">
                                            <input type="hidden" name="classe" value="<?php echo $classe?>">
                                            <input type="hidden" name="semestre" value="<?php echo $semestre?>">
                                            <input type="hidden" name="examen" value="<?php echo $examen?>">
                                            <input type="hidden" name="annee" value="<?php echo $annee?>">
                                            <input type="hidden" name="specialite" value="<?php echo $specialite?>">
                                            <button type="submit" class="btn btn-sm btn-danger">
                                                <i class="fa fa-print"></i> Imprimer
                                            </button>
                                        </form>
                                    </h4>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Statistiques -->
                        <?php 
                            $total_etudiants = 0;
                            $admis = 0;
                            $ajournes = 0;
                            $sans_notes = 0;
                            
                            $sql_stats = "SELECT
                            inscription.id ,
    candidat.code AS candidat,
    candidat.nom AS nom,
    candidat.prenom AS prenom
FROM inscription
JOIN candidat ON candidat.code = inscription.candidat
JOIN classe ON inscription.classe = classe.libelle
JOIN specialite ON classe.specialite = specialite.libelle
JOIN parcours ON specialite.parcours = parcours.libelle
WHERE classe.libelle = '$classe'
  AND parcours.libelle = '$parcours'
GROUP BY  candidat.nom, candidat.prenom
ORDER BY nom, prenom;
";
                            
                            $r_stats = $connexion->query($sql_stats);
                            
                            while($etudiant_stat = $r_stats->fetch_object()){
                                $total_etudiants++;
                                $moyenne_stat = calcul_moyenne($etudiant_stat->id, $semestre, $annee, $_SESSION['etablissement'], $connexion);
                                
                                if($moyenne_stat === "-"){
                                    $sans_notes++;
                                } else if($moyenne_stat >= 10){
                                    $admis++;
                                } else {
                                    $ajournes++;
                                }
                            }
                            
                            $pct_admis = $total_etudiants > 0 ? round(($admis / $total_etudiants) * 100, 1) : 0;
                            $pct_ajournes = $total_etudiants > 0 ? round(($ajournes / $total_etudiants) * 100, 1) : 0;
                            $pct_sans_notes = $total_etudiants > 0 ? round(($sans_notes / $total_etudiants) * 100, 1) : 0;
                        ?>
                        
                        <div class="card-body">
                            <h5 class="mb-3">Statistiques de la classe</h5>
                            <div class="stats-container">
                                <div class="stat-card">
                                    <div class="stat-label">Total Étudiants</div>
                                    <div class="stat-number stat-info"><?php echo $total_etudiants; ?></div>
                                </div>
                                
                                <div class="stat-card">
                                    <div class="stat-label">Admis</div>
                                    <div class="stat-number stat-success"><?php echo $admis; ?></div>
                                    <div class="stat-label"><?php echo $pct_admis; ?>%</div>
                                </div>
                                
                                <div class="stat-card">
                                    <div class="stat-label">Ajournés</div>
                                    <div class="stat-number stat-danger"><?php echo $ajournes; ?></div>
                                    <div class="stat-label"><?php echo $pct_ajournes; ?>%</div>
                                </div>
                                
                                <div class="stat-card">
                                    <div class="stat-label">Sans Notes</div>
                                    <div class="stat-number stat-warning"><?php echo $sans_notes; ?></div>
                                    <div class="stat-label"><?php echo $pct_sans_notes; ?>%</div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="card-body">
                            <div class="table-responsive">
                                <?php 
                                $sql = "SELECT DISTINCT
                                    ue AS code,
                                    libelle_ue AS libelle
                                FROM vue_repartition
                                WHERE specialite = '$specialite'
                                  AND semestre = '$semestre'
                                  AND classe = '$classe'";
                                ?>

                                <table id="example3" class="display">
                                    <thead>
                                        <tr>
                                            <th>Nom(s)</th>
                                            <th>Prénom(s)</th>
                                            <?php 
                                            $result_ue = $connexion->query($sql);
                                            while($data = $result_ue->fetch_object()){
                                            ?>
                                            <th><?php echo str_replace("+","'",$data->libelle)?></th>
                                            <?php }?>
                                            <th>Moyenne Generale</th>
                                            <th>Décision</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                    <?php 
                                    $sql1 = "SELECT
                                    inscription.id,
    candidat.code AS candidat,
    candidat.nom AS nom,
    candidat.prenom AS prenom
FROM inscription
JOIN candidat ON candidat.code = inscription.candidat
JOIN classe ON inscription.classe = classe.libelle
JOIN specialite ON classe.specialite = specialite.libelle
JOIN parcours ON specialite.parcours = parcours.libelle
WHERE classe.libelle = '$classe'
  AND parcours.libelle = '$parcours'
GROUP BY  candidat.nom, candidat.prenom
ORDER BY LOWER(nom), LOWER(prenom);
";
                                    
                                    $r = $connexion->query($sql1);
                                    
                                    $ues = array();
                                    $result_ue_temp = $connexion->query($sql);
                                    while($data_ue = $result_ue_temp->fetch_object()){
                                        $ues[] = $data_ue;
                                    }
                                    
                                    $num = 0;
                                    while($etudiant = $r->fetch_object()){
                                        $num++;
                                        $points = 0;
                                    ?>
                                        <tr>
                                            <th><?php echo mettrePremieresLettresMajuscules(getNomEtudiant(getCandidatCodeByInscription($etudiant->id,$connexion),$connexion,$_SESSION["lib_etab"]));?></th>
                                            <th><?php echo mettrePremieresLettresMajuscules(getPrenomEtudiant(getCandidatCodeByInscription($etudiant->id,$connexion),$connexion,$_SESSION["lib_etab"])); ?></th>
                                            
                                            <?php 
                                            foreach($ues as $data){
                                                $i=0; $countValide =0;
                                                $moyenne = getMoyenneUE($connexion,$etudiant->id,$semestre,$annee,$data->code,$_SESSION["etablissement"]);
                                            ?>
                                            <th class="text-secondary"> <?php echo ($moyenne !== "-") ? round($moyenne,2): "-";?></th>
                                            <?php } ?>
                                            
                                            <th class="text-dark"> <?php $tt=calcul_moyenne($etudiant->id,$semestre,$annee,$_SESSION['etablissement'],$connexion);echo  $tt?></th>
                                            
                                            <?php 
                                            if($tt < 10 and $tt !=="-") {
                                            ?>
                                            <th class="text-danger">Ajourné</th>
                                            <?php }else if ($tt >= 10){ ?>
                                            <th class="text-success">Admis</th>
                                            <?php }else{?>
                                            <th class="text-warning">-</th>
                                            <?php }?>
                                        </tr>
                                    <?php }?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <?php else: ?>
        
        <div class="col-lg-12">
            <div class="card">
                <div class="card-body text-center">
                    <i class="fa fa-info-circle fa-3x text-primary mb-3"></i>
                    <h4>Veuillez sélectionner les critères de recherche</h4>
                    <p class="text-muted">Utilisez les filtres ci-dessus pour afficher les résultats</p>
                </div>
            </div>
        </div>
        
        <?php endif; ?>
        
    </div>
</div>

</div>

<div class="modal" id="messageModal" tabindex="-1" role="dialog" aria-labelledby="messageModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="messageModalLabel"><?php echo $_SESSION['univ'];?></h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body" id="messageBody">
                <!-- Contenu du message -->
            </div>
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


 <script src="../vendor/select2/js/select2.full.min.js"></script>
    <script src="../js/plugins-init/select2-init.js"></script>

<script>
function resetFilters() {
    window.location.href = window.location.pathname;
}

function supprimerParametreUrl(nomParametre) {
    var url = window.location.href;
    if (url.indexOf('?') !== -1) {
        var queryString = url.split('?')[1];
        var params = queryString.split('&');
        var newParams = params.filter(function(param) {
            return param.split('=')[0] !== nomParametre;
        });
        var newUrl = url.split('?')[0] + (newParams.length > 0 ? '?' + newParams.join('&') : '');
        window.history.replaceState({}, document.title, newUrl);
    }
}

// Gestion des filtres en cascade
$(document).ready(function() {
    // Vérifier si Select2 ou Bootstrap-select est utilisé et le désactiver pour les filtres
   
    // Vérifier les messages d'erreur ou de succès
    var urlParams = new URLSearchParams(window.location.search);
    var erreur = urlParams.get('erreur');
    var success = urlParams.get('sucess');

    if (erreur || success) {
        var message = erreur ? "Erreur : " + erreur : "Message : " + success;
        $('#messageBody').text(message);
        $('#messageModal').modal('show');
        supprimerParametreUrl("erreur");
        supprimerParametreUrl("sucess");
    }
});
</script>

</body>
</html>
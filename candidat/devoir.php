<?php 
include '../php/connexion.php';
include '../php/lib.php';

session_start();

if($_SESSION['id'] == session_id()){
    
    // Récupération des informations de l'étudiant connecté
    $matricule = $_SESSION['matricule'] ?? '';
    $annee_courante = $_SESSION['annee'] ?? '';
    
    // Variables pour afficher les devoirs
    $devoirs_classe = [];
    $show_table = false;
    $classe_info = null;
    
    if(isset($_GET['ecue']) && isset($_GET['semestre']) && isset($_GET['annee']) && isset($_GET['type_evaluation'])) {
        $ecue = $_GET['ecue'];
        $semestre = $_GET['semestre'];
        $annee = $_GET['annee'];
        $type_evaluation = $_GET['type_evaluation'];
        
        // Récupérer la classe de l'étudiant connecté
        $sql_classe = "SELECT classe FROM inscription WHERE candidat='$matricule' AND annee='$annee'";
        $result_classe = $connexion->query($sql_classe);
        
        if($result_classe && $result_classe->num_rows > 0) {
            $classe_data = $result_classe->fetch_assoc();
            $classe = $classe_data['classe'];
            $show_table = true;
            
            // Récupérer les informations de l'ECUE
            $sql_ecue = "SELECT libelle FROM ecue WHERE code_ecue='$ecue'";
            $result_ecue = $connexion->query($sql_ecue);
            $ecue_info = $result_ecue->fetch_assoc();
            
            // Récupérer TOUS les étudiants de la classe avec leurs notes
            $sql_etudiants = "SELECT DISTINCT i.id, i.candidat, i.classe 
                             FROM inscription i
                             WHERE i.classe='$classe' AND i.annee='$annee'
                             ORDER BY i.candidat";
            
            $result_etudiants = $connexion->query($sql_etudiants);
            
            $etudiants_temporaires = [];
            
            while($etudiant = $result_etudiants->fetch_assoc()) {
                $etudiant_id = $etudiant['id'];
                $etudiant_matricule = $etudiant['candidat'];
                
                // Récupérer les notes de cet étudiant
                $sql_notes = "SELECT note FROM ligne2 
                             WHERE etudiant='$etudiant_id' 
                             AND code_ecue='$ecue' 
                             AND semestre='$semestre' 
                             AND annee='$annee' 
                             AND type='$type_evaluation'
                             ORDER BY id";
                
                $result_notes = $connexion->query($sql_notes);
                
                $notes = [];
                $total = 0;
                while($note_row = $result_notes->fetch_assoc()) {
                    $note_val = floatval($note_row['note']);
                    $notes[] = $note_val;
                    $total += $note_val;
                }
                
                if(count($notes) > 0) {
                    $moyenne = $total / count($notes);
                    $nom_prenom = obtenirNomPrenom($etudiant_matricule, $annee, $connexion);
                    
                    $etudiant_data = [
                        'matricule' => $etudiant_matricule,
                        'nom_prenom' => $nom_prenom,
                        'notes' => $notes,
                        'moyenne' => $moyenne,
                        'est_connecte' => ($etudiant_matricule == $matricule)
                    ];
                    
                    $etudiants_temporaires[] = $etudiant_data;
                }
            }
            
            // Trier: étudiant connecté en premier, puis ordre alphabétique
            usort($etudiants_temporaires, function($a, $b) {
                if($a['est_connecte']) return -1;
                if($b['est_connecte']) return 1;
                return strcmp($a['nom_prenom'], $b['nom_prenom']);
            });
            
            $devoirs_classe = $etudiants_temporaires;
            
            $classe_info = [
                'classe' => $classe,
                'ecue' => $ecue_info['libelle'],
                'semestre' => $semestre,
                'type_evaluation' => $type_evaluation,
                'annee' => $annee,
                'nb_devoirs' => count($devoirs_classe) > 0 ? count($devoirs_classe[0]['notes']) : 0
            ];
        }
    }
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>UDSN - Mes Devoirs</title>
    <link rel="icon" type="image/png" sizes="16x16" href="../administrateur/logo/favicon.png">
    <link rel="stylesheet" href="../vendor/bootstrap-select/dist/css/bootstrap-select.min.css">
    <link rel="stylesheet" href="../vendor/select2/css/select2.min.css">
    <link rel="stylesheet" href="../css/style.css">
    <link rel="stylesheet" href="../css/skin.css">
    <style>
        .highlight-student {
            background-color: #ffd700 !important;
            font-weight: bold;
        }
        .table-responsive {
            overflow-x: auto;
        }
    </style>
</head>

<body>
    <div id="preloader">
        <div class="sk-three-bounce">
            <div class="sk-child sk-bounce1"></div>
            <div class="sk-child sk-bounce2"></div>
            <div class="sk-child sk-bounce3"></div>
        </div>
    </div>

    <div id="main-wrapper">
        <div class="nav-header">
            <a href="#" class="brand-logo">
                <h3 class="d-none d-md-inline"><b style="color: white;">UDSN</b></h3>
                <img class="logo-abbr" src="../administrateur/logo/logo.png" alt="">
            </a>
            <div class="nav-control">
                <div class="hamburger">
                    <span class="line"></span><span class="line"></span><span class="line"></span>
                </div>
            </div>
        </div>

        <div class="header">
            <div class="header-content">
                <nav class="navbar navbar-expand">
                    <div class="collapse navbar-collapse justify-content-between">
                        <ul class="navbar-nav header-right">
                            <li class="nav-item dropdown header-profile">
                                <?php if(isset($_SESSION['img'])){ ?>
                                <a class="nav-link" href="#" role="button" data-toggle="dropdown">
                                    <img src="<?php echo $_SESSION['img'];?>" width="70" alt="">
                                </a>
                                <?php } ?>
                                <div class="dropdown-menu dropdown-menu-right">
                                    <a href="compte" class="dropdown-item ai-icon">
                                        <span class="ml-2">Mon Profile</span>
                                    </a>
                                    <a href="../connexion" class="dropdown-item ai-icon">
                                        <span class="ml-3">Deconnexion</span>
                                    </a>
                                </div>
                            </li>
                        </ul>
                    </div>
                </nav>
            </div>
        </div>

        <?php include('nav.html'); ?>

        <div class="content-body">
            <div class="container-fluid">
                <div class="row page-titles mx-0">
                    <div class="col-sm-6 p-md-0">
                        <div class="welcome-text">
                            <h3>Consultation des devoirs de la classe</h3>
                        </div>
                    </div>
                    <div class="col-sm-6 p-md-0 justify-content-sm-end mt-2 mt-sm-0 d-flex">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="../etudiant/">Accueil</a></li>
                            <li class="breadcrumb-item active"><a href="javascript:void(0);">Mes Devoirs</a></li>
                        </ol>
                    </div>
                </div>

                <!-- Formulaire de sélection des critères -->
                <div class="row">
                    <div class="col-lg-12">
                        <div class="card">
                            <div class="card-header">
                                <h4 class="card-title"><i class="la la-search"></i> Rechercher les devoirs de ma classe</h4>
                                <small class="text-muted">Sélectionnez les critères pour voir les notes de devoirs de toute la classe</small>
                            </div>
                            <div class="card-body">
                                <form method="GET" action="">
                                    <div class="row">
                                        <div class="col-md-4">
                                            <div class="input-group mb-3">
                                                <div class="input-group-prepend">
                                                    <label class="input-group-text">ECUE</label>
                                                </div>
                                                <select name="ecue" class="form-control disabling-options" required>
                                                    <option value="">Sélectionner une ECUE</option>
                                                    <?php 
                                                    $sql_ecues = "SELECT DISTINCT e.code_ecue, e.libelle 
                                                                 FROM ecue e 
                                                                 JOIN ligne2 l ON e.code_ecue = l.code_ecue 
                                                                 WHERE l.classe IN (SELECT classe FROM inscription WHERE candidat='$matricule')
                                                                 ORDER BY e.libelle";
                                                    $result_ecues = $connexion->query($sql_ecues);
                                                    while($ecue_item = $result_ecues->fetch_assoc()){
                                                    ?>
                                                    <option value="<?php echo $ecue_item['code_ecue'];?>"
                                                        <?php echo (isset($_GET['ecue']) && $_GET['ecue'] == $ecue_item['code_ecue']) ? 'selected' : ''; ?>>
                                                        <?php echo str_replace("+","'",$ecue_item['libelle']." [".$ecue_item["code_ecue"]."]");?>
                                                    </option>
                                                    <?php } ?>
                                                </select>
                                            </div>
                                        </div>

                                        <div class="col-md-2">
                                            <div class="input-group mb-3">
                                                <div class="input-group-prepend">
                                                    <label class="input-group-text">Semestre</label>
                                                </div>
                                                <select name="semestre" class="form-control disabling-options" required>
                                                    <option value="">Sélectionner</option>
                                                    <?php 
                                                    $sql_semestres = "SELECT DISTINCT semestre FROM ligne2 ORDER BY semestre";
                                                    $result_semestres = $connexion->query($sql_semestres);
                                                    while($semestre_item = $result_semestres->fetch_assoc()){
                                                    ?>
                                                    <option value="<?php echo $semestre_item['semestre'];?>"
                                                        <?php echo (isset($_GET['semestre']) && $_GET['semestre'] == $semestre_item['semestre']) ? 'selected' : ''; ?>>
                                                        <?php echo str_replace("+","'",$semestre_item['semestre']);?>
                                                    </option>
                                                    <?php } ?>
                                                </select>
                                            </div>
                                        </div>

                                        <div class="col-md-3">
                                            <div class="input-group mb-3">
                                                <div class="input-group-prepend">
                                                    <label class="input-group-text">Type d'évaluation</label>
                                                </div>
                                                <select name="type_evaluation" class="form-control disabling-options" required>
                                                    <option value="">Sélectionner</option>
                                                    <option value="Devoir de classe" <?php echo (isset($_GET['type_evaluation']) && $_GET['type_evaluation'] == 'Devoir de classe') ? 'selected' : ''; ?>>Devoir de classe</option>
                                                    <option value="Devoir pratique" <?php echo (isset($_GET['type_evaluation']) && $_GET['type_evaluation'] == 'Devoir pratique') ? 'selected' : ''; ?>>Devoir pratique</option>
                                                </select>
                                            </div>
                                        </div>

                                        <div class="col-md-2">
                                            <div class="input-group mb-3">
                                                <div class="input-group-prepend">
                                                    <label class="input-group-text">Année</label>
                                                </div>
                                                <select name="annee" class="form-control disabling-options" required>
                                                    <option value="">Sélectionner</option>
                                                    <?php 
                                                    $sql_annees = "SELECT DISTINCT annee FROM ligne2 ORDER BY annee DESC";
                                                    $result_annees = $connexion->query($sql_annees);
                                                    while($annee_item = $result_annees->fetch_assoc()){
                                                    ?>
                                                    <option value="<?php echo $annee_item['annee'];?>"
                                                        <?php echo (isset($_GET['annee']) && $_GET['annee'] == $annee_item['annee']) ? 'selected' : ''; ?>>
                                                        <?php echo str_replace("+","'",$annee_item['annee']);?>
                                                    </option>
                                                    <?php } ?>
                                                </select>
                                            </div>
                                        </div>

                                        <div class="col-md-1">
                                            <button type="submit" class="btn btn-primary" style="margin-top: 8px;">
                                                <i class="la la-search"></i> Voir
                                            </button>
                                        </div>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>

                <?php if($show_table && $classe_info && count($devoirs_classe) > 0): ?>
                <!-- Affichage des devoirs de la classe -->
                <div class="row">
                    <div class="col-lg-12">
                        <div class="card">
                            <div class="card-header bg-primary text-white">
                                <h4 class="card-title text-white">
                                    <i class="la la-users"></i> Notes de devoirs - Classe <?php echo $classe_info['classe']; ?>
                                </h4>
                                <div class="text-white">
                                    <i class="la la-info-circle"></i> Votre ligne est surlignée en jaune
                                </div>
                            </div>
                            <div class="card-body">
                                <div class="text-dark mb-3">
                                    <strong><i class="la la-info-circle"></i> Informations:</strong><br>
                                    <strong>ECUE:</strong> <?php echo str_replace("+", "'", $classe_info['ecue']); ?><br>
                                    <strong>Semestre:</strong> <?php echo $classe_info['semestre']; ?><br>
                                    <strong>Type d'évaluation:</strong> <?php echo $classe_info['type_evaluation']; ?><br>
                                    <strong>Année académique:</strong> <?php echo $classe_info['annee']; ?><br>
                                    <strong>Nombre d'étudiants:</strong> <?php echo count($devoirs_classe); ?>
                                </div>

                                <div class="table-responsive">
                                    <table class="table table-bordered table-striped">
                                        <thead class="thead-dark">
                                            <tr>
                                                <th style="width: 50px;">N°</th>
                                              
                                                <th>Nom et Prénom</th>
                                                <?php for($i = 1; $i <= $classe_info['nb_devoirs']; $i++): ?>
                                                <th class="text-center">D<?php echo $i; ?></th>
                                                <?php endfor; ?>
                                                <th class="text-center">Moyenne</th>

                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php 
                                            $numero = 1;
                                            foreach($devoirs_classe as $etudiant): 
                                                $row_class = $etudiant['est_connecte'] ? 'highlight-student' : '';
                                                $moyenne = $etudiant['moyenne'];
                                                
                                                // Déterminer l'appréciation
                                                if($moyenne >= 16) {
                                                    $appreciation = "Excellent";
                                                    $badge_class = "badge-success";
                                                } elseif($moyenne >= 14) {
                                                    $appreciation = "Très bien";
                                                    $badge_class = "badge-info";
                                                } elseif($moyenne >= 12) {
                                                    $appreciation = "Bien";
                                                    $badge_class = "badge-primary";
                                                } elseif($moyenne >= 10) {
                                                    $appreciation = "Assez bien";
                                                    $badge_class = "badge-warning";
                                                } else {
                                                    $appreciation = "Insuffisant";
                                                    $badge_class = "badge-danger";
                                                }
                                            ?>
                                            <tr class="<?php echo $row_class; ?>">
                                                <td class="text-center"><?php echo $numero; ?></td>
                                              
                                                <td>
                                                    <?php echo $etudiant['nom_prenom']; ?>
                                                    <?php if($etudiant['est_connecte']): ?>
                                                    <span class="badge badge-warning ml-2">VOUS</span>
                                                    <?php endif; ?>
                                                </td>
                                                <?php foreach($etudiant['notes'] as $note): 
                                                    if($note >= 10) {
                                                        $note_badge = "badge-success";
                                                    } else {
                                                        $note_badge = "badge-danger";
                                                    }
                                                ?>
                                                <td class="text-center">
                                                    <span class="badge <?php echo $note_badge; ?>"><?php echo number_format($note, 2); ?></span>
                                                </td>
                                                <?php endforeach; ?>
                                                <td class="text-center">
                                                    <strong><span class="badge <?php echo $badge_class; ?>"><?php echo number_format($moyenne, 2); ?></span></strong>
                                                </td>
                                               </tr>
                                            <?php 
                                            $numero++;
                                            endforeach; 
                                            ?>
                                        </tbody>
                                    </table>
                                </div>

                                <div class="text-dark mt-3">
                                    <strong><i class="la la-chart-line"></i> Statistiques de la classe:</strong><br>
                                    Nombre d'étudiants: <strong><?php echo count($devoirs_classe); ?></strong><br>
                                    Nombre de devoirs: <strong><?php echo $classe_info['nb_devoirs']; ?></strong><br>
                                    <?php 
                                    $moyennes = array_column($devoirs_classe, 'moyenne');
                                    $moyenne_classe = array_sum($moyennes) / count($moyennes);
                                    ?>
                                    Moyenne de la classe: <strong><?php echo number_format($moyenne_classe, 2); ?>/20</strong><br>
                                    Meilleure moyenne: <strong><?php echo number_format(max($moyennes), 2); ?>/20</strong><br>
                                    Plus faible moyenne: <strong><?php echo number_format(min($moyennes), 2); ?>/20</strong>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <?php elseif(isset($_GET['ecue'])): ?>
                <div class="row">
                    <div class="col-lg-12">
                        <div class="alert alert-warning">
                            <i class="la la-exclamation-triangle"></i> Aucun devoir trouvé pour ces critères. Vérifiez que vous avez bien sélectionné les bonnes informations.
                        </div>
                    </div>
                </div>
                <?php endif; ?>
            </div>
        </div>

        <div class="footer">
            <div class="copyright">
                <p>Copyright © Designed &amp; Developpé par <a href="htpps:/www.cet-up.com" target="_blank">CETUP</a> 2023</p>
            </div>
        </div>
    </div>

    <script src="../vendor/global/global.min.js"></script>
    <script src="../vendor/bootstrap-select/dist/js/bootstrap-select.min.js"></script>
    <script src="../js/custom.min.js"></script>
    <script src="../js/dlabnav-init.js"></script>
    <script src="../vendor/svganimation/vivus.min.js"></script>
    <script src="../vendor/svganimation/svg.animation.js"></script>
    <script src="../vendor/select2/js/select2.full.min.js"></script>
    <script src="../js/plugins-init/select2-init.js"></script>
</body>
</html>

<?php 
} else {
    header("location: ../connexion");
}
?>
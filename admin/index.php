<?php 
include '../php/connexion.php';
include '../php/lib.php';

session_start();

if( $_SESSION['id'] == session_id() and $_SESSION['role'] === "Administrateur" and  isset($_SESSION['univ'])){

?>


<!DOCTYPE html>
<html lang="en">

<head>
	
	<meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title><?php echo  $_SESSION['univ'];?> - Administrateur </title>
    <!-- Favicon icon -->
    <link rel="icon" type="image/png" sizes="16x16" href="../administrateur/<?php echo  $_SESSION['logo_univ']?>">
    <link rel="stylesheet" href="../vendor/jqvmap/css/jqvmap.min.css">
	<link rel="stylesheet" href="../vendor/chartist/css/chartist.min.css">
	
		<link rel="stylesheet" href="../vendor/bootstrap-select/dist/css/bootstrap-select.min.css">
 
	    <link rel="stylesheet" href="../vendor/select2/css/select2.min.css">
	<!-- Summernote -->
    <link href="../vendor/summernote/summernote.css" rel="stylesheet">
	<link rel="stylesheet" href="../vendor/bootstrap-select/dist/css/bootstrap-select.min.css">
    <link rel="stylesheet" href="../css/style.css">
    <link rel="stylesheet" href="../css/skin-3.css">

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
            Nav header start
        ***********************************-->
        <div class="nav-header">
            <a href="index.html" class="brand-logo">
                <?php echo $_SESSION['univ'];?>
                <img class="logo-abbr" src="../administrateur/<?php echo  $_SESSION['logo_univ']?>" alt="">
                <img class="logo-compact" src="../administrateur/<?php echo  $_SESSION['logo_univ']?>" alt="">
                <img class="brand-title" src="../administrateur/<?php echo  $_SESSION['logo_univ']?>" alt="">
            </a>

            <div class="nav-control">
                <div class="hamburger">
                    <span class="line"></span><span class="line"></span><span class="line"></span>
                </div>
            </div>
        </div>
        <!--**********************************
            Nav header end
        ***********************************-->

        <!--**********************************
            Header start
        ***********************************-->
        <div class="header">
            <div class="header-content">
                <nav class="navbar navbar-expand">
                    <div class="collapse navbar-collapse justify-content-between">
                        <div class="header-left">
                          <p>MODULE ADMINISTRATEUR</p>
                        </div>

                        <ul class="navbar-nav header-right">
                            <li class="nav-item dropdown notification_dropdown">
                                <a class="nav-link bell ai-icon" href="#" role="button" data-toggle="dropdown">
                                    <svg id="icon-user" xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewbox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-bell">
										<path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"></path>
										<path d="M13.73 21a2 2 0 0 1-3.46 0"></path>
									</svg>
                                    <div class="pulse-css"></div>
                                </a>
                                <div class="dropdown-menu dropdown-menu-right">
                                    <ul class="list-unstyled">
                                        
                                      
                                      
                                    </ul>
                                    <a class="all-notification" href="#">See all notifications <i class="ti-arrow-right"></i></a>
                                </div>
                            </li>
                            <li class="nav-item dropdown header-profile">
                                <a class="nav-link" href="#" role="button" data-toggle="dropdown">
                                    <img src="../administrateur/<?php echo $_SESSION['img']?>" width="20" alt="">
                                </a>
                                <div class="dropdown-menu dropdown-menu-right">
                                    <a href="" data-toggle="modal" data-target="#moncompte" class="dropdown-item ai-icon">
                                        <svg id="icon-user1" xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewbox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-user"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path><circle cx="12" cy="7" r="4"></circle></svg>
                                        <span class="ml-2">MON COMPTE </span>
                                    </a>
                                    <a href="../login" class="dropdown-item ai-icon">
                                        <svg id="icon-logout" xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewbox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-log-out"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"></path><polyline points="16 17 21 12 16 7"></polyline><line x1="21" y1="12" x2="9" y2="12"></line></svg>
                                        <span class="ml-2">DECONNEXION </span>
                                    </a>
                                </div>
                            </li>
                        </ul>
                    </div>
                </nav>
            </div>
        </div>
        <!--**********************************
            Header end ti-comment-alt
        ***********************************-->

        <!--**********************************
            Sidebar start
        ***********************************-->
      <?php 
         include 'nav.php';
      ?>
        <!--**********************************
            Sidebar end
        ***********************************-->
		
        <!--**********************************
            Content body start
        ***********************************-->
        <div class="content-body">
            <div class="container-fluid">
                <div class="row page-titles mx-0">
                    <div class="col-sm-6 p-md-0">
                        <div class="welcome-text">
                            <h4 id="salutation"></h4>
                            <p class="mb-0"><?php echo $_SESSION['nom_user']; ?></p>
                        </div>
                    </div>
                    <div class="col-sm-6 p-md-0 justify-content-sm-end mt-2 mt-sm-0 d-flex">
                        <ol class="breadcrumb">
                            
                            <li class="breadcrumb-item active"><a href="javascript:void(0)"><?php echo $_SESSION['role'];?></a></li>
                        </ol>
                    </div>
                </div>

                <!-- Formulaire de filtrage -->
                <div class="row">
                    <div class="col-lg-12">
                        <div class="card">
                            <div class="card-header">
                                <h4 class="card-title"><i class="la la-filter"></i> Filtrer les logs</h4>
                            </div>
                            <div class="card-body">
                                <form method="GET" action="" id="filterForm">
                                    <div class="row">
                                        <div class="col-md-3">
                                            <div class="form-group">
                                                <label>Date de début</label>
                                                <input type="date" class="form-control" name="date_debut" 
                                                       value="<?php echo isset($_GET['date_debut']) ? $_GET['date_debut'] : ''; ?>">
                                            </div>
                                        </div>
                                        
                                        <div class="col-md-3">
                                            <div class="form-group">
                                                <label>Date de fin</label>
                                                <input type="date" class="form-control" name="date_fin" 
                                                       value="<?php echo isset($_GET['date_fin']) ? $_GET['date_fin'] : ''; ?>">
                                            </div>
                                        </div>
                                        
                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <label>Utilisateur</label>
                                                <select class="disabling-options" name="user_id">
                                                    <option value="">Tous les utilisateurs</option>
                                                    <?php 
                                                    $sql_users = "SELECT id, nom,role FROM utilisateur ORDER BY nom";
                                                    $result_users = $connexion->query($sql_users);
                                                    while($user = $result_users->fetch_assoc()){
                                                        $selected = (isset($_GET['user_id']) && $_GET['user_id'] == $user['id']) ? 'selected' : '';
                                                    ?>
                                                    <option value="<?php echo $user['id'];?>" <?php echo $selected;?>>
                                                        <?php echo $user['nom']."(".$user['role'].")";?>
                                                    </option>
                                                    <?php } ?>
                                                </select>
                                            </div>
                                        </div>
                                        
                                        <div class="col-md-2">
                                            <div class="form-group">
                                                <label>&nbsp;</label>
                                                <button type="submit" class="btn btn-primary btn-block">
                                                    <i class="la la-search"></i> Filtrer
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <?php if(isset($_GET['date_debut']) || isset($_GET['date_fin']) || isset($_GET['user_id'])): ?>
                                    <div class="row">
                                        <div class="col-md-12">
                                            <a href="?" class="btn btn-warning btn-sm">
                                                <i class="la la-times"></i> Réinitialiser les filtres
                                            </a>
                                        </div>
                                    </div>
                                    <?php endif; ?>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>

                <?php
                // Construction de la requête SQL avec filtres
                $sql = "SELECT * FROM userlog WHERE UserID IN (SELECT id FROM utilisateur WHERE univ='".$_SESSION['univ']."')";

                // Filtrage par date de début
                if(isset($_GET['date_debut']) && !empty($_GET['date_debut'])) {
                    $date_debut = $_GET['date_debut'] . ' 00:00:00';
                    $sql .= " AND datetime >= '".$date_debut."'";
                }

                // Filtrage par date de fin
                if(isset($_GET['date_fin']) && !empty($_GET['date_fin'])) {
                    $date_fin = $_GET['date_fin'] . ' 23:59:59';
                    $sql .= " AND datetime <= '".$date_fin."'";
                }

                // Filtrage par utilisateur
                if(isset($_GET['user_id']) && !empty($_GET['user_id'])) {
                    $user_id = $_GET['user_id'];
                    $sql .= " AND UserID = '".$user_id."'";
                }

                $sql .= " ORDER BY datetime DESC";

                $resultat = $connexion->query($sql);
                $total_logs = $resultat->num_rows;
                ?>

                <div class="row">
                    <div class="col-lg-12">
                        <div class="card">
                            <div class="card-header">
                                <h4 class="card-title">
                                    Historique des logs 
                                    <span class="badge badge-primary badge-pill"><?php echo $total_logs; ?> résultat(s)</span>
                                </h4>
                                <?php if(isset($_GET['date_debut']) || isset($_GET['date_fin']) || isset($_GET['user_id'])): ?>
                                    <small class="text-muted">
                                        <i class="la la-filter"></i> Filtres actifs
                                        <?php if(isset($_GET['date_debut']) && !empty($_GET['date_debut'])): ?>
                                            | Du: <?php echo date('d/m/Y', strtotime($_GET['date_debut'])); ?>
                                        <?php endif; ?>
                                        <?php if(isset($_GET['date_fin']) && !empty($_GET['date_fin'])): ?>
                                            | Au: <?php echo date('d/m/Y', strtotime($_GET['date_fin'])); ?>
                                        <?php endif; ?>
                                        <?php if(isset($_GET['user_id']) && !empty($_GET['user_id'])): ?>
                                            | Utilisateur: <?php echo getNomUtilisateurParId($connexion, $_GET['user_id']); ?>
                                        <?php endif; ?>
                                    </small>
                                <?php endif; ?>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table id="example3" class="table table-sm mb-0">
                                        <thead>

                                       
                                            <tr>
                                                <th class="align-middle">
                                                    <div class="custom-control custom-checkbox">
														<input type="checkbox" class="custom-control-input" id="checkAll">
														<label class="custom-control-label" for="checkAll"></label>
													</div>
                                                </th>
                                                <th class="align-middle">LogID</th>
                                                <th class="align-middle pr-7">Etablissement</th>
                                                <th class="align-middle pr-7">Utilisateur</th>
                                             
                                                <th class="align-middle" style="min-width: 12.5rem;">Action</th>
                                                <th class="align-middle text-center">Date-Heure</th>
                                                <th class="align-middle text-right">Adresse Ip</th>
                                                <th class="align-middle text-right">Informations</th>
                                                
                                            </tr>
                                        </thead>
                                        <tbody id="orders">
                                        <?php 
                                        while($log = $resultat->fetch_assoc()){
                                   ?>
                                            <tr class="btn-reveal-trigger">
                                                <td class="py-2">
                                                    <div class="custom-control custom-checkbox mr-3">
														<input type="checkbox" class="custom-control-input" id="checkbox<?php echo $log['LogID'];?>">
														<label class="custom-control-label" for="checkbox<?php echo $log['LogID'];?>"></label>
													</div>
                                                </td>
                                                <td class="py-2">
                                                    <a href="#">
                                                        <strong><?php echo $log['LogID'];?></strong></a></td>
                                                        <td class="py-2">
                                                    <a href="#">
                                                        <strong><?php echo getEtablissementUtilisateur($log['UserID'],$connexion);?></strong></a></td>
                                                <td class="py-2"><?php echo getNomUtilisateurParId($connexion,$log['UserID'])?></td>
                                                <td class="py-2">
                                                    <p class="mb-0 text-500"><?php echo $log['Action'];?></p>
                                                </td>
                                                <td class="py-2">
                                                    <?php echo $log['datetime'];?>
                                                </td>
                                               
                                                <td class="py-2 text-right"> <?php echo $log['IPAddress'];?>
                                                </td>
                                                <td class="py-2">
                                                    <p class="mb-0 text-500"><?php echo $log['AdditionalInfo'];?></p>
                                                </td>
                                               
                                            </tr>
                                            <?php }?>

                                            <?php if($total_logs == 0): ?>
                                            <tr>
                                                <td colspan="8" class="text-center py-4">
                                                    <i class="la la-inbox" style="font-size: 48px; color: #ccc;"></i>
                                                    <p class="text-muted">Aucun log trouvé pour ces critères</p>
                                                </td>
                                            </tr>
                                            <?php endif; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
 </div>

        
<div class="modal" id="moncompte" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h2 class="modal-title" id="exampleModalLabel">Profil Administrateur <i class="la la-user"></i></h2>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <!-- Formulaire pour enregistrer le type d'agent -->
                <form id="typeAgentForm" method="post" action="traitement5">
                    <div class="form-group">
                        <label for="nom">Nom</label>
                        <input type="text" class="form-control" id="nom" name="nom" value="<?php echo $_SESSION['nom_user'];?>" required>
                    </div>
                    <div class="form-group">
                        <label for="login">Login( nom de connexion) </label>
                        <input type="text" class="form-control" id="login" name="login" value="<?php echo $_SESSION['login'];?>" required>
                    </div>
                    <div class="form-group">
                        <label for="mdp">Mot de passe </label>
                        <input type="text" class="form-control" id="mdp" name="mdp" value="<?php echo $_SESSION['mdp'];?>" required>
                    </div>
                    <button type="submit" class="btn btn-primary">Modifier</button>
                </form>
            </div>
        </div>
    </div>
</div>

<div class="modal" id="messageModal" tabindex="-1" role="dialog" aria-labelledby="messageModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="messageModalLabel"></h5>
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
    <script src="../js/dlabnav-init.js"></script>	
    
       <script src="../vendor/select2/js/select2.full.min.js"></script>

    <script src="../js/plugins-init/select2-init.js"></script>
	<!-- Chart sparkline plugin files -->
    <script src="../vendor/jquery-sparkline/jquery.sparkline.min.js"></script>
	<script src="../js/plugins-init/sparkline-init.js"></script>
	
	<!-- Chart Morris plugin files -->
    <script src="../vendor/raphael/raphael.min.js"></script>
    <script src="../vendor/morris/morris.min.js"></script> 
	
    <!-- Init file -->
    <script src="../js/plugins-init/widgets-script-init.js"></script>
	
	<!-- Demo scripts -->
    <script src="../js/dashboard/dashboard.js"></script>
	
	<!-- Summernote -->
    <script src="../vendor/summernote/js/summernote.min.js"></script>
    <!-- Summernote init -->
    <script src="../js/plugins-init/summernote-init.js"></script>
	
	<!-- Svganimation scripts -->
    <script src="../vendor/svganimation/vivus.min.js"></script>
    <script src="../vendor/svganimation/svg.animation.js"></script>
   

    <script src="../vendor/datatables/js/jquery.dataTables.min.js"></script>
    <script src="../js/plugins-init/datatables.init.js"></script>

    <script>
    $(document).ready(function() {
        // Vérifier si les attributs "erreur" ou "success" sont présents dans l'URL
        var urlParams = new URLSearchParams(window.location.search);
        var erreur = urlParams.get('erreur');
        var success = urlParams.get('sucess');

        // Afficher le modal si l'un des attributs est présent
        if (erreur || success) {
            var message = erreur ? "Erreur : " + erreur : "Message : " + success;
            $('#messageBody').text(message);
            $('#messageModal').modal('show');

            // Effacer les attributs de l'URL
            window.history.replaceState({}, document.title, window.location.pathname);
        }
    });
</script>


    <script>
    // Récupérer l'élément avec l'ID "salutation"
    var salutationElement = document.getElementById("salutation");

    // Obtenir l'heure actuelle
    var heure = new Date().getHours();

    // Déterminer si c'est le matin, l'après-midi ou le soir
    var salutation;
    if (heure >= 5 && heure < 12) {
        salutation = "Bonjour";
    } else if (heure >= 12 && heure < 18) {
        salutation = "Bon après-midi";
    } else {
        salutation = "Bonsoir";
    }

    // Afficher la salutation dans l'élément
    salutationElement.textContent = salutation;
</script>
		
</body>
</html>
<?php 

}else{
    header("location: ../login");
}
?>
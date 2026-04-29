<?php 
include '../php/connexion.php';
include '../php/lib.php';

session_start();

if( $_SESSION['id'] == session_id() and  $_SESSION['role']=="candidat"){




?>

<!DOCTYPE html>
<html lang="en">

<head>
	
	<meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>UDSN -Espace étudiant </title>
    <!-- Favicon icon -->
    <link rel="icon" type="image/png" sizes="16x16" href="../administrateur/logo/favicon.png">
    
    <link rel="stylesheet" href="../vendor/bootstrap-select/dist/css/bootstrap-select.min.css">
    <link rel="stylesheet" href="../css/style.css">
	
	<!-- Pick date -->
    <link rel="stylesheet" href="../vendor/pickadate/themes/default.css">
    <link rel="stylesheet" href="../vendor/pickadate/themes/default.date.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" integrity="sha256-w6L/6ZA7a0EibN42fsRg4kg0tRqBej6YHt6t1P0g0e9rFtGq49BGTmUzTSZzWGnD" crossorigin="anonymous">


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
        
              
                 
                 <a href="index" class="brand-logo">
                           <img class="logo-abbr" src="../administrateur/logo/logo.png" alt="Logo">
                           
                           <span class="titre d-none d-md-inline text-white"><?php   echo isset($_SESSION['etablissement']) ? ($_SESSION['etablissement']) : ""  ; ?></span>
                             

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
                       

                        <ul class="navbar-nav header-right">
                            <li class="nav-item dropdown notification_dropdown">
                                <a class="nav-link bell ai-icon" href="#" role="button" data-toggle="dropdown">
                                    <svg id="icon-user" xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewbox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-bell">
										<path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"></path>
										<path d="M13.73 21a2 2 0 0 1-3.46 0"></path>
									</svg>
                                    <div class="pulse-css"></div>
                                </a>
                               
                            </li>
                            <li class="nav-item dropdown header-profile">
                                <?php 
                                    if(isset($_SESSION['img'])){
                                ?>
                                <a class="nav-link" href="#" role="button" data-toggle="dropdown">
                                    <img src="<?php echo $_SESSION['img'];?>" width="70" alt="">
                                </a>

                                <?php }?>
                                  
                                <div class="dropdown-menu dropdown-menu-right">
                                    <a href="#" data-toggle="modal" data-target="#myModal" class="dropdown-item ai-icon">
                                        <svg id="icon-user1" xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewbox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-user"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path><circle cx="12" cy="7" r="4"></circle></svg>
                                        <span class="ml-2">Mon Profile </span>
                                    </a>
                                   
                                    <a href="../connexion" class="dropdown-item ai-icon">
                                        <svg id="icon-logout" xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewbox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-log-out"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"></path><polyline points="16 17 21 12 16 7"></polyline><line x1="21" y1="12" x2="9" y2="12"></line></svg>
                                        <span class="ml-3">Deconnexion </span>
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
      
        <!--**********************************
            Sidebar end
        ***********************************-->

		<?php 
           include('nav.html');
           
        ?>
		
        <!--**********************************
            Content body start
        ***********************************-->

        



        <div class="content-body">
            <!-- row -->
            <div class="container-fluid">
				    
                <div class="row page-titles mx-0">
                    <div class="col-sm-6 p-md-0">
                        <div class="welcome-text">
                            <h4>ESPACE ETUDIANT</h4>
                        </div>
                    </div>
                    <div class="col-sm-6 p-md-0 justify-content-sm-end mt-2 mt-sm-0 d-flex">
                        <ol class="breadcrumb">
                          
                            <li class="breadcrumb-item active"><a href="index">UDSN</a></li>
                            <li class="breadcrumb-item active"><a href="javascript:void(0);"> Mon profil</a></li>
                        </ol>
                    </div>
                </div>
				
				<div class="row">
					<div class="col-xl-3 col-xxl-4 col-lg-6">
						<div class="row">
							<div class="col-lg-6 justify-content-center">
								<div class="card">
									<img width="100" heigth="50" class="img-fluid" src="../images/univ.png" alt="">
								</div>
							</div>
						</div>
					</div>
                    <?php 
                    
if(idCandidatExiste1($_SESSION['id_user'],$connexion)){
    $statut=getStatutCandidat($_SESSION['id_user'],$connexion);
    $code=getCodeCandidat($_SESSION['id_user'],$connexion);
    $annee=getAnneeCandidat($_SESSION['id_user'],$connexion);
    if(isset($statut) and isset($code)){
    

                                        
                                
                                        if($statut == "en cours"){

                                          
                                   ?> 

					<div class="col-xl-9 col-xxl-8 col-lg-8">
						<div class="card">
							<div class="card-body">
								<h2 class="text-primary">Procédure de pré-inscription à l'UDSN</h2>
								<ul class="list-group mb-3 list-group-flush">
									<li class="list-group-item border-0 px-0">Veuillez remplir le formulaire de pré-inscription pour soumettre votre candidature.</li>
										</ul>


                                <a href="formulaire" class="btn btn-primary btn-lg btn-success"><i class="fa fa-send"></i>Formulaire</a>
                                      
                                
							</div>
						</div>


                       


					</div>
				</div>
                <?php 
                           if(isset($_GET['candidat'])){
                        ?>

                        <div class="card">
							<div class="card-body">
								<p class="text-primary">Votre Préinscription à été enregistré au numéro <?php echo $_GET['candidat'];?>.            
                                 </p>
							</div>
						</div>

                        <?php }?>
                           
                
                                      <?php if ( !checkCandidateValidation($connexion,$code,$annee)){?>               
             
				<div class="col-xl-12 col-xxl-12">
                        <div class="card">
                            <div class="card-body">
                                <div class="row">
       
                          
                         
                                    <div class="col-xl-9 col-xxl-8 col-lg-8">
                                        <div class="alert alert-danger left-icon-big alert-dismissible fade show">
                                            <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span><i class="mdi mdi-close"></i></span>
                                            </button>
                                            <div class="media">
                                                <div class="alert-left-icon-big">
                                                    <span><i class="mdi mdi-alert"></i></span>
                                                </div>
                                                <div class="media-body">
                                                   
                                                    <p class="mb-2" > <h3>Vous n'avez importez aucun document pour votre préinscription. Veuillez joindre vos documents pour valider votre préinscription.
                                                    </h3><a href="document" class="btn btn-primary btn-lg  btn-light"><i class="ti-clip"></i>  Joindre mes documents</a>  </p>
                                                    
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <?php }else{?>
                                   
                                    <div class="col-xl-9 col-xxl-8 col-lg-8">
                                        <div class="alert alert-info left-icon-big alert-dismissible fade show">
                                            <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span><i class="mdi mdi-close"></i></span>
                                            </button>
                                            <div class="media">
                                                <div class="alert-left-icon-big">
                                                    <span><i class="mdi mdi-help-circle-outline"></i></span>
                                                </div>
                                                <div class="media-body">
                                                    <h5 class="mt-1 mb-1">Préisncription en cours <?php echo $code;?></h5>
                                                    <p class="mb-2" > <h3>Le traitement de votre dossier de préinscription est en cours. Vous recevrez un email de confirmation </h3></p>
                                                    
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                   

                                    <?php }}else{?>
                                        <div class="col-xl-9 col-xxl-8 col-lg-8">
                                      <div class="card">
                                        <div class="card-header">
                                           <h3> Année Universitaire: <?php echo getAnneeC($_SESSION["id_user"],$connexion);?></h3>
                                           
                                        </div>
                                        <div class="card-body">
                                        <p>Matricule : <b  class="text-warning"> <?php echo $_SESSION["code"];?></b></p>
                                            <p>Nom(s) : <b  class="text-warning"> <?php echo strtoupper(getNomCandidat($_SESSION["id_user"],$connexion));   ?> </b></p>
                                            <p>Prénom(s) : <b class="text-warning"> <?php echo mettrePremieresLettresMajuscules( getPrenomCandidat($_SESSION["id_user"],$connexion));  ?> </b></p>
                                            <p>Classe : <b class="text-warning"> <?php echo mettrePremieresLettresMajuscules( getClasseCandidat($_SESSION["id_user"],$connexion));  ?> </b></p>
                                            <p>Specialité : <b class="text-warning"> <?php echo  getSpecialiteCandidat($_SESSION["id_user"],$connexion); ?> </b></p>
                                            <p>Niveau : <b class="text-warning"> <?php echo    getNiveauCandidat($_SESSION["id_user"],$connexion);?> </b></p>
                                            <p>Etablissement : <b class="text-warning"> <?php echo mettrePremieresLettresMajuscules( getEtablissementCandidat($_SESSION["id_user"],$connexion));  ?> </b></p>

                                        </div>
                                      </div>
                                    </div>
                                    <?php }?>
                                 
                                </div>

                            </div>
                        </div>
                    </div>
                 
            </div>
        </div>
        <!--**********************************
            Content body end
        ***********************************-->
        <?php }}else{?>
            <div class="col-xl-9 col-xxl-8 col-lg-8">
						<div class="card">
							<div class="card-body">
								<h2 class="text-primary">Procédure de pré-inscription à l'UDSN</h2>
								<ul class="list-group mb-3 list-group-flush">
									<li class="list-group-item border-0 px-0">Veuillez remplir le formulaire de pré-inscription pour soumettre votre candidature.</li>
										</ul>


                                <a href="formulaire" class="btn btn-primary btn-lg btn-success"><i class="fa fa-send"></i>Formulaire</a>
                                      
                                
							</div>
						</div>


                       


					</div>
				</div>
                <?php }?>

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

        <div class="modal" id="messageModal" tabindex="-1" role="dialog" aria-labelledby="messageModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="messageModalLabel">SGUDSN</h5>
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


<div class="modal fade" id="myModal">
        <div class="modal-dialog">
            <div class="modal-content">

                <!-- Modal Header -->
                <div class="modal-header">
                    <h4 class="modal-title"> Mon Compte</h4>
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                </div>
                <form id="accountForm" method="post" action="traitement">
                <!-- Modal Body -->
                <div class="modal-body">
                   
                        <div class="form-group">
                            <label for="loginInput">Login:</label>
                            <input type="text" class="form-control" id="loginInput" name="loginInput" value="<?php echo $_SESSION['login'];?>">
                        </div>
                        <div class="form-group">
    <label for="passwordInput">Nouveau Mot de passe:</label>
    <div class="input-group">
        <input type="password" class="form-control" id="passwordInput"  name="passwordInput" value="<?php echo $_SESSION['mdp'];?>">
        <div class="input-group-append">
            <span class="input-group-text" id="togglePassword">
                <i class="fas fa-eye" onclick="togglePasswordVisibility()"></i>
            </span>
        </div>
    </div>
</div>
                    
                </div>

                <!-- Modal Footer -->
                <div class="modal-footer">
                <button type="submit" class="btn btn-success" >Sauvegarder</button>
                    <button type="button" class="btn btn-danger" data-dismiss="modal">Fermer</button>
                    
                </div>
                </form>

            </div>
        </div>
    </div>


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

	<!-- Svganimation scripts -->
    <script src="../vendor/svganimation/vivus.min.js"></script>
    <script src="../vendor/svganimation/svg.animation.js"></script>
  
	<!-- pickdate -->
    <script src="../vendor/pickadate/picker.js"></script>
    <script src="../vendor/pickadate/picker.time.js"></script>
    <script src="../vendor/pickadate/picker.date.js"></script>
	
	<!-- Pickdate -->
    <script src="../js/plugins-init/pickadate-init.js"></script>

    
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
    function togglePasswordVisibility() {
        var passwordInput = document.getElementById("passwordInput");
        var togglePasswordIcon = document.getElementById("togglePassword").firstElementChild;

        if (passwordInput.type === "password") {
            passwordInput.type = "text";
            togglePasswordIcon.classList.remove("la-eye");
            togglePasswordIcon.classList.add("la-eye-slash");
        } else {
            passwordInput.type = "password";
            togglePasswordIcon.classList.remove("la-eye-slash");
            togglePasswordIcon.classList.add("la-eye");
        }
    }
</script>

</body>
</html>
<?php 


}else{
    header("location: ../connexion");
}?>
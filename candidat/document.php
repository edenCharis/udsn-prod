<?php 
include '../php/connexion.php';
include '../php/lib.php';

session_start();

if( $_SESSION['id'] == session_id() and  $_SESSION['role']=="candidat"){

            if(isset($_GET['id'])){
                $id=$_GET['id'];


                $sql ="delete from validation where id=$id";

                  if($connexion->query($sql)){

                    $userIP = $_SERVER['REMOTE_ADDR'];

                    logUserAction($connexion,$_SESSION['id_user'],"suppression d'un fichier pour la preinscription",date("Y-m-d H:i:s"),$userIP,"valeur supprimée : $id");
            
                  }
            }


?>

<!DOCTYPE html>
<html lang="en">

<head>
	
	<meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>UDSN - Candidature de pré-inscription </title>
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
            <a href="#" class="brand-logo">
              
                   <h3 class="d-none d-md-inline"> <b style="color : white;">UDSN</b></h3>
                     <img class="logo-abbr" src="../administrateur/logo/logo.png" alt="">
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
                            <h4>GESTION DE MON PROFIL CANDIDAT</h4>
                        </div>
                    </div>
                    <div class="col-sm-6 p-md-0 justify-content-sm-end mt-2 mt-sm-0 d-flex">
                        <ol class="breadcrumb">
                          
                            <li class="breadcrumb-item active"><a href="index">UDSN</a></li>
                            <li class="breadcrumb-item active"><a href="javascript:void(0);"> Mes documents joints</a></li>
                        </ol>
                    </div>

                    
                </div>
                
		     <div class="row">
            
             <div class="col-md-12">

                   <p>Importez les fichies JPEG,GIF et PNG</p>
                <!-- Formulaire d'envoi d'images avec champ de sélection -->
                <form id="uploadForm" action="traitement1" method="post" enctype="multipart/form-data">
                                       <div class="input-group mb-3">
                                            <div class="custom-file">
                                                <input type="file" class="custom-file-input" name="img" required>
                                                <label class="custom-file-label">Fichier</label>
                                            </div>
                                        </div>
                                        <div class="input-group mb-3">
                                            <div class="input-group-prepend">
                                                <label class="input-group-text">Type de fichier : </label>
                                            </div>
                                            <select id="doc" class="" name="doc" required>
                                                <option selected=""></option>
                                               <?php 
                                                        $sql="select * from type_document ";
                                                        $resultat=$connexion->query($sql);
                                                        while($etablissement =$resultat->fetch_assoc()){
                                                ?>
                                                 <option><?php echo str_replace("+","'",$etablissement['libelle']);?></option>
                                                 <?php }?>
                                            </select>
                                        </div>

                    <div class="form-group">
                        <button type="submit" value="Enregistrer" class="btn btn-primary"><i class="ti-clip"></i>Enregistrer</button>
                    </div>
                </form>
            </div>
                            <?php 

              if(idCandidatExiste1($_SESSION['id_user'],$connexion)){

                               $code =getCodeCandidat($_SESSION['id_user'],$connexion);
                               $annee=getAnneeCandidat($_SESSION['id_user'],$connexion);


                               if($code !== null and $annee !== null){
                            
                               if(idCandidatValide($code,$connexion,$annee)){

                                $sql ="select * from validation where candidat='".$code."' and annee='".$annee."'";


                                $resultat=$connexion->query($sql);

                                while($doc=$resultat->fetch_assoc()){
                            ?>
                   <div class="col-sm-6 col-xxl-3 col-lg-3 col-md-6">
						<div class="card">
							<img class="img-fluid" src="<?php echo $doc['path']?>" alt="">
							<div class="card-body">
								<h4><?php echo   str_replace("+","'",$doc['document'])?></h4>
                                Statut de validation : <?php echo $doc['statut'];?></br>
								<a href="document?id=<?php echo $doc['id'];?>" class="btn btn-danger">Supprimer</a>
							</div>
						</div>
					</div>


                    <?php }}}}?>
                    
           

          
                  
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

        <div class="modal" id="messageModal" tabindex="-1" role="dialog" aria-labelledby="messageModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="messageModalLabel">UDSN</h5>
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
        var success = urlParams.get('success');
        var id =urlParams.get('id');

        // Afficher le modal si l'un des attributs est présent
        if (erreur || success ) {
            var message = erreur ? "Erreur : " + erreur : "Message : " + success;
            $('#messageBody').text(message);
            $('#messageModal').modal('show');

            // Effacer les attributs de l'URL
            window.history.replaceState({}, document.title, window.location.pathname);
        }

        if(id){
            var message="suppression effectué avec succès";
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
<?php 

session_start();
include ('../php/connexion.php');
include ('../php/lib.php');




if( $_SESSION['id'] == session_id() and  $_SESSION['role']=="enseignant"){



    
?>

<!DOCTYPE html>
<html lang="en">

<head>
	
	<meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>Scolarité </title>
    <!-- Favicon icon -->
    <link rel="icon" type="image/png" sizes="16x16"  href="../administrateur/<?php echo ( isset( $_SESSION['logo_univ']) && $_SESSION['logo_univ'] !== null)? $_SESSION['logo_univ'] : "logo.png"?>">
	<link rel="stylesheet" href="../vendor/bootstrap-select/dist/css/bootstrap-select.min.css">
	<link rel="stylesheet" href="../vendor/select2/css/select2.min.css">
    <link rel="stylesheet" href="../css/style.css">
	<link rel="stylesheet" href="../css/skin.css">
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
        <?php include "header.php" ;?>
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
            <!-- row -->
            <div class="container-fluid">
			
                <div class="row page-titles mx-0">
                    <div class="col-sm-6 p-md-0">
                        <div class="welcome-text">
                            <h4>Mon profil</h4>
                        </div>
                    </div>
                    <div class="col-sm-6 p-md-0 justify-content-sm-end mt-2 mt-sm-0 d-flex">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="../gesnote/">Gestion des notes</a></li>
                            <li class="breadcrumb-item active"><a href="edit-professor.html">Mon profil</a></li>
                          
                        </ol>
                    </div>
                </div>
                
                                       
				
				<div class="row">
					<div class="col-xl-12 col-xxl-12 col-sm-12">
                        <div class="card">
                            <div class="card-header">
								<h5 class="card-title">Informations personnelles</h5>
							</div>
							
					     <?php 
                            
                            
                             if(isset($_SESSION['id_user'])){


                                $sql ="select * from utilisateur where id=".$_SESSION['id_user'];

                                $resultat =$connexion->query($sql);

                                while($user = $resultat ->fetch_assoc()){
                            ?>
							<div class="card-body">
                                <form action="traitement" method="POST" enctype="multipart/form-data">
									<div class="row">
										<div class="col-lg-6 col-md-6 col-sm-12">
											<div class="form-group">
												<label class="form-label">Nom</label>
												<input type="text" class="form-control" value="<?php echo $user['nom'];?>" name="nom" >
											</div>
										</div>
											<div class="col-lg-6 col-md-6 col-sm-12">
											<div class="form-group">
												<label class="form-label text-danger">Code Unique</label>
												<input type="text" class="form-control" value="<?php echo $user['code_enseignant'];?>"  name="code_unique">
											</div>
										</div>
										
										
					                 
										<div class="col-lg-6 col-md-6 col-sm-12">
											<div class="form-group">
												<label class="form-label">Login</label>
												<input type="text" class="form-control" value="<?php echo $user['login'];?>" name="login">
											</div>
										</div>
										<div class="col-lg-6 col-md-6 col-sm-12">
											<div class="form-group custom-font" style="position: relative">
												<label class="form-label">Mot de passe</label>
												<span class="toggle-password" onclick="togglePassword()">
                                                <i class="fa fa-eye"></i>
												<input type="password" id="password" class="form-control" value="<?php echo $user['mdp'];?>" name="mdp">
												   
											</div>
										</div>
										<div class="col-lg-6 col-md-6 col-sm-12">
											<div class="form-group">
												<label class="form-label">Votre Statut</label>
												<input type="text" class="form-control"  value="<?php echo $user['statut'];?>" name="mdp_conf" disabled>
											</div>
										</div>
										<div class="col-lg-6 col-md-6 col-sm-12">
											<div class="form-group">
												<label class="form-label">Role</label>
												<input type="text" class="form-control"  value="<?php echo $user['role'];?>" disabled>
											</div>
										</div>
									
										<div class="col-lg-6 col-md-6 col-sm-12">
											<div class="form-group">
												<label class="form-label text-danger">Etablissement</label>
											  <select id="etablissement" class="form-class" name="etablissement" >
                                                <option selected=""></option>
                                               <?php 
                                                        $sql="select * from etablissement ";
                                                        $resultat=$connexion->query($sql);
                                                        while($etablissement =$resultat->fetch_assoc()){
                                                ?>
                                                 <option value="<?php echo $etablissement["code"];?>"><?php echo str_replace("+","'",$etablissement['libelle']);?></option>
                                                 <?php }?>
                                            </select>
											</div>
										</div>
									<div class="col-lg-6 col-md-6 col-sm-12">
											<div class="form-group">
												<label class="form-label text-danger">Année académique</label>
											  <select id="annee" class="form-class" name="annee" >
                                                <option selected=""></option>
                                               <?php 
                                                        $sql="select * from annee ";
                                                        $resultat=$connexion->query($sql);
                                                        while($etablissement =$resultat->fetch_assoc()){
                                                ?>
                                                 <option><?php echo str_replace("+","'",$etablissement['libelle']);?></option>
                                                 <?php }?>
                                            </select>
											</div>
										</div>
										<div class="col-lg-12 col-md-12 col-sm-12">
											<div class="form-group fallback w-100">
                                            <div class="custom-file">
                                                <input type="file" class="custom-file-input" name="img">
                                                <label class="custom-file-label">Photo</label>
                                            </div>
											</div>
										</div>

                                        <?php }}?>
										<div class="col-lg-12 col-md-12 col-sm-12">
											<button type="submit" class="btn btn-primary btn-lg">Sauvegarder</button>
											<button type="submit" class="btn btn-light btn-lg">Annuler</button>
										</div>
									</div>
								</form>
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
        <div class="modal" id="messageModal" tabindex="-1" role="dialog" aria-labelledby="messageModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="messageModalLabel"><?php echo isset( $_SESSION['univ']) ? $_SESSION['univ'] : "" ; ?></h5>
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
    <script  src="../js/styleSwitcher.js"></script>
      <script>
        function togglePassword() {
            const passwordField = document.getElementById('password');
            const togglePasswordIcon = document.querySelector('.toggle-password i');
            if (passwordField.type === 'password') {
                passwordField.type = 'text';
                togglePasswordIcon.classList.remove('fa-eye');
                togglePasswordIcon.classList.add('fa-eye-slash');
            } else {
                passwordField.type = 'password';
                togglePasswordIcon.classList.remove('fa-eye-slash');
                togglePasswordIcon.classList.add('fa-eye');
            }
        }

           
    </script>

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
	
</body>
</html>
<?php 
}else{
        //  header("location: ../deconnexion1");
        
    }
  



?>

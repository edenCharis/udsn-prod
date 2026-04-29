<?php 
include '../php/connexion.php';
include '../php/lib.php';

session_start();

if( $_SESSION['id'] == session_id() and  $_SESSION['role']=="daarhspe"){


?>


<!DOCTYPE html>
<html lang="en">

<head>
	
	<meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>  <?php echo $_SESSION['etablissement'];?> </title>
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
                    <div class="col-sm-8 p-md-0">
                        <div class="welcome-text">
                            <h4>EDITION D'UN CONTRAT D'ENSEIGNEMENT </h4>
                        </div>
                    </div>
                 
                </div>

                <form action="traitement.php" method="post">
                <div class="row">
					<div class="col-xl-12 col-xxl-12 col-sm-12">
                        <div class="card">
                            <div class="card-header">
								<h5 class="card-title">INFORMATIONS PERSONNELLES DE L'ENSEIGNANT</h5>
							</div>
							<div class="card-body">
                             
									<div class="row">
										<div class="col-lg-6 col-md-6 col-sm-12">
											<div class="form-group">
												<label class="form-label">Nom(s)</label>
												<input type="text" class="form-control"  name="nom">
											</div>
										</div>
										<div class="col-lg-6 col-md-6 col-sm-12">
											<div class="form-group">
												<label class="form-label">Prénom(s)</label>
												<input type="text" class="form-control" name="prenom">
											</div>
										</div>
										<div class="col-lg-6 col-md-6 col-sm-12">
											<div class="form-group">
												<label class="form-label">Date de naissance</label>
												<input type="date" class="form-control" name="date_naissance">
											</div>
										</div>
										<div class="col-lg-6 col-md-6 col-sm-12">
											<div class="form-group">
												<label class="form-label">Diplôme</label>
                                                <select id="diplome"  class="disabling-options" name="diplome" required>
                                                <option selected=""></option>
                                               <?php 
                                                        $sql="select * from type_diplome ";
                                                        $resultat=$connexion->query($sql);
                                                        while($etablissement =$resultat->fetch_assoc()){
                                                ?>
                                                 <option><?php echo str_replace("+","'",$etablissement['libelle']);?></option>
                                                 <?php }?>
                                            </select></div>
										</div>
                                        <div class="col-lg-6 col-md-6 col-sm-12">
											<div class="form-group">
												<label class="form-label">Spécialité du diplôme</label>
												<input type="text" class="form-control" name="specialite">
											</div>
										</div>
																				<div class="col-lg-6 col-md-6 col-sm-12">
											<div class="form-group">
												<label class="form-label">Statut</label>
												<select id="etat"  class="disabling-options" name="etat" required>
                                                <option selected=""></option>
                                                <option>Permanent</option>
                                                    <option>Vacataire</option>
                                                
                                               
                                            </select>
											</div>
										</div>
										<div class="col-lg-6 col-md-6 col-sm-12">
											<div class="form-group">
												<label class="form-label">Classement</label>
												<select id="grade"  class="disabling-options" name="grade" required>
                                                <option selected=""></option>
                                               <?php 
                                                        $sql="select * from type_grade ";
                                                        $resultat=$connexion->query($sql);
                                                        while($etablissement =$resultat->fetch_assoc()){
                                                ?>
                                                 <option><?php echo str_replace("+","'",$etablissement['libelle']);?></option>
                                                 <?php }?>
                                            </select>
											</div>
										</div>
									
										<div class="col-lg-6 col-md-6 col-sm-12">
											<div class="form-group">
												<label class="form-label">Contact téléphonique</label>
												<input type="text" class="form-control" name="telephone">
											</div>
										</div>
                                        <div class="col-lg-6 col-md-6 col-sm-12">
											<div class="form-group">
												<label class="form-label">Email</label>
												<input type="email" class="form-control"  name="email">
											</div>
										</div>
                                        <div class="col-lg-6 col-md-6 col-sm-12">
											<div class="form-group">
												<label class="form-label">Lieu de résidence</label>
												<input type="text" class="form-control"  name="ville">
											</div>
										</div>

										<div class="col-lg-6 col-md-6 col-sm-12">
											<div class="form-group">
												<label class="form-label">Sexe</label>
												<select class="form-control" name="sexe">
													<option selected=""></option>
													<option value="Homme" >Homme</option>
													<option value="Femme">Femme</option>
												</select>
											</div>
										</div>
									
									
									</div>
								
                            </div>
                        </div>
                    </div>
				</div>

                <div class="row">
					<div class="col-xl-12 col-xxl-12 col-sm-12">
                        <div class="card">
                            <div class="card-header">
								<h5 class="card-title">DISPOSITIONS PARTICULIERES</h5>
							</div>
							<div class="card-body">
                                <div class="row">
                                     <div class="col-lg-6 col-md-6 col-sm-12">
											<div class="form-group">
												<label class="form-label">Etablissement</label>
												<select id="grade"  class="disabling-options" name="etab" required>
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
                           <label>Selectionnez les ECUES (Appuyez shift pour selectionner plus qu'un ECUE)</label>
                   <select multiple class="disabling-options" class="form-control form-control-lg" id="sel2"  name="ecues[]">
                               <option value=""></option>
                               <?php 
                                     $sql="select * from ecue ";
                                     $resultat=$connexion->query($sql);
                                     while($etablissement =$resultat->fetch_assoc()){
                             ?>
                              <option  value="<?php echo $etablissement['code_ecue'];?>"><?php echo str_replace("+","'", $etablissement["libelle"])."(".$etablissement["code_ecue"].")" ?></option>
                              <?php }?>
                             
                           </select>
                           </div>

                           <div class="row">
                           <div class="col-lg-12 col-md-6 col-sm-12">
											<div class="form-group">
												<label class="form-label">Année académique</label>
												<select id="annee"  class="disabling-options" name="annee" required>
                                                <option selected=""></option>
                                               <?php 
                                                        $sql="select * from annee ";
                                                        $resultat=$connexion->query($sql);
                                                        while($etablissement =$resultat->fetch_assoc()){
                                                ?>
                                                 <option value="<?php echo $etablissement["libelle"];?>"><?php echo str_replace("+","'",$etablissement['libelle']);?></option>
                                                 <?php }?>
                                            </select>
									 </div>
                       </div>

                           </div>

                           

                    
         
           </div>
       </div>

                             
                           
                                </div>
                        
                    </div>
				</div>
                <div class="col-lg-12 col-md-12 col-sm-12">
											<button type="submit" class="btn btn-primary">Sauvegarder</button>
											<button type="submit" class="btn btn-light">Annuler</button>
		     	</div>

            </form>
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
    $(document).ready(function() {
        // Gérer le clic sur le bouton "Modifier"
        $('.btn-primary').click(function() {
            // Récupérer les données de la ligne cliquée
            var id = $(this).data('id');
            var x = $(this).data('moydev');
            var y= $(this).data('moyex');
 
           

            // Pré-remplir le formulaire modal avec les données actuelles
            $('#noteId').val(id);
            $('#nouveauMoyDev').val(x);
            $('#nouveauMoyEx').val(y);

        });
    });
</script>
	
</body>
</html>

<?php 


}else{
    header("location: ../login");
}?>
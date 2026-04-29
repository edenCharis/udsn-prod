<?php 
include '../php/connexion.php';
include '../php/lib.php';

session_start();

if( $_SESSION['id'] == session_id() and  $_SESSION['role']=="caisse"){

    if(isset($_POST['save'])){

      
    }

?>


<!DOCTYPE html>
<html lang="en">

<head>
	
	<meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title> Comptabilité de <?php echo $_SESSION['univ'];?> </title> <!-- Favicon icon -->
    <!-- Favicon icon -->
    <link rel="icon" type="image/png" sizes="16x16" href="../images/favicon.png">
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
   
        <?php 
         include 'header.php';
       ?>
       
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
                    <div class="col-sm-6 p-md-0">
                        <div class="welcome-text">
                            <h4>Genération d'un état de Comptabilité</h4>
                        </div>
                    </div>
                    <div class="col-sm-6 p-md-0 justify-content-sm-end mt-2 mt-sm-0 d-flex">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="index">Caisse</a></li>
                            <li class="breadcrumb-item active"><a href="javascript:void(0);">Etat</a></li>
                             </ol>
                    </div>
                </div>
				
                <div class="row">
                <div class="col-md-12 offset-md-2  page-titles mx-0">
        <div class="card-header">
          <h2 class="text-center">Formulaire</h2>
        </div>
        <div class="card-body">

        <form  method="get" action="etat_g">
                  
                       
                  <div class="input-group mb-3 col-sm-8">
                                          <div class="input-group-prepend">
                                              <label class="input-group-text">Enseignant </label>
                                          </div>
                                          <select  class="disabling-options"  name="enseignant" required>
                                              <option selected=""></option>
                                             <?php 
                                                      $sql="select * from enseignant  where  code in ( select enseignant from affectation)";
                                                      $resultat=$connexion->query($sql);
                                                      while($enseignant =$resultat->fetch_assoc()){

                                                          
                                              ?>
                                               <option value="<?php echo $enseignant['code'];?>"><?php echo strtoupper($enseignant["nom"]." ".$enseignant['prenom']);?></option>
                                               <?php }?>
                                          </select>
                  </div>
                
                  <div class="input-group mb-3 col-sm-8">
                                          <div class="input-group-prepend">
                                              <label class="input-group-text">Mois  </label>
                                          </div>
                                          <select   class="disabling-options" class="form-control form-control-lg" name="mois" recquired>
                                            <option value=""></option>
                                          <option value="1">Janvier</option>
    <option value="2">Février</option>
    <option value="3">Mars</option>
    <option value="4">Avril</option>
    <option value="5">Mai</option>
    <option value="6">Juin</option>
    <option value="7">Juillet</option>
    <option value="8">Août</option>
    <option value="9">Septembre</option>
    <option value="10">Octobre</option>
    <option value="11">Novembre</option>
    <option value="12">Décembre</option>
                                          </select>
                  </div>
               
             <div class="input-group mb-3 col-sm-8">
                                            <div class="input-group-prepend">
                                                <label class="input-group-text">Année académique  </label>
                                            </div>
                                            <select id="e" class="disabling-options form-control form-control-lg" name="annee" required>
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
                      
                     
               
                  <input  type="submit"  value="GENERER" class="btn btn-info">
                  
              </form>

       
      </div>
    </div>
				</div>
            </div>
        </div>
        <!--**********************************
            Content body end
        ***********************************-->


<div class="modal fade" id="typeAgentModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalLabel">Formulaire de paiement</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <!-- Formulaire pour enregistrer le type d'agent -->
                <form id="typeAgentForm" method="post" action="traitement">
                   
                    <div class="input-group mb-3">
                                            <div class="input-group-prepend">
                                                <label class="input-group-text">Etudiant  </label>
                                            </div>
                                            <select id="inscription" class="form-control form-control-lg" name="inscription" required>
                                                <option selected=""></option>
                                               <?php 
                                                        $sql="select * from inscription where etab='".$_SESSION['etablissement']."'";
                                                        $resultat=$connexion->query($sql);
                                                        while($c =$resultat->fetch_assoc()){
                                                ?>
                                                 <option value="<?php echo $c['id'];?>"><?php echo str_replace("+","'",obtenirNomPrenom($c['candidat'],$c['annee'],$connexion));?></option>
                                                 <?php }?>
                                            </select>
                       </div>
                       <div class="input-group mb-3">
                                            <div class="input-group-prepend">
                                                <label class="input-group-text">Type de frais  </label>
                                            </div>
                                            <select id="ecue" class="form-control form-control-lg" name="type" required>
                                                <option selected=""></option>
                                               <?php 
                                                        $sql="select * from type_frais  where libelle <> 'frais inscription' and libelle <> 'frais de concours'";
                                                        $resultat=$connexion->query($sql);
                                                        while($etablissement =$resultat->fetch_assoc()){
                                                ?>
                                                 <option><?php echo str_replace("+","'",$etablissement['libelle']);?></option>
                                                 <?php }?>
                                            </select>
                       </div>
                       <div class="input-group mb-3">
                                            <div class="input-group-prepend">
                                                <label class="input-group-text">Année académique  </label>
                                            </div>
                                            <select id="ecue" class="form-control form-control-lg" name="annee" required>
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
                       <div class="form-group">
                        <label for="montant">Montant </label>
                        <input type="number"  min="0" max="1000000" class="form-control" id="montant" name="montant" required>
                       </div>
                       <div class="form-group">
                        <label for="datep">Date </label>
                        <input type="date"  class="form-control" id="datep" name="datep" required>
                    </div>
                    <button type="submit" class="btn btn-primary">Enregistrer</button>
                    <button type="button" data-dismiss="modal" class="btn btn-danger">Annuler</button>
                </form>
            </div>
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
		
    <!-- Chart Morris plugin files -->
   
		

		<!-- Demo scripts -->
    <script src="../js/dashboard/dashboard-1.js"></script>
	
	<!-- Svganimation scripts -->
    <script src="../vendor/svganimation/vivus.min.js"></script>
    <script src="../vendor/svganimation/svg.animation.js"></script>
    <script src="../vendor/datatables/js/jquery.dataTables.min.js"></script>
    <script src="../js/plugins-init/datatables.init.js"></script>
    <script src="../vendor/select2/js/select2.full.min.js"></script>
    <script src="../js/plugins-init/select2-init.js"></script>

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
    header("location: ../login");
}?>
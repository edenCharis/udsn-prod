<?php 
include '../php/connexion.php';
include '../php/lib.php';

session_start();

if( $_SESSION['id'] == session_id() and  $_SESSION['role']=="doyen"){
    

             

              if(isset($_GET['sup']))
              {

                  $sql ="delete from inscription where id=".$_GET['sup'];
                  if($connexion->query($sql)){
                    header("location: ../doyen/etudiant");
                    exit;
              }}


              if(isset($_POST['reinscrire']))
              {
                        $etudiant = $_POST['etudiant'];
                        $mat = getCandidatCodeByInscription( $_POST['etudiant'],$connexion);
                        $classe = $_POST['classe'];
                        $annee = $_POST['annee'];
                        $specialiteClasse=getSpecialiteClasse($connexion,$classe);
                        $specialite = getSpecialiteDuCandidat(getCandidatCodeByInscription($etudiant,$connexion),getAnneeInscription($connexion,$etudiant),$connexion);

                        if(VerifierPayementReinscription($mat,$annee,$connexion))
                        {
                            if($specialite != $specialiteClasse){

                                header("location: reinscription?erreur= Erreur de specialité");
                                exit;
                            }

                            $sql ="INSERT INTO INSCRIPTION(candidat,classe,annee,etab) values('$mat','$classe','$annee','".$_SESSION["etablissement"]."')";

                            if($connexion->query($sql)){

                                header("location: reinscription?sucess=Opération reussie avec succès $specialite;$specialiteClasse");
                            }else{

                                header("location: reinscription?erreur=$connexion->error");
                            }
                        }else{

                            header("location: reinscription?erreur= L'etudiant choisie n'a pas payé les frais de reinscription $mat");
                        }
              }



?>


<!DOCTYPE html>
<html lang="en">

<head>
	
	<meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title><?php echo $_SESSION['univ'];?> - Scolarité de  <?php echo $_SESSION['etablissement'];?> </title>
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
        <?php include "header.php" ;?>
        <!--**********************************
            Header start
        ***********************************-->
     
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
                    <div class="col-sm-6 p-md-0">
                        <div class="welcome-text">
                            <h3>Reinscription</h3>
                        </div>
                    </div>
                    <div class="col-sm-6 p-md-0 justify-content-sm-end mt-2 mt-sm-0 d-flex">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="../scolarite/">Scolarité</a></li>
                            <li class="breadcrumb-item"><a href="../scolarite/candidature">inscription</a></li>
                            <li class="breadcrumb-item"><a href="../scolarite/reinscription">reinscription</a></li>
                           
                           
                        </ol>
                    </div>
                </div>   
    <div class="col-md-12 offset-md-2  page-titles mx-0">
        <div class="card-header">
          <h2 class="text-center">Formulaire de reinscription d'un étudiant</h2>
        </div>
        <div class="card-body">

        <form  method="post" action="reinscription">
                  
                       
                  <div class="input-group mb-3">
                                          <div class="input-group-prepend">
                                              <label class="input-group-text">Etudiant </label>
                                          </div>
                                          <select  class="disabling-options"  name="etudiant" required>
                                              <option selected=""></option>
                                             <?php 
                                                      $sql="select * from inscription where etab='".$_SESSION['etablissement']."'";
                                                      $resultat=$connexion->query($sql);
                                                      while($etudiant =$resultat->fetch_assoc()){

                                                          $codeCandidat= getCandidatCodeByInscription($etudiant['id'],$connexion);
                                              ?>
                                               <option value="<?php echo $etudiant['id'];?>"><?php echo obtenirNomPrenom($codeCandidat,$etudiant['annee'],$connexion);?></option>
                                               <?php }?>
                                          </select>
                  </div>
                
                  <div class="input-group mb-3">
                                          <div class="input-group-prepend">
                                              <label class="input-group-text">Niveau </label>
                                          </div>
                                          <select   class="disabling-options" class="form-control form-control-lg" name="niveau" recquired>
                                              <option selected=""></option>
                                             <?php 
                                                      $sql="select * from niveau";
                                                      $resultat=$connexion->query($sql);
                                                      while($etablissement =$resultat->fetch_assoc()){
                                              ?>
                                               <option value="<?php echo  $etablissement['libelle']; ?>"><?php echo  $etablissement['libelle']; ?></option>
                                               <?php }?>
                                          </select>
                  </div>
          <div class="input-group mb-3">
                                          <div class="input-group-prepend">
                                              <label class="input-group-text">Classe </label>
                                          </div>
                                          <select   class="disabling-options" class="form-control form-control-lg" name="classe" recquired>
                                              <option selected=""></option>
                                             <?php 
                                                      $sql="select * from classe where etab='".$_SESSION['etablissement']."'";
                                                      $resultat=$connexion->query($sql);
                                                      while($etablissement =$resultat->fetch_assoc()){
                                              ?>
                                               <option value="<?php echo  $etablissement['libelle']; ?>"><?php echo str_replace("+","'",$etablissement['libelle'])."-".str_replace("+","'",$etablissement["niveau"])."-".str_replace("+","'",$etablissement["specialite"]);?></option>
                                               <?php }?>
                                          </select>
                  </div>
             
                 
                     <div class="input-group mb-3">
                                          <div class="input-group-prepend">
                                              <label class="input-group-text">Année académique  </label>
                                          </div>
                                          <select  class="disabling-options" class="form-control form-control-lg" name="annee" >
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
                  <input  type="submit" name="reinscrire" value="REINSCRIRE" class="btn btn-primary">
                  <a class="btn btn-danger">ANNULER</a>
            </form>

       
      </div>
    </div>
  </div>
         </div>
            
</div>
        <!--**********************************
            Content body end
        ***********************************-->


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
            var lib = $(this).data('lib');
            var niv= $(this).data('niv');
            var  spec = $(this).data('spec');
            

            // Pré-remplir le formulaire modal avec les données actuelles
            $('#classeId').val(id);
            $('#nouveauClasse').val(lib);
            $('#nouveauniv').val(niv);
            $('#nouveauspec').val(spec);
          
        });
    });
</script>
	
</body>
</html>

<?php 


}else{
    header("location: ../login");
}?>
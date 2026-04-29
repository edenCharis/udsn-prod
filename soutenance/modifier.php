<?php 
include '../php/connexion.php';
include '../php/lib.php';

session_start();

if( $_SESSION['id'] == session_id() and  $_SESSION['role']=="soutenance"){



    if(isset($_POST['modifier'])){

             $sql=" UPDATE soutenance set 
               date_soutenance ='".$_POST['date_soutenance']."',
               theme ='".clean_input($_POST['titre_these'])."',
               departement='".clean_input($_POST['departement'])."'
               where code='".$_POST['soutenance']."'";
               

               $sql1="UPDATE jury set 
               president ='".$_POST['president_jury']."',
               rapp_int ='".$_POST['rapporteur_interne']."',
               rapp_extern ='".$_POST['rapporteur_externe']."',
               examinateur='".$_POST['examinateur']."',
               autre='".$_POST['autre_membre']."',
               raison_modif='".clean_input($_POST['raison_modification'])."',
               date_modif='".date("Y-m-d")."'
               where soutenance='".$_POST['soutenance']."'";



               if($connexion->query($sql) and $connexion->query($sql1)){
                header("location: jury?soutenance=".$_POST['soutenance']);
               } else{

                   echo "<script>alert('".$connexion->error."')</script>";
               }


    }

    if(isset($_POST["retour"])){

        header("location: jury?soutenance=".$_POST['soutenance']);
    }

   if(isset($_GET["soutenance"])){

    $soutenance = $_GET["soutenance"];

     if($soutenance !==""){

?>


<!DOCTYPE html>
<html lang="en">

<head>
	
	<meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title><?php echo $_SESSION['univ'];?> - Scolarité de  <?php echo $_SESSION['etablissement'];?> </title>
    <!-- Favicon icon -->
    <link rel="icon" type="image/png" sizes="16x16" href="../images/favicon.png">
	<link rel="stylesheet" href="../vendor/bootstrap-select/dist/css/bootstrap-select.min.css">
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
                            <h4> soutenance :  <?php echo $soutenance;?></h4>
                        </div>
                    </div>
                    <div class="col-sm-6 p-md-0 justify-content-sm-end mt-2 mt-sm-0 d-flex">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="index">Soutenance</a></li>
                            <li class="breadcrumb-item active"><a href="javascript:void(0);">Jury</a></li>
                             </ol>
                    </div>
                </div>
                <div class="row">
         <div class="col-md-8 offset-md-2">
          <div class="card mt-5">
        <div class="card-header">
          <h2 class="text-center">Formulaire de Modification du Jury de Soutenance</h2>
        </div>
     <div class="card-body">

        <?php 
            $sql ="select * from soutenance where code='$soutenance'";
            $resultat = $connexion->query($sql);
            while ($s = $resultat->fetch_assoc()){
        
        ?>

          <form action="modifier" method="post">

          <div class="form-group">
              <label for="t"></label>
              <input type="hidden" id="t" value="<?php echo $s['code'];?>" name="soutenance" class="form-control">
            </div>

            <div class="form-group">
              <label for="date_soutenance">Date de la Soutenance</label>
              <input type="date" id="date_soutenance" value="<?php echo $s['date_soutenance'];?>" name="date_soutenance" class="form-control">
            </div>

            <div class="form-group">
              <label for="etudiant">Étudiant(e)</label>  
              <input type="text" id="etudiant" name="etudiant" value="<?php echo getNomByInscription($s['impetrant'],$connexion);?>" class="form-control" disabled>
            </div>

            <div class="form-group">
              <label for="titre_these">Titre du Sujet/Mémoire</label>
              <input type="text" id="titre_these" name="titre_these" value="<?php echo $s['theme'];?>" class="form-control">
            </div>


            <div class="form-group">
              <label for="departement">Département</label>
              <input type="text" id="departement" name="departement"  value="<?php echo $s['departement'];?>"class="form-control">
            </div>

            <?php }
            
               $sql="select * from jury where soutenance='$soutenance'";

               $resultat = $connexion->query($sql);
               while ($j= $resultat->fetch_assoc()){
            ?>

            <h3>Modification proposée du Jury</h3>
   
            <div class="form-group">
              <label for="president_jury">Président du Jury</label>
              <input type="text" id="president_jury" name="president_jury" value="<?php echo $j['president'];?>" class="form-control">
            </div>

            <div class="form-group">
              <label for="rapporteur_interne">Rapporteur Interne </label>
              <input type="text" id="rapporteur_interne" name="rapporteur_interne"  value="<?php echo $j['rapp_int'];?>"  class="form-control">
            </div>

            <div class="form-group">
              <label for="rapporteur_externe">Rapporteur Externe</label>
              <input type="text" id="rapporteur_externe" name="rapporteur_externe"  	 value="<?php echo $j['rapp_extern'];?>"  class="form-control">
            </div>

            <div class="form-group">
              <label for="examinateur">Examinateur</label>
              <input type="text" id="examinateur" name="examinateur"   value="<?php echo $j['examinateur'];?>" class="form-control">
            </div>

            <div class="form-group">
              <label for="autre_membre">Autre Membre</label>
              <input type="text" id="autre_membre" name="autre_membre"   value="<?php echo $j['autre'];?>" class="form-control">
            </div>

            <div class="form-group">
              <label for="raison_modification">Raison de la Modification</label>
              <textarea id="raison_modification" name="raison_modification" rows="4" class="form-control"></textarea>
            </div>

            <div class="form-group">
              <label for="date_soumission">Date de Soumission de la Modification</label>
              <input type="date" id="date_soumission" name="date_soumission" value="<?php echo date("Y-m-d");?>" class="form-control" disabled>
            </div>


            <button type="submit" name="modifier" class="btn btn-primary">Soumettre</button>
            <button type="submit" name="retour" value="Retour" class="btn btn-danger">Retour</button>

            <?php }?>
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
      








                                    <div class="modal fade bd-example-modal-lg" tabindex="-1" role="dialog" aria-hidden="true">
                                        <div class="modal-dialog modal-lg">
                                            <div class="modal-content">
                                                <div class="modal-header">
                                                    <h5 class="modal-title">Modal title</h5>
                                                    <button type="button" class="close" data-dismiss="modal"><span>&times;</span>
                                                    </button>
                                                </div>
                                                <div class="modal-body">Modal body text goes here.</div>
                                                <div class="modal-footer">
                                                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                                                    <button type="button" class="btn btn-primary">Save changes</button>
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
     }
   }
}else{
    header("location: ../login");
}?>
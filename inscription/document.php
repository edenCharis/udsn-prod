<?php 
include '../php/connexion.php';
include '../php/lib.php';

session_start();

if( $_SESSION['id'] == session_id() and  $_SESSION['role']=="inscription" ){


 if(isset($_GET["code"]) and isset($_GET["annee"]) and isset($_GET["specialite"]))
 {

    $candidat = $_GET["code"];
    $annee= $_GET["annee"];
    $specialite = $_GET["specialite"];
?>


<!DOCTYPE html>
<html lang="en">

<head>
	
	<meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title><?php echo $_SESSION['univ'];?> - Scolarité de  <?php echo $_SESSION['etablissement'];?> </title>
    <!-- Favicon icon -->
    <link rel="icon" type="image/png" sizes="16x16"  href="../administrateur/<?php echo  $_SESSION['logo_univ']?>">
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
		
	


        <div class="content-body">
            <!-- row -->
            <div class="container-fluid">
				    
                <div class="row page-titles mx-0">
                    <div class="col-sm-6 p-md-0">
                        <div class="welcome-text">
                            <h4>GESTION DES FICHIERS</h4>
                        </div>
                    </div>
                    <div class="col-sm-6 p-md-0 justify-content-sm-end mt-2 mt-sm-0 d-flex">
                        <ol class="breadcrumb">
                          
                            <li class="breadcrumb-item active"><a href="index"><?php echo $_SESSION["etablissement"];?></a></li>
                            <li class="breadcrumb-item active"><a href="javascript:void(0);"> <?php echo obtenirNomPrenom($candidat,$annee,$connexion)?></a></li>
                            <li class="breadcrumb-item active"><a href="javascript:void(0);">  Documents joints</a></li>
                        </ol>
                    </div>

                    
                </div>


                <div class="card">
                    <div class="card-body">
                    <div class="row">
            
            <div class="col-md-12">

            <p>Importez les fichies JPEG,GIF et PNG</p>
         <!-- Formulaire d'envoi d'images avec champ de sélection -->
         <form id="uploadForm" action="traitement1" method="post" enctype="multipart/form-data">

         <input type="hidden" value="<?php echo $candidat; ?>"  name="code" >

         <input type="hidden" value="<?php echo $annee;?>"    name="annee" >


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
                    </div>


                   
                    <?php 

if(idCandidatExiste2($candidat,$connexion)){

                


                 if($candidat !== null and $annee !== null){
              
                 if(idCandidatValide($candidat,$connexion,$annee,$_SESSION["etablissement"])){

                  $sql ="select * from validation where candidat='".$candidat."' and annee='".$annee."'";


                  $resultat=$connexion->query($sql);
?>
<div class="row">


<?php while($doc=$resultat->fetch_assoc()){
              ?>
     <div class="col-sm-4 col-xxl-3 col-lg-3 col-md-4">
          <div class="card">
              <img class="img-fluid" src="<?php echo $doc['path']?>" alt="">
              <div class="card-body">
                  <h4><?php echo   str_replace("+","'",$doc['document'])?></h4>
                <!--  Statut de validation : <?php echo $doc['statut'];?></br>--> 
                 <!-- <a href="document?id=<?php echo $doc['id'];?>" class="btn btn-danger">Supprimer</a>-->
              </div>
          </div>
      </div>
<?php }?>
</div>

      <?php }}}?>

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

    <script>
    // Récupérer l'élément select
    var selectAnnee = document.getElementById('anneeSelect');

    // Ajouter les années de 2000 à 2023 au sélecteur
    for (var annee = 2000; annee <= 2023; annee++) {
        var option = document.createElement('option');
        option.value = annee;
        option.text = annee;
        selectAnnee.appendChild(option);
    }
</script>
		
    <!-- Chart Morris plugin files -->
    <script src="../vendor/raphael/raphael.min.js"></script>
    <script src="../vendor/morris/morris.min.js"></script>
		
	
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
            var classe = $(this).data('classe');
            var ecue= $(this).data('ecue');
            var  sem = $(this).data('sem');
            var annee = $(this).data('annee');
           

            // Pré-remplir le formulaire modal avec les données actuelles
            $('#affId').val(id);
            $('#nouveauClasse').val(classe);
            $('#nouveauEcue').val(ecue);
            $('#nouveauSem').val(sem);
            $('#nouveauAnnee').val(annee);



        });
    });
</script>
	
</body>
</html>

<?php 
 }

}else{
    header("location: ../login");
}?>
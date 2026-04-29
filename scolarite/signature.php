<?php 
include '../php/connexion.php';
include '../php/lib.php';

session_start();

if( $_SESSION['id'] == session_id() and  $_SESSION['role']=="scolarité"){

        

  

if(isset($_POST["save"])){

    
    if ($_FILES['img']['error'] === UPLOAD_ERR_OK) {
        // Récupérez des informations sur le fichier
        $nomFichier = $_FILES['img']['name'];
        $typeFichier = $_FILES['img']['type'];
        $cheminTemporaire = $_FILES['img']['tmp_name'];

        // Vous pouvez maintenant traiter le fichier, par exemple, le déplacer vers un répertoire de stockage
         $nouveauChemin = 'signatures/' . $nomFichier;


         $finfo = finfo_open(FILEINFO_MIME_TYPE);

         // Récupère le type MIME du fichier
         $fileType = finfo_file($finfo, $_FILES['img']['tmp_name']);
         
         // Ferme l'objet finfo
         finfo_close($finfo);

         $allowedImageTypes = ['image/jpeg', 'image/png', 'image/gif'];
      

        if (!in_array($fileType, $allowedImageTypes)) {
            header("location: signature?erreur=le fichier choisie n'est pas au bon format, veuillez choisir une image.");
          exit;
         } else {

    if (move_uploaded_file($cheminTemporaire, $nouveauChemin)) {

        $type=  $_POST["type"];
    $etab=$_SESSION["etablissement"];
    $signataire =$_POST["signataire"];



    $sql1="select id from sign where type='$type' and etab='$etab'";
    $r =$connexion->query($sql1);
    if($r->num_rows > 0){

        while($t=$r->fetch_assoc()){
            $id =$t["id"];
        }

       if( $connexion->query("update sign set signature='$nouveauChemin',signataire=$signataire where id=$id")){

        header("location: signature?sucess=Importation effectuée avec succès");
        exit;
    } else {
        header("location: signature?erreur=$connexion->error");
        exit;
    }
        
    }else{
   
  
    $sql = "INSERT INTO sign (type,signataire, signature, etab) VALUES (
                            '$type','$signataire', '$nouveauChemin', '$etab')";
    if ($connexion->query($sql) === TRUE) {
        $userIP = $_SERVER['REMOTE_ADDR'];
        

        logUserAction($connexion,$_SESSION['id_user'],"enregistrement d'une signature",date("Y-m-d H:i:s"),$userIP,"valeur enregistrée : $type+$signataire");

        header("location: signature?sucess='Importation effectuée avec succès.'&id=$signataire");
    } else {
        header("location: signature?erreur=$connexion->error");
    }
    }
    // Fermer la connexion à la base de données
}else{
    header("location: signature?erreur=Erreur avec l'importation de l'image.");


}
} }else{
    header("location: signature?erreur=Une erreur est survenue lors de l'importation de l'image.");

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
                    <div class="col-sm-6 p-md-0">
                        <div class="welcome-text">
                            <h3>Importation d'une signature</h3>
                        </div>
                    </div>
                    <div class="col-sm-6 p-md-0 justify-content-sm-end mt-2 mt-sm-0 d-flex">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="../scolarite/">Scolarité</a></li>
                            <li class="breadcrumb-item"><a href="../scolarite/sign">signature</a></li>
                           
                           
                        </ol>
                    </div>
                </div>
				
				<div class="row">
					<div class="col-lg-12">
						<div class="card">
							<div class="card-header">
								
								</div>
							<div class="card-body">
                            <p>Importez les fichiers JPEG,GIF et PNG</p>
             
                                 <form id="uploadForm" action="signature" method="post" enctype="multipart/form-data">
                                       <div class="input-group mb-3">
                                            <div class="custom-file">
                                                <input type="file" class="custom-file-input" name="img" required>
                                                <label class="custom-file-label">Fichier</label>
                                            </div>
                                           
                                        </div>
                                        <div class="input-group mb-3">
                                            <div class="input-group-prepend">
                                                <label class="input-group-text">Signataire  </label>
                                            </div>
                                            <select   class="disabling-options" name="signataire"  recquired>
                                              <option value=""></option>
                                              <?php 
                                                     $sql="select * from enseignant where etab='".$_SESSION["etablissement"]."'";
                                                     $resultat=$connexion->query($sql);
                                                     while($etablissement =$resultat->fetch_assoc()){
                                             ?>
                                              <option value="<?php echo $etablissement["id"]; ?>"><?php echo  (($etablissement["grade"] !== "Aucun") ? $etablissement["grade"] : "")." ".str_replace("+","'",$etablissement['nom']." ".$etablissement["prenom"]);?></option>
                                              <?php }?>
                                         </select>
                                         <div class="input-group-prepend">
                                                <label class="input-group-text">Document  </label>
                                            </div>
                                         <select   class="disabling-options"  name="type"  recquired>
                                            <option value=""></option>
                                              
                                              <option>Note de Service</option>
                                              <option>Diplome</option>
                                              
                                         </select>
                                      </div>

                    <div class="form-group">
                        <button type="submit" name="save" value="Enregistrer" class="btn btn-primary"><i class="ti-upload"></i></button>
                    </div>
                </form>
							</div>
						</div>
                        <?php 
if(isset($_GET["id"])){
    $id=$_GET["id"];
    $signature_blob = getSignatureData($connexion,"Diplome",$_SESSION["etablissement"]);


    echo '<img src='.$signature_blob.' alt="Signature">';
    echo 'Signature Enregistrée. '.$id;
    
}



?>
                     
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
		
	
	<!-- Chart piety plugin files -->
    <script src="../vendor/peity/jquery.peity.min.js"></script>
	
		<!-- Demo scripts -->
    <script src="../js/dashboard/dashboard-2.js"></script>
    		
    <script src="../vendor/select2/js/select2.full.min.js"></script>
    <script src="../js/plugins-init/select2-init.js"></script>
	
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


}else{
    header("location: ../login");
}?>
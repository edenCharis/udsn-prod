<?php 
include '../php/connexion.php';
include '../php/lib.php';


session_start();

if( $_SESSION['id'] == session_id() and isset($_SESSION['univ'])){

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

    <link rel="stylesheet" href="../assets/plugins/feather/feather.css">

<link rel="stylesheet" href="../assets/plugins/icons/flags/flags.css">

<link rel="stylesheet" href="../assets/css/feather.css">
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
<?php include "header.php" ;?> <!--**********************************
            Nav header end
        ***********************************-->

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
                            <h4>Paramétrage de l'université</h4>
                        </div>
                    </div>
                    <div class="col-sm-6 p-md-0 justify-content-sm-end mt-2 mt-sm-0 d-flex">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="index">Tableau de Bord</a></li>
                        </ol>
                    </div>
                </div>
                <div class="page-wrapper">
<div class="row">
<div class="col-md-6">
<div class="card">
<div class="card-header">
<h5 class="card-title">Details de l'université</h5>
</div>
<?php 
         if($_SESSION['univ'] != null){

            $sql ="select * from univ where code='".$_SESSION['univ']."'";

            $resultat = $connexion->query($sql);

            while ($data = $resultat->fetch_assoc()){
?>
<div class="card-body pt-0">
<form  method="post" action="traitement" enctype="multipart/form-data">
                
<div class="settings-form">
<div class="form-group">

<input type="hidden" class="form-control" value="<?php echo $data['id']?>"  name="id_univ" recquired>
</div> 
    
<div class="form-group">
<label>Université <span class="star-red"></span></label>
<input type="text" class="form-control" value="<?php echo str_replace("+","'",$data['libelle_univ'])?>"  name="universite" recquired>
</div>
<div class="form-group">
<p class="settings-label">Logo <span class="star-red"></span></p>
<div class="custom-file">
<input type="file" accept="image/*" name="logo" id="file" onchange="loadFile(event)" class="hide-input">
<label for="file" class="upload">
<i class="feather-upload"></i>
</label>
<div class="upload-images upload-size">
<img src="../administrateur/<?php echo $data['logo']?>"  width="65" height="65" alt="Image">
<a href="javascript:void(0);" class="btn-icon logo-hide-btn">
<i class="feather-x-circle"></i>
</a>
</div>
</div>
</div>

<div class="form-group">
<label>Code <span class="star-red"></span></label>
<input type="text" class="form-control"  value="<?php echo $data['code']?>" name="code">
</div>
<div class="form-group">
<label>Email <span class="star-red"></span></label>
<input type="email" class="form-control" value="<?php echo $data['email_contact']?>"  name="email">
</div>
<div class="form-group">
<label>Téléphone <span class="star-red"></span></label>
<input type="text" class="form-control" value="<?php echo $data['tel_contact']?>" name="telephone">
</div>
</div>

</div>
</div>
</div>
<div class="col-md-6">
<div class="card">
<div class="card-header">
<h5 class="card-title">Details de Localisation</h5>
</div>
<div class="card-body pt-0">
<form>
<div class="settings-form">

<div class="row">
<div class="col-md-6">
<div class="form-group">
<label>Ville <span class="star-red"></span></label>
<input type="text" class="form-control" value="<?php echo $data['ville']?>"name="ville">
</div>
</div>

<div class="col-md-6">
<div class="form-group">
<label>Zip/Code Postal / FAX <span class="star-red">*</span></label>
<input type="text" class="form-control" name="fax" value="<?php echo $data['fax']?>">
</div>
</div>
<div class="col-md-6">
<div class="form-group">
<label>Latitude <span class="star-red"></span></label>
<input type="text" class="form-control" name="lat" value="<?php echo $data['lat_univ']?>">
</div>
</div>
<div class="col-md-6">
<div class="form-group">
<label>Longitude<span class="star-red"></span></label>
<input type="text" class="form-control" name="long" value="<?php echo $data['long_univ']?>">
</div>
</div>
</div>
<div class="form-group mb-0">
<div class="settings-btns">
<button type="submit" class="btn btn-success">Sauvegarder</button>
<button type="submit" class="btn btn-danger">Annuler</button>
</div>
</div>
</div>
</form>
<?php } } ?>
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

<div class="modal" id="modifierModal" tabindex="-1" role="dialog" aria-labelledby="modifierModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modifierModalLabel">Modifier l'etablissement</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <!-- Formulaire de modification -->
                <form  method="post" action="traitement">
                    <input type="hidden" id="etabId" name="etabId">
                    <div class="form-group">
                        <label for="nouveauNom">Nouveau Nom :</label>
                        <input type="text" class="form-control" id="nouveauNom" name="nouveauNom" required>
                    </div>
                    <div class="input-group mb-3">
                                            <div class="input-group-prepend">
                                                <label class="input-group-text">Type d'etablissement</label>
                                            </div>
                                            <select id="nouveauType" class="" name="nouveauType" recquired>
                                                <option selected=""></option>
                                                <option value="faculté">Faculté</option>
                                                <option value="institut">Institut</option>
                                                <option value="école">Ecole</option>
                                            </select>
                                        </div>
                                        <div class="form-group">
                        <label for="nouveauCode">Code de l'etablissement  :</label>
                        <input type="text" class="form-control" id="nouveauCode" name="nouveauCode" placeholder="Entrez le code de l'etablissement" required>
                    </div>
                    <div class="form-group">
                        <label for="nouveauEmail">Email  :</label>
                        <input type="email" class="form-control" id="nouveauEmail" name="nouveauEmail" placeholder="Entrez l'email" required>
                    </div>
                    <button type="submit" class="btn btn-success">Sauvegarder</button>
                </form>
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
    <script src="../js/dlabnav-init.js"></script>	
	
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

<script>
    $(document).ready(function() {
        // Gérer le clic sur le bouton "Modifier"
        $('.btn-primary').click(function() {
            // Récupérer les données de la ligne cliquée
            
            var id = $(this).data('id');
            var code =$(this).data('code')
                var lib = $(this).data('lib')
            var em =$(this).data('em')
            var type =$(this).data('type')

            // Pré-remplir le formulaire modal avec les données actuelles
            $('#etabId').val(id);
            $('#nouveauType').val(type);
            $('#nouveauCode').val(code);
            $('#nouveauNom').val(lib);
            $('#nouveauEmail').val(em);
        });
    });
</script>

<script>
$(document).ready(function() {
 
  var table = $('#example3').DataTable();

  
  $('#example3').on('click', '.edit-btn', function() {

    
    // Récupérer la ligne correspondante
    var rowData = table.row($(this).closest('tr')).data();

    // Ouvrir le modal avec les données de la ligne
    $('#modifierModal').modal('show');
    
    // Afficher les données dans le modal
    $('#etabId').val(rowData[0]);
    $('#nouveauCode').val(rowData[1]);
    $('#nouveauNom').val(rowData[2]);
      $('#nouveauType').val(rowData[3]);
    $('#nouveauEmail').val(rowData[4]);


  });
});
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
    header("location: ../login");
}
?>
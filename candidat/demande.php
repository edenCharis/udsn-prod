
<?php 
include '../php/connexion.php';
include '../php/lib.php';

session_start();

if( $_SESSION['id'] == session_id()){

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

    <link rel="stylesheet" href="../vendor/select2/css/select2.min.css">

    <script src="https://unpkg.com/dropzone@5/dist/min/dropzone.min.js"></script>
<link rel="stylesheet" href="https://unpkg.com/dropzone@5/dist/min/dropzone.min.css" type="text/css" />
   


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
                                    <a href="compte"  class="dropdown-item ai-icon">
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
                            <h4>Formulaire de demande</h4>
                        </div>
                    </div>
                    <div class="col-sm-6 p-md-0 justify-content-sm-end mt-2 mt-sm-0 d-flex">
                        <ol class="breadcrumb">
                          
                            <li class="breadcrumb-item active"><a href="index">UDSN</a></li>
                            <li class="breadcrumb-item active"><a href="javascript:void(0);">Demande</a></li>
                        </ol>
                    </div>
                </div>
				
				<div class="row">
					<div class="col-xl-12 col-xxl-12 col-sm-12">
                        <div class="card">
                            <div class="card-header">
								<h5 class="card-title">Remplissez tous les champs</h5>
							</div>
							<div class="card-body">
                                <form action="traitement3" method="post" enctype="multipart/form-data">
									<div class="row">
										<div class="col-lg-6 col-md-6 col-sm-12">
											<div class="form-group">
												<label class="form-label">Nom(s)</label>
												<input type="text" class="form-control"  name="nom" required>
											</div>
										</div>
										<div class="col-lg-6 col-md-6 col-sm-12">
											<div class="form-group">
												<label class="form-label">Prénom(s)</label>
												<input type="text" class="form-control" name="prenom" required>
											</div>
										</div>
										<div class="col-lg-6 col-md-6 col-sm-12">
											<div class="form-group">
												<label class="form-label">Email </label>
												<input type="email" class="form-control" name="email" required>
											</div>
										</div>
                                        <div class="col-lg-6 col-md-6 col-sm-12">
											<div class="form-group">
												<label class="form-label">Télephone </label>
												<input type="text" class="form-control" name="tel" required>
											</div>
										</div>
										<div class="col-lg-6 col-md-6 col-sm-12">
											<div class="form-group">
												<label class="form-label">MATRICULE</label>
												<input type="text"  class="form-control" id="matricule"  name="matricule" required >
											</div>
										</div>
                                        <div class="col-lg-6 col-md-6 col-sm-12">
                                              <div class="form-group">
												<label class="form-label">Classe</label>
                                                <select   class="disabling-options" name="classe" required>
                                                <option></option>
                                               <?php 
                                                        $sql="select * from classe ";
                                                        $resultat=$connexion->query($sql);
                                                        while($etablissement =$resultat->fetch_assoc()){
                                                ?>
                                                 <option value="<?php echo $etablissement['libelle'];?>"><?php echo str_replace("+","'",$etablissement['libelle']);?></option>
                                                 <?php }?>
                                               </select>
											</div>
										</div>
										
										<div class="col-lg-6 col-md-6 col-sm-12">
											<div class="form-group">
												<label class="form-label">Sexe</label>
												<select  class="disabling-options" name="sexe" required>
                                                    <option></option>
													<option >Masculin</option>
													<option >Feminin</option>
												</select>
											</div>
										</div>
										<div class="col-lg-6 col-md-6 col-sm-12">
											<div class="form-group">
												<label class="form-label">Type de demande</label>
												<select class="disabling-options"  name="diplome" required>
                                                <option value=""></option>
													<option  > Attestation</option>
                                                
												
												</select>
											</div>
										</div>
                                       
                                        <div class="col-lg-6 col-md-6 col-sm-12">
                                              <div class="form-group">
												<label class="form-label">Etablissement</label>
                                                <select   class="disabling-options" name="etablissement" required>
                                                <option></option>
                                               <?php 
                                                        $sql="select * from etablissement where libelle <> 'udsn' ";
                                                        $resultat=$connexion->query($sql);
                                                        while($etablissement =$resultat->fetch_assoc()){
                                                ?>
                                                 <option value="<?php echo $etablissement['code'];?>"><?php echo str_replace("+","'",$etablissement['libelle']);?></option>
                                                 <?php }?>
                                               </select>
											</div>
										</div>
                                        <div class="col-lg-6 col-md-6 col-sm-12">
                                              <div class="form-group">
												<label class="form-label">Spécialité</label>
                                                <select   class="disabling-options" name="specialite" required>
                                                <option></option>
                                               <?php 
                                                        $sql="select * from specialite where libelle <> 'Tronc Commun' ";
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
												<label class="form-label">Parcours</label>
                                                <select  class="disabling-options" name="parcours" required>
                                                <option></option>
                                               <?php 
                                                        $sql="select * from parcours  ";
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
												<label class="form-label">Année universitaire</label>
												<select  class="disabling-options" name="annee" required>
                                                    <option value=""></option>
                                                    
                                               <?php 
                                                        $sql="select * from annee  ";
                                                        $resultat=$connexion->query($sql);
                                                        while($etablissement =$resultat->fetch_assoc()){
                                                ?>
                                                 <option><?php echo str_replace("+","'",$etablissement['libelle']);?></option>
                                                 <?php }?>
                                                   
												</select>
											</div>
										</div>
                                        </div>
                                        <div class="col-lg-12 col-md-12 col-sm-12">
                                                
											
                                                <br>
                                            </div>
										<div class="col-lg-8 col-md-8 col-sm-8">
											<button type="submit" class="btn btn-sm btn-info"> <i class="la la-send"></i> SOUMETTRE</button>
											
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


    </div>
    <!--**********************************
        Main wrapper end
    ***********************************-->

  

    <!--**********************************
        Scripts
    ***********************************-->
    
   <!-- Required vendors -->
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
<script>
    // Récupérer l'élément select
    var selectSerie = document.getElementById('serieSelect');

    // Liste de séries (vous pouvez étendre cette liste selon vos besoins)
    var series = [
        "Série A1",
        "Série A2",
        "Série A4",
        "Série C",
        "Série D",
        // Ajoutez d'autres séries ici...
    ];

    // Ajouter les séries au sélecteur
    for (var i = 0; i < series.length; i++) {
        var option = document.createElement('option');
        option.value = series[i];
        option.text = series[i];
        selectSerie.appendChild(option);
    }
</script>

<script src="../vendor/global/global.min.js"></script>
	<script src="../vendor/bootstrap-select/dist/js/bootstrap-select.min.js"></script>
    <script src="../js/custom.min.js"></script>
	<script src="../js/dlabnav-init.js"></script>

	<!-- Svganimation scripts -->
    <script src="../vendor/svganimation/vivus.min.js"></script>
    <script src="../vendor/svganimation/svg.animation.js"></script>
    <script src="../vendor/select2/js/select2.full.min.js"></script>
    <script src="../js/plugins-init/select2-init.js"></script>
	<!-- pickdate -->
    <script src="../vendor/pickadate/picker.js"></script>
    <script src="../vendor/pickadate/picker.time.js"></script>
    <script src="../vendor/pickadate/picker.date.js"></script>
	
	<!-- Pickdate -->
    <script src="../js/plugins-init/pickadate-init.js"></script>

  
<!-- Script JavaScript pour gérer les actions côté client -->
<script>
    // Configuration de Dropzone.js
    Dropzone.options.pdfDropzone = {
        acceptedFiles: '.pdf',
        maxFiles: 5, // Nombre maximal de fichiers autorisés
        init: function() {
            this.on("addedfile", function(file) {
                // Fonction exécutée lorsqu'un fichier est ajouté
                displaySelectedFiles();
            });
        }
    };

    function displaySelectedFiles() {
        // Récupérer l'objet Dropzone
        var dropzone = Dropzone.forElement("#pdfDropzone");

        // Récupérer la liste des fichiers sélectionnés
        var files = dropzone.files;

        // Récupérer l'élément div pour afficher les fichiers sélectionnés
        var selectedFilesDiv = document.getElementById('selectedFiles');

        // Effacer le contenu précédent
        selectedFilesDiv.innerHTML = "";

        // Afficher les noms des fichiers dans la div
        for (var i = 0; i < files.length; i++) {
            selectedFilesDiv.innerHTML += "Fichier sélectionné : " + files[i].name + "<br>";
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


<script>
     const today = new Date();
    today.setFullYear(today.getFullYear() - 22);
    const minDate = today.toISOString().split('T')[0];

    // Définir la date minimale dans l'attribut min de l'input
    document.getElementById('dateOfBirth').setAttribute('min', minDate);

</script>
</body>
</html>
<?php 

}else{
    header("location: ../connexion");
}
?>
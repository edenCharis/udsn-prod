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
   
    <style>
        .loading-spinner {
            display: none;
            margin-left: 10px;
        }
        .error-message {
            color: red;
            font-size: 12px;
            margin-top: 5px;
        }
        .success-message {
            color: green;
            font-size: 12px;
            margin-top: 5px;
        }
    </style>

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
                            <h4>Formulaire d'accès aux resultats</h4>
                        </div>
                    </div>
                    <div class="col-sm-6 p-md-0 justify-content-sm-end mt-2 mt-sm-0 d-flex">
                        <ol class="breadcrumb">
                          
                            <li class="breadcrumb-item active"><a href="index">UDSN</a></li>
                            <li class="breadcrumb-item active"><a href="javascript:void(0);">resultat</a></li>
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
                                <form  method="post" action="traitement_resultat.php" enctype="multipart/form-data" id="resultatForm">
									<div class="row">
										
										<div class="col-lg-6 col-md-6 col-sm-12">
											<div class="form-group">
												<label class="form-label">MATRICULE *</label>
												<div class="input-group">
													<input type="text"  class="form-control" id="matricule"  name="matricule" required>
													<div class="input-group-append">
														<span class="loading-spinner" id="loadingSpinner">
															<i class="fa fa-spinner fa-spin"></i>
														</span>
													</div>
												</div>
												<div id="matriculeMessage"></div>
											</div>
										</div>

										<div class="col-lg-6 col-md-6 col-sm-12">
											<div class="form-group">
												<label class="form-label">Examen *</label>
												<select class="form-control"  name="examen" required>
                                                    <option value="">-- Sélectionner --</option>
													<option>Ordinaire</option>
                                                    <option>Rattrapage</option>
												</select>
											</div>
										</div>

                                        <div class="col-lg-6 col-md-6 col-sm-12">
                                            <div class="form-group">
												<label class="form-label">Classe</label>
                                                <input type="text" class="form-control" id="classe" name="classe" readonly required>
											</div>
										</div>
										
                                        <div class="col-lg-6 col-md-6 col-sm-12">
                                            <div class="form-group">
												<label class="form-label">Année universitaire</label>
												<input type="text" class="form-control" id="annee" name="annee" readonly required>
											</div>
										</div>

                                        <div class="col-lg-6 col-md-6 col-sm-12">
                                            <div class="form-group">
												<label class="form-label">Etablissement</label>
                                                <input type="text" class="form-control" id="etablissement" name="etablissement" readonly required>
											</div>
										</div>

										<div class="col-lg-6 col-md-6 col-sm-12">
											<div class="form-group">
												<label class="form-label">Semestre *</label>
												<select  class="form-control" name="semestre" required>
                                                    <option value="">-- Sélectionner --</option>
													<option>semestre 1</option>
                                                    <option>semestre 2</option>
                                                    <option>semestre 3</option>
                                                    <option>semestre 4</option>
                                                    <option>semestre 5</option>
                                                    <option>semestre 6</option>
												</select>
											</div>
										</div>

                                        <div class="col-lg-12 col-md-12 col-sm-12">
                                            <br>
                                        </div>

										<div class="col-lg-8 col-md-8 col-sm-8">
											<button type="submit" class="btn btn-lg btn-success" id="submitBtn" disabled> 
												<i class="la la-send"></i> Consulter mon résultat
											</button>
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

<!-- Script AJAX pour remplissage automatique -->
<script>
$(document).ready(function() {
    let typingTimer;
    const doneTypingInterval = 800; // Attendre 800ms après la dernière frappe
    
    $('#matricule').on('keyup', function() {
        clearTimeout(typingTimer);
        const matricule = $(this).val().trim();
        
        if(matricule.length >= 3) {
            typingTimer = setTimeout(function() {
                rechercherEtudiant(matricule);
            }, doneTypingInterval);
        } else {
            // Réinitialiser les champs si matricule trop court
            resetFields();
        }
    });
    
    $('#matricule').on('keydown', function() {
        clearTimeout(typingTimer);
    });
    
    function rechercherEtudiant(matricule) {
        // Afficher le spinner
        $('#loadingSpinner').show();
        $('#matriculeMessage').html('');
        
        $.ajax({
            url: 'get_student_info.php',
            type: 'POST',
            data: { matricule: matricule },
            dataType: 'json',
            success: function(response) {
                $('#loadingSpinner').hide();
                
                if(response.success) {
                    // Remplir les champs automatiquement
                    $('#classe').val(response.data.classe);
                    $('#annee').val(response.data.annee);
                    $('#etablissement').val(response.data.etab);
                    
                    // Afficher message de succès
                    $('#matriculeMessage').html('<span class="success-message"><i class="fa fa-check"></i> Étudiant trouvé</span>');
                    
                    // Activer le bouton submit
                    $('#submitBtn').prop('disabled', false);
                } else {
                    // Réinitialiser les champs
                    resetFields();
                    
                    // Afficher message d'erreur
                    $('#matriculeMessage').html('<span class="error-message"><i class="fa fa-exclamation-triangle"></i> ' + response.message + '</span>');
                }
            },
            error: function() {
                $('#loadingSpinner').hide();
                resetFields();
                $('#matriculeMessage').html('<span class="error-message"><i class="fa fa-exclamation-triangle"></i> Erreur de connexion</span>');
            }
        });
    }
    
    function resetFields() {
        $('#classe').val('');
        $('#annee').val('');
        $('#etablissement').val('');
        $('#submitBtn').prop('disabled', true);
    }
    
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
    header("location: ../connexion");
}
?>
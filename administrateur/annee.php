<?php 
include '../php/connexion.php';
include '../php/lib.php';

session_start();

if( $_SESSION['id'] == session_id()){


    if(isset($_GET['supan'])){
           $id=$_GET['supan'];
             $sql ="delete from annee where id=$id";


             if($connexion->query($sql)){
                header("location: annee?sucess=suppression avec success");
             }else{
                header("location: annee?erreur=$connexion->error");
            
             }

    }



?>


<!DOCTYPE html>
<html lang="en">

<head>
	
	<meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>CETUP - Administrateur </title>
    <!-- Favicon icon -->
    <link rel="icon" type="image/png" sizes="16x16" href="../images/favicon.png">
    <link rel="stylesheet" href="../vendor/jqvmap/css/jqvmap.min.css">
	<link rel="stylesheet" href="../vendor/chartist/css/chartist.min.css">
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
   

        <!--**********************************
            Nav header start
        ***********************************-->
        <?php 
    
    include '../entete.php';
    ?>
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
                        <div class="header-left">
                          <p>MODULE ADMINISTRATEUR</p>
                        </div>

                        <ul class="navbar-nav header-right">
                            <li class="nav-item dropdown notification_dropdown">
                                <a class="nav-link bell ai-icon" href="#" role="button" data-toggle="dropdown">
                                    <svg id="icon-user" xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewbox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-bell">
										<path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"></path>
										<path d="M13.73 21a2 2 0 0 1-3.46 0"></path>
									</svg>
                                    <div class="pulse-css"></div>
                                </a>
                                <div class="dropdown-menu dropdown-menu-right">
                                    <ul class="list-unstyled">
                                        
                                      
                                    </ul>
                                    <a class="all-notification" href="#">See all notifications <i class="ti-arrow-right"></i></a>
                                </div>
                            </li>
                            <li class="nav-item dropdown header-profile">
                                <a class="nav-link" href="#" role="button" data-toggle="dropdown">
                                    <img src="../images/profile/education/pic100.jpeg" width="20" alt="">
                                </a>
                                <div class="dropdown-menu dropdown-menu-right">
                                   
                                    <a href="../login" class="dropdown-item ai-icon">
                                        <svg id="icon-logout" xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewbox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-log-out"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"></path><polyline points="16 17 21 12 16 7"></polyline><line x1="21" y1="12" x2="9" y2="12"></line></svg>
                                        <span class="ml-2">DECONNEXION </span>
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
                            <h4>Paramétrage des années scolaires</h4>
                        </div>
                    </div>
                    <div class="col-sm-6 p-md-0 justify-content-sm-end mt-2 mt-sm-0 d-flex">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="index">Tableau de Bord</a></li>
                        </ol>
                    </div>
                </div>
					<div class="col-lg-12">
						<div class="row tab-content">
							<div id="list-view" class="tab-pane fade active show col-lg-12">
								<div class="card">
									<div class="card-header">
										<h4 class="card-title">Liste des années scolaires </h4>
										<a href="add-student.html" class="btn btn-primary" data-toggle="modal" data-target="#typeAgentModal">+ Ajouter</a>
									</div>
									<div class="card-body">
										<div class="table-responsive">
											<table id="example3" class="display" style="min-width: 845px">
												<thead>
													<tr>
														
														<th>ID. Année</th>
														<th>Année scolaire</th>
														
														<th>Action</th>
													</tr>
												</thead>
												<tbody>
                                                    <?php 
                                                      $sql ="select * from annee";
                                                      $resultat = $connexion->query($sql);

                                                      while($type = $resultat->fetch_assoc()){
                                                    ?>
													<tr>
														
														<td><?php echo $type['id'];?></td>
														<td><a href="javascript:void(0);"><?php echo str_replace("+","'",$type['libelle']);?></td>
														
														<td>
                                                           <a class="btn btn-danger" href="annee?supan=<?php echo $type['id'];?>" ><i class="la la-trash-o"></i>Supprimer</a>
														</td>												
													</tr>

                                                    <?php }?>
													
												
												</tbody>
											</table>
									
									
								</div>
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
        <div class="modal fade" id="typeAgentModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalLabel">Enregistrement d'une année scolaire</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <!-- Formulaire pour enregistrer le type d'agent -->
                <form id="typeAgentForm" method="post" action="traitement">
                    <div class="form-group">
                        <label for="debut">Année debut :</label>
                        <input type="number" class="form-control" id="debut" name="debut"  required>
                    </div>
                    <div class="form-group">
                        <label for="debut">Année fin :</label>
                        <input type="number" class="form-control" id="fin" name="fin"  required>
                    </div>
                    <button type="submit" class="btn btn-primary">Enregistrer</button>
                </form>
            </div>
        </div>
    </div>
</div>


<div class="modal" id="messageModal" tabindex="-1" role="dialog" aria-labelledby="messageModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="messageModalLabel">CETUP</h5>
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
            var docId = $(this).data('id');
            var docType = $(this).data('type');

            // Pré-remplir le formulaire modal avec les données actuelles
            $('#nivId').val(docId);
            $('#nouveauType').val(docType);
        });
    });
</script>
		
</body>
</html>
<?php 

}else{
    header("location: ../login");
}
?>
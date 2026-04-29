<?php 
include '../php/connexion.php';
include '../php/lib.php';

session_start();

if( $_SESSION['id'] == session_id() and  $_SESSION['role']=="scolarité"){



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
                            <h3>Recap </h3>
                        </div>
                    </div>
                    <div class="col-sm-6 p-md-0 justify-content-sm-end mt-2 mt-sm-0 d-flex">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="../scolarite/">Scolarité</a></li>
                            <li class="breadcrumb-item"><a href="../scolarite/pvd">Déliberation</a></li>
                           
                           
                        </ol>
                    </div>
                </div>
				
				<div class="row">
					<div class="col-lg-12">
						<div class="card">
							<div class="card-header">
								
								</div>
							<div class="card-body">
								<div class="table-responsive">
									<table id="example3" class="display" style="min-width: 845px">
										<thead>
											<tr>
												
												<th>N°</th>
												<th>Etudiant</th>
                                                <th>Parcours</th>
												<th>Specialité</th>
                                                <th>Examen</th>
                                                <th>Moyenne</th>
                                                <th>Decision</th>
                                                <th>Semestre</th>
												<th>Année universitaire</th>
												
											</tr>
										</thead>
										<tbody>

                                          <?php 
                                                 $sql ="select * from recap where etab='".$_SESSION['etablissement']."'";

                                                 $resultat =$connexion->query($sql);

                                                 while($ue=$resultat ->fetch_object()){
                                          
                                          ?>
											<tr>
												
												<td><?php echo $ue->id;?></td>
												<td><?php echo mettrePremieresLettresMajuscules(getNomEtudiant(getCandidatCodeByInscription($ue->etudiant,$connexion),$connexion,$_SESSION["lib_etab"])."  ".getPrenomEtudiant(getCandidatCodeByInscription($ue->etudiant,$connexion),$connexion,$_SESSION["lib_etab"]));?></td>
                                                <td><?php echo getParcours(getSpecialiteDuCandidat(getCandidatCodeByInscription($ue->etudiant,$connexion),$ue->annee,$connexion),$connexion);?></td>
												<td><?php echo getSpecialiteDuCandidat(getCandidatCodeByInscription($ue->etudiant,$connexion),$ue->annee,$connexion);?></td>
                                                <td><?php echo $ue->examen;?></td>
                                                <td><?php echo $ue->moy;?></td>	
                                                <td><?php echo $ue->decision;?></td>	
                                                <td><?php echo $ue->semestre;?></td>
                                                <td><?php echo $ue->annee;?></td>
                                                
																								
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
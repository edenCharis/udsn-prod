<?php 
include '../php/connexion.php';
include '../php/lib.php';

session_start();

if( $_SESSION['id'] == session_id() and  $_SESSION['role']=="daarhspe"){


    if(isset($_GET['sup']))
    {
        $userIP = $_SERVER['REMOTE_ADDR'];

        $sql ="delete from contrat where numero_contrat='".$_GET['sup']."'";
        if($connexion->query($sql)){
          logUserAction($connexion,$_SESSION['id_user'],"suppression d'un contrat ",date("Y-m-d H:i:s"),$userIP,"valeur  : ".$_GET['sup']);

          header("location: ../DAARHSPE/contrat?sucess=Opération effectuée avec succès");
          exit;
    }
  }
?>


<!DOCTYPE html>
<html lang="en">

<head>
	
	<meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title> <?php echo $_SESSION['etablissement'];?> </title>
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
                            <h4>CONTRATS D'ENSEIGNEMENT</h4>
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
										<h4 class="card-title">Liste des Contrat </h4>
										<a href="index.php" class="btn btn-primary" >+ Ajouter</a>
										<div class="col-sm-4 p-md-0 justify-content-sm-end mt-2 mt-sm-0 d-flex">
    <button type="button" class="btn btn-info" data-toggle="modal" data-target="#printListModal">
        <i class="fa fa-print"></i> Imprimer Liste des Enseignants
    </button>
</div>
									</div>
									<div class="card-body">
										<div class="table-responsive">
											<table id="example3" class="display" style="min-width: 845px">
												<thead>
													<tr>
															<th>N°</th>
														<th>Code Unique</th>
														<th>Numéro de Contrat</th>
														<th>Enseignant</th>
                                                        <th>Etablissement</th>
                                                        <th>Année académique</th>
														
														<th>Action</th>
													</tr>
												</thead>
												<tbody>
                                                    <?php 
                                                      $sql ="select * from contrat";
                                                      $resultat = $connexion->query($sql);
                                                      $count=1;

                                                      while($type = $resultat->fetch_assoc()){
                                                    ?>
													<tr>
													    <td><?php echo $count;?></td>
														  <td><?php echo getcodeUnique($type['numero_contrat'],$connexion);;?></td>
														<td class="text-warning"><?php echo $type['numero_contrat'];?></td>
														<td><a href="javascript:void(0);"><?php echo strtoupper( str_replace("+","'",getNomPrenomEnseignantById($type['enseignant'],$connexion)));?></td>
                                                        <td><a href="javascript:void(0);"><?php  echo  mettrePremieresLettresMajuscules(str_replace("+","'",getLibelleEtablissement($type['etab'],$connexion))); ?></td>
                                                        <td><a href="javascript:void(0);"><?php  echo str_replace("+","'",$type['annee']); ?></td>
													
														<td>
                                                            <a class="btn btn-sm btn-danger" href="contrat?sup=<?php echo $type["numero_contrat"];?>" ><i class="la la-trash-o"></i>Supprimer</a>
                                                            <a class="btn btn-sm  btn-warning" href="details?contrat=<?php echo $type["numero_contrat"];?>&annee=<?php echo $type["annee"];?>" > <i class="la la-info-circle"></i></i>Details</a>
                                                            <a class="btn  btn-sm  btn-info" href="t?contrat=<?php echo $type["numero_contrat"];?>&annee=<?php echo $type["annee"];?>" ><i class="la la-print"></i>Imprimer</a>
														
														</td>												
													</tr>

                                                    <?php $count++; }?>
													
												
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
    
    
    <div class="modal fade" id="printListModal" tabindex="-1" role="dialog" aria-labelledby="printListModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="printListModalLabel">Sélectionner l'Année Académique</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form id="printListForm">
                    <div class="form-group">
                        <label for="annee_select">Année Académique</label>
                        <select id="annee_select" class="form-control" required>
                            <option value="">Sélectionner une année</option>
                            <?php 
                                $sql = "SELECT * FROM annee ORDER BY libelle DESC";
                                $resultat = $connexion->query($sql);
                                while($annee = $resultat->fetch_assoc()){
                            ?>
                                <option value="<?php echo $annee['libelle']; ?>">
                                    <?php echo str_replace("+","'",$annee['libelle']); ?>
                                </option>
                            <?php } ?>
                        </select>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Annuler</button>
                <button type="button" class="btn btn-primary" onclick="generateTeacherList()">
                    Générer la Liste
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Modal for Displaying Teacher List -->
<div class="modal fade" id="teacherListModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-xl" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Liste des Enseignants</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body" id="teacherListContent" style="max-height: 500px; overflow-y: auto;">
                <!-- Content will be loaded here -->
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Fermer</button>
                <button type="button" class="btn btn-primary" onclick="printTeacherList()">
                    <i class="fa fa-print"></i> Imprimer
                </button>
            </div>
        </div>
    </div>
</div>

<script>
function generateTeacherList() {
    var annee = $('#annee_select').val();
    
    if (!annee) {
        alert('Veuillez sélectionner une année académique');
        return;
    }
    
    // Close the year selection modal
    $('#printListModal').modal('hide');
    
    // Fetch teacher list via AJAX
    $.ajax({
        url: 'get_teacher_list.php',
        method: 'POST',
        data: { annee: annee },
        success: function(response) {
            $('#teacherListContent').html(response);
            $('#teacherListModal').modal('show');
        },
        error: function() {
            alert('Erreur lors du chargement de la liste');
        }
    });
}

function printTeacherList() {
    var content = document.getElementById('teacherListContent').innerHTML;
    var printWindow = window.open('', '', 'height=600,width=800');
    
    printWindow.document.write('<html><head><title>Liste des Enseignants</title>');
    printWindow.document.write('<style>');
    printWindow.document.write('body { font-family: Arial, sans-serif; }');
    printWindow.document.write('table { width: 100%; border-collapse: collapse; margin-top: 20px; }');
    printWindow.document.write('th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }');
    printWindow.document.write('th { background-color: #4CAF50; color: white; }');
    printWindow.document.write('h3 { text-align: center; }');
    printWindow.document.write('@media print { button { display: none; } }');
    printWindow.document.write('</style>');
    printWindow.document.write('</head><body>');
    printWindow.document.write(content);
    printWindow.document.write('</body></html>');
    
    printWindow.document.close();
    printWindow.focus();
    
    setTimeout(function() {
        printWindow.print();
        printWindow.close();
    }, 250);
}
</script>
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
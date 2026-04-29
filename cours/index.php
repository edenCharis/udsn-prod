<?php 
include '../php/connexion.php';
include '../php/lib.php';

session_start();

if( $_SESSION['id'] == session_id() and  $_SESSION['role']=="cours"){



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
                            <h3>Gestion des heures effectuées</h3>
                        </div>
                    </div>
                    <div class="col-sm-6 p-md-0 justify-content-sm-end mt-2 mt-sm-0 d-flex">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="../cours/">Scolarité</a></li>
                            <li class="breadcrumb-item active"><a href="javascript:void(0);">cours</a></li>
                            
                           
                        </ol>
                    </div>
                </div>
				
				<div class="row">
					<div class="col-lg-12">
						<div class="card">
							<div class="card-header">
								<h4 class="card-title">Liste des cours effectués</h4>
								<a href="add-library.html" class="btn btn-primary" data-toggle="modal" data-target="#typeAgentModal">+ Nouveau</a>
							</div>
							<div class="card-body">
								<div class="table-responsive">
									<table id="example3" class="display" style="min-width: 845px">
										<thead>
											<tr>
												<th>N°</th>
												<th>Enseignant</th>
												<th>Ecue</th>
												<th>Semestre</th>
												<th>Classe</th>
                                                <th>Date du cours</th>
                                                <th>Nombre d'heures effectuées</th>
                                                <th>Nature</th>
												<th>Année</th>
                                                <th>Action</th>
											</tr>
										</thead>
										<tbody>

                                          <?php 
                                                 $sql ="select * from cours where etab='".$_SESSION['etablissement']."' and user_id=".$_SESSION["id_user"];

                                                 $resultat =$connexion->query($sql);
                                                 while($ue=$resultat ->fetch_assoc()){
                                          
                                          ?>
											<tr>
												
												<td><?php echo $ue['enseignant'];?></td>
												<td><?php echo strtoupper(str_replace("+","'", getNomEnseignant( $ue['enseignant'],$connexion))."   ".str_replace("+","'", getPrenomEnseignant( $ue['enseignant'],$connexion)));?></td>
											
												<td><?php echo str_replace("+","'",$ue['ecue']);?></td>
													<td><?php echo $ue['semestre'];?></td>
												<td><?php echo $ue['classe'];?></td>
                                                <td><?php echo $ue['date_c'];?></td>
                                                <td><?php echo $ue['heure'];?></td>	
                                                	
                                                <td><?php echo $ue['nature'];?></td>
                                                <td><?php echo $ue['annee'];?></td>	
												<td>

                                                
                                                   <a href="traitement2?supcours=<?php echo $ue['id'];?>" class="btn btn-sm btn-danger"><i class="la la-trash-o"></i></a>
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
<div class="modal" id="modifierModal" tabindex="-1" role="dialog" aria-labelledby="modifierModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modifierModalLabel">Modification</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <!-- Formulaire de modification -->
                <form  method="post" action="traitement2">
                    <input type="hidden" id="affId" name="affId">

                    <div class="input-group mb-3">
                                            <div class="input-group-prepend">
                                                <label class="input-group-text">ECUE  </label>
                                            </div>
                                            <select id="nouveauEcue" class="" name="nouveauEcue" >
                                                <option selected=""></option>
                                               <?php 
                                                        $sql="select * from ecue where etab='".$_SESSION['etablissement']."'";
                                                        $resultat=$connexion->query($sql);
                                                        while($etablissement =$resultat->fetch_assoc()){
                                                ?>
                                                 <option><?php echo str_replace("+","'",$etablissement['libelle']);?></option>
                                                 <?php }?>
                                            </select>
                    </div>
                    <div class="input-group mb-3">
                                            <div class="input-group-prepend">
                                                <label class="input-group-text">Classe </label>
                                            </div>
                                            <select id="nouveauClasse" class="" name="nouveauClasse" >
                                                <option selected=""></option>
                                               <?php 
                                                        $sql="select * from classe where etab='".$_SESSION['etablissement']."'";
                                                        $resultat=$connexion->query($sql);
                                                        while($etablissement =$resultat->fetch_assoc()){
                                                ?>
                                                 <option><?php echo str_replace("+","'",$etablissement['libelle']);?></option>
                                                 <?php }?>
                                            </select>
                    </div>
                       <div class="input-group mb-3">
                                            <div class="input-group-prepend">
                                                <label class="input-group-text">Semestre  </label>
                                            </div>
                                            <select id="nouveauSem" class="" name="nouveauSem" >
                                                <option selected=""></option>
                                               <?php 
                                                        $sql="select * from semestre ";
                                                        $resultat=$connexion->query($sql);
                                                        while($etablissement =$resultat->fetch_assoc()){
                                                ?>
                                                 <option><?php echo str_replace("+","'",$etablissement['libelle']);?></option>
                                                 <?php }?>
                                            </select>
                       </div>
                       <div class="input-group mb-3">
                                            <div class="input-group-prepend">
                                                <label class="input-group-text">Année académique  </label>
                                            </div>
                                            <select id="nouveauAnnee" class="" name="nouveauAnnee" >
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
                     <button type="submit" class="btn btn-success">Sauvegarder</button>
                    <button type="cancel" class="btn btn-danger">Annuler</button>
                </form>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="typeAgentModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalLabel">Formulaire d'enregistrement d'un cours </h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <!-- Formulaire pour enregistrer le type d'agent -->
                <form id="typeAgentForm" method="post" action="traitement2">
                  
                       
                    <div class="input-group mb-3">
                                            <div class="input-group-prepend">
                                                <label class="input-group-text">Enseignant </label>
                                            </div>
                                            <select id="enseignant" name="enseignant" required class="disabling-options">
                                                <option selected=""></option>
                                               <?php 
                                                        $sql="select * from enseignant where etab='".$_SESSION['etablissement']."'";
                                                        $resultat=$connexion->query($sql);
                                                        while($etablissement =$resultat->fetch_assoc()){
                                                ?>
                                                 <option value="<?php echo $etablissement['code'];?>"><?php echo str_replace("+","'", getNomEnseignant( $etablissement['code'],$connexion))."   ".str_replace("+","'", getPrenomEnseignant( $etablissement['code'],$connexion));?></option>
                                                 <?php }?>
                                            </select>
                    </div>
                    <div class="input-group mb-3">
                                            <div class="input-group-prepend">
                                                <label class="input-group-text">Ecue </label>
                                            </div>
                                            <select id="ecue"  name="ecue" class="disabling-options">
                                                <option selected=""></option>
                                               <?php 
                                                        $sql="select * from ecue where etab='".$_SESSION['etablissement']."'";
                                                        $resultat=$connexion->query($sql);
                                                        while($etablissement =$resultat->fetch_assoc()){
                                                ?>
                                                 <option value="<?php echo $etablissement['code_ecue'];?>"><?php echo str_replace("+","'",$etablissement['libelle'])."-".$etablissement["code_ecue"]; ?></option>
                                                 <?php }?>
                                            </select>
                    </div>


                    <div class="input-group mb-3">
                                            <div class="input-group-prepend">
                                                <label class="input-group-text">Classe </label>
                                            </div>
                                            <select id="classe"  name="classe" class="disabling-options" >
                                                <option selected=""></option>
                                               <?php 
                                                        $sql="select * from classe where etab='".$_SESSION['etablissement']."'";
                                                        $resultat=$connexion->query($sql);
                                                        while($etablissement =$resultat->fetch_assoc()){
                                                ?>
                                                 <option><?php echo str_replace("+","'",$etablissement['libelle']);?></option>
                                                 <?php }?>
                                            </select>
                    </div>
                    <div class="form-group">
                        <label for="nouveauClasse">Date </label>
                        <input type="date" class="form-control" id="datecours" name="datecours" required>
                    </div>
                    <div class="form-group">
                        <label for="nouveauClasse">Debut </label>
                        <input type="time" class="form-control" id="debut" name="debut" required>
                    </div>
                    <div class="form-group">
                        <label for="nouveauClasse">Fin </label>
                        <input type="time" class="form-control" id="fin" name="fin" required>
                    </div>
                    <div class="form-group">
                    <label for="nouveauClasse">Nature </label>
                      <select class="disabling-options"  name="nature" id="">
                        <option value=""></option>
                        <option value="cours">Cours</option>
                        <option value="TP">Travaux Pratiques</option>
                      </select>
                    </div>
                    

                       <div class="input-group mb-3">
                                            <div class="input-group-prepend">
                                                <label class="input-group-text">Année académique  </label>
                                            </div>
                                            <select id="annee" name="annee" class="disabling-options" >
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
                       
                       
                    
                    <button type="submit" class="btn btn-primary">Enregistrer</button>
                    <button type="button" data-dismiss="modal" class="btn btn-danger">Annuler</button>
                </form>
            </div>
        </div>
    </div>

</div>

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
    <script src="../vendor/global/global.min.js"></script>
	<script src="../vendor/bootstrap-select/dist/js/bootstrap-select.min.js"></script>
	<script src="../js/custom.min.js"></script>
		
    <!-- Chart Morris plugin files -->
    <script src="../vendor/raphael/raphael.min.js"></script>
    <script src="../vendor/morris/morris.min.js"></script>
		
	
	<!-- Chart piety plugin files -->
    <script src="../vendor/peity/jquery.peity.min.js"></script>
    <script src="../vendor/select2/js/select2.full.min.js"></script>
    <script src="../js/plugins-init/select2-init.js"></script>
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


}else{
    header("location: ../login");
}?>
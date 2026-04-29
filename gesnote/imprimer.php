<?php 
include '../php/connexion.php';
include '../php/lib.php';

session_start();

if( $_SESSION['id'] == session_id() and  $_SESSION['role']=="gesnote"){

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
     
        <!--**********************************
            Header end ti-comment-alt
        ***********************************-->

        <!--**********************************
            Sidebar start
        ***********************************-->
      
        <!--**********************************
            Sidebar end
        ***********************************-->
		
		<!--**********************************
            Content body start
        ***********************************-->
        <div class="container">
            <!-- row -->
            <div class="container-fluid">
				
				<div class="row page-titles mx-0">
                    <div class="col-sm-6 p-md-0">

                        <div class="welcome-text">
                            <img src="../administrateur/<?php echo  $_SESSION['logo_univ']?>" width="30" >
                            <h3>Gestion des notes des étudiants</h3>
                        </div>
                    </div>
                    <div class="col-sm-6 p-md-0 justify-content-sm-end mt-2 mt-sm-0 d-flex">
                        <ol class="breadcrumb">
                            
                            <li class="breadcrumb-item active">Moyennes Génerales</li>
                        </ol>
                    </div>
                </div>
				
				<div class="row">
					<div class="col-lg-12">
						<div class="card">
							<div class="card-header">
								<h4 class="card-title">Moyennes</h4>
                                <button onclick="javascript:window.print();" class="btn btn-light" type="button"> <i class="fa fa-print"></i> Imprimer </button>
							</div>
                            <div class="card-body">
								<div class="table-responsive">
									<table id="example3" class="display" style="min-width: 845px">
										<thead>
											<tr>
												<th>N°</th>
												<th>Etudiant</th>
                                                <th>Classe</th>
                                                <th>Semestre</th>
                                                <th>Moyenne Générale</th>
                                                <th>Année scolaire</th>
                                                <th>Statut</th>
                                                
												<th>Rattrapage</th>
											</tr>
										</thead>
										<tbody>

                                          <?php 
                                                 $sql ="select * from vue_moyenne_ue   where etab='".$_SESSION['etablissement']."'";
                                                 $resultat =$connexion->query($sql);
                                                 while($ue=$resultat->fetch_assoc()){
                                          ?>
											<tr>
												
												<td><?php echo $ue['id'];?></td>
												<td><?php echo str_replace("+","'", obtenirNomPrenom(obtenirCodeById($ue['inscription'],$connexion),$ue['annee'],$connexion));?></td>
											
												
												<td><?php echo $ue['classe'];?></td>
                                                <td><?php echo $ue['semestre'];?></td>
                                               
                                                <td><?php echo round(calcul_moyenne($ue['inscription'],$ue['semestre'],$ue['annee'],$ue['etab'],$connexion),2);?></td>
                                                <td><?php echo $ue['annee']?></td>

                                               <td>
                                               <?php
$ecue = rechercher_notes_eliminatoires($ue['inscription'],$ue['semestre'],$ue['annee'],$connexion);
if($ecue !== null){

?><?php echo "Non validé";}else{

    echo "Validé";
}?>
</td>
                                               			
                                                
												<td>

<?php
$ecue = rechercher_notes_eliminatoires($ue['inscription'],$ue['semestre'],$ue['annee'],$connexion);
if($ecue !== null){
    $_SESSION['ecue_non_valide'] =  rechercher_notes_eliminatoires($ue['inscription'],$ue['semestre'],$ue['annee'],$connexion);
    ;

    foreach ($_SESSION['ecue_non_valide'] as $ecue){
        echo $ecue. " <br>"; }}

?>
                                               		
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
        <!--**********************************<
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
                <form  method="post" action="traitement3">
                    <input type="hidden" id="noteId" name="noteId">

                    <div class="form-group">
                        <label for="nouveauMoyDev">Moyenne Devoir</label>
                        <input type="number" min="0" max="20" class="form-control" id="nouveauMoyDev" name="nouveauMoyDev" >
                    </div>

                    <div class="form-group">
                        <label for="nouveauMoyEx">Moyenne Examen</label>
                        <input type="number" min="0" max="20" class="form-control" id="nouveauMoyEx" name="nouveauMoyEx" >
                    </div>
                     <button type="submit" class="btn btn-success">Sauvegarder</button>
                    <button type="cancel" class="btn btn-danger">Annuler</button>
                </form>
            </div>
        </div>
    </div>
</div>

<div class="modal fade bd-example-modal-lg" id="typeAgentModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h3 class="modal-title" id="exampleModalLabel">formulaire d'enregistrement d'une note</h3>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <!-- Formulaire pour enregistrer le type d'agent -->
                <form id="typeAgentForm" method="post" action="traitement3">
                  
                       
                    <div class="input-group mb-3">
                                            <div class="input-group-prepend">
                                                <label class="input-group-text">Etudiant </label>
                                            </div>
                                            <select  class="disabling-options"  name="etudiant" required>
                                                <option selected=""></option>
                                               <?php 
                                                        $sql="select * from inscription where etab='".$_SESSION['etablissement']."'";
                                                        $resultat=$connexion->query($sql);
                                                        while($etudiant =$resultat->fetch_assoc()){

                                                            $codeCandidat= getCandidatCodeByInscription($etudiant['id'],$connexion);
                                                ?>
                                                 <option value="<?php echo $etudiant['id'];?>"><?php echo obtenirNomPrenom($codeCandidat,$etudiant['annee'],$connexion);?></option>
                                                 <?php }?>
                                            </select>
                    </div>
                    <div class="input-group mb-3">
                                            <div class="input-group-prepend">
                                                <label class="input-group-text">Ecue </label>
                                            </div>
                                            <select  id="single-select"  class="disabling-options"class="form-control form-control-lg" name="ecue" recquirec >
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
                                            <select   class="disabling-options" class="form-control form-control-lg" name="classe" recquired>
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
                                                <label class="input-group-text">Semestre </label>
                                            </div>
                                            <select   class="disabling-options" class="form-control form-control-lg" name="semestre" required >
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
                    <div class="form-group">
                        <label for="moydev">Moyenne Devoir </label>
                        <input type="number" min="0" max="20" class="form-control" id="moydev" name="moydev" required>
                    </div>
                    <div class="form-group">
                        <label for="moyex">Moyenne Examen </label>
                        <input type="number"  min="0" max="20" class="form-control" id="moyex" name="moyex" required>
                    </div>
                       <div class="input-group mb-3">
                                            <div class="input-group-prepend">
                                                <label class="input-group-text">Année académique  </label>
                                            </div>
                                            <select  class="disabling-options" class="form-control form-control-lg" name="annee" >
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
		
    <script src="../vendor/select2/js/select2.full.min.js"></script>
    <script src="../js/plugins-init/select2-init.js"></script>
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
            var x = $(this).data('moydev');
            var y= $(this).data('moyex');
 
           

            // Pré-remplir le formulaire modal avec les données actuelles
            $('#noteId').val(id);
            $('#nouveauMoyDev').val(x);
            $('#nouveauMoyEx').val(y);
         



        });
    });
</script>
	
</body>
</html>

<?php 


}else{
    header("location: ../login");
}?>
<?php 
include '../php/connexion.php';
include '../php/lib.php';

session_start();

if( $_SESSION['id'] == session_id() and  $_SESSION['role']=="scolarité"){


    if(isset($_GET['sup']))
    {
        $userIP = $_SERVER['REMOTE_ADDR'];

        $sql ="delete from enseignant where id='".$_GET['sup']."'";
        if($connexion->query($sql)){
          logUserAction($connexion,$_SESSION['id_user'],"suppression d'un enseignant ",date("Y-m-d H:i:s"),$userIP,"valeur  : ".$_GET['sup']);

          header("location: ../scolarite/enseignant?sucess=Opération effectuée avec succès");
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
                            <h3>Liste des enseignants</h3>
                        </div>
                    </div>
                    <div class="col-sm-6 p-md-0 justify-content-sm-end mt-2 mt-sm-0 d-flex">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="../scolarite/">Scolarité</a></li>
                            <li class="breadcrumb-item active"><a href="javascript:void(0);">Enseignants</a></li>
                           
                        </ol>
                    </div>
                </div>
				
				<div class="row">
					<div class="col-lg-12">
						<div class="card">
							<div class="card-header">
								<h4 class="card-title">		<a href="add-library.html" class="btn btn-sm btn-primary" data-toggle="modal" data-target="#typeAgentModal"> + Nouveau</a>
								<a href="print.php" class="btn btn-sm btn-success" > <i class="fa fa-print"></i>Imprimer</a></h4>
						
								
							</div>
							<div class="card-body">
								<div class="table-responsive">
									<table id="example3" class="display" style="min-width: 845px">
										<thead>
											<tr>
											    <th>N°</th>
												<th>N°. Contrat</th>
												<th>Parcours</th>
												<th>Matricule</th>
												<th>Nom</th>
												<th>Prenom</th>
												<th>Diplome</th>
												<th>Grade</th>
                                                <th>Email</th>
												<th>Telephone</th>
                                                <th>Taux Horaire</th>
                                                <th>Statut</th>
												<th>Action</th>
											</tr>
										</thead>
										<tbody>

                                          <?php 
                                                 $sql ="select * from enseignant where etab='".$_SESSION['etablissement']."'";

                                                 $resultat =$connexion->query($sql);
                                                 $count=1;
                                                 while($ue=$resultat ->fetch_assoc()){
                                          
                                          ?>
											<tr>
											    <td><?php echo $count;?></td>
												
												<td><?php echo $ue['contrat'];?></td>
												<td><?php echo str_replace("+","'",getparcoursbycontrat($ue['contrat'],$connexion));?></td>
												<td><?php echo $ue['code'];?></td>
												<td><?php echo str_replace("+","'", $ue['nom']);?></td>
												<td><?php echo str_replace("+","'", $ue['prenom']);?></td>
												<td><?php echo $ue['diplome'];?></td>
												<td><?php echo str_replace("+","'",$ue['grade']);?></td>
                                                <td><?php echo $ue['email'];?></td>
                                                <td><?php echo $ue['telephone'];?></td>
                                                <td><?php echo $ue['th'];?></td>
												<td><?php echo str_replace("+","'",$ue['statut']);?></td>	
												<td>

                                                
                                                    <a href="traitement1" class="btn btn-sm btn-primary " data-toggle="modal" data-target="#modifierModal" data-id="<?php echo $ue['id'];?>"
                                                    data-nom="<?php echo str_replace("+","'", $ue['nom']);?>"   data-prenom="<?php echo $ue['prenom'];?>"  data-dip="<?php echo $ue['diplome'];?>"
                                                     data-grd="<?php $ue['grade'];?>" data-th="<?php echo $ue['th'];?>"  data-email="<?php echo $ue['email']; ?>" data-tel="<?php echo $ue['telephone'];
                                                     ?>" data-type="<?php echo $ue['type']; ?>"><i class="la la-pencil"></i></a>
                                                    <a href="enseignant?sup=<?php echo $ue['id'];?>" class="btn btn-sm btn-danger"><i class="la la-trash-o"></i></a>
                                                </td>												
											</tr>

                                            <?php $count ++; }?>
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
                <h5 class="modal-title" id="modifierModalLabel">Modifier un enseignant</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <!-- Formulaire de modification -->
                <form  method="post" action="traitement2">
                    <input type="hidden" id="ensId" name="ensId">
                    <div class="form-group">
                        <label for="nouveauNom">Nom</label>
                        <input type="text" class="form-control" id="nouveauNom" name="nouveauNom" >
                    </div>
                       <div class="form-group">
                        <label for="nouveauNom">Prenom </label>
                        <input type="text" class="form-control" id="nouveauPrenom" name="nouveauPrenom" >
                    </div>

                    <div class="input-group mb-3">
                                            <div class="input-group-prepend">
                                                <label class="input-group-text">Diplome  </label>
                                            </div>
                                            <select  id="nouveauDiplome" class="disabling-options" name="nouveauDiplome" >
                                                <option selected=""></option>
                                               <?php 
                                                        $sql="select * from type_diplome ";
                                                        $resultat=$connexion->query($sql);
                                                        while($etablissement =$resultat->fetch_assoc()){
                                                ?>
                                                 <option><?php echo str_replace("+","'",$etablissement['libelle']);?></option>
                                                 <?php }?>
                                            </select>
                    </div>
                    <div class="input-group mb-3">
                                            <div class="input-group-prepend">
                                                <label class="input-group-text">Grade : </label>
                                            </div>
                                            <select id="nouveauGrade" class="disabling-options" name="nouveauGrade" >
                                                <option selected=""></option>
                                               <?php 
                                                        $sql="select * from type_grade ";
                                                        $resultat=$connexion->query($sql);
                                                        while($etablissement =$resultat->fetch_assoc()){
                                                ?>
                                                 <option><?php echo str_replace("+","'",$etablissement['libelle']);?></option>
                                                 <?php }?>
                                            </select>
                    </div>
                    <div class="form-group">
                        <label for="nouveaucredit">Email</label>
                        <input type="email" class="form-control" id="nouveauEmail" name="nouveauEmail" >
                    </div>
                    <div class="form-group">
                        <label for="nouveaucredit">Telephone</label>
                        <input type="text" class="form-control" id="nouveauTel" name="nouveauTel" >
                    </div>
                 
                       <div class="input-group mb-3">
                                            <div class="input-group-prepend">
                                                <label class="input-group-text">Statut  </label>
                                            </div>
                                            <select id="nouveauStatut" class="disabling-options" name="nouveauStatut" >
                                                <option selected=""></option>
                                               <?php 
                                                        $sql="select * from type_enseignant ";
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

<div class="modal fade bd-example-modal-sm" id="typeAgentModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-sm">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalLabel">Enregistrement d'un enseignant</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <!-- Formulaire pour enregistrer le type d'agent -->
                <form id="typeAgentForm" method="post" action="traitement2">
                    <div class="form-group">
                        <label for="nom">Nom</label>
                        <input type="text" class="form-control" id="nom" name="nom" required>
                    </div>
                       <div class="form-group">
                        <label for="prenom">Prenom</label>
                        <input type="text" class="form-control" id="prenom" name="prenom" required>
                    </div>
                    <div class="input-group mb-3">
                                            <div class="input-group-prepend">
                                                <label class="input-group-text">Diplome  </label>
                                            </div>
                                            <select id="diplome"  class="disabling-options" name="diplome" required>
                                                <option selected=""></option>
                                               <?php 
                                                        $sql="select * from type_diplome ";
                                                        $resultat=$connexion->query($sql);
                                                        while($etablissement =$resultat->fetch_assoc()){
                                                ?>
                                                 <option><?php echo str_replace("+","'",$etablissement['libelle']);?></option>
                                                 <?php }?>
                                            </select>
                    </div>
                 
                    <div class="input-group mb-3">
                                            <div class="input-group-prepend">
                                                <label class="input-group-text">Sexe </label>
                                            </div>
                                            <select id="grade"  class="disabling-options" name="sexe" required>
                                                <option selected=""></option>
                                                <option>masculin</option>
                                                <option>feminin</option>
                                            </select>
                    </div>
                    <div class="form-group">
                        <label for="email">Grade</label>
                        <select id="grade"  class="disabling-options" name="grade" required>
                                                <option selected=""></option>
                                               <?php 
                                                        $sql="select * from type_grade ";
                                                        $resultat=$connexion->query($sql);
                                                        while($etablissement =$resultat->fetch_assoc()){
                                                ?>
                                                 <option><?php echo str_replace("+","'",$etablissement['libelle']);?></option>
                                                 <?php }?>
                                            </select> </div>
                    <div class="form-group">
                        <label for="telephone">Telephone</label>
                        <input type="text" class="form-control" id="telephone" name="telephone" required>
                    </div>
                     <div class="form-group">
                        <label for="nouveaucredit">Email</label>
                        <input type="email" class="form-control" id="email" name="email" >
                    </div>
                       <div class="input-group mb-3">
                                            <div class="input-group-prepend">
                                                <label class="input-group-text">Statut  </label>
                                            </div>
                                            <select id="statut"  class="disabling-options" name="statut" required>
                                                <option selected=""></option>
                                               <?php 
                                                        $sql="select * from type_enseignant ";
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
            var nom = $(this).data('nom');
            var prenom= $(this).data('prenom');
            var  email = $(this).data('email');
            var tel = $(this).data('tel');
            var grade=  $(this).data('grd');
            var dip = $(this).data('dip');
            var st = $(this).data('type');
            var th=$(this).data('th');

            // Pré-remplir le formulaire modal avec les données actuelles
            $('#ensId').val(id);
            $('#nouveauNom').val(nom);
            $('#nouveauPrenom').val(prenom);
            $('#nouveauEmail').val(email);
            $('#nouveauTel').val(tel);
            $('#nouveauGrade').val(grade);
            $('#nouveauDiplome').val(dip);
            $('#nouveauStatut').val(st);
            $('#nouveauTh').val(th);



        });
    });
</script>
	
</body>
</html>

<?php 


}else{
    header("location: ../login");
}?>
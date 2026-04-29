<?php 
include '../php/connexion.php';
include '../php/lib.php';

session_start();

if( $_SESSION['id'] == session_id() and  $_SESSION['role']=="inscription"){

    if(isset($_GET['sup']))
    {

        $sql ="delete from candidat where id=".$_GET['sup'];
        if($connexion->query($sql)){
          logUserAction($connexion,$_SESSION['id_user'],"suppression d'une candidature ",date("Y-m-d H:i:s"),$userIP,"valeur supprimé : ".$_GET['id']);

          header("location: donnees?sucess=Suppression effectué success");
          exit;
    }}

?>


<!DOCTYPE html>
<html lang="en">

<head>
	
	<meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title><?php echo $_SESSION['univ'];?> - Scolarité de  <?php echo $_SESSION['etablissement'];?> </title>
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

        <!--**********************************
            Nav header start
        ***********************************-->

        <!--**********************************
            Nav header end
        ***********************************-->
        <?php include "header.php" ;?>
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
                            <h4>Données personnelles des étudiants</h4>
                        </div>
                    </div>
                    <div class="col-sm-6 p-md-0 justify-content-sm-end mt-2 mt-sm-0 d-flex">
                        <ol class="breadcrumb">
                          
                            <li class="breadcrumb-item active"><a href="index"> <?php echo $_SESSION['etablissement'];?></a></li>
                            <li class="breadcrumb-item active"><a href="javascript:void(0);"> Mon profil</a></li>
                        </ol>
                    </div>
                </div>
				
				<div class="row">
                    <div class="card">
                    <div class="card-body">
										<div class="table-responsive">
											<table id="example3" class="display" style="min-width: 845px">
												<thead>
													<tr>
														<th>#</th>
														<th>Code</th>
                                                        <th>Etudiant</th>
														<th>Date de naissance</th>
														<th>Lieu de naissance</th>
														<th>BAC</th>
                                                        <th>Moyenne BAC</th>
                                                        <th>Mention BAC</th>
														<th>Annee</th>
                                                        <th>Specialite</th>
                                                        <th>Année d'inscription</th>
														<th>Action</th>
													</tr>
												</thead>
												<tbody>

                                                <?php 

                                                if(isset($_GET['bac'])){
                                                    $sql ="select * from candidat where etab='".$_SESSION['lib_etab']."' and moyenneBac >= 12";
                                                }else if(isset($_GET['statutpaiement'])){
                                                    $sql ="select * from candidat where etab='".$_SESSION['lib_etab']."' and statut_paiement_inscription =1";
                                                }else{
                                                    $sql ="select * from candidat where etab='".$_SESSION['lib_etab']."'";

                                                }
                                                
                                                
                                                  $resultat=$connexion->query($sql);
                                                  if($resultat){
                                                      $count=1;
                                                  while($candidat = $resultat->fetch_assoc()){
                                                ?>
													<tr>
														<td><img class="rounded-circle" width="35" src="images/profile/small/pic1.jpg" alt=""><?php echo $count;?></td>
														<td><strong><?php echo $candidat['code'];?></strong></td>
                                                        <td><strong><?php echo $candidat['nom']."  ".$candidat["prenom"];?></strong></td>
														<td><?php echo $candidat['date_nais'] ?></td>
														<td><?php echo $candidat['lieu_nais'] ?></td>
														<td><a href="javascript:void(0);"><strong><?php echo $candidat['bac'] ?></strong></a></td>
                                                        <td><a href="javascript:void(0);"><strong><?php echo $candidat['moyenneBac'] ?></strong></a></td>
                                                        <td><a href="javascript:void(0);"><strong><?php echo $candidat['mention'] ?></strong></a></td>
														<td><a href="javascript:void(0);"><strong><?php echo $candidat['anneebac'] ?></strong></a></td>
														<td><?php echo $candidat['specialite'] ?></td>
                                                       
                                                      
                                                      
                                                        <td><?php echo $candidat['annee'] ?></td>
														<td>
                                                            

                                                      
                                                        <a href="modifier?code=<?php echo $candidat['code'];?>&annee=<?php echo $candidat['annee'];?>&specialite=<?php echo $candidat['specialite'];?>" class="btn btn-sm btn-info"><i class="la la-edit"></i>Modifier</a>
                                                        <a href="document?code=<?php echo $candidat['code'];?>&annee=<?php echo $candidat['annee'];?>&specialite=<?php echo $candidat['specialite'];?>" class="btn btn-sm btn-warning"><i class="la la-folder"></i>Fichiers</a>
                                                        <a href="donnees?sup=<?php echo $candidat['id'];?>" class="btn btn-sm btn-danger"><i class="la la-trash-o"></i></a>
														</td>												
													</tr>

                                                    <?php $count++;}}else{
                                                        echo "nothing";}?>
												
												</tbody>
											</table>
										</div>
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
                                            <select id="enseignant" class="" name="enseignant" required>
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
                                            <select id="ecue" class="" name="ecue" >
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
                                            <select id="classe" class="" name="classe" >
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
                        <input type="text" class="form-control" id="nature" name="nature" required>
                    </div>
                    

                       <div class="input-group mb-3">
                                            <div class="input-group-prepend">
                                                <label class="input-group-text">Année académique  </label>
                                            </div>
                                            <select id="annee" class="" name="annee" >
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
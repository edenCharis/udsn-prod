<?php 
include '../php/connexion.php';
include '../php/lib.php';

session_start();

if( $_SESSION['id'] == session_id() and  $_SESSION['role']=="scolarité"){

    if(isset($_GET['admis'])){
        $id=$_GET['admis'];

          $connexion->query("update candidat set statut_admission_concours=1 where id=$id");
          logUserAction($connexion,$_SESSION['id_user'],"modification statut d'admission candidat ",date("Y-m-d H:i:s"),$userIP,"valeur modifié : $id, statut=admis");
           
          header("location: candidature1");
         
    }

    if(isset($_GET['echoue'])){
        $id=$_GET['echoue'];

          $connexion->query("update candidat set statut_admission_concours=0 where id=$id");
          logUserAction($connexion,$_SESSION['id_user'],"modification statut d'admission candidat ",date("Y-m-d H:i:s"),$userIP,"valeur modifié : $id, statut=echoue");
           
          header("location: candidature1");
    }
    

              if(isset($_GET['statut']) and isset($_GET['code']) and isset($_GET['annee'])){



                if(getStatutPaimentCand($_GET['code'],$_GET['annee'],$connexion))
                {
                    $sql ="UPDATE candidat set statut ='".$_GET['statut']."' where code='".$_GET['code']."'";

                    if($connexion->query($sql)){
                        logUserAction($connexion,$_SESSION['id_user'],"modification statut de paiement candidat ",date("Y-m-d H:i:s"),$userIP,"valeur modifié : $code, statut=".$_GET['statut']);
          
                          header("location: ../scolarite/candidature1");
                          exit;
                    }
                }else{
                    $c = $_GET['code'];
                header("location: ../scolarite/candidature1?erreur=le candidat  $c n'a pas encore payé les frais d'inscription.");
                    exit;
                }


                
              }


              if(isset($_GET['sup']))
              {

                  $sql ="delete from candidat where id=".$_GET['sup'];
                  if($connexion->query($sql)){
                    logUserAction($connexion,$_SESSION['id_user'],"suppression d'une candidature ",date("Y-m-d H:i:s"),$userIP,"valeur supprimé : ".$_GET['id']);
          
                    header("location: ../scolarite/candidature1");
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
                            <h4>Gestion des candidatures en ligne</h4>
                        </div>
                    </div>
                    <div class="col-sm-6 p-md-0 justify-content-sm-end mt-2 mt-sm-0 d-flex">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="../scolarite/">Scolarité</a></li>
                            <li class="breadcrumb-item active"><a href="../scolarite/candidature">Candidatures</a></li>
                            
                        </ol>
                    </div>
                </div>
				<div class="row">
                <div class="col-lg-12">
						<ul class="nav nav-pills mb-3">
                        <li class="nav-item"><a href="#grid-view" data-toggle="tab" class="nav-link btn-primary ">Vue en grille</a></li>
						<li class="nav-item"><a href="#list-view" data-toggle="tab" class="nav-link btn-info ">Vue detaillée</a></li> 
                        <?php 
                              $typeEtablissement =typeEtablissement($_SESSION['lib_etab'],$connexion);
                                if($typeEtablissement == "institut" or  $typeEtablissement=="ecole"){
                        ?>
						    <li class="nav-item"><a href="#list-concours" data-toggle="tab" class="nav-link btn-success  mr-1 show active">Editer les resultats du concours</a></li>
						
                    <?php }?>
						</ul>
					</div>
					<div class="col-lg-12">
						<div class="row tab-content">
                            
							<div id="grid-view" class="tab-pane fade  col-lg-12">
								<div class="row">


                                <?php 
                                
                                   $sql ="select * from candidat  where etab='".$_SESSION['lib_etab']."' and not etat";

                                   $resultat=$connexion->query($sql);

                                   while($candidat=$resultat->fetch_assoc()){
                                
                                
                                ?>
									<div class="col-lg-4 col-md-6 col-sm-6 col-12">
										<div class="card card-profile">
											<div class="card-header justify-content-end pb-0">
												
											</div>
											<div class="card-body pt-2">
												<div class="text-center">
												
                                                   <img width="100" height="100"  src="../candidat/<?php echo $candidat['img']?>" alt="">
													
													<h3 class="mt-4 mb-1"><?php echo $candidat['nom']."   ".$candidat['prenom'];?></h3>
													<p class="text-muted"><?php echo $candidat['code'];?></p>
												<!--	<ul class="list-group mb-3 list-group-flush">
														<li class="list-group-item px-0 d-flex justify-content-between">
															<span>Specialité / Option</span><strong><?php //echo $candidat['specialite']?></strong></li>
														<li class="list-group-item px-0 d-flex justify-content-between">
															<span class="mb-0">Telephone:</span><strong><?php// echo $candidat['tel'];?></strong></li>
														<li class="list-group-item px-0 d-flex justify-content-between">
															<span class="mb-0">Date de candidature. :</span><strong><?php //echo $candidat['date_cand'];?></strong></li>
														<li class="list-group-item px-0 d-flex justify-content-between">
															<span class="mb-0">Email:</span><strong><?php// echo $candidat['email'];?></strong></li>
													</ul> -->
													<a class="btn btn-outline-primary btn-rounded mt-3 px-4" href="details?code=<?php echo $candidat['code']?>&annee=<?php echo $candidat['annee']?>">Voir fichiers</a>
												</div>
											</div>
										</div>
									</div>
                                    <?php }?>
									
								</div>
							</div>
                            <div id="list-view" class="tab-pane fade fade active show    col-lg-12">
								<div class="card">
									<div class="card-header">
										<h4 class="card-title">Liste des candidatures  </h4>
                                <div class="basic-form">
                                    <form>
                                        <div class="form-group">
                                            <div class="form-check form-check-inline">
                                                <label class="form-check-label">
                                                    <input type="checkbox" class="form-check-input" name="bac"  >Moyenne BAC
                                                </label>
                                            </div>
                                            <div class="form-check form-check-inline">
                                                <label class="form-check-label">
                                                    <input type="checkbox" class="form-check-input" name="statutpaiement" >Statut Paiement Inscription
                                                </label>
                                            </div>
                                            <div class="form-check form-check-inline">
                                                <label class="form-check-label">
                                                    <input type="submit" class="btn btn-success" value="Filtrer">
                                                </label>
                                            </div>

                                        </div>
                                    </form>
                                </div>
										<a onclick="javascript:window.print();" type="button" class="btn btn-primary">  <i class="fa fa-print"></i> Imprimer</a>
                                   </div>
								
							<div class="card-body">
										<div class="table-responsive">
											<table id="example3" class="display" style="min-width: 845px">
												<thead>
													<tr>
														<th>#</th>
														<th>Code</th>
                                                        <th>Nom(s)</th>
                                                        <th>Prénom(s)</th>
														<th>Date de naissance</th>
														<th>Lieu de naissance</th>
														<th>BAC</th>
                                                        <th>Moyenne BAC</th>
                                                        <th>Mention BAC</th>
														<th>Annee</th>
                                                        <th>Specialite</th>
                                                        <th>Statut Paiement Inscription</th>
                                                        <?php 
                                                           $typeEtablissement =typeEtablissement($_SESSION['lib_etab'],$connexion);
                                                           if($typeEtablissement == "institut" or  $typeEtablissement=="ecole"){
                                                        ?>
														<th>Statut Paiement  Concours</th>
                                                        <th>Statut Admission  Concours</th>
                                                        <?php }?>
                                                        <th>Statut Inscription</th>
                                                        <th>Année académique</th>
														<th>Action</th>
													</tr>
												</thead>
												<tbody>

                                                <?php 

                                                if(isset($_GET['bac'])){
                                                    $sql ="select * from candidat where etab='".$_SESSION['lib_etab']."' and moyenneBac >= 12 and not etat";
                                                }else if(isset($_GET['statutpaiement'])){
                                                    $sql ="select * from candidat where etab='".$_SESSION['lib_etab']."' and statut_paiement_inscription =1 and not etat";
                                                }else{
                                                    $sql ="select * from candidat where etab='".$_SESSION['lib_etab']."' and not etat";

                                                }
                                                
                                                
                                                  $resultat=$connexion->query($sql);
                                                  if($resultat){
                                                  while($candidat = $resultat->fetch_assoc()){
                                                ?>
													<tr>
														<td><img class="rounded-circle" width="35" src="images/profile/small/pic1.jpg" alt=""></td>
														<td><strong><?php echo $candidat['code'];?></strong></td>
                                                        <td><strong><?php echo getNomEtudiant($candidat['code'],$connexion,$candidat["etab"]);?></strong></td>
                                                        <td><strong><?php echo mettrePremieresLettresMajuscules(getPrenomEtudiant($candidat['code'],$connexion,$candidat["etab"]));?></strong></td>
														
														<td><?php echo $candidat['date_nais'] ?></td>
														<td><?php echo $candidat['lieu_nais'] ?></td>
														<td><a href="javascript:void(0);"><strong><?php echo $candidat['bac'] ?></strong></a></td>
                                                        <td><a href="javascript:void(0);"><strong><?php echo $candidat['moyenneBac'] ?></strong></a></td>
                                                        <td><a href="javascript:void(0);"><strong><?php echo $candidat['mention'] ?></strong></a></td>
														<td><a href="javascript:void(0);"><strong><?php echo $candidat['anneebac'] ?></strong></a></td>
														<td><?php echo $candidat['specialite'] ?></td>
                                                       
                                                        <td><?php echo( $candidat['statut_paiement']== 0 ? "Non Payé" : "Payé" ) ;?></td>
                                                        <?php 
                                                           $typeEtablissement =typeEtablissement($_SESSION['lib_etab'],$connexion);
                                                           if($typeEtablissement == "institut" or  $typeEtablissement=="ecole"){
                                                        ?>
														 <td><?php echo ( $candidat['statut_paiement_concours'] == 0 ? "Non Payé" : "Payé") ;?></td>
                                                         <td><?php echo ($candidat['statut_admission_concours'] !== null ? ($candidat['statut_admission_concours'] == 0 ?  '<a href="javascript:void()" class="badge badge-rounded badge-warning">Echoué</a>' : '<a href="javascript:void()" class="badge badge-rounded badge-success">Admis</a>') : "");?></td>
                                                        <?php }?>
                                                        <td>  <?php 
                                                           if( !verifierInscription($candidat['code'],$candidat['annee'],$connexion)){
                                                        ?>
                                                       // <?php } else{

                                                            echo "Inscrit";
                                                            }?>
                                                       </td>
                                                        <td><?php echo $candidat['annee'] ?></td>
														<td>
                                                            

                                                        <?php 
                                                           if( !verifierInscription($candidat['code'],$candidat['annee'],$connexion)){
                                                        ?>
                                                        <a href="traitement1?code=<?php echo $candidat['code'];?>&annee=<?php echo $candidat['annee'];?>&specialite=<?php echo $candidat['specialite'];?>" class="btn btn-sm btn-info"><i class="la la-graduation-cap"></i>Inscrire</a>
                                                        <a href="candidature1?sup=<?php echo $candidat['id'];?>" class="btn btn-sm btn-danger"><i class="la la-trash-o"></i></a>
                                                           <?php } else{

                                                            echo "//";
                                                            }?>



															
														</td>												
													</tr>

                                                    <?php }}else{
                                                        echo "nothing";}?>
												
												</tbody>
											</table>
										</div>
									</div>
                                </div>
                            </div>
                            <div id="list-concours" class="tab-pane  col-lg-12">
								<div class="card">
									<div class="card-header">
										<h4 class="card-title">Editions des resultats du concours  </h4>
										<a onclick="javascript:window.print();" type="button" class="btn btn-primary">  <i class="fa fa-print"></i> Imprimer</a>
									</div>
                                    <div class="card-body">
								<div id="DZ_W_Message" class="widget-message dz-scroll" style="height:350px;">
                                <?php 
                                
                                          $sql="select * from candidat where etab='".$_SESSION['lib_etab']."' and statut_paiement_concours=1";
                                          $resultat=$connexion->query($sql);
                                          while ($can =$resultat->fetch_assoc()){
                                ?>
									<div class="media mb-3">
										<img class="mr-3 rounded-circle" alt="image" width="50" src="../candidat/<?php echo $can['img'];?>">
										<div class="media-body">
											<h5><?php echo $can['nom']."   ".$can['prenom'];?><small class="text-primary">  | Code : <?php echo $can['code'];?></small></h5>
											<p class="mb-2">Spécialité : <?php echo $can['specialite']?>   | Année académique : <?php echo $can['annee']?> | Frais de concours : <?php echo ( $can['statut_paiement_concours']== 0 ?  '<a href="javascript:void()" class="badge badge-rounded badge-danger">Non Payé</a>' : '<a href="javascript:void()" class="badge badge-rounded badge-success">Payé</a>')?> </p>
                                            <?php 
                                               if($can['statut_admission_concours'] == null){
                                            ?>
                                            <a href="candidature?admis=<?php echo $can['id'];?>" class="btn btn-primary btn-sm d-inline-block px-3">Admis</a>
											<a href="candidature?echoue=<?php echo $can['id'];?>" class="btn btn-outline-danger btn-sm d-inline-block px-3">Echoué</a>
                                            <?php }else{
                                                   echo ( $can['statut_admission_concours'] == 0 ?  '<a href="javascript:void()" class="badge badge-rounded badge-warning">Echoué</a>' : '<a href="javascript:void()" class="badge badge-rounded badge-success">Admis</a>');
                                            }?>       
										</div>
									</div>
                                    <?php }?>
									
								</div>
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
                <h5 class="modal-title" id="modifierModalLabel">Modifier une classe</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <!-- Formulaire de modification -->
                <form  method="post" action="traitement1">
                    <input type="hidden" id="classeId" name="classeId">
                    <div class="form-group">
                        <label for="nouveauClasse">Classe:</label>
                        <input type="text" class="form-control" id="nouveauClasse" name="nouveauClasse" required>
                    </div>
                      
                       <div class="input-group mb-3">
                                            <div class="input-group-prepend">
                                                <label class="input-group-text">Niveau : </label>
                                            </div>
                                            <select id="nouveauniv" class="" name="nouveauniv" required>
                                                <option selected=""></option>
                                               <?php 
                                                        $sql="select * from niveau ";
                                                        $resultat=$connexion->query($sql);
                                                        while($etablissement =$resultat->fetch_assoc()){
                                                ?>
                                                 <option><?php echo str_replace("+","'",$etablissement['libelle']);?></option>
                                                 <?php }?>
                                            </select>
                       </div>
                       <div class="input-group mb-3">
                                            <div class="input-group-prepend">
                                                <label class="input-group-text">Specialité / Option : </label>
                                            </div>
                                            <select id="nouveauspec" class="" name="nouveauspec" required>
                                                <option selected=""></option>
                                               <?php 
                                                        $sql="select * from specialite where etab='".$_SESSION['lib_etab']."'";
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
                <h5 class="modal-title" id="exampleModalLabel">Enregistrement d'une Classe</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <!-- Formulaire pour enregistrer le type d'agent -->
                <form id="typeAgentForm" method="post" action="traitement1">
                    <div class="form-group">
                        <label for="classe">Libéllé Classe :</label>
                        <input type="text" class="form-control" id="classe" name="classe"  required>
                    </div>
                    <div class="input-group mb-3">
                                            <div class="input-group-prepend">
                                                <label class="input-group-text">Niveau : </label>
                                            </div>
                                            <select id="niveau" class="" name="niveau" required>
                                                <option selected=""></option>
                                               <?php 
                                                        $sql="select * from niveau ";
                                                        $resultat=$connexion->query($sql);
                                                        while($etablissement =$resultat->fetch_assoc()){
                                                ?>
                                                 <option><?php echo str_replace("+","'",$etablissement['libelle']);?></option>
                                                 <?php }?>
                                            </select>
                       </div>
                       <div class="input-group mb-3">
                                            <div class="input-group-prepend">
                                                <label class="input-group-text">Specialité / Option : </label>
                                            </div>
                                            <select id="specialite" class="" name="specialite" required>
                                                <option selected=""></option>
                                               <?php 
                                                        $sql="select * from specialite where etab='".$_SESSION['lib_etab']."'";
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
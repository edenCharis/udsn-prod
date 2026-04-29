<?php 
include '../php/connexion.php';
include '../php/lib.php';

session_start();

if( $_SESSION['id'] == session_id() and  $_SESSION['role']=="doyen"){



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
                            <h4>Liste des unités d'enseignements (U.E) </h4>
                        </div>
                    </div>
                    <div class="col-sm-6 p-md-0 justify-content-sm-end mt-2 mt-sm-0 d-flex">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="../scolarite/">Scolarité</a></li>
                            <li class="breadcrumb-item active"><a href="javascript:void(0);">Ues</a></li>
                           
                        </ol>
                    </div>
                </div>
				
				<div class="row">
					<div class="col-lg-12">
						<div class="card">
							<div class="card-header">
								<h4 class="card-title">	<a href="add-library.html" class="btn btn-primary" data-toggle="modal" data-target="#typeAgentModal">+ Ajouter</a>
								 <a href="#" class="btn btn-info" data-toggle="modal" data-target="#printModal"> <i class="fa fa-print"></i> Imprimer</a></h4>
							
							</div>
							<div class="card-body">
								<div class="table-responsive">
									<table id="example3" class="display" style="min-width: 845px">
										<thead>
											<tr>
												
												<th>N°</th>
												<th>Unité d'enseignement</th>
                                                <th>Code</th>
												<th>Semestre</th>
												<th>Volume Horaire</th>
												<th>Specialité/Option</th>
												<th>Niveau</th>
												<th>Action</th>
											</tr>
										</thead>
										<tbody>

                                          <?php 
                                           $count=1;

                                                 $sql ="select * from ue where etab='".$_SESSION['etablissement']."' order by libelle ASC";
        
                                                 $resultat =$connexion->query($sql);

                                                 while($ue=$resultat ->fetch_assoc()){
                                          
                                          ?>
											<tr>
												
												<td><?php echo $count;?></td>
												<td><?php echo str_replace("+","'", $ue['libelle']);?></td>
                                                <td><?php echo $ue['code'];?></td>
												<td><?php echo $ue['semestre'];?></td>
												<td><?php echo $ue['VH'];?></td>
												<td><?php echo str_replace("+","'",$ue['Specialite']);?></td>
												<td><?php echo str_replace("+","'",$ue['niveau']);?></td>	
												<td>

                                                
                                               <a href="modifier_ue?id=<?php echo urlencode($ue['id']); ?>&code=<?php echo urlencode ($ue['code'])?>&lib=<?php echo urlencode(str_replace("+", "'", $ue['libelle'])); ?>&sem=<?php echo urlencode($ue['semestre']); ?>&vh=<?php echo urlencode($ue['VH']); ?>&spec=<?php echo urlencode($ue['Specialite']); ?>&niv=<?php echo urlencode($ue['niveau']); ?>" 
   class="btn btn-sm btn-primary">
   <i class="la la-pencil"></i>
</a>

                                                    <a href="traitement1?supue=<?php echo $ue['id'];?>" class="btn btn-sm btn-danger"><i class="la la-trash-o"></i></a>
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
        
        <div class="modal fade" id="printModal" tabindex="-1" role="dialog" aria-labelledby="printModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="printModalLabel">Informations d'impression</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <form id="printForm">
                    <div class="modal-body">
                        <div class="form-group">
                            <label for="parcours">Parcours</label>
                            <select class="disabling-options" id="parcours" name="parcours" required>
                                <option value="">Sélectionnez un parcours</option>

                                               <?php 
                                                        $sql="select * from parcours where etab='".$_SESSION['lib_etab']."'";
                                                        $resultat=$connexion->query($sql);
                                                        while($etablissement =$resultat->fetch_assoc()){
                                                ?>
                                                 <option><?php echo str_replace("+","'",$etablissement['libelle']);?></option>
                                                 <?php }?>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="specialite">Spécialité</label>
                            <select id="specialite" class="disabling-options" name="specialite" required>
                                                <option value="">Sélectionnez une specialite</option>
                                               <?php 
                                                        $sql="select * from specialite where etab='".$_SESSION['lib_etab']."'";
                                                        $resultat=$connexion->query($sql);
                                                        while($etablissement =$resultat->fetch_assoc()){
                                                ?>
                                                 <option><?php echo str_replace("+","'",$etablissement['libelle']);?></option>
                                                 <?php }?>
                                            </select>
                        </div>
                        <div class="form-group">
                            <label for="semestre">Semestre</label>
                            <select class="form-control" id="semestre" name="semestre" required>
                                <option value="">Sélectionnez un semestre</option>
                                <?php 
                                                        $sql="select * from semestre  ";
                                                        $resultat=$connexion->query($sql);
                                                        while($etablissement =$resultat->fetch_assoc()){
                                                ?>
                                                 <option><?php echo str_replace("+","'",$etablissement['libelle']);?></option>
                                                 <?php }?>
                            </select>
                        </div>
                       
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Fermer</button>
                        <button type="submit" class="btn btn-primary">Valider</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<div class="modal" id="modifierModal" tabindex="-1" role="dialog" aria-labelledby="modifierModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modifierModalLabel">Modifier une unité d'enseignement</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <!-- Formulaire de modification -->
                <form  method="post" action="traitement1">
                    <input type="hidden" id="ueId" name="ueId">
                    <div class="form-group">
                        <label for="nouveauUe">unité d'enseignement </label>
                        <input type="text" class="form-control" id="nouveauUe" name="nouveauUe" required>
                    </div>
                    
                    <div class="input-group mb-3">
                                            <div class="input-group-prepend">
                                                <label class="input-group-text">Semestre </label>
                                            </div>
                                            <select id="nouveauSem" class="form-control form-control-lg" name="nouveauSem" required>
                                            <?php 
                                                        $sql="select * from semestre  ";
                                                        $resultat=$connexion->query($sql);
                                                        while($etablissement =$resultat->fetch_assoc()){
                                                ?>
                                                 <option><?php echo str_replace("+","'",$etablissement['libelle']);?></option>
                                                 <?php }?>>
                                               
                                            </select>
                       </div>
                       <div class="form-group">
                        <label for="vh">Volume Horaire :</label>
                        <input type="number" class="form-control" id="nouveauVH" name="nouveauVH" step="0.5" required>
                    </div>
                       <div class="input-group mb-3">
                                            <div class="input-group-prepend">
                                                <label class="input-group-text">Specialité / Option : </label>
                                            </div>
                                            <select id="nouveauSpec" class="form-control form-control-lg" name="nouveauSpec" required>
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

                       <div class="input-group mb-3">
                                            <div class="input-group-prepend">
                                                <label class="input-group-text">Niveau : </label>
                                            </div>
                                            <select id="nouveauNiv" class="form-control form-control-lg" name="nouveauNiv" required>
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
                <h5 class="modal-title" id="exampleModalLabel">Enregistrement d'une unité d'enseignement</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <!-- Formulaire pour enregistrer le type d'agent -->
                <form id="typeAgentForm" method="post" action="traitement1">
                    <div class="form-group">
                        <label for="ue">Unité d'enseignement</label>
                        <input type="text" class="form-control" id="ue" name="ue"  required>
                    </div>
                    <div class="form-group">
                        <label for="nouveauUe">CODE UE </label>
                        <input type="text" class="form-control" id="codeUE" name="codeUE" required>
                    </div>
                    <div class="input-group mb-3">
                                            <div class="input-group-prepend">
                                                <label class="input-group-text">Semestre </label>
                                            </div>
                                            <select id="semestre" class="form-control form-control-lg" name="semestre" required>
                                            <?php 
                                                        $sql="select * from semestre";
                                                        $resultat=$connexion->query($sql);
                                                        while($etablissement =$resultat->fetch_assoc()){
                                            ?>
                                                <option selected=""></option>
                                                 <option><?php echo str_replace("+","'",$etablissement['libelle']);?></option>
                                                 <?php }?>
                                               
                                            </select>
                       </div>
                       <div class="form-group">
                        <label for="vh">Volume Horaire </label>
                        <input type="number" class="form-control" id="vh" name="vh" step="0.5" required>
                    </div>
                    <div class="input-group mb-3">
                                            <div class="input-group-prepend">
                                                <label class="input-group-text">Specialité / Option </label>
                                            </div>
                                            <select id="specialite" class="form-control form-control-lg" name="specialite" required>
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
                       <div class="input-group mb-3">
                                            <div class="input-group-prepend">
                                                <label class="input-group-text">Niveau  </label>
                                            </div>
                                            <select id="niveau" class="form-control form-control-lg" name="niveau" required>
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
    // Gérer la soumission du formulaire
    document.getElementById('printForm').addEventListener('submit', function(e) {
        e.preventDefault();  // Empêche la soumission par défaut

        // Récupérer les valeurs du formulaire
        const parcours = document.getElementById('parcours').value;
        const specialite = document.getElementById('specialite').value;
        const semestre = document.getElementById('semestre').value;
        const niveau = document.getElementById('niveau').value;

        // Créer l'URL pour la page d'impression en fonction des choix
        const printUrl = `test.php?parcours=${parcours}&specialite=${specialite}&semestre=${semestre}&niveau=${niveau}`;

        // Rediriger vers la page d'impression
        window.location.href = printUrl;
    });
</script>
	
</body>
</html>

<?php 


}else{
    header("location: ../login");
}?>
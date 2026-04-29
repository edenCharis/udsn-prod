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
                            <h3>Liste des ECUES</h3>
                        </div>
                    </div>
                    <div class="col-sm-6 p-md-0 justify-content-sm-end mt-2 mt-sm-0 d-flex">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="../scolarite/">Scolarité</a></li>
                            <li class="breadcrumb-item"><a href="../scolarite/ue">UE</a></li>
                            <li class="breadcrumb-item active"><a href="javascript:void(0);">ECUES</a></li>
                           
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
										   	<th>Code ECUE</th>
												<th>Libelle ECUE</th>
												<th>VHCM</th>
												<th>VHTP</th>
												<th>VHTD</th>
												<th>VHG</th>
                                                <th>Credit</th> 
                                                
                                                <th>U.E</th>
                                                <th>Semestre</th>
                                                	<th>Specialité</th>
                                               
												<th>Action</th>
											</tr>
										</thead>
										<tbody>

                                          <?php 
                                                 $sql ="select * from ecue where etab='".$_SESSION['etablissement']."' order by libelle ASC";
                                                 $count=1;

                                                 $resultat =$connexion->query($sql);

                                                 while($ue=$resultat ->fetch_assoc()){
                                                     
                                             
                                          
                                          ?>
											<tr>
												
												<td><?php echo $count;?></td>
													<td><?php echo $ue['code_ecue'];?></td>
												<td><?php echo str_replace("+","'", $ue['libelle']);?></td>
												<td><?php echo $ue['VHCM'];?></td>
												<td><?php echo $ue['VHTP'];?></td>
												<td><?php echo $ue['VHTD'];?></td>
                                                <td><?php echo $ue['VHG'];?></td>
                                                <td><?php echo $ue['credit'];?></td>
												<td> <a href="ue.php"><?php echo str_replace("+","'",$ue['code_ue']);?></a> </td>
													<td><?php echo getUeBySem($ue["code_ue"],$connexion);?></td>	
													<td><?php echo str_replace("+","'",getSpecialiteByUE($ue['code_ue'],$connexion));?></td>	
											
                                             
												<td>

                                           <a class="btn btn-sm btn-warning" 
   href="modifier_ecue?id=<?php echo urlencode($ue['id']); ?>&ue=<?php echo urlencode($ue['code_ecue']); ?>&lib=<?php echo urlencode($ue['libelle']); ?>&vhcm=<?php echo urlencode($ue['VHCM']); ?>&vhtp=<?php echo urlencode($ue['VHTP']); ?>&vhtd=<?php echo urlencode($ue['VHTD']); ?>&cr=<?php echo urlencode($ue['credit']); ?>">
   <i class="la la-pencil"></i>
</a>

                                                    <a href="traitement1?supecue=<?php echo $ue['id'];?>" class="btn btn-sm btn-danger"><i class="la la-trash-o"></i></a>
                                                </td>												
											</tr>

                                            <?php  $count++;}?>
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
                            <label for="parcours">UE</label>
                            <select class="disabling-options" id="ue" name="ue" >
                                <option value="">Sélectionnez une ue</option>

                                               <?php 
                                                        $sql="select * from ue where etab='".$_SESSION['etablissement']."'";
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
                            <select class="form-control" id="semestre" name="semestre" >
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

<div class="modal fade" id="typeAgentModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalLabel">Enregistrement d'un ECUE</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <!-- Formulaire pour enregistrer le type d'agent -->
                <form id="typeAgentForm" method="post" action="traitement1">
                      <div class="form-group">
                        <label for="ecue">Code ECUE :</label>
                        <input type="text" class="form-control" id="code" name="code"  required>
                    </div>
                    <div class="form-group">
                        <label for="ecue">Libéllé ECUE :</label>
                        <input type="text" class="form-control" id="ecue" name="ecue"  required>
                    </div>
                       <div class="form-group">
                        <label for="vhcm">VHCM :</label>
                        <input type="number" class="form-control" id="vhcm" step="0.01" name="vhcm" required>
                    </div>
                   

                    <div class="form-group">
                        <label for="vhtp">VHTP :</label>
                        <input type="number" class="form-control" id="vhtp"  step="0.01" name="vhtp" required>
                    </div>
                    <div class="form-group">
                        <label for="vhtd">VHTD:</label>
                        <input type="number" class="form-control" id="vhtd" step="0.01"  name="vhtd" required>
                    </div>
                    <div class="form-group">
                        <label for="credit">Credit:</label>
                        <input type="number" class="form-control" id="credit" step="0.01" name="credit" required>
                    </div>
                    <div class="input-group mb-3">
                                            <div class="input-group-prepend">
                                                <label class="input-group-text">UE : </label>
                                            </div>
                                            <select id="ue" class="disabling-options" name="ue" required>
                                                <option selected=""></option>
                                               <?php 
                                                        $sql="select * from ue where etab='".$_SESSION['etablissement']."'";
                                                        $resultat=$connexion->query($sql);
                                                        while($etablissement =$resultat->fetch_assoc()){
                                                ?>
                                                 <option value="<?php echo str_replace("+","'",$etablissement["code"]) ?>"><?php echo str_replace("+","'",$etablissement['code']."  ( ".$etablissement["niveau"]."-".$etablissement["semestre"].")");?></option>
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
    // Gérer la soumission du formulaire
    document.getElementById('printForm').addEventListener('submit', function(e) {
        e.preventDefault();  // Empêche la soumission par défaut

        // Récupérer les valeurs du formulaire
        const ue = document.getElementById('ue').value;
        const specialite = document.getElementById('specialite').value;
        const semestre = document.getElementById('semestre').value;


        // Créer l'URL pour la page d'impression en fonction des choix
        const printUrl = `imprimer_ecue.php?ue=${ue}&specialite=${specialite}&semestre=${semestre}`;

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
<?php 
include '../php/connexion.php';
include '../php/lib.php';

session_start();

if($_SESSION['id'] == session_id() and  $_SESSION['role']=="caisse"){



?>


<!DOCTYPE html>
<html lang="en">

<head>
	
	<meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title> Comptabilité de <?php echo $_SESSION['univ'];?> </title> <!-- Favicon icon -->
     <!-- Favicon icon -->
    <link rel="icon" type="image/png" sizes="16x16" href="../images/favicon.png">
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
                            <h4>Vue sur les heures effectuées par les enseignants</h4>
                        </div>
                    </div>
                    <div class="col-sm-6 p-md-0 justify-content-sm-end mt-2 mt-sm-0 d-flex">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="index">Caisse</a></li>
                            <li class="breadcrumb-item active"><a href="javascript:void(0);">Vue</a></li>
                             </ol>
                    </div>
                </div>
				
                <div class="row">
					<div class="col-lg-12">
						<div class="card">
                            <div class="card-header">
								<h4 class="card-title">Details</h4>
								
							</div>
                            <div class="card-body">
                                <div class="table-responsive">
                                <table id="example3" class="display" style="min-width: 845px">
                                        <thead>
                                            <tr>
                                                <th>Code Enseignant</th>
                                                 <th>Enseignant</th>
                                               <th>Taux Horaire</th>
                                                <th>Mois</th>
                                                <th>Heure effectuées</th>
                                                <th>Ecue</th>
                                                <th>classe</th>
                                                <th>Année Scolaire</th>
                                                <th>Etablissement</th>
                                              
                                              
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php 
                                              if(isset($_GET['search'])){
                                               $enseignant = $_GET['search'];

                                                $sql ="select * from cumuleHeure where  enseignant='".$enseignant."'";

                                                
                                              }else{


                                                $sql ="select * from cumuleHeure";


                                              }
                                              
                                                $resultat =$connexion->query($sql);
                                                if(mysqli_num_rows($resultat) > 0){
                                                while($frais = $resultat->fetch_assoc()){
                                            ?>
                                            <tr>
                                              <td><?php echo $frais['enseignant'];?></td>
                                           
                                                <td><?php echo strtoupper(str_replace("+","'",getNomPrenomEnseignant($frais['enseignant'],$connexion))); ?></td>
                                                <td><?php echo getThByEns($frais['enseignant'],$connexion,$frais['etab']);?></td>
                                                <td><?php echo getNomMois($frais['mois']);?></td>
                                                <td><?php echo $frais['total_heures']?></td>
                                                <td><?php echo $frais['ecue']?></td>
                                                <td><?php echo $frais['classe']?></td>
                                                <td><?php echo $frais['annee']?></td>
                                                <td><?php echo $frais['etab']?></td>
                                               
                                             </td>
                                              
                                            </tr>
                                          <?php } }?>
                                        

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


<div class="modal fade" id="typeAgentModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalLabel">Formulaire de paiement</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <!-- Formulaire pour enregistrer le type d'agent -->
                <form id="typeAgentForm" method="post" action="traitement">
                   
                    <div class="input-group mb-3">
                                            <div class="input-group-prepend">
                                                <label class="input-group-text">Etudiant  </label>
                                            </div>
                                            <select id="inscription" class="form-control form-control-lg" name="inscription" required>
                                                <option selected=""></option>
                                               <?php 
                                                        $sql="select * from inscription where etab='".$_SESSION['etablissement']."' and statut_paiement=0";
                                                        $resultat=$connexion->query($sql);
                                                        while($c =$resultat->fetch_assoc()){
                                                ?>
                                                 <option value="<?php echo $c['id'];?>"><?php echo str_replace("+","'",obtenirNomPrenom($c['candidat'],$c['annee'],$connexion));?></option>
                                                 <?php }?>
                                            </select>
                       </div>
                       <div class="input-group mb-3">
                                            <div class="input-group-prepend">
                                                <label class="input-group-text">Type de frais  </label>
                                            </div>
                                            <select id="ecue" class="form-control form-control-lg" name="type" required>
                                                <option selected=""></option>
                                               <?php 
                                                        $sql="select * from type_frais ";
                                                        $resultat=$connexion->query($sql);
                                                        while($etablissement =$resultat->fetch_assoc()){
                                                ?>
                                                 <option><?php echo str_replace("+","'",$etablissement['libelle']);?></option>
                                                 <?php }?>
                                            </select>
                       </div>
                       <div class="input-group mb-3">
                                            <div class="input-group-prepend">
                                                <label class="input-group-text">Année scolaire  </label>
                                            </div>
                                            <select id="ecue" class="form-control form-control-lg" name="annee" required>
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
                       <div class="form-group">
                        <label for="montant">Montant </label>
                        <input type="number"  min="0" max="1000000" class="form-control" id="montant" name="montant" required>
                       </div>
                       <div class="form-group">
                        <label for="datep">Date </label>
                        <input type="date"  class="form-control" id="datep" name="datep" required>
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
    <script src="../js/dlabnav-init.js"></script>
		
    <!-- Chart Morris plugin files -->
   
		

		<!-- Demo scripts -->
    <script src="../js/dashboard/dashboard-1.js"></script>
	
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
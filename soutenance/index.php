<?php 
include '../php/connexion.php';
include '../php/lib.php';

session_start();

if( $_SESSION['id'] == session_id() and  $_SESSION['role']=="soutenance"){

   /* if(isset($_GET['sup'])){

        $connexion->query("delete from frais where id=".$_GET['sup']);

        $id =$_GET['sup'];
        $userIP = $_SERVER['REMOTE_ADDR'];

            logUserAction($connexion,$_SESSION['id_user'],"suppression d'un frais ",date("Y-m-d H:i:s"),$userIP,"valeur supprimé : $id");
           

        header("location: index");
    }*/

?>


<!DOCTYPE html>
<html lang="en">

<head>
	
	<meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title><?php echo $_SESSION['univ'];?> - Scolarité de  <?php echo $_SESSION['etablissement'];?> </title>
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

        <!--**********************************
            Nav header start
        ***********************************-->
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
                            <h4>Gestions des procès verbaux de soutenance</h4>
                        </div>
                    </div>
                    <div class="col-sm-6 p-md-0 justify-content-sm-end mt-2 mt-sm-0 d-flex">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="index">Soutenance</a></li>
                            <li class="breadcrumb-item active"><a href="javascript:void(0);">Gestion des PV</a></li>
                             </ol>
                    </div>
                </div>
				
                <div class="row">
					<div class="col-lg-12">
						<div class="card">
                            <div class="card-header">
								<h4 class="card-title">Soutenance</h4>
								<a href="#" class="btn btn-primary" data-toggle="modal" data-target="#typeAgentModal">+ Nouveau</a>
							</div>
                            <div class="card-body">
                                <div class="table-responsive">
                                <table id="example3" class="display" style="min-width: 845px">
                                        <thead>
                                            <tr>
                                                
                                              
                                                <th>Numéro Soutenance</th>
                                                <th>Date</th>
                                                <th>Heure debut </th>
                                                <th>Heure fin</th>
                                                <th>Impetrant</th>
                                                <th>Specialité</th>
                                                <th>Titre du sujet</th>
                                                <th>Promotion</th>
                                                <th>Note</th>
                                                <th>Mention</th>
                                                <th>Année académique</th>
                                                <th>Action</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php 
                                            
                                                $sql ="select * from soutenance where etab='".$_SESSION['etablissement']."'";

                                                $resultat =$connexion->query($sql);
                                                while($s = $resultat->fetch_assoc()){
                                            ?>
                                            <tr>
                                                <td><?php echo $s['code'];?></td>
                                                <td><?php echo $s['date_soutenance'];?></td>
                                                <td><?php echo $s['heure_debut'];?></td>
                                                <td><?php echo $s['heure_fin'];?></td>
                                               
                                                <td><?php echo obtenirNomPrenom(getNomImpetrant($s['impetrant'],$connexion),$s['derniere_annee'],$connexion);?></td>
                                                <td><?php echo $s['specialite']?></td>
                                                <td><?php echo $s['theme']?></td>
                                                <td><?php echo $s['derniere_annee'];?></td>
                                                <td><?php echo $s['note'];?></td>
                                                <td><?php echo $s['mention']?></td>
                                                <td><?php echo $s['annee_acad'];?></td>
                                                <td>
                                                <a href="jury?soutenance=<?php echo $s['code'];?>" class="btn btn-sm btn-info"><i class="la la-eye"></i> Jury</a>
                                                <a href="evaluation?soutenance=<?php echo $s['code'];?>" class="btn btn-sm btn-warning"><i class="la la-edit"></i> Evaluer</a>
                                                   <a href="traitement?sup=<?php echo $s['id'];?>" class="btn btn-sm btn-danger"><i class="la la-trash-o"></i></a>
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


<div class="modal fade bd-example-modal-lg" id="typeAgentModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title text-center" id="exampleModalLabel">FORMULAIRE D'ENREGISTREMENT D'UNE SOUTENANCE</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                 <form id="typeAgentForm" method="post" action="traitement">
                  <div class="row">
                            <div class="col-md-4">
                                        <div class="form-group">
                                            <label for="matricule">Matricule </label>
                                            <input type="text"class="form-control  " id="matricule" name="matricule" required>
                                        </div>
                                       
                                        <div class="form-group">
                                        <label for="prenom">Prénom(s) </label>
                                            <input type="text"class="form-control  " id="prenom" name="prenom" required>
                                        </div>
                                        <div class="form-group">
                                        <label for="datenaissance">Date de Naissance </label>
                                            <input type="date"class="form-control  " id="datenaissance" name="datenaissance" required>
                                        </div>

                                        <div class="form-group">
                                        <label for="specialite">Specialité </label>
                                            <select class="form-control  " name="specialite" required>
                                                    <option value=""></option>
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
                                        <label for="cycle">Cycle </label>
                                        <select id="cycle" class="form-control  " name="cycle" required>
                                               <option selected=""></option>
                                             <option value="licence">Licence</option>
                                             <option value="master">Master</option>
                                             <option value="doctorat">Doctorat</option>
                                           </select>
                                        </div>
                                        <div class="form-group">
                                            <label for="theme">Titre du sujet </label>
                                            <input type="text"class="form-control  " id="theme" name="theme" required>
                                         </div>
                                        <div class="form-group">
                                            <label for="nouveauClasse">Heure debut </label>
                                            <input type="time" class="form-control" id="debut" name="debut" required>
                                         </div>
                                         <div class="form-group">
                                           <label for="note">Note de la soutenance </label>
                                           <input type="number" max="20" min="0" step="0.25"  class="form-control" id="note" name="note" required>
                                     </div>
                                         
                                        
                         </div>
                     <div class="col-md-4">
                                        <div class="form-group">
                                           <label for="nom">Nom(s) </label>
                                            <input type="text"class="form-control  " id="nom" name="nom" required>
                                        </div>
                                        <div class="form-group">
                                        <label for="lieunaissance">Lieu  de Naissance </label>
                                            <input type="text"class="form-control  " id="lieunaissance" name="lieunaissance" required>
                                        </div>
                                        <div class="form-group">
                                        <label for="departement">Parcours  </label>
                                        <select class="form-control  " name="departement" required>
                                                    <option value=""></option>
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
                                           <label for="niveau">Niveau </label>
                                           <select class="form-control  " name="niveau" required>
                                                    <option value=""></option>
                                               <?php 
                                                        $sql="select * from niveau where libelle='Troisième année' ";
                                                        $resultat=$connexion->query($sql);
                                                        while($etablissement =$resultat->fetch_assoc()){
                                                ?>
                                                 <option><?php echo str_replace("+","'",$etablissement['libelle']);?></option>
                                                 <?php }?>
                                                   
												</select>
                                     </div>
                                       
                                     
                                     <div class="form-group">
												<label class="form-label">Promotion</label>
												<select class="form-control  " name="annee" required>
                                                    <option value=""></option>
                                               <?php 
                                                        $sql="select * from annee  ";
                                                        $resultat=$connexion->query($sql);
                                                        while($etablissement =$resultat->fetch_assoc()){
                                                ?>
                                                 <option><?php echo str_replace("+","'",$etablissement['libelle']);?></option>
                                                 <?php }?>
                                                   
												</select>
										
									</div>
                                    <div class="form-group">
                                           <label for="dm">Directeur de Memoire </label>
                                           <input type="text"  class="form-control  "id="dm" name="dm" required>
                                     </div>
                                     <div class="form-group">
                                                   <label for="nouveauClasse">Heure fin </label>
                                                   <input type="time" class="form-control" id="fin" name="fin" required>
                                        </div>
                                    
                                     <div class="form-group">
                                        <label for="mention">Mention </label>
                                        <select id="mention" class="form-control  " name="mention" required>
                                             
                                               <option selected=""></option>
                                             <option value="Passable">Passable</option>
                                             <option value="A bien">A Bien</option>
                                             <option value="Bien">Bien</option>
                                             <option value="Très Bien">Très Bien</option>
                                             <option value="Excelllent">Excellent</option>
                                           </select>
                                    </div>
                                     
                                      
                     </div> 
                     <div class="col-md-4">
                                        <div class="form-group">
                                              <label for="president">President </label>
                                               <input type="text"  class="form-control" id="president" name="president" required>
                                      </div>
                                      
                                     <div class="form-group">
                                           <label for="rapp_int">Rapporteur interne </label>
                                           <input type="text"  class="form-control" id="rapp_int" name="rapp_int" required>
                                     </div>
                                     <div class="form-group">
                                           <label for="examin">Examinateur </label>
                                           <input type="text"  class="form-control" id="examin" name="examin" required>
                                     </div>
                                     <div class="form-group">
                                           <label for="autre">Autre </label>
                                           <input type="text"  class="form-control" id="autre" name="autre" required>
                                     </div>
                                  
                                      <div class="form-group">
                                           <label for="rapp_ext">Rapporteur externe </label>
                                           <input type="text"  class="form-control" id="rapp_ext" name="rapp_ext" required>
                                     </div>
                                     <div class="form-group">
												<label class="form-label">Année académique</label>
												<select class="form-control  " name="annee_acad" required>
                                                    <option value=""></option>
                                               <?php 
                                                        $sql="select * from annee  ";
                                                        $resultat=$connexion->query($sql);
                                                        while($etablissement =$resultat->fetch_assoc()){
                                                ?>
                                                 <option><?php echo str_replace("+","'",$etablissement['libelle']);?></option>
                                                 <?php }?>
                                                   
												</select>
										
									</div>
                                    <div class="form-group">
                                           <label for="datep">Date </label>
                                           <input type="date"  class="form-control" id="datep" name="datep" required>
                                     </div> 
                     </div>
                     <div class="col-md-12 text-center">
                     <button type="submit" class="btn btn-lg  btn-success">SAUVEGARDER</button>
                       <button type="button" data-dismiss="modal" class="btn btn-lg  btn-danger">ANNULER</button>
                     </div>
                 </div>
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
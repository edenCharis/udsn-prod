<?php 
include '../php/connexion.php';
include '../php/lib.php';

session_start();

if( $_SESSION['id'] == session_id() and  $_SESSION['role']=="doyen"){


    if(isset($_GET['statut']) and isset($_GET['id'])){

        $id= $_GET['id'];
    
        $sql ="UPDATE utilisateur set statut='".$_GET['statut']."' where id=$id";
    
        if($connexion->query($sql)){
    
            $userIP = $_SERVER['REMOTE_ADDR'];
    
            logUserAction($connexion,$_SESSION['id_user'],"Mise à jour statut d'un utilisateur",date("Y-m-d H:i:s"),$userIP," utilisateur : $id; valeur mise à jour : '".$_GET['statut']);
    
            header("location: anonymat?sucess=opération effectuée avec succèss");
    
    
        }else{
            header("location: anonymat?erreur=$connexion->error");
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
                            <h4>Créations des profils d'utilisateur  <i class="la la-user"></i></h4>
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
										<h4 class="card-title">Liste des utilisateurs </h4>
										<a href="add-student.html" class="btn btn-primary" data-toggle="modal" data-target="#typeAgentModal">+ Ajouter</a>
									</div>
									<div class="card-body">
										<div class="table-responsive">
											<table id="example3" class="display" style="min-width: 845px">
												<thead>
													<tr>
                                                        <th>photo</th>
														
														<th>Nom(s) et Prénom(s)</th>
                                                        <th>ECUE</th>
                                                        <th>Classe</th>
                                                        <th>Parcours</th>
                                                        <th>Semestre</th>
                                                        <th>Examen</th>
                                                        <th>Login</th>
                                                        <th>Mot de passe</th>
														<th>Role</th>
                                                        <th>Année universitaire</th>
                                                   
														
														<th>Action</th>
													</tr>
												</thead>
												<tbody>
                                                    <?php 
                                                      $sql ="select * from utilisateur where etab='".$_SESSION['etablissement']."' and (role ='anonymat' or role='inscription' or role='gesnote' or role='pvd') ";
                                                      $resultat = $connexion->query($sql);

                                                      while($type = $resultat->fetch_assoc()){
                                                    ?>
													<tr>
                                                        <td><img class="rounded-circle" width="50"  src="<?php echo $type['img'];?>" alt=""></td>
											
														
														<td><?php echo  str_replace("+","'", $type['nom']);?></td>
                                                        	<td><?php echo ( $type ["ecue"] == null) ? "null": str_replace("+","'", $type['ecue']);?></td>
                                                            <td><?php echo   ( $type ["classe"] == null) ? "null":  str_replace("+","'", $type['classe']);?></td>
                                                            <td><?php echo  ( $type ["parcours"] == null) ? "null":str_replace("+","'", $type['parcours']);?></td>
                                                            <td><?php echo  ( $type ["semestre"] == null) ? "null":str_replace("+","'", $type['semestre']);?></td>
                                                            <td><?php echo ( $type ["examen"] == null) ? "null": str_replace("+","'", $type['examen']);?></td>
														<td><a href="javascript:void(0);"><?php echo str_replace("+","'",$type['login']);?></td>
                                                        <td>************</td>
                                                        <td><?php echo str_replace("+","'",$type['role']);?></td>
                                                        <td><?php echo str_replace("+","'",$type['annee']);?></td>
														<td>

                                                              <?php 
                                                                  if($type['statut'] == "O"){
                                                              ?>
                                                            <a  href="anonymat?statut=N&id=<?php echo $type['id'];?>" class="btn btn-info" ><i class="la la-pencil"></i>Desactiver</a>

                                                            <?php }else{?>
                                                                <a href="anonymat?statut=O&id=<?php echo $type['id'];?>" class="btn btn-success" ><i class="la la-pencil"></i>Activer</a>
                                                                <?php }  ?>


															<a class="btn btn-danger eden" data-id="<?php echo $type['id']; ?>"  data-toggle="modal" data-target="#delete_employee" ><i class="la la-trash-o"></i>Supprimer</a>
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

 <div class="modal fade" id="typeAgentModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalLabel">Enregistrement d'une nouvel utilisateur</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <!-- Formulaire pour enregistrer le type d'agent -->
                <form id="typeAgentForm" method="post" action="traitement5" enctype="multipart/form-data">
                <div class="input-group mb-3">
                                            <div class="custom-file">
                                                <input type="file" class="custom-file-input" name="img">
                                                <label class="custom-file-label">Photo</label>
                                            </div>
                                            <div class="input-group-append">
                                                <span class="input-group-text">Importer</span>
                                            </div>
                                        </div>
                     
                    <label for="nom">Agent</label>
                    <select id="classeInput" class="disabling-options" name="nom" >
                       
                                                <option selected=""></option>
                                               <?php 
                                                        $sql="select * from enseignant where etab='".$_SESSION["etablissement"]."'";
                                                        $resultat=$connexion->query($sql);
                                                        while($etablissement =$resultat->fetch_assoc()){
                                                ?>
                                                 <option><?php echo str_replace("+","'",$etablissement['nom']." ".$etablissement["prenom"]);?></option>
                                               <?php }?>
                                            </select>
                    <div class="form-group">
                        <label for="login">Login </label>
                        <input type="text" class="form-control" id="login" name="login" placeholder="Entrez le login (nom de connexion) " required>
                    </div>

                    <div class="form-group">
                        <label for="mdp">Mot de passe </label>
                        <input type="text" class="form-control" id="mdp" name="mdp" placeholder="Entrez le Mot de passe " required>
                    </div>
                    <div class="input-group mb-3">
                    <label for="classeCheck">Voulez-vous préciser la classe ?</label>
  <input type="checkbox" id="classeCheck" onchange="toggleClasseInput()">
  <br>
  <div id="classeInputContainer" style="display:none;">
  

    <select id="classeInput" class="" name="classe" >
                                                <option selected=""></option>
                                               <?php 
                                                        $sql="select * from classe where etab='".$_SESSION["etablissement"]."'";
                                                        $resultat=$connexion->query($sql);
                                                        while($etablissement =$resultat->fetch_assoc()){
                                                ?>
                                                 <option value="<?php echo $etablissement['libelle'];?>"><?php echo str_replace("+","'",$etablissement['libelle']);?></option>
                                                 <?php }?>
                                            </select>
  </div>
  <br>
                    </div>
                    <div class="input-group mb-3">
                    <label for="classeCheck">Voulez-vous préciser l'ecue ?</label>
  <input type="checkbox" id="classeCheck1" onchange="toggleClasseInput()">
  <br>
  <div id="classeInputContainer1" style="display:none;">
  

    <select id="classeInput1" class="" name="ecue" >
                                                <option selected=""></option>
                                               <?php 
                                                        $sql="select * from ecue where etab='".$_SESSION["etablissement"]."'";
                                                        $resultat=$connexion->query($sql);
                                                        while($etablissement =$resultat->fetch_assoc()){
                                                ?>
                                                 <option value="<?php echo $etablissement['libelle'];?>"><?php echo str_replace("+","'",$etablissement['libelle']);?></option>
                                                 <?php }?>
                                            </select>
  </div>
  <br>
                    </div>
                   
                    <div class="input-group mb-3">
                                            <div class="input-group-prepend">
                                                <label class="input-group-text">Role  </label>
                                            </div>
                                            <select id="type" class="form-control form-lg" name="role" required>
                                                <option selected=""></option>
                                                <option value="anonymat">Agent d'anonymat </option>
                                                <option value="inscription">Agent d'inscription </option>
                                                <option value="gesnote">Gestionnaire de notes </option>
                                                <option value="pvd">Président de jury de délibération </option>
                                                <option value="suivi">Suivi d'avancement des cours </option>
                                                   
                                            </select>
                       </div>
                       <div class="input-group mb-3">
                       <div id="champsSaisie" style="display: none;">
  <label for="parcours">Parcours :</label>
  <select id="parcours" class="" name="parcours" >
                                                <option selected=""></option>
                                               <?php 
                                                        $sql="select * from parcours where etab='".$_SESSION["lib_etab"]."'";
                                                        $resultat=$connexion->query($sql);
                                                        while($etablissement =$resultat->fetch_assoc()){
                                                ?>
                                                 <option value="<?php echo $etablissement['libelle'];?>"><?php echo $etablissement['libelle'];?></option>
                                                 <?php }?>
                                            </select><br>
  <label for="semestre">Semestre :</label>
  <select id="semestre" class="" name="semestre" >
                                                <option selected=""></option>
                                               <?php 
                                                        $sql="select * from semestre ";
                                                        $resultat=$connexion->query($sql);
                                                        while($etablissement =$resultat->fetch_assoc()){
                                                ?>
                                                 <option value="<?php echo $etablissement['libelle'];?>"><?php echo $etablissement['libelle'];?></option>
                                                 <?php }?>
                                            </select><br>
  <label for="typeExamen">Type d'examen :</label>
  <select id="typeExamen" name="examen">
    <option value=""></option>
    <option value="ordinaire">Ordinaire</option>
    <option value="rattrapage">Rattrapage</option>
  </select>
  <div class="form-group">
                        <label for="debut">Début délibération </label>
                        <input type="date" class="form-control" id="debut" name="debut"  >
                    </div>
                    <div class="form-group">
                        <label for="fin">Fin délibération </label>
                        <input type="date" class="form-control" id="fin" name="fin"  >
                    </div>
</div>
                      </div>
                       <div class="input-group mb-3">
                                            <div class="input-group-prepend">
                                                <label class="input-group-text">Année acadamique  </label>
                                            </div>
                                            <select id="annee" class="" name="annee" required>
                                                <option selected=""></option>
                                               <?php 
                                                        $sql="select * from annee ";
                                                        $resultat=$connexion->query($sql);
                                                        while($etablissement =$resultat->fetch_assoc()){
                                                ?>
                                                 <option value="<?php echo $etablissement['libelle'];?>"><?php echo $etablissement['libelle'];?></option>
                                                 <?php }?>
                                            </select>
                       </div>
                    <button type="submit"    class="btn btn-primary">Enregistrer</button>
                </form>
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
<div id="delete_employee" class="modal" role="dialog">
<div class="modal-dialog modal-dialog-centered">
<div class="modal-content modal-md">
<div class="modal-header">
<h4 class="modal-title">Suppression d'un utilisateur </h4>
</div>
<div class="modal-body">
    <p>la suppression de ce compte d'utilisateur entrainera aussi la suppression de toutes les informations liées a ce compte.</p>
<p>Voules-vous  vraiment supprimer cet utilisateur ? </p>
<button type="submit" id='lien' class="btn btn-danger">supprimer</button>
<a href="" class="btn btn-primary" data-dismiss="modal">annuler</a>
<div class="m-t-20">
</div>
</div>
</div>
</div>
</div>
</div>
    <!--**********************************
        Main wrapper end
    ***********************************-->
    <div class="footer">
            <div class="copyright">
                <p>Copyright © Designed &amp; Developpé  par <a href="htpps:/www.cet-up.com" target="_blank">CETUP</a> 2023</p>
            </div>
        </div>
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
function toggleClasseInput() {
  var classeInputContainer = document.getElementById("classeInputContainer");
  var classeCheck = document.getElementById("classeCheck");
  
  // Si la case à cocher est cochée, afficher le champ pour la classe, sinon le cacher
  if (classeCheck.checked) {
    classeInputContainer.style.display = "block";
  } else {
    classeInputContainer.style.display = "none";
  }

  var classeInputContainer = document.getElementById("classeInputContainer1");

  var classeCheck = document.getElementById("classeCheck1");
  
  // Si la case à cocher est cochée, afficher le champ pour la classe, sinon le cacher
  if (classeCheck.checked) {
    classeInputContainer.style.display = "block";
  } else {
    classeInputContainer.style.display = "none";
  }
}
</script>

<script>
    document.getElementById("type").addEventListener("change", function() {
  var role = this.value;
  var champsSaisie = document.getElementById("champsSaisie");

  if (role === "pvd") {
    champsSaisie.style.display = "block";
  } else {
    champsSaisie.style.display = "none";
  }
});

</script>

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

<script>

	
		var liste= document.getElementsByClassName('btn btn-danger eden');

		for(var i=0; i < liste.length; i++)
		{
			liste[i].addEventListener('click',function(event){
				var id=this.getAttribute('data-id');

				document.getElementById('lien').addEventListener('click',function(){
                       window.location.href='traitement5?supcompte='+id ;
				})
			})

		}
			
		
	
</script>
	
</body>
</html>

<?php 


}else{
    header("location: ../login");
}?>
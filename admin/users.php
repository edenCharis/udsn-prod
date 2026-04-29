<?php 
include '../php/connexion.php';
include '../php/lib.php';


session_start();

if( $_SESSION['id'] == session_id() and isset($_SESSION['univ'])){

?>

<!DOCTYPE html>
<html lang="en">

<head>
	
<meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title><?php echo  $_SESSION['univ'];?> - Administrateur </title>
    <!-- Favicon icon -->
    <link rel="icon" type="image/png" sizes="16x16" href="../administrateur/<?php echo  $_SESSION['logo_univ']?>">
   
    <link rel="stylesheet" href="../vendor/jqvmap/css/jqvmap.min.css">
	<link rel="stylesheet" href="../vendor/chartist/css/chartist.min.css">
	<!-- Summernote -->
    <link href="../vendor/summernote/summernote.css" rel="stylesheet">
	<link rel="stylesheet" href="../vendor/bootstrap-select/dist/css/bootstrap-select.min.css">
    <link rel="stylesheet" href="../css/style.css">
    <link rel="stylesheet" href="../css/skin-3.css">

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
        <?php include "header.php" ;?>
        <!--**********************************
            Header end ti-comment-alt
        ***********************************-->

        <!--**********************************
            Sidebar start
        ***********************************-->
        <?php 
         include 'nav.php';
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
                                                        <th>Login</th>
                                                        <th>Mot de passe</th>
														<th>Role</th>
                                                        <th>Année académqiue</th>
                                                        <th>Etablissement</th>
														
														<th>Action</th>
													</tr>
												</thead>
												<tbody>
                                                    <?php 
                                                      $sql ="select * from utilisateur where  role in ('enseignant','candidat')";
                                                      $resultat = $connexion->query($sql);

                                                      while($type = $resultat->fetch_assoc()){
                                                    ?>
													<tr>
                                                        <td><img class="rounded-circle" width="50"  src="<?php echo $type['img'];?>" alt=""></td>
											
														
														<td><?php echo  str_replace("+","'", $type['nom']);?></td>
														<td><a href="javascript:void(0);"><?php echo str_replace("+","'",$type['login']);?></td>
                                                        <td>************</td>
                                                        <td><?php echo str_replace("+","'",$type['role']);?></td>
                                                        <td><?php echo str_replace("+","'",$type['annee']);?></td>
                                                          <td><a href="etablissement"><?php $etab =  str_replace("+","'",$type['etab']); echo $etab;?></td>
<td>
    <?php if($type['statut'] == "O"){ ?>
        <a href="traitement2?statut=N&id=<?php echo $type['id'];?>" class="btn btn-info">
            <i class="la la-pencil"></i>Desactiver
        </a>
    <?php }else{ ?>
        <a href="traitement2?statut=O&id=<?php echo $type['id'];?>" class="btn btn-success">
            <i class="la la-pencil"></i>Activer
        </a>
    <?php } ?>
    
    <!-- ADD THIS EDIT BUTTON -->
    <a class="btn btn-warning edit-user" 
       data-id="<?php echo $type['id']; ?>"
       data-nom="<?php echo str_replace('+', '\'', $type['nom']); ?>"
       data-login="<?php echo str_replace('+', '\'', $type['login']); ?>"
       data-role="<?php echo str_replace('+', '\'', $type['role']); ?>"
       data-etab="<?php echo str_replace('+', '\'', $type['etab']); ?>"
       data-annee="<?php echo str_replace('+', '\'', $type['annee']); ?>"
       data-img="<?php echo $type['img']; ?>"
       data-toggle="modal" 
       data-target="#editUserModal">
        <i class="la la-edit"></i>Modifier
    </a>

    <a class="btn btn-danger eden" data-id="<?php echo $type['id']; ?>" 
       data-toggle="modal" data-target="#delete_employee">
        <i class="la la-trash-o"></i>Supprimer
    </a>
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
<!-- Edit User Modal -->
<div class="modal fade" id="editUserModal" tabindex="-1" role="dialog" aria-labelledby="editUserModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editUserModalLabel">Modifier l'utilisateur</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form id="editUserForm" method="post" action="traitement2" enctype="multipart/form-data">
                    <input type="hidden" id="edit_user_id" name="id">
                    
                    <!-- Current Photo Display -->
                    <div class="form-group text-center">
                        <img id="current_photo" src="" alt="Photo actuelle" class="rounded-circle" width="100" height="100">
                    </div>
                    
                    <!-- Photo Upload -->
                    <div class="input-group mb-3">
                        <div class="custom-file">
                            <input type="file" class="custom-file-input" name="img" id="edit_img">
                            <label class="custom-file-label">Changer la photo</label>
                        </div>
                        <div class="input-group-append">
                            <span class="input-group-text">Importer</span>
                        </div>
                    </div>
                    
                    <!-- Name -->
                    <div class="form-group">
                        <label for="edit_nom">Nom(s) et prénom(s) :</label>
                        <input type="text" class="form-control" id="edit_nom" name="nom" required>
                    </div>
                    
                    <!-- Login -->
                    <div class="form-group">
                        <label for="edit_login">Login</label>
                        <input type="text" class="form-control" id="edit_login" name="login" required>
                    </div>

                    <!-- Password -->
                    <div class="form-group">
                        <label for="edit_mdp">Nouveau mot de passe (laisser vide pour ne pas changer)</label>
                        <input type="text" class="form-control" id="edit_mdp" name="mdp" placeholder="Laisser vide pour garder l'ancien">
                    </div>
                    
                    <!-- Role -->
                    <div class="input-group mb-3">
                        <div class="input-group-prepend">
                            <label class="input-group-text">Role</label>
                        </div>
                        <select id="edit_role" class="form-control" name="role" required>
                            <option value="">Sélectionner un rôle</option>
                            <option value="scolarité">Scolarité</option>
                            <option value="caisse">Caisse</option>
                            <option value="cours">Cours</option>
                            <option value="soutenance">Gestionnaire des PV de soutenance</option>
                            <option value="gesnote">Gestionnaire des notes</option>
                            <option value="anonymat">Agent d'anonymat</option>
                            <option value="inscription">Agent Inscription</option>
                            <option value="daarhspe">Service du personnel enseignant</option>
                            <option value="suivi">Suivi d'avancement des cours</option>
                            <option value="doyen">Doyen(ne)</option>
                            <option value="vice-doyen">Vice-Doyen(ne)</option>
                        </select>
                    </div>
                    
                    <!-- Etablissement -->
                    <div class="input-group mb-3">
                        <div class="input-group-prepend">
                            <label class="input-group-text">Etablissement</label>
                        </div>
                        <select id="edit_etab" class="form-control" name="etab" required>
                            <option value="">Sélectionner un établissement</option>
                            <option value="<?php echo $_SESSION['univ'];?>">Présidence</option>
                            <?php 
                                $sql = "SELECT * FROM etablissement WHERE universite='".$_SESSION["univ"]."'";
                                $resultat = $connexion->query($sql);
                                while($etablissement = $resultat->fetch_assoc()){
                            ?>
                                <option value="<?php echo $etablissement['code'];?>">
                                    <?php echo str_replace("+","'",$etablissement['libelle']);?>
                                </option>
                            <?php } ?>
                        </select>
                    </div>
                    
                    <!-- Année académique -->
                    <div class="input-group mb-3">
                        <div class="input-group-prepend">
                            <label class="input-group-text">Année académique</label>
                        </div>
                        <select id="edit_annee" class="form-control" name="annee" required>
                            <option value="">Sélectionner une année</option>
                            <?php 
                                $sql = "SELECT * FROM annee";
                                $resultat = $connexion->query($sql);
                                while($annee_data = $resultat->fetch_assoc()){
                            ?>
                                <option value="<?php echo $annee_data['libelle'];?>">
                                    <?php echo $annee_data['libelle'];?>
                                </option>
                            <?php } ?>
                        </select>
                    </div>
                    
                    <button type="submit" name="modifier_user" class="btn btn-primary">Enregistrer les modifications</button>
                </form>
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
                <form id="typeAgentForm" method="post" action="traitement2" enctype="multipart/form-data">
                <div class="input-group mb-3">
                                            <div class="custom-file">
                                                <input type="file" class="custom-file-input" name="img">
                                                <label class="custom-file-label">Photo</label>
                                            </div>
                                            <div class="input-group-append">
                                                <span class="input-group-text">Importer</span>
                                            </div>
                                        </div>
                    <div class="form-group">

                        <label for="nom">Nom(s) et prénom(s) :</label>
                        <input type="text" class="form-control" id="nom" name="nom" placeholder="Entrez le nom " required>
                    </div>
                    <div class="form-group">
                        <label for="login">Login </label>
                        <input type="text" class="form-control" id="login" name="login" placeholder="Entrez le login (nom de connexion) " required>
                    </div>

                    <div class="form-group">
                        <label for="mdp">Mot de passe </label>
                        <input type="text" class="form-control" id="mdp" name="mdp" placeholder="Entrez le Mot de passe " required>
                    </div>
                    <div class="input-group mb-3">
                                            <div class="input-group-prepend">
                                                <label class="input-group-text">Role  </label>
                                            </div>
                                            <select id="type" class="" name="role" required>
                                                <option selected=""></option>
                                                
                                                
                                                <option value="scolarité"> Scolarité</option>
                                                <option value="caisse">Caisse </option>
                                                <option value="cours">Cours </option>
                                                <option value="soutenance">gestionnaire des PV de soutenance </option>
                                                <option value="gesnote">gestionnaire des notes </option>
                                                <option value="anonymat">Agent d'anonymat </option>
                                                    <option value="inscription">Agent Inscription </option>
                                                    <option value="daarhspe">Service du personnel enseignant </option>
                                                      <option value="suivi">Suivi d'avancement des cours </option>
                                                       <option value="doyen">Doyen(ne)</option>
                                                        <option value="doyen">Vice-Doyen(ne) </option>
                                            </select>
                       </div>
                       <div class="input-group mb-3">
                                            <div class="input-group-prepend">
                                                <label class="input-group-text">Etablissement  </label>
                                            </div>
                                            <select id="etab" class="" name="etab" required>
                                                <option selected=""></option>
                                                <option option="<?php echo $_SESSION["univ"];?>">Présidence</option>
                                               <?php 
                                                        $sql="select * from etablissement where universite='".$_SESSION["univ"]."'";
                                                        $resultat=$connexion->query($sql);
                                                        while($etablissement =$resultat->fetch_assoc()){
                                                ?>
                                                 <option value="<?php echo $etablissement['code'];?>"><?php echo str_replace("+","'",$etablissement['libelle']);?></option>
                                                 <?php }?>
                                            </select>
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
                    <button type="submit" class="btn btn-primary">Enregistrer</button>
                </form>
            </div>
        </div>
    </div>
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
<div class="modal" id="moncompte" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h2 class="modal-title" id="exampleModalLabel">Profil Administrateur <i class="la la-user"></i></h2>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <!-- Formulaire pour enregistrer le type d'agent -->
                <form id="typeAgentForm" method="post" action="traitement5">
                    <div class="form-group">
                        <label for="nom">Nom</label>
                        <input type="text" class="form-control" id="nom" name="nom" value="<?php echo $_SESSION['nom_user'];?>" required>
                    </div>
                    <div class="form-group">
                        <label for="login">Login( nom de connexion) </label>
                        <input type="text" class="form-control" id="login" name="login" value="<?php echo $_SESSION['login'];?>" required>
                    </div>
                    <div class="form-group">
                        <label for="mdp">Mot de passe </label>
                        <input type="text" class="form-control" id="mdp" name="mdp" value="<?php echo $_SESSION['mdp'];?>" required>
                    </div>
                    <button type="submit" class="btn btn-primary">Modifier</button>
                </form>
            </div>
        </div>
    </div>
</div>






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
	
	<!-- Chart sparkline plugin files -->
    <script src="../vendor/jquery-sparkline/jquery.sparkline.min.js"></script>
	<script src="../js/plugins-init/sparkline-init.js"></script>
	
	<!-- Chart Morris plugin files -->
    <script src="../vendor/raphael/raphael.min.js"></script>
    <script src="../vendor/morris/morris.min.js"></script> 
	
    <!-- Init file -->
    <script src="../js/plugins-init/widgets-script-init.js"></script>
	
	<!-- Demo scripts -->
    <script src="../js/dashboard/dashboard.js"></script>
	
	<!-- Summernote -->
    <script src="../vendor/summernote/js/summernote.min.js"></script>
    <!-- Summernote init -->
    <script src="../js/plugins-init/summernote-init.js"></script>
	
	<!-- Svganimation scripts -->
    <script src="../vendor/svganimation/vivus.min.js"></script>
    <script src="../vendor/svganimation/svg.animation.js"></script>
   

    <script src="../vendor/datatables/js/jquery.dataTables.min.js"></script>
    <script src="../js/plugins-init/datatables.init.js"></script>
<script>
$(document).ready(function() {
    // Handle edit button click
    $('.edit-user').click(function() {
        // Get data from button attributes
        var userId = $(this).data('id');
        var nom = $(this).data('nom');
        var login = $(this).data('login');
        var role = $(this).data('role');
        var etab = $(this).data('etab');
        var annee = $(this).data('annee');
        var img = $(this).data('img');
        
        // Populate the modal fields
        $('#edit_user_id').val(userId);
        $('#edit_nom').val(nom);
        $('#edit_login').val(login);
        $('#edit_role').val(role);
        $('#edit_etab').val(etab);
        $('#edit_annee').val(annee);
        $('#current_photo').attr('src', img);
        $('#edit_mdp').val(''); // Clear password field
    });
});
</script>
    <script>
    // Récupérer l'élément avec l'ID "salutation"
    var salutationElement = document.getElementById("salutation");

    // Obtenir l'heure actuelle
    var heure = new Date().getHours();

    // Déterminer si c'est le matin, l'après-midi ou le soir
    var salutation;
    if (heure >= 5 && heure < 12) {
        salutation = "Bonjour";
    } else if (heure >= 12 && heure < 18) {
        salutation = "Bon après-midi";
    } else {
        salutation = "Bonsoir";
    }

    // Afficher la salutation dans l'élément
    salutationElement.textContent = salutation;
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
            var docId = $(this).data('id');
            var docType = $(this).data('type');
            var lib = $(this).data('lib');
            var par=$(this).data('par');

            // Pré-remplir le formulaire modal avec les données actuelles
            $('#specId').val(docId);
            $('#nouveauPar').val(par);
            $('#nouveauSpec').val(lib);
            $('#nouveauType').val(docType);
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
                       window.location.href='traitement2?supcompte='+id ;
				})
			})

		}
			
		
	
</script>
		
</body>
</html>
<?php 

}else{
    header("location: ../login");
}
?>
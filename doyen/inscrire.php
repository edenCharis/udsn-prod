<?php 
include '../php/connexion.php';
include '../php/lib.php';

session_start();

if( $_SESSION['id'] == session_id() and  $_SESSION['role']=="scolarité"){
    if(isset($_GET['code']) && isset($_GET['annee']) && isset($_GET['specialite']))
        {

            $candidat =$_GET['code'];
            $annee =$_GET['annee'];
            $specialite=$_GET['specialite'];
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
                            <h3>FICHE D'INSCRIPTION</h3>
                        </div>
                    </div>
                    <div class="col-sm-6 p-md-0 justify-content-sm-end mt-2 mt-sm-0 d-flex">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="../scolarite/">Scolarité</a></li>
                            <li class="breadcrumb-item"><a href="../scolarite/ue">Inscription</a></li>
                           
                           
                        </ol>
                    </div>
                </div>
				
				<div class="row">
                <div class="card">
                            <div class="card-header">
                                <h4 class="card-title">Details de l'inscription</h4>
                                <a href="reinscription" class="btn btn-primary">  <i class="fa fa-school"></i> Re-inscription</a>
                            </div>
                            <div class="card-body">
                                <div class="basic-form">

                                <?php 
                                
                                    $sql="select * from candidat where code='$candidat' and specialite='$specialite' and annee='$annee'";

                                    $resultat=$connexion->query($sql);
                                     while($cand =$resultat->fetch_assoc()){
                                ?>
                                    <form action="traitement1" method="post">

                                        <div class="form-row">
                                        <div class="form-group col-md-6">
                                                <label>Matricule </label>
                                                <input type="text" class="form-control" value="<?php echo $cand['code']; ?>" name="code123">
                                            </div>
                                            <div class="form-group col-md-6">
                                                <label>Nom(s)</label>
                                                <input type="text" class="form-control" value="<?php echo $cand['nom'];?>"  disabled>
                                            </div>
                                            <div class="form-group col-md-6">
                                                <label>Prénom(s)</label>
                                                <input type="text" class="form-control" value="<?php echo $cand['prenom'];?>"  disabled>
                                            </div>
                                           
                                            <div class="form-group col-md-6">
                                                <label>Specialité / Option</label>
                                                <input type="text" class="form-control"   value="<?php echo $specialite;?>" name="spec">
                                            </div>
                                          
                                        </div>
                                        <div class="form-row">
                                            <div class="form-group col-md-4">
                                                <label>Classe</label>
                                                <select id="inputState" class="form-control" name="classe">
                                                <option selected=""></option>
                                                    <?php 
                                                       $sql="select * from classe where etab='".$_SESSION['etablissement']."'";
                                                       $res=$connexion->query($sql);
                                                       while($niv=$res->fetch_assoc()){
                                                    ?>
                                                    <option value="<?php echo $niv['libelle'];?>"><?php echo $niv['libelle']."-".$niv['specialite'];?></option>
                                                    <?php }?>
                                                </select>
                                            </div>
                                            <div class="form-group col-md-2">
                                                 <label>Année acad. </label>
                                                 <select id="inputState" class="form-control" name="annee">
                                                 <option selected=""></option>
                                                     <?php 
                                                        $sql="select * from annee ";
                                                        $res=$connexion->query($sql);
                                                        while($niv=$res->fetch_assoc()){
                                                     ?>
                                                     <option ><?php echo $niv['libelle'];?></option>
                                                     <?php }?>
                                                 </select>
                                            </div>
                                            <div class="form-group col-md-6">
                                            <label>Niveau</label>
                                                <select  class="form-control" name="niveau">
                                                    <option selected=""></option>
                                                    <?php 
                                                       $sql="select * from niveau";
                                                       $res=$connexion->query($sql);
                                                       while($niv=$res->fetch_assoc()){
                                                    ?>
                                                    <option ><?php echo $niv['libelle'];?></option>
                                                    <?php }?>
                                                </select>
                                            </div>
                                        </div>
                                       
                                        <button type="submit" class="btn btn-primary">Inscrire</button>
                                    </form>
                               <?php }?>
                                    
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


}
}else{
    header("location: ../login");
}?>
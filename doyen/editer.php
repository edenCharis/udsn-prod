<?php 
include '../php/connexion.php';
include '../php/lib.php';

session_start();

if( $_SESSION['id'] == session_id() and  $_SESSION['role']=="scolarité"){
    if(isset($_GET['code']))
        {

            $demande =$_GET['code'];
            $etudiant = getEtudiantByNumeroDemande($demande,$_SESSION['etablissement'],$connexion);
            $type_demande = getTypeDemandeByNumeroDemande($demande,$_SESSION['etablissement'],$connexion);
            $annee = getAnneeInscription($connexion,$etudiant,$_SESSION["etablissement"]);
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
                            <h3>Edition/ <?php echo strtoupper($type_demande);?> </h3>
                        </div>
                    </div>
                    <div class="col-sm-6 p-md-0 justify-content-sm-end mt-2 mt-sm-0 d-flex">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="../scolarite/">Scolarité</a></li>
                            <li class="breadcrumb-item"><a href="../scolarite/gestion">Demande</a></li>
                           
                           
                        </ol>
                    </div>
                </div>
				
				<div class="row">
                <div class="card">
                          
                            <div class="card-body">
                                <div class="basic-form">

                               
                                    <form action="page" method="post">
                                        <input type="hidden" name="demande" value="<?php echo $demande;?>">
                                        <div class="form-row">
                                        <div class="form-group col-md-6">
                                                <label>Nom(s) </label>
                                                <input type="text" class="form-control" value="<?php echo strtoupper(getNomEtudiant(getCandidatCodeByInscription($etudiant,$connexion),$connexion,$_SESSION["lib_etab"])); ?>" name="nom">
                                            </div>
                                            <div class="form-group col-md-6">
                                                <label>Prénom(s)</label>
                                                <input type="text" class="form-control" value="<?php echo strtoupper(getPrenomEtudiant(getCandidatCodeByInscription($etudiant,$connexion),$connexion,$_SESSION["lib_etab"]));?>"  name="prenom">
                                            </div>
                                            <div class="form-group col-md-6">
                                                <label>Date de naissance</label>
                                                <input type="text" class="form-control" value="<?php echo getDateNaissanceCandidat(getCandidatCodeByInscription($etudiant,$connexion),$_SESSION["lib_etab"],$connexion)?>"  disabled>
                                            </div>

                                            <div class="form-group col-md-6">
                                                <label>Lieu de naissance</label>
                                                <input type="text" class="form-control"   value="<?php echo strtoupper(getLieuNaissanceCandidat(getCandidatCodeByInscription($etudiant,$connexion),$_SESSION["lib_etab"],$connexion));?>" name="date">
                                            </div>
                                           
                                            <div class="form-group col-md-6">
                                                <label>Specialité</label>
                                                <input type="text" class="form-control" name="specialite"   value="<?php echo strtoupper(getSpecialitetudiant($etudiant,$_SESSION["etablissement"],$connexion));?>" name="specialite">
                                            </div>
                                            <div class="form-group col-md-6">
                                                <label>Parcours</label>
                                                <input type="text" class="form-control" name="parcours"   value="<?php echo getParcours(getSpecialitetudiant($etudiant,$_SESSION["etablissement"],$connexion),$connexion);?>" name="parcours">
                                            </div>

                                            <div class="form-group col-md-6">
                                                <label>Session de </label>
                                                <input type="text" class="form-control" name="session"   placeholder="Mois/Année" name="spec">
                                            </div>

                                            <div class="form-group col-md-6">
                                                <label>Mention </label>
                                                <input type="text" class="form-control" name="mention" value="<?php statutSoutenance(calcul_moyenne($etudiant,'semestre 1',$annee,$_SESSION['etablissement'],$connexion))?>" name="mention">
                                            </div>
                                          
                                          
                                        </div>
                                       
                                       
                                        <button type="submit" class="btn btn-primary" > <li class="la la-edit"></li>generer</button>
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
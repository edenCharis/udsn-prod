<?php 
include '../php/connexion.php';
include '../php/lib.php';

session_start();

if( $_SESSION['id'] == session_id() and  $_SESSION['role']=="enseignant"){


 

if (isset($_SESSION['id_user']) && !isset($_SESSION["code_enseignant"])) {
    $sql = "SELECT code_enseignant FROM user WHERE id = ?";
    $stmt = $connexion->prepare($sql);
    $stmt->bind_param("i", $_SESSION['id_user']);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($row = $result->fetch_assoc()) {
        $_SESSION["code_enseignant"] = $row['code_enseignant'];
    }
    $stmt->close();
}



$code_enseignant = $_SESSION["code_enseignant"];

// Get contract information
$numero_contrat = getContratEnseignant($code_enseignant, $connexion);

// FIXED: Validate numero_contrat exists
if(empty($numero_contrat)){
    header("location: index?erreur=" . urlencode("Contrat introuvable"));
    exit();
}

// Get annee from contrat table using numero_contrat
$sql_annee = "SELECT annee FROM contrat WHERE numero_contrat = ? LIMIT 1";
$stmt_annee = $connexion->prepare($sql_annee);
$stmt_annee->bind_param("s", $numero_contrat);
$stmt_annee->execute();
$result_annee = $stmt_annee->get_result();

if ($row_annee = $result_annee->fetch_assoc()) {
    $_SESSION["annee"] = $row_annee['annee'];
} else {
    $stmt_annee->close();
    header("location: index?erreur=" . urlencode("Année académique introuvable"));
    exit();
}
$stmt_annee->close();

 
 $classes = getClasseEnseignant($_SESSION["code_enseignant"],$_SESSION["annee"],$_SESSION["etablissement"],$connexion);
 
  if(isset($_GET["voir"])){

        $classe = $_GET["classe"];
     
        $annee=$_GET["annee"];
        //$niveau = NiveauParSemestre($semestre);

        header("location: notation1?annee=$annee&classe=$classe");
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
    <link rel="stylesheet" href="../vendor/select2/css/select2.min.css">
    <link rel="stylesheet" href="../css/style.css">
	<link rel="stylesheet" href="../css/skin.css">
	<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">


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
                           <i class="fa fa-pen"></i>


 Gestion des notes d'examens</h3>
                        </div>
                    </div>
                    <div class="col-sm-6 p-md-0 justify-content-sm-end mt-2 mt-sm-0 d-flex">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="../scolarite/">Enseignant</a></li>
                            <li class="breadcrumb-item"><a href="../scolarite/ecue">Etudiants</a></li>
                          
                           
                        </ol>
                    </div>
                </div>
				
				<div class="row">
				  <div class="col-md-12 text-center">
                        <div class="card">
                            
                            <div class="card-body">
                                <div class="basic-form">
                                    <form method="get">

                                     <div class="form-row align-items-center">
     <div class="form-group col-sm-3">
        <label>Classe</label>
        <select class="form-control disabling-options" name="classe" required>
            <option selected></option>
            <?php 
            foreach ($classes as $classe){
            ?>
            <option><?php echo $classe; ?></option>
            <?php }?>
        </select>
    </div>
    <div class="form-group col-sm-3">
        <label>Année académique</label>
        <select class="form-control disabling-options" name="annee" required>
            <option selected></option>
            <?php 
            $sql = "select * from annee";
            $resultat = $connexion->query($sql);
            while ($etablissement = $resultat->fetch_assoc()) {
            ?>
            <option><?php echo str_replace("+", "'", $etablissement['libelle']); ?></option>
            <?php }?>
        </select>
    </div>
    <div class="form-group col-sm-3 d-flex align-items-end">
        <button type="submit" class="btn  btn-success" name="voir">
            <i class="la la-eye"></i> Consulter
        </button>
    </div>
</div>

                                          </form>
                                   
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
                <h5 class="modal-title" id="exampleModalLabel">Formulaire de repartition</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <!-- Formulaire pour enregistrer le type d'agent -->
                <form id="typeAgentForm" method="post" action="traitement1">
                   
                    <div class="input-group mb-3">
                                            <div class="input-group-prepend">
                                                <label class="input-group-text">Classe : </label>
                                            </div>
                                            <select id="classe"  class="disabling-options" class="form-control form-control-lg"  name="classe" required>
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
                                                <label class="input-group-text">Unité d'enseignement : </label>
                                            </div>
                                            <select id="ecue" class="disabling-options" class="form-control form-control-lg" name="ue" required>
                                                <option selected=""></option>
                                               <?php 
                                                        $sql="select * from ue where etab='".$_SESSION['etablissement']."'";
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
    		
    <script src="../vendor/select2/js/select2.full.min.js"></script>
    <script src="../js/plugins-init/select2-init.js"></script>
	
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
    header("location: ../deconnexion1");
}?>
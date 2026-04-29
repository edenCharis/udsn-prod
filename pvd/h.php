
<?php 
include '../php/connexion.php';
include '../php/lib.php';
session_start();

if(isset($_GET['specialite']) and isset($_GET['parcours']) and isset($_GET['annee']) and isset($_GET['examen']))
{

    $semestre=$_GET["semestre"];
    //$parcours =$_GET["parcours"];
    $specialite = $_GET["specialite"];
    $annee=$_GET["annee"];
    $examen=$_GET["examen"];
    $niveau=NiveauParSemestre($semestre);


   
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $_SESSION['univ'];?> - Scolarité de  <?php echo $_SESSION['etablissement'];?> </title>
    <style>
        th, td {
            border: 1px solid black;
            padding: 8px;
            text-align: center;
        }

        table {
            border-collapse: collapse;
            width: 100%;
        }

        .small-column {
            width: 40px; /* Ajustez la largeur selon vos besoins */
        }
    

    th {
        background-color: #f2f2f2;
    }
    input[type="number"] {
        width: 50px; /* Largeur pour afficher 4 chiffres */
        text-align: center;
    }

    </style>
    	<link rel="stylesheet" href="../vendor/bootstrap-select/dist/css/bootstrap-select.min.css">
    <link rel="stylesheet" href="../css/style.css">
	<link rel="stylesheet" href="../css/skin.css">

    <link href="../vendor/datatables/css/jquery.dataTables.min.css" rel="stylesheet">
</head>
<body >



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
                    <h4>Procès verbal de délibération</h4>
                </div>
            </div>
            <div class="col-sm-6 p-md-0 justify-content-sm-end mt-2 mt-sm-0 d-flex">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="index">Tableau de Bord</a></li>
                    <li class="breadcrumb-item"><a href="recap">Recap</a></li>
                </ol>
            </div>
        </div>
            <div class="col-lg-12">
                <div class="row tab-content">
                    <div id="list-view" class="tab-pane fade active show col-lg-12">
                        <div class="card">
                            <div class="card-header">
                                <h4 class="card-title">specialite : <?php echo $specialite;?> </h4>
                                <h4 class="card-title">Semestre : <?php echo $semestre;?> </h4>
                                <h4 class="card-title">Année universitaire : <?php echo $annee;?> </h4>
                                <h4 class="card-title">Examen : <?php echo $examen;?> </h4>
                               
                            </div>
                           
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




<script src="../vendor/global/global.min.js"></script>
	<script src="../vendor/bootstrap-select/dist/js/bootstrap-select.min.js"></script>
	<script src="../js/custom.min.js"></script>
		
    <!-- Chart Morris plugin files -->
    <script src="../vendor/raphael/raphael.min.js"></script>
    <script src="../vendor/morris/morris.min.js"></script>
		
    <script src="../vendor/select2/js/select2.full.min.js"></script>
    <script src="../js/plugins-init/select2-init.js"></script>
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

function supprimerParametreUrl(nomParametre) {
    // Récupère l'URL actuelle
    var url = window.location.href;
    
    // Vérifie si l'URL contient des paramètres GET
    if (url.indexOf('?') !== -1) {
        // Récupère la partie de l'URL après le '?' (query string)
        var queryString = url.split('?')[1];

        // Divise la query string en un tableau de paires clé-valeur
        var params = queryString.split('&');

        // Crée une nouvelle query string sans le paramètre spécifié
        var newParams = params.filter(function(param) {
            // Vérifie si le paramètre ne correspond pas à celui que l'on souhaite supprimer
            return param.split('=')[0] !== nomParametre;
        });

        // Reconstitue l'URL avec la nouvelle query string
        var newUrl = url.split('?')[0] + (newParams.length > 0 ? '?' + newParams.join('&') : '');

        // Redirige vers la nouvelle URL
        window.history.replaceState({}, document.title, newUrl);
    }
}


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

            supprimerParametreUrl("erreur");
            supprimerParametreUrl("sucess");

      
        }
    });
</script>

</body>


</html>
<?php }?>

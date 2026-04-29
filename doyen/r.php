
<?php 
include '../php/connexion.php';
include '../php/lib.php';
session_start();

if(isset($_GET['semestre']) and isset($_GET['classe']) and isset($_GET['annee']) and isset($_GET['examen']))
{

    $semestre=$_GET["semestre"];
    $classe =$_GET["classe"];
    $annee=$_GET["annee"];
    $examen=$_GET["examen"];
    $niveau=NiveauParSemestre($semestre);
    $specialite = getSpecialiteClasse($connexion,$classe);
    $parcours=getParcours($specialite,$connexion);

   
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
                    <h4>Consultation des moyennes des unités d'enseignements</h4>
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

   
        <div class="col-md-6 card-title">
             <h4 >
                <span class="text-primary">Parcours</span>: <?php echo $parcours; ?>
            </h4>
            <h4 >
                <span class="text-primary">Specialité</span>: <?php echo $specialite; ?>
            </h4>
            <h4 >
                <span class="text-primary">Classe</span>: <?php echo $classe; ?>
            </h4>
        </div>
        <div class="col-md-6">
            <h4 >
                <span class="text-primary">Semestre</span>: <?php echo $semestre; ?>
            </h4>
           
             <h4 >
        <span class="text-primary">Examen</span>: <?php echo $examen; ?>
       </h4>
        <h4 >
                <span class="text-primary">Année-académique</span>: <?php echo $annee; ?>
            </h4>
            
              <h4 >
                <form method="POST" action="imprimer_ue.php">
                      
                      <input type="hidden" name="classe" value="<?php echo $classe?>">
                      <input  type="hidden" name="semestre" value="<?php echo $semestre?>">
                       <input  type="hidden" name="examen" value="<?php echo $examen?>">
                       <input type="hidden"  name="annee" value="<?php echo $annee?>">
                        <input type="hidden"  name="specialite" value="<?php echo $specialite?>">
                      <button href="submit" class="btn btn-sm btn-danger"><i class="fa fa-print"></i> Imprimer </button>
            
                  </form>
            </h4>
        </div>



        <div class="card-body">
            
            
        </div>
                   
                </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                <?php 
                                   $sql = "select distinct(ue.code),libelle from ue  where ue.etab='".$_SESSION["etablissement"]."' and specialite='".$specialite."' and semestre='$semestre' and code in ( select code_ue from ecue)";
                            ?>


<table id="example3" class="display" >
    <thead>
        <tr>
        <th >Matricule</th>
            <th>Nom(s) </th>
               <th>Prénom(s) </th>
          
          

            <?php 

          
            $result_ue =$connexion->query($sql);
            while($data=$result_ue ->fetch_object()){
            
            ?>
            <th><?php echo str_replace("+","'",$data->libelle)?></th>
        <?php }?>

         
         
            <th >Moyenne Generale</th>
            <th>Décision</th>
          
         
            
            
        </tr>
    </thead>
    <tbody>

    <?php 
    
          $sql1="select inscription.id as id, inscription.candidat as candidat from inscription join classe on inscription.classe=classe.libelle join specialite on classe.specialite=specialite.libelle join parcours on specialite.parcours=parcours.libelle where classe.libelle='$classe' and parcours.libelle='$parcours'";
          $r = $connexion->query($sql1);
          $num = 0;
          while($etudiant = $r ->fetch_object()){
            $num++;

            $points = 0;
    ?>

        <tr>
            <th class="text-secondary"><?php echo $etudiant->candidat;?></th>
            <th><?php echo  mettrePremieresLettresMajuscules(getNomEtudiant(getCandidatCodeByInscription($etudiant->id,$connexion),$connexion,$_SESSION["lib_etab"]));?></th>
            <th><?php echo  mettrePremieresLettresMajuscules(getPrenomEtudiant(getCandidatCodeByInscription($etudiant->id,$connexion),$connexion,$_SESSION["lib_etab"])); ?></th>
            
            
            <?php 
               
               $result_ue =$connexion->query($sql);
           
               while($data=$result_ue ->fetch_object()){
                     $i=0; $countValide =0;
                     
                     $moyenne = getMoyenneUE($connexion,$etudiant->id,$semestre,$annee,$data->code,$_SESSION["etablissement"]);
                   
                
            ?>
                
          
             <th class="text-secondary"> <?php echo ($moyenne !== "-") ? round($moyenne,2): "-";?></th>
       
           <?php } ?>

       
           <th class="text-dark"> <?php $tt=calcul_moyenne($etudiant->id,$semestre,$annee,$_SESSION['etablissement'],$connexion);echo  $tt?></th>
       
            <?php 
            
              if( $tt < 10 and $tt !=="-" ) {
            
            ?>
            
            <th class="text-danger">  Ajourné </th>
            
            <?php }else if ($tt > 10){
                
            ?>
            
            <th class="text-success"> Admis </th>
            <?php }else{?>
             <th class="text-warning"> - </th>
             <?php }?>
           
         
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

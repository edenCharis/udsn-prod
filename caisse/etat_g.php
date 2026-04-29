<?php 
include '../php/connexion.php';
include '../php/lib.php';

session_start();

if( $_SESSION['id'] == session_id() and  $_SESSION['role']=="caisse"){


    if(isset($_GET["enseignant"]) and isset($_GET["mois"]) and isset($_GET["annee"]))
    {

        $enseignant = $_GET["enseignant"];

        $annee = $_GET["annee"];

        $mois = $_GET["mois"];

      

?>


<!DOCTYPE html>
<html lang="en">

<head>
	
	<meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title> </title>
    <!-- Favicon icon -->
    <link rel="icon" type="image/png" sizes="16x16" href="../administrateur/<?php echo  $_SESSION['logo_univ']?>">
	<link rel="stylesheet" href="../vendor/bootstrap-select/dist/css/bootstrap-select.min.css">
    <link rel="stylesheet" href="../vendor/select2/css/select2.min.css">
    <link rel="stylesheet" href="../css/style.css">
	<link rel="stylesheet" href="../css/skin.css">

    <link href="../vendor/datatables/css/jquery.dataTables.min.css" rel="stylesheet">
<style>
     .underline-text {
        text-decoration: underline;
    }
.table-bold-rows-cols {
border-collapse: collapse;
}

.table-bold-rows-cols tbody tr {
font-weight: bold;
}

.table-bold-rows-cols tbody tr td,
.table-bold-rows-cols tbody tr th {
border: 1px solid black; /* Bordure extérieure */
padding: 8px; /* Ajoute un espacement entre le texte et les bordures */
}

.table-bold-rows-cols tbody tr td:not(:first-child),
.table-bold-rows-cols tbody tr th:not(:first-child) {
border-left: 1px solid black; /* Bordure intérieure gauche */
}

.table-bold-rows-cols tbody tr td:not(:last-child),
.table-bold-rows-cols tbody tr th:not(:last-child) {
border-right: 1px solid black; /* Bordure intérieure droite */
}

body {
    position: relative; /* Permet de positionner le logo par rapport à cette balise */
}

.logo-filigrane {
    position: fixed;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    opacity: 0.2; /* Opacité du logo de filigrane */
    z-index: -1; /* Pour placer le logo derrière le contenu principal */
}




     </style>

</head>
<body>

    <!--*******************
        Preloader start
    ********************-->
    <div id="preloader">
        
    </div>
    <!--*******************
        Preloader end
    ********************-->

    <!--**********************************
        Main wrapper start
    ***********************************-->
    <div id="main-wrapper">

       
        <div class="content h-50"  id="contenu-a-imprimer" >
            <!-- row -->
            <div class="container-fluid">
            <div class="row">
    <div class="col-md-12">
      <div class="d-flex justify-content-between align-items-center">
        <!-- Université Denis Sassou Nguesso -->
        <div class="col-auto"> <h5> <b>UNIVERSITE DENIS SASSOU N'GUESSO</b> </h5>
           <p>SERVICE DE LA COMPTABILITE</p></div>
        <div class="col-auto">
          <img src="../images/univ.png" alt="Logo de l'université" style="max-width: 100px;">
        </div>
        <!-- Devise -->
        <div class="col-auto">  <h4>Rigueur-Excellence-Lumieres</h4>
      
    
</div>
    
        <!-- Logo -->
     
      </div>
    </div>
  </div>
 
			
              
              
               
 <div class="row h-100 justify-content-center align-items-center mt-5">
        <div class="col-md-4">
            
                            <div class="form-group row">
                                <br>
                            <label class="col-sm-12 col-form-label"><h4>ETAT DE COMPTABILITE</h4></label>
                            </div> 
                            <div class="form-group row">
                            <label class="col-sm-12 col-form-label"> <h6>Année Universitaire : <?php echo strtoupper($annee);?></h6></label>
                            </div>        
               <div class="form-group row">
               <label class="col-sm-12 col-form-label">NOM(s) DE L'ENSEIGNANT :  <b><?php echo strtoupper(getNomEnseignant($enseignant,$connexion)." ".getPrenomEnseignant($enseignant,$connexion));?></b></label>
               </div>
               <div class="form-group row">
               <label class="col-sm-12 col-form-label">MOIS :  <b><?php echo strtoupper(getNomMois($mois));?></b></label>
                  
               </div>
               <div class="form-group row">
               <label class="col-sm-12 col-form-label"> NUMERO MATRICULE DE L'ENSEIGNANT :  <b><?php echo strtoupper($enseignant);?></b></label>
                   
               </div>
               <div class="form-group row">
                   <label class="col-sm-12 col-form-label">GRADE :  <b><?php echo getGrade($enseignant,$connexion);?></b></label>
                   
               </div>
             
               
          
              


            </div>

          

                                    
                    
</div>     

<div class="row row h-100 justify-content-center align-items-center">
    <div class="col-sm-8">
    

       
        <div class="table-responsive">
           
            <table  class="table  table-bordered table-responsive-sm table-bold-rows-cols">
              
                    <tr>
                        <th>Etablissement</th>
                        <th>Heure effectuées</th>
                        <th>Mois</th>
                    
                        <th>Taux Horaire</th> 
                        <th>Montant a payer</th>           
                     
                    </tr>
               
                <tbody>
                    <?php 
                       $sql="SELECT sum(total_heures) as heures,mois,enseignant,etab,annee FROM `cumuleheure` where enseignant='$enseignant' and mois=$mois and annee='$annee' GROUP by etab,enseignant ";
                       $requete=$connexion->query($sql);
                       $heures = 0;

                       $th =null;

                       while($data = $requete->fetch_object()){
                        $heures =$heures + $data->heures;
                        $th =getThByEns($data->enseignant,$connexion,$data->etab);
                        ?>
                    <tr>
                        <th><?php echo $data->etab;?></th>
                        <td><?php echo $data->heures;?></td>
                        <td><?php echo getNomMois($data->mois);?></td>  
                        <td><?php echo getThByEns($data->enseignant,$connexion,$data->etab);?></td>
                        <td><?php echo $heures*$th;?></td>
                       
                       
                      
                      </tr>
                    <?php }?>
                    <tr>
                      <td colspan="2"> Total Heures effectuées : <b><?php echo $heures; ?></b></td>
                       <td colspan="3"> Total Montant a payer : <b> <?php echo $heures*$th;?></b>  </td>
                          </tr>
                  
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
       
        <!--**********************************
            Footer start
        ***********************************-->
       
       

    
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
  // Déclencher l'impression une fois que le document est prêt
  window.print();
});
</script>
<script>
    $(document).ready(function() {
        // Gérer le clic sur le bouton "Modifier"
        $('.btn-primary').click(function() {
            // Récupérer les données de la ligne cliquée
            var id = $(this).data('id');
            var x = $(this).data('moydev');
            var y= $(this).data('moyex');
 
           

            // Pré-remplir le formulaire modal avec les données actuelles
            $('#noteId').val(id);
            $('#nouveauMoyDev').val(x);
            $('#nouveauMoyEx').val(y);

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
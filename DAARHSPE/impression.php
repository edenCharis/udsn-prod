<?php 
include '../php/connexion.php';
include '../php/lib.php';

session_start();

if( $_SESSION['id'] == session_id() and  $_SESSION['role']=="daarhspe"){


    if(isset($_GET["contrat"]) and isset($_GET["annee"])){
        
        $contrat=$_GET["contrat"];
        $annee_academique=$_GET["annee"];
        $id_enseignant= getEnseignantByContrat($contrat,$connexion);
        $dateString =  getDateNaissanceEnseignant($id_enseignant,$connexion);
        $lieu = getVilleEnseignant($id_enseignant,$connexion);


        $date = new DateTime($dateString);

        // Formater la date au format dd/mm/yyyy
        $date_naissance = $date->format('d/m/Y');
        
        $telephone = getTelEnseignant($id_enseignant,$connexion);

        $email = getEmailEnseignant($id_enseignant,$connexion);
           $etab= getLibelleEtablissement( getEtablissementEnseignement($id_enseignant,$connexion),$connexion);
        $ecues = getEcuesContrat($contrat,$etab,$connexion);

        $nom =  (getsexeEnseignantById($id_enseignant,$connexion) == "Homme" ? "M." : "Mme.").strtoupper(getNomPrenomEnseignantById($id_enseignant,$connexion));
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
h4,h3{
    text-decoration-color: black;

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
    width: auto;
    transform: translate(-50%, -50%);
    opacity: 0.2; /* Opacité du logo de filigrane */
    z-index: -1; /* Pour placer le logo derrière le contenu principal */
}
body {
            font-family: Arial, sans-serif; /* Utilisation de la police Arial, ou une police sans-serif si Arial n'est pas disponible */
        }

        /* Définir la police pour un élément spécifique, par exemple une classe */
        .custom-font {
            font-family: "Times New Roman", Times, serif; /* Utilisation de la police Times New Roman, ou Times, ou une police serif si elles ne sont pas disponibles */
         
        }


     </style>

<style>
        .text-underline {
            text-decoration: underline;
        }

        .word-spacing {
            word-spacing: 1rem; /* Ajustez cette valeur selon vos besoins */
        }
    </style>

</head>
<body>

    <!--*******************
        Preloader start
    ********************-->
  
    <!--*******************
        Preloader end
    ********************-->

    <!--**********************************
        Main wrapper start
    ***********************************-->
    <div id="main-wrapper">

       
        <div class="content h-50" id="contenu-a-imprimer">
            <!-- row -->
            <div class="container-fluid">
<div class="row custom-font" style="height: 280px;"  >
    <div class="col-md-12">
     <div class="d-flex justify-content-between align-items-center" >
        <!-- Université Denis Sassou Nguesso -->
        <div class="p-2 text-center"> <h5 > <b> UNIVERSITE DENIS SASSOU-N'GUESSO</b> </h5> 
        <P>**************************</P>                   
            <h5 class="text-center">                           SECRETARIAT GENERAL</h5>
            <P>**************************</P>
            <h6 >DIRECTION DES AFFAIRES ADMINISTRATIVES </h6>
            <h6>ET DES RESSOURCES HUMAINES</h6>
            <P>**************************</P>
            <h6>SERVICE DES PERSONNELS ENSEIGNANTS</h6>
            <P>**************************</P>
          </div>
        <div class="p-2">
          <img src="../images/univ.png" alt="Logo de l'université" style="max-width: 250px;">
        </div>
        <!-- Devise -->
        <div class="p-2 text-center">  <h3>Rigueur-Excellence-Lumieres</h3>
        <P>**************************</P>

     
       </div>
    
        <!-- Logo -->
     
      </div>
    </div>
  </div>



				<div class="row custom-font justify-content-center align-items-center flex-column align-items-start mb-50"  style="height: 100px;">
                     <h2 class="text-center">Année académique <?php echo $_GET["annee"];?></h2> <br>
                     <h4 class="text-center">N° ......................./UDSN-SG-DAARH/SPE du ........................................</h4>           
                </div>

                <div class="row custom-font justify-content-center align-items-center align-items-start flex-column">
                      <div class="d-flex w-100 justify-content-center">
                          <h3 class="text-center" >Entre l'Université Denis SASSOU-N'GUESSO, représentée par le Président, d'une part </h3>
                       
                     </div>
                     <div class="d-flex w-100 justify-content-center">
                         <h3 class="text-center">Et</h3>
                     </div>
                     <div class="d-flex w-100 justify-content-start">
                           <h3 class="text-left text-underline">Visas</h3>
                    </div>

                    <div class="d-flex w-100 justify-content-center">
                         <h3 class="text-center"><b><?php echo (getsexeEnseignantById($id_enseignant,$connexion) == "Homme" ? "M." : "Mme.") ?> <?php echo strtoupper(getNomPrenomEnseignantById($id_enseignant,$connexion));?>    Titre/Grade:  <?php echo getEnseignantDescription ($id_enseignant,$connexion);?></b> </h3>
                     </div>
                     <div class="d-flex w-100 justify-content-center">
                         <h3 class="text-center word-spacing">  Né le <?php echo $date_naissance ;?>                 Résidant habituellement à <?php echo $lieu;?></h3>
                     </div>
                     <div class="d-flex w-100 justify-content-center">
                         <h3 class="text-center word-spacing">  Téléphone  <?php echo $telephone  ;?> ; Email(courriel): <b class="text-underline" ><?php echo $email;?></b> </h3>
                     </div>
                </div>

       

                <div class="row custom-font justify-content-center align-items-center align-items-start flex-column">
                    
                   <div class="d-flex w-100 justify-content-start">
                           <h3 class="text-left text-underline">Chef d'établissement</h3>
                    </div>
                    <div class="d-flex w-100 justify-content-center">
                         <h3 class="text-center"> Qui déclare sur l'honneur s'engager librement à donner des préstations à L'Université Denis  SASSOU-N'GUESSO </h3>
                     </div>
                     <div class="d-flex w-100 justify-content-center">
                         <h3 class="text-end">en qualité d'enseignant vacataire, d'autre part, Il a été convenu, d'un commun accord ce qui suit : </h3>
                     </div>
                     
                     <div class="d-flex w-100 justify-content-start">
                           <h3 class="text-left text-underline">DBF</h3>
                    </div>
                    <div class="d-flex w-100 justify-content-center">
                         <h3 class="text-end"></h3>
                     </div>
                
                </div>
               
<div class="row custom-font justify-content-center align-items-center flex-column align-items-start mb-20">                             
                    <h2 class="text-justify text-underline"> <b>Dispositions particulières</b>  </h2> 
</div>
                   <div class="d-flex w-100 justify-content-center mb-20">
                         <h3 class="text-center"> <b class="text-underline">Article 1: </b>  </h3>
                         <h3 class="text-center"> <b><?php  echo $nom; ?></b>  </h3>
                     </div>
                     <div class="d-flex w-100 justify-content-start">
                           <h3 class="text-left text-underline">CB</h3>
                    </div>
                    <div class="d-flex w-100 justify-content-center">
                         <h3 class="text-center"> Loue ses services en qualité de vacataire pour dispenser l'enseignement ou les enseignements de  <?php  foreach($ecues as $e){
                            echo $e."    "; }?> au  ou à  <?php echo $etab;?>
                        </h3>
                     </div>
                     <div class="d-flex w-100 justify-content-center mb-20">
                         <h3 class="text-center"> <b class="text-underline">Article 2: </b>  </h3>
                         <p>Le vacataire est engagé , conformement aux dispositions du décret n° 2020-860 du 28 décembre 2020</p>
                         <p>portant approbation du statut particulier des personnels de l'Université Denis SASSOU-N'GUESSO</p>
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
  // Déclencher l'impression une fois que le document est prêt
  window.print();
});
</script>


	
</body>
</html>

<?php 

}
}else{
    header("location: ../login");
}?>
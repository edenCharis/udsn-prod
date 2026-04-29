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
        $ecues = getEcuesContrat($contrat, getEtablissementEnseignement($id_enseignant,$connexion),$connexion);

        $nom =  (getsexeEnseignantById($id_enseignant,$connexion) == "Homme" ? "M." : "Mme.").strtoupper(getNomPrenomEnseignantById($id_enseignant,$connexion));
$type=array();

        $sql="select * from type_grade";
        $r=$connexion->query($sql);
        while($data =$r->fetch_object()){
      $type[]=$data->libelle;
                
                }
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
   
    .rectangle {
        width: 25px;      /* Largeur du carré */
            height: 25px;     /* Hauteur du carré */
         /* Couleur de fond du carré */
            border: 2px solid #000;     /* Bordure du carré */
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
.list-group-item {
            font-size: 1.25rem; /* Taille du texte agrandie */
            padding: 1rem; /* Padding agrandi */
        }
        .form-check-input {
            transform: scale(1.5); /* Agrandir la case à cocher */
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
      

        .word-spacing {
            word-spacing: 1rem; /* Ajustez cette valeur selon vos besoins */
        }
        .ml-custom {
            margin-left: 200px; /* Ajustez cette valeur selon vos besoins */
            margin-right: 200px;
        }
        .ml-custom1 {
            margin-left: 100px; /* Ajustez cette valeur selon vos besoins */
          
        }
        .ml-custom2 {
            margin-left: 150px; /* Ajustez cette valeur selon vos besoins */
          
        }
        .ml-custom3 {
            margin-left: 400px; /* Ajustez cette valeur selon vos besoins */
          
        }
        .ml-custom4 {
            margin-left: 300px; /* Ajustez cette valeur selon vos besoins */
          
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

       
<div class="content h-750" id="contenu-a-imprimer">
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


   <div class="row align-items-center flex-column align-items-start">
   <div class="ml-custom p-3  mb-1 ">
   <h2 class="text-center">Année académique <?php echo $_GET["annee"];?></h2> <br>
                     <h4 class="text-center">N° ......................./UDSN-SG-DAARH/SPE du ........................................</h4>           
                </div>
   </div>
   
   <div class="row  flex-column align-items-start">
           <div class="ml-custom1 p-3  mb-1 ">
               <h3 class="custom-font">Entre l'Université Denis SASSOU-N'GUESSO, représentée par le Président, d'une part</h3>
               <h2 class="custom-font text-center">et</h2>            
            </div>
                <div class="ml-0 p-3  mb-0 ">
                     <h3 class="custom-font text-underline" style="text-decoration: underline">Visas</h3>       
                </div>
             <div class="ml-custom2 p-3  mb-0">
               <h2 class="custom-font"> <b><?php echo (getsexeEnseignantById($id_enseignant,$connexion) == "Homme" ? "M." : "Mme.") ?> <?php echo strtoupper(getNomPrenomEnseignantById($id_enseignant,$connexion));?>    Titre/Grade:  <?php echo getEnseignantDescription ($id_enseignant,$connexion);?></b> </h2>
               <h2 class="custom-font"> Né le <?php echo $date_naissance ;?>                 Résidant habituellement à <?php echo $lieu;?> </h2>
               <h2 class="custom-font">  Téléphone : <?php echo $telephone  ;?></h2>
               <h2 class="custom-font">  Email(courriel): <b class="text-underline" ><?php echo $email;?></h2>         
            </div>

          
                
   </div>
   <div class="row  flex-column align-items-start">
   <div class="ml-0 p-3  mb-0 ">
                     <h3 class="custom-font">Chef d'établissement</h3>       
            </div>
          
             <div class="ml-custom2 p-3">
                <h2 class="custom-font"> Qui déclare sur l'honneur s'engager librement à donner des préstations à L'Université Denis  SASSOU-N'GUESSO  en qualité d'enseignant
                 vacataire, d'autre part, Il a été convenu, d'un commun accord ce qui suit: </h2>
                        
            </div>
            <div class="ml-0 p-3  mb-0">
                     <h3 class="custom-font">DBF</h3>       
            </div>
            <div class="ml-custom3 p-3">
                <h2 class="custom-font" > <b style="text-decoration: underline;">Dispositions particulières</b>  </h2>
               
                        
            </div>
            <div class="ml-custom2 p-3">
                <h2 class="custom-font"> <b style="text-decoration: underline">Article 1: </b> <?php echo $nom;?> </h2>
                        
            </div>
            <div class="ml-custom2 p-3">
                <h2 class="custom-font"> Loue ses services en qualité de vacataire pour dispenser l'enseignement ou les enseignements de<sup>1</sup> <b ><?php foreach($ecues as $e){
                            echo $e." "; }  ?></b>  à<sup>2</sup> <?php echo strtoupper( $etab);?>.</h2> 
                        
            </div>
            <!--<div class="ml-0 p-3  mb-0"  style="height: 75px;">
                     <h3 class="custom-font">________________________________________</h3>  
                     <p> <sup>1</sup> Indiquer la ou les matières à enseigner </p>     
                    
            </div>-->
            
                
   </div>
   <div class="row  flex-column align-items-start">
   <div class="ml-0 p-3  mb-0 ">
                     <h3 class="custom-font">CB</h3>       
            </div>
          
            <div class="ml-custom2 p-3">
                <h2 class="custom-font"> <b style="text-decoration: underline">Article 2: </b> </h2>
                        
            </div>
            
            <div class="ml-custom2 p-3">
                <h2 class="custom-font"> Le vacataire engagé, conformément aux dispositions du decret n° 2020-860 du 28 décembre 2020 portant approbation du statut particulier 
                    des personnels de l'Université Denis SASSOU-N'GUESSO, assure les activités pédagogiques et scientifiques liées à son grade par réference a son classement dans la 
                    grille des personnels enseignants. <br> <br> Ce classement est arrété comme suit: </h2> 
                        
            </div>
            <div class="ml-custom4 p-3">
                <h2 class="custom-font text-underline" > <b style="text-decoration: underline">Pour les grades d'enseignants permanents des universités</b>  </h2>
               
                        
            </div>
            <style>
                 ul {
            list-style-type: none; /* Supprime les puces de la liste */
            padding: 0; /* Supprime le padding par défaut de la liste */
            margin: 0; /* Supprime la marge par défaut de la liste */
            display: flex; /* Utilise Flexbox pour aligner les éléments horizontalement */
        }
        li {
            margin-right: 100px; /* Espacement entre les éléments de la liste */
        }
            </style>
            <div class="ml-0 p-3  mb-0 ">
       
                          <h2 class="custom-font"> <ul>
                            <li><b>-</b> Professeur Titulaire </li>
                            <li><div class="rectangle"></div></li>
                          </ul>   </h2>
            </div>
            <div class="ml-0 p-3  mb-0 ">
       
       <h2 class="custom-font"> <ul>
         <li><b>-</b>Maître des conférences </li>
         <li><div class="rectangle"></div></li>
       </ul>   </h2>
</div>
<div class="ml-0 p-3  mb-0 ">
       
       <h2 class="custom-font"> <ul>
         <li><b>-</b> Maître-assistant </li>
         <li><div class="rectangle"></div></li>
       </ul>   </h2>
</div>
<div class="ml-0 p-3  mb-0 ">
       
       <h2 class="custom-font"> <ul>
         <li><b>-</b> Assistant </li>
         <li><div class="rectangle"></div></li>
       </ul>   </h2>
</div>     
                
   </div>

   <div class="ml-custom4 p-3">
                <h2 class="custom-font" style="text-decoration: underline"> <b>Pour les autres enseignants</b>  </h2>
               
                        
            </div>
   <div class="ml-0 p-3  mb-0 ">
       
       <h2 class="custom-font"> <ul>
         <li><b>-</b> Chargé de cours </li>
         <li><div class="rectangle"></div></li>
       </ul>   </h2>
   </div>  
   <div class="ml-0 p-3  mb-0 ">
       
       <h2 class="custom-font"> <ul>
         <li><b>-</b> Chargé d'encadrement technique et professionnel </li>
         <li><div class="rectangle"></div></li>
       </ul>   </h2>
   </div> 
   <div class="ml-0 p-3  mb-0 ">
       
       <h2 class="custom-font"> <ul>
         <li><b>-</b> Professionnel </li>
         <li><div class="rectangle"></div></li>
       </ul>   </h2>
   </div>   


   <div class="ml-custom2 p-3">
                <h2 class="custom-font"> <b style="text-decoration: underline">Article 3: </b> le vacataire est assujetti aux obligations suivantes : </h2>
                        
            </div>
            <div class="ml-0 p-3  mb-0 ">
       
       <h2 class="custom-font">- Effectuer un volume horaire annuel équivalent à 45 heures d'enseignement correspondant à 03 crédits;   </h2>
   </div> 
   <div class="ml-0 p-3  mb-0 ">
       
       <h2 class="custom-font">- Remplir régulièrement sa fiche de déclaration des heures;  </h2>
       
   </div>
   <div class="ml-0 p-3  mb-0"  style="height: 75px;">
                     <h3 class="custom-font">________________________________________</h3>  
                     <p> <sup>2</sup> Indiquer l'Etablissement d'attache</p>     
                    
            </div>
   <div class="ml-0 p-3  mb-0 ">
       
       <h2 class="custom-font">- S'acquitter régulièrement de ses tâches d'enseignement et du service des examens ( confection des sujet, surveillance, déliberation pour toute les sessions);   </h2>
   </div>  
  
   <div class="ml-0 p-3  mb-0 ">
       
       <h2 class="custom-font">- Participer aux activités de recherche de sa specialité;   </h2>
   </div>  
            <div class="ml-0 p-3">
                <h2 class="custom-font"> <b style="text-decoration: underline">Article 4: </b> Tout manquement à l'une des obligations mentionnées à l'article 3 du présent contrat entraîne la rupture de celui ci. </h2>
                        
            </div>
            <div class="ml-0 p-3">
                <h2 class="custom-font"> <b style="text-decoration: underline">Article 5: </b>     Le vacataire perçoit une rémuneration payable selon la procédure en vigueur à l'Université Denis SASSOU-N'GUESSO aux taux horaire fixé pour chaque grade comme suit: </h2>
                        
            </div>
            <div class="ml-0 p-3  mb-0 ">
       
                          <h2 class="custom-font"> <ul>
                            <li><b>-</b> Professeur Titulaire </li>
                            <li>: 15 000 FCFA</li>
                          </ul>   </h2>
            </div>
            <div class="ml-0 p-3  mb-0 ">
       
       <h2 class="custom-font"> <ul>
         <li><b>-</b>Maître des conférences </li>
         <li>: 12 000 FCFA</li>
       </ul>   </h2>
</div>
<div class="ml-0 p-3  mb-0 ">
       
       <h2 class="custom-font"> <ul>
         <li><b>-</b> Maître-assistant </li>
         <li>: 10 000 FCFA</li>
       </ul>   </h2>
</div>
<div class="ml-0 p-3  mb-0 ">
       
       <h2 class="custom-font"> <ul>
         <li><b>-</b> Assistant </li>
         <li>: 8 000 FCFA</li>
       </ul>   </h2>
</div> 
<div class="ml-0 p-3  mb-0 ">
       
       <h2 class="custom-font"> <ul>
         <li><b>-</b> Chargé de cours </li>
         <li>: 8 000 FCFA</li>
       </ul>   </h2>
   </div>  
   <div class="ml-0 p-3  mb-0 ">
       
       <h2 class="custom-font"> <ul>
         <li><b>-</b> Chargé d'encadrement technique et professionnel </li>
         <li>: 6 000 FCFA</li>
       </ul>   </h2>
   </div> 
   <div class="ml-0 p-3  mb-0 ">
       
       <h2 class="custom-font"> <ul>
         <li><b>-</b> Professionnel </li>
         <li>: 10 000 FCFA</li>
       </ul>   </h2>
   </div>  
   <div class="ml-0 p-3">
                <h2 class="custom-font"> <b style="text-decoration: underline">Article 6: </b>    Le présent contrat est conclu pour une durée d'une année académique. Il prend effet à compter de la rentrée académique et vient à échéance à la fin de celle-ci. Il est renouvellable par accord exprès des parties </h2>
                        
            </div>


            <div class="ml-0 p-3">
                <h2 class="custom-font"> Le contrat peut être résilié par l'une ou l'autre des parties moyennant un préavis d'une durée de trois mois donné par la partie qui prend l'initiative de la rupture. </h2>     
            </div>
            <div class="ml-0 p-3  mb-0"  style="height: 100px;">
                     <h1>                                            </h1>
            </div>
         
        </div>
        <div class="row  flex-column align-items-start" style="margin-top: 100px;">
        <div class="ml-custom2 p-3 mb-50">
                <h2 class="custom-font"> <b style="text-decoration: underline">Article 7: </b>   Pour tout ce qui n'est pas stipulé au présente conditions particulières, les soussignés se réfèrent aux dispositions générales figurant dans le décret n° 2020-860 du 28 décembre 2020 portant approbation du statut particulier des personnels et dans 
            le Règlement intérieure de L'Université Denis SASSOU-N'GUESSO dont le vacataire a pris connaissance et auquel il déclare souscrire. </h2>
                        
            </div>


            <div class="ml-custom2 p-3 mb-50">
                <h2 class="custom-font">Fait à Kintélé en deux exemplaires originaux, le  <b><?php echo date("Y-m-d")?></b> </h2>
                        
            </div>
            <div class="ml-0 p-3 mb-50">
                <h2 class="custom-font"> <b style="text-decoration: underline;">Le Contratant</b> <sup>4</sup> </h2>

                        
            </div>
       
          
                
   </div>
  
   <h2 class="custom-font text-right" style="margin-bottom: 100px;"> <b style="text-decoration: underline;">Le Président de L'UDSN</b></h2>
               
   <h2 class="custom-font text-right" style="margin-bottom: 800px;"> <b >Pr Ange Antoine ABENA</b>  </h2>
   <div class="ml-0 p-3  mb-0">
                     <h3 class="custom-font">________________________________________</h3>  
                     <p> <sup>4</sup> Signature précedée de la mention << lu et approuvé >> ( Réproduirela mention de sa main et signer)</p>     
                    
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
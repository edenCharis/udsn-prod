<?php 
include '../php/connexion.php';
include '../php/lib.php';

session_start();

if( $_SESSION['id'] == session_id() and  $_SESSION['role']=="scolarité" and isset($_GET["p"])){

$numero= $_GET["p"];

$annee = null;
$semestre = null;

$sql="select * from deliberation where numero='$numero' and etab='".$_SESSION["etablissement"]."'";
$r=$connexion->query($sql);

while($d = $r->fetch_object()){
   $annee = $d->annee;
   $semestre =$d->type_semestre;
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
     .custom-font2 {
            font-family: "Times New Roman", Times, serif;
            font-size: 25px; /* Utilisation de la police Times New Roman, ou Times, ou une police serif si elles ne sont pas disponibles */
        }
        .custom-font {
            font-family: "Times New Roman", Times, serif;
            font-size: 25px; /* Utilisation de la police Times New Roman, ou Times, ou une police serif si elles ne sont pas disponibles */
        }
        .custom-font1 {
            font-family: "Times New Roman", Times, serif;
           /* Utilisation de la police Times New Roman, ou Times, ou une police serif si elles ne sont pas disponibles */
        }
     .underline-text {
        text-decoration: underline;
    }
.table-bold-rows-cols {
border-collapse: collapse;
}

.table-bold-rows-cols tbody tr {
font-weight: bold;
}
.custom-font {
            font-family: "Times New Roman", Times, serif; /* Utilisation de la police Times New Roman, ou Times, ou une police serif si elles ne sont pas disponibles */
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
    position: relative; 
    font-family: "Times New Roman", Times, serif;/* Permet de positionner le logo par rapport à cette balise */
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
        <div class="sk-three-bounce">
        <img src="../images/univ.png" class="logo-filigrane" alt="Logo en filigrane">
        </div>
    </div>
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
            <div class="row" style="height: 300px;">
    <div class="col-md-12">
      <div class="d-flex justify-content-between align-items-center">
        <!-- Université Denis Sassou Nguesso -->
        <div class="p-2"> <p class="justify-content-center custom-font"> <b> <?php echo ("UNIVERSITE DENIS SASSOU-N'GUESSO")?></b> </p>
            <p class="justify-content-center custom-font"> <b><?php echo mettrePremieresLettresMajuscules($_SESSION["lib_etab"])?></b>
<?php if(typeEtablissement($_SESSION["lib_etab"],$connexion)== "faculté") {?>
            <p class="justify-content-center custom-font" > <b><?php echo "Décanat";?></b></p>
            <p class="justify-content-center custom-font"><b><?php echo "Vice-Décanat"?></b></p>
            <?php }?>
        
    </p>
        </div>
        <div class="p-2">
          <img src="../images/univ.png" alt="Logo de l'université" style="max-width: 100px;">
        </div>
        <!-- Devise -->
        <div class="p-2"> <b> <h4 class="custom-font">Rigueur-Excellence-Lumieres</h4></b>
      
</div>

    
        <!-- Logo -->
     
      </div>
    </div>
  </div>
 
			

            
              
                <div class="row  justify-content-center align-items-center mb-250">
                    
                   
                    <h2 class="custom-font">Note de service N° _______________________/<?php echo $_SESSION["univ"];?>.<?php echo $_SESSION["etablissement"]?><?php echo ( (typeEtablissement($_SESSION["lib_etab"],$connexion) == "faculté" )? ".D.VD. " : "" )?></h2>
                       <h4 class="custom-font justify-content-center">Portant nomination des jurys de délibération des examens des   <?php echo ($semestre == "pair" ? "semestres 2,4 et 6" : "semestres 1,3 et 5") ?>  de l’année académique <?php  echo $annee;?> à <?php echo (typeEtablissement($_SESSION["lib_etab"],$connexion) == "faculté" ? "la" : "l'")?> <?php echo mettrePremieresLettresMajuscules($_SESSION["lib_etab"])?></h4> 
                       
                </div>

        <div class="row h-100 justify-content-center align-items-center">
            <p class="custom-font justify-content-center">Conformément à l’article 25 de la charte des examens de l’UDSN, sont nommés membres des jurys de délibération des examens des  <?php echo ($semestre == "pair" ? "semestres 2,4 et 6" : "semestres 1,3 et 5") ?>  des différents parcours de <?php echo (typeEtablissement($_SESSION["lib_etab"],$connexion) == "faculté" ? "la" : "l'")?> <?php echo mettrePremieresLettresMajuscules($_SESSION["lib_etab"])?> , au titre de l’année académique <?php  echo $annee;?> , les enseignants dont les noms et prénoms suivent :</p>
            
        </div>
        <div class="row  justify-content-right align-items-right">
        <?php 
$sql1="select * from parcours where etab='".$_SESSION["lib_etab"]."'";

$r =$connexion->query($sql1);       
 $count = 0;
while($p=$r->fetch_object()){
    $count++;
    ?>
     <div class="col-md-8">
    
        <p class="custom-font">
          <h3 class="custom-font justify-content-right">  <b> <?php echo $count; ?>)	Parcours <?php echo mettrePremieresLettresMajuscules($p->libelle)?> </b></h3>  </br>
<h4 class="custom-font">Président : </h4>	<b class="custom-font"><?php echo   getPerson($numero,$p->libelle,"Président",$connexion,$_SESSION["etablissement"]);  ?></b>	
<h4 class="custom-font">Rapporteur : </h4> <b class="custom-font"><?php  getPerson($numero,$p->libelle,"Président",$connexion,$_SESSION["etablissement"]); ?></b>	
<h4 class="custom-font">Membres : </h4> </br>
<b class="custom-font"> <?php  getPerson($numero,$p->libelle,"Membre",$connexion,$_SESSION["etablissement"])?> </b>


           </p>

           </div>


        <?php }?>

           
           
        </div>

        <div class="row h-100 justify-content-center">
    <div class="col-sm-8">
    <div class="float-right" >
    <h3 style="height: 25px" class="custom-font">Fait à Kintélé le               </h3>
             <h3 class="custom-font" style="height: 100px;">Le Doyen</h3>
             
             <p><?php  $id=getNum("Note de service",$connexion,$_SESSION["etablissement"]) ;

?>    <img src="<?php echo getSignatureData0($connexion,"Note de service")?>" alt=""></p>

<p>
<h3 class="custom-font"><b> <?php $code = getCodeEnseignant($id,$connexion);
               echo getGradeById($id,$connexion)."  ".mettrePremieresLettresMajuscules(getPrenomEnseignant($code,$connexion))."  ".strtoupper(getNomEnseignant($code,$connexion));?></b></h3>
           
       
</p>
          

    </div>
  
    </div>
 
    
</div>


    

</div>



</div> 
        </div>
        </div>
        </div>
      
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

	
</body>
</html>

<?php 

    
}else{
    header("location: ../login");
}?>
<?php 
include '../php/connexion.php';
include '../php/lib.php';

session_start();

if( $_SESSION['id'] == session_id() and  $_SESSION['role']=="scolarité"){


    if(isset($_POST["demande"]))
    {

        $demande =$_POST["demande"];

        $id_etudiant = getEtudiantByNumeroDemande($demande,$_SESSION["etablissement"],$connexion);

        $annee = getAnneeInscription($connexion,$id_etudiant,$_SESSION["etablissement"]);
        $code_etudiant = getCandidatCodeByInscription($id_etudiant,$connexion);
        $nom_etudiant = getNomEtudiant($code_etudiant,$connexion,$_SESSION["lib_etab"]);
        $prenom_etudiant = getPrenomEtudiant($code_etudiant,$connexion,$_SESSION["lib_etab"]);
       

        $date_naissance = getDateNaissanceCandidat($code_etudiant,$_SESSION["lib_etab"],$connexion);
        $lieu_naissance = getLieuNaissanceCandidat($code_etudiant,$_SESSION["lib_etab"],$connexion);

        $specialite = getSpecialitetudiant($id_etudiant,$_SESSION["etablissement"],$connexion);
        $niveau = getNiveauEtudiant($id_etudiant,$_SESSION["etablissement"],$connexion);
      $classe = getClasseByInscription($id_etudiant,$connexion);
      $parcours = $_POST["parcours"];
      $specialite =$_POST["specialite"];
      $session=$_POST["session"];
      $mention = $_POST["mention"];

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

</head>
<body>

    <!--*******************
        Preloader start
    ********************-->
    <div id="preloader">
    <img src="../images/univ.png" class="logo-filigrane" alt="Logo en filigrane">
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
<div class="row custom-font" style="height: 250px;"  >
    <div class="col-md-12">
     <div class="d-flex justify-content-between align-items-center" >
        <!-- Université Denis Sassou Nguesso -->
        <div class="p-2"> <h4 > <b> UNIVERSITE DENIS SASSOU-N'GUESSO</b> </h4>                    
            <h5>DIRECTION DE LA SCOLARITE ET DES EXAMENS</h5>
          </div>
        <div class="p-2">
          <img src="../images/univ.png" alt="Logo de l'université" style="max-width: 150px;">
        </div>
        <!-- Devise -->
        <div class="p-2">  <h4>Rigueur-Excellence-Lumieres</h4>

     
       </div>
    
        <!-- Logo -->
     
      </div>
    </div>
  </div>

  <div class="row" style="height: 50px;" >
  <label class="col-sm-12 col-form-label">...................   /<?php echo $_SESSION["etablissement"]; ?>/UDSN</label>
              
  </div>

				<div class="row  justify-content-center align-items-center mb-50">
                 
                    
                 
                   
                   <h1 class="custom-font">ATTESTATION DE SUCCES</h1>
                
                     
                       
                       
                </div>
              

           
               
    

<div class="row row h-100 justify-content-center align-items-center">
    <div class="col-md-12">
    <div class="form-group row mb-0">
               <label class="col-sm-12 col-form-label custom-font"> <h3>Le président de <b>L'UNIVERSITE DENIS SASSOU-N'GUESSO</b>, soussigné, atteste  que : <br></h3>
       </label>
</div>
<div class="form-group row mb-0">
  <label class="col-sm-12 col-form-label  custom-font"> <h3> <br> Mr, Mme : <b><?php echo $nom_etudiant."     ".$prenom_etudiant;?></b> <br></h3>
       </label>
</div>

<div class="form-group row">
  <label class="col-sm-12 col-form-label custom-font"> <h3> <br> Né(e) le <b><?php echo $date_naissance;?></b>          à <?php echo $lieu_naissance;?> <br> <br></h3>
       </label>
</div>
<div class="form-group row">
  <label class="col-sm-12 col-form-label custom-font"> <h3><i>a satisfait aux conditions prescrites pour l'obtention de la licence et lui descerne l'attestation de succès de </i> <br></h3>
       </label>
</div>
</div>
</div>
<div class="row  justify-content-center align-items-center">
                 
  <h1 class="custom-font">LICENCE</h1>
     
</div> 
<div class="row row h-100 justify-content-center align-items-center">
    <div class="col-md-12">
<div class="form-group row">
  <label class="col-sm-12 col-form-label custom-font"> <h3><i>de </i>  : <b><?php echo $parcours;?></b> ,  Specialité: <b><?php echo $specialite;?></b> <br></h3>
       </label>
</div>
<div class="form-group row">
  <label class="col-sm-12 col-form-label custom-font"> <h3><i> session de </i>: <b><?php echo $session;?></b> <br></h3>
       </label>
</div>
<div class="form-group row">
  <label class="col-sm-12 col-form-label custom-font"> <h3><i>avec la mention : <b><?php echo $mention;?></b></i></h3>
       </label>
</div>
<div class="form-group row">
  <label class="col-sm-12 col-form-label custom-font"> <h3><i>pour jouir avec les droits et prérogatives qui y sont attachés.</i></h3>
       </label>
</div>  
       
      
    </div>
    

    </div>

</div>

<div class="row h-100 justify-content-center">
    <div class="col-sm-8">
    <div class="float-right" >
    <h3 style="height: 25px" class="custom-font">Fait à Kintélé le               </h3>
             <h3 class="custom-font">Pour le Président et par délégation,</h3>
             <h3 class="custom-font" style="height: 65px;">Le Directeur de la Scolarité et des Examens</h3>
             <p><?php $id=getNum("Diplome",$connexion,$_SESSION["etablissement"]) ;
             
?> <img height="100" width="250" src="<?php echo getSignatureData0($connexion,"Diplome")?>" alt=""></p>

<p>
<h3 class="custom-font"><b> <?php $code = getCodeEnseignant($id,$connexion);
              echo mettrePremieresLettresMajuscules(getPrenomEnseignant($code,$connexion))."  ".getNomEnseignant($code,$connexion);?></b><br><?php echo mettrePremieresLettresMajuscules( getGradeById($id,$connexion));?> CAMES</h3>
            
             
           
       
</p>
       

    </div>
    </div>
    <p><b>****************************************************************************************************************************************</b></p>
   <div class="col-sm-12 custom-font">
    <p>Cette attestion pour être valable, ne doit <b>ni surchargée,ni grattée</b>.
    IL NE PEUT ETRE DELIVRE QU'UN SEUL EXEMPLAIRE pour une validité couvrant une période de <b>douze ( 12)</b> à compter de la date de délivrance L'interessé en fera les copies qui lui seront necessaires, les fera certifier conforme à l'original par la Direction 
    de la Scolarité et des Examens</p>
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
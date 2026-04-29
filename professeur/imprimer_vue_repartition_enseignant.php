<?php 
include '../php/connexion.php';
include '../php/lib.php';

session_start();

error_reporting(E_ALL);
ini_set('display_errors', 1);

$ue = "";
$specialite="";
$semestre="";




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
            <div class="row" style="height: 250px;">
    <div class="col-md-12">
      <div class="d-flex justify-content-between align-items-center">
        <!-- Université Denis Sassou Nguesso -->
        <div class="p-2"> <h4 class="justify-content-center custom-font"> <b>UNIVERSITE DENIS SASSOU N'GUESSO</b> </h4>
            <h5 class="justify-content-center custom-font">DIRECTION DE LA SCOLARITE ET DES EXAMENS</h5>
           <p class="justify-content-center custom-font">SERVICE DE LA SCOLARITE ET DES EXAMENS</p></div>
        <div class="p-2">
          <img src="../images/univ.png" alt="Logo de l'université" style="max-width: 100px;">
        </div>
        <!-- Devise -->
        <div class="p-2">  <h4 class="custom-font">Rigueur-Excellence-Lumieres</h4>
       <p><?php  echo mb_strtoupper($_SESSION["lib_etab"]);?></p>
       <?php 
          if(typeEtablissement($_SESSION["lib_etab"],$connexion) =="faculté"){

                 ?>
                 <p class="justify-content-center custom-font">VICE-DECANAT</p>
                 <?php }?>
</div>
    
        <!-- Logo -->
     
      </div>
    </div>
  </div>
 
				<div class="row  justify-content-center align-items-center">
                    
                   
                       <h1 class="custom-font">Repartition des enseignants par classe </h1>
                       
                        
                       
                       
                       
                </div>
               
                
    

<div class="row row h-100 justify-content-center align-items-center" style="height: 500px;">
  


<div class="container mt-5">


   								<div class="table-responsive">
									
									<table class="table table-striped" >
								
										<thead>
											<tr>
												
												<th>N°</th>
												<th>Libelle Classe</th>
												<th>Ecue</th>
                                                <th>UE</th>
												<th>Semestre</th>
												<th>Enseignant</th>
												<th>Grade</th>
												<th>Année académique</th>
											
											</tr>
										</thead>
										<tbody>
                                          <?php 
                                                 $sql ="select distinct * from repartition_enseignant where etab='".$_SESSION['etablissement']."' and code='".$_SESSION['code_enseignant']."' and annee='".$_SESSION['annee']."'";
                                                 $resultat =$connexion->query($sql);
                                                                                                      $count=1;
                                                 while($ue=$resultat ->fetch_assoc()){

                                           ?>
											<tr>
												
												<td><?php echo $count ;?></td>
												<td><?php echo str_replace("+","'", $ue['classe']);?></td>
												<td><?php echo str_replace("+","'",$ue['ecue']);?></td>
                                                <td><?php echo str_replace("+","'", $ue['libelle_ue']);?></td>
                                                <td><?php echo  $ue['semestre'];?></td>	
                                                <td><?php echo  $ue['nom']."  ".$ue['prenom'];?></td>
                                                <td><?php echo  str_replace("+","'",$ue['grade']);?></td>
                                                <td><?php echo  $ue['annee'];?></td>
                                                
												<!--<td>

                                                 <a href="traitement1?suprep=<?php //echo $ue['id'];?>" class="btn btn-sm btn-danger"><i class="la la-trash-o"></i></a>
                                                </td>	-->											
											</tr>

                                            <?php $count=$count+1;} ?>
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


	
</body>
</html>

<?php 

 ?>
<?php 
include '../php/connexion.php';
include '../php/lib.php';

session_start();

if( $_SESSION['id'] == session_id() and  $_SESSION['role']=="gesnote"){


    if(isset($_GET["classe"]) and isset($_GET["semestre"]) and isset($_GET["annee"]) and isset($_GET["etudiant"]))
    {
      $nom_etudiant = obtenirNomPrenom(getCandidatCodeByInscription($_GET['etudiant'],$connexion),$_GET['annee'],$connexion);
     

      $classe = $_GET["classe"];
      $annee =$_GET["annee"];
      $semestre=$_GET["semestre"];
     $specialite = getSpecialiteClasse($connexion,$classe);
     $etudiant = $_GET["etudiant"];
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
           <p>SERVICE DE LA SCOLARITE ET DES EXAMENS</p></div>
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
                    
                   
                       <h1 class="custom-font">RELEVE  DE NOTES ET RESULTATS</h1>
                       
                       
                </div>
                <div class="row  justify-content-center align-items-center">
                    
                   
                    <h2 class="custom-font">Année Universitaire : <?php echo $annee;?></h2>
                       
                       
                </div>

                <div class="row  justify-content-center align-items-center">
                    
                   
                    <h2 class="custom-font">Classe :  <?php echo $classe;?></h2>
                       
                       
                </div>
              
                <div class="row  justify-content-center align-items-center">
                    
                   
                    <h2 class="custom-font">Specialité :  <?php echo $specialite;?></h2>
                       
                       
                </div>
                  <div class="row  justify-content-center align-items-center">
                    
                   
                    <h2 class="custom-font">Parcours :  <?php echo (getParcours($specialite,$connexion));?></h2>
                       
                       
                </div>
                  <div class="row  justify-content-center align-items-center">
                    
                   
                    <h2 class="custom-font">Semestre :  <?php echo $semestre;?></h2>
                       
                       
                </div>
                  <div class="row  justify-content-center align-items-center">
                    
                   
                    <h2 class="custom-font">Etudiant :  <?php echo  strtoupper($nom_etudiant);?></h2>
                       
                       
                </div>
    

<div class="row row h-100 justify-content-center align-items-center" style="height: 500px;">
 
 <div class="card">
                        
        <div class="card-body">
        <div class="row">
           
            
       <div class="col-lg-12">
                        <div class="card">
                            <div class="card-body">
                                <div class="table-responsive">
                                   <table class="table table-bordered">
                                  
    <thead class="table-dark">
        <tr>
              <th scope="col"> N°</th>
        <th scope="col"> Code UE</th>
            <th scope="col">Unité d'enseignement</th>
            <th scope="col">Moy. UE</th>
        
            <th scope="col">ECUE</th>
            <th scope="col">Note sur 20 ( Moyenne Devoir)</th>
            <th scope="col">Note sur 20 ( Moyenne Examen)</th>

            <th scope="col">Crédit</th>
  
        </tr>
    </thead>
    <tbody>
                                            <?php
                                            $sql_ue = "SELECT libelle, code FROM ue 
                                                       WHERE etab='".$connexion->real_escape_string($_SESSION["etablissement"])."' 
                                                       AND Specialite='".$connexion->real_escape_string($specialite)."' 
                                                       AND semestre='".$connexion->real_escape_string($semestre)."'";
                                            
                                            $result_ue = $connexion->query($sql_ue);

                                            if ($result_ue && $result_ue->num_rows > 0) {
                                                $count = 1;
                                                
                                                while ($row_ue = $result_ue->fetch_assoc()) {
                                                    $ue_nom = $row_ue["libelle"];
                                                    $code_ue = $row_ue["code"];
                                                    
                                                    $sql_ecue = "SELECT e.code_ecue as code_ecue, e.libelle as libelle, e.credit as credit, 
                                                                v.classe, v.specialite 
                                                                FROM ecue e
                                                                JOIN vue_repartition v ON e.code_ecue=v.code_ecue 
                                                                WHERE e.code_ue='".$connexion->real_escape_string($code_ue)."' 
                                                                AND v.specialite='".$connexion->real_escape_string($specialite)."' 
                                                                AND v.classe='".$connexion->real_escape_string($classe)."'";
                                                    
                                                    $result_ecue = $connexion->query($sql_ecue);
                                                    $rowspan = ($result_ecue && $result_ecue->num_rows > 0) ? $result_ecue->num_rows : 1;
                                                    
                                                    if ($result_ecue && $result_ecue->num_rows > 0) {
                                                        $first_row = true;
                                                        while ($row_ecue = $result_ecue->fetch_assoc()) {
                                                            echo "<tr>";
                                                            
                                                            if ($first_row) {
                                                                $moyenneUE = getMoyenneUE($connexion, $etudiant, $semestre, $annee, $code_ue, $_SESSION['etablissement']);
                                                                
                                                                echo "<td rowspan='$rowspan'>$count</td>";
                                                                echo "<td rowspan='$rowspan'>" . str_replace("+", "'", $code_ue) . "</td>";
                                                                echo "<td rowspan='$rowspan'>" . str_replace("+", "'", $ue_nom) . "</td>";
                                                                echo "<td rowspan='$rowspan'>" . $moyenneUE . "</td>";
                                                                $first_row = false;
                                                            }
                                                            
                                                            $notes = getNoteDevoir($connexion, $etudiant, $semestre, $annee, $row_ecue["code_ecue"]);
                                                            $notesEx = getNoteExamen($connexion, $etudiant, $semestre, $annee, $row_ecue["code_ecue"]);
                                                            
                                                            $notes = ($notes !== "-") ? round($notes, 2) : $notes;
                                                            $notesEx = ($notesEx !== "-") ? round($notesEx, 2) : $notesEx;
                                                            
                                                            echo "<td>" . str_replace("+", "'", $row_ecue["libelle"]) . "</td>";
                                                            echo "<td>" . $notes . "</td>";
                                                            echo "<td>" . $notesEx . "</td>";
                                                            echo "<td>" . $row_ecue["credit"] . "</td>";
                                                            echo "</tr>";
                                                        }
                                                    } else {
                                                        echo "<tr>";
                                                        echo "<td>$count</td>";
                                                        echo "<td>" . str_replace("+", "'", $code_ue) . "</td>";
                                                        echo "<td>" . str_replace("+", "'", $ue_nom) . "</td>";
                                                        echo "<td>0.00</td>";
                                                        echo "<td colspan='4'>Aucune ECUE trouvée</td>";
                                                        echo "</tr>";
                                                    }
                                                    
                                                    $count++;
                                                }
                                            } else {
                                                echo "<tr><td colspan='8' class='text-center'>Aucune UE trouvée</td></tr>";
                                            }
                                            ?>
                                        </tbody>
</table>


                                </div>
                                
                                <?php
                                
                                $moyenne = calcul_moyenne($etudiant,$semestre,$annee,$_SESSION['etablissement'],$connexion);
                                
                                ?>
                              <p>Année académique :  <b><?php echo $annee;?></b></p>
                            <p> Moyenne du <?php echo $semestre ?> : <b> <i><?php echo ($moyenne == "-") ? '-' : $moyenne; ?></b></i> </p>
                            <p>Decision du jury : <b><i ><?php echo ($moyenne == "-") ? '-' : statutSoutenance($moyenne);?></i> </b></p>
                            <p> Mention : <b> <i><?php echo ($moyenne == "-") ? '-' :mentionParmoyenne($moyenne);?></b></i> </p>
                            </div>
                        </div>
                    </div>
 </div>
</div>
   



</div>

    </div>

</div>

<div class="row h-100 justify-content-center custom-font">
    <div class="col-sm-8">
    <div class="float-right" >
    <h4 style="height: 25px">Fait à Kintélé le ...........................................................................</h4>
             <h4 style="height: 50px">Le Directeur de la Scolarité et des Examens</h4>
             <h3 class="custom-font">Cyr Jonas MORABANDZA</h3>
       

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
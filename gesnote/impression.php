<?php 
include '../php/connexion.php';
include '../php/lib.php';

session_start();

if( $_SESSION['id'] == session_id() and  $_SESSION['role']=="gesnote"){


    if(isset($_GET["classe"]) and isset($_GET["semestre"]) and isset($_GET["annee"]))
    {

      $classe = $_GET["classe"];
      $annee =$_GET["annee"];
      $semestre=$_GET["semestre"];
     $specialite = getSpecialiteClasse($connexion,$classe);
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
                    
                   
                    <h2 class="custom-font">Semestre :  <?php echo $semestre;?></h2>
                       
                       
                </div>
               
    

<div class="row row h-100 justify-content-center align-items-center" style="height: 500px;">
  <table  id="example3" class="display">
    <thead>
        <tr>
             <th scope="col">Etudiant</th>
           
           <th scope="col">  N°-UE</th>
            <th scope="col">code-ue</th>
            <th scope="col">libelle-ue</th>
            <th scope="col">moyenne-ue</th>
            <th scope="col">libelle-ecue</th>
            <th scope="col">moyenne-devoir</th>
             <th scope="col">moyenne-examen</th>
            <th scope="col">Crédit</th>
        </tr>
    </thead>
    
    
    
   <tbody>
    <?php
    // Requête SQL pour récupérer tous les étudiants
    $sql_students = "SELECT inscription.id, candidat.nom, candidat.prenom FROM inscription join candidat on inscription.candidat=candidat.code  WHERE inscription.etab='" . $_SESSION["etablissement"] . "' and inscription.classe='$classe' and inscription.annee='$annee'";
    $result_students = $connexion->query($sql_students);
    
    // Boucle sur les étudiants
    if ($result_students->num_rows > 0) {
        while ($row_student = $result_students->fetch_assoc()) {
            $etudiant = $row_student['id'];
            $nom_etudiant = $row_student['nom'];
            $prenom_etudiant = $row_student['prenom'];
            
            // Requête SQL pour récupérer les données des tables UE et ECUE
            $sql_ue = "SELECT DISTINCT ue.code as code, libelle FROM ue WHERE ue.etab='" . $_SESSION["etablissement"] . "' AND specialite='" . $specialite . "' AND semestre='" . $semestre . "'";
            $result_ue = $connexion->query($sql_ue);
            
            // Compter le nombre total d'ECUE pour l'étudiant
            $total_ecue = 0;
            $ue_info = [];
            
            if ($result_ue->num_rows > 0) {
                $count = 1;
                // Première boucle pour compter les ECUE et stocker les informations
                mysqli_data_seek($result_ue, 0);
                while ($row_ue = $result_ue->fetch_assoc()) {
                    $code_ue = $row_ue["code"];
                    
                    $sql_ecue = "SELECT ecue.code_ecue, ecue.libelle, ecue.credit, vue_repartition.classe, vue_repartition.specialite 
                                FROM ecue JOIN vue_repartition ON ecue.code_ecue = vue_repartition.code_ecue 
                                WHERE ecue.code_ue = '" . $code_ue . "' 
                                AND specialite = '" . $specialite . "' 
                                AND classe = '" . $classe . "'";
                    $result_ecue = $connexion->query($sql_ecue);
                    $ecue_count = $result_ecue->num_rows;
                    $total_ecue += $ecue_count;
                    
                    $ue_info[] = [
                        'code' => $code_ue,
                        'libelle' => $row_ue["libelle"],
                        'ecue_count' => $ecue_count,
                        'count' => $count++
                    ];
                }
                
                // Afficher la ligne de l'étudiant avec rowspan couvrant toutes les ECUE
                echo "<tr>";
                echo "<td rowspan='" . max(1, $total_ecue) . "' style='font-weight: bold;'>" . htmlspecialchars($prenom_etudiant . ' ' . $nom_etudiant) . "</td>";
                
                // Afficher la première UE
                if (!empty($ue_info)) {
                    $first_ue = $ue_info[0];
                    $rowspan_ue = max(1, $first_ue['ecue_count']);
                    
                    echo "<td rowspan='" . $rowspan_ue . "'>" . $first_ue['count'] . "</td>";
                    echo "<td rowspan='" . $rowspan_ue . "'>" . str_replace("+", "'", $first_ue['code']) . "</td>";
                    echo "<td rowspan='" . $rowspan_ue . "'>" . str_replace("+", "'", $first_ue['libelle']) . "</td>";
                    echo "<td rowspan='" . $rowspan_ue . "'>" . getMoyenneUE($connexion, $etudiant, $semestre, $annee, $first_ue['code'], $_SESSION['etablissement']) . "</td>";
                    
                    // Afficher les ECUE de la première UE
                    $sql_ecue = "SELECT ecue.code_ecue, ecue.libelle, ecue.credit 
                                FROM ecue JOIN vue_repartition ON ecue.code_ecue = vue_repartition.code_ecue 
                                WHERE ecue.code_ue = '" . $first_ue['code'] . "' 
                                AND specialite = '" . $specialite . "' 
                                AND classe = '" . $classe . "'";
                    $result_ecue = $connexion->query($sql_ecue);
                    
                    if ($result_ecue->num_rows > 0) {
                        $first_ecue = true;
                        while ($row_ecue = $result_ecue->fetch_assoc()) {
                            if (!$first_ecue) {
                                echo "<tr>";
                            }
                            
                            $notes = getNoteDevoir($connexion, $etudiant, $semestre, $annee, $row_ecue["code_ecue"]);
                            $notesEx = getNoteExamen($connexion, $etudiant, $semestre, $annee, $row_ecue["code_ecue"]);
                            
                            echo "<td>" . str_replace("+", "'", $row_ecue["libelle"]) . "</td>";
                            echo "<td>" . $notes . "</td>";
                            echo "<td>" . $notesEx . "</td>";
                            echo "<td>" . $row_ecue["credit"] . "</td>";
                            echo "</tr>";
                            
                            $first_ecue = false;
                        }
                    } else {
                        echo "<td colspan='4'>Aucune ECUE trouvée</td>";
                        echo "</tr>";
                    }
                    
                    // Traiter les UE restantes
                    for ($i = 1; $i < count($ue_info); $i++) {
                        $ue = $ue_info[$i];
                        $rowspan_ue = max(1, $ue['ecue_count']);
                        
                        echo "<tr>";
                        echo "<td rowspan='" . $rowspan_ue . "'>" . $ue['count'] . "</td>";
                        echo "<td rowspan='" . $rowspan_ue . "'>" . str_replace("+", "'", $ue['code']) . "</td>";
                        echo "<td rowspan='" . $rowspan_ue . "'>" . str_replace("+", "'", $ue['libelle']) . "</td>";
                        echo "<td rowspan='" . $rowspan_ue . "'>" . getMoyenneUE($connexion, $etudiant, $semestre, $annee, $ue['code'], $_SESSION['etablissement']) . "</td>";
                        
                        // Afficher les ECUE
                        $sql_ecue = "SELECT ecue.code_ecue, ecue.libelle, ecue.credit 
                                    FROM ecue JOIN vue_repartition ON ecue.code_ecue = vue_repartition.code_ecue 
                                    WHERE ecue.code_ue = '" . $ue['code'] . "' 
                                    AND specialite = '" . $specialite . "' 
                                    AND classe = '" . $classe . "'";
                        $result_ecue = $connexion->query($sql_ecue);
                        
                        if ($result_ecue->num_rows > 0) {
                            $first_ecue = true;
                            while ($row_ecue = $result_ecue->fetch_assoc()) {
                                if (!$first_ecue) {
                                    echo "<tr>";
                                }
                                
                                $notes = getNoteDevoir($connexion, $etudiant, $semestre, $annee, $row_ecue["code_ecue"]);
                                $notesEx = getNoteExamen($connexion, $etudiant, $semestre, $annee, $row_ecue["code_ecue"]);
                                
                                echo "<td>" . str_replace("+", "'", $row_ecue["libelle"]) . "</td>";
                                echo "<td>" . $notes . "</td>";
                                echo "<td>" . $notesEx . "</td>";
                                echo "<td>" . $row_ecue["credit"] . "</td>";
                                echo "</tr>";
                                
                                $first_ecue = false;
                            }
                        } else {
                            echo "<td colspan='4'>Aucune ECUE trouvée</td>";
                            echo "</tr>";
                        }
                    }
                } else {
                    echo "<td colspan='8'>Aucune UE trouvée</td>";
                    echo "</tr>";
                }
            } else {
                echo "<tr>";
                echo "<td style='font-weight: bold;'>" . htmlspecialchars($prenom_etudiant . ' ' . $nom_etudiant) . "</td>";
                echo "<td colspan='8'>Aucune UE trouvée</td>";
                echo "</tr>";
            }
        }
    } else {
        echo "<tr><td colspan='9'>Aucun étudiant trouvé</td></tr>";
    }
    ?>
</tbody>
</table>


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
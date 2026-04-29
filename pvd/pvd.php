<?php 

error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
include '../php/connexion.php';
include '../php/lib.php';


session_start();



if( $_SESSION['id'] == session_id() and  $_SESSION['role']=="pvd"){


   // if(utilisateurDateLimiteDepassee($connexion,$_SESSION["id_user"]) === TRUE)

   if(isset($_GET['semestre']) and isset($_GET['specialite']) and isset($_GET['annee']) and isset($_GET['examen']))
{
  $semestre = urldecode($_GET["semestre"]); // "semestre 3" au lieu de "semestre%203"
        $specialite = $_GET["specialite"]; // "Biologie de l'Environnement 2"
        $annee = urldecode($_GET["annee"]); // "2025-2026"
        $examen = urldecode($_GET["examen"]); // "ordinaire"
        $niveau = urldecode($_GET["niveau"]); // "Deuxième année"
        $classe = urldecode($_GET["classe"]); // "L2BEN"
        $parcours = $_SESSION["parcours"];
     
        
        // ECHAPPER LES VALEURS POUR SECURITE SQL
        $semestre = mysqli_real_escape_string($connexion, $semestre);
        $specialite =  str_replace("'", "+" ,  $specialite);
        $annee = mysqli_real_escape_string($connexion, $annee);
        $examen = mysqli_real_escape_string($connexion, $examen);
        $niveau = mysqli_real_escape_string($connexion, $niveau);
        $classe = mysqli_real_escape_string($connexion, $classe);
        
    
    // Cette ligne n'existe pas dans votre code !
$etablissement = $_GET["etablissement"];
$etablissement_libelle = getLibelleEtablissement($etablissement,$connexion);

     $etablissement = mysqli_real_escape_string($connexion, $etablissement);
      

 
    


   
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

    /* Styles pour l'impression */
    @media print {
        @page {
            size: landscape;
            margin: 10mm;
        }
        
        body * {
            visibility: hidden;
        }
        
        #printable-area, #printable-area * {
            visibility: visible;
        }
        
        #printable-area {
            position: absolute;
            left: 0;
            top: 0;
            width: 100%;
        }
        
        .no-print {
            display: none !important;
        }
        
        table {
            font-size: 10px;
        }
        
        th, td {
            padding: 4px;
        }
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
                    <h4 class="card-title">Procès verbal de délibération</h4>
                    <button type="button" class="btn btn-primary mt-2 no-print" id="publierResultats">
                        <i class="fa fa-paper-plane"></i> Publier les résultats
                    </button>
                    <button type="button" class="btn btn-info mt-2 no-print" onclick="window.print()">
                        <i class="fa fa-print"></i> Imprimer
                    </button>
                </div>
            </div>
            <div class="col-sm-6 p-md-0 justify-content-sm-end mt-2 mt-sm-0 d-flex">
                <ol class="breadcrumb no-print">
                    <li class="breadcrumb-item"><a href="index">Tableau de Bord</a></li>
                    <li class="breadcrumb-item"><a href="recap">Recap</a></li>
                </ol>
            </div>
        </div>
        
        <div id="printable-area">
        <div class="row">
            <div class="col-lg-12">
                <h4 class="card-title">Président du Jury : <?php echo getNomUtilisateurParId($connexion,$_SESSION["id_user"]);?> </h4>
            </div>
            
            <div class="col-lg-12">
                <div class="row tab-content">
                    <div id="list-view" class="tab-pane fade active show col-lg-12">
                        <div class="card">
                            <div class="card-header">
                                
                                 <h4 class="card-title">Specialité : <?php echo $specialite;?> </h4>
                                <h4 class="card-title">Semestre : <?php echo $semestre;?> </h4>
                                <h4 class="card-title">Année universitaire : <?php echo $annee;?> </h4>
                                <h4 class="card-title">Examen : <?php echo $examen;?> </h4>
                                <h4 class="card-title">Classe : <?php echo $classe;?> </h4>
                                
                                <!-- Légende des couleurs -->
                                <div class="alert alert-info mt-3 no-print" role="alert">
                                    <strong>Légende de saisie :</strong>
                                    <span class="badge badge-success">■ Complet (100%)</span>
                                    <span class="badge badge-warning">■ En cours (80-99%)</span>
                                    <span class="badge badge-danger">■ Incomplet (&lt;80%)</span>
                                </div>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                <?php 
                                   $sql = "select distinct ue.code,libelle from ue  where ue.etab='".$etablissement."' and specialite='".$specialite."' and semestre='$semestre' and niveau='$niveau'";
                            ?>

<table id="example3" class="display" >
    <thead>
        <tr>
      
            <th rowspan="3">Nom(s) et prénom(s)</th>
          
          

            <?php 

          
            $result_ue =$connexion->query($sql);
            while($data=$result_ue ->fetch_object()){

                $sql_="select * from ecue where code_ue='".$data->code."'";
                $result_ecue=$connexion->query($sql_);
                $colspan= ($result_ecue->num_rows > 0) ? ($result_ecue->num_rows*3)+1 : 1;
            
            ?>
            <th colspan="<?php echo $colspan;?>"><?php echo str_replace("+","'",$data->libelle)?></th>
        <?php }?>

         
          
            <th rowspan="3">UE validées sur <?php echo $result_ue->num_rows; ?></th>
            <th rowspan="3">Moyenne Generale</th>
            <th rowspan="3">Appréciation</th>
            <th rowspan="3">Decision du jury</th>
         
            
            
        </tr>
        <tr>

        <?php 
         
           $result_ue =$connexion->query($sql);
       
           while($data=$result_ue ->fetch_object()){
         
               $sql_="select * from ecue where code_ue='".$data->code."'";
               $result_ecue=$connexion->query($sql_);
               $i=0;
               while($ecue =$result_ecue->fetch_object()){
                $i++;
           
           ?>
        
            <th colspan="3"><?php echo str_replace("+","'",$ecue->libelle)?></th>

            <?php if(( $i == $result_ecue->num_rows) ){?>
          
              <th rowspan="2">Moy UE</th>
           
          <?php } }}?>
            <!-- Ajoutez d'autres colonnes ECUE selon vos besoins -->
        </tr>
        <tr>
            <?php 
               
                  $result_ue =$connexion->query($sql);
              
                  while($data=$result_ue ->fetch_object()){
                        $i=0;
                      $sql_="select * from ecue where code_ue='".$data->code."'";
                      $result_ecue=$connexion->query($sql_);
                while($ecue = $result_ecue->fetch_object()){
                    $i++;  
                  
            ?>
            <th class="small-column">CC</th>
            <th class="small-column">EX .T</th>
            <th class="small-column">MOY. ECUE</th>
          
          <?php }}?>
          
          
        </tr>
    </thead>
    <tbody>

    <?php 
    
            $sql1 = "SELECT
                                    inscription.id,
    candidat.code AS candidat,
    candidat.nom AS nom,
    candidat.prenom AS prenom
FROM inscription
JOIN candidat ON candidat.code = inscription.candidat
JOIN classe ON inscription.classe = classe.libelle
JOIN specialite ON classe.specialite = specialite.libelle
JOIN parcours ON specialite.parcours = parcours.libelle
WHERE classe.libelle = '$classe'
  AND parcours.libelle = '$parcours'
GROUP BY  candidat.nom, candidat.prenom
ORDER BY LOWER(nom), LOWER(prenom);
";
          $r = $connexion->query($sql1);
          $num = 0;
          while($etudiant = $r ->fetch_object()){
            $num++;

            $points = 0;
    ?>

        <tr>
            
            <th><?php echo  mettrePremieresLettresMajuscules(getNomEtudiant(getCandidatCodeByInscription($etudiant->id,$connexion),$connexion,$etablissement_libelle)."  ".getPrenomEtudiant(getCandidatCodeByInscription($etudiant->id,$connexion),$connexion,$etablissement_libelle));?></th>
            
            
            <?php 
               // Tableau pour stocker les UE validées
               $ue_validees_count = 0;
               $a_note_eliminatoire_globale = false;
               
               $result_ue =$connexion->query($sql);
           
               while($data=$result_ue ->fetch_object()){
                     $i=0;
                   $sql_="select * from ecue where code_ue='".$data->code."'";
                   $result_ecue=$connexion->query($sql_);
                   
                   // Tableaux pour cette UE spécifique
                   $moyennes_ecue_ue = array();
                   $a_note_eliminatoire_ue = false;
                   
            
             while($ecue = $result_ecue->fetch_object()){
                 $i++;  

                 $a=getEtudiantCC($etudiant->id,$connexion,$etablissement,$semestre,$ecue->code_ecue,$annee);

                 if($examen == "ordinaire")
                 {
                    $b= getEtudiantEXT($etudiant->id,$connexion,$etablissement,$semestre,$ecue->code_ecue,$annee);
                 

                 }else{
                    $b= getEtudiantRattrapage($etudiant->id,$connexion,$etablissement,$semestre,$ecue->code_ecue,$annee);
                 
                 }
                 
                 // Calculer la moyenne de l'ECUE
                 $moy_ecue = "-";
                 if($a !== "-" and $b !== "-") {
                     $moy_ecue = round(($a+$b)/2, 2);
                     $moyennes_ecue_ue[] = $moy_ecue;
                     
                     // Vérifier si c'est une note éliminatoire (< 6)
                     if($moy_ecue < 6) {
                         $a_note_eliminatoire_ue = true;
                         $a_note_eliminatoire_globale = true;
                     }
                 }
               
         ?>
            <th class="small-column"> <?php echo ($a !== "-") ? $a : "-" ;?></th>
            <th class="small-column text-danger"> <?php echo ($b !== "-") ? $b: "-";?></th>
            <th class="small-column text-primary"> <?php echo $moy_ecue; ?></th>
            
            <?php if(( $i == $result_ecue->num_rows) ){
                // Calculer la moyenne de l'UE
                $ue_moy = "-";
                
                if(count($moyennes_ecue_ue) > 0) {
                    $somme_ecue = array_sum($moyennes_ecue_ue);
                    $nb_ecue = count($moyennes_ecue_ue);
                    $ue_moy = round($somme_ecue / $nb_ecue, 2);
                    
                    // Valider l'UE si :
                    // 1. La moyenne de l'UE >= 10
                    // 2. Aucune note éliminatoire (< 6) dans les ECUEs
                    if($ue_moy >= 10 && !$a_note_eliminatoire_ue) {
                        $ue_validees_count++;
                    }
                }
            ?>
                
          
             <th class="small-column text-secondary"> <?php echo $ue_moy; ?></th>
       
           <?php 
               } 
         } 
       } ?>

       <th class="small-column"> <?php echo $ue_validees_count; ?></th>
       
       <th class="small-column <?php 
    // Count total ECUEs and ECUEs with data
    $total_ecues = 0;
    $ecues_with_data = 0;
    
    $result_ue_count = $connexion->query($sql);
    while($data_count = $result_ue_count->fetch_object()){
        $sql_count = "select * from ecue where code_ue='".$data_count->code."'";
        $result_ecue_count = $connexion->query($sql_count);
        
        while($ecue_count = $result_ecue_count->fetch_object()){
            $total_ecues++;
            $cc = getEtudiantCC($etudiant->id,$connexion,$etablissement,$semestre,$ecue_count->libelle,$annee);
            
            if($examen == "ordinaire"){
                $ex = getEtudiantEXT($etudiant->id,$connexion,$etablissement,$semestre,$ecue_count->libelle,$annee);
            } else {
                $ex = getEtudiantRattrapage($etudiant->id,$connexion,$etablissement,$semestre,$ecue_count->libelle,$annee);
            }
            
            if($cc !== "-" && $ex !== "-"){
                $ecues_with_data++;
            }
        }
    }
    
    // Calculate percentage of ECUEs with data
    $percentage_complete = ($total_ecues > 0) ? ($ecues_with_data / $total_ecues) * 100 : 0;
    
    // Calculer la moyenne si au moins une ECUE a des notes
    if($ecues_with_data > 0){
        $tt = calcul_moyenne($etudiant->id,$semestre,$annee,$_SESSION['etablissement'],$connexion);
        
        // Définir la couleur selon le taux de complétion
        if($percentage_complete == 100) {
            echo 'text-success'; // Vert = complet
        } elseif($percentage_complete >= 80) {
            echo 'text-info'; // Bleu = presque complet (au lieu de warning/jaune)
        } else {
            echo 'text-danger'; // Rouge = incomplet
        }
    } else {
        $tt = "-";
        echo 'text-dark';
    }
?>"> <?php 
    if($ecues_with_data > 0){
        echo $tt;
        // Afficher le pourcentage de complétion si ce n'est pas 100%
        if($percentage_complete < 100) {
            echo '<br><small style="font-size:9px;">(' . round($percentage_complete) . '%)</small>';
        }
    } else {
        echo "-";
    }
?></th>

      <th class="small-column text-primary"> <?php 
    // APPRÉCIATION = Mention basée uniquement sur la moyenne
    if($tt !== "-"){
        echo mentionParmoyenne($tt,2);
    } else {
        echo "-";
    }
?></th>
          
<th class="small-column"> <?php
$result = "-";

if($tt !== "-"){
    // DÉCISION DU JURY = Statut avec logique de note éliminatoire
    $statut = "";
    
    // Déterminer le statut (Admis/Ajourné) basé sur la moyenne
    if($result_ue->num_rows - 1 >= 1) {
        $statut = statutSoutenance(round($tt, 2));
    }
    
    // NOUVELLE LOGIQUE :
    // Si note éliminatoire présente
    if($a_note_eliminatoire_globale) {
        // Si ADMIS + note éliminatoire → afficher seulement "Note Éliminatoire"
        if(stripos($statut, 'Admis') !== false) {
            $result = "<span class='badge badge-danger'>Note Éliminatoire</span>";
        }
        // Si AJOURNÉ + note éliminatoire → afficher seulement "Ajourné"
        elseif(stripos($statut, 'Ajourné') !== false || stripos($statut, 'Ajourne') !== false) {
            $result = $statut;
        }
        // Autre cas avec note éliminatoire
        else {
            $result = "<span class='badge badge-danger'>Note Éliminatoire</span>";
        }
    } 
    // Pas de note éliminatoire : afficher le statut normal
    else {
        $result = $statut;
    }
}

echo $result;
?></th>     
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
       
    </div>
</div>


<div class="modal no-print" id="messageModal" tabindex="-1" role="dialog" aria-labelledby="messageModalLabel" aria-hidden="true">
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

<script>
    document.getElementById('numericInput').addEventListener('keypress', function(event) {
        if (event.key === 'Enter') {
            event.preventDefault(); // Empêche le comportement par défaut du champ de saisie (déplacer le curseur)
            document.getElementById('myForm').submit(); // Soumet le formulaire
        }
    });
</script>
<script>
    document.getElementById('numericInput1').addEventListener('keypress', function(event) {
        if (event.key === 'Enter') {
            event.preventDefault(); // Empêche le comportement par défaut du champ de saisie (déplacer le curseur)
            document.getElementById('myForm1').submit(); // Soumet le formulaire
        }
    });
</script>

<script>
$(document).ready(function() {
    $('#publierResultats').click(function() {
        if(confirm('Êtes-vous sûr de vouloir publier ces résultats ? Les étudiants pourront les consulter.')){
            
            $(this).prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> Publication en cours...');
            
            $.ajax({
                url: 'publier_resultats.php',
                type: 'POST',
                data: {
                    semestre: '<?php echo $semestre; ?>',
                    specialite: '<?php echo $specialite; ?>',
                    annee: '<?php echo $annee; ?>',
                    examen: '<?php echo $examen; ?>',
                    niveau: '<?php echo $niveau; ?>',
                    classe: '<?php echo $classe; ?>',
                    etablissement: '<?php echo $etablissement; ?>'
                },
                dataType: 'json',
                success: function(response) {
                    if(response.success){
                        alert(response.message);
                        $('#publierResultats').prop('disabled', false).html('<i class="fa fa-check"></i> Résultats publiés');
                    } else {
                        alert('Erreur : ' + response.message);
                        $('#publierResultats').prop('disabled', false).html('<i class="fa fa-paper-plane"></i> Publier les résultats');
                    }
                },
                error: function(xhr, status, error) {
                    console.log('XHR:', xhr);
                    console.log('Status:', status);
                    console.log('Error:', error);
                    console.log('Response Text:', xhr.responseText);
                    
                    alert('Erreur lors de la publication des résultats\n\n' + 
                          'Statut: ' + status + '\n' +
                          'Erreur: ' + error + '\n\n' +
                          'Détails (voir console): ' + xhr.responseText.substring(0, 200));
                    
                    $('#publierResultats').prop('disabled', false).html('<i class="fa fa-paper-plane"></i> Publier les résultats');
                }
            });
        }
    });
});
</script>

</body>


</html>
<?php 

}}else{
    header("location: ../login");
}?>
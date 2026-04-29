<?php 
include '../php/connexion.php';
include '../php/lib.php';

session_start();
$_SESSION['total']=0;

if( $_SESSION['id'] == session_id() and  $_SESSION['role']=="soutenance"){

   if(isset($_GET["soutenance"])){

    $soutenance = $_GET["soutenance"];

     if($soutenance !=="" ){
?>


<!DOCTYPE html>
<html lang="en">

<head>
	
	<meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title><?php echo $_SESSION['univ'];?> - Scolarité de  <?php echo $_SESSION['etablissement'];?> </title>
    <!-- Favicon icon -->
    <link rel="icon" type="image/png" sizes="16x16" href="../images/univ.png">
	<link rel="stylesheet" href="../vendor/bootstrap-select/dist/css/bootstrap-select.min.css">
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
border: 5px solid black; /* Bordure extérieure */
padding: 8px; /* Ajoute un espacement entre le texte et les bordures */
}

.table-bold-rows-cols tbody tr td:not(:first-child),
.table-bold-rows-cols tbody tr th:not(:first-child) {
border-left: 5px solid black; /* Bordure intérieure gauche */
}

.table-bold-rows-cols tbody tr td:not(:last-child),
.table-bold-rows-cols tbody tr th:not(:last-child) {
border-right: 5px solid black; /* Bordure intérieure droite */
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
        .custom-font2 {
            font-family: "Times New Roman", Times, serif;
            font-size: 22px; /* Utilisation de la police Times New Roman, ou Times, ou une police serif si elles ne sont pas disponibles */
        }
        .custom-font {
            font-family: "Times New Roman", Times, serif;
            font-size: 25px; /* Utilisation de la police Times New Roman, ou Times, ou une police serif si elles ne sont pas disponibles */
        }
        .custom-font1 {
            font-family: "Times New Roman", Times, serif;
           /* Utilisation de la police Times New Roman, ou Times, ou une police serif si elles ne sont pas disponibles */
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

   
<div class="content h-50" id="contenu-a-imprimer">
            <!-- row -->
            <div class="container-fluid">
            <div class="row">
    <div class="col-md-12">
      <div class="d-flex justify-content-between align-items-center">
        <!-- Université Denis Sassou Nguesso -->
        <div class="p-2 custom-font1"> <h3 >UNIVERSITE DENIS SASSOU N'GUESSO</h3>
    <h5 >SERVICE DE LA SCOLARITE ET DES EXAMENS</h5></div>
        <div class="p-2">
          <img src="../images/univ.png" alt="Logo de l'université" style="max-width: 100px;">
        </div>
        <!-- Devise -->
        <div class="p-2 custom-font1">  <h3>Rigueur*Excellence*Lumiere</h3></div>
    
        <!-- Logo -->
     
      </div>
    </div>
  </div>
				
				<div class="row page-titles justify-content-center align-items-center custom-font" style="height: 100px;">
                   
                            <h2>Fiche de Notation de la Soutenance du Rapport de Stage de Fin de Formation</h2>
                        
                       
                </div>
				
    <div class="row h-100 justify-content-center align-items-center" >
  

                <?php 
                
                
                         $sql="select * from soutenance join inscription on soutenance.impetrant=inscription.id where soutenance.code='$soutenance'";
                         $resultat=$connexion->query($sql);
                         while($etudiant = $resultat->fetch_assoc()){
                ?>
                <form>
                  
                 <div class="row " style="height: 475px;">
                       
               
                                     
                                     
                                        <label class="col-sm-4 col-form-label custom-font2">Nom(s)</label>
                                            <div class="col-sm-8">
                                                <input type="text" class="form-control custom-font2" id="nom" name="nom" value="<?php echo strtoupper($etudiant['nom']);?>">
                                            </div>
                                       
                                       
                                        <label class="col-sm-4 col-form-label custom-font2">Prénom(s)</label>
                                            <div class="col-sm-8">
                                                <input type="text" class="form-control custom-font2"   id="prenom" name="prenom" value="<?php echo strtoupper($etudiant['prenom']);?>">
                                            </div>
                                   

                                        <label class="col-sm-4 col-form-label custom-font2">Matricule</label>
                                            <div class="col-sm-8">
                                                <input type="text"     id="matricule" class="form-control custom-font2" name="matricule" value="<?php echo strtoupper($etudiant['candidat']);?>" disabled>
                                            </div>
                                     
                                            <label class="col-sm-4 col-form-label custom-font2">Date de naissance</label>
                                            <div class="col-sm-8">
                                                <input type="date" class="form-control custom-font2" name="datenaissance" value="<?php echo $etudiant['date_naissance'];?>" disabled>
                                            </div>
                                      
                                   
                                            <label class="col-sm-4 col-form-label custom-font2">Lieu de naissance</label>
                                            <div class="col-sm-8 mb-1">
                                                <input type="text" class="form-control custom-font2" name="lieunaissance" value="<?php echo strtoupper($etudiant['lieu de naissance']);?>" disabled>
                                            </div>
                                      
                                   
                                        
                      
                     
                                      
                                      
                                            <label class="col-sm-4 col-form-label custom-font2">Departement</label>
                                            <div class="col-sm-8  mb-1">
                                                <input type="text" class="form-control custom-font" name="departement" value="<?php echo strtoupper($etudiant['departement']);?>" disabled>
                                            </div>
                                        
                                     
                                            <label class="col-sm-4 col-form-label custom-font2">Specialité</label>
                                            <div class="col-sm-8  mb-0">
                                                <input type="text" class="form-control custom-font2" name="specialite" value="<?php echo strtoupper($etudiant['specialite']);?>" disabled>
                                            </div>
                                    
                                       
                                            <label class="col-sm-4 col-form-label custom-font2">Titre du sujet</label>
                                            <div class="col-sm-8  mb-1">
                                                <input type="text" class="form-control custom-font2" name="titre_du_sujet" value="<?php echo strtoupper(replace($etudiant['theme']));?>" >
                                            </div>
                                   
                                        
                                            <label class="col-sm-4 col-form-label custom-font2">Année Universitaire</label>
                                            <div class="col-sm-8" style="height: 25px;">
                                                <input type="text" class="form-control custom-font2" name="annee_acad" value="<?php echo strtoupper($etudiant['annee_acad']);?>" disabled>
                                            </div>
                                       
                                   
                         
                    
                </div>
                    <?php 
                       $note_soutenance =$etudiant['note'];
                     }?>
           
                </form>

           

              <?php 
              
                   $sql="select * from type_element ";
                   $resultat=$connexion->query($sql);
                   while($type_element = $resultat->fetch_assoc()){

                    $total =0;
              
              ?>
         <div class="col-lg-6 mb-10">
            
                                <h4 class="card-title custom-font" ><?php echo strtoupper($type_element['libelle_type']);?></h4>
                          
                        
                                    <table class="table  table-bordered table-responsive-sm table-bold-rows-cols custom-font " style="height: 250px;">
                                      
                                            <tr>
                                                <th scope="col">Element d'appreciation</th>
                                              
                                                <th scope="col" width="30%" >Note</th>
                                                <th scope="col">Bareme</th>
                                            </tr>
                                    
                                        <tbody>
                                        <?php 

                                                 $somme = 0;
                                                $sql="select * from element where type_element='".$type_element['libelle_type']."'";
                                                $result = $connexion->query($sql);
                                                while ($element = $result->fetch_assoc()){
                                                    $somme = $somme + getNoteElement($soutenance,$element['libelle_element'],$_SESSION['etablissement'],$connexion);
                                                     $_SESSION['total']= $_SESSION['total'] + getNoteElement($soutenance,$element['libelle_element'],$_SESSION['etablissement'],$connexion);
                                        ?> 
                                            <tr>
                                        
                                                <td> <?php echo strtolower(str_replace("+","'",$element['libelle_element']));?></td>
                                              
                                
                                               
                                        <td>  

                                            <?php
                                                if(getNoteElement($soutenance,$element['libelle_element'],$_SESSION['etablissement'],$connexion) == null) {
                                                    echo "";
                                            ?>
                                               
                                                <?php }else{?>
                                                    
                                                          <?php echo getNoteElement($soutenance,$element['libelle_element'],$_SESSION['etablissement'],$connexion);?>
                                                        
                                                      
                                                <?php }?>


                                        </td>
                                                <td><?php echo $element['note_max'];?>
                                                </td>
                                             
                                            </tr>
                                        
                                         <?php }?>
                                         <tr>
                                            <td colspan="3">  <p>TOTAL SUR <?php echo $type_element['note_max'];?> POINTS :  <?php echo $somme;  ;?></p></td>
                                         </tr>
                                       
                                        </tbody>
                                    </table>


                                   
                               
                         </div>

                           
     </div>
                   
 </div>
         <?php
            
        
           }?>  
<div class="col-md-12 text-center mb-5">

                                <h4 class="card-title custom-font">RECAPITULATIF DES RESULTATS DES SIX SEMESTRES</h4>
                      
                               
                                    <table class="table  table-bordered table-responsive-sm table-bold-rows-cols custom-font">
                                    
                                            <tr>
                                                <th>Semestre</th>
                                                <th>Moyenne Semestrielle</th>
                                                <th>Session de validation</th>
                                                <th>Decision du jury</th>
                                                <th>Mention</th>
                                                <th>Année accadémique</th>

                                                
                                            </tr>
                                      
                                        <tbody>

                                        <?php 

                                                  $tableau_moyenne = array();                                  
                                                  $sql1="select inscription,semestre,v.annee,v.etab from vue_moyenne_ue as v join inscription on v.inscription= inscription.id join soutenance on soutenance.impetrant=inscription.id where soutenance.code='$soutenance' ";
                                                    $resultat = $connexion->query($sql1);

                                                    while($recap=$resultat->fetch_assoc() ){
                                        ?>
                                            <tr>
                                                <th><?php echo $recap['semestre'];?></th>
                                                <td><?php  echo round(calcul_moyenne($recap['inscription'],$recap['semestre'],$recap['annee'],$recap['etab'],$connexion),2);?></td>
                                                <td><span class="badge badge-primary"></span>
                                                </td>
                                                <td></td>
                                                <td><?php echo mentionParmoyenne(round(calcul_moyenne($recap['inscription'],$recap['semestre'],$recap['annee'],$recap['etab'],$connexion),2));?></td>
                                                <td class="color-primary"><?php echo $recap['annee'];?></td>
                                            </tr>
                                        
                                         <?php
                                                    $tableau_moyenne[] = round(calcul_moyenne($recap['inscription'],$recap['semestre'],$recap['annee'],$recap['etab'],$connexion),2);
                                        
                                        }?>
                                            <?php 

$m=0;
$i =0;
foreach ($tableau_moyenne as $moy){
   $m=$m + $moy;
   $i++;
}

$m = $moy /$i;
?>

<tr>
    <td> <p class="custom-font">Moyenne Générale : <?php  echo $moy;?>  </p></td>
    <td> <p class="custom-font">Statut : <?php  echo statutSoutenance($moy);?> </p></td>
    <td>   <p class="custom-font">Total des points : <?php echo $_SESSION['total'];?>/100</p></td>
    <td colspan="3"> <label class="custom-font" for="">Note soutenance</label>
                                                             <?php echo $note_soutenance;?>
                                                              <br>
                                                              <p class="custom-font"> Mention : <?php echo mentionParmoyenne($note_soutenance);?></p>
                                                    </td>
</tr>
                                           
                                        </tbody>
                                    </table>

                                    
                                   
                       
                                                   
                                              
                    </div>

  

<div class="col-md-12 text-center">

                             
                                    <table class=" table  table-bordered table-responsive-sm table-responsive table-bold-rows-cols custom-font" id="monTableau">
                                     
                                            <tr>
                                                <th>Membres du Jury <br> 
                                                    (Nom(s) et Prenom(s) et grade)
                                                </th>
                                                <th>Signature</th>
                                                <th>President du jury <br>(Nom(s) et Prenom(s) et grade)</th>
                                              

                                                
                                            </tr>
                                      
                                        
                                        <?php 
                                                $sql="select * from jury where soutenance='$soutenance'";
                                                $jury=$connexion->query($sql);

                                                    while($recap=$jury->fetch_assoc() ){
                                        ?>
                                        <tbody>

                                            <tr>
                                               <td><?php echo $recap['rapp_int'];?></td>
                                               <td></td>
                                               <td rowspan="4"><?php echo $recap['president'];?></td>
                                              
                                            </tr>
                                            
                                            <tr>
                                               <td><?php echo $recap['rapp_extern'];?></td>
                                               <td></td>
                                              
                                           
                                            </tr>
                                           
                                            <tr>
                                               <td><?php echo $recap['examinateur'];?></td>
                                               <td></td>
                                               
                                              
                                            </tr>
                                            
                                            <tr>
                                               <td><?php echo $recap['autre'];?></td>
                                               <td></td>
                                               
                                               
                                            </tr>
                                           
                                        
                                         <?php
                                               
                                        }?>
                                          
                                           
                                        </tbody>
                                    </table>

</div> 
        </div>
        </div>
        </div>
        <!--**********************************
            Content body end
        ***********************************-->                                





                                                        </div>
    

    <script src="../vendor/global/global.min.js"></script>
	<script src="../vendor/bootstrap-select/dist/js/bootstrap-select.min.js"></script>
    <script src="../js/custom.min.js"></script>
    <script src="../js/dlabnav-init.js"></script>
		
    <!-- Chart Morris plugin files -->
   
    <script>
$(document).ready(function() {
  // Déclencher l'impression une fois que le document est prêt
  window.print();
});
</script>

		<!-- Demo scripts -->
    <script src="../js/dashboard/dashboard-1.js"></script>
	
	<!-- Svganimation scripts -->
    <script src="../vendor/svganimation/vivus.min.js"></script>
    <script src="../vendor/svganimation/svg.animation.js"></script>
    <script src="../vendor/datatables/js/jquery.dataTables.min.js"></script>
    <script src="../js/plugins-init/datatables.init.js"></script>
    
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>

    <script src="..js/test.js"></script>

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
         //   window.history.replaceState({}, document.title, window.location.pathname);
        }
    });
</script>
<script>
window.onload = function() {
  // Récupérer toutes les cellules de la dernière colonne
  var dernieresCellules = document.querySelectorAll('#monTableau .derniere-colonne');
    
    // Fusionner les cellules de la dernière colonne
    var nombreLignes = dernieresCellules.length;
    for (var i = 0; i < dernieresCellules.length; i++) {
        dernieresCellules[i].setAttribute('rowspan', nombreLignes);
    }
};
</script>




<script>
    $(document).ready(function() {
        // Gérer le clic sur le bouton "Modifier"
        $('.btn btn-primary element').click(function() {
            // Récupérer les données de la ligne cliquée
            var a = $(this).data('id');
            // Pré-remplir le formulaire modal avec les données actuelles
            $('#element1').val(a);

        });
    });
</script>

	
</body>
</html>

<?php 
     }
   }
}else{
    header("location: ../login");
}?>
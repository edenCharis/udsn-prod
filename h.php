
<?php 
include 'php/connexion.php';
include 'php/lib.php';
session_start();

if(isset($_GET['semestre']) and isset($_GET['spec']) and isset($_GET['annee']) and isset($_GET['examen']) and isset($_GET["etablissement"]))
{

    $semestre=$_GET["semestre"];
    $specialite =$_GET["spec"];
    $annee=$_GET["annee"];
    $examen=$_GET["examen"];
    $niveau=NiveauParSemestre($semestre);
    
    $parcours = getParcours($specialite,$connexion);

    $_SESSION["etablissement"] = $_GET["etablissement"];
    
    
    $lib=getLibelleEtablissement($_SESSION["etablissement"],$connexion);
      $_SESSION["lib_etab"] = $lib;
      
          $countValide = "";
                             $eliminatoires = array();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>UDSN</title>
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
    </style>
</head>
<body>



 <div class="col-lg-12">
                <div class="row tab-content">
                    <div id="list-view" class="tab-pane fade active show col-lg-12">
                        <div class="card">
                            <div class="card-header">
                                <h4 class="card-title">Parcours : <?php echo $parcours;?> </h4>
                                 <h4 class="card-title">Specialité : <?php echo $specialite;?> </h4>
                                <h4 class="card-title">Semestre : <?php echo $semestre;?> </h4>
                                <h4 class="card-title">Année universitaire : <?php echo $annee;?> </h4>
                                <h4 class="card-title">Examen : <?php echo $examen;?> </h4>
                               
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
<?php 
                                   $sql = "select distinct(ue.code),libelle from ue  where ue.etab='".$_SESSION["etablissement"]."' and specialite='".$specialite."' and semestre='$semestre' and code in ( select code_ue from ecue)";
                            ?>
<table id="example3" class="display" >
    <thead>
        <tr>
        <th rowspan="3">N°</th>
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
    
          $sql1="select inscription.id as id from inscription join classe on inscription.classe=classe.libelle join specialite on classe.specialite=specialite.libelle join parcours on specialite.parcours=parcours.libelle where classe.niveau='$niveau' and parcours.libelle='$parcours'";
          $r = $connexion->query($sql1);
          $num = 0;
          while($etudiant = $r ->fetch_object()){
            $num++;

            $points = 0;
    ?>

        <tr>
            <th><?php echo $num;?></th>
            <th><?php echo  mettrePremieresLettresMajuscules(getNomEtudiant(getCandidatCodeByInscription($etudiant->id,$connexion),$connexion,$_SESSION["lib_etab"])."  ".getPrenomEtudiant(getCandidatCodeByInscription($etudiant->id,$connexion),$connexion,$_SESSION["lib_etab"]));?></th>
            
            
            <?php 
               
               $result_ue =$connexion->query($sql);
           
               while($data=$result_ue ->fetch_object()){
                     $i=0; $countValide ="";
                   $sql_="select * from ecue where code_ue='".$data->code."'";
                   $result_ecue=$connexion->query($sql_);
                   $eliminatoires = array();
                   
                  
            
             while($ecue = $result_ecue->fetch_object()){
                 $i++;  

                 $a=getEtudiantCC($etudiant->id,$connexion,$_SESSION["etablissement"],$semestre,$ecue->libelle,$annee);

                 if($examen == "ordinaire")
                 {
                    $b= getEtudiantEXT($etudiant->id,$connexion,$_SESSION["etablissement"],$semestre,$ecue->libelle,$annee);
                 

                 }else{
                    $b= getEtudiantRattrapage($etudiant->id,$connexion,$_SESSION["etablissement"],$semestre,$ecue->libelle,$annee);
                 
                 }
                 
               
         ?>
            <th class="small-column"> <?php echo ($a !== "-") ? $a : "-" ;?></th>
            <th class="small-column text-danger"> <?php echo ($b !== "-") ? $b: "-";?></th>
            <th class="small-column text-warning"> <?php echo ( $a !== "-" and $b !== "-" ) ? $y=round(($a+$b)/2,2)  : ("-"); ( $a !== "-" and $b !== "-" ) ? $eliminatoires[]=round(($a+$b)/2,2)  : "";   ?></th>
            <?php if(( $i == $result_ecue->num_rows) ){
               // $ue_moy = "-";   
                $ue_moy = getMoyenneUE($connexion,$etudiant->id,$semestre,$annee,$data->code,$_SESSION["etablissement"]);
              //  $points = $points +$ue_moy;
                
                

            ?>
                
          
             <th class="small-column text-secondary"> <?php echo  ($ue_moy != "-") ? round($ue_moy,2): $ue_moy ;  ?></th>
       
           <?php 
           
           $y= ( $a !== "-" && $b!=="-" ) ? round(($a+$b)/2,2) : "";
           if( $y !=="-" && $y >=10) 
             {$countValide++;
             }
                
            }
               } 
         } ?>

        <th class="small-column"> <?php echo $countValide  ;?></th>
           <th class="small-column text-dark"> <?php $tt=calcul_moyenne($etudiant->id,$semestre,$annee,$_SESSION['etablissement'],$connexion);echo  $tt?></th>
           <th class="small-column text-warning"> <?php echo  ($tt !== "-") ?  mentionParmoyenne($tt,2): ""; ?></th>
           <th class="small-column"> <?php
$result = "";

// Vérifier si $eliminatoires n'est pas vide
if (!empty($eliminatoires)) {
    // Vérifier si les éliminatoires sont valides
    if (verifierEliminatoire($eliminatoires) == false) {
        // Vérifier si le nombre de résultats dans $result_ue est suffisant
        if ($result_ue->num_rows - 1 >= 1) {
            $result =  ( $tt !== "-") ? statutSoutenance(round($tt, 2)) : "";
        } else {
            $result = ""; // Aucun statut si condition non remplie
        }
    } else {
        $result = "Note Eliminatoire"; // Note éliminatoire détectée
    }
}

// Résultat final
echo $result;
;?></th>
           
           
         
        </tr>
        <?php }?>
    </tbody>
</table>

</div>


 
</div>

<script>
$(document).ready(function() {
  // Déclencher l'impression une fois que le document est prêt
  window.print();
});
</script>



</body>


</html>
<?php }?>

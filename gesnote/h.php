
<?php 
include '../php/connexion.php';
include '../php/lib.php';
session_start();


ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Entête de Tableau</title>
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


    <?php 
      $sql = "select ue.libelle as libelle, ue.code from ue join specialite on ue.Specialite=specialite.libelle join parcours on specialite.parcours=parcours.libelle where ue.etab='".$_SESSION["etablissement"]."' and ue.libelle in (select ue from ecue )";
    ?>

<table>
    <thead>
        <tr>
        <th rowspan="3">N°</th>
            <th rowspan="3">Nom(s)</th>
            <th rowspan="3">Prenom(s)</th>

          

            <?php 

          
            $result_ue =$connexion->query($sql);
            while($data=$result_ue ->fetch_object()){

                $sql_="select * from ecue where ue='".$data->libelle."'";
                $result_ecue=$connexion->query($sql_);
                $colspan= ($result_ecue->num_rows > 0) ? ($result_ecue->num_rows*3)+1 : 1;
            
            ?>
            <th colspan="<?php echo $colspan;?>"><?php echo $data->libelle?></th>
        <?php }?>

         
            <th rowspan="3">#REF!</th>
            <th rowspan="3">UE validées sur <?php echo $result_ue->num_rows; ?></th>
            <th rowspan="3">Moyenne Generale</th>
            <th rowspan="3">Appréciation</th>
            <th rowspan="3">Decision du jury</th>
            
        </tr>
        <tr>

        <?php 
         
           $result_ue =$connexion->query($sql);
       
           while($data=$result_ue ->fetch_object()){
         
               $sql_="select * from ecue where ue='".$data->libelle."'";
               $result_ecue=$connexion->query($sql_);
               $i=0;
               while($ecue =$result_ecue->fetch_object()){
                $i++;
           
           ?>
        
            <th colspan="3"><?php echo $ecue->libelle?></th>

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
                      $sql_="select * from ecue where ue='".$data->libelle."'";
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
    
          $sql1="select inscription.id as id from inscription join classe on inscription.classe=classe.libelle join specialite on classe.specialite=specialite.libelle join parcours on specialite.parcours=parcours.libelle where classe.niveau='Première année'";
          $r = $connexion->query($sql1);
          $num = 0;
          while($etudiant = $r ->fetch_object()){
            $num++;

            $points = 0;
    ?>

        <tr>
            <th><?php echo $num;?></th>
            <th><?php echo  getNomEtudiant(getCandidatCodeByInscription($etudiant->id,$connexion),$connexion,$_SESSION["lib_etab"]);?></th>
            <th><?php echo  getPrenomEtudiant(getCandidatCodeByInscription($etudiant->id,$connexion),$connexion,$_SESSION["lib_etab"]);?></th>
            
            <?php 
               
               $result_ue =$connexion->query($sql);
           
               while($data=$result_ue ->fetch_object()){
                     $i=0; $countValide =0;
                   $sql_="select * from ecue where ue='".$data->libelle."'";
                   $result_ecue=$connexion->query($sql_);
             while($ecue = $result_ecue->fetch_object()){
                 $i++;  

                 $a=getEtudiantCC($etudiant->id,$connexion,$_SESSION["etablissement"],"semestre 1",$ecue->libelle,"2023-2024");
                 $b= getEtudiantEXT($etudiant->id,$connexion,$_SESSION["etablissement"],"semestre 1",$ecue->libelle,"2023-2024");
                 
               
         ?>
            <th class="small-column"> <?php echo ($a !== "##") ? $a : "#" ;?></th>
            <th class="small-column"> <?php echo ($b !== "##") ? $b: "#";?></th>
            <th class="small-column"> <?php echo ( $a !== "##" and $b !== "##" ) ? round(($a+$b)/2,2) : ("#");?></th>
            <?php if(( $i == $result_ecue->num_rows) ){

                $moy =round(getMoyenneUE($connexion,$etudiant->id,"semestre 1","2023-2024",$data->libelle,$_SESSION["etablissement"]),2);
                
                $points = $points + $moy;

                ($moy > 10) ? $countValide++ : "";
                
            ?>
                
          
             <th class="small-column"> <?php echo round(getMoyenneUE($connexion,$etudiant->id,"semestre 1","2023-2024",$data->libelle,$_SESSION["etablissement"]),2);?></th>
       
           <?php }}} ?>

           <th class="small-column"> <?php echo $points;?></th>
           <th class="small-column"> <?php echo $countValide;?></th>
           <th class="small-column"> <?php echo round($points / ($result_ue->num_rows - 1),2) ;?></th>
           <th class="small-column"> <?php echo  mentionParmoyenne(round($points / ($result_ue->num_rows - 1),2)) ?></th>
           <th class="small-column"> <?php echo statutSoutenance(round($points / ($result_ue->num_rows - 1),2));?></th>
           
         
        </tr>
        <?php }?>
    </tbody>
</table>



</body>
</html>


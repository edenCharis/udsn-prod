<?php 

include '../php/connexion.php';
include '../php/lib.php';
session_start();

if(isset($_POST["changer"]) and isset($_POST["decision"])){

    $etudiant = $_POST["etudiant"];
    $decision =$_POST["decision"];
    $moyenne =$_POST["moy"];
    $etab=$_SESSION["etablissement"];

    $parcours = $_POST["parcours"];
    $semestre=$_POST["semestre"];
    $annee=$_POST["annee"];
    $examen =$_POST["examen"];
    $niveau = NiveauParSemestre($semestre);


    if( $moyenne < 10 and $decision == "Admis"){
        header("location: h?&parcours=$parcours&semestre=$semestre&examen=$examen&niveau=$niveau&annee=$annee&etablissement=".$_SESSION["etablissement"]."&erreur='Cette décision est incorrecte car l'etudiant n'a pas atteint la moyenne.'");
        exit;
        
    }
    
    if( $moyenne >= 10 and $decision == "Ajourné"){
        header("location: h?&parcours=$parcours&semestre=$semestre&examen=$examen&niveau=$niveau&annee=$annee&etablissement=".$_SESSION["etablissement"]."&erreur='Cette décision est incorrecte car l'etudiant a atteint la moyenne.'");
        exit;
        
    }
    $test ="select id from recap where etudiant=$etudiant and semestre='$semestre' and annee='$annee' and etab='$etab'";

    $tester = $connexion->query($test);

    if($tester->num_rows > 0){

        while($p = $tester->fetch_object()){
            $id = $p->id;
        }


        if($connexion->query("update recap set decision='$decision',examen='$examen' where id=$id")){

            logUserAction($connexion,$_SESSION['id_user'],"modification statut d'admission d'un etudiant ",date("Y-m-d H:i:s"),$userIP,"valeur modifié : $etudiant+$semestre+$annee, statut=$decision");
        
    
            header("location: h?&parcours=$parcours&semestre=$semestre&examen=$examen&niveau=$niveau&annee=$annee&etablissement=".$_SESSION["etablissement"]."&sucess=Modification effectuée avec succes");
       
        }


    }else{

        $sql="INSERT INTO recap(etudiant,semestre,annee,moy,decision,etab,examen) values($etudiant,'$semestre','$annee','$moyenne','$decision','$etab','$examen')";

        if($connexion->query($sql)){
    
         logUserAction($connexion,$_SESSION['id_user'],"modification statut d'admission d'un etudiant ",date("Y-m-d H:i:s"),$userIP,"valeur modifié : $etudiant+$semestre+$annee, statut=$decision");
        
    
         header("location: h?&parcours=$parcours&semestre=$semestre&examen=$examen&niveau=$niveau&annee=$annee&etablissement=".$_SESSION["etablissement"]."&sucess=Modification effectuée avec succes");
    
        }else{
            header("location: h?parcours=$parcours&semestre=$semestre&examen=$examen&niveau=$niveau&annee=$annee&etablissement=".$_SESSION["etablissement"]."&erreur='$connexion->error'");
    
    
        }

    }

    
   

 }
<?php

include '../php/connexion.php';
include '../php/lib.php';
session_start();

if($_SERVER["REQUEST_METHOD"] == "POST")
{

if(isset($_POST['etudiant']) and isset($_POST['moydev']) and isset($_POST['moyex'])){


    $etudiant = $_POST['etudiant'];
    $ecue = clean_input($_POST['ecue']);
    $classe = clean_input($_POST['classe']);
    $annee = $_POST['annee'];
    $etab =$_SESSION['etablissement'];
    $sem = obtenirSemestrePourECUE($ecue,$connexion);
    $moyenneDevoir=$_POST['moydev'];
    $moyenneExamen=$_POST['moyex'];
    $ex=($_POST['moydev']+$_POST['moyex'])/2; 
    
    
    if(eleveDansClasse(getCandidatCodeByInscription($etudiant,$connexion),$classe,$annee,$connexion)){

    

    $sql="INSERT INTO notation (inscription,classe,ecue,annee,moyDev,moyEx,moyGen,etab,semestre) VALUES 
    ($etudiant,'$classe','$ecue','$annee',$moyenneDevoir,$moyenneExamen,$ex,'$etab','$sem')";
    

  if($connexion->query($sql)){
  $userIP = $_SERVER['REMOTE_ADDR'];

  logUserAction($connexion,$_SESSION['id_user'],"enregistrement d'une note ",date("Y-m-d H:i:s"),$userIP,"valeur enregistrée :$etudiant+$classe+$annee");

  header("location: notation?sucess=Enregistrement effectuée avec succèss");
  
}else{

 echo header("location: notation?erreur=$connexion->error");
  
}}else{
  echo header("location: notation?erreur=l'etudiant choisie n'est pas inscrit dans cette classe.");
}


  }else if(isset($_POST['noteId']) and isset($_POST['nouveauMoyDev']) and isset($_POST['nouveauMoyEx'])){

          $id = $_POST['noteId'];
          $x = $_POST['nouveauMoyDev'];
          $y = $_POST['nouveauMoyEx'];


          $z = ($x +$y)/2;


          $sql ="UPDATE notation set moyDev=$x,moyEx=$y,moyGen=$z where id=$id ";


          if($connexion->query($sql)){
            $userIP = $_SERVER['REMOTE_ADDR'];
          
            logUserAction($connexion,$_SESSION['id_user'],"modification d'une note ",date("Y-m-d H:i:s"),$userIP,"valeur modifiée :$x + $y +$z");
          
            header("location: notation?sucess=Modification effectuée avec succèss" );
            
          }else{
          
           echo header("location: notation?erreur=$connexion->error");
            
          }


  }
}

?>
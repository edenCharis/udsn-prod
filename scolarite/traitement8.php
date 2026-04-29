<?php 

include '../php/connexion.php';
include '../php/lib.php';
session_start();

 
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);


if(isset($_POST['typespec'])){



    $typespec=$_POST['typespec'];
    $parcours =$_POST["parcours"];
    $type =$_POST["type"];
    $etab=$_SESSION["lib_etab"];

    if($typespec !== "" and $parcours !=="" and $etab !== null){
       $typespec=str_replace("'","+",$typespec);
     
   
   
       
       
        $sql= "insert into specialite(libelle,parcours,type,etab) values('".$typespec."','".$parcours."','".$type."','".$etab."')";
  
       if($connexion->query($sql)){
          $userIP = $_SERVER['REMOTE_ADDR'];
  
          logUserAction($connexion,$_SESSION['id_user'],"enregistrement d'une spécialité",date("Y-m-d H:i:s"),$userIP,"valeur enregistrée : $typespec");
  
          header("location: specialite?sucess=enregistrement effectuée avec succèss");
       }else{
          header("location: specialite?erreur=$connexion->error");
       }
    }else{
      header("location: specialite?erreur=Ce champ ne peut pas être vide");
    }
}




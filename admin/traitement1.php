<?php
include '../php/connexion.php';
include '../php/lib.php';
session_start();



if(isset($_GET['supetab'])){

$id=$_GET['supetab'];

$sql="delete from etablissement where id=$id";

if($connexion->query($sql)){
    $userIP = $_SERVER['REMOTE_ADDR'];

    logUserAction($connexion,$_SESSION['id_user'],"suppression d'un etablissement",date("Y-m-d H:i:s"),$userIP,"valeur supprimée : $id");

    header("location: etablissement?sucess=suppression effectuée avec succèss");

}else{
    header("location: etablissement?erreur=$connexion->error");
  }


}else if(isset($_POST['nom']) && isset($_POST['code'])){


$nom=str_replace("'","+",$_POST['nom']);
$code=str_replace("'","+",$_POST['code']);
$type=$_POST['type'];
$email=$_POST['email'];

if($nom !== "" or $code !=="" or $type !==""){
  


   $sql= "insert into etablissement(libelle,code,type,email,universite) values('".$nom."','".$code."','".$type."','".$email."','".$_SESSION['univ']."')";

   if($connexion->query($sql)){
      $userIP = $_SERVER['REMOTE_ADDR'];

      logUserAction($connexion,$_SESSION['id_user'],"création d'un établissement",date("Y-m-d H:i:s"),$userIP,"valeur enregistrée : $nom+$code+$type");

      header("location: etablissement?sucess=enregistrement effectuée avec succèss");
   }else{
      header("location: etablissement?erreur=$connexion->error");
   }
}else{
  header("location: etablissement?erreur=Un champ est vide, veuillez remplir tout le formulaire");
}


}

if(isset($_POST['etabId'])){
$id =$_POST['etabId'];

$nom=str_replace("'","+",$_POST['nouveauNom']);
$code=str_replace("'","+",$_POST['nouveauCode']);
$type=$_POST['nouveauType'];
$email=$_POST['nouveauEmail'];

   $sql= "UPDATE  etablissement set libelle='$nom',code='$code',type='$type',email='$email' where id=$id";

   if($connexion->query($sql)){
      $userIP = $_SERVER['REMOTE_ADDR'];

      logUserAction($connexion,$_SESSION['id_user'],"Modification d'un établissement",date("Y-m-d H:i:s"),$userIP,"valeur enregistrée : $nom+$code+$type");

      header("location: etablissement?sucess=modification effectuée avec succèss");
   }else{
      header("location: etablissement?erreur=$connexion->error");
   }

}
?>
<?php 
include '../php/connexion.php';
include '../php/lib.php';
session_start();

if(isset($_GET['statut']) and isset($_GET['id'])){

    $id= $_GET['id'];

    $sql ="UPDATE utilisateur set statut='".$_GET['statut']."' where id=$id";

    if($connexion->query($sql)){

        $userIP = $_SERVER['REMOTE_ADDR'];

        logUserAction($connexion,$_SESSION['id_user'],"Mise à jour statut d'un utilisateur",date("Y-m-d H:i:s"),$userIP," utilisateur : $id; valeur mise à jour : '".$_GET['statut']);

        header("location: compte?sucess=opération effectuée avec succèss");


    }else{
        header("location: compte?erreur=$connexion->error");
    }


}?>
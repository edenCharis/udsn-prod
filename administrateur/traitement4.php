<?php 
include '../php/connexion.php';
include '../php/lib.php';
session_start();


if(isset($_GET['supcompte'])){
    $id=$_GET['supcompte'];

    $sql="delete from utilisateur where id=$id";

    if($connexion->query($sql)){
        $userIP = $_SERVER['REMOTE_ADDR'];

        logUserAction($connexion,$_SESSION['id_user'],"suppression d'un utilisateur",date("Y-m-d H:i:s"),$userIP,"valeur supprimée : $id");

        header("location: compte?sucess=suppression effectuée avec succèss");

    }else{
        header("location: compte?erreur=$connexion->error");
    }
}

?>
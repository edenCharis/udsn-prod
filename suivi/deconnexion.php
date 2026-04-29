<?php 
include '../php/connexion.php';
include '../php/lib.php';
session_start();


$userIP = $_SERVER['REMOTE_ADDR'];

            logUserAction($connexion,$_SESSION['id_user'],"Deconnexion",date("Y-m-d H:i:s"),$userIP," //");
    
           if( session_destroy()){
            header("location: ../login");

           }
?>
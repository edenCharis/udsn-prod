<?php 

include '../php/connexion.php';
include '../php/lib.php';
session_start();

 error_reporting(E_ALL);
ini_set('display_errors', 1);


    $etab=$_POST["etab"];
    $annee=$_POST["annee"];
    $code = $_POST["contrat"];
   
    $ecues =$_POST["ecues"];
    


                 foreach($ecues as $e){


                        $st="INSERT into contrat_couverture(contrat,ecue,etab) values ('$code','$e','$etab')";
                        $connexion->query($st);
                         logUserAction($connexion,$_SESSION['id_user'],"enregistrement d'un nouvel ecue  ",date("Y-m-d H:i:s"),$userIP,"code contrat : $code_contrat | ECue :$e");
                 }
       

                

                 header("location: details?contrat=$code&annee=$annee");
           
       








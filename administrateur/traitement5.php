<?php 
include '../php/connexion.php';
include '../php/lib.php';
session_start();


if(isset($_POST['nom']) and isset($_POST['login']) and isset($_POST['mdp']))
{

        $nom=str_replace("'","+",$_POST['nom']);
        $login=str_replace("'","+",$_POST['login']);
        $mdp=str_replace("'","+",$_POST['mdp']);
        $id=$_SESSION['id_user'];

        if($nom !==" " and $login !==" " and $mdp !== " "){


            $sql =" UPDATE utilisateur set nom='$nom',login='$login',mdp='$mdp' where id=$id ";

            if($connexion->query($sql)){
                $userIP = $_SERVER['REMOTE_ADDR'];

                logUserAction($connexion,$_SESSION['id_user'],"Mise à jour du profil administrateur",date("Y-m-d H:i:s"),$userIP," utilisateur : $id; valeur mise à jour : $nom+$login+$mdp");
                  $_SESSION['nom_user'] =$nom;
                  $_SESSION['login'] =$login;
                  $_SESSION['mdp'] =$mdp;

                header("location: index?sucess=opération effectuée avec succèss");
        

            }
        }else{

            header("location: index?erreur=les champ(s) ne peuvent pas être vide");
        } 



}

?>
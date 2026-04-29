<?php 
require_once('connexion.php');
require_once('lib.php');

 if(isset($_POST['username']) and isset($_POST['password']) and isset($_POST['role']) and isset($_POST["email"])){


     $role = $_POST['role'];
     $etab= $_POST['etablissement'];
      $username = $_POST['username'];
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    $email = $_POST["email"];
    
if(is_login_used($username,$connexion))
{
      header("location: ../register_account?erreur=Login déja utilisé.");
    exit;
} 
    
/*

if(is_email_used($email,$connexion))
{
      header("location: ../register_account?erreur=Adresse email déja utilisée.");
    exit;
}*/



    if (strlen($password) < 6 || !preg_match("/[A-Z]/", $password) || !preg_match("/[a-z]/", $password) || !preg_match("/[0-9]/", $password) || !preg_match("/[\W]/", $password)) {
    header("location: ../register_account?erreur=Le mot de passe doit contenir au moins 6 caractères, une majuscule, une minuscule, un chiffre et un caractère spécial.");
    exit;
}





    $sql ="insert into utilisateur(login,mdp,role,email,etab,statut) values('$username','$password','$role','$email','$etab',1)";

    if($connexion->query($sql)){
        logUserAction($connexion,$id,"creation utilisateur",date("Y-m-d H:i:s"),$userIP,"valeur :  $username+$password+$email");

          header("location: ../login");
    }else{

        header("location: ../register_account?erreur=$connexion->error");
    }
}



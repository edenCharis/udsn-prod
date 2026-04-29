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

  header("location: index?sucess=Enregistrement effectuée avec succèss");
  
}else{

 echo header("location: index?erreur=$connexion->error");
  
}}else{
  echo header("location: index?erreur=l'etudiant choisie n'est pas inscrit dans cette classe.");
}


  }else if(isset($_POST['noteId']) and isset($_POST['nouveauMoyDev']) and isset($_POST['nouveauMoyEx'])){

          $id = $_POST['noteId'];
          $x = $_POST['nouveauMoyDev'];
          $y = $_POST['nouveauMoyEx'];
          $w=$_POST['nouveausession'];


          if($y < $w){
            $z = ($x +$w)/2;
          }else{

            $z =( $x + $y)/2;
          }

          $sql ="UPDATE notation set moyDev=$x,moyEx=$y,moyGen=$z,session_rappel=$w where id=$id ";


          if($connexion->query($sql)){
            $userIP = $_SERVER['REMOTE_ADDR'];
          
            logUserAction($connexion,$_SESSION['id_user'],"modification d'une note ",date("Y-m-d H:i:s"),$userIP,"valeur modifiée :$x + $y +$z");
          
            header("location: index?sucess=Modification effectuée avec succèss" );
            
          }else{
          
           echo header("location: index?erreur=$connexion->error");
            
          }


  }
}



if ($_SERVER["REQUEST_METHOD"] == "POST" and isset($_POST["nom"]) and  isset($_POST["login"]) ){


    $sql="UPDATE utilisateur set";

    if( isset($_POST['nom']) ){

             $nom=str_replace("'","+",$_POST['nom']);
             $sql.=" nom='".$nom."',";
    }

    if(isset($_POST['login'])){
        $login=str_replace("'","+",$_POST['login']);
        $sql.=" login='".$login."',";

    }

    if(isset($_POST['mdp']) ){
        $mdp=$_POST['mdp'];
     
        
            $sql.=" mdp='".$mdp."',";
         

      

    }
    
         
        if (isset($_FILES['img']) &&  $_FILES['img']['error'] === UPLOAD_ERR_OK) {
            // Récupérez des informations sur le fichier
            $nomFichier = $_FILES['img']['name'];
            $typeFichier = $_FILES['img']['type'];
            $cheminTemporaire = $_FILES['img']['tmp_name'];
    
            // Vous pouvez maintenant traiter le fichier, par exemple, le déplacer vers un répertoire de stockage
             $nouveauChemin = "photos/" . $nomFichier;
    
            if (move_uploaded_file($cheminTemporaire, $nouveauChemin)) {

             $sql=" img=".$nouveauChemin."";

            }
        
        }


        $sql=rtrim($sql, ',');

        $sql.=" where id=".$_SESSION['id_user'];
     

        if($connexion->query($sql)){
            $role =$_SESSION['role'];
            $etab=$_SESSION['etablissement'];
            $userIP = $_SERVER['REMOTE_ADDR'];

            logUserAction($connexion,$_SESSION['id_user'],"modification d'un profil utilisateur",date("Y-m-d H:i:s"),$userIP,"valeur enregistrée : $nom+$login+$mdp+$role+$etab");
    

            header("location: compte?sucess=Modification effectué avec success");
      
        }else{

            header("location: compte?erreur=$connexion->error");
        }
    
        }


       


<?php 

include '../php/connexion.php';
include '../php/lib.php';
session_start();

 error_reporting(E_ALL);
ini_set('display_errors', 1);


    if(isset($_POST["id"])){
           $sql="UPDATE enseignant set";

    if( isset($_POST['nom']) ){

             $nom=str_replace("'","+",$_POST['nom']);
             $sql.=" nom='".$nom."',";
    }

    if(isset($_POST['prenom'])){
        $prenom=str_replace("'","+",$_POST['prenom']);
        $sql.=" prenom='".$prenom."',";

    }
    if(isset($_POST['date_naissance'])){
        $dn=str_replace("'","+",$_POST['date_naissance']);
        $sql.=" date_naissance='".$dn."',";

    }
    
      if(isset($_POST['diplome'])){
        $d=str_replace("'","+",$_POST['diplome']);
        $sql.=" diplome='".$d."',";

    }
    
     if(isset($_POST['specialite'])){
        $s=str_replace("'","+",$_POST['specialite']);
        $sql.=" specialite='".$s."',";

    }
      if(isset($_POST['grade'])){
        $g=str_replace("'","+",$_POST['grade']);
        $sql.=" grade='".$g."',";

    }
     if(isset($_POST['telephone'])){
        $telephone=str_replace("'","+",$_POST['telephone']);
        $sql.=" telephone='".$telephone."',";

    }

if(isset($_POST['email'])){
        $e=str_replace("'","+",$_POST['email']);
        $sql.=" email='".$e."',";

    }
    if(isset($_POST['ville'])){
        $v=str_replace("'","+",$_POST['ville']);
        $sql.=" ville='".$v."',";

    }
    if(isset($_POST['sexe'])){
        $x=str_replace("'","+",$_POST['sexe']);
        $sql.=" sexe='".$x."',";

    }
   

    
         
    


        $sql=rtrim($sql, ',');

        $sql.=" where id=".$_POST["id"];
     

        if($connexion->query($sql)){
            $role =$_SESSION['role'];
            $etab=$_SESSION['etablissement'];
            $userIP = $_SERVER['REMOTE_ADDR'];

            logUserAction($connexion,$_SESSION['id_user'],"modification des info d'un enseignant",date("Y-m-d H:i:s"),$userIP,"valeur enregistrée : ID: ".$_POST["id"]);
    

            header("location: contrat?sucess=Modification effectué avec success");
      
        }else{

            header("location: contrat?erreur=$connexion->error");
        }
    
        }


        
    


 

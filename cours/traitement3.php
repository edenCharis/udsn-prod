<?php 

include '../php/connexion.php';
include '../php/lib.php';
session_start();

 

if ($_SERVER["REQUEST_METHOD"] == "POST"){


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


   



    
         
        if ($_FILES['img']['error'] === UPLOAD_ERR_OK) {
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


?>
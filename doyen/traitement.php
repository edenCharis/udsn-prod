<?php 

include '../php/connexion.php';
include '../php/lib.php';
session_start();

 


    if(isset($_POST["nom"]) and isset($_POST["login"])){
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

             $sql.=" img='".$nouveauChemin."'";

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


        
    


 

       if(isset($_GET['valide']) and isset($_GET['id'])){


        
       }
       
       
       
       
     if(isset($_POST['nouveauSpec']) and isset($_POST['nouveauParcours']) and isset($_POST['specId'])){

    $id =$_POST['specId'];
    $etab =getEtabByLibelleParcours($connexion,$_POST['nouveauParcours']);
    $nouveauSpec=str_replace("'","+",$_POST['nouveauSpec']);

    $nouveauPar=str_replace("'","+",$_POST['nouveauParcours']);

    $nouveauType=str_replace("'","+",$_POST['nouveauType']);

  


    $sql ="UPDATE specialite set libelle='$nouveauSpec',etab='$etab',type='$nouveauType',parcours='$nouveauPar' WHERE id=$id";

    if($connexion->query($sql)){
        $userIP = $_SERVER['REMOTE_ADDR'];

        logUserAction($connexion,$_SESSION['id_user'],"modification d'un specialite",date("Y-m-d H:i:s"),$userIP,"valeur modifiée : $nouveauSpec+$etab+$nouveauPar+$nouveauType");

        header("location: specialite?sucess=modification effectuée avec succèss");

    }else{
          header("location: specialite?erreur=$connexion->error");
      }




}


if(isset($_POST['nouveauSpec']) and isset($_POST['nouveauParcours']) and isset($_POST['specId'])){

    $id =$_POST['specId'];
    $etab =getEtabByLibelleParcours($connexion,$_POST['nouveauParcours']);
    $nouveauSpec=str_replace("'","+",$_POST['nouveauSpec']);

    $nouveauPar=str_replace("'","+",$_POST['nouveauParcours']);

    $nouveauType=str_replace("'","+",$_POST['nouveauType']);

  


    $sql ="UPDATE specialite set libelle='$nouveauSpec',etab='$etab',type='$nouveauType',parcours='$nouveauPar' WHERE id=$id";

    if($connexion->query($sql)){
        $userIP = $_SERVER['REMOTE_ADDR'];

        logUserAction($connexion,$_SESSION['id_user'],"modification d'un specialite",date("Y-m-d H:i:s"),$userIP,"valeur modifiée : $nouveauSpec+$etab+$nouveauPar+$nouveauType");

        header("location: specialite?sucess=modification effectuée avec succèss");

    }else{
          header("location: specialite?erreur=$connexion->error");
      }




}

if(isset($_POST['typespec']) and isset($_POST["parcours"]) and isset($_POST["type"])){



    $typespec=$_POST['typespec'];
    $parcours =$_POST['parcours'];
    $type =$_POST['type'];
    $etab=getEtabByLibelleParcours($connexion,$_POST['parcours']);

    if($typespec !== "" and $parcours !=="" and $etab !== null){
       $typespec=str_replace("'","+",$typespec);
       $parcours=str_replace("'","+",$parcours);
       $type=str_replace("'","+",$type);
       $etab=str_replace("'","+",$etab);
  
  
       $sql= "insert into specialite(libelle,parcours,type,etab) values('".$typespec."','".$parcours."','".$type."','".$etab."')";
  
       if($connexion->query($sql)){
          $userIP = $_SERVER['REMOTE_ADDR'];
  
          logUserAction($connexion,$_SESSION['id_user'],"enregistrement d'une spécialité",date("Y-m-d H:i:s"),$userIP,"valeur enregistrée : $typespec");
  
          header("location: specialite?sucess=enregistrement effectuée avec succèss");
       }else{
          header("location: specialite?erreur=$connexion->error");
       }
    }else{
      header("location: specialite?erreur=Ce champ ne peut pas être vide");
    }
}

if(isset($_GET['supspec'])){

    $id=$_GET['supspec'];

    $sql="delete from specialite where id=$id";

    if($connexion->query($sql)){
        $userIP = $_SERVER['REMOTE_ADDR'];

        logUserAction($connexion,$_SESSION['id_user'],"suppression d'une spécialité ",date("Y-m-d H:i:s"),$userIP,"valeur supprimée : $id");

        header("location: specialite?sucess=suppression effectuée avec succèss");

    }else{
        header("location: specialite?erreur=$connexion->error");
      }
}


if(isset($_POST['typepar'])){
    $typepar=$_POST['typepar'];
    $etab=$_SESSION['lib_etab'];

    if($typepar !== "" and $etab !==""){
       $typepar=str_replace("'","+",$typepar);
       $etab=str_replace("'","+",$etab);
  
  
       $sql= "insert into parcours(libelle,etab) values('".$typepar."','".$etab."')";
  
       if($connexion->query($sql)){
          $userIP = $_SERVER['REMOTE_ADDR'];
  
          logUserAction($connexion,$_SESSION['id_user'],"enregistrement d'un parcours",date("Y-m-d H:i:s"),$userIP,"valeur enregistrée : $typepar");
  
          header("location: parcours?sucess=enregistrement effectuée avec succèss");
       }else{
          header("location: parcours?erreur=$connexion->error");
       }
    }else{
      header("location: parcours?erreur=Ce champ ne peut pas être vide");
    }
}

if(isset($_POST['parId']) and isset($_POST['nouveauPar'])){


    $id =$_POST['parId'];
    $nouveauPar=str_replace("'","+",$_POST['nouveauPar']);
    $nouveauEtab=str_replace("'","+",$_SESSION['lib_etab']);

    $sql ="UPDATE parcours set libelle='$nouveauPar',etab='$nouveauEtab' WHERE id=$id";

    if($connexion->query($sql)){
        $userIP = $_SERVER['REMOTE_ADDR'];
        logUserAction($connexion,$_SESSION['id_user'],"modification d'un parcours",date("Y-m-d H:i:s"),$userIP,"valeur modifiée : $nouveauPar+$nouveauEtab");
        header("location: parcours?sucess=modification effectuée avec succèss");

    }else{
          header("location: parcours?erreur=$connexion->error");
      }


}
if(isset($_GET['suppar'])){

    $id=$_GET['suppar'];

    $sql="delete from parcours where id=$id";

    if($connexion->query($sql)){
        $userIP = $_SERVER['REMOTE_ADDR'];

        logUserAction($connexion,$_SESSION['id_user'],"suppression d'un parcours",date("Y-m-d H:i:s"),$userIP,"valeur supprimée : $id");

        header("location: parcours?sucess=suppression effectuée avec succèss");

    }else{
        header("location: parcours?erreur=$connexion->error");
      }

}




?>
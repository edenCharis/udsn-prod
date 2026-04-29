<?php

include '../php/connexion.php';
include '../php/lib.php';
session_start();

if(isset($_POST['element'])){

    $element =clean_input($_POST['element']);
    $type=clean_input($_POST['type']);
    $note =$_POST['note_max'];

    if(verifierNoteGroupe($type,$note,$connexion)){
        $sql ="insert into element values(null,'".$element."','".$type."',".$note.")";
        if($connexion->query($sql)){
         $userIP = $_SERVER['REMOTE_ADDR'];
     
         logUserAction($connexion,$_SESSION['id_user'],"enregistrement  element",date("Y-m-d H:i:s"),$userIP,"valeur enregistrée : $element+$type+$note");
     
         header("location: element?sucess=enregistrement effectuée avec succèss");
      }else{
         header("location: element?erreur=$connexion->error");
      }

    }else{

        header("location: element?erreur=La note maximale pour $type a été atteinte.");
    }

   
     
}




if(isset($_GET['supe'])){
    $id=$_GET['supe'];

    $sql="delete from element where id=$id";

    if($connexion->query($sql)){
        $userIP = $_SERVER['REMOTE_ADDR'];

        logUserAction($connexion,$_SESSION['id_user'],"suppression  element",date("Y-m-d H:i:s"),$userIP,"valeur supprimée : $id");

        header("location: element?sucess=suppression effectuée avec succèss");

    }else{
        header("location: element?erreur=$connexion->error");
      }
}

if(isset($_POST['typeelm']) and $_POST['note_max']){

   $li = str_replace("'","+",$_POST['typeelm']);
   $note =$_POST['note_max'];


   $sql ="insert into type_element values(null,'".$li."',".$note.")";
   if($connexion->query($sql)){
    $userIP = $_SERVER['REMOTE_ADDR'];

    logUserAction($connexion,$_SESSION['id_user'],"enregistrement type element",date("Y-m-d H:i:s"),$userIP,"valeur enregistrée : $li+$note");

    header("location: type?sucess=enregistrement effectuée avec succèss");
 }else{
    header("location: type?erreur=$connexion->error");
 }
}

if(isset($_GET['supte'])){
    $id=$_GET['supte'];

    $sql="delete from type_element where id=$id";

    if($connexion->query($sql)){
        $userIP = $_SERVER['REMOTE_ADDR'];

        logUserAction($connexion,$_SESSION['id_user'],"suppression type element",date("Y-m-d H:i:s"),$userIP,"valeur supprimée : $id");

        header("location: type?sucess=suppression effectuée avec succèss");

    }else{
        header("location: type?erreur=$connexion->error");
      }
}

if(isset($_POST['TEID'])){

    $id =$_POST['TEID'];
    $lib=str_replace("'","+",$_POST['TE']);
    $note=$_POST['note_max'];


    $sql ="UPDATE type_element set libelle_type='$lib',note_max='$note' where id=$id";

    if($connexion->query($sql)){
        $userIP = $_SERVER['REMOTE_ADDR'];
        logUserAction($connexion,$_SESSION['id_user'],"modification type element",date("Y-m-d H:i:s"),$userIP,"valeur modifiée : $lib+$note");
        header("location: type?sucess=modification effectuée avec succèss");
    }else{
        header("location: type?erreur=$connexion->error");
      }


}
if(isset($_POST['typeagent'])){

    $typeagent=$_POST['typeagent'];

  if($typeagent !== ""){
     $typeagent=str_replace("'","+",$typeagent);


     $sql= "insert into type_agent(libelle) values('".$typeagent."')";

     if($connexion->query($sql)){
        $userIP = $_SERVER['REMOTE_ADDR'];

        logUserAction($connexion,$_SESSION['id_user'],"enregistrement type agent",date("Y-m-d H:i:s"),$userIP,"valeur enregistrée : $typeagent");

        header("location: typeagent?sucess=enregistrement effectuée avec succèss");
     }else{
        header("location: typeagent?erreur=$connexion->error");
     }
  }else{
    header("location: typeagent?erreur=Ce champ ne peut pas être vide");
  }



}else if(isset($_GET['suppressiontagent'])){

    $id=$_GET['suppressiontagent'];

    $sql="delete from type_agent where id=$id";

    if($connexion->query($sql)){
        $userIP = $_SERVER['REMOTE_ADDR'];

        logUserAction($connexion,$_SESSION['id_user'],"suppression type agent",date("Y-m-d H:i:s"),$userIP,"valeur supprimée : $id");

        header("location: typeagent?sucess=suppression effectuée avec succèss");

    }else{
        header("location: typeagent?erreur=$connexion->error");
      }
    

}else if(isset($_POST['agentId'])){

    $id =$_POST['agentId'];
    $lib=str_replace("'","+",$_POST['nouveauType']);


    $sql ="UPDATE type_agent set libelle='$lib' where id=$id";

    if($connexion->query($sql)){
        $userIP = $_SERVER['REMOTE_ADDR'];

        logUserAction($connexion,$_SESSION['id_user'],"modification type agent",date("Y-m-d H:i:s"),$userIP,"valeur modifiée : $lib");

        header("location: typeagent?sucess=modification effectuée avec succèss");

    }else{
        header("location: typeagent?erreur=$connexion->error");
      }


}

if(isset($_GET["suppressiondoc"])){

    $id=$_GET['suppressiondoc'];

    $sql="delete from type_document where id=$id";

    if($connexion->query($sql)){
        $userIP = $_SERVER['REMOTE_ADDR'];

        logUserAction($connexion,$_SESSION['id_user'],"suppression type document",date("Y-m-d H:i:s"),$userIP,"valeur supprimée : $id");

        header("location: typedocument?sucess=suppression effectuée avec succèss");

    }else{
        header("location: typedocument?erreur=$connexion->error");
      }

}else if(isset($_POST['typedoc'])){

    $typedoc=$_POST['typedoc'];

    if($typedoc !== ""){
       $typedoc=str_replace("'","+",$typedoc);
  
  
       $sql= "insert into type_document(libelle) values('".$typedoc."')";
  
       if($connexion->query($sql)){
          $userIP = $_SERVER['REMOTE_ADDR'];
  
          logUserAction($connexion,$_SESSION['id_user'],"enregistrement type document",date("Y-m-d H:i:s"),$userIP,"valeur enregistrée : $typedoc");
  
          header("location: typedocument?sucess=enregistrement effectuée avec succèss");
       }else{
          header("location: typedocument?erreur=$connexion->error");
       }
    }else{
      header("location: typedocument?erreur=Ce champ ne peut pas être vide");
    }

}

if(isset($_POST['docId'])){

    $id =$_POST['docId'];
    $lib=str_replace("'","+",$_POST['nouveauType']);


    $sql ="UPDATE type_document set libelle='$lib' WHERE id=".$id;

    if($connexion->query($sql)){
        $userIP = $_SERVER['REMOTE_ADDR'];

        logUserAction($connexion,$_SESSION['id_user'],"modification type document",date("Y-m-d H:i:s"),$userIP,"valeur modifiée : $lib");

        header("location: typedocument?sucess=modification effectuée avec succèss");

    }else{
        header("location: typedocument?erreur=$connexion->error, $id $lib");
      }


}


if(isset($_GET['suppressionenseig'])){

    $id=$_GET['suppressionenseig'];

    $sql="delete from type_enseignant where id=$id";

    if($connexion->query($sql)){
        $userIP = $_SERVER['REMOTE_ADDR'];

        logUserAction($connexion,$_SESSION['id_user'],"suppression type enseignant",date("Y-m-d H:i:s"),$userIP,"valeur supprimée : $id");

        header("location: typeenseignant?sucess=suppression effectuée avec succèss");

    }else{
        header("location: typeenseignant?erreur=$connexion->error");
      }


}else if(isset($_POST['typeens'])){


    $typeens=$_POST['typeens'];

    if($typeens !== ""){
       $typeens=str_replace("'","+",$typeens);
  
  
       $sql= "insert into type_enseignant(libelle) values('".$typeens."')";
  
       if($connexion->query($sql)){
          $userIP = $_SERVER['REMOTE_ADDR'];
  
          logUserAction($connexion,$_SESSION['id_user'],"enregistrement type enseignant",date("Y-m-d H:i:s"),$userIP,"valeur enregistrée : $typeens");
  
          header("location: typeenseignant?sucess=enregistrement effectuée avec succèss");
       }else{
          header("location: typeenseignant?erreur=$connexion->error");
       }
    }else{
      header("location: typenseignant?erreur=Ce champ ne peut pas être vide");
    }


}else if(isset($_POST['typeId'])){

    $id =$_POST['typeId'];
    $lib=str_replace("'","+",$_POST['nouveauType']);


    $sql ="UPDATE type_enseignant set libelle='$lib' WHERE id=".$id;

    if($connexion->query($sql)){
        $userIP = $_SERVER['REMOTE_ADDR'];

        logUserAction($connexion,$_SESSION['id_user'],"modification type d'enseignant",date("Y-m-d H:i:s"),$userIP,"valeur modifiée : $lib");

        header("location: typeenseignant?sucess=modification effectuée avec succèss");

    }else{
        header("location: typeenseignant?erreur=$connexion->error, $id $lib");
      }


}


if(isset($_GET['supfrais'])){

    $id=$_GET['supfrais'];

    $sql="delete from type_frais where id=$id";

    if($connexion->query($sql)){
        $userIP = $_SERVER['REMOTE_ADDR'];

        logUserAction($connexion,$_SESSION['id_user'],"suppression type frais",date("Y-m-d H:i:s"),$userIP,"valeur supprimée : $id");

        header("location: typefrais?sucess=suppression effectuée avec succèss");

    }else{
        header("location: typefrais?erreur=$connexion->error");
      }


}else if(isset($_POST['typefrais'])){


    $typefrais=$_POST['typefrais'];

    if($typefrais !== ""){
       $typefrais=str_replace("'","+",$typefrais);
  
  
       $sql= "insert into type_frais(libelle) values('".$typefrais."')";
  
       if($connexion->query($sql)){
          $userIP = $_SERVER['REMOTE_ADDR'];
  
          logUserAction($connexion,$_SESSION['id_user'],"enregistrement type frais",date("Y-m-d H:i:s"),$userIP,"valeur enregistrée : $typedoc");
  
          header("location: typefrais?sucess=enregistrement effectuée avec succèss");
       }else{
          header("location: typefrais?erreur=$connexion->error");
       }
    }else{
      header("location: typefrais?erreur=Ce champ ne peut pas être vide");
    }
}else if(isset($_POST['fraisId'])){

    $id =$_POST['fraisId'];
    $lib=str_replace("'","+",$_POST['nouveauType']);


    $sql ="UPDATE type_frais set libelle='$lib' WHERE id=$id";

    if($connexion->query($sql)){
        $userIP = $_SERVER['REMOTE_ADDR'];

        logUserAction($connexion,$_SESSION['id_user'],"modification type de frais",date("Y-m-d H:i:s"),$userIP,"valeur modifiée : $lib");

        header("location: typefrais?sucess=modification effectuée avec succèss");

    }else{
          header("location: typefrais?erreur=$connexion->error , $id and $lib");
      }

}

if(isset($_GET['supgrade'])){

    $id=$_GET['supgrade'];

    $sql="delete from type_grade where id=$id";

    if($connexion->query($sql)){
        $userIP = $_SERVER['REMOTE_ADDR'];

        logUserAction($connexion,$_SESSION['id_user'],"suppression type grade",date("Y-m-d H:i:s"),$userIP,"valeur supprimée : $id");

        header("location: typegrade?sucess=suppression effectuée avec succèss");

    }else{
        header("location: typegrade?erreur=$connexion->error");
      }

}else if(isset($_POST['typegrade'])){
    $typegrade=$_POST['typegrade'];

    if($typegrade !== ""){
       $typegrade=str_replace("'","+",$typegrade);
       $th =$_POST["th"];
  
  
       $sql= "insert into type_grade(libelle,th) values('".$typegrade."',$th)";
  
       if($connexion->query($sql)){
          $userIP = $_SERVER['REMOTE_ADDR'];
  
          logUserAction($connexion,$_SESSION['id_user'],"enregistrement type grade",date("Y-m-d H:i:s"),$userIP,"valeur enregistrée : $typedoc+$th");
  
          header("location: typegrade?sucess=enregistrement effectuée avec succèss");
       }else{
          header("location: typegrade?erreur=$connexion->error");
       }
    }else{
      header("location: typegrade?erreur=Ce champ ne peut pas être vide");
    }


}else if(isset($_POST['gradeId'])){

    $id =$_POST['gradeId'];
    $lib=str_replace("'","+",$_POST['nouveauType']);
    $th=$_POST["nouveauTh"];


    $sql ="UPDATE  type_grade set libelle='$lib',th=$th WHERE id=$id";

    if($connexion->query($sql)){
        $userIP = $_SERVER['REMOTE_ADDR'];

        logUserAction($connexion,$_SESSION['id_user'],"modification type de grade",date("Y-m-d H:i:s"),$userIP,"valeur modifiée : $lib+$th");

        header("location: typegrade?sucess=modification effectuée avec succèss");

    }else{
          header("location: typegrade?erreur=$connexion->error");
      }
}

//********************************************gestion des traitements du semestre******************************************** */

if(isset($_GET['supsem'])){

    $id=$_GET['supsem'];

    $sql="delete from semestre where id=$id";

    if($connexion->query($sql)){
        $userIP = $_SERVER['REMOTE_ADDR'];

        logUserAction($connexion,$_SESSION['id_user'],"suppression d'un semestre",date("Y-m-d H:i:s"),$userIP,"valeur supprimée : $id");

        header("location: semestre?sucess=suppression effectuée avec succèss");

    }else{
        header("location: semestre?erreur=$connexion->error");
      }
}else if(isset($_POST['typesem'])){

    $typesem=$_POST['typesem'];

    if($typesem !== ""){
       $typesem=str_replace("'","+",$typesem);
  
  
       $sql= "insert into semestre(libelle) values('".$typesem."')";
  
       if($connexion->query($sql)){
          $userIP = $_SERVER['REMOTE_ADDR'];
  
          logUserAction($connexion,$_SESSION['id_user'],"enregistrement d'un semestre",date("Y-m-d H:i:s"),$userIP,"valeur enregistrée : $typesem");
  
          header("location: semestre?sucess=enregistrement effectuée avec succèss");
       }else{
          header("location: semestre?erreur=$connexion->error");
       }
    }else{
      header("location: semestre?erreur=Ce champ ne peut pas être vide");
    }

}else if(isset($_POST['semId'])){
    $id =$_POST['semId'];
    $lib=str_replace("'","+",$_POST['nouveauType']);


    $sql ="UPDATE semestre set libelle='$lib' WHERE id=$id";

    if($connexion->query($sql)){
        $userIP = $_SERVER['REMOTE_ADDR'];

        logUserAction($connexion,$_SESSION['id_user'],"modification d'un semestre",date("Y-m-d H:i:s"),$userIP,"valeur modifiée : $lib");

        header("location: semestre?sucess=modification effectuée avec succèss");

    }else{
          header("location: semestre?erreur=$connexion->error");
      }}

      //************************************** gestion des traitements niveaux ****************************************** */

      if(isset($_GET['supniv'])){

        $id=$_GET['supniv'];

        $sql="delete from niveau where id=$id";
    
        if($connexion->query($sql)){
            $userIP = $_SERVER['REMOTE_ADDR'];
    
            logUserAction($connexion,$_SESSION['id_user'],"suppression d'un niveau",date("Y-m-d H:i:s"),$userIP,"valeur supprimée : $id");
    
            header("location: niveau?sucess=suppression effectuée avec succèss");
    
        }else{
            header("location: niveau?erreur=$connexion->error");
          }
      }else if(isset($_POST['typeniv'])){
        

        $typeniv=$_POST['typeniv'];

        if($typeniv !== ""){
           $typeniv=str_replace("'","+",$typeniv);
      
      
           $sql= "insert into niveau(libelle) values('".$typeniv."')";
      
           if($connexion->query($sql)){
              $userIP = $_SERVER['REMOTE_ADDR'];
      
              logUserAction($connexion,$_SESSION['id_user'],"enregistrement d'un niveau",date("Y-m-d H:i:s"),$userIP,"valeur enregistrée : $typeniv");
      
              header("location: niveau?sucess=enregistrement effectuée avec succèss");
           }else{
              header("location: niveau?erreur=$connexion->error");
           }
        }else{
          header("location: niveau?erreur=Ce champ ne peut pas être vide");
        }

      }else if(isset($_POST['nivId'])){


        $id =$_POST['nivId'];
        $lib=str_replace("'","+",$_POST['nouveauType']);
    
    
        $sql ="UPDATE niveau set libelle='$lib' WHERE id=$id";
    
        if($connexion->query($sql)){
            $userIP = $_SERVER['REMOTE_ADDR'];
    
            logUserAction($connexion,$_SESSION['id_user'],"modification d'un niveau",date("Y-m-d H:i:s"),$userIP,"valeur modifiée : $lib");
    
            header("location: niveau?sucess=modification effectuée avec succèss");
    
        }else{
              header("location: niveau?erreur=$connexion->error");
          }

      }

//********************************************** gestion des traitements des parcours******************************* */
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
//************************************************************************************** gestion des spécialités********************************** */

if($_POST['debut'] and $_POST['fin']){

    $t =$_POST['debut']."-".$_POST["fin"];
    $sql= "insert into annee(libelle) values('".$t."')";
      
    if($connexion->query($sql)){
       $userIP = $_SERVER['REMOTE_ADDR'];

       logUserAction($connexion,$_SESSION['id_user'],"enregistrement d'une année scolaire",date("Y-m-d H:i:s"),$userIP,"valeur enregistrée : $t");

       header("location: annee?sucess=enregistrement effectuée avec succèss");
    }else{
       header("location: annee?erreur=$connexion->error");
    }


}



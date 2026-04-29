<?php 

include '../php/connexion.php';
include '../php/lib.php';
session_start();

 

if ($_SERVER["REQUEST_METHOD"] == "POST"){


  if(isset($_POST['soutenance']) and isset($_POST['element'] ) and isset($_POST['note']))
  {

            $soutenance = $_POST['soutenance'];
            $element = $_POST['element'];
            $note = $_POST['note'];

            $sql ="insert into eval values(null,'$soutenance','$element',$note,'".$_SESSION['etablissement']."')";

            if($connexion->query($sql)){

              $userIP = $_SERVER['REMOTE_ADDR'];

              logUserAction($connexion,$_SESSION['id_user'],"enregistrement d'une note d'element d'appreciation ",date("Y-m-d H:i:s"),$userIP,"valeur enregistrée : $soutenance+$element+$note");
              header("location: evaluation?sucess=enregistrement effectué avec success&soutenance=$soutenance");

            }else{
              header("location: evaluation?erreur=$connexion->error&soutenance=$soutenance");
  
            }
       
  }

  
  if(isset($_POST['nom']) and isset($_POST['cycle']) and isset($_POST['matricule']) and isset($_POST['prenom'])){
  $matricule =$_POST['matricule'];
  $annee_acad =$_POST['annee_acad'];

    $imp1 = verifierImpetrant($matricule,$connexion,$annee_acad);
    $imp2=VerifierPayementFraisSoutenance($matricule,$annee_acad,$connexion);

    if($imp1 == false){

      header("location: index?erreur=L'etudiant  $matricule n'existe pas.");
      exit;

    }

    if($imp2 == false){

         header("location: index?erreur=L'etudiant  $matricule n'a pas  payé les frais de soutenance.");
         exit;
    }

    

    $imp = getIdInscription($matricule,$annee_acad,$connexion);


       $soutenance= generateCodeSoutenance();
       $promo=$_POST['annee'];
      $datenaissance = $_POST['datenaissance'];
      $lieunaissance= $_POST['lieunaissance'];
      $specialite=$_POST['specialite'];
      $departement =$_POST['departement'];
      $nom =$_POST['nom'];
      $prenom=$_POST['prenom'];
      $niveau =$_POST['niveau'];

    $date = $_POST['datep'];
    $debut=$_POST['debut'];
    $fin =$_POST['fin'];
    $mention =$_POST['mention'];
    $cycle =$_POST['cycle'];
    $dm=$_POST['dm'];
    $theme=clean_input($_POST['theme']);


    $ref = generateReference();
    $date_nom=date("Y-m-d");
    $president =$_POST['president'];
    $rapp_ext =$_POST['rapp_ext'];
    $rapp_int =$_POST['rapp_int'];
    $examin =$_POST['examin'];

    $autre =$_POST['autre'];

    $note = $_POST['note'];

    $etab =$_SESSION['etablissement'];
    $user =$_SESSION['id_user'];

    $sql = "insert into soutenance values(null,'$soutenance','$theme','$dm','$date','$debut','$fin','$imp','$nom','$prenom','$datenaissance','$lieunaissance','$niveau','$departement','$specialite','$mention',$note,'$cyce','$promo','$annee_acad','$etab')";
    $userIP = $_SERVER['REMOTE_ADDR'];

  $sql1 ="insert into jury values(null,'$ref','$date_nom','$president','$rapp_ext','$rapp_int','$examin','$autre',null,null,'$soutenance','$etab')" ;


    if($connexion->query($sql) and $connexion->query($sql1)){

        logUserAction($connexion,$_SESSION['id_user'],"enregistrement d'une soutenance ",date("Y-m-d H:i:s"),$userIP,"valeur enregistrée : $soutenance");
        logUserAction($connexion,$_SESSION['id_user'],"enregistrement d'un jury ",date("Y-m-d H:i:s"),$userIP,"valeur enregistrée : $soutenance+$ref");
        header("location: index?sucess=enregistrement effectué avec success");
       
  
    }else{
        header("location: index?erreur=$connexion->error $imp");
  
    }



  }



    

}


if( isset($_POST['soutenance']) and isset($_POST['note_modif'])){

  $note=$_POST['note_modif'];
  $soutenance =$_POST['soutenance'];

  $sql ="update soutenance set note=$note where code='".$soutenance."'";


if($connexion->query($sql)){
  $userIP = $_SERVER['REMOTE_ADDR'];

  logUserAction($connexion,$_SESSION['id_user'],"modification d'une note de soutenance",date("Y-m-d H:i:s"),$userIP,"valeur modififé : $soutenace+$note");

  header("location: evaluation?soutenance=$soutenance");


}else{
  header("location: evaluation?soutenance=$soutenance&error=$connexion->error");

}
    

}


if(isset($_GET['sup'])){
  $id =$_GET['sup'];
  $sql="delete from soutenance where id=$id";

  if($connexion->query($sql)){
      $userIP = $_SERVER['REMOTE_ADDR'];

      logUserAction($connexion,$_SESSION['id_user'],"suppression  d'un pv soutenance",date("Y-m-d H:i:s"),$userIP,"valeur supprimée : $id");

      header("location: index?sucess=suppression effectuée avec succèss");

  }else{
      header("location: index?erreur=$connexion->error");
    }
}

?>
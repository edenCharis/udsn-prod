<?php 

include '../php/connexion.php';
include '../php/lib.php';
session_start();

if($_SERVER["REQUEST_METHOD"] == "POST")
{
if(isset($_POST['nom']) and isset($_POST['prenom'])){

  $nom=strtoupper(str_replace("'","+",$_POST['nom']));
  $prenom=strtolower(str_replace ("'","+",$_POST['prenom']));
  $grade =str_replace("'","+",$_POST['grade']);
  $diplome =str_replace("'","+",$_POST['diplome']);
  $statut =str_replace("'","+",$_POST['statut']);
  $email =$_POST['email'];
  $telephone = $_POST['telephone'];
  $th=$_POST['th'];
  $code = genererMatriculeEnseignant($prenom,$nom);
  $etab = $_SESSION['etablissement'];
  $sql ="insert into enseignant values(null,'$nom','$code','$prenom','$telephone','$email','$grade','$diplome','$th','$statut','$etab')";

  if($connexion->query($sql)){
        
    $userIP = $_SERVER['REMOTE_ADDR'];

    logUserAction($connexion,$_SESSION['id_user'],"ajout d'un enseignant ",date("Y-m-d H:i:s"),$userIP,"valeur ajouté : $code");

    header("location: enseignant?sucess=Opération effectuée avec succèss");

  }else{

    header("location: enseignant?erreur=$connexion->error");

  }

} else if(isset($_POST['nouveauNom']) and isset($_POST['nouveauPrenom']) and isset($_POST['ensId'])){
  $nom=strtoupper(str_replace("'","+",$_POST['nouveauNom']));
  $prenom=strtolower(str_replace ("'","+",$_POST['nouveauPrenom']));
  $grade =str_replace("'","+",$_POST['nouveauGrade']);
  $diplome =str_replace("'","+",$_POST['nouveauDiplome']);
  $statut =str_replace("'","+",$_POST['nouveauStatut']);
  $email =$_POST['nouveauEmail'];
  $telephone = $_POST['nouveauTelephone'];
  $th=$_POST['nouveauTh'];
  $code = $_POST['ensId'];
  $etab = $_SESSION['etablissement'];
  $sql = " UPDATE enseignant set nom='$nom', prenom='$prenom', grade='$grade' , diplome='$diplome', type='$statut', email='$email',
  telephone='$telephone', th=$th where id=$code";

if($connexion->query($sql)){
    
  $userIP = $_SERVER['REMOTE_ADDR'];
  logUserAction($connexion,$_SESSION['id_user'],"modification d'un enseignant ",date("Y-m-d H:i:s"),$userIP,"valeur modifié : $code");
  header("location: enseignant?sucess=Opération effectuée avec succèss");
   
}else{
    
  header("location: enseignant?erreur=$connexion->error");

}




}  else if(isset($_POST['enseignant']) and isset($_POST['diplome']) and isset($_POST['annee'])){

      $enseignant = $_POST['enseignant'];
      $diplome = str_replace("'","+",$_POST['diplome']);
      $grade=str_replace("'","+",$_POST['grade']);
      $annee= $_POST['annee'];
      $th=$_POST['th'];


      $sql = "INSERT into grade values(null,'$enseignant','$grade','$diplome','$annee','$th','".$_SESSION['etablissement']."')";

      if($connexion->query($sql)){
           if(modifierEnseignant($enseignant,$diplome,$th,$grade,$connexion)){
            $userIP = $_SERVER['REMOTE_ADDR'];
            logUserAction($connexion,$_SESSION['id_user'],"modification d'un grade d'enseignant ",date("Y-m-d H:i:s"),$userIP,"valeur modifié : $enseignant");

            header("location: grade?sucess=Mise à jour effectuée avec succèss");
           }else{
            header("location: grade?erreur=$connexion->error ( UPDATE)");

           }
      }else{

        header("location: grade?erreur=$connexion->error");
      }

    }
 
else if(isset($_POST['enseignant']) and isset($_POST['classe']) and isset($_POST['semestre']) and isset($_POST['annee'])){


         $enseignant = $_POST['enseignant'];
         $classe = clean_input($_POST['classe']);
         $semestre=$_POST['semestre'];
         $annee=$_POST['annee'];
         $ecue =$_POST['ecue'];
         $etab =$_SESSION['etablissement'];

         $sql ="INSERT INTO affectation values(null,'".date("Y-m-d")."','$enseignant', '$ecue', '$classe', '$annee','$etab','$semestre')";

         if($connexion->query($sql)){

          $userIP = $_SERVER['REMOTE_ADDR'];
          logUserAction($connexion,$_SESSION['id_user'],"affectation d'un cours a un enseignant ",date("Y-m-d H:i:s"),$userIP,"valeur modifié : $enseignant+$ecue+$classe+$semestre+$annee");

          header("location: affectation?sucess=Operation effectuée avec succèss");
         
           }else{
            header("location: affectation?erreur=$connexion->error");
           }
         }else if(isset($_POST['affId'])){
             

                 $id = $_POST['affId'];
                 $ecue = clean_input($_POST['nouveauEcue']);
                 $classe = clean_input($_POST['nouveauClasse']);
                 $semestre= clean_input($_POST['nouveauSemestre']);
                
                 $annee =$_POST['annee'];

             
                 $sql ="UPDATE affectation set ecue='$ecue',classe='$classe',semestre='$semestre',annee='$annee' where id=$id ";


                 if($connexion->query($sql)){

                
          $userIP = $_SERVER['REMOTE_ADDR'];
          logUserAction($connexion,$_SESSION['id_user'],"modification d'une affectation d'enseignant ",date("Y-m-d H:i:s"),$userIP,"valeur modifié :$ecue+$classe+$semestre+$annee");

          header("location: affectation?sucess=Modification effectuée avec succèss");
         
           }else{
            header("location: affectation?erreur=$connexion->error");
           }

         }


         if(isset($_POST['enseignant']) and isset($_POST['debut']) and isset($_POST['fin']) and isset($_POST['nature'])){



               $enseignant = $_POST['enseignant'];
               $code_ecue = $_POST['ecue'];
               
               $ecue = getecue($code_ecue,$connexion);
               $classe = $_POST['classe'];
               $nature= $_POST['nature'];
               $debut =$_POST['debut'];
               $fin = $_POST['fin'];
               $datecours=$_POST['datecours'];
               $annee = $_POST['annee'];
               $etab =$_SESSION['etablissement'];
               $sem = obtenirSemestrePourECUE($code_ecue,$connexion);



        if (!empty($debut) && !empty($fin)) {
              
              
              
              $differenceTotal = calculateHourDifference($debut,$fin);
                $resultat_requete = verifierAffectationEnseignant($enseignant,$code_ecue,$annee,$sem,$connexion1,$classe,$etab);
                

   if($resultat_requete){
      
     $sql ="INSERT INTO cours(enseignant,classe,ecue,code_ecue,heure,date_c,nature,semestre,annee,etab,user_id) values ('$enseignant','$classe','$ecue','$code_ecue',$differenceTotal,'$datecours','$nature','$sem','$annee','$etab',".$_SESSION["id_user"].")";
    
        if($connexion1->query($sql)){
          $userIP = $_SERVER['REMOTE_ADDR'];
          logUserAction($connexion,$_SESSION['id_user'],"enregistrement d'un cours ",date("Y-m-d H:i:s"),$userIP,"valeur enregistrée :$ecue+$enseignant+$debut+$fin+$datecours");

          header("location: index?sucess=Enregistrement effectuée avec succèss");
         
           }else{
            header("location: index?erreur=$connexion->error");
           }

        }else{
         header("location: index?erreur=L'enseignant $enseignant n'a pas été affecté l'ecue $code_ecue  cette  année académique ($sem)  $resultat_requete" );
     }
         }else{
             
               header("location: index?erreur=les informations sur les horaires manquent");
 
         }
  }
  



}

if(isset($_GET['suphisto'])){

  $id =$_GET['suphisto'];

     $sql ="DELETE FROM grade where id=$id";

     if($connexion->query($sql)){
      $userIP = $_SERVER['REMOTE_ADDR'];
      logUserAction($connexion,$_SESSION['id_user'],"suppression d'un grade d'enseignant ",date("Y-m-d H:i:s"),$userIP,"valeur modifié : $id");

      header("location: grade?sucess=Suppression effectuée avec succèss");
    }else{
     header("location: grade?erreur=$connexion->error");

    }
}

if(isset($_GET['supaff'])){


  $id =$_GET['supaff'];

  $sql ="DELETE FROM affectation where id=$id";

  if($connexion->query($sql)){
   $userIP = $_SERVER['REMOTE_ADDR'];
   logUserAction($connexion,$_SESSION['id_user'],"suppression d'une affectation d'enseignant ",date("Y-m-d H:i:s"),$userIP,"valeur modifié : $id");

   header("location: affectation?sucess=Suppression effectuée avec succèss");
 }else{
  header("location: affectation?erreur=$connexion->error");

 }

}

if(isset($_GET['supcours']))
{
  $id =$_GET['supcours'];

  $sql ="DELETE FROM cours where id=$id";

  if($connexion->query($sql)){
   $userIP = $_SERVER['REMOTE_ADDR'];
   logUserAction($connexion,$_SESSION['id_user'],"suppression d'une cours d'enseignant ",date("Y-m-d H:i:s"),$userIP,"valeur supprimé : $id");

   header("location: index?sucess=Suppression effectuée avec succèss");
 }else{
  header("location: index?erreur=$connexion->error");

 }
}


?>
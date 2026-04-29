<?php 

include '../php/connexion.php';
include '../php/lib.php';
session_start();
 
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);


if(isset($_POST['nom']) and isset($_POST['prenom'])){

  $nom=strtoupper(str_replace("'","+",$_POST['nom']));
  $prenom=strtolower(str_replace ("'","+",$_POST['prenom']));
  $grade =str_replace("'","+",$_POST['grade']);
  $diplome =str_replace("'","+",$_POST['diplome']);
  $statut =str_replace("'","+",$_POST['statut']);
  $email =$_POST['email'];
  $telephone = $_POST['telephone'];
  $th=  getThByGrade($connexion,$grade);
  
  $code = genererMatriculeEnseignant($prenom,$nom);
  $etab = $_SESSION['etablissement'];
  $sexe=$_POST["sexe"];
  $sql ="insert into enseignant(nom,code,prenom,telephone,email,sexe,grade,diplome,th,statut,etab) values('$nom','$code','$prenom','$telephone','$email','$sexe','$grade','$diplome','$th','$statut','$etab')";

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
  $telephone = $_POST['nouveauTel'];
 $th=getThByGrade($connexion,$grade);
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
         $ecue = str_replace("'","+",$_POST['ecue']);
         $etab =$_SESSION['etablissement'];


         $contrat = getContratEnseignant($enseignant,$connexion);


     


         if($contrat !== null){

          $ecues = getEcuesContrat($contrat,$etab,$connexion);

          if(in_array($ecue,$ecues)){
            $sql1 ="INSERT INTO affectation(id,date_aff,enseignant,code_ecue,classe,annee,etab,semestre) VALUES (NULL,'".date("Y-m-d")."','$enseignant', '$ecue', '$classe', '$annee','$etab','$semestre')";

            if($connexion->query($sql1)){
   
             $userIP = $_SERVER['REMOTE_ADDR'];
             logUserAction($connexion,$_SESSION['id_user'],"affectation d'un cours a un enseignant ",date("Y-m-d H:i:s"),$userIP,"valeur modifié : $enseignant+$ecue+$classe+$semestre+$annee");
   
             header("location: affectation?sucess=Operation effectuée avec succèss");
            
              }else{
               header("location: affectation?erreur=$connexion->error");
              }
          }else{
            header("location: affectation?erreur=Cet Ecue n'est pas dans le contrat d'enseignement de cet enseignant.");
                  exit;
          }
        

         }else{

          header("location: affectation?erreur=Cet enseignant n'a pas de contrat.");
          exit;
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
               $ecue = clean_input($_POST['ecue']);
               $classe = clean_input($_POST['classe']);
               $nature= $_POST['nature'];
               $debut =$_POST['debut'];
               $fin = $_POST['fin'];
               $datecours=$_POST['datecours'];
               $annee = $_POST['annee'];
               $etab =$_SESSION['etablissement'];
               $sem = obtenirSemestrePourECUE($ecue,$connexion);



               if (!empty($debut) && !empty($fin)) {
                // Convertir les heures en objets DateTime
                $dateTimeDebut = new DateTime($debut);
                $dateTimeFin = new DateTime($fin);
    
                // Calculer la différence entre les deux heures
                $difference = $dateTimeDebut->diff($dateTimeFin);
                $differenceHeures = $difference->format('%H');

   if(verifierAffectationEnseignant($enseignant,$ecue,$annee,$sem,$connexion)){
      
    $sql ="INSERT INTO cours values (null,'$enseignant','$classe','$ecue',$differenceHeures,'$datecours','$nature','$annee','$etab')";
    

        if($connexion->query($sql)){
          $userIP = $_SERVER['REMOTE_ADDR'];
          logUserAction($connexion,$_SESSION['id_user'],"enregistrement d'un cours ",date("Y-m-d H:i:s"),$userIP,"valeur enregistrée :$ecue+$enseignant+$debut+$fin+$datecours");

          header("location: cours?sucess=Enregistrement effectuée avec succèss");
         
           }else{
            header("location: cours?erreur=$connexion->error");
           }


   }else{


         header("location: cours?erreur=L'enseignant $enseignant n'a pas été affecté l'ecue $ecue cet année académique ($sem)" );
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

   header("location: cours?sucess=Suppression effectuée avec succèss");
 }else{
  header("location: cours?erreur=$connexion->error");

 }
}


?>
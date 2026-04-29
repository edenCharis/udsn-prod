<?php 
include '../php/connexion.php';
include '../php/lib.php';
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

$code_etab=$_SESSION['etablissement'];
$libelle_etab=$_SESSION['lib_etab'];
$role =$_SESSION['role'];

$id=$_SESSION['id_user'];

if ($_SERVER["REQUEST_METHOD"] == "POST"){



  if(isset($_POST['code123']) and isset($_POST['classe']) and isset($_POST['niveau']) and isset($_POST['annee']) and isset($_POST['spec']))
  {

        $candidat =$_POST['code123'];
        $classe =$_POST['classe'];
        $niveau =$_POST['niveau'];
        $annee = $_POST['annee'];
        $spec=$_POST['spec'];

        $sql = "INSERT INTO inscription (candidat, classe,annee,etab) VALUES ('$candidat', '$classe', '$annee' ,'".$_SESSION['etablissement']."')";
        if($connexion->query($sql)){
         $userIP = $_SERVER['REMOTE_ADDR'];
         logUserAction($connexion,$_SESSION['id_user'],"inscription d'un candidat ",date("Y-m-d H:i:s"),$userIP,"valeur enregistrée : $candidat+$classe+$spec+$annee");

          if( changerStatutCandidat($candidat,$annee,"inscrit",$connexion,$_SESSION["lib_etab"]) == TRUE){
            header("location: etudiant?sucess=Le candidat a été inséré dans la classe $classe de la spécialité $spec.");
            exit;

          }else{
            header("location: etudiant?sucess=Le candidat a été inséré dans la classe $classe de la spécialité $spec. mais hum..");
            exit;
          }
            
       }else{
           header("location: etudiant?erreur=$connexion->error");
       }
        

  }


    if(isset($_POST['ue']) and isset($_POST['specialite']) and isset($_POST['niveau']) and isset($_POST["codeUE"])){

       $ue=str_replace("'","+",$_POST['ue']);
       $sem=$_POST['semestre'];
       $vh=$_POST['vh'];
       $spec=str_replace("'","+",$_POST['specialite']);
       $niveau=str_replace("'","+",$_POST['niveau']);
       $code =str_replace("'","+",$_POST['codeUE']); 


       $sql="INSERT INTO ue values(null,'$ue','$code','$sem',$vh,'$spec','$niveau','".$_SESSION['etablissement']."')";

       if($connexion->query($sql)){
        $userIP = $_SERVER['REMOTE_ADDR'];

        logUserAction($connexion,$_SESSION['id_user'],"enregistrement d'une UE",date("Y-m-d H:i:s"),$userIP,"valeur enregistrée : $ue+$sem+$vh+$niveau+$spec");
    

        header("location: ue?sucess=Enregistrement effectué avec success");
       }else{
        header("location: ue?erreur=$connexion->error");

       }



    }else if(isset($_POST['ueId'])){

            $idUe=$_POST['ueId'];
            $nouvUE=str_replace("'","+",$_POST['nouveauUe']);
            $nouvSem=str_replace("'","+",$_POST['nouveauSem']);
            $nouvh=$_POST['nouveauVH'];
            $nouvSpec=str_replace("'","+",$_POST['nouveauSpec']);
            $nouvNiv=str_replace("'","+",$_POST['nouveauNiv']);


            $sql ="UPDATE ue set libelle='$nouvUE',semestre='$nouvSem',VH=$nouvh,Specialite='$nouvSpec' where id=$idUe";

            if($connexion->query($sql)){
                $userIP = $_SERVER['REMOTE_ADDR'];

                logUserAction($connexion,$_SESSION['id_user'],"modification d'une UE",date("Y-m-d H:i:s"),$userIP,"valeur enregistrée : $nouvUE+$nouvSem+$nouvh+$nouvNiv+$nouvSpec");
            
        
                header("location: ue?sucess=Modification effectué avec success");
               }else{
                header("location: ue?erreur=$connexion->error");
        
               }
    }


    if(isset($_POST['ecue']) and isset($_POST['vhcm']) and isset($_POST['vhtp'])){

        $ecue=str_replace("'","+",$_POST['ecue']);
        $ue=str_replace("'","+",$_POST['ue']);
          $code =str_replace("'","+",$_POST['code']); 
      
       $vg=$_POST['vhcm']+$_POST['vhtp']+$_POST['vhtd'];
 
        $sql="INSERT INTO ecue(code_ecue,libelle,vhcm,vhtp,vhtd,vhg,credit,code_ue,etab) values('$code','$ecue',".$_POST['vhcm'].",".$_POST['vhtp'].",".$_POST['vhtd'].",".$vg.",".$_POST['credit'].",'".$ue."','".$_SESSION['etablissement']."')";
echo $sql;
 
        if($connexion->query($sql)){
            $userIP = $_SERVER['REMOTE_ADDR'];
/*
        if(verifierExistenceUEDansRepartition($ue,$_SESSION["etablissement"],$connexion))
         {
              $classe = getClasseInRepartition($ue,$_SESSION["etablissement"],$connexion);

              $sql1 ="INSERT into repartition values(null,'$classe','$ecue','$ue','".$_SESSION["etablissement"]."')";
                
              if($connexion->query($sql1))
              {
                logUserAction($connexion,$_SESSION['id_user'],"mis à jour d'une ECUE dans la repartition",date("Y-m-d H:i:s"),$userIP,"valeur enregistrée : $ue+$ecue+$classe");
     
 
              }else{
                header("location: ecue?erreur=$connexion->error");
              }

         }else{
           echo "dadad";
         }
         
         */
 
         logUserAction($connexion,$_SESSION['id_user'],"enregistrement d'une ECUE",date("Y-m-d H:i:s"),$userIP,"valeur enregistrée : $ue+$ecue; volume horaire total =$vg");
     
 
         header("location: ecue?sucess=Enregistrement effectué avec success");
        }else{
         header("location: ecue?erreur=$connexion->error");
 
        }
    
    

}else if(isset($_POST['ecueId'])){
    $code =$_POST['nouveauCode'];

        $ecue =str_replace("'","+",$_POST['nouveauEcue']);
      //  $ue=str_replace("'","+",$_POST['nouveauue']);

        $vg=$_POST['nouveauvhcm']+$_POST['nouveauvhtp']+$_POST['nouveauvhtd'];



        $sql ="UPDATE ecue set code_ecue='$code',libelle='$ecue',VHCM=".$_POST['nouveauvhcm'].",VHTP=".$_POST['nouveauvhtp'].",VHTD=".$_POST['nouveauvhtd'].",VHG=$vg,credit=".$_POST['nouveaucredit']." where id=".$_POST['ecueId'];


 if($connexion->query($sql)){
         $userIP = $_SERVER['REMOTE_ADDR'];
 
         logUserAction($connexion,$_SESSION['id_user'],"modification d'un ECUE",date("Y-m-d H:i:s"),$userIP,"valeur enregistrée : $ue+$ecue; volume horaire total =$vg");
     
 
         header("location: ecue?sucess=Enregistrement effectué avec success");
        }else{
         header("location: ecue?erreur=$connexion->error");
 
        }
    

}


  if(isset($_POST['classe']) and isset($_POST['niveau']) and isset( $_POST['specialite'])){


    $classe=str_replace("'","+",$_POST['classe']);
    $niv=str_replace("'","+",$_POST['niveau']);
    $spec=str_replace("'","+",$_POST['specialite']);
  
 

    $sql="INSERT INTO classe values(null,'$classe','".$niv."','".$spec."','".$_SESSION['etablissement']."')";

    if($connexion->query($sql)){
     $userIP = $_SERVER['REMOTE_ADDR'];

     logUserAction($connexion,$_SESSION['id_user'],"enregistrement d'une classe",date("Y-m-d H:i:s"),$userIP,"valeur enregistrée : $classe+$niv +$spec");
 

     header("location: classe?sucess=Enregistrement effectué avec success");
    }else{
     header("location: classe?erreur=$connexion->error");

    }


  }



  if(isset($_POST['classeId'])){
    $classe=str_replace("'","+",$_POST['nouveauClasse']);
    $niv=str_replace("'","+",$_POST['nouveauniv']);
    $spec=str_replace("'","+",$_POST['nouveauspec']);
  
 

    $sql="UPDATE classe set libelle='$classe',niveau='".$niv."', specialite='".$spec."' where id=".$_POST['classeId'];

    if($connexion->query($sql)){
     $userIP = $_SERVER['REMOTE_ADDR'];

     logUserAction($connexion,$_SESSION['id_user'],"modification d'une classe",date("Y-m-d H:i:s"),$userIP,"valeur modifiée : $classe+$niv +$spec");
 
              
     header("location: classe?sucess=Modification effectué avec success");
    }else{
     header("location: classe?erreur=$connexion->error");

    }


  }

  if(isset($_POST['classe']) and isset($_POST['ue'])){
    $classe=str_replace("'","+",$_POST['classe']);
    $ue=str_replace("'","+",$_POST['ue']);
    $ecues = array();
 
    $ecues=getListeEcueFromUE($ue,$_SESSION["etablissement"],$connexion);

$test = false;
    if(!empty($ecues)){

      
foreach ($ecues as $ecue){

  $sql="INSERT into repartition values(null,'$classe','$ecue','$ue','".$_SESSION['etablissement']."')";

  if($connexion->query($sql)){
   $userIP = $_SERVER['REMOTE_ADDR'];

   logUserAction($connexion,$_SESSION['id_user'],"attribution ecue /classe",date("Y-m-d H:i:s"),$userIP,"valeur modifiée : $classe+$ecue");

   $test = true;
  }    else{
    header("location: repartition?erreur=$connexion->error");
    exit;

   }
}
    }else{

      header("location: repartition?erreur=L'UE choisit n'a aucun ECUE.");
      exit;
    }
 

    if($test){
      header("location: repartition?sucess=enregistrement effectué avec success");

    }else{
      
      header("location: repartition?erreur=Erreur survenue lors de l'opération ! $connexion->error");

    }
  


     




  }


}



if(isset($_GET['supue'])){


    $idUE=$_GET['supue'];

    $sql="delete from ue where id=$idUE";

    if($connexion->query($sql)){
        $userIP = $_SERVER['REMOTE_ADDR'];

        logUserAction($connexion,$_SESSION['id_user'],"suppression d'un UE",date("Y-m-d H:i:s"),$userIP,"valeur supprimée : $idUE");

        header("location: ue?sucess=suppression effectuée avec succèss");

    }else{
        header("location: ue?erreur=$connexion->error");
      }
}

if(isset($_GET['supecue'])){


    $idUE=$_GET['supecue'];

    $sql="delete from ecue where id=$idUE";

    if($connexion->query($sql)){
        $userIP = $_SERVER['REMOTE_ADDR'];

        logUserAction($connexion,$_SESSION['id_user'],"suppression d'un ECUE",date("Y-m-d H:i:s"),$userIP,"valeur supprimée : $idUE");

        header("location: ecue?sucess=suppression effectuée avec succèss");

    }else{
        header("location: ecue?erreur=$connexion->error");
      }
}


if(isset($_GET['supclasse'])){


    $idUE=$_GET['supclasse'];

    $sql="delete from classe where id=$idUE";

    if($connexion->query($sql)){
        $userIP = $_SERVER['REMOTE_ADDR'];

        logUserAction($connexion,$_SESSION['id_user'],"suppression d'une classe",date("Y-m-d H:i:s"),$userIP,"valeur supprimée : $idUE");

        header("location: classe?sucess=suppression effectuée avec succèss");

    }else{
        header("location: classe?erreur=$connexion->error");
      }
}


if(isset($_GET['suprep'])){


    $id=$_GET['suprep'];

    $sql="delete from repartition where id=$id";

    if($connexion->query($sql)){
        $userIP = $_SERVER['REMOTE_ADDR'];

        logUserAction($connexion,$_SESSION['id_user'],"suppression d'une attribution",date("Y-m-d H:i:s"),$userIP,"valeur supprimée : $id");

        header("location: repartition?sucess=suppression effectuée avec succèss");

    }else{
        header("location: repartition?erreur=$connexion->error");
      }
}


if($_SERVER["REQUEST_METHOD"] == "GET")
{


      if(isset($_GET['code']) and isset($_GET['annee']) and isset($_GET['specialite'])){
        $type=typeEtablissement($_SESSION['lib_etab'],$connexion);


             $candidat = $_GET['code'];

             $annee = $_GET['annee'];

             $specialite = $_GET['specialite'];
             $q=getStatutPaimentCand($candidat,$annee,$connexion);
          

             if($q==1){
            

                        
              if($type =="institut" or $type=="ecole"){
               
 
               if(!get_statut_paiement_concours($candidat,$annee,$connexion)){
                       
                 header("location: ../scolarite/candidature?erreur=le candidat  $candidat n'a pas encore payé les frais de concours.");
                 exit;
               }else{


                 if(get_statut_admission_concours($candidat,$annee,$connexion)){
                  header("location: inscrire?code=$candidat&annee=$annee&specialite=$specialite");
                 }else{
                  header("location: ../scolarite/candidature?erreur=le candidat  $candidat n'est pas admis au concours.");
                  exit;
                    
                    
                 }
                       
               }
 
              }else{
                 
                       header("location: inscrire?code=$candidat&annee=$annee&specialite=$specialite");
              }


             }else{
              
              header("location: ../scolarite/candidature?erreur=le candidat  $candidat n'a pas encore payé les frais d'étude du dossier.");
                  exit;
             }


              
      }


}






?>
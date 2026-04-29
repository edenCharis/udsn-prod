<?php 

include '../php/connexion.php';
include '../php/lib.php';
session_start();

 

if ($_SERVER["REQUEST_METHOD"] == "POST"){

  if(isset($_POST['etudiant']) and isset($_POST['datep']) and isset($_POST['typefrais']))
  {
    $etudiant =$_POST['etudiant'];
    $type = clean_input($_POST['typefrais']);
    $date=$_POST['datep'];
    $montant =$_POST['montant'];
    $annee =$_POST['annee'];

    $etab =$_POST['etablissement'];
    $frais = generateCodeFrais();
    $user =$_SESSION['id_user'];


    $sql = "insert into frais(preinscription,codeFrais,libelle,montant,date,agent,annee,etab) values('$etudiant','$frais','$type','$montant','$date','$user','$annee','$etab')";

    if($connexion->query($sql)){


        $userIP = $_SERVER['REMOTE_ADDR'];

        logUserAction($connexion,$_SESSION['id_user'],"enregistrement des $type pour $etudiant",date("Y-m-d H:i:s"),$userIP,"valeur enregistrée : $etudiant+$frais+$montant+$date");
        $connexion->query("UPDATE candidat set statut_paiement=1 where code='$etudiant'");

        header("location: index?sucess=enregistrement effectué avec success");
  
    }else{
        header("location: index?erreur=$connexion->error ; $etudiant");
  
    }
  }
      
          
 }

if(isset($_POST['inscription'])){

    $etudiant =$_POST['inscription'];
    $type = clean_input($_POST['type']);
    $date=$_POST['datep'];
    $montant =$_POST['montant'];
    $annee =$_POST['annee'];

    $etab = clean_input($_POST['etablissement']);
    $frais = generateCodeFrais();
    $user =$_SESSION['id_user'];


    if(!verifierInscription2(getCandidatCodeByInscription($etudiant,$connexion),$annee,$connexion,$etab)){

      header("location: paiement?erreur=L'etudiant n'est pas inscrit dans cette établissement");
    }


    $sql = "insert into frais(inscription,codeFrais,libelle,montant,date,agent,annee,etab) values('$etudiant','$frais','$type','$montant','$date','$user','$annee','$etab')";

    if($connexion->query($sql)){


        $userIP = $_SERVER['REMOTE_ADDR'];

        logUserAction($connexion,$_SESSION['id_user'],"enregistrement des $type pour $etudiant",date("Y-m-d H:i:s"),$userIP,"valeur enregistrée : $etudiant+$frais+$montant+$date");
        $connexion->query("UPDATE inscription set statut_paiement=1 where candidat='$etudiant'");

        header("location: paiement?sucess=enregistrement effectué avec success");
  
    }else{
        header("location: paiement?erreur=$connexion->error ; $etudiant");
  
    }
  }


  if(isset($_POST['codeCandidat'])){

    $candidat =$_POST['codeCandidat'];

    $type = clean_input($_POST['type']);
    $_POST['etab'] = clean_input($_POST['etab']);
    $date=$_POST['datep'];
    $montant =$_POST['montant'];
    $annee =$_POST['annee'];

    $etab =getLibelleEtablissement($_POST['etab'],$connexion);
    $frais = generateCodeFrais();
    $user =$_SESSION['id_user'];
    $cop=0;

    if(isset($_POST["cop"]))
    {
      $cop=$_POST["cop"];
    }

    $ss="null";

    if($type == "frais étude du dossier"){
       $ss="statut_paiement";
    }else if($type == "frais de concours"){
      $ss="statut_paiement_concours";
    }

    if(!idCandidatValide($candidat,$connexion,$annee)){
              header("location: paiement2?erreur= le code ne correspond a aucun candidat");
              exit;
    }

   ($cop !== 0) ? $sql = "insert into frais(preinscription,codeFrais,libelle,montant,date,agent,annee,etab,cop) values('$candidat','$frais','$type','$montant','$date','$user','$annee','$etab',$cop)" : $sql = "insert into frais(preinscription,codeFrais,libelle,montant,date,agent,annee,etab) values('$candidat','$frais','$type','$montant','$date','$user','$annee','$etab')"  ;

    if($connexion->query($sql)){
        $userIP = $_SERVER['REMOTE_ADDR'];
        logUserAction($connexion,$_SESSION['id_user'],"enregistrement des $type pour $candidat",date("Y-m-d H:i:s"),$userIP,"valeur enregistrée : $candidat+$frais+$montant+$date");
        if($type !== "frais de concours" and $type !== "frais étude du dossier"){
          header("location: paiement2?sucess=enregistrement effectué avec success");

        }else{
            $ok="n";
           ($type == "frais de concours") ? $connexion->query("update candidat set statut_paiement_concours=1 where code='$candidat' and annee='$annee'") : $connexion->query("update candidat set statut_paiement=1 where code='$candidat' and annee='$annee'") ;
            header("location: paiement2?sucess=enregistrement effectué avec success");
         
        }
      
       
  
    }else{
        header("location: paiement2?erreur=$connexion->error ; $etudiant");
  
    }



  }


  if(isset($_POST['enseignant']) and isset($_POST['mp']) and isset($_POST['hef']) and isset($_POST['datep']) and isset($_POST['mois']) and isset($_POST['etablissement']))
  {

      $a = getNomPrenomEnseignant($_POST['enseignant'],$connexion);

      $etablissement =$_POST["etablissement"];
  

      if( $a === "aucun"){

        header("location: paiement1?erreur=Aucun enseignant ne correspond a ce code");
        exit;

      }else{
        if(!aFaitCoursCeMois($_POST['mois'],$_POST['enseignant'],$_POST['annee'],$connexion,$etab)){
          header("location: paiement1?erreur=Aucun cours effectué dans ce mois");
          exit;
         }

           if(!verifierInput($_POST['hef'],$_POST['annee'],$_POST['mois'],$_POST['enseignant'],$connexion,$etab)){
            header("location: paiement1?erreur=les heures effectuées sont erronées");
              exit;
           }

           if(!verifierAffectationEnseignant2($_POST["enseignant"],$_POST["annee"],$etablissement,$connexion)){
            header("location: paiement1?erreur=L'enseignant n'a pas été enregistré dans cet etablissement");
              exit;
           }

              $tableau = getThByEns($_POST['enseignant'],$connexion,$etablissement);

              $montant =$tableau * $_POST['hef'];


              if( $montant < $_POST['mp']){
               
                header("location: paiement1?erreur=le montant à payé est erronée ");
                exit;
              }
                $reste =$montant - $_POST['montant'];
              


              

              $code = generateCodeFrais();
              $en =clean_input($_POST['enseignant']);

              $sql ="insert into paiement values(null,'$code','$en',".$_POST['mois'].",'".$_POST['datep']."','".$_POST['annee']."',".$_POST['hef'].",$montant,".$_POST['mp'].",$reste,'".$etablissement."')";

              if($connexion->query($sql)){


                $userIP = $_SERVER['REMOTE_ADDR'];
        
                logUserAction($connexion,$_SESSION['id_user'],"enregistrement d'un paiement pour $ens",date("Y-m-d H:i:s"),$userIP,"valeur enregistrée : $ens+$montant+".$_POST['mois']);
        
                header("location: paiement1?sucess=enregistrement effectué avec success");
          
            }else{
                header("location: paiement1?erreur=$connexion->error");
          
            }


                

      }


  }
      
          
        

               



        





       


?>
<?php

include '../php/connexion.php';
include '../php/lib.php';
session_start();

if($_SERVER["REQUEST_METHOD"] == "POST")
{

if(isset($_POST['codeanonyme']) and isset($_POST['ecue']) and isset($_POST['annee']) and isset($_POST['note'])){

    $code=$_POST['codeanonyme'];
    $ecue = clean_input($_POST['ecue']);
    $annee = $_POST['annee'];
    $etab =$_SESSION['etablissement'];
    $note =$_POST['note'];
    $examen =$_POST['examen'];
 
    
    
    if(!verifierCodeAnonyme($code,$ecue,$annee,$examen,$connexion)){

        header("location: notation1?erreur=Le code anonyme est erroné");
        exit;
    }



    $sql="INSERT INTO ligne1 (anonymat,code_ecue,note,type_examen,annee,etab) VALUES
    ('$code','$ecue','$note','$examen','$annee','$etab')";
   



    

  if($connexion->query($sql)){
  $userIP = $_SERVER['REMOTE_ADDR'];

  logUserAction($connexion,$_SESSION['id_user'],"enregistrement d'une note d'examen sur une copie anonymée  ",date("Y-m-d H:i:s"),$userIP,"valeur enregistrée :$code+$ecue+$annee+$examen");




  $etudiant =getIdInscriptionFromAnonymat($code,$annee,$connexion);

  $semestre=getSemestreByAnonymat($code,$connexion);
  $classe=getClasseByAnonymat($code,$connexion);
  $id_notation = verifierInscriptionNotation($connexion,$etudiant,$ecue,$semestre,$classe,$annee);



  if($id_notation == null){

    $note1=$note;

    if($examen === "Session Ordinaire"){
      $sql1="INSERT INTO notation (inscription,classe,ecue,annee,etab,semestre,moyEx) VALUES 
      ($etudiant,'$classe','$ecue','$annee','$etab','$semestre',$note1)";
    }else if($examen === "Session de Rappel"){
      $sql1="INSERT INTO notation (inscription,classe,ecue,annee,etab,semestre,session_rappel) VALUES 
      ($etudiant,'$classe','$ecue','$annee','$etab','$semestre',$note1)";
    }
  

  if($connexion->query($sql1)){
  $userIP = $_SERVER['REMOTE_ADDR'];
  logUserAction($connexion,$_SESSION['id_user'],"Mis à jour notation",date("Y-m-d H:i:s"),$userIP,"valeur enregistrée :$etudiant+$classe+$annee");
   }else{
          
    echo header("location: notation1?erreur=$connexion->error");
}
  }else{

      $note1=$note;

      if($examen === "Session Ordinaire")
      {
        $sql2 ="UPDATE notation set moyEx=$note1 where id=$id_notation";

      }else if($examen === "Session de Rappel"){
        $sql2 ="UPDATE notation set session_rappel=$note1 where id=$id_notation";
      }
         
         
         if($connexion->query($sql2)){
          $userIP = $_SERVER['REMOTE_ADDR'];
          logUserAction($connexion,$_SESSION['id_user'],"Mis a jour notation ",date("Y-m-d H:i:s"),$userIP,"valeur enregistrée :$etudiant+$classe+$annee");
        }else{
          
                echo header("location: notation1?erreur=$connexion->error");
        }
  }

  header("location: notation1?sucess=Enregistrement effectuée avec succèss $etudiant $id_notation");
  
}else{

  header("location: notation1?erreur=$connexion->error");
  
}
  
}


if(isset($_POST['etudiant']) and isset($_POST['classe']) and isset($_POST['note']) and isset($_POST['semestre'])and isset($_POST['ecue']) and isset($_POST['examen'])){


    $etudiant = $_POST['etudiant'];
    $ecue = clean_input($_POST['ecue']);
    $classe = clean_input($_POST['classe']);
    $annee = $_POST['annee'];
    $etab =$_SESSION['etablissement'];
    $specialite = obtenirSpecialiteClasse($classe,$connexion);
    $note=$_POST['note'];
    $examen=$_POST['examen'];
    $semestre=$_POST['semestre'];
    
    
    
    
    if(eleveDansClasse(getCandidatCodeByInscription($etudiant,$connexion),$classe,$annee,$connexion)){
      
   if( !verifierEcueClasseSemestre($ecue, $classe,$semestre, $etab, $connexion)){
        
          header("location: notation2?erreur=L'ecue $ecue n'est pas enseignée au $semestre dans la classe $classe");
          exit;
        
    }

    $sql="INSERT INTO ligne2(etudiant,classe,code_ecue,annee,note,semestre,specialite,etab,type)  VALUES 
    ($etudiant,'$classe','$ecue','$annee',$note,'$semestre','$specialite','$etab','$examen')";

    $id_notation = verifierInscriptionNotation($connexion,$etudiant,$ecue,$semestre,$classe,$annee);
    $a = false;
    
    if($connexion->query($sql)){
      $userIP = $_SERVER['REMOTE_ADDR'];
      logUserAction($connexion,$_SESSION['id_user'],"enregistrement d'une note de devoir",date("Y-m-d H:i:s"),$userIP,"valeur enregistrée :$etudiant+$classe+$annee");
      $a =true;
    }
    if($id_notation == null){

      $note1=moyenneGeneraleDevoirs($connexion,$etudiant,$ecue,$semestre,$annee);
      $sql="INSERT INTO notation (inscription,classe,code_ecue,annee,etab,semestre,moyDev) VALUES 
      ($etudiant,'$classe','$ecue','$annee','$etab','$semestre',$note1)";

    if($connexion->query($sql)){
    $userIP = $_SERVER['REMOTE_ADDR'];
    logUserAction($connexion,$_SESSION['id_user'],"Mis à jour notation",date("Y-m-d H:i:s"),$userIP,"valeur enregistrée :$etudiant+$classe+$annee");
  }
    }else{

      $note1=moyenneGeneraleDevoirs($connexion,$etudiant,$ecue,$semestre,$annee);
            $sql1 ="UPDATE notation set moyDev=$note1 where id=$id_notation";
           
           if($connexion->query($sql1)){
            $userIP = $_SERVER['REMOTE_ADDR'];
            logUserAction($connexion,$_SESSION['id_user'],"Mis a jour notation ",date("Y-m-d H:i:s"),$userIP,"valeur enregistrée :$etudiant+$classe+$annee");
          }else{
            
                  echo header("location: notation2?erreur=$connexion->error");
          }
    }
  if($a){
  header("location: notation2?sucess=Enregistrement effectuée avec succèss ");
  
}else{

 echo header("location: notation2?erreur=$connexion->error");
  
}}else{
  echo header("location: notation2?erreur=l'etudiant choisie n'est pas inscrit dans cette classe.");
}


  }




}
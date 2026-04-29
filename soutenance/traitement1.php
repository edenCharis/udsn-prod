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

            $sql ="update eval set note=$note where element='$element' and soutenance='$soutenance'";

            if($connexion->query($sql)){

              $userIP = $_SERVER['REMOTE_ADDR'];

              logUserAction($connexion,$_SESSION['id_user'],"modification d'une note d'element d'appreciation ",date("Y-m-d H:i:s"),$userIP,"valeur enregistrée : $soutenance+$element+$note");
              header("location: evaluation?sucess=modification  effectué avec success&soutenance=$soutenance");

            }else{
              header("location: evaluation?erreur=$connexion->error&soutenance=$soutenance");
  
            }
       
  }


        

}



?>




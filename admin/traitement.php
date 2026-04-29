<?php
include '../php/connexion.php';
include '../php/lib.php';
session_start();



if($_SERVER['REQUEST_METHOD'] === 'POST'){




        $univ =strtolower(str_replace("'","+",$_POST["universite"]));
        $email=$_POST["email"];
        $tel=$_POST["telephone"]; 
        $code =$_POST["code"]; 
        $fax=$_POST["fax"];
        $long=$_POST["long"];
        $lat=$_POST["lat"];
        $ville=$_POST["ville"];
      $dep=$_POST["dep"];
        $id=$_POST["id_univ"];

        if (isset($_FILES["logo"]) && $_FILES["logo"]["error"] === UPLOAD_ERR_OK){
        if ($_FILES["logo"]["error"] === UPLOAD_ERR_OK) {
            // Récupérez des informations sur le fichier
            $nomFichier = $_FILES["logo"]['name'];
            $typeFichier = $_FILES["logo"]['type'];
            $cheminTemporaire = $_FILES["logo"]['tmp_name'];
  
            // Vous pouvez maintenant traiter le fichier, par exemple, le déplacer vers un répertoire de stockage
             $nouveauChemin = '../administateur/logo/' . $nomFichier;
  
            if (move_uploaded_file($cheminTemporaire, $nouveauChemin)) {


                    $sql ="update  univ set libelle_univ='$univ',code='$code',logo='$nouveauChemin',email_contact='$email',tel_contact='$tel',ville='$ville',dep='$dep',fax='$fax',long_univ='$long',lat_univ='$lat' where id=$id";

            }else{
                $sql ="update  univ set libelle_univ='$univ',code='$code',email_contact='$email',tel_contact='$tel',ville='$ville',dep='$dep',fax='$fax',long_univ='$long',lat_univ='$lat' where id=$id";

                    if($connexion->query($sql)){

                        $userIP = $_SERVER['REMOTE_ADDR'];

                        logUserAction($connexion,$_SESSION['id_user'],"modification des valeur de l'universite",date("Y-m-d H:i:s"),$userIP,"valeur enregistrée : $univ+$code+$email+$tel+$fax+$ville");
                
                        header("location: parametrage?sucess=Enregistrement effectué avec succèss");
                
                    }else{
                          header("location: parametrage?erreur=$connexion->error");
                      }




            }}else{

                header("location: parametrage?erreur=Erreur survenu lors de l'enregistrement de l'image; Ressayez encore.");
            }


  

}else{
    $sql ="update  univ set libelle_univ='$univ',code='$code',email_contact='$email',tel_contact='$tel',ville='$ville',dep='$dep',fax='$fax',long_univ='$long',lat_univ='$lat' where id=$id";

    if($connexion->query($sql)){

        $userIP = $_SERVER['REMOTE_ADDR'];

        logUserAction($connexion,$_SESSION['id_user'],"modification des valeur de l'universite",date("Y-m-d H:i:s"),$userIP,"valeur enregistrée : $univ+$code+$email+$tel+$fax+$ville");

        header("location: parametrage?sucess=Enregistrement effectué avec succèss");

    }else{
          header("location: parametrage?erreur=$connexion->error");
      }
}



}




    







  ?>
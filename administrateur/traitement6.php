<?php
include '../php/connexion.php';
include '../php/lib.php';
session_start();

if($_SERVER['REQUEST_METHOD'] === 'POST'){


    if (isset($_FILES["logo"]) && $_FILES["logo"]["error"] === UPLOAD_ERR_OK){
    

        $univ =strtolower(str_replace("'","+",$_POST['universite']));
        $email=$_POST['email'];
        $tel=$_POST['telephone']; 
        $code =$_POST['code']; 
        $fax=$_POST['fax'];
        $long=$_POST['long'];
        $lat=$_POST['lat'];
        $ville=$_POST['ville'];
        $dep=$_POST['departement'];


        if ($_FILES['logo']['error'] === UPLOAD_ERR_OK) {
            // Récupérez des informations sur le fichier
            $nomFichier = $_FILES['logo']['name'];
            $typeFichier = $_FILES['logo']['type'];
            $cheminTemporaire = $_FILES['logo']['tmp_name'];
  
            // Vous pouvez maintenant traiter le fichier, par exemple, le déplacer vers un répertoire de stockage
             $nouveauChemin = 'logo/' . $nomFichier;
  
            if (move_uploaded_file($cheminTemporaire, $nouveauChemin)) {


                    $sql ="INSERT INTO univ values(null,'$univ','$code','$nouveauChemin','$email','$tel','$ville','$dep','$fax','$long','$lat')";


                    if($connexion->query($sql)){

                        $userIP = $_SERVER['REMOTE_ADDR'];

                        logUserAction($connexion,$_SESSION['id_user'],"enregistrement d'une universite",date("Y-m-d H:i:s"),$userIP,"valeur enregistrée : $univ+$code+$email+$tel+$fax+$ville");
                
                        header("location: universite?sucess=Enregistrement effectué avec succèss");
                
                    }else{
                          header("location: universite?erreur=$connexion->error");
                      }




            }else{

                header("location: universite?erreur=Erreur survenu lors de l'enregistrement de l'image; Ressayez encore.");
            }


  

}else{
    header("location: universite?erreur=Erreur survenu de l'importation de l'image; Ressayez encore.");
}

}

}




    







  ?>
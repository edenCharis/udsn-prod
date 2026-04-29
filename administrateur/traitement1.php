<?php
include '../php/connexion.php';
include '../php/lib.php';
session_start();

if($_SERVER['REQUEST_METHOD'] === 'POST'){


    if (isset($_FILES["img"]) && $_FILES["img"]["error"] === UPLOAD_ERR_OK){
    

        $nom =str_replace("'","+",$_POST['nom']);
        $login=$_POST['login'];
        $mdp=$_POST['mdp']; // a vérifier si c'est 8 caractères

        $role=str_replace("'","+",$_POST['role']);

        $etab=str_replace("'","+",$_POST['etab']);



        if ($_FILES['img']['error'] === UPLOAD_ERR_OK) {
            // Récupérez des informations sur le fichier
            $nomFichier = $_FILES['img']['name'];
            $typeFichier = $_FILES['img']['type'];
            $cheminTemporaire = $_FILES['img']['tmp_name'];
  
            // Vous pouvez maintenant traiter le fichier, par exemple, le déplacer vers un répertoire de stockage
             $nouveauChemin = 'photos/' . $nomFichier;
  
            if (move_uploaded_file($cheminTemporaire, $nouveauChemin)) {


                    $sql ="INSERT INTO utilisateur(nom,login,mdp,role,univ,img,statut) values('$nom','$login','$mdp','$role','$etab','$nouveauChemin','N')";


                    if($connexion->query($sql)){

                        $userIP = $_SERVER['REMOTE_ADDR'];

                        logUserAction($connexion,$_SESSION['id_user'],"enregistrement d'un utilisateur",date("Y-m-d H:i:s"),$userIP,"valeur enregistrée : $nom+$login+$mdp+$role+$etab");
                
                        header("location: compte?sucess=Enregistrement effectué avec succèss");
                
                    }else{
                          header("location: compte?erreur=$connexion->error");
                      }




            }else{

                header("location: compte?erreur=Erreur survenu lors de l'enregistrement de l'image; Ressayez encore.");
            }


  

}else{
    header("location: compte?erreur=Erreur survenu de l'importation de l'image; Ressayez encore.");
}

}

}




    







  ?>
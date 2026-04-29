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
        $annee=$_POST['annee'];



        if ($_FILES['img']['error'] === UPLOAD_ERR_OK) {
            // Récupérez des informations sur le fichier
            $nomFichier = $_FILES['img']['name'];
            $typeFichier = $_FILES['img']['type'];
            $cheminTemporaire = $_FILES['img']['tmp_name'];
  
            // Vous pouvez maintenant traiter le fichier, par exemple, le déplacer vers un répertoire de stockage
             $nouveauChemin = 'photos/' . $nomFichier;
  
            if (move_uploaded_file($cheminTemporaire, $nouveauChemin)) {


                    $sql ="INSERT INTO utilisateur(nom,login,mdp,role,univ,etab,img,statut,annee) values('$nom','$login','$mdp','$role','".$_SESSION["univ"]."','$etab','$nouveauChemin','N','$annee')";


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

}else{
    header("location: compte?erreur=Veuillez importez l'image de l'utilisateur.");
}

}


if(isset($_GET['statut']) and isset($_GET['id'])){

    $id= $_GET['id'];

    $sql ="UPDATE utilisateur set statut='".$_GET['statut']."' where id=$id";

    if($connexion->query($sql)){

        $userIP = $_SERVER['REMOTE_ADDR'];

        logUserAction($connexion,$_SESSION['id_user'],"Mise à jour statut d'un utilisateur",date("Y-m-d H:i:s"),$userIP," utilisateur : $id; valeur mise à jour : '".$_GET['statut']);

        header("location: compte?sucess=opération effectuée avec succèss");


    }else{
        header("location: compte?erreur=$connexion->error");
    }


}

if(isset($_GET['supcompte'])){
    $id=$_GET['supcompte'];

    $sql="delete from utilisateur where id=$id";

    if($connexion->query($sql)){
        $userIP = $_SERVER['REMOTE_ADDR'];

        logUserAction($connexion,$_SESSION['id_user'],"suppression d'un utilisateur",date("Y-m-d H:i:s"),$userIP,"valeur supprimée : $id");

        header("location: compte?sucess=suppression effectuée avec succèss");

    }else{
        header("location: compte?erreur=$connexion->error");
    }
}




    







  ?>
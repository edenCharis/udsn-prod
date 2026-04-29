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

        $etab=$_SESSION['etablissement'];
        $annee=$_POST['annee'];
        if ($_FILES['img']['error'] === UPLOAD_ERR_OK) {
            // Récupérez des informations sur le fichier
            $nomFichier = $_FILES['img']['name'];
            $typeFichier = $_FILES['img']['type'];
            $cheminTemporaire = $_FILES['img']['tmp_name'];
  
            // Vous pouvez maintenant traiter le fichier, par exemple, le déplacer vers un répertoire de stockage
             $nouveauChemin = 'photos/' . $nomFichier;
  
            if (move_uploaded_file($cheminTemporaire, $nouveauChemin)) {

                $classe = str_replace("'","+",$_POST['classe']);
                $ecue = str_replace("'","+",$_POST['ecue']);
                $parcours = str_replace("'","+",$_POST['parcours']);
                $semestre = str_replace("'","+",$_POST['semestre']);
                $examen = str_replace("'","+",$_POST['examen']);
                $debut =$_POST["debut"];

                $fin=$_POST["fin"];

                if( $classe !== "" and $ecue == "")
                {
                  
                    $sql ="INSERT INTO utilisateur(nom,login,mdp,role,univ,etab,img,statut,annee,classe) values('$nom','$login','$mdp','$role','".$_SESSION["univ"]."','$etab','$nouveauChemin','N','$annee','$classe')";
                   

                } else if($ecue !== "" and $classe ==""){
                
                     $sql ="INSERT INTO utilisateur(nom,login,mdp,role,univ,etab,img,statut,annee,ecue) values('$nom','$login','$mdp','$role','".$_SESSION["univ"]."','$etab','$nouveauChemin','N','$annee','$ecue')";

                }else if($classe !== "" and $ecue !== "" ){
                    $sql ="INSERT INTO utilisateur(nom,login,mdp,role,univ,etab,img,statut,annee,ecue,classe) values('$nom','$login','$mdp','$role','".$_SESSION["univ"]."','$etab','$nouveauChemin','N','$annee','$ecue','$classe')";

                   

                }else if($parcours !== "" and $semestre !== "" and $examen !== "" and $role="pvd" and $debut !== "" and $fin !== ""){
                    $sql ="INSERT INTO utilisateur(nom,login,mdp,role,univ,etab,img,statut,annee,parcours,semestre,examen,date_debut,date_fin) values('$nom','$login','$mdp','$role','".$_SESSION["univ"]."','$etab','$nouveauChemin','N','$annee','$parcours','$semestre','$examen','$debut','$fin')";


                }else{
                    $sql ="INSERT INTO utilisateur(nom,login,mdp,role,univ,etab,img,statut,annee) values('$nom','$login','$mdp','$role','".$_SESSION["univ"]."','$etab','$nouveauChemin','N','$annee')";

                }


                   

                    if($connexion->query($sql)){

                        $userIP = $_SERVER['REMOTE_ADDR'];

                        logUserAction($connexion,$_SESSION['id_user'],"enregistrement d'un utilisateur",date("Y-m-d H:i:s"),$userIP,"valeur enregistrée : $nom+$login+$mdp+$role+$etab");
                
                        header("location: anonymat?sucess=Enregistrement effectué avec succèss");
                
                    }else{
                          header("location: anonymat?erreur=$connexion->error");
                      }




            }else{

                header("location: anonymat?erreur=Erreur survenu lors de l'enregistrement de l'image; Ressayez encore.");
            }


  

}else{
    header("location: anonymat?erreur=Erreur survenu de l'importation de l'image; Ressayez encore.");
}

}else{
    header("location: anonymat?erreur=Veuillez importez l'image de l'utilisateur.");
}

}


if(isset($_GET['supcompte'])){
    $id=$_GET['supcompte'];

    $sql="delete from utilisateur where id=$id";

    if($connexion->query($sql)){
        $userIP = $_SERVER['REMOTE_ADDR'];

        logUserAction($connexion,$_SESSION['id_user'],"suppression d'un utilisateur",date("Y-m-d H:i:s"),$userIP,"valeur supprimée : $id");

        header("location: anonymat?sucess=suppression effectuée avec succèss");

    }else{
        header("location: anonymat?erreur=$connexion->error");
    }
}

?>
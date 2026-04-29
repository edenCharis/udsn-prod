<?php 
include '../php/connexion.php';
include '../php/lib.php';
session_start();

 

// Fonction pour nettoyer les données du formulaire


// Traitement du formulaire s'il est soumis
if ($_SERVER["REQUEST_METHOD"] == "POST"){

if( isset($_POST['doc']) or isset($_SESSION['code']) or isset($_SESSION['annee'])){


    if(!idCandidatExiste1($_SESSION['id_user'],$connexion)){
        header("location: document?erreur=Veuillez d'abord remplir le formulaire de preisncription");
        exit;

    }

     
    if ($_FILES['img']['error'] === UPLOAD_ERR_OK) {
        // Récupérez des informations sur le fichier
        $nomFichier = str_replace("'","+",$_FILES['img']['name']);
        $typeFichier = $_FILES['img']['type'];
        $cheminTemporaire = $_FILES['img']['tmp_name'];

        $doc=clean_input($_POST['doc']);

        // Vous pouvez maintenant traiter le fichier, par exemple, le déplacer vers un répertoire de stockage
         $nouveauChemin = 'doc/' . $nomFichier;

        if (move_uploaded_file($cheminTemporaire, $nouveauChemin)) {

            $code =getCodeCandidat($_SESSION['id_user'],$connexion);
            $annee=getAnneeCandidat($_SESSION['id_user'],$connexion);

            $sql="insert into  validation(candidat,document,path,annee) values('".$code."','".$doc."','".$nouveauChemin."','".$annee."')";


            if($connexion->query($sql)){
                $userIP = $_SERVER['REMOTE_ADDR'];

                logUserAction($connexion,$_SESSION['id_user'],"attachement d'un fichier pour la preinscription",date("Y-m-d H:i:s"),$userIP,"valeur enregistrée : $nouveauChemin+".$doc);
        

                header("location: document?success=Votre fichier a été importé avec success");
            }else{

                header("location: document?erreur=$connexion->error");

            }
            
        }else{
               header("location: document?erreur=Un problème est survenu lors de l'importation du fichier");

        }
}else{
    header("location: document?erreur=Une erreur est survenu lors de l'importation du fichier");

}
}
}
?>

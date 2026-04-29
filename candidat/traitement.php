<?php 

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require '../assets/phpmailer/src/Exception.php';
require '../assets/phpmailer/src/PHPMailer.php';
require '../assets/phpmailer/src/SMTP.php';

include '../php/connexion.php';
include '../php/lib.php';
session_start();

 

// Fonction pour nettoyer les données du formulaire



// Traitement du formulaire s'il est soumis
if ($_SERVER["REQUEST_METHOD"] == "POST") {



    // Nettoyer les données du formulaire
    $nom = clean_input($_POST["nom"]);
    $prenom = clean_input($_POST["prenom"]);
    $email = clean_input($_POST["email"]);
    $dateNaissance = $_POST["datenaissance"];
    $lieuNaissance = clean_input($_POST["lieunaissance"]);
    $sexe = clean_input($_POST["sexe"]);
    $bac = clean_input($_POST["bac"]);
    $anneeBac = clean_input($_POST["anneebac"]);
    $mention = clean_input($_POST["mention"]);
    $cycle = clean_input($_POST["cycle"]);
    $etablissement = getLibelleEtablissement(clean_input($_POST["etablissement"]),$connexion);
    $specialite = clean_input($_POST["specialite"]);
    $annee = clean_input($_POST["annee"]);
    $tel = clean_input($_POST["tel"]);
    $moybac=$_POST['moybac'];


    if(idCandidatExiste($_SESSION['id_user'],$connexion,$_SESSION['annee'])){
        header("location: formulaire?erreur=Vous avez deja une candidature en cours pour cette année académique");
        exit;

    }

    // Validation de l'email
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        die("L'adresse email n'est pas valide. Veuillez retourner et corriger le formulaire.");
        header("location: formulaire?erreur=L'adresse email n'est pas valide. Veuillez retourner et corriger le formulaire.");
        exit;
    }




    if( !isSpecialiteInEtablissement($connexion,$specialite,$etablissement)){
        header("location: formulaire?erreur=la specialité choisie ne correspond pas à l'etablissement.");
        exit;
    }

    // Autres validations peuvent être ajoutées selon les besoins

    // Traitement de l'insertion dans la base de données
    if ($_FILES['photo']['error'] === UPLOAD_ERR_OK) {
        // Récupérez des informations sur le fichier
        $nomFichier = $_FILES['photo']['name'];
        $typeFichier = $_FILES['photo']['type'];
        $cheminTemporaire = $_FILES['photo']['tmp_name'];

        // Vous pouvez maintenant traiter le fichier, par exemple, le déplacer vers un répertoire de stockage
         $nouveauChemin = 'photos/' . $nomFichier;


         $finfo = finfo_open(FILEINFO_MIME_TYPE);

         // Récupère le type MIME du fichier
         $fileType = finfo_file($finfo, $_FILES['photo']['tmp_name']);
         
         // Ferme l'objet finfo
         finfo_close($finfo);

         $allowedImageTypes = ['image/jpeg', 'image/png', 'image/gif'];
      

        if (!in_array($fileType, $allowedImageTypes)) {
            header("location: formulaire?erreur=le fichier choisie n'est pas au bon format, veuillez choisir une image.");
          exit;
         } else {

    if (move_uploaded_file($cheminTemporaire, $nouveauChemin)) {
    $code=generateUniqueCode();
    $sql = "INSERT INTO candidat (code,nom, prenom, email, date_nais, lieu_nais, sexe, bac,moyenneBac, anneebac, mention, cycle, etab, specialite, annee,tel,statut,img,utilisateur,date_cand,statut_paiement,statut_paiement_concours,univ,etat) VALUES (
                            '$code','$nom', '$prenom', '$email', '$dateNaissance', '$lieuNaissance', '$sexe', '$bac',$moybac, '$anneeBac', '$mention', '$cycle', '$etablissement', '$specialite', '$annee','$tel','en cours','$nouveauChemin',".$_SESSION['id_user'].",'".date("Y-m-d")."',0,0,'UDSN',0)";

    if ($connexion->query($sql) === TRUE) {
        $userIP = $_SERVER['REMOTE_ADDR'];
        

        logUserAction($connexion,$_SESSION['id_user'],"enregistrement d'un candidat",date("Y-m-d H:i:s"),$userIP,"valeur enregistrée : $code");
        
        $mail = new PHPMailer(true);

try {
    // Paramètres SMTP
    $mail->SMTPDebug = 2;
    $mail->isSMTP();
    $mail->Host       = 'smtp.hostinger.com';
    $mail->SMTPAuth   = true;
    $mail->Username   = 'udsn@udsn.pro';
    $mail->Password   = '3?On~o$JyJws';
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
    $mail->Port       = 587;
      $mail->CharSet = 'UTF-8';

    // Destinataires
    $mail->setFrom('udsn@udsn.pro', 'Université Denis Sassou-N\'Guesso');
    $mail->addAddress($email, $nom." ".$prenom);

    // Contenu de l'e-mail
    $mail->isHTML(true);
    $mail->Subject = 'Confirmation de Pré-inscription';
    
     $mailContent = file_get_contents('template_mail.html');
    $mailContent = str_replace('[Nom de l\'utilisateur]', $nom."  ".$prenom, $mailContent); 
    
   $mail->Body    = $mailContent;
    $mail->send();
     header("location: index?sucess='Votre pré-inscription à été effectue avec sucess.'&candidat=$code");
    
} catch (Exception $e) {
    echo "Message could not be sent.";
     header("location: index?error='Votre pré-inscription à été effectue avec sucess.'&candidat=$code, email not sent");
}










       
    } else {
        header("location: formulaire?erreur=$connexion->error");
    }

    // Fermer la connexion à la base de données
}else{
    header("location: formulaire?erreur=Erreur avec l'importation de l'image.");


}
}}else{
    header("location: formulaire?erreur=Une erreur est survenue lors de l'importation de l'image.");

}


}

?>




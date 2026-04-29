<?php 



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
    $etablissement = clean_input($_POST["etablissement"]);
    $specialite = clean_input($_POST["specialite"]);
    $annee = clean_input($_POST["annee"]);
    $tel = clean_input($_POST["tel"]);
     $classe = clean_input($_POST["classe"]);
    $moybac=$_POST['moybac'];





    if( !isSpecialiteInEtablissement($connexion,$specialite,getLibelleEtablissement($etablissement,$connexion))){
        header("location: enregistrement?erreur=la specialité choisie ne correspond pas à l'etablissement.");
        exit;
    }





if($_FILES["photo"]["error"] === UPLOAD_ERR_OK){
    
     // Traitement de l'insertion dans la base de données
    if ($_FILES['photo']['error'] === UPLOAD_ERR_OK) {
        // Récupérez des informations sur le fichier
        $nomFichier = $_FILES['photo']['name'];
        $typeFichier = $_FILES['photo']['type'];
        $cheminTemporaire = $_FILES['photo']['tmp_name'];

        // Vous pouvez maintenant traiter le fichier, par exemple, le déplacer vers un répertoire de stockage
         $nouveauChemin = 'photo_etudiant/' . $nomFichier;


         $finfo = finfo_open(FILEINFO_MIME_TYPE);

         // Récupère le type MIME du fichier
         $fileType = finfo_file($finfo, $_FILES['photo']['tmp_name']);
         
         // Ferme l'objet finfo
         finfo_close($finfo);

         $allowedImageTypes = ['image/jpeg', 'image/png', 'image/gif'];
      

        if (!in_array($fileType, $allowedImageTypes)) {
            header("location: enregistrement?erreur=le fichier choisie n'est pas au bon format, veuillez choisir une image.");
          exit;
         } else {

    if (move_uploaded_file($cheminTemporaire, $nouveauChemin)) {
    $code=generateUniqueCode();
    $etab=getLibelleEtablissement($etablissement,$connexion);
    $sql = "INSERT INTO candidat (code,nom, prenom, email, date_nais, lieu_nais, sexe, bac,moyenneBac, anneebac, mention, cycle, etab, specialite, annee,tel,statut,img,date_cand,statut_paiement,statut_paiement_concours,univ,etat) VALUES (
                            '$code','$nom', '$prenom', '$email', '$dateNaissance', '$lieuNaissance', '$sexe', '$bac',$moybac, '$anneeBac', '$mention', '$cycle', '$etab', '$specialite', '$annee','$tel','en cours','$nouveauChemin','".date("Y-m-d")."',0,0,'UDSN',0)";

    if ($connexion->query($sql) === TRUE) {
        $userIP = $_SERVER['REMOTE_ADDR'];
        
        
        $sql1="insert into inscription(candidat,classe,annee,etab,statut_paiement) values ('".$code."','".$classe."','".$annee."','".$etablissement."',1)";
        
     if(   $connexion->query($sql1)) {
          logUserAction($connexion,$_SESSION['id_user'],"enregistrement d'un étudiant",date("Y-m-d H:i:s"),$userIP,"valeur enregistrée : $code+$annee");
          header("location: enregistrement?sucess=Opération a été effectue avec sucess");
    
         
     }else{
            header("location: enregistrement?erreur=$connexion->error");
         
     }

        
    /*    $mail = new PHPMailer(true);

try {
    // Paramètres SMTP
    $mail->SMTPDebug = 2;
    $mail->isSMTP();
    $mail->Host       = 'smtp.hostinger.com';
    $mail->SMTPAuth   = true;
    $mail->Username   = 'rochngoubou@cetup.pro';
    $mail->Password   = 'Rochngoubou@2024';
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
    $mail->Port       = 587;
      $mail->CharSet = 'UTF-8';

    // Destinataires
    $mail->setFrom('rochngoubou@cetup.pro', 'Université Denis Sassou-N\'Guesso');
    $mail->addAddress($email, $nom." ".$prenom);

    // Contenu de l'e-mail
    $mail->isHTML(true);
    $mail->Subject = 'Confirmation de d'inscription';
    
    $mailContent = file_get_contents('template_mail.html');
    $mailContent = str_replace('[Nom de l\'utilisateur]', $nom."  ".$prenom, $mailContent); // Remplacez les placeholders si nécessaire
    
    $mail->Body    = $mailContent;

    $mail->send();
     header("location: index?sucess='Votre pré-inscription à été effectue avec sucess.'&candidat=$code");
    
} catch (Exception $e) {
    echo "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
     header("location: index?error='Votre pré-inscription à été effectue avec sucess.'&candidat=$code, email not sent");
}
*/









       
    } else {
        header("location: enregistrement?erreur=$connexion->error");
    }

    // Fermer la connexion à la base de données
}else{
    header("location: enregistrement?erreur=Erreur avec l'importation de l'image.");


}
}}else{
    header("location: enregistrement?erreur=Une erreur est survenue lors de l'importation de l'image.");

}
    
    
}else{
    
    $code=generateUniqueCode();
    $etab=getLibelleEtablissement($etablissement,$connexion);
    $sql = "INSERT INTO candidat (code,nom, prenom, email, date_nais, lieu_nais, sexe, bac,moyenneBac, anneebac, mention, cycle, etab, specialite, annee,tel,statut,date_cand,statut_paiement,statut_paiement_concours,univ,etat) VALUES (
                            '$code','$nom', '$prenom', '$email', '$dateNaissance', '$lieuNaissance', '$sexe', '$bac',$moybac, '$anneeBac', '$mention', '$cycle', '$etab', '$specialite', '$annee','$tel','en cours','".date("Y-m-d")."',0,0,'UDSN',0)";

    if ($connexion->query($sql) === TRUE) {
        $userIP = $_SERVER['REMOTE_ADDR'];
        
        
        $sql1="insert into inscription(candidat,classe,annee,etab,statut_paiement) values ('".$code."','".$classe."','".$annee."','".$etablissement."',1)";
        
     if(   $connexion->query($sql1)) {
          logUserAction($connexion,$_SESSION['id_user'],"enregistrement d'un étudiant",date("Y-m-d H:i:s"),$userIP,"valeur enregistrée : $code+$annee");
          header("location: enregistrement?sucess=Opération a été effectue avec sucess");
    
         
     }else{
            header("location: enregistrement?erreur=$connexion->error");
         
     }

    
    
    
    
}
   

}






}






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
   
    $sexe = clean_input($_POST["sexe"]);
    $matricule = clean_input($_POST["matricule"]);
    $classe = clean_input($_POST["classe"]);
    $type_demande = clean_input($_POST["diplome"]);
    $parcours = clean_input($_POST["parcours"]);
    $etablissement = clean_input($_POST["etablissement"]);
    $etab = getLibelleEtablissement($etablissement,$connexion);
    $specialite = clean_input($_POST["specialite"]);
    $annee = clean_input($_POST["annee"]);
    $tel = clean_input($_POST["tel"]);
    $sexe = clean_input($_POST["sexe"]);

if( getIdInscription($matricule,$annee,$connexion) !== null)
{
    $etudiant = getIdInscription($matricule,$annee,$connexion);

}else{

    header("location: demande?erreur=Erreur ! Votre matricule est erroné ");
    exit;

}
     
      $utilisateur = $_SESSION["id_user"];


    if(!verifierIdentityEtudiant($connexion,$matricule,$nom,$prenom,$etab)){
        header("location: demande?erreur=Erreur ! Votre matricule est erroné ");
        exit;

    }


    
    if(!verifierInscription2($matricule,$annee,$connexion,$etablissement)){
        header("location: demande?erreur=Erreur ! Votre classe ou année universitaire est erronée.");
        exit;
    }

    if( !verifierSpecialiteClasse($specialite,$classe,$connexion,$etablissement)){
        header("location: demande?erreur=Erreur ! Votre classe ou specialité est erronée.");
        exit;
    }

    if( !verifierSpecialiteParcours($specialite,$parcours,$connexion,$etab)){
        header("location: demande?erreur=Erreur ! Votre Parcours ou specialité est erronée.");
        exit;
    }
    // Validation de l'email
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        die("L'adresse email n'est pas valide. Veuillez retourner et corriger le formulaire.");
        header("location: demande?erreur=L'adresse email n'est pas valide. Veuillez retourner et corriger le formulaire.");
        exit;
    }
/*
    if(!verifierNotesLicence($etudiant,$connexion,$etablissement)){
        header("location: demande?erreur=L'etudiant n'as pas validés tous ses semestres");
        exit;
    }*/



    if( !isSpecialiteInEtablissement($connexion,$specialite,$etab)){
        header("location: demande?erreur=la specialité choisie ne correspond pas à l'etablissement.");
        exit;
    }

/*
if( $type_demande =="Attestation" or $type_demande =="Diplome")
{
    if(verifierSoutenanceEtudiant($etudiant,$connexion,$etablissement) == false){
        header("location: demande?erreur=L'etudiant n'a pas encore soutenue.");
        exit;
    }
 }*/

    
    $code= generateUniqueDemande();
    $sql = "INSERT INTO demande (code,etudiant, date_demande, specialite, parcours,diplome,annee_univ,utilisateur,etab,sexe,statut_soumission,statut) VALUES (
                            '$code',$etudiant, '".date("Y-m-d")."', '$specialite', '$parcours', '$type_demande', '$annee', '$utilisateur','$etablissement','$sexe',1,0)";
    if ($connexion->query($sql)) {
        $userIP = $_SERVER['REMOTE_ADDR'];
        

        logUserAction($connexion,$_SESSION['id_user'],"enregistrement d'une demande de retrait d'un diplome",date("Y-m-d H:i:s"),$userIP,"valeur enregistrée : $code+$etudiant+$diplome+$annee+$etablissement+$specialite+$parcours+$diplome+utilisateur:$utilisateur");

        header("location: demande?sucess='Votre demande à été enregistrée avec sucess.");
    } else {
        header("location: demande?erreur=$connexion->error");
    }

 

  

  
}

?>




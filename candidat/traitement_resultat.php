<?php 
include '../php/connexion.php';
include '../php/lib.php';

session_start();

// Vérifier la session
if($_SESSION['id'] !==  session_id()){
    header("location: ../connexion");
    exit;
}

// Vérifier que c'est bien une requête POST
if($_SERVER["REQUEST_METHOD"] !== "POST"){
    header("location: resultat?erreur=Requête invalide");
    exit;
}

// Vérifier que les champs requis sont présents
if(!isset($_POST["matricule"]) || !isset($_POST["annee"])){
    header("location: resultat?erreur=Données manquantes");
    exit;
}

// Nettoyer les données du formulaire
$nom = $_POST["nom"];
$prenom = $_POST["prenom"];
$matricule = $_POST["matricule"];
$classe = $_POST["classe"];
$semestre = $_POST["semestre"];
$etablissement = $_POST["etablissement"];
$etab = getLibelleEtablissement($etablissement, $connexion);
$examen = $_POST["examen"];
$annee = $_POST["annee"];



$sql = "SELECT id 
        FROM inscription 
        WHERE candidat = ?
        AND annee = ?
        AND classe = ?
        LIMIT 1";

$stmt = $connexion->prepare($sql);

if (!$stmt) {
    header("location: resultat?erreur=Erreur SQL (prepare): ". $connexion->error);
    exit;
}

$stmt->bind_param("sss", $matricule, $annee, $classe);

if (!$stmt->execute()) {
    header("location: resultat?erreur=Erreur SQL (execute): " . $stmt->error);
    exit;
}

$result = $stmt->get_result();
$row = $result->fetch_assoc();

if (!$row) {
    header("location: resultat?erreur=Erreur ! Votre matricule est erroné. Matricule : $matricule, Classe: $classe, Année: $annee");
    exit;
}

$etudiant = (int)$row['id'];


// Vérifier l'identité de l'étudiant
if(verifierIdentityEtudiant($connexion, $matricule, $nom, $prenom, $etab) === false ){
    header("location: resultat?erreur=Erreur ! Votre nom, prénom ou matricule ne correspond pas.");
    exit;
}

// Vérifier l'inscription
if(!verifierInscription2($matricule, $annee, $connexion, $etablissement)){
    header("location: resultat?erreur=Erreur ! Votre classe ou année universitaire est erronée.");
    exit;
}

// Vérifier que l'étudiant est bien dans cette classe
if(!verifierClasseEtudiant($etudiant, $classe, $annee, $connexion, $etablissement)){
    header("location: resultat?erreur=Erreur ! La classe saisie ne correspond pas à votre inscription.");
    exit;
}

// Vérifier que la spécialité correspond à l'établissement
if(!isSpecialiteInEtablissement($connexion, getSpecialiteClasse($connexion, $classe), $etab)){
    header("location: resultat?erreur=La classe choisie ne correspond pas à l'établissement.");
    exit;
}

// Vérifier que les résultats sont disponibles
if(!verifierEntree($connexion, $etudiant, $semestre, $examen, $annee, $etablissement)){
    header("location: resultat?erreur=Les résultats ne sont pas encore disponibles.");
    exit;
}

// Logger l'action
$utilisateur = $_SESSION["id_user"];
$userIP = $_SERVER['REMOTE_ADDR'];
logUserAction($connexion, $_SESSION['id_user'], "Accès aux résultats par l'étudiant $etudiant, $semestre, $annee, $examen", date("Y-m-d H:i:s"), $userIP, "utilisateur:$utilisateur");

// Redirection vers la page de visualisation des résultats
header("location: voir?classe=$classe&etudiant=$etudiant&semestre=$semestre&annee=$annee&examen=$examen&etablissement=$etablissement");
exit;
?>
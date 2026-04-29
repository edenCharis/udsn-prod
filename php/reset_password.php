<?php
session_start();
require_once 'connexion.php'; // Votre fichier de connexion à la base de données

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user_type = $_POST['user_type'] ?? '';
    
    if (empty($user_type)) {
        header("Location: ../forgot_password.php?erreur=" . urlencode("Type de compte non spécifié"));
        exit();
    }
    
    try {
        if ($user_type === 'enseignant') {
            handleEnseignantReset($connexion);
        } elseif ($user_type === 'etudiant') {
            handleEtudiantReset($connexion);
        } else {
            header("Location: ../forgot_password.php?erreur=" . urlencode("Type de compte invalide"));
            exit();
        }
    } catch (Exception $e) {
        header("Location: ../forgot_password.php?erreur=" .
        
        
        urlencode("Erreur système: " . $e->getMessage()));
        exit();
    }
} else {
    header("Location: ../forgot_password.php");
    exit();
}

function handleEnseignantReset($connexion) {
    // Récupération des données
    $login = trim($_POST['enseignant_login'] ?? '');
    $etablissement = trim($_POST['enseignant_etablissement'] ?? '');
    $code_contrat = trim($_POST['code_contrat'] ?? '');

    // Validation des champs obligatoires
    if (empty($login) || empty($etablissement) || empty($code_contrat)) {
        header("Location: ../forgot_password.php?erreur=" . urlencode("Tous les champs sont obligatoires"));
        exit();
    }
    
  
    
 
    
    // Vérifier l'enseignant dans la base de données
    // Ajustez les noms de colonnes selon votre structure de base de données
    $query = "SELECT * FROM utilisateur 
              WHERE login = ? 
              AND etab = ? 
              AND code_enseignant = ? ";
    
    $stmt = $connexion->prepare($query);
    $stmt->bind_param("sss", $login, $etablissement, $code_contrat);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0) {
        header("Location: ../forgot_password.php?erreur=" . urlencode("Les informations fournies ne correspondent à aucun enseignant"));
        exit();
    }
    
$user = $result->fetch_assoc();
    
  
    // Générer un token de réinitialisation
    $token = bin2hex(random_bytes(32));
    $token_expiry = date('Y-m-d H:i:s', strtotime('+1 hour'));
    
    // Enregistrer le token dans la base de données
    $update_query = "UPDATE utilisateur 
                     SET reset_token = ?, 
                         reset_token_expiry = ? 
                     WHERE id = ?";
    
    $update_stmt = $connexion->prepare($update_query);
    $update_stmt->bind_param("ssi", $token, $token_expiry, $user['id']);
    
    if ($update_stmt->execute()) {
        // Rediriger vers la page de création du nouveau mot de passe
        header("Location: ../new_password.php?token=" . $token . "&type=enseignant");
        exit();
    } else {
        header("Location: ../forgot_password.php?erreur=" . urlencode("Erreur lors de la génération du lien de réinitialisation"));
        exit();
    }
}

function handleEtudiantReset($connexion) {
    // Récupération des données
    $login = trim($_POST['etudiant_login'] ?? '');
    $etablissement = trim($_POST['etudiant_etablissement'] ?? '');
    $matricule = trim($_POST['matricule'] ?? '');
    
    // Validation des champs obligatoires
    if (empty($login) || empty($etablissement) || empty($matricule)) {
        header("Location: ../forgot_password.php?erreur=" . urlencode("Tous les champs sont obligatoires"));
        exit();
    }
    
    // Vérifier l'étudiant dans la base de données
    // Ajustez les noms de colonnes selon votre structure de base de données
    $query = "SELECT * FROM utilisateur 
              WHERE login = ? 
              AND etab = ? 
              AND matricule = ?";
    
    $stmt = $connexion->prepare($query);
    $stmt->bind_param("sss", $login, $etablissement, $matricule);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0) {
        header("Location: ../forgot_password.php?erreur=" . urlencode("Les informations fournies ne correspondent à aucun étudiant"));
        exit();
    }
    
    $etudiant = $result->fetch_assoc();
    
    // Générer un token de réinitialisation
    $token = bin2hex(random_bytes(32));
    $token_expiry = date('Y-m-d H:i:s', strtotime('+1 hour'));
    
    // Enregistrer le token dans la base de données
    $update_query = "UPDATE utilisateur 
                     SET reset_token = ?, 
                         reset_token_expiry = ? 
                     WHERE id = ?";
    
    $update_stmt = $connexion->prepare($update_query);
    $update_stmt->bind_param("ssi", $token, $token_expiry, $etudiant['id']);
    
    if ($update_stmt->execute()) {
        // Rediriger vers la page de création du nouveau mot de passe
        header("Location: ../new_password.php?token=" . $token . "&type=etudiant");
        exit();
    } else {
        header("Location: ../forgot_password.php?erreur=" . urlencode("Erreur lors de la génération du lien de réinitialisation"));
        exit();
    }
}
?>
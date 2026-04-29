<?php
session_start();
require_once 'connexion.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $token = trim($_POST['token'] ?? '');
    $type = trim($_POST['type'] ?? '');
    $password = trim($_POST['password'] ?? '');
    $confirm_password = trim($_POST['confirm_password'] ?? '');
    
    // Validation de base
    if (empty($token) || empty($type) || empty($password) || empty($confirm_password)) {
        header("Location: ../new_password.php?token=$token&type=$type&erreur=" . urlencode("Tous les champs sont obligatoires"));
        exit();
    }
    
    // Vérifier que les mots de passe correspondent
    if ($password !== $confirm_password) {
        header("Location: ../new_password.php?token=$token&type=$type&erreur=" . urlencode("Les mots de passe ne correspondent pas"));
        exit();
    }
    
    // Valider la force du mot de passe
    if (!isPasswordStrong($password)) {
        header("Location: ../new_password.php?token=$token&type=$type&erreur=" . urlencode("Le mot de passe doit avoir au minimu 8 caractères. Une lettre majuscule, une lettre miniscule, un caractère special et un chiffre"));
        exit();
    }
    
    // Déterminer la table en fonction du type
    $table = ($type === 'enseignant') ? 'utilisateur' : 'utilisateur';
    
    // Vérifier que le token est valide et non expiré
    $query = "SELECT * FROM $table WHERE reset_token = ? AND reset_token_expiry > NOW()";
    $stmt = $connexion->prepare($query);
    $stmt->bind_param("s", $token);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0) {
        header("Location: ../index.php?erreur=" . urlencode("Le lien de réinitialisation a expiré ou est invalide"));
        exit();
    }
    
    $user = $result->fetch_assoc();
    
    // Hasher le nouveau mot de passe
 //   $hashed_password = password_hash($password, PASSWORD_DEFAULT);
    
    // Mettre à jour le mot de passe et supprimer le token
    $update_query = "UPDATE $table 
                     SET mdp = ?, 
                         reset_token = NULL, 
                         reset_token_expiry = NULL 
                     WHERE id = ?";
    
    $update_stmt = $connexion->prepare($update_query);
    $update_stmt->bind_param("si", $password, $user['id']);
    
    if ($update_stmt->execute()) {
        // Enregistrer l'événement dans un log (optionnel)
        logPasswordReset($connexion, $user['id'], $type);
        
        // Rediriger vers la page de connexion avec un message de succès
        header("Location: ../index.php?success=" . urlencode("Votre mot de passe a été réinitialisé avec succès. Vous pouvez maintenant vous connecter."));
        exit();
    } else {
        header("Location: ../new_password.php?token=$token&type=$type&erreur=" . urlencode("Erreur lors de la mise à jour du mot de passe"));
        exit();
    }
} else {
    header("Location: ../index.php");
    exit();
}

/**
 * Vérifie si le mot de passe respecte les exigences de sécurité
 */
function isPasswordStrong($password) {
    // Au moins 8 caractères
    if (strlen($password) < 8) {
        return false;
    }
    
    // Au moins une lettre minuscule
    if (!preg_match('/[a-z]/', $password)) {
        return false;
    }
    
    // Au moins une lettre majuscule
    if (!preg_match('/[A-Z]/', $password)) {
        return false;
    }
    
    // Au moins un chiffre
    if (!preg_match('/[0-9]/', $password)) {
        return false;
    }
    
    // Au moins un caractère spécial
    if (!preg_match('/[!@#$%^&*]/', $password)) {
        return false;
    }
    
    return true;
}

/**
 * Enregistre l'événement de réinitialisation de mot de passe (optionnel)
 */
function logPasswordReset($connexion, $user_id, $type) {
    try {
        $log_query = "INSERT INTO password_reset_logs (user_id, user_type, reset_date, ip_address) 
                      VALUES (?, ?, NOW(), ?)";
        $log_stmt = $connexion->prepare($log_query);
        $ip_address = $_SERVER['REMOTE_ADDR'] ?? 'Unknown';
        $log_stmt->bind_param("iss", $user_id, $type, $ip_address);
        $log_stmt->execute();
    } catch (Exception $e) {
        // Ne pas bloquer le processus si le log échoue
        error_log("Erreur lors de l'enregistrement du log: " . $e->getMessage());
    }
}
?>
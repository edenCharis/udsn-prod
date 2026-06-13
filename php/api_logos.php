<?php
/**
 * API Helper pour les logos
 * 
 * Endpoints pour manipuler les logos via AJAX
 * 
 * Usage:
 * - Récupérer le logo par défaut
 * - Obtenir le logo d'une université spécifique
 * - Lister tous les logos
 * - Modifier les logos (avec permission)
 */

header('Content-Type: application/json');

// Inclure les configurations et bibliothèques
include __DIR__ . '/connexion.php';
include __DIR__ . '/lib.php';
include __DIR__ . '/../config/logo_config.php';

session_start();

// Fonction utilitaire pour les réponses JSON
function sendResponse($success, $message, $data = null) {
    echo json_encode([
        'success' => $success,
        'message' => $message,
        'data' => $data
    ]);
    exit;
}

// Déterminer l'action demandée
$action = $_GET['action'] ?? $_POST['action'] ?? null;

if (!$action) {
    sendResponse(false, 'Action non spécifiée');
}

$logoConfig = getLogoConfig();

switch ($action) {
    
    // Obtenir le logo par défaut
    case 'get_default':
        $logo = $logoConfig->getDefaultLogo();
        sendResponse(true, 'Logo par défaut', ['logo' => $logo]);
        break;
    
    // Obtenir le logo d'une université
    case 'get_university_logo':
        if (!isset($_GET['univ_code'])) {
            sendResponse(false, 'Code université manquant');
        }
        
        $univ_code = intval($_GET['univ_code']);
        $sql = "SELECT logo FROM univ WHERE code = ?";
        $stmt = $connexion->prepare($sql);
        $stmt->bind_param("i", $univ_code);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($row = $result->fetch_assoc()) {
            $logo = !empty($row['logo']) ? $row['logo'] : $logoConfig->getDefaultLogo();
            sendResponse(true, 'Logo trouvé', [
                'logo' => $logo,
                'univ_code' => $univ_code
            ]);
        } else {
            sendResponse(false, 'Université non trouvée');
        }
        break;
    
    // Obtenir le logo de l'utilisateur connecté
    case 'get_user_logo':
        if (!isset($_SESSION['id_user'])) {
            sendResponse(false, 'Utilisateur non authentifié');
        }
        
        $logo = $logoConfig->getLogoForUser($_SESSION['id_user']);
        sendResponse(true, 'Logo utilisateur', ['logo' => $logo]);
        break;
    
    // Lister tous les logos
    case 'list_all':
        $logos = $logoConfig->getAllLogos();
        sendResponse(true, 'Logos listés', $logos);
        break;
    
    // Mettre à jour le logo (admin uniquement)
    case 'update_logo':
        // Vérifier les permissions
        if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'administrateur') {
            sendResponse(false, 'Permission refusée');
        }
        
        $univ_code = intval($_POST['univ_code'] ?? 0);
        $logo_path = $_POST['logo_path'] ?? '';
        
        if ($univ_code <= 0 || empty($logo_path)) {
            sendResponse(false, 'Paramètres invalides');
        }
        
        if ($logoConfig->updateUniversityLogo($univ_code, $logo_path)) {
            sendResponse(true, 'Logo mis à jour avec succès', [
                'univ_code' => $univ_code,
                'logo' => $logo_path
            ]);
        } else {
            sendResponse(false, 'Erreur lors de la mise à jour');
        }
        break;
    
    // Obtenir le favicon par défaut
    case 'get_favicon':
        $use_session = isset($_GET['session']) && $_GET['session'] === '1';
        $favicon = getFavicon($use_session);
        sendResponse(true, 'Favicon', ['favicon' => $favicon]);
        break;
    
    // Obtenir le nom de l'université par défaut
    case 'get_default_university_name':
        $name = $logoConfig->getDefaultUniversityName();
        sendResponse(true, 'Nom de l\'université par défaut', ['name' => $name]);
        break;
    
    // Obtenir le nom de l'université d'un utilisateur
    case 'get_university_name':
        if (!isset($_GET['univ_code'])) {
            sendResponse(false, 'Code université manquant');
        }
        
        $univ_code = intval($_GET['univ_code']);
        $name = $logoConfig->getUniversityName($univ_code);
        sendResponse(true, 'Nom de l\'université', [
            'name' => $name,
            'univ_code' => $univ_code
        ]);
        break;
    
    // Obtenir le nom de l'université depuis la session
    case 'get_university_name_from_session':
        $name = $logoConfig->getUniversityNameFromSession();
        sendResponse(true, 'Nom de l\'université', ['name' => $name]);
        break;
    
    // Mettre à jour le nom de l'université (admin uniquement)
    case 'update_university_name':
        // Vérifier les permissions
        if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'administrateur') {
            sendResponse(false, 'Permission refusée');
        }
        
        $univ_code = intval($_POST['univ_code'] ?? 0);
        $nom_univ = $_POST['nom_univ'] ?? '';
        
        if ($univ_code <= 0 || empty($nom_univ)) {
            sendResponse(false, 'Paramètres invalides');
        }
        
        if ($logoConfig->updateUniversityName($univ_code, $nom_univ)) {
            sendResponse(true, 'Nom mis à jour avec succès', [
                'univ_code' => $univ_code,
                'name' => $nom_univ
            ]);
        } else {
            sendResponse(false, 'Erreur lors de la mise à jour');
        }
        break;
    
    default:
        sendResponse(false, 'Action inconnue: ' . htmlspecialchars($action));
}

?>

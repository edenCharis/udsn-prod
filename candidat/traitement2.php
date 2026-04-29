<?php 
include '../php/connexion.php';
include '../php/lib.php';
session_start();

// Check if user is logged in
if (!isset($_SESSION['id_user'])) {
    header("location: ../connexion.php");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST"){
    
    // Start transaction
    $connexion->begin_transaction();
    
    try {
        $user_id = $_SESSION['id_user'];
        $updates = [];
        $params = [];
        $types = "";
        $log_details = [];
        
        // Validate and sanitize nom
        if(isset($_POST['usernom']) && !empty(trim($_POST['usernom']))){
            $nom = trim($_POST['usernom']);
            if(strlen($nom) > 0 && strlen($nom) <= 100){
                $updates[] = "nom = ?";
                $params[] = $nom;
                $types .= "s";
                $log_details[] = "nom: $nom";
            }
        }
        
        // Validate and sanitize login
        if(isset($_POST['userlogin']) && !empty(trim($_POST['userlogin']))){
            $login = trim($_POST['userlogin']);
            if(strlen($login) >= 3 && strlen($login) <= 50){
                // Check if login already exists for another user
                $check_stmt = $connexion->prepare("SELECT id FROM utilisateur WHERE login = ? AND id != ?");
                $check_stmt->bind_param("si", $login, $user_id);
                $check_stmt->execute();
                $check_result = $check_stmt->get_result();
                
                if($check_result->num_rows > 0){
                    throw new Exception("Ce login existe déjà");
                }
                $check_stmt->close();
                
                $updates[] = "login = ?";
                $params[] = $login;
                $types .= "s";
                $log_details[] = "login: $login";
            } else {
                throw new Exception("Le login doit contenir entre 3 et 50 caractères");
            }
        }
        
        // Validate matricule - check if exists in candidat table
        if(isset($_POST['matricule']) && !empty(trim($_POST['matricule']))){
            $matricule = trim($_POST['matricule']);
            
            // Check if matricule exists in candidat table
            $check_stmt = $connexion->prepare("SELECT code FROM candidat WHERE code = ?");
            $check_stmt->bind_param("s", $matricule);
            $check_stmt->execute();
            $check_result = $check_stmt->get_result();
            
            if($check_result->num_rows == 0){
                $check_stmt->close();
                throw new Exception("Matricule invalide - n'existe pas dans la base des candidats");
            }
            $check_stmt->close();
            
            $updates[] = "matricule = ?";
            $params[] = $matricule;
            $types .= "s";
            $log_details[] = "matricule: $matricule";
        }
        
        // Validate classe - check if exists in classe table
        if(isset($_POST['classe']) && !empty(trim($_POST['classe']))){
            $classe = trim($_POST['classe']);
            
            // Check if classe exists in classe table (adjust column names as needed)
            $check_stmt = $connexion->prepare("SELECT id FROM classe WHERE libelle = ?");
            $check_stmt->bind_param("s", $classe);
            $check_stmt->execute();
            $check_result = $check_stmt->get_result();
            
            if($check_result->num_rows == 0){
                $check_stmt->close();
                throw new Exception("Classe invalide - n'existe pas dans la base des classes");
            }
            $check_stmt->close();
            
            $updates[] = "classe = ?";
            $params[] = $classe;
            $types .= "s";
            $log_details[] = "classe: $classe";
        }
        
       
        
        // Handle file upload with validation
        if(isset($_FILES['img']) && $_FILES['img']['error'] === UPLOAD_ERR_OK){
            $allowed_types = ['image/jpeg', 'image/png', 'image/gif'];
            $allowed_extensions = ['jpg', 'jpeg', 'png', 'gif'];
            $max_size = 5 * 1024 * 1024; // 5MB
            
            $file_type = $_FILES['img']['type'];
            $file_size = $_FILES['img']['size'];
            $file_extension = strtolower(pathinfo($_FILES['img']['name'], PATHINFO_EXTENSION));
            
            // Validate file type and extension
            if(!in_array($file_type, $allowed_types) || !in_array($file_extension, $allowed_extensions)){
                throw new Exception("Type de fichier invalide. Formats acceptés: JPG, JPEG, PNG, GIF");
            }
            
            // Validate file size
            if($file_size > $max_size){
                throw new Exception("La taille du fichier ne doit pas dépasser 5MB");
            }
            
            // Create unique filename
            $new_filename = uniqid('user_' . $user_id . '_') . '.' . $file_extension;
            $upload_dir = 'photos/';
            
            // Create directory if it doesn't exist
            if(!is_dir($upload_dir)){
                if(!mkdir($upload_dir, 0755, true)){
                    throw new Exception("Impossible de créer le répertoire de téléchargement");
                }
            }
            
            $nouveau_chemin = $upload_dir . $new_filename;
            
            // Move uploaded file
            if(move_uploaded_file($_FILES['img']['tmp_name'], $nouveau_chemin)){
                // Delete old photo if exists
                $old_photo_query = $connexion->prepare("SELECT img FROM utilisateur WHERE id = ?");
                $old_photo_query->bind_param("i", $user_id);
                $old_photo_query->execute();
                $old_photo_result = $old_photo_query->get_result();
                
                if($old_photo_row = $old_photo_result->fetch_assoc()){
                    if(!empty($old_photo_row['img']) && file_exists($old_photo_row['img'])){
                        unlink($old_photo_row['img']);
                    }
                }
                $old_photo_query->close();
                
                $updates[] = "img = ?";
                $params[] = $nouveau_chemin;
                $types .= "s";
                $log_details[] = "photo: $nouveau_chemin";
            } else {
                throw new Exception("Erreur lors du téléchargement du fichier");
            }
        } elseif(isset($_FILES['img']) && $_FILES['img']['error'] !== UPLOAD_ERR_NO_FILE) {
            // Handle upload errors
            $upload_errors = [
                UPLOAD_ERR_INI_SIZE => "Le fichier dépasse la taille maximale autorisée par le serveur",
                UPLOAD_ERR_FORM_SIZE => "Le fichier dépasse la taille maximale autorisée",
                UPLOAD_ERR_PARTIAL => "Le fichier n'a été que partiellement téléchargé",
                UPLOAD_ERR_NO_TMP_DIR => "Répertoire temporaire manquant",
                UPLOAD_ERR_CANT_WRITE => "Échec de l'écriture du fichier sur le disque",
                UPLOAD_ERR_EXTENSION => "Une extension PHP a arrêté le téléchargement"
            ];
            
            $error_message = isset($upload_errors[$_FILES['img']['error']]) 
                ? $upload_errors[$_FILES['img']['error']] 
                : "Erreur inconnue lors du téléchargement";
                
            throw new Exception($error_message);
        }
        
        // Execute update if there are changes
        if(count($updates) > 0){
            $sql = "UPDATE utilisateur SET " . implode(", ", $updates) . " WHERE id = ?";
            $params[] = $user_id;
            $types .= "i";
            
            $stmt = $connexion->prepare($sql);
            if(!$stmt){
                throw new Exception("Erreur de préparation de la requête: " . $connexion->error);
            }
            
            $stmt->bind_param($types, ...$params);
            
            if(!$stmt->execute()){
                throw new Exception("Erreur lors de l'exécution: " . $stmt->error);
            }
            
            if($stmt->affected_rows === 0){
                throw new Exception("Aucune modification n'a été effectuée");
            }
            
            $stmt->close();
            
            // Update session variables if needed
            if(isset($login)){
                $_SESSION['login'] = $login;
            }
            if(isset($matricule)){
                $_SESSION['matricule'] = $matricule;
            }
            if(isset($classe)){
                $_SESSION['classe'] = $classe;
            }
            if(isset($nouveau_chemin)){
                $_SESSION['img'] = $nouveau_chemin;
            }
            
            // Log the action
            $userIP = $_SERVER['REMOTE_ADDR'];
            $log_message = "Modification du compte";
            $log_value = implode(", ", $log_details);
            
            logUserAction($connexion, $_SESSION['id_user'], $log_message, date("Y-m-d H:i:s"), $userIP, $log_value);
            
            // Commit transaction
            $connexion->commit();
            
            header("location: compte.php?success=Modification effectuée avec succès");
            exit();
            
        } else {
            throw new Exception("Aucune modification à effectuer");
        }
        
    } catch (Exception $e) {
        // Rollback transaction on error
        $connexion->rollback();
        
        // Log the error
        error_log("Error in traitement2.php: " . $e->getMessage());
        
        // Redirect with error message
        header("location: compte.php?erreur=" . urlencode($e->getMessage()));
        exit();
    }
    
} else {
    header("location: compte.php");
    exit();
}
?>
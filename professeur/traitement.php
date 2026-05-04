<?php
session_start();
// Interface désactivée — les notes passent par notation1/notation2 + ligne1/ligne2
header("Location: index?erreur=" . urlencode("Interface désactivée. Utilisez la saisie par anonymat."));
exit;

// Inclusions après le démarrage de la session
include '../php/connexion.php';
include '../php/lib.php';

//error_reporting(E_ALL);
//ini_set('display_errors', 1);

if($_SERVER["REQUEST_METHOD"] == "POST")
{
    // Traitement des notes
    if(isset($_POST['etudiant']) && isset($_POST['moydev']) && isset($_POST['moyex'])) {
        $etudiant = $_POST['etudiant'];
        $ecue = clean_input($_POST['ecue']);
        $classe = clean_input($_POST['classe']);
        $annee = $_POST['annee'];
        $etab = $_SESSION['etablissement'];
        $sem = obtenirSemestrePourECUE($ecue, $connexion);
        $moyenneDevoir = $_POST['moydev'];
        $moyenneExamen = $_POST['moyex'];
        $ex = ($moyenneDevoir + $moyenneExamen) / 2; 
        
        if(eleveDansClasse(getCandidatCodeByInscription($etudiant, $connexion), $classe, $annee, $connexion)) {
            $sql = "INSERT INTO notation (inscription, classe, ecue, annee, moyDev, moyEx, moyGen, etab, semestre) VALUES 
                ($etudiant, '$classe', '$ecue', '$annee', $moyenneDevoir, $moyenneExamen, $ex, '$etab', '$sem')";
            
            if($connexion->query($sql)) {
                $userIP = $_SERVER['REMOTE_ADDR'];
                logUserAction($connexion, $_SESSION['id_user'], "enregistrement d'une note", date("Y-m-d H:i:s"), $userIP, "valeur enregistrée: $etudiant+$classe+$annee");
                header("Location: index?sucess=Enregistrement effectué avec succès");
                exit; // Arrêt du script après redirection
            } else {
                header("Location: index?erreur=" . urlencode($connexion->error));
                exit;
            }
        } else {
            header("Location: index?erreur=L'étudiant choisi n'est pas inscrit dans cette classe.");
            exit;
        }
    }
    
    // Mise à jour des notes
    if(isset($_POST['noteId']) && isset($_POST['nouveauMoyDev']) && isset($_POST['nouveauMoyEx'])) {
        $id = $_POST['noteId'];
        $x = $_POST['nouveauMoyDev'];
        $y = $_POST['nouveauMoyEx'];
        $w = $_POST['nouveausession'];

        // Vérification et attribution des valeurs
        $x = isset($x) && !empty($x) ? $x : 'null';
        $y = isset($y) && !empty($y) ? $y : 'null';
        $w = isset($w) && !empty($w) ? $w : 'null';

        // Calcul de la moyenne générale
        $z = 0;
        if($x != 'null' && $y == 'null' && $w == 'null') {
            // Cas spécifique
            if($y < $w) {
                $z = ($x + $w) / 2;
            } else {
                $z = ($x + $y) / 2;
            }
        } else {
            // Calcul standard
            if($x != 'null' && $y != 'null') {
                $z = ($x + $y) / 2;
            } elseif($x != 'null' && $w != 'null') {
                $z = ($x + $w) / 2;
            }
        }

        $sql = "UPDATE notation SET moyDev=$x, moyEx=$y, moyGen=$z, session_rappel=$w WHERE id=$id";

        if($connexion->query($sql)) {
            $userIP = $_SERVER['REMOTE_ADDR'];
            logUserAction($connexion, $_SESSION['id_user'], "modification d'une note", date("Y-m-d H:i:s"), $userIP, "valeur modifiée: $x + $y + $z");
            header("Location: index?sucess=Modification effectuée avec succès");
            exit;
        } else {
            header("Location: index?erreur=" . urlencode($connexion->error));
            exit;
        }
    }

    // Mise à jour du profil utilisateur

if(isset($_POST["nom"]) && isset($_POST["login"])) {
    
    // Vérification du code unique
    if(isset($_POST["code_unique"]) && $_POST["code_unique"] != "") {
        $code_unique = $_POST["code_unique"];
        
        $stmt_verif = $connexion->prepare("SELECT id FROM enseignant WHERE code = ? LIMIT 1");
        $stmt_verif->bind_param("s", $code_unique);
        $stmt_verif->execute();
        $result = $stmt_verif->get_result();
        
        if($result->num_rows == 0) {
            $stmt_verif->close();
            header("Location: compte?erreur=Votre code unique est erroné.");
            exit;
        }
      
    }
    
    $updates = [];
    $params = [];
    $types = "";
    
    // Traitement du nom
    if(isset($_POST['nom']) && !empty(trim($_POST['nom']))) {
        $updates[] = "nom = ?";
        $params[] = trim($_POST['nom']);
        $types .= "s";
    }
    
    // Traitement du login
    if(isset($_POST['login']) && !empty(trim($_POST['login']))) {
        $updates[] = "login = ?";
        $params[] = trim($_POST['login']);
        $types .= "s";
    }
    
    // Traitement du mot de passe
    if(isset($_POST['mdp']) && !empty($_POST['mdp'])) {
        $updates[] = "mdp = ?";
        $params[] = $_POST['mdp'];
        $types .= "s";
        
        // IMPORTANT: Ce code stocke les mots de passe en clair (non sécurisé)
        // Pour une meilleure sécurité, utilisez:
        // $params[] = password_hash($_POST['mdp'], PASSWORD_DEFAULT);
        // Et lors de la connexion: password_verify($mdp_saisi, $mdp_bdd)
    }
    
    // Traitement de l'année
    if(isset($_POST['annee']) && !empty($_POST['annee'])) {
        $updates[] = "annee = ?";
        $params[] = intval($_POST['annee']);
        $types .= "i";
    }
    
    // Traitement du code unique enseignant
    if(isset($_POST["code_unique"]) && verifierCode($_POST["code_unique"], $connexion)) {
        $updates[] = "statut = ?";
        $updates[] = "code_enseignant = ?";
        $params[] = 1;
        $params[] = trim($_POST["code_unique"]);
        $types .= "is";
    }
    
    // Traitement de l'image
    if(isset($_FILES['img']) && $_FILES['img']['error'] === UPLOAD_ERR_OK) {
        $nomFichier = $_FILES['img']['name'];
        $typeFichier = $_FILES['img']['type'];
        $cheminTemporaire = $_FILES['img']['tmp_name'];
        
        // Validation du type de fichier
        $extensions_autorisees = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
        $extension = strtolower(pathinfo($nomFichier, PATHINFO_EXTENSION));
        
        // Validation MIME type
        $types_mime_autorises = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
        
        if(in_array($extension, $extensions_autorisees) && in_array($typeFichier, $types_mime_autorises)) {
            // Limite de taille (5MB)
            if($_FILES['img']['size'] <= 5242880) {
                // Générer un nom unique pour éviter les collisions
                $nom_unique = uniqid() . '_' . bin2hex(random_bytes(8)) . '.' . $extension;
                $nouveauChemin = "photos/" . $nom_unique;
                
                // Créer le dossier s'il n'existe pas
                if(!is_dir("photos")) {
                    mkdir("photos", 0755, true);
                }
                
                if(move_uploaded_file($cheminTemporaire, $nouveauChemin)) {
                    $updates[] = "img = ?";
                    $params[] = $nouveauChemin;
                    $types .= "s";
                } else {
                    header("Location: compte?erreur=" . urlencode("Erreur lors du téléchargement de l'image"));
                    exit;
                }
            } else {
                header("Location: compte?erreur=" . urlencode("L'image est trop volumineuse (max 5MB)"));
                exit;
            }
        } else {
            header("Location: compte?erreur=" . urlencode("Type de fichier non autorisé"));
            exit;
        }
    }
    
    // Construction et exécution de la requête
    if(!empty($updates)) {
        $sql = "UPDATE utilisateur SET " . implode(', ', $updates) . " WHERE id = ?";
        
        // Ajouter l'ID utilisateur aux paramètres
        $params[] = $_SESSION["id_user"];
        $types .= "i";
        
        // Préparer la requête
        $stmt = $connexion->prepare($sql);
        
        if($stmt === false) {
            header("Location: compte?erreur=" . urlencode("Erreur de préparation de la requête"));
            exit;
        }
        
        // Bind des paramètres
        $stmt->bind_param($types, ...$params);
        
        // Exécution
        if($stmt->execute()) {
            // Log de l'action
            $role = $_SESSION['role'] ?? '';
            $etab = $_SESSION['etablissement'] ?? '';
            $userIP = $_SERVER['REMOTE_ADDR'];
            
            $log_details = "Modification profil - ";
            $log_details .= isset($_POST['nom']) ? "nom: " . $_POST['nom'] . " | " : "";
            $log_details .= isset($_POST['login']) ? "login: " . $_POST['login'] . " | " : "";
            $log_details .= isset($_POST['mdp']) ? "mot de passe modifié | " : "";
            $log_details .= "role: $role | etablissement: $etab";
            
            logUserAction($connexion, $_SESSION['id_user'], "modification d'un profil utilisateur", 
                        date("Y-m-d H:i:s"), $userIP, $log_details);
            
            $stmt->close();
            header("Location: compte?success=Modification effectuée avec succès");
            exit;
        } else {
            $stmt->close();
            header("Location: compte?erreur=" . urlencode("Erreur lors de la mise à jour"));
            exit;
        }
        
    } else {
        header("Location: compte?info=Aucune modification effectuée");
        exit;
    }
}

}
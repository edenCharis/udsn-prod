<?php
// Démarrage de la session en tout premier
session_start();

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
            
            if(!verifierCode($code_unique, $connexion)) {
                header("Location: compte?erreur=Votre code unique est erroné.");
                exit;
            }
        }    

        $sql = "UPDATE utilisateur SET";
        $updates = [];

        // Traitement du nom
        if(isset($_POST['nom'])) {
            $nom = str_replace("'", "+", $_POST['nom']);
            $updates[] = " nom='$nom'";
        }

        // Traitement du login
        if(isset($_POST['login'])) {
            $login = str_replace("'", "+", $_POST['login']);
            $updates[] = " login='$login'";
        }

        // Traitement du mot de passe
        if(isset($_POST['mdp']) && !empty($_POST['mdp'])) {
            $mdp = $_POST['mdp'];
            $updates[] = " mdp='$mdp'";
        }
        
        // Traitement du code unique
        if(isset($_POST["code_unique"]) && verifierCode($_POST["code_unique"], $connexion)) {
            $code_unique = $_POST["code_unique"];
            $updates[] = " statut=1, code_enseignant='$code_unique'";
        }
              
        // Traitement de l'image
        if($_FILES['img']['error'] === UPLOAD_ERR_OK) {
            $nomFichier = $_FILES['img']['name'];
            $typeFichier = $_FILES['img']['type'];
            $cheminTemporaire = $_FILES['img']['tmp_name'];
            $nouveauChemin = "photos/" . $nomFichier;
    
            if(move_uploaded_file($cheminTemporaire, $nouveauChemin)) {
                $updates[] = " img='$nouveauChemin'";
            }
        }

        // Construction de la requête finale
        if(!empty($updates)) {
            $sql .= implode(',', $updates);
            $id_user = $_SESSION["id_user"];
            $sql .= " WHERE id=$id_user";
     
            if($connexion->query($sql)) {
                $role = $_SESSION['role'];
                $etab = $_SESSION['etablissement'];
                $userIP = $_SERVER['REMOTE_ADDR'];

                logUserAction($connexion, $_SESSION['id_user'], "modification d'un profil utilisateur", 
                            date("Y-m-d H:i:s"), $userIP, 
                            "valeur enregistrée: " . (isset($nom) ? $nom : '') . "+" . 
                            (isset($login) ? $login : '') . "+" . 
                            (isset($mdp) ? $mdp : '') . "+" . 
                            $role . "+" . $etab);
                
                header("Location: compte?sucess=Modification effectuée avec succès");
                exit;
            } else {
                header("Location: compte?erreur=" . urlencode($connexion->error));
                exit;
            }
        } else {
            // Aucune mise à jour à effectuer
            header("Location: compte?info=Aucune modification effectuée");
            exit;
        }
    }
}
?>
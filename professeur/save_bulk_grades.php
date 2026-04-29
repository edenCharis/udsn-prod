<?php 
include '../php/connexion.php';
include '../php/lib.php';
session_start();

// Vérifier la session
if($_SESSION['id'] != session_id() || $_SESSION['role'] != "enseignant"){
    header("location: notation1?erreur=Session invalide");
    exit();
}

// Vérifier que c'est une soumission POST
if($_SERVER['REQUEST_METHOD'] != 'POST'){
    header("location: notation1?erreur=Requête invalide");
    exit();
}

$classe = $_POST['classe'];
$ecue = $_POST['ecue'];
$semestre = $_POST['semestre'];
$annee = $_POST['annee'];
$type_examen = $_POST['type_examen'];
$ecue_libelle = $_POST['ecue_libelle'];
$nature = $_POST['nature'];

$etablissement = $_SESSION['etablissement'];

$success_count = 0;
$error_count = 0;
$errors = [];


// À ajouter dans ajax_load_students.php et save_bulk_grades.php
if(VerifierSemestre($connexion, $semestre, $annee, $_SESSION['etablissement'])) {
    echo json_encode(['error' => 'Accès interdit : ce semestre est clôturé.']);
    exit;
}
// Démarrer une transaction
$connexion->begin_transaction();

try {
    // Traiter chaque note
    foreach($_POST['grades'] as $anonymat => $note) {
        // Ignorer les champs vides
        if(empty($note) || $note === '') continue;
        
        // Valider la note (0-20)
        if($note < 0 || $note > 20) {
            $errors[] = "Note invalide pour $anonymat: $note";
            $error_count++;
            continue;
        }
        
        // Vérifier si la note existe déjà dans ligne1
        $check_stmt = $connexion->prepare("SELECT id FROM ligne1 WHERE anonymat = ? AND code_ecue = ? AND type_examen = ? AND annee = ? AND nature= ? AND semestre=? AND etab=?");
        $check_stmt->bind_param('sssssss', $anonymat, $ecue, $type_examen, $annee,$nature,$semestre,$etablissement);
        $check_stmt->execute();
        $check_result = $check_stmt->get_result();
        
        if($check_result->num_rows > 0) {
            // MISE À JOUR de la note existante
            $row = $check_result->fetch_assoc();
            $update_stmt = $connexion->prepare("UPDATE ligne1 SET note = ?, user_id = ?,type_examen=?,nature=? WHERE id = ?");
            $update_stmt->bind_param('dissi', $note, $_SESSION['id_user'],$type_examen,$nature, $row['id']);
            
            if($update_stmt->execute()) {
                $success_count++;
            } else {
                $error_count++;
                $errors[] = "Erreur mise à jour $anonymat: " . $update_stmt->error;
            }
            $update_stmt->close();
        } else {
            // INSERTION d'une nouvelle note
            $insert_stmt = $connexion->prepare("INSERT INTO ligne1 (nature,anonymat, code_ecue, ecue, semestre, type_examen, note, annee, user_id,etab) VALUES (?,?, ?, ?, ?, ?, ?, ?, ?,?)");
            $insert_stmt->bind_param('ssssssdsis',$nature, $anonymat, $ecue, $ecue_libelle, $semestre, $type_examen, $note, $annee, $_SESSION['id_user'],$etablissement);
            
            if($insert_stmt->execute()) {
                $success_count++;
            } else {
                $error_count++;
                $errors[] = "Erreur insertion $anonymat: " . $insert_stmt->error;
            }
            $insert_stmt->close();
        }
        $check_stmt->close();
        
        // Mettre à jour la table notation
        $etudiant_id = getIdInscriptionFromAnonymat($nature,$type_examen,$anonymat,$annee,$ecue,$semestre,$connexion,$etablissement);
        
        if($etudiant_id) {
            $etablissement = $_SESSION['etablissement'];
            
           // calcul de la moyenne generale de l'ecue ( TP + Theorie)
          $sql_avg = "
    SELECT AVG(l.note) AS moyenne
    FROM ligne1 l
    JOIN anonymat a ON l.anonymat = a.numero
    WHERE l.semestre = ?
      AND l.annee = ?
      AND l.type_examen = ?
      AND l.nature=?
      AND l.etab = ?
      AND l.code_ecue = ?
      AND a.etudiant = ?
   ";

$stmt = $connexion->prepare($sql_avg);
$stmt->bind_param(
    "ssssssi",
    $semestre,
    $annee,
    $type_examen,
    $nature,
    $etablissement,
    $ecue,
    $etudiant_id
);

$stmt->execute();
$result = $stmt->get_result();
$row = $result->fetch_assoc();

$avg = $row['moyenne']; // ← LA NOTE vient du AVG
   
 // $avg = $avg !== null ? round($avg, 2) : '0';
          
            
            mettreAJourNotationExamen($connexion, $etudiant_id, $ecue, $semestre, $classe, $annee, $type_examen, $avg, $_SESSION['id_user'], $etablissement);
        } else {
            $errors[] = "Étudiant introuvable pour anonymat: $anonymat";
            $error_count++;
        }
    }
    
    // Logger l'action
    $userIP = $_SERVER['REMOTE_ADDR'];
    logUserAction($connexion, $_SESSION['id_user'], "Saisie en masse de notes - $success_count notes", date("Y-m-d H:i:s"), $userIP, "Classe: $classe, ECUE: $ecue, Type: $type_examen");
    
    // Valider la transaction
    $connexion->commit();
    
    // Redirection avec message de succès
    if($error_count > 0) {
        header("location: notation1?sucess=" . urlencode("$success_count notes enregistrées, $error_count erreurs"));
    } else {
        header("location: notation1?sucess=" . urlencode("$success_count notes enregistrées avec succès"));
    }
    exit();
    
} catch (Exception $e) {
    // Annuler la transaction en cas d'erreur
    $connexion->rollback();
    header("location: notation1?erreur=" . urlencode("Erreur: " . $e->getMessage()));
    exit();
}
?>
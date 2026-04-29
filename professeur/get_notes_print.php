<?php 
// Désactiver l'affichage des erreurs pour éviter de corrompre le JSON
error_reporting(0);
ini_set('display_errors', 0);

session_start();
include '../php/connexion.php';
include '../php/lib.php';

header('Content-Type: application/json');

try {
    // Vérifier la session
    if(!isset($_SESSION['id']) || $_SESSION['id'] != session_id() || $_SESSION['role'] != "enseignant") {
        echo json_encode(['success' => false, 'message' => 'Non autorisé']);
        exit();
    }

    // Récupérer les paramètres
    $classe = isset($_GET['classe']) ? $_GET['classe'] : '';
    $ecue = isset($_GET['ecue']) ? $_GET['ecue'] : '';
    $semestre = isset($_GET['semestre']) ? $_GET['semestre'] : '';
    $type_evaluation = isset($_GET['type_evaluation']) ? $_GET['type_evaluation'] : '';
    $annee = isset($_GET['annee']) ? $_GET['annee'] : '';

    // Validation
    if(empty($classe) || empty($ecue) || empty($semestre) || empty($type_evaluation) || empty($annee)) {
        echo json_encode([
            'success' => false, 
            'message' => 'Paramètres manquants',
            'params' => [
                'classe' => $classe,
                'ecue' => $ecue,
                'semestre' => $semestre,
                'type_evaluation' => $type_evaluation,
                'annee' => $annee
            ]
        ]);
        exit();
    }

    // Récupérer les notes avec les informations des étudiants
    $stmt = $connexion->prepare("
        SELECT 
            l.etudiant,
            l.note,
            c.code,
            c.nom,
            c.prenom
        FROM ligne2 l
        JOIN inscription i ON l.etudiant = i.id
        JOIN candidat c ON i.candidat = c.code
        WHERE l.code_ecue = ? 
        AND l.classe = ? 
        AND l.semestre = ? 
        AND l.annee = ? 
        AND l.type = ?
        AND l.etab = ?
        ORDER BY c.nom, c.prenom
    ");
    
    $stmt->bind_param("ssssss", $ecue, $classe, $semestre, $annee, $type_evaluation, $_SESSION['etablissement']);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $notes = [];
    while($row = $result->fetch_assoc()) {
        $nom_prenom = str_replace("+", "'", $row['nom'] . ' ' . $row['prenom']);
        
        $notes[] = [
            'etudiant_id' => $row['etudiant'],
            'matricule' => $row['code'],
            'nom_prenom' => $nom_prenom,
            'note' => $row['note']
        ];
    }
    
    $stmt->close();
    
    echo json_encode([
        'success' => true,
        'notes' => $notes,
        'total' => count($notes)
    ]);
    
} catch(Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Erreur: ' . $e->getMessage()
    ]);
}
?>
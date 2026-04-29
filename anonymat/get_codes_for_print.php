<?php
session_start();
include '../php/connexion.php';
include '../php/lib.php';

header('Content-Type: application/json');

if ($_SESSION['id'] == session_id() && $_SESSION['role'] == "anonymat") {
    
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $ecue = $_POST['ecue'] ?? '';
        $classe = $_POST['classe'] ?? '';
        $semestre = $_POST['semestre'] ?? '';
        $examen = $_POST['examen'] ?? '';
        $annee = $_POST['annee'] ?? '';
        $nature = $_POST['nature'] ?? '';
        $etab = $_SESSION['etablissement'];
        
        
        // Validate inputs
        if (empty($ecue) || empty($classe) || empty($semestre) || empty($examen) || empty($annee) || empty($nature)) {
            echo json_encode([
                'success' => false,
                'message' => 'Tous les champs sont requis'
            ]);
            exit;
        }
        
        // Prepare SQL query
        $sql = "SELECT a.*, a.numero as code_anonyme 
                FROM anonymat a  join inscription i  on a.etudiant=i.id join candidat c on i.candidat=c.code
                WHERE a.ecue = ? 
                AND a.classe = ? 
                AND a.semestre = ? 
                AND a.type = ? 
                AND a.annee = ? 
                AND a.nature=?
                AND a.etab = ? 
                ORDER BY c.nom, c.prenom ASC";
        
        $stmt = $connexion->prepare($sql);
        $stmt->bind_param("sssssss", $ecue, $classe, $semestre, $examen, $annee, $nature,$etab);
        
        if ($stmt->execute()) {
            $result = $stmt->get_result();
            $codes = [];
            
            while ($row = $result->fetch_assoc()) {
                // Get student information
                $codeCandidat = obtenirCodeById($row['etudiant'], $connexion);
                $nomPrenom = obtenirNomPrenom($codeCandidat, $row['annee'], $connexion);
                
                $codes[] = [
                    'id' => $row['id'],
                    'matricule' => $codeCandidat,
                    'nom_prenom' => str_replace("+", "'", $nomPrenom),
                    'code_anonyme' => $row['numero'],
                    'specialite' => $row['specialite']
                ];
            }
            
            echo json_encode([
                'success' => true,
                'codes' => $codes,
                'total' => count($codes)
            ]);
        } else {
            echo json_encode([
                'success' => false,
                'message' => 'Erreur lors de la récupération des codes: ' . $connexion->error
            ]);
        }
        
        $stmt->close();
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'Méthode non autorisée'
        ]);
    }
} else {
    echo json_encode([
        'success' => false,
        'message' => 'Session invalide'
    ]);
}

$connexion->close();
?>
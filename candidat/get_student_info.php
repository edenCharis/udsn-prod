<?php 
include '../php/connexion.php';

header('Content-Type: application/json');

if(isset($_POST['matricule'])) {
    $matricule = mysqli_real_escape_string($connexion, $_POST['matricule']);
    
    // Rechercher l'étudiant et ses informations d'inscription
    $sql = "SELECT 
                i.classe,
                i.annee,
                i.etab,
                c.nom,
                c.prenom
            FROM inscription i
            JOIN candidat c ON c.code = i.candidat
            WHERE c.code = '$matricule'
            ORDER BY i.annee DESC
            LIMIT 1";
    
    $result = $connexion->query($sql);
    
    if($result && $result->num_rows > 0) {
        $data = $result->fetch_assoc();
        echo json_encode([
            'success' => true,
            'data' => $data
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'Aucun étudiant trouvé avec ce matricule'
        ]);
    }
} else {
    echo json_encode([
        'success' => false,
        'message' => 'Matricule non fourni'
    ]);
}
?>
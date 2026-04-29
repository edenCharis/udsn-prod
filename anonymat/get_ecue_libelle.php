<?php
// Include your database connection file
include '../php/connexion.php';
include '../php/lib.php';

header('Content-Type: application/json');
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['code_ecue'])) {
    $code_ecue = $_POST['code_ecue'];
    
    try {
        // Prepare SQL query to get libelle from ecue table (MySQLi version)
        $sql = "SELECT libelle FROM ecue WHERE code_ecue = ? LIMIT 1";
        $stmt = $connexion->prepare($sql);
        $stmt->bind_param('s', $code_ecue);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($row = $result->fetch_assoc()) {
            echo json_encode([
                'success' => true,
                'libelle' => $row['libelle']
            ]);
        } else {
            echo json_encode([
                'success' => false,
                'message' => 'ECUE non trouvé'
            ]);
        }
        
        $stmt->close();
    } catch (Exception $e) {
        echo json_encode([
            'success' => false,
            'message' => 'Erreur de base de données: ' . $e->getMessage()
        ]);
    }
} else {
    echo json_encode([
        'success' => false,
        'message' => 'Paramètres manquants'
    ]);
}
?>
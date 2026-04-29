<?php 
include '../php/connexion.php';
session_start();
if($_SESSION['id'] == session_id() && $_SESSION['role'] == "daarhspe") {
    
    if(isset($_POST['annee'])) {
        $annee = $_POST['annee'];
        
        // Query to get teachers with their contracts for the selected year
        $sql = "SELECT DISTINCT 
                    c.code_unique,
                    e.nom,
                    e.prenom,
                    c.annee
                FROM enseignant e
                 JOIN contrat c ON c.enseignant = e.id
                WHERE c.annee = ?
                ORDER BY e.nom, e.prenom ASC";
        
        $stmt = $connexion->prepare($sql);
        $stmt->bind_param("s", $annee);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if($result->num_rows > 0) {
            // First, collect all rows and identify duplicates
            $rows = [];
            $nameCount = [];
            
            while($row = $result->fetch_assoc()) {
                $rows[] = $row;
                
                // Normalize the name: lowercase, remove accents, trim spaces, remove special chars
                $normalizedNom = strtolower(trim($row['nom']));
                $normalizedPrenom = strtolower(trim($row['prenom']));
                
                // Remove accents
                $normalizedNom = iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $normalizedNom);
                $normalizedPrenom = iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $normalizedPrenom);
                
                // Remove apostrophes, hyphens, and extra spaces
                $normalizedNom = preg_replace("/['\-\s]+/", '', $normalizedNom);
                $normalizedPrenom = preg_replace("/['\-\s]+/", '', $normalizedPrenom);
                
                $fullName = $normalizedNom . '|' . $normalizedPrenom;
                
                if(!isset($nameCount[$fullName])) {
                    $nameCount[$fullName] = 0;
                }
                $nameCount[$fullName]++;
            }
            
            echo '<div class="print-area">';
            echo '<h3>Liste des Enseignants - Année Académique: ' . htmlspecialchars($annee) . '</h3>';
            echo '<p><strong>Date d\'impression:</strong> ' . date('d/m/Y H:i:s') . '</p>';
            echo '<table class="table table-bordered">';
            echo '<thead>';
            echo '<tr>';
            echo '<th>N°</th>';
            echo '<th>Code Unique</th>';
            echo '<th>Nom</th>';
            echo '<th>Prénom</th>';
            echo '<th>Année Académique</th>';
            echo '</tr>';
            echo '</thead>';
            echo '<tbody>';
            
            $counter = 1;
            foreach($rows as $row) {
                // Apply same normalization for checking
                $normalizedNom = strtolower(trim($row['nom']));
                $normalizedPrenom = strtolower(trim($row['prenom']));
                $normalizedNom = iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $normalizedNom);
                $normalizedPrenom = iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $normalizedPrenom);
                $normalizedNom = preg_replace("/['\-\s]+/", '', $normalizedNom);
                $normalizedPrenom = preg_replace("/['\-\s]+/", '', $normalizedPrenom);
                
                $fullName = $normalizedNom . '|' . $normalizedPrenom;
                $isDuplicate = $nameCount[$fullName] > 1;
                $rowClass = $isDuplicate ? ' style="color: red; font-weight: bold;"' : '';
                
                echo '<tr' . $rowClass . '>';
                echo '<td>' . $counter++ . '</td>';
                echo '<td>' . htmlspecialchars($row['code_unique']) . '</td>';
                echo '<td>' . htmlspecialchars(str_replace("+", "'", $row['nom'])) . '</td>';
                echo '<td>' . htmlspecialchars(str_replace("+", "'", $row['prenom'])) . '</td>';
                echo '<td>' . htmlspecialchars($row['annee']) . '</td>';
                echo '</tr>';
            }
            
            echo '</tbody>';
            echo '</table>';
            echo '<p><strong>Total:</strong> ' . ($counter - 1) . ' enseignant(s)</p>';
            echo '</div>';
        } else {
            echo '<div class="alert alert-info">Aucun enseignant trouvé pour l\'année académique sélectionnée.</div>';
        }
        
        $stmt->close();
    } else {
        echo '<div class="alert alert-danger">Année académique non spécifiée.</div>';
    }
    
} else {
    echo '<div class="alert alert-danger">Accès non autorisé.</div>';
}
$connexion->close();
?>
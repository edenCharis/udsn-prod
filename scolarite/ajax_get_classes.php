<?php
include '../php/connexion.php';

header('Content-Type: text/html; charset=utf-8');

if(isset($_POST['specialite']) && !empty($_POST['specialite'])) {
    $specialite = $connexion->real_escape_string($_POST['specialite']);
    
    $sql = "SELECT DISTINCT libelle FROM classe WHERE specialite = '$specialite' ORDER BY libelle";
    $result = $connexion->query($sql);
    
    if($result) {
        echo '<option value="">-- Sélectionner une classe --</option>';
        
        if($result->num_rows > 0) {
            while($row = $result->fetch_object()) {
                echo '<option value="'.htmlspecialchars($row->libelle).'">'.htmlspecialchars($row->libelle).'</option>';
            }
        } else {
            echo '<option value="">Aucune classe trouvée</option>';
        }
    } else {
        echo '<option value="">Erreur de requête</option>';
    }
} else {
    echo '<option value="">-- Sélectionner une classe --</option>';
}
?>
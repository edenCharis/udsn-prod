<?php
include '../php/connexion.php';

header('Content-Type: text/html; charset=utf-8');

if(isset($_POST['parcours']) && !empty($_POST['parcours'])) {
    $parcours = $connexion->real_escape_string($_POST['parcours']);
    
    $sql = "SELECT DISTINCT libelle FROM specialite WHERE parcours = '$parcours' ORDER BY libelle";
    $result = $connexion->query($sql);
    
    if($result) {
        echo '<option value="">-- Sélectionner une spécialité --</option>';
        
        if($result->num_rows > 0) {
            while($row = $result->fetch_object()) {
                echo '<option value="'.htmlspecialchars($row->libelle).'">'.htmlspecialchars($row->libelle).'</option>';
            }
        } else {
            echo '<option value="">Aucune spécialité trouvée</option>';
        }
    } else {
        echo '<option value="">Erreur de requête</option>';
    }
} else {
    echo '<option value="">-- Sélectionner une spécialité --</option>';
}
?>
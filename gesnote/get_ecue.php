<?php
include '../php/connexion.php';
include '../php/lib.php';


// Récupérer l'identifiant de la classe depuis la requête GET
$classeId = $_REQUEST['classe'];

// Préparer la requête SQL pour récupérer les écueils correspondants à la classe sélectionnée
$sql = "SELECT ecue FROM repartition WHERE classe ='$classeId' and etab='".$_SESSION['etablissement']."'";

// Exécuter la requête SQL
$result = $connexion->query($sql);

// Construire les options HTML pour les écueils

if($result->num_rows > 0){
    $options = "<option value=''>Sélectionnez un écue</option>";
    while ($row = $result->fetch_assoc()) {
        $options .= "<option>" . $row['ecue'] . "</option>";
    }
    
    
    
    // Renvoyer les options HTML
    echo $options;
}else{
    echo $connexion->error;
}



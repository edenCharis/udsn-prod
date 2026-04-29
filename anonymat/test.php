<?php

// Fonction pour récupérer la spécialité associée à une classe
function recupererSpecialiteParClasse($nomClasse, $connexion) {
    // Préparation de la requête SQL avec un paramètre
    $requete = $connexion->prepare("SELECT specialite FROM classes WHERE libelle = ?");
    $specialite =null;
    
    // Liaison des valeurs aux paramètres de la requête
    $requete->bind_param("s", $nomClasse);
    
    // Exécution de la requête
    $requete->execute();
    
    // Liaison du résultat de la requête à une variable
    $requete->bind_result($specialite);
    
    // Récupération du résultat
    $requete->fetch();
    
 
    
    // Retourne la spécialité associée à la classe
    return $specialite;
}

?>

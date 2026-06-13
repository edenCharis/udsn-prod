<?php
/**
 * Helper pour récupérer et définir le nom de l'université dans la session
 * 
 * À utiliser dans php/routeur.php lors de la connexion
 * 
 * Usage:
 * $_SESSION['nom_univ'] = getUniversityNameFromCode($univ_code, $connexion);
 */

/**
 * Récupérer le nom de l'université pour un code donné
 * 
 * @param int $univ_code Code de l'université
 * @param mysqli $connexion Connexion à la base de données
 * @return string Nom de l'université
 */
function getUniversityNameFromCode($univ_code, $connexion) {
    $default_name = "UNIVERSITE DENIS SASSOU-N'GUESSO";
    
    if (!$connexion || empty($univ_code)) {
        return $default_name;
    }

    $sql = "SELECT nom FROM univ WHERE code = ?";
    $stmt = $connexion->prepare($sql);
    
    if ($stmt) {
        $stmt->bind_param("i", $univ_code);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result && $row = $result->fetch_assoc()) {
            $nom = !empty($row['nom']) ? $row['nom'] : $default_name;
            $stmt->close();
            return $nom;
        }
        $stmt->close();
    }

    return $default_name;
}

/**
 * Définir le nom de l'université dans la session lors de la connexion
 * 
 * À ajouter dans php/routeur.php après avoir défini $_SESSION['univ']
 * 
 * Exemple:
 *   $_SESSION['univ'] = $univ;
 *   $_SESSION['nom_univ'] = getUniversityNameFromCode($univ, $connexion);
 */

?>

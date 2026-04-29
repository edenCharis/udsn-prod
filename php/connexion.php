<?php

$host = 'localhost'; // Hôte de la base de données (souvent 'localhost')
$dbname = 'u312437937_udsn2025'; // Nom de la base de données
$username = 'u312437937_udsn2025'; // Nom d'utilisateur de la base de données
$password ='*Qr*kD;Zi8~Y'; // Mot de passe de la base de données

// Connexion à la base de données
$connexion = new mysqli($host, $username, $password, $dbname);

// Vérification de la connexion
if ($connexion->connect_error) {
    die("Erreur de connexion à la base de données: " . $conn->connect_error);
}

try {
    // Création de la connexion PDO
    $dsn = "mysql:host=$host;dbname=$dbname;charset=utf8mb4";
    $connexion1 = new PDO($dsn, $username, $password);

    // Configuration des attributs PDO
    $connexion1->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION); // Gestion des erreurs par exceptions
    $connexion1->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC); // Mode de récupération par défaut (associatif)

   

} catch (PDOException $e) {
    // Gestion des erreurs de connexion
    die("Erreur de connexion à la base de données: " . $e->getMessage());
}



?>

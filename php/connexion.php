<?php

// Load .env file if present (local dev or manual upload on prod)
$envFile ='.env';
if (file_exists($envFile)) {
    $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (strpos(trim($line), '#') === 0) continue;
        if (strpos($line, '=') !== false) {
            [$key, $value] = explode('=', $line, 2);
            $_ENV[trim($key)] = trim($value);
            putenv(trim($key) . '=' . trim($value));
        }
    }
}

$host     = 'localhost';
$dbname   = 'u313479847_cetup';
$username ='u313479847_cetup_user';
$password = '5G>CDh#Ns|4n';

$connexion = new mysqli($host, $username, $password, $dbname);

if ($connexion->connect_error) {
    die("Erreur de connexion à la base de données: " . $connexion->connect_error);
}

try {
    $dsn = "mysql:host=$host;dbname=$dbname;charset=utf8mb4";
    $connexion1 = new PDO($dsn, $username, $password);
    $connexion1->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $connexion1->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Erreur de connexion à la base de données: " . $e->getMessage());
}

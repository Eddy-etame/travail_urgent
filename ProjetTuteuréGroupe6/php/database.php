<?php
// php/database.php
$host = 'localhost'; // Remplacez par votre hôte de base de données
$db   = 'project'; // Nom de votre base de données
$user = 'eddy'; // Votre nom d'utilisateur MySQL
$pass = 'Daddiesammy1$'; // Votre mot de passe MySQL

try {
    $pdo = new PDO("mysql:host=$host;dbname=$db;charset=utf8", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Erreur de connexion à la base de données : " . $e->getMessage());
}
?>
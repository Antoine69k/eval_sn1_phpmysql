<?php
// La connexion est partagée par toutes les pages (variable $dbh demandée dans le cours).
session_start();
try {
    $dbh = new PDO('mysql:host=127.0.0.1;dbname=agenda;charset=utf8mb4', 'root', '', [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
    ]);
} catch (PDOException $e) {
    die('Connexion à MySQL impossible. Vérifiez que WAMP est vert et que la base agenda est importée.');
}
require_once __DIR__ . '/fonctions.php';

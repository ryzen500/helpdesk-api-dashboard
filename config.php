<?php
// Koneksi ke database
$host = 'localhost';
$dbname = 'db_helpdesk';
$user = 'root';
$password = 'Shinigami_145';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}
?>

<?php
// src/db.php
$host = '127.0.0.1';
$user = 'root';
$pass = 'rootpass';
$db   = 'sombra_db';

$conn = new mysqli($host, $user, $pass, $db);

if ($conn->connect_error) {
    die("Error de conexión: " . $conn->connect_error);
}
?>

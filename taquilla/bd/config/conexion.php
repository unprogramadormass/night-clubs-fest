<?php
$host = "localhost";
$user = "root";
$pass = ""; // Por defecto en XAMPP está vacío
$db   = "taquilla_db";

$conn = new mysqli($host, $user, $pass, $db);

if ($conn->connect_error) {
    die("Conexión fallida: " . $conn->connect_error);
}
?>
<?php
// db.php
$host = 'localhost';
$db   = 'taquillera'; // Asegúrate que este nombre coincida con tu phpMyAdmin
$user = 'root';
$pass = ''; 
$charset = 'utf8mb4';

$dsn = "mysql:host=$host;dbname=$db;charset=$charset";
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

try {
    $pdo = new PDO($dsn, $user, $pass, $options);
} catch (\PDOException $e) {
    // Si falla la conexión, enviamos un JSON de error para que el JS lo entienda
    echo json_encode(['status' => 'error', 'message' => 'Error de conexión BD: ' . $e->getMessage()]);
    exit;
}
?>
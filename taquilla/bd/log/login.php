<?php
header('Content-Type: application/json');
require '../config/conexion.php';

$data = json_decode(file_get_contents("php://input"));

if(isset($data->email)) {
    $email = $data->email;
    $password = $data->password;

    $sql = "SELECT * FROM usuarios WHERE email = '$email'";
    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();
        // Verificar la contraseña encriptada
        if (password_verify($password, $user['password'])) {
            // Login exitoso, devolvemos datos del usuario (menos la contraseña)
            echo json_encode([
                "success" => true, 
                "user" => [
                    "nombre" => $user['nombre'],
                    "apellido" => $user['apellido'],
                    "email" => $user['email']
                ]
            ]);
        } else {
            echo json_encode(["success" => false, "message" => "Contraseña incorrecta"]);
        }
    } else {
        echo json_encode(["success" => false, "message" => "Usuario no encontrado"]);
    }
}
$conn->close();
?>
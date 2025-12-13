<?php
// 1. LOS "USE" VAN SIEMPRE AL INICIO ABSOLUTO
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
use PHPMailer\PHPMailer\SMTP;

// Configuración para que PHP no ensucie la respuesta JSON con alertas
error_reporting(0);
ini_set('display_errors', 0);
header('Content-Type: application/json; charset=utf-8');

try {
    // 2. VERIFICAR Y CARGAR LIBRERÍAS
    // Ajusta estas rutas si tus archivos están en carpetas diferentes
    $pathException = 'PHPMailer/Exception.php';
    $pathPHPMailer = 'PHPMailer/PHPMailer.php';
    $pathSMTP      = 'PHPMailer/SMTP.php';

    if (!file_exists($pathException)) throw new Exception("Falta el archivo: $pathException");
    if (!file_exists($pathPHPMailer)) throw new Exception("Falta el archivo: $pathPHPMailer");
    if (!file_exists($pathSMTP))      throw new Exception("Falta el archivo: $pathSMTP");

    require $pathException;
    require $pathPHPMailer;
    require $pathSMTP;

    // 3. CONEXIÓN A BASE DE DATOS
    // Si register.php está en 'bd/log/', para ir a 'bd/config/' subimos un nivel (..)
    if (!file_exists('../config/conexion.php')) {
        throw new Exception("No se encuentra ../config/conexion.php");
    }
    require '../config/conexion.php';

    // 4. RECIBIR DATOS DEL FRONTEND
    $input = file_get_contents("php://input");
    $data = json_decode($input);

    if (!isset($data->email)) {
        throw new Exception("No se recibieron datos del formulario.");
    }

    $nombre = $conn->real_escape_string($data->nombre);
    $apellido = $conn->real_escape_string($data->apellido);
    $email = $conn->real_escape_string($data->email);
    $password = $data->password;

    // 5. VERIFICAR SI EL CORREO YA EXISTE
    $checkEmail = $conn->query("SELECT email FROM usuarios WHERE email = '$email'");
    if ($checkEmail->num_rows > 0) {
        echo json_encode(["success" => false, "message" => "El correo ya está registrado"]);
        exit;
    }

    // 6. GUARDAR EN BASE DE DATOS
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);
    $sql = "INSERT INTO usuarios (nombre, apellido, email, password) VALUES ('$nombre', '$apellido', '$email', '$hashed_password')";


    if ($conn->query($sql) === TRUE) {
        
        // 7. ENVIAR CORREO
        $mail = new PHPMailer(true);

        try {
            // Configuración del servidor Gmail
            $mail->isSMTP();
            $mail->Host       = 'smtp.gmail.com';
            $mail->SMTPAuth   = true;
            $mail->Username   = 'helpdeskcpsp@gmail.com'; 
            $mail->Password   = 'xdsvmcxizlywhxmn'; // <--- OJO: Recuerda poner la nueva
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
            $mail->Port       = 465;

            // Destinatarios
            $mail->setFrom('helpdeskcpsp@gmail.com', 'UrbanFest Club');
            $mail->addAddress($email, "$nombre $apellido");

            // --- DISEÑO DEL CORREO (HTML EMAIL TEMPLATE) ---
            
            // Definimos la URL de tu sitio (En local es localhost, en producción será tu dominio)
            $siteUrl = 'http://localhost/nigtclub/taquilla/index.html';
            
            // NOTA: Las imágenes en correos deben estar en internet, no en localhost.
            // Para este ejemplo usaré imágenes de stock, pero cuando subas tu web, cambia estas URL por las tuyas.
            $logoUrl = 'https://i.ibb.co/YFVD7yjd/logo.png'; // Icono Negro
            $bannerUrl = 'https://i.ibb.co/gFg86Pmx/urbanfest.webp'; 

            $mailContent = "
            <!DOCTYPE html>
            <html>
            <head>
                <meta charset='UTF-8'>
            </head>
            <body style='margin:0; padding:0; background-color:#0f172a; color:#f8fafc; font-family:Arial, Helvetica, sans-serif;'>

            <table width='100%' cellpadding='0' cellspacing='0' border='0' style='background-color:#0f172a;'>
                <tr>
                    <td align='center' style='padding:20px; background-color:#0f172a;'>

                        <table width='600' cellpadding='0' cellspacing='0' border='0'
                            style='background-color:#18181b; border:1px solid #3f3f46; border-radius:10px; max-width:100%;'>

                            <!-- HEADER -->
                            <tr>
                                <td align='center' style='padding:30px 20px; background-color:#0f172a; border-bottom:1px solid #3f3f46;'>
                                    <table cellpadding='0' cellspacing='0' border='0'>
                                        <tr>
                                            <td valign='middle'>
                                                <img src='$logoUrl' alt='Logo' width='40' style='display:block; margin-right:10px;'>
                                            </td>
                                            <td valign='middle'>
                                                <span style='font-size:24px; font-weight:900; color:#f8fafc; text-transform:uppercase;'>
                                                    UrbanFest <span style='color:#9ca3af;'>Club</span>
                                                </span>
                                            </td>
                                        </tr>
                                    </table>
                                </td>
                            </tr>

                            <!-- BANNER -->
                            <tr>
                                <td style='background-color:#0f172a;'>
                                    <img src='$bannerUrl' alt='Banner'
                                        style='display:block; width:100%; height:auto; max-height:250px;'>
                                </td>
                            </tr>

                            <!-- CONTENT -->
                            <tr>
                                <td align='center' style='padding:40px 30px; background-color:#18181b;'>

                                    <h1 style='margin:0 0 20px 0; font-size:26px; font-weight:bold; color:#f8fafc;'>
                                        ¡Bienvenido, $nombre!
                                    </h1>

                                    <p style='margin:0 0 20px 0; font-size:16px; line-height:1.6; color:#e5e7eb;'>
                                        Tu cuenta ha sido creada exitosamente. Ya eres parte de la comunidad más exclusiva de la vida nocturna.
                                    </p>

                                    <p style='margin:0 0 30px 0; font-size:14px; line-height:1.6; color:#d1d5db;'>
                                        Accede ahora para reservar suites, comprar boletos anticipados y conocer próximos eventos antes que nadie.
                                    </p>

                                    <!-- BOTÓN -->
                                    <a href='$siteUrl'
                                    style='display:inline-block; background-color:#9ca3af; color:#ffffff;
                                            font-size:14px; font-weight:bold; text-decoration:none;
                                            padding:14px 38px; border-radius:6px; text-transform:uppercase;'>
                                        Iniciar sesión
                                    </a>

                                </td>
                            </tr>

                            <!-- FOOTER -->
                            <tr>
                                <td align='center' style='padding:30px; background-color:#18181b; border-top:1px solid #3f3f46;'>

                                    <p style='margin:0 0 10px 0; font-size:12px; color:#9ca3af; text-transform:uppercase;'>
                                        Síguenos en redes
                                    </p>

                                    <p style='margin:0 0 20px 0;'>
                                        <a href='#' style='color:#f8fafc; text-decoration:none; margin:0 10px;'>Instagram</a>
                                        <a href='#' style='color:#f8fafc; text-decoration:none; margin:0 10px;'>TikTok</a>
                                        <a href='#' style='color:#f8fafc; text-decoration:none; margin:0 10px;'>Facebook</a>
                                    </p>

                                    <p style='margin:0; font-size:11px; color:#9ca3af;'>
                                        Blvd. Gral. Marcelino García Barragán 1671, Guadalajara, Jal.<br>
                                        © 2025 UrbanFest Club. Todos los derechos reservados.
                                    </p>

                                </td>
                            </tr>

                        </table>

                    </td>
                </tr>
            </table>

            </body>
            </html>
            ";


            $mail->isHTML(true);
            $mail->CharSet = 'UTF-8';
            $mail->Subject = 'Bienvenido a la Experiencia UrbanFest';
            $mail->Body    = $mailContent;
            
            // Texto plano para clientes antiguos
            $mail->AltBody = "Hola $nombre, bienvenido a UrbanFest Club. Tu cuenta fue creada. Inicia sesión en nuestra web.";

            $mail->send();
            
            echo json_encode(["success" => true, "message" => "Usuario registrado y correo enviado"]);

        } catch (Exception $e) {
            echo json_encode(["success" => true, "message" => "Registrado, pero error al enviar correo: " . $mail->ErrorInfo]);
        }

    } else {
        throw new Exception("Error BD: " . $conn->error);
    }

    $conn->close();

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(["success" => false, "message" => $e->getMessage()]);
}
?>
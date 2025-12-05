<?php
// save_venue.php
header('Content-Type: application/json');

// CORRECCIÓN AQUÍ: Llamamos al archivo que está AL LADO (en la misma carpeta)
require_once 'db.php'; 

// ... (El resto de tu código sigue igual hacia abajo)
$input = file_get_contents('php://input');
$data = json_decode($input, true);

if (!$data) {
    echo json_encode(['status' => 'error', 'message' => 'Datos inválidos']);
    exit;
}

try {
    // Preparar la consulta SQL
    $sql = "INSERT INTO venues (name, total_capacity, rows_count, cols_count, layout_data) VALUES (?, ?, ?, ?, ?)";
    $stmt = $pdo->prepare($sql);
    
    // Convertir el objeto 'layers' de nuevo a string JSON para guardarlo en la columna layout_data
    $jsonLayout = json_encode($data['layers']); 

    $stmt->execute([
        $data['name'], 
        $data['capacity'], 
        $data['dimensions']['rows'], 
        $data['dimensions']['cols'], 
        $jsonLayout
    ]);

    echo json_encode(['status' => 'success', 'message' => 'Recinto guardado correctamente', 'id' => $pdo->lastInsertId()]);

} catch (Exception $e) {
    echo json_encode(['status' => 'error', 'message' => 'Error SQL: ' . $e->getMessage()]);
}
?>
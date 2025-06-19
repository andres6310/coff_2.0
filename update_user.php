<?php
session_start();
require 'conexion.php';

header('Content-Type: application/json');

if (!isset($_SESSION['usuario_id'])) {
    echo json_encode(['success' => false, 'message' => 'Usuario no autenticado']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Método no permitido']);
    exit;
}

if (!isset($_POST['user_name']) || empty(trim($_POST['user_name']))) {
    echo json_encode(['success' => false, 'message' => 'Nombre de usuario inválido']);
    exit;
}

$user_name = trim($_POST['user_name']);
$user_name = htmlspecialchars($user_name, ENT_QUOTES, 'UTF-8');

$usuario_id = $_SESSION['usuario_id'];

if ($conexion instanceof mysqli) {
    $stmt = $conexion->prepare("UPDATE usuarios SET nombre = ? WHERE id = ?");
    if (!$stmt) {
        echo json_encode(['success' => false, 'message' => 'Error en la consulta: ' . $conexion->error]);
        exit;
    }
    $stmt->bind_param("si", $user_name, $usuario_id);
    if ($stmt->execute()) {
        echo json_encode(['success' => true, 'message' => 'Nombre actualizado correctamente', 'new_name' => $user_name]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Error al actualizar el nombre']);
    }
    $stmt->close();
} else {
    echo json_encode(['success' => false, 'message' => 'Conexión a base de datos no válida']);
}
?>

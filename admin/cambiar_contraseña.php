<?php
require __DIR__ . '/../conexion.php';

// Cambiar la contraseña de un usuario dado su email
$email = 'andres@example.com';
$nueva_contraseña = '123456'; // Cambia esta contraseña por la que desees

// Generar hash de la nueva contraseña
$hashed_password = password_hash($nueva_contraseña, PASSWORD_BCRYPT);

// Actualizar la contraseña en la base de datos
$stmt = $conexion->prepare("UPDATE usuarios SET password = ? WHERE email = ?");
if (!$stmt) {
    die("Error en la preparación de la consulta: " . $conexion->error);
}
$stmt->bind_param("ss", $hashed_password, $email);

if ($stmt->execute()) {
    if ($stmt->affected_rows > 0) {
        echo "Contraseña actualizada correctamente para el usuario: $email";
    } else {
        echo "No se encontró ningún usuario con el correo: $email";
    }
} else {
    echo "Error al actualizar la contraseña: " . $stmt->error;
}
?>

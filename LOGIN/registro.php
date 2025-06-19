<?php
session_start();
require '../conexion.php';

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nombre = limpiarDatos($_POST['nombre']);
    $email = limpiarDatos($_POST['email']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];

    // Validaciones
    if (empty($nombre) || empty($email) || empty($password)) {
        $error = 'Todos los campos son obligatorios';
    } elseif ($password !== $confirm_password) {
        $error = 'Las contraseñas no coinciden';
    } elseif (strlen($password) < 8) {
        $error = 'La contraseña debe tener al menos 8 caracteres';
    } else {
        // Verificar si el email ya existe
        $stmt = $conexion->prepare("SELECT id FROM usuarios WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $stmt->store_result();
        
        if ($stmt->num_rows > 0) {
            $error = 'El correo electrónico ya está registrado';
        } else {
            // Registrar usuario
            $hashed_password = password_hash($password, PASSWORD_BCRYPT);
            $stmt = $conexion->prepare("INSERT INTO usuarios (nombre, email, password) VALUES (?, ?, ?)");
            $stmt->bind_param("sss", $nombre, $email, $hashed_password);
            
            if ($stmt->execute()) {
                $success = 'Registro exitoso. Ahora puedes iniciar sesión.';
            } else {
                $error = 'Error al registrar el usuario';
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Registro de Usuario</title>
    <link rel="stylesheet" href="stile.css">
    <link rel="stylesheet" href="stile.css" />
    <link rel="icon" href="../img/favicon_round.png" type="image/x-icon" />
</head>
<body>
    <div class="login-container">
        <h2>Registro</h2>
        <?php if(!empty($error)) echo "<p class='error'>$error</p>"; ?>
        <?php if(!empty($success)) echo "<p class='success'>$success</p>"; ?>
        <form method="POST">
            <input type="text" name="nombre" placeholder="Nombre completo" required>
            <input type="email" name="email" placeholder="Correo electrónico" required>
            <input type="password" name="password" placeholder="Contraseña (mínimo 8 caracteres)" required>
            <input type="password" name="confirm_password" placeholder="Confirmar contraseña" required>
            <button type="submit">Registrarse</button>
        </form>
        <p>¿Ya tienes cuenta? <a href="login.php">Inicia sesión aquí</a></p>
    </div>
</body>
</html>

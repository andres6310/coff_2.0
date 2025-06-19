<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();
require '../conexion.php';

// Solo define la función si no existe
if (!function_exists('limpiarDatos')) {
    function limpiarDatos($datos) {
        $datos = trim($datos);
        $datos = stripslashes($datos);
        $datos = htmlspecialchars($datos);
        return $datos;
    }
}

$error_login = '';
$error_registro = '';
$registro_exitoso = '';

// Función para limpiar datos (que no estaba definida en el código original)
function limpiarDatos($datos) {
    $datos = trim($datos);
    $datos = stripslashes($datos);
    $datos = htmlspecialchars($datos);
    return $datos;
}

$error_login = '';
$error_registro = '';
$registro_exitoso = '';

// Procesar formulario de inicio de sesión
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['login'])) {
    $email = limpiarDatos($_POST['email']);
    $password = $_POST['password'];
    
    $stmt = $conexion->prepare("SELECT id, nombre, password, rol FROM usuarios WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $usuario = $result->fetch_assoc();
        error_log("Usuario encontrado: " . print_r($usuario, true));
        if (password_verify($password, $usuario['password'])) {
            $_SESSION['usuario_id'] = $usuario['id'];
            $_SESSION['usuario_nombre'] = $usuario['nombre'];
            $_SESSION['usuario_rol'] = $usuario['rol']; // Guardar el rol en sesión
            if ($usuario['rol'] === 'admin') {
                header("Location: ../admin/index.php");
            } else {
                header("Location: ../index.php");
            }
            exit();
        } else {
            error_log("La verificación de la contraseña falló.");
        }
    } else {
        error_log("No se encontró usuario con ese email.");
    }
    $error_login = "Credenciales incorrectas";
}

// Procesar formulario de registro
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['registro'])) {
    $nombre = limpiarDatos($_POST['nombre']);
    $email = limpiarDatos($_POST['email']);
    $password = $_POST['password'];
    
    // Verificar si el email ya existe
    $stmt = $conexion->prepare("SELECT id FROM usuarios WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $error_registro = "Este correo electrónico ya está registrado";
    } else {
        // Hash de la contraseña
        $password_hash = password_hash($password, PASSWORD_DEFAULT);
        
        // Insertar nuevo usuario
        $stmt = $conexion->prepare("INSERT INTO usuarios (nombre, email, password) VALUES (?, ?, ?)");
        $stmt->bind_param("sss", $nombre, $email, $password_hash);
        
        if ($stmt->execute()) {
            $registro_exitoso = "¡Registro exitoso! Ahora puedes iniciar sesión.";
        } else {
            $error_registro = "Error al registrar el usuario: " . $conexion->error;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <link rel="stylesheet" href="stile.css">
    <title>Login - Coffe Galactic</title>
    <link rel="icon" href="../img/favicon_round.png" type="image/x-icon" />
</head>
<body>
    <div class="container" id="container">
        <div class="form-container sign-up">
            <form method="POST" action="">
                <h1>Crear una cuenta</h1>
                <div class="social-icons">
                    <a href="#" class="icon"><i class="fa-brands fa-google-plus-g"></i></a>
                    <a href="#" class="icon"><i class="fa-brands fa-facebook-f"></i></a>
                    <a href="#" class="icon"><i class="fa-brands fa-github"></i></a>
                    <a href="#" class="icon"><i class="fa-brands fa-linkedin-in"></i></a>
                </div>
                <span>o use su correo electrónico para registrarse</span>
                <?php if($error_registro): ?>
                    <p class="error-message"><?php echo $error_registro; ?></p>
                <?php endif; ?>
                <?php if($registro_exitoso): ?>
                    <p class="success-message"><?php echo $registro_exitoso; ?></p>
                <?php endif; ?>
                <input type="text" name="nombre" placeholder="Nombre" required>
                <input type="email" name="email" placeholder="Email" required>
                <input type="password" name="password" placeholder="Password" required>
                <button name="registro" value="1">Registrarse</button>
            </form>
        </div>
        <div class="form-container sign-in">
            <form method="POST" action="">
                <h1>Iniciar sesión</h1>
                <div class="social-icons">
                    <a href="#" class="icon"><i class="fa-brands fa-google-plus-g"></i></a>
                    <a href="#" class="icon"><i class="fa-brands fa-facebook-f"></i></a>
                    <a href="#" class="icon"><i class="fa-brands fa-instagram"></i></a>
                    <a href="#" class="icon"><i class="fa-brands fa-linkedin-in"></i></a>
                </div>
                <?php if($error_login): ?>
                    <p class="error-message"><?php echo $error_login; ?></p>
                <?php endif; ?>
                <input type="email" name="email" placeholder="Email" required>
                <input type="password" name="password" placeholder="Password" required>
                <a href="#">¿Olvidaste tu contraseña?</a>
                <button name="login" value="1">Iniciar Sesión</button>
            </form>
        </div>
        <div class="toggle-container">
            <div class="toggle">
                <div class="toggle-panel toggle-left">
                    <h1>¡Bienvenido!</h1>
                    <p>Introduce tus datos personales para utilizar todas las funciones del sitio</p>
                    <button class="hidden" id="login">INICIA SESIÓN</button>
                </div>
                <div class="toggle-panel toggle-right">
                    <h1>¡Hola!</h1>
                    <p>Regístrese con sus datos personales para utilizar todas las funciones del sitio</p>
                    <button class="hidden" id="register">ÚNETE</button>
                </div>
            </div>
        </div>
    </div>

    <script src="login.js"></script>
</body>
</html>

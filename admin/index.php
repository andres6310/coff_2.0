<?php
session_start();
require '../conexion.php';


if (!isset($_SESSION['usuario_id']) || !isset($_SESSION['usuario_rol']) || $_SESSION['usuario_rol'] !== 'admin') {
    header('Location: ../LOGIN/login.php');
    exit;
}


$nombre_usuario = '';


?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Panel de Administración - Coffee Galactic</title>
    <link rel="stylesheet" href="../style.css" />
    <link rel="stylesheet" href="admin-style.css" />
    <link rel="icon" href="../img/favicon_round.png" type="image/x-icon" />
</head>
<body>
    <header>
        <h1>Panel de Administración - Coffee Galactic</h1>
        <p>Bienvenido, administrador</p>
    </header>
    <?php $currentPage = basename($_SERVER['PHP_SELF']); ?>
    <nav>
        <a href="index.php" class="<?php echo ($currentPage == 'index.php') ? 'active' : ''; ?>">Inicio</a>
        <a href="productos.php" class="<?php echo ($currentPage == 'productos.php') ? 'active' : ''; ?>">Gestión de Productos</a>
        <a href="pedidos.php" class="<?php echo ($currentPage == 'pedidos.php') ? 'active' : ''; ?>">Gestión de Pedidos</a>
        <a href="usuarios.php" class="<?php echo ($currentPage == 'usuarios.php') ? 'active' : ''; ?>">Gestión de Usuarios</a>
        <span class="user-info" style="margin-left: 10px; font-weight: bold;">
            
            <?php echo htmlspecialchars($nombre_usuario); ?>
        </span>
        <a href="../LOGIN/logout.php">Cerrar Sesión</a>
    </nav>
    <main>
        <h2>Vista Previa del Sitio</h2>
        <iframe src="../index.php" ></iframe>
    </main>
</body>
</html>

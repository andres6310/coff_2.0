<?php
session_start();
require '../conexion.php';

// Verificar si el usuario está autenticado y es administrador
if (!isset($_SESSION['usuario_id']) || !isset($_SESSION['usuario_rol']) || $_SESSION['usuario_rol'] !== 'admin') {
    header('Location: ../LOGIN/login.php');
    exit;
}

// Obtener pedidos
$resultado = $conexion->query("SELECT * FROM pedidos ORDER BY id DESC");
$pedidos = $resultado->fetch_all(MYSQLI_ASSOC);

// Evitar error undefined array key 'fecha' verificando si existe la clave antes de mostrarla

function limpiarDatos($datos) {
    $datos = trim($datos);
    $datos = stripslashes($datos);
    $datos = htmlspecialchars($datos);
    return $datos;
}
?>
<!DOCTYPE html>
<html lang="es">
    <head>
        <meta charset="UTF-8" />
        <meta http-equiv="X-UA-Compatible" content="IE=edge" />
        <meta name="viewport" content="width=device-width, initial-scale=1.0" />
        <title>Gestión de Pedidos - Panel de Administración</title>
        <link href="https://fonts.googleapis.com/css2?family=Poppins&display=swap" rel="stylesheet" />
        <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet" />
        <link rel="stylesheet" href="admin-style.css" />
        <link rel="icon" type="image/x-icon" href="../img/favicon_round.png" />
        <style>
            body {
                font-family: 'Poppins', sans-serif;
            }
        </style>
    </head>
<body class="bg-gray-50 font-poppins min-h-screen flex flex-col">
    <header class="bg-gradient-to-r from-yellow-900 via-yellow-700 to-yellow-600 text-white p-6 text-center shadow-md">
        <h1 class="text-3xl font-bold tracking-wide">Gestión de Pedidos</h1>
    </header>
<?php $currentPage = basename($_SERVER['PHP_SELF']); ?>
    <?php $currentPage = basename($_SERVER['PHP_SELF']); ?>
    <nav class="bg-yellow-900 shadow-inner">
        <a href="index.php" class="text-yellow-100 px-6 py-4 font-semibold hover:bg-yellow-700 hover:text-yellow-300 transition <?php echo ($currentPage == 'index.php') ? 'bg-yellow-700 text-yellow-300' : ''; ?>">Inicio</a>
        <a href="productos.php" class="text-yellow-100 px-6 py-4 font-semibold hover:bg-yellow-700 hover:text-yellow-300 transition <?php echo ($currentPage == 'productos.php') ? 'bg-yellow-700 text-yellow-300' : ''; ?>">Gestión de Productos</a>
        <a href="pedidos.php" class="text-yellow-100 px-6 py-4 font-semibold hover:bg-yellow-700 hover:text-yellow-300 transition <?php echo ($currentPage == 'pedidos.php') ? 'bg-yellow-700 text-yellow-300' : ''; ?>">Gestión de Pedidos</a>
        <a href="usuarios.php" class="text-yellow-100 px-6 py-4 font-semibold hover:bg-yellow-700 hover:text-yellow-300 transition <?php echo ($currentPage == 'usuarios.php') ? 'bg-yellow-700 text-yellow-300' : ''; ?>">Gestión de Usuarios</a>
        <a href="../LOGIN/logout.php" class="text-yellow-100 px-6 py-4 font-semibold hover:bg-yellow-700 hover:text-yellow-300 transition">Cerrar Sesión</a>
    </nav>
    <main class="flex-grow container mx-auto p-6">
        <table>
            <thead>
                <tr>
                    <th>ID Pedido</th>
                    <th>ID Usuario</th>
                    <th>Fecha</th>
                    <th>Estado</th>
                    <th>Total</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($pedidos as $pedido): ?>
                <tr>
                    <td><?php echo $pedido['id']; ?></td>
                    <td><?php echo $pedido['usuario_id']; ?></td>
                    <td><?php echo isset($pedido['fecha']) ? $pedido['fecha'] : 'N/A'; ?></td>
                    <td><?php echo htmlspecialchars($pedido['estado'], ENT_QUOTES); ?></td>
                    <td>$<?php echo number_format($pedido['total'], 2, ',', '.'); ?></td>
                    <td class="actions">
                        <!-- Aquí se pueden agregar acciones como ver detalles, cambiar estado, eliminar -->
                        <a href="pedido_detalle.php?id=<?php echo $pedido['id']; ?>">Ver Detalle</a>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </main>
</body>
</html>

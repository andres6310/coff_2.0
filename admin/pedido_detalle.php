<?php
session_start();
require '../conexion.php';

// Verificar si el usuario está autenticado y es administrador
if (!isset($_SESSION['usuario_id']) || !isset($_SESSION['usuario_rol']) || $_SESSION['usuario_rol'] !== 'admin') {
    header('Location: ../LOGIN/login.php');
    exit;
}

$id = $_GET['id'] ?? '';

if (!$id) {
    header('Location: pedidos.php');
    exit;
}

// Obtener detalles del pedido
$stmt = $conexion->prepare("SELECT * FROM pedidos WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$resultado = $stmt->get_result();
$pedido = $resultado->fetch_assoc();
$stmt->close();

if (!$pedido) {
    header('Location: pedidos.php');
    exit;
}

// Obtener detalles de los productos del pedido
$stmt = $conexion->prepare("SELECT p.nombre, dp.cantidad, dp.precio_unitario FROM detalle_pedidos dp JOIN productos p ON dp.producto_id = p.id WHERE dp.pedido_id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$resultado = $stmt->get_result();
$detalles = $resultado->fetch_all(MYSQLI_ASSOC);
$stmt->close();

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
    <title>Detalle del Pedido #<?php echo $pedido['id']; ?> - Panel de Administración</title>
    <link rel="stylesheet" href="../style.css" />
    <style>
        body {
            font-family: 'Poppins', sans-serif;
            background-color: #fefefe;
            margin: 0;
            padding: 0;
        }
        header {
            background: linear-gradient(to right, #f59e0b, #b45309, #f59e0b);
            color: white;
            padding: 1.5rem;
            text-align: center;
            font-weight: 700;
            font-size: 1.8rem;
            letter-spacing: 1px;
            box-shadow: 0 2px 6px rgba(0,0,0,0.15);
        }
        nav {
            background-color: #b45309;
            display: flex;
            justify-content: center;
            box-shadow: inset 0 -2px 4px rgba(0,0,0,0.1);
        }
        nav a {
            color: white;
            padding: 1rem 2rem;
            text-decoration: none;
            display: block;
            font-weight: 600;
            transition: background-color 0.3s ease;
        }
        nav a:hover {
            background-color: #fbbf24;
            color: #333;
        }
        main {
            padding: 2rem 3rem;
            max-width: 900px;
            margin: 0 auto;
            background-color: #fff;
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
            border-radius: 8px;
        }
        h2 {
            color: #b45309;
            font-weight: 700;
            margin-bottom: 1rem;
            border-bottom: 2px solid #fbbf24;
            padding-bottom: 0.5rem;
        }
        table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 0 10px;
            margin-bottom: 2rem;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
            border-radius: 8px;
            overflow: hidden;
        }
        thead tr {
            background-color: #b45309;
            color: white;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }
        th, td {
            padding: 1rem 1.5rem;
            text-align: left;
        }
        tbody tr {
            background-color: #fff;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
            border-radius: 8px;
            transition: background-color 0.3s ease;
        }
        tbody tr:hover {
            background-color: #fef3c7;
        }
        a.button {
            background-color: #b45309;
            color: white;
            padding: 0.75rem 1.5rem;
            text-decoration: none;
            border-radius: 0.375rem;
            font-weight: 600;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            transition: background-color 0.3s ease;
            display: inline-block;
        }
        a.button:hover {
            background-color: #fbbf24;
            color: #333;
        }
    </style>
</head>
<body>
    <header>
        <h1>Detalle del Pedido #<?php echo $pedido['id']; ?></h1>
    </header>
    <nav>
        <a href="index.php">Inicio</a>
        <a href="productos.php">Gestión de Productos</a>
        <a href="pedidos.php">Gestión de Pedidos</a>
        <a href="../LOGIN/logout.php">Cerrar Sesión</a>
    </nav>
    <main>
        <h2>Información del Pedido</h2>
        <p><strong>ID Usuario:</strong> <?php echo $pedido['usuario_id']; ?></p>
        <p><strong>Fecha:</strong> <?php echo isset($pedido['fecha']) ? $pedido['fecha'] : (isset($pedido['created_at']) ? $pedido['created_at'] : 'N/A'); ?></p>
        <p><strong>Estado:</strong> <?php echo htmlspecialchars($pedido['estado'], ENT_QUOTES); ?></p>
        <p><strong>Total:</strong> $<?php echo number_format($pedido['total'], 2, ',', '.'); ?></p>

        <h2>Productos</h2>
        <table>
            <thead>
                <tr>
                    <th>Nombre</th>
                    <th>Cantidad</th>
                    <th>Precio Unitario</th>
                    <th>Subtotal</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($detalles as $detalle): ?>
                <tr>
                    <td><?php echo htmlspecialchars($detalle['nombre'], ENT_QUOTES); ?></td>
                    <td><?php echo $detalle['cantidad']; ?></td>
                    <td>$<?php echo number_format($detalle['precio_unitario'], 2, ',', '.'); ?></td>
                    <td>$<?php echo number_format($detalle['cantidad'] * $detalle['precio_unitario'], 2, ',', '.'); ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <a href="pedidos.php" class="button">Volver a Pedidos</a>
    </main>
</body>
</html>

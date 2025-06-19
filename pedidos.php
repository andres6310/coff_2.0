<?php
session_start();
require 'conexion.php';

// Verificar autenticación
if (!isset($_SESSION['usuario_id'])) {
    header("Location: LOGIN/login.php");
    exit();
}

// Obtener pedidos del usuario
$pedidos = [];
$query = $conexion->prepare("SELECT p.*, pr.nombre as producto_nombre, pr.precio as producto_precio 
                            FROM pedidos p
                            JOIN productos pr ON p.producto_id = pr.id
                            WHERE p.usuario_id = ?
                            ORDER BY p.fecha DESC");
$query->bind_param("i", $_SESSION['usuario_id']);
$query->execute();
$result = $query->get_result();
$pedidos = $result->fetch_all(MYSQLI_ASSOC);

// Procesar nuevo pedido
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['producto_id'])) {
    $producto_id = intval($_POST['producto_id']);
    $cantidad = intval($_POST['cantidad']);
    
    $stmt = $conexion->prepare("INSERT INTO pedidos (usuario_id, producto_id, cantidad) VALUES (?, ?, ?)");
    $stmt->bind_param("iii", $_SESSION['usuario_id'], $producto_id, $cantidad);
    $stmt->execute();
    header("Location: pedidos.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Mis Pedidos</title>
    <link rel="stylesheet" href="style.css">
    <style>
        .pedidos-container { max-width: 800px; margin: 0 auto; padding: 20px; }
        table { width: 100%; border-collapse: collapse; margin: 20px 0; }
        th, td { padding: 10px; border: 1px solid #ddd; text-align: left; }
    </style>
</head>
<body>
    <div class="pedidos-container">
        <h1>Mis Pedidos</h1>
        
        <h2>Realizar Nuevo Pedido</h2>
        <form method="POST">
            <select name="producto_id" required>
                <option value="">Seleccione un producto</option>
                <?php
                $productos = $conexion->query("SELECT * FROM productos");
                while($producto = $productos->fetch_assoc()):
                ?>
                <option value="<?= $producto['id'] ?>">
                    <?= $producto['nombre'] ?> ($<?= number_format($producto['precio'], 2) ?>)
                </option>
                <?php endwhile; ?>
            </select>
            <input type="number" name="cantidad" min="1" value="1" required>
            <button type="submit">Realizar Pedido</button>
        </form>
        
        <h2>Historial de Pedidos</h2>
        <table>
            <thead>
                <tr>
                    <th>Fecha</th>
                    <th>Producto</th>
                    <th>Cantidad</th>
                    <th>Total</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach($pedidos as $pedido): ?>
                <tr>
                    <td><?= date('d/m/Y H:i', strtotime($pedido['fecha'])) ?></td>
                    <td><?= $pedido['producto_nombre'] ?></td>
                    <td><?= $pedido['cantidad'] ?></td>
                    <td>$<?= number_format($pedido['cantidad'] * $pedido['producto_precio'], 2) ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</body>
</html>

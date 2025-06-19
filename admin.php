<?php
session_start();
require 'conexion.php';

// Verificar autenticación y permisos
if (!isset($_SESSION['usuario_id'])) {
    header("Location: LOGIN/login.php");
    exit();
}

// Obtener productos
$productos = [];
$query = $conexion->query("SELECT * FROM productos");
if ($query) {
    $productos = $query->fetch_all(MYSQLI_ASSOC);
}

// Procesar formularios
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Agregar nuevo producto
    if (isset($_POST['agregar_producto'])) {
        $nombre = limpiarDatos($_POST['nombre']);
        $descripcion = limpiarDatos($_POST['descripcion']);
        $precio = limpiarDatos($_POST['precio']);
        $imagen = limpiarDatos($_POST['imagen']);
        
        $stmt = $conexion->prepare("INSERT INTO productos (nombre, descripcion, precio, imagen) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("ssds", $nombre, $descripcion, $precio, $imagen);
        $stmt->execute();
        header("Location: admin.php");
        exit();
    }
    
    // Eliminar producto
    if (isset($_POST['eliminar_producto'])) {
        $id = intval($_POST['id']);
        $conexion->query("DELETE FROM productos WHERE id = $id");
        header("Location: admin.php");
        exit();
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Panel de Administración</title>
    <link rel="stylesheet" href="style.css">
    <style>
        .admin-container { max-width: 1200px; margin: 0 auto; padding: 20px; }
        table { width: 100%; border-collapse: collapse; margin: 20px 0; }
        th, td { padding: 10px; border: 1px solid #ddd; text-align: left; }
        .form-group { margin-bottom: 15px; }
    </style>
</head>
<body>
    <div class="admin-container">
        <h1>Panel de Administración</h1>
        
        <h2>Agregar Nuevo Producto</h2>
        <form method="POST">
            <div class="form-group">
                <label>Nombre:</label>
                <input type="text" name="nombre" required>
            </div>
            <div class="form-group">
                <label>Descripción:</label>
                <textarea name="descripcion" required></textarea>
            </div>
            <div class="form-group">
                <label>Precio:</label>
                <input type="number" step="0.01" name="precio" required>
            </div>
            <div class="form-group">
                <label>Imagen (nombre archivo):</label>
                <input type="text" name="imagen" required>
            </div>
            <button type="submit" name="agregar_producto">Agregar Producto</button>
        </form>
        
        <h2>Productos Existentes</h2>
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Nombre</th>
                    <th>Descripción</th>
                    <th>Precio</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach($productos as $producto): ?>
                <tr>
                    <td><?= $producto['id'] ?></td>
                    <td><?= $producto['nombre'] ?></td>
                    <td><?= $producto['descripcion'] ?></td>
                    <td>$<?= number_format($producto['precio'], 2) ?></td>
                    <td>
                        <form method="POST" style="display:inline;">
                            <input type="hidden" name="id" value="<?= $producto['id'] ?>">
                            <button type="submit" name="eliminar_producto">Eliminar</button>
                        </form>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</body>
</html>

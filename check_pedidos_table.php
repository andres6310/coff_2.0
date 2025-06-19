<?php
require 'conexion.php';

$result = $conexion->query("SHOW COLUMNS FROM pedidos");

if ($result) {
    echo "La tabla 'pedidos' existe. Columnas:<br>";
    while ($row = $result->fetch_assoc()) {
        echo "- " . htmlspecialchars($row['Field']) . "<br>";
    }
} else {
    echo "Error: La tabla 'pedidos' no existe o no se puede acceder.<br>";
    echo "Error de MySQL: " . $conexion->error;
}
?>

<?php
require 'conexion.php';
$result = $conexion->query('SELECT imagen FROM productos');
echo "Imágenes en la base de datos:\n";
while($row = $result->fetch_assoc()) {
    echo $row['imagen']."\n";
    
    // Verificar si el archivo existe
    $ruta = 'img/'.$row['imagen'];
    echo "Archivo ".$ruta." existe: ".(file_exists($ruta) ? "Sí" : "No")."\n";
}
?>

<?php
// ajax_get_productos.php

require 'conexion.php'; // Asegúrate de que este archivo de conexión funcione

// Seguridad: Define los filtros permitidos
$filtros_permitidos = ['destacados', 'recientes', 'mejores-vendidos'];
$filtro_solicitado = $_GET['filtro'] ?? 'destacados';
$filtro = in_array($filtro_solicitado, $filtros_permitidos) ? $filtro_solicitado : 'destacados';

// Construye la consulta SQL
$sql = "SELECT id, nombre, descripcion, precio, descuento, imagen FROM productos WHERE imagen IS NOT NULL AND imagen != ''";

switch ($filtro) {
    case 'recientes':
        $sql .= " ORDER BY id DESC";
        break;
    case 'mejores-vendidos':
        // Si no tienes una columna 'ventas', un orden aleatorio es un buen ejemplo
        $sql .= " ORDER BY RAND()"; 
        break;
    case 'destacados':
    default:
        // Por defecto, muestra los últimos como destacados
        $sql .= " ORDER BY id DESC";
        break;
}

$sql .= " LIMIT 12";

if ($conexion instanceof mysqli) {
    $query = $conexion->query($sql);
    if ($query && $query->num_rows > 0) {
        $productos = $query->fetch_all(MYSQLI_ASSOC);
        
        // Genera y devuelve el HTML
        echo '<div class="container-products">';
        foreach ($productos as $producto) {
            $rutaImagen = './img/cafe-australiano.jpg';
            if (!empty($producto['imagen'])) {
                $rutaTemporal = 'admin/' . $producto['imagen'];
                if (file_exists($rutaTemporal)) {
                    $rutaImagen = $rutaTemporal;
                }
            }
            // Genera la tarjeta de producto (card)
            echo '
            <div class="card-product" data-product-id="prod-'.htmlspecialchars($producto['id']).'" data-description="'.htmlspecialchars($producto['descripcion']).'" data-image-large="'.htmlspecialchars($rutaImagen).'">
                <div class="container-img">
                    <img src="'.htmlspecialchars($rutaImagen).'" alt="'.htmlspecialchars($producto['nombre']).'">
                    ' . (isset($producto['descuento']) && $producto['descuento'] > 0 ? '<span class="discount">-'.$producto['descuento'].'%</span>' : '') . '
                    <div class="button-group">
                        <span><i class="fa-regular fa-eye"></i></span>
                        <span><i class="fa-regular fa-heart"></i></span>
                        <span><i class="fa-solid fa-code-compare"></i></span>
                    </div>
                </div>
                <div class="content-card-product">
                    <div class="stars"><i class="fa-solid fa-star"></i><i class="fa-solid fa-star"></i><i class="fa-solid fa-star"></i><i class="fa-solid fa-star"></i><i class="fa-regular fa-star"></i></div>
                    <h3>'.htmlspecialchars($producto['nombre']).'</h3>
                    <span class="add-cart"><i class="fa-solid fa-basket-shopping"></i></span>
                    <p class="price">$'.number_format($producto['precio'], 0, ',', '.').'</p>
                </div>
            </div>';
        }
        echo '<p class="no-results-message" style="display:none; text-align: center; width: 100%;">No se encontraron productos.</p>';
        echo '</div>';
    } else {
        echo '<p style="text-align:center; padding:20px;">No hay productos para este filtro.</p>';
    }
} else {
    http_response_code(500); // Error de servidor
    echo '<p style="text-align:center; padding:20px; color:red;">Error de conexión a la base de datos.</p>';
}
?>
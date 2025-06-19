<?php
// Habilitar reporte de errores para depuración
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/debug_pedidos.log');

// Función de depuración mejorada
function debug_log($message, $data = null) {
    $timestamp = date('Y-m-d H:i:s');
    $log_message = "[$timestamp] $message";
    if ($data !== null) {
        $log_message .= " | Data: " . json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    }
    error_log($log_message);
    file_put_contents(__DIR__ . '/debug_pedidos.log', $log_message . "\n", FILE_APPEND | LOCK_EX);
}

debug_log("=== INICIO GUARDAR_PEDIDO.PHP ===");

session_start();
require 'conexion.php';

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

debug_log("Headers establecidos");
debug_log("Método HTTP: " . $_SERVER['REQUEST_METHOD']);
debug_log("Content-Type: " . ($_SERVER['CONTENT_TYPE'] ?? 'No especificado'));

// Verificar que el usuario esté autenticado
if (!isset($_SESSION['usuario_id'])) {
    debug_log("ERROR: Usuario no autenticado", $_SESSION);
    echo json_encode([
        'success' => false, 
        'message' => 'Usuario no autenticado',
        'debug_session' => $_SESSION
    ]);
    exit();
}

$usuario_id = $_SESSION['usuario_id'];
debug_log("✅ Usuario autenticado con ID: " . $usuario_id);

// Recibir y validar datos JSON del carrito
$input_raw = file_get_contents('php://input');
debug_log("Datos RAW recibidos: " . $input_raw);

if (empty($input_raw)) {
    debug_log("ERROR: No se recibieron datos");
    echo json_encode(['success' => false, 'message' => 'No se recibieron datos del carrito']);
    exit();
}

$input = json_decode($input_raw, true);
$json_error = json_last_error();

if ($json_error !== JSON_ERROR_NONE) {
    debug_log("ERROR: Error al decodificar JSON: " . json_last_error_msg());
    echo json_encode([
        'success' => false, 
        'message' => 'Datos JSON inválidos: ' . json_last_error_msg(),
        'raw_data' => substr($input_raw, 0, 500) // Primeros 500 caracteres para depuración
    ]);
    exit();
}

debug_log("Datos JSON decodificados correctamente", $input);

// Validar estructura de datos
if (!$input || !isset($input['items']) || !is_array($input['items'])) {
    debug_log("ERROR: Estructura de datos inválida");
    echo json_encode([
        'success' => false, 
        'message' => 'Estructura de datos inválida',
        'received_data' => $input
    ]);
    exit();
}

$items = $input['items'];
debug_log("Número de items en el carrito: " . count($items));

// Validar que hay items
if (empty($items)) {
    debug_log("ERROR: Carrito vacío");
    echo json_encode(['success' => false, 'message' => 'El carrito está vacío']);
    exit();
}

// Depuración detallada de cada item
$debug_info = [];
$validation_errors = [];

foreach ($items as $index => $item) {
    debug_log("Validando item $index", $item);
    
    // Detectar diferentes formatos de datos
    $item_debug = [
        'index' => $index,
        'raw_item' => $item,
        'detected_format' => []
    ];
    
    // Formato 1: {id, nombre, precio, cantidad}
    if (isset($item['id']) && isset($item['nombre']) && isset($item['precio']) && isset($item['cantidad'])) {
        $item_debug['detected_format'][] = 'formato_1 (id, nombre, precio, cantidad)';
        $producto_id = intval($item['id']);
        $cantidad = intval($item['cantidad']);
        $precio_unitario = floatval($item['precio']);
        $nombre = $item['nombre'];
    }
    // Formato 2: {id, quantity, price, name}
    elseif (isset($item['id']) && isset($item['quantity']) && isset($item['price'])) {
        $item_debug['detected_format'][] = 'formato_2 (id, quantity, price)';
        $producto_id = intval($item['id']);
        $cantidad = intval($item['quantity']);
        $precio_unitario = floatval($item['price']);
        $nombre = $item['name'] ?? 'Producto sin nombre';
    }
    // Formato 3: otros formatos
    else {
        $validation_errors[] = "Item $index: formato no reconocido - " . json_encode($item);
        continue;
    }
    
    // Validaciones específicas
    if ($producto_id <= 0) {
        $validation_errors[] = "Item $index: ID de producto inválido ($producto_id)";
    }
    if ($cantidad <= 0) {
        $validation_errors[] = "Item $index: Cantidad inválida ($cantidad)";
    }
    if ($precio_unitario <= 0) {
        $validation_errors[] = "Item $index: Precio inválido ($precio_unitario)";
    }
    
    $item_debug['processed'] = [
        'producto_id' => $producto_id,
        'cantidad' => $cantidad,
        'precio_unitario' => $precio_unitario,
        'nombre' => $nombre,
        'subtotal' => $cantidad * $precio_unitario
    ];
    
    $debug_info[] = $item_debug;
}

debug_log("Validación completa de items", $debug_info);

// Si hay errores de validación, reportarlos
if (!empty($validation_errors)) {
    debug_log("ERRORES DE VALIDACIÓN", $validation_errors);
    echo json_encode([
        'success' => false,
        'message' => 'Errores de validación en el carrito',
        'validation_errors' => $validation_errors,
        'debug_info' => $debug_info
    ]);
    exit();
}

debug_log("✅ Validación de items exitosa");

// Verificar conexión a la base de datos
if (!$conexion) {
    debug_log("ERROR: Conexión a la base de datos falló");
    echo json_encode(['success' => false, 'message' => 'Error de conexión a la base de datos']);
    exit();
}

debug_log("✅ Conexión a base de datos establecida");

// Iniciar transacción
$conexion->begin_transaction();
debug_log("Transacción iniciada");

try {
    // Calcular total
    $total = 0;
    $items_procesados = [];
    
    foreach ($items as $index => $item) {
        // Detectar formato y extraer datos
        if (isset($item['precio']) && isset($item['cantidad'])) {
            $precio = floatval($item['precio']);
            $cantidad = intval($item['cantidad']);
            $producto_id = intval($item['id']);
            $nombre = $item['nombre'] ?? 'Producto';
        } elseif (isset($item['price']) && isset($item['quantity'])) {
            $precio = floatval($item['price']);
            $cantidad = intval($item['quantity']);
            $producto_id = intval($item['id']);
            $nombre = $item['name'] ?? 'Producto';
        } else {
            throw new Exception("Formato de item no reconocido en índice $index");
        }
        
        $subtotal = $precio * $cantidad;
        $total += $subtotal;
        
        $items_procesados[] = [
            'producto_id' => $producto_id,
            'nombre' => $nombre,
            'cantidad' => $cantidad,
            'precio_unitario' => $precio,
            'subtotal' => $subtotal
        ];
    }
    
    debug_log("Total calculado: $total");
    debug_log("Items procesados", $items_procesados);

    // Insertar pedido general
    $stmt = $conexion->prepare("INSERT INTO pedidos (usuario_id, total, fecha_pedido, estado) VALUES (?, ?, NOW(), 'completado')");
    if (!$stmt) {
        throw new Exception('Error en la preparación de la consulta de pedido: ' . $conexion->error);
    }
    
    $stmt->bind_param("id", $usuario_id, $total);
    
    if (!$stmt->execute()) {
        throw new Exception('Error al ejecutar consulta de pedido: ' . $stmt->error);
    }
    
    $pedido_id = $conexion->insert_id;
    debug_log("✅ Pedido principal insertado con ID: $pedido_id");
    $stmt->close();

    // Insertar detalles del pedido
    $stmt_detalle = $conexion->prepare("INSERT INTO detalle_pedidos (pedido_id, producto_id, cantidad, precio_unitario) VALUES (?, ?, ?, ?)");
    if (!$stmt_detalle) {
        throw new Exception('Error en la preparación de la consulta de detalle: ' . $conexion->error);
    }

    foreach ($items_procesados as $item) {
        $stmt_detalle->bind_param("iiid", 
            $pedido_id, 
            $item['producto_id'], 
            $item['cantidad'], 
            $item['precio_unitario']
        );
        
        if (!$stmt_detalle->execute()) {
            throw new Exception('Error al insertar detalle del pedido: ' . $stmt_detalle->error);
        }
        
        debug_log("Detalle insertado", $item);
    }
    
    $stmt_detalle->close();

    // Confirmar transacción
    $conexion->commit();
    debug_log("✅ Transacción confirmada exitosamente");

    // Obtener información adicional del pedido para la factura
    $stmt_info = $conexion->prepare("
        SELECT 
            p.id, p.usuario_id, p.total, p.fecha_pedido, p.estado,
            dp.producto_id, dp.cantidad, dp.precio_unitario,
            pr.nombre as producto_nombre
        FROM pedidos p
        JOIN detalle_pedidos dp ON p.id = dp.pedido_id
        LEFT JOIN productos pr ON dp.producto_id = pr.id
        WHERE p.id = ?
    ");
    
    if ($stmt_info) {
        $stmt_info->bind_param("i", $pedido_id);
        $stmt_info->execute();
        $result = $stmt_info->get_result();
        $detalles_pedido = [];
        
        while ($row = $result->fetch_assoc()) {
            $detalles_pedido[] = $row;
        }
        $stmt_info->close();
        
        debug_log("Detalles del pedido para factura", $detalles_pedido);
    }

// Respuesta exitosa con información completa
    $response = [
        'success' => true,
        'message' => 'Pedido guardado exitosamente',
        'pedido_id' => $pedido_id,
        'usuario_id' => $usuario_id,
        'total' => $total,
        'items_count' => count($items_procesados),
        'factura_url' => "/coff_2.0/factura.php?pedido_id=$pedido_id&auto=1",
        'redirect_to_invoice' => true, // Nuevo campo para indicar redirección
        'debug_info' => [
            'items_procesados' => $items_procesados,
            'total_calculado' => $total,
            'fecha_procesamiento' => date('Y-m-d H:i:s'),
            'detalles_bd' => $detalles_pedido ?? []
        ]
    ];

    debug_log("✅ RESPUESTA EXITOSA", $response);
    echo json_encode($response);

} catch (Exception $e) {
    $conexion->rollback();
    debug_log("❌ ERROR: Excepción capturada", [
        'message' => $e->getMessage(),
        'file' => $e->getFile(),
        'line' => $e->getLine(),
        'trace' => $e->getTraceAsString()
    ]);
    
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage(),
        'error_details' => [
            'file' => $e->getFile(),
            'line' => $e->getLine()
        ],
        'debug_info' => $debug_info
    ]);
} finally {
    // Cerrar conexión
    if ($conexion) {
        $conexion->close();
        debug_log("Conexión cerrada");
    }
}

debug_log("=== FIN GUARDAR_PEDIDO.PHP ===");
?>

<?php
session_start();
require 'conexion.php';

// Verificar que el usuario esté autenticado
if (!isset($_SESSION['usuario_id'])) {
    header("Location: LOGIN/login.php");
    exit();
}

// Obtener datos del pedido por ID (pasado por GET)
if (!isset($_GET['pedido_id'])) {
    die("ID de pedido no especificado.");
}

$pedido_id = intval($_GET['pedido_id']);
$usuario_id = $_SESSION['usuario_id'];

// Consultar datos del pedido principal
$stmt = $conexion->prepare("SELECT * FROM pedidos WHERE id = ? AND usuario_id = ?");
$stmt->bind_param("ii", $pedido_id, $usuario_id);
$stmt->execute();
$result = $stmt->get_result();
$pedido = $result->fetch_assoc();

if (!$pedido) {
    die("Pedido no encontrado o no autorizado.");
}

// Consultar detalles del pedido con información de productos
$stmt_detalles = $conexion->prepare("
    SELECT 
        dp.producto_id, 
        dp.cantidad, 
        dp.precio_unitario,
        pr.nombre as producto_nombre
    FROM detalle_pedidos dp
    LEFT JOIN productos pr ON dp.producto_id = pr.id
    WHERE dp.pedido_id = ?
");
$stmt_detalles->bind_param("i", $pedido_id);
$stmt_detalles->execute();
$result_detalles = $stmt_detalles->get_result();

$detalles = [];
while ($row = $result_detalles->fetch_assoc()) {
    $detalles[] = $row;
}

// Si no hay detalles, mostrar error
if (empty($detalles)) {
    die("No se encontraron detalles del pedido.");
}

// Obtener información del usuario (opcional)
$stmt_usuario = $conexion->prepare("SELECT nombre, email FROM usuarios WHERE id = ?");
$stmt_usuario->bind_param("i", $usuario_id);
$stmt_usuario->execute();
$result_usuario = $stmt_usuario->get_result();
$usuario_info = $result_usuario->fetch_assoc();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Factura - Coffee Galactic</title>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
    <style>
        body {
            font-family: Arial, sans-serif;
            max-width: 800px;
            margin: 0 auto;
            padding: 20px;
            background-color: #f5f5f5;
        }
        
        .invoice-container {
            background: white;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            margin-bottom: 20px;
        }
        
        .header {
            text-align: center;
            border-bottom: 2px solid #8B4513;
            padding-bottom: 20px;
            margin-bottom: 30px;
        }
        
        .logo {
            font-size: 24px;
            font-weight: bold;
            color: #8B4513;
            margin-bottom: 10px;
        }
        
        .company-info {
            color: #666;
            font-size: 14px;
        }
        
        .invoice-title {
            font-size: 28px;
            font-weight: bold;
            color: #333;
            margin: 20px 0;
        }
        
        .invoice-details {
            display: flex;
            justify-content: space-between;
            margin-bottom: 30px;
        }
        
        .detail-section {
            flex: 1;
        }
        
        .detail-section h3 {
            color: #8B4513;
            border-bottom: 1px solid #ddd;
            padding-bottom: 5px;
            margin-bottom: 15px;
        }
        
        .detail-item {
            margin-bottom: 8px;
            display: flex;
            justify-content: space-between;
        }
        
        .detail-label {
            font-weight: bold;
            min-width: 120px;
        }
        
        .products-table {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
        }
        
        .products-table th,
        .products-table td {
            border: 1px solid #ddd;
            padding: 12px;
            text-align: left;
        }
        
        .products-table th {
            background-color: #8B4513;
            color: white;
            font-weight: bold;
        }
        
        .products-table .text-center {
            text-align: center;
        }
        
        .products-table .text-right {
            text-align: right;
        }
        
        .total-section {
            margin-top: 20px;
            text-align: right;
        }
        
        .total-row {
            display: flex;
            justify-content: flex-end;
            margin-bottom: 10px;
        }
        
        .total-label {
            font-weight: bold;
            min-width: 150px;
            padding: 8px 15px;
            background-color: #8B4513;
            color: white;
        }
        
        .total-value {
            font-weight: bold;
            min-width: 100px;
            padding: 8px 15px;
            background-color: #f8f8f8;
            border: 1px solid #ddd;
        }
        
        .footer {
            text-align: center;
            margin-top: 40px;
            padding-top: 20px;
            border-top: 1px solid #ddd;
            color: #666;
            font-style: italic;
        }
        
        .download-btn {
            background-color: #8B4513;
            color: white;
            border: none;
            padding: 12px 30px;
            font-size: 16px;
            border-radius: 5px;
            cursor: pointer;
            margin: 20px 10px;
            transition: background-color 0.3s;
        }
        
        .download-btn:hover {
            background-color: #A0522D;
        }
        
        .print-btn {
            background-color: #28a745;
            color: white;
            border: none;
            padding: 12px 30px;
            font-size: 16px;
            border-radius: 5px;
            cursor: pointer;
            margin: 20px 10px;
            transition: background-color 0.3s;
        }
        
        .print-btn:hover {
            background-color: #218838;
        }
        
        .actions {
            text-align: center;
            margin-top: 30px;
        }
        
        .status-badge {
            display: inline-block;
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: bold;
            text-transform: uppercase;
        }
        
        .status-completado {
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        
        .status-pendiente {
            background-color: #fff3cd;
            color: #856404;
            border: 1px solid #ffeaa7;
        }
        
        @media print {
            body {
                background-color: white;
            }
            .actions {
                display: none;
            }
            .invoice-container {
                box-shadow: none;
                padding: 0;
            }
        }
    </style>
</head>
<body>
    <div class="invoice-container" id="invoice">
        <div class="header">
            <div class="logo">☕ Coffee Galactic</div>
            <div class="company-info">
                Café de Alta Calidad<br>
                www.coffeegalactic.com<br>
                contacto@coffeegalactic.com
            </div>
            <div class="invoice-title">FACTURA DE COMPRA</div>
        </div>
        
        <div class="invoice-details">
            <div class="detail-section">
                <h3>Información del Cliente</h3>
                <div class="detail-item">
                    <span class="detail-label">Cliente ID:</span>
                    <span><?php echo htmlspecialchars($usuario_id); ?></span>
                </div>
                <?php if ($usuario_info && $usuario_info['nombre']): ?>
                <div class="detail-item">
                    <span class="detail-label">Nombre:</span>
                    <span><?php echo htmlspecialchars($usuario_info['nombre']); ?></span>
                </div>
                <?php endif; ?>
                <?php if ($usuario_info && $usuario_info['email']): ?>
                <div class="detail-item">
                    <span class="detail-label">Email:</span>
                    <span><?php echo htmlspecialchars($usuario_info['email']); ?></span>
                </div>
                <?php endif; ?>
            </div>
            
            <div class="detail-section">
                <h3>Detalles de Factura</h3>
                <div class="detail-item">
                    <span class="detail-label">Factura #:</span>
                    <span><?php echo str_pad($pedido_id, 6, '0', STR_PAD_LEFT); ?></span>
                </div>
                <div class="detail-item">
                    <span class="detail-label">Pedido ID:</span>
                    <span><?php echo htmlspecialchars($pedido_id); ?></span>
                </div>
                <div class="detail-item">
                    <span class="detail-label">Fecha:</span>
                    <span><?php echo date('d/m/Y H:i', strtotime($pedido['fecha_pedido'])); ?></span>
                </div>
                <div class="detail-item">
                    <span class="detail-label">Estado:</span>
                    <span class="status-badge status-<?php echo $pedido['estado']; ?>">
                        <?php echo ucfirst($pedido['estado']); ?>
                    </span>
                </div>
            </div>
        </div>
        
        <table class="products-table">
            <thead>
                <tr>
                    <th>Producto</th>
                    <th class="text-center">Cantidad</th>
                    <th class="text-right">Precio Unitario</th>
                    <th class="text-right">Subtotal</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($detalles as $detalle): ?>
                <tr>
                    <td><?php echo htmlspecialchars($detalle['producto_nombre'] ?? 'Producto ID: ' . $detalle['producto_id']); ?></td>
                    <td class="text-center"><?php echo htmlspecialchars($detalle['cantidad']); ?></td>
                    <td class="text-right">$<?php echo number_format($detalle['precio_unitario'], 2); ?></td>
                    <td class="text-right">$<?php echo number_format($detalle['cantidad'] * $detalle['precio_unitario'], 2); ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        
        <div class="total-section">
            <div class="total-row">
                <div class="total-label">TOTAL A PAGAR</div>
                <div class="total-value">$<?php echo number_format($pedido['total'], 2); ?></div>
            </div>
        </div>
        
        <div class="footer">
            Coffee Galactic - Gracias por su compra<br>
            Esta factura fue generada automáticamente el <?php echo date('d/m/Y \a \l\a\s H:i'); ?>
        </div>
    </div>
    
    <div class="actions">
        <button class="download-btn" onclick="downloadPDF()">📄 Descargar PDF</button>
        <button class="print-btn" onclick="window.print()">🖨️ Imprimir</button>
        <button class="download-btn" onclick="window.location.href='./index.php'">← Volver</button>
    </div>

  

    <script>
        function downloadPDF() {
            const { jsPDF } = window.jspdf;
            const doc = new jsPDF();
            
            // Configuración del documento
            doc.setFontSize(20);
            doc.setTextColor(139, 69, 19); // Color café
            doc.text('☕ Coffee Galactic', 20, 20);
            
            doc.setFontSize(16);
            doc.setTextColor(0, 0, 0);
            doc.text('FACTURA DE COMPRA', 20, 35);
            
            // Línea separadora
            doc.setLineWidth(0.5);
            doc.line(20, 40, 190, 40);
            
            // Información del cliente y factura
            doc.setFontSize(12);
            doc.text('Información del Cliente:', 20, 55);
            doc.text('Cliente ID: <?php echo $usuario_id; ?>', 20, 65);
            <?php if ($usuario_info && $usuario_info['nombre']): ?>
            doc.text('Nombre: <?php echo addslashes($usuario_info['nombre']); ?>', 20, 75);
            <?php endif; ?>
            
            doc.text('Detalles de Factura:', 120, 55);
            doc.text('Factura #: <?php echo str_pad($pedido_id, 6, '0', STR_PAD_LEFT); ?>', 120, 65);
            doc.text('Pedido ID: <?php echo $pedido_id; ?>', 120, 75);
            doc.text('Fecha: <?php echo date('d/m/Y H:i', strtotime($pedido['fecha_pedido'])); ?>', 120, 85);
            doc.text('Estado: <?php echo ucfirst($pedido['estado']); ?>', 120, 95);
            
            // Tabla de productos
            doc.setFontSize(10);
            
            // Encabezados de tabla
            doc.setFillColor(139, 69, 19);
            doc.setTextColor(255, 255, 255);
            doc.rect(20, 110, 170, 10, 'F');
            doc.text('Producto', 25, 117);
            doc.text('Cant.', 90, 117);
            doc.text('Precio Unit.', 115, 117);
            doc.text('Subtotal', 155, 117);
            
            // Datos de los productos
            doc.setTextColor(0, 0, 0);
            let yPosition = 120;
            
            <?php foreach ($detalles as $index => $detalle): ?>
            doc.rect(20, <?php echo $index; ?> * 10 + 120, 170, 10);
            doc.text('<?php echo addslashes($detalle['producto_nombre'] ?? 'Producto ID: ' . $detalle['producto_id']); ?>', 25, <?php echo $index; ?> * 10 + 127);
            doc.text('<?php echo $detalle['cantidad']; ?>', 95, <?php echo $index; ?> * 10 + 127);
            doc.text('$<?php echo number_format($detalle['precio_unitario'], 2); ?>', 120, <?php echo $index; ?> * 10 + 127);
            doc.text('$<?php echo number_format($detalle['cantidad'] * $detalle['precio_unitario'], 2); ?>', 160, <?php echo $index; ?> * 10 + 127);
            <?php endforeach; ?>
            
            // Total
            const totalY = <?php echo count($detalles); ?> * 10 + 140;
            doc.setFontSize(12);
            doc.setFont(undefined, 'bold');
            doc.setFillColor(139, 69, 19);
            doc.setTextColor(255, 255, 255);
            doc.rect(120, totalY, 70, 10, 'F');
            doc.text('TOTAL A PAGAR', 125, totalY + 7);
            doc.setTextColor(0, 0, 0);
            doc.rect(120, totalY + 10, 70, 10);
            doc.text('$<?php echo number_format($pedido['total'], 2); ?>', 155, totalY + 17);
            
            // Footer
            doc.setFontSize(10);
            doc.setFont(undefined, 'italic');
            doc.setTextColor(100, 100, 100);
            doc.text('Coffee Galactic - Gracias por su compra', 105, 250, null, null, 'center');
            doc.text('Esta factura fue generada el <?php echo date('d/m/Y \a \l\a\s H:i'); ?>', 105, 260, null, null, 'center');
            
            // Descargar el PDF
            doc.save('Factura_Pedido_<?php echo $pedido_id; ?>.pdf');
        }
        
        // Auto-mostrar la factura si viene desde una compra
        window.addEventListener('load', function() {
            const urlParams = new URLSearchParams(window.location.search);
            if (urlParams.get('auto') === '1') {
                // Opcional: mostrar mensaje de éxito
                console.log('Pedido procesado exitosamente');
            }
        });
    </script>
    

</body>
</html>
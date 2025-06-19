<?php
session_start();
require '../conexion.php'; 

function limpiarDatos($datos) {
    $datos = trim($datos);
    $datos = stripslashes($datos);
    $datos = htmlspecialchars($datos, ENT_QUOTES, 'UTF-8');
    return $datos;
}

// Verificar si el usuario está autenticado y es administrador
if (!isset($_SESSION['usuario_id']) || !isset($_SESSION['usuario_rol']) || $_SESSION['usuario_rol'] !== 'admin') {
    header('Location: ../LOGIN/login.php');
    exit;
}

$accion = isset($_GET['accion']) ? limpiarDatos($_GET['accion']) : '';
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0; 

$error = '';
$success_message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre = limpiarDatos($_POST['nombre'] ?? '');
    $descripcion = limpiarDatos($_POST['descripcion'] ?? '');
    $precio = isset($_POST['precio']) ? floatval(str_replace(',', '.', $_POST['precio'])) : 0; 
    $categoria = limpiarDatos($_POST['categoria'] ?? ''); 
    $descuento = isset($_POST['descuento']) ? intval($_POST['descuento']) : 0;

    if (empty($nombre) || empty($descripcion) || $precio <= 0 || empty($categoria)) {
        $error = "Todos los campos (nombre, descripción, precio, categoría) son requeridos, y el precio debe ser mayor a cero.";
    } else {
        $imagenPath = null; 
        $imagenActual = $_POST['imagen_actual'] ?? null; 

        
        if (isset($_FILES['imagen']) && $_FILES['imagen']['error'] === UPLOAD_ERR_OK) {
            $fileTmpPath = $_FILES['imagen']['tmp_name'];
            $fileName = $_FILES['imagen']['name']; 

            $path_parts = pathinfo($fileName);
            $fileExtension = isset($path_parts['extension']) ? strtolower($path_parts['extension']) : '';

            $allowedfileExtensions = ['jpg', 'jpeg', 'png', 'gif'];

            if (!empty($fileExtension) && in_array($fileExtension, $allowedfileExtensions)) {
                $uploadFileDir = 'uploads/'; 
                if (!is_dir($uploadFileDir)) {
                    if (!mkdir($uploadFileDir, 0755, true)) {
                        $error = 'Error al crear el directorio de subidas.';
                    }
                }
                if (empty($error)) { 
                    $baseName = md5(time() . uniqid('', true)); 
                    $newFileName = $baseName . '.' . $fileExtension;
                    
                    $dest_path = $uploadFileDir . $newFileName;

                    if (move_uploaded_file($fileTmpPath, $dest_path)) {
                        $imagenPath = $dest_path; 
                
                        if ($accion === 'editar' && $imagenActual && file_exists($imagenActual) && $imagenActual !== $imagenPath) {
                            unlink($imagenActual);
                        }
                    } else {
                        $error = 'Error al mover el archivo subido. Código de error: ' . $_FILES['imagen']['error'];
                    }
                }
            } else {
                if (empty($fileExtension)) {
                    $error = 'No se pudo determinar la extensión del archivo o el archivo no tiene extensión.';
                } else {
                    $error = 'Tipo de archivo no permitido (' . htmlspecialchars($fileExtension) . '). Solo se permiten imágenes jpg, jpeg, png, gif.';
                }
            }
        } elseif ($accion === 'editar' && $imagenActual) {
            $imagenPath = $imagenActual;
        }
      
        if (empty($error)) { 
            if ($accion === 'agregar') {
                $stmt = $conexion->prepare("INSERT INTO productos (nombre, descripcion, precio, categoria, descuento, imagen) VALUES (?, ?, ?, ?, ?, ?)");
                if ($stmt === false) {
                    $error = "Error en la preparación de la consulta (INSERT): " . $conexion->error;
                } else {
                    $stmt->bind_param("ssdsis", $nombre, $descripcion, $precio, $categoria, $descuento, $imagenPath);
                    if ($stmt->execute()) {
                        $stmt->close();
                        header('Location: productos.php?status=added');
                        exit;
                    } else {
                        $error = "Error al agregar producto: " . $stmt->error;
                    }
                     if(isset($stmt) && $stmt instanceof mysqli_stmt) $stmt->close();
                }
            } elseif ($accion === 'editar' && $id > 0) {
                $sql_update = "UPDATE productos SET nombre=?, descripcion=?, precio=?, categoria=?, descuento=?, imagen=? WHERE id=?";
                $stmt = $conexion->prepare($sql_update);

                if ($stmt === false) {
                    $error = "Error en la preparación de la consulta (UPDATE): " . $conexion->error;
                } else {
                    $stmt->bind_param("ssdsisi", $nombre, $descripcion, $precio, $categoria, $descuento, $imagenPath, $id);
                    if ($stmt->execute()) {
                        $stmt->close();
                        header('Location: productos.php?status=updated');
                        exit;
                    } else {
                        $error = "Error al actualizar producto: " . $stmt->error;
                    }
                    if(isset($stmt) && $stmt instanceof mysqli_stmt) $stmt->close();
                }
            }
        }
    }
} elseif ($accion === 'eliminar' && $id > 0) { 
   
    $stmt_img = $conexion->prepare("SELECT imagen FROM productos WHERE id=?");
    if ($stmt_img) {
        $stmt_img->bind_param("i", $id);
        $stmt_img->execute();
        $stmt_img->bind_result($imagen_a_borrar);
        $stmt_img->fetch();
        $stmt_img->close();

        if ($imagen_a_borrar && file_exists($imagen_a_borrar)) {
            unlink($imagen_a_borrar);
        }
    }

    $stmt = $conexion->prepare("DELETE FROM productos WHERE id=?");
    if ($stmt) {
        $stmt->bind_param("i", $id);
        if ($stmt->execute()) {
            $stmt->close();
            header('Location: productos.php?status=deleted');
            exit;
        } else {
            $error = "Error al eliminar producto: " . $stmt->error;
        }
        if(isset($stmt) && $stmt instanceof mysqli_stmt) $stmt->close();
    } else {
        $error = "Error preparando la consulta de eliminación: " . $conexion->error;
    }
}

// --- OBTENER DATOS PARA MOSTRAR ---
$productos = []; 
$resultado_query = $conexion->query("SELECT id, nombre, descripcion, precio, categoria, descuento, imagen FROM productos ORDER BY id DESC");

if ($resultado_query) {
    $productos = $resultado_query->fetch_all(MYSQLI_ASSOC);
    $resultado_query->free();
} else {
    if(empty($error)) $error = "Error al obtener la lista de productos: " . $conexion->error;
}

// Procesar mensajes de estado desde GET
if (isset($_GET['status'])) {
    switch ($_GET['status']) {
        case 'added': $success_message = 'Producto agregado exitosamente.'; break;
        case 'updated': $success_message = 'Producto actualizado exitosamente.'; break;
        case 'deleted': $success_message = 'Producto eliminado exitosamente.'; break;
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Gestión de Productos - Panel de Administración</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins&display=swap" rel="stylesheet" />
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet" />
    <link rel="stylesheet" href="admin-style.css" />
    <link rel="icon" type="image/x-icon" href="../img/favicon_round.png" />
</head>
<body class="bg-gray-50 font-poppins min-h-screen flex flex-col">
    <header class="bg-gradient-to-r from-yellow-900 via-yellow-700 to-yellow-600 text-white p-6 text-center shadow-md">
        <h1 class="text-3xl font-bold tracking-wide">Gestión de Productos</h1>
    </header>
    <?php $currentPage = basename($_SERVER['PHP_SELF']); ?>
    <nav class="bg-yellow-900 flex flex-wrap justify-center shadow-inner">
        <a href="index.php" class="text-yellow-100 px-6 py-4 font-semibold hover:bg-yellow-700 hover:text-yellow-300 transition <?php echo ($currentPage == 'index.php') ? 'bg-yellow-700 text-yellow-300' : ''; ?>">Inicio</a>
        <a href="productos.php" class="text-yellow-100 px-6 py-4 font-semibold hover:bg-yellow-700 hover:text-yellow-300 transition <?php echo ($currentPage == 'productos.php') ? 'bg-yellow-700 text-yellow-300' : ''; ?>">Gestión de Productos</a>
        <a href="usuarios.php" class="text-yellow-100 px-6 py-4 font-semibold hover:bg-yellow-700 hover:text-yellow-300 transition <?php echo ($currentPage == 'usuarios.php') ? 'bg-yellow-700 text-yellow-300' : ''; ?>">Gestión de Usuarios</a>
        <a href="pedidos.php" class="text-yellow-100 px-6 py-4 font-semibold hover:bg-yellow-700 hover:text-yellow-300 transition <?php echo ($currentPage == 'pedidos.php') ? 'bg-yellow-700 text-yellow-300' : ''; ?>">Gestión de Pedidos</a>
        <a href="../LOGIN/logout.php" class="text-yellow-100 px-6 py-4 font-semibold hover:bg-yellow-700 hover:text-yellow-300 transition">Cerrar Sesión</a>
    </nav>

    <main class="flex-grow container mx-auto p-6">
        <?php if (!empty($error)): ?>
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4" role="alert">
                <strong class="font-bold">Error:</strong>
                <span class="block sm:inline"><?php echo htmlspecialchars($error); ?></span>
            </div>
        <?php endif; ?>
        <?php if (!empty($success_message)): ?>
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4" role="alert">
                <span class="block sm:inline"><?php echo htmlspecialchars($success_message); ?></span>
            </div>
        <?php endif; ?>

        <?php if ($accion === 'agregar' || ($accion === 'editar' && $id > 0)): 
            $productoEditar = null;
            if ($accion === 'editar' && $id > 0) {
                $stmt = $conexion->prepare("SELECT id, nombre, descripcion, precio, categoria, descuento, imagen FROM productos WHERE id=?");
                if ($stmt) {
                    $stmt->bind_param("i", $id);
                    $stmt->execute();
                    $resultado_edit = $stmt->get_result();
                    $productoEditar = $resultado_edit->fetch_assoc();
                    $stmt->close();
                    if (!$productoEditar) {
                        echo "<p class='text-red-500'>Producto no encontrado para editar.</p>";
                    }
                } else {
                    echo "<p class='text-red-500'>Error preparando la consulta para editar producto.</p>";
                }
            }
        ?>
        <h2 class="text-3xl font-bold mb-8 text-center"><?php echo $accion === 'agregar' ? 'Agregar Producto' : 'Editar Producto'; ?></h2>
        <form method="POST" action="productos.php?accion=<?php echo $accion; ?><?php echo $id ? '&id=' . $id : ''; ?>" enctype="multipart/form-data" class="max-w-xl mx-auto bg-white p-8 rounded-lg shadow-lg space-y-6">
            <div>
                <label for="nombre" class="block mb-2 font-medium text-gray-700">Nombre:</label>
                <input type="text" id="nombre" name="nombre" required value="<?php echo htmlspecialchars($productoEditar['nombre'] ?? '', ENT_QUOTES); ?>" class="w-full px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-yellow-600" />
            </div>
            <div>
                <label for="descripcion" class="block mb-2 font-medium text-gray-700">Descripción:</label>
                <textarea id="descripcion" name="descripcion" required class="w-full px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-yellow-600"><?php echo htmlspecialchars($productoEditar['descripcion'] ?? '', ENT_QUOTES); ?></textarea>
            </div>
            <div>
                <label for="precio" class="block mb-2 font-medium text-gray-700">Precio:</label>
                <input type="number" step="0.01" id="precio" name="precio" required value="<?php echo htmlspecialchars(isset($productoEditar['precio']) ? number_format(floatval($productoEditar['precio']), 2, '.', '') : '', ENT_QUOTES); ?>" class="w-full px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-yellow-600" />
            </div>
            <div>
                <label for="categoria" class="block mb-2 font-medium text-gray-700">Categoría:</label>
                <input type="text" id="categoria" name="categoria" required value="<?php echo htmlspecialchars($productoEditar['categoria'] ?? '', ENT_QUOTES); ?>" class="w-full px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-yellow-600" />
            </div>
            <div>
                <label for="descuento" class="block mb-2 font-medium text-gray-700">Descuento (%):</label>
                <input type="number" id="descuento" name="descuento" min="0" max="100" value="<?php echo htmlspecialchars($productoEditar['descuento'] ?? 0, ENT_QUOTES); ?>" class="w-full px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-yellow-600" />
            </div>
            <div>
                <label for="imagen" class="block mb-2 font-medium text-gray-700"><?php echo ($accion === 'editar' && !empty($productoEditar['imagen'])) ? 'Cambiar Imagen (opcional):' : 'Imagen:'; ?></label>
                <input type="file" id="imagen" name="imagen" accept="image/*" <?php echo $accion === 'agregar' ? 'required' : ''; ?> class="w-full px-3 py-2 border border-gray-300 rounded-md" />
                 <?php if ($accion === 'editar' && !empty($productoEditar['imagen'])): ?>
                    <div class="mt-2">
                        <p class="text-sm text-gray-600">Imagen actual:</p>
                        <img src="<?php echo htmlspecialchars($productoEditar['imagen'], ENT_QUOTES); ?>" width="100" alt="Imagen actual" class="border rounded-md shadow-sm"/>
                        <input type="hidden" name="imagen_actual" value="<?php echo htmlspecialchars($productoEditar['imagen'], ENT_QUOTES); ?>" />
                    </div>
                <?php endif; ?>
            </div>
            <div class="flex items-center space-x-4">
                <button type="submit" class="bg-gradient-to-r from-yellow-900 to-yellow-700 text-white px-6 py-2 rounded-md shadow-md hover:from-yellow-700 hover:to-yellow-500 transition"> <?php echo $accion === 'agregar' ? 'Agregar' : 'Actualizar'; ?> </button>
                <a href="productos.php" class="text-yellow-900 font-semibold hover:underline px-4 py-2 rounded-md border border-yellow-900 hover:bg-yellow-100 transition">Cancelar</a>
            </div>
        </form>
        <?php else: ?>
        <a href="productos.php?accion=agregar" class="inline-block mb-4 bg-yellow-700 text-white px-4 py-2 rounded-md shadow hover:bg-yellow-600 transition">Agregar Producto</a>
        <div class="overflow-x-auto">
            <table class="w-full border-collapse shadow rounded-lg overflow-hidden">
                <thead class="bg-yellow-900 text-yellow-100 uppercase font-semibold">
                    <tr>
                        <th class="p-3 border border-yellow-700">ID</th>
                        <th class="p-3 border border-yellow-700">Imagen</th>
                        <th class="p-3 border border-yellow-700">Nombre</th>
                        <th class="p-3 border border-yellow-700">Descripción</th>
                        <th class="p-3 border border-yellow-700">Precio</th>
                        <th class="p-3 border border-yellow-700">Categoría</th>
                        <th class="p-3 border border-yellow-700">Descuento</th>
                        <th class="p-3 border border-yellow-700">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($productos)): ?>
                        <?php foreach ($productos as $producto): ?>
                        <tr class="even:bg-yellow-50 hover:bg-yellow-100">
                            <td class="p-3 border border-yellow-700"><?php echo $producto['id']; ?></td>
                            <td class="p-3 border border-yellow-700">
                                <?php if (!empty($producto['imagen'])): ?>
                                    <img src="<?php echo htmlspecialchars($producto['imagen'], ENT_QUOTES); ?>" width="60" alt="Imagen del producto" class="mx-auto border rounded-md shadow-sm" />
                                <?php else: ?>
                                    <span class="text-gray-500">Sin imagen</span>
                                <?php endif; ?>
                            </td>
                            <td class="p-3 border border-yellow-700"><?php echo htmlspecialchars($producto['nombre'], ENT_QUOTES); ?></td>
                            <td class="p-3 border border-yellow-700"><?php echo htmlspecialchars($producto['descripcion'], ENT_QUOTES); ?></td>
                            <td class="p-3 border border-yellow-700 text-right">$<?php echo number_format(floatval($producto['precio']), 2, ',', '.'); ?></td>
                            <td class="p-3 border border-yellow-700"><?php echo htmlspecialchars($producto['categoria'] ?? '', ENT_QUOTES); ?></td>
                            <td class="p-3 border border-yellow-700 text-center"><?php echo isset($producto['descuento']) ? $producto['descuento'] . '%' : '0%'; ?></td>
                            <td class="p-3 border border-yellow-700 text-center whitespace-nowrap">
                                <a href="productos.php?accion=editar&id=<?php echo $producto['id']; ?>" 
                                   class="inline-block px-3 py-1.5 text-sm font-semibold rounded-md shadow-sm
                                          bg-yellow-600 text-white hover:bg-yellow-500 
                                          focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-yellow-700
                                          mr-2 mb-1 sm:mb-0">
                                    Editar
                                </a>
                                <a href="productos.php?accion=eliminar&id=<?php echo $producto['id']; ?>" 
                                   onclick="return confirm('¿Estás seguro de eliminar este producto?');" 
                                   class="inline-block px-3 py-1.5 text-sm font-semibold rounded-md shadow-sm
                                          bg-red-700 text-white hover:bg-red-600 
                                          focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-red-800
                                          mb-1 sm:mb-0">
                                    Eliminar
                                </a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="8" class="p-3 border border-yellow-700 text-center text-gray-500">No hay productos para mostrar.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
        <?php endif; ?>
    </main>
</body>
</html>
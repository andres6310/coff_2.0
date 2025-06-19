<?php
session_start();
require '../conexion.php'; 

error_reporting(E_ALL);
ini_set('display_errors', 1);

function limpiarDatos($datos) {
    $datos = trim($datos);
    $datos = stripslashes($datos);
    $datos = htmlspecialchars($datos, ENT_QUOTES, 'UTF-8'); 
    return $datos;
}

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
    $email = limpiarDatos($_POST['email'] ?? '');
    $rol = limpiarDatos($_POST['rol'] ?? 'cliente');
  
    $password = trim($_POST['password'] ?? ''); 

    if (empty($nombre) || empty($email) || empty($rol)) {
        $error = "Todos los campos excepto la contraseña (al editar) son requeridos.";
    } else {
        if ($accion === 'agregar') {
            if (empty($password)) {
                $error = "La contraseña es requerida para agregar un nuevo usuario.";
            } else {
                $stmt_check = $conexion->prepare("SELECT id FROM usuarios WHERE email = ?");
                if ($stmt_check) {
                    $stmt_check->bind_param("s", $email);
                    $stmt_check->execute();
                    $stmt_check->store_result();
                    if ($stmt_check->num_rows > 0) {
                        $error = "El correo electrónico ya está registrado.";
                    }
                    $stmt_check->close();
                } else {
                    $error = "Error preparando la consulta de verificación de email: " . $conexion->error;
                }

                if (empty($error)) { // Solo proceder si no hay errores previos
                    $password_hash = password_hash($password, PASSWORD_DEFAULT);
                    $stmt = $conexion->prepare("INSERT INTO usuarios (nombre, email, password, rol) VALUES (?, ?, ?, ?)");
                    if ($stmt) {
                        $stmt->bind_param("ssss", $nombre, $email, $password_hash, $rol);
                        if ($stmt->execute()) {
                            $stmt->close();
                            header('Location: usuarios.php?status=added'); 
                            exit;
                        } else {
                            $error = "Error al agregar usuario: " . $stmt->error;
                        }
                        if(isset($stmt) && $stmt instanceof mysqli_stmt) $stmt->close();
                    } else {
                        $error = "Error preparando la consulta de inserción: " . $conexion->error;
                    }
                }
            }
        } elseif ($accion === 'editar' && $id > 0) {
            $sql_update = "";
            $params_types = "";
            $params_values = [];

            if (!empty($password)) {
                $password_hash = password_hash($password, PASSWORD_DEFAULT);
                $sql_update = "UPDATE usuarios SET nombre=?, email=?, password=?, rol=? WHERE id=?";
                $params_types = "ssssi";
                $params_values = [$nombre, $email, $password_hash, $rol, $id];
            } else {
                $sql_update = "UPDATE usuarios SET nombre=?, email=?, rol=? WHERE id=?";
                $params_types = "sssi";
                $params_values = [$nombre, $email, $rol, $id];
            }

            $stmt = $conexion->prepare($sql_update);
            if ($stmt) {
               
                $stmt->bind_param($params_types, ...$params_values);
                if ($stmt->execute()) {
                    $stmt->close();
                    header('Location: usuarios.php?status=updated');
                    exit;
                } else {
                    $error = "Error al actualizar usuario: " . $stmt->error;
                }
                 if(isset($stmt) && $stmt instanceof mysqli_stmt) $stmt->close();
            } else {
                 $error = "Error preparando la consulta de actualización: " . $conexion->error;
            }
        }
    }
} elseif ($accion === 'eliminar' && $id > 0) { 
    $stmt = $conexion->prepare("DELETE FROM usuarios WHERE id=?");
    if ($stmt) {
        $stmt->bind_param("i", $id);
        if ($stmt->execute()) {
            $stmt->close();
            header('Location: usuarios.php?status=deleted');
            exit;
        } else {
            $error = "Error al eliminar usuario: " . $stmt->error;
        }
        if(isset($stmt) && $stmt instanceof mysqli_stmt) $stmt->close();
    } else {
        $error = "Error preparando la consulta de eliminación: " . $conexion->error;
    }
}


// --- OBTENER DATOS PARA MOSTRAR ---
$usuarios = []; 
$resultado = $conexion->query("SELECT id, nombre, email, rol FROM usuarios ORDER BY id DESC");

if ($resultado) {
    $usuarios = $resultado->fetch_all(MYSQLI_ASSOC);
    $resultado->free(); 
} else {
   
    if(empty($error)) $error = "Error al obtener la lista de usuarios: " . $conexion->error;
}


if (isset($_GET['status'])) {
    switch ($_GET['status']) {
        case 'added': $success_message = 'Usuario agregado exitosamente.'; break;
        case 'updated': $success_message = 'Usuario actualizado exitosamente.'; break;
        case 'deleted': $success_message = 'Usuario eliminado exitosamente.'; break;
    }
}

?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Gestión de Usuarios - Panel de Administración</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins&display=swap" rel="stylesheet" />
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet" />
    <link rel="stylesheet" href="admin-style.css" /> 
    <link rel="icon" href="../img/favicon_round.png" type="image/x-icon" />
</head>
<body class="bg-gray-50 font-poppins min-h-screen flex flex-col">
    <header class="bg-gradient-to-r from-yellow-900 via-yellow-700 to-yellow-600 text-white p-6 text-center shadow-md">
        <h1 class="text-3xl font-bold tracking-wide">Gestión de Usuarios</h1>
    </header>
    <?php $currentPage = basename($_SERVER['PHP_SELF']); ?>
    <nav class="bg-yellow-900 shadow-inner flex flex-wrap justify-center">
        <a href="index.php" class="text-yellow-100 px-6 py-4 font-semibold hover:bg-yellow-700 hover:text-yellow-300 transition <?php echo ($currentPage == 'index.php') ? 'bg-yellow-700 text-yellow-300' : ''; ?>">Inicio</a>
        <a href="productos.php" class="text-yellow-100 px-6 py-4 font-semibold hover:bg-yellow-700 hover:text-yellow-300 transition <?php echo ($currentPage == 'productos.php') ? 'bg-yellow-700 text-yellow-300' : ''; ?>">Gestión de Productos</a>
        <a href="pedidos.php" class="text-yellow-100 px-6 py-4 font-semibold hover:bg-yellow-700 hover:text-yellow-300 transition <?php echo ($currentPage == 'pedidos.php') ? 'bg-yellow-700 text-yellow-300' : ''; ?>">Gestión de Pedidos</a>
        <a href="usuarios.php" class="text-yellow-100 px-6 py-4 font-semibold hover:bg-yellow-700 hover:text-yellow-300 transition <?php echo ($currentPage == 'usuarios.php') ? 'bg-yellow-700 text-yellow-300' : ''; ?>">Gestión de Usuarios</a>
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
            $usuarioEditar = null;
            if ($accion === 'editar' && $id > 0) {
                $stmt = $conexion->prepare("SELECT id, nombre, email, rol FROM usuarios WHERE id=?");
                if ($stmt) {
                    $stmt->bind_param("i", $id);
                    $stmt->execute();
                    $resultado_edit = $stmt->get_result();
                    $usuarioEditar = $resultado_edit->fetch_assoc();
                    $stmt->close();
                    if (!$usuarioEditar) {
                        echo "<p class='text-red-500'>Usuario no encontrado para editar.</p>";
                        
                    }
                } else {
                     echo "<p class='text-red-500'>Error al preparar la consulta para editar.</p>";
                }
            }
        ?>
   
        <h2 class="text-3xl font-bold mb-8 text-center"><?php echo $accion === 'agregar' ? 'Agregar Usuario' : 'Editar Usuario'; ?></h2>
        <form method="POST" action="usuarios.php?accion=<?php echo $accion; ?><?php echo $id ? '&id=' . $id : ''; ?>" class="max-w-xl mx-auto bg-white p-8 rounded-lg shadow-lg space-y-6">
            <div>
                <label for="nombre" class="block mb-2 font-medium text-gray-700">Nombre:</label>
                <input type="text" id="nombre" name="nombre" required value="<?php echo htmlspecialchars($usuarioEditar['nombre'] ?? '', ENT_QUOTES); ?>" class="w-full px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-yellow-600" />
            </div>
            <div>
                <label for="email" class="block mb-2 font-medium text-gray-700">Email:</label>
                <input type="email" id="email" name="email" required value="<?php echo htmlspecialchars($usuarioEditar['email'] ?? '', ENT_QUOTES); ?>" class="w-full px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-yellow-600" />
            </div>
            <div>
                <label for="password" class="block mb-2 font-medium text-gray-700"><?php echo ($accion === 'editar' && $usuarioEditar) ? 'Nueva Contraseña (dejar vacío para no cambiar)' : 'Contraseña'; ?>:</label>
                <input type="password" id="password" name="password" <?php echo $accion === 'agregar' ? 'required' : ''; ?> class="w-full px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-yellow-600" />
            </div>
            <div>
                <label for="rol" class="block mb-2 font-medium text-gray-700">Rol:</label>
                <select id="rol" name="rol" required class="w-full px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-yellow-600">
                    <option value="cliente" <?php echo (isset($usuarioEditar['rol']) && $usuarioEditar['rol'] === 'cliente') ? 'selected' : ''; ?>>Cliente</option>
                    <option value="usuario" <?php echo (isset($usuarioEditar['rol']) && $usuarioEditar['rol'] === 'usuario') ? 'selected' : ''; ?>>Usuario</option>
                    <option value="admin" <?php echo (isset($usuarioEditar['rol']) && $usuarioEditar['rol'] === 'admin') ? 'selected' : ''; ?>>Administrador</option>
                </select>
            </div>
            <div class="flex items-center space-x-4">
                <button type="submit" class="bg-gradient-to-r from-yellow-900 to-yellow-700 text-white px-6 py-2 rounded-md shadow-md hover:from-yellow-700 hover:to-yellow-500 transition"> <?php echo $accion === 'agregar' ? 'Agregar' : 'Actualizar'; ?> </button>
                <a href="usuarios.php" class="text-yellow-900 font-semibold hover:underline px-4 py-2 rounded-md border border-yellow-900 hover:bg-yellow-100 transition">Cancelar</a>
            </div>
        </form>
        <?php else: ?>
        <a href="usuarios.php?accion=agregar" class="inline-block mb-4 bg-yellow-700 text-white px-4 py-2 rounded-md shadow hover:bg-yellow-600 transition">Agregar Usuario</a>
        <div class="overflow-x-auto"> 
            <table class="w-full border-collapse shadow rounded-lg overflow-hidden">
                <thead class="bg-yellow-900 text-yellow-100 uppercase font-semibold">
                    <tr>
                        <th class="p-3 border border-yellow-700">ID</th>
                        <th class="p-3 border border-yellow-700">Nombre</th>
                        <th class="p-3 border border-yellow-700">Email</th>
                        <th class="p-3 border border-yellow-700">Rol</th>
                        <th class="p-3 border border-yellow-700">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($usuarios)): ?>
                        <?php foreach ($usuarios as $usuario): ?>
                        <tr class="even:bg-yellow-50 hover:bg-yellow-100">
                            <td class="p-3 border border-yellow-700"><?php echo $usuario['id']; ?></td>
                            <td class="p-3 border border-yellow-700"><?php echo htmlspecialchars($usuario['nombre'], ENT_QUOTES); ?></td>
                            <td class="p-3 border border-yellow-700"><?php echo htmlspecialchars($usuario['email'], ENT_QUOTES); ?></td>
                            <td class="p-3 border border-yellow-700"><?php echo htmlspecialchars($usuario['rol'], ENT_QUOTES); ?></td>
                            <td class="p-3 border border-yellow-700 text-center whitespace-nowrap"> 
                                <a href="usuarios.php?accion=editar&id=<?php echo $usuario['id']; ?>" 
                                   class="inline-block px-3 py-1.5 text-sm font-semibold rounded-md shadow-sm
                                          bg-yellow-600 text-white hover:bg-yellow-500 
                                          focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-yellow-700
                                          mr-2 mb-1 sm:mb-0"> 
                                    Editar
                                </a>
                                <a href="usuarios.php?accion=eliminar&id=<?php echo $usuario['id']; ?>" 
                                   onclick="return confirm('¿Estás seguro de eliminar este usuario?');" 
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
                            <td colspan="5" class="p-3 border border-yellow-700 text-center">No hay usuarios para mostrar.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
        <?php endif; ?>
    </main>
</body>
</html>
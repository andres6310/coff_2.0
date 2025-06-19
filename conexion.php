<?php
// Configuración de la conexión a la base de datos
$servidor = "localhost";
$usuario = "root";
$password = "";
$base_datos = "coffe_galactic";

// Crear conexión
$conexion = new mysqli($servidor, $usuario, $password, $base_datos);

// Verificar conexión
if ($conexion->connect_error) {
    die("Error de conexión: " . $conexion->connect_error);
}

// Establecer codificación de caracteres
$conexion->set_charset("utf8");

// Declaración condicional de la función
if (!function_exists('limpiarDatos')) {
    function limpiarDatos($datos) {
        $datos = trim($datos);
        $datos = stripslashes($datos);
        $datos = htmlspecialchars($datos);
        return $datos;
    }
}
?>

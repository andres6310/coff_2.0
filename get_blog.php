<?php
header('Content-Type: application/json');

if (!isset($_GET['id'])) {
    echo json_encode(['error' => 'No se proporcionó el ID del blog.']);
    exit;
}

$id = intval($_GET['id']);

// Simulación de contenido de blogs (en un caso real, se consultaría la base de datos)
$blogs = [
    1 => [
        'title' => 'El arte del café: secretos para un sabor perfecto',
        'content' => 'Aprende las técnicas esenciales para preparar un café delicioso en casa, desde la molienda hasta la temperatura del agua, y disfruta de una experiencia única en cada taza.'
    ],
    2 => [
        'title' => 'Beneficios del café orgánico para la salud',
        'content' => 'El café orgánico no solo es mejor para el medio ambiente, sino que también ofrece múltiples beneficios para la salud, incluyendo antioxidantes y menor exposición a pesticidas.'
    ],
    3 => [
        'title' => 'Cómo elegir el mejor grano de café para ti',
        'content' => 'Descubre cómo seleccionar granos de café según tu preferencia de sabor, método de preparación y origen, para que cada taza sea perfecta para ti.'
    ]
];

if (array_key_exists($id, $blogs)) {
    echo json_encode($blogs[$id]);
} else {
    echo json_encode(['error' => 'Blog no encontrado.']);
}
?>

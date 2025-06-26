<?php
session_start();

require 'conexion.php'; 


$nombre_usuario = '';
$productos_destacados = []; 


if (isset($_SESSION['usuario_id'])) {
    $usuario_id = $_SESSION['usuario_id'];
    if ($conexion instanceof mysqli) { 
        $stmt_user = $conexion->prepare("SELECT nombre FROM usuarios WHERE id = ?");
        if ($stmt_user) {
            $stmt_user->bind_param("i", $usuario_id);
            $stmt_user->execute();
            $stmt_user->bind_result($nombre_usuario);
            $stmt_user->fetch();
            $stmt_user->close();
        } else {
            error_log("Error preparando la consulta de usuario: " . $conexion->error);
        }
    } else {
        error_log("Conexión a BD no válida al obtener datos de usuario.");
    }
}

if ($conexion instanceof mysqli) { 
    $search_term = '';
    $is_search = false;
    if (isset($_GET['search']) && !empty(trim($_GET['search']))) {
        $search_term = trim($_GET['search']);
        $search_term_esc = $conexion->real_escape_string($search_term);
        $sql_productos = "SELECT id, nombre, descripcion, precio, descuento, imagen 
                          FROM productos 
                          WHERE imagen IS NOT NULL AND imagen != '' 
                          AND nombre LIKE '%$search_term_esc%'
                          ORDER BY id DESC";
        $is_search = true;
    } else {
        $sql_productos = "SELECT id, nombre, descripcion, precio, descuento, imagen 
                          FROM productos 
                          WHERE imagen IS NOT NULL AND imagen != '' 
                          ORDER BY id DESC 
                          LIMIT 10";
    }

    $query_productos = $conexion->query($sql_productos);
    if ($query_productos) {
        $productos_destacados = $query_productos->fetch_all(MYSQLI_ASSOC);
        $query_productos->free();
    } else {
        error_log("Error obteniendo productos destacados: " . $conexion->error);
    }
} else {
    error_log("Conexión a BD no válida al obtener productos destacados.");
}

if (!function_exists('limpiarDatos')) {
    function limpiarDatos($datos) {
        $datos = trim($datos);
        $datos = stripslashes($datos);
        $datos = htmlspecialchars($datos, ENT_QUOTES, 'UTF-8');
        return $datos;
    }
}

?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Coffe Galactic</title>
    <link rel="stylesheet" href="./style.css" /> 
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" integrity="sha512-DTOQO9RWCH3ppGqcWaEA1BIZOC6xxalwEsw9c2QQeAIftl+Vegovlnee1c9QX4TctnWMn13TZye+giMm8e2LwA==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/npm/toastify-js/src/toastify.min.css">
    <link rel="shortcut icon" href="./img/favicon.ico" type="image/x-icon">
    

</head>
<body>
    <!-- ========================= HEADER ========================= -->
    <header>
        <div class="container-hero">
            <div class="container hero">
                <img src="img/icono.png.png" alt="Logo" class="logo1-img" />
                <div class="container-logo">
                    <i class="fa-solid fa-mug-hot"></i>
                    <h1 class="logo"><a href="/">Coffee Galactic</a></h1>
                </div>
                <div class="container-user">
                    <i class="fa-solid fa-user" id="user-icon"></i>
                    <?php if (!empty($nombre_usuario)): ?>
                        <span class="user-name" style="margin-left: 8px; font-weight: bold;"><?php echo htmlspecialchars($nombre_usuario, ENT_QUOTES, 'UTF-8'); ?></span>
                    <?php endif; ?>
                    <div id="user-dropdown" class="user-dropdown">
                        <?php if(isset($_SESSION['usuario_id'])): ?>
                            <a href="LOGIN/logout.php">Cerrar sesión</a>
                        <?php else: ?>
                            <a href="./LOGIN/login.php">Iniciar sesión</a> 
                            
                        <?php endif; ?>
                    </div>
                    <?php if(isset($_SESSION['usuario_id'])): ?>
                        <button class="btn-login" onclick="window.location.href='LOGIN/logout.php'">Cerrar sesión</button>
                    <?php else: ?>
                        <button class="btn-login" onclick="window.location.href='./LOGIN/login.php'">Iniciar sesión</button>
                    <?php endif; ?>

                    <button id="compare-action-button" class="compare-button" style="display: none;">
                        Comparar (<span id="compare-count">0</span>)
                        <i class="fa-solid fa-code-compare"></i>
                    </button>

                    <i class="fa-solid fa-basket-shopping" id="cart-toggle-icon"></i>
                    <div class="content-shopping-cart">
                        <span class="text">Carrito</span>
                        <span class="number">(0)</span> 
                    </div>
                </div>
            </div>
        </div>

        <div class="container-navbar">
            <nav class="navbar container">
                <i class="fa-solid fa-bars"></i>
                <ul class="menu">
                    <li><a href="#inicio">Inicio</a></li>
                    <li class="menu-item has-submenu">
                        <a href="#cafes">Cafés</a>
                    </li>
                    <li><a href="#blog">Blog</a></li>
                    <li><a href="#contacto">Contacto</a></li>
                </ul>
    <form class="search-form" method="GET" action="index.php">
        <input type="search" name="search" placeholder="Buscar..." value="<?php echo isset($_GET['search']) ? htmlspecialchars($_GET['search'], ENT_QUOTES, 'UTF-8') : ''; ?>" />
        <button class="btn-search" type="submit">
            <i class="fa-solid fa-magnifying-glass"></i>
        </button>
    </form>
            </nav>
        </div>
    </header>
    <!-- ========================= FIN HEADER ========================= -->

    <!-- ========================= BANNER / SLIDER ========================= -->
    <section class="banner" id="inicio">
        <div class="slider">
            <div class="slide active" style="background-image: url('img/banner.jpg');"> 
                <div class="content-banner"> <p>Café Delicioso</p> <h2>100% Natural <br />Café Fresco</h2> </div> </div>
            <div class="slide" style="background-image: url('img/blog-3.jpg');"> 
                <div class="content-banner"> <p>Café Aromático</p> <h2>El mejor sabor <br />para ti</h2> </div> </div>
            <div class="slide" style="background-image: url('img/gallery2.jpg');"> 
                <div class="content-banner"> <p>Café Orgánico</p> <h2>Disfruta la naturaleza <br />en cada taza</h2> </div> </div>
        </div>
    </section>
    <!-- ========================= FIN BANNER / SLIDER ========================= -->

    <!-- ========================= CONTENIDO PRINCIPAL ========================= -->
    <main class="main-content">
        <section class="container container-features">
            <div class="card-feature"> <i class="fa-solid fa-plane-up"></i> <div class="feature-content"> <span>Envío gratuito a nivel local</span> <p>En pedido superior a $30.000</p> </div> </div>
            <div class="card-feature"> <i class="fa-solid fa-wallet"></i> <div class="feature-content"> <span>Contrareentrega</span> <p>100% garantía de devolución de dinero</p> </div> </div>
            <div class="card-feature"> <i class="fa-solid fa-gift"></i> <div class="feature-content"> <span>Tarjeta regalo especial</span> <p>Ofrece bonos especiales con regalo</p> </div> </div>
            <div class="card-feature"> <i class="fa-solid fa-headset"></i> <div class="feature-content"> <span>Servicio al cliente 24/7</span> <p>LLámenos 24/7 al 300-750-2697</p> </div> </div>
        </section>

        <section class="container top-categories" id="cafes">
            <h1 class="heading-1">Mejores Categorías</h1>
            <div class="container-categories"> 
                <div class="card-category category-moca"> <p>Café moca</p> <span>Ver más</span> </div> 
              <div class="card-category category-cofepro"> <p>coffepro</p> <span>Ver más</span> </div> 
                <div class="card-category category-capuchino-category"> <p>Capuchino</p> <span>Ver más</span> </div> 
            </div>
        </section>

        <!-- SECCIÓN MEJORES PRODUCTOS -->
        <section class="container top-products">
            <h1 class="heading-1">Mejores Productos</h1>
            <div id="mensaje-confirmacion" style="margin: 20px 0;"></div>
            <div class="container-options">
                <span class="active">Destacados</span> <span>Más recientes</span> <span>Mejores Vendidos</span>
            </div>
            <div class="container-products">
                <?php 
                if (isset($productos_destacados) && !empty($productos_destacados)):
                    foreach ($productos_destacados as $producto): 
                        
                        $rutaImagenDesdeBD = $producto['imagen'] ?? null; 
                        $imagenPorDefecto = './img/cafe-australiano.jpg'; 
                        $rutaImagenParaMostrar = $imagenPorDefecto;

                        if (!empty($rutaImagenDesdeBD)) {
                            $rutaWebAccesible = 'admin/' . $rutaImagenDesdeBD; 
                            if (file_exists(__DIR__ . '/' . $rutaWebAccesible)) {
                                $rutaImagenParaMostrar = $rutaWebAccesible;
                            } else {
                        
                            }
                        }
                ?>
                <div class="card-product" 
                     data-product-id="<?php echo htmlspecialchars($producto['id'] ?? '', ENT_QUOTES, 'UTF-8'); ?>" 
                     data-description="<?php echo htmlspecialchars($producto['descripcion'] ?? '', ENT_QUOTES, 'UTF-8'); ?>" 
                     data-image-large="<?php echo htmlspecialchars($rutaImagenParaMostrar, ENT_QUOTES, 'UTF-8'); ?>">
                    <div class="container-img">
                        <img class="<?php echo !empty($search_term) ? 'search-result-img' : ''; ?>" src="<?php echo htmlspecialchars($rutaImagenParaMostrar, ENT_QUOTES, 'UTF-8'); ?>" 
                             alt="<?php echo htmlspecialchars($producto['nombre'] ?? 'Producto', ENT_QUOTES, 'UTF-8'); ?>" />
                        
                        <?php if (isset($producto['descuento']) && intval($producto['descuento']) > 0): ?>
                        <span class="discount">-<?php echo intval($producto['descuento']); ?>%</span>
                        <?php endif; ?>

                        <div class="button-group">
                            <span><i class="fa-regular fa-eye"></i></span>
                            <span><i class="fa-regular fa-heart"></i></span>
                            <span><i class="fa-solid fa-code-compare"></i></span>
                        </div>
                    </div>
                    <div class="content-card-product">
                        <div class="stars">
                            <i class="fa-solid fa-star"></i> <i class="fa-solid fa-star"></i> <i class="fa-solid fa-star"></i> <i class="fa-solid fa-star"></i> <i class="fa-regular fa-star"></i>
                        </div>
                        <h3><?php echo htmlspecialchars($producto['nombre'] ?? 'Nombre no disponible', ENT_QUOTES, 'UTF-8'); ?></h3>
                        <span class="add-cart"><i class="fa-solid fa-basket-shopping"></i></span>
                        <p class="price">$<?php echo number_format(floatval($producto['precio'] ?? 0), 0, ',', '.'); ?></p>
                    </div>
                </div>
                <?php 
                    endforeach; 
                else:
                ?>
                <p>No hay productos destacados disponibles en este momento.</p>
                <?php endif; ?>
            </div>
        </section>
        <!-- FIN SECCIÓN MEJORES PRODUCTOS -->

       <section class="container specials">
  <h1 class="heading-1">Especiales</h1>
  <div class="container-products">

    <div class="card-product" data-product-id="cafe-irish-especial" data-description="Nuestra versión especial del clásico irlandés, con un toque único de la casa y whisky seleccionado." data-image-large="img/cafe-irish.jpg">
      <div class="container-img">
        <img src="img/cafe-irish.jpg" alt="Cafe Irish Especial" />
        <span class="discount">-13%</span>
        <div class="button-group">
          <span><i class="fa-regular fa-eye"></i></span>
          <span><i class="fa-regular fa-heart"></i></span>
          <span><i class="fa-solid fa-code-compare"></i></span>
        </div>
      </div>
      <div class="content-card-product">
        <div class="stars">
          <i class="fa-solid fa-star"></i>
          <i class="fa-solid fa-star"></i>
          <i class="fa-solid fa-star"></i>
          <i class="fa-solid fa-star"></i>
          <i class="fa-solid fa-star-half-stroke"></i>
        </div>
        <h3>Cafe Irish</h3>
        <span class="add-cart"><i class="fa-solid fa-basket-shopping"></i></span>
        <p class="price">$35.000</p>
      </div>
    </div>

    <div class="card-product" data-product-id="cafe-ingles-especial" data-description="Un blend especial de granos arábica con notas achocolatadas, perfecto para un desayuno inglés." data-image-large="img/cafe-ingles.jpg">
      <div class="container-img">
        <img src="img/cafe-ingles.jpg" alt="Cafe Inglés Especial" />
        <span class="discount">-22%</span>
        <div class="button-group">
          <span><i class="fa-regular fa-eye"></i></span>
          <span><i class="fa-regular fa-heart"></i></span>
          <span><i class="fa-solid fa-code-compare"></i></span>
        </div>
      </div>
      <div class="content-card-product">
        <div class="stars">
          <i class="fa-solid fa-star"></i>
          <i class="fa-solid fa-star"></i>
          <i class="fa-solid fa-star"></i>
          <i class="fa-solid fa-star"></i>
          <i class="fa-regular fa-star"></i>
        </div>
        <h3>Cafe Inglés</h3>
        <span class="add-cart"><i class="fa-solid fa-basket-shopping"></i></span>
        <p class="price">$20.000</p>
      </div>
    </div>

    <div class="card-product" data-product-id="cafe-viena" data-description="Elegancia en una taza: espresso doble cubierto con crema batida y un toque de chocolate." data-image-large="img/cafe-viena.jpg">
      <div class="container-img">
        <img src="img/cafe-viena.jpg" alt="Cafe Viena" />
        <span class="discount">-30%</span>
        <div class="button-group">
          <span><i class="fa-regular fa-eye"></i></span>
          <span><i class="fa-regular fa-heart"></i></span>
          <span><i class="fa-solid fa-code-compare"></i></span>
        </div>
      </div>
      <div class="content-card-product">
        <div class="stars">
          <i class="fa-solid fa-star"></i>
          <i class="fa-solid fa-star"></i>
          <i class="fa-solid fa-star"></i>
          <i class="fa-solid fa-star"></i>
          <i class="fa-solid fa-star"></i>
        </div>
        <h3>Cafe Viena</h3>
        <span class="add-cart"><i class="fa-solid fa-basket-shopping"></i></span>
        <p class="price">$15.000</p>
      </div>
    </div>

    <div class="card-product" data-product-id="cafe-liqueurs" data-description="Para los aventureros: una selección de nuestros mejores cafés infusionados con licores premium." data-image-large="img/cafe-liqueurs.jpg">
      <div class="container-img">
        <img src="img/cafe-liqueurs.jpg" alt="Cafe Liqueurs" />
        <div class="button-group">
          <span><i class="fa-regular fa-eye"></i></span>
          <span><i class="fa-regular fa-heart"></i></span>
          <span><i class="fa-solid fa-code-compare"></i></span>
        </div>
      </div>
      <div class="content-card-product">
        <div class="stars">
          <i class="fa-solid fa-star"></i>
          <i class="fa-solid fa-star"></i>
          <i class="fa-solid fa-star"></i>
          <i class="fa-solid fa-star-half-stroke"></i>
          <i class="fa-regular fa-star"></i>
        </div>
        <h3>Cafe Liqueurs</h3>
        <span class="add-cart"><i class="fa-solid fa-basket-shopping"></i></span>
        <p class="price">$30.000</p>
      </div>
    </div>

  </div>
</section>

  <section class="container blogs" id="blog">
  <h1 class="heading-1">Últimos Blogs</h1>
  <div class="container-blogs">

    <div class="card-blog" data-blog-id="1">
      <div class="container-img">
        <img src="img/blog-1.jpg" alt="Imagen Blog 1" />
        <div class="button-group-blog">
          <span><i class="fa-solid fa-magnifying-glass"></i></span>
          <span><i class="fa-solid fa-link"></i></span>
        </div>
      </div>
      <div class="content-blog">
        <h3>El arte del café: secretos para un sabor perfecto</h3>
        <span>29 Noviembre 2023</span>
        <p>Descubre cómo preparar el café ideal en casa con técnicas profesionales.</p>
        <div class="btn-read-more" data-blog-id="1">Leer más</div>
      </div>
    </div>

    <div class="card-blog" data-blog-id="2">
      <div class="container-img">
        <img src="img/blog-2.jpg" alt="Imagen Blog 2" />
        <div class="button-group-blog">
          <span><i class="fa-solid fa-magnifying-glass"></i></span>
          <span><i class="fa-solid fa-link"></i></span>
        </div>
      </div>
      <div class="content-blog">
        <h3>Beneficios del café orgánico para la salud</h3>
        <span>29 Noviembre 2023</span>
        <p>Conoce las ventajas de consumir café orgánico y cómo impacta tu bienestar.</p>
        <div class="btn-read-more" data-blog-id="2">Leer más</div>
      </div>
    </div>

    <div class="card-blog" data-blog-id="3">
      <div class="container-img">
        <img src="img/blog-3.jpg" alt="Imagen Blog 3" />
        <div class="button-group-blog">
          <span><i class="fa-solid fa-magnifying-glass"></i></span>
          <span><i class="fa-solid fa-link"></i></span>
        </div>
      </div>
      <div class="content-blog">
        <h3>Cómo elegir el mejor grano de café para ti</h3>
        <span>29 Noviembre 2023</span>
        <p>Tips para seleccionar granos de café según tu gusto y método de preparación.</p>
        <div class="btn-read-more" data-blog-id="3">Leer más</div>
      </div>
    </div>

  </div>
</section>

<!-- Modal para mostrar contenido detallado de blog -->
<style>
  /* Estilos modernos para el modal */
  .blog-modal {
      position: fixed;
      top: 0;
      left: 0;
      width: 100%;
      height: 100%;
      background-color: rgba(0, 0, 0, 0.6);
      display: none;
      align-items: center;
      justify-content: center;
      z-index: 9999;
    }

    .modal-overlay {
      display: none; /* Ya que no se usa en tu lógica */
    }

    .modal-content {
      background-color: #fff;
      padding: 30px;
      border-radius: 15px;
      width: 90%;
      max-width: 600px;
      text-align: center;
      position: relative;
      box-shadow: 0 0 20px rgba(0,0,0,0.3);
    }

    .close-button {
      position: absolute;
      top: 15px;
      right: 20px;
      font-size: 24px;
      background: none;
      border: none;
      color: #000;
      cursor: pointer;
    }

    .btn {
      margin-top: 20px;
    }

    .btn-close-modal {
      position: static;
      display: block;
      margin: 5px auto 0 auto;
      padding: 10px 70px; 
      font-size: 1rem;
      border-radius: 25px;
      background-color: #2980b9;
      border: none;
      color: white;
      cursor: pointer;
      transition: background-color 0.3s ease;
    }

    .btn-close-modal:hover {
      background-color: #1c6ca6;
    }
</style>

<div id="blog-modal" class="blog-modal">
  <div class="modal-overlay"></div>
  <div class="modal-content">
    <button id="close-blog-modal" class="close-button" aria-label="Cerrar modal">×</button>
    <h2 id="modal-blog-title">Cargando...</h2>
    <div id="modal-blog-content">Por favor espera mientras se carga el contenido.</div>
    <div class="btn">
      <button id="modal-close-btn" class="btn-close-modal">Regresar</button>
    </div>
  </div>
</div>

</script>
</main>
<section class="container contact" id="contacto"> </section>

    <!-- ========================= FIN CONTENIDO PRINCIPAL ========================= -->

    <!-- ========================= MODALES ========================= -->
    <div id="cart-modal" class="cart-modal">
  <div class="modal-overlay"></div>
  <div class="modal-content">
    <button id="close-modal-btn" class="close-button">×</button>
    <h2><i class="fa-solid fa-basket-shopping"></i> Tu Carrito</h2>
    <div id="cart-items-list" class="cart-items-list">
      <p class="empty-cart-message">Tu carrito está vacío.</p>
    </div>
    <div class="cart-summary">
      <p>Total: <span id="cart-total" class="cart-total-price">$0</span></p>
      <div class="cart-actions">
        
        <div id="paypal-button-container" style="margin-top: 10px;"></div>
        <button id="clear-cart-btn" class="btn-clear-cart" disabled>Vaciar Carrito</button>
      </div>
    </div>
  </div>
</div>

<div id="quick-view-modal" class="quick-view-modal">
  <div class="modal-overlay"></div>
  <div class="modal-content qv-modal-content">
    <button id="close-qv-modal-btn" class="close-button">×</button>
    <div class="qv-product-details">
      <div class="qv-image-container">
        <img id="qv-image" src="" alt="Vista rápida del producto" />
      </div>
      <div class="qv-info-container">
        <h2 id="qv-name">Nombre Producto</h2>
        <p class="qv-price"><span id="qv-price">$0</span></p>
        <p class="qv-description" id="qv-description">Cargando...</p>
        <button id="qv-add-to-cart-btn" class="boton-item qv-boton-item" data-product-id="">
          Añadir al Carrito
        </button>
      </div>
    </div>
  </div>
</div>

<div id="comparison-modal" class="comparison-modal">
  <div class="modal-overlay"></div>
  <div class="modal-content comparison-modal-content">
    <button id="close-comparison-modal-btn" class="close-button">×</button>
    <h2>Comparar Productos</h2>
    <div id="comparison-table" class="comparison-table">
      <p>Selecciona hasta 3 productos para comparar.</p>
    </div>
  </div>
</div>

    <!-- ========================= FIN MODALES ========================= -->

    <!-- Modal para información y edición de usuario -->
    <div id="user-info-modal" class="user-info-modal" style="display:none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background-color: rgba(0,0,0,0.6); align-items: center; justify-content: center; z-index: 10000;">
        <div class="modal-overlay" style="position: absolute; width: 100%; height: 100%;"></div>
        <div class="modal-content" style="background: white; padding: 20px; border-radius: 10px; width: 90%; max-width: 400px; position: relative; z-index: 10001;">
            <button id="close-user-info-modal" class="close-button" aria-label="Cerrar modal" style="position: absolute; top: 10px; right: 15px; font-size: 24px; background: none; border: none; cursor: pointer;">×</button>
            <h2>Información de Usuario</h2>
            <form id="user-info-form">
                <label for="user-name-input" style="display: block; margin-bottom: 8px; font-weight: bold;">Nombre:</label>
                <input type="text" id="user-name-input" name="user-name" value="<?php echo htmlspecialchars($nombre_usuario, ENT_QUOTES, 'UTF-8'); ?>" style="width: 100%; padding: 8px; margin-bottom: 15px; border: 1px solid #ccc; border-radius: 4px;" required />
                <div style="text-align: right;">
                    <button type="button" id="cancel-user-info-btn" style="margin-right: 10px; padding: 8px 15px; border: none; background-color: #ccc; border-radius: 4px; cursor: pointer;">Cancelar</button>
                    <button type="submit" id="save-user-info-btn" style="padding: 8px 15px; border: none; background-color: #2980b9; color: white; border-radius: 4px; cursor: pointer;">Guardar</button>
                </div>
            </form>
        </div>
    </div>

    <!-- ========================= FOOTER ========================= -->
   <footer class="footer">
  <div class="container container-footer">
    <div class="menu-footer">
      <div class="contact-info">
        <p class="title-footer">Información de Contacto</p>
        <ul>
          <li>Dirección: ........</li>
          <li>Teléfono: 300-750-2697</li>
          <li>Email: coffegalactic@support.com</li>
        </ul>
        <div class="social-icons">
          <span class="facebook"><i class="fa-brands fa-facebook-f"></i></span>
          <span class="twitter"><i class="fa-brands fa-twitter"></i></span>
          <span class="youtube"><i class="fa-brands fa-youtube"></i></span>
          <span class="pinterest"><i class="fa-brands fa-pinterest-p"></i></span>
          <span class="instagram"><a href="https://www.instagram.com/david_26pro?igsh=MTN5YWFjdmtlcjRhdw==" onclick="window.open(this.href, '_blank'); return false;" rel="noopener noreferrer"><i class="fa-brands fa-instagram"></i></a></span>
        </div>
      </div>

      <div class="information">
        <p class="title-footer">Información</p>
        <ul>
          <li><a href="#">Acerca de Nosotros</a></li>
          <li><a href="#">Politicas de Privacidad</a></li>
          <li><a href="#">Términos y condiciones</a></li>
          <li><a href="#">Contactános</a></li>
        </ul>
      </div>

      <div class="my-account">
        <p class="title-footer">Mi cuenta</p>
        <ul>
          <li><a href="#">Mi cuenta</a></li>
          <li><a href="#">Historial de ordenes</a></li>
          <li><a href="#">Boletín</a></li>
          <li><a href="#">Reembolsos</a></li>
        </ul>
      </div>


    <div class="copyright">
      <p>COFFE GALACTIC © 2024</p>
      <img src="img/payment.png" alt="Pagos">
    </div>
  </div>
</footer>
    <!-- ========================= FIN FOOTER ========================= -->

    <!-- ========================= PAYPAL ========================= -->

    <script type="text/javascript" src="https://cdn.jsdelivr.net/npm/toastify-js"></script>
<script src="https://www.paypal.com/sdk/js?client-id=AZcSVX_cxVWtvmB7hY81DckOc0XRmyoX5qKUlgojnf64-BeHR8f4sqD01t2-4Ed01ogymYcpKPPIssTS"></script> 

    <script src="./index.js"></script> 
    
  <!-- Chatbot de Voiceflow -->
<script type="text/javascript">
  (function(d, t) {
      var v = d.createElement(t), s = d.getElementsByTagName(t)[0];
      v.onload = function() {
        window.voiceflow.chat.load({
          verify: { projectID: '685c4944d000f98992e44e56' },
          url: 'https://general-runtime.voiceflow.com',
          versionID: 'production',
          voice: {
            url: "https://runtime-api.voiceflow.com"
          }
        });
      }
      v.src = "https://cdn.voiceflow.com/widget-next/bundle.mjs"; v.type = "text/javascript"; s.parentNode.insertBefore(v, s);
  })(document, 'script');
</script>
        <!-- Fin del Chatbot de Voiceflow -->
    
</body>
</html>
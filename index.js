document.addEventListener('DOMContentLoaded', function() {
    // --- Variables globales para elementos comunes del DOM ---
    const body = document.body;

    // Slider
    const slides = document.querySelectorAll('.slide');
    
    // Menú hamburguesa y menú principal
    const menuIcon = document.querySelector('.fa-bars');
    const menu = document.querySelector('.menu');

    // Dropdown de Usuario y Modal de Info Usuario (si existen en tu HTML)
    const userIcon = document.getElementById('user-icon');
    // const userDropdown = document.getElementById('user-dropdown'); // Comentado si no se usa directamente
    const userInfoModal = document.getElementById('user-info-modal');
    const closeUserInfoModalBtn = document.getElementById('close-user-info-modal');
    const userInfoForm = document.getElementById('user-info-form');
    const userNameInput = document.getElementById('user-name-input');
    const cancelUserInfoBtn = document.getElementById('cancel-user-info-btn');
    const userNameDisplay = document.querySelector('.user-name');

    // Carrito de Compras
    const cartModal = document.getElementById('cart-modal');
    const cartItemsList = document.getElementById('cart-items-list');
    const cartTotalElement = document.getElementById('cart-total');
    const closeModalBtnCart = document.getElementById('close-modal-btn'); // ID del botón cerrar en el modal del carrito
    const cartModalOverlay = cartModal ? cartModal.querySelector('.modal-overlay') : null;
    const clearCartBtn = document.getElementById('clear-cart-btn');
    const originalCheckoutBtn = document.querySelector('.btn-checkout'); // Botón "Proceder al Pago"
    const cartCounterElement = document.querySelector('.content-shopping-cart .number'); // Contador de ítems en el header
    
    const openCartIcon = document.getElementById('cart-toggle-icon'); // Icono para abrir el carrito
    const openCartTextContainer = document.querySelector('.content-shopping-cart'); // Contenedor del texto "Carrito" para abrirlo

    let cartItems = []; // Array para almacenar los ítems del carrito
    let cartTotal = 0;  // Total del carrito
    // let cartItemCount = 0; // Ya no es necesaria como variable global si se calcula en updateCartCounter

    // Vista Rápida (Quick View - QV)
    const quickViewModal = document.getElementById('quick-view-modal');
    const qvImage = document.getElementById('qv-image');
    const qvName = document.getElementById('qv-name');
    const qvPrice = document.getElementById('qv-price'); // El span que contiene el precio
    const qvDescription = document.getElementById('qv-description');
    const qvAddToCartBtn = document.getElementById('qv-add-to-cart-btn');
    const closeQvModalBtn = document.getElementById('close-qv-modal-btn');
    const qvModalOverlay = quickViewModal ? quickViewModal.querySelector('.modal-overlay') : null;
    // Seleccionar botones de vista rápida (asumiendo que es el primer span en .button-group)
    const quickViewButtons = document.querySelectorAll('.card-product .button-group span:nth-child(1)'); 

    // Comparación de Productos
    const comparisonModal = document.getElementById('comparison-modal');
    const comparisonTable = document.getElementById('comparison-table');
    const closeComparisonModalBtn = document.getElementById('close-comparison-modal-btn');
    const comparisonModalOverlay = comparisonModal ? comparisonModal.querySelector('.modal-overlay') : null;
    // Seleccionar botones de comparar (asumiendo que es el tercer span en .button-group)
    const compareButtons = document.querySelectorAll('.card-product .button-group span:nth-child(3)'); 
    const compareActionButton = document.getElementById('compare-action-button'); // Botón principal "Comparar (X)"
    const compareCountSpan = document.getElementById('compare-count');
    const MAX_COMPARE_ITEMS = 4;
    let comparisonList = new Set(); // Usar un Set para evitar duplicados y fácil manejo

    // Filtros de Productos (Tabs) y Búsqueda
    const filterOptions = document.querySelectorAll('.filter-option');
    const productListContainer = document.getElementById('product-list-container'); // Contenedor donde se listan los productos dinámicamente
    const searchForm = document.querySelector('.search-form');
    const searchInput = searchForm ? searchForm.querySelector('input[type="search"]') : null;
    // Para mensajes de "no resultados" (si los tienes por cada .container-products)
    const productContainersForSearch = document.querySelectorAll('.container-products'); // Puede ser el mismo que productListContainer u otros
    const noResultsMessages = Array.from(productContainersForSearch).map(cont => cont.querySelector('.no-results-message') || document.createElement('p')); // Crear uno si no existe

    // Favoritos
    // Seleccionar botones de favoritos (asumiendo que es el segundo span en .button-group)
    const favoriteButtons = document.querySelectorAll('.card-product .button-group span:nth-child(2)'); 
    let favoriteItems = new Set();

    // Modal de Blog 
    const blogModal = document.getElementById('blog-modal'); // Asegúrate que este ID exista
    const modalBlogTitle = document.getElementById('modal-blog-title');
    const modalBlogContent = document.getElementById('modal-blog-content');
    const closeModalBtnBlog = document.getElementById('close-blog-modal'); // ID del botón cerrar en el modal del blog
    // const modalCloseBtnBlog = document.getElementById('modal-close-btn'); // Si tienes otro botón de cerrar
    const readMoreButtons = document.querySelectorAll('.btn-read-more');
    const blogModalOverlay = blogModal ? blogModal.querySelector('.modal-overlay') : null;


    // --- Toastify Options ---
    const toastOptions = {
        duration: 2500,
        close: true,
        gravity: "bottom",
        position: "right", 
        stopOnFocus: true, 
        style: {
            background: "linear-gradient(to right, #00b09b, #96c93d)", // Default success
            borderRadius: "5px",
        },
        onClick: function () {} // Callback after click
    };

    // --- Funciones Helper ---
    function parsePrice(priceString) {
        if (typeof priceString !== 'string') return 0;
        const cleanedString = priceString.replace(/\$/g, '').replace(/\./g, '').replace(',', '.');
        return parseFloat(cleanedString) || 0;
    }

    function getProductDataById(productId) {
        const productCard = document.querySelector(`.card-product[data-product-id="${productId}"]`);
        if (!productCard) {
            console.warn(`No se encontró la tarjeta del producto con ID: ${productId}`);
            return null;
        }
        return {
            id: productId,
            name: productCard.querySelector('h3')?.textContent.trim() || 'Producto Desconocido',
            priceText: productCard.querySelector('.price')?.textContent.trim() || '$0',
            price: parsePrice(productCard.querySelector('.price')?.textContent.trim()),
            description: productCard.dataset.description || 'Sin descripción.',
            imageSmall: productCard.querySelector('.container-img img')?.src || '',
            imageLarge: productCard.dataset.imageLarge || productCard.querySelector('.container-img img')?.src || '',
            imageSrc: productCard.querySelector('.container-img img')?.src || '' // Usado para el carrito
        };
    }

    // --- Lógica UI (Slider, Menú, Dropdown Usuario) ---
    function setupSlider() {
        let currentSlideIndex = 0;
        if (slides.length > 0) {
            const showSlide = (index) => {
                slides.forEach((slide, i) => {
                    slide.classList.toggle('active', i === index);
                });
            };
            showSlide(currentSlideIndex);
            setInterval(() => {
                currentSlideIndex = (currentSlideIndex + 1) % slides.length;
                showSlide(currentSlideIndex);
            }, 5000); // Cambia cada 5 segundos
        }
    }

    function setupMenuToggle() {
        if (menuIcon && menu) {
            menuIcon.addEventListener('click', (e) => {
                e.stopPropagation(); // Evitar que el click se propague al document
                menu.classList.toggle('show');
            });
            // Cerrar menú si se hace clic fuera
            document.addEventListener('click', (e) => {
                if (menu.classList.contains('show') && !menu.contains(e.target) && !menuIcon.contains(e.target)) {
                    menu.classList.remove('show');
                }
            });
        }
    }
    
    function setupUserDropdown() {
        if (userIcon && userInfoModal) { // Ahora abre el modal en lugar del dropdown
            userIcon.addEventListener('click', (e) => {
                e.stopPropagation();
                userInfoModal.style.display = 'flex';
                if (userNameInput && userNameDisplay) {
                    userNameInput.value = userNameDisplay.textContent.trim();
                }
            });
        }
        // Cierre del modal de info de usuario
        if (closeUserInfoModalBtn) {
            closeUserInfoModalBtn.addEventListener('click', () => userInfoModal.style.display = 'none');
        }
        if (cancelUserInfoBtn) { // Si tienes un botón "cancelar" en el form del modal
            cancelUserInfoBtn.addEventListener('click', () => userInfoModal.style.display = 'none');
        }
        // Cierre del modal al hacer clic fuera (si el modal tiene un overlay)
        if (userInfoModal && userInfoModal.querySelector('.modal-overlay')) {
            userInfoModal.querySelector('.modal-overlay').addEventListener('click', () => userInfoModal.style.display = 'none');
        }

        // Formulario de actualización de nombre de usuario
        if (userInfoForm) {
            userInfoForm.addEventListener('submit', e => {
                e.preventDefault();
                const newName = userNameInput.value.trim();
                if (newName.length === 0) {
                    Toastify({ ...toastOptions, text: 'El nombre no puede estar vacío.', style: { background: "#ffc107", color: "#000"} }).showToast();
                    return;
                }
                fetch('update_user.php', { // Necesitas crear este archivo PHP
                    method: 'POST',
                    headers: { 'Content-Type': 'application/x-www-form-urlencoded', },
                    body: `user_name=${encodeURIComponent(newName)}`,
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        if (userNameDisplay) userNameDisplay.textContent = data.new_name;
                        if (userInfoModal) userInfoModal.style.display = 'none';
                        Toastify({ ...toastOptions, text: "Nombre actualizado correctamente" }).showToast();
                    } else {
                        Toastify({ ...toastOptions, text: 'Error: ' + data.message, style: {background: "#dc3545"} }).showToast();
                    }
                })
                .catch(error => {
                    console.error('Error al actualizar nombre:', error);
                    Toastify({ ...toastOptions, text: 'Error al actualizar. Intente nuevamente.', style: {background: "#dc3545"} }).showToast();
                });
            });
        }
    }

    // --- Lógica Carrito ---
    function updateCartCounter() {
        if (!cartCounterElement) return;
        const currentCartItemCount = cartItems.reduce((sum, item) => sum + item.quantity, 0);
        cartCounterElement.textContent = `(${currentCartItemCount})`;
    }
    
    function addItemToCart(productData) {
        if (!productData || typeof productData.id === 'undefined') {
            console.error("Intento de añadir producto inválido al carrito:", productData);
            Toastify({ ...toastOptions, text: `Error al añadir producto.`, style: { background: "#dc3545" } }).showToast();
            return;
        }
        const existingItemIndex = cartItems.findIndex(item => item.id === productData.id);
        if (existingItemIndex > -1) {
            cartItems[existingItemIndex].quantity++;
        } else {
            cartItems.push({ ...productData, quantity: 1 });
        }
        updateCartCounter();
        if (cartModal && cartModal.classList.contains('modal-open')) renderCartModal(); // Actualizar modal si está abierto
        Toastify({ ...toastOptions, text: `✅ "${productData.name}" añadido/actualizado en el carrito` }).showToast();
    }
    
    function increaseQuantity(productId) {
        const itemIndex = cartItems.findIndex(item => item.id === productId);
        if (itemIndex > -1) {
            cartItems[itemIndex].quantity++;
            renderCartModal();
            updateCartCounter();
        }
    }
    
    function decreaseQuantity(productId) {
        const itemIndex = cartItems.findIndex(item => item.id === productId);
        if (itemIndex > -1) {
            if (cartItems[itemIndex].quantity > 1) {
                cartItems[itemIndex].quantity--;
            } else {
                // Si la cantidad es 1, disminuir significa eliminar
                cartItems.splice(itemIndex, 1); 
            }
            renderCartModal();
            updateCartCounter();
        }
    }
    
    function removeCartItem(productId) {
        const itemIndex = cartItems.findIndex(item => item.id === productId);
        if (itemIndex > -1) {
            const removedItem = cartItems.splice(itemIndex, 1)[0]; // Eliminar y obtener el ítem
            renderCartModal();
            updateCartCounter();
            Toastify({
                ...toastOptions,
                text: `❌ "${removedItem.name}" eliminado del carrito`,
                style: { background: "linear-gradient(to right, #ff5f6d, #ffc371)" }
            }).showToast();
        }
    }
    
    function renderCartModal() {
        if (!cartItemsList || !cartTotalElement) return;
        cartItemsList.innerHTML = ''; // Limpiar lista actual
        let currentTotal = 0;

        if (cartItems.length === 0) {
            cartItemsList.innerHTML = '<p class="empty-cart-message">Tu carrito está vacío.</p>';
            cartTotalElement.textContent = '$0';
            if (clearCartBtn) clearCartBtn.disabled = true;
        } else {
            if (clearCartBtn) clearCartBtn.disabled = false;
            cartItems.forEach(item => {
                const itemElement = document.createElement('div');
                itemElement.classList.add('cart-item');
                itemElement.dataset.productId = item.id;
                const itemSubtotal = item.price * item.quantity;
                currentTotal += itemSubtotal;

                itemElement.innerHTML = `
                    <img src="${item.imageSrc || './img/cafe-australiano.jpg'}" alt="${item.name}" style="width:50px;height:50px;object-fit:cover;margin-right:10px;border-radius:4px;">
                    <div class="cart-item-details" style="flex-grow:1;">
                        <span class="cart-item-name" style="display:block;font-weight:600;">${item.name}</span>
                        <span class="cart-item-price" style="font-size:0.9em;color:#555;">$${item.price.toLocaleString('es-CO')} c/u</span>
                    </div>
                    <div class="selector-cantidad cart-item-quantity" style="display:flex;align-items:center;margin:0 10px;">
                        <button class="quantity-change decrease-qty" data-id="${item.id}" style="background:#eee;border:1px solid #ccc;padding:2px 8px;cursor:pointer;border-radius:4px;">-</button>
                        <input type="text" value="${item.quantity}" class="carrito-item-cantidad" disabled style="width:30px;text-align:center;margin:0 5px;border:1px solid #ccc;border-radius:4px;">
                        <button class="quantity-change increase-qty" data-id="${item.id}" style="background:#eee;border:1px solid #ccc;padding:2px 8px;cursor:pointer;border-radius:4px;">+</button>
                    </div>
                    <span class="cart-item-subtotal" style="font-weight:bold;min-width:80px;text-align:right;">$${itemSubtotal.toLocaleString('es-CO')}</span>
                    <button class="remove-item-btn" data-id="${item.id}" style="background:none;border:none;color:red;font-size:1.3em;cursor:pointer;margin-left:10px;" title="Eliminar">×</button>
                `;
                cartItemsList.appendChild(itemElement);

                // Añadir listeners a los botones de cantidad y eliminar del ítem recién creado
                itemElement.querySelector('.increase-qty').addEventListener('click', () => increaseQuantity(item.id));
                itemElement.querySelector('.decrease-qty').addEventListener('click', () => decreaseQuantity(item.id));
                itemElement.querySelector('.remove-item-btn').addEventListener('click', () => removeCartItem(item.id));
            });
            cartTotal = currentTotal;
            cartTotalElement.textContent = `$${cartTotal.toLocaleString('es-CO', { minimumFractionDigits: 0, maximumFractionDigits: 0 })}`;
        }
    }
    
    function openCartModal() {
        if (cartModal) {
            renderCartModal(); // Siempre renderizar el carrito al abrir
            cartModal.style.display = 'flex'; // O la forma que uses para mostrarlo
            body.classList.add('modal-active'); // Para evitar scroll del fondo
        }
    }
    
    function closeCartModal() {
        if (cartModal) {
            cartModal.style.display = 'none';
            // Quitar 'modal-active' del body solo si no hay otros modales abiertos
            if (!quickViewModal?.style.display || quickViewModal.style.display === 'none') {
                 if (!comparisonModal?.style.display || comparisonModal.style.display === 'none') {
                    if (!blogModal?.style.display || blogModal.style.display === 'none') {
                       body.classList.remove('modal-active');
                    }
                 }
            }
        }
    }
    
    function clearCartAndNotify(message = "Carrito vaciado.") {
        if (cartItems.length > 0 || message !== "Carrito vaciado.") { // Mostrar siempre si el mensaje no es el por defecto de vaciado
            Toastify({ ...toastOptions, text: `🗑️ ${message}`, style: { background: "#6c757d" } }).showToast();
        }
        cartItems = [];
        cartTotal = 0;
        updateCartCounter();
        renderCartModal(); // Actualizar la vista del modal
    }
    
    function setupCartModalListeners() {
        if (openCartIcon) {
            openCartIcon.addEventListener('click', openCartModal);
        } else {
            console.warn("#cart-toggle-icon no encontrado.");
        }
        if (openCartTextContainer) { // El div que contiene "Carrito (0)"
            openCartTextContainer.addEventListener('click', openCartModal);
        } else {
             console.warn(".content-shopping-cart no encontrado.");
        }
        if (closeModalBtnCart) { // Usar la variable renombrada
            closeModalBtnCart.addEventListener('click', closeCartModal);
        }
        if (cartModalOverlay) {
            cartModalOverlay.addEventListener('click', closeCartModal);
        }
        // Evitar que el clic en el contenido del modal lo cierre
        if (cartModal && cartModal.querySelector('.modal-content')) {
            cartModal.querySelector('.modal-content').addEventListener('click', e => e.stopPropagation());
        }
        if (clearCartBtn) {
            clearCartBtn.addEventListener('click', () => clearCartAndNotify());
        }
        // Listeners para botones "Añadir al Carrito" en las tarjetas de producto
        // Usar delegación de eventos en un contenedor padre si los productos se cargan dinámicamente
        document.querySelectorAll('.add-cart').forEach(button => {
            button.addEventListener('click', function(event) {
                event.stopPropagation();
                const productCard = this.closest('.card-product');
                if (productCard) {
                    const productId = productCard.dataset.productId;
                    if (productId) {
                        const productData = getProductDataById(productId);
                        if (productData) {
                            addItemToCart(productData);
                        }
                    }
                }
            });
        });
    }
    
    // --- Lógica Modal Vista Rápida (QV) ---
    function openQuickViewModal() {
        if (quickViewModal) {
            quickViewModal.style.display = 'flex';
            body.classList.add('modal-active');
        }
    }
    
    function closeQuickViewModal() {
        if (quickViewModal) {
            quickViewModal.style.display = 'none';
            if (!cartModal?.style.display || cartModal.style.display === 'none') {
                if (!comparisonModal?.style.display || comparisonModal.style.display === 'none') {
                    if (!blogModal?.style.display || blogModal.style.display === 'none') {
                        body.classList.remove('modal-active');
                    }
                }
            }
        }
    }
    
    function populateAndShowQuickView(productData) {
        if (!quickViewModal || !qvImage || !qvName || !qvPrice || !qvDescription || !qvAddToCartBtn) {
            console.error("Elementos del modal de vista rápida no encontrados.");
            return;
        }
        qvName.textContent = productData.name;
        qvPrice.textContent = productData.priceText; // El span completo, no solo el número
        qvDescription.textContent = productData.description || "No hay descripción disponible.";
        qvImage.src = productData.imageLarge || productData.imageSmall || './img/cafe-australiano.jpg';
        qvImage.alt = productData.name;
        qvAddToCartBtn.dataset.productId = productData.id; // Guardar ID para añadir al carrito
        openQuickViewModal();
    }
    
    function setupQuickViewListeners() {
        quickViewButtons.forEach(button => {
            button.addEventListener('click', (event) => {
                event.stopPropagation();
                const productCard = button.closest('.card-product');
                if (productCard) {
                    const productId = productCard.dataset.productId;
                    if (productId) {
                        const productData = getProductDataById(productId);
                        if (productData) {
                            populateAndShowQuickView(productData);
                        }
                    }
                }
            });
        });

        if (closeQvModalBtn) closeQvModalBtn.addEventListener('click', closeQuickViewModal);
        if (qvModalOverlay) qvModalOverlay.addEventListener('click', closeQuickViewModal);
        if (quickViewModal && quickViewModal.querySelector('.qv-modal-content')) {
            quickViewModal.querySelector('.qv-modal-content').addEventListener('click', e => e.stopPropagation());
        }
        if (qvAddToCartBtn) {
            qvAddToCartBtn.addEventListener('click', function() {
                const productId = this.dataset.productId;
                if (productId) {
                    const productData = getProductDataById(productId);
                    if (productData) {
                        addItemToCart(productData);
                        closeQuickViewModal(); // Cerrar vista rápida después de añadir
                    }
                }
            });
        }
    }
    
    // --- Lógica de Comparación (MODAL) ---
    function updateCompareButton() {
        if (!compareActionButton || !compareCountSpan) return;
        const count = comparisonList.size;
        compareCountSpan.textContent = count;
        compareActionButton.style.display = count >= 2 ? 'inline-flex' : 'none'; // inline-flex para alinear icono
        compareActionButton.disabled = count < 2;
    }
    
    function renderComparisonModal() {
        if (!comparisonTable) return;
        comparisonTable.innerHTML = ''; // Limpiar
        if (comparisonList.size === 0) {
            comparisonTable.innerHTML = '<p>Selecciona productos para comparar.</p>';
            return;
        }
        const table = document.createElement('table');
        table.style.width = '100%';
        table.style.borderCollapse = 'collapse';
        
        // Cabecera con nombres de producto
        const headerRow = table.insertRow();
        headerRow.insertCell().textContent = 'Característica'; // Celda vacía para la columna de características
        comparisonList.forEach(id => {
            const productData = getProductDataById(id);
            const th = headerRow.insertCell();
            th.style.textAlign = 'center';
            th.style.padding = '8px';
            th.style.border = '1px solid #ddd';
            th.innerHTML = `
                <img src="${productData.imageSmall || './img/cafe-australiano.jpg'}" alt="${productData.name}" style="width:80px;height:80px;object-fit:cover;display:block;margin:0 auto 10px;">
                <strong>${productData.name}</strong><br>
                <button class="remove-compare-btn" data-id="${id}" style="background: #ff4d4d; color:white; border:none; padding: 5px 10px; border-radius:4px; cursor:pointer; font-size:0.8em; margin-top:5px;">Quitar</button>
            `;
        });

        // Filas de características (ejemplo: precio, descripción)
        const features = ['Precio', 'Descripción']; // Puedes añadir más características si las tienes en `productData`
        features.forEach(feature => {
            const row = table.insertRow();
            const th = row.insertCell();
            th.style.fontWeight = 'bold';
            th.style.padding = '8px';
            th.style.border = '1px solid #ddd';
            th.textContent = feature;
            comparisonList.forEach(id => {
                const productData = getProductDataById(id);
                const td = row.insertCell();
                td.style.padding = '8px';
                td.style.border = '1px solid #ddd';
                td.style.verticalAlign = 'top';
                if (feature === 'Precio') {
                    td.textContent = productData.priceText;
                } else if (feature === 'Descripción') {
                    td.textContent = productData.description;
                }
                // Añadir más 'else if' para otras características
            });
        });
        comparisonTable.appendChild(table);
    }
    
    function openComparisonModal() {
        if (comparisonModal && comparisonList.size >= 2) {
            renderComparisonModal();
            comparisonModal.style.display = 'flex';
            body.classList.add('modal-active');
        } else if (comparisonList.size < 2) {
             Toastify({ ...toastOptions, text: "Selecciona al menos 2 productos para comparar.", style: { background: "#ffc107", color:"#000" } }).showToast();
        }
    }
    
    function closeComparisonModal() {
        if (comparisonModal) {
            comparisonModal.style.display = 'none';
            if (!cartModal?.style.display || cartModal.style.display === 'none') {
                if (!quickViewModal?.style.display || quickViewModal.style.display === 'none') {
                    if (!blogModal?.style.display || blogModal.style.display === 'none') {
                        body.classList.remove('modal-active');
                    }
                }
            }
        }
    }
    
    function setupComparisonListeners() {
        compareButtons.forEach(button => {
            button.addEventListener('click', (event) => {
                event.stopPropagation();
                const productCard = button.closest('.card-product');
                const productId = productCard?.dataset.productId;
                if (!productId) return;

                const productName = productCard.querySelector('h3')?.textContent || 'Producto';
                const iconElement = button.querySelector('i');

                if (comparisonList.has(productId)) {
                    comparisonList.delete(productId);
                    Toastify({ ...toastOptions, text: `"${productName}" quitado de comparación`, style: { background: "#dc3545" } }).showToast();
                    button.style.backgroundColor = ''; // Volver al estilo original
                    if (iconElement) iconElement.style.color = 'var(--primary-color)';
                } else {
                    if (comparisonList.size >= MAX_COMPARE_ITEMS) {
                        Toastify({ ...toastOptions, text: `Máximo ${MAX_COMPARE_ITEMS} productos para comparar`, style: { background: "#ffc107", color:"#000" } }).showToast();
                        return;
                    }
                    comparisonList.add(productId);
                    Toastify({ ...toastOptions, text: `"${productName}" añadido a comparación` }).showToast();
                    button.style.backgroundColor = 'var(--primary-color)'; // Resaltar botón
                    if (iconElement) iconElement.style.color = '#fff';
                }
                updateCompareButton();
            });
        });

        if (compareActionButton) compareActionButton.addEventListener('click', openComparisonModal);
        if (closeComparisonModalBtn) closeComparisonModalBtn.addEventListener('click', closeComparisonModal);
        if (comparisonModalOverlay) comparisonModalOverlay.addEventListener('click', closeComparisonModal);
        if (comparisonModal && comparisonModal.querySelector('.comparison-modal-content')) {
            comparisonModal.querySelector('.comparison-modal-content').addEventListener('click', e => e.stopPropagation());
        }
        // Listener para quitar de la tabla de comparación
        if (comparisonTable) {
            comparisonTable.addEventListener('click', function(event) {
                if (event.target.classList.contains('remove-compare-btn')) {
                    const productIdToRemove = event.target.dataset.id;
                    if (productIdToRemove) {
                        comparisonList.delete(productIdToRemove);
                        const originalButtonSpan = document.querySelector(`.card-product[data-product-id="${productIdToRemove}"] .button-group span:nth-child(3)`);
                        if (originalButtonSpan) {
                            originalButtonSpan.style.backgroundColor = '';
                            const icon = originalButtonSpan.querySelector('i');
                            if (icon) icon.style.color = 'var(--primary-color)';
                        }
                        renderComparisonModal(); // Re-renderizar la tabla
                        updateCompareButton();
                        if (comparisonList.size < 2) closeComparisonModal(); // Cerrar si quedan menos de 2
                    }
                }
            });
        }
    }
    
    // --- Funcionalidad Filtro Productos (Tabs con AJAX) ---
    function setupFilterListeners() {
        if (filterOptions.length > 0 && productListContainer) {
            filterOptions.forEach(option => {
                option.addEventListener('click', function (event) {
                    event.preventDefault();
                    if (this.classList.contains('active')) return; // No hacer nada si ya está activo

                    filterOptions.forEach(opt => opt.classList.remove('active'));
                    this.classList.add('active');

                    let filterType = 'destacados'; // default
                    if (this.id === 'filter-recientes') filterType = 'recientes';
                    else if (this.id === 'filter-mejores-vendidos') filterType = 'mejores-vendidos';
                    
                    loadProductsWithAjax(filterType);
                });
            });
        } else {
            // console.warn("Elementos para filtros de producto no encontrados (opciones o contenedor).");
        }
    }

    function loadProductsWithAjax(filterType = 'destacados') {
        if (!productListContainer) return;
        productListContainer.innerHTML = '<p style="text-align:center; padding:20px;">Cargando...</p>'; // Indicador de carga

        fetch(`ajax_get_productos.php?filtro=${filterType}`)
            .then(response => {
                if (!response.ok) throw new Error(`Error de red: ${response.status}`);
                return response.text();
            })
            .then(html => {
                productListContainer.innerHTML = html;
                // IMPORTANTE: Después de cargar nuevo HTML, necesitas reinicializar listeners
                // para los elementos dentro de las nuevas tarjetas de producto.
                reinitializeDynamicListeners(); 
            })
            .catch(error => {
                console.error('Error al cargar productos con AJAX:', error);
                productListContainer.innerHTML = '<p style="text-align:center; padding:20px; color:red;">Error al cargar. Intente más tarde.</p>';
            });
    }
    
    // --- Funcionalidad Barra de Búsqueda ---
    function filterProductsBySearch(){
        if (!searchInput || !productContainersForSearch.length) return;
        const searchTerm = searchInput.value.toLowerCase().trim();
        
        productContainersForSearch.forEach((container, idx) => {
            const productsInContainer = container.querySelectorAll('.card-product');
            let visibleInContainer = 0;
            if (!productsInContainer.length) return;

            productsInContainer.forEach(card => {
                const name = card.querySelector('h3')?.textContent.toLowerCase() || '';
                const show = name.includes(searchTerm);
                card.style.display = show ? '' : 'none';
                if (show) visibleInContainer++;
            });
            // Actualizar mensaje de "no resultados" para este contenedor específico
            const noResultsMsg = noResultsMessages[idx]; // Usar el mensaje específico
            if (noResultsMsg) {
                noResultsMsg.style.display = (visibleInContainer === 0 && searchTerm !== '') ? 'block' : 'none';
                if(visibleInContainer === 0 && searchTerm !== '') noResultsMsg.textContent = `No hay resultados para "${searchInput.value}"`;
            }
        });
    }

    function setupSearchListeners(){
        if(searchForm && searchInput){
            searchForm.addEventListener('submit', e => { 
                e.preventDefault(); 
                filterProductsBySearch();
            });
            // Filtrar mientras se escribe (con un pequeño debounce para no sobrecargar)
            let searchTimeout;
            searchInput.addEventListener('input', () => {
                clearTimeout(searchTimeout);
                searchTimeout = setTimeout(filterProductsBySearch, 300); // Espera 300ms después de dejar de escribir
            });
        }
    }

	// --- Funcionalidad Botones Extra Productos (Favoritos) ---
    function setupFavoriteListeners() {
        // Usar delegación de eventos en un contenedor padre si los productos se cargan dinámicamente
        document.body.addEventListener('click', function(event) {
            const favoriteButton = event.target.closest('.button-group span:nth-child(2)'); // Asume que el corazón es el segundo
            if (!favoriteButton || !favoriteButton.parentElement.classList.contains('button-group')) return;
            
            event.stopPropagation();
            const icon = favoriteButton.querySelector('i');
            const productCard = favoriteButton.closest('.card-product');
            if (!icon || !productCard) return;

            const productId = productCard.dataset.productId;
            const productName = productCard.querySelector('h3')?.textContent || 'Producto';

            if (icon.classList.contains('fa-regular')) { // Si no es favorito, añadirlo
                icon.classList.replace('fa-regular', 'fa-solid');
                favoriteButton.style.backgroundColor = 'var(--primary-color)';
                icon.style.color = '#fff';
                favoriteItems.add(productId);
                Toastify({ ...toastOptions, text: `❤️ "${productName}" añadido a favoritos` }).showToast();
            } else { // Si ya es favorito, quitarlo
                icon.classList.replace('fa-solid', 'fa-regular');
                favoriteButton.style.backgroundColor = '';
                icon.style.color = 'var(--primary-color)';
                favoriteItems.delete(productId);
                Toastify({ ...toastOptions, text: `💔 "${productName}" eliminado de favoritos`, style: { background: "#6c757d" } }).showToast();
            }
        });
    }
    
    // --- Modal de Blog ---
    function setupBlogModalListeners() {
        if (!blogModal) return; // Si el modal de blog no existe en la página, no hacer nada.

        readMoreButtons.forEach(button => {
            button.addEventListener('click', () => {
                const blogId = button.getAttribute('data-blog-id'); // Asegúrate que tus botones "Leer más" tengan este data-attribute
                if (blogId) {
                    if (modalBlogTitle) modalBlogTitle.textContent = 'Cargando...';
                    if (modalBlogContent) modalBlogContent.textContent = 'Por favor espera...';
                    blogModal.style.display = 'flex';
                    body.classList.add('modal-active');

                    fetch(`get_blog.php?id=${encodeURIComponent(blogId)}`) // Necesitas crear get_blog.php
                        .then(response => {
                            if (!response.ok) throw new Error('Error al cargar el blog.');
                            return response.json();
                        })
                        .then(data => {
                            if (data.error) {
                                if (modalBlogTitle) modalBlogTitle.textContent = 'Error';
                                if (modalBlogContent) modalBlogContent.textContent = data.error;
                            } else {
                                if (modalBlogTitle) modalBlogTitle.textContent = data.title;
                                if (modalBlogContent) modalBlogContent.textContent = data.content; // O data.html_content si devuelves HTML
                            }
                        })
                        .catch(error => {
                            console.error('Error al cargar el blog:', error);
                            if (modalBlogTitle) modalBlogTitle.textContent = 'Error de Carga';
                            if (modalBlogContent) modalBlogContent.textContent = 'No se pudo cargar el contenido. Intenta nuevamente.';
                        });
                }
            });
        });

        const closeBlogModal = () => {
            blogModal.style.display = 'none';
            if (!cartModal?.style.display || cartModal.style.display === 'none') {
                 if (!quickViewModal?.style.display || quickViewModal.style.display === 'none') {
                    if (!comparisonModal?.style.display || comparisonModal.style.display === 'none') {
                        body.classList.remove('modal-active');
                    }
                 }
            }
        };

        if (closeModalBtnBlog) closeModalBtnBlog.addEventListener('click', closeBlogModal);
        // if (modalCloseBtnBlog) modalCloseBtnBlog.addEventListener('click', closeBlogModal); // Si tienes otro botón
        if (blogModalOverlay) blogModalOverlay.addEventListener('click', closeBlogModal);
        if (blogModal.querySelector('.modal-content')) { // Para evitar que se cierre al hacer clic dentro
            blogModal.querySelector('.modal-content').addEventListener('click', e => e.stopPropagation());
        }
    }

    // --- Integración PayPal ---
    // --- Integración PayPal con Depuración Completa ---
function setupPaypalButton() {
    console.log("🔧 Iniciando configuración de botón PayPal");
    
    const paypalContainer = document.getElementById('paypal-button-container');
    if (!paypalContainer || typeof paypal === 'undefined') {
        console.error("❌ Contenedor PayPal o SDK de PayPal no encontrado/cargado.");
        console.log("Contenedor existe:", !!paypalContainer);
        console.log("PayPal SDK cargado:", typeof paypal !== 'undefined');
        return;
    }
    
    console.log("✅ Contenedor PayPal y SDK encontrados");
    // Limpiar contenedor por si se llama varias veces
    paypalContainer.innerHTML = ''; 

    paypal.Buttons({
        style: { layout: 'vertical', color: 'blue', shape: 'rect', label: 'paypal' },
        
        createOrder: function(data, actions) {
            console.log("💰 Creando orden PayPal...");
            console.log("Carrito actual:", cartItems);
            console.log("Total del carrito:", cartTotal);
            
            if (cartItems.length === 0) {
                console.warn("⚠️ Carrito vacío, abortando orden");
                Toastify({ ...toastOptions, text: "El carrito está vacío", style: { background: "#ffc107", color: "#000" } }).showToast();
                return Promise.reject();
            }
            
            const amountValue = cartTotal.toFixed(2);
            console.log("💵 Monto de la orden:", amountValue, "USD");
            
            return actions.order.create({
                purchase_units: [{
                    amount: { value: amountValue, currency_code: 'USD' }
                }]
            });
        },
        
        onApprove: function(data, actions) {
            console.log("🎉 Pago aprobado por PayPal");
            console.log("Datos de aprobación:", data);
            
            return actions.order.capture().then(function(details) {
                console.log("💳 Captura de pago completada");
                console.log("Detalles de la transacción:", details);
                console.log("Nombre del pagador:", details.payer.name.given_name);
                
                Toastify({ 
                    ...toastOptions, 
                    text: `Pago completado por ${details.payer.name.given_name}. Gracias!` 
                }).showToast();
                
                // DEPURACIÓN ANTES DEL ENVÍO
                console.log("📦 PREPARANDO ENVÍO AL SERVIDOR:");
                console.log("- Carrito a enviar:", JSON.stringify(cartItems, null, 2));
                console.log("- Número de items:", cartItems.length);
                console.log("- Total calculado:", cartTotal);
                
                // Validar estructura del carrito antes del envío
                const cartValidation = cartItems.every(item => {
                    const isValid = item.id && item.name && item.price && item.quantity;
                    if (!isValid) {
                        console.error("❌ Item inválido encontrado:", item);
                    }
                    return isValid;
                });
                
                if (!cartValidation) {
                    console.error("❌ Carrito contiene items inválidos");
                    Toastify({ 
                        ...toastOptions, 
                        text: 'Error: Carrito contiene datos inválidos', 
                        style: { background: '#dc3545' } 
                    }).showToast();
                    return;
                }
                
                console.log("✅ Validación del carrito exitosa");

                // Guardar pedido en backend
                console.log("🌐 Enviando solicitud a guardar_pedido.php...");
                
                fetch('guardar_pedido.php', {
                    method: 'POST',
                    headers: { 
                        'Content-Type': 'application/json',
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({ 
                        items: cartItems,
                        paypal_transaction_id: details.id,
                        total_amount: cartTotal
                    })
                })
                .then(response => {
                    console.log("📡 RESPUESTA DEL SERVIDOR:");
                    console.log("- Status HTTP:", response.status);
                    console.log("- Status Text:", response.statusText);
                    console.log("- Headers:", Object.fromEntries(response.headers.entries()));
                    console.log("- URL:", response.url);
                    console.log("- OK:", response.ok);
                    
                    if (!response.ok) {
                        console.error(`❌ Error HTTP ${response.status}: ${response.statusText}`);
                        throw new Error(`HTTP ${response.status}: ${response.statusText}`);
                    }
                    
                    // Intentar obtener el texto de respuesta primero para depuración
                    return response.text().then(text => {
                        console.log("📄 RESPUESTA CRUDA DEL SERVIDOR:");
                        console.log(text);
                        
                        try {
                            const jsonData = JSON.parse(text);
                            console.log("✅ JSON parseado exitosamente:", jsonData);
                            return jsonData;
                        } catch (parseError) {
                            console.error("❌ Error al parsear JSON:", parseError);
                            console.error("Texto recibido:", text);
                            throw new Error("Respuesta del servidor no es JSON válido");
                        }
                    });
                })
                .then(data => {
                    console.log("🔍 ANÁLISIS DE RESPUESTA:");
                    console.log("- Datos completos:", data);
                    console.log("- Tipo de datos:", typeof data);
                    console.log("- Keys disponibles:", Object.keys(data));
                    console.log("- data.success:", data.success, "(tipo:", typeof data.success, ")");
                    console.log("- data.pedido_id:", data.pedido_id, "(tipo:", typeof data.pedido_id, ")");
                    console.log("- data.message:", data.message);
                    
                    // Validaciones específicas
                    if (data.success === undefined) {
                        console.error("❌ Propiedad 'success' no encontrada en respuesta");
                    }
                    
                    if (data.pedido_id === undefined) {
                        console.error("❌ Propiedad 'pedido_id' no encontrada en respuesta");
                    }
                    
                    // Conversión de tipos si es necesario
                    const isSuccess = data.success === true || data.success === 'true' || data.success === 1 || data.success === '1';
                    const pedidoId = data.pedido_id ? String(data.pedido_id) : null;
                    
                    console.log("🔄 DESPUÉS DE CONVERSIÓN:");
                    console.log("- isSuccess:", isSuccess);
                    console.log("- pedidoId:", pedidoId);
                    
                    if (isSuccess && pedidoId) {
                        console.log("🎯 ÉXITO: Pedido guardado correctamente con ID:", pedidoId);
                        
                        // Mostrar botón para descargar factura
                        const downloadBtn = document.createElement('button');
                        downloadBtn.textContent = 'Descargar Factura PDF';
                        downloadBtn.style.marginTop = '10px';
                        downloadBtn.style.padding = '10px 20px';
                        downloadBtn.style.backgroundColor = '#2980b9';
                        downloadBtn.style.color = 'white';
                        downloadBtn.style.border = 'none';
                        downloadBtn.style.borderRadius = '5px';
                        downloadBtn.style.cursor = 'pointer';
                        
                        downloadBtn.addEventListener('click', () => {
                            const facturaUrl = `factura.php?pedido_id=${pedidoId}`;
                            console.log("📄 Abriendo factura en:", facturaUrl);
                            window.open(facturaUrl, '_blank');
                        });

                        const paypalContainer = document.getElementById('paypal-button-container');
                        if (paypalContainer) {
                            paypalContainer.innerHTML = '';
                            paypalContainer.appendChild(downloadBtn);
                            console.log("✅ Botón de descarga añadido al DOM");
                        } else {
                            console.error("❌ No se pudo encontrar el contenedor PayPal para añadir el botón");
                        }
                    } else {
                        console.error("❌ ERROR AL GUARDAR PEDIDO:");
                        console.error("- Success:", data.success, "->", isSuccess);
                        console.error("- Pedido ID:", data.pedido_id, "->", pedidoId);
                        console.error("- Mensaje:", data.message);
                        
                        // Mensaje más específico para el usuario
                        let errorMessage = 'Error al guardar el pedido para la factura.';
                        if (data.message) {
                            errorMessage += ` Detalle: ${data.message}`;
                        }
                        
                        Toastify({ 
                            ...toastOptions, 
                            text: errorMessage, 
                            style: { background: '#dc3545' } 
                        }).showToast();
                    }
                })
                .catch((error) => {
                    console.error("🚨 ERROR CRÍTICO EN FETCH:");
                    console.error("- Tipo de error:", error.constructor.name);
                    console.error("- Mensaje:", error.message);
                    console.error("- Stack trace:", error.stack);
                    
                    // Error más específico para el usuario
                    let userMessage = 'Error en la comunicación con el servidor.';
                    
                    if (error.message.includes('Failed to fetch')) {
                        userMessage = 'No se pudo conectar con el servidor. Verifique su conexión.';
                    } else if (error.message.includes('JSON')) {
                        userMessage = 'El servidor devolvió una respuesta inválida.';
                    } else if (error.message.includes('HTTP')) {
                        userMessage = `Error del servidor: ${error.message}`;
                    }
                    
                    Toastify({ 
                        ...toastOptions, 
                        text: userMessage, 
                        style: { background: '#dc3545' } 
                    }).showToast();
                });

                console.log("🧹 Limpiando carrito y cerrando modal...");
                clearCartAndNotify("Gracias por su compra!");
                closeCartModal();
                
            }).catch(paypalError => {
                console.error("💳 Error en captura de PayPal:", paypalError);
                Toastify({ 
                    ...toastOptions, 
                    text: 'Error al procesar el pago con PayPal', 
                    style: { background: '#dc3545' } 
                }).showToast();
            });
        },
        
        onError: function(err) {
            console.error("🚨 ERROR EN PAYPAL:", err);
            Toastify({ 
                ...toastOptions, 
                text: 'Error en PayPal. Intente nuevamente.', 
                style: { background: '#dc3545' } 
            }).showToast();
        },
        
        onCancel: function(data) {
            console.log("❌ Pago cancelado por el usuario:", data);
            Toastify({ 
                ...toastOptions, 
                text: 'Pago cancelado', 
                style: { background: '#ffc107', color: '#000' } 
            }).showToast();
        }
        
    }).render('#paypal-button-container').then(() => {
        console.log("✅ Botón PayPal renderizado exitosamente");
    }).catch(renderError => {
        console.error("❌ Error al renderizar botón PayPal:", renderError);
    });
}

    function generateInvoiceHTML(payerName, items, total) {
        let itemsHTML = '';
        items.forEach(item => {
            itemsHTML += `
                <tr>
                    <td style="border: 1px solid #ddd; padding: 8px;">${item.name}</td>
                    <td style="border: 1px solid #ddd; padding: 8px; text-align: center;">${item.quantity}</td>
                    <td style="border: 1px solid #ddd; padding: 8px; text-align: right;">$${item.price.toLocaleString('es-CO')}</td>
                    <td style="border: 1px solid #ddd; padding: 8px; text-align: right;">$${(item.price * item.quantity).toLocaleString('es-CO')}</td>
                </tr>
            `;
        });
        const currentDate = new Date().toLocaleDateString('es-CO', { year: 'numeric', month: 'long', day: 'numeric' });
        return `
            <html><head><title>Factura - Coffee Galactic</title><style>body{font-family:Arial,sans-serif;margin:20px;color:#333}h1,h2{color:#4a3c2b}.invoice-header{text-align:center;margin-bottom:30px}.invoice-header img{max-width:150px}table{width:100%;border-collapse:collapse;margin-top:20px}th,td{border:1px solid #ddd;padding:10px;text-align:left}th{background-color:#f0f0f0}tfoot td{font-weight:bold}.text-right{text-align:right!important}.footer{margin-top:40px;text-align:center;font-size:0.9em;color:#777}</style></head>
            <body><div class="invoice-header"><img src="./img/icono.png" alt="Coffee Galactic Logo"><h2>Factura de Compra</h2></div>
            <p><strong>Cliente:</strong> ${payerName}</p><p><strong>Fecha:</strong> ${currentDate}</p>
            <table><thead><tr><th>Producto</th><th>Cantidad</th><th class="text-right">Precio Unitario</th><th class="text-right">Subtotal</th></tr></thead>
            <tbody>${itemsHTML}</tbody>
            <tfoot><tr><td colspan="3" class="text-right"><strong>Total:</strong></td><td class="text-right"><strong>$${total.toLocaleString('es-CO')}</strong></td></tr></tfoot>
            </table><div class="footer"><p>Gracias por su compra en Coffee Galactic.</p></div>
            </body></html>
        `;
    }
    
    // Función para reinicializar listeners en elementos dinámicos (ej. después de AJAX)
    function reinitializeDynamicListeners() {
        // Volver a seleccionar botones y añadir listeners
        // (Esta es una simplificación, idealmente usarías delegación de eventos o ser más específico)
        const newAddToCartButtons = productListContainer.querySelectorAll('.add-cart');
        newAddToCartButtons.forEach(button => {
             button.addEventListener('click', function(event) {
                event.stopPropagation();
                const productCard = this.closest('.card-product');
                if (productCard) {
                    const productId = productCard.dataset.productId;
                    if (productId) {
                        const productData = getProductDataById(productId);
                        if (productData) addItemToCart(productData);
                    }
                }
            });
        });

        const newQuickViewButtons = productListContainer.querySelectorAll('.card-product .button-group span:nth-child(1)');
        newQuickViewButtons.forEach(button => {
            button.addEventListener('click', (event) => {
                event.stopPropagation();
                const productCard = button.closest('.card-product');
                if (productCard) {
                    const productId = productCard.dataset.productId;
                    if (productId) {
                        const productData = getProductDataById(productId);
                        if (productData) populateAndShowQuickView(productData);
                    }
                }
            });
        });
        
        const newCompareButtons = productListContainer.querySelectorAll('.card-product .button-group span:nth-child(3)');
        newCompareButtons.forEach(button => {
            button.addEventListener('click', (event) => {
                event.stopPropagation();
                const productCard = button.closest('.card-product');
                const productId = productCard?.dataset.productId;
                if (!productId) return;
                // ... (lógica de añadir/quitar de comparación, similar a setupComparisonListeners) ...
                 const productName = productCard.querySelector('h3')?.textContent || 'Producto';
                const iconElement = button.querySelector('i');
                if (comparisonList.has(productId)) {
                    comparisonList.delete(productId);
                    Toastify({ ...toastOptions, text: `"${productName}" quitado de comparación`, style: { background: "#dc3545" } }).showToast();
                    button.style.backgroundColor = ''; if (iconElement) iconElement.style.color = 'var(--primary-color)';
                } else {
                    if (comparisonList.size >= MAX_COMPARE_ITEMS) {
                        Toastify({ ...toastOptions, text: `Máximo ${MAX_COMPARE_ITEMS} productos`, style: { background: "#ffc107", color:"#000" } }).showToast(); return;
                    }
                    comparisonList.add(productId);
                    Toastify({ ...toastOptions, text: `"${productName}" añadido a comparación` }).showToast();
                    button.style.backgroundColor = 'var(--primary-color)'; if (iconElement) iconElement.style.color = '#fff';
                }
                updateCompareButton();
            });
        });

        const newFavoriteButtons = productListContainer.querySelectorAll('.card-product .button-group span:nth-child(2)');
        
    }

    // --- Inicialización de la aplicación ---
    function initializeApp() {
        console.log("--- INICIALIZANDO APP ---");
        setupSlider();
        setupMenuToggle();
        setupUserDropdown();
        setupCartModalListeners(); // Asegúrate que esto se llama
        setupQuickViewListeners();
        setupComparisonListeners();
        setupFilterListeners(); 
        setupSearchListeners();  
        setupFavoriteListeners();
        setupPaypalButton(); // Llamar para configurar PayPal
        setupBlogModalListeners(); // Configurar listeners para el modal de blog

        updateCartCounter(); // Actualizar contador al inicio
        updateCompareButton(); // Actualizar botón de comparar al inicio
        console.log("--- INICIALIZACIÓN COMPLETA ---");
    }

    // Ejecutar la inicialización
    initializeApp();

});
// Función para procesar la compra
function procesarCompra() {
    // Obtener los items del carrito (esto depende de tu implementación)
    const cartItems = obtenerItemsDelCarrito(); // Tu función existente
    
    if (!cartItems || cartItems.length === 0) {
        alert('El carrito está vacío');
        return;
    }
    
    // Mostrar indicador de carga
    const loadingElement = document.getElementById('loading-indicator');
    if (loadingElement) {
        loadingElement.style.display = 'block';
    }
    
    // Enviar datos al servidor
    fetch('guardar_pedido.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({
            items: cartItems
        })
    })
    .then(response => response.json())
    .then(data => {
        console.log('Respuesta del servidor:', data);
        
        // Ocultar indicador de carga
        if (loadingElement) {
            loadingElement.style.display = 'none';
        }
        
        if (data.success) {
            // Mostrar mensaje de éxito
            alert('¡Pedido procesado exitosamente!');
            
            // Limpiar carrito
            limpiarCarrito(); // Tu función existente
            
            // Redireccionar a la factura
            if (data.redirect_to_invoice && data.factura_url) {
                window.location.href = data.factura_url;
            } else {
                // Fallback: construir URL manualmente
                window.location.href = `factura.php?pedido_id=${data.pedido_id}&auto=1`;
            }
        } else {
            // Mostrar error
            alert('Error al procesar el pedido: ' + data.message);
            console.error('Error detallado:', data);
        }
    })
    .catch(error => {
        console.error('Error en la petición:', error);
        
        // Ocultar indicador de carga
        if (loadingElement) {
            loadingElement.style.display = 'none';
        }
        
        alert('Error de conexión. Por favor, inténtalo de nuevo.');
    });
}

// Función auxiliar para obtener items del carrito
// (Adapta esto según tu implementación actual)
function obtenerItemsDelCarrito() {
    // Ejemplo si usas localStorage
    const cart = localStorage.getItem('cart');
    if (cart) {
        return JSON.parse(cart);
    }
    
    // O si usas variables globales
    if (typeof window.cartItems !== 'undefined') {
        return window.cartItems;
    }
    
    // O si tienes los datos en el DOM
    const cartElements = document.querySelectorAll('.cart-item');
    const items = [];
    
    cartElements.forEach(element => {
        const item = {
            id: parseInt(element.dataset.productId),
            nombre: element.dataset.productName || element.querySelector('.product-name')?.textContent,
            precio: parseFloat(element.dataset.productPrice),
            cantidad: parseInt(element.dataset.quantity || element.querySelector('.quantity')?.value)
        };
        items.push(item);
    });
    
    return items;
}

// Función para limpiar el carrito después de la compra
function limpiarCarrito() {
    // Si usas localStorage
    localStorage.removeItem('cart');
    
    // Si usas variables globales
    if (typeof window.cartItems !== 'undefined') {
        window.cartItems = [];
    }
    
    // Si tienes elementos en el DOM
    const cartContainer = document.getElementById('cart-container');
    if (cartContainer) {
        cartContainer.innerHTML = '<p>El carrito está vacío</p>';
    }
    
    // Actualizar contador del carrito
    const cartCounter = document.getElementById('cart-counter');
    if (cartCounter) {
        cartCounter.textContent = '0';
    }
}

// Event listener para el botón de compra
document.addEventListener('DOMContentLoaded', function() {
    const checkoutButton = document.getElementById('checkout-btn') || 
                          document.querySelector('.checkout-btn') ||
                          document.querySelector('[onclick*="comprar"]');
    
    if (checkoutButton) {
        checkoutButton.addEventListener('click', function(e) {
            e.preventDefault();
            procesarCompra();
        });
    }
});
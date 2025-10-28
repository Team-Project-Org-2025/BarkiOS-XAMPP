// novedades.js - Script específico para la página de novedades
document.addEventListener("DOMContentLoaded", () => {
    console.log("🚀 Novedades.js iniciado");

    // ============================================
    // ELEMENTOS DEL DOM
    // ============================================
    const latestContainer = document.getElementById("latest-products");
    const loadingElement = document.getElementById("loading-products");
    const errorElement = document.getElementById("error-products");
    const emptyElement = document.getElementById("empty-products");

    // Verificar que los elementos existen
    if (!latestContainer) {
        console.error("❌ No se encontró el contenedor #latest-products");
        return;
    }

    // ============================================
    // FUNCIÓN PARA CARGAR PRODUCTOS
    // ============================================
    function cargarProductosRecientes() {
        console.log("📦 Iniciando carga de productos...");

        // Mostrar loading
        if (loadingElement) loadingElement.style.display = "block";
        if (latestContainer) latestContainer.style.display = "none";
        if (errorElement) errorElement.classList.add("d-none");
        if (emptyElement) emptyElement.classList.add("d-none");

        // URL de la API
        const apiUrl = "/BarkiOS/app/controllers/front/ProductsApiController.php";
        console.log("🔗 API URL:", apiUrl);

        // Realizar petición
        fetch(apiUrl, {
            method: "GET",
            headers: {
                "Content-Type": "application/json",
            },
        })
        .then((response) => {
            console.log("📡 Respuesta recibida:", response.status);
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            return response.json();
        })
        .then((data) => {
            console.log("✅ Datos recibidos:", data);

            // Ocultar loading
            if (loadingElement) loadingElement.style.display = "none";

            // Verificar si hay productos
            if (!data.success || !data.products || data.products.length === 0) {
                console.warn("⚠️ No hay productos disponibles");
                if (emptyElement) emptyElement.classList.remove("d-none");
                return;
            }

            // Mostrar contenedor de productos
            if (latestContainer) latestContainer.style.display = "flex";

            // Limpiar contenedor
            latestContainer.innerHTML = "";

            // Tomar solo los primeros 8 productos
            const productos = data.products.slice(0, 8);
            console.log(`📦 Mostrando ${productos.length} productos`);

            // Renderizar productos
            productos.forEach((product, index) => {
                const card = crearTarjetaProducto(product, index);
                latestContainer.insertAdjacentHTML("beforeend", card);
            });

            // Refrescar animaciones AOS
            if (typeof AOS !== "undefined") {
                AOS.refresh();
            }

            console.log("✅ Productos cargados exitosamente");
        })
        .catch((error) => {
            console.error("❌ Error cargando productos:", error);

            // Ocultar loading
            if (loadingElement) loadingElement.style.display = "none";

            // Mostrar error
            if (errorElement) {
                errorElement.classList.remove("d-none");
                errorElement.innerHTML = `
                    <i class="fas fa-exclamation-triangle me-2"></i>
                    <strong>Error:</strong> ${error.message || "No se pudieron cargar los productos"}
                `;
            }
        });
    }

    // ============================================
    // FUNCIÓN PARA CREAR TARJETA DE PRODUCTO
    // ============================================
    function crearTarjetaProducto(product, index) {
        // Obtener ID del producto
        const productId = product.id || product.prenda_id || 0;

        // Procesar imagen
        let imagenSrc = "/BarkiOS/public/assets/img/no-image.png";
        
        if (product.imagen) {
            // Si la imagen ya tiene la ruta completa
            if (product.imagen.includes("/BarkiOS/")) {
                imagenSrc = product.imagen;
            } 
            // Si es ruta relativa desde uploads
            else if (product.imagen.includes("uploads/")) {
                imagenSrc = `/BarkiOS/${product.imagen}`;
            }
            // Si es solo el nombre del archivo
            else {
                imagenSrc = `/BarkiOS/public/uploads/${product.imagen}`;
            }
        }

        // Formatear precio
        const precio = parseFloat(product.precio || 0).toFixed(2);

        // Escapar HTML para prevenir XSS
        const escapeHtml = (str) => {
            const div = document.createElement('div');
            div.textContent = str || '';
            return div.innerHTML;
        };

        const nombre = escapeHtml(product.nombre);
        const categoria = escapeHtml(product.categoria);
        const tipo = escapeHtml(product.tipo || "Sin tipo");
        const descripcion = escapeHtml(product.descripcion || "");

        return `
            <div class="col-md-6 col-lg-3" data-aos="fade-up" data-aos-delay="${index * 100}">
                <div class="product-card" data-product-id="${productId}">
                    <div class="product-image">
                        <span class="product-badge bg-danger">Nuevo</span>
                        <img src="${imagenSrc}" 
                             alt="${nombre}" 
                             onerror="this.onerror=null; this.src='/BarkiOS/public/assets/img/no-image.png';"
                             loading="lazy">
                        <div class="product-actions">
                            <button class="action-btn add-to-wishlist" 
                                    data-product-id="${productId}"
                                    title="Agregar a favoritos">
                                <i class="far fa-heart"></i>
                            </button>
                            <button class="action-btn quick-view" 
                                    data-product-id="${productId}"
                                    title="Vista rápida">
                                <i class="far fa-eye"></i>
                            </button>
                        </div>
                    </div>
                    <div class="product-info">
                        <span class="badge bg-light text-dark mb-2" data-tipo="${tipo}">${tipo}</span>
                        <h4>${nombre}</h4>
                        <p class="product-category">${categoria}</p>
                        ${descripcion ? `<p class="text-muted small d-none" data-description="${descripcion}">${descripcion}</p>` : ''}
                        <div class="product-price">$${precio}</div>
                        <button class="btn btn-dark w-100 mt-2 add-to-cart" 
                                data-product-id="${productId}"
                                data-product-name="${nombre}">
                            <i class="fas fa-shopping-cart me-2"></i>Agregar al Carrito
                        </button>
                    </div>
                </div>
            </div>
        `;
    }

    // ============================================
    // INICIAR CARGA DE PRODUCTOS
    // ============================================
    cargarProductosRecientes();

    // ============================================
    // BOTÓN DE RECARGA (OPCIONAL)
    // ============================================
    if (errorElement) {
        const reloadButton = document.createElement("button");
        reloadButton.className = "btn btn-primary mt-3";
        reloadButton.innerHTML = '<i class="fas fa-redo me-2"></i>Intentar nuevamente';
        reloadButton.onclick = () => {
            console.log("🔄 Recargando productos...");
            cargarProductosRecientes();
        };
        errorElement.appendChild(reloadButton);
    }
});
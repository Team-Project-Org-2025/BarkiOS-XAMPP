document.addEventListener("DOMContentLoaded", () => {
  // Inicializar animaciones AOS
  AOS.init({
    duration: 800,
    easing: "ease-in-out",
    once: true,
    mirror: false,
  });

  const latestContainer = document.getElementById("latest-products");
  const loadingSpinner = document.getElementById("loading-products");
  const errorMessage = document.getElementById("error-products");
  const emptyMessage = document.getElementById("empty-products");

  if (!latestContainer) return;

  // Mostrar el spinner mientras carga
  loadingSpinner.style.display = "block";
  latestContainer.style.display = "none";
  errorMessage.classList.add("d-none");
  emptyMessage.classList.add("d-none");

  // Llamar a la API
  fetch("/BarkiOS/ProductsApi")
    .then((res) => {
      if (!res.ok) throw new Error("Error al conectar con el servidor");
      return res.json();
    })
    .then((data) => {
      loadingSpinner.style.display = "none";

      // Validar respuesta
      if (!data.success || !Array.isArray(data.products)) {
        errorMessage.classList.remove("d-none");
        return;
      }

      // ✅ Filtrar productos válidos
      const validProducts = data.products.filter(product => {
        const precio = parseFloat(product.precio);
        const hasValidImage = product.imagen && 
                             product.imagen.trim() !== '' &&
                             product.imagen !== 'public/assets/img/no-image.png';
        
        return precio > 0 && hasValidImage;
      });

      // Si no hay productos válidos
      if (validProducts.length === 0) {
        emptyMessage.classList.remove("d-none");
        return;
      }

      // Mostrar solo los 4 más recientes
      const recientes = validProducts
        .slice(-4)
        .reverse();

      // Renderizar productos
      latestContainer.innerHTML = "";
      recientes.forEach((product, index) => {
        // Asegurar ruta correcta de imagen
        let imagePath = product.imagen;
        if (!imagePath.startsWith('/') && !imagePath.startsWith('http')) {
          imagePath = '/BarkiOS/' + imagePath;
        }

        const card = `
          <div class="col-md-3" data-aos="fade-up" data-aos-delay="${index * 100}">
            <div class="product-card">
              <div class="product-image">
                <span class="product-badge bg-danger">Nuevo</span>
                <img src="${imagePath}" 
                     alt="${product.nombre}"
                     onerror="this.onerror=null; this.src='/BarkiOS/public/assets/img/no-image.png';">
                <div class="product-actions">
                </div>
              </div>
              <div class="product-info">
                <h4>${product.nombre}</h4>
                <p class="product-category">${product.categoria}</p>
                <div class="product-price">$${parseFloat(product.precio).toFixed(2)}</div>
              </div>
            </div>
          </div>`;
        latestContainer.insertAdjacentHTML("beforeend", card);
      });

      latestContainer.style.display = "flex";
      AOS.refresh();
      
      // Log informativo
      if (data.filtered_out > 0) {
        console.info(`✅ ${data.filtered_out} productos excluidos por validación`);
      }
    })
    .catch((err) => {
      console.error("Error cargando novedades:", err);
      loadingSpinner.style.display = "none";
      errorMessage.classList.remove("d-none");
    });
});
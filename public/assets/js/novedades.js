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

  if (!latestContainer) return; // Si no existe, salir

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

      // Si no hay productos
      if (data.products.length === 0) {
        emptyMessage.classList.remove("d-none");
        return;
      }

      // Mostrar solo los 4 más recientes
      const recientes = data.products
        .slice(-4) // últimos 4
        .reverse(); // para mostrar del más nuevo al más antiguo

      // Renderizar productos
      latestContainer.innerHTML = "";
      recientes.forEach((product, index) => {
        const card = `
          <div class="col-md-3" data-aos="fade-up" data-aos-delay="${index * 100}">
            <div class="product-card">
              <div class="product-image">
                <span class="product-badge bg-danger">Nuevo</span>
                <img src="/BarkiOS/${product.imagen}" alt="${product.nombre}">
                <div class="product-actions">
                </div>
              </div>
              <div class="product-info">
                <h4>${product.nombre}</h4>
                <p class="product-category">${product.categoria}</p>
                <div class="product-price">$${product.precio}</div>
              </div>
            </div>
          </div>`;
        latestContainer.insertAdjacentHTML("beforeend", card);
      });

      latestContainer.style.display = "flex";
      AOS.refresh();
    })
    .catch((err) => {
      console.error("Error cargando novedades:", err);
      loadingSpinner.style.display = "none";
      errorMessage.classList.remove("d-none");
    });
});

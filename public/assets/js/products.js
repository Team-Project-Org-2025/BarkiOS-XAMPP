document.addEventListener("DOMContentLoaded", () => {

  // ==========================
  // 📌 1. Load products from API
  // ==========================
  fetch('/BarkiOS/ProductsApi')
    .then(res => res.json())
    .then(data => {
      if (data.success) {
        renderProducts(data.products)
        initializeProductInteractions()
        filterProducts()
      }
    })
    .catch(error => console.error("Error cargando productos:", error))


  // ==========================
  // 🔥 2. Render dynamic products
  // ==========================
  function renderProducts(products) {
    const productsContainer = document.querySelector(".products-grid")
    productsContainer.innerHTML = ""

    products.forEach(product => {
      const card = `
        <div class="col-md-4 col-sm-6 product-item" 
          data-category="${product.categoria}"
          data-price="${product.precio}">

          <div class="product-card">
            <div class="product-image">
              <span class="product-badge">Disponible</span>
              <img src="${product.imagen}" alt="${product.nombre}">
              <div class="product-actions">
                <button class="action-btn quick-view" data-product-id="${product.id}">
                  <i class="far fa-eye"></i>
                </button>
              </div>
            </div>

            <div class="product-info">
              <h4>${product.nombre}</h4>
              <p class="product-category">${product.categoria}</p>
              <div class="product-price">$${product.precio}</div>
            </div>
          </div>
        </div>
      `
      productsContainer.insertAdjacentHTML("beforeend", card)
    })

    document.getElementById("product-count").textContent = products.length
  }

  const categoryFilters = document.querySelectorAll(".category-filter")
  const todosCheckbox = document.getElementById("category-todos")
  const applyFiltersBtn = document.getElementById("apply-filters")
  const resetFiltersBtn = document.getElementById("reset-filters")
  const productCount = document.getElementById("product-count")
  const sortBy = document.getElementById("sort-by")
  const priceRange = document.getElementById("price-range")
  const priceValue = document.getElementById("price-value")

  if (priceRange && priceValue) {
    priceRange.addEventListener("input", () => {
      priceValue.textContent = priceRange.value
    })
  }

  if (applyFiltersBtn && resetFiltersBtn) {
    applyFiltersBtn.addEventListener("click", filterProducts)

    resetFiltersBtn.addEventListener("click", () => {
      todosCheckbox.checked = true
      categoryFilters.forEach(filter => {
        if (filter.id !== "category-todos") filter.checked = false
      })

      if (priceRange && priceValue) {
        priceRange.value = 500
        priceValue.textContent = "500"
      }

      if (sortBy) sortBy.value = "relevance"

      filterProducts()
    })

    if (sortBy) sortBy.addEventListener("change", filterProducts)
  }

  function filterProducts() {
    const products = document.querySelectorAll(".products-grid > div")
    let visibleCount = 0

    const selectedCategories = []
    categoryFilters.forEach(filter => {
      if (filter.checked && filter.id !== "category-todos") {
        selectedCategories.push(filter.value)
      }
    })

    const maxPrice = priceRange ? Number.parseFloat(priceRange.value) : 500

    products.forEach(product => {
      const category = product.getAttribute("data-category")
      const price = Number.parseFloat(product.getAttribute("data-price"))

      let show = true

      if (selectedCategories.length > 0 && !selectedCategories.includes(category)) show = false
      if (price > maxPrice) show = false

      product.style.display = show ? "" : "none"
      if (show) visibleCount++
    })

    productCount.textContent = visibleCount

    // Sorting
    const sortValue = sortBy.value
    const visibleProducts = Array.from(products).filter(p => p.style.display !== "none")
    const grid = document.querySelector(".products-grid")

    visibleProducts.sort((a, b) => {
      const priceA = Number.parseFloat(a.getAttribute("data-price"))
      const priceB = Number.parseFloat(b.getAttribute("data-price"))
      return sortValue === "price-low" ? priceA - priceB :
             sortValue === "price-high" ? priceB - priceA : 0
    })

    visibleProducts.forEach(p => grid.appendChild(p))
  }


  // ==========================
  // 🎯 4. Quick View / Cart / Wishlist
  // ==========================
  function initializeProductInteractions() {
    setupQuickView()
    setupAddToCart()
    setupWishlist()
  }

  function setupQuickView() {
    const buttons = document.querySelectorAll(".quick-view")
    const modal = document.getElementById("quickViewModal")
    if (!modal) return

    buttons.forEach(btn => {
      btn.addEventListener("click", () => {
        const card = btn.closest(".product-card")

        document.getElementById("quickViewTitle").textContent = card.querySelector("h4").textContent
        document.getElementById("quickViewPrice").textContent = card.querySelector(".product-price").textContent
        document.getElementById("quickViewCategory").textContent = card.querySelector(".product-category").textContent
        document.getElementById("quickViewCategoryText").textContent = card.querySelector(".product-category").textContent

        const img = card.querySelector("img")
        document.getElementById("quickViewImage").querySelector("img").src = img.src
        document.getElementById("quickViewSku").textContent = "GB-" + btn.dataset.productId

        const bsModal = new bootstrap.Modal(modal)
        bsModal.show()
      })
    })
  }

  function setupAddToCart() {
    document.querySelectorAll(".add-to-cart").forEach(btn => {
      btn.addEventListener("click", () => {
        const title = btn.closest(".product-card").querySelector("h4").textContent
        alert(`"${title}" ha sido añadido al carrito.`)
      })
    })
  }

  function setupWishlist() {
    document.querySelectorAll(".add-to-wishlist").forEach(btn => {
      btn.addEventListener("click", () => {
        const icon = btn.querySelector("i")
        icon.classList.toggle("fas")
        icon.classList.toggle("far")
      })
    })
  }


  // Back to Top Button
  const backToTopButton = document.getElementById("backToTop")
  if (backToTopButton) {
    window.addEventListener("scroll", () => {
      backToTopButton.classList.toggle("active", window.pageYOffset > 300)
    })
    backToTopButton.addEventListener("click", e => {
      e.preventDefault()
      window.scrollTo({ top: 0, behavior: "smooth" })
    })
  }

  if (typeof AOS !== "undefined") {
    AOS.init({ duration: 800, easing: "ease-in-out", once: true })
  }

})

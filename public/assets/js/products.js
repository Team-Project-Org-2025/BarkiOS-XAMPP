document.addEventListener("DOMContentLoaded", () => {
  // Initialize AOS animation library
  if (typeof AOS !== "undefined") {
    AOS.init({
      duration: 800,
      easing: "ease-in-out",
      once: true,
      mirror: false,
    })
  }

  // Back to top button
  const backToTopButton = document.getElementById("backToTop")

  if (backToTopButton) {
    window.addEventListener("scroll", () => {
      if (window.pageYOffset > 300) {
        backToTopButton.classList.add("active")
      } else {
        backToTopButton.classList.remove("active")
      }
    })

    backToTopButton.addEventListener("click", (e) => {
      e.preventDefault()
      window.scrollTo({
        top: 0,
        behavior: "smooth",
      })
    })
  }

  // Price Range Slider
  const priceRange = document.getElementById("price-range")
  const priceValue = document.getElementById("price-value")

  if (priceRange && priceValue) {
    priceRange.addEventListener("input", () => {
      priceValue.textContent = priceRange.value
    })
  }

  // Color Selection
  const colorOptions = document.querySelectorAll(".color-option")
  const selectedColors = document.getElementById("selected-colors")
  let selectedColorsList = []

  if (colorOptions.length > 0 && selectedColors) {
    colorOptions.forEach((option) => {
      option.addEventListener("click", function () {
        const color = this.getAttribute("data-color")

        if (this.classList.contains("selected")) {
          this.classList.remove("selected")
          selectedColorsList = selectedColorsList.filter((c) => c !== color)
        } else {
          this.classList.add("selected")
          selectedColorsList.push(color)
        }

        if (selectedColorsList.length === 0) {
          selectedColors.textContent = "Ninguno"
        } else {
          selectedColors.textContent = selectedColorsList.join(", ")
        }
      })
    })
  }

  // Category Filter
  const categoryFilters = document.querySelectorAll(".category-filter")
  const todosCheckbox = document.getElementById("category-todos")

  if (categoryFilters.length > 0 && todosCheckbox) {
    // Check if URL has category parameter
    const urlParams = new URLSearchParams(window.location.search)
    const categoryParam = urlParams.get("categoria")

    if (categoryParam) {
      // Uncheck "todos" and check the specified category
      todosCheckbox.checked = false
      const categoryCheckbox = document.getElementById(`category-${categoryParam}`)
      if (categoryCheckbox) {
        categoryCheckbox.checked = true
      }

      // Apply filter immediately
      filterProducts()
    }

    todosCheckbox.addEventListener("change", function () {
      if (this.checked) {
        categoryFilters.forEach((filter) => {
          if (filter.id !== "category-todos") {
            filter.checked = false
          }
        })
      }
    })

    categoryFilters.forEach((filter) => {
      if (filter.id !== "category-todos") {
        filter.addEventListener("change", function () {
          if (this.checked) {
            todosCheckbox.checked = false
          }

          // If no category is selected, check "todos"
          const anyChecked = Array.from(categoryFilters).some((f) => f.id !== "category-todos" && f.checked)
          if (!anyChecked) {
            todosCheckbox.checked = true
          }
        })
      }
    })
  }

  // Apply Filters
  const applyFiltersBtn = document.getElementById("apply-filters")
  const resetFiltersBtn = document.getElementById("reset-filters")
  const productCount = document.getElementById("product-count")
  const sortBy = document.getElementById("sort-by")

  if (applyFiltersBtn && resetFiltersBtn) {
    applyFiltersBtn.addEventListener("click", filterProducts)

    resetFiltersBtn.addEventListener("click", () => {
      // Reset category filters
      if (todosCheckbox) {
        todosCheckbox.checked = true
        categoryFilters.forEach((filter) => {
          if (filter.id !== "category-todos") {
            filter.checked = false
          }
        })
      }

      // Reset price range
      if (priceRange && priceValue) {
        priceRange.value = 500
        priceValue.textContent = "500"
      }

      // Reset size filters
      const sizeFilters = document.querySelectorAll(".size-filter")
      sizeFilters.forEach((filter) => {
        filter.checked = false
      })

      // Reset color filters
      if (colorOptions.length > 0 && selectedColors) {
        colorOptions.forEach((option) => {
          option.classList.remove("selected")
        })
        selectedColorsList = []
        selectedColors.textContent = "Ninguno"
      }

      // Reset sort
      if (sortBy) {
        sortBy.value = "relevance"
      }

      // Apply reset filters
      filterProducts()
    })

    // Sort products
    if (sortBy) {
      sortBy.addEventListener("change", filterProducts)
    }

    // Initial filter
    filterProducts()
  }

  function filterProducts() {
    const products = document.querySelectorAll(".products-grid > div")
    let visibleCount = 0

    // Get selected categories
    const selectedCategories = []
    categoryFilters.forEach((filter) => {
      if (filter.checked && filter.id !== "category-todos") {
        selectedCategories.push(filter.value)
      }
    })

    // Get max price
    const maxPrice = priceRange ? Number.parseFloat(priceRange.value) : 500

    // Get selected sizes
    const selectedSizes = []
    const sizeFilters = document.querySelectorAll(".size-filter")
    sizeFilters.forEach((filter) => {
      if (filter.checked) {
        selectedSizes.push(filter.value)
      }
    })

    // Filter products
    products.forEach((product) => {
      const category = product.getAttribute("data-category")
      const price = Number.parseFloat(product.getAttribute("data-price"))
      const color = product.getAttribute("data-color")

      let showProduct = true

      // Filter by category
      if (selectedCategories.length > 0 && !selectedCategories.includes(category)) {
        showProduct = false
      }

      // Filter by price
      if (price > maxPrice) {
        showProduct = false
      }

      // Filter by color
      if (selectedColorsList.length > 0 && !selectedColorsList.includes(color)) {
        showProduct = false
      }

      // Show/hide product
      if (showProduct) {
        product.style.display = ""
        visibleCount++
      } else {
        product.style.display = "none"
      }
    })

    // Update product count
    if (productCount) {
      productCount.textContent = visibleCount
    }

    // Sort products
    if (sortBy) {
      const sortValue = sortBy.value
      const productsArray = Array.from(products).filter((p) => p.style.display !== "none")

      productsArray.sort((a, b) => {
        const priceA = Number.parseFloat(a.getAttribute("data-price"))
        const priceB = Number.parseFloat(b.getAttribute("data-price"))

        if (sortValue === "price-low") {
          return priceA - priceB
        } else if (sortValue === "price-high") {
          return priceB - priceA
        }

        return 0
      })

      const productsGrid = document.querySelector(".products-grid")
      productsArray.forEach((product) => {
        productsGrid.appendChild(product)
      })
    }
  }

  // Quick View Modal
  const quickViewButtons = document.querySelectorAll(".quick-view")
  const quickViewModal = document.getElementById("quickViewModal")

  if (quickViewButtons.length > 0 && quickViewModal) {
    const quickViewTitle = document.getElementById("quickViewTitle")
    const quickViewPrice = document.getElementById("quickViewPrice")
    const quickViewCategory = document.getElementById("quickViewCategory")
    const quickViewCategoryText = document.getElementById("quickViewCategoryText")
    const quickViewImage = document.getElementById("quickViewImage").querySelector("img")
    const quickViewSku = document.getElementById("quickViewSku")

    quickViewButtons.forEach((button) => {
      button.addEventListener("click", function () {
        const productId = this.getAttribute("data-product-id")
        const productCard = this.closest(".product-card")
        const productTitle = productCard.querySelector("h4").textContent
        const productPrice = productCard.querySelector(".product-price").textContent
        const productCategory = productCard.querySelector(".product-category").textContent
        const productImage = productCard.querySelector("img").getAttribute("src")

        // Set modal content
        quickViewTitle.textContent = productTitle
        quickViewPrice.textContent = productPrice
        quickViewCategory.textContent = productCategory
        quickViewCategoryText.textContent = productCategory
        quickViewImage.setAttribute("src", productImage)
        quickViewImage.setAttribute("alt", productTitle)
        quickViewSku.textContent = "GB-" + productId + "-" + Math.floor(Math.random() * 1000)

        // Show modal
        if (typeof bootstrap !== "undefined") {
          const modal = new bootstrap.Modal(quickViewModal)
          modal.show()
        } else {
          console.error("Bootstrap is not defined. Ensure it is properly loaded.")
        }
      })
    })

    // Quantity increment/decrement
    const decrementBtn = document.getElementById("decrementBtn")
    const incrementBtn = document.getElementById("incrementBtn")
    const quantityInput = document.getElementById("quantityInput")

    if (decrementBtn && incrementBtn && quantityInput) {
      decrementBtn.addEventListener("click", () => {
        let value = Number.parseInt(quantityInput.value)
        if (value > 1) {
          value--
          quantityInput.value = value
        }
      })

      incrementBtn.addEventListener("click", () => {
        let value = Number.parseInt(quantityInput.value)
        value++
        quantityInput.value = value
      })
    }
  }

  // Add to Cart
  const addToCartButtons = document.querySelectorAll(".add-to-cart")

  if (addToCartButtons.length > 0) {
    addToCartButtons.forEach((button) => {
      button.addEventListener("click", function () {
        const productId = this.getAttribute("data-product-id")
        const productCard = this.closest(".product-card")
        const productTitle = productCard.querySelector("h4").textContent

        // Show notification
        alert(`"${productTitle}" ha sido añadido al carrito.`)

        // Update cart count
        const cartBadge = document.querySelector(".fa-shopping-bag + .badge")
        if (cartBadge) {
          const count = Number.parseInt(cartBadge.textContent)
          cartBadge.textContent = count + 1
        }
      })
    })
  }

  // Add to Wishlist
  const addToWishlistButtons = document.querySelectorAll(".add-to-wishlist")

  if (addToWishlistButtons.length > 0) {
    addToWishlistButtons.forEach((button) => {
      button.addEventListener("click", function () {
        const icon = this.querySelector("i")

        if (icon.classList.contains("far")) {
          icon.classList.remove("far")
          icon.classList.add("fas")
          // Show notification
          alert("Producto añadido a favoritos.")
        } else {
          icon.classList.remove("fas")
          icon.classList.add("far")
          // Show notification
          alert("Producto eliminado de favoritos.")
        }
      })
    })
  }
})

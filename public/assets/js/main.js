// Preloader
document.addEventListener("DOMContentLoaded", () => {
  // Initialize AOS animation library
  AOS.init({
    duration: 800,
    easing: "ease-in-out",
    once: true,
    mirror: false,
  })

  // Hide preloader when page is loaded
  setTimeout(() => {
    const preloader = document.getElementById("preloader")
    if (preloader) {
      preloader.style.opacity = "0"
      preloader.style.transition = "opacity 0.5s ease"

      setTimeout(() => {
        preloader.style.display = "none"
      }, 500)
    }
  }, 1000)

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

  // Testimonial Slider
  const testimonialItems = document.querySelectorAll(".testimonial-item")
  const testimonialPrev = document.querySelector(".testimonial-prev")
  const testimonialNext = document.querySelector(".testimonial-next")

  if (testimonialItems.length > 0) {
    let currentTestimonial = 0

    // Hide all testimonials except the first one
    testimonialItems.forEach((item, index) => {
      if (index !== 0) {
        item.style.display = "none"
      }
    })

    // Previous testimonial
    testimonialPrev.addEventListener("click", () => {
      testimonialItems[currentTestimonial].style.display = "none"
      currentTestimonial = (currentTestimonial - 1 + testimonialItems.length) % testimonialItems.length
      testimonialItems[currentTestimonial].style.display = "block"
    })

    // Next testimonial
    testimonialNext.addEventListener("click", () => {
      testimonialItems[currentTestimonial].style.display = "none"
      currentTestimonial = (currentTestimonial + 1) % testimonialItems.length
      testimonialItems[currentTestimonial].style.display = "block"
    })
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
        const modal = new bootstrap.Modal(quickViewModal)
        modal.show()
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

  // Newsletter Form
  const newsletterForm = document.getElementById("newsletterForm")

  if (newsletterForm) {
    newsletterForm.addEventListener("submit", function (e) {
      e.preventDefault()
      const emailInput = this.querySelector('input[type="email"]')

      if (emailInput.value.trim() !== "") {
        alert("¡Gracias por suscribirte a nuestro newsletter!")
        emailInput.value = ""
      }
    })
  }
})

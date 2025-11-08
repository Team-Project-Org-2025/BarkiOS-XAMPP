$(document).ready(function() {
  // Initialize AOS animation library
  AOS.init({
    duration: 800,
    easing: "ease-in-out",
    once: true,
    mirror: false,
  });

  // Hide preloader when page is loaded
  setTimeout(function() {
    $("#preloader").css({
      opacity: "0",
      transition: "opacity 0.5s ease"
    });

    setTimeout(function() {
      $("#preloader").css("display", "none");
    }, 500);
  }, 1000);

  // Back to top button
  $(window).on("scroll", function() {
    if ($(window).scrollTop() > 300) {
      $("#backToTop").addClass("active");
    } else {
      $("#backToTop").removeClass("active");
    }
  });

  $("#backToTop").on("click", function(e) {
    e.preventDefault();
    $("html, body").animate({
      scrollTop: 0
    }, 600);
  });

  // Testimonial Slider
  const $testimonialItems = $(".testimonial-item");
  
  if ($testimonialItems.length > 0) {
    let currentTestimonial = 0;

    // Hide all testimonials except the first one
    $testimonialItems.not(":first").hide();

    // Previous testimonial
    $(".testimonial-prev").on("click", function() {
      $testimonialItems.eq(currentTestimonial).hide();
      currentTestimonial = (currentTestimonial - 1 + $testimonialItems.length) % $testimonialItems.length;
      $testimonialItems.eq(currentTestimonial).show();
    });

    // Next testimonial
    $(".testimonial-next").on("click", function() {
      $testimonialItems.eq(currentTestimonial).hide();
      currentTestimonial = (currentTestimonial + 1) % $testimonialItems.length;
      $testimonialItems.eq(currentTestimonial).show();
    });
  }

  // Quick View Modal
  $(document).on("click", ".quick-view", function() {
    const productId = $(this).data("product-id");
    const $productCard = $(this).closest(".product-card");
    const productTitle = $productCard.find("h4").text();
    const productPrice = $productCard.find(".product-price").text();
    const productCategory = $productCard.find(".product-category").text();
    const productImage = $productCard.find("img").attr("src");

    // Set modal content
    $("#quickViewTitle").text(productTitle);
    $("#quickViewPrice").text(productPrice);
    $("#quickViewCategory").text(productCategory);
    $("#quickViewCategoryText").text(productCategory);
    $("#quickViewImage img").attr({
      src: productImage,
      alt: productTitle
    });
    $("#quickViewSku").text("GB-" + productId + "-" + Math.floor(Math.random() * 1000));

    // Show modal
    const modal = new bootstrap.Modal(document.getElementById("quickViewModal"));
    modal.show();
  });

  // Quantity increment/decrement
  $("#decrementBtn").on("click", function() {
    const $input = $("#quantityInput");
    let value = parseInt($input.val());
    if (value > 1) {
      value--;
      $input.val(value);
    }
  });

  $("#incrementBtn").on("click", function() {
    const $input = $("#quantityInput");
    let value = parseInt($input.val());
    value++;
    $input.val(value);
  });

  // Add to Cart
  $(document).on("click", ".add-to-cart", function() {
    const productId = $(this).data("product-id");
    const $productCard = $(this).closest(".product-card");
    const productTitle = $productCard.find("h4").text();

    // Show notification
    alert(`"${productTitle}" ha sido añadido al carrito.`);

    // Update cart count
    const $cartBadge = $(".fa-shopping-bag").next(".badge");
    if ($cartBadge.length) {
      const count = parseInt($cartBadge.text());
      $cartBadge.text(count + 1);
    }
  });

  // Add to Wishlist
  $(document).on("click", ".add-to-wishlist", function() {
    const $icon = $(this).find("i");

    if ($icon.hasClass("far")) {
      $icon.removeClass("far").addClass("fas");
      alert("Producto añadido a favoritos.");
    } else {
      $icon.removeClass("fas").addClass("far");
      alert("Producto eliminado de favoritos.");
    }
  });

  // Newsletter Form
  $("#newsletterForm").on("submit", function(e) {
    e.preventDefault();
    const $emailInput = $(this).find('input[type="email"]');

    if ($emailInput.val().trim() !== "") {
      alert("¡Gracias por suscribirte a nuestro newsletter!");
      $emailInput.val("");
    }
  });

  // Load Featured Products
  const $featuredContainer = $("#featuredProducts");

  if ($featuredContainer.length) {
    $.ajax({
      url: "/BarkiOS/ProductsApi",
      method: "GET",
      dataType: "json",
      success: function(data) {
        if (!data.success || !data.products) return;
        
        const destacados = data.products.slice(0, 4);

        destacados.forEach(function(product, index) {
          const card = `
            <div class="col-md-3" data-aos="fade-up" data-aos-delay="${index * 100}">
              <div class="product-card">
                <div class="product-image">
                  <span class="product-badge">DISPONIBLE</span>
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
          $featuredContainer.append(card);
        });

        AOS.refresh();
      },
      error: function(err) {
        console.error("Error cargando destacados:", err);
      }
    });
  }

  // Load Latest Products
  const $latestContainer = $("#latest-products");

  if ($latestContainer.length) {
    $.ajax({
      url: "/BarkiOS/app/controllers/front/ProductsApiController.php",
      method: "POST",
      data: {
        limit: 8
      },
      dataType: "json",
      success: function(data) {
        if (!data.success || data.products.length === 0) {
          $latestContainer.html("<p class='text-center'>No hay productos nuevos.</p>");
          return;
        }

        $latestContainer.empty();

        data.products.forEach(function(product, index) {
          const card = `
            <div class="col-md-3" data-aos="fade-up" data-aos-delay="${index * 100}">
              <div class="product-card">
                <div class="product-image">
                  <span class="product-badge bg-danger">Nuevo</span>
                  <img src="/BarkiOS/${product.imagen}" alt="${product.nombre}">
                  <div class="product-actions">
                    <button class="action-btn add-to-wishlist" data-product-id="${product.prenda_id}">
                      <i class="far fa-heart"></i>
                    </button>
                    <button class="action-btn quick-view" data-product-id="${product.prenda_id}">
                      <i class="far fa-eye"></i>
                    </button>
                  </div>
                </div>
                <div class="product-info">
                  <h4>${product.nombre}</h4>
                  <p class="product-category">${product.categoria}</p>
                  <div class="product-price">$${product.precio}</div>
                  <button class="btn btn-dark w-100 add-to-cart" data-product-id="${product.prenda_id}">
                    Agregar al Carrito
                  </button>
                </div>
              </div>
            </div>`;
          $latestContainer.append(card);
        });

        AOS.refresh();
      },
      error: function(err) {
        console.error("Error cargando novedades:", err);
        $latestContainer.html("<p class='text-center text-danger'>Error al cargar productos.</p>");
      }
    });
  }
});
$(document).ready(function() {
  AOS.init({
    duration: 800,
    easing: "ease-in-out",
    once: true,
    mirror: false,
  });

  setTimeout(function() {
    const $preloader = $("#preloader");
    if ($preloader.length) {
      $preloader.css({
        opacity: "0",
        transition: "opacity 0.5s ease"
      });

      setTimeout(function() {
        $preloader.hide();
      }, 500);
    }
  }, 1000);

  const $backToTopButton = $("#backToTop");

  if ($backToTopButton.length) {
    $(window).on("scroll", function() {
      if ($(window).scrollTop() > 300) {
        $backToTopButton.addClass("active");
      } else {
        $backToTopButton.removeClass("active");
      }
    });

    $backToTopButton.on("click", function(e) {
      e.preventDefault();
      $("html, body").animate({
        scrollTop: 0
      }, 500);
    });
  }

  const $testimonialItems = $(".testimonial-item");
  const $testimonialPrev = $(".testimonial-prev");
  const $testimonialNext = $(".testimonial-next");

  if ($testimonialItems.length > 0) {
    let currentTestimonial = 0;

    $testimonialItems.each(function(index) {
      if (index !== 0) {
        $(this).hide();
      }
    });

    $testimonialPrev.on("click", function() {
      $testimonialItems.eq(currentTestimonial).hide();
      currentTestimonial = (currentTestimonial - 1 + $testimonialItems.length) % $testimonialItems.length;
      $testimonialItems.eq(currentTestimonial).show();
    });

    $testimonialNext.on("click", function() {
      $testimonialItems.eq(currentTestimonial).hide();
      currentTestimonial = (currentTestimonial + 1) % $testimonialItems.length;
      $testimonialItems.eq(currentTestimonial).show();
    });
  }

  const $quickViewButtons = $(".quick-view");
  const $quickViewModal = $("#quickViewModal");

  if ($quickViewButtons.length > 0 && $quickViewModal.length) {
    const $quickViewTitle = $("#quickViewTitle");
    const $quickViewPrice = $("#quickViewPrice");
    const $quickViewCategory = $("#quickViewCategory");
    const $quickViewCategoryText = $("#quickViewCategoryText");
    const $quickViewImage = $("#quickViewImage").find("img");
    const $quickViewSku = $("#quickViewSku");

    $quickViewButtons.on("click", function() {
      const productId = $(this).attr("data-product-id");
      const $productCard = $(this).closest(".product-card");
      const productTitle = $productCard.find("h4").text();
      const productPrice = $productCard.find(".product-price").text();
      const productCategory = $productCard.find(".product-category").text();
      const productImage = $productCard.find("img").attr("src");

      $quickViewTitle.text(productTitle);
      $quickViewPrice.text(productPrice);
      $quickViewCategory.text(productCategory);
      $quickViewCategoryText.text(productCategory);
      $quickViewImage.attr("src", productImage).attr("alt", productTitle);
      $quickViewSku.text("GB-" + productId + "-" + Math.floor(Math.random() * 1000));

      const modal = new bootstrap.Modal($quickViewModal[0]);
      modal.show();
    });

    const $decrementBtn = $("#decrementBtn");
    const $incrementBtn = $("#incrementBtn");
    const $quantityInput = $("#quantityInput");

    if ($decrementBtn.length && $incrementBtn.length && $quantityInput.length) {
      $decrementBtn.on("click", function() {
        let value = parseInt($quantityInput.val());
        if (value > 1) {
          value--;
          $quantityInput.val(value);
        }
      });

      $incrementBtn.on("click", function() {
        let value = parseInt($quantityInput.val());
        value++;
        $quantityInput.val(value);
      });
    }
  }

  const $addToCartButtons = $(".add-to-cart");

  if ($addToCartButtons.length > 0) {
    $addToCartButtons.on("click", function() {
      const productId = $(this).attr("data-product-id");
      const $productCard = $(this).closest(".product-card");
      const productTitle = $productCard.find("h4").text();

      alert(`"${productTitle}" ha sido añadido al carrito.`);


      const $cartBadge = $(".fa-shopping-bag").siblings(".badge");
      if ($cartBadge.length) {
        const count = parseInt($cartBadge.text());
        $cartBadge.text(count + 1);
      }
    });
  }

  const $addToWishlistButtons = $(".add-to-wishlist");

  if ($addToWishlistButtons.length > 0) {
    $addToWishlistButtons.on("click", function() {
      const $icon = $(this).find("i");

      if ($icon.hasClass("far")) {
        $icon.removeClass("far").addClass("fas");
        alert("Producto añadido a favoritos.");
      } else {
        $icon.removeClass("fas").addClass("far");
        alert("Producto eliminado de favoritos.");
      }
    });
  }

  const $newsletterForm = $("#newsletterForm");

  if ($newsletterForm.length) {
    $newsletterForm.on("submit", function(e) {
      e.preventDefault();
      const $emailInput = $(this).find('input[type="email"]');

      if ($emailInput.val().trim() !== "") {
        alert("¡Gracias por suscribirte a nuestro newsletter!");
        $emailInput.val("");
      }
    });
  }

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

  const $latestContainer = $("#latest-products");

  if ($latestContainer.length) {
    $.ajax({
      url: "/BarkiOS/app/controllers/front/ProductsApiController.php",
      method: "POST",
      data: { limit: 8 },
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
<?php $pageTitle = "Inicio | Garage Barki"; ?>
<?php require_once __DIR__ . '/../partials/header.php';?>
    <div id="preloader">
        <div class="loader">
            <div class="logo-container">
                <h1 class="preloader-logo">GARAGE<span>BARKI</span></h1>
            </div>
            <div class="loading-text">Exclusividad en cada prenda</div>
        </div>
    </div>

<?php require_once __DIR__ . '/../partials/navbar.php';?>

    <!-- Main Content (View) -->
    <main>
        <!-- Hero Section -->
        <div id="heroCarousel" class="carousel slide carousel-fade" data-bs-ride="carousel">
            <div class="carousel-indicators">
                <button type="button" data-bs-target="#heroCarousel" data-bs-slide-to="0" class="active" aria-current="true" aria-label="Slide 1"></button>
                <button type="button" data-bs-target="#heroCarousel" data-bs-slide-to="1" aria-label="Slide 2"></button>
                <button type="button" data-bs-target="#heroCarousel" data-bs-slide-to="2" aria-label="Slide 3"></button>
            </div>
            <div class="carousel-inner">
                <div class="carousel-item active">
                    <div class="hero-slide d-flex align-items-center" style="background-image: url('https://images.unsplash.com/photo-1490481651871-ab68de25d43d?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=1470&q=80');">
                        <div class="container">
                            <div class="carousel-caption text-start">
                                <h2 class="display-3 fw-bold" data-aos="fade-up">EXCLUSIVIDAD EN CADA PRENDA</h2>
                                <p class="lead" data-aos="fade-up" data-aos-delay="200">Descubre piezas únicas que nunca se repiten. Más de 14,000 unidades exclusivas.</p>
                                <a href="views/productos.html" class="btn btn-lg btn-dark" data-aos="fade-up" data-aos-delay="400">Explorar Colección</a>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="carousel-item">
                    <div class="hero-slide d-flex align-items-center" style="background-image: url('https://images.unsplash.com/photo-1469334031218-e382a71b716b?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=1470&q=80');">
                        <div class="container">
                            <div class="carousel-caption">
                                <h2 class="display-3 fw-bold">ELEGANCIA PERSONALIZADA</h2>
                                <p class="lead">Cada prenda cuenta una historia única. Encuentra la tuya.</p>
                                <a href="views/novedades.html" class="btn btn-lg btn-dark">Ver Novedades</a>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="carousel-item">
                    <div class="hero-slide d-flex align-items-center" style="background-image: url('https://images.unsplash.com/photo-1483985988355-763728e1935b?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=1470&q=80');">
                        <div class="container">
                            <div class="carousel-caption text-end">
                                <h2 class="display-3 fw-bold">PARA TODAS LAS MUJERES</h2>
                                <p class="lead">Sin importar edad o talla, tenemos la prenda perfecta para ti.</p>
                                <a href="views/sobre-nosotros.html" class="btn btn-lg btn-dark">Conócenos</a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <button class="carousel-control-prev" type="button" data-bs-target="#heroCarousel" data-bs-slide="prev">
                <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                <span class="visually-hidden">Previous</span>
            </button>
            <button class="carousel-control-next" type="button" data-bs-target="#heroCarousel" data-bs-slide="next">
                <span class="carousel-control-next-icon" aria-hidden="true"></span>
                <span class="visually-hidden">Next</span>
            </button>
        </div>

        <!-- Unique Selling Proposition -->
        <section class="py-5 bg-light">
            <div class="container">
                <div class="row g-4 text-center">
                    <div class="col-md-4" data-aos="fade-up">
                        <div class="feature-box">
                            <div class="feature-icon">
                                <i class="fas fa-fingerprint"></i>
                            </div>
                            <h3>Exclusividad Total</h3>
                            <p>Cada prenda es única. Nunca vendemos dos unidades iguales.</p>
                        </div>
                    </div>
                    <div class="col-md-4" data-aos="fade-up" data-aos-delay="200">
                        <div class="feature-box">
                            <div class="feature-icon">
                                <i class="fas fa-award"></i>
                            </div>
                            <h3>Calidad Premium</h3>
                            <p>Materiales seleccionados y acabados impecables en cada pieza.</p>
                        </div>
                    </div>
                    <div class="col-md-4" data-aos="fade-up" data-aos-delay="400">
                        <div class="feature-box">
                            <div class="feature-icon">
                                <i class="fas fa-female"></i>
                            </div>
                            <h3>Para Todas</h3>
                            <p>Diseños que celebran la belleza de cada mujer, sin importar talla o edad.</p>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Categories Section -->
        <section class="py-5">
            <div class="container">
                <h2 class="text-center mb-5" data-aos="fade-up">CATEGORÍAS DESTACADAS</h2>
                <div class="row g-4">
                    <div class="col-md-4" data-aos="fade-up">
                        <a href="views/productos.html?categoria=vestidos" class="category-card">
                            <div class="category-image">
                                <img src="https://images.unsplash.com/photo-1595777457583-95e059d581b8?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=783&q=80" alt="Vestidos">
                                <div class="category-overlay">
                                    <h3>Vestidos</h3>
                                    <p>Elegancia en cada diseño</p>
                                </div>
                            </div>
                        </a>
                    </div>
                    <div class="col-md-4" data-aos="fade-up" data-aos-delay="200">
                        <a href="views/productos.html?categoria=blusas" class="category-card">
                            <div class="category-image">
                                <img src="https://images.unsplash.com/photo-1564257631407-4deb1f99d992?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=774&q=80" alt="Blusas">
                                <div class="category-overlay">
                                    <h3>Blusas</h3>
                                    <p>Sofisticación para cada ocasión</p>
                                </div>
                            </div>
                        </a>
                    </div>
                    <div class="col-md-4" data-aos="fade-up" data-aos-delay="400">
                        <a href="views/productos.html?categoria=pantalones" class="category-card">
                            <div class="category-image">
                                <img src="https://images.unsplash.com/photo-1584370848010-d7fe6bc767ec?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=774&q=80" alt="Pantalones">
                                <div class="category-overlay">
                                    <h3>Pantalones</h3>
                                    <p>Comodidad y estilo en cada paso</p>
                                </div>
                            </div>
                        </a>
                    </div>
                </div>
                <div class="text-center mt-5">
                    <a href="views/productos.html" class="btn btn-outline-dark btn-lg">Ver Todas las Categorías</a>
                </div>
            </div>
        </section>

        <!-- Featured Products -->
        <section class="py-5 bg-light">
            <div class="container">
                <div class="d-flex justify-content-between align-items-center mb-5">
                    <h2 data-aos="fade-right">PRENDAS DESTACADAS</h2>
                    <a href="views/productos.html" class="btn btn-link text-dark text-decoration-none" data-aos="fade-left">Ver Todas <i class="fas fa-arrow-right ms-2"></i></a>
                </div>
                <div class="row g-4 featured-products">
                    <!-- Product 1 -->
                    <div class="col-md-3" data-aos="fade-up">
                        <div class="product-card">
                            <div class="product-image">
                                <span class="product-badge">Exclusivo</span>
                                <img src="https://images.unsplash.com/photo-1551803091-e20673f15770?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=735&q=80" alt="Vestido Elegante">
                                <div class="product-actions">
                                    <button class="action-btn add-to-wishlist" data-product-id="1"><i class="far fa-heart"></i></button>
                                    <button class="action-btn quick-view" data-product-id="1"><i class="far fa-eye"></i></button>
                                </div>
                            </div>
                            <div class="product-info">
                                <h4>Vestido Elegante</h4>
                                <p class="product-category">Vestidos</p>
                                <div class="product-price">$189.99</div>
                                <button class="btn btn-dark w-100 add-to-cart" data-product-id="1">Agregar al Carrito</button>
                            </div>
                        </div>
                    </div>
                    <!-- Product 2 -->
                    <div class="col-md-3" data-aos="fade-up" data-aos-delay="100">
                        <div class="product-card">
                            <div class="product-image">
                                <span class="product-badge">Exclusivo</span>
                                <img src="https://images.unsplash.com/photo-1525507119028-ed4c629a60a3?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=735&q=80" alt="Blusa Sofisticada">
                                <div class="product-actions">
                                    <button class="action-btn add-to-wishlist" data-product-id="2"><i class="far fa-heart"></i></button>
                                    <button class="action-btn quick-view" data-product-id="2"><i class="far fa-eye"></i></button>
                                </div>
                            </div>
                            <div class="product-info">
                                <h4>Blusa Sofisticada</h4>
                                <p class="product-category">Blusas</p>
                                <div class="product-price">$89.99</div>
                                <button class="btn btn-dark w-100 add-to-cart" data-product-id="2">Agregar al Carrito</button>
                            </div>
                        </div>
                    </div>
                    <!-- Product 3 -->
                    <div class="col-md-3" data-aos="fade-up" data-aos-delay="200">
                        <div class="product-card">
                            <div class="product-image">
                                <span class="product-badge">Exclusivo</span>
                                <img src="https://images.unsplash.com/photo-1509551388413-e18d0ac5d495?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=687&q=80" alt="Pantalón de Vestir">
                                <div class="product-actions">
                                    <button class="action-btn add-to-wishlist" data-product-id="3"><i class="far fa-heart"></i></button>
                                    <button class="action-btn quick-view" data-product-id="3"><i class="far fa-eye"></i></button>
                                </div>
                            </div>
                            <div class="product-info">
                                <h4>Pantalón de Vestir</h4>
                                <p class="product-category">Pantalones</p>
                                <div class="product-price">$129.99</div>
                                <button class="btn btn-dark w-100 add-to-cart" data-product-id="3">Agregar al Carrito</button>
                            </div>
                        </div>
                    </div>
                    <!-- Product 4 -->
                    <div class="col-md-3" data-aos="fade-up" data-aos-delay="300">
                        <div class="product-card">
                            <div class="product-image">
                                <span class="product-badge">Exclusivo</span>
                                <img src="https://images.unsplash.com/photo-1548624313-0396c75e4b1a?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=687&q=80" alt="Blazer Clásico">
                                <div class="product-actions">
                                    <button class="action-btn add-to-wishlist" data-product-id="4"><i class="far fa-heart"></i></button>
                                    <button class="action-btn quick-view" data-product-id="4"><i class="far fa-eye"></i></button>
                                </div>
                            </div>
                            <div class="product-info">
                                <h4>Blazer Clásico</h4>
                                <p class="product-category">Blazers</p>
                                <div class="product-price">$159.99</div>
                                <button class="btn btn-dark w-100 add-to-cart" data-product-id="4">Agregar al Carrito</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- About Us Preview -->
        <section class="py-5">
            <div class="container">
                <div class="row align-items-center">
                    <div class="col-lg-6 mb-4 mb-lg-0" data-aos="fade-right">
                        <img src="https://images.unsplash.com/photo-1441984904996-e0b6ba687e04?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=1470&q=80" class="img-fluid rounded" alt="Sobre Nosotros">
                    </div>
                    <div class="col-lg-6" data-aos="fade-left">
                        <h2 class="mb-4">GARAGE BARKI</h2>
                        <p class="lead mb-4">Donde la exclusividad se encuentra con la elegancia.</p>
                        <p>En Garage Barki, creemos que cada mujer merece sentirse única. Por eso, cada una de nuestras más de 14,000 prendas es completamente exclusiva, garantizando que nunca encontrarás a alguien con la misma prenda que tú.</p>
                        <p>Nuestro compromiso es ofrecer moda de alta calidad que celebre la individualidad de cada mujer, sin importar su edad o talla.</p>
                        <a href="views/sobre-nosotros.html" class="btn btn-outline-dark mt-3">Conoce Nuestra Historia</a>
                    </div>
                </div>
            </div>
        </section>

    <!-- Footer (View Component) -->
<?php require_once __DIR__ . '/../partials/footer.php';?>

    <!-- Quick View Modal -->
    <div class="modal fade" id="quickViewModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-xl">
            <div class="modal-content">
                <div class="modal-header border-0">
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div id="quickViewImage" class="quick-view-image">
                                <img src="/placeholder.svg" alt="Product Image" class="img-fluid">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="quick-view-content">
                                <h2 id="quickViewTitle"></h2>
                                <div class="product-price mb-3" id="quickViewPrice"></div>
                                <p id="quickViewCategory" class="mb-3"></p>
                                <p id="quickViewDescription" class="mb-4">Prenda exclusiva de Garage Barki. Cada pieza es única y no se repite, garantizando tu individualidad y estilo personal.</p>
                                
                                <div class="mb-4">
                                    <h5 class="mb-3">Talla</h5>
                                    <div class="d-flex size-options">
                                        <button class="btn btn-outline-dark me-2">XS</button>
                                        <button class="btn btn-outline-dark me-2">S</button>
                                        <button class="btn btn-outline-dark me-2">M</button>
                                        <button class="btn btn-outline-dark me-2">L</button>
                                        <button class="btn btn-outline-dark">XL</button>
                                    </div>
                                </div>
                                
                                <div class="mb-4">
                                    <h5 class="mb-3">Cantidad</h5>
                                    <div class="input-group quantity-selector" style="width: 130px;">
                                        <button class="btn btn-outline-dark" type="button" id="decrementBtn">-</button>
                                        <input type="text" class="form-control text-center" value="1" id="quantityInput">
                                        <button class="btn btn-outline-dark" type="button" id="incrementBtn">+</button>
                                    </div>
                                </div>
                                
                                <div class="d-grid gap-2 d-md-flex mb-4">
                                    <button class="btn btn-dark btn-lg flex-grow-1" id="quickViewAddToCart">Agregar al Carrito</button>
                                    <button class="btn btn-outline-dark btn-lg" id="quickViewAddToWishlist"><i class="far fa-heart"></i></button>
                                </div>
                                
                                <div class="product-meta">
                                    <p class="mb-1"><strong>SKU:</strong> <span id="quickViewSku"></span></p>
                                    <p class="mb-1"><strong>Categoría:</strong> <span id="quickViewCategoryText"></span></p>
                                    <p class="mb-0"><strong>Compartir:</strong> 
                                        <a href="#" class="text-dark me-2"><i class="fab fa-facebook-f"></i></a>
                                        <a href="#" class="text-dark me-2"><i class="fab fa-twitter"></i></a>
                                        <a href="#" class="text-dark me-2"><i class="fab fa-pinterest"></i></a>
                                        <a href="#" class="text-dark"><i class="fab fa-instagram"></i></a>
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Back to Top Button -->
    <a href="#" class="back-to-top" id="backToTop"><i class="fas fa-chevron-up"></i></a>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <!-- AOS Animation Library -->
    <script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
    <!-- Controller Scripts -->
    <script src="../../../public/assets/js/main.js"></script>
</body>
</html>

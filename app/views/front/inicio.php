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
                                <a href="/BarkiOS/productos" class="btn btn-lg btn-dark" data-aos="fade-up" data-aos-delay="400">Explorar Colección</a>
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
                                <a href="/BarkiOS/novedades" class="btn btn-lg btn-dark">Ver Novedades</a>
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
                                <a href="/BarkiOS/nosotros" class="btn btn-lg btn-dark">Conócenos</a>
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
                        <a href="/BarkiOS/productos?categoria=Formal" class="category-card">
                            <div class="category-image">
                                <img src="https://images.unsplash.com/photo-1595777457583-95e059d581b8?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=783&q=80" alt="Vestidos">
                                <div class="category-overlay">
                                    <h3>Formal</h3>
                                    <p>Elegancia en cada diseño</p>
                                </div>
                            </div>
                        </a>
                    </div>
                    <div class="col-md-4" data-aos="fade-up" data-aos-delay="200">
                        <a href="/BarkiOS/productos?categoria=Casual" class="category-card">
                            <div class="category-image">
                                <img src="https://images.unsplash.com/photo-1564257631407-4deb1f99d992?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=774&q=80" alt="Blusas">
                                <div class="category-overlay">
                                    <h3>Casual</h3>
                                    <p>Sofisticación para cada ocasión</p>
                                </div>
                            </div>
                        </a>
                    </div>
                    <div class="col-md-4" data-aos="fade-up" data-aos-delay="400">
                        <a href="/BarkiOS/productos?categoria=Deportivo" class="category-card">
                            <div class="category-image">
                                <img src="https://images.unsplash.com/photo-1584370848010-d7fe6bc767ec?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=774&q=80" alt="Pantalones">
                                <div class="category-overlay">
                                    <h3>Deportivo</h3>
                                    <p>Comodidad y estilo en cada paso</p>
                                </div>
                            </div>
                        </a>
                    </div>
                </div>
                <div class="text-center mt-5">
                    <a href="/BarkiOS/productos" class="btn btn-outline-dark btn-lg">Ver Todas las Categorías</a>
                </div>
            </div>
        </section>

        <!-- Featured Products -->
        <section class="py-5 bg-light">
            <div class="container">
                <div class="d-flex justify-content-between align-items-center mb-5">
                    <h2 data-aos="fade-right">PRENDAS DESTACADAS</h2>
                    <a href="/BarkiOS/productos" class="btn btn-link text-dark text-decoration-none" data-aos="fade-left">Ver Todas <i class="fas fa-arrow-right ms-2"></i></a>
                </div>
                <div class="row g-4 featured-products" id="featuredProducts"></div>

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
                        <a href="/BarkiOS/nosotros" class="btn btn-outline-dark mt-3">Conoce Nuestra Historia</a>
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

                                
                                <div class="product-meta">
                                    <p class="mb-1"><strong>SKU:</strong> <span id="quickViewSku"></span></p>
                                    <p class="mb-1"><strong>Categoría:</strong> <span id="quickViewCategoryText"></span></p>
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
    <script src="/BarkiOS/public/assets/js/main.js"></script>
</body>
</html>

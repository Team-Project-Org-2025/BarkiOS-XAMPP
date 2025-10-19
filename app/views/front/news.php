<?php $pageTitle = "Novedades | Garage Barki"; ?>
<?php require_once __DIR__ . '/../partials/header.php';?>
<?php require_once __DIR__ . '/../partials/navbar.php';?>

    <!-- Main Content -->
    <main>
        <!-- Hero Banner con imagen de fondo elegante -->
        <section class="hero-banner" style="background-image: url('https://images.unsplash.com/photo-1483985988355-763728e1935b?ixlib=rb-4.0.3&auto=format&fit=crop&w=1470&q=80');">
            <div class="hero-banner-content">
                <h1 data-aos="fade-up">NOVEDADES</h1>
                <p data-aos="fade-up" data-aos-delay="200">Descubre las últimas tendencias en moda femenina exclusiva</p>
            </div>
        </section>

        <!-- Sección de nueva colección mejorada -->
        <section class="py-5">
            <div class="container">
                <div class="row align-items-center g-5">
                    <div class="col-lg-6 mb-4 mb-lg-0" data-aos="fade-right">
                        <div class="info-card">
                            <div class="info-card-icon">
                                <i class="fas fa-sparkles"></i>
                            </div>
                            <h2 class="mb-4">NUEVA COLECCIÓN PRIMAVERA 2024</h2>
                            <p class="lead mb-4">Descubre las últimas tendencias en moda femenina.</p>
                            <p>Cada semana agregamos nuevas prendas exclusivas a nuestra colección. Recuerda que cada pieza es única y no se repite, así que si algo te encanta, ¡no lo dejes pasar!</p>
                            <a href="productos.html" class="btn btn-dark mt-3">Ver Toda la Colección</a>
                        </div>
                    </div>
                    <div class="col-lg-6" data-aos="fade-left">
                        <img src="https://images.unsplash.com/photo-1483985988355-763728e1935b?ixlib=rb-4.0.3&auto=format&fit=crop&w=1470&q=80" class="img-fluid rounded shadow" alt="Nueva Colección">
                    </div>
                </div>
            </div>
        </section>

        <!-- Latest Arrivals -->
        <section class="py-5">
            <div class="container">
                <h2 class="text-center mb-5" data-aos="fade-up">RECIÉN LLEGADOS</h2>
                <div class="row g-4">
                    <!-- Product 1 -->
                    <div class="col-md-3" data-aos="fade-up">
                        <div class="product-card">
                            <div class="product-image">
                                <span class="product-badge bg-danger">Nuevo</span>
                                <img src="https://images.unsplash.com/photo-1515372039744-b8f02a3ae446?ixlib=rb-4.0.3&auto=format&fit=crop&w=735&q=80" alt="Vestido Floral">
                                <div class="product-actions">
                                    <button class="action-btn add-to-wishlist" data-product-id="101"><i class="far fa-heart"></i></button>
                                    <button class="action-btn quick-view" data-product-id="101"><i class="far fa-eye"></i></button>
                                </div>
                            </div>
                            <div class="product-info">
                                <h4>Vestido Floral Primavera</h4>
                                <p class="product-category">Vestidos</p>
                                <div class="product-price">$199.99</div>
                                <button class="btn btn-dark w-100 add-to-cart" data-product-id="101">Agregar al Carrito</button>
                            </div>
                        </div>
                    </div>
                    <!-- Product 2 -->
                    <div class="col-md-3" data-aos="fade-up" data-aos-delay="100">
                        <div class="product-card">
                            <div class="product-image">
                                <span class="product-badge bg-danger">Nuevo</span>
                                <img src="https://images.unsplash.com/photo-1539008835657-9e8e9680c956?ixlib=rb-4.0.3&auto=format&fit=crop&w=735&q=80" alt="Blusa Satinada">
                                <div class="product-actions">
                                    <button class="action-btn add-to-wishlist" data-product-id="102"><i class="far fa-heart"></i></button>
                                    <button class="action-btn quick-view" data-product-id="102"><i class="far fa-eye"></i></button>
                                </div>
                            </div>
                            <div class="product-info">
                                <h4>Blusa Satinada Elegante</h4>
                                <p class="product-category">Blusas</p>
                                <div class="product-price">$95.99</div>
                                <button class="btn btn-dark w-100 add-to-cart" data-product-id="102">Agregar al Carrito</button>
                            </div>
                        </div>
                    </div>
                    <!-- Product 3 -->
                    <div class="col-md-3" data-aos="fade-up" data-aos-delay="200">
                        <div class="product-card">
                            <div class="product-image">
                                <span class="product-badge bg-danger">Nuevo</span>
                                <img src="https://images.unsplash.com/photo-1594633312681-425c7b97ccd1?ixlib=rb-4.0.3&auto=format&fit=crop&w=687&q=80" alt="Pantalón Palazzo">
                                <div class="product-actions">
                                    <button class="action-btn add-to-wishlist" data-product-id="103"><i class="far fa-heart"></i></button>
                                    <button class="action-btn quick-view" data-product-id="103"><i class="far fa-eye"></i></button>
                                </div>
                            </div>
                            <div class="product-info">
                                <h4>Pantalón Palazzo Fluido</h4>
                                <p class="product-category">Pantalones</p>
                                <div class="product-price">$139.99</div>
                                <button class="btn btn-dark w-100 add-to-cart" data-product-id="103">Agregar al Carrito</button>
                            </div>
                        </div>
                    </div>
                    <!-- Product 4 -->
                    <div class="col-md-3" data-aos="fade-up" data-aos-delay="300">
                        <div class="product-card">
                            <div class="product-image">
                                <span class="product-badge bg-danger">Nuevo</span>
                                <img src="https://images.unsplash.com/photo-1594633313593-bab3825d0caf?ixlib=rb-4.0.3&auto=format&fit=crop&w=687&q=80" alt="Conjunto Casual">
                                <div class="product-actions">
                                    <button class="action-btn add-to-wishlist" data-product-id="104"><i class="far fa-heart"></i></button>
                                    <button class="action-btn quick-view" data-product-id="104"><i class="far fa-eye"></i></button>
                                </div>
                            </div>
                            <div class="product-info">
                                <h4>Conjunto Casual Chic</h4>
                                <p class="product-category">Sets</p>
                                <div class="product-price">$179.99</div>
                                <button class="btn btn-dark w-100 add-to-cart" data-product-id="104">Agregar al Carrito</button>
                            </div>
                        </div>
                    </div>
                    <!-- Product 5 -->
                    <div class="col-md-3" data-aos="fade-up">
                        <div class="product-card">
                            <div class="product-image">
                                <span class="product-badge bg-danger">Nuevo</span>
                                <img src="https://images.unsplash.com/photo-1617019114583-affb34d1b3cd?ixlib=rb-4.0.3&auto=format&fit=crop&w=687&q=80" alt="Falda Midi">
                                <div class="product-actions">
                                    <button class="action-btn add-to-wishlist" data-product-id="105"><i class="far fa-heart"></i></button>
                                    <button class="action-btn quick-view" data-product-id="105"><i class="far fa-eye"></i></button>
                                </div>
                            </div>
                            <div class="product-info">
                                <h4>Falda Midi Plisada</h4>
                                <p class="product-category">Faldas</p>
                                <div class="product-price">$119.99</div>
                                <button class="btn btn-dark w-100 add-to-cart" data-product-id="105">Agregar al Carrito</button>
                            </div>
                        </div>
                    </div>
                    <!-- Product 6 -->
                    <div class="col-md-3" data-aos="fade-up" data-aos-delay="100">
                        <div class="product-card">
                            <div class="product-image">
                                <span class="product-badge bg-danger">Nuevo</span>
                                <img src="https://images.unsplash.com/photo-1596783074918-c84cb06531ca?ixlib=rb-4.0.3&auto=format&fit=crop&w=687&q=80" alt="Blazer Oversize">
                                <div class="product-actions">
                                    <button class="action-btn add-to-wishlist" data-product-id="106"><i class="far fa-heart"></i></button>
                                    <button class="action-btn quick-view" data-product-id="106"><i class="far fa-eye"></i></button>
                                </div>
                            </div>
                            <div class="product-info">
                                <h4>Blazer Oversize Moderno</h4>
                                <p class="product-category">Blazers</p>
                                <div class="product-price">$169.99</div>
                                <button class="btn btn-dark w-100 add-to-cart" data-product-id="106">Agregar al Carrito</button>
                            </div>
                        </div>
                    </div>
                    <!-- Product 7 -->
                    <div class="col-md-3" data-aos="fade-up" data-aos-delay="200">
                        <div class="product-card">
                            <div class="product-image">
                                <span class="product-badge bg-danger">Nuevo</span>
                                <img src="https://images.unsplash.com/photo-1562137369-1a1a0bc66744?ixlib=rb-4.0.3&auto=format&fit=crop&w=687&q=80" alt="Vestido Cóctel">
                                <div class="product-actions">
                                    <button class="action-btn add-to-wishlist" data-product-id="107"><i class="far fa-heart"></i></button>
                                    <button class="action-btn quick-view" data-product-id="107"><i class="far fa-eye"></i></button>
                                </div>
                            </div>
                            <div class="product-info">
                                <h4>Vestido Cóctel Sofisticado</h4>
                                <p class="product-category">Vestidos</p>
                                <div class="product-price">$229.99</div>
                                <button class="btn btn-dark w-100 add-to-cart" data-product-id="107">Agregar al Carrito</button>
                            </div>
                        </div>
                    </div>
                    <!-- Product 8 -->
                    <div class="col-md-3" data-aos="fade-up" data-aos-delay="300">
                        <div class="product-card">
                            <div class="product-image">
                                <span class="product-badge bg-danger">Nuevo</span>
                                <img src="https://images.unsplash.com/photo-1594633313593-bab3825d0caf?ixlib=rb-4.0.3&auto=format&fit=crop&w=687&q=80" alt="Camisa Lino">
                                <div class="product-actions">
                                    <button class="action-btn add-to-wishlist" data-product-id="108"><i class="far fa-heart"></i></button>
                                    <button class="action-btn quick-view" data-product-id="108"><i class="far fa-eye"></i></button>
                                </div>
                            </div>
                            <div class="product-info">
                                <h4>Camisa de Lino Natural</h4>
                                <p class="product-category">Blusas</p>
                                <div class="product-price">$89.99</div>
                                <button class="btn btn-dark w-100 add-to-cart" data-product-id="108">Agregar al Carrito</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Sección de tendencias mejorada con cards más elegantes -->
        <section class="py-5 bg-light">
            <div class="container">
                <h2 class="text-center mb-5" data-aos="fade-up">TENDENCIAS DE LA TEMPORADA</h2>
                <div class="row g-4">
                    <div class="col-md-4" data-aos="fade-up">
                        <div class="trend-card">
                            <div class="trend-icon">
                                <i class="fas fa-palette"></i>
                            </div>
                            <h3>Colores Pastel</h3>
                            <p>Los tonos suaves dominan esta temporada. Rosa, lavanda y menta son los protagonistas que aportan frescura y elegancia a cualquier look.</p>
                        </div>
                    </div>
                    <div class="col-md-4" data-aos="fade-up" data-aos-delay="100">
                        <div class="trend-card">
                            <div class="trend-icon">
                                <i class="fas fa-leaf"></i>
                            </div>
                            <h3>Estampados Florales</h3>
                            <p>Las flores grandes y vibrantes regresan con fuerza en vestidos y blusas, perfectas para la temporada primaveral.</p>
                        </div>
                    </div>
                    <div class="col-md-4" data-aos="fade-up" data-aos-delay="200">
                        <div class="trend-card">
                            <div class="trend-icon">
                                <i class="fas fa-tshirt"></i>
                            </div>
                            <h3>Siluetas Oversize</h3>
                            <p>La comodidad se une al estilo con prendas holgadas y relajadas que no sacrifican la elegancia.</p>
                        </div>
                    </div>
                </div>
            </div>
        </section>

    <!-- Footer -->
<?php require_once __DIR__ . '/../partials/footer.php';?>

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

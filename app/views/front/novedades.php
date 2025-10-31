<?php 
$pageTitle = "Novedades | Garage Barki"; 
require_once __DIR__ . '/../partials/header.php';
require_once __DIR__ . '/../partials/navbar.php';
?>

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
                        <h2 class="mb-4">NUEVA COLECCIÓN NOVIEMBRE 2025</h2>
                        <p class="lead mb-4">Descubre las últimas tendencias en moda femenina.</p>
                        <p>Cada semana agregamos nuevas prendas exclusivas a nuestra colección. Recuerda que cada pieza es única y no se repite, así que si algo te encanta, ¡no lo dejes pasar!</p>
                        <a href="/BarkiOS/productos" class="btn btn-dark mt-3">Ver Toda la Colección</a>
                    </div>
                </div>
                <div class="col-lg-6" data-aos="fade-left">
                    <img src="https://images.unsplash.com/photo-1483985988355-763728e1935b?ixlib=rb-4.0.3&auto=format&fit=crop&w=1470&q=80" class="img-fluid rounded shadow" alt="Nueva Colección">
                </div>
            </div>
        </div>
    </section>

    <!-- Latest Arrivals -->
    <section class="py-5 bg-light">
        <div class="container">
            <h2 class="text-center mb-5" data-aos="fade-up">
                RECIÉN LLEGADOS
            </h2>
            
            <!-- Loading Spinner -->
            <div id="loading-products" class="text-center py-5">
                <div class="spinner-border text-primary" role="status" style="width: 3rem; height: 3rem;">
                    <span class="visually-hidden">Cargando productos...</span>
                </div>
                <p class="mt-3 text-muted">Cargando las últimas novedades...</p>
            </div>

            <!-- Products Grid -->
            <div class="row g-4" id="latest-products" style="display: none;">
            </div>

            <!-- Error Message -->
            <div id="error-products" class="alert alert-danger d-none" role="alert">
                <i class="fas fa-exclamation-triangle me-2"></i>
                <strong>Error:</strong> No se pudieron cargar los productos. Por favor, intenta nuevamente más tarde.
            </div>

            <!-- Empty Message -->
            <div id="empty-products" class="alert alert-info d-none text-center" role="alert">
                <i class="fas fa-info-circle me-2"></i>
                No hay productos nuevos disponibles en este momento. ¡Vuelve pronto!
            </div>
        </div>
    </section>

    <!-- Sección de tendencias mejorada con cards más elegantes -->
    <section class="py-5">
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
</main>

<!-- Quick View Modal -->
<div class="modal fade" id="quickViewModal" tabindex="-1" aria-labelledby="quickViewModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header border-0">
                <h5 class="modal-title" id="quickViewModalLabel">Vista Rápida</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-6" id="quickViewImage">
                        <img src="" alt="" class="img-fluid rounded">
                    </div>
                    <div class="col-md-6">
                        <span class="badge bg-primary mb-2" id="quickViewCategory"></span>
                        <h3 id="quickViewTitle" class="mb-3"></h3>
                        <h4 class="text-primary mb-3" id="quickViewPrice"></h4>
                        <p class="text-muted mb-4" id="quickViewDescription"></p>
                        <div class="mb-3">
                            <strong>Tipo:</strong> <span id="quickViewTipo"></span>
                        </div>
                        <div class="mb-3">
                            <strong>Categoría:</strong> <span id="quickViewCategoryText"></span>
                        </div>
                        <div class="mb-4">
                            <strong>Código:</strong> <span id="quickViewSku"></span>
                        </div>
                        <div class="d-grid">
                            <button class="btn btn-primary btn-lg add-to-cart" id="quickViewAddToCart">
                                <i class="fas fa-shopping-cart me-2"></i>Agregar al Carrito
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Footer -->
<?php require_once __DIR__ . '/../partials/footer.php'; ?>

<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<!-- AOS Animation Library -->
<script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
<!-- Main JS con funcionalidad de productos -->

<script src="/BarkiOS/public/assets/js/front/novedades.js"></script>

</body>
</html>
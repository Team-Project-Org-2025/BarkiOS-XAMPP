<?php $pageTitle = "Productos | Garage Barki"; ?>
<?php require_once __DIR__ . '/../partials/header.php';?>
<?php require_once __DIR__ . '/../partials/navbar.php';?>

    <!-- Main Content (View) -->
    <main>
        <!-- Page Header -->
        <section class="page-header">
            <div class="container">
                <div class="row">
                    <div class="col-12 text-center">
                        <h1 data-aos="fade-up">PRODUCTOS</h1>
                        <nav aria-label="breadcrumb" data-aos="fade-up" data-aos-delay="200">
                            <ol class="breadcrumb justify-content-center">
                                <li class="breadcrumb-item"><a href="../index.html">Inicio</a></li>
                                <li class="breadcrumb-item active" aria-current="page">Productos</li>
                            </ol>
                        </nav>
                    </div>
                </div>
            </div>
        </section>

        <!-- Products Section -->
        <section class="py-5">
            <div class="container">
                <div class="row">
                    <!-- Sidebar Filters -->
                    <div class="col-lg-3 mb-4 mb-lg-0">
                        <div class="filters-sidebar" data-aos="fade-right">
                            <div class="filter-section mb-4">
                                <h4 class="mb-3">Categorías</h4>
                                <div class="category-filters">
                                    <div class="form-check mb-2">
                                        <input class="form-check-input category-filter" type="checkbox" value="todos" id="category-todos" checked>
                                        <label class="form-check-label" for="category-todos">
                                            Todos los productos
                                        </label>
                                    </div>
                                    <div class="form-check mb-2">
                                        <input class="form-check-input category-filter" type="checkbox" value="blusas" id="category-blusas">
                                        <label class="form-check-label" for="category-blusas">
                                            Blusas
                                        </label>
                                    </div>
                                    <div class="form-check mb-2">
                                        <input class="form-check-input category-filter" type="checkbox" value="camisas" id="category-camisas">
                                        <label class="form-check-label" for="category-camisas">
                                            Camisas
                                        </label>
                                    </div>
                                    <div class="form-check mb-2">
                                        <input class="form-check-input category-filter" type="checkbox" value="tshirt" id="category-tshirt">
                                        <label class="form-check-label" for="category-tshirt">
                                            T-shirt
                                        </label>
                                    </div>
                                    <div class="form-check mb-2">
                                        <input class="form-check-input category-filter" type="checkbox" value="bodys" id="category-bodys">
                                        <label class="form-check-label" for="category-bodys">
                                            Bodys
                                        </label>
                                    </div>
                                    <div class="form-check mb-2">
                                        <input class="form-check-input category-filter" type="checkbox" value="chaquetas" id="category-chaquetas">
                                        <label class="form-check-label" for="category-chaquetas">
                                            Chaquetas
                                        </label>
                                    </div>
                                    <div class="form-check mb-2">
                                        <input class="form-check-input category-filter" type="checkbox" value="cardigans" id="category-cardigans">
                                        <label class="form-check-label" for="category-cardigans">
                                            Cardigans
                                        </label>
                                    </div>
                                    <div class="form-check mb-2">
                                        <input class="form-check-input category-filter" type="checkbox" value="blazer" id="category-blazer">
                                        <label class="form-check-label" for="category-blazer">
                                            Blazer
                                        </label>
                                    </div>
                                    <div class="form-check mb-2">
                                        <input class="form-check-input category-filter" type="checkbox" value="jeans" id="category-jeans">
                                        <label class="form-check-label" for="category-jeans">
                                            Pantalones de jeans
                                        </label>
                                    </div>
                                    <div class="form-check mb-2">
                                        <input class="form-check-input category-filter" type="checkbox" value="pantalones" id="category-pantalones">
                                        <label class="form-check-label" for="category-pantalones">
                                            Pantalones de vestir
                                        </label>
                                    </div>
                                    <div class="form-check mb-2">
                                        <input class="form-check-input category-filter" type="checkbox" value="traje-bano" id="category-traje-bano">
                                        <label class="form-check-label" for="category-traje-bano">
                                            Traje de baño
                                        </label>
                                    </div>
                                    <div class="form-check mb-2">
                                        <input class="form-check-input category-filter" type="checkbox" value="ropa-playera" id="category-ropa-playera">
                                        <label class="form-check-label" for="category-ropa-playera">
                                            Ropa playera
                                        </label>
                                    </div>
                                    <div class="form-check mb-2">
                                        <input class="form-check-input category-filter" type="checkbox" value="sets" id="category-sets">
                                        <label class="form-check-label" for="category-sets">
                                            Sets
                                        </label>
                                    </div>
                                    <div class="form-check mb-2">
                                        <input class="form-check-input category-filter" type="checkbox" value="vestidos" id="category-vestidos">
                                        <label class="form-check-label" for="category-vestidos">
                                            Vestidos
                                        </label>
                                    </div>
                                    <div class="form-check mb-2">
                                        <input class="form-check-input category-filter" type="checkbox" value="bragas" id="category-bragas">
                                        <label class="form-check-label" for="category-bragas">
                                            Bragas
                                        </label>
                                    </div>
                                    <div class="form-check mb-2">
                                        <input class="form-check-input category-filter" type="checkbox" value="deportivo" id="category-deportivo">
                                        <label class="form-check-label" for="category-deportivo">
                                            Deportivo
                                        </label>
                                    </div>
                                </div>
                            </div>

                            <div class="filter-section mb-4">
                                <h4 class="mb-3">Precio</h4>
                                <div class="price-range">
                                    <div class="d-flex justify-content-between mb-2">
                                        <span>$0</span>
                                        <span>$500</span>
                                    </div>
                                    <input type="range" class="form-range" min="0" max="500" step="10" id="price-range">
                                    <div class="mt-2">
                                        <span>Precio máximo: $<span id="price-value">500</span></span>
                                    </div>
                                </div>
                            </div>
                            <button class="btn btn-dark w-100" id="apply-filters">Aplicar Filtros</button>
                            <button class="btn btn-outline-dark w-100 mt-2" id="reset-filters">Restablecer Filtros</button>
                        </div>
                    </div>

                    <!-- Products Grid -->
                    <div class="col-lg-9">
                        <div class="products-header d-flex justify-content-between align-items-center mb-4" data-aos="fade-up">
                            <div>
                                <h4 class="mb-0">Mostrando <span id="product-count">16</span> productos</h4>
                            </div>
                            <div class="d-flex align-items-center">
                                <label for="sort-by" class="me-2">Ordenar por:</label>
                                <select class="form-select" id="sort-by">
                                    <option value="relevance">Relevancia</option>
                                    <option value="price-low">Precio: Menor a Mayor</option>
                                    <option value="price-high">Precio: Mayor a Menor</option>
                                    <option value="newest">Más recientes</option>
                                </select>
                            </div>
                        </div>
                        <div class="row g-4 products-grid">
                        </div>
                        <!-- Pagination -->
                        <div class="d-flex justify-content-center mt-5" data-aos="fade-up">
                            <nav aria-label="Page navigation">
                                <ul class="pagination">
                                    <li class="page-item disabled">
                                        <a class="page-link" href="#" aria-label="Previous">
                                            <span aria-hidden="true">&laquo;</span>
                                        </a>
                                    </li>
                                    <li class="page-item active"><a class="page-link" href="#">1</a></li>
                                    <li class="page-item"><a class="page-link" href="#">2</a></li>
                                    <li class="page-item"><a class="page-link" href="#">3</a></li>
                                    <li class="page-item">
                                        <a class="page-link" href="#" aria-label="Next">
                                            <span aria-hidden="true">&raquo;</span>
                                        </a>
                                    </li>
                                </ul>
                            </nav>
                        </div>
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
    <script src="/BarkiOS/public/assets/js/products.js"></script>
</body>
</html>

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
                                    <div class="filter-widget mb-4" data-aos="fade-up">
                                        <h5 class="fw-bold mb-3">Categorías</h5>

                                        <div class="form-check">
                                            <input class="form-check-input category-filter" type="checkbox" id="category-todos" value="todos" checked>
                                            <label class="form-check-label" for="category-todos">
                                                Todas las categorías
                                            </label>
                                        </div>

                                        <div class="form-check">
                                            <input class="form-check-input category-filter" type="checkbox" id="category-formal" value="Formal">
                                            <label class="form-check-label" for="category-formal">Formal</label>
                                        </div>

                                        <div class="form-check">
                                            <input class="form-check-input category-filter" type="checkbox" id="category-casual" value="Casual">
                                            <label class="form-check-label" for="category-casual">Casual</label>
                                        </div>

                                        <div class="form-check">
                                            <input class="form-check-input category-filter" type="checkbox" id="category-deportivo" value="Deportivo">
                                            <label class="form-check-label" for="category-deportivo">Deportivo</label>
                                        </div>

                                        <div class="form-check">
                                            <input class="form-check-input category-filter" type="checkbox" id="category-invierno" value="Invierno">
                                            <label class="form-check-label" for="category-invierno">Invierno</label>
                                        </div>

                                        <div class="form-check">
                                            <input class="form-check-input category-filter" type="checkbox" id="category-verano" value="Verano">
                                            <label class="form-check-label" for="category-verano">Verano</label>
                                        </div>

                                        <div class="form-check">
                                            <input class="form-check-input category-filter" type="checkbox" id="category-fiesta" value="Fiesta">
                                            <label class="form-check-label" for="category-fiesta">Fiesta</label>
                                        </div>
                                    </div>


                            <div class="filter-section mb-4">
                                <h4 class="mb-3">Precio</h4>
                                <div class="price-range">
                                    <div class="d-flex justify-content-between mb-2">
                                        <span>$0</span>
                                        <span>$200</span>
                                    </div>
                                    <input type="range" class="form-range" min="0" max="200" step="2" id="price-range">
                                    <div class="mt-2">
                                        <span>Precio máximo: $<span id="price-value">200</span></span>
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
    <script src="/BarkiOS/public/assets/js/front/products.js"></script>
</body>
</html>

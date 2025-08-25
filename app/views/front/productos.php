<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Productos - Garage Barki</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Bodoni+Moda:opsz,wght@6..96,400;6..96,500;6..96,600;6..96,700&display=swap" rel="stylesheet">
    <!-- Custom Fonts -->
    <link href="assets/fonts/fonts.css" rel="stylesheet">
    <!-- Custom CSS -->
    <link rel="stylesheet" href="../../../public/assets/css/styles.css">
    <!-- AOS Animation Library -->
    <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">
</head>
<body>
    <!-- Navbar (View Component) -->
    <nav class="navbar navbar-expand-lg navbar-light bg-white fixed-top">
        <div class="container">
            <a class="navbar-brand" href="index.html">
                <h1 class="m-0">GARAGE<span>BARKI</span></h1>
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="nav-link active" href="../index.html">Inicio</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="views/novedades.html">Novedades</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="views/productos.html">Productos</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="sobre-nosotros.html">Nosotros</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="views/ubicanos.html">Ubícanos</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="views/contacto.html">Contacto</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="views/ayuda.html">Ayuda</a>
                    </li>
                </ul>
  
            </div>
        </div>
    </nav>

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
                                        <input class="form-check-input category-filter" type="checkbox" value="faldas" id="category-faldas">
                                        <label class="form-check-label" for="category-faldas">
                                            Faldas
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

                            <div class="filter-section mb-4">
                                <h4 class="mb-3">Talla</h4>
                                <div class="size-filters d-flex flex-wrap">
                                    <div class="form-check me-3 mb-2">
                                        <input class="form-check-input size-filter" type="checkbox" value="XS" id="size-xs">
                                        <label class="form-check-label" for="size-xs">XS</label>
                                    </div>
                                    <div class="form-check me-3 mb-2">
                                        <input class="form-check-input size-filter" type="checkbox" value="S" id="size-s">
                                        <label class="form-check-label" for="size-s">S</label>
                                    </div>
                                    <div class="form-check me-3 mb-2">
                                        <input class="form-check-input size-filter" type="checkbox" value="M" id="size-m">
                                        <label class="form-check-label" for="size-m">M</label>
                                    </div>
                                    <div class="form-check me-3 mb-2">
                                        <input class="form-check-input size-filter" type="checkbox" value="L" id="size-l">
                                        <label class="form-check-label" for="size-l">L</label>
                                    </div>
                                    <div class="form-check mb-2">
                                        <input class="form-check-input size-filter" type="checkbox" value="XL" id="size-xl">
                                        <label class="form-check-label" for="size-xl">XL</label>
                                    </div>
                                </div>
                            </div>

                            <div class="filter-section mb-4">
                                <h4 class="mb-3">Color</h4>
                                <div class="color-filters d-flex flex-wrap">
                                    <div class="color-option me-2 mb-2" style="background-color: #000000;" data-color="Negro"></div>
                                    <div class="color-option me-2 mb-2" style="background-color: #FFFFFF; border: 1px solid #ddd;" data-color="Blanco"></div>
                                    <div class="color-option me-2 mb-2" style="background-color: #FF0000;" data-color="Rojo"></div>
                                    <div class="color-option me-2 mb-2" style="background-color: #0000FF;" data-color="Azul"></div>
                                    <div class="color-option me-2 mb-2" style="background-color: #FFFF00;" data-color="Amarillo"></div>
                                    <div class="color-option me-2 mb-2" style="background-color: #008000;" data-color="Verde"></div>
                                    <div class="color-option me-2 mb-2" style="background-color: #FFC0CB;" data-color="Rosa"></div>
                                    <div class="color-option me-2 mb-2" style="background-color: #800080;" data-color="Púrpura"></div>
                                </div>
                                <div class="selected-colors mt-2">
                                    <span>Colores seleccionados: <span id="selected-colors">Ninguno</span></span>
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
                            <!-- Product 1 -->
                            <div class="col-md-4 col-sm-6" data-aos="fade-up" data-category="vestidos" data-price="189.99" data-color="Negro">
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
                            <div class="col-md-4 col-sm-6" data-aos="fade-up" data-aos-delay="100" data-category="blusas" data-price="89.99" data-color="Blanco">
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
                            <div class="col-md-4 col-sm-6" data-aos="fade-up" data-aos-delay="200" data-category="pantalones" data-price="129.99" data-color="Negro">
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
                            <div class="col-md-4 col-sm-6" data-aos="fade-up" data-aos-delay="300" data-category="blazer" data-price="159.99" data-color="Negro">
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
                            <!-- Product 5 -->
                            <div class="col-md-4 col-sm-6" data-aos="fade-up" data-category="vestidos" data-price="199.99" data-color="Rojo">
                                <div class="product-card">
                                    <div class="product-image">
                                        <span class="product-badge">Exclusivo</span>
                                        <img src="https://images.unsplash.com/photo-1572804013309-59a88b7e92f1?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=746&q=80" alt="Vestido de Noche">
                                        <div class="product-actions">
                                            <button class="action-btn add-to-wishlist" data-product-id="5"><i class="far fa-heart"></i></button>
                                            <button class="action-btn quick-view" data-product-id="5"><i class="far fa-eye"></i></button>
                                        </div>
                                    </div>
                                    <div class="product-info">
                                        <h4>Vestido de Noche</h4>
                                        <p class="product-category">Vestidos</p>
                                        <div class="product-price">$199.99</div>
                                        <button class="btn btn-dark w-100 add-to-cart" data-product-id="5">Agregar al Carrito</button>
                                    </div>
                                </div>
                            </div>
                            <!-- Product 6 -->
                            <div class="col-md-4 col-sm-6" data-aos="fade-up" data-aos-delay="100" data-category="faldas" data-price="79.99" data-color="Negro">
                                <div class="product-card">
                                    <div class="product-image">
                                        <span class="product-badge">Exclusivo</span>
                                        <img src="https://images.unsplash.com/photo-1583496661160-fb5886a0aaaa?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=764&q=80" alt="Falda Midi">
                                        <div class="product-actions">
                                            <button class="action-btn add-to-wishlist" data-product-id="6"><i class="far fa-heart"></i></button>
                                            <button class="action-btn quick-view" data-product-id="6"><i class="far fa-eye"></i></button>
                                        </div>
                                    </div>
                                    <div class="product-info">
                                        <h4>Falda Midi</h4>
                                        <p class="product-category">Faldas</p>
                                        <div class="product-price">$79.99</div>
                                        <button class="btn btn-dark w-100 add-to-cart" data-product-id="6">Agregar al Carrito</button>
                                    </div>
                                </div>
                            </div>
                            <!-- Product 7 -->
                            <div class="col-md-4 col-sm-6" data-aos="fade-up" data-aos-delay="200" data-category="jeans" data-price="119.99" data-color="Azul">
                                <div class="product-card">
                                    <div class="product-image">
                                        <span class="product-badge">Exclusivo</span>
                                        <img src="https://images.unsplash.com/photo-1541099649105-f69ad21f3246?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=687&q=80" alt="Jeans Premium">
                                        <div class="product-actions">
                                            <button class="action-btn add-to-wishlist" data-product-id="7"><i class="far fa-heart"></i></button>
                                            <button class="action-btn quick-view" data-product-id="7"><i class="far fa-eye"></i></button>
                                        </div>
                                    </div>
                                    <div class="product-info">
                                        <h4>Jeans Premium</h4>
                                        <p class="product-category">Pantalones de jeans</p>
                                        <div class="product-price">$119.99</div>
                                        <button class="btn btn-dark w-100 add-to-cart" data-product-id="7">Agregar al Carrito</button>
                                    </div>
                                </div>
                            </div>
                            <!-- Product 8 -->
                            <div class="col-md-4 col-sm-6" data-aos="fade-up" data-aos-delay="300" data-category="chaquetas" data-price="179.99" data-color="Negro">
                                <div class="product-card">
                                    <div class="product-image">
                                        <span class="product-badge">Exclusivo</span>
                                        <img src="https://images.unsplash.com/photo-1551488831-00ddcb6c6bd3?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=1470&q=80" alt="Chaqueta de Cuero">
                                        <div class="product-actions">
                                            <button class="action-btn add-to-wishlist" data-product-id="8"><i class="far fa-heart"></i></button>
                                            <button class="action-btn quick-view" data-product-id="8"><i class="far fa-eye"></i></button>
                                        </div>
                                    </div>
                                    <div class="product-info">
                                        <h4>Chaqueta de Cuero</h4>
                                        <p class="product-category">Chaquetas</p>
                                        <div class="product-price">$179.99</div>
                                        <button class="btn btn-dark w-100 add-to-cart" data-product-id="8">Agregar al Carrito</button>
                                    </div>
                                </div>
                            </div>
                            <!-- Product 9 -->
                            <div class="col-md-4 col-sm-6" data-aos="fade-up" data-category="tshirt" data-price="49.99" data-color="Blanco">
                                <div class="product-card">
                                    <div class="product-image">
                                        <span class="product-badge">Exclusivo</span>
                                        <img src="https://images.unsplash.com/photo-1503342217505-b0a15ec3261c?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=1470&q=80" alt="T-shirt Premium">
                                        <div class="product-actions">
                                            <button class="action-btn add-to-wishlist" data-product-id="9"><i class="far fa-heart"></i></button>
                                            <button class="action-btn quick-view" data-product-id="9"><i class="far fa-eye"></i></button>
                                        </div>
                                    </div>
                                    <div class="product-info">
                                        <h4>T-shirt Premium</h4>
                                        <p class="product-category">T-shirt</p>
                                        <div class="product-price">$49.99</div>
                                        <button class="btn btn-dark w-100 add-to-cart" data-product-id="9">Agregar al Carrito</button>
                                    </div>
                                </div>
                            </div>
                            <!-- Product 10 -->
                            <div class="col-md-4 col-sm-6" data-aos="fade-up" data-aos-delay="100" data-category="bodys" data-price="69.99" data-color="Negro">
                                <div class="product-card">
                                    <div class="product-image">
                                        <span class="product-badge">Exclusivo</span>
                                        <img src="https://images.unsplash.com/photo-1566206091558-7f218b696731?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=764&q=80" alt="Body Elegante">
                                        <div class="product-actions">
                                            <button class="action-btn add-to-wishlist" data-product-id="10"><i class="far fa-heart"></i></button>
                                            <button class="action-btn quick-view" data-product-id="10"><i class="far fa-eye"></i></button>
                                        </div>
                                    </div>
                                    <div class="product-info">
                                        <h4>Body Elegante</h4>
                                        <p class="product-category">Bodys</p>
                                        <div class="product-price">$69.99</div>
                                        <button class="btn btn-dark w-100 add-to-cart" data-product-id="10">Agregar al Carrito</button>
                                    </div>
                                </div>
                            </div>
                            <!-- Product 11 -->
                            <div class="col-md-4 col-sm-6" data-aos="fade-up" data-aos-delay="200" data-category="cardigans" data-price="99.99" data-color="Beige">
                                <div class="product-card">
                                    <div class="product-image">
                                        <span class="product-badge">Exclusivo</span>
                                        <img src="https://images.unsplash.com/photo-1434389677669-e08b4cac3105?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=705&q=80" alt="Cardigan Suave">
                                        <div class="product-actions">
                                            <button class="action-btn add-to-wishlist" data-product-id="11"><i class="far fa-heart"></i></button>
                                            <button class="action-btn quick-view" data-product-id="11"><i class="far fa-eye"></i></button>
                                        </div>
                                    </div>
                                    <div class="product-info">
                                        <h4>Cardigan Suave</h4>
                                        <p class="product-category">Cardigans</p>
                                        <div class="product-price">$99.99</div>
                                        <button class="btn btn-dark w-100 add-to-cart" data-product-id="11">Agregar al Carrito</button>
                                    </div>
                                </div>
                            </div>
                            <!-- Product 12 -->
                            <div class="col-md-4 col-sm-6" data-aos="fade-up" data-aos-delay="300" data-category="sets" data-price="249.99" data-color="Negro">
                                <div class="product-card">
                                    <div class="product-image">
                                        <span class="product-badge">Exclusivo</span>
                                        <img src="https://images.unsplash.com/photo-1485968579580-b6d095142e6e?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=686&q=80" alt="Set Ejecutivo">
                                        <div class="product-actions">
                                            <button class="action-btn add-to-wishlist" data-product-id="12"><i class="far fa-heart"></i></button>
                                            <button class="action-btn quick-view" data-product-id="12"><i class="far fa-eye"></i></button>
                                        </div>
                                    </div>
                                    <div class="product-info">
                                        <h4>Set Ejecutivo</h4>
                                        <p class="product-category">Sets</p>
                                        <div class="product-price">$249.99</div>
                                        <button class="btn btn-dark w-100 add-to-cart" data-product-id="12">Agregar al Carrito</button>
                                    </div>
                                </div>
                            </div>
                            <!-- Product 13 -->
                            <div class="col-md-4 col-sm-6" data-aos="fade-up" data-category="traje-bano" data-price="79.99" data-color="Azul">
                                <div class="product-card">
                                    <div class="product-image">
                                        <span class="product-badge">Exclusivo</span>
                                        <img src="https://images.unsplash.com/photo-1570976447640-ac859a223c39?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=687&q=80" alt="Traje de Baño Elegante">
                                        <div class="product-actions">
                                            <button class="action-btn add-to-wishlist" data-product-id="13"><i class="far fa-heart"></i></button>
                                            <button class="action-btn quick-view" data-product-id="13"><i class="far fa-eye"></i></button>
                                        </div>
                                    </div>
                                    <div class="product-info">
                                        <h4>Traje de Baño Elegante</h4>
                                        <p class="product-category">Traje de baño</p>
                                        <div class="product-price">$79.99</div>
                                        <button class="btn btn-dark w-100 add-to-cart" data-product-id="13">Agregar al Carrito</button>
                                    </div>
                                </div>
                            </div>
                            <!-- Product 14 -->
                            <div class="col-md-4 col-sm-6" data-aos="fade-up" data-aos-delay="100" data-category="ropa-playera" data-price="59.99" data-color="Blanco">
                                <div class="product-card">
                                    <div class="product-image">
                                        <span class="product-badge">Exclusivo</span>
                                        <img src="https://images.unsplash.com/photo-1523381294911-8d3cead13475?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=1470&q=80" alt="Túnica Playera">
                                        <div class="product-actions">
                                            <button class="action-btn add-to-wishlist" data-product-id="14"><i class="far fa-heart"></i></button>
                                            <button class="action-btn quick-view" data-product-id="14"><i class="far fa-eye"></i></button>
                                        </div>
                                    </div>
                                    <div class="product-info">
                                        <h4>Túnica Playera</h4>
                                        <p class="product-category">Ropa playera</p>
                                        <div class="product-price">$59.99</div>
                                        <button class="btn btn-dark w-100 add-to-cart" data-product-id="14">Agregar al Carrito</button>
                                    </div>
                                </div>
                            </div>
                            <!-- Product 15 -->
                            <div class="col-md-4 col-sm-6" data-aos="fade-up" data-aos-delay="200" data-category="bragas" data-price="39.99" data-color="Negro">
                                <div class="product-card">
                                    <div class="product-image">
                                        <span class="product-badge">Exclusivo</span>
                                        <img src="https://images.unsplash.com/photo-1616544956148-a7aed0c81483?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=687&q=80" alt="Braga Elegante">
                                        <div class="product-actions">
                                            <button class="action-btn add-to-wishlist" data-product-id="15"><i class="far fa-heart"></i></button>
                                            <button class="action-btn quick-view" data-product-id="15"><i class="far fa-eye"></i></button>
                                        </div>
                                    </div>
                                    <div class="product-info">
                                        <h4>Braga Elegante</h4>
                                        <p class="product-category">Bragas</p>
                                        <div class="product-price">$39.99</div>
                                        <button class="btn btn-dark w-100 add-to-cart" data-product-id="15">Agregar al Carrito</button>
                                    </div>
                                </div>
                            </div>
                            <!-- Product 16 -->
                            <div class="col-md-4 col-sm-6" data-aos="fade-up" data-aos-delay="300" data-category="camisas" data-price="89.99" data-color="Blanco">
                                <div class="product-card">
                                    <div class="product-image">
                                        <span class="product-badge">Exclusivo</span>
                                        <img src="https://images.unsplash.com/photo-1604695573706-53170668f6a6?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=687&q=80" alt="Camisa Formal">
                                        <div class="product-actions">
                                            <button class="action-btn add-to-wishlist" data-product-id="16"><i class="far fa-heart"></i></button>
                                            <button class="action-btn quick-view" data-product-id="16"><i class="far fa-eye"></i></button>
                                        </div>
                                    </div>
                                    <div class="product-info">
                                        <h4>Camisa Formal</h4>
                                        <p class="product-category">Camisas</p>
                                        <div class="product-price">$89.99</div>
                                        <button class="btn btn-dark w-100 add-to-cart" data-product-id="16">Agregar al Carrito</button>
                                    </div>
                                </div>
                            </div>
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
    </main>

    <!-- Footer (View Component) -->
    <footer class="py-5 bg-black text-white">
        <div class="container">
            <div class="row g-4">
                <div class="col-lg-4">
                    <h3 class="mb-4">GARAGE BARKI</h3>
                    <p>Ofrecemos a mujeres de todas las edades y tallas una experiencia de compra única, con prendas exclusivas que resaltan su individualidad y confianza.</p>
                    <div class="social-icons mt-3">
                        <a href="#" class="me-2"><i class="fab fa-facebook-f"></i></a>
                        <a href="#" class="me-2"><i class="fab fa-instagram"></i></a>
                        <a href="#" class="me-2"><i class="fab fa-twitter"></i></a>
                        <a href="#"><i class="fab fa-pinterest"></i></a>
                    </div>
                </div>
                <div class="col-lg-2">
                    <h5 class="mb-4">Navegación</h5>
                    <ul class="list-unstyled">
                        <li class="mb-2"><a href="../index.html">Inicio</a></li>
                        <li class="mb-2"><a href="novedades.html">Novedades</a></li>
                        <li class="mb-2"><a href="productos.html">Productos</a></li>
                        <li class="mb-2"><a href="sobre-nosotros.html">Sobre Nosotros</a></li>
                    </ul>
                </div>
                <div class="col-lg-2">
                    <h5 class="mb-4">Categorías</h5>
                    <ul class="list-unstyled">
                        <li class="mb-2"><a href="productos.html?categoria=vestidos">Vestidos</a></li>
                        <li class="mb-2"><a href="productos.html?categoria=blusas">Blusas</a></li>
                        <li class="mb-2"><a href="productos.html?categoria=pantalones">Pantalones</a></li>
                        <li class="mb-2"><a href="productos.html?categoria=sets">Sets</a></li>
                    </ul>
                </div>
                <div class="col-lg-4">
                    <h5 class="mb-4">Contacto</h5>
                    <ul class="list-unstyled contact-info">
                        <li class="mb-2"><i class="fas fa-map-marker-alt me-2"></i> Av. Principal 123, Ciudad</li>
                        <li class="mb-2"><i class="fas fa-phone me-2"></i> +123 456 7890</li>
                        <li class="mb-2"><i class="fas fa-envelope me-2"></i> info@garagebarki.com</li>
                        <li class="mb-2"><i class="fas fa-clock me-2"></i> Lun - Sáb: 10:00 - 20:00</li>
                    </ul>
                </div>
            </div>
            <hr class="my-4">
            <div class="row">
                <div class="col-md-6">
                    <p class="mb-md-0">© 2023 Garage Barki. Todos los derechos reservados.</p>
                </div>
                <div class="col-md-6 text-md-end">
                    <a href="#" class="text-white me-3">Términos y Condiciones</a>
                    <a href="#" class="text-white">Política de Privacidad</a>
                </div>
            </div>
        </div>
    </footer>

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
    <script src="../../controllers/Front/products.js"></script>
</body>
</html>

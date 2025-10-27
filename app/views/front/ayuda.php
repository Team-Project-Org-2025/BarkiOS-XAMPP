<?php $pageTitle = "Ayuda | Garage Barki"; ?>
<?php require_once __DIR__ . '/../partials/header.php';?>
<?php require_once __DIR__ . '/../partials/navbar.php';?>

    <!-- Main Content -->
    <main>
        <!-- Hero Banner elegante -->
        <section class="hero-banner" style="background-image: url('https://images.unsplash.com/photo-1556742049-0cfed4f6a45d?ixlib=rb-4.0.3&auto=format&fit=crop&w=1470&q=80');">
            <div class="hero-banner-content">
                <h1 data-aos="fade-up">CENTRO DE AYUDA</h1>
                <p data-aos="fade-up" data-aos-delay="200">Encuentra respuestas a tus preguntas</p>
            </div>
        </section>

        <!-- Búsqueda mejorada -->
        <section class="py-5">
            <div class="container">
                <div class="row justify-content-center">
                    <div class="col-lg-8 text-center" data-aos="fade-up">
                        <h2 class="mb-4">¿EN QUÉ PODEMOS AYUDARTE?</h2>
                        <div class="search-box">
                            <div class="input-group">
                                <input type="text" class="form-control" placeholder="Busca tu pregunta aquí..." id="helpSearch">
                                <button class="btn btn-dark" type="button">
                                    <i class="fas fa-search"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Categorías de ayuda mejoradas -->
        <section class="py-5 bg-light">
            <div class="container">
                <h2 class="text-center mb-5" data-aos="fade-up">CATEGORÍAS DE AYUDA</h2>
                <div class="row g-4">
                    <div class="col-md-4" data-aos="fade-up">
                        <div class="help-category-card">
                            <div class="help-category-icon">
                                <i class="fas fa-shopping-cart"></i>
                            </div>
                            <h3>Pedidos y Compras</h3>
                            <p>Información sobre cómo realizar pedidos, métodos de pago y seguimiento.</p>
                        </div>
                    </div>
                    <div class="col-md-4" data-aos="fade-up" data-aos-delay="100">
                        <div class="help-category-card">
                            <div class="help-category-icon">
                                <i class="fas fa-truck"></i>
                            </div>
                            <h3>Envíos y Entregas</h3>
                            <p>Detalles sobre tiempos de envío, costos y opciones de entrega.</p>
                        </div>
                    </div>
                    <div class="col-md-4" data-aos="fade-up" data-aos-delay="200">
                        <div class="help-category-card">
                            <div class="help-category-icon">
                                <i class="fas fa-exchange-alt"></i>
                            </div>
                            <h3>Cambios y Devoluciones</h3>
                            <p>Políticas de cambio, devolución y reembolso de productos.</p>
                        </div>
                    </div>
                    <div class="col-md-4" data-aos="fade-up">
                        <div class="help-category-card">
                            <div class="help-category-icon">
                                <i class="fas fa-tshirt"></i>
                            </div>
                            <h3>Productos</h3>
                            <p>Información sobre tallas, materiales y cuidado de las prendas.</p>
                        </div>
                    </div>
                    <div class="col-md-4" data-aos="fade-up" data-aos-delay="100">
                        <div class="help-category-card">
                            <div class="help-category-icon">
                                <i class="fas fa-user"></i>
                            </div>
                            <h3>Mi Cuenta</h3>
                            <p>Gestión de cuenta, contraseñas y datos personales.</p>
                        </div>
                    </div>
                    <div class="col-md-4" data-aos="fade-up" data-aos-delay="200">
                        <div class="help-category-card">
                            <div class="help-category-icon">
                                <i class="fas fa-credit-card"></i>
                            </div>
                            <h3>Pagos</h3>
                            <p>Métodos de pago aceptados, seguridad y facturación.</p>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- FAQ mejorado con acordeones elegantes -->
        <section class="py-5">
            <div class="container">
                <h2 class="text-center mb-5" data-aos="fade-up">PREGUNTAS FRECUENTES</h2>
                
                <!-- Pedidos -->
                <div class="mb-5" id="pedidos" data-aos="fade-up">
                    <h3 class="mb-4"><i class="fas fa-shopping-cart me-2"></i> Pedidos y Compras</h3>
                    <div class="accordion faq-accordion" id="accordionPedidos">
                        <div class="accordion-item">
                            <h2 class="accordion-header">
                                <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#pedido1">
                                    ¿Cómo puedo realizar un pedido?
                                </button>
                            </h2>
                            <div id="pedido1" class="accordion-collapse collapse show" data-bs-parent="#accordionPedidos">
                                <div class="accordion-body">
                                    Para realizar un pedido, navega por nuestro catálogo, selecciona los productos que desees, elige tu talla y agrégalos al carrito. Luego, procede al checkout donde podrás ingresar tus datos de envío y pago. Recuerda que cada prenda es única, así que si te gusta algo, ¡no lo dejes pasar!
                                </div>
                            </div>
                        </div>
                        <div class="accordion-item">
                            <h2 class="accordion-header">
                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#pedido2">
                                    ¿Puedo modificar o cancelar mi pedido?
                                </button>
                            </h2>
                            <div id="pedido2" class="accordion-collapse collapse" data-bs-parent="#accordionPedidos">
                                <div class="accordion-body">
                                    Puedes modificar o cancelar tu pedido dentro de las primeras 2 horas después de haberlo realizado. Contáctanos inmediatamente por WhatsApp o teléfono. Una vez que el pedido ha sido procesado y enviado, no podrá ser modificado.
                                </div>
                            </div>
                        </div>
                        <div class="accordion-item">
                            <h2 class="accordion-header">
                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#pedido3">
                                    ¿Cómo puedo rastrear mi pedido?
                                </button>
                            </h2>
                            <div id="pedido3" class="accordion-collapse collapse" data-bs-parent="#accordionPedidos">
                                <div class="accordion-body">
                                    Una vez que tu pedido sea enviado, recibirás un correo electrónico con el número de seguimiento. Podrás rastrear tu paquete en tiempo real a través del enlace proporcionado o contactándonos directamente.
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Envíos -->
                <div class="mb-5" id="envios" data-aos="fade-up">
                    <h3 class="mb-4"><i class="fas fa-truck me-2"></i> Envíos y Entregas</h3>
                    <div class="accordion faq-accordion" id="accordionEnvios">
                        <div class="accordion-item">
                            <h2 class="accordion-header">
                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#envio1">
                                    ¿Cuánto tiempo tarda el envío?
                                </button>
                            </h2>
                            <div id="envio1" class="accordion-collapse collapse" data-bs-parent="#accordionEnvios">
                                <div class="accordion-body">
                                    Los envíos dentro de Lima metropolitana tardan de 24 a 48 horas. Para provincias, el tiempo de entrega es de 3 a 7 días hábiles dependiendo de la ubicación. Los pedidos se procesan de lunes a viernes.
                                </div>
                            </div>
                        </div>
                        <div class="accordion-item">
                            <h2 class="accordion-header">
                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#envio2">
                                    ¿Cuál es el costo de envío?
                                </button>
                            </h2>
                            <div id="envio2" class="accordion-collapse collapse" data-bs-parent="#accordionEnvios">
                                <div class="accordion-body">
                                    El envío dentro de Lima tiene un costo de S/. 10. Para provincias, el costo varía entre S/. 15 y S/. 25 según la ubicación. ¡Envío gratis en compras mayores a S/. 200!
                                </div>
                            </div>
                        </div>
                        <div class="accordion-item">
                            <h2 class="accordion-header">
                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#envio3">
                                    ¿Hacen envíos internacionales?
                                </button>
                            </h2>
                            <div id="envio3" class="accordion-collapse collapse" data-bs-parent="#accordionEnvios">
                                <div class="accordion-body">
                                    Actualmente solo realizamos envíos dentro del Perú. Estamos trabajando para expandir nuestros servicios internacionalmente pronto.
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Cambios y Devoluciones -->
                <div class="mb-5" id="cambios" data-aos="fade-up">
                    <h3 class="mb-4"><i class="fas fa-exchange-alt me-2"></i> Cambios y Devoluciones</h3>
                    <div class="accordion faq-accordion" id="accordionCambios">
                        <div class="accordion-item">
                            <h2 class="accordion-header">
                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#cambio1">
                                    ¿Cuál es la política de cambios?
                                </button>
                            </h2>
                            <div id="cambio1" class="accordion-collapse collapse" data-bs-parent="#accordionCambios">
                                <div class="accordion-body">
                                    Aceptamos cambios dentro de los 7 días posteriores a la recepción del producto. La prenda debe estar sin usar, con etiquetas originales y en perfecto estado. Los cambios solo aplican por talla diferente o defecto de fábrica.
                                </div>
                            </div>
                        </div>
                        <div class="accordion-item">
                            <h2 class="accordion-header">
                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#cambio2">
                                    ¿Cómo solicito un cambio o devolución?
                                </button>
                            </h2>
                            <div id="cambio2" class="accordion-collapse collapse" data-bs-parent="#accordionCambios">
                                <div class="accordion-body">
                                    Contáctanos por WhatsApp, email o teléfono con tu número de pedido y el motivo del cambio. Te proporcionaremos las instrucciones para el envío de retorno. Una vez recibido y verificado el producto, procesaremos tu cambio o reembolso.
                                </div>
                            </div>
                        </div>
                        <div class="accordion-item">
                            <h2 class="accordion-header">
                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#cambio3">
                                    ¿Quién paga el envío de devolución?
                                </button>
                            </h2>
                            <div id="cambio3" class="accordion-collapse collapse" data-bs-parent="#accordionCambios">
                                <div class="accordion-body">
                                    Si el cambio es por defecto de fábrica o error en el envío, nosotros cubrimos el costo del envío de retorno. Si es por cambio de talla o preferencia personal, el cliente asume el costo del envío.
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Productos -->
                <div class="mb-5" id="productos" data-aos="fade-up">
                    <h3 class="mb-4"><i class="fas fa-tshirt me-2"></i> Productos</h3>
                    <div class="accordion faq-accordion" id="accordionProductos">
                        <div class="accordion-item">
                            <h2 class="accordion-header">
                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#producto1">
                                    ¿Cómo sé qué talla elegir?
                                </button>
                            </h2>
                            <div id="producto1" class="accordion-collapse collapse" data-bs-parent="#accordionProductos">
                                <div class="accordion-body">
                                    Cada producto tiene una guía de tallas específica. Te recomendamos medir una prenda similar que te quede bien y comparar con nuestras medidas. Si tienes dudas, contáctanos y con gusto te asesoramos personalmente.
                                </div>
                            </div>
                        </div>
                        <div class="accordion-item">
                            <h2 class="accordion-header">
                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#producto2">
                                    ¿Qué significa que cada prenda es exclusiva?
                                </button>
                            </h2>
                            <div id="producto2" class="accordion-collapse collapse" data-bs-parent="#accordionProductos">
                                <div class="accordion-body">
                                    En Garage Barki, nunca vendemos dos unidades iguales de la misma prenda. Cada pieza es única, lo que garantiza que tu estilo sea verdaderamente exclusivo. Una vez vendida una prenda, no se repone.
                                </div>
                            </div>
                        </div>
                        <div class="accordion-item">
                            <h2 class="accordion-header">
                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#producto3">
                                    ¿Cómo debo cuidar mis prendas?
                                </button>
                            </h2>
                            <div id="producto3" class="accordion-collapse collapse" data-bs-parent="#accordionProductos">
                                <div class="accordion-body">
                                    Cada prenda incluye una etiqueta con instrucciones específicas de cuidado. En general, recomendamos lavar a mano o en ciclo delicado, usar agua fría y secar a la sombra. Evita el uso de blanqueadores y plancha a temperatura baja.
                                </div>
                            </div>
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
    <!-- Help Page Script -->
    <script src="/BarkiOS/public/assets/js/help.js"></script>
</body>
</html>

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
                                    En Garage Barki, tu comodidad es nuestra prioridad. Queremos que ames lo que compras, por eso te invitamos a nuestro espacio para que te pruebes tus prendas favoritas y te asegures de hacer la elección perfecta.
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
                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#envio3">
                                    ¿Hacen envíos a nivel nacional?
                                </button>
                            </h2>
                            <div id="envio3" class="accordion-collapse collapse" data-bs-parent="#accordionEnvios">
                                <div class="accordion-body">
                                    Actualmente solo realizamos envíos dentro del Barquisimeto. Estamos trabajando para expandir nuestros servicios a todo el país.
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
    <script src="/BarkiOS/public/assets/js/front/help.js"></script>
</body>
</html>

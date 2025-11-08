<?php $pageTitle = "Contacto | Garage Barki"; ?>
<?php require_once __DIR__ . '/../partials/header.php';?>
<?php require_once __DIR__ . '/../partials/navbar.php';?>

    <!-- Main Content -->
    <main>
        <!-- Hero Banner elegante -->
        <section class="hero-banner" style="background-image: url('https://images.unsplash.com/photo-1423666639041-f56000c27a9a?ixlib=rb-4.0.3&auto=format&fit=crop&w=1474&q=80');">
            <div class="hero-banner-content">
                <h1 data-aos="fade-up">CONTACTO</h1>
                <p data-aos="fade-up" data-aos-delay="200">Estamos aquí para ayudarte</p>
            </div>
        </section>

        <!-- Sección de contacto mejorada -->
        <section class="py-5">
            <div class="container">
                <div class="row g-5">
                    <!-- Contact Form -->
                    <div class="col-lg-7" data-aos="fade-right">
                        <div class="contact-form">
                            <h2 class="mb-4">ENVÍANOS UN MENSAJE</h2>
                            <p class="lead mb-4">¿Tienes alguna pregunta o comentario? Completa el formulario y te responderemos lo antes posible.</p>
                            
                            <form id="contactForm" action="https://formspree.io/f/myzbdlpy" method="POST">
                                <div class="row g-3">
                                    <div class="col-md-6">
                                    <label for="firstName" class="form-label">Nombre *</label>
                                    <input type="text" class="form-control" id="firstName" name="Nombre" required>
                                    </div>
                                    <div class="col-md-6">
                                    <label for="lastName" class="form-label">Apellido *</label>
                                    <input type="text" class="form-control" id="lastName" name="Apellido" required>
                                    </div>
                                    <div class="col-md-6">
                                    <label for="email" class="form-label">Email *</label>
                                    <input type="email" class="form-control" id="email" name="Email" required>
                                    </div>
                                    <div class="col-md-6">
                                    <label for="phone" class="form-label">Teléfono</label>
                                    <input type="tel" class="form-control" id="phone" name="Teléfono">
                                    </div>
                                    <div class="col-12">
                                    <label for="subject" class="form-label">Asunto *</label>
                                    <select class="form-select" id="subject" name="Asunto" required>
                                        <option value="">Selecciona un asunto</option>
                                        <option value="consulta-producto">Consulta sobre Producto</option>
                                        <option value="pedido">Estado de Pedido</option>
                                        <option value="cambio-devolucion">Cambio o Devolución</option>
                                        <option value="sugerencia">Sugerencia</option>
                                        <option value="otro">Otro</option>
                                    </select>
                                    </div>
                                    <div class="col-12">
                                    <label for="message" class="form-label">Mensaje *</label>
                                    <textarea class="form-control" id="message" name="Mensaje" rows="6" required></textarea>
                                    </div>
                                    <div class="col-12">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" id="privacyPolicy" required>
                                        <label class="form-check-label" for="privacyPolicy">
                                        Acepto la <a href="#">política de privacidad</a> *
                                        </label>
                                    </div>
                                    </div>
                                    <div class="col-12">
                                    <button type="submit" class="btn btn-dark btn-lg">Enviar Mensaje</button>
                                    </div>
                                </div>
                                </form>

                        </div>
                    </div>

                    <!-- Información de contacto mejorada -->
                    <div class="col-lg-5" data-aos="fade-left">
                        <h2 class="mb-4">INFORMACIÓN DE CONTACTO</h2>
                        
                        <div class="location-info-card">
                            <h3>Nuestra Ubicación</h3>
                            <div class="location-detail">
                                <i class="fas fa-map-marker-alt"></i>
                                <div class="location-detail-content">
                                    <h5>Dirección</h5>
                                    <p>Tienda física<br>Calle 25 con Carrera 23, diagonal<br>al C.C. Cosmos<br>Barquisimeto, Venezuela</p>
                                </div>
                            </div>
                        </div>

                        <div class="location-info-card">
                            <h3>Contáctanos</h3>
                            <div class="location-detail">
                                <i class="fas fa-phone"></i>
                                <div class="location-detail-content">
                                    <h5>Teléfono</h5>
                                    <p>
                                        <a href="tel:+51987654321">+58 424 5287 855</a><br>
                                    </p>
                                </div>
                            </div>

                            <div class="location-detail">
                                <i class="fas fa-envelope"></i>
                                <div class="location-detail-content">
                                    <h5>Email</h5>
                                    <p>
                                        <a href="mailto:info@garagebarki.com">info@garagebarki.com</a><br>
                                        <a href="mailto:ventas@garagebarki.com">ventas@garagebarki.com</a>
                                    </p>
                                </div>
                            </div>

                            <div class="location-detail">
                                <i class="fas fa-clock"></i>
                                <div class="location-detail-content">
                                    <h5>Horario de Atención</h5>
                                    <p>
                                        Lunes - Viernes: 8:00 AM - 5:00 PM<br>
                                        Sábados: 8:00 AM - 5:00 PM<br>
                                        Domingos: 10:00 AM - 1:00 PM
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Opciones de contacto rápido mejoradas -->
        <section class="py-5 bg-light">
            <div class="container">
                <h2 class="text-center mb-5" data-aos="fade-up">OTRAS FORMAS DE CONTACTARNOS</h2>
                <div class="row g-4">
                    <div class="col-md-4" data-aos="fade-up">
                        <div class="quick-contact-card">
                            <i class="fab fa-whatsapp"></i>
                            <h4>WhatsApp</h4>
                            <p>Chatea con nosotros en tiempo real</p>
                            <a href="https://wa.me/584245287855" class="btn btn-outline-dark" target="_blank">Abrir Chat</a>
                        </div>
                    </div>
                    <div class="col-md-4" data-aos="fade-up" data-aos-delay="100">
                        <div class="quick-contact-card">
                            <i class="fab fa-facebook-messenger"></i>
                            <h4>Messenger</h4>
                            <p>Envíanos un mensaje por Facebook</p>
                            <a href="#" class="btn btn-outline-dark">Enviar Mensaje</a>
                        </div>
                    </div>
                    <div class="col-md-4" data-aos="fade-up" data-aos-delay="200">
                        <div class="quick-contact-card">
                            <i class="fas fa-store"></i>
                            <h4>Visítanos</h4>
                            <p>Ven a nuestra tienda física</p>
                            <a href="/BarkiOS/ubicanos" class="btn btn-outline-dark">Ver Ubicación</a>
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
    <!-- Contact Form Script -->
    <script src="/BarkiOS/public/assets/js/front/contact.js"></script>
</body>
</html>

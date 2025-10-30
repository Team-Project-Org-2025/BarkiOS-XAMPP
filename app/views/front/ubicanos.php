<?php $pageTitle = "Ubicanos | Garage Barki"; ?>
<?php require_once __DIR__ . '/../partials/header.php';?>
<?php require_once __DIR__ . '/../partials/navbar.php';?>

    <!-- Main Content -->
    <main>
        <!-- Hero Banner elegante -->
        <section class="hero-banner" style="background-image: url('https://images.unsplash.com/photo-1441984904996-e0b6ba687e04?ixlib=rb-4.0.3&auto=format&fit=crop&w=1470&q=80');">
            <div class="hero-banner-content">
                <h1 data-aos="fade-up">UBÍCANOS</h1>
                <p data-aos="fade-up" data-aos-delay="200">Visita nuestra tienda y descubre la experiencia Garage Barki</p>
            </div>
        </section>

        <!-- Mapa mejorado -->
        <section class="py-0">
            <div class="container-fluid p-0">
                <div class="map-container" data-aos="fade-up">
                    <iframe 
                        src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3916.849299169763!2d-69.31893792512194!3d10.06972238999382!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x8e8767138adaaeb7%3A0xdbbee2d5bf528015!2sGarage%20Barki!5e0!3m2!1ses!2sve!4v1730320500000!5m2!1ses!2sve"
                        width="100%" 
                        height="450" 
                        style="border:0;" 
                        allowfullscreen="" 
                        loading="lazy" 
                        referrerpolicy="no-referrer-when-downgrade">
                    </iframe>
                </div>
            </div>
        </section>

        <!-- Información de tienda mejorada -->
        <section class="py-5">
            <div class="container">
                <div class="row g-5">
                    <!-- Store Details -->
                    <div class="col-lg-6" data-aos="fade-right">
                        <h2 class="mb-4">NUESTRA TIENDA</h2>
                        <p class="lead mb-4">Te esperamos en nuestro espacio exclusivo donde podrás ver y probar nuestras prendas únicas.</p>
                        
                        <div class="location-info-card">
                            <h3>Información de Contacto</h3>
                            
                            <div class="location-detail">
                                <i class="fas fa-map-marker-alt"></i>
                                <div class="location-detail-content">
                                  <h5>Dirección</h5>
                                  <p>
                                    Calle 25 con Carrera 23<br>
                                    Diagonal al Centro Comercial Cosmos<br>
                                    Barquisimeto, Lara - Venezuela
                                  </p>
                                </div>
                            </div>

                            <div class="location-detail">
                                <i class="fas fa-clock"></i>
                                <div class="location-detail-content">
                                    <h5>Horario de Atención</h5>
                                    <p>
                                        <strong>Lunes - Viernes:</strong> 10:00 AM - 8:00 PM<br>
                                        <strong>Sábados:</strong> 10:00 AM - 9:00 PM<br>
                                        <strong>Domingos:</strong> 11:00 AM - 7:00 PM
                                    </p>
                                </div>
                            </div>

                            <div class="location-detail">
                                <i class="fas fa-phone"></i>
                                <div class="location-detail-content">
                                    <h5>Teléfono</h5>
                                    <p>
                                        <a href="tel:0424-2838909">0424-2838909</a><br>
                                        <a href="tel:0412-3637898">0412-3637898</a>
                                    </p>
                                </div>
                            </div>

                            <div class="location-detail">
                                <i class="fas fa-envelope"></i>
                                <div class="location-detail-content">
                                    <h5>Email</h5>
                                    <p>
                                        <a href="mailto:info@garagebarki.com">info@garagebarki.com</a>
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Store Image & Features -->
                    <div class="col-lg-6" data-aos="fade-left">
                        <img src="https://images.unsplash.com/photo-1441984904996-e0b6ba687e04?ixlib=rb-4.0.3&auto=format&fit=crop&w=1470&q=80" class="img-fluid rounded shadow mb-4" alt="Tienda Garage Barki">
                        
                        <h3 class="mb-4">¿Por qué visitarnos?</h3>
                        <div class="row g-3">
                            <div class="col-md-6">
                                <div class="info-card text-center">
                                    <div class="info-card-icon">
                                        <i class="fas fa-user-tie"></i>
                                    </div>
                                    <h5>Asesoría Personalizada</h5>
                                    <p>Nuestro equipo te ayudará a encontrar la prenda perfecta.</p>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="info-card text-center">
                                    <div class="info-card-icon">
                                        <i class="fas fa-tshirt"></i>
                                    </div>
                                    <h5>Prueba las Prendas</h5>
                                    <p>Amplios probadores para tu comodidad.</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Cómo llegar -->
        <section class="py-5 bg-light">
          <div class="container">
            <h2 class="text-center mb-5" data-aos="fade-up">CÓMO LLEGAR</h2>
            <div class="row g-4">

              <!-- En Auto -->
              <div class="col-md-4" data-aos="fade-up">
                <div class="transport-card">
                  <i class="fas fa-car"></i>
                  <h5>En Auto</h5>
                  <p>
                    Puedes llegar fácilmente desde la Carrera 22  y cruzas en la calle 25.
                  </p>
                </div>
              </div>

              <!-- En Transporte Público -->
              <div class="col-md-4" data-aos="fade-up" data-aos-delay="100">
                <div class="transport-card">
                  <i class="fas fa-bus"></i>
                  <h5>En Transporte Público</h5>
                  <p>
                    Diversas rutas de autobuses y taxis pasan por la Calle 25 y la Carrera 23. 
                  </p>
                </div>
              </div>

              <!-- A Pie o en Moto -->
              <div class="col-md-4" data-aos="fade-up" data-aos-delay="200">
                <div class="transport-card">
                  <i class="fas fa-walking"></i>
                  <h5>A Pie</h5>
                  <p>
                    Si te encuentras por el centro de Barquisimeto, nuestra 
                    tienda está esperandote.
                  </p>
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
    <script src="/BarkiOS/public/assets/js/about.js"></script>
</body>
</html>

<?php $pageTitle = "Login | Garage Barki"; ?>
<?php require_once __DIR__ . '/../partials/header-admin.php'; ?>

<body class="bg-light d-flex align-items-center justify-content-center min-vh-100 w-100">
    
    <div class="card-wrapper mx-auto" style="max-width: 400px;"> 
        <div class="card login-card shadow-lg">
            <div class="card-body p-4 p-md-5">
                <div class="text-center mb-4">
                    <h1>GARAGE <span>BARKI</span></h1>
                    <p class="text-muted">Panel de Administración</p>
                </div>
                
                <?php if (isset($error) && !empty($error)): ?>
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <i class="fas fa-exclamation-circle me-2"></i>
                        <?php echo htmlspecialchars($error); ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                <?php endif; ?>
                
                <!-- ✅ CORREGIDO: Action apunta a /admin/login/login -->
                <form action="/BarkiOS/admin/login/login" method="POST" id="loginForm"> 
                    <div class="mb-4">
                        <label for="email" class="form-label">Usuario</label>
                        <div class="input-group">
                            <span class="input-group-text">
                                <i class="fas fa-envelope"></i>
                            </span>
                            <input type="email" 
                                   class="form-control" 
                                   id="email" 
                                   name="email" 
                                   placeholder="correo@ejemplo.com"
                                   value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>"
                                   required 
                                   autocomplete="email"
                                   autofocus> 
                        </div>
                    </div>
                    
                    <div class="mb-4">
                        <label for="password" class="form-label">Contraseña</label>
                        <div class="input-group">
                            <span class="input-group-text">
                                <i class="fas fa-lock"></i>
                            </span>
                            <input type="password" 
                                   class="form-control" 
                                   id="password" 
                                   name="password" 
                                   placeholder="••••••••" 
                                   required
                                   autocomplete="current-password">
                            <button class="btn btn-outline-secondary" 
                                    type="button" 
                                    id="togglePassword"
                                    title="Mostrar/Ocultar contraseña">
                                <i class="fas fa-eye" id="toggleIcon"></i>
                            </button>
                        </div>
                        <small class="text-muted">
                            Mínimo 8 caracteres con mayúsculas, minúsculas, números y símbolos
                        </small>
                    </div>
                    
                    <div class="mb-4 form-check">
                        <input type="checkbox" 
                               class="form-check-input" 
                               id="rememberMe" 
                               name="remember">
                        <label class="form-check-label" for="rememberMe">
                            Recordar mis datos
                        </label>
                    </div>
                    
                    <div class="d-grid gap-2 mt-4">
                        <button type="submit" class="btn btn-primary py-2" id="loginBtn">
                            <span class="spinner-border spinner-border-sm d-none me-2" role="status" id="loginSpinner"></span>
                            <span id="loginBtnText">Ingresar</span>
                        </button>
                    </div>
                </form>
                
                <!-- Enlace para volver al sitio público -->
                <div class="text-center mt-4">
                    <a href="/BarkiOS/" class="text-decoration-none text-muted small">
                        <i class="fas fa-arrow-left me-1"></i>
                        Volver al sitio
                    </a>
                </div>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Toggle password visibility
        document.getElementById('togglePassword')?.addEventListener('click', function() {
            const passwordInput = document.getElementById('password');
            const toggleIcon = document.getElementById('toggleIcon');
            
            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                toggleIcon.classList.remove('fa-eye');
                toggleIcon.classList.add('fa-eye-slash');
            } else {
                passwordInput.type = 'password';
                toggleIcon.classList.remove('fa-eye-slash');
                toggleIcon.classList.add('fa-eye');
            }
        });
        
        // Validación del formulario antes de enviar
        document.getElementById('loginForm')?.addEventListener('submit', function(e) {
            const email = document.getElementById('email').value.trim();
            const password = document.getElementById('password').value;
            
            // Validar que no estén vacíos
            if (!email || !password) {
                e.preventDefault();
                alert('Por favor, complete todos los campos');
                return false;
            }
            
            // Mostrar spinner mientras se procesa
            const loginBtn = document.getElementById('loginBtn');
            const loginSpinner = document.getElementById('loginSpinner');
            const loginBtnText = document.getElementById('loginBtnText');
            
            loginBtn.disabled = true;
            loginSpinner.classList.remove('d-none');
            loginBtnText.textContent = 'Iniciando sesión...';
        });
        
        // Auto-cerrar la alerta después de 5 segundos
        <?php if (isset($error)): ?>
        setTimeout(() => {
            const alert = document.querySelector('.alert');
            if (alert) {
                alert.classList.remove('show');
                setTimeout(() => alert.remove(), 150);
            }
        }, 5000);
        <?php endif; ?>
    </script>
</body>
</html>
<?php $pageTitle = "Login | Garage Barki"; ?>
<?php require_once __DIR__ . '/../partials/header-admin.php'; ?>
 

<body>
    <div class="login-container">
        <div class="card login-card">
            <div class="card-body p-4 p-md-5">
                <div class="login-logo">
                    <h1>GARAGE<span>BARKI</span></h1>
                    <p class="text-muted">Panel de Administración</p>
                </div>
                
                <form>
                    <div class="mb-4">
                        <label for="email" class="form-label">Usuario</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="fas fa-envelope"></i></span>
                            <input type="email" class="form-control" id="email" placeholder="usuario" required>
                        </div>
                    </div>
                    
                    <div class="mb-4">
                        <label for="password" class="form-label">Contraseña</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="fas fa-lock"></i></span>
                            <input type="password" class="form-control" id="password" placeholder="••••••••" required>
                        </div>
                    </div>
                    
                    <div class="d-flex justify-content-between align-items-center mb-4">
                    <a href="/BarkiOS/home/" class="btn btn-primary w-100 py-2">Ingresar</a>
                </form>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

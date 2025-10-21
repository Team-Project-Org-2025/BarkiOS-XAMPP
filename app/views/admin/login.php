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
                    <div class="alert alert-danger" role="alert">
                        <?php echo htmlspecialchars($error); ?>
                    </div>
                <?php endif; ?>
                
                <form action="/BarkiOS/login/login" method="POST"> 
                    <div class="mb-4">
                        <label for="email" class="form-label">Usuario</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="fas fa-envelope"></i></span>
                            <input type="email" class="form-control" id="email" name="email" placeholder="usuario" required> 
                        </div>
                    </div>
                    
                    <div class="mb-4">
                        <label for="password" class="form-label">Contraseña</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="fas fa-lock"></i></span>
                            <input type="password" class="form-control" id="password" name="password" placeholder="••••••••" required>
                        </div>
                    </div>
                    
                    <div class="d-grid gap-2 mt-4">
                        <button type="submit" class="btn btn-primary py-2">Ingresar</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
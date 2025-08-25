<?php
session_start();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Garage Barki - Exclusividad en Moda</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Bodoni+Moda:opsz,wght@6..96,400;6..96,500;6..96,600;6..96,700&display=swap" rel="stylesheet">
    <!-- Custom Fonts -->
    <link href="views/assets/fonts/fonts.css" rel="stylesheet">
    <!-- Custom CSS -->
    <link rel="stylesheet" href="views/assets/css/styles.css">
    <!-- AOS Animation Library -->
    <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">
</head>
<body>
    <?php include 'views/components/preloader.php'; ?>
    <?php include 'views/components/navbar.php'; ?>

    <!-- Main Content (View) -->
    <main>
        <?php include 'views/components/hero-carousel.php'; ?>
        <?php include 'views/components/unique-selling-proposition.php'; ?>
        <?php include 'views/components/categories.php'; ?>
        <?php include 'views/components/featured-products.php'; ?>
    </main>

    <?php include 'views/components/footer.php'; ?>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <!-- AOS Animation Library -->
    <script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
    <!-- Custom JS -->
    <script src="views/assets/js/main.js"></script>
    <script>
        AOS.init();
    </script>
</body>
</html> 
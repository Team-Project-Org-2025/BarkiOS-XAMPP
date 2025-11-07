<?php
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= isset($pageTitle) ? $pageTitle : 'Garage Barki' ?></title>
    
    <!-- Font Awesome -->
    <link href="/BarkiOS/public/assets/libs/@fortawesome/fontawesome-free/css/all.min.css" rel="stylesheet">
    
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <link rel="shortcut icon" href= "/BarkiOS/public/assets/icons/Logo - Garage Barki.webp" type="image/x-icon">
    
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.4/css/dataTables.bootstrap5.min.css">

     <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/skeleton-elements@4.0.1/skeleton-elements.css">

    <link href="/BarkiOS/public/assets/libs/bootstrap/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <link rel="stylesheet" href="/BarkiOS/public/assets/css/admin-styles.css">

        <style>
        /* Personalización de skeleton para BarkiOS */
        .skeleton-block {
            background: linear-gradient(
                90deg,
                #f0f0f0 0%,
                #f8f8f8 50%,
                #f0f0f0 100%
            );
            background-size: 200% 100%;
            animation: skeleton-loading 1.5s ease-in-out infinite;
            border-radius: 4px;
        }

        @keyframes skeleton-loading {
            0% { background-position: 200% 0; }
            100% { background-position: -200% 0; }
        }

        /* Skeleton para tablas */
        .skeleton-table-row {
            height: 50px;
        }

        .skeleton-table-cell {
            padding: 12px;
        }

        /* Fade in cuando carga el contenido */
        .content-loaded {
            animation: fadeIn 0.3s ease-in;
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(10px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        /* Reducir animaciones para usuarios con preferencias */
        @media (prefers-reduced-motion: reduce) {
            .skeleton-block {
                animation: none;
                background: #f0f0f0;
            }
        }
    </style>

    
</head>
<body>
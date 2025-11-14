<?php

namespace Barkios\controllers;

use Exception;

class FrontController {
    private $controllerName;
    private $action;
    private $params = [];
    private $isAdmin = false; 

    public function __construct() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        if (!defined('ROOT_PATH')) {
            define('ROOT_PATH', dirname(__DIR__, 2) . '/');
        }

        $this->parseUrl();
        $this->loadController();
    }

 
    private function parseUrl(): void {
        $requestUri = $_SERVER['REQUEST_URI'] ?? '/';
        $uri = parse_url($requestUri, PHP_URL_PATH);

        $base = '/BarkiOS/';
        if (stripos($uri, $base) === 0) {
            $uri = substr($uri, strlen($base));
        }

        $segments = array_values(array_filter(explode('/', $uri)));

        if (!empty($segments) && strtolower($segments[0]) === 'admin') {
            $this->isAdmin = true;
            array_shift($segments); 

            if (empty($segments)) {
                $this->controllerName = 'login';
                $this->action = 'dashboard';
                $this->params = [];
            } else {
                // /admin/controlador/accion/params
                $this->controllerName = $this->sanitize($segments[0]);
                $this->action = $this->sanitize($segments[1] ?? 'index');
                $this->params = array_slice($segments, 2);
            }
        } 

        else {
            $this->isAdmin = false;


            if (empty($segments)) {
                $this->controllerName = 'inicio';
                $this->action = 'index';
                $this->params = [];
            } else {
                $this->controllerName = $this->sanitize($segments[0]);
                $this->action = $this->sanitize($segments[1] ?? 'index');
                $this->params = array_slice($segments, 2);
            }
        }
    }

    /**
     * Carga el controlador correcto según el área (Admin o Front)
     */
    private function loadController(): void {
        // Determinar la carpeta del controlador
        $controllerFolder = $this->isAdmin ? 'admin' : 'front';
        $controllerFile = ROOT_PATH . "app/controllers/{$controllerFolder}/" 
                        . ucfirst($this->controllerName) . "Controller.php";

        if (!file_exists($controllerFile)) {
            $this->renderNotFound(
                "El controlador '{$this->controllerName}' no existe en el área " 
                . ($this->isAdmin ? 'Admin' : 'Front')
            );
            return;
        }

        require_once $controllerFile;


        if (!function_exists($this->action)) {
            $this->renderNotFound(
                "La función '{$this->action}()' no existe en el controlador '{$this->controllerName}'"
            );
            return;
        }

        try {
            call_user_func_array($this->action, $this->params);
        } catch (Exception $e) {
            $this->renderNotFound("Error interno: " . $e->getMessage());
        }
    }


    private function sanitize(string $input): string {
        return preg_replace('/[^a-zA-Z0-9_]/', '', $input);
    }


    private function renderNotFound(string $message, bool $isAjax = false): void {
        http_response_code(404);

        $isAjax = $isAjax || (
            !empty($_SERVER['HTTP_X_REQUESTED_WITH']) &&
            strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest'
        );

        if ($isAjax) {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => $message]);
        } else {
echo "<!DOCTYPE html>
<html lang='es'>
<head>
    <meta charset='UTF-8'>
    <meta name='viewport' content='width=device-width, initial-scale=1.0'>
    <title>Error 404 | Garage Barki</title>
    <link rel=\"shortcut icon\" href= \"/BarkiOS/public/assets/icons/Logo - Garage Barki.webp\" type=\"image/x-icon\">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Montserrat:wght@400;700&display=swap');

        body {
            /* Fondo blanco para que coincida con el sitio */
            background-color: #ffffff;
            /* Color de texto principal negro/gris oscuro */
            color: #333333; 
            /* Usar una fuente similar o por defecto si no se puede importar la exacta */
            font-family: 'Montserrat', sans-serif;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
            flex-direction: column; /* Asegura que el contenido esté centrado */
            text-align: center;
        }
        /* Contenedor minimalista, sin fondo semitransparente ni blur */
        .error-container {
            text-align: center;
            padding: 2rem;
        }
        /* El logo, si puedes incluirlo (opcional) */
        .logo {
            font-size: 2.5rem;
            font-weight: 700;
            letter-spacing: 5px; /* Para simular el estilo del logo 'GARAGEBARKI' */
            margin-bottom: 2rem;
            text-transform: uppercase;
        }
        h1 { 
            /* Un tamaño grande para el '404' pero en color negro */
            font-size: 8rem; 
            margin: 0; 
            font-weight: 700;
            color: #111111;
        }
        h2 {
            font-size: 1.5rem;
            font-weight: 400;
            margin-top: 0;
        }
        p { 
            font-size: 1.1rem; 
            margin-bottom: 2rem;
        }
        a {
            display: inline-block;
            margin-top: 1rem;
            padding: 0.8rem 2.5rem;
            /* Botón con fondo blanco y borde negro, o viceversa, para un look limpio */
            background-color: #ffffff; 
            color: #333333;
            text-decoration: none;
            border: 1px solid #333333; /* Borde sutil */
            border-radius: 0; /* Bordes cuadrados o ligeramente redondeados si usas esa estética */
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 1px;
            transition: all 0.3s ease;
        }
        a:hover { 
            /* Efecto hover simple, invirtiendo colores para un look elegante */
            background-color: #333333;
            color: #ffffff; 
            transform: none; /* Quitamos el scale del código original para un look más formal */
        }
    </style>
</head>
<body>
    <div class='error-container'>
        <div class='logo'>GARAGE BARKI</div>
        <h1>404</h1>
        <h2>Página no encontrada</h2>
        <p>Lo sentimos, la página que buscas no existe o se ha movido.</p>
        <a href='/BarkiOS/'>Volver al Inicio</a>
    </div>
</body>
</html>";
        }
        exit();
    }
}
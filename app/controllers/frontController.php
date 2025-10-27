<?php

namespace Barkios\controllers;

use Exception;

/**
 * FRONT CONTROLLER - Versión Dual (Admin + Front)
 * 
 * Maneja dos áreas:
 * - /BarkiOS/ → Controladores y vistas de Front (público)
 * - /BarkiOS/admin/ → Controladores y vistas de Admin
 */
class FrontController {
    private $controllerName;
    private $action;
    private $params = [];
    private $isAdmin = false; // Indica si estamos en el área de administración

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

    /**
     * Analiza la URI y determina si es Admin o Front
     */
    private function parseUrl(): void {
        $requestUri = $_SERVER['REQUEST_URI'] ?? '/';
        $uri = parse_url($requestUri, PHP_URL_PATH);

        // Remover la base del proyecto
        $base = '/BarkiOS/';
        if (stripos($uri, $base) === 0) {
            $uri = substr($uri, strlen($base));
        }

        $segments = array_values(array_filter(explode('/', $uri)));

        // ============================================
        // DETECTAR SI ES ÁREA DE ADMINISTRACIÓN
        // ============================================
        if (!empty($segments) && strtolower($segments[0]) === 'admin') {
            $this->isAdmin = true;
            array_shift($segments); // Remover 'admin' de los segmentos

            // Si no hay más segmentos después de /admin/, cargar dashboard
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
        // ============================================
        // ÁREA PÚBLICA (FRONT)
        // ============================================
        else {
            $this->isAdmin = false;

            // Si la URL es solo /BarkiOS/ → cargar inicio
            if (empty($segments)) {
                $this->controllerName = 'inicio';
                $this->action = 'index';
                $this->params = [];
            } else {
                // /controlador/accion/params
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

        // Verificar si existe el archivo
        if (!file_exists($controllerFile)) {
            $this->renderNotFound(
                "El controlador '{$this->controllerName}' no existe en el área " 
                . ($this->isAdmin ? 'Admin' : 'Front')
            );
            return;
        }

        require_once $controllerFile;

        // Verificar si la función existe
        if (!function_exists($this->action)) {
            $this->renderNotFound(
                "La función '{$this->action}()' no existe en el controlador '{$this->controllerName}'"
            );
            return;
        }

        try {
            // Ejecutar la función del controlador
            call_user_func_array($this->action, $this->params);
        } catch (Exception $e) {
            $this->renderNotFound("Error interno: " . $e->getMessage());
        }
    }

    /**
     * Sanitiza valores de URL
     */
    private function sanitize(string $input): string {
        return preg_replace('/[^a-zA-Z0-9_]/', '', $input);
    }

    /**
     * Renderiza un mensaje 404
     */
    private function renderNotFound(string $message, bool $isAjax = false): void {
        http_response_code(404);

        // Detectar si es una petición AJAX
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
                <style>
                    body {
                        font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
                        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
                        color: #fff;
                        display: flex;
                        justify-content: center;
                        align-items: center;
                        height: 100vh;
                        margin: 0;
                    }
                    .error-container {
                        text-align: center;
                        background: rgba(255, 255, 255, 0.1);
                        padding: 3rem;
                        border-radius: 15px;
                        backdrop-filter: blur(10px);
                    }
                    h1 { font-size: 6rem; margin: 0; }
                    p { font-size: 1.2rem; }
                    a {
                        display: inline-block;
                        margin-top: 1rem;
                        padding: 0.8rem 2rem;
                        background: #fff;
                        color: #667eea;
                        text-decoration: none;
                        border-radius: 25px;
                        font-weight: bold;
                        transition: transform 0.3s;
                    }
                    a:hover { transform: scale(1.05); }
                </style>
            </head>
            <body>
                <div class='error-container'>
                    <h1>404</h1>
                    <p>{$message}</p>
                    <a href='/BarkiOS/'>Volver al Inicio</a>
                </div>
            </body>
            </html>";
        }
        exit();
    }
}
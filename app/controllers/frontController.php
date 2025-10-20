<?php

namespace Barkios\controllers;

use Exception;

/**
 * FRONT CONTROLLER (versión funcional)
 * 
 * Mantiene la estructura de clase, pero ejecuta controladores basados en funciones,
 * sin necesidad de instanciar clases.
 */
class FrontController {
    private $controllerName; // Ej: 'login'
    private $action;         // Ej: 'show', 'login', 'dashboard', 'logout'
    private $params = [];

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
     * Analiza la URI y determina controlador, acción y parámetros.
     */
    private function parseUrl(): void {
        $requestUri = $_SERVER['REQUEST_URI'] ?? '/';
        $uri = parse_url($requestUri, PHP_URL_PATH);

        // Ajusta la base del proyecto según tu entorno
        $base = '/BarkiOS/';
        if (stripos($uri, $base) === 0) {
            $uri = substr($uri, strlen($base));
        }

        $segments = array_values(array_filter(explode('/', $uri)));

        if (empty($segments)) {
            $segments = ['login', 'show'];
        }

        if (strtolower($segments[0]) === 'admin') {
            $this->controllerName = 'login';
            $this->action = $this->sanitize($segments[1] ?? 'dashboard');
            $this->params = array_slice($segments, 2);
            return;
        }

        $this->controllerName = $this->sanitize($segments[0] ?? 'home');
        $this->action = $this->sanitize($segments[1] ?? 'index');
        $this->params = array_slice($segments, 2);
    }

    /**
     * Carga el controlador (archivo PHP) y ejecuta la función.
     */
    private function loadController(): void {
        $controllerFile = ROOT_PATH . "app/controllers/admin/" . ucfirst($this->controllerName) . "Controller.php";

        // Si no existe el archivo del controlador
        if (!file_exists($controllerFile)) {
            $this->renderNotFound("El archivo del controlador '{$controllerFile}' no existe.");
            return;
        }

        require_once $controllerFile;

        // Verificar si la función existe (ya no hay clases)
        if (!function_exists($this->action)) {
            $this->renderNotFound("La función '{$this->action}()' no existe en el controlador '{$this->controllerName}'.");
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
     * Sanitiza valores de URL.
     */
    private function sanitize(string $input): string {
        return preg_replace('/[^a-zA-Z0-9_]/', '', $input);
    }

    /**
     * Renderiza un mensaje 404 amigable.
     */
    private function renderNotFound(string $message, bool $isAjax = false): void {
        http_response_code(404);

        if ($isAjax) {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => $message]);
        } else {
            echo "<h1>Error 404</h1><p>$message</p>";
        }
        exit();
    }
}

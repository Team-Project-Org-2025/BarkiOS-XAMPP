<?php

namespace Barkios\controllers;


//TODO: Refactorizar todo 
class FrontController {
    private $controller;
    private $action;
    private $params = [];

    public function __construct() {
        $this->parseUrl();
        $this->loadController();
    }

    private function parseUrl() {
        $requestUri = $_SERVER['REQUEST_URI'] ?? '/';

        // Quita el query string
        $uri = parse_url($requestUri, PHP_URL_PATH);

        // Quita el path base '/barkios/app/' (ajusta si cambias la carpeta)
        $base = '/BarkiOS/'; // Usa el nombre real de tu carpeta en Windows
        if (stripos($uri, $base) === 0) {
            $uri = substr($uri, strlen($base));
        }

        $segments = array_values(array_filter(explode('/', $uri)));

        // Soporte para rutas admin
        if (isset($segments[0]) && strtolower($segments[0]) === 'admin') {
            array_shift($segments);
        }

        $this->controller = $this->sanitize($segments[0] ?? 'login');
        $this->action = $this->sanitize($segments[1] ?? 'index');
        $this->params = array_slice($segments, 2);
    }

    private function sanitize($input) {
        return preg_replace('/[^a-zA-Z0-9_]/', '', $input);
    }

    private function loadController() {
        $isAjax = !empty($_SERVER['HTTP_X_REQUESTED_WITH']) &&
                  strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';

        $controllerName = ucfirst($this->controller) . 'Controller'; // Ej: ProductsController
        $controllerFile = __DIR__ . "/Admin/{$controllerName}.php";

        if (!file_exists($controllerFile)) {
            return $this->renderNotFound("Controlador no encontrado: {$controllerFile}", $isAjax);
        }

        require_once $controllerFile;

        $functionName = $this->action;

        if (!function_exists($functionName)) {
            return $this->renderNotFound("Función '{$functionName}' no encontrada en $controllerFile", $isAjax);
        }

        call_user_func_array($functionName, $this->params);
    }

    private function renderNotFound($message, $isAjax = false) {
        if ($isAjax) {
            http_response_code(404);
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => $message]);
        } else {
            http_response_code(404);
            echo "<h1>Error 404</h1><p>$message</p>";
        }
        exit();
    }
}
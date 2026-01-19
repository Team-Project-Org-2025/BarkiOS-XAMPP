<?php
namespace Barkios;

// Usar __DIR__ asegura que PHP busque desde la carpeta donde está este index.php
require_once __DIR__ . '/vendor/autoload.php';

use Barkios\controllers\FrontController;

$front = new FrontController();
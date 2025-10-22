<?php
// app/core/AdminContext.php
// Este archivo se encarga de cargar datos globales necesarios para todo el panel de administración.

// 1. Incluir el servicio de la API
// Asumimos que la ruta es correcta desde app/core/ hasta app/services/
require_once __DIR__ . '/../services/ExchangeRateService.php';

use Barkios\services\ExchangeRateService;

// 2. Obtener la Tasa de Cambio
$exchangeService = new ExchangeRateService();
$dolarBCVRate = $exchangeService->getDollarRate();

// La variable $dolarBCVRate ahora está disponible para cualquier archivo que incluya AdminContext.php
// También se podría usar $GLOBALS['dolarBCVRate'] = $dolarBCVRate; si se necesitara un alcance más amplio.

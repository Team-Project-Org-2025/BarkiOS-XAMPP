<?php

require_once __DIR__ . '/../services/ExchangeRateService.php';

use Barkios\services\ExchangeRateService;

$exchangeService = new ExchangeRateService();

// Obtener la tasa del dólar (con caché automático)
$dolarBCVRate = $exchangeService->getDollarRate();

if (!function_exists('getDolarRate')) {

    function getDolarRate(): float {
        global $dolarBCVRate;
        return $dolarBCVRate;
    }
}

if (!function_exists('formatCurrency')) {

    function formatCurrency(float $amount, string $currency = 'Bs.'): string {
        return $currency . ' ' . number_format($amount, 2, ',', '.');
    }
}

if (!function_exists('convertToBolivares')) {

    function convertToBolivares(float $dollars): float {
        global $dolarBCVRate;
        return $dollars * $dolarBCVRate;
    }
}

if (!function_exists('convertToDollars')) {

    function convertToDollars(float $bolivares): float {
        global $dolarBCVRate;
        return $bolivares / $dolarBCVRate;
    }
}

$GLOBALS['dolarBCVRate'] = $dolarBCVRate;
$GLOBALS['exchangeService'] = $exchangeService;
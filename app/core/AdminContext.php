<?php
// app/core/AdminContext.php
// Contexto global para el panel de administración

require_once __DIR__ . '/../services/ExchangeRateService.php';

use Barkios\services\ExchangeRateService;

// Instancia del servicio
$exchangeService = new ExchangeRateService();

// Obtener la tasa del dólar (con caché automático)
$dolarBCVRate = $exchangeService->getDollarRate();

// Funciones helper para usar en las vistas
if (!function_exists('getDolarRate')) {
    /**
     * Obtiene la tasa del dólar BCV
     * @return float
     */
    function getDolarRate(): float {
        global $dolarBCVRate;
        return $dolarBCVRate;
    }
}

if (!function_exists('formatCurrency')) {
    /**
     * Formatea un monto en bolívares
     * @param float $amount
     * @param string $currency (Bs. por defecto)
     * @return string
     */
    function formatCurrency(float $amount, string $currency = 'Bs.'): string {
        return $currency . ' ' . number_format($amount, 2, ',', '.');
    }
}

if (!function_exists('convertToBolivares')) {
    /**
     * Convierte dólares a bolívares
     * @param float $dollars
     * @return float
     */
    function convertToBolivares(float $dollars): float {
        global $dolarBCVRate;
        return $dollars * $dolarBCVRate;
    }
}

if (!function_exists('convertToDollars')) {
    /**
     * Convierte bolívares a dólares
     * @param float $bolivares
     * @return float
     */
    function convertToDollars(float $bolivares): float {
        global $dolarBCVRate;
        return $bolivares / $dolarBCVRate;
    }
}

// Variables disponibles globalmente
$GLOBALS['dolarBCVRate'] = $dolarBCVRate;
$GLOBALS['exchangeService'] = $exchangeService;
<?php
namespace Barkios\services;

class ExchangeRateService {

    private const API_URL = 'https://bcv-api.rafnixg.dev/rates/';

    // Valor de respaldo en caso de que la API falle
    private const FALLBACK_RATE = 210.28; // Actualízalo según la última tasa conocida

    /**
     * Obtiene la tasa de cambio oficial del Dólar (USD) a Bolívar Soberano (VES) del BCV.
     * @return float La tasa de cambio (usando fallback si hay error)
     */
    public function getDollarRate(): float {
        $ch = curl_init(self::API_URL);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 5);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 3);

        // Cabeceras simulando un navegador real
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Accept: application/json,text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8',
            'Accept-Language: es-ES,es;q=0.9',
            'Connection: keep-alive',
            'Referer: https://bcv-api.rafnixg.dev/',
            'Origin: https://bcv-api.rafnixg.dev',
        ]);
        curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/117.0.0.0 Safari/537.36');

        $response = curl_exec($ch);
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curl_error = curl_error($ch);
        curl_close($ch);

        // Si hubo error de cURL o HTTP distinto a 200, usamos fallback
        if ($curl_error || $http_code !== 200) {
            error_log("ExchangeRateService: Error al obtener la tasa. cURL: $curl_error, HTTP: $http_code. Usando fallback.");
            return self::FALLBACK_RATE;
        }

        $data = json_decode($response, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            error_log("ExchangeRateService: JSON inválido. Error: " . json_last_error_msg() . ". Usando fallback.");
            return self::FALLBACK_RATE;
        }

        $rate = $data['dollar'] ?? null;

        if (!$rate || !is_numeric($rate)) {
            error_log("ExchangeRateService: La API devolvió 'N/D' o valor inválido. Usando fallback.");
            return self::FALLBACK_RATE;
        }

        return (float)$rate;
    }
}

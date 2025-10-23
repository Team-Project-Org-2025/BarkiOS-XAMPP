<?php
namespace Barkios\services;

class ExchangeRateService {

    private const PRIMARY_API = 'https://bcv-api.rafnixg.dev/rates/';
    private const BACKUP_API = 'https://pydolarve.org/api/v1/dollar?page=bcv';
    
    // Archivo de caché para almacenar la tasa
    private const CACHE_FILE = __DIR__ . '/../../storage/cache/exchange_rate.json';
    private const CACHE_DURATION = 3600; // 1 hora en segundos
    
    // Valor de respaldo en caso de que todas las APIs fallen
    private const FALLBACK_RATE = 212.48;

    /**
     * Obtiene la tasa de cambio del dólar con sistema de caché
     * @param bool $forceRefresh Forzar actualización ignorando caché
     * @return float La tasa de cambio
     */
    public function getDollarRate(bool $forceRefresh = false): float {
        // Intentar obtener desde caché primero
        if (!$forceRefresh) {
            $cachedRate = $this->getFromCache();
            if ($cachedRate !== null) {
                return $cachedRate;
            }
        }

        // Intentar API principal
        $rate = $this->fetchFromPrimaryAPI();
        
        // Si falla, intentar API de respaldo
        if ($rate === null) {
            $rate = $this->fetchFromBackupAPI();
        }
        
        // Si ambas fallan, usar fallback
        if ($rate === null) {
            error_log("ExchangeRateService: Todas las APIs fallaron. Usando FALLBACK_RATE.");
            return self::FALLBACK_RATE;
        }

        // Guardar en caché
        $this->saveToCache($rate);
        
        return $rate;
    }

    /**
     * Obtiene la tasa desde la API principal (bcv-api.rafnixg.dev)
     * @return float|null
     */
    private function fetchFromPrimaryAPI(): ?float {
        $ch = curl_init(self::PRIMARY_API);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); // Solo si tienes problemas SSL
        
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Accept: application/json',
            'User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36'
        ]);

        $response = curl_exec($ch);
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curl_error = curl_error($ch);
        curl_close($ch);

        if ($curl_error) {
            error_log("ExchangeRateService [Primary API]: cURL Error: $curl_error");
            return null;
        }

        if ($http_code !== 200) {
            error_log("ExchangeRateService [Primary API]: HTTP $http_code - Response: " . substr($response, 0, 200));
            return null;
        }

        $data = json_decode($response, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            error_log("ExchangeRateService [Primary API]: JSON Error: " . json_last_error_msg());
            return null;
        }

        $rate = $data['dollar'] ?? null;

        if (!$rate || !is_numeric($rate) || $rate === 'N/D') {
            error_log("ExchangeRateService [Primary API]: Valor inválido: " . print_r($rate, true));
            return null;
        }

        return (float)$rate;
    }

    /**
     * Obtiene la tasa desde API de respaldo (pydolarve.org)
     * @return float|null
     */
    private function fetchFromBackupAPI(): ?float {
        $ch = curl_init(self::BACKUP_API);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Accept: application/json',
            'User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36'
        ]);

        $response = curl_exec($ch);
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curl_error = curl_error($ch);
        curl_close($ch);

        if ($curl_error || $http_code !== 200) {
            error_log("ExchangeRateService [Backup API]: Error. cURL: $curl_error, HTTP: $http_code");
            return null;
        }

        $data = json_decode($response, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            return null;
        }

        // Esta API devuelve: {"monitors":{"bcv":{"price":"53.92",...}}}
        $rate = $data['monitors']['bcv']['price'] ?? null;

        if (!$rate || !is_numeric($rate)) {
            return null;
        }

        return (float)$rate;
    }

    /**
     * Obtiene la tasa desde el caché si es válido
     * @return float|null
     */
    private function getFromCache(): ?float {
        // Crear directorio si no existe
        $cacheDir = dirname(self::CACHE_FILE);
        if (!file_exists($cacheDir)) {
            mkdir($cacheDir, 0755, true);
        }

        if (!file_exists(self::CACHE_FILE)) {
            return null;
        }

        $cacheData = json_decode(file_get_contents(self::CACHE_FILE), true);
        
        if (!$cacheData || !isset($cacheData['rate'], $cacheData['timestamp'])) {
            return null;
        }

        // Verificar si el caché aún es válido
        if ((time() - $cacheData['timestamp']) > self::CACHE_DURATION) {
            return null;
        }

        return (float)$cacheData['rate'];
    }

    /**
     * Guarda la tasa en caché
     * @param float $rate
     */
    private function saveToCache(float $rate): void {
        $cacheDir = dirname(self::CACHE_FILE);
        if (!file_exists($cacheDir)) {
            mkdir($cacheDir, 0755, true);
        }

        $cacheData = [
            'rate' => $rate,
            'timestamp' => time(),
            'date' => date('Y-m-d H:i:s')
        ];

        file_put_contents(self::CACHE_FILE, json_encode($cacheData, JSON_PRETTY_PRINT));
    }

    /**
     * Limpia el caché manualmente
     */
    public function clearCache(): void {
        if (file_exists(self::CACHE_FILE)) {
            unlink(self::CACHE_FILE);
        }
    }

    /**
     * Obtiene información del estado del caché
     * @return array
     */
    public function getCacheInfo(): array {
        if (!file_exists(self::CACHE_FILE)) {
            return ['exists' => false];
        }

        $cacheData = json_decode(file_get_contents(self::CACHE_FILE), true);
        $age = time() - ($cacheData['timestamp'] ?? 0);
        
        return [
            'exists' => true,
            'rate' => $cacheData['rate'] ?? null,
            'age_seconds' => $age,
            'age_minutes' => round($age / 60, 2),
            'is_valid' => $age < self::CACHE_DURATION,
            'last_update' => $cacheData['date'] ?? 'Unknown'
        ];
    }
}
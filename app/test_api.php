<?php
// test_api.php

$url = 'https://bcv-api.rafnixg.dev/rates/';

$ch = curl_init($url);

// Configuración cURL
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 5);
curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 3);

// Cabeceras para evitar 403
curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64)');
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Accept: application/json',
    'Referer: https://bcv-api.rafnixg.dev/',
    'Origin: https://bcv-api.rafnixg.dev'
]);

$response = curl_exec($ch);
$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);

if (curl_errno($ch)) {
    echo "❌ Error cURL: " . curl_error($ch);
    curl_close($ch);
    exit;
}

curl_close($ch);

// Verificar HTTP 200
if ($http_code !== 200) {
    echo "❌ La API respondió con HTTP $http_code. Respuesta:\n";
    var_dump($response);
    exit;
}

// Decodificar JSON
$data = json_decode($response, true);
if (json_last_error() !== JSON_ERROR_NONE) {
    echo "❌ Error al decodificar JSON: " . json_last_error_msg();
    var_dump($response);
    exit;
}

// Mostrar la tasa
$rate = $data['dollar'] ?? null;
if (!$rate || !is_numeric($rate)) {
    echo "⚠️ La API devolvió 'N/D' o un valor inválido.";
} else {
    echo "💵 Tasa BCV actual: " . number_format($rate, 2, ',', '.') . " VES/USD";
}

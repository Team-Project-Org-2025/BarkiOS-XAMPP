<?php
// test_exchange_rate.php
// Coloca este archivo en la raíz de tu proyecto para probar

require_once __DIR__ . '/app/services/ExchangeRateService.php';

use Barkios\services\ExchangeRateService;

echo "<h1>🧪 Test de ExchangeRateService</h1>";

$service = new ExchangeRateService();

// Test 1: Obtener tasa (con caché)
echo "<h2>Test 1: Obtener Tasa (con caché)</h2>";
$start = microtime(true);
$rate = $service->getDollarRate();
$time = round((microtime(true) - $start) * 1000, 2);
echo "✅ Tasa obtenida: <strong>" . number_format($rate, 2) . " Bs.</strong><br>";
echo "⏱️ Tiempo: {$time}ms<br><br>";

// Test 2: Info del caché
echo "<h2>Test 2: Información del Caché</h2>";
$cacheInfo = $service->getCacheInfo();
echo "<pre>";
print_r($cacheInfo);
echo "</pre>";

// Test 3: Obtener tasa forzando actualización
echo "<h2>Test 3: Forzar Actualización (sin caché)</h2>";
$start = microtime(true);
$rate = $service->getDollarRate(true);
$time = round((microtime(true) - $start) * 1000, 2);
echo "✅ Tasa actualizada: <strong>" . number_format($rate, 2) . " Bs.</strong><br>";
echo "⏱️ Tiempo: {$time}ms<br><br>";

// Test 4: Conversiones
echo "<h2>Test 4: Conversiones</h2>";
$usd = 100;
$bs = $usd * $rate;
echo "$100 USD = " . number_format($bs, 2) . " Bs.<br>";
echo number_format($bs, 2) . " Bs. = $" . number_format($bs / $rate, 2) . " USD<br>";

echo "<hr>";
echo "<p><strong>Si ves la tasa aquí, tu integración funciona correctamente! 🎉</strong></p>";
echo "<p><a href='?clear=1'>🗑️ Limpiar Caché</a></p>";

if (isset($_GET['clear'])) {
    $service->clearCache();
    echo "<script>alert('Caché limpiado'); window.location.href = window.location.pathname;</script>";
}
?>

<style>
    body { font-family: Arial, sans-serif; padding: 20px; max-width: 800px; margin: 0 auto; }
    h1 { color: #667eea; }
    h2 { color: #764ba2; border-bottom: 2px solid #eee; padding-bottom: 10px; }
    pre { background: #f5f5f5; padding: 15px; border-radius: 5px; overflow: auto; }
    strong { color: #e74c3c; }
</style>
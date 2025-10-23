<?php
// app/views/partials/exchange-rate-widget.php
// Componente reutilizable para mostrar la tasa BCV

// Si no está cargado el AdminContext, cargarlo
if (!function_exists('getDolarRate')) {
    require_once __DIR__ . '/../../core/AdminContext.php';
}

$currentRate = getDolarRate();
?>

<!-- Widget de Tasa BCV -->
<div class="bcv-rate-badge">
    <i class="fas fa-dollar-sign me-2"></i>
    <div>
        <small class="d-block">Tasa BCV</small>
        <strong id="bcv-rate-display"><?php echo number_format($currentRate, 2); ?> Bs.</strong>
    </div>
    <button class="btn btn-sm btn-link text-white p-0 ms-2" 
            onclick="refreshExchangeRate()" 
            title="Actualizar tasa">
        <i class="fas fa-sync-alt"></i>
    </button>
</div>

<!-- Estilos del widget (solo se carga una vez) -->
<style>
    .bcv-rate-badge {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        background: #2c3e50;
        color: white;
        padding: 10px 16px;
        border-radius: 8px;
        box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        font-size: 0.9rem;
    }
    
    .bcv-rate-badge small {
        font-size: 0.7rem;
        opacity: 0.9;
        line-height: 1;
        margin-bottom: 2px;
    }
    
    .bcv-rate-badge strong {
        font-size: 1rem;
        line-height: 1;
    }
    
    .bcv-rate-badge .btn-link {
        opacity: 0.8;
        transition: opacity 0.2s;
    }
    
    .bcv-rate-badge .btn-link:hover {
        opacity: 1;
    }
    
    @keyframes spin {
        from { transform: rotate(0deg); }
        to { transform: rotate(360deg); }
    }
    
    .spinning {
        animation: spin 0.6s linear;
    }
</style>

<!-- JavaScript del widget (solo se carga una vez) -->
<script>
    // Variable global con la tasa del dólar
    if (typeof DOLAR_BCV_RATE === 'undefined') {
        const DOLAR_BCV_RATE = <?php echo $currentRate; ?>;
    }

    /**
     * Calcula el equivalente en bolívares
     */
    function calculateBolivares(usdAmount, targetElementId) {
        const bolivares = (parseFloat(usdAmount) || 0) * DOLAR_BCV_RATE;
        const element = document.getElementById(targetElementId);
        if (element) {
            element.textContent = 'Bs. ' + bolivares.toLocaleString('es-VE', { 
                minimumFractionDigits: 2, 
                maximumFractionDigits: 2 
            });
        }
    }

    /**
     * Actualiza la tasa del dólar
     */
    function refreshExchangeRate() {
        const btn = event.target.closest('button');
        const icon = btn.querySelector('i');
        
        icon.classList.add('spinning');
        
        setTimeout(() => {
            window.location.href = window.location.pathname + '?refresh_rate=1';
        }, 600);
    }

    /**
     * Convierte USD a Bolívares
     */
    function convertToBolivares(usd) {
        return parseFloat(usd) * DOLAR_BCV_RATE;
    }

    /**
     * Convierte Bolívares a USD
     */
    function convertToDollars(bs) {
        return parseFloat(bs) / DOLAR_BCV_RATE;
    }

    /**
     * Formatea moneda en bolívares
     */
    function formatBolivares(amount) {
        return 'Bs. ' + parseFloat(amount).toLocaleString('es-VE', {
            minimumFractionDigits: 2,
            maximumFractionDigits: 2
        });
    }

    /**
     * Formatea moneda en dólares
     */
    function formatDollars(amount) {
        return '$' + parseFloat(amount).toFixed(2);
    }
</script>
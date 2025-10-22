<?php
// app/services/ExchangeRateService.php
namespace Barkios\services;

use Exception;

/**
 * Service to fetch currency exchange rates, specifically USD to VEF.
 * Uses a third-party API to obtain the market reference.
 */
class ExchangeRateService {

    // Robust endpoint to get the USD to VES rate (Example: Exchange Rate Host)
    private const API_URL = 'https://api.exchangerate.host/latest?base=USD&symbols=VES';

    /**
     * Gets the exchange rate of the US Dollar against the Bolívar (VEF).
     * @return float|null The exchange rate (VEF per 1 USD) or null if it fails.
     */
    public function getDollarRate(): ?float {
        // Initializes cURL
        $ch = curl_init(self::API_URL);
        
        // cURL configuration
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true); // Returns the response as a string
        curl_setopt($ch, CURLOPT_TIMEOUT, 5); // Maximum wait time
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Accept: application/json',
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);

        // Connection error handling
        if ($response === false || $httpCode !== 200) {
            error_log("Error connecting to Exchange Rate API. HTTP Code: $httpCode. cURL Error: $error");
            return null;
        }

        $data = json_decode($response, true);

        // Value extraction: Assumes the JSON response has the path ['rates']['VES']
        if (isset($data['rates']['VES']) && is_numeric($data['rates']['VES'])) {
            return (float)$data['rates']['VES'];
        }

        error_log("Unexpected API response or incorrect format (did not find 'rates.VES'): " . $response);
        return null;
    }
}
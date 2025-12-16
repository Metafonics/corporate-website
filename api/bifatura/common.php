<?php
/**
 * Bifatura API Ortak Fonksiyonlar
 */

// getallheaders() fonksiyonu yoksa tanımla
if (!function_exists('getallheaders')) {
    function getallheaders() {
        $headers = [];
        foreach ($_SERVER as $name => $value) {
            if (substr($name, 0, 5) == 'HTTP_') {
                $headers[str_replace(' ', '-', ucwords(strtolower(str_replace('_', ' ', substr($name, 5)))))] = $value;
            }
        }
        return $headers;
    }
}

/**
 * Token doğrulama helper
 */
function validateBifaturaToken($skipValidation = false) {
    $config = BifaturaConfig::getInstance();
    $headers = getallheaders();
    $token = $headers['token'] ?? $headers['Token'] ?? $_SERVER['HTTP_TOKEN'] ?? $_GET['token'] ?? $_POST['token'] ?? null;

    // Debug log (geçici)
    @file_put_contents(__DIR__ . '/../../payment/bifatura_debug.txt',
        date('Y-m-d H:i:s') . " - Token: " . ($token ?? 'NULL') . "\n" .
        "Headers: " . json_encode($headers) . "\n" .
        "GET: " . json_encode($_GET) . "\n" .
        "POST: " . json_encode($_POST) . "\n\n",
        FILE_APPEND
    );

    // Geçici: Token kontrolünü atla
    if ($skipValidation) {
        return true;
    }

    if (!$config->validateToken($token)) {
        http_response_code(401);
        echo json_encode([
            'error' => 'Unauthorized',
            'message' => 'Geçersiz API token',
            'received_token' => $token,
            'expected_token_length' => strlen($config->getApiToken()),
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }

    return true;
}

<?php

require_once __DIR__ . '/../config/vizyonpos.php';

/**
 * VizionPOS API Servis Sınıfı
 *
 * 3D Secure ödeme işlemlerini yönetir
 */
class VizionPosService
{
    private $config;

    public function __construct()
    {
        $this->config = VizionPosConfig::getInstance();
    }

    /**
     * 3D Secure ödeme başlat
     *
     * @param array $paymentData Ödeme bilgileri
     * @return array API yanıtı
     */
    public function initialize3DSecurePayment($paymentData)
    {
        $url = $this->config->getThreeDSecureUrl();

        // İstek verilerini hazırla
        $requestData = array_merge([
            'callbackUrl' => $this->config->getCallbackUrl(),
        ], $paymentData);

        // API isteği gönder
        $response = $this->sendRequest($url, $requestData);

        return $response;
    }

    /**
     * API'ye HTTP POST isteği gönder
     *
     * @param string $url İstek URL'i
     * @param array $data Gönderilecek veri
     * @return array Yanıt
     */
    private function sendRequest($url, $data)
    {
        $ch = curl_init();

        // API kimlik bilgilerini hazırla
        $headers = [
            'Content-Type: application/json',
            'X-API-KEY: ' . $this->config->getApiKey(),
            'X-API-SECRET: ' . $this->config->getApiSecret(),
        ];

        curl_setopt_array($ch, [
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => json_encode($data),
            CURLOPT_HTTPHEADER => $headers,
            CURLOPT_SSL_VERIFYPEER => true,
            CURLOPT_TIMEOUT => 30,
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);

        curl_close($ch);

        if ($error) {
            return [
                'success' => false,
                'error' => 'CURL Hatası: ' . $error,
                'httpCode' => $httpCode,
            ];
        }

        $responseData = json_decode($response, true);

        return [
            'success' => $httpCode >= 200 && $httpCode < 300,
            'httpCode' => $httpCode,
            'data' => $responseData,
            'raw' => $response,
        ];
    }

    /**
     * Callback sonucunu işle
     *
     * @param array $callbackData Callback verisi
     * @return array İşlenmiş sonuç
     */
    public function handleCallback($callbackData)
    {
        // Callback verilerini doğrula ve işle
        $result = [
            'success' => false,
            'message' => '',
            'data' => $callbackData,
        ];

        // Burada callback verilerini işleyin
        // Örnek: Ödeme durumunu kontrol et, sipariş güncelle vb.

        if (isset($callbackData['status']) && $callbackData['status'] === 'success') {
            $result['success'] = true;
            $result['message'] = 'Ödeme başarılı';
        } else {
            $result['message'] = $callbackData['message'] ?? 'Ödeme başarısız';
        }

        return $result;
    }

    /**
     * Aktif ortam bilgisini al
     */
    public function getEnvironmentInfo()
    {
        return [
            'environment' => $this->config->getEnvironment(),
            'isProduction' => $this->config->isProduction(),
            'isStage' => $this->config->isStage(),
            'baseUrl' => $this->config->getBaseUrl(),
        ];
    }
}

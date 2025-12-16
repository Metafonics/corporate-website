<?php

require_once 'env.php';

/**
 * Bifatura E-Fatura Entegrasyonu Konfigürasyonu
 */
class BifaturaConfig
{
    private static $instance = null;

    // API Anahtarı (Token - GUID formatında)
    // Bu token'ı Bifatura'ya özel entegrasyon mağaza ayarlarında API şifresi olarak vermeniz gerekiyor
    private $apiToken; // BURAYA BİFATURA İÇİN OLUŞTURACAĞINIZ GUID TOKEN GELECEK

    private $isEnabled = true; // Bifatura entegrasyonunu aktif/pasif yap

    // Site ana URL'i (Base URL)
    private $baseUrl; // BURAYA SİZİN SİTE ADRESİNİZ GELECEK

    // Bifatura API endpoint'leri (sizin sitenizde oluşturacağımız)
    private $endpoints = [
        'orderStatus' => '/api/bifatura/order-status',
        'paymentMethods' => '/api/bifatura/payment-methods',
        'orders' => '/api/bifatura/orders',
        'orderCargoUpdate' => '/api/bifatura/order-cargo-update',
        'invoiceLinkUpdate' => '/api/bifatura/invoice-link-update',
    ];

    // Varsayılan ayarlar
    private $settings = [
        'auto_invoice' => true, // Ödeme başarılı olduğunda otomatik fatura kes
        'default_vat_rate' => 20.00, // Varsayılan KDV oranı
        'invoice_type_id' => 1, // 1: e-Fatura, 2: e-Arşiv, 3: İrsaliye, 4: Kurumsal Fatura
        'e_invoice_profile_id' => 1, // E-Fatura profil ID (1: TEMELFATURA, 2: TICARIFATURA)
        'currency' => 'TRY',
        'quantity_type' => 'Adet',
    ];

    private function __construct()
    {
        // Singleton pattern
        EnvLoader::load();
        $this->apiToken = EnvLoader::get('BIFATURA_API_TOKEN');
        $this->baseUrl = EnvLoader::get('BIFATURA_BASE_URL');
    }

    public static function getInstance()
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * API Token'ı al
     */
    public function getApiToken()
    {
        return $this->apiToken;
    }

    /**
     * API Token'ı ayarla
     */
    public function setApiToken($token)
    {
        $this->apiToken = $token;
    }

    /**
     * Bifatura entegrasyonu aktif mi?
     */
    public function isEnabled()
    {
        return $this->isEnabled && !empty($this->apiToken);
    }

    /**
     * Bifatura entegrasyonunu aktif/pasif yap
     */
    public function setEnabled($enabled)
    {
        $this->isEnabled = (bool) $enabled;
    }

    /**
     * Base URL'i al
     */
    public function getBaseUrl()
    {
        return rtrim($this->baseUrl, '/');
    }

    /**
     * Base URL'i ayarla
     */
    public function setBaseUrl($url)
    {
        $this->baseUrl = $url;
    }

    /**
     * Endpoint URL'ini al
     */
    public function getEndpointUrl($endpointName)
    {
        if (!isset($this->endpoints[$endpointName])) {
            throw new Exception("Endpoint bulunamadı: {$endpointName}");
        }

        return $this->getBaseUrl() . $this->endpoints[$endpointName];
    }

    /**
     * Tüm endpoint'leri al
     */
    public function getAllEndpoints()
    {
        $urls = [];
        foreach ($this->endpoints as $name => $path) {
            $urls[$name] = $this->getBaseUrl() . $path;
        }
        return $urls;
    }

    /**
     * Ayarları al
     */
    public function getSetting($key, $default = null)
    {
        return $this->settings[$key] ?? $default;
    }

    /**
     * Ayar kaydet
     */
    public function setSetting($key, $value)
    {
        $this->settings[$key] = $value;
    }

    /**
     * Tüm ayarları al
     */
    public function getAllSettings()
    {
        return $this->settings;
    }

    /**
     * Otomatik fatura kesimi aktif mi?
     */
    public function isAutoInvoiceEnabled()
    {
        return $this->getSetting('auto_invoice', false) && $this->isEnabled();
    }

    /**
     * Token doğrulama
     */
    public function validateToken($token)
    {
        return $this->apiToken === $token && !empty($token);
    }

    /**
     * GUID token oluştur (kullanım için yardımcı fonksiyon)
     */
    public static function generateGuidToken()
    {
        if (function_exists('com_create_guid')) {
            return trim(com_create_guid(), '{}');
        }

        return sprintf(
            '%04X%04X-%04X-%04X-%04X-%04X%04X%04X',
            mt_rand(0, 65535),
            mt_rand(0, 65535),
            mt_rand(0, 65535),
            mt_rand(16384, 20479),
            mt_rand(32768, 49151),
            mt_rand(0, 65535),
            mt_rand(0, 65535),
            mt_rand(0, 65535)
        );
    }
}

<?php

require_once __DIR__ . '/env.php';

/**
 * VizionPOS Ortam Yapılandırması
 *
 * Kullanım:
 * - Test ortamı için: .env dosyasında APP_ENV=stage
 * - Canlı ortam için: .env dosyasında APP_ENV=production
 */
class VizionPosConfig
{
    private static $instance = null;
    private $environment;
    private $config;

    private function __construct()
    {
        // .env dosyasını yükle
        EnvLoader::load();

        // Aktif ortamı al (varsayılan: stage)
        $this->environment = EnvLoader::get('APP_ENV', 'stage');

        // Ortama göre yapılandırmayı ayarla
        $this->loadConfig();
    }

    /**
     * Singleton instance
     */
    public static function getInstance()
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Ortama göre yapılandırmayı yükle
     */
    private function loadConfig()
    {
        $prefix = strtoupper($this->environment);

        $this->config = [
            'environment' => $this->environment,
            'apiKey' => EnvLoader::get("{$prefix}_API_KEY"),
            'apiSecret' => EnvLoader::get("{$prefix}_API_SECRET"),
            'baseUrl' => EnvLoader::get("{$prefix}_BASE_URL"),
            'threeDSecureEndpoint' => '/external/payments/3d-secure/initialize',
            'callbackUrl' => EnvLoader::get("{$prefix}_CALLBACK_URL"),
        ];
    }

    /**
     * Yapılandırma değeri al
     */
    public function get($key, $default = null)
    {
        return $this->config[$key] ?? $default;
    }

    /**
     * Tüm yapılandırmayı al
     */
    public function all()
    {
        return $this->config;
    }

    /**
     * API Key
     */
    public function getApiKey()
    {
        return $this->config['apiKey'];
    }

    /**
     * API Secret
     */
    public function getApiSecret()
    {
        return $this->config['apiSecret'];
    }

    /**
     * Base URL
     */
    public function getBaseUrl()
    {
        return $this->config['baseUrl'];
    }

    /**
     * 3D Secure tam URL'i al
     */
    public function getThreeDSecureUrl()
    {
        return $this->config['baseUrl'] . $this->config['threeDSecureEndpoint'];
    }

    /**
     * Callback URL
     */
    public function getCallbackUrl()
    {
        return $this->config['callbackUrl'];
    }

    /**
     * Aktif ortam adı
     */
    public function getEnvironment()
    {
        return $this->environment;
    }

    /**
     * Production ortamında mı?
     */
    public function isProduction()
    {
        return $this->environment === 'production';
    }

    /**
     * Stage ortamında mı?
     */
    public function isStage()
    {
        return $this->environment === 'stage';
    }

    /**
     * Yapılandırma array olarak (eski config.php ile uyumluluk)
     */
    public function toArray()
    {
        return $this->config;
    }
}

/**
 * Helper fonksiyon - Hızlı erişim için
 */
function vizyonpos_config($key = null, $default = null)
{
    $config = VizionPosConfig::getInstance();

    if ($key === null) {
        return $config->all();
    }

    return $config->get($key, $default);
}

// Eski config.php ile uyumluluk için array döndür
return VizionPosConfig::getInstance()->toArray();

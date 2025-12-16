<?php

/**
 * Environment Configuration Loader
 * .env dosyasından ortam değişkenlerini yükler
 */
class EnvLoader
{
    private static $loaded = false;
    private static $variables = [];

    /**
     * .env dosyasını yükle
     */
    public static function load($path = null)
    {
        if (self::$loaded) {
            return;
        }

        if ($path === null) {
            $path = __DIR__ . '/.env';
        }

        if (!file_exists($path)) {
            throw new Exception(".env dosyası bulunamadı: {$path}");
        }

        $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

        foreach ($lines as $line) {
            // Yorumları ve boş satırları atla
            if (strpos(trim($line), '#') === 0) {
                continue;
            }

            // KEY=VALUE formatını parse et
            if (strpos($line, '=') !== false) {
                list($name, $value) = explode('=', $line, 2);
                $name = trim($name);
                $value = trim($value);

                // Tırnak işaretlerini temizle
                $value = trim($value, '"\'');

                self::$variables[$name] = $value;

                // $_ENV ve putenv ile de ayarla
                $_ENV[$name] = $value;
                putenv("{$name}={$value}");
            }
        }

        self::$loaded = true;
    }

    /**
     * Ortam değişkeni al
     */
    public static function get($key, $default = null)
    {
        if (!self::$loaded) {
            self::load();
        }

        return self::$variables[$key] ?? $default;
    }

    /**
     * Tüm ortam değişkenlerini al
     */
    public static function all()
    {
        if (!self::$loaded) {
            self::load();
        }

        return self::$variables;
    }
}

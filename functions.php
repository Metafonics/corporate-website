<?php
function safeInput($data, $allow_html = false)
{
    $data = trim($data);

    if (!$allow_html) {
        $data = strip_tags($data);
    }

    $data = htmlspecialchars($data, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');

    return $data;
}



function buildHtmlTable(array $data): string
{
    $html = "<table border='1' cellpadding='10' cellspacing='0' style='border-collapse: collapse; width: 100%;'>";
    $html .= "<tr><th style='background:#f2f2f2'>Alan</th><th style='background:#f2f2f2'>Bilgi</th></tr>";

    foreach ($data as $key => $value) {
        $label = is_numeric($key) ? $key : ucfirst(str_replace('_', ' ', $key));
        $value = htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
        $html .= "<tr><td>{$label}</td><td>{$value}</td></tr>";
    }

    $html .= "</table>";
    return $html;
}

/**
 * Türkçe karakterleri dönüştürüp URL-friendly slug oluşturur
 * @param string $text
 * @return string
 */
function generateSlug($text)
{
    // Türkçe karakterleri İngilizce karşılıklarına çevir
    $turkish = array('Ç', 'ç', 'Ğ', 'ğ', 'ı', 'İ', 'Ö', 'ö', 'Ş', 'ş', 'Ü', 'ü');
    $english = array('C', 'c', 'G', 'g', 'i', 'I', 'O', 'o', 'S', 's', 'U', 'u');
    $text = str_replace($turkish, $english, $text);

    // Küçük harfe çevir
    $text = strtolower($text);

    // Alfanumerik olmayan karakterleri tire ile değiştir
    $text = preg_replace('/[^a-z0-9]+/', '-', $text);

    // Baştaki ve sondaki tireleri kaldır
    $text = trim($text, '-');

    return $text;
}

/**
 * CamelCase anahtarları snake_case'e dönüştürür (Bifatura için)
 * @param array $data
 * @return array
 */
function convertToSnakeCase(array $data): array
{
    $result = [];
    foreach ($data as $key => $value) {
        // camelCase -> snake_case dönüşümü
        $snakeKey = strtolower(preg_replace('/([a-z])([A-Z])/', '$1_$2', $key));

        // Değer de array ise recursive çağır
        if (is_array($value)) {
            $result[$snakeKey] = convertToSnakeCase($value);
        } else {
            $result[$snakeKey] = $value;
        }
    }
    return $result;
}

/**
 * Snake_case anahtarları camelCase'e dönüştürür (Vizyonpay API için)
 * @param array $data
 * @return array
 */
function convertToCamelCase(array $data): array
{
    $result = [];
    foreach ($data as $key => $value) {
        // snake_case -> camelCase dönüşümü
        $camelKey = lcfirst(str_replace('_', '', ucwords($key, '_')));

        // Değer de array ise recursive çağır
        if (is_array($value)) {
            $result[$camelKey] = convertToCamelCase($value);
        } else {
            $result[$camelKey] = $value;
        }
    }
    return $result;
}

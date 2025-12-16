<?php

/**
 * VizionPOS Configuration
 *
 * Bu dosya artık yeni ortam yapılandırma sistemini kullanıyor.
 * Ortam değiştirmek için config/.env dosyasındaki APP_ENV değerini değiştirin:
 * - APP_ENV=stage (Test ortamı)
 * - APP_ENV=production (Canlı ortam)
 */

require_once __DIR__ . '/vizyonpos.php';

// Eski kullanım ile uyumluluk için array döndür
return VizionPosConfig::getInstance()->toArray();

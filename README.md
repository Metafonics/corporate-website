# Metafonics - AI Voice Assistants Platform

## Proje Açıklaması

Metafonics, farklı sektörler için yapay zeka destekli sesli asistan çözümleri sunan yenilikçi bir web platformudur. Şirket, eczane, okul, turizm, otomotiv, emlak gibi çeşitli sektörlere özel AI asistanları tasarlayarak işletmelerin operasyonlarını kolaylaştırır ve müşteri deneyimini iyileştirir.

Platform, kullanıcıların asistan paketlerini seçip satın almasını, kredi yüklemesini ve faturalandırma süreçlerini hızlı ve güvenli bir şekilde yönetmesini sağlayan entegre bir e-ticaret altyapısına sahiptir. Böylece işletmeler, ihtiyaçlarına uygun AI çözümlerine zahmetsizce erişebilir ve dijital dönüşümlerini hızlandırabilir.

## Özellikler

- **Sektörel Asistanlar**: Farklı sektörlere özel AI sesli asistan çözümleri
- **Paket Satışı**: Kredi paketleri ile asistan kullanım hakları
- **Müşteri Yönetimi**: Kayıt, giriş ve profil yönetimi
- **Ödeme Entegrasyonları**: VizyonPOS ile güvenli ödeme
- **Faturalandırma**: Bifatura entegrasyonu ile e-fatura/e-arşiv
- **API Endpoints**: Üçüncü parti entegrasyonlar için REST API

## Teknoloji Altyapısı

- **Backend**: PHP 7.4+
- **Veritabanı**: MySQL 8.0+
- **Frontend**: HTML5, CSS3, JavaScript (jQuery, Bootstrap)
- **Ödeme**: VizyonPOS API
- **Faturalandırma**: Bifatura API
- **E-posta**: PHPMailer

## Kurulum ve Çalıştırma

### Gereksinimler

- PHP 7.4 veya üzeri
- MySQL 8.0+
- Apache/Nginx web sunucusu

### Adımlar

1. **Projeyi Klonlayın**
   ```bash
   git clone https://github.com/Metafonics/corporate-website.git
   cd metafonics
   ```

2. **Veritabanını Kurun**
   - MySQL'de `metafoni_metafonics` veritabanını oluşturun
   - Gerekli tabloları import edin (SQL dosyası varsa)

3. **Konfigürasyon**
   - `config/database.php` dosyasında veritabanı bilgilerini güncelleyin
   - `config/.env` dosyasında ortam ayarlarını yapın:
     - `APP_ENV=production` (canlı) veya `APP_ENV=stage` (test)
   - Ödeme API anahtarlarını `.env` dosyasında ayarlayın

5. **Web Sunucusu Yapılandırması**
   - Document root'u proje kök dizinine ayarlayın
   - `.htaccess` dosyasının rewrite kurallarını etkinleştirin
   - SSL sertifikası yükleyin (üretim ortamı için)

6. **İzinler**
   ```bash
   chmod 755 -R .
   chown www-data:www-data -R .
   ```

7. **Çalıştırma**
   - Tarayıcıda https://metafonics.com/ adresine gidin

## Proje Yapısı

```
metafonics/
├── api/                    # API endpoints
│   ├── bifatura/          # Bifatura entegrasyonu
│   ├── invoiceLinkUpdate/ # Fatura link güncelleme
│   ├── orderCargoUpdate/  # Kargo güncelleme
│   ├── orders/            # Sipariş işlemleri
│   └── ...
├── assets/                # Statik dosyalar
│   ├── css/               # Stil dosyaları
│   ├── js/                # JavaScript dosyaları
│   ├── images/            # Görseller
│   └── fonts/             # Fontlar
├── components/            # Yeniden kullanılabilir bileşenler
├── config/                # Yapılandırma dosyaları
├── controllers/           # İş mantığı kontrolcüleri
├── layout/                # Sayfa şablonları
├── pages/                 # Sayfa dosyaları
├── payment/               # Ödeme işlemleri
├── phpmailer/             # E-posta gönderme
├── sections/              # Sayfa bölümleri
├── functions.php          # Yardımcı fonksiyonlar
├── data.php              # Ürün verileri
└── index.php             # Ana giriş noktası
```

## API Dokümantasyonu

### Genel Bilgiler

- Tüm API endpoint'leri `POST` metodunu kullanır
- Response format: JSON
- Authentication: Token-based (bazı endpoint'ler için)

### Müşteri API'leri

#### Müşteri Bilgilerini Getir
- **Endpoint**: `POST /api/get-customer.php`
- **Parametreler**:
  - `phone` (string): Telefon numarası
- **Response**:
  ```json
  {
    "success": true,
    "data": {
      "customer_number": "12345",
      "first_name": "Ahmet",
      "last_name": "Yılmaz",
      "email": "ahmet@example.com",
      "phone": "05551234567",
      "credit_balance": 100
    },
    "missing_fields": [],
    "has_missing_fields": false
  }
  ```

#### Kredi Paketlerini Getir
- **Endpoint**: `POST /api/get-credit-packages.php`
- **Parametreler**: Yok
- **Response**:
  ```json
  {
    "success": true,
    "data": [
      {
        "id": 1,
        "name": "Temel Paket",
        "price": 100.00,
        "credit_amount": 100,
        "vat": 20,
        "price_with_vat": 120.00
      }
    ]
  }
  ```

#### Fatura Adreslerini Yönet
- **GET**: `POST /api/get-billing-addresses.php`
- **SAVE**: `POST /api/save-billing-address.php`
- **DELETE**: `POST /api/delete-billing-address.php`

### Bifatura Entegrasyonu

#### Siparişleri Getir
- **Endpoint**: `POST /api/bifatura/orders.php`
- **Headers**:
  - `token`: API token
- **Body**:
  ```json
  {
    "orderStatusId": 1,
    "startDateTime": "01.11.2019 00:00:00",
    "endDateTime": "01.12.2019 23:59:59"
  }
  ```

#### Diğer Bifatura Endpoint'leri
- `POST /api/bifatura/order-status.php` - Sipariş durumu
- `POST /api/bifatura/payment-methods.php` - Ödeme yöntemleri
- `POST /api/bifatura/common.php` - Genel işlemler

### Ödeme İşlemleri

- **Başlatma**: `/payment/payment.php`
- **Callback**: `/payment/callback.php`
- **Başarı**: `/payment/payment_success.php`
- **Başarısızlık**: `/payment/payment_fail.php`

## URL Yapısı

- Ana Sayfa: `/`
- Sektörel Asistanlar: `/sektorel-asistanlar`
- Asistan Detayı: `/sektorel-asistanlar/{slug}`
- Paket Detayı: `/paket/{slug}`
- Ödeme: `/odeme/{id}`
- Giriş: `/giris`
- Kayıt: `/musteri-kayit`
- Profil: `/musteri-odeme`
- Kredi Alım: `/kontor-al`

## Geliştirme Notları

### Kod Standartları

- PHP kodları PSR-12 standardına uygun olmalıdır
- HTML/CSS/JS dosyaları düzenli indent ile yazılmalıdır
- Türkçe karakter kullanımı için UTF-8 encoding kullanılmalıdır

### Veritabanı Tabloları

Ana tablolar:
- `customers`: Müşteri bilgileri
- `orders`: Siparişler
- `invoices`: Faturalar
- `invoice_items`: Fatura kalemleri
- `credit_packages`: Kredi paketleri
- `packages`: Ürün paketleri

### Güvenlik

- Tüm kullanıcı girişleri `safeInput()` fonksiyonu ile filtrelenmelidir
- SQL injection'a karşı PDO prepared statements kullanılmalıdır
- API endpoint'leri için token doğrulaması yapılmalıdır
- Ödeme işlemleri SSL üzerinden gerçekleştirilmelidir

### Hata Yönetimi

- Try-catch blokları ile hatalar yakalanmalıdır
- Hata logları `/logs/` dizinine yazılmalıdır
- Kullanıcı dostu hata mesajları gösterilmelidir

## Katkıda Bulunma

1. Fork edin
2. Feature branch oluşturun (`git checkout -b feature/yeni-ozellik`)
3. Değişikliklerinizi commit edin (`git commit -am 'Yeni özellik eklendi'`)
4. Branch'inizi push edin (`git push origin feature/yeni-ozellik`)
5. Pull Request oluşturun

### Geliştirme Ortamı

- Local development için XAMPP/WAMP kullanın
- Test ortamı için staging server kurun
- Tüm değişiklikler test edildikten sonra production'a alın

## Destek

Sorularınız için:
- E-posta: infot@metafonics.com

## Lisans

Bu proje [Lisans Türü] lisansı altında lisanslanmıştır.
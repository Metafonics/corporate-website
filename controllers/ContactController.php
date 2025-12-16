<?php
require __DIR__ . '/../services/MailService.php';
require __DIR__ . '/../functions.php';

use App\Services\MailService;

$mail = new MailService();

// Formdan gelen veriler
$formData = [
    'İsim'    => safeInput($_POST['name'] ?? ''),
    'Email'   => safeInput($_POST['email'] ?? ''),
    'Telefon' => safeInput($_POST['phone'] ?? ''),
    'Konu'  => safeInput($_POST['subject'] ?? ''),
    'Mesaj'   => safeInput($_POST['message'] ?? '')
];

// HTML tabloya dönüştür
$body = buildHtmlTable($formData);

// Mail gönder
$response = $mail->sendMail([
    'subject'   => 'Yeni İletişim Formu Mesajı',
    'body'      => $body
]);

echo json_encode($response);

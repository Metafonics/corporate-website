<?php
header('Content-Type: application/json; charset=utf-8');
$response = [
    'OrderStatus' => [
        ['Id' => 1, 'Value' => 'Ödeme Onaylandı'],
        ['Id' => 2, 'Value' => 'Sipariş Tamamlandı']
    ]
];
http_response_code(200);
echo json_encode($response, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);

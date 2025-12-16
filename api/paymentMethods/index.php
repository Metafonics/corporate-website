<?php
header('Content-Type: application/json; charset=utf-8');
$response = [
    'PaymentMethods' => [
        ['Id' => 1, 'Value' => 'Kredi Kartı'],
        ['Id' => 2, 'Value' => 'Banka Transferi']
    ]
];
http_response_code(200);
echo json_encode($response, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);

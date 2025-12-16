<?php
header('Content-Type: application/json; charset=utf-8');
$response = ['success' => true, 'message' => 'OK'];
http_response_code(200);
echo json_encode($response, JSON_UNESCAPED_UNICODE);

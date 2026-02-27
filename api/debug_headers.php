<?php
include_once '../config/cors.php';
echo json_encode([
    'getallheaders' => function_exists('getallheaders') ? getallheaders() : 'N/A',
    '$_SERVER' => $_SERVER
]);

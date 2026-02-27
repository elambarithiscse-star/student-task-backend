<?php
$url = "http://localhost/student_task_tracker_backend/api/debug_headers.php";
$opts = [
    'http' => [
        'method' => 'GET',
        'header' => "User-Id: 1\r\n"
    ]
];
$context = stream_context_create($opts);
echo file_get_contents($url, false, $context);

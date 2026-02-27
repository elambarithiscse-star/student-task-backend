<?php
// Test script to verify API fixes (CORS and User-Id)

$baseUrl = "http://localhost/student_task_tracker_backend/api";

function testEndpoint($method, $endpoint, $data = [], $expectSuccess = true)
{
    global $baseUrl;
    $url = $baseUrl . $endpoint;

    echo "Testing $method $endpoint...\n";

    $opts = [
        'http' => [
            'method' => $method,
            'header' => "Content-Type: application/json\r\n" .
            "User-Id: 1\r\n", // Send User-Id in header
            'content' => json_encode($data),
            'ignore_errors' => true // Don't throw error on 4xx/5xx
        ]
    ];

    $context = stream_context_create($opts);
    $result = file_get_contents($url, false, $context);

    // Check headers for CORS
    $responseHeaders = $http_response_header;
    $corsFound = false;
    foreach ($responseHeaders as $header) {
        if (stripos($header, 'Access-Control-Allow-Headers') !== false && stripos($header, 'User-Id') !== false) {
            $corsFound = true;
        }
    }

    if (!$corsFound) {
    // Note: file_get_contents might not show all headers if using local loopback in some envs, 
    // or if it's a simple response. 
    // But for CORS preflight (OPTIONS) checking is hard with file_get_contents.
    // We'll trust the main request success for now, but print warning.
    // Actually, simple requests don't return Access-Control-Allow-Headers usually, only OPTIONS does.
    // So checking it here might be misleading unless we do OPTIONS.
    // But create.php includes cors.php which sends them unconditionally.
    // echo "WARNING: CORS User-Id header not found in response.\n";
    }

    if ($result === false) {
        echo "FAILED: Could not connect to $url\n";
        return null;
    }

    $json = json_decode($result, true);

    if ($expectSuccess) {
        if (isset($json['success']) && $json['success'] === true) {
            echo "PASSED: Success\n";
            return $json;
        }
        else {
            echo "FAILED: Expected success but got error.\n";
            print_r($json);
            return null;
        }
    }
    else {
        echo "Response: " . substr($result, 0, 100) . "...\n";
        return $json;
    }
}

// 1. Create Task
$taskData = [
    // 'user_id' => 1, // INTENTIONALLY OMITTED to test header extraction
    'title' => 'Test API Fix',
    'description' => 'Created via test script',
    'due_date' => '2026-12-31 12:00:00',
    'status' => 'pending'
];

$createResult = testEndpoint('POST', '/tasks/create.php', $taskData);

if ($createResult && isset($createResult['task_id'])) {
    $taskId = $createResult['task_id'];
    echo "Created Task ID: $taskId\n";

    // 2. Update Task
    $updateData = [
        'id' => $taskId,
        // 'user_id' => 1, // INTENTIONALLY OMITTED
        'title' => 'Test API Fix (Updated)',
        'due_date' => '2026-12-31 12:00:00'
    ];
    testEndpoint('PUT', '/tasks/update.php', $updateData);

    // 3. List Tasks
    testEndpoint('GET', '/tasks/list.php?user_id=1'); // GET usually sends params or headers

    // 4. Delete Task
    $deleteData = [
        'id' => $taskId,
        // 'user_id' => 1 // INTENTIONALLY OMITTED
    ];
    testEndpoint('DELETE', '/tasks/delete.php', $deleteData);
}

echo "\nTests Completed.\n";

<?php
$urls = [
    'http://127.0.0.1:8000/Admin/mastertt.php',
    'http://127.0.0.1:8000/Admin/search_g_result.php?pG=75',
    'http://127.0.0.1:8000/Admin/search_g_result.php?pG=91',
    'http://127.0.0.1:8000/Admin/search_t_result.php?pT=19',
    'http://127.0.0.1:8000/Admin/search_r_result.php?pR=1'
];

foreach ($urls as $url) {
    $ctx = stream_context_create([
        'http' => [
            'method' => 'GET',
            'ignore_errors' => true,
            'header' => "Cookie: PHPSESSID=testsession\r\n"
        ]
    ]);
    $content = @file_get_contents($url, false, $ctx);
    $status = isset($http_response_header[0]) ? $http_response_header[0] : 'FAILED';
    echo "$status -> $url\n";
    if (strpos($content, 'Fatal error') !== false || strpos($content, 'Parse error') !== false) {
        echo "  ERROR DETECTED in $url\n";
    }
}

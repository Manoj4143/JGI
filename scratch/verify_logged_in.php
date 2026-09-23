<?php
// Test login via index.php
$login_url = 'http://127.0.0.1:8000/index.php';
$post_data = http_build_query([
    'username' => 'admin',
    'password' => 'a',
    'cmdSubmit' => 'Login'
]);

$ctx = stream_context_create([
    'http' => [
        'method' => 'POST',
        'header' => "Content-Type: application/x-www-form-urlencoded\r\nContent-Length: " . strlen($post_data) . "\r\n",
        'content' => $post_data,
        'follow_location' => 0,
        'ignore_errors' => true
    ]
]);

$resp = file_get_contents($login_url, false, $ctx);
$cookie = '';
foreach ($http_response_header as $hdr) {
    if (stripos($hdr, 'Set-Cookie:') === 0) {
        preg_match('/PHPSESSID=[^;]+/', $hdr, $m);
        if (!empty($m[0])) {
            $cookie = $m[0];
            break;
        }
    }
}
echo "Session Cookie: $cookie\n";

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
            'header' => "Cookie: $cookie\r\n"
        ]
    ]);
    $html = @file_get_contents($url, false, $ctx);
    $status = isset($http_response_header[0]) ? $http_response_header[0] : 'FAILED';
    echo "\n$status -> $url\n";
    if (strpos($html, 'Fatal error') !== false || strpos($html, 'Parse error') !== false) {
        echo "  [ERROR DETECTED]\n";
    } else {
        echo "  [SUCCESS] Length: " . strlen($html) . " bytes\n";
        if (preg_match('/charset=([^"\'\s>]+)/i', $html, $cm)) {
            echo "  Charset: {$cm[1]}\n";
        }
    }
}

<?php
$login_url = 'http://127.0.0.1:8000/index.php';
$post_data = http_build_query(['username' => 'admin', 'pass' => 'a', 'cmdSubmit' => 'Login']);
$ctx = stream_context_create([
    'http' => [
        'method' => 'POST',
        'header' => "Content-Type: application/x-www-form-urlencoded\r\nContent-Length: " . strlen($post_data) . "\r\n",
        'content' => $post_data,
        'follow_location' => 0
    ]
]);
file_get_contents($login_url, false, $ctx);
$cookie = '';
foreach ($http_response_header as $hdr) {
    if (stripos($hdr, 'Set-Cookie:') === 0) {
        preg_match('/PHPSESSID=[^;]+/', $hdr, $m);
        $cookie = $m[0];
    }
}
echo "Cookie: $cookie\n";

$ctx2 = stream_context_create([
    'http' => ['method' => 'GET', 'ignore_errors' => true, 'header' => "Cookie: $cookie\r\n"]
]);
$html = file_get_contents('http://127.0.0.1:8000/Admin/mastertt.php', false, $ctx2);
echo "Headers for mastertt.php:\n";
print_r($http_response_header);
echo "\nFirst 200 chars of HTML:\n";
echo substr($html, 0, 200) . "\n";

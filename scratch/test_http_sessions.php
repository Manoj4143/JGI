<?php
// Test Admin login
function do_login($username, $password, $role) {
    $cookie_file = tempnam(sys_get_temp_dir(), 'sched_cookie_');
    $ch = curl_init('http://127.0.0.1:8000/Admin/index.php');
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query([
        'submit' => '1',
        'username' => $username,
        'pass' => $password,
        'role_type' => $role
    ]));
    curl_setopt($ch, CURLOPT_COOKIEJAR, $cookie_file);
    curl_setopt($ch, CURLOPT_COOKIEFILE, $cookie_file);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, false);
    $res = curl_exec($ch);
    $redirect = curl_getinfo($ch, CURLINFO_REDIRECT_URL);
    curl_close($ch);
    return ['cookie' => $cookie_file, 'redirect' => $redirect];
}

function fetch_page($cookie_file, $url) {
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_COOKIEFILE, $cookie_file);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, false);
    $content = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $redirect = curl_getinfo($ch, CURLINFO_REDIRECT_URL);
    curl_close($ch);
    return ['code' => $http_code, 'redirect' => $redirect, 'body' => $content];
}

echo "Testing Admin Login (admin / a):\n";
$admin = do_login('admin', 'a', 'admin');
echo "Admin redirect: {$admin['redirect']}\n";
$admin_fac_details = fetch_page($admin['cookie'], 'http://127.0.0.1:8000/Admin/faculty_details.php');
echo "Admin access to faculty_details.php: HTTP {$admin_fac_details['code']} (Contains 'Admin Confidential': " . (strpos($admin_fac_details['body'], 'Admin Confidential') !== false ? "YES ✅" : "NO ❌") . ")\n";
$admin_users = fetch_page($admin['cookie'], 'http://127.0.0.1:8000/Admin/userlist.php');
echo "Admin access to userlist.php: HTTP {$admin_users['code']} (Contains 'System User Accounts': " . (strpos($admin_users['body'], 'System User Accounts') !== false ? "YES ✅" : "NO ❌") . ")\n";

echo "\nTesting Counselor Login (counselor / counselor123):\n";
$counselor = do_login('counselor', 'counselor123', 'counsellor');
echo "Counselor redirect: {$counselor['redirect']}\n";
$counselor_fac_details = fetch_page($counselor['cookie'], 'http://127.0.0.1:8000/Admin/faculty_details.php');
echo "Counselor access to faculty_details.php: " . (strpos($counselor_fac_details['body'], 'Access Restricted') !== false ? "BLOCKED with Access Restricted ✅" : "NOT BLOCKED ❌") . "\n";
$counselor_admin_page = fetch_page($counselor['cookie'], 'http://127.0.0.1:8000/Admin/admin.php');
echo "Counselor access to admin.php redirect: {$counselor_admin_page['redirect']}\n";

echo "\nTesting Faculty Login:\n";
// Check if a faculty user exists
require_once __DIR__ . '/../includes/DBConnection.php';
$r = mysqli_query($conn, "SELECT username, userpass, teacher_id FROM user WHERE role = 'Faculty' AND teacher_id IS NOT NULL LIMIT 1");
if ($row = mysqli_fetch_assoc($r)) {
    echo "Using faculty account: {$row['username']}\n";
    $fac = do_login($row['username'], $row['userpass'], 'faculty');
    echo "Faculty redirect: {$fac['redirect']}\n";
    $fac_details = fetch_page($fac['cookie'], 'http://127.0.0.1:8000/Admin/faculty_details.php');
    echo "Faculty access to faculty_details.php: " . (strpos($fac_details['body'], 'Access Restricted') !== false ? "BLOCKED with Access Restricted ✅" : "NOT BLOCKED ❌") . "\n";
    $fac_userlist = fetch_page($fac['cookie'], 'http://127.0.0.1:8000/Admin/userlist.php');
    echo "Faculty access to userlist.php redirect: {$fac_userlist['redirect']}\n";
    $fac_timetable = fetch_page($fac['cookie'], 'http://127.0.0.1:8000/Admin/search_t_result.php?pT=' . $row['teacher_id']);
    echo "Faculty access to their own timetable: HTTP {$fac_timetable['code']} ✅\n";
} else {
    echo "No faculty user with teacher_id found.\n";
}

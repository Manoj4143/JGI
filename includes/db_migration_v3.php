<?php
require_once __DIR__ . '/DBConnection.php';

echo "=== STARTING DATABASE MIGRATION V3 ===\n";

// 1. Add role and teacher_id to user table if not present
$cols_res = mysqli_query($conn, "SHOW COLUMNS FROM user");
$existing_cols = [];
while ($c = mysqli_fetch_assoc($cols_res)) {
    $existing_cols[] = $c['Field'];
}

if (!in_array('role', $existing_cols)) {
    mysqli_query($conn, "ALTER TABLE user ADD COLUMN role VARCHAR(30) NOT NULL DEFAULT 'Admin' AFTER userpass");
    echo "Added 'role' column to user table.\n";
} else {
    echo "'role' column already exists in user table.\n";
}

if (!in_array('teacher_id', $existing_cols)) {
    mysqli_query($conn, "ALTER TABLE user ADD COLUMN teacher_id INT NULL DEFAULT NULL AFTER email");
    echo "Added 'teacher_id' column to user table.\n";
} else {
    echo "'teacher_id' column already exists in user table.\n";
}

// Update existing users with default roles
mysqli_query($conn, "UPDATE user SET role = 'Admin' WHERE username = 'admin' OR dept_id = 4");
mysqli_query($conn, "UPDATE user SET role = 'Faculty' WHERE role IS NULL OR role = ''");

// If any user matches a teacher name in profile, link teacher_id
$prof_res = mysqli_query($conn, "SELECT teacher_id, teacher_name FROM profile");
while ($p = mysqli_fetch_assoc($prof_res)) {
    $tname = mysqli_real_escape_string($conn, $p['teacher_name']);
    $tid = intval($p['teacher_id']);
    mysqli_query($conn, "UPDATE user SET teacher_id = $tid, role = 'Faculty' WHERE username = '$tname'");
}

// 2. Create faculty_details table if not exists
$create_fd = "CREATE TABLE IF NOT EXISTS faculty_details (
    teacher_id INT PRIMARY KEY,
    address TEXT NULL,
    speciality VARCHAR(255) NULL DEFAULT '',
    subjects_taught TEXT NULL,
    tenth_details VARCHAR(255) NULL DEFAULT '',
    twelfth_details VARCHAR(255) NULL DEFAULT '',
    degree_details VARCHAR(255) NULL DEFAULT '',
    experience_years VARCHAR(50) NULL DEFAULT '',
    salary DECIMAL(12, 2) NULL DEFAULT 0.00,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (teacher_id) REFERENCES profile(teacher_id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";

mysqli_query($conn, $create_fd) or die("Error creating faculty_details: " . mysqli_error($conn));
echo "faculty_details table verified/created.\n";

// Populate faculty_details for any teachers missing an entry
$teachers_res = mysqli_query($conn, "SELECT teacher_id, teacher_name, acad_rank, dept_id FROM profile");
while ($t = mysqli_fetch_assoc($teachers_res)) {
    $tid = intval($t['teacher_id']);
    $chk = mysqli_query($conn, "SELECT teacher_id FROM faculty_details WHERE teacher_id = $tid");
    if (mysqli_num_rows($chk) === 0) {
        // Collect their subjects
        $subs = [];
        $sres = mysqli_query($conn, "SELECT DISTINCT sub_code, sub_name FROM subjects WHERE instructor = $tid");
        while ($s = mysqli_fetch_assoc($sres)) {
            $subs[] = $s['sub_code'] . " (" . $s['sub_name'] . ")";
        }
        $sub_str = mysqli_real_escape_string($conn, implode(", ", $subs));
        
        $spec = "Computer Science & Engineering";
        if ($t['dept_id'] == 7) $spec = "Artificial Intelligence & Machine Learning";
        if ($t['dept_id'] == 8) $spec = "Information Science & Engineering";
        
        $salary = 65000.00;
        if (stripos($t['acad_rank'], 'HOD') !== false || stripos($t['acad_rank'], 'Professor') !== false) {
            $salary = 125000.00;
        } elseif (stripos($t['acad_rank'], 'Associate') !== false) {
            $salary = 95000.00;
        }
        
        $deg = "M.Tech in CSE / Ph.D";
        $exp = "7 Years";
        $tenth = "State Board - 88%";
        $twelfth = "PUC / CBSE - 86%";
        $addr = "Campus Faculty Quarters, Bangalore, Karnataka";
        
        mysqli_query($conn, "INSERT INTO faculty_details (teacher_id, address, speciality, subjects_taught, tenth_details, twelfth_details, degree_details, experience_years, salary) 
                             VALUES ($tid, '$addr', '$spec', '$sub_str', '$tenth', '$twelfth', '$deg', '$exp', $salary)");
    }
}
echo "faculty_details records seeded/synchronized with profile.\n";

// 3. Create timing_requests table if not exists
$create_tr = "CREATE TABLE IF NOT EXISTS timing_requests (
    request_id INT AUTO_INCREMENT PRIMARY KEY,
    teacher_id INT NOT NULL,
    day_id INT NOT NULL,
    slot_id INT NOT NULL,
    sub_id INT NULL DEFAULT NULL,
    pref_day_id INT NULL DEFAULT NULL,
    pref_slot_id INT NULL DEFAULT NULL,
    request_type VARCHAR(50) DEFAULT 'unavailability',
    reason TEXT NOT NULL,
    status VARCHAR(30) DEFAULT 'Pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    admin_notes TEXT NULL,
    admin_remark TEXT NULL,
    FOREIGN KEY (teacher_id) REFERENCES profile(teacher_id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";

mysqli_query($conn, $create_tr) or die("Error creating timing_requests: " . mysqli_error($conn));
echo "timing_requests table verified/created.\n";

// 4. Ensure a Counselor user exists for testing and use
$chk_counselor = mysqli_query($conn, "SELECT user_id FROM user WHERE username = 'counselor'");
if (mysqli_num_rows($chk_counselor) === 0) {
    mysqli_query($conn, "INSERT INTO user (username, email, userpass, dept_id, year, role) VALUES ('counselor', 'counselor@campus.edu', 'counselor123', 7, 0, 'Counselor')");
    echo "Created sample Counselor account: counselor / counselor123\n";
}

echo "=== MIGRATION V3 COMPLETE ===\n";

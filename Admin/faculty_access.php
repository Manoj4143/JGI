<?php
if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}
include("../includes/session.php");
require("../includes/dbconnection.php");

// Restrict strictly to Admin (dept_id == 4 or role == 'admin')
$currentRole = $_SESSION['is']['role'] ?? '';
$currentDept = strval($_SESSION['is']['dept_id'] ?? '');
if ($currentDept !== '4' && strtolower($currentRole) !== 'admin') {
    die("<div style='font-family: sans-serif; padding: 40px; text-align: center;'><h2>Access Restricted</h2><p>This menu is accessible only by System Administrators.</p><p><a href='logout.php'>Return to Login</a></p></div>");
}

$msg = '';
$error = '';

// Handle Access Grant / Password Update / Revoke
if (isset($_POST['action'])) {
    $action = $_POST['action'];

    if ($action === 'grant_access') {
        $teacher_id = intval($_POST['teacher_id'] ?? 0);
        $email = trim($_POST['email'] ?? '');
        $password = trim($_POST['password'] ?? '');

        if ($teacher_id <= 0 || empty($email) || empty($password)) {
            $error = 'All fields (Faculty Member, Email, and Password) are required.';
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $error = 'Please provide a valid email address (e.g. professor@campus.edu).';
        } else {
            $emailClean = mysqli_real_escape_string($conn, $email);
            $passClean = mysqli_real_escape_string($conn, $password);

            // Fetch teacher details
            $tQuery = mysqli_query($conn, "SELECT * FROM profile WHERE teacher_id = $teacher_id");
            if ($tQuery && $tRow = mysqli_fetch_assoc($tQuery)) {
                $teacher_dept = intval($tRow['dept_id'] ?? 1);
                $teacher_name = $tRow['teacher_name'];

                // Update profile record with email and password
                mysqli_query($conn, "UPDATE profile SET email = '$emailClean', userpass = '$passClean' WHERE teacher_id = $teacher_id");

                // Check if user account already exists for this teacher or email
                $uQuery = mysqli_query($conn, "SELECT user_id FROM user WHERE teacher_id = $teacher_id OR email = '$emailClean' OR username = '$emailClean'");
                if ($uQuery && mysqli_num_rows($uQuery) > 0) {
                    $uRow = mysqli_fetch_assoc($uQuery);
                    $targetUid = intval($uRow['user_id']);
                    mysqli_query($conn, "UPDATE user SET username = '$emailClean', email = '$emailClean', userpass = '$passClean', teacher_id = $teacher_id, dept_id = $teacher_dept WHERE user_id = $targetUid");
                    $msg = "Portal access credentials successfully updated for <strong>" . htmlspecialchars($teacher_name) . "</strong> (" . htmlspecialchars($emailClean) . ").";
                } else {
                    mysqli_query($conn, "INSERT INTO user (username, email, userpass, dept_id, teacher_id, year) VALUES ('$emailClean', '$emailClean', '$passClean', $teacher_dept, $teacher_id, 0)");
                    $msg = "Faculty login access successfully granted for <strong>" . htmlspecialchars($teacher_name) . "</strong> (" . htmlspecialchars($emailClean) . ").";
                }
            } else {
                $error = 'Selected faculty profile could not be found.';
            }
        }
    } elseif ($action === 'revoke_access') {
        $teacher_id = intval($_POST['teacher_id'] ?? 0);
        if ($teacher_id > 0) {
            mysqli_query($conn, "DELETE FROM user WHERE teacher_id = $teacher_id");
            mysqli_query($conn, "UPDATE profile SET userpass = '' WHERE teacher_id = $teacher_id");
            $msg = "Login credentials revoked successfully.";
        }
    }
}

// Fetch all faculty and check their access status
$facultyList = [];
$facRes = mysqli_query($conn, "SELECT p.*, d.department FROM profile p LEFT JOIN dept d ON p.dept_id = d.dept_id ORDER BY p.teacher_name ASC");
while ($f = mysqli_fetch_assoc($facRes)) {
    $tid = intval($f['teacher_id']);
    // Check if account in user table
    $uRes = mysqli_query($conn, "SELECT user_id, username, email, userpass FROM user WHERE teacher_id = $tid OR (email IS NOT NULL AND email != '' AND email = '" . mysqli_real_escape_string($conn, $f['email'] ?? '') . "') LIMIT 1");
    $uRow = ($uRes && mysqli_num_rows($uRes) > 0) ? mysqli_fetch_assoc($uRes) : null;
    $f['user_account'] = $uRow;
    $facultyList[] = $f;
}
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
<link href="../includes/style_prac2.css" rel="stylesheet" type="text/css" />
<title>Admin &mdash; Grant Faculty Access Credentials</title>
<style>
  .access-grid-container {
    display: grid;
    grid-template-columns: 360px 1fr;
    gap: 24px;
    margin-top: 20px;
    align-items: start;
  }
  @media (max-width: 900px) {
    .access-grid-container {
      grid-template-columns: 1fr;
    }
  }
  .card-box {
    background: #ffffff;
    border: 1px solid #e2e8f0;
    border-radius: 12px;
    padding: 24px;
    box-shadow: 0 4px 14px rgba(0,0,0,0.03);
  }
  .form-group-item {
    margin-bottom: 16px;
  }
  .form-group-item label {
    display: block;
    font-size: 12px;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.03em;
    color: #475569;
    margin-bottom: 6px;
  }
  .form-input-styled {
    width: 100%;
    padding: 10px 14px;
    border: 1px solid #cbd5e1;
    border-radius: 8px;
    font-size: 13.5px;
    font-family: inherit;
    box-sizing: border-box;
    outline: none;
    transition: all 0.2s;
  }
  .form-input-styled:focus {
    border-color: #006c49;
    box-shadow: 0 0 0 3px rgba(0, 108, 73, 0.15);
  }
  .btn-grant-access {
    width: 100%;
    background: #003b1b;
    color: #ffffff;
    border: none;
    padding: 12px 18px;
    border-radius: 8px;
    font-size: 13.5px;
    font-weight: 700;
    cursor: pointer;
    transition: all 0.2s;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
    box-shadow: 0 4px 12px rgba(0, 59, 27, 0.25);
  }
  .btn-grant-access:hover {
    background: #14532d;
    box-shadow: 0 6px 16px rgba(0, 59, 27, 0.35);
  }
  .roster-table {
    width: 100%;
    border-collapse: collapse;
    font-size: 13px;
  }
  .roster-table th {
    background: #f8fafc;
    color: #475569;
    text-align: left;
    padding: 12px 14px;
    border-bottom: 2px solid #e2e8f0;
    font-weight: 700;
    font-size: 11.5px;
    text-transform: uppercase;
    letter-spacing: 0.04em;
  }
  .roster-table td {
    padding: 12px 14px;
    border-bottom: 1px solid #f1f5f9;
    color: #1e293b;
    vertical-align: middle;
  }
  .roster-table tr:hover td {
    background: #f8fbfa;
  }
  .status-badge-active {
    display: inline-flex;
    align-items: center;
    gap: 5px;
    background: #ecfdf5;
    color: #047857;
    border: 1px solid #a7f3d0;
    padding: 3px 8px;
    border-radius: 9999px;
    font-size: 11px;
    font-weight: 700;
  }
  .status-badge-inactive {
    display: inline-flex;
    align-items: center;
    gap: 5px;
    background: #f1f5f9;
    color: #64748b;
    border: 1px solid #cbd5e1;
    padding: 3px 8px;
    border-radius: 9999px;
    font-size: 11px;
    font-weight: 600;
  }
</style>
</head>
<body>
<div id="container">
<?php include("../includes/shedulo_shell.php"); ?>
  <div id="content">
    <div id="left" style="width: 100%;">
      
      <!-- Banner -->
      <div class="tt-hero-banner" style="margin-top: 10px;">
        <div>
          <div style="display: flex; align-items: center; gap: 10px; margin-bottom: 4px; flex-wrap: wrap;">
            <h2 style="margin: 0; color: #0f172a; font-size: 21px; font-weight: 800; letter-spacing: -0.2px;">Faculty Access Management</h2>
            <span class="badge-pill badge-green">Admin Only</span>
            <span class="badge-pill badge-blue">Shedulo Identity Service</span>
          </div>
          <div style="font-size: 12.5px; color: #64748b; font-weight: 500;">
            Issue and manage portal login credentials (email &amp; password) for teachers to access their modern timetable dashboard.
          </div>
        </div>
        <div style="display: flex; gap: 8px;">
          <a href="admin.php" style="background: #f1f5f9; color: #334155; padding: 8px 14px; border-radius: 8px; text-decoration: none; font-size: 12.5px; font-weight: 600; border: 1px solid #cbd5e1;">&larr; Admin Home</a>
        </div>
      </div>

      <!-- Alerts -->
      <?php if (!empty($msg)): ?>
        <div style="background: #ecfdf5; border: 1px solid #a7f3d0; color: #065f46; padding: 12px 18px; border-radius: 8px; margin: 16px 0; font-size: 13.5px; display: flex; align-items: center; gap: 10px;">
          <span style="font-size: 18px;">✅</span>
          <span><?php echo $msg; ?></span>
        </div>
      <?php endif; ?>

      <?php if (!empty($error)): ?>
        <div style="background: #fef2f2; border: 1px solid #fecaca; color: #991b1b; padding: 12px 18px; border-radius: 8px; margin: 16px 0; font-size: 13.5px; display: flex; align-items: center; gap: 10px;">
          <span style="font-size: 18px;">⚠️</span>
          <span><?php echo htmlspecialchars($error); ?></span>
        </div>
      <?php endif; ?>

      <!-- Main Layout -->
      <div class="access-grid-container">

        <!-- Form Card: Grant / Update Access -->
        <div class="card-box">
          <div style="display: flex; align-items: center; gap: 8px; margin-bottom: 16px;">
            <span class="material-symbols-outlined" style="color: #006c49; font-size: 22px;">vpn_key</span>
            <h3 style="margin: 0; font-size: 16px; color: #0f172a; font-weight: 700;">Grant / Update Access</h3>
          </div>
          <p style="font-size: 12px; color: #64748b; margin-bottom: 20px; line-height: 1.5;">
            Select a faculty member from the directory and configure their login credentials. Once granted, they can sign in from the main portal.
          </p>

          <form action="faculty_access.php" method="post" id="grantForm">
            <input type="hidden" name="action" value="grant_access" />

            <div class="form-group-item">
              <label for="teacher_id">Select Faculty Member</label>
              <select name="teacher_id" id="teacher_id" class="form-input-styled" required onchange="populateFacultyData(this)">
                <option value="">-- Choose Faculty --</option>
                <?php foreach ($facultyList as $fac): ?>
                  <option value="<?php echo $fac['teacher_id']; ?>" 
                          data-email="<?php echo htmlspecialchars($fac['user_account']['email'] ?? ($fac['email'] ?? '')); ?>"
                          data-dept="<?php echo htmlspecialchars($fac['department'] ?? ''); ?>"
                          data-hasaccount="<?php echo !empty($fac['user_account']) ? '1' : '0'; ?>">
                    <?php echo htmlspecialchars($fac['teacher_name']); ?> (<?php echo htmlspecialchars($fac['department'] ?? 'General'); ?>)
                  </option>
                <?php endforeach; ?>
              </select>
            </div>

            <div class="form-group-item">
              <label for="email">Faculty Email Address (Username)</label>
              <input type="email" name="email" id="email" class="form-input-styled" placeholder="e.g. sriraksha@campus.edu" required />
              <div style="font-size: 11px; color: #64748b; margin-top: 4px;">Faculty will use this email address to log in.</div>
            </div>

            <div class="form-group-item">
              <label for="password">Account Password</label>
              <div style="position: relative;">
                <input type="text" name="password" id="password" class="form-input-styled" placeholder="Enter secure password" required style="padding-right: 70px;" />
                <button type="button" onclick="generateRandomPass()" style="position: absolute; right: 6px; top: 6px; background: #e2e8f0; border: none; border-radius: 6px; padding: 4px 8px; font-size: 11px; font-weight: 700; color: #334155; cursor: pointer;">Auto</button>
              </div>
            </div>

            <div style="margin-top: 24px;">
              <button type="submit" class="btn-grant-access">
                <span class="material-symbols-outlined" style="font-size: 18px;">security</span>
                <span>Save &amp; Grant Access</span>
              </button>
            </div>
          </form>
        </div>

        <!-- Roster Table Card -->
        <div class="card-box">
          <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 16px; flex-wrap: wrap; gap: 10px;">
            <div style="display: flex; align-items: center; gap: 8px;">
              <span class="material-symbols-outlined" style="color: #0284c7; font-size: 22px;">badge</span>
              <h3 style="margin: 0; font-size: 16px; color: #0f172a; font-weight: 700;">Faculty Access Directory (<?php echo count($facultyList); ?>)</h3>
            </div>
            <input type="text" id="rosterSearch" placeholder="Filter faculty..." onkeyup="filterRoster()" style="padding: 6px 12px; border: 1px solid #cbd5e1; border-radius: 6px; font-size: 12px; outline: none;" />
          </div>

          <div style="overflow-x: auto;">
            <table class="roster-table" id="rosterTable">
              <thead>
                <tr>
                  <th>Faculty Name</th>
                  <th>Department</th>
                  <th>Login Email</th>
                  <th>Status</th>
                  <th style="text-align: right;">Action</th>
                </tr>
              </thead>
              <tbody>
                <?php foreach ($facultyList as $fac): 
                  $hasAcc = !empty($fac['user_account']);
                  $loginEmail = $hasAcc ? ($fac['user_account']['email'] ?? $fac['user_account']['username']) : ($fac['email'] ?? '&mdash;');
                  $currentPass = $hasAcc ? ($fac['user_account']['userpass'] ?? '') : '';
                ?>
                  <tr class="roster-row">
                    <td>
                      <strong style="color: #0f172a;"><?php echo htmlspecialchars($fac['teacher_name']); ?></strong>
                      <div style="font-size: 11px; color: #64748b;"><?php echo htmlspecialchars($fac['academic_rank'] ?? $fac['acad_rank'] ?? 'Faculty'); ?></div>
                    </td>
                    <td><?php echo htmlspecialchars($fac['department'] ?? 'General'); ?></td>
                    <td>
                      <?php if ($hasAcc): ?>
                        <span style="font-family: monospace; color: #0369a1; font-weight: 600;"><?php echo htmlspecialchars($loginEmail); ?></span>
                      <?php else: ?>
                        <span style="color: #94a3b8; font-style: italic;">No login issued</span>
                      <?php endif; ?>
                    </td>
                    <td>
                      <?php if ($hasAcc): ?>
                        <span class="status-badge-active"><span style="width: 6px; height: 6px; border-radius: 50%; background: #059669;"></span> Active</span>
                      <?php else: ?>
                        <span class="status-badge-inactive">Inactive</span>
                      <?php endif; ?>
                    </td>
                    <td style="text-align: right;">
                      <div style="display: inline-flex; gap: 6px; align-items: center;">
                        <button type="button" 
                                onclick="quickEditFaculty(<?php echo $fac['teacher_id']; ?>, '<?php echo addslashes($fac['user_account']['email'] ?? ($fac['email'] ?? '')); ?>', '<?php echo addslashes($currentPass); ?>')" 
                                style="background: #f1f5f9; border: 1px solid #cbd5e1; border-radius: 6px; padding: 4px 10px; font-size: 11.5px; font-weight: 600; color: #1e293b; cursor: pointer;">
                          <?php echo $hasAcc ? 'Edit' : 'Grant'; ?>
                        </button>

                        <?php if ($hasAcc): ?>
                          <form action="faculty_access.php" method="post" style="display: inline;" onsubmit="return confirm('Revoke login access for this faculty member?');">
                            <input type="hidden" name="action" value="revoke_access" />
                            <input type="hidden" name="teacher_id" value="<?php echo $fac['teacher_id']; ?>" />
                            <button type="submit" style="background: #fef2f2; border: 1px solid #fecaca; border-radius: 6px; padding: 4px 8px; font-size: 11.5px; font-weight: 600; color: #dc2626; cursor: pointer;" title="Revoke access">
                              Revoke
                            </button>
                          </form>
                        <?php endif; ?>
                      </div>
                    </td>
                  </tr>
                <?php endforeach; ?>
              </tbody>
            </table>
          </div>
        </div>

      </div>

    </div>
  </div>
</div>

<script>
function populateFacultyData(selectElem) {
  var opt = selectElem.options[selectElem.selectedIndex];
  if (opt && opt.value) {
    var emailField = document.getElementById('email');
    var existingEmail = opt.getAttribute('data-email');
    if (existingEmail && existingEmail.trim() !== '') {
      emailField.value = existingEmail;
    } else {
      // Auto-suggest email based on name
      var rawName = opt.text.split('(')[0].trim().toLowerCase().replace(/[^a-z0-9]/g, '');
      emailField.value = rawName + '@campus.edu';
    }
  }
}

function quickEditFaculty(tid, email, pass) {
  var sel = document.getElementById('teacher_id');
  sel.value = tid;
  document.getElementById('email').value = email || '';
  document.getElementById('password').value = pass || '';
  if (!email) {
    populateFacultyData(sel);
  }
  window.scrollTo({ top: 100, behavior: 'smooth' });
}

function generateRandomPass() {
  var chars = 'abcdefghijkmnpqrstuvwxyzABCDEFGHJKLMNPQRSTUVWXYZ23456789!@#$%';
  var pass = '';
  for (var i = 0; i < 8; i++) {
    pass += chars.charAt(Math.floor(Math.random() * chars.length));
  }
  document.getElementById('password').value = pass;
}

function filterRoster() {
  var filter = document.getElementById('rosterSearch').value.toLowerCase();
  var rows = document.querySelectorAll('.roster-row');
  rows.forEach(function(r) {
    var text = r.innerText.toLowerCase();
    r.style.display = text.indexOf(filter) > -1 ? '' : 'none';
  });
}
</script>
</body>
</html>

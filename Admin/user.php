<?php 
session_start();
include("../includes/session.php");
require("../includes/dbconnection.php");

// RBAC: Only Admin can access User Management
$curr_role = $_SESSION['is']['role'] ?? (($_SESSION['is']['dept'] == 4) ? 'Admin' : 'Faculty');
if ($curr_role !== 'Admin') {
    header("Location: mastertt.php?access_denied=1");
    exit;
}

$flaguser = '';
$flagpass = '';
$flagrole = '';
$flagdept = '';
$error = '';

if (isset($_POST['cmdSubmit'])) { 		
    $user = trim($_POST['txtuser'] ?? '');
    $pass = trim($_POST['txtpass'] ?? '');
    $role = trim($_POST['user_role'] ?? 'Faculty');
    $dept = intval($_POST['pdept'] ?? 0);
    $teacher_id = intval($_POST['teacher_id'] ?? 0);

    if ($user == "") { $flaguser = 'Username / Email is required.'; }	
    if ($pass == "") { $flagpass = 'Password is required.'; }	
    if (!in_array($role, ['Admin', 'Faculty', 'Counselor'])) { $flagrole = 'Invalid role specified.'; }
    if ($dept <= 0) { $flagdept = 'Department is required.'; }	
    if ($role === 'Faculty' && $teacher_id <= 0) {
        $error = 'Please select which faculty member this user account corresponds to.';
    }

    if (empty($flaguser) && empty($flagpass) && empty($flagdept) && empty($error)) {
        $user_clean = mysqli_real_escape_string($conn, $user);
        $pass_clean = mysqli_real_escape_string($conn, $pass);
        $role_clean = mysqli_real_escape_string($conn, $role);

        $chk = mysqli_query($conn, "SELECT user_id FROM user WHERE username = '$user_clean'");
        if ($chk && mysqli_num_rows($chk) > 0) {
            $error = 'A user account with this username already exists.';
        } else {
            $tid_val = ($role === 'Faculty' && $teacher_id > 0) ? $teacher_id : "NULL";
            $query = "INSERT INTO user (username, email, userpass, role, teacher_id, dept_id, year) 
                      VALUES ('$user_clean', '$user_clean', '$pass_clean', '$role_clean', $tid_val, $dept, 0)";
            if (mysqli_query($conn, $query)) {
                header("Location: userlist.php?added=1");
                exit;
            } else {
                $error = 'Database error: ' . mysqli_error($conn);
            }
        }
    }
}
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
<link href="../includes/style_prac2.css" rel="stylesheet" type="text/css" />
<title>Scheduling System - Add System User</title>
<script type="text/javascript">
function handleRoleChange() {
    var role = document.getElementById('user_role').value;
    var facultyGroup = document.getElementById('faculty_picker_group');
    if (role === 'Faculty') {
        facultyGroup.style.display = 'block';
    } else {
        facultyGroup.style.display = 'none';
    }
}
</script>
</head>
<body>
<div id="container">
<?php include("../includes/shedulo_shell.php"); ?>
  <div id="content">
	<div id="left">
		<div class="tt-hero-banner" style="margin-top: 10px;">
			<div>
				<div style="display: flex; align-items: center; gap: 10px; margin-bottom: 4px;">
					<h2 style="margin: 0; color: #0f172a; font-size: 20px; font-weight: 800; text-align: left; letter-spacing: -0.2px;">Add System User Account</h2>
					<span class="badge-pill badge-blue">Identity &amp; Access</span>
				</div>
				<div style="font-size: 12.5px; color: #64748b; font-weight: 500;">
					Provision a new user account with distinct Role Level and Departmental assignment.
				</div>
			</div>
			<div>
				<a href="userlist.php" style="background: #2563eb; color: #ffffff; padding: 8px 14px; border-radius: 8px; text-decoration: none; font-size: 12.5px; font-weight: 600; display: inline-flex; align-items: center; gap: 6px;">📋 User Roster</a>
			</div>
		</div>

		<?php if (!empty($error)): ?>
		<div style="background: #fef2f2; border: 1px solid #fecaca; color: #991b1b; padding: 12px 18px; border-radius: 8px; margin: 16px 0; font-size: 13.5px;">
			⚠️ <?php echo htmlspecialchars($error); ?>
		</div>
		<?php endif; ?>

		<form action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>" name="form1" method="post">
		  <div class="form-panel">
			<h3 style="margin-top: 0; margin-bottom: 15px; color: #1e3a8a; font-size: 16px;">User Account Credentials</h3>
			
			<div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px; margin-bottom: 16px;">
				<div>
					<label style="display: block; font-weight: 700; font-size: 13px; color: #334155; margin-bottom: 6px;">Username / Login ID: *</label>
					<input type="text" name="txtuser" id="txtuser" value="<?php echo htmlspecialchars($_POST['txtuser'] ?? ''); ?>" required style="width: 100%; box-sizing: border-box; padding: 10px 14px; font-size: 13px; border-radius: 8px; border: 1.5px solid #cbd5e1;" placeholder="e.g. kmanoj or counselor" />
					<?php if (!empty($flaguser)): ?><span style="color: #ef4444; font-size: 11px;"><?php echo $flaguser; ?></span><?php endif; ?>
				</div>

				<div>
					<label style="display: block; font-weight: 700; font-size: 13px; color: #334155; margin-bottom: 6px;">Password: *</label>
					<input type="password" name="txtpass" id="txtpass" required style="width: 100%; box-sizing: border-box; padding: 10px 14px; font-size: 13px; border-radius: 8px; border: 1.5px solid #cbd5e1;" placeholder="Enter password" />
					<?php if (!empty($flagpass)): ?><span style="color: #ef4444; font-size: 11px;"><?php echo $flagpass; ?></span><?php endif; ?>
				</div>
			</div>

			<div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px; margin-bottom: 16px;">
				<div>
					<label style="display: block; font-weight: 700; font-size: 13px; color: #334155; margin-bottom: 6px;">Select Role: *</label>
					<select name="user_role" id="user_role" required onchange="handleRoleChange();" style="width: 100%; box-sizing: border-box; padding: 10px 14px; font-size: 13px; border-radius: 8px; border: 1.5px solid #cbd5e1; background: white; font-weight: 600;">
						<option value="Admin" <?php echo (($_POST['user_role'] ?? '') === 'Admin') ? 'selected' : ''; ?>>🛡️ Admin (System Administrator - Full Access)</option>
						<option value="Faculty" <?php echo (($_POST['user_role'] ?? 'Faculty') === 'Faculty') ? 'selected' : ''; ?>>👨‍🏫 Faculty (Personal Timetable & Profile Only)</option>
						<option value="Counselor" <?php echo (($_POST['user_role'] ?? '') === 'Counselor') ? 'selected' : ''; ?>>🧑‍💼 Counselor (Student Timetables & Advisory Only)</option>
					</select>
				</div>

				<div>
					<label style="display: block; font-weight: 700; font-size: 13px; color: #334155; margin-bottom: 6px;">Department: *</label>
					<select name="pdept" id="pdept" required style="width: 100%; box-sizing: border-box; padding: 10px 14px; font-size: 13px; border-radius: 8px; border: 1.5px solid #cbd5e1; background: white;">
						<option value="">-- Select Department --</option>
						<?php 
						$dRes = mysqli_query($conn, "SELECT * FROM dept ORDER BY dept_id ASC");
						while ($dRow = mysqli_fetch_assoc($dRes)) {
							$sel = (($_POST['pdept'] ?? '') == $dRow['dept_id']) ? 'selected' : '';
							echo '<option value="' . $dRow['dept_id'] . '" ' . $sel . '>' . htmlspecialchars($dRow['department']) . ' (' . htmlspecialchars($dRow['dept_name']) . ')</option>';
						}
						?>
					</select>
					<?php if (!empty($flagdept)): ?><span style="color: #ef4444; font-size: 11px;"><?php echo $flagdept; ?></span><?php endif; ?>
				</div>
			</div>

			<div id="faculty_picker_group" style="margin-bottom: 20px; <?php echo (($_POST['user_role'] ?? 'Faculty') === 'Faculty') ? 'display: block;' : 'display: none;'; ?> background: #f8fafc; border: 1.5px solid #e2e8f0; border-radius: 8px; padding: 14px;">
				<label style="display: block; font-weight: 700; font-size: 13px; color: #1e3a8a; margin-bottom: 6px;">
					🔗 Link to Faculty Member Profile: *
				</label>
				<div style="font-size: 12px; color: #64748b; margin-bottom: 8px;">
					Select the faculty profile from the teacher roster to link this login account directly to their teaching schedule.
				</div>
				<select name="teacher_id" id="teacher_id" style="width: 100%; box-sizing: border-box; padding: 10px 14px; font-size: 13px; border-radius: 8px; border: 1.5px solid #cbd5e1; background: white;">
					<option value="0">-- Select Faculty Member --</option>
					<?php 
					$fRes = mysqli_query($conn, "SELECT teacher_id, teacher_name, dept_id FROM profile ORDER BY teacher_name ASC");
					while ($fRow = mysqli_fetch_assoc($fRes)) {
						$sel = (($_POST['teacher_id'] ?? '') == $fRow['teacher_id']) ? 'selected' : '';
						echo '<option value="' . $fRow['teacher_id'] . '" ' . $sel . '>' . htmlspecialchars($fRow['teacher_name']) . ' (ID: ' . $fRow['teacher_id'] . ')</option>';
					}
					?>
				</select>
			</div>

			<div style="display: flex; gap: 10px; margin-top: 25px;">
				<button type="submit" name="cmdSubmit" style="background: #2563eb; color: #ffffff; border: none; padding: 10px 20px; border-radius: 8px; font-size: 13px; font-weight: 700; cursor: pointer; display: inline-flex; align-items: center; gap: 6px;">
					<span>➕ Create User Account</span>
				</button>
				<a href="userlist.php" style="background: #f1f5f9; color: #475569; padding: 10px 18px; border-radius: 8px; text-decoration: none; font-size: 13px; font-weight: 600; border: 1px solid #cbd5e1;">Cancel</a>
			</div>
		  </div>
		</form>
	</div>
  </div>
</div>
</body>
</html>
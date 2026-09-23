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
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
<link href="../includes/style_prac2.css" rel="stylesheet" type="text/css" />
<title>Scheduling System - System Users Directory</title>
</head>
<body>
<div id="container">
<?php include("../includes/shedulo_shell.php"); ?>
  <div id="content">
	<div id="left">
		<div class="tt-hero-banner" style="margin-top: 10px;">
			<div>
				<div style="display: flex; align-items: center; gap: 10px; margin-bottom: 4px;">
					<h2 style="margin: 0; color: #0f172a; font-size: 20px; font-weight: 800; text-align: left; letter-spacing: -0.2px;">System User Accounts</h2>
					<span class="badge-pill badge-green">Security &amp; RBAC Directory</span>
				</div>
				<div style="font-size: 12.5px; color: #64748b; font-weight: 500;">
					Manage role-based access control (Admin, Faculty, Counselor) and faculty account linkages.
				</div>
			</div>
			<div>
				<a href="user.php" style="background: #2563eb; color: #ffffff; padding: 8px 14px; border-radius: 8px; text-decoration: none; font-size: 12.5px; font-weight: 600; display: inline-flex; align-items: center; gap: 6px;">+ Add User</a>
			</div>
		</div>

		<?php if (isset($_GET['added'])): ?>
		<div style="background: #ecfdf5; border: 1px solid #a7f3d0; color: #065f46; padding: 12px 18px; border-radius: 8px; margin: 16px 0; font-size: 13.5px;">
			✅ User account has been successfully created.
		</div>
		<?php endif; ?>
		<?php if (isset($_GET['updated'])): ?>
		<div style="background: #eff6ff; border: 1px solid #bfdbfe; color: #1e40af; padding: 12px 18px; border-radius: 8px; margin: 16px 0; font-size: 13.5px;">
			✅ User account has been successfully updated.
		</div>
		<?php endif; ?>

		<div class="form-panel" style="padding: 0; overflow: hidden;">
			<table class="tt-grid" style="margin: 0;">
				<thead>
					<tr>
						<th style="width: 60px; text-align: center;">ID</th>
						<th>Username / Login</th>
						<th style="width: 140px; text-align: center;">System Role</th>
						<th>Department</th>
						<th>Linked Faculty Profile</th>
						<th style="width: 130px; text-align: center;">Actions</th>
					</tr>
				</thead>
				<tbody>
				<?php 
				$query = "SELECT u.*, d.department, d.dept_name, p.teacher_name 
				          FROM user u 
				          LEFT JOIN dept d ON u.dept_id = d.dept_id 
				          LEFT JOIN profile p ON u.teacher_id = p.teacher_id 
				          ORDER BY u.user_id ASC";
				$result = mysqli_query($conn, $query);
				if (!$result || mysqli_num_rows($result) == 0) {
					echo '<tr><td colspan="6" style="text-align: center; padding: 30px; color: #64748b;">No user accounts found.</td></tr>';
				} else {
					while ($row = mysqli_fetch_assoc($result)) {
						$user_id = $row['user_id'];
						$username = $row['username'];
						$deptName = !empty($row['department']) ? $row['department'] : 'General';
						$userRole = !empty($row['role']) ? $row['role'] : (($row['dept_id'] == 4) ? 'Admin' : 'Faculty');
						$teacherName = !empty($row['teacher_name']) ? $row['teacher_name'] : '-';
				?>
					<tr>
						<td style="text-align: center; font-weight: 700; color: #64748b;"><?php echo $user_id; ?></td>
						<td style="font-weight: 700; color: #1e293b;">
							<div style="font-size: 13.5px;"><?php echo htmlspecialchars($username); ?></div>
						</td>
						<td style="text-align: center;">
							<?php if ($userRole === 'Admin'): ?>
								<span class="badge-pill badge-red" style="font-size: 11px; background: #fee2e2; color: #991b1b; border: 1px solid #fca5a5;">🛡️ Admin</span>
							<?php elseif ($userRole === 'Counselor'): ?>
								<span class="badge-pill" style="font-size: 11px; background: #f3e8ff; color: #6b21a8; border: 1px solid #d8b4fe; padding: 3px 8px; border-radius: 9999px; font-weight: 700;">🧑‍💼 Counselor</span>
							<?php else: ?>
								<span class="badge-pill badge-blue" style="font-size: 11px; background: #e0f2fe; color: #075985; border: 1px solid #7dd3fc;">👨‍🏫 Faculty</span>
							<?php endif; ?>
						</td>
						<td style="color: #334155; font-weight: 600;">
							<?php echo htmlspecialchars($deptName); ?>
							<?php if (!empty($row['dept_name']) && $row['dept_name'] !== $deptName): ?>
								<div style="font-size: 11px; color: #64748b; font-weight: 400;"><?php echo htmlspecialchars($row['dept_name']); ?></div>
							<?php endif; ?>
						</td>
						<td>
							<?php if ($teacherName !== '-'): ?>
								<div style="font-weight: 600; color: #1e3a8a;">👨‍🏫 <?php echo htmlspecialchars($teacherName); ?></div>
								<div style="font-size: 11px; color: #64748b;">ID: <?php echo $row['teacher_id']; ?></div>
							<?php else: ?>
								<span style="color: #94a3b8; font-size: 12px;">Not Linked</span>
							<?php endif; ?>
						</td>
						<td style="text-align: center;">
							<div style="display: inline-flex; gap: 8px; align-items: center;">
								<a href="user-edit.php?user_id=<?php echo $user_id; ?>" style="background: #eff6ff; color: #2563eb; border: 1px solid #bfdbfe; padding: 4px 10px; border-radius: 6px; text-decoration: none; font-size: 11.5px; font-weight: 700;">Edit</a>
								<a href="user-del.php?user_id=<?php echo $user_id; ?>" onclick="return confirm('Are you sure you want to delete user #<?php echo $user_id; ?> (<?php echo htmlspecialchars(addslashes($username)); ?>)?');" style="background: #fef2f2; color: #dc2626; border: 1px solid #fecaca; padding: 4px 10px; border-radius: 6px; text-decoration: none; font-size: 11.5px; font-weight: 700;">Delete</a>
							</div>
						</td>
					</tr>
				<?php 
					}
				}
				?>
				</tbody>
			</table>
		</div>
	</div>
  </div>
</div>
</body>
</html>
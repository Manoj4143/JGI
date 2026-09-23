<?php 
session_start();
include("../includes/session.php");
require("../includes/dbconnection.php");

$currentRole = $_SESSION['is']['role'] ?? (($_SESSION['is']['dept_id'] == 4) ? 'Admin' : 'Faculty');
$teacher_id = intval($_SESSION['is']['teacher_id'] ?? 0);

// If teacher_id is not yet in session, look it up by username
if ($teacher_id <= 0) {
    $uname = mysqli_real_escape_string($conn, $_SESSION['is']['username']);
    $uRes = mysqli_query($conn, "SELECT teacher_id FROM user WHERE username = '$uname' OR email = '$uname'");
    if ($uRes && $uRow = mysqli_fetch_assoc($uRes)) {
        $teacher_id = intval($uRow['teacher_id'] ?? 0);
        $_SESSION['is']['teacher_id'] = $teacher_id;
    }
}

if ($teacher_id <= 0) {
    $uname = mysqli_real_escape_string($conn, $_SESSION['is']['username']);
    $pRes = mysqli_query($conn, "SELECT teacher_id FROM profile WHERE teacher_name LIKE '%$uname%' OR email = '$uname' LIMIT 1");
    if ($pRes && $pRow = mysqli_fetch_assoc($pRes)) {
        $teacher_id = intval($pRow['teacher_id']);
        $_SESSION['is']['teacher_id'] = $teacher_id;
    }
}

$prof = null;
if ($teacher_id > 0) {
    $q = mysqli_query($conn, "SELECT p.*, d.department, d.dept_name, fd.* 
                             FROM profile p 
                             LEFT JOIN dept d ON p.dept_id = d.dept_id 
                             LEFT JOIN faculty_details fd ON p.teacher_id = fd.teacher_id 
                             WHERE p.teacher_id = $teacher_id");
    if ($q && mysqli_num_rows($q) > 0) {
        $prof = mysqli_fetch_assoc($q);
    }
}
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
<link href="../includes/style_prac2.css" rel="stylesheet" type="text/css" />
<title>Scheduling System - Faculty Profile</title>
</head>
<body>
<div id="container">
<?php include("../includes/shedulo_shell.php"); ?>
  <div id="content">
	<div id="left">
		<div class="tt-hero-banner" style="margin-top: 10px;">
			<div>
				<div style="display: flex; align-items: center; gap: 10px; margin-bottom: 4px;">
					<h2 style="margin: 0; color: #0f172a; font-size: 20px; font-weight: 800; text-align: left; letter-spacing: -0.2px;">My Faculty Profile</h2>
					<span class="badge-pill badge-blue">Verified Academic Faculty</span>
				</div>
				<div style="font-size: 12.5px; color: #64748b; font-weight: 500;">
					Personal academic details, assigned courses, and timetable status.
				</div>
			</div>
			<div>
				<?php if ($teacher_id > 0): ?>
				<a href="search_t_result.php?pT=<?php echo $teacher_id; ?>" style="background: #2563eb; color: #ffffff; padding: 8px 14px; border-radius: 8px; text-decoration: none; font-size: 12.5px; font-weight: 600; display: inline-flex; align-items: center; gap: 6px;">📅 View My Timetable</a>
				<?php endif; ?>
			</div>
		</div>

		<?php if ($prof): ?>
		<div class="form-panel" style="margin-top: 15px;">
			<div style="display: flex; align-items: center; gap: 16px; padding-bottom: 18px; border-bottom: 1px solid #e2e8f0; margin-bottom: 18px;">
				<div style="width: 56px; height: 56px; border-radius: 16px; background: #e0f2fe; color: #0284c7; display: flex; align-items: center; justify-content: center; font-size: 24px; font-weight: 800;">
					<?php echo strtoupper(substr($prof['teacher_name'], 0, 1)); ?>
				</div>
				<div>
					<h3 style="margin: 0 0 4px 0; color: #0f172a; font-size: 18px; font-weight: 800;"><?php echo htmlspecialchars($prof['teacher_name']); ?></h3>
					<div style="font-size: 13px; color: #64748b; font-weight: 500;">
						<?php echo htmlspecialchars($prof['acad_rank'] ?? 'Faculty Member'); ?> &bull; Department of <?php echo htmlspecialchars($prof['dept_name'] ?? $prof['department'] ?? 'Engineering'); ?>
					</div>
				</div>
			</div>

			<div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
				<div style="background: #f8fafc; padding: 16px; border-radius: 10px; border: 1px solid #e2e8f0;">
					<h4 style="margin: 0 0 12px 0; color: #1e3a8a; font-size: 14px; font-weight: 700;">Academic Qualifications</h4>
					<div style="margin-bottom: 10px;">
						<span style="font-size: 11.5px; color: #64748b; font-weight: 600; display: block;">Degree Credentials:</span>
						<span style="font-size: 13.5px; font-weight: 700; color: #1e293b;"><?php echo htmlspecialchars($prof['degree_details'] ?? 'B.Tech / M.Tech'); ?></span>
					</div>
					<div style="margin-bottom: 10px;">
						<span style="font-size: 11.5px; color: #64748b; font-weight: 600; display: block;">Domain Speciality:</span>
						<span style="font-size: 13px; color: #334155; font-weight: 600;"><?php echo htmlspecialchars($prof['speciality'] ?? 'Computer Science'); ?></span>
					</div>
					<div>
						<span style="font-size: 11.5px; color: #64748b; font-weight: 600; display: block;">Experience:</span>
						<span style="font-size: 13px; color: #334155; font-weight: 600;"><?php echo htmlspecialchars($prof['experience_years'] ?? '5+ Years'); ?></span>
					</div>
				</div>

				<div style="background: #f8fafc; padding: 16px; border-radius: 10px; border: 1px solid #e2e8f0;">
					<h4 style="margin: 0 0 12px 0; color: #1e3a8a; font-size: 14px; font-weight: 700;">Teaching &amp; Timetable Information</h4>
					<div style="margin-bottom: 10px;">
						<span style="font-size: 11.5px; color: #64748b; font-weight: 600; display: block;">Assigned Subjects:</span>
						<span style="font-size: 13px; color: #1e293b; font-weight: 600;"><?php echo htmlspecialchars($prof['subjects_taught'] ?? 'Core Departmental Subjects'); ?></span>
					</div>
					<div style="margin-bottom: 10px;">
						<span style="font-size: 11.5px; color: #64748b; font-weight: 600; display: block;">Email Address:</span>
						<span style="font-size: 13px; color: #334155; font-weight: 600;"><?php echo htmlspecialchars($prof['email'] ?? 'Not set'); ?></span>
					</div>
					<div>
						<span style="font-size: 11.5px; color: #64748b; font-weight: 600; display: block;">Contact Address:</span>
						<span style="font-size: 12.5px; color: #475569;"><?php echo htmlspecialchars($prof['address'] ?? 'Campus Faculty Quarters'); ?></span>
					</div>
				</div>
			</div>

			<div style="margin-top: 20px; padding: 14px; background: #ecfdf5; border: 1px solid #a7f3d0; border-radius: 8px; display: flex; justify-content: space-between; align-items: center;">
				<div>
					<div style="font-size: 13px; font-weight: 700; color: #065f46;">Need to request a change in your class timing?</div>
					<div style="font-size: 11.5px; color: #047857;">Submit an official timing change request with reasons directly to the Administrator.</div>
				</div>
				<a href="request_timing.php" style="background: #059669; color: #ffffff; padding: 8px 14px; border-radius: 6px; font-size: 12px; font-weight: 700; text-decoration: none;">📝 Submit Timing Request</a>
			</div>
		</div>
		<?php else: ?>
		<div class="form-panel" style="text-align: center; padding: 40px;">
			<h3>Profile Not Linked</h3>
			<p style="color: #64748b; font-size: 13px;">Your user account is not currently linked to a faculty profile. Please contact the administrator.</p>
		</div>
		<?php endif; ?>
	</div>
  </div>
</div>
</body>
</html>

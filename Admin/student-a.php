<?php 
session_start();
include("../includes/session.php");
require("../includes/dbconnection.php");

$flagcourse = '';
$flagdept = '';
$error = '';

if (isset($_POST['cmdSubmit'])) { 		
    $course = trim($_POST['txtcourse'] ?? '');
    $major = trim($_POST['txtmajor'] ?? '');
    $dept_id = intval($_POST['pdept'] ?? 0);

    if ($course == "") { $flagcourse = 'Course year & section is required.'; }
    if ($dept_id <= 0) { $flagdept = 'Department is required.'; }

    if (empty($flagcourse) && empty($flagdept)) {
        $course_clean = mysqli_real_escape_string($conn, $course);
        $major_clean = mysqli_real_escape_string($conn, $major);

        $query = "INSERT INTO course (course_yrSec, major, dept_id) VALUES ('$course_clean', '$major_clean', $dept_id)";
        if (mysqli_query($conn, $query)) {
            header("Location: student-list-a.php?added=1");
            exit;
        } else {
            $error = 'Database error: ' . mysqli_error($conn);
        }
    }
}
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
<link href="../includes/style_prac2.css" rel="stylesheet" type="text/css" />
<title>Scheduling System - Add Course / Batch</title>
</head>
<body>
<div id="container">
<?php include("../includes/shedulo_shell.php"); ?>
  <div id="content">
	<div id="left">
		<div class="tt-hero-banner" style="margin-top: 10px;">
			<div>
				<div style="display: flex; align-items: center; gap: 10px; margin-bottom: 4px;">
					<h2 style="margin: 0; color: #0f172a; font-size: 20px; font-weight: 800; text-align: left; letter-spacing: -0.2px;">Add Student Course / Batch</h2>
					<span class="badge-pill badge-blue">Cohort Allocation</span>
				</div>
				<div style="font-size: 12.5px; color: #64748b; font-weight: 500;">
					Create student batches, semester sections, and branch cohorts for timetable distribution.
				</div>
			</div>
			<div>
				<a href="student-list-a.php" style="background: #2563eb; color: #ffffff; padding: 8px 14px; border-radius: 8px; text-decoration: none; font-size: 12.5px; font-weight: 600; display: inline-flex; align-items: center; gap: 6px;">📋 Course Directory</a>
			</div>
		</div>

		<?php if (!empty($error)): ?>
		<div style="background: #fef2f2; border: 1px solid #fecaca; color: #991b1b; padding: 12px 18px; border-radius: 8px; margin: 16px 0; font-size: 13.5px;">
			⚠️ <?php echo htmlspecialchars($error); ?>
		</div>
		<?php endif; ?>

		<form action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>" name="form1" method="post">
		  <div class="form-panel">
			<h3 style="margin-top: 0; margin-bottom: 15px; color: #1e3a8a; font-size: 16px;">Course &amp; Cohort Details</h3>
			
			<div style="margin-bottom: 16px;">
				<label style="display: block; font-weight: 700; font-size: 13px; color: #334155; margin-bottom: 6px;">Course Year &amp; Section: *</label>
				<input type="text" name="txtcourse" id="txtcourse" value="<?php echo htmlspecialchars($_POST['txtcourse'] ?? ''); ?>" required style="width: 100%; padding: 10px 14px; font-size: 13px; border-radius: 8px; border: 1.5px solid #cbd5e1;" placeholder="e.g. BSCS 3-A or AIML 5-B" />
				<?php if (!empty($flagcourse)): ?><span style="color: #ef4444; font-size: 11px;"><?php echo $flagcourse; ?></span><?php endif; ?>
			</div>

			<div style="margin-bottom: 16px;">
				<label style="display: block; font-weight: 700; font-size: 13px; color: #334155; margin-bottom: 6px;">Major / Specialization:</label>
				<input type="text" name="txtmajor" id="txtmajor" value="<?php echo htmlspecialchars($_POST['txtmajor'] ?? ''); ?>" style="width: 100%; padding: 10px 14px; font-size: 13px; border-radius: 8px; border: 1.5px solid #cbd5e1;" placeholder="e.g. Artificial Intelligence, Software Engineering" />
			</div>

			<div style="margin-bottom: 20px;">
				<label style="display: block; font-weight: 700; font-size: 13px; color: #334155; margin-bottom: 6px;">Academic Department / Branch: *</label>
				<select name="pdept" id="pdept" required style="width: 100%; padding: 10px 14px; font-size: 13px; border-radius: 8px; border: 1.5px solid #cbd5e1; background: white;">
					<option value="">-- Select Department --</option>
					<?php 
					$dRes = mysqli_query($conn, "SELECT * FROM dept ORDER BY department ASC");
					while ($dRow = mysqli_fetch_assoc($dRes)) {
						echo '<option value="' . $dRow['dept_id'] . '">' . htmlspecialchars($dRow['department']) . '</option>';
					}
					?>
				</select>
				<?php if (!empty($flagdept)): ?><span style="color: #ef4444; font-size: 11px;"><?php echo $flagdept; ?></span><?php endif; ?>
			</div>

			<div style="display: flex; gap: 10px; margin-top: 25px;">
				<button type="submit" name="cmdSubmit" style="background: #2563eb; color: #ffffff; border: none; padding: 10px 20px; border-radius: 8px; font-size: 13px; font-weight: 700; cursor: pointer; display: inline-flex; align-items: center; gap: 6px;">
					<span>Save Course / Batch</span>
				</button>
				<a href="student-list-a.php" style="background: #f1f5f9; color: #475569; border: 1px solid #cbd5e1; padding: 10px 18px; border-radius: 8px; font-size: 13px; font-weight: 600; text-decoration: none;">Cancel</a>
			</div>
		  </div>
		</form>
	</div>
  </div>
</div>
</body>
</html>
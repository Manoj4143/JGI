<?php 
session_start();
include("../includes/session.php");
require("../includes/dbconnection.php");

$flagdept = '';
$flagperson = '';
$flagtitle = '';
$error = '';

if (isset($_POST['cmdSubmit'])) { 		
    $dept = trim($_POST['txtdept'] ?? '');
    $person = trim($_POST['txtperson'] ?? '');
    $title = trim($_POST['txttitle'] ?? '');

    if ($dept == "") { $flagdept = 'Department Name is required.'; }
    if ($person == "") { $flagperson = 'Person Incharge is required.'; }
    if ($title == "") { $flagtitle = 'Title / Designation is required.'; }

    if (empty($flagdept) && empty($flagperson) && empty($flagtitle)) {
        $dept_clean = mysqli_real_escape_string($conn, $dept);
        $person_clean = mysqli_real_escape_string($conn, $person);
        $title_clean = mysqli_real_escape_string($conn, $title);

        $query = "INSERT INTO dept (department, dept_person, title) VALUES ('$dept_clean', '$person_clean', '$title_clean')";
        if (mysqli_query($conn, $query)) {
            header("Location: deptlist-a.php?added=1");
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
<title>Scheduling System - Add Department</title>
</head>
<body>
<div id="container">
<?php include("../includes/shedulo_shell.php"); ?>
  <div id="content">
	<div id="left">
		<div class="tt-hero-banner" style="margin-top: 10px;">
			<div>
				<div style="display: flex; align-items: center; gap: 10px; margin-bottom: 4px;">
					<h2 style="margin: 0; color: #0f172a; font-size: 20px; font-weight: 800; text-align: left; letter-spacing: -0.2px;">Add Academic Department / Branch</h2>
					<span class="badge-pill badge-blue">Department Structure</span>
				</div>
				<div style="font-size: 12.5px; color: #64748b; font-weight: 500;">
					Register a new engineering branch, department, or academic division for timetable generation and faculty assignment.
				</div>
			</div>
			<div>
				<a href="deptlist-a.php" style="background: #2563eb; color: #ffffff; padding: 8px 14px; border-radius: 8px; text-decoration: none; font-size: 12.5px; font-weight: 600; display: inline-flex; align-items: center; gap: 6px;">📋 Department List</a>
			</div>
		</div>

		<?php if (!empty($error)): ?>
		<div style="background: #fef2f2; border: 1px solid #fecaca; color: #991b1b; padding: 12px 18px; border-radius: 8px; margin: 16px 0; font-size: 13.5px;">
			⚠️ <?php echo htmlspecialchars($error); ?>
		</div>
		<?php endif; ?>

		<form action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>" name="form1" method="post">
		  <div class="form-panel">
			<h3 style="margin-top: 0; margin-bottom: 15px; color: #1e3a8a; font-size: 16px;">Department Information</h3>
			
			<div style="margin-bottom: 16px;">
				<label style="display: block; font-weight: 700; font-size: 13px; color: #334155; margin-bottom: 6px;">Department / Branch Name: *</label>
				<input type="text" name="txtdept" id="txtdept" value="<?php echo htmlspecialchars($_POST['txtdept'] ?? ''); ?>" required style="width: 100%; padding: 10px 14px; font-size: 13px; border-radius: 8px; border: 1.5px solid #cbd5e1;" placeholder="e.g. Computer Science and Engineering, AIML, or IS" />
				<?php if (!empty($flagdept)): ?><span style="color: #ef4444; font-size: 11px;"><?php echo $flagdept; ?></span><?php endif; ?>
			</div>

			<div style="margin-bottom: 16px;">
				<label style="display: block; font-weight: 700; font-size: 13px; color: #334155; margin-bottom: 6px;">Person Incharge (HOD / Dean): *</label>
				<input type="text" name="txtperson" id="txtperson" value="<?php echo htmlspecialchars($_POST['txtperson'] ?? ''); ?>" required style="width: 100%; padding: 10px 14px; font-size: 13px; border-radius: 8px; border: 1.5px solid #cbd5e1;" placeholder="e.g. Dr. Azizkhan F Pathan" />
				<?php if (!empty($flagperson)): ?><span style="color: #ef4444; font-size: 11px;"><?php echo $flagperson; ?></span><?php endif; ?>
			</div>

			<div style="margin-bottom: 20px;">
				<label style="display: block; font-weight: 700; font-size: 13px; color: #334155; margin-bottom: 6px;">Title / Designation: *</label>
				<input type="text" name="txttitle" id="txttitle" value="<?php echo htmlspecialchars($_POST['txttitle'] ?? 'Head of Department'); ?>" required style="width: 100%; padding: 10px 14px; font-size: 13px; border-radius: 8px; border: 1.5px solid #cbd5e1;" placeholder="e.g. Head of Department, Dean, or Chairperson" />
				<?php if (!empty($flagtitle)): ?><span style="color: #ef4444; font-size: 11px;"><?php echo $flagtitle; ?></span><?php endif; ?>
			</div>

			<div style="display: flex; gap: 10px; margin-top: 25px;">
				<button type="submit" name="cmdSubmit" style="background: #2563eb; color: #ffffff; border: none; padding: 10px 20px; border-radius: 8px; font-size: 13px; font-weight: 700; cursor: pointer; display: inline-flex; align-items: center; gap: 6px;">
					<span>Save Department</span>
				</button>
				<a href="deptlist-a.php" style="background: #f1f5f9; color: #475569; border: 1px solid #cbd5e1; padding: 10px 18px; border-radius: 8px; font-size: 13px; font-weight: 600; text-decoration: none;">Cancel</a>
			</div>
		  </div>
		</form>
	</div>
  </div>
</div>
</body>
</html>
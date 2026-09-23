<?php 
session_start();
include("../includes/session.php");
require("../includes/dbconnection.php");

$faculty_name = '';
$acad_rank = '';
$des = '';
$flagfaculty_name = '';
$flagdept = '';

if (isset($_POST['cmdSubmit'])) { 		
    if (trim($_POST['txtfaculty_name']) == "") { $flagfaculty_name = 'Required Field.'; }	
    if (trim($_POST['pdept']) == "") { $flagdept = 'Required Field.'; }	

    if (empty($flagfaculty_name) && empty($flagdept)) {
        $faculty_name = mysqli_real_escape_string($conn, trim($_POST['txtfaculty_name']));
        $acad_rank = mysqli_real_escape_string($conn, trim($_POST['txtacad']));
        $des = mysqli_real_escape_string($conn, trim($_POST['txtd']));
        $dept = intval($_POST['pdept']);
        
        mysqli_query($conn, "INSERT INTO profile(teacher_name, acad_rank, designation, dept_id)
            VALUES('$faculty_name','$acad_rank','$des', '$dept')") or die(mysqli_error($conn)); 
        header("Location: facultylist-a.php");
        exit;
    }
}
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
<link href="../includes/style_prac2.css" rel="stylesheet" type="text/css" />
<title>Scheduling System - Add Faculty</title>
</head>
<body>
<div id="container">
<?php include("../includes/shedulo_shell.php"); ?>
  <div id="content">
	<div id="left">
		<div class="tt-hero-banner" style="margin-top: 10px;">
			<div>
				<div style="display: flex; align-items: center; gap: 10px; margin-bottom: 4px;">
					<h2 style="margin: 0; color: #0f172a; font-size: 20px; font-weight: 800; text-align: left; letter-spacing: -0.2px;">Add Faculty Member</h2>
					<span class="badge-pill badge-green">New Instructor</span>
				</div>
				<div style="font-size: 12.5px; color: #64748b; font-weight: 500;">
					Register a new professor or teaching assistant to allocate theory and lab courses.
				</div>
			</div>
			<div>
				<a href="facultylist-a.php" style="background: #2563eb; color: #ffffff; padding: 8px 14px; border-radius: 8px; text-decoration: none; font-size: 12.5px; font-weight: 600; display: inline-flex; align-items: center; gap: 6px;">📋 Faculty List</a>
			</div>
		</div>

		<form action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>" name="form1" method="post">
		  <div class="form-panel">
			<h3 style="margin-top: 0; margin-bottom: 15px; color: #1e3a8a; font-size: 16px;">Faculty Profile Information</h3>
			
			<div style="margin-bottom: 14px;">
				<label style="display: block; font-weight: 700; font-size: 13px; color: #334155; margin-bottom: 6px;">Faculty / Professor Full Name: *</label>
				<input type="text" name="txtfaculty_name" id="txtfaculty_name" value="<?php echo htmlspecialchars($faculty_name); ?>" required style="width: 100%; padding: 10px 14px; font-size: 13px; border-radius: 8px; border: 1.5px solid #cbd5e1;" placeholder="e.g. Dr. Jane Doe" />
				<?php if ($flagfaculty_name): ?><span style="color:#dc2626; font-size:11px;"><?php echo $flagfaculty_name; ?></span><?php endif; ?>
			</div>

			<div style="margin-bottom: 14px;">
				<label style="display: block; font-weight: 700; font-size: 13px; color: #334155; margin-bottom: 6px;">Academic Rank:</label>
				<input type="text" name="txtacad" id="txtacad" value="<?php echo htmlspecialchars($acad_rank); ?>" style="width: 100%; padding: 10px 14px; font-size: 13px; border-radius: 8px; border: 1.5px solid #cbd5e1;" placeholder="e.g. Associate Professor / Assistant Professor" />
			</div>

			<div style="margin-bottom: 14px;">
				<label style="display: block; font-weight: 700; font-size: 13px; color: #334155; margin-bottom: 6px;">Designation / Title:</label>
				<input type="text" name="txtd" id="txtd" value="<?php echo htmlspecialchars($des); ?>" style="width: 100%; padding: 10px 14px; font-size: 13px; border-radius: 8px; border: 1.5px solid #cbd5e1;" placeholder="e.g. Ph.D., Senior Lecturer" />
			</div>

			<div style="margin-bottom: 18px;">
				<label style="display: block; font-weight: 700; font-size: 13px; color: #334155; margin-bottom: 6px;">Department / Academic Unit: *</label>
				<select name="pdept" id="pdept" style="width: 100%; padding: 10px 14px; font-size: 13px; border-radius: 8px; border: 1.5px solid #cbd5e1; font-weight: 600; color: #1e293b;">
                  <?php 
				$result = mysqli_query($conn,"SELECT * FROM dept ORDER BY department ASC");			  	
				while ($row = mysqli_fetch_assoc($result)) {  ?>
                  <option value="<?php echo $row['dept_id'];?>"><?php echo htmlspecialchars($row['department']);?> </option>
                <?php } ?>
                </select>
			</div>

			<div style="text-align: right; display: flex; justify-content: flex-end; gap: 8px;">
				<input type="submit" name="cmdSubmit" value="💾 Save Faculty Record" style="font-size: 13.5px; padding: 10px 24px;" />
			</div>
		  </div>
	    </form>
    </div>
	<div id="footerline">
		<p align="center" class="style4"><a href="help.php">Help</a> | <a href="about_dev.php">Developer</a> | <a href="about_sched.php">Scheduling System</a></p>
	</div>
  </div>
  <div id="footer">Automatic Timetable Generator</div>	
  </div><!-- /shedulo-main-wrapper -->
</div><!-- /container -->
</body>
</html>
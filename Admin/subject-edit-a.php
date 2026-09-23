<?php 
session_start();
include("../includes/session.php");
require("../includes/dbconnection.php");

$sub_id = isset($_REQUEST['sub_id']) ? intval($_REQUEST['sub_id']) : 0;

$result = mysqli_query($conn, "SELECT * FROM `subjects` WHERE `sub_id` = '$sub_id'");  
if (!$result || mysqli_num_rows($result) == 0) {
    header("Location: subjectlist-a.php");
    exit;
}

$subject_data = mysqli_fetch_assoc($result);
$sub_code = $subject_data['sub_code'];
$sub_name = $subject_data['sub_name'];
$sub_lechrsprday = $subject_data['sub_lechrsprday'];
$sub_labhrsprday = $subject_data['sub_labhrsprday'];
$sub_instructor = $subject_data['instructor'];
$sem_id = intval($subject_data['sem_id']);
$dept_id = intval($subject_data['dept_id']);
$sub_isNKN = $subject_data['isNKN'];
$sub_type = !empty($subject_data['subject_type']) ? $subject_data['subject_type'] : 'THEORY';

$flagccode = '';
$flagcdesc = '';
$flaglechours = '';
$flagdept = '';
$flagsem = '';

if (isset($_POST['cmdSubmit'])) { 		
    if (trim($_POST['txtccode']) == "") { $flagccode = 'Required Field.'; }	
    if (trim($_POST['txtcdesc']) == "") { $flagcdesc = 'Required Field.'; }
    if (trim($_POST['txtlechours']) == "") { $flaglechours = 'Required Field.'; }
    if (trim($_POST['pdept']) == "") { $flagdept = 'Required Field.'; }
    if (trim($_POST['sem_id']) == "") { $flagsem = 'Required Field.'; }

    if (empty($flagccode) && empty($flagcdesc) && empty($flaglechours) && empty($flagdept) && empty($flagsem)) {
        $txtccode_a = mysqli_real_escape_string($conn, trim($_POST['txtccode']));
        $txtcdesc_a = mysqli_real_escape_string($conn, trim($_POST['txtcdesc']));
        $txtlechours_a = intval($_POST['txtlechours']);
        $hidden_instructor_a = intval($_POST['pteacher']);
        $department_a = intval($_POST['pdept']);
        $sem_id_a = intval($_POST['sem_id']);
        if ($sem_id_a < 1 || $sem_id_a > 8) $sem_id_a = 1;

        $subject_type_a = isset($_POST['subject_type']) ? mysqli_real_escape_string($conn, trim($_POST['subject_type'])) : 'THEORY';
        $intIsNKN_a = ($subject_type_a == 'LAB' || (isset($_POST['intIsNKN']) && $_POST['intIsNKN'] == '1')) ? 1 : 0;
        $txtlabhours_a = ($subject_type_a == 'LAB') ? $txtlechours_a : 0;

        $gid = intval($department_a . $sem_id_a);

        mysqli_query($conn, "UPDATE subjects SET 
            sub_code = '$txtccode_a', 
            sub_name = '$txtcdesc_a', 
            isNKN = '$intIsNKN_a', 
            sub_lechrsprday = '$txtlechours_a', 
            sub_labhrsprday = '$txtlabhours_a', 
            instructor = '$hidden_instructor_a', 
            dept_id = '$department_a', 
            sem_id = '$sem_id_a',
            subject_type = '$subject_type_a', 
            group_id = '$gid' 
            WHERE sub_id = '$sub_id'") or die(mysqli_error($conn)); 

        header("Location: subjectlist-a.php");
        exit;
    }
}
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
<link href="../includes/style_prac2.css" rel="stylesheet" type="text/css" />
<title>Scheduling System - Edit Subject</title>
</head>
<body>
<div id="container">
<?php include("../includes/shedulo_shell.php"); ?>
  <div id="content">
	<div id="left">
		<div class="tt-hero-banner" style="margin-top: 10px;">
			<div>
				<div style="display: flex; align-items: center; gap: 10px; margin-bottom: 4px;">
					<h2 style="margin: 0; color: #0f172a; font-size: 20px; font-weight: 800; text-align: left; letter-spacing: -0.2px;">Edit Subject / Course Module</h2>
					<span class="badge-pill badge-blue">Curriculum Setup</span>
				</div>
				<div style="font-size: 12.5px; color: #64748b; font-weight: 500;">
					Update course parameters, assigned department, semester, and instructor.
				</div>
			</div>
			<div>
				<a href="subjectlist-a.php" style="background: #2563eb; color: #ffffff; padding: 8px 14px; border-radius: 8px; text-decoration: none; font-size: 12.5px; font-weight: 600; display: inline-flex; align-items: center; gap: 6px;">📋 Subject Catalog</a>
			</div>
		</div>

		<form action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']) . '?sub_id=' . $sub_id; ?>" name="form1" method="post">
		  <div class="form-panel">
			<h3 style="margin-top: 0; margin-bottom: 15px; color: #1e3a8a; font-size: 16px;">Subject Details</h3>
			
			<div style="margin-bottom: 14px;">
				<label style="display: block; font-weight: 700; font-size: 13px; color: #334155; margin-bottom: 6px;">Course Code: *</label>
				<input type="text" name="txtccode" id="txtccode" value="<?php echo htmlspecialchars($sub_code); ?>" required style="width: 100%; padding: 10px 14px; font-size: 13px; border-radius: 8px; border: 1.5px solid #cbd5e1;" />
			</div>

			<div style="margin-bottom: 14px;">
				<label style="display: block; font-weight: 700; font-size: 13px; color: #334155; margin-bottom: 6px;">Course Name / Description: *</label>
				<input type="text" name="txtcdesc" id="txtcdesc" value="<?php echo htmlspecialchars($sub_name); ?>" required style="width: 100%; padding: 10px 14px; font-size: 13px; border-radius: 8px; border: 1.5px solid #cbd5e1;" />
			</div>

			<div style="margin-bottom: 14px;">
				<label style="display: block; font-weight: 700; font-size: 13px; color: #334155; margin-bottom: 6px;">Subject Type: *</label>
				<select name="subject_type" id="subject_type" style="width: 100%; padding: 10px 14px; font-size: 13px; border-radius: 8px; border: 1.5px solid #cbd5e1; font-weight: 600; color: #1e293b;">
                    <option value="THEORY" <?php if($sub_type == 'THEORY') echo 'selected="selected"'; ?>>Theory (1 hr / session, distributed Mon-Sat)</option>
                    <option value="LAB" <?php if($sub_type == 'LAB') echo 'selected="selected"'; ?>>Lab (2 hrs continuous - 2 Batches / Lab Rooms simultaneously)</option>
                    <option value="MAJOR_PROJECT" <?php if($sub_type == 'MAJOR_PROJECT') echo 'selected="selected"'; ?>>Major Project (6 hrs/wk - 2 hrs/day strictly AFTER lunch)</option>
                    <option value="MINI_PROJECT" <?php if($sub_type == 'MINI_PROJECT') echo 'selected="selected"'; ?>>Mini Project (4 hrs/wk - 2 hrs/day strictly BEFORE lunch)</option>
                    <option value="NSS" <?php if($sub_type == 'NSS') echo 'selected="selected"'; ?>>NSS (2 hrs continuous - strictly in Afternoon)</option>
                    <option value="ONE_CREDIT" <?php if($sub_type == 'ONE_CREDIT') echo 'selected="selected"'; ?>>1-Credit Subject (1 hr / week)</option>
                    <option value="COMMUNITY_PROJECT" <?php if($sub_type == 'COMMUNITY_PROJECT') echo 'selected="selected"'; ?>>Community Project (2 hrs continuous)</option>
				</select>
			</div>

			<div style="margin-bottom: 14px;">
				<label style="display: block; font-weight: 700; font-size: 13px; color: #334155; margin-bottom: 6px;">Lecture / Contact Hours Per Week: *</label>
				<input type="number" name="txtlechours" id="txtlechours" value="<?php echo htmlspecialchars($sub_lechrsprday); ?>" min="1" max="10" required style="width: 100%; padding: 10px 14px; font-size: 13px; border-radius: 8px; border: 1.5px solid #cbd5e1;" />
			</div>

			<div style="margin-bottom: 14px;">
				<label style="display: block; font-weight: 700; font-size: 13px; color: #334155; margin-bottom: 6px;">Assigned Instructor / Professor:</label>
				<select name="pteacher" id="pteacher" style="width: 100%; padding: 10px 14px; font-size: 13px; border-radius: 8px; border: 1.5px solid #cbd5e1; font-weight: 600; color: #1e293b;">
                  <?php 
				$result = mysqli_query($conn,"SELECT * FROM profile ORDER BY teacher_name ASC");			  	
				while ($row = mysqli_fetch_assoc($result)) { ?>
                  <option value="<?php echo $row['teacher_id'];?>" <?php if($row['teacher_id'] == $sub_instructor) echo 'selected="selected"'; ?>><?php echo htmlspecialchars($row['teacher_name']);?> </option>
                <?php } ?>
                </select>
			</div>

			<div style="margin-bottom: 14px;">
				<label style="display: block; font-weight: 700; font-size: 13px; color: #334155; margin-bottom: 6px;">Department / Academic Branch: *</label>
				<select name="pdept" id="pdept" style="width: 100%; padding: 10px 14px; font-size: 13px; border-radius: 8px; border: 1.5px solid #cbd5e1; font-weight: 600; color: #1e293b;">
                  <?php 
				$dept_query = mysqli_query($conn,"SELECT * FROM dept ORDER BY department ASC");			  	
				while ($drow = mysqli_fetch_assoc($dept_query)) { ?>
                  <option value="<?php echo $drow['dept_id'];?>" <?php if($drow['dept_id'] == $dept_id) echo 'selected="selected"'; ?>><?php echo htmlspecialchars($drow['department']);?> </option>
                <?php } ?>
                </select>
			</div>

			<div style="margin-bottom: 18px;">
				<label style="display: block; font-weight: 700; font-size: 13px; color: #334155; margin-bottom: 6px;">Academic Semester: *</label>
				<select name="sem_id" id="sem_id" style="width: 100%; padding: 10px 14px; font-size: 13px; border-radius: 8px; border: 1.5px solid #cbd5e1; font-weight: 600; color: #1e293b;">
                  <?php 
				$sem_query = mysqli_query($conn,"SELECT * FROM sem ORDER BY sem_id ASC");			  	
				while ($sem_row = mysqli_fetch_assoc($sem_query)) { ?>
                  <option value="<?php echo $sem_row['sem_id'];?>" <?php if($sem_row['sem_id'] == $sem_id) echo 'selected="selected"'; ?>><?php echo htmlspecialchars($sem_row['semester']);?></option>
                <?php } ?>
                </select>
				<div style="font-size: 11.5px; color: #64748b; margin-top: 4px;">
					Used alongside Department to assign and generate semester-specific timetables without overlap.
				</div>
			</div>

			<div style="text-align: right;">
				<input type="submit" name="cmdSubmit" value="💾 Update Subject" style="font-size: 13.5px; padding: 10px 24px;" />
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
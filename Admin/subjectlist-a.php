<?php 
session_start();
include("../includes/session.php");
require("../includes/dbconnection.php");
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
<link href="../includes/style_prac2.css" rel="stylesheet" type="text/css" />
<title>Scheduling System - Subject &amp; Course Catalog</title>
</head>
<body>
<div id="container">
<?php include("../includes/shedulo_shell.php"); ?>
  <div id="content">
	<div id="left" style="width: 100%;">
		<div class="tt-hero-banner" style="margin-top: 10px;">
			<div>
				<div style="display: flex; align-items: center; gap: 10px; margin-bottom: 4px;">
					<h2 style="margin: 0; color: #0f172a; font-size: 20px; font-weight: 800; text-align: left; letter-spacing: -0.2px;">Academic Subject &amp; Course Catalog</h2>
					<span class="badge-pill badge-blue">Curriculum Matrix</span>
				</div>
				<div style="font-size: 12.5px; color: #64748b; font-weight: 500;">
					Theory, Laboratory, Mini/Major Project, and 1-Credit subjects configured for automated scheduling.
				</div>
			</div>
			<div style="display: flex; gap: 8px;">
				<a href="subject-a.php" style="background: #2563eb; color: #ffffff; padding: 8px 14px; border-radius: 8px; text-decoration: none; font-size: 12.5px; font-weight: 600; display: inline-flex; align-items: center; gap: 6px;">➕ Add Subject</a>
				<a href="mastertt.php" style="background: #0d9488; color: #ffffff; padding: 8px 14px; border-radius: 8px; text-decoration: none; font-size: 12.5px; font-weight: 600; display: inline-flex; align-items: center; gap: 6px;">📅 Master Grid</a>
			</div>
		</div>

		<div style="background: #ffffff; border: 1px solid #e2e8f0; border-radius: 12px; overflow: hidden; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.02); margin-top: 15px;">
		  <table width="100%" cellpadding="10" cellspacing="0" style="border-collapse: collapse; font-size: 12.5px;">
            <thead>
              <tr style="background: #f8fafc; border-bottom: 1.5px solid #e2e8f0; color: #475569; text-align: left; font-size: 11.5px; text-transform: uppercase; letter-spacing: 0.5px;">
                <th style="padding: 12px 16px;">Course Code</th>
                <th style="padding: 12px 16px;">Subject Name</th>
                <th style="padding: 12px 16px; text-align: center;">Dept / Branch</th>
                <th style="padding: 12px 16px; text-align: center;">Semester</th>
                <th style="padding: 12px 16px; text-align: center;">Type</th>
                <th style="padding: 12px 16px; text-align: center;">Hrs / Wk</th>
                <th style="padding: 12px 16px;">Instructor</th>
                <th style="padding: 12px 16px; text-align: center;">Actions</th>
              </tr>
            </thead>
            <tbody>
            <?php 
			$result = mysqli_query($conn, "SELECT subjects.*, profile.teacher_name, dept.department, sem.semester 
                FROM subjects 
                LEFT JOIN profile ON subjects.instructor = profile.teacher_id 
                LEFT JOIN dept ON subjects.dept_id = dept.dept_id 
                LEFT JOIN sem ON subjects.sem_id = sem.sem_id 
                ORDER BY subjects.dept_id ASC, subjects.sem_id ASC, subjects.sub_id ASC");
			if ($result && mysqli_num_rows($result) > 0) {
				while ($row = mysqli_fetch_assoc($result)) {
					$sub_id = $row['sub_id'];
					$sub_code = htmlspecialchars($row['sub_code']);
					$sub_name = htmlspecialchars($row['sub_name']);
					$sub_hours = intval($row['sub_lechrsprday']);
					$sub_type = !empty($row['subject_type']) ? $row['subject_type'] : 'THEORY';
					$teacher_name = !empty($row['teacher_name']) ? htmlspecialchars($row['teacher_name']) : '<span style="color:#94a3b8;">Not Assigned</span>';
					$dept_name = !empty($row['department']) ? htmlspecialchars($row['department']) : '<span style="color:#94a3b8;">Dept #'.$row['dept_id'].'</span>';
					$sem_display = !empty($row['semester']) ? htmlspecialchars($row['semester']) : 'Sem ' . (intval($row['sem_id']) ?: 1);

					$type_badge = '<span class="badge-pill badge-blue">THEORY</span>';
					if ($sub_type === 'LAB') $type_badge = '<span class="badge-pill badge-green">LAB</span>';
					elseif ($sub_type === 'NSS') $type_badge = '<span class="badge-pill badge-amber">NSS</span>';
					elseif ($sub_type === 'ONE_CREDIT') $type_badge = '<span class="badge-pill badge-purple">1-CREDIT</span>';
					elseif ($sub_type === 'MINI_PROJECT') $type_badge = '<span class="badge-pill badge-pink">MINI PROJECT</span>';
					elseif ($sub_type === 'MAJOR_PROJECT') $type_badge = '<span class="badge-pill badge-pink" style="background:#ffe4e6; color:#be123c;">MAJOR PROJECT</span>';
					elseif ($sub_type === 'COMMUNITY_PROJECT') $type_badge = '<span class="badge-pill badge-blue">COMMUNITY</span>';
			?>
              <tr style="border-bottom: 1px solid #f1f5f9; transition: background 0.15s ease;" onmouseover="this.style.background='#f8fafc'" onmouseout="this.style.background='#ffffff'">
                <td style="padding: 12px 16px; font-weight: 700; color: #1e3a8a;"><?php echo $sub_code; ?></td>
                <td style="padding: 12px 16px; color: #1e293b; font-weight: 600;"><?php echo $sub_name; ?></td>
                <td style="padding: 12px 16px; text-align: center;"><span class="badge-pill" style="background: rgba(20, 184, 166, 0.15); color: #0d9488; font-weight: 700;"><?php echo $dept_name; ?></span></td>
                <td style="padding: 12px 16px; text-align: center;"><span class="badge-pill badge-purple" style="font-weight: 700;"><?php echo $sem_display; ?></span></td>
                <td style="padding: 12px 16px; text-align: center;"><?php echo $type_badge; ?></td>
                <td style="padding: 12px 16px; text-align: center; font-weight: 700; color: #475569;"><?php echo $sub_hours; ?> hrs</td>
                <td style="padding: 12px 16px; color: #334155;">👤 <?php echo $teacher_name; ?></td>
                <td style="padding: 12px 16px; text-align: center;">
					<a href="subject-edit-a.php?sub_id=<?php echo $sub_id; ?>" style="margin-right: 8px; text-decoration: none;" title="Edit Record">✏️</a>
					<a href="subject-del-a.php?sub_id=<?php echo $sub_id; ?>" onclick="return confirm('Are you sure you want to delete this subject?');" style="text-decoration: none;" title="Delete Record">🗑️</a>
                </td>
              </tr>
            <?php 
				}
			} else {
				echo '<tr><td colspan="8" style="padding: 24px; text-align: center; color: #94a3b8;">No subjects registered in the catalog.</td></tr>';
			}
			?>
            </tbody>
          </table>
		</div>
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

<?php 
session_start();
include("../includes/session.php");
require("../includes/DBConnection.php");

$curr_role = $_SESSION['is']['role'] ?? (($_SESSION['is']['dept_id'] == 4) ? 'Admin' : 'Faculty');
$isAdmin = (strtolower($curr_role) === 'admin');
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
<link href="../includes/style_prac2.css" rel="stylesheet" type="text/css" />
<title>Scheduling System - Faculty Directory</title>
</head>
<body>
<div id="container">
<?php include("../includes/shedulo_shell.php"); ?>
  <div id="content">
	<div id="left" style="width: 100%;">
		<div class="tt-hero-banner" style="margin-top: 10px;">
			<div>
				<div style="display: flex; align-items: center; gap: 10px; margin-bottom: 4px;">
					<h2 style="margin: 0; color: #0f172a; font-size: 20px; font-weight: 800; text-align: left; letter-spacing: -0.2px;">Faculty &amp; Teaching Staff Roster</h2>
					<span class="badge-pill badge-green">Faculty Management</span>
				</div>
				<div style="font-size: 12.5px; color: #64748b; font-weight: 500;">
					Registered professors, academic designations, departmental affiliations, and timetable associations.
				</div>
			</div>
			<div style="display: flex; gap: 8px;">
				<?php if ($isAdmin): ?>
				<a href="faculty-a.php" style="background: #2563eb; color: #ffffff; padding: 8px 14px; border-radius: 8px; text-decoration: none; font-size: 12.5px; font-weight: 600; display: inline-flex; align-items: center; gap: 6px;">➕ Add Faculty</a>
				<?php endif; ?>
				<a href="search_teacher.php" style="background: #0d9488; color: #ffffff; padding: 8px 14px; border-radius: 8px; text-decoration: none; font-size: 12.5px; font-weight: 600; display: inline-flex; align-items: center; gap: 6px;">🔍 View Schedules</a>
			</div>
		</div>

		<div style="background: #ffffff; border: 1px solid #e2e8f0; border-radius: 12px; overflow: hidden; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.02); margin-top: 15px;">
		  <table width="100%" cellpadding="10" cellspacing="0" style="border-collapse: collapse; font-size: 12.5px;">
            <thead>
              <tr style="background: #f8fafc; border-bottom: 1.5px solid #e2e8f0; color: #475569; text-align: left; font-size: 11.5px; text-transform: uppercase; letter-spacing: 0.5px;">
                <th style="padding: 12px 16px;">Faculty Name</th>
                <th style="padding: 12px 16px;">Academic Rank</th>
                <th style="padding: 12px 16px;">Designation</th>
                <th style="padding: 12px 16px;">Department</th>
                <th style="padding: 12px 16px; text-align: center;">Weekly Schedule</th>
                <?php if ($isAdmin): ?>
                <th style="padding: 12px 16px; text-align: center;">Actions</th>
                <?php endif; ?>
              </tr>
            </thead>
            <tbody>
            <?php 
			$result = mysqli_query($conn, "SELECT profile.*, dept.department FROM profile LEFT JOIN dept ON profile.dept_id = dept.dept_id ORDER BY profile.teacher_name ASC");
			if ($result && mysqli_num_rows($result) > 0) {
				while ($row = mysqli_fetch_assoc($result)) {
					$profile_no = $row['teacher_id'];
					$faculty_name = htmlspecialchars($row['teacher_name']);
					$acad_rank = htmlspecialchars($row['acad_rank']);
					$des = htmlspecialchars($row['designation']);
					$dept = !empty($row['department']) ? htmlspecialchars($row['department']) : 'General';
			?>
              <tr style="border-bottom: 1px solid #f1f5f9; transition: background 0.15s ease;" onmouseover="this.style.background='#f8fafc'" onmouseout="this.style.background='#ffffff'">
                <td style="padding: 12px 16px; font-weight: 700; color: #1e293b;">
					<?php echo $faculty_name; ?>
                </td>
                <td style="padding: 12px 16px; color: #475569;">
					<span class="badge-pill badge-green"><?php echo $acad_rank; ?></span>
                </td>
                <td style="padding: 12px 16px; color: #475569;"><?php echo $des; ?></td>
                <td style="padding: 12px 16px; color: #1e40af; font-weight: 600;"><?php echo $dept; ?></td>
                <td style="padding: 12px 16px; text-align: center;">
					<a href="search_t_result.php?pT=<?php echo $profile_no; ?>" style="background: #eff6ff; color: #2563eb; padding: 4px 10px; border-radius: 6px; text-decoration: none; font-size: 11px; font-weight: 600; border: 1px solid #bfdbfe;">View Schedule &rarr;</a>
                </td>
                <?php if ($isAdmin): ?>
                <td style="padding: 12px 16px; text-align: center;">
					<a href="faculty-edit-a.php?profile_no=<?php echo $profile_no; ?>" style="margin-right: 8px; text-decoration: none;" title="Edit Record">✏️</a>
					<a href="faculty-del-a.php?profile_no=<?php echo $profile_no; ?>" onclick="return confirm('Are you sure you want to remove this faculty member?');" style="text-decoration: none;" title="Delete Record">🗑️</a>
                </td>
                <?php endif; ?>
              </tr>
            <?php 
				}
			} else {
				echo '<tr><td colspan="6" style="padding: 24px; text-align: center; color: #94a3b8;">No faculty records registered.</td></tr>';
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
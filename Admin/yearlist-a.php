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
<title>Scheduling System - Academic School Years</title>
</head>
<body>
<div id="container">
<?php include("../includes/shedulo_shell.php"); ?>
  <div id="content">
	<div id="left">
		<div class="tt-hero-banner" style="margin-top: 10px;">
			<div>
				<div style="display: flex; align-items: center; gap: 10px; margin-bottom: 4px;">
					<h2 style="margin: 0; color: #0f172a; font-size: 20px; font-weight: 800; text-align: left; letter-spacing: -0.2px;">Academic School Years</h2>
					<span class="badge-pill badge-green">Session Terms</span>
				</div>
				<div style="font-size: 12.5px; color: #64748b; font-weight: 500;">
					Registered institutional academic years and annual session calendars.
				</div>
			</div>
			<div>
				<a href="year-a.php" style="background: #2563eb; color: #ffffff; padding: 8px 14px; border-radius: 8px; text-decoration: none; font-size: 12.5px; font-weight: 600; display: inline-flex; align-items: center; gap: 6px;">+ Add School Year</a>
			</div>
		</div>

		<?php if (isset($_GET['added'])): ?>
		<div style="background: #ecfdf5; border: 1px solid #a7f3d0; color: #065f46; padding: 12px 18px; border-radius: 8px; margin: 16px 0; font-size: 13.5px;">
			✅ School year has been successfully added to the system.
		</div>
		<?php endif; ?>

		<div class="form-panel" style="padding: 0; overflow: hidden;">
			<table class="tt-grid" style="margin: 0;">
				<thead>
					<tr>
						<th style="width: 80px; text-align: center;">ID</th>
						<th>School Year Session</th>
						<th style="width: 140px; text-align: center;">Actions</th>
					</tr>
				</thead>
				<tbody>
				<?php 
				$result = mysqli_query($conn, "SELECT * FROM school_yr ORDER BY year_id ASC");
				if (!$result || mysqli_num_rows($result) == 0) {
					echo '<tr><td colspan="3" style="text-align: center; padding: 30px; color: #64748b;">No school years found. Click "+ Add School Year" to create one.</td></tr>';
				} else {
					while ($row = mysqli_fetch_assoc($result)) {
						$year_id = $row['year_id'];
						$school_year = $row['school_year'];
				?>
					<tr>
						<td style="text-align: center; font-weight: 700; color: #64748b;"><?php echo $year_id; ?></td>
						<td style="font-weight: 700; color: #1e293b;">
							<div style="font-size: 13.5px;"><?php echo htmlspecialchars($school_year); ?></div>
						</td>
						<td style="text-align: center;">
							<div style="display: inline-flex; gap: 8px; align-items: center;">
								<a href="year-edit-a.php?year_id=<?php echo $year_id; ?>" style="background: #eff6ff; color: #2563eb; border: 1px solid #bfdbfe; padding: 4px 10px; border-radius: 6px; text-decoration: none; font-size: 11.5px; font-weight: 700;">Edit</a>
								<a href="year-del-a.php?year_id=<?php echo $year_id; ?>" onclick="return confirm('Are you sure you want to delete school year #<?php echo $year_id; ?> (<?php echo htmlspecialchars(addslashes($school_year)); ?>)?');" style="background: #fef2f2; color: #dc2626; border: 1px solid #fecaca; padding: 4px 10px; border-radius: 6px; text-decoration: none; font-size: 11.5px; font-weight: 700;">Delete</a>
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
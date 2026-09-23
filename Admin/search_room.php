<?php 
session_start();
include("../includes/session.php");
require("../includes/dbconnection.php");

if (isset($_POST['cmdSubmit'])) { 		
    if (!empty($_POST['pRoom'])){ 
        header("Location: search_r_result.php?pR=". intval($_POST['pRoom']));
        exit;
    }
}
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
<link href="../includes/style_prac2.css" rel="stylesheet" type="text/css" />
<title>Scheduling System - Room Schedule Lookup</title>
</head>
<body>
<div id="container">
<?php include("../includes/shedulo_shell.php"); ?>
  <div id="content">
	<div id="left">
		<div class="tt-hero-banner" style="margin-top: 10px;">
			<div>
				<div style="display: flex; align-items: center; gap: 10px; margin-bottom: 4px;">
					<h2 style="margin: 0; color: #0f172a; font-size: 20px; font-weight: 800; text-align: left; letter-spacing: -0.2px;">Room &amp; Lab Schedule Lookup</h2>
					<span class="badge-pill badge-purple">Room Matrix</span>
				</div>
				<div style="font-size: 12.5px; color: #64748b; font-weight: 500;">
					Inspect facility schedules, computer lab utilization, and lecture hall bookings across the entire 6-day cycle.
				</div>
			</div>
			<div>
				<a href="mastertt.php" style="background: #2563eb; color: #ffffff; padding: 8px 14px; border-radius: 8px; text-decoration: none; font-size: 12.5px; font-weight: 600; display: inline-flex; align-items: center; gap: 6px;">📅 Master Grid</a>
			</div>
		</div>

		<form action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>" name="form1" method="post">
		  <div class="form-panel">
			<h3 style="margin-top: 0; margin-bottom: 15px; color: #1e3a8a; font-size: 16px;">Select Room or Computer Laboratory</h3>
			<div style="margin-bottom: 18px;">
				<label style="display: block; font-weight: 700; font-size: 13px; color: #334155; margin-bottom: 6px;">Campus Room / Lab:</label>
				<select name="pRoom" id="pRoom" style="width: 100%; padding: 11px 14px; font-size: 13px; border-radius: 8px; border: 1.5px solid #cbd5e1; font-weight: 600; color: #1e293b;">
                  <?php 
				$result = mysqli_query($conn,"SELECT * FROM room ORDER BY room_isNKN DESC, room_name ASC");			  	
				while ($row = mysqli_fetch_assoc($result)) { 
					$isLab = intval($row['room_isNKN']) === 1;
					$label = htmlspecialchars($row['room_name']) . ($isLab ? ' [LAB]' : ' [LECTURE]') . ' (Capacity: ' . htmlspecialchars($row['room_size']) . ')';
				  ?>
					<option value="<?php echo $row['room_id'];?>"><?php echo $label; ?></option>
                  <?php } ?>
				</select>
			</div>
			<div style="text-align: right;">
				<input type="submit" name="cmdSubmit" value="🔍 View Room Schedule" style="font-size: 13.5px; padding: 10px 24px;" />
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
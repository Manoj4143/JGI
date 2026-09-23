<?php 
session_start();
include("../includes/session.php");
require("../includes/dbconnection.php");

$flagroom = '';
$flagdesc = '';
$room = '';
$desc = '';
$room_size = '60';
$room_isNKN = '0';

if (isset($_POST['cmdSubmit'])) { 		
    if (trim($_POST['txtroom']) == "") { $flagroom = 'Required Field.'; }	
    if (trim($_POST['txtdesc']) == "") { $flagdesc = 'Required Field.'; }
    
    $room = mysqli_real_escape_string($conn, trim($_POST['txtroom']));
    $desc = mysqli_real_escape_string($conn, trim($_POST['txtdesc']));
    $room_isNKN = isset($_POST['txtIsNKN']) ? intval($_POST['txtIsNKN']) : 0;
    $room_size = isset($_POST['txtSize']) ? intval($_POST['txtSize']) : 60;

    if (empty($flagroom) && empty($flagdesc)) {
        mysqli_query($conn, "INSERT INTO room(room_name, room_desc, room_isNKN, room_size)
                VALUES('$room', '$desc', '$room_isNKN', '$room_size')") or die(mysqli_error($conn)); 
        header("Location: roomlist-a.php");
        exit;
    }
}
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
<link href="../includes/style_prac2.css" rel="stylesheet" type="text/css" />
<title>Scheduling System - Add Campus Room</title>
</head>
<body>
<div id="container">
<?php include("../includes/shedulo_shell.php"); ?>
  <div id="content">
	<div id="left">
		<div class="tt-hero-banner" style="margin-top: 10px;">
			<div>
				<div style="display: flex; align-items: center; gap: 10px; margin-bottom: 4px;">
					<h2 style="margin: 0; color: #0f172a; font-size: 20px; font-weight: 800; text-align: left; letter-spacing: -0.2px;">Add Room or Computer Lab</h2>
					<span class="badge-pill badge-purple">Facility Provisioning</span>
				</div>
				<div style="font-size: 12.5px; color: #64748b; font-weight: 500;">
					Register lecture halls, tutorial rooms, and NKN computer laboratories for timetable slot allocation.
				</div>
			</div>
			<div>
				<a href="roomlist-a.php" style="background: #f1f5f9; color: #334155; padding: 8px 14px; border-radius: 8px; text-decoration: none; font-size: 12.5px; font-weight: 600; border: 1px solid #cbd5e1; display: inline-flex; align-items: center; gap: 6px;">&larr; View Rooms</a>
			</div>
		</div>

		<form action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>" method="post">
		  <div class="form-panel">
			<h3 style="margin-top: 0; margin-bottom: 18px; color: #1e3a8a; font-size: 16px;">Room Details</h3>
			
			<div style="margin-bottom: 16px;">
				<label style="display: block; font-weight: 700; font-size: 13px; color: #334155; margin-bottom: 6px;">Room / Lab Identifier:</label>
				<input type="text" name="txtroom" id="txtroom" value="<?php echo htmlspecialchars($room); ?>" placeholder="e.g. Room 217, Lab 102, CC-1" style="width: 100%; padding: 10px 12px; font-size: 13px; border-radius: 8px; border: 1.5px solid #cbd5e1;" />
				<?php if (!empty($flagroom)) { echo '<span style="color: #ef4444; font-size: 11px; font-weight: 600;">' . $flagroom . '</span>'; } ?>
			</div>

			<div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px; margin-bottom: 16px;">
				<div>
					<label style="display: block; font-weight: 700; font-size: 13px; color: #334155; margin-bottom: 6px;">Is Computer Lab (NKN / Lab)?</label>
					<select name="txtIsNKN" id="txtIsNKN" style="width: 100%; padding: 10px 12px; font-size: 13px; border-radius: 8px; border: 1.5px solid #cbd5e1;">
						<option value="0" <?php if ($room_isNKN == '0') echo 'selected'; ?>>No (Lecture / Theory Hall)</option>
						<option value="1" <?php if ($room_isNKN == '1') echo 'selected'; ?>>Yes (Computer / Lab Facility)</option>
					</select>
				</div>
				<div>
					<label style="display: block; font-weight: 700; font-size: 13px; color: #334155; margin-bottom: 6px;">Seating Capacity:</label>
					<input type="number" name="txtSize" id="txtSize" value="<?php echo htmlspecialchars($room_size); ?>" min="1" max="500" style="width: 100%; padding: 10px 12px; font-size: 13px; border-radius: 8px; border: 1.5px solid #cbd5e1;" />
				</div>
			</div>

			<div style="margin-bottom: 20px;">
				<label style="display: block; font-weight: 700; font-size: 13px; color: #334155; margin-bottom: 6px;">Description / Facility Specs:</label>
				<input type="text" name="txtdesc" id="txtdesc" value="<?php echo htmlspecialchars($desc); ?>" placeholder="e.g. 60 Computers with Ubuntu, Projector, High-speed LAN" style="width: 100%; padding: 10px 12px; font-size: 13px; border-radius: 8px; border: 1.5px solid #cbd5e1;" />
				<?php if (!empty($flagdesc)) { echo '<span style="color: #ef4444; font-size: 11px; font-weight: 600;">' . $flagdesc . '</span>'; } ?>
			</div>

			<div style="text-align: right; display: flex; justify-content: flex-end; gap: 10px;">
				<a href="roomlist-a.php" style="background: #f1f5f9; color: #475569; padding: 10px 20px; border-radius: 8px; text-decoration: none; font-size: 13px; font-weight: 600; border: 1px solid #cbd5e1;">Cancel</a>
				<input type="submit" name="cmdSubmit" value="💾 Save Room" style="font-size: 13.5px; padding: 10px 24px;" />
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

<?php 
session_start();
include("../includes/session.php");
require("../includes/DBConnection.php");
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
<link href="../includes/style_prac2.css" rel="stylesheet" type="text/css" />
<title>Scheduling System - Room &amp; Lab Directory</title>
</head>
<body>
<div id="container">
<?php include("../includes/shedulo_shell.php"); ?>
  <div id="content">
	<div id="left" style="width: 100%;">
		<div class="tt-hero-banner" style="margin-top: 10px;">
			<div>
				<div style="display: flex; align-items: center; gap: 10px; margin-bottom: 4px;">
					<h2 style="margin: 0; color: #0f172a; font-size: 20px; font-weight: 800; text-align: left; letter-spacing: -0.2px;">Campus Room &amp; Laboratory Directory</h2>
					<span class="badge-pill badge-purple">Facility Management</span>
				</div>
				<div style="font-size: 12.5px; color: #64748b; font-weight: 500;">
					Registered lecture halls, smart seminar rooms, and specialized computer laboratory facilities.
				</div>
			</div>
			<div style="display: flex; gap: 8px;">
				<a href="room-a.php" style="background: #2563eb; color: #ffffff; padding: 8px 14px; border-radius: 8px; text-decoration: none; font-size: 12.5px; font-weight: 600; display: inline-flex; align-items: center; gap: 6px;">➕ Add Room</a>
				<a href="search_room.php" style="background: #0d9488; color: #ffffff; padding: 8px 14px; border-radius: 8px; text-decoration: none; font-size: 12.5px; font-weight: 600; display: inline-flex; align-items: center; gap: 6px;">🔍 Room Schedules</a>
			</div>
		</div>

		<div style="background: #ffffff; border: 1px solid #e2e8f0; border-radius: 12px; overflow: hidden; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.02); margin-top: 15px;">
		  <table width="100%" cellpadding="10" cellspacing="0" style="border-collapse: collapse; font-size: 12.5px;">
            <thead>
              <tr style="background: #f8fafc; border-bottom: 1.5px solid #e2e8f0; color: #475569; text-align: left; font-size: 11.5px; text-transform: uppercase; letter-spacing: 0.5px;">
                <th style="padding: 12px 16px;">Room Name</th>
                <th style="padding: 12px 16px; text-align: center;">Facility Type</th>
                <th style="padding: 12px 16px; text-align: center;">Seating Capacity</th>
                <th style="padding: 12px 16px;">Description</th>
                <th style="padding: 12px 16px; text-align: center;">Schedule</th>
                <th style="padding: 12px 16px; text-align: center;">Actions</th>
              </tr>
            </thead>
            <tbody>
            <?php 
			$result = mysqli_query($conn, "SELECT * FROM room ORDER BY room_isNKN DESC, room_name ASC");
			if ($result && mysqli_num_rows($result) > 0) {
				while ($row = mysqli_fetch_assoc($result)) {
					$room_id = $row['room_id'];
					$room = htmlspecialchars($row['room_name']);
					$desc = htmlspecialchars($row['room_desc']);
					$isLab = intval($row['room_isNKN']) === 1;
					$room_size = htmlspecialchars($row['room_size']);
			?>
              <tr style="border-bottom: 1px solid #f1f5f9; transition: background 0.15s ease;" onmouseover="this.style.background='#f8fafc'" onmouseout="this.style.background='#ffffff'">
                <td style="padding: 12px 16px; font-weight: 700; color: #1e293b;">
					<?php echo $room; ?>
                </td>
                <td style="padding: 12px 16px; text-align: center;">
					<span class="badge-pill <?php echo $isLab ? 'badge-green' : 'badge-blue'; ?>">
						<?php echo $isLab ? 'COMPUTER LAB' : 'LECTURE HALL'; ?>
					</span>
                </td>
                <td style="padding: 12px 16px; text-align: center; font-weight: 700; color: #475569;">
					<?php echo $room_size; ?> seats
                </td>
                <td style="padding: 12px 16px; color: #64748b;"><?php echo $desc; ?></td>
                <td style="padding: 12px 16px; text-align: center;">
					<a href="search_r_result.php?pR=<?php echo $room_id; ?>" style="background: #eff6ff; color: #2563eb; padding: 4px 10px; border-radius: 6px; text-decoration: none; font-size: 11px; font-weight: 600; border: 1px solid #bfdbfe;">View Schedule &rarr;</a>
                </td>
                <td style="padding: 12px 16px; text-align: center;">
					<a href="room-edit-a.php?Room=<?php echo $room_id; ?>" style="margin-right: 8px; text-decoration: none;" title="Edit Record">✏️</a>
					<a href="room-del-a.php?Room=<?php echo $room_id; ?>" onclick="return confirm('Are you sure you want to delete <?php echo $room; ?>?');" style="text-decoration: none;" title="Delete Record">🗑️</a>
                </td>
              </tr>
            <?php 
				}
			} else {
				echo '<tr><td colspan="6" style="padding: 24px; text-align: center; color: #94a3b8;">No room records found.</td></tr>';
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

<?php 
session_start();
include("../includes/session.php");
require("../includes/dbconnection.php");
$room_id = isset($_REQUEST['pR']) ? intval($_REQUEST['pR']) : (isset($_REQUEST['room']) ? intval($_REQUEST['room']) : 0);

$roomtemp = mysqli_fetch_array(mysqli_query($conn, "SELECT * FROM room WHERE room_id = $room_id"));
$roomname = !empty($roomtemp['room_name']) ? $roomtemp['room_name'] : 'Room Schedule';
$roomdesc = !empty($roomtemp['room_desc']) ? $roomtemp['room_desc'] : 'Campus Hall';
$isLab = intval($roomtemp['room_isNKN']) === 1;
$cap = !empty($roomtemp['room_size']) ? $roomtemp['room_size'] : '60';
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta charset="UTF-8" />
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<link href="../includes/style_prac2.css" rel="stylesheet" type="text/css" />
<title>Scheduling System - Room Schedule - <?php echo htmlspecialchars($roomname); ?></title>
</head>
<body>
<div id="container">
<?php include("../includes/shedulo_shell.php"); ?>
  <div id="content">
	<div id="left" style="width: 100%;">
		<form name="form1" method="post">
				<div class="tt-hero-banner">
					<div>
						<div style="display: flex; align-items: center; gap: 10px; margin-bottom: 4px; flex-wrap: wrap;">
							<h2 style="margin: 0; color: #0f172a; font-size: 20px; font-weight: 800; text-align: left; letter-spacing: -0.2px;"><?php echo htmlspecialchars($roomname); ?></h2>
							<span class="badge-pill <?php echo $isLab ? 'badge-green' : 'badge-blue'; ?>"><?php echo $isLab ? 'COMPUTER LAB' : 'LECTURE HALL'; ?></span>
							<span class="badge-pill" style="background: #f1f5f9; color: #475569;">Capacity: <?php echo htmlspecialchars($cap); ?></span>
							<span style="font-size: 11px; color: #64748b; font-weight: 500;"><?php echo htmlspecialchars($roomdesc); ?></span>
						</div>
						<div style="font-size: 12px; color: #64748b; font-weight: 500;">
							Facility Utilization Matrix &bull; 6-Day Weekly Schedule (Mon &ndash; Sat)
						</div>
					</div>
					<div style="display: flex; gap: 8px;">
						<a href="search_room.php" style="background: #f1f5f9; color: #334155; padding: 8px 14px; border-radius: 8px; text-decoration: none; font-size: 12.5px; font-weight: 600; border: 1px solid #cbd5e1; display: inline-flex; align-items: center; gap: 6px;">&larr; Switch Room</a>
						<button type="button" onclick="window.print()" style="background: #2563eb; color: #ffffff; border: none; padding: 8px 16px; border-radius: 8px; cursor: pointer; font-size: 12.5px; font-weight: 600; box-shadow: 0 2px 4px rgba(37, 99, 235, 0.2); display: inline-flex; align-items: center; gap: 6px;">🖨️ Print Timetable</button>
					</div>
				</div>

		   <table class="tt-grid">
            <thead>
              <tr>
                <th style="width: 110px;">DAY</th>
                <th style="width: 12.5%;">09:00 - 10:00</th>
                <th style="width: 12.5%;">10:00 - 11:00</th>
                <th class="tt-col-break"><div class="break-icon">☕</div><div class="break-title">BREAK</div><div class="break-time">11:00-11:15</div></th>
                <th style="width: 12.5%;">11:15 - 12:15</th>
                <th style="width: 12.5%;">12:15 - 01:15</th>
                <th class="tt-col-lunch"><div class="lunch-icon">🍽️</div><div class="lunch-title">LUNCH</div><div class="lunch-time">01:15-02:00</div></th>
                <th style="width: 12.5%;">02:00 - 03:00</th>
                <th style="width: 12.5%;">03:00 - 04:00</th>
                <th style="width: 12.5%;">04:00 - 05:00</th>
              </tr>
            </thead>
            <tbody>
            <?php
            $days = [
                1 => 'MONDAY',
                2 => 'TUESDAY',
                3 => 'WEDNESDAY',
                4 => 'THURSDAY',
                5 => 'FRIDAY',
                6 => 'SATURDAY'
            ];

            function renderRoomCard($entries, $is_spanned = false) {
                if (empty($entries)) {
                    return '<span style="color: #cbd5e1; font-size: 14px; font-weight: 300;">—</span>';
                }
                $first = $entries[0];
                $stype = $first['subject_type'];
                $sub_code = htmlspecialchars($first['sub_code']);
                $sub_name = htmlspecialchars($first['sub_name']);
                $teacher = htmlspecialchars($first['teacher_name']);
                $grp = htmlspecialchars($first['group_name']);

                if ($stype === 'LAB') {
                    $html = '<div class="card-lecture card-lab">';
                    $html .= '<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 4px;">';
                    $html .= '<span class="badge-pill badge-green">LAB SESSION</span>';
                    $html .= '<span style="font-size: 9px; color: #047857; font-weight: 700; background: #d1fae5; padding: 2px 6px; border-radius: 4px;">2 PERIODS</span>';
                    $html .= '</div>';
                    $html .= '<span class="card-code">' . $sub_code . '</span>';
                    $html .= '<div class="card-name">' . $sub_name . '</div>';
                    $html .= '<div class="card-meta">👤 Incharge: <strong>' . $teacher . '</strong></div>';
                    $html .= '<div style="margin-top: 4px; font-size: 10px; color: #047857; font-weight: 600;">👥 Batch: ' . $grp . '</div>';
                    $html .= '</div>';
                    return $html;
                } elseif ($stype === 'NSS') {
                    $html = '<div class="card-lecture card-nss">';
                    $html .= '<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 4px;">';
                    $html .= '<span class="badge-pill badge-amber">NSS</span>';
                    $html .= '<span style="font-size: 9px; color: #b45309; font-weight: 700; background: #fef3c7; padding: 2px 6px; border-radius: 4px;">2 PERIODS</span>';
                    $html .= '</div>';
                    $html .= '<span class="card-code">' . $sub_code . '</span>';
                    $html .= '<div class="card-name">' . $sub_name . '</div>';
                    $html .= '<div class="card-meta">👤 Teacher: <strong>' . $teacher . '</strong></div>';
                    $html .= '<div class="card-room">👥 Group: ' . $grp . '</div>';
                    $html .= '</div>';
                    return $html;
                } elseif (in_array($stype, ['MINI_PROJECT', 'MAJOR_PROJECT', 'COMMUNITY_PROJECT'])) {
                    $badge_titles = [
                        'MINI_PROJECT' => 'MINI PROJECT',
                        'MAJOR_PROJECT' => 'MAJOR PROJECT',
                        'COMMUNITY_PROJECT' => 'COMMUNITY PROJECT'
                    ];
                    $badge_label = $badge_titles[$stype];
                    $html = '<div class="card-lecture card-project">';
                    $html .= '<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 4px;">';
                    $html .= '<span class="badge-pill badge-pink">' . $badge_label . '</span>';
                    $html .= '<span style="font-size: 9px; color: #be185d; font-weight: 700; background: #fce7f3; padding: 2px 6px; border-radius: 4px;">2 PERIODS</span>';
                    $html .= '</div>';
                    $html .= '<span class="card-code">' . $sub_code . '</span>';
                    $html .= '<div class="card-name">' . $sub_name . '</div>';
                    $html .= '<div class="card-meta">👤 Guide: <strong>' . $teacher . '</strong></div>';
                    $html .= '<div class="card-room">👥 Group: ' . $grp . '</div>';
                    $html .= '</div>';
                    return $html;
                } elseif ($stype === 'ONE_CREDIT') {
                    $html = '<div class="card-lecture card-credit">';
                    $html .= '<div style="margin-bottom: 4px;"><span class="badge-pill badge-purple">1-CREDIT (1 HR)</span></div>';
                    $html .= '<span class="card-code">' . $sub_code . '</span>';
                    $html .= '<div class="card-name">' . $sub_name . '</div>';
                    $html .= '<div class="card-meta">👤 Teacher: <strong>' . $teacher . '</strong></div>';
                    $html .= '<div class="card-room">👥 Group: ' . $grp . '</div>';
                    $html .= '</div>';
                    return $html;
                } else {
                    $html = '<div class="card-lecture card-theory">';
                    $html .= '<div style="margin-bottom: 4px;"><span class="badge-pill badge-blue">THEORY</span></div>';
                    $html .= '<span class="card-code">' . $sub_code . '</span>';
                    $html .= '<div class="card-name">' . $sub_name . '</div>';
                    $html .= '<div class="card-meta">👤 Teacher: <strong>' . $teacher . '</strong></div>';
                    $html .= '<div class="card-room">👥 Group: ' . $grp . '</div>';
                    $html .= '</div>';
                    return $html;
                }
            }

            foreach ($days as $day_id => $day_name) {
                echo '<tr>';
                echo '<td class="tt-day-col">' . $day_name . '</td>';

                // Fetch all entries for this room on this day
                $day_entries = [];
                $q = "SELECT s.*, sub.sub_code, sub.sub_name, sub.subject_type, p.teacher_name 
                      FROM sched s 
                      JOIN subjects sub ON s.sub_id = sub.sub_id 
                      JOIN profile p ON s.teacher_id = p.teacher_id 
                      WHERE s.room_id = $room_id 
                        AND s.day_id = $day_id 
                      ORDER BY s.group_name ASC";
                $res = mysqli_query($conn, $q);
                if ($res) {
                    while ($row = mysqli_fetch_assoc($res)) {
                        $day_entries[$row['time_s_id']][] = $row;
                    }
                }

                // Check 2-period spanning
                $is2HrStart = function($s1, $s2) use ($day_entries) {
                    if (isset($day_entries[$s1]) && isset($day_entries[$s2])) {
                        $sub1 = $day_entries[$s1][0]['sub_id'];
                        $sub2 = $day_entries[$s2][0]['sub_id'];
                        $type = $day_entries[$s1][0]['subject_type'];
                        if ($sub1 === $sub2 && in_array($type, ['LAB', 'NSS', 'MINI_PROJECT', 'MAJOR_PROJECT', 'COMMUNITY_PROJECT'])) {
                            return true;
                        }
                    }
                    return false;
                };

                // Morning: Slot 1 & 2
                if ($is2HrStart(1, 2)) {
                    echo '<td colspan="2" style="background: #fcfdfe;">';
                    echo renderRoomCard($day_entries[1], true);
                    echo '</td>';
                } else {
                    echo '<td style="text-align: center;">' . renderRoomCard(isset($day_entries[1]) ? $day_entries[1] : []) . '</td>';
                    echo '<td style="text-align: center;">' . renderRoomCard(isset($day_entries[2]) ? $day_entries[2] : []) . '</td>';
                }

                // Break
                echo '<td class="tt-col-break"><div class="break-icon">☕</div><div class="break-title">BREAK</div></td>';

                // Midday: Slot 3 & 4
                if ($is2HrStart(3, 4)) {
                    echo '<td colspan="2" style="background: #fcfdfe;">';
                    echo renderRoomCard($day_entries[3], true);
                    echo '</td>';
                } else {
                    echo '<td style="text-align: center;">' . renderRoomCard(isset($day_entries[3]) ? $day_entries[3] : []) . '</td>';
                    echo '<td style="text-align: center;">' . renderRoomCard(isset($day_entries[4]) ? $day_entries[4] : []) . '</td>';
                }

                // Lunch
                echo '<td class="tt-col-lunch"><div class="lunch-icon">🍽️</div><div class="lunch-title">LUNCH</div></td>';

                // Afternoon: Slot 5 & 6
                if ($is2HrStart(5, 6)) {
                    echo '<td colspan="2" style="background: #fcfdfe;">';
                    echo renderRoomCard($day_entries[5], true);
                    echo '</td>';
                } else {
                    echo '<td style="text-align: center;">' . renderRoomCard(isset($day_entries[5]) ? $day_entries[5] : []) . '</td>';
                    echo '<td style="text-align: center;">' . renderRoomCard(isset($day_entries[6]) ? $day_entries[6] : []) . '</td>';
                }

                // Slot 7
                echo '<td style="text-align: center;">' . renderRoomCard(isset($day_entries[7]) ? $day_entries[7] : []) . '</td>';

                echo '</tr>';
            }
            ?>
            </tbody>
           </table>
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

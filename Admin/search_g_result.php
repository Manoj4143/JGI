<?php 
include("../includes/session.php");
require("../includes/dbconnection.php");
require_once("../includes/cohort_helpers.php");
$prof = isset($_REQUEST['pG']) ? $_REQUEST['pG'] : '';
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta charset="UTF-8" />
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<link href="../includes/style_prac2.css" rel="stylesheet" type="text/css" />
<title>Scheduling System - Semester Schedule</title>
<style type="text/css">
<!--
.style3 {font-family: Georgia, "Times New Roman", Times, serif}
.style20 {font-family: Georgia, "Times New Roman", Times, serif; font-size: x-small; }
.style21 {font-size: x-small}
.style22 {font-family: Geneva, Arial, Helvetica, sans-serif}
.style30 {font-family: "Courier New", Courier, monospace}
.style4 {font-size: 11px;
	font-family: Verdana, Arial, Helvetica, sans-serif;
}
.style31 {color: #330099}
.style32 {	font-size: 16px;
	font-weight: bold;
}
-->
</style>
</head>
<body>
<div id="container">
<?php include("../includes/shedulo_shell.php"); ?>
  <div id="content">
	<div id="left" style="width: 100%;">
		<form name="form1" method="post">
				<?php
				$group = $_REQUEST['pG'];
				$c_info = getCohortDetails($conn, $group);
				$full_heading = "{$c_info['sem_label']} Timetable";
				$dept_title = $c_info['branch'];
				?>
				<div class="tt-hero-banner">
					<div>
						<div style="display: flex; align-items: center; gap: 10px; margin-bottom: 4px; flex-wrap: wrap;">
							<h2 style="margin: 0; color: #0f172a; font-size: 20px; font-weight: 800; text-align: left; letter-spacing: -0.2px;"><?php echo htmlspecialchars($full_heading); ?></h2>
							<span class="badge-pill badge-blue"><?php echo htmlspecialchars($dept_title); ?></span>
							<span style="font-size: 11px; color: #64748b; font-weight: 600;">(Group <?php echo htmlspecialchars($group); ?>)</span>
						</div>
						<div style="font-size: 12px; color: #64748b; font-weight: 500;">
							Branch: <strong style="color: #1e293b;"><?php echo htmlspecialchars($dept_title); ?></strong> &bull; <?php echo htmlspecialchars($c_info['sem_label']); ?> &bull; 6-Day Academic Schedule (Mon &ndash; Sat)
						</div>
					</div>
					<div style="display: flex; gap: 8px;">
						<a href="search_course.php" style="background: #f1f5f9; color: #334155; padding: 8px 14px; border-radius: 8px; text-decoration: none; font-size: 12.5px; font-weight: 600; border: 1px solid #cbd5e1; display: inline-flex; align-items: center; gap: 6px;">&larr; Switch Semester</a>
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
            $group = $_REQUEST['pG'];
            $days = [
                1 => 'MONDAY',
                2 => 'TUESDAY',
                3 => 'WEDNESDAY',
                4 => 'THURSDAY',
                5 => 'FRIDAY',
                6 => 'SATURDAY'
            ];

            // Render helper for single or unified container
            function renderTimetableCard($entries, $is_spanned = false) {
                if (empty($entries)) {
                    return '<span style="color: #cbd5e1; font-size: 14px; font-weight: 300;">—</span>';
                }
                $first = $entries[0];
                $stype = $first['subject_type'];
                $sub_code = htmlspecialchars($first['sub_code']);
                $sub_name = htmlspecialchars($first['sub_name']);
                $teacher = htmlspecialchars($first['teacher_name']);
                $room = htmlspecialchars($first['room_name']);

                if ($stype === 'LAB') {
                    $html = '<div class="card-lecture card-lab">';
                    $html .= '<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 4px;">';
                    $html .= '<span class="badge-pill badge-green">LAB (2 BATCHES)</span>';
                    $html .= '<span style="font-size: 9px; color: #047857; font-weight: 700; background: #d1fae5; padding: 2px 6px; border-radius: 4px;">2 PERIODS</span>';
                    $html .= '</div>';
                    $html .= '<span class="card-code">' . $sub_code . '</span>';
                    $html .= '<div class="card-name">' . $sub_name . '</div>';
                    $html .= '<div class="card-meta">👤 Incharge: <strong>' . $teacher . '</strong></div>';
                    $html .= '<div style="margin-top: 6px; padding-top: 5px; border-top: 1px dashed #a7f3d0; font-size: 10px;">';
                    foreach ($entries as $ent) {
                        $bname = preg_match('/\(Batch\s*\d+\)/', $ent['group_name'], $m) ? $m[0] : 'Batch';
                        $html .= '<div style="color: #047857; margin-top: 2px;">• <strong>' . $bname . ':</strong> ' . htmlspecialchars($ent['room_name']) . '</div>';
                    }
                    $html .= '</div></div>';
                    return $html;
                } elseif ($stype === 'NSS') {
                    $html = '<div class="card-lecture card-nss">';
                    $html .= '<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 4px;">';
                    $html .= '<span class="badge-pill badge-amber">NSS (AFTERNOON)</span>';
                    $html .= '<span style="font-size: 9px; color: #b45309; font-weight: 700; background: #fef3c7; padding: 2px 6px; border-radius: 4px;">2 PERIODS</span>';
                    $html .= '</div>';
                    $html .= '<span class="card-code">' . $sub_code . '</span>';
                    $html .= '<div class="card-name">' . $sub_name . '</div>';
                    $html .= '<div class="card-meta">👤 Teacher: <strong>' . $teacher . '</strong></div>';
                    $html .= '<div class="card-room">📍 Room: ' . $room . '</div>';
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
                    $html .= '<div class="card-room">📍 Room: ' . $room . '</div>';
                    $html .= '</div>';
                    return $html;
                } elseif ($stype === 'ONE_CREDIT') {
                    $html = '<div class="card-lecture card-credit">';
                    $html .= '<div style="margin-bottom: 4px;"><span class="badge-pill badge-purple">1-CREDIT (1 HR)</span></div>';
                    $html .= '<span class="card-code">' . $sub_code . '</span>';
                    $html .= '<div class="card-name">' . $sub_name . '</div>';
                    $html .= '<div class="card-meta">👤 Teacher: <strong>' . $teacher . '</strong></div>';
                    $html .= '<div class="card-room">📍 Room: ' . $room . '</div>';
                    $html .= '</div>';
                    return $html;
                } else {
                    $html = '<div class="card-lecture card-theory">';
                    $html .= '<div style="margin-bottom: 4px;"><span class="badge-pill badge-blue">THEORY</span></div>';
                    $html .= '<span class="card-code">' . $sub_code . '</span>';
                    $html .= '<div class="card-name">' . $sub_name . '</div>';
                    $html .= '<div class="card-meta">👤 Teacher: <strong>' . $teacher . '</strong></div>';
                    $html .= '<div class="card-room">📍 Room: ' . $room . '</div>';
                    $html .= '</div>';
                    return $html;
                }
            }

            foreach ($days as $day_id => $day_name) {
                echo '<tr>';
                echo '<td class="tt-day-col">' . $day_name . '</td>';

                // Fetch all entries for this day
                $day_entries = [];
                $q = "SELECT s.*, sub.sub_code, sub.sub_name, sub.subject_type, r.room_name, p.teacher_name 
                      FROM sched s 
                      JOIN subjects sub ON s.sub_id = sub.sub_id 
                      JOIN room r ON s.room_id = r.room_id 
                      JOIN profile p ON s.teacher_id = p.teacher_id 
                      WHERE (s.group_name = '$group' OR s.group_name LIKE '{$group} (%') 
                        AND s.day_id = $day_id 
                      ORDER BY s.group_name ASC";
                $res = mysqli_query($conn, $q);
                if ($res) {
                    while ($row = mysqli_fetch_assoc($res)) {
                        $day_entries[$row['time_s_id']][] = $row;
                    }
                }

                // Function to check if a 2-period subject starts at slot
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
                    echo renderTimetableCard($day_entries[1], true);
                    echo '</td>';
                } else {
                    echo '<td style="text-align: center;">' . renderTimetableCard(isset($day_entries[1]) ? $day_entries[1] : []) . '</td>';
                    echo '<td style="text-align: center;">' . renderTimetableCard(isset($day_entries[2]) ? $day_entries[2] : []) . '</td>';
                }

                // Fixed Morning Break Cell
                echo '<td class="tt-col-break"><div class="break-icon">☕</div><div class="break-title">BREAK</div></td>';

                // Midday: Slot 3 & 4
                if ($is2HrStart(3, 4)) {
                    echo '<td colspan="2" style="background: #fcfdfe;">';
                    echo renderTimetableCard($day_entries[3], true);
                    echo '</td>';
                } else {
                    echo '<td style="text-align: center;">' . renderTimetableCard(isset($day_entries[3]) ? $day_entries[3] : []) . '</td>';
                    echo '<td style="text-align: center;">' . renderTimetableCard(isset($day_entries[4]) ? $day_entries[4] : []) . '</td>';
                }

                // Fixed Lunch Break Cell
                echo '<td class="tt-col-lunch"><div class="lunch-icon">🍽️</div><div class="lunch-title">LUNCH</div></td>';

                // Afternoon: Slot 5 & 6
                if ($is2HrStart(5, 6)) {
                    echo '<td colspan="2" style="background: #fcfdfe;">';
                    echo renderTimetableCard($day_entries[5], true);
                    echo '</td>';
                } else {
                    echo '<td style="text-align: center;">' . renderTimetableCard(isset($day_entries[5]) ? $day_entries[5] : []) . '</td>';
                    echo '<td style="text-align: center;">' . renderTimetableCard(isset($day_entries[6]) ? $day_entries[6] : []) . '</td>';
                }

                // Slot 7
                echo '<td style="text-align: center;">' . renderTimetableCard(isset($day_entries[7]) ? $day_entries[7] : []) . '</td>';

                echo '</tr>';
            }
            ?>
            </tbody>
           </table>
		</form>
		  <h1>&nbsp;</h1>
			
	</div>
		<div id="program"></div>
		<div id="right">
		  <p>&nbsp;</p>
	</div>
		<p>&nbsp;</p>
		<p>&nbsp;</p>
		<div id="footerline">
		  <p align="center"><span class="style4"><a href="help.php">Help</a> | <a href="about_dev.php">Developer</a>| <a href="about_sched.php">Scheduling System</a></span></p>
	</div>
  </div>
	
	<div id="footer">Automatic Timetable Generator</div>	
  </div><!-- /shedulo-main-wrapper -->
</div><!-- /container -->
</body>
</html>


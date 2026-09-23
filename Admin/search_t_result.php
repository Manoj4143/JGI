<?php 
session_start();
include("../includes/session.php");
require("../includes/dbconnection.php");
$prof = isset($_REQUEST['pT']) ? intval($_REQUEST['pT']) : 0;

$userRole = $_SESSION['is']['role'] ?? (($_SESSION['is']['dept_id'] == 4 || ($_SESSION['is']['dept'] ?? 0) == 4) ? 'Admin' : 'Faculty');
$userRole = ucfirst(strtolower($userRole));
if ($userRole === 'Faculty') {
    $myTeacherId = intval($_SESSION['is']['teacher_id'] ?? 0);
    if ($myTeacherId > 0) {
        $prof = $myTeacherId;
    }
}

$proftemp = mysqli_fetch_array(mysqli_query($conn, "SELECT * FROM profile WHERE teacher_id = $prof"));
$profname = !empty($proftemp['teacher_name']) ? $proftemp['teacher_name'] : 'Faculty Member';
$rank = !empty($proftemp['academic_rank']) ? $proftemp['academic_rank'] : 'Professor';
$dept_id = isset($proftemp['dept_id']) ? intval($proftemp['dept_id']) : 0;
$dept_row = mysqli_fetch_assoc(mysqli_query($conn, "SELECT department FROM dept WHERE dept_id = $dept_id"));
$dept_name = !empty($dept_row['department']) ? $dept_row['department'] : 'Academic Department';
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta charset="UTF-8" />
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<link href="../includes/style_prac2.css" rel="stylesheet" type="text/css" />
<title>Scheduling System - Faculty Timetable - <?php echo htmlspecialchars($profname); ?></title>
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
							<h2 style="margin: 0; color: #0f172a; font-size: 20px; font-weight: 800; text-align: left; letter-spacing: -0.2px;"><?php echo htmlspecialchars($profname); ?></h2>
							<span class="badge-pill badge-green"><?php echo htmlspecialchars($rank); ?></span>
							<span class="badge-pill badge-blue"><?php echo htmlspecialchars($dept_name); ?></span>
						</div>
						<div style="font-size: 12px; color: #64748b; font-weight: 500;">
							Faculty Teaching Matrix &bull; 6-Day Weekly Schedule (Mon &ndash; Sat)
						</div>
					</div>
					<div style="display: flex; gap: 8px;">
						<?php if ($userRole === 'Faculty'): ?>
						<a href="request_timing.php" style="background: #059669; color: #ffffff; padding: 8px 14px; border-radius: 8px; text-decoration: none; font-size: 12.5px; font-weight: 600; display: inline-flex; align-items: center; gap: 6px;">📝 Request Timing Change</a>
						<?php else: ?>
						<a href="search_teacher.php" style="background: #f1f5f9; color: #334155; padding: 8px 14px; border-radius: 8px; text-decoration: none; font-size: 12.5px; font-weight: 600; border: 1px solid #cbd5e1; display: inline-flex; align-items: center; gap: 6px;">&larr; Switch Faculty</a>
						<?php endif; ?>
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

            function renderFacultyCard($entries, $is_spanned = false) {
                if (empty($entries)) {
                    return '<span style="color: #cbd5e1; font-size: 14px; font-weight: 300;">—</span>';
                }
                $first = $entries[0];
                $stype = $first['subject_type'];
                $sub_code = htmlspecialchars($first['sub_code']);
                $sub_name = htmlspecialchars($first['sub_name']);
                $room = htmlspecialchars($first['room_name']);
                $grp = htmlspecialchars($first['group_name']);

                if ($stype === 'LAB') {
                    $html = '<div class="card-lecture card-lab">';
                    $html .= '<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 4px;">';
                    $html .= '<span class="badge-pill badge-green">LAB (2 BATCHES)</span>';
                    $html .= '<span style="font-size: 9px; color: #047857; font-weight: 700; background: #d1fae5; padding: 2px 6px; border-radius: 4px;">2 PERIODS</span>';
                    $html .= '</div>';
                    $html .= '<span class="card-code">' . $sub_code . '</span>';
                    $html .= '<div class="card-name">' . $sub_name . '</div>';
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
                    $html .= '<div class="card-meta">👥 Cohort: <strong>' . $grp . '</strong></div>';
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
                    $html .= '<div class="card-meta">👥 Cohort: <strong>' . $grp . '</strong></div>';
                    $html .= '<div class="card-room">📍 Room: ' . $room . '</div>';
                    $html .= '</div>';
                    return $html;
                } elseif ($stype === 'ONE_CREDIT') {
                    $html = '<div class="card-lecture card-credit">';
                    $html .= '<div style="margin-bottom: 4px;"><span class="badge-pill badge-purple">1-CREDIT (1 HR)</span></div>';
                    $html .= '<span class="card-code">' . $sub_code . '</span>';
                    $html .= '<div class="card-name">' . $sub_name . '</div>';
                    $html .= '<div class="card-meta">👥 Cohort: <strong>' . $grp . '</strong></div>';
                    $html .= '<div class="card-room">📍 Room: ' . $room . '</div>';
                    $html .= '</div>';
                    return $html;
                } else {
                    $html = '<div class="card-lecture card-theory">';
                    $html .= '<div style="margin-bottom: 4px;"><span class="badge-pill badge-blue">THEORY</span></div>';
                    $html .= '<span class="card-code">' . $sub_code . '</span>';
                    $html .= '<div class="card-name">' . $sub_name . '</div>';
                    $html .= '<div class="card-meta">👥 Cohort: <strong>' . $grp . '</strong></div>';
                    $html .= '<div class="card-room">📍 Room: ' . $room . '</div>';
                    $html .= '</div>';
                    return $html;
                }
            }

            foreach ($days as $day_id => $day_name) {
                echo '<tr>';
                echo '<td class="tt-day-col">' . $day_name . '</td>';

                // Fetch all entries for this faculty on this day
                $day_entries = [];
                $q = "SELECT s.*, sub.sub_code, sub.sub_name, sub.subject_type, r.room_name 
                      FROM sched s 
                      JOIN subjects sub ON s.sub_id = sub.sub_id 
                      JOIN room r ON s.room_id = r.room_id 
                      WHERE s.teacher_id = $prof 
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
                    echo renderFacultyCard($day_entries[1], true);
                    echo '</td>';
                } else {
                    echo '<td style="text-align: center;">' . renderFacultyCard(isset($day_entries[1]) ? $day_entries[1] : []) . '</td>';
                    echo '<td style="text-align: center;">' . renderFacultyCard(isset($day_entries[2]) ? $day_entries[2] : []) . '</td>';
                }

                // Break
                echo '<td class="tt-col-break"><div class="break-icon">☕</div><div class="break-title">BREAK</div></td>';

                // Midday: Slot 3 & 4
                if ($is2HrStart(3, 4)) {
                    echo '<td colspan="2" style="background: #fcfdfe;">';
                    echo renderFacultyCard($day_entries[3], true);
                    echo '</td>';
                } else {
                    echo '<td style="text-align: center;">' . renderFacultyCard(isset($day_entries[3]) ? $day_entries[3] : []) . '</td>';
                    echo '<td style="text-align: center;">' . renderFacultyCard(isset($day_entries[4]) ? $day_entries[4] : []) . '</td>';
                }

                // Lunch
                echo '<td class="tt-col-lunch"><div class="lunch-icon">🍽️</div><div class="lunch-title">LUNCH</div></td>';

                // Afternoon: Slot 5 & 6
                if ($is2HrStart(5, 6)) {
                    echo '<td colspan="2" style="background: #fcfdfe;">';
                    echo renderFacultyCard($day_entries[5], true);
                    echo '</td>';
                } else {
                    echo '<td style="text-align: center;">' . renderFacultyCard(isset($day_entries[5]) ? $day_entries[5] : []) . '</td>';
                    echo '<td style="text-align: center;">' . renderFacultyCard(isset($day_entries[6]) ? $day_entries[6] : []) . '</td>';
                }

                // Slot 7
                echo '<td style="text-align: center;">' . renderFacultyCard(isset($day_entries[7]) ? $day_entries[7] : []) . '</td>';

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

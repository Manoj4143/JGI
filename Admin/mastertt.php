<?php 
include("../includes/session.php");
require("../includes/dbconnection.php");
require_once("../includes/cohort_helpers.php");
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta charset="UTF-8" />
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<link href="../includes/style_prac2.css" rel="stylesheet" type="text/css" />
<title>Scheduling System - Master Timetable</title>
</head>
<body>
<div id="container">
<?php include("../includes/shedulo_shell.php"); ?>
  <div id="content">
<?php if (isset($_GET['generated']) && $_GET['generated'] == 1): ?>
    <div style="background: #ecfdf5; border: 1px solid #10b981; border-radius: 8px; padding: 15px; margin-bottom: 20px; color: #065f46; box-shadow: 0 2px 4px rgba(0,0,0,0.05);">
        <h3 style="margin: 0 0 6px 0; color: #047857; font-size: 16px;">🚀 Timetable Generated Successfully!</h3>
        <p style="margin: 0; font-size: 13px; line-height: 1.5;">
            All constraint rules applied strictly:<br>
            • <strong>Contiguous Student Schedules:</strong> Cohort classes are packed compactly without middle gaps.<br>
            • <strong>Faculty Free-Period Guarantee:</strong> Faculty teaching load is spaced with free periods (maximum 2 consecutive teaching periods).<br>
            • <strong>Major Project:</strong> Scheduled as <strong>6 hours/week (2 hrs/day)</strong> strictly <strong>AFTER lunch break</strong>.<br>
            • <strong>Mini Project:</strong> Scheduled as <strong>4 hours/week (2 hrs/day)</strong> strictly <strong>AFTER lunch break</strong> (02:00 PM – 04:00 PM).<br>
            • <strong>Lab Sessions:</strong> Scheduled as <strong>2 continuous hours</strong>, divided into <strong>Batch 1 &amp; Batch 2</strong> in <strong>2 distinct Lab rooms</strong> simultaneously.<br>
            • <strong>NSS:</strong> Scheduled as <strong>2 continuous hours</strong> strictly in the <strong>Afternoon</strong>.<br>
            • <strong>6-Day Full Coverage:</strong> Load-balanced across Monday to Saturday.
        </p>
    </div>
<?php endif; ?>

<?php
// Determine selected group
$selected_group = isset($_GET['pG']) ? mysqli_real_escape_string($conn, $_GET['pG']) : '';
$group_query = mysqli_query($conn, "SELECT DISTINCT group_name FROM sched ORDER BY group_name ASC");
$all_groups = [];
while ($gr = mysqli_fetch_assoc($group_query)) {
    // Extract base group if batch is present
    $g_clean = preg_replace('/\s*\(Batch\s*\d+\)$/', '', $gr['group_name']);
    if (!empty($g_clean) && !in_array($g_clean, $all_groups)) {
        $all_groups[] = $g_clean;
    }
}
if (empty($selected_group) && count($all_groups) > 0) {
    $selected_group = $all_groups[0];
}

// Cohort details for selected group
$sel_cohort = getCohortDetails($conn, $selected_group);
?>

    <div class="tt-hero-banner">
        <div>
            <div style="display: flex; align-items: center; gap: 10px; margin-bottom: 4px; flex-wrap: wrap;">
                <h2 style="margin: 0; color: #0f172a; font-size: 20px; font-weight: 800; text-align: left; letter-spacing: -0.2px;">Master Academic Timetable</h2>
                <?php if (!empty($selected_group)): ?>
                    <span class="badge-pill badge-blue"><?php echo htmlspecialchars($sel_cohort['sem_label']); ?></span>
                    <span class="badge-pill" style="background: #e0f2fe; color: #0369a1;"><?php echo htmlspecialchars($sel_cohort['branch']); ?></span>
                    <span style="font-size: 11px; color: #64748b; font-weight: 600;">(Group <?php echo htmlspecialchars($selected_group); ?>)</span>
                <?php endif; ?>
            </div>
            <div style="font-size: 12px; color: #64748b; font-weight: 500;">
                Full Institutional Schedule &bull; 6-Day Academic Matrix with Dynamic Substitution
            </div>
        </div>
        <div style="display: flex; gap: 8px; align-items: center; flex-wrap: wrap;">
            <?php if (count($all_groups) > 1): ?>
                <form method="get" action="mastertt.php" style="margin: 0; display: inline-flex; align-items: center; gap: 6px;">
                    <label style="font-size: 12px; font-weight: 700; color: #475569;">Semester &amp; Branch:</label>
                    <select name="pG" onchange="this.form.submit()" style="padding: 7px 12px; border-radius: 8px; border: 1px solid #cbd5e1; background: #ffffff; font-size: 12px; font-weight: 600; color: #1e293b; max-width: 320px;">
                        <?php foreach ($all_groups as $grp): 
                            $c_info = getCohortDetails($conn, $grp);
                        ?>
                            <option value="<?php echo htmlspecialchars($grp); ?>" <?php if ($grp == $selected_group) echo 'selected'; ?>>
                                <?php echo htmlspecialchars($c_info['dropdown_label']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </form>
            <?php endif; ?>
            <a href="faculty_timing.php" style="background: #0d9488; color: #fff; padding: 8px 14px; border-radius: 8px; text-decoration: none; font-size: 12.5px; font-weight: 600; display: inline-flex; align-items: center; gap: 5px;">⏱️ Faculty Timing</a>
            <a href="generate_tt.php" onclick="return confirm('Generate a new timetable? This will re-calculate slots for all subjects.')" style="background: #2563eb; color: #fff; padding: 8px 14px; border-radius: 8px; text-decoration: none; font-size: 12.5px; font-weight: 600; display: inline-flex; align-items: center; gap: 5px;">⚡ Re-Generate</a>
            <button onclick="window.print()" style="background: #475569; color: #fff; border: none; padding: 8px 14px; border-radius: 8px; cursor: pointer; font-size: 12.5px; font-weight: 600; display: inline-flex; align-items: center; gap: 5px;">🖨️ Print</button>
        </div>
    </div>

    <!-- Transposed Timetable Grid (Days vertical, Timings horizontal, Highlighted Breaks) -->
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

        function renderMasterCard($entries, $is_spanned = false) {
            if (empty($entries)) {
                return '<span style="color: #cbd5e1; font-size: 14px; font-weight: 300;">—</span>';
            }
            $first = $entries[0];
            $sched_id = intval($first['sched_id']);
            $day_id = intval($first['day_id']);
            $slot_id = intval($first['time_s_id']);
            $stype = $first['subject_type'];
            $sub_code = htmlspecialchars($first['sub_code']);
            $sub_name = htmlspecialchars($first['sub_name']);
            $teacher = htmlspecialchars($first['teacher_name']);
            $teacher_id = intval($first['teacher_id']);
            $room = htmlspecialchars($first['room_name']);
            $room_id = intval($first['room_id']);

            $swap_btn = '<button onclick="openSwapModal(' . $sched_id . ', \'' . addslashes($sub_code) . '\', \'' . addslashes($teacher) . '\', ' . $teacher_id . ', ' . $day_id . ', ' . $slot_id . ', ' . $room_id . ')" style="background: #ffffff; border: 1px solid #cbd5e1; border-radius: 6px; padding: 2px 7px; font-size: 10px; font-weight: 600; cursor: pointer; color: #334155; margin-top: 4px; display: inline-flex; align-items: center; gap: 3px; box-shadow: 0 1px 2px rgba(0,0,0,0.04);" title="Swap / Substitute Faculty">🔄 Swap</button>';

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
                $html .= '</div>';
                $html .= '<div style="text-align: right;">' . $swap_btn . '</div>';
                $html .= '</div>';
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
                $html .= '<div style="text-align: right;">' . $swap_btn . '</div>';
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
                $html .= '<div style="text-align: right;">' . $swap_btn . '</div>';
                $html .= '</div>';
                return $html;
            } elseif ($stype === 'ONE_CREDIT') {
                $html = '<div class="card-lecture card-credit">';
                $html .= '<div style="margin-bottom: 4px;"><span class="badge-pill badge-purple">1-CREDIT (1 HR)</span></div>';
                $html .= '<span class="card-code">' . $sub_code . '</span>';
                $html .= '<div class="card-name">' . $sub_name . '</div>';
                $html .= '<div class="card-meta">👤 Teacher: <strong>' . $teacher . '</strong></div>';
                $html .= '<div class="card-room">📍 Room: ' . $room . '</div>';
                $html .= '<div style="text-align: right;">' . $swap_btn . '</div>';
                $html .= '</div>';
                return $html;
            } else {
                $html = '<div class="card-lecture card-theory">';
                $html .= '<div style="margin-bottom: 4px;"><span class="badge-pill badge-blue">THEORY</span></div>';
                $html .= '<span class="card-code">' . $sub_code . '</span>';
                $html .= '<div class="card-name">' . $sub_name . '</div>';
                $html .= '<div class="card-meta">👤 Teacher: <strong>' . $teacher . '</strong></div>';
                $html .= '<div class="card-room">📍 Room: ' . $room . '</div>';
                $html .= '<div style="text-align: right;">' . $swap_btn . '</div>';
                $html .= '</div>';
                return $html;
            }
        }

        $group_safe = mysqli_real_escape_string($conn, $selected_group);

        foreach ($days as $day_id => $day_name) {
            echo '<tr>';
            echo '<td class="tt-day-col">' . $day_name . '</td>';

            $day_entries = [];
            $q = "SELECT s.*, sub.sub_code, sub.sub_name, sub.subject_type, r.room_name, p.teacher_name 
                  FROM sched s 
                  JOIN subjects sub ON s.sub_id = sub.sub_id 
                  JOIN room r ON s.room_id = r.room_id 
                  JOIN profile p ON s.teacher_id = p.teacher_id 
                  WHERE (s.group_name = '$group_safe' OR s.group_name LIKE '{$group_safe} (%') 
                    AND s.day_id = $day_id 
                  ORDER BY s.group_name ASC";
            $res = mysqli_query($conn, $q);
            if ($res) {
                while ($row = mysqli_fetch_assoc($res)) {
                    $day_entries[$row['time_s_id']][] = $row;
                }
            }

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
                echo renderMasterCard($day_entries[1], true);
                echo '</td>';
            } else {
                echo '<td style="text-align: center;">' . renderMasterCard(isset($day_entries[1]) ? $day_entries[1] : []) . '</td>';
                echo '<td style="text-align: center;">' . renderMasterCard(isset($day_entries[2]) ? $day_entries[2] : []) . '</td>';
            }

            // Fixed Morning Break Cell
            echo '<td class="tt-col-break"><div class="break-icon">☕</div><div class="break-title">BREAK</div></td>';

            // Midday: Slot 3 & 4
            if ($is2HrStart(3, 4)) {
                echo '<td colspan="2" style="background: #fcfdfe;">';
                echo renderMasterCard($day_entries[3], true);
                echo '</td>';
            } else {
                echo '<td style="text-align: center;">' . renderMasterCard(isset($day_entries[3]) ? $day_entries[3] : []) . '</td>';
                echo '<td style="text-align: center;">' . renderMasterCard(isset($day_entries[4]) ? $day_entries[4] : []) . '</td>';
            }

            // Fixed Lunch Break Cell
            echo '<td class="tt-col-lunch"><div class="lunch-icon">🍽️</div><div class="lunch-title">LUNCH</div></td>';

            // Afternoon: Slot 5 & 6
            if ($is2HrStart(5, 6)) {
                echo '<td colspan="2" style="background: #fcfdfe;">';
                echo renderMasterCard($day_entries[5], true);
                echo '</td>';
            } else {
                echo '<td style="text-align: center;">' . renderMasterCard(isset($day_entries[5]) ? $day_entries[5] : []) . '</td>';
                echo '<td style="text-align: center;">' . renderMasterCard(isset($day_entries[6]) ? $day_entries[6] : []) . '</td>';
            }

            // Slot 7
            echo '<td style="text-align: center;">' . renderMasterCard(isset($day_entries[7]) ? $day_entries[7] : []) . '</td>';

            echo '</tr>';
        }
        ?>
        </tbody>
    </table>

    <!-- Swap / Substitute Lecture Modal -->
    <div id="swapModal" style="display: none; position: fixed; z-index: 9999; left: 0; top: 0; width: 100%; height: 100%; overflow: auto; background-color: rgba(0,0,0,0.5); backdrop-filter: blur(2px);">
        <div style="background-color: #fff; margin: 10% auto; padding: 25px; border-radius: 10px; width: 440px; box-shadow: 0 10px 25px rgba(0,0,0,0.2); font-family: inherit;">
            <div style="display: flex; justify-content: space-between; align-items: center; border-bottom: 1px solid #e2e8f0; padding-bottom: 10px; margin-bottom: 15px;">
                <h3 style="margin: 0; color: #1e3a8a; font-size: 16px;">🔄 Swap / Substitute Lecture</h3>
                <span onclick="closeSwapModal()" style="color: #94a3b8; font-size: 24px; font-weight: bold; cursor: pointer;">&times;</span>
            </div>
            
            <form id="swapForm" onsubmit="submitSwap(event)">
                <input type="hidden" id="modal_sched_id" name="sched_id" value="">
                
                <div style="margin-bottom: 12px;">
                    <label style="display: block; font-size: 12px; font-weight: bold; color: #475569; margin-bottom: 4px;">Subject</label>
                    <input type="text" id="modal_subject" readonly style="width: 100%; padding: 7px; border: 1px solid #cbd5e1; border-radius: 6px; background: #f8fafc; font-weight: bold; color: #1e293b;">
                </div>

                <div style="margin-bottom: 12px;">
                    <label style="display: block; font-size: 12px; font-weight: bold; color: #475569; margin-bottom: 4px;">Current Faculty</label>
                    <input type="text" id="modal_current_teacher" readonly style="width: 100%; padding: 7px; border: 1px solid #cbd5e1; border-radius: 6px; background: #f8fafc; color: #64748b;">
                </div>

                <div style="margin-bottom: 18px;">
                    <label style="display: block; font-size: 12px; font-weight: bold; color: #1e3a8a; margin-bottom: 4px;">
                        Substitute Faculty (Free in this Period) 
                        <span id="freeTeacherCount" style="background: #10b981; color: white; padding: 1px 6px; border-radius: 8px; font-size: 10px;">Loading...</span>
                    </label>
                    <select id="modal_new_teacher" name="new_teacher_id" style="width: 100%; padding: 8px; border: 1.5px solid #3b82f6; border-radius: 6px; background: #fff; font-size: 12px;">
                        <option value="">-- Choose Free Substitute Faculty --</option>
                    </select>
                </div>

                <div id="swapStatusMsg" style="margin-bottom: 12px; font-size: 12px; display: none;"></div>

                <div style="display: flex; justify-content: flex-end; gap: 8px;">
                    <button type="button" onclick="closeSwapModal()" style="padding: 7px 15px; border: 1px solid #cbd5e1; background: #f1f5f9; border-radius: 6px; cursor: pointer; font-size: 12px; font-weight: bold; color: #475569;">Cancel</button>
                    <button type="submit" id="btnSaveSwap" style="padding: 7px 18px; border: none; background: #2563eb; color: #fff; border-radius: 6px; cursor: pointer; font-size: 12px; font-weight: bold;">Confirm Swap</button>
                </div>
            </form>
        </div>
    </div>

    <script>
    function openSwapModal(schedId, subCode, teacherName, teacherId, dayId, slotId, roomId) {
        document.getElementById('modal_sched_id').value = schedId;
        document.getElementById('modal_subject').value = subCode;
        document.getElementById('modal_current_teacher').value = teacherName;
        document.getElementById('swapStatusMsg').style.display = 'none';
        
        const teacherSelect = document.getElementById('modal_new_teacher');
        teacherSelect.innerHTML = '<option value="">Loading available teachers...</option>';
        document.getElementById('freeTeacherCount').innerText = '...';

        document.getElementById('swapModal').style.display = 'block';

        // Fetch free teachers via AJAX
        fetch(`swap_lecture.php?action=get_free_teachers&day_id=${dayId}&slot_id=${slotId}&current_teacher=${teacherId}`)
            .then(res => res.json())
            .then(data => {
                teacherSelect.innerHTML = '<option value="">-- Select Substitute Faculty --</option>';
                if (data.status === 'success' && data.free_teachers.length > 0) {
                    document.getElementById('freeTeacherCount').innerText = data.free_teachers.length + ' Available';
                    data.free_teachers.forEach(t => {
                        const opt = document.createElement('option');
                        opt.value = t.teacher_id;
                        opt.textContent = `${t.teacher_name} (${t.academic_rank || 'Faculty'})`;
                        teacherSelect.appendChild(opt);
                    });
                } else {
                    document.getElementById('freeTeacherCount').innerText = '0 Available';
                    teacherSelect.innerHTML = '<option value="">No other free teachers in this period</option>';
                }
            })
            .catch(err => {
                console.error(err);
                teacherSelect.innerHTML = '<option value="">Failed to load teachers</option>';
            });
    }

    function closeSwapModal() {
        document.getElementById('swapModal').style.display = 'none';
    }

    function submitSwap(e) {
        e.preventDefault();
        const schedId = document.getElementById('modal_sched_id').value;
        const newTeacherId = document.getElementById('modal_new_teacher').value;
        const statusMsg = document.getElementById('swapStatusMsg');
        const btnSave = document.getElementById('btnSaveSwap');

        if (!newTeacherId) {
            alert('Please select a substitute faculty.');
            return;
        }

        btnSave.disabled = true;
        btnSave.innerText = 'Saving...';

        const formData = new FormData();
        formData.append('action', 'swap');
        formData.append('sched_id', schedId);
        formData.append('new_teacher_id', newTeacherId);

        fetch('swap_lecture.php', {
            method: 'POST',
            body: formData
        })
        .then(res => res.json())
        .then(data => {
            btnSave.disabled = false;
            btnSave.innerText = 'Confirm Swap';
            if (data.status === 'success') {
                statusMsg.style.display = 'block';
                statusMsg.style.color = '#059669';
                statusMsg.innerText = '✅ ' + data.message;
                setTimeout(() => {
                    location.reload();
                }, 800);
            } else {
                statusMsg.style.display = 'block';
                statusMsg.style.color = '#dc2626';
                statusMsg.innerText = '❌ ' + data.message;
            }
        })
        .catch(err => {
            btnSave.disabled = false;
            btnSave.innerText = 'Confirm Swap';
            alert('Error updating lecture swap.');
        });
    }

    window.onclick = function(event) {
        const modal = document.getElementById('swapModal');
        if (event.target == modal) {
            closeSwapModal();
        }
    }
    </script>

    <!-- Room Allocation Matrix -->
    <div style="margin-top: 30px;">
        <h3 style="color: #1e3a8a; font-size: 16px; margin-bottom: 12px;">🏢 Room Allocation &amp; Lab Utilization Overview</h3>
        <table width="100%" cellpadding="6" cellspacing="0" style="border-collapse: collapse; border: 1px solid #cbd5e1; background: #fff; font-size: 12px;">
            <thead>
                <tr style="background: #334155; color: #fff;">
                    <th style="padding: 8px; border: 1px solid #475569;">Room Name</th>
                    <th style="padding: 8px; border: 1px solid #475569;">Type</th>
                    <th style="padding: 8px; border: 1px solid #475569;">Capacity</th>
                    <th style="padding: 8px; border: 1px solid #475569;">Total Scheduled Hours</th>
                    <th style="padding: 8px; border: 1px solid #475569;">Action</th>
                </tr>
            </thead>
            <tbody>
            <?php
            $rooms_res = mysqli_query($conn, "SELECT * FROM room ORDER BY room_isNKN DESC, room_id ASC");
            while ($rm = mysqli_fetch_assoc($rooms_res)) {
                $rid = intval($rm['room_id']);
                $cnt_q = mysqli_query($conn, "SELECT COUNT(*) as total FROM sched WHERE room_id = $rid");
                $cnt_row = mysqli_fetch_assoc($cnt_q);
                $isLab = intval($rm['room_isNKN']) === 1;
                echo '<tr style="border-bottom: 1px solid #e2e8f0;">';
                echo '<td style="padding: 8px; border: 1px solid #e2e8f0; font-weight: bold;">' . htmlspecialchars($rm['room_name']) . '</td>';
                echo '<td style="padding: 8px; border: 1px solid #e2e8f0;">' . ($isLab ? '<span style="background: #10b981; color: white; padding: 2px 7px; border-radius: 4px; font-size: 10px; font-weight: bold;">COMPUTER LAB</span>' : '<span style="background: #64748b; color: white; padding: 2px 7px; border-radius: 4px; font-size: 10px; font-weight: bold;">LECTURE HALL</span>') . '</td>';
                echo '<td style="padding: 8px; border: 1px solid #e2e8f0; text-align: center;">' . htmlspecialchars($rm['room_size']) . '</td>';
                echo '<td style="padding: 8px; border: 1px solid #e2e8f0; text-align: center; font-weight: bold; color: #2563eb;">' . $cnt_row['total'] . ' hrs / week</td>';
                echo '<td style="padding: 8px; border: 1px solid #e2e8f0; text-align: center;"><a href="search_r_result.php?room=' . $rid . '" style="color: #2563eb; text-decoration: none; font-weight: bold;">View Room Schedule &rarr;</a></td>';
                echo '</tr>';
            }
            ?>
            </tbody>
        </table>
    </div>

  </div>
  <div id="footerline">
      <p align="center" class="style4"><a href="help.php">Help</a> | <a href="about_dev.php">Developer</a> | <a href="about_sched.php">Scheduling System</a></p>
  </div>
  <div id="footer">Automatic Timetable Generator System</div>
  </div><!-- /shedulo-main-wrapper -->
</div><!-- /container -->
</body>
</html>



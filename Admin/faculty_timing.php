<?php session_start(); ?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
<link href="../includes/style_prac2.css" rel="stylesheet" type="text/css" />
<title>Scheduling System - Faculty Specific Timing</title>
<style type="text/css">
.slot-badge {
    display: inline-block;
    padding: 6px 12px;
    border-radius: 6px;
    font-size: 11px;
    font-weight: 600;
    cursor: pointer;
    user-select: none;
    transition: all 0.2s;
    border: 1px solid #cbd5e1;
    text-align: center;
}
.slot-available {
    background: #ecfdf5;
    color: #065f46;
    border-color: #a7f3d0;
}
.slot-unavailable {
    background: #fef2f2;
    color: #991b1b;
    border-color: #fecaca;
    text-decoration: line-through;
}
</style>
</head>
<?php
include("../includes/session.php");
require("../includes/dbconnection.php");

$msg = '';
$selected_teacher = isset($_REQUEST['teacher_id']) ? intval($_REQUEST['teacher_id']) : 0;

// Fetch all teachers
$teachers_res = mysqli_query($conn, "SELECT teacher_id, teacher_name, acad_rank FROM profile ORDER BY teacher_name ASC");
$teachers = [];
while ($t = mysqli_fetch_assoc($teachers_res)) {
    $teachers[] = $t;
}

if ($selected_teacher === 0 && count($teachers) > 0) {
    $selected_teacher = intval($teachers[0]['teacher_id']);
}

// Handle Save Availability
if (isset($_POST['save_timing'])) {
    $teacher_id = intval($_POST['teacher_id']);
    $notes = mysqli_real_escape_string($conn, $_POST['notes']);

    // Clear existing rules for this teacher
    mysqli_query($conn, "DELETE FROM faculty_timing WHERE teacher_id = $teacher_id");

    $unavail = isset($_POST['unavail']) && is_array($_POST['unavail']) ? $_POST['unavail'] : [];
    
    foreach ($unavail as $key => $val) {
        // key format: "dayId_slotId"
        list($d, $s) = explode('_', $key);
        $d = intval($d);
        $s = intval($s);
        if ($d > 0 && $s > 0) {
            mysqli_query($conn, "INSERT INTO faculty_timing (teacher_id, day_id, slot_id, is_available, notes) VALUES ($teacher_id, $d, $s, 0, '$notes')");
        }
    }

    $msg = "Timing preferences successfully updated for faculty!";
    $selected_teacher = $teacher_id;
}

// Load existing unavailabilities
$unavailable_slots = [];
if ($selected_teacher > 0) {
    $cur_timing = mysqli_query($conn, "SELECT day_id, slot_id FROM faculty_timing WHERE teacher_id = $selected_teacher AND is_available = 0");
    if ($cur_timing) {
        while ($ct = mysqli_fetch_assoc($cur_timing)) {
            $unavailable_slots[$ct['day_id'] . '_' . $ct['slot_id']] = true;
        }
    }
}

$days = [
    1 => 'Monday',
    2 => 'Tuesday',
    3 => 'Wednesday',
    4 => 'Thursday',
    5 => 'Friday',
    6 => 'Saturday'
];

$slots = [
    1 => '09:00 - 10:00',
    2 => '10:00 - 11:00',
    3 => '11:15 - 12:15',
    4 => '12:15 - 01:15',
    5 => '02:00 - 03:00',
    6 => '03:00 - 04:00',
    7 => '04:00 - 05:00'
];
?>
<body>
<div id="container">
<?php include("../includes/shedulo_shell.php"); ?>
  <div id="content">
    <?php if (!empty($msg)): ?>
        <div style="background: #ecfdf5; border: 1px solid #10b981; color: #065f46; padding: 12px 18px; border-radius: 8px; margin-bottom: 20px; font-weight: bold; font-size: 13px;">
            ✅ <?php echo htmlspecialchars($msg); ?>
        </div>
    <?php endif; ?>

    <div class="tt-hero-banner">
        <div>
            <div style="display: flex; align-items: center; gap: 10px; margin-bottom: 4px;">
                <h2 style="margin: 0; color: #0f172a; font-size: 20px; font-weight: 800; text-align: left; letter-spacing: -0.2px;">Faculty Specific Timing &amp; Availability</h2>
                <span class="badge-pill badge-green">Scheduler Constraint</span>
            </div>
            <div style="font-size: 12px; color: #64748b; font-weight: 500;">
                Configure customized available / unavailable periods for individual faculty members across all 6 days (Mon–Sat).
            </div>
        </div>
        <div>
            <a href="mastertt.php" style="background: #2563eb; color: #ffffff; padding: 8px 14px; border-radius: 8px; text-decoration: none; font-size: 12.5px; font-weight: 600; display: inline-flex; align-items: center; gap: 6px;">&larr; Back to Timetable</a>
        </div>
    </div>

    <div style="background: #ffffff; border: 1px solid #e2e8f0; border-radius: 12px; padding: 20px 25px; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.04); margin-bottom: 25px;">

        <!-- Teacher Selection Dropdown -->
        <form method="get" action="faculty_timing.php" style="margin-bottom: 25px; display: flex; align-items: center; gap: 10px;">
            <label style="font-weight: bold; font-size: 13px; color: #334155;">Select Faculty Member:</label>
            <select name="teacher_id" onchange="this.form.submit()" style="padding: 7px 12px; border-radius: 6px; border: 1.5px solid #3b82f6; font-size: 13px; font-weight: bold; color: #1e3a8a;">
                <?php foreach ($teachers as $t): ?>
                    <option value="<?php echo $t['teacher_id']; ?>" <?php if ($t['teacher_id'] == $selected_teacher) echo 'selected'; ?>>
                        <?php echo htmlspecialchars($t['teacher_name']) . ' (' . htmlspecialchars($t['acad_rank'] ? $t['acad_rank'] : 'Faculty') . ')'; ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </form>

        <!-- Availability Form -->
        <form method="post" action="faculty_timing.php">
            <input type="hidden" name="teacher_id" value="<?php echo $selected_teacher; ?>">

            <div style="margin-bottom: 12px; display: flex; justify-content: space-between; align-items: center;">
                <span style="font-size: 12px; color: #64748b;">
                    💡 <em>Click any period slot checkbox to mark as <strong>Unavailable (Busy)</strong>. Unchecked means Available.</em>
                </span>
                <div style="display: flex; gap: 8px;">
                    <button type="button" onclick="markAll(false)" style="padding: 4px 10px; font-size: 11px; border: 1px solid #cbd5e1; background: #f8fafc; border-radius: 4px; cursor: pointer;">Mark All Available</button>
                </div>
            </div>

            <table width="100%" cellpadding="8" cellspacing="0" style="border-collapse: collapse; border: 2px solid #cbd5e1; font-size: 12px; margin-bottom: 20px;">
                <thead>
                    <tr style="background: #1e293b; color: white; text-align: center;">
                        <th style="padding: 10px; border: 1px solid #334155; width: 110px;">DAY</th>
                        <th style="padding: 10px; border: 1px solid #334155;">09:00 - 10:00</th>
                        <th style="padding: 10px; border: 1px solid #334155;">10:00 - 11:00</th>
                        <th style="padding: 10px; border: 2px solid #f59e0b; background: #fffbeb; color: #b45309; width: 75px;">☕ Break<br><span style="font-size: 9px;">11:00-11:15</span></th>
                        <th style="padding: 10px; border: 1px solid #334155;">11:15 - 12:15</th>
                        <th style="padding: 10px; border: 1px solid #334155;">12:15 - 01:15</th>
                        <th style="padding: 10px; border: 2px solid #ef4444; background: #fef2f2; color: #b91c1c; width: 75px;">🍽️ Lunch<br><span style="font-size: 9px;">01:15-02:00</span></th>
                        <th style="padding: 10px; border: 1px solid #334155;">02:00 - 03:00</th>
                        <th style="padding: 10px; border: 1px solid #334155;">03:00 - 04:00</th>
                        <th style="padding: 10px; border: 1px solid #334155;">04:00 - 05:00</th>
                    </tr>
                </thead>
                <tbody>
                <?php foreach ($days as $day_id => $day_name): ?>
                    <tr style="border-bottom: 1px solid #e2e8f0; text-align: center;">
                        <td style="padding: 10px; font-weight: bold; background: #f8fafc; border: 1px solid #cbd5e1; text-align: left; color: #1e293b;">
                            <?php echo $day_name; ?>
                        </td>
                        
                        <!-- Slot 1 -->
                        <?php for ($s = 1; $s <= 2; $s++): 
                            $is_unavail = isset($unavailable_slots[$day_id . '_' . $s]);
                        ?>
                            <td style="padding: 6px; border: 1px solid #cbd5e1; background: <?php echo $is_unavail ? '#fef2f2' : '#ffffff'; ?>;">
                                <label style="cursor: pointer; display: block;">
                                    <input type="checkbox" name="unavail[<?php echo $day_id . '_' . $s; ?>]" value="1" <?php if ($is_unavail) echo 'checked'; ?> onchange="toggleSlotBg(this)">
                                    <div style="font-size: 10px; color: <?php echo $is_unavail ? '#dc2626' : '#059669'; ?>; font-weight: bold; margin-top: 3px;">
                                        <?php echo $is_unavail ? 'Busy' : 'Free'; ?>
                                    </div>
                                </label>
                            </td>
                        <?php endfor; ?>

                        <!-- Break -->
                        <td style="border: 2px solid #f59e0b; background: #fffbeb; color: #b45309; font-size: 10px; font-weight: bold;">☕</td>

                        <!-- Slot 3 & 4 -->
                        <?php for ($s = 3; $s <= 4; $s++): 
                            $is_unavail = isset($unavailable_slots[$day_id . '_' . $s]);
                        ?>
                            <td style="padding: 6px; border: 1px solid #cbd5e1; background: <?php echo $is_unavail ? '#fef2f2' : '#ffffff'; ?>;">
                                <label style="cursor: pointer; display: block;">
                                    <input type="checkbox" name="unavail[<?php echo $day_id . '_' . $s; ?>]" value="1" <?php if ($is_unavail) echo 'checked'; ?> onchange="toggleSlotBg(this)">
                                    <div style="font-size: 10px; color: <?php echo $is_unavail ? '#dc2626' : '#059669'; ?>; font-weight: bold; margin-top: 3px;">
                                        <?php echo $is_unavail ? 'Busy' : 'Free'; ?>
                                    </div>
                                </label>
                            </td>
                        <?php endfor; ?>

                        <!-- Lunch -->
                        <td style="border: 2px solid #ef4444; background: #fef2f2; color: #b91c1c; font-size: 10px; font-weight: bold;">🍽️</td>

                        <!-- Slot 5, 6, 7 -->
                        <?php for ($s = 5; $s <= 7; $s++): 
                            $is_unavail = isset($unavailable_slots[$day_id . '_' . $s]);
                        ?>
                            <td style="padding: 6px; border: 1px solid #cbd5e1; background: <?php echo $is_unavail ? '#fef2f2' : '#ffffff'; ?>;">
                                <label style="cursor: pointer; display: block;">
                                    <input type="checkbox" name="unavail[<?php echo $day_id . '_' . $s; ?>]" value="1" <?php if ($is_unavail) echo 'checked'; ?> onchange="toggleSlotBg(this)">
                                    <div style="font-size: 10px; color: <?php echo $is_unavail ? '#dc2626' : '#059669'; ?>; font-weight: bold; margin-top: 3px;">
                                        <?php echo $is_unavail ? 'Busy' : 'Free'; ?>
                                    </div>
                                </label>
                            </td>
                        <?php endfor; ?>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>

            <div style="margin-bottom: 20px;">
                <label style="font-weight: bold; font-size: 12px; color: #334155; display: block; margin-bottom: 5px;">Special Remarks / Exemption Reason:</label>
                <input type="text" name="notes" placeholder="e.g. Dean administrative duties / Research lab on Saturday afternoons" style="width: 100%; padding: 8px; border: 1px solid #cbd5e1; border-radius: 6px; font-size: 12px;">
            </div>

            <div style="display: flex; gap: 10px;">
                <button type="submit" name="save_timing" style="background: #2563eb; color: white; border: none; padding: 10px 22px; border-radius: 6px; font-weight: bold; font-size: 13px; cursor: pointer; box-shadow: 0 2px 4px rgba(37,99,235,0.2);">
                    💾 Save Timing Preferences
                </button>
                <a href="generate_tt.php" onclick="return confirm('Re-generate timetable with these new availability rules?')" style="background: #059669; color: white; text-decoration: none; padding: 10px 20px; border-radius: 6px; font-weight: bold; font-size: 13px; display: inline-block;">
                    ⚡ Re-Generate Timetable
                </a>
            </div>
        </form>
    </div>
  </div>

  <div id="footerline">
      <p align="center" class="style4"><a href="help.php">Help</a> | <a href="about_dev.php">Developer</a> | <a href="about_sched.php">Scheduling System</a></p>
  </div>
  <div id="footer">Automatic Timetable Generator System</div>
</div>

<script>
function toggleSlotBg(cb) {
    const td = cb.closest('td');
    const label = td.querySelector('div');
    if (cb.checked) {
        td.style.background = '#fef2f2';
        label.style.color = '#dc2626';
        label.innerText = 'Busy';
    } else {
        td.style.background = '#ffffff';
        label.style.color = '#059669';
        label.innerText = 'Free';
    }
}

function markAll(unavail) {
    const checkboxes = document.querySelectorAll('input[type="checkbox"]');
    checkboxes.forEach(cb => {
        cb.checked = unavail;
        toggleSlotBg(cb);
    });
}
</script>
  </div><!-- /content -->
  </div><!-- /shedulo-main-wrapper -->
</div><!-- /container -->
</body>
</html>

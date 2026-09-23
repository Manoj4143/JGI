<?php 
session_start();
include("../includes/session.php");
require("../includes/dbconnection.php");

$currentRole = $_SESSION['is']['role'] ?? (($_SESSION['is']['dept_id'] == 4) ? 'Admin' : 'Faculty');
$teacher_id = intval($_SESSION['is']['teacher_id'] ?? 0);

// If teacher_id is not yet in session, look it up
if ($teacher_id <= 0) {
    $uname = mysqli_real_escape_string($conn, $_SESSION['is']['username']);
    $uRes = mysqli_query($conn, "SELECT teacher_id FROM user WHERE username = '$uname' OR email = '$uname'");
    if ($uRes && $uRow = mysqli_fetch_assoc($uRes)) {
        $teacher_id = intval($uRow['teacher_id'] ?? 0);
        $_SESSION['is']['teacher_id'] = $teacher_id;
    }
}
if ($teacher_id <= 0) {
    $uname = mysqli_real_escape_string($conn, $_SESSION['is']['username']);
    $pRes = mysqli_query($conn, "SELECT teacher_id FROM profile WHERE teacher_name LIKE '%$uname%' OR email = '$uname' LIMIT 1");
    if ($pRes && $pRow = mysqli_fetch_assoc($pRes)) {
        $teacher_id = intval($pRow['teacher_id']);
        $_SESSION['is']['teacher_id'] = $teacher_id;
    }
}

$msg = '';
$error = '';

if (isset($_POST['btnSubmitRequest'])) {
    $day_id = intval($_POST['day_id'] ?? 0);
    $slot_id = intval($_POST['slot_id'] ?? 0);
    $pref_day_id = intval($_POST['pref_day_id'] ?? 0);
    $pref_slot_id = intval($_POST['pref_slot_id'] ?? 0);
    $sub_id = intval($_POST['sub_id'] ?? 0);
    $req_type = mysqli_real_escape_string($conn, trim($_POST['request_type'] ?? 'slot_change'));
    $reason = mysqli_real_escape_string($conn, trim($_POST['reason'] ?? ''));

    if ($teacher_id <= 0) {
        $error = 'Your account is not linked to a faculty profile. Please contact Admin.';
    } elseif ($day_id <= 0 || $slot_id <= 0) {
        $error = 'Please select your Actual Current Day and Period / Time Slot.';
    } elseif ($req_type === 'slot_change' && ($pref_day_id <= 0 || $pref_slot_id <= 0)) {
        $error = 'For a Class Slot Change, please also select the New Timing You Need (Preferred Day & Period).';
    } elseif (empty($reason)) {
        $error = 'Please provide an official reason for your timing request.';
    } else {
        $sub_val = ($sub_id > 0) ? $sub_id : "NULL";
        $pref_day_val = ($pref_day_id > 0) ? $pref_day_id : "NULL";
        $pref_slot_val = ($pref_slot_id > 0) ? $pref_slot_id : "NULL";

        $ins = "INSERT INTO timing_requests (teacher_id, request_type, day_id, slot_id, pref_day_id, pref_slot_id, sub_id, reason, status) 
                VALUES ($teacher_id, '$req_type', $day_id, $slot_id, $pref_day_val, $pref_slot_val, $sub_val, '$reason', 'Pending')";
        
        if (mysqli_query($conn, $ins)) {
            $msg = 'Your timing request has been submitted successfully! The Administrator will review and verify it.';
        } else {
            $error = 'Database error: ' . mysqli_error($conn);
        }
    }
}

$dayNames = [
    1 => 'Monday',
    2 => 'Tuesday',
    3 => 'Wednesday',
    4 => 'Thursday',
    5 => 'Friday',
    6 => 'Saturday'
];

$slotTimes = [
    1 => '09:00 AM - 10:00 AM (Period 1)',
    2 => '10:00 AM - 11:00 AM (Period 2)',
    3 => '11:15 AM - 12:15 PM (Period 3)',
    4 => '12:15 PM - 01:15 PM (Period 4)',
    5 => '02:00 PM - 03:00 PM (Period 5)',
    6 => '03:00 PM - 04:00 PM (Period 6)',
    7 => '04:00 PM - 05:00 PM (Period 7)',
    8 => '05:00 PM - 06:00 PM (Period 8)',
];

// Fetch faculty's current scheduled lectures for instant one-click selection
$scheduled_lectures = [];
if ($teacher_id > 0) {
    $scQuery = "SELECT DISTINCT s.day_id, s.time_s_id, s.sub_id, sub.sub_code, sub.sub_name 
                FROM sched s 
                JOIN subjects sub ON s.sub_id = sub.sub_id 
                WHERE s.teacher_id = $teacher_id 
                ORDER BY s.day_id ASC, s.time_s_id ASC";
    $scRes = mysqli_query($conn, $scQuery);
    if ($scRes) {
        while ($scRow = mysqli_fetch_assoc($scRes)) {
            $scheduled_lectures[] = $scRow;
        }
    }
}

// Fetch all submitted requests for this faculty
$myRequests = [];
if ($teacher_id > 0) {
    $reqQuery = "SELECT tr.*, s.sub_code, s.sub_name 
                 FROM timing_requests tr 
                 LEFT JOIN subjects s ON tr.sub_id = s.sub_id 
                 WHERE tr.teacher_id = $teacher_id 
                 ORDER BY tr.request_id DESC";
    $rRes = mysqli_query($conn, $reqQuery);
    if ($rRes) {
        while ($rRow = mysqli_fetch_assoc($rRes)) {
            $myRequests[] = $rRow;
        }
    }
}
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=5.0, user-scalable=yes" />
<link href="../includes/style_prac2.css" rel="stylesheet" type="text/css" />
<title>Scheduling System - Faculty Timing Adjustment</title>
<style type="text/css">
.sturdy-container {
    width: 100% !important;
    max-width: 100% !important;
    box-sizing: border-box;
    margin: 0;
    padding: 0;
}
.sturdy-card {
    background: #ffffff;
    border: 1px solid #e2e8f0;
    border-radius: 16px;
    padding: 24px;
    box-shadow: 0 4px 20px -2px rgba(15, 23, 42, 0.05);
    margin-bottom: 24px;
    width: 100%;
    box-sizing: border-box;
}
.timing-instruction-box {
    background: #f0fdf4;
    border: 1.5px solid #86efac;
    border-radius: 12px;
    padding: 16px 20px;
    margin-bottom: 20px;
    width: 100%;
    box-sizing: border-box;
}
.timing-step-badge {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 26px;
    height: 26px;
    border-radius: 50%;
    background: #0284c7;
    color: white;
    font-weight: 800;
    font-size: 13px;
    margin-right: 8px;
}
.timing-step-badge.green {
    background: #16a34a;
}
.timing-card {
    background: #f8fafc;
    border: 1.5px solid #e2e8f0;
    border-radius: 12px;
    padding: 18px;
    transition: all 0.2s ease;
}
.timing-card.active {
    background: #ffffff;
    border-color: #3b82f6;
    box-shadow: 0 4px 16px rgba(59, 130, 246, 0.08);
}
.timing-card.desired {
    border-color: #86efac;
    background: #f0fdf4;
}
.timing-card.disabled {
    opacity: 0.55;
    pointer-events: none;
    background: #f1f5f9;
}

/* Sturdy Desktop Table */
.desktop-requests-table {
    display: block;
    width: 100%;
    overflow-x: auto;
}
.requests-table {
    width: 100%;
    border-collapse: collapse;
    font-size: 13px;
    text-align: left;
}
.requests-table th {
    background: #0f172a;
    color: #f8fafc;
    font-size: 11.5px;
    font-weight: 800;
    letter-spacing: 0.6px;
    text-transform: uppercase;
    padding: 14px 16px;
    border-bottom: 2px solid #1e293b;
    border-right: 1px solid #1e293b;
    white-space: nowrap;
}
.requests-table th:last-child {
    border-right: none;
}
.requests-table td {
    padding: 14px 16px;
    border-bottom: 1px solid #f1f5f9;
    border-right: 1px solid #f8fafc;
    vertical-align: middle;
    color: #334155;
    background: #ffffff;
}
.requests-table tr:hover td {
    background: #fafbfc;
}
.requests-table tr:last-child td {
    border-bottom: none;
}
.timing-badge-from {
    background: #eff6ff;
    border: 1px solid #bfdbfe;
    border-radius: 8px;
    padding: 6px 10px;
    display: inline-block;
    min-width: 145px;
}
.timing-badge-to {
    background: #f0fdf4;
    border: 1px solid #bbf7d0;
    border-radius: 8px;
    padding: 6px 10px;
    display: inline-block;
    min-width: 145px;
}
.reason-bubble {
    background: #f8fafc;
    border-left: 3px solid #3b82f6;
    padding: 8px 12px;
    border-radius: 0 8px 8px 0;
    font-size: 12.5px;
    color: #1e293b;
    line-height: 1.45;
    max-width: 260px;
    word-break: break-word;
}

/* Mobile-Specific Elements */
.mobile-requests-list {
    display: none;
}

@media (max-width: 768px) {
    .sturdy-card {
        padding: 16px;
        border-radius: 12px;
    }
    .form-grid-responsive {
        grid-template-columns: 1fr !important;
        gap: 14px !important;
    }
    .timing-cards-responsive {
        grid-template-columns: 1fr !important;
        gap: 14px !important;
    }
    .timing-card {
        padding: 14px;
    }
    select, input, textarea {
        min-height: 44px;
        font-size: 13.5px !important;
    }
    .btn-submit-main {
        width: 100% !important;
        min-height: 48px;
        justify-content: center !important;
    }
    .desktop-requests-table {
        display: none !important;
    }
    .mobile-requests-list {
        display: flex !important;
        flex-direction: column;
        gap: 14px;
        padding: 14px;
    }
    .mobile-req-card {
        background: #ffffff;
        border: 1.5px solid #e2e8f0;
        border-radius: 14px;
        padding: 14px;
        box-shadow: 0 2px 8px rgba(15, 23, 42, 0.04);
        box-sizing: border-box;
    }
    html.dark .mobile-req-card {
        background: #111726;
        border-color: rgba(255, 255, 255, 0.08);
    }
    .mobile-req-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        margin-bottom: 8px;
        gap: 8px;
    }
    .mobile-id-badge {
        background: #f1f5f9;
        color: #475569;
        font-weight: 800;
        font-size: 12px;
        padding: 4px 8px;
        border-radius: 6px;
    }
    html.dark .mobile-id-badge {
        background: #1e293b;
        color: #94a3b8;
    }
    .mobile-route-box {
        display: flex;
        flex-direction: column;
        gap: 8px;
        background: #f8fafc;
        border: 1px solid #e2e8f0;
        border-radius: 10px;
        padding: 10px 12px;
        margin: 12px 0;
    }
    html.dark .mobile-route-box {
        background: #0a0e16;
        border-color: rgba(255, 255, 255, 0.06);
    }
    .mobile-route-from {
        background: #eff6ff;
        border: 1px solid #bfdbfe;
        border-radius: 8px;
        padding: 8px 12px;
    }
    html.dark .mobile-route-from {
        background: #0d1e38;
        border-color: #1e3a8a;
    }
    .mobile-route-to {
        background: #f0fdf4;
        border: 1px solid #bbf7d0;
        border-radius: 8px;
        padding: 8px 12px;
    }
    html.dark .mobile-route-to {
        background: #062b1d;
        border-color: #047857;
    }
}
</style>
<script type="text/javascript">
function onLectureSelected(sel) {
    var val = sel.value;
    if (!val) return;
    var parts = val.split('_'); // format: dayId_slotId_subId
    var dayId = parts[0];
    var slotId = parts[1];
    var subId = parts[2];

    document.getElementById('actual_day').value = dayId;
    document.getElementById('actual_slot').value = slotId;
    var subSelect = document.getElementById('subject_select');
    if (subSelect) {
        subSelect.value = subId;
    }

    var tip = document.getElementById('quick_pick_tip');
    if (tip) {
        tip.style.display = 'block';
        tip.innerHTML = '✅ Auto-filled Actual Current Timing: <strong>' + sel.options[sel.selectedIndex].text + '</strong>. Now choose the New Timing You Need below!';
    }
}

function onRequestTypeChange() {
    var reqType = document.getElementById('request_type').value;
    var desiredCard = document.getElementById('desired_timing_card');
    var desiredDay = document.getElementById('pref_day_id');
    var desiredSlot = document.getElementById('pref_slot_id');
    var desiredNote = document.getElementById('desired_note');

    if (reqType === 'unavailability') {
        desiredCard.classList.add('disabled');
        desiredDay.removeAttribute('required');
        desiredSlot.removeAttribute('required');
        desiredDay.value = '';
        desiredSlot.value = '';
        desiredNote.style.display = 'block';
    } else {
        desiredCard.classList.remove('disabled');
        desiredDay.setAttribute('required', 'required');
        desiredSlot.setAttribute('required', 'required');
        desiredNote.style.display = 'none';
    }
}
</script>
</head>
<body>
<div id="container">
<?php include("../includes/shedulo_shell.php"); ?>
  <div id="content">
	<div id="left" class="sturdy-container">
		
		<!-- TOP HERO BANNER -->
		<div class="tt-hero-banner" style="margin-top: 0;">
			<div>
				<div style="display: flex; align-items: center; gap: 10px; margin-bottom: 4px; flex-wrap: wrap;">
					<h2 style="margin: 0; color: #0f172a; font-size: 21px; font-weight: 800; text-align: left; letter-spacing: -0.3px;">Faculty Timing Change Request</h2>
					<span class="badge-pill badge-blue">Schedule Adjustment</span>
				</div>
				<div style="font-size: 13px; color: #64748b; font-weight: 500;">
					Easily reschedule an assigned lecture or report unavailability for Admin verification.
				</div>
			</div>
			<div>
				<?php if ($teacher_id > 0): ?>
				<a href="search_t_result.php?pT=<?php echo $teacher_id; ?>" style="background: #2563eb; color: #ffffff; padding: 9px 16px; border-radius: 8px; text-decoration: none; font-size: 13px; font-weight: 700; display: inline-flex; align-items: center; gap: 6px; box-shadow: 0 2px 6px rgba(37, 99, 235, 0.25);">
					📅 View My Full Timetable
				</a>
				<?php endif; ?>
			</div>
		</div>

		<?php if (!empty($msg)): ?>
		<div style="background: #ecfdf5; border: 1.5px solid #a7f3d0; color: #065f46; padding: 14px 20px; border-radius: 10px; margin: 16px 0; font-size: 14px; font-weight: 600; display: flex; align-items: center; gap: 8px;">
			<span>✅</span> <?php echo htmlspecialchars($msg); ?>
		</div>
		<?php endif; ?>
		<?php if (!empty($error)): ?>
		<div style="background: #fef2f2; border: 1.5px solid #fecaca; color: #991b1b; padding: 14px 20px; border-radius: 10px; margin: 16px 0; font-size: 14px; font-weight: 600; display: flex; align-items: center; gap: 8px;">
			<span>⚠️</span> <?php echo htmlspecialchars($error); ?>
		</div>
		<?php endif; ?>

		<!-- INSTRUCTION GUIDE BOX -->
		<div class="timing-instruction-box">
			<div style="display: flex; align-items: flex-start; gap: 14px;">
				<div style="font-size: 24px;">💡</div>
				<div>
					<h4 style="margin: 0 0 6px 0; color: #166534; font-size: 15px; font-weight: 800;">Quick Guide: Which Timings Should You Enter?</h4>
					<div style="font-size: 13px; color: #14532d; line-height: 1.6;">
						<strong>1. Actual Current Timing (From):</strong> The exact day &amp; period where your class is <em>currently scheduled</em> (or the slot you cannot teach).<br/>
						<strong>2. Timing You Need (To / Preferred Slot):</strong> The new day &amp; period where you <em>want this class shifted to</em>.
					</div>
				</div>
			</div>
		</div>

		<!-- SUBMISSION FORM CARD -->
		<form action="request_timing.php" method="post">
		  <div class="sturdy-card">
			<h3 style="margin-top: 0; margin-bottom: 18px; color: #1e3a8a; font-size: 17px; font-weight: 800; display: flex; align-items: center; gap: 8px;">
				<span>📝</span> Submit Timing Change Request
			</h3>

			<!-- Quick-select from existing scheduled classes -->
			<?php if (!empty($scheduled_lectures)): ?>
			<div style="background: #eff6ff; border: 1.5px dashed #93c5fd; border-radius: 10px; padding: 14px 18px; margin-bottom: 20px;">
				<label style="display: block; font-weight: 700; font-size: 13px; color: #1e40af; margin-bottom: 6px;">
					⚡ Quick-Fill: Select from your current scheduled lectures (Optional):
				</label>
				<select onchange="onLectureSelected(this)" style="width: 100%; box-sizing: border-box; padding: 10px 14px; font-size: 13px; border-radius: 8px; border: 1.5px solid #93c5fd; background: #ffffff; font-weight: 600; color: #1e3a8a;">
					<option value="">-- Click to pick one of your scheduled classes to auto-fill --</option>
					<?php foreach ($scheduled_lectures as $lec): 
						$dName = $dayNames[$lec['day_id']] ?? "Day {$lec['day_id']}";
						$sName = $slotTimes[$lec['time_s_id']] ?? "Slot {$lec['time_s_id']}";
						$optVal = "{$lec['day_id']}_{$lec['time_s_id']}_{$lec['sub_id']}";
					?>
					<option value="<?php echo $optVal; ?>">
						[<?php echo $dName; ?> &bull; <?php echo $sName; ?>] <?php echo htmlspecialchars($lec['sub_code']); ?> - <?php echo htmlspecialchars($lec['sub_name']); ?>
					</option>
					<?php endforeach; ?>
				</select>
				<div id="quick_pick_tip" style="display: none; margin-top: 8px; font-size: 12.5px; color: #15803d; font-weight: 700;"></div>
			</div>
			<?php endif; ?>
			
			<div class="form-grid-responsive" style="display: grid; grid-template-columns: 1fr 1fr; gap: 18px; margin-bottom: 22px;">
				<div>
					<label style="display: block; font-weight: 700; font-size: 13px; color: #334155; margin-bottom: 6px;">Request Type: *</label>
					<select id="request_type" name="request_type" onchange="onRequestTypeChange()" required style="width: 100%; box-sizing: border-box; padding: 10px 14px; font-size: 13px; border-radius: 8px; border: 1.5px solid #cbd5e1; background: white; font-weight: 600;">
						<option value="slot_change">🔄 Class Slot Change / Reschedule (Move to another slot)</option>
						<option value="unavailability">🚫 Faculty Unavailability / Leave (Cannot teach at this time)</option>
						<option value="day_off">📅 Day-Specific Rescheduling</option>
					</select>
				</div>

				<div>
					<label style="display: block; font-weight: 700; font-size: 13px; color: #334155; margin-bottom: 6px;">Subject / Course (Optional):</label>
					<select id="subject_select" name="sub_id" style="width: 100%; box-sizing: border-box; padding: 10px 14px; font-size: 13px; border-radius: 8px; border: 1.5px solid #cbd5e1; background: white;">
						<option value="0">-- Any / General Schedule --</option>
						<?php 
						if ($teacher_id > 0) {
							$sRes = mysqli_query($conn, "SELECT sub_id, sub_code, sub_name FROM subjects WHERE instructor = $teacher_id ORDER BY sub_code ASC");
							while ($sRow = mysqli_fetch_assoc($sRes)) {
								echo '<option value="' . $sRow['sub_id'] . '">' . htmlspecialchars($sRow['sub_code']) . ' - ' . htmlspecialchars($sRow['sub_name']) . '</option>';
							}
						}
						?>
					</select>
				</div>
			</div>

			<!-- TWO SIDE-BY-SIDE CARDS: ACTUAL CURRENT TIMING vs TIMING YOU NEED -->
			<div class="timing-cards-responsive" style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 22px;">
				
				<!-- CARD 1: ACTUAL CURRENT TIMING -->
				<div class="timing-card active" id="actual_timing_card">
					<div style="display: flex; align-items: center; margin-bottom: 14px;">
						<span class="timing-step-badge">1</span>
						<div>
							<h4 style="margin: 0; color: #0f172a; font-size: 14.5px; font-weight: 800;">Actual Current Timing (From)</h4>
							<div style="font-size: 11.5px; color: #64748b;">The slot where class is currently scheduled or when you are unavailable.</div>
						</div>
					</div>

					<div style="margin-bottom: 14px;">
						<label style="display: block; font-weight: 700; font-size: 12.5px; color: #334155; margin-bottom: 6px;">Current Day of Week: *</label>
						<select id="actual_day" name="day_id" required style="width: 100%; box-sizing: border-box; padding: 10px 12px; font-size: 13px; border-radius: 8px; border: 1.5px solid #cbd5e1; background: white; font-weight: 600;">
							<option value="">-- Select Current Day --</option>
							<?php foreach ($dayNames as $dId => $dName): ?>
							<option value="<?php echo $dId; ?>"><?php echo $dName; ?></option>
							<?php endforeach; ?>
						</select>
					</div>

					<div>
						<label style="display: block; font-weight: 700; font-size: 12.5px; color: #334155; margin-bottom: 6px;">Current Period / Time Slot: *</label>
						<select id="actual_slot" name="slot_id" required style="width: 100%; box-sizing: border-box; padding: 10px 12px; font-size: 13px; border-radius: 8px; border: 1.5px solid #cbd5e1; background: white; font-weight: 600;">
							<option value="">-- Select Current Period --</option>
							<?php foreach ($slotTimes as $sId => $sName): ?>
							<option value="<?php echo $sId; ?>"><?php echo $sName; ?></option>
							<?php endforeach; ?>
						</select>
					</div>
				</div>

				<!-- CARD 2: TIMING YOU NEED (PREFERRED NEW TIMING) -->
				<div class="timing-card desired" id="desired_timing_card">
					<div style="display: flex; align-items: center; margin-bottom: 14px;">
						<span class="timing-step-badge green">2</span>
						<div>
							<h4 style="margin: 0; color: #166534; font-size: 14.5px; font-weight: 800;">Timing You Need (To / Preferred Slot)</h4>
							<div style="font-size: 11.5px; color: #15803d;">The replacement day &amp; period you want your class shifted to.</div>
						</div>
					</div>

					<div style="margin-bottom: 14px;">
						<label style="display: block; font-weight: 700; font-size: 12.5px; color: #166534; margin-bottom: 6px;">Preferred New Day: *</label>
						<select id="pref_day_id" name="pref_day_id" required style="width: 100%; box-sizing: border-box; padding: 10px 12px; font-size: 13px; border-radius: 8px; border: 1.5px solid #86efac; background: white; font-weight: 600;">
							<option value="">-- Select Preferred Day --</option>
							<?php foreach ($dayNames as $dId => $dName): ?>
							<option value="<?php echo $dId; ?>"><?php echo $dName; ?></option>
							<?php endforeach; ?>
						</select>
					</div>

					<div>
						<label style="display: block; font-weight: 700; font-size: 12.5px; color: #166534; margin-bottom: 6px;">Preferred New Period / Time Slot: *</label>
						<select id="pref_slot_id" name="pref_slot_id" required style="width: 100%; box-sizing: border-box; padding: 10px 12px; font-size: 13px; border-radius: 8px; border: 1.5px solid #86efac; background: white; font-weight: 600;">
							<option value="">-- Select Preferred Period --</option>
							<?php foreach ($slotTimes as $sId => $sName): ?>
							<option value="<?php echo $sId; ?>"><?php echo $sName; ?></option>
							<?php endforeach; ?>
						</select>
					</div>

					<div id="desired_note" style="display: none; margin-top: 10px; font-size: 12px; color: #64748b; font-style: italic;">
						ℹ️ Not needed for general unavailability requests.
					</div>
				</div>

			</div>

			<div style="margin-bottom: 22px;">
				<label style="display: block; font-weight: 700; font-size: 13px; color: #334155; margin-bottom: 6px;">Official Reason for Timing Change: *</label>
				<textarea name="reason" rows="3" required style="width: 100%; box-sizing: border-box; padding: 12px 14px; font-size: 13px; border-radius: 8px; border: 1.5px solid #cbd5e1;" placeholder="Please explain clearly why this timing change is required (e.g. lab conflict, official administrative duties, external evaluation, medical appointment)..."></textarea>
			</div>

			<div>
				<button type="submit" name="btnSubmitRequest" class="btn-submit-main" style="background: #2563eb; color: #ffffff; border: none; padding: 12px 24px; border-radius: 8px; font-size: 13.5px; font-weight: 700; cursor: pointer; display: inline-flex; align-items: center; gap: 8px; box-shadow: 0 4px 12px rgba(37, 99, 235, 0.25);">
					<span>📨 Submit Timing Request to Admin</span>
				</button>
			</div>
		  </div>
		</form>

		<!-- PREVIOUS REQUESTS ROSTER -->
		<div class="sturdy-card" style="padding: 0; overflow: hidden;">
			<div style="padding: 16px 24px; background: #f8fafc; border-bottom: 1px solid #e2e8f0; display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 10px;">
				<h3 style="margin: 0; color: #0f172a; font-size: 16px; font-weight: 800;">📜 My Submitted Requests &amp; Status</h3>
				<span style="font-size: 12.5px; color: #64748b; font-weight: 600;">Track Admin Decisions in Real-Time</span>
			</div>
			
			<!-- 1. DESKTOP ROSTER TABLE VIEW -->
			<div class="desktop-requests-table">
			<table class="requests-table">
				<thead>
					<tr>
						<th style="width: 50px; text-align: center;">ID</th>
						<th style="min-width: 140px;">Type</th>
						<th style="min-width: 140px;">Subject</th>
						<th style="min-width: 175px;">Actual Current Timing</th>
						<th style="min-width: 175px;">Timing You Need</th>
						<th style="min-width: 230px;">Reason</th>
						<th style="width: 110px; text-align: center;">Status</th>
						<th style="min-width: 200px;">Admin Remarks</th>
					</tr>
				</thead>
				<tbody>
				<?php 
				if (empty($myRequests)) {
					echo '<tr><td colspan="8" style="text-align: center; padding: 40px 20px; color: #64748b; font-size: 13.5px;">No timing requests submitted yet.</td></tr>';
				} else {
					foreach ($myRequests as $rRow) {
						$stat = ucfirst(strtolower($rRow['status'] ?? 'pending'));
						$actualDay = $dayNames[$rRow['day_id']] ?? "Day {$rRow['day_id']}";
						$actualSlot = $slotTimes[$rRow['slot_id']] ?? "Period {$rRow['slot_id']}";
						
						$prefDay = !empty($rRow['pref_day_id']) ? ($dayNames[$rRow['pref_day_id']] ?? "Day {$rRow['pref_day_id']}") : null;
						$prefSlot = !empty($rRow['pref_slot_id']) ? ($slotTimes[$rRow['pref_slot_id']] ?? "Period {$rRow['pref_slot_id']}") : null;
						
						$remark = !empty($rRow['admin_remark']) ? $rRow['admin_remark'] : ($rRow['admin_notes'] ?? '');
						$reqType = $rRow['request_type'] ?? 'slot_change';
				?>
					<tr>
						<!-- ID -->
						<td style="text-align: center;">
							<span style="background: #f1f5f9; color: #475569; font-weight: 800; font-size: 12px; padding: 4px 8px; border-radius: 6px;">#<?php echo $rRow['request_id']; ?></span>
						</td>

						<!-- TYPE -->
						<td>
							<?php if ($reqType === 'unavailability'): ?>
								<span class="badge-pill badge-amber" style="font-size: 11px;">🚫 UNAVAILABILITY</span>
							<?php else: ?>
								<span class="badge-pill badge-blue" style="font-size: 11px;">🔄 SLOT CHANGE</span>
							<?php endif; ?>
						</td>

						<!-- SUBJECT -->
						<td>
							<?php if (!empty($rRow['sub_code'])): ?>
								<div style="font-weight: 800; color: #1e3a8a; font-size: 13px;">📘 <?php echo htmlspecialchars($rRow['sub_code']); ?></div>
								<?php if (!empty($rRow['sub_name'])): ?>
								<div style="font-size: 11.5px; color: #64748b;"><?php echo htmlspecialchars($rRow['sub_name']); ?></div>
								<?php endif; ?>
							<?php else: ?>
								<span style="color:#94a3b8; font-size: 12px;">General Schedule</span>
							<?php endif; ?>
						</td>

						<!-- ACTUAL TIMING (FROM) -->
						<td>
							<div class="timing-badge-from">
								<div style="font-size: 10.5px; font-weight: 800; color: #1d4ed8; text-transform: uppercase;">📍 ACTUAL TIME</div>
								<div style="font-size: 13.5px; font-weight: 800; color: #0f172a; margin-top: 2px;">
									<?php echo $actualDay; ?>
								</div>
								<div style="font-size: 11.5px; color: #475569; font-weight: 600; margin-top: 1px;">
									⏰ <?php echo $actualSlot; ?>
								</div>
							</div>
						</td>

						<!-- TIMING YOU NEED (TO) -->
						<td>
							<?php if ($prefDay && $prefSlot): ?>
							<div class="timing-badge-to">
								<div style="font-size: 10.5px; font-weight: 800; color: #15803d; text-transform: uppercase;">🎯 TIME NEEDED</div>
								<div style="font-size: 13.5px; font-weight: 800; color: #14532d; margin-top: 2px;">
									<?php echo $prefDay; ?>
								</div>
								<div style="font-size: 11.5px; color: #15803d; font-weight: 600; margin-top: 1px;">
									⏰ <?php echo $prefSlot; ?>
								</div>
							</div>
							<?php else: ?>
							<div style="background: #f8fafc; border: 1px dashed #cbd5e1; border-radius: 8px; padding: 6px 10px; display: inline-block; color: #64748b; font-size: 12px;">
								🚫 (Marked as Unavailable)
							</div>
							<?php endif; ?>
						</td>

						<!-- REASON -->
						<td>
							<div class="reason-bubble">
								"<?php echo htmlspecialchars($rRow['reason']); ?>"
							</div>
							<div style="font-size: 10.5px; color: #94a3b8; margin-top: 4px;">
								<?php echo date('M d, Y', strtotime($rRow['created_at'])); ?>
							</div>
						</td>

						<!-- STATUS -->
						<td style="text-align: center;">
							<?php if ($stat === 'Approved'): ?>
								<span class="badge-pill badge-green" style="font-size: 11.5px; padding: 6px 12px;">✅ APPROVED</span>
							<?php elseif ($stat === 'Rejected'): ?>
								<span class="badge-pill badge-red" style="font-size: 11.5px; padding: 6px 12px; background: #fee2e2; color: #991b1b; border: 1px solid #fecaca;">❌ REJECTED</span>
							<?php else: ?>
								<span class="badge-pill badge-yellow" style="font-size: 11.5px; padding: 6px 12px; background: #fef9c3; color: #854d0e; border: 1px solid #fde047;">🟡 PENDING</span>
							<?php endif; ?>
						</td>

						<!-- ADMIN REMARKS -->
						<td>
							<?php if (!empty($remark)): ?>
								<div style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 8px; padding: 8px 12px; font-size: 12px; font-weight: 600; color: #1e293b;">
									💬 &ldquo;<?php echo htmlspecialchars($remark); ?>&rdquo;
								</div>
							<?php else: ?>
								<span style="color: #94a3b8; font-size: 12px; font-style: italic;">Awaiting Administrator review</span>
							<?php endif; ?>
						</td>
					</tr>
				<?php 
					}
				}
				?>
				</tbody>
			</table>
			</div>

			<!-- 2. MOBILE CARD FEED VIEW (Visible only on mobile devices) -->
			<div class="mobile-requests-list">
				<?php 
				if (empty($myRequests)) {
					echo '<div style="text-align: center; padding: 35px 15px; color: #64748b; font-size: 13.5px;">No timing requests submitted yet.</div>';
				} else {
					foreach ($myRequests as $rRow) {
						$stat = ucfirst(strtolower($rRow['status'] ?? 'pending'));
						$actualDay = $dayNames[$rRow['day_id']] ?? "Day {$rRow['day_id']}";
						$actualSlot = $slotTimes[$rRow['slot_id']] ?? "Period {$rRow['slot_id']}";
						
						$prefDay = !empty($rRow['pref_day_id']) ? ($dayNames[$rRow['pref_day_id']] ?? "Day {$rRow['pref_day_id']}") : null;
						$prefSlot = !empty($rRow['pref_slot_id']) ? ($slotTimes[$rRow['pref_slot_id']] ?? "Period {$rRow['pref_slot_id']}") : null;
						
						$remark = !empty($rRow['admin_remark']) ? $rRow['admin_remark'] : ($rRow['admin_notes'] ?? '');
						$reqType = $rRow['request_type'] ?? 'slot_change';
				?>
					<div class="mobile-req-card">
						<div class="mobile-req-header">
							<div style="display: flex; align-items: center; gap: 8px;">
								<span class="mobile-id-badge">#<?php echo $rRow['request_id']; ?></span>
								<?php if ($reqType === 'unavailability'): ?>
									<span class="badge-pill badge-amber" style="font-size: 10px;">🚫 UNAVAILABILITY</span>
								<?php else: ?>
									<span class="badge-pill badge-blue" style="font-size: 10px;">🔄 SLOT CHANGE</span>
								<?php endif; ?>
							</div>
							<div>
								<?php if ($stat === 'Approved'): ?>
									<span class="badge-pill badge-green" style="font-size: 10.5px; padding: 4px 8px;">✅ APPROVED</span>
								<?php elseif ($stat === 'Rejected'): ?>
									<span class="badge-pill badge-red" style="font-size: 10.5px; padding: 4px 8px; background: #fee2e2; color: #991b1b; border: 1px solid #fecaca;">❌ REJECTED</span>
								<?php else: ?>
									<span class="badge-pill badge-yellow" style="font-size: 10.5px; padding: 4px 8px; background: #fef9c3; color: #854d0e; border: 1px solid #fde047;">🟡 PENDING</span>
								<?php endif; ?>
							</div>
						</div>

						<!-- Subject -->
						<?php if (!empty($rRow['sub_code'])): ?>
							<div style="font-weight: 800; color: #1e3a8a; font-size: 13.5px; margin: 6px 0 2px 0;">
								📘 <?php echo htmlspecialchars($rRow['sub_code']); ?>
								<?php if (!empty($rRow['sub_name'])): ?>
									<span style="font-weight: 500; color: #64748b; font-size: 12px;">- <?php echo htmlspecialchars($rRow['sub_name']); ?></span>
								<?php endif; ?>
							</div>
						<?php endif; ?>

						<!-- Timings Comparison Route -->
						<div class="mobile-route-box">
							<div class="mobile-route-from">
								<span style="font-size: 10px; font-weight: 800; color: #1d4ed8; text-transform: uppercase;">📍 Actual Time (From)</span>
								<div style="font-weight: 800; font-size: 13.5px; color: #0f172a; margin-top: 2px;"><?php echo $actualDay; ?></div>
								<div style="font-size: 11.5px; color: #475569; font-weight: 600;">⏰ <?php echo $actualSlot; ?></div>
							</div>
							
							<div style="display: flex; align-items: center; justify-content: center; color: #94a3b8; font-size: 16px; font-weight: 700;">
								&darr;
							</div>

							<div class="mobile-route-to">
								<span style="font-size: 10px; font-weight: 800; color: #15803d; text-transform: uppercase;">🎯 Time Needed (To)</span>
								<?php if ($prefDay && $prefSlot): ?>
									<div style="font-weight: 800; font-size: 13.5px; color: #14532d; margin-top: 2px;"><?php echo $prefDay; ?></div>
									<div style="font-size: 11.5px; color: #15803d; font-weight: 600;">⏰ <?php echo $prefSlot; ?></div>
								<?php else: ?>
									<div style="font-size: 12px; color: #64748b; font-style: italic; margin-top: 2px;">🚫 Unavailability (No replacement slot)</div>
								<?php endif; ?>
							</div>
						</div>

						<!-- Reason -->
						<div class="reason-bubble" style="max-width: 100%; margin-top: 10px;">
							"<?php echo htmlspecialchars($rRow['reason']); ?>"
							<div style="font-size: 10.5px; color: #94a3b8; margin-top: 4px;">
								Submitted: <?php echo date('M d, Y', strtotime($rRow['created_at'])); ?>
							</div>
						</div>

						<!-- Admin Remark -->
						<?php if (!empty($remark)): ?>
							<div style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 8px; padding: 10px 12px; margin-top: 10px;">
								<div style="font-size: 11px; font-weight: 700; color: #64748b; text-transform: uppercase;">Admin Decision:</div>
								<div style="font-size: 13px; font-weight: 600; color: #1e293b; margin-top: 2px;">
									&ldquo;<?php echo htmlspecialchars($remark); ?>&rdquo;
								</div>
							</div>
						<?php else: ?>
							<div style="font-size: 11.5px; color: #94a3b8; font-style: italic; margin-top: 8px;">
								⏳ Awaiting Administrator verification
							</div>
						<?php endif; ?>
					</div>
				<?php 
					}
				}
				?>
			</div>

		</div>

	</div>
  </div>
</div>
</body>
</html>

<?php 
session_start();
include("../includes/session.php");
require("../includes/dbconnection.php");

// RBAC: Exclusive to Admin
$curr_role = $_SESSION['is']['role'] ?? (($_SESSION['is']['dept_id'] == 4) ? 'Admin' : 'Faculty');
if (strtolower($curr_role) !== 'admin') {
    die("<div style='font-family: sans-serif; padding: 40px; text-align: center;'><h2>Access Restricted</h2><p>This approval console is accessible only by System Administrators.</p><p><a href='mastertt.php'>Return to Schedules</a></p></div>");
}

$msg = '';
$error = '';

// Handle Approve / Reject
if (isset($_POST['btnAction'])) {
    $req_id = intval($_POST['request_id'] ?? 0);
    $action = $_POST['btnAction']; // 'approve' or 'reject'
    $remark = mysqli_real_escape_string($conn, trim($_POST['admin_remark'] ?? ''));

    if ($req_id > 0) {
        $new_status = ($action === 'approve') ? 'Approved' : 'Rejected';
        $upd = "UPDATE timing_requests 
                SET status = '$new_status', admin_remark = '$remark', admin_notes = '$remark' 
                WHERE request_id = $req_id";
        if (mysqli_query($conn, $upd)) {
            // If approved and request was unavailability, record in faculty_timing
            if ($action === 'approve') {
                $q = mysqli_query($conn, "SELECT teacher_id, day_id, slot_id, request_type FROM timing_requests WHERE request_id = $req_id");
                if ($q && $tRow = mysqli_fetch_assoc($q)) {
                    $tid = intval($tRow['teacher_id']);
                    $did = intval($tRow['day_id']);
                    $sid = intval($tRow['slot_id']);
                    if ($tRow['request_type'] === 'unavailability') {
                        $note_escaped = !empty($remark) ? $remark : 'Approved by Admin';
                        mysqli_query($conn, "INSERT INTO faculty_timing (teacher_id, day_id, slot_id, is_available, notes) 
                                             VALUES ($tid, $did, $sid, 0, '$note_escaped') 
                                             ON DUPLICATE KEY UPDATE is_available = 0, notes = '$note_escaped'");
                    }
                }
            }
            $msg = "Timing request #$req_id has been successfully marked as $new_status.";
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
    1 => '09:00 - 10:00 (P1)',
    2 => '10:00 - 11:00 (P2)',
    3 => '11:15 - 12:15 (P3)',
    4 => '12:15 - 01:15 (P4)',
    5 => '02:00 - 03:00 (P5)',
    6 => '03:00 - 04:00 (P6)',
    7 => '04:00 - 05:00 (P7)',
    8 => '05:00 - 06:00 (P8)',
];

// Query all requests with faculty, dept, and subject details
$allRequests = [];
$pendingCount = 0;
$approvedCount = 0;
$rejectedCount = 0;

$q = "SELECT tr.*, p.teacher_name, p.acad_rank, d.department, s.sub_code, s.sub_name 
      FROM timing_requests tr 
      JOIN profile p ON tr.teacher_id = p.teacher_id 
      LEFT JOIN dept d ON p.dept_id = d.dept_id 
      LEFT JOIN subjects s ON tr.sub_id = s.sub_id 
      ORDER BY tr.request_id DESC";
$res = mysqli_query($conn, $q);
if ($res) {
    while ($row = mysqli_fetch_assoc($res)) {
        $st = strtolower($row['status'] ?? 'pending');
        if ($st === 'approved') $approvedCount++;
        elseif ($st === 'rejected') $rejectedCount++;
        else $pendingCount++;
        $allRequests[] = $row;
    }
}
$totalCount = count($allRequests);
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=5.0, user-scalable=yes" />
<link href="../includes/style_prac2.css" rel="stylesheet" type="text/css" />
<title>Scheduling System - Faculty Timing Requests Approval</title>
<style type="text/css">
/* Sturdy Full-Width Container & Table Styling */
.sturdy-container {
    width: 100% !important;
    max-width: 100% !important;
    box-sizing: border-box;
    margin: 0;
    padding: 0;
}
.requests-card {
    background: #ffffff;
    border: 1px solid #e2e8f0;
    border-radius: 16px;
    box-shadow: 0 4px 20px -2px rgba(15, 23, 42, 0.06);
    overflow: hidden;
    width: 100%;
    margin-top: 18px;
    box-sizing: border-box;
}
.requests-header {
    background: #f8fafc;
    border-bottom: 1px solid #e2e8f0;
    padding: 16px 24px;
    display: flex;
    align-items: center;
    justify-content: space-between;
    flex-wrap: wrap;
    gap: 12px;
}
.stat-pill {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 5px 12px;
    border-radius: 9999px;
    font-size: 12px;
    font-weight: 700;
}
.stat-pill-total { background: #f1f5f9; color: #334155; }
.stat-pill-pending { background: #fef9c3; color: #854d0e; border: 1px solid #fde047; }
.stat-pill-approved { background: #ecfdf5; color: #065f46; border: 1px solid #a7f3d0; }
.stat-pill-rejected { background: #fee2e2; color: #991b1b; border: 1px solid #fecaca; }

/* Desktop Table View */
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

/* Time Badges */
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
.timing-badge-unavail {
    background: #f8fafc;
    border: 1px dashed #cbd5e1;
    border-radius: 8px;
    padding: 6px 10px;
    display: inline-block;
    color: #64748b;
    font-size: 12px;
}

.reason-bubble {
    background: #f8fafc;
    border-left: 3px solid #3b82f6;
    padding: 8px 12px;
    border-radius: 0 8px 8px 0;
    font-size: 12.5px;
    color: #1e293b;
    line-height: 1.45;
    max-width: 280px;
    word-break: break-word;
}

.btn-approve {
    background: #16a34a;
    color: #ffffff;
    border: none;
    padding: 8px 14px;
    border-radius: 6px;
    font-size: 12px;
    font-weight: 700;
    cursor: pointer;
    display: inline-flex;
    align-items: center;
    gap: 4px;
    transition: all 0.15s ease;
    box-shadow: 0 2px 4px rgba(22, 163, 74, 0.2);
}
.btn-approve:hover {
    background: #15803d;
    transform: translateY(-1px);
}
.btn-approve:active {
    transform: scale(0.96);
}

.btn-reject {
    background: #dc2626;
    color: #ffffff;
    border: none;
    padding: 8px 14px;
    border-radius: 6px;
    font-size: 12px;
    font-weight: 700;
    cursor: pointer;
    display: inline-flex;
    align-items: center;
    gap: 4px;
    transition: all 0.15s ease;
    box-shadow: 0 2px 4px rgba(220, 38, 38, 0.2);
}
.btn-reject:hover {
    background: #b91c1c;
    transform: translateY(-1px);
}
.btn-reject:active {
    transform: scale(0.96);
}

/* Mobile-Specific Responsive Card Feed */
.mobile-requests-list {
    display: none;
}

@media (max-width: 768px) {
    .requests-header {
        padding: 14px 16px;
        flex-direction: column;
        align-items: flex-start;
        gap: 10px;
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
        padding: 16px;
        box-shadow: 0 2px 10px rgba(15, 23, 42, 0.04);
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
					<h2 style="margin: 0; color: #0f172a; font-size: 21px; font-weight: 800; text-align: left; letter-spacing: -0.3px;">Faculty Timing Change Requests</h2>
					<span class="badge-pill badge-green">Verification Console</span>
				</div>
				<div style="font-size: 13px; color: #64748b; font-weight: 500;">
					Verify requested changes against faculty teaching commitments and approve schedule reallocations or unavailability.
				</div>
			</div>
			<div style="display: flex; gap: 10px; align-items: center;">
				<a href="faculty_timing.php" style="background: #2563eb; color: #ffffff; padding: 9px 16px; border-radius: 8px; text-decoration: none; font-size: 13px; font-weight: 700; display: inline-flex; align-items: center; gap: 6px; box-shadow: 0 2px 6px rgba(37, 99, 235, 0.25);">
					⏰ Availability Matrix
				</a>
			</div>
		</div>

		<!-- ALERT MESSAGES -->
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

		<!-- STURDY REQUESTS CARD -->
		<div class="requests-card">
			<div class="requests-header">
				<div style="display: flex; align-items: center; gap: 10px; flex-wrap: wrap;">
					<h3 style="margin: 0; color: #0f172a; font-size: 16px; font-weight: 800;">Submitted Adjustment Roster</h3>
					<span class="stat-pill stat-pill-total"><?php echo $totalCount; ?> Total</span>
					<span class="stat-pill stat-pill-pending"><?php echo $pendingCount; ?> Pending</span>
					<span class="stat-pill stat-pill-approved"><?php echo $approvedCount; ?> Approved</span>
					<span class="stat-pill stat-pill-rejected"><?php echo $rejectedCount; ?> Rejected</span>
				</div>
				<div style="font-size: 12.5px; color: #64748b; font-weight: 600;">
					Real-time updates &bull; Sorted latest first
				</div>
			</div>

			<!-- 1. DESKTOP FULL TABLE VIEW -->
			<div class="desktop-requests-table">
			<table class="requests-table">
				<thead>
					<tr>
						<th style="width: 50px; text-align: center;">ID</th>
						<th style="min-width: 170px;">Faculty Member</th>
						<th style="min-width: 160px;">Request Type / Subject</th>
						<th style="min-width: 175px;">Actual Timing (From)</th>
						<th style="min-width: 175px;">Timing Needed (To)</th>
						<th style="min-width: 230px;">Reason from Faculty</th>
						<th style="width: 110px; text-align: center;">Status</th>
						<th style="min-width: 250px; text-align: center;">Admin Verification &amp; Remark</th>
					</tr>
				</thead>
				<tbody>
				<?php 
				if (empty($allRequests)) {
					echo '<tr><td colspan="8" style="text-align: center; padding: 45px 20px; color: #64748b; font-size: 14px;">No faculty timing requests submitted yet.</td></tr>';
				} else {
					foreach ($allRequests as $row) {
						$rid = $row['request_id'];
						$stat = ucfirst(strtolower($row['status'] ?? 'pending'));
						$dayStr = $dayNames[$row['day_id']] ?? "Day {$row['day_id']}";
						$slotStr = $slotTimes[$row['slot_id']] ?? "Period {$row['slot_id']}";

						$prefDay = !empty($row['pref_day_id']) ? ($dayNames[$row['pref_day_id']] ?? "Day {$row['pref_day_id']}") : null;
						$prefSlot = !empty($row['pref_slot_id']) ? ($slotTimes[$row['pref_slot_id']] ?? "Period {$row['pref_slot_id']}") : null;

						$remark = !empty($row['admin_remark']) ? $row['admin_remark'] : ($row['admin_notes'] ?? '');
						$reqType = $row['request_type'] ?? 'slot_change';
				?>
					<tr>
						<!-- ID -->
						<td style="text-align: center;">
							<span style="background: #f1f5f9; color: #475569; font-weight: 800; font-size: 12px; padding: 4px 8px; border-radius: 6px;">#<?php echo $rid; ?></span>
						</td>

						<!-- FACULTY MEMBER -->
						<td>
							<div style="font-weight: 800; color: #0f172a; font-size: 14px; white-space: nowrap; display: flex; align-items: center; gap: 6px;">
								<span>👨‍🏫</span> <?php echo htmlspecialchars($row['teacher_name']); ?>
							</div>
							<div style="font-size: 11.5px; color: #64748b; margin-top: 2px;">
								<?php echo htmlspecialchars($row['department'] ?? 'Department'); ?>
								<?php if (!empty($row['acad_rank'])): ?>
									&bull; <?php echo htmlspecialchars($row['acad_rank']); ?>
								<?php endif; ?>
							</div>
						</td>

						<!-- TYPE & SUBJECT -->
						<td>
							<?php if ($reqType === 'unavailability'): ?>
								<span class="badge-pill badge-amber" style="font-size: 11px;">🚫 UNAVAILABILITY</span>
							<?php else: ?>
								<span class="badge-pill badge-blue" style="font-size: 11px;">🔄 SLOT CHANGE</span>
							<?php endif; ?>

							<?php if (!empty($row['sub_code'])): ?>
								<div style="font-size: 12px; font-weight: 700; color: #1e3a8a; margin-top: 5px;">
									📘 <?php echo htmlspecialchars($row['sub_code']); ?>
								</div>
								<?php if (!empty($row['sub_name'])): ?>
								<div style="font-size: 11px; color: #64748b; max-width: 170px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;" title="<?php echo htmlspecialchars($row['sub_name']); ?>">
									<?php echo htmlspecialchars($row['sub_name']); ?>
								</div>
								<?php endif; ?>
							<?php else: ?>
								<div style="font-size: 11.5px; color: #94a3b8; margin-top: 4px;">General Faculty Schedule</div>
							<?php endif; ?>
						</td>

						<!-- ACTUAL CURRENT TIMING (FROM) -->
						<td>
							<div class="timing-badge-from">
								<div style="font-size: 11px; font-weight: 800; color: #1d4ed8; text-transform: uppercase;">📍 ACTUAL TIME</div>
								<div style="font-size: 13.5px; font-weight: 800; color: #0f172a; margin-top: 2px;">
									<?php echo $dayStr; ?>
								</div>
								<div style="font-size: 11.5px; color: #475569; font-weight: 600; margin-top: 1px;">
									⏰ <?php echo $slotStr; ?>
								</div>
							</div>
						</td>

						<!-- REQUESTED TIMING (TO / NEEDED) -->
						<td>
							<?php if ($prefDay && $prefSlot): ?>
							<div class="timing-badge-to">
								<div style="font-size: 11px; font-weight: 800; color: #15803d; text-transform: uppercase;">🎯 TIME NEEDED</div>
								<div style="font-size: 13.5px; font-weight: 800; color: #14532d; margin-top: 2px;">
									<?php echo $prefDay; ?>
								</div>
								<div style="font-size: 11.5px; color: #15803d; font-weight: 600; margin-top: 1px;">
									⏰ <?php echo $prefSlot; ?>
								</div>
							</div>
							<?php else: ?>
							<div class="timing-badge-unavail">
								<strong>🚫 Unavailability</strong><br/>
								<span>(Marked off from schedule)</span>
							</div>
							<?php endif; ?>
						</td>

						<!-- REASON -->
						<td>
							<div class="reason-bubble">
								"<?php echo htmlspecialchars($row['reason']); ?>"
							</div>
							<div style="font-size: 10.5px; color: #94a3b8; margin-top: 4px;">
								Submitted: <?php echo date('M d, Y h:i A', strtotime($row['created_at'])); ?>
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

						<!-- ADMIN ACTION & REMARK -->
						<td>
							<?php if ($stat === 'Pending'): ?>
								<form action="timing_requests_admin.php" method="post" style="display: flex; flex-direction: column; gap: 8px;">
									<input type="hidden" name="request_id" value="<?php echo $rid; ?>" />
									<input type="text" name="admin_remark" placeholder="Add verification note (optional)..." style="width: 100%; box-sizing: border-box; padding: 8px 10px; font-size: 12px; border: 1.5px solid #cbd5e1; border-radius: 6px; background: #ffffff;" />
									<div style="display: flex; gap: 8px; justify-content: center;">
										<button type="submit" name="btnAction" value="approve" class="btn-approve">
											<span>✔</span> Approve
										</button>
										<button type="submit" name="btnAction" value="reject" class="btn-reject">
											<span>✖</span> Reject
										</button>
									</div>
								</form>
							<?php else: ?>
								<div style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 8px; padding: 8px 12px; text-align: left;">
									<div style="font-size: 11px; font-weight: 700; color: #64748b; text-transform: uppercase;">Admin Decision:</div>
									<div style="font-size: 12.5px; font-weight: 600; color: #1e293b; margin-top: 2px;">
										<?php echo !empty($remark) ? ('&ldquo;' . htmlspecialchars($remark) . '&rdquo;') : '<span style="color:#94a3b8; font-style:italic;">No remarks recorded</span>'; ?>
									</div>
								</div>
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
				if (empty($allRequests)) {
					echo '<div style="text-align: center; padding: 35px 15px; color: #64748b; font-size: 14px;">No faculty timing requests submitted yet.</div>';
				} else {
					foreach ($allRequests as $row) {
						$rid = $row['request_id'];
						$stat = ucfirst(strtolower($row['status'] ?? 'pending'));
						$dayStr = $dayNames[$row['day_id']] ?? "Day {$row['day_id']}";
						$slotStr = $slotTimes[$row['slot_id']] ?? "Period {$row['slot_id']}";

						$prefDay = !empty($row['pref_day_id']) ? ($dayNames[$row['pref_day_id']] ?? "Day {$row['pref_day_id']}") : null;
						$prefSlot = !empty($row['pref_slot_id']) ? ($slotTimes[$row['pref_slot_id']] ?? "Period {$row['pref_slot_id']}") : null;

						$remark = !empty($row['admin_remark']) ? $row['admin_remark'] : ($row['admin_notes'] ?? '');
						$reqType = $row['request_type'] ?? 'slot_change';
				?>
					<div class="mobile-req-card">
						<div class="mobile-req-header">
							<div style="display: flex; align-items: center; gap: 8px;">
								<span class="mobile-id-badge">#<?php echo $rid; ?></span>
								<span style="font-weight: 800; font-size: 14.5px; color: #0f172a;">👨‍🏫 <?php echo htmlspecialchars($row['teacher_name']); ?></span>
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

						<div style="font-size: 12px; color: #64748b; margin-bottom: 10px;">
							<?php echo htmlspecialchars($row['department'] ?? 'Department'); ?>
							<?php if (!empty($row['acad_rank'])) echo ' &bull; ' . htmlspecialchars($row['acad_rank']); ?>
						</div>

						<!-- Type & Subject -->
						<div style="display: flex; align-items: center; gap: 8px; margin-bottom: 10px; flex-wrap: wrap;">
							<?php if ($reqType === 'unavailability'): ?>
								<span class="badge-pill badge-amber" style="font-size: 10.5px;">🚫 UNAVAILABILITY</span>
							<?php else: ?>
								<span class="badge-pill badge-blue" style="font-size: 10.5px;">🔄 SLOT CHANGE</span>
							<?php endif; ?>

							<?php if (!empty($row['sub_code'])): ?>
								<span style="font-weight: 700; color: #1e3a8a; font-size: 12px;">📘 <?php echo htmlspecialchars($row['sub_code']); ?></span>
								<?php if (!empty($row['sub_name'])): ?>
									<span style="font-size: 11.5px; color: #64748b;">(<?php echo htmlspecialchars($row['sub_name']); ?>)</span>
								<?php endif; ?>
							<?php endif; ?>
						</div>

						<!-- Timings Comparison Route -->
						<div class="mobile-route-box">
							<div class="mobile-route-from">
								<span style="font-size: 10px; font-weight: 800; color: #1d4ed8; text-transform: uppercase;">📍 Actual Time (From)</span>
								<div style="font-weight: 800; font-size: 13.5px; color: #0f172a; margin-top: 2px;"><?php echo $dayStr; ?></div>
								<div style="font-size: 11.5px; color: #475569; font-weight: 600;">⏰ <?php echo $slotStr; ?></div>
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
							"<?php echo htmlspecialchars($row['reason']); ?>"
							<div style="font-size: 10.5px; color: #94a3b8; margin-top: 4px;">
								Submitted: <?php echo date('M d, Y h:i A', strtotime($row['created_at'])); ?>
							</div>
						</div>

						<!-- Admin Action Controls on Mobile -->
						<?php if ($stat === 'Pending'): ?>
							<form action="timing_requests_admin.php" method="post" style="margin-top: 12px; display: flex; flex-direction: column; gap: 8px;">
								<input type="hidden" name="request_id" value="<?php echo $rid; ?>" />
								<input type="text" name="admin_remark" placeholder="Add verification note (optional)..." style="width: 100%; box-sizing: border-box; padding: 10px 12px; font-size: 13px; border: 1.5px solid #cbd5e1; border-radius: 8px; background: #ffffff;" />
								<div style="display: grid; grid-template-columns: 1fr 1fr; gap: 8px;">
									<button type="submit" name="btnAction" value="approve" class="btn-approve" style="justify-content: center; padding: 10px; font-size: 13px;">
										<span>✔</span> Approve
									</button>
									<button type="submit" name="btnAction" value="reject" class="btn-reject" style="justify-content: center; padding: 10px; font-size: 13px;">
										<span>✖</span> Reject
									</button>
								</div>
							</form>
						<?php else: ?>
							<div style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 8px; padding: 10px 12px; margin-top: 12px;">
								<div style="font-size: 11px; font-weight: 700; color: #64748b; text-transform: uppercase;">Admin Decision:</div>
								<div style="font-size: 13px; font-weight: 600; color: #1e293b; margin-top: 2px;">
									<?php echo !empty($remark) ? ('&ldquo;' . htmlspecialchars($remark) . '&rdquo;') : '<span style="color:#94a3b8; font-style:italic;">No remarks recorded</span>'; ?>
								</div>
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

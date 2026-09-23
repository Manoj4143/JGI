<?php if (empty($session)) { session_start(); } ?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
<link href="../includes/style_prac2.css" rel="stylesheet" type="text/css" />
<title>Scheduling System - Administrator Dashboard</title>
</head>
<?php
include("../includes/session.php");
require("../includes/dbconnection.php");

// RBAC: If Faculty or Counselor, redirect to appropriate portal
$userRole = $_SESSION['is']['role'] ?? (($_SESSION['is']['dept_id'] == 4 || ($_SESSION['is']['dept'] ?? 0) == 4) ? 'Admin' : 'Faculty');
$userRole = ucfirst(strtolower($userRole));
if ($userRole === 'Faculty') {
    $myTeacherId = intval($_SESSION['is']['teacher_id'] ?? 0);
    if ($myTeacherId > 0) {
        header("Location: search_t_result.php?pT=" . $myTeacherId);
    } else {
        header("Location: my_profile.php");
    }
    exit;
} elseif ($userRole === 'Counselor') {
    header("Location: mastertt.php");
    exit;
}

// Fetch real-time KPI counts
$totalDepts = mysqli_num_rows(mysqli_query($conn, "SELECT dept_id FROM dept"));
$totalTeachers = mysqli_num_rows(mysqli_query($conn, "SELECT teacher_id FROM profile"));
$totalCourses = mysqli_num_rows(mysqli_query($conn, "SELECT course_id FROM course"));
$totalSubjects = mysqli_num_rows(mysqli_query($conn, "SELECT sub_id FROM subjects"));
$totalRooms = mysqli_num_rows(mysqli_query($conn, "SELECT room_id FROM room"));
$totalUsers = mysqli_num_rows(mysqli_query($conn, "SELECT user_id FROM user"));
$totalSched = mysqli_num_rows(mysqli_query($conn, "SELECT sched_id FROM sched"));
?>
<body>
<div id="container">
<?php include("../includes/shedulo_shell.php"); ?>
  <div id="content">
	<div id="left" style="width: 100%;">
		
		<!-- Hero Banner -->
		<div class="tt-hero-banner" style="margin-top: 10px;">
			<div>
				<div style="display: flex; align-items: center; gap: 10px; margin-bottom: 4px; flex-wrap: wrap;">
					<h2 style="margin: 0; color: #0f172a; font-size: 22px; font-weight: 800; text-align: left; letter-spacing: -0.2px;">Administrator Control Center</h2>
					<span class="badge-pill badge-green">Academic Year Active</span>
					<span class="badge-pill badge-blue">Genetic Clash Engine v2.4</span>
				</div>
				<div style="font-size: 13px; color: #64748b; font-weight: 500;">
					Central orchestration for academic departments, student cohorts, faculty scheduling, and timetable automation.
				</div>
			</div>
			<div style="display: flex; gap: 10px; flex-wrap: wrap;">
				<a href="generate_tt.php" onclick="return confirm('Generate a new timetable? This will re-calculate slots for all subjects.')" style="background: #2563eb; color: #ffffff; padding: 10px 18px; border-radius: 8px; text-decoration: none; font-size: 13px; font-weight: 700; display: inline-flex; align-items: center; gap: 6px; box-shadow: 0 4px 6px -1px rgba(37, 99, 235, 0.2);">⚡ Generate Timetable</a>
				<a href="mastertt.php" style="background: #059669; color: #ffffff; padding: 10px 18px; border-radius: 8px; text-decoration: none; font-size: 13px; font-weight: 700; display: inline-flex; align-items: center; gap: 6px; box-shadow: 0 4px 6px -1px rgba(5, 150, 105, 0.2);">📅 Master Timetable</a>
			</div>
		</div>

		<!-- 4 Glowing Neon Metric KPI Cards (Inspired by Modern Dark Theme UI) -->
		<div class="kpi-neon-grid">
			<!-- KPI 1: Departments -->
			<div class="kpi-neon-card kpi-teal">
				<div class="kpi-header-row">
					<div class="kpi-icon-pill">
						<span class="material-symbols-outlined">domain</span>
					</div>
					<span class="kpi-badge">+Active Stream</span>
				</div>
				<div class="kpi-label">Total Departments</div>
				<div class="kpi-number"><?php echo $totalDepts; ?></div>
				<div class="kpi-subtext">Branches &amp; academic divisions</div>
			</div>

			<!-- KPI 2: Faculty Members -->
			<div class="kpi-neon-card kpi-purple">
				<div class="kpi-header-row">
					<div class="kpi-icon-pill">
						<span class="material-symbols-outlined">badge</span>
					</div>
					<span class="kpi-badge">+Roster Online</span>
				</div>
				<div class="kpi-label">Faculty Roster</div>
				<div class="kpi-number"><?php echo $totalTeachers; ?></div>
				<div class="kpi-subtext">Professors, HODs &amp; instructors</div>
			</div>

			<!-- KPI 3: Student Cohorts -->
			<div class="kpi-neon-card kpi-orange">
				<div class="kpi-header-row">
					<div class="kpi-icon-pill">
						<span class="material-symbols-outlined">groups</span>
					</div>
					<span class="kpi-badge">+All Semesters</span>
				</div>
				<div class="kpi-label">Courses &amp; Batches</div>
				<div class="kpi-number"><?php echo $totalCourses; ?></div>
				<div class="kpi-subtext">Year-sections &amp; student groups</div>
			</div>

			<!-- KPI 4: Scheduled Lectures -->
			<div class="kpi-neon-card kpi-pink">
				<div class="kpi-header-row">
					<div class="kpi-icon-pill">
						<span class="material-symbols-outlined">event_available</span>
					</div>
					<span class="kpi-badge">0 Collisions</span>
				</div>
				<div class="kpi-label">Scheduled Slots</div>
				<div class="kpi-number"><?php echo $totalSched; ?></div>
				<div class="kpi-subtext">Theory, Labs &amp; Projects assigned</div>
			</div>
		</div>

		<!-- Comprehensive Feature Cards for All System Capabilities -->
		<div style="margin-top: 28px; margin-bottom: 12px; display: flex; align-items: center; justify-content: space-between;">
			<h3 style="margin: 0; font-size: 16px; font-weight: 800; color: var(--app-text); letter-spacing: -0.2px;">
				Academic Management &amp; System Capabilities
			</h3>
			<span style="font-size: 12px; color: var(--app-text-muted);">All core administration tools</span>
		</div>

		<div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 18px; margin: 15px 0 35px 0;">
			
			<!-- Card: Master Timetable -->
			<div class="admin-feature-card">
				<div class="admin-feature-icon" style="background: rgba(37, 99, 235, 0.1); color: #2563eb;">
					<span class="material-symbols-outlined">calendar_month</span>
				</div>
				<h4 class="admin-feature-title">Master Timetable Grid</h4>
				<p class="admin-feature-desc">Transposed 6-day institutional grid with 1-click teacher swaps, batch views, and conflict resolution.</p>
				<a href="mastertt.php" class="admin-feature-link">Open Master Grid &rarr;</a>
			</div>

			<!-- Card: Departments & Branches -->
			<div class="admin-feature-card">
				<div class="admin-feature-icon" style="background: rgba(16, 185, 129, 0.1); color: #10b981;">
					<span class="material-symbols-outlined">domain</span>
				</div>
				<h4 class="admin-feature-title">Departments &amp; Branches</h4>
				<p class="admin-feature-desc">Register and manage academic engineering branches, department chairs, and stream associations.</p>
				<div style="display: flex; gap: 12px;">
					<a href="deptlist-a.php" class="admin-feature-link">View List &rarr;</a>
					<a href="dept-a.php" class="admin-feature-link" style="color: #10b981;">+ Add Branch</a>
				</div>
			</div>

			<!-- Card: Faculty Availability -->
			<div class="admin-feature-card">
				<div class="admin-feature-icon" style="background: rgba(13, 148, 136, 0.1); color: #0d9488;">
					<span class="material-symbols-outlined">alarm_on</span>
				</div>
				<h4 class="admin-feature-title">Faculty Availability &amp; Timing</h4>
				<p class="admin-feature-desc">Customize specific busy and free periods for individual professors across all 6 working days.</p>
				<a href="faculty_timing.php" class="admin-feature-link" style="color: #0d9488;">Configure Timing &rarr;</a>
			</div>

			<!-- Card: Student Courses & Batches -->
			<div class="admin-feature-card">
				<div class="admin-feature-icon" style="background: rgba(245, 158, 11, 0.1); color: #f59e0b;">
					<span class="material-symbols-outlined">school</span>
				</div>
				<h4 class="admin-feature-title">Student Courses &amp; Batches</h4>
				<p class="admin-feature-desc">Manage student cohorts, year-sections (e.g. 3-A, 5-B), and degree specializations.</p>
				<div style="display: flex; gap: 12px;">
					<a href="student-list-a.php" class="admin-feature-link">View Courses &rarr;</a>
					<a href="student-a.php" class="admin-feature-link" style="color: #f59e0b;">+ Add Course</a>
				</div>
			</div>

			<!-- Card: Course Subjects & Modules -->
			<div class="admin-feature-card">
				<div class="admin-feature-icon" style="background: rgba(99, 102, 241, 0.1); color: #6366f1;">
					<span class="material-symbols-outlined">menu_book</span>
				</div>
				<h4 class="admin-feature-title">Course / Subjects Catalog</h4>
				<p class="admin-feature-desc">Curriculum modules, lecture hour quotas, lab hours, project categorizations, and teacher assignments.</p>
				<div style="display: flex; gap: 12px;">
					<a href="subjectlist-a.php" class="admin-feature-link">Subject Directory &rarr;</a>
					<a href="subject-a.php" class="admin-feature-link" style="color: #6366f1;">+ Add Subject</a>
				</div>
			</div>

			<!-- Card: Classrooms & Laboratories -->
			<div class="admin-feature-card">
				<div class="admin-feature-icon" style="background: rgba(236, 72, 153, 0.1); color: #ec4899;">
					<span class="material-symbols-outlined">meeting_room</span>
				</div>
				<h4 class="admin-feature-title">Classrooms &amp; Laboratories</h4>
				<p class="admin-feature-desc">Configure lecture halls, computer laboratories, capacity limits, and room availability.</p>
				<div style="display: flex; gap: 12px;">
					<a href="roomlist-a.php" class="admin-feature-link">Room Directory &rarr;</a>
					<a href="room-a.php" class="admin-feature-link" style="color: #ec4899;">+ Add Room</a>
				</div>
			</div>

			<!-- Card: Teachers & Faculty Directory -->
			<div class="admin-feature-card">
				<div class="admin-feature-icon" style="background: rgba(20, 184, 166, 0.1); color: #14b8a6;">
					<span class="material-symbols-outlined">person_add</span>
				</div>
				<h4 class="admin-feature-title">Faculty Roster &amp; Profiles</h4>
				<p class="admin-feature-desc">Manage faculty profiles, academic designations, teaching ranks, and departmental departments.</p>
				<div style="display: flex; gap: 12px;">
					<a href="facultylist-a.php" class="admin-feature-link">Faculty List &rarr;</a>
					<a href="faculty-a.php" class="admin-feature-link" style="color: #14b8a6;">+ Add Faculty</a>
				</div>
			</div>

			<!-- Card: Faculty Details & Salaries -->
			<div class="admin-feature-card" style="border-color: #10b981;">
				<div class="admin-feature-icon" style="background: rgba(16, 185, 129, 0.1); color: #059669;">
					<span class="material-symbols-outlined">payments</span>
				</div>
				<h4 class="admin-feature-title">Faculty Qualifications &amp; Salaries</h4>
				<p class="admin-feature-desc">Admin-only confidential records: 10th/12th/degree qualifications, teaching experience, speciality, and salaries.</p>
				<a href="faculty_details.php" class="admin-feature-link" style="color: #059669;">Faculty Details &rarr;</a>
			</div>

			<!-- Card: Faculty Timing Requests -->
			<div class="admin-feature-card">
				<div class="admin-feature-icon" style="background: rgba(234, 88, 12, 0.1); color: #ea580c;">
					<span class="material-symbols-outlined">rate_review</span>
				</div>
				<h4 class="admin-feature-title">Timing Change Requests</h4>
				<p class="admin-feature-desc">Review and approve faculty-submitted class timing adjustments, slot swaps, and reasons.</p>
				<a href="timing_requests_admin.php" class="admin-feature-link" style="color: #ea580c;">Review Requests &rarr;</a>
			</div>

			<!-- Card: Faculty Login Access Provisioning -->
			<div class="admin-feature-card">
				<div class="admin-feature-icon" style="background: rgba(5, 150, 105, 0.1); color: #059669;">
					<span class="material-symbols-outlined">vpn_key</span>
				</div>
				<h4 class="admin-feature-title">Faculty Portal Credentials</h4>
				<p class="admin-feature-desc">Grant and update login credentials (email &amp; password) for faculty members to view their timetables.</p>
				<a href="faculty_access.php" class="admin-feature-link" style="color: #059669;">Manage Faculty Access &rarr;</a>
			</div>

			<!-- Card: Semester & Branch Timetables -->
			<div class="admin-feature-card">
				<div class="admin-feature-icon" style="background: rgba(59, 130, 246, 0.1); color: #3b82f6;">
					<span class="material-symbols-outlined">view_timeline</span>
				</div>
				<h4 class="admin-feature-title">Semester &amp; Branch Schedules</h4>
				<p class="admin-feature-desc">Export and view branch-specific student timetables ready for printing, display, and consultation.</p>
				<a href="search_course.php" class="admin-feature-link" style="color: #3b82f6;">View Student Schedules &rarr;</a>
			</div>

			<!-- Card: Room Timetable Matrix -->
			<div class="admin-feature-card">
				<div class="admin-feature-icon" style="background: rgba(168, 85, 247, 0.1); color: #a855f7;">
					<span class="material-symbols-outlined">door_front</span>
				</div>
				<h4 class="admin-feature-title">Room Occupancy Schedules</h4>
				<p class="admin-feature-desc">Monitor which halls and laboratories are occupied in each period to prevent room conflicts.</p>
				<a href="search_room.php" class="admin-feature-link" style="color: #a855f7;">Room Matrix &rarr;</a>
			</div>

			<!-- Card: Academic School Years -->
			<div class="admin-feature-card">
				<div class="admin-feature-icon" style="background: rgba(234, 88, 12, 0.1); color: #ea580c;">
					<span class="material-symbols-outlined">date_range</span>
				</div>
				<h4 class="admin-feature-title">Academic School Years</h4>
				<p class="admin-feature-desc">Set active academic years and session calendars for curriculum term planning.</p>
				<div style="display: flex; gap: 12px;">
					<a href="yearlist-a.php" class="admin-feature-link">View Years &rarr;</a>
					<a href="year-a.php" class="admin-feature-link" style="color: #ea580c;">+ Add Year</a>
				</div>
			</div>

			<!-- Card: System User Accounts -->
			<div class="admin-feature-card">
				<div class="admin-feature-icon" style="background: rgba(100, 116, 139, 0.1); color: #64748b;">
					<span class="material-symbols-outlined">manage_accounts</span>
				</div>
				<h4 class="admin-feature-title">System User Accounts</h4>
				<p class="admin-feature-desc">Manage system operator accounts, administrator roles, and access permissions.</p>
				<div style="display: flex; gap: 12px;">
					<a href="userlist.php" class="admin-feature-link">User Accounts &rarr;</a>
					<a href="user.php" class="admin-feature-link" style="color: #64748b;">+ Add User</a>
				</div>
			</div>

		</div>

	  </div>
	</div>
  </div><!-- /shedulo-main-wrapper -->
</div><!-- /container -->
</body>
</html>

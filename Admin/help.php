<?php 
session_start();
include("../includes/session.php");
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
<link href="../includes/style_prac2.css" rel="stylesheet" type="text/css" />
<title>Scheduling System - System Documentation &amp; User Manual</title>
</head>
<body>
<div id="container">
<?php include("../includes/shedulo_shell.php"); ?>
  <div id="content">
	<div id="left" style="width: 100%;">
		<div class="tt-hero-banner" style="margin-top: 10px;">
			<div>
				<div style="display: flex; align-items: center; gap: 10px; margin-bottom: 4px;">
					<h2 style="margin: 0; color: #0f172a; font-size: 20px; font-weight: 800; text-align: left; letter-spacing: -0.2px;">System User Manual &amp; Administrator Guide</h2>
					<span class="badge-pill badge-green">Official Documentation</span>
				</div>
				<div style="font-size: 12.5px; color: #64748b; font-weight: 500;">
					Comprehensive documentation for managing academic cohorts, faculty profiles, subjects, and generating conflict-free timetables.
				</div>
			</div>
			<div style="display: flex; gap: 8px;">
				<a href="about_dev.php" style="background: #2563eb; color: #ffffff; padding: 8px 14px; border-radius: 8px; text-decoration: none; font-size: 12.5px; font-weight: 600; display: inline-flex; align-items: center; gap: 6px;">👨‍💻 Developer Credits</a>
			</div>
		</div>

		<!-- Developer Attribution Banner -->
		<div style="background: #ecfdf5; border: 1.5px solid #a7f3d0; border-radius: 12px; padding: 16px 20px; margin-top: 18px; display: flex; align-items: center; justify-content: space-between; box-shadow: 0 2px 8px rgba(16, 185, 129, 0.08);">
			<div style="display: flex; align-items: center; gap: 12px;">
				<span class="material-symbols-outlined" style="font-size: 24px; color: #059669;">verified</span>
				<div>
					<div style="font-size: 14px; font-weight: 800; color: #065f46;">This portal is done by K MANOJ [<a href="mailto:manojkumar911088@gmail.com" style="color: #047857; text-decoration: underline;">manojkumar911088@gmail.com</a>]</div>
					<div style="font-size: 12px; color: #047857; margin-top: 2px;">Lead Software Architect &bull; Automated Timetable Generation &amp; Academic Scheduling Platform</div>
				</div>
			</div>
			<span class="badge-pill badge-green" style="font-size: 11px; padding: 4px 10px;">Primary Maintainer</span>
		</div>

		<div style="display: flex; flex-direction: column; gap: 18px; margin-top: 20px;">
			<!-- Step 1 -->
			<div style="background: #ffffff; border: 1px solid #e2e8f0; border-radius: 12px; padding: 22px; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.02);">
				<div style="display: flex; align-items: center; gap: 12px; margin-bottom: 8px;">
					<span style="background: #eff6ff; color: #2563eb; font-weight: 800; width: 32px; height: 32px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 14px;">1</span>
					<h3 style="margin: 0; font-size: 16px; color: #0f172a;">Role-Based Access Control (RBAC Architecture)</h3>
				</div>
				<p style="font-size: 13px; color: #475569; line-height: 1.6; margin-left: 44px;">
					The platform supports three distinct security tiers:
					<br/>&bull; <strong>Administrator:</strong> Full orchestration authority across Master Timetable generation, lecture swaps, department creation, and user management. Administrators have <em>exclusive access</em> to confidential <strong>Faculty Details</strong> (salary, experience, 10th/12th/degree qualifications, speciality) and approval of timing change requests.
					<br/>&bull; <strong>Faculty:</strong> Restrictive portal giving professors access <em>only to their own teaching timetable and credentials</em>. Faculty can submit formal timing change requests with official reasons for admin verification.
					<br/>&bull; <strong>Counselor:</strong> Student-centric console allowing real-time inspection of student cohort timetables, room schedules, and student consultation lookups.
				</p>
			</div>

			<!-- Step 2 -->
			<div style="background: #ffffff; border: 1px solid #e2e8f0; border-radius: 12px; padding: 22px; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.02);">
				<div style="display: flex; align-items: center; gap: 12px; margin-bottom: 8px;">
					<span style="background: #ecfdf5; color: #059669; font-weight: 800; width: 32px; height: 32px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 14px;">2</span>
					<h3 style="margin: 0; font-size: 16px; color: #0f172a;">Course &amp; Subject Curriculum Configuration</h3>
				</div>
				<p style="font-size: 13px; color: #475569; line-height: 1.6; margin-left: 44px;">
					Navigate to <strong>Subjects</strong> under <strong>Student</strong>. Modules are categorized into distinct pedagogical types:
					<br/>&bull; <em>THEORY:</em> Shuffled across distinct slots with guaranteed 1-period free gap between faculty theory classes and 0 back-to-back student clashes.
					<br/>&bull; <em>LABORATORY:</em> 2 continuous hours simultaneously distributed across two lab rooms for Batch 1 and Batch 2.
					<br/>&bull; <em>MINI PROJECT:</em> 4 hours/week scheduled strictly <strong>after the lunch hour</strong> in afternoon blocks (02:00 PM &ndash; 04:00 PM).
					<br/>&bull; <em>MAJOR PROJECT:</em> 6 hours/week scheduled across 3 afternoons post-lunch.
					<br/>&bull; <em>NSS / COMMUNITY PROJECT:</em> 2 continuous afternoon hours.
				</p>
			</div>

			<!-- Step 3 -->
			<div style="background: #ffffff; border: 1px solid #e2e8f0; border-radius: 12px; padding: 22px; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.02);">
				<div style="display: flex; align-items: center; gap: 12px; margin-bottom: 8px;">
					<span style="background: #faf5ff; color: #7c3aed; font-weight: 800; width: 32px; height: 32px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 14px;">3</span>
					<h3 style="margin: 0; font-size: 16px; color: #0f172a;">Classroom &amp; Laboratory Facility Allocation</h3>
				</div>
				<p style="font-size: 13px; color: #475569; line-height: 1.6; margin-left: 44px;">
					Access <strong>Rooms</strong> from the sidebar to inspect room occupancy grids. The scheduler ensures zero room collisions and automatically enforces lab room assignment for practical sessions and lecture rooms for theory.
				</p>
			</div>

			<!-- Step 4 -->
			<div style="background: #ffffff; border: 1px solid #e2e8f0; border-radius: 12px; padding: 22px; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.02);">
				<div style="display: flex; align-items: center; gap: 12px; margin-bottom: 8px;">
					<span style="background: #fff7ed; color: #ea580c; font-weight: 800; width: 32px; height: 32px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 14px;">4</span>
					<h3 style="margin: 0; font-size: 16px; color: #0f172a;">Automated Timetable Generation Engine</h3>
				</div>
				<p style="font-size: 13px; color: #475569; line-height: 1.6; margin-left: 44px;">
					Visit <strong>Master Timetable</strong> or click <em>Generate Timetable</em> on the Admin Dashboard. The system dynamically runs the multi-phase genetic scheduling engine across a 6-day academic cycle (Mon &ndash; Sat), achieving 100% curriculum completion with contiguous packing and zero faculty theory collisions.
				</p>
			</div>
		</div>
	</div>
	<div id="footerline">
		<p align="center" class="style4"><a href="help.php">System Manual</a> | <a href="about_dev.php">Developer</a> | <a href="mastertt.php">Timetable</a></p>
	</div>
  </div>
  <div id="footer">This portal is done by K MANOJ [manojkumar911088@gmail.com]</div>	
  </div><!-- /shedulo-main-wrapper -->
</div><!-- /container -->
</body>
</html>

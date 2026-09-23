<?php 
session_start();
include("../includes/session.php");
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
<link href="../includes/style_prac2.css" rel="stylesheet" type="text/css" />
<title>Scheduling System - About the Scheduling System</title>
</head>
<body>
<div id="container">
<?php include("../includes/shedulo_shell.php"); ?>
  <div id="content">
	<div id="left" style="width: 100%;">
		<div class="tt-hero-banner" style="margin-top: 10px;">
			<div>
				<div style="display: flex; align-items: center; gap: 10px; margin-bottom: 4px;">
					<h2 style="margin: 0; color: #0f172a; font-size: 20px; font-weight: 800; text-align: left; letter-spacing: -0.2px;">Automated Timetable Generation Engine</h2>
					<span class="badge-pill badge-blue">Architecture &amp; Docs</span>
				</div>
				<div style="font-size: 12.5px; color: #64748b; font-weight: 500;">
					Intelligent multi-constraint university schedule generator and real-time conflict-free optimization suite.
				</div>
			</div>
			<div>
				<a href="mastertt.php" style="background: #2563eb; color: #ffffff; padding: 8px 14px; border-radius: 8px; text-decoration: none; font-size: 12.5px; font-weight: 600; display: inline-flex; align-items: center; gap: 6px;">📅 Master Grid</a>
			</div>
		</div>

		<div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(320px, 1fr)); gap: 20px; margin-top: 20px;">
			<!-- Card 1: System Purpose -->
			<div style="background: #ffffff; border: 1px solid #e2e8f0; border-radius: 12px; padding: 22px; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.02);">
				<div style="display: flex; align-items: center; gap: 10px; margin-bottom: 12px;">
					<span style="background: #eff6ff; color: #2563eb; width: 36px; height: 36px; border-radius: 8px; display: flex; align-items: center; justify-content: center; font-size: 18px;">🎯</span>
					<h3 style="margin: 0; font-size: 16px; color: #0f172a; font-weight: 700;">System Purpose</h3>
				</div>
				<p style="font-size: 13px; color: #475569; line-height: 1.6; margin: 0;">
					Through automated combinatorial optimization, the engine schedules all university lectures, laboratories, faculty teaching assignments, and campus room capacities with zero overlapping clashes.
				</p>
			</div>

			<!-- Card 2: Core Technologies -->
			<div style="background: #ffffff; border: 1px solid #e2e8f0; border-radius: 12px; padding: 22px; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.02);">
				<div style="display: flex; align-items: center; gap: 10px; margin-bottom: 12px;">
					<span style="background: #ecfdf5; color: #059669; width: 36px; height: 36px; border-radius: 8px; display: flex; align-items: center; justify-content: center; font-size: 18px;">⚡</span>
					<h3 style="margin: 0; font-size: 16px; color: #0f172a; font-weight: 700;">Core Architecture</h3>
				</div>
				<ul style="font-size: 13px; color: #475569; line-height: 1.8; margin: 0; padding-left: 18px;">
					<li><strong>Optimization Core:</strong> High-performance C++ Genetic Algorithm (`main.exe`)</li>
					<li><strong>Backend:</strong> PHP 8.1+ with MySQLi Relational Persistence</li>
					<li><strong>UI Design:</strong> Shedulo Kinetic Campus Design System</li>
					<li><strong>Constraint Solver:</strong> Strict Professor, Cohort, and Room collision matrices</li>
				</ul>
			</div>

			<!-- Card 3: Academic Rules Supported -->
			<div style="background: #ffffff; border: 1px solid #e2e8f0; border-radius: 12px; padding: 22px; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.02); grid-column: 1 / -1;">
				<div style="display: flex; align-items: center; gap: 10px; margin-bottom: 12px;">
					<span style="background: #faf5ff; color: #7c3aed; width: 36px; height: 36px; border-radius: 8px; display: flex; align-items: center; justify-content: center; font-size: 18px;">📋</span>
					<h3 style="margin: 0; font-size: 16px; color: #0f172a; font-weight: 700;">Enforced Timetable Constraints</h3>
				</div>
				<div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(240px, 1fr)); gap: 15px; margin-top: 10px;">
					<div style="background: #f8fafc; padding: 14px; border-radius: 8px; border: 1px solid #e2e8f0;">
						<div style="font-weight: 700; color: #1e293b; margin-bottom: 4px;">🗓️ 6-Day Academic Cycle</div>
						<div style="font-size: 12px; color: #64748b;">Evenly distributes courses across Monday through Saturday to prevent cognitive fatigue.</div>
					</div>
					<div style="background: #f8fafc; padding: 14px; border-radius: 8px; border: 1px solid #e2e8f0;">
						<div style="font-weight: 700; color: #1e293b; margin-bottom: 4px;">🔬 Lab Batch Splitting</div>
						<div style="font-size: 12px; color: #64748b;">Concurrent 2-period laboratory blocks with synchronized multi-batch room allocations.</div>
					</div>
					<div style="background: #f8fafc; padding: 14px; border-radius: 8px; border: 1px solid #e2e8f0;">
						<div style="font-weight: 700; color: #1e293b; margin-bottom: 4px;">⏸️ Faculty 1-Period Lecture Gap</div>
						<div style="font-size: 12px; color: #64748b;">Mandatory 1-period breathing gap after faculty theory classes for preparation.</div>
					</div>
					<div style="background: #f8fafc; padding: 14px; border-radius: 8px; border: 1px solid #e2e8f0;">
						<div style="font-weight: 700; color: #1e293b; margin-bottom: 4px;">🏗️ Project Periods</div>
						<div style="font-size: 12px; color: #64748b;">Mini Projects and Major Projects scheduled strictly after lunch (02:00 PM - 04:00 PM).</div>
					</div>
				</div>
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

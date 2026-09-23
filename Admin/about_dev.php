<?php 
session_start();
include("../includes/session.php");
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
<link href="../includes/style_prac2.css" rel="stylesheet" type="text/css" />
<title>Scheduling System - Portal Developer</title>
</head>
<body>
<div id="container">
<?php include("../includes/shedulo_shell.php"); ?>
  <div id="content">
	<div id="left" style="width: 100%;">
		<div class="tt-hero-banner" style="margin-top: 10px;">
			<div>
				<div style="display: flex; align-items: center; gap: 10px; margin-bottom: 4px;">
					<h2 style="margin: 0; color: #0f172a; font-size: 20px; font-weight: 800; text-align: left; letter-spacing: -0.2px;">Portal Architect &amp; Developer</h2>
					<span class="badge-pill badge-green">Lead Engineering</span>
				</div>
				<div style="font-size: 12.5px; color: #64748b; font-weight: 500;">
					This portal is done by <strong>K MANOJ</strong> [<a href="mailto:manojkumar911088@gmail.com" style="color: #2563eb; font-weight: 600;">manojkumar911088@gmail.com</a>] &mdash; Automated Timetable Generation &amp; Academic Orchestration Platform.
				</div>
			</div>
			<div>
				<a href="admin.php" style="background: #2563eb; color: #ffffff; padding: 8px 14px; border-radius: 8px; text-decoration: none; font-size: 12.5px; font-weight: 600; display: inline-flex; align-items: center; gap: 6px;">🏠 Dashboard</a>
			</div>
		</div>

		<div style="max-width: 650px; margin: 30px auto;">
			<!-- Developer Spotlight Bento Card -->
			<div style="background: #ffffff; border: 1.5px solid #e2e8f0; border-radius: 16px; padding: 32px; text-align: center; box-shadow: 0 10px 25px -5px rgba(0,0,0,0.05); position: relative; overflow: hidden;">
				<div style="position: absolute; top: 0; left: 0; right: 0; height: 6px; background: linear-gradient(90deg, #10b981, #2563eb, #8b5cf6);"></div>
				
				<div style="width: 76px; height: 76px; border-radius: 50%; background: linear-gradient(135deg, #2563eb 0%, #1d4ed8 100%); color: #ffffff; font-size: 30px; font-weight: 800; display: flex; align-items: center; justify-content: center; margin: 0 auto 16px auto; box-shadow: 0 4px 14px rgba(37, 99, 235, 0.35);">
					KM
				</div>
				<h3 style="margin: 0 0 6px 0; color: #0f172a; font-size: 22px; font-weight: 800; letter-spacing: -0.3px;">K MANOJ</h3>
				<div style="font-size: 13.5px; color: #64748b; font-weight: 600; margin-bottom: 12px;">Chief Portal Architect &amp; Full Stack Software Engineer</div>
				
				<div style="display: inline-flex; gap: 8px; margin-bottom: 20px;">
					<span class="badge-pill badge-green" style="font-size: 11px; padding: 4px 10px;">Automated Timetable Generator</span>
					<span class="badge-pill badge-blue" style="font-size: 11px; padding: 4px 10px;">Role-Based Access Control</span>
					<span class="badge-pill badge-purple" style="font-size: 11px; padding: 4px 10px;">Conflict-Free Engine</span>
				</div>

				<div style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 10px; padding: 18px; margin-top: 10px; text-align: left; font-size: 13px; line-height: 1.6; color: #334155;">
					<p style="margin: 0 0 8px 0;">
						💡 <strong>About the System:</strong> The Automatic Timetable Generator portal was completely re-architected and modernized by <strong>K MANOJ</strong> to resolve core faculty gap constraints, back-to-back theory isolations, parallel lab multi-batch distributions, and dynamic role-based access for Administrators, Faculty, and Counselors.
					</p>
					<p style="margin: 0; color: #047857; font-weight: 600;">
						✨ <em>"This portal is done by K MANOJ [manojkumar911088@gmail.com]"</em>
					</p>
				</div>

				<div style="margin-top: 24px; padding-top: 16px; border-top: 1px dashed #e2e8f0; font-size: 13.5px; color: #475569; display: flex; justify-content: center; align-items: center; gap: 8px;">
					<span>✉️ Direct Inquiries:</span>
					<a href="mailto:manojkumar911088@gmail.com" style="color: #2563eb; font-weight: 700; text-decoration: none;">manojkumar911088@gmail.com</a>
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

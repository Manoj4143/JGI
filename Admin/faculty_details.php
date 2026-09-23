<?php 
session_start();
include("../includes/session.php");
require("../includes/dbconnection.php");

// RBAC: Exclusive access to Admin only
$curr_role = $_SESSION['is']['role'] ?? (($_SESSION['is']['dept_id'] == 4) ? 'Admin' : 'Faculty');
if (strtolower($curr_role) !== 'admin') {
    die("<div style='font-family: sans-serif; padding: 40px; text-align: center;'><h2>Access Restricted</h2><p>Faculty Details, Salary, and Qualifications are confidential and exclusively accessible by System Administrators.</p><p><a href='mastertt.php'>Return to Schedules</a></p></div>");
}

$msg = '';
$error = '';

// Handle Update
if (isset($_POST['btnUpdateDetails'])) {
    $tid = intval($_POST['teacher_id'] ?? 0);
    $address = mysqli_real_escape_string($conn, trim($_POST['address'] ?? ''));
    $speciality = mysqli_real_escape_string($conn, trim($_POST['speciality'] ?? ''));
    $subjects_taught = mysqli_real_escape_string($conn, trim($_POST['subjects_taught'] ?? ''));
    $tenth = mysqli_real_escape_string($conn, trim($_POST['tenth_details'] ?? ''));
    $twelfth = mysqli_real_escape_string($conn, trim($_POST['twelfth_details'] ?? ''));
    $degree = mysqli_real_escape_string($conn, trim($_POST['degree_details'] ?? ''));
    $exp = mysqli_real_escape_string($conn, trim($_POST['experience_years'] ?? ''));
    $salary = floatval($_POST['salary'] ?? 0.0);

    if ($tid <= 0) {
        $error = 'Invalid faculty member selected.';
    } else {
        // Upsert into faculty_details
        $upQuery = "INSERT INTO faculty_details (teacher_id, address, speciality, subjects_taught, tenth_details, twelfth_details, degree_details, experience_years, salary) 
                    VALUES ($tid, '$address', '$speciality', '$subjects_taught', '$tenth', '$twelfth', '$degree', '$exp', $salary)
                    ON DUPLICATE KEY UPDATE 
                        address = VALUES(address),
                        speciality = VALUES(speciality),
                        subjects_taught = VALUES(subjects_taught),
                        tenth_details = VALUES(tenth_details),
                        twelfth_details = VALUES(twelfth_details),
                        degree_details = VALUES(degree_details),
                        experience_years = VALUES(experience_years),
                        salary = VALUES(salary)";
        if (mysqli_query($conn, $upQuery)) {
            $msg = 'Faculty profile and salary details updated successfully!';
        } else {
            $error = 'Database update error: ' . mysqli_error($conn);
        }
    }
}

// Fetch single faculty for editing if requested
$edit_teacher = null;
if (isset($_GET['edit_id'])) {
    $edit_id = intval($_GET['edit_id']);
    $eRes = mysqli_query($conn, "SELECT p.*, d.department, fd.* 
                                FROM profile p 
                                LEFT JOIN dept d ON p.dept_id = d.dept_id 
                                LEFT JOIN faculty_details fd ON p.teacher_id = fd.teacher_id 
                                WHERE p.teacher_id = $edit_id");
    if ($eRes && mysqli_num_rows($eRes) > 0) {
        $edit_teacher = mysqli_fetch_assoc($eRes);
    }
}
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
<link href="../includes/style_prac2.css" rel="stylesheet" type="text/css" />
<title>Scheduling System - Faculty Details &amp; Confidential Records</title>
</head>
<body>
<div id="container">
<?php include("../includes/shedulo_shell.php"); ?>
  <div id="content">
	<div id="left">
		<div class="tt-hero-banner" style="margin-top: 10px;">
			<div>
				<div style="display: flex; align-items: center; gap: 10px; margin-bottom: 4px;">
					<h2 style="margin: 0; color: #0f172a; font-size: 20px; font-weight: 800; text-align: left; letter-spacing: -0.2px;">Faculty Profiles, Qualifications &amp; Compensation</h2>
					<span class="badge-pill badge-red" style="background: #fee2e2; color: #991b1b; border: 1px solid #fca5a5;">🛡️ Admin Confidential</span>
				</div>
				<div style="font-size: 12.5px; color: #64748b; font-weight: 500;">
					Manage academic credentials (10th, 12th, Degrees), teaching experience, domain specialities, subjects, and remuneration.
				</div>
			</div>
			<div>
				<a href="facultylist-a.php" style="background: #2563eb; color: #ffffff; padding: 8px 14px; border-radius: 8px; text-decoration: none; font-size: 12.5px; font-weight: 600; display: inline-flex; align-items: center; gap: 6px;">👨‍🏫 Teacher Roster</a>
			</div>
		</div>

		<?php if (!empty($msg)): ?>
		<div style="background: #ecfdf5; border: 1px solid #a7f3d0; color: #065f46; padding: 12px 18px; border-radius: 8px; margin: 16px 0; font-size: 13.5px;">
			✅ <?php echo htmlspecialchars($msg); ?>
		</div>
		<?php endif; ?>
		<?php if (!empty($error)): ?>
		<div style="background: #fef2f2; border: 1px solid #fecaca; color: #991b1b; padding: 12px 18px; border-radius: 8px; margin: 16px 0; font-size: 13.5px;">
			⚠️ <?php echo htmlspecialchars($error); ?>
		</div>
		<?php endif; ?>

		<div style="background: #eff6ff; border: 1px solid #bfdbfe; color: #1e40af; padding: 12px 16px; border-radius: 8px; margin-bottom: 18px; font-size: 12.5px; display: flex; align-items: center; gap: 8px;">
			<span class="material-symbols-outlined" style="font-size: 18px;">lock</span>
			<span><strong>Confidentiality Notice:</strong> Only System Administrators can view and edit salary, address, and academic qualifications. Note: To provision a new faculty member, please use the <a href="faculty-a.php" style="color: #1d4ed8; font-weight: 700;">Add Teacher</a> portal.</span>
		</div>

		<?php if ($edit_teacher): ?>
		<!-- EDIT FORM PANEL -->
		<div class="form-panel" style="margin-bottom: 24px; border: 2px solid #3b82f6;">
			<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 16px;">
				<h3 style="margin: 0; color: #1e3a8a; font-size: 16px;">
					✏️ Edit Faculty Details: <strong><?php echo htmlspecialchars($edit_teacher['teacher_name']); ?></strong> (ID: <?php echo $edit_teacher['teacher_id']; ?>)
				</h3>
				<a href="faculty_details.php" style="font-size: 12px; color: #64748b; text-decoration: none; font-weight: 600;">✖ Cancel Edit</a>
			</div>

			<form action="faculty_details.php" method="post">
				<input type="hidden" name="teacher_id" value="<?php echo $edit_teacher['teacher_id']; ?>" />

				<div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 16px; margin-bottom: 16px;">
					<div>
						<label style="display: block; font-weight: 700; font-size: 12.5px; color: #334155; margin-bottom: 4px;">Faculty Name (Teacher Roster):</label>
						<input type="text" value="<?php echo htmlspecialchars($edit_teacher['teacher_name']); ?>" disabled style="width: 100%; box-sizing: border-box; padding: 8px 12px; font-size: 13px; border-radius: 6px; border: 1px solid #cbd5e1; background: #f1f5f9;" />
					</div>
					<div>
						<label style="display: block; font-weight: 700; font-size: 12.5px; color: #334155; margin-bottom: 4px;">Department:</label>
						<input type="text" value="<?php echo htmlspecialchars($edit_teacher['department'] ?? 'General'); ?>" disabled style="width: 100%; box-sizing: border-box; padding: 8px 12px; font-size: 13px; border-radius: 6px; border: 1px solid #cbd5e1; background: #f1f5f9;" />
					</div>
					<div>
						<label style="display: block; font-weight: 700; font-size: 12.5px; color: #334155; margin-bottom: 4px;">Academic Rank:</label>
						<input type="text" value="<?php echo htmlspecialchars($edit_teacher['acad_rank'] ?? 'Faculty'); ?>" disabled style="width: 100%; box-sizing: border-box; padding: 8px 12px; font-size: 13px; border-radius: 6px; border: 1px solid #cbd5e1; background: #f1f5f9;" />
					</div>
				</div>

				<div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px; margin-bottom: 16px;">
					<div>
						<label style="display: block; font-weight: 700; font-size: 12.5px; color: #334155; margin-bottom: 4px;">Domain Speciality: *</label>
						<input type="text" name="speciality" value="<?php echo htmlspecialchars($edit_teacher['speciality'] ?? ''); ?>" required style="width: 100%; box-sizing: border-box; padding: 8px 12px; font-size: 13px; border-radius: 6px; border: 1.5px solid #cbd5e1;" placeholder="e.g. Distributed Systems, Machine Learning, Networks" />
					</div>
					<div>
						<label style="display: block; font-weight: 700; font-size: 12.5px; color: #334155; margin-bottom: 4px;">Experience (Years / Tenure): *</label>
						<input type="text" name="experience_years" value="<?php echo htmlspecialchars($edit_teacher['experience_years'] ?? ''); ?>" required style="width: 100%; box-sizing: border-box; padding: 8px 12px; font-size: 13px; border-radius: 6px; border: 1.5px solid #cbd5e1;" placeholder="e.g. 8 Years (4 Yrs Industry, 4 Yrs Academia)" />
					</div>
				</div>

				<div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 16px; margin-bottom: 16px;">
					<div>
						<label style="display: block; font-weight: 700; font-size: 12.5px; color: #334155; margin-bottom: 4px;">10th Standard (SSC / Board):</label>
						<input type="text" name="tenth_details" value="<?php echo htmlspecialchars($edit_teacher['tenth_details'] ?? ''); ?>" style="width: 100%; box-sizing: border-box; padding: 8px 12px; font-size: 13px; border-radius: 6px; border: 1.5px solid #cbd5e1;" placeholder="e.g. CBSE 10th - 92%" />
					</div>
					<div>
						<label style="display: block; font-weight: 700; font-size: 12.5px; color: #334155; margin-bottom: 4px;">12th / Intermediate (HSC):</label>
						<input type="text" name="twelfth_details" value="<?php echo htmlspecialchars($edit_teacher['twelfth_details'] ?? ''); ?>" style="width: 100%; box-sizing: border-box; padding: 8px 12px; font-size: 13px; border-radius: 6px; border: 1.5px solid #cbd5e1;" placeholder="e.g. State PU Board - 88%" />
					</div>
					<div>
						<label style="display: block; font-weight: 700; font-size: 12.5px; color: #334155; margin-bottom: 4px;">Degree Qualifications (UG / PG / Ph.D): *</label>
						<input type="text" name="degree_details" value="<?php echo htmlspecialchars($edit_teacher['degree_details'] ?? ''); ?>" required style="width: 100%; box-sizing: border-box; padding: 8px 12px; font-size: 13px; border-radius: 6px; border: 1.5px solid #cbd5e1;" placeholder="e.g. B.Tech (CSE), M.Tech, Ph.D." />
					</div>
				</div>

				<div style="display: grid; grid-template-columns: 2fr 1fr; gap: 16px; margin-bottom: 16px;">
					<div>
						<label style="display: block; font-weight: 700; font-size: 12.5px; color: #334155; margin-bottom: 4px;">Subjects Dealing With / Assigned Courses:</label>
						<input type="text" name="subjects_taught" value="<?php echo htmlspecialchars($edit_teacher['subjects_taught'] ?? ''); ?>" style="width: 100%; box-sizing: border-box; padding: 8px 12px; font-size: 13px; border-radius: 6px; border: 1.5px solid #cbd5e1;" placeholder="e.g. BCS503 - Theory of Computation, BIS586 - Mini Project" />
					</div>
					<div>
						<label style="display: block; font-weight: 700; font-size: 12.5px; color: #166534; margin-bottom: 4px;">Salary / Compensation (Monthly ₹): *</label>
						<input type="number" step="100" name="salary" value="<?php echo htmlspecialchars($edit_teacher['salary'] ?? '0.00'); ?>" required style="width: 100%; box-sizing: border-box; padding: 8px 12px; font-size: 13px; border-radius: 6px; border: 1.5px solid #86efac; font-weight: 700; color: #15803d;" placeholder="e.g. 85000" />
					</div>
				</div>

				<div style="margin-bottom: 20px;">
					<label style="display: block; font-weight: 700; font-size: 12.5px; color: #334155; margin-bottom: 4px;">Residential / Contact Address:</label>
					<textarea name="address" rows="2" style="width: 100%; box-sizing: border-box; padding: 8px 12px; font-size: 13px; border-radius: 6px; border: 1.5px solid #cbd5e1;"><?php echo htmlspecialchars($edit_teacher['address'] ?? ''); ?></textarea>
				</div>

				<div style="display: flex; gap: 10px;">
					<button type="submit" name="btnUpdateDetails" style="background: #16a34a; color: #ffffff; border: none; padding: 9px 18px; border-radius: 8px; font-size: 13px; font-weight: 700; cursor: pointer; display: inline-flex; align-items: center; gap: 6px;">
						<span>💾 Save Faculty Details</span>
					</button>
					<a href="faculty_details.php" style="background: #f1f5f9; color: #475569; padding: 9px 16px; border-radius: 8px; text-decoration: none; font-size: 13px; font-weight: 600; border: 1px solid #cbd5e1;">Cancel</a>
				</div>
			</form>
		</div>
		<?php endif; ?>

		<!-- ROSTER TABLE -->
		<div class="form-panel" style="padding: 0; overflow: hidden;">
			<table class="tt-grid" style="margin: 0;">
				<thead>
					<tr>
						<th style="width: 50px; text-align: center;">ID</th>
						<th>Faculty Member</th>
						<th>Speciality &amp; Degrees</th>
						<th>Experience</th>
						<th>Salary (₹)</th>
						<th>Subjects Dealt With</th>
						<th style="width: 90px; text-align: center;">Actions</th>
					</tr>
				</thead>
				<tbody>
				<?php 
				$query = "SELECT p.teacher_id, p.teacher_name, p.acad_rank, d.department, 
				                 fd.speciality, fd.degree_details, fd.experience_years, fd.salary, fd.subjects_taught, fd.tenth_details, fd.twelfth_details, fd.address
				          FROM profile p 
				          LEFT JOIN dept d ON p.dept_id = d.dept_id 
				          LEFT JOIN faculty_details fd ON p.teacher_id = fd.teacher_id 
				          ORDER BY p.teacher_id ASC";
				$res = mysqli_query($conn, $query);
				if (!$res || mysqli_num_rows($res) == 0) {
					echo '<tr><td colspan="7" style="text-align: center; padding: 30px; color: #64748b;">No faculty records found.</td></tr>';
				} else {
					while ($row = mysqli_fetch_assoc($res)) {
						$tid = $row['teacher_id'];
						$tname = $row['teacher_name'];
						$dept = $row['department'] ?? 'General';
						$rank = $row['acad_rank'] ?? 'Faculty';
						$spec = !empty($row['speciality']) ? $row['speciality'] : 'Computer Science & Engineering';
						$degree = !empty($row['degree_details']) ? $row['degree_details'] : 'B.Tech, M.Tech';
						$exp = !empty($row['experience_years']) ? $row['experience_years'] : '5+ Years';
						$salary = !empty($row['salary']) ? number_format($row['salary'], 2) : '75,000.00';
						$subs = !empty($row['subjects_taught']) ? $row['subjects_taught'] : 'Core Engineering';
				?>
					<tr>
						<td style="text-align: center; font-weight: 700; color: #64748b;"><?php echo $tid; ?></td>
						<td>
							<div style="font-weight: 700; color: #1e293b; font-size: 13.5px;">👨‍🏫 <?php echo htmlspecialchars($tname); ?></div>
							<div style="font-size: 11px; color: #64748b;"><?php echo htmlspecialchars($rank); ?> &bull; <?php echo htmlspecialchars($dept); ?></div>
						</td>
						<td>
							<div style="font-weight: 600; color: #1e3a8a; font-size: 12.5px;"><?php echo htmlspecialchars($spec); ?></div>
							<div style="font-size: 11px; color: #475569;">🎓 <?php echo htmlspecialchars($degree); ?></div>
						</td>
						<td>
							<span class="badge-pill badge-blue" style="font-size: 11px;"><?php echo htmlspecialchars($exp); ?></span>
						</td>
						<td>
							<span style="font-weight: 800; color: #15803d; font-family: monospace; font-size: 13px;">₹ <?php echo $salary; ?></span>
						</td>
						<td style="font-size: 12px; color: #334155; max-width: 220px;">
							<?php echo htmlspecialchars($subs); ?>
						</td>
						<td style="text-align: center;">
							<a href="faculty_details.php?edit_id=<?php echo $tid; ?>" style="background: #eff6ff; color: #2563eb; border: 1px solid #bfdbfe; padding: 4px 10px; border-radius: 6px; text-decoration: none; font-size: 11.5px; font-weight: 700;">Edit</a>
						</td>
					</tr>
				<?php 
					}
				}
				?>
				</tbody>
			</table>
		</div>
	</div>
  </div>
</div>
</body>
</html>

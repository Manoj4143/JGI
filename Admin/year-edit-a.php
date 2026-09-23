<?php 
session_start();
include("../includes/session.php");
require("../includes/dbconnection.php");

$year_id = intval($_REQUEST['year_id'] ?? 0);
$error = '';

$res = mysqli_query($conn, "SELECT * FROM school_yr WHERE year_id = $year_id");
if (!$res || mysqli_num_rows($res) == 0) {
    die("<div style='font-family: sans-serif; padding: 40px; text-align: center;'><h2>School Year Not Found</h2><p><a href='yearlist-a.php'>Return to School Years</a></p></div>");
}
$yRow = mysqli_fetch_assoc($res);

if (isset($_POST['cmdSubmit'])) { 	
    $year = trim($_POST['txtyear'] ?? '');

    if ($year == "") {
        $error = 'School Year is required.';
    } else {
        $year_clean = mysqli_real_escape_string($conn, $year);
        $upd = mysqli_query($conn, "UPDATE school_yr SET school_year = '$year_clean' WHERE year_id = $year_id");
        if ($upd) {
            header("Location: yearlist-a.php");
            exit;
        } else {
            $error = 'Database update error: ' . mysqli_error($conn);
        }
    }
}
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
<link href="../includes/style_prac2.css" rel="stylesheet" type="text/css" />
<title>Scheduling System - Edit School Year</title>
</head>
<body>
<div id="container">
<?php include("../includes/shedulo_shell.php"); ?>
  <div id="content">
	<div id="left">
		<div class="tt-hero-banner" style="margin-top: 10px;">
			<div>
				<div style="display: flex; align-items: center; gap: 10px; margin-bottom: 4px;">
					<h2 style="margin: 0; color: #0f172a; font-size: 20px; font-weight: 800; text-align: left; letter-spacing: -0.2px;">Edit School Year #<?php echo $year_id; ?></h2>
					<span class="badge-pill badge-blue">Academic Session</span>
				</div>
				<div style="font-size: 12.5px; color: #64748b; font-weight: 500;">
					Modify academic session name or calendar school year.
				</div>
			</div>
			<div>
				<a href="yearlist-a.php" style="background: #2563eb; color: #ffffff; padding: 8px 14px; border-radius: 8px; text-decoration: none; font-size: 12.5px; font-weight: 600; display: inline-flex; align-items: center; gap: 6px;">School Years</a>
			</div>
		</div>

		<?php if (!empty($error)): ?>
		<div style="background: #fef2f2; border: 1px solid #fecaca; color: #991b1b; padding: 12px 18px; border-radius: 8px; margin: 16px 0; font-size: 13.5px;">
			Notice: <?php echo htmlspecialchars($error); ?>
		</div>
		<?php endif; ?>

		<form action="year-edit-a.php?year_id=<?php echo $year_id; ?>" name="form1" method="post">
		  <div class="form-panel">
			<h3 style="margin-top: 0; margin-bottom: 15px; color: #1e3a8a; font-size: 16px;">Modify Session Year</h3>
			
			<div style="margin-bottom: 20px;">
				<label style="display: block; font-weight: 700; font-size: 13px; color: #334155; margin-bottom: 6px;">School Year Session: *</label>
				<input type="text" name="txtyear" id="txtyear" value="<?php echo htmlspecialchars($yRow['school_year']); ?>" required style="width: 100%; padding: 10px 14px; font-size: 13px; border-radius: 8px; border: 1.5px solid #cbd5e1;" />
			</div>

			<div style="display: flex; gap: 10px; margin-top: 25px;">
				<button type="submit" name="cmdSubmit" style="background: #2563eb; color: #ffffff; border: none; padding: 10px 20px; border-radius: 8px; font-size: 13px; font-weight: 700; cursor: pointer; display: inline-flex; align-items: center; gap: 6px;">
					<span>Update School Year</span>
				</button>
				<a href="yearlist-a.php" style="background: #f1f5f9; color: #475569; border: 1px solid #cbd5e1; padding: 10px 18px; border-radius: 8px; font-size: 13px; font-weight: 600; text-decoration: none;">Cancel</a>
			</div>
		  </div>
		</form>
	</div>
  </div>
</div>
</body>
</html>

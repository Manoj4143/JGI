<?php session_start(); ?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta charset="UTF-8" />
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<link href="../includes/style_prac2.css" rel="stylesheet" type="text/css" />
<title>Scheduling System</title>
<style type="text/css">
<!--
.style3 {font-family: Georgia, "Times New Roman", Times, serif}
.style20 {font-family: Georgia, "Times New Roman", Times, serif; font-size: x-small; }
.style21 {font-size: x-small}
.style22 {font-family: Geneva, Arial, Helvetica, sans-serif}
.style30 {font-family: "Courier New", Courier, monospace}
.style4 {font-size: 11px;
	font-family: Verdana, Arial, Helvetica, sans-serif;
}
-->
</style>
</head>
<?php 
   include("../includes/session.php");
   require ("../includes/dbconnection.php");
   require_once("../includes/cohort_helpers.php");
   if (isset($_POST['cmdSubmit'])) 
  	{ 		
		if ($_POST['psem']){
			header("Location: search_g_result.php?pG=". $_POST['psem']);
			exit;
		}
	}
?>
<body>
<div id="container">
<?php include("../includes/shedulo_shell.php"); ?>
  <div id="content">
	<div id="left">
		<div class="tt-hero-banner" style="margin-top: 10px;">
			<div>
				<div style="display: flex; align-items: center; gap: 10px; margin-bottom: 4px;">
					<h2 style="margin: 0; color: #0f172a; font-size: 20px; font-weight: 800; text-align: left; letter-spacing: -0.2px;">Semester &amp; Branch Schedule Lookup</h2>
					<span class="badge-pill badge-blue">Timetable Viewer</span>
				</div>
				<div style="font-size: 12.5px; color: #64748b; font-weight: 500;">
					Select your academic semester and engineering branch to inspect the 6-day academic timetable.
				</div>
			</div>
			<div>
				<a href="mastertt.php" style="background: #2563eb; color: #ffffff; padding: 8px 14px; border-radius: 8px; text-decoration: none; font-size: 12.5px; font-weight: 600; display: inline-flex; align-items: center; gap: 6px;">📅 Master Grid</a>
			</div>
		</div>

		<form action="<?php echo $_SERVER['PHP_SELF'] ?>" name="form1" method="post">
		  <div class="form-panel">
			<h3 style="margin-top: 0; margin-bottom: 15px; color: #1e3a8a; font-size: 16px;">Select Academic Semester &amp; Branch</h3>
			<div style="margin-bottom: 18px;">
				<label style="display: block; font-weight: 700; font-size: 13px; color: #334155; margin-bottom: 6px;">Choose Semester &amp; Department / Branch:</label>
				<select name="psem" id="psem" style="width: 100%; padding: 11px 14px; font-size: 13px; border-radius: 8px; border: 1.5px solid #cbd5e1; font-weight: 600; color: #1e293b;">
                  <?php 
				$result = mysqli_query($conn,"SELECT DISTINCT s.group_id FROM subjects s ORDER BY s.group_id ASC");
				
				if (!$result) 
					{
    					die("Query to show fields from table failed");
					}	
				
				while ($row = mysqli_fetch_assoc($result)) {  
					$group_id = $row['group_id'];
					$c_info = getCohortDetails($conn, $group_id);
					?>
					<option value="<?php echo htmlspecialchars($group_id); ?>"><?php echo htmlspecialchars($c_info['dropdown_label']); ?></option>
					<?php 
				} ?>
                </select>
			</div>
			<div style="text-align: right; margin-top: 15px;">
				<input type="submit" name="cmdSubmit" value="🔍 View Semester Timetable" style="font-size: 13.5px; padding: 10px 24px;" />
			</div>
		  </div>
	  </form>
    </div>
		<div id="program"></div>
		<div id="right">
		  <p>&nbsp;</p>
	</div>
		<p>&nbsp;</p>
		<p>&nbsp;</p>
		<div id="footerline">
		  <p align="center"><span class="style4"><a href="help.php">Help</a> | <a href="about_sched.php">Scheduling System</a>
                            </span>
          </p>
	  </div>
	</div>
	
	<div id="footer">Automatic Timetable Generator</div>		
  </div><!-- /shedulo-main-wrapper -->
</div><!-- /container -->
</body>
</html>

<script language="javascript" >
/*		function SubmitForm(form)
		{			
			var form = document.forms[0];		
			if ((form.pLName.value.length <1) ||
				(form.pFName.value.length <1) ||
				(form.pMIName.value.length <1)) 
				{						
				 return false; 		
				}				 									
			else
				{	
				  return true;
				}
		}
*/		
		function optionList_SelectedIndex()
		{
			//----------------------------------------------------------------------------------------------------------
			/*HTML/JavaScript - Working with selectedIndex (select, options, selectedIndex, text, value)
			The selectedIndex number can be used to reference the selected option in the select list. 
			Note: It is case sensitive. 		
			Make sure to capitalize the "I" in selectedIndex
			* selectedIndex - The number (base 0) of the item that is selected in the select list
			* value - For an option, what's in the value attribute.
			  If the value attribute is not set, text should be returned [* refer below]
				  o val1 is the value in the following HTML example
				  o <option value="val1">sea one</option>
			* text - For an option, what's in between the option tags
				  o sea two is the text in the following HTML example
				  o <option value="val2">sea two</option>*/
			var selObj = document.getElementById('pcourse');
			//var txtIndexObj = document.getElementById('txtIndex');
			var hidden_pcourseid_ValueObj = document.getElementById('hidden_pcourseid');
			var hidden_pcourse_TextObj = document.getElementById('hidden_pcourse');
			
			var selIndex = selObj.selectedIndex;
			//txtIndexObj.value = selIndex;
			hidden_pcourseid_ValueObj.value = selObj.options[selIndex].value;
			hidden_pcourse_TextObj.value = selObj.options[selIndex].text;
		
			//var form = document.forms[0]; --> Option B
			<!-- //Passing the data from the databse residence [ID] to hidden field
			//Source: http://www.trans4mind.com/personal_development/JavaScript2/select.htm /-- -->			
			//----------------------------------------------------------------------------------------------------------
function optionList1_SelectedIndex()
		{
			//----------------------------------------------------------------------------------------------------------
			/*HTML/JavaScript - Working with selectedIndex (select, options, selectedIndex, text, value)
			The selectedIndex number can be used to reference the selected option in the select list. 
			Note: It is case sensitive. 		
			Make sure to capitalize the "I" in selectedIndex
			* selectedIndex - The number (base 0) of the item that is selected in the select list
			* value - For an option, what's in the value attribute.
			  If the value attribute is not set, text should be returned [* refer below]
				  o val1 is the value in the following HTML example
				  o <option value="val1">sea one</option>
			* text - For an option, what's in between the option tags
				  o sea two is the text in the following HTML example
				  o <option value="val2">sea two</option>*/
			var selObj = document.getElementById('psem');
			//var txtIndexObj = document.getElementById('txtIndex');
			var hidden_psemid_ValueObj = document.getElementById('hidden_psemid');
			var hidden_psem_TextObj = document.getElementById('hidden_psem');
			
			var selIndex = selObj.selectedIndex;
			//txtIndexObj.value = selIndex;
			hidden_psemid_ValueObj.value = selObj.options[selIndex].value;
			hidden_psem_TextObj.value = selObj.options[selIndex].text;
		
			//var form = document.forms[0]; --> Option B
			<!-- //Passing the data from the databse residence [ID] to hidden field
			//Source: http://www.trans4mind.com/personal_development/JavaScript2/select.htm /-- -->			
			//----------------------------------------------------------------------------------------------------------
		}
		
		
		
		//hidden_psyid
		
								function optionList7_SelectedIndex()
		{
			//----------------------------------------------------------------------------------------------------------
			/*HTML/JavaScript - Working with selectedIndex (select, options, selectedIndex, text, value)
			The selectedIndex number can be used to reference the selected option in the select list. 
			Note: It is case sensitive. 		
			Make sure to capitalize the "I" in selectedIndex
			* selectedIndex - The number (base 0) of the item that is selected in the select list
			* value - For an option, what's in the value attribute.
			  If the value attribute is not set, text should be returned [* refer below]
				  o val1 is the value in the following HTML example
				  o <option value="val1">sea one</option>
			* text - For an option, what's in between the option tags
				  o sea two is the text in the following HTML example
				  o <option value="val2">sea two</option>*/
			var selObj = document.getElementById('psy');
			//var txtIndexObj = document.getElementById('txtIndex');
			var hidden_psyid_ValueObj = document.getElementById('hidden_psyid');
			var hidden_psy_TextObj = document.getElementById('hidden_psy');
			
			var selIndex = selObj.selectedIndex;
			//txtIndexObj.value = selIndex;
			hidden_psyid_ValueObj.value = selObj.options[selIndex].value;
			hidden_psy_TextObj.value = selObj.options[selIndex].text;
		
			//var form = document.forms[0]; --> Option B
			<!-- //Passing the data from the databse residence [ID] to hidden field
			//Source: http://www.trans4mind.com/personal_development/JavaScript2/select.htm /-- -->			
			//----------------------------------------------------------------------------------------------------------
		}
		
		
		
</script>	
<script language="javascript" >
	var form = document.forms[0];
	//purpose?: to retrieve what users last input on the field..
	form.pcourse.value = ("<?PHP echo $_POST['hidden_pcourse']; ?>");
	form.psy.value = ("<?PHP echo $_POST['hidden_psem']; ?>");
	form.psem.value = ("<?PHP echo $_POST['hidden_psem']; ?>");
	//alert (form.pCityM.value);				
</script>
<?php
/* $Id$ */
$PageSecurity = 14;
include('includes/session.inc');
$title = _('Form Designer');
include('includes/header.inc');
function FontSizes() {
	return array(6, 8, 10, 11, 12, 13, 14, 15, 16, 18, 20, 22, 24); //Possible font sizes
}
function SimpleTextLine($key) {
	/* Displays a table row containing the attributes for a
	 * line of text of type `SimpleText` and the values
	 * given in the object $key
	 */
	$FontSizes=FontSizes();
	echo '<td class=number>'._('Font Size').' = '.'</td><td><select name="'.$key['id'].'FontSize">';
	foreach ($FontSizes as $FontSize) {
		if ($FontSize==$key->FontSize) {
			echo '<option selected value="'.$FontSize.'">'.$FontSize.'</option>';
		} else {
			echo '<option value="'.$FontSize.'">'.$FontSize.'</option>';
		}
	}
	echo '</select></td>';
	/* Display the X co-ordinate (mm from the left hand side of page) */
	echo '<td class=number>'._('x').' = '.'</td><td><input type="text" class="number" name="'.$key['id'].'x" size=4 maxlength=4 value="'.$key->x.'"></td>';
	/* Display the Y co-ordinate (mm from the top of the page) */
	echo '<td class=number>'._('y').' = '.'</td><td><input type="text" class="number" name="'.$key['id'].'y" size=4 maxlength=4 value="'.$key->y.'"></td>';
}
function MultiTextLine($key) {
	/* Displays a table row containing the attributes for a
	 * line of text of type `MultiLineText` and the values
	 * given in the object $key
	 */
	$FontSizes=FontSizes();
	echo '<td class=number>'._('Font Size').' = '.'</td><td><select name="'.$key['id'].'FontSize">';
	foreach ($FontSizes as $FontSize) {
		if ($FontSize==$key->FontSize) {
			echo '<option selected value="'.$FontSize.'">'.$FontSize.'</option>';
		} else {
			echo '<option value="'.$FontSize.'">'.$FontSize.'</option>';
		}
	}
	echo '</select></td>';
	/* Display the length of the field in mm */
	echo '<td class=number>'._('Length').' = '.'</td><td><input type="text" class="number" name="'.$key['id'].'Length" size=4 maxlength=4 value="'.$key->Length.'"></td></tr><tr>';
	/* Display the X co-ordinate (mm from the left hand side of page) */
	echo '<td class=number>'._('x').' = '.'</td><td><input type="text" class="number" name="'.$key['id'].'x" size=4 maxlength=4 value="'.$key->x.'"></td>';
	/* Display the Y co-ordinate (mm from the top of the page) */
	echo '<td class=number>'._('y').' = '.'</td><td><input type="text" class="number" name="'.$key['id'].'y" size=4 maxlength=4 value="'.$key->y.'"></td>';
}
function DataTextLine($key) {
	/* Displays a table row containing the attributes for a
	 * line of text of type `DataText` and the values
	 * given in the object $key
	 */
	$FontSizes=FontSizes();
	echo '<td class=number>'._('Font Size').' = '.'</td><td><select name="'.$key['id'].'FontSize">';
	foreach ($FontSizes as $FontSize) {
		if ($FontSize==$key->FontSize) {
			echo '<option selected value="'.$FontSize.'">'.$FontSize.'</option>';
		} else {
			echo '<option value="'.$FontSize.'">'.$FontSize.'</option>';
		}
	}
	echo '</select></td>';
	/* Display the length of the field in mm */
	echo '<td class=number>'._('Length').' = '.'</td><td><input type="text" class="number" name="'.$key['id'].'Length" size=4 maxlength=4 value="'.$key->Length.'"></td>';
	/* Display the X co-ordinate (mm from the left hand side of page) */
	echo '<td class=number>'._('x').' = '.'</td><td><input type="text" class="number" name="'.$key['id'].'x" size=4 maxlength=4 value="'.$key->x.'"></td>';
}
/* If the user has chosen to either preview the form, or
 * save it then we first have to get the POST values into a
 * simplexml object and then save the file as either a
 * temporary file, or into the main code
 */
if (isset($_POST['preview']) or isset($_POST['save'])) {
	/*First create a simple xml object from the main file */
	$FormDesign = simplexml_load_file($PathPrefix.'companies/'.$_SESSION['DatabaseName'].'/FormDesigns/'.$_POST['FormName']);
	$FormDesign['name']=$_POST['formname'];
	$FormDesign->PaperSize=$_POST['PaperSize'];
	$FormDesign->LineHeight=$_POST['LineHeight'];
	/*Iterate through the object filling in the values from
	 * the POST variables */
	foreach ($FormDesign as $key) {
		foreach ($key as $subkey=>$value) {
			if ($key['type']=='ElementArray') {
				foreach ($value as $subsubkey=>$subvalue) {
					$value->$subsubkey = $_POST[$value['id'].$subsubkey];
				}
			} else {
				$key->$subkey = $_POST[$key['id'].$subkey];
			}
		}
	}
	/* If we are just previewing the form then
	 * save it to the temporary directory and call the
	 * PDF creating script */
	if (isset($_POST['preview'])) {
		$FormDesign->asXML(sys_get_temp_dir().'/'.$_POST['FormName']);
		echo '<meta http-equiv="Refresh" content="0; url=' . $rootpath . '/PO_PDFPurchOrder.php?' . SID .'OrderNo=Preview">';
	} else {
	/* otherwise check that the web server has write premissions on the companies
	 * directory and save the xml file to the correct directory */
		if (is_writable($PathPrefix.'companies/'.$_SESSION['DatabaseName'].'/FormDesigns/'.$_POST['FormName'])) {
			$FormDesign->asXML($PathPrefix.'companies/'.$_SESSION['DatabaseName'].'/FormDesigns/'.$_POST['FormName']);
		} else {
			prnMsg( _('The web server does not have write permissions on the file ').'<br />'.$PathPrefix.'companies/'.$_SESSION['DatabaseName'].'/FormDesigns/'.$_POST['FormName'].
				'<br />'._('Your changes cannot be saved').'<br />'._('See your system administrator to correct this problem'), 'error');
		}
	}
}
/* If no form has been selected to edit, then offer a
 * drop down list of possible forms */
if (empty($_POST['FormName'])) {
	echo '<p class="page_title_text"><img src="'.$rootpath.'/css/'.$theme.'/images/maintenance.png" title="' . _('Search') . '" alt="">' . ' ' . $title.'<br>';
	echo '<form method="post" id="ChooseForm" action="' . $_SERVER['PHP_SELF'] . '?' . SID . '">';
	echo '<table><tr>';
	echo '<td>'. _('Select the form to edit') .'</td><td><select name="FormName">';
	/* Iterate throght the appropriate companies FormDesigns/ directory
	 * and extract the form name from each of the xml files found */
	if ($handle = opendir($PathPrefix.'companies/'.$_SESSION['DatabaseName'].'/FormDesigns/')) {
		while (false !== ($file = readdir($handle))) {
			if ($file[0]!='.') {
				$FormDesign = simplexml_load_file($PathPrefix.'companies/'.$_SESSION['DatabaseName'].'/FormDesigns/'.$file);
				echo '<option value="'.$file.'">'.$FormDesign['name'].'</option>';
			}
		}
		closedir($handle);
	}
	echo '</select></td></tr></table>';
	echo '<br /><div class="centre"><input tabindex="6" type="submit" name="submit" value="' . _('Edit Form Layout') . '" /></div>';
	echo '</form>';
	include('includes/footer.inc');
	exit;
} // End of if (empty($_POST['FormName']))
/* If we are not previewing the form then load up the simplexml
 * object from the main xml file */
if (empty($_POST['preview'])) {
	$FormDesign = simplexml_load_file($PathPrefix.'companies/'.$_SESSION['DatabaseName'].'/FormDesigns/'.$_POST['FormName']);
}
echo '<p class="page_title_text"><img src="'.$rootpath.'/css/'.$theme.'/images/reports.png" title="' . _('Form Design') . '" alt="">' . ' ' . _('Form Design').'<br />'. $FormDesign['name'] . '';
echo '<div class="page_help_text">' . _('Enter the changes that you want in the form layout below.') .'<br /> '. _('All measurements are in millimetres') . '.</div><br>';
$Papers=array('A4_Landscape', 'A4_Portrait', 'A3_Lanscape', 'A3_Portrait'); // Possible paper sizes/orientations
echo '<form method="post" id="Form" action="' . $_SERVER['PHP_SELF'] . '?' . SID . '">';
echo '<input name=FormName type=hidden value="'.$_POST['FormName'].'">';
echo '<table width=85% border=1>'; //Start of outer table
echo '<tr><th width=33%>'._('Form Name').'<input type="text" name="formname" value="'.$FormDesign['name'].'"></th>';
/* Select the paper size/orientation */
echo '<th width=33%>'._('Paper Size').'<select name="PaperSize">';
foreach ($Papers as $Paper) {
	if ($Paper==$FormDesign->PaperSize) {
		echo '<option selected value="'.$Paper.'">'.$Paper.'</option>';
	} else {
		echo '<option value="'.$Paper.'">'.$Paper.'</option>';
	}
}
echo '</select></th>';
/* and the standard line height for the form */
echo '<th width=33%>'._('Line Height').'<input type="text" class="number" name="LineHeight" size=3 maxlength=3 value="'.$FormDesign->LineHeight.'"></th></tr><tr>';
$counter=1; // Count how many sub tables are in the row
foreach ($FormDesign as $key) {
	switch ($key['type']) {
		case 'image':
			echo '<td colspan=1 valign=top><table width=100% border=1><tr><th colspan=8>'.$key['name'].'</th></tr>';
			echo '<td class=number>'._('x').' = '.'</td><td><input type="text" class="number" name="'.$key['id'].'x" size=4 maxlength=4 value="'.$key->x.'"></td>';
			echo '<td class=number>'._('y').' = '.'</td><td><input type="text" class="number" name="'.$key['id'].'y" size=4 maxlength=4 value="'.$key->y.'"></td>';
			echo '<td class=number>'._('Width').' = '.'</td><td><input type="text" class="number" name="'.$key['id'].'width" size=4 maxlength=4 value="'.$key->width.'"></td>';
			echo '<td class=number>'._('Height').' = '.'</td><td><input type="text" class="number" name="'.$key['id'].'height" size=4 maxlength=4 value="'.$key->height.'"></td>';
			echo '</table></td>';
			$counter=$counter+1;
			break;
		case 'SimpleText':
			echo '<td colspan=1 valign=top><table width=100% border=1><tr><th colspan=6>'.$key['name'].'</th></tr>';
			SimpleTextLine($key);
			echo '</table></td>';
			$counter=$counter+1;
			break;
		case 'MultiLineText':
			echo '<td colspan=1 valign=top><table width=100% border=1><tr><th colspan=4>'.$key['name'].'</th></tr>';
			MultiTextLine($key);
			echo '</table></td>';
			$counter=$counter+1;
			break;
		case 'ElementArray':
			echo '<td colspan=1 valign=top><table width=100% border=1><tr><th colspan=7>'.$key['name'].'</th></tr>';
			foreach ($key as $subkey) {
				if ($subkey['type']=='SimpleText') {
					echo '<tr>';
					echo '<td>'.$subkey['name'].'</td>';
					SimpleTextLine($subkey);
					echo '</tr>';
				} else {
					echo '<tr>';
					if ($subkey['type']=='DataText') {
						echo '<td>'.$subkey['name'].'</td>';
						DataTextLine($subkey);
					} elseif ($subkey['type']=='StartLine') {
						echo '<td colspan=3>'.$subkey['name'].' = '.'</td><td><input type="text" class="number" name="StartLine" size=4 maxlength=4 value="'.$key->y.'"></td>';
					}
					echo '</tr>';
				}
			}
			echo '</table></td>';
			$counter=$counter+1;
			break;
		case 'CurvedRectangle':
			echo '<td colspan=1 valign=top><table width=100% border=1><tr><th colspan=6>'.$key['name'].'</th></tr>';
			echo '<td class=number>'._('x').' = '.'</td><td><input type="text" class="number" name="'.$key['id'].'x" size=4 maxlength=4 value="'.$key->x.'"></td>';
			echo '<td class=number>'._('y').' = '.'</td><td><input type="text" class="number" name="'.$key['id'].'y" size=4 maxlength=4 value="'.$key->y.'"></td>';
			echo '<td class=number>'._('Width').' = '.'</td><td><input type="text" class="number" name="'.$key['id'].'width" size=4 maxlength=4 value="'.$key->width.'"></td></tr><tr>';
			echo '<td class=number>'._('Height').' = '.'</td><td><input type="text" class="number" name="'.$key['id'].'height" size=4 maxlength=4 value="'.$key->height.'"></td>';
			echo '<td class=number>'._('Radius').' = '.'</td><td><input type="text" class="number" name="'.$key['id'].'radius" size=3 maxlength=3 value="'.$key->radius.'"></td>';
			echo '</table></td>';
			$counter=$counter+1;
			break;
		case 'Rectangle':
			echo '<td colspan=1 valign=top><table width=100% border=1><tr><th colspan=6>'.$key['name'].'</th></tr>';
			echo '<td class=number>'._('x').' = '.'</td><td><input type="text" class="number" name="'.$key['id'].'x" size=4 maxlength=4 value="'.$key->x.'"></td>';
			echo '<td class=number>'._('y').' = '.'</td><td><input type="text" class="number" name="'.$key['id'].'y" size=4 maxlength=4 value="'.$key->y.'"></td></tr><tr>';
			echo '<td class=number>'._('Width').' = '.'</td><td><input type="text" class="number" name="'.$key['id'].'width" size=4 maxlength=4 value="'.$key->width.'"></td>';
			echo '<td class=number>'._('Height').' = '.'</td><td><input type="text" class="number" name="'.$key['id'].'height" size=4 maxlength=4 value="'.$key->height.'"></td>';
			echo '</table></td>';
			$counter=$counter+1;
			break;
	}
	if ($counter==4) { // If the row is full start a new one
		$counter=1;
		echo '</tr><tr>';
	}
}
echo '</tr></table>'; //End of outer table
echo '<br /><div class="centre"><input tabindex="6" type="submit" name="preview" value="' . _('Preview the Form Layout') . '" /></div>';
echo '<br /><div class="centre"><input tabindex="6" type="submit" name="save" value="' . _('Save the Form Layout') . '" /></div>';
echo '</form>';
include('includes/footer.inc');
?>
<?php
/* $Id$ */

/*	Form Designer notes:
	- All measurements are in PostScript points (72 points = 25,4 mm).
	- All coordinates are measured from the lower left corner of the sheet to 
	  the the top left corner of the field.*/

/* BEGIN: Start common code division. */
include('includes/session.inc');
$Title = _('Form Designer');
/* $ViewTopic = 'to_add_topic'; // This is to do.*/
/* $BookMark = 'FormDesigner'; // This is to do.*/
include('includes/header.inc');
/* END: Start common code division. */

/* BEGIN: Functions division. */
// Function to select a text alignment.
function SelectAlignment($keyName, $keyValue) {
	$Alignments = array(); // Possible alignments
	$Alignments['left']['Caption'] = _('Left');
	$Alignments['left']['Title'] = _('Text lines are rendered flush left');
	$Alignments['centre']['Caption'] = _('Centre');
	$Alignments['centre']['Title'] = _('Text lines are centred');
	$Alignments['right']['Caption'] = _('Right');
	$Alignments['right']['Title'] = _('Text lines are rendered flush right');
	$Alignments['full']['Caption'] = _('Justify');
	$Alignments['full']['Title'] = _('Text lines are justified to both margins');
	echo '<select name="' . $keyName . 'Alignment">';
	foreach ($Alignments as $AlignmentValue => $AlignmentOption) {
		echo '<option';
		if ($AlignmentValue==$keyValue) {echo ' selected="selected"';}
		echo ' value="'.$AlignmentValue.'" title="' . $AlignmentOption['Title'] . '">' . $AlignmentOption['Caption'] . '</option>';
	}
	echo '</select>';
}
// Function to select a font size.
function SelectFontSize($keyName, $keyValue) {
	$FontSizes = array(6, 7, 8, 9, 10, 11, 12, 13, 14, 15, 16, 18, 20, 22, 24, 26, 28, 32, 36); // Possible font sizes
	echo '<select name="' . $keyName . 'FontSize">';
	foreach ($FontSizes as $FontSize) {
		echo '<option';
		if ($FontSize==$keyValue) {echo ' selected="selected"';}
		echo ' value="'.$FontSize.'">' . $FontSize . '</option>';
	}
	echo '</select>';
}
// Function to display a table row for the attributes of a single-line text.
function SimpleTextLine($key) {
/*	Displays a table row containing the attributes for a line of text of type 
	'SimpleText' and the values given in the object $key. */
	/* Displays the text font size in PostScript points (72 points = 25,4 mm). */
	echo '<td class="number">' . _('Font Size'). ' = </td><td>';
	SelectFontSize($key['id'], $key->FontSize);
	echo '</td>';
	/* Display the X co-ordinate in PostScript points (72 points = 25,4 mm) from the left hand side of page. */
	echo '<td class="number">' . _('x').' = ' . '</td><td><input type="text" class="number" name="'.$key['id'].'x" size="4" maxlength="4" value="'.$key->x.'" /></td>';
	/* Display the Y co-ordinate in PostScript points (72 points = 25,4 mm) from the bottom of the page. */
	echo '<td class="number">' . _('y').' = ' . '</td><td><input type="text" class="number" name="'.$key['id'].'y" size="4" maxlength="4" value="'.$key->y.'" /></td>';
}
// Function to display a table row for the attributes of a multiline text.
function MultiTextLine($key) {
/*	Displays a table row containing the attributes for a line of text of type 
	'MultiLineText' and the values given in the object $key. */
	// Displays the text font size in PostScript points (72 points = 25,4 mm).
	echo '<td class="number">' . _('Font Size'). ' = </td><td>';
	SelectFontSize($key['id'], $key->FontSize);
	echo '</td>';
	/* Display the width of the field in PostScript points (72 points = 25,4 mm).*/
	echo '<td class="number">' . _('Width').' = ' . '</td><td><input type="text" class="number" name="'.$key['id'].'Length" size="4" maxlength="4" value="'.$key->Length.'" /></td></tr><tr>';
	/* Display the X co-ordinate in PostScript points (72 points = 25,4 mm) from the left hand side of page. */
	echo '<td class="number">' . _('x').' = ' . '</td><td><input type="text" class="number" name="'.$key['id'].'x" size="4" maxlength="4" value="'.$key->x.'" /></td>';
	/* Display the Y co-ordinate in PostScript points (72 points = 25,4 mm) from the bottom of the page. */
	echo '<td class="number">' . _('y').' = ' . '</td><td><input type="text" class="number" name="'.$key['id'].'y" size="4" maxlength="4" value="'.$key->y.'" /></td>';
}
function DataTextLine($key) {
	/* Displays a table row containing the attributes for a
	 * line of text of type `DataText` and the values
	 * given in the object $key
	 */
	// Displays the text font size in PostScript points (72 points = 25,4 mm).
	echo '<td class="number">' . _('Font Size'). ' = </td><td>';
	SelectFontSize($key['id'], $key->FontSize);
	echo '</td>';
	/* Display the width of the field in PostScript points (72 points = 25,4 mm).*/
	echo '<td class="number">' . _('Width').' = ' . '</td><td><input type="text" class="number" name="'.$key['id'].'Length" size="4" maxlength="4" value="'.$key->Length.'" /></td>';
	/* Display the X co-ordinate in PostScript points (72 points = 25,4 mm) from the left hand side of page. */
	echo '<td class="number">' . _('x').' = ' . '</td><td><input type="text" class="number" name="'.$key['id'].'x" size="4" maxlength="4" value="'.$key->x.'" /></td>';
}
/* END: Functions division. */

/* BEGIN: Procedure division. */
/* If the user has chosen to either preview the form, or
 * save it then we first have to get the POST values into a
 * simplexml object and then save the file as either a
 * temporary file, or into the main code
 */
if (isset($_POST['preview']) or isset($_POST['save'])) {
	/*First create a simple xml object from the main file */
	$FormDesign = simplexml_load_file($PathPrefix.'companies/'.$_SESSION['DatabaseName'].'/FormDesigns/'.$_POST['FormName']);
	$FormDesign['name']=$_POST['formname'];
	if (mb_substr($_POST['PaperSize'],-8)=='Portrait') {
		$_POST['PaperSize']=mb_substr($_POST['PaperSize'],0,mb_strlen($_POST['PaperSize'])-9);
	}
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
		switch ($_POST['FormName']) {
			case 'PurchaseOrder.xml':
				echo '<meta http-equiv="Refresh" content="0; url=' . $RootPath . '/PO_PDFPurchOrder.php?' . SID .'OrderNo=Preview">';
				break;
			case 'GoodsReceived.xml':
				echo '<meta http-equiv="Refresh" content="0; url=' . $RootPath . '/PDFGrn.php?' . SID .'GRNNo=Preview&PONo=1">';
				break;
			case 'PickingList.xml':
				echo '<meta http-equiv="Refresh" content="0; url=' . $RootPath . '/PDFPickingList.php?' . SID .'TransNo=Preview">';
				break;
		}
	} else {
	/* otherwise check that the web server has write premissions on the companies
	 * directory and save the xml file to the correct directory */
		if (is_writable($PathPrefix.'companies/'.$_SESSION['DatabaseName'].'/FormDesigns/'.$_POST['FormName'])) {
			$FormDesign->asXML($PathPrefix.'companies/'.$_SESSION['DatabaseName'].'/FormDesigns/'.$_POST['FormName']);
		} else {
			prnMsg( _('The web server does not have write permissions on the file ') . '<br />' . $PathPrefix.'companies/'.$_SESSION['DatabaseName'].'/FormDesigns/'.$_POST['FormName'].
				'<br />' . _('Your changes cannot be saved') . '<br />' . _('See your system administrator to correct this problem'), 'error');
		}
	}
}
/* If no form has been selected to edit, then offer a
 * drop down list of possible forms */
if (empty($_POST['FormName'])) {
	echo '<p class="page_title_text"><img src="'.$RootPath.'/css/'.$Theme.'/images/maintenance.png" title="' . _('Search') . '" alt="" />' . ' ' . $Title . '</p><br />';
	echo '<form method="post" id="ChooseForm" action="' . htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') . '?' . SID . '">';
    echo '<div>';
	echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';
	echo '<table><tr>';
	echo '<td>' .  _('Select the form to edit')  . '</td><td><select name="FormName">';
	/* Iterate throght the appropriate companies FormDesigns/ directory
	 * and extract the form name from each of the xml files found */
	if ($handle = opendir($PathPrefix.'companies/'.$_SESSION['DatabaseName'].'/FormDesigns/')) {
		while (false !== ($file = readdir($handle))) {
			if ($file[0]!='.') {
				$FormDesign = simplexml_load_file($PathPrefix.'companies/'.$_SESSION['DatabaseName'].'/FormDesigns/'.$file);
				echo '<option value="'.$file.'">' . _($FormDesign['name']) . '</option>';
			}
		}
		closedir($handle);
	}
	echo '</select></td></tr></table>';
	echo '<br /><div class="centre"><input tabindex="6" type="submit" name="submit" value="' . _('Edit Form Layout') . '" /></div>';
    echo '</div>';
	echo '</form>';
	include('includes/footer.inc');
	exit;
} // End of if (empty($_POST['FormName']))
/* If we are not previewing the form then load up the simplexml
 * object from the main xml file */
if (empty($_POST['preview'])) {
	$FormDesign = simplexml_load_file($PathPrefix.'companies/'.$_SESSION['DatabaseName'].'/FormDesigns/'.$_POST['FormName']);
}
echo '<p class="page_title_text"><img src="'.$RootPath.'/css/'.$Theme.'/images/reports.png" title="' . _('Form Design') . '" alt="" />' . ' ' . _('Form Design') . '<br />' .  $FormDesign['name'] . '</p>';
echo '<div class="page_help_text">' . 
	_('Enter the changes that you want in the form layout below.')  . '<br /> '. 
	_('All measurements are in PostScript points (72 points = 25,4 mm).')  . '<br /> '. 
	_('All coordinates are measured from the lower left corner of the sheet to the the top left corner of the field.') . '</div><br />';

$Papers=array('A4_Landscape', 'A4_Portrait', 'A5_Landscape', 'A5_Portrait', 'A3_Landscape', 'A3_Portrait', 'Letter_Portrait', 'Letter_Landscape', 'Legal_Portrait', 'Legal_Landscape'); // Possible paper sizes/orientations
echo '<form method="post" id="Form" action="' . htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') . '?' . SID . '">';
echo '<div>';
echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';
echo '<input name="FormName" type="hidden" value="'.$_POST['FormName'].'" />';
echo '<table width="95%" border="1">'; //Start of outer table
echo '<tr><th style="width:33%">' . _('Form Name') . '<input type="text" name="formname" value="'.$FormDesign['name'].'" /></th>';
/* Select the paper size/orientation */
echo '<th style="width:33%">' . _('Paper Size') . '<select name="PaperSize">';
foreach ($Papers as $Paper) {
	if (mb_substr($Paper,-8)=='Portrait') {
		$PaperValue=mb_substr($Paper,0,mb_strlen($Paper)-9);
	} else {
		$PaperValue=$Paper;
	}
	if ($PaperValue==$FormDesign->PaperSize) {
		echo '<option selected="selected" value="'.$PaperValue.'">' . $Paper . '</option>';
	} else {
		echo '<option value="'.$PaperValue.'">' . $Paper . '</option>';
	}
}
echo '</select></th>';
/* and the standard line height for the form */
echo '<th style="width:33%">' . _('Line Height') . '<input type="text" class="number" name="LineHeight" size="3" maxlength="3" value="'.$FormDesign->LineHeight.'" /></th></tr><tr>';
$counter=1; // Count how many sub tables are in the row
foreach ($FormDesign as $key) {
	switch ($key['type']) {
		case 'image':
			echo '<td colspan="1" valign="top"><table width="100%" border="1"><tr><th colspan="8">' . _($key['name']) . '</th></tr>';
            echo '<tr>';
			echo '<td class="number">' . _('x').' = ' . '</td><td><input type="text" class="number" name="'.$key['id'].'x" size="4" maxlength="4" value="'.$key->x.'" /></td>';
			echo '<td class="number">' . _('y').' = ' . '</td><td><input type="text" class="number" name="'.$key['id'].'y" size="4" maxlength="4" value="'.$key->y.'" /></td>';
			echo '<td class="number">' . _('Width').' = ' . '</td><td><input type="text" class="number" name="'.$key['id'].'width" size="4" maxlength="4" value="'.$key->width.'" /></td>';
			echo '<td class="number">' . _('Height').' = ' . '</td><td><input type="text" class="number" name="'.$key['id'].'height" size="4" maxlength="4" value="'.$key->height.'" /></td>';
            echo '</tr>';
			echo '</table></td>';
			$counter=$counter+1;
			break;
		case 'SimpleText':
			echo '<td colspan="1" valign="top"><table width="100%" border="1"><tr><th colspan="6">' . _($key['name']) . '</th></tr>' . "\n";
            echo '<tr>';
			SimpleTextLine($key);
            echo '</tr>';
			echo '</table></td>';
			$counter=$counter+1;
			break;
		case 'MultiLineText':
			echo '<td colspan="1" valign="top"><table width="100%" border="1"><tr><th colspan="4">' . _($key['name']) . '</th></tr>' . "\n";
            echo '<tr>';
			MultiTextLine($key);
            echo '</tr>';
			echo '</table></td>';
			$counter=$counter+1;
			break;
		case 'ElementArray':
			echo '<td colspan="1" valign="top"><table width="100%" border="1"><tr><th colspan="7">' . _($key['name']) . '</th></tr>' . "\n";
			foreach ($key as $subkey) {
				echo '<tr>';
				if ($subkey['type']=='SimpleText') {
					echo '<td>' . _($subkey['name']) . '</td>';
					SimpleTextLine($subkey);
				} elseif ($subkey['type']=='MultiLineText') {
					echo '<td>' . _($subkey['name']) . '</td>';
					MultiTextLine($subkey);
				} elseif ($subkey['type']=='DataText') {
					echo '<td>' . _($subkey['name']) . '</td>';
					DataTextLine($subkey);
				} elseif ($subkey['type']=='StartLine') {
					echo '<td colspan="3">' . _($subkey['name']) . ' = ' . '</td>';
					echo '<td><input type="text" class="number" name="StartLine" size="4" maxlength="4" value="'.$key->y.'" /></td>';
				}
				echo '</tr>';
			}
			echo '</table></td>';
			$counter=$counter+1;
			break;
		case 'CurvedRectangle':
			echo '<td colspan="1" valign="top"><table width="100%" border="1"><tr><th colspan="6">' . _($key['name']) . '</th></tr>';
            echo '<tr>';
			echo '<td class="number">' . _('x').' = ' . '</td><td><input type="text" class="number" name="'.$key['id'].'x" size="4" maxlength="4" value="'.$key->x.'" /></td>';
			echo '<td class="number">' . _('y').' = ' . '</td><td><input type="text" class="number" name="'.$key['id'].'y" size="4" maxlength="4" value="'.$key->y.'" /></td>';
			echo '<td class="number">' . _('Width').' = ' . '</td><td><input type="text" class="number" name="'.$key['id'].'width" size="4" maxlength="4" value="'.$key->width.'" /></td></tr><tr>';
			echo '<td class="number">' . _('Height').' = ' . '</td><td><input type="text" class="number" name="'.$key['id'].'height" size="4" maxlength="4" value="'.$key->height.'" /></td>';
			echo '<td class="number">' . _('Radius').' = ' . '</td><td><input type="text" class="number" name="'.$key['id'].'radius" size="3" maxlength="3" value="'.$key->radius.'" /></td>';
            echo '</tr>';
			echo '</table></td>';
			$counter=$counter+1;
			break;
		case 'Rectangle':
			echo '<td colspan="1" valign="top"><table width="100%" border="1"><tr><th colspan="6">' . _($key['name']) . '</th></tr>';
            echo '<tr>';
			echo '<td class="number">' . _('x').' = ' . '</td><td><input type="text" class="number" name="'.$key['id'].'x" size="4" maxlength="4" value="'.$key->x.'" /></td>';
			echo '<td class="number">' . _('y').' = ' . '</td><td><input type="text" class="number" name="'.$key['id'].'y" size="4" maxlength="4" value="'.$key->y.'" /></td></tr><tr>';
			echo '<td class="number">' . _('Width').' = ' . '</td><td><input type="text" class="number" name="'.$key['id'].'width" size="4" maxlength="4" value="'.$key->width.'" /></td>';
			echo '<td class="number">' . _('Height').' = ' . '</td><td><input type="text" class="number" name="'.$key['id'].'height" size="4" maxlength="4" value="'.$key->height.'" /></td>';
            echo '</tr>';
			echo '</table></td>';
			$counter=$counter+1;
			break;
		case 'Line':
			echo '<td colspan="1" valign="top"><table width="100%" border="1"><tr><th colspan="6">' . _($key['name']) . '</th></tr>';
            echo '<tr>';
			echo '<td class="number">' . _('Start x co-ordinate').' = ' . '</td><td><input type="text" class="number" name="'.$key['id'].'startx" size="4" maxlength="4" value="'.$key->startx.'" /></td>';
			echo '<td class="number">' . _('Start y co-ordinate').' = ' . '</td><td><input type="text" class="number" name="'.$key['id'].'starty" size="4" maxlength="4" value="'.$key->starty.'" /></td></tr><tr>';
			echo '<td class="number">' . _('End x co-ordinate').' = ' . '</td><td><input type="text" class="number" name="'.$key['id'].'endx" size="4" maxlength="4" value="'.$key->endx.'" /></td>';
			echo '<td class="number">' . _('End y co-ordinate').' = ' . '</td><td><input type="text" class="number" name="'.$key['id'].'endy" size="4" maxlength="4" value="'.$key->endy.'" /></td>';
            echo '</tr>';
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
echo '</div>';
echo '</form>';
/* END: Procedure division. */

/* BEGIN: Final common code division. */
include('includes/footer.inc');
/* END: Final common code division. */
?>

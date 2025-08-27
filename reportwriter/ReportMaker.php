<?php

$DirectoryLevelsDeep = 1;
$PathPrefix = __DIR__ . '/../';

// Fetch necessary include files - Host application specific (webERP)
require($PathPrefix . 'includes/session.php');

require_once($PathPrefix . 'includes/DateFunctions.php');

// TBD The following line needs to be replaced when more translations are available
$ReportLanguage = 'en_US';				// default language file
define('DBReports','reports');			// name of the databse table holding the main report information (ReportID)
define('DBRptFields','reportfields');	// name of the database table holding the report fields
//define('FPDF_FONTPATH','../fonts/');  FPDF path to fonts directory

// Include files for ReportMaker.php
require('languages/'.$ReportLanguage.'/reports.php'); // include translation before defaults.php
require('admin/defaults.php'); // load default values

$usrMsg = array(); // setup array for return messages
if (isset($_GET['reportid'])) { // then entered with report id requested, fix variable to show filter form
	$_POST['todo'] = RPT_BTN_CONT; // fake the code to think the continue button was pressed
	$_POST['ReportID'] = (int) $_GET['reportid']; // convert Report ID to a POST variable
	$GoBackURL = htmlspecialchars_decode($RootPath) . '/index.php'; // set the return path to the index.php page
} elseif (isset($_POST['GoBackURL'])) {
	$GoBackURL = htmlspecialchars_decode($_POST['GoBackURL']); // set the return path to the index.php page because entered from a link
} else {
	$GoBackURL = ''; // unset the return path to default
}
//check to see how script was entered
if (!isset($_GET['action']) OR (!isset($_POST['ReportID']))) {
	// then form entered from somewhere other than itself or contained a bad ID, show start form
	if (isset($_POST['todo']) AND (!isset($_POST['ReportID']))) { // Error - button without report selected
		$usrMsg[] = array('message'=>RPT_NORPT, 'level'=>'error');
	}
	// fetch the existing reports for the selection menus
	$DefReportList = GetReports($Def = true);
	$CustReportList = GetReports($Def = false);
	$Title=RPT_MENU;
	$IncludePage = 'forms/ReportsList.html.php';
} elseif (!isset($_POST['todo'])) { // Now find out if an image button button was pressed and act on it
	for ($i=1; $i<1000; $i++) { // figure out what sequence button was pressed
		if (isset($_POST['up'.$i.'_x']) OR isset($_POST['dn'.$i.'_x'])) { $SeqNum = $i; break; }
	}
	$ReportID = $_POST['ReportID']; // fetch the report id
	if (isset($_POST['up'.$SeqNum.'_x'])) { // the shift up button was pushed, check for not at first sequence
		if ($SeqNum<>1) $success = ChangeSequence($ReportID, $SeqNum, 'fieldlist', 'up');
	} elseif (isset($_POST['dn'.$SeqNum.'_x'])) { // the shift down button was pushed
		$sql = "SELECT seqnum FROM ".DBRptFields."
						WHERE reportid = '".$ReportID."' AND entrytype = 'fieldlist';";
		$Result = DB_query($sql,'','',false,true);
		if ($SeqNum<DB_num_rows($Result)) {
			$success = ChangeSequence($ReportID, $SeqNum, 'fieldlist', 'down');
		}
	}
	// Overrride stored settings with current selected values
	$Prefs = FetchReportDetails($_POST['ReportID']); // fetch the current settings
	if (isset($_POST['FilterForm']) OR isset($_POST['PageForm'])) {
		// then we're here from a filter or page form, also read any updtes from the forms.
		$Prefs = ReadPostData($ReportID, $Prefs);
	}
	// reload information to display form
	$Title=RPT_CRITERIA;
	$IncludePage = 'forms/ReportsFilter.html.php';
} else { // a submit button was pressed, find out which one
	$ReportID = $_POST['ReportID']; // fetch the report id
	$Prefs = FetchReportDetails($ReportID);  //fetch the defaults
	switch ($_POST['todo']) {
		case RPT_BTN_DELRPT: // enter here only from My Report selection, never from default report
			$sql= "DELETE FROM ".DBReports." WHERE id = '".$ReportID."'";
			$Result = DB_query($sql,'','',false,true);
			$sql= "DELETE FROM ".DBRptFields." WHERE reportid = '".$ReportID."'";
			$Result = DB_query($sql,'','',false,true);
			// Recreate drop down list and return to report home (handled in Cancel below)

		case RPT_BTN_CANCEL:
			if ($GoBackURL<>'') { // then the cancel button needs to return to homepage
				header('Location: '.$GoBackURL);
				exit();
			} else { // return to report list page
				$DefReportList = GetReports($Def = true);
				$CustReportList = GetReports($Def = false);
				$Title=RPT_MENU;
				$IncludePage = 'forms/ReportsList.html.php';
			}
			break;

		case RPT_BTN_SAVE:
		case RPT_BTN_REPLACE:
			if ($_POST['todo']=='Replace') $AllowOverwrite = true; else $AllowOverwrite = false;
			$success = SaveNewReport($ReportID, $AllowOverwrite);
			if($success['result']=='success') { // Reload criteria page
				$ReportID = $success['ReportID'];
				$Prefs = FetchReportDetails($ReportID);  //fetch the defaults
				$Title=RPT_CRITERIA;
				$IncludePage = 'forms/ReportsFilter.html.php';
			} else { // an error message was sent so reload save form
				if($success['default']==false) $ShowReplace = true; else $ShowReplace = false;
				$Prefs['reportname'] = $_POST['ReportName'];
				$Title=RPT_PAGESAVE;
				$IncludePage = 'forms/ReportsSave.html.php';
			}
			$usrMsg[] = array('message'=>$success['message'], 'level'=>$success['result']);
			break;

		case RPT_BTN_CPYRPT:
			$Prefs = ReadPostData($ReportID, $Prefs); // fetch the current saved values
			if ($Prefs['defaultreport']) $Prefs['reportname'] = ''; // clear name if default report
			$ShowReplace = false;
			$Title=RPT_PAGESAVE;
			$IncludePage = 'forms/ReportsSave.html.php';
			break;

		case RPT_BTN_UPDATE:
		case RPT_BTN_CRIT:
		case RPT_BTN_PGSETUP:
			// Overrride stored settings with selected values
			$Prefs = ReadPostData($ReportID, $Prefs); // reads and updates the database
			if ($_POST['todo']==RPT_BTN_PGSETUP) { // return to the page setup screen
				$Title=RPT_PAGESETUP;
				$IncludePage = 'forms/ReportsPageUpdate.html.php';
			} else { // return to the criterai screen
				$Title=RPT_CRITERIA;
				$IncludePage = 'forms/ReportsFilter.html.php';
			}
			break;

		case RPT_BTN_EXPCSV:
		case RPT_BTN_EXPPDF:
			$Prefs = ReadPostData($ReportID, $Prefs);
			// include the necessary files to build report
			require(__DIR__ . '/includes/WriteReport.php');
			$ReportData = '';
			$success = BuildSQL($Prefs);
			if ($success['level']=='success') { // Generate the output data array
				$sql = $success['data'];
				$Prefs['filterdesc'] = $success['filterdesc']; // fetch the filter message
				$ReportData = BuildDataArray($ReportID, $sql, $Prefs);
				// Check for the report returning with data
				if(!$ReportData) $usrMsg[] = array('message'=>RPT_NODATA.' The failing sql='.$sql,'level'=>'warn');
			} else { // Houston, we have a problem, sql build failed
				$usrMsg[] = array('message'=>$success['message'], 'level'=>$success['level']);
			}
			if ($usrMsg) { // then we have a message to display and no report to show
				if ($_POST['FilterForm']) { // return to the filter form
					$Title=RPT_CRITERIA;
					$IncludePage = 'forms/ReportsFilter.html.php';
				} else { // return to the page setup form
					$Title=RPT_PAGESETUP;
					$IncludePage = 'forms/ReportsPageUpdate.html.php';
				}
			} else { // send the report (Both of these function exit the script (the point of no return)
				if ($_POST['todo']==RPT_BTN_EXPCSV) {
					GenerateCSVFile($ReportData, $Prefs);
				}
				if ($_POST['todo']==RPT_BTN_EXPPDF) {
					GeneratePDFFile($ReportData, $Prefs);
				}
			}
			break;

		case RPT_BTN_CONT:
		default:
			$Title=RPT_CRITERIA;
			$IncludePage = 'forms/ReportsFilter.html.php';
	} // end switch 'todo'
} // end if (!isset($_POST['todo']))

include($PathPrefix . 'includes/header.php');
if ($usrMsg) foreach ($usrMsg as $temp) prnmsg($temp['message'],$temp['level']);
include($IncludePage);
include( $PathPrefix . 'includes/footer.php');
// End main body

// Begin functions
function GetReports($Default) {
	$DropDownString = '';
	if ($Default) {
		$Def=1;
	} else {
		$Def=0;
	}

	$sql= "SELECT id,
				reportname,
				groupname
		FROM ". DBReports."
		WHERE defaultreport='".$Def."'
		ORDER BY groupname, reportname";

	$Result = DB_query($sql,'','',false,true);
	$DefaultReports = array();
	while ($Temp = DB_fetch_array($Result)) {
		$DefaultReports[] = $Temp;
	}
	$DropDownString = build_dropdown_list($DefaultReports);
	return $DropDownString;
}

function build_dropdown_list($arraylist) {
	global $ReportGroups;
	$OptionList = '';
	foreach ($ReportGroups as $key=>$value) {
		$OptionList .= '<optgroup label="' . $value . '" title="' . $key . '">';
		if (isset($arraylist)) {
			foreach ($arraylist as $reportinfo) {
				if ($reportinfo['groupname'] == $key) {
					$OptionList .= '<option value="'.$reportinfo['id'].'">'.$reportinfo['reportname'].'</option>';
				}
			}
		}
		$OptionList .= '</optgroup>';
	}
	return $OptionList;
}

function FetchReportDetails($ReportID) {
	$sql= "SELECT *	FROM ".DBReports." WHERE id = '".$ReportID."'";
	$Result = DB_query($sql,'','',false,true);
	$myrow=DB_fetch_assoc($Result);
	foreach ($myrow as $key=>$value) {
		$Prefs[$key]=$value;
	}
	// Build drop down menus for selectable criteria; need $ReportID
	$Temp = RetrieveFields($ReportID, 'dateselect');
	$Prefs['DateListings'] = $Temp[0]; // only need the first field
	$Temp = RetrieveFields($ReportID, 'trunclong');
	$Prefs['TruncListings'] = $Temp[0]; // only need the first field
	$Prefs['SortListings'] = RetrieveFields($ReportID, 'sortlist');
	$Prefs['GroupListings'] = RetrieveFields($ReportID, 'grouplist');
	$Prefs['CritListings'] = RetrieveFields($ReportID, 'critlist');
	$Prefs['FieldListings'] = RetrieveFields($ReportID, 'fieldlist');
	return $Prefs;
}

function RetrieveFields($ReportID, $EntryType) {
	$FieldListings = array();
	$sql= "SELECT *	FROM ".DBRptFields."
		WHERE reportid = '".$ReportID."' AND entrytype = '".$EntryType."'
		ORDER BY seqnum";
	$Result = DB_query($sql,'','',false,true);
	while ($FieldValues = DB_fetch_assoc($Result)) {
		$FieldListings[] = $FieldValues;
	}
	return $FieldListings;
}

function ChangeSequence($ReportID,
						$SeqNum,
						$EntryType,
						$UpDown) {
	// find the id of the row to move
	$sql = "SELECT id FROM ".DBRptFields."
		WHERE reportid = '".$ReportID."'
		AND entrytype = '".$EntryType."'
		AND seqnum = '".$SeqNum."'";

	$Result = DB_query($sql,'','',false,true);
	$myrow = DB_fetch_row($Result);
	$OrigID = $myrow[0];
	if ($UpDown=='up') {
		$NewSeqNum = $SeqNum-1;
	} else {
		$NewSeqNum = $SeqNum+1;
	}
	// first move affected sequence to seqnum, then seqnum to new position
	$sql = "UPDATE ".DBRptFields." SET seqnum='".$SeqNum."'
		WHERE reportid = '".$ReportID."'
		AND entrytype = '".$EntryType."'
		AND seqnum = '".$NewSeqNum."'";
	$Result = DB_query($sql,'','',false,true);
	$sql = "UPDATE ".DBRptFields." SET seqnum='".$NewSeqNum."' WHERE id = '".$OrigID."'";
	$Result = DB_query($sql,'','',false,true);
	return true;
}

function BuildCriteria($FieldListings) {
	global $CritChoices;
	$SeqNum = $FieldListings['seqnum'];
	$CriteriaString = '<tr><td>'.$FieldListings['displaydesc'].'</td>'; // add the description
	// retrieve the dropdown based on the params field (dropdown type)
	$Params = explode(':',$FieldListings['params']);  // the first value is the criteria type
	$CritBlocks = explode(':',$CritChoices[array_shift($Params)]);
	if (!isset($Params[0])) $Params[0] = '-'; // default to no default if this parameter doesn't exist
	if (!isset($Params[1])) $Params[1] = ''; // default to no entry for default from box
	if (!isset($Params[2])) $Params[2] = ''; // default to no entry for default to box
	switch (array_shift($CritBlocks)) { // determine how many text boxes to build
		default:
		case 0: $EndString = '<td>&nbsp;</td><td>&nbsp;</td>';
			break;
		case 1: $EndString = '<td><input name="fromvalue'.$SeqNum.'" type="text"
				value="'.$Params[1].'" size="21" maxlength="20"></td><td>&nbsp;</td>';
			break;
		case 2: $EndString = '<td><input name="fromvalue'.$SeqNum.'" type="text" value="'.$Params[1].'" size="21" maxlength="20"></td>
				<td><input name="tovalue'.$SeqNum.'" type="text" value="'.$Params[2].'" size="21" maxlength="20"></td>';
	} // end switch array_shift($CritBlocks)
	$CriteriaString .= '<td><select name="defcritsel'.$SeqNum.'">';
	foreach ($CritBlocks as $value) {
		if ($Params[0]==$value) $Selected = ' selected'; else $Selected = '';  // find the default
		$CriteriaString .= '<option value="'.$value.'"'.$Selected.'>'.$value.'</option>';
	}
	$CriteriaString .= '</select></td>';
	$CriteriaString .= $EndString.'</tr>';
	return $CriteriaString;
}

function BuildFieldList($FieldListings) {
	$ColCount = 1;
	$CriteriaString = '';
	foreach ($FieldListings as $FieldValues) {
		$SeqNum = $FieldValues['seqnum'];
		$CriteriaString .= '<tr><td><input name="DataField'.$SeqNum.'" type="hidden" value="'.$SeqNum.'">';
		$CriteriaString .= $FieldValues['displaydesc'].'</td>'; // add the description
		if ($FieldValues['visible']=='1') $Checked = ' checked'; else $Checked = '';
		$CriteriaString .= '<td align="center"><input type="checkbox" name="show'.$SeqNum.'" value="1"'.$Checked.'></td>';
		if ($FieldValues['columnbreak']=='1') $Checked = ' checked'; else $Checked = '';
		$CriteriaString .= '<td align="center"><input type="checkbox" name="break'.$SeqNum.'" value="1"'.$Checked.'></td>';
		if ($FieldValues['visible']=='1') {
			$CriteriaString .= '<td align="center">'.$ColCount.'</td>';
			if ($FieldValues['columnbreak']=='1') $ColCount++;
		} else {
			$CriteriaString .= '<td>&nbsp;</td>';
		}
		$CriteriaString .= '<td align="center"><INPUT type=image name="up'.$SeqNum.'" value="up'.$SeqNum.'" src="images/upicon.png" border="0">
			&nbsp;<INPUT type=image name="dn'.$SeqNum.'" value="dn" src="images/downicon.png" border="0"></td>';
		$CriteriaString .= '</tr>';
	}
	return $CriteriaString;
}

function ReadPostData($ReportID, $Prefs) {
	// check for page setup form entry to look at check boxes and save as new defaults, return
	if (isset($_POST['PageForm'])) {
		$success = SavePrefs($ReportID);
		// values saved, read them back in to update $Prefs array
		$sql= "SELECT *	FROM ".DBReports." WHERE id = '".$ReportID."'";
		$Result = DB_query($sql,'','',false,true);
		$myrow=DB_fetch_assoc($Result);
		foreach ($myrow as $key=>$value) $Prefs[$key]=$value;
		return $Prefs;
	}
	// Since we are not at the page setup form, we read from the filter form, fetch user selections
	$Prefs['DateListings']['params'] = $_POST['DefDate'];
	if ($_POST['DefDate']=='b') { // then it's a range selection, save dates, else discard
		$Prefs['DateListings']['params'] .= ':'.$_POST['DefDateFrom'].':'.$_POST['DefDateTo'];
	}
	$Prefs['defgroup'] = $_POST['DefGroup'];
	$Prefs['defsort'] = $_POST['DefSort'];
	$Prefs['TruncListings']['params'] = $_POST['DefTrunc'];
	if (!$Prefs['defaultreport']) { // Then save the filter settings because it's a custom report and we save them
		$success = SaveFilters($ReportID, 'dateselect', $Prefs['DateListings']['params']);
		$success = SaveFilters($ReportID, 'trunclong', $Prefs['TruncListings']['params']);
		if (isset($Prefs['defgroup'])) $success = SaveDefSettings($ReportID, 'grouplist', $Prefs['defgroup']);
		if (isset($Prefs['defsort'])) $success = SaveDefSettings($ReportID, 'sortlist', $Prefs['defsort']);
	}
	// update Prefs with current user selections
	if (isset($Prefs['defgroup'])) { // First clear all defaults and reset the user's choice
		for ($i=0; $i<count($Prefs['GroupListings']); $i++) $Prefs['GroupListings'][$i]['params']=0;
		if ($Prefs['defgroup']<>0) $Prefs['GroupListings'][$Prefs['defgroup']-1]['params'] = '1';
	}
	if (isset($Prefs['defsort'])) { // First clear all defaults and reset the user's choice
		for ($i=0; $i<count($Prefs['SortListings']); $i++) $Prefs['SortListings'][$i]['params']=0;
		if ($Prefs['defsort']<>0) $Prefs['SortListings'][$Prefs['defsort']-1]['params'] = '1';
	}

	// Criteria Field Selection
	$i=1;
	while (isset($_POST['defcritsel'.$i])) { // then there is at least one criteria
		// Build the criteria default string
		$Prefs['CritListings'][$i-1]['params'] = mb_substr($Prefs['CritListings'][$i-1]['params'],0,1);
		$Prefs['CritListings'][$i-1]['params'] .= ':'.$_POST['defcritsel'.$i];
		$Prefs['CritListings'][$i-1]['params'] .= ':'.$_POST['fromvalue'.$i];
		if ($_POST['tovalue'.$i]==''){ $_POST['tovalue'.$i] = $_POST['fromvalue'.$i]; }
		$Prefs['CritListings'][$i-1]['params'] .= ':'.$_POST['tovalue'.$i];
		if (!$Prefs['defaultreport']) { // save it since it's a custom report
			$sql = "UPDATE ".DBRptFields." SET params='".$Prefs['CritListings'][$i-1]['params']."'
				WHERE reportid ='".$ReportID."' AND entrytype='critlist' AND seqnum='".$i."'";
			$Result = DB_query($sql,'','',false,true);
		}
		$i++;
	}
	// If it's a default report, we're done, return
	if ($Prefs['defaultreport']) return $Prefs;
	// Read in the display field form selections
	$i=1;
	while (isset($_POST['DataField'.$i])) { // read in the field choices
		if ($_POST['show'.$i]=='1') { $Prefs['FieldListings'][$i-1]['visible'] = '1'; }
			else { $Prefs['FieldListings'][$i-1]['visible'] = '0'; }
		if ($_POST['break'.$i]=='1') { $Prefs['FieldListings'][$i-1]['columnbreak'] = '1'; }
			else { $Prefs['FieldListings'][$i-1]['columnbreak'] = '0'; }
		$sql = "UPDATE ".DBRptFields." SET
				visible='".$Prefs['FieldListings'][$i-1]['visible']."',
				columnbreak='".$Prefs['FieldListings'][$i-1]['columnbreak']."'
			WHERE reportid ='".$ReportID."' AND entrytype='fieldlist' AND seqnum='".$i."'";
		$Result = DB_query($sql,'','',false,true);
		$i++;
	}
	return $Prefs;
}

function SaveFilters($ReportID, $EntryType, $Params) {
	$sql = "UPDATE ".DBRptFields." SET params='".$Params."' WHERE reportid ='".$ReportID."' AND entrytype='".$EntryType."'";
	$Result = DB_query($sql,'','',false,true);
	return true;
}

function SaveDefSettings($ReportID, $EntryType, $SeqNum) {
	// This function sets all the params for a given entrytype to 0 and sets just the new default seqnum to 1
	$sql = "UPDATE ".DBRptFields." SET params='0' WHERE reportid='".$ReportID."' AND entrytype='".$EntryType."';";
	$Result = DB_query($sql,'','',false,true);
	$sql = "UPDATE ".DBRptFields." SET params='1'
		WHERE reportid ='".$ReportID."' AND entrytype='".$EntryType."' AND seqnum='".$SeqNum."'";
	$Result = DB_query($sql,'','',false,true);
	return true;
}

function SavePrefs($ReportID) {
	// the checkboxes to false if not checked
	if (!isset($_POST['CoyNameShow'])) $_POST['CoyNameShow'] = '0';
	if (!isset($_POST['Title1Show'])) $_POST['Title1Show'] = '0';
	if (!isset($_POST['Title2Show'])) $_POST['Title2Show'] = '0';
	$sql = "UPDATE ".DBReports." SET
			papersize = '".$_POST['PaperSize']."',
			paperorientation = '".$_POST['PaperOrientation']."',
			margintop = ".$_POST['MarginTop'].",
			marginbottom = ".$_POST['MarginBottom'].",
			marginleft = ".$_POST['MarginLeft'].",
			marginright = ".$_POST['MarginRight'].",
			coynamefont = '".$_POST['CoyNameFont']."',
			coynamefontsize = ".$_POST['CoyNameFontSize'].",
			coynamefontcolor = '".$_POST['CoyNameFontColor']."',
			coynamealign = '".$_POST['CoyNameAlign']."',
			coynameshow = '".$_POST['CoyNameShow']."',
			title1desc = '".addslashes($_POST['Title1Desc'])."',
			title1font = '".$_POST['Title1Font']."',
			title1fontsize = ".$_POST['Title1FontSize'].",
			title1fontcolor = '".$_POST['Title1FontColor']."',
			title1fontalign = '".$_POST['Title1FontAlign']."',
			title1show = '".$_POST['Title1Show']."',
			title2desc = '".addslashes($_POST['Title2Desc'])."',
			title2font = '".$_POST['Title2Font']."',
			title2fontsize = ".$_POST['Title2FontSize'].",
			title2fontcolor = '".$_POST['Title2FontColor']."',
			title2fontalign = '".$_POST['Title2FontAlign']."',
			title2show = '".$_POST['Title2Show']."',
			filterfont = '".$_POST['FilterFont']."',
			filterfontsize = ".$_POST['FilterFontSize'].",
			filterfontcolor = '".$_POST['FilterFontColor']."',
			filterfontalign = '".$_POST['FilterFontAlign']."',
			datafont = '".$_POST['DataFont']."',
			datafontsize = ".$_POST['DataFontSize'].",
			datafontcolor = '".$_POST['DataFontColor']."',
			datafontalign = '".$_POST['DataFontAlign']."',
			totalsfont = '".$_POST['TotalsFont']."',
			totalsfontsize = ".$_POST['TotalsFontSize'].",
			totalsfontcolor = '".$_POST['TotalsFontColor']."',
			totalsfontalign = '".$_POST['TotalsFontAlign']."',
			col1width = ".$_POST['Col1Width'].",
			col2width = ".$_POST['Col2Width'].",
			col3width = ".$_POST['Col3Width'].",
			col4width = ".$_POST['Col4Width'].",
			col5width = ".$_POST['Col5Width'].",
			col6width = ".$_POST['Col6Width'].",
			col7width = ".$_POST['Col7Width'].",
			col8width = ".$_POST['Col8Width'].",
			col9width = ".$_POST['Col9Width'].",
			col10width = ".$_POST['Col10Width'].",
			col11width = ".$_POST['Col11Width'].",
			col12width = ".$_POST['Col12Width'].",
			col13width = ".$_POST['Col13Width'].",
			col14width = ".$_POST['Col14Width'].",
			col15width = ".$_POST['Col15Width'].",
			col16width = ".$_POST['Col16Width'].",
			col17width = ".$_POST['Col17Width'].",
			col18width = ".$_POST['Col18Width'].",
			col19width = ".$_POST['Col19Width'].",
			col20width = ".$_POST['Col20Width'].",
		WHERE id ='".$ReportID."'";
	$Result = DB_query($sql,'','',false,true);
	return true;
}

function SaveNewReport($ReportID, $AllowOverwrite) {
	global $Prefs;
	// input error check reportname, blank duplicate, bad characters, etc.
	// Delete any special characters from ReportName
	if ($_POST['ReportName']=='') { // no report name was entered, error and reload form
		$Rtn['result'] = 'error';
		$Rtn['default'] = false;
		$Rtn['message'] = RPT_NORPT;
		return $Rtn;
	}
	// check for duplicate report name and error or overwrite if allowed
	$sql = "SELECT id, defaultreport FROM ".DBReports." WHERE reportname='".addslashes($_POST['ReportName'])."';";
	$Result = DB_query($sql,'','',false,true);
	if (DB_num_rows($Result)>0) $myrow = DB_fetch_array($Result);
	if (isset($myrow)) { // then we have a duplicate report name do some checking
		if ($myrow['defaultreport']) { // it's a default don't allow overwrite no matter what, return
			$Rtn['result'] = 'warn';
			$Rtn['default'] = true;
			$Rtn['message'] = RPT_SAVEDEF;
			return $Rtn;
		} elseif (!$AllowOverwrite) { // verify user wants to replace, return
			$Rtn['result'] = 'warn';
			$Rtn['default'] = false;
			$Rtn['message'] = RPT_SAVEDUP;
			return $Rtn;
		}
		// check for the same report to update or replace a different report than ReportID
		if ($myrow['id']<>$ReportID) { // erase the report to overwrite and duplicate ReportID
			$sql= "DELETE FROM ".DBReports." WHERE id = '".$myrow['id']."'";
			$Result = DB_query($sql,'','',false,true);
			$sql= "DELETE FROM ".DBRptFields." WHERE reportid = '".$myrow['id']."'";
			$Result = DB_query($sql,'','',false,true);
		} else { // just return because the save as name is the same as the current report name
			$Rtn['message'] = RPT_REPORT.$Prefs['reportname'].RPT_WASSAVED.$_POST['ReportName'];
			$Rtn['ReportID'] = $ReportID;
			$Rtn['result'] = 'success';
			return $Rtn;
		}
	}
	// Input validated perform requested operation
	$OrigID = $ReportID;
	// Set the report id to 0 to prepare to duplicate
	$sql = "UPDATE ".DBReports." SET id=0 WHERE id='".$ReportID."'";
	$Result = DB_query($sql,'','',false,true);
	$sql = "INSERT INTO ".DBReports." SELECT * FROM ".DBReports." WHERE id=0;";
	$Result = DB_query($sql,'','',false,true);
	// Fetch the id entered
	$ReportID = DB_Last_Insert_ID('reports','id');
	// Restore original report ID from 0
	$sql = "UPDATE ".DBReports." SET id='".$OrigID."' WHERE id=0;";
	$Result = DB_query($sql,'','',false,true);
	// Set the report name per the form and make a non-default report
	$sql = "UPDATE ".DBReports." SET reportname='".addslashes($_POST['ReportName'])."', defaultreport='0' WHERE id ='".$ReportID."'";
	$Result = DB_query($sql,'','',false,true);
	// fetch the fields and duplicate
	$sql = "SELECT * FROM ".DBRptFields." WHERE reportid='".$OrigID."'";
	$Result = DB_query($sql,'','',false,true);
	while ($temp = DB_fetch_array($Result)) $field[] = $temp;
	foreach ($field as $row) {
		$sql = "INSERT INTO ".DBRptFields." (reportid, entrytype, seqnum, fieldname,
				displaydesc, visible, columnbreak, params)
			VALUES ('".$ReportID."', '".$row['entrytype']."', '".$row['seqnum']."',
				'".$row['fieldname']."', '".$row['displaydesc']."', '".$row['visible']."',
				'".$row['columnbreak']."', '".$row['params']."');";
		$Result = DB_query($sql,'','',false,true);
	}
	$Rtn['message'] = RPT_REPORT.$Prefs['reportname'].RPT_WASSAVED.$_POST['ReportName'];
	$Rtn['ReportID'] = $ReportID;
	$Rtn['result'] = 'success';
	return $Rtn;
}

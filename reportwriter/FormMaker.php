<?php
/* $Revision: 1.2 $ */

$DirectoryLevelsDeep = 1;
$PathPrefix = '../';
$PageSecurity = 1; // set security level for webERP 

// TBD The followiung line needs to be replace when more translations are available
$ReportLanguage = 'en_US';					// default language file 
define('DBReports','reports');			// name of the databse holding the main report information (ReportID)
define('DBRptFields','reportfields');	// name of the database holding the report fields
define('FPDF_FONTPATH','../fonts/'); // FPDF path to fonts directory
define('DefRptPath','languages/'.$ReportLanguage.'/');	// path to default reports

// Fetch necessary include files - Host application specific (webERP)
require($PathPrefix . 'includes/session.inc');
require_once($PathPrefix . 'includes/DateFunctions.inc');

// Include files for ReportMaker.php
require('languages/'.$ReportLanguage.'/reports.php'); // include translation before defaults.php
require('admin/defaults.php'); // load default values

$usrMsg = ''; // setup array for return messages
$GoBackURL = $rootpath.'/index.php'; // set the return path to the index.php page

if (isset($_GET['id'])) { // then entered with form group requested
	$QueryString = '?'.$_SERVER['QUERY_STRING']; // save the passed parameters
} else { // script was entered generically
	$QueryString = ''; // no passed parameters
}

switch ($_POST['todo']) {
	case RPT_BTN_CANCEL:
		header("Location: ".$GoBackURL);
		exit();

	default: // determine how we entered the script to show correct form list information
		$OutputString = BuildFormList($_GET['id']); // ['id'] will be null for generic entry
		$title=RPT_FORMSELECT;
		$IncludePage = 'forms/FormsList.html';
		break;

	case RPT_BTN_CRIT:
		$ReportID = $_POST['ReportID']; // fetch the report id
		if ($ReportID=='') { // then no report was selected, error
			$usrMsg[] = array('message'=>FRM_NORPT, 'level'=>'error');
			$OutputString = BuildFormList($_GET['id']);
			$title=RPT_FORMSELECT;
			$IncludePage = 'forms/FormsList.html';
		} else {
			$Prefs = FetchReportDetails($ReportID);  //fetch the defaults 
			// Update with passed parameters if so
			// NOTE: The max number of parameters to test is currrently set at the date and 10 form specific.
			if (isset($_GET['cr0'])) $Prefs['DateListings']['params'] = $_GET['cr0'];
			for ($i=1; $i<11; $i++) {
				if (isset($_GET['cr'.$i])) { // then a criteria was passed update the parameter info
					$Prefs['CritListings'][$i-1]['params'] = $Prefs['CritListings'][$i-1]['params'].':'.$_GET['cr'.$i];
				}
			}
			$title=RPT_CRITERIA;
			$IncludePage = 'forms/FormsFilter.html';
		}
		break;

	case RPT_BTN_EXPPDF:
		$ReportID = $_POST['ReportID']; // fetch the report id
		if ($ReportID=='') { // then no report was selected, error
			$usrMsg[] = array('message'=>FRM_NORPT, 'level'=>'error');
			$OutputString = BuildFormList($_GET['id']);
			$title=RPT_FORMSELECT;
			$IncludePage = 'forms/FormsList.html';
			break;
		}
		$Prefs = FetchReportDetails($ReportID);  //fetch the defaults then overwrite with user preferences
		// read from the filter form, fetch user selections for date information
		if (isset($_POST['DefDate'])) { // then we entered from criteria form, get user overrides
			if ($_POST['DefDate']=='b') { // then it's a range selection, save to-from dates, else discard
				$Prefs['DateListings']['params'] = $_POST['DefDate'].':'.$_POST['DefDateFrom'].':'.$_POST['DefDateTo'];
			} else {
				$Prefs['DateListings']['params'] = $_POST['DefDate'];
			}
			// Read in the criteria field selection, if any
			$i=1;
			while (isset($_POST['defcritsel'.$i])) { // then there is at least one criteria
				// Build the criteria default string
				$Prefs['CritListings'][$i-1]['params'] = substr($Prefs['CritListings'][$i-1]['params'],0,1);
				$Prefs['CritListings'][$i-1]['params'] .= ':'.$_POST['defcritsel'.$i];
				$Prefs['CritListings'][$i-1]['params'] .= ':'.$_POST['fromvalue'.$i];
				$Prefs['CritListings'][$i-1]['params'] .= ':'.$_POST['tovalue'.$i];
				$i++;
			}
		} else { // then parameters may have been passed in the URL, load them if necessary
			// NOTE: The max number of parameters to test is currrently set at the date and 10 form specific.
			if (isset($_GET['cr0'])) $Prefs['DateListings']['params'] = $_GET['cr0'];
			for ($i=1; $i<11; $i++) {
				if (isset($_GET['cr'.$i])) { // then a criteria was passed update the parameter info
					$Prefs['CritListings'][$i-1]['params'] = $Prefs['CritListings'][$i-1]['params'].':'.$_GET['cr'.$i];
				}
			}
		} // else use default settings, i.e. no overrides
		// All done with setup, build the form
		require($PathPrefix . 'includes/fpdf.php'); // FPDF class to generate reports
		require('WriteForm.inc');
		// build the pdf pages (this function exits the script if successful; otherwise returns with error)
		$success = BuildPDF($ReportID, $Prefs); // build and output form, should not return from this function
		// if we are here, there's been an error, report it
		$usrMsg[] = array('message'=>$success['message'], 'level'=>$success['level']);
		if (isset($_POST['FormFilter'])) { // then return to the form filter page
			$Prefs = FetchReportDetails($ReportID);  //fetch the defaults 
			// Update with passed parameters if so
			// NOTE: The max number of parameters to test is currrently set at the date and 10 form specific.
			if (isset($_GET['cr0'])) $Prefs['DateListings']['params'] = $_GET['cr0'];
			for ($i=1; $i<11; $i++) {
				if (isset($_GET['cr'.$i])) { // then a criteria was passed update the parameter info
					$Prefs['CritListings'][$i-1]['params'] = $Prefs['CritListings'][$i-1]['params'].':'.$_GET['cr'.$i];
				}
			}
			$title=RPT_CRITERIA;
			$IncludePage = 'forms/FormsFilter.html';
		} else { // return to the form list page
			$OutputString = BuildFormList($_GET['id']);
			$title=RPT_FORMSELECT;
			$IncludePage = 'forms/FormsList.html';
		}
		break;
} // end switch 'todo'

include ($PathPrefix . 'includes/header.inc');
if ($usrMsg) foreach ($usrMsg as $temp) prnmsg($temp['message'],$temp['level']);
include ($IncludePage);
include ( $PathPrefix . 'includes/footer.inc');
// End main body

// Begin functions
function BuildFormList($GroupID) {
	global $db, $ReportGroups, $FormGroups;
	
	$OutputString = '';
	if ($GroupID=='') { // then fetchthe complete form list for all groups
		foreach ($ReportGroups as $key=>$GName) {
			$OutputString .= '<tr bgcolor="#CCCCCC"><td colspan="3" align="center">'.$GName.'</td></tr>';
			$OutputString .= '<tr><td colspan="3" width="250" valign="top">';
			$sql= "SELECT id, groupname, reportname FROM ".DBReports." 
				WHERE defaultreport='1' AND reporttype='frm' 
				ORDER BY groupname, reportname";
			$Result=DB_query($sql,$db,'','',false,true);
			$FormList = '';
			while ($Temp = DB_fetch_array($Result)) $FormList[] = $Temp;
			foreach ($FormGroups as $index=>$value) {
				$Group=explode(':',$index); // break into main group and form group array
				if ($Group[0]==$key) { // then it's a part of the group we're showing
					$WriteOnce = true;
					foreach ($FormList as $Entry) {
						if ($Entry['groupname']==$index) { // then it's part of this listing
							if ($WriteOnce) { $OutputString .= $value.'<br>'; $WriteOnce=false; }
							$OutputString .= '&nbsp;&nbsp;<input type="radio" name="ReportID" value="'.$Entry['id'].'">'.$Entry['reportname'].'<br>';
						}
					}
				}
			}
			$OutputString .= '</td></tr>';
		}
	} else { // fetch the forms specific to a group GroupID
		$sql= "SELECT id, reportname FROM ".DBReports." 
			WHERE defaultreport='1' AND groupname='".$GroupID."' 
			ORDER BY reportname";
		$Result=DB_query($sql,$db,'','',false,true);
		$OutputString .= '<tr><td colspan="3" width="250" valign="top">';
		while ($Forms = DB_fetch_array($Result)) {
			$OutputString .= '<input type="radio" name="ReportID" value="'.$Forms['id'].'">'.$Forms['reportname'].'<br>';
		}
		$OutputString .= '</td></tr>';
	}
	return $OutputString;
}

function FetchReportDetails($ReportID) {
	global $db;
	$sql= "SELECT reportname, reporttype, groupname, papersize, paperorientation,
			margintop, marginbottom, marginleft, marginright,
			table1, table2, table2criteria, table3, table3criteria, table4, table4criteria,
			table5, table5criteria, table6, table6criteria
		FROM ".DBReports." 
		WHERE id = ".$ReportID.";";
	$Result=DB_query($sql,$db,'','',false,true);
	$myrow=DB_fetch_assoc($Result);
	foreach ($myrow as $key=>$value) $Prefs[$key]=$value;
	// Build drop down menus for selectable criteria
	$Temp = RetrieveFields($ReportID, 'dateselect');
	$Prefs['DateListings'] = $Temp[0]; // only need the first field
	$Prefs['GroupListings'] = RetrieveFields($ReportID, 'grouplist');
	$Prefs['CritListings'] = RetrieveFields($ReportID, 'critlist');
	return $Prefs;
}

function RetrieveFields($ReportID, $EntryType) {
	global $db;
	$FieldListings = '';
	$sql= "SELECT *	FROM ".DBRptFields." 
		WHERE reportid = ".$ReportID." AND entrytype = '".$EntryType."'
		ORDER BY seqnum";
	$Result=DB_query($sql,$db,'','',false,true);
	while ($FieldValues = DB_fetch_assoc($Result)) { $FieldListings[] = $FieldValues; }
	return $FieldListings;
}

function BuildCriteria($FieldListings) {
	global $db, $CritChoices;
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

?>
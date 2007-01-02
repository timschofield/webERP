<?php
/* $Revision: 1.6 $ */

/*
This script has the responsibility to gather basic information necessary to retrieve data for reports. 
It is comprised of several steps designed to gather display preferences, database information, field 
information and filter/criteria information. The Report builder process is as follows:

Step 1: (or script entry): displays the current listing of reports. Uses form ReportsHome.html as a UI.
Step 2: (action=step2): After the user has selected an option, this step is followed to enter a report 
	name and the type of report it is for grouping purposes.
Step 3: Handles the page setup information.
Step 4: Handles the database setup and link information.
Step 5: Handles the database field selection.
Step 6: Handles the Criteria and filter selection.
Export: Handled in action=step2, calls ExportReport to save report as a text file.
Import: Handled in action=step8, calls an import function to read the setup information from a text file.
*/

$DirectoryLevelsDeep = 2;
$PathPrefix = '../../';
$PageSecurity = 2; // set security level for webERP
// Fetch necessary include files for webERP
require ($PathPrefix . 'includes/session.inc');

// Initialize some constants
$ReportLanguage = 'en_US';				// default language file 
define('DBReports','reports');		// name of the databse holding the main report information (ReportID)
define('DBRptFields','reportfields');	// name of the database holding the report fields
define ('DefRptPath',$PathPrefix . 'companies/' . $_SESSION['DatabaseName'] . '/reportwriter/');	// path to default reports
define ('MyDocPath',$PathPrefix . 'companies/' . $_SESSION['DatabaseName'] . '/reportwriter/');	// path to user saved documents


// Fetch necessary include files for report creator
require_once('../languages/' . $ReportLanguage . '/reports.php');
require_once('defaults.php');
require('RCFunctions.inc');

$usrMsg = ''; // initialize array for return messages
// a valid report id needs to be passed as a post field to do anything, except create new report
if (!isset($_POST['ReportID'])) { // entered for the first time or created new report
	$ReportID = ''; 
} else { 
	$ReportID = $_POST['ReportID'];
	if (isset($_POST['Type'])) { // then the type was passed from the previous form
		$Type=$_POST['Type'];
	} else { // we only have a reportid, we need to retrieve the type from thge db to set up the forms correctly
		$sql = "SELECT reporttype FROM ".DBReports." WHERE id='".$ReportID."'";
		$Result=DB_query($sql,$db,'','',false,true);
		$myrow = DB_fetch_array($Result);
		$Type = $myrow[0];
	}
}
switch ($_GET['action']) {
	default:
	case "step2": // entered from select an action (home) page
		// first check to see if a report was selected (except new report and import)
		if (!isset($_GET['action']) OR ($ReportID=='' AND $_POST['todo']<>RPT_BTN_ADDNEW AND $_POST['todo']<>RPT_BTN_IMPORT)) {
			// skip error message if back from import was pressed
			$DropDownString = RetrieveReports();
			if (isset($_GET['action'])) $usrMsg[] = array('message'=>FRM_NORPT, 'level'=>'error');
			$FormParams = PrepStep('1');
			break;
		}
		switch ($_POST['todo']) {
			case RPT_BTN_ADDNEW: // Fetch the defaults and got to select id screen
				$ReportID = '';
				$FormParams = PrepStep('2');
				break;
			case RPT_BTN_EDIT: // fetch the report information and go to the page setup screen
				$sql = "SELECT * FROM ".DBReports." WHERE id='".$ReportID."'";
				$Result=DB_query($sql,$db,'','',false,true);
				$myrow = DB_fetch_array($Result);
				$FormParams = PrepStep('3');
				break;
			case RPT_BTN_RENAME: // Rename a report was selected, fetch the report name and show rename form
				$sql = "SELECT reportname FROM ".DBReports." WHERE id='".$ReportID."'";
				$Result=DB_query($sql,$db,'','',false,true);
				$myrow = DB_fetch_array($Result);
				$_POST['ReportName'] = $myrow['reportname'];
				// continue like copy was pushed
			case RPT_BTN_COPY: // Copy a report was selected 
				$FormParams = PrepStep('2');
				break;
			case RPT_BTN_DEL: // after confirmation, delete the report and go to the main report admin menu
				$sql= "DELETE FROM ".DBReports." WHERE id = ".$ReportID.";";
				$Result=DB_query($sql,$db,'','',false,true);
				$sql= "DELETE FROM ".DBRptFields." WHERE reportid = ".$ReportID.";";
				$Result=DB_query($sql,$db,'','',false,true);
				// reload main entry form
			default:
				$DropDownString = RetrieveReports();
				$FormParams = PrepStep('1');
				break;
			case RPT_BTN_EXPORT:
				ExportReport($ReportID); // We don't return from here, we exit the script
				break;
			case RPT_BTN_IMPORT: // show the file import form
				$ReportName = '';
				$FormParams = PrepStep('imp');
				break;
		}
	break; // End Step 2
	
	case "step3": // entered from id setup page
		switch ($_POST['todo']) {
			case RPT_BTN_REPLACE: // Erase the default report and copy a new one with the same name
				if (isset($_POST['ReplaceReportID'])) { // then we need to delete the report to replace
					$sql= "DELETE FROM ".DBReports." WHERE id = ".$_POST['ReplaceReportID'].";";
					$Result=DB_query($sql,$db,'','',false,true);
					$sql= "DELETE FROM ".DBRptFields." WHERE reportid = ".$_POST['ReplaceReportID'].";";
					$Result=DB_query($sql,$db,'','',false,true);
				}
				// report has been deleted, continue to create or copy (in case 'Continue' below)
			case RPT_BTN_CONT: // fetch the report information and go to the page setup screen
				// input error check reportname, blank duplicate, bad characters, etc.
				if ($_POST['ReportName']=='') { // no report name was entered, error and reload form
					$usrMsg[] = array('message'=>RPT_NORPT, 'level'=>'error');
					$FormParams = PrepStep('2');
					break;
				}
				// check for duplicate report name
				$sql = "SELECT id FROM ".DBReports." WHERE reportname='".addslashes($_POST['ReportName'])."';";
				$Result=DB_query($sql,$db,'','',false,true);
				if (DB_num_rows($Result)>0) { // then we have a duplicate report name, error and reload
					$myrow = DB_fetch_array($Result);
					$ReplaceReportID = $myrow['id']; // save the duplicate report id
					$usrMsg[] = array('message'=>RPT_SAVEDUP, 'level'=>'error');
					$usrMsg[] = array('message'=>RPT_DEFDEL, 'level'=>'warn');
					$FormParams = PrepStep('2');
					break;
				}
				// Input validated perform requested operation
				if ($ReportID=='') { // then it's a new report
					// Check to see if a form or report to create
					if ($_POST['NewType']=='') { // then no type selected, error and re-display form
						$usrMsg[] = array('message'=>RPT_NORPTTYPE, 'level'=>'warn');
						$FormParams = PrepStep('2');
						break;
					} elseif ($_POST['NewType']=='rpt') { // a report, read the groupname
						$GroupName = $_POST['GroupName'];
					} elseif ($_POST['NewType']=='frm') { // a form, set the groupname
						$GroupName = $_POST['FormGroup'];
					}
					$Type = $_POST['NewType'];
					$sql = "INSERT INTO ".DBReports." (reportname, reporttype, groupname, defaultreport)
						VALUES ('".addslashes($_POST['ReportName'])."', '".$Type."', '".$GroupName."', '1')";
					$Result=DB_query($sql,$db,'','',false,true);
					$ReportID = DB_Last_Insert_ID($db,DBReports,'id');
					// Set some default report information: date display default choices to 'ALL'
					if ($Type<>'frm') { // set the truncate long descriptions default
						$sql = "INSERT INTO ".DBRptFields." (reportid, entrytype, params, displaydesc)
							VALUES (".$ReportID.", 'trunclong', '0', '');";
						$Result=DB_query($sql,$db,'','',false,true);
					} else { // it's a form so write a default form break record
						$sql = "INSERT INTO ".DBRptFields." (reportid, entrytype, params, displaydesc)
							VALUES (".$ReportID.", 'grouplist', '', '');";
						$Result=DB_query($sql,$db,'','',false,true);
					}
					$sql = "INSERT INTO ".DBRptFields." (reportid, entrytype, fieldname, displaydesc)
						VALUES (".$ReportID.", 'dateselect', '', 'a');";
					$Result=DB_query($sql,$db,'','',false,true);
				} else { // copy the report and all fields to the new report name
					$OrigID = $ReportID;
					// Set the report id to 0 to prepare to copy
					$sql = "UPDATE ".DBReports." SET id=0 WHERE id=".$ReportID.";";
					$Result=DB_query($sql,$db,'','',false,true);
					$sql = "INSERT INTO ".DBReports." SELECT * FROM ".DBReports." WHERE id = 0;";
					$Result=DB_query($sql,$db,'','',false,true);
					// Fetch the id entered
					$ReportID = DB_Last_Insert_ID($db,DBReports,'id');
					// Restore original report ID from 0
					$sql = "UPDATE ".DBReports." SET id=".$OrigID." WHERE id=0;";
					$Result=DB_query($sql,$db,'','',false,true);
					// Set the report name and group name per the form
					$sql = "UPDATE ".DBReports." SET 
							reportname = '" . DB_escape_string($_POST['ReportName']) . "' 
						WHERE id =".$ReportID.";";
					$Result=DB_query($sql,$db,'','',false,true);
					// fetch the fields and duplicate
					$sql = "SELECT * FROM ".DBRptFields." WHERE reportid=".$OrigID.";";
					$Result=DB_query($sql,$db,'','',false,true);
					while ($temp = DB_fetch_array($Result)) $field[] = $temp;
					foreach ($field as $row) {
						$sql = "INSERT INTO ".DBRptFields." (reportid, entrytype, seqnum, fieldname, 
								displaydesc, visible, columnbreak, params)
							VALUES (".$ReportID.", '".$row['entrytype']."', ".$row['seqnum'].",
								'".$row['fieldname']."', '".$row['displaydesc']."', '".$row['visible']."',
								'".$row['columnbreak']."', '".$row['params']."');";
						$Result=DB_query($sql,$db,'','',false,true);
					}
				}
				// read back in new data for next screen (will set defaults as defined in the db)
				$sql = "SELECT * FROM ".DBReports." WHERE id='".$ReportID."'";
				$Result=DB_query($sql,$db,'','',false,true);
				$myrow = DB_fetch_array($Result);
				$FormParams = PrepStep('3');
				break;
	
			case RPT_BTN_RENAME: // Rename a report was selected, fetch the report name and update
				// input error check reportname, blank duplicate, bad characters, etc.
				if ($_POST['ReportName']=='') { // no report name was entered, error and reload form
					$usrMsg[] = array('message'=>RPT_NORPT, 'level'=>'error');
					$FormParams = PrepStep('2');
					break;
				}
				// check for duplicate report name
				$sql = "SELECT id FROM ".DBReports." WHERE reportname='".addslashes($_POST['ReportName'])."';";
				$Result=DB_query($sql,$db,'','',false,true);
				if (DB_num_rows($Result)>0) { // then we have a duplicate report name, error and reload
					$myrow = DB_fetch_array($Result);
					if ($myrow['id']<>$ReportID) { // then the report has a duplicate name to something other than itself, error
						$usrMsg[] = array('message'=>RPT_REPDUP, 'level'=>'error');
						$FormParams = PrepStep('2');
						break;
					}
				}
				$sql = "UPDATE ".DBReports." SET reportname='".addslashes($_POST['ReportName'])."' WHERE id=".$ReportID.";";
				$Result=DB_query($sql,$db,'','',false,true);
				$usrMsg[] = array('message'=>RPT_UPDATED, 'level'=>'success');
				// continue with default to return to reports home
			case RPT_BTN_BACK:
			default:	// bail to reports home
				$DropDownString = RetrieveReports();
				$FormParams = PrepStep('1');
		}
	break;
	
	case "step4": // entered from page setup page
		switch ($_POST['todo']) {
			case RPT_BTN_UPDATE:
				$success = UpdatePageFields($ReportID);
				// read back in new data for next screen (will set defaults as defined in the db)
				$sql = "SELECT * FROM ".DBReports." WHERE id='".$ReportID."'";
				$Result=DB_query($sql,$db,'','',false,true);
				$myrow = DB_fetch_array($Result);
				$FormParams = PrepStep('3');
				break;
			case RPT_BTN_CONT: // fetch the report information and go to the page setup screen
				$success = UpdatePageFields($ReportID);
				// read in the data for the next form
				$sql = "SELECT table1, 
						table2, table2criteria,
						table3, table3criteria,
						table4, table4criteria,
						table5, table5criteria,
						table6, table6criteria,
						reportname
					FROM " . DBReports . " WHERE id='".$ReportID."'";
				$Result=DB_query($sql,$db,'','',false,true);
				$myrow = DB_fetch_array($Result);
				$numrows = DB_num_rows($Result);
				$FormParams = PrepStep('4');
				break;
			case RPT_BTN_BACK:
			default:	// bail to reports home
				$DropDownString = RetrieveReports();
				$FormParams = PrepStep('1');
		}
	break;
	
	case "step5": // entered from dbsetup page
		switch ($_POST['todo']) {
			case RPT_BTN_BACK:
				$sql = "SELECT * FROM ".DBReports." WHERE id='".$ReportID."'";
				$Result=DB_query($sql,$db,'','',false,true);
				$myrow = DB_fetch_array($Result);
				$FormParams = PrepStep('3');
				break;
			case RPT_BTN_UPDATE:
			case RPT_BTN_CONT: // fetch the report information and go to the page setup screen
				if ($_POST['Table1']) {
					$sql = "SELECT table1 FROM ".DBReports." WHERE id='".$ReportID."'";
					$Result=DB_query($sql,$db,'','',false,true);
					$myrow = DB_fetch_row($Result);
					if ($myrow[0] != $_POST['Table1']) {
						unset($_POST['Table2']); unset($_POST['Table2Criteria']);
						unset($_POST['Table3']); unset($_POST['Table3Criteria']);
						unset($_POST['Table4']); unset($_POST['Table4Criteria']);
						unset($_POST['Table5']); unset($_POST['Table5Criteria']);
						unset($_POST['Table6']); unset($_POST['Table6Criteria']);
					}
				}
				$success = UpdateDBFields($ReportID);
				if (!$success OR $_POST['todo']==RPT_BTN_UPDATE) { 
					// update fields and stay on this form
					if (!$success) $usrMsg[] = array('message'=>RPT_DUPDB, 'level'=>'error');
					// read back in new data for next screen (will set defaults as defined in the db)
					$sql = "SELECT table1, 
							table2, table2criteria, 
							table3, table3criteria, 
							table4, table4criteria, 
							table5, table5criteria, 
							table6, table6criteria, 
							reportname
						FROM ".DBReports." WHERE id='".$ReportID."'";
					$Result=DB_query($sql,$db,'','',false,true);
					$myrow = DB_fetch_array($Result);
					$FormParams = PrepStep('4');
					break;
				}
				// read in fields and continue to next form
				$reportname = $_POST['ReportName'];
				$FieldListings = RetrieveFields('fieldlist');
				$FormParams = PrepStep('5');
				break;
			default:	// bail to reports home
				$DropDownString = RetrieveReports();
				$FormParams = PrepStep('1');
		}
	break;
	
	case "step6": // entered from field setup page
		if (!isset($_POST['todo'])) {	// then a sequence image button was pushed
			$SeqNum = $_POST['SeqNum']; //fetch the sequence number
			if (isset($_POST['up_x'])) { // the shift up button was pushed, check for not at first sequence
				if ($SeqNum<>1) $success = ChangeSequence($SeqNum, 'fieldlist', 'up');
				$FieldListings = RetrieveFields('fieldlist');
			} elseif (isset($_POST['dn_x'])) { // the shift down button was pushed
				$sql = "SELECT seqnum FROM ".DBRptFields." WHERE reportid = ".$ReportID." AND entrytype = 'fieldlist';";
				$Result=DB_query($sql,$db,'','',false,true);
				if ($SeqNum<DB_num_rows($Result)) $success = ChangeSequence($SeqNum, 'fieldlist', 'down');
				$FieldListings = RetrieveFields('fieldlist');
			} elseif (isset($_POST['ed_x'])) { // the sequence edit button was pushed
				// pre fill form with the field to edit and change button name 
				$FieldListings = RetrieveFields('fieldlist');
				$sql = "SELECT * FROM ".DBRptFields." 
					WHERE reportid = ".$ReportID." AND entrytype = 'fieldlist' AND seqnum=".$SeqNum.";";
				$Result=DB_query($sql,$db,'','',false,true);
				$FieldListings['defaults'] = DB_fetch_array($Result);
				$FieldListings['defaults']['buttonvalue'] = RPT_BTN_CHANGE;
			} elseif (isset($_POST['rm_x'])) { // the sequence remove button was pushed
				$success = DeleteSequence($_POST['SeqNum'], 'fieldlist');
				$FieldListings = RetrieveFields('fieldlist');
			}
			$reportname = $_POST['ReportName'];
			$FormParams = PrepStep('5');
		} else {
			switch ($_POST['todo']) {
				case RPT_BTN_BACK:
					$sql = "SELECT table1, 
							table2, table2criteria, 
							table3, table3criteria, 
							table4, table4criteria, 
							table5, table5criteria, 
							table6, table6criteria, 
							reportname
						FROM ".DBReports." WHERE id='".$ReportID."'";
					$Result=DB_query($sql,$db,'','',false,true);
					$myrow = DB_fetch_array($Result);
					$FormParams = PrepStep('4');
					break;
				case RPT_BTN_ADDNEW:
				case RPT_BTN_CHANGE:
					// error check input
					$IsValidField = ValidateField($ReportID, $_POST['FieldName'], $_POST['DisplayDesc']);
					if (!$IsValidField) { // then user entered a bad fieldname or description, error and reload
						$usrMsg[] = array('message'=>RPT_BADFLD, 'level'=>'error');
						// reload form with bad data entered as field defaults, ready to be editted
						$FieldListings = RetrieveFields('fieldlist');
						$FieldListings['defaults']['seqnum']=$_POST['SeqNum'];
						$FieldListings['defaults']['fieldname']=$_POST['FieldName'];
						$FieldListings['defaults']['displaydesc']=$_POST['DisplayDesc'];
						$FieldListings['defaults']['columnbreak']=$_POST['ColumnBreak'];
						$FieldListings['defaults']['visible']=$_POST['Visible'];
						$FieldListings['defaults']['params']=$_POST['Params'];
						if ($_POST['todo']==RPT_BTN_ADDNEW) { // add new so insert
							$FieldListings['defaults']['buttonvalue'] = RPT_BTN_ADDNEW;
						} else { // exists, so update it.
							$FieldListings['defaults']['buttonvalue'] = RPT_BTN_CHANGE;
						}
						$reportname = $_POST['ReportName'];
						$FormParams = PrepStep('5');
						break;
					} 
					if ($_POST['todo']==RPT_BTN_ADDNEW) { // add new so insert
						$_POST['SeqNum'] = InsertSequence($_POST['SeqNum'], 'fieldlist');
					} else { // exists, so update it.
						$success = UpdateSequence('fieldlist');
					}
					if ($Type<>'frm') {
						$FieldListings = RetrieveFields('fieldlist');
						$reportname = $_POST['ReportName'];
						$FormParams = PrepStep('5');
						break;
					}
					// Go to the properties screen for the field just entered
				case RPT_BTN_PROP: // Enter the properties of a given field
					// see what form needs to be loaded and load based on index stored in params variable
					$SeqNum = $_POST['SeqNum'];
					$sql = "SELECT id, displaydesc, params FROM ".DBRptFields." 
						WHERE reportid = ".$ReportID." AND entrytype='fieldlist' AND seqnum = ".$SeqNum.";";
					$Result = DB_query($sql,$db,'','',false,true);
					$myrow = DB_fetch_assoc($Result);
					$Params = unserialize($myrow['params']);
					$reportname = $_POST['ReportName'];
					$ButtonValue = RPT_BTN_ADDNEW; // default the field button to Add New for form entry
					$FormParams = PrepStep('prop');
					$FormParams['id'] = $myrow['id'];
					$DisplayName = $myrow['displaydesc'];
					break;
				case RPT_BTN_CONT: // fetch the report information and go to the page setup screen
					$DateListings = RetrieveFields('dateselect');
					$DateListings = $DateListings['lists'][0]; // only need the first field
					$TruncListings = RetrieveFields('trunclong');
					$TruncListings = $TruncListings['lists'][0]; // only need the first field
					$SortListings = RetrieveFields('sortlist');
					$GroupListings = RetrieveFields('grouplist');
					$CritListings = RetrieveFields('critlist');
					$reportname = $_POST['ReportName'];
					$FormParams = PrepStep('6');
					break;
				default: // bail to reports home
					$DropDownString = RetrieveReports();
					$FormParams = PrepStep('1');
					break;
			}
		}
	break;
	
	case "step6a": // entered from properties page for fields
		$ButtonValue = RPT_BTN_ADDNEW; // default the field button to Add New unless overidden by the edit image pressed
		$reportname = $_POST['ReportName'];
		$SeqNum = $_POST['SeqNum'];
		// first fetch the original Params
		$sql = "SELECT id, params FROM ".DBRptFields." 
			WHERE reportid = ".$ReportID." AND entrytype='fieldlist' AND seqnum = ".$SeqNum.";";
		$Result = DB_query($sql,$db,'','',false,true);
		$myrow = DB_fetch_assoc($Result);
		$Params = unserialize($myrow['params']);
		if (!isset($_POST['todo'])) { // then a sequence image button was pushed, we must be in form table entry
			$success = ModFormTblEntry($Params);
			if (!$success) { // check for errors
				$usrMsg[] = array('message'=>RPT_BADDATA, 'level'=>'error');
			} else { // update the database
				$sql = "UPDATE ".DBRptFields." SET params='".serialize($Params)."' WHERE id = ".$_POST['ID'].";";
				$Result=DB_query($sql,$db,'','',false,true);
				if ($success=='edit') { // then the edit button was pressed, change button name from Add New to Change
					$ButtonValue = RPT_BTN_CHANGE;
				}
			}
			// Update field properties 
			$FormParams = PrepStep('prop');
			$FormParams['id'] = $myrow['id']; 
		} else {
			// fetch the choices with the form post data
			foreach ($_POST as $key=>$value) $Params[$key]=$value;
			// check for what button or image was pressed
			switch ($_POST['todo']) {
				case RPT_BTN_CANCEL:
					$FieldListings = RetrieveFields('fieldlist');
					$FormParams = PrepStep('5');
					break;
				case RPT_BTN_ADD:
				case RPT_BTN_REMOVE: // For the total parameters gather the list of fieldnames
					// Process the button pushed
					if ($_POST['todo']==RPT_BTN_REMOVE) { // the remove button was pressed
						$Index = $_POST['FieldIndex'];
						if ($Index<>'') $Params['Seq'] = array_merge(array_slice($Params['Seq'],0,$Index),array_slice($Params['Seq'],$Index+1));
					} else { // it's the add button, error check
						if ($_POST['TotalField']=='') { 
							$usrMsg[] = array('message'=>RPT_BADFLD, 'level'=>'error');
							// reload form with bad data entered as field defaults, ready to be editted
							$DisplayName =$_POST['DisplayName'];
							$FormParams = PrepStep('prop');
							$FormParams['id'] = $myrow['id']; 
							break;
						} 
						$Params['Seq'][] = $_POST['TotalField'];
					}
					// Update field properties 
					$sql = "UPDATE ".DBRptFields." SET params='".serialize($Params)."' WHERE id = ".$_POST['ID'].";";
					$Result=DB_query($sql,$db,'','',false,true);
					$Params['TotalField']='';
					$FormParams = PrepStep('prop');
					$FormParams['id'] = $myrow['id']; 
					break;
				case RPT_BTN_CHANGE:
				case RPT_BTN_ADDNEW:
					// Error Check input, see if user entered a bad fieldname or description, error and reload
					if ($_POST['TblField']=='' OR ($Params['index']=='Tbl' AND $_POST['TblDesc']=='')) { 
						$usrMsg[] = array('message'=>RPT_BADFLD, 'level'=>'error');
						// reload form with bad data entered as field defaults, ready to be editted
						if ($_POST['todo']==RPT_BTN_ADDNEW) $ButtonValue = RPT_BTN_ADDNEW;
							else $ButtonValue = RPT_BTN_CHANGE;
						$DisplayName =$_POST['DisplayName'];
						$FormParams = PrepStep('prop');
						$FormParams['id'] = $myrow['id']; 
						break;
					} 
					if ($_POST['todo']==RPT_BTN_ADDNEW) $success = InsertFormSeq($Params,'insert');
						else $success = InsertFormSeq($Params, 'update');
					// continue on
				case RPT_BTN_UPDATE:
				case RPT_BTN_FINISH: // Enter the properties of a given field and return to the field setup screen
					// additional processing for the image upload in the form image type
					if ($Params['index']=='Img') {
						$success = ImportImage();
						if ($success['result']=='error') { // image upload failed
							$usrMsg[] = array('message'=>$success['message'], 'level'=>'error');
							$FormParams = PrepStep('prop');
							$FormParams['id'] = $myrow['id'];
							break;
						} else {
							$Params['filename'] = $success['filename'];
						}
					}
					// reset the sequence defaults to null for Table type only
					if ($Params['index']=='Tbl' OR $Params['index']=='TBlk') {
						$Params['TblSeqNum'] = '';
						$Params['TblField'] = '';
						$Params['TblDesc'] = '';
						$Params['Processing'] = '';
					}
					// Update field properties 
					$sql = "UPDATE ".DBRptFields." SET params='".serialize($Params)."' WHERE id = ".$_POST['ID'].";";
					$Result=DB_query($sql,$db,'','',false,true);
					// check for update errors and reload
					if ($_POST['todo']==RPT_BTN_FINISH) { // no errors and finished so return to field setup
						$FieldListings = RetrieveFields('fieldlist');
						$FormParams = PrepStep('5');
					} else { // print error message if need be and reload parameter form
						$DisplayName =$_POST['DisplayName'];
						$FormParams = PrepStep('prop');
						$FormParams['id'] = $myrow['id']; 
					}
					break;
				default: // bail to reports home
					$DropDownString = RetrieveReports();
					$FormParams = PrepStep('1');
					break;
			}
		}
	break;
	
	case "step7": // entered from criteria setup page
		$OverrideDefaults = false;
		if (!isset($_POST['todo'])) {	// then a sequence image button was pushed
			$SeqNum = $_POST['SeqNum']; //fetch the sequence number
			$EntryType = $_POST['EntryType']; //fetch the entry type
			if (isset($_POST['up_x'])) { // the shift up button was pushed
				if ($SeqNum<>1) $success = ChangeSequence($_POST['SeqNum'], $EntryType, 'up');
			} elseif (isset($_POST['dn_x'])) { // the shift down button was pushed
				$sql = "SELECT seqnum FROM ".DBRptFields." WHERE reportid = ".$ReportID." AND entrytype = '".$EntryType."';";
				$Result=DB_query($sql,$db,'','',false,true);
				if ($SeqNum<DB_num_rows($Result)) $success = ChangeSequence($_POST['SeqNum'], $EntryType, 'down');
			} elseif (isset($_POST['ed_x'])) { // the sequence edit button was pushed
				$OverrideDefaults = true;
				// pre fill form with the field to edit and change button name 
				$sql = "SELECT * FROM ".DBRptFields." 
					WHERE reportid = ".$ReportID." AND entrytype = '".$EntryType."' AND seqnum=".$SeqNum.";";
				$Result=DB_query($sql,$db,'','',false,true);
				$NewDefaults['defaults'] = DB_fetch_array($Result);
				$NewDefaults['defaults']['buttonvalue'] = RPT_BTN_CHANGE;
			} elseif (isset($_POST['rm_x'])) { // the sequence remove button was pushed
				$success = DeleteSequence($_POST['SeqNum'], $EntryType);
			}
			$reportname = $_POST['ReportName'];
				$FormParams = PrepStep('6');
		} else {
			switch ($_POST['todo']) {
				case RPT_BTN_BACK:
					$reportname = $_POST['ReportName'];
					$FormParams = PrepStep('5');
					break;
				case RPT_BTN_ADDNEW:
				case RPT_BTN_CHANGE:
					$EntryType = $_POST['EntryType']; //fetch the entry type
					// error check input
					$IsValidField = ValidateField($ReportID, $_POST['FieldName'], $_POST['DisplayDesc']);
					if (!$IsValidField) { // then user entered a bad fieldname or description, error and reload
						$usrMsg[] = array('message'=>RPT_BADFLD, 'level'=>'error');
						// reload form with bad data entered as field defaults, ready to be editted
						$OverrideDefaults = true;
						$NewDefaults['defaults']['seqnum']=$_POST['SeqNum'];
						$NewDefaults['defaults']['fieldname']=$_POST['FieldName'];
						$NewDefaults['defaults']['displaydesc']=$_POST['DisplayDesc'];
						if (isset($_POST['Params'])) $NewDefaults['defaults']['params']=$_POST['Params'];
						if ($_POST['todo']==RPT_BTN_ADDNEW) { // add new so insert
							$NewDefaults['defaults']['buttonvalue'] = RPT_BTN_ADDNEW;
						} else { // exists, so update it.
							$NewDefaults['defaults']['buttonvalue'] = RPT_BTN_CHANGE;
						}
					} else { // fetch the input results and save them
						if ($_POST['todo']==RPT_BTN_ADDNEW) { // add new so insert
							$success = InsertSequence($_POST['SeqNum'], $EntryType);
						} else { // record exists, so update it.
							$success = UpdateSequence($EntryType);
						}
					}
					$reportname = $_POST['ReportName'];
					$FormParams = PrepStep('6');
					break;
				case RPT_BTN_UPDATE: // update the date and general options fields, reload form
				case RPT_BTN_FINISH: // update fields and return to report manager screen
				default:	// bail to reports home
					//fetch the entry type
					if (isset($_POST['EntryType'])) $EntryType = $_POST['EntryType']; else $EntryType = '';
					// build date string of choices from user
					$DateString = '';
					for ($i=1; $i<=count($DateChoices); $i++) { 
						if (isset($_POST['DateRange'.$i])) $DateString .= $_POST['DateRange'.$i];
					}
					// error check input for date
					if ($DateString=='' OR $DateString=='a') { // then the report is date independent
						$_POST['DateField'] = ''; // clear the date field since we don't need it
						$IsValidField = true; // 
					} else { // check the input for a valid fieldname
						$IsValidField = ValidateField($ReportID, $_POST['DateField'], 'TestField');
					}
					if ($Type=='frm' AND $IsValidField) {
						$IsValidField = ValidateField($ReportID, $_POST['FormBreakField'], 'TestField');
					}
					if (!$IsValidField) { // then user entered a bad fieldname or description, error and reload
						$usrMsg[] = array('message'=>RPT_BADFLD, 'level'=>'error');
						// reload form with bad data entered as field defaults, ready to be editted
						$DateListings['displaydesc'] = $DateString;
						$DateListings['params'] = $_POST['DefDate'];
						$DateListings['fieldname'] = $_POST['DateField'];
						if ($Type=='frm') $GroupListings['lists'][0]['fieldname'] = $_POST['FormBreakField'];
						$reportname = $_POST['ReportName'];
						$DateError = true;
						$FormParams = PrepStep('6');
						break;
					} else { // fetch the input results and save them
						$DateError = false;
						$success = UpdateCritFields($ReportID, $DateString);
					}
					// read in fields for next form
					$reportname = $_POST['ReportName'];
					if ($_POST['todo']==RPT_BTN_FINISH) { // then finish was pressed
						$DropDownString = RetrieveReports(); // needed to return to reports manager home
						$FormParams = PrepStep('1');
					} else { // update was pressed, return to criteria form
						$FormParams = PrepStep('6');
					}
					break;
			}
		}
		// reload fields to display form
		$FieldListings = RetrieveFields('fieldlist'); // needed for GO Back (fields) screen
		// Below needed to reload criteria form
		if (!$DateError) {
			$DateListings = RetrieveFields('dateselect');
			$DateListings = $DateListings['lists'][0]; // only need the first field
		}
		$TruncListings = RetrieveFields('trunclong');
		$TruncListings = $TruncListings['lists'][0]; // only need the first field
		$SortListings = RetrieveFields('sortlist');
		$GroupListings = RetrieveFields('grouplist');
		$CritListings = RetrieveFields('critlist');
		// override defaults used for edit of existing fields.
		if ($OverrideDefaults) {
			switch ($EntryType) {
				case "sortlist":
					$SortListings['defaults'] = $NewDefaults['defaults'];
					$SortListings['defaults']['buttonvalue'] = $NewDefaults['defaults']['buttonvalue'];
					break;
				case "grouplist":
					$GroupListings['defaults'] = $NewDefaults['defaults'];
					$GroupListings['defaults']['buttonvalue'] = $NewDefaults['defaults']['buttonvalue'];
					break;
				case "critlist":
					$CritListings['defaults'] = $NewDefaults['defaults'];
					$CritListings['defaults']['buttonvalue'] = $NewDefaults['defaults']['buttonvalue'];
					break;
			}
		}
	break; // End Step 7
	
	case "step8": // Entered from import report form
		switch ($_POST['todo']) {
			case RPT_BTN_IMPORT: // Error check input and import the new report
				$success = ImportReport(trim($_POST['reportname']));
				$usrMsg[] = array('message'=>$success['message'], 'level'=>$success['result']);
				if ($success['result']=='error') {
					$FormParams = PrepStep('imp');
					break;
				}
				// All through and imported successfully, return to reports home page
			case RPT_BTN_BACK:
			default:
				$DropDownString = RetrieveReports();
				$FormParams = PrepStep('1');
		}
	break; // End Step 8
} // end switch

$title = $FormParams['title']; // fetch the title for the header.inc file

include ($PathPrefix . 'includes/header.inc');
if ($usrMsg) foreach ($usrMsg as $temp) prnmsg($temp['message'],$temp['level']);
include ($FormParams['IncludePage']);
include ($PathPrefix . 'includes/footer.inc');
// End main body
?>
<?php
/* $Revision: 1.1 $ */

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

$DirectoryLevelsDeep =2;
$PathPrefix = '../../';
$PageSecurity = 2; // set security level for webERP

// webERP starts session in session.inc below 
//session_start();

// Initialize some constants
$dirpath = '../..';	// disk path to the web app root directory
$ReportLanguage = 'en_US';				// default language file 
define('DBReports','reports');		// name of the databse holding the main report information (ReportID)
define('DBRptFields','reportfields');	// name of the database holding the report fields
define ('DefRptPath','../languages/'.$ReportLanguage.'/');	// path to default reports

// Fetch necessary include files for webERP
require ('../../includes/session.inc');

// Fetch necessary include files for report creator
require_once('../languages/' . $ReportLanguage . '/reports.php');
require_once('defaults.php');
require('RCFunctions.inc');

$usrMsg = ''; // setup array for return messages
//check to see how script was entered
if (!isset($_REQUEST['action'])) {	// then form entered from somewhere other than itself, show start form
// fetch the existing reports for the selection menus
	$DropDownString = RetrieveReports();
	$title=RPT_ADMSTP1;
	$IncludePage = 'forms/ReportsHome.html';
} else {
  // a valid report id needs to be passed as a post field to do anything, except create new report
  if (!isset($_POST['ReportID'])) $ReportID = ''; else $ReportID = $_POST['ReportID'];
  switch ($_REQUEST['action']) {
	case "step2": // entered from select an action (home) page
		// first check to see if a report was selected (except new report and import)
		if ($ReportID=='' AND $_POST['todo']<>RPT_BTN_NEWRPT AND $_POST['todo']<>RPT_BTN_IMPORT) {
			// skip error message if back from import was pressed
			$usrMsg[] = array('message'=>RPT_NORPT, 'level'=>'error');
			$DropDownString = RetrieveReports();
			$title=RPT_ADMSTP1;
			$IncludePage = 'forms/ReportsHome.html';
			break;
		}
		switch ($_POST['todo']) {
			case RPT_BTN_NEWRPT: // Fetch the defaults and got to select id screen
				$ReportID = '';
				$title=RPT_ADMSTP2;
				$IncludePage = 'forms/ReportsID.html';
				break;
			case RPT_BTN_EDIT: // fetch the report information and go to the page setup screen
				$sql = "";
				$sql = "SELECT * FROM ".DBReports." WHERE id='".$ReportID."'";
				$Result=DB_query($sql,$db,'','',false,true);
				$myrow = DB_fetch_array($Result);
				$title=RPT_ADMSTP3;
				$IncludePage = 'forms/ReportsPageSetup.html';
				break;
			case RPT_BTN_RENAME: // Rename a report was selected, fetch the report name and show rename form
				$sql = "SELECT reportname FROM ".DBReports." WHERE id='".$ReportID."'";
				$Result=DB_query($sql,$db,'','',false,true);
				$myrow = DB_fetch_array($Result);
				$_POST['ReportName'] = $myrow['reportname'];
				$title=RPT_ADMSTP3;
				$IncludePage = 'forms/ReportsRename.html';
				break;
			case RPT_BTN_COPY: // Copy a report was selected 
				$title=RPT_ADMSTP2;
				$IncludePage = 'forms/ReportsID.html';
				break;
			case RPT_BTN_DEL: // after confirmation, delete the report and go to the main report admin menu
				$sql= "DELETE FROM ".DBReports." WHERE id = ".$ReportID.";";
				$Result=DB_query($sql,$db,'','',false,true);
				$sql= "DELETE FROM ".DBRptFields." WHERE reportid = ".$ReportID.";";
				$Result=DB_query($sql,$db,'','',false,true);
				$DropDownString = RetrieveReports();
				$title=RPT_ADMSTP1;
				$IncludePage = 'forms/ReportsHome.html';
				break;
			case RPT_BTN_EXPORT:
				ExportReport($ReportID); // We don't return from here, we exit the script
				break;
			case RPT_BTN_IMPORT: // show the file import form
				$ReportName = '';
				$title=RPT_ADMSTP7;
				$IncludePage = 'forms/ReportsImport.html';
				break;
			default:
				$DropDownString = RetrieveReports();
				$title=RPT_ADMSTP1;
				$IncludePage = 'forms/ReportsHome.html';
		}
	break; // End Step 2

	case "step3": // entered from id setup page
		switch ($_POST['todo']) {
			case RPT_BTN_REPLACE: // Erase the default report and create a new blank one with the same name
				$sql= "DELETE FROM ".DBReports." WHERE id = ".$ReportID.";";
				$Result=DB_query($sql,$db,'','',false,true);
				$sql= "DELETE FROM ".DBRptFields." WHERE reportid = ".$ReportID.";";
				$Result=DB_query($sql,$db,'','',false,true);
				$ReportID=''; // reest the reportid to nothing to trigger creating a new report
				// report has been deleted, continue to create a new blank report (in case 'Continue' below)
			case RPT_BTN_CONT: // fetch the report information and go to the page setup screen
				// input error check reportname, blank duplicate, bad characters, etc.
				if ($_POST['ReportName']=='') { // no report name was entered, error and reload form
					$usrMsg[] = array('message'=>RPT_NORPT, 'level'=>'error');
					$title=RPT_ADMSTP2;
					$IncludePage = 'forms/ReportsID.html';
					break;
				}
				// check for duplicate report name
				$sql = "SELECT id FROM ".DBReports." WHERE reportname='".addslashes($_POST['ReportName'])."';";
				$Result=DB_query($sql,$db,'','',false,true);
				if (DB_num_rows($Result)>0) { // then we have a duplicate report name, error and reload
					$myrow = DB_fetch_array($Result);
					$ReportID = $myrow['id']; // save the duplicate report id
					$usrMsg[] = array('message'=>RPT_SAVEDUP, 'level'=>'error');
					$usrMsg[] = array('message'=>RPT_DEFDEL, 'level'=>'warn');
					$title=RPT_ADMSTP2;
					$IncludePage = 'forms/ReportsID.html';
					break;
				}
				// Input validated perform requested operation
				if ($ReportID=='') { // then it's a new report
					$sql = "INSERT INTO ".DBReports." (reportname, reporttype, groupname, defaultreport)
						VALUES ('".addslashes($_POST['ReportName'])."', 'rpt', '".$_POST['GroupName']."', '1')";
					$Result=DB_query($sql,$db,'','',false,true);
					$ReportID = DB_Last_Insert_ID($db,DBReports,'id');
					// Set some default report information: date display default choices to 'ALL'
					$sql = "INSERT INTO ".DBRptFields." (reportid, entrytype, fieldname, displaydesc)
						VALUES (".$ReportID.", 'dateselect', '', 'a');";
					$Result=DB_query($sql,$db,'','',false,true);
					// truncate long descriptions default
					$sql = "INSERT INTO ".DBRptFields." (reportid, entrytype, params, displaydesc)
						VALUES (".$ReportID.", 'trunclong', '0', '');";
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
							reportname = '".addslashes($_POST['ReportName'])."', 
							groupname = '".$_POST['GroupName']."' 
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
				$title=RPT_ADMSTP3;
				$IncludePage = 'forms/ReportsPageSetup.html';
				break;

			case RPT_BTN_RENAME: // Rename a report was selected, fetch the report name and update
				// input error check reportname, blank duplicate, bad characters, etc.
				if ($_POST['ReportName']=='') { // no report name was entered, error and reload form
					$usrMsg[] = array('message'=>RPT_NORPT, 'level'=>'error');
					$title=RPT_ADMSTP2;
					$IncludePage = 'forms/ReportsRename.html';
					break;
				}
				// check for duplicate report name
				$sql = "SELECT id FROM ".DBReports." WHERE reportname='".addslashes($_POST['ReportName'])."';";
				$Result=DB_query($sql,$db,'','',false,true);
				if (DB_num_rows($Result)>0) { // then we have a duplicate report name, error and reload
					$myrow = DB_fetch_array($Result);
					if ($myrow['id']<>$ReportID) { // then the report has a duplicate name to something other than itself, error
						$usrMsg[] = array('message'=>RPT_REPDUP, 'level'=>'error');
						$title=RPT_ADMSTP2;
						$IncludePage = 'forms/ReportsRename.html';
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
				$title=RPT_ADMSTP1;
				$IncludePage = 'forms/ReportsHome.html';
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
				$title=RPT_ADMSTP3;
				$IncludePage = 'forms/ReportsPageSetup.html';
				break;
			case RPT_BTN_CONT: // fetch the report information and go to the page setup screen
				$success = UpdatePageFields($ReportID);
				// read in the data for the next form
				$sql = "SELECT table1, table2, table2criteria, table3, table3criteria, table4, table4criteria, reportname
					FROM ".DBReports." WHERE id='".$ReportID."'";
				$Result=DB_query($sql,$db,'','',false,true);
				$myrow = DB_fetch_array($Result);
				$title=RPT_ADMSTP4;
				$IncludePage = 'forms/ReportsDBSetup.html';
				break;
			case RPT_BTN_BACK:
			default:	// bail to reports home
				$DropDownString = RetrieveReports();
				$title=RPT_ADMSTP1;
				$IncludePage = 'forms/ReportsHome.html';
		}
	break;

	case "step5": // entered from dbsetup page
		switch ($_POST['todo']) {
			case RPT_BTN_BACK:
				$sql = "SELECT * FROM ".DBReports." WHERE id='".$ReportID."'";
				$Result=DB_query($sql,$db,'','',false,true);
				$myrow = DB_fetch_array($Result);
				$title=RPT_ADMSTP3;
				$IncludePage = 'forms/ReportsPageSetup.html';
				break;
			case RPT_BTN_UPDATE:
			case RPT_BTN_CONT: // fetch the report information and go to the page setup screen
				$success = UpdateDBFields($ReportID);
				if (!$success OR $_POST['todo']==RPT_BTN_UPDATE) { // update fields and stay on this form
					if (!$success) $usrMsg[] = array('message'=>RPT_DUPDB, 'level'=>'error');
					// read back in new data for next screen (will set defaults as defined in the db)
					$sql = "SELECT table1, table2, table2criteria, table3, table3criteria, table4, table4criteria, reportname
						FROM ".DBReports." WHERE id='".$ReportID."'";
					$Result=DB_query($sql,$db,'','',false,true);
					$myrow = DB_fetch_array($Result);
					$title=RPT_ADMSTP4;
					$IncludePage = 'forms/ReportsDBSetup.html';
					break;
				}
				// read in fields and continue to next form
				$reportname = $_POST['ReportName'];
				$FieldListings = RetrieveFields('fieldlist');
				$title=RPT_ADMSTP5;
				$IncludePage = 'forms/ReportsFieldSetup.html';
				break;
			default:	// bail to reports home
				$DropDownString = RetrieveReports();
				$title=RPT_ADMSTP1;
				$IncludePage = 'forms/ReportsHome.html';
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
				$FieldListings['defaults']['buttonvalue'] = 'Change';
			} elseif (isset($_POST['rm_x'])) { // the sequence remove button was pushed
				$success = DeleteSequence($_POST['SeqNum'], 'fieldlist');
				$FieldListings = RetrieveFields('fieldlist');
			}
			$title=RPT_ADMSTP5;
			$reportname = $_POST['ReportName'];
			$IncludePage = 'forms/ReportsFieldSetup.html';
		} else {
			switch ($_POST['todo']) {
				case RPT_BTN_BACK:
					$sql = "SELECT table1, table2, table2criteria, table3, table3criteria, table4, table4criteria, reportname
						FROM ".DBReports." WHERE id='".$ReportID."'";
					$Result=DB_query($sql,$db,'','',false,true);
					$myrow = DB_fetch_array($Result);
					$title=RPT_ADMSTP4;
					$IncludePage = 'forms/ReportsDBSetup.html';
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
					} else { // fetch the input results and save them
						if ($_POST['todo']==RPT_BTN_ADDNEW) { // add new so insert
							$success = InsertSequence($_POST['SeqNum'], 'fieldlist', $_POST);
						} else { // exists, so update it.
							$success = UpdateSequence('fieldlist', $_POST);
						}
						$FieldListings = RetrieveFields('fieldlist');
					}
					// read in fields for next form
					$reportname = $_POST['ReportName'];
					$title=RPT_ADMSTP5;
					$IncludePage = 'forms/ReportsFieldSetup.html';
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
					$title=RPT_ADMSTP6;
					$IncludePage = 'forms/ReportsCritSetup.html';
					break;
				default:	// bail to reports home
					$DropDownString = RetrieveReports();
					$title=RPT_ADMSTP1;
					$IncludePage = 'forms/ReportsHome.html';
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
			$title=RPT_ADMSTP6;
			$IncludePage = 'forms/ReportsCritSetup.html';
		} else {
			switch ($_POST['todo']) {
				case RPT_BTN_BACK:
					$title=RPT_ADMSTP5;
					$reportname = $_POST['ReportName'];
					$IncludePage = 'forms/ReportsFieldSetup.html';
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
							$success = InsertSequence($_POST['SeqNum'], $EntryType, $_POST);
						} else { // record exists, so update it.
							$success = UpdateSequence($EntryType, $_POST);
						}
					}
					$reportname = $_POST['ReportName'];
					$title=RPT_ADMSTP6;
					$IncludePage = 'forms/ReportsCritSetup.html';
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
						$IsValidField = ValidateField($ReportID, $_POST['DateField'], 'Date');
					}
					if (!$IsValidField) { // then user entered a bad fieldname or description, error and reload
						$usrMsg[] = array('message'=>RPT_BADFLD, 'level'=>'error');
						// reload form with bad data entered as field defaults, ready to be editted
						$DateListings['displaydesc'] = $DateString;
						$DateListings['params'] = $_POST['DefDate'];
						$DateListings['fieldname'] = $_POST['DateField'];
						$reportname = $_POST['ReportName'];
						$DateError = true;
						$title=RPT_ADMSTP6;
						$IncludePage = 'forms/ReportsCritSetup.html';
						break;
					} else { // fetch the input results and save them
						$DateError = false;
						$success = UpdateCritFields($ReportID, $DateString);
					}
					// read in fields for next form
					$reportname = $_POST['ReportName'];
					if ($_POST['todo']==RPT_BTN_FINISH) { // then finish was pressed
						$title=RPT_ADMSTP1;
						$IncludePage = 'forms/ReportsHome.html';
					} else { // return to criteria form
						$title=RPT_ADMSTP6;
						$IncludePage = 'forms/ReportsCritSetup.html';
					}
					break;
			}
		}
		// reload fields to display form
		$DropDownString = RetrieveReports(); // needed to return to reports manager home
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
					$title=RPT_ADMSTP8;
					$IncludePage = 'forms/ReportsImport.html';
					break;
				}
				// All through and imported successfully, return to reports home page
			case RPT_BTN_BACK:
			default:
				$DropDownString = RetrieveReports();
				$title=RPT_ADMSTP1;
				$IncludePage = 'forms/ReportsHome.html';
		}
	break; // End Step 8

	default:
  } // end switch
} // end else (!isset($_REQUEST['action'])

include ('../../includes/header.inc');
if ($usrMsg) foreach ($usrMsg as $temp) prnmsg($temp['message'],$temp['level']);
include ($IncludePage);
include ('../../includes/footer.inc');
// End main body
?>
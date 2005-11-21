<?php
/* $Revision: 1.1 $ */

// Include functions needed for ReportCreator.php
function RetrieveReports() {
	global $db, $ReportGroups;
	for ($i=0; $i<2; $i++) {
		$sql= "SELECT id, reportname, groupname 
			FROM ".DBReports." 
			WHERE defaultreport = '".$i."' 
			ORDER BY groupname, reportname";
		$Result=DB_query($sql,$db,'','',false,true);
		$TheList='';
		while ($DefaultReports = DB_fetch_array($Result)) $TheList[] = $DefaultReports;
		$OptionList[$i] = '';
		foreach ($ReportGroups as $key=>$value) {
			$OptionList[$i] .= '<optgroup label="'.$value.'" title="'.$key.'">';
			$j=0;
			while ($Temp=$TheList[$j]) {
				if ($Temp['groupname'] == $key) {
					$OptionList[$i] .= '<option value="'.$Temp['id'].'">'.$Temp['reportname'].'</option>';
				}
				$j++;
			}
			$OptionList[$i] .= '</optgroup>';
		}
	}
	return $OptionList;
}

function RetrieveFields($EntryType) {
	global $db, $ReportID;
	$FieldListings['fields'] = '';
	$sql= "SELECT *	FROM ".DBRptFields." 
		WHERE reportid = ".$ReportID." AND entrytype = '".$EntryType."'
		ORDER BY seqnum";
	$Result=DB_query($sql,$db,'','',false,true);
	if (DB_num_rows($Result)>0) {
		while ($FieldValues = DB_fetch_array($Result)) { $FieldListings['lists'][] = $FieldValues; }
	}
	// set the form field defaults
	$FieldListings['defaults']['seqnum'] = '';
	$FieldListings['defaults']['fieldname'] = '';
	$FieldListings['defaults']['displaydesc'] = '';
	$FieldListings['defaults']['visible'] = '';
	$FieldListings['defaults']['columnbreak'] = '';
	$FieldListings['defaults']['params'] = '';
	$FieldListings['defaults']['buttonvalue'] = RPT_BTN_ADDNEW;
	return $FieldListings;
}

function UpdatePageFields($ReportID) {
	global $db;
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
			col8width = ".$_POST['Col8Width']."
		WHERE id =".$ReportID.";";
	$Result=DB_query($sql,$db,'','',false,true);
	return true;
}

function UpdateCritFields($ReportID, $DateString) {
	global $db;
	$sql = "UPDATE ".DBRptFields." SET 
		reportid = '".$ReportID."',
		entrytype = 'dateselect',
		fieldname = '".addslashes($_POST['DateField'])."',
		displaydesc = '".$DateString."',
		params = '".$_POST['DefDate']."'
		WHERE reportid = ".$ReportID." AND entrytype = 'dateselect';";
	$Result=DB_query($sql,$db,'','',false,true);
	// write the truncate long descriptions choice
	$sql = "UPDATE ".DBRptFields." SET 
		reportid = '".$ReportID."',
		entrytype = 'trunclong',
		params = '".$_POST['TruncLongDesc']."',
		displaydesc = ''
		WHERE reportid = ".$ReportID." AND entrytype = 'trunclong';";
	$Result=DB_query($sql,$db,'','',false,true);
	return true;
}

function UpdateDBFields($ReportID) {
	global $db;
	// Test inputs to see if they are valid
	$strTable = addslashes($_POST['Table1']);
	if ($_POST['Table2']) $strTable .= ' INNER JOIN '.addslashes($_POST['Table2']). ' ON '.addslashes($_POST['Table2Criteria']);
	if ($_POST['Table3']) $strTable .= ' INNER JOIN '.addslashes($_POST['Table3']). ' ON '.addslashes($_POST['Table3Criteria']);
	if ($_POST['Table4']) $strTable .= ' INNER JOIN '.addslashes($_POST['Table4']). ' ON '.addslashes($_POST['Table4Criteria']);
	$sql = "SELECT * FROM ".$strTable." LIMIT 1";
	$Result=DB_query($sql,$db,'','',false,false);
	// if we have a row, sql was valid
	if (DB_num_rows($Result)==0) return false;
	$sql = "UPDATE ".DBReports." SET 
			table1 = '".addslashes($_POST['Table1'])."',
			table2 = '".addslashes($_POST['Table2'])."',
			table2criteria = '".addslashes($_POST['Table2Criteria'])."',
			table3 = '".addslashes($_POST['Table3'])."',
			table3criteria = '".addslashes($_POST['Table3Criteria'])."',
			table4 = '".addslashes($_POST['Table4'])."',
			table4criteria = '".addslashes($_POST['Table4Criteria'])."'
		WHERE id =".$ReportID.";";
	$Result=DB_query($sql,$db,'','',false,true);
	return true;
}

function UpdateSequence($EntryType) {
	global $db, $ReportID;
	if (!isset($_POST['Visible'])) $_POST['Visible'] = '0';
	if (!isset($_POST['ColumnBreak'])) $_POST['ColumnBreak'] = '0';
	if (!isset($_POST['Params'])) $_POST['Params'] = '0';
	$sql = "UPDATE ".DBRptFields." SET 
			fieldname = '".addslashes($_POST['FieldName'])."',
			displaydesc = '".addslashes($_POST['DisplayDesc'])."',
			visible = '".$_POST['Visible']."',
			columnbreak = '".$_POST['ColumnBreak']."',
			params = '".$_POST['Params']."'
		WHERE reportid = ".$ReportID." AND entrytype = '".$EntryType."' AND seqnum = ".$_POST['SeqNum'].";";
	$Result=DB_query($sql,$db,'','',false,true);
	return true;
}

function ChangeSequence($SeqNum, $EntryType, $UpDown) {
	global $db, $ReportID;
	// find the id of the row to move
	$sql = "SELECT id FROM ".DBRptFields." 
		WHERE reportid = ".$ReportID." AND entrytype = '".$EntryType."' AND seqnum = ".$SeqNum.";";
	$Result=DB_query($sql,$db,'','',false,true);
	$myrow = DB_fetch_row($Result);
	$OrigID = $myrow[0];
	if ($UpDown=='up') $NewSeqNum = $SeqNum-1; else $NewSeqNum = $SeqNum+1;
	// first move affected sequence to seqnum, then seqnum to new position
	$sql = "UPDATE ".DBRptFields." SET seqnum='".$SeqNum."' 
		WHERE reportid = ".$ReportID." AND entrytype = '".$EntryType."' AND seqnum = ".$NewSeqNum.";";
	$Result=DB_query($sql,$db,'','',false,true);
	$sql = "UPDATE ".DBRptFields." SET seqnum='".$NewSeqNum."' WHERE id = ".$OrigID.";";
	$Result=DB_query($sql,$db,'','',false,true);
	return true;
}

function InsertSequence($SeqNum, $EntryType, $RecordData) {
// This function creates a hole in the sequencing to allow inserting new data
	global $db, $ReportID;
	if (!$SeqNum) $SeqNum = 999; // set equence to max if not entered
	// read the sequence numbers for the given EntryType
	$sql = "SELECT id FROM ".DBRptFields." 
		WHERE reportid = ".$ReportID." AND entrytype = '".$EntryType."' 
		ORDER BY seqnum;";
	$Result=DB_query($sql,$db,'','',false,true);
	while ($FieldID = DB_fetch_array($Result)) { $IDList[] = $FieldID['id']; }
	$NumRows = DB_num_rows($Result);
	if (!$IDList OR ($NumRows < $SeqNum)) { $SeqNum = DB_num_rows($Result) + 1;	}
	if ($SeqNum <= $NumRows) { // shift the fields down to make a sequence hole
		for ($j=$SeqNum-1; $j<$NumRows; $j++) {
			$sql = "UPDATE ".DBRptFields." SET seqnum = ".($j+2)." WHERE id=".$IDList[$j].";";
			$Result=DB_query($sql,$db,'','',false,true);
		}
	}
	if (!isset($RecordData['Visible'])) $Visible = '0'; else $Visible = $RecordData['Visible'];
	if (!isset($RecordData['ColumnBreak'])) $ColumnBreak = '0'; else $ColumnBreak = $RecordData['ColumnBreak'];
	if (!isset($RecordData['Params'])) $Params = '0'; else $Params = $RecordData['Params'];
	$sql = "INSERT INTO ".DBRptFields."
			(reportid, entrytype, seqnum, fieldname, displaydesc, visible, columnbreak, params)
		VALUES (".$ReportID.",'".$EntryType."',".$SeqNum.",'".addslashes($RecordData['FieldName'])."',
			'".addslashes($RecordData['DisplayDesc'])."','".$Visible."','".$ColumnBreak."','".$Params."');";
	$Result=DB_query($sql,$db,'','',false,true);
	return true;
}

function DeleteSequence($SeqNum, $EntryType) {
// This function removes a sequence field and fills the sequence hole left behind
	global $db, $ReportID;
	//  delete the sequence number from the list
	$sql = "DELETE FROM ".DBRptFields." 
		WHERE reportid = ".$ReportID." AND entrytype = '".$EntryType."' AND seqnum = ".$SeqNum.";";
	$Result=DB_query($sql,$db,'','',false,true);
	// read in the remaining sequences and re-number
	$sql = "SELECT id FROM ".DBRptFields." 
		WHERE reportid = ".$ReportID." AND entrytype = '".$EntryType."' 
		ORDER BY seqnum;";
	$Result=DB_query($sql,$db,'','',false,true);
	while ($FieldID = DB_fetch_array($Result)) { $IDList[] = $FieldID['id']; }
	$NumRows = DB_num_rows($Result);
	if ($NumRows >= $SeqNum) {	// then not at end of list re-number sequences
		for ($j=$SeqNum-1; $j<$NumRows; $j++) {
			$sql = "UPDATE ".DBRptFields." SET seqnum = ".($j+1)." WHERE id=".$IDList[$j].";";
			$Result=DB_query($sql,$db,'','',false,true);
		}
	}
	return true;
}

function ValidateField($ReportID, $FieldName, $Description) {
	global $db;
	// This function checks the fieldname and field reference and validates that it is good.
	// fetch the table values to build sql
	$sql = "SELECT table1, table2, table2criteria, table3, table3criteria, table4, table4criteria
		FROM ".DBReports." WHERE id='".$ReportID."'";
	$Result=DB_query($sql,$db,'','',false,true);
	$Prefs = DB_fetch_array($Result);
	// Check for a non-blank entry in the field description or fieldname
	if (strlen($Description)<1 OR strlen($FieldName)<1) return false;
	// Build the table to search, then test inputs to see if they are valid
	$strTable = $Prefs['table1'];
	if ($Prefs['table2']) $strTable .= ' INNER JOIN '.$Prefs['table2']. ' ON '.$Prefs['table2criteria'];
	if ($Prefs['table3']) $strTable .= ' INNER JOIN '.$Prefs['table3']. ' ON '.$Prefs['table3criteria'];
	if ($Prefs['table4']) $strTable .= ' INNER JOIN '.$Prefs['table4']. ' ON '.$Prefs['table4criteria'];
	$sql = "SELECT ".$FieldName." FROM ".$strTable." LIMIT 1";
	$Result=DB_query($sql,$db,'','',false,false);
	// Try to fetch one row, if we have a row, sql was valid
	if (DB_num_rows($Result)<1) return false; else return true;
}

function ReadDefReports() {
	global $ReportGroups;
	$dh = opendir(DefRptPath);
	$i=0;
	while ($DefRpt = readdir($dh)) {
		$pinfo = pathinfo(DefRptPath.$DefRpt);
		if ($pinfo['extension']=='txt') { // then it's a report file read name and type
			$FileLines = file(DefRptPath.$DefRpt);
			foreach ($FileLines as $OneLine) { // find the main reports sql statement, language and execute it
				if (strpos($OneLine,'ReportData:')===0) { // then it's the line we'er after with reportname and groupname
					$GrpPos = strpos($OneLine,"groupname='")+11;
					$GrpName = substr($OneLine, $GrpPos, strpos($OneLine, "',", $GrpPos)-$GrpPos);
					$RptPos = strpos($OneLine,"reportname='")+12;
					$RptName = substr($OneLine, $RptPos, strpos($OneLine, "',", $RptPos)-$RptPos);
					$ReportList[$i]['GrpName'] = $GrpName;
					$ReportList[$i]['RptName'] = $RptName;
					$ReportList[$i]['FileName'] = $pinfo[basename];
					$i++;
				}
			}
		}
	}
	closedir($dh);
	$OptionList = '';
	$LstGroup = '';
	$CloseOptGrp = false;
	$i=0;
	while ($Temp=$ReportList[$i]) {
		if ($Temp['GrpName']<>$LstGroup) { // then it's a new group, close old group and start new group
			if ($LstGroup<>'') echo '</optgroup>';
			$CloseOptGrp = true; // we need to close the last option group
			$LstGroup = $Temp['GrpName'];
			$OptionList .= '<optgroup label="'.$ReportGroups[$Temp['GrpName']].'" title="'.$Temp['GrpName'].'">';
		}
		$GrpMember = $ReportGroups[$Temp['GrpName']];
		if (!$GrpMember) $Temp['GrpName'] = RPT_MISC;
		$OptionList .= '<option value="'.$Temp['FileName'].'">'.$Temp['RptName'].'</option>';
		$i++;
	}
	if ($CloseOptGrp) $OptionList .= '</optgroup>';
	return $OptionList;
}

function ExportReport($ReportID) {
	global $db;
	$crlf = chr(10);
	$CSVOutput = '/* Report Builder Export Tool */'.$crlf;
	$CSVOutput .= 'version:1.0'.$crlf;
	// Fetch the core report data from table reports
	$sql = "SELECT * FROM ".DBReports." WHERE id = ".$ReportID.";";
	$Result=DB_query($sql,$db,'','',false,true);
	$myrow = DB_fetch_assoc($Result);
	// Fetch the language dependent db entries
	$ReportName = $myrow['reportname'];
	$Title1Desc = $myrow['title1desc'];
	$Title2Desc = $myrow['title2desc'];
	// Enter some export file info for language translation
	$CSVOutput .= '/* Report Name: '.$ReportName.' */'.$crlf;
	$CSVOutput .= '/* Export File Generated: : '.date('Y-m-d h:m:s', time()).' */'.$crlf.$crlf.$crlf;
	$CSVOutput .= '/* Language Fields. */'.$crlf;
	$CSVOutput .= '/* Only modify the language portion between the single quotes after the colon. */'.$crlf.$crlf;
	$CSVOutput .= '/* Report Name and Title Information: */'.$crlf;
	$CSVOutput .= "ReportName:'".addslashes($ReportName)."'".$crlf;
	$CSVOutput .= "Title1Desc:'".addslashes($Title1Desc)."'".$crlf;
	$CSVOutput .= "Title2Desc:'".addslashes($Title2Desc)."'".$crlf.$crlf;
	// Now add the report fields
	$CSVOutput .= '/* Report Field Description Information: */'.$crlf;
	$sql = "SELECT * FROM ".DBRptFields." WHERE reportid = ".$ReportID." ORDER BY entrytype, seqnum;";
	$Result=DB_query($sql,$db,'','',false,true);
	$i=0;
	while ($FieldRows = DB_fetch_assoc($Result)) {
		if ($FieldRows['entrytype']<>'dateselect' AND $FieldRows['entrytype']<>'trunclong') {
			$CSVOutput .= "FieldDesc".$i.":'".addslashes($FieldRows['displaydesc'])."'".$crlf;
		}
		$sql = 'FieldData'.$i.':';
		foreach ($FieldRows as $key=>$value) {
			if ($key<>'id' AND $key<>'reportid') $sql .= $key."='".addslashes($value)."', ";
		}
		$sql = substr($sql,0,-2).";"; // Strip the last comma and space and add a semicolon
		$FieldData[$i] = $sql;
		$i++;
	}
	$CSVOutput .= '/* End of language fields. */'.$crlf.$crlf;
	$CSVOutput .= '/* DO NOT EDIT BELOW THIS LINE! */'.$crlf.$crlf.$crlf;
	$CSVOutput .= '/* SQL report data. */'.$crlf;
	// Build the report sql string
	$RptData = 'ReportData:';
	foreach ($myrow as $key=>$value) if ($key<>'id') $RptData .= $key."='".addslashes($value)."', ";
	$RptData = substr($RptData,0,-2).";"; // Strip the last comma and space and add a semicolon
	$CSVOutput .= $RptData.$crlf.$crlf;
	$CSVOutput .= '/* SQL field data. */'.$crlf;
	for ($i=0; $i<count($FieldData); $i++) $CSVOutput .= $FieldData[$i].$crlf;
	$CSVOutput .= $crlf;
	$CSVOutput .= '/* End of Export File */'.$crlf;
	// export the file 
	$FileSize = strlen($CSVOutput);
	header("Content-type: application/txt");
	header("Content-disposition: attachment; filename=".preg_replace('/ /','',$ReportName).".rpt.txt; size=".$FileSize);
	// These next two lines are needed for MSIE
    header('Pragma: cache');
    header('Cache-Control: public, must-revalidate, max-age=0');
	print $CSVOutput;
	exit();  
}

function ImportReport($RptName) {
	global $db;
	if ($_POST['RptFileName']<>'') { // then a locally stored report was chosen
		$arrSQL = file(DefRptPath.$_POST['RptFileName']);
	} else { // check for an uploaded file
		$Rtn['result'] = 'error';
		if ($_FILES['reportfile']['error']) { // php error uploading file
			switch ($_FILES['reportfile']['error']) {
				case '1': $Rtn['message'] = RPT_IMP_ERMSG1; break;
				case '2': $Rtn['message'] = RPT_IMP_ERMSG2; break;
				case '3': $Rtn['message'] = RPT_IMP_ERMSG3; break;
				case '4': $Rtn['message'] = RPT_IMP_ERMSG4; break;
				default:  $Rtn['message'] = RPT_IMP_ERMSG5.$_FILES['reportfile']['error'].'.';
			}
		} elseif (!is_uploaded_file($_FILES['reportfile']['tmp_name'])) { // file uploaded
			$Rtn['message'] = RPT_IMP_ERMSG10;
		} elseif (strpos($_FILES['reportfile']['type'],'text') === false)  { // not a text file, error
			$Rtn['message'] = RPT_IMP_ERMSG6;
		} elseif ($_FILES['reportfile']['size']==0) { // report contains no data, error
			$Rtn['message'] = RPT_IMP_ERMSG7;
		} else { // passed all error checking, read file and reset error message
			$arrSQL = file($_FILES['reportfile']['tmp_name']);
			$Rtn['result']='';
		}
		if ($Rtn['result']=='error') return $Rtn;
	}
	
	foreach ($arrSQL as $sql) { // find the report translated reportname and title information
		if (strpos($sql,'ReportName:')===0) $ReportName = substr(trim($sql),12,-1);
		if (strpos($sql,'Title1Desc:')===0) $Title1Desc = substr(trim($sql),12,-1);
		if (strpos($sql,'Title2Desc:')===0) $Title2Desc = substr(trim($sql),12,-1);
	}
	// check for valid file, duplicate report name
	if ($RptName=='') $RptName = $ReportName; // then no report was entered use reportname from file
	$sql= "SELECT id FROM ".DBReports." WHERE reportname='".addslashes($RptName)."';";
	$Result=DB_query($sql,$db,'','',false,true);
	if (DB_num_rows($Result)>0) { // the report name already exists, error 
		$Rtn['result'] = 'error';
		$Rtn['message'] = RPT_REPDUP;
		return $Rtn;
	}
	// Find the line with the table reports element, needs to be written first
	$ValidReportSQL = false;
	foreach ($arrSQL as $sql) { // find the main reports sql statement, language and execute it
		if (strpos($sql,'ReportData:')===0) {
			$sql="INSERT INTO ".DBReports." SET ".substr(trim($sql),11);
			$Result=DB_query($sql,$db,'','',false,true);
			$ValidReportSQL = true;
		}
	}
	if (!$ValidReportSQL) { // no valid report sql statement found in the text file, error
		$Rtn['result'] = 'error';
		$Rtn['message'] = RPT_IMP_ERMSG8;
		return $Rtn;
	}
	// fetch the id of the row inserted 
	$ReportID = DB_Last_Insert_ID($db,DBReports,'id');
	// update the translated report name and title fields into the newly imported report
	$sql = "UPDATE ".DBReports." SET 
			reportname = '".$RptName."', 
			title1desc = '".$Title1Desc."', 
			title2desc = '".$Title2Desc."' 
		WHERE id = ".$ReportID.";";
	$Result=DB_query($sql,$db,'','',false,true);
	foreach ($arrSQL as $sql) { // fetch the translations for the field descriptions
		if (strpos($sql,'FieldDesc')===0) { // then it's a field description, find the index and save
			$sql = trim($sql);
			$FldIndex = substr($sql,9,strpos($sql,':')-9);
			$Language[$FldIndex] = substr($sql,strpos($sql,':')+2,-1);
		}
	}
	foreach ($arrSQL as $sql) {
		if (strpos($sql,'FieldData')===0) { // a valid field, write it
			$sql = trim($sql);
			$FldIndex = substr($sql,9,strpos($sql,':')-9);
			$sql="INSERT INTO ".DBRptFields." SET ".substr($sql,strpos($sql,':')+1);
			$Result=DB_query($sql,$db,'','',false,true);
			$FieldID = DB_Last_Insert_ID($db,DBRptFields, 'id');
			if ($FieldID<>0) { // A field was successfully written update the report id
				$tsql = "UPDATE ".DBRptFields." SET 
						reportid = '".$ReportID."', 
						displaydesc = '".$Language[$FldIndex]."'
					WHERE id = ".$FieldID.";";
				$Result=DB_query($tsql,$db,'','',false,true);
			}
		}
	}
	$Rtn['result'] = 'success';
	$Rtn['message'] = $RptName.RPT_IMP_ERMSG9;
	return $Rtn;
}

?>
<?php

// Test Plan Results Entry.

require(__DIR__ . '/includes/session.php');

$ViewTopic = 'QualityAssurance'; /* ?????????? */
$BookMark = 'TestPlanResults';
$Title = __('Test Plan Results');
include('includes/header.php');

if (isset($_POST['FromDate'])){$_POST['FromDate'] = ConvertSQLDate($_POST['FromDate']);}
if (isset($_POST['ToDate'])){$_POST['ToDate'] = ConvertSQLDate($_POST['ToDate']);}

if (isset($_GET['SelectedSampleID'])){
	$SelectedSampleID =mb_strtoupper($_GET['SelectedSampleID']);
} elseif(isset($_POST['SelectedSampleID'])){
	$SelectedSampleID =mb_strtoupper($_POST['SelectedSampleID']);
}

if (!isset($_POST['FromDate'])){
	$_POST['FromDate']=Date(($_SESSION['DefaultDateFormat']), strtotime(date($_SESSION['DefaultDateFormat']) . ' - 15 days'));
}
if (!isset($_POST['ToDate'])){
	$_POST['ToDate'] = Date($_SESSION['DefaultDateFormat']);
}

$Errors = array();

echo '<p class="page_title_text"><img src="'.$RootPath.'/css/'.$Theme.'/images/maintenance.png" title="' . __('Search') . '" alt="" />' . ' ' . $Title . '</p>';

if (isset($_GET['CopyResults']) OR isset($_POST['CopyResults'])) {
	if (!isset($_POST['CopyToSampleID']) OR $_POST['CopyToSampleID']=='' OR !isset($_POST['Copy'])) {
		echo '<form action="' . htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') . '" method="post">
			<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />
			<input type="hidden" name="SelectedSampleID" value="' . $SelectedSampleID . '" />
			<input type="hidden" name="CopyResults" value="CopyResults" />';
		if (isset($_POST['ResetPart'])) {
			unset($SelectedStockItem);
		}

		if (isset($SampleID) AND $SampleID != '') {
			if (!is_numeric($SampleID)) {
				prnMsg(__('The Sample ID entered') . ' <U>' . __('MUST') . '</U> ' . __('be numeric'), 'error');
				unset($SampleID);
			} else {
				echo __('Sample ID') . ' - ' . $SampleID;
			}
		}
		if (!Is_Date($_POST['FromDate'])) {
			$InputError = 1;
			prnMsg(__('Invalid From Date'),'error');
			$_POST['FromDate']=Date(($_SESSION['DefaultDateFormat']), strtotime($UpcomingDate . ' - 15 days'));
		}
		if (!Is_Date($_POST['ToDate'])) {
			$InputError = 1;
			prnMsg(__('Invalid To Date'),'error');
			$_POST['ToDate'] = Date($_SESSION['DefaultDateFormat']);
		}
		if (isset($_POST['SearchParts'])) {
			if ($_POST['Keywords'] AND $_POST['StockCode']) {
				prnMsg(__('Stock description keywords have been used in preference to the Stock code extract entered'), 'info');
			}
			if ($_POST['Keywords']) {
				//insert wildcard characters in spaces
				$SearchString = '%' . str_replace(' ', '%', $_POST['Keywords']) . '%';
				$SQL = "SELECT stockmaster.stockid,
						stockmaster.description,
						stockmaster.decimalplaces,
						SUM(locstock.quantity) as qoh,
						stockmaster.units,
					FROM stockmaster INNER JOIN locstock
					ON stockmaster.stockid = locstock.stockid
					INNER JOIN locationusers ON locationusers.loccode = locstock.loccode
							AND locationusers.userid='" .  $_SESSION['UserID'] . "'
							AND locationusers.canview=1
					WHERE stockmaster.description " . LIKE  . " '" . $SearchString ."'
					AND stockmaster.categoryid='" . $_POST['StockCat'] . "'
					GROUP BY stockmaster.stockid,
						stockmaster.description,
						stockmaster.decimalplaces,
						stockmaster.units
					ORDER BY stockmaster.stockid";
			} elseif ($_POST['StockCode']) {
				$SQL = "SELECT stockmaster.stockid,
						stockmaster.description,
						stockmaster.decimalplaces,
						SUM(locstock.quantity) AS qoh,
						stockmaster.units
					FROM stockmaster INNER JOIN locstock
						ON stockmaster.stockid = locstock.stockid
					INNER JOIN locationusers ON locationusers.loccode = locstock.loccode
							AND locationusers.userid='" .  $_SESSION['UserID'] . "'
							AND locationusers.canview=1
					WHERE stockmaster.stockid " . LIKE  . " '%" . $_POST['StockCode'] . "%'
					AND stockmaster.categoryid='" . $_POST['StockCat'] . "'
					GROUP BY stockmaster.stockid,
						stockmaster.description,
						stockmaster.decimalplaces,
						stockmaster.units
					ORDER BY stockmaster.stockid";
			} elseif (!$_POST['StockCode'] AND !$_POST['Keywords']) {
				$SQL = "SELECT stockmaster.stockid,
						stockmaster.description,
						stockmaster.decimalplaces,
						SUM(locstock.quantity) AS qoh,
						stockmaster.units
					FROM stockmaster INNER JOIN locstock ON stockmaster.stockid = locstock.stockid
					INNER JOIN locationusers ON locationusers.loccode = locstock.loccode
							AND locationusers.userid='" .  $_SESSION['UserID'] . "'
							AND locationusers.canview =1
					WHERE stockmaster.categoryid='" . $_POST['StockCat'] . "'
					GROUP BY stockmaster.stockid,
						stockmaster.description,
						stockmaster.decimalplaces,
						stockmaster.units
					ORDER BY stockmaster.stockid";
			}

			$ErrMsg = __('No stock items were returned by the SQL because');
			$StockItemsResult = DB_query($SQL, $ErrMsg);
		}

		if (true or !isset($LotNumber) or $LotNumber == "") { //revisit later, right now always show all inputs
			echo '<table class="selection"><tr><td>';
			if (isset($SelectedStockItem)) {
				echo __('For the part') . ':<b>' . $SelectedStockItem . '</b> ' . __('and') . ' <input type="hidden" name="SelectedStockItem" value="' . $SelectedStockItem . '" />';
			}
			echo __('Lot Number') . ': <input name="LotNumber" autofocus="autofocus" maxlength="20" size="12" value="' . $LotNumber . '"/> ' . __('Sample ID') . ': <input name="SampleID" maxlength="10" size="10" value="' . $SampleID . '"/> ';
			echo __('From Sample Date') . ': <input name="FromDate" size="10" type="date" value="' . FormatDateForSQL($_POST['FromDate']) . '" /> ' . __('To Sample Date') . ': <input name="ToDate" size="10" type="date" value="' . FormatDateForSQL($_POST['ToDate']) . '" /> ';
			echo '<input type="submit" name="SearchSamples" value="' . __('Search Samples') . '" /></td>
				</tr>
				</table>';
		}
		$SQL = "SELECT categoryid,
					categorydescription
				FROM stockcategory
				ORDER BY categorydescription";
		$Result1 = DB_query($SQL);
		echo '
				<table class="selection">
				<tr>
					<td>';
		echo __('To search for Pick Lists for a specific part use the part selection facilities below') . '</td></tr>';
		echo '<tr>
				<td>' . __('Select a stock category') . ':<select name="StockCat">';
		while ($MyRow1 = DB_fetch_array($Result1)) {
			if (isset($_POST['StockCat']) and $MyRow1['categoryid'] == $_POST['StockCat']) {
				echo '<option selected="selected" value="' . $MyRow1['categoryid'] . '">' . $MyRow1['categorydescription'] . '</option>';
			} else {
				echo '<option value="' . $MyRow1['categoryid'] . '">' . $MyRow1['categorydescription'] . '</option>';
			}
		}
		echo '</select></td>
				<td>' . __('Enter text extracts in the') . ' <b>' . __('description') . '</b>:</td>
				<td><input type="text" name="Keywords" size="20" maxlength="25" /></td>
			</tr>
			<tr>
				<td></td>
				<td><b>' . __('OR') . ' </b>' . __('Enter extract of the') . '<b> ' . __('Stock Code') . '</b>:</td>
				<td><input type="text" name="StockCode" size="15" maxlength="18" /></td>
			</tr>
			<tr>
				<td colspan="3">
					<div class="centre">
						<input type="submit" name="SearchParts" value="' . __('Search Parts Now') . '" />
						<input type="submit" name="ResetPart" value="' . __('Show All') . '" />
					</div>
				</td>
			</tr>
			</table>
			<br />
			<br />';

		if (isset($StockItemsResult)) {
			echo '<table class="selection">
				<thead>
					<tr>
						<th class="SortedColumn">' . __('Code') . '</th>
						<th class="SortedColumn">' . __('Description') . '</th>
						<th class="SortedColumn">' . __('On Hand') . '</th>
						<th class="SortedColumn">' . __('Units') . '</th>
					</tr>
				</thead>
				<tbody>';

			while ($MyRow = DB_fetch_array($StockItemsResult)) {
				echo '<tr class="striped_row">
					<td><input type="submit" name="SelectedStockItem" value="' . $MyRow['stockid'] . '"</td>
					<td>' . $MyRow['description'] . '</td>
					<td class="number">' . locale_number_format($MyRow['qoh'],$MyRow['decimalplaces']) . '</td>
					<td>' . $MyRow['units'] . '</td>
					</tr>';
			}
			//end of while loop
			echo '</tbody></table>';
		}
		//end if stock search results to show
		else {
			$FromDate = FormatDateForSQL($_POST['FromDate']);
			$ToDate = FormatDateForSQL($_POST['ToDate']);
			if (isset($LotNumber) AND $LotNumber != '') {
				$SQL = "SELECT sampleid,
								prodspeckey,
								description,
								lotkey,
								identifier,
								createdby,
								sampledate,
								cert
							FROM qasamples
							LEFT OUTER JOIN stockmaster on stockmaster.stockid=qasamples.prodspeckey
							WHERE lotkey='" . filter_number_format($LotNumber) . "'
							AND sampleid<>'" . $SelectedSampleID . "'";
			} elseif (isset($SampleID) AND $SampleID != '') {
				$SQL = "SELECT sampleid,
								prodspeckey,
								description,
								lotkey,
								identifier,
								createdby,
								sampledate,
								cert
							FROM qasamples
							LEFT OUTER JOIN stockmaster on stockmaster.stockid=qasamples.prodspeckey
							WHERE sampleid='" . filter_number_format($SampleID) . "'
							AND sampleid<>'" . $SelectedSampleID . "'";
			} else {
				if (isset($SelectedStockItem)) {
					$SQL = "SELECT sampleid,
								prodspeckey,
								description,
								lotkey,
								identifier,
								createdby,
								sampledate,
								cert
							FROM qasamples
							INNER JOIN stockmaster on stockmaster.stockid=qasamples.prodspeckey
							WHERE stockid='" . $SelectedStockItem . "'
							AND sampledate>='$FromDate'
							AND sampledate <='$ToDate'
							AND sampleid<>'" . $SelectedSampleID . "'";
				} else {
					$SQL = "SELECT sampleid,
								prodspeckey,
								description,
								lotkey,
								identifier,
								createdby,
								sampledate,
								comments,
								cert
							FROM qasamples
							LEFT OUTER JOIN stockmaster on stockmaster.stockid=qasamples.prodspeckey
							WHERE sampledate>='$FromDate'
							AND sampledate <='$ToDate'
							AND sampleid<>'" . $SelectedSampleID . "'";
				} //no stock item selected
			} //end no sample id selected
			$ErrMsg = __('No QA samples were returned by the SQL because');
			$SampleResult = DB_query($SQL, $ErrMsg);
			if (DB_num_rows($SampleResult) > 0) {

				echo '<table cellpadding="2" width="90%" class="selection">
					<thead>
						<tr>
							<th class="SortedColumn">' . __('Copy Results') . '</th>
							<th class="SortedColumn">' . __('Enter Results') . '</th>
							<th class="SortedColumn">' . __('Specification') . '</th>
							<th class="SortedColumn">' . __('Description') . '</th>
							<th class="SortedColumn">' . __('Lot / Serial') . '</th>
							<th class="SortedColumn">' . __('Identifier') . '</th>
							<th class="SortedColumn">' . __('Created By') . '</th>
							<th class="SortedColumn">' . __('Sample Date') . '</th>
							<th class="SortedColumn">' . __('Comments') . '</th>
							<th class="SortedColumn">' . __('Cert Allowed') . '</th>
						</tr>
					</thead>
					<tbody>';

				while ($MyRow = DB_fetch_array($SampleResult)) {
					$ModifySampleID = $RootPath . '/TestPlanResults.php?SelectedSampleID=' . $MyRow['sampleid'];
					$Copy = '<a href="' . htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') . '?SelectedSampleID=' . $SelectedSampleID .'&CopyToSampleID=' . $MyRow['sampleid'] .'">' . __('Copy to This Sample') .'</a>';
					$FormatedSampleDate = ConvertSQLDate($MyRow['sampledate']);

					if ($MyRow['cert']==1) {
						$CertAllowed='<a target="_blank" href="'. $RootPath . '/PDFCOA.php?LotKey=' .$MyRow['lotkey'] .'&ProdSpec=' .$MyRow['prodspeckey'] .'">' . __('Yes') . '</a>';
					} else {
						$CertAllowed=__('No');
					}

					echo '<tr class="striped_row">
							<td><input type="radio" name="CopyToSampleID" value="' . $MyRow['sampleid'] .'">
							<td><a target="blank" href="' . $ModifySampleID . '">' . str_pad($MyRow['sampleid'],10,'0',STR_PAD_LEFT) . '</a></td>
							<td>' . $MyRow['prodspeckey'] . '</td>
							<td>' . $MyRow['description'] . '</td>
							<td>' . $MyRow['lotkey'] . '</td>
							<td>' .  $MyRow['identifier']  . '</td>
							<td>' .  $MyRow['createdby']  . '</td>
							<td class="date">' . $FormatedSampleDate . '</td>
							<td>' . $MyRow['comments'] . '</td>
							<td>' . $CertAllowed . '</td>
							</tr>';
				} //end of while loop
				echo '</tbody></table>';
			} // end if Pick Lists to show
		}
		echo '</div>' . __('Override existing Test values?') .
			 '<input type="checkbox" name="OverRide"><input type="submit" name="Copy" value="' . __('Copy') . '" />
			  </form>';
		include('includes/footer.php');
		exit();
	} else {
		$SQL = "SELECT sampleresults.testid,
						sampleresults.defaultvalue,
						sampleresults.targetvalue,
						sampleresults.rangemin,
						sampleresults.rangemax,
						sampleresults.testvalue,
						sampleresults.testdate,
						sampleresults.testedby,
						sampleresults.comments,
						sampleresults.isinspec,
						sampleresults.showoncert,
						sampleresults.showontestplan,
						prodspeckey,
						type
					FROM sampleresults
					INNER JOIN qasamples ON qasamples.sampleid=sampleresults.sampleid
					INNER JOIN qatests ON qatests.testid=sampleresults.testid
					WHERE sampleresults.sampleid='" .$SelectedSampleID. "'";
		$Msg = __('Test Results have been copied to sample') . ' ' . $_POST['CopyToSampleID']  . ' from sample' . ' ' . $SelectedSampleID ;
		$ErrMsg = __('The insert of the test results failed because');
		$Result = DB_query($SQL, $ErrMsg);

		while ($MyRow = DB_fetch_array($Result)) {
			$Result2 = DB_query("SELECT count(testid) FROM prodspecs
						WHERE testid = '".$MyRow['testid']."'
						AND keyval='".$MyRow['prodspeckey']."'");
			$MyRow2 = DB_fetch_row($Result2);
			if($MyRow2[0]>0) {
				$ManuallyAdded=0;
			} else {
				$ManuallyAdded=1;
			}
			$Result2 = DB_query("SELECT resultid, targetvalue,rangemin, rangemax FROM sampleresults
						WHERE testid = '".$MyRow['testid']."'
						AND sampleid='".$_POST['CopyToSampleID']."'");
			$MyRow2 = DB_fetch_array($Result2);
			$IsInSpec=1;
			$CompareVal='yes';
			$CompareRange='no';
			if ($MyRow['targetvalue']=='') {
				$CompareVal='no';
			}
			if ($MyRow['type']==4) {
				//$RangeDisplay=$MyRow['rangemin'] . '-'  . $MyRow['rangemax'] . ' ' . $MyRow['units'];
				$RangeDisplay='';
				if ($MyRow['rangemin'] > '' OR $MyRow['rangemax'] > '') {
					if ($MyRow['rangemin'] > '' AND $MyRow['rangemax'] == '') {
						$RangeDisplay='> ' . $MyRow['rangemin'] . ' ' . $MyRow['units'];
					} elseif ($MyRow['rangemin']== '' AND $MyRow['rangemax'] > '') {
						$RangeDisplay='< ' . $MyRow['rangemax'] . ' ' . $MyRow['units'];
					} else {
						$RangeDisplay=$MyRow['rangemin'] . ' - ' . $MyRow['rangemax'] . ' ' . $MyRow['units'];
					}
					$CompareRange='yes';
				}

			} else {
				$RangeDisplay='&nbsp;';
				$CompareRange='no';
			}
			if ($MyRow['type']==3) {
				$CompareVal='no';
			}
			//var_dump($CompareVal); var_dump($CompareRange);
			if ($CompareVal=='yes'){
				if ($CompareRange=='yes'){
					if ($MyRow2['rangemin'] > '' AND $MyRow2['rangemax'] > '') {
						if (($MyRow['testvalue']<>$MyRow2['targetvalue']) AND ($MyRow['testvalue']<$MyRow2['rangemin'] OR $MyRow['testvalue'] > $MyRow2['rangemax'])) {
							$IsInSpec=0;
						}
					} elseif ($MyRow2['rangemin'] > '' AND $MyRow2['rangemax'] == '') {
						if (($MyRow['testvalue']<>$MyRow2['targetvalue']) AND ($MyRow['testvalue']<=$MyRow2['rangemin'])) {
							$IsInSpec=0;
						}
					} elseif ($MyRow2['rangemin'] == '' AND $MyRow2['rangemax'] > '') {
						if (($MyRow['testvalue']<>$MyRow2['targetvalue']) AND ($MyRow['testvalue'] >= $MyRow2['rangemax'])) {
							$IsInSpec=0;
						}
					}
					//var_dump($MyRow['testvalue']); var_dump($MyRow2['targetvalue']); var_dump($MyRow2['rangemin']); var_dump($MyRow2['rangemax']);
				} else {
					if (($MyRow['testvalue']<>$MyRow2['targetvalue'])) {
						$IsInSpec=0;
					}
				}
			}
			if($MyRow2[0]>'') {
				//test already exists on CopyToSample
				if ($_POST['OverRide']=='on') {
					$UpdSQLl = "UPDATE sampleresults
								SET	testvalue='" .$MyRow['testvalue']. "',
									testdate='" .$MyRow['testdate']. "',
									testedby='" .$MyRow['testedby']. "',
									isinspec='" .$IsInSpec. "'
								WHERE sampleid='" . $_POST['CopyToSampleID'] ."'
								AND resultid='".$MyRow2[0]."'";
					$Msg = __('Test Results have been overwritten to sample') . ' ' . $_POST['CopyToSampleID']  . __(' from sample') . ' ' . $SelectedSampleID  . __(' for test ') . $MyRow['testid'];
					$ErrMsg = __('The insert of the test results failed because');
					$UpdResult = DB_query($UpdSQLl, $ErrMsg);
					prnMsg($Msg , 'success');
				} else {
					$Msg = __('Test Results have NOT BEEN overwritten for Result ID ') . $MyRow2[0];
					prnMsg($Msg , 'warning');
				}
			} else {
				//Need to insert the test and results
				$InsSQL = "INSERT INTO sampleresults
							(sampleid,
							testid,
							defaultvalue,
							targetvalue,
							testvalue,
							rangemin,
							rangemax,
							showoncert,
							showontestplan,
							comments,
							manuallyadded,
							testedby,
							testdate,
							isinspec)
						VALUES ( '"  . $_POST['CopyToSampleID'] . "',
								'"  . $MyRow['testid'] . "',
								'"  . $MyRow['defaultvalue'] . "',
								'"  . $MyRow['targetvalue']. "',
								'"  . $MyRow['testvalue']. "',
								'"  . $MyRow['rangemin'] . "',
								'"  . $MyRow['rangemax'] . "',
								'"  . $MyRow['showoncert'] . "',
								'"  . $MyRow['showontestplan'] . "',
								'"  . $MyRow['comments'] . "',
								'"  . $ManuallyAdded . "',
								'"  . $MyRow['testedby'] . "',
								'"  . $MyRow['testdate'] . "',
								'"  . $IsInSpec . "'
								)";
				$Msg = __('Test Results have been copied to') . ' ' . $_POST['CopyToSampleID'] . ' ' . __('from') . ' ' . $SelectedSampleID . ' ' . __('for') . ' ' . $MyRow['testid'];
				$ErrMsg = __('The insert of the test results failed because');
				$insresult = DB_query($InsSQL, $ErrMsg);
				prnMsg($Msg , 'success');
			}
		} //while loop on myrow
		$SelectedSampleID=$_POST['CopyToSampleID'];
		unset($_GET['CopyResults']);
		unset($_POST['CopyResults']);
	} //else
} //CopySpec

if (isset($_GET['ListTests'])) {
	$SQL = "SELECT qatests.testid,
				name,
				method,
				units,
				type,
				numericvalue,
				qatests.defaultvalue
			FROM qatests
			LEFT JOIN sampleresults
			ON sampleresults.testid=qatests.testid
			AND sampleresults.sampleid='".$SelectedSampleID."'
			WHERE qatests.active='1'
			AND sampleresults.sampleid IS NULL";
	$Result = DB_query($SQL);
	echo '<form method="post" action="' . htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') . '">
		<div>
		<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />
		<table class="selection">
		<thead>
			<tr>
				<th class="SortedColumn">' . __('Add') . '</th>
				<th class="SortedColumn">' . __('Name') . '</th>
				<th class="SortedColumn">' . __('Method') . '</th>
				<th class="SortedColumn">' . __('Units') . '</th>
				<th>' . __('Possible Values') . '</th>
				<th>' . __('Target Value') . '</th>
				<th>' . __('Range Min') . '</th>
				<th>' . __('Range Max') . '</th>
			</tr>
		</thead>
		<tbody>';

	$x=0;
	while ($MyRow=DB_fetch_array($Result)) {

		$x++;
		$Class='';
		$RangeMin='';
		$RangeMax='';
		if ($MyRow['numericvalue'] == 1) {
			$IsNumeric = __('Yes');
			$Class="number";
		} else {
			$IsNumeric = __('No');
		}

		switch ($MyRow['type']) {
			case 0: //textbox
		 		$TypeDisp='Text Box';
		 		break;
			case 1: //select box
		 		$TypeDisp='Select Box';
				break;
			case 2: //checkbox
				$TypeDisp='Check Box';
				break;
			case 3: //datebox
				$TypeDisp='Date Box';
				$Class="date";
				break;
			case 4: //range
				$TypeDisp='Range';
				$RangeMin='<input  class="' .$Class. '" type="text" name="AddRangeMin' .$x.'" />';
				$RangeMax='<input  class="' .$Class. '" type="text" name="AddRangeMax' .$x.'" />';
				break;
		} //end switch
		echo '<tr class="striped_row">
				<td><input type="checkbox" name="AddRow' .$x.'"><input type="hidden" name="AddTestID' .$x.'" value="' .$MyRow['testid']. '"></td>
				<td>', $MyRow['name'], '</td>
				<td>', $MyRow['method'], '</td>
				<td>', $MyRow['units'], '</td>
				<td>', $MyRow['defaultvalue'], '</td>
				<td><input  class="' .$Class. '" type="text" name="AddTargetValue' .$x.'" /></td>
				<td>', $RangeMin, '</td>
				<td>', $RangeMax, '</td>
			</tr>';

	} //END WHILE LIST LOOP

	echo '</tbody></table><br />
			<div class="centre">
				<input type="hidden" name="SelectedSampleID" value="' . $SelectedSampleID . '" />
				<input type="hidden" name="AddTestsCounter" value="' . $x . '" />
				<input type="submit" name="AddTests" value="' . __('Add') . '" />
			</div>
		</div>
		</form>';
	include('includes/footer.php');
	exit();
}  //ListTests
if (isset($_POST['AddTests'])) {
	for ($i=0;$i<=$_POST['AddTestsCounter'];$i++){
		if ($_POST['AddRow' .$i]=='on') {
				if ($_POST['AddRangeMin' .$i]=='') {
				$AddRangeMin="NULL";
			} else {
				$AddRangeMin="'" . $_POST['AddRangeMin' .$i] . "'";
			}
			if ($_POST['AddRangeMax' .$i]=='') {
				$AddRangeMax="NULL";
			} else {
				$AddRangeMax="'" . $_POST['AddRangeMax' .$i] . "'";
			}
			$SQL = "INSERT INTO sampleresults
							(sampleid,
							testid,
							defaultvalue,
							targetvalue,
							rangemin,
							rangemax,
							showoncert,
							showontestplan,
							manuallyadded)
						SELECT '"  . $SelectedSampleID . "',
								testid,
								defaultvalue,
								'"  .  $_POST['AddTargetValue' .$i] . "',
								"  . $AddRangeMin . ",
								"  . $AddRangeMax . ",
								showoncert,
								'1',
								'1'
						FROM qatests WHERE testid='" .$_POST['AddTestID' .$i]. "'";
			$Msg = __('A Sample Result record has been added for Test ID') . ' ' . $_POST['AddTestID' .$i]  . ' for ' . ' ' . $KeyValue ;
			$ErrMsg = __('The insert of the Sample Result failed because');
			$Result = DB_query($SQL, $ErrMsg);
			prnMsg($Msg , 'success');
		} //if on
	} //for
} //AddTests

if (isset($_POST['submit'])) {

	//initialise no input errors assumed initially before we test
	$InputError = 0;

	for ($i=1;$i<=$_POST['TestResultsCounter'];$i++){
		$IsInSpec=1;
		//var_dump($_POST['CompareVal' .$i]); var_dump($_POST['CompareRange' .$i]);
		if ($_POST['CompareVal' .$i]=='yes'){
			if ($_POST['CompareRange' .$i]=='yes'){
				//if (($_POST['TestValue' .$i]<>$_POST['ExpectedValue' .$i]) AND ($_POST['TestValue' .$i]<$_POST['MinVal' .$i] OR $_POST['TestValue' .$i] > $_POST['MaxVal' .$i])) {
				//	$IsInSpec=0;
				//}
				if ($_POST['MinVal' .$i] > '' AND $_POST['MaxVal' .$i] > '') {
					if (($_POST['TestValue' .$i]<>$_POST['ExpectedValue' .$i]) AND ($_POST['TestValue' .$i]<$_POST['MinVal' .$i] OR $_POST['TestValue' .$i] > $_POST['MaxVal' .$i])) {
						//echo "one";
						$IsInSpec=0;
					}
				} elseif ($_POST['MinVal' .$i] > '' AND $_POST['MaxVal' .$i] == '') {
					if (($_POST['TestValue' .$i]<>$_POST['ExpectedValue' .$i]) AND ($_POST['TestValue' .$i] <= $_POST['MinVal' .$i])) {
						//echo "two";
						$IsInSpec=0;
					}
				} elseif ($_POST['MinVal' .$i] == '' AND $_POST['MaxVal' .$i] > '') {
					if (($_POST['TestValue' .$i]<>$_POST['ExpectedValue' .$i]) AND ($_POST['TestValue' .$i] >= $_POST['MaxVal' .$i])) {
						//echo "three";
						$IsInSpec=0;
					}
				}
				//echo "four";
				//var_dump($_POST['TestValue' .$i]); var_dump($_POST['ExpectedValue' .$i]); var_dump($_POST['MinVal' .$i]); var_dump($_POST['MaxVal' .$i]); var_dump($IsInSpec);
			} else {
				if (($_POST['TestValue' .$i]<>$_POST['ExpectedValue' .$i])) {
					$IsInSpec=0;
				}
			}
		}
		$SQL = "UPDATE sampleresults SET testedby='".  $_POST['TestedBy' .$i] . "',
										testdate='". FormatDateForSQL($_POST['TestDate' .$i]) . "',
										testvalue='".  $_POST['TestValue' .$i] . "',
										showoncert='".  $_POST['ShowOnCert' .$i] . "',
										isinspec='".  $IsInSpec . "'
						WHERE resultid='".  $_POST['ResultID' .$i] . "'";

		$Msg = __('Sample Results were updated for Result ID') . ' ' . $_POST['ResultID' .$i] ;
		$ErrMsg = __('The updated of the sampleresults failed because');
		$Result = DB_query($SQL, $ErrMsg);
		prnMsg($Msg , 'success');
	} //for
	//check to see all values are in spec or at least entered
	$Result = DB_query("SELECT count(sampleid) FROM sampleresults
						WHERE sampleid = '".$SelectedSampleID."'
						AND showoncert='1'
						AND testvalue=''");
	$MyRow = DB_fetch_row($Result);
	if($MyRow[0]>0) {
		$SQL = "UPDATE qasamples SET identifier='" . $_POST['Identifier'] . "',
									comments='" . $_POST['Comments'] . "',
									cert='0'
				WHERE sampleid = '".$SelectedSampleID."'";
		$Msg = __('Test Results have not all been entered.  This Lot is not able to be used for a a Certificate of Analysis');
		$ErrMsg = __('The update of the QA Sample failed because');
		$Result = DB_query($SQL, $ErrMsg);
		prnMsg($Msg , 'error');
	}
}
if (isset($_GET['Delete'])) {
	$SQL= "SELECT COUNT(*) FROM sampleresults WHERE sampleresults.resultid='".$_GET['ResultID']."'
											AND sampleresults.manuallyadded='1'";
	$Result = DB_query($SQL);
	$MyRow = DB_fetch_row($Result);
	if ($MyRow[0]==0) {
		prnMsg(__('Cannot delete this Result ID because it is a part of the Product Specification'),'error');
	} else {
		$SQL="DELETE FROM sampleresults WHERE resultid='". $_GET['ResultID']."'";
		$ErrMsg = __('The sample results could not be deleted because');
		$Result = DB_query($SQL, $ErrMsg);

		prnMsg(__('Result QA Sample') . ' ' . $_GET['ResultID'] . __('has been deleted from the database'),'success');
		unset($_GET['ResultID']);
		unset($Delete);
		unset ($_GET['delete']);
	}
}
if (!isset($SelectedSampleID)) {
	echo '<div class="centre">
			<a href="' . $RootPath . '/SelectQASamples.php">' .  __('Select a sample to enter results against') . '</a>
		</div>';
	prnMsg(__('This page can only be opened if a QA Sample has been selected. Please select a sample first'),'info');
	include('includes/footer.php');
	exit();
}

echo '<div class="centre"><a href="' . $RootPath . '/SelectQASamples.php">' . __('Back to Samples') . '</a></div>';


echo '<form method="post" action="' . htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') . '">';
echo '<div>';
echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';


$SQL = "SELECT prodspeckey,
				description,
				lotkey,
				identifier,
				sampledate,
				comments,
				cert
		FROM qasamples
		LEFT OUTER JOIN stockmaster on stockmaster.stockid=qasamples.prodspeckey
		WHERE sampleid='".$SelectedSampleID."'";

$Result = DB_query($SQL);
$MyRow = DB_fetch_array($Result);

if ($MyRow['cert']==1){
	$Cert=__('Yes');
} else {
	$Cert=__('No');
}

echo '<input type="hidden" name="SelectedSampleID" value="' . $SelectedSampleID . '" />';
echo '<table class="selection">
		<tr>
			<th>' . __('Sample ID') . '</th>
			<th>' . __('Specification') . '</th>
			<th>' . __('Lot / Serial') . '</th>
			<th>' . __('Identifier') . '</th>
			<th>' . __('Sample Date') . '</th>
			<th>' . __('Comments') . '</th>
			<th>' . __('Used for Cert') . '</th>
		</tr>';

echo '<tr class="striped_row"><td>' . str_pad($SelectedSampleID,10,'0',STR_PAD_LEFT)  . '</td>
	<td>' . $MyRow['prodspeckey'] . ' - ' . $MyRow['description'] . '</td>
	<td>' . $MyRow['lotkey'] . '</td>
	<td>' . $MyRow['identifier'] . '</td>
	<td>' . ConvertSQLDate($MyRow['sampledate']) . '</td>
	<td>' . $MyRow['comments'] . '</td>
	<td>' . $Cert . '</td>
	</tr>	</table><br />';
$LotKey=$MyRow['lotkey'];
$ProdSpec=$MyRow['prodspeckey'];
$CanCert=$MyRow['cert'];
$SQL = "SELECT sampleid,
				resultid,
				sampleresults.testid,
				qatests.name,
				qatests.method,
				qatests.units,
				qatests.type,
				qatests.numericvalue,
				sampleresults.defaultvalue,
				sampleresults.targetvalue,
				sampleresults.rangemin,
				sampleresults.rangemax,
				sampleresults.testvalue,
				sampleresults.testdate,
				sampleresults.testedby,
				sampleresults.showoncert,
				isinspec,
				sampleresults.manuallyadded
		FROM sampleresults
		INNER JOIN qatests ON qatests.testid=sampleresults.testid
		WHERE sampleresults.sampleid='".$SelectedSampleID."'
		AND sampleresults.showontestplan='1'
		ORDER BY groupby, name";

$Result = DB_query($SQL);

echo '<table cellpadding="2" width="90%" class="selection">
	<thead>
		<tr>
			<th class="SortedColumn">' . __('Test Name') . '</th>
			<th class="SortedColumn">' . __('Test Method') . '</th>
			<th class="SortedColumn">' . __('Range') . '</th>
			<th class="SortedColumn">' . __('Target Value') . '</th>
			<th class="SortedColumn">' . __('Test Date') . '</th>
			<th class="SortedColumn">' . __('Tested By') . '</th>
			<th class="SortedColumn">' . __('Test Result') . '</th>
			<th class="SortedColumn">' . __('On Cert') . '</th>
		</tr>
	</thead>
	<tbody>';

$x = 0;

$TechSQL = "SELECT userid,
						realname
					FROM www_users
					INNER JOIN securityroles ON securityroles.secroleid=www_users.fullaccess
					INNER JOIN securitygroups on securitygroups.secroleid=securityroles.secroleid
					WHERE blocked='0'
					AND tokenid='16'";

$TechResult = DB_query($TechSQL);


while ($MyRow = DB_fetch_array($Result)) {
	$x++;
	$CompareVal='yes';
	$CompareRange='no';
	if ($MyRow['targetvalue']=='') {
		$CompareVal='no';
	}
	if ($MyRow['type']==4) {
		//$RangeDisplay=$MyRow['rangemin'] . '-'  . $MyRow['rangemax'] . ' ' . $MyRow['units'];
		$RangeDisplay='';
		if ($MyRow['rangemin'] > '' OR $MyRow['rangemax'] > '') {
			//var_dump($MyRow['rangemin']); var_dump($MyRow['rangemax']);
			if ($MyRow['rangemin'] > '' AND $MyRow['rangemax'] == '') {
				$RangeDisplay='> ' . $MyRow['rangemin'] . ' ' . $MyRow['units'];
			} elseif ($MyRow['rangemin']== '' AND $MyRow['rangemax'] > '') {
				$RangeDisplay='< ' . $MyRow['rangemax'] . ' ' . $MyRow['units'];
			} else {
				$RangeDisplay=$MyRow['rangemin'] . ' - ' . $MyRow['rangemax'] . ' ' . $MyRow['units'];
			}
			$CompareRange='yes';
		}
		//$CompareRange='yes';
		$CompareVal='yes';
	} else {
		$RangeDisplay='&nbsp;';
		$CompareRange='no';
	}
	if ($MyRow['type']==3) {
		$CompareVal='no';
	}
	if ($MyRow['showoncert'] == 1) {
		$ShowOnCertText = __('Yes');
	} else {
		$ShowOnCertText = __('No');
	}
	if ($MyRow['testdate']=='1000-01-01'){
		$TestDate=date('Y-m-d');
	} else {
		$TestDate=$MyRow['testdate'];
	}

	$BGColor='';
	if ($MyRow['testvalue']=='') {
		$BGColor=' style="background-color:yellow;" ';
	} else {
		if ($MyRow['isinspec']==0) {
		$BGColor=' style="background-color:orange;" ';
		}
	}

	$Class='';
	if ($MyRow['numericvalue'] == 1) {
		$Class="number";
	}
	switch ($MyRow['type']) {
		case 0: //textbox
			$TypeDisp='Text Box';
			$TestResult='<input type="text" size="10" maxlength="20" class="' . $Class . '" name="TestValue' .$x .'" value="' . $MyRow['testvalue'] . '"' . $BGColor . '/>';
			break;
		case 1: //select box
			$TypeDisp='Select Box';
			$OptionValues = explode(',',$MyRow['defaultvalue']);
			$TestResult='<select name="TestValue' .$x .'"' . $BGColor . '/>';
			foreach ($OptionValues as $PropertyOptionValue){
				if ($PropertyOptionValue == $MyRow['testvalue']){
					$TestResult.='<option selected="selected" value="' . $PropertyOptionValue . '">' . $PropertyOptionValue . '</option>';
				} else {
					$TestResult.='<option value="' . $PropertyOptionValue . '">' . $PropertyOptionValue . '</option>';
				}
			}
			$TestResult.='</select>';
			break;
		case 2: //checkbox
			$TypeDisp='Check Box';
			break;
		case 3: //datebox
			$TypeDisp='Date Box';
			$Class="date";
			$TestResult='<input type="text" size="10" maxlength="20" class="' . $Class . '" name="TestValue' .$x .'" value="' . $MyRow['testvalue'] . '"' . $BGColor . '/>';
			break;
		case 4: //range
			$TypeDisp='Range';
			//$Class="number";
			$TestResult='<input type="text" size="10" maxlength="20" class="' . $Class . '" name="TestValue' .$x .'" value="' . $MyRow['testvalue'] . '"' . $BGColor . '/>';
			break;
	} //end switch
	if ($MyRow['manuallyadded']==1) {
		$Delete = '<a href="' .htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8')  .'?Delete=yes&amp;SelectedSampleID=' . $MyRow['sampleid'].'&amp;ResultID=' . $MyRow['resultid']. '" onclick="return confirm(\'' . __('Are you sure you wish to delete this Test from this Sample ?') . '\');">' . __('Delete').'</a>';
		//echo $MyRow['showoncert'];
		$ShowOnCert='<select name="ShowOnCert' .$x .'">';
		if ($MyRow['showoncert']==1) {
			$ShowOnCert.= '<option value="1" selected="selected">' . __('Yes') . '</option>';
			$ShowOnCert.= '<option value="0">' . __('No') . '</option>';
		} else {
			$ShowOnCert.= '<option value="0" selected="selected">' . __('No') . '</option>';
			$ShowOnCert.= '<option value="1">' . __('Yes') . '</option>';
		}
		$ShowOnCert.='</select>';
	} else {
		$Delete ='';
		$ShowOnCert='<input type="hidden" name="ShowOnCert' .$x .'" value="' . $MyRow['showoncert'] . '" />' .$ShowOnCertText;
	}
	if ($MyRow['testedby']=='') {
		$MyRow['testedby']=$_SESSION['UserID'];
	}
	echo '<tr class="striped_row">
			<td><input type="hidden" name="ResultID' .$x. '" value="' . $MyRow['resultid'] . '" /> ' . $MyRow['name'] . '
			<input type="hidden" name="ExpectedValue' .$x. '" value="' . $MyRow['targetvalue'] . '" />
			<input type="hidden" name="MinVal' .$x. '" value="' . $MyRow['rangemin'] . '" />
			<input type="hidden" name="MaxVal' .$x. '" value="' . $MyRow['rangemax'] . '" />
			<input type="hidden" name="CompareRange' .$x. '" value="' . $CompareRange . '" />
			<input type="hidden" name="CompareVal' .$x. '" value="' . $CompareVal . '" />
			</td>
			<td>' . $MyRow['method'] . '</td>
			<td>' . $RangeDisplay . '</td>
			<td>' . $MyRow['targetvalue'] . ' ' . $MyRow['units'] . '</td>
			<td class="date"><input type="date" name="TestDate' .$x. '" size="10" maxlength="10" value="' . $TestDate . '" /> </td>
			<td><select name="TestedBy' .$x .'"/>';
	while ($TechRow = DB_fetch_array($TechResult)) {
		if ($TechRow['userid'] == $MyRow['testedby']){
			echo '<option selected="selected" value="' . $TechRow['userid'] . '">' .$TechRow['realname'] . '</option>';
		} else {
			echo '<option value="' .$TechRow['userid'] . '">' . $TechRow['realname'] . '</option>';
		}
	}
	echo '</select>';
	DB_data_seek($TechResult,0);
	echo '<td>' . $TestResult . '</td>
			<td>' . $ShowOnCert . '</td>
			<td>' . $Delete . '</td>
		</tr>';
}

echo '</tbody>
		</table>
		<div class="centre">
			<input type="hidden" name="TestResultsCounter" value="' . $x . '" />
			<input type="submit" name="submit" value="' . __('Enter Information') . '" />
		</div>
	</form>';

echo '<div class="centre"><a href="' . htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') . '?ListTests=yes&amp;SelectedSampleID=' .$SelectedSampleID .'">' . __('Add More Tests') . '</a></div>';
echo '<div class="centre"><a href="' . htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') . '?CopyResults=yes&amp;SelectedSampleID=' .$SelectedSampleID .'">' . __('Copy These Results') . '</a></div>';

if ($CanCert==1){
	echo '<div class="centre"><a target="_blank" href="'. $RootPath . '/PDFCOA.php?LotKey=' .$LotKey .'&ProdSpec=' . $ProdSpec. '">' . __('Print COA') . '</a></div>';
}

include('includes/footer.php');

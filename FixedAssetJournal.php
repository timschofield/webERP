<?php

/* $Revision: 1.1 $ */

include('includes/DefineJournalClass.php');

$PageSecurity = 10;
include('includes/session.inc');
$title = _('Depreciation Journal Entry');

include('includes/header.inc');
include('includes/SQL_CommonFunctions.inc');


/*Get the last period depreciation (depn is transtype =44) was posted for */
$result = DB_query('SELECT periods.lastdate_in_period, max(fixedassettrans.periodno) FROM fixedassettrans INNER JOIN periods ON fixedassettrans.periodno=periods.periodno WHERE transtype=44 GROUP BY periods.lastdate_in_period',$db);

$LastDepnRun = DB_fetch_row($result);

//echo '<BR>LastRun period = ' . $LastDepnRun[1] . ' Last date in period = ' . $LastDepnRun[0];

$AllowUserEnteredProcessDate = true;

if ($LastDepnRun[1]==0 AND $LastDepnRun[0]==NULL) { //then depn has never been run yet?
	
	/*in this case default depreciation calc to the last day of last month - and allow user to select a period */
	if (!isset($_POST['ProcessDate'])) {
		$_POST['ProcessDate'] = Date($_SESSION['DefaultDateFormat'],mktime(0,0,0,date('m'),0,date('Y')));
	}
	
} else { //depn calc has been run previously 
	$AllowUserEnteredProcessDate = false;
	$_POST['ProcessDate'] = DateAdd(ConvertSQLDate($LastDepnRun[0]),'m',1);
}
$PeriodNo = GetPeriod($_POST['ProcessDate'],$db);

/* Get list of assets for journal */
$sql='SELECT fixedassets.assetid,
						fixedassets.description,
						fixedassets.cost,
						fixedassets.accumdepn,
						fixedassets.depntype,
						fixedassets.depnrate,
						fixedassetcategories.accumdepnact AS bsdepn,
						fixedassetcategories.depnact AS pldepn,
						fixedassetcategories.categorydescription
			FROM fixedassets
			INNER JOIN fixedassetcategories
				ON fixedassets.assetcategoryid=fixedassetcategories.categoryid
			ORDER BY assetcategoryid, assetid';
$result=DB_query($sql, $db);

if (isset($_POST['CommitDepreciation'])){
	$result = DB_Txn_Begin($db);
	$TransNo = GetNextTransNo(44, $db);
	
}

echo '<p></p><table>';
$Heading = '<tr><th>' . _('Asset ID') . '</th>
								<th>' . _('Description') . '</th>
								<th>' . _('Cost') . '</th>
								<th>' . _('Accum Depn') . '</th>
								<th>' . _('B/fwd Book Value') . '</th>
								<th>'. _('Depn Type') . '</th>
								<th>'. _('Depn Rate') . '</th>
								<th>' . _('New Depn') . '</th>
							</tr>';
echo $Heading;

$AssetCategoryDescription ='0';

$TotalCost =0;
$TotalAccumDepn=0;
$TotalDepn = 0;
$RowCounter = 0;
$k=0;

while ($myrow=DB_fetch_array($result)) {
	if ($AssetCategoryDescription != $myrow['categorydescription'] OR $AssetCategoryDescription =='0'){
		if ($AssetCategoryDescription !='0'){ //then print totals
			echo '<tr><th colspan=2 align="right">' . _('Total for') . ' ' . $AssetCategoryDescription . ' </th>
								<th class="number">' . number_format($TotalCategoryCost,2) . '</th>
								<th class="number">' . number_format($TotalCategoryAccumDepn,2) . '</th>
								<th class="number">' . number_format(($TotalCategoryCost-$TotalCategoryAccumDepn),2) . '</th>
								<th colspan=2></th>
								<th class="number">' . number_format($TotalCategoryDepn,2) . '</th>
								</tr>';
		}
		echo '<tr><th colspan=8 align="left">' . $myrow['categorydescription']  . '</th></tr>';
		$AssetCategoryDescription = $myrow['categorydescription'];
		$TotalCategoryCost = 0;
		$TotalCategoryAccumDepn =0;
		$TotalCategoryDepn = 0;
	}
	$BookValueBfwd = $myrow['cost'] - $myrow['accumdepn'];
	if ($myrow['depntype']==0){ //striaght line depreciation
		$DepreciationType = _('SL');
		$NewDepreciation = $myrow['cost'] * $myrow['depnrate']/100/12;
		if ($NewDepreciation > $BookValueBfwd){
			$NewDepreciation = $BookValueBfwd;
		}
	} else { //Diminishing value depreciation
		$DepreciationType = _('DV');
		$NewDepreciation = $BookValueBfwd * $myrow['depnrate']/100/12;
	}
	$RowCounter++;
	if ($RowCounter ==15){
		echo $Heading;
		$RowCounter =0;
	}
	if ($k==1){
		echo '<tr class="EvenTableRows">';
		$k=0;
	} else {
		echo '<tr class="OddTableRows">';
		$k++;
	}
	echo '<td>' . $myrow['assetid'] . '</td>
				<td>' . $myrow['description'] . '</td>
				<td class="number">' . number_format($myrow['cost'],2) . '</td>
				<td class="number">' . number_format($myrow['accumdepn'],2) . '</td>
				<td class="number">' . number_format($myrow['cost']-$myrow['accumdepn'],2) . '</td>
				<td align="center">' . $DepreciationType . '</td>
				<td class="number">' . $myrow['depnrate']  . '</td>
				<td class="number">' . $NewDepreciation  . '</td>
			</tr>';
	$TotalCategoryCost +=$myrow['cost'];
	$TotalCategoryAccumDepn +=$myrow['accumdepn'];
	$TotalCategoryDepn +=$NewDepreciation;
	$TotalCost +=$myrow['cost'];
	$TotalAccumDepn +=$myrow['accumdepn'];
	$TotalDepn +=$NewDepreciation;
	
	if (isset($_POST['CommitDepreciation']) AND $NewDepreciation !=0){
		//debit depreciation expense
		$SQL = "INSERT INTO gltrans (type,
																typeno,
																trandate,
																periodno,
																account,
																narrative,
																amount) 
												VALUES (44,
															'" . $TransNo . "',
															'" . FormatDateForSQL($_POST['ProcessDate']) . "',
															'" . $PeriodNo . "',
															'" . $myrow['depnact'] . "',
															'" . $myrow['assetid'] . "',
															'" . $NewDepreciation ."')";
		$ErrMsg = _('Cannot insert a depreciation GL entry for the depreciation because');
		$DbgMsg = _('The SQL that failed to insert the GL Trans record was');
		$result = DB_query($SQL,$db,$ErrMsg,$DbgMsg,true);
		$SQL = "INSERT INTO gltrans (type,
																typeno,
																trandate,
																periodno,
																account,
																narrative,
																amount) 
												VALUES (44,
															'" . $TransNo . "',
															'" . FormatDateForSQL($_POST['ProcessDate']) . "',
															'" . $PeriodNo . "',
															'" . $myrow['accumdepnact'] . "',
															'" . $myrow['assetid'] . "',
															'" . -$NewDepreciation ."')";
		$result = DB_query($SQL,$db,$ErrMsg,$DbgMsg,true);
		
		//insert the fixedassettrans record
		$SQL = "INSERT INTO fixedassettrans (assetid,
																			transtype,
																			transno,
																			transdate,
																			periodno,
																			inputdate,
																			cost,
																			depn)
															VALUES ('" . $myrow['assetid'] . "',
																			'44',
																			'" . $TransNo . "',
																			'" . FormatDateForSQL($_POST['ProcessDate']) . "',
																			'" . $PeriodNo . "',
																			'" . Date('Y-m-d') . "',
																			'0',
																			'" . $NewDepreciation . "')";
		$ErrMsg = _('Cannot insert a fixed asset transaction entry for the depreciation because');
		$DbgMsg = _('The SQL that failed to insert the fixed asset transaction record was');
		$result = DB_query($SQL,$db,$ErrMsg,$DbgMsg,true);
	} //end if Committing the depreciation to DB
} //end loop around the assets to calculate depreciation for
echo '<tr><th colspan=2 align="right">' . _('Total for') . ' ' . $AssetCategoryDescription . ' </th>
					<th class="number">' . number_format($TotalCategoryCost,2) . '</th>
					<th class="number">' . number_format($TotalCategoryAccumDepn,2) . '</th>
					<th class="number">' . number_format(($TotalCategoryCost-$TotalCategoryAccumDepn),2) . '</th>
					<th colspan=2></th>
					<th class="number">' . number_format($TotalCategoryDepn,2) . '</th>
					</tr>';
echo '<tr><th colspan=2 align="right">' . _('GRAND Total') . ' </th>
					<th class="number">' . number_format($TotalCost,2) . '</th>
					<th class="number">' . number_format($TotalAccumDepn,2) . '</th>
					<th class="number">' . number_format(($TotalCost-$TotalAccumDepn),2) . '</th>
					<th colspan=2></th>
					<th class="number">' . number_format($TotalDepn,2) . '</th>
					</tr>';

echo '</table><hr><p></p>';

if (isset($_POST['CommitDepreciation'])){
	$result = DB_Txn_Commit($db);
	prnMsg(_('Depreciation') . ' ' . $TransNo . ' ' . _('has been successfully entered'),'success');
	unset($_POST['ProcessDate']);
	echo '<br><a href="index.php' . '?' . SID . '">' ._('Return to main menu').'</a>';
	/*And post the journal too */
	include ('includes/GLPostings.inc');
} else {
	echo '<form action=' . $_SERVER['PHP_SELF'] . '?' . SID . ' method=post name="form">';
	echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';
	echo '<p></p>';
	echo '<table class=selection width=30%><tr></tr><tr>';
	if (AllowUserEnteredProcessDate){
		echo '<td>'._('Date to Process Depreciation'). ':</td>
					<td><input type="text" class="date" alt="' .$_SESSION['DefaultDateFormat']. '" name="ProcessDate" maxlength=10 size=11 value="' . $_POST['ProcessDate'] . '"></td>';
	}
	echo '<td><input type="submit" name="CommitDepreciation" value="'._('Commit Depreciation').'">';
	echo '</tr></table><br>';
	echo '</form>';
}
include('includes/footer.inc');
?>
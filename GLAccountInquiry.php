<?php

/* $Id: GLAccountInquiry.php 6499 2013-12-14 13:50:20Z rchacon $*/

include ('includes/session.inc');
$Title = _('General Ledger Account Inquiry');
include('includes/header.inc');
include('includes/GLPostings.inc');

if (isset($_POST['Account'])){
	$SelectedAccount = $_POST['Account'];
} elseif (isset($_GET['Account'])){
	$SelectedAccount = $_GET['Account'];
}

if (isset($_POST['Period'])){
	$SelectedPeriod = $_POST['Period'];
} elseif (isset($_GET['Period'])){
	$SelectedPeriod = array($_GET['Period']);
}

/* Get the start and periods, depending on how this script was called*/
if (isset($SelectedPeriod)) { //If it was called from itself (in other words an inquiry was run and we wish to leave the periods selected unchanged
	$FirstPeriodSelected = min($SelectedPeriod);
	$LastPeriodSelected = max($SelectedPeriod);
} elseif (isset($_GET['FromPeriod'])) { //If it was called from the Trial Balance/P&L or Balance sheet
	$FirstPeriodSelected = $_GET['FromPeriod'];
	$LastPeriodSelected = $_GET['ToPeriod'];
} else { // Otherwise just highlight the current period
	$FirstPeriodSelected = GetPeriod(date($_SESSION['DefaultDateFormat']), $db);
	$LastPeriodSelected = GetPeriod(date($_SESSION['DefaultDateFormat']), $db);
}

echo '<p class="page_title_text"><img src="'.$RootPath.'/css/'.$Theme.'/images/transactions.png" title="' . _('General Ledger Account Inquiry') . '" alt="" />' . ' ' . _('General Ledger Account Inquiry') . '</p>';

echo '<div class="page_help_text">' . _('Use the keyboard Shift key to select multiple periods') . '</div><br />';

echo '<form method="post" action="' . htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') . '">';
echo '<div>';
echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';

/*Dates in SQL format for the last day of last month*/
$DefaultPeriodDate = Date ('Y-m-d', Mktime(0,0,0,Date('m'),0,Date('Y')));

/*Show a form to allow input of criteria for TB to show */
echo '<table class="selection">
		<tr>
			<td>' . _('Account').':</td>
			<td><select name="Account">';

$sql = "SELECT accountcode, accountname FROM chartmaster ORDER BY accountcode";
$Account = DB_query($sql,$db);
while ($myrow=DB_fetch_array($Account,$db)){
	if($myrow['accountcode'] == $SelectedAccount){
		echo '<option selected="selected" value="' . $myrow['accountcode'] . '">' . $myrow['accountcode'] . ' ' . htmlspecialchars($myrow['accountname'], ENT_QUOTES, 'UTF-8', false) . '</option>';
	} else {
		echo '<option value="' . $myrow['accountcode'] . '">' . $myrow['accountcode'] . ' ' . htmlspecialchars($myrow['accountname'], ENT_QUOTES, 'UTF-8', false) . '</option>';
	}
 }
echo '</select></td>
	</tr>';

//Select the tag
echo '<tr>
		<td>' . _('Select Tag') . ':</td>
		<td><select name="tag">';

$SQL = "SELECT tagref,
			tagdescription
		FROM tags
		ORDER BY tagref";

$result=DB_query($SQL,$db);
echo '<option value="0">0 - '._('All tags') . '</option>';

while ($myrow=DB_fetch_array($result)){
	if (isset($_POST['tag']) and $_POST['tag']==$myrow['tagref']){
		echo '<option selected="selected" value="' . $myrow['tagref'] . '">' . $myrow['tagref'].' - ' .$myrow['tagdescription'] . '</option>';
	} else {
		echo '<option value="' . $myrow['tagref'] . '">' . $myrow['tagref'].' - ' .$myrow['tagdescription'] . '</option>';
	}
}
echo '</select></td>
	</tr>';
// End select tag
echo '<tr>
		<td>' . _('For Period range').':</td>
		<td><select name="Period[]" size="12" multiple="multiple">';

$sql = "SELECT periodno, lastdate_in_period FROM periods ORDER BY periodno DESC";
$Periods = DB_query($sql,$db);
while ($myrow=DB_fetch_array($Periods,$db)){
	if (isset($FirstPeriodSelected) AND $myrow['periodno'] >= $FirstPeriodSelected AND $myrow['periodno'] <= $LastPeriodSelected) {
		echo '<option selected="selected" value="' . $myrow['periodno'] . '">' . _(MonthAndYearFromSQLDate($myrow['lastdate_in_period'])) . '</option>';
	} else {
		echo '<option value="' . $myrow['periodno'] . '">' . _(MonthAndYearFromSQLDate($myrow['lastdate_in_period'])) . '</option>';
	}
}
echo '</select></td>
	</tr>
	</table>
	<br />
	<div class="centre">
		<input type="submit" name="Show" value="'._('Show Account Transactions').'" />
	</div>
	</div>
	</form>';

/* End of the Form  rest of script is what happens if the show button is hit*/

if (isset($_POST['Show'])){

	if (!isset($SelectedPeriod)){
		prnMsg(_('A period or range of periods must be selected from the list box'),'info');
		include('includes/footer.inc');
		exit;
	}
	/*Is the account a balance sheet or a profit and loss account */
	$result = DB_query("SELECT pandl
				FROM accountgroups
				INNER JOIN chartmaster ON accountgroups.groupname=chartmaster.group_
				WHERE chartmaster.accountcode='" . $SelectedAccount ."'",$db);
	$PandLRow = DB_fetch_row($result);
	if ($PandLRow[0]==1){
		$PandLAccount = True;
	}else{
		$PandLAccount = False; /*its a balance sheet account */
	}

	$FirstPeriodSelected = min($SelectedPeriod);
	$LastPeriodSelected = max($SelectedPeriod);

	$sql= "SELECT counterindex,
				type,
				typename,
				gltrans.typeno,
				trandate,
				narrative,
				amount,
				periodno,
				gltrans.tag,
				tagdescription
			FROM gltrans INNER JOIN systypes
			ON systypes.typeid=gltrans.type
			LEFT JOIN tags
			ON gltrans.tag = tags.tagref
			WHERE gltrans.account = '" . $SelectedAccount . "'
			AND posted=1
			AND periodno>='" . $FirstPeriodSelected . "'
			AND periodno<='" . $LastPeriodSelected . "'";

	if ($_POST['tag']!=0) {
 		$sql = $sql . " AND tag='" . $_POST['tag'] . "'";
	}
			
	$sql = $sql . " ORDER BY periodno, gltrans.trandate, counterindex";

	$namesql = "SELECT accountname FROM chartmaster WHERE accountcode='" . $SelectedAccount . "'";
	$nameresult = DB_query($namesql, $db);
	$namerow=DB_fetch_array($nameresult);
	$SelectedAccountName=$namerow['accountname'];
	$ErrMsg = _('The transactions for account') . ' ' . $SelectedAccount . ' ' . _('could not be retrieved because') ;
	$TransResult = DB_query($sql,$db,$ErrMsg);

	echo '<br />
		<table class="selection">
		<tr>
			<th colspan="8"><b>' ._('Transactions for account').' '.$SelectedAccount. ' - '. $SelectedAccountName . '</b></th>
		</tr>';

	$TableHeader = '<tr>
						<th>' . _('Type') . '</th>
						<th>' . _('Number') . '</th>
						<th>' . _('Date') . '</th>
						<th>' . _('Debit') . '</th>
						<th>' . _('Credit') . '</th>
						<th>' . _('Narrative') . '</th>
						<th>' . _('Balance') . '</th>
						<th>' . _('Tag') . '</th>
					</tr>';

	echo $TableHeader;

	if ($PandLAccount==True) {
		$RunningTotal = 0;
	} else {
			// added to fix bug with Brought Forward Balance always being zero
		$sql = "SELECT bfwd,
					actual,
					period
				FROM chartdetails
				WHERE chartdetails.accountcode='" . $SelectedAccount . "'
				AND chartdetails.period='" . $FirstPeriodSelected . "'";

		$ErrMsg = _('The chart details for account') . ' ' . $SelectedAccount . ' ' . _('could not be retrieved');
		$ChartDetailsResult = DB_query($sql,$db,$ErrMsg);
		$ChartDetailRow = DB_fetch_array($ChartDetailsResult);

		$RunningTotal =$ChartDetailRow['bfwd'];
			echo '<tr>
					<td colspan="3"><b>' . _('Brought Forward Balance') . '</b></td>';
		if ($RunningTotal < 0 ){ //its a credit balance b/fwd
			echo '
					<td></td>
					<td class="number"><b>' . locale_number_format(-$RunningTotal,$_SESSION['CompanyRecord']['decimalplaces']) . '</b></td>
					<td colspan="3">&nbsp;</td>
				</tr>';
		} else { //its a debit balance b/fwd
			echo '
					<td class="number"><b>' . locale_number_format($RunningTotal,$_SESSION['CompanyRecord']['decimalplaces']) . '</b></td>
					<td colspan="4">&nbsp;</td>
				</tr>';
		}
	}
	$PeriodTotal = 0;
	$PeriodNo = -9999;
	$ShowIntegrityReport = False;
	$j = 1;
	$k=0; //row colour counter
	$IntegrityReport='';
	while ($myrow=DB_fetch_array($TransResult)) {
		if ($myrow['periodno']!=$PeriodNo){
			if ($PeriodNo!=-9999){ //ie its not the first time around
				/*Get the ChartDetails balance b/fwd and the actual movement in the account for the period as recorded in the chart details - need to ensure integrity of transactions to the chart detail movements. Also, for a balance sheet account it is the balance carried forward that is important, not just the transactions*/

				$sql = "SELECT bfwd,
						actual,
						period
					FROM chartdetails
					WHERE chartdetails.accountcode='" . $SelectedAccount . "'
					AND chartdetails.period='" . $PeriodNo . "'";

				$ErrMsg = _('The chart details for account') . ' ' . $SelectedAccount . ' ' . _('could not be retrieved');
				$ChartDetailsResult = DB_query($sql,$db,$ErrMsg);
				$ChartDetailRow = DB_fetch_array($ChartDetailsResult);

				echo '<tr>
					<td colspan="3"><b>' . _('Total for period') . ' ' . $PeriodNo . '</b></td>';
				if ($PeriodTotal < 0 ){ //its a credit balance b/fwd
					if ($PandLAccount==True) {
						$RunningTotal = 0;
					}
					echo '<td></td>
						<td class="number"><b>' . locale_number_format(-$PeriodTotal,$_SESSION['CompanyRecord']['decimalplaces']) . '</b></td>
						<td colspan="3">&nbsp;</td>
				</tr>';
				} else { //its a debit balance b/fwd
					if ($PandLAccount==True) {
						$RunningTotal = 0;
					}
					echo '<td class="number"><b>' . locale_number_format($PeriodTotal,$_SESSION['CompanyRecord']['decimalplaces']) . '</b></td>
							<td colspan="4">&nbsp;</td>
				</tr>';
				}
				$IntegrityReport .= '<br />' . _('Period') . ': ' . $PeriodNo  . _('Account movement per transaction') . ': '  . locale_number_format($PeriodTotal,$_SESSION['CompanyRecord']['decimalplaces']) . ' ' . _('Movement per ChartDetails record') . ': ' . locale_number_format($ChartDetailRow['actual'],$_SESSION['CompanyRecord']['decimalplaces']) . ' ' . _('Period difference') . ': ' . locale_number_format($PeriodTotal -$ChartDetailRow['actual'],3);

				if (ABS($PeriodTotal -$ChartDetailRow['actual'])>0.01){
					$ShowIntegrityReport = True;
				}
			}
			$PeriodNo = $myrow['periodno'];
			$PeriodTotal = 0;
		}

		if ($k==1){
			echo '<tr class="EvenTableRows">';
			$k=0;
		} else {
			echo '<tr class="OddTableRows">';
			$k++;
		}

		$RunningTotal += $myrow['amount'];
		$PeriodTotal += $myrow['amount'];

		if($myrow['amount']>=0){
			$DebitAmount = locale_number_format($myrow['amount'],$_SESSION['CompanyRecord']['decimalplaces']);
			$CreditAmount = '';
		} else {
			$CreditAmount = locale_number_format(-$myrow['amount'],$_SESSION['CompanyRecord']['decimalplaces']);
			$DebitAmount = '';
		}

		$FormatedTranDate = ConvertSQLDate($myrow['trandate']);
		$URL_to_TransDetail = $RootPath . '/GLTransInquiry.php?TypeID=' . $myrow['type'] . '&amp;TransNo=' . $myrow['typeno'];

		printf('<td>%s</td>
				<td class="number"><a href="%s">%s</a></td>
				<td>%s</td>
				<td class="number">%s</td>
				<td class="number">%s</td>
				<td>%s</td>
				<td class="number"><b>%s</b></td>
				<td>%s</td>
				</tr>',
				$myrow['typename'],
				$URL_to_TransDetail,
				$myrow['typeno'],
				$FormatedTranDate,
				$DebitAmount,
				$CreditAmount,
				$myrow['narrative'],
				locale_number_format($RunningTotal,$_SESSION['CompanyRecord']['decimalplaces']),
				$myrow['tagdescription']);

	}

	echo '<tr>
			<td colspan="3"><b>';
	if ($PandLAccount==True){
		echo _('Total Period Movement');
	} else { /*its a balance sheet account*/
		echo _('Balance C/Fwd');
	}
	echo '</b></td>';

	if ($RunningTotal >0){
		echo '<td class="number"><b>' . locale_number_format(($RunningTotal),$_SESSION['CompanyRecord']['decimalplaces']) . '</b></td>
				<td colspan="2"></td>
			</tr>';
	}else {
		echo '<td></td>
				<td class="number"><b>' . locale_number_format((-$RunningTotal),$_SESSION['CompanyRecord']['decimalplaces']) . '</b></td><td colspan="2"></td>
			</tr>';
	}
	echo '</table>';
} /* end of if Show button hit */



if (isset($ShowIntegrityReport) AND $ShowIntegrityReport==True AND $_POST['tag']=='0'){
	if (!isset($IntegrityReport)) {
		$IntegrityReport='';
	}
	prnMsg( _('There are differences between the sum of the transactions and the recorded movements in the ChartDetails table') . '. ' . _('A log of the account differences for the periods report shows below'),'warn');
	echo '<p>' . $IntegrityReport;
}
include('includes/footer.inc');
?>

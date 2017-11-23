<?php
/* $Id: PcReportExpense.php 7751 2017-04-13 16:34:26Z rchacon $*/

include ('includes/session.php');
$Title = _('Petty Cash Expense Management Report');
/* webERP manual links before header.php */
$ViewTopic = 'PettyCash';
$BookMark = 'PcReportExpense';

include ('includes/SQL_CommonFunctions.inc');
include  ('includes/header.php');

echo '<p class="page_title_text"><img src="' . $RootPath . '/css/' . $_SESSION['Theme'] . '/images/money_add.png" title="' . _('PC Expense Report')
. '" alt="" />' . ' ' . $Title . '</p>';

echo '<form method="post" action="' . htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') . '">
	<div>
	<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';

if (isset($_POST['SelectedExpense'])){
	$SelectedExpense = mb_strtoupper($_POST['SelectedExpense']);
} elseif (isset($_GET['SelectedExpense'])){
	$SelectedExpense = mb_strtoupper($_GET['SelectedExpense']);
}

if ((! isset($_POST['FromDate']) AND ! isset($_POST['ToDate'])) OR isset($_POST['SelectDifferentDate'])) {



	if (!isset($_POST['FromDate'])){
		$_POST['FromDate']=Date($_SESSION['DefaultDateFormat'], mktime(0,0,0,Date('m'),1,Date('Y')));
	}

	if (!isset($_POST['ToDate'])){
		$_POST['ToDate'] = Date($_SESSION['DefaultDateFormat']);
	}

	/*Show a form to allow input of criteria for Expenses to show */
	echo '<table class="selection">
		<tr>
			<td>' . _('Expense Code') . ':</td>
			<td><select name="SelectedExpense">';

	$SQL = "SELECT DISTINCT(pctabexpenses.codeexpense)
			FROM pctabs, pctabexpenses
			WHERE pctabexpenses.typetabcode = pctabs.typetabcode
				AND ( pctabs.authorizer='" . $_SESSION['UserID'] .
					"' OR pctabs.usercode ='" . $_SESSION['UserID'].
					"' OR pctabs.assigner ='" . $_SESSION['UserID'] . "' )
			ORDER BY pctabexpenses.codeexpense";

	$Result = DB_query($SQL);

	while ($MyRow = DB_fetch_array($Result)) {
		if (isset($_POST['SelectedExpense']) and $MyRow['codeexpense']==$_POST['SelectedExpense']) {
			echo '<option selected="selected" value="';
		} else {
			echo '<option value="';
		}
		echo $MyRow['codeexpense'] . '">' . $MyRow['codeexpense'] . '</option>';

	} //end while loop get type of tab

	DB_free_result($Result);


	echo '</select></td>
		</tr>
		<tr>
			<td>' . _('From Date') . ':' . '</td>
			<td><input tabindex="2" class="date" alt="'.$_SESSION['DefaultDateFormat'].'" type="text" name="FromDate" maxlength="10" size="11" value="' . $_POST['FromDate'] . '" /></td>
		</tr>
		<tr>
			<td>' . _('To Date') . ':' . '</td>
			<td><input tabindex="3" class="date" alt="'.$_SESSION['DefaultDateFormat'].'" type="text" name="ToDate" maxlength="10" size="11" value="' . $_POST['ToDate'] . '" /></td>
		</tr>
		</table>
		<br />
		<div class="centre">
			<input type="submit" name="ShowTB" value="' . _('Show HTML') .'" />
		</div>
		</div>
	</form>';

} else {

	$SQL_FromDate = FormatDateForSQL($_POST['FromDate']);
	$SQL_ToDate = FormatDateForSQL($_POST['ToDate']);

	echo '<input type="hidden" name="FromDate" value="' . $_POST['FromDate'] . '" />
			<input type="hidden" name="ToDate" value="' . $_POST['ToDate'] . '" />';

	echo '<br /><table class="selection">';

	echo '<tr>
			<td>' . _('Expense Code') . ':</td>
			<td>' . $SelectedExpense . '</td>
			</tr>
		<tr>
			<td>' . _('From') . ':</td>
			<td>' . $_POST['FromDate'] . '</td>
		</tr>
		<tr>
			<td>' . _('To') . ':</td>
			<td>' . $_POST['ToDate'] . '</td>
		</tr>';

	echo '</table>';

	$SQL = "SELECT pcashdetails.counterindex,
					pcashdetails.tabcode,
					pcashdetails.tag,
					pcashdetails.date,
					pcashdetails.codeexpense,
					pcashdetails.amount,
					pcashdetails.authorized,
					pcashdetails.posted,
					pcashdetails.notes,
					pctabs.currency,
					currencies.decimalplaces
			FROM pcashdetails, pctabs, currencies
			WHERE pcashdetails.tabcode = pctabs.tabcode
				AND pctabs.currency = currencies.currabrev
				AND pcashdetails.codeexpense='".$SelectedExpense."'
				AND pcashdetails.date >='" . $SQL_FromDate . "'
				AND pcashdetails.date <= '" . $SQL_ToDate . "'
				AND (pctabs.authorizer='" . $_SESSION['UserID'] .
					"' OR pctabs.usercode ='" . $_SESSION['UserID'].
					"' OR pctabs.assigner ='" . $_SESSION['UserID'] . "')
			ORDER BY pcashdetails.date, pcashdetails.counterindex ASC";

	$Result = DB_query($SQL,
						_('No Petty Cash movements for this expense code were returned by the SQL because'),
						_('The SQL that failed was:'));

	echo '<br /><table class="selection">
		<tr>
			<th>' . _('Date of Expense') . '</th>
			<th>' . _('Tab') . '</th>
			<th>' . _('Currency') . '</th>
			<th>' . _('Gross Amount') . '</th>
			<th>', _('Tax'), '</th>
			<th>', _('Tax Group'), '</th>
			<th>', _('Tag'), '</th>
			<th>' . _('Notes') . '</th>
			<th>' . _('Date Authorised') . '</th>
		</tr>';

	$k=0; //row colour counter

	while ($MyRow = DB_fetch_array($Result)) {
		$CurrDecimalPlaces = $MyRow['decimalplaces'];
		if ($k == 1) {
			echo '<tr class="EvenTableRows">';
			$k = 0;
		} else {
			echo '<tr class="OddTableRows">';
			$k = 1;
		}
		$TaxesDescription = '';
		$TaxesTaxAmount = '';
		$TaxSQL = "SELECT counterindex,
							pccashdetail,
							calculationorder,
							description,
							taxauthid,
							purchtaxglaccount,
							taxontax,
							taxrate,
							amount
						FROM pcashdetailtaxes
						WHERE pccashdetail='" . $MyRow['counterindex'] . "'";
		$TaxResult = DB_query($TaxSQL);
		while ($MyTaxRow = DB_fetch_array($TaxResult)) {
			$TaxesDescription .= $MyTaxRow['description'] . '<br />';
			$TaxesTaxAmount .= locale_number_format($MyTaxRow['amount'], $CurrDecimalPlaces) . '<br />';
		}
		$TagSQL = "SELECT tagdescription FROM tags WHERE tagref='" . $MyRow['tag'] . "'";
		$TagResult = DB_query($TagSQL);
		$TagRow = DB_fetch_array($TagResult);
		if ($MyRow['tag'] == 0) {
			$TagRow['tagdescription'] = _('None');
		}
		$TagTo = $MyRow['tag'];
		$TagDescription = $TagTo . ' - ' . $TagRow['tagdescription'];
		
		if ($MyRow['authorized'] == '0000-00-00') {
			$AuthorisedDate = _('Unauthorised');
		} else {
			$AuthorisedDate = ConvertSQLDate($MyRow['authorized']);
		}
			
		/*
		if ($MyRow['posted'] == 0) {
			$Posted = _('No');
		} else {
			$Posted = _('Yes');
		}
		*/
		
		echo '<td>', ConvertSQLDate($MyRow['date']), '</td>
			<td>', $MyRow['tabcode'], '</td>
			<td>', $MyRow['currency'], '</td>
			<td class="number">', locale_number_format($MyRow['amount'], $CurrDecimalPlaces), '</td>
			<td class="number">', $TaxesTaxAmount, '</td>
			<td>', $TaxesDescription, '</td>
			<td>', $TagDescription, '</td>
			<td>', $MyRow['notes'], '</td>
			<td>', $AuthorisedDate, '</td>
		</tr>';
	} //end of looping

	echo '</table>';
	echo '<br /><div class="centre"><input type="submit" name="SelectDifferentDate" value="' . _('Select A Different Date') . '" /></div>';
    echo '</div>
          </form>';
}
include('includes/footer.php');

?>
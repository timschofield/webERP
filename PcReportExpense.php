<?php

include ('includes/session.php');
$Title = _('Petty Cash Expense Management Report');
$ViewTopic = 'PettyCash';
$BookMark = 'PcReportExpense';

include ('includes/SQL_CommonFunctions.inc');
include  ('includes/header.php');

echo '<p class="page_title_text"><img src="'.$RootPath.'/css/'.$Theme.'/images/money_add.png" title="' . _('PC Expense Report')
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
			<td>' . _('Code Of Petty Cash Expense') . ':</td>
			<td><select name="SelectedExpense">';

	$SQL = "SELECT DISTINCT(pctabexpenses.codeexpense)
			FROM pctabs, pctabexpenses
			WHERE pctabexpenses.typetabcode = pctabs.typetabcode
				AND ( pctabs.authorizer='" . $_SESSION['UserID'] .
					"' OR pctabs.usercode ='" . $_SESSION['UserID'].
					"' OR pctabs.assigner ='" . $_SESSION['UserID'] . "' )
			ORDER BY pctabexpenses.codeexpense";

	$result = DB_query($SQL);

	while ($myrow = DB_fetch_array($result)) {
		if (isset($_POST['SelectedExpense']) and $myrow['codeexpense']==$_POST['SelectedExpense']) {
			echo '<option selected="selected" value="';
		} else {
			echo '<option value="';
		}
		echo $myrow['codeexpense'] . '">' . $myrow['codeexpense'] . '</option>';

	} //end while loop get type of tab

	DB_free_result($result);


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
			<td style="width:200px">' . $SelectedExpense . '</td>'  .
			'<td>' . _('From') . ':</td>
			<td>' . $_POST['FromDate'] . '</td>
		</tr>
		<tr>
			<td></td>
			<td></td>' .
			'<td>' . _('To') . ':</td>
			<td>' . $_POST['ToDate'] . '</td>
		</tr>';

	echo '</table>';

	$SQL = "SELECT pcashdetails.date,
					pcashdetails.tabcode,
					pcashdetails.amount,
					pcashdetails.notes,
					pcashdetails.receipt,
					pcashdetails.authorized,
					pctabs.currency,
					currencies.decimalplaces
			FROM pcashdetails, pctabs, currencies
			WHERE pcashdetails.tabcode = pctabs.tabcode
				AND pctabs.currency = currencies.currabrev
				AND codeexpense='".$SelectedExpense."'
				AND date >='" . $SQL_FromDate . "'
				AND date <= '" . $SQL_ToDate . "'
				AND (pctabs.authorizer='" . $_SESSION['UserID'] .
					"' OR pctabs.usercode ='" . $_SESSION['UserID'].
					"' OR pctabs.assigner ='" . $_SESSION['UserID'] . "')
			ORDER BY date, counterindex ASC";

	$TabDetail = DB_query($SQL,
						_('No Petty Cash movements for this expense code were returned by the SQL because'),
						_('The SQL that failed was:'));

	echo '<br /><table class="selection">
		<tr>
			<th>' . _('Date') . '</th>
			<th>' . _('Tab') . '</th>
			<th>' . _('Amount') . '</th>
			<th>' . _('Currency') . '</th>
			<th>' . _('Notes') . '</th>
			<th>' . _('Receipt') . '</th>
			<th>' . _('Authorised') . '</th>
		</tr>';

	$k=0; //row colour counter

	while ($myrow = DB_fetch_array($TabDetail)) {
		if ($k==1){
			echo '<tr class="EvenTableRows">';
			$k=0;
		} else {
			echo '<tr class="OddTableRows">';
			$k=1;
		}

		printf("<td>%s</td>
				<td>%s</td>
				<td class='number'>%s</td>
				<td>%s</td>
				<td>%s</td>
				<td>%s</td>
				<td>%s</td>
				</tr>",
				ConvertSQLDate($myrow['date']),
				$myrow['tabcode'],
				locale_number_format($myrow['amount'],$myrow['decimalplaces']),
				$myrow['currency'],
				$myrow['notes'],
				$myrow['receipt'],
				ConvertSQLDate($myrow['authorized'])
				);
	}

	echo '</table>';
	echo '<br /><div class="centre"><input type="submit" name="SelectDifferentDate" value="' . _('Select A Different Date') . '" /></div>';
    echo '</div>
          </form>';
}
include('includes/footer.php');

?>
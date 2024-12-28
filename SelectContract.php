<?php

include('includes/session.php');
$Title = _('Select Contract');
$ViewTopic= 'Contracts';
$BookMark = 'SelectContract';
include('includes/header.php');

echo '<p class="page_title_text"><img alt="" src="', $RootPath, '/css/', $Theme,
	'/images/contract.png" title="', // Icon image.
	_('Contracts'), '" /> ', // Icon title.
	_('Select A Contract'), '</p>';// Page title.

echo '<form action="' . htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') . '" method="post">';
echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';

echo '<fieldset>
		<legend class="search">', _('Contract Search'), '</legend>';

if (isset($_GET['ContractRef'])){
	$_POST['ContractRef']=$_GET['ContractRef'];
}
if (isset($_GET['SelectedCustomer'])){
	$_POST['SelectedCustomer']=$_GET['SelectedCustomer'];
}


if (isset($_POST['ContractRef']) AND $_POST['ContractRef']!='') {
	$_POST['ContractRef'] = trim($_POST['ContractRef']);
	echo _('Contract Reference') . ' - ' . $_POST['ContractRef'];
} else {
	if (isset($_POST['SelectedCustomer'])) {
		echo _('For customer') . ': ' . $_POST['SelectedCustomer'] . ' ' . _('and') . ' ';
		echo '<input type="hidden" name="SelectedCustomer" value="' . $_POST['SelectedCustomer'] . '" />';
	}
}

if (!isset($_POST['ContractRef']) or $_POST['ContractRef']==''){

	echo '<field>
			<label for="ContractRef">', _('Contract Reference') . ':</label>
			<input type="text" name="ContractRef" maxlength="20" size="20" />
		</field>';

	echo '<field>
			<label for="Status">', _('Search Contracts In'), '</label>
			<select name="Status">';

	if (isset($_GET['Status'])){
		$_POST['Status']=$_GET['Status'];
	}
	if (!isset($_POST['Status'])){
		$_POST['Status']=4;
	}

	$Statuses[] = _('Not Yet Quoted');
	$Statuses[] = _('Quoted - No Order Placed');
	$Statuses[] = _('Order Placed');
	$Statuses[] = _('Completed');
	$Statuses[] = _('All Contracts');

	$StatusCount = count($Statuses);

	for ( $i = 0; $i < $StatusCount; $i++ ) {
		if ( $i == $_POST['Status'] ) {
			echo '<option selected="selected" value="' . $i . '">' . $Statuses[$i] . '</option>';
		} else {
			echo '<option value="' . $i . '">' . $Statuses[$i] . '</option>';
		}
	}

	echo '</select>
		</field>';
}
echo '</fieldset>';
echo '<div class="centre">
		<input type="submit" name="SearchContracts" value="' . _('Search') . '" />';
echo '&nbsp;&nbsp;<a href="' . $RootPath . '/Contracts.php">' . _('New Contract') . '</a></div>';


//figure out the SQL required from the inputs available

if (isset($_POST['ContractRef']) AND $_POST['ContractRef'] !='') {
		$SQL = "SELECT contractref,
					   contractdescription,
					   categoryid,
					   contracts.debtorno,
					   debtorsmaster.name AS customername,
					   branchcode,
					   status,
					   orderno,
					   wo,
					   customerref,
					   requireddate
				FROM contracts INNER JOIN debtorsmaster
				ON contracts.debtorno = debtorsmaster.debtorno
				INNER JOIN locationusers ON locationusers.loccode=contracts.loccode AND locationusers.userid='" .  $_SESSION['UserID'] . "' AND locationusers.canview=1
				WHERE contractref " . LIKE . " '%" .  $_POST['ContractRef'] ."%'";

} else { //contractref not selected
	if (isset($_POST['SelectedCustomer'])) {

		$SQL = "SELECT contractref,
					   contractdescription,
					   categoryid,
					   contracts.debtorno,
					   debtorsmaster.name AS customername,
					   branchcode,
					   status,
					   orderno,
					   wo,
					   customerref,
					   requireddate
				FROM contracts INNER JOIN debtorsmaster
				ON contracts.debtorno = debtorsmaster.debtorno
				INNER JOIN locationusers ON locationusers.loccode=contracts.loccode AND locationusers.userid='" .  $_SESSION['UserID'] . "' AND locationusers.canview=1
				WHERE debtorno='". $_POST['SelectedCustomer'] ."'";
		if ($_POST['Status']!=4){
			$SQL .= " AND status='" . $_POST['Status'] . "'";
		}
	} else { //no customer selected
		$SQL = "SELECT contractref,
					   contractdescription,
					   categoryid,
					   contracts.debtorno,
					   debtorsmaster.name AS customername,
					   branchcode,
					   status,
					   orderno,
					   wo,
					   customerref,
					   requireddate
				FROM contracts INNER JOIN debtorsmaster
				ON contracts.debtorno = debtorsmaster.debtorno
				INNER JOIN locationusers ON locationusers.loccode=contracts.loccode AND locationusers.userid='" .  $_SESSION['UserID'] . "' AND locationusers.canview=1";
		if ($_POST['Status']!=4){
			$SQL .= " AND status='" . $_POST['Status'] . "'";
		}
	}
} //end not contract ref selected

$ErrMsg = _('No contracts were returned by the SQL because');
$ContractsResult = DB_query($SQL,$ErrMsg);

/*show a table of the contracts returned by the SQL */

echo '<table cellpadding="2" width="98%" class="selection">';

$TableHeader = '<tr>
					<th>' . _('Modify') . '</th>
					<th>' . _('Order') . '</th>
					<th>' . _('Issue To WO') . '</th>
					<th>' . _('Costing') . '</th>
					<th>' . _('Contract Ref') . '</th>
					<th>' . _('Description') . '</th>
					<th>' . _('Customer') . '</th>
					<th>' . _('Required Date') . '</th>
				</tr>';

echo $TableHeader;

$j = 1;

while ($MyRow=DB_fetch_array($ContractsResult)) {
	echo '<tr class="striped_row">';

	$ModifyPage = $RootPath . '/Contracts.php?ModifyContractRef=' . $MyRow['contractref'];
	$OrderModifyPage = $RootPath . '/SelectOrderItems.php?ModifyOrderNumber=' . $MyRow['orderno'];
	$IssueToWOPage = $RootPath . '/WorkOrderIssue.php?WO=' . $MyRow['wo'] . '&amp;StockID=' . $MyRow['contractref'];
	$CostingPage = $RootPath . '/ContractCosting.php?SelectedContract=' . $MyRow['contractref'];
	$FormatedRequiredDate = ConvertSQLDate($MyRow['requireddate']);

	if ($MyRow['status']==0 OR $MyRow['status']==1){ //still setting up the contract
		echo '<td><a href="' . $ModifyPage . '">' . _('Modify') . '</a></td>';
	} else {
		echo '<td>' . _('n/a') . '</td>';
	}
	if ($MyRow['status']==1 OR $MyRow['status']==2){ // quoted or ordered
		echo '<td><a href="' . $OrderModifyPage . '">' . $MyRow['orderno'] . '</a></td>';
	} else {
		echo '<td>' . _('n/a') . '</td>';
	}
	if ($MyRow['status']==2){ //the customer has accepted the quote but not completed contract yet
		echo '<td><a href="' . $IssueToWOPage . '">' . $MyRow['wo'] . '</a></td>';
	} else {
		echo '<td>' . _('n/a') . '</td>';
	}
	if ($MyRow['status']==2 OR $MyRow['status']==3){
			echo '<td><a href="' . $CostingPage . '">' . _('View') . '</a></td>';
		} else {
			echo '<td>' . _('n/a') . '</td>';
	}
	echo '<td>' . $MyRow['contractref'] . '</td>
		  <td>' . $MyRow['contractdescription'] . '</td>
		  <td>' . $MyRow['customername'] . '</td>
		  <td>' . $FormatedRequiredDate . '</td></tr>';

	$j++;
	if ($j == 12){
		$j=1;
		echo $TableHeader;
	}
//end of page full new headings if
}
//end of while loop

echo '</table>
      </div>
      </form>
      <br />';
include('includes/footer.php');
?>
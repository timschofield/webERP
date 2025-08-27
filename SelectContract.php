<?php

include('includes/session.php');

$Title = __('Select Contract');
$ViewTopic = 'Contracts';
$BookMark = 'SelectContract';
include('includes/header.php');

echo '<p class="page_title_text"><img alt="" src="', $RootPath, '/css/', $Theme,
	'/images/contract.png" title="', // Icon image.
	__('Contracts'), '" /> ', // Icon title.
	__('Select A Contract'), '</p>';// Page title.

echo '<form action="' . htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') . '" method="post">';
echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';

echo '<fieldset>
		<legend class="search">', __('Contract Search'), '</legend>';

if (isset($_GET['ContractRef'])){
	$_POST['ContractRef']=$_GET['ContractRef'];
}
if (isset($_GET['SelectedCustomer'])){
	$_POST['SelectedCustomer']=$_GET['SelectedCustomer'];
}


if (isset($_POST['ContractRef']) AND $_POST['ContractRef']!='') {
	$_POST['ContractRef'] = trim($_POST['ContractRef']);
	echo __('Contract Reference') . ' - ' . $_POST['ContractRef'];
} else {
	if (isset($_POST['SelectedCustomer'])) {
		echo __('For customer') . ': ' . $_POST['SelectedCustomer'] . ' ' . __('and') . ' ';
		echo '<input type="hidden" name="SelectedCustomer" value="' . $_POST['SelectedCustomer'] . '" />';
	}
}

if (!isset($_POST['ContractRef']) or $_POST['ContractRef']==''){

	echo '<field>
			<label for="ContractRef">', __('Contract Reference') . ':</label>
			<input type="text" name="ContractRef" maxlength="20" size="20" />
		</field>';

	echo '<field>
			<label for="Status">', __('Search Contracts In'), '</label>
			<select name="Status">';

	if (isset($_GET['Status'])){
		$_POST['Status']=$_GET['Status'];
	}
	if (!isset($_POST['Status'])){
		$_POST['Status']=4;
	}

	$Statuses[] = __('Not Yet Quoted');
	$Statuses[] = __('Quoted - No Order Placed');
	$Statuses[] = __('Order Placed');
	$Statuses[] = __('Completed');
	$Statuses[] = __('All Contracts');

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
		<input type="submit" name="SearchContracts" value="' . __('Search') . '" />';
echo '&nbsp;&nbsp;<a href="' . $RootPath . '/Contracts.php">' . __('New Contract') . '</a></div>';


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

$ErrMsg = __('No contracts were returned by the SQL because');
$ContractsResult = DB_query($SQL, $ErrMsg);

/*show a table of the contracts returned by the SQL */

echo '<table cellpadding="2" width="98%" class="selection">';

$TableHeader = '<tr>
					<th>' . __('Modify') . '</th>
					<th>' . __('Order') . '</th>
					<th>' . __('Issue To WO') . '</th>
					<th>' . __('Costing') . '</th>
					<th>' . __('Contract Ref') . '</th>
					<th>' . __('Description') . '</th>
					<th>' . __('Customer') . '</th>
					<th>' . __('Required Date') . '</th>
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
		echo '<td><a href="' . $ModifyPage . '">' . __('Modify') . '</a></td>';
	} else {
		echo '<td>' . __('n/a') . '</td>';
	}
	if ($MyRow['status']==1 OR $MyRow['status']==2){ // quoted or ordered
		echo '<td><a href="' . $OrderModifyPage . '">' . $MyRow['orderno'] . '</a></td>';
	} else {
		echo '<td>' . __('n/a') . '</td>';
	}
	if ($MyRow['status']==2){ //the customer has accepted the quote but not completed contract yet
		echo '<td><a href="' . $IssueToWOPage . '">' . $MyRow['wo'] . '</a></td>';
	} else {
		echo '<td>' . __('n/a') . '</td>';
	}
	if ($MyRow['status']==2 OR $MyRow['status']==3){
			echo '<td><a href="' . $CostingPage . '">' . __('View') . '</a></td>';
		} else {
			echo '<td>' . __('n/a') . '</td>';
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

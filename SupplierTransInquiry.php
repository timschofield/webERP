<?php

require(__DIR__ . '/includes/session.php');

$Title = __('Supplier Transactions Inquiry');
$ViewTopic = 'AccountsPayable';
$BookMark = '';
include('includes/header.php');

if (isset($_POST['FromDate'])){$_POST['FromDate'] = ConvertSQLDate($_POST['FromDate']);}
if (isset($_POST['ToDate'])){$_POST['ToDate'] = ConvertSQLDate($_POST['ToDate']);}

echo '<p class="page_title_text">
		<img src="'.$RootPath.'/css/'.$Theme.'/images/supplier.png" title="' . __('Search') .
	'" alt="" />' . ' ' . $Title . '
	</p>';

echo '<form action="' . htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') . '" method="post">';
echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';

echo '<fieldset>
		<legend>', __('Inquiry Criteria'), '</legend>
		<field>
			<label for="TransType">' . __('Type') . ':</label>
			<select name="TransType">';

$SQL = "SELECT typeid,
				typename
		FROM systypes
		WHERE typeid >= 20
		AND typeid <= 23";

$ResultTypes = DB_query($SQL);

echo '<option value="All">' .__('All') . '</option>';
while ($MyRow=DB_fetch_array($ResultTypes)){
	if (isset($_POST['TransType'])){
		if ($MyRow['typeid'] == $_POST['TransType']){
		     echo '<option selected="selected" value="' . $MyRow['typeid'] . '">' . $MyRow['typename'] . '</option>';
		} else {
		     echo '<option value="' . $MyRow['typeid'] . '">' . $MyRow['typename'] . '</option>';
		}
	} else {
		     echo '<option value="' . $MyRow['typeid'] . '">' . $MyRow['typename'] . '</option>';
	}
}
echo '</select>
	</field>';

if (!isset($_POST['FromDate'])){
	$_POST['FromDate']=Date($_SESSION['DefaultDateFormat'], mktime(0,0,0,Date('m'),1,Date('Y')));
}
if (!isset($_POST['ToDate'])){
	$_POST['ToDate'] = Date($_SESSION['DefaultDateFormat']);
}
if (!isset($_POST['SupplierNo'])) {
	$_POST['SupplierNo'] = '';
}
echo '<field>
		<label for="FromDate">' . __('From Date') . ':</label>
		<input type="date" name="FromDate" maxlength="10" size="11" value="' . FormatDateForSQL($_POST['FromDate']) . '" />
	</field>
	<field>
		<label for="ToDate">' . __('To Date') . ':</label>
		<input type="date" name="ToDate" maxlength="10" size="11" value="' . FormatDateForSQL($_POST['ToDate']) . '" />
	</field>
	<field>
		<label for="SupplierNo">' . __('Supplier No') . ':</label>
		<input type="text" name="SupplierNo" size="11" maxlength="10" value="' . $_POST['SupplierNo'] . '" />
	</field>
	</fieldset>
	<div class="centre">
		<input type="submit" name="ShowResults" value="' . __('Show transactions') . '" />
	</div>
	</form>';

if (isset($_POST['ShowResults']) && $_POST['TransType'] != ''){
   $SQL_FromDate = FormatDateForSQL($_POST['FromDate']);
   $SQL_ToDate = FormatDateForSQL($_POST['ToDate']);
   $SQL = "SELECT type,
				transno,
		   		trandate,
				duedate,
				supplierno,
				suppname,
				suppreference,
				transtext,
				supptrans.rate,
				diffonexch,
				alloc,
				ovamount+ovgst as totalamt,
				currcode,
				typename,
				decimalplaces AS currdecimalplaces
			FROM supptrans
			INNER JOIN suppliers ON supptrans.supplierno=suppliers.supplierid
			INNER JOIN systypes ON supptrans.type = systypes.typeid
			INNER JOIN currencies ON suppliers.currcode=currencies.currabrev
			WHERE ";

   $SQL = $SQL . "trandate >='" . $SQL_FromDate . "' AND trandate <= '" . $SQL_ToDate . "'";
	if  ($_POST['TransType']!='All')  {
		$SQL .= " AND type = " . $_POST['TransType'];
	}
	if($_POST['SupplierNo'] != "")
	{
		$SQL .= " AND supptrans.supplierno LIKE '%".$_POST['SupplierNo']."%'";
	}
	$SQL .=  " ORDER BY id";

   $TransResult = DB_query($SQL);
   $ErrMsg = __('The supplier transactions for the selected criteria could not be retrieved because') . ' - ' . DB_error_msg();

   echo '<table class="selection">';

	echo '<tr>
			<th>' . __('Type') . '</th>
			<th>' . __('Number') . '</th>
			<th>' . __('Supp Ref') . '</th>
			<th>' . __('Date') . '</th>
			<th>' . __('Supplier') . '</th>
			<th>' . __('Comments') . '</th>
			<th>' . __('Due Date') . '</th>
			<th>' . __('Ex Rate') . '</th>
			<th>' . __('Amount') . '</th>
			<th>' . __('Currency') . '</th>
		</tr>';

	$RowCounter = 1;

	while ($MyRow=DB_fetch_array($TransResult)) {

		echo '<tr class="striped_row">
				<td>', $MyRow['typename'], '</td>
				<td>', $MyRow['transno'], '</td>
				<td>', $MyRow['suppreference'], '</td>
				<td>', ConvertSQLDate($MyRow['trandate']), '</td>
				<td>', $MyRow['supplierno'] . ' - ' . $MyRow['suppname'], '</td>
				<td>', $MyRow['transtext'], '</td>
				<td>', ConvertSQLDate($MyRow['duedate']), '</td>
				<td class="number">', locale_number_format($MyRow['rate'],'Variable'), '</td>
				<td class="number">', locale_number_format($MyRow['totalamt'],$MyRow['currdecimalplaces']), '</td>
				<td>', $MyRow['currcode'], '</td>
			</tr>';


		$GLTransResult = DB_query("SELECT account,
										accountname,
										narrative,
										amount
									FROM gltrans INNER JOIN chartmaster
									ON gltrans.account=chartmaster.accountcode
									WHERE type='" . $MyRow['type'] . "'
									AND typeno='" . $MyRow['transno'] . "'",
									__('Could not retrieve the GL transactions for this AP transaction'));

		if (DB_num_rows($GLTransResult)==0){
			echo '<tr>
					<td colspan="10">' . __('There are no GL transactions created for the above AP transaction') . '</td>
				</tr>';
		} else {
			echo '<tr>
					<td colspan="1"></td>
					<td colspan="8">
						<table style="float:left" width="100%">';
			echo '<tr>
					<th colspan="2"><b>' . __('GL Account') . '</b></th>
					<th><b>' . __('Local Amount') . '</b></th>
					<th><b>' . __('Narrative') . '</b></th>
				</tr>';
			$CheckGLTransBalance =0;
			while ($GLTransRow = DB_fetch_array($GLTransResult)){

				echo '<tr class="striped_row">
						<td>', $GLTransRow['account'], '</td>
						<td>', $GLTransRow['accountname'], '</td>
						<td class="number">', locale_number_format($GLTransRow['amount'],$_SESSION['CompanyRecord']['decimalplaces']), '</td>
						<td>', $GLTransRow['narrative'], '</td>
					</tr>';

				$CheckGLTransBalance += $GLTransRow['amount'];
			}
			if (round($CheckGLTransBalance,5)!= 0){
				echo '<tr>
						<td colspan="4" style="background-color:red"><b>' . __('The GL transactions for this AP transaction are out of balance by') .  ' ' . $CheckGLTransBalance . '</b></td>
					</tr>';
			}
			echo '</table></td></tr>';
		}

		$RowCounter++;
		if ($RowCounter == 12){
			$RowCounter=1;
			echo $Tableheader;
		}
	//end of page full new headings if
	}
	//end of while loop

 echo '</table>';
}
include('includes/footer.php');

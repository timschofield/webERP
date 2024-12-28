<?php


include('includes/session.php');
$Title = _('Supplier Transactions Inquiry');
include('includes/header.php');

echo '<p class="page_title_text">
		<img src="'.$RootPath.'/css/'.$Theme.'/images/supplier.png" title="' . _('Search') .
	'" alt="" />' . ' ' . $Title . '
	</p>';

echo '<form action="' . htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') . '" method="post">';
echo '<div>';
echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';

echo '<table class="selection">
		<tr>
			<td>' . _('Type') . ':</td>
			<td><select name="TransType">';

$SQL = "SELECT typeid,
				typename
		FROM systypes
		WHERE typeid >= 20
		AND typeid <= 23";

$ResultTypes = DB_query($SQL);

echo '<option value="All">' ._('All') . '</option>';
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
echo '</select></td>';

if (!isset($_POST['FromDate'])){
	$_POST['FromDate']=Date($_SESSION['DefaultDateFormat'], mktime(0,0,0,Date('m'),1,Date('Y')));
}
if (!isset($_POST['ToDate'])){
	$_POST['ToDate'] = Date($_SESSION['DefaultDateFormat']);
}
if (!isset($_POST['SupplierNo'])) {
	$_POST['SupplierNo'] = '';
}
echo '<td>' . _('From') . ':</td>
		<td><input type="text" class="date" name="FromDate" maxlength="10" size="11" value="' . $_POST['FromDate'] . '" /></td>
		<td>' . _('To') . ':</td>
		<td><input type="text" class="date" name="ToDate" maxlength="10" size="11" value="' . $_POST['ToDate'] . '" /></td>
		<td>' . _('Supplier No') . ':</td>
		<td><input type="text" name="SupplierNo" size="11" maxlength="10" value="' . $_POST['SupplierNo'] . '" />
		</td>
	</tr>
	</table>
	<br />
	<div class="centre">
		<input type="submit" name="ShowResults" value="' . _('Show transactions') . '" />
	</div>
	<br />
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
   $ErrMsg = _('The supplier transactions for the selected criteria could not be retrieved because') . ' - ' . DB_error_msg();
   $DbgMsg =  _('The SQL that failed was');

   echo '<table class="selection">';

   $Tableheader = '<tr>
					<th>' . _('Type') . '</th>
					<th>' . _('Number') . '</th>
					<th>' . _('Supp Ref') . '</th>
					<th>' . _('Date') . '</th>
					<th>' . _('Supplier') . '</th>
					<th>' . _('Comments') . '</th>
					<th>' . _('Due Date') . '</th>
					<th>' . _('Ex Rate') . '</th>
					<th>' . _('Amount') . '</th>
					<th>' . _('Currency') . '</th>
				</tr>';
	echo $Tableheader;

	$RowCounter = 1;

	while ($MyRow=DB_fetch_array($TransResult)) {

		printf ('<tr class="striped_row">
				<td>%s</td>
				<td>%s</td>
				<td>%s</td>
				<td>%s</td>
				<td>%s</td>
				<td>%s</td>
				<td>%s</td>
				<td class="number">%s</td>
				<td class="number">%s</td>
				<td>%s</td>
				</tr>',
				$MyRow['typename'],
				$MyRow['transno'],
				$MyRow['suppreference'],
				ConvertSQLDate($MyRow['trandate']),
				$MyRow['supplierno'] . ' - ' . $MyRow['suppname'],
				$MyRow['transtext'],
				ConvertSQLDate($MyRow['duedate']),
				locale_number_format($MyRow['rate'],'Variable'),
				locale_number_format($MyRow['totalamt'],$MyRow['currdecimalplaces']),
				$MyRow['currcode']);


		$GLTransResult = DB_query("SELECT account,
										accountname,
										narrative,
										amount
									FROM gltrans INNER JOIN chartmaster
									ON gltrans.account=chartmaster.accountcode
									WHERE type='" . $MyRow['type'] . "'
									AND typeno='" . $MyRow['transno'] . "'",
									_('Could not retrieve the GL transactions for this AP transaction'));

		if (DB_num_rows($GLTransResult)==0){
			echo '<tr>
					<td colspan="10">' . _('There are no GL transactions created for the above AP transaction') . '</td>
				</tr>';
		} else {
			echo '<tr>
					<td colspan="2"></td>
					<td colspan="8">
						<table class="selection" width="100%">';
			echo '<tr>
					<th colspan="2"><b>' . _('GL Account') . '</b></th>
					<th><b>' . _('Local Amount') . '</b></th>
					<th><b>' . _('Narrative') . '</b></th>
				</tr>';
			$CheckGLTransBalance =0;
			while ($GLTransRow = DB_fetch_array($GLTransResult)){

				printf('<tr>
						<td>%s</td>
						<td>%s</td>
						<td class="number">%s</td>
						<td>%s</td>
						</tr>',
						$GLTransRow['account'],
						$GLTransRow['accountname'],
						locale_number_format($GLTransRow['amount'],$_SESSION['CompanyRecord']['decimalplaces']),
						$GLTransRow['narrative']);

				$CheckGLTransBalance += $GLTransRow['amount'];
			}
			if (round($CheckGLTransBalance,5)!= 0){
				echo '<tr>
						<td colspan="4" style="background-color:red"><b>' . _('The GL transactions for this AP transaction are out of balance by') .  ' ' . $CheckGLTransBalance . '</b></td>
					</tr>';
			}
			echo '</table></td></tr>';
		}

		$RowCounter++;
		If ($RowCounter == 12){
			$RowCounter=1;
			echo $Tableheader;
		}
	//end of page full new headings if
	}
	//end of while loop

 echo '</table>';
}
include('includes/footer.php');
?>

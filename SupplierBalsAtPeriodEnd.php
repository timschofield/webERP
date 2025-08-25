<?php

include('includes/session.php');

if (isset($_POST['PrintPDF'])
	AND isset($_POST['FromCriteria'])
	AND mb_strlen($_POST['FromCriteria'])>=1
	AND isset($_POST['ToCriteria'])
	AND mb_strlen($_POST['ToCriteria'])>=1){

	include('includes/PDFStarter.php');

	$pdf->addInfo('Title',__('Supplier Balance Listing'));
	$pdf->addInfo('Subject',__('Supplier Balances'));

	$FontSize=12;
	$PageNumber=0;
	$LineHeight=12;

	  /*Now figure out the aged analysis for the Supplier range under review */

	$SQL = "SELECT suppliers.supplierid,
					suppliers.suppname,
		  			currencies.currency,
		  			currencies.decimalplaces AS currdecimalplaces,
					SUM((supptrans.ovamount + supptrans.ovgst - supptrans.alloc)/supptrans.rate) AS balance,
					SUM(supptrans.ovamount + supptrans.ovgst - supptrans.alloc) AS fxbalance,
					SUM(CASE WHEN supptrans.trandate > '" . $_POST['PeriodEnd'] . "' THEN
			(supptrans.ovamount + supptrans.ovgst)/supptrans.rate ELSE 0 END) AS afterdatetrans,
					SUM(CASE WHEN supptrans.trandate > '" . $_POST['PeriodEnd'] . "'
						AND (supptrans.type=22 OR supptrans.type=21) THEN
						supptrans.diffonexch ELSE 0 END) AS afterdatediffonexch,
					SUM(CASE WHEN supptrans.trandate > '" . $_POST['PeriodEnd'] . "' THEN
						supptrans.ovamount + supptrans.ovgst ELSE 0 END) AS fxafterdatetrans
			FROM suppliers INNER JOIN currencies
			ON suppliers.currcode = currencies.currabrev
			INNER JOIN supptrans
			ON suppliers.supplierid = supptrans.supplierno
			WHERE suppliers.supplierid >= '" . $_POST['FromCriteria'] . "'
			AND suppliers.supplierid <= '" . $_POST['ToCriteria'] . "'
			GROUP BY suppliers.supplierid,
				suppliers.suppname,
				currencies.currency,
				currencies.decimalplaces";

	$ErrMsg = __('The Supplier details could not be retrieved');
	$SupplierResult = DB_query($SQL, $ErrMsg);

	if (DB_num_rows($SupplierResult) ==0) {
		$Title = __('Supplier Balances - Problem Report');
		include('includes/header.php');
		prnMsg(__('There are no supplier balances to list'),'error');
		echo '<br /><a href="' . $RootPath . '/index.php">' . __('Back to the menu') . '</a>';
		include('includes/footer.php');
		exit();
	}

	include('includes/PDFSupplierBalsPageHeader.php');

	$TotBal=0;

	while ($SupplierBalances = DB_fetch_array($SupplierResult)){

		$Balance = $SupplierBalances['balance'] - $SupplierBalances['afterdatetrans'] + $SupplierBalances['afterdatediffonexch'];
		$FXBalance = $SupplierBalances['fxbalance'] - $SupplierBalances['fxafterdatetrans'];

		if (ABS($Balance)>0.009 OR ABS($FXBalance)>0.009) {
			$DisplayBalance = locale_number_format($SupplierBalances['balance'] - $SupplierBalances['afterdatetrans'] + $SupplierBalances['afterdatediffonexch'],$_SESSION['CompanyRecord']['decimalplaces']);
			$DisplayFXBalance = locale_number_format($SupplierBalances['fxbalance'] - $SupplierBalances['fxafterdatetrans'],$SupplierBalances['currdecimalplaces']);

			$TotBal += $Balance;

			$pdf->addTextWrap($Left_Margin,$YPos,220-$Left_Margin,$FontSize,$SupplierBalances['supplierid'] . ' - ' . $SupplierBalances['suppname'],'left');
			$pdf->addTextWrap(220,$YPos,60,$FontSize,$DisplayBalance,'right');
			$pdf->addTextWrap(280,$YPos,60,$FontSize,$DisplayFXBalance,'right');
			$pdf->addTextWrap(350,$YPos,100,$FontSize,$SupplierBalances['currency'],'left');

			$YPos -=$LineHeight;
			if ($YPos < $Bottom_Margin + $LineHeight){
			include('includes/PDFSupplierBalsPageHeader.php');
			}
		}
	} /*end Supplier aged analysis while loop */

	$YPos -=$LineHeight;
	if ($YPos < $Bottom_Margin + (2*$LineHeight)){
		$PageNumber++;
		include('includes/PDFSupplierBalsPageHeader.php');
	}

	$DisplayTotBalance = locale_number_format($TotBal,$_SESSION['CompanyRecord']['decimalplaces']);

	$pdf->addTextWrap(220,$YPos,60,$FontSize,$DisplayTotBalance,'right');

	$pdf->OutputD($_SESSION['DatabaseName'] . '_Supplier_Balances_at_Period_End_' . Date('Y-m-d') . '.pdf');
	$pdf->__destruct();

} else { /*The option to print PDF was not hit */

	$Title=__('Supplier Balances At A Period End');
$ViewTopic = 'AccountsPayable';
$BookMark = '';
	include('includes/header.php');

	echo '<p class="page_title_text"><img src="'.$RootPath.'/css/'.$Theme.'/images/transactions.png" title="' .
		__('Supplier Allocations') . '" alt="" />' . ' ' . $Title . '</p>';
	if (!isset($_POST['FromCriteria'])) {
		$_POST['FromCriteria'] = '1';
	}
	if (!isset($_POST['ToCriteria'])) {
		$_POST['ToCriteria'] = 'zzzzzz';
	}
	/*if $FromCriteria is not set then show a form to allow input	*/

	echo '<form action="' . htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') . '" method="post">';
	echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';

	echo '<fieldset>
			<legend>', __('Report Criteria'), '</legend>';
	echo '<field>
			<label for="FromCriteria">' . __('From Supplier Code') . ':</label>
			<input type="text" maxlength="6" size="7" name="FromCriteria" value="'.$_POST['FromCriteria'].'" />
		</field>
		<field>
			<label for="ToCriteria">' . __('To Supplier Code') . ':</label>
			<input type="text" maxlength="6" size="7" name="ToCriteria" value="'.$_POST['ToCriteria'].'" />
		</field>
		<field>
			<label for="PeriodEnd">' . __('Balances As At') . ':</label>
			<select name="PeriodEnd">';

	$SQL = "SELECT periodno,
					lastdate_in_period
			FROM periods
			ORDER BY periodno DESC";

	$ErrMsg = __('Could not retrieve period data because');
	$Periods = DB_query($SQL, $ErrMsg);

	while ($MyRow = DB_fetch_array($Periods)){
		echo '<option value="' . $MyRow['lastdate_in_period'] . '" selected="selected" >' . MonthAndYearFromSQLDate($MyRow['lastdate_in_period'],'M',-1) . '</option>';
	}
	echo '</select>
		</field>';

	echo '</fieldset>
			<div class="centre">
				<input type="submit" name="PrintPDF" value="' . __('Print PDF') . '" />
			</div>';
	echo '</form>';
	include('includes/footer.php');
}/*end of else not PrintPDF */

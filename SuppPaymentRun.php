<?php
/* $Revision: 1.5 $ */

$PageSecurity = 5;

include('includes/SQL_CommonFunctions.inc');

If ((isset($_POST['PrintPDF']) OR isset($_POST['PrintPDFAndProcess']))
	AND isset($_POST['FromCriteria'])
	AND strlen($_POST['FromCriteria'])>=1
	AND isset($_POST['ToCriteria'])
	AND strlen($_POST['ToCriteria'])>=1
	AND is_numeric($_POST['ExRate'])){

/*then print the report */

	include('config.php');
	include('includes/ConnectDB.inc');

	include('includes/PDFStarter_ros.inc');
	include('includes/DateFunctions.inc');


	$CompanyRecord = ReadInCompanyRecord($db);
	$RefCounter = 0;


	$pdf->addinfo('Title',_('Payment Run Report'));
	$pdf->addinfo('Subject',_('Payment Run') . ' - ' . _('suppliers from') . ' ' . $_POST['FromCriteria'] . ' to ' . $_POST['ToCriteria'] . ' in ' . $_POST['Currency'] . ' ' . _('and Due By') . ' ' .  $_POST['AmountsDueBy']);

	$PageNumber=1;
	$line_height=12;

  /*Now figure out the invoice less credits due for the Supplier range under review */

	include ('includes/PDFPaymentRunPageHeader.inc');

	$sql = "SELECT Suppliers.SupplierID, SUM(SuppTrans.OvAmount + SuppTrans.OvGST - SuppTrans.Alloc) AS Balance
		FROM Suppliers, PaymentTerms, SuppTrans, SysTypes
		WHERE SysTypes.TypeID = SuppTrans.Type AND Suppliers.PaymentTerms = PaymentTerms.TermsIndicator AND 			Suppliers.SupplierID = SuppTrans.SupplierNo
		AND SuppTrans.OvAmount + SuppTrans.OvGST - SuppTrans.Alloc !=0
		AND SuppTrans.DueDate <='" . FormatDateForSQL($_POST['AmountsDueBy']) . "' AND SuppTrans.Hold=0 AND 			Suppliers.CurrCode = '" . $_POST['Currency'] . "'
		AND SuppTrans.SupplierNo >= '" . $_POST['FromCriteria'] . "' AND SuppTrans.SupplierNo <= '" . 				$_POST['ToCriteria'] . "'
		GROUP BY Suppliers.SupplierID
		HAVING SUM(SuppTrans.OvAmount + SuppTrans.OvGST - SuppTrans.Alloc) > 0
		ORDER BY Suppliers.SupplierID";

	$SuppliersResult = DB_query($sql,$db);

	$SupplierID ='';
	$TotalPayments = 0;
	$TotalAccumDiffOnExch = 0;


	if (isset($_POST['PrintPDFAndProcess'])){
		$ProcessResult = DB_query('begin',$db);
	}

	while ($SuppliersToPay = DB_fetch_array($SuppliersResult)){

		$sql = "SELECT Suppliers.SupplierID,
				Suppliers.SuppName,
				SysTypes.TypeName,
				PaymentTerms.Terms,
				SuppTrans.SuppReference,
				SuppTrans.TranDate,
				SuppTrans.Rate,
				SuppTrans.TransNo,
				SuppTrans.Type,
				(SuppTrans.OvAmount + SuppTrans.OvGST - SuppTrans.Alloc) AS Balance,
				(SuppTrans.OvAmount + SuppTrans.OvGST ) AS TranTotal,
				SuppTrans.DiffOnExch
			FROM Suppliers,
				PaymentTerms,
				SuppTrans,
				SysTypes
			WHERE SysTypes.TypeID = SuppTrans.Type
			AND Suppliers.PaymentTerms = PaymentTerms.TermsIndicator
			AND Suppliers.SupplierID = SuppTrans.SupplierNo
			AND SuppTrans.SupplierNo = '" . $SuppliersToPay['SupplierID'] . "'
			AND SuppTrans.OvAmount + SuppTrans.OvGST - SuppTrans.Alloc !=0
			AND SuppTrans.DueDate <='" . FormatDateForSQL($_POST['AmountsDueBy']) . "'
			AND SuppTrans.Hold=0
			AND Suppliers.CurrCode = '" . $_POST['Currency'] . "'
			AND SuppTrans.SupplierNo >= '" . $_POST['FromCriteria'] . "'
			AND SuppTrans.SupplierNo <= '" . $_POST['ToCriteria'] . "'
			ORDER BY SuppTrans.SupplierNo,
				SuppTrans.Type,
				SuppTrans.TransNo";

		$TransResult = DB_query($sql,$db,'','',false,false);
		if (DB_error_no($db) !=0) {
			$title = _('Payment Run') . ' - ' . _('Problem Report') . '.... ';
			include('includes/header.inc');
			echo '<BR>' . _('The details of supplier invoices due could not be retrieved because') . ' - ' . DB_error_msg($db);
			echo '<BR><A HREF="' . $rootpath . '/index.php">' . _('Back to the menu') . '</A>';

			if ($debug==1){
				echo '<BR>' . _('The SQL that failed was') . ' ' . $sql;
			}
			include('includes/footer.inc');
			exit;
		}

		while ($DetailTrans = DB_fetch_array($TransResult)){

			if ($DetailTrans['SupplierID'] != $SupplierID){ /*Need to head up for a new suppliers details */

				if ($SupplierID!=''){ /*only print the footer if this is not the first pass */
					include('includes/PDFPaymentRun_PymtFooter.php');
				}
				$SupplierID = $DetailTrans['SupplierID'];
				$SupplierName = $DetailTrans['SuppName'];
				if (isset($_POST['PrintPDFAndProcess'])){
					$SuppPaymentNo = GetNextTransNo(22, $db);
				}
				$AccumBalance = 0;
				$AccumDiffOnExch = 0;
				$LeftOvers = $pdf->addTextWrap($Left_Margin, $YPos, 450-$Left_Margin,$FontSize,$DetailTrans['SupplierID'] . ' - ' . $DetailTrans['SuppName'] . ' - ' . 				$DetailTrans['Terms'], 'left');

				$YPos -= $line_height;
			}

			$DislayTranDate = ConvertSQLDate($DetailTrans['TranDate']);

			$LeftOvers = $pdf->addTextWrap($Left_Margin+15, $YPos, 340-$Left_Margin,$FontSize,$DislayTranDate . ' - ' . $DetailTrans['TypeName'] . ' - ' . $DetailTrans['SuppReference'], 'left');

			/*Positive is a favourable */
			$DiffOnExch = ($DetailTrans['Balance'] / $DetailTrans['Rate']) -  ($DetailTrans['Balance'] / $_POST['ExRate']);

			$AccumBalance += $DetailTrans['Balance'];
			$AccumDiffOnExch += $DiffOnExch;


			if (isset($_POST['PrintPDFAndProcess'])){

			/*Do the inserts for the allocation record against the payment for this charge */

				$SQL = 'INSERT INTO SuppAllocs (TypeNo, TransNo, Amt, PaytNo, PaytTypeNo) ';
				$SQL = $SQL .  'VALUES (' . $DetailTrans['Type'] . ',
                                ' . $DetailTrans['TransNo'] . ',
                                ' . $DetailTrans['Balance'] . ',
                                ' . $SuppPaymentNo . ', 22)';
				$ProcessResult = DB_query($SQL,$db);
				if (DB_error_no($db) !=0) {
					$title = _('Payment Processing - Problem Report') . '.... ';
					include('includes/header.inc');
					echo '<BR>' . _('None of the payments will be processed since an allocation record for') . $SupplierName . _('could not be inserted because') . ' - ' . DB_error_msg($db);
					echo '<BR><A HREF="' . $rootpath . '/index.php">' . _('Back to the menu') . '</A>';
					if ($debug==1){
						echo '<BR>' . _('The SQL that failed was') . $SQL;
					}
					$SQL= 'rollback';
					$ProcessResult = DB_query($SQL,$db);

					include('includes/footer.inc');
					exit;
				}

				/*Now update the SuppTrans for the allocation made and the fact that it is now settled */

				$SQL = "UPDATE SuppTrans SET Settled = 1,
                                     				Alloc = " . $DetailTrans['TranTotal'] . ",
                                     				DiffOnExch = " . ($DetailTrans['DiffOnExch'] + $DiffOnExch)  . "
                                 	WHERE Type = " . $DetailTrans['Type'] . '
					AND TransNo = ' . $DetailTrans['TransNo'];

				$ProcessResult = DB_query($SQL,$db,'','',false,false);
				if (DB_error_no($db) !=0) {
					$title = _('Payment Processing - Problem Report') . '.... ';
					include('includes/header.inc');
					echo '<BR>' . _('None of the payments will be processed since updates to the transaction records for') . ' ' .$SupplierName . ' ' . _('could not be processed because') . ' - ' . DB_error_msg($db);
					echo '<BR><A HREF="' . $rootpath . '/index.php">' . _('Back to the menu') . '</A>';
					if ($debug==1){
						echo '<BR>' . _('The SQL that failed was') . $SQL;
					}
					$SQL= 'rollback';
					$ProcessResult = DB_query($SQL,$db);

					include('includes/footer.inc');
					exit;
				}
			}

			$LeftOvers = $pdf->addTextWrap(340, $YPos,60,$FontSize,number_format($DetailTrans["Balance"],2), 'right');
			$LeftOvers = $pdf->addTextWrap(405, $YPos,60,$FontSize,number_format($DiffOnExch,2), 'right');

			$YPos -=$line_height;
			if ($YPos < $Bottom_Margin + $line_height){
				$PageNumber++;
				include('includes/PDFPaymentRunPageHeader.inc');
			}
		} /*end while there are detail transactions to show */
	} /* end while there are suppliers to retrieve transactions for */

	if ($SupplierID!=''){

		include('includes/PDFPaymentRun_PymtFooter.php');

		$ProcessResult = DB_query('Commit',$db,'','',false,false);
		if (DB_error_no($db) !=0) {
			$title = _('Payment Processing - Problem Report') . '.... ';
			include('includes/header.inc');
			echo '<BR>' . _('None of the payments will be processed') . '. ' . _('Unfortunately there was a problem committing the changes to the database because') . ' - ' . DB_error_msg($db);
			echo '<BR><A HREF="' . $rootpath . '/index.php">' . _('Back to the menu') . '</A>';
			if ($debug==1){
				echo '<BR>' . _('The SQL that failed was') . $SQL;
			}
			$SQL= 'rollback';
			$ProcessResult = DB_query($SQL,$db);
			include('includes/footer.inc');
			exit;
		}

		$LeftOvers = $pdf->addTextWrap($Left_Margin, $YPos, 340-$Left_Margin,$FontSize,_('Grand Total Payments Due'), 'left');
		$LeftOvers = $pdf->addTextWrap(340, $YPos, 60,$FontSize,number_format($TotalPayments,2), 'right');
		$LeftOvers = $pdf->addTextWrap(405, $YPos, 60,$FontSize,number_format($TotalAccumDiffOnExch,2), 'right');

	}

	$pdfcode = $pdf->output();
	$len = strlen($pdfcode);
	header('Content-type: application/pdf');
	header('Content-Length: ' . $len);
	header('Content-Disposition: inline; filename=PaymentRun.pdf');
	header('Expires: 0');
	header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
	header('Pragma: public');

	$pdf->stream();

	header('Expires: 0');
	header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
	header('Pragma: public');


} else { /*The option to print PDF was not hit */

	include('includes/session.inc');
	$title=_('Payment Run');
	include('includes/header.inc');
	$CompanyRecord = ReadInCompanyRecord($db);

	if (isset($_POST['Currency']) AND !is_numeric($_POST['ExRate'])){
		echo '<BR>' . _('To process payments for') . ' ' . $_POST['Currency'] . ' ' . _('a numeric exchange rate applicable for purchasing the currency to make the payment with must be entered') . '. ' . _('This rate is used to calculate the difference in exchange and make the necessary postings to the General ledger if linked') . '.';
	}

	/* show form to allow input	*/

	echo '<FORM ACTION="' . $_SERVER['PHP_SELF'] . '?' . SID . '" METHOD="POST"><CENTER><TABLE>';

	if (strlen($_POST['FromCriteria'])<1){
		$DefaultFromCriteria = '1';
	} else {
		$DefaultFromCriteria = $_POST['FromCriteria'];
	}
	if (strlen($_POST['ToCriteria'])<1){
		$DefaultToCriteria = 'zzzzzzz';
	} else {
		$DefaultToCriteria = $_POST['ToCriteria'];
	}
	echo '<TR><TD>' . _('From Supplier Code') . ':</FONT></TD>
            <TD><input Type=text maxlength=6 size=7 name=FromCriteria value="' . $DefaultFromCriteria . '"></TD></TR>';
	echo '<TR><TD>' . _('To Supplier Code') . ':</TD>
            <TD><input Type=text maxlength=6 size=7 name=ToCriteria value="' . $DefaultToCriteria . '"></TD></TR>';


	echo '<TR><TD>' . _('For Suppliers Trading in') . ':</TD><TD><SELECT name="Currency">';
	$sql = 'SELECT Currency, CurrAbrev FROM Currencies';
	$result=DB_query($sql,$db);

	while ($myrow=DB_fetch_array($result)){
	if ($myrow['CurrAbrev'] == $CompanyRecord['CurrencyDefault']){
			echo '<OPTION SELECTED Value="' . $myrow['CurrAbrev'] . '">' . $myrow['Currency'];
	} else {
		echo '<OPTION Value="' . $myrow['CurrAbrev'] . '">' . $myrow['Currency'];
	}
	}
	echo '</SELECT></TD></TR>';

	if (!is_numeric($_POST['ExRate'])){
		$DefaultExRate = '1';
	} else {
		$DefaultExRate = $_POST['ExRate'];
	}
	echo '<TR><TD>' . _('Exchange Rate') . ':</TD>
            <TD><INPUT TYPE=text name="ExRate" MAXLENGTH=11 SIZE=12 VALUE=' . $DefaultExRate . '></TD></TR>';

	if (!isset($_POST['AmountsDueBy'])){
		$DefaultDate = Date($DefaultDateFormat, Mktime(0,0,0,Date('m')+1,0 ,Date('y')));
	} else {
		$DefaultDate = $_POST['AmountsDueBy'];
	}

	echo '<TR><TD>' . _('Payments Due To') . ':</TD>
            <TD><INPUT TYPE=text name="AmountsDueBy" MAXLENGTH=11 SIZE=12 VALUE=' . $DefaultDate . '></TD></TR>';

	$SQL = 'SELECT BankAccountName, AccountCode FROM BankAccounts';

	$AccountsResults = DB_query($SQL,$db,'','',false,false);

	if (DB_error_no($db) !=0) {
		 echo '<BR>' . _('The bank accounts could not be retrieved by the SQL because') . ' - ' . DB_error_msg($db);
		 if ($debug==1){
			echo '<BR>' . _('The SQL used to retrieve the bank acconts was') . ':<BR>' . $SQL;
		 }
		 exit;
	}

	echo '<TR><TD>' . _('Pay From Account') . ':</TD><TD><SELECT name="BankAccount">';

	if (DB_num_rows($AccountsResults)==0){
		 echo '</SELECT></TD></TR></TABLE><P>' . _('Bank Accounts have not yet been defined. You must first') . ' <A HREF="' . $rootpath . '/BankAccounts.php">' . _('define the bank accounts') . '</A> ' . _('and general ledger accounts to be affected') . '.';
		 exit;
	} else {
		while ($myrow=DB_fetch_array($AccountsResults)){
		      /*list the bank account names */

			if ($_POST['BankAccount']==$myrow['AccountCode']){
				echo '<OPTION SELECTED VALUE="' . $myrow['AccountCode'] . '">' . $myrow['BankAccountName'];
			} else {
				echo '<OPTION VALUE="' . $myrow['AccountCode'] . '">' . $myrow['BankAccountName'];
			}
		}
		echo '</SELECT></TD></TR>';
	}

	echo '<TR><TD>' . _('Payment Type') . ':</TD><TD><SELECT name=PaytType>';

/* The array PaytTypes is set up in config.php for user modification
Payment types can be modified by editing that file */

	foreach ($PaytTypes as $PaytType) {

	     if ($_POST['PaytType']==$PaytType){
		   echo '<OPTION SELECTED Value="' . $PaytType . '">' . $PaytType;
	     } else {
		   echo '<OPTION Value="' . $PaytType . '">' . $PaytType;
	     }
	}
	echo '</SELECT></TD></TR>';

	if (!is_numeric($_POST['Ref'])){
		$DefaultRef = '1';
	} else {
		$DefaultRef = $_POST['Ref'];
	}

	echo '<TR><TD>' . _('Starting Reference no (eg chq no)') . ':</TD>
            <TD><INPUT TYPE=text name="Ref" MAXLENGTH=11 SIZE=12 VALUE=' . $_POST['Ref'] . '></TD></TR>';

	echo '</TABLE><INPUT TYPE=Submit Name="PrintPDF" Value="' . _('Print PDF Only') . '">
                <INPUT TYPE=Submit Name="PrintPDFAndProcess" Value="' . _('Print and Process Payments') . '"></CENTER>';

	include ('includes/footer.inc');
} /*end of else not PrintPDF */

?>

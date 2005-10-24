<?php
/* $Revision: 1.12 $ */

$PageSecurity = 5;

Class Allocation {
	Var $TransID;
	Var $Amount;

	function Allocation ($TransID, $Amount){
		$this->TransID = $TransID;
		$this->Amount = $Amount;
	}
}

include('includes/session.inc');
include('includes/SQL_CommonFunctions.inc');
include('includes/GetPaymentMethods.php');

If ((isset($_POST['PrintPDF']) OR isset($_POST['PrintPDFAndProcess']))
	AND isset($_POST['FromCriteria'])
	AND strlen($_POST['FromCriteria'])>=1
	AND isset($_POST['ToCriteria'])
	AND strlen($_POST['ToCriteria'])>=1
	AND is_numeric($_POST['ExRate'])){

/*then print the report */

	$RefCounter = 0;
	include('includes/PDFStarter.php');
	$pdf->addinfo('Title',_('Payment Run Report'));
	$pdf->addinfo('Subject',_('Payment Run') . ' - ' . _('suppliers from') . ' ' . $_POST['FromCriteria'] . ' to ' . $_POST['ToCriteria'] . ' in ' . $_POST['Currency'] . ' ' . _('and Due By') . ' ' .  $_POST['AmountsDueBy']);

	$PageNumber=1;
	$line_height=12;

  /*Now figure out the invoice less credits due for the Supplier range under review */

	include ('includes/PDFPaymentRunPageHeader.inc');

	$sql = "SELECT suppliers.supplierid, 
			SUM(supptrans.ovamount + supptrans.ovgst - supptrans.alloc) AS balance
		FROM suppliers, 
			paymentterms, 
			supptrans, 
			systypes
		WHERE systypes.typeid = supptrans.type 
		AND suppliers.paymentterms = paymentterms.termsindicator 
		AND suppliers.supplierid = supptrans.supplierno
		AND supptrans.ovamount + supptrans.ovgst - supptrans.alloc !=0
		AND supptrans.duedate <='" . FormatDateForSQL($_POST['AmountsDueBy']) . "' 
		AND supptrans.hold=0 
		AND suppliers.currcode = '" . $_POST['Currency'] . "'
		AND supptrans.supplierNo >= '" . $_POST['FromCriteria'] . "' 
		AND supptrans.supplierno <= '" . $_POST['ToCriteria'] . "'
		GROUP BY suppliers.supplierid
		HAVING SUM(supptrans.ovamount + supptrans.ovgst - supptrans.alloc) > 0
		ORDER BY suppliers.supplierid";

	$SuppliersResult = DB_query($sql,$db);

	$SupplierID ='';
	$TotalPayments = 0;
	$TotalAccumDiffOnExch = 0;


	if (isset($_POST['PrintPDFAndProcess'])){
		$ProcessResult = DB_query('begin',$db);
	}

	while ($SuppliersToPay = DB_fetch_array($SuppliersResult)){

		$sql = "SELECT suppliers.supplierid,
				suppliers.suppname,
				systypes.typename,
				paymentterms.terms,
				supptrans.suppreference,
				supptrans.trandate,
				supptrans.rate,
				supptrans.transno,
				supptrans.type,
				(supptrans.ovamount + supptrans.ovgst - supptrans.alloc) AS balance,
				(supptrans.ovamount + supptrans.ovgst ) AS trantotal,
				supptrans.diffonexch,
				supptrans.id
			FROM suppliers,
				paymentterms,
				supptrans,
				systypes
			WHERE systypes.typeid = supptrans.type
			AND suppliers.paymentterms = paymentterms.termsindicator
			AND suppliers.supplierid = supptrans.supplierno
			AND supptrans.supplierno = '" . $SuppliersToPay['supplierid'] . "'
			AND supptrans.ovamount + supptrans.ovgst - supptrans.alloc !=0
			AND supptrans.duedate <='" . FormatDateForSQL($_POST['AmountsDueBy']) . "'
			AND supptrans.hold=0
			AND suppliers.currcode = '" . $_POST['Currency'] . "'
			AND supptrans.supplierno >= '" . $_POST['FromCriteria'] . "'
			AND supptrans.supplierno <= '" . $_POST['ToCriteria'] . "'
			ORDER BY supptrans.supplierno,
				supptrans.type,
				supptrans.transno";

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

		unset($Allocs);
		$Allocs = array();
		$AllocCounter =0;

		while ($DetailTrans = DB_fetch_array($TransResult)){

			if ($DetailTrans['supplierid'] != $SupplierID){ /*Need to head up for a new suppliers details */

				if ($SupplierID!=''){ /*only print the footer if this is not the first pass */
					include('includes/PDFPaymentRun_PymtFooter.php');
				}
				$SupplierID = $DetailTrans['supplierid'];
				$SupplierName = $DetailTrans['suppname'];
				if (isset($_POST['PrintPDFAndProcess'])){
					$SuppPaymentNo = GetNextTransNo(22, $db);
				}
				$AccumBalance = 0;
				$AccumDiffOnExch = 0;
				$LeftOvers = $pdf->addTextWrap($Left_Margin,
									$YPos,
									450-$Left_Margin,
									$FontSize,
									$DetailTrans['supplierid'] . ' - ' . $DetailTrans['suppname'] . ' - ' . $DetailTrans['terms'],
									'left');

				$YPos -= $line_height;
			}

			$DislayTranDate = ConvertSQLDate($DetailTrans['trandate']);

			$LeftOvers = $pdf->addTextWrap($Left_Margin+15, $YPos, 340-$Left_Margin,$FontSize,$DislayTranDate . ' - ' . $DetailTrans['typename'] . ' - ' . $DetailTrans['suppreference'], 'left');

			/*Positive is a favourable */
			$DiffOnExch = ($DetailTrans['balance'] / $DetailTrans['rate']) -  ($DetailTrans['balance'] / $_POST['ExRate']);

			$AccumBalance += $DetailTrans['balance'];
			$AccumDiffOnExch += $DiffOnExch;


			if (isset($_POST['PrintPDFAndProcess'])){

				/*Record the Allocations for later insertion once we have the ID of the payment SuppTrans */

				$Allocs[$AllocCounter] = new Allocation($DetailTrans['id'],$DetailTrans['balance']);
				$AllocCounter++;

				/*Now update the SuppTrans for the allocation made and the fact that it is now settled */

				$SQL = "UPDATE supptrans SET settled = 1,
                                     				alloc = " . $DetailTrans['trantotal'] . ",
                                     				diffonexch = " . ($DetailTrans['diffonexch'] + $DiffOnExch)  . "
                                 	WHERE type = " . $DetailTrans['type'] . '
					AND transno = ' . $DetailTrans['transno'];

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

			$LeftOvers = $pdf->addTextWrap(340, $YPos,60,$FontSize,number_format($DetailTrans['balance'],2), 'right');
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

		$ProcessResult = DB_query('COMMIT',$db,'','',false,false);
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

	$title=_('Payment Run');
	include('includes/header.inc');

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
	$sql = 'SELECT currency, currabrev FROM currencies';
	$result=DB_query($sql,$db);

	while ($myrow=DB_fetch_array($result)){
	if ($myrow['currabrev'] == $_SESSION['CompanyRecord']['currencydefault']){
			echo '<OPTION SELECTED Value="' . $myrow['currabrev'] . '">' . $myrow['currency'];
	} else {
		echo '<OPTION Value="' . $myrow['currabrev'] . '">' . $myrow['currency'];
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
		$DefaultDate = Date($_SESSION['DefaultDateFormat'], Mktime(0,0,0,Date('m')+1,0 ,Date('y')));
	} else {
		$DefaultDate = $_POST['AmountsDueBy'];
	}

	echo '<TR><TD>' . _('Payments Due To') . ':</TD>
            <TD><INPUT TYPE=text name="AmountsDueBy" MAXLENGTH=11 SIZE=12 VALUE=' . $DefaultDate . '></TD></TR>';

	$SQL = 'SELECT bankaccountname, accountcode FROM bankaccounts';

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
		 include('includes/footer.inc');
		 exit;
	} else {
		while ($myrow=DB_fetch_array($AccountsResults)){
		      /*list the bank account names */

			if ($_POST['BankAccount']==$myrow['accountcode']){
				echo '<OPTION SELECTED VALUE="' . $myrow['accountcode'] . '">' . $myrow['bankaccountname'];
			} else {
				echo '<OPTION VALUE="' . $myrow['accountcode'] . '">' . $myrow['bankaccountname'];
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
<?php

include('includes/session.php');
include('includes/SQL_CommonFunctions.inc');
include('includes/KLDefines.php');
include('includes/KLGeneralFunctions.php');

$Title = _('Print Consignment Invoices');

// The default company to Invoice from (PTADU).
if(!isset($_POST['CompanyFrom'])) {
	$_POST['CompanyFrom']='PTADU';
}

// The default company to Invoice to (PTBB).
if(!isset($_POST['CompanyTo'])) {
	$_POST['CompanyTo']='PTBB';
}

// default date to invoice is until Yesterday
if (!isset($_POST['EndDate'])){
	$_POST['EndDate'] = DateAdd(Date($_SESSION['DefaultDateFormat']),'d',-1); 
}

// The default draft or Invoice should be draft.
if(!isset($_POST['DraftOrInvoice'])) {
	$_POST['DraftOrInvoice']='DRAFT';
}

if (isset($_POST['submit'])) {
	submit($Title, $_POST['CompanyFrom'], $_POST['CompanyTo'], $_POST['EndDate'], $_POST['DraftOrInvoice'], $db);
} else {
	display($Title, $db);
}

//####_SUBMIT_SUBMIT_SUBMIT_SUBMIT_SUBMIT_SUBMIT_SUBMIT_SUBMIT_SUBMIT_SUBMIT_SUBMIT_SUBMIT####
function submit($Title, $CompanyFrom, $CompanyTo, $EndDate, $DraftOrInvoice, &$db) {

	$EndDate = FormatDateForSQL($EndDate);
	$InvoiceNumber = CreateConsignmentInvoiceNumber($CompanyFrom, $CompanyTo, $EndDate);
	$PageTitle = 'Invoice-' . $InvoiceNumber;

	//initialise no input errors
	$InputError = FALSE;
	
	if(!$InputError){
		$SQL = "SELECT klconsignment.stockid,
						stockmaster.description,
					SUM(klconsignment.qty) AS qty,
					SUM(klconsignment.qty * klconsignment.consignmentprice) AS consignmentsale
				FROM klconsignment,stockmaster
				WHERE klconsignment.stockid = stockmaster.stockid
					AND companycode = '" . $CompanyFrom . "'
					AND partnercode = '" . $CompanyTo . "'
					AND invoicedtopartner = '0000-00-00'
					AND saledate <= '" . $EndDate . "'
				GROUP BY klconsignment.stockid
				ORDER BY klconsignment.stockid";

		$result = DB_query($SQL);

		if (DB_num_rows($result) != 0){
			// Let's start the real PDF creation 
			require_once('includes/tcpdf/tcpdf.php');
			
			$pdf = new TCPDF('P', 'mm', 'A4', true, 'UTF-8', false);

			// set PDF document information
			$pdf->SetCreator($CompanyFrom);
			$pdf->SetAuthor($CompanyFrom);
			$pdf->SetTitle($PageTitle);
			$pdf->SetSubject($PageTitle);
			$pdf->setHeaderFont(Array(PDF_FONT_NAME_MAIN, '', PDF_FONT_SIZE_MAIN));
			$pdf->setFooterFont(Array(PDF_FONT_NAME_DATA, '', PDF_FONT_SIZE_DATA));
			$pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);
			$pdf->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
			$pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
			$pdf->SetFooterMargin(PDF_MARGIN_FOOTER);
			$pdf->setPrintHeader(false);
			$pdf->setPrintFooter(false);
			$pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);
			$pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);
			
			$FontType = 'helvetica';
			$FontSizeXL = 16;
			$FontSizeL = 12;
			$FontSizeM = 10;
			$FontSizeS = 8;
			
			$pdf->AddPage();
			// https://tcpdf.org/examples/example_005/
			// https://tcpdf.org/docs/source_docs/classTCPDF/#aa81d4b585de305c054760ec983ed3ece

			// Invoice header
			// Company From Information
			$WidthColumn1 = 0;
			if ($CompanyFrom == 'PTADU'){
				$pdf->SetFont($FontType, 'B', $FontSizeXL);
				$pdf->MultiCell($WidthColumn1, 0, 'PT. Angin Dingin Utara', 0, 'C', 0, 1, '', '', true);
				$pdf->SetFont($FontType, '', $FontSizeS);
				$pdf->MultiCell($WidthColumn1, 0, 'Jl. Raya Kesambi No. 1B, Kerobokan Kuta Utara, Badung - Bali', 0, 'C', 0, 1, '', '', true);
				$pdf->MultiCell($WidthColumn1, 0, 'Ph. +62 812 381 6795', 0, 'C', 0, 1, '', '', true);
			}elseif ($CompanyFrom == 'CASH'){
				$pdf->SetFont($FontType, 'B', $FontSizeL);
				$pdf->MultiCell($WidthColumn1, 0, 'CASH', 0, 'C', 0, 1, '', '', true);
			}

			if ($DraftOrInvoice == 'DRAFT'){
				$pdf->ln(6);
				$pdf->SetFont($FontType, '', $FontSizeL);
				$pdf->MultiCell($WidthColumn1, 0, 'This is a DRAFT INVOICE', 0, 'C', 0, 1, '', '', true);
			}

			// Company To header
			$SQLCompanyTo = "SELECT partnernameinvoice,
								partneraddressjalan,
								partneraddressblok,
								partneraddressnomor,
								partneraddressrt,
								partneraddressrw,
								partneraddresskecamatan,
								partneraddresskelurahan,
								partneraddresskabupaten,
								partneraddresspropinsi,
								partneraddresskodepos,
								partnertelepon,
								partnernpwpinvoice,
								accountppn,
								accountconsignmentcogspartner,
								accountconsignmentsalesptadu,
								daysinvoicedue
							FROM klretailpartners
							WHERE partnercode = '" . $CompanyTo . "'";
			$resultCompanyTo = DB_query($SQLCompanyTo);
			$myCompanyTo= DB_fetch_array($resultCompanyTo);
			
			$AddressPartner = $myCompanyTo['partneraddressjalan'];
			if ($myCompanyTo['partneraddressblok'] != ''){
				$AddressPartner .= ' ' . $myCompanyTo['partneraddressblok'];
			}
			if ($myCompanyTo['partneraddressnomor'] != ''){
				$AddressPartner .= ' ' . $myCompanyTo['partneraddressnomor'];
			}
			if ($myCompanyTo['partneraddressrt'] != ''){
				$AddressPartner .= ' ' . $myCompanyTo['partneraddressrt'];
			}
			if ($myCompanyTo['partneraddressrw'] != ''){
				$AddressPartner .= ' ' . $myCompanyTo['partneraddressrw'];
			}
			if ($myCompanyTo['partneraddresskecamatan'] != ''){
				$AddressPartner .= ' ' . $myCompanyTo['partneraddresskecamatan'];
			}
			if ($myCompanyTo['partneraddresskelurahan'] != ''){
				$AddressPartner .= ' ' . $myCompanyTo['partneraddresskelurahan'];
			}
			if ($myCompanyTo['partneraddresskabupaten'] != ''){
				$AddressPartner .= ' ' . $myCompanyTo['partneraddresskabupaten'];
			}
			if ($myCompanyTo['partneraddresspropinsi'] != ''){
				$AddressPartner .= ' ' . $myCompanyTo['partneraddresspropinsi'];
			}
			if ($myCompanyTo['partneraddresskodepos'] != ''){
				$AddressPartner .= ' ' . $myCompanyTo['partneraddresskodepos'];
			}
			
			$pdf->ln(6);
			$WidthColumn1 = 28;
			$WidthColumn2 = 0;
			$pdf->SetFont($FontType, '', $FontSizeM);
			$pdf->MultiCell($WidthColumn1, 0, 'Invoice to:', 0, 'L', 0, 0, '', '', true);
			$pdf->MultiCell($WidthColumn2, 0, $myCompanyTo['partnernameinvoice'], 0, 'L', 0, 1, '', '', true);
			$pdf->MultiCell($WidthColumn1, 0, 'Address:', 0, 'L', 0, 0, '', '', true);
			$pdf->MultiCell($WidthColumn2, 0, $AddressPartner, 0, 'L', 0, 1, '', '', true);
			$pdf->MultiCell($WidthColumn1, 0, 'NPWP:', 0, '', 0, 0, '', '', true);
			$pdf->MultiCell($WidthColumn2, 0, $myCompanyTo['partnernpwpinvoice'], 0, 'L', 0, 1, '', '', true);
			$pdf->MultiCell($WidthColumn1, 0, 'Invoice number:', 0, 'L', 0, 0, '', '', true);
			$pdf->MultiCell($WidthColumn2, 0, $InvoiceNumber, 0, 'L', 0, 1, '', '', true);
			$pdf->MultiCell($WidthColumn1, 0, 'Invoice date:', 0, 'L', 0, 0, '', '', true);
			$pdf->MultiCell($WidthColumn2, 0, ConvertSQLDate($EndDate), 0, 'L', 0, 1, '', '', true);
			$pdf->MultiCell($WidthColumn1, 0, 'Due date:', 0, 'L', 0, 0, '', '', true);
			$pdf->MultiCell($WidthColumn2, 0, DateAdd(ConvertSQLDate($EndDate),'d',+$myCompanyTo['daysinvoicedue']), 0, 'L', 0, 1, '', '', true);

			// Line header
			$pdf->ln(8);
			$pdf->SetFont($FontType, '', $FontSizeM);
			$WidthColumn1 = 10;
			$WidthColumn2 = 30;
			$WidthColumn3 = 75;
			$WidthColumn4 = 12;
			$WidthColumn5 = 25;
			$WidthColumn6 = 0;
			$pdf->MultiCell($WidthColumn1, 0, '#', 1, 'C', 0, 0, '', '', true);
			$pdf->MultiCell($WidthColumn2, 0, 'Code', 1, 'C', 0, 0, '', '', true);
			$pdf->MultiCell($WidthColumn3, 0, 'Description', 1, 'C', 0, 0, '', '', true);
			$pdf->MultiCell($WidthColumn4, 0, 'Qty', 1, 'C', 0, 0, '', '', true);
			$pdf->MultiCell($WidthColumn5, 0, 'Unit Price', 1, 'C', 0, 0, '', '', true);
			$pdf->MultiCell($WidthColumn6, 0, 'Total', 1, 'C', 0, 1, '', '', true);

			$TotalInvoice = 0;
			$TotalItems = 0;
			$LineNum = 0;
			$pdf->SetFont($FontType, '', $FontSizeS);
			
			while ($myrow = DB_fetch_array($result)) {

				$LineNum++;
				$TotalLine = $myrow['consignmentsale'];
				$AveragePrice = round($TotalLine / $myrow['qty']);
				$TotalInvoice = $TotalInvoice + $TotalLine;
				$TotalItems = $TotalItems + $myrow['qty'];
				
				$pdf->MultiCell($WidthColumn1, 0, locale_number_format($LineNum), 1, 'R', 0, 0, '', '', true);
				$pdf->MultiCell($WidthColumn2, 0, $myrow['stockid'], 1, 'L', 0, 0, '', '', true);
				$pdf->MultiCell($WidthColumn3, 0, $myrow['description'], 1, 'L', 0, 0, '', '', true);
				$pdf->MultiCell($WidthColumn4, 0, locale_number_format($myrow['qty']), 1, 'R', 0, 0, '', '', true);
				$pdf->MultiCell($WidthColumn5, 0, locale_number_format($AveragePrice), 1, 'R', 0, 0, '', '', true);
				$pdf->MultiCell($WidthColumn6, 0, locale_number_format($TotalLine), 1, 'R', 0, 1, '', '', true);
			}

			// TOTALS
			$pdf->SetFont($FontType, 'B', $FontSizeM);
			$pdf->MultiCell($WidthColumn1+
							$WidthColumn2+
							$WidthColumn3, 0, 'Total Qty:', 1, 'R', 0, 0, '', '', true);
			$pdf->MultiCell($WidthColumn4, 0, locale_number_format($TotalItems), 1, 'R', 0, 0, '', '', true);
			$pdf->MultiCell($WidthColumn5, 0, 'Total:', 1, 'R', 0, 0, '', '', true);
			$pdf->MultiCell($WidthColumn6, 0, locale_number_format($TotalInvoice), 1, 'R', 0, 1, '', '', true);
			
			if ($CompanyFrom == 'PTADU'){
				$pdf->SetFont($FontType, '', $FontSizeM);
				$TotalGoods = $TotalInvoice / ((100 + PPN_PERCENT) / 100);
				$TotalPPN = $TotalInvoice - $TotalGoods;
				$pdf->MultiCell($WidthColumn1+
								$WidthColumn2+
								$WidthColumn3+
								$WidthColumn4+
								$WidthColumn5, 0, 'Total Goods:', 1, 'R', 0, 0, '', '', true);
				$pdf->MultiCell($WidthColumn6, 0, locale_number_format($TotalGoods), 1, 'R', 0, 1, '', '', true);
				$pdf->MultiCell($WidthColumn1+
								$WidthColumn2+
								$WidthColumn3+
								$WidthColumn4+
								$WidthColumn5, 0, 'PPN:', 1, 'R', 0, 0, '', '', true);
				$pdf->MultiCell($WidthColumn6, 0, locale_number_format($TotalPPN), 1, 'R', 0, 1, '', '', true);
			}

			// payment details
			$pdf->ln(5);
			$pdf->SetFont($FontType, '', $FontSizeM);
			if ($CompanyFrom == 'PTADU'){
				$WidthColumn1 = 33;
				$WidthColumn2 = 60;
				$pdf->MultiCell($WidthColumn1 + $WidthColumn2, 0, 'Payment due by bank transfer', 0, 'L', 0, 1, '', '', true);
				$pdf->MultiCell($WidthColumn1, 0, 'Bank name:', 0, 'L', 0, 0, '', '', true);
				$pdf->MultiCell($WidthColumn2, 0, 'Bank Danamon Indonesia', 0, 'L', 0, 1, '', '', true);
				$pdf->MultiCell($WidthColumn1, 0, 'Account number:', 0, 'L', 0, 0, '', '', true);
				$pdf->MultiCell($WidthColumn2, 0, '3617556887', 0, 'L', 0, 1, '', '', true);
				$pdf->MultiCell($WidthColumn1, 0, 'Beneficiary name:', 0, 'L', 0, 0, '', '', true);
				$pdf->MultiCell($WidthColumn2, 0, 'PT. Angin Dingin Utara', 0, 'L', 0, 1, '', '', true);
			}elseif ($CompanyFrom == 'CASH'){
				$WidthColumn1 = 0;
				$pdf->MultiCell($WidthColumn1, 0, 'Payment by Cash', 0, 'L', 0, 1, '', '', true);
			}

			if ($DraftOrInvoice == 'INVOICE'){
				$rTx = DB_Txn_Begin();
				$SQL = "UPDATE klconsignment
						SET invoicedtopartner = '". $EndDate ."'
						WHERE companycode = '" . $CompanyFrom . "'
							AND partnercode = '" . $CompanyTo . "'
							AND invoicedtopartner = '0000-00-00'
							AND saledate <= '" . $EndDate . "'";
				$ErrMsg = 'CRITICAL ERROR! WRITE THIS CODE AND CALL THE OFFICE IMMEDIATELY: ERROR-CONSIGNMENT-00001';		
				$DbgMsg = 'SQL to update klconsignment record: ';
				$result = DB_query($SQL,$ErrMsg,$DbgMsg,true);

				if ($CompanyFrom == 'PTADU'){
					$PeriodNo = GetPeriod(ConvertSQLDate($EndDate));
					$TransNo = GetNextTransNo(10);

					// account for the goods sold
					InsertIntoGLTrans("10", 
									$TransNo, 
									$EndDate,
									$PeriodNo,
									$myCompanyTo['accountconsignmentsalesptadu'],
									"Invoice " . $InvoiceNumber,
									-round($TotalGoods),
									"",
									'ERROR-CNS-00003'
									);

					InsertIntoGLTrans("10", 
									$TransNo, 
									$EndDate,
									$PeriodNo,
									$myCompanyTo['accountconsignmentcogspartner'],
									"Invoice " . $InvoiceNumber,
									round($TotalGoods),
									"",
									'ERROR-CNS-00004'
									);

					// account for the PPN crossed
					InsertIntoGLTrans("10", 
									$TransNo, 
									$EndDate,
									$PeriodNo,
									ACCOUNT_PPN_ADU,
									"PPN Received " . $InvoiceNumber,
									-round($TotalPPN),
									"",
									'ERROR-CNS-00001'
									);
									
					InsertIntoGLTrans("10", 
									$TransNo, 
									$EndDate,
									$PeriodNo,
									$myCompanyTo['accountppn'],
									"PPN Paid Invoice " . $InvoiceNumber,
									round($TotalPPN),
									"",
									'ERROR-CNS-00002'
									);
					
				}
				$rTx = DB_Txn_Commit();
			}
			
			// download the pdf file
			$FileName= $PageTitle . '.pdf';
			$pdf->Output($FileName, 'D');
			$pdf->__destruct();
		}else{
			include('includes/header.php');
			prnMsg('No consignment sales to invoice');
			include('includes/footer.php');
		}
	}else{
		include('includes/header.php');
		echo '<p class="page_title_text">
				<img src="' . $RootPath . '/css/' . $Theme . '/images/magnifier.png" title="' . $PageTitle . '" alt="" />' . ' ' . $PageTitle . 
			'</p>';
		prnMsg($InputErrorMessage, "warn");
		include('includes/footer.php');
	}
} // End of function submit()


function display($Title, &$db)  //####DISPLAY_DISPLAY_DISPLAY_DISPLAY_DISPLAY_DISPLAY_#####
{
// Display form fields. This function is called the first time
// the page is called.
	include('includes/header.php');

	echo '<form action="' . htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') . '" method="post">
          <div>
			<br/>';
	echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';

	echo '<p class="page_title_text">
			<img src="' . $RootPath . '/css/' . $Theme . '/images/magnifier.png" title="' . $Title . '" alt="" />' . ' ' . $Title . '
		</p>';

	echo '<table class="selection">';

	include('includes/KLConsignmentParameterSelection.php');

	echo '<tr><td>&nbsp;</td></tr>
		<tr>
			<td>&nbsp;</td>
			<td><input type="submit" name="submit" value="' . $Title . '" /></td>
		</tr>
		</table>
		<br />';
	echo '</div>
         </form>';
	include('includes/footer.php');

} // End of function display()

?>
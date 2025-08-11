<?php

include('includes/session.php');
include('includes/SQL_CommonFunctions.php');
include('includes/UIGeneralFunctions.php');
include('includes/KLDefines.php');
include('includes/KLGeneralFunctions.php');
include('includes/KLUIGeneralFunctions.php');

use Dompdf\Dompdf;

$Title = _('Print PTADU Consignment Invoices');

// The default company to Invoice from (PTADU).
if(!isset($_POST['CompanyFrom'])) {
	$_POST['CompanyFrom']='PTADU';
}

// The default company to Invoice to (PTSMH).
if(!isset($_POST['CompanyTo'])) {
	$_POST['CompanyTo']='PTSMH';
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
	submit($_POST['CompanyFrom'], $_POST['CompanyTo'], $_POST['EndDate'], $_POST['DraftOrInvoice']);
} else {
	display($Title);
}

//####_SUBMIT_SUBMIT_SUBMIT_SUBMIT_SUBMIT_SUBMIT_SUBMIT_SUBMIT_SUBMIT_SUBMIT_SUBMIT_SUBMIT####
function submit($CompanyFrom, $CompanyTo, $EndDate, $DraftOrInvoice) {

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
					AND invoicedtopartner = '1000-01-01'
					AND saledate <= '" . $EndDate . "'
				GROUP BY klconsignment.stockid
				ORDER BY klconsignment.stockid";

		$Result = DB_query($SQL);

		if (DB_num_rows($Result) != 0){
			// Let's start the real PDF creation 

			// Increase memory limit to avoid exhaustion
			// ini_set('memory_limit', '512M');
	
			// Create a new DOMPDF instance
			$dompdf = new Dompdf();
			
			 // Configure DomPDF options first to optimize memory usage
			$options = $dompdf->getOptions();
			$options->set('defaultFont', 'Helvetica');
			$options->set('isRemoteEnabled', false);
			$options->set('isJavascriptEnabled', false);
			$options->set('isHtml5ParserEnabled', true);
			$options->set('isFontSubsettingEnabled', true);
			$options->set('tempDir', sys_get_temp_dir());
			$dompdf->setOptions($options);
			
			// Start building the HTML for the PDF
			$HTML = '<!DOCTYPE html>
			<html>
			<head>
				<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
				<title>' . $PageTitle . '</title>
				<style>
					body { font-family: Arial, sans-serif; margin: 20px; font-size: 10pt; }
					h1 { font-size: 16pt; margin: 0; text-align: center; }
					.header p { text-align: center; margin: 0; font-size: 8pt; }
					.draft { text-align: center; font-size: 12pt; margin: 15px 0; }
					table { width: 100%; border-collapse: collapse; }
					table.details td { padding: 3px 0; vertical-align: top; }
					.label { width: 28mm; }
					table.items { margin-top: 20px; width: 100%; }
					table.totals { margin-top: 0; border-top: 0; width: 100%; }
					table.items th, table.items td, table.totals td { border: 1px solid #000; padding: 3px; }
					table.items th { text-align: center; font-size: 10pt; } /* All headers centered */
					
					/* Set explicit widths for each column */
					.col1 { width: 8mm; }  /* # column - narrower */
					.col2 { width: 30mm; }  /* Code column */
					.col3 { width: 85mm; }  /* Description column - wider */
					.col4 { width: 10mm; }  /* Qty column - narrower */
					.col5 { width: 25mm; }  /* Unit Price column */
					.col6 { width: 30mm; }  /* Total column - explicit width instead of auto */
					
					/* Additional styles to enforce column widths and alignment */
					table.items th, table.items td {
						overflow: hidden;
						white-space: normal;
					}
					/* Right-align numeric columns */
					.num { text-align: right; }
					/* Left-align text columns */
					.left { text-align: left; }
					/* Center text */
					.ctr { text-align: center; }
					.bold { font-weight: bold; }
				</style>
			</head>
			<body>';

			// Invoice header
			// Company From Information
			$HTML .= '<div class="header">';
			if ($CompanyFrom == 'PTADU'){
				$HTML .= '<h1>PT. Angin Dingin Utara</h1>';
				$HTML .= '<p>Jl. Raya Kesambi No. 1B, Kerobokan Kuta Utara, Badung - Bali</p>';
				$HTML .= '<p>Ph. +62 812 381 6795</p>';
			}elseif ($CompanyFrom == 'CASH'){
				$HTML .= '<h1 style="font-size: 12pt;">CASH</h1>';
			}
			$HTML .= '</div>';

			if ($DraftOrInvoice == 'DRAFT'){
				$HTML .= '<div class="draft">This is a DRAFT INVOICE</div>';
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
			$ResultCompanyTo = DB_query($SQLCompanyTo);
			$MyCompanyTo= DB_fetch_array($ResultCompanyTo);
			
			$AddressPartner = $MyCompanyTo['partneraddressjalan'];
			if ($MyCompanyTo['partneraddressblok'] != ''){
				$AddressPartner .= ' ' . $MyCompanyTo['partneraddressblok'];
			}
			if ($MyCompanyTo['partneraddressnomor'] != ''){
				$AddressPartner .= ' ' . $MyCompanyTo['partneraddressnomor'];
			}
			if ($MyCompanyTo['partneraddressrt'] != ''){
				$AddressPartner .= ' ' . $MyCompanyTo['partneraddressrt'];
			}
			if ($MyCompanyTo['partneraddressrw'] != ''){
				$AddressPartner .= ' ' . $MyCompanyTo['partneraddressrw'];
			}
			if ($MyCompanyTo['partneraddresskecamatan'] != ''){
				$AddressPartner .= ' ' . $MyCompanyTo['partneraddresskecamatan'];
			}
			if ($MyCompanyTo['partneraddresskelurahan'] != ''){
				$AddressPartner .= ' ' . $MyCompanyTo['partneraddresskelurahan'];
			}
			if ($MyCompanyTo['partneraddresskabupaten'] != ''){
				$AddressPartner .= ' ' . $MyCompanyTo['partneraddresskabupaten'];
			}
			if ($MyCompanyTo['partneraddresspropinsi'] != ''){
				$AddressPartner .= ' ' . $MyCompanyTo['partneraddresspropinsi'];
			}
			if ($MyCompanyTo['partneraddresskodepos'] != ''){
				$AddressPartner .= ' ' . $MyCompanyTo['partneraddresskodepos'];
			}
			
			$HTML .= '<table class="details">
				<tr>
					<td class="label">Invoice to:</td>
					<td>' . htmlspecialchars($MyCompanyTo['partnernameinvoice']) . '</td>
				</tr>
				<tr>
					<td class="label">Address:</td>
					<td>' . htmlspecialchars($AddressPartner) . '</td>
				</tr>
				<tr>
					<td class="label">NPWP:</td>
					<td>' . htmlspecialchars($MyCompanyTo['partnernpwpinvoice']) . '</td>
				</tr>
				<tr>
					<td class="label">Invoice number:</td>
					<td>' . htmlspecialchars($InvoiceNumber) . '</td>
				</tr>
				<tr>
					<td class="label">Invoice date:</td>
					<td>' . ConvertSQLDate($EndDate) . '</td>
				</tr>
				<tr>
					<td class="label">Due date:</td>
					<td>' . DateAdd(ConvertSQLDate($EndDate),'d',+$MyCompanyTo['daysinvoicedue']) . '</td>
				</tr>
			</table>';

			// Line header - use colgroup to enforce column widths
			$HTML .= '<table class="items">
				<colgroup>
					<col style="width: 8mm">
					<col style="width: 30mm">
					<col style="width: 85mm">
					<col style="width: 10mm">
					<col style="width: 25mm">
					<col style="width: 30mm">
				</colgroup>
				<thead>
					<tr>
						<th>#</th>
						<th>Code</th>
						<th>Description</th>
						<th>Qty</th>
						<th>Unit Price</th>
						<th>Total</th>
					</tr>
				</thead>
				<tbody>';

			$TotalInvoice = 0;
			$TotalItems = 0;
			$LineNum = 0;
			
			while ($MyRow = DB_fetch_array($Result)) {
				$LineNum++;
				$TotalLine = $MyRow['consignmentsale'];
				$AveragePrice = round($TotalLine / $MyRow['qty']);
				$TotalInvoice = $TotalInvoice + $TotalLine;
				$TotalItems = $TotalItems + $MyRow['qty'];
				
				$HTML .= '<tr>
					<td class="num">' . locale_number_format($LineNum) . '</td>
					<td class="left">' . $MyRow['stockid'] . '</td>
					<td class="left">' . $MyRow['description'] . '</td>
					<td class="num">' . locale_number_format($MyRow['qty']) . '</td>
					<td class="num">' . locale_number_format($AveragePrice) . '</td>
					<td class="num">' . locale_number_format($TotalLine) . '</td>
				</tr>';
			}
			
			$HTML .= '</tbody></table>';

			// TOTALS - Also use colgroup to maintain same column widths
			$HTML .= '<table class="items totals">
				<colgroup>
					<col style="width: 8mm">
					<col style="width: 30mm">
					<col style="width: 85mm">
					<col style="width: 10mm">
					<col style="width: 25mm">
					<col style="width: 30mm">
				</colgroup>
				<tr>
					<td colspan="3" class="bold num">Total Qty:</td>
					<td class="num bold col4">' . locale_number_format($TotalItems) . '</td>
					<td class="bold num col5">Total:</td>
					<td class="num bold col6">' . locale_number_format($TotalInvoice) . '</td>
				</tr>';
			
			if ($CompanyFrom == 'PTADU'){
				$TotalGoods = $TotalInvoice / ((100 + PPN_PERCENT) / 100);
				$TotalPPN = $TotalInvoice - $TotalGoods;
				
				$HTML .= '<tr>
					<td colspan="5" class="num">Total Goods:</td>
					<td class="num col6">' . locale_number_format($TotalGoods) . '</td>
				</tr>
				<tr>
					<td colspan="5" class="num">PPN:</td>
					<td class="num col6">' . locale_number_format($TotalPPN) . '</td>
				</tr>';
			}
			
			$HTML .= '</table>';

			// payment details
			$HTML .= '<div class="payment">';
			if ($CompanyFrom == 'PTADU'){
				$HTML .= '<p>Payment due by bank transfer</p>
				<table style="width:auto;">
					<tr>
						<td style="width:33mm;">Bank name:</td>
						<td>Bank Danamon Indonesia</td>
					</tr>
					<tr>
						<td>Account number:</td>
						<td>3617556887</td>
					</tr>
					<tr>
						<td>Beneficiary name:</td>
						<td>PT. Angin Dingin Utara</td>
					</tr>
				</table>';
			}elseif ($CompanyFrom == 'CASH'){
				$HTML .= '<p>Payment by Cash</p>';
			}
			$HTML .= '</div>';
			
			$HTML .= '</body></html>';

			if ($DraftOrInvoice == 'INVOICE'){
				// Processing database operations before PDF generation to free memory
				DB_Txn_Begin();
				$SQL = "UPDATE klconsignment
						SET invoicedtopartner = '". $EndDate ."'
						WHERE companycode = '" . $CompanyFrom . "'
							AND partnercode = '" . $CompanyTo . "'
							AND invoicedtopartner = '1000-01-01'
							AND saledate <= '" . $EndDate . "'";
				$ErrMsg = 'CRITICAL ERROR! WRITE THIS CODE AND CALL THE OFFICE IMMEDIATELY: ERROR-CONSIGNMENT-00001';		
				$Result = DB_query($SQL,$ErrMsg,'',true);

				if ($CompanyFrom == 'PTADU'){
					$PeriodNo = GetPeriod(ConvertSQLDate($EndDate));
					$TransNo = GetNextTransNo(10);

					// account for the goods sold
					InsertIntoGLTrans("10", 
									$TransNo, 
									$EndDate,
									$PeriodNo,
									$MyCompanyTo['accountconsignmentsalesptadu'],
									"Invoice " . $InvoiceNumber,
									-round($TotalGoods),
									"",
									'ERROR-CNS-00003'
									);

					InsertIntoGLTrans("10", 
									$TransNo, 
									$EndDate,
									$PeriodNo,
									$MyCompanyTo['accountconsignmentcogspartner'],
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
									$MyCompanyTo['accountppn'],
									"PPN Paid Invoice " . $InvoiceNumber,
									round($TotalPPN),
									"",
									'ERROR-CNS-00002'
									);
					
				}
				DB_Txn_Commit();
			}
			
			try {
				// Load HTML into DomPDF and free the original HTML variable to save memory
				$dompdf->loadHtml($HTML);
				$HTML = null; // Free up memory by releasing the HTML string
				
				// Set paper size and orientation
				$dompdf->setPaper('A4', 'portrait');
				
				// Render the PDF with memory optimization
				$dompdf->render();
				
				// Free any resources that might be holding memory
				gc_collect_cycles();
				
				// Output the PDF for download
				$FileName = $PageTitle . '.pdf';
				$dompdf->stream($FileName, array('Attachment' => true));
				exit(); // Important to prevent any further output after PDF is streamed
			} catch (Exception $e) {
				// In case of errors, show a user-friendly message
				include('includes/header.php');
				prnMsg('An error occurred while generating the PDF: ' . $e->getMessage(), 'error');
				include('includes/footer.php');
				exit();
			}
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


function display($Title)  //####DISPLAY_DISPLAY_DISPLAY_DISPLAY_DISPLAY_DISPLAY_#####
{
// Display form fields. This function is called the first time
// the page is called.
	include('includes/header.php');

	echo '<form action="' . htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') . '" method="post">';
	echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';

	echo '<p class="page_title_text">
			<img src="' . $RootPath . '/css/' . $_SESSION['Theme'] . '/images/magnifier.png" title="' . $Title . '" alt="" />' . ' ' . $Title . '
		</p>';

	echo '<fieldset>';

	echo FixedField("CompanyFrom", "PTADU", 'From', '');	
	echo FieldToSelectOneRetailPartner("CompanyTo", $_POST['CompanyTo'], _('To'), 'Select the company receiving the Faktur Pajak', '', 1, true, false);
	echo FieldToSelectOneDate('EndDate', $_POST['EndDate'], _('Invoice Consignment Sales until'), '', '', 2, true, false);
	echo FieldToSelectFromTwoOptions('DRAFT', 'Draft', 
									'INVOICE', 'Invoice',
									'DraftOrInvoice', $_POST['DraftOrInvoice'], _('Draft or Invoice'), '', '', 3, true, false);
	
	echo '</fieldset>';

	echo OneButtonCenteredForm("submit", $Title, 4, false, false);
	
	echo '</form>';
	
	include('includes/footer.php');

} // End of function display()

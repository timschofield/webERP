<?php

require(__DIR__ . '/includes/session.php');

$Title = __('Export XML File for Faktur Pajak');

include('includes/SQL_CommonFunctions.php');
include('includes/UIGeneralFunctions.php');
include('includes/KLDefines.php');
include('includes/KLUIGeneralFunctions.php');

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

if(!isset($_POST['NomorSeriFP'])) {
	$_POST['NomorSeriFP']='0000000000000';
}

if (isset($_POST['submit'])) {
	submit($Title, $_POST['CompanyFrom'], $_POST['CompanyTo'], $_POST['EndDate'], $_POST['DraftOrInvoice'], $_POST['NomorSeriFP']);
} else {
	display($Title);
}

//####_SUBMIT_SUBMIT_SUBMIT_SUBMIT_SUBMIT_SUBMIT_SUBMIT_SUBMIT_SUBMIT_SUBMIT_SUBMIT_SUBMIT####
function submit($Title, $CompanyFrom, $CompanyTo, $EndDate, $DraftOrInvoice, $NomorSeriFP) {

	$EndDate = ConvertSQLDate($EndDate);
	$EndDateSQL = FormatDateForSQL($EndDate);

	//initialise no input errors
	$InputError = false;

	//first off validate inputs sensible

	if(!$InputError){
		$SQL = "SELECT klconsignment.stockid,
						stockmaster.description,
					SUM(klconsignment.qty) AS qty,
					SUM(klconsignment.qty * klconsignment.consignmentprice) AS consignmentsale
				FROM klconsignment,stockmaster
				WHERE klconsignment.stockid = stockmaster.stockid
					AND companycode = '" . $CompanyFrom . "'
					AND partnercode = '" . $CompanyTo . "'
					AND (fakturpajakdate = '1000-01-01'
						OR fakturpajakdate = '" . $EndDateSQL . "')
					AND saledate <= '" . $EndDateSQL . "'
				GROUP BY klconsignment.stockid
				ORDER BY klconsignment.stockid";

		$Result = DB_query($SQL);
		if (DB_num_rows($Result) != 0){

			$xml = new SimpleXMLElement(
				'<?xml version="1.0" encoding="utf-8"?>' .
				'<TaxInvoiceBulk xmlns:xsd="http://www.w3.org/2001/XMLSchema" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance">' .
				'</TaxInvoiceBulk>'
			);

			$SQLCompanyFrom = "SELECT partnernpwpinvoice
							FROM klretailpartners
							WHERE partnercode = '" . $CompanyFrom . "'";
			$ResultCompanyFrom = DB_query($SQLCompanyFrom);
			$MyCompanyFrom = DB_fetch_array($ResultCompanyFrom);
			
			$CharsToStripFromNPWP = array(".", "-");
			$NPWPCompanyFrom = str_replace($CharsToStripFromNPWP,"",$MyCompanyFrom['partnernpwpinvoice']); //NPWP number only, no format
			$xml->addChild('TIN', htmlspecialchars($NPWPCompanyFrom));

			$ListOfTaxInvoice = $xml->addChild('ListOfTaxInvoice');
			
			// Add <TaxInvoice> element
			$TaxInvoice = $ListOfTaxInvoice->addChild('TaxInvoice');

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
								partneremail,
								ppn,
								accountppn,
								daysinvoicedue
							FROM klretailpartners
							WHERE partnercode = '" . $CompanyTo . "'";
			$ResultCompanyTo = DB_query($SQLCompanyTo);
			$MyCompanyTo= DB_fetch_array($ResultCompanyTo);

			$TaxInvoiceDate = $EndDateSQL;
			$TaxInvoiceOpt = 'Normal';
			$TrxCode = '04';
			$AddInfo = '';
			$CustomDoc = '';
			$RefDesc = $CompanyFrom . '-' . $CompanyTo . '-' . $EndDateSQL;
			$FacilityStamp = '';
			$SellerIDTKU = $NPWPCompanyFrom . '000000';
			$CharsToStripFromNPWP = array(".", "-");
			$BuyerTin = str_replace($CharsToStripFromNPWP,"",$MyCompanyTo['partnernpwpinvoice']); //NPWP number only, no format
			$BuyerDocument = 'TIN';
			$BuyerCountry = 'IDN';
			$BuyerDocumentNumber = '-';
			$BuyerName = strtoupper(str_replace($CharsToStripFromNPWP,"",$MyCompanyTo['partnernameinvoice']));

			$BuyerAdress = $MyCompanyTo['partneraddressjalan'];
			if ($MyCompanyTo['partneraddressblok'] != ''){
				$BuyerAdress .= ' ' . $MyCompanyTo['partneraddressblok'];
			}
			if ($MyCompanyTo['partneraddressnomor'] != ''){
				$BuyerAdress .= ' ' . $MyCompanyTo['partneraddressnomor'];
			}
			if ($MyCompanyTo['partneraddressrt'] != ''){
				$BuyerAdress .= ' ' . $MyCompanyTo['partneraddressrt'];
			}
			if ($MyCompanyTo['partneraddressrw'] != ''){
				$BuyerAdress .= ' ' . $MyCompanyTo['partneraddressrw'];
			}
			if ($MyCompanyTo['partneraddresskecamatan'] != ''){
				$BuyerAdress .= ' ' . $MyCompanyTo['partneraddresskecamatan'];
			}
			if ($MyCompanyTo['partneraddresskelurahan'] != ''){
				$BuyerAdress .= ' ' . $MyCompanyTo['partneraddresskelurahan'];
			}
			if ($MyCompanyTo['partneraddresskabupaten'] != ''){
				$BuyerAdress .= ' ' . $MyCompanyTo['partneraddresskabupaten'];
			}
			if ($MyCompanyTo['partneraddresspropinsi'] != ''){
				$BuyerAdress .= ' ' . $MyCompanyTo['partneraddresspropinsi'];
			}
			if ($MyCompanyTo['partneraddresskodepos'] != ''){
				$BuyerAdress .= ' ' . $MyCompanyTo['partneraddresskodepos'];
			}

			$BuyerEmail = $MyCompanyTo['partneremail'];
			$BuyerIDTKU = $BuyerTin . '000000';
			$BuyerIDTKU = str_replace($CharsToStripFromNPWP,"",$BuyerIDTKU); //NPWP number only, no format

			// Add invoice header data
			$TaxInvoice->addChild('TaxInvoiceDate', htmlspecialchars($TaxInvoiceDate)); 
			$TaxInvoice->addChild('TaxInvoiceOpt', $TaxInvoiceOpt); 
			$TaxInvoice->addChild('TrxCode', $TrxCode);
			$TaxInvoice->addChild('AddInfo', $AddInfo);
			$TaxInvoice->addChild('CustomDoc', $CustomDoc);
			$TaxInvoice->addChild('RefDesc', htmlspecialchars($RefDesc ?? ''));
			$TaxInvoice->addChild('FacilityStamp', $FacilityStamp);
			$TaxInvoice->addChild('SellerIDTKU', $SellerIDTKU);
			$TaxInvoice->addChild('BuyerTin', htmlspecialchars($BuyerTin ?? ''));
			$TaxInvoice->addChild('BuyerDocument', $BuyerDocument);
			$TaxInvoice->addChild('BuyerCountry', $BuyerCountry); 
			$TaxInvoice->addChild('BuyerDocumentNumber', $BuyerDocumentNumber);
			$TaxInvoice->addChild('BuyerName', htmlspecialchars($BuyerName ?? ''));
			$TaxInvoice->addChild('BuyerAdress', htmlspecialchars($BuyerAdress ?? ''));
			$TaxInvoice->addChild('BuyerEmail', htmlspecialchars($BuyerEmail ?? ''));
			$TaxInvoice->addChild('BuyerIDTKU', $BuyerIDTKU);

			$ListOfGoodService = $TaxInvoice->addChild('ListOfGoodService');

			while ($MyRow = DB_fetch_array($Result)) {
				$Opt = 'A';
				// $Code = $MyRow['stockid'];
				$Code = '000000';
				$Name = $MyRow['description'];
				$Unit = 'UM.0021';
				$Price = round(($MyRow['consignmentsale'] / $MyRow['qty']) / ((100 + PPN_PERCENT) / 100), 0);
				$Qty = round($MyRow['qty'], 0);
				$TotalDiscount = 0;
				$TaxBase = $Price * $Qty;
				// Weird system of CoreTax to account for PPN as 12% when in reality it is 11%
				// We guess will be fixed in the future, but for now we need to do this.
				$OtherTaxBase = round($TaxBase / 12 * 11, 2);
				$VATRate = 12;
				$VAT = round($OtherTaxBase * $VATRate / 100, 2);
				$STLGRate = 0;
				$STLG = 0;

				// Add <GoodService> element
				$GoodService = $ListOfGoodService->addChild('GoodService');
				$GoodService->addChild('Opt', htmlspecialchars($Opt));
				$GoodService->addChild('Code', htmlspecialchars($Code));
				$GoodService->addChild('Name', htmlspecialchars($Name));
				$GoodService->addChild('Unit', htmlspecialchars($Unit));
				$GoodService->addChild('Price', number_format($Price, 0, '.', ''));
				$GoodService->addChild('Qty', $Qty);
				$GoodService->addChild('TotalDiscount', number_format($TotalDiscount, 0, '.', ''));
				$GoodService->addChild('TaxBase', number_format($TaxBase, 2, '.', ''));
				$GoodService->addChild('OtherTaxBase', number_format($OtherTaxBase, 2, '.', ''));
				$GoodService->addChild('VATRate', $VATRate);
				$GoodService->addChild('VAT', number_format($VAT, 2, '.', ''));
				$GoodService->addChild('STLGRate', $STLGRate);
				$GoodService->addChild('STLG', number_format($STLG, 0, '.', ''));
				
			}

			if ($DraftOrInvoice == 'INVOICE'){
				DB_Txn_Begin();
				$SQL = "UPDATE klconsignment
						SET fakturpajakdate = '". $EndDateSQL ."'
						WHERE companycode = '" . $CompanyFrom . "'
							AND partnercode = '" . $CompanyTo . "'
							AND fakturpajakdate = '1000-01-01'
							AND saledate <= '" . $EndDateSQL . "'";
				$ErrMsg = 'CRITICAL ERROR! WRITE THIS CODE AND CALL THE OFFICE IMMEDIATELY: ERROR-CONSIGNMENT-00002';		
				$Result = DB_query($SQL, $ErrMsg, '', true);
				DB_Txn_Commit();
			}

		// --- Prepare for Download ---
		$filename = $RefDesc . ".xml";
		$xmlOutput = $xml->asXML();

		// Format the XML output
		$dom = new DOMDocument();
		$dom->preserveWhiteSpace = false;
		$dom->formatOutput = true;
		$dom->loadXML($xmlOutput);
		$xmlOutput = $dom->saveXML();

		// Send headers
		header('Content-Type: application/xml; charset=utf-8');
		header('Content-Disposition: attachment; filename="' . $filename . '"');
		header('Content-Length: ' . strlen($xmlOutput));
		header('Pragma: no-cache');
		header('Expires: 0');

		// Output the formatted XML
		echo $xmlOutput;
		include('includes/footer.php');
			
		} else {
			include('includes/header.php');
			prnMsg('No data to create a Faktur Pajak','warn');
			include('includes/footer.php');
		}
	} else {
		include('includes/header.php');
		echo '<p class="page_title_text">
				<img src="' . $RootPath . '/css/' . $Theme . '/images/magnifier.png" title="' . $PageTitle . '" alt="" />' . ' ' . $PageTitle . 
			'</p>';
		prnMsg($InputErrorMessage, "warn");
		include('includes/footer.php');
	}
} // End of function submit()


function display($Title) {
	include('includes/header.php');

	echo '<form action="' . htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') . '" method="post">
          <div>
			<br/>';
	echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';

	echo '<p class="page_title_text">
			<img src="' . $RootPath . '/css/' . $_SESSION['Theme'] . '/images/magnifier.png" title="' . $Title . '" alt="" />' . ' ' . $Title . '
		</p>';

	echo '<fieldset>';
	echo FixedField("CompanyFrom", "PTADU", 'From', '');	
	echo FieldToSelectOneRetailPartner("CompanyTo", $_POST['CompanyTo'], __('To'), 'Select the company receiving the Faktur Pajak', '', 1, true, false);
	echo FieldToSelectOneDate('EndDate', $_POST['EndDate'], __('Invoice Consignment Sales until'), '', '', 2, true, false);
	echo FieldToSelectFromTwoOptions('DRAFT', 'Draft', 
									'INVOICE', 'Invoice',
									'DraftOrInvoice', $_POST['DraftOrInvoice'], __('Draft or Invoice'), '', '', 3, true, false);
	echo FieldToSelectOneText("NomorSeriFP", $_POST['NomorSeriFP'], 14, 13, 'Nomor Seri Faktur Pajak', '', '', 4, true, false);
	echo '</fieldset>';

	echo OneButtonCenteredForm("submit", $Title, 6, false, false);

	echo '</form>';
	
	include('includes/footer.php');

} // End of function display()

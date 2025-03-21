<?php

include('includes/session.php');
include('includes/SQL_CommonFunctions.inc');
include('includes/UIGeneralFunctions.php');
include('includes/KLDefines.php');
include('includes/KLGeneralFunctions.php');
include('includes/KLUIGeneralFunctions.php');

$Title = _('Print Authorized Internal Stock Request still not fulfilled');

// The default location from (KANTO).
if(!isset($_POST['LocationForm'])) {
	$_POST['LocationForm']='KANTO';
}


if (isset($_POST['submit'])) {
	submit($Title, $_POST['LocationForm']);
} else {
	display($Title);
}

function submit($Title, $LocationForm) {

	//initialise no input errors
	$InputError = FALSE;
	
	if(!$InputError){
		$SQL = "SELECT stockrequest.dispatchid,
					locations.locationname,
					stockrequest.despatchdate,
					stockrequest.narrative,
					departments.description,
					stockrequest.initiator,
					www_users.realname
				FROM stockrequest
				LEFT JOIN departments
					ON stockrequest.departmentid=departments.departmentid
				LEFT JOIN locations
					ON stockrequest.loccode=locations.loccode
				LEFT JOIN www_users
					ON www_users.userid=stockrequest.initiator
				WHERE stockrequest.authorised=1
					AND stockrequest.closed=0
					AND stockrequest.loccode='" . $LocationForm . "'";

		$Result = DB_query($SQL);

		if (DB_num_rows($Result) != 0){
			// Let's start the real PDF creation 
			require_once('includes/tcpdf/tcpdf.php');
			$PageTitle = _('Pending Stock Requests '). date('Y-m-d-H-i-s');

			$pdf = new TCPDF('P', 'mm', 'A4', true, 'UTF-8', false);

			// set PDF document information
			$pdf->SetCreator('webERP');
			$pdf->SetAuthor('webERP');
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
			$pdf->SetFont($FontType, '', $FontSizeXL);
			$WidthColumn1 = 0;
			$pdf->MultiCell($WidthColumn1, 0, 'Internal Stock Requests', 0, 'C', 0, 1, '', '', true);
		
			while($MyRow = DB_fetch_array($Result)){
				// Calculate the height needed for this request block
				$LineSQL = "SELECT COUNT(*) AS linecount
						FROM stockrequestitems
						WHERE dispatchid='" . $MyRow['dispatchid'] . "'
							AND completed=0";
				$LineCountResult = DB_query($LineSQL);
				$LineCountRow = DB_fetch_array($LineCountResult);
				$lineCount = $LineCountRow['linecount'];
				
				// Estimate needed height for header and lines (5 header lines + table header + items)
				$neededHeight = 30 + (8 * $lineCount); // approx 8mm per line
				
				// Check if we need a page break before this request
				if ($pdf->GetY() + $neededHeight > $pdf->getPageHeight() - PDF_MARGIN_BOTTOM) {
					$pdf->AddPage();
					$WidthColumn1 = 0;
					$pdf->SetFont($FontType, '', $FontSizeXL);
					$pdf->MultiCell($WidthColumn1, 0, 'Internal Stock Requests', 0, 'C', 0, 1, '', '', true);
				}

				// https://tcpdf.org/examples/example_005/
				// https://tcpdf.org/docs/source_docs/classTCPDF/#aa81d4b585de305c054760ec983ed3ece
	
				$pdf->ln(4);
				$pdf->SetFont($FontType, '', $FontSizeM);
				$WidthColumn1 = 0;
				$pdf->MultiCell($WidthColumn1, 0, 'From: ' . $MyRow['locationname'], 0, 'L', 0, 1, '', '', true);
				$pdf->MultiCell($WidthColumn1, 0, 'To: ' . $MyRow['description'], 0, 'L', 0, 1, '', '', true);
				$pdf->MultiCell($WidthColumn1, 0, 'Date: ' . ConvertSQLDate($MyRow['despatchdate']), 0, 'L', 0, 1, '', '', true);
				$pdf->MultiCell($WidthColumn1, 0, 'Initiator: ' . $MyRow['initiator'] . ' - '. $MyRow['realname'], 0, 'L', 0, 1, '', '', true);
				$pdf->MultiCell($WidthColumn1, 0, '# Request: ' . $MyRow['dispatchid'], 0, 'L', 0, 1, '', '', true);
	
				// Line header
				$pdf->ln(4);
				$pdf->SetFont($FontType, '', $FontSizeM);
				$WidthColumn1 = 10;
				$WidthColumn2 = 30;
				$WidthColumn3 = 75;
				$WidthColumn4 = 20;
				$WidthColumn5 = 20;
				$WidthColumn6 = 10;
				$pdf->MultiCell($WidthColumn1, 0, '#', 1, 'C', 0, 0, '', '', true);
				$pdf->MultiCell($WidthColumn2, 0, 'Code', 1, 'C', 0, 0, '', '', true);
				$pdf->MultiCell($WidthColumn3, 0, 'Description', 1, 'C', 0, 0, '', '', true);
				$pdf->MultiCell($WidthColumn4, 0, 'Requested', 1, 'C', 0, 0, '', '', true);
				$pdf->MultiCell($WidthColumn5, 0, 'Pending', 1, 'C', 0, 0, '', '', true);
				$pdf->MultiCell($WidthColumn6, 0, 'Uom', 1, 'C', 0, 1, '', '', true);
	
				$pdf->SetFont($FontType, '', $FontSizeS);
				
				$LineSQL = "SELECT stockrequestitems.dispatchitemsid,
									stockrequestitems.dispatchid,
									stockrequestitems.stockid,
									stockrequestitems.decimalplaces,
									stockrequestitems.uom,
									stockmaster.description,
									stockrequestitems.quantity,
									stockrequestitems.qtydelivered,
									stockmaster.controlled
							FROM stockrequestitems
							LEFT JOIN stockmaster
								ON stockmaster.stockid=stockrequestitems.stockid
							WHERE dispatchid='" . $MyRow['dispatchid'] . "'
								AND completed=0";

				$LineResult = DB_query($LineSQL);
				$i=1;
				while ($MyLine = DB_fetch_array($LineResult)) {
					
					$pdf->MultiCell($WidthColumn1, 0, locale_number_format($i), 1, 'R', 0, 0, '', '', true);
					$pdf->MultiCell($WidthColumn2, 0, $MyLine['stockid'], 1, 'L', 0, 0, '', '', true);
					$pdf->MultiCell($WidthColumn3, 0, $MyLine['description'], 1, 'L', 0, 0, '', '', true);
					$pdf->MultiCell($WidthColumn4, 0, locale_number_format($MyLine['quantity']), 1, 'R', 0, 0, '', '', true);
					$pdf->MultiCell($WidthColumn5, 0, locale_number_format($MyLine['quantity']-$MyLine['qtydelivered']), 1, 'R', 0, 0, '', '', true);
					$pdf->MultiCell($WidthColumn6, 0, $MyLine['uom'], 1, 'L', 0, 1, '', '', true);
					$i++;
				}
			}
						
			// download the pdf file
			$FileName= $PageTitle . '.pdf';
			$pdf->Output($FileName, 'D');
			$pdf->__destruct();
		}else{
			include('includes/header.php');
			prnMsg('No Pending Authorized Internal Stock Requests');
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


function display($Title)
{
	include('includes/header.php');

	echo '<form action="' . htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') . '" method="post">';
	echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';

	echo '<p class="page_title_text">
			<img src="' . $RootPath . '/css/' . $_SESSION['Theme'] . '/images/magnifier.png" title="' . $Title . '" alt="" />' . ' ' . $Title . '
		</p>';

	echo '<fieldset>';

	echo FieldToSelectOneLocation("LocationForm", $_POST['LocationForm'], 'Location from', '', 'CANVIEW', 1, true, false);
	
	echo '</fieldset>';

	echo OneButtonCenteredForm("submit", $Title, 2, false, false);
	
	echo '</form>';
	
	include('includes/footer.php');

} // End of function display()

?>
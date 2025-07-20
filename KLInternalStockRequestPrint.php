<?php

include('includes/session.php');
include('includes/SQL_CommonFunctions.php');
include('includes/UIGeneralFunctions.php');
include('includes/KLDefines.php');
include('includes/KLGeneralFunctions.php');
include('includes/KLUIGeneralFunctions.php');

// Use necessary classes for PDF and Spreadsheet generation
use Dompdf\Dompdf;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Reader\Html as HtmlReader; // Alias to avoid conflict if Html class exists

// The default location from (KANTO).
if (!isset($_POST['LocationForm'])) {
	$_POST['LocationForm'] = 'KANTO';
}

// Decide action based on button pressed
if (isset($_POST['PrintPDF']) or isset($_POST['Spreadsheet']) or isset($_POST['submit'])) {
	GenerateReport($_POST['LocationForm']);
} else {
	DisplayOptions();
}

function GenerateReport($LocationForm) {
	global $RootPath, $Theme, $_SESSION; // Make global variables available

	// Initialise no input errors
	$InputError = FALSE; // In a real scenario, add validation if needed

	if (!$InputError) {
		$SQL = "SELECT stockrequest.dispatchid,
					locations.locationname,
					stockrequest.despatchdate,
					stockrequest.narrative,
					departments.description AS departmentdescription, -- Alias to avoid conflict
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
					AND stockrequest.loccode='" . $LocationForm . "'
				ORDER BY stockrequest.dispatchid"; // Added order for consistency

		$Result = DB_query($SQL);

		if (DB_num_rows($Result) == 0) {
			$Title = _('Print Authorized Internal Stock Request still not fulfilled');
			include('includes/header.php');
			prnMsg(_('No Pending Authorized Internal Stock Requests found for the selected location.'), 'info');
			include('includes/footer.php');
			exit(); // Stop script execution
		}

		// Start building the HTML
		$HTML = '';

		// Add styles common to PDF and Screen view
		// For PDF, external CSS might need specific configuration or inline styles are safer.
		$HTML .= '<html>
					<head>
						<meta charset="UTF-8">
						<title>' . _('Pending Stock Requests') . '</title>
						<style>
							body { font-family: sans-serif; }
							.page_title_text { text-align: center; font-size: 1.2em; font-weight: bold; padding-bottom: 15px; }
							.request-block { border: 1px solid #000; margin-bottom: 15px; padding: 10px; page-break-inside: avoid; }
							.request-header p { margin: 2px 0; }
							.items-table { width: 100%; border-collapse: collapse; margin-top: 10px; }
							.items-table th, .items-table td { border: 1px solid #ccc; padding: 4px; text-align: left; }
							.items-table th { background-color: #eee; font-weight: bold; text-align: center; }
							.items-table td.number { text-align: right; }
							.items-table td.centre { text-align: center; }
							.items-table td.text { text-align: left; }
							/* Minimal styling for spreadsheet compatibility */
							@media print {
								.request-block { border: none; } /* Simplify for printing if needed */
							}
						</style>
					</head>
					<body>';

		$HTML .= '<div class="page_title_text">' . _('Pending Authorized Internal Stock Requests for Location:') . ' ' . $LocationForm . '</div>';

		while ($MyRow = DB_fetch_array($Result)) {

			$HTML .= '<div class="request-block">';
			$HTML .= '<div class="request-header">';
			$HTML .= '<p><strong>' . _('From Location') . ':</strong> ' . $MyRow['locationname'] . '</p>';
			$HTML .= '<p><strong>' . _('To Department') . ':</strong> ' . $MyRow['departmentdescription'] . '</p>';
			$HTML .= '<p><strong>' . _('Request Date') . ':</strong> ' . ConvertSQLDate($MyRow['despatchdate']) . '</p>';
			$HTML .= '<p><strong>' . _('Initiator') . ':</strong> ' . $MyRow['initiator'] . ' - ' . $MyRow['realname'] . '</p>';
			$HTML .= '<p><strong>' . _('Request #') . ':</strong> ' . $MyRow['dispatchid'] . '</p>';
			if (!empty($MyRow['narrative'])) {
				$HTML .= '<p><strong>' . _('Narrative') . ':</strong> ' . $MyRow['narrative'] . '</p>';
			}
			$HTML .= '</div>'; // end request-header

			// Get items for this request
			$LineSQL = "SELECT stockrequestitems.dispatchitemsid,
								stockrequestitems.dispatchid,
								stockrequestitems.stockid,
								stockrequestitems.decimalplaces,
								stockrequestitems.uom,
								stockmaster.description,
								stockrequestitems.quantity,
								stockrequestitems.qtydelivered,
								(stockrequestitems.quantity - stockrequestitems.qtydelivered) AS qtypending,
								stockmaster.controlled
						FROM stockrequestitems
						LEFT JOIN stockmaster
							ON stockmaster.stockid=stockrequestitems.stockid
						WHERE dispatchid='" . $MyRow['dispatchid'] . "'
							AND completed=0
							AND (stockrequestitems.quantity - stockrequestitems.qtydelivered) > 0"; // Only show lines with pending qty

			$LineResult = DB_query($LineSQL);

			if (DB_num_rows($LineResult) > 0) {
				$HTML .= '<table class="items-table">';
				$HTML .= '<thead>
							<tr>
								<th>#</th>
								<th>' . _('Item Code') . '</th>
								<th>' . _('Description') . '</th>
								<th>' . _('Requested') . '</th>
								<th>' . _('Delivered') . '</th>
								<th>' . _('Pending') . '</th>
								<th>' . _('UoM') . '</th>
							</tr>
						</thead>';
				$HTML .= '<tbody>';
				$i = 1;
				while ($MyLine = DB_fetch_array($LineResult)) {
					$HTML .= '<tr>';
					$HTML .= '<td class="centre">' . $i . '</td>';
					$HTML .= '<td class="text">' . $MyLine['stockid'] . '</td>';
					$HTML .= '<td class="text">' . $MyLine['description'] . '</td>';
					$HTML .= '<td class="number">' . locale_number_format($MyLine['quantity'], $MyLine['decimalplaces']) . '</td>';
					$HTML .= '<td class="number">' . locale_number_format($MyLine['qtydelivered'], $MyLine['decimalplaces']) . '</td>';
					$HTML .= '<td class="number">' . locale_number_format($MyLine['qtypending'], $MyLine['decimalplaces']) . '</td>';
					$HTML .= '<td class="text">' . $MyLine['uom'] . '</td>';
					$HTML .= '</tr>';
					$i++;
				}
				$HTML .= '</tbody></table>';
			} else {
				$HTML .= '<p>' . _('No pending items found for this request.') . '</p>';
			}
			$HTML .= '</div>'; // end request-block
		} // End while loop for requests

		$HTML .= '</body></html>';

		// Now process the generated $HTML based on the button pressed
		if (isset($_POST['PrintPDF'])) {
			// Use DomPDF
			 // Ensure DomPDF is loaded via Composer

			$dompdf = new Dompdf(['chroot' => __DIR__, 'isRemoteEnabled' => true]); // chroot for local assets, remote enabled if using external images/css
			$dompdf->loadHtml($HTML);

			// (Optional) Setup the paper size and orientation
			$dompdf->setPaper($_SESSION['PageSize'], 'portrait'); // Usually portrait for lists

			// Render the HTML as PDF
			$dompdf->render();

			// Output the generated PDF to Browser
			$PDFFileName = $_SESSION['DatabaseName'] . '_InternalStockRequest_' . date('Y-m-d') . '.pdf';
			// Setting Attachment to false streams inline, true forces download
			$dompdf->stream($PDFFileName, array("Attachment" => false));
			exit(); // Stop script after PDF output

		} elseif (isset($_POST['Spreadsheet'])) {
			// Use PhpSpreadsheet
			 // Ensure PhpSpreadsheet is loaded via Composer

			// Set Headers for ODS download
			header('Content-Type: application/vnd.oasis.opendocument.spreadsheet'); // Correct mime type for ODS
			$FileName = 'InternalStockRequest-' . Date('Y-m-d') . '.ods';
			header('Content-Disposition: attachment;filename="' . $FileName . '"');
			header('Cache-Control: max-age=0');

			$reader = new HtmlReader();
			// This might require tweaking based on HTML complexity and PhpSpreadsheet version
			try {
			    $spreadsheet = $reader->loadFromString($HTML);

			    // Optional: Apply basic styling or adjustments if needed
                // Example: Set column widths (requires knowing sheet structure)
                // $spreadsheet->getActiveSheet()->getColumnDimension('C')->setWidth(40);

			    $writer = IOFactory::createWriter($spreadsheet, 'Ods');
			    $writer->save('php://output');
			} catch (\PhpOffice\PhpSpreadsheet\Reader\Exception $e) {
			    // Handle cases where HTML parsing might fail
			    error_log("Error loading HTML into Spreadsheet: " . $e->getMessage());
			    // Provide a fallback or error message
			    echo "Error creating spreadsheet from HTML content.";
			}
			exit(); // Stop script after ODS output

		} else { // Default to 'View' which corresponds to the 'submit' button
			$Title = _('View Authorized Internal Stock Request still not fulfilled');
			include('includes/header.php');
			// Removed the specific image/title echo here, as the $HTML includes a title.
			// You can add it back if needed:
			// echo '<p class="page_title_text"><img src="' . $RootPath . '/css/' . $Theme . '/images/magnifier.png" title="' . $Title . '" alt="" />' . ' ' . $Title . '</p>';
			echo $HTML; // Display the generated HTML
			include('includes/footer.php');
		}

	} else {
		// Handle Input Errors if validation was added
		$Title = _('Input Error');
		include('includes/header.php');
		echo '<p class="page_title_text">
				<img src="' . $RootPath . '/css/' . $Theme . '/images/warning.png" title="' . $Title . '" alt="" />' . ' ' . $Title .
			'</p>';
		// Display specific error messages here using prnMsg()
		// prnMsg($InputErrorMessage, "warn");
		echo '<br /><a href="' . htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') . '">' . _('Back') . '</a>';
		include('includes/footer.php');
	}
} // End of function GenerateReport()


function DisplayOptions() {
    global $RootPath, $_SESSION; // Make global variables available
	$Title = _('Print Authorized Internal Stock Request still not fulfilled');
	include('includes/header.php');

	echo '<form action="' . htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') . '" method="post">';
	echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';

	echo '<p class="page_title_text">
			<img src="' . $RootPath . '/css/' . $_SESSION['Theme'] . '/images/magnifier.png" title="' . $Title . '" alt="" />' . ' ' . $Title . '
		</p>';

	echo '<fieldset>';
	echo '<legend>' . _('Selection Criteria') . '</legend>';
	// Assuming FieldToSelectOneLocation is a custom function defined elsewhere
	// Make sure it's included or defined.
	echo FieldToSelectOneLocation("LocationForm", $_POST['LocationForm'], _('Location from'), '', 'CANVIEW', 1, true, false);
	echo '</fieldset>';

	// Buttons similar to FixedAssetRegister.php
	echo '<div class="centre">
            <br />
            <input type="submit" name="submit" value="' . _('View Requests') . '" />&nbsp;
            <input type="submit" name="PrintPDF" value="' . _('Print as PDF') . '" />&nbsp;
            <input type="submit" name="Spreadsheet" value="' . _('Export as ODS') . '" />
            <br />
          </div>';

	echo '</form>';

	include('includes/footer.php');

} // End of function DisplayOptions()

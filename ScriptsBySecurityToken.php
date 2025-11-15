<?php

require(__DIR__ . '/includes/session.php');

use Dompdf\Dompdf;

include('includes/SetDomPDFOptions.php');

if (isset($_POST['PrintPDF']) or isset($_POST['Spreadsheet']) or isset($_POST['View'])) {

	if (!isset($_POST['Tokens'])) {
		$Title = __('Scripts by Security Token Report Error');
		include('includes/header.php');
		prnMsg(__('No security tokens were selected. Please select at least one token to report on.'), 'error');
		echo '<br /><a href="' . $RootPath . '/ScriptsbySecurityToken.php">' . __('Back to the menu') . '</a>';
		include('includes/footer.php');
		exit();
	}

	/* Now figure out the data to report */
	$SQL = "SELECT securitytokens.tokenid,
				securitytokens.tokenname,
				scripts.script,
				scripts.description
			FROM scripts
			INNER JOIN securitytokens ON scripts.pagesecurity = securitytokens.tokenid ";

	if (!in_array('All', $_POST['Tokens'])) {
		$SQL .= "WHERE scripts.pagesecurity IN ('" . implode("','", $_POST['Tokens']) . "') ";
	}

	$SQL .= "ORDER BY securitytokens.tokenid, scripts.script";

	$ErrMsg = __('The scripts could not be retrieved');
	$Result = DB_query($SQL, $ErrMsg);

	if (DB_num_rows($Result) == 0) {
		$Title = __('Print Scripts by Security Token Error');
		include('includes/header.php');
		prnMsg(__('There were no scripts found for the selected security tokens'), 'info');
		echo '<br /><a href="' . $RootPath . '/ScriptsbySecurityToken.php">' . __('Back to the menu') . '</a>';
		include('includes/footer.php');
		exit();
	}

	$HTML = '';

	if (isset($_POST['PrintPDF'])) {
		$HTML .= '<html>
					<head>';
		$HTML .= '<link href="css/reports.css" rel="stylesheet" type="text/css" />';
	}

	$HTML .= '<meta name="author" content="WebERP " . $Version">
			<meta name="Creator" content="webERP https://www.weberp.org">
			</head>
			<body>
				<div class="centre" id="ReportHeader">
					' . $_SESSION['CompanyRecord']['coyname'] . '<br />
					' . __('Scripts by Security Token Report') . '<br />
					' . __('Printed') . ': ' . date($_SESSION['DefaultDateFormat']) . '<br />
				</div>
				<table>
					<thead>
						<tr>
							<th>' . __('Script') . '</th>
							<th>' . __('Description') . '</th>
						</tr>
					</thead>
					<tbody>';

	$CurrentToken = '';

	while ($MyRow = DB_fetch_array($Result)) {

		if ($CurrentToken != $MyRow['tokenid']) {
			$HTML .= '<tr>
						<th colspan="2"><h3>' . $MyRow['tokenid'] . ' - ' . $MyRow['tokenname'] . '</h3></th>
					</tr>';
			$CurrentToken = $MyRow['tokenid'];
		}

		$HTML .= '<tr class="striped_row">
					<td>' . $MyRow['script'] . '</td>
					<td>' . $MyRow['description'] . '</td>
				</tr>';
	}

	if (isset($_POST['PrintPDF']) or isset($_POST['Email'])) {
		$HTML .= '</tbody>
				<div class="footer fixed-section">
					<div class="right">
						<span class="page-number">Page </span>
					</div>
				</div>
			</table>';
	} else {
		$HTML .= '</tbody>
				</table>
				<div class="centre">
					<form><input type="submit" name="close" value="' . __('Close') . '" onclick="window.close()" /></form>
				</div>';
	}
	$HTML .= '</body>
		</html>';

	if (isset($_POST['PrintPDF'])) {
		// Clear output buffer to prevent file corruption
		if (ob_get_length()) {
			ob_end_clean();
		}

		$DomPDF = new Dompdf($DomPDFOptions); // Pass the options object defined in SetDomPDFOptions.php containing common options
		$DomPDF->loadHtml($HTML);

		// Setup the paper size and orientation
		$DomPDF->setPaper($_SESSION['PageSize'], 'portrait');

		// Render the HTML as PDF
		$DomPDF->render();

		// Output the generated PDF to Browser
		$DomPDF->stream($_SESSION['DatabaseName'] . '_ScriptsBySecurityToken_' . date('Y-m-d') . '.pdf', array(
			"Attachment" => false
		));
		exit();
	} elseif (isset($_POST['Spreadsheet'])) {
		// Clear output buffer to prevent file corruption
		if (ob_get_length()) {
			ob_end_clean();
		}
		
		header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');

		$File = 'ScriptsBySecurityToken-' . date('Y-m-d') . '.' . 'ods';

		header('Content-Disposition: attachment;filename="' . $File . '"');
		header('Cache-Control: max-age=0');
		$reader = new \PhpOffice\PhpSpreadsheet\Reader\Html();
		$spreadsheet = $reader->loadFromString($HTML);

		$writer = \PhpOffice\PhpSpreadsheet\IOFactory::createWriter($spreadsheet, 'Ods');
		$writer->save('php://output');
		exit();
	} else {
		$Title = __('Scripts by Security Token Report');
		include('includes/header.php');
		echo '<p class="page_title_text"><img src="' . $RootPath . '/css/' . $Theme . '/images/security.png" title="' . __('Security') . '" alt="" />' . ' ' . $Title . '</p>';
		echo $HTML;
		include('includes/footer.php');
	}
} else { /*The option to print PDF nor to create the CSV was not hit */

	$Title = __('Scripts by Security Token Reporting');
	$ViewTopic = 'Security';
	$BookMark = 'ScriptsByToken';
	include('includes/header.php');

	echo '<p class="page_title_text">
			<img src="' . $RootPath . '/css/' . $Theme . '/images/security.png" title="' . __('Security') . '" alt="" />' . ' ' . $Title . '
		</p>';

	echo '<form action="' . htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') . '" method="post" target="_blank">
		<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';

	echo '<fieldset>
				<field>
					<label for="Tokens">' . __('Select Security Tokens') . ':</label>
					<select autofocus="autofocus" required="required" minlength="1" name="Tokens[]" multiple="multiple" size="12">';

	echo '<option value="All">' . __('All Security Tokens') . '</option>';

	$SQL = "SELECT tokenid, tokenname FROM securitytokens ORDER BY tokenid";
	$TokenResult = DB_query($SQL);

	while ($MyRow = DB_fetch_array($TokenResult)) {
		if (isset($_POST['Tokens']) and in_array($MyRow['tokenid'], $_POST['Tokens'])) {
			echo '<option selected="selected" value="' . $MyRow['tokenid'] . '">' . $MyRow['tokenid'] . ' - ' . $MyRow['tokenname'] . '</option>';
		} else {
			echo '<option value="' . $MyRow['tokenid'] . '">' . $MyRow['tokenid'] . ' - ' . $MyRow['tokenname'] . '</option>';
		}
	}
	echo '</select>
		</field>
		</fieldset>
		<div class="centre">
				<input type="submit" name="PrintPDF" title="' . __('Produce PDF Report') . '" value="' . __('Print PDF') . '" />
				<input type="submit" name="View" title="' . __('View Report') . '" value="' . __('View') . '" />
				<input type="submit" name="Spreadsheet" title="' . __('Spreadsheet') . '" value="' . __('Spreadsheet') . '" />
		</div>';
	echo '</form>';

	include('includes/footer.php');
} /*end of else not PrintPDF */

<?php

require(__DIR__ . '/includes/session.php');

use Dompdf\Dompdf;

if (isset($_POST['PrintPDF']) or isset($_POST['View'])) {

	  /*Now figure out the bills to report for the part range under review */
	$SQL = "SELECT bom.parent,
				bom.component,
				stockmaster.description as compdescription,
				stockmaster.decimalplaces,
				stockmaster.units,
				bom.quantity,
				bom.loccode,
				bom.workcentreadded,
				bom.effectiveto AS eff_to,
				bom.effectiveafter AS eff_frm
			FROM stockmaster INNER JOIN bom
			ON stockmaster.stockid=bom.component
			INNER JOIN locationusers ON locationusers.loccode=bom.loccode AND locationusers.userid='" .  $_SESSION['UserID'] . "' AND locationusers.canview=1
			WHERE bom.parent >= '" . $_POST['FromCriteria'] . "'
			AND bom.parent <= '" . $_POST['ToCriteria'] . "'
			AND bom.effectiveto > CURRENT_DATE AND bom.effectiveafter <= CURRENT_DATE
			ORDER BY bom.parent,
					bom.component";

	$ErrMsg = ('The Bill of Material listing could not be retrieved');
	$BOMResult = DB_query($SQL, $ErrMsg);

	if (DB_num_rows($BOMResult)==0){
	   $Title = __('Bill of Materials Listing') . ' - ' . __('Problem Report');
	   include('includes/header.php');
	   prnMsg( __('The Bill of Material listing has no bills to report on'),'warn');
	   include('includes/footer.php');
	   exit();
	}


	$HTML = '';

	if (isset($_POST['PrintPDF'])) {
		$HTML .= '<html>
					<head>';
		$HTML .= '<link href="css/reports.css" rel="stylesheet" type="text/css" />';
	}

	$HTML .= '<meta name="author" content="WebERP">
					<meta name="Creator" content="webERP https://www.weberp.org">
				</head>
				<body>
				<div class="centre" id="ReportHeader">
					' . $_SESSION['CompanyRecord']['coyname'] . '<br />
					' . __('Bill Of Material Listing for Parts Between') . ' ' . $_POST['FromCriteria'] . ' ' . __('and') . ' ' . $_POST['ToCriteria'] . '<br />
					' . __('Printed') . ': ' . Date($_SESSION['DefaultDateFormat']) . '<br />
				</div>
				<table>
					<thead>
						<tr>
							<th>' . __('Component Part') . '</th>
							<th>' . __('Description') . '</th>
							<th>' . __('Effective After') . '</th>
							<th>' . __('Effective To') . '</th>
							<th>' . __('Location') . '</th>
							<th>' . __('Work') . '<br />' . __('Centre') . '</th>
							<th>' . __('Quantity') . '</th>
						</tr>
					</thead>
					<tbody>';

	$ParentPart = '';

	while ($BOMList = DB_fetch_array($BOMResult)){

		if ($ParentPart!=$BOMList['parent']){
			$SQL = "SELECT description FROM stockmaster WHERE stockmaster.stockid = '" . $BOMList['parent'] . "'";
			$ParentResult = DB_query($SQL);
			$ParentRow = DB_fetch_row($ParentResult);
			$HTML .= '<tr class="total_row">
						<td>' . $BOMList['parent'] . '</td>
						<td>' . $ParentRow[0] . '</td>
						<td colspan="5"></td>
					</tr>';
			$ParentPart = $BOMList['parent'];
		}
		$HTML .= '<tr class="striped_row">
					<td>' . $BOMList['component'] . '</td>
					<td>' . $BOMList['compdescription'] . '</td>
					<td class="date">' . ConvertSQLDate($BOMList['eff_frm']) . '</td>
					<td class="date">' . ConvertSQLDate($BOMList['eff_to']) . '</td>
					<td>' . $BOMList['loccode'] . '</td>
					<td>' . $BOMList['workcentreadded'] . '</td>
					<td class="number">' . locale_number_format($BOMList['quantity'],$BOMList['decimalplaces']) . ' ' . $BOMList['units'] . '</td>
				</tr>';

	} /*end BOM Listing while loop */

	if (isset($_POST['PrintPDF'])) {
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
		$dompdf = new Dompdf(['chroot' => __DIR__]);
		$dompdf->loadHtml($HTML);

		// (Optional) Setup the paper size and orientation
		$dompdf->setPaper($_SESSION['PageSize'], 'landscape');

		// Render the HTML as PDF
		$dompdf->render();

		// Output the generated PDF to Browser
		$dompdf->stream($_SESSION['DatabaseName'] . '_BOMListing_' . date('Y-m-d') . '.pdf', array(
			"Attachment" => false
		));
	} else {
		$Title = __('Bill Of Material Listing');
		include('includes/header.php');
		echo '<p class="page_title_text"><img src="' . $RootPath . '/css/' . $Theme . '/images/maintenance.png" title="' . $Title . '" alt="" />' . ' ' . $Title . '</p>';
		echo $HTML;
		include('includes/footer.php');
	}

} else { /*The option to print PDF was not hit */

	$Title=__('Bill Of Material Listing');

	$ViewTopic = 'Manufacturing';
	$BookMark = '';

	include('includes/header.php');
	echo '<p class="page_title_text"><img src="'.$RootPath.'/css/'.$Theme.'/images/reports.png" title="' . __('Search') .
		'" alt="" />' . ' ' . $Title . '</p>';
	if (!isset($_POST['FromCriteria']) || !isset($_POST['ToCriteria'])) {

	/*if $FromCriteria is not set then show a form to allow input	*/

		echo '<form action="' . htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') . '" method="post" target="_blank">
			  <input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />
			  <fieldset>
				<legend>', __('Report Criteria'), '</legend>';

		echo '<field>
				<label for="FromCriteria">' . __('From Inventory Part Code') . ':' . '</label>
				<input tabindex="1" type="text" autofocus="autofocus" required="required" data-type="no-illegal-chars" title="" name="FromCriteria" size="20" maxlength="20" value="1" />
				<fieldhelp>' . __('Enter the lowest alpha code of parent bom items to list the bill of material for') .  '</fieldhelp>
			</field>';

		echo '<field>
				<label for="ToCriteria">' . __('To Inventory Part Code') . ':' . '</label>
				<input tabindex="2" type="text" required="required" data-type="no-illegal-chars" title="" name="ToCriteria" size="20" maxlength="20" value="zzzzzzz" />
				<fieldhelp>' . __('Enter the end alpha numeric code of any parent bom items to list the bill of material for') .  '</fieldhelp>
			</field>';

		echo '</fieldset>
				<div class="centre">
					<input type="submit" name="PrintPDF" title="Produce PDF Report" value="' . __('Print PDF') . '" />
					<input type="submit" name="View" title="View Report" value="' . __('View') . '" />
				</div>
			</form>';
	}
	include('includes/footer.php');

} /*end of else not PrintPDF */

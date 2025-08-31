<?php

/* Shows the bill of material indented for each level */

require(__DIR__ . '/includes/session.php');

use Dompdf\Dompdf;

if (isset($_POST['PrintPDF']) or isset($_POST['View'])) {

	$SQL = "DROP TABLE IF EXISTS tempbom";
	$Result = DB_query($SQL);
	$SQL = "DROP TABLE IF EXISTS passbom";
	$Result = DB_query($SQL);
	$SQL = "DROP TABLE IF EXISTS passbom2";
	$Result = DB_query($SQL);
	$SQL = "CREATE TEMPORARY TABLE passbom (
				part char(20),
				sortpart text) DEFAULT CHARSET=utf8";
	$ErrMsg = __('The SQL to create passbom failed with the message');
	$Result = DB_query($SQL, $ErrMsg);

	$SQL = "CREATE TEMPORARY TABLE tempbom (
				parent char(20),
				component char(20),
				sortpart text,
				level int,
				workcentreadded char(5),
				loccode char(5),
				effectiveafter date,
				effectiveto date,
				quantity double) DEFAULT CHARSET=utf8";
	$Result = DB_query($SQL,__('Create of tempbom failed because'));
	// First, find first level of components below requested assembly
	// Put those first level parts in passbom, use COMPONENT in passbom
	// to link to PARENT in bom to find next lower level and accumulate
	// those parts into tempbom

	// This finds the top level
	$SQL = "INSERT INTO passbom (part, sortpart)
			   SELECT bom.component AS part,
					  CONCAT(bom.parent,bom.component) AS sortpart
			  FROM bom
			  WHERE bom.parent ='" . $_POST['Part'] . "'
			  AND bom.effectiveafter <= CURRENT_DATE
			  AND bom.effectiveto > CURRENT_DATE";
	$Result = DB_query($SQL);

	$LevelCounter = 2;
	// $LevelCounter is the level counter
	$SQL = "INSERT INTO tempbom (
				parent,
				component,
				sortpart,
				level,
				workcentreadded,
				loccode,
				effectiveafter,
				effectiveto,
				quantity)
			  SELECT bom.parent,
					 bom.component,
					 CONCAT(bom.parent,bom.component) AS sortpart,
					 " . $LevelCounter . " AS level,
					 bom.workcentreadded,
					 bom.loccode,
					 bom.effectiveafter,
					 bom.effectiveto,
					 bom.quantity
			  FROM bom
			  INNER JOIN locationusers ON locationusers.loccode=bom.loccode AND locationusers.userid='" .  $_SESSION['UserID'] . "' AND locationusers.canview=1
			  WHERE bom.parent ='" . $_POST['Part'] . "'
			  AND bom.effectiveafter <= CURRENT_DATE
			  AND bom.effectiveto > CURRENT_DATE";
	$Result = DB_query($SQL);
	//echo "<br />sql is $SQL<br />";
	// This while routine finds the other levels as long as $ComponentCounter - the
	// component counter - finds there are more components that are used as
	// assemblies at lower levels

	$ComponentCounter = 1;
	if ($_POST['Levels'] == 'All') {
		while ($ComponentCounter > 0) {
			$LevelCounter++;
			$SQL = "INSERT INTO tempbom (
					parent,
					component,
					sortpart,
					level,
					workcentreadded,
					loccode,
					effectiveafter,
					effectiveto,
					quantity)
				  SELECT bom.parent,
						 bom.component,
						 CONCAT(passbom.sortpart,bom.component) AS sortpart,
						 $LevelCounter as level,
						 bom.workcentreadded,
						 bom.loccode,
						 bom.effectiveafter,
						 bom.effectiveto,
						 bom.quantity
				FROM bom
				 INNER JOIN passbom ON bom.parent = passbom.part
				 INNER JOIN locationusers ON locationusers.loccode=bom.loccode AND locationusers.userid='" .  $_SESSION['UserID'] . "' AND locationusers.canview=1
				WHERE bom.effectiveafter <= CURRENT_DATE
				AND bom.effectiveto > CURRENT_DATE";
			$Result = DB_query($SQL);

			$SQL = "DROP TABLE IF EXISTS passbom2";
			$Result = DB_query($SQL);

			$SQL = "ALTER TABLE passbom RENAME AS passbom2";
			$Result = DB_query($SQL);

			$SQL = "DROP TABLE IF EXISTS passbom";
			$Result = DB_query($SQL);

			$SQL = "CREATE TEMPORARY TABLE passbom (
								part char(20),
								sortpart text) DEFAULT CHARSET=utf8";
			$Result = DB_query($SQL);


			$SQL = "INSERT INTO passbom (part, sortpart)
					   SELECT bom.component AS part,
							  CONCAT(passbom2.sortpart,bom.component) AS sortpart
					   FROM bom,passbom2
					   WHERE bom.parent = passbom2.part
					   AND bom.effectiveafter <= CURRENT_DATE
					   AND bom.effectiveto > CURRENT_DATE";
			$Result = DB_query($SQL);


			$SQL = "SELECT COUNT(*) FROM bom,passbom WHERE bom.parent = passbom.part";
			$Result = DB_query($SQL);

			$MyRow = DB_fetch_row($Result);
			$ComponentCounter = $MyRow[0];

		} // End of while $ComponentCounter > 0
	} // End of if $_POST['Levels']

	$SQL = "SELECT stockmaster.stockid,
				   stockmaster.description
			  FROM stockmaster
			  WHERE stockid = " . "'" . $_POST['Part'] . "'";
	$Result = DB_query($SQL);
	$MyRow = DB_fetch_array($Result);
	$Assembly = $_POST['Part'];
	$AssemblyDesc = $MyRow['description'];

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
					' . __('Indented BOM Listing For') . ' ' . mb_strtoupper($_POST['Part']) . '<br />
					' . __('Printed') . ': ' . Date($_SESSION['DefaultDateFormat']) . '<br />
				</div>
				<table>
					<thead>
						<tr>
							<th class="SortedColumn">' . __('Part Number') . '</th>
							<th class="SortedColumn">' . __('M/B') . '</th>
							<th class="SortedColumn">' . __('Description') . '</th>
							<th class="SortedColumn">' . __('Location') . '</th>
							<th class="SortedColumn">' . __('Work') . '<br />' . __('Centre') . '</th>
							<th class="SortedColumn">' . __('Quantity') . '</th>
							<th class="SortedColumn">' . __('UOM') . '</th>
							<th class="SortedColumn">' . __('From Date') . '</th>
							<th class="SortedColumn">' . __('To Date') . '</th>
						</tr>
					</thead>
					<tbody>';

	$Tot_Val=0;
	$SQL = "SELECT tempbom.*,
				stockmaster.description,
				stockmaster.mbflag,
				stockmaster.units
			FROM tempbom,stockmaster
			WHERE tempbom.component = stockmaster.stockid
			ORDER BY sortpart";
	$Result = DB_query($SQL);

	// $Fill is used to alternate between lines with transparent and painted background

	while ($MyRow = DB_fetch_array($Result)){

		$FormatedEffectiveAfter = ConvertSQLDate($MyRow['effectiveafter']);
		$FormatedEffectiveTo = ConvertSQLDate($MyRow['effectiveto']);

		$HTML .= '<tr class="striped_row">
					<td>' . $MyRow['component'] . '</td>
					<td>' . $MyRow['mbflag'] . '</td>
					<td>' . $MyRow['description'] . '</td>
					<td>' . $MyRow['loccode'] . '</td>
					<td>' . $MyRow['workcentreadded'] . '</td>
					<td class="number">' . locale_number_format($MyRow['quantity'],'Variable') . '</td>
					<td>' . $MyRow['units'] . '</td>
					<td class="date">' . $FormatedEffectiveAfter . '</td>
					<td class="date">' . $FormatedEffectiveTo . '</td>
				</tr>';

	} /*end while loop */

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
		$dompdf->stream($_SESSION['DatabaseName'] . '_BOMIndented_' . date('Y-m-d') . '.pdf', array(
			"Attachment" => false
		));
	} else {
		$Title = __('Indented BOM Listing');
		include('includes/header.php');
		echo '<p class="page_title_text"><img src="' . $RootPath . '/css/' . $Theme . '/images/maintenance.png" title="' . $Title . '" alt="" />' . ' ' . $Title . '</p>';
		echo $HTML;
		include('includes/footer.php');
	}

} else { /*The option to print PDF was not hit so display form */

	$ViewTopic = 'Manufacturing';
	$BookMark = '';

	$Title=__('Indented BOM Listing');
	include('includes/header.php');
		echo '<p class="page_title_text"><img src="'.$RootPath.'/css/'.$Theme.'/images/maintenance.png" title="' . __('Search') . '" alt="" />' . ' ' . $Title . '</p>';

	echo '<form action="' . htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') . '" method="post" target="_blank">
		  <input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';

	echo '<fieldset>
			<legend>', __('Select Report Criteria'), '</legend>';
	echo '<field>
			<label for="Part">' . __('Part') . ':</label>
			<input type="text" name="Part" autofocus="autofocus" required="required" data-type="no-illegal-chars" title="" size="20" />
			<fieldhelp>' . __('Enter the item code of parent item to list the bill of material for') . '</fieldhelp>
		</field>
		<field>
			<label for="Levels">' . __('Levels') . ':</label>
			<select name="Levels">
				<option selected="selected" value="All">' . __('All Levels') . '</option>
				<option value="One">' . __('One Level') . '</option>
			</select>
		</field>
		</fieldset>
		<div class="centre">
				<input type="submit" name="PrintPDF" title="Produce PDF Report" value="' . __('Print PDF') . '" />
				<input type="submit" name="View" title="View Report" value="' . __('View') . '" />
		</div>
	</form>';

	include('includes/footer.php');

} /*end of else not PrintPDF */

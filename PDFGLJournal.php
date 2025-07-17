<?php

/* $Revision: 1.5 $ */

include('includes/session.php');
use Dompdf\Dompdf;

if (isset($_POST['JournalNo'])) {
	$JournalNo = $_POST['JournalNo'];
	$Type = $_POST['Type'];
} else if (isset($_GET['JournalNo'])) {
	$JournalNo = $_GET['JournalNo'];
	$Type = $_GET['Type'];
} else {
	$JournalNo = '';
}

if (isset($_GET['PDF'])) {
	$_POST['PrintPDF'] = True;
} else if (isset($_GET['View'])) {
	$_POST['View'] = True;
}

if (!isset($JournalNo) OR !isset($Type)) {
	prnMsg(_('This page should be called with Journal No and Type'), 'error');
	include('includes/footer.php');
	exit;
}

if (isset($_POST['PrintPDF']) or isset($_POST['View'])) {
	$SQL = "SELECT gltrans.counterindex,
				gltrans.typeno,
				gltrans.trandate,
				gltrans.account,
				chartmaster.accountname,
				gltrans.narrative,
				gltrans.amount,
				gltrans.jobref
			FROM gltrans
			INNER JOIN chartmaster
				ON gltrans.account = chartmaster.accountcode
			WHERE gltrans.type = '" . $Type . "'
				AND gltrans.typeno = '" . $JournalNo . "'";

	$Result = DB_query($SQL);

	$HTML = '';

	if (isset($_POST['PrintPDF'])) {
		$HTML .= '<html>
					<head>';
		$HTML .= '<link href="css/reports.css" rel="stylesheet" type="text/css" />';
	}

	$HTML .= '<meta name="author" content="WebERP">
					<meta name="Creator" content="webERP https://www.weberp.org">
				</head>
				<body>';

	$HTML .= '<table>';
	$HTML .= '<tr>
				<th colspan="7"><h3>' . _('General Ledger Journal') . '</h3></th>
			</tr>
			<tr>
				<th>' . _('Account Code') . '</th>
				<th>' . _('Description') . '</th>
				<th>' . _('Date') . '</th>
				<th>' . _('Narrative') . '</th>
				<th>' . _('Amount') . '</th>
				<th>' . _('Tag') . '</th>
				<th>' . _('Job Reference') . '</th>
			  </tr>';

	while ($MyRow = DB_fetch_array($Result)) {
		$TagsSQL = "SELECT gltags.tagref,
							tags.tagdescription
						FROM gltags
						INNER JOIN tags
							ON gltags.tagref=tags.tagref
						WHERE gltags.counterindex='" . $MyRow['counterindex'] . "'";
		$TagsResult = DB_query($TagsSQL);

		$TagDescriptions = '';
		while ($TagRows = DB_fetch_array($TagsResult)) {
			$TagDescriptions .= $TagRows['tagref'] . ' - ' . $TagRows['tagdescription'] . '<br />';
		}
		$HTML .= '<tr class="striped_row">
					<td>' . $MyRow['account']. ' </td>
					<td>' . $MyRow['accountname']. ' </td>
					<td class="date">' . ConvertSQLDate($MyRow['trandate']). ' </td>
					<td>' . $MyRow['narrative']. ' </td>
					<td class="number">' . locale_number_format($MyRow['amount'], $_SESSION['CompanyRecord']['decimalplaces']) . '</td>
					<td>' . $TagDescriptions. ' </td>
					<td>' . $MyRow['jobref']. ' </td>
				  </tr>';
	}

	$HTML .= '</table>';
}

$HTML .= '</body></html>';

if (isset($_POST['PrintPDF'])) {
	// Handle PDF generation
	require 'vendor/autoload.php'; // Ensure DomPDF is installed via Composer

	$dompdf = new Dompdf(['chroot' => __DIR__]);
	$dompdf->loadHtml($HTML);

	// (Optional) Setup the paper size and orientation
	$dompdf->setPaper($_SESSION['PageSize'], 'landscape');

	// Render the HTML as PDF
	$dompdf->render();

	// Output the generated PDF to Browser
	$dompdf->stream($_SESSION['DatabaseName'] . '_Journal_' . date('Y-m-d') . '.pdf', array(
		"Attachment" => false
	));
} elseif (isset($_POST['View'])) {
	// Handle on-screen view
	$Title = _('General Ledger Journal');
	include('includes/header.php');
	echo '<p class="page_title_text"><img src="' . $RootPath . '/css/' . $Theme . '/images/maintenance.png" title="' . _('Search') . '" alt="" />' . ' ' . $Title . '</p>';
	echo $HTML;
		echo '<div class="centre">
				<form><input type="submit" name="close" value="' . _('Close') . '" onclick="window.close()" /></form>
			</div>';
	include('includes/footer.php');
} else {
	prnMsg(_('No valid action selected'), 'error');
	include('includes/footer.php');
	exit;
}

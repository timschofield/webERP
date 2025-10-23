<?php

require(__DIR__ . '/includes/session.php');

$Title = __('Tax Rates');
$ViewTopic = 'Tax';
$BookMark = 'TaxAuthorityRates';
include('includes/header.php');

echo '<p class="page_title_text"><img alt="" src="' . $RootPath . '/css/' . $Theme .
		'/images/maintenance.png" title="' .
		__('Tax Rates Maintenance') . '" />' . ' ' .
		__('Tax Rates Maintenance') . '</p>';

if(isset($_POST['TaxAuthority'])) {
	$TaxAuthority = $_POST['TaxAuthority'];
}
if(isset($_GET['TaxAuthority'])) {
	$TaxAuthority = $_GET['TaxAuthority'];
}

if(!isset($TaxAuthority)) {
	prnMsg(__('This page can only be called after selecting the tax authority to edit the rates for') . '. ' .
		__('Please select the Rates link from the tax authority page') . '<br /><a href="' .
		$RootPath . '/TaxAuthorities.php">' . __('click here') . '</a> ' .
		__('to go to the Tax Authority page'), 'error');
	include('includes/footer.php');
	exit();
}

if(isset($_POST['UpdateRates'])) {
	$TaxRatesResult = DB_query("SELECT taxauthrates.taxcatid,
										taxauthrates.taxrate,
										taxauthrates.dispatchtaxprovince
								FROM taxauthrates
								WHERE taxauthrates.taxauthority='" . $TaxAuthority . "'");

	while($MyRow=DB_fetch_array($TaxRatesResult)) {

		$SQL = "UPDATE taxauthrates SET taxrate=" . (filter_number_format($_POST[$MyRow['dispatchtaxprovince'] . '_' . $MyRow['taxcatid']])/100) . "
						WHERE taxcatid = '" . $MyRow['taxcatid'] . "'
						AND dispatchtaxprovince = '" . $MyRow['dispatchtaxprovince'] . "'
						AND taxauthority = '" . $TaxAuthority . "'";
		DB_query($SQL);
	}
	prnMsg(__('All rates updated successfully'),'info');
}

/* end of update code*/

/*Display updated rates*/

$TaxAuthDetail = DB_query("SELECT description
							FROM taxauthorities WHERE taxid='" . $TaxAuthority . "'");
$MyRow = DB_fetch_row($TaxAuthDetail);

echo '<form action="' . htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') . '" method="post">
	<div>
	<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />
	<input type="hidden" name="TaxAuthority" value="' . $TaxAuthority . '" />';

$TaxRatesResult = DB_query("SELECT taxauthrates.taxcatid,
									taxcategories.taxcatname,
									taxauthrates.taxrate,
									taxauthrates.dispatchtaxprovince,
									taxprovinces.taxprovincename
							FROM taxauthrates INNER JOIN taxauthorities
							ON taxauthrates.taxauthority=taxauthorities.taxid
							INNER JOIN taxprovinces
							ON taxauthrates.dispatchtaxprovince= taxprovinces.taxprovinceid
							INNER JOIN taxcategories
							ON taxauthrates.taxcatid=taxcategories.taxcatid
							WHERE taxauthrates.taxauthority='" . $TaxAuthority . "'
							ORDER BY taxauthrates.dispatchtaxprovince,
							taxauthrates.taxcatid");

if(DB_num_rows($TaxRatesResult)>0) {
	echo '<div class="centre"><h1>' . $MyRow[0] . '</h1></div>';// TaxAuthorityRates table title.

	echo '<table class="selection">
		<thead>
		<tr>
			<th class="SortedColumn">' . __('Deliveries From') . '<br />' . __('Tax Province') . '</th>
			<th class="SortedColumn">' . __('Tax Category') . '</th>
			<th class="SortedColumn">' . __('Tax Rate') . '</th>
			</tr>
		</thead>
		<tbody>';

	while($MyRow = DB_fetch_array($TaxRatesResult)) {
		echo '<tr class="striped_row">
				<td>', $MyRow['taxprovincename'], '</td>
				<td>', __($MyRow['taxcatname']), '</td>
				<td><input class="number" maxlength="5" name="', $MyRow['dispatchtaxprovince'] . '_' . $MyRow['taxcatid'], '" required="required" size="5" title="' . __('Input must be numeric') . '" type="text" value="', locale_number_format($MyRow['taxrate']*100,2), '" /></td>
			</tr>';
	}// End of while loop.
	echo '</tbody></table>
		<div class="centre">
		<input type="submit" name="UpdateRates" value="' . __('Update Rates') . '" />';
	//end if tax taxcatid/rates to show

} else {
	echo '<div class="centre">';
	prnMsg(__('There are no tax rates to show - perhaps the dispatch tax province records have not yet been created?'),'warn');
}
echo '</div>';// Closes Submit or prnMsg division.

echo '<div class="centre">
		<a href="' . $RootPath . '/TaxAuthorities.php">' . __('Tax Authorities Maintenance') .  '</a><br />
		<a href="' . $RootPath . '/TaxGroups.php">' . __('Tax Group Maintenance') .  '</a><br />
		<a href="' . $RootPath . '/TaxProvinces.php">' . __('Dispatch Tax Province Maintenance') .  '</a><br />
		<a href="' . $RootPath . '/TaxCategories.php">' . __('Tax Category Maintenance') .  '</a>
	</div>';

include('includes/footer.php');

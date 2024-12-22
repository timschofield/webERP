<?php

include('includes/session.php');
$Title = _('Tax Rates');
$ViewTopic = 'Tax';// Filename in ManualContents.php's TOC.
$BookMark = 'TaxAuthorityRates';// Anchor's id in the manual's html document.
include('includes/header.php');
echo '<p class="page_title_text"><img alt="" src="' . $RootPath . '/css/' . $Theme .
		'/images/maintenance.png" title="' .
		_('Tax Rates Maintenance') . '" />' . ' ' .
		_('Tax Rates Maintenance') . '</p>';

if(isset($_POST['TaxAuthority'])) {
	$TaxAuthority = $_POST['TaxAuthority'];
}
if(isset($_GET['TaxAuthority'])) {
	$TaxAuthority = $_GET['TaxAuthority'];
}

if(!isset($TaxAuthority)) {
	prnMsg(_('This page can only be called after selecting the tax authority to edit the rates for') . '. ' .
		_('Please select the Rates link from the tax authority page') . '<br /><a href="' .
		$RootPath . '/TaxAuthorities.php">' . _('click here') . '</a> ' .
		_('to go to the Tax Authority page'), 'error');
	include ('includes/footer.php');
	exit;
}

if(isset($_POST['UpdateRates'])) {
	$TaxRatesResult = DB_query("SELECT taxauthrates.taxcatid,
										taxauthrates.taxrate,
										taxauthrates.dispatchtaxprovince
								FROM taxauthrates
								WHERE taxauthrates.taxauthority='" . $TaxAuthority . "'");

	while($myrow=DB_fetch_array($TaxRatesResult)) {

		$sql = "UPDATE taxauthrates SET taxrate=" . (filter_number_format($_POST[$myrow['dispatchtaxprovince'] . '_' . $myrow['taxcatid']])/100) . "
						WHERE taxcatid = '" . $myrow['taxcatid'] . "'
						AND dispatchtaxprovince = '" . $myrow['dispatchtaxprovince'] . "'
						AND taxauthority = '" . $TaxAuthority . "'";
		DB_query($sql);
	}
	prnMsg(_('All rates updated successfully'),'info');
}

/* end of update code*/

/*Display updated rates*/

$TaxAuthDetail = DB_query("SELECT description
							FROM taxauthorities WHERE taxid='" . $TaxAuthority . "'");
$myrow = DB_fetch_row($TaxAuthDetail);

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
	echo '<div class="centre"><h1>' . $myrow[0] . '</h1></div>';// TaxAuthorityRates table title.

	echo '<table class="selection">
		<thead>
		<tr>
			<th class="ascending">' . _('Deliveries From') . '<br />' . _('Tax Province') . '</th>
			<th class="ascending">' . _('Tax Category') . '</th>
			<th class="ascending">' . _('Tax Rate') . '</th>
			</tr>
		</thead>
		<tbody>';

	while($myrow = DB_fetch_array($TaxRatesResult)) {
		printf('<tr class="striped_row">
				<td>%s</td>
				<td>%s</td>
				<td><input class="number" maxlength="5" name="%s" required="required" size="5" title="' . _('Input must be numeric') . '" type="text" value="%s" /></td>
				</tr>',
			// Deliveries From:
			$myrow['taxprovincename'],
			// Tax Category:
			_($myrow['taxcatname']),// Uses gettext() to translate 'Exempt', 'Freight' and 'Handling'.
			// Tax Rate:
			$myrow['dispatchtaxprovince'] . '_' . $myrow['taxcatid'],
			locale_number_format($myrow['taxrate']*100,2));
	}// End of while loop.
	echo '</tbody></table><br />
		<div class="centre">
		<input type="submit" name="UpdateRates" value="' . _('Update Rates') . '" />';
	//end if tax taxcatid/rates to show

} else {
	echo '<div class="centre">';
	prnMsg(_('There are no tax rates to show - perhaps the dispatch tax province records have not yet been created?'),'warn');
}
echo '</div>';// Closes Submit or prnMsg division.

echo '<br />
	<div class="centre">
		<a href="' . $RootPath . '/TaxAuthorities.php">' . _('Tax Authorities Maintenance') .  '</a><br />
		<a href="' . $RootPath . '/TaxGroups.php">' . _('Tax Group Maintenance') .  '</a><br />
		<a href="' . $RootPath . '/TaxProvinces.php">' . _('Dispatch Tax Province Maintenance') .  '</a><br />
		<a href="' . $RootPath . '/TaxCategories.php">' . _('Tax Category Maintenance') .  '</a>
	</div>';

include('includes/footer.php');
?>

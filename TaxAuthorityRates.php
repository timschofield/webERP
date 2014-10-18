<?php
/* $Id: TaxAuthorityRates.php 6287 2013-08-22 07:47:44Z daintree $*/

if (isset($_POST['TaxAuthority'])){
	$TaxAuthority = $_POST['TaxAuthority'];
}
if (isset($_GET['TaxAuthority'])){
	$TaxAuthority = $_GET['TaxAuthority'];
}

include('includes/session.inc');
$Title = _('Tax Rates');
include('includes/header.inc');

echo '<p class="page_title_text">
		<img src="'.$RootPath.'/css/'.$Theme.'/images/maintenance.png" title="' . _('Supplier Types')
	. '" alt="" />' . $Title. '
	</p>';


if (!isset($TaxAuthority)){
	prnMsg(_('This page can only be called after selecting the tax authority to edit the rates for') . '. ' . _('Please select the Rates link from the tax authority page') . '<br /><a href="' . $RootPath . '/TaxAuthorities.php">' . _('click here') . '</a> ' . _('to go to the Tax Authority page'),'error');
	include ('includes/footer.inc');
	exit;
}


if (isset($_POST['UpdateRates'])){

	$TaxRatesResult = DB_query("SELECT taxauthrates.taxcatid,
										taxauthrates.taxrate,
										taxauthrates.dispatchtaxprovince
								FROM taxauthrates
								WHERE taxauthrates.taxauthority='" . $TaxAuthority . "'",
								$db);

	while ($myrow=DB_fetch_array($TaxRatesResult)){

		$sql = "UPDATE taxauthrates SET taxrate=" . (filter_number_format($_POST[$myrow['dispatchtaxprovince'] . '_' . $myrow['taxcatid']])/100) . "
						WHERE taxcatid = '" . $myrow['taxcatid'] . "'
						AND dispatchtaxprovince = '" . $myrow['dispatchtaxprovince'] . "'
						AND taxauthority = '" . $TaxAuthority . "'";
		DB_query($sql,$db);
	}
	prnMsg(_('All rates updated successfully'),'info');
}

/* end of update code
*/

/*Display updated rates
*/

$TaxAuthDetail = DB_query("SELECT description
							FROM taxauthorities WHERE taxid='" . $TaxAuthority . "'",$db);
$myrow = DB_fetch_row($TaxAuthDetail);

echo '<form action="' . htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') . '" method="post">';
echo '<div>';
echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';

echo '<input type="hidden" name="TaxAuthority" value="' . $TaxAuthority . '" />';

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
							taxauthrates.taxcatid",
							$db);

if (DB_num_rows($TaxRatesResult)>0){

	echo '<table class="selection">';
	echo '<tr>
			<th colspan="3"><h3>' . _('Update') . ' ' . $myrow[0] . ' ' . _('Rates') . '</h3></th>
		</tr>';
	$TableHeader = '<tr>
						<th class="ascending">' . _('Deliveries From') . '<br />' . _('Tax Province') . '</th>
						<th class="ascending">' . _('Tax Category') . '</th>
						<th class="ascending">' . _('Tax Rate') . ' %</th>
					</tr>';
	echo $TableHeader;
	$j = 1;
	$k = 0; //row counter to determine background colour
	$OldProvince='';

	while ($myrow = DB_fetch_array($TaxRatesResult)){

		if ($OldProvince!=$myrow['dispatchtaxprovince'] AND $OldProvince!=''){
			echo '<tr style="background-color:#555555"><td colspan="3"></td></tr>';
		}

		if ($k==1){
			echo '<tr class="EvenTableRows">';
			$k=0;
		} else {
			echo '<tr class="OddTableRows">';
			$k=1;
		}

		printf('<td>%s</td>
				<td>%s</td>
				<td><input type="text" required="required" title="'._('Input must be numeric').'" class="number" name="%s" maxlength="5" size="5" value="%s" /></td>
				</tr>',
				$myrow['taxprovincename'],
				$myrow['taxcatname'],
				$myrow['dispatchtaxprovince'] . '_' . $myrow['taxcatid'],
				locale_number_format($myrow['taxrate']*100,2));

		$OldProvince = $myrow['dispatchtaxprovince'];

	}
//end of while loop
echo '</table>';
echo '<br />
		<div class="centre">
		<input type="submit" name="UpdateRates" value="' . _('Update Rates') . '" />';
} //end if tax taxcatid/rates to show
	else {
	prnMsg(_('There are no tax rates to show - perhaps the dispatch tax province records have not yet been created?'),'warn');
}

echo '<br />
	<br />
	<a href="' . $RootPath . '/TaxAuthorities.php">' . _('Tax Authorities') .  '</a>
	<br />
	<a href="' . $RootPath . '/TaxGroups.php">' . _('Tax Groupings') .  '</a>
	<br />
	<a href="' . $RootPath . '/TaxCategories.php">' . _('Tax Categories') .  '</a>
	<br />
	<a href="' . $RootPath . '/TaxProvinces.php">' . _('Dispatch Tax Provinces') .  '</a>
	</div>';

echo '</div>
      </form>';

include( 'includes/footer.inc' );
?>

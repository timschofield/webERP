<?php
/* $Revision: 1.15 $ */

$PageSecurity = 11; // only allow accountant access

if (isset($_POST['TaxAuthority'])){
	$TaxAuthority = $_POST['TaxAuthority'];
}
if (isset($_GET['TaxAuthority'])){
	$TaxAuthority = $_GET['TaxAuthority'];
}

include('includes/session.inc');
$title = _('Tax Rates');
include('includes/header.inc');

/* <-- $Revision: 1.15 $ --> */

if (!isset($TaxAuthority)){
	prnMsg(_('This page can only be called after selecting the tax authority to edit the rates for') . '. ' . _('Please select the Rates link from the tax authority page') . ".<BR><A HREF='$rootpath/TaxAuthorities.php'>" . _('click here') . '</A> ' . _('to go to the Tax Authority page'),'error');
	include ('includes/footer.inc');
	exit;
}


if (isset($_POST['UpdateRates'])){

	$TaxRatesResult = DB_query('SELECT taxauthrates.taxcatid,
						taxauthrates.taxrate,
						taxauthrates.dispatchtaxprovince
						FROM taxauthrates
						WHERE taxauthrates.taxauthority=' . $TaxAuthority, 
						$db);

	while ($myrow=DB_fetch_array($TaxRatesResult)){

		$sql = 'UPDATE taxauthrates SET taxrate=' . ($_POST[$myrow['dispatchtaxprovince'] . '_' . $myrow['taxcatid']]/100) . '
						WHERE taxcatid = ' . $myrow['taxcatid'] . '
						AND dispatchtaxprovince = ' . $myrow['dispatchtaxprovince'] . '
						AND taxauthority = ' . $TaxAuthority;
		DB_query($sql,$db);
	}
	prnMsg(_('All rates updated successfully'),'info');
}

/* end of update code
*/

/*Display updated rates
*/

$TaxAuthDetail = DB_query('SELECT description FROM taxauthorities WHERE taxid=' . $TaxAuthority,$db);
$myrow = DB_fetch_row($TaxAuthDetail);
echo '<FONT SIZE=3 COLOR=BLUE><B>' . _('Update') . ' ' . $myrow[0] . ' ' . _('Rates') . '</B></FONT>';

echo '<FORM ACTION="' . $_SERVER['PHP_SELF'] . '?' . SID .'" METHOD=POST>';

echo '<INPUT TYPE=HIDDEN NAME="TaxAuthority" VALUE=' . $TaxAuthority . '>';

$TaxRatesResult = DB_query('SELECT taxauthrates.taxcatid,
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
						WHERE taxauthrates.taxauthority=' . $TaxAuthority . "
						ORDER BY taxauthrates.dispatchtaxprovince, 
						taxauthrates.taxcatid",
					$db);

if (DB_num_rows($TaxRatesResult)>0){

	echo '<center><table cellpadding=2 border=2>';
	$TableHeader = '<tr><th>' . _('Deliveries From') . '<br>' . _('Tax Province') . '</th>
						<th>' . _('Tax Category') . '</th>
						<th>' . _('Tax Rate') . ' %</th></tr>';
	echo $TableHeader;
	$j = 1;
	$k = 0; //row counter to determine background colour
	$OldProvince='';
	
	while ($myrow = DB_fetch_array($TaxRatesResult)){
		
		if ($OldProvince!=$myrow['dispatchtaxprovince'] AND $OldProvince!=''){
			echo '<tr bgcolor="#555555"><font size=1> </font><td colspan=3></td></tr>';
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
			<td><input type=text name=%s maxlength=5 size=5 value=%s></td>
			</tr>',
			$myrow['taxprovincename'],
			$myrow['taxcatname'],
			$myrow['dispatchtaxprovince'] . '_' . $myrow['taxcatid'],
			$myrow['taxrate']*100 );
		
		$OldProvince = $myrow['dispatchtaxprovince'];

	}
//end of while loop
echo '</TABLE>';
echo "<BR><INPUT TYPE=SUBMIT NAME='UpdateRates' VALUE='" . _('Update Rates') . "'></CENTER>";
} //end if tax taxcatid/rates to show 
	else {
	prnMsg(_('There are no tax rates to show - perhaps the dispatch tax province records have not yet been created?'),'warn');
}

echo '</FORM>';

echo '<BR><A HREF="' . $rootpath . '/TaxAuthorities.php?' . SID . '">' . _('Tax Authorities') .  '</A>';
echo '<BR><A HREF="' . $rootpath . '/TaxGroups.php?' . SID . '">' . _('Tax Groupings') .  '</A>';
echo '<BR><A HREF="' . $rootpath . '/TaxCategories.php?' . SID . '">' . _('Tax Categories') .  '</A>';
echo '<BR><A HREF="' . $rootpath . '/TaxProvinces.php?' . SID . '">' . _('Dispatch Tax Provinces') .  '</A>';

include( 'includes/footer.inc' );
?>
<?php
/* $Revision: 1.6 $ */
// TaxAuthorityRates.php
//
// Date     Author
// 22-02-04 DRStins@Zion-IT.com / 26-02-04 phil


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

/* <-- $Revision: 1.6 $ --> */

if (!isset($TaxAuthority)){
	prnMsg(_('This page can only be called after selecting the tax authority to edit the rates for') . '. ' . _('Please select the Rates link from the tax authority page') . ".<BR><A HREF='$rootpath/TaxAuthorities.php'>" . _('click here') . '</A> ' . _('to go to the Tax Authority page'),'error');
	include ('includes/footer.inc');
	exit;
}


if (isset($_POST['UpdateRates'])){

	$TaxRatesResult = DB_query('SELECT taxauthlevels.level,
						taxauthlevels.taxrate,
						taxauthlevels.dispatchtaxauthority
					FROM taxauthlevels
					WHERE taxauthlevels.taxauthority=' . $TaxAuthority, $db);

	while ($myrow=DB_fetch_array($TaxRatesResult)){

		$sql = 'UPDATE taxauthlevels SET taxrate=' . ($_POST[$myrow['dispatchtaxauthority'] . '_' . $myrow['level']]/100) . '
			WHERE level = ' . $myrow['level'] . '
			AND dispatchtaxauthority = ' . $myrow['dispatchtaxauthority'] . '
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
echo '<BR><FONT SIZE=3 COLOR=BLUE><B>' . _('Update') . ' ' . $myrow[0] . ' ' . _('Rates') . '</B></FONT><BR>';

echo "<FORM ACTION='" . $_SERVER['PHP_SELF'] . '?' . SID ."' METHOD=POST>";

echo "<INPUT TYPE=HIDDEN NAME='TaxAuthority' VALUE=$TaxAuthority>";

$TaxRatesResult = DB_query('SELECT taxauthlevels.level,
				taxauthlevels.taxrate,
				taxauthlevels.dispatchtaxauthority,
				taxauthorities.description
				FROM taxauthlevels INNER JOIN taxauthorities
					ON taxauthlevels.dispatchtaxauthority=taxauthorities.taxid
				WHERE taxauthlevels.taxauthority=' . $TaxAuthority . "
				ORDER BY taxauthlevels.dispatchtaxauthority, 
					taxauthlevels.level",
				$db);

if (DB_num_rows($TaxRatesResult)>0){

	echo '<CENTER><TABLE CELLPADDING=2 BORDER=2>';
	$TableHeader = "<TR><TD Class='tableheader'>" . _('Deliveres From') . '<BR>' . _('Tax Authority') . "</TD>
				<TD Class='tableheader'>" . _('Tax Level') . "</TD>
				<TD Class='tableheader'>" . _('Tax Rate') . ' %</TD></TR>';
	echo $TableHeader;
	$j = 1;
	$k = 0; //row counter to determine background colour

	while ($myrow = DB_fetch_array($TaxRatesResult)){

		if ($k==1){
			echo "<tr bgcolor='#CCCCCC'>";
			$k=0;
		} else {
			echo "<tr bgcolor='#EEEEEE'>";
			$k=1;
		}

		printf('<td>%s</td>
			<td>%s</td>
			<td><INPUT TYPE=TEXT NAME=%s MAXLENGTH=5 SIZE=5 VALUE=%s></td>
			</tr>',
			$myrow['description'],
			$myrow['level'],
			$myrow['dispatchtaxauthority'] . '_' . $myrow['level'],
			$myrow['taxrate']*100 );

	}
//end of while loop


} //end if tax level/rates to show

echo '</TABLE>';
echo "<BR><INPUT TYPE=SUBMIT NAME='UpdateRates' VALUE='" . _('Update Rates') . "'></CENTER>";
echo '</form>';
echo '<BR><BR>';
prnMsg(_('Tax rates must be specified for all defined tax levels') . '. ' . _('The tax level refers to the specific level of tax attributable to different items') . '. ' . _('These are set up against the item'),'info');

echo '<BR><BR>';
prnMsg(_('For all tax levels, tax rates must be specified for all other defined Tax Authorities ie for goods moving between tax authorities. In most countries selling products between tax authorities attracts 0% tax. It is normally only when sales are delivered from within the tax authority to a customer in the same tax authority that the tax rate will be other than 0%'),'info');

include( 'includes/footer.inc' );
?>
<?php
/* $Revision: 1.3 $ */
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

echo "<!-- \$Revision: 1.3 $ -->";

if (!isset($TaxAuthority)){
	prnMsg(_('This page can only be called after selecting the tax authority to edit the rates for. Please select the Rates link from the tax authority page') . ".<BR><A HREF='$rootpath/TaxAuthorities.php'>" . _('Click Here') . '</A> ' . _('to go to the Tax Authority page'),'error');
	include ("includes/footer.inc");
	exit;
}



if (isset($_POST['UpdateRates'])){

	$TaxRatesResult = DB_query("SELECT TaxAuthLevels.Level,
						TaxAuthLevels.TaxRate,
						TaxAuthLevels.DispatchTaxAuthority
					FROM TaxAuthLevels
					WHERE TaxAuthLevels.TaxAuthority=" . $TaxAuthority, $db);

	while ($myrow=DB_fetch_array($TaxRatesResult)){

		$sql = "UPDATE TaxAuthLevels SET
				TaxRate=" . ($_POST[$myrow['DispatchTaxAuthority'] . "_" . $myrow['Level']]/100) . "
			WHERE Level = " . $myrow['Level'] . "
			AND DispatchTaxAuthority = " . $myrow['DispatchTaxAuthority'] . "
			AND TaxAuthority = " . $TaxAuthority;
		DB_query($sql,$db);

	}
	prnMsg(_('All rates updated successfully'),'info');
}

/* end of update code
*/

/*Display updated rates
*/

$TaxAuthDetail = DB_query('SELECT Description FROM TaxAuthorities WHERE TaxID=' . $TaxAuthority,$db);
$myrow = DB_fetch_row($TaxAuthDetail);
echo '<BR><FONT SIZE=3 COLOR=BLUE><B>' . _('Update') . ' ' . $myrow[0] . ' ' . _('Rates') . '</B></FONT><BR>';

echo "<FORM ACTION='" . $_SERVER['PHP_SELF'] . "?" . SID ."' METHOD=POST>";

echo "<INPUT TYPE=HIDDEN NAME='TaxAuthority' VALUE=$TaxAuthority>";

$TaxRatesResult = DB_query("SELECT TaxAuthLevels.Level,
				TaxAuthLevels.TaxRate,
				TaxAuthLevels.DispatchTaxAuthority,
				TaxAuthorities.Description
				FROM TaxAuthLevels INNER JOIN TaxAuthorities
					ON TaxAuthLevels.DispatchTaxAuthority=TaxAuthorities.TaxID
				WHERE TaxAuthLevels.TaxAuthority=" . $TaxAuthority . "
				ORDER BY TaxAuthLevels.DispatchTaxAuthority, TaxAuthLevels.Level",
				$db);

if (DB_num_rows($TaxRatesResult)>0){

	echo '<CENTER><TABLE CELLPADDING=2 BORDER=2>';
	$TableHeader = "<TR><TD Class='tableheader'>" . _('Deliveres From') . '<BR>' . _('Tax Authority') . "</TD>
				<TD Class='tableheader'>" . _('Tax Level') . "</TD>
				<TD Class='tableheader'>" . _('Tax Rate %') . '</TD></TR>';
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

		printf("<td>%s</td>
			<td>%s</td>
			<td><INPUT TYPE=TEXT NAME=%s MAXLENGTH=5 SIZE=5 VALUE=%s></td>
			</tr>",
			$myrow["Description" ],
			$myrow['Level'],
			$myrow['DispatchTaxAuthority'] . "_" . $myrow['Level'],
			$myrow['TaxRate']*100 );

	}
//end of while loop


} //end if tax level/rates to show

echo '</TABLE>';
echo "<BR><INPUT TYPE=SUBMIT NAME='UpdateRates' VALUE='" . _('Update Rates') . "'></CENTER>";
echo '</form>';
echo '<BR><BR>';
prnMsg(_('Tax rates must be specified for all defined tax levels. The tax level refers to the specific level of tax attributable to different items. These are set up against the item.'),'info');

echo '<BR><BR>';
prnMsg(_('For all tax levels, tax rates must be specified for all other defined Tax Authorities ie for goods moving between tax authorities. In most countries selling products between tax authorities attracts 0% tax. It is normally only when sales are delivered from within the tax authority to a customer in the same tax authority that the tax rate will be other than 0%'),'info');

include( "includes/footer.inc" );
?>

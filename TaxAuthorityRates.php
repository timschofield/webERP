<?php

// TaxAuthorityRates.php
//
// Date     Author
// 22-02-04 DRStins@Zion-IT.com / 26-02-04 phil

$title = "Tax Rates";

$PageSecurity = 11; // only allow accountant access

if (isset($_POST['TaxAuthority'])){
	$TaxAuthority = $_POST['TaxAuthority'];
}
if (isset($_GET['TaxAuthority'])){
	$TaxAuthority = $_GET['TaxAuthority'];
}

include( "includes/session.inc" );
include( "includes/header.inc" );
echo "<!-- \$Revision: 1.1 $ -->";

if (!isset($TaxAuthority)){
	echo "<BR>This page can only be called after selecting the tax authority to edit the rates for. Please select the Rates link from the tax authority page.<BR><A HREF='$rootpath/TaxAuthorities.php'>Click Here</A> to go to the Tax Authority page.";
	include ("includes/footer.inc");
	exit;
}



if ($_POST['UpdateRates']=='Update Rates'){

	$TaxRatesResult = DB_query("SELECT TaxAuthLevels.Level, TaxAuthLevels.TaxRate, TaxAuthLevels.DispatchTaxAuthority FROM TaxAuthLevels WHERE TaxAuthLevels.TaxAuthority=" . $TaxAuthority, $db);

	while ($myrow=DB_fetch_array($TaxRatesResult)){

		$sql = "UPDATE TaxAuthLevels SET TaxRate=" . ($_POST[$myrow['DispatchTaxAuthority'] . "_" . $myrow['Level']]/100) . " WHERE Level = " . $myrow['Level'] . " AND DispatchTaxAuthority = " . $myrow['DispatchTaxAuthority'] . " AND TaxAuthority = " . $TaxAuthority;
	DB_query($sql,$db);

	}
	echo "<BR>All rates updated successfully";
}

/* end of update code
*/

/*Display updated rates
*/

$TaxAuthDetail = DB_query("SELECT Description FROM TaxAuthorities WHERE TaxID=" . $TaxAuthority,$db);
$myrow = DB_fetch_row($TaxAuthDetail);
echo "<BR><FONT SIZE=3 COLOR=BLUE><B>Update " . $myrow[0] . " Rates</B></FONT><BR>";

echo "<FORM ACTION='" . $_SERVER['PHP_SELF'] . "?" . SID ."' METHOD=POST>";

echo "<INPUT TYPE=HIDDEN NAME='TaxAuthority' VALUE=$TaxAuthority>";

$TaxRatesResult = DB_query("SELECT TaxAuthLevels.Level, TaxAuthLevels.TaxRate, TaxAuthLevels.DispatchTaxAuthority, TaxAuthorities.Description FROM TaxAuthLevels INNER JOIN TaxAuthorities ON TaxAuthLevels.DispatchTaxAuthority=TaxAuthorities.TaxID WHERE TaxAuthLevels.TaxAuthority=" . $TaxAuthority . " ORDER BY TaxAuthLevels.DispatchTaxAuthority, TaxAuthLevels.Level",$db);

if (DB_num_rows($TaxRatesResult)>0){

	echo "<CENTER><TABLE CELLPADDING=2 BORDER=2>";
	$TableHeader = "<TR><TD Class='tableheader'>Deliveres From<BR>Tax Authority</TD><TD Class='tableheader'>Tax Level</TD><TD Class='tableheader'>Tax Rate %</TD></TR>";
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

		printf("<td>%s</td><td>%s</td><td><INPUT TYPE=TEXT NAME=%s MAXLENGTH=5 SIZE=5 VALUE=%s></td></tr>",$myrow["Description" ],$myrow['Level'],$myrow['DispatchTaxAuthority'] . "_" . $myrow['Level'], $myrow['TaxRate']*100 );

	}
//end of while loop


} //end if tax level/rates to show

echo "</TABLE>";
echo "<BR><INPUT TYPE=SUBMIT NAME='UpdateRates' VALUE='Update Rates'></CENTER>";
echo "</form>";
echo "<BR><BR>Tax rates must be specified for all defined tax levels. The tax level refers to the specific level of tax attributable to different items. These are set up against the item.";

echo "<BR><BR>For all tax levels, tax rates must be specified for all other defined Tax Authorities ie for goods moving between tax authorities. In most countries selling products between tax authorities attracts 0% tax. It is normally only when sales are delivered from within the tax authority to a customer in the same tax authority that the tax rate will be other than 0%";

include( "includes/footer.inc" );
?>

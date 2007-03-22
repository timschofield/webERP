<?php
/* $Revision: 1.10 $ */

$PageSecurity = 11;
include('includes/session.inc');
$title = _('Freight Costs Set Up');
include('includes/header.inc');

?>

<?php

if (isset($_GET['LocationFrom'])){
	$LocationFrom = $_GET['LocationFrom'];
} elseif (isset($_POST['LocationFrom'])){
	$LocationFrom = $_POST['LocationFrom'];
}
if (isset($_GET['ShipperID'])){
	$ShipperID = $_GET['ShipperID'];
} elseif (isset($_POST['ShipperID'])){
	$ShipperID = $_POST['ShipperID'];
}
if (isset($_GET['SelectedFreightCost'])){
	$SelectedFreightCost = $_GET['SelectedFreightCost'];
} elseif (isset($_POST['SelectedFreightCost'])){
	$SelectedFreightCost = $_POST['SelectedFreightCost'];
}



if (!isset($LocationFrom) OR !isset($ShipperID)) {

	echo "<FORM METHOD='post' action='" . $_SERVER['PHP_SELF'] . '?' . SID . "'>";
	$sql = 'SELECT shippername, shipper_id FROM shippers';
	$ShipperResults = DB_query($sql,$db);

	echo '<CENTER><TABLE BORDER=1>
		<TR>
			<TD>' . _('Select A Freight Company to set up costs for') . "</TD>
			<TD><SELECT name='ShipperID'>";

	while ($myrow = DB_fetch_array($ShipperResults)){
		echo '<OPTION VALUE=' . $myrow['shipper_id'] . '>' . $myrow['shippername'];
	}
	echo '</SELECT></TD></TR>
			<TR>
				<TD>' . _('Select the warehouse') . ' (' . _('ship from location') . ")</TD>
				<TD><SELECT name='LocationFrom'>";

	$sql = 'SELECT loccode, locationname FROM locations';
	$LocationResults = DB_query($sql,$db);

	while ($myrow = DB_fetch_array($LocationResults)){
		echo '<OPTION VALUE=' . $myrow['loccode'] . '>' . $myrow['locationname'];
	}

	echo "</SELECT></TD></TR></TABLE><INPUT TYPE=SUBMIT VALUE='" . _('Accept') . "' NAME='Accept'></FORM>";

} else {

	$sql = "SELECT shippername FROM shippers WHERE shipper_id = $ShipperID";
	$ShipperResults = DB_query($sql,$db);
	$myrow = DB_fetch_row($ShipperResults);
	$ShipperName = $myrow[0];
	$sql = "SELECT locationname FROM locations WHERE loccode = '$LocationFrom'";
	$LocationResults = DB_query($sql,$db);
	$myrow = DB_fetch_row($LocationResults);
	$LocationName = $myrow[0];
	echo '<FONT SIZE=4 COLOR=BLUE>' . _('For Deliveries From') . ' ' . $LocationName . ' ' . _('using') . ' ' . $ShipperName . '</FONT><BR>';

}


if (isset($_POST['submit'])) {

	//initialise no input errors assumed initially before we test
	$InputError = 0;

	//first off validate inputs sensible
	if (strlen($_POST['Destination'])<2){
		$InputError=1;
		prnMsg(_('The entry for the destination must be at least two characters long') . '. ' . _('These entries are matched against the town names entered for customer delivery addresses'),'warn');
	}


	if (trim($_POST['CubRate']) == '' ) {
		$_POST['CubRate'] = 0;
	}
	if (trim($_POST['KGRate']) == '' ) {
		$_POST['KGRate'] = 0;
	}
	if (trim($_POST['MAXKGs']) == '' ) {
		$_POST['MAXKGs'] = 0;
	}
	if (trim($_POST['MAXCub']) == '' ) {
		$_POST['MAXCub'] = 0;
	}
	if (trim($_POST['FixedPrice']) == '' ){
		$_POST['FixedPrice'] = 0;
	}
	if (trim($_POST['MinimumChg']) == '' ) {
		$_POST['MinimumChg'] = 0;
	}

	if (!is_double((double) $_POST['CubRate']) OR !is_double((double) $_POST['KGRate']) OR !is_double((double) $_POST['MAXKGs']) OR !is_double((double) $_POST['MAXCub']) OR !is_double((double) $_POST['FixedPrice']) OR !is_double((double) $_POST['MinimumChg'])) {
		$InputError=1;
		prnMsg(_('The entries for Cubic Rate, KG Rate, Maxmimum Weight, Maximum Volume, Fixed Price and Minimum charge must be numeric'),'warn');
	}

	
 
	if (isset($SelectedFreightCost) AND $InputError !=1) {

		$sql = "UPDATE freightcosts
				SET
					locationfrom='$LocationFrom',
					destination='" . $_POST['Destination'] . "',
					shipperid=$ShipperID,
					cubrate=" . $_POST['CubRate'] . ",
					kgrate = " . $_POST['KGRate'] . ",
					maxkgs = " . $_POST['MAXKGs'] . ",
					maxcub= " . $_POST['MAXCub'] . ",
					fixedprice = " . $_POST['FixedPrice'] . ",
					minimumchg= " . $_POST['MinimumChg'] . "
			WHERE shipcostfromid=" . $SelectedFreightCost;

		$msg = _('Freight cost record updated');

	} elseif ($InputError !=1) {

	/*Selected freight cost is null cos no item selected on first time round so must be adding a record must be submitting new entries */

		$sql = "INSERT INTO freightcosts (
				locationfrom,
				destination,
				shipperid,
				cubrate,
				kgrate,
				maxkgs,
				maxcub,
				fixedprice,
				minimumchg)
			VALUES (
				'$LocationFrom',
				'" . $_POST['Destination'] . "',
				$ShipperID,
				" . $_POST['CubRate'] . ",
				" . $_POST['KGRate'] . ",
				" . $_POST['MAXKGs'] . ",
				" . $_POST['MAXCub'] . ",
				" . $_POST['FixedPrice'] .",
				" . $_POST['MinimumChg'] . "
			)";

		$msg = _('Freight cost record inserted');

	}
	//run the SQL from either of the above possibilites


	$ErrMsg = _('The freight cost record could not be updated because');
	$result = DB_query($sql,$db,$ErrMsg);

	prnMsg($msg,'success');
	
	unset($SelectedFreightCost);
	unset($_POST['CubRate']);
	unset($_POST['KGRate']);
	unset($_POST['MAXKGs']);
	unset($_POST['MAXCub']);
	unset($_POST['FixedPrice']);
	unset($_POST['MinimumChg']);

} elseif (isset($_GET['delete'])) {

	$sql = 'DELETE FROM freightcosts WHERE shipcostfromid=' . $SelectedFreightCost;
	$result = DB_query($sql,$db);
	prnMsg( _('Freight cost record deleted'),'success');
	unset ($SelectedFreightCost);
	unset($_GET['delete']);
}

if (!isset($SelectedFreightCost) AND isset($LocationFrom) AND isset($ShipperID)){


	$sql = "SELECT shipcostfromid,
			destination,
			cubrate,
			kgrate,
			maxkgs,
			maxcub,
			fixedprice,
			minimumchg
		FROM freightcosts
		WHERE freightcosts.locationfrom = '$LocationFrom'
		AND freightcosts.shipperid = $ShipperID
		ORDER BY destination";

	$result = DB_query($sql,$db);
	
	echo '<table border=1>';
	$TableHeader = "<tr>
				<td class='tableheader'>" . _('Destination') . "</td>
				<td class='tableheader'>" . _('Cubic Rate') . "</td>
				<td class='tableheader'>" . _('KG Rate') . "</td>
				<td class='tableheader'>" . _('MAX KGs') . "</td>
				<td class='tableheader'>" . _('MAX Volume') . "</td>
				<td class='tableheader'>" . _('Fixed Price') . "</td>
				<td class='tableheader'>" . _('Minimum Charge') . "</td>
			</tr>";

	echo $TableHeader;

	$k = 0; //row counter to determine background colour
	$PageFullCounter=0;

	while ($myrow = DB_fetch_row($result)) {
		$PageFullCounter++;
		if ($PageFullCounter==15){
				$PageFullCounter=0;
				echo $TableHeader;

		}
		if ($k==1){
			echo "<tr bgcolor='#CCCCCC'>";
			$k=0;
		} else {
			echo "<tr bgcolor='#EEEEEE'>";
			$k++;
		}

		
		printf("<td>%s</td>
			<td ALIGN=RIGHT>%s</td>
			<td ALIGN=RIGHT>%s</td>
			<td ALIGN=RIGHT>%s</td>
			<td ALIGN=RIGHT>%s</td>
			<td ALIGN=RIGHT>%s</td>
			<td ALIGN=RIGHT>%s</td>
			<td><a href=\"%s&SelectedFreightCost=%s&LocationFrom=%s&ShipperID=%s\">" . _('Edit') . "</td>
			<td><a href=\"%s&SelectedFreightCost=%s&LocationFrom=%s&ShipperID=%s&delete=yes\">" . _('Delete') . "</td></tr>",
			$myrow[1],
			$myrow[2],
			$myrow[3],
			$myrow[4],
			$myrow[5],
			$myrow[6],
			$myrow[7],
			$_SERVER['PHP_SELF'] . '?' . SID,
			$myrow[0],
			$LocationFrom,
			$ShipperID,
			$_SERVER['PHP_SELF'] . '?' . SID,
			$myrow[0],
			$LocationFrom,
			$ShipperID);

	}

	//END WHILE LIST LOOP
	echo '</table>';
}

//end of ifs and buts!

if (isset($SelectedFreightCost)) {
	echo "<Center><a href='" . $_SERVER['PHP_SELF'] . "?" . SID . '&LocationFrom=' . $LocationFrom . '&ShipperID=' . $ShipperID . "'>" . _('Show all freight costs for') . ' ' . $ShipperName  . ' ' . _('from') . ' ' . $LocationName . '</a></Center>';
}

if (isset($LocationFrom) AND isset($ShipperID)) {

	echo "<FORM METHOD='post' action='" . $_SERVER['PHP_SELF'] . '?' . SID . "'>";

	if ($SelectedFreightCost) {
		//editing an existing freight cost item

		$sql = "SELECT locationfrom,
				destination,
				shipperid,
				cubrate,
				kgrate,
				maxkgs,
				maxcub,
				fixedprice,
				minimumchg
			FROM freightcosts
			WHERE shipcostfromid=$SelectedFreightCost";

		$result = DB_query($sql, $db);
		$myrow = DB_fetch_array($result);

		$LocationFrom  = $myrow['locationfrom'];
		$_POST['Destination']	= $myrow['destination'];
		$ShipperID  = $myrow['shipperid'];
		$_POST['CubRate']  = $myrow['cubrate'];
		$_POST['KGRate'] = $myrow['kgrate'];
		$_POST['MAXKGs'] = $myrow['maxkgs'];
		$_POST['MAXCub'] = $myrow['maxcub'];
		$_POST['FixedPrice'] = $myrow['fixedprice'];
		$_POST['MinimumChg'] = $myrow['minimumchg'];
		
		echo "<INPUT TYPE=HIDDEN NAME='SelectedFreightCost' VALUE=$SelectedFreightCost>";

	} else {
		$_POST['FixedPrice'] = 0;
		$_POST['MinimumChg'] = 0;

	}
	echo "<input type=HIDDEN name='LocationFrom' value='$LocationFrom'>";
	echo "<input type=HIDDEN name='ShipperID' value=$ShipperID>";


	echo '<TABLE>
		<TR><TD>' . _('Destination') . ":</TD>
		<TD><input type='text' maxlength=20 size=20 name='Destination' VALUE='" . $_POST['Destination'] . "'></TD></TR>";
	echo '<TR><TD>' . _('Rate per Cubic Metre') . ":</TD>
		<TD><input type='Text' name='CubRate' SIZE=6 MAXLENGTH=5 value=" . $_POST['CubRate'] . "></TD></TR>";
	echo '<TR><TD>' . _('Rate Per KG') . ":</TD>
		<TD><input type='Text' name='KGRate' SIZE=6 MAXLENGTH=5 value=" . $_POST['KGRate'] . "></TD></TR>";
	echo '<TR><TD>' . _('Maximum Weight Per Package (KGs)') . ":</a></TD>
		<TD><input type='Text' name='MAXKGs' SIZE=8 MAXLENGTH=7 value=" . $_POST['MAXKGs'] . "></TD></TR>";
	echo '<TR><TD>' . _('Maximum Volume Per Package (cubic metres)') . ":</a></TD>
		<TD><input type='Text' name='MAXCub' SIZE=8 MAXLENGTH=7 value=" . $_POST['MAXCub'] . "></TD></TR>";
	echo '<TR><TD>' . _('Fixed Price (zero if rate per KG or Cubic)') . ":</a></TD>
		<TD><input type='Text' name='FixedPrice' SIZE=6 MAXLENGTH=5 value=" . $_POST['FixedPrice'] . "></TD></TR>";
	echo '<TR><TD>' . _('Minimum Charge (0 is N/A)') . ":</a></TD>
		<TD><input type='Text' name='MinimumChg' SIZE=6 MAXLENGTH=5 value=" . $_POST['MinimumChg'] . "></TD></TR>";

	echo '</TABLE>';

	echo "<CENTER><input type='Submit' name='submit' value='" . _('Enter Information') . "'>";

	echo '</FORM>';

} //end if record deleted no point displaying form to add record

include('includes/footer.inc');
?>
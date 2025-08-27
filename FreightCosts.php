<?php

require(__DIR__ . '/includes/session.php');

$Title = __('Freight Costs Maintenance');
$ViewTopic = 'Setup';
$BookMark = 'FreightCosts';
include('includes/header.php');

include('includes/CountriesArray.php');

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
	echo '<div class="centre"><p class="page_title_text"><img src="'.$RootPath.'/css/'.$Theme.'/images/supplier.png" title="' .
		__('Freight Costs') . '" alt="" />' . ' ' . $Title . '</p></div>';

	echo '<form method="post" action="' . htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') . '">';
	echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';
	$SQL = "SELECT shippername, shipper_id FROM shippers ORDER BY shippername";
	$ShipperResults = DB_query($SQL);

	echo '<fieldset>
			<legend>', __('Select Details'), '</legend>';

	echo '<field>
			<label for="ShipperID">' . __('Select A Freight Company to set up costs for') . '</label>
			<select name="ShipperID">';

	while ($MyRow = DB_fetch_array($ShipperResults)){
		echo '<option value="' . $MyRow['shipper_id'] . '">' . $MyRow['shippername'] . '</option>';
	}
	echo '</select>
		</field>';

	echo '<field>
			<label for="LocationFrom">' . __('Select the warehouse') . ' (' . __('ship from location') . ')</label>
			<select name="LocationFrom">';

	$SQL = "SELECT locations.loccode,
					locationname
			FROM locations INNER JOIN locationusers ON locationusers.loccode=locations.loccode AND locationusers.userid='" .  $_SESSION['UserID'] . "' AND locationusers.canupd=1";
	$LocationResults = DB_query($SQL);

	while ($MyRow = DB_fetch_array($LocationResults)){
		echo '<option value="' . $MyRow['loccode'] . '">' . $MyRow['locationname'] . '</option>';
	}

	echo '</select>
		</field>
	</fieldset>';

	echo '<div class="centre">
			<input type="submit" value="' . __('Accept') . '" name="Accept" />
		</div>
	</form>';

} else {

	$SQL = "SELECT shippername FROM shippers WHERE shipper_id = '".$ShipperID."'";
	$ShipperResults = DB_query($SQL);
	$MyRow = DB_fetch_row($ShipperResults);
	$ShipperName = $MyRow[0];
	$SQL = "SELECT locationname FROM locations WHERE loccode = '".$LocationFrom."'";
	$LocationResults = DB_query($SQL);
	$MyRow = DB_fetch_row($LocationResults);
	$LocationName = $MyRow[0];

	if (isset($ShipperID)){
		$Title .= ' ' . __('For') . ' ' . $ShipperName;
	}
	if (isset($LocationFrom)){
		$Title .= ' ' . __('From') . ' ' . $LocationName;
	}

	echo '<div class="centre"><p class="page_title_text"><img src="'.$RootPath.'/css/'.$Theme.'/images/supplier.png" title="' .
		__('Freight Costs') . '" alt="" />' . ' ' . $Title . '</p></div>';

}

if (isset($_POST['submit'])) {

	//initialise no input errors assumed initially before we test
	$InputError = 0;

	//first off validate inputs sensible

	if (trim($_POST['DestinationCountry']) == '' ) {
		$_POST['DestinationCountry'] = $CountriesArray[$_SESSION['CountryOfOperation']];
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
		prnMsg(__('The entries for Cubic Rate, KG Rate, Maximum Weight, Maximum Volume, Fixed Price and Minimum charge must be numeric'),'warn');
	}

	if (isset($SelectedFreightCost) AND $InputError !=1) {

		$SQL = "UPDATE freightcosts
				SET	locationfrom='".$LocationFrom."',
					destinationcountry='" . $_POST['DestinationCountry'] . "',
					destination='" . $_POST['Destination'] . "',
					shipperid='" . $ShipperID . "',
					cubrate='" . $_POST['CubRate'] . "',
					kgrate ='" . $_POST['KGRate'] . "',
					maxkgs ='" . $_POST['MAXKGs'] . "',
					maxcub= '" . $_POST['MAXCub'] . "',
					fixedprice = '" . $_POST['FixedPrice'] . "',
					minimumchg= '" . $_POST['MinimumChg'] . "'
			WHERE shipcostfromid='" . $SelectedFreightCost . "'";

		$Msg = __('Freight cost record updated');

	} elseif ($InputError !=1) {

	/*Selected freight cost is null cos no item selected on first time round so must be adding a record must be submitting new entries */

		$SQL = "INSERT INTO freightcosts (locationfrom,
											destinationcountry,
											destination,
											shipperid,
											cubrate,
											kgrate,
											maxkgs,
											maxcub,
											fixedprice,
											minimumchg)
										VALUES (
											'".$LocationFrom."',
											'" . $_POST['DestinationCountry'] . "',
											'" . $_POST['Destination'] . "',
											'" . $ShipperID . "',
											'" . $_POST['CubRate'] . "',
											'" . $_POST['KGRate'] . "',
											'" . $_POST['MAXKGs'] . "',
											'" . $_POST['MAXCub'] . "',
											'" . $_POST['FixedPrice'] ."',
											'" . $_POST['MinimumChg'] . "'
										)";

		$Msg = __('Freight cost record inserted');

	}
	//run the SQL from either of the above possibilites

	$ErrMsg = __('The freight cost record could not be updated because');
	$Result = DB_query($SQL, $ErrMsg);

	prnMsg($Msg,'success');

	unset($SelectedFreightCost);
	unset($_POST['CubRate']);
	unset($_POST['KGRate']);
	unset($_POST['MAXKGs']);
	unset($_POST['MAXCub']);
	unset($_POST['FixedPrice']);
	unset($_POST['MinimumChg']);

} elseif (isset($_GET['delete'])) {

	$SQL = "DELETE FROM freightcosts WHERE shipcostfromid='" . $SelectedFreightCost . "'";
	$Result = DB_query($SQL);
	prnMsg( __('Freight cost record deleted'),'success');
	unset ($SelectedFreightCost);
	unset($_GET['delete']);
}

if (!isset($SelectedFreightCost) AND isset($LocationFrom) AND isset($ShipperID)){

	$SQL = "SELECT shipcostfromid,
					destinationcountry,
					destination,
					cubrate,
					kgrate,
					maxkgs,
					maxcub,
					fixedprice,
					minimumchg
				FROM freightcosts
				WHERE freightcosts.locationfrom = '".$LocationFrom. "'
				AND freightcosts.shipperid = '" . $ShipperID . "'
				ORDER BY destinationcountry,
						destination,
						maxkgs,
						maxcub";

	$Result = DB_query($SQL);

	echo '<table class="selection">';
	$TableHeader = '<tr>
					<th>' . __('Country') . '</th>
					<th>' . __('Destination') . '</th>
					<th>' . __('Cubic Rate') . '</th>
					<th>' . __('KG Rate') . '</th>
					<th>' . __('MAX KGs') . '</th>
					<th>' . __('MAX Volume') . '</th>
					<th>' . __('Fixed Price') . '</th>
					<th>' . __('Minimum Charge') . '</th>
					<th colspan="2"></th>
					</tr>';

	echo $TableHeader;

	$PageFullCounter=0;

	while ($MyRow = DB_fetch_row($Result)) {
		$PageFullCounter++;
		if ($PageFullCounter==15){
				$PageFullCounter=0;
				echo $TableHeader;

		}

		echo '<tr class="striped_row">
				<td>', $MyRow[1], '</td>
				<td>', $MyRow[2], '</td>
				<td class="number">', locale_number_format($MyRow[3],$_SESSION['CompanyRecord']['decimalplaces']), '</td>
				<td class="number">', locale_number_format($MyRow[4],$_SESSION['CompanyRecord']['decimalplaces']), '</td>
				<td class="number">', locale_number_format($MyRow[5],2), '</td>
				<td class="number">', locale_number_format($MyRow[6],3), '</td>
				<td class="number">', locale_number_format($MyRow[7],$_SESSION['CompanyRecord']['decimalplaces']), '</td>
				<td class="number">', locale_number_format($MyRow[8],$_SESSION['CompanyRecord']['decimalplaces']), '</td>
				<td><a href="', htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') . '?&amp;SelectedFreightCost=', $MyRow[0], '&amp;LocationFrom=', $LocationFrom, '&amp;ShipperID=', $ShipperID, '">' . __('Edit') . '</a></td>
				<td><a href="', htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') . '?&amp;SelectedFreightCost=', $MyRow[0], '&amp;LocationFrom=', $LocationFrom, '&amp;ShipperID=', $ShipperID, '&amp;delete=yes" onclick="return confirm(\'' . __('Are you sure you wish to delete this freight cost') . '\');">' . __('Delete') . '</a></td>
			</tr>';

	}

	//END WHILE LIST LOOP
	echo '</table>';
}

//end of ifs and buts!

if (isset($SelectedFreightCost)) {
	echo '<div class="centre"><a href="' . htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') . '?LocationFrom=' . $LocationFrom . '&amp;ShipperID=' . $ShipperID . '">' . __('Show all freight costs for') . ' ' . $ShipperName  . ' ' . __('from') . ' ' . $LocationName . '</a></div>';
}

if (isset($LocationFrom) AND isset($ShipperID)) {

	echo '<form method="post" action="' . htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') . '">';
	echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';

	if (isset($SelectedFreightCost)) {
		//editing an existing freight cost item

		$SQL = "SELECT locationfrom,
					destinationcountry,
					destination,
					shipperid,
					cubrate,
					kgrate,
					maxkgs,
					maxcub,
					fixedprice,
					minimumchg
				FROM freightcosts
				WHERE shipcostfromid='" . $SelectedFreightCost ."'";

		$Result = DB_query($SQL);
		$MyRow = DB_fetch_array($Result);

		$LocationFrom  = $MyRow['locationfrom'];
		$_POST['DestinationCountry']	= $MyRow['destinationcountry'];
		$_POST['Destination']	= $MyRow['destination'];
		$ShipperID  = $MyRow['shipperid'];
		$_POST['CubRate']  = $MyRow['cubrate'];
		$_POST['KGRate'] = $MyRow['kgrate'];
		$_POST['MAXKGs'] = $MyRow['maxkgs'];
		$_POST['MAXCub'] = $MyRow['maxcub'];
		$_POST['FixedPrice'] = $MyRow['fixedprice'];
		$_POST['MinimumChg'] = $MyRow['minimumchg'];

		echo '<input type="hidden" name="SelectedFreightCost" value="' . $SelectedFreightCost . '" />';

	} else {
		$_POST['FixedPrice'] = 0;
		$_POST['MinimumChg'] = 0;
	}

	echo '<input type="hidden" name="LocationFrom" value="' . $LocationFrom . '" />';
	echo '<input type="hidden" name="ShipperID" value="' . $ShipperID . '" />';

	if (!isset($_POST['DestinationCountry'])) {$_POST['DestinationCountry']=$CountriesArray[$_SESSION['CountryOfOperation']];}
	if (!isset($_POST['Destination'])) {$_POST['Destination']='';}
	if (!isset($_POST['CubRate'])) {$_POST['CubRate']='';}
	if (!isset($_POST['KGRate'])) {$_POST['KGRate']='';}
	if (!isset($_POST['MAXKGs'])) {$_POST['MAXKGs']='';}
	if (!isset($_POST['MAXCub'])) {$_POST['MAXCub']='';}

	echo '<fieldset>';
	echo '<legend>' . __('For Deliveries From') . ' ' . $LocationName . ' ' . __('using') . ' ' .
		$ShipperName . '</legend>';

	echo '<field>
			<label for="DestinationCountry">' . __('Destination Country') . ':</label>
			<select name="DestinationCountry">';
	foreach ($CountriesArray as $CountryEntry => $CountryName){
		if (isset($_POST['DestinationCountry']) AND (strtoupper($_POST['DestinationCountry']) == strtoupper($CountryName))){
			echo '<option selected="selected" value="' . $CountryName . '">' . $CountryName  . '</option>';
		} else {
			echo '<option value="' . $CountryName . '">' . $CountryName  . '</option>';
		}
	}
	echo '</select>
		</field>';

	echo'<field>
			<label for="Destination">' . __('Destination Zone') . ':</label>
			<input type="text" maxlength="20" size="20" name="Destination" value="' . $_POST['Destination'] . '" />
		</field>';
	echo '<field>
			<label for="CubRate">' . __('Rate per Cubic Metre') . ':</label>
			<input type="text" name="CubRate" class="number" size="6" maxlength="5" value="' . $_POST['CubRate'] . '" />
		</field>';
	echo '<field>
			<label for="KGRate">' . __('Rate Per KG') . ':</label>
			<input type="text" name="KGRate" class="number" size="6" maxlength="5" value="' . $_POST['KGRate'] . '" />
		</field>';
	echo '<field>
			<label for="MAXKGs">' . __('Maximum Weight Per Package (KGs)') . ':</label>
			<input type="text" name="MAXKGs" class="number" size="8" maxlength="7" value="' . $_POST['MAXKGs'] . '" />
		</field>';
	echo '<field>
			<label for="MAXCub">' . __('Maximum Volume Per Package (cubic metres)') . ':</label>
			<input type="text" name="MAXCub" class="number" size="8" maxlength="7" value="' . $_POST['MAXCub'] . '" />
		</field>';
	echo '<field>
			<label for="FixedPrice">' . __('Fixed Price (zero if rate per KG or Cubic)') . ':</label>
			<input type="text" name="FixedPrice" class="number" size="11" maxlength="10" value="' . $_POST['FixedPrice'] . '" />
		</field>';
	echo '<field>
			<label for="MinimumChg">' . __('Minimum Charge (0 is N/A)') . ':</label>
			<input type="text" name="MinimumChg" class="number" size="11" maxlength="10" value="' . $_POST['MinimumChg'] . '" />
		</field>';

	echo '</fieldset>';

	echo '<div class="centre"><input type="submit" name="submit" value="' . __('Enter Information') . '" /></div>';
	echo '</form>';

} //end if record deleted no point displaying form to add record

include('includes/footer.php');

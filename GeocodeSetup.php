<?php

$PageSecurity = 3;
include('includes/session.inc');
$title = _('Geocode Maintenance');
include('includes/header.inc');

if (isset($_GET['SelectedParam'])){
	$SelectedParam = $_GET['SelectedParam'];
} elseif(isset($_POST['SelectedParam'])){
	$SelectedParam = $_POST['SelectedParam'];
}

if (isset($Errors)) {
	unset($Errors);
}
$Errors = array();
$InputError = 0;

if (isset($_POST['submit'])) {

	//initialise no input errors assumed initially before we test
	$i=1;

	/* actions to take once the user has clicked the submit button
	ie the page has called itself with some user input */

	//first off validate inputs are sensible

	$sql="SELECT count(geocodeid) 
			FROM geocode_param WHERE geocodeid='".$_POST['geocodeid']."'";
	$result=DB_query($sql, $db);
	$myrow=DB_fetch_row($result);

	if ($myrow[0]!=0 and !isset($SelectedParam)) {
		$InputError = 1;
		prnMsg( _('That geocode ID already exists in the database'),'error');
		$Errors[$i] = 'geocodeid';
		$i++;		
	}

	$msg='';

	if (isset($SelectedParam) AND $InputError !=1) {

		/*SelectedParam could also exist if submit had not been clicked this code would not run in this case cos submit is false of course see the delete code below*/

		if (isset($_POST['geocode_key']) and $_POST['geocode_key'] > 0){
			$sql = "UPDATE geocode_param SET 
					geocode_key='" . $_POST['geocode_key'] . "', 
					WHERE geocodeid = $SelectParam";
		} else {
			$sql = "UPDATE geocode_param SET 
					geocode_key='" . $_POST['geocode_key'] . "',
					center_long='" . $_POST['center_long'] . "',
					center_lat='" . $_POST['center_lat'] . "',
					map_height='" . $_POST['map_height'] . "',
					map_width='" . $_POST['map_width'] . "'
					WHERE geocodeid = $SelectedParam";
		}
		$msg = _('The geocode status record has been updated');

	} else if ($InputError !=1) {

	/*Selected Param is null cos no item selected on first time round so must be adding a record must be submitting new entries in the new status code form */

		if (isset($_POST['geocode_key']) and $_POST['geocode_key']>0){

			$sql = 'INSERT INTO geocode_param (
					geocodeid, 
					geocode_key, 
					center_long,
					center_lat,
					map_height,
					map_width) 
					VALUES (' . 
					$_POST['geocode_key'] . ", '" . 
					$_POST['center_long'] . "', '" . 
					$_POST['center_lat'] . "', '" . 
					$_POST['map_height'] . "', '" . 
					$_POST['map_width'] . "', 1)";
		} else {
			$sql = 'INSERT INTO geocode_param (
					geocodeid, 
					geocode_key, 
					center_long,
					center_lat,
					map_height,
					map_width) 
					VALUES (' . 
					$_POST['geocodeid'] . ", '" . 
					$_POST['geocode_key'] . "', '" . 
					$_POST['center_long'] . "', '" . 
					$_POST['center_lat'] . "', '" . 
					$_POST['map_height'] . "', '" . 
					$_POST['map_width'] . "')";
		}

		$msg = _('A new geocode status record has been inserted');
		unset ($SelectedParam);
		unset ($_POST['geocode_key']);
	}
	//run the SQL from either of the above possibilites
	$result = DB_query($sql,$db);
	if ($msg != '') {
		prnMsg($msg,'success');
	}
} elseif (isset($_GET['delete'])) {
//the link to delete a selected record was clicked instead of the submit button
$sql = 'DELETE FROM geocode_param WHERE geocodeid = ' . $_GET['delete'] . ' LIMIT 1';
$result = DB_query($sql,$db);
$msg = _('Geocode deleted');
	//end if status code used in customer or supplier accounts
	unset ($_GET['delete']);
	unset ($SelectedParam);

}

if (!isset($SelectedParam)) {

/* It could still be the second time the page has been run and a record has been selected for modification - SelectedParam will exist because it was sent with the new call. If its the first time the page has been displayed with no parameters
then none of the above are true and the list of status codes will be displayed with
links to delete or edit each. These will call the same page again and allow update/input
or deletion of the records*/

	$sql = 'SELECT geocodeid, geocode_key, center_long, center_lat, map_height, map_width FROM geocode_param';
	$result = DB_query($sql, $db);

	echo '<center><b><p>Setup configuration for Geocoding of Customers and Suppliers</b>';
	echo '<center><p>Get a google API key at http://code.google.com/apis/maps/signup.html</b>';
	echo '<center><p>Find the lat/long for your map center point at http://www.batchgeocode.com/lookup/</b>';
	echo '<center><p>Set the maps centre point using the Center Longitude and Center Latitude.  Set the maps screen size using the height and width in pixels (px).</b>';
	echo '<CENTER><table border=1>';
	echo "<tr>
		<th>". _('Geocode ID') ."</th>
		<th>". _('Geocode Key') ."</th>
		<th>". _('Center Longitude') ."</th>
		<th>". _('Center Latitude') ."</th>
		<th>". _('Map height (px)') ."</th>
		<th>". _('Map width (px)') .'</th>';

	$k=0; //row colour counter
	while ($myrow=DB_fetch_row($result)) {

		if ($k==1){
			echo '<tr class="EvenTableRows">';
			$k=0;
		} else {
			echo '<tr class="OddTableRows">';
			$k=1;
		}

	printf("<td>%s</td>
		<td>%s</td>
		<td>%s</td>
		<td>%s</td>
		<td>%s</td>
		<td>%s</td>
		<td><a href=\"%s?SelectedParam=%s\">" . _('Edit') . "</a></td>
		<td><a href=\"%s?SelectedParam=%s&delete=%s\">". _('Delete') .'</a></td>
		</tr>',
		$myrow[0],
		$myrow[1],
		$myrow[2],
		$myrow[3],
		$myrow[4],
		$myrow[5],
		$_SERVER['PHP_SELF'],
		$myrow[0],
		$_SERVER['PHP_SELF'],
		$myrow[0],
		$myrow[0]);

	} //END WHILE LIST LOOP
	echo '</table></CENTER>';

} //end of ifs and buts!

if (isset($SelectedParam)) {
	echo '<Center><a href="' . $_SERVER['PHP_SELF'] . '">' . _('Show Defined Geocode Param Codes') . '</a></Center>';
}

if (!isset($_GET['delete'])) {

	echo "<FORM METHOD='post' action=" . $_SERVER['PHP_SELF'] . '>';

	if (isset($SelectedParam) and ($InputError!=1)) {
		//editing an existing status code

		$sql = "SELECT geocodeid,
				geocode_key,
				center_long,
				center_lat,
				map_height,
				map_width
			FROM geocode_param
			WHERE geocodeid='$SelectedParam'";

		$result = DB_query($sql, $db);
		$myrow = DB_fetch_array($result);

		$_POST['geocodeid'] = $myrow['geocodeid'];
		$_POST['geocode_key']  = $myrow['geocode_key'];
		$_POST['center_long']  = $myrow['center_long'];
		$_POST['center_lat']  = $myrow['center_lat'];
		$_POST['map_height']  = $myrow['map_height'];
		$_POST['map_width']  = $myrow['map_width'];

		echo "<INPUT TYPE=HIDDEN NAME='SelectedParam' VALUE='" . $SelectedParam . "'>";
		echo "<INPUT TYPE=HIDDEN NAME='geocodeid' VALUE='" . $_POST['geocodeid'] . "'>";
		echo "<CENTER>Setup configuration for Geocoding of Suppliers and Customers";
		echo "<CENTER><TABLE><TR><TD>". _('Geocode Code') .':</TD><TD>';
		echo $_POST['geocodeid'] . '</TD></TR>';

	} else { //end of if $SelectedParam only do the else when a new record is being entered
		if (!isset($_POST['geocodeid'])) {
			$_POST['geocodeid'] = '';
		}
		echo '<CENTER><TABLE>
			<TR>
				<TD>'. _('Geocode Code') .":</TD>
				<TD><input " . (in_array('geocodeid',$Errors) ? 'class="inputerror"' : '' ) .
					" tabindex=1 type='Text' name='geocodeid' VALUE='". $_POST['geocodeid'] ."' SIZE=3 MAXLENGTH=2></TD>
			</TR>";
	}

	if (!isset($_POST['geocode_key'])) {
		$_POST['geocode_key'] = '';
	}
	echo '<TR>
		<TD>'. _('Geocode Key') .":</TD>
		<TD><INPUT " . (in_array('geocode_key',$Errors) ? 'class="inputerror"' : '' ) .
		 " tabindex=2 TYPE='text' name='geocode_key' VALUE='". $_POST['geocode_key'] ."' SIZE=28 MAXLENGTH=300>
	</TD></TR>
	<TR><TD>". _('Geocode Center Long') . "</TD>
	<TD><INPUT tabindex=3 TYPE='text' name='center_long' VALUE='". $_POST['center_long'] ."' SIZE=28 MAXLENGTH=300></TD></TR>

<TR><TD>". _('Geocode Center Lat') . "</TD>
        <TD><INPUT tabindex=4 TYPE='text' name='center_lat' VALUE='". $_POST['center_lat'] ."' SIZE=28 MAXLENGTH=300></TD></TR>

<TR><TD>". _('Geocode Map Height') . "</TD>
        <TD><INPUT tabindex=5 TYPE='text' name='map_height' VALUE='". $_POST['map_height'] ."' SIZE=28 MAXLENGTH=300></TD></TR>

<TR><TD>". _('Geocode Map Width') . "</TD>
        <TD><INPUT tabindex=6 TYPE='text' name='map_width' VALUE='". $_POST['map_width'] ."' SIZE=28 MAXLENGTH=300></TD></TR>



	</TABLE>
	<CENTER><input tabindex=4 type='Submit' name='submit' value='" . _('Enter Information') . "'>
	</FORM>";
echo '<p>When ready, click on the link below to run the GeoCode process.  This will Geocode all Branch Codes.  This may take some time.</p>';

echo '<a href="' . $rootpath . '/geocode.php">' . _('Run GeoCode process (may take a long time)') . '</a></Center></p>';
echo '<a href="' . $rootpath . '/geo_displaymap_customers.php">' . _('Display Map of Customer Branches') . '</a></Center>';
echo '<a href="' . $rootpath . '/geo_displaymap_suppliers.php">' . _('Display Map of Suppliers') . '</a></Center>';
} //end if record deleted no point displaying form to add record
include('includes/footer.inc');
?>

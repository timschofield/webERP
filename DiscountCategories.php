<?php
/* $Revision: 1.7 $ */

$PageSecurity = 11;

include('includes/session.inc');

$title = _('Discount Categories Maintenance');

include('includes/header.inc');

if (isset($_POST['submit'])) {

	//initialise no input errors assumed initially before we test
	$InputError = 0;

	/* actions to take once the user has clicked the submit button
	ie the page has called itself with some user input */

	//first off validate inputs sensible

	$result = DB_query("SELECT stockid
				FROM stockmaster
				WHERE mbflag <>'K'
				AND mbflag<>'D'
				AND stockid='" . strtoupper($_POST['StockID']) . "'",$db);
	if (DB_num_rows($result)==0){
		$InputError = 1;
		prnMsg(_('The stock item entered must be set up as either a manufactured or purchased or assembly item'),'warn');
	}

	if ($InputError !=1) {

		$sql = "UPDATE stockmaster SET discountcategory='" . $_POST['DiscountCategory'] . "'
			WHERE stockid='" . strtoupper($_POST['StockID']) . "'";

		$result = DB_query($sql,$db, _('The discount category') . ' ' . $_POST['DiscountCategory'] . ' ' . _('record for') . ' ' . strtoupper($_POST['StockID']) . ' ' . _('could not be updated because'));

		prnMsg(_('The stock master has been updated with this discount category'),'success');
		unset($_POST['DiscountCategory']);
		unset($_POST['StockID']);
	}


} elseif ($_GET['Delete']=='yes') {
/*the link to delete a selected record was clicked instead of the submit button */

	$sql="UPDATE stockmaster SET discountcategory='' WHERE stockid='" . trim(strtoupper($_GET['StockID'])) ."'";
	$result = DB_query($sql,$db);
	prnMsg( _('The stock master record has been updated to no discount category'),'success');
}

echo "<FORM METHOD='post' action=" . $_SERVER['PHP_SELF'] . '?' . SID . '>';

$sql = "SELECT DISTINCT discountcategory FROM stockmaster WHERE discountcategory <>''";

$result = DB_query($sql, $db);

echo '<CENTER>'. _('Discount Category Code') .': ';

echo "<SELECT NAME='DiscCat'>";

while ($myrow = DB_fetch_array($result)){
	if ($myrow['discountcategory']==$_POST['DiscCat']){
		echo "<OPTION SELECTED VALUE='" . $myrow['discountcategory'] . "'>" . $myrow['discountcategory'];
	} else {
		echo "<OPTION VALUE='" . $myrow['discountcategory'] . "'>" . $myrow['discountcategory'];
	}
	echo '</OPTION>';
}

echo '</SELECT>';

if (! isset($_POST['DiscCat'])){ /*set DiscCat to something to show results for first cat defined */

	if (DB_num_rows($result)>0){
		DB_data_seek($result,0);
		$myrow = DB_fetch_array($result);
		$_POST['DiscCat'] = $myrow['discountcategory'];
	} else {
		$_POST['DiscCat']='0';
	}
}

if ($_POST['DiscCat']!='0'){

	$sql = "SELECT stockmaster.stockid,
			stockmaster.description,
			discountcategory
		FROM stockmaster
		WHERE discountcategory='" . $_POST['DiscCat'] . "'
		ORDER BY stockmaster.stockid";

	$result = DB_query($sql,$db);

	echo '<table border=1>';
	echo "<tr>
		<td class='tableheader'>". _('Discount Category') ."</td>
		<td class='tableheader'>". _('Item') .'</td></TR>';

	$k=0; //row colour counter

	while ($myrow = DB_fetch_array($result)) {
		if ($k==1){
			echo "<tr bgcolor='#CCCCCC'>";
			$k=0;
		} else {
			echo "<tr bgcolor='#EEEEEE'>";
			$k=1;
		}
		$DeleteURL = $_SERVER['PHP_SELF'] . '?' . SID . '&Delete=yes&StockID=' . $myrow['stockid'] . '&DiscountCategory=' . $myrow['discountcategory'];

		printf("<td>%s</td>
			<td>%s - %s</td>
			<td><a href='%s'>". _('Delete') .'</td>
			</tr>',
			$myrow['discountcategory'],
			$myrow['stockid'],
			$myrow['description'],
			$DeleteURL);

	}

	echo '</TABLE>';

} else { /* $_POST['DiscCat'] ==0 */

	echo '</CENTER><BR>';
	prnMsg( _('There are currently no discount categories defined') . '. ' . _('Enter a two character abbreviation for the discount category and the stock code to which this category will apply to. Discount rules can then be applied to this discount category'),'info');
}

echo "<FORM METHOD='post' action=" . $_SERVER['PHP_SELF'] . '?' . SID . '>';


echo '<CENTER><TABLE><TR><TD>'. _('Discount Category Code') .':</TD><TD>';

echo "<INPUT TYPE='Text' NAME='DiscountCategory' MAXLENGTH=2 SIZE=2 VALUE='" . $_POST['DiscCat'] . "'></TD></TR>";

echo '<TR><TD>'. _('Stock Code') .":</TD><TD><input type='Text' name='StockID' SIZE=20 MAXLENGTH=20></TD></TR>";

echo '</TABLE>';

echo "<CENTER><input type='Submit' name='submit' value='". _('Enter Information') ."'></CENTER>";

echo '</FORM>';

include('includes/footer.inc');
?>
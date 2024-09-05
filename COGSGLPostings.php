<?php
/* Defines the general ledger account to be used for cost of sales entries */

include('includes/session.php');
$Title = _('Cost Of Sales GL Postings Set Up');
$ViewTopic = 'CreatingNewSystem';
$BookMark = 'COGSGLPostings';
include('includes/header.php');

echo '<p class="page_title_text"><img alt="" src="', $RootPath, '/css/', $Theme,
	'/images/maintenance.png" title="', // Icon image.
	$Title, '" /> ', // Icon title.
	$Title, '</p>
	<br />';// Page title.


if (isset($_POST['SelectedCOGSPostingID'])){
	$SelectedCOGSPostingID=$_POST['SelectedCOGSPostingID'];
} elseif (isset($_GET['SelectedCOGSPostingID'])){
	$SelectedCOGSPostingID=$_GET['SelectedCOGSPostingID'];
}

if (isset($_POST['submit'])) {

	/* actions to take once the user has clicked the submit button
	ie the page has called itself with some user input */

	if (isset($SelectedCOGSPostingID)) {

		/*SelectedCOGSPostingID could also exist if submit had not been clicked this 		code would not run in this case cos submit is false of course	see the delete code below*/

		$sql = "UPDATE cogsglpostings SET
						glcode = '" . $_POST['GLCode'] . "',
						area = '" . $_POST['Area'] . "',
						stkcat = '" . $_POST['StkCat'] . "',
						salestype='" . $_POST['SalesType'] . "'
				WHERE id ='" .$SelectedCOGSPostingID."'";

		$msg = _('Cost of sales GL posting code has been updated');
	} else {

	/*Selected Sales GL Posting is null cos no item selected on first time round so must be	adding a record must be submitting new entries in the new SalesGLPosting form */

		$sql = "INSERT INTO cogsglpostings (
						glcode,
						area,
						stkcat,
						salestype)
				VALUES (
					'" . $_POST['GLCode'] . "',
					'" . $_POST['Area'] . "',
					'" . $_POST['StkCat'] . "',
					'" . $_POST['SalesType'] . "'
					)";
		$msg = _('A new cost of sales posting code has been inserted') . '.';
	}
	//run the SQL from either of the above possibilites

	$result = DB_query($sql);
	prnMsg ($msg,'info');
	unset ($SelectedCOGSPostingID);

} elseif (isset($_GET['delete'])) {
//the link to delete a selected record was clicked instead of the submit button

	$sql="DELETE FROM cogsglpostings WHERE id='".$SelectedCOGSPostingID."'";
	$result = DB_query($sql);
	prnMsg( _('The cost of sales posting code record has been deleted'),'info');
	unset ($SelectedCOGSPostingID);
}

if (!isset($SelectedCOGSPostingID)) {

	$ShowLivePostingRecords = true;

	$sql = "SELECT cogsglpostings.id,
				cogsglpostings.area,
				cogsglpostings.stkcat,
				cogsglpostings.salestype,
				chartmaster.accountname
			FROM cogsglpostings LEFT JOIN chartmaster
			ON cogsglpostings.glcode = chartmaster.accountcode
			WHERE chartmaster.accountcode IS NULL
			ORDER BY cogsglpostings.area,
				cogsglpostings.stkcat,
				cogsglpostings.salestype";

	$result = DB_query($sql);
	if (DB_num_rows($result)>0){
		$ShowLivePostingRecords = false;
		prnMsg (_('The following cost of sales posting records that do not have valid general ledger code specified - these records must be amended.'),'error');
		echo '<table class="selection">
			<tr>
				<th>' . _('Area') . '</th>
				<th>' . _('Stock Category') . '</th>
				<th>' . _('Sales Type') . '</th>
				<th>' . _('COGS Account') . '</th>
			</tr>';

		while ($myrow = DB_fetch_array($result)) {

			printf('<tr class="striped_row">
					<td>%s</td>
					<td>%s</td>
					<td>%s</td>
					<td>%s</td>
					<td><a href="%sSelectedCOGSPostingID=%s">' . _('Edit') . '</a></td>
					<td><a href="%sSelectedCOGSPostingID=%s&amp;delete=yes" onclick="return confirm(\'' . _('Are you sure you wish to delete this COGS GL posting record?') . '\');">' .  _('Delete') . '</a></td></tr>',
					$myrow['area'],
					$myrow['stkcat'],
					$myrow['salestype'],
					$myrow['accountname'],
					htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') . '?',
					$myrow['id'],
					htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8'). '?',
					$myrow['id']);
		}//end while
		echo '</table>';
	}

	$sql = "SELECT cogsglpostings.id,
				cogsglpostings.area,
				cogsglpostings.stkcat,
				cogsglpostings.salestype
			FROM cogsglpostings
			ORDER BY cogsglpostings.area,
				cogsglpostings.stkcat,
				cogsglpostings.salestype";

	$result = DB_query($sql);

	if (DB_num_rows($result)==0){
		/* there is no default set up so need to check that account 1 is not already used */
		/* First Check if we have at least a group_ caled Sales */
		$sql = "SELECT groupname FROM accountgroups WHERE groupname = 'Sales'";
		$result = DB_query($sql);
		if (DB_num_rows($result)==0){
			/* The required group does not seem to exist so we create it */
			$sql = "INSERT INTO accountgroups (	groupname,
												sectioninaccounts,
												pandl,
												sequenceintb,
												accountgroups
										       			)
										VALUES ('Sales',
												'1',
												'1',
												'10',
												' ')";

			$result = DB_query($sql);
		}
		$sql = "SELECT accountcode FROM chartmaster WHERE accountcode ='1'";
		$result = DB_query($sql);
		if (DB_num_rows($result)==0){
		/* account number 1 is not used, so insert a new account */
			$sql = "INSERT INTO chartmaster (accountcode,
											accountname,
											group_)
									VALUES ('1',
											'Default Sales/Discounts',
											'Sales'
											)";
			$result = DB_query($sql);
		}

		$sql = "INSERT INTO cogsglpostings (	area,
											stkcat,
											salestype,
											glcode)
									VALUES ('AN',
											'ANY',
											'AN',
											'1')";
		$result = DB_query($sql);
	}

	if ($ShowLivePostingRecords){
		$sql = "SELECT cogsglpostings.id,
					cogsglpostings.area,
					cogsglpostings.stkcat,
					cogsglpostings.salestype,
					chartmaster.accountname
				FROM cogsglpostings,
					chartmaster
				WHERE cogsglpostings.glcode = chartmaster.accountcode
				ORDER BY cogsglpostings.area,
					cogsglpostings.stkcat,
					cogsglpostings.salestype";

		$result = DB_query($sql);

		echo '<table class="selection">
			<tr>
				<th>' . _('Area') . '</th>
				<th>' . _('Stock Category') . '</th>
				<th>' . _('Sales Type') . '</th>
				<th>' . _('GL Account') . '</th>
			</tr>';

		while ($myrow = DB_fetch_array($result)) {

			printf('<tr class="striped_row">
				<td>%s</td>
				<td>%s</td>
				<td>%s</td>
				<td>%s</td>
				<td><a href="%sSelectedCOGSPostingID=%s">' . _('Edit') . '</a></td>
				<td><a href="%sSelectedCOGSPostingID=%s&amp;delete=yes" onclick="return confirm(\'' . _('Are you sure you wish to delete this COGS GL posting record?') . '\');">' . _('Delete') . '</a></td>
				</tr>',
				$myrow['area'],
				$myrow['stkcat'],
				$myrow['salestype'],
				$myrow['accountname'],
				htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') . '?',
				$myrow['id'],
				htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') . '?',
				$myrow['id']);

		}//END WHILE LIST LOOP
		echo '</table>';
	}
}
//end of ifs and buts!

if (isset($SelectedCOGSPostingID)) {
	echo '<div class="centre"><a href="' . htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') .'">' . _('Show all cost of sales posting records') . '</a></div>';
}

echo '<form method="post" action="' . htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') . '">';
echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';

if (isset($SelectedCOGSPostingID)) {
	//editing an existing cost of sales posting record

	$sql = "SELECT stkcat,
				glcode,
				area,
				salestype
			FROM cogsglpostings
			WHERE id='".$SelectedCOGSPostingID."'";

	$result = DB_query($sql);
	$myrow = DB_fetch_array($result);

	$_POST['GLCode']  = $myrow['glcode'];
	$_POST['Area']	= $myrow['area'];
	$_POST['StkCat']  = $myrow['stkcat'];
	$_POST['SalesType'] = $myrow['salestype'];

	echo '<input type="hidden" name="SelectedCOGSPostingID" value="' . $SelectedCOGSPostingID . '" />';

}  //end of if $SelectedCOGSPostingID only do the else when a new record is being entered


$sql = "SELECT areacode,
		areadescription
		FROM areas";
$result = DB_query($sql);

echo '<fieldset>
		<legend>', _('Select criteria for COGS posting'), '</legend>
		<field>
			<label for="Area">', _('Area'), ':</label>
			<select name="Area" autofocus="autofocus">
				<option value="AN">', _('Any Other'), '</option>';

while ($MyRow = DB_fetch_array($result)) {
	if (isset($_POST['Area']) and $MyRow['areacode'] == $_POST['Area']) {
		echo '<option selected="selected" value="', $MyRow['areacode'], '">', $MyRow['areadescription'], '</option>';
	} else {
		echo '<option value="', $MyRow['areacode'], '">', $MyRow['areadescription'], '</option>';
	}
} //end while loop
echo '</select>
	<fieldhelp>', _('Select the area to be used in this group. To cover all areas just select Any Other'), '</fieldhelp>
</field>';

$SQL = "SELECT categoryid, categorydescription FROM stockcategory";
$Result = DB_query($SQL);

echo '<field>
		<label for="StkCat">', _('Stock Category'), ':</label>
		<select name="StkCat">
			<option value="ANY">', _('Any Other'), '</option>';

while ($MyRow = DB_fetch_array($Result)) {
	if (isset($_POST['StkCat']) and $MyRow['categoryid'] == $_POST['StkCat']) {
		echo '<option selected="selected" value="', $MyRow['categoryid'], '">', $MyRow['categorydescription'], '</option>';
	} else {
		echo '<option value="', $MyRow['categoryid'], '">', $MyRow['categorydescription'], '</option>';
	}
} //end while loop
echo '</select>
	<fieldhelp>', _('Select the stock category to be used in this group. To cover all categories just select Any Other'), '</fieldhelp>
</field>';

$SQL = "SELECT typeabbrev, sales_type FROM salestypes";
$Result = DB_query($SQL);

echo '<field>
		<label for="SalesType">', _('Sales Type'), ' / ', _('Price List'), ':</label>
		<select name="SalesType">
			<option value="AN">', _('Any Other'), '</option>';

while ($MyRow = DB_fetch_array($Result)) {
	if (isset($_POST['SalesType']) and $MyRow['typeabbrev'] == $_POST['SalesType']) {
		echo '<option selected="selected" value="', $MyRow['typeabbrev'], '">', $MyRow['sales_type'], '</option>';
	} else {
		echo '<option value="', $MyRow['typeabbrev'], '">', $MyRow['sales_type'], '</option>';
	}
} //end while loop
echo '</select>
	<fieldhelp>', _('Select the sales type to be used in this group. To cover all types just select Any Other'), '</fieldhelp>
</field>';

echo '<field>
		<label for="GLCode">', _('Post to GL account'), ':</label>';

echo '<select tabindex="4" name="GLCode">';

DB_free_result($result);
$sql = "SELECT chartmaster.accountcode,
			chartmaster.accountname
		FROM chartmaster,
			accountgroups
		WHERE chartmaster.group_=accountgroups.groupname
		AND accountgroups.pandl=1
		ORDER BY accountgroups.sequenceintb,
			chartmaster.accountcode,
			chartmaster.accountname";
$result = DB_query($sql);

while ($myrow = DB_fetch_array($result)) {
	if (isset($_POST['GLCode']) and $myrow['accountcode']==$_POST['GLCode']) {
		echo '<option selected="selected" value="';
	} else {
		echo '<option value="';
	}
	echo $myrow['accountcode'] . '">' . $myrow['accountcode']  . ' - '  . htmlspecialchars($myrow['accountname'],ENT_QUOTES,'UTF-8',false) . '</option>';

} //end while loop

DB_free_result($result);

echo '</select>';
echo '<fieldhelp>', _('Select the general ledger code to do COGS postingst to where the above criteria have been met.'), '</fieldhelp>
</field>';

echo '</fieldset>';

echo '<div class="centre">
		<input tabindex="5" type="submit" name="submit" value="' . _('Enter Information') . '" />
    </div>
	</form>';

include('includes/footer.php');
?>

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

		$SQL = "UPDATE cogsglpostings SET
						glcode = '" . $_POST['GLCode'] . "',
						area = '" . $_POST['Area'] . "',
						stkcat = '" . $_POST['StkCat'] . "',
						salestype='" . $_POST['SalesType'] . "'
				WHERE id ='" .$SelectedCOGSPostingID."'";

		$Msg = _('Cost of sales GL posting code has been updated');
	} else {

	/*Selected Sales GL Posting is null cos no item selected on first time round so must be	adding a record must be submitting new entries in the new SalesGLPosting form */

		$SQL = "INSERT INTO cogsglpostings (
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
		$Msg = _('A new cost of sales posting code has been inserted') . '.';
	}
	//run the SQL from either of the above possibilites

	$Result = DB_query($SQL);
	prnMsg ($Msg,'info');
	unset ($SelectedCOGSPostingID);

} elseif (isset($_GET['delete'])) {
//the link to delete a selected record was clicked instead of the submit button

	$SQL="DELETE FROM cogsglpostings WHERE id='".$SelectedCOGSPostingID."'";
	$Result = DB_query($SQL);
	prnMsg( _('The cost of sales posting code record has been deleted'),'info');
	unset ($SelectedCOGSPostingID);
}

if (!isset($SelectedCOGSPostingID)) {

	$ShowLivePostingRecords = true;

	$SQL = "SELECT cogsglpostings.id,
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

	$Result = DB_query($SQL);
	if (DB_num_rows($Result)>0){
		$ShowLivePostingRecords = false;
		prnMsg (_('The following cost of sales posting records that do not have valid general ledger code specified - these records must be amended.'),'error');
		echo '<table class="selection">
			<tr>
				<th>' . _('Area') . '</th>
				<th>' . _('Stock Category') . '</th>
				<th>' . _('Sales Type') . '</th>
				<th>' . _('COGS Account') . '</th>
			</tr>';

		while ($MyRow = DB_fetch_array($Result)) {

			printf('<tr class="striped_row">
					<td>%s</td>
					<td>%s</td>
					<td>%s</td>
					<td>%s</td>
					<td><a href="%sSelectedCOGSPostingID=%s">' . _('Edit') . '</a></td>
					<td><a href="%sSelectedCOGSPostingID=%s&amp;delete=yes" onclick="return confirm(\'' . _('Are you sure you wish to delete this COGS GL posting record?') . '\');">' .  _('Delete') . '</a></td></tr>',
					$MyRow['area'],
					$MyRow['stkcat'],
					$MyRow['salestype'],
					$MyRow['accountname'],
					htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') . '?',
					$MyRow['id'],
					htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8'). '?',
					$MyRow['id']);
		}//end while
		echo '</table>';
	}

	$SQL = "SELECT cogsglpostings.id,
				cogsglpostings.area,
				cogsglpostings.stkcat,
				cogsglpostings.salestype
			FROM cogsglpostings
			ORDER BY cogsglpostings.area,
				cogsglpostings.stkcat,
				cogsglpostings.salestype";

	$Result = DB_query($SQL);

	if (DB_num_rows($Result)==0){
		/* there is no default set up so need to check that account 1 is not already used */
		/* First Check if we have at least a group_ caled Sales */
		$SQL = "SELECT groupname FROM accountgroups WHERE groupname = 'Sales'";
		$Result = DB_query($SQL);
		if (DB_num_rows($Result)==0){
			/* The required group does not seem to exist so we create it */
			$SQL = "INSERT INTO accountgroups (	groupname,
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

			$Result = DB_query($SQL);
		}
		$SQL = "SELECT accountcode FROM chartmaster WHERE accountcode ='1'";
		$Result = DB_query($SQL);
		if (DB_num_rows($Result)==0){
		/* account number 1 is not used, so insert a new account */
			$SQL = "INSERT INTO chartmaster (accountcode,
											accountname,
											group_)
									VALUES ('1',
											'Default Sales/Discounts',
											'Sales'
											)";
			$Result = DB_query($SQL);
		}

		$SQL = "INSERT INTO cogsglpostings (	area,
											stkcat,
											salestype,
											glcode)
									VALUES ('AN',
											'ANY',
											'AN',
											'1')";
		$Result = DB_query($SQL);
	}

	if ($ShowLivePostingRecords){
		$SQL = "SELECT cogsglpostings.id,
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

		$Result = DB_query($SQL);

		echo '<table class="selection">
			<tr>
				<th>' . _('Area') . '</th>
				<th>' . _('Stock Category') . '</th>
				<th>' . _('Sales Type') . '</th>
				<th>' . _('GL Account') . '</th>
			</tr>';

		while ($MyRow = DB_fetch_array($Result)) {

			printf('<tr class="striped_row">
				<td>%s</td>
				<td>%s</td>
				<td>%s</td>
				<td>%s</td>
				<td><a href="%sSelectedCOGSPostingID=%s">' . _('Edit') . '</a></td>
				<td><a href="%sSelectedCOGSPostingID=%s&amp;delete=yes" onclick="return confirm(\'' . _('Are you sure you wish to delete this COGS GL posting record?') . '\');">' . _('Delete') . '</a></td>
				</tr>',
				$MyRow['area'],
				$MyRow['stkcat'],
				$MyRow['salestype'],
				$MyRow['accountname'],
				htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') . '?',
				$MyRow['id'],
				htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') . '?',
				$MyRow['id']);

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

	$SQL = "SELECT stkcat,
				glcode,
				area,
				salestype
			FROM cogsglpostings
			WHERE id='".$SelectedCOGSPostingID."'";

	$Result = DB_query($SQL);
	$MyRow = DB_fetch_array($Result);

	$_POST['GLCode']  = $MyRow['glcode'];
	$_POST['Area']	= $MyRow['area'];
	$_POST['StkCat']  = $MyRow['stkcat'];
	$_POST['SalesType'] = $MyRow['salestype'];

	echo '<input type="hidden" name="SelectedCOGSPostingID" value="' . $SelectedCOGSPostingID . '" />';

}  //end of if $SelectedCOGSPostingID only do the else when a new record is being entered


$SQL = "SELECT areacode,
		areadescription
		FROM areas";
$Result = DB_query($SQL);

echo '<fieldset>
		<legend>', _('Select criteria for COGS posting'), '</legend>
		<field>
			<label for="Area">', _('Area'), ':</label>
			<select name="Area" autofocus="autofocus">
				<option value="AN">', _('Any Other'), '</option>';

while ($MyRow = DB_fetch_array($Result)) {
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

DB_free_result($Result);
$SQL = "SELECT chartmaster.accountcode,
			chartmaster.accountname
		FROM chartmaster,
			accountgroups
		WHERE chartmaster.group_=accountgroups.groupname
		AND accountgroups.pandl=1
		ORDER BY accountgroups.sequenceintb,
			chartmaster.accountcode,
			chartmaster.accountname";
$Result = DB_query($SQL);

while ($MyRow = DB_fetch_array($Result)) {
	if (isset($_POST['GLCode']) and $MyRow['accountcode']==$_POST['GLCode']) {
		echo '<option selected="selected" value="';
	} else {
		echo '<option value="';
	}
	echo $MyRow['accountcode'] . '">' . $MyRow['accountcode']  . ' - '  . htmlspecialchars($MyRow['accountname'],ENT_QUOTES,'UTF-8',false) . '</option>';

} //end while loop

DB_free_result($Result);

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

<?php
/* $Revision: 1.15 $ */

$PageSecurity = 10;

include('includes/session.inc');

$title = _('Cost Of Sales GL Postings Set Up');

include('includes/header.inc');


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
						glcode = " . $_POST['GLCode'] . ",
						area = '" . $_POST['Area'] . "',
						stkcat = '" . $_POST['StkCat'] . "',
						salestype='" . $_POST['SalesType'] . "'
				WHERE id = $SelectedCOGSPostingID";

		$msg = _('Cost of sales GL posting code has been updated');
	} else {

	/*Selected Sales GL Posting is null cos no item selected on first time round so must be	adding a record must be submitting new entries in the new SalesGLPosting form */

		$sql = "INSERT INTO cogsglpostings (
						glcode,
						area,
						stkcat,
						salestype)
				VALUES (
					" . $_POST['GLCode'] . ",
					'" . $_POST['Area'] . "',
					'" . $_POST['StkCat'] . "',
					'" . $_POST['SalesType'] . "'
					)";
		$msg = _('A new cost of sales posting code has been inserted') . '.';
	}
	//run the SQL from either of the above possibilites

	$result = DB_query($sql,$db);
	prnMsg ($msg,'info');
	unset ($SelectedCOGSPostingID);

} elseif (isset($_GET['delete'])) {
//the link to delete a selected record was clicked instead of the submit button

	$sql="DELETE FROM cogsglpostings WHERE id=$SelectedCOGSPostingID";
	$result = DB_query($sql,$db);
	prnMsg( _('The cost of sales posting code record has been deleted'),'info');
	unset ($SelectedCOGSPostingID);

}

if (!isset($SelectedCOGSPostingID)) {

	$ShowLivePostingRecords = true;
	
	$sql = 'SELECT cogsglpostings.id,
			cogsglpostings.area,
			cogsglpostings.stkcat,
			cogsglpostings.salestype,
			chartmaster.accountname
		FROM cogsglpostings LEFT JOIN chartmaster 
			ON cogsglpostings.glcode = chartmaster.accountcode
				WHERE chartmaster.accountcode IS NULL';
				
	$result = DB_query($sql,$db);
	if (DB_num_rows($result)>0){
		$ShowLivePostingRecords = false;
		prnMsg (_('The following cost of sales posting records that do not have valid general ledger code specified - these records must be amended.'),'error');
		echo '<table border=1>';
		echo "<tr><th>" . _('Area') . "</th>
				<th>" . _('Stock Category') . "</th>
				<th>" . _('Sales Type') . "</th>
				<th>" . _('COGS Account') . "</th>
			</tr>";
		$k=0; //row colour counter
	
		while ($myrow = DB_fetch_row($result)) {
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
				<td><a href=\"%sSelectedCOGSPostingID=%s\">" . _('Edit') . "</td>
				<td><a href=\"%sSelectedCOGSPostingID=%s&delete=yes\">". _('Delete') . "</td></tr>",
				$myrow[1],
				$myrow[2],
				$myrow[3],
				$myrow[4],
				$_SERVER['PHP_SELF'] . '?' . SID . '&',
				$myrow[0],
				$_SERVER['PHP_SELF']. '?' . SID . '&',
				$myrow[0]);
		}//end while 
		echo '</table>';
	}

	$sql = 'SELECT cogsglpostings.id,
			cogsglpostings.area,
			cogsglpostings.stkcat,
			cogsglpostings.salestype
		FROM cogsglpostings';
		
	$result = DB_query($sql,$db);

	if (DB_num_rows($result)==0){
		/* there is no default set up so need to check that account 1 is not already used */
		/* First Check if we have at least a group_ caled Sales */
		$sql = "SELECT groupname FROM accountgroups WHERE groupname = 'Sales'";
		$result = DB_query($sql,$db);
		if (DB_num_rows($result)==0){
			/* The required group does not seem to exist so we create it */
			$sql = "INSERT INTO accountgroups (
					groupname, 
					sectioninaccounts, 
					pandl, 
					sequenceintb 
				) VALUES (
					'Sales',
					1,
					1,
					10)";
					
			$result = DB_query($sql,$db);	
		}		
		$sql = 'SELECT accountcode FROM chartmaster WHERE accountcode =1';
		$result = DB_query($sql,$db);
		if (DB_num_rows($result)==0){
		/* account number 1 is not used, so insert a new account */
			$sql = "INSERT INTO chartmaster (
							accountcode,
							accountname,
							group_
							)
					VALUES (
							1,
							'Default Sales/Discounts',
							'Sales'
							)";
			$result = DB_query($sql,$db);
		}

		$sql = "INSERT INTO cogsglpostings (
						area,
						stkcat,
						salestype,
						glcode)
				VALUES ('AN',
						'ANY',	
						'AN',
						1)";						
		$result = DB_query($sql,$db);
	}

	if ($ShowLivePostingRecords){
		$sql = 'SELECT cogsglpostings.id,
				cogsglpostings.area,
				cogsglpostings.stkcat,
				cogsglpostings.salestype,
				chartmaster.accountname
			FROM cogsglpostings,
				chartmaster
			WHERE cogsglpostings.glcode = chartmaster.accountcode';
	
		$result = DB_query($sql,$db);
	
		echo '<table border=1>';
		echo '<tr><th>' . _('Area') .
			'</th><th>' . _('Stock Category') .
			'</th><th>' . _('Sales Type') .
			'</th><th>' . _('GL Account') .
			'</th></tr>';

		$k = 0;
	
		while ($myrow = DB_fetch_row($result)) {
			if ($k==1){
				echo '<tr class="EvenTableRows">';
				$k=0;
			}else {
				echo '<tr class="OddTableRows">';
				$k++;
			}
		
		printf("<td>%s</td>
			<td>%s</td>
			<td>%s</td>
			<td>%s</td>
			<td><a href=\"%sSelectedCOGSPostingID=%s\">" . _('Edit') . "</td>
			<td><a href=\"%sSelectedCOGSPostingID=%s&delete=yes\">" . _('Delete') . "</td>
			</tr>",
			$myrow[1],
			$myrow[2],
			$myrow[3],
			$myrow[4],
			$_SERVER['PHP_SELF'] . '?' . SID . '&',
			$myrow[0],
			$_SERVER['PHP_SELF'] . '?' . SID . '&',
			$myrow[0]);
	
		}//END WHILE LIST LOOP
		echo '</table>';
	}
}
//end of ifs and buts!

if (isset($SelectedCOGSPostingID)) {  
	echo "<div class='centre'><a href=" . $_SERVER['PHP_SELF'] .">" . _('Show all cost of sales posting records') . "</a></div>";
}

echo "<p>";

if (!isset($_GET['delete'])) {

	echo "<form method='post' action=" . $_SERVER['PHP_SELF'] . ">";

	if (isset($SelectedCOGSPostingID)) {
		//editing an existing cost of sales posting record

		$sql = "SELECT stkcat,
				glcode,
				area,
				salestype
			FROM cogsglpostings
			WHERE id=$SelectedCOGSPostingID";

		$result = DB_query($sql, $db);
		$myrow = DB_fetch_array($result);

		$_POST['GLCode']  = $myrow['glcode'];
		$_POST['Area']	= $myrow['area'];
		$_POST['StkCat']  = $myrow['stkcat'];
		$_POST['SalesType'] = $myrow['salestype'];

		echo '<input type=hidden name="SelectedCOGSPostingID" VALUE="' . $SelectedCOGSPostingID . '">';

	}  //end of if $SelectedCOGSPostingID only do the else when a new record is being entered


	$sql = "SELECT areacode,
			areadescription
		FROM areas";
	$result = DB_query($sql,$db);

	echo "<table><tr><td>" . _('Area') . ":</td><td><select tabindex=1 name='Area'><option value='AN'>" . _('Any Other');

	while ($myrow = DB_fetch_array($result)) {
		if (isset($_POST['Area']) and $myrow['areacode']==$_POST['Area']) {
			echo "<option selected VALUE='";
		} else {
			echo "<option VALUE='";
		}
		echo $myrow['areacode'] . "'>" . $myrow['areadescription'];

	} //end while loop
	DB_free_result($result);

	$sql = 'SELECT categoryid, categorydescription FROM stockcategory';
	$result = DB_query($sql,$db);

	echo "</select></td></tr><tr><td>" . _('Stock Category') . ":</td><td><select tabindex=2 name='StkCat'>
			<option VALUE='ANY'>" . _('Any Other');

	while ($myrow = DB_fetch_array($result)) {
		if (isset($_POST['StkCat']) and $myrow["categoryid"]==$_POST['StkCat']) {
			echo "<option selected VALUE='";
		} else {
			echo "<option VALUE='";
		}
		echo $myrow['categoryid'] . "'>" . $myrow['categorydescription'];

	} //end while loop

	DB_free_result($result);

	$sql = 'SELECT typeabbrev, sales_type FROM salestypes';
	$result = DB_query($sql,$db);

	echo "</select></td></tr><tr><td>" . _('Sales Type') . " / " . _('Price List') . ":</td>
		<td><select tabindex=3 name='SalesType'><option VALUE='AN'>" . _('Any Other');

	while ($myrow = DB_fetch_array($result)) {
		if (isset($_POST['SalesType']) and $myrow['typeabbrev']==$_POST['SalesType']) {
			echo "<option selected VALUE='";
		} else {
			echo "<option VALUE='";
		}
		echo $myrow["typeabbrev"] . "'>" . $myrow['sales_type'];

	} //end while loop

	echo "</select></td></tr><tr><td>" . _('Post to GL account') . ":</td><td><select tabindex=4 name='GLCode'>";

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
	$result = DB_query($sql,$db);

	while ($myrow = DB_fetch_array($result)) {
		if (isset($_POST['GLCode']) and $myrow['accountcode']==$_POST['GLCode']) {
			echo "<option selected VALUE='";
		} else {
			echo "<option VALUE='";
		}
		echo $myrow['accountcode'] . "'>" . $myrow['accountcode']  . ' - '  . $myrow['accountname'];

	} //end while loop

	DB_free_result($result);

	echo "</select></td></tr></table><div class='centre'><input tabindex=5 type='Submit' name='submit' value=" . _('Enter Information') . "></form></div>";

} //end if record deleted no point displaying form to add record

include('includes/footer.inc');
?>

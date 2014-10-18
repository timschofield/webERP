<?php

/* $Id: SalesGLPostings.php 6310 2013-08-29 10:42:50Z daintree $*/

include('includes/session.inc');
$Title = _('Sales GL Postings Set Up');
$ViewTopic= 'CreatingNewSystem';
$BookMark = 'SalesGLPostings';
include('includes/header.inc');

if (isset($_GET['SelectedSalesPostingID'])){
	$SelectedSalesPostingID =$_GET['SelectedSalesPostingID'];
} elseif (isset($_POST['SelectedSalesPostingID'])){
	$SelectedSalesPostingID =$_POST['SelectedSalesPostingID'];
}

$InputError=false;

echo '<p class="page_title_text"><img src="'.$RootPath.'/css/'.$Theme.'/images/customer.png" title="' . _('Search') . '" alt="" />' . ' ' . $Title . '</p>';

if (isset($_POST['submit'])) {

	/* actions to take once the user has clicked the submit button
	ie the page has called itself with some user input */

	if (isset($SelectedSalesPostingID)) {

		/*SelectedSalesPostingID could also exist if submit had not been clicked this		code would not run in this case cos submit is false of course	see the delete code below*/

		$sql = "UPDATE salesglpostings SET salesglcode = '" . $_POST['SalesGLCode'] . "',
										discountglcode = '" . $_POST['DiscountGLCode'] . "',
										area = '" . $_POST['Area'] . "',
										stkcat = '" . $_POST['StkCat'] . "',
										salestype = '" . $_POST['SalesType'] . "'
				WHERE salesglpostings.id = '".$SelectedSalesPostingID."'";
		$msg = _('The sales GL posting record has been updated');
	} else {

	/*Selected Sales GL Posting is null cos no item selected on first time round so must be	adding a record must be submitting new entries in the new SalesGLPosting form */

		/* Verify if item doesn't exists to insert it, otherwise just refreshes the page. */
		$sql = "SELECT count(*) FROM salesglpostings
				WHERE area='" . $_POST['Area'] . "'
				AND stkcat='" . $_POST['StkCat'] . "'
				AND salestype='" . $_POST['SalesType'] . "'";

		$result = DB_query($sql,$db);
		$myrow = DB_fetch_row($result);
		if ($myrow[0] == 0) {
			$sql = "INSERT INTO salesglpostings (
						salesglcode,
						discountglcode,
						area,
						stkcat,
						salestype)
					VALUES (
						'" . $_POST['SalesGLCode'] . "',
						'" . $_POST['DiscountGLCode'] . "',
						'" . $_POST['Area'] . "',
						'" . $_POST['StkCat'] . "',
						'" . $_POST['SalesType'] . "'
						)";
			$msg = _('The new sales GL posting record has been inserted');
		} else {
			prnMsg (_('A sales gl posting account already exists for the selected area, stock category, salestype'),'warn');
			$InputError = true;
		}
	}
	//run the SQL from either of the above possibilites

	$result = DB_query($sql,$db);

	if ($InputError==false){
		prnMsg($msg,'success');
	}
	unset ($SelectedSalesPostingID);
	unset($_POST['SalesGLCode']);
	unset($_POST['DiscountGLCode']);
	unset($_POST['Area']);
	unset($_POST['StkCat']);
	unset($_POST['SalesType']);

} elseif (isset($_GET['delete'])) {
//the link to delete a selected record was clicked instead of the submit button

	$sql="DELETE FROM salesglpostings WHERE id='".$SelectedSalesPostingID."'";

	$result = DB_query($sql,$db);

	prnMsg( _('Sales posting record has been deleted'),'success');
}

if (!isset($SelectedSalesPostingID)) {

	$ShowLivePostingRecords = true;

	$SQL = "SELECT salesglpostings.id,
				salesglpostings.area,
				salesglpostings.stkcat,
				salesglpostings.salestype,
				salesglpostings.salesglcode,
				salesglpostings.discountglcode
				FROM salesglpostings LEFT JOIN chartmaster
					ON salesglpostings.salesglcode = chartmaster.accountcode
				WHERE chartmaster.accountcode IS NULL
				ORDER BY salesglpostings.area,
					salesglpostings.stkcat,
					salesglpostings.salestype";

	$result = DB_query($SQL,$db);
	if (DB_num_rows($result)>0){
		$ShowLivePostingRecords = false;
		prnMsg (_('The following posting records that do not have valid general ledger code specified - these records must be amended.'),'error');
		echo '<table class="selection">';
		echo '<tr><th>' . _('Area') . '</th>
				<th>' . _('Stock Category') . '</th>
				<th>' . _('Sales Type') . '</th>
				<th>' . _('Sales Account') . '</th>
				<th>' . _('Discount Account') . '</th>
			</tr>';
		$k=0; //row colour counter

		while ($myrow = DB_fetch_row($result)) {
			if ($k==1){
				echo '<tr class="EvenTableRows">';
				$k=0;
			} else {
				echo '<tr class="OddTableRows">';
				$k=1;
			}

			printf('<td>%s</td>
				<td>%s</td>
				<td>%s</td>
				<td>%s</td>
				<td>%s</td>
				<td><a href="%sSelectedSalesPostingID=%s">' . _('Edit') . '</a></td>
				<td><a href="%sSelectedSalesPostingID=%s&amp;delete=yes" onclick="return confirm(\'' . _('Are you sure you wish to delete this sales GL posting record?') . '\');">' .  _('Delete') . '</a></td></tr>',
				$myrow[1],
				$myrow[2],
				$myrow[3],
				htmlspecialchars($myrow[4],ENT_QUOTES,'UTF-8'),
				$myrow[5],
				htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') . '?',
				$myrow[0],
				htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8'). '?',
				$myrow[0]);
		}
	}

	$SQL = "SELECT salesglpostings.id,
			salesglpostings.area,
			salesglpostings.stkcat,
			salesglpostings.salestype
			FROM salesglpostings";

	$result = DB_query($SQL,$db);

	if (DB_num_rows($result)==0){
		/* there is no default set up so need to check that account 1 is not already used */
		/* First Check if we have at least a group_ caled Sales */
		$SQL = "SELECT groupname FROM accountgroups WHERE groupname = 'Sales'";
		$result = DB_query($SQL,$db);
		if (DB_num_rows($result)==0){
			/* The required group does not seem to exist so we create it */
			$SQL = "INSERT INTO accountgroups (
					groupname,
					sectioninaccounts,
					pandl,
					sequenceintb
				) VALUES (
					'Sales',
					1,
					1,
					10)";

			$result = DB_query($SQL,$db);
		}
		$SQL = "SELECT accountcode FROM chartmaster WHERE accountcode ='1'";
		$result = DB_query($SQL,$db);
		if (DB_num_rows($result)==0){
		/* account number 1 is not used, so insert a new account */
			$SQL = "INSERT INTO chartmaster (
						accountcode,
						accountname,
						group_)
					VALUES (
						1,
						'Default Sales/Discounts',
						'Sales'
						)";
			$result = DB_query($SQL,$db);
		}

		$SQL = "INSERT INTO salesglpostings (
						area,
						stkcat,
						salestype,
						salesglcode,
						discountglcode)
				VALUES ('AN',
					'ANY',
					'AN',
					1,
					1)";
		$result = DB_query($SQL,$db);

	}
	if ($ShowLivePostingRecords){

		$SQL = "SELECT salesglpostings.id,
				salesglpostings.area,
				salesglpostings.stkcat,
				salesglpostings.salestype,
				chart1.accountname,
				chart2.accountname
			FROM salesglpostings,
				chartmaster as chart1,
				chartmaster as chart2
			WHERE salesglpostings.salesglcode = chart1.accountcode
			AND salesglpostings.discountglcode = chart2.accountcode
			ORDER BY salesglpostings.area,
					salesglpostings.stkcat,
					salesglpostings.salestype";

		$result = DB_query($SQL,$db);

		echo '<table class="selection">
			<tr>
			<th>' . _('Area') . '</th>
			<th>' . _('Stock Category') . '</th>
			<th>' . _('Sales Type') . '</th>
			<th>' . _('Sales Account') . '</th>
			<th>' . _('Discount Account') . '</th>
			</tr>';

		$k=0; //row colour counter

		while ($myrow = DB_fetch_row($result)) {
			if ($k==1){
				echo '<tr class="EvenTableRows">';
				$k=0;
			} else {
				echo '<tr class="OddTableRows">';
				$k=1;
			}

			printf('<td>%s</td>
				<td>%s</td>
				<td>%s</td>
				<td>%s</td>
				<td>%s</td>
				<td><a href="%sSelectedSalesPostingID=%s">' . _('Edit') . '</a></td>
				<td><a href="%sSelectedSalesPostingID=%s&amp;delete=yes" onclick="return confirm(\'' . _('Are you sure you wish to delete this sales GL posting record?') . '\');">' .  _('Delete') . '</a></td></tr>',
				$myrow[1],
				$myrow[2],
				$myrow[3],
                htmlspecialchars($myrow[4],ENT_QUOTES,'UTF-8'),
				$myrow[5],
				htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') . '?',
				$myrow[0],
				htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8'). '?',
				$myrow[0]);
		}
		//END WHILE LIST LOOP
		echo '</table>';
	}
}

//end of ifs and buts!

if (isset($SelectedSalesPostingID)) {
	echo '<div class="centre"><a href="' . htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') . '">' . _('Show All Sales Posting Codes Defined') . '</a></div>';
}


if (!isset($_GET['delete'])) {

	echo '<form method="post" action="' . htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') .  '">';
    echo '<div>';
	echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';

	if (isset($SelectedSalesPostingID)) {
		//editing an existing sales posting record

		$sql = "SELECT salesglpostings.stkcat,
				salesglpostings.salesglcode,
				salesglpostings.discountglcode,
				salesglpostings.area,
				salesglpostings.salestype
			FROM salesglpostings
			WHERE salesglpostings.id='".$SelectedSalesPostingID."'";

		$result = DB_query($sql, $db);
		$myrow = DB_fetch_array($result);

		$_POST['SalesGLCode']= $myrow['salesglcode'];
		$_POST['DiscountGLCode']= $myrow['discountglcode'];
		$_POST['Area']=$myrow['area'];
		$_POST['StkCat']=$myrow['stkcat'];
		$_POST['SalesType']=$myrow['salestype'];
		DB_free_result($result);

		echo '<input type="hidden" name="SelectedSalesPostingID" value="' . $SelectedSalesPostingID . '" />';

	}
/*end of if $SelectedSalesPostingID only do the else when a new record is being entered */

	$SQL = "SELECT areacode,
			areadescription FROM areas";
	$result = DB_query($SQL,$db);

	echo '<br /><table class="selection">
		<tr>
		<td>' . _('Area') . ':</td>
		<td><select name="Area">
			<option value="AN">' . _('Any Other') . '</option>';

	while ($myrow = DB_fetch_array($result)) {
		if (isset($_POST['Area']) and $myrow['areacode']==$_POST['Area']) {
			echo '<option selected="selected" value="';
		} else {
			echo '<option value="';
		}
		echo $myrow['areacode'] . '">' .  $myrow['areadescription'] . '</option>';

	} //end while loop

	DB_free_result($result);

	$SQL = "SELECT categoryid, categorydescription FROM stockcategory";
	$result = DB_query($SQL,$db);

	echo '</select></td></tr>';


	echo '<tr><td>' . _('Stock Category') . ':</td>
		<td><select name="StkCat">
			<option value="ANY">' . _('Any Other') . '</option>';

	while ($myrow = DB_fetch_array($result)) {

		if (isset($_POST['StkCat']) and $myrow['categoryid']==$_POST['StkCat']) {
			echo '<option selected="selected" value="';
		} else {
			echo '<option value="';
		}
		echo $myrow['categoryid'] . '">' . $myrow['categorydescription'] . '</option>';

	} //end while loop

	echo '</select></td></tr>';


	DB_free_result($result);

	$SQL = "SELECT typeabbrev,
					sales_type
			FROM salestypes";
	$result = DB_query($SQL,$db);


	echo '<tr><td>' . _('Sales Type') . ' / ' . _('Price List') . ':</td>
		<td><select name="SalesType">';
	echo '<option value="AN">' . _('Any Other') . '</option>';

	while ($myrow = DB_fetch_array($result)) {
		if (isset($_POST['SalesType']) and $myrow['typeabbrev']==$_POST['SalesType']) {
			echo '<option selected="selected" value="';
		} else {
			echo '<option value="';
		}
		echo $myrow['typeabbrev'] . '">' . $myrow['sales_type']  . '</option>';

	} //end while loop

	echo '</select></td></tr>';


	echo '<tr><td>' . _('Post Sales to GL Account') . ':</td>
			<td><select name="SalesGLCode">';

	DB_free_result($result);
	$SQL = "SELECT chartmaster.accountcode,
			chartmaster.accountname
		FROM chartmaster,
			accountgroups
		WHERE chartmaster.group_=accountgroups.groupname
		AND accountgroups.pandl='1'
		ORDER BY accountgroups.sequenceintb,
			chartmaster.accountcode";

	$result = DB_query($SQL,$db);

	while ($myrow = DB_fetch_array($result)) {
		if (isset($_POST['SalesGLCode']) and $myrow['accountcode']==$_POST['SalesGLCode']) {
			echo '<option selected="selected" value="';
		} else {
			echo '<option value="';
		}
		echo $myrow['accountcode'] . '">' . $myrow['accountcode'] . ' - ' . htmlspecialchars($myrow['accountname'], ENT_QUOTES, 'UTF-8', false)  . '</option>';

	} //end while loop

	DB_data_seek($result,0);

	echo '</select></td></tr>
		<tr><td>' . _('Post Discount to GL Account') . ':</td>
			<td><select name="DiscountGLCode">';

	while ($myrow = DB_fetch_array($result)) {
		if (isset($_POST['DiscountGLCode']) and $myrow['accountcode']==$_POST['DiscountGLCode']) {
			echo '<option selected="selected" value="';
		} else {
			echo '<option value="';
		}
		echo $myrow['accountcode'] . '">' . $myrow['accountcode'] . ' - ' . htmlspecialchars($myrow['accountname'], ENT_QUOTES, 'UTF-8', false)  . '</option>';

	} //end while loop

	echo'</select></td>
		</tr>
		</table>';

	echo '<br /><div class="centre"><input type="submit" name="submit" value="' . _('Enter Information') . '" /></div>';

	echo '</div>
          </form>';

} //end if record deleted no point displaying form to add record


include('includes/footer.inc');
?>
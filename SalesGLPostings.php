<?php

/* $Revision: 1.8 $ */
$PageSecurity = 10;

include('includes/session.inc');
$title = _('Sales GL Postings Set Up');
include('includes/header.inc');

if (isset($_GET['SelectedSalesPostingID'])){
	$SelectedSalesPostingID =$_GET['SelectedSalesPostingID'];
} elseif (isset($_POST['SelectedSalesPostingID'])){
	$SelectedSalesPostingID =$_POST['SelectedSalesPostingID'];
}


if (isset($_POST['submit'])) {

	/* actions to take once the user has clicked the submit button
	ie the page has called itself with some user input */


	if (isset($SelectedSalesPostingID)) {

		/*SelectedSalesPostingID could also exist if submit had not been clicked this		code would not run in this case cos submit is false of course	see the delete code below*/

		$sql = 'UPDATE salesglpostings SET
				salesglcode = ' . $_POST['SalesGLCode'] . ',
				discountglcode = ' . $_POST['DiscountGLCode'] . ",
				area = '" . $_POST['Area'] . "',
				stkcat = '" . $_POST['StkCat'] . "',
				salestype = '" . $_POST['SalesType'] . "'
			WHERE salesglpostings.id = $SelectedSalesPostingID";
		$msg = _('The sales GL posting record has been updated');
	} elseif ($InputError !=1) {

	/*Selected Sales GL Posting is null cos no item selected on first time round so must be	adding a record must be submitting new entries in the new SalesGLPosting form */

		$sql = 'INSERT INTO salesglpostings (
					salesglcode,
					discountglcode,
					area,
					stkcat,
					salestype)
				VALUES (
					' . $_POST['SalesGLCode'] . ',
					' . $_POST['DiscountGLCode'] . ",
					'" . $_POST['Area'] . "',
					'" . $_POST['StkCat'] . "',
					'" . $_POST['SalesType'] . "'
					)";
		$msg = _('The new sales GL posting record has been inserted');
	}
	//run the SQL from either of the above possibilites

	$result = DB_query($sql,$db);
	prnMsg($msg,'success');
	unset ($SelectedSalesPostingID);
	unset($_POST['SalesGLCode']);
	unset($_POST['DiscountGLCode']);
	unset($_POST['Area']);
	unset($_POST['StkCat']);
	unset($_POST['SalesType']);

} elseif (isset($_GET['delete'])) {
//the link to delete a selected record was clicked instead of the submit button

	$sql="DELETE FROM salesglpostings
		WHERE id=$SelectedSalesPostingID
		AND stkcat!='ANY'
		AND area != 'AN'";

	$result = DB_query($sql,$db);

	prnMsg('<P>' . _('Sales posting record has been deleted') . '<BR><I>(' . _('That is provided it was not a default sales posting code for any stock category or location') . '. ' . _('To delete a default posting code a new default must be defined') . ', ' . _('then it must be changed to a non default code') . '. ' . _('Only then can it be deleted') .')</I>','success');
}

if (!isset($SelectedSalesPostingID)) {

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
		AND salesglpostings.discountglcode = chart2.accountcode";


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
		$SQL = 'SELECT accountcode FROM chartmaster WHERE accountcode =1';
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

		/*now re-run the query and we should have default record */
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
			AND salesglpostings.discountglcode = chart2.accountcode";


		$result = DB_query($SQL,$db);

	}

	echo '<CENTER><table border=1>';
	echo "<tr><td class='tableheader'>" . _('Area') . "</td>
		<td class='tableheader'>" . _('Stock Category') . "</td>
		<td class='tableheader'>" . _('Sales Type') . "</td>
		<td class='tableheader'>" . _('Sales Account') . "</td>
		<td class='tableheader'>" . ('Discount Account') . "</td>
		</tr>";

	$k=0; //row colour counter

	while ($myrow = DB_fetch_row($result)) {
		if ($k==1){
			echo "<tr bgcolor='#CCCCCC'>";
			$k=0;
		} else {
			echo "<tr bgcolor='#EEEEEE'>";
			$k=1;
		}

		printf("<td>%s</td>
			<td>%s</td>
			<td>%s</td>
			<td>%s</td>
			<td>%s</td>
			<td><a href=\"%sSelectedSalesPostingID=%s\">" . _('Edit') . "</td>
			<td><a href=\"%sSelectedSalesPostingID=%s&delete=yes\">". _('Delete') . "</td></tr>",
			$myrow[1],
			$myrow[2],
			$myrow[3],
			$myrow[4],
			$myrow[5],
			$_SERVER['PHP_SELF'] . '?' . SID . '&',
			$myrow[0],
			$_SERVER['PHP_SELF']. '?' . SID . '&',
			$myrow[0]);

	}
	//END WHILE LIST LOOP
	echo '</TABLE></CENTER>';
}

//end of ifs and buts!

if (isset($SelectedSalesPostingID)) {
	echo "<CENTER><A HREF='" . $_SERVER['PHP_SELF'] . '?' . SID . "'>" . _('Show All Sales Posting Codes Defined') . '</A></CENTER>';
}


if (!isset($_GET['delete'])) {

	echo "<FORM METHOD='post' action=" . $_SERVER['PHP_SELF'] . '?' . SID . '>';

	if (isset($SelectedSalesPostingID)) {
		//editing an existing sales posting record

		$sql = "SELECT salesglpostings.stkcat,
				salesglpostings.salesglcode,
				salesglpostings.discountglcode,
				salesglpostings.area,
				salesglpostings.salestype
			FROM salesglpostings
			WHERE salesglpostings.id=$SelectedSalesPostingID";

		$result = DB_query($sql, $db);
		$myrow = DB_fetch_array($result);

		$_POST['SalesGLCode']= $myrow['salesglcode'];
		$_POST['DiscountGLCode']= $myrow['discountglcode'];
		$_POST['Area']=$myrow['area'];
		$_POST['StkCat']=$myrow['stkcat'];
		$_POST['SalesType']=$myrow['salestype'];
		DB_free_result($result);

		echo "<INPUT TYPE=HIDDEN NAME='SelectedSalesPostingID' VALUE='$SelectedSalesPostingID'>";

	}
/*end of if $SelectedSalesPostingID only do the else when a new record is being entered */

	$SQL = 'SELECT areacode,
			areadescription FROM areas';
	$result = DB_query($SQL,$db);

	echo '<CENTER><TABLE>
		<TR>
		<TD>' . _('Area') . ":</TD>
		<TD><SELECT name='Area'><OPTION VALUE='ANY'>" . _('Any Other');

	while ($myrow = DB_fetch_array($result)) {
		if ($myrow['areacode']==$_POST['Area']) {
			echo "<OPTION SELECTED VALUE='";
		} else {
			echo "<OPTION VALUE='";
		}
		echo $myrow['areacode'] . "'>" . $myrow['areadescription'];

	} //end while loop

	DB_free_result($result);

	$SQL = 'SELECT categoryid, categorydescription FROM stockcategory';
	$result = DB_query($SQL,$db);

	echo '</SELECT></TD></TR>';


	echo '<TR><TD>' . _('Stock Category') . ":</TD>
		<TD><SELECT name='StkCat'><OPTION VALUE='ANY'>" . _('Any Other');

	while ($myrow = DB_fetch_array($result)) {

		if ($myrow['categoryid']==$_POST['StkCat']) {
			echo "<OPTION SELECTED VALUE='";
		} else {
			echo "<OPTION VALUE='";
		}
		echo $myrow['categoryid'] . "'>" . $myrow['categorydescription'];

	} //end while loop

	echo '</SELECT></TD></TR>';


	DB_free_result($result);

	$SQL = 'SELECT typeabbrev,
			sales_type
		FROM salestypes';
	$result = DB_query($SQL,$db);


	echo '<TR><TD>' . _('Sales Type') . ' / ' . _('Price List') . ":</TD>
		<TD><SELECT name='SalesType'>";
	echo "<OPTION VALUE='AN'>" . _('Any Other');

	while ($myrow = DB_fetch_array($result)) {
		if ($myrow['typeabbrev']==$_POST['SalesType']) {
			echo "<OPTION SELECTED VALUE='";
		} else {
			echo "<OPTION VALUE='";
		}
		echo $myrow['typeabbrev'] . "'>" . $myrow['sales_type'];

	} //end while loop

	echo '</SELECT></TD></TR>';


	echo '<TR><TD>' . _('Post Sales to GL Account') . ":</TD><TD><SELECT name='SalesGLCode'>";

	DB_free_result($result);
	$SQL = "SELECT chartmaster.accountcode,
			chartmaster.accountname
		FROM chartmaster,
			accountgroups
		WHERE chartmaster.group_=accountgroups.groupname
		AND accountgroups.pandl=1
		ORDER BY accountgroups.sequenceintb,
			chartmaster.accountname";

	$result = DB_query($SQL,$db);

	while ($myrow = DB_fetch_array($result)) {
		if ($myrow['accountcode']==$_POST['SalesGLCode']) {
			echo "<OPTION SELECTED VALUE='";
		} else {
			echo "<OPTION VALUE='";
		}
		echo $myrow['accountcode'] . "'>" . $myrow['accountname'];

	} //end while loop

	DB_data_seek($result,0);

	echo '</TD></TR>
		<TR><TD>' . _('Post Discount to GL Account') . ":</TD>
			<TD><SELECT name='DiscountGLCode'>";

	while ($myrow = DB_fetch_array($result)) {
		if ($myrow['accountcode']==$_POST['DiscountGLCode']) {
			echo "<OPTION SELECTED VALUE='";
		} else {
			echo "<OPTION VALUE='";
		}
		echo $myrow['accountcode'] . "'>" . $myrow['accountname'];

	} //end while loop

	echo'</SELECT></TD>
	</TR>
	</TABLE>';

	echo "<input type='Submit' name='submit' value='" . _('Enter Information') . "'></CENTER>";

	echo '</FORM>';

} //end if record deleted no point displaying form to add record


include('includes/footer.inc');
?>
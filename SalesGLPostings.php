<?php

/* $Revision: 1.4 $ */
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

		$sql = 'UPDATE SalesGLPostings SET
				SalesGLCode = ' . $_POST['SalesGLCode'] . ',
				DiscountGLCode = ' . $_POST['DiscountGLCode'] . ",
				Area = '" . $_POST['Area'] . "',
				StkCat = '" . $_POST['StkCat'] . "',
				SalesType = '" . $_POST['SalesType'] . "'
			WHERE ID = $SelectedSalesPostingID";
		$msg = _('The sales GL posting record has been updated');
	} elseif ($InputError !=1) {

	/*Selected Sales GL Posting is null cos no item selected on first time round so must be	adding a record must be submitting new entries in the new SalesGLPosting form */

		$sql = 'INSERT INTO SalesGLPostings (
					SalesGLCode,
					DiscountGLCode,
					Area,
					StkCat,
					SalesType)
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

	$sql="DELETE FROM SalesGLPostings
		WHERE ID=$SelectedSalesPostingID
		AND StkCat!='ANY'
		AND Area != 'AN'";

	$result = DB_query($sql,$db);

	prnMsg('<P>' . _('Sales posting record has been deleted') . '<BR><I>(' . _('That is provided it was not a default sales posting code for any stock category or location. To delete a default posting code a new default must be defined, then it must be changed to a non default code. Only then can it be deleted') .')</I>','success');
}

if (!isset($SelectedSalesPostingID)) {

	$SQL = "SELECT SalesGLPostings.ID,
			SalesGLPostings.Area,
			SalesGLPostings.StkCat,
			SalesGLPostings.SalesType,
			Chart1.AccountName,
			Chart2.AccountName
		FROM SalesGLPostings,
			ChartMaster AS Chart1,
			ChartMaster AS Chart2
		WHERE SalesGLPostings.SalesGLCode = Chart1.AccountCode
		AND SalesGLPostings.DiscountGLCode = Chart2.AccountCode";


	$result = DB_query($SQL,$db);

	if (DB_num_rows($result)==0){
	/* there is no default set up so need to check that account 1 is not already used */
		$SQL = 'SELECT AccountCode FROM ChartMaster WHERE AccountCode =1';
		$result = DB_query($SQL,$db);
		if (DB_num_rows($result)==0){
		/* account number 1 is not used, so insert a new account */
			$SQL = "INSERT INTO ChartMaster (
						AccountCode,
						AccountName,
						Group_)
					VALUES (
						1,
						'Default Sales/Discounts',
						'Sales'
						)";
			$result = DB_query($SQL,$db);
		}
		/* now insert default row for postings */

		$SQL = "INSERT INTO SalesGLPostings (
						Area,
						StkCat,
						SalesType,
						SalesGLCode,
						DiscountGLCode)
				VALUES ('AN',
					'ANY',
					'AN',
					1,
					1)";
		$result = DB_query($SQL,$db);

		/*now re-run the query and we should have default record */
		$SQL = "SELECT SalesGLPostings.ID,
				SalesGLPostings.Area,
				SalesGLPostings.StkCat,
				SalesGLPostings.SalesType,
				Chart1.AccountName,
				Chart2.AccountName
			FROM SalesGLPostings,
				ChartMaster AS Chart1,
				ChartMaster AS Chart2
			WHERE SalesGLPostings.SalesGLCode = Chart1.AccountCode
			AND SalesGLPostings.DiscountGLCode = Chart2.AccountCode";


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
			<td><a href=\"%sSelectedSalesPostingID=%s&delete=yes\">". _('DELETE') . "</td></tr>",
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
	echo '</TABLE></CENTER';
}

//end of ifs and buts!

if (isset($SelectedSalesPostingID)) {
	echo "<CENTER><A HREF='" . $_SERVER['PHP_SELF'] . '?' . SID . "'>" . _('Show All Sales Posting Codes Defined') . '</A></CENTER>';
}


if (!isset($_GET['delete'])) {

	echo "<FORM METHOD='post' action=" . $_SERVER['PHP_SELF'] . '?' . SID . '>';

	if (isset($SelectedSalesPostingID)) {
		//editing an existing sales posting record

		$sql = "SELECT StkCat,
				SalesGLCode,
				DiscountGLCode,
				Area,
				SalesType
			FROM SalesGLPostings
			WHERE ID=$SelectedSalesPostingID";

		$result = DB_query($sql, $db);
		$myrow = DB_fetch_array($result);

		$_POST['SalesGLCode']= $myrow['SalesGLCode'];
		$_POST['DiscountGLCode']= $myrow['DiscountGLCode'];
		$_POST['Area']=$myrow['Area'];
		$_POST['StkCat']=$myrow['StkCat'];
		$_POST['SalesType']=$myrow['SalesType'];
		DB_free_result($result);

		echo "<INPUT TYPE=HIDDEN NAME='SelectedSalesPostingID' VALUE='$SelectedSalesPostingID'>";

	}
/*end of if $SelectedSalesPostingID only do the else when a new record is being entered */

	$SQL = 'SELECT AreaCode,
			AreaDescription FROM Areas';
	$result = DB_query($SQL,$db);

	echo '<CENTER><TABLE><TR><TD>' . _('Area') . ":</TD><TD><SELECT name='Area'><OPTION VALUE='ANY'>" . _('Any Other');

	while ($myrow = DB_fetch_array($result)) {
		if ($myrow['AreaCode']==$_POST['Area']) {
			echo "<OPTION SELECTED VALUE='";
		} else {
			echo "<OPTION VALUE='";
		}
		echo $myrow['AreaCode'] . "'>" . $myrow['AreaDescription'];

	} //end while loop

	DB_free_result($result);

	$SQL = 'SELECT CategoryID, CategoryDescription FROM StockCategory';
	$result = DB_query($SQL,$db);

	echo '</SELECT></TD></TR>';


	echo '<TR><TD>' . _('Stock Category') . ":</TD><TD><SELECT name='StkCat'><OPTION VALUE='ANY'>" . _('Any Other');

	while ($myrow = DB_fetch_array($result)) {

		if ($myrow['CategoryID']==$_POST['StkCat']) {
			echo "<OPTION SELECTED VALUE='";
		} else {
			echo "<OPTION VALUE='";
		}
		echo $myrow['CategoryID'] . "'>" . $myrow['CategoryDescription'];

	} //end while loop

	echo '</SELECT></TD></TR>';


	DB_free_result($result);

	$SQL = 'SELECT TypeAbbrev,
			Sales_Type
		FROM SalesTypes';
	$result = DB_query($SQL,$db);


	echo '<TR><TD>' . _('Sales Type / Price List') . ":</TD><TD><SELECT name='SalesType'>";
	echo "<OPTION VALUE='AN'>" . _('Any Other');

	while ($myrow = DB_fetch_array($result)) {
		if ($myrow['TypeAbbrev']==$_POST['SalesType']) {
			echo "<OPTION SELECTED VALUE='";
		} else {
			echo "<OPTION VALUE='";
		}
		echo $myrow['TypeAbbrev'] . "'>" . $myrow['Sales_Type'];

	} //end while loop

	echo '</SELECT></TD></TR>';


	echo '<TR><TD>' . _('Post Sales to GL Account') . ":</TD><TD><SELECT name='SalesGLCode'>";

	DB_free_result($result);
	$SQL = "SELECT AccountCode,
			AccountName
		FROM ChartMaster,
			AccountGroups
		WHERE ChartMaster.Group_=AccountGroups.GroupName
		AND AccountGroups.PandL=1
		ORDER BY AccountGroups.SequenceInTB,
			ChartMaster.AccountName";

	$result = DB_query($SQL,$db);

	while ($myrow = DB_fetch_array($result)) {
		if ($myrow['AccountCode']==$_POST['SalesGLCode']) {
			echo "<OPTION SELECTED VALUE='";
		} else {
			echo "<OPTION VALUE='";
		}
		echo $myrow['AccountCode'] . "'>" . $myrow['AccountName'];

	} //end while loop

	DB_data_seek($result,0);

	echo '</TD></TR>
		<TR><TD>' . _('Post Discount to GL Account') . ":</TD>
			<TD><SELECT name='DiscountGLCode'>";

	while ($myrow = DB_fetch_array($result)) {
		if ($myrow['AccountCode']==$_POST['DiscountGLCode']) {
			echo "<OPTION SELECTED VALUE='";
		} else {
			echo "<OPTION VALUE='";
		}
		echo $myrow['AccountCode'] . "'>" . $myrow['AccountName'];

	} //end while loop

	echo'</SELECT></TD>
	</TR>
	</TABLE>';

	echo "<input type='Submit' name='submit' value='" . _('Enter Information') . "'></CENTER>";

	echo '</FORM>';

} //end if record deleted no point displaying form to add record


include('includes/footer.inc');
?>


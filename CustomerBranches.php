<?php
/* $Revision: 1.2 $ */
$title = "Customer Branches";
$PageSecurity = 3;

include("includes/session.inc");
include("includes/header.inc");

if (isset($_GET['DebtorNo'])) {
	$DebtorNo = strtoupper($_GET['DebtorNo']);
}elseif (isset($_POST['DebtorNo'])){
	$DebtorNo = strtoupper($_POST['DebtorNo']);
}

if (!isset($DebtorNo)) {
	die ("<p><p>This page must be called with the debtor code of the customer for whom you wish to edit the branches for. <BR>When the pages is called from within the system this will always be the case.<BR>Select a customer first, then select the link to add/edit/delete branches.");
}


if (isset($_GET['SelectedBranch'])){
	$SelectedBranch = strtoupper($_GET['SelectedBranch']);
}elseif (isset($_POST['SelectedBranch'])){
	$SelectedBranch = strtoupper($_POST['SelectedBranch']);
}

?>

<?php
if ($_POST['submit']) {

	//initialise no input errors assumed initially before we test
	$InputError = 0;

	/* actions to take once the user has clicked the submit button
	ie the page has called itself with some user input */

	//first off validate inputs sensible

	$_POST["BranchCode"] = strtoupper($_POST["BranchCode"]);

	if (strstr($_POST['BranchCode'],"'") OR strstr($_POST['BranchCode'],'"') OR strstr($_POST['BranchCode'],"&")) {
		$InputError = 1;
		echo "<BR>The Branch code cannot contain any of the following characters - ' & \"";
	} elseif (strlen($_POST['BranchCode'])==0) {
		$InputError = 1;
		echo "<BR>The Branch code must be at least one character long. ";
	} elseif (!Is_integer((int) $_POST['FwdDate'])) {
		$InputError = 1;
		echo "The date after which invoices are charged to the following month is expected to be a number and a recognised number has not been entered";
	} elseif ($_POST['FwdDate'] >30) {
		$InputError = 1;
		echo "The date (in the month) after which invoices are charged to the following month should be a number less than 31";
	} elseif (!Is_integer((int) $_POST['EstDeliveryDays'])) {
		$InputError = 1;
		echo "The estimated delivery days is expected to be a number and a recognised number has not been entered";
	} elseif ($_POST['EstDeliveryDays'] >60) {
		$InputError = 1;
		echo "The estimated delivery days should be a number of days less than 60. A package can be delivered by seafreight anywhere in the world normally in less than 60 days";
	}

	if (!isset($_POST['EstDeliveryDays']) OR !is_numeric($_POST['EstDeliveryDays'])){
		$_POST['EstDeliveryDays']=1;
	}
	if (!isset($_POST['FwdDate']) OR !is_numeric($_POST['FwdDate'])){
		$_POST['FwdDate']=0;
	}


	if (isset($SelectedBranch) AND $InputError !=1) {

		/*SelectedBranch could also exist if submit had not been clicked this code would not run in this case cos submit is false of course see the 	delete code below*/

		$sql = "UPDATE CustBranch SET BrName = '" . $_POST['BrName'] . "', BrAddress1 = '" . $_POST['BrAddress1'] . "', BrAddress2 = '" . $_POST['BrAddress2'] . "', BrAddress3 = '" . $_POST['BrAddress3'] . "', BrAddress4 = '" . $_POST['BrAddress4'] . "', PhoneNo='" . $_POST['PhoneNo'] . "', FaxNo='" . $_POST['FaxNo'] . "', FwdDate= " . $_POST['FwdDate'] . ", ContactName='" . $_POST['ContactName'] . "', Salesman= '" . $_POST['Salesman'] . "', Area='" . $_POST['Area'] . "', EstDeliveryDays =" . $_POST['EstDeliveryDays'] . ", Email='" . $_POST['Email'] . "', TaxAuthority=" . $_POST['TaxAuthority'] . ", DefaultLocation='" . $_POST['DefaultLocation'] . "', BrPostAddr1 = '" . $_POST['BrPostAddr1'] . "', BrPostAddr2 = '" . $_POST['BrPostAddr2'] . "', BrPostAddr3 = '" . $_POST['BrPostAddr3'] . "', BrPostAddr4 = '" . $_POST['BrPostAddr4'] . "', DisableTrans=" . $_POST['DisableTrans'] . ", DefaultShipVia=" . $_POST['DefaultShipVia'] . ", CustBranchCode='" . $_POST['CustBranchCode'] ."' WHERE BranchCode = '$SelectedBranch' AND DebtorNo='$DebtorNo'";

		$msg = "<BR>" . $_POST['BrName'] . " branch  has been updated.";

	} elseif ($InputError !=1) {

	/*Selected branch is null cos no item selected on first time round so must be adding a	record must be submitting new entries in the new Customer Branches form */

		$sql = "INSERT INTO CustBranch (BranchCode, DebtorNo, BrName, BrAddress1, BrAddress2, BrAddress3, BrAddress4, EstDeliveryDays, FwdDate, Salesman, PhoneNo, FaxNo, ContactName, Area, Email, TaxAuthority, DefaultLocation, BrPostAddr1, BrPostAddr2, BrPostAddr3, BrPostAddr4, DisableTrans, DefaultShipVia, CustBranchCode) VALUES ('" . $_POST['BranchCode'] . "', '$DebtorNo', '" . $_POST['BrName'] . "', '" . $_POST['BrAddress1'] . "', '" . $_POST['BrAddress2'] . "', '" . $_POST['BrAddress3'] . "', '" . $_POST['BrAddress4'] . "', " . $_POST['EstDeliveryDays'] . ", " . $_POST['FwdDate'] . ", '" . $_POST['Salesman'] . "', '" . $_POST['PhoneNo'] . "', '" . $_POST['FaxNo'] . "','" . $_POST['ContactName'] . "', '" . $_POST['Area'] . "','" . $_POST['Email'] . "', " . $_POST['TaxAuthority'] . ", '" . $_POST['DefaultLocation'] . "', '" . $_POST['BrPostAddr1'] . "', '" . $_POST['BrPostAddr2'] . "', '" . $_POST['BrPostAddr3'] . "', '" . $_POST['BrPostAddr4'] . "'," . $_POST['DisableTrans'] . ", " . $_POST['DefaultShipVia'] . ",'" . $_POST['CustBranchCode'] ."')";
		$msg = "<BR>Customer branch " . $_POST['BrName'] . " has been added.";
	}
	//run the SQL from either of the above possibilites


	$result = DB_query($sql,$db);

	if (DB_error_no($db) !=0) {
		echo "<BR><FONT SIZE=4 COLOR=RED>Problem Report:</FONT><BR>The branch record could not be inserted or updated because - " . DB_error_msg($db);
		if ($debug==1){
			echo "<BR>The sql that failed was:<BR>$sql";
		}

	} else {
		echo $msg;
		unset($_POST['BranchCode']);
		unset($_POST['BrName']);
		unset($_POST['BrAddress1']);
		unset($_POST['BrAddress2']);
		unset($_POST['BrAddress3']);
		unset($_POST['BrAddress4']);
		unset($_POST['EstDeliveryDays']);
		unset($_POST['FwdDate']);
		unset($_POST['Salesman']);
		unset($_POST['PhoneNo']);
		unset($_POST['FaxNo']);
		unset($_POST['ContactName']);
		unset($_POST['Area']);
		unset($_POST['Email']);
		unset($_POST['TaxAuthority']);
		unset($_POST['DefaultLocation']);
		unset($_POST['DisableTrans']);
		unset($_POST['BrPostAddr1']);
		unset($_POST['BrPostAddr2']);
		unset($_POST['BrPostAddr3']);
		unset($_POST['BrPostAddr4']);
		unset($_POST['DefaultShipVia']);
		unset($_POST['CustBranchCode']);
		unset($SelectedBranch);
	}


} elseif ($_GET['delete']) {
//the link to delete a selected record was clicked instead of the submit button

// PREVENT DELETES IF DEPENDENT RECORDS IN 'DebtorTrans'

	$sql= "SELECT COUNT(*) FROM DebtorTrans WHERE DebtorTrans.BranchCode='$SelectedBranch' AND DebtorNo = '$DebtorNo'";
	$result = DB_query($sql,$db);
	$myrow = DB_fetch_row($result);
	if ($myrow[0]>0) {
		echo "<BR>Cannot delete this branch because customer transactions have been created to this branch.";
		echo "<br> There are " . $myrow[0] . " transactions with this Branch Code.";

	} else {
		$sql= "SELECT COUNT(*) FROM SalesAnalysis WHERE SalesAnalysis.CustBranch='$SelectedBranch' AND Cust = '$DebtorNo'";

		$result = DB_query($sql,$db);

		$myrow = DB_fetch_row($result);
		if ($myrow[0]>0) {
			echo "<BR>Cannot delete this branch because sales analysis records exist for it.";
		echo "<br> There are " . $myrow[0] . " sales analysis records with this Branch Code/customer.";

		} else {

			$sql= "SELECT COUNT(*) FROM SalesOrders WHERE SalesOrders.BranchCode='$SelectedBranch' AND SalesOrders.DebtorNo = '$DebtorNo'";
			$result = DB_query($sql,$db);

			$myrow = DB_fetch_row($result);
			if ($myrow[0]>0) {
				echo "<BR>Cannot delete this branch because sales orders exist for it. Purge old sales orders first.";
			echo "<br> There are " . $myrow[0] . " sales orders for this Branch/customer.";
			} else {
				// Sherifoz 22.06.03 Check if there are any users that refer to this branch code
				$sql= "SELECT COUNT(*) FROM WWW_Users WHERE WWW_Users.BranchCode='$SelectedBranch' AND WWW_Users.CustomerID = '$DebtorNo'";

				$result = DB_query($sql,$db);
				$myrow = DB_fetch_row($result);

				if ($myrow[0]>0) {
					echo "<BR>Cannot delete this branch because users exist that refer to it. Purge old users first.";
				echo "<br> There are " . $myrow[0] . " users referring to this Branch/customer.";
				} else {

					$sql="DELETE FROM CustBranch WHERE BranchCode='$SelectedBranch' AND DebtorNo='$DebtorNo'";
					$result = DB_query($sql,$db);
					echo "Record Deleted <p>";
				}
			}
		}
	} //end ifs to test if the branch can be deleted

}

if (!isset($SelectedBranch)){

/* It could still be the second time the page has been run and a record has been selected for modification - SelectedBranch will exist because it was sent with the new call. If its the first time the page has been displayed with no parameters then none of the above are true and the list of branches will be displayed with links to delete or edit each. These will call the same page again and allow update/input or deletion of the records*/

	$sql = "SELECT DebtorsMaster.Name, CustBranch.BranchCode, BrName, Salesman.SalesmanName, Areas.AreaDescription, ContactName, PhoneNo, FaxNo, Email, TaxAuthority, CustBranch.BranchCode FROM CustBranch, DebtorsMaster, Areas, Salesman WHERE CustBranch.DebtorNo=DebtorsMaster.DebtorNo AND CustBranch.Area=Areas.AreaCode AND CustBranch.Salesman=Salesman.SalesmanCode AND CustBranch.DebtorNo = '$DebtorNo'";

	$result = DB_query($sql,$db);
	$myrow = DB_fetch_row($result);

	if ($myrow) {
		echo "<BR><B>Branches Defined for $DebtorNo - $myrow[0]</B>";
	} else {
		$sql = "SELECT DebtorsMaster.Name, Address1, Address2, Address3, Address4 FROM DebtorsMaster WHERE DebtorNo = '$DebtorNo'";
		$result = DB_query($sql,$db);
		$myrow = DB_fetch_row($result);
		echo "<B>Branches Defined for - $myrow[0]</B>";
		$_POST['BranchCode'] = substr($DebtorNo,0,10);
		$_POST['BrName'] = $myrow[0];
		$_POST['BrAddress1'] = $myrow[1];
		$_POST['BrAddress2'] = $myrow[2];
		$_POST['BrAddress3'] = $myrow[3];
		$_POST['BrAddress4'] = $myrow[4];
		unset($myrow);
	}

	echo "<table border=1>\n";

	echo "<tr><td class='tableheader'>Code</td><td class='tableheader'>Name</td><td class='tableheader'>Contact</td><td class='tableheader'>Salesman</td><td class='tableheader'>Area</td><td class='tableheader'>Phone No</td><td class='tableheader'>Fax No</td><td class='tableheader'>E-mail</td><td class='tableheader'>Tax Auth</td></tr>\n";

	do {
		 printf("<tr><td><font size=2>%s</td><td><font size=2>%s</td><td><font size=2>%s</font></td><td><font size=2>%s</font></td><td><font size=2>%s</font></td><td><font size=2>%s</font></td><td><font size=2>%s</font></td><td><font size=2><a href=\"Mailto:%s\">%s</a></font></td><td><font size=2>%s</font></td><td><font size=2><a href=\"%s?DebtorNo=%s&SelectedBranch=%s\">Edit</font></td><td><font size=2><a href=\"%s?DebtorNo=%s&SelectedBranch=%s&delete=yes\">DELETE</font></td></tr>", $myrow[10],$myrow[2], $myrow[5], $myrow[3], $myrow[4], $myrow[6], $myrow[7], $myrow[8], $myrow[8], $myrow[9], $_SERVER['PHP_SELF'], $DebtorNo,$myrow[1], $_SERVER['PHP_SELF'], $DebtorNo, $myrow[1]);

	} while ($myrow = DB_fetch_row($result));

	//END WHILE LIST LOOP
}

//end of ifs and buts!

?>
</table>
<p>
<?php
if (isset($SelectedBranch)) {  ?>
	<Center><a href="<?php echo $_SERVER['PHP_SELF'] . "?" . SID . "DebtorNo=" . $DebtorNo; ?>">Show all branches defined for <?php echo $DebtorNo; ?></a></Center>
<?php } ?>

<P>


<?php


if (! isset($_GET['delete'])) {

	echo "<FORM METHOD='post' action=" . $_SERVER['PHP_SELF'] ."?" . SID . ">";

	if (isset($SelectedBranch)) {
		//editing an existing branch

		$sql = "SELECT BranchCode, BrName, BrAddress1, BrAddress2, BrAddress3, BrAddress4, EstDeliveryDays, FwdDate, Salesman, Area, PhoneNo, FaxNo, ContactName, Email, TaxAuthority, DefaultLocation, BrPostAddr1, BrPostAddr2, BrPostAddr3, BrPostAddr4, DisableTrans, DefaultShipVia, CustBranchCode FROM CustBranch WHERE BranchCode='$SelectedBranch' AND DebtorNo='$DebtorNo'";

		$result = DB_query($sql, $db);
		$myrow = DB_fetch_array($result);

		$_POST['BranchCode'] = $myrow["BranchCode"];
		$_POST['BrName']  = $myrow["BrName"];
		$_POST['BrAddress1']  = $myrow["BrAddress1"];
		$_POST['BrAddress2']  = $myrow["BrAddress2"];
		$_POST['BrAddress3']  = $myrow["BrAddress3"];
		$_POST['BrAddress4']  = $myrow["BrAddress4"];
		$_POST['BrPostAddr1']  = $myrow["BrPostAddr1"];
		$_POST['BrPostAddr2']  = $myrow["BrPostAddr2"];
		$_POST['BrPostAddr3']  = $myrow["BrPostAddr3"];
		$_POST['BrPostAddr4']  = $myrow["BrPostAddr4"];
		$_POST['EstDeliveryDays']  = $myrow["EstDeliveryDays"];
		$_POST['FwdDate'] =$myrow["FwdDate"];
		$_POST['ContactName'] = $myrow["ContactName"];
		$_POST['Salesman'] =$myrow["Salesman"];
		$_POST['Area'] =$myrow["Area"];
		$_POST['PhoneNo'] =$myrow["PhoneNo"];
		$_POST['FaxNo'] =$myrow["FaxNo"];
		$_POST['Email'] =$myrow["Email"];
		$_POST['TaxAuthority'] = $myrow["TaxAuthority"];
		$_POST['DisableTrans'] = $myrow['DisableTrans'];
		$_POST['DefaultLocation'] = $myrow["DefaultLocation"];
		$_POST['DefaultShipVia'] = $myrow['DefaultShipVia'];
		$_POST['CustBranchCode'] = $myrow['CustBranchCode'];

		echo "<INPUT TYPE=HIDDEN NAME='SelectedBranch' VALUE=" . $SelectedBranch . ">";
		echo "<INPUT TYPE=HIDDEN NAME='BranchCode'  VALUE=" . $_POST['BranchCode'] . ">";
		echo "<CENTER><TABLE> <TR><TD>Branch Code:</TD><TD>";
		echo $_POST['BranchCode'] . "</TD></TR>";

	} else { //end of if $SelectedBranch only do the else when a new record is being entered

		echo "<CENTER><TABLE><TR><TD>Branch Code:</TD><TD><input type='Text' name='BranchCode' SIZE=12 MAXLENGTH=10 value=" . $_POST['BranchCode'] . "></TD></TR>";
	}

	//SQL to poulate account selection boxes
	$sql = "SELECT SalesmanName, SalesmanCode FROM Salesman";

	$result = DB_query($sql,$db);

	if (DB_num_rows($result)==0){
		echo "</TABLE>";
		echo "<BR><FONT COLOR=RED SIZE=4>Problem Report:</FONT><BR>There are no sales people defined as yet - customer branches must be allocated to an sales person. Please use the link below to define at least one sales person.";
		echo "<BR><A HREF='$rootpath/SalesPeople.php?" . SID . "'>Define Sales People</A>";
		exit;
	}

	?>

	<input type=HIDDEN name="DebtorNo" value="<?php echo $DebtorNo;?>">


	<TR><TD>Branch Name:</TD>
	<TD><input type="Text" name="BrName" SIZE=41 MAXLENGTH=40 value="<?php echo $_POST['BrName'];?>"></TD></TR>
	<TR><TD>Contact:</TD>
	<TD><input type="Text" name="ContactName" SIZE=41 MAXLENGTH=40 value="<?php echo $_POST['ContactName'];?>"></TD></TR>
	<TR><TD>Street Address 1:</TD>
	<TD><input type="Text" name="BrAddress1" SIZE=41 MAXLENGTH=40 value="<?php echo $_POST['BrAddress1']; ?>"></TD></TR>
	<TR><TD>Street Address 2:</TD>
	<TD><input type="Text" name="BrAddress2" SIZE=41 MAXLENGTH=40 value="<?php echo $_POST['BrAddress2']; ?>"></TD></TR>
	<TR><TD>Street Address 3:</TD>
	<TD><input type="Text" name="BrAddress3" SIZE=41 MAXLENGTH=40 value="<?php echo $_POST['BrAddress3']; ?>"></TD></TR>
	<TR><TD>Street Address 4:</TD>
	<TD><input type="Text" name="BrAddress4" SIZE=31 MAXLENGTH=30 value="<?php echo $_POST['BrAddress4']; ?>"></TD></TR>

	<TR><TD>Delivery Days:</TD>
	<TD><input type="Text" name="EstDeliveryDays" SIZE=4 MAXLENGTH=2 value=<?php echo $_POST['EstDeliveryDays'];?>></TD></TR>
	<TR><TD>Forward Date After (day in month):</TD>
	<TD><input type="Text" name="FwdDate" SIZE=4 MAXLENGTH=2 value="<?php echo $_POST['FwdDate'];?>"></TD></TR>

	<TR><TD>Sales-person:</TD>
	<TD><SELECT name="Salesman">

	<?php
	while ($myrow = DB_fetch_array($result)) {
		if ($myrow["SalesmanCode"]==$_POST['Salesman']) {
			echo "<OPTION SELECTED VALUE=";
		} else {
			echo "<OPTION VALUE=";
		}
		echo $myrow["SalesmanCode"] . ">" . $myrow["SalesmanName"];

	} //end while loop

	echo "</SELECT></TD></TR>";

	DB_data_seek($result,0);

	$sql = "SELECT AreaCode, AreaDescription FROM Areas";
	$result = DB_query($sql,$db);
	if (DB_num_rows($result)==0){
		echo "</TABLE>";
		echo "<BR><FONT COLOR=RED SIZE=4>Problem Report:</FONT><BR>There are no areas defined as yet - customer branches must be allocated to an area. Please use the link below to define at least one sales area.";
		echo "<BR><A HREF='$rootpath/Areas.php?" . SID . "'>Define Sales Areas</A>";
		exit;
	}

	?>

	<TR><TD>Sales Area:</TD>
	<TD>
	<SELECT name="Area">
	<?php
	while ($myrow = DB_fetch_array($result)) {
		if ($myrow["AreaCode"]==$_POST['Area']) {
			echo "<OPTION SELECTED VALUE=";
		} else {
			echo "<OPTION VALUE=";
		}
		echo $myrow["AreaCode"] . ">" . $myrow["AreaDescription"];

	} //end while loop


	echo "</SELECT></TD></TR>";
	DB_data_seek($result,0);

	$sql = "SELECT LocCode, LocationName FROM Locations";
	$result = DB_query($sql,$db);

	if (DB_num_rows($result)==0){
		echo "</TABLE>";
		echo "<BR><FONT COLOR=RED SIZE=4>Problem Report:</FONT><BR>There are no stock locations defined as yet - customer branches must refer to a default location where stock is normally drawn from. Please use the link below to define at least one stock location.";
		echo "<BR><A HREF='$rootpath/Locations.php?" . SID . "'>Define Stock Locations</A>";
		exit;
	}

	?>

	<TR><TD>Draw Stock From:</TD>
	<TD>
	<SELECT name="DefaultLocation">
	<?php
	while ($myrow = DB_fetch_array($result)) {
		if ($myrow["LocCode"]==$_POST['DefaultLocation']) {
			echo "<OPTION SELECTED VALUE=";
		} else {
			echo "<OPTION VALUE=";
		}
		echo $myrow["LocCode"] . ">" . $myrow["LocationName"];

	} //end while loop

	?>
	</SELECT></TD></TR>
	<TR><TD>Phone Number:</TD>
	<TD><input type="Text" name="PhoneNo" SIZE=22 MAXLENGTH=20 value="<?php echo $_POST['PhoneNo'];?>"></TD></TR>

	<TR><TD>Fax Number:</TD>
	<TD><input type="Text" name="FaxNo" SIZE=22 MAXLENGTH=20 value="<?php echo $_POST['FaxNo'];?>"></TD></TR>


	<TR><TD><a href="Mailto:<?php echo $_POST['Email']; ?>">E-mail:</a></TD>
	<TD><input type="Text" name="Email" SIZE=56 MAXLENGTH=55 value="<?php echo $_POST['Email']; ?>"></TD></TR>

	<TR><TD>Tax Authority:</TD>
	<TD>
	<SELECT name="TaxAuthority">
	<?php

	DB_data_seek($result,0);

	$sql = "SELECT TaxID, Description FROM TaxAuthorities";
	$result = DB_query($sql,$db);

	while ($myrow = DB_fetch_array($result)) {
		if ($myrow["TaxID"]==$_POST['TaxAuthority']) {
			echo "<OPTION SELECTED VALUE=";
		} else {
			echo "<OPTION VALUE=";
		}
		echo $myrow["TaxID"] . ">" . $myrow["Description"];

	} //end while loop

	echo "</SELECT></TD></TR>";
	echo "<TR><TD>Disable transactions on this branch</TD><TD><SELECT NAME='DisableTrans'>";
	if ($_POST['DisableTrans']==0){
		echo "<OPTION SELECTED VALUE=0>Enabled";
		echo "<OPTION VALUE=1>Disabled";
	} else {
		echo "<OPTION SELECTED VALUE=1>Disabled";
		echo "<OPTION VALUE=0>Enabled";
	}

	echo "	</SELECT></TD></TR>";

	echo "<TR><TD>Default freight company:</TD><TD><SELECT name='DefaultShipVia'>";
	$SQL = "SELECT Shipper_ID, ShipperName FROM Shippers";
	$ShipperResults = DB_query($SQL,$db);
	while ($myrow=DB_fetch_array($ShipperResults)){
		if ($myrow['Shipper_ID']==$_POST['DefaultShipVia']){
			echo "<OPTION SELECTED VALUE=" . $myrow["Shipper_ID"] . ">" . $myrow["ShipperName"];
		}else {
			echo "<OPTION VALUE=" . $myrow["Shipper_ID"] . ">" . $myrow["ShipperName"];
		}
	}

	echo "</SELECT></TD></TR>";

	?>

	<TR><TD>Postal Address 1:</TD>
	<TD><input type="Text" name="BrPostAddr1" SIZE=41 MAXLENGTH=40 value="<?php echo $_POST['BrPostAddr1']; ?>"></TD></TR>
	<TR><TD>Post Address 2:</TD>
	<TD><input type="Text" name="BrPostAddr2" SIZE=41 MAXLENGTH=40 value="<?php echo $_POST['BrPostAddr2']; ?>"></TD></TR>
	<TR><TD>Postal Address 3:</TD>
	<TD><input type="Text" name="BrPostAddr3" SIZE=31 MAXLENGTH=30 value="<?php echo $_POST['BrPostAddr3']; ?>"></TD></TR>
	<TR><TD>Postal Address 4:</TD>
	<TD><input type="Text" name="BrPostAddr4" SIZE=21 MAXLENGTH=20 value="<?php echo $_POST['BrPostAddr4']; ?>"></TD></TR>
	<TR><TD>Customers Internal Branch Code (EDI):</TD>
	<TD><input type="Text" name="CustBranchCode" SIZE=31 MAXLENGTH=30 value="<?php echo $_POST['CustBranchCode']; ?>"></TD></TR>
	</TABLE>

	<CENTER><input type="Submit" name="submit" value="Enter Information">

	</FORM>

<?php } //end if record deleted no point displaying form to add record

include("includes/footer.inc");
?>

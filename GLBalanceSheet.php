<?php

/* $Revision: 1.1 $ */

/*Through deviousness and cunning, this system allows shows the balance sheets as at the end of any period selected - so first off need to show the input of criteria screen while the user is selecting the period end of the balance date meanwhile the system is posting any unposted transactions */
$title = "Balance Sheet";

$PageSecurity = 8;

include ("includes/session.inc");
include("includes/header.inc");
include("includes/DateFunctions.inc");
include("includes/SQL_CommonFunctions.inc");


echo "<FORM METHOD='POST' ACTION=" . $_SERVER["PHP_SELF"] . "?" . SID . ">";


if (! isset($_POST["BalancePeriodEnd"]) OR $_POST["SelectADifferentPeriod"]=="Select A Different Balance Date"){


/*Show a form to allow input of criteria for TB to show */
	echo "<CENTER><TABLE><TR><TD>Select The Balance Date:</TD><TD><SELECT Name='BalancePeriodEnd'>";

	$sql = "SELECT PeriodNo, LastDate_In_Period FROM Periods";
	$Periods = DB_query($sql,$db);


	while ($myrow=DB_fetch_array($Periods,$db)){
		if( $_POST["BalancePeriodEnd"]== $myrow["PeriodNo"]){
			echo "<OPTION SELECTED VALUE=" . $myrow["PeriodNo"] . ">" . ConvertSQLDate($myrow["LastDate_In_Period"]);
		} else {
			echo "<OPTION VALUE=" . $myrow["PeriodNo"] . ">" . ConvertSQLDate($myrow["LastDate_In_Period"]);
		}
	}

	echo "</SELECT></TD></TR>";

	echo "<TR><TD>Detail Or Summary:</TD><TD><SELECT Name='Detail'>";
		echo "<OPTION SELECTED VALUE='Summary'>Summary";
		echo "<OPTION SELECTED VALUE='Detailed'>All Accounts";
	echo "</SELECT></TD></TR>";

	echo "</TABLE>";

	echo "<INPUT TYPE=SUBMIT Name='ShowBalanceSheet' Value='Show Balance Sheet'></CENTER>";

/*Now do the posting while the user is thinking about the period to select */

	include ("includes/GLPostings.inc");

} else {

	echo "<INPUT TYPE=HIDDEN NAME='BalancePeriodEnd' VALUE=" . $_POST["BalancePeriodEnd"] . ">";

	$CompanyRecord = ReadInCompanyRecord($db);
	$RetainedEarningsAct = $CompanyRecord["RetainedEarnings"];

	$sql = "SELECT LastDate_in_Period FROM Periods WHERE PeriodNo=" . $_POST["BalancePeriodEnd"];
	$PrdResult = DB_query($sql, $db);
	$myrow = DB_fetch_row($PrdResult);
	$BalanceDate = ConvertSQLDate($myrow[0]);

	/*Calculate B/Fwd retained earnings */

	$SQL = "SELECT Sum(CASE WHEN ChartDetails.Period=" . $_POST['BalancePeriodEnd'] . " THEN ChartDetails.BFwd + ChartDetails.Actual ELSE 0 END) AS AccumProfitBFwd,
			Sum(CASE WHEN ChartDetails.Period=" . ($_POST['BalancePeriodEnd'] - 12) . " THEN ChartDetails.BFwd + ChartDetails.Actual ELSE 0 END) AS LYAccumProfitBFwd
		FROM ChartMaster INNER JOIN AccountGroups
		ON ChartMaster.Group_ = AccountGroups.GroupName INNER JOIN ChartDetails
		ON ChartMaster.AccountCode= ChartDetails.AccountCode
		WHERE AccountGroups.PandL=1";

	$AccumProfitResult = DB_query($SQL,$db,"<BR>The accumulated profits brought forward could not be calculated by the SQL because","<br>The SQL that failed was:");

	$AccumProfitRow = DB_fetch_array($AccumProfitResult); /*should only be one row returned */

	$SQL = "SELECT AccountGroups.SectionInAccounts, AccountGroups.GroupName,
			ChartDetails.AccountCode ,
			ChartMaster.AccountName,
			Sum(CASE WHEN ChartDetails.Period=" . $_POST['BalancePeriodEnd'] . " THEN ChartDetails.BFwd + ChartDetails.Actual ELSE 0 END) AS BalanceCFwd,
			Sum(CASE WHEN ChartDetails.Period=" . ($_POST['BalancePeriodEnd'] - 12) . " THEN ChartDetails.BFwd + ChartDetails.Actual ELSE 0 END) AS LYBalanceCFwd
		FROM ChartMaster INNER JOIN AccountGroups
		ON ChartMaster.Group_ = AccountGroups.GroupName INNER JOIN ChartDetails
		ON ChartMaster.AccountCode= ChartDetails.AccountCode
		WHERE AccountGroups.PandL=0
		GROUP BY AccountGroups.GroupName,
			ChartDetails.AccountCode,
			ChartMaster.AccountName
		ORDER BY AccountGroups.SectionInAccounts, AccountGroups.SequenceInTB, ChartDetails.AccountCode";

	$AccountsResult = DB_query($SQL,$db,"<BR>No general ledger accounts were returned by the SQL because","<br>The SQL that failed was:");

	echo "<CENTER><FONT SIZE=4 COLOR=BLUE><B>Balance Sheet As At $BalanceDate</B></FONT><BR>";

	echo "<TABLE CELLPADDING=2>";

	if ($_POST['Detail']=='Detailed'){
		$TableHeader = "<TR>
				<TD class='tableheader'>Account</TD>
				<TD class='tableheader'>Account Name</TD>
				<TD COLSPAN=2 class='tableheader' ALIGN=CENTER>$BalanceDate</TD>
				<TD COLSPAN=2 class='tableheader' ALIGN=CENTER>Last Year</TD>
				</TR>";
	} else { /*summary */
		$TableHeader = "<TR>
				<TD COLSPAN=2 class='tableheader'></TD>
				<TD COLSPAN=2 class='tableheader' ALIGN=CENTER>$BalanceDate</TD>
				<TD COLSPAN=2 class='tableheader' ALIGN=CENTER>Last Year</TD>
				</TR>";
	}


	$k=0; //row colour counter
	$Section="";
	$SectionBalance = 0;
	$SectionBalanceLY = 0;

	$LYCheckTotal = 0;
	$CheckTotal = 0;

	$ActGrp ="";

	$GroupTotal = 0;
	$LYGroupTotal = 0;

	while ($myrow=DB_fetch_array($AccountsResult)) {

		$AccountBalance = $myrow['BalanceCFwd'];
		$LYAccountBalance = $myrow["LYBalanceCFwd"];

		if ($myrow['AccountCode'] == $RetainedEarningsAct){
			$AccountBalance += $AccumProfitRow['AccumProfitBFwd'];
			$LYAccountBalance += $AccumProfitRow['LYAccumProfitBFwd'];
		}

		if ($myrow["GroupName"]!= $ActGrp AND $_POST['Detail']=='Summary' AND $ActGrp != "") {

			printf("<td COLSPAN=3>%s</td>
			<td ALIGN=RIGHT>%s</td>
			<TD></TD>
			<td ALIGN=RIGHT>%s</td>
			</tr>",
			$ActGrp,
			number_format($GroupTotal),
			number_format($LYGroupTotal)
			);

		}
		if ($myrow["SectionInAccounts"]!= $Section){

			if ($SectionBalanceLY+$SectionBalance !=0){
				if ($_POST['Detail']=='Detailed'){
					echo "<TR>
					<TD COLSPAN=2></TD>
      					<TD><HR></TD>
					<TD></TD>
					<TD><HR></TD>
					<TD></TD>
					</TR>";
				} else {
					echo "<TR>
					<TD COLSPAN=3></TD>
      					<TD><HR></TD>
					<TD></TD>
					<TD><HR></TD>
					</TR>";
				}

				printf("<TR>
				<TD COLSPAN=3><FONT SIZE=4>%s</FONT></td>
				<TD ALIGN=RIGHT>%s</TD>
				<TD></TD>
				<TD ALIGN=RIGHT>%s</TD>
				</TR>",
				$Sections[$Section],
				number_format($SectionBalance),
				number_format($SectionBalanceLY));
			}
			$SectionBalanceLY = 0;
			$SectionBalance = 0;

			$Section = $myrow["SectionInAccounts"];

			if ($_POST['Detail']=="Detailed"){
				printf("<TR>
					<TD COLSPAN=6><FONT SIZE=4 COLOR=BLUE><B>%s</B></FONT></TD>
					</TR>",
					$Sections[$myrow["SectionInAccounts"]]);
			}
		}

		if ($myrow["GroupName"]!= $ActGrp){

			if ($_POST['Detail']=='Detailed'){
				$ActGrp = $myrow["GroupName"];
				printf("<TR>
				<td COLSPAN=6><FONT SIZE=2 COLOR=BLUE><B>%s</B></FONT></TD>
				</TR>",
				$myrow["GroupName"]);
				echo $TableHeader;
			}
			$GroupTotal=0;
			$LYGroupTotal=0;
			$ActGrp = $myrow["GroupName"];
		}

		$SectionBalanceLY +=	$LYAccountBalance;
		$SectionBalance	  +=	$AccountBalance;

		$LYGroupTotal	  +=	$LYAccountBalance;
		$GroupTotal	  +=	$AccountBalance;

		$LYCheckTotal 	  +=	$LYAccountBalance;
		$CheckTotal  	  +=	$AccountBalance;


		if ($_POST['Detail']=='Detailed'){

			if ($k==1){
				echo "<tr bgcolor='#CCCCCC'>";
				$k=0;
			} else {
				echo "<tr bgcolor='#EEEEEE'>";
				$k++;
			}

			$ActEnquiryURL = "<A HREF='$rootpath/GLAccountInquiry.php?" . SID . "Period=" . $_POST["BalancePeriodEnd"] . "&Account=" . $myrow["AccountCode"] . "'>" . $myrow["AccountCode"] . "<A>";

			$PrintString = "<td>%s</td>
					<td>%s</td>
					<td ALIGN=RIGHT>%s</td>
					<TD></TD>
					<td ALIGN=RIGHT>%s</td>
					<TD></TD>
					</tr>";

			printf($PrintString,
				$ActEnquiryURL,
				$myrow["AccountName"],
				number_format($AccountBalance),
				number_format($LYAccountBalance)
				);

		}
	}
	//end of loop


	if ($SectionBalanceLY+$SectionBalance !=0){
		if ($_POST['Detail']=="Summary"){
			printf("<td COLSPAN=3>%s</td>
				<td ALIGN=RIGHT>%s</td>
				<TD></TD>
				<td ALIGN=RIGHT>%s</td>
				</tr>",
			$ActGrp,
			number_format($GroupTotal),
			number_format($LYGroupTotal)
			);
		}
		echo "<TR>
			<TD COLSPAN=3></TD>
      			<TD><HR></TD>
			<TD></TD>
			<TD><HR></TD>
			</TR>";

		printf("<TR>
			<TD COLSPAN=3><FONT SIZE=4>%s</FONT></td>
			<TD ALIGN=RIGHT>%s</TD>
			<TD></TD>
			<TD ALIGN=RIGHT>%s</TD>
			</TR>",
			$Sections[$Section],
			number_format($SectionBalance),
			number_format($SectionBalanceLY));
	}

	echo "<TR>
		<TD COLSPAN=3></TD>
      		<TD><HR></TD>
		<TD></TD>
		<TD><HR></TD>
		</TR>";

	printf("<TR>
		<TD COLSPAN=3>Check Total</FONT></td>
		<TD ALIGN=RIGHT>%s</TD>
		<TD></TD>
		<TD ALIGN=RIGHT>%s</TD>
		</TR>",
		number_format($CheckTotal),
		number_format($LYCheckTotal));

	echo "<TR>
		<TD COLSPAN=3></TD>
      		<TD><HR></TD>
		<TD></TD>
		<TD><HR></TD>
		</TR>";

	echo "</TABLE>";
	echo "<INPUT TYPE=SUBMIT Name='SelectADifferentPeriod' Value='Select A Different Balance Date'></CENTER>";
}
echo "</form>";
include("includes/footer.inc");

?>

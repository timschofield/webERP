<?php
/* $Revision: 1.2 $ */
$title = "General Ledger Account Inquiry";
$PageSecurity = 8;
include ("includes/session.inc");
include("includes/header.inc");
include("includes/DateFunctions.inc");
include("includes/GLPostings.inc");


if (isset($_POST["Account"])){
	$SelectedAccount = $_POST["Account"];
} elseif (isset($_GET["Account"])){
	$SelectedAccount = $_GET["Account"];
}

if (isset($_POST["Period"])){
	$SelectedPeriod = $_POST["Period"];
} elseif (isset($_GET["Period"])){
	$SelectedPeriod = $_GET["Period"];
}

if (isset($_GET['Show'])){
	$_POST["Show"]="Show Account Transactions";
}


if (!isset($SelectedAccount) OR $SelectedAccount==""){
	echo "<P>This page must be called with an account. Click on the link below to select an account to inquire upon.";
	echo "<BR><A HREF='$rootpath/SelectGLAccount.php?" . SID . "'>Select a General Ledger Account</A>";
	exit;
}

echo "<FORM METHOD='POST' ACTION=" . $_SERVER["PHP_SELF"] . "?" . SID . ">";

/*Dates in SQL format for the last day of last month*/
$DefaultPeriodDate = Date ("Y-m-d", Mktime(0,0,0,Date("m"),0,Date("Y")));

/*Show a form to allow input of criteria for TB to show */
echo "<CENTER><TABLE><TR><TD>Account:</TD><TD><INPUT TYPE=TEXT NAME='Account' MAXLENGTH=12 SIZE=12 VALUE='" . $SelectedAccount . "'></TD><TD>For Period:</TD><TD><SELECT Name='Period'>";

$sql = "SELECT PeriodNo, LastDate_In_Period FROM Periods";
$Periods = DB_query($sql,$db);

while ($myrow=DB_fetch_array($Periods,$db)){

	if($myrow["LastDate_In_Period"]==$DefaultPeriodDate AND !isset($SelectedPeriod)){
		echo "<OPTION SELECTED VALUE=" . $myrow["PeriodNo"] . ">" . MonthAndYearFromSQLDate($myrow["LastDate_In_Period"]);
	} elseif ($myrow["PeriodNo"]==$SelectedPeriod) {
		echo "<OPTION SELECTED VALUE=" . $myrow["PeriodNo"] . ">" . MonthAndYearFromSQLDate($myrow["LastDate_In_Period"]);
	} else {
		echo "<OPTION VALUE=" . $myrow["PeriodNo"] . ">" . MonthAndYearFromSQLDate($myrow["LastDate_In_Period"]);
	}
}

echo "</SELECT></TD></TR></TABLE><P><INPUT TYPE=SUBMIT NAME='Show' VALUE='Show Account Transactions'></CENTER></FORM>";
$sql = "SELECT AccountName FROM ChartMaster WHERE ChartMaster.AccountCode=$SelectedAccount";
$ActNameResult = DB_query($sql,$db);
$ActName = DB_fetch_row($ActNameResult);
echo "<P><CENTER><FONT SIZE=4 COLOR=BLUE>" . $ActName[0] . "</FONT></CENTER>";

/* End of the Form  rest of script is what happens if the show button is hit*/


if ($_POST["Show"]=="Show Account Transactions"){


/*First off get the ChartDetails balance b/fwd and the actual movement in the account for the period as recorded */

	$sql = "SELECT BFwd, Actual FROM ChartDetails WHERE ChartDetails.AccountCode=$SelectedAccount AND ChartDetails.Period=" . $SelectedPeriod;

	$ChartDetailsResult = DB_query($sql,$db);
	if (DB_error_no($db) !=0) {
		echo "<P>The chart details for " . $ActName[0] . " could not be retrieved, the SQL returned an error - " . DB_error_msg($db);
		if ($debug==1){
			echo "<BR>$sql";
		}
		exit;
	}

/*Now get the transactions for the period */

	$sql = "SELECT Type, TypeName, GLTrans.TypeNo, TranDate, Narrative, Amount FROM GLTrans, SysTypes WHERE GLTrans.Account = $SelectedAccount AND SysTypes.TypeID=GLTrans.Type AND Posted=1 AND PeriodNo=" . $SelectedPeriod;
	$TransResult = DB_query($sql,$db);

	if (DB_error_no($db) !=0) {
		echo "<P>The transactions for " . $SelectedAccount . " could not be retrieved, the SQL returned an error because - " . DB_error_msg($db);
		if ($debug==1){
			echo "<BR>$sql";
		}
		exit;
	}

	if (DB_num_rows($TransResult)==0){
		echo "<P>No general ledger transactions have been created for account number " . $SelectedAccount;
		exit;
	}

	/*show a table of the transactions returned by the SQL */

	echo "<CENTER><TABLE CELLPADDING=2>";

	$TableHeader = "<TR><TD class='tableheader'>Type</TD><TD class='tableheader'>Number</TD><TD class='tableheader'>Date</TD><TD class='tableheader'>Debit</TD><TD class='tableheader'>Credit</TD><TD class='tableheader'>Narrative</TD></TR>";

	echo $TableHeader;

	echo "<TR bgcolor='#FDFEEF'><TD COLSPAN=3><B>Brought Forward</B></TD><TD ALIGN=RIGHT><B>";
	$BfwdRow = DB_fetch_array($ChartDetailsResult);
	$BFwd = $BfwdRow["BFwd"];
	$Actual = $BfwdRow["Actual"];

	if ( $BFwd >=0){
		echo number_format($BFwd,2) . "</B></TD><TD COLSPAN=2></TD></TR>";
	} else {
		echo "</TD><TD ALIGN=RIGHT><B>" . number_format($BFwd,2) . "</B></TD><TD></TD></TR>";
	}

	$RunningTotal =0;
	$j = 1;
	$k=0; //row colour counter
	while ($myrow=DB_fetch_array($TransResult)) {
		if ($k==1){
			echo "<tr bgcolor='#CCCCCC'>";
			$k=0;
		} else {
			echo "<tr bgcolor='#EEEEEE'>";
			$k++;
		}

		$RunningTotal += $myrow["Amount"];

		if($myrow["Amount"]>=0){
			$DebitAmount = number_format($myrow["Amount"],2);
			$CreditAmount = "";
		} else {
			$CreditAmount = number_format(-$myrow["Amount"],2);
			$DebitAmount = "";
		}
		$FormatedTranDate = ConvertSQLDate($myrow["TranDate"]);
		$URL_to_TransDetail = "$rootpath/GLTransInquiry.php?" . SID . "TypeID=" . $myrow["Type"] . "&TransNo=" . $myrow["TypeNo"];
		printf("<td>%s</td><td><A HREF='%s'>%s</A></td><td>%s</td><td ALIGN=RIGHT>%s</td><td ALIGN=RIGHT>%s</td><td>%s</td></tr>", $myrow["TypeName"],$URL_to_TransDetail, $myrow["TypeNo"],$FormatedTranDate, $DebitAmount, $CreditAmount, $myrow['Narrative']);

		$j++;
		If ($j == 18){
			$j=1;
			echo $TableHeader;
		}
	}
	//end of while loop

	echo "<TR bgcolor='#FDFEEF'><TD COLSPAN=3><B>Carried Forward</B></TD><TD ALIGN=RIGHT><B>";
	if ( ($BFwd+$RunningTotal) >=0){
		echo number_format(($BFwd+$RunningTotal),2) . "</B></TD><TD COLSPAN=2></TD></TR>";
	} else {
		echo "</TD><TD ALIGN=RIGHT><B>" . number_format(-($BFwd+$RunningTotal),2) . "</B></TD><TD></TD></TR>";
	}
	echo "</TABLE></CENTER>";

	if (ABS($RunningTotal - $Actual)>0.009) {
		echo "<P>There is a data inconsistency here the recorded movement for the month was " . $Actual . " but the sum of the transactions for the period = " . $RunningTotal;
		echo "<P>The difference is " . ($RunningTotal-$Actual);
	} else {
		echo "<P>Movement as per Actual in chart details = " . $Actual;
		echo "<BR>Movement for the month per listing above = " . $RunningTotal;
	}
} /* end of if Show button hit */

include("includes/footer.inc");

?>

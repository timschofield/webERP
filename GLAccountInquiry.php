<?php
/* $Revision: 1.5 $ */
/*As modified by Dirk Eversmann */

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

echo "<FORM METHOD='POST' ACTION=" . $_SERVER["PHP_SELF"] . "?" . SID . ">";

/*Dates in SQL format for the last day of last month*/
$DefaultPeriodDate = Date ("Y-m-d", Mktime(0,0,0,Date("m"),0,Date("Y")));

/*Show a form to allow input of criteria for TB to show */
echo "<CENTER><TABLE>
        <TR>
         <TD>Account:</TD>
         <TD><SELECT Name='Account'>";
         $sql = "SELECT AccountCode, AccountName FROM ChartMaster ORDER BY AccountCode";
         $Account = DB_query($sql,$db);
         while ($myrow=DB_fetch_array($Account,$db)){
            if($myrow["AccountCode"] == $SelectedAccount){
   	        echo "<OPTION SELECTED VALUE=" . $myrow["AccountCode"] . ">" . $myrow["AccountCode"] . " " . $myrow["AccountName"];
	    } else {
		echo "<OPTION VALUE=" . $myrow["AccountCode"] . ">" . $myrow["AccountCode"] . " " . $myrow["AccountName"];
	    }
         }
         echo "</SELECT></TD></TR>
         <TR>
         <TD>For Period range:</TD>
         <TD><SELECT Name=Period[] multiple>";
	 $sql = "SELECT PeriodNo, LastDate_In_Period FROM Periods";
	 $Periods = DB_query($sql,$db);
         $id=0;
         while ($myrow=DB_fetch_array($Periods,$db)){

            if($myrow["PeriodNo"] == $SelectedPeriod[$id]){
              echo "<OPTION SELECTED VALUE=" . $myrow["PeriodNo"] . ">" . MonthAndYearFromSQLDate($myrow["LastDate_In_Period"]);
            $id++;
            } else {
              echo "<OPTION VALUE=" . $myrow["PeriodNo"] . ">" . MonthAndYearFromSQLDate($myrow["LastDate_In_Period"]);
            }

         }
         echo "</SELECT></TD>
        </TR>
</TABLE><P>
<INPUT TYPE=SUBMIT NAME='Show' VALUE='Show Account Transactions'></CENTER></FORM>";

/* End of the Form  rest of script is what happens if the show button is hit*/

if ($_POST["Show"]=="Show Account Transactions"){

	/*Is the account a balance sheet or a profit and loss account */
	$result = DB_query("SELECT PandL FROM AccountGroups INNER JOIN ChartMaster ON AccountGroups.GroupName=ChartMaster.Group_ WHERE ChartMaster.AccountCode=$SelectedAccount",$db);
	$PandLRow = DB_fetch_row($result);
	if ($PandLRow[0]==1){
		$PandLAccount = True;
	}else{
		$PandLAccount = False; /*its a balance sheet account */
	}

	$FirstPeriodSelected = min($SelectedPeriod);
	$LastPeriodSelected = max($SelectedPeriod);

 	$sql= "SELECT Type, TypeName, GLTrans.TypeNo, TranDate, Narrative, Amount, PeriodNo
		FROM GLTrans, SysTypes
		WHERE GLTrans.Account = $SelectedAccount
		AND SysTypes.TypeID=GLTrans.Type
		AND Posted=1
		AND PeriodNo>=$FirstPeriodSelected
		AND PeriodNo<=$LastPeriodSelected
		ORDER BY PeriodNo, TranDate";

	$TransResult = DB_query($sql,$db);
	if (DB_error_no($db) !=0) {
		echo "<P>The transactions for account " . $SelectedAccount . " could not be retrieved.";
		if ($debug==1){
			echo "The SQL that failed was:<BR>$sql<BR><BR>The error returned was - " . DB_error_msg($db);
		}
		include ("includes/footer.inc");
		exit;
	}
/*Get the ChartDetails balance b/fwd and the actual movement in the account for the period as recorded in the chart details - need to ensure integrity of transactions to the chart detail movements. Also, for a balance sheet account it is the balance carried forward that is important, not just the transactions*/

	$sql = "SELECT BFwd, Actual FROM ChartDetails WHERE ChartDetails.AccountCode=$SelectedAccount AND ChartDetails.Period>=" . $FirstPeriodSelected . " AND ChartDetails.Period<=" . $LastPeriodSelected;

	$ChartDetailsResult = DB_query($sql,$db);
	if (DB_error_no($db) !=0) {
		echo "<P>The chart details for account " . $SelectedAccount . " could not be retrieved.";
		if ($debug==1){
			echo "The SQL that failed was:<BR>$sql<BR><BR>The error returned was - " . DB_error_msg($db);
		}
		include ("includes/footer.inc");
		exit;
	}
	$ChartDetailRow = DB_fetch_array($ChartDetailsResult);

	echo "<table>\n";

	$TableHeader = "<TR>
			<TD class='tableheader'>Type</TD>
			<TD class='tableheader'>Number</TD>
			<TD class='tableheader'>Date</TD>
			<TD class='tableheader'>Debit</TD>
			<TD class='tableheader'>Credit</TD>
			<TD class='tableheader'>Narrative</TD>
			</TR>";

	echo $TableHeader;

	if ($PandLAccount==True) {
		$RunningTotal = 0;
	} else {
		$RunningTotal =$ChartDetailRow['BFwd'];
		if ($RunningTotal < 0 ){ //its a credit balance b/fwd
			echo "<TR bgcolor='#FDFEEF'><TD COLSPAN=3><B>Brought Forward Balance</B><TD></TD></TD><TD ALIGN=RIGHT><B>" . number_format(-$RunningTotal,2) . "</B></TD><TD></TD></TR>";
		} else { //its a debit balance b/fwd
			echo "<TR bgcolor='#FDFEEF'><TD COLSPAN=3><B>Brought Forward Balance</B></TD><TD ALIGN=RIGHT><B>" . number_format($RunningTotal,2) . "</B></TD><TD COLSPAN=2></TD></TR>";
		}
	}
	$PeriodTotal = 0;
	$PeriodNo = -9999;
	$ShowIntegrityReport = False;
	$j = 1;
	$k=0; //row colour counter

	while ($myrow=DB_fetch_array($TransResult)) {

		if ($myrow['PeriodNo']!=$PeriodNo){
			If ($PeriodNo!=-9999){
				echo "<TR bgcolor='#FDFEEF'><TD COLSPAN=3><B>Total for period $PeriodNo</B></TD>";
				if ($PeriodTotal < 0 ){ //its a credit balance b/fwd
					echo "<TD></TD><TD ALIGN=RIGHT><B>" . number_format(-$PeriodTotal,2) . "</B></TD><TD></TD></TR>";
				} else { //its a debit balance b/fwd
					echo "<TD ALIGN=RIGHT><B>" . number_format($PeriodTotal,2) . "</B></TD><TD COLSPAN=2></TD></TR>";
				}
				$IntegrityReport .= "<BR>Period: $PeriodNo Account movement Per Transactions: " . number_format($PeriodTotal,2) . " Movement Per ChartDetails record: " . number_format($ChartDetailRow['Actual'],2) . " Period difference: " . number_format($PeriodTotal -$ChartDetailRow['Actual'],3);
				if (ABS($PeriodTotal -$ChartDetailRow['Actual'])>0.009){
					$ShowIntegrityReport = True;
				}
				$ChartDetailRow = DB_fetch_array($ChartDetailsResult);
			}
			$PeriodNo = $myrow['PeriodNo'];
			$PeriodTotal = 0;
		}

		if ($k==1){
			echo "<tr bgcolor='#CCCCCC'>";
			$k=0;
		} else {
			echo "<tr bgcolor='#EEEEEE'>";
			$k++;
		}

		$RunningTotal += $myrow["Amount"];
		$PeriodTotal += $myrow["Amount"];

		if($myrow["Amount"]>=0){
			$DebitAmount = number_format($myrow["Amount"],2);
			$CreditAmount = "";
		} else {
			$CreditAmount = number_format(-$myrow["Amount"],2);
			$DebitAmount = "";
		}

		$FormatedTranDate = ConvertSQLDate($myrow["TranDate"]);
		$URL_to_TransDetail = "$rootpath/GLTransInquiry.php?" . SID . "TypeID=" . $myrow["Type"] . "&TransNo=" . $myrow["TypeNo"];

		printf("<td>%s</td>
			<td><A HREF='%s'>%s</A></td>
			<td>%s</td>
			<td ALIGN=RIGHT>%s</td>
			<td ALIGN=RIGHT>%s</td>
			<td>%s</td>
			</tr>",
			$myrow["TypeName"],
			$URL_to_TransDetail,
			$myrow["TypeNo"],
			$FormatedTranDate,
			$DebitAmount,
			$CreditAmount,
			$myrow['Narrative']);

		$j++;

		If ($j == 18){
			echo $TableHeader;
			$j=1;
		}
	}

	echo "<TR bgcolor='#FDFEEF'><TD COLSPAN=3><B>";
	if ($PandLAccount==True){
		echo "Total Period Movement";
	} else { /*its a balance sheet account*/
		echo "Balance C/Fwd";
	}
	echo "</B></TD>";

	if ($RunningTotal >0){
		echo "<TD ALIGN=RIGHT><B>" . number_format(($RunningTotal),2) . "</B></TD><TD COLSPAN=2></TD></TR>";
	}else {
		echo "<TD></TD><TD ALIGN=RIGHT><B>" . number_format((-$RunningTotal),2) . "</B></TD><TD COLSPAN=2></TD></TR>";
	}
	echo "</table>\n";
} /* end of if Show button hit */



if ($ShowIntegrityReport){

	echo "<BR>WARNING: There are differences between the sum of the transactions and the recorded movements in the ChartDetails table. A log of the account differences for the periods report shows below:<P>$IntegrityReport";

}
include("includes/footer.inc");
?>

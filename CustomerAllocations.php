<?php
/* $Revision: 1.2 $ */
/*This page can be called with

1. A DebtorTrans ID

The page will then show potential allocations for the transaction called with,
this page can be called from the customer enquiry to show the make up and to modify
existing allocations

2. A DebtorNo

The page will show all outstanding receipts or credits yet to be allocated

3. No parameters

The page will show all outstanding credits and receipts yet to be
allocated */
include("includes/DefineCustAllocsClass.php");

$title = "Customer Receipt/Credit Note Allocations ";

$PageSecurity = 3;

include("includes/session.inc");
include("includes/header.inc");
include("includes/DateFunctions.inc");
include("includes/SQL_CommonFunctions.inc");

if ($_POST['UpdateDatabase']=="Process Allocations" OR $_POST['RefreshAllocTotal']=='Recalculate Total To Allocate') {

	if (!isset($_SESSION['Alloc'])){
		echo "<BR>Allocations can not be processed again. If you hit refresh on this page after having just processed an allocation, try to use the navigation links provided rather than the back button, to avoid this message in future.";
		exit;
	}
	//initialise no input errors assumed initially before we test
	$InputError = 0;

	/*1st off run through and update the array with the amounts allocated
	This works because the form has an input field called the value of
	AllocnItm->ID for each record of the array - and PHP sets the value of
	the form variable on a post*/

	$TotalAllocated=0;
	$TotalDiffOnExch=0;

	for ($AllocCounter=0;$AllocCounter < $_POST["TotalNumberOfAllocs"]; $AllocCounter++){

		if (isset($_POST['Amt' . $AllocCounter])){ // only do the below for allocatable charge amounts
						// there will be no Amtxx field for credits/receipt so skip them
			if (!is_numeric($_POST['Amt' . $AllocCounter])){
			$_POST['Amt' . $AllocCounter]=0;
			}
			if ($_POST['Amt' . $AllocCounter]<0){
			echo "<BR>The entry for the amount to allocate was negative. A positive allocation amount is expected.";
				$_POST['Amt' . $AllocCounter]=0;
			}

			if ($_POST['All' . $AllocCounter]==True){
				$_POST['Amt' . $AllocCounter] = $_POST['YetToAlloc' . $AllocCounter];

			}
			/*Now check to see that the AllocAmt is no greater than the
			amount left to be allocated against the transaction under review */
			if ($_POST['Amt' . $AllocCounter] > $_POST['YetToAlloc' . $AllocCounter]){
			$_POST['Amt' . $AllocCounter]=$_POST['YetToAlloc' . $AllocCounter];
			}


			$_SESSION['Alloc']->Allocs[$_POST['AllocID' . $AllocCounter]]->AllocAmt = $_POST['Amt' . $AllocCounter];
			/*recalcuate the new difference on exchange
			(a +positive amount is a gain -ve a loss)*/

			$_SESSION['Alloc']->Allocs[$_POST['AllocID' . $AllocCounter]]->DiffOnExch =  ($_POST['Amt' . $AllocCounter] / $_SESSION['Alloc']->TransExRate) - ($_POST['Amt' . $AllocCounter] / $_SESSION['Alloc']->Allocs[$_POST['AllocID' . $AllocCounter]]->ExRate);

			$TotalDiffOnExch = $TotalDiffOnExch + $_SESSION['Alloc']->Allocs[$_POST['AllocID' . $AllocCounter]]->DiffOnExch;
			$TotalAllocated = $TotalAllocated + $_POST['Amt' . $AllocCounter];
		} // only do the above for allocatable charge amounts

	} /*end of the loop to set the new allocation amounts,
	recalc diff on exchange and add up total allocations */

	If ($TotalAllocated + $_SESSION['Alloc']->TransAmt >0.008){
	   echo "<BR><HR><B>ERROR: </B>These allocations cannot be processed because the amount allocated is more than the amount of the " . $_SESSION['Alloc']->TransTypeName  . " being allocated";
	   echo "<BR>Total allocated = " . $TotalAllocated . " and the total amount of the " . $_SESSION['Alloc']->TransTypeName  . " was " . -$_SESSION['Alloc']->TransAmt . "<BR><HR>";
	   $InputError=1;
	}
}

if ($_POST['UpdateDatabase']=="Process Allocations"){

	If ($InputError==0){ /* ie all the traps were passed */
	/* actions to take having checked that the input is sensible
	1st set up a transaction on this thread*/
		$SQL = "Begin";
		$Result=DB_query($SQL,$db);

		foreach ($_SESSION['Alloc']->Allocs as $AllocnItem) {

			  if ($AllocnItem->OrigAlloc >0 AND ($AllocnItem->OrigAlloc != $AllocnItem->AllocAmt)){
			  /*Orignial allocation was not 0 and it has now changed
			    need to delete the old allocation record */

				     $SQL = "DELETE FROM CustAllocns WHERE ID = " . $AllocnItem->PrevAllocRecordID;
				     $Result=DB_query($SQL,$db);
				     if (DB_error_no($db) !=0){
					    echo "<BR>CRITICAL ERROR! NOTE DOWN THIS ERROR AND SEEK ASSISTANCE: The existing allocation for $AllocnItem->TransType $AllocnItem->TypeNo  could not be deleted because: -<BR>" . DB_error_msg($db);

					    if ($debug==1){
						    echo "<BR>The following SQL to delete the allocation record was used:<BR>$SQL<BR>";
					    }
					    $SQL = "rollback";
					    $Result = DB_query($SQL,$db);
					    exit;
				      }
			 }
			 if ($AllocnItem->OrigAlloc != $AllocnItem->AllocAmt){
			 /*Only when there has been a change to the allocated amount
			 do we need to insert a new allocation record and update
			 the transaction with the new alloc amount and diff on exch */
				     if ($AllocnItem->AllocAmt >0){
					     $SQL = "INSERT INTO CustAllocns (DateAlloc, Amt, TransID_AllocFrom, TransID_AllocTo) VALUES ('" . FormatDateForSQL(date("d/m/Y")) . "', " . $AllocnItem->AllocAmt . ", " . $_SESSION['Alloc']->AllocTrans . ", " . $AllocnItem->ID . ")";
					     $Result=DB_query($SQL,$db);
					     if (DB_error_no($db) !=0){
						  echo "<BR>CRITICAL ERROR! NOTE DOWN THIS ERROR AND SEEK ASSISTANCE: The customer allocation record for $AllocnItem->TransType $AllocnItem->TypeNo could not be inserted because: -<BR>" . DB_error_msg($db);

						  if ($debug==1){
							echo "<BR>The following SQL to insert the allocation record was used:<BR>$SQL<BR>";
						  }
						  $SQL = "rollback";
						  $Result = DB_query($SQL,$db);
						  exit;
					      }
				     }
				     $NewAllocTotal = $AllocnItem->PrevAlloc + $AllocnItem->AllocAmt;

				     if (abs($NewAllocTotal-$AllocnItem->TransAmount) < 0.005){
					     $Settled =1;
				     } else {
					     $Settled =0;
				     }

				     $SQL = "UPDATE DebtorTrans SET DiffOnExch=" . $AllocnItem->DiffOnExch . ", Alloc = " . $NewAllocTotal . ", Settled = " . $Settled . " WHERE ID = " . $AllocnItem->ID;
				     $Result=DB_query($SQL,$db);
				     if (DB_error_no($db) !=0){
					    echo "<BR>CRITICAL ERROR! NOTE DOWN THIS ERROR AND SEEK ASSISTANCE: The debtor transaction record could not be modified for the allocation against it because: -<BR>" . DB_error_msg($db);
    					    if ($debug==1){
						    echo "<BR>The following SQL to update the debtor transaction record was used:<BR>$SQL<BR>";
					    }

					    $SQL = "rollback";
					    $Result = DB_query($SQL,$db);

					    exit;
				      }
			 } /*end if the new allocation is different to what it was before */

		}  /*end of the loop through the array of allocations made */

		/*Now update the receipt or credit note with the amount allocated
		and the new diff on exchange */


		if (abs($TotalAllocated+$_SESSION['Alloc']->TransAmt)<0.01){
		   $Settled = 1;
		} else {
		   $Settled = 0;
		}

		$SQL = "UPDATE DebtorTrans SET Alloc = " .  -$TotalAllocated . ", DiffOnExch = " . -$TotalDiffOnExch . ", Settled=" . $Settled . " WHERE ID = " . $_POST['AllocTrans'];

		$Result=DB_query($SQL,$db);

		if (DB_error_no($db) !=0){
			echo "<BR>CRITICAL ERROR! NOTE DOWN THIS ERROR AND SEEK ASSISTANCE: The debtor receipt or credit note transaction could not be modified for the new allocation and exchange difference because: -<BR>" . DB_error_msg($db);
			if ($debug==1){
				echo "<BR>The following SQL to update the receipt or credit note was used:<BR>$SQL<BR>";
			}
			$SQL = "rollback";
			$Result = DB_query($SQL,$db);

			exit;
		}

		/*Almost there ... if there is a change in the total diff on exchange
		 and if the GLLink to debtors is active - need to post diff on exchange to GL */
		$MovtInDiffOnExch = -$_SESSION['Alloc']->PrevDiffOnExch - $TotalDiffOnExch;
		if ($MovtInDiffOnExch !=0){

		   $Coy = ReadInCompanyRecord($db);
		   if ($Coy["GLLink_Debtors"]==1){

		      $PeriodNo = GetPeriod($_SESSION['Alloc']->TransDate, $db);
		      $_SESSION['Alloc']->TransDate = FormatDateForSQL($_SESSION['Alloc']->TransDate);

		      $SQL = "INSERT INTO GLTrans (Type, TypeNo, TranDate, PeriodNo, Account, Narrative, Amount) VALUES (" . $_SESSION['Alloc']->TransType . ", " . $_SESSION['Alloc']->TransNo . ", '" . $_SESSION['Alloc']->TransDate . "', " . $PeriodNo . ", " . $Coy["ExchangeDiffAct"] . ", '', " . $MovtInDiffOnExch . ")";
		      $Result = DB_query($SQL,$db);
		      if (DB_error_no($db) !=0){
			      echo "<BR>CRITICAL ERROR! NOTE DOWN THIS ERROR AND SEEK ASSISTANCE: The GL entry for the difference on exchange arising out of this allocation could not be inserted because: -<BR>" . DB_error_msg($db);
			      if ($debug==1){
					echo "<BR>The following SQL to insert the GLTrans record was used:<BR>$SQL<BR>";
			      }
			      $SQL = "rollback";
			      $Result = DB_query($SQL,$db);
			      exit;
			}

		      $SQL = "INSERT INTO GLTrans (Type, TypeNo, TranDate, PeriodNo, Account, Narrative, Amount) VALUES (" . $_SESSION['Alloc']->TransType . ", " . $_SESSION['Alloc']->TransNo . ", '" . $_SESSION['Alloc']->TransDate . "', " . $PeriodNo . ", " . $Coy["DebtorsAct"] . ", '', " . -$MovtInDiffOnExch . ")";
		      $Result = DB_query($SQL,$db);
		      if (DB_error_no($db) !=0){
			      echo "<BR>CRITICAL ERROR! NOTE DOWN THIS ERROR AND SEEK ASSISTANCE: The GL entry for the difference on exchange arising out of this allocation could not be inserted because: -<BR>" . DB_error_msg($db);
			      if ($debug==1){
					echo "<BR>The following SQL to insert the GLTrans record was used:<BR>$SQL<BR>";
			      }
			      $SQL = "rollback";
			      $Result = DB_query($SQL,$db);
			      exit;
			}
		   }

		}
	 /* OK Commit the transaction */
		$SQL = "Commit";
		$Result = DB_query($SQL,$db);
		if (DB_error_no($db) !=0){
		      echo "<BR>CRITICAL ERROR! NOTE DOWN THIS ERROR AND SEEK ASSISTANCE: The updates and insertions arising from this allocation could not be committed to the database: -<BR>" . DB_error_msg($db);
		      if ($debug==1){
				echo "<BR>The commit SQL failed<BR>";
		      }
		      exit;
		}
	/*finally delete the session variables holding all the previous data */
		unset($_SESSION['Alloc']);
		unset($_POST['AllocTrans']);
	} /* end of processing required if there were no input errors trapped */
}

/*The main logic determines whether the page is called with a customer code
a specific transaction or with no parameters ie else
If with a customer code show just that customer's receipts and credits for allocating
If with a specific receipt or credit show the invoices and credits available
for allocating to  */

If (isset($_GET['AllocTrans'])){

	/*page called with a specific transaction ID for allocating
	DebtorNo may also be set but this is the logic to follow
	the DebtorNo logic is only for showing the receipts and credits to allocate


	So need to set up the session variables and populate from the DB
	subsequent calls do not need this until the page is called with another
	AllocTrans from a link ie a GET
	Calls from a POST assume the SESSION vbles are already set up from the GET*/

	/*The logic is:
	- read in the transaction into a session class variable
	- read in the invoices available for allocating to into a session array of allocs object
	- Display the customer name the transaction being allocated amount and trans no
	- Display the invoices for allocating to with a form entry for each one
	for the allocated amount to be entered */

	Session_register("AllocTrans");
	Session_register("Alloc");

	if (isset($_SESSION['Alloc'])){
		unset($_SESSION['Alloc']->Allocs);
		unset($_SESSION['Alloc']);
	}

	$_SESSION['Alloc'] = new Allocation;
	/*The session varibale AllocTrans is set from the passed variable AllocTrans
	on the first pass */
	$_POST['AllocTrans']=$_GET['AllocTrans'];

	$SQL= "SELECT SysTypes.TypeName, DebtorTrans.Type, DebtorTrans.TransNo, DebtorTrans.TranDate, DebtorTrans.DebtorNo, DebtorsMaster.Name, Rate, (DebtorTrans.OvAmount+DebtorTrans.OvGST+DebtorTrans.OvFreight+DebtorTrans.OvDiscount) AS Total, DebtorTrans.DiffOnExch, DebtorTrans.Alloc FROM DebtorTrans, SysTypes, DebtorsMaster WHERE DebtorTrans.Type = SysTypes.TypeID AND DebtorTrans.DebtorNo = DebtorsMaster.DebtorNo AND DebtorTrans.ID=" . $_POST['AllocTrans'];
	$Result = DB_query($SQL,$db);
	if (DB_num_rows($Result)!=1){
	echo "<BR>There was a problem retrieving the information relating the transaction selected. Allocations are unable to proceed.";
	if ($debug==1){
		echo "<BR>The SQL that was used to retreive the transaction information was :<BR>$SQL";
	}
	exit;
	}

	$myrow = DB_fetch_array($Result);

	$_SESSION['Alloc']->AllocTrans = $_POST['AllocTrans'];
	$_SESSION['Alloc']->DebtorNo = $myrow["DebtorNo"];
	$_SESSION['Alloc']->CustomerName = $myrow["Name"];;
	$_SESSION['Alloc']->TransType = $myrow["Type"];
	$_SESSION['Alloc']->TransTypeName = $myrow["TypeName"];
	$_SESSION['Alloc']->TransNo = $myrow["TransNo"];
	$_SESSION['Alloc']->TransExRate = $myrow["Rate"];
	$_SESSION['Alloc']->TransAmt = $myrow["Total"];
	$_SESSION['Alloc']->PrevDiffOnExch = $myrow["DiffOnExch"];
	$_SESSION['Alloc']->TransDate = ConvertSQLDate($myrow["TranDate"]);

	/* Now populate the array of possible (and previous actual) allocations for this customer */
	/*First get the transactions that have outstanding balances ie Total-Alloc >0 */

	$SQL= "SELECT DebtorTrans.ID, TypeName, TransNo, TranDate, Rate, OvAmount+OvGST+OvFreight+OvDiscount AS Total, DiffOnExch, Alloc FROM DebtorTrans, SysTypes WHERE DebtorTrans.Type = SysTypes.TypeID AND DebtorTrans.Settled=0 AND DebtorNo='" . $_SESSION['Alloc']->DebtorNo . "' ORDER BY DebtorTrans.ID";

	$Result = DB_query($SQL,$db);
	if (DB_error_no($db)!=0){
		echo "<BR>There was a problem retrieving the transactions available to allocate to.";
		if ($debug==1){
			echo "<BR>The SQL that was used to retreive the transaction information was :<BR>$SQL";
		}
		exit;
	}

	while ($myrow=DB_fetch_array($Result)){
		$_SESSION['Alloc']->add_to_AllocsAllocn ($myrow["ID"], $myrow["TypeName"], $myrow["TransNo"], ConvertSQLDate($myrow["TranDate"]), 0, $myrow["Total"], $myrow["Rate"], $myrow["DiffOnExch"], $myrow["DiffOnExch"], $myrow["Alloc"], "NA");
	}

	/* Now get trans that might have previously been allocated to by this trans
	NB existing entries where still some of the trans outstanding entered from
	above logic will be overwritten with the prev alloc detail below */

	$SQL= "SELECT DebtorTrans.ID, TypeName, TransNo, TranDate, Rate, OvAmount+OvGST+OvFreight+OvDiscount AS Total, DiffOnExch, DebtorTrans.Alloc-CustAllocns.Amt AS PrevAllocs, Amt, CustAllocns.ID AS AllocID FROM DebtorTrans, SysTypes, CustAllocns WHERE DebtorTrans.Type = SysTypes.TypeID AND DebtorTrans.ID=CustAllocns.TransID_AllocTo AND CustAllocns.TransID_AllocFrom=" . $_POST['AllocTrans'] . " AND DebtorNo='" . $_SESSION['Alloc']->DebtorNo . "' ORDER BY DebtorTrans.ID";

	$Result = DB_query($SQL,$db);

	if (DB_error_no($db)!=0){
		echo "<BR>There was a problem retrieving the previously allocated transactions for modification.";
	if ($debug==1){
		echo "<BR>The SQL that was used to retreive the previously allocated transaction information was :<BR>$SQL";
	}
		exit;
	}

	while ($myrow=DB_fetch_array($Result)){

		$DiffOnExchThisOne = ($myrow["Amt"]/$myrow["Rate"]) - ($myrow["Amt"]/$_SESSION['Alloc']->TransExRate);

		$_SESSION['Alloc']->add_to_AllocsAllocn ($myrow["ID"], $myrow["TypeName"], $myrow["TransNo"], ConvertSQLDate($myrow["TranDate"]), $myrow["Amt"], $myrow["Total"], $myrow["Rate"], $DiffOnExchThisOne, ($myrow["DiffOnExch"] - $DiffOnExchThisOne), $myrow["PrevAllocs"], $myrow["AllocID"]);

	}
}

if (isset($_POST['AllocTrans'])){

	echo "<FORM ACTION='" . $_SERVER['PHP_SELF'] . "?" . SID . "' METHOD=POST>";
	echo "<INPUT TYPE=HIDDEN NAME='AllocTrans' VALUE=" . $_POST['AllocTrans'] . ">";

	/*Show the transaction being allocated and the potential trans it could be allocated to
        and those where there is already an existing allocation */

        echo "<HR><CENTER><FONT COLOR=BLUE>Allocation of customer " . $_SESSION['Alloc']->TransTypeName . " number " . $_SESSION['Alloc']->TransNo . " from " . $_SESSION['Alloc']->DebtorNo . " - <B>" . $_SESSION['Alloc']->CustomerName . "</B>, dated " . $_SESSION['Alloc']->TransDate;

        if ($_SESSION['Alloc']->TransExRate!=1){
	     echo "<BR>Amount in customer's currency <B>" . number_format(-$_SESSION['Alloc']->TransAmt,2) . "</B><i> (converted into local currency at an exchange rate of " . $_SESSION['Alloc']->TransExRate . ")</i><P>";
        } else {
	     echo "<BR>Transaction total: <B>" . -$_SESSION['Alloc']->TransAmt . "</B>";
        }

        echo "<HR>";
   /*Now display the potential and existing allocations put into the array above */

   	$TableHeader = "<TR><TD class='tableheader'>Type</TD><TD class='tableheader'>Trans<BR>Number</TD><TD class='tableheader'>Trans<BR>Date</TD><TD class='tableheader'>Total<BR>Amount</TD><TD class='tableheader'>Yet to<BR>Allocate</TD><TD class='tableheader'>This<BR>Allocation</TD></TR>";

        echo "<TABLE CELLPADDING=2 COLSPAN=7 BORDER=0>" . $TableHeader;

        $k=0;
        $RowCounter =0;
	$Counter = 0;
        $TotalAllocated =0;
        foreach ($_SESSION['Alloc']->Allocs as $AllocnItem) {
	    /*Alternate the background colour for each potential allocation line */
	    if ($k==1){
		  echo "<tr bgcolor='#CCCCCC'>";
		  $k=0;
	    } else {
		  echo "<tr bgcolor='#EEEEEE'>";
		  $k=1;
	    }
	    $RowCounter++;
	    if ($RowCounter==15){
		/*Set up another row of headings to ensure always a heading on the screen of potential allocns*/
		echo $TableHeader;
		$RowCounter=1;
	    }
	    $YetToAlloc = ($AllocnItem->TransAmount - $AllocnItem->PrevAlloc);

	    echo "<TD>$AllocnItem->TransType</TD><TD>$AllocnItem->TypeNo</TD><TD ALIGN=RIGHT>$AllocnItem->TransDate</TD><TD ALIGN=RIGHT>" . number_format($AllocnItem->TransAmount,2) . "</TD><TD ALIGN=RIGHT>" . number_format($YetToAlloc,2);

	    if ($AllocnItem->TransAmount < 0) {
	    	echo "</TD></TR>";
	    } else {
	    	echo "<input type=hidden name='YetToAlloc" . $Counter . "' value=" . $YetToAlloc . "></TD>";
	    	echo "<TD ALIGN=RIGHT><input type='checkbox' name='All" .  $Counter . "'";
	    	if (ABS($AllocnItem->AllocAmt-$YetToAlloc)<0.01){
			echo " VALUE=" . True . ">";
	    	} else {
	    		echo ">";
	    	}
	    	echo "<input type=text name='Amt" . $Counter ."' maxlength=12 SIZE=13 value=" . $AllocnItem->AllocAmt . "><input type=hidden name='AllocID" . $Counter . "' value=" . $AllocnItem->ID . "></TD></TR>";
	    }

	    $TotalAllocated =$TotalAllocated + $AllocnItem->AllocAmt;

	    $Counter++;
   }


   echo "<TR><TD COLSPAN=5 ALIGN=RIGHT><B>Total Allocated:</B></TD><TD ALIGN=RIGHT><B><U>" . number_format($TotalAllocated,2) . "</U></B></TD></TR>";

   echo "<TR><TD COLSPAN=5 ALIGN=RIGHT><B>Left to allocate</B></TD><TD ALIGN=RIGHT><B>" . number_format(-$_SESSION['Alloc']->TransAmt - $TotalAllocated,2) . "</B></TD></TR></TABLE>";

   echo "<INPUT TYPE=HIDDEN NAME='TotalNumberOfAllocs' VALUE=$Counter>";

   echo "<INPUT TYPE=SUBMIT NAME='RefreshAllocTotal' VALUE='Recalculate Total To Allocate'>";
   echo " <INPUT TYPE=SUBMIT NAME=UpdateDatabase VALUE='Process Allocations'>";

} elseif(isset($_GET['DebtorNo'])){
  /*page called with customer code  so show the transactions to allocate
  specific to the customer selected */

  /*Clear any previous allocation records */
  unset($_SESSION['Alloc']->Allocs);
  unset($_SESSION['Alloc']);

  $sql = "SELECT ID, TransNo, TypeName, Type, DebtorTrans.DebtorNo, Name, TranDate, Reference, Rate, OvAmount+OvGST-OvDiscount+OvFreight AS Total, Alloc FROM DebtorTrans, DebtorsMaster, SysTypes WHERE DebtorTrans.Type=SysTypes.TypeID AND DebtorTrans.DebtorNo=DebtorsMaster.DebtorNo AND DebtorTrans.DebtorNo='" . $_GET['DebtorNo'] . "' AND (Type=12 or Type=11) AND Settled=0 ORDER BY ID";

  $result = DB_query($sql,$db);
  if (DB_num_rows($result)==0){
      echo "<P>There are no outstanding receipts or credits yet to be allocated for this customer.";
      exit;
  }
  echo "<CENTER><table>";
  echo "<tr><td class='tableheader'>Trans Type</td><td class='tableheader'>Customer</td><td class='tableheader'>Number</td><td class='tableheader'>Date</td><td class='tableheader'>Total</td><td class='tableheader'>To Alloc</td></tr>\n";
  /* set up table of TransType - Customer - Trans No - Date - Total - Left to alloc  */

  $k=0; //row colour counter

  while ($myrow = DB_fetch_array($result)) {
	if ($k==1){
		echo "<tr bgcolor='#CCCCCC'>";
		$k=0;
	} else {
		echo "<tr bgcolor='#EEEEEE'>";
		$k=1;
	}

	printf("<td>%s</td><td>%s</td><td>%s</td><td>%s</td><td ALIGN=RIGHT>%0.2f</td><td ALIGN=RIGHT>%0.2f</td><td><a href='%sAllocTrans=%s'>Allocate</td></tr>", $myrow["TypeName"], $myrow["Name"], $myrow["TransNo"], ConvertSQLDate($myrow["TranDate"]), $myrow["Total"], $myrow["Total"]-$myrow["Alloc"], $_SERVER['PHP_SELF'] . "?" . SID, $myrow["ID"]);
  }
} else { /* show all outstanding receipts and credits to be allocated */
  /*Clear any previous allocation records */
  unset($_SESSION['Alloc']->Allocs);
  unset($_SESSION['Alloc']);

  $sql = "SELECT ID, TransNo, TypeName, Type, DebtorTrans.DebtorNo, Name, TranDate, Reference, Rate, OvAmount+OvGST+OvDiscount+OvFreight AS Total, Alloc FROM DebtorTrans, DebtorsMaster, SysTypes WHERE DebtorTrans.Type=SysTypes.TypeID AND DebtorTrans.DebtorNo=DebtorsMaster.DebtorNo AND (Type=12 or Type=11) AND Settled=0 AND DebtorTrans.OvAmount<0 ORDER BY ID";

  $result = DB_query($sql,$db);

  echo "<CENTER><table>";
  echo "<tr><td class='tableheader'>Trans Type</td><td class='tableheader'>Customer</td><td class='tableheader'>Number</td><td class='tableheader'>Date</td><td class='tableheader'>Total</td><td class='tableheader'>To Alloc</td></tr>\n";
  /* set up table of Tran Type - Customer - Trans No - Date - Total - Left to alloc  */

  $k=0; //row colour counter

  while ($myrow = DB_fetch_array($result)) {
	if ($k==1){
		echo "<tr bgcolor='#CCCCCC'>";
		$k=0;
	} else {
		echo "<tr bgcolor='#EEEEEE'>";
		$k=1;
	}

	printf("<td>%s</td><td>%s</td><td>%s</td><td>%s</td><td ALIGN=RIGHT>%0.2f</td><td ALIGN=RIGHT>%0.2f</td><td><a href='%sAllocTrans=%s'>Allocate</td></tr>", $myrow["TypeName"], $myrow["Name"], $myrow["TransNo"], ConvertSQLDate($myrow["TranDate"]), $myrow["Total"], $myrow["Total"]-$myrow["Alloc"], $_SERVER['PHP_SELF'] . "?" . SID, $myrow["ID"]);

  }
  //END WHILE LIST LOOP
  echo "</table></CENTER>";
  if (DB_num_rows($result) == 0) {
	echo "<BR>There are no allocations to be done";
  }

} /* end of else if not a debtorno or transaction called with the URL */


include("includes/footer.inc");

?>


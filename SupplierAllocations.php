<?php
/*
	This page can be called with...

	1. A SuppTrans TransNo and Type
	The page will then show potential allocations for the transaction called with,
	this page can be called from the supplier enquiry to show the make up and to modify
	existing allocations

	2. A SupplierID
	The page will show all outstanding payments or credits yet to be allocated for the supplier selected

	3. No parameters
	The page will show all outstanding supplier credit notes and payments yet to be
	allocated 
*/

include("includes/DefineSuppAllocsClass.php");

$title = "Supplier Payment/Credit Note Allocations ";

$PageSecurity=5;

include("includes/session.inc");
include("includes/header.inc");
include("includes/DateFunctions.inc");
include("includes/SQL_CommonFunctions.inc");


if ($_POST['UpdateDatabase'] == "Process Allocations" OR $_POST['RefreshAllocTotal']=='Recalculate Total To Allocate') {

	//initialise no input errors assumed initially before we test
	$InputError = 0;

	if (!isset($_SESSION['Alloc'])){
		echo "<BR>Allocations can not be processed again. If you hit refresh on this page after having just processed an allocation, try to use the navigation links provided rather than the back button, to avoid this message in future.";
		exit;
	}

	/*1st off run through and update the array with the amounts allocated
	This works because the form has an input field called the value of
	AllocnItem->ID for each record of the array - and PHP sets the value of
	the form variable on a post*/

	$TotalAllocated=0;
	$TotalDiffOnExch=0;

/*1st off run through and update the array with the amounts allocated
	This works because the form has an input field called the value of
	AllocnItm->ID for each record of the array - and PHP sets the value of
	the form variable on a post*/

	$TotalAllocated=0;
	$TotalDiffOnExch=0;

	for ($AllocCounter=0;$AllocCounter < $_POST["TotalNumberOfAllocs"]; $AllocCounter++){

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

	} /*end of the loop to set the new allocation amounts,
	recalc diff on exchange and add up total allocations */
	If ($TotalAllocated + $_SESSION['Alloc']->TransAmt >0.005){
		echo "<BR><HR><B>ERROR: </B>These allocations cannot be processed because the amount allocated is more than the amount of the " . $_SESSION['Alloc']->TransTypeName  . " being allocated";
		echo "<BR>Total allocated = " . $TotalAllocated . " and the total amount of the Credit/payment was " . -$_SESSION['Alloc']->TransAmt . "<BR><HR>";
		$InputError=1;
	}

}


if ($_POST['UpdateDatabase'] == "Process Allocations"){

	If ($InputError==0){ /* ie all the traps were passed */
	/* actions to take having checked that the input is sensible
	1st set up a transaction on this thread*/
		$SQL = "Begin";
		$Result=DB_query($SQL,$db);

		foreach ($_SESSION['Alloc']->Allocs as $AllocnItem) {

			  if ($AllocnItem->OrigAlloc >0 AND ($AllocnItem->OrigAlloc != $AllocnItem->AllocAmt)){
			  /*Orignial allocation was not 0 and it has now changed
			    need to delete the old allocation record */

				     $SQL = "DELETE FROM SuppAllocs WHERE ID = " . $AllocnItem->PrevAllocRecordID;
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
					     $SQL = "INSERT INTO SuppAllocs (DateAlloc, Amt, TransID_AllocFrom, TransID_AllocTo) VALUES ('" . FormatDateForSQL(date("d/m/Y")) . "', " . $AllocnItem->AllocAmt . ", " . $_SESSION['Alloc']->AllocTrans . ", " . $AllocnItem->ID . ")";
					     $Result=DB_query($SQL,$db);
					     if (DB_error_no($db) !=0){
						  echo "<BR>CRITICAL ERROR! NOTE DOWN THIS ERROR AND SEEK ASSISTANCE: The supplier allocation record for $AllocnItem->TransType $AllocnItem->TypeNo could not be inserted because: -<BR>" . DB_error_msg($db);

						  if ($debug==1){
							echo "<BR>The following SQL to insert the allocation record was used:<BR>$SQL<BR>";
						  }
						  $SQL = "rollback";
						  $Result = DB_query($SQL,$db);
						  exit;
					      }
				     }
				     $NewAllocTotal = $AllocnItem->PrevAlloc + $AllocnItem->AllocAmt;

				     if (abs($NewAllocTotal-$AllocnItem->TransAmount) < 0.01){
					     $Settled =1;
				     } else {
					     $Settled =0;
				     }

				     $SQL = "UPDATE SuppTrans SET DiffOnExch=" . $AllocnItem->DiffOnExch . ", Alloc = " . $NewAllocTotal . ", Settled = " . $Settled . " WHERE ID = " . $AllocnItem->ID;
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

		/*Now update the payment or credit note with the amount allocated
		and the new diff on exchange */

		if (abs($TotalAllocated+$_SESSION['Alloc']->TransAmt)<0.01){
		   $Settled = 1;
		} else {
		   $Settled = 0;
		}

		$SQL = "UPDATE SuppTrans SET Alloc = " .  -$TotalAllocated . ", DiffOnExch = " . -$TotalDiffOnExch . ", Settled=" . $Settled . " WHERE ID = " . $_SESSION['AllocTrans'];
		$Result=DB_query($SQL,$db);
		if (DB_error_no($db) !=0){
			echo "<BR>CRITICAL ERROR! NOTE DOWN THIS ERROR AND SEEK ASSISTANCE: The supplier payment or credit note transaction could not be modified for the new allocation and exchange difference because: -<BR>" . DB_error_msg($db);
			if ($debug==1){
				echo "<BR>The following SQL to update the payment or credit note was used:<BR>$SQL<BR>";
			}
			$SQL = "rollback";
			$Result = DB_query($SQL,$db);

			exit;
		}

		/*Almost there ... if there is a change in the total diff on exchange
		 and if the GLLink to debtors is active - need to post diff on exchange to GL */
		$MovtInDiffOnExch = $_SESSION['Alloc']->PrevDiffOnExch + $TotalDiffOnExch;
		if ($MovtInDiffOnExch !=0){

		   $Coy = ReadInCompanyRecord($db);
		   if ($Coy["GLLink_Debtors"]==1){

		      $PeriodNo = GetPeriod($_SESSION['Alloc']->TransDate, $db);
		      $_SESSION['Alloc']->TransDate = FormatDateForSQL($_SESSION['Alloc']->TransDate);

		      $SQL = "INSERT INTO GLTrans (Type, TypeNo, TranDate, PeriodNo, Account, Narrative, Amount) VALUES (" . $_SESSION['Alloc']->TransType . ", " . $_SESSION['Alloc']->TransNo . ", '" . $_SESSION['Alloc']->TransDate . "', " . $PeriodNo . ", " . $Coy["PurchasesExchangeDiffAct"] . ", 'Exch diff', " . $MovtInDiffOnExch . ")";
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

		      $SQL = "INSERT INTO GLTrans (Type, TypeNo, TranDate, PeriodNo, Account, Narrative, Amount) VALUES (" . $_SESSION['Alloc']->TransType . ", " . $_SESSION['Alloc']->TransNo . ", '" . $_SESSION['Alloc']->TransDate . "', " . $PeriodNo . ", " . $Coy["CreditorsAct"] . ", 'Exchg Diff', " . -$MovtInDiffOnExch . ")";
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
		unset($_SESSION['AllocTrans']);
		unset($_SESSION['Alloc']);
		unset($_POST['AllocTrans']);
	} /* end of processing required if there were no input errors trapped */
}

/*The main logic determines whether the page is called with a Supplier code
a specific transaction or with no parameters ie else
If with a supplier code show just that supplier's payments and credits for allocating
If with a specific payment or credit show the invoices and credits available
for allocating to  */

echo "<FORM ACTION='" . $_SERVER['PHP_SELF'] . "?" . SID . "' METHOD=POST>";

if (isset($_POST["SupplierID"])){
 	$_GET["SupplierID"]=$_POST["SupplierID"];
	echo "<INPUT TYPE='hidden' NAME='SupplierID' VALUE='" . $_POST["SupplierID"] . "'>";
}


If (isset($_GET['AllocTrans'])){
	/*page called with a specific transaction ID for allocating
	SupplierID may also be set but this is the logic to follow
	the SupplierID logic is only for showing the payments and credits to allocate*/


	/*The logic is:
	- read in the transaction into a session class variable
	- read in the invoices available for allocating to into a session array of allocs object
	- Display the supplier name the transaction being allocated amount and trans no
	- Display the invoices for allocating to with a form entry for each one
	for the allocated amount to be entered */



	Session_register("AllocTrans");
	Session_register("Alloc");


	$_SESSION['Alloc'] = new Allocation;
	/*The session varibale AllocTrans is set from the passed variable AllocTrans
	on the first pass */
	$_SESSION['AllocTrans'] = $_GET['AllocTrans'];
	$_POST['AllocTrans'] = $_GET['AllocTrans'];


	$SQL= "SELECT SysTypes.TypeName, SuppTrans.Type, SuppTrans.TransNo, SuppTrans.TranDate, SuppTrans.SupplierNo, Suppliers.SuppName, Rate, (SuppTrans.OvAmount+SuppTrans.OvGST) AS Total, SuppTrans.DiffOnExch, SuppTrans.Alloc FROM SuppTrans, SysTypes, Suppliers WHERE SuppTrans.Type = SysTypes.TypeID AND SuppTrans.SupplierNo = Suppliers.SupplierID AND SuppTrans.ID=" . $_SESSION['AllocTrans'];
	$Result = DB_query($SQL,$db);
	if (DB_num_rows($Result)!=1){
	echo "There was a problem retrieving the information relating the transaction selected. Allocations are unable to proceed.";
	if ($debug==1){
		echo "<BR>The SQL that was used to retreive the transaction information was :<BR>$SQL";
	}
	exit;
	}

	$myrow = DB_fetch_array($Result);

	$_SESSION['Alloc']->AllocTrans = $_SESSION['AllocTrans'];
	$_SESSION['Alloc']->SupplierID = $myrow["SupplierNo"];
	$_SESSION['Alloc']->SuppName = $myrow["SuppName"];;
	$_SESSION['Alloc']->TransType = $myrow["Type"];
	$_SESSION['Alloc']->TransTypeName = $myrow["TypeName"];
	$_SESSION['Alloc']->TransNo = $myrow["TransNo"];
	$_SESSION['Alloc']->TransExRate = $myrow["Rate"];
	$_SESSION['Alloc']->TransAmt = $myrow["Total"];
	$_SESSION['Alloc']->PrevDiffOnExch = $myrow["DiffOnExch"];
	$_SESSION['Alloc']->TransDate = ConvertSQLDate($myrow["TranDate"]);

	/* Now populate the array of possible (and previous actual) allocations for this supplier */
	/*First get the transactions that have outstanding balances ie Total-Alloc >0 */

	$SQL= "SELECT SuppTrans.ID, TypeName, TransNo, TranDate, SuppReference, Rate, OvAmount+OvGST AS Total, DiffOnExch, Alloc FROM SuppTrans, SysTypes WHERE SuppTrans.Type = SysTypes.TypeID AND SuppTrans.Settled=0 AND ABS(OvAmount+OvGST-Alloc)>0.009 AND SupplierNo='" . $_SESSION['Alloc']->SupplierID . "'";
	$Result = DB_query($SQL,$db);
	if (DB_error_no($db)!=0){
	echo "<BR>There was a problem retrieving the transactions available to allocate to.";
	if ($debug==1){
		echo "<BR>The SQL that was used to retreive the transaction information was :<BR>$SQL";
	}
	exit;
	}
	while ($myrow=DB_fetch_array($Result)){
		$_SESSION['Alloc']->add_to_AllocsAllocn ($myrow["ID"], $myrow["TypeName"], $myrow["TransNo"], ConvertSQLDate($myrow["TranDate"]), $myrow["SuppReference"], 0, $myrow["Total"], $myrow["Rate"], $myrow["DiffOnExch"], $myrow["DiffOnExch"], $myrow["Alloc"], "NA");
	}

	/* Now get trans that might have previously been allocated to by this trans
	NB existing entries where still some of the trans outstanding entered from
	above logic will be overwritten with the prev alloc detail below */

	$SQL= "SELECT SuppTrans.ID, TypeName, TransNo, TranDate, SuppReference, Rate, OvAmount+OvGST AS Total, DiffOnExch, SuppTrans.Alloc-SuppAllocs.Amt AS PrevAllocs, Amt, SuppAllocs.ID AS AllocID FROM SuppTrans, SysTypes, SuppAllocs WHERE SuppTrans.Type = SysTypes.TypeID AND SuppTrans.ID=SuppAllocs.TransID_AllocTo AND SuppAllocs.TransID_AllocFrom=" . $_SESSION['AllocTrans'] . " AND SupplierNo='" . $_SESSION['Alloc']->SupplierID . "'";

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
		$_SESSION['Alloc']->add_to_AllocsAllocn ($myrow["ID"], $myrow["TypeName"], $myrow["TransNo"], ConvertSQLDate($myrow["TranDate"]), $myrow["SuppReference"], $myrow["Amt"], $myrow["Total"], $myrow["Rate"], $DiffOnExchThisOne, ($myrow["DiffOnExch"] - $DiffOnExchThisOne), $myrow["PrevAllocs"], $myrow["AllocID"]);
	}
}

if (isset($_POST['AllocTrans'])){

	echo "<INPUT TYPE='hidden' NAME='AllocTrans' VALUE='" . $_POST["AllocTrans"] . "'>";
	/*Show the transaction being allocated and the potential trans it could be allocated to
        and those where there is already an existing allocation */

        echo "<HR><CENTER><FONT COLOR=BLUE>Allocation of supplier " . $_SESSION['Alloc']->TransTypeName . " number " . $_SESSION['Alloc']->TransNo . " from " . $_SESSION['Alloc']->SupplierID . " - <B>" . $_SESSION['Alloc']->SuppName . "</B>, dated " . $_SESSION['Alloc']->TransDate;

        if ($_SESSION['Alloc']->TransExRate!=1){
	     echo "<BR>Amount in supplier's currency <B>" . number_format(-$_SESSION['Alloc']->TransAmt,2) . "</B><i> (converted into local currency at an exchange rate of " . $_SESSION['Alloc']->TransExRate . ")</i><P>";
        } else {
	     echo "<BR>Transaction total: <B>" . -$_SESSION['Alloc']->TransAmt . "</B>";
        }

        echo "<HR>";
   /*Now display the potential and existing allocations put into the array above */

        echo "<TABLE CELLPADDING=2 COLSPAN=7 BORDER=0>";
	$TableHeader = "<TR><TD class='tableheader'>Type</TD><TD class='tableheader'>Trans<BR>Number</TD><TD class='tableheader'>Trans<BR>Date</TD><TD class='tableheader'>Supp<BR>Ref</TD><TD class='tableheader'>Total<BR>Amount</TD><TD class='tableheader'>Yet to<BR>Allocate</TD><TD class='tableheader'>This<BR>Allocation</TD></TR>";
        $k=0;
	$Counter=0;
	$RowCounter =0;
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
	    echo "<TD>$AllocnItem->TransType</TD><TD>$AllocnItem->TypeNo</TD><TD>$AllocnItem->TransDate</TD><TD>$AllocnItem->SuppRef</TD><TD ALIGN=RIGHT>" . number_format($AllocnItem->TransAmount,2) . "</TD><TD ALIGN=RIGHT>" . number_format($YetToAlloc,2) . "<input type=hidden name='YetToAlloc" . $Counter . "' value=" . $YetToAlloc . "></TD>";
	    echo "<TD ALIGN=RIGHT><input type='checkbox' name='All" .  $Counter . "'";
	    if (ABS($AllocnItem->AllocAmt-$YetToAlloc)<0.01){
		echo " VALUE=" . True . ">";
	    } else {
	    	echo ">";
	    }
	     echo "<input type=text name='Amt" . $Counter ."' maxlength=12 SIZE=13 value=" . $AllocnItem->AllocAmt . "><input type=hidden name='AllocID" . $Counter . "' value=" . $AllocnItem->ID . "></TD></TR>";

	    $TotalAllocated =$TotalAllocated + $AllocnItem->AllocAmt;

	    $Counter++;

   }

   echo "<TR><TD COLSPAN=5 ALIGN=RIGHT><B><U>Total Allocated:</U></B></TD><TD ALIGN=RIGHT><B><U>" . number_format($TotalAllocated,2) . "</U></B></TD></TR>";
     echo "<TR><TD COLSPAN=5 ALIGN=RIGHT><B>Left to allocate</B></TD><TD ALIGN=RIGHT><B>" . number_format(-$_SESSION['Alloc']->TransAmt - $TotalAllocated,2) . "</B></TD></TR></TABLE>";

   echo "<INPUT TYPE=HIDDEN NAME='TotalNumberOfAllocs' VALUE=$Counter>";

   echo "<INPUT TYPE=SUBMIT NAME='RefreshAllocTotal' VALUE='Recalculate Total To Allocate'>";
   echo "<INPUT TYPE=SUBMIT NAME=UpdateDatabase VALUE='Process Allocations'>";

} elseif(isset($_GET['SupplierID'])){
  /*page called with a supplier code  so show the transactions to allocate
  specific to the supplier selected */
  echo "<INPUT TYPE=hidden NAME=SupplierID VALUE='" . $_GET["SupplierID"] . "'>";

  /*Clear any previous allocation records */
  unset($_SESSION['Alloc']);
  $sql = "SELECT ID, TransNo, TypeName, Type, Suppliers.SupplierID, SuppName, TranDate, SuppReference, Rate, OvAmount+OvGST AS Total, Alloc FROM SuppTrans, Suppliers, SysTypes WHERE SuppTrans.Type=SysTypes.TypeID AND SuppTrans.SupplierNo=Suppliers.SupplierID AND Suppliers.SupplierID='" . $_GET['SupplierID'] . "' AND (Type=21 or Type=22) AND Settled=0 ORDER BY ID";

  $result = DB_query($sql,$db);
  if (DB_num_rows($result)==0){
      echo "<P>There are no outstanding payments or credits yet to be allocated for this supplier.";
      exit;
  }
  echo "<CENTER><table>";

  $TableHeader = "<tr><td class='tableheader'>Trans Type</td><td class='tableheader'>Supplier</td><td class='tableheader'>Number</td><td class='tableheader'>Date</td><td class='tableheader'>Total</td><td class='tableheader'>To Alloc</td></tr>\n";

  echo $TableHeader;
  /* set up table of TransType - Supplier - Trans No - Date - Total - Left to alloc  */
  $RowCounter=0;
  $k=0; //row colour counter
  while ($myrow = DB_fetch_array($result)) {
	if ($k==1){
		echo "<tr bgcolor='#CCCCCC'>";
		$k=0;
	} else {
		echo "<tr bgcolor='#EEEEEE'>";
		$k=1;
	}

	printf("<td>%s</td><td>%s</td><td>%s</td><td>%s</td><td ALIGN=RIGHT>%0.2f</td><td ALIGN=RIGHT>%0.2f</td><td><a href='%sAllocTrans=%s'>Allocate</td></tr>", $myrow["TypeName"], $myrow["SuppName"], $myrow["TransNo"], ConvertSQLDate($myrow["TranDate"]), $myrow["Total"], $myrow["Total"]-$myrow["Alloc"], $_SERVER['PHP_SELF'] . "?" . SID, $myrow["ID"]);

	$RowCounter++;
	if ($RowCounter==20){
		echo $TableHeader;
		$RowCounter=0;
	}
  }
} else { /* show all outstanding payments and credits to be allocated */
  /*Clear any previous allocation records */
  unset($_SESSION['Alloc']->Allocs);
  unset($_SESSION['Alloc']);

  $sql = "SELECT ID, TransNo, TypeName, Type, Suppliers.SupplierID, SuppName, TranDate, SuppReference, Rate, OvAmount+OvGST AS Total, Alloc FROM SuppTrans, Suppliers, SysTypes WHERE SuppTrans.Type=SysTypes.TypeID AND SuppTrans.SupplierNo=Suppliers.SupplierID AND (Type=21 or Type=22) AND Settled=0 ORDER BY ID";

  $result = DB_query($sql,$db);

  echo "<CENTER><table>";
  $TableHeader = "<tr><td class='tableheader'>Trans Type</td><td class='tableheader'>Supplier</td><td class='tableheader'>Number</td><td class='tableheader'>Date</td><td class='tableheader'>Total</td><td class='tableheader'>To Alloc</td></tr>\n";

  echo $TableHeader;
  /* set up table of Tran Type - Supplier - Trans No - Date - Total - Left to alloc  */

  $k=0; //row colour counter
  $RowCounter =0;
  while ($myrow = DB_fetch_array($result)) {
	if ($k==1){
		echo "<tr bgcolor='#CCCCCC'>";
		$k=0;
	} else {
		echo "<tr bgcolor='#EEEEEE'>";
		$k=1;
	}

	printf("<td>%s</td><td>%s</td><td>%s</td><td>%s</td><td ALIGN=RIGHT>%0.2f</td><td ALIGN=RIGHT>%0.2f</td><td><a href='%sAllocTrans=%s'>Allocate</td></tr>", $myrow["TypeName"], $myrow["SuppName"], $myrow["TransNo"], ConvertSQLDate($myrow["TranDate"]), $myrow["Total"], $myrow["Total"]-$myrow["Alloc"], $_SERVER['PHP_SELF'] . "?" . SID, $myrow["ID"]);

	$RowCounter++;
	if ($RowCounter==20){
		echo $TableHeader;
		$RowCounter=0;
	}
   }
  //END WHILE LIST LOOP
   echo "</table></CENTER>";
   if (DB_num_rows($result) == 0) {
	echo "There are no allocations to be done";
   }

} /* end of else if not a SupplierID or transaction called with the URL */

echo "</form>";
include("includes/footer.inc");
?>

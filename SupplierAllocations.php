<?php


/* $Revision: 1.15 $ */

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

include('includes/DefineSuppAllocsClass.php');

$PageSecurity = 5;

include('includes/session.inc');

$title = _('Supplier Payment') . '/' . _('Credit Note Allocations');

include('includes/header.inc');
include('includes/SQL_CommonFunctions.inc');

echo '<p class="page_title_text"><img src="'.$rootpath.'/css/'.$theme.'/images/transactions.png" title="' . _('Supplier Allocations') . '" alt="">' . ' ' . _('Supplier Allocations') . '</p>';


if (isset($_POST['UpdateDatabase']) OR isset($_POST['RefreshAllocTotal'])) {

	//initialise no input errors assumed initially before we test

	$InputError = 0;

	if (!isset($_SESSION['Alloc'])){
		prnMsg( _('Allocations can not be processed again') . '. ' . _('If you hit refresh on this page after having just processed an allocation') . ', ' . _('try to use the navigation links provided rather than the back button, to avoid this message in future'),'warn');
		include('includes/footer.inc');
		exit;
	}

/*1st off run through and update the array with the amounts allocated
	This works because the form has an input field called the value of
	AllocnItm->ID for each record of the array - and PHP sets the value of
	the form variable on a post*/

	$TotalAllocated = 0;
	$TotalDiffOnExch = 0;

	for ($AllocCounter=0; $AllocCounter < $_POST['TotalNumberOfAllocs']; $AllocCounter++){

		if (!is_numeric($_POST['Amt' . $AllocCounter])){
		      $_POST['Amt' . $AllocCounter] = 0;
		 }
		 if ($_POST['Amt' . $AllocCounter] < 0){
		      prnMsg(_('The entry for the amount to allocate was negative') . '. ' . _('A positive allocation amount is expected'),'error');
			$_POST['Amt' . $AllocCounter] = 0;
		 }

		if ($_POST['All' . $AllocCounter] == True){
			$_POST['Amt' . $AllocCounter] = $_POST['YetToAlloc' . $AllocCounter];

		 }

		  /*Now check to see that the AllocAmt is no greater than the
		 amount left to be allocated against the transaction under review */

		 if ($_POST['Amt' . $AllocCounter] > $_POST['YetToAlloc' . $AllocCounter]){
		     $_POST['Amt' . $AllocCounter] = $_POST['YetToAlloc' . $AllocCounter];
		 }



		 $_SESSION['Alloc']->Allocs[$_POST['AllocID' . $AllocCounter]]->AllocAmt = $_POST['Amt' . $AllocCounter];

		 /*recalcuate the new difference on exchange
		 (a +positive amount is a gain -ve a loss)*/

		 $_SESSION['Alloc']->Allocs[$_POST['AllocID' . $AllocCounter]]->DiffOnExch = ($_POST['Amt' . $AllocCounter] / $_SESSION['Alloc']->TransExRate) - ($_POST['Amt' . $AllocCounter] / $_SESSION['Alloc']->Allocs[$_POST['AllocID' . $AllocCounter]]->ExRate);

		 $TotalDiffOnExch = $TotalDiffOnExch + $_SESSION['Alloc']->Allocs[$_POST['AllocID' . $AllocCounter]]->DiffOnExch;
		 $TotalAllocated = $TotalAllocated + $_POST['Amt' . $AllocCounter];

	} /*end of the loop to set the new allocation amounts,
	recalc diff on exchange and add up total allocations */

	if ($TotalAllocated + $_SESSION['Alloc']->TransAmt > 0.005){
		echo '<br><hr>';
		prnMsg(_('These allocations cannot be processed because the amount allocated is more than the amount of the') .
			  ' ' . $_SESSION['Alloc']->TransTypeName  . ' ' . _('being allocated') . '<br>' . _('Total allocated') . ' = ' . 			$TotalAllocated . ' ' . _('and the total amount of the Credit/payment was') . ' ' . -$_SESSION['Alloc']->TransAmt,'error');
		echo '<br><hr>';
		$InputError = 1;
	}

}


if (isset($_POST['UpdateDatabase'])){

	if ($InputError == 0){ /* ie all the traps were passed */

	/* actions to take having checked that the input is sensible
	1st set up a transaction on this thread*/

		$SQL = 'BEGIN';

		$ErrMsg = _('CRITICAL ERROR') . '! ' . _('The transaction BEGIN failed with error');
		$DbgMsg = _('The SQL that was used but failed was');

		$Result=DB_query($SQL, $db, $ErrMsg, $DbgMsg);

		foreach ($_SESSION['Alloc']->Allocs as $AllocnItem) {

			  if ($AllocnItem->OrigAlloc >0 AND ($AllocnItem->OrigAlloc != $AllocnItem->AllocAmt)){

			  /*Orignial allocation was not 0 and it has now changed
			    need to delete the old allocation record */

				     $SQL = 'DELETE FROM suppallocs WHERE id = ' . $AllocnItem->PrevAllocRecordID;

					  $ErrMsg = _('CRITICAL ERROR') . '! ' . _('NOTE DOWN THIS ERROR AND SEEK ASSISTANCE') . ': ' . _('The existing allocation for') . ' ' . $AllocnItem->TransType .' ' . $AllocnItem->TypeNo . ' ' . _('could not be deleted because');
					  $DbgMsg = _('The following SQL to delete the allocation record was used');

				     $Result=DB_query($SQL, $db, $ErrMsg, $DbgMsg, True);
			 }

			 if ($AllocnItem->OrigAlloc != $AllocnItem->AllocAmt){

			 /*Only when there has been a change to the allocated amount
			 do we need to insert a new allocation record and update
			 the transaction with the new alloc amount and diff on exch */

				     if ($AllocnItem->AllocAmt > 0){
					     $SQL = "INSERT INTO suppallocs (datealloc, amt, transid_allocfrom, transid_allocto) VALUES ('" . FormatDateForSQL(date($_SESSION['DefaultDateFormat'])) . "', 
					     		" . $AllocnItem->AllocAmt . ', 
							' . $_SESSION['Alloc']->AllocTrans . ', 
							' . $AllocnItem->ID . ')';

						  $ErrMsg = _('CRITICAL ERROR') . '! ' . _('NOTE DOWN THIS ERROR AND SEEK ASSISTANCE') . ': ' .  _('The supplier allocation record for') . ' ' . $AllocnItem->TransType . ' ' .  $AllocnItem->TypeNo . ' ' ._('could not be inserted because');
						  $DbgMsg = _('The following SQL to insert the allocation record was used');

					     $Result=DB_query($SQL, $db, $ErrMsg, $DbgMsg, True);
				     }
				     $NewAllocTotal = $AllocnItem->PrevAlloc + $AllocnItem->AllocAmt;

				     if (abs($NewAllocTotal-$AllocnItem->TransAmount) < 0.01){
					     $Settled = 1;
				     } else {
					     $Settled = 0;
				     }

				     $SQL = 'UPDATE supptrans SET diffonexch=' . $AllocnItem->DiffOnExch . ', 
				     				alloc = ' .  $NewAllocTotal . ', 
								settled = ' . $Settled . ' 
						WHERE id = ' . $AllocnItem->ID;

					  $ErrMsg = _('CRITICAL ERROR') . '! ' . _('NOTE DOWN THIS ERROR AND SEEK ASSISTANCE') . ': ' . _('The debtor transaction record could not be modified for the allocation against it because');

					  $DbgMsg = _('The following SQL to update the debtor transaction record was used');

				     $Result = DB_query($SQL, $db, $ErrMsg, $DbgMsg, True);

			 } /*end if the new allocation is different to what it was before */

		}  /*end of the loop through the array of allocations made */

		/*Now update the payment or credit note with the amount allocated
		and the new diff on exchange */

		if (abs($TotalAllocated + $_SESSION['Alloc']->TransAmt) < 0.01){
		   $Settled = 1;
		} else {
		   $Settled = 0;
		}

		$SQL = 'UPDATE supptrans SET alloc = ' .  -$TotalAllocated . ', 
					diffonexch = ' . -$TotalDiffOnExch . ', 
					settled=' . $Settled . ' 
				WHERE id = ' . $_SESSION['AllocTrans'];

		$ErrMsg = _('CRITICAL ERROR') . '! ' . _('NOTE DOWN THIS ERROR AND SEEK ASSISTANCE') . ': ' .
					 _('The supplier payment or credit note transaction could not be modified for the new allocation and exchange difference because');

		$DbgMsg = _('The following SQL to update the payment or credit note was used');

		$Result = DB_query($SQL, $db, $ErrMsg, $DbgMsg, True);

		/*Almost there ... if there is a change in the total diff on exchange
		 and if the GLLink to debtors is active - need to post diff on exchange to GL */

		$MovtInDiffOnExch = $_SESSION['Alloc']->PrevDiffOnExch + $TotalDiffOnExch;
		if ($MovtInDiffOnExch !=0 ){

		   if ($_SESSION['CompanyRecord']['gllink_debtors'] == 1){

		      $PeriodNo = GetPeriod($_SESSION['Alloc']->TransDate, $db);

		      $_SESSION['Alloc']->TransDate = FormatDateForSQL($_SESSION['Alloc']->TransDate);

		      $SQL = 'INSERT INTO gltrans (type, 
		      					typeno, 
							trandate, 
							periodno, 
							account, 
							narrative, 
							amount) 
					VALUES (' . $_SESSION['Alloc']->TransType . ', 
						' . $_SESSION['Alloc']->TransNo . ", 
						'" . $_SESSION['Alloc']->TransDate . "', 
						" . $PeriodNo . ', 
						' . $_SESSION['CompanyRecord']['purchasesexchangediffact'] . ", 
						'". _('Exch diff') . "', 
						" . $MovtInDiffOnExch . ')';

		      $ErrMsg = _('CRITICAL ERROR') . '! ' . _('NOTE DOWN THIS ERROR AND SEEK ASSISTANCE') . ': ' .
		      			 _('The GL entry for the difference on exchange arising out of this allocation could not be inserted because');
		      $DbgMsg = _('The following SQL to insert the GLTrans record was used');

		      $Result = DB_query($SQL, $db, $ErrMsg, $DbgMsg, True);


		      $SQL = 'INSERT INTO gltrans (type, 
		      					typeno, 
							trandate, 
							periodno, 
							account, 
							narrative, 
							amount) 
					VALUES (' . $_SESSION['Alloc']->TransType . ', 
						' . $_SESSION['Alloc']->TransNo . ", 
						'" . $_SESSION['Alloc']->TransDate . "', 
						" . $PeriodNo . ', 
						' . $_SESSION['CompanyRecord']['creditorsact'] . ", 
						'" . _('Exch Diff') . "', 
						" . -$MovtInDiffOnExch . ')';

		      $ErrMsg = _('CRITICAL ERROR') . '! ' . _('NOTE DOWN THIS ERROR AND SEEK ASSISTANCE') . ' : ' .
		      			 _('The GL entry for the difference on exchange arising out of this allocation could not be inserted because');

		      $DbgMsg = _('The following SQL to insert the GLTrans record was used');

		      $Result = DB_query($SQL, $db, $ErrMsg, $DbgMsg, True);

		   }

		}

	 /* OK Commit the transaction */

		$SQL = 'COMMIT';

		$ErrMsg = _('CRITICAL ERROR') . '! ' . _('NOTE DOWN THIS ERROR AND SEEK ASSISTANCE') . ': ' .
					 _('The updates and insertions arising from this allocation could not be committed to the database');

		$DbgMsg = _('The COMMIT SQL failed');

		$Result = DB_query($SQL, $db, $ErrMsg, $DbgMsg);

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

echo "<form action='" . $_SERVER['PHP_SELF'] . "?" . SID . "' method=post>";

if (isset($_POST['SupplierID'])){
 	$_GET['SupplierID'] = $_POST['SupplierID'];
	echo "<input type='hidden' name='SupplierID' VALUE='" . $_POST["SupplierID"] . "'>";
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


	$_SESSION['Alloc'] = new Allocation;

	/*The session varibale AllocTrans is set from the passed variable AllocTrans
	on the first pass */

	$_SESSION['AllocTrans'] = $_GET['AllocTrans'];
	$_POST['AllocTrans'] = $_GET['AllocTrans'];


	$SQL= 'SELECT systypes.typename, 
			supptrans.type, 
			supptrans.transno, 
			supptrans.trandate, 
			supptrans.supplierno,
			suppliers.suppname, 
			rate, 
			(supptrans.ovamount+supptrans.ovgst) AS total, 
			supptrans.diffonexch,
			supptrans.alloc
		    FROM supptrans, 
		    	systypes, 
			suppliers
		    WHERE supptrans.type = systypes.typeid
		    AND supptrans.supplierno = suppliers.supplierid
		    AND supptrans.id=' . $_SESSION['AllocTrans'];

	$Result = DB_query($SQL, $db);
	if (DB_num_rows($Result) != 1){
	echo _('There was a problem retrieving the information relating the transaction selected') . '. ' . _('Allocations are unable to proceed');
	if ($debug == 1){
		echo '<br>' . _('The SQL that was used to retrieve the transaction information was') . " :<br>$SQL";
	}
	exit;
	}

	$myrow = DB_fetch_array($Result);

	$_SESSION['Alloc']->AllocTrans = $_SESSION['AllocTrans'];
	$_SESSION['Alloc']->SupplierID = $myrow['supplierno'];
	$_SESSION['Alloc']->SuppName = $myrow['suppname'];;
	$_SESSION['Alloc']->TransType = $myrow['type'];
	$_SESSION['Alloc']->TransTypeName = $myrow['typename'];
	$_SESSION['Alloc']->TransNo = $myrow['transno'];
	$_SESSION['Alloc']->TransExRate = $myrow['rate'];
	$_SESSION['Alloc']->TransAmt = $myrow['total'];
	$_SESSION['Alloc']->PrevDiffOnExch = $myrow['diffonexch'];
	$_SESSION['Alloc']->TransDate = ConvertSQLDate($myrow['trandate']);

	/* Now populate the array of possible (and previous actual) allocations for this supplier */
	/*First get the transactions that have outstanding balances ie Total-Alloc >0 */

	$SQL= "SELECT supptrans.id, 
			typename, 
			transno, 
			trandate, 
			suppreference, 
			rate,
			ovamount+ovgst AS total, 
			diffonexch, 
			alloc
		FROM supptrans, 
			systypes
		WHERE supptrans.type = systypes.typeid
		AND supptrans.settled=0
		AND abs(ovamount+ovgst-alloc)>0.009
		AND supplierno='" . $_SESSION['Alloc']->SupplierID . "'";

	$ErrMsg = _('There was a problem retrieving the transactions available to allocate to');

	$DbgMsg = _('The SQL that was used to retrieve the transaction information was');

	$Result = DB_query($SQL, $db, $ErrMsg, $DbgMsg);

	while ($myrow=DB_fetch_array($Result)){
		$_SESSION['Alloc']->add_to_AllocsAllocn ($myrow['id'], 
							$myrow['typename'], 
							$myrow['transno'],
							ConvertSQLDate($myrow['trandate']), 
							$myrow['suppreference'], 
							0, 
							$myrow['total'],
							$myrow['rate'], 
							$myrow['diffonexch'], 
							$myrow['diffonexch'], 
							$myrow['alloc'], 
							'NA');
	}

	/* Now get trans that might have previously been allocated to by this trans
	NB existing entries where still some of the trans outstanding entered from
	above logic will be overwritten with the prev alloc detail below */

	$SQL = 'SELECT supptrans.id, 
			typename, 
			transno, 
			trandate, 
			suppreference, 
			rate,
			ovamount+ovgst AS total, 
			diffonexch, 
			supptrans.alloc-suppallocs.amt AS prevallocs,
			amt, 
			suppallocs.id AS allocid
			  FROM supptrans, 
			  	systypes, 
				suppallocs
			  WHERE supptrans.type = systypes.typeid
			  AND supptrans.id=suppallocs.transid_allocto
			  AND suppallocs.transid_allocfrom=' . $_SESSION['AllocTrans'] .
			  " AND supplierno='" . $_SESSION['Alloc']->SupplierID . "'";

	$ErrMsg = _('There was a problem retrieving the previously allocated transactions for modification');

	$DbgMsg = _('The SQL that was used to retrieve the previously allocated transaction information was');

	$Result = DB_query($SQL, $db, $ErrMsg, $DbgMsg);

	while ($myrow = DB_fetch_array($Result)){

		$DiffOnExchThisOne = ($myrow['amt']/$myrow['rate']) - ($myrow['amt']/$_SESSION['Alloc']->TransExRate);

		$_SESSION['Alloc']->add_to_AllocsAllocn ($myrow['id'], $myrow['typename'], $myrow['transno'],
								ConvertSQLDate($myrow['trandate']), $myrow['suppreference'], $myrow['amt'],
								$myrow['total'], 
								$myrow['rate'], 
								$DiffOnExchThisOne,
								($myrow['diffonexch'] - $DiffOnExchThisOne), 
								$myrow['prevallocs'], 
								$myrow['allocid']);
	}
}

if (isset($_POST['AllocTrans'])){

	echo "<input type='hidden' name='AllocTrans' VALUE='" . $_POST["AllocTrans"] . "'>";

	/*Show the transaction being allocated and the potential trans it could be allocated to
        and those where there is already an existing allocation */

        echo '<hr><div class="centre"><font color=BLUE>' . _('Allocation of supplier') . ' ' .
        		 $_SESSION['Alloc']->TransTypeName . ' ' . _('number') . ' ' .
        		 $_SESSION['Alloc']->TransNo . ' ' . _('from') . ' ' .
        		 $_SESSION['Alloc']->SupplierID . ' - <b>' .
        		 $_SESSION['Alloc']->SuppName . '</b>, ' . _('dated') . ' ' .
        		 $_SESSION['Alloc']->TransDate;

        if ($_SESSION['Alloc']->TransExRate != 1){
	     	  echo '<br>' . _("Amount in supplier currency"). ' <b>' .
	     	  		 number_format(-$_SESSION['Alloc']->TransAmt,2) . '</b><i> (' .
	     	  		 _('converted into local currency at an exchange rate of') . ' ' .
	     	  		 $_SESSION['Alloc']->TransExRate . ')</i><p>';

        } else {

		     echo '<br>' . _('Transaction total') . ': <b>' . -$_SESSION['Alloc']->TransAmt . '</b></div>';
        }

        echo '<hr>';

   /*Now display the potential and existing allocations put into the array above */

        echo '<table cellpadding=2 colspan=7 BORDER=0>';
	  	  $TableHeader = "<tr><th>" . _('Type') . "</th>
		 			<th>" . _('Trans') . '<br>' . _('Number') . "</th>
					<th>" . _('Trans') .'<br>' . _('Date') . "</th>
					<th>" . _('Supp') . '<br>' . _('Ref') . "</th>
					<th>" . _('Total') . '<br>' . _('Amount') .	"</th>
					<th>" . _('Yet to') . '<br>' . _('Allocate') . "</th>
					<th>" . _('This') . '<br>' . _('Allocation') . '</th></tr>';
        $k = 0;
	$Counter = 0;
	$RowCounter = 0;
        $TotalAllocated = 0;

        foreach ($_SESSION['Alloc']->Allocs as $AllocnItem) {

	    /*Alternate the background colour for each potential allocation line */

	    if ($k == 1){
		    echo '<tr class="EvenTableRows">';
		    $k = 0;
	    } else {
		    echo '<tr class="OddTableRows">';
		    $k = 1;
	    }
	    $RowCounter++;

	    if ($RowCounter == 15){

		/*Set up another row of headings to ensure always a heading on the screen of potential allocns*/

			echo $TableHeader;

			$RowCounter = 1;

	    }

	    $YetToAlloc = ($AllocnItem->TransAmount - $AllocnItem->PrevAlloc);

	    echo "<td>$AllocnItem->TransType</td>
	    		<td>$AllocnItem->TypeNo</td>
			<td>$AllocnItem->TransDate</td>
	    		<td>$AllocnItem->SuppRef</td>
			<td align=right>" . number_format($AllocnItem->TransAmount,2) . '</td>
	    		<td align=right>' . number_format($YetToAlloc,2) . "<input type=hidden name='YetToAlloc" .
	    		 $Counter . "' VALUE=" . $YetToAlloc . '></td>';

	    echo "<td align=right><input type='checkbox' name='All" .  $Counter . "'";

	    if (ABS($AllocnItem->AllocAmt-$YetToAlloc) < 0.01){
			echo ' VALUE=' . True . '>';
	    } else {
	    	echo '>';
	    }
       echo "<input type=text class='number' name='Amt" . $Counter ."' maxlength=12 size=13 VALUE=" .
       		$AllocnItem->AllocAmt . "><input type=hidden name='AllocID" . $Counter .
       		"' VALUE=" . $AllocnItem->ID . '></td></tr>';

	    $TotalAllocated = $TotalAllocated + $AllocnItem->AllocAmt;

	    $Counter++;

   }

   echo '<tr><td colspan=5 align=right><b><U>' . _('Total Allocated') . ':</U></b></td>
   		<td align=right><b><U>' .  number_format($TotalAllocated,2) . '</U></b></td></tr>';

   echo '<tr><td colspan=5 align=right><b>' . _('Left to allocate') . '</b></td><td align=right><b>' .
     		number_format(-$_SESSION['Alloc']->TransAmt - $TotalAllocated,2) . '</b></td></tr></table>';

   echo "<div class='centre'><input type=hidden name='TotalNumberOfAllocs' VALUE=$Counter>";

   echo "<input type=submit name='RefreshAllocTotal' VALUE='" . _('Recalculate Total To Allocate') . "'>";
   echo "<input type=submit name=UpdateDatabase VALUE='" . _('Process Allocations') . "'></div>";

} elseif(isset($_GET['SupplierID'])){

  /*page called with a supplier code  so show the transactions to allocate
  specific to the supplier selected */

  echo "<input type=hidden name=SupplierID VALUE='" . $_GET['SupplierID'] . "'>";

  /*Clear any previous allocation records */

  unset($_SESSION['Alloc']);

  $sql = "SELECT id, 
  		transno, 
		typename, 
		type, 
		suppliers.supplierid, 
		suppname, 
		trandate,
  		suppreference, 
		rate, 
		ovamount+ovgst AS total, 
		alloc
  	FROM supptrans, 
		suppliers, 
		systypes
  	WHERE supptrans.type=systypes.typeid
  	AND supptrans.supplierno=suppliers.supplierid
  	AND suppliers.supplierid='" . $_GET['SupplierID'] ."' 
	AND (type=21 or type=22) AND settled=0 ORDER BY id";

  $result = DB_query($sql, $db);
  if (DB_num_rows($result) == 0){
	prnMsg(_('There are no outstanding payments or credits yet to be allocated for this supplier'),'info');
	include('includes/footer.inc');
	exit;
  }
  echo '<table>';

  $TableHeader = "<tr><th>" . _('Trans Type') .
		"</th><th>" . _('Supplier') .
		"</th><th>" . _('Number') .
		"</th><th>" . _('Date') .
		"</th><th>" . _('Total') .
		"</th><th>" . _('To Alloc') . "</th></tr>\n";

  echo $TableHeader;

  /* set up table of TransType - Supplier - Trans No - Date - Total - Left to alloc  */

  $RowCounter = 0;
  $k = 0; //row colour counter
  while ($myrow = DB_fetch_array($result)) {
	if ($k == 1){
		echo '<tr class="EvenTableRows">';
		$k = 0;
	} else {
		echo '<tr class="OddTableRows">';
		$k = 1;
	}

	printf("<td>%s</td>
		<td>%s</td>
		<td>%s</td>
		<td>%s</td>
		<td align=right>%0.2f</td>
		<td align=right>%0.2f</td>
		<td><a href='%sAllocTrans=%s'>" . _('Allocate') .'</td>
		</tr>',
		$myrow['typename'],
		$myrow['suppname'],
		$myrow['transno'],
		ConvertSQLDate($myrow['trandate']),
		$myrow['total'],
		$myrow['total']-$myrow['alloc'],
		$_SERVER['PHP_SELF'] . "?" . SID,
		$myrow['id']);

  }

} else { /* show all outstanding payments and credits to be allocated */

  /*Clear any previous allocation records */

  unset($_SESSION['Alloc']->Allocs);
  unset($_SESSION['Alloc']);

  $sql = 'SELECT id, 
  		transno, 
		typename, 
		type, 
		suppliers.supplierid, 
		suppname, 
		trandate,
  		suppreference, 
		rate, 
		ovamount+ovgst AS total, 
		alloc
  	FROM supptrans, 
		suppliers, 
		systypes
  	WHERE supptrans.type=systypes.typeid
  	AND supptrans.supplierno=suppliers.supplierid
  	AND (type=21 or type=22)
  	AND settled=0 ORDER BY id';

  $result = DB_query($sql, $db);

  echo '<table>';
  $TableHeader = "<tr><th>" . _('Trans Type') . "</th>
  		<th>" . _('Supplier') . "</th>
  		<th>" . _('Number') . "</th>
  		<th>" . _('Date') . "</th>
  		<th>" . _('Total') . "</th>
  		<th>" . _('To Alloc') . "</th>
		<th>" . _('More Info') . "</th></tr>\n";

  echo $TableHeader;

  /* set up table of Tran Type - Supplier - Trans No - Date - Total - Left to alloc  */

  $k = 0; //row colour counter
  $RowCounter = 0;
  while ($myrow = DB_fetch_array($result)) {
	if ($k == 1){
		echo '<tr class="EvenTableRows">';
		$k = 0;
	} else {
		echo '<tr class="OddTableRows">';
		$k = 1;
	}

	printf("<td>%s</td>
		<td>%s</td>
		<td>%s</td>
		<td>%s</td>
		<td align=right>%0.2f</td>
		<td align=right>%0.2f</td>
		<td><a href='%sAllocTrans=%s'>" . _('Allocate') . '</td>
		</tr>',
		$myrow['typename'],
		$myrow['suppname'],
		$myrow['transno'],
		ConvertSQLDate($myrow['trandate']),
		$myrow['total'],
		$myrow['total']-$myrow['alloc'],
		$_SERVER['PHP_SELF'] . "?" . SID,
		$myrow['id']);


  }  //END WHILE LIST LOOP

  echo "</table>";

  if (DB_num_rows($result) == 0) {
	prnMsg(_('There are no allocations to be done'),'info');
  }

} /* end of else if not a SupplierID or transaction called with the URL */

echo '</form>';
include('includes/footer.inc');
?>

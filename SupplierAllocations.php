<?php
/*	This page can be called with...

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

// NB: these classes are not autoloaded, and their definition has to be included before the session is started (in session.php)
include('includes/DefineSuppAllocsClass.php');

require(__DIR__ . '/includes/session.php');
$Title = __('Supplier Payment') . '/' . __('Credit Note Allocations');
$ViewTopic = 'ARTransactions';// Filename in ManualContents.php's TOC./* RChacon: To do ManualAPInquiries.html from ManualARInquiries.html */
$BookMark = 'SupplierAllocations';
include('includes/header.php');

echo '<p class="page_title_text"><img alt="" src="', $RootPath, '/css/', $Theme,
	'/images/transactions.png" title="', // Icon image.
	__('Supplier Allocations'), '" /> ', // Icon title.
	__('Supplier Allocations'), '</p>';// Page title.

include('includes/SQL_CommonFunctions.php');

if (isset($_POST['UpdateDatabase']) OR isset($_POST['RefreshAllocTotal'])) {

	if (!isset($_SESSION['Alloc'])){
		prnMsg(
			__('Allocations can not be processed again') . '. ' .
				__('If you hit refresh on this page after having just processed an allocation') . ', ' .
				__('try to use the navigation links provided rather than the back button, to avoid this message in future'),
			'warn');
		include('includes/footer.php');
		exit();
	}

/*1st off run through and update the array with the amounts allocated
	This works because the form has an input field called the value of
	AllocnItm->ID for each record of the array - and PHP sets the value of
	the form variable on a post*/

	$InputError = 0;
	$TotalAllocated = 0;
	$TotalDiffOnExch = 0;

	for ($AllocCounter=0; $AllocCounter < $_POST['TotalNumberOfAllocs']; $AllocCounter++){

		$_POST['Amt' . $AllocCounter] = filter_number_format($_POST['Amt' . $AllocCounter]);

		if (!is_numeric($_POST['Amt' . $AllocCounter])){
		      $_POST['Amt' . $AllocCounter] = 0;
		 }
		 if ($_POST['Amt' . $AllocCounter] < 0){
			prnMsg(__('The entry for the amount to allocate was negative') . '. ' . __('A positive allocation amount is expected'),'error');
			$_POST['Amt' . $AllocCounter] = 0;
		 }

		if (isset($_POST['All' . $AllocCounter]) AND $_POST['All' . $AllocCounter] == true){
			/* $_POST['YetToAlloc...] is a hidden item on the form not locale_number_formatted */
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

		 $TotalDiffOnExch += $_SESSION['Alloc']->Allocs[$_POST['AllocID' . $AllocCounter]]->DiffOnExch;
		 $TotalAllocated += round($_POST['Amt' . $AllocCounter],$_SESSION['Alloc']->CurrDecimalPlaces);
	} /*end of the loop to set the new allocation amounts,
	recalc diff on exchange and add up total allocations */

	if ($TotalAllocated + $_SESSION['Alloc']->TransAmt > 0.005){
		echo '<br />';
		prnMsg(__('These allocations cannot be processed because the amount allocated is more than the amount of the') . ' ' . $_SESSION['Alloc']->TransTypeName  . ' ' . __('being allocated') . '<br />' . __('Total allocated') . ' = ' . locale_number_format($TotalAllocated,$_SESSION['Alloc']->CurrDecimalPlaces) . ' ' . __('and the total amount of the Credit/payment was') . ' ' . locale_number_format(-$_SESSION['Alloc']->TransAmt,$_SESSION['Alloc']->CurrDecimalPlaces) ,'error');
		echo '<br />';
		$InputError = 1;
	}

}

if (isset($_POST['UpdateDatabase'])){

	if ($InputError == 0){ /* ie all the traps were passed */

	/* actions to take having checked that the input is sensible
	1st set up a transaction on this thread*/

		DB_Txn_Begin();

		foreach ($_SESSION['Alloc']->Allocs as $AllocnItem) {

			  if ($AllocnItem->OrigAlloc >0 AND ($AllocnItem->OrigAlloc != $AllocnItem->AllocAmt)){

			  /*Orignial allocation was not 0 and it has now changed
			    need to delete the old allocation record */

				$SQL = "DELETE FROM suppallocs WHERE id = '" . $AllocnItem->PrevAllocRecordID . "'";

				$ErrMsg = __('CRITICAL ERROR') . '! ' . __('NOTE DOWN THIS ERROR AND SEEK ASSISTANCE') . ': ' . __('The existing allocation for') . ' ' . $AllocnItem->TransType .' ' . $AllocnItem->TypeNo . ' ' . __('could not be deleted because');

				$Result = DB_query($SQL, $ErrMsg, '', true);
			 }

			 if ($AllocnItem->OrigAlloc != $AllocnItem->AllocAmt){

			 /*Only when there has been a change to the allocated amount
			 do we need to insert a new allocation record and update
			 the transaction with the new alloc amount and diff on exch */

				     if ($AllocnItem->AllocAmt > 0){
					     $SQL = "INSERT INTO suppallocs (datealloc,
														amt,
														transid_allocfrom,
														transid_allocto)
										VALUES ('" . FormatDateForSQL(date($_SESSION['DefaultDateFormat'])) . "',
										     		'" . $AllocnItem->AllocAmt . "',
												'" . $_SESSION['Alloc']->AllocTrans . "',
												'" . $AllocnItem->ID . "')";

						 $ErrMsg = __('CRITICAL ERROR') . '! ' . __('NOTE DOWN THIS ERROR AND SEEK ASSISTANCE') . ': ' .  __('The supplier allocation record for') . ' ' . $AllocnItem->TransType . ' ' .  $AllocnItem->TypeNo . ' ' .__('could not be inserted because');

					     $Result = DB_query($SQL, $ErrMsg, '', true);
				     }
				     $NewAllocTotal = $AllocnItem->PrevAlloc + $AllocnItem->AllocAmt;

				     if (abs($NewAllocTotal-$AllocnItem->TransAmount) < 0.01){
					     $Settled = 1;
				     } else {
					     $Settled = 0;
				     }

				     $SQL = "UPDATE supptrans SET diffonexch='" . $AllocnItem->DiffOnExch . "',
												alloc = '" .  $NewAllocTotal . "',
												settled = '" . $Settled . "'
							WHERE id = '" . $AllocnItem->ID . "'";

					 $ErrMsg = __('CRITICAL ERROR') . '! ' . __('NOTE DOWN THIS ERROR AND SEEK ASSISTANCE') . ': ' . __('The debtor transaction record could not be modified for the allocation against it because');

					 $Result = DB_query($SQL, $ErrMsg, '', true);

			 } /*end if the new allocation is different to what it was before */

		}  /*end of the loop through the array of allocations made */

		/*Now update the payment or credit note with the amount allocated
		and the new diff on exchange */

		if (abs($TotalAllocated + $_SESSION['Alloc']->TransAmt) < 0.01){
		   $Settled = 1;
		} else {
		   $Settled = 0;
		}

		$SQL = "UPDATE supptrans SET alloc = '" .  -$TotalAllocated . "',
					diffonexch = '" . -$TotalDiffOnExch . "',
					settled='" . $Settled . "'
				WHERE id = '" . $_SESSION['AllocTrans'] . "'";

		$ErrMsg = __('CRITICAL ERROR') . '! ' . __('NOTE DOWN THIS ERROR AND SEEK ASSISTANCE') . ': ' .
					 __('The supplier payment or credit note transaction could not be modified for the new allocation and exchange difference because');

		$Result = DB_query($SQL, $ErrMsg, '', true);

		/*Almost there ... if there is a change in the total diff on exchange
		 and if the GLLink to debtors is active - need to post diff on exchange to GL */

		$MovtInDiffOnExch = $_SESSION['Alloc']->PrevDiffOnExch + $TotalDiffOnExch;
		if ($MovtInDiffOnExch !=0 ){

		   if ($_SESSION['CompanyRecord']['gllink_debtors'] == 1){

		      $PeriodNo = GetPeriod($_SESSION['Alloc']->TransDate);

		      $_SESSION['Alloc']->TransDate = FormatDateForSQL($_SESSION['Alloc']->TransDate);

		      $SQL = "INSERT INTO gltrans (type,
							typeno,
							trandate,
							periodno,
							account,
							narrative,
							amount)
						VALUES ('" . $_SESSION['Alloc']->TransType . "',
							'" . $_SESSION['Alloc']->TransNo . "',
							'" . $_SESSION['Alloc']->TransDate . "',
							'" . $PeriodNo . "',
							'" . $_SESSION['CompanyRecord']['purchasesexchangediffact'] . "',
							'". __('Exchange difference') . "',
							'" . $MovtInDiffOnExch . "')";

		      $ErrMsg = __('CRITICAL ERROR') . '! ' . __('NOTE DOWN THIS ERROR AND SEEK ASSISTANCE') . ': ' . __('The GL entry for the difference on exchange arising out of this allocation could not be inserted because');

		      $Result = DB_query($SQL, $ErrMsg, '', true);

		      $SQL = "INSERT INTO gltrans (type,
							typeno,
							trandate,
							periodno,
							account,
							narrative,
							amount)
						VALUES ('" . $_SESSION['Alloc']->TransType . "',
							'" . $_SESSION['Alloc']->TransNo . "',
							'" . $_SESSION['Alloc']->TransDate . "',
							'" . $PeriodNo . "',
							'" . $_SESSION['CompanyRecord']['creditorsact'] . "',
							'" . __('Exchange difference') . "',
							'" . -$MovtInDiffOnExch . "')";

		      $ErrMsg = __('CRITICAL ERROR') . '! ' . __('NOTE DOWN THIS ERROR AND SEEK ASSISTANCE') . ' : ' .
		      			 __('The GL entry for the difference on exchange arising out of this allocation could not be inserted because');

		      $Result = DB_query($SQL, $ErrMsg, '', true);

		   }

		}

	 /* OK Commit the transaction */

		DB_Txn_Commit();

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

echo '<form action="' . htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') . '" method="post">';
echo '<div>';
echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';

if (isset($_POST['SupplierID'])){
 	$_GET['SupplierID'] = $_POST['SupplierID'];
	echo '<input type="hidden" name="SupplierID" value="' . $_POST['SupplierID'] . '" />';
}

if (isset($_GET['AllocTrans'])){

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


	$SQL= "SELECT systypes.typename,
				supptrans.type,
				supptrans.transno,
				supptrans.trandate,
				supptrans.supplierno,
				suppliers.suppname,
				supptrans.rate,
				(supptrans.ovamount+supptrans.ovgst) AS total,
				supptrans.diffonexch,
				supptrans.alloc,
				currencies.decimalplaces
		    FROM supptrans INNER JOIN systypes
			ON supptrans.type = systypes.typeid
			INNER JOIN suppliers
			ON supptrans.supplierno = suppliers.supplierid
			INNER JOIN currencies
			ON suppliers.currcode=currencies.currabrev
		    WHERE supptrans.id='" . $_SESSION['AllocTrans'] . "'";

	$Result = DB_query($SQL);
	if (DB_num_rows($Result) != 1){
		prnMsg(__('There was a problem retrieving the information relating the transaction selected') . '. ' . __('Allocations are unable to proceed'), 'error');
		exit();
	}

	$MyRow = DB_fetch_array($Result);

	$_SESSION['Alloc']->AllocTrans = $_SESSION['AllocTrans'];
	$_SESSION['Alloc']->SupplierID = $MyRow['supplierno'];
	$_SESSION['Alloc']->SuppName = $MyRow['suppname'];
	$_SESSION['Alloc']->TransType = $MyRow['type'];
	$_SESSION['Alloc']->TransTypeName = __($MyRow['typename']);
	$_SESSION['Alloc']->TransNo = $MyRow['transno'];
	$_SESSION['Alloc']->TransExRate = $MyRow['rate'];
	$_SESSION['Alloc']->TransAmt = $MyRow['total'];
	$_SESSION['Alloc']->PrevDiffOnExch = $MyRow['diffonexch'];
	$_SESSION['Alloc']->TransDate = ConvertSQLDate($MyRow['trandate']);
	$_SESSION['Alloc']->CurrDecimalPlaces = $MyRow['decimalplaces'];

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
			FROM supptrans INNER JOIN systypes
			ON supptrans.type = systypes.typeid
			WHERE supptrans.settled=0
			AND abs(ovamount+ovgst-alloc)>0.009
			AND supplierno='" . $_SESSION['Alloc']->SupplierID . "'";

	$ErrMsg = __('There was a problem retrieving the transactions available to allocate to');

	$Result = DB_query($SQL, $ErrMsg);

	while ($MyRow=DB_fetch_array($Result)){
		$_SESSION['Alloc']->add_to_AllocsAllocn ($MyRow['id'],
												__($MyRow['typename']),
												$MyRow['transno'],
												ConvertSQLDate($MyRow['trandate']),
												$MyRow['suppreference'],
												0,
												$MyRow['total'],
												$MyRow['rate'],
												$MyRow['diffonexch'],
												$MyRow['diffonexch'],
												$MyRow['alloc'],
												'NA');
	}

	/* Now get trans that might have previously been allocated to by this trans
	NB existing entries where still some of the trans outstanding entered from
	above logic will be overwritten with the prev alloc detail below */

	$SQL = "SELECT supptrans.id,
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
			FROM supptrans INNER JOIN systypes
			ON supptrans.type = systypes.typeid
			INNER JOIN suppallocs
			ON supptrans.id=suppallocs.transid_allocto
			WHERE suppallocs.transid_allocfrom='" . $_SESSION['AllocTrans'] .
			"' AND supplierno='" . $_SESSION['Alloc']->SupplierID . "'";

	$ErrMsg = __('There was a problem retrieving the previously allocated transactions for modification');

	$Result = DB_query($SQL, $ErrMsg);

	while ($MyRow = DB_fetch_array($Result)){

		$DiffOnExchThisOne = ($MyRow['amt']/$MyRow['rate']) - ($MyRow['amt']/$_SESSION['Alloc']->TransExRate);

		$_SESSION['Alloc']->add_to_AllocsAllocn ($MyRow['id'],
												__($MyRow['typename']),
												$MyRow['transno'],
												ConvertSQLDate($MyRow['trandate']), $MyRow['suppreference'], $MyRow['amt'],
												$MyRow['total'],
												$MyRow['rate'],
												$DiffOnExchThisOne,
												($MyRow['diffonexch'] - $DiffOnExchThisOne),
												$MyRow['prevallocs'],
												$MyRow['allocid']);
	}
}

if (isset($_POST['AllocTrans'])){

	echo '<input type="hidden" name="AllocTrans" value="' . $_POST['AllocTrans'] . '" />';

	/*Show the transaction being allocated and the potential trans it could be allocated to
        and those where there is already an existing allocation */

        echo '<div class="centre">
				<font color="blue">' . __('Allocation of supplier') . ' ' .
        		 $_SESSION['Alloc']->TransTypeName . ' ' . __('number') . ' ' .
        		 $_SESSION['Alloc']->TransNo . ' ' . __('from') . ' ' .
        		 $_SESSION['Alloc']->SupplierID . ' - <b>' .
        		 $_SESSION['Alloc']->SuppName . '</b>, ' . __('dated') . ' ' .
        		 $_SESSION['Alloc']->TransDate;

        if ($_SESSION['Alloc']->TransExRate != 1){
	     	  echo '<br />' . __('Amount in supplier currency'). ' <b>' .
	     	  		 locale_number_format(-$_SESSION['Alloc']->TransAmt,$_SESSION['Alloc']->CurrDecimalPlaces) . '</b><i> (' .
	     	  		 __('converted into local currency at an exchange rate of') . ' ' .
	     	  		 $_SESSION['Alloc']->TransExRate . ')</i><p>';

        } else {
		     echo '<br />' . __('Transaction total') . ': <b>' . locale_number_format(-$_SESSION['Alloc']->TransAmt,$_SESSION['Alloc']->CurrDecimalPlaces) . '</b></div>';
        }

    /*Now display the potential and existing allocations put into the array above */

		echo '<table class="selection">
			<thead>
				<tr>
							<th class="SortedColumn">' . __('Type') . '</th>
				 			<th class="SortedColumn">' . __('Trans') . '<br />' . __('Number') . '</th>
							<th class="SortedColumn">' . __('Trans')  . '<br />' . __('Date') . '</th>
							<th class="SortedColumn">' . __('Supp') . '<br />' . __('Ref') . '</th>
							<th class="SortedColumn">' . __('Total') . '<br />' . __('Amount')  . '</th>
							<th class="SortedColumn">' . __('Yet to') . '<br />' . __('Allocate') . '</th>
							<th class="SortedColumn">' . __('This') . '<br />' . __('Allocation') . '</th>
				</tr>
			</thead>
			<tbody>';

		$Counter = 0;
		$TotalAllocated = 0;

		foreach ($_SESSION['Alloc']->Allocs as $AllocnItem) {

	    $YetToAlloc = ($AllocnItem->TransAmount - $AllocnItem->PrevAlloc);

	    echo '<tr class="striped_row">
			<td>' . $AllocnItem->TransType . '</td>
			<td class="number">' . $AllocnItem->TypeNo . '</td>
			<td class="date">' . $AllocnItem->TransDate . '</td>
			<td>' . $AllocnItem->SuppRef . '</td>
			<td class="number">' . locale_number_format($AllocnItem->TransAmount,$_SESSION['Alloc']->CurrDecimalPlaces) . '</td>
			<td class="number">' . locale_number_format($YetToAlloc,$_SESSION['Alloc']->CurrDecimalPlaces) . '<input type="hidden" name="YetToAlloc' . $Counter . '" value="' . $YetToAlloc . '" /></td>';
		 if (ABS($AllocnItem->AllocAmt-$YetToAlloc) < 0.01){
			echo '<td class="number"><input type="checkbox" name="All' .  $Counter . '" checked="checked" />';
	    } else {
	    	echo '<td class="number"><input type="checkbox" name="All' .  $Counter . '" />';
	    }
		echo '<input type="text" class="number" name="Amt' . $Counter .'" maxlength="12" size="13" value="' . locale_number_format($AllocnItem->AllocAmt,$_SESSION['Alloc']->CurrDecimalPlaces) . '" /><input type="hidden" name="AllocID' . $Counter .'" value="' . $AllocnItem->ID . '" /></td></tr>';

	    $TotalAllocated = $TotalAllocated + $AllocnItem->AllocAmt;
	    $Counter++;
   }

   echo '</tbody>
		<tfoot>
			<tr>
			<td colspan="5" class="number"><b><u>' . __('Total Allocated') . ':</u></b></td>
			<td class="number"><b><u>' .  locale_number_format($TotalAllocated,$_SESSION['Alloc']->CurrDecimalPlaces) . '</u></b></td>
			</tr>
			<tr>
			<td colspan="5" class="number"><b>' . __('Left to allocate') . '</b></td>
			<td class="number"><b>' . locale_number_format(-$_SESSION['Alloc']->TransAmt - $TotalAllocated,$_SESSION['Alloc']->CurrDecimalPlaces) . '</b></td>
		</tr>
		</tfoot>
		</table>';

   echo '<div class="centre">
			<input type="hidden" name="TotalNumberOfAllocs" value="' . $Counter . '" />
			<br />
			<input type="submit" name="RefreshAllocTotal" value="' . __('Recalculate Total To Allocate') . '" />
			<input type="submit" name="UpdateDatabase" value="' . __('Process Allocations') . '" />
			<input type="reset" name="Cancel" value="' . __('Cancel') . '" />
		</div>';

} elseif(isset($_GET['SupplierID'])){

  /*page called with a supplier code  so show the transactions to allocate
  specific to the supplier selected */

  echo '<input type="hidden" name="SupplierID" value="' . $_GET['SupplierID'] . '" />';

  /*Clear any previous allocation records */

  unset($_SESSION['Alloc']);

  $SQL = "SELECT id,
		  		transno,
				typename,
				type,
				suppliers.supplierid,
				suppname,
				trandate,
		  		suppreference,
				supptrans.rate,
				ovamount+ovgst AS total,
				alloc,
				decimalplaces AS currdecimalplaces
		  	FROM supptrans INNER JOIN suppliers
		  	ON supptrans.supplierno=suppliers.supplierid
		  	INNER JOIN systypes
		  	ON supptrans.type=systypes.typeid
		  	INNER JOIN currencies
		  	ON suppliers.currcode=currencies.currabrev
		  	WHERE suppliers.supplierid='" . $_GET['SupplierID'] ."'
			AND (supptrans.type=21 OR supptrans.type=22)
			AND settled=0
			ORDER BY id";

  $Result = DB_query($SQL);
  if (DB_num_rows($Result) == 0){
	prnMsg(__('There are no outstanding payments or credits yet to be allocated for this supplier'),'info');
	include('includes/footer.php');
	exit();
  }
  echo '<table class="selection">';

	echo '<thead>
			<tr>
				<th class="SortedColumn">' . __('Trans Type')  . '</th>
				<th class="SortedColumn">' . __('Supplier') . '</th>
				<th class="SortedColumn">' . __('Number') . '</th>
				<th class="SortedColumn">' . __('Date') .  '</th>
				<th class="SortedColumn">' . __('Total') . '</th>
				<th class="SortedColumn">' . __('To Alloc') . '</th>
			</tr>
		</thead>
	<tbody>';

  /* set up table of TransType - Supplier - Trans No - Date - Total - Left to alloc  */

  $RowCounter = 0;

  while ($MyRow = DB_fetch_array($Result)) {

	echo '<tr class="striped_row">
			<td>', __($MyRow['typename']), '</td>
			<td>', $MyRow['suppname'], '</td>
			<td>', $MyRow['transno'], '</td>
			<td class="date">', ConvertSQLDate($MyRow['trandate']), '</td>
			<td class="number">', locale_number_format($MyRow['total'],$MyRow['currdecimalplaces']), '</td>
			<td class="number">', locale_number_format($MyRow['total']-$MyRow['alloc'], $MyRow['currdecimalplaces']), '</td>
			<td><a href="', htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') . '?AllocTrans=', $MyRow['id'], '">' . __('Allocate')  . '</a></td>
		</tr>';

  }

} else { /* show all outstanding payments and credits to be allocated */

  /*Clear any previous allocation records */

  unset($_SESSION['Alloc']->Allocs);
  unset($_SESSION['Alloc']);

  $SQL = "SELECT id,
		  		transno,
				typename,
				type,
				suppliers.supplierid,
				suppname,
				trandate,
		  		suppreference,
				supptrans.rate,
				ovamount+ovgst AS total,
				alloc,
				decimalplaces AS currdecimalplaces
		  	FROM supptrans INNER JOIN suppliers
			ON supptrans.supplierno=suppliers.supplierid
			INNER JOIN systypes
			ON supptrans.type=systypes.typeid
			INNER JOIN currencies
			ON suppliers.currcode=currencies.currabrev
			WHERE (supptrans.type=21 OR supptrans.type=22)
			AND settled=0
			ORDER BY id";

  $Result = DB_query($SQL);

  echo '<table class="selection">';

  echo '<thead>
			<tr>
				<th class="SortedColumn">' . __('Trans Type') . '</th>
				<th class="SortedColumn">' . __('Supplier') . '</th>
		 		<th class="SortedColumn">' . __('Number') . '</th>
		  		<th class="SortedColumn">' . __('Date') . '</th>
		  		<th class="SortedColumn">' . __('Total') . '</th>
		  		<th class="SortedColumn">' . __('To Alloc') . '</th>
				<th class="SortedColumn">' . __('More Info') . '</th>
			</tr>
		</thead>';

  /* set up table of Tran Type - Supplier - Trans No - Date - Total - Left to alloc  */
	echo '<tbody>';
  $RowCounter = 0;
  while ($MyRow = DB_fetch_array($Result)) {

	echo '<tr class="striped_row">
			<td>', __($MyRow['typename']), '</td>
			<td>', $MyRow['suppname'], '</td>
			<td>', $MyRow['transno'], '</td>
			<td class="date">', ConvertSQLDate($MyRow['trandate']), '</td>
			<td class="number">', locale_number_format($MyRow['total'],$MyRow['currdecimalplaces']), '</td>
			<td class="number">', locale_number_format($MyRow['total']-$MyRow['alloc'],$MyRow['currdecimalplaces']), '</td>
			<td><a href="', htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') . '?AllocTrans=', $MyRow['id'], '">' . __('Allocate') . '</a></td>
		</tr>';

  }  //END WHILE LIST LOOP

  echo '</tbody>
	</table>';

  if (DB_num_rows($Result) == 0) {
	prnMsg(__('There are no allocations to be done'),'info');
  }

} /* end of else if not a SupplierID or transaction called with the URL */

echo '</div>
      </form>';
include('includes/footer.php');

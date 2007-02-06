<?php
/* $Revision: 1.12 $ */
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
include('includes/DefineCustAllocsClass.php');

$PageSecurity = 3;

include('includes/session.inc');

$title = _('Customer Receipt') . '/' . _('Credit Note Allocations');

include('includes/header.inc');
include('includes/SQL_CommonFunctions.inc');

if (isset($_POST['UpdateDatabase']) OR isset($_POST['RefreshAllocTotal'])) {

	if (!isset($_SESSION['Alloc'])){
		prnMsg(_('Allocations can not be processed again') . '. ' . _('If you hit refresh on this page after having just processed an allocation') . ', ' . _('try to use the navigation links provided rather than the back button') . ', ' . _('to avoid this message in future'),'info');
		include('includes/footer.inc');
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

	for ($AllocCounter=0;$AllocCounter < $_POST['TotalNumberOfAllocs']; $AllocCounter++){

		if (isset($_POST['Amt' . $AllocCounter])){ // only do the below for allocatable charge amounts
						// there will be no Amtxx field for credits/receipt so skip them
			if (!is_numeric($_POST['Amt' . $AllocCounter])){
			$_POST['Amt' . $AllocCounter]=0;
			}
			if ($_POST['Amt' . $AllocCounter]<0){
				prnMsg(_('The entry for the amount to allocate was negative') . '. ' . _('A positive allocation amount is expected') . '.','warn');
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
	   echo '<BR><HR>';
	   prnMsg(_('These allocations cannot be processed because the amount allocated is more than the amount of the').' ' . $_SESSION['Alloc']->TransTypeName  . ' '._('being allocated') . '<BR>' . _('Total allocated').' = ' . $TotalAllocated . ' '._('and the total amount of the') .' ' . $_SESSION['Alloc']->TransTypeName  . ' '._('was').' ' . -$_SESSION['Alloc']->TransAmt,'error');
	   $InputError=1;
	}
}

if (isset($_POST['UpdateDatabase'])){

	If ($InputError==0){ /* ie all the traps were passed */
	/* actions to take having checked that the input is sensible
	1st set up a transaction on this thread*/
		$Result=DB_query('BEGIN',$db);

		foreach ($_SESSION['Alloc']->Allocs as $AllocnItem) {

			  if ($AllocnItem->OrigAlloc >0 AND ($AllocnItem->OrigAlloc != $AllocnItem->AllocAmt)){
			  /*Orignial allocation was not 0 and it has now changed
			    need to delete the old allocation record */

				$SQL = 'DELETE FROM custallocns WHERE id = ' . $AllocnItem->PrevAllocRecordID;
        			$ErrMsg = _('CRITICAL ERROR') . '! ' . _('NOTE DOWN THIS ERROR AND SEEK ASSISTANCE') . ': ' . _('The existing allocation for').' '. $AllocnItem->TransType .' '. $AllocnItem->TypeNo. ' ' . _('could not be deleted because');
        			$DbgMsg = _('The following SQL to delete the allocation record was used');
				$Result=DB_query($SQL,$db,$ErrMsg,$DbgMsg,true);
			 }
			 if ($AllocnItem->OrigAlloc != $AllocnItem->AllocAmt){
			 /*Only when there has been a change to the allocated amount
			 do we need to insert a new allocation record and update
			 the transaction with the new alloc amount and diff on exch */
				if ($AllocnItem->AllocAmt >0){
					$SQL = "INSERT INTO custallocns (datealloc,
					     					amt,
										transid_allocfrom,
										transid_allocto)
								VALUES ('" . FormatDateForSQL(date('d/m/Y')) . "',
									" . $AllocnItem->AllocAmt . ',
									' . $_SESSION['Alloc']->AllocTrans . ',
									' . $AllocnItem->ID . ')';

           				$ErrMsg = _('CRITICAL ERROR') . '! ' . _('NOTE DOWN THIS ERROR AND SEEK ASSISTANCE') . ': ' . _('The customer allocation record for').' '. $AllocnItem->TransType .' '. $AllocnItem->TypeNo. ' ' . _('could not be inserted because');
           				$DbgMsg = _('The following SQL to delete the allocation record was used');
		         		$Result=DB_query($SQL,$db,$ErrMsg,$DbgMsg,true);
				}
				$NewAllocTotal = $AllocnItem->PrevAlloc + $AllocnItem->AllocAmt;

				if (abs($NewAllocTotal-$AllocnItem->TransAmount) < 0.005){
					$Settled =1;
				} else {
					$Settled =0;
				}

				$SQL = 'UPDATE debtortrans SET diffonexch=' . $AllocnItem->DiffOnExch . ',
								alloc = ' . $NewAllocTotal . ',
								settled = ' . $Settled . '
						WHERE id = ' . $AllocnItem->ID;

				$ErrMsg = _('CRITICAL ERROR') . '! ' . _('NOTE DOWN THIS ERROR AND SEEK ASSISTANCE') . ': ' . _('The debtor transaction record could not be modified for the allocation against it because');
        			$DbgMsg = _('The following SQL to delete the allocation record was used');
        			$Result=DB_query($SQL,$db,$ErrMsg,$DbgMsg,true);
			 } /*end if the new allocation is different to what it was before */

		}  /*end of the loop through the array of allocations made */

		/*Now update the receipt or credit note with the amount allocated
		and the new diff on exchange */


		if (abs($TotalAllocated+$_SESSION['Alloc']->TransAmt)<0.01){
		   $Settled = 1;
		} else {
		   $Settled = 0;
		}

		$SQL = 'UPDATE debtortrans SET alloc = ' .  -$TotalAllocated . ', diffonexch = ' . -$TotalDiffOnExch . ', settled=' . $Settled . ' WHERE id = ' . $_POST['AllocTrans'];

     		$ErrMsg = _('CRITICAL ERROR') . '! ' . _('NOTE DOWN THIS ERROR AND SEEK ASSISTANCE') . ': ' . _('The debtor receipt or credit note transaction could not be modified for the new allocation and exchange difference because');
     		$DbgMsg = _('The following SQL to delete the allocation record was used');
     		$Result=DB_query($SQL,$db,$ErrMsg,$DbgMsg,true);


		/*Almost there ... if there is a change in the total diff on exchange
		 and if the GLLink to debtors is active - need to post diff on exchange to GL */
		$MovtInDiffOnExch = -$_SESSION['Alloc']->PrevDiffOnExch - $TotalDiffOnExch;

		if ($MovtInDiffOnExch !=0){

			if ($_SESSION['CompanyRecord']['gllink_debtors']==1){

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
						' . $_SESSION['CompanyRecord']['exchangediffact'] . ",
						'',
						" . $MovtInDiffOnExch . ')';

           			$ErrMsg = _('CRITICAL ERROR') . '! ' . _('NOTE DOWN THIS ERROR AND SEEK ASSISTANCE') . ': ' . _('The GL entry for the difference on exchange arising out of this allocation could not be inserted because');
           			$DbgMsg = _('The following SQL to delete the allocation record was used');
           			$Result=DB_query($SQL,$db,$ErrMsg,$DbgMsg,true);

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
							' . $_SESSION['CompanyRecord']['debtorsact'] . ",
							'',
							" . -$MovtInDiffOnExch . ')';

           			$ErrMsg = _('CRITICAL ERROR') . '! ' . _('NOTE DOWN THIS ERROR AND SEEK ASSISTANCE') . ': ' . _('The GL entry for the difference on exchange arising out of this allocation could not be inserted because');
           			$DbgMsg = _('The following SQL to delete the allocation record was used');
           			$Result=DB_query($SQL,$db,$ErrMsg,$DbgMsg,true);
			}

		}
	 /* OK Commit the transaction */
		$ErrMsg = _('CRITICAL ERROR') . '! ' . _('NOTE DOWN THIS ERROR AND SEEK ASSISTANCE') . ': ' . _('The updates and insertions arising from this allocation could not be committed to the database');
      		$DbgMsg = _('The following SQL to delete the allocation record was used');
      		$Result=DB_query('COMMIT',$db,$ErrMsg,$DbgMsg,true);
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

	
	if (isset($_SESSION['Alloc'])){
		unset($_SESSION['Alloc']->Allocs);
		unset($_SESSION['Alloc']);
	}

	$_SESSION['Alloc'] = new Allocation;
	/*The session varibale AllocTrans is set from the passed variable AllocTrans
	on the first pass */
	$_POST['AllocTrans']=$_GET['AllocTrans'];

	$SQL= "SELECT systypes.typename,
			debtortrans.type,
			debtortrans.transno,
			debtortrans.trandate,
			debtortrans.debtorno,
			debtorsmaster.name,
			rate,
			(debtortrans.ovamount+debtortrans.ovgst+debtortrans.ovfreight+debtortrans.ovdiscount) as total,
			debtortrans.diffonexch,
			debtortrans.alloc
		FROM debtortrans,
			systypes,
			debtorsmaster
		WHERE debtortrans.type = systypes.typeid
		AND debtortrans.debtorno = debtorsmaster.debtorno
		AND debtortrans.id=" . $_POST['AllocTrans'];

	$ErrMsg = _('There was a problem retrieving the information relating the transaction selected') . '. ' . _('Allocations are unable to proceed') . '.';
	$DbgMsg = _('The following SQL to delete the allocation record was used');
	$Result=DB_query($SQL,$db,$ErrMsg,$DbgMsg,true);

	$myrow = DB_fetch_array($Result);

	$_SESSION['Alloc']->AllocTrans = $_POST['AllocTrans'];
	$_SESSION['Alloc']->DebtorNo = $myrow['debtorno'];
	$_SESSION['Alloc']->CustomerName = $myrow['name'];;
	$_SESSION['Alloc']->TransType = $myrow['type'];
	$_SESSION['Alloc']->TransTypeName = $myrow['typename'];
	$_SESSION['Alloc']->TransNo = $myrow['transno'];
	$_SESSION['Alloc']->TransExRate = $myrow['rate'];
	$_SESSION['Alloc']->TransAmt = $myrow['total'];
	$_SESSION['Alloc']->PrevDiffOnExch = $myrow['diffonexch'];
	$_SESSION['Alloc']->TransDate = ConvertSQLDate($myrow['trandate']);

	/* Now populate the array of possible (and previous actual) allocations for this customer */
	/*First get the transactions that have outstanding balances ie Total-Alloc >0 */

	$SQL= "SELECT debtortrans.id,
			typename,
			transno,
			trandate,
			rate,
			ovamount+ovgst+ovfreight+ovdiscount as total,
			diffonexch,
			alloc
		FROM debtortrans,
			systypes
		WHERE debtortrans.type = systypes.typeid
		AND debtortrans.settled=0
		AND debtorno='" . $_SESSION['Alloc']->DebtorNo . "'
		ORDER BY debtortrans.id";

    $ErrMsg = _('There was a problem retrieving the transactions available to allocate to');
    $DbgMsg = _('The following SQL to delete the allocation record was used');
    $Result=DB_query($SQL,$db,$ErrMsg,$DbgMsg,true);

	while ($myrow=DB_fetch_array($Result)){
		$_SESSION['Alloc']->add_to_AllocsAllocn ($myrow['id'],
							$myrow['typename'],
							$myrow['transno'],
							ConvertSQLDate($myrow['trandate']),
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

	$SQL= "SELECT debtortrans.id,
			typename,
			transno,
			trandate,
			rate,
			ovamount+ovgst+ovfreight+ovdiscount AS total,
			diffonexch,
			debtortrans.alloc-custallocns.amt AS prevallocs,
			amt,
			custallocns.id AS allocid
		FROM debtortrans,
			systypes,
			custallocns
		WHERE debtortrans.type = systypes.typeid
		AND debtortrans.id=custallocns.transid_allocto
		AND custallocns.transid_allocfrom=" . $_POST['AllocTrans'] . "
		AND debtorno='" . $_SESSION['Alloc']->DebtorNo . "'
		ORDER BY debtortrans.id";

    $ErrMsg = _('There was a problem retrieving the previously allocated transactions for modification');
    $DbgMsg = _('The following SQL to delete the allocation record was used');
    $Result=DB_query($SQL,$db,$ErrMsg,$DbgMsg,true);

	while ($myrow=DB_fetch_array($Result)){

		$DiffOnExchThisOne = ($myrow['amt']/$myrow['rate']) - ($myrow['amt']/$_SESSION['Alloc']->TransExRate);

		$_SESSION['Alloc']->add_to_AllocsAllocn ($myrow['id'],
							$myrow['typename'],
							$myrow['transno'],
							ConvertSQLDate($myrow['trandate']),
							$myrow['amt'],
							$myrow['total'],
							$myrow['rate'],
							$DiffOnExchThisOne,
							($myrow['diffonexch'] - $DiffOnExchThisOne),
							$myrow['prevallocs'],
							$myrow['allocid']);

	}
}

if (isset($_POST['AllocTrans'])){

	echo "<FORM ACTION='" . $_SERVER['PHP_SELF'] . '?' . SID . "' METHOD=POST>";
	echo "<INPUT TYPE=HIDDEN NAME='AllocTrans' VALUE=" . $_POST['AllocTrans'] . '>';

	/*Show the transaction being allocated and the potential trans it could be allocated to
        and those where there is already an existing allocation */

        echo '<HR><CENTER><FONT COLOR=BLUE>'._('Allocation of customer'). ' ' . $_SESSION['Alloc']->TransTypeName . ' '._('number').' ' . $_SESSION['Alloc']->TransNo . ' '._('from').' ' . $_SESSION['Alloc']->DebtorNo . ' - <B>' . $_SESSION['Alloc']->CustomerName . '</B>, '._('dated').' ' . $_SESSION['Alloc']->TransDate;

        if ($_SESSION['Alloc']->TransExRate!=1){
	     echo '<BR>'._('Amount in customer currency').' <B>' . number_format(-$_SESSION['Alloc']->TransAmt,2) . '</B><i> ('._('converted into local currency at an exchange rate of'). ' ' . $_SESSION['Alloc']->TransExRate . ')</i><P>';
        } else {
	     echo '<BR>'._('Transaction total'). ': <B>' . -$_SESSION['Alloc']->TransAmt . '</B>';
        }

        echo '<HR>';
   /*Now display the potential and existing allocations put into the array above */

   	$TableHeader = "<TR>
				<TD class='tableheader'>"._('Type')."</TD>
				<TD class='tableheader'>"._('Trans').'<BR>'._('Number')."</TD>
				<TD class='tableheader'>"._('Trans').'<BR>'._('Date')."</TD>
				<TD class='tableheader'>"._('Total').'<BR>'._('Amount')."</TD>
				<TD class='tableheader'>"._('Yet to').'<BR>'._('Allocate')."</TD>
				<TD class='tableheader'>"._('This').'<BR>'._('Allocation')."</TD>
			</TR>";

        echo '<TABLE CELLPADDING=2 COLSPAN=7 BORDER=0>' . $TableHeader;

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

	    echo "<TD>$AllocnItem->TransType</TD>
	    	<TD>$AllocnItem->TypeNo</TD>
		<TD ALIGN=RIGHT>$AllocnItem->TransDate</TD>
		<TD ALIGN=RIGHT>" . number_format($AllocnItem->TransAmount,2) . '</TD>
		<TD ALIGN=RIGHT>' . number_format($YetToAlloc,2);

	    if ($AllocnItem->TransAmount < 0) {
	    	echo '</TD></TR>';
	    } else {
	    	echo "<input type=hidden name='YetToAlloc" . $Counter . "' value=" . round($YetToAlloc,2) . '></TD>';
	    	echo "<TD ALIGN=RIGHT><input type='checkbox' name='All" .  $Counter . "'";
	    	if (ABS($AllocnItem->AllocAmt-$YetToAlloc)<0.01){
			echo ' VALUE=' . True . '>';
	    	} else {
	    		echo '>';
	    	}
	    	echo "<input type=text name='Amt" . $Counter ."' maxlength=12 SIZE=13 value=" . round($AllocnItem->AllocAmt,2) . "><input type=hidden name='AllocID" . $Counter . "' value=" . $AllocnItem->ID . '></TD></TR>';
	    }

	    $TotalAllocated =$TotalAllocated + round($AllocnItem->AllocAmt,2);

	    $Counter++;
   }


   echo "<TR>
   		<TD COLSPAN=5 ALIGN=RIGHT><B>"._('Total Allocated').':</B></TD>
		<TD ALIGN=RIGHT><B><U>' . number_format($TotalAllocated,2) . '</U></B></TD>
	</TR>';

   echo '<TR>
   		<TD COLSPAN=5 ALIGN=RIGHT><B>'._('Left to allocate').'</B></TD>
   		<TD ALIGN=RIGHT><B>' . number_format(-$_SESSION['Alloc']->TransAmt - $TotalAllocated,2) . '</B></TD>
	</TR>
	</TABLE>';

   echo "<INPUT TYPE=HIDDEN NAME='TotalNumberOfAllocs' VALUE=$Counter>";

   echo "<INPUT TYPE=SUBMIT NAME='RefreshAllocTotal' VALUE="._('Recalculate Total To Allocate').'>';
   echo "<INPUT TYPE=SUBMIT NAME=UpdateDatabase VALUE="._('Process Allocations').'>';

} elseif(isset($_GET['DebtorNo'])){
  /*page called with customer code  so show the transactions to allocate
  specific to the customer selected */

  /*Clear any previous allocation records */
  unset($_SESSION['Alloc']->Allocs);
  unset($_SESSION['Alloc']);

  $sql = "SELECT id,
  		transno,
		typename,
		type,
		debtortrans.debtorno,
		name,
		trandate,
		reference,
		rate,
		ovamount+ovgst-ovdiscount+ovfreight AS total,
		alloc
	FROM debtortrans,
		debtorsmaster,
		systypes
	WHERE debtortrans.type=systypes.typeid
	AND debtortrans.debtorno=debtorsmaster.debtorno
	AND debtortrans.debtorno='" . $_GET['DebtorNo'] . "'
	AND (type=12 or type=11)
	AND settled=0
	ORDER BY id";

  $result = DB_query($sql,$db);
  if (DB_num_rows($result)==0){
  	prnMsg(_('There are no outstanding receipts or credits yet to be allocated for this customer'),'info');
	include('includes/footer.inc');
      	exit;
  }
  echo '<CENTER><table>';
  echo "<tr>
  		<td class='tableheader'>"._('Trans Type')."</td>
		<td class='tableheader'>"._('Customer')."</td>
		<td class='tableheader'>"._('Number')."</td>
		<td class='tableheader'>"._('Date')."</td>
		<td class='tableheader'>"._('Total')."</td>
		<td class='tableheader'>"._('To Alloc')."</td>
		</tr><BR>";
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

	printf("<td>%s</td>
		<td>%s</td>
		<td>%s</td>
		<td>%s</td>
		<td ALIGN=RIGHT>%0.2f</td>
		<td ALIGN=RIGHT>%0.2f</td>
		<td><a href='%sAllocTrans=%s'>%s</td>
		</tr>",
		$myrow['typename'],
		$myrow['name'],
		$myrow['transno'],
		ConvertSQLDate($myrow['trandate']),
		$myrow['total'],
		$myrow['total']-$myrow['alloc'],
		$_SERVER['PHP_SELF'] . '?' . SID,
		$myrow['id'],
		_('Allocate'));
  }
} else { /* show all outstanding receipts and credits to be allocated */
  /*Clear any previous allocation records */
  unset($_SESSION['Alloc']->Allocs);
  unset($_SESSION['Alloc']);

  $sql = "SELECT id,
  		transno,
		typename,
		type,
		debtortrans.debtorno,
		name,
		trandate,
		reference,
		rate,
		ovamount+ovgst+ovdiscount+ovfreight as total,
		alloc
	FROM debtortrans,
		debtorsmaster,
		systypes
	WHERE debtortrans.type=systypes.typeid
	AND debtortrans.debtorno=debtorsmaster.debtorno
	AND (type=12 or type=11)
	AND SETTLED=0
	AND debtortrans.ovamount<0
	ORDER BY id";

  $result = DB_query($sql,$db);

  echo '<CENTER><table>';
  echo "<tr>
  		<td class='tableheader'>"._('Trans Type')."</td>
		<td class='tableheader'>"._('Customer')."</td>
	    <td class='tableheader'>"._('Cust No.')."</td>
		<td class='tableheader'>"._('Number')."</td>
		<td class='tableheader'>"._('Date')."</td>
		<td class='tableheader'>"._('Total')."</td>
		<td class='tableheader'>"._('To Alloc')."</td>
	</tr><BR>";
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

	printf("<td>%s</td>
		<td>%s</td>
		<td>%s</td>
		<td>%s</td>
		<td>%s</td>
		<td ALIGN=RIGHT>%0.2f</td>
		<td ALIGN=RIGHT>%0.2f</td>
		<td><a href='%sAllocTrans=%s'>%s</td>
		</tr>",
		$myrow['typename'],
		$myrow['name'],
		$myrow['debtorno'],
		$myrow['transno'],
		ConvertSQLDate($myrow['trandate']),
		$myrow['total'],
		$myrow['total']-$myrow['alloc'],
		$_SERVER['PHP_SELF'] . '?' . SID,
		$myrow['id'],
		_('Allocate'));

  }
  //END WHILE LIST LOOP
  echo '</table></CENTER>';
  if (DB_num_rows($result) == 0) {
	prnMsg(_('There are no allocations to be done'),'info');
  }

} /* end of else if not a debtorno or transaction called with the URL */


include('includes/footer.inc');

?>

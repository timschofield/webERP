<?php

/*
Call this page with:
	1. A DebtorNo to show all outstanding receipts or credits yet to be allocated.
*/

// NB: these classes are not autoloaded, and their definition has to be included before the session is started (in session.php)
include('includes/DefineCustAllocsClass.php');

require(__DIR__ . '/includes/session.php');

$Title = __('Automatic Customer Receipt') . '/' . __('Credit Note Allocations');
$ViewTopic = 'ARTransactions';
$BookMark = 'CustomerAllocations';
include('includes/header.php');

include('includes/SQL_CommonFunctions.php');

if (isset($_GET['DebtorNo'])) {
	// Page called with customer code

	$SQL = "SELECT debtortrans.id,
				debtortrans.transno,
				systypes.typename,
				debtortrans.type,
				debtortrans.debtorno,
				debtorsmaster.name,
				debtortrans.trandate,
				debtortrans.reference,
				debtortrans.rate,
				debtortrans.ovamount+debtortrans.ovgst+debtortrans.ovdiscount+debtortrans.ovfreight as total,
				debtortrans.alloc,
				currencies.decimalplaces AS currdecimalplaces,
				debtorsmaster.currcode
			FROM debtortrans INNER JOIN debtorsmaster
			ON debtortrans.debtorno=debtorsmaster.debtorno
			INNER JOIN systypes
			ON debtortrans.type=systypes.typeid
			INNER JOIN currencies
			ON debtorsmaster.currcode=currencies.currabrev
			WHERE debtortrans.debtorno='" . $_GET['DebtorNo'] . "'
			AND ( (debtortrans.type=12 AND debtortrans.ovamount<0) OR debtortrans.type=11)
			AND debtortrans.settled=0
			ORDER BY debtortrans.id";

	$Result = DB_query($SQL);

	if (DB_num_rows($Result)==0) {
		prnMsg(__('No outstanding receipts or credits to be allocated for this customer'),'info');
		include('includes/footer.php');
		exit();
	}
	 echo '<table class="selection">';
	echo $TableHeader;

	while ($MyRow = DB_fetch_array($Result)) {
		unset($_SESSION['Alloc']->Allocs);
		unset($_SESSION['Alloc']);
		$_SESSION['Alloc'] = new Allocation;
		$_SESSION['Alloc']->AllocTrans 		= $MyRow['id'];
		$_SESSION['Alloc']->DebtorNo		= $MyRow['debtorno'];
		$_SESSION['Alloc']->CustomerName	= $MyRow['name'];
		$_SESSION['Alloc']->TransType		= $MyRow['type'];
		$_SESSION['Alloc']->TransTypeName	= $MyRow['typename'];
		$_SESSION['Alloc']->TransNo		= $MyRow['transno'];
		$_SESSION['Alloc']->TransExRate	= $MyRow['rate'];
		$_SESSION['Alloc']->TransAmt		= $MyRow['total'];
		$_SESSION['Alloc']->PrevDiffOnExch = $MyRow['diffonexch'];
		$_SESSION['Alloc']->TransDate		= ConvertSQLDate($MyRow['trandate']);
		$_SESSION['Alloc']->CurrDecimalPlaces = $MyRow['decimalplaces'];

		// Now get invoices or neg receipts that have outstanding balances
		$SQL = "SELECT debtortrans.id,
					typename,
					transno,
					trandate,
					rate,
					ovamount+ovgst+ovfreight+ovdiscount as total,
					diffonexch,
					alloc
				FROM debtortrans INNER JOIN systypes
				ON debtortrans.type = systypes.typeid
				WHERE debtortrans.settled=0
				AND (systypes.typeid=10 OR (systypes.typeid=12 AND ovamount>0))
				AND debtorno='" . $_SESSION['Alloc']->DebtorNo . "'
				ORDER BY debtortrans.id DESC";
		$TransResult = DB_query($SQL);
		$BalToAllocate = $_SESSION['Alloc']->TransAmt - $MyRow['alloc'];
		while ($MyAlloc=DB_fetch_array($TransResult) AND $BalToAllocate < 0) {
			if ($MyAlloc['total']-$MyAlloc['alloc']< abs($BalToAllocate)) {
				$ThisAllocation = $MyAlloc['total']-$MyAlloc['alloc'];
			} else {
				$ThisAllocation = abs($BalToAllocate);
			}
			$_SESSION['Alloc']->add_to_AllocsAllocn ($MyAlloc['id'],
													$MyAlloc['typename'],
													$MyAlloc['transno'],
													ConvertSQLDate($MyAlloc['trandate']),
													$ThisAllocation,
													$MyAlloc['total'],
													$MyAlloc['rate'],
													$MyAlloc['diffonexch'],
													$MyAlloc['diffonexch'],
													$MyAlloc['alloc'],
													'NA');
			$BalToAllocate += $ThisAllocation;//since $BalToAllocate is negative
		}
		DB_free_result($TransResult);

		ProcessAllocation();
	}
	echo '</table>';
}

include('includes/footer.php');

function ProcessAllocation() {
	if ($InputError==0) {
		//
		//========[ START TRANSACTION ]===========
		//
		$Error = '';
		DB_Txn_Begin();
		$AllAllocations = 0;
		$TotalDiffOnExch = 0;
		foreach ($_SESSION['Alloc']->Allocs as $AllocnItem) {

			if ($AllocnItem->AllocAmt > 0) {
				$SQL = "INSERT INTO
							custallocns (
							datealloc,
							amt,
							transid_allocfrom,
							transid_allocto
						) VALUES (
							CURRENT_DATE,
							'" . $AllocnItem->AllocAmt . "',
							'" . $_SESSION['Alloc']->AllocTrans . "',
							'" . $AllocnItem->ID . "'
						)";
				if( !$Result = DB_query($SQL) ) {
					$Error = __('Could not insert allocation record');
				}
			}
			$NewAllocTotal = $AllocnItem->PrevAlloc + $AllocnItem->AllocAmt;
			$AllAllocations = $AllAllocations + $AllocnItem->AllocAmt;
			$Settled = (abs($NewAllocTotal-$AllocnItem->TransAmount) < 0.005) ? 1 : 0;
			$TotalDiffOnExch += $AllocnItem->DiffOnExch;

			$SQL = "UPDATE debtortrans
					SET diffonexch='" . $AllocnItem->DiffOnExch . "',
					alloc = '" . $NewAllocTotal . "',
					settled = '" . $Settled . "'
					WHERE id = '" . $AllocnItem->ID."'";
			if( !$Result = DB_query($SQL) ) {
				$Error = __('Could not update difference on exchange');
			}
		}
		if (abs($TotalAllocated + $_SESSION['Alloc']->TransAmt) < 0.01) {
			$Settled = 1;
		} else {
			$Settled = 0;
		}
		// Update the receipt or credit note
		$SQL = "UPDATE debtortrans
				SET alloc = '" .  -$AllAllocations . "',
				diffonexch = '" . -$TotalDiffOnExch . "',
				settled='" . $Settled . "'
				WHERE id = '" . $_SESSION['Alloc']->AllocTrans . "'";

		if( !$Result = DB_query($SQL) ) {
			$Error = __('Could not update receipt or credit note');
		}

		// If GLLink to debtors active post diff on exchange to GL
		$MovtInDiffOnExch = -$_SESSION['Alloc']->PrevDiffOnExch - $TotalDiffOnExch;

		if ($MovtInDiffOnExch !=0) {
			if ($_SESSION['CompanyRecord']['gllink_debtors'] == 1) {
				$PeriodNo = GetPeriod($_SESSION['Alloc']->TransDate);
				$_SESSION['Alloc']->TransDate = FormatDateForSQL($_SESSION['Alloc']->TransDate);

					$SQL = "INSERT INTO gltrans (
								type,
								typeno,
								trandate,
								periodno,
								account,
								narrative,
								amount
							) VALUES (
								'" . $_SESSION['Alloc']->TransType . "',
								'" . $_SESSION['Alloc']->TransNo . "',
								'" . $_SESSION['Alloc']->TransDate . "',
								'" . $PeriodNo . "',
								'" . $_SESSION['CompanyRecord']['exchangediffact'] . "',
								'',
								'" . $MovtInDiffOnExch . "'
							)";
				if( !$Result = DB_query($SQL) ) {
					$Error = __('Could not update exchange difference in General Ledger');
				}

		  		$SQL = "INSERT INTO gltrans (
							type,
							typeno,
							trandate,
							periodno,
							account,
							narrative,
							amount
		  				) VALUES ('" . $_SESSION['Alloc']->TransType . "',
									'" . $_SESSION['Alloc']->TransNo . "',
									'" . $_SESSION['Alloc']->TransDate . "',
									'" . $PeriodNo . "',
									'" . $_SESSION['CompanyRecord']['debtorsact'] . "',
									'',
									'" . -$MovtInDiffOnExch . "')";

				if( !$Result = DB_query($SQL) ) {
					$Error = __('Could not update debtors control in General Ledger');
				}
			}

		}

		//
		//========[ COMMIT TRANSACTION ]===========
		//
		if (empty($Error) ) {
			DB_Txn_Commit();
		} else {
			DB_Txn_Rollback();
			prnMsg($Error,'error');
		}
		unset($_SESSION['Alloc']);
		unset($_POST['AllocTrans']);
	}
}

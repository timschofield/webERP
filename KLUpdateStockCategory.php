<?php

include('includes/session.php');
include('includes/SQL_CommonFunctions.php');
$Title = _('KL Update stock category');
include('includes/header.php');
include('includes/KLBoards.php');

//Get Out if we have no StockId or OldCat or NewCat
If (!isset($_GET['StockId']) OR $_GET['OldCat']=='' OR $_GET['NewCat']==''){
	prnMsg( _('We need an item code and Old Category and New Category codes') , 'error');
	include('includes/footer.php');
	exit();
}

$Result = DB_query("SELECT * FROM stockmaster WHERE stockid='" . $_GET['StockId'] . "'");
$MyRow = DB_fetch_row($Result);
if (DB_num_rows($Result)==0) {
	prnMsg (_('The entered item code does not exist'),'error',_('Non-existent Item'));
} elseif ($_GET['OldCat'] != $_GET['NewCat']){
	ChangeItemStockCategory( $_GET['StockId'], $_GET['OldCat'], $_GET['NewCat']);
	prnMsg ('Stock Category of ' . $_GET['StockId'] . ' changed from ' . $_GET['OldCat'] . ' to ' . $_GET['NewCat'] ,'success');
}

include('includes/footer.php');

function ChangeItemStockCategory($StockID, $OldCat, $NewCat){
	$Result = DB_Txn_Begin();
	
	$SQL = "SELECT SUM(locstock.quantity) AS qoh
			FROM locstock
			WHERE stockid='".$StockID."'";
	$QOHResult = DB_query($SQL);
	$StockQtyRow = DB_fetch_array($QOHResult);
	$QOH = $StockQtyRow['qoh'];

	$SQL = "SELECT stockcategory.stockact,
				stockcategory.wipact,
				actualcost AS itemcost,
				stockmaster.categoryid
		FROM stockmaster
		INNER JOIN stockcategory
		ON stockmaster.categoryid=stockcategory.categoryid
		WHERE stockid = '".$StockID."'";
		$OldResult = DB_query($SQL);
		$MyRow = DB_fetch_array($OldResult);
		
	$OldStockAccount = $MyRow['stockact'];
	$OldWIPAccount = $MyRow['wipact'];
	$OldCatInStockMaster = $MyRow['categoryid']; 
	$UnitCost = $MyRow['itemcost']; 
	
	$NewResult = DB_query("SELECT stockact,
								wipact
						FROM stockcategory
						WHERE categoryid='" . $NewCat . "'");
	$NewStockActRow = DB_fetch_array($NewResult);
	$NewStockAct = $NewStockActRow['stockact'];
	$NewWIPAct = $NewStockActRow['wipact'];	
	
	if ($OldCat == $OldCatInStockMaster){
		if ($OldStockAccount != $NewStockAct) {
			/*Then we need to make a journal to transfer the cost to the new stock account */
			$JournalNo = GetNextTransNo(0); //enter as a journal
			$SQL = "INSERT INTO gltrans (type,
										typeno,
										trandate,
										periodno,
										account,
										narrative,
										amount)
								VALUES ( 0,
										'" . $JournalNo . "',
										CURRENT_DATE,
										'" . GetPeriod(Date($_SESSION['DefaultDateFormat']),true) . "',
										'" . $NewStockAct . "',
										'" . mb_substr($StockID . ' ' . _('Change stock category'), 0, 200) . "',
										'" . round($UnitCost * $QOH) . "')";
			$ErrMsg =  _('The stock cost journal could not be inserted because');
			$Result = DB_query($SQL, $ErrMsg, '',true);
			$SQL = "INSERT INTO gltrans (type,
										typeno,
										trandate,
										periodno,
										account,
										narrative,
										amount)
								VALUES ( 0,
										'" . $JournalNo . "',
										CURRENT_DATE,
										'" . GetPeriod(Date($_SESSION['DefaultDateFormat']),true) . "',
										'" . $OldStockAccount . "',
										'" . mb_substr($StockID . ' ' . _('Change stock category'), 0, 200) . "',
										'" . round(-$UnitCost * $QOH) . "')";
			$Result = DB_query($SQL, $ErrMsg, '',true);

		} /* end if the stock category changed and forced a change in stock cost account */
		if ($OldWIPAccount != $NewWIPAct) {
		/*Then we need to make a journal to transfer the cost  of WIP to the new WIP account */
		/*First get the total cost of WIP for this category */

			$WOCostsResult = DB_query("SELECT workorders.costissued,
											SUM(woitems.qtyreqd * woitems.stdcost) AS costrecd
										FROM woitems INNER JOIN workorders
										ON woitems.wo = workorders.wo
										INNER JOIN stockmaster
										ON woitems.stockid=stockmaster.stockid
										WHERE stockmaster.stockid='". $StockID . "'
										AND workorders.closed=0
										GROUP BY workorders.costissued",
										_('Error retrieving value of finished goods received and cost issued against work orders for this item'));
			$WIPValue = 0;
			while ($WIPRow=DB_fetch_array($WOCostsResult)){
				$WIPValue += ($WIPRow['costissued']-$WIPRow['costrecd']);
			}
			if ($WIPValue !=0){
				$JournalNo = GetNextTransNo(0); //enter as a journal
				$SQL = "INSERT INTO gltrans (type,
											typeno,
											trandate,
											periodno,
											account,
											narrative,
											amount)
									VALUES ( 0,
											'" . $JournalNo . "',
											CURRENT_DATE,
											'" . GetPeriod(Date($_SESSION['DefaultDateFormat']),true) . "',
											'" . $NewWIPAct . "',
											'" . mb_substr($StockID . ' ' . _('Change stock category'), 0, 200) . "',
											'" . $WIPValue . "')";
				$ErrMsg =  _('The WIP cost journal could not be inserted because');
				$Result = DB_query($SQL, $ErrMsg, '',true);
				$SQL = "INSERT INTO gltrans (type,
											typeno,
											trandate,
											periodno,
											account,
											narrative,
											amount)
									VALUES ( 0,
											'" . $JournalNo . "',
											CURRENT_DATE,
											'" . GetPeriod(Date($_SESSION['DefaultDateFormat']),true) . "',
											'" . $OldWIPAccount . "',
											'" . mb_substr($StockID . ' ' . _('Change stock category'), 0, 200) . "',
											'" . (-$WIPValue) . "')";
				$Result = DB_query($SQL, $ErrMsg, '',true);
			}
		} /* end if the stock category changed and forced a change in WIP account */
		$SQL = "UPDATE stockmaster
				SET categoryid='" . $NewCat . "' 
				WHERE stockid='".$StockID."'";

		$ErrMsg = _('The stock item could not be updated because');
		$Result = DB_query($SQL,$ErrMsg,'',true);
			
		prnMsg ('CHANGE OF Stock Category of ' . $StockID . ' QOH='. $QOH . ' SC=' . $UnitCost. ' changed from ' . $OldCat . ' to ' . $NewCat ,'success');
	}else{
		prnMsg ('Item ' . $StockID . ' belongs to ' . $OldCatInStockMaster . ' not to ' . $OldCat,'error');
	}

	$Result = DB_Txn_Commit();
}

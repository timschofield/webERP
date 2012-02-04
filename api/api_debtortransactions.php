<?php
/* $Id$*/

/* Check that the transaction number is unique
 * for this type of transaction*/
	function VerifyTransNo($TransNo, $Type, $i, $Errors, $db) {
		$Searchsql = "SELECT count(transno)
				FROM debtortrans
				WHERE type='".$Type."' and transno='".$TransNo . "'";
		$SearchResult=DB_query($Searchsql, $db);
		$answer = DB_fetch_array($SearchResult);
		if ($answer[0]>0) {
			$Errors[$i] = TransactionNumberAlreadyExists;
		}
		return $Errors;
	}

function ConvertToSQLDate($DateEntry) {

//for MySQL dates are in the format YYYY-mm-dd


	if (mb_strpos($DateEntry,'/')) {
		$Date_Array = explode('/',$DateEntry);
	} elseif (mb_strpos ($DateEntry,'-')) {
		$Date_Array = explode('-',$DateEntry);
	} elseif (mb_strpos ($DateEntry,'.')) {
		$Date_Array = explode('.',$DateEntry);
	}

	if (mb_strlen($Date_Array[2])>4) {  /*chop off the time stuff */
		$Date_Array[2]= mb_substr($Date_Array[2],0,2);
	}


	if ($_SESSION['DefaultDateFormat']=='d/m/Y'){
		return $Date_Array[2].'-0'.$Date_Array[1].'-'.$Date_Array[0];
	} elseif ($_SESSION['DefaultDateFormat']=='m/d/Y'){
		return $Date_Array[1].'/'.$Date_Array[2].'/'.$Date_Array[0];
	} elseif ($_SESSION['DefaultDateFormat']=='Y/m/d'){
		return $Date_Array[0].'/'.$Date_Array[1].'/'.$Date_Array[2];
	} elseif ($_SESSION['DefaultDateFormat']=='d.m.Y'){
		return $Date_Array[2].'/'.$Date_Array[1].'/'.$Date_Array[0];
	}

} // end function ConvertSQLDate

/* Check that the transaction date is a valid date. The date
 * must be in the same format as the date format specified in the
 * target webERP company */
	function VerifyTransactionDate($TranDate, $i, $Errors, $db) {
		$sql="SELECT confvalue FROM config WHERE confname='" . DefaultDateFormat ."'";
		$result=DB_query($sql, $db);
		$myrow=DB_fetch_array($result);
		$DateFormat=$myrow[0];
		if (mb_strpos($TranDate,'/')>0) {
			$DateArray = explode('/',$TranDate);
		} elseif (mb_strpos($TranDate,'.')>0) {
			$DateArray = explode('.',$TranDate);
		}
		if ($DateFormat=='d/m/Y') {
			$Day=$DateArray[0];
			$Month=$DateArray[1];
			$Year=$DateArray[2];
		} elseif ($DateFormat=='m/d/Y') {
			$Day=$DateArray[1];
			$Month=$DateArray[0];
			$Year=$DateArray[2];
		} elseif ($DateFormat=='Y/m/d') {
			$Day=$DateArray[2];
			$Month=$DateArray[1];
			$Year=$DateArray[0];
		} elseif ($DateFormat=='d.m.Y') {
			$Day=$DateArray[0];
			$Month=$DateArray[1];
			$Year=$DateArray[2];
		}
		if (!checkdate(intval($Month), intval($Day), intval($Year))) {
			$Errors[$i] = InvalidCurCostDate;
		}
		return $Errors;
	}

/* Find the period number from the transaction date */
	function GetPeriodFromTransactionDate($TranDate, $i, $Errors, $db) {
		$sql="SELECT confvalue FROM config WHERE confname='DefaultDateFormat'";
		$result=DB_query($sql, $db);
		$myrow=DB_fetch_array($result);
		$DateFormat=$myrow[0];
		if (mb_strstr('/',$PeriodEnd)) {
			$Date_Array = explode('/',$PeriodEnd);
		} elseif (mb_strstr('.',$PeriodEnd)) {
			$Date_Array = explode('.',$PeriodEnd);
		}
		if ($DateFormat=='d/m/Y') {
			$Day=$DateArray[0];
			$Month=$DateArray[1];
			$Year=$DateArray[2];
		} elseif ($DateFormat=='m/d/Y') {
			$Day=$DateArray[1];
			$Month=$DateArray[0];
			$Year=$DateArray[2];
		} elseif ($DateFormat=='Y/m/d') {
			$Day=$DateArray[2];
			$Month=$DateArray[1];
			$Year=$DateArray[0];
		} elseif ($DateFormat=='d.m.Y') {
			$Day=$DateArray[0];
			$Month=$DateArray[1];
			$Year=$DateArray[2];
		}
		$DateArray=explode('-',$TranDate);
		$Day=$DateArray[2];
		$Month=$DateArray[1];
		$Year=$DateArray[0];
		$Date=$Year.'-'.$Month.'-'.$Day;
		$sql="SELECT MAX(periodno) FROM periods WHERE lastdate_in_period<='" . $Date . "'";
		$result=DB_query($sql, $db);
		$myrow=DB_fetch_array($result);
		return $myrow[0];
	}

/* Verify that the Settled flag is a 1 or 0 */
	function VerifySettled($Settled, $i, $Errors) {
		if ($Settled!=0 and $Settled!=1) {
			$Errors[$i] = InvalidSettled;
		}
		return $Errors;
	}

/* Check that the transaction reference is 20 characters
 *  or less long */
	function VerifyReference($reference, $i, $Errors) {
		if (mb_strlen($reference)>20) {
			$Errors[$i] = IncorrectReference;
		}
		return $Errors;
	}

/* Check that the tpe field is 2 characters or less long */
	function VerifyTpe($tpe, $i, $Errors) {
		if (mb_strlen($tpe)>2) {
			$Errors[$i] = IncorrectTpe;
		}
		return $Errors;
	}

/* Verify that the order number is numeric */
	function VerifyOrderNumber($order, $i, $Errors) {
		if (!is_numeric($order)) {
			$Errors[$i] = InvalidOrderNumbers;
		}
		return $Errors;
	}

/* Verify that the exchange rate is numeric */
	function VerifyExchangeRate($rate, $i, $Errors) {
		if (!is_numeric($rate)) {
			$Errors[$i] = InvalidExchangeRate;
		}
		return $Errors;
	}

/* Verify that the ovamount is numeric */
	function VerifyOVAmount($ovamount, $i, $Errors) {
		if (!is_numeric($ovamount)) {
			$Errors[$i] = InvalidOVAmount;
		}
		return $Errors;
	}

/* Verify that the ovgst is numeric */
	function VerifyOVGst($ovgst, $i, $Errors) {
		if (!is_numeric($ovgst)) {
			$Errors[$i] = InvalidOVGst;
		}
		return $Errors;
	}

/* Verify that the ovfreight is numeric */
	function VerifyOVFreight($ovfreight, $i, $Errors) {
		if (!is_numeric($ovfreight)) {
			$Errors[$i] = InvalidOVFreight;
		}
		return $Errors;
	}

/* Verify that the ovdiscount is numeric */
	function VerifyOVDiscount($ovdiscount, $i, $Errors) {
		if (!is_numeric($ovdiscount)) {
			$Errors[$i] = InvalidOVDiscount;
		}
		return $Errors;
	}

/* Verify that the diffonexch is numeric */
	function VerifyDiffOnExchange($diffonexch, $i, $Errors) {
		if (!is_numeric($diffonexch)) {
			$Errors[$i] = InvalidDiffOnExchange;
		}
		return $Errors;
	}

/* Verify that the allocated figure is numeric */
	function VerifyAllocated($alloc, $i, $Errors) {
		if (!is_numeric($alloc)) {
			$Errors[$i] = InvalidAllocation;
		}
		return $Errors;
	}

/* Check that the invoice text is 256 characters or less long */
	function VerifyInvoiceText($invtext, $i, $Errors) {
		if (mb_strlen($invtext)>256) {
			$Errors[$i] = IncorrectInvoiceText;
		}
		return $Errors;
	}

/* Check that the ship via field is 10 characters or less long */
	function VerifyShipVia($shipvia, $i, $Errors) {
		if (mb_strlen($shipvia)>10) {
			$Errors[$i] = InvalidShipVia;
		}
		return $Errors;
	}

/* Verify that the edisent flag is a 1 or 0 */
	function VerifyEdiSent($edisent, $i, $Errors) {
		if ($edisent!=0 and $edisent!=1) {
			$Errors[$i] = InvalidEdiSent;
		}
		return $Errors;
	}

/* Check that the consignment field is 15 characters or less long */
	function VerifyConsignment($consignment, $i, $Errors) {
		if (mb_strlen($consignment)>15) {
			$Errors[$i] = InvalidConsignment;
		}
		return $Errors;
	}

/* Retrieves the default sales GL code for a given part code and sales area 
 * 
 * This function also in SQL_CommonFunctions...better to use it from there as it covers all cases
 * and not limited to stk='any'!!
 * 
	function GetSalesGLCode($salesarea, $partnumber, $db) {
		$sql="SELECT salesglcode FROM salesglpostings
			WHERE stkcat='any'";
		$result=DB_query($sql, $db);
		$myrow=DB_fetch_array($result);
		return $myrow[0];
	}
*/

/* Retrieves the default debtors code for webERP */
	function GetDebtorsGLCode($db) {
		$sql="SELECT debtorsact FROM companies";
		$result=DB_query($sql, $db);
		$myrow=DB_fetch_array($result);
		return $myrow[0];
	}

	/* 
	This function inserts a debtors receipts into a bank account/GL Postings and does the allocation and journals for difference on exchange

	$Receipt contains an associative array in the format:
		 * $Receipt['debtorno'] - the customer code
		 * $Receipt['branchcode']  - the branch code
		 * $Receipt['trandate'] - the date of the receipt
		 * $Receipt['amountfx'] - the amount in FX
		 * $Receipt['paymentmethod'] - the payment method of the receipt e.g. cash/EFTPOS/credit card
		 * $Receipt['bankaccount'] - the webERP bank account
		 * $Receipt['allocto_transid'] - the invoice to allocate against

	*/

	function InsertDebtorReceipt($Receipt, $User, $Password) {

		$Errors = array();
		$db = db($User, $Password);
		if (gettype($db)=='integer') {
			$Errors[0]=NoAuthorisation;
			return $Errors;
		}
		$fp = fopen( "/root/Web-Server/apidebug/DebugInfo.txt", "w");

		$Errors=VerifyDebtorExists($Header['debtorno'], sizeof($Errors), $Errors, $db);
		$Errors=VerifyBranchNoExists($Header['debtorno'],$Header['branchcode'], sizeof($Errors), $Errors, $db);





	}
/* Retrieves the next transaction number for the given type
 * This function is already included from SQL_CommonFunctions.php????
	Isn't it??

	function GetNextTransactionNo($type, $db) {
		$sql="SELECT typeno FROM systypes WHERE typeid='" . $type . "'";
		$result=DB_query($sql, $db);
		$myrow=DB_fetch_array($result);
		$NextTransaction=$myrow[0]+1;
		return $NextTransaction;
	}

*/
/* Create a customer credit note in webERP.
 * Needs an associative array for the Header
 * and an array of assocative arrays for the $LineDetails
 */
	function CreateCreditNote($Header,$LineDetails, $User, $Password) {

		/* $Header contains an associative array in the format:
		 * Header['debtorno'] - the customer code
		 * Header['branchcode']  - the branch code
		 * Header['trandate'] - the date of the credit note
		 * Header['tpe'] - the sales type
		 * Header['fromstkloc'] - the inventory location where the stock is put back into
		 * Header['customerref'] - the customer's reference
		 * Header['shipvia'] - the shipper required by webERP
		 *
		 * and $LineDetails contains an array of associative arrays of the format:
		 *
		 * $LineDetails[0]['stockid']
		 * $LineDetails[0]['price']
		 * $LineDetails[0]['qty'] - expected to be a negative quantity (a negative sale)
		 * $LineDetails[0]['discountpercent']
		 */
		$Errors = array();
		$db = db($User, $Password);
		if (gettype($db)=='integer') {
			$Errors[0]=NoAuthorisation;
			return $Errors;
		}
		
		
		$Errors=VerifyDebtorExists($Header['debtorno'], sizeof($Errors), $Errors, $db);
		$Errors=VerifyBranchNoExists($Header['debtorno'],$Header['branchcode'], sizeof($Errors), $Errors, $db);
		/*Does not deal with serialised/lot track items - for use by POS */
		/*Get Company Defaults */
		$ReadCoyResult = api_DB_query("SELECT debtorsact,
												freightact,
												gllink_debtors,
												gllink_stock
										FROM companies
										WHERE coycode=1",$db);

		$CompanyRecord = DB_fetch_array($ReadCoyResult);
		if (DB_error_no($db) != 0) {
			$Errors[] = NoCompanyRecord;
		}
		
		$HeaderSQL = "SELECT custbranch.area,
							 custbranch.taxgroupid,
							 debtorsmaster.currcode,
							 rate,
							 salesman
							FROM debtorsmaster
							INNER JOIN custbranch
							ON debtorsmaster.debtorno = custbranch.debtorno
							INNER JOIN currencies
							ON debtorsmaster.currcode=currencies.currabrev
							WHERE custbranch.debtorno = '" . $Header['debtorno'] . "'
							AND custbranch.branchcode='" . $Header['branchcode'] . "'";

		$HeaderResult = api_DB_query($HeaderSQL,$db);
		if (DB_error_no($db) != 0) {
			$Errors[] = NoReadCustomerBranch;
		}

		$CN_Header = DB_fetch_array($HeaderResult);

		$TaxProvResult = api_DB_query("SELECT taxprovinceid FROM locations WHERE loccode='" . $Header['fromstkloc'] ."'",$db);
		if (DB_error_no($db) != 0) {
			$Errors[] = NoTaxProvince;
		}
		$myrow = DB_fetch_row($TaxProvResult);
		$DispTaxProvinceID = $myrow[0];

	/*Start an SQL transaction */
		$result = DB_Txn_Begin($db);
	/*Now Get the next credit note number - function in SQL_CommonFunctions*/
		$CreditNoteNo = GetNextTransNo(11, $db);
		$PeriodNo = GetCurrentPeriod($db);

		$TotalFXNetCredit = 0;
		$TotalFXTax = 0;

		$TaxTotals =array();
		$LineCounter =0;

		foreach ($LineDetails as $CN_Line) {

			$LineSQL = "SELECT taxcatid,
								mbflag,
								materialcost+labourcost+overheadcost AS standardcost
						FROM stockmaster
						WHERE stockid ='" . $CN_Line['stockid'] . "'";

			$LineResult = api_DB_query($LineSQL,$db);
			if (DB_error_no($db) != 0 OR DB_num_rows($LineResult)==0) {
				$Errors[] = NoReadItem;
				return $Errors;
			}
			$LineRow = DB_fetch_array($LineResult);

			$StandardCost = $LineRow['standardcost'];
			$LocalCurrencyPrice= ($CN_Line['price'] *(1- floatval($CN_Line['discountpercent'])))/ $CN_Header['rate'];
			$LineNetAmount = $CN_Line['price'] * $CN_Line['qty'] *(1- floatval($CN_Line['discountpercent']));

			/*Gets the Taxes and rates applicable to this line from the TaxGroup of the branch and TaxCategory of the item
			and the taxprovince of the dispatch location */

			$SQL = "SELECT taxgrouptaxes.calculationorder,
							taxauthorities.description,
							taxgrouptaxes.taxauthid,
							taxauthorities.taxglcode,
							taxgrouptaxes.taxontax,
							taxauthrates.taxrate
					FROM taxauthrates INNER JOIN taxgrouptaxes ON
						taxauthrates.taxauthority=taxgrouptaxes.taxauthid
						INNER JOIN taxauthorities ON
						taxauthrates.taxauthority=taxauthorities.taxid
					WHERE taxgrouptaxes.taxgroupid='" . $CN_Header['taxgroupid'] . "'
					AND taxauthrates.dispatchtaxprovince='" . $DispTaxProvinceID . "'
					AND taxauthrates.taxcatid = '" . $LineRow['taxcatid'] . "'
					ORDER BY taxgrouptaxes.calculationorder";

			$GetTaxRatesResult = api_DB_query($SQL,$db);

			if (DB_error_no($db) != 0) {
				$Errors[] = TaxRatesFailed;
			}

			$LineTaxAmount = 0;
			
			while ($myrow = DB_fetch_array($GetTaxRatesResult)){
				if (!isset($TaxTotals[$myrow['taxauthid']]['FXAmount'])) {
					$TaxTotals[$myrow['taxauthid']]['FXAmount']=0;
				}
				$TaxAuthID=$myrow['taxauthid'];
				$TaxTotals[$myrow['taxauthid']]['GLCode'] = $myrow['taxglcode'];
				$TaxTotals[$myrow['taxauthid']]['TaxRate'] = $myrow['taxrate'];
				$TaxTotals[$myrow['taxauthid']]['TaxAuthDescription'] = $myrow['description'];

				if ($myrow['taxontax'] ==1){
					  $TaxAuthAmount = ($LineNetAmount+$LineTaxAmount) * $myrow['taxrate'];
				} else {
					$TaxAuthAmount =  $LineNetAmount * $myrow['taxrate'];
				}
				$TaxTotals[$myrow['taxauthid']]['FXAmount'] += $TaxAuthAmount;
				
				/*Make an array of the taxes and amounts including GLcodes for later posting - need debtortransid
				so can only post once the debtor trans is posted - can only post debtor trans when all tax is calculated */
				$LineTaxes[$LineCounter][$myrow['calculationorder']] = array('TaxCalculationOrder' =>$myrow['calculationorder'],
												'TaxAuthID' =>$myrow['taxauthid'],
												'TaxAuthDescription'=>$myrow['description'],
												'TaxRate'=>$myrow['taxrate'],
												'TaxOnTax'=>$myrow['taxontax'],
												'TaxAuthAmount'=>$TaxAuthAmount);
				$LineTaxAmount += $TaxAuthAmount;

			}//end loop around Taxes

			$TotalFXNetCredit += $LineNetAmount;
			$TotalFXTax += $LineTaxAmount;

						
			if ($LineRow['mbflag']=='B' OR $LineRow['mbflag']=='M') {
				$Assembly = False;

				/* Need to get the current location quantity
				will need it later for the stock movement */
               	$SQL="SELECT locstock.quantity
						FROM locstock
						WHERE locstock.stockid='" . $CN_Line['stockid'] . "'
						AND loccode= '" . $Header['fromstkloc'] . "'";
				$Result = api_DB_query($SQL, $db);
				
				if (DB_num_rows($Result)==1){
					$LocQtyRow = DB_fetch_row($Result);
					$QtyOnHandPrior = $LocQtyRow[0];
				} else {
					/* There must be some error this should never happen */
					$QtyOnHandPrior = 0;
				}

				$SQL = "UPDATE locstock
						SET quantity = locstock.quantity - " . $CN_Line['qty'] . "
						WHERE locstock.stockid = '" . $CN_Line['stockid'] . "'
						AND loccode = '" . $Header['fromstkloc'] . "'";

				$Result = api_DB_query($SQL,$db,'','',true);

				$SQL = "INSERT INTO stockmoves (stockid,
												type,
												transno,
												loccode,
												trandate,
												debtorno,
												branchcode,
												price,
												prd,
												reference,
												qty,
												discountpercent,
												standardcost,
												newqoh)
						VALUES ('" . $CN_Line['stockid'] . "',
								'11',
								'" . $CreditNoteNo . "',
								'" . $Header['fromstkloc'] . "',
								'" . $Header['trandate'] . "',
								'" . $Header['debtorno'] . "',
								'" . $Header['branchcode'] . "',
								'" . $LocalCurrencyPrice . "',
								'" . $PeriodNo . "',
								'" . $Header['customerref'] . "',
								'" . -$CN_Line['qty'] . "',
								'" . $CN_Line['discountpercent'] . "',
								'" . $StandardCost . "',
								'" . ($QtyOnHandPrior - $CN_Line['qty']) . "' )";

				$Result = api_DB_query($SQL,$db,'','',true);

			} else if ($LineRow['mbflag']=='A'){ /* its an assembly */
				/*Need to get the BOM for this part and make
				stock moves for the components then update the Location stock balances */
				$Assembly=True;
				$StandardCost =0; /*To start with - accumulate the cost of the comoponents for use in journals later on */
				$SQL = "SELECT bom.component,
								bom.quantity,
								stockmaster.materialcost+stockmaster.labourcost+stockmaster.overheadcost AS standard
							FROM bom INNER JOIN stockmaster
							ON bom.component=stockmaster.stockid
							WHERE bom.parent='" . $CN_Line['stockid'] . "'
							AND bom.effectiveto >= '" . Date('Y-m-d') . "'
							AND bom.effectiveafter < '" . Date('Y-m-d') . "'";

				$AssResult = api_DB_query($SQL,$db);

				while ($AssParts = DB_fetch_array($AssResult,$db)){

					$StandardCost += ($AssParts['standard'] * $AssParts['quantity']) ;
					/* Need to get the current location quantity
					will need it later for the stock movement */
					$SQL="SELECT locstock.quantity
							FROM locstock
							WHERE locstock.stockid='" . $AssParts['component'] . "'
							AND loccode= '" . $Header['fromstkloc'] . "'";

					$Result = api_DB_query($SQL,$db);
					if (DB_num_rows($Result)==1){
						$LocQtyRow = DB_fetch_row($Result);
	                  	$QtyOnHandPrior = $LocQtyRow[0];
					} else {
						/*There must be some error this should never happen */
						$QtyOnHandPrior = 0;
					}
					if (empty($AssParts['standard'])) {
						$AssParts['standard']=0;
					}
					$SQL = "INSERT INTO stockmoves (stockid,
													type,
													transno,
													loccode,
													trandate,
													debtorno,
													branchcode,
													prd,
													reference,
													qty,
													standardcost,
													show_on_inv_crds,
													newqoh)
										VALUES ('" . $AssParts['component'] . "',
												 11,
												 '" . $CreditNoteNo . "',
												 '" . $Header['fromstkloc'] . "',
												 '" . $Header['trandate'] . "',
												 '" . $Header['debtorno'] . "',
												 '" . $Header['branchcode'] . "',
												 '" . $PeriodNo . "',
												 '" . _('Assembly') . ': ' . $CN_Line['stockid'] . ' ' . $Header['customerref'] . "',
												 '" . (-$AssParts['quantity'] * $CN_Line['qty']) . "',
												 '" . $AssParts['standard'] . "',
												 0,
												 '" . ($QtyOnHandPrior - ($AssParts['quantity'] * $CN_Line['qty'])) . "'	)";

					$Result = DB_query($SQL,$db,'','',true);

					$SQL = "UPDATE locstock
							SET quantity = locstock.quantity - " . ($AssParts['quantity'] * $CN_Line['qty']) . "
							WHERE locstock.stockid = '" . $AssParts['component'] . "'
							AND loccode = '" . $Header['fromlocstk'] . "'";

					$Result = DB_query($SQL,$db,'','',true);
				} /* end of assembly explosion and updates */
			} /* end of its an assembly */


			if ($LineRow['mbflag']=='A' OR $LineRow['mbflag']=='D'){
				/*it's a Dummy/Service item or an Assembly item - still need stock movement record
				 * but quantites on hand are always nil */
				$SQL = "INSERT INTO stockmoves (stockid,
												type,
												transno,
												loccode,
												trandate,
												debtorno,
												branchcode,
												price,
												prd,
												reference,
												qty,
												discountpercent,
												standardcost,
												newqoh)
						VALUES ('" . $CN_Line['stockid'] . "',
								'11',
								'" . $CreditNoteNo . "',
								'" . $Header['fromstkloc'] . "',
								'" . $Header['trandate'] . "',
								'" . $Header['debtorno'] . "',
								'" . $Header['branchcode'] . "',
								'" . $LocalCurrencyPrice . "',
								'" . $PeriodNo . "',
								'" . $Header['customerref'] . "',
								'" . -$CN_Line['qty'] . "',
								'" . $CN_Line['discountpercent'] . "',
								'" . $StandardCost . "',
								'0' )";

				$Result = api_DB_query($SQL,$db,'','',true);
			}
			/*Get the ID of the StockMove... */
			$StkMoveNo = DB_Last_Insert_ID($db,'stockmoves','stkmoveno');
			/*Insert the taxes that applied to this line */
			foreach ($LineTaxes[$LineCounter] as $Tax) {

				$SQL = "INSERT INTO stockmovestaxes (stkmoveno,
									taxauthid,
									taxrate,
									taxcalculationorder,
									taxontax)
						VALUES ('" . $StkMoveNo . "',
							'" . $Tax['TaxAuthID'] . "',
							'" . $Tax['TaxRate'] . "',
							'" . $Tax['TaxCalculationOrder'] . "',
							'" . $Tax['TaxOnTax'] . "')";

				$Result = DB_query($SQL,$db,'','',true);
			}

			/*Insert Sales Analysis records */

			$SQL="SELECT COUNT(*),
						salesanalysis.stkcategory,
						salesanalysis.area,
						salesanalysis.salesperson,
						salesanalysis.periodno,
						salesanalysis.typeabbrev,
						salesanalysis.cust,
						salesanalysis.custbranch,
						salesanalysis.stockid
					FROM salesanalysis,
						custbranch,
						stockmaster
					WHERE salesanalysis.stkcategory=stockmaster.categoryid
					AND salesanalysis.stockid=stockmaster.stockid
					AND salesanalysis.cust=custbranch.debtorno
					AND salesanalysis.custbranch=custbranch.branchcode
					AND salesanalysis.area=custbranch.area
					AND salesanalysis.salesperson=custbranch.salesman
					AND salesanalysis.typeabbrev ='" . $Header['tpe'] . "'
					AND salesanalysis.periodno='" . $PeriodNo . "'
					AND salesanalysis.cust " . LIKE . "  '" . $Header['debtorno'] . "'
					AND salesanalysis.custbranch  " . LIKE . " '" . $Header['branchcode'] . "'
					AND salesanalysis.stockid  " . LIKE . " '" . $CN_Line['stockid'] . "'
					AND salesanalysis.budgetoractual='1'
					GROUP BY salesanalysis.stockid,
						salesanalysis.stkcategory,
						salesanalysis.cust,
						salesanalysis.custbranch,
						salesanalysis.area,
						salesanalysis.periodno,
						salesanalysis.typeabbrev,
						salesanalysis.salesperson";

			$Result = api_DB_query($SQL,$db,'','',true);

			$myrow = DB_fetch_row($Result);

			if ($myrow[0]>0){  /*Update the existing record that already exists */

				$SQL = "UPDATE salesanalysis
						SET amt=amt+" . ($CN_Line['price'] * $CN_Line['qty'] / $CN_Header['rate']) . ",
						qty=qty +" . $CN_Line['qty'] . ",
						disc=disc+" . ($CN_Line['discountpercent'] * $CN_Line['price'] * $CN_Line['qty'] / $CN_Header['rate']) . "
						WHERE salesanalysis.area='" . $myrow[2] . "'
						AND salesanalysis.salesperson='" . $myrow[3] . "'
						AND typeabbrev ='" . $Header['tpe'] . "'
						AND periodno = '" . $PeriodNo . "'
						AND cust  " . LIKE . " '" . $Header['debtorno'] . "'
						AND custbranch  " . LIKE . "  '" . $Header['branchcode'] . "'
						AND stockid  " . LIKE . " '" . $CN_Line['stockid'] . "'
						AND salesanalysis.stkcategory ='" . $myrow[1] . "'
						AND budgetoractual='1'";

			} else { /* insert a new sales analysis record */

				$SQL = "INSERT INTO salesanalysis (	typeabbrev,
													periodno,
													amt,
													cost,
													cust,
													custbranch,
													qty,
													disc,
													stockid,
													area,
													budgetoractual,
													salesperson,
													stkcategory )
								SELECT '" . $Header['tpe']. "',
									'" . $PeriodNo . "',
									'" . ($CN_Line['price'] * $CN_Line['qty'] / $CN_Header['rate']) . "',
									0,
									'" . $Header['debtorno'] . "',
									'" . $Header['branchcode'] . "',
									'" . $CN_Line['qty'] . "',
									'" . ($CN_Line['discountpercent'] * $CN_Line['price'] * $CN_Line['qty'] / $CN_Header['rate']) . "',
									'" . $CN_Line['stockid'] . "',
									custbranch.area,
									1,
									custbranch.salesman,
									stockmaster.categoryid
								FROM stockmaster, custbranch
								WHERE stockmaster.stockid = '" . $CN_Line['stockid'] . "'
								AND custbranch.debtorno = '" . $Header['debtorno'] . "'
								AND custbranch.branchcode='" . $Header['branchcode'] . "'";

			}

			$Result = api_DB_query($SQL,$db,'','',true);

			if ($CompanyRecord['gllink_stock']==1 AND $StandardCost !=0){

/*first the cost of sales entry - GL accounts are retrieved using the function GetCOGSGLAccount from includes/GetSalesTransGLCodes.inc  */

				$SQL = "INSERT INTO gltrans (type,
											typeno,
											trandate,
											periodno,
											account,
											narrative,
											amount)
									VALUES (11,
										'" . $CreditNoteNo . "',
										'" . $Header['trandate'] . "',
										'" . $PeriodNo . "',
										'" . GetCOGSGLAccount($CN_Header['area'], $CN_Line['stockid'], $Header['tpe'], $db) . "',
										'" . $Header['debtorno'] . " - " . $CN_Line['stockid'] . " x " . $CN_Line['qty'] . " @ " . $StandardCost . "',
										'" . ($StandardCost * $CN_Line['qty']) . "')";

				$Result = api_DB_query($SQL,$db,'','',true);

/*now the stock entry - this is set to the cost act in the case of a fixed asset disposal */
				$StockGLCode = GetStockGLCode($CN_Line['stockid'],$db);

				$SQL = "INSERT INTO gltrans (type,
											typeno,
											trandate,
											periodno,
											account,
											narrative,
											amount)
									VALUES (11,
										'" . $CreditNoteNo . "',
										'" . $Header['trandate'] . "',
										'" . $PeriodNo . "',
										'" . $StockGLCode['stockact'] . "',
										'" . $Header['debtorno'] . " - " . $CN_Line['stockid'] . " x " . $CN_Line['qty'] . " @ " . $StandardCost . "',
										'" . (-$StandardCost * $CN_Line['qty']) . "')";

				$Result = api_DB_query($SQL,$db,'','',true);

			} /* end of if GL and stock integrated and standard cost !=0  and not an asset */

			if ($CompanyRecord['gllink_debtors']==1 AND $CN_Line['price'] !=0){

				//Post sales transaction to GL credit sales
				$SalesGLAccounts = GetSalesGLAccount($CN_Header['area'], $CN_Line['stockid'], $Header['tpe'], $db);

				$SQL = "INSERT INTO gltrans (type,
											typeno,
											trandate,
											periodno,
											account,
											narrative,
											amount )
					VALUES ('11',
						'" . $CreditNoteNo . "',
						'" . $Header['trandate'] . "',
						'" . $PeriodNo . "',
						'" . $SalesGLAccounts['salesglcode'] . "',
						'" . $Header['debtorno'] . " - " . $CN_Line['stockid'] . " x " . $CN_Line['qty'] . " @ " . $CN_Line['price'] . "',
						'" . (-$CN_Line['price'] * $CN_Line['qty']/$CN_Header['rate']) . "'
					)";
				$Result = api_DB_query($SQL,$db,'','',true);

				if ($CN_Line['discountpercent'] !=0){

					$SQL = "INSERT INTO gltrans (type,
												typeno,
												trandate,
												periodno,
												account,
												narrative,
												amount)
							VALUES (11,
								'" . $CreditNoteNo . "',
								'" . $Header['trandate'] . "',
								'" . $PeriodNo . "',
								'" . $SalesGLAccounts['discountglcode'] . "',
								'" . $Header['debtorno'] . " - " . $CN_Line['stockid'] . " @ " . ($CN_Line['discountpercent'] * 100) . "%',
								'" . ($CN_Line['price'] * $CN_Line['qty'] * $CN_Line['discountpercent']/$CN_Header['rate']) . "')";

					$Result = DB_query($SQL,$db,'','',true);
				} /*end of if discount !=0 */

			} /*end of if sales integrated with gl */

			$LineCounter++; //needed for the array of taxes by line
		} /*end of OrderLine loop */

		$TotalCreditLocalCurr = ($TotalFXNetCredit + $TotalFXTax)/$CN_Header['rate'];

		if ($CompanyRecord['gllink_debtors']==1){

			/*Now post the tax to the GL at local currency equivalent */
			if ($CompanyRecord['gllink_debtors']==1 AND $TaxAuthAmount !=0) {

				/*Loop through the tax authorities array to post each total to the taxauth glcode */
				foreach ($TaxTotals as $Tax){
					$SQL = "INSERT INTO gltrans (type,
												typeno,
												trandate,
												periodno,
												account,
												narrative,
												amount )
											VALUES (11,
											'" . $CreditNoteNo . "',
											'" . $Header['trandate']. "',
											'" . $PeriodNo . "',
											'" . $Tax['GLCode'] . "',
											'" . $Header['debtorno'] . "-" . $Tax['TaxAuthDescription'] . "',
											'" . -$Tax['FXAmount']/$CN_Header['rate'] . "' )";

					$Result = api_DB_query($SQL,$db,'','',true);
				}
			}

			/*Post debtors transaction to GL credit debtors, and debit sales */
			if (($TotalCreditLocalCurr) !=0) {
				$SQL = "INSERT INTO gltrans (type,
											typeno,
											trandate,
											periodno,
											account,
											narrative,
											amount)
									VALUES ('11',
										'" . $CreditNoteNo . "',
										'" . $Header['trandate'] . "',
										'" . $PeriodNo . "',
										'" . $CompanyRecord['debtorsact'] . "',
										'" . $Header['debtorno'] . "',
										'" . $TotalCreditLocalCurr . "')";

				$Result = api_DB_query($SQL,$db,'','',true);
			}
			EnsureGLEntriesBalance(11,$CreditNoteNo,$db);

		} /*end of if Sales and GL integrated */

	/*Now insert the DebtorTrans */

		$SQL = "INSERT INTO debtortrans (transno,
										type,
										debtorno,
										branchcode,
										trandate,
										inputdate,
										prd,
										reference,
										tpe,
										ovamount,
										ovgst,
										rate,
										shipvia)
									VALUES (
										'". $CreditNoteNo . "',
										11,
										'" . $Header['debtorno'] . "',
										'" . $Header['branchcode'] . "',
										'" . $Header['trandate'] . "',
										'" . date('Y-m-d H-i-s') . "',
										'" . $PeriodNo . "',
										'" . $Header['customerref'] . "',
										'" . $Header['tpe'] . "',
										'" . $TotalFXNetCredit . "',
										'" . $TotalFXTax . "',
										'" . $CN_Header['rate'] . "',
										'" . $Header['shipvia'] . "')";

		$Result = api_DB_query($SQL,$db,'','',true);

		$DebtorTransID = DB_Last_Insert_ID($db,'debtortrans','id');

		/*for each Tax - need to insert into debtortranstaxes */
		foreach ($TaxTotals AS $TaxAuthID => $Tax) {

			$SQL = "INSERT INTO debtortranstaxes (debtortransid,
												taxauthid,
												taxamount)
								VALUES ('" . $DebtorTransID . "',
										'" . $TaxAuthID . "',
										'" . $Tax['FXAmount']/$CN_Header['rate'] . "')";
			$Result = api_DB_query($SQL,$db,'','',true);
		}

		if (sizeof($Errors)==0) {
			$Result = DB_Txn_Commit($db);
			$Errors[0]=0;
			$Errors[1]=$CreditNoteNo;
		} else {
			$Result = DB_Txn_Rollback($db);
		}
		return $Errors;

	} /*End of CreateCreditNote method */

/* Create a customer invoice in webERP. This function will bypass the
 * normal procedure in webERP for creating a sales order first, and then
 * delivering it.

 * NB: There are no stock updates no accounting for assemblies no updates
 * to sales analysis records - no cost of sales entries in GL

 ************ USE ONLY WITH CAUTION********************
 */
	function InsertSalesInvoice($InvoiceDetails, $user, $password) {
		$Errors = array();
		$db = db($user, $password);
		if (gettype($db)=='integer') {
			$Errors[0]=NoAuthorisation;
			return $Errors;
		}
		foreach ($InvoiceDetails as $key => $value) {
			$InvoiceDetails[$key] = DB_escape_string($value);
		}
		$PartCode=$InvoiceDetails['partcode'];
		$Errors=VerifyStockCodeExists($PartCode, sizeof($Errors), $Errors, $db );
		unset($InvoiceDetails['partcode']);
		$SalesArea=$InvoiceDetails['salesarea'];
		unset($InvoiceDetails['salesarea']);
		$InvoiceDetails['transno']=GetNextTransactionNo(10, $db);
		$InvoiceDetails['type'] = 10;
		$Errors=VerifyDebtorExists($InvoiceDetails['debtorno'], sizeof($Errors), $Errors, $db);
		$Errors=VerifyBranchNoExists($InvoiceDetails['debtorno'],$InvoiceDetails['branchcode'], sizeof($Errors), $Errors, $db);
		$Errors=VerifyTransNO($InvoiceDetails['transno'], 10, sizeof($Errors), $Errors, $db);
		$Errors=VerifyTransactionDate($InvoiceDetails['trandate'], sizeof($Errors), $Errors, $db);
		if (isset($InvoiceDetails['settled'])){
			$Errors=VerifySettled($InvoiceDetails['settled'], sizeof($Errors), $Errors);
		}
		if (isset($InvoiceDetails['reference'])){
			$Errors=VerifyReference($InvoiceDetails['reference'], sizeof($Errors), $Errors);
		}
		if (isset($InvoiceDetails['tpe'])){
			$Errors=VerifyTpe($InvoiceDetails['tpe'], sizeof($Errors), $Errors);
		}
		if (isset($InvoiceDetails['order_'])){
			$Errors=VerifyOrderNumber($InvoiceDetails['order_'], sizeof($Errors), $Errors);
		}
		if (isset($InvoiceDetails['rate'])){
			$Errors=VerifyExchangeRate($InvoiceDetails['rate'], sizeof($Errors), $Errors);
		}
		if (isset($InvoiceDetails['ovamount'])){
			$Errors=VerifyOVAmount($InvoiceDetails['ovamount'], sizeof($Errors), $Errors);
		}
		if (isset($InvoiceDetails['ovgst'])){
			$Errors=VerifyOVGst($InvoiceDetails['ovgst'], sizeof($Errors), $Errors);
		}
		if (isset($InvoiceDetails['ovfreight'])){
			$Errors=VerifyOVFreight($InvoiceDetails['ovfreight'], sizeof($Errors), $Errors);
		}
		if (isset($InvoiceDetails['ovdiscount'])){
			$Errors=VerifyOVDiscount($InvoiceDetails['ovdiscount'], sizeof($Errors), $Errors);
		}
		if (isset($InvoiceDetails['diffonexch'])){
			$Errors=VerifyDiffOnExchange($InvoiceDetails['diffonexch'], sizeof($Errors), $Errors);
		}
		if (isset($InvoiceDetails['alloc'])){
			$Errors=VerifyAllocated($InvoiceDetails['alloc'], sizeof($Errors), $Errors);
		}
		if (isset($InvoiceDetails['invtext'])){
			$Errors=VerifyInvoiceText($InvoiceDetails['invtext'], sizeof($Errors), $Errors);
		}
		if (isset($InvoiceDetails['shipvia'])){
			$Errors=VerifyShipVia($InvoiceDetails['shipvia'], sizeof($Errors), $Errors);
		}
		if (isset($InvoiceDetails['edisent'])){
			$Errors=VerifyEdiSent($InvoiceDetails['edisent'], sizeof($Errors), $Errors);
		}
		if (isset($InvoiceDetails['consignment'])){
			$Errors=VerifyConsignment($InvoiceDetails['consignment'], sizeof($Errors), $Errors);
		}
		$FieldNames='';
		$FieldValues='';
		$InvoiceDetails['trandate']=ConvertToSQLDate($InvoiceDetails['trandate']);
		$InvoiceDetails['prd']=GetPeriodFromTransactionDate($InvoiceDetails['trandate'], sizeof($Errors), $Errors, $db);
		foreach ($InvoiceDetails as $key => $value) {
			$FieldNames.=$key.', ';
			$FieldValues.='"'.$value.'", ';
		}
		if (sizeof($Errors)==0) {
			$result = DB_Txn_Begin($db);
			$sql = "INSERT INTO debtortrans (" . mb_substr($FieldNames,0,-2) .") 
									VALUES ('" . mb_substr($FieldValues,0,-2) ."') ";
			$result = DB_Query($sql, $db);
			$sql = "UPDATE systypes SET typeno='" . GetNextTransactionNo(10, $db) . "' WHERE typeid=10";
			$result = DB_Query($sql, $db);
			$SalesGLCode=GetSalesGLCode($SalesArea, $PartCode, $db);
			$DebtorsGLCode=GetDebtorsGLCode($db);
			$sql="INSERT INTO gltrans VALUES(null, 
											10,
											'" . GetNextTransactionNo(10, $db) . "',
											0,
											'" . $InvoiceDetails['trandate'] ."',
											'" . $InvoiceDetails['prd'] . "', 
											'" . $DebtorsGLCode. "',
											'". _('Invoice for') .' -' . $InvoiceDetails['debtorno'] .' ' . _('Total') . ' - '. $InvoiceDetails['ovamount'] . "', 
											'" . $InvoiceDetails['ovamount'] . "', 
											0,
											'" . $InvoiceDetails['jobref'] . "',
											1)";
			$result = DB_Query($sql, $db);
			$sql="INSERT INTO gltrans VALUES(null, 
											10,
											'" . GetNextTransactionNo(10, $db) . "',
											0,
											'" . $InvoiceDetails['trandate'] ."',
											'" . $InvoiceDetails['prd'] . "', 
											'" . $SalesGLCode . "',
											'" . _('Invoice for') . ' -' . $InvoiceDetails['debtorno'] . ' ' . _('Total') .' - '. $InvoiceDetails['ovamount'] ."', 
											'" . (-intval($InvoiceDetails['ovamount'])) ."', 
											0,
											'" . $InvoiceDetails['jobref'] . "',
											1)";
			$result = DB_Query($sql, $db);
			$result= DB_Txn_Commit($db);
			if (DB_error_no($db) != 0) {
				$Errors[0] = DatabaseUpdateFailed;
			} else {
				$Errors[0]=0;
				//  Return invoice number too
				$Errors[] = $InvoiceDetails['transno'];
			}
			return  $Errors;
		} else {
			return $Errors;
		}
	}

/* Create a customer credit note in webERP. This function will bypass the
 * normal procedure in webERP for creating a sales order first, and then
 * delivering it. All values should be sent as negatives.


 * NB: Stock is not updated and the method cannot deal with assembly items
 * the sales analysis is not updated either

 ****************** USE WITH CAUTION!! **********************
 */
	function InsertSalesCredit($CreditDetails, $user, $password) {
		$Errors = array();
		$db = db($user, $password);
		if (gettype($db)=='integer') {
			$Errors[0]=NoAuthorisation;
			return $Errors;
		}
		foreach ($CreditDetails as $key => $value) {
			$CreditDetails[$key] = DB_escape_string($value);
		}
		$PartCode=$CreditDetails['partcode'];
		$Errors=VerifyStockCodeExists($PartCode, sizeof($Errors), $Errors, $db );
		unset($CreditDetails['partcode']);
		$SalesArea=$CreditDetails['salesarea'];
		unset($CreditDetails['salesarea']);
		$CreditDetails['transno']=GetNextTransactionNo(11, $db);
		$CreditDetails['type'] = 10;
		$Errors=VerifyDebtorExists($CreditDetails['debtorno'], sizeof($Errors), $Errors, $db);
		$Errors=VerifyBranchNoExists($CreditDetails['debtorno'],$CreditDetails['branchcode'], sizeof($Errors), $Errors, $db);
		$Errors=VerifyTransNO($CreditDetails['transno'], 10, sizeof($Errors), $Errors, $db);
		$Errors=VerifyTransactionDate($CreditDetails['trandate'], sizeof($Errors), $Errors, $db);
		if (isset($CreditDetails['settled'])){
			$Errors=VerifySettled($CreditDetails['settled'], sizeof($Errors), $Errors);
		}
		if (isset($CreditDetails['reference'])){
			$Errors=VerifyReference($CreditDetails['reference'], sizeof($Errors), $Errors);
		}
		if (isset($CreditDetails['tpe'])){
			$Errors=VerifyTpe($CreditDetails['tpe'], sizeof($Errors), $Errors);
		}
		if (isset($CreditDetails['order_'])){
			$Errors=VerifyOrderNumber($CreditDetails['order_'], sizeof($Errors), $Errors);
		}
		if (isset($CreditDetails['rate'])){
			$Errors=VerifyExchangeRate($CreditDetails['rate'], sizeof($Errors), $Errors);
		}
		if (isset($CreditDetails['ovamount'])){
			$Errors=VerifyOVAmount($CreditDetails['ovamount'], sizeof($Errors), $Errors);
		}
		if (isset($CreditDetails['ovgst'])){
			$Errors=VerifyOVGst($CreditDetails['ovgst'], sizeof($Errors), $Errors);
		}
		if (isset($CreditDetails['ovfreight'])){
			$Errors=VerifyOVFreight($CreditDetails['ovfreight'], sizeof($Errors), $Errors);
		}
		if (isset($CreditDetails['ovdiscount'])){
			$Errors=VerifyOVDiscount($CreditDetails['ovdiscount'], sizeof($Errors), $Errors);
		}
		if (isset($CreditDetails['diffonexch'])){
			$Errors=VerifyDiffOnExchange($CreditDetails['diffonexch'], sizeof($Errors), $Errors);
		}
		if (isset($CreditDetails['alloc'])){
			$Errors=VerifyAllocated($CreditDetails['alloc'], sizeof($Errors), $Errors);
		}
		if (isset($CreditDetails['invtext'])){
			$Errors=VerifyInvoiceText($CreditDetails['invtext'], sizeof($Errors), $Errors);
		}
		if (isset($CreditDetails['shipvia'])){
			$Errors=VerifyShipVia($CreditDetails['shipvia'], sizeof($Errors), $Errors);
		}
		if (isset($CreditDetails['edisent'])){
			$Errors=VerifyEdiSent($CreditDetails['edisent'], sizeof($Errors), $Errors);
		}
		if (isset($CreditDetails['consignment'])){
			$Errors=VerifyConsignment($CreditDetails['consignment'], sizeof($Errors), $Errors);
		}
		$FieldNames='';
		$FieldValues='';
		$CreditDetails['trandate']=ConvertToSQLDate($CreditDetails['trandate']);
		$CreditDetails['prd']=GetPeriodFromTransactionDate($CreditDetails['trandate'], sizeof($Errors), $Errors, $db);
		foreach ($CreditDetails as $key => $value) {
			$FieldNames.=$key.', ';
			$FieldValues.='"'.$value.'", ';
		}
		if (sizeof($Errors)==0) {
			$result = DB_Txn_Begin($db);
			$sql = "INSERT INTO debtortrans (" . mb_substr($FieldNames,0,-2) . ") 
						VALUES ('".mb_substr($FieldValues,0,-2) ."') ";
			$result = DB_Query($sql, $db);
			$sql = "UPDATE systypes SET typeno='" . GetNextTransactionNo(11, $db) ."' WHERE typeid=10";
			$result = DB_Query($sql, $db);
			$SalesGLCode=GetSalesGLCode($SalesArea, $PartCode, $db);
			$DebtorsGLCode=GetDebtorsGLCode($db);
			$sql="INSERT INTO gltrans VALUES(null, 
											10,
											'" . GetNextTransactionNo(11, $db). "',
											0,
											'" . $CreditDetails['trandate'] . "',
											'" . $CreditDetails['prd'] . "', 
											'" .$DebtorsGLCode . "',
											'". _('Invoice for') .  ' - '.$CreditDetails['debtorno'].' ' . -('Total') .' - '.$CreditDetails['ovamount']. "', 
											'" . $CreditDetails['ovamount'] . "', 
											0,
											'" . $CreditDetails['jobref'] ."')";
			$result = DB_Query($sql, $db);
			$sql="INSERT INTO gltrans VALUES(null,
											10,
											'" . GetNextTransactionNo(11, $db) . "',
											0,
											'" . $CreditDetails['trandate'] ."',
											'" . $CreditDetails['prd'] . "', 
											'" . $SalesGLCode ."',
											'". _('Invoice for') . ' - ' . $CreditDetails['debtorno'] .' ' . _('Total') . ' - '. $CreditDetails['ovamount'] ."', 
											'" .(-intval($CreditDetails['ovamount'])) . "', 
											0,
											'" . $CreditDetails['jobref'] ."')";
			$result = DB_Query($sql, $db);
			$result= DB_Txn_Commit($db);
			if (DB_error_no($db) != 0) {
				$Errors[0] = DatabaseUpdateFailed;
			} else {
				$Errors[0]=0;
			}
			return  $Errors;
		} else {
			return $Errors;
		}
	}

?>
<?php
// ########################################
// Code by Waleed A. Meligy - ITWorx 2005
// ########################################
include_once("includes/CSVclasses.php");
include_once("includes/csv.inc.php");
require_once("includes/COAMap.inc.php");
$COA= new COAMap();

// ^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^
/*
function DB_Query($x,&$y){
	return mysql_query($x,$y);
}
function DB_fetch_assoc(&$x){

	return mysql_fetch_assoc($x);
}
function DB_error_msg(){
	return mysql_error();
}
*/
// ^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^

Class ERPExport extends CSV_Titles{
	var $TotalEntries;		// how many total entries
	var $ExportsDir;		// set the dir of exports
	var $Debug;				// want to view debging messages
	var $db; 				// db resource
	var $LastExport;
	var $EntriesArray;                      // to store the data to be saved
	
	// ##############################
	// Constructor
	// ##############################
	
	function ERPExport($dbResource){
		$this->TotalEntries = 0;
		$this->Debug=false;
		$this->db = $dbResource;
	}

	// ##############################
	// Export Customers from weberp
	// ##############################	
	function ExportCustomers($y){
		$this->LastExport = 'Customers';
		$CustQuery = "SELECT DebtorsMaster.DebtorNo AS Debtor,`Name`,`CustomerEmail`,`ClientSince`,`CreditLimit`,`ContactName`,`PhoneNo`,`FaxNo`,`Email` from `DebtorsMaster`,`CustBranch` where `ClientSince` >= '".$y." 00:00:00' AND DebtorsMaster.DebtorNo = CustBranch.DebtorNo";
		$CustResult = DB_Query($CustQuery,$this->db) or die(DB_error_msg());
		
		// first clear the array
		$this->EntriesArray = "";
		
		while($row = DB_fetch_assoc($CustResult)){
				
			$this->EntriesArray['Name'][] = $row['Debtor'];
			$this->EntriesArray['CompanyName'][]=$row['Name'];
			$this->EntriesArray['Email'][]=$row['Email'];
/*
			$this->EntriesArray['ShipAddr1'][]= $row['Address1'];
			$this->EntriesArray['ShipAddr2'][]= $row['Address2'];
			$this->EntriesArray['ShipAddr3'][]= $row['Address3'];
			$this->EntriesArray['ShipAddr4'][]= $row['Address4'];
*/
			$temp = $row['ClientSince'];
			//print $temp;
			$tempa = explode(' ',$temp);
			$modifieddate = str_replace("/","-",$tempa[0]);
			$this->EntriesArray['OpenBalanceDate'][] = $modifieddate;

			$temp = sprintf("%8.2f",trim($row['CreditLimit']));

			$this->EntriesArray['CreditLimit'][] = $temp;
			$this->EntriesArray['Contact'][] = $row['ContactName'];
			$this->EntriesArray['Phone'][] = $row['PhoneNo'];
			$this->EntriesArray['Fax'][] = $row['FaxNo'];
			// Note currency is always USD
			
/*			// now to get the invoicing address, check if HO
			if($row['InvAddrBranch']==0){
				$this->EntriesArray['BillAddr1'][]= $row['Address1']
				$this->EntriesArray['BillAddr2'][]= $row['Address2'];
				$this->EntriesArray['BillAddr3'][]= $row['Address3'];
				$this->EntriesArray['BillAddr4'][]= $row['Address4'];
			}else{
			        // get the branch address
			}
*/
	
		}
				
		if($this->Debug){
		        print_r($this->EntriesArray);
		        print("<br>");
		        print("file name: ".$this->ExportsDir."/Customers".$y.".csv <br>");
		}
		if(!is_array($this->EntriesArray))$this->EntriesArray = array(''=>array(''));
		if($this->Write($this->ExportsDir."/Customers".$y.".csv",$this->EntriesArray)){
			
			
			DB_Query("UPDATE `ExportTransTrack` set `Date`=NOW() , `IP`='".$_SERVER['REMOTE_ADDR']."' WHERE `Type`='Customers'",$this->db);
			
			return true;
		}
		else return false;
	}
	
	// ##############################
	// Export Suppliers from weberp
	// ##############################
	
	function ExportSuppliers($y){
		$this->LastExport = 'Suppliers';
		$VenQuery = "SELECT * from `Suppliers` where `SupplierSince` >= '".$y." 00:00:00'";
		$VenResult = DB_Query($VenQuery,$this->db) or die(DB_error_msg());

		// first clear the array
		$this->EntriesArray = '';

		while($row = DB_fetch_assoc($VenResult)){
			//if($this->Debug)print_r($row);
			$this->EntriesArray['Name'][] = $row['SupplierID'];
			$this->EntriesArray['CompanyName'][]=$row['SuppName'];

			$this->EntriesArray['Addr1'][]= $row['Address1'];
			$this->EntriesArray['Addr2'][]= $row['Address2'];
			$this->EntriesArray['Addr3'][]= $row['Address3'];
			$this->EntriesArray['Addr4'][]= $row['Address4'];

			// now get the other contact details
			$tempquery = "Select * from `SupplierContacts` WHERE `SupplierID`='".$row['SupplierID']."' ORDER BY OrderContact ASC";
			$tempResult = DB_Query($tempquery,$this->db) or die(mysql_error());

			$contactrow=array();
			$contactrow = DB_fetch_assoc($tempResult);

			$this->EntriesArray['Tel'][] = $contactrow['Tel'];
			$this->EntriesArray['Fax'][] = $contactrow['Fax'];
			$this->EntriesArray['Email'][] = $row['SupplierEmail'];
			$this->EntriesArray['Contact'][] = $contactrow['Contact'];
			// Carry on
			$temp = $row['SupplierSince'];
			//print $temp;
			$tempa = explode(' ',$temp);
			//$wdate = explode('/',$tempa[0]);
//			$modifieddate = $wdate[2]."-".$wdate[1]."-".$wdate[0];
			$modifieddate = str_replace("/","-",$tempa[0]);
			
			$this->EntriesArray['OpenBalanceDate'][] = $modifieddate;


			// Note currency is always USD
		}
		if($this->Debug){
		        print_r($this->EntriesArray);
		        print("<br>");
		        print("file name: ".$this->ExportsDir."/Vendors".$y.".csv <br>");
		}
		if(!is_array($this->EntriesArray))$this->EntriesArray = array(''=>array(''));
		if($this->Write($this->ExportsDir."/Vendors".$y.".csv",$this->EntriesArray)){
			DB_Query("UPDATE `ExportTransTrack` set `Date`=NOW() ,`IP`='".$_SERVER['REMOTE_ADDR']."' WHERE `Type`='Vendors'",$this->db);
			return true;
		}
		else return false;
//		}
	}


	// ##############################
	// Export COA from weberp
	// ##############################
	function ExportAccounts($COA,$y){

		$this->LastExport = 'Chart Of Accounts';
		// first clear the array
		$this->EntriesArray = '';

		// To get the current Actual balance of the current GL account we need
		// the period number fro mthe periods tables
		
		$periodquery="SELECT * FROM `Periods` WHERE `LastDate_in_Period` > '".date("Y-m-d")."' ORDER BY `PeriodNo` DESC";
		$periodresult = DB_Query($periodquery,$this->db) or die(DB_error_msg());
		$dummy = DB_fetch_assoc($periodresult);
		$PeriodNo = $dummy['PeriodNo'];

		// now we need to check to make sure that the COA is set
		if($COA->NumAccounts() == 0 || !$COA->IsMapped()) exit(0);
		
		// ok carry on, now we will loop through the Accounts array
		
		foreach($COA->Accounts as $Account){
			// and find the actual balace of each one from the db
			if($Account['IsMapped'] != 0){
//				$Account['Number'];
				// get the balance
				$balanceQuery = "SELECT `Actual` FROM `ChartDetails` WHERE `AccountCode`='".$Account['Number']."' AND `Period`='".$PeriodNo."'";
				$dummyresult = DB_Query($balanceQuery,$this->db);
				$dummy= DB_fetch_assoc($dummyresult);
				$AccountBalance = $dummy['Actual'];
				
			// then finally add it to the entries array
			
				if(strlen($Account['Name']) >29) $Name = substr($Account['Name'],0,29)."_";
				else $Name = $Account['Name'];
				$this->EntriesArray['Name'][] =$Name;
				$this->EntriesArray['AccountType'][] = $Account['Map'];
				$this->EntriesArray['AccountNumber'][] = $Account['Number'];
				$this->EntriesArray['OpenBalance'][] = sprintf("%8.2f",trim($AccountBalance));
				$this->EntriesArray['OpenBalanceDate'][]=date("Y-m-d");

			 }

		
		}


		if($this->Debug){
		        print_r($this->EntriesArray);
		        print("<br>");
		        print("file name: ".$this->ExportsDir."/Accounts".$y.".csv <br>");
		}
		if(!is_array($this->EntriesArray))$this->EntriesArray = array(''=>array(''));
		if($this->Write($this->ExportsDir."/Accounts".$y.".csv",$this->EntriesArray)){
			DB_Query("UPDATE `ExportTransTrack` set `Date`=NOW() ,`IP`='".$_SERVER['REMOTE_ADDR']."' WHERE `Type`='Accounts'",$this->db);
			return true;
		}
		else return false;
	//	}
	}

	// ##############################
	// Export Invoices from weberp
	// ##############################

	function ExportInvoices($y){
		$this->LastExport = 'Invoices';
		$Query = "SELECT * from `DebtorTrans` WHERE `Type`='10' and `TranDate` >= '".$y."'";
		$Result = DB_Query($Query,$this->db) or die(DB_error_msg());

		// first clear the array
		$this->EntriesArray = "";
		

		while($row = DB_fetch_assoc($Result)){

// now since each order can contain several items, we will check for all of them and loop through them
			$OrderNo = $row['Order_'];
			$dummyquery = "SELECT * FROM `SalesOrderDetails` WHERE `OrderNo`='".$OrderNo."' ";
			$dummyres = DB_Query($dummyquery,$this->db) or die(DB_error_msg());
			$i=0;
			

			
			while($itemspecific = DB_fetch_assoc($dummyres)){

				$QtyInvoiced = DB_Query("SELECT `Qty` FROM `StockMoves` WHERE `TransNo`='".$row['TransNo']."' AND `Type`='10' AND `StockID`='".$itemspecific['StkCode']."'",$this->db);
				$Qty = DB_fetch_assoc($QtyInvoiced);

				//CustomerRef>FullName
				// use DebtorNo to get name from Debtor's master
				$this->EntriesArray['FullName'][]=$row['DebtorNo'];
				//TxnDate	[ the date of invoice ]
				$dummy = explode(" ",$row['TranDate']);
				$this->EntriesArray['TxnDate'][]=$dummy[0];
				//BillAddress

				//ShipAddress

				//IsPending [ completed or not ]
				if($row['Completed']==0)$dummy=1;
				else $dummy=0;

				$this->EntriesArray['IsPending'][]=$dummy;

				//DueDate

				$dummyquery = "SELECT `DeliveryDate` FROM `SalesOrders` WHERE `OrderNo`='".$OrderNo."'";
				$dummyres1 = DB_Query($dummyquery,$this->db);
				$dummy = DB_fetch_assoc($dummyres1);
				$this->EntriesArray['DueDate'][]=$dummy['DeliveryDate'];

				//Memo
				$this->EntriesArray['Memo'][]=$row['InvText'];
				//IsToBePrinted*
				$this->EntriesArray['IsToBePrinted'][]=1;


				//InvoiceLineAdd>Quantity




				$this->EntriesArray['Quantity'][] = sprintf("%8.2f",-1*$Qty['Qty']);
				//InvoiceLineAdd>Rate	[ unit price ]
				$this->EntriesArray['Rate'][] = sprintf("%8.2f",$itemspecific['UnitPrice']*(1-$itemspecific['DiscountPercent']));

				//InvoiceLineAdd>ItemRef>FullName*
				//InvoiceLineAdd>Desc*
				$this->EntriesArray['Item'][] = $itemspecific['StkCode'];
				$this->EntriesArray['Desc'][] = ($itemspecific['Narrative']);
				$this->EntriesArray['TransNo'][] = $row['TransNo'];
				if($row['OvAmount']==0)	$row['OvAmount'] = 1;
				$this->EntriesArray['SalesTax'][] = sprintf("%7.5f",($row['OvGST']/$row['OvAmount'])*100);
				

	                }
			// Note currency is always USD
			$i++;
			//if($this->Debug)print_r($row);
			
			
		}
		if($i ==0){
//				print("<center><font color=red >No data was found.</font></center><br>");
//				return false;
		}
		if($this->Debug){
		        print_r($this->EntriesArray);
		        print("<br>");
		        print("file name: ".$this->ExportsDir."/Invoices".$y.".csv <br>");
		}
		if(!is_array($this->EntriesArray))$this->EntriesArray = array(''=>array(''));
		if($this->Write($this->ExportsDir."/Invoices".$y.".csv",$this->EntriesArray)){
                        DB_Query("UPDATE `ExportTransTrack` set `Date`=NOW() , `IP`='".$_SERVER['REMOTE_ADDR']."' WHERE `Type`='Invoices'",$this->db);
			return true;
		}
		else return false;
//		}
	}


	// ##############################
	// Export Journal entries from weberp
	// ##############################

	function ExportJournalEntries($y){
		$this->LastExport = 'Journal Entries';
		$this->EntriesArray="";
		// Start extracting all transactions from the GL which are nor PO,SO,CN, or Invoice
		$TypesRes=DB_Query ("SELECT `TypeNo`,`TypeID` FROM `SysTypes` WHERE `TypeID` <> 10 AND `TypeID`<> 11",$this->db);
		while($TypesRes1 = DB_fetch_assoc($TypesRes)){
			$Types[] = $TypesRes1['TypeID'];
			$NumTypes[$TypesRes1['TypeID']] = $TypesRes1['TypeNo'];
		}

		foreach($Types as $Typ){
		        // each type here
			$TypNo = $NumTypes[$Typ];
			//print("Type no: ".$TypNo."<br>");
			// now the query for all
			for($i=1;$i<=$TypNo;$i++){
			        // now go through each collection together
			        $Transaction = DB_query("SELECT * FROM `GLTrans` WHERE (`TypeNo`='".$i."' AND `Type`='".$Typ."' AND `Posted`=1 AND `TranDate` >= '".$y."')",$this->db);
			        
			        while($TransDetails = DB_fetch_assoc($Transaction)){
					//print("Transaction Details:<br>");
//					if($this->Debug){
						//print_r($TransDetails);
						//print("<br>");
//					}
			        	// JournalEntryAdd>TxnDate
				        $this->EntriesArray['TxnDate'][] = $TransDetails['TranDate'];
					// JournalDebitLine>AccountRef>FullName
					$tempres = DB_Query("SELECT `AccountName` FROM `ChartMaster` WHERE `AccountCode`='".$TransDetails['Account']."'",$this->db);
					$tempdata = DB_fetch_assoc($tempres);
					if(strlen($tempdata['AccountName']) >29) $Name = substr($tempdata['AccountName'],0,29)."_";
					else $Name = $tempdata['AccountName'];
                                	$this->EntriesArray['FullName'][] =$Name ;
					//JournalDebitLine>Amount
//echo $TransDetails['amount']."<br>";
					$this->EntriesArray['Amount'][] = sprintf("%8.2f",$TransDetails['Amount']);
					// JournalDebitLine>Memo
					$this->EntriesArray['Memo'][] = $TransDetails['Narrative'];
					// this is needed by the client side app to track the
					// transactions as a group
					
					$this->EntriesArray['TypeNo'][]= $Typ."-".$i;


			        }

			}
		
		}
		
		
		if($this->Debug){
		        print_r($this->EntriesArray);
		        print("<br>");
		        print("file name: ".$this->ExportsDir."/JournalEntries".$y.".csv <br>");
		}
		if(!is_array($this->EntriesArray))$this->EntriesArray = array(''=>array(''));
		if($this->Write($this->ExportsDir."/JournalEntries".$y.".csv",$this->EntriesArray)){
                        DB_Query("UPDATE `ExportTransTrack` set `Date`=NOW() ,`IP`='".$_SERVER['REMOTE_ADDR']."' WHERE `Type`='JournalEntries'",$this->db);
			return true;
		}
		else return false;
//		}
	}

	// ##############################
	// Export Credit Notes from weberp
	// ##############################

	function ExportCreditNotes($y){
		$this->LastExport = 'Credit Memo';
		$Query = "SELECT * from `DebtorTrans` WHERE `Type`='11' AND `TranDate` >= '".$y."'";
		$Result = DB_Query($Query,$this->db) or die(DB_error_msg());

		// first clear the array
		$this->EntriesArray = "";

		while($row = DB_fetch_assoc($Result)){

			$details = DB_Query("SELECT * from `StockMoves` WHERE `Type`='11' and `TransNo`='".$row['TransNo']."'",$this->db);
			
			while( $item = DB_fetch_assoc($details)){
				//CustomerRef>FullName
				// use DebtorNo to get name from Debtor's master
				$this->EntriesArray['FullName'][]=$row['DebtorNo'];
			
				//ARAccountRef>FullName ---
			
				//TxnDate	[ the date of Note ]
				$dummy = explode(" ",$row['TranDate']);
				$this->EntriesArray['TxnDate'][]=$dummy[0];
				//BillAddress

				//ShipAddress
				//RefNumber [ bla bla for this ote ]
			
				//IsPending [ completed or not ]
				if($row['Settled']==0)$dummy=1;
				else $dummy=0;
			
				$this->EntriesArray['IsPending'][]=$dummy;

				//DueDate [ Not Applicable ]
				//FOB
				//ShipMethodRef>FullName

				//Memo
				$this->EntriesArray['Memo'][]=$row['InvText'];
				//IsToBePrinted*
				$this->EntriesArray['IsToBePrinted'][]=1;


			
				//InvoiceLineAdd>Amount

//				$temp = $row['OvAmount'] +$row['OvGST'] +$row['OvFreight'] +$row['OvDiscount'] ;
//				$temp = $row['OvAmount'] +$row['OvFreight'] +$row['OvDiscount'] ;
//				$temp = str_replace("-","",$temp);
			
				$this->EntriesArray['Quantity'][] = sprintf("%7.5f",($item['Qty']));
				$this->EntriesArray['Rate'][] = sprintf("%8.5f",$item['Price']);
				//InvoiceLineAdd>Rate	[ unit price ]
				//InvoiceLineAdd>ItemRef>FullName*
				$this->EntriesArray['Item'][]=$item['StockID'];
				//InvoiceLineAdd>Desc*
				$this->EntriesArray['Desc'][]=($item['Narrative']);
				$this->EntriesArray['TransNo'][] = $row['TransNo'];
				 if($row['OvAmount']==0) $row['OvAmount'] = 1;
				$this->EntriesArray['SalesTax'][] = sprintf("%7.5f",($row['OvGST']/$row['OvAmount'])*100);
			}

			//if($this->Debug)print_r($row);

			// Note currency is always USD

		}
		if($this->Debug){
		        print_r($this->EntriesArray);
		        print("<br>");
		        print("file name: ".$this->ExportsDir."/CreditMemos".$y.".csv <br>");
		}
		if(!is_array($this->EntriesArray))$this->EntriesArray = array(''=>array(''));
		if($this->Write($this->ExportsDir."/CreditMemos".$y.".csv",$this->EntriesArray)){
                        DB_Query("UPDATE `ExportTransTrack` set `Date`=NOW() , `IP`='".$_SERVER['REMOTE_ADDR']."' WHERE `Type`='CreditMemos'",$this->db);
			return true;
		}
		else return false;
//		}
	}


	// ##############################
	// Export Sales Orders from weberp
	// ##############################

	function ExportSalesOrders($y){
		$this->LastExport = 'Sales Orders';
		$Query = "SELECT * from `SalesOrders` WHERE `OrdDate` >= '".$y."'";
		$Result = DB_Query($Query,$this->db) or die(DB_error_msg());

		// first clear the array
		$this->EntriesArray = '';

		while($row = DB_fetch_assoc($Result)){
			$dummyquery = "SELECT `StkCode`,`UnitPrice`,`Quantity` FROM `SalesOrderDetails` WHERE `OrderNo`='".$row['OrderNo']."' ";
			$dummyres = DB_Query($dummyquery,$this->db) or die(DB_error_msg());
			
			while($dummy = DB_fetch_assoc($dummyres)){
			//CustomerRef>FullName
			// use Dbtone no to get name from Debtor's master
			$this->EntriesArray['FullName'][]=$row['DebtorNo'];
                        //TxnDate
                        $this->EntriesArray['TxnDate'][]=$row['OrdDate'];
			//BillAddress>Addr1,2,3,4
			//ShipAddress>Addr1,2,3,4
			//DueDate
			$this->EntriesArray['DueDate'][]=$row['DeliveryDate'];
			//FOB [ where from , just for info ]
			
			//ShipDate
			
			//ShipMethodRef
			//Memo
			$this->EntriesArray['Memo'][]=$row['Comments'];
			//IsToBePrinted
			$this->EntriesArray['IsToBePrinted'][]=1;
			///SalesOrderLineAdd>ItemRef>FullName
			$this->EntriesArray['ItemFullName'][]=$dummy['StkCode'];
			//Quantity





			$this->EntriesArray['Quantity'][] = sprintf("%8.2f",$dummy['Quantity']);
			//Rate [ unit price ]
			$this->EntriesArray['Rate'][] = sprintf("%8.2f",$dummy['UnitPrice']);
			$this->EntriesArray['OrderNo'][] = $row['OrderNo'];
		//	$this->EntriesArray['SalesTax'][] = sprintf("%7.5f",($row['OvGST']/$row['OvAmount'])*100);

			}		
			
			



		}if($this->Debug){
		        print_r($this->EntriesArray);
		        print("<br>");
		        print("file name: ".$this->ExportsDir."/SalesOrders".$y.".csv <br>");
		}
		if(!is_array($this->EntriesArray))$this->EntriesArray = array(''=>array(''));
		if($this->Write($this->ExportsDir."/SalesOrders".$y.".csv",$this->EntriesArray)){
                        DB_Query("UPDATE `ExportTransTrack` set `Date`=NOW() , `IP`='".$_SERVER['REMOTE_ADDR']."' WHERE `Type`='SalesOrders'",$this->db);
			return true;
		}
		else return false;
	//	}
	}


	// ##############################
	// Export Purchase Orders from weberp
	// ##############################

	function ExportPurchaseOrders($y){
		$this->LastExport = 'Purchase Orders';
		$Query = "SELECT * from `PurchOrders`  WHERE `OrdDate` >= '".$y."'";
		$Result = DB_Query($Query,$this->db) or die(DB_error_msg());

		// first clear the array
		$this->EntriesArray = '';

		while($row = DB_fetch_assoc($Result)){

//ShipMethodRef>FullName
//FOB
//Memo
//IsToBePrinted
			$dummyquery = "SELECT `QuantityRecd`,`ItemCode`,`ItemDescription`,`DeliveryDate`,`UnitPrice`,`QuantityOrd` FROM `PurchOrderDetails` WHERE `OrderNo`='".$row['OrderNo']."' ";
			$dummyres = DB_Query($dummyquery,$this->db) or die(DB_error_msg());
			while($orderdata = DB_fetch_assoc($dummyres)){
			
			//VendorRef>FullName
			// use Dbtone no to get name from Debtor's master
				$this->EntriesArray['FullName'][]=$row['SupplierNo'];
                        //TxnDate
				$temp = explode(' ',$row['OrdDate']);
				$this->EntriesArray['TxnDate'][]=$temp[0];
			//BillAddress>Addr1,2,3,4
			//ShipAddress>Addr1,2,3,4
			//DueDate [ Not applicable ]

			//ExpectedDate
			
				$this->EntriesArray['ExpectedDate'][]=$orderdata['DeliveryDate'];
                        
			//ShipMethodRef
			//FOB [ where from , just for info ]
			//Memo
				$this->EntriesArray['Memo'][]=$row['Comments'];
				//IsToBePrinted
				$this->EntriesArray['IsToBePrinted'][]=1;
				///PurchseOrderLineAdd>ItemRef>FullName
				$this->EntriesArray['ItemFullName'][]=$orderdata['ItemCode'];
				
				//PurchaseOrderLineAdd>Desc
				$this->EntriesArray['Desc'][]=($orderdata['ItemDescription']);
				//PurchaseOrderLineAdd>Quantity
				//PurchaseOrderLineAdd>Rate
				
				//Quantity
			
				$this->EntriesArray['Quantity'][] = sprintf("%8.2f",$orderdata['QuantityOrd']);
				$this->EntriesArray['ReceivedQuantity'][] = sprintf("%8.2f",$orderdata['QuantityRecd']);

			//Rate [ unit price ]
				$this->EntriesArray['Rate'][] = sprintf("%8.2f",$orderdata['UnitPrice']);
				$this->EntriesArray['OrderNo'][] = $row['OrderNo'];
			}




		}if($this->Debug){
		        print_r($this->EntriesArray);
		        print("<br>");
		        print("file name: ".$this->ExportsDir."/PurchaseOrders".$y.".csv <br>");
		}
		if(!is_array($this->EntriesArray))$this->EntriesArray = array(''=>array(''));
		if($this->Write($this->ExportsDir."/PurchaseOrders".$y.".csv",$this->EntriesArray)){
                        DB_Query("UPDATE `ExportTransTrack` set `Date`=NOW() , `IP`='".$_SERVER['REMOTE_ADDR']."' WHERE `Type`='PurchaseOrders'",$this->db);
			return true;
		}
		else return false;
	//	}
	}

	// ##############################
	// Export Inventory from weberp
	// ##############################

	function ExportInventory($y){
	       // global $_SESSION;
		$this->LastExport = 'Inventory Items';
		$Query = "SELECT * from `StockMaster`  WHERE `MBflag` = 'B'";
		$Result = DB_Query($Query,$this->db) or die(DB_error_msg());

		// first clear the array
		$this->EntriesArray = '';

		while($row = DB_fetch_assoc($Result)){

			//Name
			$this->EntriesArray['Name'][]=$row['StockID'];
			//SalesDesc
			$this->EntriesArray['SalesDesc'][]=($row['LongDescription']);

			//IncomeAccountRef>FullName

			$Dummyres =DB_Query("SELECT `AccountName` FROM `ChartMaster` WHERE `AccountCode`='1'",$this->db);
			$Dummy = DB_fetch_assoc($Dummyres);
			if(strlen($Dummy['AccountName']) >29) $Name = substr($Dummy['AccountName'],0,29)."_";
			else $Name = $Dummy['AccountName'];	
			$this->EntriesArray['IncomeFullName'][]=$Name;

			//COGSAccountRef>FullName
			if(strlen($_SESSION['COGSGLAccount']) >29) $Name = substr($_SESSION['COGSGLAccount'],0,29)."_";
			else $Name = $_SESSION['COGSGLAccount'];
                        $this->EntriesArray['COGSFullName'][]=$Name;
                        
			//AssetAccountRef>FullName
			$StockAcc_result = DB_Query("SELECT `AccountName` from `ChartMaster` where `AccountCode` IN (SELECT `StockAct` FROM `StockCategory` WHERE `CategoryID` = '".$row['CategoryID']."')",$this->db);
			$StockAcc_res = DB_fetch_Assoc($StockAcc_result);
			if(strlen($StockAcc_res['AccountName']) >29) $Name = substr($StockAcc_res['AccountName'],0,29)."_";
			else $Name = $StockAcc_res['AccountName'];			
                        $this->EntriesArray['AssetFullName'][]=$Name;

			//ReorderPoint
			$Dummyres = DB_Query("SELECT * FROM `LocStock` WHERE `StockID`='".$row['StockID']."'",$this->db);
			$Dummy = DB_fetch_assoc($Dummyres);
			if(DB_num_rows($Dummyres)==1) $reorderlevel = $Dummy['ReorderLevel'];
			else $reorderlevel =0;
			
			$this->EntriesArray['ReorderPoint'][] = $reorderlevel;
			
			//QuantityOnHand
			$Dummyres = DB_Query("SELECT SUM(`Quantity`) as Total FROM `LocStock` WHERE `StockID`='".$row['StockID']."'",$this->db);
			$Dummy = DB_fetch_assoc($Dummyres);
			$this->EntriesArray['QuantityOnHand'][] = $Dummy['Total'];
			
		}if($this->Debug){
		        print_r($this->EntriesArray);
		        print("<br>");
		        print("file name: ".$this->ExportsDir."/InventoryItems_Buy_".$y.".csv <br>");
		}
		if(!is_array($this->EntriesArray))$this->EntriesArray = array(''=>array(''));
		if($this->Write($this->ExportsDir."/InventoryItems_Buy_".$y.".csv",$this->EntriesArray)){
                        DB_Query("UPDATE `ExportTransTrack` set `Date`=NOW() , `IP`='".$_SERVER['REMOTE_ADDR']."' WHERE `Type`='InventoryItems'",$this->db);
			return true;
		}
		else return false;
	//	}
	}
	
	// ##############################
	// Export Inventory from weberp
	// ##############################

	function ExportAssemblyInventory($y){
//	        global $_SESSION;
		$this->LastExport = 'Inventory Items [Make,Assembly ]';
		$Query = "SELECT * from `StockMaster`  WHERE `MBflag` = 'M' OR `MBflag` = 'A'";
		$Result = DB_Query($Query,$this->db) or die(DB_error_msg());

		// first clear the array
		$this->EntriesArray = '';
		$tobeadded = array();
		while($row = DB_fetch_assoc($Result)){
			// now extract all results from the BOM table
                        $Dummyresult = DB_Query("SELECT  `Component`,`Quantity` FROM `BOM` WHERE `Parent`='".$row['StockID']."'",$this->db);

			$DokoKoko = DB_Query("SELECT `MBflag` FROM `StockMaster`,`BOM` WHERE BOM.Parent='".$row['StockID']."' AND StockMaster.MBflag ='M' AND StockMaster.StockID=BOM.Component",$this->db);
			if(DB_num_rows($DokoKoko)==0) $Flag ='';
			else $Flag = 'M';
                        
                        while($DummyTop = DB_fetch_assoc($Dummyresult)){

				//Name
				if($Flag != 'M')$this->EntriesArray['Name'][]=$row['StockID'];
				else $tobeadded['Name'][] =$row['StockID'];
				//SalesDesc
				if($Flag != 'M')$this->EntriesArray['SalesDesc'][]=($row['LongDescription']);
				else $tobeadded['SalesDesc'][]=($row['LongDescription']);

				//IncomeAccountRef>FullName

				$Dummyres =DB_Query("SELECT `AccountName` FROM `ChartMaster` WHERE `AccountCode`='1'",$this->db);
				$Dummy = DB_fetch_assoc($Dummyres);
				if(strlen($Dummy['AccountName']) >29) $Name = substr($Dummy['AccountName'],0,29)."_";
				else $Name = $Dummy['AccountName'];	
				if($Flag != 'M')$this->EntriesArray['IncomeFullName'][]=$Name;
				else $tobeadded['IncomeFullName'][] =$Name;
				
				//PurchaseCost

				//COGSAccountRef>FullName
				if(strlen($_SESSION['COGSGLAccount']) >29) $Name = substr($_SESSION['COGSGLAccount'],0,29)."_";
				else $Name = $_SESSION['COGSGLAccount'];	
	                        if($Flag != 'M')$this->EntriesArray['COGSFullName'][]=$Name;
	                        else $tobeadded['COGSFullName'][]=$Name;

				//AssetAccountRef>FullName
				$StockAcc_result = DB_Query("SELECT `AccountName` from `ChartMaster` where `AccountCode` IN (SELECT `StockAct` FROM `StockCategory` WHERE `CategoryID` = '".$row['CategoryID']."')",$this->db);
				$StockAcc_res = DB_fetch_Assoc($StockAcc_result);
				if(strlen($StockAcc_res['AccountName']) >29) $Name = substr($StockAcc_res['AccountName'],0,29)."_";
				else $Name = $StockAcc_res['AccountName'];	
	                        if($Flag != 'M')$this->EntriesArray['AssetFullName'][]=$Name;
	                        else $tobeadded['AssetFullName'][]=$Name;

				//QuantityOnHand
				$Dummyres = DB_Query("SELECT SUM(`Quantity`) as Total FROM `LocStock` WHERE `StockID`='".$row['StockID']."'",$this->db);
				$Dummy = DB_fetch_assoc($Dummyres);
				if($Flag != 'M')$this->EntriesArray['QuantityOnHand'][] = $Dummy['Total'];
				else $tobeadded['QuantityOnHand'][] = $Dummy['Total'];
				
	                        //ItemInventoryAssemblyLine>ItemInventoryRef>FullName
                                if($Flag != 'M')$this->EntriesArray['ComponentFullName'][] = $DummyTop['Component'];
                                else $tobeadded['ComponentFullName'][] = $DummyTop['Component'];
                                if($Flag != 'M')$this->EntriesArray['ComponentQuantity'][] = $DummyTop['Quantity'];
                                else $tobeadded['ComponentQuantity'][] = $DummyTop['Quantity'];
                        
                	}
                        
		}
		if($this->Debug){
		        print_r($this->EntriesArray);
		        print("<br>");
		        print("file name: ".$this->ExportsDir."/InventoryItems_Make-Assembly_".$y.".csv <br>");
		}
		// add the $tobeadded to original
//		print_r($tobeadded);
		$oldindex = @count($this->EntriesArray['Name']);
		$total = @count($tobeadded['Name']);
		$keys = @array_keys($this->EntriesArray);

		for($i=0;$i<($total);$i++){
			foreach($keys as $k){
			        $this->EntriesArray[$k][$i+$oldindex] = $tobeadded[$k][$i];
			}
		}
		unset($tobeadded);
		
		if(!is_array($this->EntriesArray))$this->EntriesArray = array(''=>array(''));
		if($this->Write($this->ExportsDir."/InventoryItems_Make-Assembly_".$y.".csv",$this->EntriesArray)){
                        DB_Query("UPDATE `ExportTransTrack` set `Date`=NOW() , `IP`='".$_SERVER['REMOTE_ADDR']."' WHERE `Type`='AssemblyInv'",$this->db);
			return true;
		}
		else return false;
	//	}
	}

	// ##############################
	// Export KIT Inventory from weberp
	// ##############################

	function ExportGroupInventory($y){
//	        global $_SESSION;
		$this->LastExport = 'Inventory Items [Kit]';
		$Query = "SELECT * from `StockMaster`  WHERE `MBflag` = 'K'";
		$Result = DB_Query($Query,$this->db) or die(DB_error_msg());

		// first clear the array
		$this->EntriesArray = '';

		while($row = DB_fetch_assoc($Result)){
                                                // now extract all results from the BOM table
                        $Dummyresult = DB_Query("SELECT  `Component`,`Quantity` FROM `BOM` WHERE `Parent`='".$row['StockID']."'",$this->db);
                        while($DummyTop = DB_fetch_assoc($Dummyresult)){

//Name
//ItemDesc
//IsPrintItemsInGroup " must be always true " for consistancy with weberp
//ItemGroupLine>FullName
//ItemGroupLine>Quantity
				//Name
				$this->EntriesArray['Name'][]=$row['StockID'];
				//SalesDesc
				$this->EntriesArray['ItemDesc'][]=($row['LongDescription']);
                                $this->EntriesArray['IsPrintItemsInGroup'][]="true";
	                        //ItemInventoryAssemblyLine>ItemInventoryRef>FullName
                                $this->EntriesArray['ComponentFullName'][] = $DummyTop['Component'];
                                $this->EntriesArray['ComponentQuantity'][] = $DummyTop['Quantity'];

                	}


		}if($this->Debug){
		        print_r($this->EntriesArray);
		        print("<br>");
		        print("file name: ".$this->ExportsDir."/InventoryItems_Kit_".$y.".csv <br>");
		}
		if(!is_array($this->EntriesArray))$this->EntriesArray = array(''=>array(''));
		if($this->Write($this->ExportsDir."/InventoryItems_Kit_".$y.".csv",$this->EntriesArray)){
                        DB_Query("UPDATE `ExportTransTrack` set `Date`=NOW() , `IP`='".$_SERVER['REMOTE_ADDR']."' WHERE `Type`='GroupInv'",$this->db);
			return true;
		}
		else return false;
	//	}
	}

	// ##############################
	// Export KIT Inventory from weberp
	// ##############################

	function ExportNonInventory($y){
//	        global $_SESSION;
		$this->LastExport = 'Inventory Items [Dummy]';
		$Query = "SELECT * from `StockMaster`  WHERE `MBflag` = 'D'";
		$Result = DB_Query($Query,$this->db) or die(DB_error_msg());

		// first clear the array
		$this->EntriesArray = '';
		// now get the accounts from the companies table
		$Dummyres= DB_Query("SELECT `DebtorsAct`,`PayrollAct` from `Companies` where `CoyCode`=1",$this->db);
		$Dummy = DB_fetch_assoc($Dummyres);
		// check if ok

		$dummores = DB_Query("SELECT `AccountName` FROM `ChartMaster` WHERE `AccountCode`='".$Dummy['DebtorsAct']."'",$this->db);
		$dumm = DB_fetch_assoc($dummores);
		if(strlen($dumm['AccountName']) >29) $Name = substr($dumm['AccountName'],0,29)."_";
		else $Name = $dumm['AccountName'];	
		$IncomeAccount = $Name;
		$dummores = DB_Query("SELECT `AccountName` FROM `ChartMaster` WHERE `AccountCode`='".$Dummy['PayrollAct']."'",$this->db);
		$dumm = DB_fetch_assoc($dummores);
		if(strlen($dumm['AccountName']) >29) $Name = substr($dumm['AccountName'],0,29)."_";
		else $Name = $dumm['AccountName'];	
		$ExpenseAccount = $dumm['AccountName'];
		if($IncomeAccount =="" || $ExpenseAccount ==""){
//			print("<div align=center><font color=red >Please update your company preferences first.</font></div><br />");
			print("<div align=center>Please update the GL accounts settings in the <a href='CompanyPreferences.php'>company preferences</a> page.</div><br />");
			$_SESSION['ExportInProgress']=0;
			return false;
		}	
		while($row = DB_fetch_assoc($Result)){


                        //Name
			$this->EntriesArray['Name'][]=$row['StockID'];
			//SalesAndPurchase>SalesDesc
			$this->EntriesArray['SalesDesc'][]=($row['LongDescription']);

			//SalesAndPurchase>IncomeAccountRef>FullName
			$this->EntriesArray['IncomeFullName'][]=$IncomeAccount;

			//SalesAndPurchase>ExpenseAccountRef>FullName
			$this->EntriesArray['ExpenseFullName'][]=$ExpenseAccount;


		}if($this->Debug){
		        print_r($this->EntriesArray);
		        print("<br>");
		        print("file name: ".$this->ExportsDir."/InventoryItems_Dummy_".$y.".csv <br>");
		}
		if(!is_array($this->EntriesArray))$this->EntriesArray = array(''=>array(''));
		if($this->Write($this->ExportsDir."/InventoryItems_Dummy_".$y.".csv",$this->EntriesArray)){
                        DB_Query("UPDATE `ExportTransTrack` set `Date`=NOW() , `IP`='".$_SERVER['REMOTE_ADDR']."' WHERE `Type`='NonInv'",$this->db);
			return true;
		}
		else return false;
	//	}
	}

	// ##############################
	// Export Whole system
	// ##############################

	function ExportWholeSystem($y){
	        global $COA;
		$this->LastExport = 'Whole System';
		// 1) change exportfolder
		$OldDir = $this->ExportsDir;
		$this->ExportsDir = $this->ExportsDir."/tmp";
		@mkdir($this->ExportsDir,0777);
		// 2) start export sequence
		// get oldest customer and supplier
		$dummy = DB_Query("SELECT MIN(`ClientSince`) FROM `DebtorsMaster`",$this->db);
		$dummy1 = DB_fetch_row($dummy);
		$x1 = substr($dummy1[0],0,10);

		$dummy = DB_Query("SELECT MIN(`SupplierSince`) FROM `Suppliers`",$this->db);
		$dummy1 = DB_fetch_row($dummy);
		$x2 = substr($dummy1[0],0,10);

		$continue = true;
		if($continue)if(!$this->ExportAccounts($COA,$y)) $continue=false;
		if($continue)if(!$this->ExportCustomers($x1)) $continue=false;
		if($continue)if(!$this->ExportSuppliers($x2)) $continue=false;
		if($continue)if(!$this->ExportInventory($y)) $continue=false;
		if($continue)if(!$this->ExportNonInventory($y)) $continue=false;
		if($continue)if(!$this->ExportAssemblyInventory($y)) $continue=false;
		if($continue)if(!$this->ExportGroupInventory($y)) $continue=false;
		if($continue)if(!$this->ExportJournalEntries($y)) $continue=false;
		if($continue)if(!$this->ExportInvoices($y)) $continue=$continue;
		if($continue)if(!$this->ExportCreditNotes($y)) $continue=false;
		if($continue)if(!$this->ExportSalesOrders($y)) $continue=false;
		if($continue)if(!$this->ExportPurchaseOrders($y)) $continue=false;
		
		if(!$continue) return;
			// 3) make zip file in old exports dir
		
			exec("zip -ujq ".$OldDir."/WholeSystem_".$y.".zip ".$this->ExportsDir."/*.csv");
		
			// 4) delete all csv exorted files
		$Error = 0;
		if(!@unlink($this->ExportsDir."/Accounts".$y.".csv")) $Error = 1;
		if(!@unlink($this->ExportsDir."/Customers".$x1.".csv")) $Error = 1;
		if(!@unlink($this->ExportsDir."/Vendors".$x2.".csv")) $Error = 1;
		if(!@unlink($this->ExportsDir."/InventoryItems_Buy_".$y.".csv")) $Error = 1;
		if(!@unlink($this->ExportsDir."/InventoryItems_Kit_".$y.".csv")) $Error = 1;
		if(!@unlink($this->ExportsDir."/InventoryItems_Dummy_".$y.".csv")) $Error = 1;
		if(!@unlink($this->ExportsDir."/InventoryItems_Make-Assembly_".$y.".csv")) $Error = 1;
		if(!@unlink($this->ExportsDir."/Invoices".$y.".csv")) $Error = 1;
		if(!@unlink($this->ExportsDir."/CreditMemos".$y.".csv")) $Error = 1;
		if(!@unlink($this->ExportsDir."/JournalEntries".$y.".csv")) $Error = 1;
		if(!@unlink($this->ExportsDir."/SalesOrders".$y.".csv")) $Error = 1;
		if(!@unlink($this->ExportsDir."/PurchaseOrders".$y.".csv")) $Error = 1;
		
		// 5) Update ExportTransTrack
		if($Error == 0 ){
		
	                DB_Query("UPDATE `ExportTransTrack` set `Date`=NOW() , `IP`='".$_SERVER['REMOTE_ADDR']."' WHERE `Type`='WholeSystem'",$this->db);
			return true;
		}else{
		        return false;
		}
	}

	// ##############################
	// Select the function to use
	// ##############################

	function Export($x,$y){
		global $WWWLogos,$LogosFolder;
	        // --------------------------
		// first check for valid date
		
		$goon=true;
		$temp = explode("-",$y);
		if(count($temp) != 3) $goon=false;
		if(strlen($temp[0])!=4 || !is_numeric($temp[0]))$goon = false;
		if(strlen($temp[1])!=2 || !is_numeric($temp[1]))$goon = false;
		if(strlen($temp[2])!=2 || !is_numeric($temp[2]))$goon = false;
		if(!@checkdate($temp[1],$temp[2],$temp[0])) $goon = false;
		if($goon == false){
                        print("<center><font color=red>Value of Start Date has invalid Date format.</font></center><br>");
                        $browser = new FileBrowser($LogosFolder.$_SESSION['SelectedCompany']."/exports/QB");

			$browser->SetBrowsedFolder($WWWLogos.$_SESSION['SelectedCompany']."/exports/QB");
			$browser->SetIcon('bdoc.gif');
			$browser->SetFileType('csv');
			$browser->Browse();
			return false;
		}
// --------------------------

		switch($x){
		
		        case "Accounts":
				global $COA;
				if(!isset($COA)){
					print("<center><font color=red>Please Map All Accounts <a href='mapcoa.php'>here</a>.</font></center>");
				 	return false;
				}
				if(!$COA->IsMapped()){
					print("<center><font color=red>Please Map All Accounts <a href='mapcoa.php'>here</a>.</font></center>");
					return false;
				}
				if($this->ExportAccounts($COA,$y))return true;
				else return false;
		                break;

		        case "Customers":
                                if($this->ExportCustomers($y))return true;
                                else return false;
		                break;

		        case "CreditMemos":
                                if($this->ExportCreditNotes($y))return true;
                                else return false;
		                break;
		                
		        case "Invoices":
                                if($this->ExportInvoices($y))return true;
                                else return false;
		                break;
		                
		        case "JournalEntries":
				if($this->ExportJournalEntries($y))return true;
				else return false;
		                break;

		        case "PurchaseOrders":
				if($this->ExportPurchaseOrders($y))return true;
				else return false;
		                break;

		        case "SalesOrders":
				if($this->ExportSalesOrders($y))return true;
				else return false;
		                break;

		        case "Vendors":
				if($this->ExportSuppliers($y))return true;
				else return false;
		                break;
		                
		        case "WholeSystem":
				if($this->ExportWholeSystem($y))return true;
				else return false;
		                break;
		                
		        case "InventoryItems":
				global $COA;
				$_SESSION['exporteditems'] = 'InventoryItems';
				if(!isset($COA)){
					print("<center><font color=red>Please Map All Accounts <a href='mapcoa.php'>here</a>.</font></center>");
				 	return false;
				}
				if(!$COA->IsMapped()){
					print("<center><font color=red>Please Map All Accounts <a href='mapcoa.php'>here</a>.</font></center>");
					return false;
				}
				if($this->ExportInventory($y))return true;
				else return false;
		                break;
		                
		        case "AssemblyInv":
				global $COA;
				$_SESSION['exporteditems'] = 'AssemblyInv';
				if(!isset($COA)){
					print("<center><font color=red>Please Map All Accounts <a href='mapcoa.php'>here</a>.</font></center>");
				 	return false;
				}
				if(!$COA->IsMapped()){
					print("<center><font color=red>Please Map All Accounts <a href='mapcoa.php'>here</a>.</font></center>");
					return false;
				}
				if($this->ExportAssemblyInventory($y))return true;
				else return false;
		                break;

		        case "GroupInv":
				global $COA;
				$_SESSION['exporteditems'] = 'GroupInv';
				if($this->ExportGroupInventory($y))return true;
				else return false;
		                break;
		                
		        case "NonInv":
				global $COA;
				$_SESSION['exporteditems'] = 'NonInv';
				if($this->ExportNonInventory($y))return true;
				else return false;
		                break;

		        default:
		                return false;
		}
		
	}
	
	// ##############################
	// Set the exports dir
	// ##############################
	
	function SetExportsDir($dir){
		// -------------------------------- 
		if(!is_dir($dir)){
			
			@mkdir($dir,0777);
			if(!is_dir($dir)){
				if($this->Debug) print("specified Exports dir is Not a Dir<br />");
				return false;
			}
		}
		
		// writable 
		if(!is_writable($dir)){
			@chmod($dir,0777);
			
			if(!is_writable($dir)){
				if($this->Debug) print("specified Exports dir is Not writable<br />");
				return false;
			}
		}	
		
		// -------------------------------------
		$this->ExportsDir=$dir;
		return true;
		
	}
	
	// ####################################
	// Enable the debug
	// ####################################
	
	function EnableDebug($x=true){
	        $this->Debug = $x;
	}

}

//$dbconn = mysql_connect("localhost","root","") or die("ha2aaw");
//mysql_select_db('weberp');
$test = new ERPExport($db);
$test->EnableDebug(false);
if(! is_dir($LogosFolder.$_SESSION['SelectedCompany']."/exports/QB")) mkdir($LogosFolder.$_SESSION['SelectedCompany']."/exports/QB",0777);
$test->SetExportsDir($LogosFolder.$_SESSION['SelectedCompany']."/exports/QB");
//print_r($_SESSION);
if($test->Debug){
	$test->ExportCustomers();
	print("__________________________________________________<br>Suppliers:<br>");
	print("__________________________________________________<br>");
	$test->ExportSuppliers();
	print("__________________________________________________<br>COA:<br>");
	print("__________________________________________________<br>");
	$test->ExportAccounts($COA);
	print("__________________________________________________<br>Invoices:<br>");
	print("__________________________________________________<br>");
	$test->ExportInvoices('2000-01-01');
	print("__________________________________________________<br>Sales Orders:<br>");
	print("__________________________________________________<br>");
	$test->ExportSalesOrders('2000-01-01');
	print("__________________________________________________<br>Credit Notes:<br>");
	print("__________________________________________________<br>");
	$test->ExportCreditNotes('2000-01-01');
	print("__________________________________________________<br>Purchase Orders:<br>");
	print("__________________________________________________<br>");
	$test->ExportPurchaseOrders('2000-01-01');
	print("__________________________________________________<br>Journal Entries:<br>");
	print("__________________________________________________<br>");
	$test->ExportJournalEntries('2000-01-01');
}
?>

<?php
// #####################################################
// Code by Waleed a. Meligy - ITWorx , march 2005
// #####################################################
// ############ last modified 11/04/2005 by waleed A. Meligy ##############
include_once("includes/CSVclasses.php");
include_once("includes/csv.inc.php");
require_once("includes/COAMap.inc.php");
include_once('includes/SQL_CommonFunctions.inc');
//require_once("includes/archive.php");
require_once('includes/xmlize.inc');
require_once('includes/XMLChunks.inc.php');
require_once('includes/DateFunctions.inc');
$COA= new COAMap("AccountGroupsMap_".$_SESSION['SelectedCompany'],0);
$COA_invitems= new COAMap("InvItemsStockCatMap_".$_SESSION['SelectedCompany'],0);
$COA_noninvitems= new COAMap("NonInvItemsStockCatMap_".$_SESSION['SelectedCompany'],0);
$COA_kits= new COAMap("ItemGroupStockCatMap_".$_SESSION['SelectedCompany'],0);
$COA_Mass= new COAMap("ItemAssemblyStockCatMap_".$_SESSION['SelectedCompany'],0);
//print("hhh");
//print_r($_SESSION['FullName']);
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

Class ERPImport extends CSVImport{

	var $Debug;				// want to view debging messages
	var $DB; 				// db resource
	var $FileName;                  // String - The uploaded file full path
	var $DataArray;
	var $DefDateFormat;
	var $LocCode;
	var $LocName;

	// ##############################
	// Constructor
	// ##############################

	function ERPImport($filename,$dbResource){

		global $_SERVER,$DefaultDateFormat;

		if(!$this->VerifyPermission()){
			$this->ErrorReport("You don't have permission to use this  Page.");
			exit(1);
		}
		if(!file_exists($filename)){
			$this->ErrorReport("File doesn't exist.");
			exit(1);
		}
		if(!is_readable($filename)){
			$this->ErrorReport("File can not be read.");
			exit(1);
		}
		$this->LocCode = 'QBSTK';
		$this->LocName = 'QuickBooks Stock Location';
		$this->FileName = $filename;
		$this->TotalEntries = 0;
		$this->TotalTrials = 0;
		$this->TotalRepEntries = 0;
		$this->Debug=false;
		$this->DefDateFormat= $DefaultDateFormat;
		$this->DB = $dbResource;
/*
	        $dummy = $_SERVER['PHP_SELF'];
	        $dummy1 = explode("/",$dummy);

	        for($j=0;$j<(count($dummy)-1);$j++){
	                $working_dir .=$dummy1[$j]."/";
	        }
        	$this->URL = "http://".$_SERVER['HTTP_HOST'].$working_dir;
        */
	}

	// ##############################
	// Set the locations details
	// ##############################

	function SetLocation($LocCode,$LocName){
	        $this->LocCode = $LocCode;
	        $this->LocName = $LocName;
	}

	// ##############################
	// Set the exports dir
	// ##############################

	function SetUploadDir($dir){
		// --------------------------------
		if(!is_dir($dir)){

			@mkdir($dir,0777);
			if(!is_dir($dir)){
				if($this->Debug) $this->ErrorReport("specified Uploads dir is Not a Dir.");
				return false;
			}
		}

		// writable
		if(!is_writable($dir)){
			@chmod($dir,0777);

			if(!is_writable($dir)){
				if($this->Debug) $this->ErrorReport("specified uploads dir is Not writable.");
				return false;
			}
		}

		// readable
		if(!is_readable($dir)){
			@chmod($dir,0777);

			if(!is_readable($dir)){
				if($this->Debug) $this->ErrorReport("specified uploads dir is Not writable.");
				return false;
			}
		}
		// -------------------------------------
		$this->UploadDir=$dir;
		return true;

	}

	// ####################################
	// Enable the debug
	// ####################################

	function ImportQB($x,$new){

		global $COA,$COA_invitems,$COA_noninvitems,$COA_kits,$COA_Mass,$CompaniesFolder;


		// ^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^

		// first read the entries from the file


		$file = file_get_contents($this->FileName);
//print($this->FileName."<br>");
//print($file);
//		$test = xmlize($file);
		$opFolder = $CompaniesFolder.$_SESSION['SelectedCompany']."/exports/QB/uploads/";
			//
			// now adjust the DataArray in case of accounts

		if($x=="Accounts"){

			$i=1;
			// create xml chunks in the tmp dir of each company
			$XMLChunks = new XMLChunks(basename($this->FileName),$opFolder,80480);
			$XMLChunks->GenChunks("AccountRet","AccountRet",$opFolder);
			$files = $XMLChunks->FetchFiles();

			foreach($files as $file){

				$test = xmlize(implode('',file($file)));
		//		print($file."<br>");
				if(!is_array($test['QBXML']['#']['QBXMLMsgsRs'][0]['#']['AccountQueryRs'][0]['#']['AccountRet'])){
				        // invalid file
					$this->ErrorReport("Please upload a valid Accounts QBXML file.");
					return false;
				}

				foreach($test['QBXML']['#']['QBXMLMsgsRs'][0]['#']['AccountQueryRs'][0]['#']['AccountRet'] as $entry){

					$this->DataArray['Account'][$i] = $this->AdjustEntry($entry['#']['FullName'][0]['#']);
//					$this->DataArray['FullName'][$i] = $this->AdjustEntry($entry['#']['FullName'][0]['#']);
			//		$_SESSION['FullName'][$i] = $this->AdjustEntry($entry['#']['FullName'][0]['#']);
					$this->DataArray['Accnt. #'][$i] = $entry['#']['AccountNumber'][0]['#'];
					$i++;

				}
			}
			$i=0;


		}elseif($x=="InventoryItems"){
		// Inventory ITEMS [ BUY ]

			$i=1;
			// create xml chunks in the tmp dir of each company
			$XMLChunks = new XMLChunks(basename($this->FileName),$opFolder,80480);
			$XMLChunks->GenChunks("ItemInventoryRet","ItemInventoryRet",$opFolder);
			$files = $XMLChunks->FetchFiles();

			foreach($files as $file){

				$test = xmlize(implode('',file($file)));

				if(!is_array($test['QBXML']['#']['QBXMLMsgsRs'][0]['#']['ItemInventoryQueryRs'][0]['#']['ItemInventoryRet'])){
				        // invalid file
					$this->ErrorReport("Please upload a valid Inventory Items [ Buy ] QBXML file.");
					return false;
				 }


				foreach($test['QBXML']['#']['QBXMLMsgsRs'][0]['#']['ItemInventoryQueryRs'][0]['#']['ItemInventoryRet'] as $entry){

					$this->DataArray['StockID'][$i] = $this->AdjustEntry($entry['#']['FullName'][0]['#'],1);
//					$this->DataArray['FullName'][$i] = $this->AdjustEntry($entry['#']['FullName'][0]['#']);
//					$_SESSION['FullName'][$i] = $this->AdjustEntry($entry['#']['FullName'][0]['#']);
					$this->DataArray['EOQ'][$i] = $entry['#']['ReorderPoint'][0]['#'];
					if(!$entry['#']['PurchaseDesc'][0]['#']) $entry['#']['PurchaseDesc'][0]['#'] = $entry['#']['SalesDesc'][0]['#'];
					$this->DataArray['LongDescription'][$i] = $this->AdjustEntry($entry['#']['PurchaseDesc'][0]['#']);

					if(strlen($this->DataArray['LongDescription'][$i]) > 49)$this->DataArray['Description'][$i] = substr($entry['#']['PurchaseDesc'][0]['#'],0,49)."_";

					else $this->DataArray['Description'][$i] = $this->DataArray['LongDescription'][$i];

					$this->DataArray['Quantity'][$i] = $entry['#']['QuantityOnHand'][0]['#'];

				 	$i++;


				}
			}
			$i=0;
		}
                // -----------------------------------------------------------------------------------------
		elseif($x=="NonInv"){

		// Non Inventory ITEMS [ DUMMY ]

			$i=1;

			// divide the mainfile into to for each rs
			// first seperate the NonInv fro mthe service
//			$XMLChunks = new XMLChunks(basename($this->FileName),$opFolder,80480);
//			$XMLChunks->GenChunks("QBXMLMsgsRs","QBXMLMsgRs",$opFolder);
//			$sepfiles = $XMLChunks->FetchFiles();
			//print("<script>alert('".$XMLChunks->FileName."');</script>");
//print("<br>hoolaaa: ".$XMLChunks->NumFiles);
			$XMLChunks1 = new XMLChunks(basename($this->FileName),$opFolder,80480);
			$XMLChunks1->GenChunks("ItemNonInventoryRet","ItemNonInventoryRet",$opFolder);
			$NonInvfiles = $XMLChunks1->FetchFiles();
			$file1 = $this->FileName.".tmp";
			copy($this->FileName,$file1);
			$XMLChunks2 = new XMLChunks(basename($file1),$opFolder,80480);
			$XMLChunks2->GenChunks("ItemServiceRet","ItemServiceRet",$opFolder);
			$Servicefiles = $XMLChunks2->FetchFiles();
			@unlink($file1);
			if(count($Servicefiles) < 1 && count($NonInvfiles) < 1){
				  // invalid file
				$this->ErrorReport("Please upload a valid Inventory Items [ Dummy ] QBXML file.");
				return false;
			}
// %%%
			foreach($NonInvfiles as $file){
//print $file;
				$test = xmlize(implode('',file($file)));
//print_r($test);

				// handle the non inventory items first
				if(is_array($test['QBXML']['#']['QBXMLMsgsRs'][0]['#']['ItemNonInventoryQueryRs'][0]['#']['ItemNonInventoryRet'])){
					foreach($test['QBXML']['#']['QBXMLMsgsRs'][0]['#']['ItemNonInventoryQueryRs'][0]['#']['ItemNonInventoryRet'] as $entry){

						$this->DataArray['StockID'][$i] = $this->AdjustEntry($entry['#']['FullName'][0]['#'],1);
//						$this->DataArray['FullName'][$i] = $this->AdjustEntry($entry['#']['FullName'][0]['#']);
//						$_SESSION['FullName'][$i] = $this->AdjustEntry($entry['#']['FullName'][0]['#']);
           	                             if(!$entry['#']['SalesAndPurchase'][0]['#']['PurchaseDesc'][0]['#']) $entry['#']['SalesAndPurchase'][0]['#']['PurchaseDesc'][0]['#'] = $entry['#']['SalesAndPurchase'][0]['#']['SalesDesc'][0]['#'];
						 if(!$entry['#']['SalesOrPurchase'][0]['#']['PurchaseDesc'][0]['#']) $entry['#']['SalesOrPurchase'][0]['#']['PurchaseDesc'][0]['#'] = $entry['#']['SalesOrPurchase'][0]['#']['SalesDesc'][0]['#'];


						if(isset($entry['#']['SalesAndPurchase'][0]['#']['PurchaseDesc'][0]['#'])){
							$this->DataArray['LongDescription'][$i] = $this->AdjustEntry($entry['#']['SalesAndPurchase'][0]['#']['PurchaseDesc'][0]['#']);


							if(strlen($this->DataArray['LongDescription'][$i]) > 49)$this->DataArray['Description'][$i] = substr($this->AdjustEntry($entry['#']['SalesAndPurchase'][0]['#']['PurchaseDesc'][0]['#']),0,49)."_";

							else $this->DataArray['Description'][$i] = $this->AdjustEntry($this->DataArray['LongDescription'][$i]);
	                                	}else{

							$this->DataArray['LongDescription'][$i] = $this->AdjustEntry($entry['#']['SalesOrPurchase'][0]['#']['Desc'][0]['#']);


							if(strlen($this->DataArray['LongDescription'][$i]) > 49)$this->DataArray['Description'][$i] = substr($this->AdjustEntry($entry['#']['SalesOrPurchase'][0]['#']['Desc'][0]['#']),0,49)."_";

							else $this->DataArray['Description'][$i] = $this->AdjustEntry($this->DataArray['LongDescription'][$i]);

						}
					        $i++;


					}

				}
			   }
			foreach($Servicefiles as $file){
			        $test = xmlize(implode('',file($file)));
				// then handle services
				if(is_array($test['QBXML']['#']['QBXMLMsgsRs'][0]['#']['ItemServiceQueryRs'][0]['#']['ItemServiceRet'])){
					foreach($test['QBXML']['#']['QBXMLMsgsRs'][0]['#']['ItemServiceQueryRs'][0]['#']['ItemServiceRet'] as $entry){

						$this->DataArray['StockID'][$i] = $this->AdjustEntry($entry['#']['FullName'][0]['#'],1);
//						$this->DataArray['FullName'][$i] = $this->AdjustEntry($entry['#']['FullName'][0]['#']);
//						$_SESSION['FullName'][$i] = $this->AdjustEntry($entry['#']['FullName'][0]['#']);
 if(!$entry['#']['SalesAndPurchase'][0]['#']['PurchaseDesc'][0]['#']) $entry['#']['SalesAndPurchase'][0]['#']['PurchaseDesc'][0]['#'] = $entry['#']['SalesAndPurchase'][0]['#']['SalesDesc'][0]['#'];
                                                 if(!$entry['#']['SalesOrPurchase'][0]['#']['PurchaseDesc'][0]['#']) $entry['#']['SalesOrPurchase'][0]['#']['PurchaseDesc'][0]['#'] = $entry['#']['SalesOrPurchase'][0]['#']['SalesDesc'][0]['#'];


						if(isset($entry['#']['SalesAndPurchase'][0]['#']['PurchaseDesc'][0]['#'])){
							$this->DataArray['LongDescription'][$i] = $this->AdjustEntry($entry['#']['SalesAndPurchase'][0]['#']['PurchaseDesc'][0]['#']);


							if(strlen($this->DataArray['LongDescription'][$i]) > 49)$this->DataArray['Description'][$i] = substr($this->AdjustEntry($entry['#']['SalesAndPurchase'][0]['#']['PurchaseDesc'][0]['#']),0,49)."_";

							else $this->DataArray['Description'][$i] = $this->AdjustEntry($this->DataArray['LongDescription'][$i]);
	                                	}else{

							$this->DataArray['LongDescription'][$i] = $this->AdjustEntry($entry['#']['SalesOrPurchase'][0]['#']['Desc'][0]['#']);


							if(strlen($this->DataArray['LongDescription'][$i]) > 49)$this->DataArray['Description'][$i] = substr($this->AdjustEntry($entry['#']['SalesOrPurchase'][0]['#']['Desc'][0]['#']),0,49)."_";

							else $this->DataArray['Description'][$i] = $this->AdjustEntry($this->DataArray['LongDescription'][$i]);

						}
					        $i++;


					}

					// end of this foreach of the chunks
				}

			}
			$i=0;

		}
		// -----------------------------------------------------------------------------------------
		elseif($x=="GroupInv"){
		// Group ITEMS [ KIT ]
			$i=1;

			$XMLChunks = new XMLChunks(basename($this->FileName),$opFolder,80480);
			$XMLChunks->GenChunks("ItemGroupRet","ItemGroupRet",$opFolder);
			$files = $XMLChunks->FetchFiles();

			foreach($files as $file){
			        $test = xmlize(implode('',file($file)));
				if(!is_array($test['QBXML']['#']['QBXMLMsgsRs'][0]['#']['ItemGroupQueryRs'][0]['#']['ItemGroupRet'])){
				        // invalid file
					$this->ErrorReport("Please upload a valid Inventory Items [ Kit ] QBXML file.");
					return false;
				}

				foreach($test['QBXML']['#']['QBXMLMsgsRs'][0]['#']['ItemGroupQueryRs'][0]['#']['ItemGroupRet'] as $entry){

					$this->DataArray['StockID'][$i] = $this->AdjustEntry($entry['#']['Name'][0]['#'],1);
//					$_SESSION['FullName'][$i] = $this->AdjustEntry($entry['#']['FullName'][0]['#']);
					//print("Before adjust and just after xmlize: ".$entry['#']['ItemDesc'][0]['#']."<br>");
					//print("After Adjust : ".$this->AdjustEntry($entry['#']['ItemDesc'][0]['#'])."<br>");
					$this->DataArray['LongDescription'][$i] = trim($this->AdjustEntry($entry['#']['ItemDesc'][0]['#']));

					if(strlen($this->DataArray['LongDescription'][$i]) > 49)$this->DataArray['Description'][$i] = substr($this->AdjustEntry($entry['#']['ItemDesc'][0]['#']),0,49)."_";

					else $this->DataArray['Description'][$i] = $this->AdjustEntry($this->DataArray['LongDescription'][$i]);

					// to get over the empty entries exported by QB!!
					if(is_array($entry['#']['ItemGroupLine'])){
						foreach($entry['#']['ItemGroupLine'] as $centry){
				    	// make sure we get the name of the item WITHOUT the parents
							$tako = $centry['#']['ItemRef'][0]['#']['FullName'][0]['#'];
							if($tako=='')$tako = $centry['#']['ItemGroupRef'][0]['#']['FullName'][0]['#'];
//							$indexer = count($thename);
							$thename = explode(":",$this->AdjustEntry($tako,1));
						
							$indexer = count($thename);
							//print("haaay: ".$thename[$indexer-1]."<br>");
							if($thename[$indexer-1]!=""){
								$thename[$indexer-1] = $this->GetID(
								'StockID',
								$thename[$indexer-1],
								'FullName',
								$this->AdjustEntry($tako,1),
								'StockMaster',
								20);
								if($centry['#']['Quantity'][0]['#'] !="") $_SESSION['KitComponents'][$i][$thename[$indexer-1]] = $centry['#']['Quantity'][0]['#'];
								else   $_SESSION['KitComponents'][$i][$thename[$indexer-1]] = 1;
							}
						        //print("haaay: ".$thename[$indexer-1]."<br>");

						}
					}

				     $i++;


				}


			}
			$i = 0;
		}

		// -----------------------------------------------------------------------------------------
		elseif($x=="AssemblyInv"){
		// Group ITEMS [ Make,assembly ]
			$i=1;
			$XMLChunks = new XMLChunks(basename($this->FileName),$opFolder,80480);
			$XMLChunks->GenChunks("ItemInventoryAssemblyRet","ItemInventoryAssemblyRet",$opFolder);
			$files = $XMLChunks->FetchFiles();
		//print("<script>alert('".$files."');</script>");
			foreach($files as $file){
			        $test = xmlize(implode('',file($file)));
				if(!is_array($test['QBXML']['#']['QBXMLMsgsRs'][0]['#']['ItemInventoryAssemblyQueryRs'][0]['#']['ItemInventoryAssemblyRet'])){
				        // invalid file
					$this->ErrorReport("Please upload a valid Inventory Items [ Make,Assembly ] QBXML file.");
					return false;
				 }

				foreach($test['QBXML']['#']['QBXMLMsgsRs'][0]['#']['ItemInventoryAssemblyQueryRs'][0]['#']['ItemInventoryAssemblyRet'] as $entry){

					$this->DataArray['StockID'][$i] = $this->AdjustEntry($entry['#']['FullName'][0]['#'],1);
//					$_SESSION['FullName'][$i] = $this->AdjustEntry($entry['#']['FullName'][0]['#']);
					$this->DataArray['LongDescription'][$i] = trim($this->AdjustEntry($entry['#']['SalesDesc'][0]['#']));

					if(strlen($this->DataArray['LongDescription'][$i]) > 49)$this->DataArray['Description'][$i] = substr($this->AdjustEntry($entry['#']['SalesDesc'][0]['#']),0,49)."_";

					else $this->DataArray['Description'][$i] = $this->AdjustEntry($this->DataArray['LongDescription'][$i]);
					$this->DataArray['Quantity'][$i] = $entry['#']['QuantityOnHand'][0]['#'];

					// to get over the empty entries exported by QB!!
					if(is_array($entry['#']['ItemInventoryAssemblyLine'])){
						foreach($entry['#']['ItemInventoryAssemblyLine'] as $centry){
					    	// make sure we get the name of the item WITHOUT the parents
							$thename = explode(":",$this->AdjustEntry($centry['#']['ItemInventoryRef'][0]['#']['FullName'][0]['#'],1));
							$indexer = count($thename);
							if($thename[$indexer-1]!=""){
								$thename[$indexer-1] = $this->GetID(
								'StockID',
								$thename[$indexer-1],
								'FullName',
								$this->AdjustEntry($centry['#']['ItemInventoryRef'][0]['#']['FullName'][0]['#'],1),
								'StockMaster',
								20);
								if($centry['#']['Quantity'][0]['#'] !="") $_SESSION['MassComponents'][$i][$thename[$indexer-1]] = $centry['#']['Quantity'][0]['#'];
								else   $_SESSION['MassComponents'][$i][$thename[$indexer-1]] = 1;
							}
						}
					}

				     $i++;


				}


			}
			$i= 0;
		}
                	// -----------------------------------------------------------------------------------------
		else{
			// for all others to process the request as they wish, but we need to part them and so
			switch($x){
				// -----------------------------------------------------------------------------------------					
			        case "CreditMemos" :
					$XMLChunks = new XMLChunks(basename($this->FileName),$opFolder,80480);
					$XMLChunks->GenChunks("CreditMemoRet","CreditMemoRet",$opFolder);
					$files = $XMLChunks->FetchFiles();
					$TMP = "CreditMemo";
			                break;
				// -----------------------------------------------------------------------------------------					
				case "Customers" :
					$XMLChunks = new XMLChunks(basename($this->FileName),$opFolder,80480);
					$XMLChunks->GenChunks("CustomerRet","CustomerRet",$opFolder);
					$files = $XMLChunks->FetchFiles();
					$TMP = "Customer";
			                break;
				// -----------------------------------------------------------------------------------------						
				case "Invoices":

					$XMLChunks = new XMLChunks(basename($this->FileName),$opFolder,80480);
					$XMLChunks->GenChunks("InvoiceRet","InvoiceRet",$opFolder);
					$files = $XMLChunks->FetchFiles();
					$TMP = "Invoice";
			                break;
				// -----------------------------------------------------------------------------------------						
				case "JournalEntries":
					$XMLChunks = new XMLChunks(basename($this->FileName),$opFolder,80480);
					$XMLChunks->GenChunks("JournalEntryRet","JournalEntryRet",$opFolder);
					$files = $XMLChunks->FetchFiles();
					$TMP = "JournalEntry";
			                break;
				// -----------------------------------------------------------------------------------------						
				case "PurchaseOrders":
					$XMLChunks = new XMLChunks(basename($this->FileName),$opFolder,80480);
					$XMLChunks->GenChunks("PurchaseOrderRet","PurchaseOrderRet",$opFolder);
					$files = $XMLChunks->FetchFiles();
					$TMP = "PurchaseOrder";
			                break;
				// -----------------------------------------------------------------------------------------					
				case "SalesOrders":
					$XMLChunks = new XMLChunks(basename($this->FileName),$opFolder,80480);
					$XMLChunks->GenChunks("SalesOrderRet","SalesOrderRet",$opFolder);
					$files = $XMLChunks->FetchFiles();
					$TMP = "SalesOrder";
				        break;
				// -----------------------------------------------------------------------------------------					
				case "Vendors":
					$XMLChunks = new XMLChunks(basename($this->FileName),$opFolder,80480);
					$XMLChunks->GenChunks("VendorRet","VendorRet",$opFolder);
					$files = $XMLChunks->FetchFiles();
					$TMP = "Vendor";
			                break;
				// -----------------------------------------------------------------------------------------						
			}
			$i=0;
			if(is_array($files)){
				foreach($files as $file){

//int($file."<br>");

				        $test = xmlize(implode("",file($file)));
					$v=0;
				        while($test['QBXML']['#']['QBXMLMsgsRs'][0]['#'][$TMP.'QueryRs'][0]['#'][$TMP.'Ret'][$v]['#']){
						$this->DataArray[$TMP.'QueryRs'][0]['#'][$TMP.'Ret'][$i] = $test['QBXML']['#']['QBXMLMsgsRs'][0]['#'][$TMP.'QueryRs'][0]['#'][$TMP.'Ret'][0];
						$i++;
						$v++;
					}
				}
			}
		}
		unset($test);
		unset($files);
		unset($XMLChunks);
		unset($XMLChunks1);
		unset($XMLChunks2);
		//var_dump($this->DataArray);
		// ^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^
	        $Type= $x;
		//require("includes/QBdefined_tables.php");
		//$this->TablesArray = $dbtable;


		// check if map is there and mapped
		if(!$COA->IsMapped() && $Type == "Accounts"){
			// if not, first extract them from the file
			$temparray['AccountCode'] = $this->DataArray['Accnt. #'];
			$temparray['AccountName'] = $this->DataArray['Account'];
			$temparray['FullName'] = $this->DataArray['FullName'];
			$_SESSION['QBAccounts'] = serialize($temparray);
			unset($temparray);


			// then rediredt to the mapagrp.php?imported=TYPE

			print("<script type='text/javascript'>document.location = 'mapagrp.php?imported=".$x."&new=".$new."'</script>");

		}
		// ---------------------------------------------------------------------------------
		elseif(!$COA_invitems->IsMapped() && $Type == "InventoryItems"){
			// if not, first extract them from the file
			$temparray['StockID'] = $this->DataArray['StockID'];
			$temparray['EOQ'] = $this->DataArray['EOQ'];
			$temparray['Description'] = $this->DataArray['Description'];
			$temparray['LongDescription'] = $this->DataArray['LongDescription'];
			$temparray['Quantity'] = $this->DataArray['Quantity'];
			$temparray['FullName'] = $this->DataArray['FullName'];
			$_SESSION['QBInvStock'] = serialize($temparray);
			unset($temparray);

				// then rediredt to the mapscat.php?imported=TYPE
			print("<script type='text/javascript'>document.location = 'mapscat.php?imported=".$x."&new=".$new."'</script>");

		}
		// ---------------------------------------------------------------------------------
		elseif(!$COA_noninvitems->IsMapped() && $Type == "NonInv"){
			// if not, first extract them from the file
			$temparray['StockID'] = $this->DataArray['StockID'];
			$temparray['Description'] = $this->DataArray['Description'];
			$temparray['LongDescription'] = $this->DataArray['LongDescription'];
			$temparray['FullName'] = $this->DataArray['FullName'];
			$_SESSION['QBNonInvStock'] = serialize($temparray);
//print("<hr>Array:<hr>");
//print_r($this->DataArray);
			unset($temparray);

				// then rediredt to the mapscat.php?imported=TYPE
			print("<script type='text/javascript'>document.location = 'mapscat.php?imported=".$x."&new=".$new."'</script>");

		}
		// ---------------------------------------------------------------------------------
		elseif(!$COA_kits->IsMapped() && $Type == "GroupInv"){
			// if not, first extract them from the file
			$temparray['StockID'] = $this->DataArray['StockID'];
			$temparray['Description'] = $this->DataArray['Description'];
			$temparray['LongDescription'] = $this->DataArray['LongDescription'];
			$temparray['FullName'] = $this->DataArray['FullName'];
			$_SESSION['QBGroupInvStock'] = serialize($temparray);
			unset($temparray);

			// then rediredt to the mapscat.php?imported=TYPE
			print("<script type='text/javascript'>document.location = 'mapscat.php?imported=".$x."&new=".$new."'</script>");

		}
		// ---------------------------------------------------------------------------------
		elseif(!$COA_Mass->IsMapped() && $Type == "AssemblyInv"){
			// if not, first extract them from the file
			$temparray['StockID'] = $this->DataArray['StockID'];
			$temparray['Description'] = $this->DataArray['Description'];
			$temparray['LongDescription'] = $this->DataArray['LongDescription'];
			$temparray['Quantity'] = $this->DataArray['Quantity'];
			$temparray['FullName'] = $this->DataArray['FullName'];
			$_SESSION['QBAssemblyInvStock'] = serialize($temparray);
			unset($temparray);

			// then rediredt to the mapscat.php?imported=TYPE
			print("<script type='text/javascript'>document.location = 'mapscat.php?imported=".$x."&new=".$new."'</script>");

		}
		// ---------------------------------------------------------------------------------
		else{
			// BUT if map is there Or we don't need it anyway, start entering into the db

			        // if yes start entry [ POST VARS ]
/*
				$ch = curl_init();
				curl_setopt($ch, CURLOPT_URL,$this->URL."GLAccounts.php");
				curl_setopt($ch, CURLOPT_POST, 1);
				curl_setopt($ch, CURLOPT_POSTFIELDS, "AccountCode=".$COA[$i]['Number']."&AccountName=".$COA[$i]['Name']."&Group=".$COA[$i]['Map']."&submit=yes");
				curl_exec ($ch);
				curl_close ($ch);
*/

                       switch($x){
				case "Accounts":
					return $this->ImportAccounts($COA->Accounts);
					break;

				case "InventoryItems":
					return $this->ImportInventoryItems($COA_invitems->Accounts);
				        break;

				case "NonInv":
					return $this->ImportNonInventoryItems($COA_noninvitems->Accounts);
				        break;

				case "GroupInv":
					return $this->ImportKits($COA_kits->Accounts);
				        break;

				case "AssemblyInv":
					return $this->ImportAssembly($COA_Mass->Accounts);
				        break;

				case "Customers":
					return $this->ImportCustomers();
				    break;

				case "Vendors":
					return $this->ImportVendors();
				    break;

				case "JournalEntries":
					return $this->ImportJournalEntries();
				    break;

				case "SalesOrders":
				    return $this->ImportSalesOrders();
				    break;

				case "PurchaseOrders":
				    return $this->ImportPurchaseOrders();
				    break;

				case "Invoices":

				    return $this->ImportInvoices();
				    break;

				case "CreditMemos":
				    return $this->ImportCreditMemos();
				    break;

				default:
				        return false;
			}



		}
	}

	// ####################################
	// Enable the debug
	// ####################################

	function EnableDebug($x=true){
	        $this->Debug = $x;
	}

	// ####################################
	// print error messages
	// ####################################

	function ErrorReport($x){
	        print("<div align=center><font color=red>".$x."</font></div><br>");
	        return;
	}

	// ####################################
	// Function to import accounts
	// ####################################

	function ImportAccounts(&$data){
		// now first adjust the first array to be written for the 'ChartMaster'
		$count =count($data);
		for($i=0;$i<$count;$i++){
			$teko = explode(":",$data[$i]['Number']);
			$name = $teko [ count($teko)-1];
			$temparray['AccountName'][]= $this->GetID(
			'AccountName',
			$this->AdjustEntry($name),
			'FullName',
			$this->AdjustEntry($data[$i]['Number']),
			'ChartMaster',
			50,
			$temparray['AccountName']
			);
			$temparray['FullName'][] =$this->AdjustEntry($data[$i]['Number']);
			$temparray['AccountCode'][]= $data[$i]['Name'];
			$temparray['Group_'][]= $data[$i]['Map'];
		}


		$files[0] = $this->UploadDir."/ChartMaster.csv";
		CSV_Titles::Write($files[0],$temparray,1);


		// then for the 'chartDetails' , enter one empty entry for all periods
	unset(	$temparray);

	// initialize all periods

		$tempdate= date($this->DefDateFormat);
		$Period = GetPeriod($tempdate,$this->DB);
		$dummy = DB_Query("SELECT `PeriodNo` FROM `Periods`",$this->DB);
                while($periods = DB_fetch_row($dummy) ){
			$period[] = $periods[0];
		}



		for($i=0;$i<$count;$i++){

                        foreach($period as $dummy ){
				$temparray['AccountCode'][] = $data[$i]['Name'];
				$temparray['Period'][] = $dummy;
			}

		}

		$files[1] = $this->UploadDir."/ChartDetails.csv";
		CSV_Titles::Write($files[1],$temparray,1);
		unset($temparray);

		// ******* Now make archive **********
		@unlink($this->UploadDir."/Accounts.zip");
		foreach($files as $tobeadded){
			exec("zip -ujq ".$this->UploadDir."/Accounts.zip ".$tobeadded);
		}


		// now cleanup what is left of the files

		foreach($files as $tobedeleted){
			unlink($tobedeleted);
		}

		// ***********************************

		// now do the import
		$this->TablesFrom = array("ChartMaster"=>array("AccountCode","FullName","AccountName","Group_"),"ChartDetails"=>array("Period","AccountCode"));
		$this->CustomType = true;

		if($this->Import("Accounts.zip")){
			print("<div align=center ><b>Accounts</b> item imported successfully.</div><br><br>");
			//unset($_SESSION['FullName']);
			$this->PrintReports();
		}


	}

	// ####################################
	// Function to import Inventory Items [ buy ]
	// ####################################

	function ImportInventoryItems(&$data){

		$count =count($data);


		// now first make sure there is a default QB stock location
		$dummy = DB_Query("SELECT `LocCode` FROM `Locations` WHERE `LocCode` = '$this->LocCode' ",$this->DB);
		if(DB_num_rows($dummy) == 0){
		        DB_Query("INSERT INTO `Locations`(`LocCode`,`LocationName`) VALUES('$this->LocCode','$this->LocName')",$this->DB);
		        // insert into locstock for all existing items
		        $dummy = DB_Query("SELECT `StockID` FROM `StockMaster`",$this->DB);
		        while($dummyres = DB_fetch_row($dummy)){
				$dummy2 = DB_Query("SELECT * FROM `LocStock` WHERE `LocCode`='$this->LocCode' AND `StockID`='".$dummyres[0]."'",$this->DB);
				if(!DB_num_rows($dummy2)){
					DB_Query("INSERT INTO `LocStock`(`LocCode`,`StockID`) VALUES('$this->LocCode','".$dummyres[0]."')",$this->DB);
				}
		        }
		}


		// ----------- STOCKMASTER -------------------
		$count =count($data);
		for($i=0;$i<$count;$i++){
			$temp = explode(":",$data[$i]['Number']);
			$temparray['StockID'][]= $this->GetID('StockID',$temp[count($temp)-1],'FullName',$data[$i]['Number'],'StockMaster','20',$temparray['StockID']);
			$temparray['FullName'][] =$data[$i]['Number'];
			$extractdata = explode("~%~",$data[$i]['Name']);
			$temparray['EOQ'][]= $extractdata[0];
			$temparray['Description'][]= $extractdata[1];
			$temparray['LongDescription'][]= trim($extractdata[2]);
			$temparray['CategoryID'][]= $data[$i]['Map'];
			$temparray['MBflag'][]= "B";
		}

		$files[0] = $this->UploadDir."/StockMaster.csv";
		CSV_Titles::Write($files[0],$temparray,1);
		//print("haay");
		//print_r($temparray);
		// ----------- LOCSTOCK -------------------

		unset($temparray );
		$count =count($data);
		for($i=0;$i<$count;$i++){
			$temp = explode(":",$data[$i]['Number']);
			$teko = $temp [ count($temp)-1];
			$temparray['StockID'][]= $this->GetID('StockID',$teko,'FullName',$data[$i]['Number'],'StockMaster','20',$temparray['StockID']);
			$extractdata = explode("~%~",$data[$i]['Name']);
			$temparray['Quantity'][]= $extractdata[3];
			$temparray['LocCode'][]= "$this->LocCode";
		}
		$files[1] = $this->UploadDir."/LocStock.csv";
		CSV_Titles::Write($files[1],$temparray,1);
		unset($temparray);
		// ******* Now make archive **********
		@unlink($this->UploadDir."/InventoryItems.zip");


		foreach($files as $tobeadded){
			exec("zip -ujq ".$this->UploadDir."InventoryItems.zip ".$tobeadded);
		}


		// now cleanup what is left of the files

		foreach($files as $tobedeleted){
			unlink($tobedeleted);
		}

		// ***********************************

		// now do the import
		$this->TablesFrom = array("StockMaster"=>array("StockID","FullName","EOQ","Description","LongDescription","CategoryID","MBflag"),"LocStock"=>array("StockID","Quantity","LocCode"));
		$this->CustomType = true;
		if($this->Import("InventoryItems.zip")){
			print("<div align=center><b>Inventory Items [ Buy ]</b> item imported successfully.</div><br><br>");
			//unset($_SESSION['FullName']);
			$this->PrintReports();
		}


	}

	// ####################################
	// Function to import Inventory Items [ Dummy ]
	// ####################################

	function ImportNonInventoryItems(&$data){

		$count =count($data);
		// now first make sure there is a default QB stock location
		$dummy = DB_Query("SELECT `LocCode` FROM `Locations` WHERE `LocCode` = '$this->LocCode' ",$this->DB);
		if(DB_num_rows($dummy) == 0){
		        DB_Query("INSERT INTO `Locations`(`LocCode`,`LocationName`) VALUES('$this->LocCode','$this->LocName')",$this->DB);

		        // insert into locstock for all existing items
		        $dummy = DB_Query("SELECT `StockID` FROM `StockMaster`",$this->DB);
		        while($dummyres = DB_fetch_row($dummy)){
				$dummy2 = DB_Query("SELECT * FROM `LocStock` WHERE `LocCode`='$this->LocCode' AND `StockID`='".$dummyres[0]."'",$this->DB);
				if(!DB_num_rows($dummy2)){
					DB_Query("INSERT INTO `LocStock`(`LocCode`,`StockID`) VALUES('$this->LocCode','".$dummyres[0]."')",$this->DB);
				}
		        }

		}


		// ----------- STOCKMASTER -------------------
		$count =count($data);
		for($i=0;$i<$count;$i++){
			$temp = explode(":",$data[$i]['Number']);
			$temparray['StockID'][]= $this->GetID('StockID',$temp[count($temp)-1],'FullName',$data[$i]['Number'],'StockMaster','20',$temparray['StockID']);
			$extractdata = explode("~%~",$data[$i]['Name']);
			$temparray['FullName'][] = $data[$i]['Number'];
			$temparray['Description'][]= $extractdata[0];
			$temparray['LongDescription'][]= trim($extractdata[1]);
			$temparray['CategoryID'][]= $data[$i]['Map'];
			$temparray['MBflag'][]= "D";
		}

		$files[0] = $this->UploadDir."/StockMaster.csv";
		CSV_Titles::Write($files[0],$temparray,1);
		unset($temparray);
		// ----------- LOCSTOCK -------------------

		$temparray = array();
		$count =count($data);
		for($i=0;$i<$count;$i++){
			$temp = explode(":",$data[$i]['Number']);
			$temparray['StockID'][]= $this->GetID('StockID',$temp[count($temp)-1],'FullName',$data[$i]['Number'],'StockMaster','20',$temparray['StockID']);
			$extractdata = explode("~%~",$data[$i]['Name']);
//			$temparray['FullName'][] = $data[$i]['Number'];
			$temparray['LocCode'][]= "$this->LocCode";
		}
		$files[1] = $this->UploadDir."/LocStock.csv";
		CSV_Titles::Write($files[1],$temparray,1);
		unset($temparray);
		// ******* Now make archive **********
		@unlink($this->UploadDir."/NonInv.zip");


		foreach($files as $tobeadded){
			exec("zip -ujq ".$this->UploadDir."NonInv.zip ".$tobeadded);
		}


		// now cleanup what is left of the files

		foreach($files as $tobedeleted){
			unlink($tobedeleted);
		}

		// ***********************************

		// now do the import
		$this->TablesFrom = array("StockMaster"=>array("StockID","FullName","Description","LongDescription","CategoryID","MBflag"),"LocStock"=>array("StockID","LocCode"));
		$this->CustomType = true;
		if($this->Import("NonInv.zip")){
			print("<div align=center><b>Inventory Items [ Dummy ]</b> item imported successfully.</div><br><br>");
			//unset($_SESSION['FullName']);
			$this->PrintReports();
		}


	}


	// ####################################
	// Function to import Inventory Items [ KIT ]
	// ####################################

	function ImportKits(&$data){

		///global $_SESSION;
		$count =count($data);
		$dummy = DB_Query("SELECT `TaxID` FROM `TaxAuthorities` WHERE `TaxID` ='1'",$this->DB);

		if(DB_num_rows($dummy) ==0){
			$dummy1 = DB_Query("SELECT `AccountCode`,`AccountName` FROM `ChartMaster` WHERE `Group_`=(SELECT `GroupName` FROM `AccountGroups` WHERE `PandL`='0') ORDER BY `AccountCode` LIMIT 0,1",$this->DB);
			$dummyres = DB_fetch_assoc($dummy1);
			DB_Query("INSERT INTO `TaxAuthorities`(`TaxID`,`Description`,`TaxGLCode`,`PurchTaxGLAccount`) values('1','Default Tax Authority','".$dummyres['AccountCode']."','".$dummyres['AccountCode']."')",$this->DB);
		}
		// now first make sure there is a default QB stock location
		$dummy = DB_Query("SELECT `LocCode` FROM `Locations` WHERE `LocCode` = '$this->LocCode' ",$this->DB);
		if(DB_num_rows($dummy) == 0){
		        DB_Query("INSERT INTO `Locations`(`LocCode`,`LocationName`,`TaxAuthority`) VALUES('$this->LocCode','$this->LocName','1')",$this->DB);
		        // insert into locstock for all existing items
		        $dummy = DB_Query("SELECT `StockID` FROM `StockMaster`",$this->DB);
		        while($dummyres = DB_fetch_row($dummy)){
				$dummy2 = DB_Query("SELECT * FROM `LocStock` WHERE `LocCode`='$this->LocCode' AND `StockID`='".$dummyres[0]."'",$this->DB);
				if(!DB_num_rows($dummy2)){
					DB_Query("INSERT INTO `LocStock`(`LocCode`,`StockID`) VALUES('$this->LocCode','".$dummyres[0]."')",$this->DB);
				}
		        }

		}
		// then make sure there is a default QB work centre
		$dummy = DB_Query("SELECT `Code` FROM `WorkCentres` WHERE `Code` = 'QBWOR' ",$this->DB);
		if(DB_num_rows($dummy) == 0){
		        DB_Query("INSERT INTO `WorkCentres`(`Code`,`Location`,`Description`) VALUES('QBWOR','$this->LocCode','QB Work Centre')",$this->DB);
		}

		// ----------- STOCKMASTER -------------------
		$count =count($data);
		for($i=0;$i<$count;$i++){
			$temp = explode(":",$data[$i]['Number']);
			$temparray['StockID'][]= $this->GetID('StockID',$temp[count($temp)-1],'FullName',$data[$i]['Number'],'StockMaster','20',$temparray['StockID']);
			$extractdata = explode("~%~",$data[$i]['Name']);
			$temparray['FullName'][] = $data[$i]['Number'];
			$temparray['Description'][]= $extractdata[0];
			$temparray['LongDescription'][]= trim($extractdata[1]);
			$temparray['CategoryID'][]= $data[$i]['Map'];
			$temparray['MBflag'][]= "K";
		}

		$files[0] = $this->UploadDir."/StockMaster.csv";
		CSV_Titles::Write($files[0],$temparray,1);
		unset($temparray);
		// ----------- LOCSTOCK -------------------

		$temparray = array();
		$count =count($data);
		for($i=0;$i<$count;$i++){
			$temp = explode(":",$data[$i]['Number']);
			$temparray['StockID'][]=$this->GetID('StockID',$temp[count($temp)-1],'FullName',$data[$i]['Number'],'StockMaster','20',$temparray['StockID']);
			$extractdata = explode("~%~",$data[$i]['Name']);

			$temparray['LocCode'][]= "$this->LocCode";
		}
		$files[1] = $this->UploadDir."/LocStock.csv";
		CSV_Titles::Write($files[1],$temparray,1);
		unset($temparray);
		// ----------- BOM -----------------------

		$temparray = array();
		$count =count($data);
		for($i=0;$i<$count;$i++){
			$extractdata = explode("~%~",$data[$i]['Name']);
			$Compo1 = $_SESSION['KitComponents'][$i+1];

			if(is_array($Compo1)){
				foreach($Compo1 as $comp=>$Quantity){

					$temp = explode(":",$data[$i]['Number']);
					$somo = explode(":",$comp);
					$comp1 = $somo[count($somo)-1];
					$temparray['Parent'][]=$this->GetID('StockID',$temp[count($temp)-1],'FullName',$data[$i]['Number'],'StockMaster','20',$temparray['StockID']);

					$temparray['Component'][]= $comp1;
					$temparray['Quantity'][]= $Quantity;
					$temparray['LocCode'][]= "$this->LocCode";
					$temparray['WorkCentreAdded'][] = "QBWORK";
				}
			}
		}
		$files[2] = $this->UploadDir."/BOM.csv";
		CSV_Titles::Write($files[2],$temparray,1);
		unset($temparray);
		// ******* Now make archive **********
		@unlink($this->UploadDir."/GroupInv.zip");


		foreach($files as $tobeadded){
			exec("zip -ujq ".$this->UploadDir."GroupInv.zip ".$tobeadded);
		}

		// now cleanup what is left of the files

		foreach($files as $tobedeleted){
			unlink($tobedeleted);
		}

		// ***********************************

		// now do the import
		$this->TablesFrom = array("StockMaster"=>array("StockID","FullName","Description","LongDescription","CategoryID","MBflag"),
		"LocStock"=>array("StockID","LocCode"),
		"BOM"=>array("Component","LocCode","Parent","WorkCentreAdded","Quantity"));
		$this->CustomType = true;
		if($this->Import("GroupInv.zip")){
			print("<div align=center><b>Inventory Items [ Kit ]</b> item imported successfully.</div><br><br>");
			//unset($_SESSION['FullName']);
			$this->PrintReports();
		}


	}

	// ####################################
	// Function to import Inventory Items [ Manufactue,Assembly ]
	// ####################################

	function ImportAssembly(&$data){

	//	global $_SESSION;
		$count =count($data);
		$dummy = DB_Query("SELECT `TaxID` FROM `TaxAuthorities` WHERE `TaxID` ='1'",$this->DB);

		if(DB_num_rows($dummy) ==0){
			$dummy1 = DB_Query("SELECT `AccountCode`,`AccountName` FROM `ChartMaster` WHERE `Group_`=(SELECT `GroupName` FROM `AccountGroups` WHERE `PandL`='0') ORDER BY `AccountCode` LIMIT 0,1",$this->DB);
			$dummyres = DB_fetch_assoc($dummy1);
			DB_Query("INSERT INTO `TaxAuthorities`(`TaxID`,`Description`,`TaxGLCode`,`PurchTaxGLAccount`) values('1','Default Tax Authority','".$dummyres['AccountCode']."','".$dummyres['AccountCode']."')",$this->DB);
		}
		// now first make sure there is a default QB stock location
		$dummy = DB_Query("SELECT `LocCode` FROM `Locations` WHERE `LocCode` = '$this->LocCode' ",$this->DB);
		if(DB_num_rows($dummy) == 0){
		        DB_Query("INSERT INTO `Locations`(`LocCode`,`LocationName`,`TaxAuthority`) VALUES('$this->LocCode','$this->LocName','1')",$this->DB);

		        // insert into locstock for all existing items
		        $dummy = DB_Query("SELECT `StockID` FROM `StockMaster`",$this->DB);
		        while($dummyres = DB_fetch_row($dummy)){
				$dummy2 = DB_Query("SELECT * FROM `LocStock` WHERE `LocCode`='$this->LocCode' AND `StockID`='".$dummyres[0]."'",$this->DB);
				if(!DB_num_rows($dummy2)){
					DB_Query("INSERT INTO `LocStock`(`LocCode`,`StockID`) VALUES('$this->LocCode','".$dummyres[0]."')",$this->DB);
				}
		        }

		}
		// then make sure there is a default QB work centre
		$dummy = DB_Query("SELECT `Code` FROM `WorkCentres` WHERE `Code` = 'QBWOR' ",$this->DB);
		if(DB_num_rows($dummy) == 0){
		        DB_Query("INSERT INTO `WorkCentres`(`Code`,`Location`,`Description`) VALUES('QBWOR','$this->LocCode','QB Work Centre')",$this->DB);
		}

		// ----------- STOCKMASTER -------------------
		$count =count($data);
		for($i=0;$i<$count;$i++){
                                        $temp = explode(":",$data[$i]['Number']);

                                        $temparray['StockID'][]=$this->GetID('StockID',$temp[count($temp)-1],'FullName',$data[$i]['Number'],'StockMaster','20',$temparray['StockID']);

			$extractdata = explode("~%~",$data[$i]['Name']);
			$temparray['FullName'][] = $data[$i]['Number'];
			$temparray['Description'][]= $extractdata[1];
			$temparray['LongDescription'][]= $extractdata[2];
			$temparray['CategoryID'][]= $data[$i]['Map'];
			if(isset($_SESSION['MassMBflag'][$i]))$temparray['MBflag'][]= $_SESSION['MassMBflag'][$i];
			else $temparray['MBflag'][]= $extractdata[3];
		}

		$files[0] = $this->UploadDir."/StockMaster.csv";
		CSV_Titles::Write($files[0],$temparray,1);
		unset($temparray);
		// ----------- LOCSTOCK -------------------

		$temparray = array();
		$count =count($data);
		for($i=0;$i<$count;$i++){
			$temparray['StockID'][]= $this->GetID('StockID',$data[$i]['Number'],'FullName',$_SESSION['FullName'][$i],'StockMaster','20',$temparray['StockID']);
			$extractdata = explode("~%~",$data[$i]['Name']);
			$temparray['Quantity'][] = $extractdata[0];
			$temparray['LocCode'][]= "$this->LocCode";
		}
		$files[1] = $this->UploadDir."/LocStock.csv";
		CSV_Titles::Write($files[1],$temparray,1);
		unset($temparray);
		// ----------- BOM -----------------------

		$temparray = array();
		$count =count($data);
		for($i=0;$i<$count;$i++){
			$extractdata = explode("~%~",$data[$i]['Name']);
			$Compo1= $_SESSION['MassComponents'][$i+1];

			if(is_array($Compo1)){
				foreach($Compo1 as $comp=>$Quantity){
                                        $temp = explode(":",$data[$i]['Number']);

                                        $temparray['Parent'][]=$this->GetID('StockID',$temp[count($temp)-1],'FullName',$data[$i]['Number'],'StockMaster','20',$temparray['StockID']);

					$temparray['Component'][]= $comp;
					$temparray['Quantity'][]= $Quantity;
					$temparray['LocCode'][]= "$this->LocCode";
					$temparray['WorkCentreAdded'][] = "QBWOR";
				}
			}
		}
		$files[2] = $this->UploadDir."/BOM.csv";
		CSV_Titles::Write($files[2],$temparray,1);
		unset($temparray);
		// ******* Now make archive **********
		@unlink($this->UploadDir."/AssemblyInv.zip");

		foreach($files as $tobeadded){
			exec("zip -ujq ".$this->UploadDir."AssemblyInv.zip ".$tobeadded);
		}


		// now cleanup what is left of the files

		foreach($files as $tobedeleted){
			unlink($tobedeleted);
		}

		// ***********************************

		// now do the import
		$this->TablesFrom = array("StockMaster"=>array("StockID","FullName","Description","LongDescription","CategoryID","MBflag"),
		"LocStock"=>array("StockID","LocCode","Quantity"),
		"BOM"=>array("Component","LocCode","Parent","WorkCentreAdded","Quantity"));
		$this->CustomType = true;
		if($this->Import("AssemblyInv.zip")){
			print("<div align=center><b>Inventory Items [ Make,Assembly ]</b> item imported successfully.</div><br><br>");
			//unset($_SESSION['FullName']);
			$this->PrintReports();
		}


	}


	// ####################################
	// Function to import Customers
	// ####################################

	function ImportCustomers(){

		// *******************************************
		// Getting the maximum QBxxxxxxxx Customer
		$dummy= DB_Query("SELECT `DebtorNo` FROM `DebtorsMaster` WHERE `DebtorNo` like 'QBC%' ORDER By `DebtorNo` DESC LIMIT 0,1",$this->DB);
		if(DB_num_rows($dummy)==0){

			$DebtorNoNum = "0000000";
		}else{
			// increase the latest num by one
$dummy = DB_fetch_row($dummy);
			$DebtorNoNum = intval(substr($dummy[0],3,strlen($dummy[0]))) + 1;
		}

		// *******************************************
		// now make sure the QB sales man is there

		$dummy= DB_Query("SELECT `SalesmanCode` FROM `Salesman` WHERE `SalesmanCode` = 'QBS'",$this->DB);
		if(DB_num_rows($dummy)==0){
			DB_Query(" INSERT INTO `Salesman` ( `SalesmanCode` , `SalesmanName` ) VALUES ('QBS', 'QB Sales Man') ",$this->DB);
		}
		// *******************************************

		// *******************************************
		// now make sure the QB sales area is there

		$dummy= DB_Query("SELECT `AreaCode` FROM `Areas` WHERE `AreaCode` = 'QB'",$this->DB);
		if(DB_num_rows($dummy)==0){
			DB_Query(" INSERT INTO `Areas` ( `AreaCode` , `AreaDescription` ) VALUES ('QB', 'QB Sales Area') ",$this->DB);
		}
		// *******************************************

		// *******************************************
		// now make sure the QB stock location is there

		$dummy = DB_Query("SELECT `LocCode` FROM `Locations` WHERE `LocCode` = '$this->LocCode' ",$this->DB);
		if(DB_num_rows($dummy) == 0){
		        DB_Query("INSERT INTO `Locations`(`LocCode`,`LocationName`) VALUES('$this->LocCode','$this->LocName')",$this->DB);

		        // insert into locstock for all existing items
		        $dummy = DB_Query("SELECT `StockID` FROM `StockMaster`",$this->DB);
		        while($dummyres = DB_fetch_row($dummy)){
				$dummy2 = DB_Query("SELECT * FROM `LocStock` WHERE `LocCode`='$this->LocCode' AND `StockID`='".$dummyres[0]."'",$this->DB);
				if(!DB_num_rows($dummy2)){
					DB_Query("INSERT INTO `LocStock`(`LocCode`,`StockID`) VALUES('$this->LocCode','".$dummyres[0]."')",$this->DB);
				}
		        }

		}
		// *******************************************
/*
		// *******************************************
		// Getting the maximum QBBRxxxxxx Customer Branch
		$dummy= DB_Query("SELECT `BranchCode` FROM `CustBranch` WHERE `BranchCode` = 'QBBR%' ORDER By `BranchCode` DESC LIMIT 0,1",$this->DB);
		if(DB_num_rows($dummy)==0){
			$BranchCodeNum = "000000";
		}else{
			// increase the latest num by one
			$BranchCodeNum = intval(substr($dummy[0],2,strlen($dummy[0]))) + 1;
		}
*/
		// *******************************************
		// now make sure the USD is there

		$dummy= DB_Query("SELECT `CurrAbrev` FROM `Currencies` WHERE `CurrAbrev` = 'USD'",$this->DB);
		if(DB_num_rows($dummy)==0){
			DB_Query(" INSERT INTO `Currencies` ( `Currency` , `CurrAbrev` , `Country` , `HundredsName` , `Rate` ) VALUES ('US Dollars', 'USD', 'United States', 'Cents', '1.0000') ",$this->DB);
		}
		// *******************************************
                // *******************************************
                // now make sure the USD is there

                $dummy= DB_Query("SELECT `TypeAbbrev` FROM `SalesTypes` WHERE `TypeAbbrev` = 'QB'",$this->DB);
                if(DB_num_rows($dummy)==0){
                        DB_Query(" INSERT INTO `SalesTypes` ( `TypeAbbrev` , `Sales_Type`  ) VALUES ('QB', 'QB Sales type') ",$this->DB);
                }
                // *******************************************
		$dummy = DB_Query("SELECT `TermsIndicator` FROM `PaymentTerms` WHERE `TermsIndicator`='QB'",$this->DB);
		if(DB_num_rows($dummy)==0){
			DB_Query("INSERT INTO `PaymentTerms`(`TermsIndicator`,`Terms`) VALUES('QB','QB Default Terms')",$this->DB);
		}

		$dummy = DB_Query("SELECT `ReasonCode` FROM `HoldReasons` WHERE `ReasonCode`='1'",$this->DB);
		if(DB_num_rows($dummy)==0){
			DB_Query("INSERT INTO `HoldReasons`(`ReasonCode`,`ReasonDescription`,`DissallowInvoices`) VALUES('1','QB Hold reason','0')",$this->DB);
		}

		// Now extract all required data from the XML that will be used later


		if(!is_array($this->DataArray['CustomerQueryRs'][0]['#']['CustomerRet'])){
			// invalid file
			$this->ErrorReport("Please upload a valid Customers QBXML file.");
			return false;
		}

		foreach($this->DataArray['CustomerQueryRs'][0]['#']['CustomerRet'] as $entry){
			// make sure we add only customers and not there jobs
			if(!strchr($entry['#']['FullName'][0]['#'],":")){
				$DebtorNoNum++;
				// make sure length is 8 chars
				while(strlen($DebtorNoNum) <7){
					$DebtorNoNum = "0".$DebtorNoNum;
				}
				$alldata['DebtorNo'][] = "QBC".$DebtorNoNum;

				$alldata['BranchCode'][] = "QBC".$DebtorNoNum;


				$alldata['Name'][] = $this->AdjustEntry($entry['#']['FullName'][0]['#']);
				$alldata['CustomerEmail'][] = $entry['#']['Email'][0]['#'];
				$alldata['CreditLimit'][] = $entry['#']['CreditLimit'][0]['#'];
				$alldata['Address1'][] = $this->AdjustEntry($entry['#']['BillAddress'][0]['#']['Addr1'][0]['#']);
				$alldata['Address2'][] = $this->AdjustEntry($entry['#']['BillAddress'][0]['#']['Addr2'][0]['#']);
				if($entry['#']['BillAddress'][0]['#']['City'][0]['#'] && $entry['#']['BillAddress'][0]['#']['State'][0]['#']) $alldata['Address3'][] = $this->AdjustEntry($entry['#']['BillAddress'][0]['#']['City'][0]['#'].",".$entry['#']['BillAddress'][0]['#']['State'][0]['#']);
				else  $alldata['Address3'][] = $this->AdjustEntry($entry['#']['BillAddress'][0]['#']['City'][0]['#'].$entry['#']['BillAddress'][0]['#']['State'][0]['#']);
				$alldata['Address4'][] = $this->AdjustEntry($entry['#']['BillAddress'][0]['#']['PostalCode'][0]['#']);
				$alldata['ContactName'][] = $this->AdjustEntry($entry['#']['Contact'][0]['#']);
				$alldata['PhoneNo'][] = $entry['#']['Phone'][0]['#'];
				$alldata['Fax'][] = $entry['#']['Fax'][0]['#'];
				$alldata['ClientSince'][] = substr($entry['#']['TimeCreated'][0]['#'],0,10);
				$alldata['CurrCode'][] = "USD";
				$alldata['Salesman'][]="QBS";
				$alldata['Area'][]='QB';
				$alldata['HoldReason'][] = 1;
				$alldata['DefaultShipVia'][] = 1;
//				$alldata['SalesType'][] = 'QB';
			}
		}
		// ok, now we will rearrange the $alldata array by the 'name' field, because that
		// is the unique ID in QB
		// -------------------------------------------------
		$this->TotalTrials = count($alldata['Area']);

		$newalldata['Name'] = $alldata['Name'];
		sort($newalldata['Name']);
		// now the data is sorted by name
		for($i=0;$i<$this->TotalTrials;$i++){
		        // now look in old arrays for the corresponding values where Name is the same
			$keys = array_keys($alldata);
			foreach($keys as $field){
			        if($field != 'Name') $newalldata[$field][$i] = $alldata[$field][array_search($newalldata['Name'][$i],$alldata['Name'])];
			}

		}
		// return sorted data to array
		$alldata = $newalldata;
		unset($newalldata);
		// -------------------------------------------------
		// ok carry on

		// ----------- DEBTORSMASTER -------------------
		$temparray['DebtorNo'] = $alldata['DebtorNo'];
		$temparray['Name']=$alldata['Name'];
		$temparray['CustomerEmail']=$alldata['CustomerEmail'];
		$temparray['CreditLimit']=$alldata['CreditLimit'];
		$temparray['ClientSince']=$alldata['ClientSince'];
		$temparray['Address1']=($alldata['Address1']);
		$temparray['Address2']=($alldata['Address2']);
		$temparray['Address3']=($alldata['Address3']);
		$temparray['Address4']=($alldata['Address4']);
		$temparray['CurrCode']=$alldata['CurrCode'];
		$temparray['SalesType']=$alldata['Area'];
		$temparray['PaymentTerms']=$alldata['Area'];
		$temparray['HoldReason'] = $alldata['HoldReason'];
		$this->TotalRepEntries=0;
		$this->TotalRepEntries=0;


		for($i=0;$i<$this->TotalTrials;$i++){

			// check if there
			$testifthere = DB_Query("SELECT `DebtorNo` FROM `DebtorsMaster` WHERE
			LOWER(`Name`)='".strtolower(addslashes($temparray['Name'][$i]))."' AND
			`DebtorNo` like 'QBC%'
			",$this->DB);

			$existing = DB_num_rows($testifthere);

			if($this->Overwrite && $existing){
			        $this->TotalRepEntries++;
			        $this->TotalEntries++;
			        $debtornores = DB_fetch_assoc($testifthere);
			        $debtorno = $debtornores['DebtorNo'];

			        $sql = "UPDATE `DebtorsMaster` SET";
			        foreach($temparray as $key=>$val){
			                if($key !='DebtorNo')$sql.="`".$key."` = '".addslashes($temparray[$key][$i])."' ,";
			        }
			        $sql = substr($sql,0,(strlen($sql)-1));
			        $sql.= " WHERE `DebtorNo`='".$debtorno."'";

			        DB_Query($sql,$this->DB);

			}elseif(!$existing){
			        $this->TotalEntries++;
			        $sql = "INSERT INTO `DebtorsMaster`(";
			        $fields = array_keys($temparray);
			        foreach($fields as $key){
			                $sql.="`".$key."`,";
			        }
			        $sql = substr($sql,0,(strlen($sql)-1));
			        $sql.=") VALUES(";
			        foreach($temparray as $key=>$value){
			                $sql.="'".addslashes($value[$i])."',";
			        }
			        $sql = substr($sql,0,(strlen($sql)-1));
			        $sql .= ")";
				DB_Query($sql,$this->DB);

			}elseif(!$this->Overwrite && $existing){
			        $this->TotalRepEntries++;

			}

		}
		$this->AddReport("DebtorsMaster");
		unset($temparray);
/*
		$files[0] = $this->UploadDir."/DebtorsMaster.csv";
		CSV_Titles::Write($files[0],$temparray,1);
*/
		// ----------- CUSTBRANCH -------------------

		$temparray = array();
		$temparray['DebtorNo'] = $alldata['DebtorNo'];
		$temparray['BranchCode'] = $alldata['DebtorNo'];
		$temparray['BrName'] = $alldata['Name'];
		$temparray['PhoneNo'] = $alldata['PhoneNo'];
		$temparray['FaxNo'] = $alldata['Fax'];
		$temparray['Email'] = $alldata['CustomerEmail'];
		$temparray['ContactName'] = $alldata['ContactName'];
		$temparray['BrAddress1']=($alldata['Address1']);

		$temparray['BrAddress2']=($alldata['Address2']);
		$temparray['BrAddress3']=($alldata['Address3']);
		$temparray['BrAddress4']=($alldata['Address4']);
		$temparray['Salesman']=$alldata['Salesman'];
		$temparray['Area']=$alldata['Salesman'];
		$temparray['DefaultShipVia'] = $alldata['DefaultShipVia'];

		$this->TotalEntries=0;
		$this->TotalRepEntries=0;


		for($i=0;$i<$this->TotalTrials;$i++){

			// check if there
			$testifthere = DB_Query("SELECT `DebtorNo` FROM `CustBranch` WHERE
			`DebtorNo` like '".$temparray['DebtorNo'][$i]."' AND
			`BranchCode` like '".$temparray['DebtorNo'][$i]."'
			",$this->DB);

			$existing = DB_num_rows($testifthere);

			if($this->Overwrite && $existing){
			        $this->TotalRepEntries++;
			        $this->TotalEntries++;
			        $debtornores = DB_fetch_assoc($testifthere);
			        $debtorno = $debtornores['DebtorNo'];

			        $sql = "UPDATE `CustBranch` SET";
			        foreach($temparray as $key=>$val){
			                if($key !='DebtorNo')$sql.="`".$key."` = '".addslashes($temparray[$key][$i])."' ,";
			        }
			        $sql .= " `DefaultLocation`='$this->LocCode' ";
			        $sql.= " WHERE `DebtorNo`='".$debtorno."'";

			        DB_Query($sql,$this->DB);

			}elseif(!$existing){
			        $this->TotalEntries++;
			        $sql = "INSERT INTO `CustBranch`(";
			        $fields = array_keys($temparray);
			        foreach($fields as $key){
			                $sql.="`".$key."`,";
			        }
			        $sql .="`DefaultLocation`";
			        $sql.=") VALUES(";
			        foreach($temparray as $key=>$value){
			                $sql.="'".addslashes($value[$i])."',";
			        }
			        $sql .= "'$this->LocCode'";
			        $sql .= ")";
			        DB_Query($sql,$this->DB);

			}elseif(!$this->Overwrite && $existing){
			        $this->TotalRepEntries++;

			}

		}
		$this->AddReport("CustBranch");
		print("<div align=center><b>Customers</b> item imported successfully.</div><br><br>");
		$this->PrintReports();
		unset($temparray);
/*
		$files[1] = $this->UploadDir."/CustBranch.csv";
		CSV_Titles::Write($files[1],$temparray,1);

		// ******* Now make archive **********
		@unlink($this->UploadDir."/Customers.zip");


		foreach($files as $tobeadded){
			exec("zip -ujq ".$this->UploadDir."Customers.zip ".$tobeadded);
		}

		// now cleanup what is left of the files

		foreach($files as $tobedeleted){
			unlink($tobedeleted);
		}

		// ***********************************

		// now do the import
		$this->TablesFrom = array(
		"DebtorsMaster"=>array('DebtorNo','Name','CustomerEmail','CreditLimit','ClientSince','Address1','Address2','Address3','Address4','CurrCode'),

		"CustBranch"=>array('DebtorNo','BranchCode','BrName','PhoneNo','FaxNo','Email','ContactName','BrAddress1','BrAddress2','BrAddress3','BrAddress4','Salesman','Area')

		);
		$this->CustomType = true;
		if($this->Import("Customers.zip")){
			print("<div align=center><b>Customers</b> item imported successfully.</div><br><br>");
			$this->PrintReports();
		}
*/

	}

	// ####################################
	// Function to import Vendors
	// ####################################

	function ImportVendors(){

		// *******************************************
		// Getting the maximum QBxxxxxxxx Supplier
		$dummy= DB_Query("SELECT `SupplierID` FROM `Suppliers` WHERE `SupplierID` like 'QBV%' ORDER By `SupplierID` DESC LIMIT 0,1",$this->DB);
		if(DB_num_rows($dummy)==0){
			$SupplierIDNum = "0000000";
		}else{
			// incres the latest num by one
$dummy = DB_fetch_row($dummy);

			$SupplierIDNum = intval(substr($dummy[0],3,strlen($dummy[0]))) + 1;
		}


		// *******************************************
		// now make sure the USD is there

		$dummy= DB_Query("SELECT `CurrAbrev` FROM `Currencies` WHERE `CurrAbrev` = 'USD'",$this->DB);
		if(DB_num_rows($dummy)==0){
			DB_Query(" INSERT INTO `Currencies` ( `Currency` , `CurrAbrev` , `Country` , `HundredsName` , `Rate` ) VALUES ('US Dollars', 'USD', 'United States', 'Cents', '1.0000') ",$this->DB);
		}

		// default payment terms
                $dummy = DB_Query("SELECT `TermsIndicator` FROM `PaymentTerms` WHERE `TermsIndicator`='QB'",$this->DB);
                if(DB_num_rows($dummy)==0){
                        DB_Query("INSERT INTO `PaymentTerms`(`TermsIndicator`,`Terms`) VALUES('QB','QB Default Terms')",$this->DB);
                }

		// *******************************************
		// Now extract all required data from the XML that will be used later


		if(!is_array($this->DataArray['VendorQueryRs'][0]['#']['VendorRet'])){
			// invalid file
			$this->ErrorReport("Please upload a valid Vendors QBXML file.");
			return false;
		}

		foreach($this->DataArray['VendorQueryRs'][0]['#']['VendorRet'] as $entry){
			// make sure we add only customers and not there jobs
			if(!strchr($entry['#']['Name'][0]['#'],":")){
				$SupplierIDNum++;
				// make sure length is 8 chars
				while(strlen($SupplierIDNum) <7){
					$SupplierIDNum = "0".$SupplierIDNum;
				}
				$alldata['SupplierID'][] = "QBV".$SupplierIDNum;



				$alldata['SuppName'][] = $this->AdjustEntry($entry['#']['Name'][0]['#']);
				$alldata['SupplierEmail'][] = $entry['#']['Email'][0]['#'];

				$alldata['Address1'][] = $this->AdjustEntry($entry['#']['VendorAddress'][0]['#']['Addr1'][0]['#']);
				$alldata['Address2'][] = $this->AdjustEntry($entry['#']['VendorAddress'][0]['#']['Addr2'][0]['#']);
				if($entry['#']['VendorAddress'][0]['#']['City'][0]['#'] && $entry['#']['VendorAddress'][0]['#']['State'][0]['#']) $alldata['Address3'][] = $this->AdjustEntry($entry['#']['VendorAddress'][0]['#']['City'][0]['#'].",".$entry['#']['VendorAddress'][0]['#']['State'][0]['#']);
				else  $alldata['Address3'][] = $this->AdjustEntry($entry['#']['VendorAddress'][0]['#']['City'][0]['#'].$entry['#']['VendorAddress'][0]['#']['State'][0]['#']);
				$alldata['Address4'][] = $this->AdjustEntry($entry['#']['VendorAddress'][0]['#']['PostalCode'][0]['#']);
				$alldata['Contact'][] = $this->AdjustEntry($entry['#']['Contact'][0]['#']);
				$alldata['Tel'][] = $entry['#']['Phone'][0]['#'];
				$alldata['Fax'][] = $entry['#']['Fax'][0]['#'];
				$alldata['SupplierSince'][] = substr($entry['#']['TimeCreated'][0]['#'],0,10);
				$alldata['CurrCode'][] = "USD";
			}
		}

		// ok, now we will rearrange the $alldata array by the 'name' field, because that
		// is the unique ID in QB
		// -------------------------------------------------
		$this->TotalTrials = count($alldata['SuppName']);

		$newalldata['SuppName'] = $alldata['SuppName'];
		sort($newalldata['SuppName']);
		// now the data is sorted by name
		for($i=0;$i<$this->TotalTrials;$i++){
		        // now look in old arrays for the corresponding values where Name is the same
			$keys = array_keys($alldata);
			foreach($keys as $field){
			        if($field != 'SuppName') $newalldata[$field][$i] = $alldata[$field][array_search($newalldata['SuppName'][$i],$alldata['SuppName'])];
			}

		}
		// return sorted data to array
		$alldata = $newalldata;
		unset($newalldata);
		// -------------------------------------------------
		// ok carry on

		// ----------- SUPPLIERS-------------------
		$temparray['SupplierID'] = $alldata['SupplierID'];
		$temparray['SuppName']=$alldata['SuppName'];
		$temparray['SupplierEmail']=$alldata['SupplierEmail'];

		$temparray['SupplierSince']=$alldata['SupplierSince'];
		$temparray['Address1']=$alldata['Address1'];
		$temparray['Address2']=$alldata['Address2'];
		$temparray['Address3']=$alldata['Address3'];
		$temparray['Address4']=$alldata['Address4'];
		$temparray['CurrCode']=$alldata['CurrCode'];
/*
		$files[0] = $this->UploadDir."/Suppliers.csv";
		CSV_Titles::Write($files[0],$temparray,1);
*/
		$this->TotalRepEntries=0;
		$this->TotalRepEntries=0;


		for($i=0;$i<$this->TotalTrials;$i++){

			// check if there
			$testifthere = DB_Query("SELECT `SupplierID` FROM `Suppliers` WHERE
			LOWER(`SuppName`)='".strtolower(addslashes($temparray['SuppName'][$i]))."' AND
			`SupplierID` like 'QBV%'
			",$this->DB);

			$existing = DB_num_rows($testifthere);

			if($this->Overwrite && $existing){
			        $this->TotalRepEntries++;
			        $this->TotalEntries++;
			        $debtornores = DB_fetch_assoc($testifthere);
			        $debtorno = $debtornores['SupplierID'];

			        $sql = "UPDATE `Suppliers` SET";
			        foreach($temparray as $key=>$val){
			                if($key !='SupplierID')$sql.="`".$key."` = '".addslashes($temparray[$key][$i])."' ,";
			        }
			        $sql = substr($sql,0,(strlen($sql)-1));
			        $sql.= " WHERE `SupplierID`='".$debtorno."'";

			        DB_Query($sql,$this->DB);

			}elseif(!$existing){
			        $this->TotalEntries++;
			        $sql = "INSERT INTO `Suppliers`(";
			        $fields = array_keys($temparray);
			        foreach($fields as $key){
			                $sql.="`".$key."`,";
			        }
			        $sql .= "`PaymentTerms`";
			        $sql.=") VALUES(";
			        foreach($temparray as $key=>$value){
			                $sql.="'".addslashes($value[$i])."',";
			        }
//	        $sql = substr($sql,0,(strlen($sql)-1));
			        $sql .= "'QB')";
				DB_Query($sql,$this->DB);

			}elseif(!$this->Overwrite && $existing){
			        $this->TotalRepEntries++;

			}

		}
		$this->AddReport("Suppliers");
		unset($temparray);
		// ----------- SUPLPLIERCONTACTS -------------------

		$temparray = array();
		$temparray['SupplierID'] = $alldata['SupplierID'];
		$temparray['Contact'] = $alldata['Contact'];
		$temparray['Tel'] = $alldata['Tel'];
		$temparray['Fax'] = $alldata['Fax'];
		$temparray['Email'] = $alldata['SupplierEmail'];
			// check if there
		$this->TotalEntries=0;
		$this->TotalRepEntries=0;

		for($i=0;$i<$this->TotalTrials;$i++){


			$existing = DB_num_rows($testifthere);

			if($this->Overwrite && $existing){
			        $this->TotalRepEntries++;
			        $this->TotalEntries++;

			        $sql = "UPDATE `SupplierContacts` SET";
			        foreach($temparray as $key=>$val){
			                if($key !='SupplierID')$sql.="`".$key."` = '".addslashes($temparray[$key][$i])."' ,";
			        }
			        $sql = substr($sql,0,(strlen($sql)-1));
			        $sql.= " WHERE `SupplierID`='".$debtorno."'";

			        DB_Query($sql,$this->DB);

			}elseif(!$existing){
			        $this->TotalEntries++;
			        $sql = "INSERT INTO `SupplierContacts`(";
			        $fields = array_keys($temparray);
			        foreach($fields as $key){
			                $sql.="`".$key."`,";
			        }
			        $sql = substr($sql,0,(strlen($sql)-1));
			        $sql.=") VALUES(";
			        foreach($temparray as $key=>$value){
			                $sql.="'".addslashes($value[$i])."',";
			        }
			        $sql = substr($sql,0,(strlen($sql)-1));
			        $sql .= ")";
			        DB_Query($sql,$this->DB);

			}elseif(!$this->Overwrite && $existing){
			        $this->TotalRepEntries++;

			}

		}
		$this->AddReport("SupplierContacts");
		print("<div align=center><b>Vendors</b> item imported successfully.</div><br><br>");
		$this->PrintReports();

/*
		$files[1] = $this->UploadDir."/SupplierContacts.csv";
		CSV_Titles::Write($files[1],$temparray,1);
*/
		// ******* Now make archive **********
/*
		@unlink($this->UploadDir."/Vendors.zip");

		foreach($files as $tobeadded){
			exec("zip -ujq ".$this->UploadDir."Vendors.zip ".$tobeadded);
		}

		// now cleanup what is left of the files

		foreach($files as $tobedeleted){
			unlink($tobedeleted);
		}

		// ***********************************

		// now do the import
		$this->TablesFrom = array(
		"Suppliers"=>array('SupplierID','SuppName','SupplierEmail','SupplierSince','Address1','Address2','Address3','Address4','CurrCode'),
		"SupplierContacts"=>array('SupplierID','Contact','Tel','Fax','Email')
		);
		$this->CustomType = true;
		if($this->Import("Vendors.zip")){
			print("<div align=center><b>Vendors</b> item imported successfully.</div><br><br>");
			$this->PrintReports();
		}
*/

	}

	// ####################################
	// Function to import Journal entries
	// ####################################

	function ImportJournalEntries(){

		// A varriable to manually adjust the report
		$FixRep = 0;
		// *******************************************
		// Now extract all required data from the XML that will be used later


		if(!is_array($this->DataArray['JournalEntryQueryRs'][0]['#']['JournalEntryRet'])){
			// invalid file
			$this->ErrorReport("Please upload a valid Journal Entries QBXML file.");
			return false;
		}
          //              $Topdummy = DB_Query("SELECT `QBTxnNumber` FROM `GLTrans` WHERE `QBTxnNumber` = '".$entry['#']['TxnID'][0]['#']."'",$this->DB);
                        $TransNoQuery = DB_Query("SELECT MAX(`TypeNo`) FROM `SysTypes` WHERE `TypeID` ='0'",$this->DB);
                        $transNores = DB_fetch_row($TransNoQuery);
                        $TransNo = $transNores[0];

		foreach($this->DataArray['JournalEntryQueryRs'][0]['#']['JournalEntryRet'] as $entry){
			// To start with we need to make sure that the TXN number is not duplicate
			$Topdummy = DB_Query("SELECT `QBTxnNumber` FROM `GLTrans` WHERE `QBTxnNumber` = '".$entry['#']['TxnID'][0]['#']."'",$this->DB);
	/*		$TransNoQuery = DB_Query("SELECT MAX(`TypeNo`) FROM `SysTypes` WHERE `TypeID` ='0'",$this->DB);
			$transNores = DB_fetch_row($TransNoQuery);
			$TransNo = $transNores[0];	*/
				// first handle the DEBIT lines
			if(is_array($entry['#']['JournalDebitLine'])){

				foreach($entry['#']['JournalDebitLine'] as $debit){
					// get the Account [ make sure is there first ]
					$dummy = explode(":",$debit['#']['AccountRef'][0]['#']['FullName'][0]['#']);
					$AccountName = $this->GetID('AccountName',
					$this->AdjustEntry($dummy[(count($dummy)-1)]),
					'FullName',
					$debit['#']['AccountRef'][0]['#']['FullName'][0]['#'],
					'ChartMaster',
					50);
					$dummy = DB_Query("SELECT `AccountCode` FROM `ChartMaster` WHERE `AccountName`='".$AccountName."'",$this->DB);
					$dummyres = DB_fetch_row($dummy);
					//print("AccountCode: ".$dummyres[0]." <br>");
					$continue =1;
					if(DB_num_rows($dummy) != 0){
						if(DB_num_rows($Topdummy)!=0){
							$FixRep ++;
							$continue = 0;
							if($this->Overwrite){
								DB_Query("DELETE FROM `GLTrans` WHERE `QBTxnNumber`='".$entry['#']['TxnID'][0]['#']."' ",$this->DB);
								$continue =1;
							}
						}
						if($continue){
							$alldata['Account'][] = intval($dummyres[0]);
							$alldata['Amount'][] =  floatval($debit['#']['Amount'][0]['#']);
							$alldata['Narrative'][] = $this->AdjustEntry($debit['#']['Memo'][0]['#']);
			                                $tempdate1 = strtotime(substr($entry['#']['TxnDate'][0]['#'],0,10));
                        			        $tempdate= date($this->DefDateFormat , $tempdate1);

							$alldata['PeriodNo'][] = GetPeriod($tempdate,$this->DB);
							$alldata['TranDate'][] = $entry['#']['TxnDate'][0]['#'];
							$alldata['QBTxnNumber'][] = $entry['#']['TxnID'][0]['#'];
							$alldata['TypeNo'][] = $TransNo;
							$alldata['Posted'][] = 1;

						}
					}else{
						if($AccountName != ''){
							$this->ErrorReport("Account '".$AccountName."' doesn't exist. Please import the chart of accounts before trying to import the journal entries.");
							return false;
						}
					}
				}
			}
			// Secondly Handle the Credit Lines
			if(is_array($entry['#']['JournalCreditLine'])){
				foreach($entry['#']['JournalCreditLine'] as $credit){
					// get the Account [ make sure is there first ]
					$dummy = explode(":",$credit['#']['AccountRef'][0]['#']['FullName'][0]['#']);
					$AccountName = $this->GetID('AccountName',
					$this->AdjustEntry($dummy[(count($dummy)-1)]),
					'FullName',
					$credit['#']['AccountRef'][0]['#']['FullName'][0]['#'],
					'ChartMaster',
					50
					);

					$dummy = DB_Query("SELECT `AccountCode` FROM `ChartMaster` WHERE `AccountName`='".$AccountName."'",$this->DB);
					$dummyres = DB_fetch_row($dummy);
					$continue =1;
					if(DB_num_rows($dummy) != 0){
						if(DB_num_rows($Topdummy)!=0 ){
							$FixRep ++;
							$continue = 0;
							if($this->Overwrite){
								DB_Query("DELETE FROM `GLTrans` WHERE `QBTxnNumber`='".$entry['#']['TxnID'][0]['#']."' ",$this->DB);
								$continue =1;
							}
						}
						if($continue){
							$alldata['Account'][] = $dummyres[0];
							$alldata['Amount'][] = -1*floatval($credit['#']['Amount'][0]['#']);
							$alldata['Narrative'][] = $credit['#']['Memo'][0]['#'];
				                        $tempdate1 = strtotime(substr($entry['#']['TxnDate'][0]['#'],0,10));
                                                        $tempdate= date($this->DefDateFormat , $tempdate1);

                                                        $alldata['PeriodNo'][] = GetPeriod($tempdate,$this->DB);

							$alldata['TranDate'][] =$entry['#']['TxnDate'][0]['#']; 
							$alldata['QBTxnNumber'][] = $entry['#']['TxnID'][0]['#'];
							$alldata['TypeNo'][] = $TransNo;
							$alldata['Posted'][] = 1;

						}

					}else{
						$this->ErrorReport("Account '".$AccountName."' doesn't exist. Please import the chart of accounts before trying to import the journal entries.");
						return false;
					}
				}
			}
//	print "tra no".$TransNo."<br>";
			$TransNo++;
		}

		// ----------- GLTRANS -------------------
		$temparray['Amount'] = $alldata['Amount'];
		$temparray['Account'] = $alldata['Account'];
		$temparray['Narrative'] = $alldata['Narrative'];
		$temparray['TranDate'] = $alldata['TranDate'];
		$temparray['PeriodNo'] = $alldata['PeriodNo'];
		$temparray['QBTxnNumber'] = $alldata['QBTxnNumber'];
		$temparray['TypeNo'] = $alldata['TypeNo'];
		$temparray['Posted'] = $alldata['Posted'];
		$files[0] = $this->UploadDir."/GLTrans.csv";
		CSV_Titles::Write($files[0],$temparray,1);
		unset($temparray);
		// ******* Now make archive **********
		@unlink($this->UploadDir."/JournalEntries.zip");

		foreach($files as $tobeadded){
			exec("zip -ujq ".$this->UploadDir."JournalEntries.zip ".$tobeadded);
		}

		// now cleanup what is left of the files

		foreach($files as $tobedeleted){
			unlink($tobedeleted);
		}

		// ***********************************

		// now do the import
		$this->TablesFrom = array(
		"GLTrans"=>array('Amount','Account','PeriodNo','Narrative','TranDate','QBTxnNumber','TypeNo','Posted')

		);
		$this->CustomType = true;
		if($this->Import("JournalEntries.zip")){
			print("<div align=center><b>Journal Entries</b> item imported successfully.</div><br><br>");
			// initialize the dat before printing the reports
			$this->TotalRepEntries += $FixRep;
			if(!$this->Overwrite)$this->TotalTrials += $FixRep;
			$this->Reports = array();
			$this->AddReport("GLTrans");
			$this->PrintReports();
			DB_Query("UPDATE `SysTypes` set `TypeNo`=".$TransNo."	WHERE `TypeID`=0",$this->DB);

		}


	}



	// ####################################
	// Function to import Sales Orders
	// ####################################

	function ImportSalesOrders(){


		// *******************************************
		// Now extract all required data from the XML that will be used later

		if(!is_array($this->DataArray['SalesOrderQueryRs'][0]['#']['SalesOrderRet'])){
			// invalid file
			$this->ErrorReport("Please upload a valid Sales Order QBXML file.");
			return false;
		}

		foreach($this->DataArray['SalesOrderQueryRs'][0]['#']['SalesOrderRet'] as $entry){

			$SalesTaxPercentage = $entry['#']['SalesTaxPercentage'][0]['#'];

			// To start with we need to make sure that the TXN number is not duplicate
			$Topdummy = DB_Query("SELECT `QBTxnID`,`OrderNo` FROM `SalesOrders` WHERE `QBTxnID` = '".$entry['#']['TxnID'][0]['#']."'",$this->DB);
//			print("SELECT `QBTxnID`,`OrderNo` FROM `SalesOrders` WHERE `QBTxnID` = '".$entry['#']['TxnID'][0]['#']."'<br>");
			$TopdummyRes = DB_fetch_row($Topdummy);
			$OrderNo = $TopdummyRes[1];
			//print('Num Result: '.DB_num_rows($Topdummy).',Order No is :'.$OrderNo.', TxnID: '.$entry['#']['TxnID'][0]['#'].'<br />');
				// first handle the DEBIT lines
			WamaMerge($entry['#']['SalesOrderLineRet'],$entry['#']['SalesOrderLineGroupRet'],array('#','SalesOrderLineRet'));
			if(is_array($entry['#']['SalesOrderLineRet'])){

				foreach($entry['#']['SalesOrderLineRet'] as $item){
					$continue =1;

//					if(DB_num_rows($dummy) != 0){
						if(DB_num_rows($Topdummy)!=0){

							$continue = 1;
							if($this->Overwrite){
								// how to delete here
								//DB_Query("DELETE FROM `SalesOrders` WHERE `QBTxnID`='".$entry['#']['TxnID'][0]['#']."'",$this->DB);
								//DB_Query("DELETE FROM `SalesOrderDetails` WHERE `OrderNo`='".$OrderNo."'",$this->DB);
								$continue =1;
							}
						}
						if($continue){

						        // get the debtor no
						        $dummyname = explode(":",$this->AdjustEntry($entry['#']['CustomerRef'][0]['#']['FullName'][0]['#']));
						        $FullName = $this->AdjustEntry($dummyname[count($dummyname)-1]);
							$NameToQuery = substr($FullName,0,40);

							// -- now get the debtor no from the debtor no table
							$dummy = DB_Query("SELECT `DebtorNo`,`SalesType` FROM `DebtorsMaster` WHERE `Name` = '".$NameToQuery."' ",$this->DB);
							$dummyres = DB_fetch_assoc($dummy);
							// Check if there is no such customer
							if(DB_num_rows($dummy) ==0){
							        $this->ErrorReport("Customer '".$NameToQuery."' doesn't exist. Please import customers first.");
							        return false;
							}
							$DebtorNo = $dummyres['DebtorNo'];
							$SalesType = $dummyres['SalesType'];

							// -- now obtain the first branch code ordered alphabitically
       							$dummy = DB_Query("SELECT * FROM `CustBranch` WHERE `DebtorNo` = '".$dummyres['DebtorNo']."' ORDER BY `BranchCode` ASC ",$this->DB);
							$dummyres = DB_fetch_assoc($dummy);
							if(DB_num_rows($dummy)==0){
							      $this->ErrorReport('Please Make sure you have set customer branches.');
							        return false;
							}
	   						$BranchCode = $dummyres['BranchCode'];
						        $BuyerName =$dummyres['BrName'];
							$ContactPhone =$dummyres['PhoneNo'];
       						       $ContactEmail =$dummyres['Email'];
						       $Shipper = $dummyres['DefaultShipVia'];
       						       // ok all clear for now , go on
							if(1){


								$alldata['DebtorNo'][] = $DebtorNo;
								$alldata['OrderType'][] = $SalesType;
								$alldatat['SalesTaxPercentage'][] = $SalesTaxPercentage;
							        $alldata['BranchCode'][] = $BranchCode;
							        $alldata['BuyerName'][] =$BuyerName;
							        $alldata['ContactPhone'][] =$ContactPhone;
							        $alldata['ContactEmail'][] =$ContactEmail;
							        $alldata['DeliveryDate'][] =$entry['#']['DueDate'][0]['#'];

							        $alldata['OrdDate'][] = $entry['#']['TxnDate'][0]['#'];
							        $alldata['DelAdd1'][] = $this->AdjustEntry($entry['#']['BillAddress'][0]['#']['Addr1'][0]['#']);
							        $alldata['DelAdd2'][] = $this->AdjustEntry($entry['#']['BillAddress'][0]['#']['Addr2'][0]['#']);
							        // Adjust the address three format
							        if(isset($entry['#']['BillAddress'][0]['#']['City'][0]['#'],$entry['#']['BillAddress'][0]['#']['State'][0]['#'])){
									$alldata['DelAdd3'][] = $this->AdjustEntry($entry['#']['BillAddress'][0]['#']['City'][0]['#'] . "," . $entry['#']['BillAddress'][0]['#']['State'][0]['#']);
								}
							       else $alldata['DelAdd3'][] = $this->AdjustEntry($entry['#']['BillAddress'][0]['#']['City'][0]['#'].$entry['#']['BillAddress'][0]['#']['State'][0]['#']);
							        $alldata['DelAdd4'][] = $this->AdjustEntry($entry['#']['BillAddress'][0]['#']['PostalCode'][0]['#']);
							        // Get the stock location and check it has enough quantity
							        // &&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&
							        // first get the item full name
							        $dummy = explode(":",$this->AdjustEntry($item['#']['ItemRef'][0]['#']['FullName'][0]['#'],1));
							        if($dummy =='') $dummy = explode(":",$this->AdjustEntry($item['#']['ItemGroupRef'][0]['#']['FullName'][0]['#'],1));
							        $itemName = $this->GetID('StockID',
								$this->AdjustEntry($dummy[count($dummy)-1],1),
								'FullName',
								implode(":",$dummy),
								'StockMaster',
								20

								);


							        // now check it exists and in enough quantity in at least one stock location
							        $dummy=DB_Query("SELECT `LocCode` FROM `LocStock` WHERE ( `StockID`='".substr($itemName,0,20)."')",$this->DB);
							        // &&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&
								if(DB_num_rows($dummy)==0){
								        DB_Query("INSERT INTO `LocStock`(`LocCode`,`StockID`) VALUES('QBSTK','".substr($itemName,0,20)."')",$this->DB);
								}
								$alldata['StkCode'][] = $itemName;
								$dummyres = DB_fetch_assoc($dummy);
								$alldata['FromStkLoc'][] = ($dummyres['LocCode'])?$dummyres['LocCode']:'QBSTK';
								if($item['#']['Invoiced'][0]['#'] == $item['#']['Quantity'][0]['#'] || $item['#']['IsManuallyClosed'][0]['#'] == 'true'){
									$alldata['Completed'][] = 1;
									$alldata['QtyInvoiced'][] = $item['#']['Quantity'][0]['#'];
								}else{
								        $alldata['Completed'][] = 0;
									$alldata['QtyInvoiced'][] = $item['#']['Invoiced'][0]['#'];
								}
								$alldata['UnitPrice'][] = $item['#']['Rate'][0]['#'];
								$alldata['Quantity'][] = $item['#']['Quantity'][0]['#'];
								$alldata['ShipVia'][] = $Shipper;
								$alldata['Narrative'][] = $this->AdjustEntry($item['#']['Desc'][0]['#']);

                                                       		$alldata['QBTxnID'][] = $entry['#']['TxnID'][0]['#'];

                                                       		// now add all incvoices data together
                                                       		if(is_array($entry['#']['LinkedTxn'])){
                                                       		        $invoices = array();
                                                       			foreach($entry['#']['LinkedTxn'] as $invoice){
                                                       			        if($invoice['#']['Type'] == 'invoice'){
                                                       			                $invoices[] =$invoice['#']['TxnID'];
                                                       			        }
                                                       			}
                                                                        $alldata['QBInvoices'][] = implode("~",$invoices);
                                   				}else{
                                   				        // to guarantee sequnce is maintained, emter anempty record
	                                   				$alldata['QBInvoices'][] = "";
	                                   			}
							}

						}
/*
					}else{
						$this->ErrorReport("Please import the chart of accounts before trying to import the Sales Orders.");
						return false;
					}
*/
				}
			}

		}
		// initialize the reports array
		$this->Reports = array();

		// ----------- SalesOrders -------------------

		$row['DebtorNo'] = $alldata['DebtorNo'];
		$row['OrderType'] = $alldata['OrderType'];
		$row['BranchCode'] = $alldata['BranchCode'];
		$row['BuyerName'] = $alldata['BuyerName'];
		$row['ContactPhone'] = $alldata['ContactPhone'];
		$row['ContactEmail'] = $alldata['ContactEmail'];
		$row['DeliveryDate'] = $alldata['DeliveryDate'];
		$row['FromStkLoc'] = $alldata['FromStkLoc'];
		$row['QBTxnID'] = $alldata['QBTxnID'];
		$row['QBInvoices'] = $alldata['QBInvoices'];
		$row['DelAdd1'] = $alldata['DelAdd1'];
		$row['DelAdd2'] = $alldata['DelAdd2'];
		$row['DelAdd3'] = $alldata['DelAdd3'];
		$row['DelAdd4'] = $alldata['DelAdd4'];
		$row['OrdDate'] = $alldata['OrdDate'];
		$row['ShipVia'] = $alldata['ShipVia'];
		$row['SalesTaxPercentage'] = $alldata['SalesTaxPercentage'];

//$files[0] = $this->UploadDir."/SalesOrders.csv";
//		CSV_Titles::Write($files[0],$temparray,1);
		// ok, now start adding the orders one by one and get the ordernumbers back and save them in an array

		$prevTxn = "";          // to store the prvous QBTxnID
		$DetailsRep = 0;
		for($i=0;$i<count($row['DebtorNo']);$i++){
			// first check if order is there using TxnID


			$dummy = DB_Query("SELECT `QBTxnID`,`OrderNo` FROM `SalesOrders` WHERE `QBTxnID`='".$row['QBTxnID'][$i]."'",$this->DB);
			$dummyres = DB_fetch_assoc($dummy);
			$OldOrderNo = $dummyres['OrderNo'];
			
			// second if there & not ours we will overwrite it , delete it and its sales order details
			if($this->Overwrite && $prevTxn !=$row['QBTxnID'][$i] && DB_num_rows($dummy)!=0){
				DB_Query("DELETE FROM `SalesOrders` WHERE `QBTxnID`='".$row['QBTxnID'][$i]."'",$this->DB);
				$detailsdummy = DB_Query("SELECT count(*) FROM `SalesOrderDetails` WHERE `OrderNo` = '".$OldOrderNo."'",$this->DB);
				$detdummy = DB_fetch_row($detailsdummy);
				$DetailsRep += $detdummy[0];
				DB_Query("DELETE FROM `SalesOrderDetails` WHERE `OrderNo` = '".$OldOrderNo."'",$this->DB);
				$this->TotalRepEntries++;
			}elseif($prevTxn !=$row['QBTxnID'][$i] && DB_num_rows($dummy)!=0){
				$this->TotalRepEntries++;
				$detailsdummy = DB_Query("SELECT count(*) FROM `SalesOrderDetails` WHERE `OrderNo` = '".$OldOrderNo."'",$this->DB);
				$detdummy = DB_fetch_row($detailsdummy);
				$DetailsRep += $detdummy[0];

			}



			// third insert Order if not repeated from us
			if($prevTxn!=$row['QBTxnID'][$i]){
				$this->TotalTrials++;
				if(($this->Overwrite && DB_num_rows($dummy)!=0) || DB_num_rows($dummy)==0){

					$sql = "INSERT INTO `SalesOrders`
			(";

			if(DB_num_rows($dummy)!=0) $sql .="`OrderNo`,";

			$sql .="`DebtorNo`,`OrderType`,`BranchCode`,`DeliverTo`,`BuyerName`,`ContactPhone`,`ContactEmail`,`DeliveryDate`,`FromStkLoc`,`QBTxnID`,`QBInvoices`,`DelAdd1`,`DelAdd2`,`DelAdd3`,`DelAdd4`,`OrdDate`,`ShipVia`,`Comments`)
			VALUES
			(";

			if(DB_num_rows($dummy)!=0) $sql .="'".$OldOrderNo."',";
		$sql .="'".$row['DebtorNo'][$i]."',
		'".$row['OrderType'][$i]."',
		'".$row['BranchCode'][$i]."',
		'".$row['BuyerName'][$i]."',
		'".$row['BuyerName'][$i] ."',
		'".$row['ContactPhone'][$i]."',
		'".$row['ContactEmail'][$i]."',
		'".$row['DeliveryDate'][$i]."',
		'".$row['FromStkLoc'][$i]."',
		'".$row['QBTxnID'][$i] ."',
		'".$row['QBInvoices'][$i]."',
		'".$row['DelAdd1'][$i] ."',
		'".$row['DelAdd2'][$i] ."',
		'".$row['DelAdd3'][$i] ."',
		'".$row['DelAdd4'] [$i]."',
		'".$row['OrdDate'][$i]."',
		'".$row['ShipVia'][$i]."',
		'Please note that multiple entries of the same item are not supported in WebERP.<br/>Also, please note that WebERP does not apply taxes to sales orders.'
			)
			";

			DB_Query($sql,$this->DB);
			        $this->TotalEntries++;

				}
			}
			// forth get the order's No.
			$dummy = DB_Query("SELECT `OrderNo` FROM `SalesOrders` WHERE `QBTxnID`='".$row['QBTxnID'][$i]."'",$this->DB);

			$dummyres = DB_fetch_assoc($dummy);
			$alldata['OrderNo'][$i] = $dummyres['OrderNo'];
			// fifth Save TXNID to make sure we don't overwrite our own entries over and over
			$prevTxn = $row['QBTxnID'][$i];

		}

		$this->AddReport("SalesOrders");
		$this->TotalTrials = 0;
		$this->TotalEntries = 0;
//		$this->TotalRepEntries = $DetailsRep;
		$this->TotalRepEntries=0;
		unset($row);
		// ----------- SalesOrderDetails -------------------

		$row = array();
		$row['OrderNo'] = $alldata['OrderNo'];
		$row['StkCode'] = $alldata['StkCode'];
		$row['Completed'] = $alldata['Completed'];
		$row['Narrative'] = $alldata['Narrative'];
		$row['Quantity']=$alldata['Quantity'];
		$row['UnitPrice']=$alldata['UnitPrice'];
		$row['QBTxnID'] = $alldata['QBTxnID'];
		$row['QtyInvoiced'] = $alldata['QtyInvoiced'];

	for($i=0;$i<count($row['OrderNo']);$i++){
			// first check if order is there using TxnID


			$dummy = DB_Query("SELECT `OrderNo` FROM `SalesOrderDetails` WHERE `OrderNo`='".$row['OrderNo'][$i]."' AND `StkCode` = '".$row['StkCode']."'",$this->DB);


			// Second insert Order if not repeated from us

			$this->TotalTrials++;
			if(($this->Overwrite && DB_num_rows($dummy)!=0) || DB_num_rows($dummy)==0){

				$dummy1 = DB_Query("SELECT `OrderNo` FROM `SalesOrderDetails` WHERE `OrderNo` = '".$row['OrderNo'][$i]."' AND `StkCode` = '".$row['StkCode'][$i]."'",$this->DB);
				if(DB_num_rows($dummy1)==0){
					DB_Query("INSERT INTO `SalesOrderDetails`
			(`OrderNo`,`StkCode`,`Completed`,`Narrative`,`Quantity`,`UnitPrice`,`QtyInvoiced`)
			VALUES
			(
		'".$row['OrderNo'][$i]."',
		'".$row['StkCode'][$i]."',
		'".$row['Completed'][$i]."',
		'".$row['Narrative'][$i]."',
		'".$row['Quantity'][$i]."',
		'".$row['UnitPrice'][$i]."',
		'".$row['QtyInvoiced'][$i]."'
			)
			",$this->DB);
			        	$this->TotalEntries++;
				}

			
			}elseif(DB_num_rows($dummy) != 0) $this->TotalRepEntries++;



		}

		$this->AddReport("SalesOrderDetails");
		unset($row);
		// DONE ! :)
		print("<div align=center><b>Sales Orders</b> item imported successfully.</div><br><br>");
		$this->PrintReports();


	}


	// ####################################
	// Function to import Purchase orders
	// ####################################

	function ImportPurchaseOrders(){


		// *******************************************
		// Now extract all required data from the XML that will be used later


		if(!is_array($this->DataArray['PurchaseOrderQueryRs'][0]['#']['PurchaseOrderRet'])){
			// invalid file
			$this->ErrorReport("Please upload a valid Purchase Order QBXML file.");
			return false;
		}

		foreach($this->DataArray['PurchaseOrderQueryRs'][0]['#']['PurchaseOrderRet'] as $entry){

			// To start with we need to make sure that the TXN number is not duplicate
			$Topdummy = DB_Query("SELECT `QBTxnID`,`OrderNo` FROM `PurchOrders` WHERE `QBTxnID` = \"".$entry['#']['TxnID'][0]['#']."\"",$this->DB);
			$TopdummyRes = DB_fetch_row($Topdummy);
			$OrderNo = $TopdummyRes[1];

			// now get the currency rate in case user adjusted it
			$dummy = DB_Query("SELECT `Rate` FROM `Currencies` WHERE `CurrAbrev` = 'USD'",$this->DB);
			$dummyres= DB_fetch_assoc($dummy);
			$Rate = $dummyres['Rate'];

			// now check iwe have at least one stock location
			$dummy=DB_Query("SELECT * FROM `Locations` ORDER BY `LocationName` ASC ",$this->DB);

			// &&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&
			if(DB_num_rows($dummy)==0){
			        $this->ErrorReport('Please Make sure you have set stock Locations.');
			       	return false;
			}
			$dummyres = DB_fetch_assoc($dummy);
			$LocCode = $dummyres['LocCode'];
		        $DelAdd1 = $dummyres['DelAdd1'];
			$DelAdd2 = $dummyres['DelAdd2'];
			$DelAdd3 = $dummyres['DelAdd3'];


			WamaMerge($entry['#']['PurchaseOrderLineRet'],$entry['#']['PurchaseOrderLineGroupRet'],array('#','PurchaseOrderLineRet'));				
				// first handle the DEBIT lines
			if(is_array($entry['#']['PurchaseOrderLineRet'])){

				foreach($entry['#']['PurchaseOrderLineRet'] as $item){
					$continue =1;

//					if(DB_num_rows($dummy) != 0){
						if(DB_num_rows($Topdummy)!=0){

							$continue = 1;

						}
						if($continue){

						        // get the debtor no
						        $dummyname = explode(":",$entry['#']['VendorRef'][0]['#']['FullName'][0]['#']);
						        $FullName = $this->AdjustEntry($dummyname[count($dummyname)-1]);
							$NameToQuery = substr($FullName,0,40);

							// -- now get the debtor no from the debtor no table
							$dummy = DB_Query("SELECT `SupplierID` FROM `Suppliers` WHERE `SuppName` = '".$NameToQuery."' ",$this->DB);
							$dummyres = DB_fetch_assoc($dummy);
							// Check if there is no such customer
							if(DB_num_rows($dummy) ==0){
							        $this->ErrorReport("Supplier '".$NameToQuery."' doesn't exist. Please import suppliers first.");
							        return false;
							}
							$SupplierID = $dummyres['SupplierID'];


							// -- now obtain the first branch code ordered alphabitically
       							$dummy = DB_Query("SELECT * FROM `SupplierContacts` WHERE `SupplierID` = '".$dummyres['SupplierID']."' ORDER BY `OrderContact` ASC ",$this->DB);
							$dummyres = DB_fetch_assoc($dummy);
							if(DB_num_rows($dummy)==0){
							      $this->ErrorReport('Please Make sure you have set supplier contacts.');
							        return false;
							}

	   						$Email = $dummyres['Email'];
						        $Phone =$dummyres['Tel'];
							$Contact =$this->AdjustEntry($dummyres['Contact']);
// #################
// Get Stock Location details here, namely the DelAdd1,2,3
// #################
       						       // ok all clear for now , go on
							if($item['#']['ItemRef'][0]['#']['FullName'][0]['#']){


								$alldata['SupplierNo'][] = $SupplierID;


							        $alldata['ContactPhone'][] =$Phone;
							        $alldata['ContactEmail'][] =$Email;
							        $alldata['DeliveryDate'][] =$entry['#']['DueDate'][0]['#'];

							        $alldata['OrdDate'][] = $entry['#']['TxnDate'][0]['#'];

							        // Get the stock location and check it has enough quantity
							        // &&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&
							        // first get the item full name
							        $dummy = explode(":",$this->AdjustEntry($item['#']['ItemRef'][0]['#']['FullName'][0]['#'],1));
							        if($dummy == '')$dummy = explode(":",$this->AdjustEntry($item['#']['ItemGroupRef'][0]['#']['FullName'][0]['#'],1));
							        $itemName = $this->GetID('StockID',
								$this->AdjustEntry($dummy[count($dummy)-1],1),
								'FullName',
								implode(":",$dummy),
								'StockMaster',
								40

								);

								$alldata['IntoStockLocation'][] = $LocCode;
							        $alldata['DelAdd1'][] = $this->AdjustEntry($DelAdd1);
							        $alldata['DelAdd2'][] = $this->AdjustEntry($DelAdd2);
							        $alldata['DelAdd3'][] = $this->AdjustEntry($DelAdd3);
								$alldata['itemCode'][] = $itemName;
								$alldata['Rate'][] = $Rate;


								// get the cogs gl account
								$dummy = DB_Query("SELECT `StockAct` FROM `StockCategory` WHERE `CategoryID` = (SELECT `CategoryID` FROM `StockMaster` WHERE `StockID`='".substr($itemName,0,20)."')",$this->DB);
								$dummyres = DB_fetch_assoc($dummy);
								$alldata['GLCode'][] = $dummyres['StockAct'];
								if($item['#']['ReceivedQuantity'][0]['#'] ==  $item['#']['Quantity'][0]['#'] || $entry['#']['IsFullyReceived'][0]['#'] == 'true' || $item['#']['IsManuallyClosed'][0]['#'] == 'true'){
									$alldata['Completed'][] = 1;
									$alldata['QuantityRecd'][] = ($item['#']['Quantity'][0]['#'])?$item['Quantity'][0]['#'] : 1;
							}	else{
									 $alldata['Completed'][] = 0;
	

									$alldata['QuantityRecd'][] = $item['#']['ReceivedQuantity'][0]['#'];
								}
								$alldata['UnitPrice'][] = $item['#']['Rate'][0]['#'];
								$alldata['QuantityOrd'][] = ($item['#']['Quantity'][0]['#'])? $item['#']['Quantity'][0]['#'] : 1;
								$alldata['ItemDescription'][] = $this->AdjustEntry($item['#']['Desc'][0]['#']);

                                                       		$alldata['QBTxnID'][] = $entry['#']['TxnID'][0]['#'];

							}

						}

				}
			}

		}
		// initialize the reports array
		$this->Reports = array();

		// ----------- PurchOrders -------------------

		$row['SupplierNo'] = $alldata['SupplierNo'];
		$row['OrdDate'] = $alldata['OrdDate'];
		$row['Rate']=$alldata['Rate'];
		$row['IntoStockLocation'] = $alldata['IntoStockLocation'];
		$row['QBTxnID'] = $alldata['QBTxnID'];
		$row['DelAdd1'] = $alldata['DelAdd1'];
		$row['DelAdd2'] = $alldata['DelAdd2'];
		$row['DelAdd3'] = $alldata['DelAdd3'];


		// ok, now start adding the orders one by one and get the ordernumbers back and save them in an array

		$prevTxn = "";          // to store the prvous QBTxnID
		$DetailsRep = 0;
		for($i=0;$i<count($row['SupplierNo']);$i++){
			// first check if order is there using TxnID


			$dummy = DB_Query("SELECT `QBTxnID`,`OrderNo` FROM `PurchOrders` WHERE `QBTxnID`='".$row['QBTxnID'][$i]."'",$this->DB);

			$dummyres = DB_fetch_assoc($dummy);
			$OldOrderNo = $dummyres['OrderNo'];

			// second if there & not ours we will overwrite it , delete it and its sales order details
			if($this->Overwrite && $prevTxn !=$row['QBTxnID'][$i] && DB_num_rows($dummy)!=0){
				DB_Query("DELETE FROM `PurchOrders` WHERE `QBTxnID`='".$row['QBTxnID'][$i]."'",$this->DB);
				$detailsdummy = DB_Query("SELECT count(*) FROM `PurchOrderDetails` WHERE `OrderNo` = '".$OldOrderNo."'",$this->DB);
				$detdummy = DB_fetch_row($detailsdummy);
				$DetailsRep += $detdummy[0];
				
				$detailsdummy = DB_Query("SELECT `PODetailItem` FROM `PurchOrderDetails` WHERE `OrderNo` = '".$OldOrderNo."'",$this->DB);
				while($koko = DB_fetch_assoc($detailsdummy)){
					DB_Query("DELETE FROM `GRNs` WHERE `PODetailItem` = '".$$koko['PODetailItem']."'",$this->DB);
				}
				DB_Query("DELETE FROM `PurchOrderDetails` WHERE `OrderNo` = '".$OldOrderNo."'",$this->DB);
				$this->TotalRepEntries++;
			}elseif($prevTxn !=$row['QBTxnID'][$i] && DB_num_rows($dummy)!=0){
				$detailsdummy = DB_Query("SELECT count(*) FROM `PurchOrderDetails` WHERE `OrderNo` = '".$OldOrderNo."'",$this->DB);
				$detdummy = DB_fetch_row($detailsdummy);
				$DetailsRep += $detdummy[0];
				$this->TotalRepEntries++;

			}



			// third insert Order if not repeated from us
			if($prevTxn!=$row['QBTxnID'][$i]){
				$this->TotalTrials++;
				if(($this->Overwrite && DB_num_rows($dummy)!=0) || DB_num_rows($dummy)==0){

					$sql = "INSERT INTO `PurchOrders`
			(";

			if(DB_num_rows($dummy)!=0) $sql .="`OrderNo`,";

			$sql .="`SupplierNo`,`OrdDate`,`Rate`,`IntoStockLocation`,`QBTxnID`,`DelAdd1`,`DelAdd2`,`DelAdd3`)
			VALUES
			(";

			if(DB_num_rows($dummy)!=0) $sql .="'".$OldOrderNo."',";
		$sql .="'".$row['SupplierNo'][$i]."',".
		"'".$row['OrdDate'][$i]."',".
		"'".$row['Rate'][$i]."',".
		"'".$row['IntoStockLocation'][$i]."',".
		"'".$row['QBTxnID'][$i]."',".
		"'".$row['DelAdd1'][$i]."',".
		"'".$row['DelAdd2'][$i]."',".
		"'".$row['DelAdd3'][$i]."'".
			")
			";

			DB_Query($sql,$this->DB);
			        $this->TotalEntries++;

				}
			}
			// forth get the order's No.
			$dummy = DB_Query("SELECT `OrderNo` FROM `PurchOrders` WHERE `QBTxnID`='".$row['QBTxnID'][$i]."'",$this->DB);

			$dummyres = DB_fetch_assoc($dummy);

			$alldata['OrderNo'][$i] = $dummyres['OrderNo'];
			// fifth Save TXNID to make sure we don't overwrite our own entries over and over
			$prevTxn = $row['QBTxnID'][$i];

		}

		$this->AddReport("PurchOrders");
		unset($row);
		$this->TotalTrials = 0;
		$this->TotalEntries = 0;
//		$this->TotalRepEntries = $DetailsRep;
		$this->TotalRepEntries = 0;
		// ----------- PurchOrderDetails -------------------

		$row = array();
		$row['SupplierID'] = $alldata['SupplierNo'];
		$row['OrderNo'] = $alldata['OrderNo'];
		$row['itemCode'] = $alldata['itemCode'];
		$row['Completed'] = $alldata['Completed'];
		$row['DeliveryDate'] = $alldata['DeliveryDate'];
		$row['ItemDescription'] = $alldata['ItemDescription'];
		$row['GLCode'] =$alldata['GLCode'];
		$row['QuantityOrd']=$alldata['QuantityOrd'];
		$row['QuantityRecd']=$alldata['QuantityRecd'];
		$row['UnitPrice']=$alldata['UnitPrice'];
		$row['QBTxnID'] = $alldata['QBTxnID'];

	for($i=0;$i<count($row['OrderNo']);$i++){
			// first check if order is there using TxnID


			$dummy = DB_Query("SELECT `OrderNo` FROM `PurchOrderDetails` WHERE `OrderNo`='".$row['OrderNo'][$i]."'",$this->DB);


			// second insert Order if not repeated from us

			$this->TotalTrials++;
			if(($this->Overwrite && DB_num_rows($dummy)!=0) || DB_num_rows($dummy)==0){

				DB_Query("INSERT INTO `PurchOrderDetails`
			(`OrderNo`,`itemCode`,`Completed`,`DeliveryDate`,`ItemDescription`,`GLCode`,`QuantityOrd`,`QuantityRecd`,`UnitPrice`)
			VALUES
			(
		'".$row['OrderNo'][$i]."',
		'".$row['itemCode'][$i]."',
		'".$row['Completed'][$i]."',
		'".$row['DeliveryDate'][$i]."',
		'".$row['ItemDescription'][$i]."',
		'".$row['GLCode'][$i]."',
		'".$row['QuantityOrd'][$i]."',
		'".$row['QuantityRecd'][$i]."',
		'".$row['UnitPrice'][$i]."'
			)
			",$this->DB);
				// get the latest PO detail item id
			if($row['QuantityRecd'][$i] != 0){
				$soso = DB_Query("SELECT MAX(`PODetailItem`) FROM `PurchOrderDetails`",$this->DB);
				$POID = DB_fetch_row($soso);
				$PODetailItem= $POID[0];
				// get the next GRN number 
				$dodo = DB_Query("SELECT `TypeNo` FROM `SysTypes` WHERE `TypeID` = '25'",$this->DB);
				$dodo1 = DB_fetch_row($dodo);
				$GRN = $dodo1[0];
				// update the values in systypes
				DB_Query("UPDATE `SysTypes` SET `TypeNo` = `TypeNo` + 1 WHERE `TypeID` = '25'",$this->DB);
				
				DB_Query("INSERT INTO `GRNs`
				(`GRNBatch`,`PODetailItem`,`ItemCode`,`DeliveryDate`,`ItemDescription`,`QtyRecd`,`SupplierID`)
				VALUES
				(
			'".$GRN."',
			'".$PODetailItem."',
			'".$row['itemCode'][$i]."',
			'".$row['DeliveryDate'][$i]."',
			'".$row['ItemDescription'][$i]."',
			'".$row['QuantityRecd'][$i]."',
			'".$row['SupplierID'][$i]."'
				)
				",$this->DB);
			}
			        $this->TotalEntries++;

				}elseif(DB_num_rows($dummy)!=0)$this->TotalRepEntries++;

			$prevTxn = $row['QBTxnID'][$i];

		}
unset($row);
		$this->AddReport("PurchOrderDetails");

		// DONE ! :)
		print("<div align=center><b>Purchase Orders</b> item imported successfully.</div><br><br>");
		$this->PrintReports();


	}

	// ####################################
	// Function to import Invoices
	// ####################################

	function ImportInvoices(){


		$CompanyData = ReadInCompanyRecord($this->DB);
		// check if a valid file
		if(!is_array($this->DataArray['InvoiceQueryRs'][0]['#']['InvoiceRet'])){
			// invalid file

			$this->ErrorReport("Please upload a valid Invoices QBXML file.");
			return false;
		}
			// get the latest TransNo for the invoices , add 1 and then inc each tiome
		$dummy = DB_Query("SELECT `TypeNo` FROM `SysTypes` WHERE `TypeID`='10'",$this->DB);
		$dummyres= DB_fetch_assoc($dummy);
		$NewTransNo = $dummyres['TypeNo'];

		foreach($this->DataArray['InvoiceQueryRs'][0]['#']['InvoiceRet'] as $entry){
			// To start with we need to make sure that the TXN number is not duplicate

			$Topdummy = DB_Query("SELECT `QBTxnID`,`TransNo` FROM `DebtorTrans` WHERE `QBTxnID` = '".$entry['#']['TxnID'][0]['#']."'",$this->DB);
			$TopdummyRes = DB_fetch_row($Topdummy);
			$TransNo = $TopdummyRes[1];

			// now make sure that the customer is there
			// ------------------------------------------------- >> CUSTOMER DATA << ##
			$dummy = explode(":",$entry['#']['CustomerRef'][0]['#']['FullName'][0]['#']);

			$CustName = substr($this->AdjustEntry($dummy[0]),0,40);



			$dummy = DB_Query("SELECT * FROM `DebtorsMaster` WHERE `Name` like '".$CustName."'",$this->DB);
			if(DB_num_rows($dummy)==0){
			        $this->ErrorReport("Customer '".$CustName."' doesn't exist. Please import customers first.");
			        return false;
			}
			$dummyres = DB_fetch_assoc($dummy);
			$Discount = $dummyres['Discount'];
			$DebtorNo = $dummyres['DebtorNo'];
			$SalesType = $dummyres['SalesType'];

			$ContactPhone =$dummyres['PhoneNo'];
       			$ContactEmail =$dummyres['Email'];

			$dummy = DB_Query("SELECT * FROM `CustBranch` WHERE `DebtorNo` ='".$DebtorNo."' ORDER BY `BrName` ASC",$this->DB);
			if(DB_num_rows($dummy)==0){
			        $this->ErrorReport("Please set customers branches first.");
			        return false;
			}
			$dummyres = DB_fetch_assoc($dummy);
			$Branch = $dummyres['BranchCode'];
			
			//print("For Debtor Bo ".$DebtorNo." , Branch code ".$Branch." from a total of ".DB_num_rows($dummy)." records<br>");
			
			$Area = $dummyres['Area'];
			$Salesman = $dummyres['Salesman'];
			$Shipper = $dummyres['DefaultShipVia'];
			$DelAdd1 = $dummyres['BrAddress1'];
			$DelAdd2 = $dummyres['BrAddress2'];
			$DelAdd3 = $dummyres['BrAddress3'];
			$DelAdd4 = $dummyres['BrAddress4'];
			$BuyerName =$dummyres['BrName'];



			// now check if we will relate to an order or add a new one
			// ------------------------------------------------------ >> SALES ORDER DETAILS << ##
			$dummy = DB_Query("SELECT * FROM `SalesOrders` WHERE `QBInvoices` like '%".$entry['#']['TxnID'][0]['#']."%' ORDER BY `OrderNo` DESC",$this->DB);
			if(DB_num_rows($dummy) !=0){
				$dummyres= DB_fetch_assoc($dummy);
				$OrderNo = $dummyres['OrderNo'];
				//print("Order NO for ".$entry['#']['TxnID'][0]['#']." is ".$OrderNo."<br>");
			}else{
			        $OrderNo = '';
			      //print("No Order NO for ".$entry['#']['TxnID'][0]['#']."<br>");
			}
			// $DebtorNo = $dummyres['DebtorNo'];
			// ^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^
			// now if the customer is not the same, then the user at weberp
			// changed the customer name, then we will use his new name
			// ^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^

			// ### OrderNo:    empty-> new  / value->exists
			WamaMerge($entry['#']['InvoiceLineRet'],$entry['#']['InvoiceLineGroupRet'],array('#','InvoiceLineRet'));
		if(is_array($entry['#']['InvoiceLineRet']))

			foreach($entry['#']['InvoiceLineRet'] as $centry){

				// foreach: item exist and check enough quantity
				// ------------------------------------------------ >> ITEM DETAILS << ##
				$dummy = explode(":",$this->AdjustEntry($centry['#']['ItemRef'][0]['#']['FullName'][0]['#'],1));
				if($dummy =='') $dummy = explode(":",$this->AdjustEntry($centry['#']['ItemGroupRef'][0]['#']['FullName'][0]['#'],1));

				$ItemName = $this->GetID('StockID',
				$this->AdjustEntry($dummy[count($dummy)-1],1),
				'FullName',
				implode(":",$dummy),
				'StockMaster',
				20
				);

				if($ItemName !=''){

					$dummy = DB_Query("SELECT * from `StockMaster` WHERE `StockID` like '".substr($ItemName,0,20)."' ",$this->DB);
					if(DB_num_rows($dummy)==0 && (
					$centry['#']['ItemRef'][0]['#']['Type'][0]['#'] != 'OtherCharge' &&
					$centry['#']['ItemRef'][0]['#']['Type'][0]['#'] != 'Discount' &&
					$centry['#']['ItemRef'][0]['#']['Type'][0]['#'] != 'Subtotal' &&
					$centry['#']['ItemRef'][0]['#']['Type'][0]['#'] != 'Payment' )
					){
					//	print("Item: ".$ItemName."<br>");
					        $this->ErrorReport("Item '".$ItemName."' doesn't exist. Please import items first.");
					        return false;
					}
					$dummyres = DB_fetch_assoc($dummy);
					$StockCat = $dummyres['CategoryID'];
					$MBflag =$dummyres['MBflag'];
					$StdCost = $dummyres['Overheadcost'] + $dummyres['Materialcost'] + $dummyres['Labourcost'];
					// forach: check for quantity and stock ;pcationa

					$dummy= DB_Query("SELECT * FROM `LocStock` WHERE `Quantity`>='".$centry['#']['Quantity'][0]['#']."' AND `StockID` like '".substr($ItemName,0,20)."' ORDER BY `Quantity` DESC",$this->DB);
					if(DB_num_rows($dummy)==0&&(
					$centry['#']['ItemRef'][0]['#']['Type'][0]['#'] != 'OtherCharge' &&
					$centry['#']['ItemRef'][0]['#']['Type'][0]['#'] != 'Discount' &&
					$centry['#']['ItemRef'][0]['#']['Type'][0]['#'] != 'Subtotal' &&
					$centry['#']['ItemRef'][0]['#']['Type'][0]['#'] != 'Payment' ) &&
					($MBflag == 'B' || $MBflag == 'M' )){
/*
						$this->ErrorReport("Please setup stock locations and make sure you have enough quantity of items in them.");
				    		return false;
				    		*/
			
				    		/// insert item quantities
						$dummy= DB_Query("SELECT * FROM `LocStock` WHERE `LocCode`='QBSTK' AND `StockID` like '".substr($ItemName,0,20)."' ORDER BY `Quantity` DESC",$this->DB);
						if(DB_num_rows($dummy) == 0){
						        //DB_Query("INSERT INTO `LocStock`(`LocCode`,`StockID`,`Quantity`) VALUES('QBSTK','".substr($ItemName,0,20)."','".$centry['#']['Quantity'][0]['#']."')",$this->DB);
						}else{
						        //DB_Query("UPDATE `LocStock` SET `Quantity` = `Quantity`+ ".$centry['#']['Quantity'][0]['#']." WHERE `LocCode`='QBSTK' AND `StockID`= '".substr($ItemName,0,20)."'",$this->DB);
						}

					//$dummy= DB_Query("SELECT * FROM `LocStock` WHERE `Quantity`>='".$centry['#']['Quantity'][0]['#']."' AND `StockID` like '".substr($ItemName,0,20)."' ORDER BY `Quantity` DESC",$this->DB);
					}
					$dummyres = DB_fetch_assoc($dummy);
					$LocCode = $dummyres['LocCode'];
					$OldQty = $dummyres['Quantity'];
			
				}
				else{
				        // ^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^
				        // No Item is set
				        // ^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^
				}
				// and now get the period for the transaction date
				$tempdate1 = strtotime($entry['#']['TxnDate'][0]['#']);
				$tempdate= date($this->DefDateFormat , $tempdate1);
				$Period = GetPeriod($tempdate,$this->DB);

				// get all info required about the found stock loc
				// ------------------------------------------------- >> STOCK LOCATION << ##
				$dummy = DB_Query("SELECT * FROM `Locations` WHERE `LocCode` = '".$LocCode."'",$this->DB);
				$dummyres = DB_fetch_assoc($dummy);


				if($centry['#']['Amount'][0]['#'] != 0 && (
				$centry['#']['ItemRef'][0]['#']['Type'][0]['#'] != 'OtherCharge' &&
				$centry['#']['ItemRef'][0]['#']['Type'][0]['#'] != 'Discount' &&
				$centry['#']['ItemRef'][0]['#']['Type'][0]['#'] != 'Subtotal' &&
				$centry['#']['ItemRef'][0]['#']['Type'][0]['#'] != 'Payment' )
				){
				// foreach: increase the TransNo
					if($ItemName!=''){


					// ok now add the data
						$alldata['StockLocation'][] = $LocCode;
						if($TransNo!='') $alldata['TransNo'][] = $TransNo;
						
						else{ 
							if($alldata['QBTxnID'][count($alldata['QBTxnID'])-1] != $entry['#']['TxnID'][0]['#'] )$alldata['TransNo'][] = ++$NewTransNo;
							else $alldata['TransNo'][] = $NewTransNo;
						}
						$alldata['DebtorNo'][] = $DebtorNo;
						$alldata['StockCat'][] = $StockCat;
						$alldata['BranchCode'][] = $DebtorNo;
						$alldata['Period'][] = $Period;
						$alldata['OldQuantity'][] = $OldQty;
						$alldata['StockID'][] = substr($ItemName,0,20);
						$alldata['MBflag'][] = $MBflag;
						$alldata['OrderType'][] = $SalesType;
						$alldata['StandardCost'][] = $StdCost;
						$alldata['Area'][] = $Area;
						$alldata['Discount'][] = $Discount;
						$alldata['Salesperson'][] = $Salesman;
					    $alldata['QBTxnID'][] = $entry['#']['TxnID'][0]['#'];
					    $alldata['InvText'][] = "Please note that multiple entries of the same item are not supported in WebERP.  If the invoice contains such items, their values are added directly to the invoice subtotal.<br/>".$this->AdjustEntry($entry['#']['Memo'][0]['#']);
					    $alldata['Settled'][] = $entry['#']['IsPaid'][0]['#'];
					    $alldata['ActDispDate'][] = $entry['#']['ShipDate'][0]['#'];
					    $alldata['TranDate'][] = $entry['#']['TxnDate'][0]['#'];
						$alldata['DueDate'][] = $entry['#']['DueDate'][0]['#'];
				        $alldata['ItemDesc'][] = $this->AdjustEntry($centry['#']['Desc'][0]['#']);
				        $alldata['UnitPrice'][] = $centry['#']['Rate'][0]['#'];
					    $alldata['Quantity'][] = ($centry['#']['Quantity'][0]['#'])? $centry['#']['Quantity'][0]['#'] : 1;					    $alldata['Amount'][] = $centry['#']['Amount'][0]['#'];
						$alldata['OrderNo'][] = $OrderNo;
						$alldata['BuyerName'][] = $BuyerName;
						$alldata['ContactPhone'][] = $ContactPhone;
			       		$alldata['ContactEmail'][] = $ContactEmail;
			       		$alldata['DelAdd1'][] = $DelAdd1;
			       		$alldata['DelAdd2'][] = $DelAdd2;
			       		$alldata['DelAdd3'][] = $DelAdd3;
			       		$alldata['DelAdd4'][] = $DelAdd4;
						$alldata['OvFreight'][] = 0;
						$alldata['OvGST'][] = $entry['#']['SalesTaxTotal'][0]['#'];
						$alldata['Subtotal'][] = $entry['#']['Subtotal'][0]['#'];
     					}
					else{
					        // ^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^
					        // No Item is set
							// only allowed in QBXML, bug in BXML :)
							// QB doesn't allow this case and so it won't be handeled
					        // ^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^
					}
				}
				elseif( $centry['#']['ItemRef'][0]['#']['Type'][0]['#'] != 'SubTotal'){
				        // it's a discount/payment/othercharge
				        // use += and not = in case two charge items after each other
						//print("FullName: ".$centry['#']['ItemRef'][0]['#']['FullName'][0]['#'].", Amount: ".$centry['#']['Amount'][0]['#']."<br />");
						
										if($centry['#']['ItemRef'][0]['#']['FullName'][0]['#'] != ""){

                                                        $TotalAmount += $centry['#']['Amount'][0]['#'];
                                                        $AllText .= " -- QB ".$centry['#']['ItemRef'][0]['#']['Type'][0]['#']." Item Applied: ".$centry['#']['ItemRef'][0]['#']['FullName'][0]['#']." @ ".$centry['#']['Amount'][0]['#']." ";

                                        }
                                }


                        }
                        foreach($alldata['QBTxnID'] as $x=>$val){
//                              print("QBTxnID id: ".$x." and its val: ".$val." , TxnID : ".$entry['#']['TxnID'][0]['#']."<br />");
                                if($val == $entry['#']['TxnID'][0]['#']){
//                                      The Following 2 lines to be used to dsotre the total amount i the Freight cost
//                                      $alldata['Subtotal'][$x] -= $TotalAmount;
//                                      $alldata['OvFreight'][$x] += $TotalAmount;
                                        if($AllText!="")$alldata['InvText'][$x] .= "<br />The QuickBooks types `OtherCharge`, `Payment` and `Discount` are not supported by webERP, the following values were added directly to the Subtotal<br />".$AllText;
                                }
//			print("DumQB: ".$dumQB[$val]['Text'].", Print_r ");	
//			print_r($dumQB[$val]);
//			print("<br />");
//								if($dumQB[$val]['Text'] !="") $alldata['InvText'][$x] .= "<br />Multiple items in the same invoice are not supported by webERP,  the following values were added directly to the Subtotal<br />".$dumQB[$val]['Text'];
                        }
                        $TotalAmount = 0;
                        $AllText = "";

			
		}
		// ALSO we may have to enter a sales orders + salesorder details
		$this->TotalTrials=0;
		$tempTrials = 0;
		$i=-1;
		$Prev='';
		
		foreach($alldata['OrderNo'] as $Order){
			$i++;
			//print("entry $i : order no $Order <br>");
			if($Order ==''){
/*
			// we will reuse the sales orders import
			// 1) Save old data array
			$temparray = $this->DataArray;

			// 2) Tailor the new xml document request
			// 3) Use xmlize to convert it and save it to data array
			// 4) start ob_start
			// 5) call importSalesOrders()
			// 6) call ob_end_clean
			// 7) Restore the original data array

*/

			if($Prev != $alldata['QBTxnID'][$i]){
				$sql = "INSERT INTO `SalesOrders`
				(";


				$sql .="`DebtorNo`,`OrderType`,`BranchCode`,`ContactPhone`,`ContactEmail`,`DeliveryDate`,`FromStkLoc`,`QBInvoices`,`DelAdd1`,`DelAdd2`,`DelAdd3`,`DelAdd4`,`OrdDate`,`Comments`)
			VALUES
			(";

				$sql .="'".$alldata['DebtorNo'][$i]."',
			'".$alldata['OrderType'][$i]."',
			'".$alldata['BranchCode'][$i]."',
			'".$alldata['ContactPhone'][$i]."',
			'".$alldata['ContactEmail'][$i]."',
			'".$alldata['DueDate'][$i]."',
			'".$this->LocCode."',
			'".$alldata['QBTxnID'][$i] ."',
			'".$alldata['DelAdd1'][$i] ."',
			'".$alldata['DelAdd2'][$i] ."',
			'".$alldata['DelAdd3'][$i] ."',
			'".$alldata['DelAdd4'] [$i]."',
			'".$alldata['TranDate'][$i]."',
'Please note that multiple entries of the same item are not supported in WebERP.'

			)
			";

				DB_Query($sql,$this->DB);
				// now get this order no
				$dummy = DB_Query("Select * FROM `SalesOrders` WHERE `QBInvoices` = '".$alldata['QBTxnID'][$i]."'",$this->DB);

				$dummyres= DB_fetch_assoc($dummy);
				$alldata['OrderNo'][$i] = $dummyres['OrderNo'];
				$this->TotalTrials++;
			}else{
				$alldata['OrderNo'][$i] = $alldata['OrderNo'][$i-1];
				$tempTrials++;
			}				
				//print $dummyres['OrderNo']."<br />";
		
			// handling repeated items in the same imported invoice from QB		
			$testifthere = DB_Query("SELECT * FROM `SalesOrderDetails` WHERE `OrderNo`='".$alldata['OrderNo'][$i]."' AND `StkCode`='".$alldata['StockID'][$i]."'",$this->DB);
			
			$testcount = DB_num_rows($testifthere);
//		print "Test Count: ".$testcount."<br>";	
			if($testcount == 0){
					DB_Query("INSERT INTO `SalesOrderDetails`
			(`OrderNo`,`StkCode`,`Narrative`,`Quantity`,`UnitPrice`,`Completed`)
			VALUES
			(
			'".$alldata['OrderNo'][$i]."',
			'".$alldata['StockID'][$i]."',
			'QB Inv: ".$alldata['InvText'][$i]."',
			'".$alldata['Quantity'][$i]."',
			'".$alldata['UnitPrice'][$i]."',
			'1'
			)
				",$this->DB);
			}else{
				// item already added in this order, add to invoice text 
//				$dumQB[$alldata['QBTxnID'][$i]]['Text'] .= $alldata['StockID'][$i]." @ ".$alldata['UnitPrice'][$i]." X ".$alldata['Quantity'][$i]."<br />";
//				print "dum QB $i : ".$dumQB[$i]."<br>";	
			}
			
			$Prev = $alldata['QBTxnID'][$i];
			}
		}
		$this->TotalEntries =$this->TotalTrials;
		$this->TotalRepEntries=0;
		if($this->TotalEntries != 0 ){
			$this->AddReport("SalesOrders");
			
		}
		if($tempTrials != 0 ){
			$this->TotalEntries = $tempTrials;
			$this->TotalTrials = $tempTrials;
			$this->AddReport("SalesOrderDetails");		
		}

		$total = count($alldata['OrderNo']);
		$this->TotalRepEntries=0;
		$this->TotalEntries=0;
		$this->TotalTrials=0;
		
		$Prev='';
		
		for($i=0;$i<$total;$i++){

			if($dumQB[$alldata['QBTxnID'][$i]]['Text'] != ''){
				if(!substr('<br />Multiple items in the same invoice are not supported by webERP,  the following values were added directly to the Subtotal<br />',$alldata['InvText'][$i])){
					$alldata['InvText'][$i] .= '<br />Multiple items in the same invoice are not supported by webERP,  the following values were added directly to the Subtotal<br />';
				}
				$alldata['InvText'][$i] .=$dumQB[$alldata['QBTxnID'][$i]]['Text'];
			}
		// ----------- DEBTORTRANS -------------------


				if($Prev == $alldata['QBTxnID'][$i] && $i!=0) $FirstRound=false;
				else $FirstRound=true;
				$Prev = $alldata['QBTxnID'][$i];

			// check if repeated entry using QBTxnID
			$dummy = DB_Query("SELECT * FROM `DebtorTrans` WHERE `QBTxnID` = '".$alldata['QBTxnID'][$i]."'",$this->DB);

			// if so increase the repeated entries count
			if(DB_num_rows($dummy) !=0 ){
			        $this->TotalRepEntries++;
			}
			// if we should overwrite, get ID

			if($this->Overwrite && DB_num_rows($dummy) !=0){
			        $dummyres= DB_fetch_assoc($dummy);
			        $ID = $dummyres['ID'];
			        $TempTransNo = $dummyres['TransNo'];
				//print $ID."<BR>";
				// , Delete
				DB_Query("DELETE FROM `DebtorTrans` WHERE `ID`='".$ID."'",$this->DB);
				//DB_Query("DELETE FROM `GLTrans` WHERE `QBTxnNumber`='".$alldata['QBTxnID'][$i]."'",$this->DB);
				// , save ID to be used later in entering the entry into db
			}
			if( (DB_num_rows($dummy) != 0 && $this->Overwrite) ||  DB_num_rows($dummy) ==0){
				// insert the record and increase number of entries
				$this->TotalEntries++;
				$sql = "INSERT INTO `DebtorTrans`
				(";

				$sql .="`TransNo`,
				`Type`,
				`DebtorNo`,
				`BranchCode`,
				`TranDate`,
				`Order_`,
				`OvAmount`,
				`InvText`,
				`QBTxnID`,
				`Prd`,
				`ShipVia`,
				`Rate`,
				`OvFreight`,
				`OvGST`,
				`Tpe`
				)
				Values
				(";

				$sql .="
				'".$alldata['TransNo'][$i]."',
				'10',
				'".$alldata['DebtorNo'][$i]."',
				'".$alldata['BranchCode'][$i]."',
				'".$alldata['TranDate'][$i]."',
				'".$alldata['OrderNo'][$i]."',
				'".$alldata['Subtotal'][$i]."',
				'".$alldata['InvText'][$i]."',
				'".$alldata['QBTxnID'][$i]."',
				'".$Period."',
				'".$Shipper."',
				'1',
				'".$alldata['OvFreight'][$i]."',
				'".$alldata['OvGST'][$i]."',
				'".$alldata['OrderType'][$i]."'
				)
				";
				DB_Query($sql,$this->DB);
				// ------------------------- SysTypes -----------------
				DB_Query("UPDATE `SysTypes` set `TypeNo` = `TypeNo`+1 WHERE `TypeId` = 10",$this->DB);

			}
		}
		$this->TotalTrials = $total;
		$this->AddReport("DebtorTrans");

		// ----------- TRANSDETAILS -------------------
		$this->TotalRepEntries=0;
		$this->TotalEntries=0;
		$this->TotalTrials=0;
		$Prev='';

		for($i=0;$i<$total;$i++){

				if($Prev == $alldata['QBTxnID'][$i] && $i!=0) $FirstRound=false;
				else{
					 $FirstRound=true;
					 $RepTxn = false;
				}
				$Prev = $alldata['QBTxnID'][$i];
				$this->TotalTrials++;
			// check if repeated
			$dummy = DB_Query("SELECT * FROM `TransDetails` WHERE `QBTxnID` = '".$alldata['QBTxnID'][$i]."'",$this->DB);
			if(DB_num_rows($dummy)!=0){
				$this->TotalRepEntries++;

				if($this->Overwrite && $FirstRound){

					DB_Query("DELETE FROM `TransDetails` WHERE `QBTxnID`='".$alldata['QBTxnID'][$i]."'",$this->DB);
					$RepTxn = true;
				}
			}
			// now insert the record
			if( ($RepTxn && $this->Overwrite) ||  DB_num_rows($dummy) ==0 ){
				$this->TotalEntries++;
				$sql = "INSERT INTO `TransDetails`(";

				$sql .="
				 `TransType`,
				 `OrderNo`,
				 `StkCode`,
				 `Quantity`,
				 `Price`,
				 `ActDispDate`,
				 `QBTxnID`
				 ) Values
				 (";

				$sql .="'IN',";
				$sql .="'".$alldata['OrderNo'][$i]."',";
				$sql .="'".$alldata['StockID'][$i]."',";
				$sql .="'".$alldata['Quantity'][$i]."',";
				$sql .="'".$alldata['UnitPrice'][$i]."',";
				$sql .="'".$alldata['ActDispDate'][$i]."',";
				$sql .="'".$alldata['QBTxnID'][$i]."')";
				DB_Query($sql,$this->DB);

			}

		}
		$this->AddReport("TransDetails");

		// ----------- SalesAnalysis -------------------
			$this->TotalRepEntries=0;
			$this->TotalEntries=0;
			$this->TotalTrials=0;
			$Prev = '';
		for($i=0;$i<$total;$i++){
		        // check if repeated entry

				if($Prev == $alldata['QBTxnID'][$i] && $i!=0) $FirstRound=false;
				else $FirstRound=true;
				$Prev = $alldata['QBTxnID'][$i];
						if($FirstRound)$this->TotalTrials++;
		        $dummy= DB_Query("SELECT * FROM `SalesAnalysis` WHERE `QBTxnID` = '".$alldata['QBTxnID'][$i]."'",$this->DB);
		        // if so and overwrite then delete it
		        $ID = '';
		        if(DB_num_rows($dummy) !=0  && $FirstRound){
		                $this->TotalRepEntries++;
		                if($this->Overwrite){
					DB_Query("DELETE FROM `SalesAnalysis` WHERE `QBTxnID`='".$alldata['QBTxnID'][$i]."'",$this->DB);
				}
		        }
		        // if so and not overwrite do nothing
		        // if not repeated / new entry just insert it into the db
		        if(($this->Overwrite && DB_num_rows($dummy)!=0 && $FirstRound) || DB_num_rows($dummy) ==0){
		        		                $this->TotalEntries++;
		                $sql = "INSERT INTO `SalesAnalysis`(";

		                $sql.="
		                `Cust`,
		                `CustBranch`,
		                `Qty`,
		                `StkCategory`,
		                `StockID`,
				`BudgetOrActual`,
				`Area`,
				`Salesperson`,
				`PeriodNo`,
				`TypeAbbrev`,
				`QBTxnID`
		                )values(";

		                $sql .="'".$alldata['DebtorNo'][$i]."',";
		                $sql .="'".$alldata['BranchCode'][$i]."',";
		                $sql .="'".$alldata['Quantity'][$i]."',";
		                $sql .="'".$alldata['StockCat'][$i]."',";
		                $sql .="'".$alldata['StockID'][$i]."',";
		                $sql .="'1',";
				$sql .="'".$alldata['Area'][$i]."',";
				$sql .="'".$alldata['Salesperson'][$i]."',";
				$sql .="'".$alldata['Period'][$i]."',";
				$sql .="'".$alldata['OrderType'][$i]."',";
				$sql .="'".$alldata['QBTxnID'][$i]."'";
				$sql .=");";
				DB_Query($sql,$this->DB);
		        }

		}
		$this->AddReport("SalesAnalysis");
		
		// ----------- StockMoves -------------------

			$this->TotalRepEntries=0;
			$this->TotalEntries=0;
			$this->TotalTrials=0;
			$Prev='';
		for($i=0;$i<$total;$i++){
		// is not dummy

				if($Prev == $alldata['QBTxnID'][$i] && $i!=0) $FirstRound=false;
				else{
					$FirstRound=true;
					$RepTxn=false;
					$SameTxnItems = array();
				}
				$Prev = $alldata['QBTxnID'][$i];
			$this->TotalTrials++;
		        // first check if repeated
		        $dummy = DB_Query("SELECT * FROM `StockMoves` WHERE `QBTxnID` = '".$alldata['QBTxnID'][$i]."' ORDER BY `StkMoveNo` ASC",$this->DB);
		        // if repeated and overwirte, save StkMoveNo and delete 

                                //print "first Round: ".$FirstRound.", DB_num_rows ".DB_num_rows($dummy)." , Overwrite ".$this->Overwrite.", RepTxn: ".$RepTxn."<br />";


		        if(DB_num_rows($dummy) ){
					$this->TotalRepEntries++;
					if($FirstRound)$RepTxn = true;
				}
//				print "first Round: ".$FirstRound.", DB_num_rows ".DB_num_rows($dummy)." , Overwrite ".$this->Overwrite.", RepTxn: ".$RepTxn."<br />";
				
		        if($alldata['OrderNo'][$i] != '' && $this->Overwrite && $RepTxn && $FirstRound){
                DB_Query("DELETE FROM `StockMoves` WHERE `QBTxnID`='".$alldata['QBTxnID'][$i]."'",$this->DB);
						
						
		        }
		        // if not repeated or new insert it
				
		        if( ( ($RepTxn &&  $this->Overwrite) || !$RepTxn ) && !in_array($alldata['StockID'][$i],$SameTxnItems) ){
		                $this->TotalEntries++;
						$SameTxnItems[] = $alldata['StockID'][$i];
			      if($alldata['MBflag'][$i] != 'K'){

					$sql = "INSERT INTO `StockMoves`(";

			                $sql .="
			                `LocCode`,
			                `Type`,
			                `Reference`,
		                	`TransNo`,
		        	        `StockID`,
		        	        `StandardCost`,
			                `TranDate`,
			                `DebtorNo`,
			                `BranchCode`,
		                	`Price`,
		                	`QBTxnID`,
		        	        `Qty`,";
			                // QOH is only tracked iof buy or manfuctured
			                if($alldata['MBflag'][$i]=='M' || $alldata['MBflag'][$i]=='B') $sql .="`NewQOH`,";
			                $sql .="`Prd`


			                )VALUES(";

			                $sql .="'".$alldata['StockLocation'][$i]."',";
		                	$sql .="'10',";
		                	$sql .="'".$alldata['OrderNo'][$i]."',";
		        	        $sql .="'".$alldata['TransNo'][$i]."',";
			                $sql .="'".$alldata['StockID'][$i]."',";
			                $sql .="'".$alldata['StandardCost'][$i]."',";
			                $sql .="'".$alldata['TranDate'][$i]."',";
			                $sql .="'".$alldata['DebtorNo'][$i]."',";
		                	$sql .="'".$alldata['BranchCode'][$i]."',";
		        	        $sql .="'".$alldata['UnitPrice'][$i]."',";
		        	        $sql .="'".$alldata['QBTxnID'][$i]."',";
			                $sql .="'-".$alldata['Quantity'][$i]."',";
			                if($alldata['MBflag'][$i]=='M' || $alldata['MBflag'][$i]=='B') $sql .="'".($alldata['OldQuantity'][$i] - $alldata['Quantity'][$i] )."',";
			                $sql .="'".$alldata['Period'][$i]."'";

			                $sql .=")";
		                	DB_Query($sql,$this->DB);

		                	// now update the LOCSTOCK quantities
					/*
		                	DB_Query("UPDATE `LocStock` SET
					`Quantity`='".floatval($alldata['OldQuantity'][$i] - $alldata['Quantity'][$i])."'
					WHERE
					`LocCode`='".$alldata['StockLocation'][$i]."' AND
					`StockID`='".$alldata['StockID'][$i]."'
					",$this->DB);
					*/
				}
		                // now check if assembly/kit get its components from BOm and post it to the stock moves
		                if($alldata['MBflag'][$i] == 'K' || $alldata['MBflag'][$i] == 'A'){
					// get all its components from the BOM and insert the stock moves
					$dummy=DB_Query("SELECT WorkCentreAdded FROM `BOM` WHERE `Parent`='".$alldata['StockID'][$i]."' AND `LocCode` ='".$alldata['StockLocation'][$i]."' ORDER BY `WorkCentreAdded` ASC",$this->DB);
					$dummyres = DB_fetch_assoc($dummy);
					$WorkCentre = $dummyres['WorkCenterAdded'];
					$dummy=DB_Query("SELECT * FROM `BOM` WHERE `Parent`='".$alldata['StockID'][$i]."' AND `LocCode` ='".$alldata['StockLocation'][$i]."' AND `WorkCentreAdded`= '".$WorkCentre."'",$this->DB);
					while($dummyres = DB_fetch_assoc($dummy)){
						//print($alldata['StockID']."<br />");
						// Query to get the standard cost of the componets
						$dummy1 = DB_Query("SELECT * FROM `StockMaster` WHERE `StockID`='".$dummyres['Component']."'",$this->DB);
						$dummyr1 = DB_fetch_assoc($dummy1);
						$CompStdCost = $dummyr1['Labourcost'] + $dummyr1['Overheadcost'] + $dummyr1['Materialcost'];
						$MBflag = $dummyr1['MBflag'];
					        // nsert a record for each component
				        	$sql = "INSERT INTO `StockMoves`(";

				                $sql .="
				                `LocCode`,
			        	        `Type`,
			        	        `Reference`,
		                		`TransNo`,
			        	        `StockID`,
				                `TranDate`,
				                `DebtorNo`,
				                `BranchCode`,
						`StandardCost`,
						`QBTxnID`,
		        	        	`Qty`,";
				                // QOH is only tracked iof buy or manfuctured
				                if($MBflag=='M' || $MBflag=='B') $sql .="`NewQOH`,";
				                $sql .="`Prd`


				                )VALUES(";

				                $sql .="'".$alldata['StockLocation'][$i]."',";
		        	        	$sql .="'10',";
		        	        	$sql .="'Assembly: ".$alldata['StockID'][$i]." Order: ".$alldata['OrderNo'][$i]."',";
		        		        $sql .="'".$alldata['TransNo'][$i]."',";
			                	$sql .="'".$dummyres['Component']."',";
				                $sql .="'".$alldata['TranDate'][$i]."',";
				                $sql .="'".$alldata['DebtorNo'][$i]."',";
			                	$sql .="'".$alldata['BranchCode'][$i]."',";
						$sql .="'".$CompStdCost."',";
			        	        $sql .="'".$alldata['QBTxnID'][$i]."',";
			        	        $sql .="'-".$dummyres['Quantity']."',";
			                	if($MBflag=='M' || $MBflag=='B') $sql .="'".($alldata['OldQuantity'][$i] - $alldata['Quantity'][$i] )."',";
				                $sql .="'".$alldata['Period'][$i]."'";

				                $sql .=")";
			                	DB_Query($sql,$this->DB);

			            	// now update the LOCSTOCK quantities
					/*
		        	        	DB_Query("UPDATE `LocStock` SET
						`Quantity`=`Quantity`-".floatval($dummyres['Quantity'])."
						WHERE
						`LocCode`='".$alldata['StockLocation'][$i]."' AND
						`StockID`='".$dummyres['Component']."'
						",$this->DB);
						*/
					}




		                }
		        }

		}
		$this->AddReport("StockMoves");
		
		// ----------- GLTRANS -------------------
			$this->TotalRepEntries=0;
			$this->TotalEntries=0;
			$this->TotalTrials=0;
			$Prev = '';
			for($i=0;$i<$total;$i++){
				// frist check if the transactions is there

				if($Prev == $alldata['QBTxnID'][$i] && $i!=0) $FirstRound=false;
				else $FirstRound=true;
				$Prev = $alldata['QBTxnID'][$i];
				if($FirstRound)$this->TotalTrials++;

				$dummy = DB_Query("SELECT * FROM `GLTrans` WHERE `QBTxnNumber` = '".$alldata['QBTxnID'][$i]."'",$this->DB);
				// if repeated and overwrite then delete them
				$Counter ='';

				if(DB_num_rows($dummy) && $FirstRound)$this->TotalRepEntries++;
				if($this->Overwrite && DB_num_rows($dummy) && $FirstRound){

				        $dummyres = DB_fetch_assoc($dummy);
					$Counter = $dummyres['CounterIndex'];
					DB_Query("DELETE FROM `GLTrans` WHERE `QBTxnNumber` ='".$alldata['QBTxnID'][$i]."'",$this->DB);
				}

				// if new / repeeated and overwrite insert the new records
				if(($this->Overwrite && DB_num_rows($dummy)!= 0 ) || DB_num_rows($dummy) ==0 ){
		                        include_once('includes/GetSalesTransGLCodes.inc');
								
					// the check for  the oirginal code if the config allows to post to GL
					        if($FirstRound)$this->TotalEntries++;

					if ($CompanyData['GLLink_Stock']==1 AND $alldata['StandardCost'][$i] !=0){
					        // COGS Accounts

					        $sql = "INSERT INTO `GLTrans`(";

					        $sql .="
						`Type`,
						`TypeNo`,
						`QBTxnNumber`,
						`TranDate`,
						`PeriodNo`,
						`Amount`,
						`Narrative`,
						`Account`,
						`Posted`
						)VALUES(";

						$sql.="'10',";
						$sql.="'".$alldata['TransNo'][$i]."',";
						$sql.="'".$alldata['QBTxnID'][$i]."',";
						$sql.="'".$alldata['TranDate'][$i]."',";
						$sql.="'".$alldata['Period'][$i]."',";
						$sql.="'".$alldata['Amount'][$i]."',";
						$sql.="'".$alldata['DebtorNo'][$i]." - ".$alldata['StockID'][$i]." x ".$alldata['Quantity'][$i]." @ ".$alldata['UnitPrice'][$i]."',";
						$sql.="'".GetCOGSGLAccount($alldata['Area'][$i], $alldata['StockID'][$i], $alldata['SalesType'][$i], $this->DB)."',";
						$sql.="'1'";
						$sql.=");";
						
						DB_Query($sql,$this->DB);
						// now the sales post
						$xyz = GetStockGLCode($alldata['StockID'][$i],$this->DB);
						$sql = "INSERT INTO `GLTrans`(";

					        $sql .="
						`Type`,
						`TypeNo`,
						`QBTxnNumber`,
						`TranDate`,
						`PeriodNo`,
						`Amount`,
						`Narrative`,
						`Account`,
						`Posted`
						)VALUES(";

						$sql.="'10',";
						$sql.="'".$alldata['TransNo'][$i]."',";
						$sql.="'".$alldata['QBTxnID'][$i]."',";
						$sql.="'".$alldata['TranDate'][$i]."',";
						$sql.="'".$alldata['Period'][$i]."',";
						$sql.="'".-1 * ($alldata['Amount'][$i])."',";
						$sql.="'".$alldata['DebtorNo'][$i]." - ".$alldata['StockID'][$i]." x ".$alldata['Quantity'][$i]." @ ".$alldata['UnitPrice'][$i]."',";
						$sql.="'".$xyz[0]."',";
						$sql.="'1'";
						$sql.=");";
						DB_Query($sql,$this->DB);
					}
					// Sales posting
					if ($CompanyData['GLLink_Debtors']==1 && $alldata['UnitPrice'][$i] !=0){
						//Post sales transaction to GL credit sales
						$SalesGLAccounts = GetSalesGLAccount($alldata['Area'][$i], $alldata['StockID'][$i], $alldata['SalesType'][$i], $this->DB);

						$sql = "INSERT INTO `GLTrans`(";

					        $sql .="
						`Type`,
						`TypeNo`,
						`QBTxnNumber`,
						`TranDate`,
						`PeriodNo`,
						`Amount`,
						`Narrative`,
						`Account`,
						`Posted`
						)VALUES(";

						$sql.="'10',";
						$sql.="'".$alldata['TransNo'][$i]."',";
						$sql.="'".$alldata['QBTxnID'][$i]."',";
						$sql.="'".$alldata['TranDate'][$i]."',";
						$sql.="'".$alldata['Period'][$i]."',";
						$sql.="'". -1 * ($alldata['Amount'][$i])."',";
						$sql.="'".$alldata['DebtorNo'][$i]." - ".$alldata['StockID'][$i]." x ".$alldata['Quantity'][$i]." @ ".$alldata['UnitPrice'][$i]."',";
						$sql.="'".$SalesGLAccounts['SalesGLCode']."',";
						$sql.="'1'";
						$sql.=");";
						// now post the discount if there is one
						if($alldata['Discount'][$i] > 0){
							$sql .= "INSERT INTO `GLTrans`(";

						        $sql .="
							`Type`,
							`TypeNo`,
							`QBTxnNumber`,
							`TranDate`,
							`PeriodNo`,
							`Amount`,
							`Narrative`,
							`Account`,
							`Posted`
							)VALUES(";

							$sql.="'10',";
							$sql.="'".$alldata['TransNo'][$i]."',";
							$sql.="'".$alldata['QBTxnID'][$i]."',";
							$sql.="'".$alldata['TranDate'][$i]."',";
							$sql.="'".$alldata['Period'][$i]."',";
							$sql.="'". ($alldata['Amount'][$i] * $alldata['Discount'][$i])."',";
							$sql.="'".$alldata['DebtorNo'][$i]." - ".$alldata['StockID'][$i]." x ".$alldata['Quantity'][$i]." @ ".$alldata['UnitPrice'][$i]."',";
							$sql.="'".$SalesGLAccounts['SalesGLCode']."',";
							$sql.="'1'";
							$sql.=");";
						}
						DB_Query($sql,$this->DB);
					}
					}


				}


		$this->AddReport("GLTrans");
/*
		// ------------ SysTypes --------------------
			$this->TotalRepEntries=0;
			$this->TotalEntries=0;
			$this->TotalTrials=0;
		// Update it with the latest value for the trans no if we are writting a new invoice
			$MaxTransNo = $alldata['TransNo'][count($alldata['TransNo'])-1];

		        DB_Query("UPDATE `SysTypes` SET `TypeNo`='".$MaxTransNo."' WHERE `TypeID`='10'",$this->DB);
*/
		return true;

	}

	// ####################################
	// Function to import Credit Memos
	// ####################################

	function ImportCreditMemos(){

		$CompanyData = ReadInCompanyRecord($this->DB);
		// check if a valid file
		if(!is_array($this->DataArray['CreditMemoQueryRs'][0]['#']['CreditMemoRet'])){
			// invalid file
			$this->ErrorReport("Please upload a valid Credit Memos QBXML file.");
			return false;
		}
		// get the latest TransNo for the credit notes, add 1 and then inc each tiome
		$dummy = DB_Query("SELECT `TypeNo` FROM `SysTypes` WHERE `TypeID`='11'",$this->DB);
		$dummyres= DB_fetch_assoc($dummy);
		$NewTransNo = $dummyres['TypeNo'];

		foreach($this->DataArray['CreditMemoQueryRs'][0]['#']['CreditMemoRet'] as $entry){
			// To start with we need to make sure that the TXN number is not duplicate
			$Topdummy = DB_Query("SELECT `QBTxnID`,`TransNo` FROM `DebtorTrans` WHERE `QBTxnID` = '".$entry['#']['TxnID'][0]['#']."'",$this->DB);
			$TopdummyRes = DB_fetch_row($Topdummy);
			$TransNo = $TopdummyRes[1];

			// now make sure that the customer is there
			// ------------------------------------------------- >> CUSTOMER DATA << ##
			$dummy = explode(":",$entry['#']['CustomerRef'][0]['#']['FullName'][0]['#']);
			$CustName = substr($this->AdjustEntry($dummy[0]),0,40);


			$dummy = DB_Query("SELECT * FROM `DebtorsMaster` WHERE `Name` like '".$CustName."'",$this->DB);
			if(DB_num_rows($dummy)==0){
			        $this->ErrorReport("Customer '".$CustName."' doesn't exist. Please import customers first.");
			        return false;
			}
			$dummyres = DB_fetch_assoc($dummy);
			$Discount = $dummyres['Discount'];
			$DebtorNo = $dummyres['DebtorNo'];

			$dummy = DB_Query("SELECT * FROM `CustBranch` WHERE `DebtorNo` ='".$DebtorNo."' ORDER BY `BrName` ASC",$this->DB);
			if(DB_num_rows($dummy)==0){
			        $this->ErrorReport("Please set customers branches first.");
			        return false;
			}
			$dummyres = DB_fetch_assoc($dummy);
//			$Branch = $dummyres['BranchCode'];
			$Branch = $DebtorNo;
			$Area = $dummyres['Area'];
			$Salesman = $dummyres['Salesman'];


			WamaMerge($entry['#']['CreditMemoLineRet'],$entry['#']['CreditMemoLineGroupRet'],array('#','CreditMemoLineRet'));

			foreach($entry['#']['CreditMemoLineRet'] as $centry){
				// foreach: item exist and check enough quantity
				// ------------------------------------------------ >> ITEM DETAILS << ##
				$dummy = explode(":",$this->AdjustEntry($centry['#']['ItemRef'][0]['#']['FullName'][0]['#'],1));
				if($dummy =='') $dummy = explode(":",$this->AdjustEntry($centry['#']['ItemGroupRef'][0]['#']['FullName'][0]['#'],1));
//				$ItemName = substr($this->AdjustEntry($dummy[count($dummy)-1]),0,20);
				$ItemName = $this->GetID('StockID',
				$this->AdjustEntry($dummy[count($dummy)-1],1)
				,'FullName',$centry['#']['ItemRef'][0]['#']['FullName'][0]['#'],'StockMaster',20);

				if($ItemName !=''){
					$dummy = DB_Query("SELECT * from `StockMaster` WHERE `StockID`='".substr($ItemName,0,20)."' ",$this->DB);
					if(DB_num_rows($dummy)==0 &&(
					$centry['#']['ItemRef'][0]['#']['Type'][0]['#'] != 'OtherCharge' &&
					$centry['#']['ItemRef'][0]['#']['Type'][0]['#'] != 'Discount' &&
					$centry['#']['ItemRef'][0]['#']['Type'][0]['#'] != 'Subtotal' &&
					$centry['#']['ItemRef'][0]['#']['Type'][0]['#'] != 'Payment' )
					){
					        $this->ErrorReport("Item '".$ItemName."' doesn't exist. Please import items first.");
					        return false;
					}
					$dummyres = DB_fetch_assoc($dummy);
					$StockCat = $dummyres['CategoryID'];
					$MBflag =$dummyres['MBflag'];
					$StdCost = $dummyres['Overheadcost'] + $dummyres['Materialcost'] + $dummyres['Labourcost'];
					// forach: check for quantity and stock ;pcationa

					$dummy= DB_Query("SELECT * FROM `LocStock` WHERE `StockID`='".substr($ItemName,0,20)."' ORDER BY `Quantity` DESC",$this->DB);
					if(DB_num_rows($dummy)==0&&(
					$centry['#']['ItemRef'][0]['#']['Type'][0]['#'] != 'OtherCharge' &&
					$centry['#']['ItemRef'][0]['#']['Type'][0]['#'] != 'Discount' &&
					$centry['#']['ItemRef'][0]['#']['Type'][0]['#'] != 'Subtotal' &&
					$centry['#']['ItemRef'][0]['#']['Type'][0]['#'] != 'Payment' )&&
					($MBflag == 'B' || $MBflag == 'M' )){
					        $this->ErrorReport("Please setup stock locations.");
					        return false;
					}
					$dummyres = DB_fetch_assoc($dummy);
					$LocCode = $dummyres['LocCode'];
					$OldQty = $dummyres['Quantity'];
                                }
				else{
					// ^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^
				        // No Item is set
				        // ^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^
				}
				// and now get the period for the transaction date
				$tempdate1 = strtotime($entry['#']['TxnDate'][0]['#']);
				$tempdate= date($this->DefDateFormat , $tempdate1);
				$Period = GetPeriod($tempdate,$this->DB);
//print("<script>alert('".$Period."');</script>");

				// get all info required about the found stock loc
				// ------------------------------------------------- >> STOCK LOCATION << ##
				$dummy = DB_Query("SELECT * FROM `Locations` WHERE `LocCode` = '".$LocCode."'",$this->DB);
				$dummyres = DB_fetch_assoc($dummy);


				if($centry['#']['Amount'][0]['#'] != 0&& (
				$centry['#']['ItemRef'][0]['#']['Type'][0]['#'] != 'OtherCharge' &&
				$centry['#']['ItemRef'][0]['#']['Type'][0]['#'] != 'Discount' &&
				$centry['#']['ItemRef'][0]['#']['Type'][0]['#'] != 'Subtotal' &&
				$centry['#']['ItemRef'][0]['#']['Type'][0]['#'] != 'Payment' )
				){
				// foreach: increase the TransNo

					if($ItemName !=''){


					// ok now add the data
						$alldata['StockLocation'][] = $LocCode;
						if($TransNo!='') $alldata['TransNo'][] = $TransNo;
						else{ 
							if($alldata['QBTxnID'][count($alldata['QBTxnID'])-1] != $entry['#']['TxnID'][0]['#'] )$alldata['TransNo'][] = ++$NewTransNo;
							else $alldata['TransNo'][] = $NewTransNo;
						}
						$alldata['DebtorNo'][] = $DebtorNo;
						$alldata['StockCat'][] = $StockCat;
						$alldata['BranchCode'][] = $Branch;
						$alldata['Period'][] = $Period;
						$alldata['OldQuantity'][] = $OldQty;
						$alldata['StockID'][] = substr($ItemName,0,20);
						$alldata['MBflag'][] = $MBflag;
						$alldata['StandardCost'][] = $StdCost;
						$alldata['Area'][] = $Area;
						$alldata['Discount'][] = $Discount;
						$alldata['Salesperson'][] = $Salesman;
						$alldata['InvText'][] = "Please note that multiple entries of the same item  are not supported in WebERP.  If the credit memo contains such items, their values are added directly to the credit memo subtotal.<br/>".$this->AdjustEntry($entry['#']['Memo'][0]['#']);
				        	$alldata['QBTxnID'][] = $entry['#']['TxnID'][0]['#'];
							$alldata['Subtotal'][] = $entry['#']['Subtotal'][0]['#'];
					        $alldata['Settled'][] = $entry['#']['IsPaid'][0]['#'];
					        $alldata['ActDispDate'][] = $entry['#']['ShipDate'][0]['#'];
					        $alldata['TranDate'][] = $entry['#']['TxnDate'][0]['#'];
					        $alldata['ItemDesc'][] = $this->AdjustEntry($centry['#']['Desc'][0]['#']);
			        		$alldata['UnitPrice'][] = $centry['#']['Rate'][0]['#'];
				        	$alldata['Quantity'][] = ($centry['#']['Quantity'][0]['#'])?$centry['#']['Quantity'][0]['#']:1;
					        $alldata['Amount'][] = $centry['#']['Amount'][0]['#'];
							$alldata['OvFreight'][] = 0;
							$alldata['OvGST'][] = $entry['#']['SalesTaxTotal'][0]['#'];
//						$alldata['BranchCode'][] = $Branch;
					}
					else{
					        // ^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^
					        // No Item is set
					        // ^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^
					}

				}elseif( $centry['#']['ItemRef'][0]['#']['Type'][0]['#'] != 'SubTotal'){
				        // it's a discount/payment/othercharge
				        // it's a discount/payment/othercharge
				        // use += and not = in case two charge items after each other
					if($centry['#']['ItemRef'][0]['#']['FullName'][0]['#'] != 0){
         
                                                        $TotalAmount += $centry['#']['Amount'][0]['#'];
                                                        $AllText .= " -- QB ".$centry['#']['ItemRef'][0]['#']['Type'][0]['#']." Item Applied: ".$centry['#']['ItemRef'][0]['#']['FullName'][0]['#']." @ ".$centry['#']['Amount'][0]['#']." ";

                                        }
                                }


                        }
                        if(is_array($alldata['QBTxnID']))foreach($alldata['QBTxnID'] as $x=>$val){
//                              print("QBTxnID id: ".$x." and its val: ".$val." , TxnID : ".$entry['#']['TxnID'][0]['#']."<br />");
                                if($val == $entry['#']['TxnID'][0]['#']){
//                                      The Following 2 lines to be used to dsotre the total amount i the Freight cost
//                                      $alldata['Subtotal'][$x] -= $TotalAmount;
//                                      $alldata['OvFreight'][$x] += $TotalAmount;
                                        if($AllText!="")$alldata['InvText'][$x] .= "<br />The QuickBooks types `OtherCharge`, `Payment` and `Discount` are not supported by webERP, the values were added directly to the Subtotal<br />".$AllText;
                                }
                        }
                        $TotalAmount = 0;
                        $AllText = "";

		}


		$total = count($alldata['DebtorNo']);

			$this->TotalRepEntries=0;
			$this->TotalEntries=0;
			$this->TotalTrials=0;
			$Prev = '';
			$FirstRound=True;

		for($i=0;$i<$total;$i++){

		// ----------- DEBTORTRANS -------------------
			if($Prev == $alldata['QBTxnID'][$i] && $i!=0) $FirstRound=false;
			else $FirstRound=true;
			$Prev = $alldata['QBTxnID'][$i];

			// check if repeated entry using QBTxnID
			$dummy = DB_Query("SELECT * FROM `DebtorTrans` WHERE `QBTxnID` = '".$alldata['QBTxnID'][$i]."'",$this->DB);

			// if so increase the repeated entries count
			if(DB_num_rows($dummy) !=0){
			        $this->TotalRepEntries++;
			}
			// if we should overwrite, get ID
			$ID='';
			if($this->Overwrite && DB_num_rows($dummy)!=0){
			        $dummyres= DB_fetch_assoc($dummy);
			        $ID = $dummyres['ID'];
			        $TempTransNo = $dummyres['TransNo'];

				// , Delete
				DB_Query("DELETE FROM `DebtorTrans` WHERE `ID`='".$ID."'",$this->DB);
				//DB_Query("DELETE FROM `GLTrans` WHERE `QBTxnNumber`='".$alldata['QBTxnID'][$i]."'",$this->DB);
				// , save ID to be used later in entering the entry into db
			}
			if( (DB_num_rows($dummy) != 0 && $this->Overwrite) ||  DB_num_rows($dummy) ==0){
				// insert the record and increase number of entries
				$this->TotalEntries++;

				$sql = "INSERT INTO `DebtorTrans`
				(";

				$sql .="`TransNo`,
				`Type`,
				`DebtorNo`,
				`BranchCode`,
				`TranDate`,
				`OvAmount`,
				`OvFreight`,
				`OvGST`,
				`InvText`,
				`QBTxnID`,
				`Rate`,
				`Prd`
				)
				Values
				(";

				$sql .="
				'".$alldata['TransNo'][$i]."',
				'11',
				'".$alldata['DebtorNo'][$i]."',
				'".$alldata['BranchCode'][$i]."',
				'".$alldata['TranDate'][$i]."',
				'".-1*$alldata['Subtotal'][$i]."',
				'".-1*$alldata['OvFreight'][$i]."',
				'".-1*$alldata['OvGST'][$i]."',
				'".$alldata['InvText'][$i]."',
				'".$alldata['QBTxnID'][$i]."',
				'1',
				'".$alldata['Period'][$i]."'
				)
				";
				DB_Query($sql,$this->DB);
			}
		}
		$this->TotalTrials = $total;
		$this->AddReport("DebtorTrans");

		// ----------- TRANSDETAILS -------------------
		$this->TotalRepEntries=0;
		$this->TotalEntries=0;
		$this->TotalTrials=0;
		$Prev='';
		for($i=0;$i<$total;$i++){

			// check if repeated
			if($Prev == $alldata['QBTxnID'][$i]  && $i!=0) $FirstRound=false;
			else{
					 $FirstRound=true;
					 $RepTxn = false;
				}
				$Prev = $alldata['QBTxnID'][$i];
				$this->TotalTrials++;
			// check if repeated
			$dummy = DB_Query("SELECT * FROM `TransDetails` WHERE `QBTxnID` = '".$alldata['QBTxnID'][$i]."'",$this->DB);
			$dummyres = DB_fetch_assoc($dummy);
			if(DB_num_rows($dummy)!=0 && $FirstRound){
				$this->TotalRepEntries++;

				if($this->Overwrite && $FirstRound){

					DB_Query("DELETE FROM `TransDetails` WHERE `QBTxnID`='".$alldata['QBTxnID'][$i]."'",$this->DB);
					$RepTxn = true;
				}
			}
			// now insert the record
			if( ($RepTxn && $this->Overwrite) ||  DB_num_rows($dummy)==0){
				$this->TotalEntries++;
				$sql = "INSERT INTO `TransDetails`(";

				$sql .="
				 `TransType`,
				 `StkCode`,
				 `Quantity`,
				 `Price`,
				 `ActDispDate`,
				 `QBTxnID`
				 ) Values
				 (";

				$sql .="'CN',";
				$sql .="'".$alldata['StockID'][$i]."',";
				$sql .="'".$alldata['Quantity'][$i]."',";
				$sql .="'".$alldata['UnitPrice'][$i]."',";
				$sql .="'".$alldata['ActDispDate'][$i]."',";
				$sql .="'".$alldata['QBTxnID'][$i]."')";
				DB_Query($sql,$this->DB);

			}

		}
		$this->AddReport("TransDetails");

			// ----------- StockMoves -------------------

			$this->TotalRepEntries=0;
			$this->TotalEntries=0;
			$this->TotalTrials=0;
			$Prev = '';
			$FirstRound=True;
		for($i=0;$i<$total;$i++){
		// is not dummy


			// first check if repeated & NOT OURS
			if($Prev == $alldata['QBTxnID'][$i] && $i != 0) $FirstRound=false;
			else{
				$FirstRound=true;
				$RepTxn=false;
				$SameTxnItems = array();
			}
			$Prev = $alldata['QBTxnID'][$i];
			$this->TotalTrials++;
		        $dummy = DB_Query("SELECT * FROM `StockMoves` WHERE `QBTxnID` = '".$alldata['QBTxnID'][$i]."'",$this->DB);
		        // if repeated and overwirte, save StkMoveNo and delete entry

		        if(DB_num_rows($dummy) ){
				$this->TotalRepEntries++;
				if($FirstRound)$RepTxn = true;
			}
//print($FirstRound.", ".$RepTxn.", ".$this->Overwrite);
		        if($this->Overwrite &&  $FirstRound && $RepTxn){
//	print("in sql");
		                DB_Query("DELETE FROM `StockMoves` WHERE `QBTxnID`='".$alldata['QBTxnID'][$i]."'",$this->DB);
		        }

		        // if not repeated or new insert it
		        if((($RepTxn &&  $this->Overwrite) || !$RepTxn ) && !in_array($alldata['StockID'][$i],$SameTxnItems) ){
		                $this->TotalEntries++;
				$SameTxnItems[] = $alldata['StockID'][$i];
			      if($alldata['MBflag'][$i] != 'K'){

					$sql = "INSERT INTO `StockMoves`(";

			                $sql .="
			                `LocCode`,
			                `Type`,

		                	`TransNo`,
		        	        `StockID`,
		        	        `StandardCost`,
			                `TranDate`,
			                `DebtorNo`,
			                `BranchCode`,
		                	`Price`,
		                	`QBTxnID`,
		        	        `Qty`,";
			                // QOH is only tracked iof buy or manfuctured
			                if($alldata['MBflag'][$i]=='M' || $alldata['MBflag'][$i]=='B') $sql .="`NewQOH`,";
			                $sql .="`Prd`


			                )VALUES(";

			                $sql .="'".$alldata['StockLocation'][$i]."',";
		                	$sql .="'11',";

		        	        $sql .="'".$alldata['TransNo'][$i]."',";
			                $sql .="'".$alldata['StockID'][$i]."',";
			                $sql .="'".$alldata['StandardCost'][$i]."',";
			                $sql .="'".$alldata['TranDate'][$i]."',";
			                $sql .="'".$alldata['DebtorNo'][$i]."',";
		                	$sql .="'".$alldata['BranchCode'][$i]."',";
		        	        $sql .="'".$alldata['UnitPrice'][$i]."',";
		        	        $sql .="'".$alldata['QBTxnID'][$i]."',";
			                $sql .="'".$alldata['Quantity'][$i]."',";
			                if($alldata['MBflag'][$i]=='M' || $alldata['MBflag'][$i]=='B') $sql .="'".($alldata['OldQuantity'][$i] - $alldata['Quantity'][$i] )."',";
			                $sql .="'".$alldata['Period'][$i]."'";

			                $sql .=")";
		                	DB_Query($sql,$this->DB);

		                	// now update the LOCSTOCK quantities
					/*
		                	DB_Query("UPDATE `LocStock` SET
					`Quantity`='".floatval($alldata['OldQuantity'][$i] + $alldata['Quantity'][$i])."'
					WHERE
					`LocCode`='".$alldata['StockLocation'][$i]."' AND
					`StockID`='".$alldata['StockID'][$i]."'
					",$this->DB);
					*/
				}
		                // now check if assembly/kit get its components from BOm and post it to the stock moves
		                if($alldata['MBflag'][$i] == 'K' || $alldata['MBflag'][$i] == 'A'){
					// get all its components from the BOM and insert the stock moves
					$dummy=DB_Query("SELECT WorkCentreAdded FROM `BOM` WHERE `Parent`='".$alldata['StockID'][$i]."' AND `LocCode` ='".$alldata['LocCode'][$i]."' ORDER BY `WorkCentreAdded` ASC",$this->DB);
					$dummyres = DB_fetch_assoc($dummy);
					$WorkCentre = $dummyres['WorkCenterAdded'];
					$dummy=DB_Query("SELECT * FROM `BOM` WHERE `Parent`='".$alldata['StockID'][$i]."' AND `LocCode` ='".$alldata['StockLocation'][$i]."' AND `WorkCentreAdded`= '".$WorkCentre."'",$this->DB);
					while($dummyres = DB_fetch_assoc($dummy)){
						// Query to get the standard cost of the componets
						$dummy1 = DB_Query("SELECT * FROM `StockMaster` WHERE `StockID`='".$dummyres['Component']."'",$this->DB);
						$dummyr1 = DB_fetch_assoc($dummy1);
						$CompStdCost = $dummyr1['Labourcost'] + $dummyr1['Overheadcost'] + $dummyr1['Materialcost'];
					        // nsert a record for each component
				        	$sql = "INSERT INTO `StockMoves`(";

				                $sql .="
				                `LocCode`,
			        	        `Type`,
			        	        `Reference`,
		                		`TransNo`,
			        	        `StockID`,
				                `TranDate`,
				                `DebtorNo`,
				                `BranchCode`,
						`StandardCost`,
						`QBTxnID`,
		        	        	`Qty`,";
				                // QOH is only tracked iof buy or manfuctured
				                if($alldata['MBflag'][$i]=='M' || $alldata['MBflag'][$i]=='B') $sql .="`NewQOH`,";
				                $sql .="`Prd`


				                )VALUES(";

				                $sql .="'".$alldata['StockLocation'][$i]."',";
		        	        	$sql .="'11',";
		        	        	$sql .="'Assembly: ".$alldata['StockID'][$i]." Order: ".$alldata['OrderNo'][$i]."',";
		        		        $sql .="'".$alldata['TransNo'][$i]."',";
			                	$sql .="'".$dummyres['Component']."',";
				                $sql .="'".$alldata['TranDate'][$i]."',";
				                $sql .="'".$alldata['DebtorNo'][$i]."',";
			                	$sql .="'".$alldata['BranchCode'][$i]."',";
						$sql .="'".$CompStdCost."',";
			        	        $sql .="'".$alldata['QBTxnID'][$i]."',";
			        	        $sql .="'-".$dummyres['Quantity']."',";
			                	if($alldata['MBflag'][$i]=='M' || $alldata['MBflag'][$i]=='B') $sql .="'".($alldata['OldQuantity'][$i] - $alldata['Quantity'][$i] )."',";
				                $sql .="'".$alldata['Period'][$i]."'";

				                $sql .=")";
			                	DB_Query($sql,$this->DB);

			            	// now update the LOCSTOCK quantities
					/*
		        	        	DB_Query("UPDATE `LocStock` SET
						`Quantity`=`Quantity`+".floatval($dummyres['Quantity'])."
						WHERE
						`LocCode`='".$alldata['StockLocation'][$i]."' AND
						`StockID`='".$dummyres['Component']."'
						",$this->DB);
						*/
					}




		                }
		        }

		}
		$this->AddReport("StockMoves");
		// ----------- GLTRANS -------------------
			$this->TotalRepEntries=0;
			$this->TotalEntries=0;
			$this->TotalTrials=0;
			$Prev = '';
			//$FirstRound=True;
			for($i=0;$i<$total;$i++){
				// frist check if the transactions is there

				if($Prev == $alldata['QBTxnID'][$i] && $i!=0 ) $FirstRound=false;
				else $FirstRound=true;
				$Prev = $alldata['QBTxnID'][$i];
				if($FirstRound)$this->TotalTrials++;
				$dummy = DB_Query("SELECT * FROM `GLTrans` WHERE `QBTxnNumber` = '".$alldata['QBTxnID'][$i]."'",$this->DB);
				// if repeated and overwrite then delete them
				$Counter ='';

				if(DB_num_rows($dummy) && $FirstRound)$this->TotalRepEntries++;
				if($this->Overwrite && DB_num_rows($dummy) && $FirstRound){
					$dummyres = DB_fetch_assoc($dummy);
					$Counter = $dummyres['CounterIndex'];

					DB_Query("DELETE FROM `GLTrans` WHERE `QBTxnNumber` ='".$alldata['QBTxnID'][$i]."'",$this->DB);
				}
				// if new / repeeated and overwrite insert the new records
				if(($this->Overwrite && DB_num_rows($dummy)!= 0 ) || DB_num_rows($dummy) ==0 ){
		                        include_once('includes/GetSalesTransGLCodes.inc');
								
					// the check for  the oirginal code if the config allows to post to GL
					        if($FirstRound)$this->TotalEntries++;
					if ($CompanyData['GLLink_Stock']==1 AND $alldata['StandardCost'][$i] !=0){
					        // COGS Accounts

					        $sql = "INSERT INTO `GLTrans`(";

					        $sql .="
						`Type`,
						`TypeNo`,
						`QBTxnNumber`,
						`TranDate`,
						`PeriodNo`,
						`Amount`,
						`Narrative`,
						`Account`,
						`Posted`
						)VALUES(";

						$sql.="'11',";
						$sql.="'".$alldata['TransNo'][$i]."',";
						$sql.="'".$alldata['QBTxnID'][$i]."',";
						$sql.="'".$alldata['TranDate'][$i]."',";
						$sql.="'".$alldata['Period'][$i]."',";
						$sql.="'".-1 * $alldata['Amount'][$i]."',";
						$sql.="'".$alldata['DebtorNo'][$i]." - ".$alldata['StockID'][$i]." x ".$alldata['Quantity'][$i]." @ ".$alldata['UnitPrice'][$i]."',";
						$sql.="'".GetCOGSGLAccount($alldata['Area'][$i], $alldata['StockID'][$i], $alldata['SalesType'][$i], $this->DB)."',";
						$sql.="'1'";
						$sql.=");";
						DB_Query($sql,$this->DB);
						// now the sales post
						$xyz = GetStockGLCode($alldata['StockID'][$i],$this->DB);
						$sql = "INSERT INTO `GLTrans`(";

					        $sql .="
						`Type`,
						`TypeNo`,
						`QBTxnNumber`,
						`TranDate`,
						`PeriodNo`,
						`Amount`,
						`Narrative`,
						`Account`,
						`Posted`
						)VALUES(";

						$sql.="'11',";
						$sql.="'".$alldata['TransNo'][$i]."',";
						$sql.="'".$alldata['QBTxnID'][$i]."',";
						$sql.="'".$alldata['TranDate'][$i]."',";
						$sql.="'".$alldata['Period'][$i]."',";
						$sql.="'". ($alldata['Amount'][$i])."',";
						$sql.="'".$alldata['DebtorNo'][$i]." - ".$alldata['StockID'][$i]." x ".$alldata['Quantity'][$i]." @ ".$alldata['UnitPrice'][$i]."',";
						$sql.="'".$xyz[0]."',";
						$sql.="'1'";
						$sql.=");";
						DB_Query($sql,$this->DB);
					}
					// Sales posting
					if ($CompanyData['GLLink_Debtors']==1 && $alldata['UnitPrice'][$i] !=0){
						//Post sales transaction to GL credit sales
						$SalesGLAccounts = GetSalesGLAccount($alldata['Area'][$i], $alldata['StockID'][$i], $alldata['SalesType'][$i], $this->DB);

						$sql = "INSERT INTO `GLTrans`(";

					        $sql .="
						`Type`,
						`TypeNo`,
						`QBTxnNumber`,
						`TranDate`,
						`PeriodNo`,
						`Amount`,
						`Narrative`,
						`Account`,
						`Posted`
						)VALUES(";

						$sql.="'11',";
						$sql.="'".$alldata['TransNo'][$i]."',";
						$sql.="'".$alldata['QBTxnID'][$i]."',";
						$sql.="'".$alldata['TranDate'][$i]."',";
						$sql.="'".$alldata['Period'][$i]."',";
						$sql.="'". ($alldata['Amount'][$i])."',";
						$sql.="'".$alldata['DebtorNo'][$i]." - ".$alldata['StockID'][$i]." x ".$alldata['Quantity'][$i]." @ ".$alldata['UnitPrice'][$i]."',";
						$sql.="'".$SalesGLAccounts['SalesGLCode']."',";
						$sql.="'1'";
						$sql.=");";
						
						// now post the discount if there is one
						if($alldata['Discount'][$i] > 0){
							$sql .= "INSERT INTO `GLTrans`(";

						        $sql .="
							`Type`,
							`TypeNo`,
							`QBTxnNumber`,
							`TranDate`,
							`PeriodNo`,
							`Amount`,
							`Narrative`,
							`Account`,
							`Posted`
							)VALUES(";

							$sql.="'11',";
							$sql.="'".$alldata['TransNo'][$i]."',";
							$sql.="'".$alldata['QBTxnID'][$i]."',";
							$sql.="'".$alldata['TranDate'][$i]."',";
							$sql.="'".$alldata['Period'][$i]."',";
							$sql.="'". -1 * ($alldata['Amount'][$i] * $alldata['Discount'][$i])."',";
							$sql.="'".$alldata['DebtorNo'][$i]." - ".$alldata['StockID'][$i]." x ".$alldata['Quantity'][$i]." @ ".$alldata['UnitPrice'][$i]."',";
							$sql.="'".$SalesGLAccounts['SalesGLCode']."',";
							$sql.="'1'";
							$sql.=");";
						}
						DB_Query($sql,$this->DB);
					}


				}

		}
		$this->AddReport("GLTrans");
		// ------------ SysTypes --------------------
		// Update it with the latest value for the trans no if we are writting a new invoice
			$MaxTransNo = $alldata['TransNo'][count($alldata['TransNo'])-1];

		        DB_Query("UPDATE `SysTypes` SET `TypeNo`='".$MaxTransNo."' WHERE `TypeID`='11'",$this->DB);

		return true;
	}

	// ######################
	// Adjust XML entries
	// ######################

	function AdjustEntry($x,$habekha=0){
		//`$x = @html_entity_decode($x);
		$x = str_replace("&amp;","&",$x);
		$x = str_replace("&apos;","`",$x);
		$x = str_replace("&#039;","`",$x);
		$x = str_replace("%","_",$x);
		$x = str_replace("'","`",$x);
		$x = str_replace("\\","",$x);
		$x = str_replace("<","&lt;",$x);
		$x = str_replace(">","&gt;",$x);
		$x = str_replace("\r","",$x);
		$x = nl2br($x);
		$x = str_replace("&quot;",'`',$x);
		if($habekha){
			$x = str_replace("-","_",$x);
			$x = str_replace("+","_",$x);
			$x = str_replace("&","_",$x);
			$x = str_replace(" ","_",$x);
			while(strstr($x,"__")) $x = str_replace("__","_",$x);
		}
		return $x;
	}

	// ######################
	// Get Unique id name
	// ######################

	function GetID($desiredfield,$desired,$FullNamefield,$FullNamevalue,$table,$maxlength,$array=''){
		// $desired = [FieldName] desired value
		// $FullName = [FieldName] desired Full Name
	/*	$temp = array_keys($desired);
		$desiredfield = $temp[0];

		$temp = array_keys($FullName);
		$FullNamefield = $temp[0];
	*/
		if(!is_array($array)) $array = array();	// case array is not set, that's why call be ref is not used

		$desiredvalue = trim(substr($this->AdjustEntry($desired),0,$maxlength));
		//print("desired: ".$desiredvalue." , FullName: ".$FullNamevalue."<br>");
		//$FullNamevalue = $FullName[0];
	//	print("max length: ".$maxlength."<br>");
	//	print("Desired: ".$desired."<br>");
	//	print("Desired value: ".$desiredvalue."<br>");
		$dummy = DB_Query("SELECT `".$desiredfield."`,`".$FullNamefield."` FROM `".$table."` WHERE `".$desiredfield."` = '".addslashes($desiredvalue)."'",$this->DB);
		// 1) first check if the unique "desired" name is there AFTER TRIMING TO THE MAXLENGTH
		if(DB_num_rows($dummy) != 0 || in_array($desiredvalue,$array) ){
			$dummyres = DB_fetch_row($dummy);
		// 	1.1) Same FullName , return the desired value
			if($dummyres[1] == $FullNamevalue) return $desiredvalue;
			//print("ggg");
// 	1.2) Not same FullName , trim the desired name to the max length -2 chars , Jump to (2) after changing the opriginal value of desired

			$i =1;
			do{
				$desiredvalue = substr($desiredvalue,0,($maxlength)-2);

				if(strlen($i) <2) $temp = "0".$i;
				$desiredvalue.=$temp;
				$kool = DB_Query("SELECT `".$desiredfield."`,`".$FullNamefield."` FROM `".$table."` WHERE `".$desiredfield."` = '".addslashes($desiredvalue)."'",$this->DB);
				$koolres = DB_fetch_row($kool);
			//rint("FullNamge : ".$koolres[1].", target: ".$FullNamevalue."<br>");
				if($koolres[1] == $this->AdjustEntry($FullNamevalue)) return $desiredvalue;
				$i++;
			}while(DB_num_rows($kool) != 0 || in_array($desiredvalue,$array));
			$des = DB_fetch_row($kool);
			if(DB_num_rows($kool)==0) $des[0] = $desiredvalue;
//print("des: ".$des[0]);
			return $des[0];

		}
		// 2) Not Same Name , return all as the same
		return $desiredvalue;

	}
}

function WamaMerge(&$ar1,&$ar2,$depth){
	
	// first get max index in $ar1
	$maxindex = count($ar1);
	$totalcount = 0;
	if(is_array($ar2))foreach($ar2 as $ar3){
			// get the depth
		
		foreach($depth as $level){
			$ar3 = $ar3[$level];
		}	
		
		// second start reading from the ar2 using For($i = ..count(..) 
		$maxInAdded = count($ar3);
		// 	use $i+count to add to the first
		for($i = 0;$i<$maxInAdded;$i++){
			$ar1[$i+$maxindex+$totalcount] = $ar3[$i];
			$totalcount++;
		}
		
	}

}

?>	

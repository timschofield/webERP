<?php

include('includes/session.php');

$Title = __('Process EDI Orders');
$ViewTopic = 'EDI';
$BookMark = '';

include('includes/header.php');
include('includes/SQL_CommonFunctions.php'); // need for EDITransNo
include('includes/DefineCartClass.php');
require_once('includes/MiscFunctions.php');

/*The logic outline is this ....

Make an array of the format of the ORDER message from the table EDI_ORDERS_Segs

Get the list of files in EDI_Incoming_Orders - work through each one as follows

Read in the flat file one line at a time

Compare the SegTag in the flat file with the expected SegTag from EDI_ORDERS_Segs

parse the data in the line of text from the flat file to enable the order to be created

Create a html email to the customer service person based on the location
of the customer doing the ordering and where it would be best to pick the order from

Read the next line of the flat file ...

If the order processed ok then move the file to processed and go on to next file.

Order segements:
          ALI Additional information
          APR Additional price information
          BGM Beginning of message
          CAV Characteristic value
          CCI Characteristic/class id
          CNT Control total
          COM Communication contact
          CTA Contact information
          CUX Currencies
          DGS Dangerous goods
          DOC Document/message details
          DTM Date/time/period
          EQD Equipment details
          FII Financial institution information
          FTX Free text
          GIN Goods identity number
          GIR Related identification numbers
       X  GIS General indicator
          HAN Handling instructions
          IMD Item description
          LIN Line item
          LOC Place/location identification
          MEA Measurements
          MOA Monetary amount
          NAD Name and address
          PAC Package
          PAI Payment instructions
       X  PAT Payment terms basis
          PCD Percentage details
          PCI Package identification
          PIA Additional product id
          PRI Price details
          QTY Quantity
          QVR Quantity variances
          RCS Requirements and conditions
          RFF Reference
          RJL Accounting journal identification
          RNG Range details
          RTE Rate details
          SCC Scheduling conditions
          STG Stages
          TAX Duty/tax/fee details
          TDT Details of transport
          TOD Terms of delivery or transport
          UNH Message header
          UNS Section control
          UNT Message trailer
----------------------------------------------------------------------*/

/*Read in the EANCOM Order Segments for the current seg group from the segments table */

$SQL = "SELECT id, segtag, maxoccur, seggroup FROM edi_orders_segs";
$OrderSeg = DB_query($SQL);
$i=0;
$Seg = array();

while ($SegRow=DB_fetch_array($OrderSeg)){
	$Seg[$i] = array('SegTag'=>$SegRow['segtag'], 'MaxOccur'=>$SegRow['maxoccur'], 'SegGroup'=>$SegRow['seggroup']);
	$i++;
}

$TotalNoOfSegments = $i-1;

$ediordersdir = $_SERVER['DOCUMENT_ROOT'] . $RootPath . '/' . $_SESSION['EDI_Incoming_Orders'];
echo '<br />' . $ediOrdersdir;
if (!is_dir($ediordersdir)) {
	error_log("EDI orders directory error " . $ediordersdir, 0); // php logging
	exit();

}
//	error_log("EDI orders directory " . $ediordersdir , 0);

/*get the list of files in the incoming orders directory - from config.php */
//$DirHandle = opendir($_SERVER['DOCUMENT_ROOT'] . '/' . $RootPath . '/' . $_SESSION['EDI_Incoming_Orders']);
$DirHandle = opendir($ediordersdir);

//if $DirHandle = false {
//error_log("EDI orders directory error", 0);
//}

while (false !== ($OrderFile=readdir($DirHandle))){ /*there are files in the incoming orders dir */

	$TryNextFile = False;

    if ($OrderFile == "." || $OrderFile == "..") {
		continue;
	}

//	  error_log("EDI orders file " . $OrderFile , 0);

	echo '<br />' . $OrderFile;

	/*Counter that keeps track of the array pointer for the 1st seg in the current seg group */
	$FirstSegInGrp =0;
	$SegGroup =0;

//	$fp = fopen($_SERVER['DOCUMENT_ROOT'] .'/$RootPath/'.$_SESSION['EDI_Incoming_Orders'].'/'.$OrderFile,'r');
	$fp = fopen($ediordersdir.'/'.$OrderFile,'r');

	$SegID = 0;
	$SegCounter =0;
	$SegTag='';
	$LastSeg = 0;
	$FirstSegInGroup = 0;
	$EmailText =''; /*Text of email to send to customer service person */
	$CreateOrder = True; /*Assume that we are to create a sales order in the system for the message read */
    $LinCount = 0; // LIN segments
    $ErrorCount=0;
	$Order = new cart;

	while ($LineText = fgets($fp) AND $TryNextFile != True){ /* get each line of the order file */

		$LineText = StripTrailingComma($LineText);
		echo '<br />' . $LineText;

		if ($SegTag != mb_substr($LineText,0,3)){
			$SegCounter=1;
			$SegTag = mb_substr($LineText,0,3);
		} else {
			$SegCounter++;
			if ($SegCounter > $Seg[$SegID]['MaxOccur']){
				$EmailText = $EmailText . "\n" . __('The EANCOM Standard only allows for') . ' ' . $Seg[$SegID]['MaxOccur'] . ' ' .__('occurrences of the segment') . ' ' . $Seg[$SegID]['SegTag'] . ' ' . __('this is the') . ' ' . $SegCounter . ' ' . __('occurrence') .  '<br />' . __('The segment line read as follows') . ':<br />' . $LineText;
			}
		}

/* Go through segments in the order message array in sequence looking for matching SegTags

*/
		while ($SegTag != $Seg[$SegID]['SegTag'] AND $SegID < $TotalNoOfSegments) {

			$SegID++; /*Move to the next Seg in the order message */
			$LastSeg = $SegID; /*Remember the last segid moved to */

			echo "\n" . __('Segment Group') . ' = ' . $Seg[$SegID]['SegGroup'] . ' ' . __('Max Occurrences of Segment') . ' = ' . $Seg[$SegID]['MaxOccur'] . ' ' . __('No occurrences so far') . ' = ' . $SegCounter;

			if ($Seg[$SegID]['SegGroup'] != $SegGroup AND $Seg[$SegID]['MaxOccur'] > $SegCounter){ /*moved to a new seg group  but could be more segment groups*/
				$SegID = $FirstSegInGroup; /*Try going back to first seg in the group */
				if ($SegTag != $Seg[$SegID]['SegTag']){ /*still no match - must be into new seg group */
					$SegID = $LastSeg;
					$FirstSegInGroup = $SegID;
				} else {
					$SegGroup = $Seg[$SegID]['SegGroup'];
				}
			}
		}

		if ($SegTag != $Seg[$SegID]['SegTag']){

			$EmailText .= "\n" . __('ERROR') . ': ' . __('Unable to identify segment tag') . ' ' . $SegTag . ' ' . __('from the message line') . '<br />' . $LineText . '<br /><font color=RED><b>' . __('This message processing has been aborted and separate advice will be required from the customer to obtain details of the order') . '<b></font>';

			$TryNextFile = True;
		}

		echo '<br />' . __('The segment tag') . ' ' . $SegTag . ' ' . __('is being processed');

		switch ($SegTag){
			case 'UNB';
				$UNB = explode ('+',mb_substr($LineText,4));
				if (mb_substr($UNB[6],0,6)!='ORDERS'){
					$EmailText .= "\n" . __('This message is not an edi order');
					$TryNextFile = True;
				}

			break;

			case 'UNH':
				$UNH_elements = explode ('+',mb_substr($LineText,4));
				$UNH2 = explode (':',$UNH_elements[1]);
				$EdiMsgVer = $UNH2[1] . '.' . $UNH2[2] ; // fex D.00A
//				echo '<br />' . $EdiMsgVer;
				$Order->Comments .= __('Customer EDI Ref') . ': ' . $UNH_elements[0];
				$EmailText .= "\n" . __('EDI Message Ref') . ': ' . $UNH_elements[0];
				if (mb_substr($UNH_elements[1],0,6)!='ORDERS'){
					$EmailText .= "\n" . __('This message is not an EDI order');
					$TryNextFile = True;
				}
				break;
			case 'BGM':
				$BGM_elements = explode('+',mb_substr($LineText,4));
				$BGM_C002 = explode(':',$BGM_elements[0]);
				switch ($BGM_C002[0]){
					case '220':
						$EmailText .= "\n" . __('This message is a standard order');
						break;
					case '221':
						$EmailText .= "\n" . __('This message is a blanket order');
						$Order->Comments .= "\n" . __('blanket order');
						break;
					case '224':
						$EmailText .= "\n\n" . __('This order is URGENT') . '</font>';
						$Order->Comments .= "\n" . __('URGENT ORDER');
						break;
					case '226':
						$EmailText .= "\n" . __('Call off order');
						$Order->Comments .= "\n" . __('Call Off Order');
						break;
					case '227':
						$EmailText .= "\n" . __('Consignment order');
						$Order->Comments .= "\n" . __('Consignment order');
						break;
					case '22E':
						$EmailText .= "\n" . __('Manufacturer raised order');
						$Order->Comments .= "\n" . __('Manufacturer raised order');
						break;
					case '258':
						$EmailText .= "\n" . __('Standing order');
						$Order->Comments .= "\n" .__('Standing order');
						break;
					case '237':
						$EmailText .= "\n" . __('Cross docking services order');
						$Order->Comments .= "\n" . __('Cross docking services order');
						break;
					case '400':
						$EmailText .= "\n" . __('Exceptional Order');
						$Order->Comments .= "\n" . __('Exceptional Order');
						break;
					case '401':
						$EmailText .= "\n" . __('Trans-shipment order');
						$Order->Comments .= "\n" . __('Trans-shipment order');
						break;
					case '402':
						$EmailText .= "\n" . __('Cross docking order');
						$Order->Comments .= "\n" . __('Cross docking order');
						break;

				} /*end switch for type of order */
				if (isset($BGM_elements[1])){
					echo '<br />echo BGM_elements[1] ' .$BGM_elements[1];
					$BGM_C106 = explode(':',$BGM_elements[1]);
					$Order->CustRef = $BGM_C106[0];
					$EmailText .= "\n" . __('Customers order ref') . ': ' . $BGM_C106[0];
				}
				if (isset($BGM_elements[2])){
					echo '<br />echo BGM_elements[2] ' .$BGM_elements[2];
					$BGM_1225 = explode(':',$BGM_elements[2]);
					$MsgFunction = $BGM_1225[0];

					switch ($MsgFunction){
						case '5':
							$EmailText .= "\n\n" . __('REPLACEMENT order') . ' - ' . __('MUST DELETE THE ORIGINAL ORDER MANUALLY');
							break;
						case '6':
							$EmailText .= "\n" . __('Confirmation of previously sent order');
							break;
						case '7':
							$EmailText .= "\n\n" . __('DUPLICATE order DELETE ORIGINAL ORDER MANUALLY');
							break;
						case '16':
							$CreateOrder = False; /*Dont create order in system */
							$EmailText .= "\n\n" . __('Proposed order only no order created in web-ERP');
							break;
						case '31':
							$CreateOrder = False; /*Dont create order in system */
							$EmailText .= "\n" . __('COPY order only no order will be created in web-ERP');
							break;
						case '42':
							$CreateOrder = False; /*Dont create order in system */
							$EmailText .= "\n" . __('Confirmation of order') . ' - ' . __('not created in web-ERP');
							break;
						case '46':
							$CreateOrder = False; /*Dont create order in system */
							$EmailText .= "\n" . __('Provisional order only') . ' - ' . __('not created in web-ERP');
							break;
					}

					if (isset($BGM_1225[1])){
						$ResponseCode = $BGM_1225[1];
						echo '<br />' . __('Response Code') . ': ' . $ResponseCode;
						switch ($ResponseCode) {
							case 'AC':
								$EmailText .= "\n" . __('Please acknowledge to customer with detail and changes made to the order');
								break;
							case 'AB':
								$EmailText .= "\n" . __('Please acknowledge to customer the receipt of message');
								break;
							case 'AI':
								$EmailText .= "\n" . __('Please acknowledge to customer any changes to the order');
								break;
							case 'NA':
								$EmailText .= "\n" . __('No acknowledgement to customer is required');
								break;
						}
					}
				}
				break;
			case 'DTM':
				/*explode into an arrage all items delimited by the : - only after the + */
				$DTM_C507 = explode(':',mb_substr($LineText,4));
				$LocalFormatDate = ConvertEDIDate($DTM_C507[1],$DTM_C507[2]);
				switch ($DTM_C507[0]){
					case '2': /*Delivery date */
						$Order->DeliveryDate = $LocalFormatDate;
						$EmailText .= "\n" . __('Delivery date') . ' ' . $Order->DeliveryDate;
						break;
					case '10': /*shipment date requested */
					case '11': /*dispatch date */
					case 'X14': /*Reguested delivery week commencing EAN code */
					case '64': /*Earliest delivery date */
					case '8': /* Order received date/time */
					case '4': /*orig order date */
						$Order->Orig_OrderDate = $LocalFormatDate;
						$Order->OrdDate = $LocalFormatDate;
						$EmailText .= "\n" . __('Orig,.order date') . ' ' . $Order->Orig_OrderDate;
						break;
					case '69': /*Promised delivery date */
						$Order->DeliveryDate = $LocalFormatDate;
						$EmailText .= "\n" . __('Promised delivery date') . ' ' . $Order->DeliveryDate;
						break;
					case '15': /*promotion start date */
						$EmailText .= "\n" . __('Promotion start date') . ' ' . $LocalFormatDate;
						break;
					case '37': /*ship not before */
						$EmailText .= "\n" . __('Do NOT ship before') . ' ' . $LocalFormatDate;
						break;
					case '38': /*ship not later than */
					case '61': /*Cancel if not delivered by this date */
					case '63': /*Latest delivery date */
					case '393': /*Cancel if not shipped by this date */
						$EmailText .= "\n" . __('Cancel order if not dispatched before') . ' ' . $LocalFormatDate;
						break;
					case '137': /*Order date */
						$Order->Orig_OrderDate = $LocalFormatDate;
						$Order->OrdDate = $LocalFormatDate;
						$EmailText .= "\n" . __('Order date') . ':  ' . $LocalFormatDate;
						break;
					case '171': /*A date relating to a RFF seg */
						/*This DTM segment follows a RFF seg so $RFF will be set
						use the RFF seg to determine if the date refers to the
						order */
						$EmailText .= "\n" . __('dated') . ' ' . $LocalFormatDate;
						if ($SegGroup == 1){
							$Order->Comments .= ' ' . __('dated') . ': ' . $LocalFormatDate;
						}
						break;
					case '200': /*Pickup collection date/time */
						$EmailText .= "\n\n" . __('Pickup date') . ':  ' . $LocalFormatDate;
						$Order->DeliveryDate = $LocalFormatDate;
						break;
					case '263': /*Invoicing period */
						$EmailText .= "\n" . __('Invoice period') . ':  ' . $LocalFormatDate;
						break;
					case '273': /*Validity period */
						$EmailText .= "\n" . __('Valid period') . ':  ' . $LocalFormatDate;
						break;
					case '282': /*Confirmation date lead time */
						$EmailText .= "\n" . __('Confirmation of date lead time') . ' ' . $LocalFormatDate;
						break;
					case '132': /*depature date/time */
						$EmailText .= "\n" . __('Departure date') . ': ' . $LocalFormatDate;
						break;
					case '133': /*arrival date/time */
						$EmailText .= "\n" . __('Arrival date') . ': ' . $LocalFormatDate;
						break;
					case '134': /*Rate of exchange date/time */
						$EmailText .= "\n" . __('Rate of exchange date / time') . ': ' . $LocalFormatDate;
						break;
				}
				break;
			case 'PAI':
				/*explode into an array all items delimited by the : - only after the + */
				$PAI_C534 = explode(':',mb_substr($LineText,4));
				if ($PAI_C534[0]=='1'){
					$EmailText .= "\n" . __('Payment will be effected by a direct payment for this order');
				} elseif($PAI_C534[0]=='OA'){
					$EmailText .= "\n" . __('This order to be settled in accordance with the normal account trading terms');
				}
				if ($PAI_C534[1]=='20'){
					$EmailText .= "\n" . __('The goods on this order') . ' - ' . __('once delivered') . ' - ' . __('will be held as security for the payment');
				}
				if ($PAI_C534[2]=='42'){
					$EmailText .= "\n" . __('Payment will be effected to bank account');
				} elseif ($PAI_C534[2]=='60'){
					$EmailText .= "\n" . __('Payment will be effected by promissory note');
				} elseif ($PAI_C534[2]=='40'){
					$EmailText .= "\n" . __('Payment will be effected by a bill drawn by the creditor on the debtor');
				} elseif ($PAI_C534[2]=='10E'){
					$EmailText .= "\n" . __('Payment terms are defined in the Commercial Account Summary Section');
				}
				if (isset($PAI_C534[5])){
					if ($PAI_C534[5]=='2')
					$EmailText .= "\n" . __('Payment will be posted through the ordinary mail system');
				}
				break;
			case 'ALI':
				$ALI = explode('+',mb_substr($LineText,4));
				if (mb_strlen($ALI[0])>1){
					$EmailText .= "\n" . __('Goods of origin') . ' ' . $ALI[0];
				}
				if (mb_strlen($ALI[1])>1){
					$EmailText .= "\n" . __('Duty regime code') . ' ' . $ALI[1];
				}
				switch ($ALI[2]){
					case '136':
						$EmailText .= "\n" . __('Buying group conditions apply');
						break;
					case '137':
						$EmailText .= "\n\n" . __('Cancel the order if complete delivery is not possible on the requested date or time');
						break;
					case '73E':
						$EmailText .= "\n" . __('Delivery subject to final authorisation');
						break;
					case '142':
						$EmailText .= "\n" . __('Invoiced but not replenished');
						break;
					case '143':
						$EmailText .= "\n" . __('Replenished but not invoiced');
						break;
					case '144':
						$EmailText .= "\n" . __('Deliver Full order');
						break;
				}
				break;
			case 'FTX':
				$FTX = explode('+',mb_substr($LineText,4));
				/*agreed coded text is not catered for ... yet
				only free form text */
				if (mb_strlen($FTX[3])>5){
					$FTX_C108=explode(':',$FTX[3]);
					$Order->Comments .= $FTX_C108[0] . " " . $FTX_C108[1] . ' ' . $FTX_C108[2] . ' ' . $FTX_C108[3] . ' ' . $FTX_C108[4];
					$EmailText .= "\n" . $FTX_C108[0] . ' ' . $FTX_C108[1] . ' ' . $FTX_C108[2] . ' ' . $FTX_C108[3] . ' ' . $FTX_C108[4] . ' ';
				}
				break;
			case 'RFF':
				$RFF = explode(':',mb_substr($LineText,4));
				switch ($RFF[0]){
					case 'AE':
						$MsgText = "\n" . __('Authorisation for expense no') . ' ' . $RFF[1];
						break;
					case 'BO':
						$MsgText =  "\n" . __('Blanket Order') . ' # ' . $RFF[1];
						break;
					case 'CR':
						$Order->CustRef = $RFF[1];
						$MsgText =  "\n" . __('Customer Ref') . ' # ' . $RFF[1];
						break;
					case 'CT':
						$MsgText =  "\n" . __('Contract'). ' # ' . $RFF[1];
						break;
					case 'IP':
						$MsgText =  "\n" . __('Import Licence') . ' # ' . $RFF[1];
						break;
					case 'ON':
						$Order->CustRef = $RFF[1];
						$MsgText =  "\n" . __('Buyer order') . ' # ' . $RFF[1];
						break;
					case 'PD':
						$MsgText =  "\n" . __('Promo deal') . ' # ' . $RFF[1];
						break;
					case 'PL':
						$MsgText =  "\n" . __('Price List') . ' # ' . $RFF[1];
						break;
					case 'UC':
						$MsgText =  "\n" . __('Ultimate customer ref') . ' ' . $RFF[1];
						break;
					case 'VN':
						$MsgText =  "\n" . __('Supplier Order') . ' # ' . $RFF[1];
						break;
					case 'AKO':
						$MsgText =  "\n" . __('Action auth') . ' # ' . $RFF[1];
						break;
					case 'ANJ':
						$MsgText =  "\n" . __('Authorisation') . ' # ' . $RFF[1];
						break;
					case 'VA':
					// VA    VAT registration number
						$MsgText =  "\n" . __('VAT registration number') . ' # ' . $RFF[1];
						break;
				}
				if ($SegGroup == 1){
					$Order->Comments .= $MsgText;
				}
				$EmailText .= $MsgText;
				break;
			case 'NAD':
				$NAD = explode('+',mb_substr($LineText,4));
				$NAD_C082 = explode(':', $NAD[1]);
				$NAD_C058 = explode(':', $NAD[2]); /*Not used according to MIG */
				$NAD_C080 = explode(':', $NAD[3]);
				$NAD_C059 = explode(':', $NAD[4]);
				switch ($NAD[0]){
					case 'IV': /* This Name and address detail is that of the party to be invoiced */
						/*Look up the EAN Code given $NAD[1] for the buyer */
						if ($NAD_C082[2] ==9){
						/*if NAD_C082[2] must = 9 then NAD_C082[0] is the EAN Intnat Article Numbering Assocn code of the customer - look up the customer by EDIReference*/
							$InvoiceeResult = DB_query("SELECT debtorno FROM debtorsmaster WHERE edireference='" . $NAD_C082[0] . "' AND ediorders=1");
							if (DB_num_rows($InvoiceeResult)!=1){
								$EmailText .= "\n" . __('The Buyer reference was specified as an EAN International Article Numbering Association code') . '. ' . __('Unfortunately the field EDIReference of any of the customers currently set up to receive EDI orders does not match with the code') . ' ' . $NAD_C082[0] . ' ' . __('used in this message') . '. ' . __('So that is the end of the road for this message');
								$TryNextFile = True; /* Look for other EDI msgs */
								$CreateOrder = False; /*Dont create order in system */
							} else {
								$CustRow = DB_fetch_array($InvoiceeResult);
								$Order->DebtorNo = $CustRow['debtorno'];
							}
							break;
						}
						if (mb_strlen($NAD_C080[0])>0){
							$Order->CustomerName = $NAD_C080[0];
						}
						break;
					case 'SU':
						/*Supplier party details. This should be our EAN IANA number if not the message is not for us!! */
//					  error_log("SU/EDIReference " . $_SESSION['EDIReference'] , 0);
						if ($NAD_C082[0]!= $_SESSION['EDIReference']){
							/* $_SESSION['EDIReference'] is set in config.php as our EDIReference it should be our EAN International Article Numbering Association code */
							$EmailText .= "\n" . __('The supplier reference was specified as an EAN International Article Numbering Association code') . '. ' . __('Unfortunately the company EDIReference') . ' - ' . $_SESSION['EDIReference']  . ' ' . __('does not match with the code') . ' ' . $NAD_C082[0] . ' ' . __('used in this message') . '. ' . __('This implies that the EDI message is for some other supplier') . '. ' . __('No further processing will be done');
							$TryNextFile = True; /* Look for other EDI msgs */
							$CreateOrder = False; /* Don't create order in system */						}
						break;
					case 'DP':
						/*Delivery Party - get the address and name etc */
						/*Snag here - how do I figure out what branch to charge */
						if (mb_strlen($NAD_C080[0])>0){
							$Order->DeliverTo = $NAD_C080[0];
						}
						if (mb_strlen($NAD_C059[0])>0){
							$Order->DelAdd1 = $NAD_C059[0];
							$Order->DelAdd2 = $NAD_C059[1];
							$Order->DelAdd3 = $NAD_C059[2];
							$Order->DelAdd4 = $NAD_C059[4];
							$Order->DelAdd5 = $NAD_C059[5];
							$Order->DelAdd6 = $NAD_C059[6];
						}
						break;
					case 'SN':
						/*Store Number - get the branch details from the store number - snag here too cos need to ensure got the Customer detail first before try looking up its branches */
						$BranchResult = DB_query("SELECT branchcode,
														brname,
														braddress1,
														braddress2,
														braddress3,
														braddress4,
														braddress5,
														braddress6,
														contactname,
														defaultlocation,
														phoneno,
														email
												FROM custbranch INNER JOIN debtorsmaster ON custbranch.debtorno = custbranch.debtorno WHERE custbranchcode='" . $NAD_C082[0] . "' AND custbranch.debtorno='" . $Order->DebtorNo . "' AND debtorsmaster.ediorders=1");
						if (DB_num_rows($BranchResult)!=1){
							$EmailText .= "\n" . __('The Store number was specified as') . ' ' . $NAD_C082[0] . ' ' . __('Unfortunately there are either no branches of customer code') . ' ' . $Order->DebtorNo . ' ' .__('or several that match this store number') . '. ' . __('This order could not be processed further');
							$TryNextFile = True; /* Look for other EDI msgs */
							$CreateOrder = False; /*Dont create order in system */
						} else {
							$BranchRow = DB_fetch_array($BranchResult);
							$Order->BranchCode = $BranchRow['branchcode'];
							$Order->DeliverTo = $BranchRow['brname'];
							$Order->DelAdd1 = $BranchRow['braddress1'];
							$Order->DelAdd2 = $BranchRow['braddress2'];
							$Order->DelAdd3 = $BranchRow['braddress3'];
							$Order->DelAdd4 = $BranchRow['braddress4'];
							$Order->DelAdd5 = $BranchRow['braddress5'];
							$Order->DelAdd6 = $BranchRow['braddress6'];
							$Order->PhoneNo = $BranchRow['phoneno'];
							$Order->Email = $BranchRow['email'];
							$Order->Location = $BranchRow['defaultlocation'];
						}
						break;
					case 'BY':
					//BY    Buyer
					//Party to whom merchandise and/or service is sold.
//					NAD+BY+1234567890123::9++Buyer Company Ltd+123 Buyer St+Citytown++12345+DE'
						/* The buyer details - don't think we care about this */
						$ByResult = DB_query("SELECT  name,
													 address1,
													 address2,
													 address3,
													 address4,
													 address5,
													 address6,
													 salestype,
													 edireference,
													 currcode,
													 taxref,
													 custbranch.branchcode,
 													 custbranch.debtorno,
													 custbranch.phoneno,
													 custbranch.email,
													 custbranch.defaultlocation,
													 custbranch.defaultshipvia
												FROM debtorsmaster INNER JOIN custbranch ON debtorsmaster.debtorno = custbranch.debtorno WHERE debtorsmaster.edireference='" . $NAD_C082[0] . "' AND debtorsmaster.ediorders=1");

					 if (DB_num_rows($ByResult)!=1){
							$EmailText .= "\n" . __('The buyer ediref code was specified as') . ' ' . $NAD_C082[0] . ' ' . __('Unfortunately there are either no branches of customer code') . ' ' . __('or several that match this ediref code. This order could not be processed further');
							$TryNextFile = True; /* Look for other EDI msgs */
							$CreateOrder = False; /* Dont create order in system */
					} else {
							$ByRow = DB_fetch_array($ByResult);
							$Order->BranchCode = $ByRow['branchcode'];
							$Order->DebtorNo = $ByRow['debtorno'];
							$Order->DeliverTo = $ByRow['name'];
							$Order->DelAdd1 = $ByRow['address1'];
							$Order->DelAdd2 = $ByRow['address2'];
							$Order->DelAdd3 = $ByRow['address3'];
							$Order->DelAdd4 = $ByRow['address4'];
							$Order->DelAdd5 = $BranchRow['address5'];
							$Order->DelAdd6 = $ByRow['address6'];
							$Order->PhoneNo = $ByRow['phoneno'];
							$Order->Email = $ByRow['email'];
							$Order->Location = $ByRow['defaultlocation'];
							$Order->ShipVia = $ByRow['defaultshipvia'];

			//			error_log("nad/by: " . DB_num_rows($ByResult) . ' '  . $NAD_C082[0] . ' ' . $ByRow['email'] , 0); // php logging
						}
						break;
					case 'CO':
						/* The coporate office details - don't think we care about this either*/
						break;
					case 'SR':
						/* Our (the suppliers) representative - don't think we care about this either*/
						break;
					case 'WH':
						/* The warehouse keeper details - don't think we care about this either*/
						break;
				}
				break; /*end of NAD segment */


			// added more segments ......

			// CUX+2:GBP:9+3:EUR:4+1.67
			// CUX+2:EUR:9'
			case 'CUX':
				$CUX = explode(':',mb_substr($LineText,4));
				// $Order->Currency = $CUX[1];
				$EmailText .= "\n" . __('Currency') . ' ' . $CUX[1];
		    	break;

			// TDT+20++30+31'
			case 'TDT':
				$EmailText .= "\n" . __('Details of transport');
				break;

		     // TOD+3++CIF:2E:9'
			case 'TOD':
			// Terms of delivery or transport
				$EmailText .= "\n" . __('Terms of delivery or transport');

			    break;

			// LOC+1+BE-BRU
			case 'LOC':
//			Place/location identification
					/*
					4     Goods receipt place
          			   Place at which the goods have been received.
  		  		   5     Place of departure
 		             (3214) Port, airport or other location from which a
		              means of transport or transport equipment is scheduled
     		         to depart or has departed.
		          7     Place of delivery
                   (3246) Place to which the goods are to be finally
                   delivered under transport contract terms (operational
                   term).
					 */
					$LOC = explode('+',mb_substr($LineText,4));
					switch ($LOC[2]){
						case '1': /*Place of terms of delivery*/
							$EmailText .= "\n" . __('Place of terms of delivery (LOC/1)') . ': ' . $LOC[2];
							break;
						case '7': /*Place/location identification*/
							$EmailText .= "\n" . __('Place of terms of delivery (LOC/7)') . ': ' . $LOC[2];
							break;

					}
					break;

		   /* -----------------------------------------------------------------
		     order lines....
			 Segment group 28: LIN-PIA-IMD-MEA-QTY-PCD-ALI-DTM-MOA-GIS-GIN-
                         GIR-QVR-DOC-PAI-FTX-SG29-SG30-SG32-SG33-SG34-
                         SG37-SG38-SG39-SG43-SG49-SG51-SG52-SG53-SG55-
                         SG56-SG58
				A group of segments providing details of the individual ordered
				items. This segment group may be repeated to give sub-line details.
		   -----------------------------------------------------------------------*/
			case 'LIN':
				//echo '<br />' . 'Line# ' . $Order->ItemsOrdered;

					if ($LinCount > 0) {
						$UpdateDB='NO';
						// add prev processed line to cart;
				       // echo '<br />' . 'LinCount ' . $LinCount;
						AddLinToChart ($Order,$StockID,$Qty,$Descr,$LongDescr,$Price,$Disc,$UOM,$Volume,$Weight);
					}

					$LIN = explode('+',mb_substr($LineText,4));
					$LIN2 = explode(':', $LIN[2]);
					$EmailText .= "\n" . __('Line item') . ': ' . $LIN[0] . ' ' . $LIN2[0] ;
					$LinCount++;
					$Order->LineCounter = $LinCount;
					// init order line data ffu....
					$UpdateDB='NO';
					$StockID=$LIN2[0];
					$Descr='';
					$LongDescr='';
					$Qty=0;
					$Price=1;
					$Disc=0;
					$UOM=''; // units
					$Volume=0;
					$Weight=0;
					$QOHatLoc=0;
					$MBflag='B';
					$ActDispatchDate=NULL;
					$QtyInvoiced=0;
					$DiscCat='';
					$Controlled=0;
					$Serialised=0;
					$DecimalPlaces=2;
					$Narrative='';
					$TaxCategory=0;
					$ItemDue='';
					$POLine='';
					$StandardCost=0;
					$EOQ=1;
					$NextSerialNo=0;
					$ExRate=1;
					$identifier=0;
					break;

			case 'PIA':
					$PIA = explode('+',mb_substr($LineText,4));
					$PIA2 = explode(':',$PIA[1]);
					switch ($PIA[0]){
					   case '1'; // additional item id
//					      $Qty=$QTY2[1];
					   break;
					}
					$EmailText .= "\n" . __('Additional product id') . ': ' . $PIA2[0] ;
					break;

			case 'IMD':
					$IMD = explode('+',mb_substr($LineText,4));
					$IMD2 = explode(':',mb_substr($LineText,4));
					switch ($IMD[1]){
					   case 'F'; //
					   //   $Qty=$QTY2[1];
					   break;
					}

					$Descr=$IMD2[3];
					$EmailText .= "\n" . __('Item description') . ': ' . $IMD2[3] ;
					break;

			case 'QTY':
					$QTY = explode('+',mb_substr($LineText,4));
					$QTY2 = explode(':',$QTY[0]);
					switch ($QTY2[0]){
					   case '21'; // ordered qty
					      $Qty=$QTY2[1];
					   break;
					}
					$EmailText .= "\n" . __('Quantity') . ': ' . $QTY2[0] . '/'  . $QTY2[1] ;
					break;

			case 'MOA':
					$MOA = explode('+',mb_substr($LineText,4));
					$MOA2 = explode(':',$MOA[0]);
					switch ($MOA[0]){
					   case '203'; // Line item amount
					   break;
					   case '39'; // total amount
					   break;
					}
					$EmailText .= "\n" . __('Monetary amount'). ': ' . $MOA2[1];
					break;

			case 'PRI':
					$PRI = explode('+',mb_substr($LineText,4));
					$PRI2 = explode(':',$PRI[0]);
					switch ($PRI2[0]){
					   case 'AAA'; // Calculation net
					     $Price = $PRI2[1];
					   break;
					}
					$EmailText .= "\n" . __('Price details') . ': ' . $PRI2[1] ;
					break;

			case 'PAC':
					$PAC = explode('+',mb_substr($LineText,4));
					$EmailText .= "\n" . __('Package') . ': ' . $PAC[1];
					break;

			case 'PIA': // ADDITIONAL PRODUCT ID
					// SA    Supplier's article number
					$PIA = explode('+',mb_substr($LineText,4));
					$PIA2 = explode(':',$PIA[1]);
					switch ($PIA2[1]){
					   case 'SA'; // Supplier's article number
					   break;
					}
					$EmailText .= "\n" . __('Additional product id') . ': ' . $PIA[3];
					break;

			case 'PCI':
					$EmailText .= "\n" . __('Package identification');
					break;

			case 'TAX':
			// Duty/tax/fee details
					$TAX = explode('+',mb_substr($LineText,4));
					$TAX2 = explode(':',mb_substr($LineText,4));
					$TAX3 = explode('+',mb_substr($TAX2[3],1));
					switch ($TAX[0]){
					   case '7'; // tax
					   break;
					}
					$EmailText .= "\n" . __('Duty/tax/fee details') . ': ' . $TAX[1] . ' ' . $TAX3[0];
					break;

			case 'MEA':
					$MEA = explode('+',mb_substr($LineText,4));
					// MEA+AAE+G+10'
					// AAE measurement
					switch ($MEA[2]){
					   case 'G'; // gross weight
					   break;
					   case 'K'; // netweight kg
					   break;
					}
					$EmailText .= "\n" . __('Measurements'). ': ' . $MEA[1] . ' ' . $MEA[2] . ' kg' ;
					break;

			case 'UNS':
			// LIN END
			// lin end
			// add last lin into chart
					if ($LinCount > 0) {
						$UpdateDB='NO';
						// add prev processed line to cart;
				       // echo '<br />' . 'LinCount ' . $LinCount;
						AddLinToChart ($Order,$StockID,$Qty,$Descr,$LongDescr,$Price,$Disc,$UOM,$Volume,$Weight);
					}

					$EmailText .= "\n" . __('Section control/Order line count') . ': ' . $LinCount;
					break;
			case 'CNT':
			// control total
					$CNT = explode('+',mb_substr($LineText,4));
					$CNT2 = explode(':',mb_substr($LineText,4));
					switch ($CNT[0]){
					   case '2'; // lin count
					   break;
					}

					$EmailText .= "\n" . __('Control total') . ': ' . $CNT[0] ;
					break;

          // message end
			case 'UNT':
			// end of msg  number of segments / message id
				IF ($ErrorCount > 0) {
					$CreateOrder = false;
				}
/*
echo '<pre>';
print_r($Order);
echo '</pre>';
*/
				if ($CreateOrder = true) {
					//	UpdateOrder($Order);
					DB_Txn_Begin();
				//$Order->OrdDate = Date("Y-m-d");
					$OrderNo = GetNextTransNo(30); // next order number
					$DelDate = FormatDateforSQL($Order->DeliveryDate);
					$OrderDate = FormatDateforSQL($Order->OrdDate);
					if ($Order->OrderType == "") {
						$Order->OrderType = $_SESSION['DefaultOrdertype'];
					}
					if ($Order->SalesPerson == "") {
						$Order->SalesPerson = $_SESSION['DefaultSalesperson'];
					}

				//echo '<br />' . 'ordertype ' . $Order->OrderType . ' / ' . $_SESSION['DefaultOrdertype'];
				//echo '<br />' . 'salesperon ' . $Order->SalesPerson . ' / ' . $_SESSION['DefaultSalesperson'];
					$HeaderSQL = "INSERT INTO salesorders (
								orderno,
								debtorno,
								branchcode,
								customerref,
								comments,
								orddate,
								ordertype,
								shipvia,
								deliverto,
								deladd1,
								deladd2,
								deladd3,
								deladd4,
								deladd5,
								deladd6,
								contactphone,
								contactemail,
								salesperson,
								freightcost,
								fromstkloc,
								deliverydate,
								quotedate,
								confirmeddate,
								quotation,
								deliverblind
								)
							VALUES (
								'". $OrderNo . "',
								'" . $Order->DebtorNo . "',
								'" . $Order->BranchCode . "',
								'". DB_escape_string($Order->CustRef) ."',
								'". DB_escape_string($Order->Comments) ."',
								'" . $OrderDate . "',
								'" . $Order->OrderType . "',
								'" . $Order->ShipVia ."',
								'". DB_escape_string($_SESSION['Items'.$identifier]->DeliverTo) . "',
								'" . DB_escape_string($Order->DelAdd1) . "',
								'" . DB_escape_string($Order->DelAdd2) . "',
								'" . DB_escape_string($Order->DelAdd3) . "',
								'" . DB_escape_string($Order->DelAdd4) . "',
								'" . DB_escape_string($Order->DelAdd5) . "',
								'" . DB_escape_string($Order->DelAdd6) . "',
								'" . $Order->PhoneNo . "',
								'" . $Order->Email . "',
								'" . $Order->SalesPerson . "',
								'" . $Order->FreightCost ."',
								'" . $Order->Location ."',
								'" . $DelDate . "',
								'" . $QuotDate . "',
								'" . $ConfDate . "',
								'" . $Order->Quotation . "',
								'" . $Order->DeliverBlind ."'
								)";
						$ErrMsg = __('The order cannot be created because');
						$InsertQryResult = DB_query($HeaderSQL, $ErrMsg);
						// update salesorderdetails too here ..
						$xi=0;
						foreach ($Order->LineItems as $lineNumber => $linedetail) {
							$xi++;
							/*
							echo "Line: " . $xi . "<br>";
							echo "StockID: " . $linedetail->StockID . "<br>";
							  echo "Line Number: " . $lineNumber . "<br>";
							  echo "qty: " . $linedetail->Quantity . "<br>";
							  echo "price: " . $linedetail->Price . "<br>";
                           */

							$orderlineno = $linedetail->LineNumber;
							$quantity = $linedetail->Quantity;
							$stkcode = trim(mb_strtoupper($linedetail->StockID));
							$unitprice = $linedetail->Price;
							$discountpercent = $linedetail->Disc;
							$itemdue = FormatDateForSQL($linedetail->ItemDue);
							if ($linedetail->ItemDue == null) {
								$itemdue = Date("Y-m-d");
							}
							//  echo "itemdue: " . $itemdue . "<br>";

							$poline = $linedetail->POLine;
							$LINSQL = "INSERT INTO salesorderdetails (orderlineno,
														orderno,
														stkcode,
														quantity,
														unitprice,
														discountpercent,
														itemdue,
														poline)
													VALUES('" . $orderlineno . "',
														'" . $OrderNo  . "',
														'" . $stkcode ."',
														'" . $quantity . "',
														'" . $unitprice . "',
														'" . $discountpercent . "',
														'" . $itemdue . "',
														'" . $poline . "')";
							$LinResult = DB_query($LINSQL,
							__('The order line for') . ' ' . $xi . ' ' .__('could not be inserted'));

						}
						DB_Txn_Commit();
						// prnMsg(__('Order Number') . ' ' . $OrderNo . ' ' . __('has been created'),'success');
						$EmailText .= "\n" . __('Order number: ') . $OrderNo . __(' has been created from edi orders.') ;
				}
					$UNT = explode('+',mb_substr($LineText,4));
					$EmailText .= "\n" . __('Message trailer') . ': ' . $UNT[0] ;
					break;
			case 'UNZ':
					$UNZ = explode('+',mb_substr($LineText,4)); // nbr of mesages
					$EmailText .= "\n" . __('Message trailer') . ': ' . $UNZ[0] ;
					break;


		} /*end case  Seg Tag*/

	} /*end while get next line of message */

	/*Thats the end of the message or had to abort */
	if (mb_strlen($EmailText)>10){
		/*Now send the email off to the appropriate person */

		if ($TryNextFile==True){ /*had to abort this message */
			/* send the email to the sysadmin  - get email address from users*/

			$Result = DB_query("SELECT realname, email FROM www_users WHERE fullaccess=7 AND email <>''");
			if (DB_num_rows($Result)==0){ /*There are no sysadmins with email address specified */

				$Recipients = array("'phil' <phil@localhost>");

			} else { /*Make an array of the sysadmin recipients */
				$Recipients = array();
				$i=0;
				while ($SysAdminsRow=DB_fetch_array($Result)){
					$Recipients[$i] = "'" . $SysAdminsRow['realname'] . "' <" . $SysAdminsRow['email'] . ">";
					$i++;
				}
			}
			$TryNextFile = False; /*reset the abort to false before hit next file*/
			$MailSubject = __('EDI Order Message Error');
		} else {
			$MailSubject = __('EDI Order Message') . ' ' . $Order->CustRef;
			$EDICustServPerson = $_SESSION['PurchasingManagerEmail'];
			$Recipients = array($EDICustServPerson);
		}
//		$From = $_SESSION['CompanyRecord']['coyname'] . "<" . $_SESSION['CompanyRecord']['email'] . ">";
		$From = $_SESSION['CompanyRecord']['email'];
		$To = array('"' . $_SESSION['UsersRealName'] . '" <' . $_SESSION['UserEmail'] . '>');
//	error_log("Emailing: " . $From . ' -> ' .$To[0] . $To[1] . ' / ' . $MailSubject, 0); // php logging
		SendEmailFromWebERP($From,
							$_SESSION['UserEmail'], //$Recipients,
							$MailSubject,
							$EmailText,
							'',
							false);

		echo $EmailText;
	}
}/*end of the loop around all the incoming order files in the incoming orders directory */

include('includes/footer.php');

function StripTrailingComma($StringToStrip) {
	if (strrpos($StringToStrip, "'")) {
		return mb_substr($StringToStrip, 0, strrpos($StringToStrip, "'"));
	} else {
		return $StringToStrip;
	}
}

function AddLinToChart ($Order,$StockID,$Qty,$Descr,$LongDescr,$Price,$Disc,$UOM,$Volume,$Weight) {
	$Order->add_to_cart($StockID,
						$Qty,
						$Descr,
						$LongDescr,
						$Price,
						$Disc,
						$UOM,
						$Volume,
						$Weight);
	//echo '<br />' . 'Line2# ' . $StockID . ' ' . ' ' . $Qty . ' ' . $Order->ItemsOrdered;

/*
echo '<pre>';
print_r($Order);
echo '</pre>';
*/
}

function UpdateOrder($Order){
//include('DeliveryDetails.php');
/*
echo '<pre>';
print_r($Order);
echo '</pre>';
*/
}

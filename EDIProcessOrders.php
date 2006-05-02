<?php

/* $Revision: 1.17 $ */

$PageSecurity =11;

include ('includes/session.inc');

$title = _('Process EDI Orders');

include ('includes/header.inc');
include('includes/SQL_CommonFunctions.inc'); // need for EDITransNo
include('includes/htmlMimeMail.php'); // need for sending email attachments
include('includes/DefineCartClass.php');


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

*/


/*Read in the EANCOM Order Segments for the current seg group from the segments table */


$sql = 'SELECT id, segtag, maxoccur, seggroup FROM edi_orders_segs';
$OrderSeg = DB_query($sql,$db);
$i=0;
$Seg = array();

while ($SegRow=DB_fetch_array($OrderSeg)){
	$Seg[$i] = array('SegTag'=>$SegRow['segtag'], 'MaxOccur'=>$SegRow['maxoccur'], 'SegGroup'=>$SegRow['seggroup']);
	$i++;
}

$TotalNoOfSegments = $i-1;

/*get the list of files in the incoming orders directory - from config.php */
$dirhandle = opendir($_SERVER['DOCUMENT_ROOT'] . '/' . $rootpath . '/' . $_SESSION['EDI_Incoming_Orders']);

 while (false !== ($OrderFile=readdir($dirhandle))){ /*there are files in the incoming orders dir */

	$TryNextFile = False;
	echo "<BR>$OrderFile";

	/*Counter that keeps track of the array pointer for the 1st seg in the current seg group */
	$FirstSegInGrp =0;
	$SegGroup =0;

	$fp = fopen($_SERVER['DOCUMENT_ROOT'] . "/$rootpath/$_SESSION['EDI_Incoming_Orders']/$OrderFile",'r');

	$SegID = 0;
	$SegCounter =0;
	$SegTag='';
	$LastSeg = 0;
	$FirstSegInGroup = 0;
	$EmailText =''; /*Text of email to send to customer service person */
	$CreateOrder = True; /*Assume that we are to create a sales order in the system for the message read */

	$Order = new cart;

	while ($LineText = fgets($fp) AND $TryNextFile != True){ /* get each line of the order file */

		$LineText = StripTrailingComma($LineText);
		echo "<BR>$LineText";

		if ($SegTag != substr($LineText,0,3)){
			$SegCounter=1;
			$SegTag = substr($LineText,0,3);
		} else {
			$SegCounter++;
			if ($SegCounter > $Seg[$SegID]['MaxOccur']){
				$EmailText = $EmailText . "\n" . _('The EANCOM Standard only allows for') . ' ' . $Seg[$SegID]['MaxOccur'] . ' ' ._('occurrences of the segment') . ' ' . $Seg[$SegID]['SegTag'] . ' ' . _('this is the') . ' ' . $SegCounter . ' ' . _('occurrence') .  '<BR>' . _('The segment line read as follows') . ':<BR>' . $LineText;
			}
		}

/* Go through segments in the order message array in sequence looking for matching SegTags

   */
		while ($SegTag != $Seg[$SegID]['SegTag'] AND $SegID < $TotalNoOfSegments) {

			$SegID++; /*Move to the next Seg in the order message */
			$LastSeg = $SegID; /*Remember the last segid moved to */

			echo "\n" . _('Segment Group') . ' = ' . $Seg[$SegID]['SegGroup'] . ' ' . _('Max Occurences of Segment') . ' = ' . $Seg[$SegID]['MaxOccur'] . ' ' . _('No occurrences so far') . ' = ' . $SegCounter;

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

			$EmailText .= "\n" . _('ERROR') . ': ' . _('Unable to identify segment tag') . ' ' . $SegTag . ' ' . _('from the message line') . '<BR>' . $LineText . '<BR><FONT COLOR=RED><B>' . _('This message processing has been aborted and seperate advice will be required from the customer to obtain details of the order') . '<B></FONT>';

			$TryNextFile = True;
		}

		echo '<BR>' . _('The segment tag') . ' ' . $SegTag . ' ' . _('is being processed');
		switch ($SegTag){
			case 'UNH':
				$UNH_elements = explode ('+',substr($LineText,4));
				$Order->Comments .= _('Customer EDI Ref') . ': ' . $UNH_elements[0];
				$EmailText .= "\n" . _('EDI Message Ref') . ': ' . $UNH_elements[0];
				if (substr($UNH_elements[1],0,6)!='ORDERS'){
					$EmailText .= "\n" . _('This message is not an order');
					$TryNextFile = True;
				}

				break;
			case 'BGM':
				$BGM_elements = explode('+',substr($LineText,4));
				$BGM_C002 = explode(':',$BGM_elements[0]);
				switch ($BGM_C002[0]){
					case '220':
						$EmailText .= "\n" . _('This message is a standard order');
						break;
					case '221':
						$EmailText .= "\n" . _('This message is a blanket order');
						$Order->Comments .= "\n" . _('blanket order');
						break;
					case '224':
						$EmailText .= "\n\n" . _('This order is URGENT') . "</FONT>";
						$Order->Comments .= "\n" . _('URGENT ORDER');
						break;
					case '226':
						$EmailText .= "\n" . _('Call off order');
						$Order->Comments .= "\n" . _('Call Off Order');
						break;
					case '227':
						$EmailText .= "\n" . _('Consignment order');
						$Order->Comments .= "\n" . _('Consigment order');
						break;
					case '22E':
						$EmailText .= "\n" . _('Manufacturer raised order');
						$Order->Comments .= "\n" . _('Manufacturer raised order');
						break;
					case '258':
						$EmailText .= "\n" . _('Standing order');
						$Order->Comments .= "\n" ._('Standing order');
						break;
					case '237':
						$EmailText .= "\n" . _('Cross docking services order');
						$Order->Comments .= "\n" . _('Cross docking services order');
						break;
					case '400':
						$EmailText .= "\n" . _('Exceptional Order');
						$Order->Comments .= "\n" . _('Exceptional Order');
						break;
					case '401':
						$EmailText .= "\n" . _('Trans-shipment order');
						$Order->Comments .= "\n" . _('Trans-shipment order');
						break;
					case '402':
						$EmailText .= "\n" . _('Cross docking order');
						$Order->Comments .= "\n" . _('Cross docking order');
						break;

				} /*end switch for type of order */
				if (isset($BGM_elements[1])){
					echo '<BR>echo BGM_elements[1] ' .$BGM_elements[1];
					$BGM_C106 = explode(':',$BGM_elements[1]);
					$Order->CustRef = $BGM_C106[0];
					$EmailText .= "\n" . _('Customers order ref') . ': ' . $BGM_C106[0];
				}
				if (isset($BGM_elements[2])){
					echo "<BR>echo BGM_elements[2] " .$BGM_elements[2];
					$BGM_1225 = explode(':',$BGM_elements[2]);
					$MsgFunction = $BGM_1225[0];


					switch ($MsgFunction){
						case '5':
							$EmailText .= "\n\n" . _('REPLACEMENT order') . ' - ' . _('MUST DELETE THE ORIGINAL ORDER MANUALLY');
							break;
						case '6':
							$EmailText .= "\n" . _('Confirmation of previously sent order');
							break;
						case '7':
							$EmailText .= "\n\n" . _('DUPLICATE order DELETE ORIGINAL ORDER MANUALLY');
							break;
						case '16':
							$CreateOrder = False; /*Dont create order in system */
							$EmailText .= "\n\n" . _('Proposed order only no order created in web-ERP');
							break;
						case '31':
							$CreateOrder = False; /*Dont create order in system */
							$EmailText .= "\n" . _('COPY order only no order will be created in web-ERP');
							break;
						case '42':
							$CreateOrder = False; /*Dont create order in system */
							$EmailText .= "\n" . _('Confirmation of order') . ' - ' . _('not created in web-ERP');
							break;
						case '46':
							$CreateOrder = False; /*Dont create order in system */
							$EmailText .= "\n" . _('Provisional order only') . ' - ' . _('not created in web-ERP');
							break;
					}

					if (isset($BGM_1225[1])){
						$ResponseCode = $BGM_1225[1];
						echo '<BR>' . _('Response Code') . ': ' . $ResponseCode;
						switch ($ResponseCode) {
							case 'AC':
								$EmailText .= "\n" . _('Please acknowlege to customer with detail and changes made to the order');
								break;
							case 'AB':
								$EmailText .= "\n" . _('Please acknowlege to customer the receipt of message');
								break;
							case 'AI':
								$EmailText .= "\n" . _('Please acknowlege to customer any changes to the order');
								break;
							case 'NA':
								$EmailText .= "\n" . _('No acknowlegement to customer is required');
								break;
						}
					}
				}
				break;
			case 'DTM':
				/*explode into an arrage all items delimited by the : - only after the + */
				$DTM_C507 = explode(':',substr($LineText,4));
				$LocalFormatDate = ConvertEDIDate($DTM_C507[1],$DTM_C507[2]);

				switch ($DTM_C507[0]){
					case '2': /*Delivery date */
					case '10': /*shipment date requested */
					case '11': /*dispatch date */
					case 'X14': /*Reguested delivery week commencing EAN code */
					case '64': /*Earliest delivery date */
					case '69': /*Promised delivery date */
						$Order->DeliveryDate = $LocalFormatDate;
						$EmailText .= "\n" . _('Requested delivery date') . ' ' . $Order->DeliveryDate;
						break;
					case '15': /*promotion start date */
						$EmailText .= "\n" . _('Promotion start date') . ' ' . $LocalFormatDate;
						break;
					case '37': /*ship not before */
						$EmailText .= "\n" . _('Do NOT ship before') . ' ' . $LocalFormatDate;
						break;
					case '38': /*ship not later than */
					case '61': /*Cancel if not delivered by this date */
					case '63': /*Latest delivery date */
					case '393': /*Cancel if not shipped by this date */
						$EmailText .= "\n" . _('Cancel order if not dispatched before') . ' ' . $LocalFormatDate;
						break;
					case '137': /*Order date */
						$Order->Orig_OrderDate = $LocalFormatDate;
						$EmailText .= "\n" . _('Order date') . ' ' . $LocalFormatDate;
						break;
					case '171': /*A date relating to a RFF seg */
						/*This DTM segment follows a RFF seg so $RFF will be set
						use the RFF seg to determine if the date refers to the
						order */
						$EmailText .= "\n" . _('dated') . ' ' . $LocalFormatDate;
						if ($SegGroup == 1){
							$Order->Comments .= ' ' . _('dated') . ' ' . $LocalFormatDate;
						}
						break;
					case '200': /*Pickup collection date/time */
						$EmailText .= "\n\n" . _('Pickup date') . ' ' . $LocalFormatDate;
						$Order->DeliveryDate = $LocalFormatDate;
						break;
					case '263': /*Invoicing period */
						$EmailText .= "\n" . _('Invoice period') . ' ' . $LocalFormatDate;
						break;
					case '273': /*Validity period */
						$EmailText .= "\n" . _('Valid period') . ' ' . $LocalFormatDate;
						break;
					case '282': /*Confirmation date lead time */
						$EmailText .= "\n" . _('Confirmation of date lead time') . ' ' . $LocalFormatDate;
						break;
				}
				break;
			case 'PAI':
				/*explode into an array all items delimited by the : - only after the + */
				$PAI_C534 = explode(':',substr($LineText,4));
				if ($PAI_C534[0]=='1'){
					$EmailText .= "\n" . _('Payment will be effected by a direct payment for this order');
				} elseif($PAI_C534[0]=='OA'){
					$EmailText .= "\n" . _('This order to be settled in accordance with the normal account trading terms');
				}
				if ($PAI_C534[1]=='20'){
					$EmailText .= "\n" . _('The goods on this order') . ' - ' . _('once delivered') . ' - ' . _('will be held as security for the payment');
				}
				if ($PAI_C534[2]=='42'){
					$EmailText .= "\n" . _('Payment will be effected to bank account');
				} elseif ($PAI_C534[2]=='60'){
					$EmailText .= "\n" . _('Payment will be effected by promissory note');
				} elseif ($PAI_C534[2]=='40'){
					$EmailText .= "\n" . _('Payment will be effected by a bill drawn by the creditor on the debtor');
				} elseif ($PAI_C534[2]=='10E'){
					$EmailText .= "\n" . _('Payment terms are defined in the Commerical Account Summary Section');
				}
				if (isset($PAI_C534[5])){
					if ($PAI_C534[5]=='2')
					$EmailText .= "\n" . _('Payment will be posted through the ordinary mail system');
				}
				break;
			case 'ALI':
				$ALI = explode('+',substr($LineText,4));
				if (strlen($ALI[0])>1){
					$EmailText .= "\n" . _('Goods of origin') . ' ' . $ALI[0];
				}
				if (strlen($ALI[1])>1){
					$EmailText .= "\n" . _('Duty regime code') . ' ' . $ALI[1];
				}
				switch ($ALI[2]){
					case '136':
						$EmailText .= "\n" . _('Buying group conditions apply');
						break;
					case '137':
						$EmailText .= "\n\n" . _('Cancel the order if complete delivery is not possible on the requested date or time');
						break;
					case '73E':
						$EmailText .= "\n" . _('Delivery subject to final authorisation');
						break;
					case '142':
						$EmailText .= "\n" . _('Invoiced but not replenished');
						break;
					case '143':
						$EmailText .= "\n" . _('Replenished but not invoiced');
						break;
					case '144':
						$EmailText .= "\n" . _('Deliver Full order');
						break;
				}
				break;
			case 'FTX':
				$FTX = explode('+',substr($LineText,4));
				/*agreed coded text is not catered for ... yet
				only free form text */
				if (strlen($FTX[3])>5){
					$FTX_C108=explode(':',$FTX[3]);
					$Order->Comments .= $FTX_C108[0] . " " . $FTX_C108[1] . ' ' . $FTX_C108[2] . ' ' . $FTX_C108[3] . ' ' . $FTX_C108[4];
					$EmailText .= "\n" . $FTX_C108[0] . ' ' . $FTX_C108[1] . ' ' . $FTX_C108[2] . ' ' . $FTX_C108[3] . ' ' . $FTX_C108[4] . ' ';
				}
				break;
			case 'RFF':
				$RFF = explode(':',substr($LineText,4));
				switch ($RFF[0]){
					case 'AE':
						$MsgText = "\n" . _('Authorisation for expense no') . ' ' . $RFF[1];
						break;
					case 'BO':
						$MsgText =  "\n" . _('Blanket Order') . ' # ' . $RFF[1];
						break;
					case 'CR':
						$Order->CustRef = $RFF[1];
						$MsgText =  "\n" . _('Customer Ref') . ' # ' . $RFF[1];
						break;
					case 'CT':
						$MsgText =  "\n" . _('Contract'). ' # ' . $RFF[1];
						break;
					case 'IP':
						$MsgText =  "\n" . _('Import Licence') . ' # ' . $RFF[1];
						break;
					case 'ON':
						$Order->CustRef = $RFF[1];
						$MsgText =  "\n" . _('Buyer order') . ' # ' . $RFF[1];
						break;
					case 'PD':
						$MsgText =  "\n" . _('Promo deal') . ' # ' . $RFF[1];
						break;
					case 'PL':
						$MsgText =  "\n" . _('Price List') . ' # ' . $RFF[1];
						break;
					case 'UC':
						$MsgText =  "\n" . _('Ultimate customer ref') . ' ' . $RFF[1];
						break;
					case 'VN':
						$MsgText =  "\n" . _('Supplier Order') . ' # ' . $RFF[1];
						break;
					case 'AKO':
						$MsgText =  "\n" . _('Action auth') . ' # ' . $RFF[1];
						break;
					case 'ANJ':
						$MsgText =  "\n" . _('Authorisation') . ' # ' . $RFF[1];
						break;
				}
				if ($SegGroup == 1){
					$Order->Comments .= $MsgText;
				}
				$EmailText .= $MsgText;
				break;
			case 'NAD':
				$NAD = explode('+',substr($LineText,4));
				$NAD_C082 = explode(':', $NAD[1]);
				$NAD_C058 = explode(':', $NAD[2]); /*Not used according to MIG */
				$NAD_C080 = explode(':', $NAD[3]);
				$NAD_C059 = explode(':', $NAD[4]);
				switch ($NAD[0]){
					case 'IV': /* This Name and address detail is that of the party to be invoiced */
						/*Look up the EAN Code given $NAD[1] for the buyer */
						if ($NAD_C082[2] ==9){
						/*if NAD_C082[2] must = 9 then NAD_C082[0] is the EAN Intnat Article Numbering Assocn code of the customer - look up the customer by EDIReference*/
							$InvoiceeResult = DB_query("SELECT debtorno FROM debtorsmaster WHERE edireference='" . $NAD_C082[0] . "' AND ediorders=1",$db);
							if (DB_num_rows($InvoiceeResult)!=1){
								$EmailText .= "\n" . _('The Buyer reference was specified as an EAN International Article Numbering Association code') . '. ' . _('Unfortunately the field EDIReference of any of the customers currently set up to receive EDI orders does not match with the code') . ' ' . $NAD_C082[0] . ' ' . _('used in this message') . '. ' . _('So that is the end of the road for this message');
								$TryNextFile = True; /* Look for other EDI msgs */
								$CreateOrder = False; /*Dont create order in system */
							} else {
								$CustRow = DB_fetch_array($InvoiceeResult);
								$Order->DebtorNo = $CustRow['debtorno'];
							}
							break;
						}
						if (strlen($NAD_C080[0])>0){
							$Order->CustomerName = $NAD_C080[0];
						}
						break;

					case 'SU':
						/*Supplier party details. This should be our EAN IANA number if not the message is not for us!! */
						if ($NAD_C082[0]!= $_SESSION['EDIReference']){
							/* $_SESSION['EDIReference'] is set in config.php as our EDIReference it should be our EAN International Article Numbering Association code */
							$EmailText .= "\n" . _('The supplier reference was specified as an EAN International Article Numbering Association code') . '. ' . _('Unfortunately the company EDIReference') . ' - ' . $_SESSION['EDIReference']  . ' ' . _('does not match with the code') . ' ' . $NAD_C082[0] . ' ' . _('used in this message') . '. ' . _('This implies that the EDI message is for some other supplier') . '. ' . _('No further processing will be done');
							$TryNextFile = True; /* Look for other EDI msgs */
							$CreateOrder = False; /*Dont create order in system */						}
						break;
					case 'DP':
						/*Delivery Party - get the address and name etc */

						/*Snag here - how do I figure out what branch to charge */
						if (strlen($NAD_C080[0])>0){
							$Order->DeliverTo = $NAD_C080[0];
						}
						if (strlen($NAD_C059[0])>0){
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
									FROM custbranch INNER JOIN debtorsmaster ON custbranch.debtorno = custbranch.debtorno WHERE custbranchcode='" . $NAD_C082[0] . "' AND custbranch.debtorno='" . $Order->DebtorNo . "' AND debtorsmaster.ediorders=1",$db);
						if (DB_num_rows($BranchResult)!=1){
							$EmailText .= "\n" . _('The Store number was specified as') . ' ' . $NAD_C082[0] . ' ' . _('Unfortunately there are either no branches of customer code') . ' ' . $Order->DebtorNo . ' ' ._('or several that match this store number') . '. ' . _('This order could not be processed further');
							$TryNextFile = True; /* Look for other EDI msgs */
							$CreateOrder = False; /*Dont create order in system */						} else {
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
						/* The buyer details - don't think we care about this */
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



			/* UP TO HERE NEED TESTER */



		} /*end case  Seg Tag*/
	} /*end while get next line of message */
	/*Thats the end of the message or had to abort */
	if (strlen($EmailText)>10){
		/*Now send the email off to the appropriate person */
		$mail = new htmlMimeMail();
		$mail->setText($EmailText);
		$mail->setFrom($_SESSION['CompanyRecord']['coyname'] . "<" . $_SESSION['CompanyRecord']['email'] . ">");

		if ($TryNextFile==True){ /*had to abort this message */
			/* send the email to the sysadmin  - get email address from users*/

			$Result = DB_query("SELECT realname, email FROM www_users WHERE fullaccess=7 AND email <>''",$db);
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
			$TryNextFile=False; /*reset the abort to false before hit next file*/
			$mail->setSubject(_('EDI Order Message Error'));
		} else {

			$mail->setSubject(_('EDI Order Message') . ' ' . $Order->CustRef);
			$EDICustServPerson ="'phil' <phil@localhost>";
			$Recipients = array($EDICustServPerson);
		}



		$result = $mail->send($Recipients);

		echo $EmailText;
	} /* nothing in the email text to send - the message file is a complete dud - maybe directory */

	/*Now create the order from the $Order object  and commit to the DB*/




 } /*end of the loop around all the incoming order files in the incoming orders directory */


include ('includes/footer.inc');

function StripTrailingComma ($StringToStrip){

	if (strrpos($StringToStrip,"'")){
		Return substr($StringToStrip,0,strrpos($StringToStrip,"'"));
	} else {
		Return $StringToStrip;
	}
}

?>

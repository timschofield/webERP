<?php



$PageSecurity =11;
$title = "Process EDI Orders";

include ("includes/session.inc");
include ("includes/header.inc");
include ("includes/DateFunctions.inc");
include("includes/SQL_CommonFunctions.inc"); //need for EDITransNo
include("includes/htmlMimeMail.php"); // need for sending email attachments
include("includes/DefineCartClass.php");

echo "<!-- \$Revision: 1.2 $ -->"

$CompanyRecord = ReadInCompanyRecord($db);

/*The logic outline is this ....

Make an array of the format of the ORDER message from the table EDI_ORDERS_Segs

Get the list of files in EDI_Incoming_Orders - work through each one as follows

Read in the flat file one line at a time

Compare the SegTag in the flat file with the expected SegTag from EDI_ORDERS_Segs

parse the data in the line of text from the flat file to enable the order to be created

Compile an html email to the customer service person based on the location
of the customer doing the ordering and where it would be best to pick the order
from

Read the next line of the flat file ...

*/




/*get the list of files in the incoming orders directory */
echo "<BR>The document_root is " . $_SERVER['DOCUMENT_ROOT'];

/*Read in the EANCOM Order Segments for the current seg group from the segments table */

$sql = "SELECT ID, SegTag, MaxOccur, SegGroup FROM EDI_ORDERS_Segs";
$OrderSeg = DB_query($sql,$db);
$i=0;
$Seg =array();

while ($SegRow=DB_fetch_array($OrderSeg)){
	$Seg[$i] = array('SegTag'=>$SegRow['SegTag'],'MaxOccur'=>$SegRow['MaxOccur'],'SegGroup'=>$SegRow['SegGroup']);
	$i++;
}


$dirhandle = opendir($_SERVER['DOCUMENT_ROOT'] . "/" . $rootpath . "/" . $EDI_Incoming_Orders);


 while (false !== ($OrderFile=readdir($dirhandle))){ /*there are files in the incoming orders dir */

	$TryNextFile = False;
	echo "<BR>$OrderFile";

	/*Counter that keeps track of the array pointer for the 1st seg in the current seg group */
	$FirstSegInGrp =0;
	$SegGroup =0;

	$fp = fopen($_SERVER['DOCUMENT_ROOT'] . "/$rootpath/$EDI_Incoming_Orders/$OrderFile","r");
	$msg = array();
	$LineNo=0;
	$SegID = 0;
	$SegCounter =0;
	$LastSeg = 0;
	$EmailText =""; /*Text of email to send to customer service person */
	$CreateOrder = True; /*Assume create a sales order in the system for the message read */

	$Order = new cart;

	while ($LineText = fgets($fp) AND $TryNextFile != True){ /* get each line of the order file */

		$LineNo++;
		echo "<BR>$LineText";
		$msg[$LineNo] = $LineText;  /*Put each line of the message into the $msg array */

		if ($SegTag != substr($LineText,0,3)){
			$SegCounter=1;
			$SegTag = substr($LineText,0,3);
		} else {
			$SegCounter++;
			if ($SegCounter > $Seg[$SegID]['MaxOccur']){
				$EmailText = $EmailText . "<BR>The EANCOM Standard only allows for " . $Seg[$SegID]['MaxOccur'] . " occurrences of the segment " . $Seg[$SegID]['SegTag'] . " this is the " . $SegCounter . " occurrence. <BR>The segment line read as follows:<BR>" . $LineText;
			}
		}

/* Go through segments in the order message array in sequence looking for matching SegTags

   */
		while ($SegTag != $Seg[$SegID]['SegTag']) {
			$SegID++; /*Move to the next Seg in the order message */
			$LastSeg = $SegID; /*Remember the last segid moved to */
			if ($Seg[$SegID]['SegGroup'] != $SegGroup AND $Seg[$SegID]['MaxOccur']> $SegCounter){ /*moved to a new seg group  but could be more segment groups*/
				$SegID = $FirstSegInGroup; /*Try going back to first seg in the group */
				if ($SegTag != $Seg[$SegID]['SegTag'])){ /*still no match - must be into new seg group */
					$SegID = $LastSeg;
				} else {
					$SegGroup = $Seg[$SegID]['SegGroup'];
				}
			}
		}

		switch ($SegTag){
			case = 'UNH':
				$UNH_elements = explode ('+',$LineText);
				$Orders->Comments = "Customer EDI Ref:" . $UNH_elements[0];
				$EmailText .= "<BR>EDI Message Ref " . $UNH_elements[0];
				if (substr($UNH_elements[1],0,6)!='ORDERS'){
					$EmailText .= "<BR>This message is not an order";
					$TryNextFile = True;
				}
				break;
			case = 'BGM':
				$BGM_elements = explode('+',$LineText);
				$BGM_C002 = explode(':',$BGM_elements[0]);
				switch ($BGM_C002[0]){
					case = '220':
						$EmailText .= "<BR>This message is a standard order";
						break;
					case = '221':
						$EmailText .= "<BR>This message is a blanket order";
						$Orders->Comments .= "/n blanket order";
						break;
					case = '224':
						$EmailText .= "<BR><FONT SIZE=4 COLOR=RED>This order is URGENT</FONT>";
						$Orders->Comments .= "/n URGENT ORDER";
						break;
					case = '226':
						$EmailText .= "<BR>Call off order";
						$Orders->Comments .= "/n Call Off Order";
						break;
					case = '227':
						$EmailText .= "<BR>Consignment order";
						$Orders->Comments .= "/n consigment order";
						break;
					case = '22E':
						$EmailText .= "<BR>Manufacturer raised order";
						$Orders->Comments .= "/n Manufacturer raised order";
						break;
					case = '258':
						$EmailText .= "<BR>Standing order";
						$Orders->Comments .= "/n standing order";
						break;
					case = '237':
						$EmailText .= "<BR>Cross docking services order";
						$Orders->Comments .= "/n Cross docking services order";
						break;
					case = '400':
						$EmailText .= "<BR>Exceptional Order";
						$Orders->Comments .= "/n exceptional order";
						break;
					case = '401':
						$EmailText .= "<BR>Trans-shipment order";
						$Orders->Comments .= "/n Trans-shipment order";
						break;
					case = '402':
						$EmailText .= "<BR>Cross docking order";
						$Orders->Comments .= "/n cross docking order";
						break;

				} /*end switch for type of order */
				$BGM_C106 = explode(':',$BGM_elements[1]);
				$Order->CustRef = $BGM_C106[0];
				$EmailText .= "<BR>Customer's order ref: " . $BGM_C106[0];
				$BGM_1225 = explode(':',$BGM_elements[2]);
				$MsgFunction = StripTrailingComma($BGM_1225[0]);

				switch ($MsgFunction){
					case = '5':
						$EmailText .= "<BR><FONT SIZE=4 COLOR=RED>REPLACEMENT order - must delete original order manually</FONT>";
						break;
					case = '6':
						$EmailText .= "<BR>Confirmation of previously sent order";
						break;
					case = '7':
						$EmailText .= "<BR><FONT SIZE=4 COLOR=RED>DUPLICATE order</FONT> Delete original order manually";
						break;
					case = '16':
						$CreateOrder = False; /*Dont create order in system */
						$EmailText .= "<BR><FONT SIZE=4 COLOR=RED>Proposed order only</FONT> no order created in web-erp";
						break;
					case = '31':
						$CreateOrder = False; /*Dont create order in system */
						$EmailText .= "<BR><FONT SIZE=4 COLOR=RED>COPY order only</FONT> no order will be created in web-erp";
						break;
					case = '42':
						$CreateOrder = False; /*Dont create order in system */
						$EmailText .= "<BR>Confirmation of order - not created in web-erp";
						break;
					case = '46':
						$CreateOrder = False; /*Dont create order in system */
						$EmailText .= "<BR>Provisional order only- not created in web-erp";
						break;
				}
				if (strlen($BGM_1225[1])>1){
					$ResponseCode = StripTrailingComma($BGM_1225[1]);
					switch ($ResponseCode) {
						case = 'AC':
							$EmailText .= "<BR>Please acknowlege to customer with detail and changes made to the order";
							break;
						case = 'AB':
							$EmailText .= "<BR>Please acknowlege to customer the receipt of message";
							break;
						case = 'AI':
							$EmailText .= "<BR>Please acknowlege to customer any changes to the order";
							break;
						case = 'NA':
							$EmailText .= "<BR>No acknowlegement to customer is required";
							break;
					}
				}
			case = 'DTM':
				/*explode into an arrage all items delimited by the : - only after the + */
				$DTM_C507 = explode(':',substr($LineText,4));
				$LocalFormatDate = ConvertEDIDate($DTM_C507[1],$DTM_C507[2]);

				switch ($DTM_C507[0]){
					case = '2': /*Delivery date */
					case = '10': /*shipment date requested */
					case = '11': /*dispatch date */
					case = 'X14': /*Reguested delivery week commencing EAN code */
					case = '64': /*Earliest delivery date */
					case = '69': /*Promised delivery date */
						$Order->DeliveryDate = $LocalFormatDate;
						$EmailText .= "<BR>Requested delivery date " . $Order->DeliveryDate;
						break;
					case = '15': /*promotion start date */
						$EmailText .= "<BR>Promotion start date " . $LocalFormatDate;
						break;
					case = '37': /*ship not before */
						$EmailText .= "<BR>Do NOT ship before " . $LocalFormatDate;
						break;
					case = '38': /*ship not later than */
					case = '61': /*Cancel if not delivered by this date */
					case = '63': /*Latest delivery date */
					case = '393': /*Cancel if not shipped by this date */
						$EmailText .= "<BR>Cancel order if not dispatched before " . $LocalFormatDate;
						break;
					case = '137': /*Order date */
						$Order->Orig_OrderDate = $LocalFormatDate;
						$EmailText .= "<BR>Order date " . $LocalFormatDate;
						break;
					case = '200': /*Pickup collection date/time */
						$EmailText .= "<BR><FONT COLOR=RED SIZE=4>Pickup date " . $LocalFormatDate;
						$Order->DeliveryDate = $LocalFormatDate;
						break;
					case = '263': /*Invoicing period */
						$EmailText .= "<BR>Invoice " . $LocalFormatDate;
						break;
					case = '273': /*Validity period */
						$EmailText .= "<BR>Valid to " . $LocalFormatDate;
						break;
					case = '282': /*Confirmation date lead time */
						$EmailText .= "<BR>Confirmation of date lead time " . $LocalFormatDate;
						break;
				}



		} /*end case  Seg Tag*/
	} /*end while get next line of message */


 } /*end of the loop around all the incoming order files in the incoming orders directory */


include ("includes/footer.inc");

function StripTrailingComma ($StringToStrip){

	if (strrpos($StringToStrip,"'")){
		Return substr($StringToStrip,0,strrpos($StringToStrip,"'"));
	} else {
		Return $StringToStrip;
	}
}

?>

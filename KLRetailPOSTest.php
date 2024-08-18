<?php

define("VERSIONFILE", "0.00"); // 

include('includes/DefineCartClass.php');
include('includes/session.php');

$Title = _('STRIPPED POS with PRINTING RECEIPT '. VERSIONFILE);

include('includes/header.php');
include('includes/GetPrice.inc');
include('includes/SQL_CommonFunctions.inc');
include('includes/GetSalesTransGLCodes.inc');

include('includes/KLCountriesForRetail.php');

include('includes/KLDefines.php');
include('includes/KLGeneralFunctions.php');
include('includes/KLPOSGeneral.php');
include('includes/KLEmails.php');

include('includes/wcpInitScript.php');  

if (isset($_POST['CancelOrder'])) {
	echo '<br /><br /><a href="' .$_SERVER['PHP_SELF'] . '">' . _('Start a new Retail Sale') . '</a>';
	include('includes/footer.php');
	exit;
} else { /*Not cancelling the order */
	echo '<p class="page_title_text"><img src="'.$RootPath.'/css/'.$Theme.'/images/inventory.png" title="' . _('Retail Sales') . '" alt="" />' . ' ';
	echo '</p>';
}


echo '<form action="' . $_SERVER['PHP_SELF'] . '?' . SID .'identifier='.$identifier . '" name="SelectParts" method="post">';
echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';


if (!isset($_POST['ProcessSale'])){ /*only show order lines if there are any */

	echo 'Here comes the details of the purchase <br />';
	echo 'Click Process The Sale to continue';

	/////////////////////////////////////////////////
	// Buttons confirm / recalculate the sale
	/////////////////////////////////////////////////
	echo '<br /><div class="centre">
				<input type="submit" name="ProcessSale" value="' . _('Process The Sale') . '" />
				</div>
				<hr />';

} # end of if lines

if (isset($_POST['ProcessSale']) and $_POST['ProcessSale'] != ""){

	// *************************************************************************
	//   SHOW THE DETAILS OF PAYMENTS 
	// *************************************************************************

	echo 'Here is where the details of the sale are shown once processed <br /><br />';

	/************************************************************************************/
	/*                         PRINT THE CUSTOMER RECEIPT                                */
	/************************************************************************************/
	// TEST identifier
	$identifier = '123456789';
	$HeaderText = KLPrintReceiptHeader($identifier, $OrderNo);
	$CustomerFooter = KLPrintReceiptCustomerFooter($identifier, $OrderNo);
	$ShopFooter = KLPrintReceiptShopFooter($identifier, $OrderNo);
//	$Receipt = $HeaderText . $CustomerFooter . $HeaderText . $ShopFooter;
	$Receipt = $HeaderText . $ShopFooter;
	
//	$Receipt = mb_convert_encoding($Receipt, "ISO-8859-1");
	$filename = GetFilenameFromPOSIdentifier($identifier);   
	file_put_contents($filename, $Receipt);

	//################## PRINTING STUFF ##################### 
	echo '<img src="'.$RootPath.'/css/'.$Theme.'/images/printer.png" title="' . 
		_('Print the customer receipt') . '" alt="" />' . ' ' . 
		'<a href="#"' . 'onclick="javascript:jsWebClientPrint.print(\'identifier='.$identifier.
																	'\');">' .  
		_('Print the customer receipt'). '</a><br /><br />';
   //################## PRINTING STUFF ##################### 
	
	echo '<br /><br /><a href="' .$_SERVER['PHP_SELF'] . '">' . _('Start a new Retail Sale') . '</a></div>';

} else {
	//pretend the user never tried to commit the sale
	unset($_POST['ProcessSale']);
}
/*******************************
 * end of Invoice Processing
 * ******************************/


/* Now show the stock item selection search stuff below */
if (!isset($_POST['ProcessSale'])){

  		echo '<br /><div class="centre"><input type="submit" name="CancelOrder" value="' . _('Cancel Sale') . '" onclick="return confirm(\'' . _('Are you sure you wish to cancel this sale?') . '\');" /></div>';
}
echo '</form>';
include('includes/footer.php');

?>
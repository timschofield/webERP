<?php


include('includes/session.php');
$Title = _('Supplier Offers');
$ViewTopic = 'SupplierTenders';
$BookMark = 'SupplierOffers';
include('includes/header.php');
include('includes/SQL_CommonFunctions.inc');

if (isset($_POST['supplierid'])) {
	$SQL="SELECT suppname,
				email,
				currcode,
				paymentterms
			FROM suppliers
			WHERE supplierid='" . $_POST['supplierid'] . "'";
	$Result = DB_query($SQL);
	$MyRow=DB_fetch_array($Result);
	$SupplierName=$MyRow['suppname'];
	$Email=$MyRow['email'];
	$CurrCode=$MyRow['currcode'];
	$PaymentTerms=$MyRow['paymentterms'];
}

if (!isset($_POST['supplierid'])) {
	$SQL="SELECT DISTINCT
			offers.supplierid,
			suppliers.suppname
		FROM offers
		LEFT JOIN purchorderauth
			ON offers.currcode=purchorderauth.currabrev
		LEFT JOIN suppliers
			ON suppliers.supplierid=offers.supplierid
		WHERE purchorderauth.userid='" . $_SESSION['UserID'] . "'
			AND offers.expirydate > CURRENT_DATE
			AND purchorderauth.cancreate=0";
	$Result=DB_query($SQL);
	if (DB_num_rows($Result)==0) {
		prnMsg(_('There are no offers outstanding that you are authorised to deal with'), 'information');
	} else {
		echo '<p class="page_title_text"><img src="' . $RootPath.'/css/' . $Theme.'/images/supplier.png" title="' . _('Select Supplier') . '" alt="" />' . ' ' . _('Select Supplier') . '</p>';
		echo '<form method="post" action="' . htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') .'">';
		echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';
		echo '<fieldset>
				<legend>', _('Supplier Selection'), '</legend>
				<field>
					<label for="supplierid">' . _('Select Supplier') . '</label>
					<select name=supplierid>';
		while ($MyRow=DB_fetch_array($Result)) {
			echo '<option value="' . $MyRow['supplierid'].'">' . $MyRow['suppname'] . '</option>';
		}
		echo '</select>
			</field>
			</fieldset>
			<div class="centre">
				<input type="submit" name="select" value="' . _('Enter Information') . '" />
			</div>
			</form>';
	}
}

if (!isset($_POST['submit']) and isset($_POST['supplierid'])) {
	$SQL = "SELECT offers.offerid,
				offers.tenderid,
				offers.supplierid,
				suppliers.suppname,
				offers.stockid,
				stockmaster.description,
				offers.quantity,
				offers.uom,
				offers.price,
				offers.expirydate,
				offers.currcode,
				stockmaster.decimalplaces,
				currencies.decimalplaces AS currdecimalplaces
			FROM offers INNER JOIN purchorderauth
				ON offers.currcode=purchorderauth.currabrev
			INNER JOIN suppliers
				ON suppliers.supplierid=offers.supplierid
			INNER JOIN currencies
				ON suppliers.currcode=currencies.currabrev
			LEFT JOIN stockmaster
				ON stockmaster.stockid=offers.stockid
			WHERE purchorderauth.userid='" . $_SESSION['UserID'] . "'
				AND offers.expirydate >= CURRENT_DATE
				AND offers.supplierid='" . $_POST['supplierid'] . "'
			ORDER BY offerid";
	$Result=DB_query($SQL);

	echo '<form method="post" action="' . htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') . '">';
    echo '<div>';
	echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';

	echo '<p class="page_title_text">
			<img src="' . $RootPath.'/css/' . $Theme.'/images/supplier.png" title="' . _('Supplier Offers') . '" alt="" />' . ' ' . _('Supplier Offers') . '
		</p>';

	echo '<table class="selection">
			<tr>
				<th>' . _('Offer ID') . '</th>
				<th>' . _('Supplier') . '</th>
				<th>' . _('Stock Item') . '</th>
				<th>' . _('Quantity') . '</th>
				<th>' . _('Units') . '</th>
				<th>' . _('Price') . '</th>
				<th>' . _('Total') . '</th>
				<th>' . _('Currency') . '</th>
				<th>' . _('Offer Expires') . '</th>
				<th>' . _('Accept') . '</th>
				<th>' . _('Reject') . '</th>
				<th>' . _('Defer') . '</th>
			</tr>';

	echo 'The result has rows '.DB_num_rows($Result) . '<br/>';

	while ($MyRow=DB_fetch_array($Result)) {
		echo '<tr class="striped_row">
			<td>' . $MyRow['offerid'] . '</td>
			<td>' . $MyRow['suppname'] . '</td>
			<td>' . $MyRow['description'] . '</td>
			<td class="number">' . locale_number_format($MyRow['quantity'],$MyRow['decimalplaces']) . '</td>
			<td>' . $MyRow['uom'] . '</td>
			<td class="number">' . locale_number_format($MyRow['price'],$MyRow['currdecimalplaces']) . '</td>
			<td class="number">' . locale_number_format($MyRow['price']*$MyRow['quantity'],$MyRow['currdecimalplaces']) . '</td>
			<td>' . $MyRow['currcode'] . '</td>
			<td>' . $MyRow['expirydate'] . '</td>
			<td><input type="radio" name="action' . $MyRow['offerid'] . '" value="1" /></td>
			<td><input type="radio" name="action' . $MyRow['offerid'] . '" value="2" /></td>
			<td><input type="radio" checked name="action' . $MyRow['offerid'] . '" value="3" /></td>
			<td><input type="hidden" name="supplierid" value="' . $MyRow['supplierid'] . '" /></td>
		</tr>';
	}
	echo '<tr>
			<td colspan="12">
				<div class="centre">
					<input type="submit" name="submit" value="' . _('Enter Information') . '" />
				</div>
			</td>
		</tr>
		</table>
        </div>
        </form>';
} else if(isset($_POST['submit']) and isset($_POST['supplierid'])) {
	include ('includes/htmlMimeMail.php');
	$Accepts=array();
	$RejectsArray=array();
	$Defers=array();
	foreach ($_POST as $key => $Value) {
		if(mb_substr($key,0,6)=='action') {
			$OfferID=mb_substr($key,6);
			switch ($Value) {
				case 1:
					$Accepts[]=$OfferID;
					break;
				case 2:
					$RejectsArray[]=$OfferID;
					break;
				case 3:
					$Defers[]=$OfferID;
					break;
			}
		}
	}
	if (sizeOf($Accepts)>0){
		$MailText=_('This email has been automatically generated by the webERP installation at'). ' ' . $_SESSION['CompanyRecord']['coyname'] . "\n";
		$MailText.=_('The following offers you made have been accepted')."\n";
		$MailText.=_('An official order will be sent to you in due course')."\n\n";
		$SQL="SELECT rate FROM currencies where currabrev='" . $CurrCode ."'";
		$Result=DB_query($SQL);
		$MyRow=DB_fetch_array($Result);
		$Rate=$MyRow['rate'];
		$OrderNo =  GetNextTransNo(18);
		$SQL="INSERT INTO purchorders (
					orderno,
					supplierno,
					orddate,
					rate,
					initiator,
					intostocklocation,
					deliverydate,
					status,
					stat_comment,
					paymentterms)
				VALUES (
					'" . $OrderNo."',
					'" . $_POST['supplierid'] . "',
					'".date('Y-m-d')."',
					'" . $Rate."',
					'" . $_SESSION['UserID'] . "',
					'" . $_SESSION['DefaultFactoryLocation'] . "',
					'".date('Y-m-d')."',
					'"._('Pending')."',
					'"._('Automatically generated from tendering system')."',
					'" . $PaymentTerms."')";
		DB_query($SQL);
		foreach ($Accepts as $AcceptID) {
			$SQL="SELECT offers.quantity,
							offers.price,
							offers.uom,
							stockmaster.description,
							stockmaster.stockid
						FROM offers
						LEFT JOIN stockmaster
							ON offers.stockid=stockmaster.stockid
						WHERE offerid='" . $AcceptID."'";
			$Result= DB_query($SQL);
			$MyRow=DB_fetch_array($Result);
			$MailText.=$MyRow['description'] . "\t"._('Quantity').' ' . $MyRow['quantity'] . "\t"._('Price').' '.
					locale_number_format($MyRow['price'])."\n";
			$SQL="INSERT INTO purchorderdetails (orderno,
												itemcode,
												deliverydate,
												itemdescription,
												unitprice,
												actprice,
												quantityord,
												suppliersunit)
									VALUES ('" . $OrderNo."',
											'" . $MyRow['stockid'] . "',
											'".date('Y-m-d')."',
											'".DB_escape_string($MyRow['description'])."',
											'" . $MyRow['price'] . "',
											'" . $MyRow['price'] . "',
											'" . $MyRow['quantity'] . "',
											'" . $MyRow['uom'] . "')";
			$Result=DB_query($SQL);
			$SQL="DELETE FROM offers WHERE offerid='" . $AcceptID."'";
			$Result=DB_query($SQL);
		}
		$mail = new htmlMimeMail();
		$mail->setSubject(_('Your offer to').' ' . $_SESSION['CompanyRecord']['coyname'].' ' . _('has been accepted'));
		$mail->setText($MailText);
		$Recipients = GetMailList('OffersReceivedResultRecipients');
		if (sizeOf($Recipients) == 0) {
			prnMsg( _('There are no members of the Offers Received Result Recipients email group'), 'warn');
			include('includes/footer.php');
			exit;
		}
		array_push($Recipients,$Email);
		if($_SESSION['SmtpSetting']==0){
			$mail->setFrom($_SESSION['CompanyRecord']['coyname'] . ' <' . $_SESSION['CompanyRecord']['email'] . '>');
			$Result = $mail->send($Recipients);
		}else{
			$Result = SendEmailByHTMLMimeMail($mail,$Recipients);
		}
		if($Result){
			prnMsg(_('The accepted offers from').' ' . $SupplierName.' ' . _('have been converted to purchase orders and an email sent to')
				.' ' . $Email."\n"._('Please review the order contents').' ' . '<a href="' . $RootPath .
				'/PO_Header.php?ModifyOrderNumber=' . $OrderNo.'">' . _('here') . '</a>', 'success');
		}else{
			prnMsg(_('The accepted offers from').' ' . $SupplierName.' ' . _('have been converted to purcharse orders but failed to mail, you can view the order contents').' ' . '<a href="' . $RootPath.'/PO_Header.php?ModifyOrderNumber=' . $OrderNo.'">' . _('here') . '</a>','warn');
		}
	}
	if (sizeOf($RejectsArray)>0){
		$MailText=_('This email has been automatically generated by the webERP installation at').' '.
		$_SESSION['CompanyRecord']['coyname'] . "\n";
		$MailText.=_('The following offers you made have been rejected')."\n\n";
		foreach ($RejectsArray as $RejectID) {
			$SQL="SELECT offers.quantity,
							offers.price,
							stockmaster.description
						FROM offers
						LEFT JOIN stockmaster
							ON offers.stockid=stockmaster.stockid
						WHERE offerid='" . $RejectID."'";
			$Result= DB_query($SQL);
			$MyRow=DB_fetch_array($Result);
			$MailText .= $MyRow['description'] . "\t" . _('Quantity') . ' ' . $MyRow['quantity'] . "\t" . _('Price') . ' ' . locale_number_format($MyRow['price'])."\n";
			$SQL="DELETE FROM offers WHERE offerid='" . $RejectID . "'";
			$Result=DB_query($SQL);
		}
		$mail = new htmlMimeMail();
		$mail->setSubject(_('Your offer to').' ' . $_SESSION['CompanyRecord']['coyname'].' ' . _('has been rejected'));
		$mail->setText($MailText);
		$mail->setFrom($_SESSION['CompanyRecord']['coyname'] . ' <' . $_SESSION['CompanyRecord']['email'] . '>');
		$Recipients = GetMailList('OffersReceivedResultRecipients');
		if (sizeOf($Recipients) == 0) {
			prnMsg( _('There are no members of the Offers Received Result Recipients email group'), 'warn');
			include('includes/footer.php');
			exit;
		}
		array_push($Recipients,$Email);
		if($_SESSION['SmtpSetting']==0){
			$mail->setFrom($_SESSION['CompanyRecord']['coyname'] . ' <' . $_SESSION['CompanyRecord']['email'] . '>');
			$Result = $mail->send($Recipients);
		}else{
			$Result = SendEmailByHTMLMimeMail($mail,$Recipients);
		}
		if($Result){
			prnMsg(_('The rejected offers from').' ' . $SupplierName.' ' . _('have been removed from the system and an email sent to')
				.' ' . $Email, 'success');
		}else{
			prnMsg(_('The rejected offers from').' ' . $SupplierName.' ' . _('have been removed from the system and but no email was not sent to')
				.' ' . $Email, 'warn');

		}
	}
	prnMsg(_('All offers have been processed, and emails sent where appropriate'), 'success');
}
include('includes/footer.php');

?>
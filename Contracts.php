<?php

/* $Id: Contract_Header.php 3325 2010-01-25 16:50:32Z tim_schofield $ */

$PageSecurity = 4;
include('includes/DefineContractClass.php');
include('includes/session.inc');

if (isset($_GET['ModifyContractNo'])) {
	$title = _('Modify Contract') . ' ' . $_GET['ModifyContractNo'];
} else {
	$title = _('Contract Entry');
}

if (isset($_GET['CustomerID'])) {
	$_ContractST['Select']=$_GET['CustomerID'];
}

include('includes/header.inc');
include('includes/SQL_CommonFunctions.inc');

/*If the page is called is called without an identifier being set then
 * it must be either a new contract, or the start of a modification of an
 * existing contract, and so we must create a new identifier.
 *
 * The identifier only needs to be unique for this php session, so a
 * unix timestamp will be sufficient.
 */

if (empty($_GET['identifier'])) {
	$identifier=date('U');
} else {
	$identifier=$_GET['identifier'];
}

/*Page is called with NewContract=Yes when a new order is to be entered
 * the session variable that holds all the Contract data $_SESSION['Contract'][$identifier]
 * is unset to allow all new details to be created */

if (isset($_GET['NewContract']) and isset($_SESSION['Contract'.$identifier])){
	unset($_SESSION['Contract'.$identifier]);
	$_SESSION['ExistingContract']=0;
}

if (isset($_ContractST['Select']) and empty($_ContractST['SupplierContact'])) {
	$sql = "SELECT contact
			FROM suppliercontacts
			WHERE CustomerID='". $_ContractST['Select'] ."'";

	$SuppCoResult = DB_query($sql,$db);
	if (DB_num_rows($SuppCoResult)>0) {
		$myrow = DB_fetch_row($SuppCoResult);
		$_ContractST['SupplierContact'] = $myrow[0];
	} else {
		$_ContractST['SupplierContact']='';
	}
}


if (isset($_GET['NewContract']) AND isset($_GET['SelectedCustomer'])) {
		/*
		* initialize a new contract
		*/
		$_SESSION['ExistingContract']=0;
		unset($_SESSION['Contract'.$identifier]);
		/* initialize new class object */
		$_SESSION['Contract'.$identifier] = new Contract;
		/**
		* and fill it with essential data
		*/
		$_SESSION['Contract'.$identifier]->AllowPrintContract = 1; /* Of course cos the
		* order aint even started !!*/
		$_SESSION['Contract'.$identifier]->GLLink = $_SESSION['CompanyRecord']['gllink_stock'];
		/* set the CustomerID we got */
		$_SESSION['Contract'.$identifier]->CustomerID = $_GET['SelectedCustomer'];
		/**/
		$_SESSION['RequireSupplierSelection'] = 0;
		/**/
		$_ContractST['Select'] = $_GET['SelectedCustomer'];

		/**
		* the item (its item code) that should be purchased
		*/
		$purch_item = $_GET['StockID'];

}

if (isset($_ContractST['EnterLines'])){
/*User hit the button to enter line items -
 *  ensure session variables updated then meta refresh to Contract_Items.php*/

	$_SESSION['Contract'.$identifier]->Location=$_ContractST['StkLocation'];
	$_SESSION['Contract'.$identifier]->SupplierContact=$_ContractST['SupplierContact'];
	$_SESSION['Contract'.$identifier]->DelAdd1 = $_ContractST['DelAdd1'];
	$_SESSION['Contract'.$identifier]->DelAdd2 = $_ContractST['DelAdd2'];
	$_SESSION['Contract'.$identifier]->DelAdd3 = $_ContractST['DelAdd3'];
	$_SESSION['Contract'.$identifier]->DelAdd4 = $_ContractST['DelAdd4'];
	$_SESSION['Contract'.$identifier]->DelAdd5 = $_ContractST['DelAdd5'];
	$_SESSION['Contract'.$identifier]->DelAdd6 = $_ContractST['DelAdd6'];
	$_SESSION['Contract'.$identifier]->suppDelAdd1 = $_ContractST['suppDelAdd1'];
	$_SESSION['Contract'.$identifier]->suppDelAdd2 = $_ContractST['suppDelAdd2'];
	$_SESSION['Contract'.$identifier]->suppDelAdd3 = $_ContractST['suppDelAdd3'];
	$_SESSION['Contract'.$identifier]->suppDelAdd4 = $_ContractST['suppDelAdd4'];
	$_SESSION['Contract'.$identifier]->suppDelAdd5 = $_ContractST['suppDelAdd5'];
	$_SESSION['Contract'.$identifier]->supptel= $_ContractST['supptel'];
	$_SESSION['Contract'.$identifier]->Initiator = $_ContractST['Initiator'];
	$_SESSION['Contract'.$identifier]->RequisitionNo = $_ContractST['Requisition'];
	$_SESSION['Contract'.$identifier]->version = $_ContractST['version'];
	$_SESSION['Contract'.$identifier]->deliverydate = $_ContractST['deliverydate'];
	$_SESSION['Contract'.$identifier]->revised = $_ContractST['revised'];
	$_SESSION['Contract'.$identifier]->ExRate = $_ContractST['ExRate'];
	$_SESSION['Contract'.$identifier]->Comments = $_ContractST['Comments'];
	$_SESSION['Contract'.$identifier]->deliveryby = $_ContractST['deliveryby'];
	$_SESSION['Contract'.$identifier]->StatusMessage = $_ContractST['StatComments'];
	$_SESSION['Contract'.$identifier]->paymentterms = $_ContractST['paymentterms'];
	$_SESSION['Contract'.$identifier]->contact = $_ContractST['Contact'];
	$_SESSION['Contract'.$identifier]->tel = $_ContractST['tel'];
	$_SESSION['Contract'.$identifier]->Contractrt = $_ContractST['Contractrt'];

	if (isset($_ContractST['RePrint']) and $_ContractST['RePrint']==1){

		$_SESSION['Contract'.$identifier]->AllowPrintContract=1;

		$sql = 'UPDATE purchorders
			SET purchorders.allowprint=1
			WHERE purchorders.orderno=' . $_SESSION['Contract'.$identifier]->OrderNo;

		$ErrMsg = _('An error occurred updating the Contract to allow reprints') . '. ' . _('The error says');
		$updateResult = DB_query($sql,$db,$ErrMsg);

	} else {
		$_ContractST['RePrint'] = 0;
	}

	echo "<meta http-equiv='Refresh' content='0; url=" . $rootpath . '/Contract_Items.php?' . SID . 'identifier='.$identifier. "'>";
	echo '<p>';
	prnMsg(_('You should automatically be forwarded to the entry of the Contract line items page') . '. ' .
		_('If this does not happen') . ' (' . _('if the browser does not supContractrt META Refresh') . ') ' .
		"<a href='$rootpath/Contract_Items.php?" . SID. 'identifier='.$identifier . "'>" . _('click here') . '</a> ' . _('to continue'),'info');
		include('includes/footer.inc');
		exit;
} /* end of if isset _ContractST'EnterLines' */

echo '<a href="'. $rootpath . '/Contract_SelectOSPurchOrder.php?' . SID . "identifier=".$identifier.'">'. _('Back to Contracts'). '</a><br>';

/*The page can be called with ModifyContractNo=x where x is a purchase
 * order number. The page then looks up the details of order x and allows
 * these details to be modified */

if (isset($_GET['ModifyContractNo'])){
	include ('includes/Contract_ReadInOrder.inc');
}

if (isset($_ContractST['CancelOrder']) AND $_ContractST['CancelOrder']!='') {
/*The cancel button on the header screen - to delete order */
	$OK_to_delete = 1;	 //assume this in the first instance

	if(!isset($_SESSION['ExistingContract']) OR $_SESSION['ExistingContract']!=0) {
		/* need to check that not already dispatched or invoiced
		 * by the supplier */

		if($_SESSION['Contract'.$identifier]->Any_Already_Received()==1){
			$OK_to_delete =0;
			prnMsg( _('This order cannot be cancelled because some of it has already been received') . '. ' .
				_('The line item quantities may be modified to quantities more than already received') . '. ' .
				_('Prices cannot be altered for lines that have already been received') .' '.
				_('and quantities cannot be reduced below the quantity already received'),'warn');
		}

	}

	if ($OK_to_delete==1){
		$emailsql='SELECT email FROM www_users WHERE userid="'.$_SESSION['Contract'.$identifier]->Initiator.'"';
		$emailresult=DB_query($emailsql, $db);
		$emailrow=DB_fetch_array($emailresult);
		$StatusComment=date($_SESSION['DefaultDateFormat']).
			' - Order Cancelled by <a href="mailto:'.$emailrow['email'].'">'.$_SESSION['UserID'].'</a><br>'.$_ContractST['statcommentscomplete'];
		unset($_SESSION['Contract'.$identifier]->LineItems);
		unset($_SESSION['Contract'.$identifier]);
		$_SESSION['Contract'.$identifier] = new PurchOrder;
		$_SESSION['RequireSupplierSelection'] = 1;

		if($_SESSION['ExistingContract']!=0){

			$sql = 'UPDATE purchorderdetails
				SET completed=1
				WHERE purchorderdetails.orderno =' . $_SESSION['ExistingContract'];
			$ErrMsg = _('The order detail lines could not be deleted because');
			$DelResult=DB_query($sql,$db,$ErrMsg);

			$sql="UPDATE purchorders
				SET status='".PurchOrder::STATUS_CANCELLED."',
				stat_comment='".$StatusComment."'
				WHERE orderno=".$_SESSION['ExistingContract'];

			$ErrMsg = _('The order header could not be deleted because');
			$DelResult=DB_query($sql,$db,$ErrMsg);
			prnMsg( _('Order number').' '.$_SESSION['ExistingContract'].' '._('has been cancelled'), 'success');
			unset($_SESSION['Contract'.$identifier]);
			unset($_SESSION['ExistingContract']);
		} else {
		// Re-Direct to right place
			unset($_SESSION['Contract'.$identifier]);
			prnMsg( _('The creation of the new order has been cancelled'), 'success');
		}
	}
}

if (!isset($_SESSION['Contract'.$identifier])){
	/* It must be a new order being created
	 * $_SESSION['Contract'.$identifier] would be set up from the order modification
	 * code above if a modification to an existing order. Also
	 * $ExistingContract would be set to 1. The delivery check screen
	 * is where the details of the order are either updated or
	 * inserted depending on the value of ExistingContract */

		$_SESSION['ExistingContract']=0;
		$_SESSION['Contract'.$identifier] = new PurchOrder;
		$_SESSION['Contract'.$identifier]->AllowPrintContract = 1; /*Of course cos the order aint even started !!*/
		$_SESSION['Contract'.$identifier]->GLLink = $_SESSION['CompanyRecord']['gllink_stock'];

		if ($_SESSION['Contract'.$identifier]->CustomerID=='' OR !isset($_SESSION['Contract'.$identifier]->CustomerID)){

/* a session variable will have to maintain if a supplier
 * has been selected for the order or not the session
 * variable CustomerID holds the supplier code already
 * as determined from user id /password entry  */
			$_SESSION['RequireSupplierSelection'] = 1;
		} else {
			$_SESSION['RequireSupplierSelection'] = 0;
		}

}

if (isset($_ContractST['ChangeSupplier'])) {

/* change supplier only allowed with appropriate permissions -
 * button only displayed to modify is AccessLevel >10
 * (see below)*/
	if ($_SESSION['Contract'.$identifier]->Stat == PurchOrder::STATUS_PENDING and $_SESSION['UserID']==$_SESSION['Contract'.$identifier]->Initiator) {
		if ($_SESSION['Contract'.$identifier]->Any_Already_Received()==0){
			$emailsql='SELECT email FROM www_users WHERE userid="'.$_SESSION['Contract'.$identifier]->Initiator.'"';
			$emailresult=DB_query($emailsql, $db);
			$emailrow=DB_fetch_array($emailresult);
			$date = date($_SESSION['DefaultDateFormat']);
			$_SESSION['RequireSupplierSelection']=1;
			$_SESSION['Contract'.$identifier]->Stat = PurchOrder::STATUS_PENDING;
			$StatusComment=$date.' - Supplier changed by <a href="mailto:'.$emailrow['email'].'">'.$_SESSION['UserID'].
				'</a> - '.$_ContractST['StatComments'].'<br>'.$_ContractST['statcommentscomplete'];
			$_SESSION['Contract'.$identifier]->StatComments=$StatusComment;
		} else {
			echo '<br><br>';
			prnMsg(_('Cannot modify the supplier of the order once some of the order has been received'),'warn');
		}
	}
}

$msg='';
if (isset($_ContractST['SearchSuppliers'])){

	if (strlen($_ContractST['Keywords'])>0 AND strlen($_SESSION['Contract'.$identifier]->CustomerID)>0) {
		$msg=_('Supplier name keywords have been used in preference to the supplier code extract entered');
	}
	if ($_ContractST['Keywords']=='' AND $_ContractST['SuppCode']=='') {
		$msg=_('At least one Supplier Name keyword OR an extract of a Supplier Code must be entered for the search');
	} else {
		if (strlen($_ContractST['Keywords'])>0) {
		//insert wildcard characters in spaces
			$SearchString = '%' . str_replace(' ', '%', $_ContractST['Keywords']) . '%';
			
			$SQL = "SELECT suppliers.CustomerID,
					suppliers.suppname,
					suppliers.address1,
					suppliers.address2,
					suppliers.address3,
					suppliers.address4,
					suppliers.address5,
					suppliers.address6,
					suppliers.currcode
				FROM suppliers
				WHERE suppliers.suppname " . LIKE . " '$SearchString'
				ORDER BY suppliers.suppname";

		} elseif (strlen($_ContractST['SuppCode'])>0){
			$SQL = "SELECT suppliers.CustomerID,
					suppliers.suppname,
					suppliers.address1,
					suppliers.address2,
					suppliers.address3,
					suppliers.address4,
					suppliers.address5,
					suppliers.address6,
					suppliers.currcode
				FROM suppliers
				WHERE suppliers.CustomerID " . LIKE . " '%" . $_ContractST['SuppCode'] . "%'
				ORDER BY suppliers.CustomerID";
		}

		$ErrMsg = _('The searched supplier records requested cannot be retrieved because');
		$result_SuppSelect = DB_query($SQL,$db,$ErrMsg);

		if (DB_num_rows($result_SuppSelect)==1){
			$myrow=DB_fetch_array($result_SuppSelect);
			$_ContractST['Select'] = $myrow['CustomerID'];
		} elseif (DB_num_rows($result_SuppSelect)==0){
			prnMsg( _('No supplier records contain the selected text') . ' - ' .
				_('please alter your search criteria and try again'),'info');
		}
	} /*one of keywords or SuppCode was more than a zero length string */
} /*end of if search for supplier codes/names */


// added by Hudson
if((!isset($_ContractST['SearchSuppliers']) or $_ContractST['SearchSuppliers']=='' ) AND
	(isset($_SESSION['Contract'.$identifier]->CustomerID) AND $_SESSION['Contract'.$identifier]->CustomerID!='')){

	/*The session variables are set but the form variables have been lost
	 * need to restore the form variables from the session */
	$_ContractST['CustomerID']=$_SESSION['Contract'.$identifier]->CustomerID;
	$_ContractST['SupplierName']=$_SESSION['Contract'.$identifier]->SupplierName;
	$_ContractST['CurrCode'] = $_SESSION['Contract'.$identifier]->CurrCode;
	$_ContractST['ExRate'] = $_SESSION['Contract'.$identifier]->ExRate;
	$_ContractST['paymentterms'] = $_SESSION['Contract'.$identifier]->paymentterms;
	$_ContractST['DelAdd1']=$_SESSION['Contract'.$identifier]->DelAdd1;
	$_ContractST['DelAdd2']=$_SESSION['Contract'.$identifier]->DelAdd2;
	$_ContractST['DelAdd3']=$_SESSION['Contract'.$identifier]->DelAdd3;
	$_ContractST['DelAdd4']=$_SESSION['Contract'.$identifier]->DelAdd4;
	$_ContractST['DelAdd5']=$_SESSION['Contract'.$identifier]->DelAdd5;
	$_ContractST['DelAdd6']=$_SESSION['Contract'.$identifier]->DelAdd6;
	$_ContractST['suppDelAdd1']=$_SESSION['Contract'.$identifier]->suppDelAdd1;
	$_ContractST['suppDelAdd2']=$_SESSION['Contract'.$identifier]->suppDelAdd2;
	$_ContractST['suppDelAdd3']=$_SESSION['Contract'.$identifier]->suppDelAdd3;
	$_ContractST['suppDelAdd4']=$_SESSION['Contract'.$identifier]->suppDelAdd4;
	$_ContractST['suppDelAdd5']=$_SESSION['Contract'.$identifier]->suppDelAdd5;
	$_ContractST['suppDelAdd6']=$_SESSION['Contract'.$identifier]->suppDelAdd6;

}

if (isset($_ContractST['Select'])) {

/* will only be true if page called from supplier selection form
 * or set because only one supplier record returned from a search
 * so parse the $Select string into supplier code and branch code */
	$sql='SELECT currcode FROM suppliers where CustomerID="'.$_ContractST['Select'].'"';
	$result=DB_query($sql,$db);
	$myrow=DB_fetch_array($result);
	$SupplierCurrCode=$myrow['currcode'];

	$authsql='SELECT cancreate
			FROM purchorderauth
			WHERE userid="'.$_SESSION['UserID'].'"
			AND currabrev="'.$SupplierCurrCode.'"';

	$authresult=DB_query($authsql,$db);

	$sql = "SELECT suppliers.suppname,
		suppliers.currcode,
		currencies.rate,
		suppliers.paymentterms,
                        suppliers.address1,
                        suppliers.address2,
                        suppliers.address3,
                        suppliers.address4,
                        suppliers.address5,
                        suppliers.address6,
                        suppliers.phn,
                        suppliers.Contractrt
		FROM suppliers INNER JOIN currencies
		ON suppliers.currcode=currencies.currabrev
		WHERE CustomerID='" . $_ContractST['Select'] . "'";

	$ErrMsg = _('The supplier record of the supplier selected') . ': ' . $_ContractST['Select'] . ' ' .
		_('cannot be retrieved because');
	$DbgMsg = _('The SQL used to retrieve the supplier details and failed was');
	$result =DB_query($sql,$db,$ErrMsg,$DbgMsg);
	$myrow = DB_fetch_row($result);
	$SupplierName = $myrow[0];
		// added for suppliers lookup fields

	if (($authmyrow=DB_fetch_array($authresult) and $authmyrow[0]==0 ) ) {
		$_ContractST['SupplierName'] = $myrow[0];
		$_ContractST['CurrCode'] = 	$myrow[1];
		$_ContractST['ExRate'] = 	$myrow[2];
		$_ContractST['paymentterms']=	$myrow[3];
		$_ContractST['suppDelAdd1'] = $myrow[4];
		$_ContractST['suppDelAdd2'] = $myrow[5];
		$_ContractST['suppDelAdd3'] = $myrow[6];
		$_ContractST['suppDelAdd4'] = $myrow[7];
		$_ContractST['suppDelAdd5'] = $myrow[8];
		$_ContractST['suppDelAdd6'] = $myrow[9];
		$_ContractST['supptel'] = $myrow[10];
		$_ContractST['Contractrt'] = $myrow[11];

		$_SESSION['Contract'.$identifier]->CustomerID = $_ContractST['Select'];
		$_SESSION['RequireSupplierSelection'] = 0;
		$_SESSION['Contract'.$identifier]->SupplierName = $_ContractST['SupplierName'];
		$_SESSION['Contract'.$identifier]->CurrCode = $_ContractST['CurrCode'];
		$_SESSION['Contract'.$identifier]->ExRate = $_ContractST['ExRate'];
		$_SESSION['Contract'.$identifier]->paymentterms = $_ContractST['paymentterms'];
		$_SESSION['Contract'.$identifier]->suppDelAdd1 = $_ContractST['suppDelAdd1'];
		$_SESSION['Contract'.$identifier]->suppDelAdd2 = $_ContractST['suppDelAdd2'];
		$_SESSION['Contract'.$identifier]->suppDelAdd3 = $_ContractST['suppDelAdd3'];
		$_SESSION['Contract'.$identifier]->suppDelAdd4 = $_ContractST['suppDelAdd4'];
		$_SESSION['Contract'.$identifier]->suppDelAdd5 = $_ContractST['suppDelAdd5'];
		$_SESSION['Contract'.$identifier]->suppDelAdd6 = $_ContractST['suppDelAdd6'];
		$_SESSION['Contract'.$identifier]->supptel = $_ContractST['supptel'];
		$_SESSION['Contract'.$identifier]->Contractrt = $_ContractST['Contractrt'];
	} else {
		prnMsg( _('You do not have the authority to raise Contracts for ').
			$SupplierName.'. '._('Please Consult your system administrator for more information').'. '
			._('You can setup authorisations ').'<a href=Contract_AuthorisationLevels.php>'._('here').'.</a>', 'warn');
		include('includes/footer.inc');
		exit;
	}

	// end of added for suppliers lookup fields

} else {
	$_ContractST['Select'] = $_SESSION['Contract'.$identifier]->CustomerID;
	$sql = "SELECT suppliers.suppname,
			suppliers.currcode,
			suppliers.paymentterms,
			suppliers.address1,
			suppliers.address2,
			suppliers.address3,
			suppliers.address4,
			suppliers.address5,
			suppliers.address6,
			suppliers.phn,
			suppliers.Contractrt
		FROM suppliers INNER JOIN currencies
		ON suppliers.currcode=currencies.currabrev
		WHERE CustomerID='" . $_ContractST['Select'] . "'";

	$ErrMsg = _('The supplier record of the supplier selected') . ': ' . $_ContractST['Select'] . ' ' .
		_('cannot be retrieved because');
	$DbgMsg = _('The SQL used to retrieve the supplier details and failed was');
	$result =DB_query($sql,$db,$ErrMsg,$DbgMsg);


	$myrow = DB_fetch_row($result);


	// added for suppliers lookup fields
	if (!isset($_SESSION['Contract'.$identifier])) {
		$_ContractST['SupplierName'] = $myrow[0];
		$_ContractST['CurrCode'] = 	$myrow[1];
		$_ContractST['paymentterms']=	$myrow[2];
		$_ContractST['suppDelAdd1'] = $myrow[3];
		$_ContractST['suppDelAdd2'] = $myrow[4];
		$_ContractST['suppDelAdd3'] = $myrow[5];
		$_ContractST['suppDelAdd4'] = $myrow[6];
		$_ContractST['suppDelAdd5'] = $myrow[7];
		$_ContractST['suppDelAdd6'] = $myrow[8];
		$_ContractST['supptel'] = $myrow[9];
		$_ContractST['Contractrt'] = $myrow[10];

		$_SESSION['Contract'.$identifier]->CustomerID = $_ContractST['Select'];
		$_SESSION['RequireSupplierSelection'] = 0;
		$_SESSION['Contract'.$identifier]->SupplierName = $_ContractST['SupplierName'];
		$_SESSION['Contract'.$identifier]->CurrCode = $_ContractST['CurrCode'];
		$_SESSION['Contract'.$identifier]->ExRate = $_ContractST['ExRate'];
		$_SESSION['Contract'.$identifier]->paymentterms = $_ContractST['paymentterms'];
		$_SESSION['Contract'.$identifier]->suppDelAdd1 = $_ContractST['suppDelAdd1'];
		$_SESSION['Contract'.$identifier]->suppDelAdd2 = $_ContractST['suppDelAdd2'];
		$_SESSION['Contract'.$identifier]->suppDelAdd3 = $_ContractST['suppDelAdd3'];
		$_SESSION['Contract'.$identifier]->suppDelAdd4 = $_ContractST['suppDelAdd4'];
		$_SESSION['Contract'.$identifier]->suppDelAdd5 = $_ContractST['suppDelAdd5'];
		$_SESSION['Contract'.$identifier]->suppDelAdd6 = $_ContractST['suppDelAdd6'];
		$_SESSION['Contract'.$identifier]->supptel = $_ContractST['supptel'];
		$_SESSION['Contract'.$identifier]->Contractrt = $_ContractST['Contractrt'];
	// end of added for suppliers lookup fields
	}
}

// MADE THE SUPPILERS BECOME SELECT MENU NOT BY SEARCHING By Hudson @2008/6/30

// part of step 1
if ($_SESSION['RequireSupplierSelection'] ==1 OR !isset($_SESSION['Contract'.$identifier]->CustomerID) OR
		$_SESSION['Contract'.$identifier]->CustomerID=='' ) {
//if (true) {
	echo '<p class="page_title_text"><img src="'.$rootpath.'/css/'.$theme.'/images/supplier.png" title="' .
		_('Contract') . '" alt="">' . ' ' . _('Contract: Select Supplier') . '';
	echo "<form action='" . $_SERVER['PHP_SELF'] . '?' . SID . "identifier=".$identifier."' method=Contractst name='choosesupplier'>";
	if (strlen($msg)>1){
		prnMsg($msg,'warn');
	}

	echo '<table cellpadding=3 colspan=4>
	<tr>
	<td><font size=1>' . _('Enter text in the supplier name') . ":</font></td>
	<td><input type='Text' name='Keywords' size=20	maxlength=25></td>
	<td><font size=3><b>" . _('OR') . '</b></font></td>
	<td><font size=1>' . _('Enter text extract in the supplier code') . ":</font></td>
	<td><input type='text' name='SuppCode' size=15	maxlength=18></td>
	</tr>
	</table><br><div class='centre'>
	<input type=submit name='SearchSuppliers' value=" . _('Search Now') . ">
	<input type=submit action=reset value='" . _('Reset') . "'></div>";

	echo '<script  type="text/javascript">defaultControl(document.forms[0].Keywords);</script>';

// UPDATED BY HUDSON 30/6/2008

	if (isset($result_SuppSelect)) {

		echo '<br><table cellpadding=3 colspan=7 border=1>';

		$tableheader = "<tr>
				<th>" . _('Code') . "</th>
				<th>" . _('Supplier Name') . "</th>
				<th>" . _('Address') . "</th>
				<th>" . _('Currency') . '</th>
				</tr>';

		echo $tableheader;

		$j = 1;
		$k = 0; /*row counter to determine background colour */

		while ($myrow=DB_fetch_array($result_SuppSelect)) {

			if ($k==1){
				echo '<tr class="EvenTableRows">';
				$k=0;
			} else {
				echo '<tr class="OddTableRows">';
				$k++;
			}

			echo "<td><input type='submit' style='width:100%' name='Select' value='".$myrow['CustomerID']."' ></td>
				<td>".$myrow['suppname']."</td><td>";

			for ($i=1; $i<=6; $i++) {
				if ($myrow['address'.$i] != '') {
					echo $myrow['address'.$i].'<br>';
				}
			}
			echo "</td><td>".$myrow['currcode']."</td></tr>";

			//end of page full new headings if
		}
//end of while loop

		echo '</table>';

	}
//end if results to show

//end if RequireSupplierSelection
} else {
/* everything below here only do if a supplier is selected */

	echo "<form name='form1' action='" . $_SERVER['PHP_SELF'] . '?' . SID . "identifier=".$identifier. "' method=Contractst>";

// Be careful not made confused by orderno and realorderno
//	$orderno = previous_id("purchorders","orderno");
//    	$_SESSION['Contract'.$identifier]->OrderNo2 = $orderno;
	echo '<p class="page_title_text">
            <img src="'.$rootpath.'/css/'.$theme.'/images/supplier.png" title="' . _('Contract') . '" alt="">
	        ' . $_SESSION['Contract'.$identifier]->SupplierName . ' - ' . _('All amounts stated in') . '
            ' . $_SESSION['Contract'.$identifier]->CurrCode . '<br />';
	if ($_SESSION['ExistingContract']) {
		echo  _(' Modify Contract Number') . ' ' . $_SESSION['Contract'.$identifier]->OrderNo;

    echo '</p>';

	}

/* 2008-08-19 ToPu -- debugging purContractse */
	if (isset($purch_item)) {
		prnMsg(_('Purchase Item(s) with this code') . ': ' .  $purch_item,'info');

		/**
		 * 2008-08-21 ToPu
		 * Now go ahead to Contract_Items.php
		 * with NewItem=$purch_item
		 */
		/* a somewhat nice outfit for that link */
		echo "<div class='centre'>";
		echo '<br><table class="table_index"><tr><td class="menu_group_item">';

		/* the link */
		echo '<li><a href="'.$rootpath.'/Contract_Items.php?' . SID . 'NewItem=' . $purch_item . "&identifier=".$identifier. '">' .
			_('Enter Line Item to this Contract') . '</a></li>';
		/**/
		echo "</td></tr></table></div><br>";

		if (isset($_GET['Quantity'])) {
			$Qty=$_GET['Quantity'];
		} else {
			$Qty=1;
		}

		$sql='SELECT
					controlled,
					serialised,
					description,
					units ,
					decimalplaces
				FROM stockmaster
				WHERE stockid="'.$purch_item.'" ';
		$result=DB_query($sql, $db);
		$stockmasterrow=DB_fetch_array($result);

		$sql='SELECT
					price,
					suppliersuom,
					suppliers_partno
				FROM purchdata
				WHERE supplierno="'.$_GET['SelectedCustomer'] .'"
				AND stockid="'.$purch_item.'" ';
		$result=DB_query($sql, $db);
		$purchdatarow=DB_fetch_array($result);

		$sql='SELECT
					stockact
				FROM stockcategory
				LEFT JOIN stockmaster ON stockmaster.categoryid=stockcategory.categoryid
				WHERE stockid="'.$purch_item.'" ';
		$result=DB_query($sql, $db);
		$categoryrow=DB_fetch_array($result);

		$_SESSION['Contract'.$identifier]->add_to_order(
				1,
				$purch_item,
				$stockmasterrow['serialised'],
				$stockmasterrow['controlled'],
				$Qty,
				$stockmasterrow['description'],
				$purchdatarow['price'],
				$stockmasterrow['units'],
				$categoryrow['stockact'],
				date($_SESSION['DefaultDateFormat']),
				0,
				0,
				'',
				0,
				0,
				'',
				$stockmasterrow['decimalplaces'],
				$purch_item,
				$purchdatarow['suppliersuom'],
				$purchdatarow['suppliers_partno'],
				$Qty*$purchdatarow['price'],
				'',
				0,
				0,
				0,
				0,
				$Qty,
				$Qty*$purchdatarow['price']);
		echo "<meta http-equiv='Refresh' content='0; url=" . $rootpath . '/Contract_Items.php?' . SID . 'identifier='.$identifier. "'>";
	}

	/*Set up form for entry of order header stuff */

	if (!isset($_ContractST['LookupDeliveryAddress']) and (!isset($_ContractST['StkLocation']) or $_ContractST['StkLocation'])
		AND (isset($_SESSION['Contract'.$identifier]->Location) AND $_SESSION['Contract'.$identifier]->Location != '')) {
		/* The session variables are set but the form variables have
	     * been lost --
	     * need to restore the form variables from the session */
	    $_ContractST['StkLocation']=$_SESSION['Contract'.$identifier]->Location;
		$_ContractST['SupplierContact']=$_SESSION['Contract'.$identifier]->SupplierContact;
	    $_ContractST['DelAdd1']=$_SESSION['Contract'.$identifier]->DelAdd1;
	    $_ContractST['DelAdd2']=$_SESSION['Contract'.$identifier]->DelAdd2;
	    $_ContractST['DelAdd3']=$_SESSION['Contract'.$identifier]->DelAdd3;
	    $_ContractST['DelAdd4']=$_SESSION['Contract'.$identifier]->DelAdd4;
	    $_ContractST['DelAdd5']=$_SESSION['Contract'.$identifier]->DelAdd5;
	    $_ContractST['DelAdd6']=$_SESSION['Contract'.$identifier]->DelAdd6;
	    $_ContractST['Initiator']=$_SESSION['Contract'.$identifier]->Initiator;
	    $_ContractST['Requisition']=$_SESSION['Contract'.$identifier]->RequisitionNo;
	    $_ContractST['version']=$_SESSION['Contract'.$identifier]->version;
	    $_ContractST['deliverydate']=$_SESSION['Contract'.$identifier]->deliverydate;
	    $_ContractST['revised']=$_SESSION['Contract'.$identifier]->revised;
	    $_ContractST['ExRate']=$_SESSION['Contract'.$identifier]->ExRate;
	    $_ContractST['Comments']=$_SESSION['Contract'.$identifier]->Comments;
	    $_ContractST['deliveryby']=$_SESSION['Contract'.$identifier]->deliveryby;
	    $_ContractST['paymentterms']=$_SESSION['Contract'.$identifier]->paymentterms;
	}

// move apart by Hudson
	echo '<br><table border=1 colspan=1 width=80%>
		<tr>
			<td><font color=blue size=4><b>' . _('Order Initiation Details') . '</b></font></td>

			<td><font color=blue size=4><b>' . _('Order Status') . '</b></font></td>
		</tr>		<tr><td style="width:50%">';

	echo '<table>';
	echo '<tr><td>' . _('Contract Date') . ':</td><td>';
	if ($_SESSION['ExistingContract']!=0){
		echo ConvertSQLDate($_SESSION['Contract'.$identifier]->Orig_OrderDate);
	} else {
		/* DefaultDateFormat defined in config.php */
		echo Date($_SESSION['DefaultDateFormat']);
	}
	echo '</td></tr>';

	$date = date($_SESSION['DefaultDateFormat']);

	if (isset($_GET['ModifyContractNo']) && $_GET['ModifyContractNo'] != '') {
		$_SESSION['Contract'.$identifier]->version += 1;
		$_ContractST['version'] =  $_SESSION['Contract'.$identifier]->version;
	} elseif (isset($_SESSION['Contract'.$identifier]->version) and $_SESSION['Contract'.$identifier]->version != '') {
		$_ContractST['version'] =  $_SESSION['Contract'.$identifier]->version;
	} else {
		$_ContractST['version']='1';
	}

	if (!isset($_ContractST['deliverydate'])) {
		$_ContractST['deliverydate']= date($_SESSION['DefaultDateFormat']);
	}

	echo '<tr><td>' . _('Version'). ' #' . ":</td><td><input type='hidden' name='version' size=16 maxlength=15
		value='" . $_ContractST['version'] . "'>".$_ContractST['version']."</td></tr>";
	echo '<tr><td>' . _('Revised') . ":</td><td><input type='hidden' name='revised' size=11 maxlength=15 value=" .
		$date . '>'.$date.'</td></tr>';

	echo '<tr><td>' . _('Delivery Date') . ":</td><td><input type='text' class=date alt='".$_SESSION['DefaultDateFormat'].
		"' name='deliverydate' size=11 value=" . $_ContractST['deliverydate'] . '>'."</td></tr>";

	if (!isset($_ContractST['Initiator'])) {
		$_ContractST['Initiator'] = $_SESSION['UserID'];
		$_ContractST['Requisition'] = '';
	}

	echo '<tr><td>' . _('Initiated By') . ":</td>
			<td><input type='hidden' name='Initiator' size=11 maxlength=10 value=" .
			$_ContractST['Initiator'] . ">".$_ContractST['Initiator']."</td></tr>";
	echo '<tr><td>' . _('Requisition Ref') . ":</td><td><input type='text' name='Requisition' size=16
		maxlength=15 value=" . $_ContractST['Requisition'] . '></td></tr>';

//	echo '<tr><td>' . _('Exchange Rate') . ":</td>
//			<td><input type=TEXT name='ExRate' size=16 maxlength=15 VALUE=" . $_ContractST['ExRate'] . '></td>
//	echo "<input type='hidden' name='ExRate' size=16 maxlength=15 value=" . $_ContractST['ExRate'] . "></td>";
//		</tr>';
	echo '<tr><td>' . _('Date Printed') . ':</td><td>';

	if (isset($_SESSION['Contract'.$identifier]->DatePurchaseOrderPrinted) AND strlen($_SESSION['Contract'.$identifier]->DatePurchaseOrderPrinted)>6){
		echo ConvertSQLDate($_SESSION['Contract'.$identifier]->DatePurchaseOrderPrinted);
		$Printed = True;
	} else {
		$Printed = False;
		echo _('Not yet printed');
	}

	if (isset($_ContractST['AllowRePrint'])) {
		$sql='UPDATE purchorders SET allowprint=1 WHERE orderno='.$_SESSION['Contract'.$identifier]->OrderNo;
		$result=DB_query($sql, $db);
	}

	if ($_SESSION['Contract'.$identifier]->AllowPrintContract==0 AND empty($_ContractST['RePrint'])){
		echo '<tr><td>' . _('Allow Reprint') . ":</td><td><select name='RePrint' onChange='ReloadForm(form1.AllowRePrint)'><option selected value=0>" .
			_('No') . "<option value=1>" . _('Yes') . '</select></td>';
		echo '<td><input type=submit name="AllowRePrint" value="Update"></td></tr>';
	} elseif ($Printed) {
		echo "<tr><td colspan=2><a target='_blank'  href='$rootpath/Contract_PDFPurchOrder.php?" .
			SID . "OrderNo=" . $_SESSION['ExistingContract'] . "&identifier=".$identifier. "'>" . _('Reprint Now') . '</a></td></tr>';
	}

	echo '</table>';

	echo '<td style="width:50%"><table>';
	if($_SESSION['ExistingContract'] != 0 and $_SESSION['Contract'.$identifier]->Stat == PurchOrder::STATUS_PRINTED){
		echo '<tr><td><a href="' .$rootpath . "/GoodsReceived.php?" . SID . "&ContractNumber=" .
			$_SESSION['Contract'.$identifier]->OrderNo . "&identifier=".$identifier.'">'._('Receive this order').'</a></td></tr>';
	}
	echo '<td>' . _('Status') . ' :  </td><td><select name=Stat onChange="ReloadForm(form1.UpdateStat)">';

	switch ($_SESSION['Contract'.$identifier]->Stat) {
		case '':
			$StatusList = array(PurchOrder::STATUS_NEW_ORDER);
			break;
		case PurchOrder::STATUS_PENDING:
			$StatusList = array(PurchOrder::STATUS_PENDING, PurchOrder::STATUS_AUTHORISED,
                                PurchOrder::STATUS_REJECTED, PurchOrder::STATUS_CANCELLED);
			break;
		case PurchOrder::STATUS_AUTHORISED:
			$StatusList = array(PurchOrder::STATUS_PENDING, PurchOrder::STATUS_AUTHORISED,
                                PurchOrder::STATUS_CANCELLED);
			break;
		case PurchOrder::STATUS_REJECTED:
			$StatusList = array(PurchOrder::STATUS_PENDING, PurchOrder::STATUS_AUTHORISED,
                                PurchOrder::STATUS_REJECTED, PurchOrder::STATUS_CANCELLED);
			break;
		case PurchOrder::STATUS_CANCELLED:
			$StatusList = array(PurchOrder::STATUS_PENDING, PurchOrder::STATUS_CANCELLED);
			break;
		case PurchOrder::STATUS_PRINTED:
			$StatusList = array(PurchOrder::STATUS_PENDING, PurchOrder::STATUS_PRINTED,
                                PurchOrder::STATUS_CANCELLED);
			break;
		case PurchOrder::STATUS_COMPLITED:
			$StatusList = array(PurchOrder::STATUS_COMPLITED);
			break;
		default:
			$StatusList = array(PurchOrder::STATUS_NEW_ORDER, PurchOrder::STATUS_PENDING,
                                PurchOrder::STATUS_AUTHORISED, PurchOrder::STATUS_REJECTED,
                                PurchOrder::STATUS_CANCELLED);
			break;
	}

	foreach ($StatusList as $Status) {
		if ($_SESSION['Contract'.$identifier]->Stat == $Status){
			echo '<option selected value="' . $Status . '">' . _($Status) . '</option>';
		} else {
			echo '<option value="'.$Status.'">' . _($Status) . '</option>';
		}
	}
	echo '</select></td></tr>';

	echo '<tr><td>' . _('Status Comment');
	echo ":</td><td><input type=text name='StatComments' size=50></td></tr><tr><td colspan=2><b>" . html_entity_decode($_SESSION['Contract'.$identifier]->StatComments) .'</b></td></tr>';
	echo "<input type=hidden name='statcommentscomplete' value='".$_SESSION['Contract'.$identifier]->StatComments."'>";
	echo '<tr><td><input type="submit" name=UpdateStat value="' . _("Status Update") .'"></td>';

	echo "<td><input type='submit' name='CancelOrder' value='" . _("Cancel and Delete Order") . "'></td></tr>";
	echo '</table></td>';

// end of move by Hudson

	echo '<table border=1 width=80%>
		<tr>
		<td><font color=blue size=4><b>' . _('Warehouse Info') . '</b></font></td>
		<!--	<td><font color=blue size=4><b>' . _('Delivery To') . '</b></font></td> -->
			<td><font color=blue size=4><b>' . _('Supplier Info') . '</b></font></td>
		</tr>
		<tr><td valign=top>';
	/*nested table level1 */

	echo '<table><tr><td>' . _('Warehouse') . ':</td>
			<td><select name=StkLocation onChange="ReloadForm(form1.LookupDeliveryAddress)">';

	$sql = 'SELECT loccode,
				locationname
		FROM locations';
	$LocnResult = DB_query($sql,$db);

	while ($LocnRow=DB_fetch_array($LocnResult)){
		if (isset($_ContractST['StkLocation']) and ($_ContractST['StkLocation'] == $LocnRow['loccode'] OR
				($_ContractST['StkLocation']=='' AND $LocnRow['loccode']==$_SESSION['UserStockLocation']))){
			echo "<option selected value='" . $LocnRow['loccode'] . "'>" . $LocnRow['locationname'];
		} else {
			echo "<option value='" . $LocnRow['loccode'] . "'>" . $LocnRow['locationname'];
		}
	}

	echo '</select>
		<input type="submit" name="LookupDeliveryAddress" value="' ._('Select') . '"></td>
		</tr>';

/* If this is the first time
 * the form loaded set up defaults */

	if (!isset($_ContractST['StkLocation']) OR $_ContractST['StkLocation']==''){

		$_ContractST['StkLocation'] = $_SESSION['UserStockLocation'];

		$sql = "SELECT deladd1,
	 			deladd2,
				deladd3,
				deladd4,
				deladd5,
				deladd6,
				tel,
				contact
			FROM locations
			WHERE loccode='" . $_ContractST['StkLocation'] . "'";

		$LocnAddrResult = DB_query($sql,$db);
		if (DB_num_rows($LocnAddrResult)==1){
			$LocnRow = DB_fetch_row($LocnAddrResult);
			$_ContractST['DelAdd1'] = $LocnRow[0];
			$_ContractST['DelAdd2'] = $LocnRow[1];
			$_ContractST['DelAdd3'] = $LocnRow[2];
			$_ContractST['DelAdd4'] = $LocnRow[3];
			$_ContractST['DelAdd5'] = $LocnRow[4];
			$_ContractST['DelAdd6'] = $LocnRow[5];
			$_ContractST['tel'] = $LocnRow[6];
			$_ContractST['Contact'] = $LocnRow[7];

			$_SESSION['Contract'.$identifier]->Location= $_ContractST['StkLocation'];
//			$_SESSION['Contract'.$identifier]->SupplierContact= $_ContractST['SupplierContact'];
			$_SESSION['Contract'.$identifier]->DelAdd1 = $_ContractST['DelAdd1'];
			$_SESSION['Contract'.$identifier]->DelAdd2 = $_ContractST['DelAdd2'];
			$_SESSION['Contract'.$identifier]->DelAdd3 = $_ContractST['DelAdd3'];
			$_SESSION['Contract'.$identifier]->DelAdd4 = $_ContractST['DelAdd4'];
			$_SESSION['Contract'.$identifier]->DelAdd5 = $_ContractST['DelAdd5'];
			$_SESSION['Contract'.$identifier]->DelAdd6 = $_ContractST['DelAdd6'];
			$_SESSION['Contract'.$identifier]->tel = $_ContractST['tel'];
			$_SESSION['Contract'.$identifier]->contact = $_ContractST['Contact'];

		} else {
			 /*The default location of the user is crook */
			prnMsg(_('The default stock location set up for this user is not a currently defined stock location') .
				'. ' . _('Your system administrator needs to amend your user record'),'error');
		}


	} elseif (isset($_ContractST['LookupDeliveryAddress'])){

		$sql = "SELECT deladd1,
				deladd2,
				deladd3,
				deladd4,
				deladd5,
				deladd6,
				tel,
				contact
			FROM locations
			WHERE loccode='" . $_ContractST['StkLocation'] . "'";

		$LocnAddrResult = DB_query($sql,$db);
		if (DB_num_rows($LocnAddrResult)==1){
			$LocnRow = DB_fetch_row($LocnAddrResult);
			$_ContractST['DelAdd1'] = $LocnRow[0];
			$_ContractST['DelAdd2'] = $LocnRow[1];
			$_ContractST['DelAdd3'] = $LocnRow[2];
			$_ContractST['DelAdd4'] = $LocnRow[3];
			$_ContractST['DelAdd5'] = $LocnRow[4];
			$_ContractST['DelAdd6'] = $LocnRow[5];
			$_ContractST['tel'] = $LocnRow[6];
			$_ContractST['Contact'] = $LocnRow[7];

			$_SESSION['Contract'.$identifier]->Location= $_ContractST['StkLocation'];
			$_SESSION['Contract'.$identifier]->DelAdd1 = $_ContractST['DelAdd1'];
			$_SESSION['Contract'.$identifier]->DelAdd2 = $_ContractST['DelAdd2'];
			$_SESSION['Contract'.$identifier]->DelAdd3 = $_ContractST['DelAdd3'];
			$_SESSION['Contract'.$identifier]->DelAdd4 = $_ContractST['DelAdd4'];
			$_SESSION['Contract'.$identifier]->DelAdd5 = $_ContractST['DelAdd5'];
			$_SESSION['Contract'.$identifier]->DelAdd6 = $_ContractST['DelAdd6'];
			$_SESSION['Contract'.$identifier]->tel = $_ContractST['tel'];
			$_SESSION['Contract'.$identifier]->contact = $_ContractST['Contact'];
		}
	}


	echo '<tr><td>' . _('Delivery Contact') . ":</td>
		<td><input type='text' name=Contact size=41  value='" . $_SESSION['Contract'.$identifier]->contact . "'></td>
		</tr>";
	echo '<tr><td>' . _('Address') . " 1 :</td>
		<td><input type='text' name=DelAdd1 size=41 maxlength=40 value='" . $_ContractST['DelAdd1'] . "'></td>
		</tr>";
	echo '<tr><td>' . _('Address') . " 2 :</td>
		<td><input type='text' name=DelAdd2 size=41 maxlength=40 value='" . $_ContractST['DelAdd2'] . "'></td>
		</tr>";
	echo '<tr><td>' . _('Address') . " 3 :</td>
		<td><input type='text' name=DelAdd3 size=41 maxlength=40 value='" . $_ContractST['DelAdd3'] . "'></td>
		</tr>";
	echo '<tr><td>' . _('Address') . " 4 :</td>
		<td><input type='text' name=DelAdd4 size=21 maxlength=20 value='" . $_ContractST['DelAdd4'] . "'></td>
		</tr>";
	echo '<tr><td>' . _('Address') . " 5 :</td>
		<td><input type='text' name=DelAdd5 size=16 maxlength=15 value='" . $_ContractST['DelAdd5'] . "'></td>
		</tr>";
	echo '<tr><td>' . _('Address') . " 6 :</td>
		<td><input type='text' name=DelAdd6 size=16 maxlength=15 value='" . $_ContractST['DelAdd6'] . "'></td>
		</tr>";
	echo '<tr><td>' . _('Phone') . ":</td>
		<td><input type='text' name=tel size=31 maxlength=30 value='" . $_SESSION['Contract'.$identifier]->tel . "'></td>
		</tr>";

	echo '<tr><td>' . _('Delivery By') . ':</td><td><select name=deliveryby>';

	$sql = 'SELECT shipper_id, shippername FROM shippers';
	$shipperResult = DB_query($sql,$db);

	while ($shipperRow=DB_fetch_array($shipperResult)){
		if (isset($_ContractST['deliveryby']) and ($_ContractST['deliveryby'] == $shipperRow['shipper_id'])) {
			echo "<option selected value='" . $shipperRow['shipper_id'] . "'>" . $shipperRow['shippername'];
		} else {
			echo "<option value='" . $shipperRow['shipper_id'] . "'>" . $shipperRow['shippername'];
		}
	}

	echo '</select></tr></table>';
	  /* end of sub table */

	echo '</td><td>'; /*sub table nested */
	echo '<table><tr><td>' . _('Supplier Selection') . ':</td><td>
		<select name=Keywords onChange="ReloadForm(form1.SearchSuppliers)">';

	$sql = "SELECT CustomerID,suppname FROM suppliers ORDER BY suppname";
	$SuppCoResult = DB_query($sql,$db);

	while ( $SuppCoRow=DB_fetch_array($SuppCoResult)){
		if ($SuppCoRow['suppname'] == $_SESSION['Contract'.$identifier]->SupplierName) {
			echo "<option selected value='" . $SuppCoRow['suppname'] . "'>" . $SuppCoRow['suppname'];
		} else {
			echo "<option value='" . $SuppCoRow['suppname'] . "'>" . $SuppCoRow['suppname'];
		}
	}

	echo '</select> ';
	echo '<input type="submit" name="SearchSuppliers" value=' . _('Select Now') . '"></td></tr>';

// END of added <input type=submit action=RESET VALUE="' . _('Reset')

	echo '</td></tr><tr><td>' . _('Supplier Contact') . ':</td><td>
		<select name=SupplierContact>';

	$sql = "SELECT contact FROM suppliercontacts WHERE CustomerID='". $_ContractST['Select'] ."'";

	$SuppCoResult = DB_query($sql,$db);

	while ( $SuppCoRow=DB_fetch_array($SuppCoResult)){
		if ($_ContractST['SupplierContact'] == $SuppCoRow['contact'] OR ($_ContractST['SupplierContact']==''
			AND $SuppCoRow['contact']==$_SESSION['Contract'.$identifier]->SupplierContact)){
			//if (1) {
			echo "<option selected value='" . $SuppCoRow['contact'] . "'>" . $SuppCoRow['contact'];
		} else {
			echo "<option value='" . $SuppCoRow['contact'] . "'>" . $SuppCoRow['contact'];
		}
	}

	echo '</select> ';
	echo '</td></tr>';

	echo '<tr><td>' . _('Address') . " 1 :</td>
		</td><td><input type='text' name=suppDelAdd1 size=41 maxlength=40 value='" . $_ContractST['suppDelAdd1'] . "'></td>
		</tr>";
	echo '<tr><td>' . _('Address') . " 2 :</td>
		</td><td><input type='text' name=suppDelAdd2 size=41 maxlength=40 value='" . $_ContractST['suppDelAdd2'] . "'></td>
		</tr>";
	echo '<tr><td>' . _('Address') . " 3 :</td>
		</td><td><input type='text' name=suppDelAdd3 size=41 maxlength=40 value='" . $_ContractST['suppDelAdd3'] . "'></td>
		</tr>";
	echo '<tr><td>' . _('Address') . " 4 :</td>
		</td><td><input type='text' name=suppDelAdd5 size=21 maxlength=20 value='" . $_ContractST['suppDelAdd5'] . "'></td>
		</tr>";
	echo '<tr><td>' . _('Address') . " 5 :</td>
		</td><td><input type='text' name=suppDelAdd4 size=41 maxlength=40 value='" . $_ContractST['suppDelAdd4'] . "'></td>
		</tr>";
	echo '<tr><td>' . _('Phone') . ":
		</td><td><input type='text' name=supptel size=31 maxlength=30 value='" . $_SESSION['Contract'.$identifier]->supptel  . "'></td>
		</tr>";

	$result=DB_query('SELECT terms, termsindicator FROM paymentterms', $db);

	echo '<tr><td>' . _('Payment Terms') . ":</td><td><select name='paymentterms'>";

	while ($myrow = DB_fetch_array($result)) {
		if ($myrow['termsindicator']==$_SESSION['Contract'.$identifier]->paymentterms) {
			echo "<option selected value='". $myrow['termsindicator'] . "'>" . $myrow['terms'];
		} else {
			echo "<option value='". $myrow['termsindicator'] . "'>" . $myrow['terms'];
		} //end while loop
	}
	DB_data_seek($result, 0);
	echo '</select></td></tr>';

	$result=DB_query("SELECT loccode, locationname FROM locations WHERE loccode='" . $_SESSION['Contract'.$identifier]->Contractrt."'", $db);
	$myrow = DB_fetch_array($result);
	$_ContractST['Contractrt'] = $myrow['locationname'];

	echo '<tr><td>' . _('Delivery To') . ":
		</td><td><input type='text' name=Contractrt size=31 value='" . $_ContractST['Contractrt'] . "'></td>
		</tr>";

	if ($_SESSION['Contract'.$identifier]->CurrCode != $_SESSION['CompanyRecord']['currencydefault']) {
		echo '<tr><td>'. _('Exchange Rate').':'.'</td><td><input type=text name="ExRate"
		value='.$_ContractST['ExRate'].' class=number size=11></td></tr>';
	} else {
		echo '<input type=hidden name="ExRate" value="1">';
	}
	echo '</td></tr></table>'; /*end of sub table */

	echo '</td></tr><tr><th colspan=4>' . _('Comments');

	$Default_Comments = '';

	if (!isset($_ContractST['Comments'])) {
		$_ContractST['Comments']=$Default_Comments;
	}

	echo ":</th></tr><tr><td colspan=4><textarea name='Comments' style='width:100%' rows=5>" . $_ContractST['Comments'] . '</textarea>';

	echo '</table>';

	echo '</td></tr></table><br>'; /* end of main table */
	// discard change supplier submit buttom
	// kept enter line item but remove Delete button by Hudson 11/16,and added status field
	echo "<div class='centre'>
  		<input type=submit name='EnterLines' value='" . _('Enter Line Items') . "'></div>";
	// Delete Contract when necessrary

} /*end of if supplier selected */

echo '</form>';
include('includes/footer.inc');
?>

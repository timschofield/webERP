<?php

/* $Revision: 1.22 $ */

/*
 *      PO_Header.php
 *
 * 	webERP Linux-Apache-MySQL-PHP based accounting system
 *	Copyright Logic Works Ltd - 2007 - Contact: info@weberp.org
 *
 *  This program is free software; you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation; either version 2 of the License, or
 *  (at your option) any later version.
 *
 *  This program is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *
 *  You should have received a copy of the GNU General Public License
 *  along with this program; if not, write to the Free Software
 *  Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston,
 *  MA 02110-1301, USA.
 *
 *
 *  modifications and add-ons started from:
 *   Revision: 1.18
 *  by Thomas Pulina
 *  <hollerland@nord-com.net>|<thomas.pulina@wagner-bremen.de>
 *  2008
 *
 */


$PageSecurity = 4;
include('includes/DefinePOClass.php');
include('includes/session.inc');

if (isset($_GET['ModifyOrderNumber'])) {
	$title = _('Modify Purchase Order') . ' ' . $_GET['ModifyOrderNumber'];
} else {
	$title = _('Purchase Order Entry');
}

include('includes/header.inc');
include('includes/SQL_CommonFunctions.inc');

/* Page is called with NewOrder=Yes when a new order is to be entered
 * the session variable that holds all the PO data $_SESSION['PO']
 * is unset to allow all new details to be created */

if (isset($_GET['NewOrder']) and isset($_SESSION['PO'])) {
	unset($_SESSION['PO']);
	$_SESSION['ExistingOrder']=0;
}

/**
 * 2008-08-19 ToPu at WAT
 * add extra functionality:
 * enter a purchase order with the item's stockid and maintained
 * suppliers (in purchdata) form the screen "Search Inventory Items"
 * of SelectProduct.php
 */
if (isset($_GET['NewOrder']) and isset($_GET['StockID']) and isset($_GET['SelectedSupplier'])) {
	/**
	 * initialize a new order
	 */
	$_SESSION['ExistingOrder']=0;
	unset($_SESSION['PO']);
	/* initialize new class object */
	$_SESSION['PO'] = new PurchOrder;
	/**
	 * and fill it with essential data
	 */
	$_SESSION['PO']->AllowPrintPO = 1; /* Of course cos the
	* order aint even started !!*/
	$_SESSION['PO']->GLLink = $_SESSION['CompanyRecord']['gllink_stock'];
	/* set the SupplierID we got */
	$_SESSION['PO']->SupplierID = $_GET['SelectedSupplier'];
	/**/
	$_SESSION['RequireSupplierSelection'] = 0;
	/**/
	$_POST['Select'] = $_GET['SelectedSupplier'];

	/**
	 * the item (its item code) that should be purchased
	 */
	$purch_item = $_GET['StockID'];

}


If (isset($_POST['EnterLines'])) {
/* User hit the button to enter line items -
 * ensure session variables updated then meta refresh to PO_Items.php */

	$_SESSION['PO']->Location = $_POST['StkLocation'];
	$_SESSION['PO']->DelAdd1 = $_POST['DelAdd1'];
	$_SESSION['PO']->DelAdd2 = $_POST['DelAdd2'];
	$_SESSION['PO']->DelAdd3 = $_POST['DelAdd3'];
	$_SESSION['PO']->DelAdd4 = $_POST['DelAdd4'];
	$_SESSION['PO']->DelAdd5 = $_POST['DelAdd5'];
	$_SESSION['PO']->DelAdd6 = $_POST['DelAdd6'];
	$_SESSION['PO']->Initiator = $_POST['Initiator'];
	$_SESSION['PO']->RequisitionNo = $_POST['Requisition'];
	$_SESSION['PO']->ExRate = $_POST['ExRate'];
	$_SESSION['PO']->Comments = $_POST['Comments'];

	if (isset($_POST['RePrint']) and $_POST['RePrint'] == 1) {

		$_SESSION['PO']->AllowPrintPO = 1;

		$sql = 'UPDATE purchorders
			SET purchorders.allowprint=1
			WHERE purchorders.orderno=' . $_SESSION['PO']->OrderNo;

		$ErrMsg = _('An error occurred updating the purchase order to allow reprints') . '. ' . _('The error says');
		$updateResult = DB_query($sql,$db,$ErrMsg);

	}

	echo "<META HTTP-EQUIV='Refresh' CONTENT='0; URL=" . $rootpath . '/PO_Items.php?' . SID . "'>";
	echo '<P>';
	prnMsg(_('You should automatically be forwarded to the entry of the purchase order line items page') . '. ' . _('If this does not happen') . ' (' . _('if the browser does not support META Refresh') . ') ' . "<a href='$rootpath/PO_Items.php?" . SID . "'>" . _('click here') . '</a> ' . _('to continue'),'info');

} /* end of if isset _POST'EnterLines' */


echo '<A HREF="'. $rootpath . '/PO_SelectOSPurchOrder.php?' . SID . '">'. _('Back to Purchase Orders'). '</A><BR>';

/* The page can be called with ModifyOrderNumber=x where x is a purchase
 * order number. The page then looks up the details of order x and allows
 * these details to be modified */

if (isset($_GET['ModifyOrderNumber'])) {
      include ('includes/PO_ReadInOrder.inc');
}

if (isset($_POST['CancelOrder']) AND $_POST['CancelOrder'] != '') {
	/* The cancel button on the header screen - to delete order */
	$OK_to_delete = 1;	 //assume this in the first instance

	if(!isset($_SESSION['ExistingOrder']) OR $_SESSION['ExistingOrder']!=0) {
		/* need to check that not already dispatched or invoiced
		 * by the supplier */

		if($_SESSION['PO']->Any_Already_Received()==1){
			$OK_to_delete =0;
			prnMsg( _('This order cannot be cancelled because some of it has already been received') . '. ' . _('The line item quantities may be modified to quantities more than already received') . '. ' . _('Prices cannot be altered for lines that have already been received and quantities cannot be reduced below the quantity already received'),'warn');
		}

	}

	if ($OK_to_delete == 1) {
		unset($_SESSION['PO']->LineItems);
		unset($_SESSION['PO']);
		$_SESSION['PO'] = new PurchOrder;
		$_SESSION['RequireSupplierSelection'] = 1;

		if($_SESSION['ExistingOrder'] != 0) {

			$SQL = 'DELETE FROM purchorderdetails WHERE purchorderdetails.orderno =' . $_SESSION['ExistingOrder'];
			$ErrMsg = _('The order detail lines could not be deleted because');
			$DelResult=DB_query($SQL,$db,$ErrMsg);

			$SQL = 'DELETE FROM purchorders WHERE purchorders.orderno=' . $_SESSION['ExistingOrder'];
			$ErrMsg = _('The order header could not be deleted because');
			$DelResult=DB_query($SQL,$db,$ErrMsg);
		 }
	}
}

if (!isset($_SESSION['PO'])) {
	/* It must be a new order being created.
	 * $_SESSION['PO'] would be set up from the order modification
	 * code above if a modification to an existing order. Also
	 * $ExistingOrder would be set to 1. The delivery check screen
	 * is where the details of the order are either updated or
	 * inserted depending on the value of ExistingOrder */

	$_SESSION['ExistingOrder'] = 0;
	$_SESSION['PO'] = new PurchOrder;
	$_SESSION['PO']->AllowPrintPO = 1; /* Of course cos the order aint even started !!*/
	$_SESSION['PO']->GLLink = $_SESSION['CompanyRecord']['gllink_stock'];

	if ($_SESSION['PO']->SupplierID == '' OR !isset($_SESSION['PO']->SupplierID)) {
		/* a session variable will have to maintain if a supplier
		 * has been selected for the order or not the session
		 * variable supplierID holds the supplier code already
		 * as determined from user id /password entry  */
		$_SESSION['RequireSupplierSelection'] = 1;
	} else {
		$_SESSION['RequireSupplierSelection'] = 0;
	}
}

if (isset($_POST['ChangeSupplier'])) {
	/* change supplier only allowed with appropriate permissions -
	 * button only displayed to modify is AccessLevel >10
	 *  (see below) */
	if ($_SESSION['PO']->Any_Already_Received() == 0) {
		$_SESSION['RequireSupplierSelection'] = 1;
	} else {
		echo '<BR><BR>';
		prnMsg(_('Cannot modify the supplier of the order once some of the order has been received'),'warn');
	}
}

$msg='';
if (isset($_POST['SearchSuppliers'])) {

	If (strlen($_POST['Keywords']) > 0 AND strlen($_POST['SuppCode']) > 0) {
		$msg = _('Supplier name keywords have been used in preference to the supplier code extract entered');
	}
	If ($_POST['Keywords'] == '' AND $_POST['SuppCode'] == '') {
		$msg = _('At least one Supplier Name keyword OR an extract of a Supplier Code must be entered for the search');
	} else {
		If (strlen($_POST['Keywords']) > 0) {
		//insert wildcard characters in spaces

			$i = 0;
			$SearchString = '%';
			while (strpos($_POST['Keywords'], ' ', $i)) {
				$wrdlen = strpos($_POST['Keywords'], ' ', $i) - $i;
				$SearchString=$SearchString . substr($_POST['Keywords'],$i,$wrdlen) . '%';
				$i = strpos($_POST['Keywords'], ' ' ,$i) + 1;
			}
			$SearchString = $SearchString . substr($_POST['Keywords'],$i) . '%';
			$SQL = "SELECT suppliers.supplierid,
					suppliers.suppname,
					suppliers.currcode
				FROM suppliers
				WHERE suppliers.suppname " . LIKE . " '$SearchString'
				ORDER BY suppliers.suppname";

		} elseif (strlen($_POST['SuppCode']) > 0) {
			$SQL = "SELECT suppliers.supplierid,
					suppliers.suppname,
					suppliers.currcode
				FROM suppliers
				WHERE suppliers.supplierid " . LIKE . " '%" . $_POST['SuppCode'] . "%'
				ORDER BY suppliers.supplierid";
		}

		$ErrMsg = _('The searched supplier records requested cannot be retrieved because');
		$result_SuppSelect = DB_query($SQL,$db,$ErrMsg);

		if (DB_num_rows($result_SuppSelect) == 1) {
			$myrow = DB_fetch_array($result_SuppSelect);
			$_POST['Select'] = $myrow['supplierid'];
		} elseif (DB_num_rows($result_SuppSelect) == 0) {
			prnMsg( _('No supplier records contain the selected text') . ' - ' . _('please alter your search criteria and try again'),'info');
		}
	} /*one of keywords or SuppCode was more than a zero length string */
} /*end of if search for supplier codes/names */


if (isset($_POST['Select'])) {
	/* will only be true if page called from supplier selection form
	 * or set because only one supplier record returned from a search
	 * so parse the $Select string into supplier code and branch code
	 * */
	$sql = "SELECT suppliers.suppname,
			suppliers.currcode,
			currencies.rate
		FROM suppliers INNER JOIN currencies
		ON suppliers.currcode=currencies.currabrev
		WHERE supplierid='" . $_POST['Select'] . "'";

	$ErrMsg = _('The supplier record of the supplier selected') . ': ' . $_POST['Select'] . ' ' . _('cannot be retrieved because');
	$DbgMsg = _('The SQL used to retrieve the supplier details and failed was');
	$result = DB_query($sql,$db,$ErrMsg,$DbgMsg);


	$myrow = DB_fetch_row($result);
	$_SESSION['PO']->SupplierID = $_POST['Select'];
	$_SESSION['RequireSupplierSelection'] = 0;
	$_SESSION['PO']->SupplierName = $myrow[0];
	$_SESSION['PO']->CurrCode = $myrow[1];
	$_SESSION['PO']->ExRate = $myrow[2];
	$_POST['ExRate'] = $myrow[2];
}


if ($_SESSION['RequireSupplierSelection'] == 1 OR !isset($_SESSION['PO']->SupplierID) OR $_SESSION['PO']->SupplierID == '' ) {

	echo '<P CLASS="page_title_text"><img src="'.$rootpath.'/css/'.$theme.'/images/supplier.png" TITLE="' . _('Purchase Order') . '" ALT="">' . ' ' . _('Purchase Order: Select Supplier') . '';
	echo "<FORM ACTION='" . $_SERVER['PHP_SELF'] . '?' . SID . "' METHOD=POST>";
	if (strlen($msg)>1){
		prnMsg($msg,'warn');
	}

	echo '<TABLE CELLPADDING=3 COLSPAN=4>
	<TR>
	<TD><FONT SIZE=1>' . _('Enter text in the supplier name') . ":</FONT></TD>
	<TD><INPUT TYPE='Text' NAME='Keywords' SIZE=20	MAXLENGTH=25></TD>
	<TD><FONT SIZE=3><B>" . _('OR') . '</B></FONT></TD>
	<TD><FONT SIZE=1>' . _('Enter text extract in the supplier code') . ":</FONT></TD>
	<TD><INPUT TYPE='Text' NAME='SuppCode' SIZE=15	MAXLENGTH=18></TD>
	</TR>
	</TABLE>
	<CENTER><INPUT TYPE=SUBMIT NAME='SearchSuppliers' VALUE=" . _('Search Now') . ">
	<INPUT TYPE=SUBMIT ACTION=RESET VALUE='" . _('Reset') . "'></CENTER>";

	If (isset($result_SuppSelect)) {

		echo '<BR><CENTER><TABLE CELLPADDING=3 COLSPAN=7 BORDER=1>';

		$tableheader = "<TR>
				<TH>" . _('Code') . "</TH>
				<TH>" . _('Supplier Name') . "</TH>
				<TH>" . _('Currency') . '</TH>
				</TR>';

		echo $tableheader;

		$j = 1;
		$k = 0; /* row counter to determine background colour */

		while ($myrow = DB_fetch_array($result_SuppSelect)) {

			if ($k == 1){
				echo '<tr class="EvenTableRows">';
				$k = 0;
			} else {
				echo '<tr class="OddTableRows">';
				$k++;
			}

			printf("<td><INPUT TYPE=SUBMIT NAME='Select' VALUE='%s'</td>
				<td>%s</td>
				<td>%s</td>
				</tr>",
				$myrow['supplierid'],
				$myrow['suppname'],
				$myrow['currcode']);

			$j++;
			If ($j == 11){
				$j = 1;
				echo $tableheader;
			}
			// end of page full new headings if
		}
		// end of while loop

		echo '</TABLE></CENTER>';

	}
	// end if results to show

//end if RequireSupplierSelection
} else {
	/* everything below here only do if a supplier is selected */

	echo "<FORM ACTION='" . $_SERVER['PHP_SELF'] . '?' . SID . "' METHOD=POST>";
	
	echo '<P CLASS="page_title_text"><img src="'.$rootpath.'/css/'.$theme.'/images/supplier.png" TITLE="' . _('Purchase Order') . '" ALT="">' . ' ' . _('Purchase Order') . '';

	echo ':' . $_SESSION['PO']->OrderNo . ' ' . $_SESSION['PO']->SupplierName . '</U> </B></FONT> - ' . _('All amounts stated in') . ' ' . $_SESSION['PO']->CurrCode . '<BR><BR>';
	echo '<CENTER>';

	/* 2008-08-19 ToPu -- debugging purpose */
	if (isset($purch_item)) {
		prnMsg(_('Purchase Item(s) with this code') . ': ' .  $purch_item,'info');

		/**
		 * 2008-08-21 ToPu
		 * Now go ahead to PO_Items.php
		 * with NewItem=$purch_item
		 */
		/* a somewhat nice outfit for that link */
		echo "<div align=center>";
		echo '<br><table class="table_index"><tr><td class="menu_group_item">';

		/* the link */
		echo '<li><a href="'.$rootpath.'/PO_Items.php?' . SID . 'NewItem=' . $purch_item . '">' . _('Enter Line Item to this purchase order') . '</a></li>';
		/**/
		echo "</td></tr></table></div><br>";
	}

	/* Set up form for entry of order header stuff */

	if (!isset($_POST['LookupDeliveryAddress']) and (!isset($_POST['StkLocation']) or $_POST['StkLocation']) AND (isset($_SESSION['PO']->Location) AND $_SESSION['PO']->Location != '')) {
	    /* The session variables are set but the form variables have
	     * been lost --
	     * need to restore the form variables from the session */
	    $_POST['StkLocation'] = $_SESSION['PO']->Location;
	    $_POST['DelAdd1'] = $_SESSION['PO']->DelAdd1;
	    $_POST['DelAdd2'] = $_SESSION['PO']->DelAdd2;
	    $_POST['DelAdd3'] = $_SESSION['PO']->DelAdd3;
	    $_POST['DelAdd4'] = $_SESSION['PO']->DelAdd4;
	    $_POST['DelAdd5'] = $_SESSION['PO']->DelAdd5;
	    $_POST['DelAdd6'] = $_SESSION['PO']->DelAdd6;
	    $_POST['Initiator'] = $_SESSION['PO']->Initiator;
	    $_POST['Requisition'] = $_SESSION['PO']->RequisitionNo;
	    $_POST['ExRate'] = $_SESSION['PO']->ExRate;
	    $_POST['Comments'] = $_SESSION['PO']->Comments;
	}

	echo '<TABLE BORDER=1>
		<TR>
			<TD><FONT COLOR=BLUE SIZE=4><B>' . _('Delivery To') . '</B></FONT></TD>
			<TD><FONT COLOR=BLUE SIZE=4><B>' . _('Order Initiation Details') . '</B></FONT></TD>
		</TR>
		<TR><TD>';
	/* nested table level1 */
	echo '<TABLE>
			<TR>
				<TD>' . _('Receive Into') . ':</TD>
				<TD><SELECT NAME=StkLocation>';

	$sql = 'SELECT loccode, locationname FROM locations';
	$LocnResult = DB_query($sql,$db);

	while ($LocnRow = DB_fetch_array($LocnResult)) {
		if (isset($_POST['StkLocation']) and ($_POST['StkLocation'] == $LocnRow['loccode'] OR ($_POST['StkLocation'] == '' AND $LocnRow['loccode'] == $_SESSION['UserStockLocation']))){
			echo "<OPTION SELECTED Value='" . $LocnRow['loccode'] . "'>" . $LocnRow['locationname'];
		} else {
			echo "<OPTION Value='" . $LocnRow['loccode'] . "'>" . $LocnRow['locationname'];
		}
	}
	echo '</SELECT>
		<INPUT TYPE=SUBMIT NAME="LookupDeliveryAddress" VALUE="' ._('Lookup Address') . '"></TD>
		</TR>';

	if (!isset($_POST['StkLocation']) OR $_POST['StkLocation'] == '') {
		/* If this is the first time
		 * the form loaded set up defaults */
		$_POST['StkLocation'] = $_SESSION['UserStockLocation'];
		$sql = "SELECT deladd1,
				deladd2,
				deladd3,
				deladd4,
				deladd5,
				deladd6,
				tel
			FROM locations
			WHERE loccode='" . $_POST['StkLocation'] . "'";
		$LocnAddrResult = DB_query($sql,$db);
		if (DB_num_rows($LocnAddrResult) == 1) {
			$LocnRow = DB_fetch_row($LocnAddrResult);
			$_POST['DelAdd1'] = $LocnRow[0];
			$_POST['DelAdd2'] = $LocnRow[1];
			$_POST['DelAdd3'] = $LocnRow[2];
			$_POST['DelAdd4'] = $LocnRow[3];
			$_POST['DelAdd5'] = $LocnRow[4];
			$_POST['DelAdd6'] = $LocnRow[5];
			$_POST['tel'] = $LocnRow[6];
			$_SESSION['PO']->Location= $_POST['StkLocation'];
			$_SESSION['PO']->DelAdd1 = $_POST['DelAdd1'];
			$_SESSION['PO']->DelAdd2 = $_POST['DelAdd2'];
			$_SESSION['PO']->DelAdd3 = $_POST['DelAdd3'];
			$_SESSION['PO']->DelAdd4 = $_POST['DelAdd4'];
			$_SESSION['PO']->DelAdd5 = $_POST['DelAdd5'];
			$_SESSION['PO']->DelAdd6 = $_POST['DelAdd6'];
			$_SESSION['PO']->tel = $_POST['tel'];

		} else {
	 		/* The default location of the user is crook */
		  	prnMsg(_('The default stock location set up for this user is not a currently defined stock location') . '. ' . _('Your system administrator needs to amend your user record'),'error');
		}
	} elseif (isset($_POST['LookupDeliveryAddress'])) {
		$sql = "SELECT deladd1,
				deladd2,
				deladd3,
				deladd4,
				deladd5,
				deladd6,
				tel
			FROM locations
			WHERE loccode='" . $_POST['StkLocation'] . "'";

		$LocnAddrResult = DB_query($sql,$db);
		if (DB_num_rows($LocnAddrResult) == 1) {
			$LocnRow = DB_fetch_row($LocnAddrResult);
			$_POST['DelAdd1'] = $LocnRow[0];
			$_POST['DelAdd2'] = $LocnRow[1];
			$_POST['DelAdd3'] = $LocnRow[2];
			$_POST['DelAdd4'] = $LocnRow[3];
			$_POST['DelAdd5'] = $LocnRow[4];
			$_POST['DelAdd6'] = $LocnRow[5];
			$_POST['tel'] = $LocnRow[6];
			$_SESSION['PO']->Location= $_POST['StkLocation'];
			$_SESSION['PO']->DelAdd1 = $_POST['DelAdd1'];
			$_SESSION['PO']->DelAdd2 = $_POST['DelAdd2'];
			$_SESSION['PO']->DelAdd3 = $_POST['DelAdd3'];
			$_SESSION['PO']->DelAdd4 = $_POST['DelAdd4'];
			$_SESSION['PO']->DelAdd5 = $_POST['DelAdd5'];
			$_SESSION['PO']->DelAdd6 = $_POST['DelAdd6'];
			$_SESSION['PO']->tel = $_POST['tel'];
		}
	}
	echo '<TR><TD>' . _('Deliver to') . ' - ' . _('Address 1') . ":</TD>
	  		<TD><INPUT TYPE=text NAME=DelAdd1 SIZE=41 MAXLENGTH=40 Value='" . $_POST['DelAdd1'] . "'></TD>
		</TR>";
	echo '<TR><TD>' . _('Deliver to') . ' - ' . _('Address 2') . ":</TD>
	  		<TD><INPUT TYPE=text NAME=DelAdd2 SIZE=41 MAXLENGTH=40 Value='" . $_POST['DelAdd2'] . "'></TD>
		</TR>";
	echo '<TR><TD>' . _('Deliver to') . ' - ' . _('Address 3') . ":</TD>
		<TD><INPUT TYPE=text NAME=DelAdd3 SIZE=41 MAXLENGTH=40 Value='" . $_POST['DelAdd3'] . "'></TD>
		</TR>";
	echo '<TR><TD>' . _('Deliver to') . ' - ' . _('Address 4') . ":</TD>
		<TD><INPUT TYPE=text NAME=DelAdd4 SIZE=41 MAXLENGTH=40 Value='" . $_POST['DelAdd4'] . "'></TD>
		</TR>";
	echo '<TR><TD>' . _('Deliver to') . ' - ' . _('Address 5') . ":</TD>
		<TD><INPUT TYPE=text NAME=DelAdd5 SIZE=21 MAXLENGTH=20 Value='" . $_POST['DelAdd5'] . "'></TD>
		</TR>";
	echo '<TR><TD>' . _('Deliver to') . ' - ' . _('Address 6') . ":</TD>
		<TD><INPUT TYPE=text NAME=DelAdd6 SIZE=16 MAXLENGTH=15 Value='" . $_POST['DelAdd6'] . "'></TD>
		</TR>";
		if ( !isset($_POST['tel']) ) { $_POST['tel']=''; }
	echo '<TR><TD>' . _('Deliver to') . ' - ' . _('Phone') . ":</TD>
		<TD><INPUT TYPE=text NAME=tel SIZE=31 MAXLENGTH=30 Value='" . $_POST['tel'] . "'></TD>
		</TR>";
	echo '</TABLE>'; /* end of sub table */

	{
	/* start of sub table nested */
	echo '</TD><TD>'; /* sub table nested */

	echo '<TABLE><TR><TD>' . _('Originally Ordered') . ':</TD><TD>';
	if ($_SESSION['ExistingOrder'] != 0) {
		echo ConvertSQLDate($_SESSION['PO']->Orig_OrderDate);
	} else {
		/* DefaultDateFormat defined in config.php */
		echo Date($_SESSION['DefaultDateFormat']);
	}

	echo '</TD></TR>';
	if (!isset($_POST['Initiator'])) {
		$_POST['Initiator'] = '';
	}
	echo '<TR><TD>' . _('Initiated By') . ":</TD>
		<TD><INPUT TYPE=TEXT NAME='Initiator' SIZE=11 MAXLENGTH=10 VALUE=" . $_POST['Initiator'] . "></TD>
		</TR>";
		if (!isset($_POST['Requisition'])) {
			$_POST['Requisition'] = 0;
		}
	echo '<TR><TD>' . _('Requistion Ref') . ":</TD><TD><INPUT TYPE=TEXT NAME='Requisition' SIZE=16 MAXLENGTH=15 VALUE=" . $_POST['Requisition'] . '></TD>
		</TR>';

	echo '<TR><TD>' . _('Exchange Rate') . ":</TD>
		<TD><INPUT TYPE=TEXT NAME='ExRate' SIZE=16 MAXLENGTH=15 VALUE=" . $_POST['ExRate'] . '></TD>
		</TR>';
	echo '<TR><TD>' . _('Date Printed') . ':</TD><TD>';

	if (isset($_SESSION['PO']->DatePurchaseOrderPrinted) AND strlen($_SESSION['PO']->DatePurchaseOrderPrinted) > 6) {
		echo ConvertSQLDate($_SESSION['PO']->DatePurchaseOrderPrinted);
		$Printed = True;
	} else {
		$Printed = False;
		echo _('Not yet printed');
	}

	if ($_SESSION['PO']->AllowPrintPO==0 AND $_POST['RePrint'] != 1) {
		echo '<TR><TD>' . _('Allow Reprint') . ":</TD><TD><SELECT NAME='RePrint'><OPTION SELECTED VALUE=0>" . _('No') . "<OPTION VALUE=1>" . _('Yes') . '</SELECT></TD></TR>';
	} elseif ($Printed) {
		echo "<TR><TD COLSPAN=2 ALIGN=CENTER><A target='_blank'  HREF='$rootpath/PO_PDFPurchOrder.php?" . SID . "OrderNo=" . $_SESSION['ExistingOrder'] . "'>" . _('Reprint Now') . '</A></TD></TR>';
	}
	echo '</TD></TR></TABLE>';
	/* end of sub table nested */
	}

	echo '</TD></TR><TR><TD VALIGN=TOP COLSPAN=2>' . _('Comments');
	if (!isset($_POST['Comments'])) {
		$_POST['Comments']='';
	}
	echo ":<textarea name='Comments' cols=70 rows=2>" . AddCarriageReturns($_POST['Comments']) . '</textarea>';
	echo '</TD></TR></TABLE>'; /* end of main table */
	echo "<INPUT TYPE=SUBMIT Name='EnterLines' VALUE='" . _('Enter Line Items') . "'><INPUT TYPE=SUBMIT Name='ChangeSupplier' VALUE='" . _('Change supplier') . "'><BR><BR><INPUT TYPE=SUBMIT NAME='CancelOrder' VALUE='" . _("Cancel and Delete The Whole Order") . "'>";

} /*end of if supplier selected */

echo '</form>';
include('includes/footer.inc');
?>

<?php

include('includes/session.php');
$NumDays = 90;
$Title = _('Returned Items Maintenance for the last ') . $NumDays . ' days';
include('includes/header.php');
include('includes/UIGeneralFunctions.php'); 
include('includes/KLUIGeneralFunctions.php');


if (isset($_POST['SelectedReturnedItemsId'])){
	$SelectedReturnedItemsId = mb_strtoupper($_POST['SelectedReturnedItemsId']);
}
elseif (isset($_GET['SelectedReturnedItemsId'])){
	$SelectedReturnedItemsId = mb_strtoupper($_GET['SelectedReturnedItemsId']);
}

if (isset($Errors)) {
	unset($Errors);
}

$Errors = array();

echo '<p class="page_title_text"><img src="'.$RootPath.'/css/'.$Theme.'/images/maintenance.png" title="' . _('Search') . '" alt="" />' . ' ' . $Title . '</p>';

if (isset($_POST['submit'])) {

	$_POST['orderno']=mb_strtoupper($_POST['orderno']);
	$_POST['itemcodes']=mb_strtoupper($_POST['itemcodes']);
	$_POST['oldinvoice']=mb_strtoupper($_POST['oldinvoice']);

	//initialise no input errors assumed initially before we test
	$InputError = 0;

	/* actions to take once the user has clicked the submit button
	ie the page has called itself with some user input */

	//first off validate inputs sensible
	$i=1;

	if (isset($SelectedReturnedItemsId) AND $InputError !=1) {

		$SQL = "UPDATE returneditems
				SET orderno = '" . $_POST['orderno'] . "',
					returndate = '" . $_POST['returndate'] . "',
					reasonid = '" . $_POST['reasonid'] . "',
					itemcodes = '" . $_POST['itemcodes'] . "',
					oldinvoice = '" . $_POST['oldinvoice'] . "',
					oldinvoicedate = '" . $_POST['oldinvoicedate'] . "'
				WHERE returneditemsid = '".$SelectedReturnedItemsId."'";

		$Msg = _('The Returned Item') . ' ' . $SelectedReturnedItemsId . ' ' .  _('has been updated');
	}
	elseif ( $InputError !=1 ) {

		// First check the returned item is not being duplicated

		$CheckSQL = "SELECT count(*)
			     FROM returneditems
			     WHERE returneditemsid = '" . $SelectedReturnedItemsId . "'";

		$CheckResult = DB_query($CheckSQL);
		$CheckRow = DB_fetch_row($CheckResult);

		if ( $CheckRow[0] > 0 ) {
			$InputError = 1;
			prnMsg( _('The Returned Item ') . $SelectedReturnedItemsId . _(' already exist.'),'error');
		} else {

			// Add new record on submit

			$SQL = "INSERT INTO returneditems (orderno,
											returndate,
											reasonid,
											itemcodes,
											oldinvoice,
											oldinvoicedate)
							VALUES ('" . $_POST['orderno'] . "',
							'" . FormatDateForSQL($_POST['returndate']) . "',
							'" . $_POST['reasonid'] . "',
							'" . $_POST['itemcodes'] . "',
							'" . $_POST['oldinvoice'] . "',
							'" . FormatDateForSQL($_POST['oldinvoicedate']) . "')";

			$Msg = _('Returned Item') . ' ' . $_POST['orderno'] .  ' ' . _('has been created');
		}
	}

	if ( $InputError !=1) {
	//run the SQL from either of the above possibilites
		$Result = DB_query($SQL);

		prnMsg($Msg,'success');

		unset($SelectedReturnedItemsId);
		unset($_POST['orderno']);
		unset($_POST['returndate']);
		unset($_POST['reasonid']);
		unset($_POST['itemcodes']);
		unset($_POST['oldinvoice']);
		unset($_POST['oldinvoicedate']);
	}

}
elseif ( isset($_GET['delete']) ) {

	$SQL="DELETE FROM returneditems WHERE code='" . $SelectedReturnedItemsId . "'";
	$ErrMsg = _('The Returned Item record could not be deleted because');
	$Result = DB_query($SQL,$ErrMsg);
	prnMsg(_('The returned item') . ' ' . $SelectedReturnedItemsId  . ' ' . _('has been deleted') ,'success');

	unset ($SelectedReturnedItemsId);
	unset($_GET['delete']);
}

if(isset($_POST['Cancel'])){
	unset($SelectedReturnedItemsId);
	unset($_POST['orderno']);
	unset($_POST['returndate']);
	unset($_POST['reasonid']);
	unset($_POST['itemcodes']);
	unset($_POST['oldinvoice']);
	unset($_POST['oldinvoicedate']);
}

if (!isset($SelectedReturnedItemsId)){

/* It could still be the second time the page has been run and a record has been selected for modification - SelectedReturnedItemsId will exist because it was sent with the new call. If its the first time the page has been displayed with no parameters
then none of the above are true and the list of sales types will be displayed with
links to delete or edit each. These will call the same page again and allow update/input
or deletion of the records*/
	$StartDate = FormatDateForSQL(DateAdd(Date($_SESSION['DefaultDateFormat']),'d',-$NumDays));

	$SQL = "SELECT  returneditemsid,
					orderno,
					returneditems.reasonid,
					reasonname,
					itemcodes,
					returndate,
					oldinvoice,
					oldinvoicedate
			FROM returneditems, returnitemreasons
			WHERE returneditems.reasonid = returnitemreasons.reasonid
				AND returndate >= '" . $StartDate . "'
			ORDER BY returndate DESC, 
					orderno DESC, 
					returneditemsid DESC";
	$Result = DB_query($SQL);

	echo '<table class="selection">
		<thead>
			<tr>
				<th class="SortedColumn">' . '#' . '</th>
				<th class="SortedColumn">' . _('Item Code') . '</th>
				<th class="SortedColumn">' . _('Reason') . '</th>
				<th class="SortedColumn">' . _('Order Return') . '</th>
				<th class="SortedColumn">' . _('Date of Return') . '</th>
				<th class="SortedColumn">' . _('Original Invoice') . '</th>
				<th class="SortedColumn">' . _('Date Original Invoice') . '</th>
			</tr>
		</thead>
		<tbody>';

while ($MyRow = DB_fetch_array($Result)) {
	echo '<tr class="striped_row">';

	echo '<td class="number">'.$MyRow['returneditemsid'].'</td>
		<td>'.$MyRow['itemcodes'].'</td>
		<td>'.$MyRow['reasonname'].'</td>
		<td class="number">'.$MyRow['orderno'].'</td>
		<td>'.ConvertSQLDate($MyRow['returndate']).'</td>
		<td>'.$MyRow['oldinvoice'].'</td>
		<td>'.ConvertSQLDate($MyRow['oldinvoicedate']).'</td>
		<td><a href="'.htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8').'?SelectedReturnedItemsId='.$MyRow['returneditemsid'].'">' . _('Edit') . '</a></td>
		<td><a href="'.htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8').'?SelectedReturnedItemsId='.$MyRow['returneditemsid'].'&amp;delete=yes" onclick="return confirm(\'' . _('Are you sure you wish to delete this return?') . '\');">' . _('Delete') . '</a></td>
		</tr>';
	}
	//END WHILE LIST LOOP
	echo '</tbody></table>';
}

//end of ifs and buts!
if (isset($SelectedReturnedItemsId)) {

	echo '<br />
			<div class="centre">
				<a href="' . htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') .'">' . _('Show List of Returned Items') . '</a>
			</div>';
}
if (! isset($_GET['delete'])) {

	if (!isset($_POST['itemcodes'])) {
		$_POST['itemcodes']='';
	}
	if (!isset($_POST['reasonid'])) {
		$_POST['reasonid'] = 1;
	}
	if (!isset($_POST['orderno'])) {
		$_POST['orderno']=0;
	}
	if (!isset($_POST['returndate'])) {
		$_POST['returndate']=Date($_SESSION['DefaultDateFormat']);
	}
	if (!isset($_POST['oldinvoice'])) {
		$_POST['oldinvoice']='';
	}
	if (!isset($_POST['oldinvoicedate'])) {
		$_POST['oldinvoicedate']=Date($_SESSION['DefaultDateFormat']);
	}

	echo '<form method="post" action="' . htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') . '" >
		<div>
		<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';

	if ( isset($SelectedReturnedItemsId) AND $SelectedReturnedItemsId!='' ) {
		$SQL = "SELECT returneditemsid,
						orderno,
						returndate,
						reasonid,
						itemcodes,
						oldinvoice,
						oldinvoicedate
		        FROM returneditems
		        WHERE returneditemsid='" . $SelectedReturnedItemsId . "'";

		$Result = DB_query($SQL);
		$MyRow = DB_fetch_array($Result);

		$_POST['SelectedReturnedItemsId'] = $MyRow['returneditemsid'];
		$_POST['orderno']  = $MyRow['orderno'];
		$_POST['returndate']  = $MyRow['returndate'];
		$_POST['reasonid']  = $MyRow['reasonid'];
		$_POST['itemcodes']  = $MyRow['itemcodes'];
		$_POST['oldinvoice']  = $MyRow['oldinvoice'];
		$_POST['oldinvoicedate']  = $MyRow['oldinvoicedate'];

		echo '<input type="hidden" name="SelectedReturnedItemsId" value="' . $_POST['SelectedReturnedItemsId'] . '" />';
		echo '<fieldset>
			<legend>' . _('Returned Item') . '</legend>';
		
		echo '<field>' . _('# Return') . ': ' . $SelectedReturnedItemsId . '</field>';
	}
	else {
		echo '<fieldset>
			<legend>' . _('Returned Item') . '</legend>';
	}

	echo FieldToSelectOneText('itemcodes', $_POST['itemcodes'], 20, 20, _('Item Code'));
	echo FieldToSelectOneReturnedItemReason('reasonid', $_POST['reasonid'], _('Return Reason'));
	echo FieldToSelectOneText('orderno', $_POST['orderno'], 20, 20, _('Order Return'));
	echo FieldToSelectOneDate('returndate', $_POST['returndate'], _('Date Of Return'), '', '', '', true, true);
	echo FieldToSelectOneText('oldinvoice', $_POST['oldinvoice'], 20, 20, _('Original Invoice'));
	echo FieldToSelectOneDate('oldinvoicedate', $_POST['oldinvoicedate'], _('Date Of Original Invoice'));
		
	echo '</fieldset>';

	echo TwoButtonsCenteredForm('submit', _('Accept'), 'Cancel', _('Cancel'));

	echo '</div>
          </form>';

}

include('includes/footer.php');
?>
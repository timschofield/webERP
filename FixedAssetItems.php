<?php

/* $Id: FixedAssetItems.php 6497 2013-12-14 00:41:44Z daintree $ */

include('includes/session.inc');
$Title = _('Fixed Assets');

$ViewTopic = 'FixedAssets';
$BookMark = 'AssetItems';

include('includes/header.inc');
include('includes/SQL_CommonFunctions.inc');

echo '<a href="' . $RootPath . '/SelectAsset.php">' . _('Back to Select') . '</a><br />' . "\n";

echo '<p class="page_title_text"><img src="'.$RootPath.'/css/'.$Theme.'/images/money_add.png" title="' .
		_('Fixed Asset Items') . '" alt="" />' . ' ' . $Title . '</p>';

/* If this form is called with the AssetID then it is assumed that the asset is to be modified  */
if (isset($_GET['AssetID'])){
	$AssetID =$_GET['AssetID'];
} elseif (isset($_POST['AssetID'])){
	$AssetID =$_POST['AssetID'];
} elseif (isset($_POST['Select'])){
	$AssetID =$_POST['Select'];
} else {
	$AssetID = '';
}

if (isset($_FILES['ItemPicture']) AND $_FILES['ItemPicture']['name'] !='') {

	$result    = $_FILES['ItemPicture']['error'];
 	$UploadTheFile = 'Yes'; //Assume all is well to start off with
	$filename = $_SESSION['part_pics_dir'] . '/ASSET_' . $AssetID . '.jpg';

	 //But check for the worst
	if (mb_strtoupper(mb_substr(trim($_FILES['ItemPicture']['name']),mb_strlen($_FILES['ItemPicture']['name'])-3))!='JPG'){
		prnMsg(_('Only jpg files are supported - a file extension of .jpg is expected'),'warn');
		$UploadTheFile ='No';
	} elseif ( $_FILES['ItemPicture']['size'] > ($_SESSION['MaxImageSize']*1024)) { //File Size Check
		prnMsg(_('The file size is over the maximum allowed. The maximum size allowed in KB is') . ' ' . $_SESSION['MaxImageSize'],'warn');
		$UploadTheFile ='No';
	} elseif ( $_FILES['ItemPicture']['type'] == 'text/plain' ) {  //File Type Check
		prnMsg( _('Only graphics files can be uploaded'),'warn');
         	$UploadTheFile ='No';
	} elseif (file_exists($filename)){
		prnMsg(_('Attempting to overwrite an existing item image'),'warn');
		$result = unlink($filename);
		if (!$result){
			prnMsg(_('The existing image could not be removed'),'error');
			$UploadTheFile ='No';
		}
	}

	if ($UploadTheFile=='Yes'){
		$result  =  move_uploaded_file($_FILES['ItemPicture']['tmp_name'], $filename);
		$message = ($result)?_('File url')  . '<a href="' . $filename .'">' .  $filename . '</a>' : _('Something is wrong with uploading a file');
	}
 /* EOR Add Image upload for New Item  - by Ori */
}

if (isset($Errors)) {
	unset($Errors);
}
$Errors = array();
$InputError = 0;

if (isset($_POST['submit'])) {

	//initialise no input errors assumed initially before we test

	/* actions to take once the user has clicked the submit button
	ie the page has called itself with some user input */

	//first off validate inputs sensible
	$i=1;


	if (!isset($_POST['Description']) or mb_strlen($_POST['Description']) > 50 OR mb_strlen($_POST['Description'])==0) {
		$InputError = 1;
		prnMsg (_('The asset description must be entered and be fifty characters or less long. It cannot be a zero length string either, a description is required'),'error');
		$Errors[$i] = 'Description';
		$i++;
	}
	if (mb_strlen($_POST['LongDescription'])==0) {
		$InputError = 1;
		prnMsg (_('The asset long description cannot be a zero length string, a long description is required'),'error');
		$Errors[$i] = 'LongDescription';
		$i++;
	}

	if (mb_strlen($_POST['BarCode']) >20) {
		$InputError = 1;
		prnMsg(_('The barcode must be 20 characters or less long'),'error');
		$Errors[$i] = 'BarCode';
		$i++;
	}

	if (trim($_POST['AssetCategoryID'])==''){
		$InputError = 1;
		prnMsg(_('There are no asset categories defined. All assets must belong to a valid category,'),'error');
		$Errors[$i] = 'AssetCategoryID';
		$i++;
	}
	if (trim($_POST['AssetLocation'])==''){
		$InputError = 1;
		prnMsg(_('There are no asset locations defined. All assets must belong to a valid location,'),'error');
		$Errors[$i] = 'AssetLocation';
		$i++;
	}
	if (!is_numeric(filter_number_format($_POST['DepnRate']))
		OR filter_number_format($_POST['DepnRate'])>100
		OR filter_number_format($_POST['DepnRate'])<0){

		$InputError = 1;
		prnMsg(_('The depreciation rate is expected to be a number between 0 and 100'),'error');
		$Errors[$i] = 'DepnRate';
		$i++;
	}
	if (filter_number_format($_POST['DepnRate'])>0 AND filter_number_format($_POST['DepnRate'])<1){
		prnMsg(_('Numbers less than 1 are interpreted as less than 1%. The depreciation rate should be entered as a number between 0 and 100'),'warn');
	}


	if ($InputError !=1){

		if ($_POST['submit']==_('Update')) { /*so its an existing one */

			/*Start a transaction to do the whole lot inside */
			$result = DB_Txn_Begin($db);

			/*Need to check if changing the balance sheet codes - as will need to do journals for the cost and accum depn of the asset to the new category */
			$result = DB_query("SELECT assetcategoryid,
										cost,
										accumdepn,
										costact,
										accumdepnact
								FROM fixedassets INNER JOIN fixedassetcategories
								ON fixedassets.assetcategoryid=fixedassetcategories.categoryid
								WHERE assetid='" . $AssetID . "'",$db);
			$OldDetails = DB_fetch_array($result);
			if ($OldDetails['assetcategoryid'] !=$_POST['AssetCategoryID']  AND $OldDetails['cost']!=0){

				$PeriodNo = GetPeriod(Date($_SESSION['DefaultDateFormat']),$db);
				/* Get the new account codes for the new asset category */
				$result = DB_query("SELECT costact,
											accumdepnact
									FROM fixedassetcategories
									WHERE categoryid='" . $_POST['AssetCategoryID'] . "'",$db);
				$NewAccounts = DB_fetch_array($result);

				$TransNo = GetNextTransNo( 42, $db); /* transaction type is asset category change */

				//credit cost for the old category
				$SQL = "INSERT INTO gltrans (type,
											typeno,
											trandate,
											periodno,
											account,
											narrative,
											amount)
							VALUES ('42',
								'" . $TransNo . "',
								'" . Date('Y-m-d') . "',
								'" . $PeriodNo . "',
								'" . $OldDetails['costact'] . "',
								'" . $AssetID . ' ' . _('change category') . ' ' . $OldDetails['assetcategoryid'] . ' - ' . $_POST['AssetCategoryID'] . "',
								'" . -$OldDetails['cost']. "'
								)";
				$ErrMsg = _('Cannot insert a GL entry for the change of asset category because');
				$DbgMsg = _('The SQL that failed to insert the cost GL Trans record was');
				$result = DB_query($SQL,$db,$ErrMsg,$DbgMsg,true);

				//debit cost for the new category
				$SQL = "INSERT INTO gltrans (type,
											typeno,
											trandate,
											periodno,
											account,
											narrative,
											amount)
							VALUES ('42',
								'" . $TransNo . "',
								'" . Date('Y-m-d') . "',
								'" . $PeriodNo . "',
								'" . $NewAccounts['costact'] . "',
								'" . $AssetID . ' ' . _('change category') . ' ' . $OldDetails['assetcategoryid'] . ' - ' . $_POST['AssetCategoryID'] . "',
								'" . $OldDetails['cost']. "'
								)";
				$ErrMsg = _('Cannot insert a GL entry for the change of asset category because');
				$DbgMsg = _('The SQL that failed to insert the cost GL Trans record was');
				$result = DB_query($SQL,$db,$ErrMsg,$DbgMsg,true);
				if ($OldDetails['accumdepn']!=0) {
					//debit accumdepn for the old category
					$SQL = "INSERT INTO gltrans (type,
												typeno,
												trandate,
												periodno,
												account,
												narrative,
												amount)
								VALUES ('42',
									'" . $TransNo . "',
									'" . Date('Y-m-d') . "',
									'" . $PeriodNo . "',
									'" . $OldDetails['accumdepnact'] . "',
									'" . $AssetID . ' ' . _('change category') . ' ' . $OldDetails['assetcategoryid'] . ' - ' . $_POST['AssetCategoryID'] . "',
									'" . $OldDetails['accumdepn']. "'
									)";
					$ErrMsg = _('Cannot insert a GL entry for the change of asset category because');
					$DbgMsg = _('The SQL that failed to insert the cost GL Trans record was');
					$result = DB_query($SQL,$db,$ErrMsg,$DbgMsg,true);

					//credit accum depn for the new category
					$SQL = "INSERT INTO gltrans (type,
												typeno,
												trandate,
												periodno,
												account,
												narrative,
												amount)
								VALUES ('42',
									'" . $TransNo . "',
									'" . Date('Y-m-d') . "',
									'" . $PeriodNo . "',
									'" . $NewAccounts['accumdepnact'] . "',
									'" . $AssetID . ' ' . _('change category') . ' ' . $OldDetails['assetcategoryid'] . ' - ' . $_POST['AssetCategoryID'] . "',
									'" . -$OldDetails['accumdepn']. "'
									)";
					$ErrMsg = _('Cannot insert a GL entry for the change of asset category because');
					$DbgMsg = _('The SQL that failed to insert the cost GL Trans record was');
					$result = DB_query($SQL,$db,$ErrMsg,$DbgMsg,true);
				} /*end if there was accumulated depreciation for the asset */
			} /* end if there is a change in asset category */
			$sql = "UPDATE fixedassets
					SET longdescription='" . $_POST['LongDescription'] . "',
						description='" . $_POST['Description'] . "',
						assetcategoryid='" . $_POST['AssetCategoryID'] . "',
						assetlocation='" . $_POST['AssetLocation'] . "',
						depntype='" . $_POST['DepnType'] . "',
						depnrate='" . filter_number_format($_POST['DepnRate']) . "',
						barcode='" . $_POST['BarCode'] . "',
						serialno='" . $_POST['SerialNo'] . "'
					WHERE assetid='" . $AssetID . "'";

			$ErrMsg = _('The asset could not be updated because');
			$DbgMsg = _('The SQL that was used to update the asset and failed was');
			$result = DB_query($sql,$db,$ErrMsg,$DbgMsg);

			prnMsg( _('Asset') . ' ' . $AssetID . ' ' . _('has been updated'), 'success');
			echo '<br />';
		} else { //it is a NEW part
			$sql = "INSERT INTO fixedassets (description,
											longdescription,
											assetcategoryid,
											assetlocation,
											depntype,
											depnrate,
											barcode,
											serialno)
						VALUES (
							'" . $_POST['Description'] . "',
							'" . $_POST['LongDescription'] . "',
							'" . $_POST['AssetCategoryID'] . "',
							'" . $_POST['AssetLocation'] . "',
							'" . $_POST['DepnType'] . "',
							'" . filter_number_format($_POST['DepnRate']). "',
							'" . $_POST['BarCode'] . "',
							'" . $_POST['SerialNo'] . "' )";
			$ErrMsg =  _('The asset could not be added because');
			$DbgMsg = _('The SQL that was used to add the asset failed was');
			$result = DB_query($sql,$db, $ErrMsg, $DbgMsg);

			if (DB_error_no($db) ==0) {
				$NewAssetID = DB_Last_Insert_ID($db,'fixedassets', 'assetid');
				prnMsg( _('The new asset has been added to the database with an asset code of:') . ' ' . $NewAssetID,'success');
				unset($_POST['LongDescription']);
				unset($_POST['Description']);
				unset($_POST['BarCode']);
				unset($_POST['SerialNo']);
			}//ALL WORKED SO RESET THE FORM VARIABLES
			$result = DB_Txn_Commit($db);
		}
	} else {
		echo '<br />' .  "\n";
		prnMsg( _('Validation failed, no updates or deletes took place'), 'error');
	}

} elseif (isset($_POST['delete']) AND mb_strlen($_POST['delete']) >1 ) {
//the button to delete a selected record was clicked instead of the submit button

	$CancelDelete = 0;
	//what validation is required before allowing deletion of assets ....  maybe there should be no deletion option?
	$result = DB_query("SELECT cost,
								accumdepn,
								accumdepnact,
								costact
						FROM fixedassets INNER JOIN fixedassetcategories
						ON fixedassets.assetcategoryid=fixedassetcategories.categoryid
						WHERE assetid='" . $AssetID . "'", $db);
	$AssetRow = DB_fetch_array($result);
	$NBV = $AssetRow['cost'] -$AssetRow['accumdepn'];
	if ($NBV!=0) {
		$CancelDelete =1; //cannot delete assets where NBV is not 0
		prnMsg(_('The asset still has a net book value - only assets with a zero net book value can be deleted'),'error');
	}
	$result = DB_query("SELECT * FROM fixedassettrans WHERE assetid='" . $AssetID . "'",$db);
	if (DB_num_rows($result) > 0){
		$CancelDelete =1; /*cannot delete assets with transactions */
		prnMsg(_('The asset has transactions associated with it. The asset can only be deleted when the fixed asset transactions are purged, otherwise the integrity of fixed asset reports may be compromised'),'error');
	}
	$result = DB_query("SELECT * FROM purchorderdetails WHERE assetid='" . $AssetID . "'",$db);
	if (DB_num_rows($result) > 0){
		$CancelDelete =1; /*cannot delete assets where there is a purchase order set up for it */
		prnMsg(_('There is a purchase order set up for this asset. The purchase order line must be deleted first'),'error');
	}
	if ($CancelDelete==0) {
		$result = DB_Txn_Begin($db);

		/*Need to remove cost and accumulate depreciation from cost and accumdepn accounts */
		$PeriodNo = GetPeriod(Date($_SESSION['DefaultDateFormat']),$db);
		$TransNo = GetNextTransNo( 43, $db); /* transaction type is asset deletion - (and remove cost/acc5umdepn from GL) */
		if ($AssetRow['cost'] > 0){
			//credit cost for the asset deleted
			$SQL = "INSERT INTO gltrans (type,
										typeno,
										trandate,
										periodno,
										account,
										narrative,
										amount)
						VALUES ('43',
							'" . $TransNo . "',
							'" . Date('Y-m-d') . "',
							'" . $PeriodNo . "',
							'" . $AssetRow['costact'] . "',
							'" . _('Delete asset') . ' ' . $AssetID . "',
							'" . -$AssetRow['cost']. "'
							)";
			$ErrMsg = _('Cannot insert a GL entry for the deletion of the asset because');
			$DbgMsg = _('The SQL that failed to insert the cost GL Trans record was');
			$result = DB_query($SQL,$db,$ErrMsg,$DbgMsg,true);

			//debit accumdepn for the depreciation removed on deletion of this asset
			$SQL = "INSERT INTO gltrans (type,
										typeno,
										trandate,
										periodno,
										account,
										narrative,
										amount)
						VALUES ('43',
							'" . $TransNo . "',
							'" . Date('Y-m-d') . "',
							'" . $PeriodNo . "',
							'" . $AssetRow['accumdepnact'] . "',
							'" . _('Delete asset') . ' ' . $AssetID . "',
							'" . $Asset['accumdepn']. "'
							)";
			$ErrMsg = _('Cannot insert a GL entry for the reversal of accumulated depreciation on deletion of the asset because');
			$DbgMsg = _('The SQL that failed to insert the cost GL Trans record was');
			$result = DB_query($SQL,$db,$ErrMsg,$DbgMsg,true);

		} //end if cost > 0

		$sql="DELETE FROM fixedassets WHERE assetid='" . $AssetID . "'";
		$result=DB_query($sql,$db, _('Could not delete the asset record'),'',true);

		$result = DB_Txn_Commit($db);

		prnMsg(_('Deleted the asset  record for asset number' ) . ' ' . $AssetID );
		unset($_POST['LongDescription']);
		unset($_POST['Description']);
		unset($_POST['AssetCategoryID']);
		unset($_POST['AssetLocation']);
		unset($_POST['DepnType']);
		unset($_POST['DepnRate']);
		unset($_POST['BarCode']);
		unset($_POST['SerialNo']);
		unset($AssetID);
		unset($_SESSION['SelectedAsset']);

	} //end if OK Delete Asset
} /* end if delete asset */
$result = DB_Txn_Commit($db);

echo '<form id="AssetForm" enctype="multipart/form-data" method="post" action="' . htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') . '">
      <div>';
echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';
echo '<table class="selection">';


if (!isset($AssetID) OR $AssetID=='') {

/*If the page was called without $AssetID passed to page then assume a new asset is to be entered other wise the form showing the fields with the existing entries against the asset will show for editing with a hidden AssetID field. New is set to flag that the page may have called itself and still be entering a new asset, in which case the page needs to know not to go looking up details for an existing asset*/

	$New = 1;
	echo '<tr><td><input type="hidden" name="New" value="" /></td></tr>';

	$_POST['LongDescription'] = '';
	$_POST['Description'] = '';
	$_POST['AssetCategoryID']  = '';
	$_POST['SerialNo']  = '';
	$_POST['AssetLocation']  = '';
	$_POST['DepnType']  = '';
	$_POST['BarCode']  = '';
	$_POST['DepnRate']  = 0;

} elseif ($InputError!=1) { // Must be modifying an existing item and no changes made yet - need to lookup the details

	$sql = "SELECT assetid,
				description,
				longdescription,
				assetcategoryid,
				serialno,
				assetlocation,
				datepurchased,
				depntype,
				depnrate,
				cost,
				accumdepn,
				barcode,
				disposalproceeds,
				disposaldate
			FROM fixedassets
			WHERE assetid ='" . $AssetID . "'";

	$result = DB_query($sql, $db);
	$AssetRow = DB_fetch_array($result);

	$_POST['LongDescription'] = $AssetRow['longdescription'];
	$_POST['Description'] = $AssetRow['description'];
	$_POST['AssetCategoryID']  = $AssetRow['assetcategoryid'];
	$_POST['SerialNo']  = $AssetRow['serialno'];
	$_POST['AssetLocation']  = $AssetRow['assetlocation'];
	$_POST['DepnType']  = $AssetRow['depntype'];
	$_POST['BarCode']  = $AssetRow['barcode'];
	$_POST['DepnRate']  = locale_number_format($AssetRow['depnrate'],2);

	echo '<tr>
			<td>' . _('Asset Code') . ':</td>
			<td>' . $AssetID . '</td>
		</tr>';
	echo '<tr><td><input type="hidden" name="AssetID" value="'.$AssetID.'"/></td></tr>';

} else { // some changes were made to the data so don't re-set form variables to DB ie the code above
	echo '<tr>
			<td>' . _('Asset Code') . ':</td>
			<td>' . $AssetID . '</td>
		</tr>';
	echo '<tr><td><input type="hidden" name="AssetID" value="' . $AssetID . '"/></td></tr>';
}

if (isset($AssetRow['disposaldate']) AND $AssetRow['disposaldate'] !='0000-00-00'){
	echo '<tr>
			<td>' . _('Asset Already disposed on') . ':</td>
			<td>' . ConvertSQLDate($AssetRow['disposaldate']) . '</td>
		</tr>';
}

if (isset($_POST['Description'])) {
	$Description = $_POST['Description'];
} else {
	$Description ='';
}

echo '<tr>
		<td>' . _('Asset Description') . ' (' . _('short') . '):</td>
		<td><input ' . (in_array('Description',$Errors) ?  'class="inputerror"' : '' ) .' type="text" required="required" title="' . _('Enter the description of the item. Up to 50 characters can be used.') . '" name="Description" size="52" maxlength="50" value="' . $Description . '" /></td>
	</tr>';

if (isset($_POST['LongDescription'])) {
	$LongDescription = AddCarriageReturns($_POST['LongDescription']);
} else {
	$LongDescription ='';
}
echo '<tr>
		<td>' . _('Asset Description') . ' (' . _('long') . '):</td>
		<td><textarea ' . (in_array('LongDescription',$Errors) ?  'class="texterror"' : '' ) .'  name="LongDescription" required="required" title="' . _('Enter the lond description of the asset including specs etc. Up to 255 characters are allowed.') . '" cols="40" rows="4">' . stripslashes($LongDescription) . '</textarea></td>
	</tr>';

if (!isset($New) ) { //ie not new at all!

	echo '<tr>
			<td>' .  _('Image File (.jpg)') . ':</td>
			<td><input type="file" id="ItemPicture" name="ItemPicture" /></td>';

	if (function_exists('imagecreatefromjpg')){
		$AssetImgLink = '<img src="GetStockImage.php?automake=1&textcolor=FFFFFF&bgcolor=CCCCCC'.
			'&AssetID='.urlencode($AssetID).
			'&text='.
			'&width=64'.
			'&height=64'.
			'" />';
	} else {
		if( isset($AssetID) and file_exists($_SESSION['part_pics_dir'] . '/ASSET_' .$AssetID.'.jpg') ) {
			$AssetImgLink = '<img src="' . $_SESSION['part_pics_dir'] . '/ASSET_' .$AssetID.'.jpg" />';
		} else {
			$AssetImgLink = _('No Image');
		}
	}

	if ($AssetImgLink!=_('No Image')) {
		echo '<td>' . _('Image') . '<br />' . $AssetImgLink . '</td></tr>';
	} else {
		echo '</td></tr>';
	}

	// EOR Add Image upload for New Item  - by Ori
} //only show the add image if the asset already exists - otherwise AssetID will not be set - and the image needs the AssetID to save

echo '<tr>
		<td>' . _('Asset Category') . ':</td>
		<td><select name="AssetCategoryID">';

$sql = "SELECT categoryid, categorydescription FROM fixedassetcategories";
$ErrMsg = _('The asset categories could not be retrieved because');
$DbgMsg = _('The SQL used to retrieve stock categories and failed was');
$result = DB_query($sql,$db,$ErrMsg,$DbgMsg);

while ($myrow=DB_fetch_array($result)){
	if (!isset($_POST['AssetCategoryID']) or $myrow['categoryid']==$_POST['AssetCategoryID']){
		echo '<option selected="selected" value="'. $myrow['categoryid'] . '">' . $myrow['categorydescription'] . '</option>';
	} else {
		echo '<option value="'. $myrow['categoryid'] . '">' . $myrow['categorydescription']. '</option>';
	}
	$category=$myrow['categoryid'];
}
echo '</select><a target="_blank" href="'. $RootPath . '/FixedAssetCategories.php">' . ' ' . _('Add or Modify Asset Categories') . '</a></td></tr>';
if (!isset($_POST['AssetCategoryID'])) {
	$_POST['AssetCategoryID']=$category;
}

if (isset($AssetRow) AND ($AssetRow['datepurchased']!='0000-00-00' AND $AssetRow['datepurchased']!='')){
	echo '<tr>
			<td>' . _('Date Purchased') . ':</td>
			<td>' . ConvertSQLDate($AssetRow['datepurchased']) . '</td>
		</tr>';
}

$sql = "SELECT locationid, locationdescription FROM fixedassetlocations";
$ErrMsg = _('The asset locations could not be retrieved because');
$DbgMsg = _('The SQL used to retrieve asset locations and failed was');
$result = DB_query($sql,$db,$ErrMsg,$DbgMsg);

echo '<tr>
		<td>' . _('Asset Location') . ':</td>
		<td><select name="AssetLocation">';

while ($myrow=DB_fetch_array($result)){
	if ($_POST['AssetLocation']==$myrow['locationid']){
		echo '<option selected="selected" value="' . $myrow['locationid'] .'">' . $myrow['locationdescription'] . '</option>';
	} else {
		echo '<option value="' . $myrow['locationid'] .'">' . $myrow['locationdescription'] . '</option>';
	}
}
echo '</select>
	<a target="_blank" href="'. $RootPath . '/FixedAssetLocations.php">' . ' ' . _('Add Asset Location') . '</a></td>
	</tr>
	<tr>
		<td>' . _('Bar Code') . ':</td>
		<td><input ' . (in_array('BarCode',$Errors) ?  'class="inputerror"' : '' ) .'  type="text" name="BarCode" size="22" maxlength="20" value="' . $_POST['BarCode'] . '" /></td>
	</tr>
	<tr>
		<td>' . _('Serial Number') . ':</td>
		<td><input ' . (in_array('SerialNo',$Errors) ?  'class="inputerror"' : '' ) .'  type="text" name="SerialNo" size="32" maxlength="30" value="' . $_POST['SerialNo'] . '" /></td>
	</tr>
	<tr>
		<td>' . _('Depreciation Type') . ':</td>
		<td><select name="DepnType">';

if (!isset($_POST['DepnType'])){
	$_POST['DepnType'] = 0; //0 = Straight line - 1 = Diminishing Value
}
if ($_POST['DepnType']==0){ //straight line
	echo '<option selected="selected" value="0">' . _('Straight Line') . '</option>';
	echo '<option value="1">' . _('Diminishing Value') . '</option>';
} else {
	echo '<option value="0">' . _('Straight Line') . '</option>';
	echo '<option selected="selected" value="1">' . _('Diminishing Value') . '</option>';
}

echo '</select></td>
	</tr>
	<tr>
		<td>' . _('Depreciation Rate') . ':</td>
		<td><input ' . (in_array('DepnRate',$Errors) ?  'class="inputerror number"' : 'class="number"' ) .'  type="text" name="DepnRate" size="4" maxlength="4" value="' . $_POST['DepnRate'] . '" />%</td>
	</tr>
	</table>';

if (isset($AssetRow)){
	echo '<table>
		<tr>
			<th colspan="2">' . _('Asset Financial Summary') . '</th>
		</tr>
		<tr>
			<td>' . _('Accumulated Costs') . ':</td>
			<td class="number">' . locale_number_format($AssetRow['cost'],$_SESSION['CompanyRecord']['decimalplaces']) . '</td>
		</tr>
		<tr>
			<td>' . _('Accumulated Depreciation') . ':</td>
			<td class="number">' . locale_number_format($AssetRow['accumdepn'],$_SESSION['CompanyRecord']['decimalplaces']) . '</td>
		</tr>';
	if ($AssetRow['disposaldate'] != '0000-00-00'){
		echo'<tr>
			<td>' . _('Net Book Value at disposal date') . ':</td>
			<td class="number">' . locale_number_format($AssetRow['cost']-$AssetRow['accumdepn'],$_SESSION['CompanyRecord']['decimalplaces']) . '</td>
		</tr>';
		echo'<tr>
			<td>' . _('Disposal Proceeds') . ':</td>
			<td class="number">' . locale_number_format($AssetRow['disposalproceeds'],$_SESSION['CompanyRecord']['decimalplaces']) . '</td>
		</tr>';
		echo'<tr>
			<td>' . _('P/L after disposal') . ':</td>
			<td class="number">' . locale_number_format(-$AssetRow['cost']+$AssetRow['accumdepn']+$AssetRow['disposalproceeds'],$_SESSION['CompanyRecord']['decimalplaces']) . '</td>
		</tr>';
	
	}else{
		echo'<tr>
			<td>' . _('Net Book Value') . ':</td>
			<td class="number">' . locale_number_format($AssetRow['cost']-$AssetRow['accumdepn'],$_SESSION['CompanyRecord']['decimalplaces']) . '</td>
		</tr>';
	}
	/*Get the last period depreciation (depn is transtype =44) was posted for */
	$result = DB_query("SELECT periods.lastdate_in_period,
								max(fixedassettrans.periodno)
					FROM fixedassettrans INNER JOIN periods
					ON fixedassettrans.periodno=periods.periodno
					WHERE transtype=44
					GROUP BY periods.lastdate_in_period
					ORDER BY periods.lastdate_in_period DESC",$db);

	$LastDepnRun = DB_fetch_row($result);
	if(DB_num_rows($result)==0){
		$LastRunDate = _('Not Yet Run');
	} else {
		$LastRunDate = ConvertSQLDate($LastDepnRun[0]);
	}
	echo '<tr>
			<td>' . _('Depreciation last run') . ':</td>
			<td>' . $LastRunDate . '</td>
		</tr>
		</table>';
}

if (isset($New)) {
	echo '<div class="centre">
			<br />
			<input type="submit" name="submit" value="' . _('Insert New Fixed Asset') . '" />';
} else {
	echo '<br />
		<div class="centre">
			<input type="submit" name="submit" value="' . _('Update') . '" />
		</div>';
		prnMsg( _('Only click the Delete button if you are sure you wish to delete the asset. Only assets with a zero book value can be deleted'), 'warn', _('WARNING'));
	echo '<br />
		<div class="centre">
			<input type="submit" name="delete" value="' . _('Delete This Asset') . '" onclick="return confirm(\'' . _('Are You Sure? Only assets with a zero book value can be deleted.') . '\');" />';
}

echo '</div>
      </div>
	</form>';
include('includes/footer.inc');
?>

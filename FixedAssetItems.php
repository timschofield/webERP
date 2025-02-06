<?php


include('includes/session.php');
$Title = _('Fixed Assets');

$ViewTopic = 'FixedAssets';
$BookMark = 'AssetItems';

include('includes/header.php');
include('includes/SQL_CommonFunctions.inc');
include ('includes/ImageFunctions.php');

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

$SupportedImgExt = array('png','jpg','jpeg');

if (isset($_FILES['ItemPicture']) AND $_FILES['ItemPicture']['name'] !='') {
	$ImgExt = pathinfo($_FILES['ItemPicture']['name'], PATHINFO_EXTENSION);

	$Result    = $_FILES['ItemPicture']['error'];
 	$UploadTheFile = 'Yes'; //Assume all is well to start off with
	$FileName = $_SESSION['part_pics_dir'] . '/ASSET_' . $AssetID . '.' . $ImgExt;
	//But check for the worst
	if (!in_array ($ImgExt, $SupportedImgExt)) {
		prnMsg(_('Only ' . implode(", ", $SupportedImgExt) . ' files are supported - a file extension of ' . implode(", ", $SupportedImgExt) . ' is expected'),'warn');
		$UploadTheFile ='No';
	} elseif ( $_FILES['ItemPicture']['size'] > ($_SESSION['MaxImageSize']*1024)) { //File Size Check
		prnMsg(_('The file size is over the maximum allowed. The maximum size allowed in KB is') . ' ' . $_SESSION['MaxImageSize'],'warn');
		$UploadTheFile ='No';
	} elseif ( $_FILES['ItemPicture']['type'] == 'text/plain' ) {  //File Type Check
		prnMsg( _('Only graphics files can be uploaded'),'warn');
         	$UploadTheFile ='No';
	}
	foreach ($SupportedImgExt as $Ext) {
		$File = $_SESSION['part_pics_dir'] . '/ASSET_' . $AssetID . '.' . $Ext;
		if (file_exists ($File) ) {
			$Result = unlink($File);
			if (!$Result){
				prnMsg(_('The existing image could not be removed'),'error');
				$UploadTheFile ='No';
			}
		}
	}

	if ($UploadTheFile=='Yes'){
		$Result  =  move_uploaded_file($_FILES['ItemPicture']['tmp_name'], $FileName);
		$Message = ($Result)?_('File url')  . '<a href="' . $FileName .'">' .  $FileName . '</a>' : _('Something is wrong with uploading a file');
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
			DB_Txn_Begin();

			/*Need to check if changing the balance sheet codes - as will need to do journals for the cost and accum depn of the asset to the new category */
			$Result = DB_query("SELECT assetcategoryid,
										cost,
										accumdepn,
										costact,
										accumdepnact
								FROM fixedassets INNER JOIN fixedassetcategories
								ON fixedassets.assetcategoryid=fixedassetcategories.categoryid
								WHERE assetid='" . $AssetID . "'");
			$OldDetails = DB_fetch_array($Result);
			if ($OldDetails['assetcategoryid'] !=$_POST['AssetCategoryID']  AND $OldDetails['cost']!=0){

				$PeriodNo = GetPeriod(Date($_SESSION['DefaultDateFormat']));
				/* Get the new account codes for the new asset category */
				$Result = DB_query("SELECT costact,
											accumdepnact
									FROM fixedassetcategories
									WHERE categoryid='" . $_POST['AssetCategoryID'] . "'");
				$NewAccounts = DB_fetch_array($Result);

				$TransNo = GetNextTransNo( 42 ); /* transaction type is asset category change */

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
				$Result = DB_query($SQL,$ErrMsg,$DbgMsg,true);

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
				$Result = DB_query($SQL,$ErrMsg,$DbgMsg,true);
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
					$Result = DB_query($SQL,$ErrMsg,$DbgMsg,true);

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
					$Result = DB_query($SQL,$ErrMsg,$DbgMsg,true);
				} /*end if there was accumulated depreciation for the asset */
			} /* end if there is a change in asset category */
			$SQL = "UPDATE fixedassets
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
			$Result = DB_query($SQL,$ErrMsg,$DbgMsg);

			prnMsg( _('Asset') . ' ' . $AssetID . ' ' . _('has been updated'), 'success');
			echo '<br />';
		} else { //it is a NEW part
			$SQL = "INSERT INTO fixedassets (description,
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
			$Result = DB_query($SQL, $ErrMsg, $DbgMsg);

			if (DB_error_no() ==0) {
				$NewAssetID = DB_Last_Insert_ID('fixedassets', 'assetid');
				prnMsg( _('The new asset has been added to the database with an asset code of:') . ' ' . $NewAssetID,'success');
				unset($_POST['LongDescription']);
				unset($_POST['Description']);
				unset($_POST['BarCode']);
				unset($_POST['SerialNo']);
			}//ALL WORKED SO RESET THE FORM VARIABLES
			DB_Txn_Commit();
		}
	} else {
		echo '<br />' .  "\n";
		prnMsg( _('Validation failed, no updates or deletes took place'), 'error');
	}

} elseif (isset($_POST['delete']) AND mb_strlen($_POST['delete']) >1 ) {
//the button to delete a selected record was clicked instead of the submit button

	$CancelDelete = 0;
	//what validation is required before allowing deletion of assets ....  maybe there should be no deletion option?
	$Result = DB_query("SELECT cost,
								accumdepn,
								accumdepnact,
								costact
						FROM fixedassets INNER JOIN fixedassetcategories
						ON fixedassets.assetcategoryid=fixedassetcategories.categoryid
						WHERE assetid='" . $AssetID . "'");
	$AssetRow = DB_fetch_array($Result);
	$NBV = $AssetRow['cost'] -$AssetRow['accumdepn'];
	if ($NBV!=0) {
		$CancelDelete =1; //cannot delete assets where NBV is not 0
		prnMsg(_('The asset still has a net book value - only assets with a zero net book value can be deleted'),'error');
	}
	$Result = DB_query("SELECT * FROM fixedassettrans WHERE assetid='" . $AssetID . "'");
	if (DB_num_rows($Result) > 0){
		$CancelDelete =1; /*cannot delete assets with transactions */
		prnMsg(_('The asset has transactions associated with it. The asset can only be deleted when the fixed asset transactions are purged, otherwise the integrity of fixed asset reports may be compromised'),'error');
	}
	$Result = DB_query("SELECT * FROM purchorderdetails WHERE assetid='" . $AssetID . "'");
	if (DB_num_rows($Result) > 0){
		$CancelDelete =1; /*cannot delete assets where there is a purchase order set up for it */
		prnMsg(_('There is a purchase order set up for this asset. The purchase order line must be deleted first'),'error');
	}
	if ($CancelDelete==0) {
		DB_Txn_Begin();

		/*Need to remove cost and accumulate depreciation from cost and accumdepn accounts */
		$PeriodNo = GetPeriod(Date($_SESSION['DefaultDateFormat']));
		$TransNo = GetNextTransNo( 43 ); /* transaction type is asset deletion - (and remove cost/acc5umdepn from GL) */
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
			$Result = DB_query($SQL,$ErrMsg,$DbgMsg,true);

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
			$Result = DB_query($SQL,$ErrMsg,$DbgMsg,true);

		} //end if cost > 0

		$SQL="DELETE FROM fixedassets WHERE assetid='" . $AssetID . "'";
		$Result=DB_query($SQL, _('Could not delete the asset record'),'',true);

		DB_Txn_Commit();

		// Delete the AssetImage
		foreach ($SupportedImgExt as $Ext) {
			$File = $_SESSION['part_pics_dir'] . '/ASSET_' . $AssetID . '.' . $Ext;
			if (file_exists ($File) ) {
				unlink($File);
			}
		}

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
DB_Txn_Commit();

echo '<form id="AssetForm" enctype="multipart/form-data" method="post" action="' . htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') . '">';
echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';
echo '<fieldset>';

if (!isset($AssetID) OR $AssetID=='') {

/*If the page was called without $AssetID passed to page then assume a new asset is to be entered other wise the form showing the fields with the existing entries against the asset will show for editing with a hidden AssetID field. New is set to flag that the page may have called itself and still be entering a new asset, in which case the page needs to know not to go looking up details for an existing asset*/

	$New = 1;
	echo '<tr><td><input type="hidden" name="New" value="" /></td></tr>';

	$_POST['LongDescription'] = '';
	$_POST['Description'] = '';
	$_POST['AssetCategoryID']  = '';
	$_POST['SerialNo']  = '';
	$_POST['AssetLocation']  = '';
	$_POST['DepnType']  = 0;
	$_POST['BarCode']  = '';
	$_POST['DepnRate']  = 0;
	
	echo '<legend>', _('Create New Asset Details'), '</legend>';

} elseif ($InputError!=1) { // Must be modifying an existing item and no changes made yet - need to lookup the details

	$SQL = "SELECT assetid,
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

	$Result = DB_query($SQL);
	$AssetRow = DB_fetch_array($Result);

	$_POST['LongDescription'] = $AssetRow['longdescription'];
	$_POST['Description'] = $AssetRow['description'];
	$_POST['AssetCategoryID']  = $AssetRow['assetcategoryid'];
	$_POST['SerialNo']  = $AssetRow['serialno'];
	$_POST['AssetLocation']  = $AssetRow['assetlocation'];
	$_POST['DepnType']  = $AssetRow['depntype'];
	$_POST['BarCode']  = $AssetRow['barcode'];
	$_POST['DepnRate']  = locale_number_format($AssetRow['depnrate'],2);

	echo '<legend>', _('Edit Asset Details'), '</legend>';

	echo '<field>
			<label for="AssetID">' . _('Asset Code') . ':</label>
			<fieldtext>' . $AssetID . '</fieldtext>
		</field>';
	echo '<field><td><input type="hidden" name="AssetID" value="'.$AssetID.'"/></td></field>';

} else { // some changes were made to the data so don't re-set form variables to DB ie the code above
	echo '<field>
			<label for="AssetID">' . _('Asset Code') . ':</label>
			<fieldtext>' . $AssetID . '</fieldtext>
		</field>';
	echo '<field><td><input type="hidden" name="AssetID" value="' . $AssetID . '"/></td></field>';
}

if (isset($AssetRow['disposaldate']) AND $AssetRow['disposaldate'] !='0000-00-00'){
	echo '<field>
			<label for="disposaldate">' . _('Asset Already disposed on') . ':</label>
			<fieldtext>' . ConvertSQLDate($AssetRow['disposaldate']) . '</fieldtext>
		</field>';
}

if (isset($_POST['Description'])) {
	$Description = $_POST['Description'];
} else {
	$Description ='';
}

echo '<field>
		<label for="Description">' . _('Asset Description') . ' (' . _('short') . '):</label>
		<input ' . (in_array('Description',$Errors) ?  'class="inputerror"' : '' ) .' type="text" required="required" title="" name="Description" size="52" maxlength="50" value="' . $Description . '" />
		<fieldhelp>' . _('Enter the description of the item. Up to 50 characters can be used.') . '</fieldhelp>
	</field>';

if (isset($_POST['LongDescription'])) {
	$LongDescription = AddCarriageReturns($_POST['LongDescription']);
} else {
	$LongDescription ='';
}
echo '<field>
		<label for="LongDescription">' . _('Asset Description') . ' (' . _('long') . '):</label>
		<textarea ' . (in_array('LongDescription',$Errors) ?  'class="texterror"' : '' ) .'  name="LongDescription" required="required" title="" cols="40" rows="4">' . stripslashes($LongDescription) . '</textarea>
		<fieldhelp>' . _('Enter the lond description of the asset including specs etc. Up to 255 characters are allowed.') . '</fieldhelp>
	</field>';

if (!isset($New) ) { //ie not new at all!

	echo '<field>
			<label for="ItemPicture">' .  _('Image File (' . implode(", ", $SupportedImgExt) . ')') . ':</label>
			<input type="file" id="ItemPicture" name="ItemPicture" />
		</field>
		<field>
			<label for"ClearImage">'._('Clear Image').'</label>
			<input type="checkbox" name="ClearImage" id="ClearImage" value="1" > ';
    $Glob = (glob($_SESSION['part_pics_dir'] . '/ASSET_' . $AssetID . '.{' . implode(",", $SupportedImgExt) . '}', GLOB_BRACE));
	$ImageFile = reset($Glob);
	$AssetImgLink = GetImageLink($ImageFile, 'ASSET_' . $AssetID, 64, 64, "", "");
	if ($AssetImgLink!=_('No Image')) {
		echo '<td>' . _('Image') . '<br />' . $AssetImgLink . '</td></field>';
	} else {
		echo '</td></field>';
	}

	// EOR Add Image upload for New Item  - by Ori
} //only show the add image if the asset already exists - otherwise AssetID will not be set - and the image needs the AssetID to save

if (isset($_POST['ClearImage']) ) {
	foreach ($SupportedImgExt as $Ext) {
		$File = $_SESSION['part_pics_dir'] . '/ASSET_' . $AssetID . '.' . $Ext;
		if (file_exists ($File) ) {
			//workaround for many variations of permission issues that could cause unlink fail
			@unlink($File);
			if(is_file($ImageFile)) {
               prnMsg(_('You do not have access to delete this item image file.'),'error');
			} else {
				$AssetImgLink = _('No Image');
			}
		}
	}
}

echo '<field>
		<label for="AssetCategoryID">' . _('Asset Category') . ':</label>
		<select name="AssetCategoryID">';

$SQL = "SELECT categoryid, categorydescription FROM fixedassetcategories";
$ErrMsg = _('The asset categories could not be retrieved because');
$DbgMsg = _('The SQL used to retrieve stock categories and failed was');
$Result = DB_query($SQL,$ErrMsg,$DbgMsg);

while ($MyRow=DB_fetch_array($Result)){
	if (!isset($_POST['AssetCategoryID']) or $MyRow['categoryid']==$_POST['AssetCategoryID']){
		echo '<option selected="selected" value="'. $MyRow['categoryid'] . '">' . $MyRow['categorydescription'] . '</option>';
	} else {
		echo '<option value="'. $MyRow['categoryid'] . '">' . $MyRow['categorydescription']. '</option>';
	}
	$Category=$MyRow['categoryid'];
}
echo '</select><a target="_blank" href="'. $RootPath . '/FixedAssetCategories.php">' . ' ' . _('Add or Modify Asset Categories') . '</a></field>';
if (!isset($_POST['AssetCategoryID'])) {
	$_POST['AssetCategoryID']=$Category;
}

if (isset($AssetRow) AND ($AssetRow['datepurchased']!='0000-00-00' AND $AssetRow['datepurchased']!='')){
	echo '<field>
			<label for="datepurchased">' . _('Date Purchased') . ':</label>
			<fieldtext>' . ConvertSQLDate($AssetRow['datepurchased']) . '</fieldtext>
		</field>';
}

$SQL = "SELECT locationid, locationdescription FROM fixedassetlocations";
$ErrMsg = _('The asset locations could not be retrieved because');
$DbgMsg = _('The SQL used to retrieve asset locations and failed was');
$Result = DB_query($SQL,$ErrMsg,$DbgMsg);

echo '<field>
		<label for="AssetLocation">' . _('Asset Location') . ':</label>
		<select name="AssetLocation">';

while ($MyRow=DB_fetch_array($Result)){
	if ($_POST['AssetLocation']==$MyRow['locationid']){
		echo '<option selected="selected" value="' . $MyRow['locationid'] .'">' . $MyRow['locationdescription'] . '</option>';
	} else {
		echo '<option value="' . $MyRow['locationid'] .'">' . $MyRow['locationdescription'] . '</option>';
	}
}
echo '</select>
	<a target="_blank" href="'. $RootPath . '/FixedAssetLocations.php">' . ' ' . _('Add Asset Location') . '</a>
	</field>';
	
echo '<field>
		<label for="BarCode">' . _('Bar Code') . ':</label>
		<input ' . (in_array('BarCode',$Errors) ?  'class="inputerror"' : '' ) .'  type="text" name="BarCode" size="22" maxlength="20" value="' . $_POST['BarCode'] . '" />
	</field>
	<field>
		<label for="SerialNo">' . _('Serial Number') . ':</label>
		<input ' . (in_array('SerialNo',$Errors) ?  'class="inputerror"' : '' ) .'  type="text" name="SerialNo" size="32" maxlength="30" value="' . $_POST['SerialNo'] . '" />
	</field>
	<field>
		<label for="DepnType">' . _('Depreciation Type') . ':</label>
		<select name="DepnType">';

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

echo '</select>
	</field>';
	
echo '<field>
		<label for="DepnRate">' . _('Depreciation Rate') . ':</label>
		<input ' . (in_array('DepnRate',$Errors) ?  'class="inputerror number"' : 'class="number"' ) .'  type="text" name="DepnRate" size="4" maxlength="4" value="' . $_POST['DepnRate'] . '" />%
	</field>
	</fieldset>';

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
	$Result = DB_query("SELECT periods.lastdate_in_period,
								max(fixedassettrans.periodno)
					FROM fixedassettrans INNER JOIN periods
					ON fixedassettrans.periodno=periods.periodno
					WHERE transtype=44
					GROUP BY periods.lastdate_in_period
					ORDER BY periods.lastdate_in_period DESC");

	$LastDepnRun = DB_fetch_row($Result);
	if(DB_num_rows($Result)==0){
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
			<input type="submit" name="submit" value="' . _('Insert New Fixed Asset') . '" />';
} else {
	echo '<div class="centre">
			<input type="submit" name="submit" value="' . _('Update') . '" />
		</div>';
	echo '<div class="centre">
			<input type="reset" name="delete" value="' . _('Delete This Asset') . '" onclick="return confirm(\'' . _('Are You Sure? Only assets with a zero book value can be deleted.') . '\');" />';
}

echo '</div>
	</form>';
include('includes/footer.php');
?>

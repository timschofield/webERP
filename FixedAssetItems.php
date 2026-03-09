<?php

/**************************************************************************************
*
* KL RICARD: Keep the v4.13.1 version, as we want to keep the same SOP standard v4.15.2 
* uses a different accountint SOP for fixed assets. To be improved probably in v5
* 
* Add some data entry validations
*
**************************************************************************************/

require(__DIR__ . '/includes/session.php');

$Title = __('Fixed Assets');
$ViewTopic = 'FixedAssets';
$BookMark = 'AssetItems';
include(__DIR__ . '/includes/header.php');

include(__DIR__ . '/includes/SQL_CommonFunctions.php');
include(__DIR__ . '/includes/ImageFunctions.php');

echo '<a href="' . $RootPath . '/SelectAsset.php">' . __('Back to Select') . '</a><br />' . "\n";

echo '<p class="page_title_text"><img src="'.$RootPath.'/css/'.$Theme.'/images/money_add.png" title="' .
		__('Fixed Asset Items') . '" alt="" />' . ' ' . $Title . '</p>';

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

if (!isset($_POST['Description'])){
	$_POST['Description'] = '';
}

if (!isset($_POST['LongDescription'])){
	$_POST['LongDescription'] = '';
}

if (!isset($_POST['BarCode'])){
	$_POST['BarCode'] = '';
}

$SupportedImgExt = array('png','jpg','jpeg');

if (isset($_FILES['ItemPicture']) AND $_FILES['ItemPicture']['name'] !='') {
	$ImgExt = pathinfo($_FILES['ItemPicture']['name'], PATHINFO_EXTENSION);

	$Result    = $_FILES['ItemPicture']['error'];
 	$UploadTheFile = 'Yes'; //Assume all is well to start off with
	$FileName = $_SESSION['part_pics_dir'] . '/ASSET_' . $AssetID . '.' . $ImgExt;
	//But check for the worst
	if (!in_array ($ImgExt, $SupportedImgExt)) {
		prnMsg(__('Only ' . implode(", ", $SupportedImgExt) . ' files are supported - a file extension of ' . implode(", ", $SupportedImgExt) . ' is expected'),'warn');
		$UploadTheFile ='No';
	} elseif ( $_FILES['ItemPicture']['size'] > ($_SESSION['MaxImageSize']*1024)) { //File Size Check
		prnMsg(__('The file size is over the maximum allowed. The maximum size allowed in KB is') . ' ' . $_SESSION['MaxImageSize'],'warn');
		$UploadTheFile ='No';
	} elseif ( $_FILES['ItemPicture']['type'] == 'text/plain' ) {  //File Type Check
		prnMsg( __('Only graphics files can be uploaded'),'warn');
		 	$UploadTheFile ='No';
	}
	foreach ($SupportedImgExt as $Ext) {
		$File = $_SESSION['part_pics_dir'] . '/ASSET_' . $AssetID . '.' . $Ext;
		if (file_exists ($File) ) {
			$Result = unlink($File);
			if (!$Result){
				prnMsg(__('The existing image could not be removed'),'error');
				$UploadTheFile ='No';
			}
		}
	}

	if ($UploadTheFile=='Yes'){
		$Result  =  move_uploaded_file($_FILES['ItemPicture']['tmp_name'], $FileName);
		$Message = ($Result)?__('File url')  . '<a href="' . $FileName .'">' .  $FileName . '</a>' : __('Something is wrong with uploading a file');
	}
 /* EOR Add Image upload for New Item  - by Ori */
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
		prnMsg(__('The asset description must be entered and be fifty characters or less long. It cannot be a zero length string either, a description is required'),'error');
		$Errors[$i] = 'Description';
		$i++;
	}
	if (mb_strlen($_POST['LongDescription'])==0) {
		$InputError = 1;
		prnMsg(__('The asset long description cannot be a zero length string, a long description is required'),'error');
		$Errors[$i] = 'LongDescription';
		$i++;
	}

	if (isset($_POST['BarCode']) AND (mb_strlen($_POST['BarCode']) >20)) {
		$InputError = 1;
		prnMsg(__('The barcode must be 20 characters or less long'),'error');
		$Errors[$i] = 'BarCode';
		$i++;
	}

	// KL RICARD Add some data entry validations
	if (!is_numeric($_POST['Cost'])){
		$InputError = 1;
		prnMsg(__('The cost is expected to be numeric'),'error');
		$Errors[$i] = 'Cost';
		$i++;
	} elseif ($_POST['Cost'] < 0){
		$InputError = 1;
		prnMsg(__('The cost is expected to be positive'),'error');
		$Errors[$i] = 'Cost';
		$i++;
	}
	if (!is_numeric($_POST['AccumDepn'])){
		$InputError = 1;
		prnMsg(__('The Accumulated Depreciation is expected to be numeric'),'error');
		$Errors[$i] = 'AccumDepn';
		$i++;
	} elseif ($_POST['AccumDepn'] < 0){
		$InputError = 1;
		prnMsg(__('The Accumulated Depreciation is expected to be positive'),'error');
		$Errors[$i] = 'AccumDepn';
		$i++;
	}
	if ($_POST['Cost'] < $_POST['AccumDepn']){
		$InputError = 1;
		prnMsg(__('The Accumulated Depreciation cannot be higher than Cost'),'error');
		$Errors[$i] = 'Cost';
		$i++;
	}	
	// KL RICARD END Add some data entry validations - End
	
	if (trim($_POST['AssetCategoryID'])==''){
		$InputError = 1;
		prnMsg(__('There are no asset categories defined. All assets must belong to a valid category,'),'error');
		$Errors[$i] = 'AssetCategoryID';
		$i++;
	}
	if (trim($_POST['AssetLocation'])==''){
		$InputError = 1;
		prnMsg(__('There are no asset locations defined. All assets must belong to a valid location,'),'error');
		$Errors[$i] = 'AssetLocation';
		$i++;
	}
	if (!is_numeric(filter_number_format($_POST['DepnRate']))
		OR filter_number_format($_POST['DepnRate'])>100
		OR filter_number_format($_POST['DepnRate'])<0){

		$InputError = 1;
		prnMsg(__('The depreciation rate is expected to be a number between 0 and 100'),'error');
		$Errors[$i] = 'DepnRate';
		$i++;
	}
	if (filter_number_format($_POST['DepnRate'])>0 AND filter_number_format($_POST['DepnRate'])<1){
		prnMsg(__('Numbers less than 1 are interpreted as less than 1%. The depreciation rate should be entered as a number between 0 and 100'),'warn');
	}


	if ($InputError !=1){

		if ($_POST['submit']==__('Update')) { /*so its an existing one */

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

				$PeriodNo = GetPeriod(date($_SESSION['DefaultDateFormat']));
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
								CURRENT_DATE,
								'" . $PeriodNo . "',
								'" . $OldDetails['costact'] . "',
								'" . mb_substr($AssetID . ' ' . __('change category') . ' ' . $OldDetails['assetcategoryid'] . ' - ' . $_POST['AssetCategoryID'], 0, 200) . "',
								'" . -$OldDetails['cost']. "'
								)";
				$ErrMsg = __('Cannot insert a GL entry for the change of asset category because');
				$Result = DB_query($SQL,$ErrMsg,'',true);

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
								CURRENT_DATE,
								'" . $PeriodNo . "',
								'" . $NewAccounts['costact'] . "',
								'" . mb_substr($AssetID . ' ' . __('change category') . ' ' . $OldDetails['assetcategoryid'] . ' - ' . $_POST['AssetCategoryID'], 0, 200) . "',
								'" . $OldDetails['cost']. "'
								)";
				$ErrMsg = __('Cannot insert a GL entry for the change of asset category because');
				$Result = DB_query($SQL,$ErrMsg,'',true);
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
									CURRENT_DATE,
									'" . $PeriodNo . "',
									'" . $OldDetails['accumdepnact'] . "',
									'" . mb_substr($AssetID . ' ' . __('change category') . ' ' . $OldDetails['assetcategoryid'] . ' - ' . $_POST['AssetCategoryID'], 0, 200) . "',
									'" . $OldDetails['accumdepn']. "'
									)";
					$ErrMsg = __('Cannot insert a GL entry for the change of asset category because');
					$Result = DB_query($SQL,$ErrMsg,'',true);

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
									CURRENT_DATE,
									'" . $PeriodNo . "',
									'" . $NewAccounts['accumdepnact'] . "',
									'" . mb_substr($AssetID . ' ' . __('change category') . ' ' . $OldDetails['assetcategoryid'] . ' - ' . $_POST['AssetCategoryID'], 0, 200) . "',
									'" . -$OldDetails['accumdepn']. "'
									)";
					$ErrMsg = __('Cannot insert a GL entry for the change of asset category because');
					$Result = DB_query($SQL,$ErrMsg,'',true);
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

			$ErrMsg = __('The asset could not be updated because');
			$Result = DB_query($SQL,$ErrMsg,'');
			prnMsg( __('Asset') . ' ' . $AssetID . ' ' . __('has been updated'), 'success');
			echo '<br />';
			DB_Txn_Commit();
		} else { //it is a NEW part
			$SQL = "INSERT INTO fixedassets (description,
											longdescription,
											assetcategoryid,
											assetlocation,
											cost,
											accumdepn,
											datepurchased,
											depntype,
											depnrate,
											barcode,
											serialno)
						VALUES (
							'" . $_POST['Description'] . "',
							'" . $_POST['LongDescription'] . "',
							'" . $_POST['AssetCategoryID'] . "',
							'" . $_POST['AssetLocation'] . "',
							'" . filter_number_format($_POST['Cost']). "',
							'" . filter_number_format($_POST['AccumDepn']). "',
							'" . FormatDateForSQL($_POST['DatePurchased']). "',
							'" . $_POST['DepnType'] . "',
							'" . filter_number_format($_POST['DepnRate']). "',
							'" . $_POST['BarCode'] . "',
							'" . $_POST['SerialNo'] . "' )";
			$ErrMsg =  __('The asset could not be added because');
			$Result = DB_query($SQL, $ErrMsg, '');

			if (DB_error_no() == 0) { //the insert of the new code worked so bang in the fixedassettrans records too
				$NewAssetID = DB_Last_Insert_ID('fixedassets', 'assetid');
				$TransNo = GetNextTransNo(49);
				$PeriodNo = GetPeriod(ConvertSQLDate($_POST['DatePurchased']));

				$SQL = "INSERT INTO fixedassettrans ( assetid,
												transtype,
												transno,
												transdate,
												periodno,
												inputdate,
												fixedassettranstype,
												amount)
									VALUES ( '" . $NewAssetID . "',
											'49',
											'" . $TransNo . "',
											'" . FormatDateForSQL($_POST['DatePurchased']) . "',
											'" . $PeriodNo . "',
											CURRENT_DATE,
											'cost',
											'" . filter_number_format($_POST['Cost']) . "')";

				$ErrMsg =  __('The transaction for the cost of the asset could not be added because');
				$InsResult = DB_query($SQL,$ErrMsg,'');

				$SQL = "INSERT INTO fixedassettrans ( assetid,
													transtype,
													transno,
													transdate,
													periodno,
													inputdate,
													fixedassettranstype,
													amount)
									VALUES ( '" . $NewAssetID . "',
											'49',
											'" . $TransNo . "',
											'" . FormatDateForSQL($_POST['DatePurchased']) . "',
											'" . $PeriodNo . "',
											CURRENT_DATE,
											'depn',
											'" . filter_number_format($_POST['AccumDepn']) . "')";

				$ErrMsg =  __('The transaction for the cost of the asset could not be added because');
				$InsResult = DB_query($SQL,$ErrMsg,'');
			}
			if (DB_error_no() == 0) {
				prnMsg( __('The new asset has been added to the database with an asset code of:') . ' ' . $NewAssetID,'success');
				unset($_POST['LongDescription']);
				unset($_POST['Description']);
				unset($_POST['BarCode']);
				unset($_POST['SerialNo']);
				unset($_POST['Cost']);
				unset($_POST['AccumDepn']);
				unset($_POST['DatePurchased']);
			}//ALL WORKED SO RESET THE FORM VARIABLES
			DB_Txn_Commit();
		}
	} else {
		echo '<br />' .  "\n";
		prnMsg( __('Validation failed, no updates or deletes took place'), 'error');
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
		prnMsg(__('The asset still has a net book value - only assets with a zero net book value can be deleted'),'error');
	}
	$Result = DB_query("SELECT * FROM fixedassettrans WHERE assetid='" . $AssetID . "'");
	if (DB_num_rows($Result) > 0){
		$CancelDelete =1; /*cannot delete assets with transactions */
		prnMsg(__('The asset has transactions associated with it. The asset can only be deleted when the fixed asset transactions are purged, otherwise the integrity of fixed asset reports may be compromised'),'error');
	}
	$Result = DB_query("SELECT * FROM purchorderdetails WHERE assetid='" . $AssetID . "'");
	if (DB_num_rows($Result) > 0){
		$CancelDelete =1; /*cannot delete assets where there is a purchase order set up for it */
		prnMsg(__('There is a purchase order set up for this asset. The purchase order line must be deleted first'),'error');
	}
	if ($CancelDelete==0) {
		DB_Txn_Begin();

		/*Need to remove cost and accumulate depreciation from cost and accumdepn accounts */
		$PeriodNo = GetPeriod(date($_SESSION['DefaultDateFormat']));
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
							CURRENT_DATE,
							'" . $PeriodNo . "',
							'" . $AssetRow['costact'] . "',
							'" . mb_substr(__('Delete asset') . ' ' . $AssetID, 0, 200) . "',
							'" . -$AssetRow['cost']. "'
							)";
			$ErrMsg = __('Cannot insert a GL entry for the deletion of the asset because');
			$Result = DB_query($SQL,$ErrMsg,'',true);

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
						CURRENT_DATE,
						'" . $PeriodNo . "',
						'" . $AssetRow['accumdepnact'] . "',
						'" . mb_substr(__('Delete asset') . ' ' . $AssetID, 0, 200) . "',
						'" . $Asset['accumdepn']. "'
						)";
			$ErrMsg = __('Cannot insert a GL entry for the reversal of accumulated depreciation on deletion of the asset because');
			$Result = DB_query($SQL,$ErrMsg,'',true);

		} //end if cost > 0

		$SQL="DELETE FROM fixedassets WHERE assetid='" . $AssetID . "'";
		$Result=DB_query($SQL, __('Could not delete the asset record'),'',true);

		DB_Txn_Commit();

		// Delete the AssetImage
		foreach ($SupportedImgExt as $Ext) {
			$File = $_SESSION['part_pics_dir'] . '/ASSET_' . $AssetID . '.' . $Ext;
			if (file_exists ($File) ) {
				unlink($File);
			}
		}

		prnMsg(__('Deleted the asset  record for asset number' ) . ' ' . $AssetID );
		unset($_POST['LongDescription']);
		unset($_POST['Description']);
		unset($_POST['AssetCategoryID']);
		unset($_POST['AssetLocation']);
		unset($_POST['Cost']);
		unset($_POST['AccumDepn']);
		unset($_POST['DatePurchased']);
		unset($_POST['DepnType']);
		unset($_POST['DepnRate']);
		unset($_POST['BarCode']);
		unset($_POST['SerialNo']);
		unset($AssetID);
		unset($_SESSION['SelectedAsset']);

	} //end if OK Delete Asset
} /* end if delete asset */
DB_Txn_Commit();

echo '<form id="AssetForm" enctype="multipart/form-data" method="post" action="' . htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') . '">
      <div>';
echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';

echo '<fieldset>
        <legend>' . __('Asset Details') . '</legend>';

if (!isset($AssetID) OR $AssetID=='') {

/*If the page was called without $AssetID passed to page then assume a new asset is to be entered other wise the form showing the fields with the existing entries against the asset will show for editing with a hidden AssetID field. New is set to flag that the page may have called itself and still be entering a new asset, in which case the page needs to know not to go looking up details for an existing asset*/

	$New = 1;
	echo '<tr><td><input type="hidden" name="New" value="" /></td></tr>';

	$_POST['LongDescription'] = '';
	$_POST['Description'] = '';
	$_POST['AssetCategoryID']  = '';
	$_POST['SerialNo']  = '';
	$_POST['AssetLocation']  = '';
	$_POST['Cost']  = 0;
	$_POST['AccumDepn']  = 0;
	$_POST['DatePurchased']=Date($_SESSION['DefaultDateFormat']);
	$_POST['DepnType']  = 0;
	$_POST['BarCode']  = '';
	$_POST['DepnRate']  = 0;

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
	$_POST['Cost']  = $AssetRow['cost'];
	$_POST['AccumDepn']  = $AssetRow['accumdepn'];
	$_POST['DatePurchased']  = $AssetRow['datepurchased'];
	$_POST['DepnType']  = $AssetRow['depntype'];
	$_POST['BarCode']  = $AssetRow['barcode'];
	$_POST['DepnRate']  = locale_number_format($AssetRow['depnrate'],2);

	echo '<field>
			<label>' . __('Asset Code') . ':</label>
			<div class="fieldvalue">' . $AssetID . '</div>
		</field>';
	echo '<field><input type="hidden" name="AssetID" value="'.$AssetID.'"/></field>';

} else { // some changes were made to the data so don't re-set form variables to DB ie the code above
	echo '<field>
			<label>' . __('Asset Code') . ':</label>
			<div class="fieldvalue">' . $AssetID . '</div>
		</field>';
	echo '<field><input type="hidden" name="AssetID" value="' . $AssetID . '"/></field>';
}

if (isset($AssetRow['disposaldate']) AND $AssetRow['disposaldate'] !='1000-01-01'){
	echo '<field>
			<label>' . __('Asset Already disposed on') . ':</label>
			<div class="fieldvalue">' . ConvertSQLDate($AssetRow['disposaldate']) . '</div>
		</field>';
}

$Description = $_POST['Description'] ?? '';

echo '<field>
		<label>' . __('Asset Description') . ' (' . __('short') . '):</label>
		<input ' . (in_array('Description',$Errors) ?  'class="inputerror"' : '' ) .' type="text" required="required" title="' . __('Enter the description of the item. Up to 50 characters can be used.') . '" name="Description" size="52" maxlength="50" value="' . $Description . '" />
	</field>';

if (isset($_POST['LongDescription'])) {
	$LongDescription = AddCarriageReturns($_POST['LongDescription']);
} else {
	$LongDescription ='';
}
echo '<field>
		<label>' . __('Asset Description') . ' (' . __('long') . '):</label>
		<textarea ' . (in_array('LongDescription',$Errors) ?  'class="texterror"' : '' ) .'  name="LongDescription" required="required" title="' . __('Enter the lond description of the asset including specs etc. Up to 255 characters are allowed.') . '" cols="40" rows="4">' . stripslashes($LongDescription) . '</textarea>
	</field>';

if (!isset($New) ) { //ie not new at all!

	echo '<field>
			<label for="ItemPicture">' .  __('Image File (' . implode(", ", $SupportedImgExt) . ')') . ':</label>
			<input type="file" id="ItemPicture" name="ItemPicture" />
		</field>
		<field>
			<label for"ClearImage">'.__('Clear Image').'</label>
			<input type="checkbox" name="ClearImage" id="ClearImage" value="1" > ';
    $Glob = (glob($_SESSION['part_pics_dir'] . '/ASSET_' . $AssetID . '.{' . implode(",", $SupportedImgExt) . '}', GLOB_BRACE));
	$ImageFile = reset($Glob);
	$AssetImgLink = GetImageLink($ImageFile, 'ASSET_' . $AssetID, 64, 64, "", "");
	if ($AssetImgLink!=__('No Image')) {
		echo '<div class="fieldvalue">' . __('Image') . '<br />' . $AssetImgLink . '</div></field>';
	} else {
		echo '</field>';
	}

	// EOR Add Image upload for New Item  - by Ori
} //only show the add image if the asset already exists - otherwise AssetID will not be set - and the image needs the AssetID to save

if (isset($_POST['ClearImage']) ) {
	foreach ($SupportedImgExt as $Ext) {
		$File = $_SESSION['part_pics_dir'] . '/ASSET_' . $AssetID . '.' . $Ext;
		if (file_exists ($File) ) {
			//workaround for many variations of permission issues that could cause unlink fail
			@unlink($File);
			if (is_file($ImageFile)) {
               prnMsg(__('You do not have access to delete this item image file.'),'error');
			} else {
				$AssetImgLink = __('No Image');
			}
		}
	}
}


echo '<field>
		<label>' . __('Asset Category') . ':</label>
		<select name="AssetCategoryID">';

$SQL = "SELECT categoryid, categorydescription FROM fixedassetcategories";
$ErrMsg = __('The asset categories could not be retrieved because');
$Result = DB_query($SQL,$ErrMsg,'');

while ($MyRow=DB_fetch_array($Result)){
	if (!isset($_POST['AssetCategoryID']) or $MyRow['categoryid']==$_POST['AssetCategoryID']){
		echo '<option selected="selected" value="'. $MyRow['categoryid'] . '">' . $MyRow['categorydescription'] . '</option>';
	} else {
		echo '<option value="'. $MyRow['categoryid'] . '">' . $MyRow['categorydescription']. '</option>';
	}
	$Category=$MyRow['categoryid'];
}
echo '</select><a target="_blank" href="'. $RootPath . '/FixedAssetCategories.php">' . ' ' . __('Add or Modify Asset Categories') . '</a></field>';
if (!isset($_POST['AssetCategoryID'])) {
	$_POST['AssetCategoryID']=$Category;
}


echo '<field>
		<label>' . __('Date Purchased') . ':</label>
		<input type="date" required="required" alt="' . $_SESSION['DefaultDateFormat'] . '" name="DatePurchased" maxlength="10" size="11" value="' . FormatDateForSQL($_POST['DatePurchased']) . '" />
	</field>';

echo '<field>
		<label>' . __('Cost of Purchase') . '</label>
		<input type="text" class="number" name="Cost" maxlength="12" size="10" value="' . locale_number_format($_POST['Cost'],$_SESSION['CompanyRecord']['decimalplaces']) . '" />
	</field>';
	
echo '<field>
		<label>' . __('Accumulated Depreciation (0 at purchase)') . '</label>
		<input type="text" class="number" name="AccumDepn" maxlength="12" size="10" value="' . locale_number_format($_POST['AccumDepn'],$_SESSION['CompanyRecord']['decimalplaces']) . '" />
	</field>';
	
$SQL = "SELECT locationid, locationdescription FROM fixedassetlocations";
$ErrMsg = __('The asset locations could not be retrieved because');
$Result = DB_query($SQL,$ErrMsg,'');

echo '<field>
		<label>' . __('Asset Location') . ':</label>
		<select name="AssetLocation">';

while ($MyRow=DB_fetch_array($Result)){
	if ($_POST['AssetLocation']==$MyRow['locationid']){
		echo '<option selected="selected" value="' . $MyRow['locationid'] .'">' . $MyRow['locationdescription'] . '</option>';
	} else {
		echo '<option value="' . $MyRow['locationid'] .'">' . $MyRow['locationdescription'] . '</option>';
	}
}

//	<tr>
//		<td>' . __('Bar Code') . ':</td>
//		<td><input ' . (in_array('BarCode',$Errors) ?  'class="inputerror"' : '' ) .'  type="text" name="BarCode" size="22" maxlength="20" value="' . $_POST['BarCode'] . '" /></td>
//	</tr>

	
echo '</select>
	<a target="_blank" href="'. $RootPath . '/FixedAssetLocations.php">' . ' ' . __('Add Asset Location') . '</a></field>
	<field>
		<label>' . __('Serial Number') . ':</label>
		<input ' . (in_array('SerialNo',$Errors) ?  'class="inputerror"' : '' ) .'  type="text" name="SerialNo" size="32" maxlength="30" value="' . $_POST['SerialNo'] . '" />
	</field>
	<field>
		<label>' . __('Depreciation Type') . ':</label>
		<select name="DepnType">';

if (!isset($_POST['DepnType'])){
	$_POST['DepnType'] = 0; //0 = Straight line - 1 = Diminishing Value
}
if ($_POST['DepnType']==0){ //straight line
	echo '<option selected="selected" value="0">' . __('Straight Line') . '</option>';
	echo '<option value="1">' . __('Diminishing Value') . '</option>';
} else {
	echo '<option value="0">' . __('Straight Line') . '</option>';
	echo '<option selected="selected" value="1">' . __('Diminishing Value') . '</option>';
}

echo '</select></field>
	<field>
		<label>' . __('Depreciation Rate') . ':</label>
		<input ' . (in_array('DepnRate',$Errors) ?  'class="inputerror number"' : 'class="number"' ) .'  type="text" name="DepnRate" size="4" maxlength="4" value="' . $_POST['DepnRate'] . '" />%
	</field>
	</fieldset>';

if (isset($AssetRow)){
	echo '<fieldset>
		<legend>' . __('Asset Financial Summary') . '</legend>
		<field>
			<label>' . __('Accumulated Costs') . ':</label>
			<div class="fieldvalue">' . locale_number_format($_POST['Cost'],$_SESSION['CompanyRecord']['decimalplaces']) . '</div>
		</field>
		<field>
			<label>' . __('Accumulated Depreciation') . ':</label>
			<div class="fieldvalue">' . locale_number_format($_POST['AccumDepn'],$_SESSION['CompanyRecord']['decimalplaces']) . '</div>
		</field>';
	if ($AssetRow['disposaldate'] != '1000-01-01'){
		echo'<field>
			<label>' . __('Net Book Value at disposal date') . ':</label>
			<div class="fieldvalue">' . locale_number_format($AssetRow['cost']-$AssetRow['accumdepn'],$_SESSION['CompanyRecord']['decimalplaces']) . '</div>
		</field>';
		echo'<field>
			<label>' . __('Disposal Proceeds') . ':</label>
			<div class="fieldvalue">' . locale_number_format($AssetRow['disposalproceeds'],$_SESSION['CompanyRecord']['decimalplaces']) . '</div>
		</field>';
		echo'<field>
			<label>' . __('P/L after disposal') . ':</label>
			<div class="fieldvalue">' . locale_number_format(-$AssetRow['cost']+$AssetRow['accumdepn']+$AssetRow['disposalproceeds'],$_SESSION['CompanyRecord']['decimalplaces']) . '</div>
		</field>';

	} else {
		echo'<field>
			<label>' . __('Net Book Value') . ':</label>
			<div class="fieldvalue">' . locale_number_format($AssetRow['cost']-$AssetRow['accumdepn'],$_SESSION['CompanyRecord']['decimalplaces']) . '</div>
		</field>';
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
	if (DB_num_rows($Result)==0){
		$LastRunDate = __('Not Yet Run');
	} else {
		$LastRunDate = ConvertSQLDate($LastDepnRun[0]);
	}
	echo '<field>
			<label>' . __('Depreciation last run') . ':</label>
			<div class="fieldvalue">' . $LastRunDate . '</div>
		</field>
		</fieldset>';
}

if (isset($New)) {
	echo '<div class="centre">
			<br />
			<input type="submit" name="submit" value="' . __('Insert New Fixed Asset') . '" />';
} else {
	echo '<br />
		<div class="centre">
			<input type="submit" name="submit" value="' . __('Update') . '" />
		</div>';
	echo '<br />
		<div class="centre">
			<input type="reset" name="delete" value="' . __('Delete This Asset') . '" onclick="return confirm(\'' . __('Are You Sure? Only assets with a zero book value can be deleted.') . '\');" />';
}

echo '</div>
      </div>
	</form>';
include(__DIR__ . '/includes/footer.php');


<?php

/* $Id$ */
/* $Revision: 1.3 $ */

$PageSecurity = 11;

include('includes/session.inc');
$title = _('Fixed Assets');
include('includes/header.inc');

echo '<a href="' . $rootpath . '/SelectAsset.php?' . SID . '">' . _('Back to Select') . '</a><br>' . "\n";

echo '<p class="page_title_text"><img src="'.$rootpath.'/css/'.$theme.'/images/money_add.png" title="' .
		_('Fixed Asset Items') . '" alt="">' . ' ' . $title . '</p>';

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

if (isset($AssetID)) {
	$sql = "SELECT COUNT(assetid) FROM fixedassets WHERE assetid='".$AssetID."'";
	$result = DB_query($sql,$db);
	$myrow = DB_fetch_row($result);
	if ($myrow[0]==0) {
		$New=0;
	} else {
		$New=1;
	}
}

if (isset($_FILES['ItemPicture']) AND $_FILES['ItemPicture']['name'] !='') {

	$result    = $_FILES['ItemPicture']['error'];
 	$UploadTheFile = 'Yes'; //Assume all is well to start off with
	$filename = $_SESSION['part_pics_dir'] . '/ASSET_' . $AssetID . '.jpg';

	 //But check for the worst
	if (strtoupper(substr(trim($_FILES['ItemPicture']['name']),strlen($_FILES['ItemPicture']['name'])-3))!='JPG'){
		prnMsg(_('Only jpg files are supported - a file extension of .jpg is expected'),'warn');
		$UploadTheFile ='No';
	} elseif ( $_FILES['ItemPicture']['size'] > ($_SESSION['MaxImageSize']*1024)) { //File Size Check
		prnMsg(_('The file size is over the maximum allowed. The maximum size allowed in KB is') . ' ' . $_SESSION['MaxImageSize'],'warn');
		$UploadTheFile ='No';
	} elseif ( $_FILES['ItemPicture']['type'] == "text/plain" ) {  //File Type Check
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
		$message = ($result)?_('File url') ."<a href='". $filename ."'>" .  $filename . '</a>' : _('Something is wrong with uploading a file');
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


	if (!isset($_POST['Description']) or strlen($_POST['Description']) > 50 OR strlen($_POST['Description'])==0) {
		$InputError = 1;
		prnMsg (_('The asset description must be entered and be fifty characters or less long. It cannot be a zero length string either, a description is required'),'error');
		$Errors[$i] = 'Description';
		$i++;
	}
	if (strlen($_POST['LongDescription'])==0) {
		$InputError = 1;
		prnMsg (_('The asset long description cannot be a zero length string, a long description is required'),'error');
		$Errors[$i] = 'LongDescription';
		$i++;
	}
	
	if (strlen($_POST['BarCode']) >20) {
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
	
	if ($InputError !=1){
		
		if ($_POST['submit']==_('Update')) { /*so its an existing one */

				$sql = "UPDATE fixedassets
									SET longdescription='" . $_POST['LongDescription'] . "',
										description='" . $_POST['Description'] . "',
										categoryid='" . $_POST['AssetCategoryID'] . "',
										assetlocation='" . $_POST['AssetLocation'] . "',
										datepurchased='" . $_POST['DatePurchased'] . "',
										depntype='" . $_POST['DepnType'] . "',
										depnrate='" . $_POST['DepnRate'] . "',
										barcode='" . $_POST['BarCode'] . "',
										serialno='" . $_POST['SerialNo'] . "'
									WHERE assetid='" . $AssetID . "'";

				$ErrMsg = _('The asset could not be updated because');
				$DbgMsg = _('The SQL that was used to update the asset and failed was');
				$result = DB_query($sql,$db,$ErrMsg,$DbgMsg);

				prnMsg( _('Asset') . ' ' . $AssetID . ' ' . _('has been updated'), 'success');
				echo '<br>';
		} else { //it is a NEW part
			$sql = "INSERT INTO fixedassets (
																		description,
																		longdescription,
																		assetcategoryid,
																		assetlocation,
																		datepurchased,
																		depntype,
																		depnrate,
																		barcode,
																		serialno)
																	VALUES ('" . $AssetID . "',
																		'" . $_POST['Description'] . "',
																		'" . $_POST['LongDescription'] . "',
																		'" . $_POST['AssetCategoryID'] . "',
																		'" . $_POST['AssetLocation'] . "',
																		'" . $_POST['DatePurchased'] . "',
																		'" . $_POST['DepnType'] . "',
																		'" . $_POST['DepnRate']. "',
																		'" . $_POST['BarCode'] . "',
																		'" . $_POST['SerialNo'] . "'
																		)";
			$ErrMsg =  _('The asset could not be added because');
			$DbgMsg = _('The SQL that was used to add the asset failed was');
			$result = DB_query($sql,$db, $ErrMsg, $DbgMsg);
			
			if (DB_error_no($db) ==0) {
				prnMsg( _('New Item') .' ' . $AssetID . ' '. _('has been added to the database'),'success');
				unset($_POST['LongDescription']);
				unset($_POST['Description']);
//				unset($_POST['AssetCategoryID']);
//				unset($_POST['AssetLocation']);
				unset($_POST['DatePurchased']);
//				unset($_POST['DepnType']);
//				unset($_POST['DepnRate']);
				unset($_POST['BarCode']);
				unset($_POST['SerialNo']);
			}//ALL WORKED SO RESET THE FORM VARIABLES
		}
	} else {
		echo '<br>'. "\n";
		prnMsg( _('Validation failed, no updates or deletes took place'), 'error');
	}

} elseif (isset($_POST['delete']) AND strlen($_POST['delete']) >1 ) {
//the button to delete a selected record was clicked instead of the submit button

	$CancelDelete = 0;
	//what validation is required before allowing deletion of assets ....  maybe there should be no deletion option?
	$result = DB_query('SELECT cost-depn AS netbookvalue FROM fixedassets WHERE assetid="' . $AssetID . '"', $db);
	$AssetRow = DB_fetch_row($result);
	if ($AssetRow[0] !=0) {
		$CancelDelete =1; //cannot delete assets where NBV is not 0
	}
	if ($CancelDelete==0) {
		$result = DB_Txn_Begin($db);

			$sql="DELETE FROM fixedassets WHERE assetid='" . $AssetID . "'";
			$result=DB_query($sql,$db, _('Could not delete the asset record'),'',true);

		$result = DB_Txn_Commit($db);

		prnMsg(_('Deleted the asset  record for asset number' ) . ' ' . $AssetID );
		unset($_POST['LongDescription']);
		unset($_POST['Description']);
		unset($_POST['AssetCategoryID']);
		unset($_POST['AssetLocation']);
		unset($_POST['DatePurchased']);
		unset($_POST['DepnType']);
		unset($_POST['DepnRate']);
		unset($_POST['BarCode']);
		unset($_POST['SerialNo']);
		unset($AssetID);
		unset($_SESSION['SelectedAsset']);

	} //end if Delete Asset
}

echo '<form name="AssetForm" enctype="multipart/form-data" method="post" action="' . $_SERVER['PHP_SELF'] . '?' .SID .
	'"><table class=selection>';
echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';

if (!isset($AssetID) or $AssetID=='') {

/*If the page was called without $AssetID passed to page then assume a new asset is to be entered other wise the form showing the fields with the existing entries against the asset will show for editing with a hidden AssetID field. New is set to flag that the page may have called itself and still be entering a new asset, in which case the page needs to know not to go looking up details for an existing asset*/

	$New = true;
	echo '<input type="hidden" name="New" value="">'. "\n";
	
} elseif ($InputError!=1) { // Must be modifying an existing item and no changes made yet - need to lookup the details

	$sql = "SELECT assetid,
					description,
					longdescription,
					assetcategoryid,
					serialno,
					assetlocation,
					datepurchased,
					depntype,
					depnrate
		FROM fixedassets
		WHERE assetid ='" . $AssetID . "'";

	$result = DB_query($sql, $db);
	$myrow = DB_fetch_array($result);

	$_POST['LongDescription'] = $myrow['longdescription'];
	$_POST['Description'] = $myrow['description'];
	$_POST['AssetCategoryID']  = $myrow['assetcategoryid'];
	$_POST['SerialNo']  = $myrow['serialno'];
	$_POST['AssetLocation']  = $myrow['assetlocation'];
	$_POST['DatePurchased']  = ConvertSQLDate($myrow['DatePurchased']);
	$_POST['DepnType']  = $myrow['depntype'];
	$_POST['BarCode']  = $myrow['barcode'];
	$_POST['DepnRate']  = $myrow['depnrate'];
	
	echo '<tr><td>' . _('Asset Code') . ':</td><td>'.$AssetID.'</td></tr>'. "\n";
	echo '<input type="Hidden" name="AssetID" value='.$AssetID.'>'. "\n";

} else { // some changes were made to the data so don't re-set form variables to DB ie the code above
	echo '<tr><td>' . _('Asset Code') . ':</td><td>'.$AssetID.'</td></tr>';
	echo '<input type="Hidden" name="AssetID" value="' . $AssetID . '">';
}

if (isset($_POST['Description'])) {
	$Description = $_POST['Description'];
} else {
	$Description ='';
}
echo '<tr><td>' . _('Asset Description') . ' (' . _('short') . '):</td><td><input ' . (in_array('Description',$Errors) ?  'class="inputerror"' : '' ) .' type="Text" name="Description" size=52 maxlength=50 value="' . $Description . '"></td></tr>'."\n";

if (isset($_POST['LongDescription'])) {
	$LongDescription = AddCarriageReturns($_POST['LongDescription']);
} else {
	$LongDescription ='';
}
echo '<tr><td>' . _('Asset Description') . ' (' . _('long') . '):</td><td><textarea ' . (in_array('LongDescription',$Errors) ?  'class="texterror"' : '' ) .'  name="LongDescription" cols=40 rows=4>' . stripslashes($LongDescription) . '</textarea></td></tr>'."\n";

// Add image upload for New Item  - by Ori
echo '<tr><td>'. _('Image File (.jpg)') . ':</td><td><input type="file" id="ItemPicture" name="ItemPicture"></td>';
// EOR Add Image upload for New Item  - by Ori

if (function_exists('imagecreatefromjpg')){
	$StockImgLink = '<img src="GetStockImage.php?SID&automake=1&textcolor=FFFFFF&bgcolor=CCCCCC'.
		'&AssetID='.urlencode($AssetID).
		'&text='.
		'&width=64'.
		'&height=64'.
		'" >';
} else {
	if( isset($AssetID) and file_exists($_SESSION['part_pics_dir'] . '/ASSET_' .$AssetID.'.jpg') ) {
		$AssetImgLink = '<img src="' . $_SESSION['part_pics_dir'] . '/ASSET_' .$AssetID.'.jpg" >';
	} else {
		$AssetImgLink = _('No Image');
	}
}

if ($AssetImgLink!=_('No Image')) {
	echo '<td>' . _('Image') . '<br>'.$AssetImgLink . '</td></tr>';
} else {
	echo '</td></tr>';
}

// EOR Add Image upload for New Item  - by Ori

echo '<tr><td>' . _('Asset Category') . ':</td><td><select name="AssetCategoryID">';

$sql = 'SELECT categoryid, categorydescription FROM fixedassetcategories';
$ErrMsg = _('The asset categories could not be retrieved because');
$DbgMsg = _('The SQL used to retrieve stock categories and failed was');
$result = DB_query($sql,$db,$ErrMsg,$DbgMsg);

while ($myrow=DB_fetch_array($result)){
	if (!isset($_POST['AssetCategoryID']) or $myrow['categoryid']==$_POST['AssetCategoryID']){
		echo '<option selected VALUE="'. $myrow['categoryid'] . '">' . $myrow['categorydescription'];
	} else {
		echo '<option VALUE="'. $myrow['categoryid'] . '">' . $myrow['categorydescription'];
	}
	$category=$myrow['categoryid'];
}
echo '</select><a target="_blank" href="'. $rootpath . '/FixedAssetCategories.php?' . SID . '">'.' ' . _('Add or Modify Asset Categories') . '</a></td></tr>';
if (!isset($_POST['AssetCategoryID'])) {
	$_POST['AssetCategoryID']=$category;
}

$sql = 'SELECT locationid, locationdescription FROM fixedassetlocations';
$ErrMsg = _('The asset locations could not be retrieved because');
$DbgMsg = _('The SQL used to retrieve asset locations and failed was');
$result = DB_query($sql,$db,$ErrMsg,$DbgMsg);

echo '<tr><td>' . _('Asset Location') . ':</td><td><select name="AssetLocation">';
while ($myrow=DB_fetch_array($result)){
	if ($_POST['AssetLocation']==$myrow['locationid']){
		echo '<option selected value="' . $myrow['locationid'] .'">' . $myrow['locationdescription'] . '</option>';
	} else {
		echo '<option value="' . $myrow['locationid'] .'">' . $myrow['locationdescription'] . '</option>';
	}
}
echo '</select></td></tr>';


if (isset($_POST['BarCode'])) {
	$BarCode = $_POST['BarCode'];
} else {
	$BarCode='';
}
echo '<tr><td>' . _('Bar Code') . ':</td><td><input ' . (in_array('BarCode',$Errors) ?  'class="inputerror"' : '' ) .'  type="Text" name="BarCode" size=22 maxlength=20 value="' . $BarCode . '"></td></tr>';

echo '<tr><td>' . _('Tax Category') . ':</td><td><select name="TaxCat">';
$sql = 'SELECT taxcatid, taxcatname FROM taxcategories ORDER BY taxcatname';
$result = DB_query($sql, $db);

if (!isset($_POST['TaxCat'])){
	$_POST['TaxCat'] = $_SESSION['DefaultTaxCategory'];
}

while ($myrow = DB_fetch_array($result)) {
	if ($_POST['TaxCat'] == $myrow['taxcatid']){
		echo '<option selected value=' . $myrow['taxcatid'] . '>' . $myrow['taxcatname'] . '</option>';
	} else {
		echo '<option value=' . $myrow['taxcatid'] . '>' . $myrow['taxcatname'] . '</option>';
	}
} //end while loop

echo '</select></td></tr></table>';


if (!isset($_POST['AssetCategoryID'])) {
	$_POST['AssetCategoryID'] = '';
}
echo '<br><table class=selection><tr><th colspan="2">' . _('Depreciation Properties') . '</th></tr>';
$sql = "SELECT stkcatpropid,
							label,
							controltype,
							defaultvalue,
							maximumvalue,
							minimumvalue,
							numericvalue
					FROM stockcatproperties
					WHERE categoryid ='" . $_POST['AssetCategoryID'] . "'
					AND reqatsalesorder =0
					ORDER BY stkcatpropid";

$PropertiesResult = DB_query($sql,$db);
$PropertyCounter = 0;
$PropertyWidth = array();

while ($PropertyRow=DB_fetch_array($PropertiesResult)){

	$PropValResult = DB_query("SELECT value FROM
																		stockitemproperties
																		WHERE assetid='" . $AssetID . "'
																		AND stkcatpropid ='" . $PropertyRow['stkcatpropid'] . "'",
																	$db);
	$PropValRow = DB_fetch_row($PropValResult);
	$PropertyValue = $PropValRow[0];

	echo '<input type="hidden" name="PropID' . $PropertyCounter . '" value=' .$PropertyRow['stkcatpropid'] .'>';

	echo '<tr><td>' . $PropertyRow['label'] . '</td>
				<td>';
	switch ($PropertyRow['controltype']) {
	 	case 0; //textbox
	 		if ($PropertyRow['numericvalue']==1) {
				echo '<input type="textbox" class="number" name="PropValue' . $PropertyCounter . '" size="20" maxlength="100" value="' . $PropertyValue . '">';
				echo _('A number between') . ' ' . $PropertyRow['minimumvalue'] . ' ' . _('and') . ' ' . $PropertyRow['maximumvalue'] . ' ' . _('is expected');
			} else {
				echo '<input type="textbox" name="PropValue' . $PropertyCounter . '" size="20" maxlength="100" value="' . $PropertyValue . '">';
			}
	 		break;
	 	case 1; //select box
	 		$OptionValues = explode(',',$PropertyRow['defaultvalue']);
			echo '<select name="PropValue' . $PropertyCounter . '">';
			foreach ($OptionValues as $PropertyOptionValue){
				if ($PropertyOptionValue == $PropertyValue){
					echo '<option selected value="' . $PropertyOptionValue . '">' . $PropertyOptionValue . '</option>';
				} else {
					echo '<option value="' . $PropertyOptionValue . '">' . $PropertyOptionValue . '</option>';
				}
			}
			echo '</select>';
			break;
		case 2; //checkbox
			echo '<input type="checkbox" name="PropValue' . $PropertyCounter . '"';
			if ($PropertyValue==1){
				echo '"checked"';
			}
			echo '>';
			break;
	} //end switch
	echo '<input type="hidden" name="PropType' . $PropertyCounter .'" value=' . $PropertyRow['controltype'] . '>';
	echo '</td></tr>';
	$PropertyCounter++;
} //end loop round properties for the item category
echo '</table>';
echo '<input type="hidden" name="PropertyCounter" value=1>';

if ($New==1) {
	echo '<div class=centre><br><input type="Submit" name="submit" value="' . _('Insert New Item') . '">';

} else {

	// Now the form to enter the item properties
	echo '<br><div class=centre><input type="submit" name="submit" value="' . _('Update') . '"></div>';
	echo '<input type="submit" name="UpdateCategories" style="visibility:hidden;width:1px" value="' . _('Categories') . '">';
	prnMsg( _('Only click the Delete button if you are sure you wish to delete the asset.'), 'warn', _('WARNING'));
	echo '<br><div class=centre><input type="Submit" name="delete" value="' . _('Delete This Asset') . '" onclick="return confirm(\'' . _('Are You Sure?') . '\');"></div>';
}

echo '</form></div>';
include('includes/footer.inc');
?>
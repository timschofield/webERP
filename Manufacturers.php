<?php

include('includes/session.php');

$Title = _('Brands Maintenance');

$ViewTopic = 'Inventory';
$BookMark = '';

include('includes/header.php');
include ('includes/ImageFunctions.php');

if (isset($_GET['SelectedManufacturer'])){
	$SelectedManufacturer = $_GET['SelectedManufacturer'];
} elseif (isset($_POST['SelectedManufacturer'])){
	$SelectedManufacturer = $_POST['SelectedManufacturer'];
}

$SupportedImgExt = array('png','jpg','jpeg');

if (isset($_POST['submit'])) {


	//initialise no input errors assumed initially before we test
	$InputError = 0;

	/* actions to take once the user has clicked the submit button
	ie the page has called itself with some user input */

	if (isset($SelectedManufacturer) AND $InputError !=1) {

		if (isset($_FILES['BrandPicture']) AND $_FILES['BrandPicture']['name'] !='') {

			$Result	= $_FILES['BrandPicture']['error'];
		 	$UploadTheFile = 'Yes'; //Assume all is well to start off with

			$ImgExt = pathinfo($_FILES['BrandPicture']['name'], PATHINFO_EXTENSION);
			$FileName = $_SESSION['part_pics_dir'] . '/BRAND-' . $SelectedManufacturer . '.' . $ImgExt;

			 //But check for the worst
			if (!in_array ($ImgExt, $SupportedImgExt)) {
				prnMsg(_('Only ' . implode(", ", $SupportedImgExt) . ' files are supported - a file extension of ' . implode(", ", $SupportedImgExt) . ' is expected'),'warn');
				$UploadTheFile ='No';
			} elseif ( $_FILES['BrandPicture']['size'] > ($_SESSION['MaxImageSize']*1024)) { //File Size Check
				prnMsg(_('The file size is over the maximum allowed. The maximum size allowed in KB is') . ' ' . $_SESSION['MaxImageSize'],'warn');
				$UploadTheFile ='No';
			} elseif ( $_FILES['BrandPicture']['type'] == 'text/plain' ) {  //File Type Check
				prnMsg( _('Only graphics files can be uploaded'),'warn');
				 	$UploadTheFile ='No';
			}
			foreach ($SupportedImgExt as $Ext) {
				$File = $_SESSION['part_pics_dir'] . '/BRAND-' . $SelectedManufacturer . '.' . $Ext;
				if (file_exists ($File) ) {
					$Result = unlink($File);
					if (!$Result){
						prnMsg(_('The existing image could not be removed'),'error');
						$UploadTheFile ='No';
					}
				}
			}

			if ($UploadTheFile=='Yes'){
				$Result  =  move_uploaded_file($_FILES['BrandPicture']['tmp_name'], $FileName);
				$Message = ($Result)?_('File url')  . '<a href="' . $FileName .'">' .  $FileName . '</a>' : _('Something is wrong with uploading a file');
				$_POST['ManufacturersImage'] = 'BRAND-' . $SelectedManufacturer;
			} else {
				$_POST['ManufacturersImage'] = '';
			}
		}
		if( isset($_POST['ManufacturersImage'])){
			foreach ($SupportedImgExt as $Ext) {
				$File = $_SESSION['part_pics_dir'] . '/BRAND-' . $SelectedManufacturer . '.' . $Ext;
				if (file_exists ($File) ) {
					$_POST['ManufacturersImage'] = 'BRAND-' . $SelectedManufacturer;
					break;
				} else {
					$_POST['ManufacturersImage'] = '';
				}
			}

		}
		if (isset($_POST['ClearImage']) ) {
		foreach ($SupportedImgExt as $Ext) {
				$File = $_SESSION['part_pics_dir'] . '/BRAND-' . $SelectedManufacturer . '.' . $Ext;
				if (file_exists ($File) ) {
					@unlink($File);
					$_POST['ManufacturersImage'] = '';
					if(is_file($ImageFile)) {
						prnMsg(_('You do not have access to delete this item image file.'),'error');
					}
				}
			}
		}

		$SQL = "UPDATE manufacturers SET manufacturers_name='" . $_POST['ManufacturersName'] . "',
									manufacturers_url='" . $_POST['ManufacturersURL'] . "'";
		if (isset($_POST['ManufacturersImage'])){
			$SQL .= ", manufacturers_image='" . $_POST['ManufacturersImage'] . "'";
		}
		$SQL .= " WHERE manufacturers_id = '" . $SelectedManufacturer . "'";

		$ErrMsg = _('An error occurred updating the') . ' ' . $SelectedManufacturer . ' ' . _('manufacturer record because');
		$DbgMsg = _('The SQL used to update the manufacturer record was');

		$Result = DB_query($SQL,$ErrMsg,$DbgMsg);

		prnMsg( _('The manufacturer record has been updated'),'success');
		unset($_POST['ManufacturersName']);
		unset($_POST['ManufacturersURL']);
		unset($_POST['ManufacturersImage']);
		unset($SelectedManufacturer);

	} elseif ($InputError !=1) {

		/*SelectedManufacturer is null cos no item selected on first time round so must be adding a	record must be submitting new entries in the new Location form */

		$SQL = "INSERT INTO manufacturers (manufacturers_name,
										manufacturers_url)
						VALUES ('" . $_POST['ManufacturersName'] . "',
								'" . $_POST['ManufacturersURL'] . "')";

		$ErrMsg =  _('An error occurred inserting the new manufacturer record because');
		$DbgMsg =  _('The SQL used to insert the manufacturer record was');
		$Result = DB_query($SQL,$ErrMsg,$DbgMsg);

		if (isset($_FILES['BrandPicture']) AND $_FILES['BrandPicture']['name'] !='') {

			$Result	= $_FILES['BrandPicture']['error'];
		 	$UploadTheFile = 'Yes'; //Assume all is well to start off with

			$ImgExt = pathinfo($_FILES['BrandPicture']['name'], PATHINFO_EXTENSION);
			$FileName = $_SESSION['part_pics_dir'] . '/BRAND-' . $_SESSION['LastInsertId'] . '.' . $ImgExt;

			 //But check for the worst
			if (!in_array ($ImgExt, $SupportedImgExt)) {
				prnMsg(_('Only ' . implode(", ", $SupportedImgExt) . ' files are supported - a file extension of ' . implode(", ", $SupportedImgExt) . ' is expected'),'warn');
				$UploadTheFile ='No';
			} elseif ( $_FILES['BrandPicture']['size'] > ($_SESSION['MaxImageSize']*1024)) { //File Size Check
				prnMsg(_('The file size is over the maximum allowed. The maximum size allowed in KB is') . ' ' . $_SESSION['MaxImageSize'],'warn');
				$UploadTheFile ='No';
			} elseif ( $_FILES['BrandPicture']['type'] == 'text/plain' ) {  //File Type Check
				prnMsg( _('Only graphics files can be uploaded'),'warn');
				 	$UploadTheFile ='No';
			}
			foreach ($SupportedImgExt as $Ext) {
				$File = $_SESSION['part_pics_dir'] . '/BRAND-' . $_SESSION['LastInsertId'] . '.' . $Ext;
				if (file_exists ($File) ) {
					$Result = unlink($File);
					if (!$Result){
						prnMsg(_('The existing image could not be removed'),'error');
						$UploadTheFile ='No';
					}
				}
			}

			if ($UploadTheFile=='Yes'){
				$Result  =  move_uploaded_file($_FILES['BrandPicture']['tmp_name'], $FileName);
				$Message = ($Result)?_('File url')  . '<a href="' . $FileName .'">' .  $FileName . '</a>' : _('Something is wrong with uploading a file');
				DB_query("UPDATE manufacturers
					SET  manufacturers_image='" . 'BRAND-' . $_SESSION['LastInsertId'] . "'
					WHERE manufacturers_id = '" . $_SESSION['LastInsertId'] . "'
					");
			}
		}

		prnMsg( _('The new manufacturer record has been added'),'success');

		unset($_POST['ManufacturersName']);
		unset($_POST['ManufacturersURL']);
		unset($_POST['ManufacturersImage']);
		unset($SelectedManufacturer);
	}

} elseif (isset($_GET['delete'])) {
//the link to delete a selected record was clicked instead of the submit button

	$CancelDelete = false;

// PREVENT DELETES IF DEPENDENT RECORDS
	$SQL= "SELECT COUNT(*) FROM salescatprod WHERE manufacturers_id='". $SelectedManufacturer . "'";
	$Result = DB_query($SQL);
	$MyRow = DB_fetch_row($Result);
	if ($MyRow[0]>0) {
		$CancelDelete = true;
		prnMsg( _('Cannot delete this manufacturer because products have been defined as from this manufacturer'),'warn');
		echo  _('There are') . ' ' . $MyRow[0] . ' ' . _('items with this manufacturer code');
	}

	if (!$CancelDelete) {

		$Result = DB_query("DELETE FROM manufacturers WHERE manufacturers_id='" . $SelectedManufacturer . "'");
		foreach ($SupportedImgExt as $Ext) {
			$File = $_SESSION['part_pics_dir'] . '/BRAND-' . $SelectedManufacturer . '.' . $Ext;
			if (file_exists ($File) ) {
				@unlink($File);
			}
		}
		prnMsg( _('Manufacturer') . ' ' . $SelectedManufacturer . ' ' . _('has been deleted') . '!', 'success');
		unset ($SelectedManufacturer);
	} //end if Delete Manufacturer
	unset($SelectedManufacturer);
	unset($_GET['delete']);
}

if (!isset($SelectedManufacturer)) {

/* It could still be the second time the page has been run and a record has been selected for modification - SelectedManufacturer will exist because it was sent with the new call. If its the first time the page has been displayed with no parameters
then none of the above are true and the list of Manufacturers will be displayed with
links to delete or edit each. These will call the same page again and allow update/input
or deletion of the records*/

	$SQL = "SELECT manufacturers_id,
				manufacturers_name,
				manufacturers_url,
				manufacturers_image
			FROM manufacturers";
	$Result = DB_query($SQL);

	if (DB_num_rows($Result)==0){
		prnMsg (_('There are no manufacturers to display'),'error');
	}
	echo '<p class="page_title_text"><img src="'.$RootPath.'/css/'.$Theme.'/images/supplier.png" Title="' .
			_('Manufacturers') . '" alt="" />' . ' ' . $Title . '</p>';

	echo '<table class="selection">';
	echo '<tr>
			<th>' . _('Brand Code') . '</th>
			<th>' . _('Brand Name') . '</th>
			<th>' . _('Brand URL') . '</th>
			<th>' . _('Brands Image') . '</th>
		</tr>';

while ($MyRow = DB_fetch_array($Result)) {
    $Glob = (glob($_SESSION['part_pics_dir'] . '/BRAND-' . $MyRow['manufacturers_id'] . '.{' . implode(",", $SupportedImgExt) . '}', GLOB_BRACE));
	$ImageFile = reset($Glob);
	$BrandImgLink = GetImageLink($ImageFile, '/BRAND-' . $MyRow['manufacturers_id'], 120, 120, "", "");

	printf('<tr class="striped_row">
			<td>%s</td>
			<td>%s</td>
			<td><a target="_blank" href="%s">%s</a></td>
			<td>%s</td>
			<td><a href="%sSelectedManufacturer=%s&amp;edit=1">' . _('Edit') . '</a></td>
			<td><a href="%sSelectedManufacturer=%s&amp;delete=1" onclick="return confirm(\'' . _('Are you sure you wish to delete this brand?') . '\');">' . _('Delete') . '</a></td>
			</tr>',
			$MyRow['manufacturers_id'],
			$MyRow['manufacturers_name'],
			$MyRow['manufacturers_url'],
			$MyRow['manufacturers_url'],
			$BrandImgLink,
			htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') . '?',
			$MyRow['manufacturers_id'],
			htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') . '?',
			$MyRow['manufacturers_id']);

	}
	//END WHILE LIST LOOP
	echo '</table>';
}

//end of ifs and buts!

if (isset($SelectedManufacturer)) {
	echo '<a href="' . htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') . '">' . _('Review Records') . '</a>';
}

if (!isset($_GET['delete'])) {

	echo '<form enctype="multipart/form-data" method="post" action="' . htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') . '">';
	echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';

	if (isset($SelectedManufacturer)) {
		//editing an existing Brand
		echo '<p class="page_title_text"><img src="'.$RootPath.'/css/'.$Theme.'/images/supplier.png" Title="' .
			_('Brand') . '" alt="" />' . ' ' . $Title . '</p>';

		$SQL = "SELECT manufacturers_id,
					manufacturers_name,
					manufacturers_url,
					manufacturers_image
				FROM manufacturers
				WHERE manufacturers_id='" . $SelectedManufacturer . "'";

		$Result = DB_query($SQL);
		$MyRow = DB_fetch_array($Result);

		$_POST['ManufacturersName']  = $MyRow['manufacturers_name'];
		$_POST['ManufacturersURL'] = $MyRow['manufacturers_url'];
		$_POST['ManufacturersImage'] = $MyRow['manufacturers_image'];


		echo '<input type="hidden" name="SelectedManufacturer" value="' . $SelectedManufacturer . '" />';
		echo '<fieldset>';
		echo '<legend>' . _('Amend Brand Details') . '</legend>';
	} else { //end of if $SelectedManufacturer only do the else when a new record is being entered

		echo '<fieldset>
				<legend>' . _('New Brand/Manufacturer Details') . '</legend>';
	}
	if (!isset($_POST['ManufacturersName'])) {
		$_POST['ManufacturersName'] = '';
	}
	if (!isset($_POST['ManufacturersURL'])) {
		$_POST['ManufacturersURL'] = ' ';
	}
	if (!isset($_POST['ManufacturersImage'])) {
		$_POST['ManufacturersImage'] = '';
	}

	echo '<field>
			<label for="ManufacturersName">' .  _('Brand Name') . ':' . '</label>
			<input type="text" required="required" autofocus="autofocus" name="ManufacturersName" value="'. $_POST['ManufacturersName'] . '" size="32" maxlength="32" />
		</field>
		<field>
			<label for="ManufacturersURL">' . _('Brand URL') . ':' . '</label>
			<input type="text" name="ManufacturersURL" value="' . $_POST['ManufacturersURL'] . '" size="50" maxlength="50" />
		</field>
		<field>
			<label for="BrandPicture">' .  _('Brand Image File (' . implode(", ", $SupportedImgExt) . ')') . ':</label>
			<input type="file" id="BrandPicture" name="BrandPicture" />';

	if (isset ($_GET['edit']) ) {
		echo '<field>
				<label for="ClearImage">'._('Clear Image').'</label>
				<input type="checkbox" name="ClearImage" id="ClearImage" value="1">
			</field>';
	}

	echo '</field>';
		if (isset($SelectedManufacturer)){
            $Glob = (glob($_SESSION['part_pics_dir'] . '/BRAND-' . $SelectedManufacturer . '.{' . implode(",", $SupportedImgExt) . '}', GLOB_BRACE));
			$ImageFile = reset($Glob);
			if (extension_loaded('xgd') && function_exists('gd_info') && file_exists($ImageFile)){
				$BrandImgLink = '<img src="GetStockImage.php?automake=1&amp;textcolor=FFFFFF&amp;bgcolor=CCCCCC'.
					'&amp;StockID='.urlencode('/BRAND-' . $SelectedManufacturer).
					'&amp;text='.
					'&amp;width=100'.
					'&amp;height=100'.
					'" alt="" />';
			} else {
				if( isset($SelectedManufacturer) AND  !empty($SelectedManufacturer) AND file_exists($ImageFile) ) {
					$BrandImgLink = '<img src="' . $ImageFile . '" height="100" width="100" />';
				} else {
					$BrandImgLink = _('No Image');
				}
			}
			$BrandImgLink = GetImageLink($ImageFile, '/BRAND-' . $SelectedManufacturer, 100, 100, "", "");
			echo '<field><td colspan="2">' . $BrandImgLink . '</td></field>';
		}

		echo 	'</fieldset>
			<div class="centre">
				<input type="submit" name="submit" value="' .  _('Enter Information') . '" />
			</div>
			</form>';

} //end if record deleted no point displaying form to add record

include('includes/footer.php');
?>
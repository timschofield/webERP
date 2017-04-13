<?php


/* $Id: Manufacturers.php 5498 2012-07-13 08:35:54Z tehonu $*/

include('includes/session.php');

$Title = _('Brands Maintenance');

include('includes/header.php');

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

			$result	= $_FILES['BrandPicture']['error'];
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
			foreach ($SupportedImgExt as $ext) {
				$file = $_SESSION['part_pics_dir'] . '/BRAND-' . $SelectedManufacturer . '.' . $ext;
				if (file_exists ($file) ) {
					$result = unlink($file);
					if (!$result){
						prnMsg(_('The existing image could not be removed'),'error');
						$UploadTheFile ='No';
					}
				}
			}

			if ($UploadTheFile=='Yes'){
				$result  =  move_uploaded_file($_FILES['BrandPicture']['tmp_name'], $FileName);
				$message = ($result)?_('File url')  . '<a href="' . $FileName .'">' .  $FileName . '</a>' : _('Something is wrong with uploading a file');
				$_POST['ManufacturersImage'] = 'BRAND-' . $SelectedManufacturer;
			} else {
				$_POST['ManufacturersImage'] = '';
			}
		}
		if( isset($_POST['ManufacturersImage'])){
			foreach ($SupportedImgExt as $ext) {
				$file = $_SESSION['part_pics_dir'] . '/BRAND-' . $SelectedManufacturer . '.' . $ext;
				if (file_exists ($file) ) {
					$_POST['ManufacturersImage'] = 'BRAND-' . $SelectedManufacturer;
					break;
				} else {
					$_POST['ManufacturersImage'] = '';
				}
			}

		}
		if (isset($_POST['ClearImage']) ) {
		foreach ($SupportedImgExt as $ext) {
				$file = $_SESSION['part_pics_dir'] . '/BRAND-' . $SelectedManufacturer . '.' . $ext;
				if (file_exists ($file) ) {
					@unlink($file);
					$_POST['ManufacturersImage'] = '';
					if(is_file($imagefile)) {
						prnMsg(_('You do not have access to delete this item image file.'),'error');
					}
				}
			}
		}

		$sql = "UPDATE manufacturers SET manufacturers_name='" . $_POST['ManufacturersName'] . "',
									manufacturers_url='" . $_POST['ManufacturersURL'] . "'";
		if (isset($_POST['ManufacturersImage'])){
			$sql .= ", manufacturers_image='" . $_POST['ManufacturersImage'] . "'";
		}
		$sql .= " WHERE manufacturers_id = '" . $SelectedManufacturer . "'";

		$ErrMsg = _('An error occurred updating the') . ' ' . $SelectedManufacturer . ' ' . _('manufacturer record because');
		$DbgMsg = _('The SQL used to update the manufacturer record was');

		$result = DB_query($sql,$ErrMsg,$DbgMsg);

		prnMsg( _('The manufacturer record has been updated'),'success');
		unset($_POST['ManufacturersName']);
		unset($_POST['ManufacturersURL']);
		unset($_POST['ManufacturersImage']);
		unset($SelectedManufacturer);

	} elseif ($InputError !=1) {

		/*SelectedManufacturer is null cos no item selected on first time round so must be adding a	record must be submitting new entries in the new Location form */

		$sql = "INSERT INTO manufacturers (manufacturers_name,
										manufacturers_url)
						VALUES ('" . $_POST['ManufacturersName'] . "',
								'" . $_POST['ManufacturersURL'] . "')";

		$ErrMsg =  _('An error occurred inserting the new manufacturer record because');
		$DbgMsg =  _('The SQL used to insert the manufacturer record was');
		$result = DB_query($sql,$ErrMsg,$DbgMsg);

		if (isset($_FILES['BrandPicture']) AND $_FILES['BrandPicture']['name'] !='') {

			$result	= $_FILES['BrandPicture']['error'];
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
			foreach ($SupportedImgExt as $ext) {
				$file = $_SESSION['part_pics_dir'] . '/BRAND-' . $_SESSION['LastInsertId'] . '.' . $ext;
				if (file_exists ($file) ) {
					$result = unlink($file);
					if (!$result){
						prnMsg(_('The existing image could not be removed'),'error');
						$UploadTheFile ='No';
					}
				}
			}

			if ($UploadTheFile=='Yes'){
				$result  =  move_uploaded_file($_FILES['BrandPicture']['tmp_name'], $FileName);
				$message = ($result)?_('File url')  . '<a href="' . $FileName .'">' .  $FileName . '</a>' : _('Something is wrong with uploading a file');
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
	$sql= "SELECT COUNT(*) FROM salescatprod WHERE manufacturers_id='". $SelectedManufacturer . "'";
	$result = DB_query($sql);
	$myrow = DB_fetch_row($result);
	if ($myrow[0]>0) {
		$CancelDelete = true;
		prnMsg( _('Cannot delete this manufacturer because products have been defined as from this manufacturer'),'warn');
		echo  _('There are') . ' ' . $myrow[0] . ' ' . _('items with this manufacturer code');
	}

	if (!$CancelDelete) {

		$result = DB_query("DELETE FROM manufacturers WHERE manufacturers_id='" . $SelectedManufacturer . "'");
		foreach ($SupportedImgExt as $ext) {
			$file = $_SESSION['part_pics_dir'] . '/BRAND-' . $SelectedManufacturer . '.' . $ext;
			if (file_exists ($file) ) {
				@unlink($file);
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

	$sql = "SELECT manufacturers_id,
				manufacturers_name,
				manufacturers_url,
				manufacturers_image
			FROM manufacturers";
	$result = DB_query($sql);

	if (DB_num_rows($result)==0){
		prnMsg (_('There are no manufacturers to display'),'error');
	}
	echo '<p class="page_Title_text"><img src="'.$RootPath.'/css/'.$Theme.'/images/supplier.png" Title="' .
			_('Manufacturers') . '" alt="" />' . ' ' . $Title . '</p>';

	echo '<table class="selection">';
	echo '<tr>
			<th>' . _('Brand Code') . '</th>
			<th>' . _('Brand Name') . '</th>
			<th>' . _('Brand URL') . '</th>
			<th>' . _('Brands Image') . '</th>
		</tr>';

$k=0; //row colour counter
while ($myrow = DB_fetch_array($result)) {
	if ($k==1){
		echo '<tr class="EvenTableRows">';
		$k=0;
	} else {
		echo '<tr class="OddTableRows">';
		$k=1;
	}

	$imagefile = reset((glob($_SESSION['part_pics_dir'] . '/BRAND-' . $myrow['manufacturers_id'] . '.{' . implode(",", $SupportedImgExt) . '}', GLOB_BRACE)));
	if (extension_loaded('gd') && function_exists('gd_info') && file_exists($imagefile)){
		$BrandImgLink = '<img src="GetStockImage.php?automake=1&amp;textcolor=FFFFFF&amp;bgcolor=CCCCCC'.
			'&amp;StockID='.urlencode('/BRAND-' . $myrow['manufacturers_id']).
			'&amp;text='.
			'&amp;width=120'.
			'&amp;height=120'.
			'" alt="" />';
	} else if (file_exists ($imagefile)) {
		$BrandImgLink = '<img src="' . $imagefile . '" height="120" width="120" />';
	} else {
		$BrandImgLink = _('No Image');
	}

	printf('<td>%s</td>
			<td>%s</td>
			<td><a target="_blank" href="%s">%s</a></td>
			<td>%s</td>
			<td><a href="%sSelectedManufacturer=%s&amp;edit=1">' . _('Edit') . '</a></td>
			<td><a href="%sSelectedManufacturer=%s&amp;delete=1" onclick="return confirm(\'' . _('Are you sure you wish to delete this brand?') . '\');">' . _('Delete') . '</a></td>
			</tr>',
			$myrow['manufacturers_id'],
			$myrow['manufacturers_name'],
			$myrow['manufacturers_url'],
			$myrow['manufacturers_url'],
			$BrandImgLink,
			htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') . '?',
			$myrow['manufacturers_id'],
			htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') . '?',
			$myrow['manufacturers_id']);

	}
	//END WHILE LIST LOOP
	echo '</table>';
}

//end of ifs and buts!

echo '<br />';
if (isset($SelectedManufacturer)) {
	echo '<a href="' . htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') . '">' . _('Review Records') . '</a>';
}
echo '<br />';

if (!isset($_GET['delete'])) {

	echo '<form enctype="multipart/form-data" method="post" action="' . htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') . '">';
    echo '<div>';
	echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';

	if (isset($SelectedManufacturer)) {
		//editing an existing Brand
		echo '<p class="page_Title_text"><img src="'.$RootPath.'/css/'.$Theme.'/images/supplier.png" Title="' .
			_('Brand') . '" alt="" />' . ' ' . $Title . '</p>';

		$sql = "SELECT manufacturers_id,
					manufacturers_name,
					manufacturers_url,
					manufacturers_image
				FROM manufacturers
				WHERE manufacturers_id='" . $SelectedManufacturer . "'";

		$result = DB_query($sql);
		$myrow = DB_fetch_array($result);

		$_POST['ManufacturersName']  = $myrow['manufacturers_name'];
		$_POST['ManufacturersURL'] = $myrow['manufacturers_url'];
		$_POST['ManufacturersImage'] = $myrow['manufacturers_image'];


		echo '<input type="hidden" name="SelectedManufacturer" value="' . $SelectedManufacturer . '" />';
		echo '<table class="selection">';
		echo '<tr>
				<th colspan="2">' . _('Amend Brand Details') . '</th>
			</tr>';
	} else { //end of if $SelectedManufacturer only do the else when a new record is being entered

		echo '<table class="selection">
				<tr>
					<th colspan="2"><h3>' . _('New Brand/Manufacturer Details') . '</h3></th>
				</tr>';
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

	echo '<tr>
			<td>' .  _('Brand Name') . ':' . '</td>
			<td><input type="text" required="required" autofocus="autofocus" name="ManufacturersName" value="'. $_POST['ManufacturersName'] . '" size="32" maxlength="32" /></td>
		</tr>
		<tr>
			<td>' . _('Brand URL') . ':' . '</td>
			<td><input type="text" name="ManufacturersURL" value="' . $_POST['ManufacturersURL'] . '" size="50" maxlength="50" /></td>
		</tr>
		<tr>
			<td>' .  _('Brand Image File (' . implode(", ", $SupportedImgExt) . ')') . ':</td>
			<td><input type="file" id="BrandPicture" name="BrandPicture" />';

	if (isset ($_GET['edit']) ) {
		echo '	<br /><input type="checkbox" name="ClearImage" id="ClearImage" value="1" > '._('Clear Image').' ';
	}

	echo '	</td>
		</tr>';
		if (isset($SelectedManufacturer)){

			$imagefile = reset((glob($_SESSION['part_pics_dir'] . '/BRAND-' . $SelectedManufacturer . '.{' . implode(",", $SupportedImgExt) . '}', GLOB_BRACE)));
			if (extension_loaded('xgd') && function_exists('gd_info') && file_exists($imagefile)){
				$BrandImgLink = '<img src="GetStockImage.php?automake=1&amp;textcolor=FFFFFF&amp;bgcolor=CCCCCC'.
					'&amp;StockID='.urlencode('/BRAND-' . $SelectedManufacturer).
					'&amp;text='.
					'&amp;width=100'.
					'&amp;height=100'.
					'" alt="" />';
			} else {
				if( isset($SelectedManufacturer) AND  !empty($SelectedManufacturer) AND file_exists($imagefile) ) {
					$BrandImgLink = '<img src="' . $imagefile . '" height="100" width="100" />';
				} else {
					$BrandImgLink = _('No Image');
				}
			}
			echo '<tr><td colspan="2">' . $BrandImgLink . '</td></tr>';
		}

		echo 	'</table>
			<br />
			<div class="centre">
				<input type="submit" name="submit" value="' .  _('Enter Information') . '" />
			</div>
	        </div>
			</form>';

} //end if record deleted no point displaying form to add record

include('includes/footer.php');
?>
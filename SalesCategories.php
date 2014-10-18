<?php

/* $Id: SalesCategories.php 6310 2013-08-29 10:42:50Z daintree $ */

include('includes/session.inc');

$Title = _('Sales Category Maintenance');

include('includes/header.inc');

echo '<p class="page_title_text"><img src="'.$RootPath.'/css/'.$Theme.'/images/inventory.png" title="' . _('Search') . '" alt="" />' . ' ' . $Title . '</p>';

if (isset($_GET['SelectedCategory'])){
	$SelectedCategory = mb_strtoupper($_GET['SelectedCategory']);
} else if (isset($_POST['SelectedCategory'])){
	$SelectedCategory = mb_strtoupper($_POST['SelectedCategory']);
}

if (isset($_GET['ParentCategory'])){
	$ParentCategory = mb_strtoupper($_GET['ParentCategory']);
} else if (isset($_POST['ParentCategory'])){
	$ParentCategory = mb_strtoupper($_POST['ParentCategory']);
}

if(isset($ParentCategory) AND $ParentCategory == 0 ) {
	unset($ParentCategory);
}

if (isset($_GET['EditName'])){
	$EditName = $_GET['EditName'];
} else if (isset($_POST['EditName'])){
	$EditName = $_POST['EditName'];
}

if (isset($SelectedCategory) AND isset($_FILES['CategoryPicture']) AND $_FILES['CategoryPicture']['name'] !='') {

	$result    = $_FILES['CategoryPicture']['error'];
 	$UploadTheFile = 'Yes'; //Assume all is well to start off with
 	// Stock is always capatalized so there is no confusion since "cat_" is lowercase
	$FileName = $_SESSION['part_pics_dir'] . '/SALESCAT_' . $SelectedCategory . '.jpg';

	 //But check for the worst
	if (mb_strtoupper(mb_substr(trim($_FILES['CategoryPicture']['name']),mb_strlen($_FILES['CategoryPicture']['name'])-3))!='JPG'){
		prnMsg(_('Only jpg files are supported - a file extension of .jpg is expected'),'warn');
		$UploadTheFile ='No';
	} elseif ( $_FILES['CategoryPicture']['size'] > ($_SESSION['MaxImageSize']*1024)) { //File Size Check
		prnMsg(_('The file size is over the maximum allowed. The maximum size allowed in KB is') . ' ' . $_SESSION['MaxImageSize'],'warn');
		$UploadTheFile ='No';
	} elseif ( $_FILES['CategoryPicture']['type'] == 'text/plain' ) {  //File Type Check
		prnMsg( _('Only graphics files can be uploaded'),'warn');
         	$UploadTheFile ='No';
	} elseif (file_exists($FileName)){
		prnMsg(_('Attempting to overwrite an existing item image'),'warn');
		$result = unlink($FileName);
		if (!$result){
			prnMsg(_('The existing image could not be removed'),'error');
			$UploadTheFile ='No';
		}
	}

	if ($UploadTheFile=='Yes'){
		$result  =  move_uploaded_file($_FILES['CategoryPicture']['tmp_name'], $FileName);
		$message = ($result)?_('File url')  . '<a href="' . $FileName .'">' .  $FileName . '</a>' : _('Somthing is wrong with uploading a file');
	}
 /* EOR Add Image upload for New Item  - by Ori */
}



if (isset($_POST['submit'])  AND isset($EditName) ) { // Creating or updating a category

	//initialise no input errors assumed initially before we test
	$InputError = 0;

	/* actions to take once the user has clicked the submit button
	ie the page has called itself with some user input */

	//first off validate inputs sensible

	if (mb_strlen($_POST['SalesCatName']) >50 OR trim($_POST['SalesCatName'])=='') {
		$InputError = 1;
		prnMsg(_('The Sales category description must be fifty characters or less long'),'error');
	}

	if (isset($SelectedCategory) and $InputError !=1 ) {

		/*SelectedCategory could also exist if submit had not been clicked this code
		would not run in this case cos submit is false of course  see the
		Delete code below*/

		$sql = "UPDATE salescat SET salescatname = '" . $_POST['SalesCatName'] . "',
									active  = '" . $_POST['Active'] . "'
				WHERE salescatid = '" .$SelectedCategory . "'";
		$msg = _('The Sales category record has been updated');
	} elseif ($InputError !=1) {

	/*Selected category is null cos no item selected on first time round so must be adding a	record must be submitting new entries in the new stock category form */

		$sql = "INSERT INTO salescat (salescatname,
									   parentcatid,
									   active)
						   VALUES (	'" . $_POST['SalesCatName'] . "',
									'" . (isset($ParentCategory)?($ParentCategory):('NULL')) . "',
									'" . $_POST['Active'] . "')";
		$msg = _('A new Sales category record has been added');
	}

	if ($InputError!=1){
		//run the SQL from either of the above possibilites
		$result = DB_query($sql,$db);
		prnMsg($msg,'success');
	}

	unset ($SelectedCategory);
	unset($_POST['SalesCatName']);
	unset($_POST['Active']);
	unset($EditName);

} elseif (isset($_GET['Delete']) AND isset($EditName)) {
//the link to Delete a selected record was clicked instead of the submit button

// PREVENT DELETES IF DEPENDENT RECORDS IN 'salescatprod'

	$sql= "SELECT COUNT(*) FROM salescatprod WHERE salescatid='".$SelectedCategory . "'";
	$result = DB_query($sql,$db);
	$myrow = DB_fetch_row($result);
	if ($myrow[0]>0) {
		prnMsg(_('Cannot delete this sales category because stock items have been added to this category') . '<br /> ' . _('There are') . ' ' . $myrow[0] . ' ' . _('items under to this category'),'warn');

	} else {
		$sql = "SELECT COUNT(*) FROM salescat WHERE parentcatid='".$SelectedCategory."'";
		$result = DB_query($sql,$db);
		$myrow = DB_fetch_row($result);
		if ($myrow[0]>0) {
		prnMsg(_('Cannot delete this sales category because sub categories have been added to this category') . '<br /> ' . _('There are') . ' ' . $myrow[0] . ' ' . _('sub categories'),'warn');
		} else {
			$sql="DELETE FROM salescat WHERE salescatid='".$SelectedCategory."'";
			$result = DB_query($sql,$db);
			prnMsg(_('The sales category') . ' ' . $SelectedCategory . ' ' . _('has been deleted') .
				' !','success');
			if( file_exists($_SESSION['part_pics_dir'] . '/SALESCAT_' . $SelectedCategory . '.jpg') ) {
				unlink($_SESSION['part_pics_dir'] . '/SALESCAT_' . $SelectedCategory . '.jpg');
			}
			unset ($SelectedCategory);
		}
	} //end if stock category used in debtor transactions
	unset($_GET['Delete']);
	unset($EditName);
} elseif( isset($_POST['submit']) AND isset($_POST['AddStockID']) AND $_POST['Brand']!='') {
	$sql = "INSERT INTO salescatprod (stockid,
										salescatid,
										manufacturers_id) 
							VALUES ('". $_POST['AddStockID']."',
									'".(isset($ParentCategory)?($ParentCategory):('NULL'))."',
									'" . $_POST['Brand'] . "')";
	$result = DB_query($sql,$db);
	prnMsg(_('Item') . ' ' . $_POST['AddStockID'] . ' ' . _('has been added'),'success');
	unset($_POST['AddStockID']);
} elseif( isset($_GET['DelStockID']) ) {
	$sql = "DELETE FROM salescatprod WHERE
				stockid='". $_GET['DelStockID']."' 
				AND salescatid".(isset($ParentCategory)?('='.$ParentCategory):(' IS NULL'));
	$result = DB_query($sql,$db);
	prnMsg(_('Stock item') . ' ' . $_GET['DelStockID'] . ' ' . _('has been removed') .
		' !','success');
	unset($_GET['DelStockID']);
} elseif ( isset($_GET['AddFeature'])){
	$result = DB_query("UPDATE salescatprod SET featured=1 WHERE stockid='" . $_GET['StockID'] . "'",$db);
} elseif (isset($_GET['RemoveFeature'])){
	$result = DB_query("UPDATE salescatprod SET featured=0 WHERE stockid='" . $_GET['StockID'] . "'",$db);
}

// ----------------------------------------------------------------------------------------
// Calculate Path for navigation
$CategoryPath = '<a href="'.htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') . '?ParentCategory=0">' . _('Main') . '</a>' . "&nbsp;\\&nbsp;";

$TempPath = '';
if (!isset($ParentCategory)){
	$ParentCategory=0;
}

if (isset($ParentCategory)) {
	$TmpParentID = $ParentCategory;
}

$LastParentName = '';
for($Busy = (isset($TmpParentID) AND ($TmpParentID != 0));
	$Busy == true;
	$Busy = (isset($TmpParentID) AND ($TmpParentID != 0)) ) {
	$sql = "SELECT parentcatid, salescatname FROM salescat WHERE salescatid='".$TmpParentID."'";
	$result = DB_query($sql,$db);
	if( $result ) {
		if (DB_num_rows($result) > 0) {
			$row = DB_fetch_array($result);
			$LastParentName =  $row['salescatname'];
			$TempPath = '<a href="'.htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') . '?ParentCategory='.$TmpParentID.'">' . $LastParentName . '</a>' . '&nbsp;\\&nbsp;' . $TempPath;
			$TmpParentID = $row['parentcatid']; // Set For Next Round
		} else {
			$Busy = false;
		}
		DB_free_result($result);
	}
}

$CategoryPath = $CategoryPath.$TempPath;

echo '<br />
		<div class="centre">
			<i>' . _('Selected Sales Category Path') . '</i>&nbsp;:&nbsp;'. $CategoryPath . '&nbsp;*&nbsp;
		</div>';

if ($ParentCategory!=0 ){
	echo '<div class="centre"><a href="' . $RootPath . '/SalesCategoryDescriptions.php?SelectedSalesCategory=' . $ParentCategory . '">' . _('Manage Sales Category Translations') . '</a></div>';
}

// END Calculate Path for navigation
// ----------------------------------------------------------------------------------------


// ----------------------------------------------------------------------------------------
// We will always display Categories

/* It could still be the second time the page has been run and a record has been selected for modification - SelectedCategory will exist because it was sent with the new call. If its the first time the page has been displayed with no parameters
then none of the above are true and the list of stock categorys will be displayed with
links to delete or edit each. These will call the same page again and allow update/input
or deletion of the records*/

$sql = "SELECT salescatid,
				salescatname,
				active
			FROM salescat
			WHERE parentcatid". (isset($ParentCategory)?('='.$ParentCategory):' =0') . "
			ORDER BY salescatname";
$result = DB_query($sql,$db);


echo '<br />';
if (DB_num_rows($result) == 0) {
	prnMsg(_('There are no categories defined at this level.'));
} else {
	echo '<table class="selection">';
	echo '<tr>
			<th>' . _('Sub Category') . '</th>
			<th>' . _('Active?') . '</th>
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

		if( file_exists($_SESSION['part_pics_dir'] . '/SALESCAT_' . $myrow['salescatid'] . '.jpg') ) {
			$CatImgLink = '<img src="GetStockImage.php?automake=1&amp;textcolor=FFFFFF&amp;bgcolor=CCCCCC&amp;StockID=' . 'SALESCAT_' . $myrow['salescatid'] . '&amp;text=&amp;width=120&amp;height=120" alt="" />';
		} else {
			$CatImgLink = _('No Image');
		}
		if ($myrow['active'] == 1){
			$Active = _('Yes');
		}else{
			$Active = _('No');
		}

		printf('<td>%s</td>
				<td>%s</td>
				<td><a href="%sParentCategory=%s">' . _('Select') . '</td>
				<td><a href="%sSelectedCategory=%s&amp;ParentCategory=%s">' . _('Edit') . '</td>
				<td><a href="%sSelectedCategory=%s&amp;Delete=yes&amp;EditName=1&amp;ParentCategory=%s" onclick="return confirm(\'' . _('Are you sure you wish to delete this sales category?') . '\');">' . _('Delete')  . '</a></td>
				<td>%s</td>
				</tr>',
				$myrow['salescatname'],
				$Active,
				htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') . '?',
				$myrow['salescatid'],
				htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') . '?',
				$myrow['salescatid'],
				$ParentCategory,
				htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') . '?',
				$myrow['salescatid'],
				$ParentCategory,
				$CatImgLink);
	}
	//END WHILE LIST LOOP
	echo '</table>';
}

// END display Categories
// ----------------------------------------------------------------------------------------
//end of ifs and buts!


// ----------------------------------------------------------------------------------------
// Show New or Edit Category

echo '<br /><form enctype="multipart/form-data" method="post" action="' . htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') . '">';
echo '<div>';
echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';

// This array will contain the stockids in use for this category
if (isset($SelectedCategory)) {
	//editing an existing stock category

	$sql = "SELECT salescatid, 
				parentcatid, 
				salescatname,
				active
			FROM salescat
			WHERE salescatid='". $SelectedCategory."'";

	$result = DB_query($sql, $db);
	$myrow = DB_fetch_array($result);

	$_POST['SalesCatId'] = $myrow['salescatid'];
	$_POST['ParentCategory']  = $myrow['parentcatid'];
	$_POST['SalesCatName']  = $myrow['salescatname'];
	$_POST['Active']  = $myrow['active'];

	echo '<input type="hidden" name="SelectedCategory" value="' . $SelectedCategory . '" />';
	echo '<input type="hidden" name="ParentCategory" value="' . (isset($_POST['ParentCatId'])?($_POST['ParentCategory']):('0')) . '" />';
	$FormCaps = _('Edit Sub Category');

} else { //end of if $SelectedCategory only do the else when a new record is being entered
	$_POST['SalesCatName']  = '';
	if (isset($ParentCategory)) {
		$_POST['ParentCategory']  = $ParentCategory;
	}
	echo '<input type="hidden" name="ParentCategory" value="' .
        (isset($_POST['ParentCategory'])?($_POST['ParentCategory']):('0')) . '" />';
	$FormCaps = _('New Sub Category');
}
echo '<input type="hidden" name="EditName" value="1" />';

echo '<table class="selection">
		<tr>
			<th colspan="2">' . $FormCaps . '</th>
		</tr>
		<tr>
			<td>' . _('Category Name') . ':</td>
			<td><input type="text" name="SalesCatName" size="20" maxlength="20" value="' . $_POST['SalesCatName'] . '" /></td>
		</tr>';

echo '<tr>
		<td>' . _('Display in webSHOP?') . ':</td>
		<td><select name="Active">';
if ($_POST['Active'] == '1') {
	echo '<option selected="selected" value="1">' . _('Yes') . '</option>';
	echo '<option value="0">' . _('No') . '</option>';
} else {
	echo '<option selected="selected" value="0">' . _('No') . '</option>';
	echo '<option value="1">' . _('Yes') . '</option>';
}
echo '</select></td>
	</tr>';		
	
// Image upload only if we have a selected category
if (isset($SelectedCategory)) {
	echo '<tr>
			<td>' .  _('Image File (.jpg)') . ':</td>
			<td><input type="file" id="CategoryPicture" name="CategoryPicture" /></td>
		</tr>';
}

echo '</table>
		<br />
		<div class="centre">
			<input type="submit" name="submit" value="' . _('Submit Information') . '" />
		</div>
		</div>
		</form>';

// END Show New or Edit Category
// ----------------------------------------------------------------------------------------

// ----------------------------------------------------------------------------------------
// Always display Stock Select screen

// $sql = "SELECT stockid, description FROM stockmaster ORDER BY stockid";
/*
$sql = "SELECT sm.stockid, sm.description FROM stockmaster as sm
	WHERE NOT EXISTS
		( SELECT scp.stockid FROM salescatprod as scp
			WHERE
				scp.salescatid". (isset($ParentCategory)?('='.$ParentCategory):' IS NULL') ."
			AND
				scp.stockid = sm.stockid
	) ORDER BY sm.stockid";
*/

// Now add this stockid to the array
$StockIDs = array();
$sql = "SELECT stockid,
				manufacturers_id
		FROM salescatprod
		WHERE salescatid". (isset($ParentCategory)?('='.$ParentCategory):' is NULL') . "
		ORDER BY stockid";
$result = DB_query($sql,$db);
if($result AND DB_num_rows($result)) {
	while( $myrow = DB_fetch_array($result) ) {
		$StockIDs[] = $myrow['stockid']; // Add Stock
	}
	DB_free_result($result);
}

// This query will return the stock that is available
$sql = "SELECT stockid, 
				description 
		FROM stockmaster INNER JOIN stockcategory 
		ON stockmaster.categoryid=stockcategory.categoryid 
		WHERE discontinued = 0
		AND mbflag<>'G'
		AND stocktype<>'M'
		ORDER BY stockid";
$result = DB_query($sql,$db);
if($result AND DB_num_rows($result)) {
	// continue id stock id in the stockid array
	echo '<br />
			<form enctype="multipart/form-data" method="post" action="' . htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') .'">
			<div>
			<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';
	if( isset($SelectedCategory) ) { // If we selected a category we need to keep it selected
		echo '<input type="hidden" name="SelectedCategory" value="' . $SelectedCategory . '" />';
	}
	echo '<input type="hidden" name="ParentCategory" value="' .
		(isset($_POST['ParentCategory'])?($_POST['ParentCategory']):('0')) . '" /> ';

	
	echo '<table class="selection">
		<tr>
			<th colspan="2">' . _('Add Inventory to this category') . '</th>
		</tr>
		<tr>
			<td>' . _('Select Item') . ':</td>
			<td><select name="AddStockID">';
		
	while( $myrow = DB_fetch_array($result) ) {
		if ( !array_keys( $StockIDs, $myrow['stockid']  ) ) {
			// Only if the StockID is not already selected
			echo '<option value="'.$myrow['stockid'].'">' . 
				$myrow['stockid'] . '&nbsp;-&nbsp;&quot;'.
				$myrow['description'] . '&quot;</option>';
		}
	}
	echo '</select></td>
			</tr>
			<tr>
			<td>' . _('Select Manufacturer/Brand') . ':</td>
			<td><select name="Brand">
			 <option value="">' . _('Select Brand') . '</option>';
	$BrandResult = DB_query("SELECT manufacturers_id, manufacturers_name FROM manufacturers",$db); 
	while( $myrow = DB_fetch_array($BrandResult) ) {
		echo '<option value="'.$myrow['manufacturers_id'].'">' .  $myrow['manufacturers_name'] . '</option>';
	}

	echo '</select></td>
			</tr></table>
		<br />
		<div class="centre">
			<input type="submit" name="submit" value="' . _('Add Item To Sales Category') . '" />
		</div>
		</div>
		</form>';
} else {
	echo '<p>';
	echo prnMsg( _('No more Inventory items to add') );
	echo '</p>';
}
if( $result ) {
	DB_free_result($result);
}
unset($StockIDs);
// END Always display Stock Select screen
// ----------------------------------------------------------------------------------------

// ----------------------------------------------------------------------------------------
// Always Show Stock In Category
echo '<br />';
if (isset($ParentCategory)){
	$ShowSalesCategory = "='" . $ParentCategory . "'";
} else {
	$ShowSalesCategory = ' IS NULL';
} 
$sql = "SELECT salescatprod.stockid,
				salescatprod.featured,
				stockmaster.description,
				manufacturers_name 
		FROM salescatprod 
		INNER JOIN stockmaster 
			ON salescatprod.stockid=stockmaster.stockid
		INNER JOIN manufacturers 
			ON salescatprod.manufacturers_id=manufacturers.manufacturers_id
		WHERE salescatprod.salescatid". $ShowSalesCategory . "
		ORDER BY salescatprod.stockid";

$result = DB_query($sql,$db);

if($result ) {
	if( DB_num_rows($result)) {
		echo '<table class="selection">';
		echo '<tr>
				<th colspan="4">' . _('Inventory items for') . ' ' . $CategoryPath . '</th>
			</tr>
			<tr>
				<th class="ascending">' . _('Item') . '</th>
				<th class="ascending">' . _('Description') . '</th>
				<th class="ascending">' . _('Brand') . '</th>
				<th class="ascending">' . _('Featured') . '</th>
			</tr>';

		$k=0; //row colour counter

		while( $myrow = DB_fetch_array($result) ) {
			if ($k==1){
				echo '<tr class="EvenTableRows">';
				$k=0;
			} else {
				echo '<tr class="OddTableRows">';
				$k=1;
			}

			echo '<td>' . $myrow['stockid'] . '</td>
				<td>' . $myrow['description'] . '</td>
				<td>' . $myrow['manufacturers_name'] . '</td>
				<td>';
			if ($myrow['featured']==1){
				echo '<img src="css/' . $Theme . '/images/tick.png"></td>
				<td><a href="'.htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') . '?RemoveFeature=Yes&amp;ParentCategory='.$ParentCategory.'&amp;StockID='.$myrow['stockid'].'">' .  _('Cancel Feature') . '</a></td>';
			} else {
				echo '</td>
				<td><a href="'.htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') . '?AddFeature=Yes&amp;ParentCategory='.$ParentCategory.'&amp;StockID='.$myrow['stockid'].'">' .  _('Make Featured') . '</a></td>';
			}
			echo '<td><a href="'.htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') . '?ParentCategory='.$ParentCategory.'&amp;DelStockID='.$myrow['stockid'].'">' .  _('Remove') . '</a></td>
			</tr>';
		}
		echo '</table>';
	} else {
		prnMsg(_('No Inventory items in this category'));
	}
	DB_free_result($result);
}

include('includes/footer.inc');
?>

<?php

/* $Revision: 1.6 $ */

$PageSecurity = 11;

include('includes/session.inc');

$title = _('Sales Category Maintenance');

include('includes/header.inc');

if (isset($_GET['SelectedCategory'])){
	$SelectedCategory = strtoupper($_GET['SelectedCategory']);
} else if (isset($_POST['SelectedCategory'])){
	$SelectedCategory = strtoupper($_POST['SelectedCategory']);
}

if (isset($_GET['ParentCategory'])){
	$ParentCategory = strtoupper($_GET['ParentCategory']);
} else if (isset($_POST['ParentCategory'])){
	$ParentCategory = strtoupper($_POST['ParentCategory']);
}
if( isset($ParentCategory) && $ParentCategory == 0 ) {
	unset($ParentCategory);
}

if (isset($_GET['EditName'])){
	$EditName = strtoupper($_GET['EditName']);
} else if (isset($_POST['EditName'])){
	$EditName = strtoupper($_POST['EditName']);
}

if ($SelectedCategory && isset($_FILES['ItemPicture']) AND $_FILES['ItemPicture']['name'] !='') {
	
	$result    = $_FILES['ItemPicture']['error'];
 	$UploadTheFile = 'Yes'; //Assume all is well to start off with
 	// Stock is always capatalized so there is no confusion since "cat_" is lowercase
	$filename = $_SESSION['part_pics_dir'] . '/cat_' . $SelectedCategory . '.jpg'; 
	
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
		$message = ($result)?_('File url') ."<a href='". $filename ."'>" .  $filename . '</a>' : "Somthing is wrong with uploading a file.";
	}
 /* EOR Add Image upload for New Item  - by Ori */
}



if (isset($_POST['submit'])  && $EditName == 1 ) { // Creating or updating a category

	//initialise no input errors assumed initially before we test
	$InputError = 0;

	/* actions to take once the user has clicked the submit button
	ie the page has called itself with some user input */

	//first off validate inputs sensible

	if (strlen($_POST['SalesCatName']) >20 OR trim($_POST['SalesCatName'])=='') {
		$InputError = 1;
		prnMsg(_('The Sales category description must be twenty characters or less long'),'error');
	}

	if ($SelectedCategory && $InputError !=1 ) {

		/*SelectedCategory could also exist if submit had not been clicked this code
		would not run in this case cos submit is false of course  see the
		delete code below*/

		$sql = "UPDATE salescat SET salescatname = '" . $_POST['SalesCatName'] . "'
                            WHERE salescatid = " .$SelectedCategory;
		$msg = _('The Sales category record has been updated');
	} elseif ($InputError !=1) {

	/*Selected category is null cos no item selected on first time round so must be adding a	record must be submitting new entries in the new stock category form */

		$sql = "INSERT INTO salescat (salescatname,
                                       parentcatid)
                                       VALUES (
                                       '" . DB_escape_string($_POST['SalesCatName']) . "',
                                       " . (isset($ParentCategory)?($ParentCategory):('NULL')) . ")";
		$msg = _('A new Sales category record has been added');
	}
	
	if ($InputError!=1){
		//run the SQL from either of the above possibilites
		$result = DB_query($sql,$db);
		prnMsg($msg,'success');
	}
	
	unset ($SelectedCategory);
	unset($_POST['SalesCatName']);
	unset($EditName);

} elseif (isset($_GET['delete']) && $EditName == 1) {
//the link to delete a selected record was clicked instead of the submit button

// PREVENT DELETES IF DEPENDENT RECORDS IN 'StockMaster'

	$sql= "SELECT COUNT(*) FROM salescatprod WHERE salescatid=".$SelectedCategory;
	$result = DB_query($sql,$db);
	$myrow = DB_fetch_row($result);
	if ($myrow[0]>0) {
		prnMsg(_('Cannot delete this sales category because stock items have been added to this category') .
			'<br> ' . _('There are') . ' ' . $myrow[0] . ' ' . _('items under to this category'),'warn');

	} else {
		$sql = "SELECT COUNT(*) FROM salescat WHERE parentcatid='$SelectedCategory'";
		$result = DB_query($sql,$db);
		$myrow = DB_fetch_row($result);
		if ($myrow[0]>0) {
		prnMsg(_('Cannot delete this sales category because sub categories have been added to this category') .
			'<br> ' . _('There are') . ' ' . $myrow[0] . ' ' . _('sub categories'),'warn');
		} else {
			$sql="DELETE FROM salescat WHERE salescatid=".$SelectedCategory;
			$result = DB_query($sql,$db);
			prnMsg(_('The sales category') . ' ' . $SelectedCategory . ' ' . _('has been deleted') . 
				' !','success');
			unset ($SelectedCategory);
		}
	} //end if stock category used in debtor transactions
	unset($_GET['delete']);
	unset($EditName);
} elseif( isset($_POST['submit'])  && isset($_POST['AddStockID']) ) {
	$sql = "INSERT INTO salescatprod ( 
				stockid, 
				salescatid 
			) VALUES (
				'".DB_escape_string($_POST['AddStockID'])."',
				".(isset($ParentCategory)?($ParentCategory):('NULL'))."
			)";
	$result = DB_query($sql,$db);
	prnMsg(_('Stock item') . ' ' . $_POST['AddStockID'] . ' ' . _('has been added') . 
		' !','success');
	unset($_POST['AddStockID']);
} elseif( isset($_GET['DelStockID']) ) {
	$sql = "DELETE FROM salescatprod WHERE 
				stockid='".DB_escape_string($_GET['DelStockID'])."' AND
				salescatid".(isset($ParentCategory)?('='.$ParentCategory):(' IS NULL'));
	$result = DB_query($sql,$db);
	prnMsg(_('Stock item') . ' ' . $_GET['DelStockID'] . ' ' . _('has been removed') . 
		' !','success');
	unset($_GET['DelStockID']);
}


// ----------------------------------------------------------------------------------------
// Calculate Path for navigation 

$CategoryPath = '<A href="'.$_SERVER['PHP_SELF'] . '?' . SID . 
			'&ParentCategory=0">' . htmlentities(_('Main'), ENT_QUOTES, _('ISO-8859-1')) . '</A>' . "&nbsp;\\&nbsp;";
$TempPath = '';
$TmpParentID = $ParentCategory;
$LastParentName = '';
for($Buzy = (isset($TmpParentID) && ($TmpParentID <> '')); 
		$Buzy == true;
		$Buzy = (isset($TmpParentID) && ($TmpParentID <> '')) ) {
  	$sql = "SELECT parentcatid, salescatname FROM salescat WHERE salescatid=".$TmpParentID;
	$result = DB_query($sql,$db);
	if( $result ) {
		if (DB_num_rows($result) > 0) {
			$row = DB_fetch_array($result);
			$LastParentName =  htmlentities($row['salescatname'], ENT_QUOTES, _('ISO-8859-1'));
			$TempPath = '<A href="'.$_SERVER['PHP_SELF'] . '?' . SID . 
				'&ParentCategory='.$TmpParentID.'">'.$LastParentName . 
				'</A>'."&nbsp;\\&nbsp;".$TempPath;
			$TmpParentID = $row['parentcatid']; // Set For Next Round
		} else {
			$Buzy = false;
		}
		DB_free_result($result);
	}
}

$CategoryPath = $CategoryPath.$TempPath;

echo '<p><center><i>'._("Selected Sales Category Path").'</i>&nbsp;:&nbsp;'. 
	$CategoryPath .
	'&nbsp;*&nbsp;</b></center></p>';

// END Calculate Path for navigation 
// ----------------------------------------------------------------------------------------


// ----------------------------------------------------------------------------------------
// We will always display Categories

/* It could still be the second time the page has been run and a record has been selected for modification - SelectedCategory will exist because it was sent with the new call. If its the first time the page has been displayed with no parameters
then none of the above are true and the list of stock categorys will be displayed with
links to delete or edit each. These will call the same page again and allow update/input
or deletion of the records*/

$sql = "SELECT salescatid, 
		salescatname 
	FROM salescat 
	WHERE parentcatid". (isset($ParentCategory)?('='.$ParentCategory):' is NULL') . " 
	ORDER BY salescatname";
$result = DB_query($sql,$db);


echo '<p><center>';
if (DB_num_rows($result) == 0) {
	prnMsg(_('There are no categories defined at this level.'));
} else {
	echo "<table border=1>\n";
	echo '<tr><td class="tableheader">' . _('Sub Category') . '</td></tr>';

	$k=0; //row colour counter

	while ($myrow = DB_fetch_array($result)) {
		if ($k==1){
			echo '<tr bgcolor="#CCCCCC">';
			$k=0;
		} else {
			echo '<tr bgcolor="#EEEEEE">';
			$k=1;
		}
		
		if (function_exists('imagecreatefrompng')){
			$CatImgLink = '<img src="GetStockImage.php?SID&automake=1&textcolor=FFFFFF&bgcolor=CCCCCC'.
				'&stockid='.urlencode('cat_'.$myrow['salescatid'].'.jpg').
				'&text='.
				'&width=32'.
				'&height=32'.
				'" >';
		} else {
			if( file_exists($_SESSION['part_pics_dir'] . '/' .'cat_'.$myrow['salescatid'].'.jpg') ) {
				$CatImgLink = '<img src="'.$rootpath . '/' . $_SESSION['part_pics_dir'] . '/' .
					'cat_'.$myrow['salescatid'].'.jpg" >';
			} else {
				$CatImgLink = 'No Image';
			}
				
		}
		printf("<td>%s</td>
            		<td><a href=\"%sParentCategory=%s\">" . _('Select') . "</td>
            		<td><a href=\"%sSelectedCategory=%s&ParentCategory=%s\">" . _('Edit') . "</td>
            		<td><a href=\"%sSelectedCategory=%s&delete=yes&EditName=1&ParentCategory=%s\">" . _('Delete') . "</td>
					<td>%s</td>
            		</tr>",
            		$myrow['salescatname'],
            		$_SERVER['PHP_SELF'] . '?' . SID,
            		$myrow['salescatid'],
            		$_SERVER['PHP_SELF'] . '?' . SID,
            		$myrow['salescatid'],
            		$ParentCategory,
            		$_SERVER['PHP_SELF'] . '?' . SID,
            		$myrow['salescatid'],
            		$ParentCategory,
            		$CatImgLink);
	}
	//END WHILE LIST LOOP
	echo '</table>';
}
echo '</center></p>';

// END display Categories
// ----------------------------------------------------------------------------------------
//end of ifs and buts!


// ----------------------------------------------------------------------------------------
// Show New or Edit Category

echo '<p><FORM ENCTYPE="MULTIPART/FORM-DATA" METHOD="POST" action="' . $_SERVER['PHP_SELF'] . '?' . SID . '">';

// This array will contain the stockids in use for this category
if (isset($SelectedCategory)) {
	//editing an existing stock category

	$sql = "SELECT salescatid, parentcatid, salescatname FROM salescat sc 
			WHERE salescatid=". $SelectedCategory;

	$result = DB_query($sql, $db);
	$myrow = DB_fetch_array($result);

	$_POST['SalesCatId'] = $myrow['salescatid'];
	$_POST['ParentCategory']  = $myrow['parentcatid'];
	$_POST['SalesCatName']  = $myrow['salescatname'];

	echo '<INPUT TYPE=HIDDEN NAME="SelectedCategory" VALUE="' . $SelectedCategory . '">';
	echo '<INPUT TYPE=HIDDEN NAME="ParentCategory" VALUE="' . 
		(isset($_POST['ParentCatId'])?($_POST['ParentCategory']):('0')) . '">';
	$FormCaps = _('Edit Sub Category');

} else { //end of if $SelectedCategory only do the else when a new record is being entered
	$_POST['SalesCatName']  = '';
	$_POST['ParentCategory']  = $ParentCategory;
	echo '<INPUT TYPE=HIDDEN NAME="ParentCategory" VALUE="' . 
		(isset($_POST['ParentCategory'])?($_POST['ParentCategory']):('0')) . '">';
	$FormCaps = _('New Sub Category');
}
echo '<INPUT TYPE=HIDDEN NAME="EditName" VALUE="1">';
echo '<CENTER><TABLE>';
echo '<tr><td class="tableheader" colspan="2">' . $FormCaps . '</td></tr>';
echo '<TR><TD>' . _('Category Name') . ':</TD>
            <TD><input type="Text" name="SalesCatName" SIZE=20 MAXLENGTH=20 value="' . 
			$_POST['SalesCatName'] . '"></TD></TR>';
// Image upload only if we have a selected category			
if (isset($SelectedCategory)) {
	echo '<TR><TD>'. _('Image File (.jpg)') . ':</TD>
		<TD><input type="file" id="ItemPicture" name="ItemPicture"></TD></TR>';
}		
	
echo '</TABLE>';
echo '<CENTER><input type="Submit" name="submit" value="' . _('Submit Information') . '">';

echo '</FORM></p>';

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
$stockids = array();
$sql = "SELECT stockid FROM salescatprod 
		WHERE salescatid". (isset($ParentCategory)?('='.$ParentCategory):' is NULL') . " 
		ORDER BY stockid";
$result = DB_query($sql,$db);
if($result && DB_num_rows($result)) {
	while( $myrow = DB_fetch_array($result) ) {
		$stockids[] = $myrow['stockid']; // Add Stock
	}
	DB_free_result($result);	
}

// This query will return the stock that is available
$sql = "SELECT stockid, description FROM stockmaster ORDER BY stockid";
$result = DB_query($sql,$db);
if($result && DB_num_rows($result)) {
	// continue id stock id in the stockid array
	echo '<p><FORM ENCTYPE="MULTIPART/FORM-DATA" METHOD="POST" action="' . $_SERVER['PHP_SELF'] . '?' . SID . '">';
	if( isset($SelectedCategory) ) { // If we selected a category we need to keep it selected
		echo '<INPUT TYPE=HIDDEN NAME="SelectedCategory" VALUE="' . $SelectedCategory . '">';
	}
	echo '<INPUT TYPE=HIDDEN NAME="ParentCategory" VALUE="' . 
		(isset($_POST['ParentCategory'])?($_POST['ParentCategory']):('0')) . '">';
	
	echo '<CENTER>';
	echo '<TABLE>';
	echo '<tr><td class="tableheader" colspan="2">'._('Add Inventory to this category.').'</td></tr>';
	echo '<TR><TD>' . _('Select Inv. Item') . ':</TD><TD>';
	echo '<select name="AddStockID">';
	while( $myrow = DB_fetch_array($result) ) {
		if ( !array_keys( $stockids, $myrow['stockid']  ) ) {
			// Only if the StockID is not already selected
			echo '<option value="'.$myrow['stockid'].'">'.
				htmlentities($myrow['stockid'], ENT_QUOTES, _('ISO-8859-1')) .
				'&nbsp;-&nbsp;&quot;'.
				htmlentities($myrow['description'], ENT_QUOTES, _('ISO-8859-1')) . '&quot;';
		}
	}
	echo '</select>';
	echo '</TD></TR></TABLE></CENTER>';
	echo '<CENTER><input type="Submit" name="submit" value="' . _('Add Inventory Item') . '">';
	echo '</center>';
	echo '</FORM></p>';
} else {
	echo "<p><center>";
	echo prnMsg( _("No more Inventory items to add.") );
	echo "</center></p>";
}
if( $result ) {
	DB_free_result($result);
}
unset($stockids);
// END Always display Stock Select screen
// ----------------------------------------------------------------------------------------

// ----------------------------------------------------------------------------------------
// Always Show Stock In Category
echo '<p><center>';
$sql = "SELECT scp.stockid, sm.description FROM salescatprod scp
			LEFT JOIN stockmaster sm ON sm.stockid = scp.stockid
			WHERE scp.salescatid". (isset($ParentCategory)?('='.$ParentCategory):' is NULL') . " 
		ORDER BY scp.stockid";

$result = DB_query($sql,$db);
if($result ) {
	if( DB_num_rows($result)) {
		echo '<TABLE>';
		echo '<tr><td class="tableheader" colspan="3">'._('Inventory items in this category.').'</td></tr>';
		echo '<TR><TD class="tableheader">' . _('Stock Code') . '</TD>';
		echo '<TD class="tableheader">' . _('Description') . '</TD></TR>';

		$k=0; //row colour counter

		while( $myrow = DB_fetch_array($result) ) {
			if ($k==1){
				echo '<tr bgcolor="#CCCCCC">';
				$k=0;
			} else {
				echo '<tr bgcolor="#EEEEEE">';
				$k=1;
			}
			
			echo '<TD>' . htmlentities($myrow['stockid'], ENT_QUOTES, _('ISO-8859-1')) . '</TD>';
			echo '<TD>' . htmlentities($myrow['description'], ENT_QUOTES, _('ISO-8859-1')) . '</TD>';
			echo '<TD><A href="'.$_SERVER['PHP_SELF'] . '?' . SID . 
					'&ParentCategory='.$ParentCategory.'&DelStockID='.$myrow['stockid'].'">'. 
					_('Remove').
					'</A></TD></TR>';
		}
		echo '</TABLE>';
	} else {
		prnMsg(_("No Inventory items in this category."));
	}
	DB_free_result($result);
}
echo '</center></p>';



// ----------------------------------------------------------------------------------------
// END Always Show Stock In Category

include('includes/footer.inc');
?>

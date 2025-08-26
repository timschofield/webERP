<?php

include('includes/session.php');

$Title = __('Sales Category Maintenance');
$ViewTopic = 'Setup';
$BookMark = '';
include('includes/header.php');

include('includes/ImageFunctions.php');

if (isset($_GET['SelectedCategory'])) {
	$SelectedCategory = mb_strtoupper($_GET['SelectedCategory']);
} else if (isset($_POST['SelectedCategory'])) {
	$SelectedCategory = mb_strtoupper($_POST['SelectedCategory']);
}

if (isset($SelectedCategory)) {
	echo '<a href="', $RootPath, '/SalesCategories.php" class="toplink">', __('Select a Different Category'), '</a>';
}

$SupportedImgExt = array('png', 'jpg', 'jpeg');

echo '<p class="page_title_text">
		<img src="', $RootPath, '/css/', $_SESSION['Theme'], '/images/inventory.png" title="', __('Search'), '" alt="" />', ' ', $Title, '
	</p>';

if (isset($_GET['AddFeature'])) {
	$SQL = "UPDATE salescatprod SET featured=1 WHERE salescatid='" . $SelectedCategory . "' AND stockid='" . $_GET['StockID'] . "'";
	$Result = DB_query($SQL);
	if (DB_error_no($Result) == 0) {
		prnMsg(__('The item has been successfully added to the featured list'), 'success');
	}
	$_GET['Select'] = 'Yes';
}

if (isset($_GET['RemoveFeature'])) {
	$SQL = "UPDATE salescatprod SET featured=0 WHERE salescatid='" . $SelectedCategory . "' AND stockid='" . $_GET['StockID'] . "'";
	$Result = DB_query($SQL);
	if (DB_error_no($Result) == 0) {
		prnMsg(__('The item has been successfully removed from the featured list'), 'success');
	}
	$_GET['Select'] = 'Yes';
}

if (isset($_GET['DelStockID'])) {
	$SQL = "DELETE FROM salescatprod WHERE salescatid='" . $SelectedCategory . "' AND stockid='" . $_GET['DelStockID'] . "'";
	$Result = DB_query($SQL);
	if (DB_error_no($Result) == 0) {
		prnMsg(__('The item has been successfully removed from this category'), 'success');
	}
	$_GET['Select'] = 'Yes';
}

if (isset($_POST['AddItems'])) {
	$Items = array();
	foreach ($_POST as $Key => $Value) {
		if (substr($Key, 0, 8) == 'StockID_') {
			if ($_POST['Brand_' . substr($Key, 8) ] == '') {
				prnMsg(__('Item') . ' ' . substr($Key, 8) . ' ' . __('does not have a brand selected and so cannot be added'), 'warn');
			} else {
				$Items[substr($Key, 8) ] = $_POST['Brand_' . substr($Key, 8) ];
			}
		}
	}
	foreach ($Items as $StockID => $Brand) {
		$SQL = "INSERT INTO salescatprod (stockid,
										salescatid,
										manufacturers_id)
									VALUES ('" . $StockID . "',
										'" . $SelectedCategory . "',
										'" . $Brand . "')";
		$Result = DB_query($SQL);
		prnMsg(__('Item') . ' ' . $StockID . ' ' . __('has been added'), 'success');
	}
	$_GET['Select'] = 'Yes';
}

if (isset($_POST['Search']) or isset($_POST['Prev']) or isset($_POST['Next'])) {

	if ($_POST['Keywords'] and $_POST['StockCode']) {
		prnMsg(__('Stock description keywords have been used in preference to the Stock code extract entered'), 'warn');
	}
	//insert wildcard characters in spaces
	$_POST['Keywords'] = mb_strtoupper($_POST['Keywords']);
	$SearchString = '%' . str_replace(' ', '%', $_POST['Keywords']) . '%';
	$SearchCode = '%' . $_POST['StockCode'] . '%';

	if ($_POST['StockCat'] == 'All') {
		$_POST['StockCat'] = '%';
	}
	$SQL = "SELECT  stockmaster.stockid,
					description,
					stockmaster.units
				FROM stockmaster
				INNER JOIN stockcategory
					ON stockmaster.categoryid=stockcategory.categoryid
				WHERE stockmaster.description " . LIKE . " '" . $SearchString . "'
					AND stockmaster.categoryid " . LIKE . " '" . $_POST['StockCat'] . "'
					AND stockmaster.stockid " . LIKE . " '" . $SearchCode . "'
					AND stockmaster.discontinued=0
				ORDER BY stockmaster.stockid";

	$ErrMsg = __('There was an error retrieving the stock item details');
	$SearchResult = DB_query($SQL, $ErrMsg);

	$MyRow = DB_fetch_array($SearchResult);
	DB_free_result($SearchResult);
	unset($SearchResult);
	$ListCount = $MyRow[0];
	if ($ListCount > 0) {
		$ListPageMax = ceil($ListCount / $_SESSION['DisplayRecordsMax']) - 1;
	} else {
		$ListPageMax = 1;
	}

	if (isset($_POST['Next'])) {
		$Offset = $_POST['CurrPage'] + 1;
	}
	if (isset($_POST['Prev'])) {
		$Offset = $_POST['CurrPage'] - 1;
	}
	if (!isset($Offset)) {
		$Offset = 0;
	}
	if ($Offset < 0) {
		$Offset = 0;
	}
	if ($Offset > $ListPageMax) {
		$Offset = $ListPageMax;
	}
	$SQL = $SQL . ' LIMIT ' . $_SESSION['DisplayRecordsMax'] . ' OFFSET ' . strval($_SESSION['DisplayRecordsMax'] * $Offset);

	$ErrMsg = __('There is a problem selecting the part records to display because');
	$SearchResult = DB_query($SQL, $ErrMsg);

	if (DB_num_rows($SearchResult) == 0) {
		prnMsg(__('There are no products available meeting the criteria specified'), 'info');
	}

} //end of if search
if (isset($SearchResult)) {

	echo '<form enctype="multipart/form-data" method="post" action="', htmlspecialchars(basename(__FILE__), ENT_QUOTES, 'UTF-8'), '">';
	echo '<input type="hidden" name="FormID" value="', $_SESSION['FormID'], '" />';

	if (DB_num_rows($SearchResult) > 0) {
		$SQL = "SELECT salescatname FROM salescat WHERE salescatid='" . $SelectedCategory . "'";
		$Result = DB_query($SQL);
		$NameRow = DB_fetch_array($Result);
		echo '<input type="hidden" name="SelectedCategory" value="', $SelectedCategory, '" />
				<table cellpadding="2">
				<thead>
					<tr>
						<th colspan="6">', __('Add items to sales category'), ' ', $NameRow['salescatname'], '(', $SelectedCategory, ')</th>
					</tr>
					<tr>
						<th class="SortedColumn">', __('Code'), '</th>
						<th class="SortedColumn">', __('Description'), '</th>
						<th>', __('Units'), '</th>
						<th colspan="2">', __('Add to Sales Category'), '</th>
						<th>', __('Manuafacturer'), '</th>
					</tr>
			</thead>';

		$SQL = "SELECT stockid FROM salescatprod WHERE salescatid='" . $SelectedCategory . "'";
		$CountResult = DB_query($SQL);
		$ItemCodes = array();
		while ($CountRow = DB_fetch_array($CountResult)) {
			$ItemCodes[] = $CountRow['stockid'];
		}
		echo '<tbody>';
		while ($MyRow = DB_fetch_array($SearchResult)) {

			if (!in_array($MyRow['stockid'], $ItemCodes)) {

				$SupportedImgExt = array('png', 'jpg', 'jpeg');
				$ImageFileArray = glob($_SESSION['part_pics_dir'] . '/' . $MyRow['stockid'] . '.{' . implode(",", $SupportedImgExt) . '}', GLOB_BRACE);
				$ImageFile = reset($ImageFileArray);
				$ImageSource = GetImageLink($ImageFile, $MyRow['stockid'], 64, 64, "", "");

				echo '<tr class="striped_row">
						<td>', $MyRow['stockid'], '</td>
						<td>', $MyRow['description'], '</td>
						<td>', $MyRow['units'], '</td>
						<td>', $ImageSource, '</td>
						<td><input type="checkbox" value="0" name="StockID_', $MyRow['stockid'], '" /></td>
						<td><select name="Brand_', $MyRow['stockid'], '">
							<option value="">', __('Select Brand'), '</option>';
				$BrandResult = DB_query("SELECT manufacturers_id, manufacturers_name FROM manufacturers");
				while ($MyRow = DB_fetch_array($BrandResult)) {
					echo '<option value="', $MyRow['manufacturers_id'], '">', $MyRow['manufacturers_name'], '</option>';
				}

				echo '</select>
					</td>
				</tr>';
			} //end if not already on work order

		} //end of while loop

	} //end if more than 1 row to show
	echo '</tbody>
		</table>';

	echo '<div class="centre">
			<input type="submit" name="AddItems", value="', __('Add items to category'), '" />
		</div>';

	include('includes/footer.php');
	exit();
} //end if SearchResults to show
if (isset($_POST['SubmitCategory'])) {
	//initialise no input errors assumed initially before we test
	$InputError = 0;

	/* actions to take once the user has clicked the submit button
	 ie the page has called itself with some user input */

	//first off validate inputs sensible
	if (mb_strlen($_POST['SalesCatName']) > 50 or trim($_POST['SalesCatName']) == '') {
		$InputError = 1;
		prnMsg(__('The Sales category description must be fifty characters or less long'), 'error');
	}

	if (isset($SelectedCategory) and $InputError != 1) {

		/*SelectedCategory could also exist if submit had not been clicked this code
		would not run in this case cos submit is false of course  see the
		delete code below*/

		$SQL = "UPDATE salescat SET salescatname = '" . $_POST['SalesCatName'] . "',
									parentcatid = '" . $_POST['ParentCategory'] . "',
									active  = '" . $_POST['Active'] . "'
							WHERE salescatid = '" . $SelectedCategory . "'";
		$Msg = __('The Sales category record has been updated');
	} elseif ($InputError != 1) {

		/*Selected category is null cos no item selected on first time round so must be adding a	record must be submitting new entries in the new stock category form */

		$SQL = "INSERT INTO salescat (salescatname,
									  parentcatid,
									  active)
									  VALUES (
									  '" . $_POST['SalesCatName'] . "',
									  '" . $_POST['ParentCategory'] . "',
									  '" . $_POST['Active'] . "')";
		$Msg = __('A new Sales category record has been added');
	}

	if ($InputError != 1) {
		//run the SQL from either of the above possibilites
		$Result = DB_query($SQL);
		prnMsg($Msg, 'success');
	}

	unset($SelectedCategory);
	unset($_POST['SalesCatName']);
	unset($_POST['Active']);
	unset($EditName);
}

if (!isset($_GET['Select'])) {
	$SQL = "SELECT salescatid,
					parentcatid,
					salescatname,
					active
				FROM salescat
				ORDER BY salescatname";
	$Result = DB_query($SQL);
	echo '<table>
			<thead>
				<tr>
					<th class="SortedColumn">', __('Category Name'), '</th>
					<th class="SortedColumn">', __('Parent Category'), '</th>
					<th>', __('Active?'), '</th>
					<th></th>
					<th></th>
					<th></th>
					<th></th>
				</tr>
			</thead>';

	echo '<tbody>';
	while ($MyRow = DB_fetch_array($Result)) {

		$ImageFileArray = glob($_SESSION['part_pics_dir'] . '/SALESCAT_' . $MyRow['salescatid'] . '.{' . implode(",", $SupportedImgExt) . '}', GLOB_BRACE);
		$ImageFile = reset($ImageFileArray);
		$CatImgLink = GetImageLink($ImageFile, 'SALESCAT_' . $MyRow['salescatid'], 64, 64, "", "");
		if ($MyRow['active'] == 1) {
			$Active = __('Yes');
		} else {
			$Active = __('No');
		}

		$SQL = "SELECT salescatname FROM salescat WHERE salescatid='" . $MyRow['parentcatid'] . "'";
		$ParentResult = DB_query($SQL);
		$ParentRow = DB_fetch_array($ParentResult);
		if (!isset($ParentRow['salescatname']) or $ParentRow['salescatname'] == '') {
			$ParentRow['salescatname'] = __('No parent');
		}

		echo '<tr class="striped_row">
					<td>', $MyRow['salescatname'], '</td>
					<td>', $ParentRow['salescatname'], '</td>
					<td>', $Active, '</td>
					<td><a href="', htmlspecialchars(basename(__FILE__), ENT_QUOTES, 'UTF-8'), '?SelectedCategory=', urlencode($MyRow['salescatid']), '&ParentCategory=', urlencode($MyRow['parentcatid']), '&Select=Yes">', __('Add Stock Items'), '</td>
					<td><a href="', htmlspecialchars(basename(__FILE__), ENT_QUOTES, 'UTF-8'), '?SelectedCategory=', urlencode($MyRow['salescatid']), '&ParentCategory=', urlencode($MyRow['parentcatid']), '&Edit=Yes">', __('Edit'), '</td>
					<td><a href="', htmlspecialchars(basename(__FILE__), ENT_QUOTES, 'UTF-8'), '?SelectedCategory=', urlencode($MyRow['salescatid']), '&ParentCategory=', urlencode($MyRow['parentcatid']), '&Delete=yes" onclick="return MakeConfirm(\'', __('Are you sure you wish to delete this sales category?'), '\', \'Confirm Delete\', this);">', __('Delete'), '</a></td>
					<td>', $CatImgLink, '</td>
				</tr>';
	}
	//END WHILE LIST LOOP
	echo '</tbody>';
	echo '</table>';

	echo '<form enctype="multipart/form-data" method="post" action="', htmlspecialchars(basename(__FILE__), ENT_QUOTES, 'UTF-8'), '">';
	echo '<input type="hidden" name="FormID" value="', $_SESSION['FormID'], '" />';

	if (isset($_GET['Edit'])) {
		//editing an existing stock category
		$SQL = "SELECT salescatid,
						parentcatid,
						salescatname,
						active
					FROM salescat
					WHERE salescatid='" . $SelectedCategory . "'";

		$Result = DB_query($SQL);
		$MyRow = DB_fetch_array($Result);

		$_POST['SalesCatId'] = $MyRow['salescatid'];
		$_POST['ParentCategory'] = $MyRow['parentcatid'];
		$_POST['SalesCatName'] = $MyRow['salescatname'];
		$_POST['Active'] = $MyRow['active'];

		echo '<input type="hidden" name="SelectedCategory" value="', $SelectedCategory, '" />';
		echo '<input type="hidden" name="ParentCategory" value="', $MyRow['parentcatid'], '" />';
		echo '<fieldset>
				<legend>', __('Edit Sales Category'), '</legend>';

	} else { //end of if $SelectedCategory only do the else when a new record is being entered
		$_POST['SalesCatName'] = '';
		if (isset($ParentCategory)) {
			$_POST['ParentCategory'] = $ParentCategory;
		} else {
			$_POST['ParentCategory'] = 0;
		}
		echo '<fieldset>
				<legend>', __('New Sales Category'), '</legend>';
	}
	echo '<field>
			<label for="SalesCatName">', __('Category Name'), ':</label>
			<input type="text" name="SalesCatName" size="20" required="required" autofocus="autofocus" maxlength="50" value="', $_POST['SalesCatName'], '" />
		</field>';

	$SQL = "SELECT salescatid, salescatname FROM salescat";
	$Result = DB_query($SQL);

	echo '<field>
			<label for="ParentCategory">', __('Parent Category'), '</label>
			<select name="ParentCategory">';
	if ($_POST['ParentCategory']==0){
		echo '<option value="0" selected="selected">', __('No parent'), '</option>';
	} else {
		echo '<option value="0">', __('No parent'), '</option>';
	}
	while ($MyRow = DB_fetch_array($Result)) {
		if ($MyRow['salescatid'] == $_POST['ParentCategory']) {
			echo '<option value="', $MyRow['salescatid'], '" selected="selected">', $MyRow['salescatname'], '</option>';
		} else {
			echo '<option value="', $MyRow['salescatid'], '">', $MyRow['salescatname'], '</option>';
		}
	}
	echo '</select>
	</field>';

	echo '<field>
			<label for="Active">', __('Is the category in active use?'), ':</label>
			<select name="Active">';
	if (isset($_POST['Active']) and $_POST['Active'] == '1') {
		echo '<option selected="selected" value="1">', __('Yes'), '</option>';
		echo '<option value="0">', __('No'), '</option>';
	} else {
		echo '<option selected="selected" value="0">', __('No'), '</option>';
		echo '<option value="1">', __('Yes'), '</option>';
	}
	echo '</select>
		</field>';

	echo '<field>
			<label for="CategoryPicture">', __('Image File (' . implode(", ", $SupportedImgExt) . ')'), ':</label>
			<input type="file" id="CategoryPicture" name="CategoryPicture" />
		</field>
		<field>
			<label for="ClearImage">', __('Clear Image'), '</label>
			<input type="checkbox" name="ClearImage" id="ClearImage" value="1" >
		</field>';

	echo '</fieldset>';

	echo '<div class="centre">
				<input type="submit" name="SubmitCategory" value="', __('Enter Information'), '" />
			</div>
		</form>';

} else {

	$SQL = "SELECT salescatname FROM salescat WHERE salescatid='" . $SelectedCategory . "'";
	$Result = DB_query($SQL);
	$NameRow = DB_fetch_array($Result);

	$SQL = "SELECT salescatprod.stockid,
					salescatprod.featured,
					stockmaster.description,
					manufacturers_name
				FROM salescatprod
				INNER JOIN stockmaster
					ON salescatprod.stockid=stockmaster.stockid
				INNER JOIN manufacturers
					ON salescatprod.manufacturers_id=manufacturers.manufacturers_id
				WHERE salescatprod.salescatid=" . $SelectedCategory . "
				ORDER BY salescatprod.stockid";

	$Result = DB_query($SQL);
	if ($Result) {
		if (DB_num_rows($Result)) {
			echo '<table>
					<thead>
						<tr>
							<th colspan="6">', __('Inventory items for'), ' ', $NameRow['salescatname'], ' (', $SelectedCategory, ')</th>
						</tr>
						<tr>
							<th class="SortedColumn">', __('Item'), '</th>
							<th class="SortedColumn">', __('Description'), '</th>
							<th class="SortedColumn">', __('Brand'), '</th>
							<th>', __('Featured'), '</th>
							<th></th>
							<th></th>
						</tr>
					</thead>';

			echo '<tbody>';
			while ($MyRow = DB_fetch_array($Result)) {

				echo '<tr class="striped_row">
						<td>', $MyRow['stockid'], '</td>
						<td>', $MyRow['description'], '</td>
						<td>', $MyRow['manufacturers_name'], '</td>
						<td>';
				if ($MyRow['featured'] == 1) {
					echo __('Yes'), '</td>
						<td><a href="', htmlspecialchars(basename(__FILE__), ENT_QUOTES, 'UTF-8'), '?RemoveFeature=Yes&amp;SelectedCategory=', urlencode($SelectedCategory), '&amp;StockID=', urlencode($MyRow['stockid']), '">', __('Cancel Feature'), '</a></td>';
				} else {
					echo __('No'), '</td>
						<td><a href="', htmlspecialchars(basename(__FILE__), ENT_QUOTES, 'UTF-8'), '?AddFeature=Yes&amp;SelectedCategory=', urlencode($SelectedCategory), '&amp;StockID=', urlencode($MyRow['stockid']), '">', __('Make Featured'), '</a></td>';
				}
				echo '<td><a href="', htmlspecialchars(basename(__FILE__), ENT_QUOTES, 'UTF-8'), '?SelectedCategory=', urlencode($SelectedCategory), '&amp;DelStockID=', urlencode($MyRow['stockid']), '">', __('Remove'), '</a></td>
				</tr>';
			}
			echo '</tbody>';
			echo '</table>';
		} else {
			prnMsg(__('No Inventory items in this category'));
		}
	}
	$SQL = "SELECT categoryid,
					categorydescription
				FROM stockcategory
				WHERE stocktype='F' OR stocktype='M'
				ORDER BY categorydescription";
	$Result1 = DB_query($SQL);

	echo '<form enctype="multipart/form-data" method="post" action="', htmlspecialchars(basename(__FILE__), ENT_QUOTES, 'UTF-8'), '">';
	echo '<input type="hidden" name="FormID" value="', $_SESSION['FormID'], '" />';
	echo '<input type="hidden" name="SelectedCategory" value="', $SelectedCategory, '" />';

	echo '<table>
			<th colspan="5">', __('Select Stock Items'), '</th>';

	echo '<tr>
			<td>', __('Select a stock category'), ':</td>
			<td><select name="StockCat">';

	if (!isset($_POST['StockCat'])) {
		echo '<option selected="True" value="All">', __('All'), '</option>';
		$_POST['StockCat'] = 'All';
	} else {
		echo '<option value="All">', __('All'), '</option>';
	}

	while ($MyRow1 = DB_fetch_array($Result1)) {

		if ($_POST['StockCat'] == $MyRow1['categoryid']) {
			echo '<option selected="True" value=', $MyRow1['categoryid'], '>', $MyRow1['categorydescription'], '</option>';
		} else {
			echo '<option value=', $MyRow1['categoryid'], '>', $MyRow1['categorydescription'], '</option>';
		}
	}

	if (!isset($_POST['Keywords'])) {
		$_POST['Keywords'] = '';
	}

	if (!isset($_POST['StockCode'])) {
		$_POST['StockCode'] = '';
	}

	echo '</select>
		</td>
	</tr>';

	echo '<tr>
			<td>', __('Enter text extracts in the'), ' <b>', __('description'), '</b>:</td>
			<td><input type="text" name="Keywords" size="20" maxlength="25" value="', $_POST['Keywords'], '" /></td>
		</tr>';

	echo '<div style="padding-bottom:8px;"><font size="3"><b>', __('OR'), ' </b></font></div>';

	echo '<tr>
			<td>', __('Enter extract of the'), ' <b>', __('Stock Code'), '</b>:</td>
			<td><input type="text" name="StockCode" autofocus="autofocus" size="15" maxlength="18" value="', $_POST['StockCode'], '" /></td>
		</tr>
		</table>
		<div class="centre">
			<input type="submit" name="Search" value="', __('Search Now'), '" />
		</div>
	</form>';
}

include('includes/footer.php');

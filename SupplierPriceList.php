<?php

/* Maintain Supplier Price Lists */

require(__DIR__ . '/includes/session.php');

$Title = __('Supplier Purchasing Data');
$ViewTopic = 'PurchaseOrdering';
$BookMark = 'SupplierPriceList';
include('includes/header.php');

if(isset($_POST['StockSearch'])) {
	echo '<p class="page_title_text"><img alt="" src="', $RootPath, '/css/', $Theme,
		'/images/magnifier.png" title="', // Icon image.
		__('Search'), '" /> ', // Icon title.
		__('Search for Inventory Items'), '</p>';// Page title.

	echo '<form action="', htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8'), '" method="post">
		<input name="FormID" type="hidden" value="', $_SESSION['FormID'], '" />
		<input name="SupplierID" type="hidden" value="', $_POST['SupplierID'], '" />
		<fieldset>
			<legend class="search">', __('Search for Item'), '</legend>
			<field>
				<label for="StockCat">' . __('In Stock Category') . ':</label>
				<select name="StockCat">';
	if(!isset($_POST['StockCat'])) {
		$_POST['StockCat'] = '';
	}
	if($_POST['StockCat'] == 'All') {
		echo '<option selected="selected" value="All">' . __('All') . '</option>';
	} else {
		echo '<option value="All">' . __('All') . '</option>';
	}
	$SQL = "SELECT categoryid,
				categorydescription
			FROM stockcategory
			ORDER BY categorydescription";
	$Result1 = DB_query($SQL);
	while($MyRow1 = DB_fetch_array($Result1)) {
		if($MyRow1['categoryid'] == $_POST['StockCat']) {
			echo '<option selected="selected" value="' . $MyRow1['categoryid'] . '">' . $MyRow1['categorydescription'] . '</option>';
		} else {
			echo '<option value="' . $MyRow1['categoryid'] . '">' . $MyRow1['categorydescription'] . '</option>';
		}
	}
	echo '</select>
		</field>';
	echo '<field>
			<label for="Keywords">' . __('Enter partial') . '<b> ' . __('Description') . '</b>:</label>';
	if(isset($_POST['Keywords'])) {
		echo '<input type="search" name="Keywords" value="' . $_POST['Keywords'] . '" autofocus="autofocus" size="34" maxlength="25" />';
	} else {
		echo '<input type="search" name="Keywords" size="34" maxlength="25" autofocus="autofocus" placeholder="Enter part of the item description" />';
	}
	echo '</field>';

	echo '<field>
			<label for="StockCode">'.'<b>' . __('OR') . ' </b>' .  __('Enter partial') . ' <b>' . __('Stock Code') . '</b>:</label>';
	if(isset($_POST['StockCode'])) {
		echo '<input type="text" name="StockCode" value="' . $_POST['StockCode'] . '" size="15" maxlength="18" />';
	} else {
		echo '<input type="text" name="StockCode" size="15" maxlength="18" />';
	}
	echo '</field>
		</fieldset>
		<div class="centre">
			<input type="submit" name="Search" value="' . __('Search Now') . '" />
		</div>
	</form>';
	include('includes/footer.php');
	exit();
}

if(isset($_POST['Search']) OR isset($_POST['Go']) OR isset($_POST['Next']) OR isset($_POST['Previous'])) {
	if(!isset($_POST['Go']) AND !isset($_POST['Next']) AND !isset($_POST['Previous'])) {
		// if Search then set to first page
		$_POST['PageOffset'] = 1;
	}
	if($_POST['Keywords'] AND $_POST['StockCode']) {
		prnMsg(__('Stock description keywords have been used in preference to the Stock code extract entered'), 'info');
	}
	if($_POST['Keywords']) {
		//insert wildcard characters in spaces
		$_POST['Keywords'] = mb_strtoupper($_POST['Keywords']);
		$SearchString = '%' . str_replace(' ', '%', $_POST['Keywords']) . '%';
		if($_POST['StockCat'] == 'All') {
			$SQL = "SELECT stockmaster.stockid,
							stockmaster.description,
							SUM(locstock.quantity) AS qoh,
							stockmaster.units,
							stockmaster.mbflag,
							stockmaster.discontinued,
							stockmaster.decimalplaces
						FROM stockmaster
						LEFT JOIN stockcategory
						ON stockmaster.categoryid=stockcategory.categoryid,
							locstock
						WHERE stockmaster.stockid=locstock.stockid
						AND stockmaster.description " . LIKE . " '$SearchString'
						AND (stockmaster.mbflag='B' OR stockmaster.mbflag='M')
						GROUP BY stockmaster.stockid,
							stockmaster.description,
							stockmaster.units,
							stockmaster.mbflag,
							stockmaster.discontinued,
							stockmaster.decimalplaces
						ORDER BY stockmaster.stockid";
		} else {
			$SQL = "SELECT stockmaster.stockid,
							stockmaster.description,
							SUM(locstock.quantity) AS qoh,
							stockmaster.units,
							stockmaster.mbflag,
							stockmaster.discontinued,
							stockmaster.decimalplaces
						FROM stockmaster INNER JOIN locstock
						ON stockmaster.stockid=locstock.stockid
						WHERE description " . LIKE . " '$SearchString'
						AND (stockmaster.mbflag='B' OR stockmaster.mbflag='M')
						AND categoryid='" . $_POST['StockCat'] . "'
						GROUP BY stockmaster.stockid,
							stockmaster.description,
							stockmaster.units,
							stockmaster.mbflag,
							stockmaster.discontinued,
							stockmaster.decimalplaces
						ORDER BY stockmaster.stockid";
		}
	} elseif(isset($_POST['StockCode'])) {
		$_POST['StockCode'] = mb_strtoupper($_POST['StockCode']);
		if($_POST['StockCat'] == 'All') {
			$SQL = "SELECT stockmaster.stockid,
							stockmaster.description,
							stockmaster.mbflag,
							stockmaster.discontinued,
							SUM(locstock.quantity) AS qoh,
							stockmaster.units,
							stockmaster.decimalplaces
						FROM stockmaster
						INNER JOIN stockcategory
						ON stockmaster.categoryid=stockcategory.categoryid
						INNER JOIN locstock
						ON stockmaster.stockid=locstock.stockid
						WHERE (stockmaster.mbflag='B' OR stockmaster.mbflag='M')
						AND stockmaster.stockid " . LIKE . " '%" . $_POST['StockCode'] . "%'
						GROUP BY stockmaster.stockid,
								stockmaster.description,
								stockmaster.units,
								stockmaster.mbflag,
								stockmaster.discontinued,
								stockmaster.decimalplaces
						ORDER BY stockmaster.stockid";
		} else {
			$SQL = "SELECT stockmaster.stockid,
					stockmaster.description,
					stockmaster.mbflag,
					stockmaster.discontinued,
					sum(locstock.quantity) as qoh,
					stockmaster.units,
					stockmaster.decimalplaces
				FROM stockmaster INNER JOIN locstock
				ONstockmaster.stockid=locstock.stockid
				WHERE stockmaster.stockid " . LIKE . " '%" . $_POST['StockCode'] . "%'
				AND (stockmaster.mbflag='B' OR stockmaster.mbflag='M')
				AND categoryid='" . $_POST['StockCat'] . "'
				GROUP BY stockmaster.stockid,
					stockmaster.description,
					stockmaster.units,
					stockmaster.mbflag,
					stockmaster.discontinued,
					stockmaster.decimalplaces
				ORDER BY stockmaster.stockid";
		}
	} elseif(!isset($_POST['StockCode']) AND !isset($_POST['Keywords'])) {
		if($_POST['StockCat'] == 'All') {
			$SQL = "SELECT stockmaster.stockid,
					stockmaster.description,
					stockmaster.mbflag,
					stockmaster.discontinued,
					SUM(locstock.quantity) AS qoh,
					stockmaster.units,
					stockmaster.decimalplaces
				FROM stockmaster
				LEFT JOIN stockcategory
				ON stockmaster.categoryid=stockcategory.categoryid,
					locstock
				WHERE stockmaster.stockid=locstock.stockid
				AND (stockmaster.mbflag='B' OR stockmaster.mbflag='M')
				GROUP BY stockmaster.stockid,
					stockmaster.description,
					stockmaster.units,
					stockmaster.mbflag,
					stockmaster.discontinued,
					stockmaster.decimalplaces
				ORDER BY stockmaster.stockid";
		} else {
			$SQL = "SELECT stockmaster.stockid,
					stockmaster.description,
					stockmaster.mbflag,
					stockmaster.discontinued,
					SUM(locstock.quantity) AS qoh,
					stockmaster.units,
					stockmaster.decimalplaces
				FROM stockmaster INNER JOIN locstock
				ONstockmaster.stockid=locstock.stockid
				WHERE categoryid='" . $_POST['StockCat'] . "'
				AND (stockmaster.mbflag='B' OR stockmaster.mbflag='M')
				GROUP BY stockmaster.stockid,
					stockmaster.description,
					stockmaster.units,
					stockmaster.mbflag,
					stockmaster.discontinued,
					stockmaster.decimalplaces
				ORDER BY stockmaster.stockid";
		}
	}
	$ErrMsg = __('No stock items were returned by the SQL because');
	$SearchResult = DB_query($SQL, $ErrMsg);
	if(DB_num_rows($SearchResult) == 0) {
		prnMsg(__('No stock items were returned by this search please re-enter alternative criteria to try again'), 'info');
	}
	unset($_POST['Search']);
}
/* end query for list of records */
/* display list if there is more than one record */
if(isset($SearchResult) AND !isset($_POST['Select'])) {
	echo '<p class="page_title_text"><img src="' . $RootPath . '/css/' . $Theme . '/images/magnifier.png" title="' . __('Search') . '" alt="" />' . ' ' . __('Search for Inventory Items'). '</p>';
	echo '<form action="' . htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') . '" method="post">
		<div>
		<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />
		<input type="hidden" value="' . $_POST['SupplierID'] . '" name="SupplierID" />';
	$ListCount = DB_num_rows($SearchResult);
	if($ListCount > 0) {
		// If the user hit the search button and there is more than one item to show
		$ListPageMax = ceil($ListCount / $_SESSION['DisplayRecordsMax']);
		if(isset($_POST['Next'])) {
			if($_POST['PageOffset'] < $ListPageMax) {
				$_POST['PageOffset'] = $_POST['PageOffset'] + 1;
			}
		}
		if(isset($_POST['Previous'])) {
			if($_POST['PageOffset'] > 1) {
				$_POST['PageOffset'] = $_POST['PageOffset'] - 1;
			}
		}
		if($_POST['PageOffset'] > $ListPageMax) {
			$_POST['PageOffset'] = $ListPageMax;
		}
		if($ListPageMax > 1) {
			echo '<div class="centre"><br />&nbsp;&nbsp;' . $_POST['PageOffset'] . ' ' . __('of') . ' ' . $ListPageMax . ' ' . __('pages') . '. ' . __('Go to Page') . ': ';
			echo '<select name="PageOffset">';
			$ListPage = 1;
			while($ListPage <= $ListPageMax) {
				if($ListPage == $_POST['PageOffset']) {
					echo '<option value=' . $ListPage . ' selected>' . $ListPage . '</option>';
				} else {
					echo '<option value=' . $ListPage . '>' . $ListPage . '</option>';
				}
				$ListPage++;
			}
			echo '</select>
				<input type="submit" name="Go" value="' . __('Go') . '" />
				<input type="submit" name="Previous" value="' . __('Previous') . '" />
				<input type="submit" name="Next" value="' . __('Next') . '" />';
			echo '<input type="hidden" name=Keywords value="'.$_POST['Keywords'].'" />';
			echo '<input type="hidden" name=StockCat value="'.$_POST['StockCat'].'" />';
			echo '<input type="hidden" name=StockCode value="'.$_POST['StockCode'].'" />';
//			echo '<input type="hidden" name=Search value="Search" />';
			echo '<br /></div>';
		}
		echo '<table class="selection">
			<thead>
				<tr>
				<th class="SortedColumn">' . __('Code') . '</th>
				<th class="SortedColumn">' . __('Description') . '</th>
				<th>' . __('Units') . '</th>
				</tr>
			</thead>
			<tbody>';

		$RowIndex = 0;
		if(DB_num_rows($SearchResult) <> 0) {
			DB_data_seek($SearchResult, ($_POST['PageOffset'] - 1) * $_SESSION['DisplayRecordsMax']);
		}
		while(($MyRow = DB_fetch_array($SearchResult)) AND ($RowIndex <> $_SESSION['DisplayRecordsMax'])) {

			echo '<tr class="striped_row">
				<td><input type="submit" name="Select" value="' . $MyRow['stockid'] . '" /></td>
				<td>' . $MyRow['description'] . '</td>
				<td>' . $MyRow['units'] . '</td>
				</tr>';

			$RowIndex = $RowIndex + 1;
		}

		//end of while loop
		echo '</tbody></table>
              <br />
              </div>
              </form>';
		include('includes/footer.php');
		exit();
	}
}

foreach ($_POST as $key=>$Value) {
	if(mb_substr($key,0,6)=='Update') {
		$Index = mb_substr($key,6,mb_strlen($key)-6);
		$StockID = $_POST['StockID'.$Index];
		$Price = filter_number_format($_POST['Price'.$Index]);// Convert data from user format to database number format.
		$SuppUOM = $_POST['SuppUOM'.$Index];
		$ConversionFactor = $_POST['ConversionFactor'.$Index];
		$SupplierDescription = $_POST['SupplierDescription'.$Index];
		$LeadTime = $_POST['LeadTime'.$Index];
		if(isset($_POST['Preferred'.$Index])) {
			$Preferred = 1;
			$PreferredSQL = "UPDATE purchdata SET preferred=0
									WHERE stockid='" . $StockID . "'";
			$PreferredResult = DB_query($PreferredSQL);
		} else {
			$Preferred = 0;
		}
		$EffectiveFrom=$_POST['EffectiveFrom'.$Index];
		$SupplierPartNo=$_POST['SupplierPartNo'.$Index];
		$MinOrderQty=$_POST['MinOrderQty'.$Index];
		$SQL="UPDATE purchdata SET price='" . $Price . "',
									suppliersuom='" . $SuppUOM . "',
									conversionfactor='" . $ConversionFactor . "',
									supplierdescription='" . $SupplierDescription . "',
									leadtime='" . $LeadTime . "',
									preferred='" . $Preferred . "',
									effectivefrom='" . $EffectiveFrom . "',
									suppliers_partno='" . $SupplierPartNo . "',
									minorderqty='" . $MinOrderQty . "'
								WHERE supplierno='" . $_POST['SupplierID'] . "'
								AND stockid='" . $StockID . "'";
		$Result = DB_query($SQL);
	}
	if(mb_substr($key,0,6)=='Insert') {
		if(isset($_POST['Preferred0'])) {
			$Preferred=1;
		} else {
			$Preferred=0;
		}
		$SQL="INSERT INTO purchdata (stockid,
									supplierno,
									price,
									suppliersuom,
									conversionfactor,
									supplierdescription,
									leadtime,
									preferred,
									effectivefrom,
									suppliers_partno,
									minorderqty
								) VALUES (
									'" . $_POST['StockID0'] . "',
									'" . $_POST['SupplierID'] . "',
									'" . $_POST['Price0'] . "',
									'" . $_POST['SuppUOM0'] . "',
									'" . $_POST['ConversionFactor0'] . "',
									'" . $_POST['SupplierDescription0'] . "',
									'" . $_POST['LeadTime0'] . "',
									'" . $Preferred . "',
									'" . $_POST['EffectiveFrom0'] . "',
									'" . $_POST['SupplierPartNo0'] . "',
									'" . $_POST['MinOrderQty0'] . "'
								)";
		$Result = DB_query($SQL);
	}
}

if(isset($_GET['SupplierID'])) {
	$SupplierID = trim(mb_strtoupper($_GET['SupplierID']));
} elseif(isset($_POST['SupplierID'])) {
	$SupplierID = trim(mb_strtoupper($_POST['SupplierID']));
}

if(isset($SupplierID) AND $SupplierID != '' AND !isset($_POST['SearchSupplier'])) { /*NOT EDITING AN EXISTING BUT SUPPLIER selected OR ENTERED*/
	$SQL = "SELECT suppliers.suppname, suppliers.currcode FROM suppliers WHERE supplierid='".$SupplierID."'";
	$ErrMsg = __('The supplier details for the selected supplier could not be retrieved because');
	$SuppSelResult = DB_query($SQL, $ErrMsg);
	if(DB_num_rows($SuppSelResult) == 1) {
		$MyRow = DB_fetch_array($SuppSelResult);
		$SuppName = $MyRow['suppname'];
		$CurrCode = $MyRow['currcode'];
	} else {
		prnMsg(__('The supplier code') . ' ' . $SupplierID . ' ' . __('is not an existing supplier in the database') . '. ' . __('You must enter an alternative supplier code or select a supplier using the search facility below'), 'error');
		unset($SupplierID);
	}
} else {
	if($NoPurchasingData=0) {
		echo '<p class="page_title_text"><img src="' . $RootPath . '/css/' . $Theme . '/images/maintenance.png" title="' . __('Search') . '" alt="" />' . ' ' .
			$Title . ' ' . __('For Stock Code') . ' - ' . $StockID . '</p><br />';
	}
	if(!isset($_POST['SearchSupplier'])) {
		echo '<p class="page_title_text"><img alt="" src="', $RootPath, '/css/', $Theme,
			'/images/supplier.png" title="', // Icon image.
			__('Search for a supplier'), '" /> ', // Icon title.
			__('Search for a supplier'), '</p>';// Page title.

		echo '<form action="', htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8'), '" method="post">';
		echo '<input name="FormID" type="hidden" value="', $_SESSION['FormID'], '" />';

		echo '<fieldset>
				<legend class="search">', __('Select Supplier'), '</legend>';
		echo '<field>
				<label for="Keywords">' . __('Text in the Supplier') . ' <b>' . __('NAME') . '</label>
				<input maxlength="25" name="Keywords" size="20" type="text" />
			</field>';
		echo '<field>
				<label for="SupplierCode">' . '<b>' . __('OR') . ' </b>' . __('Text in Supplier') . ' <b>' . __('CODE') . '</label>
				<input maxlength="18" name="SupplierCode" size="15" type="text" />
			</field>
		</fieldset>';
		echo '<div class="centre">
				<input name="SearchSupplier" type="submit" value="' . __('Find Suppliers Now') . '" />
			</div>';
        echo '</form>';
		include('includes/footer.php');
		exit();
	}
}

if(isset($_POST['SearchSupplier'])) {
	if($_POST['Keywords'] == '' AND $_POST['SupplierCode'] == '') {
		$_POST['Keywords'] = ' ';
	}
	if(mb_strlen($_POST['Keywords']) > 0) {
		//insert wildcard characters in spaces
		$SearchString = '%' . str_replace(' ', '%', $_POST['Keywords']) . '%';

		$SQL = "SELECT suppliers.supplierid,
					suppliers.suppname,
					suppliers.currcode,
					suppliers.address1,
					suppliers.address2,
					suppliers.address3
					FROM suppliers WHERE suppliers.suppname " . LIKE  . " '".$SearchString."'";
	} elseif(mb_strlen($_POST['SupplierCode']) > 0) {
		$SQL = "SELECT suppliers.supplierid,
				suppliers.suppname,
				suppliers.currcode,
				suppliers.address1,
				suppliers.address2,
				suppliers.address3
			FROM suppliers
			WHERE suppliers.supplierid " . LIKE . " '%" . $_POST['SupplierCode'] . "%'";
	} //one of keywords or SupplierCode was more than a zero length string
	$ErrMsg = __('The suppliers matching the criteria entered could not be retrieved because');
	$SuppliersResult = DB_query($SQL, $ErrMsg);
} //end of if search

if(isset($SuppliersResult)) {
	echo '<p class="page_title_text"><img alt="" src="', $RootPath, '/css/', $Theme,
		'/images/supplier.png" title="', // Icon image.
		__('Search'), '" /> ', // Icon title.
		__('Select a supplier'), '</p>';// Page title.
	echo '<br />
		<form action="', htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8'), '" method="post">
		<input name="FormID" type="hidden" value="' . $_SESSION['FormID'] . '" />';

	echo '<table cellpadding="2" class="selection">
			<tr>
				<th>' . __('Code') . '</th>
				<th>' . __('Supplier Name') . '</th>
				<th>' . __('Currency') . '</th>
				<th>' . __('Address 1') . '</th>
				<th>' . __('Address 2') . '</th>
				<th>' . __('Address 3') . '</th>
			</tr>';

	while($MyRow = DB_fetch_array($SuppliersResult)) {
		echo '<tr class="striped_row">
				<td><input name="SupplierID" type="submit" value="', $MyRow['supplierid'], '" /></td>
				<td>', $MyRow['suppname'], '</td>
				<td>', $MyRow['currcode'], '</td>
				<td>', $MyRow['address1'], '</td>
				<td>', $MyRow['address2'], '</td>
				<td>', $MyRow['address3'], '</td>
			</tr>';
	}// END while($MyRow = DB_fetch_array($SuppliersResult)).
	echo '</table>
          </form>
          <br />';
	include('includes/footer.php');
	exit();
}// END if(isset($SuppliersResult)).

if(isset($_POST['SupplierID'])) {
	$MyRow = DB_fetch_array(DB_query("SELECT suppname FROM `suppliers` WHERE `supplierid`='" . $_POST['SupplierID'] . "'"));// Retrieve supplier's name from database.
	$SuppName = $MyRow['suppname'];
	echo '<p class="page_title_text"><img alt="" src="', $RootPath, '/css/', $Theme,
		'/images/supplier.png" title="', // Icon image.
		__('Search'), '" /> ', // Icon title.
		__('Supplier Purchasing Data'), '<br />',
			$_POST['SupplierID'], ' - ', $SuppName, '</p>';// Page title.

	$SQL="SELECT purchdata.stockid,
				stockmaster.description,
				price,
				suppliersuom,
				conversionfactor,
				supplierdescription,
				leadtime,
				preferred,
				effectivefrom,
				suppliers_partno,
				minorderqty
			FROM purchdata
			INNER JOIN stockmaster
			ON purchdata.stockid=stockmaster.stockid
			WHERE supplierno='".$_POST['SupplierID']."'
			ORDER BY purchdata.stockid, effectivefrom DESC";
	$Result = DB_query($SQL);

	$UOMSQL = "SELECT unitid,
						unitname
					FROM unitsofmeasure";
	$UOMResult = DB_query($UOMSQL);

	echo '<br />
		<form action="', htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8'), '" method="post">
		<input name="FormID" type="hidden" value="', $_SESSION['FormID'], '" />
		<input name="SupplierID" type="hidden" value="', $_POST['SupplierID'], '" />';

	echo '<table class="selection">
			<tr>
				<th colspan="12" class="number">' . __('Find new Item Code') .
					'<button type="submit" name="StockSearch"><img width="15" src="' . $RootPath . '/css/' . $Theme . '/images/magnifier.png" alt="" /></button></th>
			</tr>';
	echo '<tr>
			<th>' . __('StockID') . '</th>
			<th>' . __('Description') . '</th>
			<th>' . __('Price') . '</th>
			<th>' . __('Suppliers UOM') . '</th>
			<th>' . __('Conversion Factor') . '</th>
			<th>' . __('Suppliers Description') . '</th>
			<th>' . __('Lead Time') . '</th>
			<th>' . __('Preferred') . '</th>
			<th>' . __('Effective From') . '</th>
			<th>' . __('Suppliers Item Code') . '</th>
			<th>' . __('Min Order Qty') . '</th>
			<th>', __('Save'), '</th>
		</tr>';// RChacon: Sortable by StockID, Description, Suppliers_Description, Suppliers_Description ?

	if(isset($_POST['Select'])) {
		$StockSQL = "SELECT description, units FROM stockmaster WHERE stockid='" . $_POST['Select'] . "'";
		$StockResult = DB_query($StockSQL);
		$StockRow = DB_fetch_array($StockResult);
		// RChacon: if exist, retrieve data, not a blank line?
		echo '<tr bgcolor="#847F7F">
				<td><input type="hidden" value="' . $_POST['Select'] . '" name="StockID0" />' . $_POST['Select'] . '</td>
				<td>' . $StockRow['description'] . '</td>
				<td><input type="text" class="number" size="11" value="0.0000" name="Price0" /></td>
				<td><select name="SuppUOM0">';
		while($UOMRow=DB_fetch_array($UOMResult)) {
			if($UOMRow['unitname']==$StRowoc['units']) {
				echo '<option selected="selected" value="'.$UOMRow['unitname'].'">' . $UOMRow['unitname'] . '</option>';
			} else {
				echo '<option value="'.$UOMRow['unitname'].'">' . $UOMRow['unitname'] . '</option>';
			}
		}
		DB_data_seek($UOMResult, 0);
		echo '</select></td>
				<td><input class="number" name="ConversionFactor0" size="11" type="text" value="1" /></td>
				<td><input maxlength="50" name="SupplierDescription0" size="30" type="text" value="" /></td>
				<td><input class="number" name="LeadTime0" size="11" type="text" value="1" /></td>
				<td><input name="Preferred0" type="checkbox" /></td>
				<td><input type="date" name="EffectiveFrom0" size="11" value="', date( $_SESSION['DefaultDateFormat']), '" /></td>
				<td><input maxlength="50" name="SupplierPartNo0" size="20" type="text" value="" /></td>
				<td><input class="number" name="MinOrderQty0" size="11" type="text" value="1" /></td>
				<td><button name="Insert" type="submit" style="width:100%;text-align:left"><img alt="" src="' . $RootPath . '/css/' . $Theme . '/images/tick.svg" width="15" /></button></td>
			</tr>';
	}

	$RowCounter = 1;
	while($MyRow = DB_fetch_array($Result)) {
		echo '<tr>
				<td class="text"><input name="StockID'. $RowCounter. '" type="hidden" value="' . $MyRow['stockid'] . '" />' . $MyRow['stockid'] . '</td>
				<td class="text">' . $MyRow['description'], '</td>
				<td><input class="number" size="11" type="text" value="', locale_number_format($MyRow['price'], 4), // Show price in locale user format. RChacon: Decimals from parameters ?
					'" name="Price'.$RowCounter.'" /></td>
				<td><select name="SuppUOM'.$RowCounter.'">';
		DB_data_seek($UOMResult, 0);
		while($UOMRow=DB_fetch_array($UOMResult)) {
			if($UOMRow['unitname']==$MyRow['suppliersuom']) {
				echo '<option selected="selected" value="'.$UOMRow['unitname'].'">' . $UOMRow['unitname'] . '</option>';
			} else {
				echo '<option value="'.$UOMRow['unitname'].'">' . $UOMRow['unitname'] . '</option>';
			}
		}
		echo '</select></td>
				<td><input class="number" name="ConversionFactor'. $RowCounter. '" size="11" type="text" value="' . $MyRow['conversionfactor'] . '" /></td>
				<td><input maxlength="50" name="SupplierDescription'. $RowCounter. '" size="30" type="text" value="' . $MyRow['supplierdescription'] . '" /></td>
				<td><input class="number" name="LeadTime'. $RowCounter. '" size="11" type="text" value="' . $MyRow['leadtime'] . '" /></td>';
		if($MyRow['preferred'] == 1) {
			echo '<td><input checked="checked" name="Preferred'. $RowCounter. '" type="checkbox" /></td>';
		} else {
			echo '<td><input name="Preferred'. $RowCounter. '" type="checkbox" /></td>';
		}
		echo '<td><input type="date" size="11" name="EffectiveFrom'. $RowCounter. '" value="' . $MyRow['effectivefrom'] . '" /></td>
				<td><input maxlength="50" name="SupplierPartNo'. $RowCounter. '" size="20" type="text" value="' . $MyRow['suppliers_partno'] . '" /></td>
				<td><input class="number" name="MinOrderQty'. $RowCounter. '" size="11" type="text" value="' . $MyRow['minorderqty'] . '" /></td>
				<td><button type="submit" style="width:100%;text-align:left" name="Update'.$RowCounter.'"><img alt="" src="' . $RootPath . '/css/' . $Theme . '/images/tick.svg" width="15" /></button></td>
			</tr>';
		$RowCounter++;
	}
	echo '</table>';
	echo '</form>';
	include('includes/footer.php');
	exit();
}

<?php

include ('includes/session.inc');

$Title = _('Supplier Purchasing Data');

include ('includes/header.inc');

if (isset($_POST['StockSearch'])) {
	echo '<form action="' . htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') . '" method="post">';
    echo '<div>';
	echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';
	echo '<input type="hidden" value="' . $_POST['SupplierID'] . '" name="SupplierID" />';

	echo '<p class="page_title_text"><img src="' . $RootPath . '/css/' . $Theme . '/images/magnifier.png" title="' . _('Search') . '" alt="" />' . ' ' . _('Search for Inventory Items'). '</p>';
	echo '<table class="selection"><tr>';
	echo '<td>' . _('In Stock Category') . ':';
	echo '<select name="StockCat">';
	if (!isset($_POST['StockCat'])) {
		$_POST['StockCat'] = '';
	}
	if ($_POST['StockCat'] == 'All') {
		echo '<option selected="True" value="All">' . _('All').'</option>';
	} else {
		echo '<option value="All">' . _('All').'</option>';
	}
	$SQL = "SELECT categoryid,
				categorydescription
			FROM stockcategory
			ORDER BY categorydescription";
	$result1 = DB_query($SQL, $db);
	while ($myrow1 = DB_fetch_array($result1)) {
		if ($myrow1['categoryid'] == $_POST['StockCat']) {
			echo '<option selected="True" value="' . $myrow1['categoryid'] . '">' . $myrow1['categorydescription'].'</option>';
		} else {
			echo '<option value="' . $myrow1['categoryid'] . '">' . $myrow1['categorydescription'].'</option>';
		}
	}
	echo '</select></td>';
	echo '<td>' . _('Enter partial') . '<b> ' . _('Description') . '</b>:</td><td>';
	if (isset($_POST['Keywords'])) {
		echo '<input type="search" name="Keywords" value="' . $_POST['Keywords'] . '" size="34" maxlength="25" />';
	} else {
		echo '<input type="search" name="Keywords" size="34" maxlength="25" placeholder="Enter part of the item description" />';
	}
	echo '</td></tr><tr><td></td>';
	echo '<td><b>' . _('OR') . ' ' . '</b>' . _('Enter partial') . ' <b>' . _('Stock Code') . '</b>:</td>';
	echo '<td>';
	if (isset($_POST['StockCode'])) {
		echo '<input type="text" name="StockCode" value="' . $_POST['StockCode'] . '" size="15" maxlength="18" />';
	} else {
		echo '<input type="text" name="StockCode" size="15" maxlength="18" />';
	}
	echo '</td></tr></table><br />';
	echo '<div class="centre"><input type="submit" name="Search" value="' . _('Search Now') . '" /></div><br />';
	echo '<script  type="text/javascript">defaultControl(document.forms[0].StockCode);</script>';
	echo '</div>
          </form>';
	include('includes/footer.inc');
	exit;
}

if (isset($_POST['Search']) OR isset($_POST['Go']) OR isset($_POST['Next']) OR isset($_POST['Previous'])) {
	if (!isset($_POST['Go']) AND !isset($_POST['Next']) AND !isset($_POST['Previous'])) {
		// if Search then set to first page
		$_POST['PageOffset'] = 1;
	}
	if ($_POST['Keywords'] AND $_POST['StockCode']) {
		prnMsg (_('Stock description keywords have been used in preference to the Stock code extract entered'), 'info');
	}
	if ($_POST['Keywords']) {
		//insert wildcard characters in spaces
		$_POST['Keywords'] = mb_strtoupper($_POST['Keywords']);
		$SearchString = '%' . str_replace(' ', '%', $_POST['Keywords']) . '%';
		if ($_POST['StockCat'] == 'All') {
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
	} elseif (isset($_POST['StockCode'])) {
		$_POST['StockCode'] = mb_strtoupper($_POST['StockCode']);
		if ($_POST['StockCat'] == 'All') {
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
	} elseif (!isset($_POST['StockCode']) AND !isset($_POST['Keywords'])) {
		if ($_POST['StockCat'] == 'All') {
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
	$ErrMsg = _('No stock items were returned by the SQL because');
	$DbgMsg = _('The SQL that returned an error was');
	$searchresult = DB_query($SQL, $db, $ErrMsg, $DbgMsg);
	if (DB_num_rows($searchresult) == 0) {
		prnMsg(_('No stock items were returned by this search please re-enter alternative criteria to try again'), 'info');
	}
	unset($_POST['Search']);
}
/* end query for list of records */
/* display list if there is more than one record */
if (isset($searchresult) AND !isset($_POST['Select'])) {
	echo '<p class="page_title_text"><img src="' . $RootPath . '/css/' . $Theme . '/images/magnifier.png" title="' . _('Search') . '" alt="" />' . ' ' . _('Search for Inventory Items'). '</p>';
	echo '<form action="' . htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') . '" method="post">';
    echo '<div>';
	echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';
	echo '<input type="hidden" value="' . $_POST['SupplierID'] . '" name="SupplierID" />';
	$ListCount = DB_num_rows($searchresult);
	if ($ListCount > 0) {
		// If the user hit the search button and there is more than one item to show
		$ListPageMax = ceil($ListCount / $_SESSION['DisplayRecordsMax']);
		if (isset($_POST['Next'])) {
			if ($_POST['PageOffset'] < $ListPageMax) {
				$_POST['PageOffset'] = $_POST['PageOffset'] + 1;
			}
		}
		if (isset($_POST['Previous'])) {
			if ($_POST['PageOffset'] > 1) {
				$_POST['PageOffset'] = $_POST['PageOffset'] - 1;
			}
		}
		if ($_POST['PageOffset'] > $ListPageMax) {
			$_POST['PageOffset'] = $ListPageMax;
		}
		if ($ListPageMax > 1) {
			echo '<div class="centre"><br />&nbsp;&nbsp;' . $_POST['PageOffset'] . ' ' . _('of') . ' ' . $ListPageMax . ' ' . _('pages') . '. ' . _('Go to Page') . ': ';
			echo '<select name="PageOffset">';
			$ListPage = 1;
			while ($ListPage <= $ListPageMax) {
				if ($ListPage == $_POST['PageOffset']) {
					echo '<option value=' . $ListPage . ' selected>' . $ListPage . '</option>';
				} else {
					echo '<option value=' . $ListPage . '>' . $ListPage . '</option>';
				}
				$ListPage++;
			}
			echo '</select>
				<input type="submit" name="Go" value="' . _('Go') . '" />
				<input type="submit" name="Previous" value="' . _('Previous') . '" />
				<input type="submit" name="Next" value="' . _('Next') . '" />';
			echo '<input type="hidden" name=Keywords value="'.$_POST['Keywords'].'" />';
			echo '<input type="hidden" name=StockCat value="'.$_POST['StockCat'].'" />';
			echo '<input type="hidden" name=StockCode value="'.$_POST['StockCode'].'" />';
//			echo '<input type="hidden" name=Search value="Search" />';
			echo '<br /></div>';
		}
		echo '<table class="selection">';
		echo'<tr>
				<th>' . _('Code') . '</th>
				<th>' . _('Description') . '</th>
				<th>' . _('Units') . '</th>
			</tr>';
		$j = 1;
		$k = 0; //row counter to determine background colour
		$RowIndex = 0;
		if (DB_num_rows($searchresult) <> 0) {
			DB_data_seek($searchresult, ($_POST['PageOffset'] - 1) * $_SESSION['DisplayRecordsMax']);
		}
		while (($myrow = DB_fetch_array($searchresult)) AND ($RowIndex <> $_SESSION['DisplayRecordsMax'])) {
			if ($k == 1) {
				echo '<tr class="EvenTableRows">';
				$k = 0;
			} else {
				echo '<tr class="OddTableRows">';
				$k++;
			}

			echo '<td><input type="submit" name="Select" value="' . $myrow['stockid'] . '" /></td>
				<td>' . $myrow['description'] . '</td>
				<td>' . $myrow['units'] . '</td>
				</tr>';
			$RowIndex = $RowIndex + 1;
			//end of page full new headings if
		}
		//end of while loop
		echo '</table>
              <br />
              </div>
              </form>';
		include('includes/footer.inc');
		exit;
	}
}

foreach ($_POST as $key=>$value) {
	if (mb_substr($key,0,6)=='Update') {
		$Index = mb_substr($key,6,mb_strlen($key)-6);
		$StockID=$_POST['StockID'.$Index];
		$Price=$_POST['Price'.$Index];
		$SuppUOM=$_POST['SuppUOM'.$Index];
		$ConversionFactor=$_POST['ConversionFactor'.$Index];
		$SupplierDescription=$_POST['SupplierDescription'.$Index];
		$LeadTime=$_POST['LeadTime'.$Index];
		if (isset($_POST['Preferred'.$Index])) {
			$Preferred=1;
			$PreferredSQL="UPDATE purchdata SET preferred=0
									WHERE stockid='" . $StockID . "'";
			$PreferredResult=DB_query($PreferredSQL, $db);
		} else {
			$Preferred=0;
		}
		$EffectiveFrom=$_POST['EffectiveFrom'.$Index];
		$SupplierPartNo=$_POST['SupplierPartNo'.$Index];
		$MinOrderQty=$_POST['MinOrderQty'.$Index];
		$sql="UPDATE purchdata SET price='" . $Price . "',
									suppliersuom='" . $SuppUOM . "',
									conversionfactor='" . $ConversionFactor . "',
									supplierdescription='" . $SupplierDescription . "',
									leadtime='" . $LeadTime . "',
									preferred='" . $Preferred . "',
									effectivefrom='" . FormatDateForSQL($EffectiveFrom) . "',
									suppliers_partno='" . $SupplierPartNo . "',
									minorderqty='" . $MinOrderQty . "'
								WHERE supplierno='" . $_POST['SupplierID'] . "'
								AND stockid='" . $StockID . "'";
		$result=DB_query($sql, $db);
	}
	if (mb_substr($key,0,6)=='Insert') {
		if (isset($_POST['Preferred0'])) {
			$Preferred=1;
		} else {
			$Preferred=0;
		}
		$sql="INSERT INTO purchdata (stockid,
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
									'" . FormatDateForSQL($_POST['EffectiveFrom0']) . "',
									'" . $_POST['SupplierPartNo0'] . "',
									'" . $_POST['MinOrderQty0'] . "'
								)";
		$result=DB_query($sql, $db);
	}
}

if (isset($_GET['SupplierID'])) {
	$SupplierID = trim(mb_strtoupper($_GET['SupplierID']));
} elseif (isset($_POST['SupplierID'])) {
	$SupplierID = trim(mb_strtoupper($_POST['SupplierID']));
}

if (isset($SupplierID) AND $SupplierID != '' AND !isset($_POST['SearchSupplier'])) { /*NOT EDITING AN EXISTING BUT SUPPLIER selected OR ENTERED*/
	$sql = "SELECT suppliers.suppname, suppliers.currcode FROM suppliers WHERE supplierid='".$SupplierID."'";
	$ErrMsg = _('The supplier details for the selected supplier could not be retrieved because');
	$DbgMsg = _('The SQL that failed was');
	$SuppSelResult = DB_query($sql, $db, $ErrMsg, $DbgMsg);
	if (DB_num_rows($SuppSelResult) == 1) {
		$myrow = DB_fetch_array($SuppSelResult);
		$SuppName = $myrow['suppname'];
		$CurrCode = $myrow['currcode'];
	} else {
		prnMsg(_('The supplier code') . ' ' . $SupplierID . ' ' . _('is not an existing supplier in the database') . '. ' . _('You must enter an alternative supplier code or select a supplier using the search facility below'), 'error');
		unset($SupplierID);
	}
} else {
	if ($NoPurchasingData=0) {
		echo '<p class="page_title_text"><img src="' . $RootPath . '/css/' . $Theme . '/images/maintenance.png" title="' . _('Search') . '" alt="" />' . ' ' .
			$Title . ' ' . _('For Stock Code') . ' - ' . $StockID . '</p><br />';
	}
	if (!isset($_POST['SearchSupplier'])) {
		echo '<p class="page_title_text"><img src="' . $RootPath . '/css/' . $Theme . '/images/supplier.png" title="' . _('Search') . '" alt="" />' . _('Search for a supplier') . '</p><br />';
		echo '<form action="' . htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') . '" method="post">';
        echo '<div>';
        echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';

		echo '<table cellpadding="3" class="selection"><tr>';
		echo '<td>' . _('Text in the Supplier') . ' <b>' . _('NAME') . '</b>:</td>';
		echo '<td><input type="text" name="Keywords" size="20" maxlength="25" /></td>';
		echo '<td><b>' . _('OR') . '</b></td>';
		echo '<td>' . _('Text in Supplier') . ' <b>' . _('CODE') . '</b>:</td>';
		echo '<td><input type="text" name="SupplierCode" size="15" maxlength="18" /></td>';
		echo '</tr></table><br />';
		echo '<div class="centre"><input type="submit" name="SearchSupplier" value="' . _('Find Suppliers Now') . '" /></div>';
        echo '</div>
              </form>';
		include ('includes/footer.inc');
		exit;
	};
}

if (isset($_POST['SearchSupplier'])) {
	if ($_POST['Keywords'] == '' AND $_POST['SupplierCode'] == '') {
		$_POST['Keywords'] = ' ';
	}
	if (mb_strlen($_POST['Keywords']) > 0) {
		//insert wildcard characters in spaces
		$SearchString = '%' . str_replace(' ', '%', $_POST['Keywords']) . '%';

		$SQL = "SELECT suppliers.supplierid,
					suppliers.suppname,
					suppliers.currcode,
					suppliers.address1,
					suppliers.address2,
					suppliers.address3
					FROM suppliers WHERE suppliers.suppname " . LIKE  . " '".$SearchString."'";
	} elseif (mb_strlen($_POST['SupplierCode']) > 0) {
		$SQL = "SELECT suppliers.supplierid,
				suppliers.suppname,
				suppliers.currcode,
				suppliers.address1,
				suppliers.address2,
				suppliers.address3
			FROM suppliers
			WHERE suppliers.supplierid " . LIKE . " '%" . $_POST['SupplierCode'] . "%'";
	} //one of keywords or SupplierCode was more than a zero length string
	$ErrMsg = _('The suppliers matching the criteria entered could not be retrieved because');
	$DbgMsg = _('The SQL to retrieve supplier details that failed was');
	$SuppliersResult = DB_query($SQL, $db, $ErrMsg, $DbgMsg);
} //end of if search

if (isset($SuppliersResult)) {
	echo '<p class="page_title_text"><img src="' . $RootPath . '/css/' . $Theme . '/images/supplier.png" title="' . _('Search') . '" alt="" />' . _('Select a supplier') . '</p><br />';
	echo '<form action="' . htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') . '" method="post">';
    echo '<div>';
    echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';

	echo '<table cellpadding="2" class="selection">';
	$TableHeader = '<tr><th>' . _('Code') . '</th>
						<th>' . _('Supplier Name') . '</th>
				<th>' . _('Currency') . '</th>
				<th>' . _('Address 1') . '</th>
				<th>' . _('Address 2') . '</th>
				<th>' . _('Address 3') . '</th>
			</tr>';
	echo $TableHeader;
	$k = 0;
	while ($myrow = DB_fetch_array($SuppliersResult)) {
		if ($k==1){
			echo '<tr class="EvenTableRows">';
			$k=0;
		} else {
			echo '<tr class="OddTableRows">';
			$k++;
		}
	   printf('<td><input type="submit" name="SupplierID" value="%s" /></td>
				<td>%s</td>
				<td>%s</td>
				<td>%s</td>
				<td>%s</td>
				<td>%s</td>
			</tr>',
				$myrow['supplierid'],
				$myrow['suppname'],
				$myrow['currcode'],
				$myrow['address1'],
				$myrow['address2'],
				$myrow['address3']
			);

	}
	//end of while loop
	echo '</table>
          <br/>
          </div>
          </form>';
	include('includes/footer.inc');
	exit;
}
//end if results to show

if (isset($_POST['SupplierID'])) {
	echo '<p class="page_title_text"><img src="' . $RootPath . '/css/' . $Theme . '/images/supplier.png" title="' . _('Search') . '" alt="" />' . _('Supplier Purchasing Data') . '</p><br />';
	echo '<form action="' . htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') . '" method="post">';
    echo '<div>';
	echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';
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

	$result=DB_query($SQL, $db);

	$UOMSQL = "SELECT unitid,
						unitname
					FROM unitsofmeasure";
	$UOMResult = DB_query($UOMSQL, $db);
	echo '<input type="hidden" value="' . $_POST['SupplierID'] . '" name="SupplierID" />';
	echo '<table class="selection">';
	echo '<tr><th colspan="8" style="text-align: left"><h3>' . _('Supplier purchasing data for') . ' ' . $_POST['SupplierID'] . '</h3></th>';
	echo '<th colspan="5" style="text-align: right">' . _('Find new Item Code') . '
			<button type="submit" name="StockSearch"><img width="15" src="' . $RootPath . '/css/' . $Theme . '/images/magnifier.png" alt="" /></button></th></tr>';
	echo '<tr>
			<th>' . _('StockID') . '</th>
			<th>' . _('Description') . '</th>
			<th>' . _('Price') . '</th>
			<th>' . _('Suppliers UOM') . '</th>
			<th>' . _('Conversion Factor') . '</th>
			<th>' . _('Suppliers Description') . '</th>
			<th>' . _('Lead Time') . '</th>
			<th>' . _('Preferred') . '</th>
			<th>' . _('Effective From') . '</th>
			<th>' . _('Suppliers Item Code') . '</th>
			<th>' . _('Min Order Qty') . '</th>
		</tr>';

	if (isset($_POST['Select'])) {
		$StockSQL="SELECT description, units FROM stockmaster WHERE stockid='" . $_POST['Select'] . "'";
		$StockResult=DB_query($StockSQL, $db);
		$StockRow=DB_fetch_array($StockResult);
		echo '<tr bgcolor="#847F7F">
				<td><input type="hidden" value="' . $_POST['Select'] . '" name="StockID0" />' . $_POST['Select'] . '</td>
				<td>' . $StockRow['description'] . '</td>
				<td><input type="text" class="number" size="11" value="0.0000" name="Price0" /></td>
				<td><select name="SuppUOM0">';
					while ($UOMRow=DB_fetch_array($UOMResult)) {
						if ($UOMRow['unitname']==$StRowoc['units']) {
							echo '<option selected="selected" value="'.$UOMRow['unitname'].'">' . $UOMRow['unitname'] . '</option>';
						} else {
							echo '<option value="'.$UOMRow['unitname'].'">' . $UOMRow['unitname'] . '</option>';
						}
					}
					DB_data_seek($UOMResult, 0);
					echo '</select></td>
				<td><input type="text" class="number" size="11" value="1" name="ConversionFactor0" /></td>
				<td><input type="text" size="30" maxlength="50" value="" name="SupplierDescription0" /></td>
				<td><input type="text" class="number" size="11" value="1" name="LeadTime0" /></td>';
				echo '<td><input type="checkbox" name="Preferred0" /></td>';
				echo '<td><input type="text" class="date" size="11" value="' . date( $_SESSION['DefaultDateFormat']) . '" alt="' . $_SESSION['DefaultDateFormat'] . '"  name="EffectiveFrom0" /></td>
				<td><input type="text" size="20" maxlength="50" value="" name="SupplierPartNo0" /></td>
				<td><input type="text" class="number" size="11" value="1" name="MinOrderQty0" /></td>
				<td><button type="submit" style="width:100%;text-align:left" name="Insert"><img width="15" src="' . $RootPath . '/css/' . $Theme . '/images/tick.png" alt="" /></button></td>
			</tr>';
	}

	$RowCounter=1;
	while ($myrow=DB_fetch_array($result)) {
		echo '<tr>
				<td><input type="hidden" value="' . $myrow['stockid'] . '" name="StockID'.$RowCounter.'" />' . $myrow['stockid'] . '</td>
				<td>' . $myrow['description'] . '</td>
				<td><input type="text" class="number" size="11" value="' . $myrow['price'] . '" name="Price'.$RowCounter.'" /></td>
				<td><select name="SuppUOM'.$RowCounter.'">';
					DB_data_seek($UOMResult, 0);
					while ($UOMRow=DB_fetch_array($UOMResult)) {
						if ($UOMRow['unitname']==$myrow['suppliersuom']) {
							echo '<option selected="selected" value="'.$UOMRow['unitname'].'">' . $UOMRow['unitname'] . '</option>';
						} else {
							echo '<option value="'.$UOMRow['unitname'].'">' . $UOMRow['unitname'] . '</option>';
						}
					}
					echo '</select></td>
				<td><input type="text" class="number" size="11" value="' . $myrow['conversionfactor'] . '" name="ConversionFactor'.$RowCounter.'" /></td>
				<td><input type="text" size="30" maxlength="50" value="' . $myrow['supplierdescription'] . '" name="SupplierDescription'.$RowCounter.'" /></td>
				<td><input type="text" class="number" size="11" value="' . $myrow['leadtime'] . '" name="LeadTime'.$RowCounter.'" /></td>';
				if ($myrow['preferred']==1) {
					echo '<td><input type="checkbox" checked="checked" name="Preferred'.$RowCounter.'" /></td>';
				} else {
					echo '<td><input type="checkbox" name="Preferred'.$RowCounter.'" /></td>';
				}
				echo '<td><input type="text" class="date" size="11" value="' . ConvertSQLDate($myrow['effectivefrom']) . '" alt="' . $_SESSION['DefaultDateFormat'] . '"  name="EffectiveFrom'.$RowCounter.'" /></td>
				<td><input type="text" size="20" maxlength="50" value="' . $myrow['suppliers_partno'] . '" name="SupplierPartNo'.$RowCounter.'" /></td>
				<td><input type="text" class="number" size="11" value="' . $myrow['minorderqty'] . '" name="MinOrderQty'.$RowCounter.'" /></td>
				<td><button type="submit" style="width:100%;text-align:left" name="Update'.$RowCounter.'"><img width="15" src="' . $RootPath . '/css/' . $Theme . '/images/tick.png" alt="" /></button></td>
			</tr>';
		$RowCounter++;
	}
	echo '</table>';
	echo '</div>
          </form>';
	include('includes/footer.inc');
	exit;
}

?>
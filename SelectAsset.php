<?php
/* $Id$*/
$PageSecurity = 2;
$PricesSecurity = 9;
include ('includes/session.inc');
$title = _('Select an Asset');
include ('includes/header.inc');

if (isset($_GET['AssetID'])) {
	//The page is called with a AssetID
	$_POST['Select'] = $_GET['AssetID'];
}

if (isset($_GET['NewSearch']) or isset($_POST['Next']) or isset($_POST['Previous']) or isset($_POST['Go'])) {
	unset($AssetID);
	unset($_SESSION['SelectedAsset']);
	unset($_POST['Select']);
}
if (!isset($_POST['PageOffset'])) {
	$_POST['PageOffset'] = 1;
} else {
	if ($_POST['PageOffset'] == 0) {
		$_POST['PageOffset'] = 1;
	}
}
if (isset($_POST['AssetCode'])) {
	$_POST['AssetCode'] = trim(strtoupper($_POST['AssetCode']));
}
// Always show the search facilities
$SQL = 'SELECT categoryid,
								categorydescription
							FROM fixedassetcategories
							ORDER BY categorydescription';
$result = DB_query($SQL, $db);
if (DB_num_rows($result) == 0) {
	echo '<p><font size=4 color=red>' . _('Problem Report') . ':</font><br>' . _('There are no asset categories currently defined please use the link below to set them up');
	echo '<br><a href="' . $rootpath . '/FixedAssetCategories.php?' . SID . '">' . _('Define Asset Categories') . '</a>';
	exit;
}
// end of showing search facilities

echo '<form action="SelectAsset.php?' . SID . '" method=post>';
echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';
echo '<p class="page_title_text"><img src="' . $rootpath . '/css/' . $theme . '/images/magnifier.png" title="' . _('Search') . '" alt="">' . ' ' . $title.'</p>';
echo '<table class=selection><tr>';
echo '<td>' . _('In Asset Category') . ':</td><td>';
echo '<select name="AssetCategory">';

if (!isset($_POST['AssetCategory'])) {
	$_POST['AssetCategory'] = "";
}
if ($_POST['AssetCategory']=='ALL'){
	echo '<option selected value="ALL">' . _('Any asset category') . '</option>';
} else {
	echo '<option value="ALL">' . _('Any asset category') . '</option>';
}

while ($myrow = DB_fetch_array($result)) {
	if ($myrow['categoryid'] == $_POST['AssetCategory']) {
		echo '<option selected VALUE="' . $myrow['categoryid'] . '">' . $myrow['categorydescription'];
	} else {
		echo '<option value="' . $myrow['categoryid'] . '">' . $myrow['categorydescription'];
	}
}
echo '</select>';

echo '<td>' . _('Enter partial description') . '</b>:</td><td>';
if (isset($_POST['Keywords'])) {
	echo '<input type="text" name="Keywords" value="' . $_POST['Keywords'] . '" size=20 maxlength=25>';
} else {
	echo '<input type="text" name="Keywords" size=20 maxlength=25>';
}
echo '</td></tr><tr><td>' . _('Asset Location') . ':</td><td><select name="AssetLocation">';
if (!isset($_POST['AssetLocation'])) {
	$_POST['AssetLocation'] = 'ALL';
}
if ($_POST['AssetLocation']=='ALL'){
	echo '<option selected value="ALL">' . _('Any asset location') . '</option>';
} else {
	echo '<option value="ALL">' . _('Any asset location') . '</option>';
}
$result = DB_query('SELECT locationid, locationdescription FROM fixedassetlocations',$db);

while ($myrow = DB_fetch_array($result)) {
	if ($myrow['locationid'] == $_POST['AssetLocation']) {
		echo '<option selected VALUE="' . $myrow['locationid'] . '">' . $myrow['locationdescription'];
	} else {
		echo '<option value="' . $myrow['locationid'] . '">' . $myrow['locationdescription'];
	}
}
echo '</select>';

echo '  </td><td><b>' . _('OR') . ' ' . '</b>' . _('Enter partial asset code') . '</b>:</td>';
echo '<td>';
if (isset($_POST['AssetCode'])) {
	echo '<input type="text" class="number" name="AssetCode" value="' . $_POST['AssetCode'] . '" size=15 maxlength=13>';
} else {
	echo '<input type="text" name="AssetCode" size=15 maxlength=13>';
}
echo '</td></tr></table><br>';
echo '<div class="centre"><input type=submit name="Search" value="' . _('Search Now') . '"></div><br></form>';
echo '<script  type="text/javascript">defaultControl(document.forms[0].Keywords);</script>';
echo '</form>';
// query for list of record(s)
if(isset($_POST['Go']) OR isset($_POST['Next']) OR isset($_POST['Previous'])) {
	$_POST['Search']='Search';
}
if (isset($_POST['Search']) OR isset($_POST['Go']) OR isset($_POST['Next']) OR isset($_POST['Previous'])) {
	if (!isset($_POST['Go']) AND !isset($_POST['Next']) AND !isset($_POST['Previous'])) {
		// if Search then set to first page
		$_POST['PageOffset'] = 1;
	}
	if ($_POST['Keywords'] AND $_POST['AssetCode']) {
		prnMsg( _('Asset description keywords have been used in preference to the asset code extract entered'), 'info' );
	}
	$SQL = 'SELECT assetid,
									description,
									datepurchased,
									fixedassetlocations.locationdescription
							FROM fixedassets INNER JOIN fixedassetlocations 
							ON fixedassets.assetlocation=fixedassetlocations.locationid ';
							
	if ($_POST['Keywords']) {
		//insert wildcard characters in spaces
		$_POST['Keywords'] = strtoupper($_POST['Keywords']);
		$SearchString = '%' . str_replace(' ', '%', $_POST['Keywords']) . '%';
		if ($_POST['AssetCategory'] == 'All') {
			if ($_POST['AssetLocation']=='ALL'){
				$SQL .= 'WHERE description ' . LIKE .  "'" . $SearchString . "' ORDER BY fixedassets.assetid";
			} else {
				$SQL .= "WHERE fixedassets.assetlocation='" . $_POST['AssetLocation'] . "' AND description " . LIKE .  "'" . $SearchString . "' ORDER BY fixedassets.assetid";
			}
		} else {
			if ($_POST['AssetLocation']=='ALL'){
				$SQL .= 'WHERE description ' . LIKE .  "'" . $SearchString . "' AND  assetcategoryid='" . $_POST['AssetCategory'] . "' ORDER BY fixedassets.assetid";
			} else {
				$SQL .= "WHERE fixedassets.assetlocation='" . $_POST['AssetLocation'] . "' AND description " . LIKE .  "'" . $SearchString . "' AND  assetcategoryid='" . $_POST['AssetCategory'] . "'
								ORDER BY fixedassets.assetid";
			}				
		}
	} elseif (isset($_POST['AssetCode'])) {
		if ($_POST['AssetCategory'] == 'ALL') {
			if ($_POST['AssetLocation']=='ALL'){
				$SQL .= 'WHERE fixedassets.assetid ' . LIKE . " '%" . $_POST['AssetCode'] . "%' ORDER BY fixedassets.assetid";
			} else {
				$SQL .= "WHERE fixedassets.assetlocation='" . $_POST['AssetLocation'] . "' AND fixedassets.assetid " . LIKE . " '%" . $_POST['AssetCode'] . "%' ORDER BY fixedassets.assetid";
			}
		} else {
			if ($_POST['AssetLocation']=='ALL'){
				$SQL .= 'WHERE fixedassets.assetid ' . LIKE . " '%" . $_POST['AssetCode'] . "%' AND  assetcategoryid='" . $_POST['AssetCategory'] . "'
								ORDER BY fixedassets.assetid";
			} else {
				$SQL .= "WHERE fixedassets.assetlocation='" . $_POST['AssetLocation'] . "' AND fixedassets.assetid " . LIKE . " '%" . $_POST['AssetCode'] . "%' AND  assetcategoryid='" . $_POST['AssetCategory'] . "' ORDER BY fixedassets.assetid";
			}
		}
	} elseif (!isset($_POST['AssetCode']) AND !isset($_POST['Keywords'])) {
		if ($_POST['AssetCategory'] == 'All') {
			if ($_POST['AssetLocation']=='ALL'){
				$SQL .= 'ORDER BY fixedassets.assetid';
			} else {
				$SQL .= "WHERE fixedassets.assetlocation='" . $_POST['AssetLocation'] . "'  ORDER BY fixedassets.assetid";
			}
		} else {
			if ($_POST['AssetLocation']=='ALL'){
				$SQL .= "WHERE assetcategoryid='" . $_POST['AssetCategory'] . "' ORDER BY fixedassets.assetid";
			} else {
				$SQL .= "WHERE assetcategoryid='" . $_POST['AssetCategory'] . "' AND fixedassets.assetlocation='" . $_POST['AssetLocation'] . "'  ORDER BY fixedassets.assetid";
			}
		}
	}
	$ErrMsg = _('No assets were returned by the SQL because');
	$DbgMsg = _('The SQL that returned an error was');
	$searchresult = DB_query($SQL, $db, $ErrMsg, $DbgMsg);
	if (DB_num_rows($searchresult) == 0) {
		prnMsg(_('No assets were returned by this search please re-enter alternative criteria to try again'), 'info');
	}
	unset($_POST['Search']);
}
/* end query for list of records */
/* display list if there is more than one record */
if (isset($searchresult) AND !isset($_POST['Select'])) {
	echo '<form action="FixedAssetItems.php?' . SID . '" method=post>';
	echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';
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
			echo "<div class='centre'><p>&nbsp;&nbsp;" . $_POST['PageOffset'] . ' ' . _('of') . ' ' . $ListPageMax . ' ' . _('pages') . '. ' . _('Go to Page') . ': ';
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
				<input type=submit name="Go" value="' . _('Go') . '">
				<input type=submit name="Previous" value="' . _('Previous') . '">
				<input type=submit name="Next" value="' . _('Next') . '">';
			
			echo '<p></div>';
		}
		echo '<table cellpadding=2 colspan=7 class=selection>';
		$tableheader = '<tr>
					<th>' . _('Asset Code') . '</th>
					<th>' . _('Description') . '</th>
					<th>' . _('Date Purchased') . '</th>
					<th>' . _('Asset Location') . '</th>
				</tr>';
		echo $tableheader;
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
			echo '<td><input type="submit" name="Select" value="' . $myrow['assetid'] .'"></td>
						<td>' . $myrow['description'] . '</td>
						<td>' . $myrow['locationdescription'] . '</td>
						<td>' . ConvertSQLDate($myrow['datepurchased']) . '</td>
						</tr>';
			$j++;
			if ($j == 20 AND ($RowIndex + 1 != $_SESSION['DisplayRecordsMax'])) {
				$j = 1;
				echo $tableheader;
			}
			$RowIndex = $RowIndex + 1;
			//end of page full new headings if
		}
		//end of while loop
		echo '</table></form>';
	} // there were records to list
}
/* end display list if there is more than one record */
include ('includes/footer.inc');
?>
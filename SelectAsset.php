<?php

include('includes/session.php');

$Title = __('Select an Asset');
$ViewTopic = 'FixedAssets';
$BookMark = 'AssetSelection';
include('includes/header.php');

if (isset($_GET['AssetID'])) {
	//The page is called with a AssetID
	$_POST['Select'] = $_GET['AssetID'];
}

if (isset($_GET['NewSearch']) OR isset($_POST['Next']) OR isset($_POST['Previous']) OR isset($_POST['Go'])) {
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
	$_POST['AssetCode'] = trim(mb_strtoupper($_POST['AssetCode']));
}

if (!isset($_POST['DisposalStatus'])) {
	$_POST['DisposalStatus'] = "ACTIVE";
}

// Always show the search facilities
$SQL = "SELECT categoryid,
				categorydescription
			FROM fixedassetcategories
			ORDER BY categorydescription";
$Result = DB_query($SQL);
if (DB_num_rows($Result) == 0) {
	echo '<p><font size="4" color="red">' . __('Problem Report') . ':</font><br />' .
		__('There are no asset categories currently defined please use the link below to set them up');
	echo '<br /><a href="' . $RootPath . '/FixedAssetCategories.php">' . __('Define Asset Categories') . '</a>';
	exit();
}
// end of showing search facilities

echo '<form action="' . htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') . '" method="post">
		<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />
		<p class="page_title_text"><img src="' . $RootPath . '/css/' . $Theme .
		'/images/magnifier.png" title="' . __('Search') . '" alt="" />' . ' ' . $Title . '</p>
		<fieldset>
		<legend class="search">', __('Search Criteria'), '</legend>
		<field>
			<label for="AssetCategory">' . __('In Asset Category') . ':</label>
			<select name="AssetCategory">';

if (!isset($_POST['AssetCategory'])) {
	$_POST['AssetCategory'] = 'ALL';
}
if ($_POST['AssetCategory'] == 'ALL') {
	echo '<option selected="selected" value="ALL">' . __('Any asset category') . '</option>';
} else {
	echo '<option value="ALL">' . __('Any asset category') . '</option>';
}

while ($MyRow = DB_fetch_array($Result)) {
	if ($MyRow['categoryid'] == $_POST['AssetCategory']) {
		echo '<option selected="selected" value="' . $MyRow['categoryid'] . '">' . $MyRow['categorydescription'] . '</option>';
	} else {
		echo '<option value="' . $MyRow['categoryid'] . '">' . $MyRow['categorydescription'] . '</option>';
	}
}
echo '</select>
	</field>';

echo '<field>
		<label for="Keywords">' . __('Enter partial description') . ':</label>';
if (isset($_POST['Keywords'])) {
	echo '<input type="text" name="Keywords" autofocus="autofocus" value="' . $_POST['Keywords'] . '" size="20" maxlength="25" />';
} else {
	echo '<input type="text" name="Keywords" autofocus="autofocus" size="20" maxlength="25" />';
}
echo '</field>';

echo '<field>
		<label for="AssetLocation">' . __('Asset Location') . ':</label>
		<select name="AssetLocation">';

if (!isset($_POST['AssetLocation'])) {
	$_POST['AssetLocation'] = 'ALL';
}
if ($_POST['AssetLocation'] == 'ALL') {
	echo '<option selected="selected" value="ALL">' . __('Any asset location') . '</option>';
} else {
	echo '<option value="ALL">' . __('Any asset location') . '</option>';
}
$Result = DB_query("SELECT locationid, locationdescription FROM fixedassetlocations");

while ($MyRow = DB_fetch_array($Result)) {
	if ($MyRow['locationid'] == $_POST['AssetLocation']) {
		echo '<option selected="selected" value="' . $MyRow['locationid'] . '">' . $MyRow['locationdescription'] . '</option>';
	} else {
		echo '<option value="' . $MyRow['locationid'] . '">' . $MyRow['locationdescription'] . '</option>';
	}
}
echo '</select>
	</field>';

echo '<field>
		<label>'. '<b>' . __('OR') . ' </b>' . __('Enter partial asset code') . ':</label>';
if (isset($_POST['AssetCode'])) {
	echo '<input type="text" class="number" name="AssetCode" value="' . $_POST['AssetCode'] . '" size="15" maxlength="13" />';
} else {
	echo '<input type="text" name="AssetCode" size="15" maxlength="13" />';
}

echo '</field>';

echo '<field>
		<label for="DisposalStatus">' . __('Asset Disposal Status') . ':</label>
		<select name="DisposalStatus">';

if ($_POST['DisposalStatus'] == 'ALL') {
	echo '	<option selected="selected" value="ALL">' . __('All') . '</option>
			<option value="ACTIVE">' . __('Active') . '</option>
			<option value="DISPOSED">' . __('Disposed') . '</option>';
} elseif ($_POST['DisposalStatus'] == 'ACTIVE') {
	echo '	<option value="ALL">' . __('All') . '</option>
			<option selected="selected" value="ACTIVE">' . __('Active') . '</option>
			<option value="DISPOSED">' . __('Disposed') . '</option>';
} else {
	echo '	<option value="ALL">' . __('All') . '</option>
			<option value="ACTIVE">' . __('Active') . '</option>
			<option selected="selected" value="DISPOSED">' . __('Disposed') . '</option>';
}

echo '</select>
	</field>';

echo '</fieldset>
	<div class="centre">
		<input type="submit" name="Search" value="' . __('Search Now') . '" />
	</div>';

// query for list of record(s)
if (isset($_POST['Go']) OR isset($_POST['Next']) OR isset($_POST['Previous'])) {
	$_POST['Search'] = 'Search';
}
if (isset($_POST['Search']) OR isset($_POST['Go']) OR isset($_POST['Next']) OR isset($_POST['Previous'])) {
	if (!isset($_POST['Go']) AND !isset($_POST['Next']) AND !isset($_POST['Previous'])) {
		// if Search then set to first page
		$_POST['PageOffset'] = 1;
	}
	if ($_POST['Keywords'] AND $_POST['AssetCode']) {
		prnMsg(__('Asset description keywords have been used in preference to the asset code extract entered'), 'info');
	}
	$SQL = "SELECT assetid,
					description,
					datepurchased,
					fixedassetlocations.locationdescription
			FROM fixedassets INNER JOIN fixedassetlocations
			ON fixedassets.assetlocation=fixedassetlocations.locationid ";

	if ($_POST['Keywords']) {
		//insert wildcard characters in spaces
		$_POST['Keywords'] = mb_strtoupper($_POST['Keywords']);
		$SearchString = '%' . str_replace(' ', '%', $_POST['Keywords']) . '%';
		if ($_POST['AssetCategory'] == 'ALL') {
			if ($_POST['AssetLocation'] == 'ALL') {
				$SQL .= "WHERE description " . LIKE . " '" . $SearchString . "'";
			} else {
				$SQL .= "WHERE fixedassets.assetlocation='" . $_POST['AssetLocation'] . "'
						AND description " . LIKE . " '" . $SearchString . "'";
			}
		} else {
			if ($_POST['AssetLocation'] == 'ALL') {
				$SQL .= "WHERE description " . LIKE . " '" . $SearchString . "'
						AND assetcategoryid='" . $_POST['AssetCategory'] . "'";
			} else {
				$SQL .= "WHERE fixedassets.assetlocation='" . $_POST['AssetLocation'] . "'
						AND description " . LIKE . " '" . $SearchString . "'
						AND assetcategoryid='" . $_POST['AssetCategory'] . "'";
			}
		}
	} elseif (isset($_POST['AssetCode'])) {
		if ($_POST['AssetCategory'] == 'ALL') {
			if ($_POST['AssetLocation'] == 'ALL') {
				$SQL .= "WHERE fixedassets.assetid " . LIKE . " '%" . $_POST['AssetCode'] . "%'";
			} else {
				$SQL .= "WHERE fixedassets.assetlocation='" . $_POST['AssetLocation'] . "'
						AND fixedassets.assetid " . LIKE . " '%" . $_POST['AssetCode'] . "%'";
			}
		} else {
			if ($_POST['AssetLocation'] == 'ALL') {
				$SQL .= "WHERE fixedassets.assetid " . LIKE . " '%" . $_POST['AssetCode'] . "%'
						AND assetcategoryid='" . $_POST['AssetCategory'] . "'";
			} else {
				$SQL .= "WHERE fixedassets.assetlocation='" . $_POST['AssetLocation'] . "'
						AND fixedassets.assetid " . LIKE . " '%" . $_POST['AssetCode'] . "%'
						AND assetcategoryid='" . $_POST['AssetCategory'] . "'";
			}
		}
	} elseif (!isset($_POST['AssetCode']) AND !isset($_POST['Keywords'])) {
		if ($_POST['AssetCategory'] == 'All') {
			if ($_POST['AssetLocation'] == 'ALL') {
				$SQL .= 'WHERE 1=1 ';
			} else {
				$SQL .= "WHERE fixedassets.assetlocation='" . $_POST['AssetLocation'] . "'";
			}
		} else {
			if ($_POST['AssetLocation'] == 'ALL') {
				$SQL .= "WHERE assetcategoryid='" . $_POST['AssetCategory'] . "'";
			} else {
				$SQL .= "WHERE assetcategoryid='" . $_POST['AssetCategory'] . "'
						AND fixedassets.assetlocation='" . $_POST['AssetLocation'] . "'";
			}
		}
	}

	if ($_POST['DisposalStatus'] == 'ALL') {
		$SQL .= ' ';
	} elseif ($_POST['DisposalStatus'] == 'ACTIVE') {
		$SQL .= ' AND disposaldate = "1000-01-01"';
	} else {
		$SQL .= ' AND disposaldate != "1000-01-01"';
	}

	$SQL .= " ORDER BY fixedassets.assetid";

	$ErrMsg = __('No assets were returned by the SQL because');
	$SearchResult = DB_query($SQL, $ErrMsg);

	if (DB_num_rows($SearchResult) == 0) {
		prnMsg(__('No assets were returned by this search please re-enter alternative criteria to try again'), 'info');
	}
	unset($_POST['Search']);
}
/* end query for list of records */
/* display list if there is more than one record */
if (isset($SearchResult) AND !isset($_POST['Select'])) {
	$ListCount = DB_num_rows($SearchResult);
	if ($ListCount > 0) {
		// If the user hit the search button and there is more than one item to show
		$ListPageMax = ceil($ListCount / $_SESSION['DisplayRecordsMax']);
		if (isset($_POST['Next'])) {
			if ($_POST['PageOffset'] < $ListPageMax) {
				$_POST['PageOffset']++;
			}
		}
		if (isset($_POST['Previous'])) {
			if ($_POST['PageOffset'] > 1) {
				$_POST['PageOffset']--;
			}
		}
		if ($_POST['PageOffset'] > $ListPageMax) {
			$_POST['PageOffset'] = $ListPageMax;
		}
		if ($ListPageMax > 1) {
			echo '<div class="centre"><p>&nbsp;&nbsp;' . $_POST['PageOffset'] . ' ' . __('of') . ' ' .
				$ListPageMax . ' ' . __('pages') . '. ' . __('Go to Page') . ': ';
			echo '<select name="PageOffset">';
			$ListPage = 1;
			while ($ListPage <= $ListPageMax) {
				if ($ListPage == $_POST['PageOffset']) {
					echo '<option value="' . $ListPage . '" selected="selected">' . $ListPage . '</option>';
				} else {
					echo '<option value="' . $ListPage . '">' . $ListPage . '</option>';
				}
				$ListPage++;
			}
			echo '</select>
				<input type="submit" name="Go" value="' . __('Go') . '" />
				<input type="submit" name="Previous" value="' . __('Previous') . '" />
				<input type="submit" name="Next" value="' . __('Next') . '" />';

			echo '<br /></div>';
		}
		echo '</form>';

		echo '<form action="FixedAssetItems.php" method="post">';
		echo '<div>';
		echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';

		echo '<table class="selection">';
		$TableHeader = '<tr>
					<th>' . __('Asset Code') . '</th>
					<th>' . __('Description') . '</th>
					<th>' . __('Asset Location') . '</th>
					<th>' . __('Date Purchased') . '</th>
				</tr>';
		echo $TableHeader;
		$j = 1;
		$RowIndex = 0;
		if (DB_num_rows($SearchResult) <> 0) {
			DB_data_seek($SearchResult, ($_POST['PageOffset'] - 1) * $_SESSION['DisplayRecordsMax']);
		}
		while (($MyRow = DB_fetch_array($SearchResult)) AND ($RowIndex <> $_SESSION['DisplayRecordsMax'])) {
			echo '<tr class="striped_row">
				<td><input type="submit" name="Select" value="' . $MyRow['assetid'] . '" /></td>
				<td>' . $MyRow['description'] . '</td>
				<td>' . $MyRow['locationdescription'] . '</td>
				<td>' . ConvertSQLDate($MyRow['datepurchased']) . '</td>
				</tr>';
			$j++;
			if ($j == 20 AND ($RowIndex + 1 != $_SESSION['DisplayRecordsMax'])) {
				$j = 1;
				echo $TableHeader;
			}
			$RowIndex = $RowIndex + 1;
			//end of page full new headings if
		}
		//end of while loop
		echo '</table>';
		echo '</div>
          </form>';
	} // there were records to list

}
/* end display list if there is more than one record */
include('includes/footer.php');

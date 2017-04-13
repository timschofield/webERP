<?php
/* $Id: SelectAsset.php 4443 2010-12-23 15:30:30Z tim_schofield $*/

include ('includes/session.php');
$Title = _('Select an Asset');

$ViewTopic = 'FixedAssets';
$BookMark = 'AssetSelection';

include ('includes/header.php');

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
// Always show the search facilities
$SQL = "SELECT categoryid,
				categorydescription
			FROM fixedassetcategories
			ORDER BY categorydescription";
$result = DB_query($SQL);
if (DB_num_rows($result) == 0) {
	echo '<p><font size="4" color="red">' . _('Problem Report') . ':</font><br />' . _('There are no asset categories currently defined please use the link below to set them up');
	echo '<br /><a href="' . $RootPath . '/FixedAssetCategories.php">' . _('Define Asset Categories') . '</a>';
	exit;
}
// end of showing search facilities

echo '<form action="' . htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') . '" method="post">
	<div>
		<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />
		<p class="page_title_text"><img src="' . $RootPath . '/css/' . $Theme . '/images/magnifier.png" title="' . _('Search') . '" alt="" />' . ' ' . $Title . '</p>
		<table class="selection">
		<tr>
			<td>' . _('In Asset Category') . ':</td>
			<td><select name="AssetCategory">';

if (!isset($_POST['AssetCategory'])) {
	$_POST['AssetCategory'] = 'ALL';
}
if ($_POST['AssetCategory']=='ALL'){
	echo '<option selected="selected" value="ALL">' . _('Any asset category') . '</option>';
} else {
	echo '<option value="ALL">' . _('Any asset category') . '</option>';
}

while ($myrow = DB_fetch_array($result)) {
	if ($myrow['categoryid'] == $_POST['AssetCategory']) {
		echo '<option selected="selected" value="' . $myrow['categoryid'] . '">' . $myrow['categorydescription'] . '</option>';
	} else {
		echo '<option value="' . $myrow['categoryid'] . '">' . $myrow['categorydescription'] . '</option>';
	}
}
echo '</select></td>
	<td>' . _('Enter partial description') . ':</td>
	<td>';
if (isset($_POST['Keywords'])) {
	echo '<input type="text" name="Keywords" autofocus="autofocus" value="' . $_POST['Keywords'] . '" size="20" maxlength="25" />';
} else {
	echo '<input type="text" name="Keywords" autofocus="autofocus" size="20" maxlength="25" />';
}
echo '</td>
	</tr>
	<tr>
		<td>' . _('Asset Location') . ':</td>
		<td><select name="AssetLocation">';

if (!isset($_POST['AssetLocation'])) {
	$_POST['AssetLocation'] = 'ALL';
}
if ($_POST['AssetLocation']=='ALL'){
	echo '<option selected="selected" value="ALL">' . _('Any asset location') . '</option>';
} else {
	echo '<option value="ALL">' . _('Any asset location') . '</option>';
}
$result = DB_query("SELECT locationid, locationdescription FROM fixedassetlocations");

while ($myrow = DB_fetch_array($result)) {
	if ($myrow['locationid'] == $_POST['AssetLocation']) {
		echo '<option selected="selected" value="' . $myrow['locationid'] . '">' . $myrow['locationdescription'] . '</option>';
	} else {
		echo '<option value="' . $myrow['locationid'] . '">' . $myrow['locationdescription'] . '</option>';
	}
}
echo '</select>';

echo '  </td>
		<td><b>' . _('OR') . ' ' . '</b>' . _('Enter partial asset code') . ':</td>
		<td>';
if (isset($_POST['AssetCode'])) {
	echo '<input type="text" class="number" name="AssetCode" value="' . $_POST['AssetCode'] . '" size="15" maxlength="13" />';
} else {
	echo '<input type="text" name="AssetCode" size="15" maxlength="13" />';
}
echo '</td>
	</tr>
	</table>
	<br />
	<div class="centre">
		<input type="submit" name="Search" value="' . _('Search Now') . '" />
	</div>
	<br />
    </div>';

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
			if ($_POST['AssetLocation']=='ALL'){
				$SQL .= "WHERE description " . LIKE .  "'" . $SearchString . "'
						ORDER BY fixedassets.assetid";
			} else {
				$SQL .= "WHERE fixedassets.assetlocation='" . $_POST['AssetLocation'] . "'
						AND description " . LIKE .  "'" . $SearchString . "'
						ORDER BY fixedassets.assetid";
			}
		} else {
			if ($_POST['AssetLocation']=='ALL'){
				$SQL .= "WHERE description " . LIKE .  "'" . $SearchString . "'
						AND  assetcategoryid='" . $_POST['AssetCategory'] . "'
						ORDER BY fixedassets.assetid";
			} else {
				$SQL .= "WHERE fixedassets.assetlocation='" . $_POST['AssetLocation'] . "'
						AND description " . LIKE .  "'" . $SearchString . "'
						AND  assetcategoryid='" . $_POST['AssetCategory'] . "'
						ORDER BY fixedassets.assetid";
			}
		}
	} elseif (isset($_POST['AssetCode'])) {
		if ($_POST['AssetCategory'] == 'ALL') {
			if ($_POST['AssetLocation']=='ALL'){
				$SQL .= "WHERE fixedassets.assetid " . LIKE . " '%" . $_POST['AssetCode'] . "%'
						ORDER BY fixedassets.assetid";
			} else {
				$SQL .= "WHERE fixedassets.assetlocation='" . $_POST['AssetLocation'] . "'
						AND fixedassets.assetid " . LIKE . " '%" . $_POST['AssetCode'] . "%'
						ORDER BY fixedassets.assetid";
			}
		} else {
			if ($_POST['AssetLocation']=='ALL'){
				$SQL .= "WHERE fixedassets.assetid " . LIKE . " '%" . $_POST['AssetCode'] . "%'
						AND  assetcategoryid='" . $_POST['AssetCategory'] . "'
						ORDER BY fixedassets.assetid";
			} else {
				$SQL .= "WHERE fixedassets.assetlocation='" . $_POST['AssetLocation'] . "'
						AND fixedassets.assetid " . LIKE . " '%" . $_POST['AssetCode'] . "%'
						AND  assetcategoryid='" . $_POST['AssetCategory'] . "'
						ORDER BY fixedassets.assetid";
			}
		}
	} elseif (!isset($_POST['AssetCode']) AND !isset($_POST['Keywords'])) {
		if ($_POST['AssetCategory'] == 'All') {
			if ($_POST['AssetLocation']=='ALL'){
				$SQL .= 'ORDER BY fixedassets.assetid';
			} else {
				$SQL .= "WHERE fixedassets.assetlocation='" . $_POST['AssetLocation'] . "'
						ORDER BY fixedassets.assetid";
			}
		} else {
			if ($_POST['AssetLocation']=='ALL'){
				$SQL .= "WHERE assetcategoryid='" . $_POST['AssetCategory'] . "'
						ORDER BY fixedassets.assetid";
			} else {
				$SQL .= "WHERE assetcategoryid='" . $_POST['AssetCategory'] . "'
						AND fixedassets.assetlocation='" . $_POST['AssetLocation'] . "'
						ORDER BY fixedassets.assetid";
			}
		}
	}

	$ErrMsg = _('No assets were returned by the SQL because');
	$DbgMsg = _('The SQL that returned an error was');
	$SearchResult = DB_query($SQL, $ErrMsg, $DbgMsg);

	if (DB_num_rows($SearchResult) == 0) {
		prnMsg(_('No assets were returned by this search please re-enter alternative criteria to try again'), 'info');
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
				$_POST['PageOffset'] ++;
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
			echo '<div class="centre"><p>&nbsp;&nbsp;' . $_POST['PageOffset'] . ' ' . _('of') . ' ' . $ListPageMax . ' ' . _('pages') . '. ' . _('Go to Page') . ': ';
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
				<input type="submit" name="Go" value="' . _('Go') . '" />
				<input type="submit" name="Previous" value="' . _('Previous') . '" />
				<input type="submit" name="Next" value="' . _('Next') . '" />';

			echo '<br /></div>';
		}
		echo '</form>';

		echo '<form action="FixedAssetItems.php" method="post">';
		echo '<div>';
		echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';

		echo '<table class="selection">';
		$tableheader = '<tr>
					<th>' . _('Asset Code') . '</th>
					<th>' . _('Description') . '</th>
					<th>' . _('Asset Location') . '</th>
					<th>' . _('Date Purchased') . '</th>
				</tr>';
		echo $tableheader;
		$j = 1;
		$k = 0; //row counter to determine background colour
		$RowIndex = 0;
		if (DB_num_rows($SearchResult) <> 0) {
			DB_data_seek($SearchResult, ($_POST['PageOffset'] - 1) * $_SESSION['DisplayRecordsMax']);
		}
		while (($myrow = DB_fetch_array($SearchResult)) AND ($RowIndex <> $_SESSION['DisplayRecordsMax'])) {
			if ($k == 1) {
				echo '<tr class="EvenTableRows">';
				$k = 0;
			} else {
				echo '<tr class="OddTableRows">';
				$k++;
			}
			echo '<td><input type="submit" name="Select" value="' . $myrow['assetid'] .'" /></td>
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
		echo '</table>';
		echo '</div>
          </form>';
	} // there were records to list

}
/* end display list if there is more than one record */
include ('includes/footer.php');
?>

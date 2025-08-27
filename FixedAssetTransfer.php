<?php

require(__DIR__ . '/includes/session.php');

$Title = __('Change Asset Location');
$ViewTopic = 'FixedAssets';
$BookMark = 'AssetTransfer';
include('includes/header.php');

echo '<p class="page_title_text"><img src="'.$RootPath.'/css/'.$Theme.'/images/magnifier.png" title="' . __('Search') .
		'" alt="" />' . ' ' . $Title . '</p>';

foreach ($_POST as $AssetToMove => $Value) { //Value is not used?
	if (mb_substr($AssetToMove,0,4)=='Move') { // the form variable is of the format MoveAssetID so need to strip the move bit off
		$AssetID	= mb_substr($AssetToMove,4);
		if (isset($_POST['Location' . $AssetID]) AND $_POST['Location' . $AssetID] !=''){
			$SQL		= "UPDATE fixedassets
						SET assetlocation='".$_POST['Location'.$AssetID] ."'
						WHERE assetid='". $AssetID . "'";

			$Result = DB_query($SQL);
			prnMsg(__('The Fixed Asset has been moved successfully'), 'success');
			echo '<br />';
		}
	}
}

if (isset($_GET['AssetID'])) {
	$AssetID=$_GET['AssetID'];
} else if (isset($_POST['AssetID'])) {
	$AssetID=$_POST['AssetID'];
} else {
	$SQL="SELECT categoryid, categorydescription FROM fixedassetcategories";
	$Result = DB_query($SQL);
	echo '<form action="'. htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') . '" method="post">';
	echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';

	echo '<fieldset>
			<legend>', __('Asset Transfer Details'), '</legend>';

	echo '<field>
			<label for="AssetCat">' .  __('In Asset Category') . ': </label>
			<select name="AssetCat">';

	if (!isset($_POST['AssetCat'])) {
		$_POST['AssetCat'] = '';
	}

	while ($MyRow = DB_fetch_array($Result)) {
		if ($MyRow['categoryid'] == $_POST['AssetCat']) {
			echo '<option selected="selected" value="' . $MyRow['categoryid'] . '">' . $MyRow['categorydescription'] . '</option>';
		} else {
			echo '<option value="' . $MyRow['categoryid'] . '">' . $MyRow['categorydescription'] . '</option>';
		}
	}

	echo '</select>
		</field>';

	echo '<field>
			<label for="Keywords">' .  __('Enter partial') . '<b> ' . __('Description') . '</b>:</label>';

	if (isset($_POST['Keywords'])) {
		echo '<input type="text" name="Keywords" value="' . trim($_POST['Keywords'],'%') . '" title="' . __('Enter some text that should appear in the fixed asset\'s description to search for') . '" size="20" maxlength="25" />';
	} else {
		echo '<input type="text" name="Keywords" title="' . __('Enter some text that should appear in the fixed asset\'s description to search for') . '" size="20" maxlength="25" />';
	}

	echo '</field>';

	echo '<field>
			<label for="AssetLocation">' . __('Asset Location') . ':</label>
			<select name="AssetLocation">';
			if (!isset($_POST['AssetLocation'])) {
				$_POST['AssetLocation'] = 'ALL';
			}
			if ($_POST['AssetLocation']=='ALL'){
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
			<label><b>' . __('OR').' ' . '</b>' . __('Enter partial') .' <b>' .  __('Asset Code') . '</b>:</label>';

	if (isset($_POST['AssetID'])) {
		echo '<input type="text" name="AssetID" value="'. trim($_POST['AssetID'],'%') . '" title="' . __('Enter some text that should appear in the fixed asset\'s item code to search for') . '" size="15" maxlength="20" />';
	} else {
		echo '<input type="text" name="AssetID" title="' . __('Enter some text that should appear in the fixed asset\'s item code to search for') . '" size="15" maxlength="20" />';
	}

	echo '</td>
		</field>
	</fieldset>';

	echo '<div class="centre">
			<input type="submit" name="Search" value="'. __('Search Now') . '" />
		</div>
	</form>';
}

if (isset($_POST['Search'])) {

	if ($_POST['AssetLocation']=='ALL') {
		$AssetLocation	='%';
	} else {
		$AssetLocation	= '%'.$_POST['AssetLocation'].'%';
	}
	if ($_POST['AssetCat']=='All') {
		$AssetID	='%';
	}
	if (isset($_POST['Keywords'])) {
		$Keywords	='%'.$_POST['Keywords'].'%';
	} else {
		$Keywords	='%';
	}
	if (isset($_POST['AssetID'])) {
		$AssetID	='%'.$_POST['AssetID'].'%';
	} else {
		$AssetID	='%';
	}


	$SQL= "SELECT fixedassets.assetid,
				fixedassets.cost,
				fixedassets.accumdepn,
				fixedassets.description,
				fixedassets.depntype,
				fixedassets.serialno,
				fixedassets.barcode,
				fixedassets.assetlocation as ItemAssetLocation,
				fixedassetlocations.locationdescription
			FROM fixedassets
			INNER JOIN fixedassetlocations
			ON fixedassets.assetlocation=fixedassetlocations.locationid
			WHERE fixedassets.assetcategoryid " . LIKE . "'".$_POST['AssetCat']."'
			AND fixedassets.description " . LIKE . "'".$Keywords."'
			AND fixedassets.assetid " . LIKE . "'".$AssetID."'
			AND fixedassets.assetlocation " . LIKE . "'".$AssetLocation."'
			ORDER BY fixedassets.assetid";


	$Result = DB_query($SQL);
	echo '<br />';
	echo '<form action="'. htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') . '" method="post">
          <div>';
    echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';
	echo '<table class="selection">';
	echo '<tr>
			<th>' . __('Asset ID') . '</th>
			<th>' . __('Description') . '</th>
			<th>' . __('Serial number') . '</th>
			<th>' . __('Purchase Cost') . '</th>
			<th>' . __('Total Depreciation') . '</th>
			<th>' . __('Current Location') . '</th>
			<th colspan="2">' . __('Move To') . '</th>
		</tr>';

	$LocationSQL="SELECT locationid, locationdescription from fixedassetlocations";
	$LocationResult = DB_query($LocationSQL);

	while ($MyRow=DB_fetch_array($Result)) {

		echo '<tr>
				<td>' . $MyRow['assetid'] . '</td>
				<td>' . $MyRow['description'] . '</td>
				<td>' . $MyRow['serialno'] . '</td>
				<td class="number">' . locale_number_format($MyRow['cost'],$_SESSION['CompanyRecord']['decimalplaces']) . '</td>
				<td class="number">' . locale_number_format($MyRow['accumdepn'],$_SESSION['CompanyRecord']['decimalplaces']) . '</td>
				<td>' . $MyRow['ItemAssetLocation'] . '</td>';
		echo '<td><select name="Location' . $MyRow['assetid'] . '" onchange="ReloadForm(Move'.$MyRow['assetid'].')">';
		$ThisDropDownName	= 'Location' . $MyRow['assetid'];
		while ($LocationRow=DB_fetch_array($LocationResult)) {

			if(isset($_POST[$ThisDropDownName]) AND ($_POST[$ThisDropDownName] == $LocationRow['locationid'])) {
				echo '<option selected="selected" value="' . $LocationRow['locationid'].'">' . $LocationRow['locationdescription'] . '</option>';
			} elseif ($LocationRow['locationid'] == $MyRow['ItemAssetLocation']) {
				echo '<option selected="selected" value="'.$LocationRow['locationid'].'">' . $LocationRow['locationdescription'] . '</option>';
			} else {
				echo '<option value="'.$LocationRow['locationid'].'">' . $LocationRow['locationdescription'] . '</option>';
			}
		}
		DB_data_seek($LocationResult,0);
		echo '</select></td>';
		echo '<input type="hidden" name="AssetCat" value="' . $_POST['AssetCat'].'" />';
		echo '<input type="hidden" name="AssetLocation" value="' . $_POST['AssetLocation'].'" />';
		echo '<input type="hidden" name="Keywords" value="' . $_POST['Keywords'].'" />';
		echo '<input type="hidden" name="AssetID" value="' . $_POST['AssetID'].'" />';
		echo '<input type="hidden" name="Search" value="' . $_POST['Search'].'" />';
		echo '<td><input type="submit" name="Move'.$MyRow['assetid'].'" value="Move" /></td>';
		echo '</tr>';
	}
	echo '</table>
          </div>
          </form>';
}

include('includes/footer.php');

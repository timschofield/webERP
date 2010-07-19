<?php

$PageSecurity = 11;

include('includes/session.inc');

$title = _('Change Asset Location');

include('includes/header.inc');

foreach ($_POST as $key => $value) {
	if (substr($key,0,4)=='move') {
		$id=substr($key,4);
		$location=$_POST['location'.$id];
		$sql='UPDATE assetmanager
			SET location="'.$location.'"
			WHERE id='.$id;
		$result=DB_query($sql, $db);
	}
}

if (isset($_GET['AssetID'])) {
	$AssetID=$_GET['AssetID'];
} else if (isset($_POST['AssetID'])) {
	$AssetID=$_POST['AssetID'];
} else {
	$sql='SELECT categoryid, categorydescription FROM stockcategory WHERE stocktype="'.'A'.'"';
	$result=DB_query($sql, $db);
	echo '<form action="'. $_SERVER['PHP_SELF'] . '?' . SID .'" method=post>';
	echo '<p class="page_title_text"><img src="'.$rootpath.'/css/'.$theme.'/images/magnifier.png" title="' . _('Search') .
		'" alt="">' . ' ' . $title . '</p>';
	echo '<table class=selection><tr>';
	echo '<td>'. _('In Asset Category') . ': ';
	echo '<select name="StockCat">';

	if (!isset($_POST['StockCat'])) {
		$_POST['StockCat'] = "";
	}

	while ($myrow = DB_fetch_array($result)) {
		if ($myrow['categoryid'] == $_POST['StockCat']) {
			echo '<option selected VALUE="' . $myrow['categoryid'] . '">' . $myrow['categorydescription'];
		} else {
			echo '<option value="' . $myrow['categoryid'] . '">' . $myrow['categorydescription'];
		}
	}

	echo '</select>';
	echo '<td>'. _('Enter partial') . '<b> ' . _('Description') . '</b>:</td><td>';


	if (isset($_POST['Keywords'])) {
		echo '<input type="text" name="Keywords" value="' . trim($_POST['Keywords'],'%') . '" size=20 maxlength=25>';
	} else {
		echo '<input type="text" name="Keywords" size=20 maxlength=25>';
	}

	echo '</td></tr><tr><td></td>';

	echo '<td><font size 3><b>' . _('OR').' ' . '</b></font>' . _('Enter partial') .' <b>'. _('Stock Code') . '</b>:</td>';
	echo '<td>';

	if (isset($_POST['StockCode'])) {
		echo '<input type="text" name="StockCode" value="'. trim($_POST['StockCode'],'%') . '" size=15 maxlength=18>';
	} else {
		echo '<input type="text" name="StockCode" size=15 maxlength=18>';
	}

	echo '<tr><td></td>';

	echo '<td><font size 3><b>' . _('OR').' ' . '</font></b>' . _('Enter partial').' <b>'. _('Serial Number') . '</b>:</td>';
	echo '<td>';

	if (isset($_POST['StockCode'])) {
		echo '<input type="text" name="SerialNumber" value="'. trim($_POST['SerialNumber'],'%') . '" size=15 maxlength=18>';
	} else {
		echo '<input type="text" name="SerialNumber" size=15 maxlength=18>';
	}

	echo '</td></tr></table><br>';

	echo '<div class="centre"><input type=submit name="Search" value="'. _('Search Now') . '"></div></form><br>';
}
	if (isset($_POST['Search'])) {
		if ($_POST['StockCat']=='All') {
			$_POST['StockCat']='%';
		}
		if (isset($_POST['Keywords'])) {
			$_POST['Keywords']='%'.$_POST['Keywords'].'%';
		} else {
			$_POST['Keywords']='%';
		}
		if (isset($_POST['StockCode'])) {
			$_POST['StockCode']='%'.$_POST['StockCode'].'%';
		} else {
			$_POST['StockCode']='%';
		}
		if (isset($_POST['SerialNumber'])) {
			$_POST['SerialNumber']='%'.$_POST['SerialNumber'].'%';
		} else {
			$_POST['SerialNumber']='%';
		}
		$sql= 'SELECT assetmanager.*,stockmaster.description, fixedassetlocations.locationdescription
				FROM assetmanager
				LEFT JOIN stockmaster
				ON assetmanager.stockid=stockmaster.stockid
				LEFT JOIN fixedassetlocations
				ON assetmanager.location=fixedassetlocations.locationid
				WHERE stockmaster.categoryid like "'.$_POST['StockCat'].'"
				AND stockmaster.description like "'.$_POST['Keywords'].'"
				AND assetmanager.stockid like "'.$_POST['StockCode'].'"
				AND assetmanager.serialno like "'.$_POST['SerialNumber'].'"';
		$result=DB_query($sql, $db);
		echo '<form action="'. $_SERVER['PHP_SELF'] . '?' . SID .'" method=post><table class=selection>';
		echo '<tr><th>'._('Asset ID').'</th>
				<th>'._('Stock Code').'</th>
				<th>'._('Description').'</th>
				<th>'._('Serial number').'</th>
				<th>'._('Purchase Cost').'</th>
				<th>'._('Total Depreciation').'</th>
				<th>'._('Current Location').'</th>
				<th>'._('Move To :').'</th>
				</tr>';
		while ($myrow=DB_fetch_array($result)) {
			$locationsql='select * from fixedassetlocations';
			$locationresult=DB_query($locationsql, $db);
			echo '<tr><td>'.$myrow['id'].'</td>';
			echo '<td>'.$myrow['stockid'].'</td>';
			echo '<td>'.$myrow['description'].'</td>';
			echo '<td>'.$myrow['serialno'].'</td>';
			echo '<td class=number>'.number_format($myrow['cost'],2).'</td>';
			echo '<td class=number>'.number_format($myrow['depn'],2).'</td>';
			echo '<td>'.$myrow['locationdescription'].'</td>';
			echo '<td><select name="location'.$myrow['id'].'" onChange="ReloadForm(move'.$myrow['id'].')">';
			echo '<option></option>';
			while ($locationrow=DB_fetch_array($locationresult)) {
				if ($locationrow['locationid']==$myrow['location']) {
					echo '<option selected value="'.$locationrow['locationid'].'">'.$locationrow['locationdescription'].
						'</option>';
				} else {
					echo '<option value="'.$locationrow['locationid'].'">'.$locationrow['locationdescription'].'</option>';
				}
			}
			echo '</select></td>';
			echo '<input type=hidden name=StockCat value="' . $_POST['StockCat'].'"';
			echo '<input type=hidden name=Keywords value="' . $_POST['Keywords'].'"';
			echo '<input type=hidden name=StockCode value="' . $_POST['StockCode'].'"';
			echo '<input type=hidden name=SerialNumber value="' . $_POST['SerialNumber'].'"';
			echo '<input type=hidden name=Search value="' . $_POST['Search'].'"';
			echo '<td><input type=submit name="move'.$myrow['id'].'" value=Move></td>';
			echo '</tr>';
		}
		echo '</table></form>';
	}
//}

include('includes/footer.inc');

?>
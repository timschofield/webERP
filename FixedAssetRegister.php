<?php

$PageSecurity = 11;

include('includes/session.inc');

$title = _('Fixed Asset Register');

include('includes/header.inc');
echo '<p class="page_title_text"><img src="'.$rootpath.'/css/'.$theme.'/images/magnifier.png" title="' .
	 _('Search') . '" alt="">' . ' ' . $title;
	
$sql = "SELECT * FROM stockcategory WHERE stocktype='".'A'."'";
$result = DB_query($sql,$db);
	 
echo '<form name="RegisterForm" method="post" action="' . $_SERVER['PHP_SELF'] . '?' . SID . '"><table>';
echo '<tr><th>'._('Asset Category').'</th>';
echo '<td><select name=assetcategory>';
echo '<option value="%">ALL</option>';
while ($myrow=DB_fetch_array($result)) {
	if (isset($_POST['assetcategory']) and $myrow['categoryid']==$_POST['assetcategory']) {
		echo '<option selected value='.$myrow['categoryid'].'>'.$myrow['categorydescription'].'</option>';
	} else {
		echo '<option value='.$myrow['categoryid'].'>'.$myrow['categorydescription'].'</option>';
	}
}
echo '</select></td></tr>';
	
$sql = "SELECT  locationid, locationdescription FROM fixedassetlocations";
$result = DB_query($sql,$db);

echo '<tr><th>'._('Asset Location').'</th>';
echo '<td><select name=assetlocation>';
echo '<option value="All">ALL</option>';
while ($myrow=DB_fetch_array($result)) {
	if (isset($_POST['assetlocation']) and $myrow['locationdescription']==$_POST['assetlocation']) {
		echo '<option selected value="'.$myrow['locationdescription'].'">'.$myrow['locationdescription'].'</option>';
	} else {
		echo '<option value="'.$myrow['locationdescription'].'">'.$myrow['locationdescription'].'</option>';
	}
}
echo '</select></td></tr>';
	
$sql = "SELECT stockid, description FROM stockmaster LEFT JOIN stockcategory ON stockmaster.categoryid=stockcategory.categoryid WHERE stocktype='A'";
$result = DB_query($sql,$db);

echo '<tr><th>'._('Asset Type').'</th>';
echo '<td><select name=assettype>';
echo '<option value="%">ALL</option>';
while ($myrow=DB_fetch_array($result)) {
	if (isset($_POST['assettype']) and $myrow['stockid']==$_POST['assettype']) {
		echo '<option selected value='.$myrow['stockid'].'>'.$myrow['description'].'</option>';
	} else {
		echo '<option value='.$myrow['stockid'].'>'.$myrow['description'].'</option>';
	}
}

echo '</select></td></tr>';

//FULUSI CHANGE BELOW TO ADD TIME 
echo '<tr>
                <th>'._(' From Date')."</th>
                <td><input type='text' class='date' alt='".$_SESSION['DefaultDateFormat'].
                        "' name='fromDate' maxlength=10 size=11 value='".$_POST['fromDate']."'></td>";

echo '</tr>';
echo '<tr>
                <th>'._('To Date ')."</th>
                 <td><input type='text' class='date' alt='".$_SESSION['DefaultDateFormat'].
                         "' name='toDate' maxlength=10 size=11 value='".$_POST['toDate']."'></td>";

echo '</tr>';
//end of FULUSI STUFF 

echo '</table><br>';

echo '<div class="centre"><input type="Submit" name="submit" value="' . _('Show Assets') . '"></div>';

echo '</form>';

if (isset($_POST['submit'])) {
        $dateFrom = FormatDateForSQL($_POST['fromDate']);
        $dateTo = FormatDateForSQL($_POST['toDate']);

	
	$sql='SELECT assetmanager.id, 
			assetmanager.stockid,
			stockmaster.longdescription,
			stockmaster.categoryid,
			assetmanager.serialno,
			fixedassetlocations.locationdescription, 
			assetmanager.cost, 
			assetmanager.datepurchased,
			assetmanager.depn,
			assetmanager.disposalvalue,
			fixedassetlocations.parentlocationid,
			assetmanager.location
			FROM assetmanager 
		LEFT JOIN stockmaster ON assetmanager.stockid=stockmaster.stockid 
		LEFT JOIN stockcategory ON stockmaster.categoryid=stockcategory.categoryid 
		LEFT JOIN fixedassetlocations ON assetmanager.location=fixedassetlocations.locationid
		WHERE stockmaster.categoryid like "'.$_POST['assetcategory'].'"
		AND stockmaster.stockid like "'.$_POST['assettype'].'"
		AND assetmanager.datepurchased BETWEEN "'.$dateFrom.'" AND "'.$dateTo.'"';
	$result=DB_query($sql, $db);

	echo '<br><table width=80% cellspacing="9"><tr>';
	echo '<th colspan=6></th>';
	echo '<th colspan=3>'._('External Depreciation').'</th>';
	echo '<th colspan=3>'._('Internal Depreciation').'</th><th></th></tr><tr>';
	echo '<th>'._('Asset ID').'</th>';
	echo '<th>'._('Stock ID').'</th>';
	echo '<th>'._('Description').'</th>';
	echo '<th>'._('Serial Number').'</th>';
	echo '<th>'._('Location').'</th>';
	echo '<th>'._('Date Acquired').'</th>';
	echo '<th>'._('Cost').'</th>';
	echo '<th>'._('Depreciation').'</th>';
	echo '<th>'._('NBV').'</th>';
	echo '<th>'._('Cost').'</th>';
	echo '<th>'._('Depreciation').'</th>';
	echo '<th>'._('NBV').'</th>';
	echo '<th>'._('Disposal Value').'</th></tr>';

	while ($myrow=DB_fetch_array($result)) {
		
		$ancestors=array();
		$ancestors[0]=$myrow['locationdescription'];
		$i=0;
		while ($ancestors[$i]!='') {
			$locationsql='SELECT parentlocationid from fixedassetlocations where locationdescription="'.$ancestors[$i].'"';
			$locationresult=DB_query($locationsql, $db);
			$locationrow=DB_fetch_array($locationresult);
			$parentsql='SELECT locationdescription from fixedassetlocations where locationid="'.$locationrow['parentlocationid'].'"';
			$parentresult=DB_query($parentsql, $db);
			$parentrow=DB_fetch_array($parentresult);
			$i++;
			$ancestors[$i]=$parentrow['locationdescription'];
		}
		$catidsql='SELECT stkcatpropid FROM stockcatproperties WHERE categoryid="'.$myrow['categoryid']
			.'" AND label="'. _('Annual Internal Depreciation Percentage').'"';
		$catidresult=DB_query($catidsql, $db);
		$catidrow=DB_fetch_array($catidresult);
		$catvaluesql='SELECT value FROM stockitemproperties WHERE stockid="'.$myrow['stockid'].'" AND stkcatpropid='.
			$catidrow['stkcatpropid'];
		$catvalueresult=DB_query($catvaluesql, $db);
		$catvaluerow=DB_fetch_array($catvalueresult);
		$MonthsOld=DateDiff(date('d/m/Y'),ConvertSQLDate($myrow['datepurchased']),  'm');
		$InternalDepreciation=$myrow['cost']*$catvaluerow['value']/100*$MonthsOld/12;
		if (($InternalDepreciation+$myrow['disposalvalue'])>$myrow['cost']) {
			$InternalDepreciation=$myrow['cost']-$myrow['disposalvalue'];
		}

		if (in_array($_POST['assetlocation'],$ancestors) or $_POST['assetlocation']=='All') {
		
			echo '<tr><td style="vertical-align:top">'.$myrow['id'].'</td>';
			echo '<td style="vertical-align:top">'.$myrow['stockid'].'</td>';
			echo '<td style="vertical-align:top">'.$myrow['longdescription'].'</td>';
			echo '<td style="vertical-align:top">'.$myrow['serialno'].'</td>';
			echo '<td>'.$myrow['locationdescription'].'<br>';
			for ($i=1;$i<sizeOf($ancestors)-1;$i++) {
				for ($j=0;$j<$i; $j++) {
					echo '&nbsp;&nbsp;&nbsp;&nbsp;';
				}
				echo '|_'.$ancestors[$i].'<br>';
			}
			echo '</td><td style="vertical-align:top">'.ConvertSQLDate($myrow['datepurchased']).'</td>';
			echo '<td style="vertical-align:top" class=number>'.number_format($myrow['cost'],2).'</td>';
			echo '<td style="vertical-align:top" class=number>'.number_format($myrow['depn'],2).'</td>';
			echo '<td style="vertical-align:top" class=number>'.number_format($myrow['cost']-$myrow['depn'],2).'</td>';
			echo '<td style="vertical-align:top" class=number>'.number_format($myrow['cost'],2).'</td>';
			echo '<td style="vertical-align:top" class=number>'.number_format($InternalDepreciation,2).'</td>';
			echo '<td style="vertical-align:top" class=number>'.number_format($myrow['cost']-$InternalDepreciation,2).'</td>';
			echo '<td style="vertical-align:top" class=number>'.number_format($myrow['disposalvalue'],2).'</td></tr>';
		}
	}
}

include('includes/footer.inc');

?>

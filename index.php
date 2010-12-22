<?php

/* $Id$*/

/* $Revision: 1.91 $ */

// $PageSecurity = 1;

include('includes/session.inc');
$title=_('Main Menu');
include('includes/header.inc');
echo '<link href="' . $rootpath . '/css/'. $_SESSION['Theme'] .'/menu.css" rel="stylesheet" type="text/css" />';

$sql="SELECT * FROM menu";
$result=DB_query($sql, $db);

$i=0;
while ($myrow=DB_fetch_array($result)) {
	$MenuStructure[$i]=$myrow;
	$i++;
}

foreach ($_POST as $key => $value) {
	if (substr($key, 0, 6)=='Update') {
		$ReportletID=substr($key, 6, strlen($key)-6);

		$sql="UPDATE reportlets SET refresh='".$_POST['RefreshRate'.$ReportletID]."' WHERE userid='".$_SESSION['UserID']."' AND id='".$ReportletID."'";
		$result=DB_query($sql, $db);
	}
}

$sql="SELECT * FROM reportlets WHERE userid='".$_SESSION['UserID']."'";
$result=DB_query($sql, $db);
$j=1;

$RefreshTimes=array(0, 60, 300, 600, 1800, 3600);
$RefreshValues=array('never', '1 Min', '5 mins', '10 Mins', '30 Mins', '60 Mins');

echo '<table style="position: relative; width: 75%; margin-top: 10px;margin-left: 200px;">';
echo '<tr>';
while ($myrow=DB_fetch_array($result)) {
	$RefreshRate=$myrow['refresh'];
	echo '<td style="height: 150px">';
	echo '<form method="post" id="headerForm" action="' . $_SERVER['PHP_SELF'] . '?' . SID . '">';
	echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';
	echo '<table style="border: 1px solid; width:100%; height:100%" >';
	echo '<tr><th style="font-size: 12px;color: navy; width: 68%">'.$myrow['title'].'</th><th>'._('Refresh');
	echo ':<select name="RefreshRate'.$myrow['id'].'" onChange="ReloadForm(Update'.$myrow['id'].')">';
	for ($i=0; $i<sizeOf($RefreshTimes); $i++) {
		if ($myrow['refresh']==$RefreshTimes[$i]) {
			echo '<option selected="True" value="'.$RefreshTimes[$i].'">'.$RefreshValues[$i].'</option>';
		} else {
			echo '<option value="'.$RefreshTimes[$i].'">'.$RefreshValues[$i].'</option>';
		}
	}
	echo '</select>';
	echo '<input type="submit" name="Update'.$myrow['id'].'" value="Go" />';
	echo '</th></tr>';
	echo '<tr><td colspan="2"><iframe frameborder="0" height="100%" width="100%" src="dashboard/ReportletContainer.php?Reportlet='.$myrow['id'].'&amp;Refresh='.$RefreshRate.'"></iframe>';
	echo '</td></tr></table></form></td>';
	if (($j % 2) == 0) echo '</tr><tr>';
	$j++;
}
echo '</tr></table>';

echo "<div style='margin-left: 10px; position: absolute; top:50px; z-index:99;'><ul class='makeMenu'>";
$ModuleID=0;
for ($i=0; $i<sizeOf($MenuStructure); $i++) {
	if ($MenuStructure[$i]['parent']==-1) {
		if ($_SESSION['ModulesEnabled'][$ModuleID]==1) {
			echo '<li><a href="'.$MenuStructure[$i]['href'].'">'._($MenuStructure[$i]['caption']).'</a>';
			$parent=$MenuStructure[$i]['id'];
			echo "<ul>";
			for ($j=0; $j<sizeOf($MenuStructure); $j++) {
				if ($MenuStructure[$j]['parent']==$parent) {
					$sql="SELECT count(id)
						FROM menu
						LEFT JOIN usermenurights
						ON menu.id=usermenurights.menuid
						WHERE parent='".$MenuStructure[$j]['id']."'
							AND usermenurights.userid='".$_SESSION['UserID']."'
							AND usermenurights.access=1";
					$result=DB_query($sql, $db);
					$myrow=DB_fetch_row($result);
					if ($myrow[0]!=0) {
						echo '<li><a href="'.$MenuStructure[$j]['href'].'">'._($MenuStructure[$j]['caption']).'</a>';
						$parent1=$MenuStructure[$j]['id'];
						echo "<ul>";
						for ($k=0; $k<sizeOf($MenuStructure); $k++) {
							if ($MenuStructure[$k]['parent']==$parent1 and $_SESSION['MenuAccess'][$MenuStructure[$k]['id']]==1) {
								echo '<li><a href="'.$MenuStructure[$k]['href'].'">'._($MenuStructure[$k]['caption']).'</a>';
								echo '</li>';
							}
						}
						echo '</ul>';
						echo '</li>';
					}
				}
			}
			echo '</ul>';
			echo '</li>';
		}
		$ModuleID++;
	}
}
echo "</ul></div>";
include('includes/footer.inc');
?>
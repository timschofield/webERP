<?php
/**
 * Author: Ashish Shukla <gmail.com!wahjava>
 *
 * Script to duplicate BoMs.
 */
/* $Id$*/

include('includes/session.inc');

$title = _('Copy a BOM to New Item Code');

include('includes/header.inc');

include('includes/SQL_CommonFunctions.inc');

if(isset($_POST['Submit'])) {
	$stkid = $_POST['stkid'];
	$type = $_POST['type'];
	$newstkid = '';

	if($type == 'N') {
		$newstkid = $_POST['tostkid'];
	} else {
		$newstkid = $_POST['exstkid'];
	}

	$result = DB_query("begin", $db);

	if($type == 'N') {
      /* duplicate rows into stockmaster */
		$sql = "INSERT INTO stockmaster
						SELECT '".$newstkid."' AS stockid,
								categoryid,
								description,
								longdescription,
								units,
								mbflag,
								lastcurcostdate,
								actualcost,
								lastcost,
								materialcost,
								labourcost,
								overheadcost,
								lowestlevel,
								discontinued,
								controlled,
								eoq,
								volume,
								kgs,
								barcode,
								discountcategory,
								taxcatid,
								serialised,
								appendfile,
								perishable,
								decimalplaces,
								nextserialno,
								pansize,
								shrinkfactor,
								netweight
						FROM stockmaster
						WHERE stockid='".$stkid."';";
		$result = DB_query($sql, $db);
	} else {
		$sql = "SELECT lastcurcostdate,
						actualcost,
						lastcost,
						materialcost,
						labourcost,
						overheadcost,
						lowestlevel
					FROM stockmaster
					WHERE stockid='".$stkid."';";
		$result = DB_query($sql, $db);

		$row = DB_fetch_row($result);

		$sql = "UPDATE stockmaster set
				lastcurcostdate = '".$row[0]."',
				actualcost      = ".$row[1].",
				lastcost        = ".$row[2].",
				materialcost    = ".$row[3].",
				labourcost      = ".$row[4].",
				overheadcost    = ".$row[5].",
				lowestlevel     = ".$row[6]."
				WHERE stockid='".$newstkid."';";
		$result = DB_query($sql, $db);
	}

	$sql = "INSERT INTO bom
				SELECT '".$newstkid."' AS parent,
						component,
						workcentreadded,
						loccode,
						effectiveafter,
						effectiveto,
						quantity,
						autoissue
				FROM bom
				WHERE parent='".$stkid."';";
	$result = DB_query($sql, $db);

	if($type == 'N') {
		$sql = "INSERT INTO locstock
	      SELECT loccode,
				'".$newstkid."' AS stockid,
				0 AS quantity,
				reorderlevel
			FROM locstock
			WHERE stockid='".$stkid."';";
		$result = DB_query($sql, $db);
	}

	$result = DB_query('commit', $db);

	UpdateCost($db, $newstkid);

	header('Location: BOMs.php?Select='.$newstkid);
} else {

	echo '<p class="page_title_text"><img src="'.$rootpath.'/css/'.$theme.'/images/inventory.png" title="' . _('Contract') . '" alt="" />' . ' ' . $title . '</p>';

	echo '<form method="post" action="' . htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') . '">';
    echo '<div>';
	echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';

	$sql = "SELECT stockid,
					description
				FROM stockmaster
				WHERE stockid IN (SELECT DISTINCT parent FROM bom)
				AND  mbflag IN ('M', 'A', 'K');";
	$result = DB_query($sql, $db);

	echo '<table class="selection">
			<tr>
				<td>' . _('From Stock ID') . '</td>';
	echo '<td><select name="stkid">';
	while($row = DB_fetch_row($result)) {
		echo '<option value="'.$row[0].'">'.$row[0].' -- '.$row[1].'</option>';
	}
	echo '</select></td>
			</tr>';
	echo '<tr>
			<td><input type="radio" name="type" value="N" />' . _(' To New Stock ID') . '</td>';
	echo '<td><input type="text" maxlength="20" name="tostkid" /></td></tr>';

	$sql = "SELECT stockid,
					description
				FROM stockmaster
				WHERE stockid NOT IN (SELECT DISTINCT parent FROM bom)
				AND mbflag IN ('M', 'A', 'K');";
	$result = DB_query($sql, $db);

	if (DB_num_rows($result) > 0) {
		echo '<tr>
				<td><input type="radio" name="type" value="E" />'._('To Existing Stock ID') . '</td>';
		echo '<td><select name="exstkid">';
		while($row = DB_fetch_row($result)) {
			echo '<option value="'.$row[0].'">'.$row[0].' -- '.$row[1].'</option>';
		}
		echo '</select></td>';
        echo '</tr>';
	}
	echo '</table>';
	echo '<br /><div class="centre"><input type="submit" name="Submit" value="Submit" /></div>
         </div>
         </form>';

	include('includes/footer.inc');
}
?>
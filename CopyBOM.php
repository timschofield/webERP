<?php
/**
 * Author: Ashish Shukla <gmail.com!wahjava>
 *
 * Script to duplicate BoMs.
 */
/* $Id: CopyBOM.php 6310 2013-08-29 10:42:50Z daintree $*/

include('includes/session.inc');

$Title = _('Copy a BOM to New Item Code');

include('includes/header.inc');

include('includes/SQL_CommonFunctions.inc');

if(isset($_POST['Submit'])) {
	$StockID = $_POST['StockID'];
	$NewOrExisting = $_POST['NewOrExisting'];
	$NewStockID = '';
	$InputError = 0; //assume the best

	if($NewOrExisting == 'N') {
		$NewStockID = $_POST['ToStockID'];
		if (mb_strlen($NewStockID)==0 OR $NewStockID==''){
			$InputError = 1;
			prnMsg(_('The new item code cannot be blank. Enter a new code for the item to copy the BOM to'),'error');
		}
	} else {
		$NewStockID = $_POST['ExStockID'];
	}
	if ($InputError==0){
		$result = DB_Txn_Begin($db);

		if($NewOrExisting == 'N') {
	      /* duplicate rows into stockmaster */
			$sql = "INSERT INTO stockmaster( stockid,
									categoryid,
									description,
									longdescription,
									units,
									mbflag,
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
									grossweight,
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
									netweight )
							SELECT '".$NewStockID."' AS stockid,
									categoryid,
									description,
									longdescription,
									units,
									mbflag,
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
									grossweight,
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
							WHERE stockid='".$StockID."';";
			$result = DB_query($sql, $db);
		} else {
			$sql = "SELECT lastcostupdate,
							actualcost,
							lastcost,
							materialcost,
							labourcost,
							overheadcost,
							lowestlevel
						FROM stockmaster
						WHERE stockid='".$StockID."';";
			$result = DB_query($sql, $db);

			$myrow = DB_fetch_row($result);

			$sql = "UPDATE stockmaster set
					lastcostupdate  = " . $myrow[0] . ",
					actualcost      = " . $myrow[1] . ",
					lastcost        = " . $myrow[2] . ",
					materialcost    = " . $myrow[3] . ",
					labourcost      = " . $myrow[4] . ",
					overheadcost    = " . $myrow[5] . ",
					lowestlevel     = " . $myrow[6] . "
					WHERE stockid='".$NewStockID."';";
			$result = DB_query($sql, $db);
		}

		$sql = "INSERT INTO bom
					SELECT '".$NewStockID."' AS parent,
							component,
							workcentreadded,
							loccode,
							effectiveafter,
							effectiveto,
							quantity,
							autoissue
					FROM bom
					WHERE parent='".$StockID."';";
		$result = DB_query($sql, $db);

		if($NewOrExisting == 'N') {
			$sql = "INSERT INTO locstock
		      SELECT loccode,
					'".$NewStockID."' AS stockid,
					0 AS quantity,
					reorderlevel
				FROM locstock
				WHERE stockid='".$StockID."'";

			$result = DB_query($sql, $db);
		}

		$result = DB_Txn_Commit($db);

		UpdateCost($db, $NewStockID);

		header('Location: BOMs.php?Select='.$NewStockID);
		ob_end_flush();
	} //end  if there is no input error
} else {

	echo '<p class="page_title_text"><img src="'.$RootPath.'/css/'.$Theme.'/images/inventory.png" title="' . _('Contract') . '" alt="" />' . ' ' . $Title . '</p>';

	echo '<form method="post" action="' . htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') . '">';
    echo '<div>';
	echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';

	$sql = "SELECT stockid,
					description
				FROM stockmaster
				WHERE stockid IN (SELECT DISTINCT parent FROM bom)
				AND  mbflag IN ('M', 'A', 'K', 'G');";
	$result = DB_query($sql, $db);

	echo '<table class="selection">
			<tr>
				<td>' . _('From Stock ID') . '</td>';
	echo '<td><select name="StockID">';
	while($myrow = DB_fetch_row($result)) {
		echo '<option value="'.$myrow[0].'">' . $myrow[0].' -- '.$myrow[1] . '</option>';
	}
	echo '</select></td>
			</tr>';
	echo '<tr>
			<td><input type="radio" name="NewOrExisting" value="N" />' . _(' To New Stock ID') . '</td>';
	echo '<td><input type="text" maxlength="20" autofocus="autofocus" pattern="[a-zA-Z0-9_\-]*" name="ToStockID" title="' . _('Enter a new item code to copy the existing item and its bill of material to. Item codes can contain only alpha-numeric characters, underscore or hyphens.') . '" /></td></tr>';

	$sql = "SELECT stockid,
					description
				FROM stockmaster
				WHERE stockid NOT IN (SELECT DISTINCT parent FROM bom)
				AND mbflag IN ('M', 'A', 'K', 'G');";
	$result = DB_query($sql, $db);

	if (DB_num_rows($result) > 0) {
		echo '<tr>
				<td><input type="radio" name="NewOrExisting" checked="checked" value="E" />' . _('To Existing Stock ID') . '</td><td>';
		echo '<select name="ExStockID">';
		while($myrow = DB_fetch_row($result)) {
			echo '<option value="'.$myrow[0].'">' . $myrow[0].' -- '.$myrow[1] . '</option>';
		}
		echo '</select></td></tr>';
	}
	echo '</table>';
	echo '<br /><div class="centre"><input type="submit" name="Submit" value="Submit" /></div>
          </div>
          </form>';

	include('includes/footer.inc');
}
?>

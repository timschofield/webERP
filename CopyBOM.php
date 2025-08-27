<?php

/**
 * Author: Ashish Shukla <gmail.com!wahjava>
 *
 * Script to duplicate BoMs.
 */

require(__DIR__ . '/includes/session.php');

include('includes/SQL_CommonFunctions.php');

$Title = __('Copy a BOM to New Item Code');
$ViewTopic = 'Manufacturing';
$BookMark = '';

ob_start();
include('includes/header.php');

if (isset($_POST['Submit'])) {
	$StockID = $_POST['StockID'];
	$NewOrExisting = $_POST['NewOrExisting'];
	$NewStockID = '';
	$InputError = 0; //assume the best

	if($NewOrExisting == 'N') {
		$NewStockID = $_POST['ToStockID'];
		if (mb_strlen($NewStockID)==0 OR $NewStockID==''){
			$InputError = 1;
			prnMsg(__('The new item code cannot be blank. Enter a new code for the item to copy the BOM to'),'error');
		}
	} else {
		$NewStockID = $_POST['ExStockID'];
	}
	if ($InputError==0) {
		DB_Txn_Begin();

		if($NewOrExisting == 'N') {
	      /* duplicate rows into stockmaster */
			$SQL = "INSERT INTO stockmaster( stockid,
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
									perishable,
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
									perishable,
									nextserialno,
									pansize,
									shrinkfactor,
									netweight
							FROM stockmaster
							WHERE stockid='".$StockID."';";
			$Result = DB_query($SQL);
		} else {
			$SQL = "SELECT lastcostupdate,
							actualcost,
							lastcost,
							materialcost,
							labourcost,
							overheadcost,
							lowestlevel
						FROM stockmaster
						WHERE stockid='".$StockID."';";
			$Result = DB_query($SQL);

			$MyRow = DB_fetch_row($Result);

			$SQL = "UPDATE stockmaster set
					lastcostupdate  = '" . $MyRow[0] . "',
					actualcost      = " . $MyRow[1] . ",
					lastcost        = " . $MyRow[2] . ",
					materialcost    = " . $MyRow[3] . ",
					labourcost      = " . $MyRow[4] . ",
					overheadcost    = " . $MyRow[5] . ",
					lowestlevel     = " . $MyRow[6] . "
					WHERE stockid='".$NewStockID."';";
			$Result = DB_query($SQL);
		}

		$SQL = "INSERT INTO bom
					SELECT '".$NewStockID."' AS parent,
					        sequence,
							component,
							workcentreadded,
							loccode,
							effectiveafter,
							effectiveto,
							quantity,
							autoissue,
							remark,
							digitals
					FROM bom
					WHERE parent='".$StockID."';";
		$Result = DB_query($SQL);

		if($NewOrExisting == 'N') {
			$SQL = "INSERT INTO locstock (loccode,
								            stockid,
								            quantity,
								            reorderlevel,
								            bin )
				      SELECT loccode,
							'".$NewStockID."' AS stockid,
							0 AS quantity,
							reorderlevel,
							bin
						FROM locstock
						WHERE stockid='".$StockID."'";

			$Result = DB_query($SQL);
		}

		DB_Txn_Commit();

		UpdateCost($NewStockID);

		header('Location: ' . htmlspecialchars_decode($RootPath) . '/BOMs.php?Select='.urlencode(htmlspecialchars_decode($NewStockID)));
		//ob_end_flush();
		exit();
	} //end  if there is no input error

	/// @todo what to display if there is an input error?
} else {
	ob_end_flush();

	echo '<p class="page_title_text"><img src="'.$RootPath.'/css/'.$Theme.'/images/inventory.png" title="' . __('Contract') . '" alt="" />' . ' ' . $Title . '</p>';

	echo '<form method="post" action="' . htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') . '">';
	echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';

	$SQL = "SELECT stockid,
					description
				FROM stockmaster
				WHERE stockid IN (SELECT DISTINCT parent FROM bom)
				AND  mbflag IN ('M', 'A', 'K', 'G');";
	$Result = DB_query($SQL);

	echo '<fieldset>
			<legend>', __('Copy Criteria'), '</legend>';

	echo '<field>
			<label for="StockID">', __('From Stock ID'), '</label>
			<select name="StockID">';
	while ($MyRow = DB_fetch_row($Result)) {
		if (isset($_GET['Item']) and $MyRow[0] == $_GET['Item']) {
			echo '<option selected="selected" value="', $MyRow[0], '">', $MyRow[0], ' -- ', $MyRow[1], '</option>';
		} else {
			echo '<option value="', $MyRow[0], '">', $MyRow[0], ' -- ', $MyRow[1], '</option>';
		}
	}
	echo '</select>
		</field>';

	echo '<field>
			<label for="ToStockID"><input type="radio" name="NewOrExisting" value="N" />', __(' To New Stock ID'), '</label>
			<input type="text" required="required" maxlength="20" name="ToStockID" />
		</field>';

	$SQL = "SELECT stockid,
					description
				FROM stockmaster
				WHERE stockid NOT IN (SELECT DISTINCT parent FROM bom)
				AND mbflag IN ('M', 'A', 'K', 'G');";
	$Result = DB_query($SQL);
	if (DB_num_rows($Result) > 0) {

		echo '<field>
				<label for="NewOrExisting"><input type="radio" name="NewOrExisting" value="E" />', '<b>' , __('OR') , ' </b>' , __('To Existing Stock ID'), '</label>';
		echo '<select name="ExStockID">';
		while ($MyRow = DB_fetch_row($Result)) {
			echo '<option value="', $MyRow[0], '">', $MyRow[0], ' -- ', $MyRow[1], '</option>';
		}
		echo '</select>
			</field>';
	}
	echo '</fieldset>';
	echo '<div class="centre">
			<input type="submit" name="Submit" value="Submit" />
		</div>
	</form>';

	include('includes/footer.php');
}

<?php

include ('includes/session.php');
$Title = _('Copy all reorder levels from one location to another');// Screen identificator.
include('includes/header.php');
echo '<p class="page_title_text"><img alt="" src="', $RootPath, '/css/', $Theme,
	'/images/maintenance.png" title="',// Icon image.
	$Title, '" /> ',// Icon title.
	$Title, '</p>';// Page title.

include('includes/SQL_CommonFunctions.php');
include('includes/KLGeneralFunctions.php');
include('includes/UIGeneralFunctions.php'); // Add this line to include UI functions
include('includes/KLUIGeneralFunctions.php');

if(isset($_POST['ProcessCopyAuthority'])) {

	$InputError =0;
	
	if($_POST['FromLocationID']==$_POST['ToLocationID']) {
		prnMsg(_('Location FROM must be different from location TO'),'error');
		$InputError =1;
	}
	
	if($InputError ==0) {// no input errors
		$Result = DB_Txn_Begin();

		$SQL = "UPDATE locstock SET reorderlevel = 0 WHERE loccode = '" . $_POST['ToLocationID'] . "'";
		$DbgMsg = _('The SQL statement that failed was');
		$ErrMsg =_('The SQL to set RL = 0 at location TO failed');
		$Result = DB_query($SQL,$ErrMsg,$DbgMsg,true);

		
		$SQL = "SELECT stockid,
					reorderlevel
				FROM locstock
				WHERE loccode = '". $_POST['FromLocationID'] ."'
					AND reorderlevel > 0
				ORDER BY stockid";
				
		$Result = DB_query($SQL);
		if (DB_num_rows($Result) != 0){
			$TableTitleText = _('Reorder Levels Assigned to Location ') . $_POST['ToLocationID'];
			ShowTableTitle($TableTitleText);
			echo '<div>';
			echo '<table class="selection">
					<thead>
						<tr>
							<th class="SortedColumn">' . _('#') . '</th>
							<th class="SortedColumn">' . _('Item Code') . '</th>
							<th class="SortedColumn">' . _('Reorder Level') . '</th>
						</tr>
					</thead>
					<tbody>';
			$i = 1;
			while ($MyRow = DB_fetch_array($Result)) {
				
				$SQL = "UPDATE locstock 
						SET reorderlevel = '". $MyRow['reorderlevel']. "' 
						WHERE stockid = '" . $MyRow['stockid'] . "'
							AND loccode = '" . $_POST['ToLocationID'] . "'";
				$DbgMsg = "The SQL statement that failed was";
				$ErrMsg = "The SQL to set RL to item " . $MyRow['stockid'] . " at location '".  $_POST['ToLocationID'] ."' failed";
				$Resultitem = DB_query($SQL,$ErrMsg,$DbgMsg,true);

				$CodeLink = '<a href="' . $RootPath . '/SelectProduct.php?StockID=' . $MyRow['stockid'] . '">' . $MyRow['stockid'] . '</a>';
				echo '<tr class="striped_row">
						<td class="number">' . $i . '</td>
						<td>' . $CodeLink . '</td>
						<td class="number">' . locale_number_format($MyRow['reorderlevel'],0) . '</td>
						</tr>';
				$i++;
			}
			echo '</tbody>
				  </table>
				  </div>';
		}
		$Result = DB_Txn_Commit();

	}//only do the stuff above if  $InputError==0
}

echo '<form action="' . htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') .  '" method="post">';
echo '<div class="centre">';
echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';

echo '<fieldset>';
echo FieldToSelectOneLocation("FromLocationID", $_POST['FromLocationID'], _('Select Location to copy the Reorder Levels FROM'), '', 'CANVIEW', 1, true, false);
echo FieldToSelectOneLocation("ToLocationID", $_POST['ToLocationID'], _('Select Location to copy the Reorder Levels TO'), '', 'CANUPDATE', 2, true, false);
echo '</fieldset>';

echo OneButtonCenteredForm("ProcessCopyAuthority", $Title, 3, false, false);

echo '</div>
	</form>';

include('includes/footer.php');
?>
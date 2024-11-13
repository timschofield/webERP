<?php

include('includes/session.php');

$Title=_('Update of PPH21 Deduction');
include('includes/header.php');

echo '<p class="page_title_text"><img src="'.$RootPath.'/css/'.$Theme.'/images/inventory.png" title="' . $Title . '" alt="" />' . ' ' . $Title . '</p>';

//update database if update pressed
if (isset($_POST['submit'])){
	for ($i=1;$i<count($_POST);$i++){ //loop through the returned customers
		if (isset($_POST['CodeName' . $i]) AND is_numeric(filter_number_format($_POST['PotonganPPH21'.$i]))){
			if ($_POST['PotonganPPH21'.$i] > 0){
				// should be always negative
				$_POST['PotonganPPH21'.$i] = -$_POST['PotonganPPH21'.$i];
			}
			$SQLUpdate="UPDATE salariescalculated SET potonganpph21 = '" . filter_number_format($_POST['PotonganPPH21'.$i]) . "' 
						WHERE company = '" . $_POST['Company'] . "'
						AND periodno = '" . $_POST['PeriodPPH21'] . "'
						AND salarytype = '" . $_POST['SalaryType'] . "'
						AND codename = '" . $_POST['CodeName' . $i] . "'";
			$Result = DB_query($SQLUpdate);
		}
	}
}

if (isset($_POST['submit']) OR isset($_POST['Update'])) {

	$PeriodPPH21 = GetPeriod(ConvertSQLDate($_POST['DateOfFile']));
	$PeriodName = MonthAndYearFromSQLDate($_POST['DateOfFile']);

	$sql="SELECT codename,
				fullname,
				zonepph21,
				potonganpph21
			FROM salariescalculated
			WHERE periodno = '" . $PeriodPPH21 . "'
				AND company = '" . $_POST['Company'] . "'
				AND salarytype = '" . $_POST['SalaryType'] . "'
			ORDER BY zonepph21,
				fullname";

	$result = DB_query($sql);

	echo'<p class="page_title_text"><strong>' . _('Company: ') . '' . $_POST['Company'] . ' </strong></p>';
	echo'<p class="page_title_text"><strong>' . _('Month: ') . '' . $PeriodName . ' </strong></p>';
	$k=0; //row colour counter
	echo '<form action="KLPersonaliaDeductionPPH21.php" method="post" id="Update">';
    echo '<div>';
	echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';
	echo '<input type="hidden" name="Company" value="' . $_GET['Company'] . '" />';
	echo '<table>';
	
	echo '<tr>
            <th>' . _('Zone PPH21') . '</th>
            <th>' . _('Full Name') . '</th>
            <th>' . _('Code Name') . '</th>
            <th>' . _('Deduction PPH21') . '</th>
        </tr>';

	$i=1;
	while ($myrow=DB_fetch_array($result))	{

		if ($k==1){
			echo '<tr class="EvenTableRows">';
			$k=0;
		} else {
			echo '<tr class="OddTableRows">';
			$k=1;
		}

		//variable for update data

		echo'<input type="hidden" value="' . $_POST['Company'] . '" name="Company" />
			<input type="hidden" value="' . $_POST['DateOfFile'] . '" name="DateOfFile" />
			<input type="hidden" value="' . $_POST['SalaryType'] . '" name="SalaryType" />
			<input type="hidden" value="' . $PeriodPPH21 . '" name="PeriodPPH21" />';

		echo '<td>'. $myrow['zonepph21'] . '</td>
			<td>' . $myrow['fullname'] . '</td>
			<td>' . $myrow['codename'] . '</td>';
		echo '<td><input type="text" class="number" name="PotonganPPH21' . $i .'" maxlength="12" size="12" value="'. locale_number_format($myrow['potonganpph21'],0) .'" />
			<input type="hidden" name="CodeName' . $i . '" value="' . $myrow['codename'] . '" /></td>
			</tr> ';
		$i++;
	} //end of looping
	echo'<tr>
			<td style="text-align:center" colspan="4">
				<input type="submit" name="submit" value="' . _('Update') . '" />
			</td>
		</tr>
        </table>
        </div>
		</form>';


} else { /*The option to submit was not hit so display form */

	echo '<br />
		<br />
		<form action="' . htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') . '" method="post">
		<div>';
	echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';

	echo '<p class="page_title_text">
			<img src="' . $RootPath . '/css/' . $Theme . '/images/magnifier.png" title="' . $Title . '" alt="" />' . ' ' . $Title . '
		</p>';

	echo '<table class="selection">';

	include('includes/KLPersonaliaParameterSelection.php');

	echo '</table>
			<br />
			<div class="centre">
				<input type="submit" name="submit" value="' . _('Submit') . '" />
			</div>';
    echo '</div>
          </form>';

} /*end of else not submit */
include('includes/footer.php');
?>
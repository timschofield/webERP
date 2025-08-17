<?php

include('includes/session.php');
include('includes/UIGeneralFunctions.php');
include('includes/KLUIGeneralFunctions.php');

$Title=__('Update of PPH21 Deduction');
include('includes/header.php');

echo '<p class="page_title_text"><img src="'.$RootPath.'/css/'.$Theme.'/images/inventory.png" title="' . $Title . '" alt="" />' . ' ' . $Title . '</p>';

//update database if update pressed
if (isset($_POST['submit'])){
	for ($i=1;$i<count($_POST);$i++){ //loop through the returned employess
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

	$PeriodPPH21 = $_POST['PeriodOfFile'];
	$PeriodName = MonthAndYearFromPeriodNo($_POST['PeriodOfFile']);

	$SQL="SELECT codename,
				fullname,
				zonepph21,
				potonganpph21
			FROM salariescalculated
			WHERE periodno = '" . $PeriodPPH21 . "'
				AND company = '" . $_POST['Company'] . "'
				AND salarytype = '" . $_POST['SalaryType'] . "'
			ORDER BY zonepph21,
				fullname";

	$Result = DB_query($SQL);

	echo'<p class="page_title_text">' . __('Company: ') . '' . $_POST['Company'] . ' ' . __('Month: ') . $PeriodName .' </p>';
	echo '<form action="KLPersonaliaDeductionPPH21.php" method="post" id="Update">';
    echo '<div>';
	echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';
	echo '<input type="hidden" name="Company" value="' . $_GET['Company'] . '" />';
	echo '<table>';
	
	echo '<thead>
            <tr>
                <th>' . __('Zone PPH21') . '</th>
                <th>' . __('Full Name') . '</th>
                <th>' . __('Code Name') . '</th>
                <th>' . __('Deduction PPH21') . '</th>
            </tr>
          </thead>
          <tbody>';

	$i=1;
	while ($MyRow=DB_fetch_array($Result))	{
		echo '<tr class="striped_row">';

		//variable for update data

		echo'<input type="hidden" value="' . $_POST['Company'] . '" name="Company" />
			<input type="hidden" value="' . $_POST['PeriodOfFile'] . '" name="PeriodOfFile" />
			<input type="hidden" value="' . $_POST['SalaryType'] . '" name="SalaryType" />
			<input type="hidden" value="' . $PeriodPPH21 . '" name="PeriodPPH21" />';

		echo '<td>'. $MyRow['zonepph21'] . '</td>
			<td>' . $MyRow['fullname'] . '</td>
			<td>' . $MyRow['codename'] . '</td>';
		echo '<td><input type="text" class="number" name="PotonganPPH21' . $i .'" maxlength="12" size="12" value="'. locale_number_format($MyRow['potonganpph21'],0) .'" />
			<input type="hidden" name="CodeName' . $i . '" value="' . $MyRow['codename'] . '" /></td>
			</tr> ';
		$i++;
	} //end of looping
	echo'<tr>
			<td style="text-align:center" colspan="4">
				<input type="submit" name="submit" value="' . __('Update') . '" />
			</td>
		</tr>
        </tbody>
        </table>
        </div>
		</form>';


} else { /*The option to submit was not hit so display form */

	echo '<form action="' . htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') . '" method="post">
		<div>';
	echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';

	echo '<fieldset>
		<legend>' . __('Selection Parameters') . '</legend>';

	include('includes/KLPersonaliaParameterSelection.php');

	echo '</fieldset>';
	
	echo OneButtonCenteredForm('submit', __('Submit'));
	
    echo '</div>
          </form>';

} /*end of else not submit */
include('includes/footer.php');

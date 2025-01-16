<?php

/////////////////////////////////////////////////////////////////////
//  Company, Month and Type of Salary Selection
/////////////////////////////////////////////////////////////////////

echo FieldToSelectFromThreeOptions('PTADU', 'PT Angin Dingin Utara',
									'PTBB', 'PT Bumi Biru',
									'PTSMH', 'PT Sungai Mutiara Hitam',
									'Company', 
									isset($_POST['Company']) ? $_POST['Company'] : 'PTADU',
									_('For Employees of') . ':');

echo FieldToSelectOnePeriod('DateOfFile',
							isset($_POST['DateOfFile']) ? $_POST['DateOfFile'] : '',
							_('Select Month of the Salaries'));

echo FieldToSelectFromTwoOptions('MONTHLY', _('Monthly Salary'),
								'THRONLY', _('THR Only'),
								'SalaryType',
								isset($_POST['SalaryType']) ? $_POST['SalaryType'] : 'MONTHLY',
								_('Type Of Salary') . ':');



?>

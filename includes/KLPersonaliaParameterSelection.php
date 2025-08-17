<?php

/////////////////////////////////////////////////////////////////////
//  Company, Month and Type of Salary Selection
/////////////////////////////////////////////////////////////////////

echo FieldToSelectFromThreeOptions('PTADU', 'PT Angin Dingin Utara',
								'PTSMH', 'PT Sungai Mutiara Hitam',
								'PTBB', 'PT Bumi Biru',
								'Company', 
								isset($_POST['Company']) ? $_POST['Company'] : 'PTADU',
								__('For Employees of') . ':');

echo FieldToSelectOnePeriod('PeriodOfFile',
							isset($_POST['PeriodOfFile']) ? $_POST['PeriodOfFile'] : GetPeriod(Date($_SESSION['DefaultDateFormat'])) - 1,
							__('Select Month of the Salaries'));

echo FieldToSelectFromTwoOptions('MONTHLY', __('Monthly Salary'),
							'THRONLY', __('THR Only'),
							'SalaryType',
							isset($_POST['SalaryType']) ? $_POST['SalaryType'] : 'MONTHLY',
							__('Type Of Salary') . ':');


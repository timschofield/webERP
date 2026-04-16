<?php

UpdateField('hrsystemoptions', 'optionname', 'AppraisalFrequency', 'optionname="appraisal_frequency"');
UpdateField('hrsystemoptions', 'optionname', 'ProbationPeriod', 'optionname="probation_period_days"');

InsertRecord('hrsystemoptions', array('optioncategory', 'optionname'), array('Compensation', 'MinSalaryIncreasePercent'), array('optioncategory', 'optionname', 'optionvalue', 'optiondescription'), array('Compensation', 'MinSalaryIncreasePercent', '0', 'Minimum Salary increase in percentage'));
InsertRecord('hrsystemoptions', array('optioncategory', 'optionname'), array('Compensation', 'MaxSalaryIncreasePercent'), array('optioncategory', 'optionname', 'optionvalue', 'optiondescription'), array('Compensation', 'MaxSalaryIncreasePercent', '15', 'Maximum Salary increase in percentage'));
InsertRecord('hrsystemoptions', array('optioncategory', 'optionname'), array('General', 'MaxSickDays'), array('optioncategory', 'optionname', 'optionvalue', 'optiondescription'), array('General', 'MaxSickDays', '10', 'Maximum Sick Days per year'));
InsertRecord('hrsystemoptions', array('optioncategory', 'optionname'), array('General', 'MaxVacationDays'), array('optioncategory', 'optionname', 'optionvalue', 'optiondescription'), array('General', 'MaxVacationDays', '20', 'Maximum Vacation Days per year'));

DeleteRecords('hrsystemoptions', 'optionname="auto_approval_limit"');
DeleteRecords('hrsystemoptions', 'optionname="default_currency"');
DeleteRecords('hrsystemoptions', 'optionname="fiscal_year_start_month"');
DeleteRecords('hrsystemoptions', 'optionname="merit_increase_cap"');
DeleteRecords('hrsystemoptions', 'optionname="requisition_approval_required"');

UpdateDBNo(basename(__FILE__, '.php'), __('Set proper HR settings names'));

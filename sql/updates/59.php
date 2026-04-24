<?php

ChangeColumnDefault('hiredate', 'hremployees', 'date', ' NOT NULL ', '1000-01-01');

$SQL = "UPDATE hremployees 
		SET terminationdate = '1000-01-01' 
		WHERE terminationdate IS NULL";
$Result = DB_query($SQL, '', '', false, false);

ChangeColumnDefault('terminationdate', 'hremployees', 'date', ' NOT NULL ', '1000-01-01');
ChangeColumnDefault('birthdate', 'hremployees', 'date', ' NOT NULL ', '1000-01-01');

ChangeColumnDefault('effectivedate', 'hremployeecompensation', 'date', ' NOT NULL ', '1000-01-01');
ChangeColumnDefault('approvaldate', 'hremployeecompensation', 'date', ' NOT NULL ', '1000-01-01');

ChangeColumnDefault('startdate', 'hrcompreviewcycles', 'date', ' NOT NULL ', '1000-01-01');
ChangeColumnDefault('enddate', 'hrcompreviewcycles', 'date', ' NOT NULL ', '1000-01-01');

ChangeColumnDefault('reviewperiodstart', 'hrperfappraisals', 'date', ' NOT NULL ', '1000-01-01');
ChangeColumnDefault('reviewperiodend', 'hrperfappraisals', 'date', ' NOT NULL ', '1000-01-01');
ChangeColumnDefault('duedate', 'hrperfappraisals', 'date', ' NOT NULL ', '1000-01-01');
ChangeColumnDefault('completiondate', 'hrperfappraisals', 'date', ' NOT NULL ', '1000-01-01');

ChangeColumnDefault('targetcompletiondate', 'hrperfgoals', 'date', ' NOT NULL ', '1000-01-01');

ChangeColumnDefault('reviewdate', 'hrperformancereviews', 'date', ' NOT NULL ', '1000-01-01');
ChangeColumnDefault('reviewperiodstart', 'hrperformancereviews', 'date', ' NOT NULL ', '1000-01-01');
ChangeColumnDefault('reviewperiodend', 'hrperformancereviews', 'date', ' NOT NULL ', '1000-01-01');
ChangeColumnDefault('nextreviewdate', 'hrperformancereviews', 'date', ' NOT NULL ', '1000-01-01');

ChangeColumnDefault('assessmentdate', 'hrempcompetencies', 'date', ' NOT NULL ', '1000-01-01');
ChangeColumnDefault('expirydate', 'hrempcompetencies', 'date', ' NOT NULL ', '1000-01-01');

ChangeColumnDefault('assessmentdate', 'hrempskills', 'date', ' NOT NULL ', '1000-01-01');

ChangeColumnDefault('assessmentdate', 'hremployeeskills', 'date', ' NOT NULL ', '1000-01-01');

ChangeColumnDefault('targetdate', 'hrperformancegoals', 'date', ' NOT NULL ', '1000-01-01');
ChangeColumnDefault('completiondate', 'hrperformancegoals', 'date', ' NOT NULL ', '1000-01-01');

ChangeColumnDefault('requestdate', 'hrrequisitions', 'date', ' NOT NULL ', '1000-01-01');
ChangeColumnDefault('targetstartdate', 'hrrequisitions', 'date', ' NOT NULL ', '1000-01-01');
ChangeColumnDefault('approvaldate', 'hrrequisitions', 'date', ' NOT NULL ', '1000-01-01');
ChangeColumnDefault('filleddate', 'hrrequisitions', 'date', ' NOT NULL ', '1000-01-01');

ChangeColumnDefault('applicationdate', 'hrapplicants', 'date', ' NOT NULL ', '1000-01-01');

ChangeColumnDefault('interviewdate', 'hrapplicantreqs', 'date', ' NOT NULL ', '1000-01-01');
ChangeColumnDefault('offerdate', 'hrapplicantreqs', 'date', ' NOT NULL ', '1000-01-01');
ChangeColumnDefault('startdate', 'hrapplicantreqs', 'date', ' NOT NULL ', '1000-01-01');

ChangeColumnDefault('activitydate', 'hrapplicantactivities', 'date', ' NOT NULL ', '1000-01-01');

ChangeColumnDefault('approvaldate', 'hrpositionbudgets', 'date', ' NOT NULL ', '1000-01-01');

ChangeColumnDefault('incidentdate', 'hrsafetyincidents', 'datetime', ' NOT NULL ', '1000-01-01 00:00:00');
ChangeColumnDefault('reporteddate', 'hrsafetyincidents', 'date', ' NOT NULL ', '1000-01-01');
ChangeColumnDefault('investigationdate', 'hrsafetyincidents', 'date', ' NOT NULL ', '1000-01-01');

DropColumn('addressbookid', 'hremployees');

RemoveMenuItem('hr', 'Transactions', __('Employee Entry'), '/HREmployeeEntry.php');
RemoveScript('HREmployeeEntry.php');

UpdateDBNo(basename(__FILE__, '.php'), __('More consistent handling of date fields in HR module'));

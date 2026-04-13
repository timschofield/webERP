INSERT INTO `hrsystemoptions` (`optioncategory`, `optionname`, `optionvalue`, `optiondescription`) VALUES
('Compensation', 'default_currency', 'USD', 'Default currency code for compensation'),
('Compensation', 'merit_increase_cap', '10.00', 'Maximum merit increase percentage'),
('Compensation', 'auto_approval_limit', '5000.00', 'Auto-approval limit for salary changes'),
('Performance', 'appraisal_frequency', 'Annual', 'Default appraisal frequency'),
('Performance', 'probation_period_days', '90', 'Standard probation period in days'),
('Recruitment', 'requisition_approval_required', '1', 'Require approval for requisitions'),
('General', 'fiscal_year_start_month', '1', 'Fiscal year start month (1-12)');

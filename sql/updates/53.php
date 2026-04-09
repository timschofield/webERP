<?php

// =====================================================
// Human Resources Management System Database Schema
// =====================================================

// Employee Master Data
CreateTable('hremployees', "CREATE TABLE `hremployees` (
	`employeeid` INT(11) NOT NULL AUTO_INCREMENT,
	`employeenumber` VARCHAR(20) NOT NULL,
	`userid` VARCHAR(20) DEFAULT NULL,
	`addressbookid` INT(11) DEFAULT NULL,
	`firstname` VARCHAR(50) NOT NULL,
	`middlename` VARCHAR(50) DEFAULT NULL,
	`lastname` VARCHAR(50) NOT NULL,
	`hiredate` DATE NOT NULL,
	`terminationdate` DATE DEFAULT NULL,
	`birthdate` DATE DEFAULT NULL,
	`gender` ENUM('M','F','Other') DEFAULT NULL,
	`email` VARCHAR(100) DEFAULT NULL,
	`phone` VARCHAR(20) DEFAULT NULL,
	`departmentid` INT(11) DEFAULT NULL,
	`positionid` INT(11) DEFAULT NULL,
	`supervisorid` INT(11) DEFAULT NULL,
	`employmentstatus` ENUM('Active','Terminated','On Leave','Suspended') DEFAULT 'Active',
	`employmenttype` ENUM('Full-Time','Part-Time','Contract','Temporary') DEFAULT NULL,
	`locationid` INT(11) DEFAULT NULL,
	`currentsalary` DECIMAL(15,2) DEFAULT NULL,
	`createddate` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
	`modifieddate` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
	`createdby` VARCHAR(50) DEFAULT NULL,
	`modifiedby` VARCHAR(50) DEFAULT NULL,
	PRIMARY KEY (`employeeid`),
	UNIQUE KEY `employeenumber` (`employeenumber`),
	UNIQUE KEY `userid` (`userid`),
	KEY `idx_supervisor` (`supervisorid`),
	KEY `idx_department` (`departmentid`),
	KEY `idx_position` (`positionid`),
	KEY `idx_status` (`employmentstatus`)
) ");

// Enhance existing departments table for HR functionality
// Add new columns to existing departments table
AddColumn('departmentcode', 'departments', 'VARCHAR(20)', 'NULL', '', 'departmentid');
AddColumn('parentdepartmentid', 'departments', 'INT(11)', 'NULL', '', 'authoriser');
AddColumn('managerid', 'departments', 'INT(11)', 'NULL', '', 'parentdepartmentid');
AddColumn('locationid', 'departments', 'VARCHAR(5)', 'NULL', '', 'managerid');
AddColumn('active', 'departments', 'TINYINT(1)', 'NOT NULL', '1', 'locationid');
AddColumn('createddate', 'departments', 'TIMESTAMP', 'NOT NULL', 'CURRENT_TIMESTAMP', 'active');

// Populate departmentcode with departmentid for existing records
$SQL = "UPDATE departments SET departmentcode = CONCAT('DEPT', LPAD(departmentid, 4, '0')) WHERE departmentcode IS NULL OR departmentcode = ''";
$Result = DB_query($SQL, '', '', false, false);

// Add indexes for performance
AddIndex(array('parentdepartmentid'), 'departments', 'idx_parent');
AddIndex(array('departmentcode'), 'departments', 'idx_departmentcode');

CreateTable('hrpositions', "CREATE TABLE `hrpositions` (
	`positionid` INT(11) NOT NULL AUTO_INCREMENT,
	`positioncode` VARCHAR(20) NOT NULL,
	`positiontitle` VARCHAR(100) NOT NULL,
	`departmentid` INT(11) DEFAULT NULL,
	`reportstopositionid` INT(11) DEFAULT NULL,
	`paygradeid` INT(11) DEFAULT NULL,
	`positionstatus` ENUM('Open','Filled','Frozen','Eliminated') DEFAULT 'Open',
	`fte` DECIMAL(4,2) DEFAULT 1.00,
	`jobdescription` TEXT,
	`requirements` TEXT,
	`active` TINYINT(1) DEFAULT 1,
	`createddate` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
	PRIMARY KEY (`positionid`),
	UNIQUE KEY `positioncode` (`positioncode`),
	KEY `idx_department` (`departmentid`),
	KEY `idx_paygrade` (`paygradeid`)
) ");

// Compensation Management
CreateTable('hrpaygrades', "CREATE TABLE `hrpaygrades` (
	`paygradeid` INT(11) NOT NULL AUTO_INCREMENT,
	`paygradecode` VARCHAR(20) NOT NULL,
	`paygradename` VARCHAR(100) NOT NULL,
	`currencycode` VARCHAR(3) DEFAULT 'USD',
	`minsalary` DECIMAL(15,2) DEFAULT NULL,
	`midsalary` DECIMAL(15,2) DEFAULT NULL,
	`maxsalary` DECIMAL(15,2) DEFAULT NULL,
	`active` TINYINT(1) DEFAULT 1,
	`createddate` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
	PRIMARY KEY (`paygradeid`),
	UNIQUE KEY `paygradecode` (`paygradecode`)
) ");

CreateTable('hrpaysteps', "CREATE TABLE `hrpaysteps` (
	`paystepid` INT(11) NOT NULL AUTO_INCREMENT,
	`paygradeid` INT(11) NOT NULL,
	`stepnumber` INT(11) NOT NULL,
	`stepamount` DECIMAL(15,2) NOT NULL,
	PRIMARY KEY (`paystepid`),
	UNIQUE KEY `unique_grade_step` (`paygradeid`,`stepnumber`)
) ");

AddConstraint('hrpaysteps', 'fk_paysteps_paygrade', 'paygradeid', 'hrpaygrades', 'paygradeid', 'RESTRICT');

CreateTable('hremployeecompensation', "CREATE TABLE `hremployeecompensation` (
	`compensationid` INT(11) NOT NULL AUTO_INCREMENT,
	`employeeid` INT(11) NOT NULL,
	`effectivedate` DATE NOT NULL,
	`paygradeid` INT(11) DEFAULT NULL,
	`paystepid` INT(11) DEFAULT NULL,
	`basesalary` DECIMAL(15,2) NOT NULL,
	`currencycode` VARCHAR(3) DEFAULT 'USD',
	`payfrequency` ENUM('Hourly','Weekly','Bi-Weekly','Semi-Monthly','Monthly','Annual') DEFAULT NULL,
	`increasetype` VARCHAR(20) DEFAULT NULL,
	`increaseamount` DECIMAL(15,2) DEFAULT NULL,
	`increasepercentage` DECIMAL(5,2) DEFAULT NULL,
	`reasoncode` VARCHAR(20) DEFAULT NULL,
	`notes` TEXT,
	`approvedby` INT(11) DEFAULT NULL,
	`approvaldate` DATE DEFAULT NULL,
	`createddate` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
	`createdby` VARCHAR(50) DEFAULT NULL,
	PRIMARY KEY (`compensationid`),
	KEY `idx_employee` (`employeeid`),
	KEY `idx_effectivedate` (`effectivedate`)
) ");

AddConstraint('hremployeecompensation', 'fk_empcompensation_employee', 'employeeid', 'hremployees', 'employeeid', 'RESTRICT');

CreateTable('hrincreaseguidelines', "CREATE TABLE `hrincreaseguidelines` (
	`guidelineid` INT(11) NOT NULL AUTO_INCREMENT,
	`increasetype` VARCHAR(20) NOT NULL,
	`guidelinecode` VARCHAR(20) NOT NULL,
	`description` VARCHAR(200) DEFAULT NULL,
	`minpercentage` DECIMAL(5,2) DEFAULT NULL,
	`maxpercentage` DECIMAL(5,2) DEFAULT NULL,
	`targetpercentage` DECIMAL(5,2) DEFAULT NULL,
	`serviceyearsfrom` INT(11) DEFAULT NULL,
	`serviceyearsto` INT(11) DEFAULT NULL,
	`performanceratingfrom` INT(11) DEFAULT NULL,
	`performanceratingto` INT(11) DEFAULT NULL,
	`active` TINYINT(1) DEFAULT 1,
	PRIMARY KEY (`guidelineid`),
	UNIQUE KEY `unique_guideline` (`increasetype`,`guidelinecode`)
) ");

CreateTable('hrcompreviewcycles', "CREATE TABLE `hrcompreviewcycles` (
	`reviewcycleid` INT(11) NOT NULL AUTO_INCREMENT,
	`cyclename` VARCHAR(100) NOT NULL,
	`fiscalyear` INT(11) NOT NULL,
	`startdate` DATE NOT NULL,
	`enddate` DATE NOT NULL,
	`budgetamount` DECIMAL(15,2) DEFAULT NULL,
	`status` ENUM('Planning','Active','Completed','Cancelled') DEFAULT 'Planning',
	`createddate` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
	PRIMARY KEY (`reviewcycleid`)
) ");

// Performance Management
CreateTable('hrperfratscales', "CREATE TABLE `hrperfratscales` (
	`scaleid` INT(11) NOT NULL AUTO_INCREMENT,
	`scalename` VARCHAR(50) NOT NULL,
	`ratingvalue` INT(11) NOT NULL,
	`ratinglabel` VARCHAR(50) NOT NULL,
	`ratingdescription` VARCHAR(200) DEFAULT NULL,
	`active` TINYINT(1) DEFAULT 1,
	PRIMARY KEY (`scaleid`),
	UNIQUE KEY `unique_scale_value` (`scalename`,`ratingvalue`)
) ");

CreateTable('hrperfappraisals', "CREATE TABLE `hrperfappraisals` (
	`appraisalid` INT(11) NOT NULL AUTO_INCREMENT,
	`employeeid` INT(11) NOT NULL,
	`reviewperiodstart` DATE NOT NULL,
	`reviewperiodend` DATE NOT NULL,
	`appraisaltype` ENUM('Annual','Mid-Year','Probation','90-Day','Project') NOT NULL,
	`reviewerid` INT(11) DEFAULT NULL,
	`overallrating` INT(11) DEFAULT NULL,
	`status` ENUM('Not Started','In Progress','Employee Review','Manager Review','Completed','Cancelled') DEFAULT 'Not Started',
	`duedate` DATE DEFAULT NULL,
	`completiondate` DATE DEFAULT NULL,
	`comments` TEXT,
	`goalsnextperiod` TEXT,
	`createddate` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
	`modifieddate` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
	PRIMARY KEY (`appraisalid`),
	KEY `idx_employee` (`employeeid`),
	KEY `idx_reviewer` (`reviewerid`),
	KEY `idx_status` (`status`)
) ");

AddConstraint('hrperfappraisals', 'fk_perfapp_employee', 'employeeid', 'hremployees', 'employeeid', 'RESTRICT');

CreateTable('hrperfgoals', "CREATE TABLE `hrperfgoals` (
	`goalid` INT(11) NOT NULL AUTO_INCREMENT,
	`appraisalid` INT(11) NOT NULL,
	`goaldescription` TEXT NOT NULL,
	`goalweight` DECIMAL(5,2) DEFAULT NULL,
	`targetcompletiondate` DATE DEFAULT NULL,
	`achievementrating` INT(11) DEFAULT NULL,
	`comments` TEXT,
	`goalorder` INT(11) DEFAULT NULL,
	PRIMARY KEY (`goalid`),
	KEY `idx_appraisal` (`appraisalid`)
) ");

AddConstraint('hrperfgoals', 'fk_perfgoals_appraisal', 'appraisalid', 'hrperfappraisals', 'appraisalid', 'CASCADE');

CreateTable('hrperfcompratings', "CREATE TABLE `hrperfcompratings` (
	`skillratingid` INT(11) NOT NULL AUTO_INCREMENT,
	`appraisalid` INT(11) NOT NULL,
	`skillid` INT(11) NOT NULL,
	`rating` INT(11) DEFAULT NULL,
	`comments` TEXT,
	PRIMARY KEY (`skillratingid`),
	KEY `idx_appraisal` (`appraisalid`)
) ");

AddConstraint('hrperfcompratings', 'fk_perfcomprat_appraisal', 'appraisalid', 'hrperfappraisals', 'appraisalid', 'CASCADE');

CreateTable('hrperformancereviews', "CREATE TABLE `hrperformancereviews` (
	`reviewid` INT(11) NOT NULL AUTO_INCREMENT,
	`employeeid` INT(11) NOT NULL,
	`reviewdate` DATE NOT NULL,
	`reviewperiodstart` DATE DEFAULT NULL,
	`reviewperiodend` DATE DEFAULT NULL,
	`reviewtype` ENUM('Performance','Probation','Annual','Mid-Year','Project') NOT NULL,
	`reviewerid` INT(11) DEFAULT NULL,
	`overallrating` VARCHAR(50) DEFAULT NULL,
	`overallscore` DECIMAL(5,2) DEFAULT NULL,
	`strengths` TEXT,
	`areasforimprovement` TEXT,
	`goals` TEXT,
	`developmentplan` TEXT,
	`reviewercomments` TEXT,
	`employeecomments` TEXT,
	`nextreviewdate` DATE DEFAULT NULL,
	`status` ENUM('Draft','Submitted','Completed') DEFAULT 'Draft',
	`createdby` VARCHAR(50) DEFAULT NULL,
	`createddate` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
	`modifiedby` VARCHAR(50) DEFAULT NULL,
	`modifieddate` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
	PRIMARY KEY (`reviewid`),
	KEY `idx_employee` (`employeeid`),
	KEY `idx_reviewer` (`reviewerid`)
) ");

AddConstraint('hrperformancereviews', 'fk_perfreview_employee', 'employeeid', 'hremployees', 'employeeid', 'RESTRICT');

CreateTable('hrperformanceratings', "CREATE TABLE `hrperformanceratings` (
	`ratingid` INT(11) NOT NULL AUTO_INCREMENT,
	`reviewid` INT(11) NOT NULL,
	`criteriaid` INT(11) NOT NULL,
	`rating` INT(11) NOT NULL,
	`comments` TEXT,
	`weightedscore` DECIMAL(5,2) DEFAULT NULL,
	`createdby` VARCHAR(20) NOT NULL,
	`createddate` DATETIME NOT NULL,
	PRIMARY KEY (`ratingid`),
	KEY `reviewid` (`reviewid`),
	KEY `criteriaid` (`criteriaid`)
) ");

AddConstraint('hrperformanceratings', 'fk_perfrating_review', 'reviewid', 'hrperformancereviews', 'reviewid', 'CASCADE');
AddConstraint('hrperformanceratings', 'fk_perfrating_criteria', 'criteriaid', 'hrperformancecriteria', 'criteriaid', 'RESTRICT');

// Competency Management
CreateTable('hrskills', "CREATE TABLE `hrskills` (
	`skillid` INT(11) NOT NULL AUTO_INCREMENT,
	`skillcode` VARCHAR(20) NOT NULL,
	`skillname` VARCHAR(100) NOT NULL,
	`skillcategory` ENUM('Technical','Behavioral','Leadership','Core') NOT NULL,
	`description` TEXT,
	`active` TINYINT(1) DEFAULT 1,
	`createddate` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
	PRIMARY KEY (`skillid`),
	UNIQUE KEY `skillcode` (`skillcode`)
) ");

CreateTable('hrskilllevels', "CREATE TABLE `hrskilllevels` (
	`skilllevelid` INT(11) NOT NULL AUTO_INCREMENT,
	`skillid` INT(11) NOT NULL,
	`levelnumber` INT(11) NOT NULL,
	`levelname` VARCHAR(50) NOT NULL,
	`leveldescription` TEXT,
	PRIMARY KEY (`skilllevelid`),
	UNIQUE KEY `unique_competency_level` (`skillid`,`levelnumber`)
) ");

AddConstraint('hrskilllevels', 'fk_complevel_comp', 'skillid', 'hrskills', 'skillid', 'RESTRICT');

CreateTable('hrjobcompetencies', "CREATE TABLE `hrjobcompetencies` (
	`jobskillid` INT(11) NOT NULL AUTO_INCREMENT,
	`positionid` INT(11) NOT NULL,
	`skillid` INT(11) NOT NULL,
	`requiredlevel` INT(11) NOT NULL,
	`importance` ENUM('Essential','Important','Desired') DEFAULT NULL,
	PRIMARY KEY (`jobskillid`),
	UNIQUE KEY `unique_position_competency` (`positionid`,`skillid`)
) ");

AddConstraint('hrjobcompetencies', 'fk_jobcomp_position', 'positionid', 'hrpositions', 'positionid', 'RESTRICT');
AddConstraint('hrjobcompetencies', 'fk_jobcomp_competency', 'skillid', 'hrskills', 'skillid', 'RESTRICT');

// Job Skills table (alternative name for compatibility)
CreateTable('hrjobskills', "CREATE TABLE `hrjobskills` (
	`jobskillid` INT(11) NOT NULL AUTO_INCREMENT,
	`positionid` INT(11) NOT NULL,
	`skillid` INT(11) NOT NULL,
	`requiredlevel` INT(11) NOT NULL,
	`importance` ENUM('Essential','Important','Desired') DEFAULT NULL,
	PRIMARY KEY (`jobskillid`),
	UNIQUE KEY `unique_position_skill` (`positionid`,`skillid`)
) ");

AddConstraint('hrjobskills', 'fk_jobskill_position', 'positionid', 'hrpositions', 'positionid', 'RESTRICT');
AddConstraint('hrjobskills', 'fk_jobskill_skill', 'skillid', 'hrskills', 'skillid', 'RESTRICT');

CreateTable('hrempcompetencies', "CREATE TABLE `hrempcompetencies` (
	`empskillid` INT(11) NOT NULL AUTO_INCREMENT,
	`employeeid` INT(11) NOT NULL,
	`skillid` INT(11) NOT NULL,
	`currentlevel` INT(11) DEFAULT NULL,
	`assessmentdate` DATE DEFAULT NULL,
	`assessedby` INT(11) DEFAULT NULL,
	`expirydate` DATE DEFAULT NULL,
	`notes` TEXT,
	PRIMARY KEY (`empskillid`),
	KEY `idx_employee` (`employeeid`)
) ");

AddConstraint('hrempcompetencies', 'fk_empcomp_employee', 'employeeid', 'hremployees', 'employeeid', 'RESTRICT');
AddConstraint('hrempcompetencies', 'fk_empcomp_competency', 'skillid', 'hrskills', 'skillid', 'RESTRICT');

// Employee Skills table (alternative name for compatibility)
CreateTable('hrempskills', "CREATE TABLE `hrempskills` (
	`empskillid` INT(11) NOT NULL AUTO_INCREMENT,
	`employeeid` INT(11) NOT NULL,
	`skillid` INT(11) NOT NULL,
	`currentlevel` INT(11) NOT NULL DEFAULT 0 COMMENT '1-5 proficiency level',
	`assessmentdate` DATE NOT NULL,
	`assessedby` VARCHAR(20) NOT NULL,
	`notes` TEXT DEFAULT NULL,
	PRIMARY KEY (`empskillid`),
	UNIQUE KEY `employee_skill` (`employeeid`, `skillid`),
	KEY `employeeid` (`employeeid`),
	KEY `skillid` (`skillid`)
) ");

AddConstraint('hrempskills', 'fk_empskill_employee', 'employeeid', 'hremployees', 'employeeid', 'RESTRICT');
AddConstraint('hrempskills', 'fk_empskill_skill', 'skillid', 'hrskills', 'skillid', 'RESTRICT');

// Employee Skills table (another alternative name for compatibility)
CreateTable('hremployeeskills', "CREATE TABLE `hremployeeskills` (
	`empskillid` INT(11) NOT NULL AUTO_INCREMENT,
	`employeeid` INT(11) NOT NULL,
	`skillid` INT(11) NOT NULL,
	`currentlevel` INT(11) NOT NULL DEFAULT 0 COMMENT '1-5 proficiency level',
	`assessmentdate` DATE NOT NULL,
	`assessedby` VARCHAR(20) NOT NULL,
	`notes` TEXT DEFAULT NULL,
	PRIMARY KEY (`empskillid`),
	UNIQUE KEY `employee_skill` (`employeeid`, `skillid`),
	KEY `employeeid` (`employeeid`),
	KEY `skillid` (`skillid`)
) ");

AddConstraint('hremployeeskills', 'fk_empskill2_employee', 'employeeid', 'hremployees', 'employeeid', 'RESTRICT');
AddConstraint('hremployeeskills', 'fk_empskill2_skill', 'skillid', 'hrskills', 'skillid', 'RESTRICT');

// Employee Performance Goals (standalone goals not part of appraisals)
CreateTable('hrperformancegoals', "CREATE TABLE `hrperformancegoals` (
	`goalid` INT(11) NOT NULL AUTO_INCREMENT,
	`employeeid` INT(11) NOT NULL,
	`goaldescription` TEXT NOT NULL,
	`goalcategory` VARCHAR(50) DEFAULT NULL,
	`targetdate` DATE NOT NULL,
	`status` VARCHAR(20) NOT NULL DEFAULT 'Not Started',
	`progress` INT(11) NOT NULL DEFAULT 0,
	`completiondate` DATE DEFAULT NULL,
	`notes` TEXT DEFAULT NULL,
	`weight` DECIMAL(5,2) DEFAULT 1.00,
	`createdby` VARCHAR(20) NOT NULL,
	`createddate` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
	`modifiedby` VARCHAR(20) DEFAULT NULL,
	`modifieddate` DATETIME DEFAULT NULL,
	PRIMARY KEY (`goalid`),
	KEY `idx_employee` (`employeeid`)
) ");

AddConstraint('hrperformancegoals', 'fk_perfgoals_employee', 'employeeid', 'hremployees', 'employeeid', 'CASCADE');

// Recruitment Management
CreateTable('hrrequisitions', "CREATE TABLE `hrrequisitions` (
	`requisitionid` INT(11) NOT NULL AUTO_INCREMENT,
	`requisitionnumber` VARCHAR(20) NOT NULL,
	`positionid` INT(11) NOT NULL,
	`departmentid` INT(11) NOT NULL,
	`numberofpositions` INT(11) DEFAULT 1,
	`requisitiontype` ENUM('New','Replacement','Temporary') NOT NULL,
	`priority` ENUM('Low','Medium','High','Urgent') DEFAULT 'Medium',
	`requestedby` INT(11) DEFAULT NULL,
	`requestdate` DATE NOT NULL,
	`targetstartdate` DATE DEFAULT NULL,
	`status` ENUM('Draft','Pending Approval','Approved','In Progress','Filled','Cancelled') DEFAULT 'Draft',
	`approvedby` INT(11) DEFAULT NULL,
	`approvaldate` DATE DEFAULT NULL,
	`filleddate` DATE DEFAULT NULL,
	`justification` TEXT,
	`createdby` VARCHAR(20) DEFAULT NULL,
	`createddate` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
	PRIMARY KEY (`requisitionid`),
	UNIQUE KEY `requisitionnumber` (`requisitionnumber`),
	KEY `idx_status` (`status`),
	KEY `idx_position` (`positionid`)
) ");

AddConstraint('hrrequisitions', 'fk_requisition_position', 'positionid', 'hrpositions', 'positionid', 'RESTRICT');

CreateTable('hrapplicants', "CREATE TABLE `hrapplicants` (
	`applicantid` INT(11) NOT NULL AUTO_INCREMENT,
	`firstname` VARCHAR(50) NOT NULL,
	`middlename` VARCHAR(50) DEFAULT NULL,
	`lastname` VARCHAR(50) NOT NULL,
	`email` VARCHAR(100) NOT NULL,
	`phone` VARCHAR(20) DEFAULT NULL,
	`address` TEXT,
	`resumefile` VARCHAR(255) DEFAULT NULL,
	`coverletter` TEXT,
	`source` VARCHAR(50) DEFAULT NULL,
	`applicationdate` DATE NOT NULL,
	`overallstatus` ENUM('New','Under Review','Interview','Offer','Hired','Rejected','Withdrawn') DEFAULT 'New',
	`createddate` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
	PRIMARY KEY (`applicantid`),
	KEY `idx_status` (`overallstatus`),
	KEY `idx_email` (`email`)
) ");

CreateTable('hrapplicantreqs', "CREATE TABLE `hrapplicantreqs` (
	`applicationid` INT(11) NOT NULL AUTO_INCREMENT,
	`applicantid` INT(11) NOT NULL,
	`requisitionid` INT(11) NOT NULL,
	`applicationstatus` ENUM('New','Screening','Interview','Offer Extended','Offer Accepted','Offer Rejected','Hired','Rejected','Withdrawn') DEFAULT 'New',
	`screeningscore` DECIMAL(5,2) DEFAULT NULL,
	`interviewdate` DATE DEFAULT NULL,
	`interviwerid` INT(11) DEFAULT NULL,
	`interviewrating` INT(11) DEFAULT NULL,
	`interviewnotes` TEXT,
	`offeramount` DECIMAL(15,2) DEFAULT NULL,
	`offerdate` DATE DEFAULT NULL,
	`startdate` DATE DEFAULT NULL,
	`rejectionreason` VARCHAR(200) DEFAULT NULL,
	`notes` TEXT,
	`createddate` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
	PRIMARY KEY (`applicationid`),
	KEY `idx_applicant` (`applicantid`),
	KEY `idx_requisition` (`requisitionid`)
) ");

AddConstraint('hrapplicantreqs', 'fk_appreq_applicant', 'applicantid', 'hrapplicants', 'applicantid', 'RESTRICT');
AddConstraint('hrapplicantreqs', 'fk_appreq_requisition', 'requisitionid', 'hrrequisitions', 'requisitionid', 'RESTRICT');

CreateTable('hrapplicantactivities', "CREATE TABLE `hrapplicantactivities` (
	`activityid` INT(11) NOT NULL AUTO_INCREMENT,
	`applicantid` INT(11) NOT NULL,
	`activitydate` DATE NOT NULL,
	`activitytype` VARCHAR(50) NOT NULL,
	`description` TEXT,
	`interviewer` VARCHAR(100) DEFAULT NULL,
	`rating` INT(11) DEFAULT NULL,
	`nextsteps` TEXT,
	`createdby` VARCHAR(20) NOT NULL,
	`createddate` DATETIME NOT NULL,
	PRIMARY KEY (`activityid`),
	KEY `applicantid` (`applicantid`),
	KEY `activitydate` (`activitydate`)
) ");

AddConstraint('hrapplicantactivities', 'fk_appactivity_applicant', 'applicantid', 'hrapplicants', 'applicantid', 'CASCADE');

// Position Budget Management
CreateTable('hrpositionbudgets', "CREATE TABLE `hrpositionbudgets` (
	`budgetid` INT(11) NOT NULL AUTO_INCREMENT,
	`fiscalyear` INT(11) NOT NULL,
	`departmentid` INT(11) NOT NULL,
	`positionid` INT(11) NOT NULL,
	`budgetedpositions` DECIMAL(4,2) NOT NULL,
	`budgetedsalary` DECIMAL(15,2) NOT NULL,
	`actualpositions` DECIMAL(4,2) DEFAULT NULL,
	`actualsalary` DECIMAL(15,2) DEFAULT NULL,
	`variancepositions` DECIMAL(4,2) DEFAULT NULL,
	`variancesalary` DECIMAL(15,2) DEFAULT NULL,
	`status` ENUM('Draft','Submitted','Approved','Active','Closed') DEFAULT 'Draft',
	`approvedby` INT(11) DEFAULT NULL,
	`approvaldate` DATE DEFAULT NULL,
	`notes` TEXT,
	PRIMARY KEY (`budgetid`),
	UNIQUE KEY `unique_budget` (`fiscalyear`,`departmentid`,`positionid`),
	KEY `idx_fiscalyear` (`fiscalyear`)
) ");

AddConstraint('hrpositionbudgets', 'fk_posbudget_position', 'positionid', 'hrpositions', 'positionid', 'RESTRICT');

// Health & Safety Management
CreateTable('hrsafetyincidents', "CREATE TABLE `hrsafetyincidents` (
	`incidentid` INT(11) NOT NULL AUTO_INCREMENT,
	`incidentnumber` VARCHAR(20) NOT NULL,
	`incidentdate` DATETIME NOT NULL,
	`employeeid` INT(11) DEFAULT NULL,
	`locationid` VARCHAR(5) DEFAULT NULL,
	`incidenttype` ENUM('Injury','Near Miss','Property Damage','Environmental','Other') NOT NULL,
	`severity` ENUM('Minor','Moderate','Serious','Critical','Fatal') DEFAULT NULL,
	`description` TEXT NOT NULL,
	`immediateaction` TEXT,
	`rootcause` TEXT,
	`correctiveaction` TEXT,
	`reportedby` INT(11) DEFAULT NULL,
	`reporteddate` DATE DEFAULT NULL,
	`investigatedby` INT(11) DEFAULT NULL,
	`investigationdate` DATE DEFAULT NULL,
	`status` ENUM('Reported','Under Investigation','Resolved','Closed') DEFAULT 'Reported',
	`dayslost` INT(11) DEFAULT 0,
	`createddate` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
	PRIMARY KEY (`incidentid`),
	UNIQUE KEY `incidentnumber` (`incidentnumber`),
	KEY `idx_incidentdate` (`incidentdate`),
	KEY `idx_employee` (`employeeid`)
) ");

AddConstraint('hrsafetyincidents', 'fk_safetyinc_employee', 'employeeid', 'hremployees', 'employeeid', 'RESTRICT');

// System Configuration
CreateTable('hrsystemoptions', "CREATE TABLE `hrsystemoptions` (
	`optionid` INT(11) NOT NULL AUTO_INCREMENT,
	`optioncategory` VARCHAR(50) NOT NULL,
	`optionname` VARCHAR(100) NOT NULL,
	`optionvalue` TEXT,
	`optiondescription` VARCHAR(255) DEFAULT NULL,
	PRIMARY KEY (`optionid`),
	UNIQUE KEY `unique_option` (`optioncategory`,`optionname`)
) ");

CreateTable('hrapprovalhierarchy', "CREATE TABLE `hrapprovalhierarchy` (
	`hierarchyid` INT(11) NOT NULL AUTO_INCREMENT,
	`approvaltype` VARCHAR(50) NOT NULL,
	`levelnumber` INT(11) NOT NULL,
	`approverrole` VARCHAR(50) DEFAULT NULL,
	`approverid` INT(11) DEFAULT NULL,
	`amountlimit` DECIMAL(15,2) DEFAULT NULL,
	`required` TINYINT(1) DEFAULT 1,
	PRIMARY KEY (`hierarchyid`),
	UNIQUE KEY `unique_approval_level` (`approvaltype`,`levelnumber`)
) ");

// Audit and History
CreateTable('hraudittrail', "CREATE TABLE `hraudittrail` (
	`auditid` INT(11) NOT NULL AUTO_INCREMENT,
	`tablename` VARCHAR(50) NOT NULL,
	`recordid` INT(11) NOT NULL,
	`actiontype` ENUM('INSERT','UPDATE','DELETE') NOT NULL,
	`oldvalues` TEXT,
	`newvalues` TEXT,
	`changedby` VARCHAR(50) NOT NULL,
	`changeddate` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
	PRIMARY KEY (`auditid`),
	KEY `idx_table_record` (`tablename`,`recordid`),
	KEY `idx_changeddate` (`changeddate`)
) ");

// Insert Default Data
InsertRecord('hrperfratscales', array('scalename', 'ratingvalue'), array('Standard', 1), array('scalename', 'ratingvalue', 'ratinglabel', 'ratingdescription', 'active'), array('Standard', 1, 'Unsatisfactory', 'Performance is below expectations and requires immediate improvement', 1));
InsertRecord('hrperfratscales', array('scalename', 'ratingvalue'), array('Standard', 2), array('scalename', 'ratingvalue', 'ratinglabel', 'ratingdescription', 'active'), array('Standard', 2, 'Needs Improvement', 'Performance meets some expectations but requires development', 1));
InsertRecord('hrperfratscales', array('scalename', 'ratingvalue'), array('Standard', 3), array('scalename', 'ratingvalue', 'ratinglabel', 'ratingdescription', 'active'), array('Standard', 3, 'Meets Expectations', 'Performance consistently meets job requirements', 1));
InsertRecord('hrperfratscales', array('scalename', 'ratingvalue'), array('Standard', 4), array('scalename', 'ratingvalue', 'ratinglabel', 'ratingdescription', 'active'), array('Standard', 4, 'Exceeds Expectations', 'Performance consistently exceeds job requirements', 1));
InsertRecord('hrperfratscales', array('scalename', 'ratingvalue'), array('Standard', 5), array('scalename', 'ratingvalue', 'ratinglabel', 'ratingdescription', 'active'), array('Standard', 5, 'Outstanding', 'Performance significantly exceeds all expectations', 1));

InsertRecord('hrsystemoptions', array('optioncategory', 'optionname'), array('Compensation', 'default_currency'), array('optioncategory', 'optionname', 'optionvalue', 'optiondescription'), array('Compensation', 'default_currency', 'USD', 'Default currency code for compensation'));
InsertRecord('hrsystemoptions', array('optioncategory', 'optionname'), array('Compensation', 'merit_increase_cap'), array('optioncategory', 'optionname', 'optionvalue', 'optiondescription'), array('Compensation', 'merit_increase_cap', '10.00', 'Maximum merit increase percentage'));
InsertRecord('hrsystemoptions', array('optioncategory', 'optionname'), array('Compensation', 'auto_approval_limit'), array('optioncategory', 'optionname', 'optionvalue', 'optiondescription'), array('Compensation', 'auto_approval_limit', '5000.00', 'Auto-approval limit for salary changes'));
InsertRecord('hrsystemoptions', array('optioncategory', 'optionname'), array('Performance', 'appraisal_frequency'), array('optioncategory', 'optionname', 'optionvalue', 'optiondescription'), array('Performance', 'appraisal_frequency', 'Annual', 'Default appraisal frequency'));
InsertRecord('hrsystemoptions', array('optioncategory', 'optionname'), array('Performance', 'probation_period_days'), array('optioncategory', 'optionname', 'optionvalue', 'optiondescription'), array('Performance', 'probation_period_days', '90', 'Standard probation period in days'));
InsertRecord('hrsystemoptions', array('optioncategory', 'optionname'), array('Recruitment', 'requisition_approval_required'), array('optioncategory', 'optionname', 'optionvalue', 'optiondescription'), array('Recruitment', 'requisition_approval_required', '1', 'Require approval for requisitions'));
InsertRecord('hrsystemoptions', array('optioncategory', 'optionname'), array('General', 'fiscal_year_start_month'), array('optioncategory', 'optionname', 'optionvalue', 'optiondescription'), array('General', 'fiscal_year_start_month', '1', 'Fiscal year start month (1-12)'));

// Register HR Module
NewModule('hr', 'HR', __('Human Resources'), 8);
ChangeColumnSize('modulesallowed', 'www_users', 'varchar(100)', ' NOT NULL ', '', 100);

// Register HR scripts
NewScript('HRDashboard.php', 15, __('HR management dashboard'));
NewMenuItem('hr', 'Maintenance', __('HR Dashboard'), '/HRDashboard.php', 1);

NewScript('HREmployeeEntry.php', 15, __('Employee entry and maintenance'));
NewMenuItem('hr', 'Transactions', __('Employee Entry'), '/HREmployeeEntry.php', 1);

NewScript('HREmployees.php', 15, __('Employee list and search'));
NewMenuItem('hr', 'Reports', __('Employee List'), '/HREmployees.php', 1);

NewScript('HRDepartments.php', 15, __('Department management'));
NewMenuItem('hr', 'Maintenance', __('Departments'), '/HRDepartments.php', 2);

NewScript('HRPositions.php', 15, __('Position management'));
NewMenuItem('hr', 'Maintenance', __('Positions'), '/HRPositions.php', 3);

NewScript('HRPayGrades.php', 15, __('Pay grade management'));
NewMenuItem('hr', 'Maintenance', __('Pay Grades'), '/HRPayGrades.php', 4);

NewScript('HREmployeeCompensation.php', 15, __('Employee compensation management'));
NewMenuItem('hr', 'Transactions', __('Employee Compensation'), '/HREmployeeCompensation.php', 2);

NewScript('HRSalaryIncrease.php', 15, __('Salary increase processing'));
NewMenuItem('hr', 'Transactions', __('Salary Increase'), '/HRSalaryIncrease.php', 3);

NewScript('HRCompReviewCycles.php', 15, __('Compensation review cycles'));
NewMenuItem('hr', 'Maintenance', __('Comp Review Cycles'), '/HRCompReviewCycles.php', 5);

NewScript('HRIncreaseGuidelines.php', 15, __('Increase guidelines'));
NewMenuItem('hr', 'Maintenance', __('Increase Guidelines'), '/HRIncreaseGuidelines.php', 6);

NewScript('HRPerformanceAppraisals.php', 15, __('Performance appraisals'));
NewMenuItem('hr', 'Transactions', __('Performance Appraisals'), '/HRPerformanceAppraisals.php', 4);

NewScript('HRAppraisalEntry.php', 15, __('Appraisal entry form'));
NewScript('HRAppraisalsDue.php', 15, __('Appraisal entry form'));

NewScript('HRMyAppraisals.php', 15, __('My appraisals'));
NewMenuItem('hr', 'Reports', __('My Appraisals'), '/HRMyAppraisals.php', 2);

NewScript('HRPerformanceRatings.php', 15, __('Performance rating scales'));
NewMenuItem('hr', 'Maintenance', __('Performance Ratings'), '/HRPerformanceRatings.php', 7);

NewScript('HRPerformanceGoals.php', 15, __('Performance goals'));
NewMenuItem('hr', 'Maintenance', __('Performance Goals'), '/HRPerformanceGoals.php', 8);

NewScript('HRSkills.php', 15, __('Competency management'));
NewMenuItem('hr', 'Maintenance', __('Skills'), '/HRSkills.php', 9);

NewScript('HREmployeeSkills.php', 15, __('Employee skills'));
NewMenuItem('hr', 'Transactions', __('Employee Skills'), '/HREmployeeSkills.php', 5);

NewScript('HRJobSkills.php', 15, __('Job skills'));
NewMenuItem('hr', 'Maintenance', __('Job Skills'), '/HRJobSkills.php', 10);

NewScript('HRSkillGapAnalysis.php', 15, __('Competency gap analysis'));
NewMenuItem('hr', 'Reports', __('Competency Gap Analysis'), '/HRSkillGapAnalysis.php', 3);

NewScript('HRRequisitions.php', 15, __('Position requisitions'));
NewMenuItem('hr', 'Transactions', __('Requisitions'), '/HRRequisitions.php', 6);

NewScript('HRApplicants.php', 15, __('Applicant management'));
NewMenuItem('hr', 'Transactions', __('Applicants'), '/HRApplicants.php', 7);

NewScript('HRApplicantTracking.php', 15, __('Applicant tracking'));
NewMenuItem('hr', 'Reports', __('Applicant Tracking'), '/HRApplicantTracking.php', 4);

NewScript('HRPositionBudgets.php', 15, __('Position budgets'));
NewMenuItem('hr', 'Maintenance', __('Position Budgets'), '/HRPositionBudgets.php', 11);

NewScript('HRSafetyIncidents.php', 15, __('Safety incident tracking'));
NewMenuItem('hr', 'Transactions', __('Safety Incidents'), '/HRSafetyIncidents.php', 8);

NewScript('HRSystemOptions.php', 15, __('HR system options'));
NewMenuItem('hr', 'Maintenance', __('System Options'), '/HRSystemOptions.php', 12);

NewScript('HRAuditTrail.php', 15, __('HR audit trail'));
NewMenuItem('hr', 'Reports', __('Audit Trail'), '/HRAuditTrail.php', 5);

NewScript('HRPerformanceReviews.php', 15, __('Performance review reports'));
NewMenuItem('hr', 'Reports', __('Performance Reviews'), '/HRPerformanceReviews.php', 6);

CreateTable('hrperformancecriteria', "CREATE TABLE `hrperformancecriteria` (
	`criteriaid` INT(11) NOT NULL AUTO_INCREMENT,
	`criterianame` VARCHAR(100) NOT NULL,
	`description` TEXT,
	`category` VARCHAR(50) DEFAULT NULL,
	`weight` DECIMAL(5,2) DEFAULT 0.00,
	`displayorder` INT(11) DEFAULT 0,
	`isactive` TINYINT(1) NOT NULL DEFAULT 1,
	`createdby` VARCHAR(50) DEFAULT NULL,
	`createddate` DATETIME DEFAULT NULL,
	`modifiedby` VARCHAR(50) DEFAULT NULL,
	`modifieddate` DATETIME DEFAULT NULL,
	PRIMARY KEY (`criteriaid`)
) ");

NewScript('HRPerformanceCriteria.php', 15, __('Performance criteria'));
NewMenuItem('hr', 'Maintenance', __('Performance Criteria'), '/HRPerformanceCriteria.php', 13);

CreateTable('hrratingscales', "CREATE TABLE `hrratingscales` (
	`scaleid` INT(11) NOT NULL AUTO_INCREMENT,
	`scalename` VARCHAR(100) NOT NULL,
	`description` TEXT,
	`minvalue` INT(11) DEFAULT 1,
	`maxvalue` INT(11) DEFAULT 5,
	`active` TINYINT(1) DEFAULT 1,
	`createddate` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
	PRIMARY KEY (`scaleid`),
	UNIQUE KEY `scalename` (`scalename`)
) ");

NewScript('HRRatingScales.php', 15, __('Rating scales'));
NewMenuItem('hr', 'Maintenance', __('Rating Scales'), '/HRRatingScales.php', 14);

// Migrate data from employees table to hremployees table
$SQL = "INSERT INTO hremployees
	(employeenumber, firstname, lastname, email, createdby, hiredate)
	SELECT
		CONCAT('EMP', LPAD(id, 5, '0')) as employeenumber,
		firstname,
		surname as lastname,
		email,
		'system' as createdby,
		CURDATE() as hiredate
	FROM employees
	WHERE NOT EXISTS (
		SELECT 1 FROM hremployees WHERE hremployees.employeenumber = CONCAT('EMP', LPAD(employees.id, 5, '0'))
	)";
$Result = DB_query($SQL, '', '', false, false);

if ($_SESSION['Updates']['Errors'] == 0) {
	UpdateDBNo(basename(__FILE__, '.php'), __('Human Resources Management System - Complete HR functionality'));
}

?>

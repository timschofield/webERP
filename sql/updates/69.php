<?php

/* -----------------------------------------------------------------------
 * Register the CRM module for every security role
 * ----------------------------------------------------------------------- */
NewModule('CRM', 'crm', __('CRM'), 9);

CreateTable('crmindustries', "CREATE TABLE crmindustries (
	industryid INT(11) NOT NULL AUTO_INCREMENT,
	industryname VARCHAR(60) NOT NULL,
	active TINYINT(1) NOT NULL DEFAULT 1,
	PRIMARY KEY (industryid),
	KEY idx_active (active)
)");

NewScript('CRMIndustries.php', 15, __('CRM Industry Maintenance'));
NewMenuItem('CRM', 'Maintenance', __('Industries Maintenance'), '/CRMIndustries.php', 30);

/* debtorsmaster — add CRM profile fields */
AddColumn('industry',      'debtorsmaster', 'varchar(60)',   ' NOT NULL ', "''", 'typeid');
AddColumn('website',       'debtorsmaster', 'varchar(120)',  ' NOT NULL ', "''", 'industry');
AddColumn('leadsource',    'debtorsmaster', 'varchar(40)',   ' NOT NULL ', "''", 'website');
AddColumn('lifecycle',     'debtorsmaster', 'varchar(20)',   ' NOT NULL ', 'customer', 'leadsource');
AddColumn('employeecount', 'debtorsmaster', 'int(11)',       ' NOT NULL ', '0', 'lifecycle');
AddColumn('annualrevenue', 'debtorsmaster', 'decimal(14,2)', ' NOT NULL ', '0', 'employeecount');

/* custcontacts — add extended contact fields */
AddColumn('mobile',     'custcontacts', 'varchar(20)',  ' NOT NULL ', "''", 'phoneno');
AddColumn('department', 'custcontacts', 'varchar(40)',  ' NOT NULL ', "''", 'mobile');
AddColumn('linkedin',   'custcontacts', 'varchar(120)', ' NOT NULL ', "''", 'department');
AddColumn('active',     'custcontacts', 'tinyint(1)',   ' NOT NULL ', '1', 'linkedin');
AddColumn('createdby',  'custcontacts', 'varchar(20)',  ' NOT NULL ', '', 'active');
AddColumn('createdat',  'custcontacts', 'timestamp',     ' NOT NULL ', 'CURRENT_TIMESTAMP', 'createdby');

/* custnotes — extend into a full activity log */
AddColumn('activitytype', 'custnotes', 'varchar(20)',  ' NOT NULL ', 'note',      'priority');
AddColumn('contid',       'custnotes', 'int(11)',       ' NOT NULL ', '0',         'activitytype');
AddColumn('durationmins', 'custnotes', 'int(11)',       ' NOT NULL ', '0',         'contid');
AddColumn('outcome',      'custnotes', 'varchar(20)',   ' NOT NULL ', '',          'durationmins');
AddColumn('followupdate', 'custnotes', 'date',       ' NULL ', '',       'outcome');
AddColumn('status',       'custnotes', 'varchar(20)',   ' NOT NULL ', 'completed', 'followupdate');
AddColumn('createdby',    'custnotes', 'varchar(20)',   ' NOT NULL ', '',          'status');
AddColumn('createdat',    'custnotes', 'timestamp',      ' NOT NULL ', 'CURRENT_TIMESTAMP', 'createdby');

/* crmactivitytypes — reference table for activity type codes */
CreateTable('crmactivitytypes',
	"CREATE TABLE crmactivitytypes (
		typecode  VARCHAR(20)  NOT NULL,
		typename  VARCHAR(40)  NOT NULL,
		iconclass VARCHAR(40)  NOT NULL DEFAULT '',
		active    TINYINT(1)   NOT NULL DEFAULT 1,
		PRIMARY KEY (typecode)
	)"
);

InsertRecord('crmactivitytypes', array('typecode'), array('note'),    array('typecode', 'typename'), array('note',    'Note'));
InsertRecord('crmactivitytypes', array('typecode'), array('call'),    array('typecode', 'typename'), array('call',    'Phone Call'));
InsertRecord('crmactivitytypes', array('typecode'), array('email'),   array('typecode', 'typename'), array('email',   'Email'));
InsertRecord('crmactivitytypes', array('typecode'), array('meeting'), array('typecode', 'typename'), array('meeting', 'Meeting'));
InsertRecord('crmactivitytypes', array('typecode'), array('task'),    array('typecode', 'typename'), array('task',    'Task'));
InsertRecord('crmactivitytypes', array('typecode'), array('demo'),    array('typecode', 'typename'), array('demo',    'Demo'));

NewScript('CRMActivityTypes.php', 15);

// Create crmleadsources table
CreateTable('crmleadsources', "CREATE TABLE crmleadsources (
	sourceid    INT(11)      NOT NULL AUTO_INCREMENT,
	sourcename  VARCHAR(40)  NOT NULL,
	active      TINYINT(1)   NOT NULL DEFAULT 1,
	PRIMARY KEY (sourceid)
)");

// Seed default lead sources
InsertRecord('crmleadsources', array('sourcename'), array('Web'),              array('sourcename'), array('Web'));
InsertRecord('crmleadsources', array('sourcename'), array('Referral'),         array('sourcename'), array('Referral'));
InsertRecord('crmleadsources', array('sourcename'), array('Trade Show'),       array('sourcename'), array('Trade Show'));
InsertRecord('crmleadsources', array('sourcename'), array('Cold Call'),        array('sourcename'), array('Cold Call'));
InsertRecord('crmleadsources', array('sourcename'), array('Social Media'),     array('sourcename'), array('Social Media'));
InsertRecord('crmleadsources', array('sourcename'), array('Advertisement'),    array('sourcename'), array('Advertisement'));
InsertRecord('crmleadsources', array('sourcename'), array('Email Campaign'),   array('sourcename'), array('Email Campaign'));
InsertRecord('crmleadsources', array('sourcename'), array('Other'),            array('sourcename'), array('Other'));

// Create crmleads table
CreateTable('crmleads', "CREATE TABLE crmleads (
	leadid          INT(11)       NOT NULL AUTO_INCREMENT,
	firstname       VARCHAR(40)   NOT NULL DEFAULT '',
	lastname        VARCHAR(40)   NOT NULL DEFAULT '',
	company         VARCHAR(60)   DEFAULT '',
	jobtitle        VARCHAR(40)   DEFAULT '',
	email           VARCHAR(80)   DEFAULT '',
	phone           VARCHAR(20)   DEFAULT '',
	mobile          VARCHAR(20)   DEFAULT '',
	website         VARCHAR(120)  DEFAULT '',
	industry        VARCHAR(60)   DEFAULT '',
	sourceid        INT(11)       DEFAULT 0,
	status          VARCHAR(20)   NOT NULL DEFAULT 'new',
	assignedto      VARCHAR(4)    DEFAULT '',
	estimatedvalue  DECIMAL(12,2) DEFAULT 0,
	currcode        CHAR(3)       DEFAULT '',
	notes           TEXT,
	convertedto     VARCHAR(10)   DEFAULT '',
	convertedat     DATETIME      DEFAULT NULL,
	createdat       TIMESTAMP     NOT NULL DEFAULT CURRENT_TIMESTAMP,
	createdby       VARCHAR(20)   DEFAULT '',
	updatedat       DATETIME      DEFAULT NULL,
	updatedby       VARCHAR(20)   DEFAULT '',
	PRIMARY KEY (leadid),
	KEY idx_status     (status),
	KEY idx_assignedto (assignedto)
)");

// Register new scripts
NewScript('CRMLeadSources.php', 15);
NewScript('CRMLeads.php', 3);
NewScript('CRMLeadEntry.php', 3);
NewScript('CRMLeadInquiry.php', 3);
NewScript('CRMLeadConvert.php', 3);
NewScript('CRMSelectLead.php', 3);

// Renames SelectLead.php to CRMSelectLead.php, creates the crmpipelinestages and
// crmopportunities tables, adds oppid to salesorders, registers the new scripts
// and menu items.

// Rename SelectLead.php to CRMSelectLead.php
RemoveScript('SelectLead.php');
NewScript('CRMSelectLead.php', 3);

// Create crmpipelinestages table
CreateTable('crmpipelinestages', "CREATE TABLE crmpipelinestages (
	stageid        INT(11)      NOT NULL AUTO_INCREMENT,
	stagename      VARCHAR(40)  NOT NULL,
	probability    DECIMAL(5,2) NOT NULL DEFAULT 0,
	sequence       INT(11)      NOT NULL DEFAULT 0,
	isclosed       TINYINT(1)   NOT NULL DEFAULT 0,
	closedoutcome  VARCHAR(10)  NOT NULL DEFAULT '',
	active         TINYINT(1)   NOT NULL DEFAULT 1,
	PRIMARY KEY (stageid)
)");

// Seed default pipeline stages
InsertRecord('crmpipelinestages',
	array('stagename'), array('Prospecting'),
	array('stagename', 'probability', 'sequence', 'isclosed', 'closedoutcome'),
	array('Prospecting', '10', '10', '0', ''));

InsertRecord('crmpipelinestages',
	array('stagename'), array('Qualification'),
	array('stagename', 'probability', 'sequence', 'isclosed', 'closedoutcome'),
	array('Qualification', '20', '20', '0', ''));

InsertRecord('crmpipelinestages',
	array('stagename'), array('Needs Analysis'),
	array('stagename', 'probability', 'sequence', 'isclosed', 'closedoutcome'),
	array('Needs Analysis', '40', '30', '0', ''));

InsertRecord('crmpipelinestages',
	array('stagename'), array('Value Proposition'),
	array('stagename', 'probability', 'sequence', 'isclosed', 'closedoutcome'),
	array('Value Proposition', '60', '40', '0', ''));

InsertRecord('crmpipelinestages',
	array('stagename'), array('Proposal / Quote'),
	array('stagename', 'probability', 'sequence', 'isclosed', 'closedoutcome'),
	array('Proposal / Quote', '75', '50', '0', ''));

InsertRecord('crmpipelinestages',
	array('stagename'), array('Negotiation'),
	array('stagename', 'probability', 'sequence', 'isclosed', 'closedoutcome'),
	array('Negotiation', '90', '60', '0', ''));

InsertRecord('crmpipelinestages',
	array('stagename'), array('Closed Won'),
	array('stagename', 'probability', 'sequence', 'isclosed', 'closedoutcome'),
	array('Closed Won', '100', '70', '1', 'won'));

InsertRecord('crmpipelinestages',
	array('stagename'), array('Closed Lost'),
	array('stagename', 'probability', 'sequence', 'isclosed', 'closedoutcome'),
	array('Closed Lost', '0', '80', '1', 'lost'));

// Create crmopportunities table
CreateTable('crmopportunities', "CREATE TABLE crmopportunities (
	oppid           INT(11)       NOT NULL AUTO_INCREMENT,
	oppname         VARCHAR(80)   NOT NULL,
	debtorno        VARCHAR(10)   NOT NULL DEFAULT '',
	leadid          INT(11)       NOT NULL DEFAULT 0,
	stageid         INT(11)       NOT NULL DEFAULT 1,
	probability     DECIMAL(5,2)  NOT NULL DEFAULT 0,
	amount          DECIMAL(14,2) NOT NULL DEFAULT 0,
	currcode        CHAR(3)       NOT NULL DEFAULT '',
	expectedclose   DATE          DEFAULT NULL,
	assignedto      VARCHAR(4)    NOT NULL DEFAULT '',
	description     TEXT,
	outcome         VARCHAR(10)   NOT NULL DEFAULT 'open',
	lostnotes       TEXT,
	closedat        DATETIME      DEFAULT NULL,
	createdat       TIMESTAMP     NOT NULL DEFAULT CURRENT_TIMESTAMP,
	createdby       VARCHAR(20)   NOT NULL DEFAULT '',
	updatedat       DATETIME      DEFAULT NULL,
	updatedby       VARCHAR(20)   NOT NULL DEFAULT '',
	PRIMARY KEY (oppid),
	KEY idx_stageid      (stageid),
	KEY idx_assignedto   (assignedto),
	KEY idx_outcome      (outcome),
	KEY idx_expectedclose (expectedclose),
	KEY idx_debtorno     (debtorno),
	KEY idx_leadid       (leadid)
)");

// Add oppid column to salesorders
AddColumn('oppid', 'salesorders', 'INT(11)', 'NOT NULL', '0', 'salesperson');

// 5. Register new scripts
NewScript('CRMPipelineStages.php', 15);
NewScript('CRMOpportunities.php', 3);
NewScript('CRMOpportunityEntry.php', 3);
NewScript('CRMOpportunityInquiry.php', 3);
NewScript('CRMPipeline.php', 3);
NewScript('CRMForecast.php', 3);

/* crmactivities — unified activity log linked to customers, leads, and opportunities */
CreateTable('crmactivities',
	"CREATE TABLE crmactivities (
		activityid      INT(11)      NOT NULL AUTO_INCREMENT,
		activitytype    VARCHAR(20)  NOT NULL DEFAULT 'note',
		subject         VARCHAR(120) NOT NULL DEFAULT '',
		description     TEXT,
		debtorno        VARCHAR(10)  DEFAULT '',
		leadid          INT(11)      DEFAULT 0,
		oppid           INT(11)      DEFAULT 0,
		contid          INT(11)      DEFAULT 0,
		assignedto      VARCHAR(4)   DEFAULT '',
		scheduledstart  DATETIME     DEFAULT NULL,
		scheduledend    DATETIME     DEFAULT NULL,
		durationmins    INT(11)      DEFAULT 0,
		status          VARCHAR(20)  NOT NULL DEFAULT 'planned',
		outcome         VARCHAR(20)  DEFAULT '',
		outcomedesc     TEXT,
		followupdate    DATE         DEFAULT NULL,
		createdat       TIMESTAMP     NOT NULL DEFAULT CURRENT_TIMESTAMP,
		createdby       VARCHAR(20)  DEFAULT '',
		completedat     DATETIME     DEFAULT NULL,
		PRIMARY KEY (activityid),
		KEY idx_debtorno    (debtorno),
		KEY idx_leadid      (leadid),
		KEY idx_oppid       (oppid),
		KEY idx_assignedto  (assignedto),
		KEY idx_scheduled   (scheduledstart),
		KEY idx_status      (status)
	)"
);

NewScript('CRMActivities.php', 3);
NewScript('CRMActivityEntry.php', 3);
NewScript('CRMCalendar.php', 3);
NewScript('CRMMyActivities.php', 3);

/* crmemailtemplates — reusable email templates with merge-field placeholders */
CreateTable('crmemailtemplates',
	"CREATE TABLE crmemailtemplates (
		templateid    INT(11)       NOT NULL AUTO_INCREMENT,
		templatename  VARCHAR(80)   NOT NULL,
		subject       VARCHAR(120)  NOT NULL DEFAULT '',
		body          TEXT          NOT NULL,
		active        TINYINT(1)    NOT NULL DEFAULT 1,
		createdat     TIMESTAMP     NOT NULL DEFAULT CURRENT_TIMESTAMP,
		createdby     VARCHAR(20)   NOT NULL DEFAULT '',
		PRIMARY KEY (templateid),
		KEY idx_active (active)
	)"
);

/* Seed three commonly-used templates. */
InsertRecord('crmemailtemplates',
	array('templatename'),
	array('Introduction'),
	array('templatename', 'subject', 'body', 'active', 'createdby'),
	array(
		'Introduction',
		'Introduction from {CompanyName}',
		"Hi {CustomerName},\n\nThank you for your interest in {CompanyName}. My name is {SalesmanName} and I will be your dedicated contact here.\n\nPlease do not hesitate to get in touch if you have any questions — I am happy to help.\n\nKind regards,\n{SalesmanName}",
		'1',
		'system'
	)
);

InsertRecord('crmemailtemplates',
	array('templatename'),
	array('Follow-up'),
	array('templatename', 'subject', 'body', 'active', 'createdby'),
	array(
		'Follow-up',
		'Following up — {CompanyName}',
		"Hi {CustomerName},\n\nI am following up on our recent conversation. I wanted to make sure you had everything you need and to answer any questions that may have come up.\n\nPlease feel free to contact me at any time.\n\nKind regards,\n{SalesmanName}",
		'1',
		'system'
	)
);

InsertRecord('crmemailtemplates',
	array('templatename'),
	array('Proposal'),
	array('templatename', 'subject', 'body', 'active', 'createdby'),
	array(
		'Proposal',
		'Proposal from {CompanyName} — {Date}',
		"Hi {CustomerName},\n\nThank you for the opportunity to present our proposal. I am pleased to outline how {CompanyName} can meet your requirements.\n\nPlease review the attached proposal and let me know if you would like to discuss any aspect of it.\n\nKind regards,\n{SalesmanName}",
		'1',
		'system'
	)
);

NewScript('CRMEmailTemplates.php', 15);
NewScript('CRMSendEmail.php', 3);

NewScript('CRMDashboard.php', 3);
NewScript('CRMLeadReport.php', 3);
NewScript('CRMOpportunityReport.php', 3);
NewScript('CRMActivityReport.php', 3);
NewScript('CRMCustomerHealthReport.php', 3);


AddColumn('address1', 'crmleads', 'VARCHAR(40)', 'NOT NULL', '', 'website');
AddColumn('address2', 'crmleads', 'VARCHAR(40)', 'NOT NULL', '', 'address1');
AddColumn('address3', 'crmleads', 'VARCHAR(40)', 'NOT NULL', '', 'address2');
AddColumn('address4', 'crmleads', 'VARCHAR(50)', 'NOT NULL', '', 'address3');
AddColumn('address5', 'crmleads', 'VARCHAR(20)', 'NOT NULL', '', 'address4');
AddColumn('address6', 'crmleads', 'VARCHAR(40)', 'NOT NULL', '', 'address5');


/* -----------------------------------------------------------------------
 *    Add all CRM items under the new CRM module
 *    NewMenuItem($Link, $Section, $Caption, $URL, $Sequence)
 * ----------------------------------------------------------------------- */

/* --- Transactions --- */
NewMenuItem('CRM', 'Transactions', __('My Activities'),       '/CRMMyActivities.php',       10);
NewMenuItem('CRM', 'Transactions', __('Log Activity'),        '/CRMActivityEntry.php',       20);
NewMenuItem('CRM', 'Transactions', __('Compose Email'),       '/CRMSendEmail.php',           30);
NewMenuItem('CRM', 'Transactions', __('Leads'),               '/CRMLeads.php',               40);
NewMenuItem('CRM', 'Transactions', __('Pipeline'),            '/CRMPipeline.php',            50);
NewMenuItem('CRM', 'Transactions', __('Opportunities'),       '/CRMOpportunities.php',       60);
NewMenuItem('CRM', 'Transactions', __('Activities'),          '/CRMActivities.php',          70);
NewMenuItem('CRM', 'Transactions', __('Calendar'),            '/CRMCalendar.php',            80);

/* --- Reports --- */
NewMenuItem('CRM', 'Reports', __('Forecast'),                 '/CRMForecast.php',            10);
NewMenuItem('CRM', 'Reports', __('CRM Dashboard'),            '/CRMDashboard.php',           20);
NewMenuItem('CRM', 'Reports', __('Lead Report'),              '/CRMLeadReport.php',          30);
NewMenuItem('CRM', 'Reports', __('Opportunity Report'),       '/CRMOpportunityReport.php',   40);
NewMenuItem('CRM', 'Reports', __('Activity Report'),          '/CRMActivityReport.php',      50);
NewMenuItem('CRM', 'Reports', __('Customer Health'),          '/CRMCustomerHealthReport.php', 60);

/* --- Maintenance --- */
NewMenuItem('CRM', 'Maintenance', __('Activity Types'),       '/CRMActivityTypes.php',       10);
NewMenuItem('CRM', 'Maintenance', __('Lead Sources'),         '/CRMLeadSources.php',         20);
NewMenuItem('CRM', 'Maintenance', __('Pipeline Stages'),      '/CRMPipelineStages.php',      30);
NewMenuItem('CRM', 'Maintenance', __('Email Templates'),      '/CRMEmailTemplates.php',      40);

/* -----------------------------------------------------------------------
 *    Update www_users.modulesallowed for every existing user.
 *
 *    The string is a comma-separated list of "1" or "0" values, one per
 *    module in sequence order, with a trailing comma.  After the HR module
 *    was added (update 53/54) all users have 13 entries (26 chars).
 *
 *    CRM is inserted at position 8 (0-indexed, after the 8 modules at
 *    sequences 1-8).  The first 8 entries occupy characters 0-15 of the
 *    string (each entry is "1," = 2 chars).  We insert "1," at offset 16
 *    to give every user access to the new CRM module by default.
 *
 *    Users who already have 14 or more entries (28+ chars) are skipped so
 *    the update is safe to re-run.
 * ----------------------------------------------------------------------- */
$SQL = "SELECT userid, modulesallowed FROM www_users";
$Result = DB_query($SQL);

while ($MyRow = DB_fetch_array($Result)) {
	if (mb_strlen($MyRow['modulesallowed']) < 28) {
		$StringLength = mb_strlen($MyRow['modulesallowed']);
		$NewModulesAllowed = mb_substr($MyRow['modulesallowed'], 0, 16)
			. '1,'
			. mb_substr($MyRow['modulesallowed'], 16, $StringLength - 16);
		UpdateField('www_users', 'modulesallowed', $NewModulesAllowed, 'userid="' . $MyRow['userid'] . '"');
	}
}

if ($_SESSION['Updates']['Errors'] == 0) {
	UpdateDBNo(basename(__FILE__, '.php'), __('CRM Module'));
}

<?php
/**
 * Database Update Script for Forecast Management System
 * Add this to sql/updates/ folder with next sequential number
 */

// Create Forecast Header table
CreateTable('forecastheader', "CREATE TABLE IF NOT EXISTS `forecastheader` (
  `forecastid` int(11) NOT NULL AUTO_INCREMENT,
  `stockid` varchar(20) NOT NULL,
  `locationcode` varchar(5) NOT NULL DEFAULT '',
  `forecasttype` varchar(2) NOT NULL DEFAULT 'DT',
  `forecastmethod` tinyint(2) NOT NULL DEFAULT 1,
  `description` varchar(100) NOT NULL DEFAULT '',
  `startdate` date NOT NULL,
  `enddate` date DEFAULT NULL,
  `active` tinyint(1) NOT NULL DEFAULT 1,
  `lastgenerated` datetime DEFAULT NULL,
  `createdby` varchar(20) NOT NULL,
  `createdon` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `modifiedby` varchar(20) DEFAULT NULL,
  `modifiedon` datetime DEFAULT NULL,
  PRIMARY KEY (`forecastid`),
  KEY `stockid` (`stockid`),
  KEY `locationcode` (`locationcode`),
  KEY `forecasttype` (`forecasttype`),
  KEY `active` (`active`)
)");

// Create Forecast Details table
CreateTable('forecastdetails', "CREATE TABLE IF NOT EXISTS `forecastdetails` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `forecastid` int(11) NOT NULL,
  `perioddate` date NOT NULL,
  `periodnum` int(11) NOT NULL,
  `forecastqty` decimal(20,4) NOT NULL DEFAULT 0.0000,
  `actualqty` decimal(20,4) NOT NULL DEFAULT 0.0000,
  `revisedqty` decimal(20,4) DEFAULT NULL,
  `variance` decimal(20,4) DEFAULT NULL,
  `mad` decimal(20,4) DEFAULT NULL,
  `poa` decimal(10,4) DEFAULT NULL,
  `confidence` decimal(10,4) DEFAULT NULL,
  `notes` text,
  PRIMARY KEY (`id`),
  UNIQUE KEY `forecast_period` (`forecastid`,`perioddate`),
  KEY `forecastid` (`forecastid`),
  KEY `perioddate` (`perioddate`)
)");

AddConstraint('forecastdetails', 'forecastdetails_ibfk_1', 'forecastid', 'forecastheader', 'forecastid', 'CASCADE');

// Create Forecast Summary table
CreateTable('forecastsummary', "CREATE TABLE IF NOT EXISTS `forecastsummary` (
  `summaryid` int(11) NOT NULL AUTO_INCREMENT,
  `summarycode` varchar(20) NOT NULL,
  `summarydesc` varchar(100) NOT NULL,
  `categorycode` varchar(20) DEFAULT NULL,
  `customertype` varchar(2) DEFAULT NULL,
  `area` varchar(3) DEFAULT NULL,
  `salesperson` varchar(10) DEFAULT NULL,
  `active` tinyint(1) NOT NULL DEFAULT 1,
  `createdby` varchar(20) NOT NULL,
  `createdon` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`summaryid`),
  UNIQUE KEY `summarycode` (`summarycode`),
  KEY `categorycode` (`categorycode`),
  KEY `active` (`active`)
)");

// Create Forecast Summary Details table
CreateTable('forecastsummarydetails', "CREATE TABLE IF NOT EXISTS `forecastsummarydetails` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `summaryid` int(11) NOT NULL,
  `perioddate` date NOT NULL,
  `periodnum` int(11) NOT NULL,
  `forecastqty` decimal(20,4) NOT NULL DEFAULT 0.0000,
  `forecastvalue` decimal(20,4) NOT NULL DEFAULT 0.0000,
  `actualqty` decimal(20,4) NOT NULL DEFAULT 0.0000,
  `actualvalue` decimal(20,4) NOT NULL DEFAULT 0.0000,
  `revisedqty` decimal(20,4) DEFAULT NULL,
  `revisedvalue` decimal(20,4) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `summary_period` (`summaryid`,`perioddate`),
  KEY `summaryid` (`summaryid`),
  KEY `perioddate` (`perioddate`)
)");

AddConstraint('forecastsummarydetails', 'forecastsummarydetails_ibfk_1', 'summaryid', 'forecastsummary', 'summaryid', 'CASCADE');

// Create Forecast Constants table
CreateTable('forecastconstants', "CREATE TABLE IF NOT EXISTS `forecastconstants` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `stockid` varchar(20) NOT NULL,
  `locationcode` varchar(5) NOT NULL DEFAULT '',
  `smoothingalpha` decimal(5,4) NOT NULL DEFAULT 0.3000,
  `smoothingbeta` decimal(5,4) NOT NULL DEFAULT 0.3000,
  `smoothinggamma` decimal(5,4) NOT NULL DEFAULT 0.3000,
  `periodsaverage` int(11) NOT NULL DEFAULT 4,
  `periodshistory` int(11) NOT NULL DEFAULT 12,
  `safetystock` decimal(20,4) NOT NULL DEFAULT 0.0000,
  `safetystockpct` decimal(10,2) NOT NULL DEFAULT 0.00,
  `outlierfilter` tinyint(1) NOT NULL DEFAULT 1,
  `outlierdeviation` int(11) NOT NULL DEFAULT 2,
  PRIMARY KEY (`id`),
  UNIQUE KEY `stock_location` (`stockid`,`locationcode`),
  KEY `stockid` (`stockid`)
)");

// Create Forecast Prices table
CreateTable('forecastprices', "CREATE TABLE IF NOT EXISTS `forecastprices` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `forecastid` int(11) NOT NULL,
  `effectivedate` date NOT NULL,
  `unitprice` decimal(20,4) NOT NULL DEFAULT 0.0000,
  `costprice` decimal(20,4) NOT NULL DEFAULT 0.0000,
  `currencycode` char(3) NOT NULL DEFAULT '',
  PRIMARY KEY (`id`),
  KEY `forecastid` (`forecastid`,`effectivedate`)
)");

AddConstraint('forecastprices', 'forecastprices_ibfk_1', 'forecastid', 'forecastheader', 'forecastid', 'CASCADE');

// Create Forecast Methods table
CreateTable('forecastmethods', "CREATE TABLE IF NOT EXISTS `forecastmethods` (
  `methodid` tinyint(2) NOT NULL,
  `methodname` varchar(50) NOT NULL,
  `methoddesc` varchar(255) NOT NULL,
  `requireshistory` int(11) NOT NULL DEFAULT 12,
  `active` tinyint(1) NOT NULL DEFAULT 1,
  PRIMARY KEY (`methodid`)
)");

// Insert forecast methods
$Methods = array(
	array(1, __('Percent Over Last Year'), __('Increases last year forecast by a percentage'), 12),
	array(2, __('Calculated Percent Over LY'), __('Calculates percentage increase from historical data'), 24),
	array(3, __('Last Year to This Year'), __('Uses last year actual as this year forecast'), 12),
	array(4, __('Moving Average'), __('Simple moving average of recent periods'), 4),
	array(5, __('Linear Approximation'), __('Linear trend projection'), 6),
	array(6, __('Least Squares Regression'), __('Statistical linear regression'), 12),
	array(7, __('Second Degree Approximation'), __('Polynomial regression (quadratic)'), 12),
	array(8, __('Flexible Method'), __('Custom formula-based forecasting'), 12),
	array(9, __('Weighted Moving Average'), __('Moving average with weights'), 4),
	array(10, __('Linear Smoothing'), __('Simple exponential smoothing'), 12),
	array(11, __('Exponential Smoothing'), __('Exponential smoothing with trend'), 12),
	array(12, __('Exp Smooth Trend/Season'), __('Triple exponential smoothing (Holt-Winters)'), 24)
);

foreach ($Methods as $method) {
    InsertRecord('forecastmethods', array('methodname'), array($method[1]), array('methodid', 'methodname', 'methoddesc', 'requireshistory', 'active'), array($method[0],$method[1],$method[2] ,$method[3], 1));
}

// Create Forecast Metrics table
CreateTable('forecastmetrics', "CREATE TABLE IF NOT EXISTS `forecastmetrics` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `forecastid` int(11) NOT NULL,
  `perioddate` date NOT NULL,
  `mad` decimal(20,4) DEFAULT NULL,
  `mse` decimal(20,4) DEFAULT NULL,
  `rmse` decimal(20,4) DEFAULT NULL,
  `mape` decimal(10,4) DEFAULT NULL,
  `poa` decimal(10,4) DEFAULT NULL,
  `bias` decimal(20,4) DEFAULT NULL,
  `trackingsignal` decimal(10,4) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `forecast_period` (`forecastid`,`perioddate`),
  KEY `forecastid` (`forecastid`)
)");

AddConstraint('forecastmetrics', 'forecastmetrics_ibfk_1', 'forecastid', 'forecastheader', 'forecastid', 'CASCADE');

// Create Forecast Inclusion Rules table
CreateTable('forecastinclusionrules', "CREATE TABLE IF NOT EXISTS `forecastinclusionrules` (
  `ruleid` int(11) NOT NULL AUTO_INCREMENT,
  `rulename` varchar(50) NOT NULL,
  `includetype` enum('demand','supply','both') NOT NULL DEFAULT 'demand',
  `ordertypes` varchar(100) DEFAULT NULL,
  `includebackorders` tinyint(1) NOT NULL DEFAULT 0,
  `includeworkorders` tinyint(1) NOT NULL DEFAULT 0,
  `includepurchaseorders` tinyint(1) NOT NULL DEFAULT 0,
  `minstockvalue` decimal(20,4) DEFAULT NULL,
  `active` tinyint(1) NOT NULL DEFAULT 1,
  PRIMARY KEY (`ruleid`),
  UNIQUE KEY `rulename` (`rulename`)
)");

// Create Forecast Simulation table
CreateTable('forecastsimulation', "CREATE TABLE IF NOT EXISTS `forecastsimulation` (
  `simulationid` int(11) NOT NULL AUTO_INCREMENT,
  `forecastid` int(11) NOT NULL,
  `simulationname` varchar(50) NOT NULL,
  `adjustmentpct` decimal(10,4) NOT NULL DEFAULT 0.0000,
  `adjustmentqty` decimal(20,4) NOT NULL DEFAULT 0.0000,
  `startdate` date NOT NULL,
  `enddate` date NOT NULL,
  `notes` text,
  `createdon` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `createdby` varchar(20) NOT NULL,
  PRIMARY KEY (`simulationid`),
  KEY `forecastid` (`forecastid`)
)");

AddConstraint('forecastsimulation', 'forecastsimulation_ibfk_1', 'forecastid', 'forecastheader', 'forecastid', 'CASCADE');

// Create Forecast Sales History cache table
CreateTable('forecastsaleshistory', "CREATE TABLE IF NOT EXISTS `forecastsaleshistory` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `stockid` varchar(20) NOT NULL,
  `locationcode` varchar(5) NOT NULL DEFAULT '',
  `customerid` varchar(10) DEFAULT NULL,
  `customertype` varchar(2) DEFAULT NULL,
  `area` varchar(3) DEFAULT NULL,
  `salesperson` varchar(10) DEFAULT NULL,
  `perioddate` date NOT NULL,
  `quantity` decimal(20,4) NOT NULL DEFAULT 0.0000,
  `amount` decimal(20,4) NOT NULL DEFAULT 0.0000,
  `cost` decimal(20,4) NOT NULL DEFAULT 0.0000,
  PRIMARY KEY (`id`),
  KEY `stockid` (`stockid`,`locationcode`,`perioddate`),
  KEY `perioddate` (`perioddate`),
  KEY `composite` (`stockid`,`customertype`,`area`,`perioddate`)
)");

NewModule('for', 'Fcst', __('Forecasting'), 7);

// Register new scripts
NewScript('ForecastDashboard.php', 11, __('Forecast management dashboard'));
NewMenuItem('for', 'Maintenance', __('Forecast Dashboard'), '/ForecastDashboard.php', 1);
NewScript('ForecastGeneration.php', 11, __('Generate forecasts for items'));
NewMenuItem('for', 'Transactions', __('Generate forecasts for items'), '/ForecastGeneration.php', 1);
NewScript('ForecastReview.php', 11, __('Review and revise forecasts'));
NewMenuItem('for', 'Reports', __('Review and revise forecasts'), '/ForecastReview.php', 1);
NewScript('ForecastSummary.php', 11, __('Manage summary forecasts'));
NewMenuItem('for', 'Reports', __('Manage summary forecasts'), '/ForecastSummary.php', 2);
NewScript('ForecastSummaryReview.php', 11, __('Review summary forecast details'));
NewMenuItem('for', 'Reports', __('Review summary forecast details'), '/ForecastSummaryReview.php', 3);
NewScript('ForecastExtractActuals.php', 11, __('Extract sales history for forecasting'));
NewMenuItem('for', 'Transactions', __('Extract sales history'), '/ForecastExtractActuals.php', 2);
NewScript('ForecastAccuracy.php', 11, __('Forecast accuracy reporting'));
NewMenuItem('for', 'Reports', __('Forecast accuracy'), '/ForecastAccuracy.php', 4);
NewScript('ForecastInquiry.php', 11, __('Query and view forecasts'));
NewMenuItem('for', 'Reports', __('View forecasts'), '/ForecastInquiry.php', 5);
NewScript('ForecastConstants.php', 11, __('Configure forecast parameters'));
NewMenuItem('for', 'Maintenance', __('Forecast parameters'), '/ForecastConstants.php', 2);
NewScript('ForecastBatchGeneration.php', 11, __('Batch forecast generation'));
NewMenuItem('for', 'Transactions', __('Forecast batch generation'), '/ForecastBatchGeneration.php', 3);
NewScript('ForecastComparison.php', 11, __('Compare forecasting methods'));
NewMenuItem('for', 'Reports', __('Forecast comparison'), '/ForecastComparison.php', 6);
NewScript('ForecastToMRP.php', 11, __('Update forecasts to MRP'));
NewMenuItem('for', 'Maintenance', __('Update forecasts to MRP'), '/ForecastToMRP.php', 3);

RemoveMenuItem('manuf', 'Maintenance', 'Auto Create Master Schedule', '/MRPCreateDemands.php');
NewMenuItem('for', 'Maintenance', __('Auto Create Master Schedule'), '/MRPCreateDemands.php', 4);
RemoveMenuItem('manuf', 'Maintenance', 'Master Schedule', '/MRPDemands.php');
NewMenuItem('for', 'Maintenance', __('Master Schedule'), '/MRPDemands.php', 5);
RemoveMenuItem('manuf', 'Maintenance', 'MRP Calculation', '/MRP.php');
NewMenuItem('for', 'Maintenance', __('MRP Calculation'), '/MRP.php', 6);

RemoveMenuItem('manuf', 'Reports', 'MRP', '/MRPReport.php');
NewMenuItem('for', 'Reports', __('MRP'), '/MRPReport.php', 7);
RemoveMenuItem('manuf', 'Reports', 'MRP Reschedules Required', '/MRPReschedules.php');
NewMenuItem('for', 'Reports', __('MRP Reschedules Required'), '/MRPReschedules.php', 8);
RemoveMenuItem('manuf', 'Reports', 'MRP Shortages', '/MRPShortages.php');
NewMenuItem('for', 'Reports', __('MRP Shortages'), '/MRPShortages.php', 9);
RemoveMenuItem('manuf', 'Reports', 'MRP Suggested Purchase Orders', '/MRPPlannedPurchaseOrders.php');
NewMenuItem('for', 'Reports', __('MRP Suggested Purchase Orders'), '/MRPPlannedPurchaseOrders.php', 10);
RemoveMenuItem('manuf', 'Reports', 'MRP Suggested Work Orders', '/MRPPlannedWorkOrders.php');
NewMenuItem('for', 'Reports', __('MRP Suggested Work Orders'), '/MRPPlannedWorkOrders.php', 11);

UpdateDBNo(basename(__FILE__, '.php'), __('Forecast Management System - Statistical forecasting for inventory planning'));

AddColumn('loccode', 'salesanalysis', 'varchar(5)', ' NOT NULL ', "''", 'area');
AddColumn('custtype', 'salesanalysis', 'char(2)', ' NOT NULL ', "''", 'custbranch');

?>

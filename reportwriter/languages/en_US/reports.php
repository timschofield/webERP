<?php
/* Version 1.0 */

/**************************************************************************************
Locale language file for reports. This file can be broken out for apps that use
DEFINE statements. The report scripts and forms obtain their text definitions here.
This first part of this file may be broken out to a language file for apps that don't
use getttext for translation.
***************************************************************************************/

// Message definitions
define('RPT_SAVEDEF',_('The name you entered is a default report. Please enter a new My Report name.'));
define('RPT_SAVEDUP',_('This report name already exists! Press Replace to overwrite or enter a new name and press continue.'));
define('RPT_DUPDB',_('There is an error in your database selection. Check your database names and link equations.'));
define('RPT_BADFLD',_('There is an error in your database field or description. Pleae check and try again.'));
define('RPT_NODATA',_('There was not any data in this report based on the criteria provided!'));
define('RPT_NOROWS',_('You do not have any rows to show!'));
define('RPT_DEFDEL',_('If you replace this report with one of the same name, the original report will be erased!'));
define('RPT_WASSAVED',_(' was saved and copied to report: '));
define('RPT_UPDATED',_('The report name has been updated!'));
define('RPT_NORPT',_('No report name was selected to perform this operation.'));
define('RPT_REPDUP',_('The name you entered is already in use. Please enter a new report name!'));
define('RPT_REPDEL',_('Press OK to delete this report.'));
define('RPT_REPOVER',_('Press OK to overwrite this report.'));
define('RPT_NOSHOW',_('There are no reports to show!'));
define('RPT_RPTENTER',_('Enter a name for this report.'));
define('RPT_RPTNOENTER',_('(Leave blank to use default report name from import file)'));
define('RPT_MAX30',_('(maximum 30 characters)'));
define('RPT_RPTGRP',_('Enter the group this form is a part of:'));
define('RPT_FILEIMP',_('Please select a report file to import'));
define('RPT_DEFIMP',_('Select a default report to import.'));
define('RPT_RPTBROWSE',_('Or browse for a report to upload.'));

// Error messages for importing reports
define('RPT_IMP_ERMSG1',_('The filesize exceeds the upload_max_filesize directive in you php.ini settings.'));
define('RPT_IMP_ERMSG2',_('The filesize exceeds the MAX_FLE_SIZE directive in the webERP form.'));
define('RPT_IMP_ERMSG3',_('The file was not completely uploaded. Please retry.'));
define('RPT_IMP_ERMSG4',_('No file was selected to upload.'));
define('RPT_IMP_ERMSG5',_('Unknown php upload error, php returned error # '));
define('RPT_IMP_ERMSG6',_('This file is not reported by the server as a text file.'));
define('RPT_IMP_ERMSG7',_('The uploaded file does not conatin any data!'));
define('RPT_IMP_ERMSG8',_('webERP could not find a valid report to import in the uploaded file!'));
define('RPT_IMP_ERMSG9',_(' was successfully imported!'));
define('RPT_IMP_ERMSG10',_('There was an unexpected error uploading the file!'));

// General definitions
define('RPT_ACTIVE',_('Active'));
define('RPT_ALL',_('All'));
define('RPT_ALIGN',_('Align'));
define('RPT_ASSEMBLY',_('Assembly'));
define('RPT_BOTTOM',_('Bottom'));
define('RPT_BREAK',_('Break'));
define('RPT_CENTER',_('Center'));
define('RPT_COLUMN',_('Column'));
define('RPT_COLOR',_('Color'));
define('RPT_DATE',_('Date'));
define('RPT_DEFAULT',_('Default'));
define('RPT_EQUAL',_('Equal'));
define('RPT_FALSE',_('False'));
define('RPT_FIELDS',_('Fields'));
define('RPT_FILTER',_('Filter'));
define('RPT_FLDNAME',_('Field Name'));
define('RPT_FROM',_('From'));
define('RPT_FONT',_('Font'));
define('RPT_FOURTH',_('Fourth'));
define('RPT_GROUP',_('Group'));
define('RPT_INACTIVE',_('Inactive'));
define('RPT_LEFT',_('Left'));
define('RPT_MOVE',_('Move'));
define('RPT_NO',_('No'));
define('RPT_NONE',_('None'));
define('RPT_ORDER',_('Order'));
define('RPT_PRIMARY',_('Primary'));
define('RPT_PRINTED',_('Printed'));
define('RPT_RANGE',_('Range'));
define('RPT_RIGHT',_('Right'));
define('RPT_SHOW',_('Show'));
define('RPT_SECOND',_('Second'));
define('RPT_SEQ',_('Sequence'));
define('RPT_SIZE',_('Size'));
define('RPT_SORT',_('Sort'));
define('RPT_STOCK',_('Stock'));
define('RPT_THIRD',_('Third'));
define('RPT_TO',_('To'));
define('RPT_TOP',_('Top'));
define('RPT_TOTAL',_('Total'));
define('RPT_TRUE',_('True'));
define('RPT_TRUNC',_('Truncate Long Descriptions'));
define('RPT_TYPE',_('Type'));
define('RPT_UNPRINTED',_('Unprinted'));
define('RPT_YES',_('Yes'));

// Report page title definitions
define('RPT_REPORT',_('Report: '));
define('RPT_RPRBLDR',_('Report Builder - '));
define('RPT_ADMSTP1',_('Report Builder Menu'));
define('RPT_ADMSTP2',_('Report Builder - Step 2'));
define('RPT_ADMSTP3',_('Report Builder - Step 3'));
define('RPT_ADMSTP4',_('Report Builder - Step 4'));
define('RPT_ADMSTP5',_('Report Builder - Step 5'));
define('RPT_ADMSTP6',_('Report Builder - Step 6'));
define('RPT_ADMSTP7',_('Report Builder - Import Report'));
define('RPT_MENU',_('Reports Menu'));
define('RPT_CRITERIA',_('Report Criteria'));
define('RPT_PAGESAVE',_('Save Report'));
define('RPT_PAGESETUP',_('Report Page Setup'));

// Form page title definitions
define('FORM_ADMSTP1',_('Form Builder Menu'));
define('FORM_ADMSTP2',_('Form Builder - Step 2'));
define('FORM_ADMSTP3',_('Form Builder - Step 3'));
define('FORM_ADMSTP4',_('Form Builder - Step 4'));
define('FORM_ADMSTP5',_('Form Builder - Step 5'));
define('FORM_ADMSTP6',_('Form Builder - Step 6'));

// Button definitions - General
define('RPT_BTN_ADDNEW',_('Add New'));
define('RPT_BTN_BACK',_('Go Back'));
define('RPT_BTN_CANCEL',_('Cancel'));
define('RPT_BTN_CHANGE',_('Change'));
define('RPT_BTN_CONT',_('Continue'));
define('RPT_BTN_COPY',_('Copy'));
define('RPT_BTN_CPYRPT',_('Copy To My Reports'));
define('RPT_BTN_CRIT',_('Criteria Setup'));
define('RPT_BTN_DB',_('Database Setup'));
define('RPT_BTN_DEL',_('Delete'));
define('RPT_BTN_DELRPT',_('Delete Report'));
define('RPT_BTN_EDIT',_('Edit'));
define('RPT_BTN_EXPCSV',_('Export CSV'));
define('RPT_BTN_EXPORT',_('Export'));
define('RPT_BTN_EXPPDF',_('Export PDF'));
define('RPT_BTN_FINISH',_('Finish'));
define('RPT_BTN_FLDSETUP',_('Field Setup'));
define('RPT_BTN_IMPORT',_('Import'));
define('RPT_BTN_MAN',_('Report Builder Manual'));
define('RPT_BTN_NEWRPT',_('Create New Report'));
define('RPT_BTN_PGSETUP',_('Page Setup'));
define('RPT_BTN_REPLACE',_('Replace'));
define('RPT_BTN_RENAME',_('Rename'));
define('RPT_BTN_RESTDEF',_('Restore Defaults'));
define('RPT_BTN_SAVE',_('Save'));
define('RPT_BTN_UPDATE',_('Update'));
define('RPT_RPTFILTER',_('Report Filters: '));
define('RPT_GROUPBY',_('Grouped by:'));
define('RPT_SORTBY',_('Sorted by:'));
define('RPT_DATERANGE',_('Date Range:'));
define('RPT_CRITBY',_('Filters:'));

// Report  Specific
define('RPT_ADMIN',_('Administrator Page'));
define('RPT_COL1W',_('Column 1'));
define('RPT_COL2W',_('Column 2'));
define('RPT_COL3W',_('Column 3'));
define('RPT_COL4W',_('Column 4'));
define('RPT_COL5W',_('Column 5'));
define('RPT_COL6W',_('Column 6'));
define('RPT_COL7W',_('Column 7'));
define('RPT_COL8W',_('Column 8'));
define('RPT_CRITTYPE',_('Type of Criteria'));
define('RPT_CWDEF',_('Column widths - mm (0 for same as prior column)'));
define('RPT_CUSTRPT',_('Custom Reports'));
define('RPT_DATEDEF',_('Default Date Selected'));
define('RPT_DATEFNAME',_('Date Fieldname (table.fieldname)'));
define('RPT_DATEINFO',_('Report Date Information'));
define('RPT_DATEINST',_('Uncheck all boxes for date independent reports; leave Date Fieldname empty'));
define('RPT_DATELIST',_('Date Field List<br>(check all that apply)'));
define('RPT_DEFRPT',_('Default Reports'));
define('RPT_ENTRFLD',_('Enter a New Field'));
define('RPT_FLDLIST',_('Field List'));
define('RPT_GRPLIST',_('Grouping List'));
define('RPT_LINKEQ',_('Link Equation (SQL Syntax)<br>example: tablename1.fieldname1=tablename2.fieldname2'));
define('RPT_MYRPT',_('My Reports'));
define('RPT_DISPNAME',_('Name to Display'));
define('RPT_PGCOYNM',_('Company Name'));
define('RPT_PGFILDESC',_('Report Filter Description'));
define('RPT_PGHEADER',_('Header Information / Formatting'));
define('RPT_PGLAYOUT',_('Page Layout'));
define('RPT_PGMARGIN',_('Page Margins'));
define('RPT_RNRPT',_('Rename Report'));
define('RPT_PGTITL1',_('Report Title 1'));
define('RPT_PGTITL2',_('Report Title 2'));
define('RPT_RPTDATA',_('Report Data'));
define('RPT_RPTID',_('Report Identification'));
define('RPT_RPTIMPORT',_('Report Import'));
define('RPT_SORTLIST',_('Sorting Information'));
define('RPT_TBLNAME',_('Table Name'));
define('RPT_TBLFNAME',_('Fieldname (table.fieldname)'));
define('RPT_TOTALS',_('Report Totals'));

// Form Specific
define('FORM_BTN_NEWRPT',_('Create New Form'));
define('FORM_BTN_DELRPT',_('Delete Form'));

// Account Group Definitions
define('RPT_ORDERS',_('Orders'));
define('RPT_PAYABLES',_('Payables'));
define('RPT_PURCHASES',_('Purchases'));
define('RPT_RECEIVABLES',_('Receivables'));
define('RPT_INVENTORY',_('Inventory'));
define('RPT_MANUFAC',_('Manufacturing'));
define('RPT_GL',_('General Ledger'));
define('RPT_FINANCIAL',_('Financial Reports'));
define('RPT_MISC',_('Miscellaneous Reports'));

// Paper Size Definitions
define('RPT_PAPER',_('Paper Size:'));
define('RPT_ORIEN',_('Orientation:'));
define('RPT_MM',_('mm'));
define('RPT_A3',_('A3'));
define('RPT_A4',_('A4'));
define('RPT_A5',_('A5'));
define('RPT_LEGAL',_('Legal'));
define('RPT_LETTER',_('Letter'));
define('RPT_PORTRAIT',_('Portrait'));
define('RPT_LANDSCAPE',_('Landscape'));

// Font Names
define('RPT_COURIER',_('Courier'));
define('RPT_HELVETICA',_('Helvetica'));
define('RPT_TIMES',_('Times'));

// General Number Definitions
define('RPT_8',_('8'));
define('RPT_10',_('10'));
define('RPT_12',_('12'));
define('RPT_14',_('14'));
define('RPT_16',_('16'));
define('RPT_18',_('18'));
define('RPT_20',_('20'));
define('RPT_24',_('24'));
define('RPT_28',_('28'));
define('RPT_32',_('32'));
define('RPT_36',_('36'));
define('RPT_40',_('40'));
define('RPT_50',_('50'));

// Color definitions
define('RPT_BLACK',_('Black'));
define('RPT_BLUE',_('Blue'));
define('RPT_RED',_('Red'));
define('RPT_ORANGE',_('Orange'));
define('RPT_YELLOW',_('Yellow'));
define('RPT_GREEN',_('Green'));

// Definitions for date selection dropdown list
define('RPT_TODAY',_('Today'));
define('RPT_WEEK',_('This Week'));
define('RPT_WTD',_('This Week To Date'));
define('RPT_MONTH',_('This Month'));
define('RPT_MTD',_('This Month To Date'));
define('RPT_QUARTER',_('This Quarter'));
define('RPT_QTD',_('This Quarter To Date'));
define('RPT_YEAR',_('This Year'));
define('RPT_YTD',_('This Year To Date'));
?>
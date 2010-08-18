<?php
/************************************************************************************
Default setups
*************************************************************************************/
define ('CompanyDataBase','companies'); // Defines the db to be used to fetch the company information

// Sets the default groups for forms, index max 4 chars
$ReportGroups = array (
	'ord' => RPT_ORDERS,
	'ar' => RPT_RECEIVABLES,
	'ap' => RPT_PAYABLES,
	'prch' => RPT_PURCHASES,
	'inv' => RPT_INVENTORY,
	'man' => RPT_MANUFAC,
	'gl' => RPT_GL,
	'misc' => RPT_MISC);  // do not delete misc category

// This array is imploded with the first entry = number of text boxes to build (0, 1 or 2), 
// the remaining is the dropdown menu listings
$CritChoices = array(
	0 => '2:'.RPT_ALL.':'.RPT_RANGE,
	1 => '2:'.RPT_RANGE.':'.RPT_ALL,
	2 => '0:'.RPT_YES.':'.RPT_NO,
	3 => '0:'.RPT_ALL.':'.RPT_YES.':'.RPT_NO,
	4 => '0:'.RPT_ALL.':'.RPT_ACTIVE.':'.RPT_INACTIVE,
	5 => '0:'.RPT_ALL.':'.RPT_PRINTED.':'.RPT_UNPRINTED,
	6 => '0:'.RPT_ALL.':'.RPT_STOCK.':'.RPT_ASSEMBLY);

// Paper orientation
$PaperOrientation = array (
	'P' => RPT_PORTRAIT,
	'L' => RPT_LANDSCAPE);
	
// Paper sizes supported in fpdf class, includes dimensions width, length in mm for page setup
$PaperSizes = array (
	'A3:297:420' => RPT_A3,
	'A4:210:297' => RPT_A4,
	'A5:148:210' => RPT_A5,
	'Legal:216:357' => RPT_LEGAL,
	'Letter:216:282' => RPT_LETTER);

// Fonts (defaults for FPDF)
$Fonts = array (
	'helvetica' => RPT_HELVETICA,
	'courier' => RPT_COURIER,
	'times' => RPT_TIMES);

// Available font sizes in units: points
$FontSizes = array (
	'8' => RPT_8, 
	'10' => RPT_10, 
	'12' => RPT_12, 
	'14' => RPT_14, 
	'16' => RPT_16, 
	'18' => RPT_18, 
	'20' => RPT_20, 
	'24' => RPT_24, 
	'28' => RPT_28, 
	'32' => RPT_32, 
	'36' => RPT_36, 
	'40' => RPT_40, 
	'50' => RPT_50);

// Available font sizes in units: points
$LineSizes = array (
	'1' => RPT_1, 
	'2' => RPT_2, 
	'3' => RPT_3, 
	'4' => RPT_4, 
	'5' => RPT_5, 
	'6' => RPT_6, 
	'7' => RPT_7, 
	'8' => RPT_8, 
	'9' => RPT_9, 
	'10' => RPT_10);

// Font colors keyed by color Red:Green:Blue
$FontColors = array (
	'0:0:0' => RPT_BLACK, // Leave black first as it is typically the default value
	'0:0:255' => RPT_BLUE,
	'255:0:0' => RPT_RED,
	'255:128:0' => RPT_ORANGE,
	'255:255:0' => RPT_YELLOW,
	'0:255:0' => RPT_GREEN);

$FontAlign = array (
	'L' => RPT_LEFT,
	'R' => RPT_RIGHT,
	'C' => RPT_CENTER);

$TotalLevels = array(
	'0' => RPT_NO,
	'1' => RPT_YES);

$DateChoices = array(
	'a' => RPT_ALL,
	'b' => RPT_RANGE,
	'c' => RPT_TODAY,
	'd' => RPT_WEEK,
	'e' => RPT_WTD,
	'f' => RPT_MONTH,
	'g' => RPT_MTD,
	'h' => RPT_QUARTER,
	'i' => RPT_QTD,
	'j' => RPT_YEAR,
	'k' => RPT_YTD);

/*********************************************************************************************
Form unique defaults
**********************************************************************************************/ 
// Sets the groupings for forms indexed to a specific report (top level) grouping, 
// index is of the form ReportGroup[index]:FormGroup[index], each have a max of 4 chars
// This array is linked to the ReportGroups array by using the index values of ReportGroup
// the first value must match an index value of ReportGroup.
$FormGroups = array (
	'gl:chk' => RPT_BANKCHK,	// Bank checks grouped with the gl report group
	'ar:col' => RPT_COLLECTLTR,
	'ar:cust' => RPT_CUSTSTATE,
	'gl:deps' => RPT_BANKDEPSLIP,
	'ar:inv' => RPT_INVPKGSLIP,
	'ar:lblc' => RPT_CUSTLBL,
	'prch:lblv' => RPT_VENDLBL,
	'prch:po' => RPT_PURCHORD,
	'ord:quot' => RPT_CUSTQUOTE,
	'ar:rcpt' => RPT_SALESREC,
	'ord:so' => RPT_SALESORD,
	'misc:misc' => RPT_MISC);  // do not delete misc category

// DataTypes
// A corresponding class function needs to be generated for each new function added.
// The index code is also used to identify the form to include to set the properties.
$FormEntries = array(
	'Data' => FRM_DATALINE,
	'TBlk' => FRM_DATABLOCK,
	'Tbl' => FRM_DATATABLE,
	'Ttl' => FRM_DATATOTAL,
	'Text' => FRM_FIXEDTXT,
	'Img' => FRM_IMAGE,
	'Rect' => FRM_RECTANGLE,
	'Line' => FRM_LINE,
	'CDta' => FRM_COYDATA,
	'CBlk' => FRM_COYBLOCK,
	'PgNum' => FRM_PAGENUM);

// The function to process these values is: ProcessData
// which is located in the file: WriteForm.php
// A case statement needs to be generated to process each new value
$FormProcessing = array(
	'' => RPT_NONE,
	'uc' => FRM_UPPERCASE,
	'lc' => FRM_LOWERCASE,
	'neg' => FRM_NEGATE,
	'rnd2d' => FRM_RNDR2,
	'dlr' => FRM_CNVTDLR,
	'euro' => FRM_CNVTEURO);

// The function to process these values is: AddSep
// which is located in the file: WriteForm.php
// A case statement needs to be generated to process each new value
$TextProcessing = array(
	'' => RPT_NONE,
	'sp' => FRM_SPACE1,
	'2sp' => FRM_SPACE2,
	'comma' => FRM_COMMA,
	'com-sp' => FRM_COMMASP,
	'nl' => FRM_NEWLINE,
	'semi-sp' => FRM_SEMISP);

?>
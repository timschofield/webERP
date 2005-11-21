<?php
/************************************************************************************
Default array field setups
*************************************************************************************/

// Sets the default groups for reporting
$ReportGroups = array (
	'ord' => RPT_ORDERS,
	'ar' => RPT_RECEIVABLES,
	'ap' => RPT_PAYABLES,
	'purch' => RPT_PURCHASES,
	'inv' => RPT_INVENTORY,
	'man' => RPT_MANUFAC,
	'gl' => RPT_GL,
//	'fin' => RPT_FINANCIAL,
	'misc' => RPT_MISC);  // do not delete misc category

// This array is imploded with the first entry = number of text boxes to build (0, 1 or 2), 
// the remaining is the dropdown menu listings
$CritChoices = array(
	0 => '2:'.RPT_ALL.':'.RPT_RANGE,
	1 => '0:'.RPT_YES.':'.RPT_NO,
	2 => '0:'.RPT_ALL.':'.RPT_YES.':'.RPT_NO,
	3 => '0:'.RPT_ALL.':'.RPT_ACTIVE.':'.RPT_INACTIVE,
	4 => '0:'.RPT_ALL.':'.RPT_PRINTED.':'.RPT_UNPRINTED,
	5 => '0:'.RPT_ALL.':'.RPT_STOCK.':'.RPT_ASSEMBLY);

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

// Font colors keyed by color Red:Green:Blue
$FontColors = array (
	'0:0:0' => RPT_BLACK,
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
// DataTypes
$EntryTypes = array(
	'text' => 'Fixed - Text field',
	'image' => 'Fixed - JPG, PNG or GIF Image',
	'box' => 'Fixed - Draws a box',
	'line' => 'Fixed - Draws a line',
	'circle' => 'Fixed - Draws a circle',
	'dataline' => 'Variable - Single line of information',
	'datablock' => 'Variable - Block of information',
	'pagenum' => 'Variable - Page numbering',
	'forminfo' => 'Variable - Form information',
	'dupforminfo' => 'Variable - Copy of form derived information',
	'total' => 'Variable - Form total');
?>
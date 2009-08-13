<?php

/* $Revision: 1.35 $ */

/*
date validation and parsing functions

These functions refer to the config variable defining the date format
The date format is defined in SystemParameters called DefaultDateFormat
this can be a string either 'd/m/Y' for UK/Australia/New Zealand dates or
'm/d/Y' for US/Canada format dates

or Y/m/d  for Sweden ;) Anders Eriksson anders@weberp.se.

or d.m.Y  for Germany ;) Juergen Ruemmler heinrich@ruemmler.net

*/

function Is_Date($DateEntry) {

	$DateEntry =Trim($DateEntry);

	//echo '<BR>The date entered is ' . $DateEntry;

	if (strpos($DateEntry,'/')) {
		$Date_Array = explode('/',$DateEntry);
	} elseif (strpos ($DateEntry,'-')) {
		$Date_Array = explode('-',$DateEntry);
	} elseif (strpos ($DateEntry,'.')) {
		$Date_Array = explode('.',$DateEntry);
	} elseif (strlen($DateEntry)==6) {
		$Date_Array[0]= substr($DateEntry,0,2);
		$Date_Array[1]= substr($DateEntry,2,2);
		$Date_Array[2]= substr($DateEntry,4,2);
	} elseif (strlen($DateEntry)==8) {
		$Date_Array[0]= substr($DateEntry,0,2);
		$Date_Array[1]= substr($DateEntry,2,2);
		$Date_Array[2]= substr($DateEntry,4,4);
	}

	if (!isset($Date_Array) or sizeof($Date_Array)<3) {
		return 0;
	}


	if ((int)$Date_Array[2] >9999) {
		return 0;
	}


	if (is_long((int)$Date_Array[0]) AND is_long((int)$Date_Array[1]) AND is_long((int)$Date_Array[2])) {
		if (($_SESSION['DefaultDateFormat']=='d/m/Y') || ($_SESSION['DefaultDateFormat']=='d.m.Y')){

			if (checkdate((int)$Date_Array[1],(int)$Date_Array[0],(int)$Date_Array[2])){
				return 1;
			} else {
				return 0;
			}
		} elseif ($_SESSION['DefaultDateFormat']=='m/d/Y'){

			if (checkdate((int)$Date_Array[0],(int)$Date_Array[1],(int)$Date_Array[2])){
				return 1;
			} else {
				return 0;
			}
		} elseif ($_SESSION['DefaultDateFormat']=='Y/m/d'){

			if (checkdate((int)$Date_Array[1],(int)$Date_Array[2],(int)$Date_Array[0])){
				return 1;
			} else {
				return 0;
			}
		} else { /*Can't be in an appropriate DefaultDateFormat */
			return 0;
		}
	}else { // end if all numeric inputs
		return 0;
	}

} //end of Is_Date function



//_______________________________________________________________

function MonthAndYearFromSQLDate($DateEntry) {


	if (strpos($DateEntry,'/')) {
		$Date_Array = explode('/',$DateEntry);
	} elseif (strpos ($DateEntry,'-')) {
		$Date_Array = explode('-',$DateEntry);
	} elseif (strpos ($DateEntry,'.')) {
		$Date_Array = explode('.',$DateEntry);
	}

	if (strlen($Date_Array[2])>4) {
		$Date_Array[2]= substr($Date_Array[2],0,2);
	}

	return ucfirst(strftime('%B %Y', mktime(0,0,0, (int)$Date_Array[1],(int)$Date_Array[2],(int)$Date_Array[0])));

}

function DayOfWeekFromSQLDate($DateEntry) {


	if (strpos($DateEntry,'/')) {
		$Date_Array = explode('/',$DateEntry);
	} elseif (strpos ($DateEntry,'-')) {
		$Date_Array = explode('-',$DateEntry);
	} elseif (strpos ($DateEntry,'.')) {
		$Date_Array = explode('.',$DateEntry);
	}

	if (strlen($Date_Array[2])>4) {
		$Date_Array[2]= substr($Date_Array[2],0,2);
	}

	return date( 'w', mktime(0,0,0, (int)$Date_Array[1],(int)$Date_Array[2],(int)$Date_Array[0]));

}


// Returns the timestamp for the financial year end. To find other year ends,
// use $YearIncrement to move back and fgorward. in -1 gives last year end, 1
// gives next year end.

function YearEndDate($MonthNo, $YearIncrement) {
	if (Date('m') > $MonthNo) {
		$Year = Date('Y')+1+$YearIncrement;
	} else {
		$Year = Date('Y')+$YearIncrement;
	}
	return mktime(0,0,0,$MonthNo+1,0,$Year);
}

function ConvertSQLDate($DateEntry) {

//for MySQL dates are in the format YYYY-mm-dd


	if (strpos($DateEntry,'/')) {
		$Date_Array = explode('/',$DateEntry);
	} elseif (strpos ($DateEntry,'-')) {
		$Date_Array = explode('-',$DateEntry);
	} elseif (strpos ($DateEntry,'.')) {
		$Date_Array = explode('.',$DateEntry);
	}

	if (strlen($Date_Array[2])>4) {  /*chop off the time stuff */
		$Date_Array[2]= substr($Date_Array[2],0,2);
	}

	if ($_SESSION['DefaultDateFormat']=='d/m/Y'){
		return $Date_Array[2].'/'.$Date_Array[1].'/'.$Date_Array[0];
	} elseif ($_SESSION['DefaultDateFormat']=='d.m.Y'){
		return $Date_Array[2].'.'.$Date_Array[1].'.'.$Date_Array[0];
	} elseif ($_SESSION['DefaultDateFormat']=='m/d/Y'){
		return $Date_Array[1].'/'.$Date_Array[2].'/'.$Date_Array[0];
	} elseif ($_SESSION['DefaultDateFormat']=='Y/m/d'){
		return $Date_Array[0].'/'.$Date_Array[1].'/'.$Date_Array[2];
	}

} // end function ConvertSQLDate

function SQLDateToEDI($DateEntry) {

//for MySQL dates are in the format YYYY-mm-dd
//EDI format 102 dates are in the format CCYYMMDD - just need to lose the seperator

	if (strpos($DateEntry,'/')) {
		$Date_Array = explode('/',$DateEntry);
	} elseif (strpos ($DateEntry,'-')) {
		$Date_Array = explode('-',$DateEntry);
	} elseif (strpos ($DateEntry,'.')) {
		$Date_Array = explode('.',$DateEntry);
	}

	if (strlen($Date_Array[2])>4) {  /*chop off the time stuff */
		$Date_Array[2]= substr($Date_Array[2],0,2);
	}

	return $Date_Array[0].$Date_Array[1].$Date_Array[2];

} // end function SQLDateToEDI

function ConvertToEDIDate($DateEntry) {

/* takes a date in a the format specified in $_SESSION['DefaultDateFormat']
and converts to a yyyymmdd - EANCOM format 102*/


	$DateEntry = trim($DateEntry);

	if (strpos($DateEntry,'/')) {
		$Date_Array = explode('/',$DateEntry);
	} elseif (strpos ($DateEntry,'-')) {
		$Date_Array = explode('-',$DateEntry);
	} elseif (strpos ($DateEntry,'.')) {
		$Date_Array = explode('.',$DateEntry);
	} elseif (strlen($DateEntry)==6) {
		$Date_Array[0]= substr($DateEntry,0,2);
		$Date_Array[1]= substr($DateEntry,2,2);
		$Date_Array[2]= substr($DateEntry,4,2);
	} elseif (strlen($DateEntry)==8) {
		$Date_Array[0]= substr($DateEntry,0,2);
		$Date_Array[1]= substr($DateEntry,2,2);
		$Date_Array[2]= substr($DateEntry,4,4);
	}


//to modify assumption in 2030

	if ((int)$Date_Array[2] <60) {
		$Date_Array[2] = '20'.$Date_Array[2];
	} elseif ((int)$Date_Array[2] >59 AND (int)$Date_Array[2] <100) {
		$Date_Array[2] = '19'.$Date_Array[2];
	} elseif ((int)$Date_Array[2] >9999) {
		return 0;
	}

	if (($_SESSION['DefaultDateFormat']=='d/m/Y') || ($_SESSION['DefaultDateFormat']=='d.m.Y')){
		return $Date_Array[2].$Date_Array[1].$Date_Array[0];

	} elseif ($_SESSION['DefaultDateFormat']=='m/d/Y') {
		return $Date_Array[2].$Date_Array[0].$Date_Array[1];

	} elseif ($_SESSION['DefaultDateFormat']=='Y/m/d') {
		return $Date_Array[1].$Date_Array[2].$Date_Array[0];

	}

} // end function to convert DefaultDateFormat Date to EDI format 102

function ConvertEDIDate($DateEntry, $EDIFormatCode) {

	/*EDI Format codes:
		102  -  CCYYMMDD
		203  -  CCYYMMDDHHMM
		616  -  CCYYWW  - cant handle the week number
		718  -  CCYYMMDD-CCYYMMDD  can't handle this either a date range
	*/


	switch ($EDIFormatCode) {
	case 102:
		if ($_SESSION['DefaultDateFormat']=='d/m/Y'){
			return substr($DateEntry,6,2).'/'.substr($DateEntry,4,2).'/'.substr($DateEntry,0,4);

		} elseif ($_SESSION['DefaultDateFormat']=='d.m.Y') {
						return substr($DateEntry,6,2).'.'.substr($DateEntry,4,2).'.'.substr($DateEntry,0,4);

		} elseif ($_SESSION['DefaultDateFormat']=='m/d/Y') {
						return substr($DateEntry,4,2).'/'.substr($DateEntry,6,2).'/'.substr($DateEntry,0,4);

		} elseif ($_SESSION['DefaultDateFormat']=='Y/m/d') {
						return substr($DateEntry,0,4).'/'.substr($DateEntry,4,2).'/'.substr($DateEntry,6,2);

		}
		break;
	case 203:
		if ($_SESSION['DefaultDateFormat']=='d/m/Y') {
			return substr($DateEntry,6,2).'/'.substr($DateEntry,4,2).'/'.substr($DateEntry,0,4).' ' . substr($DateEntry,6,2).':' . substr($DateEntry,8,2);

		} elseif ($_SESSION['DefaultDateFormat']=='d.m.Y') {
						return substr($DateEntry,6,2).'.'.substr($DateEntry,4,2).'.'.substr($DateEntry,0,4).' ' . substr($DateEntry,6,2).':' . substr($DateEntry,8,2);

		} elseif ($_SESSION['DefaultDateFormat']=='m/d/Y') {
						return substr($DateEntry,4,2).'/'.substr($DateEntry,6,2).'/'.substr($DateEntry,0,4).' ' . substr($DateEntry,6,2).':' . substr($DateEntry,8,2);

		} elseif ($_SESSION['DefaultDateFormat']=='Y/m/d') {
						return substr($DateEntry,0,4).'/'.substr($DateEntry,4,2).'/'.substr($DateEntry,6,2).' ' . substr($DateEntry,6,2).':' . substr($DateEntry,8,2);

		}
		break;
	case 616:
		/*multiply the week number by 7 and add to the 1/1/CCYY */
		return date($_SESSION['DefaultDateFormat'], mktime(0,0,0, 1,1+(7*(int)substr($DateEntry,4,2)),substr($DateEntry,0,4)));
		break;
	case 718:
		if ($_SESSION['DefaultDateFormat']=='d/m/Y'){
			return substr($DateEntry,6,2).'/'.substr($DateEntry,4,2).'/'.substr($DateEntry,0,4) . ' - '. substr($DateEntry,15,2).'/'.substr($DateEntry,13,2).'/'.substr($DateEntry,9,4);
		} elseif ($_SESSION['DefaultDateFormat']=='d.m.Y') {
						return substr($DateEntry,6,2).'.'.substr($DateEntry,4,2).'.'.substr($DateEntry,0,4) . ' - '. substr($DateEntry,15,2).'.'.substr($DateEntry,13,2).'.'.substr($DateEntry,9,4);
		} elseif ($_SESSION['DefaultDateFormat']=='m/d/Y') {
						return substr($DateEntry,4,2).'/'.substr($DateEntry,6,2).'/'.substr($DateEntry,0,4).' - '. substr($DateEntry,13,2).'/'.substr($DateEntry,15,2).'/'.substr($DateEntry,9,4);
		} elseif ($_SESSION['DefaultDateFormat']=='Y/m/d') {
						return substr($DateEntry,0,4).'/'.substr($DateEntry,4,2).'/'.substr($DateEntry,6,2).' - '. substr($DateEntry,13,2).'/'.substr($DateEntry,15,2).'/'.substr($DateEntry,9,4);

		}

		break;
	}


}



function Format_Date($DateEntry) {

	$DateEntry =trim($DateEntry);

	if (strpos($DateEntry,'/')) {
		$Date_Array = explode('/',$DateEntry);
	} elseif (strpos ($DateEntry,'-')) {
		$Date_Array = explode('-',$DateEntry);
	} elseif (strpos ($DateEntry,'.')) {
		$Date_Array = explode('.',$DateEntry);
	} elseif (strlen($DateEntry)==6) {
		$Date_Array[0]= substr($DateEntry,0,2);
		$Date_Array[1]= substr($DateEntry,2,2);
		$Date_Array[2]= substr($DateEntry,4,2);
	} elseif (strlen($DateEntry)==8) {
		$Date_Array[0]= substr($DateEntry,0,2);
		$Date_Array[1]= substr($DateEntry,2,2);
		$Date_Array[2]= substr($DateEntry,4,4);
	}

//to modify assumption in 2030

	if ((int)$Date_Array[2] <60) {
		$Date_Array[2] = '20'.$Date_Array[2];
	} elseif ((int)$Date_Array[2] >59 AND (int)$Date_Array[2] <100)						{
		$Date_Array[2] = '19'.$Date_Array[2];
	} elseif ((int)$Date_Array[2] >9999) {
		return 0;
	}

	if (is_long((int)$Date_Array[0]) AND is_long((int)$Date_Array[1]) AND is_long((int)$Date_Array[2])) {
		if ($_SESSION['DefaultDateFormat']=='d/m/Y'){
			if (checkdate((int)$Date_Array[1],(int)$Date_Array[0],(int)$Date_Array[2])){
				return $Date_Array[0].'/'.$Date_Array[1].'/'.$Date_Array[2];
			}
		} elseif ($_SESSION['DefaultDateFormat']=='d.m.Y'){
			if (checkdate((int)$Date_Array[1],(int)$Date_Array[0],(int)$Date_Array[2])){
				return $Date_Array[0].'.'.$Date_Array[1].'.'.$Date_Array[2];
			}
		} elseif ($_SESSION['DefaultDateFormat']='m/d/Y'){
			if (checkdate((int)$Date_Array[0],(int)$Date_Array[1],(int)$Date_Array[2]))							{
				return $Date_Array[0].'/'.$Date_Array[1].'/'.$Date_Array[2];
			}
		} elseif ($_SESSION['DefaultDateFormat']='Y/m/d'){
			if (checkdate((int)$Date_Array[2],(int)$Date_Array[0],(int)$Date_Array[1]))							{
				return $Date_Array[0].'/'.$Date_Array[1].'/'.$Date_Array[2];
			}
		} // end if check date
	} else { // end if all numeric inputs
		return 0;
	}
}// end of function




function FormatDateForSQL($DateEntry) {

/* takes a date in a the format specified in $_SESSION['DefaultDateFormat']
and converts to a yyyy/mm/dd format */


	$DateEntry = trim($DateEntry);


	if (strpos($DateEntry,'/')) {
		$Date_Array = explode('/',$DateEntry);
	} elseif (strpos ($DateEntry,'-')) {
		$Date_Array = explode('-',$DateEntry);
	} elseif (strpos ($DateEntry,'.')) {
		$Date_Array = explode('.',$DateEntry);
	} elseif (strlen($DateEntry)==6) {
		$Date_Array[0]= substr($DateEntry,0,2);
		$Date_Array[1]= substr($DateEntry,2,2);
		$Date_Array[2]= substr($DateEntry,4,2);
	} elseif (strlen($DateEntry)==8) {
		$Date_Array[0]= substr($DateEntry,0,4);
		$Date_Array[1]= substr($DateEntry,4,2);
		$Date_Array[2]= substr($DateEntry,6,2);
	}



	if ($_SESSION['DefaultDateFormat']=='Y/m/d') {
		return $Date_Array[0].'/'.$Date_Array[1].'/'.$Date_Array[2];

	}

//to modify assumption in 2030

	if ((int)$Date_Array[2] <60) {
		$Date_Array[2] = '20'.$Date_Array[2];
	} elseif ((int)$Date_Array[2] >59 AND (int)$Date_Array[2] <100) {
		$Date_Array[0] = '19'.$Date_Array[2];
	} elseif ((int)$Date_Array[2] >9999) {
		return 0;
	}

	if (($_SESSION['DefaultDateFormat']=='d/m/Y') || ($_SESSION['DefaultDateFormat']=='d.m.Y')){
		/* echo '<BR>The date returned is ' . $Date_Array[2].'/'.$Date_Array[1].'/'.$Date_Array[0]; */
		return $Date_Array[2].'/'.$Date_Array[1].'/'.$Date_Array[0];

	} elseif ($_SESSION['DefaultDateFormat']=='m/d/Y') {
		return $Date_Array[2].'/'.$Date_Array[0].'/'.$Date_Array[1];
	} elseif ($_SESSION['DefaultDateFormat']=='Y/m/d') {
		return $Date_Array[0].'/'.$Date_Array[1].'/'.$Date_Array[2];

	}

}// end of function

function Date1GreaterThanDate2 ($Date1, $Date2) {

/* returns 1 true if Date1 is greater than Date 2 */

	$Date1 = trim($Date1);
	$Date2 = trim($Date2);
	$Date1_array = explode('/', $Date1);
	$Date2_array = explode('/', $Date2);

	/*Try to make the year of each date comparable - if one date is specified as just
	2 characters and the other >2 then take the last 2 characters of the other date only */
	if (strlen($Date1_array[2])>2 AND strlen($Date2_array[2])==2){
		$Date1_array[2] = substr($Date1_array[2], strlen($Date1_array[2])-2);
	}
	if (strlen($Date2_array[2])>2 AND strlen($Date1_array[2])==2){
		$Date2_array[2] = substr($Date2_array[2], strlen($Date2_array[2])-2);
	}

	/*The 2 element of the array will be the year in either d/m/Y or m/d/Y formats */

	if (($Date1_array[2] - $Date2_array[2]) >0){
		return 1;
	} elseif (($Date1_array[2] - $Date2_array[2]) ==0){

	/*The 0 and 1 elements of the array are switched depending on the format used */

		if (($_SESSION['DefaultDateFormat']=='d/m/Y') || ($_SESSION['DefaultDateFormat']=='d.m.Y')){
			if ( ($Date1_array[1] -  $Date2_array[1]) >0){
				return 1;
			} elseif (($Date1_array[1] - $Date2_array[1])==0){
				if (($Date1_array[0] -  $Date2_array[0])>0){
					return 1;
				}
			}

		} elseif ($_SESSION['DefaultDateFormat'] =='m/d/Y'){
			If (($Date1_array[0] - $Date2_array[0])>0){
				Return 1;
			} elseif (($Date1_array[0] - $Date2_array[0])==0){
				If (($Date1_array[1] - $Date2_array[1])>0){
					Return 1;
				}
			}
		} elseif ($_SESSION['DefaultDateFormat'] =='Y/m/d'){
			if (($Date1_array[2] - $Date2_array[2])>0){
				return 1;
			} elseif (($Date1_array[2] - $Date2_array[2])==0){
				if (($Date1_array[1] - $Date2_array[1])>0){
					return 1;
				}
			}
		}
	}

	return 0;
}

function CalcDueDate($TranDate, $DayInFollowingMonth, $DaysBeforeDue){

	$TranDate = trim($TranDate);

	if (strpos($TranDate,'/')) {
		$Date_Array = explode('/',$TranDate);
	} elseif (strpos ($TranDate,'-')) {
		$Date_Array = explode('-',$TranDate);
	} elseif (strpos ($TranDate,'.')) {
		$Date_Array = explode('.',$TranDate);
  }

	if (($_SESSION['DefaultDateFormat']=='d/m/Y') || ($_SESSION['DefaultDateFormat']=='d.m.Y')){
		if ($DayInFollowingMonth==0){ /*then it must be set up for DaysBeforeDue type */

			$DayDue = $Date_array[0]+$DaysBeforeDue;
			$MonthDue = $Date_array[1];
			$YearDue = $Date_array[2];

		} elseif($DayInFollowingMonth>=29) { //take the last day of month

			$DayDue = 0;
			$MonthDue = $Date_array[1]+2;
			$YearDue = $Date_array[2];
		} else {
			$DayDue = $DayInFollowingMonth;
			$MonthDue = $Date_array[1]+1;
			$YearDue = $Date_array[2];

		}
	} elseif ($_SESSION['DefaultDateFormat']=='m/d/Y'){
		if ($DayInFollowingMonth==0){ /*then it must be set up for DaysBeforeDue type */
			$DayDue = $Date_array[1]+$DaysBeforeDue;
			$MonthDue = $Date_array[0];
			$YearDue = $Date_array[2];

		} elseif($DayInFollowingMonth>=29) { //take the last day of month

			$DayDue = 0;
			$MonthDue = $Date_array[0]+2;
			$YearDue = $Date_array[2];
		} else {
			$DayDue = $DayInFollowingMonth;
			$MonthDue = $Date_array[0]+1;
			$YearDue = $Date_array[2];
		}
	} elseif ($_SESSION['DefaultDateFormat']=='Y/m/d'){
		if ($DayInFollowingMonth==0){ /*then it must be set up for DaysBeforeDue type */
			$DayDue = $Date_array[2]+$DaysBeforeDue;
			$MonthDue = $Date_array[1];
			$YearDue = $Date_array[0];

		} elseif($DayInFollowingMonth>=29) { //take the last day of month

			$DayDue = 0;
			$MonthDue = $Date_array[1]+2;
			$YearDue = $Date_array[0];
		} else {
			$DayDue = $DayInFollowingMonth;
			$MonthDue = $Date_array[1]+1;
			$YearDue = $Date_array[0];
		}
	}
	return Date($_SESSION['DefaultDateFormat'], mktime(0,0,0, $MonthDue, $DayDue,$YearDue));

}

function DateAdd ($DateToAddTo,$PeriodString,$NumberPeriods){

  $DateToAddTo = trim($DateToAddTo);

	if (strpos($DateToAddTo,'/')) {
		$Date_Array = explode('/',$DateToAddTo);
	} elseif (strpos ($DateToAddTo,'-')) {
		$Date_Array = explode('-',$DateToAddTo);
	} elseif (strpos ($TranDate,'.')) {
		$Date_Array = explode('.',$DateToAddTo);
  }

	if (($_SESSION['DefaultDateFormat']=='d/m/Y') || ($_SESSION['DefaultDateFormat']=='d.m.Y')){

		switch ($PeriodString) {
		case 'd': //Days
			return Date($_SESSION['DefaultDateFormat'],mktime(0,0,0, (int)$Date_array[1],(int)$Date_array[0]+$NumberPeriods ,(int)$Date_array[2]));
			break;
		case 'w': //weeks
			return Date($_SESSION['DefaultDateFormat'],mktime(0,0,0, (int)$Date_array[1],(int)$Date_array[0]+($NumberPeriods*7),(int)$Date_array[2]));
			break;
		case 'm': //months
			return Date($_SESSION['DefaultDateFormat'],mktime(0,0,0, (int)$Date_array[1]+$NumberPeriods,(int)$Date_array[0],(int)$Date_array[2]));
			break;
		case 'y': //years
			return Date($_SESSION['DefaultDateFormat'],mktime(0,0,0, (int)$Date_array[1],(int)$Date_array[0],(int)$Date_array[2]+$NumberPeriods));
			break;
		default:
			return 0;
		}
	} elseif ($_SESSION['DefaultDateFormat']=='m/d/Y'){

		switch ($PeriodString) {
		case 'd':
			return Date($_SESSION['DefaultDateFormat'],mktime(0,0,0, (int)$Date_array[0],(int)$Date_array[1]+$NumberPeriods,(int)$Date_array[2]));
			break;
		case 'w':
			return Date($_SESSION['DefaultDateFormat'],mktime(0,0,0, (int)$Date_array[0],(int)$Date_array[1]+($NumberPeriods*7),(int)$Date_array[2]));
			break;
		case 'm':
			return Date($_SESSION['DefaultDateFormat'],mktime(0,0,0, (int)$Date_array[0]+$NumberPeriods,(int)$Date_array[1],(int)$Date_array[2]));
			break;
		case 'y':
			return Date($_SESSION['DefaultDateFormat'],mktime(0,0,0, (int)$Date_array[0],(int)$Date_array[1],(int)$Date_array[2]+$NumberPeriods));
			break;
		default:
			return 0;
		}
	} elseif ($_SESSION['DefaultDateFormat']=='Y/m/d'){

		switch ($PeriodString) {
		case 'd':
			return Date($_SESSION['DefaultDateFormat'],mktime(0,0,0, (int)$Date_array[2],(int)$Date_array[1]+$NumberPeriods,(int)$Date_array[0]));
			break;
		case 'w':
			return Date($_SESSION['DefaultDateFormat'],mktime(0,0,0, (int)$Date_array[2],(int)$Date_array[1]+($NumberPeriods*7),(int)$Date_array[0]));
			break;
		case 'm':
			return Date($_SESSION['DefaultDateFormat'],mktime(0,0,0, (int)$Date_array[2]+$NumberPeriods,(int)$Date_array[1],(int)$Date_array[0]));
			break;
		case 'y':
			return Date($_SESSION['DefaultDateFormat'],mktime(0,0,0, (int)$Date_array[2],(int)$Date_array[1],(int)$Date_array[0]+$NumberPeriods));
			break;
		default:
			return 0;
		}
	}
}

function DateDiff ($Date1, $Date2, $Period) {

/* expects dates in the format specified in $_SESSION['DefaultDateFormat'] - period can be one of 'd','w','y','m'
months are assumed to be 30 days and years 365.25 days This only works
provided that both dates are after 1970. Also only works for dates up to the year 2035 ish */

	$Date1 = trim($Date1);
	$Date2 = trim($Date2);

	if (strpos($Date1,'/')) {
		$Date1_array = explode('/',$Date1);
	} elseif (strpos ($Date1,'-')) {
		$Date1_array = explode('-',$Date1);
	} elseif (strpos ($Date1,'.')) {
		$Date1_array = explode('.',$Date1);
  }
	if (strpos($Date2,'/')) {
		$Date2_array = explode('/',$Date2);
	} elseif (strpos ($Date1,'-')) {
		$Date2_array = explode('-',$Date2);
	} elseif (strpos ($Date1,'.')) {
		$Date2_array = explode('.',$Date2);
  }

	if (($_SESSION['DefaultDateFormat']=='d/m/Y') || ($_SESSION['DefaultDateFormat']=='d.m.Y')){
		$Date1_Stamp = mktime(0,0,0, (int)$Date1_array[1],(int)$Date1_array[0],(int)$Date1_array[2]);
		$Date2_Stamp = mktime(0,0,0, (int)$Date2_array[1],(int)$Date2_array[0],(int)$Date2_array[2]);
	} elseif ($_SESSION['DefaultDateFormat']=='m/d/Y'){
		$Date1_Stamp = mktime(0,0,0, (int)$Date1_array[0],(int)$Date1_array[1],(int)$Date1_array[2]);
		$Date2_Stamp = mktime(0,0,0, (int)$Date2_array[0],(int)$Date2_array[1],(int)$Date2_array[2]);
	} elseif ($_SESSION['DefaultDateFormat']=='Y/m/d'){
		$Date1_Stamp = mktime(0,0,0, (int)$Date1_array[1],(int)$Date1_array[2],(int)$Date1_array[0]);//Changeorder of entries to match Y/M/D format
		$Date2_Stamp = mktime(0,0,0, (int)$Date2_array[1],(int)$Date2_array[2],(int)$Date2_array[0]); //Changeorder of entries to match Y/M/D format
	}
	$Difference = $Date1_Stamp - $Date2_Stamp;

/* Difference is the number of seconds between each date negative if Date 2 > Date 1 */

	switch ($Period) {
	case 'd':
		return (int) ($Difference/(24*60*60));
		break;
	case 'w':
		return (int) ($Difference/(24*60*60*7));
		break;
	case 'm':
		return (int) ($Difference/(24*60*60*30));
		break;
	case 's':
		return $Difference;
		break;
	case 'y':
		return (int) ($Difference/(24*60*60*365.25));
		break;
	default:
		return 0;
	}

}


function CalcEarliestDispatchDate (){

/*This function will need to be modified depending on the business - many businesses run 24x7
The default assumes no delivery on Sat and Sun*/

	$EarliestDispatch = Mktime();
	if (Date('w',$EarliestDispatch)==0 ){

/*if today is a sunday the dispatch date must be tomorrow (Monday) or after */

		$EarliestDispatch = Mktime(0,0,0,Date('m',$EarliestDispatch),Date('d',$EarliestDispatch)+1,Date('y',$EarliestDispatch));

	} elseif (Date('w',$EarliestDispatch)==6){

/*if today is a saturday the dispatch date must be Monday or after */

		$EarliestDispatch = Mktime(0,0,0,Date('m',$EarliestDispatch),Date('d',$EarliestDispatch)+2,Date('y',$EarliestDispatch));

	}elseif (Date('H')>$_SESSION['DispatchCutOffTime']) {

/* If the hour is after Dispatch Cut Off Time default dispatch date to tomorrow */

		$EarliestDispatch = Mktime(0,0,0,Date('m'),Date('d')+1,Date('y'));
	}
	return $EarliestDispatch;
}

function CreatePeriod($PeriodNo, $PeriodEnd, &$db) {
				$GetPrdSQL = 'INSERT INTO periods (periodno, lastdate_in_period) VALUES ('
					. $PeriodNo . ", '" . Date('Y/m/d', $PeriodEnd) . "')";
				$ErrMsg = _('An error occurred in adding a new period number');
				$GetPrdResult = DB_query($GetPrdSQL, $db, $ErrMsg);

				$sql = 'INSERT INTO chartdetails (accountcode, period)
						SELECT chartmaster.accountcode, periods.periodno
							FROM chartmaster
								CROSS  JOIN periods
						WHERE ( chartmaster.accountcode, periods.periodno ) NOT
							IN ( SELECT chartdetails.accountcode, chartdetails.period FROM chartdetails )';
/*dont trap errors - chart details records created only as required - duplicate messages ignored */
				$InsNewChartDetails = DB_query($sql,$db,'','','',false);
}

function PeriodExists($TransDate, &$db) {

	/* Find the date a month on */
	$MonthAfterTransDate = Mktime(0,0,0,Date('m',$TransDate)+1,Date('d',$TransDate),Date('Y',$TransDate));

	$GetPrdSQL = "SELECT periodno FROM periods WHERE lastdate_in_period < '" . Date('Y/m/d', $MonthAfterTransDate) . "' AND lastdate_in_period >= '" . Date('Y/m/d', $TransDate) . "'";

	$ErrMsg = _('An error occurred in retrieving the period number');
	$GetPrdResult = DB_query($GetPrdSQL,$db,$ErrMsg);

	if (DB_num_rows($GetPrdResult)==0) {
		return false;
	} else {
		return true;
	}

}

function GetPeriod ($TransDate, &$db) {

	/* Convert the transaction date into a unix time stamp.*/

	if (strpos($TransDate,'/')) {
		$Date_Array = explode('/',$TransDate);
	} elseif (strpos ($TransDate,'-')) {
		$Date_Array = explode('-',$TransDate);
	} elseif (strpos ($TransDate,'.')) {
		$Date_Array = explode('.',$TransDate);
	}
	if (($_SESSION['DefaultDateFormat']=='d/m/Y') or ($_SESSION['DefaultDateFormat']=='d.m.Y')){
		$TransDate = mktime(0,0,0,$Date_Array[1],$Date_Array[0],$Date_Array[2]);
	} elseif ($_SESSION['DefaultDateFormat']=='m/d/Y'){
		$TransDate = mktime(0,0,0,$Date_Array[0],$Date_Array[1],$Date_Array[2]);
	} elseif ($_SESSION['DefaultDateFormat']=='Y/m/d'){
		$TransDate = mktime(0,0,0,$Date_Array[1],$Date_Array[2],$Date_Array[0]);
	}

	if (strlen($_SESSION['ProhibitPostingsBefore'])>=8){ //then the ProhibitPostingsBefore configuration is set
		$Date_array = explode('-', $_SESSION['ProhibitPostingsBefore']);
	
		$ProhibitPostingsBefore = mktime(0,0,0,$Date_array[1],$Date_array[2],$Date_array[0]);
		
		/* If transaction date is in a closed period use the month end of that period */
		if ($TransDate < $ProhibitPostingsBefore) {
			$TransDate = $ProhibitPostingsBefore;
		}
	}
	/* Find the unix timestamp of the last period end date in periods table */
	$sql = 'SELECT MAX(lastdate_in_period), MAX(periodno) from periods';
	$result = DB_query($sql, $db);
	$myrow=DB_fetch_row($result);
	if (is_null($myrow[0])){
		$InsertFirstPeriodResult = DB_query("INSERT INTO periods VALUES (0,'" . Date('Y-m-d',mktime(0,0,0,Date('m')+1,0,Date('Y'))) . "')",$db,_('Could not insert first period'));
		$InsertFirstPeriodResult = DB_query("INSERT INTO periods VALUES (1,'" . Date('Y-m-d',mktime(0,0,0,Date('m')+2,0,Date('Y'))) . "')",$db,_('Could not insert second period'));
		$LastPeriod=1;
		$LastPeriodEnd = mktime(0,0,0,Date('m')+2,0,Date('Y'));
	} else {
		$Date_array = explode('-', $myrow[0]);
		$LastPeriodEnd = mktime(0,0,0,$Date_array[1]+2,0,(int)$Date_array[0]);
		$LastPeriod = $myrow[1];
	}
	/* Find the unix timestamp of the first period end date in periods table */
	$sql = 'SELECT MIN(lastdate_in_period), MIN(periodno) from periods';
	$result = DB_query($sql, $db);
	$myrow=DB_fetch_row($result);
	$Date_array = explode('-', $myrow[0]);
	$FirstPeriodEnd = mktime(0,0,0,$Date_array[1],0,(int)$Date_array[0]);
	$FirstPeriod = $myrow[1];
	
	/* If the period number doesn't exist */
	if (!PeriodExists($TransDate, $db)) {
		/* if the transaction is after the last period */
		echo ($TransDate > $LastPeriodEnd);
		if ($TransDate > $LastPeriodEnd) {
			$PeriodEnd = mktime(0,0,0,Date('m', $TransDate)+1, 0, Date('Y', $TransDate));
			$Period = $LastPeriod + 1;
			while ($PeriodEnd >= $LastPeriodEnd) {
				CreatePeriod($Period, $LastPeriodEnd, $db);
				$Period++;
				if (Date('m', $LastPeriodEnd)<=13) {
					$LastPeriodEnd = mktime(0,0,0,Date('m', $LastPeriodEnd)+2, 0, Date('Y', $LastPeriodEnd));
				} else {
					$LastPeriodEnd = mktime(0,0,0,2, 0, Date('Y', $LastPeriodEnd)+1);
				}
			}
			$PeriodEnd = mktime(0,0,0,Date('m', $LastPeriodEnd)+1, 0, Date('Y', $LastPeriodEnd));
			CreatePeriod($Period, $PeriodEnd, $db);
		} else {
		/* The transaction is before the first period */
			$PeriodEnd = mktime(0,0,0,Date('m', $TransDate), 0, Date('Y', $TransDate));
			$Period = $FirstPeriod - 1;
			while ($FirstPeriodEnd > $PeriodEnd) {
				CreatePeriod($Period, $FirstPeriodEnd, $db);
				$Period--;
				if (Date('m', $FirstPeriodEnd)>0) {
					$FirstPeriodEnd = mktime(0,0,0,Date('m', $FirstPeriodEnd), 0, Date('Y', $FirstPeriodEnd));
				} else {
					$FirstPeriodEnd = mktime(0,0,0,13, 0, Date('Y', $FirstPeriodEnd));
				}
			}
		}
	} else if (!PeriodExists(mktime(0,0,0,Date('m',$TransDate)+1,Date('d',$TransDate),Date('Y',$TransDate)), $db)) {
		/* Make sure the following months period exists */
		$sql = 'SELECT MAX(lastdate_in_period), MAX(periodno) from periods';
		$result = DB_query($sql, $db);
		$myrow=DB_fetch_row($result);
		$Date_array = explode('-', $myrow[0]);
		$LastPeriodEnd = mktime(0,0,0,$Date_array[1]+2,0,(int)$Date_array[0]);
		$LastPeriod = $myrow[1];
		CreatePeriod($LastPeriod+1, $LastPeriodEnd, $db);
	}

	/* Now return the period number of the transaction */

	$MonthAfterTransDate = Mktime(0,0,0,Date('m',$TransDate)+1,Date('d',$TransDate),Date('Y',$TransDate));
	$GetPrdSQL = "SELECT periodno FROM periods WHERE lastdate_in_period < '" .
	Date('Y/m/d', $MonthAfterTransDate) . "' AND lastdate_in_period >= '" . Date('Y/m/d', $TransDate) . "'";

	$ErrMsg = _('An error occurred in retrieving the period number');
	$GetPrdResult = DB_query($GetPrdSQL,$db,$ErrMsg);
	$myrow = DB_fetch_row($GetPrdResult);

	return $myrow[0];
}


?>
function setTextAlign(control, alignment) {
	control.style.textAlign=alignment;
}

function setBGColour(control, colour) {
	control.style.backgroundColor=colour;
}

function numberFormat(control, dp) {
	control.value=parseFloat(control.value).toFixed(dp);
}

function restrictToNumbers(myfield, e) {
	var key;
	var keychar;
	if (window.event) {
		key = window.event.keyCode;
	} else if (e) {
		key = e.which;
	} else {
		return true;
	}
	keychar = String.fromCharCode(key);
	if ((key==null) || (key==0) || (key==8) || (key==9) || (key==13) || (key==27) ) {
		return true;
	} else if ((("0123456789,.-").indexOf(keychar) > -1)) {
		return true;
	} else {
		return false;
	}
}

function assignComboToInput(combo, input) {
	input.value=combo.value;
}

function inArray(control, value, thisArray, msg) {
	for (i=0; i<thisArray.length; i++) {
		if (( value == thisArray[i].value )) {
			setBGColour(control, '#ffffff');
			return true;
		}
	}
	setBGColour(control, '#fddbdb');
	alert(msg);
	return false;
}

function isDate(control, dateStr, dateFmt) {
	var matchArray = dateStr.match(/^(\d{1,2})(\/|-)(\d{1,2})(\/|-)(\d{4})$/);
	if (matchArray == null) {
		setBGColour(control, '#fddbdb');
		alert("Please enter the date in the format "+dateFmt+". Your current selection reads: " + dateStr);
		return false;
	}
	if (dateFmt=="d/m/Y") {
		day = matchArray[1];
		month = matchArray[3];
	} else {
		day = matchArray[3];
		month = matchArray[1];
	}
	year = matchArray[5];
	if (month < 1 || month > 12) {
		setBGColour(control, '#fddbdb');
		alert("Month must be between 1 and 12.");
		return false;
	}
	if (day < 1 || day > 31) {
		setBGColour(control, '#fddbdb');
		alert("Day must be between 1 and 31.");
		return false;
	}
	if ((month==4 || month==6 || month==9 || month==11) && day==31) {
		setBGColour(control, '#fddbdb');
		alert("Month "+month+" doesn`t have 31 days!");
		return false;
	}
	if (month == 2) {
		var isleap = (year % 4 == 0 && (year % 100 != 0 || year % 400 == 0));
		if (day > 29 || (day==29 && !isleap)) {
			setBGColour(control, '#fddbdb');
			alert("February " + year + " doesn`t have " + day + " days!");
			return false;
		}
	}
	setBGColour(control, '#ffffff');
	return true;
}

function eitherOr(one, two) {
	if (( two.value!='' )) {
		two.value='';
	} else if (( one.value=='NaN' )) {
		one.value='';
	}
}
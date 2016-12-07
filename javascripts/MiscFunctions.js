/* $Id: MiscFunctions.js 7645 2016-10-17 05:34:08Z rchacon $ */
/* Miscellaneous JavaScript functions. */

function defaultControl(c) {
c.select();
c.focus();
}

function ReloadForm(fB) {
fB.click();
}

function rTN(event) {
	if(window.event) k = window.event.keyCode;
	else if (event) k = event.which;
	else return true;
	kC = String.fromCharCode(k);
	if(k==13) return false;
	if((k==null) || (k==0) || (k==8) || (k==9) || (k==13) || (k==27)) return true;
	else if((("0123456789.,- ").indexOf(kC)>-1)) return true;
	else return false;
}

function rTI(event) {
	if(window.event) k = window.event.keyCode;
	else if(event) k = event.which;
	else return true;
	kC = String.fromCharCode(k);
	if((k==null) || (k==0) || (k==8) || (k==9) || (k==13) || (k==27)) return true;
	else if((("0123456789-").indexOf(kC)>-1)) return true;
	else return false;
}

function rLocaleNumber() {
	var Lang = document.getElementById('Lang').value;
	switch(Lang) {
		case 'US':
			var patt = /(?:^(-)?([1-9]{1}\d{0,2}(?:,?\d{3})*(?:\.\d{1,})?)$)|(?:^(-)?(0?\.\d{1,})$)|(?:^0$)/;
			break;
		case 'IN':
			var patt = /(?:^(-)?([1-9]{1}\d{0,1},)?(\d{2},)*(\d{3})(\.\d+)?$)|(?:^(-)?[1-9]{1}\d{0,2}(\.\d+)?$)|(?:^(-)?(0?\.\d{1,})$)|(?:^0$)/;
			break;
		case 'EE':
			var patt = /(?:^(-)?[1-9]{1}\d{0,2}(?:\s?\d{3})*(?:\.\d{1,})?$)|(?:^(-)?(0?\.\d{1,})$)|(?:^0$)/;
			break;
		case 'FR':
			var patt = /(?:^(-)?[1-9]{1}\d{0,2}(?:\s?\d{3})*(?:,\d{1,})?$)|(?:^(-)?(0?,\d{1,})$)|(?:^0$)/;
			break;
		case 'GM':
			var patt = /(?:^(-)?[1-9]{1}\d{0,2}(?:\.?\d{3})*(?:,\d{1,})?$)|(?:^(-)?(0?,\d{1,})$)|(?:^0$)/;
			break;
		default:
			alert('something is wrong with your language setting');
	}
	if(patt.test(this.value)) {
		this.setCustomValidity('');
		return true;

	} else {
		this.setCustomValidity('The number format is wrong');
		return false;
	};
}

function assignComboToInput(c, i) {
	i.value=c.value;
}

function inArray(v, tA, m) {
	for(i=0;i<tA.length;i++) {
		if(v.value==tA[i].value) {
			return true;
		}
	}
	alert(m);
	return false;
}

function isDate(dS, dF) {
	switch(dF) {
		case "d/m/Y":
		case "d.m.Y":
		case "m/d/Y":
		var mA=dS.match(/^(\d{1,2})(\/|-|.)(\d{1,2})(\/|-|.)(\d{4})$/);
		break;
		case "Y/m/d":
		case "Y-m-d":
		var mA=dS.match(/^(\d{4})(\/|-|.)(\d{1,2})(\/|-|.)(\d{1,2})$/);
		break;
	}

	if(mA==null) {
		alert("Please enter the date in the format "+dF);
		return false;
	}
	if(dF=="d/m/Y"||dF=="d.m.Y") {
		d=mA[1];
		m=mA[3];
		y=mA[5];
	}else if(dF=='m/d/Y') {
		m=mA[1];
		d=mA[3];
		y=mA[5];
	}else{
		d=mA[5];
		m=mA[3];
		y=mA[1];
	}

	if(m<1 || m>12) {
		alert("Month must be between 1 and 12");
		return false;
	}
	if(d<1 || d>31) {
		alert("Day must be between 1 and 31");
		return false;
	}
	if((m==4 || m==6 || m==9 || m==11) && d==31) {
		alert("Month "+m+" doesn`t have 31 days");
		return false;
	}
	if(m==2) {
		var isleap=(y%4==0);
		if(d>29 || (d==29 && !isleap)) {
			alert("February "+y+" doesn`t have "+d+" days");
			return false;
		}
	}
	return true;
}

function eitherOr(o, t) {
	if(o.value!='') t.value='';
	else if(o.value=='NaN') o.value='';
}
/*Renier & Louis (info@tillcor.com) 25.02.2007
Copyright 2004-2007 Tillcor International
*/
days=new Array('Su', 'Mo', 'Tu', 'We', 'Th', 'Fr', 'Sa');
months=new Array('Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec');
dateDivID="calendar";

function Calendar(md, dF) {
	// dF: Date format. Formats: "d/m/Y", "d.m.Y", "m/d/Y", "Y-m-d", "Y/m/d".
	iF = document.getElementsByName(md).item(0);
	pB = iF;
	x = pB.offsetLeft;
	y = pB.offsetTop+pB.offsetHeight;
	var p = pB;
	while(p.offsetParent) {
		p = p.offsetParent;
		x += p.offsetLeft;
		y += p.offsetTop;
	}
	dt = convertDate(iF.value, dF);
	nN = document.createElement("div");
	nN.setAttribute("id", dateDivID);
	nN.setAttribute("style", "visibility:hidden;");
	document.body.appendChild(nN);
	cD = document.getElementById(dateDivID);
	cD.style.position = "absolute";
	cD.style.left = x + "px";
	cD.style.top = y + "px";
	cD.style.visibility = (cD.style.visibility == "visible" ? "hidden" : "visible");
	cD.style.display = (cD.style.display == "block" ? "none" : "block");
	cD.style.zIndex = 10000;
	drawCalendar(md, dt.getFullYear(), dt.getMonth(), dt.getDate(), dF);
}

function drawCalendar(md, y, m, d, dF) {
	// y: Year. Number.
	// m: Month. Number.
	// d: Day. Number.
	// dF: Date format. Formats: "d/m/Y", "d.m.Y", "m/d/Y", "Y-m-d", "Y/m/d".
	var tD = new Date();
	if((m>=0) && (y>0)) tD = new Date(y, m, 1);
	else {
		d = tD.getDate();
		tD.setDate(1);
	}
	TR = "<tr>";
	xTR = "</tr>";
	TD = "<td class='dpTD' onMouseOut='this.className=\"dpTD\";' onMouseOver='this.className=\"dpTDHover\";'";
	xTD = "</td>";
	html = "<table class='dpTbl'>" + TR + "<th colspan=\"3\">" + months[tD.getMonth()] + " " + tD.getFullYear() + "</th>" + "<td colspan=\"2\">" + getButtonCode(md, tD, -1, "&lt;", dF) + xTD + "<td colspan=\"2\">" + getButtonCode(md, tD, 1, "&gt;", dF) + xTD + xTR + TR;
	for(i = 0; i < days.length; i++) html += "<th>" + days[i] + "</th>";
	html += xTR + TR;
	for(i = 0; i < tD.getDay(); i++) html += TD + "&nbsp;" + xTD;
	do {
		dN = tD.getDate();
		TD_onclick = " onclick=\"postDate('" + md + "','" + formatDate(tD, dF) + "');\">";
		if(dN == d) html += "<td" + TD_onclick + "<div class='dpDayHighlight'>" + dN + "</div>" + xTD;
		else html += TD + TD_onclick + dN + xTD;
		if(tD.getDay() == 6) html += xTR + TR;
		tD.setDate(tD.getDate() + 1);
	} while(tD.getDate() > 1)
	if(tD.getDay() > 0) for(i = 6; i > tD.getDay(); i--) html += TD + "&nbsp;" + xTD;
	html += "</table>";
	document.getElementById(dateDivID).innerHTML = html;
}

function getButtonCode(mD, dV, a, lb, dF) {
	nM = (dV.getMonth()+a)%12;
	nY = dV.getFullYear() + parseInt((dV.getMonth()+a)/12, 10);
if(nM < 0) {
	nM += 12;
	nY += -1;
}
return "<button onClick='drawCalendar(\""+mD+"\","+nY+","+nM+","+1+",\""+dF+"\");'>"+lb+"</button>";
}

function formatDate(dV, dF) {
	ds=String(dV.getDate());
	ms=String(dV.getMonth()+1);
	d=("0"+dV.getDate()).substring(ds.length-1, ds.length+1);
	m=("0"+(dV.getMonth()+1)).substring(ms.length-1, ms.length+1);
	y=dV.getFullYear();
	switch(dF) {
		case "d/m/Y":
			return d+"/"+m+"/"+y;
		case "d.m.Y":
			return d+"."+m+"."+y;
		case "Y/m/d":
			return y+"/"+m+"/"+d;
		case "Y-m-d":
			return y+"-"+m+"-"+d;
		default :
			return m+"/"+d+"/"+y;
	}
}

function convertDate(dS, dF) {
	// Converts a date in DefaultDateFormat into a javascript date-object.
	// dS: Date to convert.
	// dF: Date format. Formats: "d/m/Y", "d.m.Y", "m/d/Y", "Y-m-d", "Y/m/d".
	var y, m, d;
	switch(dF) {
		case "d/m/Y":
			dA = dS.split("/");
			d = parseInt(dA[0], 10);
			m = parseInt(dA[1], 10)-1;
			y = parseInt(dA[2], 10);
			break;
		case "d.m.Y":
			dA = dS.split(".");
			d = parseInt(dA[0], 10);
			m = parseInt(dA[1], 10)-1;
			y = parseInt(dA[2], 10);
			break;
		case "m/d/Y":
			dA = dS.split("/");
			m = parseInt(dA[0], 10);
			d = parseInt(dA[1], 10)-1;
			y = parseInt(dA[2], 10);
			break;
		case "Y-m-d":
			dA = dS.split("-");
			y = parseInt(dA[0], 10);
			m = parseInt(dA[1], 10)-1;
			d = parseInt(dA[2], 10);
			break;
		case "Y/m/d":
			dA = dS.split("/");
			y = parseInt(dA[0], 10);
			m = parseInt(dA[1], 10)-1;
			d = parseInt(dA[2], 10);
			break;
		default:
			alert("Unknown date format " + dF);
			return false;
	}
return new Date(y, m, d);
}

function postDate(mydate, dS) {
var iF = document.getElementsByName(mydate).item(0);
iF.value = dS;
var cD = document.getElementById(dateDivID);
cD.style.visibility = "hidden";
cD.style.display = "none";
iF.focus();
}

function clickDate() {
	Calendar(this.name, this.alt);
}

function changeDate() {
	isDate(this.value, this.alt);
}

function SortSelect() {
	selElem=this;
	var tmpArray = new Array();
	columnText=selElem.innerHTML;
	parentElem=selElem.parentNode;
	table=parentElem.parentNode;
	row = table.rows[0];
	for(var j = 0, col; col = row.cells[j]; j++) {
		if(row.cells[j].innerHTML==columnText) {
			columnNumber=j;
			if(selElem.className=="ascending") {
				selElem.className='descending';
				direction="a";
			} else {
				selElem.className='ascending';
				direction="d";
			}
		}
	}
	for(var i = 1, row; row = table.rows[i]; i++) {
		var rowArray = new Array();
		for(var j = 0, col; col = row.cells[j]; j++) {
			if(row.cells[j].tagName == 'TD' ) {
				rowArray[j]=row.cells[j].innerHTML;
				columnClass=row.cells[columnNumber].className;
			}
		}
		tmpArray[i]=rowArray;
	}
	tmpArray.sort(
		function(a,b) {
			if(direction=="a") {
				if(columnClass=="number") {
					return parseFloat(a[columnNumber].replace(/[,.]/g, '')) - parseFloat(b[columnNumber].replace(/[,.]/g, ''));
				} else if(columnClass=="date") {
					da=new Date(a[columnNumber]);
					db=new Date(b[columnNumber]);
					return da>db;
				} else {
					return a[columnNumber].localeCompare(b[columnNumber])
				}
			} else {
				if(columnClass=="number") {
					return parseFloat(b[columnNumber].replace(/[,.]/g, '')) - parseFloat(a[columnNumber].replace(/[,.]/g, ''));
				} else if(columnClass=="date") {
					da=new Date(a[columnNumber]);
					db=new Date(b[columnNumber]);
					return da<=db;
				} else {
					return b[columnNumber].localeCompare(a[columnNumber])
				}
			}
		}
	);
	for(var i = 0, row; row = table.rows[i+1]; i++) {
		var rowArray = new Array();
		rowArray=tmpArray[i];
		for(var j = 0, col; col = row.cells[j]; j++) {
			if(row.cells[j].tagName == 'TD' ) {
				row.cells[j].innerHTML=rowArray[j];
			}
		}
	}
	return;
}

function initial() {
	if(document.getElementsByTagName) {
		var as=document.getElementsByTagName("a");
		for(i=0;i<as.length;i++) {
			var a=as[i];
			if(a.getAttribute("href") &&
				a.getAttribute("rel")=="external")
				a.target="_blank";
		}
	}
	var ds=document.getElementsByTagName("input");
	for(i=0;i<ds.length;i++) {
		if(ds[i].className=="date") {
			ds[i].onclick=clickDate;
			ds[i].onchange=changeDate;
		}
		if(ds[i].getAttribute("data-type") == 'no-illegal-chars') ds[i].pattern="(?!^ +$)[^?\'\u0022+.&\\\\><]*";
		if(ds[i].className=="number") ds[i].onkeypress=rTN;
		if(ds[i].className=="integer") ds[i].onkeypress=rTI;
		if(ds[i].className=="number") {
			ds[i].origonchange=ds[i].onchange;
			ds[i].newonchange=rLocaleNumber;
			ds[i].onchange=function() {
				if(this.origonchange)
					this.origonchange();
				this.newonchange();
			};
		}
	}
	var ds=document.getElementsByTagName("th");
	for(i=0;i<ds.length;i++) {
		if(ds[i].className=="ascending") ds[i].onclick=SortSelect;
	}
}

function AddAmount(t, Target, d) {
	if(t.checked) {
		document.getElementById(Target).value=Number(t.value);
		if(d) document.getElementById(d).required="required";
	} else {
		document.getElementById(Target).value=Number(document.getElementById(Target).value)-Number(t.value);
		if(d) document.getElementById(d).required="";
	}
}
window.onload=initial;

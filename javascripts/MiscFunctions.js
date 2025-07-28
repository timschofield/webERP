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
	/// @todo we do not have any more a 'Lang' element, but the lang attribute is set on the HTML tag
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
	}
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

function eitherOr(o, t) {
	if(o.value!='') t.value='';
	else if(o.value=='NaN') o.value='';
}

function SortSelect() {
	selElem = this;
	var e = new Array;
	th = localStorage.Theme;
	columnText = selElem.innerHTML;
	TableHeader = selElem.parentNode;
	TableBodyElements = TableHeader.parentNode.parentNode.getElementsByTagName('tbody');
	table = TableBodyElements[0];
	i = TableHeader;
	for (var t = 0, n; n = i.cells[t]; t++) {
		if (i.cells[t].innerHTML == columnText) {
			columnNumber = t;
			s = getComputedStyle(i.cells[t], null);
			if (s.cursor == "s-resize") {
				i.cells[t].style.cursor = "n-resize";
				i.cells[t].style.backgroundImage = "url('css/" + th + "/images/descending.png')";
//				i.cells[t].style.backgroundPosition = "right center";
				i.cells[t].style.backgroundRepeat = "no-repeat";
				i.cells[t].style.backgroundSize = "12px";
				direction = "a"
			} else {
				i.cells[t].style.cursor = "s-resize";
				i.cells[t].style.backgroundImage = "url('css/" + th + "/images/ascending.png')";
//				i.cells[t].style.backgroundPosition = "right center";
				i.cells[t].style.backgroundRepeat = "no-repeat";
				i.cells[t].style.backgroundSize = "12px";
				direction = "d"
			}
		}
	}
	for (var r = 0, i; i = table.rows[r]; r++) {
		var o = new Array;
		for (var t = 0, n; n = i.cells[t]; t++) {
			if (i.cells[t].tagName == "TD") {
				o[t] = i.cells[t].innerHTML;
				columnClass = i.cells[columnNumber].className
			}
		}
		e[r] = o
	}
	e.sort(function (e, t) {
		if (direction == "a") {
			if (columnClass == "number") {
				return parseFloat(e[columnNumber].replace(/[,.]/g, '')) - parseFloat(t[columnNumber].replace(/[,.]/g, ''))
			} else if (columnClass == "date") {
				if (e[columnNumber] !== undefined) {
					da = new Date(convertDate(e[columnNumber], localStorage.DateFormat));
				} else {
					da = new Date(e[columnNumber]);
				}
				db = new Date(convertDate(t[columnNumber], localStorage.DateFormat));
				return da > db
			} else {
				return e[columnNumber].localeCompare(t[columnNumber])
			}
		} else {
			if (columnClass == "number") {
				return parseFloat(t[columnNumber].replace(/[,.]/g, '')) - parseFloat(e[columnNumber].replace(/[,.]/g, ''))
			} else if (columnClass == "date") {
				if (e[columnNumber] !== undefined) {
					da = new Date(convertDate(e[columnNumber], localStorage.DateFormat));
				} else {
					da = new Date(e[columnNumber]);
				}
				db = new Date(convertDate(t[columnNumber], localStorage.DateFormat));
				return da <= db
			} else {
				return t[columnNumber].localeCompare(e[columnNumber])
			}
		}
	});
	for (var r = 0, i; i = table.rows[r]; r++) {
		var o = new Array;
		o = e[r];
		for (var t = 0, n; n = i.cells[t]; t++) {
			if (i.cells[t].tagName == "TD") {
				i.cells[t].innerHTML = o[t]
			}
		}
	}
}

function SetSortingEvent() {
	var n = document.getElementsByTagName("th");
	for (i = 0; i < n.length; i++) {
		if (n[i].className == "SortedColumn") {
			n[i].onclick = SortSelect
		}
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
			m = parseInt(dA[0], 10)-1;
			d = parseInt(dA[1], 10);
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
		if(ds[i].className=="SortedColumn") ds[i].onclick=SortSelect;
	}

	/* Notification messages */
	SetSortingEvent();

	/* Move messages from footer div into header div */
	if (document.getElementById('MessageContainerFoot') != null) {
		document.getElementById('MessageContainerHead').appendChild(
			document.getElementById('MessageContainerFoot')
		);

		/* Show footer div after it has been moved to header div */
		document.getElementById('MessageContainerFoot').style["display"] = "block";
	}

	/* Close button dynamic styling*/
	var close = document.getElementsByClassName("MessageCloseButton");
	var i;
	for (i = 0; i < close.length; i++) {
		close[i].onclick = function(){
			var div = this.parentElement;
			div.style.opacity = "0";
			setTimeout(function(){ div.style.display = "none"; }, 600);
		}
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

function update1(s) {
	var ss=s.split(';');
	var sss=ss.map((a)=>document.getElementById(a).value);
	var ttl = sss.reduce((a,b)=>parseFloat(a)+parseFloat(b));
	document.getElementById('ttl').value = ttl;
}

function payVerify(b,a) {
	var s=document.getElementById('update');
	var s=s.getAttribute('data-ids');
	update1(s);
	var cs=document.getElementById('Amount').getAttribute('class');
	if ((parseFloat(document.getElementById(b).value) < parseFloat(parseFloat(document.getElementById(a).value))) && (parseFloat(document.getElementById(b).value) >= 0)){
		if (cs.indexOf('error') == -1) {
			document.getElementById('Amount').className="error" + ' ' + cs;
		}
		event.preventDefault();
	} else {
		if (cs.indexOf('error') != -1) {
			document.getElementById('Amount').className="number";
		}
		return true;
	}
}

function remSelOpt(e, t) {
	len1 = t.options.length;
	for (i = 0; i < len1; i++) {
		if (t.options[i].value == e) {
			t.options[i] = null;
		}
	}
}

function AddScript(e, t) {
	theme = localStorage.Theme;
	document.getElementById("favourites").innerHTML = document.getElementById("favourites").innerHTML + '<option value="' + e + '">' + t + "</option>";
	document.getElementById("PlusMinus").src = "css/" + theme + "/images/subtract.png";
	document.getElementById("PlusMinus").setAttribute("onClick", "javascript: RemoveScript('" + e + "', '" + t + "');");
	UpdateFavourites(e, t)
}

function RemoveScript(e, t) {
	theme = localStorage.Theme;
	remSelOpt(e, document.getElementById("favourites"));
	document.getElementById("PlusMinus").src = "css/" + theme + "/images/add.png";
	document.getElementById("PlusMinus").setAttribute("onClick", "javascript: AddScript('" + e + "', '" + t + "');");
	UpdateFavourites(e, t)
}

function UpdateFavourites(e, t) {
	Target = "UpdateFavourites.php?Script=" + e + "&Title=" + t;
	if (window.XMLHttpRequest) {
		xmlhttp = new XMLHttpRequest
	} else {
		xmlhttp = new ActiveXObject("Microsoft.XMLHTTP")
	}
	xmlhttp.open("GET", Target, true);
	xmlhttp.send();
	return false
}

function ping() {
	Target = "Sessions.php?Id="+localStorage.Id;
	if (window.XMLHttpRequest) {
		xmlhttp = new XMLHttpRequest
	} else {
		xmlhttp = new ActiveXObject("Microsoft.XMLHTTP")
	}
	xmlhttp.open("GET", Target, true);
	xmlhttp.send();
	return false
}

function unload() {
//	alert(localStorage.Theme);
	console.log('hello');
/*	Target = "Logout.php";
	if (window.XMLHttpRequest) {
		xmlhttp = new XMLHttpRequest
	} else {
		xmlhttp = new ActiveXObject("Microsoft.XMLHTTP")
	}
	xmlhttp.open("GET", Target, true);
	xmlhttp.send();
	return false*/
}

//setInterval(ping, 10000);

window.onload=initial;

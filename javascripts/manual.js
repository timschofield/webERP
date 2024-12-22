function ShowHelp(ViewTopic, BookMark) {
	document.getElementById("help-bubble").style.display="block";
	document.getElementById('help-header').innerHTML='<div id="help_exit" class="close_button" onclick="CloseHelp()" title="Close this window">X</div>';
	GetContent("help-content", "doc/Manual/Manual"+ViewTopic+".html", BookMark);
}
function CloseHelp() {
	document.getElementById("help-bubble").style.display="none";
}
function GetContent(id, section, BookMark="") {
	if (window.XMLHttpRequest) {// code for IE7+, Firefox, Chrome, Opera, Safari
		xmlhttp=new XMLHttpRequest();
	} else {// code for IE6, IE5
		xmlhttp=new ActiveXObject("Microsoft.XMLHTTP");
	}

	xmlhttp.onreadystatechange=function() {
		if (xmlhttp.readyState==4 && xmlhttp.status==200) {
			document.getElementById(id).innerHTML=xmlhttp.responseText;
			if (id.toString()=="help-content") {
				var ViewTopic = section.toString().substring(17, section.toString().length-5);
				document.getElementById('help-header').innerHTML=document.getElementById('help-header').innerHTML+document.getElementById(ViewTopic).innerHTML+" - "+document.getElementById(BookMark).innerHTML;
				var help_anchor = document.getElementById(BookMark);
				help_anchor.scrollIntoView({behavior: "smooth"});
			} else {
				if ((CurrentPage.toString().substring(0,8) != "Menu.php")) {
					document.title=document.getElementById("TitleIcon").src = document.getElementsByClassName("page_title_text")[0].children[0].src;
				}
				document.title=document.getElementById("title_bar").textContent.substring(0, document.getElementById("title_bar").textContent.length - 2);
			}
			OverRideClicks();
			SetSortingEvent();
		}
	}
	xmlhttp.open("GET",section,true);
	xmlhttp.setRequestHeader("Content-type","application/x-www-form-urlencoded");
	xmlhttp.setRequestHeader("Cache-Control","no-store, no-cache, must-revalidate");
	xmlhttp.setRequestHeader("Pragma","no-cache");
	xmlhttp.send();
	return false;
};
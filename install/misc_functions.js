function toggle_button(element) {
	if (document.getElementById('next').className == 'is_disabled') {
		document.getElementById('next').setAttribute('class', '')
		document.getElementById('next').href = 'index.php?Page=2&Agreed=Yes';
	} else {
		document.getElementById('next').setAttribute('class', 'is_disabled')
		document.getElementById('next').href = '';
	}
}
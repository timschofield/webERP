(function() {
	var dialog = document.getElementById("logoutDialog");
	var mask = document.getElementById("mask");
	var logoutLink = document.getElementById("logoutLink");
	var cancelBtn = document.getElementById("cancelLogout");
	var confirmBtn = document.getElementById("confirmLogout");

	logoutLink.addEventListener("click", function(e) {
		e.preventDefault();
		mask.style.display = "block";
		dialog.showModal();
	});

	cancelBtn.addEventListener("click", function() {
		mask.style.display = "none";
		dialog.close();
	});

	confirmBtn.addEventListener("click", function() {
		window.location.href = "Logout.php";
	});

	dialog.addEventListener("click", function(e) {
		if (e.target === dialog) {
			mask.style.display = "none";
			dialog.close();
		}
	});

	mask.addEventListener("click", function() {
		mask.style.display = "none";
		dialog.close();
	});
})();

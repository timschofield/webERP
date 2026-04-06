// Hamburger menu toggle
function toggleModuleMenu() {
	var menu = document.getElementById("module-menu");
	var hamburger = document.querySelector(".hamburger-icon");
	
	if (menu) {
		var isActive = menu.classList.toggle("active");
		
		// Hide hamburger when menu is open, show when closed
		if (hamburger) {
			if (isActive) {
				hamburger.style.display = "none";
			} else {
				hamburger.style.display = "flex";
			}
		}
	}
}

// Close module options modal
function closeModuleModal() {
	var modal = document.getElementById("module-options-modal");
	var overlay = document.getElementById("module-modal-overlay");
	if (modal) modal.classList.remove("active");
	if (overlay) overlay.classList.remove("active");
	
	// Hide the elements after animation completes
	setTimeout(function() {
		if (modal) modal.style.display = "none";
		if (overlay) overlay.style.display = "none";
	}, 300);
}

// Show module options modal with menu items
function showModuleModal(moduleLink) {
	var menu = document.getElementById("module-menu");
	var hamburger = document.querySelector(".hamburger-icon");
	
	if (menu) {
		menu.classList.remove("active");
		// Show hamburger when menu closes
		if (hamburger) {
			hamburger.style.display = "flex";
		}
	}
	
	var modal = document.getElementById("module-options-modal");
	var overlay = document.getElementById("module-modal-overlay");
	var modalTitle = document.getElementById("modal-module-title");
	var tabsContainer = document.getElementById("modal-tabs-container");
	
	if (!modal || !overlay || !tabsContainer) return;
	
	// Set title
	if (modalTitle && moduleNames[moduleLink]) {
		modalTitle.textContent = moduleNames[moduleLink];
	}
	
	// Get menu data for this module
	var menuData = moduleMenuData[moduleLink] || {};
	
	// Clear tabs container
	tabsContainer.innerHTML = '';
	
	// If no data, show message
	if (!menuData || Object.keys(menuData).length === 0) {
		tabsContainer.innerHTML = '<div class="no-items">No menu items available</div>';
		modal.style.display = "block";
		overlay.style.display = "block";
		setTimeout(function() {
			modal.classList.add("active");
			overlay.classList.add("active");
		}, 10);
		return;
	}
	
	// Create tabs with proper ordering
	var tabButtons = document.createElement("div");
	tabButtons.className = "tab-buttons";
	
	var tabContents = document.createElement("div");
	tabContents.className = "tab-contents";
	
	// Collect sections in order
	var sections = [];
	for (var section in menuData) {
		if (menuData.hasOwnProperty(section)) {
			sections.push(section);
		}
	}
	// Sort sections in a standard order
	sections.sort(function(a, b) {
		var order = {'Transactions': 0, 'Reports': 1, 'Maintenance': 2};
		var aOrder = order[a] !== undefined ? order[a] : 999;
		var bOrder = order[b] !== undefined ? order[b] : 999;
		if (aOrder === bOrder) return a.localeCompare(b);
		return aOrder - bOrder;
	});
	
	var firstTab = true;
	for (var s = 0; s < sections.length; s++) {
		var section = sections[s];
		var sectionData = menuData[section];
		
		// Create tab button
		var button = document.createElement("button");
		button.className = "tab-btn" + (firstTab ? " active" : "");
		button.textContent = section;
		var tabId = section.toLowerCase().replace(/ /g, "-") + "-tab";
		button.onclick = (function(id) {
			return function() {
				switchTab(this, id);
			};
		})(tabId);
		tabButtons.appendChild(button);
		
		// Create tab content
		var tabContent = document.createElement("div");
		tabContent.id = tabId;
		tabContent.className = "tab-content" + (firstTab ? " active" : "");
		
		// Populate with menu items
		if (sectionData.Caption && sectionData.URL) {
			var html = "<ul class=\"module-menu-items\">";
			for (var i = 0; i < sectionData.Caption.length; i++) {
				var url = sectionData.URL[i];
				// Ensure URL has the /webERP/ base path
				if (!url.includes('/webERP/')) {
					url = '/webERP/' + url;
				}
				html += "<li><a href=\"" + url + "\">" + sectionData.Caption[i] + "</a></li>";
			}
			html += "</ul>";
			tabContent.innerHTML = html;
		}
		
		tabContents.appendChild(tabContent);
		firstTab = false;
	}
	
	tabsContainer.appendChild(tabButtons);
	tabsContainer.appendChild(tabContents);
	
	// Re-attach listeners to new links
	attachLinkListeners();
	
	// Show modal
	modal.style.display = "block";
	overlay.style.display = "block";
	setTimeout(function() {
		modal.classList.add("active");
		overlay.classList.add("active");
	}, 10);
}

function switchTab(button, tabId) {
	// Remove active from all buttons and tabs
	var tabsContainer = document.getElementById("modal-tabs-container");
	if (tabsContainer) {
		var buttons = tabsContainer.querySelectorAll(".tab-btn");
		buttons.forEach(function(btn) {
			btn.classList.remove("active");
		});
		var tabs = tabsContainer.querySelectorAll(".tab-content");
		tabs.forEach(function(tab) {
			tab.classList.remove("active");
		});
	}
	
	// Add active to clicked button and corresponding tab
	button.classList.add("active");
	var tab = document.getElementById(tabId);
	if (tab) tab.classList.add("active");
}

// Open content modal with AJAX
var openModals = {};
var minimizedModals = {};
var activeModalCount = 0;

function openContentModal(url, title) {
	var modalId = "modal-" + (++activeModalCount);
	var container = document.getElementById("modals-container");
	
	if (!container) {
		container = document.createElement("div");
		container.id = "modals-container";
		container.className = "modals-container";
		document.body.appendChild(container);
	}
	
	// Close the module menu modal when opening content modal
	var moduleModal = document.getElementById("module-options-modal");
	var moduleOverlay = document.getElementById("module-modal-overlay");
	if (moduleModal) {
		moduleModal.classList.remove("active");
		if (moduleOverlay) moduleOverlay.classList.remove("active");
		setTimeout(function() {
			moduleModal.style.display = "none";
			if (moduleOverlay) moduleOverlay.style.display = "none";
		}, 300);
	}
	
	var modal = document.createElement("div");
	modal.id = modalId;
	modal.className = "content-modal active";
	modal.setAttribute("data-url", url);
	modal.innerHTML = '<div class="content-modal-window">' +
		'<div class="content-modal-header">' +
		'<h2 class="modal-title">' + title + '</h2>' +
		'<div class="content-modal-controls">' +
		'<button class="content-modal-minimize" title="Minimize">_</button>' +
		'<button class="content-modal-maximize" title="Maximize">□</button>' +
		'<span class="content-modal-close">&times;</span>' +
		'</div></div>' +
		'<div class="content-modal-body"><div class="loading-spinner">Loading...</div></div>' +
		'</div>';
	
	container.appendChild(modal);
	openModals[modalId] = {url: url, title: title, maximized: false};
	
	// Attach button event listeners IMMEDIATELY after DOM insertion
	var minimizeBtn = modal.querySelector(".content-modal-minimize");
	var maximizeBtn = modal.querySelector(".content-modal-maximize");
	var closeBtn = modal.querySelector(".content-modal-close");
	
	if (minimizeBtn) {
		minimizeBtn.onclick = function(e) {
			e.stopPropagation();
			minimizeContentModal(modalId);
		};
	}
	if (maximizeBtn) {
		maximizeBtn.onclick = function(e) {
			e.stopPropagation();
			maximizeContentModal(modalId);
		};
	}
	if (closeBtn) {
		closeBtn.onclick = function(e) {
			e.stopPropagation();
			closeContentModal(modalId);
		};
	}
	
	bringModalToFront(modalId);
	
	// Resolve relative URLs and ensure proper path construction
	if (url) {
		// If it's a full URL, extract just the pathname
		if (url.startsWith('http')) {
			try {
				const urlObj = new URL(url);
				url = urlObj.pathname + urlObj.search + urlObj.hash;
			} catch (e) {
				// If URL parsing fails, use as-is
			}
		}
		
		// Now url should be either:
		// - /webERP/AccountSections.php (absolute path)
		// - AccountSections.php (relative path from menu)
		if (!url.startsWith('/')) {
			// Relative URL - prepend RootPath
			if (window.RootPath) {
				url = window.RootPath + '/' + url;
			} else {
				url = '/' + url;
			}
		}
		// If it starts with /, use as-is (already has full path)
	}
	
	// Load content
	console.log("Fetching URL:", url, "RootPath:", window.RootPath);
	fetch(url, {
		method: "GET",
		headers: {"X-Requested-With": "XMLHttpRequest"},
		credentials: "same-origin"
	})
	.then(response => {
		if (!response.ok) throw new Error("HTTP " + response.status + ": " + response.statusText);
		return response.text();
	})
	.then(html => {
		var modalBody = document.querySelector("#" + modalId + " .content-modal-body");
		if (modalBody) {
			// Strip the minimal HTML wrapper if present
			var contentStart = html.indexOf('<body');
			if (contentStart > -1) {
				html = html.substring(contentStart);
				var bodyEnd = html.indexOf('>');
				if (bodyEnd > -1) {
					html = html.substring(bodyEnd + 1);
				}
				var contentEnd = html.lastIndexOf('</body>');
				if (contentEnd > -1) {
					html = html.substring(0, contentEnd);
				}
			}
			// Clear old event listeners by removing processed markers
			var oldLinks = modalBody.querySelectorAll("a[data-ajax-processed]");
			oldLinks.forEach(function(link) {
				link.removeAttribute("data-ajax-processed");
			});
			var oldForms = modalBody.querySelectorAll("form[data-ajax-processed]");
			oldForms.forEach(function(form) {
				form.removeAttribute("data-ajax-processed");
			});
			
			modalBody.innerHTML = html;
			attachLinkListeners();
			attachFormListeners();
			if (typeof initial === "function") initial();
		}
	})
	.catch(error => {
		console.error("Error loading page:", error);
		var modalBody = document.querySelector("#" + modalId + " .content-modal-body");
		if (modalBody) {
			modalBody.innerHTML = "<div class=\"error-message\">Error loading page: " + error.message + "</div>";
		}
	});
}

// Load content into an existing modal
function loadContentIntoModal(url, modalId) {
	var modal = document.getElementById(modalId);
	if (!modal) return;
	
	var modalBody = modal.querySelector(".content-modal-body");
	if (!modalBody) return;
	
	modalBody.innerHTML = "<div class=\"loading-message\">Loading...</div>";
	
	fetch(url + (url.indexOf("?") > -1 ? "&" : "?") + "ajax=1", {
		headers: {"X-Requested-With": "XMLHttpRequest"}
	})
	.then(response => {
		if (!response.ok) {
			throw new Error("HTTP " + response.status);
		}
		return response.text();
	})
	.then(html => {
		// Strip the HTML wrapper if present
		var contentStart = html.indexOf('<body');
		if (contentStart > -1) {
			html = html.substring(contentStart);
			var bodyEnd = html.indexOf('>');
			if (bodyEnd > -1) {
				html = html.substring(bodyEnd + 1);
			}
			var contentEnd = html.lastIndexOf('</body>');
			if (contentEnd > -1) {
				html = html.substring(0, contentEnd);
			}
		}
		modalBody.innerHTML = html;
		attachLinkListeners();
		attachFormListeners();
	})
	.catch(error => {
		console.error("Error loading page:", error);
		modalBody.innerHTML = "<div class=\"error-message\">Error loading page: " + error.message + "</div>";
	});
}

function closeContentModal(modalId) {
	var modal = document.getElementById(modalId);
	if (modal) {
		modal.classList.remove("active");
		setTimeout(function() {
			modal.remove();
			delete openModals[modalId];
			removeMinimizedIcon(modalId);
		}, 300);
	}
}

function minimizeContentModal(modalId) {
	var modal = document.getElementById(modalId);
	if (modal) {
		var title = modal.querySelector(".modal-title").textContent;
		var url = modal.getAttribute("data-url");
		modal.classList.add("minimized");
		setTimeout(function() {
			modal.style.display = "none";
		}, 300);
		addMinimizedIcon(modalId, url, title);
	}
}

function maximizeContentModal(modalId) {
	var modal = document.getElementById(modalId);
	if (modal && openModals[modalId]) {
		if (openModals[modalId].maximized) {
			modal.classList.remove("maximized");
			openModals[modalId].maximized = false;
		} else {
			modal.classList.add("maximized");
			openModals[modalId].maximized = true;
		}
		bringModalToFront(modalId);
	}
}

function restoreContentModal(modalId) {
	var modal = document.getElementById(modalId);
	if (modal) {
		modal.classList.remove("minimized");
		modal.style.display = "block";
		setTimeout(function() {
			modal.classList.add("active");
		}, 10);
		removeMinimizedIcon(modalId);
		bringModalToFront(modalId);
	}
}

function bringModalToFront(modalId) {
	var modal = document.getElementById(modalId);
	if (modal) {
		document.querySelectorAll(".content-modal").forEach(function(m) {
			m.style.zIndex = 1900;
		});
		modal.style.zIndex = 2000;
	}
}

function addMinimizedIcon(modalId, url, title) {
	var bar = document.getElementById("minimized-modals-bar");
	if (!bar) {
		bar = document.createElement("div");
		bar.id = "minimized-modals-bar";
		bar.className = "minimized-modals-bar";
		document.body.appendChild(bar);
	}
	
	var container = document.createElement("div");
	container.className = "minimized-modal-container";
	container.setAttribute("data-modal-id", modalId);
	
	var canvas = document.createElement("canvas");
	canvas.width = 128;
	canvas.height = 96;
	canvas.className = "minimized-modal-preview";
	
	var ctx = canvas.getContext("2d");
	ctx.fillStyle = "#ffffff";
	ctx.fillRect(0, 0, 128, 96);
	ctx.strokeStyle = "#588BB6";
	ctx.lineWidth = 2;
	ctx.strokeRect(0, 0, 128, 96);
	ctx.fillStyle = "#588BB6";
	ctx.fillRect(0, 0, 128, 20);
	ctx.fillStyle = "#ffffff";
	ctx.font = "11px Arial";
	ctx.textBaseline = "middle";
	ctx.fillText(title.substring(0, 14), 5, 10);
	ctx.strokeStyle = "#e0e0e0";
	ctx.lineWidth = 1;
	for (var i = 0; i < 10; i++) {
		ctx.beginPath();
		ctx.moveTo(i * 13, 20);
		ctx.lineTo(i * 13 + 10, 96);
		ctx.stroke();
	}
	
	var button = document.createElement("button");
	button.className = "minimized-modal-button";
	button.title = title;
	button.onclick = function() {
		restoreContentModal(modalId);
	};
	button.appendChild(canvas);
	
	var label = document.createElement("div");
	label.className = "minimized-modal-label";
	label.textContent = title.substring(0, 12);
	button.appendChild(label);
	
	container.appendChild(button);
	bar.appendChild(container);
	bar.style.display = "flex";
	minimizedModals[modalId] = container;
}

function removeMinimizedIcon(modalId) {
	if (minimizedModals[modalId]) {
		minimizedModals[modalId].remove();
		delete minimizedModals[modalId];
		if (Object.keys(minimizedModals).length === 0) {
			var bar = document.getElementById("minimized-modals-bar");
			if (bar) bar.style.display = "none";
		}
	}
}

// Attach event listeners to links
function attachLinkListeners() {
	document.querySelectorAll("a").forEach(function(link) {
		if (link.hasAttribute("data-ajax-processed")) return;
		link.setAttribute("data-ajax-processed", "true");
		link.addEventListener("click", function(e) {
			var href = this.getAttribute("href");
			var target = this.getAttribute("target");
			
			if (!href || href.indexOf("://") > -1 || target === "_blank" || href.startsWith("#") || 
				this.hasAttribute("data-no-ajax") || href.indexOf("Logout.php") > -1) {
				return;
			}
			
			e.preventDefault();
			
			// Check if we're inside a content modal
			var currentModal = this.closest(".content-modal");
			if (currentModal) {
				// Reload content in the current modal
				var modalId = currentModal.id;
				console.log("Loading into existing modal:", modalId, "URL:", href);
				loadContentIntoModal(href, modalId);
			} else {
				// Open new modal if not inside one
				var linkText = this.textContent || "Page";
				console.log("Opening new modal with URL:", href);
				openContentModal(href, linkText);
			}
		});
	});
}

// Attach event listeners to forms
function attachFormListeners() {
	document.querySelectorAll("form").forEach(function(form) {
		if (form.hasAttribute("data-ajax-processed")) return;
		if (form.hasAttribute("data-no-ajax") || form.target === "_blank") return;
		
		form.setAttribute("data-ajax-processed", "true");
		form.addEventListener("submit", function(e) {
			e.preventDefault();
			
			var formElement = this;
			var formAction = formElement.action || window.location.pathname;
			var formMethod = (formElement.method || "POST").toUpperCase();
			
			var modal = document.querySelector(".content-modal.active");
			var modalBody = modal ? modal.querySelector(".content-modal-body") : null;
			
			if (!modalBody) return;
			
			modalBody.innerHTML = "<div class=\"loading-spinner\">Submitting...</div>";
			
			var formData = new FormData(formElement);
			
			// Add submit button to FormData
			var submitButtons = [];
			var allButtons = formElement.querySelectorAll("button, input");
			for (var k = 0; k < allButtons.length; k++) {
				if ((allButtons[k].type === "submit" || allButtons[k].getAttribute("type") === "submit") && !submitButtons.length) {
					submitButtons.push(allButtons[k]);
				}
			}
			if (submitButtons.length > 0) {
				var submitButton = submitButtons[0];
				var submitName = submitButton.getAttribute("name");
				var submitValue = submitButton.getAttribute("value");
				if (submitName && submitValue) {
					formData.append(submitName, submitValue);
				}
			}
			
			var requestOptions = {
				method: formMethod,
				credentials: "same-origin",
				headers: {"X-Requested-With": "XMLHttpRequest"}
			};
			
			var submitUrl = formAction;
			if (formMethod === "POST") {
				requestOptions.body = formData;
			} else {
				var params = new URLSearchParams(formData);
				submitUrl = formAction + (formAction.indexOf("?") > -1 ? "&" : "?") + params.toString();
			}
			
			fetch(submitUrl, requestOptions)
			.then(response => {
				if (!response.ok) throw new Error("Network response was not ok: " + response.status);
				return response.text();
			})
			.then(html => {
				modalBody.innerHTML = html;
				attachLinkListeners();
				attachFormListeners();
				if (typeof initial === "function") initial();
			})
			.catch(error => {
				console.error("Error submitting form:", error);
				modalBody.innerHTML = "<div class=\"error-message\">Error submitting form.</div>";
			});
		});
	});
}

// Initialize on page load
document.addEventListener("DOMContentLoaded", function() {
	attachLinkListeners();
	attachFormListeners();
});

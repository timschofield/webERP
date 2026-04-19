<?php
// Multi-Modal Window Management System
// This JavaScript provides support for multiple concurrent modal windows

$MultiModalScript = <<<'SCRIPT'
		// Multi-modal system
		var openModals = {};
		var minimizedModals = {};
		var activeModalCount = 0;
		var modalsContainer = null;
		
		function getModalsContainer() {
			if (!modalsContainer) {
				modalsContainer = document.getElementById("modals-container");
				if (!modalsContainer) {
					modalsContainer = document.createElement("div");
					modalsContainer.id = "modals-container";
					modalsContainer.className = "modals-container";
					document.body.appendChild(modalsContainer);
				}
			}
			return modalsContainer;
		}
		
		function createModalWindow(url, title) {
			var container = getModalsContainer();
			var modalId = "modal-" + (++activeModalCount);
			var modal = document.createElement("div");
			modal.id = modalId;
			modal.className = "content-modal active";
			modal.setAttribute("data-url", url);
			modal.innerHTML = '<div class="content-modal-window">' +
				'<div class="content-modal-header">' +
				'<h2 class="modal-title">' + title + '</h2>' +
				'<div class="content-modal-controls">' +
				'<button class="content-modal-minimize" onclick="minimizeContentModal(\'' + modalId + '\')" title="Minimize">_</button>' +
				'<button class="content-modal-maximize" onclick="maximizeContentModal(\'' + modalId + '\')" title="Maximize">□</button>' +
				'<span class="content-modal-close" onclick="closeContentModal(\'' + modalId + '\')">&times;</span>' +
				'</div></div>' +
				'<div class="content-modal-body"><div class="loading-spinner">Loading...</div></div>' +
				'</div>';
			
			container.appendChild(modal);
			openModals[modalId] = {url: url, title: title, maximized: false};
			bringModalToFront(modalId);
			return modalId;
		}
		
		function openContentModal(url, title) {
			var modalId = createModalWindow(url, title);
			
			// Close module options modal if open
			closeModuleModal();
			
			// Load content
			fetch(url, {
				method: "GET",
				headers: {"X-Requested-With": "XMLHttpRequest"},
				credentials: "same-origin"
			})
			.then(response => {
				if (!response.ok) throw new Error("Network response was not ok");
				return response.text();
			})
			.then(html => {
				var modalBody = document.querySelector("#" + modalId + " .content-modal-body");
				if (modalBody) {
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
					modalBody.innerHTML = "<div class=\"error-message\">Error loading page. <a href=\"" + url + "\" data-no-ajax=\"true\">Click here to open in new tab.</a></div>";
				}
			});
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
				modal.setAttribute("data-minimized", "true");
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
				modal.removeAttribute("data-minimized");
				modal.style.display = "block";
				setTimeout(function() {
					modal.classList.add("active");
				}, 10);
				removeMinimizedIcon(modalId);
				bringModalToFront(modalId);
			}
		}
		
		function addMinimizedIcon(modalId, url, title) {
			var bar = document.getElementById("minimized-modals-bar");
			var track = document.getElementById("minimized-modals-track");
			if (!track) track = bar;
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
			track.appendChild(container);
			bar.style.display = "flex";
			minimizedModals[modalId] = container;
			if (typeof updateCarouselArrows === "function") updateCarouselArrows();
		}
		
		function removeMinimizedIcon(modalId) {
			if (minimizedModals[modalId]) {
				minimizedModals[modalId].remove();
				delete minimizedModals[modalId];
				if (Object.keys(minimizedModals).length === 0) {
					document.getElementById("minimized-modals-bar").style.display = "none";
				}
				if (typeof updateCarouselArrows === "function") updateCarouselArrows();
			}
		}
SCRIPT;

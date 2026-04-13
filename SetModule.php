<?php
// Set the module in the session
// This is called via AJAX when a user selects a module from the menu

$PathPrefix = __DIR__ . '/';
include($PathPrefix . 'includes/session.php');

// Check if this is an AJAX request
if (!isset($_SERVER['HTTP_X_REQUESTED_WITH']) || strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) != 'xmlhttprequest') {
	http_response_code(403);
	echo json_encode(['success' => false, 'error' => 'Invalid request']);
	exit;
}

// Check if module parameter is provided
if (!isset($_GET['module']) || empty($_GET['module'])) {
	http_response_code(400);
	echo json_encode(['success' => false, 'error' => 'Module parameter required']);
	exit;
}

// Validate that the module is in the allowed list
include_once($PathPrefix . 'includes/MainMenuLinksArray.php');

$requestedModule = $_GET['module'];
$moduleValid = false;

if (isset($ModuleLink) && is_array($ModuleLink)) {
	foreach ($ModuleLink as $index => $moduleLink) {
		if ($moduleLink === $requestedModule && isset($_SESSION['ModulesEnabled'][$index]) && $_SESSION['ModulesEnabled'][$index] == 1) {
			$moduleValid = true;
			break;
		}
	}
}

if (!$moduleValid) {
	http_response_code(403);
	echo json_encode(['success' => false, 'error' => 'Module not authorized']);
	exit;
}

// Set the module in the session
$_SESSION['Module'] = $requestedModule;

// Return success
header('Content-Type: application/json');
echo json_encode([
	'success' => true,
	'module' => $requestedModule
]);

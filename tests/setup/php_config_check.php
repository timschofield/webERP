<?php

// This file allows checking that all the php settings required for testing are fine.
// It is requested from the phpunit web crawler.
// It returns a json response.
// NB: given the sensitive nature of the returned data, we only allow this to run when called from the test suite - using
// out-of-band info saved to disk

$randIdFile = sys_get_temp_dir() . '/phpunit_rand_id.txt';
$isTestSuiteCall = false;
if (isset($_GET['phpunit_rand_id']) && $_GET['phpunit_rand_id'] !== '' && file_exists($randIdFile)) {
	if (file_get_contents($randIdFile) == $_GET['phpunit_rand_id']) {
		$isTestSuiteCall = true;
	}
}
if (!$isTestSuiteCall) {
	http_response_code(404);
	exit(1);
}

$extensions = array_map('strtolower', get_loaded_extensions());
sort($extensions);
$data = array(
	'active_extensions' => $extensions,
	'current_user' => get_current_user(),
	'ini_settings' => ini_get_all(null, false)
);

header('Content-Type: application/json');
echo json_encode($data, JSON_PRETTY_PRINT);

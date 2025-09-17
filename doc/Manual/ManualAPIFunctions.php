<?php

// in case this file is accessed directly...
if (!isset($PathPrefix)) {
	$PathPrefix = __DIR__ . '/../../';
}
if (!isset($RootPath)) {
	$RootPath = htmlspecialchars(dirname(dirname(dirname($_SERVER['PHP_SELF']))), ENT_QUOTES, 'UTF-8');
}

include($PathPrefix . 'api/api_errorcodes.php');

$Title = 'API documentation';

/// @todo move to html5 as the rest of the app
echo '<html xmlns="http://www.w3.org/1999/xhtml"><head><title>' . $Title . '</title>';
echo '<link rel="icon" href="' . $RootPath . '/favicon.ico" type="image/x-icon" />';
echo '<meta http-equiv="Content-Type" content="text/html; charset=utf-8">';

echo '</head>';

echo '<body>';

// avoid sending an xml-rpc request to self, interrogate directly the server
$dispatchMap = include($PathPrefix . 'api/api_xml-rpc_definition.php');
$server = new PhpXmlRpc\Server($dispatchMap, false);
$response = PhpXmlRpc\Server::_xmlrpcs_listMethods($server);
$answer = $response->value();

$encoder = new \PhpXmlRpc\Encoder();
for ($i=0; $i<sizeof($answer); $i++) {
	$method = $answer[$i];
	echo '<br /><table border="1" width="80%"><tr><th colspan="3"><h4>'.__('Method name').__('  -  ').'<b>'.htmlspecialchars($method->scalarval()).'</b></h4></th></tr>';
	$request = new PhpXmlRpc\Request("system.methodHelp", array($method));
	$response = PhpXmlRpc\Server::_xmlrpcs_methodHelp($server, $request);
	$signature = $encoder->decode($response->value());
	echo $signature.'<br />';
}

echo '</body>';

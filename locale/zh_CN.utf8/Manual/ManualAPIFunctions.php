<?php

$PageSecurity = 1;
$PathPrefix = __DIR__ . '/../../../';
//include('../../includes/session.php');
include('../../api/api_errorcodes.php');

$Title = 'API documentation';

/// @todo move to html5, as the rest of the app uses
echo '<html xmlns="http://www.w3.org/1999/xhtml"><head><title>' . $Title . '</title>';
echo '<link rel="icon" href="'. $RootPath.'/favicon.ico" type="image/x-icon" />';
/// @todo change the translation string, as it makes no sense! In zh_CN, we are translating 'iso-8859-1' as 'utf-8'!
echo '<meta http-equiv="Content-Type" content="text/html; charset=' . __('iso-8859-1') . '">';
echo '<link href="'.$RootPath. '/../../css/'. $_SESSION['Theme'] .'/default.css" REL="stylesheet" TYPE="text/css">';
echo '</head>';

echo '<body>';

// avoid sending an xml-rpc request to self, interrogate directly the server
$dispatchMap = include('api/includes/api_xml-rpc_definition.php');
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

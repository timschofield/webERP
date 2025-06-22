<?php

$RootPath = dirname(htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8'));
$PathPrefix= $_SERVER['HTTP_HOST'].$RootPath.'/../../';

include('vendor/phpxmlrpc/phpxmlrpc/lib/xmlrpc.inc');
include('vendor/phpxmlrpc/phpxmlrpc/lib/xmlrpcs.inc');
include('api/api_errorcodes.php');

$Title = 'API documentation';

echo '<html xmlns="http://www.w3.org/1999/xhtml"><head><title>' . $Title . '</title>';
echo '<link REL="shortcut icon" HREF="'. $RootPath.'/favicon.ico">';
echo '<link REL="icon" HREF="' . $RootPath.'/favicon.ico">';
echo '<meta http-equiv="Content-Type" content="text/html; charset=utf-8">';

echo '</head>';

echo '<body>';

// avoid sending an xml-rpc request to self, interrogate directly the server
$server = include 'api/api_xml-rpc_server.php';
$response = PhpXmlRpc\Server::_xmlrpcs_listMethods($server);
$answer = $response->value();

for ($i=0; $i<sizeof($answer); $i++) {
	$method = $answer[$i];
	echo '<br /><table border="1" width="80%"><tr><th colspan="3"><h4>'._('Method name')._('  -  ').'<b>'.htmlspecialchars($method->scalarval()).'</b></h4></th></tr>';
	$msg = new xmlrpcmsg("system.methodHelp", array($method));
	$response = PhpXmlRpc\Server::_xmlrpcs_methodHelp($server, $msg);
	$signature = php_xmlrpc_decode($response->value());
	echo $signature.'<br />';
}

echo '</body>';

?>

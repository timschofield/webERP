<?php

/* $Id: ManualAPIFunctions.php 3152 2009-12-11 14:28:49Z tim_schofield $ */
$RootPath = dirname(htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8'));
$PathPrefix= $_SERVER['HTTP_HOST'].$RootPath.'/../../';

include('../../xmlrpc/lib/xmlrpc.inc');
include('../../api/api_errorcodes.php');

$Title = 'API documentation';

echo '<html xmlns="http://www.w3.org/1999/xhtml"><head><title>' . $Title . '</title>';
echo '<link REL="shortcut icon" HREF="'. $RootPath.'/favicon.ico">';
echo '<link REL="icon" HREF="' . $RootPath.'/favicon.ico">';
echo '<meta http-equiv="Content-Type" content="text/html; charset=utf-8">';

echo '</head>';

echo '<body>';

$ServerString = $_SERVER['HTTP_HOST'].$RootPath;
$FirstBitOfURL = mb_substr($ServerString,0,mb_strpos($ServerString,'/doc/Manual'));


$ServerURL = "http://".  $FirstBitOfURL ."/api/api_xml-rpc.php";
$DebugLevel = 0; //Set to 0,1, or 2 with 2 being the highest level of debug info

$msg = new xmlrpcmsg("system.listMethods", array());

$client = new xmlrpc_client($ServerURL);
$client->setDebug($DebugLevel);

$response = $client->send($msg);
$answer = php_xmlrpc_decode($response->value());

for ($i=0; $i<sizeof($answer); $i++) {
	echo '<br /><table border="1" width="80%"><tr><th colspan="3"><h4>'._('Method name')._('  -  ').'<b>'.$answer[$i].'</b></h4></th></tr>';
	$method = php_xmlrpc_encode($answer[$i]);
	$msg = new xmlrpcmsg("system.methodHelp", array($method));

	$client = new xmlrpc_client($ServerURL);
	$client->setDebug($DebugLevel);

	$response = $client->send($msg);
	$signature = php_xmlrpc_decode($response->value());
	echo $signature.'<br />';
}

echo '</body>';

?>
<?php
/* $Id$*/

$PageSecurity = 15;
include('includes/session.inc');
$title = _('Create trac item for bugs');

include 'xmlrpc/lib/xmlrpc.inc';
include('includes/header.inc');


$ServerURL = "https://sourceforge.net/apps/trac/web-erp/";
$DebugLevel = 2; //Set to 0,1, or 2 with 2 being the highest level of debug info

$msg = new xmlrpcmsg("system.listMethods", array());

$client = new xmlrpc_client($ServerURL);
$client->setDebug($DebugLevel);

$response = $client->send($msg);

$answer = php_xmlrpc_decode($response->value());

echo $answer;

include('includes/footer.inc');
?>
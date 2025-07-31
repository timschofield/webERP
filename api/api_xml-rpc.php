<?php

	$dispatchMap = include('api_xml-rpc_definition.php');

	$server = new \PhpXmlRpc\Server($dispatchMap, false);
	$server->service();

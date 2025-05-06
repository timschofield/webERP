<?php

/////////////////////////////////////////////////////////////////////
//  Creates initial HTML and CSS styles for PDF document
/////////////////////////////////////////////////////////////////////
$AdminTeam = $Company . ' Admin Team';

// Initialize HTML with CSS styles
$HTML = '<!DOCTYPE html>
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
<title>' . $CoreFileName . '</title>
</head>
<body>';

// Include centralized CSS styling
include('includes/KLPersonaliaPDFCSStyling.php');

?>
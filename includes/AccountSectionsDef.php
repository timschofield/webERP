<?php

$Sections = array();
$SQL = 'SELECT sectionid, sectionname FROM accountsection ORDER by sectionid';
$SectionResult = DB_query($SQL);
while( $SecRow = DB_fetch_array($SectionResult) ) {
	$Sections[$SecRow['sectionid']] = $SecRow['sectionname'];
}
DB_free_result($SectionResult); // no longer needed

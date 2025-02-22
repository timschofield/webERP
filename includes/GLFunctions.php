<?php

function InsertGLTags($TagArray) {
	if (!empty($TagArray)) {
		$ErrMsg = _('Cannot insert a GL tag for the journal line because');
		$DbgMsg = _('The SQL that failed to insert the GL tag record was');
		foreach ($TagArray as $Tag) {
			$SQL = "INSERT INTO gltags 
					VALUES ( LAST_INSERT_ID(),
							'" . $Tag . "')";
			$Result = DB_query($SQL, $ErrMsg, $DbgMsg, true);
        }
	}
    return true;
}

?>
<?php
function write_config_db() {
	global $path_to_root, $db_connection;
	
	$msg = "<?php\n\n";
	$msg .= "/* \$Revision: 1.4 $ */\n";
	$msg .= "// User configurable variables\n";
	$msg .= "//---------------------------------------------------\n\n";
	$msg .= "//DefaultLanguage to use for the login screen and the setup of new users - the users language selection will override\n";
	$msg .= "\$DefaultLanguage ='en_GB';\n\n";
	$msg .= "// Whether to display the demo login and password or not on the login screen\n";
	$msg .= "\$allow_demo_mode = True;\n\n";
	$msg .= "// webERP version\n\n";
	$msg .= "\$Version = '3.11';\n\n";
	$msg .= "//  Connection information for the database\n";
	$msg .= "// \$host is the computer ip address or name where the database is located\n";
	$msg .= "// assuming that the web server is also the sql server\n";		
	$msg .= "\$host = '" . $db_connection['host'] . "';\n\n";
	
	$msg .= "// assuming that the web server is also the sql server\n";		
	$msg .= "\$dbType = 'mysqli';\n";
	
	$msg .= "// assuming that the web server is also the sql server\n";		
	$msg .= "\$dbuser = '" . $db_connection['dbuser'] . "';\n";
	$msg .= "// assuming that the web server is also the sql server\n";		
	$msg .= "\$dbpassword = '" . $db_connection['dbpassword'] . "';\n";
	
	$msg .= "// The timezone of the business - this allows the possibility of having;\n";
	
	$msg .= "putenv('" . $db_connection['timezone'] . "');\n";
	$msg .= "\$AllowCompanySelectionBox = true;\n";
	$msg .= "\$DefaultCompany = '" . $db_connection['name']. "';\n";
	$msg .= "\$SessionLifeTime = 3600;\n";
	$msg .= "\$MaximumExecutionTime =120;\n";
	$msg .= "\$CryptFunction = 'sha1';\n";
	$msg .= "\$DefaultClock = 12;\n";
	
	$msg .= "\$rootpath = dirname(\$_SERVER['PHP_SELF']);\n";		
	$msg .= "if (isset(\$DirectoryLevelsDeep)){\n";		
	$msg .= "for (\$i=0;\$i<\$DirectoryLevelsDeep;\$i++){\n";		
	$msg .= "\$rootpath = substr(\$rootpath,0, strrpos(\$rootpath,'/'));\n";		
	$msg .= "} }\n";		
	
	$msg .= "if (\$rootpath == '/' OR \$rootpath == '\\\') {;\n";		
	$msg .= "\$rootpath = '';\n";		
	$msg .= "}\n";		
	$msg .= "error_reporting (E_ALL & ~E_NOTICE);\n";		
	
	$msg .= "?>";

	$filename = $path_to_root . "/config.php";
	// Check if the file is writable first.
	//if(is_writable($filename)){
		if (!$zp = fopen($filename, 'w')){
			return -1;
		} else {
			if (!fwrite($zp, $msg)){
				fclose($zp);
				return -2;
			}
			// close file
			fclose($zp);
		}
	//} else {
	//	echo $filename . ' is really not writable!! ' ;
	//	exit;
	//	return -3;
	//}
	return 0;
}

function db_create_db($connection) {
	$db = mysql_connect($connection['host'] ,
		$connection['dbuser'], $connection['dbpassword']);
	if (!mysql_select_db($connection['dbname'], $db)) {
		$sql = 'CREATE DATABASE ' . $connection['dbname'];
		if (!mysql_query($sql)){
			return 0;
		}
		mysql_select_db($connection['dbname'], $db);
	}
	return $db;
}

function db_drop_db($connection) {
	if ($connection['tbpref'] == "") {
		$sql = "DROP DATABASE " . $connection["dbname"] . "";
		return mysql_query($sql);
	} else {
    	$res = db_query('show table status');
    	$all_tables = array();
    	while($row = db_fetch($res)) {
    		$all_tables[] = $row;
		}
        // get table structures
		foreach ($all_tables as $table) {
			if (strpos($table['Name'], $connection['tbpref']) === 0) {
				db_query("DROP TABLE `".$table['Name'] . "`");
			}
		}
		//deleting the tables, how??
		return true;
	}
}

function db_import($filename, $connection) {
	$data_queries = array();
	$drop_queries = array();
	$table_queries = array();

	// uncrompress gziped backup files
	if (strpos($filename, ".gzip") || strpos($filename, ".GZIP")){
		$lines = db_ungzip("lines", $filename);
	} elseif (strpos($filename, ".zip") || strpos($filename, ".ZIP")){
		$lines = db_unzip("lines", $filename);
	} else {
		$lines = file("". $filename);
	}
	// divide insert and create sql queries
	// $table is set to TRUE if the next line belongs to a create sql query
	$table = false;
	foreach($lines as $line) {
		$line = trim($line);

		$line = str_replace("0_", $connection["tbpref"], $line);
		// $line = str_replace("Y_", "0_", $line);
		// the last line did not belong to a 'create' sql query
		if (!$table) {
			// this line does not, too
			if (strtolower(substr($line,0,6)) == "insert") {
				$data_queries[] = substr($line, 0, strlen($line) - 1);

			// this line does not, too
			} elseif (strtolower(substr($line,0,6)) == "update") {
				$data_queries[] = substr($line, 0, strlen($line) - 1);

			// this line does not, too
			} elseif (strtolower(substr($line, 0, 20)) == "drop table if exists"){
				$drop_queries[] = substr($line, 0, strlen($line) - 1);

			// this line does!
			} elseif (strtolower(substr($line, 0, 6)) == "create") {
				$table = true;
				$table_queries[] = $line . "\n";
			} elseif (strtolower(substr($line, 0, 11)) == "alter table") {
				$data_queries[] = substr($line, 0, strlen($line) - 1);
			}

		// the current line belongs to a create sql query
		} else {
			// create sql query ending in this line
			if (strtolower(substr($line, 0, 1)) == ")") {
				$table = false;
				$line = substr($line,0,strlen($line)-1);
  		    }
			$table_queries[count($table_queries) - 1] .= $line . "\n";
		}
	}

	$sql_error = false;

	// execute drop tables if exists queries
	if (is_array($drop_queries)) {
		foreach($drop_queries as $drop_query) {
			$sql_error = false;
			if (!db_query($drop_query))	{
				$sql_error = true;
				//if ($CONF['import_error']) echo nl2br($drop_query)."\n<div class=\"bold_left\">".mysql_error()."</div><br>\n";
			}
		}
	}

	// execute create tables queries
	if (is_array($table_queries)) {
		foreach($table_queries as $table_query)	{
			$sql_error = false;
			if (!db_query($table_query)){
				$sql_error = true;
				//if ($CONF['import_error']) echo nl2br($table_query)."\n<div class=\"bold_left\">".mysql_error()."</div><br>\n";
			}
		}
	}

	// execute insert data queries
	if (is_array($data_queries)) {
		foreach($data_queries as $data_query) {
			$sql_error = false;
			if (!db_query($data_query))	{
				//if ($CONF['import_error']) echo $data_query."\n<div class=\"bold_left\">".mysql_error()."</div><br>\n";
				$sql_error = true;
				return false;
			}
		}
	}

	// show number successful executed querys or if an error did occur
	if ($sql_error == 1){
		return false;
	
		//echo "<div class=\"red\">".IM_ERROR.".</div>\n";
	} else {
		return true;
		//echo "<div class=\"green\">".IM_SUCCESS." ".count($table_queries)." ".IM_TABLES." ".count($data_queries)." ".IM_ROWS." (".$import_file.")</div>\n";
	//$shell_command = C_MYSQL_PATH . " -h $host -u $user -p{$password} $dbname < $filename";
	//shell_exec($shell_command);
	}
}

function InstallSplitSql($sql, $last_update) {
 global $lastDBUpdate;

 $buffer = array();
 $ret = array();

 $sql = trim($sql);

 $matched =  preg_match_all('/\n#\s*(\d{8})\b/', $sql, $matches);
 if ($matched) {
	 // Used for updating from previous versions, even if the update
	 // is not correctly set.
	 $len = count($matches[0]);
   $lastDBUpdate = $matches[1][$len-1];
 }
 
 if ($last_update && $last_update != '00000000') {
  // Find the first occurrance of an update that is
  // greater than the last_update number.
  dPmsg("Checking for previous updates");
  if ($matched) {
   for ($i = 0; $i < $len; $i++) {
    if ((int)$last_update < (int)$matches[1][$i]) {
     // Remove the SQL up to the point found
     $match = '/^.*' . trim($matches[0][$i]) . '/Us';
     $sql = preg_replace($match, "", $sql);
     break;
    }
   }
   // If we run out of indicators, we need to debunk, otherwise we will reinstall
   if ($i == $len)
    return $ret;
  }
 }
 $sql = ereg_replace("\n#[^\n]*\n", "\n", $sql);

 $in_string = false;

 for($i=0; $i<strlen($sql)-1; $i++) {
  if($sql[$i] == ";" && !$in_string) {
   $ret[] = substr($sql, 0, $i);
   $sql = substr($sql, $i + 1);
   $i = 0;
  }

  if($in_string && ($sql[$i] == $in_string) && $buffer[1] != "\\") {
   $in_string = false;
  }
  elseif(!$in_string && ($sql[$i] == '"' || $sql[$i] == "'") && (!isset($buffer[0]) || $buffer[0] != "\\")) {
   $in_string = $sql[$i];
  }
  if(isset($buffer[1])) {
   $buffer[0] = $buffer[1];
  }
  $buffer[1] = $sql[$i];
 }

 if(!empty($sql)) {
  $ret[] = $sql;
 }
 return($ret);
}

function InstallLoadSQL($sqlfile, $last_update = null) {
 global $dbErr, $dbMsg, $db;

 // Don't complain about missing files.
 if (! file_exists($sqlfile)){
	return;
 }
 $mqr = @get_magic_quotes_runtime();
 @set_magic_quotes_runtime(0);

 $pieces = array();
 if ($sqlfile) {
  $query = fread(fopen($sqlfile, "r"), filesize($sqlfile));
  $pieces  = InstallSplitSql($query, $last_update);
 }

 @set_magic_quotes_runtime($mqr);
 $errors = 0;
 $piece_count = count($pieces);

 for ($i=0; $i<$piece_count; $i++) {
  $pieces[$i] = trim($pieces[$i]);
  if(!empty($pieces[$i]) && $pieces[$i] != "#") {
  //echo $pieces[$i];
   if (!$result = mysql_query($pieces[$i])) {
    $errors++;
    $dbErr = true;
    $sql_error = true;
   }
  }
 }
 //prnMsg("There were $errors errors in $piece_count SQL statements");
 $sql_error = true;
}

?>
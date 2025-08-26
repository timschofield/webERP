<?php

/* mysql specific functions for the database upgrade script */

function CharacterSet($Table) {
	$SQL = "SELECT TABLE_COLLATION
		FROM information_schema.tables
		WHERE TABLE_SCHEMA='" . $_SESSION['DatabaseName'] . "'
			AND TABLE_NAME='" . $Table . "'";
	$Result = DB_query($SQL);
	$MyRow = DB_fetch_array($Result);
	return $MyRow['TABLE_COLLATION'];
}

function CreateTrigger($Table, $TriggerName, $Event, $Row, $EventSql) {
	$SQL = "SELECT TRIGGER_NAME
		FROM information_schema.triggers
		WHERE TRIGGER_NAME='" . $TriggerName . "'
		AND TRIGGER_SCHEMA='" . $_SESSION['DatabaseName'] . "'";
	$Result = DB_query($SQL);
	if (DB_num_rows($Result) == 0) {
		$SQL = "CREATE TRIGGER " . $TriggerName . " " . $Event . " ON " . $Table . " FOR EACH ROW SET " . $Row . "." . $EventSql;
		$Response = executeSQL($SQL, false);
		if ($Response == 0) {
			OutputResult(__('The trigger') . ' ' . $TriggerName . ' ' . __('has been created'), 'success');
		} else {
			OutputResult(__('The trigger') . ' ' . $TriggerName . ' ' . __('could not be created') . '<br />' . $SQL, 'error');
		}
	} else {
		OutputResult(__('The trigger') . ' ' . $TriggerName . ' ' . __('already exists'), 'info');
	}
}

function NewSecurityToken($TokenId, $TokenName) {
	$SQL = "SELECT tokenid FROM securitytokens WHERE tokenid='" . $TokenId . "'";
	$Result = DB_query($SQL);
	if (DB_num_rows($Result) == 0) {
		$SQL = "INSERT INTO securitytokens (tokenid,
											tokenname
										) VALUES (
											'" . $TokenId . "',
											'" . $TokenName . "'
										)";
		$Response = executeSQL($SQL, false);
		if ($Response == 0) {
			OutputResult(__('The security token') . ' ' . $TokenId . ' ' . __('has been created'), 'success');
		} else {
			OutputResult(__('The security token') . ' ' . $TokenId . ' ' . __('could not be created') . '<br />' . $SQL, 'error');
		}
	} else {
		OutputResult(__('The security token') . ' ' . $TokenId . ' ' . __('already exists'), 'info');
	}
}

function NewSysType($TypeID, $TypeDescription) {
	/* Does type already exist */
	$SQL = "SELECT typeid FROM systypes WHERE typeid='" . $TypeID . "'";
	$Result = DB_query($SQL);
	if (DB_num_rows($Result) == 0) {
		$SQL = "INSERT INTO `systypes` (`typeid`, `typename`) VALUES ('" . $TypeID . "', '" . $TypeDescription . "')";
		$Response = executeSQL($SQL, false);
		if ($Response == 0) {
			OutputResult(__('The type') . ' ' . $TypeDescription . ' ' . __('has been inserted'), 'success');
		} else {
			OutputResult(__('The type') . ' ' . $TypeDescription . ' ' . __('could not be inserted') . '<br />' . $SQL, 'error');
		}
	} else {
		OutputResult(__('The script') . ' ' . $TypeDescription . ' ' . __('already exists'), 'info');
	}
}

function NewScript($ScriptName, $PageSecurity) {
	/*Is page already in table */
	$SQL = "SELECT script FROM scripts WHERE script='" . $ScriptName . "'";
	$Result = DB_query($SQL);
	if (DB_num_rows($Result) == 0) {
		$SQL = "INSERT INTO `scripts` (`script`, `pagesecurity`, `description`) VALUES ('" . $ScriptName . "', '" . $PageSecurity . "', '')";
		$Response = executeSQL($SQL, false);
		if ($Response == 0) {
			OutputResult(__('The script') . ' ' . $ScriptName . ' ' . __('has been inserted'), 'success');
		} else {
			OutputResult(__('The script') . ' ' . $ScriptName . ' ' . __('could not be inserted') . '<br />' . $SQL, 'error');
		}
	} else {
		OutputResult(__('The script') . ' ' . $ScriptName . ' ' . __('already exists'), 'info');
	}
}

function RemoveScript($ScriptName) {
	/*Is page already in table */
	$SQL = "SELECT script FROM scripts WHERE script='" . $ScriptName . "'";
	$Result = DB_query($SQL);
	if (DB_num_rows($Result) != 0) {
		$SQL = "DELETE FROM `scripts` WHERE script='" . $ScriptName . "'";
		$Response = executeSQL($SQL, false);
		if ($Response == 0) {
			OutputResult(__('The script') . ' ' . $ScriptName . ' ' . __('has been removed'), 'success');
		} else {
			OutputResult(__('The script') . ' ' . $ScriptName . ' ' . __('could not be removed'), 'error');
		}
	} else {
		OutputResult(__('The script') . ' ' . $ScriptName . ' ' . __('does not exist'), 'info');
	}
}

function NewModule($Link, $Report, $Name, $Sequence) {
	/*Is module already in table */
	$SQL = "SELECT modulelink FROM modules WHERE modulelink='" . $Link . "'";
	$Result = DB_query($SQL);
	if (DB_num_rows($Result) == 0) {
		$SQL = "SELECT secroleid FROM securityroles";
		$Result = DB_query($SQL);
		while ($MyRow = DB_fetch_array($Result)) {
			$SQL = "UPDATE `modules` SET sequence=sequence+1
							WHERE sequence>='" . $Sequence . "'
								AND secroleid='" . $MyRow['secroleid'] . "'";
			$Response = executeSQL($SQL, false);
			$SQL = "INSERT INTO `modules` ( `secroleid`,
											`modulelink`,
											`reportlink`,
											`modulename`,
											`sequence`
										) VALUES (
											'" . $MyRow['secroleid'] . "',
											'" . $Link . "',
											'" . $Report . "',
											'" . $Name . "',
											'" . $Sequence . "'
										)";
			$Response = executeSQL($SQL, false);
			if ($Response == 0) {
				OutputResult(__('The module') . ' ' . $Name . ' ' . __('has been inserted'), 'success');
			} else {
				OutputResult(__('The module') . ' ' . $Name . ' ' . __('could not be inserted') . '<br />' . $SQL, 'error');
			}
		}
	} else {
		OutputResult(__('The module') . ' ' . $Name . ' ' . __('already exists'), 'info');
	}
}

function NewMenuItem($Link, $Section, $Caption, $URL, $Sequence) {
	/*Is module already in table */
	$SQL = "SELECT modulelink FROM menuitems WHERE modulelink='" . $Link . "' AND menusection='" . $Section . "' AND url='" . $URL . "'";
	$Result = DB_query($SQL);
	if (DB_num_rows($Result) == 0) {
		$SQL = "SELECT secroleid FROM securityroles";
		$Result = DB_query($SQL);
		while ($MyRow = DB_fetch_array($Result)) {
			$SQL = "UPDATE `menuitems` SET sequence=sequence+1
							WHERE sequence>='" . $Sequence . "'
								AND secroleid='" . $MyRow['secroleid'] . "'
								AND link='" . $Link . "'
								AND section='" . $Section . "'";
			$Response = executeSQL($SQL, false);
			$SQL = "INSERT INTO `menuitems` (`secroleid`,
												`modulelink`,
												`menusection`,
												`caption`,
												`url`,
												`sequence`
											) VALUES (
												'" . $MyRow['secroleid'] . "',
												'" . $Link . "',
												'" . $Section . "',
												'" . $Caption . "',
												'" . $URL . "',
												'" . $Sequence . "'
											)";
			$Response = executeSQL($SQL, false);
			if ($Response == 0) {
				OutputResult(__('The menu link') . ' ' . $Caption . ' ' . __('has been inserted'), 'success');
			} else {
				OutputResult(__('The menu link') . ' ' . $Caption . ' ' . __('could not be inserted') . '<br />' . $SQL, 'error');
			}
		}
	} else {
		OutputResult(__('The menu link') . ' ' . $Caption . ' ' . __('already exists'), 'info');
	}
}

function RemoveMenuItem($Link, $Section, $Caption, $URL) {
	$SQL = "SELECT modulelink FROM menuitems WHERE modulelink='" . $Link . "' AND menusection='" . $Section . "' AND url='" . $URL . "'";
	$Result = DB_query($SQL);
	if (DB_num_rows($Result) != 0) {
		$SQL = "SELECT secroleid FROM securityroles";
		$Result = DB_query($SQL);
		while ($MyRow = DB_fetch_array($Result)) {
			$SQL = "DELETE FROM menuitems WHERE modulelink='" . $Link . "'
											AND menusection='" . $Section . "'
											AND caption='" . $Caption . "'
											AND url='" . $URL . "'";
			$Response = executeSQL($SQL, false);
			if ($Response == 0) {
				OutputResult(__('The menu link') . ' ' . $Caption . ' ' . __('has been deleted'), 'success');
			} else {
				OutputResult(__('The menu link') . ' ' . $Caption . ' ' . __('could not be deleted') . '<br />' . $SQL, 'error');
			}
		}
	} else {
		OutputResult(__('The menu link') . ' ' . $Caption . ' ' . __('does not exist'), 'info');
	}
}

function AddColumn($Column, $Table, $Type, $Null, $Default, $After) {
	global $SQLFile;
	if (DB_table_exists($Table)) {
		$SQL = "desc " . $Table . " " . $Column;
		$Result = DB_query($SQL);
		if (isset($SQLFile) or DB_num_rows($Result) == 0) {
			if ($Type == 'text') {
				$SQL = "ALTER TABLE `" . $Table . "` ADD COLUMN `" . $Column . "` " . $Type . " " . $Null . " AFTER `" . $After . "`";
			} else {
				if (isset($Default) and $Default != '') {
					if ($Default == 'CURRENT_TIMESTAMP') {
						$SQL = "ALTER TABLE `" . $Table . "` ADD COLUMN `" . $Column . "` " . $Type . " " . $Null . " DEFAULT " . $Default . " AFTER `" . $After . "`";
					} else {
						$SQL = "ALTER TABLE `" . $Table . "` ADD COLUMN `" . $Column . "` " . $Type . " " . $Null . " DEFAULT '" . $Default . "' AFTER `" . $After . "`";
					}
				} else {
					$SQL = "ALTER TABLE `" . $Table . "` ADD COLUMN `" . $Column . "` " . $Type . " " . $Null . " AFTER `" . $After . "`";
				}
			}
			$Response = executeSQL($SQL);
			if ($Response == 0) {
				OutputResult(__('The column') . ' ' . $Column . ' ' . __('has been inserted'), 'success');
			} else {
				OutputResult(__('The column') . ' ' . $Column . ' ' . __('could not be inserted') . '<br />' . $SQL, 'error');
			}
		} else {
			OutputResult(__('The column') . ' ' . $Column . ' ' . __('already exists'), 'info');
		}
	}
}

function AddIndex($Columns, $Table, $Name) {
	if (DB_table_exists($Table)) {
		$SQL = "SHOW INDEX FROM " . $Table . " WHERE Key_name='" . $Name . "'";
		$Result = DB_query($SQL);
		if (DB_num_rows($Result) == 0) {
			$SQL = "ALTER TABLE `" . $Table . "` ADD INDEX `" . $Name . "` (`" . $Columns[0] . "`";
			$SizeOfColumns = sizeOf($Columns);
			for ($i = 1;$i < $SizeOfColumns;$i++) {
				$SQL.= "," . $Columns[$i];
			}
			$SQL.= ")";
			$Response = executeSQL($SQL, false);
			if ($Response == 0) {
				OutputResult(__('The index has been inserted'), 'success');
			} else {
				OutputResult(__('The index could not be inserted') . '<br />' . $SQL, 'error');
			}
		} else {
			OutputResult(__('The index already exists'), 'info');
		}
	}
}

function DropIndex($Table, $Name) {
	if (DB_table_exists($Table)) {
		$SQL = "SHOW INDEX FROM " . $Table . " WHERE Key_name='" . $Name . "'";
		$Result = DB_query($SQL);
		if (DB_num_rows($Result) != 0) {
			$SQL = "ALTER TABLE `" . $Table . "` DROP INDEX " . $Name;
			$Response = executeSQL($SQL, false);
			if ($Response == 0) {
				OutputResult(__('The index has been droppeed'), 'success');
			} else {
				OutputResult(__('The index could not be dropped'), 'error');
			}
		} else {
			OutputResult(__('The index does not exist'), 'info');
		}
	}
}

function DropColumn($Column, $Table) {
	global $SQLFile;
	if (DB_table_exists($Table)) {
		$SQL = "desc " . $Table . " " . $Column;
		$Result = DB_query($SQL);
		if (isset($SQLFile) or DB_num_rows($Result) != 0) {
			$Response = executeSQL("ALTER TABLE `" . $Table . "` DROP `" . $Column, false);
			if ($Response == 0) {
				OutputResult(__('The column') . ' ' . $Column . ' ' . __('has been removed'), 'success');
			} else {
				OutputResult(__('The column') . ' ' . $Column . ' ' . __('could not be removed'), 'error');
			}
		} else {
			OutputResult(__('The column') . ' ' . $Column . ' ' . __('is already removed'), 'info');
		}
	}
}

function ChangeColumnSize($Column, $Table, $Type, $Null, $Default, $Size) {
	$SQL = "SELECT CHARACTER_MAXIMUM_LENGTH
		FROM information_schema.columns
		WHERE TABLE_SCHEMA='" . $_SESSION['DatabaseName'] . "'
			AND TABLE_NAME='" . $Table . "'
			AND COLUMN_NAME='" . $Column . "'";
	$Result = DB_query($SQL);
	$MyRow = DB_fetch_row($Result);
	if ($MyRow[0] <> $Size) {
		$Response = executeSQL("ALTER TABLE " . $Table . " CHANGE COLUMN " . $Column . " " . $Column . " " . $Type . " " . $Null . " DEFAULT '" . $Default . "'", false);
		if ($Response == 0) {
			OutputResult(__('The column') . ' ' . $Column . ' ' . __('has been changed'), 'success');
		} else {
			OutputResult(__('The column') . ' ' . $Column . ' ' . __('could not be changed'), 'error');
		}
	} else {
		OutputResult(__('The column') . ' ' . $Column . ' ' . __('is already changed'), 'info');
	}
}

function ChangeColumnName($OldName, $Table, $Type, $Null, $Default, $NewName, $AutoIncrement = '') {
	$OldSQL = "SELECT CHARACTER_MAXIMUM_LENGTH
		FROM information_schema.columns
		WHERE TABLE_SCHEMA='" . $_SESSION['DatabaseName'] . "'
			AND TABLE_NAME='" . $Table . "'
			AND COLUMN_NAME='" . $OldName . "'";
	$OldResult = DB_query($OldSQL);
	$NewSQL = "SELECT CHARACTER_MAXIMUM_LENGTH
		FROM information_schema.columns
		WHERE TABLE_SCHEMA='" . $_SESSION['DatabaseName'] . "'
			AND TABLE_NAME='" . $Table . "'
			AND COLUMN_NAME='" . $NewName . "'";
	$NewResult = DB_query($NewSQL);
	if (DB_num_rows($OldResult) > 0 and DB_num_rows($NewResult) == 0) {
		if (strtoupper($Type) == 'TIMESTAMP') {
			$Default = "DEFAULT CURRENT_TIMESTAMP";
		}else{
			$Default = "DEFAULT '" . $Default . "'";
		}
		if ($AutoIncrement == '') {
			$Response = executeSQL("ALTER TABLE " . $Table . " CHANGE COLUMN " . $OldName . " " . $NewName . " " . $Type . " " . $Null . " " . $Default, false);
		} else {
			$Response = executeSQL("ALTER TABLE " . $Table . " CHANGE COLUMN " . $OldName . " " . $NewName . " " . $Type . " " . $Null . " " . $AutoIncrement, false);
		}
		if ($Response == 0) {
			OutputResult(__('The column') . ' ' . $OldName . ' ' . __('has been renamed') . ' ' . $NewName, 'success');
		} else {
			OutputResult(__('The column') . ' ' . $OldName . ' ' . __('could not be renamed'), 'error');
		}
	} else {
		OutputResult(__('The column') . ' ' . $OldName . ' ' . __('is already changed'), 'info');
	}
}

function ChangeColumnType($Column, $Table, $Type, $Null, $Default) {
	$SQL = "SELECT DATA_TYPE
		FROM information_schema.columns
		WHERE TABLE_SCHEMA='" . $_SESSION['DatabaseName'] . "'
			AND TABLE_NAME='" . $Table . "'
			AND COLUMN_NAME='" . $Column . "'";
	$Result = DB_query($SQL);
	$MyRow = DB_fetch_row($Result);
	if ($MyRow[0] <> $Type) {
		if ($Default == '') {
			$SQL = "ALTER TABLE " . $Table . " CHANGE COLUMN " . $Column . " " . $Column . " " . $Type . " " . $Null;
			$Response = executeSQL($SQL, false);
		} else {
			$SQL = "ALTER TABLE " . $Table . " CHANGE COLUMN " . $Column . " " . $Column . " " . $Type . " " . $Null . " DEFAULT '" . $Default . "'";
			$Response = executeSQL($SQL, false);
		}
		if ($Response == 0) {
			OutputResult(__('The column') . ' ' . $Column . ' ' . __('has been changed'), 'success');
		} else {
			OutputResult(__('The column') . ' ' . $Column . ' ' . __('in the table') . ' ' . $Table . ' ' . __('could not be changed to type') . ' ' . $Type . ' ' . __('and returned error number') . ' ' . $Response . '<br />' . $SQL, 'error');
		}
	} else {
		OutputResult(__('The column') . ' ' . $Column . ' ' . __('is already changed'), 'info');
	}
}

function ChangeColumnDefault($Column, $Table, $Type, $Null, $Default) {
	$SQL = "SELECT COLUMN_DEFAULT
		FROM information_schema.columns
		WHERE TABLE_SCHEMA='" . $_SESSION['DatabaseName'] . "'
			AND TABLE_NAME='" . $Table . "'
			AND COLUMN_NAME='" . $Column . "'";
	$Result = DB_query($SQL);
	$MyRow = DB_fetch_row($Result);
	if ($MyRow[0] <> $Default) {
		$Response = executeSQL("ALTER TABLE " . $Table . " CHANGE COLUMN " . $Column . " " . $Column . " " . $Type . " " . $Null . " DEFAULT '" . $Default . "'", false);
		if ($Response == 0) {
			OutputResult(__('The column') . ' ' . $Column . ' ' . __('has been changed'), 'success');
		} else {
			OutputResult(__('The column') . ' ' . $Column . ' ' . __('could not be changed'), 'error');
		}
	} else {
		OutputResult(__('The column') . ' ' . $Column . ' ' . __('is already changed'), 'info');
	}
}

function RemoveAutoIncrement($Column, $Table, $Type, $Null, $Default) {
	$SQL = "SELECT COLUMN_DEFAULT
		FROM information_schema.columns
		WHERE TABLE_SCHEMA='" . $_SESSION['DatabaseName'] . "'
			AND TABLE_NAME='" . $Table . "'
			AND COLUMN_NAME='" . $Column . "'";
	$Result = DB_query($SQL);
	$MyRow = DB_fetch_row($Result);
	if ($MyRow[0] <> $Default) {
		$Response = executeSQL("ALTER TABLE " . $Table . " CHANGE COLUMN " . $Column . " " . $Column . " " . $Type . " " . $Null . " DEFAULT '" . $Default . "'", false);
		if ($Response == 0) {
			OutputResult(__('The column') . ' ' . $Column . ' ' . __('has been changed'), 'success');
		} else {
			OutputResult(__('The column') . ' ' . $Column . ' ' . __('could not be changed'), 'error');
		}
	} else {
		OutputResult(__('The column') . ' ' . $Column . ' ' . __('is already changed'), 'info');
	}
}

function NewConfigValue($ConfName, $ConfValue) {
	$SQL = "SELECT confvalue
		FROM config
		WHERE confname='" . $ConfName . "'";
	$Result = DB_query($SQL);
	$MyRow = DB_fetch_row($Result);
	if (DB_num_rows($Result) == 0) {
		$Response = executeSQL("INSERT INTO `config` (`confname`, `confvalue`) VALUES ('" . $ConfName . "', '" . $ConfValue . "')", false);
		if ($Response == 0) {
			OutputResult(__('The config value') . ' ' . $ConfName . ' ' . __('has been inserted'), 'success');
		} else {
			OutputResult(__('The config value') . ' ' . $ConfName . ' ' . __('could not be inserted'), 'error');
		}
	} else {
		OutputResult(__('The config value') . ' ' . $ConfName . ' ' . __('is in'), 'info');
	}
}

function ChangeConfigValue($ConfName, $NewConfigValue) {
	$SQL = "SELECT confvalue
		FROM config
		WHERE confname='" . $ConfName . "'
			AND confvalue='" . $NewConfigValue . "'";
	$Result = DB_query($SQL);
	$MyRow = DB_fetch_row($Result);
	if (DB_num_rows($Result) == 0) {
		$Response = executeSQL("UPDATE `config` SET `confvalue`='" . $NewConfigValue . "' WHERE `confname`='" . $ConfName . "'", false);
		if ($Response == 0) {
			OutputResult(__('The config value') . ' ' . $ConfName . ' ' . __('has been updated'), 'success');
		} else {
			OutputResult(__('The config value') . ' ' . $ConfName . ' ' . __('could not be updated'), 'error');
		}
	} else {
		OutputResult(__('The config value') . ' ' . $ConfName . ' ' . __('is already set to') . ' ' . $NewConfigValue, 'info');
	}
}

function ChangeConfigName($OldConfName, $NewConfName) {
	$SQL = "SELECT confvalue
		FROM config
		WHERE confname='" . $NewConfName . "'";
	$Result = DB_query($SQL);
	$MyRow = DB_fetch_row($Result);
	if (DB_num_rows($Result) == 0) {
		$Response = executeSQL("UPDATE `config` SET `confname`='" . $NewConfName . "' WHERE `confname`='" . $OldConfName . "'", false);
		if ($Response == 0) {
			OutputResult(__('The config value') . ' ' . $OldConfName . ' ' . __('has been updated'), 'success');
		} else {
			OutputResult(__('The config value') . ' ' . $OldConfName . ' ' . __('could not be updated'), 'error');
		}
	} else {
		OutputResult(__('The config value') . ' ' . $OldConfName . ' ' . __('is already changed to') . ' ' . $NewConfName, 'info');
	}
}

function DeleteConfigValue($ConfName) {
	$SQL = "SELECT confvalue
		FROM config
		WHERE confname='" . $ConfName . "'";
	$Result = DB_query($SQL);
	$MyRow = DB_fetch_row($Result);
	if (DB_num_rows($Result) == 0) {
		$Response = executeSQL("DELETE FROM `config` WHERE `confname`='" . $ConfName . "'", false);
		if ($Response == 0) {
			OutputResult(__('The config value') . ' ' . $ConfName . ' ' . __('has been removed'), 'success');
		} else {
			OutputResult(__('The config value') . ' ' . $ConfName . ' ' . __('could not be removed'), 'error');
		}
	} else {
		OutputResult(__('The config value') . ' ' . $ConfName . ' ' . __('is already removed'), 'info');
	}
}

function CreateTable($Table, $SQL) {
	$ShowSQL = "SHOW TABLES WHERE Tables_in_" . $_SESSION['DatabaseName'] . "='" . $Table . "'";
	$Result = DB_query($ShowSQL);

	if (DB_num_rows($Result) == 0) {
		DB_IgnoreForeignKeys();
		$Response = executeSQL($SQL . ' ENGINE=InnoDB DEFAULT CHARSET=utf8', false);
		DB_ReinstateForeignKeys();
		if ($Response == 0) {
			OutputResult(__('The table') . ' ' . $Table . ' ' . __('has been created'), 'success');
		} else {
			OutputResult(__('The table') . ' ' . $Table . ' ' . __('could not be created'), 'error');
		}
	} else {
		OutputResult(__('The table') . ' ' . $Table . ' ' . __('already exists'), 'info');
	}
}

function ConstraintExists($Table, $Constraint) {
	$SQL = "SELECT CONSTRAINT_NAME
		FROM information_schema.TABLE_CONSTRAINTS
		WHERE TABLE_SCHEMA='" . $_SESSION['DatabaseName'] . "'
			AND TABLE_NAME='" . $Table . "'
			AND CONSTRAINT_NAME='" . $Constraint . "'";
	$Result = DB_query($SQL);
	if (DB_num_rows($Result) == 0) {
		return false;
	} else {
		return true;
	}
}

function DropConstraint($Table, $Constraint) {
	if (ConstraintExists($Table, $Constraint)) {
		$Response = executeSQL("ALTER TABLE `" . $Table . "` DROP FOREIGN KEY `" . $Constraint . "`", false);
		if ($Response == 0) {
			OutputResult(__('The constraint') . ' ' . $Constraint . ' ' . __('has been removed'), 'success');
		} else {
			OutputResult(__('The constraint') . ' ' . $Constraint . ' ' . __('could not be removed'), 'error');
		}
	} else {
		OutputResult(__('The constraint') . ' ' . $Constraint . ' ' . __('does not exist'), 'info');
	}
}

function AddConstraint($Table, $Constraint, $Field, $ReferenceTable, $ReferenceField) {
	if (!ConstraintExists($Table, $Constraint)) {
		if (gettype($Field) == 'array') {
			$List = implode(',', $Field);
			$Field = $List;
		}
		if (gettype($ReferenceField) == 'array') {
			$List = implode(',', $ReferenceField);
			$ReferenceField = $List;
		}
		$SQL = "ALTER TABLE " . $Table . " ADD CONSTRAINT " . $Constraint . " FOREIGN KEY (" . $Field . ") REFERENCES " . $ReferenceTable . " (" . $ReferenceField . ")";
		$Response = executeSQL($SQL, false);
		if ($Response == 0) {
			OutputResult(__('The constraint') . ' ' . $Constraint . ' ' . __('has been added'), 'success');
		} else {
			OutputResult(__('The constraint') . ' ' . $Constraint . ' ' . __('could not be added') . '<br />' . $SQL, 'error');
		}
	} else {
		OutputResult(__('The constraint') . ' ' . $Constraint . ' ' . __('already exists'), 'info');
	}
}

function UpdateField($Table, $Field, $NewValue, $Criteria) {
	global $SQLFile;
	if (DB_table_exists($Table)) {
		$SQL = "desc " . $Table . " " . $Field;
		$Result = DB_query($SQL);
		$MyRow = DB_fetch_row($Result);
	} else {
		$MyRow[0] = 0;
	}
	if ($MyRow[0] != 0 or DB_num_rows($Result) > 0) {
		$SQL = "SELECT " . $Field . " FROM " . $Table . " WHERE " . $Criteria;
		$Result = DB_query($SQL);
		$MyRow = DB_fetch_row($Result);
		if ($MyRow[0] != $NewValue) {
			$SQL = "UPDATE " . $Table . " SET " . $Field . "='" . $NewValue . "' WHERE " . $Criteria;
			$Response = executeSQL($SQL, false);
			if ($Response == 0) {
				OutputResult(__('The field') . ' ' . $Field . ' ' . __('has been updated'), 'success');
			} else {
				OutputResult(__('The field') . ' ' . $Field . ' ' . __('could not be updated') . '<br />' . $SQL, 'error');
			}
		} else {
			OutputResult(__('The field') . ' ' . $Field . ' ' . __('is already correct'), 'info');
		}
	} else if (isset($SQLFile)) {
		$Response = executeSQL("UPDATE " . $Table . " SET " . $Field . "='" . $NewValue . "' WHERE " . $Criteria, false);
	}
}

function DeleteRecords($Table, $Criteria) {
	$SQL = "SELECT * FROM " . $Table . " WHERE " . $Criteria;
	$Result = DB_query($SQL);
	if (DB_num_rows($Result) > 0) {
		$Response = executeSQL("DELETE FROM " . $Table . " WHERE " . $Criteria, false);
		if ($Response == 0) {
			OutputResult(__('Rows have been deleted from') . ' ' . $Table, 'success');
		} else {
			OutputResult(__('Rows could not be deleted from') . ' ' . $Table, 'error');
		}
	} else {
		OutputResult(__('There was nothing to delete from') . ' ' . $Table, 'info');
	}
}

function DropTable($Table) {
	$SQL = "SHOW tables WHERE Tables_in_" . $_SESSION['DatabaseName'] . " ='" . $Table . "'";
	$Result = DB_query($SQL);
	if (DB_num_rows($Result) > 0) {
		$Response = executeSQL("DROP TABLE IF EXISTS `" . $Table . "`");
		if ($Response == 0) {
			OutputResult(__('The old table') . ' ' . $Table . ' ' . __('has been removed'), 'success');
		} else {
			OutputResult(__('The old table') . ' ' . $Table . ' ' . __('could not be removed'), 'error');
		}
	} else {
		OutputResult(__('The old table') . ' ' . $Table . ' ' . __('has already been removed'), 'info');
	}
}

function InsertRecord($Table, $CheckFields, $CheckValues, $Fields, $Values) {
	if (DB_table_exists($Table)) {
		$SQL = "SELECT * FROM " . $Table . " WHERE ";
		$SizeOfCheckFields = sizeOf($CheckFields);
		for ($i = 0;$i < $SizeOfCheckFields;$i++) {
			$SQL = $SQL . $CheckFields[$i] . "='" . DB_escape_string($CheckValues[$i]) . "' AND ";
		}
		$SQL = mb_substr($SQL, 0, mb_strlen($SQL) - 5);
		$Result = DB_query($SQL);
	}
	if (DB_num_rows($Result) == 0 or isset($SQLFile)) {
		$SQL = "INSERT INTO " . $Table . " (";
		$SizeOfFields = sizeOf($Fields);
		for ($i = 0;$i < $SizeOfFields;$i++) {
			$SQL = $SQL . $Fields[$i] . ",";
		}
		$SQL = mb_substr($SQL, 0, mb_strlen($SQL) - 1) . ") VALUES (";
		$SizeOfValues = sizeOf($Values);
		for ($i = 0;$i < $SizeOfValues;$i++) {
			$SQL = $SQL . "'" . DB_escape_string($Values[$i]) . "',";
		}
		$SQL = mb_substr($SQL, 0, mb_strlen($SQL) - 1) . ")";
		$Response = executeSQL($SQL);
		if ($Response == 0) {
			OutputResult(__('The record has been inserted'), 'success');
		} else {
			OutputResult(__('The record could not be inserted') . ' ' . __('The sql used was') . '<br />' . $SQL, 'error');
		}
	} else {
		OutputResult(__('The record is already in the table'), 'info');
	}
}

function DropPrimaryKey($Table, $OldKey) {
	$SQL = "SELECT COLUMN_NAME, TABLE_NAME FROM information_schema.key_column_usage WHERE table_name='" . $Table . "' AND constraint_name='primary' AND table_schema='" . $_SESSION['DatabaseName'] . "'";
	$Result = DB_query($SQL);
	$Total = DB_num_rows($Result);
	$Fields = array();
	while ($MyRow = DB_fetch_array($Result)) {
		$Fields[] = $MyRow['COLUMN_NAME'];
	}
	if ($Total == sizeOf($OldKey) and $Fields == $OldKey) {
		$SQL = "ALTER TABLE `" . $Table . "` DROP PRIMARY KEY";
		$Response = executeSQL($SQL);
		if ($Response == 0) {
			OutputResult(__('The primary key in') . ' ' . $Table . ' ' . __('has been removed'), 'success');
		} else {
			OutputResult(__('The primary key in') . ' ' . $Table . ' ' . __('could not be removed') . '<br />' . $SQL, 'error');
		}
	} else {
		OutputResult(__('The primary key in') . ' ' . $Table . ' ' . __('has already been removed'), 'info');
	}
}

function AddPrimaryKey($Table, $Fields) {
	$SQL = "SELECT table_name FROM information_schema.key_column_usage WHERE table_name='" . $Table . "' AND constraint_name='primary' AND
		table_schema='" . $_SESSION['DatabaseName'] . "'";
	$Result = DB_query($SQL);
	if (DB_num_rows($Result) != sizeOf($Fields)) {
		$KeyString = implode(",", $Fields);
		$Response = executeSQL("ALTER TABLE `" . $Table . "` ADD PRIMARY KEY ( " . $KeyString . " )");
		if ($Response == 0) {
			OutputResult(__('The primary key in') . ' ' . $Table . ' ' . __('has been added'), 'success');
		} else {
			OutputResult(__('The primary key in') . ' ' . $Table . ' ' . __('could not be added') . '<br />' . "ALTER TABLE " . $Table . " ADD PRIMARY KEY ( " . $KeyString . " )", 'error');
		}
	} else {
		OutputResult(__('The primary key in') . ' ' . $Table . ' ' . __('has already been added'), 'info');
	}
}

function RenameTable($OldName, $NewName) {
	$Newsql = "SHOW TABLES WHERE Tables_in_" . $_SESSION['DatabaseName'] . "='" . $NewName . "'";
	$Newresult = DB_query($Newsql);
	$Oldsql = "SHOW TABLES WHERE Tables_in_" . $_SESSION['DatabaseName'] . "='" . $OldName . "'";
	$Oldresult = DB_query($Oldsql);

	if (DB_num_rows($Newresult) != 0 and DB_num_rows($Oldresult) != 0) {
		$Response = executeSQL("DROP TABLE " . $OldName . "", false);
	}
	if (DB_num_rows($Newresult) == 0) {
		$SQL = "RENAME TABLE " . $OldName . " to " . $NewName;
		$Response = executeSQL($SQL, false);
		if ($Response == 0) {
			OutputResult(__('The table') . ' ' . $OldName . ' ' . __('has been renamed to') . ' ' . $NewName, 'success');
		} else {
			OutputResult(__('The table') . ' ' . $OldName . ' ' . __('could not be renamed to') . ' ' . $NewName . '<br />' . $SQL, 'error');
		}
	} else {
		OutputResult(__('The table') . ' ' . $NewName . ' ' . __('already exists'), 'info');
	}
}

function SetAutoIncStart($Table, $Field, $StartNumber) {
	$GetLargestSQL = "SELECT MAX(" . $Field . ") AS highest FROM " . $Table;
	$GetLargestResult = DB_query($GetLargestSQL);
	$LargestRow = DB_fetch_array($GetLargestResult);
	if ($LargestRow['highest'] > $StartNumber) {
		OutputResult(__('You are trying to set the auto increment number below the current number'), 'warn');
	} else {
		$Response = executeSQL("ALTER TABLE " . $Table . " AUTO_INCREMENT = " . $StartNumber, false);
		OutputResult(__('The auto increment field in table') . ' ' . $Table . __('has been updated'), 'success');
	}
}

function OutputResult($Msg, $Status) {
	if ($Status == 'error') {
		$_SESSION['Updates']['Errors']++;
		$_SESSION['Updates']['Messages'][] = $Msg;
	} else if ($Status == 'success') {
		$_SESSION['Updates']['Successes']++;
	} else {
		$_SESSION['Updates']['Warnings']++;
	}
}

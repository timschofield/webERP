<?php

if (!isset($PathPrefix)) {
	header('Location: ../');
	exit();
}

$Result = '';

if (isset($_POST['test'])) {
	$_SESSION['Installer']['Port'] = $_POST['Port'];
	$_SESSION['Installer']['HostName'] = $_POST['HostName'];
	$_SESSION['Installer']['Database'] = $_POST['Database'];
	$_SESSION['Installer']['UserName'] = $_POST['UserName'];
	$_SESSION['Installer']['Password'] = $_POST['Password'];
	$_SESSION['Installer']['DBMS'] = $_POST['dbms'];
	try {
		$conn = mysqli_connect($_SESSION['Installer']['HostName'], $_SESSION['Installer']['UserName'],
			$_SESSION['Installer']['Password'], 'information_schema', $_SESSION['Installer']['Port']);
		$Result = 'valid';

		/// @todo check out if db type is mysql/mariadb by querying the server. If a wrong type is found, fix the setting
		///       and notify the user with a warning

		$Message = __('Database connection working');
	}
	catch(Exception $e) {
		$Result = 'invalid';
		$Message = $e->getMessage();
	}

	// gg: dead code?
	//if (mysqli_connect_error()) {
	//	$DBConnectionError = true;
	//}
}

echo '<form method="post" action="' . htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') . '?Page=3">
		<fieldset>
			<legend>' . __('Database settings') . '</legend>
			<div class="page_help_text">
				<p>' . __('Please enter your database information below.') . '<br />
				</p>
			</div>
			<ul>
				<field>
					<label for="dbms">' . __('DBMS Driver') . ': </label>
					<select name="dbms">';

if ($_SESSION['Installer']['DBMS'] == 'mysqli') {
	echo '<option value="mysqli" selected="selected">MYSQLI</option>';
} else {
	echo '<option value="mysqli">MYSQLI</option>';
}
if ($_SESSION['Installer']['DBMS'] == 'mariadb') {
	echo '<option value="mariadb" selected="selected">MariaDB</option>';
} else {
	echo '<option value="mariadb">MariaDB</option>';
}

echo '</select>
					<fieldhelp>' . __('Select the Database Management System you are using') . '</fieldhelp>
				</field>
				<field>
					<label for="HostName">' . __('Host Name') . ': </label>
					<input type="text" name="HostName" id="HostName" required="required" value="' . $_SESSION['Installer']['HostName'] . '" placeholder="' . __('Enter database host name') . '" />
					<fieldhelp>' . __('Commonly: localhost or 127.0.0.1') . '</fieldhelp>
				</field>
				<field>
					<label for="Port">' . __('Database Port') . ': </label>
					<input type="text" name="Port" id="Port" required="required" value="' . $_SESSION['Installer']['Port'] . '" maxlength="16" placeholder="' . __('The database port') . '" />
					<fieldhelp>' . __('The port to use to connect to the database.') . '</fieldhelp>
				</field>
				<field>
					<label for="Database">' . __('Database Name') . ': </label>
					<input type="text" name="Database" id="Database" required="required" value="' . $_SESSION['Installer']['Database'] . '" maxlength="32" placeholder="' . __('The database name') . '" />
					<fieldhelp>' . __('If your user name below does not have permissions to create a database then this database must be created and empty.') . '</fieldhelp>
				</field>
				<!-- DB prefix is not handled at all atm...
				<field>
					<label for="Prefix">' . __('Database Prefix') . ' - ' . __('Optional') . ': </label>
					<input type="text" name="Prefix" size="25" placeholder="' . __('Useful with shared hosting') . '" pattern="^[A-Za-z0-9$]+_$" />&#160;
					<fieldhelp>' . __('Optional: in the form of prefix_') . '</fieldhelp>
				</field>
				-->
				<field>
					<label for="UserName">' . __('Database User Name') . ':</label>
					<input type="text" name="UserName" id="UserName" value="' . $_SESSION['Installer']['UserName'] . '" placeholder="' . __('A valid database user name') . '" maxlength="32" required="required" />&#160;
					<fieldhelp>' . __('If this user does not have permission to create databases, then the database entered above must exist and be empty.') . '</fieldhelp>
				</field>
				<field>
					<label for="Password">' . __('Password') . ': </label>
					<input type="password" name="Password" placeholder="' . __('Database user password') . '" value="' . $_SESSION['Installer']['Password'] . '" />
					<fieldhelp>' . __('Enter the database user password if one exists') . '</fieldhelp>
				</field>
			</ul>';
if ($Result != '') {
	echo '<input type="submit" id="save" name="test" value="', __('Save details and test the connection'), '" /><img class="result_icon" src="images/', $Result, '.png" />', $Message;
} else {
	echo '<input type="submit" id="save" name="test" value="', __('Save details and test the connection'), '" />';
}
echo '</fieldset>
	</form>';

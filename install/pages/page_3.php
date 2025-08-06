<?php

$Result = '';

if (isset($_POST['test'])) {
	$_SESSION['Installer']['Port'] = $_POST['Port'];
	$_SESSION['Installer']['HostName'] = $_POST['HostName'];
	$_SESSION['Installer']['Database'] = $_POST['Database'];
	$_SESSION['Installer']['UserName'] = $_POST['UserName'];
	$_SESSION['Installer']['Password'] = $_POST['Password'];
	$_SESSION['Installer']['DBMS'] = $_POST['dbms'];
	try {
		/// @todo we lost the PORT!
		$conn = mysqli_connect($_SESSION['Installer']['HostName'], $_SESSION['Installer']['UserName'], $_SESSION['Installer']['Password'], 'information_schema');
		$Result = 'valid';
		$Message = _('Database connection working');
	}
	catch(Exception $e) {
		$Result = 'invalid';
		$Message = $e->getMessage();
	}

	if (mysqli_connect_error()) {
		$DBConnectionError = True;
	}
}

echo '<form method="post" action="index.php?Page=3">
		<fieldset>
			<legend>' . _('Database settings') . '</legend>
			<div class="page_help_text">
				<p>' . _('Please enter your database information below.') . '<br />
				</p>
			</div>
			<ul>
				<field>
					<label for="dbms">' . _('DBMS Driver') . ': </label>
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
		<fieldhelp>' . _('Select the Database Management System you are using') . '</fieldhelp>
				</field>
				<field>
					<label for="HostName">' . _('Host Name') . ': </label>
					<input type="text" name="HostName" id="HostName" required="required" value="' . $_SESSION['Installer']['HostName'] . '" placeholder="' . _('Enter database host name') . '" />
					<fieldhelp>' . _('Commonly: localhost or 127.0.0.1') . '</fieldhelp>
				</field>
				<field>
					<label for="Port">' . _('Database Port') . ': </label>
					<input type="text" name="Port" id="Port" required="required" value="' . $_SESSION['Installer']['Port'] . '" maxlength="16" placeholder="' . _('The database port') . '" />
					<fieldhelp>' . _('The port to use to connect to the databse.') . '</fieldhelp>
				</field>
				<field>
					<label for="Database">' . _('Database Name') . ': </label>
					<input type="text" name="Database" id="Database" required="required" value="' . $_SESSION['Installer']['Database'] . '" maxlength="32" placeholder="' . _('The database name') . '" />
					<fieldhelp>' . _('If your user name below does not have permissions to create a database then this database must be created and empty.') . '</fieldhelp>
				</field>
				<field>
					<label for="Prefix">' . _('Database Prefix') . ' - ' . _('Optional') . ': </label>
					<input type="text" name="Prefix" size="25" placeholder="' . _('Useful with shared hosting') . '" pattern="^[A-Za-z0-9$]+_$" />&#160;
					<fieldhelp>' . _('Optional: in the form of prefix_') . '</fieldhelp>
				</field>
				<field>
					<label for="UserName">' . _('Database User Name') . ':</label>
					<input type="text" name="UserName" id="UserName" value="' . $_SESSION['Installer']['UserName'] . '" placeholder="' . _('A valid database user name') . '" maxlength="32" required="required" />&#160;
					<fieldhelp>' . _('If this user does not have permission to create databases, then the database entered above must exist and be empty.') . '</fieldhelp>
				</field>
				<field>
					<label for="Password">' . _('Password') . ': </label>
					<input type="password" name="Password" placeholder="' . _('Database user password') . '" value="' . $_SESSION['Installer']['Password'] . '" />
					<fieldhelp>' . _('Enter the database user password if one exists') . '</fieldhelp>
				</field>
			</ul>';
if ($Result != '') {
	echo '<input type="submit" id="save" name="test" value="', _('Save details and test the connection'), '" /><img class="result_icon" src="images/', $Result, '.png" />', $Message;
} else {
	echo '<input type="submit" id="save" name="test" value="', _('Save details and test the connection'), '" />';
}
echo '</fieldset>
	</form>';

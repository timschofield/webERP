# The webERP CookBook

Recipes for common tasks

### Creating "web pages" aka. "controller"

Those should reside in the top-level directory, unless you are creating a specific submodule with lots of pages.

Every controller should start with the following line, to bootstrap the application:

	require(__DIR__ . '/includes/session.php');

### Connecting to the database and executing queries

The connection to the DB happens automagically for you from code started within `session.php`.

To execute a query and retrieve data, use code similar to

	$SQL = "SELECT periodno, lastdate_in_period FROM periods ORDER BY periodno DESC";
	$Periods = DB_query($SQL);

	while ($MyRow = DB_fetch_array($Periods)) {
		...
	}

### Escaping data

This is also done automagically within `session.php`. No need to do anything in your controllers to prevent XSS
and SQL injection, except __always putting variables in queries within single quotes in the SQL statement__

	$SQL = "SELECT periodno, lastdate_in_period FROM periods WHERE id='" . $Id . "'";

### User Notifications

The standard message function `prnMsg()` should be used to send notifications to the user. `prnMsg()` has two required
parameters, the string to display and the message type, either 'error', 'warn', 'success' or 'info' (a third optional
parameter is a prefix heading for the message).

### Internationalization

The `__('...')` function is used to translate every user-facing string

### Accessing files

Every files opened for reading/writing should be prefixed by the `$PathPrefix` variable. No use of relative paths

### Outputting links

Links in html are to be prefixed by the `$RootPath` variable.

### Including files

The absolute path should be used for all `include` and `require` call

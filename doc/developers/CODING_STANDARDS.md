# Coding Conventions/Style

It is a core goal of webERP to be easy to read for business people and newcomers to PHP.

The webERP Coding Standards are not only good programming practice to reduce errors, they make the code easier to follow
- which also reduces errors. The webERP Coding Standards are regarded as critically important and all code is to conform.


## PHP

_Simpler is generally better._

Prefer logical, well-structured code that is more easily understood over the easiest or quickest solution at that moment.

### Code Tags

Always use `<?php` to delimit PHP code (not the `<?` shorthand). This is the most portable way to include PHP code on
differing operating systems and setups.

PHP files are not to include a closing tag.

### Functions and Keywords

All PHP keywords must be in lower case. There are _many_ counterexamples to this in the codebase. Do not follow their example!

### Constants

Constants should always be UPPER_CASE using underscores to separate words. Where it is possible to use a literal instead
of a constant, a literal is preferred.

### Formatting

Code shall be formatted using tabs ("tab indented") instead of spaces.

### Spacing

Lines of code should be separated by a blank line if readability is improved (separating groups of lines, or separate
a line from the preceding or following lines).

### Comments

Comments are bread crumbs you are leaving for future you and other developer that explain the basics of the script,
what is the goal of the script and how it is achieved, and to explain in more detail when the script is complex.

Comments need to be kept current. Whenever you touch a script, check for obsolete comments to update, clarify or delete -
as you see fit considering the needs of the community. Long scripts that contain several functions will benefit from -
an overview and list of functions with descriptions, even referencing related scripts. The goal is to help the
future you and other developers to grok a script and its role in webERP as quickly as possible (arguably at the expense
of writing and maintaining comments).

The code will tend to be self commenting with the aid of long variable names and the addition of the context of variables.

Explanations should be provided wherever possible using C style comment blocks in the format:

	/* quick guide to next line or couple lines */

or

	/*
	 * multiple line comment
	 * giving an overview or
	 * explaining a concept
	 */

### Function/Class/Variable/Field Naming

Long, self-explanatory variable names help a lot to decipher what's contained in that variable. Descriptive names
(using PascalCase) are preferred over short variable names: e.g.

	$a = 3.14159;

should be avoided in favour of:

	$Pi = 3.14159;

The variables `$i` `$j` and `$k` can be used as a counters.

As displayed above, there should be one space on either side of an equals sign used to assign the return value of a
function to a variable. In the case of a block of related assignments, more space may be inserted to promote readability:

	$Short = foo($bar);
	$LongVariable = foo($baz);

Good descriptive variable names consisting of several words appended together should have the first letter of each word
capitalised. eg.

	$longvariablename = 1;

should be written as:

	$LongVariableName = 1;

### Label Strings and Multi-Language

Since webERP is a multi-language system it is important not to compromise this capability by having labels in your scripts
that are not enclosed in the gettext function eg.

	echo 'Enter the quantity:<input type="text" name="Quantity" />';

should be written as:

	echo __('Enter the quantity') . ':<input type="text" name="Quantity" />';

note that there should be no trailing spaces on the string to be translated inside the `__()` function call.

NB: the translation function is named `__` (two underscores). this is not a typo.

### Variable Arrays

The PHP variable arrays `$_POST`, `$_GET`, `$_SERVER`, `$_SESSION` provide context about where a variable comes from -
many developers are tempted to abbreviate:

	$StartingCustomer = $_POST['StartingCustomer'];

or worse:

	$s = $_POST['StartingCustomer'];

This should be avoided in favour of using:

	$_POST['StartingCustomer']

everywhere it is required so the reader can see where the variable comes from.

However, variables which could come from either a `$_GET` or a `$_POST` and/or a `$_SESSION` may be assigned as in the
first example since there is no value in the context.

### Quotation Marks

Notice that single quotes (`) are used in preference to double quotes ("). Double quotes should only be used where
absolutely necessary and concatenation of variables is preferred to having variables inside double quotes. eg.

	echo "Some text with a $Variable";

would be better written as:

	echo _('Some text with a') . ' ' . $Variable;

to reduce the parsing job required of the web-server. Notice all strings displayed to users need to be inside the gettext
`__()` function.

Arrays and super global arrays should always have the element name within single quotes not doubles eg.

	$_POST["FormVariableName"]

should be written as:

	$_POST['FormVariableName']

The only exception to this is that when constructing SQL statements, due to the requirement to single quote string
literals in SQL statements, the entire SQL string should preferably be written using double quotes. (See below)

### Control Structures

Where there are many comparisons new lines should be created for each comparison

	if ($OneTwoThree == $_POST['OneTwoThree']
		and $MyRow['onetwothree'] == $_SESSION['OneTwoThree']) {

All control structures (these include `if`, `for`, `while`, `switch`) must always use "1 True Brace" style statement blocks.

You are strongly encouraged to always use curly braces even in situations where they are technically optional. Having
them increases readability and decreases the likelihood of logic errors being introduced when new lines are added. eg.

	if ($VariableName == true) echo _('Variable was true');

Whilst legal PHP syntax, this should be avoided in favour of the following syntax:

	if ($VariableName == true) {
		echo _('Variable was true');
	}

Parenthesis should open on the same line (after a space) as the initiating control structure and close the statement
block at the same level of indenting as the initiating line.

Else statements should be on the same line as the closing statement block from the preceding elseif or if statement eg.

	if ($VariableName == true) {
		echo 'Variable was true';
	} else {
		echo 'Variable was false';
	} /* end else $VariableName was false */

This is the only time there should be anything other than a comment on the closing curly brace line. Comments on a
closing curly brace line where the block has been quite a few lines of code are encouraged to show the control
structure to which they related.

Whenever a statement block is used code within the block should be one tab indented. Indenting code correctly is critical
to avoid logic errors and to improve readability.

Function definitions should follow the same conventions.

Most programming editors have word wrap and this is preferred to manual line breaks.

### Repeated Code

In general shorter scripts are easier to understand but too much abstraction can make a path hard to follow. The best
solution will depend on the context.

## HTML

HTML keywords and tags should be in lower case to improve.

HTML table cell tags in echo statements should use carriage returns to keep cells together so that it is easy to see what
is in each cell. eg.

	echo '<table> <tr> <td>' . _('Label text') . ':</td> <td>' . $SomeVariable . '</td> <td>' . _('Some Other Label') . ':</td> <td align="right">' . number_format($SomeNumber,2) . '</td> </tr> </table> ';

Would be more easily digested and should be written as

	echo '<table>
			<tr>
				<td>' . _('Label text') . ':</td>
				<td>' . $SomeVariable . '</td>
				<td>' . _('Some Other Label') . '</td>
				<td align="right">' . number_format($SomeNumber,2) . '</td>
			</tr>
		</table>';

Carriage returns and indentation should be used in a similar way for `printf` statements.

All values of html properties should be between double quotes e.g.

	<input type="text" name="InputBox" value="Default" />

This goes hand in hand with using single quotes for all echo statements.

## SQL

The SQL should be ANSI compliant. Using SQL particular to a specific RDBMS is to be avoided in favour of the ANSI equivalent.

NB: ANSI compliant SQL includes: no usage of backtik "`" to quote column names, table names and other database identifiers,
and no usage of the double quote (") as string delimiter.

The webERP goal of providing "low footprint" efficient system - requires careful thought. The number of "round trips"
must be minimised - never go off to the database to get data that could have been got in a prior query. This is
inefficient design and to be avoided.

There should never be any database specific calls in scripts other than `includes/ConnectDB_XXXX.php` where XXXX is the
abbreviation for the RDBMS the abstraction code refers to.

All database calls should be performed by calling the abstraction functions in those scripts (`ConnectDB_postgres.php` was
deprecated but could easily be revived if we stick with this convention).

Table and field names should always use lower case and should be descriptive of the data they hold. e.g. Field names such
as "nw" should be avoided in favour of "netweight"

SQL statements should be on several lines for easier reading eg.

	$SQL = "select transno, trandate, debtortrans.debtorno, branchcode, reference, invtext, order_, rate, ovamount+ovgst+ovfreight+ovdiscount as totalamt, currcode from debtortrans inner join debtorsmaster on debtortrans.debtorno=debtorsmaster.debtorno";

is harder to read than:

	$SQL = "SELECT transno,
		trandate,
		debtortrans.debtorno,
		branchcode, reference,
		invtext,
		order_,
		rate,
		ovamount+ovgst+ovfreight+ovdiscount AS totalamt,
		currcode
	FROM debtortrans
	INNER JOIN debtorsmaster ON debtortrans.debtorno=debtorsmaster.debtorno";

SQL keywords should be capitalised as above eg. `SELECT`, `CASE`, `FROM`, `WHERE`, `GROUP BY`, `ORDER BY`, `AS`, `INNER JOIN` etc.

Line breaks after every comma and on major SQL reserved words as above.

Quoting SQL variables - variables incorporated into SQL strings need to be inside SINGLE quotes so that the variable cannot
be used by a hacker to send spurious SQL to retrieve private data. _This includes variables which represent numbers_

NOTE: Since variables incorporated into a SQL string need to be quoted with single quotes inside the SQL string,
the SQL strings themselves need to be quoted inside double quotes. This is the one exception to the general rule to
always use single quotes for strings for easier quicker PHP parsing.

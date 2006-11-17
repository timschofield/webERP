<?php
/* $Revision: 1.10 $ */
$PageSecurity = 9;

include('includes/session.inc');
$title = _('Currencies Maintenance');

include('includes/header.inc');
include('includes/SQL_CommonFunctions.inc');

if (isset($_GET['SelectedCurrency'])){
    $SelectedCurrency = $_GET['SelectedCurrency'];
} elseif (isset($_POST['SelectedCurrency'])){
    $SelectedCurrency = $_POST['SelectedCurrency'];
}

$ForceConfigReload = true;
include('includes/GetConfig.php');

$FunctionalCurrency = $_SESSION['CompanyRecord']['currencydefault'];


if (isset($_POST['submit'])) {

    //initialise no input errors assumed initially before we test
    $InputError = 0;

    /* actions to take once the user has clicked the submit button
    ie the page has called itself with some user input */

    //first off validate inputs are sensible

    if (strlen($_POST['Abbreviation']) > 3) {
        $InputError = 1;
        prnMsg(_('The currency abbreviation must be 3 characters or less long'),'error');
    } elseif (!is_numeric($_POST['ExchangeRate'])){
        $InputError = 1;
       prnMsg(_('The exchange rate must be numeric'),'error');
    } elseif (strlen($_POST['CurrencyName']) > 20) {
        $InputError = 1;
        prnMsg(_('The currency name must be 20 characters or less long'),'error');
    } elseif (strlen($_POST['Country']) > 50) {
        $InputError = 1;
        prnMsg(_('The currency country must be 50 characters or less long'),'error');
    } elseif (strlen($_POST['HundredsName']) > 15) {
        $InputError = 1;
        prnMsg(_('The hundredths name must be 15 characters or less long'),'error');
    } elseif (($FunctionalCurrency != '') and $SelectedCurrency == $FunctionalCurrency){
        $InputError = 1;
        prnMsg(_('The functional currency cannot be modified or deleted'),'error');
    } elseif (strstr($_POST['Abbreviation'],"'") OR strstr($_POST['Abbreviation'],'+') OR strstr($_POST['Abbreviation'],"\"") OR strstr($_POST['Abbreviation'],'&') OR strstr($_POST['Abbreviation'],' ') OR strstr($_POST['Abbreviation'],"\\") OR strstr($_POST['Abbreviation'],'.') OR strstr($_POST['Abbreviation'],'"')) {
		$InputError = 1;
		prnMsg( _('The currency code cannot contain any of the following characters') . " . - ' & + \" " . _('or a space'),'error');
	}

    if (isset($SelectedCurrency) AND $InputError !=1) {

        /*SelectedCurrency could also exist if submit had not been clicked this code would not run in this case cos submit is false of course  see the delete code below*/
        $sql = "UPDATE currencies SET
			currency='" . DB_escape_string($_POST['CurrencyName']) . "',
			country='". DB_escape_string($_POST['Country']). "',
			hundredsname='" . DB_escape_string($_POST['HundredsName']) . "',
			rate=" .$_POST['ExchangeRate'] . "
		WHERE currabrev = '" . $SelectedCurrency . "'";

        $msg = _('The currency definition record has been updated');
    } else if ($InputError !=1) {

    /*Selected currencies is null cos no item selected on first time round so must be adding a record must be submitting new entries in the new payment terms form */
    	$sql = "INSERT INTO currencies (currency,
    					currabrev,
					country,
					hundredsname,
					rate)
			VALUES ('" . DB_escape_string($_POST['CurrencyName']) . "',
				'" . $_POST['Abbreviation'] . "',
				'" . DB_escape_string($_POST['Country']) . "',
				'" . DB_escape_string($_POST['HundredsName']) .  "',
				" . $_POST['ExchangeRate'] . ")";

    	$msg = _('The currency definition record has been added');
    }
    //run the SQL from either of the above possibilites
    $result = DB_query($sql,$db);
    if ($InputError!=1){
    	prnMsg( $msg,'success');
    }
    unset($SelectedCurrency);

} elseif (isset($_GET['delete'])) {
//the link to delete a selected record was clicked instead of the submit button

// PREVENT DELETES IF DEPENDENT RECORDS IN DebtorsMaster

    $sql= "SELECT COUNT(*) FROM debtorsmaster WHERE debtorsmaster.currcode = '$SelectedCurrency'";
    $result = DB_query($sql,$db);
    $myrow = DB_fetch_row($result);
    if ($myrow[0] > 0)
    {
        prnMsg(_('Cannot delete this currency because customer accounts have been created referring to this currency') .
         	'<BR>' . _('There are') . ' ' . $myrow[0] . ' ' . _('customer accounts that refer to this currency'),'warn');
    } else {
        $sql= "SELECT COUNT(*) FROM suppliers WHERE suppliers.currcode = '$SelectedCurrency'";
        $result = DB_query($sql,$db);
        $myrow = DB_fetch_row($result);
        if ($myrow[0] > 0)
        {
            prnMsg(_('Cannot delete this currency because supplier accounts have been created referring to this currency')
             . '<BR>' . _('There are') . ' ' . $myrow[0] . ' ' . _('supplier accounts that refer to this currency'),'warn');
        } else {
            $sql= "SELECT COUNT(*) FROM banktrans WHERE banktrans.currcode = '$SelectedCurrency'";
            $result = DB_query($sql,$db);
            $myrow = DB_fetch_row($result);
            if ($myrow[0] > 0){
                prnMsg(_('Cannot delete this currency because there are bank transactions that use this currency') .
                '<BR>' . ' ' . _('There are') . ' ' . $myrow[0] . ' ' . _('bank transactions that refer to this currency'),'warn');
            } elseif ($FunctionalCurrency==$SelectedCurrency){
                prnMsg(_('Cannot delete this currency because it is the functional currency of the company'),'warn');
            } else {
                //only delete if used in neither customer or supplier, comp prefs, bank trans accounts
                $sql="DELETE FROM currencies WHERE currabrev='$SelectedCurrency'";
                $result = DB_query($sql,$db);
                prnMsg(_('The currency definition record has been deleted'),'success');
            }
        }
    }
    //end if currency used in customer or supplier accounts
}

if (!isset($SelectedCurrency)) {

/* It could still be the second time the page has been run and a record has been selected for modification - SelectedCurrency will exist because it was sent with the new call. If its the first time the page has been displayed with no parameters
then none of the above are true and the list of payment termss will be displayed with
links to delete or edit each. These will call the same page again and allow update/input
or deletion of the records*/

    $sql = 'SELECT currency, currabrev, country, hundredsname, rate FROM currencies';
    $result = DB_query($sql, $db);

    echo '<CENTER><table border=1>';
    echo "<tr><td class='tableheader'>"._('Abbreviation')."</td>
    		<td class='tableheader'>"._('Currency Name')."</td>
		<td class='tableheader'>"._('Country')."</td>
		<td class='tableheader'>"._('Hundredths Name')."</td>
		<td class='tableheader'>"._('Exchange Rate')."</td></tr>";

    $k=0; //row colour counter
    while ($myrow = DB_fetch_row($result)) {
        if ($myrow[1]==$FunctionalCurrency){
            echo "<tr bgcolor='#FFbbbb'>";
        } elseif ($k==1){
            echo "<tr bgcolor='#CCCCCC'>";
            $k=0;
        } else {
            echo "<tr bgcolor='#EEEEEE'>";
            $k++;
        }
        if ($myrow[1]!=$FunctionalCurrency){
            printf("<td>%s</td>
	    		<td>%s</td>
			<td>%s</td>
			<td>%s</td>
			<td>%s</td>
			<td><a href=\"%s&SelectedCurrency=%s\">%s</a></td>
			<td><a href=\"%s&SelectedCurrency=%s&delete=1\">%s</a></td>
			</tr>",
			$myrow[1],
			$myrow[0],
			$myrow[2],
			$myrow[3],
			$myrow[4],
			$_SERVER['PHP_SELF'] . "?" . SID,
			$myrow[1],
			_('Edit'),
			$_SERVER['PHP_SELF'] . '?' . SID,
			$myrow[1],
			_('Delete'));
        } else {
            printf("<td>%s</td>
	    		<td>%s</td>
			<td>%s</td>
			<td>%s</td>
			<td>%s</td>
			<td colspan=2>%s</td>
			</tr>",
			$myrow[1],
			$myrow[0],
			$myrow[2],
			$myrow[3],
			$myrow[4],
			_('Functional Currency'));
        }

    } //END WHILE LIST LOOP
    echo '</table></CENTER><BR>';
} //end of ifs and buts!


if (isset($SelectedCurrency)) {
    echo '<Center><a href=' .$_SERVER['PHP_SELF']  . '?' . SID. '>'._('Show all currency definitions').'</a></Center>';
}

echo '<BR>';

if (!isset($_GET['delete'])) {

    echo "<FORM METHOD='post' action=" . $_SERVER['PHP_SELF'] . '?' . SID . '>';

    if (isset($SelectedCurrency) AND $SelectedCurrency!='') {
        //editing an existing payment terms

        $sql = "SELECT currency,
			currabrev,
			country,
			hundredsname,
			rate
		FROM currencies
		WHERE currabrev='$SelectedCurrency'";

        $ErrMsg = _('An error occurred in retrieving the currency information');;
	$result = DB_query($sql, $db, $ErrMsg);

        $myrow = DB_fetch_array($result);

        $_POST['Abbreviation'] = $myrow['currabrev'];
        $_POST['CurrencyName']  = $myrow['currency'];
        $_POST['Country']  = $myrow['country'];
        $_POST['HundredsName']  = $myrow['hundredsname'];
        $_POST['ExchangeRate']  = $myrow['rate'];



        echo "<INPUT TYPE=HIDDEN NAME='SelectedCurrency' VALUE='" . $SelectedCurrency . "'>";
        echo "<INPUT TYPE=HIDDEN NAME='Abbreviation' VALUE='" . $_POST['Abbreviation'] . "'>";
        echo "<CENTER><TABLE><TR>
			<TD>"._('Currency Abbreviation').':</TD>
			<TD>';
        echo $_POST['Abbreviation'] . '</TD></TR>';

    } else { //end of if $SelectedCurrency only do the else when a new record is being entered

        echo "<CENTER><TABLE><TR>
		<TD>"._('Currency Abbreviation').":</TD>
		<TD><input type='Text' name='Abbreviation' value='" . $_POST['Abbreviation'] . "' SIZE=4 MAXLENGTH=3></TD></TR>";
    }

    echo '<TR><TD>'._('Currency Name').':</TD>';
    echo '<TD>';
    echo '<INPUT TYPE="text" name="CurrencyName" SIZE=20 MAXLENGTH=20 VALUE="' . $_POST['CurrencyName'] . '">';
    echo '</TD></TR>';
    echo '<TR><TD>'._('Country').':</TD>';
    echo '<TD>';
    echo '<INPUT TYPE="text" name="Country" SIZE=30 MAXLENGTH=50 VALUE="' . $_POST['Country'] . '">';
    echo '</TD></TR>';
    echo '<TR><TD>'._('Hundredths Name').':</TD>';
    echo '<TD>';
    echo '<INPUT TYPE="text" name="HundredsName" SIZE=10 MAXLENGTH=15 VALUE="'. $_POST['HundredsName'].'">';
    echo '</TD></TR>';
    echo '<TR><TD>'._('Exchange Rate').':</TD>';
    echo '<TD>';
    echo '<INPUT TYPE="text" name="ExchangeRate" SIZE=10 MAXLENGTH=9 VALUE='. $_POST['ExchangeRate'].'>';
    echo '</TD></TR>';
    echo '</TABLE>';

    echo '<CENTER><input type="Submit" name="submit" value='._('Enter Information').'>';

    echo '</FORM>';

} //end if record deleted no point displaying form to add record

include('includes/footer.inc');
?>

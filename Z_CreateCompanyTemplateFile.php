<?php
/* $Id: Z_CreateCompanyTemplateFile.php 5914 2013-05-10 15:20:48Z exsonqu $*/

include ('includes/session.inc');
$Title = _('Create Database Template File');
include ('includes/header.inc');

if (isset($_POST['CreateTemplate'])){
      $InputError = false; //assume the best - but check for the worst
      if (mb_strlen($_POST['TemplateName'])<=1){
         prnMsg(_('The name of the template has not been entered or is just 1 character - an informative name is required e.g. Deutsche-distribution in the case of a german distribution company'),'error');
         $InputError = true;
      }

      if ($InputError==false){
          $CurrResult = DB_query( "SELECT currabrev,
								  currency,
								  country,
								  debtorsact,
								  creditorsact,
								  payrollact,
								  grnact,
								  exchangediffact,
								  purchasesexchangediffact,
								  retainedearnings,
								  freightact
								FROM currencies INNER JOIN companies
								ON companies.currencydefault=currencies.currabrev
								WHERE coycode='1'",$db);
          $CurrRow = DB_fetch_array($CurrResult);


          $SQLScript = "SET FOREIGN_KEY_CHECKS=0;
                            DELETE FROM currencies WHERE currabrev='" . $CurrRow['currabrev'] ."';\n";
          $SQLScript .= "INSERT INTO currencies (currabrev, currency, country, rate)
                                VALUES ('" . $CurrRow['currabrev'] . "', '" . $CurrRow['currency'] ."', '" . $CurrRow['country'] . "', 1);\n";
          $SQLScript .= "UPDATE companies SET currencydefault='" . $CurrRow['currabrev'] ."',
                                              regoffice6='" . $CurrRow['country'] . "',
                                              debtorsact=" . $CurrRow['debtorsact'] . ",
                                              creditorsact=" . $CurrRow['creditorsact'] . ",
                                              payrollact=" . $CurrRow['payrollact'] . ",
                                              grnact=" . $CurrRow['grnact'] . ",
                                              exchangediffact=" . $CurrRow['exchangediffact'] . ",
                                              purchasesexchangediffact=" . $CurrRow['purchasesexchangediffact'] . ",
                                              retainedearnings=" . $CurrRow['retainedearnings'] . ",
                                              freightact=" . $CurrRow['freightact'] . "
                          WHERE coycode='1';\n";

          /*empty out any existing records in
           chartmaster,
           accountgroups,
           taxauthorities,
           taxauthrates,
           taxgroups,
           taxgrouptaxes,
           taxcategories,
           taxprovinces */

          $SQLScript .= "TRUNCATE TABLE chartmaster;\n";
          $SQLScript .= "TRUNCATE TABLE accountgroups;\n";
          $SQLScript .= "TRUNCATE TABLE taxauthorities;\n";
          $SQLScript .= "TRUNCATE TABLE taxauthrates;\n";
          $SQLScript .= "TRUNCATE TABLE taxgroups;\n";
          $SQLScript .= "TRUNCATE TABLE taxgrouptaxes;\n";
          $SQLScript .= "TRUNCATE TABLE taxcategories;\n";
          $SQLScript .= "TRUNCATE TABLE taxprovinces;\n";

		  $GroupsResult = DB_query("SELECT groupname,
									sectioninaccounts,
									pandl,
									sequenceintb,
									parentgroupname
									FROM accountgroups",$db);

          while ($GroupRow = DB_fetch_array($GroupsResult)){
              $SQLScript .= "INSERT INTO accountgroups (groupname,sectioninaccounts,pandl, sequenceintb, parentgroupname)
                                   VALUES ('" . $GroupRow['groupname'] . "',
                                          '" . $GroupRow['sectioninaccounts'] . "',
                                          " . $GroupRow['pandl'] . ",
                                          " . $GroupRow['sequenceintb'] . ",
                                          '" . $GroupRow['parentgroupname'] . "');\n";
          }

		  $ChartResult = DB_query("SELECT accountcode, accountname, group_ FROM chartmaster",$db);
          $i=0;
          while ($ChartRow = DB_fetch_array($ChartResult)){
                if ($_POST['IncludeAccount_' .$i]=='on'){

                         $SQLScript .= "INSERT INTO chartmaster (accountcode,accountname,group_)
                                               VALUES ('" . $ChartRow['accountcode'] . "',
								'" . $ChartRow['accountname'] . "',
								'" . $ChartRow['group_'] . "');\n";
                }
                $i++;
          }

          /*Now the tax set up */

          /*Tax Authorities table */
          $TaxAuthoritiesResult = DB_query("SELECT taxid,
										description,
										taxglcode,
										purchtaxglaccount,
										bank,
										bankacctype,
										bankacc,
										bankswift
										FROM taxauthorities",$db);

          while ($TaxAuthoritiesRow = DB_fetch_array($TaxAuthoritiesResult)){
              $SQLScript .= "INSERT INTO taxauthorities (taxid,
                                                   description,
                                                   taxglcode,
                                                   purchtaxglaccount,
                                                   bank,
                                                   bankacctype,
                                                   bankacc,
                                                   bankswift)
                                   VALUES (" . $TaxAuthoritiesRow['taxid'] . ",
                                          '" . $TaxAuthoritiesRow['description'] . "',
                                          " . $TaxAuthoritiesRow['taxglcode'] . ",
                                          " . $TaxAuthoritiesRow['purchtaxglaccount'] . ",
                                          '" . $TaxAuthoritiesRow['bank'] . "',
                                          '" . $TaxAuthoritiesRow['bankacctype'] . "',
                                          '" . $TaxAuthoritiesRow['bankacc'] . "',
                                          '" . $TaxAuthoritiesRow['bankaccswift'] . "');\n";
          }

          /*taxauthrates table */
          $TaxAuthRatesResult = DB_query("SELECT taxauthority,
									 dispatchtaxprovince,
									 taxcatid,
									 taxrate
									FROM taxauthrates",$db);

          while ($TaxAuthRatesRow = DB_fetch_array($TaxAuthRatesResult)){
              $SQLScript .= "INSERT INTO taxauthrates (taxauthority,
                                                       dispatchtaxprovince,
                                                       taxcatid,
                                                       taxrate)
                                   VALUES (" . $TaxAuthRatesRow['taxauthority'] . ",
                                          " . $TaxAuthRatesRow['dispatchtaxprovince'] . ",
                                          " . $TaxAuthRatesRow['taxcatid'] . ",
                                          " . $TaxAuthRatesRow['taxrate'] . ");\n";
          }

          /*taxgroups table */
          $TaxGroupsResult = DB_query("SELECT taxgroupid,
										taxgroupdescription
										FROM taxgroups",$db);

          while ($TaxGroupsRow = DB_fetch_array($TaxGroupsResult)){
              $SQLScript .= "INSERT INTO taxgroups (taxgroupid,
                                                    taxgroupdescription)
                                   VALUES ('" . $TaxGroupsRow['taxgroupid'] . "',
                                          '" . $TaxGroupsRow['taxgroupdescription'] . "');\n";
          }
          /*tax categories table */
          $TaxCategoriesResult = DB_query("SELECT taxcatid,
				                                              taxcatname
				                                            FROM taxcategories",$db);

          while ($TaxCategoriesRow = DB_fetch_array($TaxCategoriesResult)){
              $SQLScript .= "INSERT INTO taxcategories (taxcatid,
                                                    taxcatname)
                                   VALUES (" . $TaxCategoriesRow['taxcatid'] . ",
                                          '" . $TaxCategoriesRow['taxcatname'] . "');\n";
          }
          /*tax provinces table */
          $TaxProvincesResult = DB_query("SELECT taxprovinceid,
				                                              taxprovincename
				                                            FROM taxprovinces",$db);

          while ($TaxProvincesRow = DB_fetch_array($TaxProvincesResult)){
              $SQLScript .= "INSERT INTO taxprovinces (taxprovinceid,
                                                    taxprovincename)
                                   VALUES (" . $TaxProvincesRow['taxprovinceid'] . ",
                                          '" . $TaxProvincesRow['taxprovincename'] . "');\n";
          }
          /*taxgroup taxes table */
          $TaxGroupTaxesResult = DB_query("SELECT taxgroupid,
					                                                 taxauthid,
					                                                 calculationorder,
					                                                 taxontax
					                                            FROM taxgrouptaxes",$db);

          while ($TaxGroupTaxesRow = DB_fetch_array($TaxGroupTaxesResult)){
              $SQLScript .= "INSERT INTO taxgrouptaxes (taxgroupid,
                                                        taxauthid,
                                                        calculationorder,
                                                        taxontax)
                                   VALUES (" . $TaxGroupTaxesRow['taxgroupid'] . ",
                                           " . $TaxGroupTaxesRow['taxauthid'] . ",
                                           " . $TaxGroupTaxesRow['calculationorder'] . ",
                                           " . $TaxGroupTaxesRow['taxontax'] . ");\n";
          }
		  $SQLScript .= "SET FOREIGN_KEY_CHECKS=1;";
          /*Now write $SQLScript to a file */
          $FileHandle = fopen("./companies/" . $_SESSION['DatabaseName'] . "/reports/" . $_POST['TemplateName'] .".sql","w");
           fwrite ($FileHandle, $SQLScript);
           fclose ($FileHandle);

           echo '<P><a href="' . $RootPath . '/companies/' . $_SESSION['DatabaseName'] . '/reports/' . $_POST['TemplateName'] .'.sql">' . _('Show the sql template file produced') . '</a>';
		   include('includes/htmlMimeMail.php');
		   $Recipients = array('"Submissions" <submissions@weberp.org>');
		   $mail = new htmlMimeMail();
		   $Host = $_SERVER['HTTP_HOST'];
		   $attachment = $mail->getFile( 'http://'.$Host.$RootPath . '/companies/' . $_SESSION['DatabaseName'] . '/reports/' . $_POST['TemplateName'] .'.sql');
		   $mail->setText('Please find company template ' . $_POST['TemplateName']);
		   $mail->addAttachment($attachment, 'CompanyTemplate.sql', 'application/txt');
		   $mail->setSubject('Company Template Submission');
		   if($_SESSION['SmtpSetting']==0){
		 	 $mail->setFrom($_SESSION['CompanyRecord']['coyname'] . '<' . $_SESSION['CompanyRecord']['email'] . '>');
			 $result = $mail->send($Recipients);
		   }else{
			$result = SendmailBySmtp($mail,$Recipients);   
		   }
          /*end of SQL Script creation */
      }/*end if Input error*/
} /*end submit button hit */

echo '<form method="post" action="' . htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') . '">';
echo '<div class="centre">';
echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';
prnMsg(_('Running the create a new company template script will export all account groups, account codes and tax set up tables including tax groups, tax authorities, tax rates etc. However, no transactions or private data will be exported. There is opportunity to prevent specific general ledger accounts from being exported where these are considered private - again no transactional or balance data is exported and you can inspect the contents of the sql file. The template file will be emailed automatically to the webERP project'),'info');

echo _('Enter the name of the template to be created') . ':<input type="text" name="TemplateName" />';

prnMsg(_('Warning: All selected accounts will be exported - please de-select the accounts you do not wish to export to the new template file'),'warn');

echo '<table>';
 /*Show the chart of accounts to be exported for deslection of company specific ones */

$ChartResult = DB_query("SELECT accountcode, accountname, group_ FROM chartmaster",$db);

$TableHeadings = '<tr><th>' . _('Account Code') . '</th>
					<th>' . _('Account Name') . '</th></tr>';
$i = 0;
while ($ChartRow = DB_fetch_array($ChartResult)){
     echo '<tr><td>' . $ChartRow['accountcode'] . '</td>
               <td>' . htmlspecialchars($ChartRow['accountname'],ENT_QUOTES,'UTF-8',false) . '</td>
               <td><input type="checkbox" name="IncludeAccount_' . $i . '" checked="checked" /></td>
          </tr>';
     $i++;
}

echo '</table>';
echo '<hr />';
echo '<div class="centre"><input type="submit" name="CreateTemplate" value="' . _('Create Template and Email') . '" /></div>';

echo '</div>
      </form>';
include('includes/footer.inc');
?>

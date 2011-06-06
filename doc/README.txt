webERP README

/* $Id$*/

Version  4.x of webERP

Now fully utf-8 compatible. Now reports can be created in any language using the utf-8 character set, the resultant pdf reports  use Adobe CID fonts and the fonts that come with the Adobe Acrobat reader on client computers. This avoids the problem of large pdf downloads on the creation of reports as the alternative is to bundle the enormous utf-8 fonts with the reports. 

Also, this release comes with an automated database upgrade system, so that database changes in later versions will be applied automatically on upgrade of the scripts.

Other than the re-engineering of pdf reporting using the TCPDF pdf report creation class, that has enabled the utf-8 pdf reporting, the other new areas of functionality since the 3 series include:

1. Fixed assets module, this allows the recording of fixed asset additions, depreciation calculations on a monthly basis and disposals all integrated with the sale/debtors system with appropriate general ledger journals also created.

2. Contract Costing functionality that allows contracts to be defined and the costs recorded against the contract. Contracts can be created using items from stock and also other items that might be required to be purchased that are not currently stock items. Contracts can be converted to quotations and from a quotation to a sales order/sale. The final variances on contracts are recorded and a final costing comparison report available.

3. Significant work has also been done in conjunction with Secunia, the software security testing people, who have scruitinised webERP for security vulnerabilities. Their findings were useful in identifying scripts that needed to be changed. All identified weaknesses have now been removed.

4. Prices can now be set for a period - with start and end date, to allow for promotional periods where the price should revert back and can be set ahead of time.

5. Counter Sales functionality to allow sales to be processed to a default cash sales account and receipt of cash processed at the same time. This avoids having to enter a sales order, confirm the dispatch to invoice, the also entering the receipt as separate process and effectively enables a kind of point of sale system for each inventory location.

6. A significant push has been made to try to reduce the number of bugs, with a great deal of testing from all quarters.

The change log shows descriptions and dates of all changes made.

Installation instructions are in the file INSTALL.txt in the doc directory. It is important to read the INSTALL.txt file in its entirety before proceeding. A printout is recommended.

The user documentation contains a wealth of information and is installed under the doc/Manual directory in html format. Links to it are available from the application itself.

SUPPORT

The primary means of support queries is through the user mailing list.
Please join the list at: http://lists.sourceforge.net/lists/listinfo/web-erp-users
if you have queries. The archives of the mailing lists on sourceforge and the FAQ (see http://www.weberp.org/wikidocs/FrequentlyAskedQuestionsInstallation) contain the most common issues with respect to installation.

Feedback, wants and gripes are encouraged in the interests of improving the applicaton.

DEVELOPING

Contributions of code are documents including HOW-TOs with screen-shots etc are encouraged. Contributions in the form of bug reports or other feedback through the mainling lists above are also encouraged.
Guidelines for contributing code are in the document at http://www.weberp.org/wikidocs/ContributingtowebERP developers should read this document carefully and follow the guidelines therein. Standards and conventions used in the code are rigorously applied in the interests of consistency and readability. Code submitted that does not conform to these standards will be changed so it does where possible. If the job to make the code conform to webERP standards is too large then the code will not be included.

TRANSLATIONS

All available translations are now included in the archive downloaded.

Translators should read the document http://www.weberp.org/HowToTranslate which describes how to translate webERP.

Translations must be installed under the webERP/locale directory and the locale must be available on the web-server.

LEGAL

This program is free software; you can redistribute it and/or modify it under the terms of the GNU General Public License
as published by the Free Software Foundation; version 2 of the License.

This program is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU General Public License for more details.

A copy of the GNU General Public License is included in the doc directory along with this program; if not, write to the Free Software
Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307, USA.

Copyright weberp.org 2011 - Contact: info@weberp.org
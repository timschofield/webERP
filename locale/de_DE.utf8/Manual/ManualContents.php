<!-- This table of contents allows the choice to display one section or select multiple sections to format for print.
     Selecting multiple sections is for printing
-->

<!-- The individual topics in the manual are in straight html files that are called along with the header and foot from here.
     No style, inline style or style sheet on purpose.
     In this way the help can be easily broken into sections for online context-sensitive help.
		 The only html used in them are:
		 <br>
		 <div>
		 <table>
		 <font>
		 <b>
		 <u>
		 <ul>
		 <ol>

		 Comments beginning with Help Begin and Help End denote the beginning and end of a section that goes into the online help.
		 What section is named after Help Begin: and there can be multiple sections separated with a comma.
-->

<?php
include('ManualHeader.html');

?>
	<form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="POST">
<?php
if (((!isset($_POST['Submit'])) AND (!isset($_GET['ViewTopic']))) OR
     ((isset($_POST['Submit'])) AND (isset($_POST['SelectTableOfContents'])))) {
// if not submittws then coming into manual to look at TOC
// if SelectTableOfContents set then user wants it displayed
?>
<?php
  if (!isset($_POST['Submit'])) {
?>  
          <input type="submit" name="Submit" value="Markierte anzeigen"><br/>
					Klicken Sie auf einen Titel, um den Anschnitt anzuzeigen.  Markieren Sie Auswahlkästchen und drücken Sie dann auf "Markierte anzeigen", wenn Sie eine druckfähige Ausgabe erzeugen möchten. 
					<br><br><br> 
<?php
  }
?>
    <table cellpadding="0" cellspacing="0">
      <tr>
        <td>
<?php
  if (!isset($_POST['Submit'])) {
?>  
  	      <input type="checkbox" name="SelectTableOfContents">
<?php
  }
?>
          <font size="+3"><b>Inhaltsverzeichnis</b></font>
          <br><br>
          <UL>
            <LI>
<?php
  if (!isset($_POST['Submit'])) {
?>  
              <input type="checkbox" name="SelectIntroduction">
              <A HREF="<?php echo $_SERVER['PHP_SELF'] . '?ViewTopic=Introduction'; ?>">Einleitung</A>
<?php
  } else {
?>
              <A href="#Introduction">Einleitung</A>
<?php	
	}
?>
              <UL>
                <LI>Warum noch ein Buchhaltungsprogramm?</LI>
              </UL>
              <BR>
            </LI>
						<LI>
<?php
  if (!isset($_POST['Submit'])) {
?>  
              <input type="checkbox" name="SelectRequirements">
              <A HREF="<?php echo $_SERVER['PHP_SELF'] . '?ViewTopic=Requirements'; ?>">Anforderungen</A>
<?php
  } else {
?>
              <A href="#Requirements">Anforderungen</A>
<?php	
	}
?>
              <UL>
                <LI>Hardware-Anforderungen </LI>
                <LI>Software-Anforderungen</LI>
              </UL>
              <BR>
            </LI>
						<LI>
<?php
  if (!isset($_POST['Submit'])) {
?>  
              <input type="checkbox" name="SelectGettingStarted">
              <A HREF="<?php echo $_SERVER['PHP_SELF'] . '?ViewTopic=GettingStarted'; ?>">Inbetriebnahme</A>
<?php
  } else {
?>
              <A HREF="#GettingStarted">Inbetriebnahme</A>
<?php	
  }
?>
              <UL>
                <LI>Voraussetzungen</LI>
                <LI>Die PHP-Scripte kopieren</LI>
                <LI>Die Datenbank anlegen</LI>
                <LI>Die Datei config.php bearbeiten</LI>
                <LI>Erstmalige Anmeldung</LI>
                <LI>Layouts und GUI-Anpassungen</LI>
                <LI>Benutzer einrichten</LI>
              </UL>
              <BR>
            </LI>
            <LI>
<?php
  if (!isset($_POST['Submit'])) {
?>  
              <input type="checkbox" name="SelectSecuritySchema">
              <A HREF="<?php echo $_SERVER['PHP_SELF'] . '?ViewTopic=SecuritySchema'; ?>">Sicherheitskonzept</A>
<?php
  } else {
?>
              <A HREF="#SecuritySchema">Sicherheitskonzept</A>
<?php	
  }
?>
            </LI>
            <br><br>
            <LI>
<?php
  if (!isset($_POST['Submit'])) {
?>  
              <input type="checkbox" name="SelectCreatingNewSystem">
              <A HREF="<?php echo $_SERVER['PHP_SELF'] . '?ViewTopic=CreatingNewSystem'; ?>">Ein neues System einrichten</A>
<?php
  } else {
?>
              <A HREF="#CreatingNewSystem">Ein neues System einrichten</A>
<?php	
  }
?>
              <UL>
                <LI>Das Demosystem erproben</LI>
                <LI>Einen Mandanten einrichten</LI>
                <LI>Materialien einrichten</LI>
                <LI>Materialbestände einpflegen</LI>
                <LI>Problematik der Integration der Bestandsführung mit dem Hauptbuch</LI>
                <LI>Kundenstammdaten erfassen</LI>
                <LI>Kundensalden aufnehmen</LI>
                <LI>Das Sammelkonto für Debitorenforderungen</LI>
                <LI>Zum Schluss</LI>
              </UL>
              <BR>
						</LI>	
            <LI>
<?php
  if (!isset($_POST['Submit'])) {
?>  
              <input type="checkbox" name="SelectSystemConventions">
              <A HREF="<?php echo $_SERVER['PHP_SELF'] . '?ViewTopic=SystemConventions'; ?>">System-Gepflogenheiten </A>
<?php
  } else {
?>
              <A HREF="#SystemConventions">System-Gepflogenheiten </A>
<?php	
  }
?>
              <UL>
                <LI>Navigation im Menü</LI>
                <LI>Berichtswesen</LI>
              </UL>
              <BR>
            </LI>
						<LI>
<?php
  if (!isset($_POST['Submit'])) {
?>  
              <input type="checkbox" name="SelectInventory">
              <A HREF="<?php echo $_SERVER['PHP_SELF'] . '?ViewTopic=Inventory'; ?>">Bestandsführung</A>
<?php
  } else {
?>
              <A HREF="#Inventory">Bestandsführung</A>
<?php	
  }
?>
              <UL>
                <LI>Übersicht</LI>
                <LI>Merkmale der Bestandsführung</LI>
                <LI>Neue Materialien anlegen</LI>
                <LI>Materialnummer</LI>
                <LI>Materialbezeichnung</LI>
                <LI>Warengruppe</LI>
                <LI>Optimale Bestellmenge</LI>
                <LI>Volumen pro Verpackungseinheit</LI>
                <LI>Gewicht pro Verpackungseinheit</LI>
                <LI>Maßeinheiten</LI>
                <LI>CZeitgemäß oder veraltet </LI>
                <LI>Materialart</LI>
                <LI>Sammelmaterialien einrichten</LI>
                <LI>Chargen-, Seriennummern oder Lose überwachen</LI>
                <LI>mit Seriennummer</LI>
                <LI>Barcode</LI>
                <LI>Rabattgruppe</LI>
                <LI>Dezimalstellen</LI>
                <LI>Bestandsbewertung</LI>
                <LI>Materialkosten</LI>
                <LI>Arbeitskosten</LI>
                <LI>Gemeinkosten</LI>
                <LI>Überlegungen zu den Standardkosten</LI>
                <LI>Istkosten</LI>
                <LI>Änderungen an den Arbeitskosten, Materialkosten oder Gemeinkosten</LI>
                <LI>Materialsuche</LI>
                <LI>Materialstamm ändern</LI>
                <LI>Änderung der Warengruppe</LI>
                <LI>Änderung der Materialart </LI>
                <LI>Warengruppen</LI>
                <LI>Warengruppen-Schlüssel</LI>
                <LI>Warengruppen-Beschreibung</LI>
                <LI>Konto Bestand</LI>
                <LI>Konto Bestandskorrekturen</LI>
                <LI>Konto Einkaufsabweichungen</LI>
                <LI>Konto Fertigungsabweichungen</LI>
                <LI>Ressourcentyp</LI>
                <LI>Betriebsstätten (Lagerorte) pflegen</LI>
                <LI>Bestandskorrekturen</LI>
                <LI>Umlagerungen</LI>
                <LI>Bestandsauswertungen und -berichte</LI>
                <LI>Auswertung Bestandsstatus</LI>
                <LI>Auswertung Warenbewegungen</LI>
                <LI>Auswertung Bestandsverwendung</LI>
                <LI>Bericht Bestandsbewertung</LI>
                <LI>Bericht Bestandsplanung</LI>
                <LI>Inventur</LI>
              </UL>
              <BR>
            </LI>
            <LI>
<?php
  if (!isset($_POST['Submit'])) {
?>
              <input type="checkbox" name="SelectAccountsReceivable">
              <A HREF="<?php echo $_SERVER['PHP_SELF'] . '?ViewTopic=AccountsReceivable'; ?>">Debitorenbuchhaltung</A>
<?php
  } else {
?>
              <A HREF="#AccountsReceivable">Debitorenbuchhaltung</A>
<?php
  }
?>
              <UL>
                <LI>Übersicht</LI>
                <LI>Merkmale der Debitorenbuchhaltung</LI>
                <LI>Neue Kunden anlegen</LI>
                <LI>Kundennummer</LI>
                <LI>Kundenname</LI>
                <LI>Adresszeilen 1, 2, 3, 4, 5 und 6</LI>
                <LI>Währung</LI>
                <LI>Rabattsatz</LI>
                <LI>Skontoprozentsatz</LI>
                <LI>Kunde seit</LI>
                <LI>Zahlungsbedingungen</LI>
                <LI>Kreditstatus</LI>
                <LI>Kreditlimit</LI>
                <LI>Rechnung senden an</LI>
                <LI>Kundenniederlassungen anlegen</LI>
                <LI>Name der Niederlassung</LI>
                <LI>Nummer der Niederlassung</LI>
                <LI>Kontakt / Telefon / Fax / Adresse</LI>
                <LI>Verkäufer</LI>
                <LI>bezieht Waren vom Lager</LI>
                <LI>Vordatieren nach dem (Tag im Monat)</LI>
                <LI>Lieferfrist Tage</LI>
                <LI>Telefon/Fax/Email</LI>
                <LI>Steuergruppe</LI>
                <LI>Aufträge für diese Niederlassung</LI>
                <LI>Übliche Versandart</LI>
                <LI>Postanschrift Zeilen 1, 2, 3 und 4</LI>
                <LI>Änderungen an den Kundendaten</LI>
                <LI>Versandarten</LI>
              </UL>
              <BR>
            </LI>
            <LI>

<?php
  if (!isset($_POST['Submit'])) {
?>
              <input type="checkbox" name="SelectAccountsPayable">
              <A HREF="<?php echo $_SERVER['PHP_SELF'] . '?ViewTopic=AccountsPayable'; ?>">Kreditorenbuchhaltung</A>
<?php
  } else {
?>
              <A HREF="#AccountsPayable">Kreditorenbuchhaltung</A>
<?php
  }
?>
              <UL>
                <LI>Übersicht</LI>
                <LI>Merkmale der Kreditorenbuchhaltung</LI>
                <LI>Lieferanten (Kreditoren) anlegen</LI>
                <LI>Lieferantennummer</LI>
                <LI>Lieferantenname</LI>
                <LI>Adresszeilen 1, 2, 3 und 4</LI>
                <LI>Lieferant seit </LI>
                <LI>Zahlungsbedingung</LI>
                <LI>Bankangaben, Bankreferenz</LI>
                <LI>Bankkontonummer</LI>
                <LI>Lieferantenwährung</LI>
		<LI>Zahlungsmitteilung</LI>
              </UL>
              <BR>
            </LI>
            <LI>
<?php
  if (!isset($_POST['Submit'])) {
?>
              <input type="checkbox" name="SelectSalesPeople">
              <A HREF="<?php echo $_SERVER['PHP_SELF'] . '?ViewTopic=SalesPeople'; ?>">Verkäufer</A>
<?php
  } else {
?>
              <A HREF="#SalesPeople">Verkäufer</A>
<?php	
  }
?>
              <UL>
                <LI>Verkäuferstammdaten</LI>
                <LI>Verkäufer-Schlüssel</LI>
                <LI>Kommunikationsdaten</LI>
                <LI>Provisionssätze und Grenzbetrag</LI>
              </UL>
              <BR>
            </LI>
            <LI>
<?php
  if (!isset($_POST['Submit'])) {
?>  
              <input type="checkbox" name="SelectSalesTypes">
              <A HREF="<?php echo $_SERVER['PHP_SELF'] . '?ViewTopic=SalesTypes'; ?>">Umsatzarten/Preislisten</A>
<?php
  } else {
?>
              <A HREF="#SalesTypes">Umsatzarten/Preislisten</A>
<?php	
  }
?>
              <UL>
                <LI>Umsatzart-ID</LI>
                <LI>Umsatzart-Bezeichnung</LI>
              </UL>
              <BR>
            </LI>
            <LI>
<?php
  if (!isset($_POST['Submit'])) {
?>
              <input type="checkbox" name="SelectPaymentTerms">
              <A HREF="<?php echo $_SERVER['PHP_SELF'] . '?ViewTopic=PaymentTerms'; ?>">Zahlungsbedingungen</A>
<?php
  } else {
?>
              <A HREF="#PaymentTerms">Zahlungsbedingungen</A>
<?php	
  }
?>
              <UL>
                <LI>ZB-Schlüssel</LI>
                <LI>Beschreibung der Zahlungsbedingung</LI>
                <LI>Fällig nach Anzahl Tage / Tage oder Tag im Folgemonat</LI>
              </UL>
              <BR>
            </LI>
            <LI>
<?php
  if (!isset($_POST['Submit'])) {
?>  
              <input type="checkbox" name="SelectCreditStatus">
              <A HREF="<?php echo $_SERVER['PHP_SELF'] . '?ViewTopic=CreditStatus'; ?>">Kreditstatus</A>
<?php
  } else {
?>
              <A HREF="#CreditStatus">Kreditstatus</A>
<?php	
  }
?>
              <UL>
                <LI>Credit Status Ratings</LI>
                <LI>Status-Schlüssel</LI>
                <LI>Beschreibung</LI>
                <LI>Fakturasperre</LI>
              </UL>
              <BR>
            </LI>
            <LI>
<?php
  if (!isset($_POST['Submit'])) {
?>  
              <input type="checkbox" name="SelectTax">
              <A HREF="<?php echo $_SERVER['PHP_SELF'] . '?ViewTopic=Tax'; ?>">Steuern</A>
<?php
  } else {
?>
              <A HREF="#Tax">Steuern</A>
<?php	
  }
?>
              <UL>
                <LI>Steuerberechnungen</LI>
                <LI>Überblick</LI>
                <LI>Steuern einrichten</LI>
                <LI>Beispiel 1: Eine Verkaufssteuer innerhalb eines Steuerstandortes - Zwei Steuerkategorien</LI>
                <LI>Beispiel 2: Verkauf innerhalb eines Steuerstandortes - drei Steuersätze</LI>
                <LI>Beispiel 3: Verkauf zwischen zwei Steuerstandorten - drei Steuersätze</LI>
              </UL>
              <BR>
            </LI>
            <LI>
<?php
  if (!isset($_POST['Submit'])) {
?>  
              <input type="checkbox" name="SelectPrices">
              <A HREF="<?php echo $_SERVER['PHP_SELF'] . '?ViewTopic=Prices'; ?>">Preise und Rabatte</A>
<?php
  } else {
?>
              <A HREF="#Prices">Preise und Rabatte</A>
<?php	
  }
?>
              <UL>
                <LI>Preise und Rabatte</LI>
                <LI>Übersicht</LI>
                <LI>Preise pflegen</LI>
                <LI>Rabattstaffel</LI>
              </UL>
              <BR>
            </LI>
            <LI>
<?php
  if (!isset($_POST['Submit'])) {
?>  
              <input type="checkbox" name="SelectARTransactions">
              <A HREF="<?php echo $_SERVER['PHP_SELF'] . '?ViewTopic=ARTransactions'; ?>">Debitorenbuchungen</A>
<?php
  } else {
?>
              <A HREF="#ARTransactions">Debitorenbuchungen</A>
<?php	
  }
?>
              <UL>
                <LI>Fakturieren eines Kundenauftrages</LI>
                <LI>Auftrag zum Fakturieren auswählen</LI>
                <LI>Erstellen der Faktura zum Kundenauftrag</LI>
                <LI>Gutschriften</LI>
                <LI>Erfassung von Zahlungseingängen</LI>
                <LI>Zahlungseingang - Debitor</LI>
                <LI>Zahlungseingang - Datum</LI>
                <LI>Zahlungseingang - Währung und Umrechnungskurs</LI>
                <LI>Zahlungseingang - Zahlweg</LI>
                <LI>Zahlungseingang - Betrag</LI>
                <LI>Zahlungseingang - Skonto</LI>
                <LI>Zahlungseingang - Ausgleichen mit der Rechnung</LI>
                <LI>Kursdifferenzen</LI>
                <LI>Zahlungseingänge verbuchen</LI>
                <LI>Liste der Zahlungseingänge</LI>
                <LI>Habenbeträge dem Debitorenkonto zuordnen</LI>
              </UL>
              <BR>
            </LI>
            <LI>
<?php
  if (!isset($_POST['Submit'])) {
?>  
              <input type="checkbox" name="SelectARInquiries">
              <A HREF="<?php echo $_SERVER['PHP_SELF'] . '?ViewTopic=ARInquiries'; ?>">Debitorenauswertungen</A>
<?php
  } else {
?>
              <A HREF="#ARInquiries">Debitorenauswertungen</A>
<?php	
  }
?>
              <UL>
                <LI>Kundenauswertungen</LI>
                <LI>Auswertung des Debitorenkontos</LI>
                <LI>Beleganzeige</LI>
              </UL>
              <BR>
            </LI>
            <LI>
<?php
  if (!isset($_POST['Submit'])) {
?>  
              <input type="checkbox" name="SelectARReports">
              <A HREF="<?php echo $_SERVER['PHP_SELF'] . '?ViewTopic=ARReports'; ?>">Debitorenberichte</A>
<?php
  } else {
?>
              <A HREF="#ARReports">Debitorenberichte</A>
<?php	
  }
?>
              <UL>
                <LI>Customers - Reporting</LI>
                <LI>Gerasterte Debitorensalden</LI>
                <LI>Kontoauszüge</LI>
                <LI>Auswertung der Kundenvorgänge</LI>
                <LI>Rechnungen und Gutschriften drucken</LI>
              </UL>
              <BR>
            </LI>
            <LI>
<?php
  if (!isset($_POST['Submit'])) {
?>  
              <input type="checkbox" name="SelectSalesAnalysis">
              <A HREF="<?php echo $_SERVER['PHP_SELF'] . '?ViewTopic=SalesAnalysis'; ?>">Umsatzauswertungen</A>
<?php
  } else {
?>
              <A HREF="#SalesAnalysis">Umsatzauswertungen</A>
<?php	
  }
?>
              <UL>
                <LI>Umsatzauswertungen</LI>
                <LI>Kopf des Ergebnisberichtes</LI>
                <LI>Spalten der Ergebnisberichte</LI>
                <LI>Automatisierung der Ergebnisberichte</LI>
              </UL>
              <BR>
            </LI>
            <LI>
<?php
  if (!isset($_POST['Submit'])) {
?>  
              <input type="checkbox" name="SelectSalesOrders">
              <A HREF="<?php echo $_SERVER['PHP_SELF'] . '?ViewTopic=SalesOrders'; ?>">Kundenaufträge</A>
<?php
  } else {
?>
              <A HREF="#SalesOrders">Kundenaufträge</A>
<?php	
  }
?>
              <UL>
                <LI>Kundenaufträge</LI>
                <LI>Funktionalität</LI>
                <LI>Kundenaufträge erfassen</LI>
                <LI>Auswahl des Kunden und der Niederlassung</LI>
                <LI>Auswahl der Kundenauftragspositionen</LI>
                <LI>Lieferangaben</LI>
                <LI>Kundenauftrag ändern</LI>
		<LI>Angebote</LI>
		<LI>Daueraufträge</LI>
              </UL>
              <BR>
            </LI>
            <LI>
<?php
  if (!isset($_POST['Submit'])) {
?>  
              <input type="checkbox" name="SelectShipments">
              <A HREF="<?php echo $_SERVER['PHP_SELF'] . '?ViewTopic=Shipments'; ?>">Transportkosten</A>
<?php
  } else {
?>
              <A HREF="#Shipments">Transportkosten</A>
<?php	
  }
?>
              <UL>
                <LI>Transportkosten</LI>
                <LI>Buchung der Transportkosten im Hauptbuch</LI>
                <LI>Transport anlegen</LI>
                <LI>Transportkalkulation</LI>
                <LI>Abrechnung eines Transportes</LI>
              </UL>
              <BR>
            </LI>
            <LI>
<?php
  if (!isset($_POST['Submit'])) {
?>  
              <input type="checkbox" name="SelectManufacturing">
              <A HREF="<?php echo $_SERVER['PHP_SELF'] . '?ViewTopic=Manufacturing'; ?>">Fertigung</A>
<?php
  } else {
?>
              <A HREF="#Manufacturing">Fertigung</A>
<?php	
  }
?>
              <UL>
                <LI>Fertigung Überblick</LI>
                <LI>Hauptbuch-Integration</LI>
                <LI>Fertigungsauftrag anlegen</LI>
                <LI>Ablieferungen zum Fertigungsauftrag</LI>
                <LI>Entnahmen zum Fertigungsauftrag</LI>
                <LI>Fertigungsaufträge abschließen</LI>
              </UL>
              <BR>
            </LI>
            <LI>
<?php
  if (!isset($_POST['Submit'])) {
?>  
              <input type="checkbox" name="SelectGeneralLedger">
              <A HREF="<?php echo $_SERVER['PHP_SELF'] . '?ViewTopic=GeneralLedger'; ?>">Hauptbuchhaltung</A>
<?php
  } else {
?>
              <A HREF="#GeneralLedger">Hauptbuchhaltung</A>
<?php	
  }
?>
              <UL>
                <LI>Überblick</LI>
                <LI>Kontengruppen</LI>
                <LI>Bankkonten</LI>
                <LI>Zahlungsausgänge</LI>
                <LI>Einrichten der Hauptbuchintegration</LI>
                <LI>Umsatzpositionen</LI>
                <LI>Bestandsbuchungen</LI>
                <LI>EDI</LI>
                <LI>EDI einrichten</LI>
                <LI>Versenden von EDI-Rechnungen</LI>
              </UL>
              <BR>
            </LI>
            <LI>
<?php
if (!isset($_POST['Submit'])) {
?>  
              <input type="checkbox" name="SelectReportBuilder">
              <A HREF="<?php echo $_SERVER['PHP_SELF'] . '?ViewTopic=ReportBuilder'; ?>">Report Builder/Form Builder</A>
<?php
  } else {
?>
              <A HREF="#ReportBuilder">Report Builder/Form Builder</A>
<?php	
  }
?>
              <UL>
                <LI>Einführung</LI>
                <LI>Reports Administration</LI>
                <LI>Importing and Exporting Reports</LI>
                <LI>Editing Copying Renaming Reports</LI>
                <LI>Creating A New Report - Identification</LI>
                <LI>Creating A New Report - Page Setup</LI>
                <LI>Creating A New Report - Specifying Database Tables and Links</LI>
                <LI>Creating A New Report - Specifying fields to Retrieve</LI>
                <LI>Creating A New Report - Entering and Arranging Criteria</LI>
                <LI>Viewing Reports</LI>
              </UL>
              <BR>
            </LI>
            <LI>
<?php
  if (!isset($_POST['Submit'])) {
?>  
              <input type="checkbox" name="SelectMultilanguage">
              <A HREF="<?php echo $_SERVER['PHP_SELF'] . '?ViewTopic=Multilanguage'; ?>">Mehrsprachigkeit</A>
<?php
  } else {
?>
              <A HREF="#Multilanguage">Mehrsprachigkeit</A>
<?php	
  }
?>
              <UL>
                <LI>Mehrsprachigkeit</LI>
                <LI>Die System-Sprachdatei neu erstellen</LI>
                <LI>Eine neue Sprache zum System hinzufügen</LI>
                <LI>Sprachdatei-Kopf bearbeiten</LI>
                <LI>Sprachdatei-Module bearbeiten</LI>
              </UL>
              <BR>
            </LI>
            <LI>
<?php
  if (!isset($_POST['Submit'])) {
?>  
              <input type="checkbox" name="SelectSpecialUtilities">
              <A HREF="<?php echo $_SERVER['PHP_SELF'] . '?ViewTopic=SpecialUtilities'; ?>">Servicewerkzeuge</A>
<?php
  } else {
?>
              <A HREF="#SpecialUtilities">Servicewerkzeuge</A>
<?php	
  }
?>
              <UL>
                <LI>Ergebnisrechnungssätze zu Standardkosten neu bewerten</LI>
                <LI>Eine Kundennummer ändern</LI>
                <LI>Eine Materialnummer ändern</LI>
                <LI>Bestandsdatensätze erzeugen</LI>
                <LI>Hauptbuchsalden nachbuchen</LI>
              </UL>
              <BR>
            </LI>
            <LI>
<?php
  if (!isset($_POST['Submit'])) {
?>  
              <input type="checkbox" name="SelectNewScripts">
              <A HREF="<?php echo $_SERVER['PHP_SELF'] . '?ViewTopic=NewScripts'; ?>">Entwicklung - Grundlagen</A>
<?php
  } else {
?>
              <A HREF="#NewScripts">Entwicklung - Grundlagen</A>
<?php	
  }
?>
              <UL>
                <LI>Verzeichnisstruktur</LI>
                <LI>session.inc</LI>
                <LI>header.inc</LI>
                <LI>footer.inc</LI>
                <LI>config.php</LI>
                <LI>PDFStarter.php</LI>
                <LI>Datenbank-Abstraktionsschicht - ConnectDB.inc</LI>
                <LI>DateFunctions.inc</LI>
                <LI>SQL_CommonFuctions.inc</LI>
              </UL>
              <BR>
            </LI>
            <LI>
<?php
  if (!isset($_POST['Submit'])) {
?>  
              <input type="checkbox" name="SelectStructure">
              <A HREF="<?php echo $_SERVER['PHP_SELF'] . '?ViewTopic=Structure'; ?>">Entwicklung - Struktur</A>
<?php
  } else {
?>
              <A HREF="#Structure">Entwicklung - Struktur</A>
<?php	
  }
?>
              <UL>
                <LI>Kundenaufträge</LI>
                <LI>Preisfestlegung</LI>
                <LI>Lieferangaben und Versandkosten</LI>
                <LI>Kundenaufträge suchen</LI>
                <LI>Faktura</LI>
                <LI>Forderungen / Debitorenkonten</LI>
                <LI>Debitoren-Zahlungseingänge</LI>
                <LI>Debitoren-Ausgleich</LI>
                <LI>Umsatzauswertungen</LI>
                <LI>Bestellungen</LI>
                <LI>Bestand</LI>
                <LI>Bestandsauswertungen</LI>
                <LI>Kreditoren</LI>
                <LI>Kreditorenzahlungen</LI>
              </UL>
              <BR>
            </LI>
            <LI>
<?php
  if (!isset($_POST['Submit'])) {
?>  
              <input type="checkbox" name="SelectContributors">
              <A HREF="<?php echo $_SERVER['PHP_SELF'] . '?ViewTopic=Contributors'; ?>">Mitwirkende - Anerkennungen</A>
<?php
  } else {
?>
              <A HREF="#Contributors">Mitwirkende - Anerkennungen</A>
<?php	
  }
?>
            </LI>
          </UL>
        </td>
      </tr>
    </table>

<?php
}
?>
  </form>
<?php

if (!isset($_GET['ViewTopic'])) {
	$_GET['ViewTopic'] = '';
}

if ($_GET['ViewTopic'] == 'Introduction' OR isset($_POST['SelectIntroduction'])) {
  include('ManualIntroduction.html');
}

if ($_GET['ViewTopic'] == 'Requirements' OR isset($_POST['SelectRequirements'])) {
  include('ManualRequirements.html');
}

if ($_GET['ViewTopic'] == 'GettingStarted' OR isset($_POST['SelectGettingStarted'])) {
  include('ManualGettingStarted.html');
}

if ($_GET['ViewTopic'] == 'SecuritySchema' OR isset($_POST['SelectSecuritySchema'])) {
  include('ManualSecuritySchema.html');
}

if ($_GET['ViewTopic'] == 'CreatingNewSystem' OR isset($_POST['SelectCreatingNewSystem'])) {
  include('ManualCreatingNewSystem.html');
}

if ($_GET['ViewTopic'] == 'SystemConventions' OR isset($_POST['SelectSystemConventions'])) {
  include('ManualSystemConventions.html');
}

if ($_GET['ViewTopic'] == 'Inventory' OR isset($_POST['SelectInventory'])) {
  include('ManualInventory.html');
}

if ($_GET['ViewTopic'] == 'AccountsReceivable' OR isset($_POST['SelectAccountsReceivable'])) {
  include('ManualAccountsReceivable.html');
}

if ($_GET['ViewTopic'] == 'AccountsPayable' OR isset($_POST['SelectAccountsPayable'])) {
  include('ManualAccountsPayable.html');
}

if ($_GET['ViewTopic'] == 'SalesPeople' OR isset($_POST['SelectSalesPeople'])) {
  include('ManualSalesPeople.html');
}

if ($_GET['ViewTopic'] == 'SalesTypes' OR isset($_POST['SelectSalesTypes'])) {
  include('ManualSalesTypes.html');
}

if ($_GET['ViewTopic'] == 'PaymentTerms' OR isset($_POST['SelectPaymentTerms'])) {
  include('ManualPaymentTerms.html');
}

if ($_GET['ViewTopic'] == 'CreditStatus' OR isset($_POST['SelectCreditStatus'])) {
  include('ManualCreditStatus.html');
}

if ($_GET['ViewTopic'] == 'Tax' OR isset($_POST['SelectTax'])) {
  include('ManualTax.html');
}

if ($_GET['ViewTopic'] == 'Prices' OR isset($_POST['SelectPrices'])) {
  include('ManualPrices.html');
}

if ($_GET['ViewTopic'] == 'ARTransactions' OR isset($_POST['SelectARTransactions'])) {
  include('ManualARTransactions.html');
}

if ($_GET['ViewTopic'] == 'ARInquiries' OR isset($_POST['SelectARInquiries'])) {
  include('ManualARInquiries.html');
}

if ($_GET['ViewTopic'] == 'ARReports' OR isset($_POST['SelectARReports'])) {
  include('ManualARReports.html');
}

if ($_GET['ViewTopic'] == 'SalesAnalysis' OR isset($_POST['SelectSalesAnalysis'])) {
  include('ManualSalesAnalysis.html');
}

if ($_GET['ViewTopic'] == 'SalesOrders' OR isset($_POST['SelectSalesOrders'])) {
  include('ManualSalesOrders.html');
}

if ($_GET['ViewTopic'] == 'Shipments' OR isset($_POST['SelectShipments'])) {
  include('ManualShipments.html');
}

if ($_GET['ViewTopic'] == 'Manufacturing' OR isset($_POST['SelectManufacturing'])) {
  include('ManualManufacturing.html');
}

if ($_GET['ViewTopic'] == 'GeneralLedger' OR isset($_POST['SelectGeneralLedger'])) {
  include('ManualGeneralLedger.html');
}

if ($_GET['ViewTopic'] == 'ReportBuilder' OR isset($_POST['SelectReportBuilder'])) {
  include('ManualReportBuilder.html');
}

if ($_GET['ViewTopic'] == 'Multilanguage' OR isset($_POST['SelectMultilanguage'])) {
  include('ManualMultilanguage.html');
}

if ($_GET['ViewTopic'] == 'SpecialUtilities' OR isset($_POST['SelectSpecialUtilities'])) {
  include('ManualSpecialUtilities.html');
}

if ($_GET['ViewTopic'] == 'NewScripts' OR isset($_POST['SelectNewScripts'])) {
  include('ManualNewScripts.html');
}

if ($_GET['ViewTopic'] == 'Structure' OR isset($_POST['SelectStructure'])) {
  include('ManualDevelopmentStructure.html');
}

if ($_GET['ViewTopic'] == 'Contributors' OR isset($_POST['SelectContributors'])) {
  include('ManualContributors.html');
}

include('ManualFooter.html');

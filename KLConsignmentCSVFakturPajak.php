<?php

include('includes/session.php');
include('includes/SQL_CommonFunctions.php');
include('includes/UIGeneralFunctions.php');
include('includes/KLDefines.php');
include('includes/KLUIGeneralFunctions.php');

$Title = _('Export CSV File for Faktur Pajak');

// The default company to Invoice from (PTADU).
if(!isset($_POST['CompanyFrom'])) {
	$_POST['CompanyFrom']='PTADU';
}

// The default company to Invoice to (PTSMH).
if(!isset($_POST['CompanyTo'])) {
	$_POST['CompanyTo']='PTSMH';
}

// default date to invoice is until Yesterday
if (!isset($_POST['EndDate'])){
	$_POST['EndDate'] = DateAdd(Date($_SESSION['DefaultDateFormat']),'d',-1); 
}

// The default draft or Invoice should be draft.
if(!isset($_POST['DraftOrInvoice'])) {
	$_POST['DraftOrInvoice']='DRAFT';
}

if(!isset($_POST['NomorSeriFP'])) {
	$_POST['NomorSeriFP']='0000000000000';
}

if(!isset($_POST['DecimalDigits'])) {
	$_POST['DecimalDigits']=0;
}

if (isset($_POST['submit'])) {
	submit($Title, $_POST['CompanyFrom'], $_POST['CompanyTo'], $_POST['EndDate'], $_POST['DraftOrInvoice'], $_POST['NomorSeriFP'], $_POST['DecimalDigits']);
} else {
	display($Title);
}

//####_SUBMIT_SUBMIT_SUBMIT_SUBMIT_SUBMIT_SUBMIT_SUBMIT_SUBMIT_SUBMIT_SUBMIT_SUBMIT_SUBMIT####
function submit($Title, $CompanyFrom, $CompanyTo, $EndDate, $DraftOrInvoice, $NomorSeriFP, $DecimalDigits) {

	$EndDate = ConvertSQLDate($EndDate);
	$EndDateSQL = FormatDateForSQL($EndDate);

	//initialise no input errors
	$InputError = FALSE;

	//first off validate inputs sensible

	if(!$InputError){
		$SQL = "SELECT klconsignment.stockid,
						stockmaster.description,
					SUM(klconsignment.qty) AS qty,
					SUM(klconsignment.qty * klconsignment.consignmentprice) AS consignmentsale
				FROM klconsignment,stockmaster
				WHERE klconsignment.stockid = stockmaster.stockid
					AND companycode = '" . $CompanyFrom . "'
					AND partnercode = '" . $CompanyTo . "'
					AND (fakturpajakdate = '1000-01-01'
						OR fakturpajakdate = '" . $EndDateSQL . "')
					AND saledate <= '" . $EndDateSQL . "'
				GROUP BY klconsignment.stockid
				ORDER BY klconsignment.stockid";

		$Result = DB_query($SQL);
		if (DB_num_rows($Result) != 0){
			// prepare CSV file
			header("Content-Type: text/csv");
			header("Content-Disposition: attachment; filename=FakturPajak-" . $CompanyFrom . "-". $CompanyTo . "-". $NomorSeriFP . ".csv");
			$output = fopen("php://output", "w");
			$BOL = "";
			$Separator = ";";
			$EOL = "\n";
			
			if ($DraftOrInvoice == 'DRAFT'){
				$DraftLine = $BOL. 'DRAFT LINE: Remove to TEST or select INVOICE to export Faktur Pajak'. $EOL;
				fwrite($output, $DraftLine);
			}

			// Prepare OF Lines for products in the FP, and calculate totals needed in line FK (first one)
			$i = 0;
			$JumlahDPP = 0;
			$JumlahPPN = 0;
			$OFLines = '';
			while ($MyRow = DB_fetch_array($Result)) {

				$LineType = 'OF';
				$KodeObjek = $MyRow['stockid'];
				$Nama = $MyRow['description'];
				$HargaSatuan = round(($MyRow['consignmentsale']/$MyRow['qty']) / ((100 + PPN_PERCENT) / 100),$DecimalDigits);
				$JumlahBarang = round($MyRow['qty'],0);
				$HargaTotal = $HargaSatuan * $JumlahBarang;
				$Diskon = 0;
				$DPP = round($JumlahBarang *($HargaSatuan-$Diskon),$DecimalDigits);
				$PPN = round($JumlahBarang *(($MyRow['consignmentsale']/$MyRow['qty'])-$HargaSatuan),$DecimalDigits);
				$TarifPPNBM = 0;
				$PPNBM = 0;
				
				$JumlahDPP += $DPP;
				$JumlahPPN += $PPN;
				
				$Line = $BOL. 
						$LineType . $Separator . 
						$KodeObjek . $Separator . 
						$Nama . $Separator . 
						$HargaSatuan . $Separator . 
						$JumlahBarang . $Separator . 
						$HargaTotal . $Separator . 
						$Diskon . $Separator . 
						$DPP . $Separator . 
						$PPN . $Separator . 
						$TarifPPNBM . $Separator . 
						$PPNBM . $EOL;
				
				$OFLines .= $Line;
				$i++;
			}
			
			$SQLCompanyTo = "SELECT partnernameinvoice,
								partneraddressjalan,
								partneraddressblok,
								partneraddressnomor,
								partneraddressrt,
								partneraddressrw,
								partneraddresskecamatan,
								partneraddresskelurahan,
								partneraddresskabupaten,
								partneraddresspropinsi,
								partneraddresskodepos,
								partnertelepon,
								partnernpwpinvoice,
								accountppn,
								daysinvoicedue
							FROM klretailpartners
							WHERE partnercode = '" . $CompanyTo . "'";
			$ResultCompanyTo = DB_query($SQLCompanyTo);
			$MyCompanyTo= DB_fetch_array($ResultCompanyTo);


			// Prepare the 1st line (FK) of the file 
			$LineType = 'FK';
			$KDJenisTransaksi = '01';
			$FGPengganti = '0';
			$NomorFaktur = $NomorSeriFP;
			$MasaPajak = substr($EndDate,3,2);
			$TahunPajak = substr($EndDate,-4);
			$TanggalFaktur = $EndDate;
			$CharsToStripFromNPWP = array(".", "-");
			$NPWP = str_replace($CharsToStripFromNPWP,"",$MyCompanyTo['partnernpwpinvoice']); //NPWP number only, no format
			$Nama = $MyCompanyTo['partnernameinvoice'];
			$AlamatLengkap = $MyCompanyTo['partneraddressjalan'];
			if ($MyCompanyTo['partneraddressblok'] != ''){
				$AlamatLengkap .= ' ' . $MyCompanyTo['partneraddressblok'];
			}
			if ($MyCompanyTo['partneraddressnomor'] != ''){
				$AlamatLengkap .= ' ' . $MyCompanyTo['partneraddressnomor'];
			}
			if ($MyCompanyTo['partneraddressrt'] != ''){
				$AlamatLengkap .= ' ' . $MyCompanyTo['partneraddressrt'];
			}
			if ($MyCompanyTo['partneraddressrw'] != ''){
				$AlamatLengkap .= ' ' . $MyCompanyTo['partneraddressrw'];
			}
			if ($MyCompanyTo['partneraddresskecamatan'] != ''){
				$AlamatLengkap .= ' ' . $MyCompanyTo['partneraddresskecamatan'];
			}
			if ($MyCompanyTo['partneraddresskelurahan'] != ''){
				$AlamatLengkap .= ' ' . $MyCompanyTo['partneraddresskelurahan'];
			}
			if ($MyCompanyTo['partneraddresskabupaten'] != ''){
				$AlamatLengkap .= ' ' . $MyCompanyTo['partneraddresskabupaten'];
			}
			if ($MyCompanyTo['partneraddresspropinsi'] != ''){
				$AlamatLengkap .= ' ' . $MyCompanyTo['partneraddresspropinsi'];
			}
			if ($MyCompanyTo['partneraddresskodepos'] != ''){
				$AlamatLengkap .= ' ' . $MyCompanyTo['partneraddresskodepos'];
			}
			$JumlahPPNBM = '0';
			$IDKeteranganTambahan = '';
			$FGUangMuka = '0';
			$UangMukaDPP = '0';
			$UangMukaPPN = '0';
			$UangMukaPPNBM = '0';
			$Referensi = $CompanyFrom . '-' . $CompanyTo . '-' . $EndDateSQL;
			$KodeDokumenPendukung = '';
			
			$FKLine = $BOL . 
					$LineType . $Separator . 
					$KDJenisTransaksi . $Separator . 
					$FGPengganti . $Separator . 
					$NomorFaktur . $Separator . 
					$MasaPajak . $Separator . 
					$TahunPajak . $Separator . 
					$TanggalFaktur . $Separator . 
					$NPWP . $Separator . 
					$Nama . $Separator . 
					$AlamatLengkap . $Separator . 
					$JumlahDPP . $Separator . 
					$JumlahPPN . $Separator . 
					$JumlahPPNBM . $Separator . 
					$IDKeteranganTambahan . $Separator . 
					$FGUangMuka . $Separator . 
					$UangMukaDPP . $Separator . 
					$UangMukaPPN . $Separator . 
					$UangMukaPPNBM . $Separator . 
					$Referensi . $Separator . 
					$KodeDokumenPendukung . $EOL; 

			// Prepare the 2nd line (LT) of the file 

			$LineType = 'LT';
			$CharsToStripFromNPWP = array(".", "-");
			$NPWP = str_replace($CharsToStripFromNPWP,"",$MyCompanyTo['partnernpwpinvoice']); //NPWP number only, no format
			$Nama = $MyCompanyTo['partnernameinvoice'];

			if ($MyCompanyTo['partneraddressjalan'] != ''){
				$Jalan = $MyCompanyTo['partneraddressjalan'];
			} else {
				$Jalan = '-';
			}
			if ($MyCompanyTo['partneraddressblok'] != ''){
				$Blok = $MyCompanyTo['partneraddressblok'];
			} else {
				$Blok = '-';
			}
			if ($MyCompanyTo['partneraddressnomor'] != ''){
				$Nomor = $MyCompanyTo['partneraddressnomor'];
			} else {
				$Nomor = '-';
			}
			if ($MyCompanyTo['partneraddressrt'] != ''){
				$RT = $MyCompanyTo['partneraddressrt'];
			} else {
				$RT = '0';
			}
			if ($MyCompanyTo['partneraddressrw'] != ''){
				$RW = $MyCompanyTo['partneraddressrw'];
			} else {
				$RW = '0';
			}
			if ($MyCompanyTo['partneraddresskecamatan'] != ''){
				$Kecamatan = $MyCompanyTo['partneraddresskecamatan'];
			} else {
				$Kecamatan = '-';
			}
			if ($MyCompanyTo['partneraddresskelurahan'] != ''){
				$Kelurahan = $MyCompanyTo['partneraddresskelurahan'];
			} else {
				$Kelurahan = '-';
			}
			if ($MyCompanyTo['partneraddresskabupaten'] != ''){
				$Kabupaten = $MyCompanyTo['partneraddresskabupaten'];
			} else {
				$Kabupaten = '-';
			}
			if ($MyCompanyTo['partneraddresspropinsi'] != ''){
				$Propinsi = $MyCompanyTo['partneraddresspropinsi'];
			} else {
				$Propinsi = '-';
			}
			if ($MyCompanyTo['partneraddresskodepos'] != ''){
				$KodePos = $MyCompanyTo['partneraddresskodepos'];
			} else {
				$KodePos = '-';
			}
			if ($MyCompanyTo['partnertelepon'] != ''){
				$NomorTelepon = $MyCompanyTo['partnertelepon'];
			} else {
				$NomorTelepon = '-';
			}

			$LTLine = $BOL . 
					$LineType . $Separator . 
					$NPWP . $Separator . 
					$Nama . $Separator . 
					$Jalan . $Separator . 
					$Blok . $Separator . 
					$Nomor . $Separator . 
					$RT . $Separator . 
					$RW . $Separator . 
					$Kecamatan . $Separator . 
					$Kelurahan . $Separator . 
					$Kabupaten . $Separator . 
					$Propinsi . $Separator . 
					$KodePos . $Separator . 
					$NomorTelepon . $EOL; 
					
			if ($DraftOrInvoice == 'INVOICE'){
				DB_Txn_Begin();
				$SQL = "UPDATE klconsignment
						SET fakturpajakdate = '". $EndDateSQL ."'
						WHERE companycode = '" . $CompanyFrom . "'
							AND partnercode = '" . $CompanyTo . "'
							AND fakturpajakdate = '1000-01-01'
							AND saledate <= '" . $EndDateSQL . "'";
				$ErrMsg = 'CRITICAL ERROR! WRITE THIS CODE AND CALL THE OFFICE IMMEDIATELY: ERROR-CONSIGNMENT-00002';		
				$DbgMsg = 'SQL to update klconsignment record: ';
				$Result = DB_query($SQL,$ErrMsg,$DbgMsg,true);
				DB_Txn_Commit();
			}

			// Write lines into actual file
			$InitialLine1 = $BOL. 'FK;KD_JENIS_TRANSAKSI;FG_PENGGANTI;NOMOR_FAKTUR;MASA_PAJAK;TAHUN_PAJAK;TANGGAL_FAKTUR;NPWP;NAMA;ALAMAT_LENGKAP;JUMLAH_DPP;JUMLAH_PPN;JUMLAH_PPNBM;ID_KETERANGAN_TAMBAHAN;FG_UANG_MUKA;UANG_MUKA_DPP;UANG_MUKA_PPN;UANG_MUKA_PPNBM;REFERENSI;KODE_DOKUMEN_PENDUKUNG' . $EOL;
			$InitialLine2 = $BOL. 'LT;NPWP;NAMA;JALAN;BLOK;NOMOR;RT;RW;KECAMATAN;KELURAHAN;KABUPATEN;PROPINSI;KODE_POS;NOMOR_TELEPON' . $EOL;
			$InitialLine3 = $BOL. 'OF;KODE_OBJEK;NAMA;HARGA_SATUAN;JUMLAH_BARANG;HARGA_TOTAL;DISKON;DPP;PPN;TARIF_PPNBM;PPNBM' . $EOL;
			fwrite($output, $InitialLine1);
			fwrite($output, $InitialLine2);
			fwrite($output, $InitialLine3);
			fwrite($output, $FKLine);
//			fwrite($output, $LTLine);
			fwrite($output, $OFLines);
			fclose($output);
			
		}else{
			include('includes/header.php');
			prnMsg('No data to create a Faktur Pajak','warn');
			include('includes/footer.php');
		}
	}else{
		include('includes/header.php');
		echo '<p class="page_title_text">
				<img src="' . $RootPath . '/css/' . $Theme . '/images/magnifier.png" title="' . $PageTitle . '" alt="" />' . ' ' . $PageTitle . 
			'</p>';
		prnMsg($InputErrorMessage, "warn");
		include('includes/footer.php');
	}
} // End of function submit()


function display($Title)
{
	include('includes/header.php');

	echo '<form action="' . htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') . '" method="post">
          <div>
			<br/>';
	echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';

	echo '<p class="page_title_text">
			<img src="' . $RootPath . '/css/' . $_SESSION['Theme'] . '/images/magnifier.png" title="' . $Title . '" alt="" />' . ' ' . $Title . '
		</p>';

	echo '<fieldset>';
	echo FixedField("CompanyFrom", "PTADU", 'From', '');	
	echo FieldToSelectOneRetailPartner("CompanyTo", $_POST['CompanyTo'], _('To'), 'Select the company receiving the Faktur Pajak', '', 1, true, false);
	echo FieldToSelectOneDate('EndDate', $_POST['EndDate'], _('Invoice Consignment Sales until'), '', '', 2, true, false);
	echo FieldToSelectFromTwoOptions('DRAFT', 'Draft', 
									'INVOICE', 'Invoice',
									'DraftOrInvoice', $_POST['DraftOrInvoice'], _('Draft or Invoice'), '', '', 3, true, false);
	echo FieldToSelectOneText("NomorSeriFP", $_POST['NomorSeriFP'], 14, 13, 'Nomor Seri Faktur Pajak', '', '', 4, true, false);
	echo FieldToSelectFromTwoOptions('0', '0 - For e-Faktur', 
									'2', '2 - For Pajak Online',
									'DecimalDigits', $_POST['DecimalDigits'], _('Decimal Digits'), '', '', 3, true, false);
    echo '</fieldset>';

	echo OneButtonCenteredForm("submit", $Title, 6, false, false);

	echo '</form>';
	
	include('includes/footer.php');

} // End of function display()

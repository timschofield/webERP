<?php

/* $Id$ */

     /* -----------------------------------------------------------------------------------------------
	This class was an extension to the FPDF class to use the syntax of the R&OS pdf.php class,
	the syntax that WebERP original reports were written in.
	Due to limitation of R&OS class for foreign character support, this wrapper class was
	written to allow the same code base to use the more functional fpdf.class by Olivier Plathey.

	However, due to limitations of FPDF class for UTF-8 support, now this class inherits from
	the TCPDF class by Nicola Asuni.

	Work to move from FPDF to TCPDF by:
		Javier de Lorenzo-Cáceres <info@civicom.eu>
	----------------------------------------------------------------------------------------------- */
require_once(dirname(__FILE__).'/tcpdf/config/lang/eng.php');
require_once(dirname(__FILE__).'/tcpdf/tcpdf.php');

if (!class_exists('Cpdf', false)) {

class Cpdf extends TCPDF {

	public function __construct($DocOrientation='P', $DocUnits='mm', $DocPaper='A4') {

		parent::__construct($DocOrientation, $DocUnits, $DocPaper, true, 'utf-8', false);

		$this->setuserpdffont();
	}

	protected function setuserpdffont() {

		if (session_id()=='') {
			session_start();
		}

		if (isset($_SESSION['PDFLanguage'])) {

			$userpdflang = $_SESSION['PDFLanguage'];

			switch ($userpdflang) {
				case 0: $userpdffont = 'times';     break;
				case 1: $userpdffont = 'javierjp';  break;
				case 2: $userpdffont = 'javiergb';  break;
				case 3: $userpdffont = 'javierjp';  break;
				case 4: $userpdffont = 'javierjp';  break;
				case 5: $userpdffont = 'javierjp';  break;
				case 6: $userpdffont = 'javierjp';  break;
				case 7: $userpdffont = 'javierjp';  break;
			}

		} else {
			$userpdffont = 'helvetica';
		}

		$this->SetFont($userpdffont, '', 11);
		//     SetFont($family, $style='', $size=0, $fontfile='')
	}


	function newPage() {
/* Javier: 	$this->setPrintHeader(false);  This is not a removed call but added in. */
		$this->AddPage();
	}

	function line($x1,$y1,$x2,$y2,$style=array()) {
// Javier	FPDF::line($x1, $this->h-$y1, $x2, $this->h-$y2);
// Javier: width, color and style might be edited
		TCPDF::Line ($x1,$this->h-$y1,$x2,$this->h-$y2,$style);
	}

	function addText($xb,$yb,$size,$text)//,$angle=0,$wordSpaceAdjust=0)
															{
// Javier	$text = html_entity_decode($text);
		$this->SetFontSize($size);
		$this->Text($xb, $this->h-$yb, $text);
	}

	function addinfo($label, $value) {
		if ($label == 'Creator') {

/* Javier: Some scripts set the creator to be WebERP like this
			$pdf->addInfo('Creator', 'WebERP http://www.weberp.org');
	But the Creator is TCPDF by Nicola Asuni, PDF_CREATOR is defined as 'TCPDF' in tcpdf/config/tcpdfconfig.php
*/ 			$this->SetCreator(PDF_CREATOR);
		}
		if ($label == 'Author') {
/* Javier: Many scripts set the author to be WebERP like this
			$pdf->addInfo('Author', 'WebERP ' . $Version);
	But the Author might be set to be the user or make it constant here.
*/			$this->SetAuthor( $value );
		}
		if ($label == 'Title') {
			$this->SetTitle( $value );
		}
		if ($label == 'Subject') {
			$this->SetSubject( $value );
		}
		if ($label == 'Keywords') {
			$this->SetKeywords( $value );
		}
	}


	function addJpegFromFile($img,$x,$y,$w=0,$h=0){
		$this->Image($img, $x, $this->h-$y-$h, $w, $h);
	}

	/*
	* Next Two functions are adopted from R&OS pdf class
	*/

	/**
	* draw a part of an ellipse
	*/
	function partEllipse($x0,$y0,$astart,$afinish,$r1,$r2=0,$angle=0,$nSeg=8) {
		$this->ellipse($x0,$y0,$r1,$r2,$angle,$nSeg,$astart,$afinish,0);
	}

	/**
	* draw an ellipse
	* note that the part and filled ellipse are just special cases of this function
	*
	* draws an ellipse in the current line style
	* centered at $x0,$y0, radii $r1,$r2
	* if $r2 is not set, then a circle is drawn
	* nSeg is not allowed to be less than 2, as this will simply draw a line (and will even draw a
	* pretty crappy shape at 2, as we are approximating with bezier curves.
	*/
	function ellipse($x0,$y0,$r1,$r2=0,$angle=0,$nSeg=8,$astart=0,$afinish=360,$close=1,$fill=0) {

		if ($r1==0){
			return;
		}
		if ($r2==0){
			$r2=$r1;
		}
		if ($nSeg<2){
			$nSeg=2;
		}

		$astart = deg2rad((float)$astart);
		$afinish = deg2rad((float)$afinish);
		$totalAngle =$afinish-$astart;

		$dt = $totalAngle/$nSeg;
		$dtm = $dt/3;

		if ($angle != 0){
			$a = -1*deg2rad((float)$angle);
			$tmp = "\n q ";
			$tmp .= sprintf('%.3f',cos($a)).' '.sprintf('%.3f',(-1.0*sin($a))).' '.sprintf('%.3f',sin($a)).' '.sprintf('%.3f',cos($a)).' ';
			$tmp .= sprintf('%.3f',$x0).' '.sprintf('%.3f',$y0).' cm';
			$x0=0;
			$y0=0;
		} else {
			$tmp='';
		}

		$t1 = $astart;
		$a0 = $x0+$r1*cos($t1);
		$b0 = $y0+$r2*sin($t1);
		$c0 = -$r1*sin($t1);
		$d0 = $r2*cos($t1);

		$tmp.="\n".sprintf('%.3f',$a0).' '.sprintf('%.3f',$b0).' m ';
		for ($i=1;$i<=$nSeg;$i++){
			// draw this bit of the total curve
			$t1 = $i*$dt+$astart;
			$a1 = $x0+$r1*cos($t1);
			$b1 = $y0+$r2*sin($t1);
			$c1 = -$r1*sin($t1);
			$d1 = $r2*cos($t1);
			$tmp.="\n".sprintf('%.3f',($a0+$c0*$dtm)).' '.sprintf('%.3f',($b0+$d0*$dtm));
			$tmp.= ' '.sprintf('%.3f',($a1-$c1*$dtm)).' '.sprintf('%.3f',($b1-$d1*$dtm)).' '.sprintf('%.3f',$a1).' '.sprintf('%.3f',$b1).' c';
			$a0=$a1;
			$b0=$b1;
			$c0=$c1;
			$d0=$d1;
		}
		if ($fill){
			//$this->objects[$this->currentContents]['c']
			$tmp.=' f';
		} else {
		if ($close){
			$tmp.=' s'; // small 's' signifies closing the path as well
		} else {
			$tmp.=' S';
		}
		}
		if ($angle !=0) {
			$tmp .=' Q';
		}
		$this->_out($tmp);
	}

/* Javier:
	A file's name is needed if we don't want file extension to be .php
	TCPDF has a different behaviour than FPDF, the recursive scripts needs D.
	The admin/user may change I to D to force all pdf to be downloaded or open in a desktop app instead the browser plugin, but not vice-versa.
	The admin/user may change I and D to F to save all pdf in the server for Document Management.
*/

	function OutputI($DocumentFilename = 'Document.pdf') {
		if (($DocumentFilename == null) or ($DocumentFilename == '')) {
			$DocumentFilename = _('Document.pdf');
		}
		$this->Output($DocumentFilename,'I');
	}

	function OutputD($DocumentFilename = 'Document.pdf') {
		if (($DocumentFilename == null) or ($DocumentFilename == '')) {
			$DocumentFilename = _('Document.pdf');
		}
		$this->Output($DocumentFilename,'D');
	}

	function RoundRectangle($XPos, $YPos, $Width, $Height, $Radius) {
		/*from the top right */
		$this->partEllipse($XPos+$Width,$YPos,0,90,$Radius,$Radius);
		/*line to the top left */
		$this->line($XPos+$Width, $YPos+$Radius,$XPos+$Radius, $YPos+$Radius);
		/*Do top left corner */
		$this->partEllipse($XPos+$Radius, $YPos,90,180,$Radius,$Radius);
		/*Do a line to the bottom left corner */
		$this->line($XPos+$Radius, $YPos-$Height-$Radius,$XPos+$Width, $YPos-$Height-$Radius);
		/*Now do the bottom left corner 180 - 270 coming back west*/
		$this->partEllipse($XPos+$Radius, $YPos-$Height,180,270,$Radius,$Radius);
		/*Now a line to the bottom right */
		$this->line($XPos, $YPos-$Height,$XPos, $YPos);
		/*Now do the bottom right corner */
		$this->partEllipse($XPos+$Width, $YPos-$Height,270,360,$Radius,$Radius);
		/*Finally join up to the top right corner where started */
		$this->line($XPos+$Width+$Radius, $YPos-$Height,$XPos+$Width+$Radius, $YPos);
	}

	function Rectangle($XPos, $YPos, $Width, $Height) {
		$this->line($XPos, $YPos, $XPos+$Width, $YPos);
		$this->line($XPos+$Width, $YPos, $XPos+$Width, $YPos-$Height);
		$this->line($XPos+$Width, $YPos-$Height, $XPos, $YPos-$Height);
		$this->line($XPos, $YPos-$Height, $XPos, $YPos);
	}

	function addTextWrap($xb, $yb, $w, $h, $txt, $align='J', $border=0, $fill=0) {
//		$txt = html_entity_decode($txt);
		$this->x = $xb;
		$this->y = $this->h - $yb - $h;

		switch($align) {
			case 'right':
			$align = 'R'; break;
			case 'center':
			$align = 'C'; break;
			default:
			$align = 'L';

		}
		$this->SetFontSize($h);
		$cw=&$this->CurrentFont['cw'];
		if($w==0) {
			$w=$this->w-$this->rMargin-$this->x;
		}
		$wmax=($w-2*$this->cMargin)*1000/$this->FontSize;
		$s=str_replace("\r",'',$txt);
		$s=str_replace("\n",' ',$s);
		$s = trim($s).' ';
		$nb=strlen($s);
		$b=0;
		if ($border) {
			if ($border==1) {
				$border='LTRB';
				$b='LRT';
				$b2='LR';
			} else {
				$b2='';
				if(is_int(strpos($border,'L'))) {
					$b2.='L';
				}
				if(is_int(strpos($border,'R'))) {
					$b2.='R';
				}
				$b=is_int(strpos($border,'T')) ? $b2.'T' : $b2;
			}
		}
		$sep=-1;
		$i=0;
		$l= $ls=0;
		$ns=0;
		while($i<$nb) {

			$c=$s{$i};

			if($c==' ' AND $i>0) {
				$sep=$i;
				$ls=$l;
				$ns++;
			}
			$l+=$cw[$i];
			if($l>$wmax)
			break;
			else
			$i++;
		}
		if($sep==-1) {
			if($i==0) $i++;

			if(isset($this->ws) and $this->ws>0) {
				$this->ws=0;
				$this->_out('0 Tw');
			}
			$sep = $i;
		} else {
			if($align=='J') {
			$this->ws=($ns>1) ? ($wmax-$ls)/1000*$this->FontSize/($ns-1) : 0;
				$this->_out(sprintf('%.3f Tw',$this->ws*$this->k));
			}
		}

		$this->Cell($w,$h,substr($s,0,$sep),$b,2,$align,$fill);
		$this->x=$this->lMargin;

		return substr($s,$sep);
	}

} // end of class
}
?>
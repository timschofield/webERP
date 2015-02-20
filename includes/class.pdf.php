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
require_once(dirname(__FILE__).'/tcpdf/tcpdf.php');

if (!class_exists('Cpdf', false)) {

	class Cpdf extends TCPDF {

		public function __construct($DocOrientation='P', $DocUnits='pt', $DocPaper='A4') {

			parent::__construct($DocOrientation, $DocUnits, $DocPaper, true, 'utf-8', false);

			$this->setuserpdffont();
		}

		protected function setuserpdffont() {

			if (session_id()=='') {
				session_start();
			}

			if (isset($_SESSION['PDFLanguage'])) {

				$UserPdfLang = $_SESSION['PDFLanguage'];

				switch ($UserPdfLang) {
					case 0:
						$UserPdfFont = 'times';
						break;
					case 1:
						$UserPdfFont = 'javierjp';
						break;
					case 2:
						$UserPdfFont = 'javiergb';
						break;
					case 3:
						$UserPdfFont = 'freeserif';
						break;
				}

			} else {
				$UserPdfFont = 'helvetica';
			}

			$this->SetFont($UserPdfFont, '', 11);
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

		function addText($xb,$YPos,$size,$text/*,$angle=0,$wordSpaceAdjust=0*/) {
	// Javier	$text = html_entity_decode($text);
			$this->SetFontSize($size);// Public function SetFontSize() in ~/includes/tcpdf/tcpdf.php.
			$this->Text($xb, $this->h-$YPos, $text);// Public function Text() in ~/includes/tcpdf/tcpdf.php.
		}

//------------------------------------------------------------------------------
public function addTextWrap($xpos, $ypos, $linewidth, $fontsize, $text, $justification='left', $angle=0, $test=0) {
// Adds text to the page, but ensure that it fits within a certain width. If it does not fit then put in as much as possible, splitting at white space or soft hyphen character and return the remainder. Justification can also be specified for the text. It use UTF-8 encoding.
// $xpos = cell horizontal coordinate from page left side to cell left side in dpi (72dpi = 25.4mm).
// $ypos = cell vertical coordinate from page bottom side to cell bottom? side in dpi (72dpi = 25.4mm).
// $linewidth = cell (line) width in dpi (72dpi = 25.4mm).
// $fontsize = font size in dpi (72dpi = 25.4mm).
// $text = text to be split in portion to be add to the page and the remainder to be returned.
// $justification = 'left', 'right', 'center', 'centre' or 'full'.
// Ignores $angle and $test.
// @access public.

if($linewidth == 0) {
	$linewidth = $this->w -$this->rMargin -$xpos;// Line_width = Page_width - Right_margin - Cell_horizontal_coordinate.
}

$this->SetFontSize($fontsize);// Public function SetFontSize() in ~/includes/tcpdf/tcpdf.php.

$text = html_entity_decode($text, ENT_QUOTES, 'UTF-8');// Convert all HTML entities to their applicable characters.

switch($justification) {// Translate from Pdf-Creator to TCPDF.
	case 'left':
		$justification = 'L'; break;
	case 'right':
		$justification = 'R'; break;
	case 'center':
		$justification = 'C'; break;
	case 'centre':
		$justification = 'C'; break;
	case 'full':
		$justification = 'J'; break;
	default:
		$justification = 'L'; break;
}

$this->MultiCell($linewidth, $fontsize, $text, 0, $justification, false, 1, $xpos, $this->h - $ypos - $fontsize);// COMMENT: In "$this->h -$ypos -$fontsize", "-$fontsize" is the difference in the yPos between AddText() and AddTextWarp(). It is better "$this->h -$ypos", but that requires to recode all the pdf generator scripts.
}
//------------------------------------------------------------------------------
function TextWrap($xpos, $ypos, $linewidth, $fontsize, $text, $justification='left', $angle=0, $test=0) {
	// Adds text to the page, but ensure that it fits within a certain width. If it does not fit then put in as much as possible, splitting at white space or soft hyphen character and return the remainder. Justification can also be specified for the text. It use UTF-8 encoding.
	// $xpos = cell horizontal coordinate from page left side to cell left side in dpi (72dpi = 25.4mm).
	// $ypos = cell vertical coordinate from page bottom side to cell bottom? side in dpi (72dpi = 25.4mm).
	// $linewidth = cell (line) width in dpi (72dpi = 25.4mm).
	// $fontsize = font size in dpi (72dpi = 25.4mm).
	// $text = text to be split in portion to be add to the page and the remainder to be returned.
	// $justification = 'left', 'right', 'center', 'centre' or 'full'.
	// Ignores $angle and $test.
	// @access public.
	if($linewidth == 0) {
		$linewidth = $this->w -$this->rMargin -$xpos;// Line_width = Page_width - Right_margin - Cell_horizontal_coordinate.
	}
	$this->SetFontSize($fontsize);// Public function SetFontSize() in ~/includes/tcpdf/tcpdf.php.
	$text = html_entity_decode($text, ENT_QUOTES, 'UTF-8');// Convert all HTML entities to their applicable characters.
	switch($justification) {// Translate from Pdf-Creator to TCPDF.
		case 'left':
			$justification = 'L'; break;
		case 'right':
			$justification = 'R'; break;
		case 'center':
			$justification = 'C'; break;
		case 'centre':
			$justification = 'C'; break;
		case 'full':
			$justification = 'J'; break;
		default:
			$justification = 'L'; break;
	}
	$this->MultiCell($linewidth, $fontsize, $text, 0, $justification, false, 1, $xpos, $this->h - $ypos);
	return $this->h - $this->y;// yPos is the same as in addText().
}
//===========================================================================================

		function addInfo($label, $value) {
			if ($label == 'Creator') {

	/* Javier: Some scripts set the creator to be WebERP like this
				$pdf->addInfo('Creator', 'WebERP http://www.weberp.org');
		But the Creator is TCPDF by Nicola Asuni, PDF_CREATOR is defined as 'TCPDF' in ~/includes/tcpdf/config/tcpdfconfig.php
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

		function addJpegFromFile($img,$XPos,$YPos,$Width=0,$Height=0,$Type=''){
			$this->Image($img, $x=$XPos, $y=$this->h-$YPos/*-$Height*/, $w=$Width, $h=$Height,$type=$Type);
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
		function ellipse($x0,$y0,$r1,$r2=0,$angle=0,$nSeg=8,$astart=0,$afinish=360,$close=1,$fill=0,$fill_color=array(),$nc=8) {

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

		function Rectangle($XPos, $YPos, $Width, $Height) {
			// $XPos, $YPos = Left top position (left line, top line).
			// $Width, $Height = Size (line-to-line).
			$this->line($XPos, $YPos, $XPos+$Width, $YPos);// Top side.
			$this->line($XPos, $YPos-$Height, $XPos+$Width, $YPos-$Height);// Bottom side.
			$this->line($XPos, $YPos, $XPos, $YPos-$Height);// Left side.
			$this->line($XPos+$Width, $YPos, $XPos+$Width, $YPos-$Height);// Right side
		}

		function RoundRectangle($XPos, $YPos, $Width, $Height, $RadiusX, $RadiusY) {
			// $XPos, $YPos = Left top position (left line, top line).
			// $Width, $Height = Size (line-to-line).
			// $RadiusX, $RadiusY = corner radii (horizontal, vertical).
			$this->line($XPos+$RadiusX, $YPos, $XPos+$Width-$RadiusX, $YPos);// Top side.
			$this->line($XPos+$RadiusX, $YPos-$Height, $XPos+$Width-$RadiusX, $YPos-$Height);// Bottom side.
			$this->line($XPos, $YPos-$RadiusY, $XPos, $YPos-$Height+$RadiusY);// Left side.
			$this->line($XPos+$Width, $YPos-$RadiusY, $XPos+$Width, $YPos-$Height+$RadiusY);// Right side.
			$this->partEllipse($XPos+$RadiusX, $YPos-$RadiusY, 90, 180, $RadiusX, $RadiusY);// Top left corner.
			$this->partEllipse($XPos+$Width-$RadiusX, $YPos-$RadiusY, 0, 90, $RadiusX, $RadiusY);// Top right corner.
			$this->partEllipse($XPos+$RadiusX, $YPos-$Height+$RadiusY, 180, 270, $RadiusX, $RadiusY);// Bottom left corner.
			$this->partEllipse($XPos+$Width-$RadiusX, $YPos-$Height+$RadiusY, 270, 360, $RadiusX, $RadiusY);// Bottom right corner.
		}

	} // end of class
} //end if  Cpdf class exists already
?>

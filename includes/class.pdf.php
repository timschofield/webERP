<?php
/*
	This class is an extension to the fpdf class using a syntax that the original reports were written in
	(the R &OS pdf.php class) - due to limitation of this class for foreign character support this wrapper class
	was written to allow the same code base to use the more functional fpdf.class by Olivier Plathey
	
*	Wrapper for use R&OSpdf API with fpdf.org class
*	Janusz Dobrowolski <janusz@iron.from.pl>
*	David Luo <davidluo188@yahoo.com.cn>
	extended for Chinese/Japanese/Korean support by Phil Daintree
*/

define('FPDF_FONTPATH','./fonts/');
include ('fpdf.php');

if ($_SESSION['Language']=='zh_CN'){
	include('FPDF_Chinese.php');
} elseif ($_SESSION['Language']=='ja_JP'){
	include('FPDF_Japanese.php');
}elseif ($_SESSION['Language']=='ko_KR'){
	include('FPDF_Korean.php');
} else {
	class PDF_Language extends FPDF {
	}
}

class Cpdf extends PDF_Language {
	
	function Cpdf($pageSize=array(0,0,612,792)) {
	
		$this->PDF_Language( 'P', 'pt',array($pageSize[2]-$pageSize[0],$pageSize[3]-$pageSize[1]));
		$this->setAutoPageBreak(0);
		$this->AddPage();
		$this->SetLineWidth(1);
		$this->cMargin = 0;
		
		// Next three lines should be here for any fonts genarted with 'makefont' utility
		if ($_SESSION['Language']=='zh_CN'){
			$this->AddBig5Font();
		}elseif ($_SESSION['Language']=='ja_JP'){
			$this->AddSJISFont();
		}elseif ($_SESSION['Language']=='ko_KR'){
			$this->AddUHCFont();
		} else {
			$this->AddFont('helvetica');
			$this->AddFont('helvetica','I');
			$this->AddFont('helvetica','B');
		}
	}
	
	function selectFont($FontName) {
		
		$type = '';
		if(strpos($FontName, 'Oblique')) {
			$type = 'I';
		}
		if(strpos($FontName, 'Bold')) {
			$type = 'B';
		}
		if ($_SESSION['Language']=='zh_CN'){
			$FontName = 'Big5';
		} elseif ($_SESSION['Language']=='ja_JP'){
			$FontName = 'SJIS';
		} elseif ($_SESSION['Language']=='ko_KR'){
			$FontName = 'UHC';
		} else {
			$FontName ='helvetica';
		}
		$this->SetFont($FontName, $type);
	}
	
	function newPage() {
		$this->AddPage();
	}
	
	function line($x1,$y1,$x2,$y2) {
		FPDF::line($x1, $this->h-$y1, $x2, $this->h-$y2);
	}
	
	function addText($xb,$yb,$size,$text)//,$angle=0,$wordSpaceAdjust=0) 
															{
		$text = html_entity_decode($text);
		$this->SetFontSize($size);
		$this->Text($xb, $this->h-$yb, $text);
	}
	
	function addInfo($label,$value){
		if($label=='Title') {
			$this->SetTitle($value);
		} 
		if ($label=='Subject') {
			$this->SetSubject($value);
		}
		if($label=='Creator') {
			// The Creator info in source is not exactly it should be ;) 
			$value = str_replace( "ros.co.nz", "fpdf.org", $value );
			$value = str_replace( "R&OS", "", $value );
			$this->SetCreator( $value );
		}
		if($label=='Author') {
			$this->SetAuthor($value);
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
		if ($angle !=0){
		$tmp .=' Q';
		}
		$this->_out($tmp);
	}
	
	function Stream() {
	$this->Output('','I');
	}
	
	function addTextWrap($xb, $yb, $w, $h, $txt, $align='J', $border=0, $fill=0) {
		$txt = html_entity_decode($txt);
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
			$l+=$cw[$c];
			if($l>$wmax)
			break;
			else 
			$i++;
		}
		if($sep==-1) {
			if($i==0) $i++;
			
			if($this->ws>0) {
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

?>
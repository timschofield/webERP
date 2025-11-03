<?
/*
 * PHP-Barcode 0.4
 
 * PHP-Barcode generates
 *   - Barcode-Images using libgd2 (png, jpg, gif)
 *   - HTML-Images (using 1x1 pixel and html-table)
 *   - silly Text-Barcodes
 *
 * PHP-Barcode encodes using
 *   - a built-in EAN-13/ISBN Encoder
 *   - genbarcode (by Folke Ashberg), a command line
 *     barcode-encoder which uses GNU-Barcode
 *     genbarcode can encode EAN-13, EAN-8, UPC, ISBN, 39, 128(a,b,c),
 *     I25, 128RAW, CBR, MSI, PLS
 *     genbarcode is available at www.ashberg.de/php-barcode 
 
 * (C) 2001,2002,2003,2004,2011 by Folke Ashberg <folke@ashberg.de>
 
 * The newest version can be found at http://www.ashberg.de/php-barcode
 
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 675 Mass Ave, Cambridge, MA 02139, USA.

 */


/* CONFIGURATION */

/* ******************************************************************** */
/*                          COLORS                                      */
/* ******************************************************************** */
$bar_color=Array(0,0,0);
$bg_color=Array(255,255,255);
$Text_color=Array(0,0,0);


/* ******************************************************************** */
/*                          FONT FILE                                   */
/* ******************************************************************** */
/* location the the ttf-font */

//$font_loc=dirname(__FILE__)."/"."FreeSansBold.ttf";

/* ******************************************************************** */
/*                          GENBARCODE                                  */
/* ******************************************************************** */
/* location of 'genbarcode'
 * leave blank if you don't have them :(
 * genbarcode is needed to render encodings other than EAN-12/EAN-13/ISBN
 */
//$genbarcode_loc="c:\winnt\genbarcode.exe";


/* CONFIGURATION ENDS HERE */

//require("encode_bars.php"); /* build-in encoders */

/* 
 * barcode_outimage(text, bars [, scale [, mode [, total_y [, space ]]]] )
 *
 *  Outputs an image using libgd
 *
 *    text   : the text-line (<position>:<font-size>:<character> ...)
 *    bars   : where to place the bars  (<space-width><bar-width><space-width><bar-width>...)
 *    scale  : scale factor ( 1 < scale < unlimited (scale 50 will produce
 *                                                   5400x300 pixels when
 *                                                   using EAN-13!!!))
 *    mode   : png,gif,jpg, depending on libgd ! (default='png')
 *    total_y: the total height of the image ( default: scale * 60 )
 *    space  : space
 *             default:
 *		$space[top]   = 2 * $scale;
 *		$space[bottom]= 2 * $scale;
 *		$space[left]  = 2 * $scale;
 *		$space[right] = 2 * $scale;
 */


function barcode_outimage($Text, $bars, $scale = 1, $mode = "png",
	    $Total_y = 0, $space = ''){
    global $bar_color, $bg_color, $Text_color;    /* set defaults */
    if ($scale<1) $scale=2;
    $Total_y=(int)($Total_y);
    if ($Total_y<1) $Total_y=(int)$scale * 60;
    if (!$space)
      $space=array('top'=>2*$scale,'bottom'=>2*$scale,'left'=>2*$scale,'right'=>2*$scale);
    
    /* count total width */
    $xpos=0;
    $width=true;
    for ($i=0;$i<strlen($bars);$i++){
	$val=strtolower($bars[$i]);
	if ($width){
	    $xpos+=$val*$scale;
	    $width=false;
	    continue;
	}
	if (preg_match("#[a-z]#", $val)){
	    /* tall bar */
	    $val=ord($val)-ord('a')+1;
	} 
	$xpos+=$val*$scale;
	$width=true;
    }

    /* allocate the image */
    $Total_x=( $xpos )+$space['right']+$space['right'];
    $xpos=$space['left'];
    if (!function_exists("imagecreate")){
	print "You don't have the gd2 extension enabled<BR>\n";
	print "<BR>\n";
	print "<BR>\n";
	print "Short HOWTO<BR>\n";
	print "<BR>\n";
	print "Debian: # apt-get install php4-gd2<BR>\n";
	print "<BR>\n";
	print "SuSE: ask YaST<BR>\n";
	print "<BR>\n";
	print "OpenBSD: # pkg_add /path/php4-gd-4.X.X.tgz (read output, you have to enable it)<BR>\n";
	print "<BR>\n";
	print "Windows: Download the PHP zip package from <A href=\"http://www.php.net/downloads.php\">php.net</A>, NOT the windows-installer, unzip the php_gd2.dll to C:\PHP (this is the default install dir) and uncomment 'extension=php_gd2.dll' in C:\WINNT\php.ini (or where ever your os is installed)<BR>\n";
	print "<BR>\n";
	print "<BR>\n";
	print "The author of php-barcode will give not support on this topic!<BR>\n";
	print "<BR>\n";
	print "<BR>\n";
	print "<A HREF=\"http://www.ashberg.de/php-barcode/\">Folke Ashberg's OpenSource PHP-Barcode</A><BR>\n";
	return "";
    }
    $im=imagecreate($Total_x, $Total_y);
    /* create two images */
    $col_bg=ImageColorAllocate($im,$bg_color[0],$bg_color[1],$bg_color[2]);
    $col_bar=ImageColorAllocate($im,$bar_color[0],$bar_color[1],$bar_color[2]);
    $col_text=ImageColorAllocate($im,$Text_color[0],$Text_color[1],$Text_color[2]);
    $height=round($Total_y-($scale*10));
    $height2=round($Total_y-$space['bottom']);


    /* paint the bars */
    $width=true;
    for ($i=0;$i<strlen($bars);$i++){
	$val=strtolower($bars[$i]);
	if ($width){
	    $xpos+=$val*$scale;
	    $width=false;
	    continue;
	}
	if (preg_match("#[a-z]#", $val)){
	    /* tall bar */
	    $val=ord($val)-ord('a')+1;
	    $h=$height2;
	} else $h=$height;
	imagefilledrectangle($im, $xpos, $space['top'], $xpos+($val*$scale)-1, $h, $col_bar);
	$xpos+=$val*$scale;
	$width=true;
    }
    
    }

    /* output the image */
    $mode=strtolower($mode);
    if ($mode=='jpg' || $mode=='jpeg'){
	header("Content-Type: image/jpeg; name=\"barcode.jpg\"");
	imagejpeg($im);
    } elseif ($mode=='gif'){
	header("Content-Type: image/gif; name=\"barcode.gif\"");
	imagegif ($im);
    } else {
	header("Content-Type: image/png; name=\"barcode.png\"");
	imagepng($im);
    }

}

/*
 * barcode_outtext(code, bars)
 *
 *  Returns (!) a barcode as plain-text
 *  ATTENTION: this is very silly!
 *
 *    text   : the text-line (<position>:<font-size>:<character> ...)
 *    bars   : where to place the bars  (<space-width><bar-width><space-width><bar-width>...)
 */

function barcode_outtext($Code,$bars){
    $width=true;
    $xpos=$heigh2=0;
    $bar_line="";
    for ($i=0;$i<strlen($bars);$i++){
	$val=strtolower($bars[$i]);
	if ($width){
	    $xpos+=$val;
	    $width=false;
	    for ($a=0;$a<$val;$a++) $bar_line.="-";
	    continue;
	}
	if (preg_match("#[a-z]#", $val)){
	    $val=ord($val)-ord('a')+1;
	    $h=$heigh2;
	    for ($a=0;$a<$val;$a++) $bar_line.="I";
	} else for ($a=0;$a<$val;$a++) $bar_line.="#";
	$xpos+=$val;
	$width=true;
    }
    return $bar_line;
}

/* 
 * barcode_outhtml(text, bars [, scale [, total_y [, space ]]] )
 *
 *  returns(!) HTML-Code for barcode-image using html-code (using a table and with black.png and white.png)
 *
 *    text   : the text-line (<position>:<font-size>:<character> ...)
 *    bars   : where to place the bars  (<space-width><bar-width><space-width><bar-width>...)
 *    scale  : scale factor ( 1 < scale < unlimited (scale 50 will produce
 *                                                   5400x300 pixels when
 *                                                   using EAN-13!!!))
 *    total_y: the total height of the image ( default: scale * 60 )
 *    space  : space
 *             default:
 *		$space[top]   = 2 * $scale;
 *		$space[bottom]= 2 * $scale;
 *		$space[left]  = 2 * $scale;
 *		$space[right] = 2 * $scale;
 */



function barcode_outhtml($Code, $bars, $scale = 1, $Total_y = 0, $space = ''){
    /* set defaults */
    $Total_y=(int)($Total_y);
    if ($scale<1) $scale=2;
    if ($Total_y<1) $Total_y=(int)$scale * 60;
    if (!$space)
      $space=array('top'=>2*$scale,'bottom'=>2*$scale,'left'=>2*$scale,'right'=>2*$scale);


    /* generate html-code */
    $height=round($Total_y-($scale*10));
    $height2=round($Total_y)-$space['bottom'];
    $out=
      '<table border=0 cellspacing=0 cellpadding=0 bgcolor="white">'."\n".
      '<tr><td><img src="white.png" height="'.$space['top'].'" width="1" alt=" "></td></tr>'."\n".
      '<tr><td>'."\n".
      '<img src="white.png" height="'.$height2.'" width="'.$space['left'].'" alt="#"/>';
    
    $width=true;
    for ($i=0;$i<strlen($bars);$i++){
	$val=strtolower($bars[$i]);
	if ($width){
	    $w=$val*$scale;
	    if ($w>0) $out.='<img src="white.png" height="'.$Total_y.'" width="'.$w.'" align="top" alt="" />';
	    $width=false;
	    continue;
	}
	if (preg_match("#[a-z]#", $val)){
	    //hoher strich
	    $val=ord($val)-ord('a')+1;
	    $h=$height2;
	} else $h=$height;
	$w=$val*$scale;
	if ($w>0) $out.='<img src="black.png" height="'.$h.'" width="'.$w.'" align="top" />';
	$width=true;
    }
    $out.=
      '<img src="white.png" height="'.$height2.'" width=".'.$space['right'].'" />'.
      '</td></tr>'."\n".
      '<tr><td><img src="white.png" height="'.$space['bottom'].'" width="1"></td></tr>'."\n".
      '</table>'."\n";
    //for ($i=0;$i<strlen($bars);$i+=2) print $Line[$i]."<B>".$Line[$i+1]."</B>&nbsp;";
    return $out;
}


/* barcode_encode_genbarcode(code, encoding)
 *   encodes $Code with $encoding using genbarcode
 *
 *   return:
 *    array[encoding] : the encoding which has been used
 *    array[bars]     : the bars
 *    array[text]     : text-positioning info
 */
function barcode_encode_genbarcode($Code,$encoding){
    global $genbarcode_loc;
    /* delete EAN-13 checksum */
    if (preg_match("#^ean$#i", $encoding) && strlen($Code)==13) $Code=substr($Code,0,12);
    if (!$encoding) $encoding="ANY";
    $encoding=preg_replace("#[|\\\\]#", "_", $encoding);
    $Code=preg_replace("#[|\\\\]#", "_", $Code);
    $cmd=$genbarcode_loc." "
	.escapeshellarg($Code)." "
	.escapeshellarg(strtoupper($encoding))."";
    //print "'$cmd'<BR>\n";
    $fp=popen($cmd, "r");
    if ($fp){
	$bars=fgets($fp, 1024);
	$Text=fgets($fp, 1024);
	$encoding=fgets($fp, 1024);
	pclose($fp);
    } else return false;
    $ret=array(
		"encoding" => trim($encoding),
		"bars" => trim($bars),
		"text" => trim($Text)
	      );
    if (!$ret['encoding']) return false;
    if (!$ret['bars']) return false;
    if (!$ret['text']) return false;
    return $ret;
}

/* barcode_encode(code, encoding)
 *   encodes $Code with $encoding using genbarcode OR built-in encoder
 *   if you don't have genbarcode only EAN-13/ISBN is possible
 *
 * You can use the following encodings (when you have genbarcode):
 *   ANY    choose best-fit (default)
 *   EAN    8 or 13 EAN-Code
 *   UPC    12-digit EAN 
 *   ISBN   isbn numbers (still EAN-13) 
 *   39     code 39 
 *   128    code 128 (a,b,c: autoselection) 
 *   128C   code 128 (compact form for digits)
 *   128B   code 128, full printable ascii 
 *   I25    interleaved 2 of 5 (only digits) 
 *   128RAW Raw code 128 (by Leonid A. Broukhis)
 *   CBR    Codabar (by Leonid A. Broukhis) 
 *   MSI    MSI (by Leonid A. Broukhis) 
 *   PLS    Plessey (by Leonid A. Broukhis)
 * 
 *   return:
 *    array[encoding] : the encoding which has been used
 *    array[bars]     : the bars
 *    array[text]     : text-positioning info
 */
function barcode_encode($Code,$encoding){
    global $genbarcode_loc;
    if (
		((preg_match("#^ean$#i", $encoding)
		 && ( strlen($Code)==12 || strlen($Code)==13)))
		 
		|| (($encoding) && (preg_match("#^isbn$#i", $encoding))
		 && (( strlen($Code)==9 || strlen($Code)==10) ||
		 (((preg_match("#^978#", $Code) && strlen($Code)==12) ||
		  (strlen($Code)==13)))))

		|| (( !isset($encoding) || !$encoding || (preg_match("#^ANY$#i", $encoding) ))
		 && (preg_match("#^[0-9]{12,13}$#", $Code)))
	      
		){
	/* use built-in EAN-Encoder */
	$bars=barcode_encode_ean($Code, $encoding);
    } elseif (file_exists($genbarcode_loc)){
	/* use genbarcode */
	$bars=barcode_encode_genbarcode($Code, $encoding);
    } else {
	print "php-barcode needs an external programm for encodings other then EAN/ISBN<BR>\n";
	print "<ul>\n";
	print "<li>download gnu-barcode from <a href=\"http://www.gnu.org/software/barcode/\">www.gnu.org/software/barcode/</a></li>\n";
	print "<li>compile and install them</li>\n";
	print "<li>download genbarcode from <a href=\"http://www.ashberg.de/php-barcode/\">www.ashberg.de/php-barcode/</a></li>\n";
	print "<li>compile and install them</li>\n";
	print "<li>specify path to genbarcode in php-barcode.php</li>\n";
	print "</ul>\n";
	print "<br />\n";
	print "<a href=\"http://www.ashberg.de/php-barcode/\">Folke Ashberg's OpenSource PHP-Barcode</a><br />\n";
	return false;
    }
    return $bars;
}

/* barcode_print(code [, encoding [, scale [, mode ]]] );
 *
 *  encodes and prints a barcode
 *
 *   return:
 *    array[encoding] : the encoding which has been used
 *    array[bars]     : the bars
 *    array[text]     : text-positioning info
 */


function barcode_print($Code, $encoding="ANY", $scale = 2 ,$mode = "png" ){
    $bars=barcode_encode($Code,$encoding);
    if (!$bars) return;
    if (!$mode) $mode="png";
    if (preg_match("#^(text|txt|plain)$#i", $mode)) print barcode_outtext($bars['text'],$bars['bars']);
    elseif (preg_match("#^(html|htm)$#i", $mode)) print barcode_outhtml($bars['text'],$bars['bars'], $scale,0, 0);
    else barcode_outimage($bars['text'],$bars['bars'],$scale, $mode);
    return $bars;
}

/*

 * Built-In Encoders
 * Part of PHP-Barcode 0.4
 
 * (C) 2001,2002,2003,2004,2011 by Folke Ashberg <folke@ashberg.de>
 
 * The newest version can be found at http://www.ashberg.de/php-barcode
 
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 675 Mass Ave, Cambridge, MA 02139, USA.

 */

function barcode_gen_ean_sum($ean){
  $even=true; $esum=0; $osum=0;
  for ($i=strlen($ean)-1;$i>=0;$i--){
	if ($even) $esum+=$ean[$i];	else $osum+=$ean[$i];
	$even=!$even;
  }
  return (10-((3*$esum+$osum)%10))%10;
}

/* barcode_encode_ean(code [, encoding])
 *   encodes $ean with EAN-13 using builtin functions
 *
 *   return:
 *    array[encoding] : the encoding which has been used (EAN-13)
 *    array[bars]     : the bars
 *    array[text]     : text-positioning info
 */
function barcode_encode_ean($ean, $encoding = "EAN-13"){
    $digits=array(3211,2221,2122,1411,1132,1231,1114,1312,1213,3112);
    $mirror=array("000000","001011","001101","001110","010011","011001","011100","010101","010110","011010");
    $guards=array("9a1a","1a1a1","a1a");

    $ean=trim($ean);
    if (preg_match("#[^0-9]#i",$ean)){
	return array("text"=>"Invalid EAN-Code");
    }
    $encoding=strtoupper($encoding);
    if ($encoding=="ISBN"){
	if (!preg_match("#^978#", $ean)) $ean="978".$ean;
    }
    if (preg_match("#^978#", $ean)) $encoding="ISBN";
    if (strlen($ean)<12 || strlen($ean)>13){
	return array("text"=>"Invalid $encoding Code (must have 12/13 numbers)");
    }

    $ean=substr($ean,0,12);
    $eansum=barcode_gen_ean_sum($ean);
    $ean.=$eansum;
    $Line=$guards[0];
    for ($i=1;$i<13;$i++){
	$str=$digits[$ean[$i]];
	if ($i<7 && $mirror[$ean[0]][$i-1]==1) $Line.=strrev($str); else $Line.=$str;
	if ($i==6) $Line.=$guards[1];
    }
    $Line.=$guards[2];

    /* create text */
    $pos=0;
    $Text="";
    for ($a=0;$a<13;$a++){
	if ($a>0) $Text.=" ";
	$Text.="$pos:12:{$ean[$a]}";
	if ($a==0) $pos+=12;
	elseif ($a==6) $pos+=12;
	else $pos+=7;
    }

    return array(
		"encoding" => $encoding,
		"bars" => $Line,
		"text" => $Text
		);
}
?>

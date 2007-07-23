<?php
$PageSecurity = 1;
include ('includes/session.inc');
/*
http://127.0.0.1/~brink/webERP/GetStockImage.php
?automake=1&width=81&height=74&stockid=&textcolor=FFFFF0&bevel=3&text=aa&bgcolor=007F00

automake - if specified allows autocreate images
stockid - if not specified it produces a blank image if set to empty string uses default stock image
bgcolor   - Background color specified in hex
textcolor - Forground color specified in hex
transcolor - Transparent color specified in hex
width - if specified scales image to width
height - if specified scales image to height
transparent - if specfied uses bgcolor as transparent unless specified
text - if specified override stockid to be printed on image
bevel - if specified draws a drop down bevel

*/
// Color decode function
function DecodeBgColor( $colorstr ) {
	if ( $colorstr[0] == '#' ) {
		$colorstr = substr($colorstr,1,strlen($colorstr));
	}
	$red = 0;
	if(strlen($colorstr) > 1) {
		$red = hexdec(substr($colorstr,0,2));
		$colorstr = substr($colorstr,2,strlen($colorstr));
	}
	$green = 0;
	if(strlen($colorstr) > 1) {
		$green = hexdec(substr($colorstr,0,2));
		$colorstr = substr($colorstr,2,strlen($colorstr));
	}
	$blue = 0;
	if(strlen($colorstr) > 1) {
		$blue = hexdec(substr($colorstr,0,2));
		$colorstr = substr($colorstr,2,strlen($colorstr));
	}
	if(strlen($colorstr) > 1) {
		$alpha = hexdec(substr($colorstr,0,2));
		$colorstr = substr($colorstr,2,strlen($colorstr));
	}
	if ( isset($alpha) )
		return array('red' => $red, 'green' => $green, 'blue' => $blue, 'alpha' => $alpha );
	else
		return array('red' => $red, 'green' => $green, 'blue' => $blue );
}

if (!function_exists('imagecreatefrompng')){
	$title = _('Image Manipulation Script Problem');
	include('includes/header.inc');
	prnMsg(_('This script requires the gd image functions to be available to php - this needs to be enabled in your server php version before this script can be used'),'error');
	include('includes/footer.inc');
	exit;
}
$defaultimage = 'webERPsmall.png';

// FOR APACHE
if ( $_SERVER['PATH_TRANSLATED'][0] == '/' OR $_SERVER['PATH_TRANSLATED'][0]=='') {
	//*nix
	$pathsep = '/';
} else {
	//Windows
	$pathsep = "\\";
}
$filepath =  $_SESSION['part_pics_dir'] . $pathsep;

$stockid = trim(strtoupper($_GET['StockID']));
if( isset($_GET['bgcolor']) )
	$bgcolor = $_GET['bgcolor'];
if( isset($_GET['textcolor']) )
	$textcolor = $_GET['textcolor'];
if( isset($_GET['width']) )
	$width = $_GET['width'];
if( isset($_GET['height']) )
	$height = $_GET['height'];
if( isset($_GET['scale']) )
	$scale = $_GET['scale'];
if( isset($_GET['automake']) )
	$automake = $_GET['automake'];
if( isset($_GET['transparent'])) {
	$doTrans = true;
}
if( isset($_GET['text']) ) {
	$text = $_GET['text'];
}
if( isset($_GET['transcolor'])) {
	$doTrans = true;
	$transcolor = $_GET['transcolor'];
}
if( isset($_GET['bevel']) ) {
	$bevel = $_GET['bevel'];
}
if( isset($_GET['useblank']) ) {
	$useblank = $_GET['useblank'];
}
if( isset($_GET['fontsize']) ) {
	$fontsize = $_GET['fontsize'];
} else {
	$fontsize = 3;
}
if( isset($_GET['notextbg']) ) {
	$notextbg = true;
}





// Extension requirements and Stock ID Isolation
if($stockid == '') {
	$stockid = $defaultimage;
	$blanktext = true;
}

$i = strrpos($stockid,'.');
if( $i === false )
  	$type = 'png';
else {
	$type   = strtolower(substr($stockid,$i+1,strlen($stockid)));
	$stockid = substr($stockid,0,$i);
	if($blanktext && !isset($text))
		$text = '';
}
$style = $type;
$functype = $type;
if ( $style == 'jpg' ) {
	$style = 'jpeg';
	$functype = 'jpeg';
}

$tmpfilename = $filepath.$stockid;
// First check for an image this is not the type requested
if ( file_exists($tmpfilename.'.jpg') ) {
	$filename = $stockid.'.jpg';
	$isjpeg = true;
} elseif (file_exists($tmpfilename.'.jpeg')) {
	$filename = $stockid.'.jpeg';
	$isjpeg = true;
} elseif (file_exists($tmpfilename.'.png')) {
	$filename = $stockid.'.png';
	$isjpeg = false;
} else {
	$filename = $defaultimage;
	$isjpeg = $defaultisjpeg;
}
if( !$automake && !isset($filename) ) {
		$title = _('Stock Image Retrieval ....');
		include('includes/header.inc');
		prnMsg( _('The Image could not be retrieved because it does not exist'), 'error');
		echo '<BR><A HREF="' .$rootpath .'/index.php?' . SID . '">'.  _('Back to the menu'). '</A>';
		include('includes/footer.inc');
		exit;
}

/*
	$title = _('Stock Image Retrieval ....');
	include('includes/header.inc');
	echo 'The image ' . $filename . ' using functype ' . $functype
	 	. '<BR> The tmpfilename = ' . $tmpfilename . '<BR> The temppath = ' . $filepath . '<BR>The stockid = ' . $stockid . '<BR> filepath . stockid .jpg = ' . $filepath . $stockid .'.jpg<BR> The result of file_exists($filepath . $stockid .jpg) =' . file_exists($filepath . $stockid .'.jpg')
		. '<BR>filepath = ' . $filepath
		. '<BR>rootpath = ' . $rootpath;
	include('includes/footer.inc');
	exit;
*/



// See if we need to automake this image
if( $automake && !isset($filename) || $useblank ) {
	// Have we got height and width specs
	if( !isset($width) )
		$width = 64;
	if( !isset($height) )
		$height = 64;
	// Have we got a background color
	$im = imagecreate($width, $height);
	if( isset($bgcolor) )
		$bgcolor = DecodeBgColor( $bgcolor );
	else
		$bgcolor = DecodeBgColor( '#7F7F7F' );
	if( !isset($bgcolor['alpha']) ) {
		$ixbgcolor = imagecolorallocate($im,
			$bgcolor['red'],$bgcolor['green'],$bgcolor['blue']);
	} else {
		$ixbgcolor = imagecolorallocatealpha($im,
			$bgcolor['red'],$bgcolor['green'],$bgcolor['blue'],$bgcolor['alpha']);
	}
	// Have we got a text color
	if( isset($textcolor) )
		$textcolor = DecodeBgColor( $textcolor );
	else
		$textcolor = DecodeBgColor( '#000000' );
	if( !isset($textcolor['alpha']) ) {
		$ixtextcolor = imagecolorallocate($im,
			$textcolor['red'],$textcolor['green'],$textcolor['blue']);
	} else {
		$ixtextcolor = imagecolorallocatealpha($im,
			$textcolor['red'],$textcolor['green'],$textcolor['blue'],$textcolor['alpha']);
	}
	// Have we got transparency requirements
	if( isset($transcolor) ) {
		$transcolor = DecodeBgColor( $transcolor );
		if( $transcolor != $bgcolor ) {
			if( !isset($textcolor['alpha']) ) {
				$ixtranscolor = imagecolorallocate($im,
					$transcolor['red'],$transcolor['green'],$transcolor['blue']);
			} else {
				$ixtranscolor = imagecolorallocatealpha($im,
					$transcolor['red'],$transcolor['green'],$transcolor['blue'],$transcolor['alpha']);
			}
		} else {
			$ixtranscolor = $ixbgcolor;
		}
	}
	imagefill($im, 0, 0, $ixbgcolor );

	if( $doTrans ) {
		imagecolortransparent($im, $ixtranscolor);
	}

	if(!isset($text))
		$text = $stockid;
	if(strlen($text) > 0 ) {
		$fw = imagefontwidth($fontsize);
		$fh = imagefontheight($fontsize);
		$fy = (imagesy($im) - ($fh)) / 2;
		$fyh = $fy + $fh - 1;
		$textwidth = $fw * strlen($text);
		$px = (imagesx($im) - $textwidth) / 2;
		if (!$notextbg)
			imagefilledrectangle($im,$px,$fy,imagesx($im)-($px+1),$fyh, $ixtextbgcolor );
		imagestring($im, $fontsize, $px, $fy, $text, $ixtextcolor);
	}

} else {
	$tmpfilename = $filepath.$filename;
	if( $isjpeg ) {
		$im = imagecreatefromjpeg($tmpfilename);
	} else {
		$im = imagecreatefrompng($tmpfilename);
	}
	// Have we got a background color
	if( isset($bgcolor) )
		$bgcolor = DecodeBgColor( $bgcolor );
	else
		$bgcolor = DecodeBgColor( '#7F7F7F' );
	if( !isset($bgcolor['alpha']) ) {
		$ixbgcolor = imagecolorallocate($im,
			$bgcolor['red'],$bgcolor['green'],$bgcolor['blue']);
	} else {
		$ixbgcolor = imagecolorallocatealpha($im,
			$bgcolor['red'],$bgcolor['green'],$bgcolor['blue'],$bgcolor['alpha']);
	}
	// Have we got a text color
	if( isset($textcolor) )
		$textcolor = DecodeBgColor( $textcolor );
	else
		$textcolor = DecodeBgColor( '#000000' );
	if( !isset($textcolor['alpha']) ) {
		$ixtextcolor = imagecolorallocate($im,
			$textcolor['red'],$textcolor['green'],$textcolor['blue']);
	} else {
		$ixtextcolor = imagecolorallocatealpha($im,
			$textcolor['red'],$textcolor['green'],$textcolor['blue'],$textcolor['alpha']);
	}
	$sw = imagesx($im);
	$sh = imagesy($im);
	if ( isset($width) && ($width != $sw) || isset($height) && ($height != $sh)) {
		if( !isset($width) )
			$width = imagesx($im);
		if( !isset($height) )
			$height = imagesy($im);
		$tmpim = imagecreatetruecolor($width, $height);
		imagealphablending ( $tmpim, true);
		imagecopyresized($tmpim,$im,0,0,0,0,$width, $height, imagesx($im), imagesy($im) );
		imagedestroy($im);
		$im = $tmpim;
		unset($tmpim);

		if( !isset($bgcolor['alpha']) ) {
			$ixbgcolor = imagecolorallocate($im,
				$bgcolor['red'],$bgcolor['green'],$bgcolor['blue']);
		} else {
			$ixbgcolor = imagecolorallocatealpha($im,
				$bgcolor['red'],$bgcolor['green'],$bgcolor['blue'],$bgcolor['alpha']);
		}
		if( !isset($textcolor['alpha']) ) {
			$ixtextcolor = imagecolorallocate($im,
				$textcolor['red'],$textcolor['green'],$textcolor['blue']);
		} else {
			$ixtextcolor = imagecolorallocatealpha($im,
				$textcolor['red'],$textcolor['green'],$textcolor['blue'],$textcolor['alpha']);
		}
		//imagealphablending ( $im, false);
	}
	// Have we got transparency requirements
	if( isset($transcolor) ) {
		$transcolor = DecodeBgColor( $transcolor );
		if( $transcolor != $bgcolor ) {
			if( !isset($textcolor['alpha']) ) {
				$ixtranscolor = imagecolorallocate($im,
					$transcolor['red'],$transcolor['green'],$transcolor['blue']);
			} else {
				$ixtranscolor = imagecolorallocatealpha($im,
					$transcolor['red'],$transcolor['green'],$transcolor['blue'],$transcolor['alpha']);
			}
		} else {
			$ixtranscolor = $ixbgcolor;
		}
	}
	if( $doTrans ) {
		imagecolortransparent($im, $ixtranscolor);
	}
	if( $doTrans )
		$ixtextbgcolor = $ixtranscolor;
	else
	    $ixtextbgcolor = $ixbgcolor;
//	$ixtextbgcolor = imagecolorallocatealpha($im,
//		0,0,0,0);
	if(!isset($text))
		$text = $stockid;
	if(strlen($text) > 0 ) {
		$fw = imagefontwidth($fontsize);
		$fh = imagefontheight($fontsize);
		$fy = imagesy($im) - ($fh);
		$fyh = imagesy($im) - 1;
		$textwidth = $fw * strlen($text);
		$px = (imagesx($im) - $textwidth) / 2;
		if (!$notextbg)
			imagefilledrectangle($im,$px,$fy,imagesx($im)-($px+1),$fyh, $ixtextbgcolor );
		imagestring($im, $fontsize, $px, $fy, $text, $ixtextcolor);
	}
}
// Do we need to bevel
if( $bevel ) {
	$drgray = imagecolorallocate($im,63,63,63);
	$silver = imagecolorallocate($im,127,127,127);
	$white = imagecolorallocate($im,255,255,255);
	imageline($im, 0,0,imagesx($im)-1, 0, $drgray); // top
	imageline($im, 0,1,imagesx($im)-1, 1, $drgray); // top
	imageline($im, 1,0,1, imagesy($im)-1, $drgray); // left
	imageline($im, 0,0,0, imagesy($im)-1, $drgray); // left
	imageline($im, 0,imagesy($im)-1,imagesx($im)-1, imagesy($im)-1, $silver); // bottom
	imageline($im, imagesx($im)-1,0,imagesx($im)-1, imagesy($im)-1, $silver); // right
}
// Set up headers
header('Content-Disposition: filename='.$stockid.'.'.$type);
header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
header('Pragma: public');
header('Content-type: image/'.$style);
// Which function should we use jpeg or png
//images
$func = 'image'.$functype;
// AND send image
$func($im);
// Destroy image
imagedestroy($im);
?>
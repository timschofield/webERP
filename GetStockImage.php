<?php

require(__DIR__ . '/includes/session.php');

//global $RootPath;

/*
http://127.0.0.1/~brink/webERP/GetStockImage.php
?automake=1&width=81&height=74&stockid=&textcolor=FFFFF0&bevel=3&text=aa&bgcolor=007F00

automake - if specified allows auto creation of images
stockid - if not specified it produces a blank image if set to empty string uses default stock image
bgcolor - Background color specified in hex
textcolor - Foreground color specified in hex
transcolor - Transparent color specified in hex
width - if specified scales image to width
height - if specified scales image to height
transparent - if specified uses bgcolor as transparent unless specified
text - if specified override stockid to be printed on image
bevel - if specified draws a drop-down bevel
*/

// Color decode function
function DecodeBgColor( $ColourStr ) {
	if ( $ColourStr[0] == '#' ) {
		$ColourStr = mb_substr($ColourStr, 1, mb_strlen($ColourStr));
	}
	$Red = 0;
	if (mb_strlen($ColourStr) > 1) {
		$Red = hexdec(mb_substr($ColourStr, 0, 2));
		$ColourStr = mb_substr($ColourStr, 2, mb_strlen($ColourStr));
	}
	$Green = 0;
	if (mb_strlen($ColourStr) > 1) {
		$Green = hexdec(mb_substr($ColourStr, 0, 2));
		$ColourStr = mb_substr($ColourStr, 2, mb_strlen($ColourStr));
	}
	$Blue = 0;
	if (mb_strlen($ColourStr) > 1) {
		$Blue = hexdec(mb_substr($ColourStr, 0, 2));
		$ColourStr = mb_substr($ColourStr, 2, mb_strlen($ColourStr));
	}
	if (mb_strlen($ColourStr) > 1) {
		$Alpha = hexdec(mb_substr($ColourStr, 0, 2));
		$ColourStr = mb_substr($ColourStr, 2, mb_strlen($ColourStr));
	}
	if ( isset($Alpha) )
		return array('red' => $Red, 'green' => $Green, 'blue' => $Blue, 'alpha' => $Alpha );
	else
		return array('red' => $Red, 'green' => $Green, 'blue' => $Blue );
}

$DefaultImage = 'webERPsmall.png';
$DefaultIsJpeg = false;

$FilePath = $_SESSION['part_pics_dir'] . '/';

$StockID = trim(mb_strtoupper(@$_GET['StockID']));
if ( isset($_GET['bgcolor']) )
	$BackgroundColour = $_GET['bgcolor'];
if ( isset($_GET['textcolor']) )
	$TextColour = $_GET['textcolor'];
if ( isset($_GET['width']) )
	$width = $_GET['width'];
if ( isset($_GET['height']) )
	$height = $_GET['height'];
if ( isset($_GET['scale']) )
	$scale = $_GET['scale'];
if ( isset($_GET['automake']) )
	$automake = $_GET['automake'];
if ( isset($_GET['transparent'])) {
	$doTrans = true;
}
if ( isset($_GET['text']) ) {
	$Text = $_GET['text'];
}
if ( isset($_GET['transcolor'])) {
	$doTrans = true;
	$TranspColour = $_GET['transcolor'];
} else {
	$doTrans = false;
}
if ( isset($_GET['bevel']) ) {
	$bevel = $_GET['bevel'];
} else {
	$bevel = false;
}
if ( isset($_GET['fontsize']) ) {
	$fontsize = $_GET['fontsize'];
} else {
	$fontsize = 3;
}
if ( isset($_GET['notextbg']) ) {
	$notextbg = true;
} else {
	$notextbg = false;
}

// Extension requirements and Stock ID Isolation
if ($StockID == '') {
	$StockID = $DefaultImage;
	$blanktext = true;
}

$i = strrpos($StockID, '.');
if ( $i === false )
	$Type = 'png';
else {
	$Type = strtolower(mb_substr($StockID, $i+1));
	$StockID = mb_substr($StockID, 0, $i);
	if ($blanktext && !isset($Text))
		$Text = '';
}

// First check for an existing image - this is not necessarily the type requested
$tmpFileName = $FilePath.$StockID;
if ( file_exists($tmpFileName.'.jpg') ) {
	$FileName = $StockID.'.jpg';
	$IsJpeg = true;
} elseif (file_exists($tmpFileName.'.jpeg')) {
	$FileName = $StockID.'.jpeg';
	$IsJpeg = true;
} elseif (file_exists($tmpFileName.'.png')) {
	$FileName = $StockID.'.png';
	$IsJpeg = false;
} else {
	$FileName = $DefaultImage;
	$IsJpeg = $DefaultIsJpeg;
}

if ( !extension_loaded('gd') OR (!$automake && !isset($FileName)) ) {
	$Title = __('Stock Image Retrieval ....');
	include('includes/header.php');
	if ( !extension_loaded('gd') ) {
		prnMsg( __('The Image could not be retrieved because the GD extension is missing'), 'error');
	} else {
		prnMsg( __('The Image could not be retrieved because it does not exist'), 'error');
	}
	echo '<br /><a href="' .$RootPath .'/index.php">' . __('Back to the menu'). '</a>';
	include('includes/footer.php');
	exit();
}

/// @todo validate that type is supported by gd, see `imagetypes()`. This also avoids users being able to specify an unexpected function name to call
$style = $Type;
$functype = $Type;
if ( $style == 'jpg' ) {
	$style = 'jpeg';
	$functype = 'jpeg';
}

// See if we need to automake this image
if ( $automake AND !isset($FileName) ) {
	// Have we got height and width specs
	if ( !isset($width) )
		$width = 64;
	if ( !isset($height) )
		$height = 64;
	// Have we got a background color
	$im = imagecreate($width, $height);
	if ( isset($BackgroundColour) )
		$BackgroundColour = DecodeBgColor( $BackgroundColour );
	else
		$BackgroundColour = DecodeBgColor( '#7F7F7F' );
	if ( !isset($BackgroundColour['alpha']) ) {
		$ixbgcolor = imagecolorallocate($im,
			$BackgroundColour['red'], $BackgroundColour['green'], $BackgroundColour['blue']);
	} else {
		$ixbgcolor = imagecolorallocatealpha($im,
			$BackgroundColour['red'], $BackgroundColour['green'], $BackgroundColour['blue'], $BackgroundColour['alpha']);
	}
	// Have we got a text color
	if ( isset($TextColour) )
		$TextColour = DecodeBgColor( $TextColour );
	else
		$TextColour = DecodeBgColor( '#000000' );
	if ( !isset($TextColour['alpha']) ) {
		$ixtextcolor = imagecolorallocate($im,
			$TextColour['red'], $TextColour['green'], $TextColour['blue']);
	} else {
		$ixtextcolor = imagecolorallocatealpha($im,
			$TextColour['red'], $TextColour['green'], $TextColour['blue'], $TextColour['alpha']);
	}
	// Have we got transparency requirements
	if ( isset($TranspColour) ) {
		$TranspColour = DecodeBgColor( $TranspColour );
		if ( $TranspColour != $BackgroundColour ) {
			if ( !isset($TextColour['alpha']) ) {
				$ixtranscolor = imagecolorallocate($im,
					$TranspColour['red'], $TranspColour['green'], $TranspColour['blue']);
			} else {
				$ixtranscolor = imagecolorallocatealpha($im,
					$TranspColour['red'], $TranspColour['green'], $TranspColour['blue'], $TranspColour['alpha']);
			}
		} else {
			$ixtranscolor = $ixbgcolor;
		}
	}
	imagefill($im, 0, 0, $ixbgcolor );

	if ( $doTrans ) {
		imagecolortransparent($im, $ixtranscolor);
	}

	if ($_SESSION['ShowStockidOnImages'] == 1) {
		if (!isset($Text))
			$Text = $StockID;
		if (mb_strlen($Text) > 0 ) {
			$fw = imagefontwidth($fontsize);
			$fh = imagefontheight($fontsize);
			$fy = (imagesy($im) - ($fh)) / 2;
			$fyh = $fy + $fh - 1;
			$Textwidth = $fw * mb_strlen($Text);
			$px = (imagesx($im) - $Textwidth) / 2;
			if (!$notextbg)
				imagefilledrectangle($im, $px, $fy, imagesx($im)-($px+1), $fyh, $ixbgcolor );
			imagestring($im, $fontsize, $px, $fy, $Text, $ixtextcolor);
		}
	}

} else {
	$tmpFileName = $FilePath.$FileName;
	if ( $IsJpeg ) {
		$im = imagecreatefromjpeg($tmpFileName);
	} else {
		$im = imagecreatefrompng($tmpFileName);
	}
	// Have we got a background color
	if ( isset($BackgroundColour) )
		$BackgroundColour = DecodeBgColor( $BackgroundColour );
	else
		$BackgroundColour = DecodeBgColor( '#7F7F7F' );
	if ( !isset($BackgroundColour['alpha']) ) {
		$ixbgcolor = imagecolorallocate($im,
			$BackgroundColour['red'], $BackgroundColour['green'], $BackgroundColour['blue']);
	} else {
		$ixbgcolor = imagecolorallocatealpha($im,
			$BackgroundColour['red'], $BackgroundColour['green'], $BackgroundColour['blue'], $BackgroundColour['alpha']);
	}
	// Have we got a text color
	if ( isset($TextColour) )
		$TextColour = DecodeBgColor( $TextColour );
	else
		$TextColour = DecodeBgColor( '#000000' );
	if ( !isset($TextColour['alpha']) ) {
		$ixtextcolor = imagecolorallocate($im,
			$TextColour['red'], $TextColour['green'], $TextColour['blue']);
	} else {
		$ixtextcolor = imagecolorallocatealpha($im,
			$TextColour['red'], $TextColour['green'], $TextColour['blue'], $TextColour['alpha']);
	}

	$sw = imagesx($im);
	$sh = imagesy($im);
	if ( isset($width) AND ($width != $sw) OR isset($height) AND ($height != $sh)) {
		if ( !isset($width) )
			$width = imagesx($im);
		if ( !isset($height) )
			$height = imagesy($im);
			$resize_scale = min($width/$sw, $height/$sh);
		if ($resize_scale < 1) {
			$resize_new_width = floor($resize_scale*$sw);
			$resize_new_height = floor($resize_scale*$sh);
		} else {
			$resize_new_width = $sw;
			$resize_new_height = $sh;
		}

		$tmpim = imagecreatetruecolor($resize_new_width, $resize_new_height);
		imagealphablending ( $tmpim, true);
		imagecopyresampled($tmpim, $im, 0, 0, 0, 0, $resize_new_width, $resize_new_height, $sw, $sh );
		imagedestroy($im);
		$im = $tmpim;
		unset($tmpim);

		if ( !isset($BackgroundColour['alpha']) ) {
			$ixbgcolor = imagecolorallocate($im,
				$BackgroundColour['red'], $BackgroundColour['green'], $BackgroundColour['blue']);
		} else {
			$ixbgcolor = imagecolorallocatealpha($im,
				$BackgroundColour['red'], $BackgroundColour['green'], $BackgroundColour['blue'], $BackgroundColour['alpha']);
		}
		if ( !isset($TextColour['alpha']) ) {
			$ixtextcolor = imagecolorallocate($im,
				$TextColour['red'], $TextColour['green'], $TextColour['blue']);
		} else {
			$ixtextcolor = imagecolorallocatealpha($im,
				$TextColour['red'], $TextColour['green'], $TextColour['blue'], $TextColour['alpha']);
		}
		//imagealphablending ( $im, false);
	}
	// Have we got transparency requirements
	if ( isset($TranspColour) ) {
		$TranspColour = DecodeBgColor( $TranspColour );
		if ( $TranspColour != $BackgroundColour ) {
			if ( !isset($TextColour['alpha']) ) {
				$ixtranscolor = imagecolorallocate($im,
					$TranspColour['red'], $TranspColour['green'], $TranspColour['blue']);
			} else {
				$ixtranscolor = imagecolorallocatealpha($im,
					$TranspColour['red'], $TranspColour['green'], $TranspColour['blue'], $TranspColour['alpha']);
			}
		} else {
			$ixtranscolor = $ixbgcolor;
		}
	}
	if ( $doTrans ) {
		imagecolortransparent($im, $ixtranscolor);
	}
	if ( $doTrans )
		$ixtextbgcolor = $ixtranscolor;
	else
		$ixtextbgcolor = $ixbgcolor;
//	$ixtextbgcolor = imagecolorallocatealpha($im, 0, 0, 0, 0);
	if ($_SESSION['ShowStockidOnImages'] == 1) {
		if (!isset($Text))
			$Text = $StockID;
		if (mb_strlen($Text) > 0 ) {
			$fw = imagefontwidth($fontsize);
			$fh = imagefontheight($fontsize);
			$fy = imagesy($im) - ($fh);
			$fyh = imagesy($im) - 1;
			$Textwidth = $fw * mb_strlen($Text);
			$px = (imagesx($im) - $Textwidth) / 2;
			if (!$notextbg)
				imagefilledrectangle($im, $px, $fy, imagesx($im)-($px+1), $fyh, $ixtextbgcolor );
				imagestring($im, $fontsize, $px, $fy, $Text, $ixtextcolor);
		}
	}
}

// Do we need to bevel
if ( $bevel ) {
	$drgray = imagecolorallocate($im, 63, 63, 63);
	$silver = imagecolorallocate($im, 127, 127, 127);
	$white = imagecolorallocate($im, 255, 255, 255);
	imageline($im, 0, 0, imagesx($im)-1, 0, $drgray); // top
	imageline($im, 0, 1, imagesx($im)-1, 1, $drgray); // top
	imageline($im, 1, 0, 1, imagesy($im)-1, $drgray); // left
	imageline($im, 0, 0, 0, imagesy($im)-1, $drgray); // left
	imageline($im, 0, imagesy($im)-1, imagesx($im)-1, imagesy($im)-1, $silver); // bottom
	imageline($im, imagesx($im)-1, 0, imagesx($im)-1, imagesy($im)-1, $silver); // right
}

/// @todo cache on disk the final generated image

// Set up headers
header('Content-Disposition: filename='.$StockID.'.'.$Type);
header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
/// @todo review caching header
header('Pragma: public');
header('Content-type: image/'.$style);
// Which function should we use jpeg or png
//images
$func = 'image'.$functype;
// AND send image
$func($im);
// Destroy image
imagedestroy($im);

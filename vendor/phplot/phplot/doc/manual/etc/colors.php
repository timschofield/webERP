<?php
# Make a PHPlot Color Chart
# 2005-01-29 L Bayuk for the PHPlot Reference Manual

# === Configuration Data ===

# Margins:
$left_margin = 5;
$right_margin = 5;
$top_margin = 30;    # Including space for title
$bottom_margin = 5;

# Width and height of color boxes:
$box_width = 80;
$box_height = 40;

# Spacing between corresponding corner of boxes:
$dx = 100;
$dy = 60;

# Number of color boxes across:
$columns = 6;

# Box label font (1-5):
$font_label = 3;
# Title font size (1-5):
$font_title = 5;

# Text for the title:
$title = "PHPlot Default Colors";

# === Color Map, copied from phplot.php: ===

$colormap = array(
    'white'          => array(255, 255, 255),
    'snow'           => array(255, 250, 250),
    'PeachPuff'      => array(255, 218, 185),
    'ivory'          => array(255, 255, 240),
    'lavender'       => array(230, 230, 250),
    'black'          => array(  0,   0,   0),
    'DimGrey'        => array(105, 105, 105),
    'gray'           => array(190, 190, 190),
    'grey'           => array(190, 190, 190),
    'navy'           => array(  0,   0, 128),
    'SlateBlue'      => array(106,  90, 205),
    'blue'           => array(  0,   0, 255),
    'SkyBlue'        => array(135, 206, 235),
    'cyan'           => array(  0, 255, 255),
    'DarkGreen'      => array(  0, 100,   0),
    'green'          => array(  0, 255,   0),
    'YellowGreen'    => array(154, 205,  50),
    'yellow'         => array(255, 255,   0),
    'orange'         => array(255, 165,   0),
    'gold'           => array(255, 215,   0),
    'peru'           => array(205, 133,  63),
    'beige'          => array(245, 245, 220),
    'wheat'          => array(245, 222, 179),
    'tan'            => array(210, 180, 140),
    'brown'          => array(165,  42,  42),
    'salmon'         => array(250, 128, 114),
    'red'            => array(255,   0,   0),
    'pink'           => array(255, 192, 203),
    'maroon'         => array(176,  48,  96),
    'magenta'        => array(255,   0, 255),
    'violet'         => array(238, 130, 238),
    'plum'           => array(221, 160, 221),
    'orchid'         => array(218, 112, 214),
    'purple'         => array(160,  32, 240),
    'azure1'         => array(240, 255, 255),
    'aquamarine1'    => array(127, 255, 212)
    );

# === End of configuration data ===

# Sort by color name and count:
ksort($colormap);
$nc = count($colormap);

# Compute image size based on above data:
# There are N-1 spaces for N boxes across, but count N spaces down to leave
# room for the labels below the last row.
$image_width = ($columns - 1) * $dx + $box_width + $left_margin + $right_margin;
$rows = (int)(($nc + $columns - 1) / $columns);
$image_height = $rows * $dy + $top_margin + $bottom_margin;

# Usable boundaries inside margins:
$x_left = $left_margin;
$x_right = $image_width - $right_margin;
$y_top = $top_margin;
$y_bottom = $image_height - $bottom_margin;

# Make an image:
$g = imagecreate($image_width, $image_height);

# Allocate all the colors:
$bgnd = imagecolorallocate($g, 255, 255, 255); # Background
$fgnd = imagecolorallocate($g, 0, 0, 0); # Used for borders
$cval = array();
$cname = array();
foreach ($colormap as $colorname => $rgb) {
  $cval[] = imagecolorallocate($g, $rgb[0], $rgb[1], $rgb[2]);
  $cname[] = $colorname;
}

# Draw the boxes:
$x1 = $x_left;
$y1 = $y_top;
$y2 = $y1 + $box_height;
for ($i = 0; $i < $nc; $i++) {

  if (($x2 = $x1 + $box_width) > $x_right) {
    $x1 = $x_left;
    $x2 = $x1 + $box_width;
    $y1 += $dy;
    if (($y2 = $y1 + $box_height) > $y_bottom) break; # Doesn't fit
  }

  # Draw a filled box with a black outline:
  imagefilledrectangle($g, $x1, $y1, $x2, $y2, $cval[$i]);
  imagerectangle($g, $x1, $y1, $x2, $y2, $fgnd);

  # Label the color box below:
  imagestring($g, $font_label, $x1, $y2, $cname[$i], $fgnd);
  $x1 += $dx;
}

# Add a centered title:
$x = (int)(($image_width - imagefontwidth($font_title) * strlen($title)) / 2);
if ($x < $x_left) $x = $x_left;
imagestring($g, $font_title, $x, 2, $title, $fgnd);

# Output the image:
imagepng($g);

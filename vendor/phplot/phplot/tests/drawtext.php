<?php

# Unit test: PHPlot DrawText function - Alignment.
# This is a unit test. It does not draw a graph, but does produce an image file.
# The image shows text with all 9 cases of alignment:
#    (top|center|bottom)+(right|center|left)
# Notes: After PHPlot-5.0.4, top/bottom alignment were switched. They
# were wrong before.
# Unlike other test scripts, this is more dependent on PHPlot internals,
# such as the font[] structure.
# This script is also used to create documentation for text alignment.
# It uses a debug callback to get the orthogonal bounding box.
#
# Note: This could be called alone, but isn't. There is another script
# which sets no parameters and calls this. That was only so the filenames
# would be consistent. The other script is: drawtext-gd_000d_1l.php
# (for: GD text, 0 degrees, 1 line of text).
#
# Font info is in this configuration file:
require 'config.php';
#
# This is a parameterized test. Other scripts can set $tp and then include
# this script. The parameters are shown in the defaults array below:
if (!isset($tp)) $tp = array();
$tp = array_merge(array(
  'use_ttf' => False,        # True to use TTF text, False for GD text
  'gd_font_size' => 3,       # GD font of size
  'ttfont' => $phplot_test_ttfonts['sans'],  # TrueType font filename
  'ttfdir' => $phplot_test_ttfdir,   # TrueType font directory
  'ttfsize' => 10,    # TrueType font size in points
  'textangle' => 0,   # Text angle. GD fonts only allow 0 and 90.
  'nlines' => 1,      # Number of lines of text (>= 1)
  'line_spacing' => NULL,  # Line spacing, or NULL for default.
        ), $tp);

require_once 'phplot.php';

# Callback function which gets the bounding box:
function draw_bbox($img, $color, $px, $py, $width, $height)
{
    imagerectangle($img, (int)$px, (int)$py, (int)$px+(int)$width, (int)$py+(int)$height, $color);
}

# Image size:
$width = 500;
$height = 500;

# Testing framework: Create an object and initialize.
$p = new Phplot\Phplot\phplot($width, $height);

# Local variable with image resource:
$img = $p->img;

$angle = $tp['textangle'];
$nlines = $tp['nlines'];
if ($nlines == 1) $plural = ''; else $plural = 's';

# Font setup:
# For PHPlot-5.0.5 and earlier, this would directly set its own font[] array
# with members that we knew phplot drawtext() uses. But this changed after
# PHPlot-5.0.5, when mixed text types are supported. The goal now is to use
# PHPlot's own SetFont() methods to set up the array, then peak inside to
# see whether it is the old one or new one font array name, and copy it out.
#
if ($tp['use_ttf']) {
  # Setup for TrueType fonts:
  $fsize = $tp['ttfsize'];
  $p->SetTTFPath($tp['ttfdir']);
  $p->SetDefaultTTFont($tp['ttfont']);
  $title = "TrueType text, $nlines line$plural, $fsize points, at $angle degrees";
  $p->SetFont('generic', '', $fsize);
} else {
  # Setup for GD fonts:
  $fsize = $tp['gd_font_size'];
  $title = "GD Text, $nlines line$plural, size $fsize, at $angle degrees";
  $p->SetFont('generic', $fsize);
}
# Now grab the font array, checking for old vs new name:
if (isset($p->generic_font)) {
  $font = $p->generic_font;
} elseif (isset($p->fonts['generic'])) {
  $font = $p->fonts['generic'];
} else {
  die("drawtext.php failure: Unable to determine font class variable name\n");
}

# Assign colors:
$black = imagecolorallocate($img, 0, 0, 0);
$white = imagecolorallocate($img, 0xff, 0xff, 0xff);
$red = imagecolorallocate($img, 0xff, 0, 0);
$green = imagecolorallocate($img, 0, 0xcc, 0);
$blue = imagecolorallocate($img, 0, 0, 0xff);

# Register the callback for drawing the bounding box:
$p->SetCallback('debug_textbox', 'draw_bbox', $green);

# Array indexes are factors to offset the point from center.
# Note: These are for the corrected top/bottom meaning after PHPlot-5.0.4
$v_opts = array(1=>'top', 0=>'center', -1=>'bottom');
$h_opts = array(1=>'left', 0=>'center', -1=>'right');

$cx = $width / 2;
$cy = $height / 2;
$x_off = $height / 4.0;
$y_off = $width / 4.0;

# Because we don't let PHPlot finish drawing, we need to set the background.
imagefilledrectangle($img, 0, 0, $width, $height, $white);

if (isset($tp['line_spacing']))
    $p->SetLineSpacing($tp['line_spacing']);

# Draw a title:
ImageString($img, '3', 5, 5, $title, $black);

# This is the rest of the text after the first line:
$addl_lines = '';
$addl_text = '';
for ($i = 2; $i <= $nlines; $i++) {
    $addl_lines .= "\nLine $i" . $addl_text;
    $addl_text .= " E";
}

# Loop over all text alignment cases:
foreach ($v_opts as $v_step => $v) {
  foreach ($h_opts as $h_step => $h) {
    $text = "[$h, $v]" . $addl_lines;
    $x = $cx + $h_step * $x_off;
    $y = $cy + $v_step * $y_off;

    $p->DrawText($font, $angle, $x, $y, $blue, $text, $h, $v);

    ImageFilledEllipse($img, $x, $y, 8, 8, $red);
  }
}

# Don't have PHPlot output a graph: just do it ourselves:
imagepng($img);

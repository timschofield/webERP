<?php

# PHPlot Test: DrawMessage() message image, case 4
# This is a parameterized test. See the script named at the bottom for details.
$options = array(
  'force_print' => False,
  'reset_font' => False,
  'text_wrap' => 40,
);
$text = 'No automatic output, use EncodeImage for result at end, '
      . 'do not reset fonts, use a TrueType font for text, '
      . 'wrap text at 40 due to larger font.';
$extra_chars = 100;
$noprint = True;
$use_ttfont = True;

require 'drawmessage.php';

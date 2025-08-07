<?php

# PHPlot Test: DrawMessage() message image, case 5
# This is a parameterized test. See the script named at the bottom for details.
$options = array(
  'reset_font' => False,
  'text_wrap' => False,
);
$text = "Text wrapping False, GD large font, wrap with\n"
      . " embedded newlines.\n"
      . "This is the 3rd line.\n"
      . "This is the 4th line and it is very long and will not fit but won't"
      . " be wrapped anyway because text wrapping is turned off.";
$use_gdfont = TRUE;

require 'drawmessage.php';

<?php

# PHPlot Test: DrawMessage() message image, case 7
# This is a parameterized test. See the script named at the bottom for details.
$options = array(
  'draw_background' => True, // No effect
  'draw_border' => True,
  'reset_font' => False,
  'text_color' => 'blue',
);
$text = 'Blue text, border, TT font, text is too large to fit in the window'
      . ' and gets clipped off but top left is visible.';
$extra_chars = 8000;
$use_ttfont = True;
$set_border = True;

require 'drawmessage.php';

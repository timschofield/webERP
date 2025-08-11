<?php

# PHPlot Test: DrawMessage() message image, case 2
# This is a parameterized test. See the script named at the bottom for details.
$options = array(
  'draw_background' => FALSE,
  'draw_border' => TRUE,
  'reset_font' => False,
  'text_color' => '#0099CC',
);
$text = 'Image border, text #0099cc, fonts not reset but not changed either.';
$set_border = True;

require 'drawmessage.php';

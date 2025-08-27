<?php

# PHPlot Test: DrawMessage() message image, case 1
# This is a parameterized test. See the script named at the bottom for details.
$options = array(
  'draw_background' => TRUE,
  'text_color' => 'red',
);
$text = 'Red text, background color.';
$extra_chars = 100;
$set_bgcolor = True;

require 'drawmessage.php';

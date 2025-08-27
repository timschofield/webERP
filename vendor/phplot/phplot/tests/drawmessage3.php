<?php

# PHPlot Test: DrawMessage() message image, case 3
# This is a parameterized test. See the script named at the bottom for details.
$options = array(
  'draw_background' => TRUE,
  'draw_border' => TRUE,
  'text_color' => array(0,150,0),
  'reset_font' => False, // No effect without font change
  'force_print' => False, // No effect without SetPrintImage(False)
);
$text = 'Text color (0,150,0), border, background image, '
       . 'force_print False but SetPrintImage not disabled, '
       . 'do not reset fonts, change font default to large GD.';
$set_border = True;
$set_bgimage = True;
$use_gdfont = True;

require 'drawmessage.php';

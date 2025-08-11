<?php

# PHPlot Test: DrawMessage() message image - master script
require_once 'config.php'; // For TT fonts
require_once 'phplot.php';

/*
This is a parameterized test. Other scripts can set the following variables
then include this script.
  $options - Options array for DrawMessage(). Empty (all defaults) if unset.
  $text - Message text. Should include a description of the test.
  $extra_chars - Append approximately this many extra characters to $text,
      for testing word wrap.
  $noprint - If True, run the test using EncodeImage()
  $use_ttfont - If True, set a TrueType font
  $use_gdfont - If True, set a non-default GD font size
  $set_border - If True, set a non-default border
  $set_bgcolor - If True, set a non-default background color
  $set_bgimage - If True, set a background image

The values and defaults for $options in DrawMessage are:
  Setting:         Default: Description:
  draw_background  FALSE    Draw image background (color or image, as set)
  draw_border      FALSE    Draw image border (as set with SetBorder*())
  force_print      TRUE     Ignore SetPrintImage() setting and always output
  reset_font       TRUE     Reset fonts (to avoid possible TTF error)
  text_color       ''       If not empty, text color specification
  text_wrap        TRUE     Wrap the message text with wordwrap()
  wrap_width       75       Width in characters for wordwrap()

*/

# Set defaults:
if (!isset($options)) $options = array();
$message = 'DrawMessage() test: ';
if (empty($text)) $message .= 'All defaults';
else $message .= $text;
if (!empty($extra_chars))
    $message .= str_repeat(' Test', (int)($extra_chars / 5));

$p = new Phplot\Phplot\phplot(800, 600);
if (!empty($use_gdfont)) {
    $p->SetFontGD('generic', 5);
} elseif (!empty($use_ttfont)) {
    $p->SetTTFPath($phplot_test_ttfdir);
    $p->SetFontTTF('generic', $phplot_test_ttfonts['serifitalic'], 14);
}

if (!empty($set_bgcolor)) $p->SetBackgroundColor('yellow');
if (!empty($set_bgimage)) $p->SetBgImage('images/bubbles.png', 'tile');
if (!empty($set_border)) {
    $p->SetImageBorderWidth(3);
    $p->SetImageBorderColor('red');
    $p->SetImageBorderType('raised');
}
if (!empty($noprint)) $p->SetPrintImage(False);
$p->DrawMessage($message, $options);
if (!empty($noprint)) echo $p->EncodeImage('raw');

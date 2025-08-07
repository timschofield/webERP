<?php

# X label size and angle test - baseline
# Bug report 945439 - "x_tick_label_height not set correctly"
# 2006-11-16
# 2007-09-11 Fix font path and name
# 2007-11-14 Fix: Don't call setfont for TTF with NULL size.
# 2007-11-28 Use config.php for font information.
require_once 'config.php';

# This is a parameterized test. Other scripts can set $tp and then include
# this script. The parameters are shown in the defaults array below:
if (!isset($tp)) $tp = array();
$tp = array_merge(array(
  'title' => 'Long X labels',
  'suffix' => "",           # Title part 2
  'MaxLen' => 35,           # Max label length (int * 5)
  'angle' => 90,            # Label text angle, in degrees
  'TTF' => False,           # If True, use TrueType fonts, else GD fonts
  'FontSize' => NULL,       # GD font size/TTF font point size, NULL to omit
  'FontName' => 'sans',     # Font index name - see config.php
        ), $tp);
require_once 'phplot.php';

$data = array();
for ($len = 5; $len <= $tp['MaxLen']; $len += 5)
  $data[] = array(str_repeat('Label', $len/5), $len);

$p = new Phplot\Phplot\phplot(800, 600);
$p->SetTitle($tp['title'] . $tp['suffix']);
$p->SetDataType('text-data');
$p->SetDataValues($data);
# Fix Y ticks
$p->SetPlotAreaWorld(NULL, 0, NULL, NULL);
$p->SetYTickIncrement(5);

# Font:
if ($tp['TTF']) {
  # Using TrueType fonts: Set path and default font.
  $p->SetTTFPath($phplot_test_ttfdir);
  $font = $phplot_test_ttfonts[$tp['FontName']];
  $p->SetDefaultTTFont($font);
  # Now select label font with size (if supplied):
  if (empty($tp['FontSize']))
    $p->SetFont('x_label', $font);
  else
    $p->SetFont('x_label', $font, $tp['FontSize']);
} else {
  # Using GD fonts:
  if (isset($tp['FontSize'])) $p->SetFont('x_label', $tp['FontSize']);

}

# Label angle:
if (isset($tp['angle'])) $p->SetXLabelAngle($tp['angle']);

# Turn off X tick labels and ticks because they don't apply here:
$p->SetXTickLabelPos('none');
$p->SetXTickPos('none');

$p->SetPlotType('bars');
$p->DrawGraph();

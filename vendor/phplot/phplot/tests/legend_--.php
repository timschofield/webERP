<?php

# PHPlot test: Legend tests, including SetLegendStyle() - Baseline - 1
# This uses config.php to identify TrueType font locations and names.
require_once 'config.php';
# This is a parameterized test. Other scripts can set $tp and then include
# this script. The parameters are shown in the defaults array below:
if (!isset($tp)) $tp = array();
$tp = array_merge(array(
  'title' => 'Legend Test',
  'suffix' => ' (baseline)',   # Title part 2
  'use_ttf' => False,       # True to use TTF text, False for GD
  'textalign' => NULL,   # Legend Text Align. If NULL, don't call SetLegendStyle
  'colorboxalign' => NULL,  # Colorbox align argument, or NULL to omit.
  'legendfont' => NULL,     # Legend font, NULL to omit.
  'line_spacing' => NULL,   # Text line spacing, NULL for default.
  'text' => NULL,           # Legend array text, NULL to use built-in data.
  'px' => NULL, 'py' => NULL,   # Pixel coordinates or NULL to ignore.
  'wx' => NULL, 'wy' => NULL,   # World coordinates or NULL to ignore.
  'ttfont' => $phplot_test_ttfonts['sans'],  # Default TrueType font filename
  'ttfdir' => $phplot_test_ttfdir,   # TrueType font directory
  'ttfsize' => 14,    # TrueType font size in points
  'cbwa' => NULL,     # Colorbox Width Adjustment factor
     ), $tp);
require_once 'phplot.php';

$data = array(
  array('', 0, 0, 0, 0),
  array('', 1, 1, 2, 3),
  array('', 2, 2, 4, 6),
  array('', 3, 3, 6, 9),
);
if (!isset($tp['text']))
  $tp['text'] = array('Plot Line 1', 'Longer label for Plot Line 2', 'line 3');
$p = new Phplot\Phplot\phplot(800, 600);
if ($tp['use_ttf']) {
  $p->SetTTFPath($tp['ttfdir']);
  $p->SetDefaultTTFont($tp['ttfont']);
}
# Set line spacing:
if (isset($tp['line_spacing'])) {
  $p->SetLineSpacing($tp['line_spacing']);
}
$p->SetTitle($tp['title'] . $tp['suffix']);
# Need to set the font for TTF even if legendfont isn't given, to get the size.
if ($tp['use_ttf']) {
  if (isset($tp['legendfont'])) {
    $p->SetFont('legend', $tp['legendfont'], $tp['ttfsize']);
  } else {
    $p->SetFont('legend', $tp['ttfont'], $tp['ttfsize']);
  }
} elseif (isset($tp['legendfont'])) {
  $p->SetFont('legend', $tp['legendfont']);
}
$p->SetLegend($tp['text']);
$p->SetDataType('data-data');
$p->SetDataValues($data);
$p->SetPlotType('lines');
$p->SetXDataLabelPos('none');
$p->SetXTickIncrement(1.0);
$p->SetYTickIncrement(1.0);
if (isset($tp['textalign'])) {
  if (isset($tp['colorboxalign']))
    $p->SetLegendStyle($tp['textalign'], $tp['colorboxalign']);
  else
    $p->SetLegendStyle($tp['textalign']);
}
if (isset($tp['px']) && isset($tp['py']))
  $p->SetLegendPixels($tp['px'], $tp['py']);
if (isset($tp['wx']) && isset($tp['wy']))
  $p->SetLegendWorld($tp['wx'], $tp['wy']);
if (isset($tp['cbwa'])) $p->legend_colorbox_width = $tp['cbwa'];
$p->DrawGraph();

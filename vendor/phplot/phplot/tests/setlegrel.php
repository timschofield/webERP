<?php

# Testing legend relative position - master script
# This is a parameterized test. Other scripts can set $tp and then include
# this script. The parameters are shown in the defaults array below:
if (!isset($tp)) $tp = array();
$tp = array_merge(array(
  'title' => 'Legend Relative Positioning', # First title line
  'suffix' => 'Baseline, defaults',         # Title line 2
  'relto' => NULL,       # Relative to: image|plot|title|world|NULL to skip
  'lx' => 0, 'ly' => 0,  # Legend box reference point, relative coords
  'bx' => 0, 'by' => 0,  # Base point, relative coords
  'ox' => 0, 'oy' => 0,  # Additional pixel offset
  'callback' => NULL,    # Callback, call before DrawGraph
  'ttfontsize' => NULL,  # If not NULL, use TT font at this size
  'ttlinespace' => NULL, # If not NULL, set TT font line space factor
        ), $tp);
require_once 'phplot.php';
require_once 'config.php'; // For TrueType Fonts

# A TrueType font to use:
$font = $phplot_test_ttfdir . $phplot_test_ttfonts['sansbold'];

extract($tp); // Import test parameters

$data = array(
  array('', 0, 0, 0, 0),
  array('', 1, 1, 2, 3),
  array('', 2, 2, 4, 6),
  array('', 3, 3, 6, 9),
);
$legend = array('Plot Line 1', 'Longer label for Plot Line 2', 'line 3');


$p = new Phplot\Phplot\phplot(800, 600);
if (!empty($title)) {     // $title can be set empty for special case
    if (!empty($suffix)) $title .= "\n" . $suffix;
    $p->SetTitle($title);
}

// Use smaller window, offset, so legend position is more apparent.
$p->SetPlotAreaPixels(100, 80, 700, 480);
$p->SetLegend($legend);
$p->SetDataType('data-data');
$p->SetDataValues($data);
$p->SetPlotType('lines');
$p->SetXDataLabelPos('none');
$p->SetXTickIncrement(1.0);
$p->SetYTickIncrement(1.0);
$p->SetDrawXGrid(True);
if (isset($ttfontsize))
    $p->SetFontTTF('legend', $font, $ttfontsize, $ttlinespace);
if (isset($relto))
    $p->SetLegendPosition($lx, $ly, $relto, $bx, $by, $ox, $oy);
if (isset($callback))
    call_user_func($callback, $p);
$p->DrawGraph();

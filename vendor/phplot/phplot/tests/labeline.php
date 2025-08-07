<?php

# Test for X Label Alignment problem with TTF (PHP-5.0.5): Bug # 1891636

require_once 'config.php';

# This is a parameterized test. Other scripts can set $tp and then include
# this script. The parameters are shown in the defaults array below:
if (!isset($tp)) $tp = array();
$tp = array_merge(array(
  'title' => 'Text Alignment (X Labels)',  # First or only line
  'suffix' => '',           # Title part 2
  'lspace' => 4,            # Line spacing factor
  'xlfs' => 14,             # X Label Font Size (pts)
        ), $tp);
require_once 'phplot.php';

# Debug callback for text drawing:
function debug_text($img, $unused, $px, $py, $bbwidth, $bbheight)
{
  fwrite(STDERR, "text: ($px, $py) @ ($bbwidth, $bbheight)\n");
}

# The bug was originally seen with a plot using month names (Jan, Feb, ...)
# but this varies it a bit to exagerate the problem: a line with all small
# letters and no descenders, and a two-line label. Also made up "Juy" which
# has J which goes left/down from basepoint, and y which goes below.
$data = array(
    array('Jan', 1, 1),
    array('Feb', 2, 2),
    array('Mar', 3, 3),
    array('Apr', 4, 4),
    array('May', 5, 5),
    array('Juy', 6, 6),
    array('Jul', 7, 7),
    array("Aug\nSummer!", 8, 8),
    array('Sep', 9, 9),
    array("ocr", 10, 10),
    array('Nov', 11, 11),
    array('Dec', 12, 12),
);

$p = new Phplot\Phplot\phplot(800, 600);
$p->SetDataType('data-data');
$p->SetDataValues($data);
$p->SetPlotType('lines');

if (isset($tp['lspace'])) $p->SetLineSpacing($tp['lspace']);
#$p->SetCallback('debug_textbox', 'debug_text');
$p->SetTitle($tp['title'] . " (spacing={$tp['lspace']})" . $tp['suffix']);
$p->SetXTitle('X Axis Title');
$p->SetYTitle('Y Axis Title');

$p->SetDefaultTTFont($phplot_test_ttfdir . $phplot_test_ttfonts['sans']);
$p->SetFont('x_label', '', $tp['xlfs']);
$p->SetFont('title', '', 24);

$p->SetXDataLabelPos('plotdown');
$p->SetXTickLabelPos('none');
$p->SetXTickPos('plotdown');

$p->SetXTickIncrement(1);
$p->SetYTickIncrement(2);

$p->SetPlotAreaWorld(0, 0, 12, 12);

$p->DrawGraph();

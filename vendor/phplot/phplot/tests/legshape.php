<?php

# Legend shape marker tests - master script
require_once 'config.php';
# This is a parameterized test. Other scripts can set $tp and then include
# this script. The parameters are shown in the defaults array below:
if (!isset($tp)) $tp = array();
$tp = array_merge(array(
  'title' => 'Legend w/Shape Markers',  # First or only line
  'suffix' => 'Baseline - defaults with color boxes',  # Title line 2
  'useshapes' => False,     # True for shape markers, false for color boxes
  'fontsize' => NULL,       # Use TT font at this size
  'linespacing' => NULL,    # Line spacing scale
  'plottype' => 'points',   # Plot type, points or linepoints
  'setpointsizes' => False, # True to vary the point shape sizes
  'textalign' => NULL,      # Text alignment: left | right, NULL to ignore
                            #  both textalign and colorboxalign.
  'colorboxalign' => NULL,  # Color box alignment: left | right | none
  'colorboxwidth' => NULL,  # Color box width horizontal scale adjust
        ), $tp);
require_once 'phplot.php';
extract($tp); // Import all parameters

// A TT Font, if used.
$font = $phplot_test_ttfdir . $phplot_test_ttfonts['sans'];

$nlines = 10;
$shapes = array('circle', 'cross', 'diamond', 'dot', 'halfline',
      'line', 'plus', 'rect', 'triangle', 'trianglemid');

# Legend goes top-to-bottom, so make the first line have the highest Y.
$data = array();
for ($i = 0; $i < 6; $i++) {
  $row = array('', $i);
  for ($j = 0; $j < $nlines; $j++) $row[] = $nlines - $j - $i / 4;
  $data[] = $row;
}
$legend = array();
for ($j = 0; $j < $nlines; $j++)
   $legend[] = "Plot line $j (" . $shapes[$j] . ')';

$p = new Phplot\Phplot\phplot(800, 600);
$p->SetTitle($title . "\n" . $suffix);
$p->SetPointShapes($shapes);
$p->SetLineStyles('solid');
$p->SetDataColors(array('red', 'blue', 'green'));
$p->SetLegend($legend);
$p->SetDataType('data-data');
$p->SetDataValues($data);
$p->SetPlotType($plottype);
$p->SetPlotAreaWorld(0, 0, 5, 10);
$p->SetXDataLabelPos('none');
$p->SetXTickIncrement(1.0);
$p->SetYTickIncrement(1.0);

# Option: Use legend shape markers?
$p->SetLegendUseShapes($useshapes);

# Option: Use TT Font, set size and optional line spacing scale:
if (!empty($fontsize)) {
    $p->SetFontTTF('legend', $font, $fontsize, $linespacing);
}

# Option: Varying point shape sizes:
if ($setpointsizes) $p->SetPointSizes(array(2, 4, 8, 10, 16));

# Turn on backgrounds for visibility.
$p->SetBackgroundColor('SkyBlue');
$p->SetDrawPlotAreaBackground(True);
$p->SetPlotBgColor('plum');

# Option: Change alignment of lines/color boxes in legend?
if (isset($textalign)) $p->SetLegendStyle($textalign, $colorboxalign);

# Opton: Scale factor for color box width?
if (isset($colorboxwidth)) $p->legend_colorbox_width = $colorboxwidth;

$p->DrawGraph();

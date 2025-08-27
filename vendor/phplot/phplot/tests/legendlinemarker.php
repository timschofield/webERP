<?php

# PHPlot test: Legend with line markers, baseline case
# This is a parameterized test. Other scripts can set $tp and then include
# this script. The parameters are shown in the defaults array below:
if (!isset($tp)) $tp = array();
$tp = array_merge(array(
  'subtitle' => "Baseline: Color boxes with default width",   # Plot subtitle
  'nx' => 20,                        # Number of X values
  'ny' => 10,                        # Number of datasets, or Y values per X
  'plot_type' => 'lines',            # Plot type; must work with data-data
  'set_linewidths' => True,          # Vary line widths?
  'legend_use_shapes' => False,      # Use shapes vs colorboxes in legend?
  'plot_area_background' => False,   # Change plot area background color?
  'colorbox_width' => NULL,          # If not NULL, colorbox width factor
  'set_styles' => FALSE,             # Vary line styles?
  'more_legendlines' => 0,           # Additional legend lines after $ny
        ), $tp);
require_once 'phplot.php';

# Import parameters as variables:
extract($tp);

# Build data array:
$data = array();
for ($x = 0; $x < $nx; $x++) {
    $row = array('', $x);
    for ($j = 0; $j < $ny; $j++) {
        $row[] = ($j + 1) * $x;
    }
    $data[] = $row;
}

# Build array for legend:
for ($j = 0; $j < $ny; $j++) $legend[$j] = "Dataset #$j";
# Test more legend lines than there are data columns:
for ($j = 0; $j < $more_legendlines; $j++) $legend[$j + $ny] = "Extra line #$j";

$title = "Demo: Legend line markers feature ($plot_type plot)";
if (!empty($subtitle)) $title .= "\n" . $subtitle;

$p = new Phplot\Phplot\phplot(640, 480);
$p->SetTitle($title);
$p->SetDataType('data-data');
$p->SetDataValues($data);
$p->SetPlotType($plot_type);

// Set up arrays for line style and width.
// Dashed lines and wide lines don't play well together when applied to
// the same line, so don't do it.
if ($set_styles) {
    for ($j = 0; $j < $ny; $j++)
        $styles[$j] = ($j % 3 == 1) ? 'dashed' : 'solid';
} else {
    $styles = 'solid';
}
if ($set_linewidths) {
    if ($set_styles) {
        $width = 1;
        for ($j = 0; $j < $ny; $j++) {
            if ($styles[$j] == 'dashed') $widths[$j] = 1;
            else $widths[$j] = $width++;
        }
    } else {
        for ($j = 0; $j < $ny; $j++) $widths[$j] = $j + 1;
    }
}

if ($set_styles) {
    // $p->SetDefaultDashedStyle('10-5');
    $p->SetDrawYGrid(False); // Hard to see dashed lines with dashed grid.
}
// Always do SetLineStyles (if set_styles is false, it sets them all to solid)
$p->SetLineStyles($styles);
if ($set_linewidths) $p->SetLineWidths($widths);

$p->SetLegend($legend);
$p->SetLegendPosition(0, 0, 'plot', 0, 0, 5, 5);
if ($legend_use_shapes) $p->SetLegendUseShapes(True);

$p->SetPlotBgColor('black');
if ($plot_area_background) $p->SetDrawPlotAreaBackground(True);

if (isset($colorbox_width)) $p->legend_colorbox_width = $colorbox_width;
$p->DrawGraph();

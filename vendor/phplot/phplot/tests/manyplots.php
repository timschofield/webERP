<?php

# Many plots in one image
# 09/20/2007 From posting: "More Plots on One Image"
# Corrected boundaries and inserted fake titles and data.
# 4/2010 Changed to parameterize number of plots.
require_once 'phplot.php';

# These variables can be set in another script which then sources this
# script to make variations on this test.
if (!isset($n_plots))
    $n_plots = 8;               // Number of plots to draw
if (!isset($height_of_each_plot))
    $height_of_each_plot = 300; // Plot area height for of each plot
if (!isset($title_space))
    $title_space = 50;          // Space at top of first plot, for overall title
if (!isset($space_below_plots))
    $space_below_plots = 60;    // Space below each plot, including axis label

# Fake some data:
for ($i = 0; $i < $n_plots; $i++) {
  $report[$i] = array(array('Alpha', $i),
                      array('Beta', 1.5*$i),
                      array('Gamma', 2*$i));
}
$max_x = 2 * ($n_plots - 1) + 1;

$image_height = $n_plots * ($height_of_each_plot + $space_below_plots)
                + $title_space;

$plot = new Phplot\Phplot\phplot(800, $image_height);
$plot->SetImageBorderType('plain');

# Disable auto-output:
$plot->SetPrintImage(0);

# There is only one title: it is outside both plot areas.
$plot->SetTitle("Many Plots Test ($n_plots)");

$y1 = $title_space;                    // Top of plot area
for($i = 0; $i < $n_plots; $i++) {
    $y2 = $y1 + $height_of_each_plot;      // Bottom of plot area
    # fwrite(STDERR, "Plot $i area: min=(80, $y1) : max=(740, $y2)\n");
    $plot->SetPlotAreaPixels(80, $y1, 740,$y2);
    $plot->SetDataType('text-data');
    $plot->SetDataValues($report[$i]);
    $plot->SetPlotAreaWorld(NULL, 0, NULL, $max_x);
    $plot->SetDataColors(array('blue'));
    $plot->SetXTickLabelPos('none');
    $plot->SetXDataLabelPos('plotdown');
    $plot->SetXTickPos('plotdown');
    $plot->SetYTickIncrement(1);
    $plot->SetXTitle("Chart $i X Values");
    $plot->SetYTitle("Chart $i Y Values");
    $plot->SetPlotType('bars');
    $plot->DrawGraph();
    $y1 = $y2 + $space_below_plots; // Start next plot below last plot
}
$plot->PrintImage();

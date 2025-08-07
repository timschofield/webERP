<?php

# PHPlot test: Background image reset, baseline (case 0)
# There are 4 cases: reset or not to reset image and plot area background.
# These parameters control the cases. They can be set in a calling script:
#   $reset_plot_area_background = TRUE;
#   $reset_image_background = TRUE;
require_once 'phplot.php';

# Create the title string:
$title = "Background Image Reset Test\n"
       . "Image background is "
       . (empty($reset_image_background)? "on (set)\n"
                                        : "off (set and reset)\n")
       . "Plot area background is "
       . (empty($reset_plot_area_background)? "on (set)\n"
                                            : "off (set and reset)\n");

# Use a bar graph because it is easier to see on the weird background.
$data = array(
  array('Jan', 1000), array('Feb', 2000), array('Mar', 3000),
  array('Apr', 2500), array('May', 1500), array('Jun',  500),
);
$plot = new Phplot\Phplot\phplot(800, 600);
$plot->SetPlotType('bars');
$plot->SetDataType('text-data');
$plot->SetDataValues($data);
$plot->SetTitle($title);
# Make a legend so we can see how the background behaves:
$plot->SetLegend(array('Widgets Produced'));
$plot->SetXTickLabelPos('none');
$plot->SetXTickPos('none');
# Set Y range and tick interval:
$plot->SetPlotAreaWorld(NULL, 0, NULL, 4000);
$plot->SetYTickIncrement(500);
# Give it extra room around the plot area so we can see the backgrounds:
$plot->SetMarginsPixels(100, 100, 100, 100);

$plot->SetBgImage('images/clouds.jpg', 'scale');
if (!empty($reset_image_background)) $plot->SetBgImage(NULL);
$plot->SetPlotAreaBgImage('images/bubbles.png', 'tile');
if (!empty($reset_plot_area_background)) $plot->SetPlotAreaBgImage(NULL);

$plot->DrawGraph();

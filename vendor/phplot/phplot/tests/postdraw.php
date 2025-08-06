<?php

# Testing PHPlot: Annotating a plot using callbacks
# This is similar to the Annotate example in the manual.
# Note: This example is coded for PHPlot > 5.0.7
require_once 'phplot.php';

# Get the Sales data. In real life, this would most likely come from
# a database or external file. For this example, we will use 'random'
# data, but with a fixed seed for repeatable results.
function get_data()
{
    mt_srand(1);
    $data = array();
    # Build an array with 12 arrays of (month_name, value):
    for ($month = 1; $month <= 12; $month++)
        $data[] = array(@strftime('%b', mktime(12, 0, 0, $month, 1)),
                        5 + mt_rand(5, 40));
    return $data;
}

# Find the best and worst sales data.
# Gets the Y value (sales data) and X value. For PHPlot text-data data,
# the X values are assigned as 0.5, 1.5, 2.5, etc.
# The data array is in 'text-data' format: array of array(label, Y)...
function get_best_worst($data,
    &$best_index, &$best_sales, &$worst_index, &$worst_sales)
{
  $best_sales = NULL;
  $worst_sales = NULL;
  foreach ($data as $x => $point) {
      if (!isset($best_sales) || $point[1] > $best_sales) {
          $best_sales = $point[1];
          $best_index = $x + 0.5;
      }
      if (!isset($worst_sales) || $point[1] < $worst_sales) {
          $worst_sales = $point[1];
          $worst_index = $x + 0.5;
      }
  }
}

# Plot annotation callback.
# The pass-through argument is the PHPlot object.
function annotate_plot($img, $plot)
{
    global $best_index, $best_sales, $worst_index, $worst_sales;

    # Allocate our own colors, rather than poking into the PHPlot object:
    $red = imagecolorresolve($img, 255, 0, 0);
    $green = imagecolorresolve($img, 0, 216, 0);

    # Get the pixel coordinates of the data points for the best and worst:
    list($best_x, $best_y) = $plot->GetDeviceXY($best_index, $best_sales);
    list($worst_x, $worst_y) = $plot->GetDeviceXY($worst_index, $worst_sales);

    # Draw ellipses centered on those two points:
    imageellipse($img, $best_x, $best_y, 50, 20, $green);
    imageellipse($img, $worst_x, $worst_y, 50, 20, $red);

    # Place some text above the points:
    $font = '3';
    $fh = imagefontheight($font);
    $fw = imagefontwidth($font);
    imagestring($img, $font, $best_x-$fw*4, $best_y-$fh-10,
                'Good Job!', $green);

    # We can also use the PHPlot internal function for text.
    # It does the center/bottom alignment calculations for us.
    # Specify the font argument as NULL or '' to use the generic one.
    $plot->DrawText('', 0, $worst_x, $worst_y-10, $red,
                 'Bad News!', 'center', 'bottom');
}

# Begin main processing:

# Fill the data array:
$data = get_data();

# Find the best and worst months:
get_best_worst($data, $best_index, $best_sales, $worst_index, $worst_sales);

# Create the PHPlot object, set title, plot type, data array type, and data:
$plot = new Phplot\Phplot\phplot(800, 600);
#$plot->SetTitle('Monthly Widget Sales');
$plot->SetTitle('Plot Annotatation with Callbacks');
$plot->SetPlotType('bars');
$plot->SetDataType('text-data');
$plot->SetDataValues($data);
# Borders are needed for the manual:
$plot->SetImageBorderType('plain');

# Select X data labels (not tick labels):
$plot->SetXTickPos('none');
$plot->SetXTickLabelPos('none');
$plot->SetXDataLabelPos('plotdown');

# Format Y labels as "$nM" with no decimals, steps of 5:
$plot->SetYLabelType('data', 0, '$', 'M');
$plot->SetYTickIncrement(5.0);

# Force the bottom of the plot to be at Y=0, and omit
# the bottom "$0M" tick label because it looks odd:
$plot->SetPlotAreaWorld(NULL, 0);
$plot->SetSkipBottomTick(True);

# Establish the drawing callback to do the annotation:
$plot->SetCallback('draw_all', 'annotate_plot', $plot);

# Draw the graph:
$plot->DrawGraph();

<?php

# Generate plot type thumbnail images For PHPlot Reference Manual
require_once 'phplot.php';

# This class extends PHPlot, configured for thumbnails.
class thumbnail extends PHPlot {
    function __construct($output_file, $plot_type, $data_type, $data_values)
    {
        parent::__construct(64, 64, $output_file);
        $this->SetIsInline(True); // Needed to output to file
        # Turn everything off - just a plot in a box.
        $this->SetMarginsPixels(2, 2, 2, 2);
        $this->SetPlotBorderType('none');
        $this->SetXDataLabelPos('none');
        $this->SetXTickLabelPos('none');
        $this->SetXTickPos('none');
        $this->SetYDataLabelPos('none');
        $this->SetYTickLabelPos('none');
        $this->SetYTickPos('none');
        $this->SetDrawXGrid(False);
        $this->SetDrawYGrid(False);
        $this->SetDrawXAxis(False);
        $this->SetDrawYAxis(False);
        $this->SetPlotType($plot_type);
        $this->SetDataType($data_type);
        $this->SetDataValues($data_values);
        $this->SetDataColors(array('red', 'blue', 'DarkGreen', 'magenta'));
        $this->SetShading(0);
        $this->SetLabelScalePosition(0); // Turn off pie chart labels
        $this->SetLineStyles('solid');
    }

    // This static method returns the list of plot types from the PHPlot
    // class (which is in a protected static array there).
    static function get_plot_types()
    {
        return array_keys(PHPlot::$plots);
    }
}



# Data type and data array for each plot type:
$data_types['area'] = 'data-data';
$data_values['area'] = array(
    array('', 0, 9, 5, 3, 2),
    array('', 1, 6, 5, 2, 1),
    array('', 2, 9, 6, 4, 2),
    array('', 3, 6, 4, 2, 1),
);

$data_types['bars'] = 'text-data';
$data_values['bars'] = array(
    array('', 3, 5),
    array('', 4, 6),
    array('', 7, 5),
    array('', 4, 9),
);

$data_types['boxes'] = 'text-data';
$data_values['boxes'] = array(
    // Ymin YQ1 Ymid YQ3 Ymax (outliers not used)
    array('', 1, 2, 3, 4, 6),
    array('', 2, 3, 4, 5, 6),
    array('', 1, 2, 3, 4, 5),
    array('', 1, 3, 4, 5, 6),
);

$data_types['bubbles'] = 'data-data-xyz';
$data_values['bubbles'] = array(
    array('', 1, 1, 1,  6, 8),
    array('', 2, 9, 3,  2, 5),
    array('', 3, 4, 6,  8, 2),
    array('', 4, 7, 4,  3, 9),
);

$data_types['candlesticks'] = 'text-data';
$data_values['candlesticks'] = array(
    // Open High Low Close; see desired range in setup below
    array('', 27, 41, 20, 34),
    array('', 30, 44, 23, 37),
    array('', 37, 44, 23, 30),
    array('', 34, 41, 20, 27),
);

$data_types['candlesticks2'] = $data_types['candlesticks'];
$data_values['candlesticks2'] = $data_values['candlesticks'];

$data_types['ohlc'] = $data_types['candlesticks'];
$data_values['ohlc'] = $data_values['candlesticks'];

$data_types['lines'] = 'data-data';
$data_values['lines'] = array(
    array('', 0, 1, 2,  3),
    array('', 1, 2, 3,  2),
    array('', 2, 3, 5,  1),
    array('', 3, 4, 7,  0),
);

$data_types['points'] = $data_types['lines'];
$data_values['points'] = $data_values['lines'];

$data_types['linepoints'] = $data_types['lines'];
$data_values['linepoints'] = $data_values['lines'];

$data_types['pie'] = 'text-data-single';
$data_values['pie'] = array(
    array('', 1),
    array('', 1),
    array('', 1),
);

$data_types['squared'] = 'data-data';
$data_values['squared'] = array(
    array('', 0, 0, 5, 8),
    array('', 1, 2, 6, 8),
    array('', 2, 4, 5, 7),
    array('', 3, 2, 6, 8),
);

$data_types['squaredarea'] = 'data-data';
$data_values['squaredarea'] = array(
    array('', 0, 8, 5, 1),
    array('', 1, 7, 6, 4),
    array('', 2, 6, 3, 2),
    array('', 3, 6, 2, 1),
);

$data_types['stackedarea'] = 'data-data';
$data_values['stackedarea'] = array(
    array('', 0, 0, 2, 3, 1),
    array('', 1, 1, 1, 2, 2),
    array('', 2, 2, 0, 1, 2),
    array('', 2, 3, 1, 0, 1),
);

$data_types['stackedbars'] = 'text-data';
$data_values['stackedbars'] = array(
    array('', 2, 3, 5),
    array('', 2, 5, 4),
    array('', 2, 7, 3),
    array('', 2, 9, 2),
);

$data_types['stackedsquaredarea'] = 'data-data';
$data_values['stackedsquaredarea'] = array(
    array('', 0, 1, 1, 2),
    array('', 1, 4, 1, 3),
    array('', 2, 3, 2, 1),
    array('', 3, 3, 1, 2),
);

$data_types['thinbarline'] = 'text-data';
for ($i = 0; $i < 20; $i++)
    $data_values['thinbarline'][] = array('', $i);


# Extra setup function for each plot type, if needed:

function setup_bubbles($plot)
{
    $plot->SetPlotAreaWorld(0, NULL, 5, NULL);
    $plot->bubbles_min_size = 4;
    $plot->bubbles_max_size = 12;
}

function setup_candlesticks($plot)
{
    $plot->SetPlotAreaWorld(NULL, 20, NULL, 50);
    $plot->SetDataColors(array('red', 'DarkGreen', 'red', 'DarkGreen'));
}
function setup_candlesticks2($plot)
{
    setup_candlesticks($plot);
}
function setup_ohlc($plot)
{
    setup_candlesticks($plot);
}

function setup_boxes($plot)
{
    $plot->SetDataColors(array('blue', 'red', 'blue', 'red'));
}



# Loop over each plot type and make the thumbnail image:
foreach (thumbnail::get_plot_types() as $plot_type) {

    if (!isset($data_types[$plot_type], $data_values[$plot_type])) {
        echo "Error: Missing data setup for plot type '$plot_type'\n";
        exit(1);
    }
    $filename = "thumbnail-$plot_type.png";
    $plot = new thumbnail($filename, $plot_type,
                        $data_types[$plot_type],
                        $data_values[$plot_type]);
    $setup_function = "setup_$plot_type";
    if (function_exists($setup_function)) $setup_function($plot);
    $plot->DrawGraph();
    echo "Generated $filename\n";
}

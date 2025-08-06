<?php

# PHPlot test: Data label extended custom formatting - base
# This tests custom label formatting for axis data labels and data value
# labels, with access to the row and column (if applicable) of the label.
# This was added after PHPlot-5.7.0
require_once 'phplot.php';
require_once 'makedata.php'; // Builds a data array.

# Other scripts can set the following variables and then include this script:
#   $plot_type : Any available plot type.
#      Note: Only these plot types support data value labels:
#              points lines linepoints squared bars stackedbars
#      All plot types support axis data labels except for pie charts.
#   $data_type : Any data type that works with the $plot_type.
#   $nx : Number of independent variable data points
#   $ny : Number of data sets, or number of dependent variable values per
#         independent variable value.
#         Note: Some plot types (e.g. OHLC) restrict this.
#   $max : Maximum independent variable data value.
#   $dvls : True if Data Value Labels are expected to be produced, False if not
#
if (empty($plot_type)) $plot_type = 'lines';
if (empty($data_type)) $data_type = 'text-data';
if (empty($nx)) $nx = 10;
if (empty($ny)) $ny = 3;
if (empty($max)) $max = 20;
if (!isset($dvls)) $dvls = TRUE;

$title = "Data Labels, Custom Format ($plot_type, $data_type)";
if (empty($dvls))
    $title .= "\nThis plot does not have Data Value Labels";
else
    $title .= "\nData Value Labels suffix ':row,column[X|Y]'";
$title .= "\nAxis Data Labels suffix ':row[X|Y]'";

# Label formatting function for data value labels and axis data labels.
function fmtdl($label, $passthru, $row=NULL, $col=NULL)
{
    $result = $label;
    if (isset($row)) {
        $result .= ":$row";
        if (isset($col)) $result .= ",$col";
    }
    return "{$result}[$passthru]";
}

$plot = new Phplot\Phplot\phplot(800, 600);
$plot->SetDataType($data_type);
$plot->SetDataValues(make_data_array($plot_type, $data_type, $nx, $ny, $max));
$plot->SetPlotType($plot_type);

$plot->SetTitle($title);
$plot->SetXTitle('X Axis Title Here');
$plot->SetYTitle('Y Axis Title Here');

# Use the same custom formatting function for both label types:
#   Vertical plots:   X axis data labels, Y data value labels
#   Horizontal plots: X data value labels, Y axis data labels
$plot->SetXDataLabelType('custom', 'fmtdl', 'X');
$plot->SetYDataLabelType('custom', 'fmtdl', 'Y');

# Turn off ticks on independent axis, and turn on data value labels. This
# depends on the plot type and data type (horizontal or vertical):
$label_pos = $plot_type == 'stackedbars' ? 'plotstack' : 'plotin';
if ($data_type == 'text-data-yx' || $data_type == 'data-data-yx') {
    // Horizontal plot: configure X data value labels
    $plot->SetYTickPos('none');
    $plot->SetXDataLabelPos($label_pos);
    $plot->SetPlotAreaWorld(0, 0, NULL, $nx);
    $plot->SetXTickIncrement(1);
} else {
    // Vertical plot: configure Y data value labels
    $plot->SetXTickPos('none');
    $plot->SetYDataLabelPos($label_pos);
    $plot->SetPlotAreaWorld(0, 0, $nx, NULL);
    $plot->SetYTickIncrement(1);
}
# For data-data-error plots, pivot the data value labels away from
# the error bars:
if ($data_type == 'data-data-error')
    $plot->data_value_label_angle = 45;

$plot->DrawGraph();

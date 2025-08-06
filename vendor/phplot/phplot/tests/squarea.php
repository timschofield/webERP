<?php

# PHPlot test: Squared and Square Area (PHPlot >= 6.2.0)
# This is a parameterized test. Other scripts can set $tp and then include
# this script. The parameters are shown in the defaults array below:
if (!isset($tp)) $tp = array();
$tp = array_merge(array(
  'suffix' => 'Squared',       # Title part 2
  'plot_type' => 'squared',  # Plot Type: squared squaredarea stackedsquaredarea
  'dataset' => 0,               # Which dataset to use
  'borders' => NULL,            # Borders for area plots? T, F, NULL to omit
  'broken_lines' => NULL,       # Draw Broken Lines?
  'missing_point' => FALSE,     # Test missing point(s)?
  'axis_data_labels' => FALSE,  # Do X axis data labels?
  'data_value_labels' => FALSE, # Do data value labels?
        ), $tp);
require_once 'phplot.php';
require_once 'phplot_truecolor.php';

function fail($why)
{
    fwrite(STDERR, "squarea: Invalid parameter: $why\n");
    exit(1);
}

// Selectable data sets:
$datanames[0] = "15 points, 1 column (2 neg.)";
$datatype[0] = 'data-data';
$datasets[0] = array(
      array('Pt#01',  0,   0),
      array('Pt#02',  1,   1),
      array('Pt#03',  2,   4),
      array('Pt#04',  3,  10),
      array('Pt#05',  4,  10),
      array('Pt#06',  5,   8),
      array('Pt#07',  6,   6),
      array('Pt#08',  7,   9),
      array('Pt#09',  8,  11),
      array('Pt#10',  9,  12),
      array('Pt#11', 10,  15),
      array('Pt#12', 11,   5),
      array('Pt#13', 12,  -3),
      array('Pt#14', 13,  -5),
      array('Pt#15', 14,   8),
    );

$datanames[1] = "15 points, 3 columns";
$datatype[1] = 'data-data';
$datasets[1] = array(
      array('A',  0,   0,   0,   0),
      array('B',  1,   2,   0,   0),
      array('C',  2,   4,   1,   0),
      array('D',  3,   6,   3,   0),
      array('E',  4,   9,   5,   2),
      array('F',  5,  12,   8,   4),
      array('G',  6,  15,  11,   7),
      array('H',  7,  18,  14,   9),
      array('I',  8,  21,  17,  13),
      array('J',  9,  22,  16,   9),
      array('K', 10,  18,  10,   4),
      array('L', 11,  12,   5,   1),
      array('M', 12,   6,   2,   0),
      array('N', 13,   3,   0,   0),
      array('O', 14,   0,   0,   0),
    );

$datanames[2] = "12 points, 1 column";
$datatype[2] = 'text-data';
$datasets[2] = array(
      array('Jan',  20),
      array('Feb',  32),
      array('Mar',  48),
      array('Apr',  51),
      array('May',  47),
      array('Jun',  29),
      array('Jul',  19),
      array('Aug',   9),
      array('Sep',   1),
      array('Oct',   5),
      array('Nov',  10),
      array('Dec',   5),
    );

$datanames[3] = "7 points, 2 columns";
$datatype[3] = 'text-data';
$datasets[3] = array(
      array('Sun', 50, 20),
      array('Mon', 45, 25),
      array('Tue', 40, 30),
      array('Wed', 45, 20),
      array('Thu', 50, 15),
      array('Fri', 60, 20),
      array('Sat', 55, 25),
    );

$datanames[4] = "For debugging, not normally used";
$datatype[4] = 'data-data';
$datasets[4] = array(
      array('', 1, 10, 5),
      array('', 2, 25, 4),
      array('', 3, 15, 3),
    );

# Extract all test parameters as local variables:
extract($tp);

# Parameter Checks:
if (!preg_match('/squared/', $plot_type))
    fail("plot type=$plot_type");
$is_area = preg_match('/area/', $plot_type);
if ($is_area && (isset($broken_lines) || $missing_point))
    fail("Area plots do not support missing points/broken lines");
if (!$is_area && isset($borders))
    fail("borders parameter applies only to area plots");
if (!isset($datasets[$dataset]))
    fail("dataset=$dataset");
# Use transparency only for area plots (it makes line plots harder to see):
$alpha = $is_area ? 64 : 0;

$data = $datasets[$dataset];
$data_type = $datatype[$dataset];

$title = "Squared and Squared Area Tests\n"
       . "$data_type: $datanames[$dataset]";

# Test missing Y value(s)?
if ($missing_point) {
    if (($n = count($data)) >= 10)
        $miss_x = 8;
    else
        $miss_x = $n - 3;

    if ($data_type  == 'text-data') {
        $data[$miss_x][1] = '';
        $where = "x=" . ($miss_x + 0.5); // How text-data does it
    } else {
        $data[$miss_x][2] = '';
        $where = "x=$miss_x";
    }

    if ($axis_data_labels)
        $title .= ", missing point at label '{$data[$miss_x][0]}'";
    else
        $title .= ", missing point at $where";
}
if (!empty($suffix)) $title .= "\n" . $suffix;

$plot = new Phplot\Phplot\phplot_truecolor(800, 600);

$plot->SetTitle($title);
$plot->SetDataType($data_type);
$plot->SetDataValues($data);
$plot->SetPlotType($plot_type);
if (isset($broken_lines)) $plot->SetDrawBrokenLines($broken_lines);
if (isset($borders)) $plot->SetDrawDataBorders($borders);

// Set contrasting colors for data sets and borders.
// Use transparency for area-fill plots. There should be no overlapping
// fill areas, but this will show them if there are.
$plot->SetDataColors(array('DarkGreen', 'purple', 'orange', 'yellow'),
                     NULL, $alpha);
$plot->SetDataBorderColors(array('red', 'green', 'SlateBlue', 'pink'));

// Don't use dashed lines
$plot->SetLineStyles('solid');

// Label options:
if ($data_value_labels) $plot->SetYDataLabelPos('plotin');
$plot->SetXDataLabelPos($axis_data_labels ? 'plotdown' : 'none');

// To prevent clipping the right edge:
$plot->TuneXAutoRange(NULL, NULL, 0.02);

$plot->DrawGraph();

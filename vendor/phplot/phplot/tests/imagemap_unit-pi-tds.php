<?php

# PHPlot Unit test: Image map with pie chart, baseline text-data-single case
# This generates (and discards) a plot, and checks the image map data.
# For pie charts, it just checks the number of points and syntax. It
# can't check the actual values without duplicating the calculations.
require_once 'phplot.php';

# This variable can be set by an external script:
# Data type: text-data data-data text-data-single
if (!isset($data_type)) $data_type = 'text-data-single';

# True to report test cases and all results:
$test_verbose = False;

# ===========

# Capture the points which would be generated for the image map:
function store_map($im, $unused1, $shape, $segment, $unused2,
                   $xc, $yc, $wd, $ht, $start_angle, $end_angle)
{
  global $points;
  if ($shape != 'pie') die("Error expecting pie shapes from plot\n");
  $points[$segment] = array($xc, $yc, $wd, $ht, $start_angle, $end_angle);
}


# Check the points generated for an image map.
# This only validates that there is one point generated per segment, and
# no missing points.
# Returns 0 if OK, non-zero on error after writing a message.
function check_points($data, $n_segments, $points)
{
    global $test_verbose;

    $errors = 0;

    # Checks:
    #   A map point was generated for each pie segment
    #   There are no extra map points
    for ($seg = 0; $seg < $n_segments; $seg++) {
        if (!isset($points[$seg]) || count($points[$seg]) != 6) {
            $errors++;
            fwrite(STDERR, "Error: Missing map point for segment $seg\n");
        } else {
            if ($test_verbose) fwrite(STDERR, "($seg): "
                   . implode(', ', $points[$seg]) . "\n");
            # Unset the point to check for extra points at the end.
            unset($points[$seg]);
        }
    }

    # If all points are accounted for, the array will be empty now.
    if (!empty($points)) {
        $errors++;
        fwrite(STDERR, "Error: Extra points in map: "
                       . print_r($points, True) .  "\n");
    }

    return $errors;
}


# Create a data arrays for a pie chart with the given data type.
# Note: There must be no sectors that are so small (< 1 degree arc, when
# converted to integers) that they vanish.

if ($data_type == 'text-data-single') {
    $data = array(
        array('', 11),
        array('', 22),
        array('', 33),
        array('', 44),
    );
    $n_segments = count($data);
} elseif ($data_type == 'data-data') {
    $data = array(
        array('', 0, 5, 6, 10),
        array('', 1, 4, 8, 2),
        array('', 2, 1, 0, 15),
        array('', 3, 9, 8, 0),
    );
    $n_segments = count($data[0]) - 2;
} elseif ($data_type == 'text-data') {
    $data = array(
        array('', 5, 6, 10, 8, 5),
        array('', 1, 2, 1, 2, 6),
    );
    $n_segments = count($data[0]) - 1;
} else die("Invalid data type: $data_type");

# This will hold the generated map points:
$points = array();

$plot = new Phplot\Phplot\phplot(800, 600);
$plot->SetFailureImage(False); // No error images
$plot->SetPrintImage(False); // No automatic output
$plot->SetDataValues($data);
$plot->SetDataType($data_type);
$plot->SetPlotType('pie');
$plot->SetCallback('data_points', 'store_map', $data);
$plot->DrawGraph();

// For validation of error checks:
// $points[] = array(1,2,3,4,5,6); // Add a point
// unset($points[2]); // Delete a point

$errors = check_points($data, $n_segments, $points);
$name = "pie chart image map unit test ($data_type, $n_segments segments)";
if ($errors > 0) {
  fwrite(STDERR, "Failed: $errors error(s) in $name\n");
  exit(1);
}
echo "Passed: no errors in $name\n";

<?php

# PHPlot Unit test: Image map with points plot
# This generates (and discards) a plot, and checks the image map data.
require_once 'phplot.php';

# True to report test cases and all results:
$test_verbose = False;

# Capture the points which would be generated for the image map:
function store_map($im, $data, $shape, $row, $col, $x, $y)
{
  global $points;
  if ($shape != 'dot') die("Error expecting dot shapes from plot\n");
  $points[$row][$col] = array($x, $y);
}


# Check the points generated for an image map.
# Validate the number of points, one point for each data point, with
# the right X,Y device coordinates.
#   $plot : The PHPlot object (needed to use the GetDeviceXY method)
#   $data : The data array, format data-data
#   $points : Array of generated points (see store_map above).
# Returns 0 if OK, non-zero on error after writing a message.
function check_points($plot, $data, $n_rows, $n_columns, $points)
{
    global $test_verbose;

    $errors = 0;

    # Checks:
    #   A map point was generated for each data point
    #   The map point has the correct X,Y coordinates
    #   There are no extra map points
    for ($row = 0; $row < $n_rows; $row++) {
        # Get X coordinate from data row:
        $x_world = $data[$row][1]; // 1 : skip label
        for ($col = 0; $col < $n_columns; $col++) {
            # Get Y coordinate from data point:
            $y_world = $data[$row][$col + 2]; // Skip label and X
            if (!isset($points[$row][$col])) {
                $errors++;
                fwrite(STDERR, "Error: Missing map point for data at $row,$col\n");
            } else {
                # Convert the data point X,Y to device coordinates:
                list($x_data, $y_data) = $plot->GetDeviceXY($x_world, $y_world);
                # Check for exact match (these are integer values):
                list($x_map, $y_map) = $points[$row][$col];
                if ($test_verbose) fwrite(STDERR, "($row,$col): "
                       . "$x_world,$y_world $x_data,$y_data $x_map,$y_map\n");
                if ($x_data != $x_map || $y_data != $y_map) {
                    $errors++;
                    fwrite(STDERR, "Error: Value mismatch at $row,$col\n"
                       . "  Data array (world coords): $x_world, $y_world\n"
                       . "  Data array (dev.  coords): $x_data, $y_data\n"
                       . "    Map data (image coords): $x_map, $y_map\n");
                }

                # Unset the point to check for extra points at the end.
                unset($points[$row][$col]);
                # Unset row after last point is removed:
                if (empty($points[$row])) unset($points[$row]);
            }
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


# Make a pseudo-random data array.
$n_rows = 100;
$n_points_per_row = 50;
$data = array();
mt_srand(1);
for ($i = 0; $i < $n_rows; $i++)
{
    $row = array('', mt_rand(0, 10000) / 100.0); // X value = 0.00 : 100.00
    for ($j = 0; $j < $n_points_per_row; $j++) {
        $row[] = mt_rand(-1000, 1000) / 100.0;  // Y value -10.00 - 10.00
    }
    $data[] = $row;
}

# This will hold the generated map points:
$points = array();

$plot = new Phplot\Phplot\phplot(800, 600);
$plot->SetFailureImage(False); // No error images
$plot->SetPrintImage(False); // No automatic output
$plot->SetDataValues($data);
$plot->SetDataType('data-data');
$plot->SetPlotType('points');
$plot->SetCallback('data_points', 'store_map', $data);
$plot->DrawGraph();

// For validation of error checks:
// $points[] = array(100,100); // Add a point
// unset($points[1][2]); // Delete a point
// $points[3][2][0]++; // Corrupt an X
// $points[1][0][1] = 0; // Corrupt a Y

$errors = check_points($plot, $data, $n_rows, $n_points_per_row, $points);
$name = 'points plot image map unit test';
if ($errors > 0) {
  fwrite(STDERR, "Failed: $errors error(s) in $name\n");
  exit(1);
}
echo "Passed: no errors in $name\n";

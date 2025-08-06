<?php

# Unit test for partial margin settings.
# This tests the cases with partial specification of plot area or margins.
# Each of SetMarginsPixels() and SetPlotAreaPixels has 4 arguments, any of
# which can be NULL, giving 32 test cases, plus not calling at all.
# For each test case, produce the plot but don't output an image. Look inside
# the PHPlot object to report the resulting plot area.
require_once 'phplot.php';
require_once 'usupport.php';  # Support functions for unit tests.

// Extend PHPlot class to allow access to protected variable(s):
class PHPlot_pv extends Phplot\Phplot\phplot {
    public function GET_plot_area() { return $this->plot_area; }
}

# True to report test cases and all results:
$test_verbose = False;

# Global test case counters:
$n_tests = 0;
$n_pass = 0;
$n_fail = 0;

# Global data:
$data = array(
  array('Jan', 1, 100, 200, 300),
  array('Feb', 2, 120, 190, 240),
  array('Mar', 3, 130, 180, 290),
  array('Apr', 4, 140, 170, 260),
  array('May', 5, 120, 160, 200),
  array('Jun', 6, 130, 150, 220),
);
define('PLOT_WIDTH', 1024);
define('PLOT_HEIGHT', 768);

# Override data for SetMarginsPixels (left, right, top, bottom):
$margins_override = array(200, 100, 150, 120);
# Override data for SetPlotAreaPixels (x1, y1, x2, y2):
$plotarea_override = array(100, 120, PLOT_WIDTH-200, PLOT_HEIGHT-150);

# "Mask" an array: Return the array with NULL replacing the positions
# which are set in the mask. The mask is apply LSB first.
function array_mask($ar, $mask, $size)
{
    $bitmask = 1;
    for ($i = 0; $i < $size; $i++) {
        $result[$i] = ($mask & $bitmask) ? $ar[$i] : NULL;
        $bitmask *= 2;
    }
    return $result;
}

# "Select" from arrays: Return elements from the first array when
# the mask is 1, else the second array:
function array_select($ar1, $ar2, $mask, $size)
{
    $bitmask = 1;
    for ($i = 0; $i < $size; $i++) {
        $result[$i] = ($mask & $bitmask) ? $ar1[$i] : $ar2[$i];
        $bitmask *= 2;
    }
    return $result;
}


# Execute one test case:
#   $call_*  Flag indicating if the function should be called
#   $args_*  Bitmap (0x0-0x0f) indicating which args should be used.
#   $return_margins : True to return margins, false to return coordinates.
# Returns an array of 4 values depending on $return_margins:
#    If true: Returns margins left right top bottom
#    If false: Returns coordinates x1 y1 x2 y2
function pmarg_test($call_SetMarginsPixels,
    $args_SetMarginsPixels,
    $call_SetPlotAreaPixels,
    $args_SetPlotAreaPixels,
    $return_margins)
{
    global $data, $margins_override, $plotarea_override;

    $p = new phplot_pv(PLOT_WIDTH, PLOT_HEIGHT);
    $p->SetTitle("Unused Title Goes Here");
    $p->SetDataType('data-data');
    $p->SetDataValues($data);
    $p->SetPlotType('lines');
    $p->SetXTickLabelPos('none');
    $p->SetXTickIncrement(1.0);
    $p->SetYTickIncrement(10.0);
    $p->SetYTickPos('both');
    $p->SetYTickLabelPos('both');
    $p->SetDrawXGrid(False);
    $p->SetDrawYGrid(False);
    $p->SetXTitle("Two Line\nX Axis Title");
    $p->SetYTitle("Three Line\nY Axis\nTitle");

    # Variant cases:
    if ($call_SetMarginsPixels) {
        call_user_func_array(array($p, 'SetMarginsPixels'),
            array_mask($margins_override, $args_SetMarginsPixels, 4));
    }
    if ($call_SetPlotAreaPixels) {
        call_user_func_array(array($p, 'SetPlotAreaPixels'),
            array_mask($plotarea_override, $args_SetPlotAreaPixels, 4));

    }

    # Produce but don't output the image. Comment out to test the test.
    $p->SetPrintImage(False);

    # Produce the graph:
    $p->DrawGraph();

    # Return the resulting margins or coordinates, as requested.
    $plot_area = $p->GET_plot_area();
    if ($return_margins) {
        return array($plot_area[0], PLOT_WIDTH - $plot_area[2],
                     $plot_area[1], PLOT_HEIGHT - $plot_area[3]);
    }
    return $plot_area;
}

# Execute one test case.
# Increments the test, pass, and fail counters based on the results.
#   $name : Name of this test case
#   ...  : Arguments passed through to pmarg_test()
#   $expected : If not empty, array to compare with results.
function test($name,
    $call_SetMarginsPixels,
    $args_SetMarginsPixels,
    $call_SetPlotAreaPixels,
    $args_SetPlotAreaPixels,
    $return_margins,
    $expected = NULL)
{
    global $test_verbose, $n_tests, $n_pass, $n_fail;

    $n_tests++;
    $result = pmarg_test($call_SetMarginsPixels, $args_SetMarginsPixels,
        $call_SetPlotAreaPixels, $args_SetPlotAreaPixels, $return_margins);

    $title = "Test case $n_tests: $name";
    if ($test_verbose) {
        echo "$title - " . ($return_margins ? "Margins=" : "PlotArea=")
            . implode(', ', $result) . "\n";
    }

    $error = '';
    if (empty($expected) || expect_equal($expected, $result, $title, $error)) {
        $n_pass++;
    } else {
        $n_fail++;
        echo "$error\n";
    }
    return $result;
}

if ($test_verbose) echo "test set partial margins:\n";

# Baseline - auto calculated margins:
$base = test("SetMarginPixels baseline",
            False, 0x00, False, 0x00, True);

# Cases of SetMarginsPixels:
for ($mask = 0; $mask < 16; $mask++) {
    test("SetMarginPixels $mask",
         True, $mask, False, 0, True,
         array_select($margins_override, $base, $mask, 4));
}

# Baseline - for plot area:
$base = test("SetPlotAreaPixels baseline",
             False, 0x00, False, 0x00, False);

# Cases of SetPlotAreaPixels:
for ($mask = 0; $mask < 16; $mask++) {
    test("SetPlotAreaPixels $mask",
         False, 0, True, $mask, False,
         array_select($plotarea_override, $base, $mask, 4));
}

# ======== End of test cases and error reporting ==========

echo "test set partial margins: $n_tests test cases, $n_pass pass, $n_fail fail\n";
if ($n_fail > 0) exit(1);
# PHPlot test suite requires falling off the end, not exit, on success.

<?php

# PHPlot unit test: Plot Types and Data Types
# This test tries every plot type with every data type.
# (It replaces individual, parameterized tests - last up to 78 tests - one
# for each data type and plot type combination.)
# Each case either throws an error because the plot type does not handle that
# data type, or creates a plot. The error message for the unsupported type
# case is checked, so other errors won't cause the case to be misreported
# as passing.

require_once 'phplot.php';

# True to report test cases and all results:
$test_verbose = False;

# Global test case counters:
$n_tests = 0;
$n_pass = 0;
$n_fail = 0;

# ====== Test Data ======

# Table of plot types and data types and support status.
# This is based on the table in "Plot Types and Data Types" in the PHPlot
# Reference Manual.

# Data types, in the same order used in the plot_types array below.
$data_types = array(
    "text-data",
    "data-data",
    "data-data-error",
    "text-data-single",
    "text-data-yx",           // PHPlot-5.1.2
    "data-data-yx",           // PHPlot-5.1.3
    "data-data-yx-error",     // PHPlot-6.1.0
    "data-data-xyz",          // PHPlot-5.5.0
);

# Plot types, and which data types they support, ordered per $data_types.
# Note: These rows should match the rows in the Reference Manual, "Concepts"
# chapter, Plot Types and Data Types table.
$plot_types = array(
    "area"           => array( 1, 1, 0, 0, 0, 0, 0, 0),
    "bars"           => array( 1, 0, 0, 0, 1, 0, 0, 0),
    "boxes"          => array( 1, 1, 0, 0, 0, 0, 0, 0), // PHPlot-6.1.0
    "bubbles"        => array( 0, 0, 0, 0, 0, 0, 0, 1), // PHPlot-5.5.0
    "candlesticks"   => array( 1, 1, 0, 0, 0, 0, 0, 0), // PHPlot-5.3.0
    "candlesticks2"  => array( 1, 1, 0, 0, 0, 0, 0, 0), // PHPlot-5.3.0
    "linepoints"     => array( 1, 1, 1, 0, 1, 1, 1, 0),
    "lines"          => array( 1, 1, 1, 0, 1, 1, 1, 0),
    "ohlc"           => array( 1, 1, 0, 0, 0, 0, 0, 0), // PHPlot-5.3.0
    "pie"            => array( 1, 1, 0, 1, 0, 0, 0, 0),
    "points"         => array( 1, 1, 1, 0, 1, 1, 1, 0),
    "squared"        => array( 1, 1, 0, 0, 0, 0, 0, 0),
    "squaredarea"    => array( 1, 1, 0, 0, 0, 0, 0, 0), // PHPlot-6.2.0
    "stackedsquaredarea"
                     => array( 1, 1, 0, 0, 0, 0, 0, 0), // PHPlot-6.2.0
    "stackedarea"    => array( 1, 1, 0, 0, 0, 0, 0, 0), // PHPlot-5.1.1
    "stackedbars"    => array( 1, 0, 0, 0, 1, 0, 0, 0),
    "thinbarline"    => array( 1, 1, 0, 0, 1, 1, 0, 0),
);


# ====== Test functions ======

# Extend the PHPlot class to suppress error images.
# Instead of displaying an error image, the error text is stored
# into a class variable. The test function will check there.
# Note: Because the error handler returns, rather than raising an error,
# need to check return values from methods and not continue if they return
# FALSE. Otherwise the messages cascade and do not reflect actual behavior.
# Note: This test predates use of SetFailureImage() to disable error images.
class PHPlot_noerr extends Phplot\Phplot\phplot
{
    public $test_error_text = '';
    function PrintError($message)
    {
        $this->test_error_text = $message;
        return FALSE;
    }
}


# Create a valid data array for a data type.
function make_data_array($plot_type, $data_type)
{
    $data = array( array('A'), array('B'), array('C'), array('D'));
    $n_rows = count($data);

    # Explicit X values?
    $need_x = ($data_type != 'text-data' && $data_type != 'text-data-single'
            && $data_type != 'text-data-yx');

    # Data values to use:
    if ($data_type == 'data-data-error' || $data_type == 'data-data-yx-error') {
        # Special case: error plots
        $n_cols = 3;
        $y = array( array(1, 2, 1),
                    array(2, 1, 1),
                    array(4, 2, 1),
                    array(8, 3, 2));

    } elseif ($data_type != 'text-data-single'  // See comment below
          && ($plot_type == 'ohlc' || $plot_type == 'candlesticks'
             || $plot_type == 'candlesticks2')) {

        # Special case: OHLC and related plots need specific ordering.
        # Note: OHLC and text-data-single is not legal, but we need a
        # valid array for that type to make it past the 2nd level checks.
        # So this array is not used for that case.
        $n_cols = 4;
        $y = array( array(10, 14,  8, 12),
                    array(12, 16, 10, 14),
                    array(14, 18, 12, 16),
                    array(16, 20, 14, 18));

    } elseif ($data_type != 'text-data-single'
               && $data_type != 'data-data-xyz'
               && $plot_type == 'boxes') {

        # Special case: box plots require 5 (or more) Y values per row.
        # Note: box plots and text-data-single, data-data-xyz are not legal
        # but they still need valid data arrays. Below would get the wrong
        # error message if used for those.
        $n_cols = 5;
        $y = array( array(5, 10, 15, 20, 25),
                    array(5, 10, 15, 20, 25),
                    array(5, 10, 15, 20, 25),
                    array(5, 10, 15, 20, 25));

    } elseif ($data_type == 'data-data-xyz') {
        $n_cols = 4;
        # This is actually Y, Z pairs.
        $y = array( array(1, 1, 2, 2),
                    array(2, 1, 3, 4),
                    array(3, 1, 4, 6),
                    array(4, 1, 5, 8));
    } else {
        # Default case
        $y = array( array(1, 3),
                    array(2, 7),
                    array(4, 2),
                    array(8, 7));
        if ($data_type == 'text-data-single') $n_cols = 1;
        else $n_cols = 2;
    }

    # Now fill in the X and Y values:
    for ($i = 0; $i < 4; $i++) {
        if ($need_x) $data[$i][] = $i + 1; // Include explicit X values
        for ($j = 0; $j < $n_cols; $j++) $data[$i][] = $y[$i][$j];
    }
    return $data;
}

# Execute one test case.
#  $name : Name or title of the test case
#  $plot_type, $data_type : Test case plot and data types
#  $expected : True if case should complete, False if it should error out.
# Increments the test, pass, and fail counters based on the results.
function test($name, $plot_type, $data_type, $expected)
{
    global $test_verbose, $n_tests, $n_pass, $n_fail;

    $n_tests++;
    $title = "Test case $n_tests: $name";
    if ($test_verbose) echo "$title\n";

    $data = make_data_array($plot_type, $data_type);

    # See note above on error handler returning - need to check each method
    # here and stop if any returns FALSE, until the last.
    $plot = new phplot_noerr();
    if (!$plot->SetPlotType($plot_type)
     || !$plot->SetDataType($data_type)
     || !$plot->SetPrintImage(False)
     || !$plot->SetDataValues($data)) {
        $n_fail++;
        echo "Failed test case $n_tests: $name - Unexpected early error:\n"
            . "  $plot->test_error_text\n";
        return;
    }
    $plot->DrawGraph();

    # Regular expression match for error: unsupported data type for plot type.
    $errmatch = "Data type '[^']*' is not valid for '[^']*' plots";

    # Examine result - if there was an error message or not - based on
    # the expected outcome.
    $err = $plot->test_error_text;
    if ($expected) {
        # Plot type was expected to accept this data type.
        if (empty($err)) {
            $test_passed = 1;
        } else {
            $test_passed = 0;
            $details = "Plot type did not accept this data type."
                     . " Error is:\n  $err";
        }

    } else {
        # Plot type was expected to NOT accept this data type.
        if (empty($err)) {
            $test_passed = 0;
            $details = "Plot type did not report an error for this data type.";
        } elseif (preg_match("/$errmatch/", $err)) {
            $test_passed = 1;
        } else {
            $test_passed = 0;
            $details = "Plot type reported an error, but not the expected"
                     . " error:\n  $err";
        }
    }

    if ($test_passed) {
        $n_pass++;
    } else {
        $n_fail++;
        echo "Failed test case $n_tests: $name\n$details\n";
    }
}


# ===== Test Cases =====

foreach ($plot_types as $plot_type => $suplist) {
    foreach ($data_types as $index => $data_type) {
        test("Plot type $plot_type, Data type $data_type",
            $plot_type, $data_type, $suplist[$index]);
    }
}

# ======== End of test cases and error reporting ==========

# Option: Use basename of file, or replace with a string.
echo "Plot Type/Data Type results: $n_tests test cases, $n_pass pass, $n_fail fail\n";
if ($n_fail > 0) exit(1);
# PHPlot test suite requires falling off the end, not exit, on success.

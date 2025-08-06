<?php

# PHPlot unit test: bad data arrays, empty rows, edge cases
require_once 'phplot.php';

# True to report test cases and all results:
$test_verbose = False;

# Set this to the name of an existing directory, with trailing separator,
# to write all non-error image files there, as case#.png:
# Note: Due to replacement of error handler, some extra image files will
# be created from tests that PHPlot would otherwise just make an error image.
#$test_debug_images = 'B' . DIRECTORY_SEPARATOR;

# Global test case counters:
$n_tests = 0;
$n_pass = 0;
$n_fail = 0;

# PHP-produced warning or notice message, caught by test():
$php_warning = '';

# Some data arrays which are used for multiple tests:
$data0 = array();
$data1 = array(array());
$data2 = array(array('a'));
$data3 = array(array('a', 1));
$data4 = array(array('a', ''));     // For text-data: 1 row with 1 missing value
$data5 = array(array('a', 1, ''));  // For data-data: 1 row with 1 missing value
$data6 = array(array('a', 1, '', '', '')); // For data-data-error: 1 row missing value
$data7 = array(array('a', 1), array('b'), array('c', 3));
$data8 = array(array('a', 1, '' ,''));  // For data-data-xyz: 1 row, missing value
$data9 = array(array('a', 1, 2, 3), array('b', 4, 5), array('c', 3, 2, 1)); // Unmatched column counts

# Extend the PHPlot class to suppress error images.
# Instead of displaying an error image, the error text is stored
# into a class variable. The test function will check there.
class PHPlot_noerr extends Phplot\Phplot\phplot
{
    public $test_error_text = '';
    function PrintError($message)
    {
        $this->test_error_text = $message;
        return FALSE;
    }
}

# Catch notice and warning messages from PHP:
function catch_error($errno, $errstr, $errfile, $errline)
{
    global $test_verbose, $php_warning;

    $msg = "$errstr\nIn " . basename($errfile) . ":$errline\n";
    if ($test_verbose) echo "Caught error: $msg";
    $php_warning .= $msg;
    return TRUE;
}
set_error_handler('catch_error', E_NOTICE | E_WARNING);


# Execute one test case.
#  $name : Name or title of the test case
#  $data : The data array to use
#  $datatype, $plottype : Data and plot type
#  $expect_fail : True if PHPlot is expected to detect and report an error.
#  $errmatch : If $fail is True, this is a regular expression to match the
#    error message produced by PHPlot.
# Increments the test, pass, and fail counters based on the results.
# Note: The test passes if the the expected result matches the actual
# result, else it fails. "Test fail" is not the same as "PHPlot fail".
# A test case will also fail if PHP displays any warning or notice.
function test($name, $data, $datatype, $plottype, $expect_fail, $errmatch='')
{
    global $test_verbose, $php_warning, $n_tests, $n_pass, $n_fail;
    global $test_debug_images;

    $n_tests++;
    $php_warning = ''; // Initialize empty. Error handler will append.
    $details = '';  // Why it passed or failed.
    if ($test_verbose) echo "Test case $n_tests: $name\n";
    $plot = new phplot_noerr;

    if (empty($test_debug_images)) {
        $plot->SetPrintImage(False); // Don't produce an image if OK
    } else {
        $plot->SetTitle("Case $n_tests: $name");
        $plot->SetOutputFile($test_debug_images . sprintf("%03d", $n_tests)
            . ".png");
        $plot->SetIsInline(True);
    }
    $plot->SetDataType($datatype);
    $plot->SetPlotType($plottype);
    $failed = !$plot->SetDataValues($data) || !$plot->DrawGraph()
               || !empty($plot->test_error_text);
    $fail_message = $plot->test_error_text;

    if ($test_verbose) {
        if ($failed) echo "Result: PHPlot error: '$fail_message'\n";
        else echo "Result: PHPlot graph, no error message\n";
    }

    # Evaluate pass/fail status of the test case:
    if ($expect_fail) {
        if ($failed) {
            if (preg_match("\x07$errmatch\x07i", $fail_message)) {
                $details = "(produced error as expected)\n";
                $test_passed = True;
            } else {
               $details = "  Expecting an error, got a different error\n"
                        . "  Actual message: $fail_message\n"
                        . "  Expected to match: $errmatch\n";
                $test_passed = False;
            }
        } else {
            $details = "  Expecting an error, but produced a graph.\n";
            $test_passed = False;
        }
    } else { // Not expected to fail
        if ($failed) {
            $details = "  Expected a graph, but got this error instead:\n"
                     . "  $fail_message\n";
            $test_passed = False;
        } else {
            $details = "(produced graph as expected)\n";
            $test_passed = True;
        }
    }

    // Fail the test if any notice or warnings were produced:
    if (!empty($php_warning)) {
        if ($test_passed) {
            // Replace the "test passed" message.
            $details = "Test would have passed except for PHP warnings or notices:\n"
                     . $php_warning . "\n";
            $test_passed = False;
        } else {
            $details .= "Test also failed due to PHP warnings or notices:\n"
                     . $php_warning . "\n";
        }
    }

    if ($test_passed) {
        $n_pass++;
        if ($test_verbose) echo "Pass $details";
    } else {
        $n_fail++;
        echo "Failed test case $n_tests: $name\n$details";
    }

    if ($test_verbose) echo "\n";
}

# The test cases:

test('Empty array [not valid]',
     $data0, 'text-data', 'lines', True, 'no data array');

test('Array with 1 empty row array [not valid]',
     $data1, 'text-data', 'lines', True, 'empty data set');

test('row with label only, text-data [valid]',
     $data2, 'text-data', 'lines', False);

test('row with label only, data-data [not valid]',
     $data2, 'data-data', 'lines', True, 'invalid.*data array');

test('row with label and X only, data-data [valid]',
     $data3, 'data-data', 'lines', False);

# data-data-error - values must be in 3s

test('data-data-error with only Y, missing +/-err [not valid]',
     array(array('a', 1, 10)),
     'data-data-error', 'points', True, 'invalid.*data array');

test('data-data-error with Y and +err, missing -err [not valid]',
     array(array('a', 1, 10, 3)),
     'data-data-error', 'points', True, 'invalid.*data array');

# data-data-xyz - values must have Y and Z paired
test('data-data-xyz with only Y, missing Z [not valid]',
     array(array('a', 1, 10)),
     'data-data-xyz', 'bubbles', True, 'invalid.*data array');

# Outer array not conforming to 0-based, sequential integer index:
test('Array with non-numeric index [not valid]',
     array(array('a', 1), 'z'=>array('b', 2), array('c', 3)),
     'text-data', 'lines', True, 'invalid data array');

test('Array with non-sequential index [not valid]',
     array(0=>array('a', 1), 1=>array('b', 2), 3=>array('c', 3)),
     'text-data', 'lines', True, 'invalid data array');

# Row isn't array:
test('Row is NULL not array [not valid]',
     array(array('a', 1), NULL, array('c', 3)),
     'text-data', 'lines', True, 'invalid data array');
test('Row is string not array [not valid]',
     array(array('a', 1), 'hello', array('c', 3)),
     'text-data', 'lines', True, 'invalid data array');

# Data type: text-data-single for pie charts
# Pie charts are still drawn, with no pie, if all data are 0.
test('Pie, text-data-single, no value [not valid]',
     $data2,
     'text-data-single', 'pie', True, 'invalid.*data array');
test('Pie, text-data-single, one row with 0 [valid, empty]',
     array(array('a', 0)),
     'text-data-single', 'pie', False);

# Plot type: OHLC, needs exactly 4 Y values.
test('OHLC, text-data with wrong value count [not valid]',
     array(array('a', 2, 3, 1)),
     'text-data', 'ohlc', True, 'must have 4 values');
test('OHLC, data-data with wrong value count [not valid]',
     array(array('a', 1, 2, 3, 1)),
     'data-data', 'ohlc', True, 'must have 4 values');

# All plot types with no values.
# Except for special cases, all should make an empty plot with no error.
# text-data data type:
test('area plot: no values', $data2, 'text-data', 'area', False);
test('bars plot: no values', $data2, 'text-data', 'bars', False);
#     Special case, requires 5 or more values per row
test('boxes plot: no values', $data2, 'text-data', 'boxes', True,
      'must have 5 or more values');
test('linepoints plot: no values', $data2, 'text-data', 'linepoints', False);
test('lines plot: no values', $data2, 'text-data', 'lines', False);
test('pie plot: no values', $data2, 'text-data', 'pie', False);
#    Special case, requires exactly 4 values per row
test('ohlc plot: no values', $data2, 'text-data', 'ohlc',
      True, 'must have 4 values');
test('points plot: no values', $data2, 'text-data', 'points', False);
test('squared plot: no values', $data2, 'text-data', 'squared', False);
test('squaredarea plot: no values', $data2, 'text-data', 'squaredarea', False);
test('stackedarea plot: no values', $data2, 'text-data', 'stackedarea', False);
test('stackedbars plot: no values', $data2, 'text-data', 'stackedbars', False);
test('stackedsquaredarea plot: no values', $data2, 'text-data', 'stackedsquaredarea', False);
test('thinbarline plot: no values', $data2, 'text-data', 'thinbarline', False);
# data-data data type:
test('area data-data plot: no values', $data3, 'data-data', 'area', False);
#     Special case, requires 5 or more values per row
test('boxes data-data plot: no values', $data3, 'data-data', 'boxes', True,
      'must have 5 or more values');
test('linepoints data-data plot: no values', $data3, 'data-data', 'linepoints', False);
test('lines data-data plot: no values', $data3, 'data-data', 'lines', False);
test('pie data-data plot: no values', $data3, 'data-data', 'pie', False);
#    Special case, requires exactly 4 values per row
test('ohlc data-data plot: no values', $data3, 'data-data', 'ohlc',
      True, 'must have 4 values');
test('points data-data plot: no values', $data3, 'data-data', 'points', False);
test('squared data-data plot: no values', $data3, 'data-data', 'squared', False);
test('squaredarea data-data plot: no values', $data3, 'data-data', 'squaredarea', False);
test('stackedarea data-data plot: no values', $data3, 'data-data', 'stackedarea', False);
test('stackedsquaredarea data-data plot: no values', $data3, 'data-data', 'stackedsquaredarea', False);
test('thinbarline data-data plot: no values', $data3, 'data-data', 'thinbarline', False);
# data-data-error data type:
test('linepoints error plot: no values', $data3, 'data-data-error', 'linepoints', False);
test('lines error plot: no values', $data3, 'data-data-error', 'lines', False);
test('points error plot: no values', $data3, 'data-data-error', 'points', False);
# text-data-yx (swapped) data type:
test('horiz bars plot: no values', $data2, 'text-data-yx', 'bars', False);
test('horiz stackedbars plot: no values', $data2, 'text-data-yx', 'stackedbars', False);
test('horiz thinbarline plot: no values', $data2, 'text-data-yx', 'thinbarline', False);
# data-data-yx (swapped) data type:
test('horiz thinbarline plot: no values', $data3, 'data-data-yx', 'thinbarline', False);
# data-data-xyz (3D) data type:
test('bubbles plot: no values', $data3, 'data-data-xyz', 'bubbles', False);

# All plot types with 1 row with 1 missing value.
# Except for special cases, all should make an empty plot with no error.
# text-data data type:
test('area plot: 1 row/1 missing value', $data4, 'text-data', 'area', False);
test('bars plot: 1 row/1 missing value', $data4, 'text-data', 'bars', False);
#     Special case, requires 5 or more values per row
test('boxes plot: 1 row/1 missing value', $data4, 'text-data', 'boxes', True,
      'must have 5 or more values');
test('linepoints plot: 1 row/1 missing value', $data4, 'text-data', 'linepoints', False);
test('lines plot: 1 row/1 missing value', $data4, 'text-data', 'lines', False);
test('pie plot: 1 row/1 missing value', $data4, 'text-data', 'pie', False);
#    Special case, requires exactly 4 values per row
test('ohlc plot: 1 row/1 missing value', $data4, 'text-data', 'ohlc',
      True, 'must have 4 values');
test('points plot: 1 row/1 missing value', $data4, 'text-data', 'points', False);
test('squared plot: 1 row/1 missing value', $data4, 'text-data', 'squared', False);
test('squaredarea plot: 1 row/1 missing value', $data4, 'text-data', 'squaredarea', False);
test('stackedarea plot: 1 row/1 missing value', $data4, 'text-data', 'stackedarea', False);
test('stackedbars plot: 1 row/1 missing value', $data4, 'text-data', 'stackedbars', False);
test('stackedsquaredarea plot: 1 row/1 missing value', $data4, 'text-data', 'stackedsquaredarea', False);
test('thinbarline plot: 1 row/1 missing value', $data4, 'text-data', 'thinbarline', False);
# data-data data type:
test('area data-data plot: 1 row/1 missing value', $data5, 'data-data', 'area', False);
#     Special case, requires 5 or more values per row
test('boxes data-data plot: 1 row/1 missing value', $data5, 'data-data', 'boxes', True,
      'must have 5 or more values');
test('linepoints data-data plot: 1 row/1 missing value', $data5, 'data-data', 'linepoints', False);
test('lines data-data plot: 1 row/1 missing value', $data5, 'data-data', 'lines', False);
test('pie data-data plot: 1 row/1 missing value', $data5, 'data-data', 'pie', False);
#    Special case, requires exactly 4 values per row
test('ohlc data-data plot: 1 row/1 missing value', $data5, 'data-data', 'ohlc',
      True, 'must have 4 values');
test('points data-data plot: 1 row/1 missing value', $data5, 'data-data', 'points', False);
test('squared data-data plot: 1 row/1 missing value', $data5, 'data-data', 'squared', False);
test('squaredarea data-data plot: 1 row/1 missing value', $data5, 'data-data', 'squaredarea', False);
test('stackedarea data-data plot: 1 row/1 missing value', $data5, 'data-data', 'stackedarea', False);
test('stackedsquaredarea data-data plot: 1 row/1 missing value', $data5, 'data-data', 'stackedsquaredarea', False);
test('thinbarline data-data plot: 1 row/1 missing value', $data5, 'data-data', 'thinbarline', False);
# data-data-error data type:
test('linepoints error plot: 1 row/1 missing value', $data6, 'data-data-error', 'linepoints', False);
test('lines error plot: 1 row/1 missing value', $data6, 'data-data-error', 'lines', False);
test('points error plot: 1 row/1 missing value', $data6, 'data-data-error', 'points', False);
# data-data-xyz (3D) data type:
test('bubbles plot: 1 row/1 missing value pair', $data8, 'data-data-xyz', 'bubbles', False);

# 3 rows, one with missing value, text-data data type:
test('bars plot: 1 of 3 rows without value',
     $data7, 'text-data', 'bars', False);
test('linepoints plot: 1 of 3 rows without value',
     $data7, 'text-data', 'linepoints', False);
test('lines plot: 1 of 3 rows without value',
     $data7, 'text-data', 'lines', False);
test('pie plot: 1 of 3 rows without value',
     $data7, 'text-data', 'pie', False);
test('points plot: 1 of 3 rows without value',
     $data7, 'text-data', 'points', False);
test('squared plot: 1 of 3 rows without value',
     $data7, 'text-data', 'squared', False);
test('stackedbars plot: 1 of 3 rows without value',
     $data7, 'text-data', 'stackedbars', False);
test('thinbarline plot: 1 of 3 rows without value',
     $data7, 'text-data', 'thinbarline', False);
# Special cases with requirements on number of values:
test('area plot: 1 of 3 rows without value',
     $data7, 'text-data', 'area', True, 'must contain the same number');
test('squaredarea plot: 1 of 3 rows without value',
     $data7, 'text-data', 'squaredarea', True, 'must contain the same number');
test('stackedarea plot: 1 of 3 rows without value',
     $data7, 'text-data', 'stackedarea', True, 'must contain the same number');
test('stackedsquaredarea plot: 1 of 3 rows without value',
     $data7, 'text-data', 'stackedsquaredarea', True, 'must contain the same number');
test('ohlc plot: 1 of 3 rows without value',
     $data7, 'text-data', 'ohlc', True, 'must have 4');
test('area plot: different value counts per row',
     $data9, 'text-data', 'area', True, 'must contain the same number');
test('squaredarea plot: different value counts per row',
     $data9, 'text-data', 'squaredarea', True, 'must contain the same number');
test('stackedarea plot: different value counts per row',
     $data9, 'text-data', 'stackedarea', True, 'must contain the same number');
test('stackedsquaredarea plot: different value counts per row',
     $data9, 'text-data', 'stackedsquaredarea', True, 'must contain the same number');

# Empty plot cases without error for plot types requiring specific numbers of values:
# OHLC (after 5.4.0) requires 4 values but accepts empty values and skips that point.
# Test OHLC with one empty row.
test('ohlc plot: 1 row no values',
     array(array('a', '', '', '', '')), 'text-data', 'ohlc', False);
test('ohlc plot: 1 row no values',
     array(array('a', 1, '', '', '', '')), 'data-data', 'ohlc', False);
# Test Box plot with 1 empty row (needs 5 Y values per row).
test('box plot: 1 row no values',
     array(array('a', '', '', '', '', '')), 'text-data', 'boxes', False);
test('box plot: 1 row no values',
     array(array('a', 1, '', '', '', '', '')), 'data-data', 'boxes', False);

# ===== Summarize:
echo "test bad data array results: $n_tests test cases, $n_pass pass, $n_fail fail\n";
if ($n_fail > 0) exit(1);
# PHPlot test suite requires falling off the end, not exit, on success.

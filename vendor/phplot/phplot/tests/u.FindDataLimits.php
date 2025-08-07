<?php
# Unit tests for FindDataLimits, related to bug reports: 2786354, 2786350
# This doesn't output a graph. It checks the behavior of FindDataLimits
# for various data sets.
require_once 'phplot.php';
require_once 'usupport.php';    // Unit test support

# True to report test cases and all results:
$test_verbose = False;

# Global test case counters:
$n_tests = 0;
$n_pass = 0;
$n_fail = 0;

// Extend PHPlot to access protected method and variables.
class PHPlot_test extends Phplot\Phplot\phplot
{
    // CheckDataArray()
    function CALL_CheckDataArray()
    {
        if (method_exists($this, 'CheckDataArray')) $this->CheckDataArray();
    }

    function CALL_FindDataLimits()
    {
        return $this->FindDataLimits();
    }

    // Return an array of min_x, max_x, min_y, max_y converted to float:
    function GET_min_max_x_y()
    {
        return array((float)$this->min_x, (float)$this->max_x,
                     (float)$this->min_y, (float)$this->max_y);
    }

    // Return the version ID constant, if present, else make one up.
   function get_version()
   {
       if (defined('self::version_id')) return self::version_id;
       else return 50000;
   }
}

# Compatibility fix
# Starting at Rel6/SVN 1502, the returned range for implied independent
# variable cases (text-data) changed from 0,NP-1 to 0,NP with
# NP points. The expected results in all the applicable test cases were
# changed to account for this. In order to keep the test working with
# older versions, this function adjusts the expected results to match the
# older versions. (Note: This will not work on some intermediate SVN versions.)
function compat_1($plot, $data_type, &$expected)
{
    if ($plot->get_version() < 60000
          && preg_match('/^text-data/', $data_type)) {
        # Adjust max_x expected result for PHPlot < 6.0.0
        $expected[1]--;
    }
}


# Single test case.
#  $name : Name of this test case
#  $data_type (e.g. 'text-data')
#  $plot_type (only 'stackedbars' is different)
#  $data :  PHPlot data array
#  $expected : Array of 4 elements - expected results min_x max_x min_y max_y
# Returns: Nothing. Tracks pass/fails internally, and reports.
function test($name, $data_type, $plot_type, $data, $expected)
{
    global $test_verbose, $n_tests, $n_pass, $n_fail;

    $n_tests++;
    $title = "Test case $n_tests: $name";
    if ($test_verbose) echo "$title\n";

    $p = new phplot_test(); // See above, for access to protected methods
    $p->SetDataType($data_type);
    $p->SetPlotType($plot_type);
    $p->SetDataValues($data);
    // For PHPlot>5.1.2 (CVS). FindDataLimits requires this.
    $p->CALL_CheckDataArray();

    // Call internal function:
    $p->CALL_FindDataLimits();
    // Get min,max x,y - cast to float for comparing
    $results = $p->GET_min_max_x_y();

    // Backward compatibility fixup(s):
    compat_1($p, $data_type, $expected);

    $error = '';
    if (expect_equal($expected, $results, $title, $error)) {
        $n_pass++;
    } else {
        $n_fail++;
        echo "$error\n";
    }
}


# ===== Test Cases =====
# Expected results (last arg) is array(Xmin, Xmax, Ymin, Ymax)

# Simple baseline case:
test('data-data baseline', 'data-data', 'lines', array(
         array('a', 1, 0, 50, 60, 90),
         array('b', 2, 0, 10,  0, 20),
         array('c', 3, 0, 80, 40, 40)),
     array(1.0, 3.0, 0.0, 90.0));

# Other simple cases:
test('text-data.2', 'text-data', 'lines', array(
         array('a', 1, 100, 100, -200),
         array('b', 2, 100, 200,  300)),
     array(0.0, 2.0, -200.0, 300.0));

test('data-data.3', 'data-data', 'lines', array(
         array('a', 1, 100, 100, -200),
         array('b', 2, 100, 200,  300),
         array('c', 5, 400, 200,  0),
         array('d', 8, 100, 800, 30)),
     array(1.0, 8.0, -200.0, 800.0));

test('text-data.4', 'text-data', 'lines', array(
         array('a', 100),
         array('b', -100),
         array('c', 0)),
     array(0.0, 3.0, -100.0, 100.0));

# Missing data point cases
test('Missing Y data point, text-data', 'text-data', 'lines', array(
         array('a', 100, 100, 50),
         array('b', 100, 200, ''),
         array('c', 100, '',  300)),
     array(0.0, 3.0, 50.0, 300.0));

test('Missing Y data point, data-data', 'data-data', 'lines', array(
         array('a', 1, '', 200),
         array('b', 2, 50, 75)),
     array(1.0, 2.0, 50.0, 200.0));

test('Missing all Y data in a row, data-data', 'data-data', 'lines', array(
         array('a', 1, ''),
         array('b', 2, '')),
     array(1.0, 2.0, 0.0, 0.0));

test('Missing all Y data for one X, data-data, 2 Y per X', 'data-data', 'lines', array(
         array('a', 1, 10, 20),
         array('b', 2, '', ''),
         array('c', 3, 20, 10)),
     array(1.0, 3.0, 10.0, 20.0));

test('Missing Y data for only X', 'data-data', 'lines', array(
         array('a', 1, 10),
         array('b', 2, ''),
         array('c', 3, 20)),
     array(1.0, 3.0, 10.0, 20.0));

# Stackedbars cases
test('Stackedbars.1', 'text-data', 'stackedbars', array(
         array('a', 10, 20, 5, 2),
         array('b', 40, 20, 10, 5)),
     array(0.0, 2.0, 0.0, 75.0));

# Data-data-error: Bug report 2786354
test('data-data-error.1', 'data-data-error', 'lines', array(
         array('A', 1, 10,  5,  5,   25, 2, 2),
         array('B', 2, 20, 10,  5,   25, 2, 2),
         array('C', 3, 30, 15, 15,    5, 2, 2),
         array('D', 4, 40,  0,  5,   25, 2, 10)),
     array(1.0, 4.0, 3.0, 45.0));

test('data-data-error.2', 'data-data-error', 'lines', array(
         array('A', 1, 100, 5, 5, 200, 5, 5, 300, 5, 5),
         array('B', 2, 120, 5, 5, 220, 5, 5, 320, 10, 10),
         array('C', 3, 140, 5, 5, 240, 80, 80, 340, 5, 5)),
     array(1.0, 3.0, 95.0, 345.0));

test('data-data-error.3', 'data-data-error', 'lines', array(
         array('a', 1, 10, 5, 5, 100, 50, 70, 200, 5, 5)),
     array(1.0, 1.0, 5.0, 205.0));

# ======== End of test cases and error reporting ==========
echo "FindDataLimits results: $n_tests test cases, $n_pass pass, $n_fail fail\n";
if ($n_fail > 0) exit(1);
# PHPlot test suite requires falling off the end, not exit, on success.

<?php

# PHPlot test: Data Type Aliases
# This test does not output any images. It creates pairs of images as
# data, one for the alias and one for the primary data type, and sees if
# they are the same.
require_once 'phplot.php';

# True to report test cases and all results:
$test_verbose = False;
# True to save the images to files, used for checking the test.
$test_save = False;

# Global test case counters:
$n_tests = 0;
$n_pass = 0;
$n_fail = 0;

# Test cases:
$cases = array(
  0 => array(
           'data_type' => 'text-linear',
           'like' => 'text-data',
        ),
  1 => array(
           'data_type' => 'linear-linear',
           'like' => 'data-data',
        ),
  2 => array(
           'data_type' => 'linear-linear-error',
           'like' => 'data-data-error',
        ),
  3 => array(
           'data_type' => 'text-data-pie',
           'like' => 'text-data-single',
           'pie' => TRUE,
        ),
  4 => array(
           'data_type' => 'data-data-error-yx',
           'like' => 'data-data-yx-error',
        ),
);
$n_cases = count($cases);

# ====== Test functions ======

# Test one $case:
function test_case($case)
{
    global $cases, $test_verbose, $n_tests, $n_pass, $n_fail, $test_save;

    $n_tests++;
    extract($cases[$case]);

    $title = "Test case $n_tests: $data_type (should match $like)";

    # Make a data array that is valid (but not necessarily reasonable)
    # for any data type. One works for all except pie chart.
    if (!empty($pie)) {
        $plot_type = 'pie';
        $data = array(array('', 1), array('', 1), array('', 2));
    } else {
        $plot_type = 'lines';
        # Valid for text-data, data-data, and data-data-error:
        $data = array(array('', 1, 2, 2, 2),
                      array('', 2, 4, 1, 1),
                      array('', 3, 5, 2, 2));
    }

    $p1 = new Phplot\Phplot\phplot(400, 300);
    $p1->SetFailureImage(False);
    $p1->SetPrintImage(False);
    $p1->SetDataValues($data);
    $p1->SetDataType($data_type); // Alias data type
    $p1->SetPlotType($plot_type);
    $p1->DrawGraph();
    $p1_image = $p1->EncodeImage('raw');
    if ($test_save) file_put_contents("dta-{$case}a_$data_type.png", $p1_image);

    $p2 = new Phplot\Phplot\phplot(400, 300);
    $p2->SetFailureImage(False);
    $p2->SetPrintImage(False);
    $p2->SetDataValues($data);
    $p2->SetDataType($like);  // Base data type - alias should match this
    $p2->SetPlotType($plot_type);
    $p2->DrawGraph();
    $p2_image = $p2->EncodeImage('raw');
    if ($test_save) file_put_contents("dta-{$case}b_$like.png", $p2_image);

    if ($p1_image == $p2_image) {
        $n_pass++;
        if ($test_verbose) echo "Pass: $title\n";
    } else {
        $n_fail++;
        echo "FAIL - Image Mismatch: $title\n";
    }
}

# ===== Test Cases =====

# Test all cases:
for ($case = 0; $case < $n_cases; $case++) test_case($case);

# ======== End of test cases and error reporting ==========

# Option: Use basename of file, or replace with a string.
echo basename(__FILE__)
    . " results: $n_tests test cases, $n_pass pass, $n_fail fail\n";
if ($n_fail > 0) exit(1);
# PHPlot test suite requires falling off the end, not exit, on success.

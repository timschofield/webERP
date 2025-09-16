<?php

# PHPlot unit test - Serialize/unserialize
# This compares a plot with the same plot produced from a serialized/
# unserialized copy of the PHPlot object.

require_once 'phplot.php';
require_once 'makedata.php'; // For generating data arrays.

# True to report test cases and all results:
$test_verbose = False;
# True to save the plots to files in the results directory:
$save_plots = False;

# Global test case counters:
$n_tests = 0;
$n_pass = 0;
$n_fail = 0;

# Returns a PHPlot object, ready for DrawGraph:
function make_plot($plot_type, $data_type, $nx, $ny)
{
    $plot = new Phplot\Phplot\phplot(1280, 1024);
    $plot->SetPrintImage(False);
    $plot->SetFailureImage(False);
    $plot->SetDataType($data_type);
    $plot->SetDataValues(make_data_array($plot_type, $data_type, $nx,$ny, 100));
    $plot->SetPlotType($plot_type);
    $plot->SetTitle("Serialize/Unserialize Tests\n$plot_type - $data_type");
    $plot->SetXTickIncrement(5);
    $plot->SetYTickIncrement(10);
    $plot->SetPlotBorderType('full');
    $plot->SetDrawXGrid(True);
    $plot->SetDrawYGrid(True);
    $plot->SetXTitle('X Axis Title');
    $plot->SetYTitle('Y Axis Title');
    # Select data labels or tick labels based on data type:
    if ($data_type == 'data-data') {
        $plot->SetXDataLabelPos('none');
        $plot->SetXTickLabelPos('plotdown');
        $plot->SetXTickPos('plotdown');
    } elseif ($data_type == 'text-data') {
        $plot->SetXDataLabelPos('plotdown');
        $plot->SetXTickLabelPos('none');
        $plot->SetXTickPos('none');
    } elseif ($data_type == 'data-data-yx') {
        $plot->SetYDataLabelPos('none');
        $plot->SetYTickLabelPos('plotleft');
        $plot->SetYTickPos('plotleft');
    } elseif ($data_type == 'text-data-yx') {
        $plot->SetYDataLabelPos('plotleft');
        $plot->SetYTickLabelPos('none');
        $plot->SetYTickPos('none');
    }
    return $plot;
}

# Helper function - save plot to file
function save_file($prefix, $num, $suffix, $data)
{
    global $test_verbose, $save_plots;

    if (!$save_plots) return;
    $filename = $prefix . '-' . $num . $suffix . '.png';
    $r = getenv("RESULTDIR");
    if (empty($r)) $pathname = $filename;
    else $pathname = $r . DIRECTORY_SEPARATOR . $filename;
    file_put_contents($pathname, $data);
    if ($test_verbose) echo "Saved plot to $filename in results directory\n";
}

# Run a test case:

function test($plot_type, $data_type, $nx, $ny)
{
    global $test_verbose, $n_tests, $n_pass, $n_fail;

    $n_tests++;
    $title = "Test case $n_tests: Serialize ($plot_type, $data_type)";
    if ($test_verbose) echo "$title\n";

    # Plot 1 is the baseline.
    $plot1 = make_plot($plot_type, $data_type, $nx, $ny);
    $plot1->DrawGraph();
    $image1 = $plot1->EncodeImage('raw');
    save_file('serialize', $n_tests, 'a', $image1);
    $sig1 = md5($image1);
    unset($plot1);

    # Plot 2 is serialized. The object should still be good after serialization.
    $plot2 = make_plot($plot_type, $data_type, $nx, $ny);
    $serial2 = serialize($plot2);
    $plot2->DrawGraph();
    $image2 = $plot2->EncodeImage('raw');
    save_file('serialize', $n_tests, 'b', $image2);
    $sig2 = md5($image2);
    unset($plot2);

    # Plot 3 results from un-serializing plot2.
    $plot3 = unserialize($serial2);
    $plot3->DrawGraph();
    $image3 = $plot3->EncodeImage('raw');
    save_file('serialize', $n_tests, 'c', $image3);
    $sig3 = md5($image3);
    unset($plot3);

    # All 3 plot signatures should be equal:
    if ($sig1 == $sig2 && $sig2 == $sig3) {
        $n_pass++;
    } else {
        $n_fail++;
        fwrite(STDERR, "serialize test failed ($plot_type, $data_type)\n"
                 .     "           base sig = $sig1\n"
                 .     " post-serialize sig = $sig2\n"
                 .     "    unserialize sig = $sig3\n");
    }
}

# ===== Test Cases =====
test('linepoints', 'data-data', 25, 3);
test('pie', 'text-data-single', 7, 1);
test('bars', 'text-data', 4, 5);
test('stackedarea', 'data-data', 20, 10);
test('stackedbars', 'text-data-yx', 10, 5);
test('thinbarline', 'data-data-yx', 100, 1);

# ======== End of test cases and error reporting ==========
echo basename(__FILE__)
    . " results: $n_tests test cases, $n_pass pass, $n_fail fail\n";
if ($n_fail > 0) exit(1);
# PHPlot test suite requires falling off the end, not exit, on success.

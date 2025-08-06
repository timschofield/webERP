<?php

# PHPlot unit test: Matching plot types with 1 data set.
# This tests that the following plot type pairs make identical plots when
# there is only a single data set:
#   'area' and 'stackedarea'
#   'bars' and 'stackedbars'
#   'squarefilled' and 'stackedsquarefilled'
require_once 'phplot.php';
require_once 'phplot_truecolor.php';

// Save the plot images to files? "never", "always", or "onfailure".
$save_plots = "onfailure";

// Make a data array, with random but repeatable results:
mt_srand(16); // Any number will do
$data = array();
for ($i = 0; $i < 50; $i++) {
    $data[] = array('', mt_rand(0, 1000) / 10.0);
}

// Wrapper for simple plot type test. Invoke the object as a function
// to return the image as a raw data string.
class testplot extends Phplot\Phplot\Phplot_truecolor
{
    function __construct($plot_type, $data, $data_type)
    {
        global $data;
        parent::__construct(800, 600);
        $this->SetFailureImage(False);
        $this->SetDataType($data_type);
        $this->SetDataValues($data);
        $this->SetPlotType($plot_type);
        $this->SetPrintImage(False);
        $this->DrawGraph();
    }

    function __invoke()
    {
        return $this->EncodeImage('raw');
    }
}

function compare_plots($type1, $type2, $horizontal = FALSE)
{
    global $data, $save_plots;

    if ($horizontal) {
        $data_type = 'text-data-yx';
        $word = 'horizontal ';
        $filesuffix = '.horiz';
    } else {
        $data_type = 'text-data';
        $word = '';
        $filesuffix = '';
    }
    $title = "Single data set plot compare: $word$type1 vs $word$type2:";
    $p1 = new testplot($type1, $data, $data_type);
    $p2 = new testplot($type2, $data, $data_type);

    $pass = ($p1() == $p2());
    if ($pass) echo "$title OK\n";
    else echo "$title FAILED\n";
    if ($save_plots == 'always' || ($save_plots == 'onfailure' && !$pass)) {
        $filename = "matchtypes.$type1$filesuffix.png";
        file_put_contents($filename, $p1());
        echo "  $type1 image saved to: $filename\n";
        $filename = "matchtypes.$type2$filesuffix.png";
        file_put_contents($filename, $p2());
        echo "  $type2 image saved to: $filename\n";
    }
    return $pass;
}

$failed = 0;
if (!compare_plots('area', 'stackedarea')) $failed++;
if (!compare_plots('bars', 'stackedbars')) $failed++;
if (!compare_plots('bars', 'stackedbars', TRUE)) $failed++;
if (!compare_plots('squaredarea', 'stackedsquaredarea')) $failed++;
if ($failed > 0) exit(1); // For the test driver. If pass, must not use exit().

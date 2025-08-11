<?php

# PHPlot Example: Custom label formatting using row and column
# This example produces a linepoints plot with only the maximum Y value
# for each X value labeled. This is an example of a custom label
# formatting with access to the data array position.
# This requires PHPlot > 5.7.0
require_once 'phplot.php';

# Build a data array. The values are psuedo-random integers, but the first
# and last rows have all Y=0. Return the completed data array.
function make_data_array($n_rows, $n_y_per_row)
{
    mt_srand(10); // For repeatable results
    $data[0] = array('', 0) + array_fill(2, $n_y_per_row, 0);
    for ($i = 1; $i < $n_rows - 1; $i++) {
        $row = array('', $i);
        for ($j = 0; $j < $n_y_per_row; $j++) $row[] = mt_rand(0, 999);
        $data[] = $row;
    }
    if ($n_rows > 1)
        $data[] = array('', $n_rows - 1) + array_fill(2, $n_y_per_row, 0);
    return $data;
}

# Find the index of the largest Y for each X in the data array.
# Build an array $max_indexes such that $max_indexes[$row]=$column means
# the $column-th Y value is the largest in row $row (where $row and $column
# are zero-based). However, if max Y<=0 in the row, mark this with $column=-1
# to prevent labeling. Returns the array $max_indexes.
function find_max_indexes($data)
{
    $max_indexes = array();
    foreach ($data as $row) {
        $max_index = 0;
        $max_y = $row[2]; // Skip label and X value
        // Process remaining values in the row:
        for ($j = 3; $j < count($row); $j++) {
            if ($row[$j] > $max_y) {
                $max_y = $row[$j];
                $max_index = $j - 2; // Offset by 2 for label and X value
            }
        }
        if ($max_y <= 0) $max_index = -1; // Will suppress the label
        $max_indexes[] = $max_index;
    }
    return $max_indexes;
}

# Custom label formatting function: Return an empty string, unless this is
# the largest value in the row.
function fmt_label($value, $maxes, $row, $column)
{
    if ($maxes[$row] == $column) return $value;
    return "";
}

# Get the data array with 11 rows, 6 values per row:
$data = make_data_array(11, 6);
# Process the data array to find the largest Y per row:
$max_indexes = find_max_indexes($data);

# Now plot the data:
$plot = new Phplot\Phplot\phplot(800, 600);
$plot->SetImageBorderType('plain'); // For presentation in the manual
$plot->SetPlotType('linepoints');
$plot->SetDataType('data-data');
$plot->SetDataValues($data);
$plot->SetTitle('Linepoints plot with only max Y values labeled');
$plot->SetYDataLabelPos('plotin');
$plot->SetYDataLabelType('custom', 'fmt_label', $max_indexes);
$plot->SetLineStyles('solid');
$plot->SetYTickIncrement(100);
$plot->DrawGraph();

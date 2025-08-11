<?php
# PHPlot Example: OHLC (Financial) plot, basic lines, using
# external data file, text-data format.
define('DATAFILE', __DIR__ . '/ohlcdata.csv'); // External data file
require_once __DIR__ . '/../../../src/phplot.php';

/*
  Read historical price data from a CSV data downloaded from Yahoo! Finance.
  The first line is a header which must contain: Date,Open,High,Low,Close[...]
  Each additional line has a date (YYYY-MM-DD), then 4 price values.
  The rows have to be sorted by date, because the original is reversed.
  This version of the function uses the date as a label, and returns a
  text-data (implied X) PHPlot data array.
*/
function read_prices_text_data($filename)
{
    $f = fopen($filename, 'r');
    if (!$f) {
        fwrite(STDERR, "Failed to open data file: $filename\n");
        return FALSE;
    }
    // Read and check the file header.
    $row = fgetcsv($f);
    if ($row === FALSE || $row[0] != 'Date' || $row[1] != 'Open'
            || $row[2] != 'High' || $row[3] != 'Low' || $row[4] != 'Close') {
        fwrite(STDERR, "Incorrect header in: $filename\n");
        return FALSE;
    }
    // Read the rest of the file into array keyed by date for sorting.
    while ($r = fgetcsv($f)) {
        $d[$r[0]] = array($r[1], $r[2], $r[3], $r[4]);
    }
    fclose($f);
    ksort($d);
    // Convert to a PHPlot data array with label and 4 values per row.
    foreach ($d as $date => $r) {
        $data[] = array($date, $r[0], $r[1], $r[2], $r[3]);
    }
    return $data;
}

$plot = new Phplot\Phplot\phplot(800, 600);
$plot->SetImageBorderType('plain'); // Improves presentation in the manual
$plot->SetTitle("OHLC (Open/High/Low/Close) Financial Plot\nMSFT Q1 2009");
$plot->SetDataType('text-data');
$plot->SetDataValues(read_prices_text_data(DATAFILE));
$plot->SetPlotType('ohlc');
$plot->SetDataColors('black');
$plot->SetXLabelAngle(90);
$plot->SetXTickPos('none');
if (method_exists($plot, 'TuneYAutoRange'))
    $plot->TuneYAutoRange(0); // Suppress Y zero magnet (PHPlot >= 6.0.0)
$plot->DrawGraph();

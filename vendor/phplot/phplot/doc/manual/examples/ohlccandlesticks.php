<?php
# PHPlot Example: OHLC (Financial) plot, Candlesticks plot, using
# external data file, data-data format with date-formatted labels.
define('DATAFILE', __DIR__ . '/ohlcdata.csv'); // External data file
require_once __DIR__ . '/../../../src/phplot.php';

/*
  Read historical price data from a CSV data downloaded from Yahoo! Finance.
  The first line is a header which must contain: Date,Open,High,Low,Close[...]
  Each additional line has a date (YYYY-MM-DD), then 4 price values.
  Convert to PHPlot data-data data array with empty labels and time_t X
  values and return the data array.
*/
function read_prices_data_data($filename)
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
    // Read the rest of the file and convert.
    while ($d = fgetcsv($f)) {
        $data[] = array('', strtotime($d[0]), $d[1], $d[2], $d[3], $d[4]);
    }
    fclose($f);
    return $data;
}

$plot = new Phplot\Phplot\phplot(800, 600);
$plot->SetImageBorderType('plain'); // Improves presentation in the manual
$plot->SetTitle("Candlesticks Financial Plot (data-data)\nMSFT Q1 2009");
$plot->SetDataType('data-data');
$plot->SetDataValues(read_prices_data_data(DATAFILE));
$plot->SetPlotType('candlesticks');
$plot->SetDataColors(array('SlateBlue', 'black', 'SlateBlue', 'black'));
$plot->SetXLabelAngle(90);
$plot->SetXLabelType('time', '%Y-%m-%d');
$plot->SetXTickIncrement(7*24*60*60); // 1 week interval
if (method_exists($plot, 'TuneYAutoRange'))
    $plot->TuneYAutoRange(0); // Suppress Y zero magnet (PHPlot >= 6.0.0)
$plot->DrawGraph();

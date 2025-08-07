<?php
# OHLC Plot with skipped values
# Set $plottype to 'ohlc' (default), 'candlesticks', or 'candlesticks2',
# Set $datatype to 'text-data' (default) or 'data-data',
# then call this script.
require_once 'phplot.php';

if (!isset($plottype)) $plottype = 'ohlc';
if (!isset($datatype)) $datatype = 'text-data';

# Open, High, Low, Close
if ($datatype == 'text-data') {
    $data = array(
        array('06/01', '', '', '', ''),
        array('06/02', 100, 110,  80,  90),
        array('06/03',  90, 100,  80,  90),
        array('06/04',  90,  90,  70,  80),
        array('06/05',  80, 120,  80, 110),
        array('06/06', 110, 110, 110, 110),
        array('06/07', '', '', '', ''),
        array('06/08', '', '', '', ''),
        array('06/09', 110, 150, 100, 140),
    );

} elseif ($datatype == 'data-data') {
    $data = array(
        array('06/01', 1,  '', '', '', ''),
        array('06/02', 2,  100, 110,  90,  90),
        array('06/03', 3,   90, 100,  80,  90),
        array('06/04', 4,   90,  90,  70,  80),
        array('06/05', 5,   80, 120,  80, 110),
        array('06/06', 6,  110, 110, 110, 110),
        array('06/07', 7,  '', '', '', ''),
        array('06/08', 8,  '', '', '', ''),
        array('06/09', 9,  110, 120, 110, 120),
    );
}

$p = new Phplot\Phplot\phplot(800, 600);
$p->SetTitle("$plottype ($datatype) with missing points");
$p->SetDataType($datatype);
$p->SetDataValues($data);
$p->SetXTickPos('none');
$p->SetPlotType($plottype);
$p->DrawGraph();

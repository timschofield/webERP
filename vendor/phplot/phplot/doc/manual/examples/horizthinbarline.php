<?php
# PHPlot example - horizontal thinbarline plot (impulse plot)
require_once __DIR__ . '/../../../src/phplot.php';
$data = array(
    array('',  79, 33.18), array('',  13, 22.62), array('',  71, 41.18),
    array('',   8, 14.72), array('',  48, 49.92), array('',  46, 49.68),
    array('',  90, 18.00), array('',  15, 25.50), array('',  73, 39.42),
    array('',  30, 42.00), array('',  24, 36.48), array('',  85, 25.50),
    array('',  14, 24.08), array('',   3,  5.82), array('',  98,  3.92),
    array('',  39, 47.58), array('',  70, 42.00), array('',  16, 26.88),
    array('',  81, 30.78), array('',  40, 48.00), array('',  44, 49.28),
);
$plot = new Phplot\Phplot\phplot(800, 400);
$plot->SetImageBorderType('plain'); // Improves presentation in the manual
$plot->SetUseTTF(True);
$plot->SetTitle('Experimental Results');
$plot->SetXTitle('Density (g/cm&#179;)'); // 179=superscript 3
$plot->SetYTitle('Temperature (&#176;C)'); // 176=degrees
$plot->SetPlotType('thinbarline');
$plot->SetDataType('data-data-yx');
$plot->SetDataValues($data);
$plot->SetPlotAreaWorld(0, 0, 50, 100);
$plot->SetLineWidths(4);
$plot->DrawGraph();

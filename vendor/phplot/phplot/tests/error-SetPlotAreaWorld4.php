<?php

# Testing PHPlot - Bad data range with SetPlotAreaWorld
# See the script named below for details
$subtitle = 'Xmin>Data_Max, Xmax unset'; // Subtitle for plot
$spaw = array(200, 20, NULL, 200); // Args to SetPlotAreaWorld
require 'error-SetPlotAreaWorld.php';

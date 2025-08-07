<?php

# Testing PHPlot - Bad data range with SetPlotAreaWorld
# See the script named below for details
$subtitle = 'Ymin>Data_Max, Ymax unset'; // Subtitle for plot
$spaw = array(20, 200, 200, NULL); // Args to SetPlotAreaWorld
require 'error-SetPlotAreaWorld.php';

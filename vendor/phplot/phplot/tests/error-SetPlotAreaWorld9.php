<?php

# Testing PHPlot - Bad data range with SetPlotAreaWorld
# See the script named below for details
$subtitle = 'Ymin==Ymax, Xmin Xmax unset'; // Subtitle for plot
$spaw = array(NULL, 100, NULL, 100); // Args to SetPlotAreaWorld
require 'error-SetPlotAreaWorld.php';

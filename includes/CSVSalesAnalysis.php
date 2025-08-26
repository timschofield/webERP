<?php
function stripcomma($str) { //because we're using comma as a delimiter
	return str_replace(",","",$str);
}

$fp = fopen( $_SESSION['reports_dir'] . "/SalesAnalysis.csv", "w");


while ($MyRow = DB_fetch_row($Result)){

/*First off check that at least one of the columns of data has some none zero amounts */
	$ThisLineHasOutput=false;   /*assume no output to start with */
	$NumberOfFields = DB_num_rows($ColsResult);

	for ($i=3; $i<=$NumberOfFields+7; $i++) {
		if (abs($MyRow[$i])>0.009){
			$ThisLineHasOutput = true;
		}
	}
	if ($ThisLineHasOutput==true){
		$Line='';
		for ($i=0;$i<=$NumberOfFields+7;$i++){
			if (isset($MyRow[$i])){
				if ($i>0){
					$Line.=',';
				}
				$Line.=stripcomma($MyRow[$i]);
			}
		}
		fputs($fp, $Line."\n");
	}
}
fclose($fp);

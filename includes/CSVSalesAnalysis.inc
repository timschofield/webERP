<?php
/* $Revision: 1.3 $ */
function stripcomma($str) { //because we're using comma as a delimiter
	return str_replace(",","",$str);
}

$fp = fopen( $_SESSION['reports_dir'] . "/SalesAnalysis.csv", "w");


While ($myrow = DB_fetch_row($result)){

/*First off check that at least one of the columns of data has some none zero amounts */
	$ThisLineHasOutput=False;   /*assume no output to start with */
	$NumberOfFields = DB_num_rows($ColsResult);

	for ($i=3; $i<=$NumberOfFields+7; $i++) {
		if (abs($myrow[$i])>0.009){
			$ThisLineHasOutput = True;
		}
	}
	if ($ThisLineHasOutput==True){
		$line='';
		for ($i=0;$i<=$NumberOfFields+7;$i++){
			if (isset($myrow[$i])){
				if ($i>0){
					$line.=',';
				}
				$line.=stripcomma($myrow[$i]);
			}
		}
		fputs($fp, $line."\n");
	}
}
fclose($fp);
?>

<?php

/*Code to replace [Variables] in Message LineText with the value of the variable itself */

	$PositionPointer = 0;
	$NewLineText ='';
	/* now get each occurence of [ in the line */
	while (mb_strpos ($MsgLineText,'[',$PositionPointer)!=false){
		$LastPositionPointer = $PositionPointer;
		$PositionPointer = mb_strpos ($MsgLineText,'[',$PositionPointer);

		$NewLineText = $NewLineText .  mb_substr($MsgLineText,$LastPositionPointer,$PositionPointer-$LastPositionPointer);

		$LastPositionPointer = $PositionPointer;
		$PositionPointer = mb_strpos ($MsgLineText,']',$PositionPointer);

		$VariableName = mb_substr($MsgLineText,$LastPositionPointer+1,$PositionPointer-$LastPositionPointer-1);

		$PositionPointer++;

		echo '<BR>' . __('The variable') . ' - ' . $VariableName . ' ' . __('is set to') . ' ' . $$VariableName;

		$NewLineText = $NewLineText . $$VariableName;

	}
	/* now add the text from the last ] to the end of the line */
	$LastPositionPointer = $PositionPointer;
	$NewLineText = $NewLineText .  mb_substr($MsgLineText,$LastPositionPointer);

	echo "<BR>$NewLineText";

	fputs($fp, $NewLineText . "\n");

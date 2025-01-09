<?php
/**********************************************************************
*
* KL RICARD Functions related to Audit Scripts
*
***********************************************************************/

function RecordRunningTime($Title, $UserName){

    if (isset($Title)) {
        $TitleScriptRunning = $Title;
    }else{
        $TitleScriptRunning = "Undefined title";
    }
    
    $Time = explode(' ', $_SESSION['ScriptStartTime']);
    $BeginTime = $Time[1] + $Time[0];
    
    $Time = microtime();
    $Time = explode(" ", $Time);
    $EndTime = $Time[1] + $Time[0];
    $RunningTime = round(($EndTime - $BeginTime),5);
    
    $AuditSQL = "INSERT INTO auditscripts (executiondate,
                        secondsrunning,
                        userid,
                        scripttitle)
                VALUES('" . Date('Y-m-d H:i:s') . "',
                    '" . $RunningTime . "',
                    '" . trim($UserName) . "',
                    '" . DB_escape_string($TitleScriptRunning) . "')";
    $Result = DB_query($AuditSQL);
    
}

?>

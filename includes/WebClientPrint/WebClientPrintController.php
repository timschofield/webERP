<?php

//*********************************
// IMPORTANT NOTE 
// 1. In this sample we store users related stuff (like
// the list of printers and whether they have the WCPP 
// client utility installed) in the wcpcache folder BUT 
// you can change it to another different storage (like a DB)!
// which will be required in Load Balacing scenarios
// 
// 2. If your website requires user authentication, then
// THIS FILE MUST be set to ALLOW ANONYMOUS access!!!
// 
//*********************************

include 'WebClientPrint.php';
use Neodynamic\SDK\Web\WebClientPrint;

//IMPORTANT SETTINGS:
//===================
//Set wcpcache folder RELATIVE to WebClientPrint.php file
//FILE WRITE permission on this folder is required!!!
WebClientPrint::$wcpCacheFolder = getcwd().'/wcpcache/';

if (file_exists(WebClientPrint::$wcpCacheFolder) == false) {
    //create wcpcache folder
    $old_umask = umask(0);
    mkdir(WebClientPrint::$wcpCacheFolder, 0777);
    umask($old_umask);
}

//===================

// Clean built-in Cache
// NOTE: Remove it if you implement your own cache system
WebClientPrint::cacheClean(30); //in minutes


// Process WebClientPrint Request

$urlParts = parse_url($_SERVER['REQUEST_URI']);
if (isset($urlParts['query'])){
    $query = $urlParts['query'];
    parse_str($query, $qs);
    
    //get session id from querystring if any
    $sid = NULL;
    if (isset($qs[WebClientPrint::SID])){
        $sid = $qs[WebClientPrint::SID];
    }
    
    try{
        //get request type
        $reqType = WebClientPrint::GetProcessRequestType($query);
        
        if($reqType == WebClientPrint::GenPrintScript ||
           $reqType == WebClientPrint::GenWcppDetectScript){
            //Let WebClientPrint to generate the requested script
            
            //Get Absolute URL of this file
            $currentAbsoluteURL = (@$_SERVER["HTTPS"] == "on") ? "https://" : "http://";
            $currentAbsoluteURL .= $_SERVER["SERVER_NAME"];
            if($_SERVER["SERVER_PORT"] != "80" && $_SERVER["SERVER_PORT"] != "443")
            {
                $currentAbsoluteURL .= ":".$_SERVER["SERVER_PORT"];
            } 
            $currentAbsoluteURL .= $_SERVER["REQUEST_URI"];
            $currentAbsoluteURL = substr($currentAbsoluteURL, 0, strrpos($currentAbsoluteURL, '?'));
            
            ob_start();
            ob_clean();
            header('Content-type: text/javascript');
            echo WebClientPrint::generateScript($currentAbsoluteURL, $query);
            return;
        } 
        else if ($reqType == WebClientPrint::ClientSetWcppVersion)
        {
            //This request is a ping from the WCPP utility
            //so store the session ID indicating this user has the WCPP installed
            //also store the WCPP Version if available
            if(isset($qs[WebClientPrint::WCPP_SET_VERSION]) && strlen($qs[WebClientPrint::WCPP_SET_VERSION]) > 0){
                WebClientPrint::cacheAdd($sid, WebClientPrint::WCP_CACHE_WCPP_VER, $qs[WebClientPrint::WCPP_SET_VERSION]);
            }
            return;
        }
        else if ($reqType == WebClientPrint::ClientSetInstalledPrinters)
        {
            //WCPP Utility is sending the installed printers at client side
            //so store this info with the specified session ID
            WebClientPrint::cacheAdd($sid, WebClientPrint::WCP_CACHE_PRINTERS, strlen($qs[WebClientPrint::WCPP_SET_PRINTERS]) > 0 ? $qs[WebClientPrint::WCPP_SET_PRINTERS] : '');
            return;
        }
        else if ($reqType == WebClientPrint::ClientSetInstalledPrintersInfo)
        {
            //WCPP Utility is sending the installed printers at client side with detailed info
            //so store this info with the specified session ID
            //Printers Info is in JSON format
            $printersInfo = $_POST['printersInfoContent'];
            
            WebClientPrint::cacheAdd($sid, WebClientPrint::WCP_CACHE_PRINTERSINFO, $printersInfo);
            return;
        }
        else if ($reqType == WebClientPrint::ClientGetWcppVersion)
        {
            //return the WCPP version for the specified Session ID (sid) if any
            ob_start();
            ob_clean();
            header('Content-type: text/plain');
            echo WebClientPrint::cacheGet($sid, WebClientPrint::WCP_CACHE_WCPP_VER);
            return;    
        }
        else if ($reqType == WebClientPrint::ClientGetInstalledPrinters)
        {
            //return the installed printers for the specified Session ID (sid) if any
            ob_start();
            ob_clean();
            header('Content-type: text/plain');
            echo base64_decode(WebClientPrint::cacheGet($sid, WebClientPrint::WCP_CACHE_PRINTERS));
            return;
        }    
        else if ($reqType == WebClientPrint::ClientGetInstalledPrintersInfo)
        {
            //return the installed printers with detailed info for the specified Session ID (sid) if any
            ob_start();
            ob_clean();
            header('Content-type: text/plain');
            echo base64_decode(WebClientPrint::cacheGet($sid, WebClientPrint::WCP_CACHE_PRINTERSINFO));
            return;
        }    
    }
    catch (Exception $ex)
    {
        throw $ex;
    }
    
} 
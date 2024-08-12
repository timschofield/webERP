<?php

namespace Neodynamic\SDK\Web;
use Exception;
use ZipArchive;

// Setting WebClientPrint
WebClientPrint::$licenseOwner = 'PT Bumi Biru - 1 WebApp Lic - 1 WebServer Lic';
WebClientPrint::$licenseKey = 'C6C5F6CDE8CF3D055C65F9FF24C9550EFA8BEC9B';

//IMPORTANT SETTINGS:
//===================
//Set ABSOLUTE URL to WebClientPrint.php file
$RootPath = dirname(htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8'));
if (isset($DirectoryLevelsDeep)){
	for ($i=0;$i<$DirectoryLevelsDeep;$i++){
		$RootPath = mb_substr($RootPath,0, strrpos($RootPath,'/'));
	}
}
if ($RootPath == "/" OR $RootPath == "\\") {
	$RootPath = "";
}
WebClientPrint::$webClientPrintAbsoluteUrl = Utils::getRoot(). $RootPath .'/includes/WebClientPrint/WebClientPrint.php';

//Set wcpcache folder RELATIVE to WebClientPrint.php file
//FILE WRITE permission on this folder is required!!!

//$CachePath = 'wcpcache/';
//$CachePath = Utils::getRoot() . $RootPath .'/includes/WebClientPrint/wcpcache/';
//$CachePath = realpath($_SERVER["DOCUMENT_ROOT"]) . $RootPath .'/includes/WebClientPrint/wcpcache/';

if (strpos($_SERVER['PHP_SELF'],"TEST")!== false){
	$CachePath = '/var/www/vhosts/kapal-laut.com/ptadu-development.com/TEST/weberp/includes/WebClientPrint/wcpcache/';
}else{
	$CachePath = '/var/www/vhosts/kapal-laut.com/ptadu-development.com/weberp/includes/WebClientPrint/wcpcache/';
}
WebClientPrint::$wcpCacheFolder = $CachePath;
//===================

// Clean built-in Cache
// NOTE: Remove it if you implement your own cache system
WebClientPrint::cacheClean(60*24); //in minutes

// Process request
$urlParts = parse_url($_SERVER['REQUEST_URI']);
if (isset($urlParts['query'])){
    if (Utils::strContains($urlParts['query'], WebClientPrint::WCP)){
        WebClientPrint::processRequest($urlParts['query']);
    }
} 


/**
 * WebClientPrint provides functions for registering the "WebClientPrint for PHP" solution 
 * script code in PHP web pages as well as for processing client requests and managing the
 * internal cache.
 * 
 * @author Neodynamic <http://neodynamic.com/support>
 * @copyright (c) 2016, Neodynamic SRL
 * @license http://neodynamic.com/eula Neodynamic EULA
 */
class WebClientPrint {
   
    const VERSION = '2.0.2016.800';
    const CLIENT_PRINT_JOB = "clientPrint";
    const WCP = 'WEB_CLIENT_PRINT';
    
    const WCP_CACHE_WCPP_INSTALLED = 'WCPP_INSTALLED';
    const WCP_CACHE_WCPP_VER = 'WCPP_VER';
    const WCP_CACHE_PRINTERS = 'PRINTERS';
    
    /**
     * Gets or sets the License Owner
     * @var string 
     */
    static $licenseOwner = '';
    /**
     * Gets or sets the License Key
     * @var string
     */
    static $licenseKey = '';
    /**
     * Gets or sets the ABSOLUTE URL to WebClientPrint.php file
     * @var string
     */
    static $webClientPrintAbsoluteUrl = '';
    /**
     * Gets or sets the wcpcache folder URL RELATIVE to WebClientPrint.php file. 
     * FILE WRITE permission on this folder is required!!!
     * @var string
     */
    static $wcpCacheFolder = '';
    
    /**
     * Adds a new entry to the built-in file system cache. 
     * @param string $sid The user's session id
     * @param string $key The cache entry key
     * @param string $val The data value to put in the cache
     * @throws Exception
     */
    public static function cacheAdd($sid, $key, $val){
        if (Utils::isNullOrEmptyString(self::$wcpCacheFolder)){
            throw new Exception('WebClientPrint wcpCacheFolder is missing, please specify it.');
        }
        if (Utils::isNullOrEmptyString($sid)){
            throw new Exception('WebClientPrint FileName cache is missing, please specify it.');
        }
        $cacheFileName = (Utils::strEndsWith(self::$wcpCacheFolder, '/')?self::$wcpCacheFolder:self::$wcpCacheFolder.'/').$sid.'.wcpcache';
        $dataWCPP_VER = '';
        $dataPRINTERS = '';
            
        if(file_exists($cacheFileName)){
            $cache_info = parse_ini_file($cacheFileName);
            
            $dataWCPP_VER = $cache_info[self::WCP_CACHE_WCPP_VER];
            $dataPRINTERS = $cache_info[self::WCP_CACHE_PRINTERS];
        }
        
        if ($key === self::WCP_CACHE_WCPP_VER){
            $dataWCPP_VER = self::WCP_CACHE_WCPP_VER.'='.'"'.$val.'"';
            $dataPRINTERS = self::WCP_CACHE_PRINTERS.'='.'"'.$dataPRINTERS.'"';
        } else if ($key === self::WCP_CACHE_PRINTERS){
            $dataWCPP_VER = self::WCP_CACHE_WCPP_VER.'='.'"'.$dataWCPP_VER.'"';
            $dataPRINTERS = self::WCP_CACHE_PRINTERS.'='.'"'.$val.'"';
        }

        $data = $dataWCPP_VER.chr(13).chr(10).$dataPRINTERS;
        $handle = fopen($cacheFileName, 'w') or die('Cannot open file:  '.$cacheFileName);  
        fwrite($handle, $data);
        fclose($handle);
        
    }
    
    /**
     * Gets a value from the built-in file system cache based on the specified sid & key 
     * @param string $sid The user's session id
     * @param string $key The cache entry key
     * @return string Returns the value from the cache for the specified sid & key if it's found; or an empty string otherwise.
     * @throws Exception
     */
    public static function cacheGet($sid, $key){
        if (Utils::isNullOrEmptyString(self::$wcpCacheFolder)){
            throw new Exception('WebClientPrint wcpCacheFolder is missing, please specify it.');
        }
        if (Utils::isNullOrEmptyString($sid)){
            throw new Exception('WebClientPrint FileName cache is missing, please specify it.');
        }
        $cacheFileName = (Utils::strEndsWith(self::$wcpCacheFolder, '/')?self::$wcpCacheFolder:self::$wcpCacheFolder.'/').$sid.'.wcpcache';
        if(file_exists($cacheFileName)){
            $cache_info = parse_ini_file($cacheFileName, FALSE, INI_SCANNER_RAW);
                
            if($key===self::WCP_CACHE_WCPP_VER || $key===self::WCP_CACHE_WCPP_INSTALLED){
                return $cache_info[self::WCP_CACHE_WCPP_VER];
            }else if($key===self::WCP_CACHE_PRINTERS){
                return $cache_info[self::WCP_CACHE_PRINTERS];
            }else{
                return '';
            }
        }else{
            return '';
        }
    }
    
    /**
     * Cleans the built-in file system cache
     * @param type $minutes The number of minutes after any files on the cache will be removed.
     */
    public static function cacheClean($minutes){
        if (!Utils::isNullOrEmptyString(self::$wcpCacheFolder)){
            $cacheDir = (Utils::strEndsWith(self::$wcpCacheFolder, '/')?self::$wcpCacheFolder:self::$wcpCacheFolder.'/');
            if ($handle = opendir($cacheDir)) {
                 while (false !== ($file = readdir($handle))) {
                    if ($file!='.' && $file!='..' && (time()-filectime($cacheDir.$file)) > (60*$minutes)) {
                        unlink($cacheDir.$file);
                    }
                 }
                 closedir($handle);
            }
        }
    }
    
    /**
     * Returns script code for detecting whether WCPP is installed at the client machine.
     *
     * The WCPP-detection script code ends with a 'success' or 'failure' status.
     * You can handle both situation by creating two javascript functions which names 
     * must be wcppDetectOnSuccess() and wcppDetectOnFailure(). 
     * These two functions will be automatically invoked by the WCPP-detection script code.
     * 
     * The WCPP-detection script uses a delay time variable which by default is 10000 ms (10 sec). 
     * You can change it by creating a javascript global variable which name must be wcppPingDelay_ms. 
     * For example, to use 5 sec instead of 10, you should add this to your script: 
     *   
     * var wcppPingDelay_ms = 5000;
     *    
     * @return string A [script] tag linking to the WCPP-detection script code.
     * @throws Exception
     */
    public static function createWcppDetectionScript(){
        
        if (Utils::isNullOrEmptyString(self::$webClientPrintAbsoluteUrl)){
            throw new Exception('WebClientPrint absolute URL is missing, please specify it.');
        }
        
        $buffer = '<script type="text/javascript">';
        if(Utils::isIE6to9() || Utils::isIE10orGreater()){
            $buffer .= 'var wcppPingNow=false;'; 
        } else {
            $buffer .= 'var wcppPingNow=true;';
        }
        $buffer .= '</script>';
        
        $wcpHandler = self::$webClientPrintAbsoluteUrl.'?'.self::WCP.'&d='.session_id();
        $buffer .= '<script src="'.$wcpHandler.'" type="text/javascript"></script>';
        
        if(Utils::isIE6to9() || Utils::isIE10orGreater()){
            $crlf = chr(13).chr(10);
            $buffer .= $crlf.$crlf;
            $buffer .= '<!--[if WCPP]>'.$crlf;
            $buffer .= '<script type="text/javascript">'.$crlf;
            $buffer .= '$(document).ready(function(){jsWCPP.ping();});'.$crlf;
            $buffer .= '</script>'.$crlf;
            $buffer .= '<![endif]-->'.$crlf;
            $buffer .= '<!--[if !WCPP]>'.$crlf;
            $buffer .= '<script type="text/javascript">'.$crlf;
            $buffer .= '$(document).ready(function(){wcppDetectOnFailure();});'.$crlf;
            $buffer .= '</script>'.$crlf;
            $buffer .= '<![endif]-->'.$crlf;
         }
         
         return $buffer;
    }
    
    /**
     * Returns a string containing a HTML meta tag for the WCPP-detection procedure.
     * 
     * The meta tag X-UA-Compatible is generated for Internet Explorer (IE) 10 or greater to emulate IE9. 
     * If this meta tag is not generated, then IE10 or greater will display some unwanted dialog box to 
     * the user when the WCPP-detection script is executed.
     * 
     * @return string A string containing a HTML meta tag for the WCPP-detection procedure.
     */
    public static function getWcppDetectionMetaTag(){
         return Utils::isIE10orGreater()?'<meta http-equiv="X-UA-Compatible" content="IE=EmulateIE9" />':'';
    }
    
    /**
     * Returns a [script] tag linking to the WebClientPrint script code by using 
     * the specified URL for the client print job generation.
     * 
     * @param string $clientPrintJobUrl The URL to the php file that creates ClientPrintJob objects.
     * @return string A [script] tag linking to the WebClientPrint script code by using the specified URL for the client print job generation.
     * @throws Exception
     */
    public static function createScript($clientPrintJobUrl){
        if (Utils::isNullOrEmptyString(self::$webClientPrintAbsoluteUrl)){
            throw new Exception('WebClientPrint absolute URL is missing, please specify it.');
        }
        $wcpHandler = self::$webClientPrintAbsoluteUrl.'?';
        $wcpHandler .= self::WCP;
        $wcpHandler .= '&';
        $wcpHandler .= self::VERSION;
        $wcpHandler .= '&';
        $wcpHandler .= microtime(true);
        $wcpHandler .= '&u=';
        $wcpHandler .= base64_encode($clientPrintJobUrl);
        return '<script src="'.$wcpHandler.'" type="text/javascript"></script>';
    }
    
    public static function processRequest($data){
        
        $PING = 'wcppping';
        $SID = 'sid';
        $HAS_WCPP = 'wcppInstalled';
        
        $GEN_WCP_SCRIPT_URL = 'u';
        $GEN_DETECT_WCPP_SCRIPT = 'd';
        $WCP_SCRIPT_AXD_GET_PRINTERS = 'getPrinters';
        $WCPP_SET_PRINTERS = 'printers';
        $WCP_SCRIPT_AXD_GET_WCPPVERSION = 'getWcppVersion';
        $WCPP_SET_VERSION = 'wcppVer';
        
        $wcpUrl=self::$webClientPrintAbsoluteUrl;
        
        parse_str($data, $qs);
    
        if(isset($qs[$SID])){
            if(isset($qs[$PING])){
                if (isset($qs[$WCPP_SET_VERSION])){
                    self::cacheAdd($qs[$SID], self::WCP_CACHE_WCPP_VER, $qs[$WCPP_SET_VERSION]);
                }else{
                    self::cacheAdd($qs[$SID], self::WCP_CACHE_WCPP_VER,'1.0.0.0');
                }
            }else if(isset($qs[$WCPP_SET_PRINTERS])){
                self::cacheAdd($qs[$SID], self::WCP_CACHE_PRINTERS, strlen($qs[$WCPP_SET_PRINTERS]) > 0 ? base64_decode($qs[$WCPP_SET_PRINTERS]) : '');
            }else if(strpos($data, $WCP_SCRIPT_AXD_GET_PRINTERS)>0){
                ob_start();
                ob_clean();
                header('Content-type: text/plain');
                echo self::cacheGet($qs[$SID], self::WCP_CACHE_PRINTERS);
            }else if(isset($qs[$WCPP_SET_VERSION])){
                if(strlen($qs[$WCPP_SET_VERSION]) > 0){
                    self::cacheAdd($qs[$SID], self::WCP_CACHE_WCPP_VER, $qs[$WCPP_SET_VERSION]);
                }
            }else if(strpos($data, $WCP_SCRIPT_AXD_GET_WCPPVERSION)>0){
                ob_start();
                ob_clean();
                header('Content-type: text/plain');
                echo self::cacheGet($qs[$SID], self::WCP_CACHE_WCPP_VER);
            }else{
                ob_start();
                ob_clean();
                header('Content-type: text/plain');
                echo self::cacheGet($qs[$SID], self::WCP_CACHE_WCPP_INSTALLED);
            }
        }else if(isset($qs[$GEN_DETECT_WCPP_SCRIPT])){
            
            $curSID = $qs[$GEN_DETECT_WCPP_SCRIPT];
            $onSuccessScript = 'wcppDetectOnSuccess(data);';
            $onFailureScript = 'wcppDetectOnFailure();';
            $dynamicIframeId = 'i'.substr(uniqid(), 0, 3);
            $absoluteWcpAxd = $wcpUrl.'?'.self::WCP.'&'.$SID.'='.$curSID;
            
            $s1 = 'dmFyIGpzV0NQUD0oZnVuY3Rpb24oKXt2YXIgc2V0WFhYLU5FTy1IVE1MLUlELVhYWD1mdW5jdGlvbigpe2lmKHdpbmRvdy5jaHJvbWUpeyQoJyNYWFgtTkVPLUhUTUwtSUQtWFhYJykuYXR0cignaHJlZicsJ3dlYmNsaWVudHByaW50OicrYXJndW1lbnRzWzBdKTt2YXIgYT0kKCdhI1hYWC1ORU8tSFRNTC1JRC1YWFgnKVswXTt2YXIgZXZPYmo9ZG9jdW1lbnQuY3JlYXRlRXZlbnQoJ01vdXNlRXZlbnRzJyk7ZXZPYmouaW5pdEV2ZW50KCdjbGljaycsdHJ1ZSx0cnVlKTthLmRpc3BhdGNoRXZlbnQoZXZPYmopfWVsc2V7JCgnI1hYWC1ORU8tSFRNTC1JRC1YWFgnKS5hdHRyKCdzcmMnLCd3ZWJjbGllbnRwcmludDonK2FyZ3VtZW50c1swXSl9fTtyZXR1cm57aW5pdDpmdW5jdGlvbigpe2lmKHdpbmRvdy5jaHJvbWUpeyQoJzxhIC8+Jyx7aWQ6J1hYWC1ORU8tSFRNTC1JRC1YWFgnfSkuYXBwZW5kVG8oJ2JvZHknKX1lbHNleyQoJzxpZnJhbWUgLz4nLHtuYW1lOidYWFgtTkVPLUhUTUwtSUQtWFhYJyxpZDonWFhYLU5FTy1IVE1MLUlELVhYWCcsd2lkdGg6JzEnLGhlaWdodDonMScsc3R5bGU6J3Zpc2liaWxpdHk6aGlkZGVuO3Bvc2l0aW9uOmFic29sdXRlJ30pLmFwcGVuZFRvKCdib2R5Jyl9fSxwaW5nOmZ1bmN0aW9uKCl7c2V0WFhYLU5FTy1IVE1MLUlELVhYWCgnWFhYLU5FTy1QSU5HLVVSTC1YWFgnKyhhcmd1bWVudHMubGVuZ3RoPT0xPycmJythcmd1bWVudHNbMF06JycpKTt2YXIgZGVsYXlfbXM9KHR5cGVvZiB3Y3BwUGluZ0RlbGF5X21zPT09J3VuZGVmaW5lZCcpPzEwMDAwOndjcHBQaW5nRGVsYXlfbXM7c2V0VGltZW91dChmdW5jdGlvbigpeyQuZ2V0KCdYWFgtTkVPLVVTRVItSEFTLVdDUFAtWFhYJyxmdW5jdGlvbihkYXRhKXtpZihkYXRhIT0nZicpe1hYWC1ORU8tT04tU1VDQ0VTUy1TQ1JJUFQtWFhYfWVsc2V7WFhYLU5FTy1PTi1GQUlMVVJFLVNDUklQVC1YWFh9fSl9LGRlbGF5X21zKX19fSkoKTsgJChkb2N1bWVudCkucmVhZHkoZnVuY3Rpb24oKXtqc1dDUFAuaW5pdCgpO2lmKHdjcHBQaW5nTm93KWpzV0NQUC5waW5nKCk7fSk7';
            $s2 = base64_decode($s1);
            $s2 = str_replace('XXX-NEO-HTML-ID-XXX', $dynamicIframeId, $s2);
            $s2 = str_replace('XXX-NEO-PING-URL-XXX', $absoluteWcpAxd.'&'.$PING, $s2);
            $s2 = str_replace('XXX-NEO-USER-HAS-WCPP-XXX', $absoluteWcpAxd, $s2);
            $s2 = str_replace('XXX-NEO-ON-SUCCESS-SCRIPT-XXX', $onSuccessScript, $s2);
            $s2 = str_replace('XXX-NEO-ON-FAILURE-SCRIPT-XXX', $onFailureScript, $s2);
            
            ob_start();
            ob_clean();
            header('Content-type: application/javascript');
            echo $s2;
            
        }else if(isset($qs[$GEN_WCP_SCRIPT_URL])){
            
            $clientPrintJobUrl = base64_decode($qs[$GEN_WCP_SCRIPT_URL]);
            if (strpos($clientPrintJobUrl, '?')>0){
                $clientPrintJobUrl .= '&';
            }else{
                $clientPrintJobUrl .= '?';
            }
            $clientPrintJobUrl .= self::CLIENT_PRINT_JOB;
            $absoluteWcpAxd = $wcpUrl;
            $wcppGetPrintersParam = '-getPrinters:'.$absoluteWcpAxd.'?'.self::WCP.'&'.$SID.'=';
            $wcpHandlerGetPrinters = $absoluteWcpAxd.'?'.self::WCP.'&'.$WCP_SCRIPT_AXD_GET_PRINTERS.'&'.$SID.'=';
            $wcppGetWcppVerParam = '-getWcppVersion:'.$absoluteWcpAxd.'?'.self::WCP.'&'.$SID.'=';
            $wcpHandlerGetWcppVer = $absoluteWcpAxd.'?'.self::WCP.'&'.$WCP_SCRIPT_AXD_GET_WCPPVERSION.'&'.$SID.'=';

            $s1 = 'dmFyIGpzV2ViQ2xpZW50UHJpbnQ9KGZ1bmN0aW9uKCl7dmFyIHNldEE9ZnVuY3Rpb24oKXt2YXIgZV9pZD0naWRfJytuZXcgRGF0ZSgpLmdldFRpbWUoKTtpZih3aW5kb3cuY2hyb21lKXskKCdib2R5JykuYXBwZW5kKCc8YSBpZD0iJytlX2lkKyciPjwvYT4nKTskKCcjJytlX2lkKS5hdHRyKCdocmVmJywnd2ViY2xpZW50cHJpbnQ6Jythcmd1bWVudHNbMF0pO3ZhciBhPSQoJ2EjJytlX2lkKVswXTt2YXIgZXZPYmo9ZG9jdW1lbnQuY3JlYXRlRXZlbnQoJ01vdXNlRXZlbnRzJyk7ZXZPYmouaW5pdEV2ZW50KCdjbGljaycsdHJ1ZSx0cnVlKTthLmRpc3BhdGNoRXZlbnQoZXZPYmopfWVsc2V7JCgnYm9keScpLmFwcGVuZCgnPGlmcmFtZSBuYW1lPSInK2VfaWQrJyIgaWQ9IicrZV9pZCsnIiB3aWR0aD0iMSIgaGVpZ2h0PSIxIiBzdHlsZT0idmlzaWJpbGl0eTpoaWRkZW47cG9zaXRpb246YWJzb2x1dGUiIC8+Jyk7JCgnIycrZV9pZCkuYXR0cignc3JjJywnd2ViY2xpZW50cHJpbnQ6Jythcmd1bWVudHNbMF0pfXNldFRpbWVvdXQoZnVuY3Rpb24oKXskKCcjJytlX2lkKS5yZW1vdmUoKX0sNTAwMCl9O3JldHVybntwcmludDpmdW5jdGlvbigpe3NldEEoJ1VSTF9QUklOVF9KT0InKyhhcmd1bWVudHMubGVuZ3RoPT0xPycmJythcmd1bWVudHNbMF06JycpKX0sZ2V0UHJpbnRlcnM6ZnVuY3Rpb24oKXtzZXRBKCdVUkxfV0NQX0FYRF9XSVRIX0dFVF9QUklOVEVSU19DT01NQU5EJyskKCcjc2lkJykudmFsKCkpO3ZhciBkZWxheV9tcz0odHlwZW9mIHdjcHBHZXRQcmludGVyc0RlbGF5X21zPT09J3VuZGVmaW5lZCcpPzEwMDAwOndjcHBHZXRQcmludGVyc0RlbGF5X21zO3NldFRpbWVvdXQoZnVuY3Rpb24oKXskLmdldCgnVVJMX1dDUF9BWERfR0VUX1BSSU5URVJTJyskKCcjc2lkJykudmFsKCksZnVuY3Rpb24oZGF0YSl7aWYoZGF0YS5sZW5ndGg+MCl7d2NwR2V0UHJpbnRlcnNPblN1Y2Nlc3MoZGF0YSl9ZWxzZXt3Y3BHZXRQcmludGVyc09uRmFpbHVyZSgpfX0pfSxkZWxheV9tcyl9LGdldFdjcHBWZXI6ZnVuY3Rpb24oKXtzZXRBKCdVUkxfV0NQX0FYRF9XSVRIX0dFVF9XQ1BQVkVSU0lPTl9DT01NQU5EJyskKCcjc2lkJykudmFsKCkpO3ZhciBkZWxheV9tcz0odHlwZW9mIHdjcHBHZXRWZXJEZWxheV9tcz09PSd1bmRlZmluZWQnKT8xMDAwMDp3Y3BwR2V0VmVyRGVsYXlfbXM7c2V0VGltZW91dChmdW5jdGlvbigpeyQuZ2V0KCdVUkxfV0NQX0FYRF9HRVRfV0NQUFZFUlNJT04nKyQoJyNzaWQnKS52YWwoKSxmdW5jdGlvbihkYXRhKXtpZihkYXRhLmxlbmd0aD4wKXt3Y3BHZXRXY3BwVmVyT25TdWNjZXNzKGRhdGEpfWVsc2V7d2NwR2V0V2NwcFZlck9uRmFpbHVyZSgpfX0pfSxkZWxheV9tcyl9LHNlbmQ6ZnVuY3Rpb24oKXtzZXRBLmFwcGx5KHRoaXMsYXJndW1lbnRzKX19fSkoKTs=';
            $s2 = base64_decode($s1);
            $s2 = str_replace('URL_PRINT_JOB', $clientPrintJobUrl, $s2);
            $s2 = str_replace('URL_WCP_AXD_WITH_GET_PRINTERS_COMMAND', $wcppGetPrintersParam, $s2);
            $s2 = str_replace('URL_WCP_AXD_GET_PRINTERS', $wcpHandlerGetPrinters, $s2);
            $s2 = str_replace('URL_WCP_AXD_WITH_GET_WCPPVERSION_COMMAND', $wcppGetWcppVerParam, $s2);
            $s2 = str_replace('URL_WCP_AXD_GET_WCPPVERSION', $wcpHandlerGetWcppVer, $s2);
            
            ob_start();
            ob_clean();
            header('Content-type: application/javascript');
            echo $s2;
        }
        
    }
    
}

/**
 * The base class for all kind of printers supported at the client side.
 */
abstract class ClientPrinter{
    
    public $printerId;
    public function serialize(){
        
    }
}

/**
 * It represents the default printer installed in the client machine.
 */
class DefaultPrinter extends ClientPrinter{
    public function __construct() {
        $this->printerId = chr(0);
    }
    
    public function serialize() {
        return $this->printerId;
    }
}

/**
 * It represents a printer installed in the client machine with an associated OS driver.
 */
class InstalledPrinter extends ClientPrinter{
    
    /**
     * Gets or sets the name of the printer installed in the client machine. Default value is an empty string.
     * @var string 
     */
    public $printerName = '';

    /**
     * Creates an instance of the InstalledPrinter class with the specified printer name.
     * @param string $printerName The name of the printer installed in the client machine.
     */
    public function __construct($printerName) {
        $this->printerId = chr(1);
        $this->printerName = $printerName;
    }
    
    
    public function serialize() {
        
        if (Utils::isNullOrEmptyString($this->printerName)){
             throw new Exception("The specified printer name is null or empty.");
        }
        
        return $this->printerId.$this->printerName;
    }
}

/**
 * It represents a printer which is connected through a parallel port in the client machine.
 */
class ParallelPortPrinter extends ClientPrinter{
    
    /**
     * Gets or sets the parallel port name, for example LPT1. Default value is "LPT1"
     * @var string 
     */
    public $portName = "LPT1";

    /**
     * Creates an instance of the ParallelPortPrinter class with the specified port name.
     * @param string $portName The parallel port name, for example LPT1.
     */
    public function __construct($portName) {
        $this->printerId = chr(2);
        $this->portName = $portName;
    }
    
    public function serialize() {
        
        if (Utils::isNullOrEmptyString($this->portName)){
             throw new Exception("The specified parallel port name is null or empty.");
        }
        
        return $this->printerId.$this->portName;
    }
}

/**
 * It represents a printer which is connected through a serial port in the client machine.
 */
class SerialPortPrinter extends ClientPrinter{
    
    /**
     * Gets or sets the serial port name, for example COM1. Default value is "COM1"
     * @var string 
     */
    public $portName = "COM1";
    /**
     * Gets or sets the serial port baud rate in bits per second. Default value is 9600
     * @var integer 
     */
    public $baudRate = 9600;
    /**
     * Gets or sets the serial port parity-checking protocol. Default value is NONE = 0
     * NONE = 0, ODD = 1, EVEN = 2, MARK = 3, SPACE = 4
     * @var integer 
     */
    public $parity = SerialPortParity::NONE;
    /**
     * Gets or sets the serial port standard number of stopbits per byte. Default value is ONE = 1
     * ONE = 1, TWO = 2, ONE_POINT_FIVE = 3
     * @var integer
     */
    public $stopBits = SerialPortStopBits::ONE;
    /**
     * Gets or sets the serial port standard length of data bits per byte. Default value is 8
     * @var integer
     */
    public $dataBits = 8;
    /**
     * Gets or sets the handshaking protocol for serial port transmission of data. Default value is XON_XOFF = 1
     * NONE = 0, REQUEST_TO_SEND = 2, REQUEST_TO_SEND_XON_XOFF = 3, XON_XOFF = 1
     * @var integer
     */
    public $flowControl = SerialPortHandshake::XON_XOFF;
    
    /**
     * Creates an instance of the SerialPortPrinter class wiht the specified information.
     * @param string $portName The serial port name, for example COM1.
     * @param integer $baudRate The serial port baud rate in bits per second.
     * @param integer $parity The serial port parity-checking protocol.
     * @param integer $stopBits The serial port standard number of stopbits per byte.
     * @param integer $dataBits The serial port standard length of data bits per byte.
     * @param integer $flowControl The handshaking protocol for serial port transmission of data.
     */
    public function __construct($portName, $baudRate, $parity, $stopBits, $dataBits, $flowControl) {
        $this->printerId = chr(3);
        $this->portName = $portName;
        $this->baudRate = $baudRate;
        $this->parity = $parity;
        $this->stopBits = $stopBits;
        $this->dataBits = $dataBits;
        $this->flowControl = $flowControl;
    }
    
    public function serialize() {
        
        if (Utils::isNullOrEmptyString($this->portName)){
             throw new Exception("The specified serial port name is null or empty.");
        }
        
        return $this->printerId.$this->portName.Utils::SER_SEP.$this->baudRate.Utils::SER_SEP.$this->dataBits.Utils::SER_SEP.$this->flowControl.Utils::SER_SEP.$this->parity.Utils::SER_SEP.$this->stopBits;
    }
}

/**
 * It represents a Network IP/Ethernet printer which can be reached from the client machine.
 */
class NetworkPrinter extends ClientPrinter{
    
    /**
     * Gets or sets the DNS name assigned to the printer. Default is an empty string
     * @var string 
     */
    public $dnsName = "";
    /**
     * Gets or sets the Internet Protocol (IP) address assigned to the printer. Default value is an empty string
     * @var string 
     */
    public $ipAddress = "";
    /**
     * Gets or sets the port number assigned to the printer. Default value is 0
     * @var integer 
     */
    public $port = 0;
    
    /**
     * Creates an instance of the NetworkPrinter class with the specified DNS name or IP Address, and port number.
     * @param string $dnsName The DNS name assigned to the printer.
     * @param string $ipAddress The Internet Protocol (IP) address assigned to the printer.
     * @param integer $port The port number assigned to the printer.
     */
    public function __construct($dnsName, $ipAddress, $port) {
        $this->printerId = chr(4);
        $this->dnsName = $dnsName;
        $this->ipAddress = $ipAddress;
        $this->port = $port;
    }
    
    public function serialize() {
        
        if (Utils::isNullOrEmptyString($this->dnsName) && Utils::isNullOrEmptyString($this->ipAddress)){
             throw new Exception("The specified network printer settings is not valid. You must specify the DNS Printer Name or its IP address.");
        }
        
        return $this->printerId.$this->dnsName.Utils::SER_SEP.$this->ipAddress.Utils::SER_SEP.$this->port;
    }
}

/**
 *  It represents a printer which will be selected by the user in the client machine. The user will be prompted with a print dialog.
 */
class UserSelectedPrinter extends ClientPrinter{
    public function __construct() {
        $this->printerId = chr(5);
    }
    
    public function serialize() {
        return $this->printerId;
    }
}

/**
 * Specifies the parity bit for Serial Port settings. 
 */
class SerialPortParity{
    const NONE = 0;
    const ODD = 1;
    const EVEN = 2;
    const MARK = 3;
    const SPACE = 4;
}

/**
 * Specifies the number of stop bits used for Serial Port settings.
 */
class SerialPortStopBits{
    const NONE = 0;
    const ONE = 1;
    const TWO = 2;
    const ONE_POINT_FIVE = 3;
}

/**
 * Specifies the control protocol used in establishing a serial port communication.
 */
class SerialPortHandshake{
    const NONE = 0;
    const REQUEST_TO_SEND = 2;
    const REQUEST_TO_SEND_XON_XOFF = 3;
    const XON_XOFF = 1;
}

/**
 * It represents a file in the server that will be printed at the client side.
 */
class PrintFile{
    
    /**
     * Gets or sets the path of the file at the server side that will be printed at the client side.
     * @var string 
     */
    public $filePath = '';
    /**
     * Gets or sets the file name that will be created at the client side. 
     * It must include the file extension like .pdf, .txt, .doc, .xls, etc.
     * @var string 
     */
    public $fileName = '';
    /**
     * Gets or sets the binary content of the file at the server side that will be printed at the client side.
     * @var string 
     */
    public $fileBinaryContent = '';
    
    const PREFIX = 'wcpPF:';
    const SEP = '|';
        
    /**
     * 
     * @param string $filePath The path of the file at the server side that will be printed at the client side.
     * @param string $fileName The file name that will be created at the client side. It must include the file extension like .pdf, .txt, .doc, .xls, etc.
     * @param string $fileBinaryContent The binary content of the file at the server side that will be printed at the client side.
     */
    public function __construct($filePath, $fileName, $fileBinaryContent) {
        $this->filePath = $filePath;
        $this->fileName = $fileName;
        $this->fileBinaryContent = $fileBinaryContent;
        
    }
    
    public function serialize() {
        $file = str_replace('\\', 'BACKSLASHCHAR',$this->fileName );
        return self::PREFIX.$file.self::SEP.$this->getFileContent();
    }
    
    public function getFileContent(){
        $content = $this->fileBinaryContent;
        if(!Utils::isNullOrEmptyString($this->filePath)){
            $handle = fopen($this->filePath, 'rb');
            $content = fread($handle, filesize($this->filePath));
            fclose($handle);
        }
        return $content;
    }
}

/**
 * Some utility functions used by WebClientPrint for PHP solution.
 */
class Utils{
    const SER_SEP = "|";
    
    static function isNullOrEmptyString($s){
        return (!isset($s) || trim($s)==='');
    }
    
    static function formatHexValues($s){
        
        $buffer = '';
            
        $l = strlen($s);
        $i = 0;

        while ($i < $l)
        {
            if ($s[$i] == '0')
            {
                if ($i + 1 < $l && ($s[$i] == '0' && $s[$i + 1] == 'x'))
                {
                    if ($i + 2 < $l &&
                        (($s[$i + 2] >= '0' && $s[$i + 2] <= '9') || ($s[$i + 2] >= 'a' && $s[$i + 2] <= 'f') || ($s[$i + 2] >= 'A' && $s[$i + 2] <= 'F')))
                    {
                        if ($i + 3 < $l &&
                           (($s[$i + 3] >= '0' && $s[$i + 3] <= '9') || ($s[$i + 3] >= 'a' && $s[$i + 3] <= 'f') || ($s[$i + 3] >= 'A' && $s[$i + 3] <= 'F')))
                        {
                            try{
                                $buffer .= chr(substr($s, $i, 4));
                                $i += 4;
                                continue;
                                
                            } catch (Exception $ex) {
                                throw new Exception("Invalid hex notation in the specified printer commands at index: ".$i);
                            }
                                
                            
                        }
                        else
                        {
                            try{
                                
                                $buffer .= chr(substr($s, $i, 3));
                                $i += 3;
                                continue;
                                
                            } catch (Exception $ex) {
                                throw new ArgumentException("Invalid hex notation in the specified printer commands at index: ".$i);
                            }
                        }
                    }
                }
            }

            $buffer .= substr($s, $i, 1);
            
            $i++;
        }

        return $buffer;
        
    }
    
    public static function intToArray($i){
        return pack("L", $i);
    }
        
    public static function getRoot(){
        $s = empty($_SERVER["HTTPS"]) ? '' : ($_SERVER["HTTPS"] == "on") ? "s" : "";
        $protocol = static::strleft(strtolower($_SERVER["SERVER_PROTOCOL"]), "/").$s;
        $port = ($_SERVER["SERVER_PORT"] == "80") ? "" : (":".$_SERVER["SERVER_PORT"]);
        return $protocol."://".$_SERVER['SERVER_NAME'].$port;
    }
   
    public static function strleft($s1, $s2) {
	return substr($s1, 0, strpos($s1, $s2));
    }
    
    public static function strContains($s1, $s2){
        return (strpos($s1, $s2) !== false);
    }
    
    public static function strEndsWith($s1, $s2)
    {
        return substr($s1, -strlen($s2)) === $s2;
    }
    
    public static function getUA(){
        return $_SERVER['HTTP_USER_AGENT'];
    }
    
    public static function isIE6to9(){
        $ua = static::getUA();
        return ((static::strContains($ua,"MSIE 6.") ||
                 static::strContains($ua,"MSIE 7.") ||
                 static::strContains($ua,"MSIE 8.") ||
                 static::strContains($ua,"MSIE 9.")) &&
                 static::strContains($ua,"Opera") === false);
    }

    public static function isIE5orOlder(){
        $ua = static::getUA();
        return ((static::strContains($ua,"MSIE 5.") ||
                 static::strContains($ua,"MSIE 4.") ||
                 static::strContains($ua,"MSIE 3.") ||
                 static::strContains($ua,"MSIE 2.")) &&
                 static::strContains($ua,"Opera") === false);
    }
        
    public static function isIE10orGreater(){
        $ua = static::getUA();
        return (static::strContains($ua,"MSIE") || static::strContains($ua,"Trident/")) && static::isIE6to9() === false && static::isIE5orOlder() === false && static::strContains($ua,"Opera") === false;
    }
    
}

/**
 * Specifies information about the print job to be processed at the client side.
 */
class ClientPrintJob{
    
    /**
     * Gets or sets the ClientPrinter object. Default is NULL.
     * The ClientPrinter object refers to the kind of printer that the client machine has attached or can reach.
     * - Use a DefaultPrinter object for using the default printer installed in the client machine.
     * - Use a InstalledPrinter object for using a printer installed in the client machine with an associated Windows driver.
     * - Use a ParallelPortPrinter object for using a printer which is connected through a parallel port in the client machine.
     * - Use a SerialPortPrinter object for using a printer which is connected through a serial port in the client machine.
     * - Use a NetworkPrinter object for using a Network IP/Ethernet printer which can be reached from the client machine.
     * @var ClientPrinter 
     */
    public $clientPrinter = null;
    /**
     * Gets or sets the printer's commands in text plain format. Default is an empty string.
     * @var string 
     */
    public $printerCommands = '';
    /**
     * Gets or sets whether the printer commands have chars expressed in hexadecimal notation. Default is false.
     * The string set to the $printerCommands property can contain chars expressed in hexadecimal notation.
     * Many printer languages have commands which are represented by non-printable chars and to express these commands 
     * in a string could require many concatenations and hence be not so readable.
     * By using hex notation, you can make it simple and elegant. Here is an example: if you need to encode ASCII 27 (escape), 
     * then you can represent it as 0x27.        
     * @var boolean 
     */
    public $formatHexValues = false;
    /**
     * Gets or sets the PrintFile object to be printed at the client side. Default is NULL.
     * @var PrintFile 
     */
    public $printFile = null;
    /**
     * Gets or sets an array of PrintFile objects to be printed at the client side. Default is NULL.
     * @var array 
     */
    public $printFileGroup = null;
    
    
    /**
     * Sends this ClientPrintJob object to the client for further processing.
     * The ClientPrintJob object will be processed by the WCPP installed at the client machine.
     * @return string A string representing a ClientPrintJob object.
     */
    public function sendToClient(){
        
        header('Content-type: application/octet-stream');
        
        $cpjHeader = chr(99).chr(112).chr(106).chr(2);
        
        $buffer = '';
        
        if (!Utils::isNullOrEmptyString($this->printerCommands)){
            if($this->formatHexValues){
                $buffer = Utils::formatHexValues ($this->printerCommands);
            } else {
                $buffer = $this->printerCommands;
            }
        } else if (isset ($this->printFile)){
            $buffer = $this->printFile->serialize();
        } else if (isset ($this->printFileGroup)){
            $buffer = 'wcpPFG:';
            $zip = new ZipArchive;
            $cacheFileName = (Utils::strEndsWith(WebClientPrint::$wcpCacheFolder, '/')?WebClientPrint::$wcpCacheFolder:WebClientPrint::$wcpCacheFolder.'/').'PFG'.uniqid().'.zip';
            $res = $zip->open($cacheFileName, ZipArchive::CREATE);
            if ($res === TRUE) {
                foreach ($this->printFileGroup as $printFile) {
                    $zip->addFromString($printFile->fileName, $printFile->getFileContent());
                }
                $zip->close();
                $handle = fopen($cacheFileName, 'rb');
                $buffer .= fread($handle, filesize($cacheFileName));
                fclose($handle);
                unlink($cacheFileName);
            } else {
                $buffer='Creating PrintFileGroup failed. Cannot create zip file.';
            }
        }
        
        $arrIdx1 = Utils::intToArray(strlen($buffer));
        
        if (!isset($this->clientPrinter)){
            $this->clientPrinter = new UserSelectedPrinter();
        }    
        
        $buffer .= $this->clientPrinter->serialize();
        
        $arrIdx2 = Utils::intToArray(strlen($buffer));
        
        $lo = '';
        if(Utils::isNullOrEmptyString(WebClientPrint::$licenseOwner)){
            $lo = substr(uniqid(), 0, 8);
        }  else {
            $lo = WebClientPrint::$licenseOwner;
        }
        $lk = '';
        if(Utils::isNullOrEmptyString(WebClientPrint::$licenseKey)){
            $lk = substr(uniqid(), 0, 8);
        }  else {
            $lk = WebClientPrint::$licenseKey;
        }
        $buffer .= $lo.chr(124).$lk;
        
        return $cpjHeader.$arrIdx1.$arrIdx2.$buffer;
    }
    
}
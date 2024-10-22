<?php

namespace Neodynamic\SDK\Web;
use Exception;
use ZipArchive;

//phpseclib is required for Encryption and Password Protected file printing feature
include 'phpseclib/Math/BigInteger.php';
include 'phpseclib/Crypt/Hash.php';
include 'phpseclib/Crypt/Random.php';
include 'phpseclib/Crypt/Base.php';
include 'phpseclib/Crypt/RSA.php';
include 'phpseclib/Crypt/Rijndael.php';
include 'phpseclib/Crypt/AES.php';

// Setting WebClientPrint
WebClientPrint::$licenseOwner = 'PT ANGIN DINGIN UTARA - 1 WebApp Lic - 1 WebServer Lic';
WebClientPrint::$licenseKey   = 'BF5A6656E89AE6B6ACE0CC5634182872BAADEE88';

//Set wcpcache folder RELATIVE to WebClientPrint.php file
//FILE WRITE permission on this folder is required!!!
WebClientPrint::$wcpCacheFolder = 'wcpcache/';

/**
 * WebClientPrint provides functions for registering the "WebClientPrint for PHP" solution 
 * script code in PHP web pages as well as for processing client requests and managing the
 * internal cache.
 * 
 * @author Neodynamic <http://neodynamic.com/support>
 * @copyright (c) 2021, Neodynamic SRL
 * @license http://neodynamic.com/eula Neodynamic EULA
 */
class WebClientPrint {
   
    const VERSION = '6.0.0.0';
    const CLIENT_PRINT_JOB = 'clientPrint';
    const WCP = 'WEB_CLIENT_PRINT';
    const WCP_SCRIPT_AXD_GET_PRINTERS = 'getPrinters';
    const WCP_SCRIPT_AXD_GET_PRINTERSINFO = 'getPrintersInfo';
    const WCPP_SET_PRINTERS = 'printers';
    const WCPP_SET_PRINTERSINFO = 'printersInfo';
    const WCP_SCRIPT_AXD_GET_WCPPVERSION = 'getWcppVersion';
    const WCPP_SET_VERSION = 'wcppVer';
    const GEN_WCP_SCRIPT_URL = 'u';
    const GEN_DETECT_WCPP_SCRIPT = 'd';
    const SID = 'sid';
    const PING = 'wcppping';
    
    const WCP_CACHE_WCPP_INSTALLED = 'WCPP_INSTALLED';
    const WCP_CACHE_WCPP_VER = 'WCPP_VER';
    const WCP_CACHE_PRINTERS = 'PRINTERS';
    const WCP_CACHE_PRINTERSINFO = 'PRINTERSINFO';
    
    
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
        $dataPRINTERSINFO = '';
            
        if(file_exists($cacheFileName)){
            $cache_info = parse_ini_file($cacheFileName);
            
            $dataWCPP_VER = $cache_info[self::WCP_CACHE_WCPP_VER];
            $dataPRINTERS = $cache_info[self::WCP_CACHE_PRINTERS];
            $dataPRINTERSINFO = $cache_info[self::WCP_CACHE_PRINTERSINFO];
        }
        
        if ($key === self::WCP_CACHE_WCPP_VER){
            $dataWCPP_VER = self::WCP_CACHE_WCPP_VER.'='.'"'.$val.'"';
            $dataPRINTERS = self::WCP_CACHE_PRINTERS.'='.'"'.$dataPRINTERS.'"';
            $dataPRINTERSINFO = self::WCP_CACHE_PRINTERSINFO.'='.'"'.$dataPRINTERSINFO.'"';
        } else if ($key === self::WCP_CACHE_PRINTERS){
            $dataWCPP_VER = self::WCP_CACHE_WCPP_VER.'='.'"'.$dataWCPP_VER.'"';
            $dataPRINTERS = self::WCP_CACHE_PRINTERS.'='.'"'.$val.'"';
            $dataPRINTERSINFO = self::WCP_CACHE_PRINTERSINFO.'='.'"'.$dataPRINTERSINFO.'"';
        } else if ($key === self::WCP_CACHE_PRINTERSINFO){
            $dataWCPP_VER = self::WCP_CACHE_WCPP_VER.'='.'"'.$dataWCPP_VER.'"';
            $dataPRINTERS = self::WCP_CACHE_PRINTERS.'='.'"'.$dataPRINTERS.'"';
            $dataPRINTERSINFO = self::WCP_CACHE_PRINTERSINFO.'='.'"'.$val.'"';
        }

        $data = $dataWCPP_VER.chr(13).chr(10).$dataPRINTERS.chr(13).chr(10).$dataPRINTERSINFO;
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
            }else if($key===self::WCP_CACHE_PRINTERSINFO){
                return $cache_info[self::WCP_CACHE_PRINTERSINFO];
            }else{
                return '';
            }
        }else{
            return '';
        }
    }
    
    /**
     * Cleans the built-in file system cache
     * @param integer $minutes The number of minutes after any files on the cache will be removed.
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
     * @param string $webClientPrintControllerAbsoluteUrl The Absolute URL to the WebClientPrintController file.
     * @param string $sessionID The current Session ID.
     * @return string A [script] tag linking to the WCPP-detection script code.
     * @throws Exception
     */
    public static function createWcppDetectionScript($webClientPrintControllerAbsoluteUrl, $sessionID){
        
        if (Utils::isNullOrEmptyString($webClientPrintControllerAbsoluteUrl) || 
            !Utils::strStartsWith($webClientPrintControllerAbsoluteUrl, 'http')){
            throw new Exception('WebClientPrintController absolute URL is missing, please specify it.');
        }
        if (Utils::isNullOrEmptyString($sessionID)){
            throw new Exception('Session ID is missing, please specify it.');
        }
        
        $url = $webClientPrintControllerAbsoluteUrl.'?'.self::GEN_DETECT_WCPP_SCRIPT.'='.$sessionID;
        return '<script src="'.$url.'" type="text/javascript"></script>';
         
    }
    
    
    /**
     * Returns a [script] tag linking to the WebClientPrint script code by using 
     * the specified URL for the client print job generation.
     * 
     * @param string $webClientPrintControllerAbsoluteUrl The Absolute URL to the WebClientPrintController file.
     * @param string $clientPrintJobAbsoluteUrl The Absolute URL to the PHP file that creates ClientPrintJob objects.
     * @paran string $sessionID The current Session ID.
     * @return string A [script] tag linking to the WebClientPrint script code by using the specified URL for the client print job generation.
     * @throws Exception
     */
    public static function createScript($webClientPrintControllerAbsoluteUrl, $clientPrintJobAbsoluteUrl, $sessionID){
        if (Utils::isNullOrEmptyString($webClientPrintControllerAbsoluteUrl) || 
            !Utils::strStartsWith($webClientPrintControllerAbsoluteUrl, 'http')){
            throw new Exception('WebClientPrintController absolute URL is missing, please specify it.');
        }
        if (Utils::isNullOrEmptyString($clientPrintJobAbsoluteUrl) || 
            !Utils::strStartsWith($clientPrintJobAbsoluteUrl, 'http')){
            throw new Exception('ClientPrintJob absolute URL is missing, please specify it.');
        }
        if (Utils::isNullOrEmptyString($sessionID)){
            throw new Exception('Session ID is missing, please specify it.');
        }
        
        
        $wcpHandler = $webClientPrintControllerAbsoluteUrl.'?';
        $wcpHandler .= self::VERSION;
        $wcpHandler .= '&';
        $wcpHandler .= microtime(true);
        $wcpHandler .= '&sid=';
        $wcpHandler .= $sessionID;
        $wcpHandler .= '&'.self::GEN_WCP_SCRIPT_URL.'=';
        $wcpHandler .= base64_encode($clientPrintJobAbsoluteUrl);
        return '<script src="'.$wcpHandler.'" type="text/javascript"></script>';
    }
    
    
    /**
     * Generates the WebClientPrint scripts based on the specified query string. Result is stored in the HTTP Response Content
     * 
     * @param type $webClientPrintControllerAbsoluteUrl The Absolute URL to the WebClientPrintController file.
     * @param type $queryString The Query String from current HTTP Request.
     */

    public static function generateScript($webClientPrintControllerAbsoluteUrl, $queryString)
    {
        if (Utils::isNullOrEmptyString($webClientPrintControllerAbsoluteUrl) || 
            !Utils::strStartsWith($webClientPrintControllerAbsoluteUrl, 'http')){
              
            throw new Exception('WebClientPrintController absolute URL is missing, please specify it.');
        }
        
        parse_str($queryString, $qs);
    
        if(isset($qs[self::GEN_DETECT_WCPP_SCRIPT])){
            
            $curSID = $qs[self::GEN_DETECT_WCPP_SCRIPT];
            $dynamicIframeId = 'i'.substr(uniqid(), 0, 3);
            $absoluteWcpAxd = $webClientPrintControllerAbsoluteUrl.'?'.self::SID.'='.$curSID;
            
            $s1 = 'dmFyIGpzV0NQUD0oZnVuY3Rpb24oKXt2YXIgc2V0PDw8LU5FTy1IVE1MLUlELT4+Pj1mdW5jdGlvbigpe3ZhciBlbD1kb2N1bWVudC5nZXRFbGVtZW50QnlJZCgnPDw8LU5FTy1IVE1MLUlELT4+PicpO2lmKHdpbmRvdy5jaHJvbWUpe2VsLmhyZWY9J3dlYmNsaWVudHByaW50dmk6Jythcmd1bWVudHNbMF07dmFyIGV2T2JqPWRvY3VtZW50LmNyZWF0ZUV2ZW50KCdNb3VzZUV2ZW50cycpO2V2T2JqLmluaXRFdmVudCgnY2xpY2snLHRydWUsdHJ1ZSk7ZWwuZGlzcGF0Y2hFdmVudChldk9iail9ZWxzZXtlbC5zcmM9J3dlYmNsaWVudHByaW50dmk6Jythcmd1bWVudHNbMF19fTtyZXR1cm57aW5pdDpmdW5jdGlvbigpe2lmKHdpbmRvdy5jaHJvbWUpe3ZhciBhRWw9ZG9jdW1lbnQuY3JlYXRlRWxlbWVudCgnYScpO2FFbC5pZD0nPDw8LU5FTy1IVE1MLUlELT4+Pic7ZG9jdW1lbnQuYm9keS5hcHBlbmRDaGlsZChhRWwpfWVsc2V7dmFyIGlmRWw9ZG9jdW1lbnQuY3JlYXRlRWxlbWVudCgnaWZyYW1lJyk7aWZFbC5pZD0nPDw8LU5FTy1IVE1MLUlELT4+Pic7aWZFbC5uYW1lPSc8PDwtTkVPLUhUTUwtSUQtPj4+JztpZkVsLndpZHRoPTE7aWZFbC5oZWlnaHQ9MTtpZkVsLnN0eWxlLnZpc2liaWxpdHk9J2hpZGRlbic7aWZFbC5zdHlsZS5wb3NpdGlvbj0nYWJzb2x1dGUnO2RvY3VtZW50LmJvZHkuYXBwZW5kQ2hpbGQoaWZFbCl9fSxwaW5nOmZ1bmN0aW9uKCl7c2V0PDw8LU5FTy1IVE1MLUlELT4+PignPDw8LU5FTy1QSU5HLVVSTC0+Pj4nKyhhcmd1bWVudHMubGVuZ3RoPT0xPycmJythcmd1bWVudHNbMF06JycpKTt2YXIgZGVsYXlfbXM9KHR5cGVvZiB3Y3BwUGluZ0RlbGF5X21zPT09J3VuZGVmaW5lZCcpPzA6d2NwcFBpbmdEZWxheV9tcztpZihkZWxheV9tcz4wKXtzZXRUaW1lb3V0KGZ1bmN0aW9uKCl7dmFyIHhocj1uZXcgWE1MSHR0cFJlcXVlc3QoKTt4aHIub25yZWFkeXN0YXRlY2hhbmdlPWZ1bmN0aW9uKCl7aWYoeGhyLnJlYWR5U3RhdGU9PTQmJnhoci5zdGF0dXM9PTIwMCl7dmFyIGRhdGE9eGhyLnJlc3BvbnNlVGV4dDtpZihkYXRhLmxlbmd0aD4wKXt3Y3BwRGV0ZWN0T25TdWNjZXNzKGRhdGEpfWVsc2V7d2NwcERldGVjdE9uRmFpbHVyZSgpfX19O3hoci5vcGVuKCdHRVQnLCc8PDwtTkVPLVVTRVItSEFTLVdDUFAtPj4+Jyk7eGhyLnNlbmQoKX0sZGVsYXlfbXMpfWVsc2V7dmFyIGZuY1dDUFA9c2V0SW50ZXJ2YWwoZ2V0V0NQUFZlcix3Y3BwUGluZ1RpbWVvdXRTdGVwX21zKTt2YXIgd2NwcF9jb3VudD0wO2Z1bmN0aW9uIGdldFdDUFBWZXIoKXtpZih3Y3BwX2NvdW50PD13Y3BwUGluZ1RpbWVvdXRfbXMpe3ZhciB4aHI9bmV3IFhNTEh0dHBSZXF1ZXN0KCk7eGhyLm9ucmVhZHlzdGF0ZWNoYW5nZT1mdW5jdGlvbigpe2lmKHhoci5yZWFkeVN0YXRlPT00JiZ4aHIuc3RhdHVzPT0yMDApe3ZhciBkYXRhPXhoci5yZXNwb25zZVRleHQ7aWYoZGF0YS5sZW5ndGg+MCl7Y2xlYXJJbnRlcnZhbChmbmNXQ1BQKTt3Y3BwRGV0ZWN0T25TdWNjZXNzKGRhdGEpfX19O3hoci5vcGVuKCdHRVQnLCc8PDwtTkVPLVVTRVItSEFTLVdDUFAtPj4+Jyk7eGhyLnNlbmQoeydfJzoobmV3IERhdGUoKS5nZXRUaW1lKCkpfSk7d2NwcF9jb3VudCs9d2NwcFBpbmdUaW1lb3V0U3RlcF9tc31lbHNle2NsZWFySW50ZXJ2YWwoZm5jV0NQUCk7d2NwcERldGVjdE9uRmFpbHVyZSgpfX19fX19KSgpO2RvY3VtZW50LmFkZEV2ZW50TGlzdGVuZXIoJ0RPTUNvbnRlbnRMb2FkZWQnLGZ1bmN0aW9uKCl7anNXQ1BQLmluaXQoKTtqc1dDUFAucGluZygpfSk7';
                    
            $s2 = base64_decode($s1);
            $s2 = str_replace('<<<-NEO-HTML-ID->>>', $dynamicIframeId, $s2);
            $s2 = str_replace('<<<-NEO-PING-URL->>>', $absoluteWcpAxd.'&'.self::PING, $s2);
            $s2 = str_replace('<<<-NEO-USER-HAS-WCPP->>>', $absoluteWcpAxd, $s2);
            
            return $s2;
            
        }else if(isset($qs[self::GEN_WCP_SCRIPT_URL])){
            
            $clientPrintJobUrl = base64_decode($qs[self::GEN_WCP_SCRIPT_URL]);
            if (strpos($clientPrintJobUrl, '?')>0){
                $clientPrintJobUrl .= '&';
            }else{
                $clientPrintJobUrl .= '?';
            }
            $clientPrintJobUrl .= self::CLIENT_PRINT_JOB;
            $absoluteWcpAxd = $webClientPrintControllerAbsoluteUrl;
            $wcppGetPrintersParam = '-getPrinters:'.$absoluteWcpAxd.'?'.self::WCP.'&'.self::SID.'=';
            $wcpHandlerGetPrinters = $absoluteWcpAxd.'?'.self::WCP.'&'.self::WCP_SCRIPT_AXD_GET_PRINTERS.'&'.self::SID.'=';
            $wcppGetPrintersInfoParam = '-getPrintersInfo:'.$absoluteWcpAxd.'?'.self::WCP.'&'.self::SID.'=';
            $wcpHandlerGetPrintersInfo = $absoluteWcpAxd.'?'.self::WCP.'&'.self::WCP_SCRIPT_AXD_GET_PRINTERSINFO.'&'.self::SID.'=';
            $wcppGetWcppVerParam = '-getWcppVersion:'.$absoluteWcpAxd.'?'.self::WCP.'&'.self::SID.'=';
            $wcpHandlerGetWcppVer = $absoluteWcpAxd.'?'.self::WCP.'&'.self::WCP_SCRIPT_AXD_GET_WCPPVERSION.'&'.self::SID.'=';
            $sessionIDVal = $qs[self::SID];
        
            $s1 = 'dmFyIGpzV2ViQ2xpZW50UHJpbnQ9KGZ1bmN0aW9uKCl7dmFyIGdldFJlcT1mdW5jdGlvbih1cmwsb25TdWNjZXNzLG9uRmFpbHVyZSxvbkNsZWFuKXt2YXIgeGhyPW5ldyBYTUxIdHRwUmVxdWVzdCgpO3hoci5vbnJlYWR5c3RhdGVjaGFuZ2U9ZnVuY3Rpb24oKXtpZih4aHIucmVhZHlTdGF0ZT09NCYmeGhyLnN0YXR1cz09MjAwKXt2YXIgZGF0YT14aHIucmVzcG9uc2VUZXh0O2lmKGRhdGEubGVuZ3RoPjApe2lmKG9uQ2xlYW4pY2xlYXJJbnRlcnZhbChvbkNsZWFuKTtpZihvblN1Y2Nlc3Mpb25TdWNjZXNzKGRhdGEpfWVsc2V7aWYob25GYWlsdXJlKW9uRmFpbHVyZSgpfX19O3hoci5vcGVuKCdHRVQnLHVybCk7eGhyLnNlbmQoeydfJzoobmV3IERhdGUoKS5nZXRUaW1lKCkpfSl9O3ZhciBzZXRBPWZ1bmN0aW9uKCl7dmFyIGVfaWQ9J2lkXycrbmV3IERhdGUoKS5nZXRUaW1lKCk7aWYod2luZG93LmNocm9tZSl7dmFyIGFFbD1kb2N1bWVudC5jcmVhdGVFbGVtZW50KCdhJyk7YUVsLmlkPWVfaWQ7YUVsLmhyZWY9J3dlYmNsaWVudHByaW50dmk6Jythcmd1bWVudHNbMF07dmFyIGV2T2JqPWRvY3VtZW50LmNyZWF0ZUV2ZW50KCdNb3VzZUV2ZW50cycpO2V2T2JqLmluaXRFdmVudCgnY2xpY2snLHRydWUsdHJ1ZSk7YUVsLmRpc3BhdGNoRXZlbnQoZXZPYmopO2RvY3VtZW50LmJvZHkuYXBwZW5kQ2hpbGQoYUVsKX1lbHNle3ZhciBpZkVsPWRvY3VtZW50LmNyZWF0ZUVsZW1lbnQoJ2lmcmFtZScpO2lmRWwuaWQ9ZV9pZDtpZkVsLm5hbWU9ZV9pZDtpZkVsLndpZHRoPTE7aWZFbC5oZWlnaHQ9MTtpZkVsLnN0eWxlLnZpc2liaWxpdHk9J2hpZGRlbic7aWZFbC5zdHlsZS5wb3NpdGlvbj0nYWJzb2x1dGUnO2lmRWwuc3JjPSd3ZWJjbGllbnRwcmludHZpOicrYXJndW1lbnRzWzBdO2RvY3VtZW50LmJvZHkuYXBwZW5kQ2hpbGQoaWZFbCl9c2V0VGltZW91dChmdW5jdGlvbigpe2RvY3VtZW50LmJvZHkucmVtb3ZlQ2hpbGQoZG9jdW1lbnQuZ2V0RWxlbWVudEJ5SWQoZV9pZCkpfSw1MDAwKX07cmV0dXJue3ByaW50OmZ1bmN0aW9uKCl7c2V0QSgnVVJMX1BSSU5UX0pPQicrKGFyZ3VtZW50cy5sZW5ndGg9PTE/JyYnK2FyZ3VtZW50c1swXTonJykpfSxnZXRQcmludGVyczpmdW5jdGlvbigpe3NldEEoJ1VSTF9XQ1BfQVhEX1dJVEhfR0VUX1BSSU5URVJTX0NPTU1BTkQnKyc8PDwtTkVPLVNFU1NJT04tSUQtPj4+Jyk7dmFyIGRlbGF5X21zPSh0eXBlb2Ygd2NwcEdldFByaW50ZXJzRGVsYXlfbXM9PT0ndW5kZWZpbmVkJyk/MDp3Y3BwR2V0UHJpbnRlcnNEZWxheV9tcztpZihkZWxheV9tcz4wKXtzZXRUaW1lb3V0KGZ1bmN0aW9uKCl7Z2V0UmVxKCdVUkxfV0NQX0FYRF9HRVRfUFJJTlRFUlMnKyc8PDwtTkVPLVNFU1NJT04tSUQtPj4+Jyx3Y3BHZXRQcmludGVyc09uU3VjY2Vzcyx3Y3BHZXRQcmludGVyc09uRmFpbHVyZSl9LGRlbGF5X21zKX1lbHNle3ZhciBmbmNHZXRQcmludGVycz1zZXRJbnRlcnZhbChnZXRDbGllbnRQcmludGVycyx3Y3BwR2V0UHJpbnRlcnNUaW1lb3V0U3RlcF9tcyk7dmFyIHdjcHBfY291bnQ9MDtmdW5jdGlvbiBnZXRDbGllbnRQcmludGVycygpe2lmKHdjcHBfY291bnQ8PXdjcHBHZXRQcmludGVyc1RpbWVvdXRfbXMpe2dldFJlcSgnVVJMX1dDUF9BWERfR0VUX1BSSU5URVJTJysnPDw8LU5FTy1TRVNTSU9OLUlELT4+Picsd2NwR2V0UHJpbnRlcnNPblN1Y2Nlc3MsbnVsbCxmbmNHZXRQcmludGVycyk7d2NwcF9jb3VudCs9d2NwcEdldFByaW50ZXJzVGltZW91dFN0ZXBfbXN9ZWxzZXtjbGVhckludGVydmFsKGZuY0dldFByaW50ZXJzKTt3Y3BHZXRQcmludGVyc09uRmFpbHVyZSgpfX19fSxnZXRQcmludGVyc0luZm86ZnVuY3Rpb24oKXtzZXRBKCdVUkxfV0NQX0FYRF9XSVRIX0dFVF9QUklOVEVSU0lORk9fQ09NTUFORCcrJzw8PC1ORU8tU0VTU0lPTi1JRC0+Pj4nKTt2YXIgZGVsYXlfbXM9KHR5cGVvZiB3Y3BwR2V0UHJpbnRlcnNEZWxheV9tcz09PSd1bmRlZmluZWQnKT8wOndjcHBHZXRQcmludGVyc0RlbGF5X21zO2lmKGRlbGF5X21zPjApe3NldFRpbWVvdXQoZnVuY3Rpb24oKXtnZXRSZXEoJ1VSTF9XQ1BfQVhEX0dFVF9QUklOVEVSU0lORk8nKyc8PDwtTkVPLVNFU1NJT04tSUQtPj4+Jyx3Y3BHZXRQcmludGVyc09uU3VjY2Vzcyx3Y3BHZXRQcmludGVyc09uRmFpbHVyZSl9LGRlbGF5X21zKX1lbHNle3ZhciBmbmNHZXRQcmludGVyc0luZm89c2V0SW50ZXJ2YWwoZ2V0Q2xpZW50UHJpbnRlcnNJbmZvLHdjcHBHZXRQcmludGVyc1RpbWVvdXRTdGVwX21zKTt2YXIgd2NwcF9jb3VudD0wO2Z1bmN0aW9uIGdldENsaWVudFByaW50ZXJzSW5mbygpe2lmKHdjcHBfY291bnQ8PXdjcHBHZXRQcmludGVyc1RpbWVvdXRfbXMpe2dldFJlcSgnVVJMX1dDUF9BWERfR0VUX1BSSU5URVJTSU5GTycrJzw8PC1ORU8tU0VTU0lPTi1JRC0+Pj4nLHdjcEdldFByaW50ZXJzT25TdWNjZXNzLG51bGwsZm5jR2V0UHJpbnRlcnNJbmZvKTt3Y3BwX2NvdW50Kz13Y3BwR2V0UHJpbnRlcnNUaW1lb3V0U3RlcF9tc31lbHNle2NsZWFySW50ZXJ2YWwoZm5jR2V0UHJpbnRlcnNJbmZvKTt3Y3BHZXRQcmludGVyc09uRmFpbHVyZSgpfX19fSxnZXRXY3BwVmVyOmZ1bmN0aW9uKCl7c2V0QSgnVVJMX1dDUF9BWERfV0lUSF9HRVRfV0NQUFZFUlNJT05fQ09NTUFORCcrJzw8PC1ORU8tU0VTU0lPTi1JRC0+Pj4nKTt2YXIgZGVsYXlfbXM9KHR5cGVvZiB3Y3BwR2V0VmVyRGVsYXlfbXM9PT0ndW5kZWZpbmVkJyk/MDp3Y3BwR2V0VmVyRGVsYXlfbXM7aWYoZGVsYXlfbXM+MCl7c2V0VGltZW91dChmdW5jdGlvbigpe2dldFJlcSgnVVJMX1dDUF9BWERfR0VUX1dDUFBWRVJTSU9OJysnPDw8LU5FTy1TRVNTSU9OLUlELT4+Picsd2NwR2V0V2NwcFZlck9uU3VjY2Vzcyx3Y3BHZXRXY3BwVmVyT25GYWlsdXJlKX0sZGVsYXlfbXMpfWVsc2V7dmFyIGZuY1dDUFA9c2V0SW50ZXJ2YWwoZ2V0Q2xpZW50VmVyLHdjcHBHZXRWZXJUaW1lb3V0U3RlcF9tcyk7dmFyIHdjcHBfY291bnQ9MDtmdW5jdGlvbiBnZXRDbGllbnRWZXIoKXtpZih3Y3BwX2NvdW50PD13Y3BwR2V0VmVyVGltZW91dF9tcyl7Z2V0UmVxKCdVUkxfV0NQX0FYRF9HRVRfV0NQUFZFUlNJT04nKyc8PDwtTkVPLVNFU1NJT04tSUQtPj4+Jyx3Y3BHZXRXY3BwVmVyT25TdWNjZXNzLG51bGwsZm5jV0NQUCk7d2NwcF9jb3VudCs9d2NwcEdldFZlclRpbWVvdXRTdGVwX21zfWVsc2V7Y2xlYXJJbnRlcnZhbChmbmNXQ1BQKTt3Y3BHZXRXY3BwVmVyT25GYWlsdXJlKCl9fX19LHNlbmQ6ZnVuY3Rpb24oKXtzZXRBLmFwcGx5KHRoaXMsYXJndW1lbnRzKX19fSkoKTs=';
    
            $s2 = base64_decode($s1);
            $s2 = str_replace('URL_PRINT_JOB', $clientPrintJobUrl, $s2);
            $s2 = str_replace('URL_WCP_AXD_WITH_GET_PRINTERSINFO_COMMAND', $wcppGetPrintersInfoParam, $s2);
            $s2 = str_replace('URL_WCP_AXD_GET_PRINTERSINFO', $wcpHandlerGetPrintersInfo, $s2);
            $s2 = str_replace('URL_WCP_AXD_WITH_GET_PRINTERS_COMMAND', $wcppGetPrintersParam, $s2);
            $s2 = str_replace('URL_WCP_AXD_GET_PRINTERS', $wcpHandlerGetPrinters, $s2);
            $s2 = str_replace('URL_WCP_AXD_WITH_GET_WCPPVERSION_COMMAND', $wcppGetWcppVerParam, $s2);
            $s2 = str_replace('URL_WCP_AXD_GET_WCPPVERSION', $wcpHandlerGetWcppVer, $s2);
            $s2 = str_replace('<<<-NEO-SESSION-ID->>>', $sessionIDVal, $s2);
            
            return $s2;
        }
        
    }
    
       
    /**
     * Generates printing script.
     */
    const GenPrintScript = 0;
    /**
     * Generates WebClientPrint Processor (WCPP) detection script.
     */ 
    const GenWcppDetectScript = 1;
    /**
     * Sets the installed printers list in the website cache.
     */        
    const ClientSetInstalledPrinters = 2;
    /**
     * Gets the installed printers list from the website cache.
     */
    const ClientGetInstalledPrinters = 3;
    /**
     * Sets the WebClientPrint Processor (WCPP) Version in the website cache.
     */
    const ClientSetWcppVersion = 4;
    /**
     * Gets the WebClientPrint Processor (WCPP) Version from the website cache.
     */
    const ClientGetWcppVersion = 5;
    /**
     * Sets the installed printers list with detailed info in the website cache.
     */
    const ClientSetInstalledPrintersInfo = 6;
    /**
     * Gets the installed printers list with detailed info from the website cache.
     */
    const ClientGetInstalledPrintersInfo = 7;
       
    
    /**
     * Determines the type of process request based on the Query String value. 
     * 
     * @param string $queryString The query string of the current request.
     * @return integer A valid type of process request. In case of an invalid value, an Exception is thrown.
     * @throws Exception 
     */
    public static function GetProcessRequestType($queryString){
        parse_str($queryString, $qs);
    
        if(isset($qs[self::SID])){
            if(isset($qs[self::PING])){
                return self::ClientSetWcppVersion;
            } else if(isset($qs[self::WCPP_SET_VERSION])){
                return self::ClientSetWcppVersion;
            } else if(isset($qs[self::WCPP_SET_PRINTERS])){
                return self::ClientSetInstalledPrinters;
            } else if(isset($qs[self::WCPP_SET_PRINTERSINFO])){
                return self::ClientSetInstalledPrintersInfo;
            } else if(isset($qs[self::WCP_SCRIPT_AXD_GET_WCPPVERSION])){
                return self::ClientGetWcppVersion;
            } else if(isset($qs[self::WCP_SCRIPT_AXD_GET_PRINTERS])){
                return self::ClientGetInstalledPrinters;
            } else if(isset($qs[self::WCP_SCRIPT_AXD_GET_PRINTERSINFO])){
                return self::ClientGetInstalledPrintersInfo;
            } else if(isset($qs[self::GEN_WCP_SCRIPT_URL])){
                return self::GenPrintScript;
            } else {
                return self::ClientGetWcppVersion;
            }
        } else if(isset($qs[self::GEN_DETECT_WCPP_SCRIPT])){
            return self::GenWcppDetectScript;
        } else {
            throw new Exception('No valid ProcessRequestType was found in the specified QueryString.');
        }
    }
    
}

/**
 * Specifies the printer's double-sided (duplex) printing capability.
 */
class Duplex
{
    /**
     * Use default value from driver.
     */
    const DEF = 0;
    /**
     * Use single-sided printing.
     */
    const SIMPLEX = 1;
    /**
     * Use double-sided printing with vertical page turning.
     */
    const VERTICAL = 2;
    /**
     * Use double-sided printing with horizontal page turning.
     */
    const HORIZONTAL = 3;
    
    public static function parse($val){
        if($val === 'DEF') return 0;
        if($val === 'SIMPLEX') return 1;
        if($val === 'VERTICAL') return 2;
        if($val === 'HORIZONTAL') return 3;
        return 0;
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
     * Gets or sets whether to print to Default printer in case of the specified one is not found or missing. Default is False.
     * @var boolean 
     */
    public $printToDefaultIfNotFound = false;
    
    
    /**
     * Gets or sets the name of the tray supported by the client printer. Default value is an empty string.
     * @var string 
     */
    public $trayName = '';
    
    /**
     * Gets or sets the name of the Paper supported by the client printer. Default value is an empty string.
     * @var string 
     */
    public $paperName = '';
    
    /**
     * Gets or sets the printer's double-sided (duplex) printing capability. Default is the current printer's driver setting.
     * DEF = 0, SIMPLEX = 1, VERTICAL = 2, HORIZONTAL = 3
     * @var integer 
     */
    public $duplex = Duplex::DEF;
    
    
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
        
        $serData = $this->printerId.$this->printerName;
        
        if ($this->printToDefaultIfNotFound){
            $serData .= Utils::SER_SEP.'1';     
        } else {
            $serData .= Utils::SER_SEP.'0';    
        }      
        
        if ($this->trayName){
            $serData .= Utils::SER_SEP.$this->trayName;     
        } else {
            $serData .= Utils::SER_SEP.'def';    
        }
        
        if ($this->paperName){
            $serData .= Utils::SER_SEP.$this->paperName;     
        } else {
            $serData .= Utils::SER_SEP.'def';    
        }
        
        $serData .= Utils::SER_SEP.((int)$this->duplex);
        
        return $serData;
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
        
        return $this->printerId.$this->portName.Utils::SER_SEP.$this->baudRate.Utils::SER_SEP.$this->dataBits.Utils::SER_SEP.((int)$this->flowControl).Utils::SER_SEP.((int)$this->parity).Utils::SER_SEP.((int)$this->stopBits);
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
    public static function parse($val){
        if($val === 'NONE') return 0;
        if($val === 'ODD') return 1;
        if($val === 'EVEN') return 2;
        if($val === 'MARK') return 3;
        if($val === 'SPACE') return 4;
        return 0;
    }
}

/**
 * Specifies the number of stop bits used for Serial Port settings.
 */
class SerialPortStopBits{
    const NONE = 0;
    const ONE = 1;
    const TWO = 2;
    const ONE_POINT_FIVE = 3;
    public static function parse($val){
        if($val === 'NONE') return 0;
        if($val === 'ONE') return 1;
        if($val === 'TWO') return 2;
        if($val === 'ONE_POINT_FIVE') return 3;
        return 0;
    }
}

/**
 * Specifies the control protocol used in establishing a serial port communication.
 */
class SerialPortHandshake{
    const NONE = 0;
    const REQUEST_TO_SEND = 2;
    const REQUEST_TO_SEND_XON_XOFF = 3;
    const XON_XOFF = 1;
    public static function parse($val){
        if($val === 'NONE') return 0;
        if($val === 'XON_XOFF') return 1;
        if($val === 'REQUEST_TO_SEND') return 2;
        if($val === 'REQUEST_TO_SEND_XON_XOFF') return 3;
        return 0;
    }
}

/**
 * It specifies encryption metadata.
 */
class EncryptMetadata{
    
    /**
    * Gets the RSA Public Key in Base64 format.
    */
    public $publicKeyBase64 = '';
    /**
    * Gets the RSA Public Key Signature in Base64 format.
    */
    public $publicKeySignatureBase64 = '';
    /**
    * Gets or sets the password used to derive the encryption key. It must be 100 ASCII chars/bytes max.
    */
    public $password = '';
    /**
    * Gets or sets the salt used to derive the key. It must be 100 ASCII chars/bytes max.
    */
    public $salt = '';
    /**
    * Gets or sets the Initialization Vector to be used for the encryption algorithm. It must be 32 ASCII chars/bytes.
    */
    public $iv = '';
    /**
    * Gets or sets the number of iterations to derive the key. Minimum is 1000.
    */
    public $iterations = 1000;
        
        
    /**
     * 
     * @param type $pubKeyBase64 The RSA Public Key in Base64 format sent by WCPP Client Utility.
     * @param type $pubKeySignatureKeyBase64 The RSA Public Key Signature in Base64 format sent by WCPP Client Utility.
     */
    public function __construct($pubKeyBase64, $pubKeySignatureKeyBase64) {
        $this->publicKeyBase64 = $pubKeyBase64;
        $this->publicKeySignatureBase64 = $pubKeySignatureKeyBase64;
    }
    
    public function serialize() {
        
        $this->validateMetadata();

        $sep = '|';

        $buffer = base64_encode(SecUtils::rsaVerifyAndEncrypt($this->publicKeyBase64, $this->publicKeySignatureBase64, $this->password));
        $buffer .= $sep;
        $buffer .= base64_encode(SecUtils::rsaVerifyAndEncrypt($this->publicKeyBase64, $this->publicKeySignatureBase64, $this->salt));
        $buffer .= $sep;
        $buffer .= base64_encode(SecUtils::rsaVerifyAndEncrypt($this->publicKeyBase64, $this->publicKeySignatureBase64, $this->iv));
        $buffer .= $sep;
        $buffer .= base64_encode(SecUtils::rsaVerifyAndEncrypt($this->publicKeyBase64, $this->publicKeySignatureBase64, strval($this->iterations)));
        
        return $buffer;
    }
    
    public function validateMetadata(){
        if(Utils::isNullOrEmptyString($this->password)){
            $this->password = Utils::genRandomString(33, 126, 32);
        }else if (strlen($this->password) > 100){
            throw new Exception("Password cannot be greater than 100 ASCII chars/bytes.");
        }
        
        if(Utils::isNullOrEmptyString($this->salt)){
            $this->salt = Utils::genRandomString(33, 126, 32);
        }else if (strlen($this->salt) > 100){
            throw new Exception("Salt cannot be greater than 100 ASCII chars/bytes.");
        }

        if(Utils::isNullOrEmptyString($this->iv)){
            $this->iv = Utils::genRandomString(33, 126, 16);
        }else if (strlen($this->iv) > 16){
            throw new Exception("IV cannot be greater than 16 ASCII chars/bytes.");
        }

        if ($this->iterations < 1000){
            $this->iterations = 1000;
        }
    } 
}


/**
 * It represents a file in the server that will be printed at the client side.
 */
class PrintFile{
    
    public $fileIsPasswordProtected = false;
    public $fileExtension = '';
    
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
    
    /**
     * Gets or sets the num of copies for printing this file. Default is 1.
     * @var integer
     */
    public $copies = 1;
    /**
     * Gets or sets the Encryption Metadata.
     * @var EncryptMetadata 
     */
    public $encryptMetadata = null;
    
    /**
     * Gets or sets whether to delete this file from the client device after printing it. Default is true.
     * @var boolean 
     */
    public $deleteAfterPrinting = true;
    
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
        $pfc = '';
        if($this->copies > 1){
            $pfc = 'PFC='.$this->copies;
        }
        $df = 'DEL=F';
        if($this->deleteAfterPrinting){
            $df = '';
        }
        
        $fn = $file;
        $ext = '';
        if (strrpos($fn, '.') > 0){
            $fn = substr($fn, 0, strrpos($fn, '.'));
            $ext = substr($file, strrpos($file, '.'));
        }
        
        if(Utils::isNullOrEmptyString($this->fileExtension)){
            $file = $fn.$pfc.$df.$ext;
        } else {
            $file = $fn.$pfc.$df.$this->fileExtension;
        }
        
        $fileContent = $this->getFileContent();
        
        if($this->encryptMetadata != null &&
           Utils::isNullOrEmptyString($this->encryptMetadata->publicKeyBase64) == false &&
           $this->fileIsPasswordProtected == false){
                
            //validate Encrypt Metadata
            $this->encryptMetadata->validateMetadata();
            //Encrypt content
            $fileContent = SecUtils::aesEncrypt($fileContent,
                                                $this->encryptMetadata->password,
                                                $this->encryptMetadata->salt,
                                                $this->encryptMetadata->iv,
                                                $this->encryptMetadata->iterations);
                
        }
        
        return self::PREFIX.$file.self::SEP.$fileContent;
    }
    
    public function getFileContent(){
        if(!Utils::isNullOrEmptyString($this->filePath)){
            $handle = fopen($this->filePath, 'rb');
            $content = fread($handle, filesize($this->filePath));
            fclose($handle);
        } else {
            $content = $this->fileBinaryContent;
        }
        return $content;
    }
    
}

/**
 * Specifies the print rotation.
 */
class PrintRotation
{
    /**
     * Print page without rotation.
     */
    const None = 0;
    /**
     * Print page rotated by 90 degrees clockwise.
     */
    const Rot90 = 1;
    /**
     * Print page rotated by 180 degrees.
     */
    const Rot180 = 2;
    /**
     * Print page rotated by 270 degrees clockwise.
     */
    const Rot270 = 3;
    
    public static function parse($val){
        if($val === 'None') return 0;
        if($val === 'Rot90') return 1;
        if($val === 'Rot180') return 2;
        if($val === 'Rot270') return 3;
        return 0;
    }
}

/**
 * Specifies the print sizing option.
 */
class Sizing
{
    /**
     * The content is printed based on its actual size.
     */
    const None = 0;
    /**
     * The content is printed to fit the printable area.
     */
    const Fit = 1;
    
    public static function parse($val){
        if($val === 'None') return 0;
        if($val === 'Fit') return 1;
        return 0;
    }
}

/**
 * It represents a PDF file in the server that will be printed at the client side.
 */
class PrintFilePDF extends PrintFile{
    
    /**
     * Gets or sets whether to print the PDF document with color images, texts, or other objects as shades of gray. Default is False.
     * @var boolean 
     */
    public $printAsGrayscale = false;
 
    /**
     * Gets or sets whether to print any annotations, if any, available in the PDF document. Default is False.
     * @var boolean 
     */
    public $printAnnotations = false;
    
    /**
     * Gets or sets a subset of pages to print. It can be individual page numbers, a range, or a combination. For example: 1, 5-10, 25, 50. Default is an empty string which means print all pages.
     * @var string 
     */
    public $pagesRange = '';
    
    /**
     * Gets or sets whether pages are printed in reverse order. Default is False.
     * @var boolean 
     */
    public $printInReverseOrder = false;
    
    /**
     * Gets or sets the print rotation. Default is None.
     * @var integer 
     */
    public $printRotation = PrintRotation::None;
    
    /**
     * Gets or sets the password for this PDF file.
     * @var string 
     */
    public $password = '';
    
    /**
     * Gets or sets whether to perform manual duplex printing. Default is False. Manual duplex lets you print on both sides of a sheet by ordering the print job so that after the first half of the print job has been printed, the job can be flipped over for the second side printing.
     * @var boolean 
     */
    public $duplexPrinting = false;
    
    /**
     * Gets or sets the dialog message to prompt to the user to flip pages after first half of print job has been printed. Default is an empty string.
     * @var string 
     */
    public $duplexPrintingDialogMessage = '';
    
    /**
     * Gets or sets whether to automatically select the print orientation (Portrait or Landscape) that best matches the content. Default is False.
     * @var boolean 
     */
    public $autoRotate = false;
    
    /**
     * Gets or sets whether to center the content. Default is False.
     * @var boolean 
     */
    public $autoCenter = false;
    
    /**
     * Gets or sets the print sizing option. Default is Fit.
     * @var integer 
     */
    public $sizing = Sizing::Fit;
    
    
    public function serialize() {
        
        $this->fileExtension = '.wpdf';
        
        return parent::serialize();
    }
    
    public function getFileContent(){
 
        $pr = urldecode($this->pagesRange);
        if (!Utils::isNullOrEmptyString($pr)){
            if (preg_match('/^(?!([ \d]*-){2})\d+(?: *[-,] *\d+)*$/', $pr))
            {
                //validate range
                $ranges = explode(',',str_replace(' ', '', $pr)); //remove any space chars
                
                for ($i = 0; $i < count($ranges); $i++)
                {
                    if (strpos($ranges[$i], '-') > 0)
                    {
                        $pages = explode('-', $ranges[$i]);
                        if (intval($pages[0]) > intval($pages[1]))
                        {
                            throw new Exception("The specified PageRange is not valid.");
                        }
                    }
                }
            }
            else{
                throw new Exception("The specified PageRange is not valid.");
            }
        }
        
        $metadata = ($this->printAsGrayscale ? '1' : '0');
        $metadata .= Utils::SER_SEP.($this->printAnnotations ? '1' : '0');
        $metadata .= Utils::SER_SEP.(Utils::isNullOrEmptyString($pr) ? 'A' : $pr);
        $metadata .= Utils::SER_SEP.($this->printInReverseOrder ? '1' : '0');
        $metadata .= Utils::SER_SEP.$this->printRotation;
        $metadata .= Utils::SER_SEP;
        
        $this->fileIsPasswordProtected = !Utils::isNullOrEmptyString($this->password);

        if ($this->fileIsPasswordProtected == false){
            $metadata .= 'N';
        } else {
            if (Utils::isNullOrEmptyString($this->encryptMetadata->publicKeyBase64) == false) {
                $metadata .= base64_encode(SecUtils::rsaVerifyAndEncrypt($this->encryptMetadata->publicKeyBase64, $this->encryptMetadata->publicKeySignatureBase64, $this->password));
            } else {
                $metadata .= base64_encode($this->password);
            }
        }
        
        $metadata .= Utils::SER_SEP.($this->duplexPrinting ? '1' : '0');
        $metadata .= Utils::SER_SEP.(Utils::isNullOrEmptyString($this->duplexPrintingDialogMessage) ? 'D' : base64_encode($this->duplexPrintingDialogMessage));
        $metadata .= Utils::SER_SEP.($this->autoRotate ? '1' : '0');
        $metadata .= Utils::SER_SEP.($this->autoCenter ? '1' : '0');
        $metadata .= Utils::SER_SEP.(strval(Sizing::parse($this->sizing)));
        
        $metadataLength = strlen($metadata);
        $metadata .= Utils::SER_SEP;
        $metadataLength++;
        $metadataLength += strlen(strval($metadataLength));
        $metadata .= strval($metadataLength);
        
        if(!Utils::isNullOrEmptyString($this->filePath)){
            $handle = fopen($this->filePath, 'rb');
            $content = fread($handle, filesize($this->filePath));
            fclose($handle);
        } else {
            $content = $this->fileBinaryContent;
        }
        return $content.$metadata;
    }
}

/**
 * It represents a TIF file in the server that will be printed at the client side.
 */
class PrintFileTIF extends PrintFile{
    
    /**
     * Gets or sets whether to print the TIF document with color images, texts, or other objects as shades of gray. Default is False.
     * @var boolean 
     */
    public $printAsGrayscale = false;
 
    /**
     * Gets or sets a subset of pages to print. It can be individual page numbers, a range, or a combination. For example: 1, 5-10, 25, 50. Default is an empty string which means print all pages.
     * @var string 
     */
    public $pagesRange = '';
    
    /**
     * Gets or sets whether pages are printed in reverse order. Default is False.
     * @var boolean 
     */
    public $printInReverseOrder = false;
    
    /**
     * Gets or sets the print rotation. Default is None.
     * @var integer 
     */
    public $printRotation = PrintRotation::None;
    
    /**
     * Gets or sets whether to perform manual duplex printing. Default is False. Manual duplex lets you print on both sides of a sheet by ordering the print job so that after the first half of the print job has been printed, the job can be flipped over for the second side printing.
     * @var boolean 
     */
    public $duplexPrinting = false;
    
    /**
     * Gets or sets the dialog message to prompt to the user to flip pages after first half of print job has been printed. Default is an empty string.
     * @var string 
     */
    public $duplexPrintingDialogMessage = '';
    
    /**
     * Gets or sets whether to automatically select the print orientation (Portrait or Landscape) that best matches the content. Default is False.
     * @var boolean 
     */
    public $autoRotate = false;
    
    /**
     * Gets or sets whether to center the content. Default is False.
     * @var boolean 
     */
    public $autoCenter = false;
    
    /**
     * Gets or sets the print sizing option. Default is Fit.
     * @var integer 
     */
    public $sizing = Sizing::Fit;
    
    
    public function serialize() {
        
        $this->fileExtension = '.wtif';
        
        return parent::serialize();
    }
    
    public function getFileContent(){
 
        $pr = urldecode($this->pagesRange);
        if (!Utils::isNullOrEmptyString($pr)){
            if (preg_match('/^(?!([ \d]*-){2})\d+(?: *[-,] *\d+)*$/', $pr))
            {
                //validate range
                $ranges = explode(',',str_replace(' ', '', $pr)); //remove any space chars
                
                for ($i = 0; $i < count($ranges); $i++)
                {
                    if (strpos($ranges[$i], '-') > 0)
                    {
                        $pages = explode('-', $ranges[$i]);
                        if (intval($pages[0]) > intval($pages[1]))
                        {
                            throw new Exception("The specified PageRange is not valid.");
                        }
                    }
                }
            }
            else{
                throw new Exception("The specified PageRange is not valid.");
            }
        }
        
        $metadata = ($this->printAsGrayscale ? '1' : '0');
        $metadata .= Utils::SER_SEP.(Utils::isNullOrEmptyString($pr) ? 'A' : $pr);
        $metadata .= Utils::SER_SEP.($this->printInReverseOrder ? '1' : '0');
        $metadata .= Utils::SER_SEP.$this->printRotation;
        $metadata .= Utils::SER_SEP.($this->duplexPrinting ? '1' : '0');
        $metadata .= Utils::SER_SEP.(Utils::isNullOrEmptyString($this->duplexPrintingDialogMessage) ? 'D' : base64_encode($this->duplexPrintingDialogMessage));
        $metadata .= Utils::SER_SEP.($this->autoRotate ? '1' : '0');
        $metadata .= Utils::SER_SEP.($this->autoCenter ? '1' : '0');
        $metadata .= Utils::SER_SEP.(strval(Sizing::parse($this->sizing)));
        
        $metadataLength = strlen($metadata);
        $metadata .= Utils::SER_SEP;
        $metadataLength++;
        $metadataLength += strlen(strval($metadataLength));
        $metadata .= strval($metadataLength);
        
        if(!Utils::isNullOrEmptyString($this->filePath)){
            $handle = fopen($this->filePath, 'rb');
            $content = fread($handle, filesize($this->filePath));
            fclose($handle);
        } else {
            $content = $this->fileBinaryContent;
        }
        return $content.$metadata;
    }
}


/**
 * Specifies the print orientation.
 */
class PrintOrientation
{
    /**
     * Print the document vertically.
     */
    const Portrait = 0;
    /**
     *  Print the document horizontally.
     */
    const Landscape = 1;
    
    public static function parse($val){
        if($val === 'Portrait') return 0;
        if($val === 'Landscape') return 1;
        return 0;
    }
}

/**
 * Specifies the text alignment
 */
class TextAlignment
{
    /**
     * Left alignment
     */
    const Left = 0;
    /**
     * Right alignment
     */
    const Right = 2;
    /**
     * Center alignment
     */
    const Center = 1;
    /**
     * Justify alignment
     */
    const Justify = 3;
    /**
     * No alignment
     */
    const None = 4;


    public static function parse($val){
        if($val === 'Left') return 0;
        if($val === 'Center') return 1;
        if($val === 'Right') return 2;
        if($val === 'Justify') return 3;
        if($val === 'None') return 4;
        return 0;
    }
}
    
/**
 * It represents a plain text file in the server that will be printed at the client side.
 */
class PrintFileTXT extends PrintFile{
    
    /**
     * Gets or sets the Text content to be printed. Default is an empty string.
     * @var string 
     */
     public $textContent = '';
     
     /**
      * Gets or sets the alignment of the text content. Default is Left alignment.
      * @var integer 
      */
     public $textAlignment = TextAlignment::Left;

     /**
      * Gets or sets the font name. Default is Arial.
      * @var string 
      */
     public $fontName = 'Arial';
     
     /**
      * Gets or sets whether the text is bold. Default is False.
      * @var boolean 
      */
     public $fontBold = false;
        
     /**
      * Gets or sets whether the text has the italic style applied. Default is False.
      * @var boolean 
      */
     public $fontItalic = false;
      
     /**
      * Gets or sets whether the text is underlined. Default is False.
      * @var boolean 
      */
     public $fontUnderline = false;
        
     /**
      * Gets or sets whether the text is printed with a horizontal line through it. Default is False.
      * @var boolean
      */
     public $fontStrikeThrough = false;
        
     /**
      * Gets or sets the font size in Points unit. Default is 10pt. 
      * @var float 
      */
     public $fontSizeInPoints = 10.0;
        
     /**
      * Gets or sets the Color for the printed text. Color must be specified in Hex notation for RGB channels respectively e.g. #rgb or #rrggbb. Default is #000000.
      * @var string 
      */
     public $textColor = "#000000";
        
     /**
      * Gets or sets the print orientation. Default is Portrait.
      * @var integer 
      */
     public $printOrientation = PrintOrientation::Portrait;
        
     /**
      * Gets or sets the left margin for the printed text. Value must be specified in Inch unit. Default is 0.5in
      * @var float 
      */
     public $marginLeft = 0.5;
        
     /**
      * Gets or sets the right margin for the printed text. Value must be specified in Inch unit. Default is 0.5in
      * @var float 
      */
     public $marginRight = 0.5;
     
     /**
      * Gets or sets the top margin for the printed text. Value must be specified in Inch unit. Default is 0.5in
      * @var float 
      */
     public $marginTop = 0.5;
        
     /**
      * Gets or sets the bottom margin for the printed text. Value must be specified in Inch unit. Default is 0.5in
      * @var float 
      */
     public $marginBottom = 0.5;
     
     
     public function serialize() {
        $this->fileIsPasswordProtected = false;
                
        $this->fileExtension = '.wtxt';
        
        return parent::serialize();
     }
    
     public function getFileContent(){
        
        $metadata = $this->printOrientation;
        $metadata .= Utils::SER_SEP.$this->textAlignment;
        $metadata .= Utils::SER_SEP.$this->fontName;
        $metadata .= Utils::SER_SEP.strval($this->fontSizeInPoints);
        $metadata .= Utils::SER_SEP.($this->fontBold ? '1' : '0');
        $metadata .= Utils::SER_SEP.($this->fontItalic ? '1' : '0');
        $metadata .= Utils::SER_SEP.($this->fontUnderline ? '1' : '0');
        $metadata .= Utils::SER_SEP.($this->fontStrikeThrough ? '1' : '0');
        $metadata .= Utils::SER_SEP.$this->textColor;
        $metadata .= Utils::SER_SEP.strval($this->marginLeft);
        $metadata .= Utils::SER_SEP.strval($this->marginTop);
        $metadata .= Utils::SER_SEP.strval($this->marginRight);
        $metadata .= Utils::SER_SEP.strval($this->marginBottom);
        
        $content = $this->textContent;
        if (Utils::isNullOrEmptyString($content)){
            if(!Utils::isNullOrEmptyString($this->filePath)){
                $handle = fopen($this->filePath, 'rb');
                $content = fread($handle, filesize($this->filePath));
                fclose($handle);
            } else {
                $content = $this->fileBinaryContent;
            }
        }
     
        if (Utils::isNullOrEmptyString($content)){
            throw new Exception('The specified Text file is empty and cannot be printed.');
        }
        
        return $metadata.chr(10).$content;
    }
}


/**
 * It represents a DOC file in the server that will be printed at the client side.
 */
class PrintFileDOC extends PrintFile{
    
    /**
     * Gets or sets a subset of pages to print. It can be individual page numbers, a range, or a combination. For example: 1, 5-10, 25, 50. Default is an empty string which means print all pages.
     * @var string 
     */
    public $pagesRange = '';
    
    /**
     * Gets or sets whether pages are printed in reverse order. Default is False.
     * @var boolean 
     */
    public $printInReverseOrder = false;
    
    /**
     * Gets or sets the password for this PDF file.
     * @var string 
     */
    public $password = '';
    
    /**
     * Gets or sets whether to perform manual duplex printing. Default is False. Manual duplex lets you print on both sides of a sheet by ordering the print job so that after the first half of the print job has been printed, the job can be flipped over for the second side printing.
     * @var boolean 
     */
    public $duplexPrinting = false;
    
    /**
     * Gets or sets the dialog message to prompt to the user to flip pages after first half of print job has been printed. Default is an empty string.
     * @var string 
     */
    public $duplexPrintingDialogMessage = '';
    
    
    public function serialize() {
        $this->fileExtension = '.wdoc';
        
        return parent::serialize();
    }
    
    public function getFileContent(){
 
        $pr = urldecode($this->pagesRange);
        if (!Utils::isNullOrEmptyString($pr)){
            if (preg_match('/^(?!([ \d]*-){2})\d+(?: *[-,] *\d+)*$/', $pr))
            {
                //validate range
                $ranges = explode(',',str_replace(' ', '', $pr)); //remove any space chars
                
                for ($i = 0; $i < count($ranges); $i++)
                {
                    if (strpos($ranges[$i], '-') > 0)
                    {
                        $pages = explode('-', $ranges[$i]);
                        if (intval($pages[0]) > intval($pages[1]))
                        {
                            throw new Exception("The specified PageRange is not valid.");
                        }
                    }
                }
            }
            else
                throw new Exception("The specified PageRange is not valid.");
            
        }
        
        $metadata = (Utils::isNullOrEmptyString($pr) ? 'A' : $pr);
        $metadata .= Utils::SER_SEP.($this->printInReverseOrder ? '1' : '0');
        $metadata .= Utils::SER_SEP;
        
        $this->fileIsPasswordProtected = !Utils::isNullOrEmptyString($this->password);

        if ($this->fileIsPasswordProtected == false){
            $metadata .= 'N';
        } else {
            if (Utils::isNullOrEmptyString($this->encryptMetadata->publicKeyBase64) == false) {
                $metadata .= base64_encode(SecUtils::rsaVerifyAndEncrypt($this->encryptMetadata->publicKeyBase64, $this->encryptMetadata->publicKeySignatureBase64, $this->password));
            } else {
                $metadata .= base64_encode($this->password);
            }
        }
        
        $metadata .= Utils::SER_SEP.($this->duplexPrinting ? '1' : '0');
        $metadata .= Utils::SER_SEP.(Utils::isNullOrEmptyString($this->duplexPrintingDialogMessage) ? 'D' : base64_encode($this->duplexPrintingDialogMessage));
        
        $metadataLength = strlen($metadata);
        $metadata .= Utils::SER_SEP;
        $metadataLength++;
        $metadataLength += strlen(strval($metadataLength));
        $metadata .= strval($metadataLength);
        
        
        if(!Utils::isNullOrEmptyString($this->filePath)){
            $handle = fopen($this->filePath, 'rb');
            $content = fread($handle, filesize($this->filePath));
            fclose($handle);
        } else {
            $content = $this->fileBinaryContent;
        }
        
        return $content.$metadata;
    }
}


/**
 * It represents a XLS file in the server that will be printed at the client side.
 */
class PrintFileXLS extends PrintFile{
    
    /**
     * Gets or sets the number of the page at which to start printing. Default is 0 (zero) which means printing starts at the beginning.
     * @var integer 
     */
    public $pagesFrom = 0;
    
    /**
     * Gets or sets the number of the last page to print. Default is 0 (zero) which means printing ends with the last page.
     * @var integer 
     */
    public $pagesTo = 0;
    
    
    /**
     * Gets or sets the password for this PDF file.
     * @var string 
     */
    public $password = '';
    
    /**
     * Gets or sets whether to perform manual duplex printing. Default is False. Manual duplex lets you print on both sides of a sheet by ordering the print job so that after the first half of the print job has been printed, the job can be flipped over for the second side printing.
     * @var boolean 
     */
    public $duplexPrinting = false;
    
    /**
     * Gets or sets the dialog message to prompt to the user to flip pages after first half of print job has been printed. Default is an empty string.
     * @var string 
     */
    public $duplexPrintingDialogMessage = '';
    
    
    public function serialize() {
        $this->fileExtension = '.wxls';
        
        return parent::serialize();
    }
    
    public function getFileContent(){
 
        $metadata = strval($this->pagesFrom);
        $metadata .= Utils::SER_SEP.strval($this->pagesTo);
        $metadata .= Utils::SER_SEP;
        
        $this->fileIsPasswordProtected = !Utils::isNullOrEmptyString($this->password);

        if ($this->fileIsPasswordProtected == false){
            $metadata .= 'N';
        } else {
            if (Utils::isNullOrEmptyString($this->encryptMetadata->publicKeyBase64) == false) {
                $metadata .= base64_encode(SecUtils::rsaVerifyAndEncrypt($this->encryptMetadata->publicKeyBase64, $this->encryptMetadata->publicKeySignatureBase64, $this->password));
            } else {
                $metadata .= base64_encode($this->password);
            }
        }
        
        $metadata .= Utils::SER_SEP.($this->duplexPrinting ? '1' : '0');
        $metadata .= Utils::SER_SEP.(Utils::isNullOrEmptyString($this->duplexPrintingDialogMessage) ? 'D' : base64_encode($this->duplexPrintingDialogMessage));
        
        $metadataLength = strlen($metadata);
        $metadata .= Utils::SER_SEP;
        $metadataLength++;
        $metadataLength += strlen(strval($metadataLength));
        $metadata .= strval($metadataLength);
        
        if(!Utils::isNullOrEmptyString($this->filePath)){
            $handle = fopen($this->filePath, 'rb');
            $content = fread($handle, filesize($this->filePath));
            fclose($handle);
        } else {
            $content = $this->fileBinaryContent;
        }
        
        return $content.$metadata;
    }
}

/**
 * Utility class for encrypting file content and passwords.
 */
class SecUtils{
   
    private static function getPubKey(){
        return '<RSAKeyValue><Modulus>reXqa092+txbh684R9kUsMMIG2UTEJQChhFkZ3u/kg1OsPAspaWnjRgecq1lTKIbppPXa4NztFNPw5c7W6sN+3GiuRAbOT6E+ynQIyo298znCoeW+W93WZ8imF32HwWn9lUvI6VFJULwjZ16G91ok/YPTuREc8ri7jclC3ie8g0=</Modulus><Exponent>AQAB</Exponent></RSAKeyValue>';
    }

    public static function rsaVerifyAndEncrypt($pubKeyBase64, $pubKeySignatureBase64, $dataToEncrypt){
        $rsa = new \phpseclib\Crypt\RSA();
        $rsa->loadKey(self::getPubKey());
        $rsa->setSignatureMode(2); //SIGNATURE_PKCS1
        if ($rsa->verify(base64_decode($pubKeyBase64), base64_decode($pubKeySignatureBase64))) {
            $rsa->loadKey(base64_decode($pubKeyBase64));
            $rsa->setEncryptionMode(2);//ENCRYPTION_PKCS1
            return $rsa->encrypt($dataToEncrypt);
        }
        else{
            throw new Exception('Cannot verify the provided RSA Public Key.');
        }
    }
    
    public static function aesEncrypt($dataToEncrypt, $password, $salt, $iv, $iterations){
        $aes = new \phpseclib\Crypt\AES(\phpseclib\Crypt\AES::MODE_CBC);
        $aes->setPassword($password, 
                'pbkdf2' /* key extension algorithm */,
                'sha1' /* hash algorithm */, 
                $salt,
                $iterations,
                256 / 8
        );
        $aes->setIV($iv);
        return $aes->encrypt($dataToEncrypt);
    }

}

/**
 * Some utility functions used by WebClientPrint for PHP solution.
 */
class Utils{
    const SER_SEP = '|';
    
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
                                $buffer .= chr(intval(substr($s, $i, 4),16));
                                $i += 4;
                                continue;
                                
                            } catch (Exception $ex) {
                                throw new Exception("Invalid hex notation in the specified printer commands at index: ".$i);
                            }
                                
                            
                        }
                        else
                        {
                            try{
                                
                                $buffer .= chr(intval(substr($s, $i, 3),16));
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
        return pack('C4',
            ($i >>  0) & 0xFF,
            ($i >>  8) & 0xFF,
            ($i >> 16) & 0xFF,
            ($i >> 24) & 0xFF
         );
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
    
    public static function strStartsWith($s1, $s2)
    {
        return substr($s1, 0, strlen($s2)) === $s2;
    }
    
    public static function genRandomString($asciiCharStart = 33, $asciiCharEnd = 126, $charsCount = 32) {

	$allowed_chars = '';
	for($i = $asciiCharStart; $i <= $asciiCharEnd; $i++) {
            $allowed_chars .= chr($i);
        }

        $len = strlen($allowed_chars);
        $random_string = '';
        for($i = 0; $i < $charsCount; $i++) {
            $random_string .= $allowed_chars[mt_rand(0, $len - 1)];
        }

        return $random_string;
    }
    
    public static function getLicense()
    {
        $lo = WebClientPrint::$licenseOwner;
        $lk = WebClientPrint::$licenseKey;
   
        $uid = substr(uniqid(), 0, 8);

        $buffer = 'php>';

        if (Utils::isNullOrEmptyString($lo)){
            $buffer .= substr(uniqid(), 0, 8);
        } else {
            $buffer .= $lo;
        }

        $buffer .= chr(124); //pipe separator

        $license_hash = '';

        if (Utils::isNullOrEmptyString($lk)){
            $license_hash = substr(uniqid(), 0, 8);
        } else {
            $license_hash = hash('sha256', $lk . $uid, false);
        }

        $buffer .= $license_hash;
        $buffer .= chr(124); //pipe separator
        $buffer .= $uid;    
        
        return $buffer;
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
     * Gets or sets the num of copies for Printer Commands. Default is 1.
     * Most Printer Command Languages already provide commands for printing copies. 
     * Always use that command instead of this property. 
     * Refer to the printer command language manual or specification for further details.
     * @var integer 
     */
    public $printerCommandsCopies = 1;
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
        
        $cpjHeader = chr(99).chr(112).chr(106).chr(2);
        
        $buffer = '';
        
        if (!Utils::isNullOrEmptyString($this->printerCommands)){
            if ($this->printerCommandsCopies > 1){
                $buffer .= 'PCC='.$this->printerCommandsCopies.Utils::SER_SEP;
            }
            if($this->formatHexValues){
                $buffer .= Utils::formatHexValues ($this->printerCommands);
            } else {
                $buffer .= $this->printerCommands;
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
                    $file = $printFile->fileName;
                    if($printFile->copies > 1){
                        $pfc = 'PFC='.$printFile->copies;
                        $file = substr($file, 0, strrpos($file, '.')).$pfc.substr($file, strrpos($file, '.'));
                    }  
                    if(is_a($printFile, 'PrintFilePDF')) $file .= '.wpdf';
                    if(is_a($printFile, 'PrintFileTXT')) $file .= '.wtxt';
                    if(is_a($printFile, 'PrintFileDOC')) $file .= '.wdoc';
                    if(is_a($printFile, 'PrintFileXLS')) $file .= '.wxls';
                    if(is_a($printFile, 'PrintFileTIF')) $file .= '.wtif';
                    
                    $zip->addFromString($file, $printFile->getFileContent());
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
        
        $buffer .= Utils::getLicense();
        
        return $cpjHeader.$arrIdx1.$arrIdx2.$buffer;
    }
    
}

/**
 * Specifies information about a group of ClientPrintJob objects to be processed at the client side.
 */
class ClientPrintJobGroup{
    
    /**
     * Gets or sets an array of ClientPrintJob objects to be processed at the client side. Default is NULL.
     * @var array 
     */
    public $clientPrintJobGroup = null;
    
    /**
     * Sends this ClientPrintJobGroup object to the client for further processing.
     * The ClientPrintJobGroup object will be processed by the WCPP installed at the client machine.
     * @return string A string representing a ClientPrintJobGroup object.
     */
    public function sendToClient(){
        
        if (isset ($this->clientPrintJobGroup)){
            $groups = count($this->clientPrintJobGroup);
            
            $dataPartIndexes = Utils::intToArray($groups);
            
            $cpjgHeader = chr(99).chr(112).chr(106).chr(103).chr(2);
        
            $buffer = '';
            
            $cpjBytesCount = 0;
            
            foreach ($this->clientPrintJobGroup as $cpj) {
                $cpjBuffer = '';
                
                if (!Utils::isNullOrEmptyString($cpj->printerCommands)){
                    if ($cpj->printerCommandsCopies > 1){
                        $cpjBuffer .= 'PCC='.$cpj->printerCommandsCopies.Utils::SER_SEP;
                    }
                    if($cpj->formatHexValues){
                        $cpjBuffer .= Utils::formatHexValues ($cpj->printerCommands);
                    } else {
                        $cpjBuffer .= $cpj->printerCommands;
                    }
                } else if (isset ($cpj->printFile)){
                    $cpjBuffer = $cpj->printFile->serialize();
                } else if (isset ($cpj->printFileGroup)){
                    $cpjBuffer = 'wcpPFG:';
                    $zip = new ZipArchive;
                    $cacheFileName = (Utils::strEndsWith(WebClientPrint::$wcpCacheFolder, '/')?WebClientPrint::$wcpCacheFolder:WebClientPrint::$wcpCacheFolder.'/').'PFG'.uniqid().'.zip';
                    $res = $zip->open($cacheFileName, ZipArchive::CREATE);
                    if ($res === TRUE) {
                        foreach ($cpj->printFileGroup as $printFile) {
                            $file = $printFile->fileName;
                            if($printFile->copies > 1){
                                $pfc = 'PFC='.$printFile->copies;
                                $file = substr($file, 0, strrpos($file, '.')).$pfc.substr($file, strrpos($file, '.'));
                            }
                            if(is_a($printFile, 'PrintFilePDF')) $file .= '.wpdf';
                            if(is_a($printFile, 'PrintFileTXT')) $file .= '.wtxt';
                            if(is_a($printFile, 'PrintFileDOC')) $file .= '.wdoc';
                            if(is_a($printFile, 'PrintFileXLS')) $file .= '.wxls';
                            if(is_a($printFile, 'PrintFileTIF')) $file .= '.wtif';
                    
                            $zip->addFromString($file, $printFile->getFileContent());
                        }
                        $zip->close();
                        $handle = fopen($cacheFileName, 'rb');
                        $cpjBuffer .= fread($handle, filesize($cacheFileName));
                        fclose($handle);
                        unlink($cacheFileName);
                    } else {
                        $cpjBuffer='Creating PrintFileGroup failed. Cannot create zip file.';
                    }
                }

                $arrIdx1 = Utils::intToArray(strlen($cpjBuffer));

                if (!isset($cpj->clientPrinter)){
                    $cpj->clientPrinter = new UserSelectedPrinter();
                }    

                $cpjBuffer .= $cpj->clientPrinter->serialize();
                    
                $cpjBytesCount += strlen($arrIdx1.$cpjBuffer);
 
                $dataPartIndexes .= Utils::intToArray($cpjBytesCount);
 
                $buffer .= $arrIdx1.$cpjBuffer;
            }
                    
            
            $buffer .= Utils::getLicense();

            return $cpjgHeader.$dataPartIndexes.$buffer;    
        
        
        } else {
            
            return NULL;
        }
            
        
    }
}
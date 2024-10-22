<?php 
$imageSrc = $RootPath . '/css/' . $Theme . '/images/printer.png';
$imageTitle = $textActionToPrint;
?>

<img src="<?php echo $imageSrc; ?>" title="<?php echo $imageTitle; ?>" alt="" />
<a href="#" onclick="javascript:jsWebClientPrint.print('useDefaultPrinter=1' + '&printerName=' + encodeURIComponent($('#installedPrinterName').val()) + '&identifier=<?php echo urlencode($identifier); ?>');">
<?= $textActionToPrint; ?>
</a>

<?php 
use Neodynamic\SDK\Web\WebClientPrint;
?>

<script type="text/javascript">
	
	var wcppGetPrintersTimeout_ms = 60000; //60 sec
        var wcppGetPrintersTimeoutStep_ms = 500; //0.5 sec

        function wcpGetPrintersOnSuccess(){
            // Display client installed printers
            if(arguments[0].length > 0){
                var p=arguments[0].split("|");
                var options = '';
                for (var i = 0; i < p.length; i++) {
                    options += '<option>' + p[i] + '</option>';
                }
                $('#installedPrinters').css('visibility','visible');
                $('#installedPrinterName').html(options);
                $('#installedPrinterName').focus();
                $('#loadPrinters').hide();                                                        
            }else{
                alert("No printers are installed in your system.");
            }
        }

        function wcpGetPrintersOnFailure() {
            // Do something if printers cannot be got from the client
            alert("No printers are installed in your system.");
        }
    </script>
    
    <!-- Add Reference to jQuery at Google CDN -->
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.8.2/jquery.min.js" type="text/javascript"></script>

	<?php
    //Get Absolute URL of this page
    $currentAbsoluteURL = (@$_SERVER["HTTPS"] == "on") ? "https://" : "http://";
    $currentAbsoluteURL .= $_SERVER["SERVER_NAME"];
    if($_SERVER["SERVER_PORT"] != "80" && $_SERVER["SERVER_PORT"] != "443")
    {
        $currentAbsoluteURL .= ":".$_SERVER["SERVER_PORT"];
    } 
    $currentAbsoluteURL .= $_SERVER["REQUEST_URI"];
    
    //WebClientPrinController.php is at the same page level as WebClientPrint.php
    $webClientPrintControllerAbsoluteURL = substr($currentAbsoluteURL, 0, strrpos($currentAbsoluteURL, '/')).'/includes/WebClientPrint/WebClientPrintController.php';
    
    //PrintESCPOSController.php is at the same page level as WebClientPrint.php
    $printESCPOSControllerAbsoluteURL = substr($currentAbsoluteURL, 0, strrpos($currentAbsoluteURL, '/')).'/includes/WebClientPrint/PrintESCPOSController.php';
    
    //Specify the ABSOLUTE URL to the WebClientPrintController.php and to the file that will create the ClientPrintJob object
    echo WebClientPrint::createScript($webClientPrintControllerAbsoluteURL, $printESCPOSControllerAbsoluteURL, session_id());
    ?>
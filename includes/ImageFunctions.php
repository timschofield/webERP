<?php

function GetImageLink($ImageFile, $Item, $Width, $Height, $AltText, $Class = "") {

    if (extension_loaded('gd') 
		and function_exists('gd_info') 
		and file_exists($ImageFile) 
		and isset($Item) 
		and !empty($Item)) {
        if ($_SESSION['ShowStockidOnImages'] == '0'){
            $ImageLink = '<img src="GetStockImage.php?automake=1' . 
                                                    '&textcolor=FFFFFF' .
                                                    '&bgcolor=CCCCCC' .
                                                    '&amp;width=' . $Width . 
                                                    '&amp;height=' . $Height .
                                                    '&$Item=' . urlencode($Item) .
                                                    '" alt="" />';
        } else {
            $ImageLink = '<img src="GetStockImage.php?automake=1' . 
                                                    '&textcolor=FFFFFF' .
                                                    '&bgcolor=CCCCCC' .
                                                    '&amp;text='. $Item .
                                                    '&amp;width=' . $Width . 
                                                    '&amp;height=' . $Height .
                                                    '&$Item=' . urlencode($Item) .
                                                    '" alt="" />';
}
    } else if (file_exists($ImageFile)) {
        $ImageLink = '<img class="StockImage" src="' . $ImageFile . '" />';
    } else {
        $ImageLink = _('No Image');
    }
   return $ImageLink;
}

?>
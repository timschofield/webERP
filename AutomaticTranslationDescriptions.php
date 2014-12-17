<?php


// Based on http://hayageek.com/google-translate-api-tutorial/

include ('includes/session.inc');
$Title = _('Automatic Translation - Descriptions');
include ('includes/header.inc');

$api_key = 'AIzaSyBwTQG8F0cue7FY5sP34uZYXZEH9KVBesU';
$source="en";
$target="id";

// Select items and classify them
$SQL = "SELECT stockid,
				description
		FROM stockmaster
		WHERE stockmaster.discontinued = 0
		ORDER BY stockmaster.stockid
		LIMIT 0, 5";
$result = DB_query($SQL);

if (DB_num_rows($result) != 0){
	echo '<p class="page_title_text" align="center"><strong>' . _('Description Automatic Translation') . '</strong></p>';
	echo '<div>';
	echo '<table class="selection">';
	$TableHeader = '<tr>
						<th>' . _('#') . '</th>
						<th>' . _('Code') . '</th>
						<th>' . _('Description') . '</th>
						<th>' . _('To') . '</th>
						<th>' . _('Translated') . '</th>
					</tr>';
	echo $TableHeader;
	$k = 0; //row colour counter
	$i = 0;
	while ($myrow = DB_fetch_array($result)) {
		if ($k==1){
			echo '<tr class="EvenTableRows">';
			$k=0;
		} else {
			echo '<tr class="OddTableRows">';
			$k++;
		}
		$i++;
		
		$obj = translate_google_api($api_key,$myrow['description'],$target,$source);
		if($obj != null)
		{
			if(isset($obj['error']))
			{
				$TranslatedText = "ERROR: " . $obj['error']['message'];
			}
			else
			{
				$TranslatedText = $obj['data']['translations'][0]['translatedText'];
//				if(isset($obj['data']['translations'][0]['detectedSourceLanguage'])) //this is set if only source is not available.
//					echo "Detecte Source Languge : ".$obj['data']['translations'][0]['detectedSourceLanguage']."n";     
			}
		}
		else{
			$TranslatedText = "UNKNOW ERROR";		
		}
		
		printf('<td class="number">%s</td>
				<td>%s</td>
				<td>%s</td>
				<td>%s</td>
				<td>%s</td>
				</tr>', 
				$i, 
				$myrow['stockid'], 
				$myrow['description'],
				$target,
				$TranslatedText
				);
	}
	echo '</table>
			</div>';
	prnMsg("Number of translated descriptions via Google API: " . locale_number_format($i));
}

include ('includes/footer.inc');

function translate_google_api($api_key,$text,$target,$source=false)
{
    $url = 'https://www.googleapis.com/language/translate/v2?key=' . $api_key . '&q=' . rawurlencode($text);
    $url .= '&target='.$target;
    if($source)
     $url .= '&source='.$source;
 
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $response = curl_exec($ch);                 
    curl_close($ch);
 
    $obj =json_decode($response,true); //true converts stdClass to associative array.
    return $obj;
}  

?>
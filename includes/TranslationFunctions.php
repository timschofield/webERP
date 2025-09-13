<?php

// Detailed info on Google Translator API https://cloud.google.com/translate/
// This webERP-style code is based on http://hayageek.com/google-translate-api-tutorial/

function translate_via_google_translator($Text,$Target,$Source=false){
	$url = 'https://www.googleapis.com/language/translate/v2?key=' . $_SESSION['GoogleTranslatorAPIKey'] . '&q=' . rawurlencode($Text) . '&target=' . $Target;
	if($Source){
		$url .= '&source=' . $Source;
	}
	$ch = curl_init($url);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	$response = curl_exec($ch);
	curl_close($ch);
	$obj =json_decode($response,true); //true converts stdClass to associative array.
	if($obj != null){
		if(isset($obj['error'])){
			$TranslatedText = "ERROR: " . $obj['error']['message'];
		}
		else{
			$TranslatedText = $obj['data']['translations'][0]['translatedText'];
	//				if(isset($obj['data']['translations'][0]['detectedSourceLanguage'])) //this is set if only source is not available.
	//					echo "Detecte Source Languge : ".$obj['data']['translations'][0]['detectedSourceLanguage']."n";
		}
	}
	else{
		$TranslatedText = "UNKNOW ERROR";
	}
	return $TranslatedText;
}

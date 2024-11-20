<?php

// Detailed info on Google Translator API https://cloud.google.com/translate/
// This webERP-style code is based on http://hayageek.com/google-translate-api-tutorial/

function translate_via_google_translator($text,$target,$source=false){
	$url = 'https://www.googleapis.com/language/translate/v2?key=' . $_SESSION['GoogleTranslatorAPIKey'] . '&q=' . rawurlencode($text) . '&target=' . $target;
	if($source){
		$url .= '&source=' . $source;
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
	$TranslatedText = KLTranslationFixes($TranslatedText, $target);
	return $TranslatedText;
}  

function KLTranslationFixes($TranslatedText, $target){
	if($target == 'id'){
		$TranslatedText = str_replace("Finishing dipoles tinggi", "Finishing dipoles", $TranslatedText);

		// Anting Anting 
		if (strpos($TranslatedText," anting-anting perak") !== false) {
			$TranslatedText = str_replace(" anting-anting perak", "", $TranslatedText);
			$TranslatedText = "Anting perak ". $TranslatedText; 
		}
		if (strpos($TranslatedText," anting perak") !== false) {
			$TranslatedText = str_replace(" anting perak", "", $TranslatedText);
			$TranslatedText = "Anting perak ". $TranslatedText; 
		}
		if (strpos($TranslatedText," perak anting") !== false) {
			$TranslatedText = str_replace(" perak anting", "", $TranslatedText);
			$TranslatedText = "Anting perak ". $TranslatedText; 
		}
		if (strpos($TranslatedText," anting kawat") !== false) {
			$TranslatedText = str_replace(" anting kawat", "", $TranslatedText);
			$TranslatedText = "Anting kawat ". $TranslatedText; 
		}
		if (strpos($TranslatedText," anting-anting") !== false) {
			$TranslatedText = str_replace(" anting-anting", "", $TranslatedText);
			$TranslatedText = "Anting-anting ". $TranslatedText; 
		}

		// cincin
		if (strpos($TranslatedText," perak cincin") !== false) {
			$TranslatedText = str_replace(" perak cincin", "", $TranslatedText);
			$TranslatedText = "Cincin perak ". $TranslatedText; 
		}
		if (strpos($TranslatedText," cincin perak") !== false) {
			$TranslatedText = str_replace(" cincin perak", "", $TranslatedText);
			$TranslatedText = "Cincin perak ". $TranslatedText; 
		}

		// kalung
		if (strpos($TranslatedText," perak kalung") !== false) {
			$TranslatedText = str_replace(" perak kalung", "", $TranslatedText);
			$TranslatedText = "Kalung perak ". $TranslatedText; 
		}
		if (strpos($TranslatedText," kawat kalung") !== false) {
			$TranslatedText = str_replace(" kawat kalung", "", $TranslatedText);
			$TranslatedText = "Kalung kawat ". $TranslatedText; 
		}

		// gelang
		if (strpos($TranslatedText," perak gelang") !== false) {
			$TranslatedText = str_replace(" perak gelang", "", $TranslatedText);
			$TranslatedText = "Gelang perak ". $TranslatedText; 
		}
		if (strpos($TranslatedText," gelang perak") !== false) {
			$TranslatedText = str_replace(" gelang perak", "", $TranslatedText);
			$TranslatedText = "Gelang perak ". $TranslatedText; 
		}
		if (strpos($TranslatedText," gelang") !== false) {
			$TranslatedText = str_replace(" gelang", "", $TranslatedText);
			$TranslatedText = "Gelang ". $TranslatedText; 
		}
		
		// liontin
		if (strpos($TranslatedText," liontin perak") !== false) {
			$TranslatedText = str_replace(" liontin perak", "", $TranslatedText);
			$TranslatedText = "Liontin perak ". $TranslatedText; 
		}
		
	}
	return $TranslatedText;
}


?>
<?php

/**
 * @param $CompanyName
 * @param $Path_To_Root
 * @param $CompanyDir
 * @return bool true on success
 */
function CreateCompanyLogo($CompanyName, $Path_To_Root, $CompanyDir) {
	if (extension_loaded('gd')) {
		// generate an image, based on company name

		$Font = 3;

		$im = imagecreatefrompng($Path_To_Root . '/images/logo_background.png');
		imagealphablending($im, false);

		//$BackgroundColour = imagecolorallocate($im, 119, 119, 119); // #777777, same as default color theme
		$TextColour = imagecolorallocate($im, 255, 255, 255);

		$fw = imagefontwidth($Font);
		$fh = imagefontheight($Font);
		$TextWidth = $fw * mb_strlen($CompanyName);
		$px = (imagesx($im) - $TextWidth) / 2;
		$py = (imagesy($im) - ($fh)) / 2;
		//imagefill($im, 0, 0, $BackgroundColour);
		imagestring($im, $Font, (int)$px, (int)$py, $CompanyName, $TextColour);

		imagesavealpha($im, true);

		$Result = true;
		if (!imagepng($im, $CompanyDir . '/logo.png')) {
			$Result = copy($Path_To_Root . '/images/default_logo.jpg', $CompanyDir . '/logo.jpg');
		}

	} else {
		$Result = copy($Path_To_Root . '/images/default_logo.jpg', $CompanyDir . '/logo.jpg');
	}

	if ($Result) {
		echo '<div class="success">' . __('A default company logo has been generated') . '</div>';
	} else {
		echo '<div class="warning">' . __('Failed generating default company logo.') . '</div>';
	}
	flush();

	return $Result;
}

/**
 * @param $DatabaseName
 * @param $Path_To_Root
 * @return bool true on success
 */
function SaveUploadedCompanyLogo($DatabaseName, $Path_To_Root)
{
	/* Upload logo file */
	$UploadOK = 1;

	$TargetDir = $Path_To_Root . '/companies/' . $DatabaseName . '/';
	$TargetFile = $TargetDir . basename($_FILES["LogoFile"]["name"]);
	$ImageFileType = strtolower(pathinfo($TargetFile, PATHINFO_EXTENSION));

	// Check if image file is an actual image or fake image
	if (isset($_POST["install"])) {
		$check = getimagesize($_FILES["LogoFile"]["tmp_name"]);
		if ($check !== false) {
			$UploadOK = 1;
		} else {
			echo '<div class="warning">' . __('Logo file is not an image.') . '</div>';
			$UploadOK = 0;
		}
	}

	// Check if file already exists
	if (file_exists($TargetFile)) {
		echo '<div class="warning">' . __('Sorry, logo file already exists.') . '</div>';
		$UploadOK = 0;
	}

	// Check file size
	if ($_FILES["LogoFile"]["size"] > 500000) {
		echo '<div class="warning">' . __('Sorry, your logo file is too large.') . '</div>';
		$UploadOK = 0;
	}

	// Allow certain file formats
	if ($ImageFileType != "jpg" && $ImageFileType != "png" && $ImageFileType != "jpeg" && $ImageFileType != "gif" ) {
		echo '<div class="warning">' . __('Sorry, only JPG, JPEG, PNG & GIF logo files are allowed.') . '</div>';
		$UploadOK = 0;
	}

	// Check if $UploadOK is set to 0 by an error
	if ($UploadOK == 0) {
		echo '<div class="warning">' . __('Sorry, your logo file was not uploaded.') . '</div>';
	} else {
		// if everything is ok, try to upload file
		if (move_uploaded_file($_FILES["LogoFile"]["tmp_name"], $TargetFile)) {
			echo '<div class="success">' . __('Your logo has been successfully uploaded') . '</div>';
		} else {
			echo '<div class="warning">' . __('Your logo could not be uploaded. You must copy this to your companies directory later.') . '</div>';
		}
	}
	flush();

	return (bool)$UploadOK;
}

function HighestFileName($Path_To_Root) {
	$files = glob($Path_To_Root . '/sql/updates/*.php');
	natsort($files);
	$LastFile = array_pop($files);
	return $LastFile ? basename($LastFile, ".php") : '';
}

function CryptPass($Password) {
	$Hash = password_hash($Password, PASSWORD_DEFAULT);
	return $Hash;
}


/// @todo in case this is used outside the installer, we could move to its own file, TimezonesArray.php
function GetTimezones() {
	return array(
		'Africa/Abidjan',
		'Africa/Accra',
		'Africa/Addis_Ababa',
		'Africa/Algiers',
		'Africa/Asmara',
		'Africa/Asmera',
		'Africa/Bamako',
		'Africa/Bangui',
		'Africa/Banjul',
		'Africa/Bissau',
		'Africa/Blantyre',
		'Africa/Brazzaville',
		'Africa/Bujumbura',
		'Africa/Cairo',
		'Africa/Casablanca',
		'Africa/Ceuta',
		'Africa/Conakry',
		'Africa/Dakar',
		'Africa/Dar_es_Salaam',
		'Africa/Djibouti',
		'Africa/Douala',
		'Africa/El_Aaiun',
		'Africa/Freetown',
		'Africa/Gaborone',
		'Africa/Harare',
		'Africa/Johannesburg',
		'Africa/Kampala',
		'Africa/Khartoum',
		'Africa/Kigali',
		'Africa/Kinshasa',
		'Africa/Lagos',
		'Africa/Libreville',
		'Africa/Lome',
		'Africa/Luanda',
		'Africa/Lubumbashi',
		'Africa/Lusaka',
		'Africa/Malabo',
		'Africa/Maputo',
		'Africa/Maseru',
		'Africa/Mbabane',
		'Africa/Mogadishu',
		'Africa/Monrovia',
		'Africa/Nairobi',
		'Africa/Ndjamena',
		'Africa/Niamey',
		'Africa/Nouakchott',
		'Africa/Ouagadougou',
		'Africa/Porto-Novo',
		'Africa/Sao_Tome',
		'Africa/Timbuktu',
		'Africa/Tripoli',
		'Africa/Tunis',
		'Africa/Windhoek',
		'America/Adak',
		'America/Anchorage',
		'America/Anguilla',
		'America/Antigua',
		'America/Araguaina',
		'America/Argentina/Buenos_Aires',
		'America/Argentina/Catamarca',
		'America/Argentina/ComodRivadavia',
		'America/Argentina/Cordoba',
		'America/Argentina/Jujuy',
		'America/Argentina/La_Rioja',
		'America/Argentina/Mendoza',
		'America/Argentina/Rio_Gallegos',
		'America/Argentina/Salta',
		'America/Argentina/San_Juan',
		'America/Argentina/San_Luis',
		'America/Argentina/Tucuman',
		'America/Argentina/Ushuaia',
		'America/Aruba',
		'America/Asuncion',
		'America/Atikokan',
		'America/Atka',
		'America/Bahia',
		'America/Barbados',
		'America/Belem',
		'America/Belize',
		'America/Blanc-Sablon',
		'America/Boa_Vista',
		'America/Bogota',
		'America/Boise',
		'America/Buenos_Aires',
		'America/Cambridge_Bay',
		'America/Campo_Grande',
		'America/Cancun',
		'America/Caracas',
		'America/Catamarca',
		'America/Cayenne',
		'America/Cayman',
		'America/Chicago',
		'America/Chihuahua',
		'America/Coral_Harbour',
		'America/Cordoba',
		'America/Costa_Rica',
		'America/Cuiaba',
		'America/Curacao',
		'America/Danmarkshavn',
		'America/Dawson',
		'America/Dawson_Creek',
		'America/Denver',
		'America/Detroit',
		'America/Dominica',
		'America/Edmonton',
		'America/Eirunepe',
		'America/El_Salvador',
		'America/Ensenada',
		'America/Fort_Wayne',
		'America/Fortaleza',
		'America/Glace_Bay',
		'America/Godthab',
		'America/Goose_Bay',
		'America/Grand_Turk',
		'America/Grenada',
		'America/Guadeloupe',
		'America/Guatemala',
		'America/Guayaquil',
		'America/Guyana',
		'America/Halifax',
		'America/Havana',
		'America/Hermosillo',
		'America/Indiana/Indianapolis',
		'America/Indiana/Knox',
		'America/Indiana/Marengo',
		'America/Indiana/Petersburg',
		'America/Indiana/Tell_City',
		'America/Indiana/Vevay',
		'America/Indiana/Vincennes',
		'America/Indiana/Winamac',
		'America/Indianapolis',
		'America/Inuvik',
		'America/Iqaluit',
		'America/Jamaica',
		'America/Jujuy',
		'America/Juneau',
		'America/Kentucky/Louisville',
		'America/Kentucky/Monticello',
		'America/Knox_IN',
		'America/La_Paz',
		'America/Lima',
		'America/Los_Angeles',
		'America/Louisville',
		'America/Maceio',
		'America/Managua',
		'America/Manaus',
		'America/Marigot',
		'America/Martinique',
		'America/Mazatlan',
		'America/Mendoza',
		'America/Menominee',
		'America/Merida',
		'America/Mexico_City',
		'America/Miquelon',
		'America/Moncton',
		'America/Monterrey',
		'America/Montevideo',
		'America/Montreal',
		'America/Montserrat',
		'America/Nassau',
		'America/New_York',
		'America/Nipigon',
		'America/Nome',
		'America/Noronha',
		'America/North_Dakota/Center',
		'America/North_Dakota/New_Salem',
		'America/Panama',
		'America/Pangnirtung',
		'America/Paramaribo',
		'America/Phoenix',
		'America/Port-au-Prince',
		'America/Port_of_Spain',
		'America/Porto_Acre',
		'America/Porto_Velho',
		'America/Puerto_Rico',
		'America/Rainy_River',
		'America/Rankin_Inlet',
		'America/Recife',
		'America/Regina',
		'America/Resolute',
		'America/Rio_Branco',
		'America/Rosario',
		'America/Santarem',
		'America/Santiago',
		'America/Santo_Domingo',
		'America/Sao_Paulo',
		'America/Scoresbysund',
		'America/Shiprock',
		'America/St_Barthelemy',
		'America/St_Johns',
		'America/St_Kitts',
		'America/St_Lucia',
		'America/St_Thomas',
		'America/St_Vincent',
		'America/Swift_Current',
		'America/Tegucigalpa',
		'America/Thule',
		'America/Thunder_Bay',
		'America/Tijuana',
		'America/Toronto',
		'America/Tortola',
		'America/Vancouver',
		'America/Virgin',
		'America/Whitehorse',
		'America/Winnipeg',
		'America/Yakutat',
		'America/Yellowknife',
		'Asia/Aden',
		'Asia/Almaty',
		'Asia/Amman',
		'Asia/Anadyr',
		'Asia/Aqtau',
		'Asia/Aqtobe',
		'Asia/Ashgabat',
		'Asia/Ashkhabad',
		'Asia/Baghdad',
		'Asia/Bahrain',
		'Asia/Baku',
		'Asia/Bangkok',
		'Asia/Beirut',
		'Asia/Bishkek',
		'Asia/Brunei',
		'Asia/Choibalsan',
		'Asia/Chongqing',
		'Asia/Chungking',
		'Asia/Colombo',
		'Asia/Dacca',
		'Asia/Damascus',
		'Asia/Dhaka',
		'Asia/Dili',
		'Asia/Dubai',
		'Asia/Dushanbe',
		'Asia/Gaza',
		'Asia/Harbin',
		'Asia/Ho_Chi_Minh',
		'Asia/Hong_Kong',
		'Asia/Hovd',
		'Asia/Irkutsk',
		'Asia/Istanbul',
		'Asia/Jakarta',
		'Asia/Jayapura',
		'Asia/Jerusalem',
		'Asia/Kabul',
		'Asia/Kamchatka',
		'Asia/Karachi',
		'Asia/Kashgar',
		'Asia/Kathmandu',
		'Asia/Katmandu',
		'Asia/Kolkata',
		'Asia/Krasnoyarsk',
		'Asia/Kuala_Lumpur',
		'Asia/Kuching',
		'Asia/Kuwait',
		'Asia/Macao',
		'Asia/Macau',
		'Asia/Magadan',
		'Asia/Makassar',
		'Asia/Manila',
		'Asia/Muscat',
		'Asia/Nicosia',
		'Asia/Novosibirsk',
		'Asia/Omsk',
		'Asia/Oral',
		'Asia/Phnom_Penh',
		'Asia/Pontianak',
		'Asia/Pyongyang',
		'Asia/Qatar',
		'Asia/Qyzylorda',
		'Asia/Rangoon',
		'Asia/Riyadh',
		'Asia/Saigon',
		'Asia/Sakhalin',
		'Asia/Samarkand',
		'Asia/Seoul',
		'Asia/Shanghai',
		'Asia/Singapore',
		'Asia/Taipei',
		'Asia/Tashkent',
		'Asia/Tbilisi',
		'Asia/Tehran',
		'Asia/Tel_Aviv',
		'Asia/Thimbu',
		'Asia/Thimphu',
		'Asia/Tokyo',
		'Asia/Ujung_Pandang',
		'Asia/Ulaanbaatar',
		'Asia/Ulan_Bator',
		'Asia/Urumqi',
		'Asia/Vientiane',
		'Asia/Vladivostok',
		'Asia/Yakutsk',
		'Asia/Yekaterinburg',
		'Asia/Yerevan',
		'Atlantic/Azores',
		'Atlantic/Bermuda',
		'Atlantic/Canary',
		'Atlantic/Cape_Verde',
		'Atlantic/Faeroe',
		'Atlantic/Faroe',
		'Atlantic/Jan_Mayen',
		'Atlantic/Madeira',
		'Atlantic/Reykjavik',
		'Atlantic/South_Georgia',
		'Atlantic/St_Helena',
		'Atlantic/Stanley',
		'Australia/ACT',
		'Australia/Adelaide',
		'Australia/Brisbane',
		'Australia/Broken_Hill',
		'Australia/Canberra',
		'Australia/Currie',
		'Australia/Darwin',
		'Australia/Eucla',
		'Australia/Hobart',
		'Australia/LHI',
		'Australia/Lindeman',
		'Australia/Lord_Howe',
		'Australia/Melbourne',
		'Australia/North',
		'Australia/NSW',
		'Australia/Perth',
		'Australia/Queensland',
		'Australia/South',
		'Australia/Sydney',
		'Australia/Tasmania',
		'Australia/Victoria',
		'Australia/West',
		'Australia/Yancowinna',
		'Europe/Amsterdam',
		'Europe/Andorra',
		'Europe/Athens',
		'Europe/Belfast',
		'Europe/Belgrade',
		'Europe/Berlin',
		'Europe/Bratislava',
		'Europe/Brussels',
		'Europe/Bucharest',
		'Europe/Budapest',
		'Europe/Chisinau',
		'Europe/Copenhagen',
		'Europe/Dublin',
		'Europe/Gibraltar',
		'Europe/Guernsey',
		'Europe/Helsinki',
		'Europe/Isle_of_Man',
		'Europe/Istanbul',
		'Europe/Jersey',
		'Europe/Kaliningrad',
		'Europe/Kiev',
		'Europe/Lisbon',
		'Europe/Ljubljana',
		'Europe/London',
		'Europe/Luxembourg',
		'Europe/Madrid',
		'Europe/Malta',
		'Europe/Mariehamn',
		'Europe/Minsk',
		'Europe/Monaco',
		'Europe/Moscow',
		'Europe/Nicosia',
		'Europe/Oslo',
		'Europe/Paris',
		'Europe/Podgorica',
		'Europe/Prague',
		'Europe/Riga',
		'Europe/Rome',
		'Europe/Samara',
		'Europe/San_Marino',
		'Europe/Sarajevo',
		'Europe/Simferopol',
		'Europe/Skopje',
		'Europe/Sofia',
		'Europe/Stockholm',
		'Europe/Tallinn',
		'Europe/Tirane',
		'Europe/Tiraspol',
		'Europe/Uzhgorod',
		'Europe/Vaduz',
		'Europe/Vatican',
		'Europe/Vienna',
		'Europe/Vilnius',
		'Europe/Volgograd',
		'Europe/Warsaw',
		'Europe/Zagreb',
		'Europe/Zaporozhye',
		'Europe/Zurich',
		'Indian/Antananarivo',
		'Indian/Chagos',
		'Indian/Christmas',
		'Indian/Cocos',
		'Indian/Comoro',
		'Indian/Kerguelen',
		'Indian/Mahe',
		'Indian/Maldives',
		'Indian/Mauritius',
		'Indian/Mayotte',
		'Indian/Reunion',
		'Pacific/Apia',
		'Pacific/Auckland',
		'Pacific/Chatham',
		'Pacific/Easter',
		'Pacific/Efate',
		'Pacific/Enderbury',
		'Pacific/Fakaofo',
		'Pacific/Fiji',
		'Pacific/Funafuti',
		'Pacific/Galapagos',
		'Pacific/Gambier',
		'Pacific/Guadalcanal',
		'Pacific/Guam',
		'Pacific/Honolulu',
		'Pacific/Johnston',
		'Pacific/Kiritimati',
		'Pacific/Kosrae',
		'Pacific/Kwajalein',
		'Pacific/Majuro',
		'Pacific/Marquesas',
		'Pacific/Midway',
		'Pacific/Nauru',
		'Pacific/Niue',
		'Pacific/Norfolk',
		'Pacific/Noumea',
		'Pacific/Pago_Pago',
		'Pacific/Palau',
		'Pacific/Pitcairn',
		'Pacific/Ponape',
		'Pacific/Port_Moresby',
		'Pacific/Rarotonga',
		'Pacific/Saipan',
		'Pacific/Samoa',
		'Pacific/Tahiti',
		'Pacific/Tarawa',
		'Pacific/Tongatapu',
		'Pacific/Truk',
		'Pacific/Wake',
		'Pacific/Wallis',
		'Pacific/Yap',
		'Etc/UTC'
	);
}

function DetectServerTimezone() {
	 // Try php.ini setting first
	 $tz = ini_get('date.timezone');
	 if (!empty($tz) && $tz !== 'UTC') {
		 return $tz;
	 }

	 // Try /etc/timezone (Debian/Ubuntu)
	 if (file_exists('/etc/timezone')) {
		 $tz = trim(file_get_contents('/etc/timezone'));
		 if (!empty($tz)) return $tz;
	 }

	 // Try /etc/localtime symlink (RHEL/CentOS)
	 if (is_link('/etc/localtime')) {
		 $link = readlink('/etc/localtime');
		 if (preg_match('#zoneinfo/(.+)$#', $link, $m)) {
			 return $m[1];
		 }
	 }

	 // Fallback
	 return date_default_timezone_get();
}

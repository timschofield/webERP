<?php

/**
 * Validate password quality rules.
 * Returns an empty string when valid, otherwise a translated error message.
 *
 * @param string $Password
 * @param string $UserID
 * @return string
 */
function ValidatePasswordQuality($Password, $UserID) {

	$PasswordMinLenght = isset($_SESSION['PasswordMinLenght']) ? (int)$_SESSION['PasswordMinLenght'] : 5;

	if (mb_strlen($Password) < $PasswordMinLenght) {
		return __('The password entered must be at least') . ' ' . $PasswordMinLenght . ' ' . __('characters long');
	}

	if (mb_strstr($Password, $UserID) != false) {
		return __('The password cannot contain the user id');
	}

	return '';
}

<?php
/**
File: htmlMimeMail.php
Dersion: 3.0.0
Date: 2015-09-17

This file is part of the htmlMimeMail3 package
Version control: https://github.com/smxi/php-html-mime-mail

Because the 2005 release of this class contained some features that
broke some functionality we required, and since both the 1.4 and
5.0 versions required significant updating to work with modern PHP,
I decided to fork version 1.4, but using the version 5 of smtp.php
and RFC822.php libraries.

To maintain full compatibility with htmlMimeMail note that there are
some key differences between the original and version 5, particularly
with attachment syntax. So if you want version 5, this is the wrong
version for you. This is a drop in replacemnt for the original class,
and should require no changes to your on page code to run.

htmlMimeMail3 is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation; either version 3 of the License, or
(at your option) any later version.

htmlMimeMail3 is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with htmlMimeMail3; if not, write to the Free Software
Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA

Original Author:
© Copyright 2001, 2002 Richard Heyes
Project: HTML Mime mail class
Date: 2002/07/24 13:14:10 $
Version: 1.4

Update/Fork Changes:
Version: 3.x.x
© Copyright 2015 Harald Hope
**/

require_once(dirname(__FILE__) . '/mimePart.php');
// NOTE: if you will not use smtp email send, comment these out:
require_once(dirname(__FILE__) . '/smtp.php');
require_once(dirname(__FILE__) . '/RFC822.php');

class htmlMimeMail
{
	/**
	* The html part of the message
	* @var string
	*/
	private $html;
	/**
	* The text part of the message(only used in TEXT only messages)
	* @var string
	*/
	private $Text;
	/**
	* The main body of the message after building
	* @var string
	*/
	private $output;
	/**
	* The alternative text to the HTML part (only used in HTML messages)
	* @var string
	*/
	private $html_text;
	/**
	* An array of embedded images/objects
	* @var array
	*/
	private $html_images;
	/**
	* An array of recognised image types for the findHtmlImages() method
	* @var array
	*/
	private $image_types;
	/**
	* Parameters that affect the build process
	* @var array
	*/
	private $build_params;
	/**
	* Array of attachments
	* @var array
	*/
	private $attachments;
	/**
	* The main message headers
	* @var array
	*/
	private $Headers;
	/**
	* Whether the message has been built or not
	* @var boolean
	*/
	private $is_built;
	/**
	* The return path address. If not set the From:
	* address is used instead
	* @var string
	*/
	private $Return_path;
	/**
	* Array of information needed for smtp sending
	* @var array
	*/
	private $smtp_params;
	/**
	* Constructor function. Sets the headers
	* if supplied.
	*/
	public function __construct()
	## adjust
	// public function htmlMimeMail()
	{
		/**
		* Initialise some variables.
		*/
		$this->html_images = array();
		$this->headers     = array();
		$this->is_built    = false;

		/**
		* If you want the auto load functionality
		* to find other image/file types, add the
		* extension and content type here.
		*/
		$this->image_types = array(
		'gif'	=> 'image/gif',
		'jpg'	=> 'image/jpeg',
		'jpeg'	=> 'image/jpeg',
		'jpe'	=> 'image/jpeg',
		'bmp'	=> 'image/bmp',
		'png'	=> 'image/png',
		'tif'	=> 'image/tiff',
		'tiff'	=> 'image/tiff',
		'swf'	=> 'application/x-shockwave-flash'
		);

		/**
		* Set these up
		*/
		$this->build_params['html_encoding'] = 'quoted-printable';
		$this->build_params['text_encoding'] = '7bit';
		$this->build_params['html_charset']  = 'ISO-8859-1';
		$this->build_params['text_charset']  = 'ISO-8859-1';
		$this->build_params['head_charset']  = 'ISO-8859-1';
		$this->build_params['text_wrap']     = 998;

		/**
		* Defaults for smtp sending
		*/
		if (!empty($_SERVER['HTTP_HOST'])) {
			$helo = $_SERVER['HTTP_HOST'];
		}
		elseif (!empty($_SERVER['SERVER_NAME'])) {
			$helo = $_SERVER['SERVER_NAME'];
		}
		else {
			$helo = 'localhost';
		}

		$this->smtp_params['host'] = 'localhost';
		$this->smtp_params['port'] = 25;
		$this->smtp_params['helo'] = $helo;
		$this->smtp_params['auth'] = false;
		$this->smtp_params['user'] = '';
		$this->smtp_params['pass'] = '';

		/**
		* Make sure the MIME version header is first.
		*/
		$this->headers['MIME-Version'] = '1.0';
	}
	/**
	* This function will read a file in
	* from a supplied filename and return
	* it. This can then be given as the first
	* argument of the the functions
	* add_html_image() or add_attachment().
	*/
	public function getFile($FileName)
	{
		$Return = '';
		if ($fp = fopen($FileName, 'rb')) {
			while (!feof($fp)) {
				$Return .= fread($fp, 1024);
			}
			fclose($fp);
			return $Return;
		}
		else {
			## adjust
			$Return = false;
			return $Return;
		}
	}
	/**
	* Accessor to set the CRLF style
	*/
	public function setCrlf($crlf = "\n")
	{
		if (!defined('CRLF')) {
			define('CRLF', $crlf);
		}
		if (!defined('MAIL_MIMEPART_CRLF')) {
			define('MAIL_MIMEPART_CRLF', $crlf);
		}
	}
	/**
	* Accessor to set the SMTP parameters
	*/
	public function setSMTPParams($Host = null, $port = null, $helo = null, $auth = null, $user = null, $pass = null)
	{
		if (!is_null($Host)) {
			$this->smtp_params['host'] = $Host;
		}
		if (!is_null($port)){
			$this->smtp_params['port'] = $port;
		}
		if (!is_null($helo)){
			$this->smtp_params['helo'] = $helo;
		}
		if (!is_null($auth)){
			$this->smtp_params['auth'] = $auth;
		}
		if (!is_null($user)){
			$this->smtp_params['user'] = $user;
		}
		if (!is_null($pass)){
			$this->smtp_params['pass'] = $pass;
		}
	}
	/**
	* Accessor function to set the text encoding
	*/
	public function setTextEncoding($encoding = '7bit')
	{
		$this->build_params['text_encoding'] = $encoding;
	}
	/**
	* Accessor function to set the HTML encoding
	*/
	public function setHtmlEncoding($encoding = 'quoted-printable')
	{
		$this->build_params['html_encoding'] = $encoding;
	}
	/**
	* Accessor function to set the text charset
	*/
	public function setTextCharset($charset = 'ISO-8859-1')
	{
		$this->build_params['text_charset'] = $charset;
	}
	/**
	* Accessor function to set the HTML charset
	*/
	public function setHtmlCharset($charset = 'ISO-8859-1')
	{
		$this->build_params['html_charset'] = $charset;
	}
	/**
	* Accessor function to set the header encoding charset
	*/
	public function setHeadCharset($charset = 'ISO-8859-1')
	{
		$this->build_params['head_charset'] = $charset;
	}
	/**
	* Accessor function to set the text wrap count
	*/
	public function setTextWrap($count = 998)
	{
		$this->build_params['text_wrap'] = $count;
	}
	/**
	* Accessor to set a header
	*/
	public function setHeader($name, $Value)
	{
		$this->headers[$name] = $Value;
	}
	/**
	* Accessor to add a Subject: header
	*/
	public function setSubject($subject)
	{
		$this->headers['Subject'] = $subject;
	}
	/**
	* Accessor to add a From: header
	*/
	public function setFrom($from)
	{
		$this->headers['From'] = $from;
	}
	/**
	* Accessor to set the return path
	*/
	public function setReturnPath($Return_path)
	{
		$this->return_path = $Return_path;
	}
	/**
	* Accessor to add a Cc: header
	*/
	public function setCc($cc)
	{
		$this->headers['Cc'] = $cc;
	}
	/**
	* Accessor to add a Bcc: header
	*/
	public function setBcc($bcc)
	{
		$this->headers['Bcc'] = $bcc;
	}
	/**
	* Adds plain text. Use this function
	* when NOT sending html email
	*/
	public function setText($Text = '')
	{
		$this->text = $Text;
	}
	/**
	* Adds a html part to the mail.
	* Also replaces image names with
	* content-id's.
	*/
	public function setHtml($html, $Text = null, $images_dir = null)
	{
		$this->html      = $html;
		$this->html_text = $Text;
		if (isset($images_dir)) {
			$this->_findHtmlImages($images_dir);
		}
	}
	/**
	* Function for extracting images from
	* html source. This function will look
	* through the html code supplied by add_html()
	* and find any file that ends in one of the
	* extensions defined in $obj->image_types.
	* If the file exists it will read it in and
	* embed it, (not an attachment).
	*
	* @author Dan Allen
	*/
	private function _findHtmlImages($images_dir)
	{
		// Build the list of image extensions
		# while (list($key,) = each($this->image_types)) { # 20241023-depreciated
        foreach($this->image_types as $key => $Value){
			$extensions[] = $key;
		}
		preg_match_all('/(?:"|\')([^"\']+\.('.implode('|', $extensions).'))(?:"|\')/Ui', $this->html, $images);
		for ($i=0; $i<count($images[1]); $i++) {
			if (file_exists($images_dir . $images[1][$i])) {
				$html_images[] = $images[1][$i];
				$this->html = str_replace($images[1][$i], basename($images[1][$i]), $this->html);
			}
		}
		if (!empty($html_images)) {
			// If duplicate images are embedded, they may show up as attachments, so remove them.
			$html_images = array_unique($html_images);
			sort($html_images);
			for ($i=0; $i<count($html_images); $i++) {
				if ($image = $this->getFile($images_dir.$html_images[$i])) {
					$ext = substr($html_images[$i], strrpos($html_images[$i], '.') + 1);
					$Content_type = $this->image_types[strtolower($ext)];
					$this->addHtmlImage($image, basename($html_images[$i]), $Content_type);
				}
			}
		}
	}
	/**
	* Adds an image to the list of embedded
	* images.
	*/
	public function addHtmlImage($file, $name = '', $c_type='application/octet-stream')
	{
		$this->html_images[] = array(
		'body'   => $file,
		'name'   => $name,
		'c_type' => $c_type,
		'cid'    => md5(uniqid(time()))
	);
	}
	/**
	* Adds a file to the list of attachments.
	*/
	public function addAttachment($file, $name = '', $c_type='application/octet-stream', $encoding = 'base64')
	{
		$this->attachments[] = array(
		'body'		=> $file,
		'name'		=> $name,
		'c_type'	=> $c_type,
		'encoding'	=> $encoding
		);
	}
	/**
	* Adds a text subpart to a mime_part object
	*/
	private function &_addTextPart(&$obj, $Text)
	{
		$params['content_type'] = 'text/plain';
		$params['encoding']     = $this->build_params['text_encoding'];
		$params['charset']      = $this->build_params['text_charset'];
		if (is_object($obj)) {
			## adjust
			// return $obj->addSubpart($Text, $params);
			$Return = $obj->addSubpart($Text, $params);
			return $Return;
		}
		else {
			## adjust
			// return new Mail_mimePart($Text, $params);
			$Return = new Mail_mimePart($Text, $params);
			return $Return;
		}
	}
	/**
	* Adds a html subpart to a mime_part object
	*/
	private function &_addHtmlPart(&$obj)
	{
		$params['content_type'] = 'text/html';
		$params['encoding']     = $this->build_params['html_encoding'];
		$params['charset']      = $this->build_params['html_charset'];
		## adjust
		if (is_object($obj)) {
			// return $obj->addSubpart($this->html, $params);
			$Return = $obj->addSubpart($this->html, $params);
			return $Return;
		}
		else {
			// return new Mail_mimePart($this->html, $params);
			$Return = new Mail_mimePart($this->html, $params);
			return $Return;
		}
	}
	/**
	* Starts a message with a mixed part
	*/
	private function &_addMixedPart()
	{
		$params['content_type'] = 'multipart/mixed';
		## adjust
		// return new Mail_mimePart('', $params);
		$Return = new Mail_mimePart('', $params);
		return $Return;
	}
	/**
	* Adds an alternative part to a mime_part object
	*/
	private function &_addAlternativePart(&$obj)
	{
		$params['content_type'] = 'multipart/alternative';
		if (is_object($obj)) {
			## adjust
			// return $obj->addSubpart('', $params);
			$Return = $obj->addSubpart('', $params);
			return $Return;
		}
		else {
			## adjust
			// return new Mail_mimePart('', $params);
			$Return = new Mail_mimePart('', $params);
			return $Return;
		}
	}
	/**
	* Adds a html subpart to a mime_part object
	*/
	private function &_addRelatedPart(&$obj)
	{
		$params['content_type'] = 'multipart/related';
		if (is_object($obj)) {
			## adjust
			// return $obj->addSubpart('', $params);
			$Return = $obj->addSubpart('', $params);
			return $Return;
		}
		else {
			## adjust
			//return new Mail_mimePart('', $params);
			$Return = new Mail_mimePart('', $params);
			return $Return;
		}
	}
	/**
	* Adds an html image subpart to a mime_part object
	*/
	private function &_addHtmlImagePart(&$obj, $Value)
	{
		$params['content_type'] = $Value['c_type'];
		$params['encoding']     = 'base64';
		$params['disposition']  = 'inline';
		$params['dfilename']    = $Value['name'];
		$params['cid']          = $Value['cid'];
		$obj->addSubpart($Value['body'], $params);
	}
	/**
	* Adds an attachment subpart to a mime_part object
	*/
	private function &_addAttachmentPart(&$obj, $Value)
	{
		$params['content_type'] = $Value['c_type'];
		$params['encoding']     = $Value['encoding'];
		$params['disposition']  = 'attachment';
		$params['dfilename']    = $Value['name'];
		$obj->addSubpart($Value['body'], $params);
	}
	/**
	* Builds the multipart message from the
	* list ($this->_parts). $params is an
	* array of parameters that shape the building
	* of the message. Currently supported are:
	*
	* $params['html_encoding'] - The type of encoding to use on html. Valid options are
	*                            "7bit", "quoted-printable" or "base64" (all without quotes).
	*                            7bit is EXPRESSLY NOT RECOMMENDED. Default is quoted-printable
	* $params['text_encoding'] - The type of encoding to use on plain text Valid options are
	*                            "7bit", "quoted-printable" or "base64" (all without quotes).
	*                            Default is 7bit
	* $params['text_wrap']     - The character count at which to wrap 7bit encoded data.
	*                            Default this is 998.
	* $params['html_charset']  - The character set to use for a html section.
	*                            Default is ISO-8859-1
	* $params['text_charset']  - The character set to use for a text section.
	*                          - Default is ISO-8859-1
	* $params['head_charset']  - The character set to use for header encoding should it be needed.
	*                          - Default is ISO-8859-1
	*/
	public function buildMessage($params = array())
	{
		if (!empty($params)) {
			# while (list($key, $Value) = each($params)) { # 20241023-depreciated
            foreach ($params as $key => $Value) {
				$this->build_params[$key] = $Value;
			}
		}
		if (!empty($this->html_images)) {
			foreach ($this->html_images as $Value) {
				$this->html = str_replace($Value['name'], 'cid:'.$Value['cid'], $this->html);
			}
		}
		$null        = null;
		$attachments = !empty($this->attachments) ? true : false;
		$html_images = !empty($this->html_images) ? true : false;
		$html        = !empty($this->html)        ? true : false;
		$Text        = isset($this->text)         ? true : false;
		switch (true) {
			case $Text AND !$attachments:
				$message = &$this->_addTextPart($null, $this->text);
				break;
			case !$Text AND $attachments AND !$html:
				$message = &$this->_addMixedPart();
				for ($i=0; $i<count($this->attachments); $i++) {
					$this->_addAttachmentPart($message, $this->attachments[$i]);
				}
				break;
			case $Text AND $attachments:
				$message = &$this->_addMixedPart();
				$this->_addTextPart($message, $this->text);
				for ($i=0; $i<count($this->attachments); $i++) {
					$this->_addAttachmentPart($message, $this->attachments[$i]);
				}
				break;
			case $html AND !$attachments AND !$html_images:
				if (!is_null($this->html_text)) {
					$message = &$this->_addAlternativePart($null);
					$this->_addTextPart($message, $this->html_text);
					$this->_addHtmlPart($message);
				}
				else {
					$message = &$this->_addHtmlPart($null);
				}
				break;
			case $html AND !$attachments AND $html_images:
				if (!is_null($this->html_text)) {
					$message = &$this->_addAlternativePart($null);
					$this->_addTextPart($message, $this->html_text);
					$related = &$this->_addRelatedPart($message);
				}
				else {
					$message = &$this->_addRelatedPart($null);
					$related = &$message;
				}
				$this->_addHtmlPart($related);
				for ($i=0; $i<count($this->html_images); $i++) {
					$this->_addHtmlImagePart($related, $this->html_images[$i]);
				}
				break;
			case $html AND $attachments AND !$html_images:
				$message = &$this->_addMixedPart();
				if (!is_null($this->html_text)) {
					$alt = &$this->_addAlternativePart($message);
					$this->_addTextPart($alt, $this->html_text);
					$this->_addHtmlPart($alt);
				}
				else {
					$this->_addHtmlPart($message);
				}
				for ($i=0; $i<count($this->attachments); $i++) {
					$this->_addAttachmentPart($message, $this->attachments[$i]);
				}
				break;
			case $html AND $attachments AND $html_images:
				$message = &$this->_addMixedPart();
				if (!is_null($this->html_text)) {
					$alt = &$this->_addAlternativePart($message);
					$this->_addTextPart($alt, $this->html_text);
					$rel = &$this->_addRelatedPart($alt);
				}
				else {
					$rel = &$this->_addRelatedPart($message);
				}
				$this->_addHtmlPart($rel);
				for ($i=0; $i<count($this->html_images); $i++) {
					$this->_addHtmlImagePart($rel, $this->html_images[$i]);
				}
				for ($i=0; $i<count($this->attachments); $i++) {
					$this->_addAttachmentPart($message, $this->attachments[$i]);
				}
				break;
		}
		if (isset($message)) {
			$output = $message->encode();
			$this->output   = $output['body'];
			$this->headers  = array_merge($this->headers, $output['headers']);

			// Add message ID header
			srand((double)microtime()*10000000);
			$message_id = sprintf('<%s.%s@%s>', base_convert(time(), 10, 36), base_convert(rand(), 10, 36), !empty($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : $_SERVER['SERVER_NAME']);
			$this->headers['Message-ID'] = $message_id;

			$this->is_built = true;
			return true;
		}
		else {
			return false;
		}
	}
	/**
	* Function to encode a header if necessary
	* according to RFC2047
	* bug fix function added:
	* http://www.phpclasses.org/discuss/package/32/thread/4/
	* NOTE - this is suggested as an option to fix:
	* wanted whitespace is removed if subject contains ÄÖÜß:
	*/
	private function _encodeHeader($input, $charset = 'ISO-8859-1')
	{
		$m = preg_match_all('/(\w*[\x80-\xFF]+\w*)/', $input, $matches);
		if ($m === true) {
			$input = mb_encode_mimeheader($input,$charset, 'Q');
		}
		return $input;
	}
	/*
	// original function:
	private function _encodeHeader($input, $charset = 'ISO-8859-1')
	{
		preg_match_all('/(\w*[\x80-\xFF]+\w*)/', $input, $matches);
		foreach ($matches[1] as $Value) {
			$Replacement = preg_replace('/([\x80-\xFF])/e', '"=" . strtoupper(dechex(ord("\1")))', $Value);
			$input = str_replace($Value, '=?' . $charset . '?Q?' . $Replacement . '?=', $input);
		}

		return $input;
	}
	*/
	/**
	* Sends the mail.
	*
	* @param  array  $recipients
	* @param  string $type OPTIONAL
	* @return mixed
	*/
	public function send($recipients, $type = 'mail')
	{
		if (!defined('CRLF')) {
			$this->setCrlf($type == 'mail' ? "\n" : "\r\n");
		}
		if (!$this->is_built) {
			$this->buildMessage();
		}
		switch ($type) {
			case 'mail':
				$subject = '';
				if (!empty($this->headers['Subject'])) {
					$subject = $this->_encodeHeader($this->headers['Subject'], $this->build_params['head_charset']);
					unset($this->headers['Subject']);
				}
				// Get flat representation of headers
				foreach ($this->headers as $name => $Value) {
					$Headers[] = $name . ': ' . $this->_encodeHeader($Value, $this->build_params['head_charset']);
				}
				$to = $this->_encodeHeader(implode(', ', $recipients), $this->build_params['head_charset']);
				if (!empty($this->return_path)) {
					$Result = mail($to, $subject, $this->output, implode(CRLF, $Headers), '-f' . $this->return_path);
				}
				else {
					$Result = mail($to, $subject, $this->output, implode(CRLF, $Headers));
				}
				// Reset the subject in case mail is resent
				if ($subject !== '') {
					$this->headers['Subject'] = $subject;
				}

				// Return
				return $Result;
				break;
			case 'smtp':
				## adjust - this one seems to work now without a fix
				$smtp = @smtp::connect($this->smtp_params); // called statically
				// $smtp = smtp::connect($this->smtp_params);
				//$smtp = new smtp();
				// @$smtp->connect($this->smtp_params);

				// Parse recipients argument for internet addresses
				foreach ($recipients as $recipient) {
					## adjust
					// $Addresses = Mail_RFC822::parseAddressList($recipient, $this->smtp_params['helo'], null, false);
					$mailrfc = new Mail_RFC822();
					$Addresses = $mailrfc->parseAddressList($recipient, $this->smtp_params['helo'], null, false);
					foreach ($Addresses as $Address) {
						$smtp_recipients[] = sprintf('%s@%s', $Address->mailbox, $Address->host);
					}
				}
				unset($Addresses); // These are reused
				unset($Address);   // These are reused
				// Get flat representation of headers, parsing
				// Cc and Bcc as we go
				foreach ($this->headers as $name => $Value) {
					if ($name == 'Cc' OR $name == 'Bcc') {
						##  adjust
						// $Addresses = Mail_RFC822::parseAddressList($Value, $this->smtp_params['helo'], null, false);
						$mailrfc = new Mail_RFC822();
						$Addresses = $mailrfc->parseAddressList($Value, $this->smtp_params['helo'], null, false);

						foreach ($Addresses as $Address) {
							$smtp_recipients[] = sprintf('%s@%s', $Address->mailbox, $Address->host);
						}
					}
					if ($name == 'Bcc') {
						continue;
					}
					$Headers[] = $name . ': ' . $this->_encodeHeader($Value, $this->build_params['head_charset']);
				}
				// Add To header based on $recipients argument
				$Headers[] = 'To: ' . $this->_encodeHeader(implode(', ', $recipients), $this->build_params['head_charset']);

				// Add headers to send_params
				$send_params['headers']    = $Headers;
				$send_params['recipients'] = array_values(array_unique($smtp_recipients));
				$send_params['body']       = $this->output;

				// Setup return path
				if (isset($this->return_path)) {
					$send_params['from'] = $this->return_path;
				}
				elseif (!empty($this->headers['From'])) {
					## adjust
					// $from = Mail_RFC822::parseAddressList($this->headers['From']);
					$mailrfc = new Mail_RFC822();
					$from = $mailrfc->parseAddressList($this->headers['From']);
					// echo '<pre>fr: ';
// 					echo $from[0]->mailbox . '<br>';
// 					echo $from[0]->host . '<br>';
					// var_dump($from);
					// echo '</pre>';
					// _validateAtom($atom)
					$send_params['from'] = sprintf('%s@%s', $from[0]->mailbox, $from[0]->host);
				}
				else {
					$send_params['from'] = 'postmaster@' . $this->smtp_params['helo'];
				}
				// Send it
				if (!$smtp->send($send_params)) {
					// $this->errors = $smtp->errors;
					$this->errors = $smtp->getErrors();
					return false;
				}
				return true;
				break;
		}
	}
	/**
	* Use this method to return the email
	* in message/rfc822 format. Useful for
	* adding an email to another email as
	* an attachment. there's a commented
	* out example in example.php.
	*/
	public function getRFC822($recipients)
	{
		// Make up the date header as according to RFC822
		$this->setHeader('Date', date('D, d M y H:i:s O'));

		if (!defined('CRLF')) {
			$this->setCrlf($type == 'mail' ? "\n" : "\r\n");
		}
		if (!$this->is_built) {
			$this->buildMessage();
		}
		// Return path ?
		if (isset($this->return_path)) {
			$Headers[] = 'Return-Path: ' . $this->return_path;
		}
		// Get flat representation of headers
		foreach ($this->headers as $name => $Value) {
			$Headers[] = $name . ': ' . $Value;
		}
		$Headers[] = 'To: ' . implode(', ', $recipients);
		## adjust
		$Return = implode(CRLF, $Headers) . CRLF . CRLF . $this->output;
		return $Return;
	}
} // End of class.
?>
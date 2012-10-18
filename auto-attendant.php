<?php

////////////////////////////////////////////////////////////////////////////// 
// What does this script do?
// When someone calls your Hoiio Number, the script will read out your transfer directory.
// When a key is pressed, transfer/sms/record voicemail accordingly.
//
// IMPORTANT:
// Rename config-sample.php to config.php, and change the required info.
//////////////////////////////////////////////////////////////////////////////

// Configuration file
include 'config.php';

// Setup Hoiio SDK
require 'php-hoiio/Services/HoiioService.php';
$hoiio = new HoiioService($HOIIO_APP_ID, $HOIIO_ACCESS_TOKEN);

// app_state is the application state at the point when script post to Hoiio
// Look at $app_state and decide what to do now
$app_state = $_POST["app_state"];
switch ($app_state) {
	case NULL:
		// State: A call comes in
		// Action: Answer the call, play a welcome message, and gather key response
		$notify = $hoiio->parseIVRNotify($_POST);
		$session = $notify->getSession();
		$text = $TEXT_WELCOME_MESSAGE . formDirectoryText($directory);
		// Gather for a single digit. Repeat 3 times max.
		$hoiio->ivrGather($session, $THIS_SERVER_URL . '?app_state=gather', $text, 1, 10, 3);
		break;

	case 'gather':
		// State: User has pressed a key
		// Action: Transfer accordingly
		$notify = $hoiio->parseIVRNotify($_POST);
		$session = $notify->getSession();
		$key = $notify->getDigits();
		$transfer_to = $directory[$key];
		switch ($transfer_to[0]) {
			case NULL:
				// Invalid key. Retry gather
				$text = $TEXT_INVALID_KEY . ' ' . formDirectoryText($directory);
				$hoiio->ivrGather($session, $THIS_SERVER_URL . '?app_state=gather', $text, 1, 10, 3);
				break;
			case 'SMSALERT':
				// Send an SMS Alert and hangup
				$hoiio->sms($MY_MOBILE_NUMBER, 'You have received a call from ' . $_POST['from'], $SMS_SENDER_NAME);
				$hoiio->ivrHangup($session, $THIS_SERVER_URL . '?app_state=hangup', $TEXT_SMS_ALERT_SENT);
				break;
			case 'VOICEMAIL':
				// Record a voicemail
				$hoiio->ivrRecord($session, $THIS_SERVER_URL . '?app_state=record', $TEXT_RECORD_VOICEMAIL);
				break;
			default:
				// Transfer
				$hoiio->ivrTransfer($session, $transfer_to[0], $THIS_SERVER_URL . '?app_state=transfer', $TEXT_TRANSFERRING, '', '', 'continue');
				break;
		}
		break;

	case 'transfer':
		// State: Transferring
		// Action: If failed, play a message
		if ($_POST['transfer_status'] != 'answered') {
			$notify = $hoiio->parseIVRNotify($_POST);
			$session = $notify->getSession();
			// If could not transfer, we ask to retry
			$hoiio->ivrGather($session, $THIS_SERVER_URL . '?app_state=gather', $TEXT_TRANSFER_FAILED, 1, 10, 3);		
		}
		break;

	case 'record':
		// State: Recorded voicemail
		// Action: Send an SMS
		$recordUrl = $_POST['record_url';
		$newVoiceMail = "New Voicemail from " . $_POST['from'] . ": " . $recordUrl];
		appendToVoiceMail($newVoiceMail);
		// $post_body = file_get_contents('php://input');
		// appendToVoiceMail(http_build_query($_POST));
		if ($_POST['record_url'] != NULL) {
			$shortenUrl = shortenUrl($recordUrl, $GOOGLE_URL_SHORTENER_API_KEY);
			$hoiio->sms($MY_MOBILE_NUMBER, 'You have received a voicemail from ' . $_POST['from'] . '. Listen at ' . $shortenUrl, $SMS_SENDER_NAME);
		}
		break;
}

/** Return the text "Press 1 to ... Press 2 to ..." **/
function formDirectoryText($directory) {
	$text = '';
	foreach ($directory as $key => $value) {
		$text = $text . ' Press ' . $key . ' ' . $value[1]. '.';
	}
	return $text;
}

/** Append a line of text in voicemail.txt **/
function appendToVoiceMail($text) {
	$myFile = "voicemail.txt";
	$fh = fopen($myFile, 'a') or die("can't open file");
	fwrite($fh, $text . "\n");
	fclose($fh);	
}

/** Shorten URL **/
function shortenUrl($longUrl, $apiKey = '') {
	$postData = array('longUrl' => $longUrl, 'key' => $apiKey);
	$jsonData = json_encode($postData);
	 
	$curlObj = curl_init();
	 
	curl_setopt($curlObj, CURLOPT_URL, 'https://www.googleapis.com/urlshortener/v1/url');
	curl_setopt($curlObj, CURLOPT_RETURNTRANSFER, 1);
	curl_setopt($curlObj, CURLOPT_SSL_VERIFYPEER, 0);
	curl_setopt($curlObj, CURLOPT_HEADER, 0);
	curl_setopt($curlObj, CURLOPT_HTTPHEADER, array('Content-type:application/json'));
	curl_setopt($curlObj, CURLOPT_POST, 1);
	curl_setopt($curlObj, CURLOPT_POSTFIELDS, $jsonData);
	 
	$response = curl_exec($curlObj);
	 
	//change the response json string to object
	$json = json_decode($response);
	 
	curl_close($curlObj);
	 
	return $json->id;
}

?>
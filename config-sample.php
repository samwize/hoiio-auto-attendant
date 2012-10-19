<?php

// Important information. Change as needed.
$HOIIO_APP_ID 		= "";		// Get from http://developer.hoiio.com
$HOIIO_ACCESS_TOKEN = "";		// Get from http://developer.hoiio.com
$HOIIO_NUMBER 		= "";		// Hoiio Number that you bought eg. +16501234567
$MY_MOBILE_NUMBER 	= "";		// Your personal mobile number eg. +16501234567
$THIS_SERVER_URL 	= "";		// Your server URL eg. http://www.example.com/myapp/auto-attendant.php

// Text being read. Change as you like, or use the default.
$MY_NAME 				= "Junda";
$TEXT_WELCOME_MESSAGE 	= "Hello. Welcome to " . $MY_NAME . "'s auto attendant.";
$TEXT_TRANSFERRING 		= 'Please wait while I transfer your call.';
$TEXT_TRANSFER_FAILED 	= 'Sorry, the call was not answered. Please try again.';
$TEXT_INVALID_KEY 		= 'You have entered an invalid option. Please try again.';
$TEXT_RECORD_VOICEMAIL 	= 'Please leave your voice message after the beep.';
$TEXT_SMS_ALERT_SENT	= $MY_NAME . ' has received your SMS. He will call you back shortly. Goodbye!';

// Your transfer directory
$directory = array(
	'1'=>array($MY_MOBILE_NUMBER,	"to reach " . $MY_NAME . "'s office phone"),
	'2'=>array($MY_MOBILE_NUMBER,	"to reach " . $MY_NAME . "'s mobile phone"),
	'3'=>array('SMSALERT',			"to send " . $MY_NAME . " an SMS, and he will call you back"),
	'4'=>array('VOICEMAIL',			"to leave " . $MY_NAME . " a voice mail message"),
);

// Others
$SMS_SENDER_NAME 	= $MY_MOBILE_NUMBER;
$GOOGLE_URL_SHORTENER_API_KEY	= '';
?>
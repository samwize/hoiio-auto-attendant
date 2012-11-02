<?php

include 'config.php';
include 'utilities.php';
require 'php-hoiio/Services/HoiioService.php';

// Log all notifications
$post_body = file_get_contents('php://input');
appendToNotificationFile(date("[Y-m-d H:i:s] ") . $post_body);
// print_r($_REQUEST);

// Forward SMS 
if ($_POST['msg'] != NULL) {
	appendToSmsFile($_POST['from'] . ": " . $_POST['msg'] . " [" . $_POST['debit'] . " " . $_POST['currency'] . "]");
	$hoiio = new HoiioService($HOIIO_APP_ID, $HOIIO_ACCESS_TOKEN);
	$hoiio->sms($MY_MOBILE_NUMBER, 'Fr: ' . $_POST['from'] . "\n" . $_POST['msg'], $SMS_SENDER_NAME);
}
?>
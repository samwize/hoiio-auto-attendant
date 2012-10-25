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

// Logging all Hoiio notifications
$post_body = file_get_contents('php://input');
appendToNotificationFile(date("[Y-m-d H:i:s] ") . $post_body);

// The call can be hung up at any point in time
// If it is "ended", do whatever is needed, then return
$call_state = $_POST["call_state"];
if ($call_state == "ended") {
    // If there is a voicemail
    if ($_POST["record_url"] != NULL)
        handleVoiceMail($_POST['from'], $_POST['record_url']);
    
    // Log the call
    $call_record = ">> " . $_POST["from"]. " to " . $_POST["to"] . " for " . $_POST["duration"] . " min [" . $_POST["date"] . "]. Cost: " . $_POST["debit"] . " " . $_POST["currency"];
    appendToCallRecordFile($call_record);
    return;
}

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
        // Action: Send an SMS. Hangup
        handleVoiceMail($_POST['from'], $_POST['record_url']);
        $hoiio->ivrHangup($session, $THIS_SERVER_URL . '?app_state=hangup', $TEXT_RECORDED_AND_HANGUP);
        break;
}

/** Handle when there is a voicemail */
function handleVoiceMail($from, $record_url) {
    global $hoiio, $MY_MOBILE_NUMBER, $SMS_SENDER_NAME;

    $newVoiceMail = "New Voicemail from " . $from . ": " . $record_url;
    appendToVoiceMailFile($newVoiceMail);
    if ($record_url != NULL && $hoiio != NULL) {
        $shortenUrl = shortenUrl($record_url, $GOOGLE_URL_SHORTENER_API_KEY);
        $hoiio->sms($MY_MOBILE_NUMBER, 'You have received a voicemail from ' . $from . '. Listen at ' . $shortenUrl, $SMS_SENDER_NAME);
    }
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
function appendToVoiceMailFile($text) {
    appendToFile($text, 'voicemails.log');
}

function appendToCallRecordFile($text) {
    appendToFile($text, 'calls.log');
}

function appendToNotificationFile($text) {
    appendToFile($text, 'notifications.log');
}

function appendToFile($text, $filename = "others.log") {
    $fh = fopen($filename, 'a') or die("can't open file");
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
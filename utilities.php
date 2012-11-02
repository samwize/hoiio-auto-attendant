<?php

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

function appendToSmsFile($text) {
    appendToFile($text, 'sms.log');
}

function appendToSmsCsv($text) {
    appendToFile($text, 'sms.csv');
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
Hoiio Auto Attendant
====================

Hoiio Auto Attendant is a telephony service that automatically transfer your calls with a simple voice menu.

This is how it works:

- When someone calls your Hoiio number, it plays a voice menu reading out various options such as "Press 1 to transfer to my office phone. Press 2 to ..."
- The caller will respond by pressing the phone's keypad
- As described in the voice menu, it will transfer to another phone number, record a voicemail or SMS to return the call


Requirements
-------------

- PHP Web Server
- [Hoiio Developer Account](http://developer.hoiio.com/)


Setup
------

Clone this project:

	git clone https://github.com/samwize/hoiio-auto-attendant.git

Download [Hoiio PHP SDK](https://github.com/Hoiio/hoiio-php/zipball/master), unzip it, rename to `php-hoiio`, and place it in the project folder. You should have the following folders.
	
	$ ls -la
	drwxr-xr-x  10 Junda  staff   340B 18 Ott 18:06 ./
	drwxr-xr-x   9 Junda  staff   306B 18 Ott 18:07 ../
	drwxr-xr-x  14 Junda  staff   476B 18 Ott 18:01 .git/
	-rw-r--r--   1 Junda  staff    23B 16 Ott 18:05 .gitignore
	-rw-r--r--   1 Junda  staff   151B 18 Ott 18:01 README.md
	-rw-r--r--@  1 Junda  staff   4,4K 18 Ott 17:46 auto-attendant.php
	-rw-r--r--   1 Junda  staff   1,3K 18 Ott 17:45 config-sample.php
	drwxr-xr-x@  6 Junda  staff   204B 16 Ott 02:16 php-hoiio/

Rename config-sample.php to config.php. This file contains the Hoiio account information and also the application configurations such as the voice menu. 

	$ mv config-sample.php config

Read on to configure the application.


Configure config.php
---------------------

There are various configurations.

These MUST be changed:

	$HOIIO_APP_ID 		= "";		// Get from http://developer.hoiio.com
	$HOIIO_ACCESS_TOKEN = "";		// Get from http://developer.hoiio.com
	$HOIIO_NUMBER 		= "";		// Hoiio Number that you bought eg. +16501234567
	$MY_MOBILE_NUMBER 	= "";		// Your personal mobile number eg. +16501234567
	$THIS_SERVER_URL 	= "";		// Your server URL eg. http://www.example.com/myapp/auto-attendant.php

To get a Hoiio App ID, Access Token and Number, read the next section.

You will probably also want to change the voice menu. It is a simple dictionary with the 'key' as keypad respond, and the 'value' is a 2-element array containing the action and the text.

The default voice menu:

	$directory = array(
		'1'=>array($MY_MOBILE_NUMBER,	"to reach " . $MY_NAME . "'s office phone"),
		'2'=>array($MY_MOBILE_NUMBER,	"to reach " . $MY_NAME . "'s mobile phone"),
		'3'=>array('SMSALERT',			"to send " . $MY_NAME . " an SMS, and he will call you back"),
		'4'=>array('VOICEMAIL',			"to leave " . $MY_NAME . " a voice mail message"),
	);

For example, it will read out "Press 1 to reach Junda's office phone.". If the caller press 1 on the keypad, then it will be transferred to the mobile number. You many change the text and numbers as you like. 


Hoiio Account
--------------

You need to register a Hoiio Account at [http://developer.hoiio.com/](http://developer.hoiio.com/). 

After login, create an app and note down the Hoiio App ID and Access Token.

Then go to Numbers and purchase a number of your choice. 

Configure the number and change the **Voice Notify URL** to your server script URL eg. http://www.example.com/myapp/auto-attendant.php

Note: A free Hoiio account has trial restrictions. You would need to top up Hoiio credits to lift the trial.


Deploy
-------

Upload the project folder to any PHP web server.

Call your Hoiio number and you should hear the voice menu!





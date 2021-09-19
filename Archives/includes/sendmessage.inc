<?php

include ('includes/xmpphp/XMPPHP/XMPP.php');

function send_gmail_message( $recipient, $message ) {
	#Use XMPPHP_Log::LEVEL_VERBOSE to get more logging for error reports
	#If this doesn't work, are you running 64-bit PHP with < 5.2.6?
	$conn = new XMPPHP_XMPP('talk.google.com', 5222, 'bob.t.erbauer', 'build4me', 'xmpphp', 'gmail.com', $printlog=false, $loglevel=XMPPHP_Log::LEVEL_INFO);
    $conn->connect();
    $conn->processUntil('session_start');
    $conn->presence();
    $conn->message($recipient, $message );
    $conn->disconnect();
}

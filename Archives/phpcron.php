<?php

$_POST['UserNameEntryField']='gordon';
$_POST['Password']='baldw1n';
$_POST['IgnoreTitle']='AutoOrdering';
if ($_SERVER['argc']==3) { 
	ob_start();
	include($_SERVER['argv'][1]);
	$the = ob_get_contents();
	ob_end_clean();

	include('includes/htmlMimeMail.php');
	$Recipients = array('"Gordon Kruberg" <gordon@gumstix.com>');
	
	$mail = new htmlMimeMail();
	$mail->setHtml( $the );
	$mail->setSubject( $_SERVER['argv'][2]);
	$mail->setFrom( "Bob Erbauer<bob@gumstix.com>");
	$result = $mail->send( $Recipients );
	echo "Running: ".$_SERVER['argv'][1]. " using " . $_SERVER['argv'][0] . "\n" ;
} else {
	echo "Needs 2 arguments: filename, subject line.\n";
}

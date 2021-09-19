<?php
if ( $_SERVER['argv'][1] ) {
	$pageToSend = $_SERVER['argv'][1];
	{
		$_POST['UserNameEntryField']='gordon';
		$_POST['Password']='baldw1n';
		$_POST['IgnoreTitle']="IGNORE";
//		ob_start();
		include( $pageToSend ) ;
//		$the = ob_get_contents();
//		ob_end_clean();
//		$Recipients = array('"Gordon Kruberg" <gordon@gumstix.com>' );
//		include('includes/htmlMimeMail.php');
//		$mail = new htmlMimeMail();
//		$mail->setHtml( $the );
//		$mail->setSubject( $title );
//		$mail->setFrom( "Bob Erbauer<bob@gumstix.com>");
//		$result = $mail->send( $Recipients );
//		ob_flush();
	}
} else {
	echo "Nope.\n";
	exit;
}

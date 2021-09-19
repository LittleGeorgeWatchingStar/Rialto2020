<?php
/* $Revision: 1.7 $ */
$bgText = array(
		'Silicon Valley Bank',	'3003 Tasman Drive.',			'Santa Clara, CA 95054',	'(408) 654-7400',
		'Gumstix, Inc.',	'3130 Alpine Rd., Suite 288-606',	'Portola Valley, CA 94028',	'DATE',
		'09-4039 / 1211',
		'PAY TO THE ORDER OF',
		'DOLLARS',
		'MEMO' );
$bgSize = array(
		9,	9,	9,	9,
		14,	9,	9,	9,
		8,
		8,
		10,
		9	);
$bgX =  array(	350,	350,	350,	350,
		 55,	 55,	 55,	455,
		370, 
		20,
		510,
		20 );

$bgY = array(	10,	20,	30,	40,
		39,	49,	59,	58,
		65,
		100,
		120,
		213	);

$bgJust = array(
		'centre',	'centre',	'centre',	'centre',
		'left',		'left',		'left',		'left',
		'left',
		'left',
		'left',
		'left'	);

for ($it = 0; $it<12; $it++) {
	$LeftOvers = $pdf->addTextWrap( $bgX[$it],
		 775 - $bgY[$it],
		 150, 
		 $bgSize[$it],
		 $bgText[$it],
                 $bgJust[$it]
		 );
}
?>

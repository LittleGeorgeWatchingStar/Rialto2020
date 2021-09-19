<?php
/* $Revision: 1.4 $ */

$PageSecurity = 2;
ini_set("memory_limit","36M");

$Form_Name = "sed.png";
$_POST['FormID'] = $Form_Name;

require_once 'includes/session.inc';
include('includes/PDFStarter_ros.inc');
require_once 'includes/DateFunctions.inc';
require_once('gumstix/tools/I18n.php'); // 00044 - utf8ToAscii()

$FontSize=11;
$pdf->addinfo('Title', $Form_Name );
$pdf->addinfo('Subject',"Filing");

$PageNumber=0;
$line_height=10;

$YPos += $line_height;
if (!isset( $_POST['FormID'] )) {
       $_POST['FormID'] = 'f1120';
}

if ($_POST['FormID'] == 'sed.png') {
    $filename = SITE_FS_WEBERP_PUBLIC . "/Forms/$Form_Name";
    $pshandle = fopen($filename,'rb');
    $backgroundImage = fread($pshandle, filesize($filename));
    fclose($pshandle);
} else {
    $handle = curl_init("http://ops.gumstix.com/svn/weberp/gumstix/Forms/1120.jpg");
}

/*
curl_setopt($handle, CURLOPT_USERPWD, 'weberp:saywhat');
curl_setopt($handle, CURLOPT_HEADER, false);
curl_setopt($handle, CURLOPT_RETURNTRANSFER, true);
curl_setopt($handle, CURLOPT_USERAGENT, 'WebERP');
curl_setopt($handle, CURLOPT_BINARYTRANSFER, true);
curl_setopt($handle, CURLOPT_FAILONERROR, true);
$backgroundImage = curl_exec($handle); // set topImage or botImage
curl_close($handle);
*/

$topImage = imagecreatefromstring($backgroundImage);
$topX = imagesx($topImage);
$topY = imagesy($topImage);
$scale = $topX / $topY * $pictureHeight;
$pagew = 8.5*72;
$pageh = 11*72;
$pdf->addImage($topImage,0,0, $pagew );

/* "Forms" table specifies the positions of fields in a form. */
$fieldSQL = "SELECT * FROM Forms WHERE FormID='" . $_POST['FormID'] ."'";
$fieldResults = DB_Query($fieldSQL, $db );
while ($myFields = DB_fetch_array($fieldResults)) {
    /* If the current field is a variable field... */
    if (substr($myFields['Text'],0,1)=='$') {

        if (substr($myFields['Text'],0,2)=='$$') {
            /* These fields form the items list, and come from the POST. */
            $j=-1;
            while ($j++<=$_POST['NumRows']) {
                $mytext = $_POST[$j . "_" . substr($myFields['Text'],2)];
                $pdf->addTextWrap($Left_Margin+$myFields['X'],$pageh-$myFields['Y'] - 25*$j,$myFields['L'],10,$mytext,$myFields['A']);
            }
        } else {
            /* These are other posted fields. */
            $mytext = $_POST[substr($myFields['Text'],1)];
            // 00044 - our PDF library can't do UTF-8, so we need to strip out
            // foreign characters.
            $mytext = utf8ToAscii($mytext);
            $pdf->addTextWrap($Left_Margin+$myFields['X'],$pageh-$myFields['Y'],$myFields['L'],10,$mytext,$myFields['A']);
        }
    } else {
        /* These fields have constant values that come from the DB. */
        $pdf->addTextWrap($Left_Margin+$myFields['X'],$pageh-$myFields['Y'],$myFields['L'],10,$myFields['Text'],$myFields['A']);
    }
}

header('Content-type: application/pdf');
header("Content-Length: $len");
header('Content-Disposition: inline; filename=' . $_POST['FormID'] . '.pdf');
header('Expires: 0');
header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
header('Pragma: public');
$pdf->stream();

?>


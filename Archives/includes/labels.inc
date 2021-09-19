<?php

use Rialto\PrintBundle\Exception\PrinterException;
require_once 'gumstix/erp/tools/Printer.php';

function label_reset($fp)
{
    fwrite($fp,"^027*");
}

define("WRAP_NEITHER", "000");
define("WRAP_LINE",    "001");
define("WRAP_PAGE",    "002");
define("WRAP_BOTH",    "003");
function label_setwrapmode($fp,$mode)
{
    fwrite($fp,"^029T^$mode");
}

function label_setcharsperline($fp,$chars)
{
    fwrite($fp,"^029t^"); fprintf($fp, "%03d", $chars);
}

define("PORTRAIT",  "000");
define("LANDSCAPE", "001");
function label_setorientation($fp,$orientation)
{
    fwrite($fp,"^029V^$orientation");
}

define("FONT_7",  "T");
define("FONT_10", "U");
define("FONT_12", "M");
define("FONT_16", "P");
define("FONT_20", "S");
function label_setfontsize($fp, $size)
{
    fwrite($fp, "^027$size");
}

function label_setypos($fp, $ypos)
{
        fprintf($fp, "^027Y^%03d", $ypos);
}

function label_setxpos($fp, $xpos)
{
    fprintf($fp, "^027X^%03d^%03d", floor($xpos/256), $xpos%256);
}

function label_newline($fp)
{
        fwrite($fp,"^010");
}

function label_text($fp, $txt)
{
    fwrite($fp, $txt);
}

function barcode_text($fp, $txt)
{
    fprintf($fp, "^029h^%03d", 101);
    fwrite($fp, "^029k" . chr(2) . chr(strlen($txt)) . $txt);

}

function draw_EL_image($fp,$x, $y, $image)
{
    fprintf($fp, "^029*" . chr( 10 ) .  "^027D" . chr($x)  );
    for ($j=0;$j<$y;$j++) {
        fwrite($fp, chr(22));
        for ($i=0;$i<$x;$i++) {
            fprintf($fp, $image[ $j * $x  + $i ]);
        }
    }
}

function label_formfeed($fp)
{
    fwrite($fp,"^012");
}

function print_label($LABEL_TEXT_1, $LABEL_TEXT_2, $LABEL_TEXT_3, $BARCODE_TEXT,
    $newprinter=0 )
{
    if ($newprinter!=0) {
        print_label_1($LABEL_TEXT_1, $LABEL_TEXT_2, $LABEL_TEXT_3, $BARCODE_TEXT );
    } else {
        flush();ob_flush();flush();
        $lockfile = fopen('/tmp/labellock','w+');
        while(!flock($lockfile,LOCK_EX))
        {
            echo "locked...";flush();ob_flush();flush();
            sleep(5);
        }
        /* Original logic is to fail if we can't open the printer, so we
         * don't put a try/catch around Printer::open... */
        $fp = Printer::openSerial();

        label_reset($fp);
        label_setwrapmode($fp,WRAP_NEITHER);
        label_setcharsperline($fp,26);
        label_setorientation($fp,LANDSCAPE);

        label_newline($fp);
        label_setfontsize($fp,FONT_16);
        label_setypos($fp,33);
        label_setxpos($fp, 95);
        label_text($fp, $LABEL_TEXT_1);

        label_newline($fp);
        label_setfontsize($fp,FONT_12);
        label_setypos($fp,44);
        label_setxpos($fp, 95);
        label_text($fp, $LABEL_TEXT_2);

        label_newline($fp);
        label_setfontsize($fp,FONT_16);
        label_setypos($fp,48);
        label_setxpos($fp, 95);
        label_text($fp, $LABEL_TEXT_3);

        label_newline($fp);
        label_text($fp,$BARCODE_TEXT);
        label_formfeed($fp);

        flush();ob_flush();flush();
        fclose($fp);
        fclose($lockfile);
    }
}

function print_label_1( $LABEL_TEXT_1, $LABEL_TEXT_2, $LABEL_TEXT_3, $BARCODE_TEXT ) {
    $Page_Width    =  75.0;
    $Page_Height   = 220.0;
    $Top_Margin    =  0;
    $Bottom_Margin =  40.0;
    $Left_Margin   =  0;
    $Right_Margin  =  0;

    if (!extension_loaded("ps")) {
        dl ("ps.so");
    }

    $tmp_fn = tempnam ( '/tmp', 'LBL' );
    $ps_label = ps_new();

    ps_set_info($ps_label, "BoundingBox", "0 0 " . $Page_Width . " " . $Page_Height );
    ps_set_parameter($ps_label, "warning", "true");

    ps_open_file( $ps_label, $tmp_fn  );
    ps_begin_page  (  $ps_label, $Page_Width , $Page_Height );
    $font_id    = ps_findfont  (  $ps_label, "../fonts/arial", null, 1);

    ps_setfont( $ps_label,  $font_id, 11);
    $x =  25.0;
    $y = $Page_Height;
//      ps_set_text_pos  ( $ps_label  , $x  , $y  );
//      ps_show( $ps_label, $ReelID  );
//      ps_save( $ps_label );

    ps_rotate( $ps_label, -90.0 );
    ps_translate(  $ps_label  , -( $Page_Height - 10 ), 0  );

    ps_set_text_pos  ( $ps_label, 10, 28  );
    ps_show( $ps_label, $LABEL_TEXT_2);
    ps_setfont( $ps_label,  $font_id, 8);
    ps_continue_text( $ps_label, $LABEL_TEXT_3);
    ps_setfont( $ps_label,  $font_id, 8);
    ps_continue_text( $ps_label, $BARCODE_TEXT );

//      ps_restore( $ps_label );
    ps_end_page  ( $ps_label );

    ps_close( $ps_label );
    ps_delete( $ps_label );

    try {
        $fp = Printer::openLabel();
    }
    catch (PrinterException $ex ) {
        prnMsg($ex->getMessage(), 'error');
    }
    if ($fp) {
            $pshandle = fopen( $tmp_fn, 'rb' );
            $psdata=fread($pshandle, filesize( $tmp_fn ));
            fwrite($fp, $psdata);
            fclose($pshandle);
    }
    fclose($fp);
    unlink ( $tmp_fn );
}
?>

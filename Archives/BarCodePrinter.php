<?php

use Rialto\UtilBundle\Exception\PrinterException;
$PageSecurity = 2;

include('includes/session.inc');
session_start();

$title = _('Bar code printer test');
include('includes/DateFunctions.inc');
include('includes/WO_ui_input.inc');
include_once("includes/CommonGumstix.inc");
require_once 'gumstix/erp/tools/Printer.php';


function print_stock_label( $StockID, $ReelID, $Qty ) {
    $Page_Width    =  75.0;
    $Page_Height   = 220.0;
    $Top_Margin    =  0;
    $Bottom_Margin =  40.0;
    $Left_Margin   =  0;
    $Right_Margin  =  0;

    if (!extension_loaded("ps")) {
        dl ("ps.so");
    }

    $tmp_fn = SITE_FS_WEBERP_REPORTS . "/pstest.ps";
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
    ps_save( $ps_label );

    ps_rotate( $ps_label, -90.0 );
    ps_translate(  $ps_label  , -( $Page_Height - 10 ), 0  );

    ps_set_text_pos  ( $ps_label, 10, 28  );
    ps_show( $ps_label, $StockID  );
    ps_setfont( $ps_label,  $font_id, 8);
    ps_continue_text( $ps_label, $ReelID );
    ps_setfont( $ps_label,  $font_id, 8);
    ps_continue_text( $ps_label, '#' . number_format($Qty,0) );

    ps_restore( $ps_label );
    ps_end_page  ( $ps_label );

    ps_close( $ps_label );
    ps_delete( $ps_label );

    try {
        $fp = Printer::openLabel();
    }
    catch ( PrinterException $ex ) {
        prnMsg($ex->getMessage(), 'error');
    }
    if ($fp) {
        $pshandle = fopen( $tmp_fn, 'rb' );
        $psdata=fread($pshandle, filesize( $tmp_fn ));
        fwrite($fp, $psdata);
        fclose($pshandle);
    }
    fclose($fp);
}


function print_barcode_label( $StockID, $ReelID, $Qty ) {
    $Page_Width    =  75.0;
    $Page_Height   = 220.0;
    $Top_Margin    =  0;
    $Bottom_Margin =  40.0;
    $Left_Margin   =  0;
    $Right_Margin  =  0;

    $code = '*' . $ReelID . $StockID . '*';

    if (!extension_loaded("ps")) {
        dl ("ps.so");
    }

    $ps_label = ps_new();

    ps_set_info($ps_label, "BoundingBox", "0 0 " . $Page_Width . " " . $Page_Height );
    ps_set_parameter($ps_label, "warning", "true");

    ps_open_file( $ps_label, SITE_FS_WEBERP_REPORTS . "/pstest.ps" );

    $temp = ps_begin_template( $ps_label,   $Page_Width, $Page_Height );
    ps_setlinewidth( $ps_label,  1 );
    ps_moveto( $ps_label,   10, $Bottom_Margin  );
    ps_lineto( $ps_label,   70, $Bottom_Margin  );
    ps_lineto( $ps_label,   70, $Page_Height    );
    ps_lineto( $ps_label,   10, $Page_Height    );

    ps_moveto( $ps_label,   70, $Page_Height  );
    ps_lineto( $ps_label,   70, $Page_Height );
    ps_lineto( $ps_label,   10, $Page_Height );
    ps_lineto( $ps_label,   10, $Bottom_Margin  );
    ps_stroke( $ps_label);
    ps_end_template($ps_label);

    ps_begin_page  (  $ps_label, $Page_Width , $Page_Height );

    $font_id    = ps_findfont  (  $ps_label, "../fonts/arial", null, 1);
    $barcode_id = ps_findfont  (  $ps_label, "../fonts/bc/code39", null, 1);
    ps_setfont( $ps_label,  $font_id, 11);

    $x =  25.0;
    $y = $Page_Height;

    ps_set_text_pos  ( $ps_label  , $x  , $y  );
//    ps_show( $ps_label, $ReelID  );

    ps_save( $ps_label );

    ps_rotate( $ps_label, -90.0 );
    ps_translate(  $ps_label  , -( $Page_Height - 10 ), 0  );
    ps_set_text_pos  ( $ps_label, -20, 50  );

    ps_show( $ps_label, $StockID  . ' :' . $ReelID . '    #' . number_format($Qty,0) );

    ps_setfont( $ps_label,  $barcode_id, 32 );
    ps_set_text_pos  ( $ps_label, -20, 15  );
    ps_show( $ps_label, $code );

    ps_restore( $ps_label );
//    ps_place_image( $ps_label,  $temp, 0,  $Bottom_Margin , 1.0 );

    ps_end_page  ( $ps_label );

    ps_close( $ps_label );
    ps_delete( $ps_label );

    try {
        $fp = Printer::openLabel();
    }
    catch (PrinterException $ex) {
        prnMsg($ex->getMessage(), 'error');
    }
    if ($fp) {
        $psfilename = SITE_FS_WEBERP_REPORTS . "/pstest.ps";
        $pshandle = fopen( $psfilename, 'rb' );
        $psdata=fread($pshandle, filesize( $psfilename ));
        fwrite($fp, $psdata);
        fclose($pshandle);
    }
    fclose($fp);
}

print_stock_label( 'CC103A', 4231, 10000);

?>

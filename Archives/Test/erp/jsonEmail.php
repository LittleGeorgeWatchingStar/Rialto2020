<?php

require_once 'Zend/Db.php';
require_once 'Zend/Json.php';
require_once 'Zend/Dojo.php';
require_once 'Zend/Dojo/Data.php';
require_once 'Zend/Dojo/View/Helper/Dojo.php';
require_once 'Zend/Db/Adapter/Pdo/Mysql.php';

require_once 'gumstix/tools/Debugger.php';

define('INVOICES_DIR', '../../reports/invoices');
define('TEMP_DIR', '../../reports/temp4zip');
define('UNZIP_FATAL_ERROR', 3);

$vendor_match = array(
    '1' => array(
        'keyword' => 'Invoice',
        'sender' => 'arrow.com',
        'location' => 'attachment'),
    '3' => array(
        'keyword' => 'Digi-Key Invoice',
        'sender' => 'digikey.com',
        'location' => 'attachment'),
    '11' => array(
        'keyword' => 'Invoice',
        'sender' => 'NUHORIZONS.COM',
        'location' => 'attachment'),
    '14' => array(
        'keyword' => 'PO',
        'sender' => 'innerstep.com',
        'location' => 'attachment'),
    '19' => array(
        'keyword' => 'UPS Billing',
        'sender' => 'ups.com',
        'location' => 'body link'),
    '31' => array(
        'keyword' => 'Avnet',
        'sender' => 'avnet.com',
        'location' => 'attachment'),
    '38' => array(
        'keyword' => 'Invoice',
        'sender' => 'carrferrell.com',
        'location' => 'attachment'),
    '44' => array(
        'keyword' => 'Invoice',
        'sender' => 'bestekmfg.com',
        'location' => 'attachment'),
    '54' => array(
        'keyword' => 'E-Invoice',
        'sender' => 'uture.ca',
        'location' => 'attachment'),
    '61' => array(
        'keyword' => 'Invoice',
        'sender' => '4pcb.com',
        'location' => 'attachment'),
    '127' => array(
        'keyword' => 'Invoice',
        'sender' => 'ddiglobal.com',
        'location' => 'attachment'),
    '154' => array(
        'keyword' => 'Invoice',
        'sender' => 'wpgamericas.com',
        'location' => 'attachment'),
    '160' => array(
        'keyword' => 'Invoice',
        'sender' => 'fastenersuperstore.com',
        'location' => 'attachment'),
    '162' => array(
        'keyword' => 'Invoice from Sakoman Incorporated',
        'sender' => 'sakoman.com',
        'location' => 'attachment'),
    '178' => array(
        'keyword' => 'Invoice',
        'sender' => 'circuitco.com',
        'location' => 'attachment'),
    '182' => array(
        'keyword' => 'Wurth',
        'sender' => 'we-online.com',
        'location' => 'attachment'),
    '190' => array(
        'keyword' => 'GUM',
        'sender' => 'abracon.com',
        'location' => 'attachment'),
    '195' => array(
        'keyword' => 'Invoice',
        'sender' => 'L-COM.Com',
        'location' => 'attachment'),
    '201' => array(
        'keyword' => 'Invoice',
        'sender' => 'labtestcert.com',
        'location' => 'attachment'),
    '212' => array(
        'keyword' => 'Invoice',
        'sender' => 'marshallelectronics.net',
        'location' => 'attachment')
);

function getVendorDir($vendor_id)
{
    $vendor_dir = INVOICES_DIR . "/$vendor_id";
    if ( ! is_dir($vendor_dir) ) {
        mkdir($vendor_dir, 0775, true);
        if ( ! is_dir($vendor_dir) ) {
            trigger_error("Unable to create directory $vendor_dir.", E_USER_ERROR);
        }
    }
    return $vendor_dir;
}

function vendorMatches($header_info, array $vendor)
{
    //logDebug($vendor, 'vendor');
    //logDebug($header_info, 'header info');
    $keywordMatch = false !== stripos($header_info->subject, $vendor['keyword']);
    $senderMatch = false !== strpos($header_info->fromaddress, $vendor['sender']);
    $referenceMatch = (
        false !== strpos($header_info->fromaddress, "gordon@gumstix.com")
        && false !== strpos($header_info->references, $vendor['sender'])
    );
    return ( $senderMatch || $referenceMatch ) && $keywordMatch;
}

function getInvoiceFromAttachments($mailbox, $i, array $email_headers)
{
    $new_emails = array();
    $msg_objects = imap_fetchstructure($mailbox, $i);
    //logDebug($msg_objects, 'msg_objects');
    if ( empty($msg_objects->parts) ) return $new_emails;

    $msg_parts = $msg_objects->parts;
    //logDebug($msg_parts, 'msg_parts');
    $j = 0;
    $the_filename = '';
    if ( count($msg_parts) <= 1 ) return $new_emails;

    $vendor_id = $email_headers['supplierNo'];

    for ( $j = 1; $j <= count($msg_parts); $j ++ )
    {
        $attachment_structure = imap_bodystruct($mailbox, $i, $j);
        //logDebug($attachment_structure, "attachment_structure $j");

        $FileName = null;
        $FileType = strtolower($attachment_structure->subtype);
        if ( 'plain' == $FileType ) continue;

        if ( ( $FileType != 'rfc822') && ( ! empty($attachment_structure->parameters[0]->value)) ) {
            $FileName = substr($attachment_structure->parameters[0]->value, 0, -4);
        }
        if ( ($FileType == 'octet-stream' ) && ( ! empty($attachment_structure->parameters[0]->value)) ) {
            $FileType = strtolower(substr($attachment_structure->parameters[0]->value, -3));
        }
        if ( ! ($FileName && $FileType) ) continue;

        $the_filename = $FileName . '.' . $FileType;

        $vendor_dir = getVendorDir($vendor_id);
        $filepath = "$vendor_dir/$the_filename";
        if ( ! is_file($filepath) ) {
            $attachment_body = imap_fetchbody($mailbox, $i, $j);
            $encoding = (int) $attachment_structure->encoding;
            switch ( $encoding ) {
                case ENC7BIT:
                    file_put_contents(
                        $filepath,
                        quoted_printable_decode($attachment_body)
                    );
                    break;
                case ENCBASE64:
                    file_put_contents(
                        $filepath,
                        imap_base64($attachment_body)
                    );
                    break;
                case ENCQUOTEDPRINTABLE:
                    file_put_contents(
                        $filepath,
                        imap_qprint($attachment_body)
                    );
                    break;
                default:
                    throw new Exception("Unknown encoding type $encoding");
            }
        }
        $new_email = $email_headers;
        $new_email['emailID'] = $i . ':' . $j;
        $new_email['contents'] = $the_filename;
        $new_emails[] = $new_email;
    }
    return $new_emails;
}

function getInvoiceFromBodyLink($mailbox, $i, $email_headers)
{
    $email_body = imap_body($mailbox, $i);
    //logDebug($email_body, 'email body');

    $lines = split("\r", $email_body);
    require_once 'gumstix/erp/tools/ErpCurlHelper.php';
    foreach ( $lines as $line ) {
        $starting = strpos($line, 'https');
        if ( false === $starting ) continue;

        $the_link = substr($line, $starting);
        //logDebug($the_link, 'the link');
        $curl = new ErpCurlHelper();
        $the_ups_page = $curl->fetch($the_link);
        //logDebug($the_ups_page, 'the ups page');
        $the_ups_lines = split("\n", $the_ups_page);

        foreach ( $the_ups_lines as $a_line ) {
            $pattern = '/<a href="(.*1-pdf\.zip.*)">/';
            $matches = array();
            //if ( false === strpos($a_line, "-1-pdf.zip") ) continue;
            if (! preg_match($pattern, $a_line, $matches) ) continue;

            $vendor_id = $email_headers['supplierNo'];
            $real_url = $matches[1];
            //logDebug($real_url, 'real url');
            if ( 19 == $vendor_id && false === strpos( $real_url, '7Y284V' ) ) {
                $vendor_id = 108;
            }

            $filenamePattern = '/([A-Z0-9\-]+)-1-pdf\.zip/';
            $matches = array();
            if (! preg_match($filenamePattern, $real_url, $matches) ) {
                trigger_error("URL $real_url does not match expected filename pattern.", E_USER_ERROR);
            }
            $filename = $matches[1] . '.pdf';
            //logDebug($filename, 'filename');
            $vendor_dir = getVendorDir($vendor_id);
            $filepath = "$vendor_dir/$filename";
            if (! is_file($filepath) )
            {
                if ( ! is_dir(TEMP_DIR) ) {
                    mkdir(TEMP_DIR, 0775, true);
                    if ( ! is_dir(TEMP_DIR) ) {
                        trigger_error("Unable to create directory " . TEMP_DIR,
                            E_USER_ERROR);
                    }
                }
                $curl = new ErpCurlHelper();
                $the_zip_data = $curl->fetch('https://epackage1.ups.com' . $real_url);
                $the_temp_file = TEMP_DIR . "/temp.zip";
                if ( false === file_put_contents($the_temp_file, $the_zip_data) ) {
                    trigger_error("Unable to write to $the_temp_file", E_USER_ERROR);
                }
                $exec_output = array();
                $error_code = UNZIP_FATAL_ERROR;
                exec("unzip $the_temp_file -d $vendor_dir/", $exec_output, $error_code);
                if ( $error_code >= UNZIP_FATAL_ERROR ) {
                    trigger_error("Error code $error_code unzipping $the_temp_file", E_USER_ERROR);
                }
                elseif ( $error_code > 0 ) {
                    trigger_error(
                        "Warning code $error_code unzipping $the_temp_file",
                        E_USER_WARNING
                    );
                }
                unlink($the_temp_file);

                if (! is_file($filepath) ) {
                    trigger_error("Expected file $filepath not found.", E_USER_ERROR);
                }
            }
            $new_email = $email_headers;
            $new_email['supplierNo'] = $vendor_id;
            $new_email['emailID'] = $i;
            $new_email['contents'] = $filename;
            return array( $new_email );
        }
    }
    return array();
}

function main()
{
    global $vendor_match;
    $mailbox_name = "INBOX";
    //	$server_name	= "{mail.gumstix.com:993/imap/ssl/novalidate-cert}";$username	= "roy@gumstix.com";$password	= "fie4tack";
    $server_name = "{208.97.132.223:993/imap/ssl/novalidate-cert}";
    $username = "roy@gumstix.com";
    $password = "fie4tack";
    $mailbox = imap_open($server_name . $mailbox_name, $username, $password);
    if ( ! $mailbox ) die("Unable to connect to $server_name via IMAP.");

    $email_list = array();
    $message_count = imap_num_msg($mailbox);
    //logDebug("found $message_count messages");
    for ( $i = 1; $i <= $message_count; $i ++  ) {
        $header_info = imap_headerinfo($mailbox, $i);

        $email_headers = array(
            'emailID' => $i,
            'messageId' => $header_info->message_id,
            'from' => $header_info->fromaddress,
            'subject' => $header_info->subject
        );
        $emails = array();
        foreach ( $vendor_match as $vendor_id => $m ) {
            if ( ! vendorMatches($header_info, $m) ) continue;

            //logDebug("vendor $vendor_id matches");
            $email_headers['supplierNo'] = $vendor_id;

            switch ( $m['location'] ) {
                case 'body link':
                    $emails = getInvoiceFromBodyLink($mailbox, $i, $email_headers);
                    break;
                case 'attachment':
                    $emails = getInvoiceFromAttachments($mailbox, $i, $email_headers);
                    break;
                default:
                    throw new Exception("Unknown invoice location {$m['location']}");
            }
        }
        foreach ( $emails as $email ) {
            $id = $email['emailID'];
            $email_list[ $id ] = $email;
        }
    }
    imap_close($mailbox);
    //logDebug($email_list, 'email list');

    $tableObj = new Zend_Dojo_Data('emailID', $email_list);
    $tableObj->setLabel('emailID');

    return $tableObj->toJson();
}

echo main();

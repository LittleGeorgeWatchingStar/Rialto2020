<?php
//  $_POST['IgnoreTitle'] =1;
//  $PageSecurity = 10;

//  include ('includes/session.inc');
//  include ('includes/header.inc');
//  include ('includes/WO_ui_input.inc');

//
//	This should be named something like emailInvoiceView or views/emailInvoice
//	This should post data to some script named SuppInvoiceDetailsPost or posts/suppInvoiceDetails
//
//
//	take data from an invoice email and post it to and editable form
//
//	then posting it using xhr to the posts/suppInvoiceDetails will put it into the DB
//	--> both views/*** and posts/*** use the same object classes
//	--> the class needs to be able to create an object from either the email it reads[ i.e. the file] or from the form
//

require_once 'Zend/Form.php';
require_once 'Zend/View.php';
require_once 'Zend/Dojo.php';
require_once 'Zend/Dojo/Form.php';
require_once 'Zend/Dojo/View/Helper/Dojo.php';

require_once 'Zend/Json.php';
require_once 'Zend/Dojo/Data.php';

$view = new Zend_View();
Zend_Dojo::enableView($view);

$view->setScriptPath( 'Test/erp/includes/views/' );
$view->doctype('HTML4_STRICT');

$view->addHelperPath('/js/dojo/', 'Zend_Dojo_View_Helper');

//	$view->dojo()->setUseDeclarative;
$view->dojo()->useCdn();
$view->dojo()->setCdnVersion("1.6.1");
$view->dojo()->enable();
$view->dojo()->setDjConfigOption('parseOnLoad', true);
$view->dojo()->requireModule('dojo.parser');

$view->dojo()->requireModule('dojo.data.ItemFileReadStore');

$view->dojo()->requireModule("dijit.form.Button");
$view->dojo()->requireModule("dijit.form.ComboBox");
$view->dojo()->requireModule('dojo.number');
$view->dojo()->requireModule("dojox.data.HtmlStore");
$view->dojo()->requireModule("dojox.grid.DataGrid");

$view->dojo()->requireModule("dojox.widget.AnalogGauge");
$view->dojo()->requireModule("dojox.widget.gauge.AnalogArcIndicator");
$view->dojo()->requireModule("dojox.widget.gauge.AnalogNeedleIndicator");
$view->dojo()->requireModule("dojox.widget.gauge.AnalogArrowIndicator");
$view->dojo()->requireModule("dojox.data.GoogleSearchStore");
$view->dojo()->addStylesheetModule('dijit.themes.claro');

$display_format = ( isset( $_GET['format'] )) ? ( $_GET['format']) : 0;

switch ( $display_format ) {
    case 1:     echo $view->render('gauge.php'); break;
    default:    echo $view->render('gauge.php'); break;
}
?>

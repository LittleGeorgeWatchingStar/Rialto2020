<?php
require_once 'Zend/View.php';
require_once 'Zend/Json.php';
require_once 'Zend/Dojo.php';
require_once 'Zend/Dojo/Form.php';
require_once 'Zend/Dojo/Data.php';
require_once 'Zend/Dojo/View/Helper/Dojo.php';

$view = new Zend_View();
Zend_Dojo::enableView($view);

$view->addHelperPath('/js/dojo/', 'Zend_Dojo_View_Helper');
//$view->dojo()->setLocalPath('/js/dojo/dojo/dojo.js');
//$view->dojo()->useLocalPath();
$view->dojo()->useCdn();
$view->dojo()->setCdnVersion("1.6.1");

$view->setScriptPath( 'includes/views/' );
$view->doctype('HTML4_STRICT');

$view->dojo()->enable();
$view->dojo()->setDjConfigOption('parseOnLoad', true);
$view->dojo()->addLayer('/js/rialto/form.js');

	define( 'STYLE', 'claro');
//	define( 'STYLE', 'nihilo');
//	define( 'STYLE', 'tundra');
//	define( 'STYLE', 'soria');

$_POST['MENUS'][] = array( 'file_name' => 'financials.xml',		'title' => 'Financials',	'type' => 'xml' ) ;
$_POST['MENUS'][] = array( 'file_name' => 'manufacturing.xml',	'title' => 'Projects',		'type' => 'proj' ) ;
$_POST['MENUS'][] = array( 'file_name' => '', 					'title' => 'Eagle',			'type' => 'jsconf' ) ;
//	$_POST['MENUS'][] = array( 'file_name' => 'PCB3',				'title' => 'Overo Boards',	'type' => 'svn' ) ;
//	$_POST['MENUS'][] = array( 'file_name' => 'PCB1',				'title' => 'Verdex Boards', 'type' => 'svn' ) ;
//	$_POST['MENUS'][] = array( 'file_name' => 'setup.xml',			'title' => 'Setup',         'type' => 'xml' ) ;

if ( isset( $_GET['m'])) {
	$view->dojo()->requireModule("dojox.mobile");
	// Load the lightweight parser.  dojo.parser can also be used, but it requires much more code to be loaded.
	$view->dojo()->requireModule('dojo.parser');
//	$view->dojo()->requireModule("dojox.mobile.parser");

	$view->dojo()->addStyleSheetModule('dijit.themes.claro');
//	$view->dojo()->addStyleSheetModule('dojox.mobile.themes.android');
	$view->dojo()->addStyleSheetModule('dojox.mobile.themes.iphone');
    $dojoStylesheetBase = $view->dojo()->getCdnBase() . $view->dojo()->getCdnVersion();
	$view->dojo()->addStyleSheet("$dojoStylesheetBase/dojox/grid/resources/Grid.css");
	$view->dojo()->addStyleSheet("$dojoStylesheetBase/dojox/grid/resources/claroGrid.css");

	// Load the compat layer if the incoming browser isn't webkit based
	$view->dojo()->requireModule("dojox.mobile.compat");
	$view->dojo()->requireModule("dojox.grid.DataGrid");
	$view->dojo()->requireModule('dijit.Tree');
	$view->dojo()->requireModule('dijit.form.TextBox');

	$view->dojo()->requireModule('dijit.layout.TabContainer');
	$view->dojo()->requireModule('dijit.layout.ContentPane');


	$view->dojo()->requireModule('dojo.data.ItemFileReadStore');
	$view->dojo()->requireModule("dojox.data.HtmlStore");
	$view->dojo()->requireModule('dojox.layout.ContentPane');

	echo $view->render('mExplorer.php');
} else {
	$view->dojo()->requireModule('dojo.parser');

    $view->dojo()->addStyleSheetModule('dijit.themes.claro');
    $dojoStylesheetBase = $view->dojo()->getCdnBase() . $view->dojo()->getCdnVersion();
	$view->dojo()->addStyleSheet("$dojoStylesheetBase/dojox/grid/resources/Grid.css");
	$view->dojo()->addStyleSheet("$dojoStylesheetBase/dojox/grid/resources/claroGrid.css");
//    $view->dojo()->addStyleSheetModule('dojox.mobile.themes.android');
//    $view->dojo()->addStyleSheetModule('dojox.mobile.themes.iphone');

	$view->dojo()->requireModule('dojo.data.ItemFileReadStore');
	$view->dojo()->requireModule("dojox.data.CsvStore");
	$view->dojo()->requireModule("dojox.data.FileStore");
	$view->dojo()->requireModule("dojox.data.HtmlStore");
	$view->dojo()->requireModule("dojox.grid.DataGrid");

	$view->dojo()->requireModule('dijit.Tree');
	$view->dojo()->requireModule('dijit.tree.ForestStoreModel');

	$view->dojo()->requireModule('dijit.form.TextBox');
	$view->dojo()->requireModule('dijit.form.SimpleTextarea');
	$view->dojo()->requireModule("dijit.form.Textarea");
	$view->dojo()->requireModule("dijit.form.CurrencyTextBox");
	$view->dojo()->requireModule("dijit.form.DateTextBox");

	$view->dojo()->requireModule("dijit.layout.AccordionContainer");
	$view->dojo()->requireModule("dojox.layout.ContentPane");
	$view->dojo()->requireModule("dijit.layout.BorderContainer");
	$view->dojo()->requireModule("dijit.layout.StackContainer");
	$view->dojo()->requireModule("dijit.layout.StackController");

	$view->dojo()->requireModule('dojox.gfx.move');

	$view->dojo()->requireModule("dojo.dnd.Moveable");
	$view->dojo()->requireModule('dojo.number');

	$view->dojo()->requireModule("dojox.widget.BarGauge");
	$view->dojo()->requireModule("dojox.widget.AnalogGauge");
	$view->dojo()->requireModule("dojox.widget.gauge.AnalogArrowIndicator");
	$view->dojo()->requireModule("dojox.widget.gauge.AnalogNeedleIndicator");
	$view->dojo()->requireModule("dojox.widget.gauge.BarIndicator");

	$view->dojo()->requireModule("dojox.timing._base");

	echo $view->render('nExplorer.php');
}

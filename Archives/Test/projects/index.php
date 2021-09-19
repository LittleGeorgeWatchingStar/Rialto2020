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
$view->dojo()->setLocalPath('/js/dojo/dojo/dojo.js');
$view->dojo()->useLocalPath();

$view->setScriptPath( 'gumstix/erp/views/' );
$view->doctype('HTML4_STRICT');

$view->dojo()->setUseDeclarative;
//  $view->dojo()->useCdn();
$view->dojo()->enable();
$view->dojo()->setDjConfigOption('parseOnLoad', true);
//  $view->dojo()->setDjConfigOption('isDebug',true);

$view->dojo()->requireModule('dojo.data.ItemFileReadStore');
$view->dojo()->requireModule("dojo.data.ItemFileWriteStore");

$view->dojo()->requireModule('dijit.form.Button');
$view->dojo()->requireModule('dijit.Tree');
$view->dojo()->requireModule('dijit.tree.ForestStoreModel');
$view->dojo()->requireModule('dojox.grid.TreeGrid');
$view->dojo()->requireModule('dojo.parser');

//  $view->dojo()->requireModule("dijit.layout.AccordionContainer");
//  $view->dojo()->requireModule("dijit.layout.ContentPane");
//  $view->dojo()->requireModule("dijit.layout.BorderContainer");

$view->dojo()->requireModule("dojox.widget.BarGauge");
$view->dojo()->requireModule("dojox.widget.gauge.BarIndicator");

//  $view->dojo()->requireModule("dojox.widget.AnalogGauge");
//  $view->dojo()->requireModule("dojox.widget.gauge.AnalogArrowIndicator");

//  $view->dojo()->requireModule("dojox.grid.DataGrid");
//  $view->dojo()->requireModule("dojox.data.CsvStore");
//  $view->dojo()->requireModule("dojox.data.FileStore");
$view->dojo()->requireModule('dojox.data.CsvStore');

//  $view->dojo()->requireModule("dijit.layout.StackContainer");
//  $view->dojo()->requireModule("dijit.layout.StackController");

//  $view->dojo()->requireModule("dojo.dnd.Container");
//  $view->dojo()->requireModule("dojo.dnd.Manager");
//  $view->dojo()->requireModule("dojo.dnd.Source");
//  $view->dojo()->requireModule("dijit.tree.dndSource");

$view->dojo()->requireModule("dojox.gfx");
$view->dojo()->requireModule("dojox.gfx.utils");

	define( 'STYLE', 'claro');
//	define( 'STYLE', 'nihilo');
//	define( 'STYLE', 'tundra');
//	define( 'STYLE', 'soria');

//	$_POST['MENUS'][] = array( 'file_name' => 'sales.xml', 'title' => 'Sales') ;
//	$_POST['MENUS'][] = array( 'file_name' => 'rma.xml', 'title' => 'Returns') ;
//	$_POST['MENUS'][] = array( 'file_name' => 'purchasing.xml', 'title' => 'Purchasing') ;
//	$_POST['MENUS'][] = array( 'file_name' => 'manufacturing.xml', 'title' => 'Manufacturing') ;
//	$_POST['MENUS'][] = array( 'file_name' => 'development.xml', 'title' => 'Development') ;
//  $_POST['MENUS'][] = array( 'file_name' => 'gantt.gan', 'title' => 'Project listing', 'store' => 'tree_1' ) ;
//	$_POST['MENUS'][] = array( 'file_name' => 'setup.xml', 'title' => 'Main') ;


echo $view->render('taskExplorer.php');

?>

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
//	$view->dojo()->useCdn();
$view->dojo()->enable();
$view->dojo()->setDjConfigOption('parseOnLoad', true);

$view->dojo()->requireModule('dojo.data.ItemFileReadStore');
$view->dojo()->requireModule('dijit.Tree');
$view->dojo()->requireModule('dijit.tree.ForestStoreModel');
$view->dojo()->requireModule('dojo.parser');

$view->dojo()->requireModule("dijit.layout.AccordionContainer");
$view->dojo()->requireModule("dijit.layout.ContentPane");
$view->dojo()->requireModule("dijit.layout.BorderContainer");

$view->dojo()->requireModule("dojox.widget.BarGauge");
$view->dojo()->requireModule("dojox.widget.gauge.BarIndicator");

$view->dojo()->requireModule("dojox.widget.AnalogGauge");
$view->dojo()->requireModule("dojox.widget.gauge.AnalogArrowIndicator");

$view->dojo()->requireModule("dojox.grid.DataGrid");
$view->dojo()->requireModule("dojox.data.FileStore");
$view->dojo()->requireModule('dojox.data.CsvStore');

//	$view->headLink()->appendStylesheet("http://ajax.googleapis.com/ajax/libs/dojo/1.5/dijit/themes/claro/claro.css");
//	$view->headLink()->appendStylesheet("http://ajax.googleapis.com/ajax/libs/dojo/1.5/dojox/grid/resources/Grid.css");

	define( 'STYLE', 'claro');
//	define( 'STYLE', 'nihilo');
//	define( 'STYLE', 'tundra');
//	define( 'STYLE', 'soria');

echo $view->doctype();
?>

<HTML>
<HEAD>
<!--
<LINK rel="stylesheet" type="text/css"  href="http://ajax.googleapis.com/ajax/libs/dojo/1.5/dijit/themes/claro/claro.css" >
<LINK rel="stylesheet" type="text/css"  href="http://ajax.googleapis.com/ajax/libs/dojo/1.5/dojox/grid/resources/Grid.css" >
--!>

<LINK rel="stylesheet" type="text/css"  href="/js/dojo/dijit/themes/claro/claro.css" >
<LINK rel="stylesheet" type="text/css"  href="/js/dojo/dojox/grid/resources/Grid.css" >

<?php  //	echo $view->headLink();	?>
<?php  echo $view->dojo();		?>
<style type="text/css">
    html, body { width: 100%; height: 100%; margin: 0; overflow:hidden; }
    #borderContainer { width: 100%; height: 100%; }
</style>

</HEAD>
<BODY CLASS="claro">
<?php
echo $view->render('eagleExplorer.php');
?>
</BODY>
</HTML>

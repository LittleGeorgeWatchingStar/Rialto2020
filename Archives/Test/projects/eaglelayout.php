<?php
require_once 'Zend/Db.php';
require_once 'Zend/Json.php'; 
require_once 'Zend/View.php';
require_once 'Zend/Json.php'; 
require_once 'Zend/Dojo.php';
require_once 'Zend/Dojo/Form.php'; 
require_once 'Zend/Dojo/Data.php'; 
require_once 'Zend/Dojo/View/Helper/Dojo.php'; 
require_once 'Zend/Db/Adapter/Pdo/Mysql.php'; 

$view = new Zend_View();
Zend_Dojo::enableView($view);
$view->dojo()->setUseDeclarative;
$view->dojo()->useCdn();
$view->dojo()->enable();

$view->dojo()->requireModule('dojo.data.ItemFileReadStore');
$view->dojo()->requireModule('dojo.parser');

$board = $_GET['board'];
$sheet = $_GET['sheet'];

$production_directory_name = '/gumstix-hardware/Production/PCB/';
//  $boardFile = str_replace( ".sch", ".mod.csv", $board);
$modFile = $board . ".mod.csv";

if (($handle = fopen( $production_directory_name . $modFile, "r")) !== FALSE) {
    if (($titles = fgetcsv($handle, 1000, ",")) !== FALSE) {
		while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
			$sheets[$data[0]] =   $data[1] ;
		}
	}
    fclose($handle);
}

$bomFile = str_replace( ".sch", ".XY.csv", $board);
//  $bomFile = $board . ".XY.csv";

if (($handle = fopen($production_directory_name . $bomFile, "r")) !== FALSE) {
	$labels = array( 'name', 'x', 'y', 'l', 'r' );
	while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
		if ( $sheets[$data[0]] == $sheet ) {
			$components[] = array(	'name' =>  $data[0], 'sheet' =>  $sheets[$data[0]], 'board' => 'B30014', 
									'x'	=> $data[1],	 'y' => $data[2],	 'l' =>$data[3] , 'r' => $data[4]);
		}
	}
    fclose($handle);
}

  $dataObj = new Zend_Dojo_Data('name', $components );
  $dataObj->setLabel('name');
  echo $dataObj->toJson();
?>

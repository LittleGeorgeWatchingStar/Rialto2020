<?php 

require_once 'Zend/View.php';
require_once 'Zend/Json.php'; 
require_once 'Zend/Dojo.php';
require_once 'Zend/Dojo/Data.php'; 
require_once 'Zend/Dojo/View/Helper/Dojo.php'; 

$view = new Zend_View();
Zend_Dojo::enableView($view);

$view->dojo()->useCdn();
$view->dojo()->enable();

$view->dojo()->requireModule('dijit.Tree');
$view->dojo()->requireModule('dijit.tree.ForestStoreModel');


$boards = array();

$chooser = $_GET['select'];
if ( $_GET['select'] != '' ) {
	$to_match = $_GET['select'];
} else {
	$to_match = 'PCB30014';
}

$production_directory_name = '/gumstix-hardware/Production/PCB/';
$production_directory =  @ dir( $production_directory_name );
while ( ($board_directory_name = $production_directory->read()) !== false) {
	if ( substr_count( $board_directory_name, $to_match)) {
		if ( is_dir( $production_directory_name .'/'. $board_directory_name) && ($board_directory_name !='.') && ($board_directory_name !='..') && ($board_directory_name !='.svn')  ) {
			$board_directory = @ dir ( $production_directory_name .'/'. $board_directory_name );
			$board_name = substr( $board_directory_name, 0, strpos ( $board_directory_name, '-R'));
			$board_version = substr( $board_directory_name, 2 + strpos ( $board_directory_name, '-R') - strlen( $board_directory_name) );
			while ( ($board_file_name = $board_directory->read()) !== false) {
				if ( !is_dir( $production_directory_name .'/'.$board_directory_name.'/'.$board_file_name . '/')) {
					$boards[ $board_name ][$board_version][] = $board_file_name;
					if (( $eagle_file == '') && (substr_count( $board_file_name, '.brd')==1)) {
						$eagle_file = $production_directory_name .'/'. $board_directory_name . '/' . $board_file_name;
					}
				}
			}
		}
	}
}

$id=0;
$board_tree = array();
foreach ( $boards as $board_name => $board) {
	$id++;
//	echo '<B>' . $board_name . '</B><BR>';
	$board_item = array( 'id' => $id, 'name' => $board_name, 'type' => 'folder', 'children' => array() ) ;
	foreach ( $board as $board_version => $files ) {
		$id++;
//		echo '<I>' . $board_name . ': ' .  $board_version . '</I><BR>';
		$version_item = array( 'id' => $id, 'name' => $board_name . '-R' . $board_version, 'type' => 'folder', 'children' => array() ) ;
		foreach ( $files as $file) {
			$id++;
			$version_item['children'][] = array( 'id' => $id, 'name' => $file, 'type' => 'item') ;
		}
		$board_item['children'][] = $version_item;
	}
	$board_tree[] = $board_item;
}

$eagle_program = '/Applications/EAGLE/EAGLE.app/Contents/MacOS/EAGLE';
$eagle_command = 'run /gumstix-hardware/trunk/Eagle/ulp/gum-bio-4.ulp;';

//	echo "'" . $eagle_program . "' -C '". $eagle_command . "' '" . $eagle_file . "'<BR>";
//	system ( "'" . $eagle_program . "' -C '". $eagle_command . "' '" . $eagle_file . "'" );

//print_r( $board_tree );


$treeObj = new Zend_Dojo_Data('id', $board_tree );
$treeObj->setLabel('name');

echo $view->tree->ForestStoreModel = $treeObj->toJson();

?>

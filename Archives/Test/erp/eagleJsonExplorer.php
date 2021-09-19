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

$a  = array();
$t  = array();
$aa = array();
$bb = array();
$p  = array();
$id = 0;

$handle = fopen( "modules.csv", "r");
$t = fgetcsv( $handle );
while ( $aa = fgetcsv( $handle) ) {
    $id ++;
    foreach ( $t as $i => $n ) {
        switch ($n) {
            case "Category":
            case "Name":
            case "Schematic":
            case "Board":
            case "Revision":
            case "Module":
            case "Description":
            case "Sheet":   $bb[ strtolower($n) ] = $aa[ $i ];
                            break;
            default:        break;
        }
    }
    $bb['type'] = 'item';
    $bb['id']   = 'X_' . $id;
    $a[] = $bb;
    if ( !isset( $p[ $bb[ 'category' ] ])) {
      $p[ $bb[ 'category' ] ]['name'] = $bb[ 'category' ];
    }
    $p[ $bb[ 'category' ] ]['children'][] = array ( "_reference" => $bb['id'] );
}

foreach ( $p as $parent ) {
    $id++;
    $parent[ 'id' ] = 'P_' . $id;
    $parent[ 'type' ] = 'folder';
    $a[] = $parent;
}



$treeObj = new Zend_Dojo_Data('id', $a);
$treeObj->setLabel('name');

echo $treeObj->toJson();
?>

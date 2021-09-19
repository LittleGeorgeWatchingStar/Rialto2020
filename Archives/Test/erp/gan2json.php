<?php 

$debug = false;

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
//	$view->dojo()->requireModule('dijit.tree.ForestStoreModel');

class xml_menu {
	protected $parser;
	protected $target_items;

	public function __construct( ) {
		$this->parser = xml_parser_create('UTF-8');
		xml_parser_set_option($this->parser, XML_OPTION_SKIP_WHITE, 15);
		xml_parser_set_option($this->parser, XML_OPTION_TARGET_ENCODING, 'UTF-8');
		xml_set_object($this->parser, $this);
	}

	public function fetch_from_file( $new_filename = null ) {
		xml_parse_into_struct( $this->parser, file_get_contents( $new_filename ), $this->target_items  );
	}

	public function show_topitems() {
		return $this->target_items;
	}
}

define ( 'MENU_DIR', 'includes/menus/');

if ( is_file( MENU_DIR . $_GET['menu_name'])) {
	$source_file = MENU_DIR . $_GET['menu_name'];
} else {
	echo "Failed " . $source_file = MENU_DIR . 'test_exp.xml';
	exit;
}

$tree_data = new xml_menu();
$tree_data->fetch_from_file( $source_file );
$working_data =  $tree_data->show_topitems() ;
$id = 0;
$the_tree = array();
$working_parent = array();
$base_level	= -1;

foreach ( $working_data as $working_id => $working_object ) {
	$id ++;
	switch ( $working_object['type']) {
		case 'open':	$working_on	 =  strtolower( $working_object['tag'] );
						if (($working_on=='task') || ($working_on=='task') || ($working_on=='task') ) {
							$level		 = $working_object['level'];		//	FOR 'open' WE DEFINE A NEW PARENT TO ADD TO
							if ( $base_level < 0) {
								$base_level = $level;
							}
							if ( $level==$base_level ) {
									$to_add = array( 
										'id' => "x" . $id, 
										'type' => 'folder' ) ;
									if ( is_array( $working_object['attributes']) ) {
										foreach ( $working_object['attributes'] as $key => $value ) {
											switch  ( strtolower( $key )) {
												case 'id':		break;
												case 'name':    $to_add[ strtolower( $key ) ] = $value;
																break;
												default: 		$to_add[ strtolower( $key ) ] = $value;
																break;
											}
										}
									}
									if ( !isset(  $to_add[ 'name' ])) {
										$to_add[ 'name' ] = $working_object['tag' ] ;
									}
									$working_parent[ $level ] = count( $the_tree );
									$the_tree[ ] = $to_add;
							} else {
									$to_add = array(
										'id'   => "x" . $id, 
										'type' => strtolower( $working_object['tag' ] ) ) ;
									if ( is_array( $working_object['attributes']) ) {
										foreach ( $working_object['attributes'] as $key => $value ) {
											switch  ( strtolower( $key )) {
												case 'id':		break;
												case 'name':    $to_add[ strtolower( $key ) ] = $value ;
																break;
												default: 		$to_add[ strtolower( $key ) ] = $value;
																break;
											}
										}
									}
									if ( !isset(  $to_add[ 'name' ])) {
										$to_add[ 'name' ] = $working_object['tag' ] ;
									}
								$working_parent[ $level ] = count( $the_tree );
								$the_tree[ ] = $to_add;
								$the_tree[ $working_parent[ $level -1 ]]['children'][] = array( "_reference" => "x" . $id );
							}
							if ( $debug ) {
								echo '<HR>**OPEN**<BR>';
								print_r(  $working_object );
								echo '<BR>';
								echo '<BR>';
								print_r( $the_tree[$working_parent[ $level -1 ]] );
								echo '<BR>';
							}
						}
						break;
		case 'close': 	break;
		case 'complete':if (($working_on=='task') || ($working_on=='task') ) {
							$to_add = array(
								'id'   => "x" . $id, 
								'type' => 'item'  ) ;
							if ( !empty( $working_object['attributes']) ) 
							if ( is_array( $working_object['attributes']) ) {
								foreach ( $working_object['attributes'] as $key => $value ) {
									switch  ( strtolower( $key )) {
										case 'id':		break;
										default: 		$to_add[ strtolower( $key ) ] = $value;
														break;
									}
								}
							}
							if ( !isset(  $to_add[ 'name' ])) {
								$to_add[ 'name' ] = $working_object['tag' ];
							}
							$the_tree[ ] = $to_add;
							if ( $level>1) $the_tree[ $working_parent[ $level ]]['children'][] = array( "_reference" => "x" . $id );
							if ( $debug ) {
								echo '<HR>**COMPLETE**<BR>';
								print_r(  $working_object );
								echo '<BR>';
								echo '<BR>';
								print_r( $to_add );
								echo '<BR>';
							}
							break;
						}
		default:
	}
}

if ($debug) {
	foreach ( $working_data as $datum ) {
		print_r( $datum );
		echo '<BR>';
	}
}
$treeObj = new Zend_Dojo_Data('id', $the_tree );
$treeObj->setLabel('name');
if ( $debug ) {
	echo '<HR>';
}
echo $treeObj->toJson();

?>

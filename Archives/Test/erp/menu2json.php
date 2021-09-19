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

foreach ( $working_data as $working_id => $working_object ) {
	$level = $working_object['level'];
	switch ( $working_object['type']) {
		case 'open':$id ++;
			switch ( $level ) {
				case 1: break;
				case 2: $the_tree[] = array(
						'id' => $id, 
						'name' =>  (empty($working_object['attributes']['NAME'])) ? '' : $working_object['attributes']['NAME'], 
						'type' => 'folder', 
						'value' => (empty($working_object['value']))              ? '' : $working_object['value'], 
						'children' => array()
							 ) ;
				$working_parent[ $level ] = &$the_tree[ count($the_tree) -1 ]['children'];
				break;
			default:$working_parent[ $level-1][] = array( 'id' => $id, 'name' => $working_object['attributes']['NAME'], 'type' => 'folder', 'value' => $working_object['value'], 'children' => array() ) ;
				$working_parent[ $level ] = &$working_parent[ $level-1][ count( $working_parent[ $level-1] ) -1]['children'];
				break;
			}
			break;
		case 'close':
			break;
		case 'complete':
			$id ++;
			$to_add = array ( 'id' => $id,  'name' => $working_object['attributes']['NAME'], 'type' => 'item' );
			foreach ( $working_object['attributes'] as $att_key => $attribute ) {
				switch ( $att_key ) {
					case 'NAME':	break;
					case 'IMAGE':	if (is_file( 'includes/images/OpenIcon/' . $attribute )) {
								$to_add[ 'icon' ] = 'includes/images/OpenIcon/' . $attribute;
							} elseif ( is_file( 'includes/images/Tango/' . $attribute )) {
								$to_add[ 'icon' ] = 'includes/images/Tango/' . $attribute;
							}
					default:	$to_add[ strtolower( $att_key ) ] = $attribute;
				}
			}
			$to_add['value'] = (empty($working_object['value']))              ? '' : $working_object['value'];
			$working_parent[ $level-1][] = $to_add;
			break;
		default:
	}
}

$treeObj = new Zend_Dojo_Data('id', $the_tree );
$treeObj->setLabel('name');
echo $treeObj->toJson();
?>

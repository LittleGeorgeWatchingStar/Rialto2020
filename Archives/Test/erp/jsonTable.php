<?php
require_once 'Zend/Db.php';
require_once 'Zend/Json.php'; 
require_once 'Zend/Dojo.php';
require_once 'Zend/Dojo/Data.php'; 
require_once 'Zend/Dojo/View/Helper/Dojo.php'; 
require_once 'Zend/Db/Adapter/Pdo/Mysql.php'; 

$db = new Zend_Db_Adapter_Pdo_Mysql(array(
    'host'     => 'bug-gum.featurestix.com',
    'username' => 'bug_explorer',
    'password' => 'say3when',
    'dbname'   => 'bug_gum'
));


if ( isset( $_GET['fID'])) {
	switch ( $_GET['fID']) {
	        case '0':       $elected_choice = '"PCB000%"'; break;
	        case '1':       $elected_choice = '"PCB100%"'; break;
	        case '3':       $elected_choice = '"PCB200%"'; break;
	        default:        $elected_choice = '"PCB300%"'; break;
	}
	$selector = $db->select()
             ->from('mantis_project_table', array( 'id as projectID', '*'))
             ->where('name LIKE ' . $elected_choice );

	$projects = $db->fetchAll( $selector );
	$tableObj = new Zend_Dojo_Data('projectID', $projects);
	$tableObj->setLabel('projectID');
	echo $view->tree = $tableObj->toJson();
}

if ( isset( $_GET['pID'])) {
	$selector = $db->select()
             ->from('mantis_bug_table', array( 'mantis_bug_table.id as issueID', '*'))
	     ->joinLeft('mantis_bug_text_table', 'mantis_bug_text_table.id=mantis_bug_table.id')
             ->where('mantis_bug_table.project_id=' . $_GET['pID'] );
	$issues = $db->fetchAll( $selector );
	$tableObj = new Zend_Dojo_Data('issueID', $issues) ;
	$tableObj->setLabel('issueID');
	echo $view->tree = $tableObj->toJson();
}
?>

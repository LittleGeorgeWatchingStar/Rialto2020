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

function csv_parse($str,$f_delim = ',',$r_delim = "\n",$qual = '"')
{
   $output = array();
   $row = array();
   $word = '';

   $len = strlen($str);
   $inside = false;

   $skipchars = array($qual,'\\');

   for ($i = 0; $i < $len; ++$i) {
       $c = $str[$i];
       if (!$inside && $c == $f_delim) {
           $row[] = $word;
           $word = '';
       } elseif (!$inside && $c == $r_delim) {
           $row[] = $word;
           $word = '';
           $output[] = $row;
           $row = array();
       } else if ($inside && in_array($c,$skipchars) && ($i+1 < $len && $str[$i+1] == $qual)) {
           $word .= $qual;
           ++$i;
       } else if ($c == $qual) {
           $inside = !$inside;
       } else {
           $word .= $c;
       }
   }

   $row[] = $word;
   $output[] = $row;

   return $output;
}

function svnGetCsv ( $fileName  )   {
        $handle = curl_init("http://ops.gumstix.com/svn/$fileName");
        curl_setopt($handle, CURLOPT_USERPWD, 'weberp:saywhat');
        curl_setopt($handle, CURLOPT_HEADER, false);
        curl_setopt($handle, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($handle, CURLOPT_USERAGENT, 'WebERP');
        curl_setopt($handle, CURLOPT_FAILONERROR, true);
        $BOMCSV = curl_exec($handle);

        if(curl_errno($handle)) {
                return -2;
        }
        curl_close($handle);

        return ( csv_parse($BOMCSV));
}

$board = $_GET['board'];
$sheet = $_GET['sheet'];

$sheets = array();
$components = array();

$production_directory_name = 'gumstix-hardware/Production/PCB/';

$modFile = $board . ".mod.csv";
$dataRows = svnGetCsv ( $production_directory_name . $modFile );
foreach ( $dataRows as $data ) {
	$sheets[trim($data[0])] =   $data[1] ;
}

$bomFile = str_replace( ".sch", ".XY.csv", $board);
$bomRows = svnGetCsv ( $production_directory_name . $bomFile );
foreach ( $bomRows as $data) {
	$labels = array( 'name', 'x', 'y', 'l', 'r' );
	if ( $sheets[$data[0]] == $sheet ) {
		$components[] = array(  'name' =>  $data[0], 'sheet' =>  $sheets[$data[0]], 'board' => 'BRD30014', 
					'x'     => $data[1],     'y' => $data[2],        'l' =>$data[3] , 'r' => $data[4]);
	}
}

$dataObj = new Zend_Dojo_Data('name', $components );
$dataObj->setLabel('name');
echo $dataObj->toJson();
?>

<?php

/* $Revision: 1.7 $ */

$PageSecurity = 2;

require_once 'Image/GraphViz.php';

function add_brd_nodes($str, $theGraph)
{
    $qual = '"';
    $lastBoard = '';
    $len = strlen($str);
    $state = 0;
    for ( $i = 0; $i < $len;  ++ $i ) {
        $c = $str[$i];
        switch ( $state ) {
            case '0': if ( $c == $qual && $str[$i + 1] != 'h' && $str[$i + 1] != '.' && $str[$i + 1] != '>' ) {
                    $state = 1;
                    $board = '';
                }
                break;
            case '1':
                if ( ($c == '-') && ($str[$i + 1] == 'R') ) {
                    $state = 2;
                    $version = '';
                    ++ $i;
                    if ( $board != $lastBoard ) {
                        $lastBoard = $board;
                        $theGraph->addNode(
                            'Node' . $i, array(
                            'label' => $board,
                            'shape' => 'box'
                            )
                        );
                        if ( strncmp($board, "BRD", 3) != 1 ) {
                            $mfr = "Bestek";
                        }
                        else {
                            $mfr = "Innerstep";
                        }
                        $theGraph->addEdge(
                            array(
                            $mfr => 'Node' . $i
                            ), array(
                            'color' => 'red'
                            )
                        );
                    }
                    else {

                    }
                }
                else {
                    if ( $c == $qual ) {
                        $state = 0;
                    }
                    else {
                        $board .= $c;
                    }
                }
                break;
            case '2': if ( $c != "/" ) {
                    $version .= $c;
                }
                else {
                    $state = 0;
                }
                break;
        }
    }
}

$handle = curl_init("http://svn.rungie.com/svn/gumstix-hardware/Production/BRD/");
curl_setopt($handle, CURLOPT_USERPWD, 'weberp:saywhat');
curl_setopt($handle, CURLOPT_HEADER, false);
curl_setopt($handle, CURLOPT_RETURNTRANSFER, true);
curl_setopt($handle, CURLOPT_USERAGENT, 'WebERP');
curl_setopt($handle, CURLOPT_BINARYTRANSFER, true);
curl_setopt($handle, CURLOPT_FAILONERROR, true);
$BRD_List = curl_exec($handle);
include('includes/session.inc');
$title = _('Select Build Order');
//	include('includes/header.inc');
//	include("includes/WO_ui_input.inc");
if ( curl_errno($handle) ) {
    include('includes/footer.inc');
    exit;
}

$version_List = array();

curl_close($handle);
$graph = new Image_GraphViz(false);

$graph->addNode(
    'Bestek'
);

$graph->addNode(
    'Innerstep'
);

add_brd_nodes($BRD_List, $graph);

$graph->addAttributes(
    array(
        'overlap' => "false",
        'pack' => "true",
        'size' => "8,4"
    )
);

echo $graph->fetch("png");
?>

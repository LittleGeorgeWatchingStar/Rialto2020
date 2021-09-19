<?php
/* $Revision: 1.4 $ */

function drawdrillicon($pdfpage, $tool, $x, $y) 
{
	if ( $tool == 0 ) {
                $pdfpage->line($x -4, $y+4, $x+4, $y-4);        //      backslash
		$pdfpage->ellipse($x, $y, 4, 4);		//      circle
	} else if ($tool == 1) {
                $pdfpage->line($x -4, $y, $x+4, $y);            //      dash
                $pdfpage->line($x , $y+4, $x  , $y-4);          //      bar
	} else if ($tool == 2) {
                $pdfpage->line($x -4, $y+4, $x+4, $y-4);        //      backslash
                $pdfpage->line($x -4, $y-4, $x+4, $y+4);        //      slash
	} else if ($tool == 3) {
                $pdfpage->line($x-4 , $y+4, $x-4  , $y-4);      //      L-bar
                $pdfpage->line($x+4 , $y+4, $x+4  , $y-4);      //      R-bar
                $pdfpage->line($x-4 , $y+4, $x+4  , $y+4);      //      ceiling
                $pdfpage->line($x-4 , $y-4, $x+4  , $y-4);      //      floor
                $pdfpage->line($x , $y, $x  , $y+4); 	    	//      uptick
	} else if ($tool == 4) {
                $pdfpage->line($x-4,  $y, $x,   $y-4);          //      L-trough
                $pdfpage->line($x,  $y-4, $x+4,   $y);          //      R-trough
                $pdfpage->line($x-4,  $y, $x, $y+4);            //      L-Roof
                $pdfpage->line($x,  $y+4, $x+4, $y);            //      R-Roof
                $pdfpage->line($x , $y, $x  , $y+4);            //      uptick
	} else if ($tool == 5) {
                $pdfpage->line($x -4, $y+4, $x+4, $y-4);        //      backslash
                $pdfpage->line($x -4, $y-4, $x+4, $y+4);        //      slash
                $pdfpage->line($x-4 , $y+4, $x+4  , $y+4);      //      ceiling
                $pdfpage->line($x-4 , $y-4, $x+4  , $y-4);      //      floor
	} else if ($tool == 6) {
                $pdfpage->line($x -4, $y+4, $x+4, $y-4);        //      backslash
                $pdfpage->line($x -4, $y-4, $x+4, $y+4);        //      slash
                $pdfpage->line($x-4 , $y+4, $x-4  , $y-4);      //      L-bar
                $pdfpage->line($x+4 , $y+4, $x+4  , $y-4);      //      R-bar
	} else if ($tool == 7) {
                $pdfpage->line($x-4,  $y, $x,   $y-4);          //      L-trough
                $pdfpage->line($x,  $y+4, $x+4, $y);            //      R-Roof
                $pdfpage->line($x -4, $y, $x+4, $y);            //      dash
                $pdfpage->line($x , $y+4, $x  , $y-4);          //      bar 
	} else if ($tool == 8) {
                $pdfpage->line($x -4, $y, $x+4, $y);            //      dash
                $pdfpage->line($x , $y+4, $x  , $y-4);          //      bar
                $pdfpage->line($x-4,  $y, $x, $y+4);            //      L-Roof
                $pdfpage->line($x,  $y-4, $x+4,   $y);          //      R-trough
	} else if ($tool == 9) {
                $pdfpage->line($x -4, $y+4, $x+4, $y-4);        //      backslash
                $pdfpage->line($x -4, $y-4, $x+4, $y+4);        //      slash
                $pdfpage->line($x , $y+4, $x  , $y-4);          //      bar
        } else if ($tool == 10) {
                $pdfpage->line($x -4, $y+4, $x+4, $y-4);        //      backslash
		$pdfpage->line($x -4, $y-4, $x+4, $y+4);	//	slash
                $pdfpage->line($x -4, $y, $x+4, $y);		//	dash
	} else if ($tool == 11) {
                $pdfpage->line($x,  $y-4, $x, $y);              //      downtick
                $pdfpage->line($x,  $y, $x+4, $y+4);            //      R-lift
                $pdfpage->line($x-4 , $y+4, $x+4  , $y+4);      //      ceiling
                $pdfpage->line($x,  $y, $x-4, $y+4);            //      L-lift
	} else if ($tool == 12) {
                $pdfpage->line($x , $y, $x  , $y+4);            //      uptick
                $pdfpage->line($x,  $y, $x+4, $y-4);            //      R-fall
                $pdfpage->line($x-4 , $y-4, $x+4  , $y-4);      //      floor
                $pdfpage->line($x,  $y, $x-4, $y-4);            //      L-fall
	} else if ($tool == 13) {
                $pdfpage->line($x-4, $y, $x, $y);               //      lefttick
                $pdfpage->line($x,  $y, $x+4, $y+4);            //      R-lift
                $pdfpage->line($x+4 , $y+4, $x+4  , $y-4);      //      R-bar
                $pdfpage->line($x,  $y, $x+4, $y-4);            //      R-fall
	} else if ($tool == 14) {
                $pdfpage->line($x, $y, $x+4, $y);               //      righttick
                $pdfpage->line($x,  $y, $x-4, $y-4);            //      L-fall
                $pdfpage->line($x-4 , $y+4, $x-4  , $y-4);      //      L-bar
                $pdfpage->line($x,  $y, $x-4, $y+4);            //      L-lift
	} else if ($tool == 15) {
                $pdfpage->line($x-4,  $y, $x+4, $y);		//	dash
                $pdfpage->line($x,  $y+4, $x, $y-4);            //      bar
		$pdfpage->ellipse($x, $y,  4, 4);		//      circle
	} else if ($tool == 16) {
                $pdfpage->line($x -4, $y, $x+4, $y);		//	dash
                $pdfpage->line($x , $y+4, $x  , $y-4);          //      bar
		$pdfpage->ellipse($x, $y, 4, 4);		//      circle
		$pdfpage->ellipse($x, $y, 2, 2);		//      small-circle
	} else if ($tool == 17) {
                $pdfpage->line($x -4, $y, $x+4, $y);		//	dash
                $pdfpage->line($x , $y+4, $x  , $y-4);          //      bar
                $pdfpage->line($x-4 , $y+4, $x-4  , $y-4);      //      L-bar
                $pdfpage->line($x+4 , $y+4, $x+4  , $y-4);      //      R-bar
                $pdfpage->line($x-4 , $y+4, $x+4  , $y+4);      //      ceiling
                $pdfpage->line($x-4 , $y-4, $x+4  , $y-4);      //      floor
	} else if ($tool == 18) {
                $pdfpage->line($x-4,       $y,   $x+4,     $y);	     //	     dash
                $pdfpage->line($x,       $y+4,     $x,   $y-4);      //      bar
                $pdfpage->line($x-4,     $y+4,   $x-4,   $y-4);      //      L-bar
                $pdfpage->line($x+4,     $y+4,   $x+4,   $y-4);      //      R-bar
                $pdfpage->line($x-4,     $y+4,   $x+4,   $y+4);      //      ceiling
                $pdfpage->line($x-4,     $y-4,   $x+4,   $y-4);      //      floor
                $pdfpage->line($x-2, $y+2, $x-2, $y-2);      //      L-bar
                $pdfpage->line($x+2, $y+2, $x+2, $y-2);      //      R-bar
                $pdfpage->line($x-2, $y+2, $x+2, $y+2);      //      ceiling
                $pdfpage->line($x-2, $y-2, $x+2, $y-2);      //      floor
	}
}

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

$PageSecurity = 2;

if ( isset ( $_GET['Fab'] ) ) {
	$FabName = $_GET['Fab'] ;
	$FabVersion = $_GET['FabVersion'] ;
} else {
	if ( isset ( $_POST['Fab'] ) ) {
		$FabName = $_POST['Fab'] ;
		$FabVersion = $_POST['FabVersion'] ;
	}
}
if ( isset ($FabName) && isset($FabVersion) && ($FabVersion!="")  ) {
	include('config.php');
	include('includes/ConnectDB.inc');
	include('includes/PDFStarter_ros.inc');
	include('includes/DateFunctions.inc');
	$FontSize=11;
	$pdf->addinfo('Title',_('DRLCSV Drawing'));
	$pdf->addinfo('Subject',_('Fab Drawing'));
	$PageNumber=0;
	$line_height=10;
	include ('includes/PDFFabOrders.inc');
	$baseline = $YPos + 5;
	$leftmarg{'drills'} =  33; // $Left_Margin;
	$leftmarg{'fab'}    = 270; // $Left_Margin + 300;
	/* *** FIRST ***	WRITE-OUT THE DRILL & FAB TABLES	***  */
	foreach(array('drills','fab') as $whichTable)	{
		$handle = curl_init("http://svn.rungie.com/svn/gumstix-hardware/Production/PCB/PC$FabName-R$FabVersion/$FabName.$whichTable.csv");
		curl_setopt($handle, CURLOPT_USERPWD, 'weberp:saywhat');
		curl_setopt($handle, CURLOPT_HEADER, false);
		curl_setopt($handle, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($handle, CURLOPT_USERAGENT, 'WebERP');
		curl_setopt($handle, CURLOPT_FAILONERROR, true);
		$DRLCSV = curl_exec($handle);
		curl_close($handle);
		if ( curl_errno($handle) ) {
			$YPos -= $line_height*2;
			$LeftOvers = $pdf->addTextWrap($Left_Margin+ 50,$YPos- ($multiplier * $pictureHeight), 350, $FontSize, "NO $FabName.$whichImage.png",'left');
		} else {
			$DRLCSV = csv_parse($DRLCSV);
			for ($row=1; $row < count($DRLCSV); $row++) {
				$BOMList = $DRLCSV[$row];
				$YPos = $baseline - $row * ($line_height + 1);
				$FontSize=8;
				if ( ($whichTable=='drills')&& ($BOMList[1]!="") ) {
					drawdrillicon($pdf,$BOMList[0],$leftmarg{'drills'}+  20, $YPos+$line_height/3 );
					$LeftOvers = $pdf->addTextWrap($leftmarg{'drills'}+  30, $YPos, 20, $FontSize, $BOMList[1],'left');
					$LeftOvers = $pdf->addTextWrap($leftmarg{'drills'}+  50, $YPos,150, $FontSize, $BOMList[2],'left');
					$LeftOvers = $pdf->addTextWrap($leftmarg{'drills'}+ 150, $YPos,250, $FontSize, $BOMList[3],'left');
					$LeftOvers = $pdf->addTextWrap($leftmarg{'drills'}+ 180, $YPos,300, $FontSize, $BOMList[4],'left');
				} else {
					$LeftOvers = $pdf->addTextWrap($leftmarg{'fab'},  $YPos, 50, $FontSize, $BOMList[0],'left');
					$LeftOvers = $pdf->addTextWrap($leftmarg{'fab'}+10,  $YPos,350, $FontSize, $BOMList[1],'left');
				}
			}
		}
	}
	/* *** SECOND ***	DRAW-OUT THE DRILL & FAB DRAWINGS	***  */
	$YPos = 400;
	$pictureHeight = ($YPos - $Bottom_Margin )/ 2 ;
	$multiplier = 0;
	foreach(array('drills','fab') as $whichImage)	{
		$handle = curl_init("http://svn.rungie.com/svn/gumstix-hardware/Production/PCB/PC$FabName-R$FabVersion/$FabName.$whichImage.png");
		curl_setopt($handle, CURLOPT_USERPWD, 'weberp:saywhat');
		curl_setopt($handle, CURLOPT_HEADER, false);
		curl_setopt($handle, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($handle, CURLOPT_USERAGENT, 'WebERP');
		curl_setopt($handle, CURLOPT_BINARYTRANSFER, true);
		$whichImageImage = curl_exec($handle); // set topImage or botImage
		if(!curl_errno($handle) && curl_getinfo($handle, CURLINFO_CONTENT_TYPE)=='image/png')		{
			$multiplier++;
			$whichImageImage = imagecreatefromstring($whichImageImage);
        		$topX = imagesx($whichImageImage);
		        $topY = imagesy($whichImageImage);
		        $scale = $topX / $topY * $pictureHeight;

		        if ( $scale > 550 ) {
                               $pdf->addImage($whichImageImage, $Left_Margin, $YPos - ($multiplier * $pictureHeight), 550 );
        		} else {
		               $pdf->addImage($whichImageImage, $Left_Margin, $YPos - ($multiplier * $pictureHeight), 0, $pictureHeight - 2);
			}
		}						
		curl_close($handle);
	}
	$buf = $pdf->output();
	$len = strlen($buf);
	if (isset($_POST['SaveAs']) && ($_POST['SaveAs'] != "") ) {
                if (is_dir($build_orders_dir . '/' . $_POST['SaveAs'])===false) {
		        mkdir ( $build_orders_dir . '/' . $_POST['SaveAs'] . '/' );
		}
	        $pdfcode = $buf;
	        $fp = fopen( $build_orders_dir . '/' . $_POST['SaveAs'] . '/' . $_POST['SaveAs'] . '.pdf','wb');
	        fwrite ($fp, $pdfcode);
	        fclose ($fp);
	}
	header('Content-type: application/pdf');
	header("Content-Length: $len");
	header('Content-Disposition: inline; filename='.$FabName.'-R'.$FabVersion.'.pdf');
	header('Expires: 0');
	header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
	header('Pragma: public');
	$pdf->stream();
} else {
	include('includes/session.inc');
	$title = _('Fab Drawing');
	include('includes/header.inc');
	include("includes/WO_ui_input.inc");
	include("includes/WO_Includes.inc");
	echo "<FORM METHOD='post' action=" . $_SERVER['PHP_SELF'] . ">";
	echo "<CENTER><TABLE>";
	echo "<TR>";
	TextInput_TableCells( "Fab (eg 'B00019')", 'Fab', $_POST['Fab'], 9, 9);
	TextInput_TableCells( "Version (eg 1142)", 'FabVersion', $_POST['FabVersion'], 9, 9);
	echo "<TD>";
	Input_Submit("Display","Display");
	echo "</TD></TR>";
	echo "</TABLE></FORM>";
	include('includes/footer.inc');
}
?>

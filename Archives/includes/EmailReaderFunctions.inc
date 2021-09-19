<?php

function InitializeRules() {

	$rule_list = array();

	$key_words_3  = array ( 'STOP'=>'non-existent', 'TABLE'=>'Idx', 'FOOTER'=>'BOX' );
	$header_words_3  = array( 'INVOICE'=>'Invoice', 'TERMS' => 'Net', 'DATE'=> 'Net', 'PONUMBER'=>'SAN', 'SALESORDER'=>'PORTOLA');
	$header_location_3  = array( 'INVOICE'=>'2', 'TERMS' => '1', 'DATE'=> '3', 'PONUMBER'=>'100', 'SALESORDER'=> '101' );
	$table_words_3  = array( 0=>'LINE', 1=>'BOX', 2=>'ORDERED', 3=>'CANCELLED', 4=>'SHIPPED', 5=>'DESCRIPTION', 6=>'BACKORDERED', 7=>'EACH', 8=>'TOTAL' );
	$footer_words_3  = array('CHECKSUM'=>'INVOICED', );
	$footer_location_3  = array('CHECKSUM'=>'1');

        $key_words_11  = array ( 'STOP'=>'REGULATIONS', 'TABLE'=>'ITEM', 'FOOTER'=>'TRACK' );
        $header_words_11  = array( 'INVOICE'=>'SPECIAL', 'TERMS' => 'Net', 'DATE'=> 'SPECIAL', 'PONUMBER'=>'VIA', 'SALESORDER'=>'OUR');
        $header_location_11  = array( 'INVOICE'=>'100', 'TERMS' => '1', 'DATE'=> '102', 'PONUMBER'=>'103', 'SALESORDER'=> '102' );
        $table_words_11  = array( 0=>'LINE', 1=>'BOX', 2=>'ORDERED', 3=>'CANCELLED', 5=>'SHIPPED', 2=>'DESCRIPTION', 6=>'BACKORDERED', 7=>'EACH', 8=>'TOTAL' );
        $footer_words_11  = array('CHECKSUM'=>'Pay', );
        $footer_location_11  = array('CHECKSUM'=>'3');

	$key_words_14 = array ( 'STOP'=>'non-existent', 'TABLE'=>'Order', 'FOOTER'=>'Subtotal' );
	$header_words_14 = array( );//'INVOICE'=>'Order', 'DATE'=>'Order', 'DESCRIPTION'=>'Order' ); 
	$header_location_14 = array( );//'INVOICE'=>'101', 'DATE'=>'104', 'DESCRIPTION'=>'109' );
	$table_words_14 = array( 0=>'DIV', 1=>'INVOICE', 2=>'SALESORDER', 3=>'LINE', 4=>'DATE', 5=>'PONUMBER', 6=>'DESCRIPTION', 7=>'SHIPPED', 8=>'SHIP-TO', 9=>'WILDCARD', 10=>'TOTAL' );
        $wild_column_14 = array( 'N'=>11, 'W'=> 9 );
	$footer_words_14 = array('SHIPPING' => 'Freight', 'CHECKSUM'=>'Grand');
	$footer_location_14 = array('SHIPPING' => '1', 'CHECKSUM'=>'2');

	$key_words_19 = array ('STOP'=>'16', 'TABLE'=>'non-existent','FOOTER'=>'non-existent' );
	$header_words_19  = array( 'INVOICE'=>'-MATCH-', 'TERMS' => 'Net', 'DATE'=> 'Net', 'PONUMBER'=>'Sales', 'TOTAL'=>'Due');
	$header_location_19  = array( 'INVOICE'=>'/0007Y284V/', 'TERMS' => '1', 'DATE'=> '3', 'PONUMBER'=>'100', 'TOTAL'=> '4' );

        $key_words_31  = array ( 'STOP'=>'non-existent', 'TABLE'=>'LI.', 'FOOTER'=>'HANDLING' );
        $header_words_31  = array( 'INVOICE'=>'MCCARTHY', 'TERMS' => 'Net', 'DATE'=> 'CONTROL', 'PONUMBER'=>'CONTROL', 'SALESORDER'=>'OUR');
        $header_location_31  = array( 'INVOICE'=>'100', 'TERMS' => '1', 'DATE'=> '101', 'PONUMBER'=>'102', 'SALESORDER'=> '102' );
        $table_words_31  = array( 0=>'LINE', /* 1=>'BOX',*/  1=>'SHIPPED', 2=>'DESCRIPTION', 4=>'EACH', 5=>'TOTAL' );
        $footer_words_31  = array('SHIPPING' => 'HANDLING', 'CHECKSUM'=>'PASADENA', );
        $footer_location_31  = array('SHIPPING' => 2, 'CHECKSUM'=>'100');

	$key_words_44 = array ( 'STOP'=>'non-existent', 'TABLE'=>'ITEM', 'FOOTER'=>'Thank' );
	$header_words_44 = array( 'INVOICE'=>'(408)', 'TERMS' => 'TERMS', 'DATE'=> '(408)', 'PONUMBER'=>'TERMS' );
	$header_location_44 = array( 'INVOICE'=>'101', 'TERMS' => '102', 'DATE'=> '100', 'PONUMBER'=>'100' );
	$table_words_44 = array( 0=>'ITEM', 1=>'DESCRIPTION', 2=>'SHIPPER', 3=>'SHIPPED', 4=>'EACH', 5=>'TOTAL' );
	$wild_column_44 = array( 'N'=>6, 'W'=> 1 );
	$footer_words_44 = array('CHECKSUM'=>'Total', 'TAX'=>'Tax', 'SHIPPING'=> 'ID');
	$footer_location_44 = array('CHECKSUM'=>'1', 'TAX'=>'1', 'SHIPPING'=> '1');

        $key_words_178= array ( 'STOP'=>'non-existent', 'TABLE'=>'Qua...', 'FOOTER'=>'Total' );
        $header_words_178= array( 'INVOICE'=>'Richardson,', 'TERMS' => 'P.O.', 'DATE'=> 'Richardson,', 'PONUMBER'=>'Number' );
        $header_location_178= array( 'INVOICE'=>'101', 'TERMS' => '102', 'DATE'=> '100', 'PONUMBER'=>'100' );
        $table_words_178= array( 1=>'ITEM', 2=>'DESCRIPTION', 2=>'SHIPPER', 0=>'SHIPPED', 4=>'EACH', 5=>'TOTAL' );
        $wild_column_178= array( 'N'=>6, 'W'=> 1 );
        $footer_words_178= array('CHECKSUM'=>'Total', 'TAX'=>'Tax', 'SHIPPING'=> 'ID');
        $footer_location_178= array('CHECKSUM'=>'1', 'TAX'=>'1', 'SHIPPING'=> '1');

        $key_words_61 = array ( 'STOP'=>'non-existent', 'TABLE'=>'Quantity', 'FOOTER'=>'File' );
        $header_words_61 = array( 'INVOICE'=>'DATE', 'DATE'=> 'DATE', 'PONUMBER'=>'P.O.' );
        $header_location_61 = array( 'INVOICE'=>'101', 'DATE'=> '100', 'PONUMBER'=>'100' );
        $table_words_61 = array( 0=>'SHIPPED', 1=>'ITEM', 2=>'DESCRIPTION', 3=>'EACH', 4=>'TOTAL');
	$wild_column_61 = array( 'N'=>4, 'W'=> 1 );
        $footer_words_61 = array('CHECKSUM'=>'TOTAL', 'TRACKING'=> 'Tracking#:');
        $footer_location_61 = array('CHECKSUM'=>'1', 'TRACKING'=> '1');

        $key_words_108 = array ('STOP'=>'Thank', 'TABLE'=>'non-existent','FOOTER'=>'non-existent' );
        $header_words_108  = array( 'INVOICE'=>'Account', 'TERMS' => 'Net', 'DATE'=> 'Net', 'PONUMBER'=>'Sales', 'TOTAL'=>'Total');
        $header_location_108  = array( 'INVOICE'=>'102', 'TERMS' => '1', 'DATE'=> '3', 'PONUMBER'=>'100', 'TOTAL'=> '3' );

        $key_words_162 = array ('STOP'=>'BILL', 'TABLE'=>'non-existent','FOOTER'=>'non-existent' );
        $header_words_162  = array( 'INVOICE'=>'INVOICE', 'TERMS' => 'TERMS', 'DATE'=> 'DATE:', 'PONUMBER'=>'Sales', 'TOTAL'=>'AMOUNT');
        $header_location_162  = array( 'INVOICE'=>'2', 'TERMS' => '2', 'DATE'=> '1', 'TOTAL'=> '2' );

        $key_words_201 = array ('STOP'=>'BILL', 'TABLE'=>'Item','FOOTER'=>'Total' );
        $header_words_201  = array( 'INVOICE'=>'Due', 'TERMS' => 'TERMS', 'DATE'=> 'Due', 'PONUMBER'=>'Sales', 'TOTAL'=>'AMOUNT');
        $header_location_201  = array( 'INVOICE'=>'101', 'TERMS' => '2', 'DATE'=> '100', 'TOTAL'=> '2' );
        $table_words_201= array( 0=>'CUSTREF', 1=>'ITEM', 2=>'DESCRIPTION', 3=>'SHIPPED', 4=>'EACH', 5=>'TOTAL' );
        $wild_column_201= array( 'N'=>6, 'W'=> 2 );
        $footer_words_201= array('CHECKSUM'=>'Total', 'TAX'=>'Tax', 'SHIPPING'=> 'ID');
        $footer_location_201= array('CHECKSUM'=>'1', 'TAX'=>'1', 'SHIPPING'=> '1');

	$defined_vendors = array ( '3', '11', '14', '19', '31', '44', '61', '108', '162', '178', '201'  );
	foreach ( $defined_vendors as $v_id ) {
		$rule_list[ $v_id ] = array (	'key_words'	  => ${'key_words_' . $v_id},
						'header_words'	  => ${'header_words_' . $v_id},
						'header_location' => ${'header_location_' . $v_id},
						'table_words'	  =>${'table_words_' . $v_id},
						'wild_column'	  =>${'wild_column_'. $v_id},
						'footer_words'	  =>${'footer_words_' . $v_id},
						'footer_location' =>${'footer_location_' . $v_id}
						);
	}

	$rule_list['3']['SUBJECTMATCH']= 'Digi-Key Invoice';
	$rule_list['3']['SENDERMATCH'] = 'digikey.com';
	$rule_list['3']['FILETYPE']='pdf';

        $rule_list['11']['SUBJECTMATCH']= 'Invoice';
        $rule_list['11']['SENDERMATCH'] = 'NUHORIZONS.COM';
        $rule_list['11']['FILETYPE']='pdf';

	$rule_list['14']['SUBJECTMATCH']= 'INV';
	$rule_list['14']['SENDERMATCH'] = 'innerstep.com';
	$rule_list['14']['FILETYPE']='pdf';

	$rule_list['19']['SUBJECTMATCH']= 'UPS Billing';
	$rule_list['19']['SENDERMATCH'] = 'ups.com';
	$rule_list['19']['FILETYPE'] = 'BODY';
        $rule_list['19']['ALLOW_NO_COUNT']='TRUE';

        $rule_list['31']['SUBJECTMATCH']= 'Avnet';
        $rule_list['31']['SENDERMATCH'] = 'Avnet.com';
        $rule_list['31']['FILETYPE']='pdf';

	$rule_list['44']['SUBJECTMATCH']= 'Invoice';
	$rule_list['44']['SENDERMATCH'] = 'bestekmfg.com';
        $rule_list['44']['FILETYPE']='pdf';

        $rule_list['61']['SUBJECTMATCH']= 'AC-Invoice';
        $rule_list['61']['SENDERMATCH'] = '4pcb.com';
        $rule_list['61']['FILETYPE'] = 'BODY';

        $rule_list['178']['SUBJECTMATCH']= 'Invoice';
        $rule_list['178']['SENDERMATCH'] = 'circuitco.com';
        $rule_list['178']['FILETYPE']='pdf';

        $rule_list['162']['SUBJECTMATCH']= 'Invoice from Sakoman Incorporated';
        $rule_list['162']['SENDERMATCH'] = 'sakoman.com';
        $rule_list['162']['FILETYPE']='BODY';
	$rule_list['162']['ALLOW_NO_COUNT']='TRUE';

        $rule_list['201']['SUBJECTMATCH']= 'Invoice';
        $rule_list['201']['SENDERMATCH'] = 'labtestcert.com';
        $rule_list['201']['FILETYPE']='pdf';

	return $rule_list;
}

function ParseLines( $source_lines, $rules ) {
	$header_return	 = array();
	$current_line	= array();
	$lines_return	 = array();

	$add_these_as_lines	= array( 'TAX', 'SHIPPING' );

        $state = 'HEADER';
	$line_number = 0;

	$key_words	 = $rules['key_words'];
	$header_words	 = $rules['header_words'];
	$header_location = $rules['header_location'];
	$table_words	 = $rules['table_words'];
	$wild_column	 = $rules['wild_column'];
	$footer_words	 = $rules['footer_words'];
	$footer_location = $rules['footer_location'];
	
	foreach ( $source_lines as $psdata ) {
		$words	= preg_split( "/[\s]+/", trim($psdata) );
		if ( (count ($words) > 0 ) && ($words[0][0] != '-') )switch ( $state ) {
			case 'STOP'   :		break;
			case 'HEADER' :		if( $words[0] == $key_words['STOP'] ) {
							$state = 'STOP';
						}
						
						if ( $words[0] == $key_words['TABLE'] ) {
							$state = 'TABLE';
//							echo 'Entering TABLE<BR>';
						} else {
							if ( (count($trigger_list) > 0) && ( trim($words[0]) != '') ) {
								foreach ( $trigger_list as $TRIGGER ) {
									$header_return[$TRIGGER] = $words[  $header_location[ $TRIGGER ] - 100 ];
								}
								$trigger_list = array();
							}
							foreach ( $words as $i => $word ) {
								foreach ( $header_words as $header_word => $header_value ) {
//									echo $header_value . ' ' . trim($word) . '<BR>';
									if ( ($header_value == '-MATCH-') && preg_match( $header_location[$header_word], trim($word) )) {
//										echo $header_value . $header_word . trim($word) . '<BR>';
										if ( $header_return[$header_word]=='' ) {
											$header_return[$header_word] =  $word;
											echo $header_word . ': '.$word . '<BR>';
										}
									} else 	if ( $header_value == $word && ($header_return[$header_word]=='' ) && $header_location[ $header_word] > 99 ) {
										$trigger_list[] = $header_word;
									} else if ( $header_value == $word && ($header_return[$header_word]=='' ) && $header_location[ $header_word] >=0 ) {
										$header_return[$header_word] =  ereg_replace( "[\'\"\$\,]","", $words[ $i + $header_location[ $header_word] ]);

									}
								}
							}
						}
						break; 
			case 'TABLE':		if ( $words[0] == $key_words['FOOTER'] ) {
                                                        $state = 'FOOTER';
							if ( $key_words['FOOTER'] == $footer_words['SHIPPING'] ) {
								$header_return[ 'SHIPPING' ] = $words[ $footer_location['SHIPPING']] ;
							}
                                                } else {
							if ( $table_words[0] == 'LINE' ) {
								$count_diff = 9-count( $words );
								if ( is_numeric($words[0]) && ($words[0]<50)) {        //       == $line_number + 1) {

//								if ( $words[0] == $line_number + 1) {
									$line_number++;	
									foreach ( $words as $i => $word ) {
										if ($i<6 || $count_diff==0) {
											$current_line[ $table_words[$i] ] = $word;
										} else {
											$current_line[ $table_words[$i+1] ] = $word;
										}
									}
									$lines_return[ $line_number ] = $current_line;
								} else {
									if ( $words[0]=='CUST') {
										$lines_return[$line_number]['CUSTREF'] = $words[ count( $words ) - 1];
									} else {
										if ( count( $words )==1 && $lines_return[$line_number]['CUSTREF'] = '' ) {
											$lines_return[$line_number]['CUSTREF'] = $words[0];
										}
									}
								}
							} else {
								$count_diff = count( $words ) - $wild_column['N'];
								$line_number++;
								$current_line = array();
                                                                foreach ( $words as $i => $word ) {
									$concatting = false;
                                                                        if ( $i<$wild_column['W'])  {
                                                                               $field_id = $table_words[$i];
                                                                        } else if ( $i <= $wild_column['W'] + $count_diff) {
										$field_id = $table_words[ $wild_column['W']];
										$concatting = true;
                                                                        } else {
										$field_id = $table_words[$i - $count_diff];
									}
									if ( $concatting ) {
										$word = ereg_replace( "[\'\"\$\,]","",$word);
										$current_line[ $field_id ] .= ' ' . $word;
									} else {
										$word = ereg_replace( "[\$\,]","",$word);
										$current_line[ $field_id ] = $word;
       									}
	                                                         }
                                                                $lines_return[ $line_number ] = $current_line;
								if (  $table_words[0] == 'DIV' ) $state = 'FOOTER';
							}
						}
						break;
			case 'FOOTER':		if ( count($trigger_list) > 0  ) {
							foreach ( $trigger_list as $TRIGGER ) {
								$header_return[$TRIGGER] = $words[  $footer_location[ $TRIGGER ] - 100 ];
							}
						}
						$trigger_list = array();
						foreach ( $words as $i => $word ) {
							foreach ( $footer_words as $footer_word => $footer_value ) {
								if ( $footer_value == $word && !isset( $$footer_word) && $footer_location[ $footer_word] > 99 ) {
									$trigger_list[] = $footer_word;
								} else if ( $footer_value == $word && !isset( $$footer_word) && $footer_location[ $footer_word] >=0 ) {
									$header_return[$footer_word] =  ereg_replace( "[\'\"\$\,]|UPS\#","",$words[ $i + $footer_location[ $footer_word] ]);
								}
							}
						}	
						break;
		}
	}

	foreach ( $add_these_as_lines as $tk ) {
		if ( isset( $header_return[$tk]) ) {
			$line_number++;
			$current_line = array();
			$current_line['DESCRIPTION'] = $tk;
			$current_line['TOTAL']      = $header_return[$tk];
			$lines_return[ $line_number ] = $current_line;
		}
	}

	if ( ( count ( $lines_return ) == 0 ) && isset( $header_return['TOTAL'] ) ) {
		$one_liner['TOTAL'] = $header_return['TOTAL'];
		$lines_return[]	= $one_liner;
	}

	if ( !isset( $header_return['INVOICE']) ) {
		$header_return['INVOICE'] = $lines_return[1]['INVOICE'] ;
	}

	$header_return['Lines'] = $lines_return;
	return $header_return;
}

function GetLinesFromBody( $msg_body ) {
	$the_lines = array();
	$the_lines = split( "\r", $msg_body );
	return $the_lines;
}

function GetLinesFromPDF( $file_name, $vendor_id  ) {
	$invoice_dir = 'reports/invoices/' . $vendor_id . '/';
	$file_name_esc = escapeshellcmd( $file_name );
	if (is_file(  $invoice_dir . $file_name_esc  ) ) {
		$outpath =  escapeshellcmd( $invoice_dir . preg_replace("/\.pdf$/", "", $file_name_esc ).".txt" );
//		echo 'Attempting: ' . $outpath . '<br>';
//		echo   "/usr/bin/pdftotext -layout '". ($invoice_dir . $file_name_esc ) . "' '" . $outpath ."'" . '<br>'; 
		system("/usr/bin/pdftotext -layout '". ($invoice_dir . $file_name_esc ) . "' '" . $outpath ."'" , $ret);
		if ($ret == 127) {
			echo "Could not find pdftotext tool.";
			return false;
	        }
		if ($ret == 1) {
			echo "Could not find pdf file.";
			return false;
		}
	} else {
		echo 'file not found' . ': ' . $invoice_dir  . $file_name_esc . '<br>';
		return false;
	}
	if ( is_file($outpath) ) {
	        $fn = fopen( $outpath, 'r' );
		$all_data = file_get_contents ( $outpath );
		$lines = split( "\n", $all_data);
//		unlink($outpath);
		return $lines;
	} else {
		echo 'file not processed' . $outpath . '<br>';
		return false;
	}
}

function EchoInvoice( $the_invoice ) {
	echo '<table border=1>';
	$top_keys = array ( 'VENDOR', 'INVOICE', 'DATE', 'PONUMBER' );
	$line_keys = array ( 'LINE', 'DESCRIPTION', 'CUSTREF', 'EACH', 'SHIPPED', 'TOTAL');
	echo '<tr>';
	foreach ( $top_keys as $tk ) {
                 echo '<td>' . $tk .'</td>';
        }
	foreach ( $line_keys as $tk ) {
		 echo '<td>' . $tk .'</td>';
	}
	echo '</tr>';
	echo '<tr>';
	foreach ( $top_keys as $tk ) {
//                 echo '<td>' . $the_invoice[$tk] .'</td>';
        }
	echo '</tr>';
	$stuff_line = 0;
	foreach ($the_invoice['Lines'] as $the_line ) {
		 $stuff_line++;
		echo '<tr>';
		foreach ( $top_keys as $tk ) {
			if ( !isset( $the_line[$tk] ) ) {
				$the_line[$tk] = $the_invoice[$tk];
			}
			echo '<td>' . $the_line[$tk] . '</td>';
		}
		if ( $the_line['LINE'] =='' ) {
			$the_line['LINE'] = $stuff_line;
		}
		foreach ( $line_keys as $tk ) {
        	        echo '<td>' . $the_line[$tk] . '</td>';
        	}
		echo '</tr>';
	}
	echo '<tr>';
	echo '<td>CHECKSUM</td><td>' . $the_invoice['CHECKSUM'] . '</td>';
	echo '<td>TRACKING</td><td>' . $the_invoice['TRACKING'] . '</td>';
	echo '</tr>';
	echo '</table>';
}


function ReiterateInvoice( $db ) {
	if ( !isset( $_POST['VENDOR'] ) ||  ( $_POST['VENDOR'] =='' ) ) {
		return -1;
	}
	echo '<center><table width=75%  border=1 BGCOLOR=#AAFFFF>';
	$top_keys = array ( 'VENDOR', 'INVOICE', 'DATE', 'PONUMBER' );
	$line_keys = array ( 'LINE', 'DESCRIPTION', 'CUSTREF', 'EACH', 'SHIPPED', 'TOTAL');
	$foot_keys = array( 'CHECKSUM', 'TRACKING');

	echo '<tr BGCOLOR=#AAAAAA>';
	foreach ( $top_keys as $tk ) {
                 echo '<td>' . $tk .'</td>';
        }
	foreach ( $line_keys as $tk ) {
		 echo '<td>' . $tk .'</td>';
	}
	echo '</tr>';
	foreach ( $_POST['LINE'] as $i => $line_no ) {
		echo '<tr>';
		foreach ( $top_keys as $tk ) {
			echo '<td>' . $_POST[$tk][$i] . '</td>';
		}
		foreach ( $line_keys as $tk ) {
        	        echo '<td>' . $_POST[$tk][$i] . '</td>';
		}
		echo '</tr>';
		if ((  $_POST['TOTAL'][$i] != '' ) && ( is_numeric($_POST['SHIPPED'][$i]) || ($rule_list[$_POST['VENDOR'][$i]]['ALLOW_NO_COUNT']='TRUE') ) ) {
			echo '<tr><td colspan=10>';
			$SupplierID	= $_POST['VENDOR'][$i];
			$POID		= $_POST['PONUMBER'][$i];
			$InvoiceID	= $_POST['INVOICE'][$i];
			$Line		= $line_no;
			$StockID	= $_POST['CUSTREF'][$i];
			$Description	= $_POST['DESCRIPTION'][$i];
			$Qty		= $_POST['SHIPPED'][$i];
			$Price		= ereg_replace( "[\'\"\$\,]","",  $_POST['EACH'][$i]   );
			$Amount		= ereg_replace( "[\'\"\$\,]","",  $_POST['TOTAL'][$i]  );
			$d		= Date("Y-m-d", strtotime($_POST['DATE'][$i]) );
			$sql = "INSERT INTO SuppInvoiceDetails (SupplierID, PONumber,SuppReference,LineNo,StockID,Description,Ordered,GRNNo,Invoicing,Price,Total,Approved,InvoiceDate)
				VALUES ( '$SupplierID', '$POID','$InvoiceID','$Line','$StockID','$Description','$Ordered','$Shipper','$Qty','$Price','$Amount','0','$d');";
			echo $sql;
			$ret = DB_query( $sql, $db );	
			echo '</td></tr>';
		}
	}
        foreach ( $foot_keys as $tk ) {
                 echo '<tr><td colspan=' . (int)(count($line_keys) + count($top_keys) -2 ) . '></td><td>' . $tk . '</td><td>' . $_POST[$tk] .'</td></tr>';
        }
	if ( $_POST['CHECKSUM'] > 0) {
                $sql = "INSERT INTO SuppInvoiceDetails (SupplierID, PONumber,SuppReference,LineNo,StockID,Description,Ordered,GRNNo,Invoicing,Price,Total,Approved,InvoiceDate)
                        VALUES ( '$SupplierID', '$POID','$InvoiceID','0','','CHECKSUM','0','0','','0','" . $_POST['CHECKSUM']    ."','0','$d');";
//                echo $sql;
                $ret = DB_query( $sql, $db );
	}		
	echo '</table>';
	echo '</center>';
}

function GetBlanketPO( $v_id, $db ) {
	$sql = 'SELECT * FROM PurchOrderDetails LEFT JOIN PurchOrders ON PurchOrders.OrderNo = PurchOrderDetails.OrderNo
		WHERE PurchOrders.SupplierNo="' . $v_id . '" AND PurchOrderDetails.ItemDescription="BLANKET ORDER" AND Completed=0';
	$ret = DB_query( $sql, $db );
	if ($row = DB_fetch_array( $ret) ) {
		return $row['OrderNo'];
	} else {
		return null;
	}
}

function EchoInvoiceApproval( $the_invoice, $db ) {
	$sql =	' SELECT COUNT(*) FROM SuppInvoiceDetails WHERE SupplierID="' . $the_invoice['VENDOR'] .'"' .
		' AND SuppReference = "' . $the_invoice['INVOICE'] . '"';
	$ret	= DB_fetch_row( DB_query ( $sql, $db ) );
	if ( $ret[0] !=0) {
//		echo 'ALREADY ENTERED: ' . $sql . '<br>';
		return -1;
	}
//echo $sql;
	echo '<br>';
//	print_r( $the_invoice );
	echo '<form  ACTION="'. $_SERVER['PHP_SELF'] . "?" . SID . '" METHOD=POST>';
	echo '<br><br><center><table width=75% border=1>';
	$top_keys = array ( 'VENDOR', 'INVOICE', 'DATE', 'PONUMBER' );
	$line_keys = array ( 'LINE', 'DESCRIPTION', 'CUSTREF', 'EACH', 'SHIPPED', 'TOTAL');
	$foot_keys = array( 'CHECKSUM', 'TRACKING', 'FILENAME');

	echo '<tr>';
	foreach ( $top_keys as $tk ) {
                 echo '<td>' . $tk .'</td>';
        }
	foreach ( $line_keys as $tk ) {
		 echo '<td>' . $tk .'</td>';
	}
	echo '</tr>';

	echo '<tr>';
	foreach ( $top_keys as $tk ) {
//                echo '<td>' . $the_invoice[$tk] .'</td>';
//		Input_Hidden( $tk,  $the_invoice[$tk]);
        }
	echo '</tr>';

	if ( !isset($the_invoice['PONUMBER']) || ($the_invoice['PONUMBER']=='') ) {
		$the_invoice['PONUMBER']=GetBlanketPO( $the_invoice['VENDOR'], $db );
	}


	$stuff_line = 0;
	foreach ($the_invoice['Lines'] as $the_line ) {
		 $stuff_line++;
		echo '<tr>';
		foreach ( $top_keys as $tk ) {
			if ( !isset( $the_line[$tk] ) ) {
				$the_line[$tk] = $the_invoice[$tk];
			}
			echo '<td>' . $the_line[$tk] . '</td>';
			Input_Hidden( $tk . '[]',$the_line[$tk] );	
		}
		if ( $the_line['LINE'] =='' ) {
			$the_line['LINE'] = $stuff_line;
		}
		if (( $the_line['EACH'] =='') && ($the_line['SHIPPED'] > 0) ) {
			 $the_line['EACH'] =  $the_line['TOTAL'] / $the_line['SHIPPED'];
		}
		foreach ( $line_keys as $tk ) {
        	        echo '<td>' . $the_line[$tk] . '</td>';
        		Input_Hidden( $tk . '[]', $the_line[$tk]);
		}
		echo '</tr>';
	}
        foreach ( $foot_keys as $tk ) {
                 echo '<tr><td colspan=' . (int)(count($line_keys) + count($top_keys)-2 ) . '></td><td>' . $tk . '</td><td>' . $the_invoice[$tk] .'</td></tr>';
                Input_Hidden( $tk,  $the_invoice[$tk]);
        }
	echo '<tr><td colspan=' . (int)(count($line_keys) + count($top_keys) ) . '><center>';
	Input_Submit( 'OK', 'OK' );
	echo '</center></td></tr>';
	echo '</table></center>';
	echo '</form>';
	echo '<br><br>';
	return 0;
}
?>

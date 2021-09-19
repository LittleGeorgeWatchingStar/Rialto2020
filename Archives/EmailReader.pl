#!/usr/bin/perl  -T -w

use lib '/sw/lib/perl5';
use lib '/sw/lib/perl5/darwin';
use DBI;
use strict;
use Date::Calc;
use Mail::IMAPClient;
use MIME::Parser;
use File::MkTemp;
use File::Slurp;
use Data::Dumper;
use IO::String;
use Shell "xlhtml";
my $weberpdb;

my %MONTHS = (	 JAN=>'01',  FEB=>'02',  MAR=>'03',  APR=>'04',  MAY=>'05',  JUN=>'06',
		 JUL=>'07',  AUG=>'08',  SEP=>'09',  OCT=>'10',  NOV=>'11',  DEC=>'12' );

my $p = new MIME::Parser;
$p->output_to_core(1);
my $my_page;
my @size;
my $AttachmentName = "";
my $limit;
my $FileType;

my %OtherSubjectKeys = (
        	DFM => '^Your FreeDFM.com results for your design B(\d\d\d\d\d)\.R(\d\d\d\d)\.zip'
		);
              
		  		
my %InvoiceMessageKey = (
		  1 => 'ARROW ELECTRONICS, INC. :  Invoice',
		  3 => "Digi-Key Invoice",
		 14 => "Gumstix Invoice ",
		 44 => "BESTEK",
#		 61 => "AC-Invoice",
	);

sub ParseInvoiceCSV {
	my $string = shift;
	my ( $lineNo, $SuppID, $POID, $description, $qty, $price, $total, $InvoiceID, $d, $sql );
	my $expr = ',(?=(?:[^\"]*\"[^\"]*\")*(?![^\"]*\"))';
	
	print STDERR 'CSV>  ' . $string;
	
	my @words = split( $expr , $string );
	foreach my $thisWord ( @words ) {
		$thisWord =~ s/"//g;
	}
        ( $lineNo, $POID, $description, $SuppID, $d, $qty, $price, $total, $InvoiceID ) = @words[0,1,2,3,5,7,9,10,11];

	if (!defined $SuppID) {
		return -1;
	}

	if (($price==0) && ($qty!=0) )  {
		$price = $total / $qty ;
	}

	if ($SuppID == 44) {
		$d =~ s/^(\d\d).(\d\d).(\d\d)$/20$3-$2-$1/;
	} elsif ($SuppID == 61) {
		$d =~ s/^(\d\d).(\d\d).(\d\d)$/20$3-$1-$2/;
        }
	if ($d =~ m/^(\d+)\/(\d+)\/(\d\d\d\d)$/ ) {
		my $mm = $1;
		my $dd = $2;
		$d = "$3-" . substr( "0" . $1, -2) . "-" .  substr( "0" . $2, -2) ;
	}
	$qty  =~ s/,//g;
	$price=~ s/[,|\$]//g;
	$total=~ s/[,|\$]//g;

	my $testSQL = "  SELECT Count(*) AS RES FROM SuppInvoiceDetails
			 WHERE LineNo=$lineNo AND SupplierID=$SuppID AND SuppReference='" . qq($InvoiceID) . "';";
	my  $existingInv = $weberpdb->selectall_arrayref($testSQL);
	if ( $existingInv->[0]->[0] != 0 ) {
		print STDERR " this line ($lineNo) is already on invoice $InvoiceID.\n";
	} elsif ($words[0] ne "")    {
		print STDERR "\n";
		$sql = " INSERT INTO SuppInvoiceDetails
				( SIDetailID, SupplierID, PONumber, SuppReference, LineNo, StockID, Description, Ordered, GRNNo, 
					Invoicing, Price, Total, Approved, InvoiceDate)
			 VALUES ( NULL, '$SuppID', '$POID', '$InvoiceID', '$lineNo', '', '$description', 0, 0, 
					'$qty', '$price', '$total', 0, '$d' ) ";
		$weberpdb->do($sql);
	}
}

sub ParseInvoiceForm {
	my $VendorID = shift;
	my $string = shift;

	$string =~ s/\r/\n/g;
	my ( @lines, @words, @my_columns, $POID, $InvoiceID, $d, $sql );
	@lines = split (/^/,$string);
	my $doTable = '0';
	my $end_table = '0';
	my $LineNumber = 1;
        my $FileLineNumber = 1;
	my $savedLine= "";
	my $stillSaving = 0;
	my $savedItem="";
	my $Substituting="";
	my $NextLineIsThe = "X";

	foreach my $theLine ( @lines ) {
		chomp $theLine;
		$theLine =~ s/[ ]{4,}\b/   /g;
                $theLine =~ s/[\*]\b//g;
##		print STDERR "$FileLineNumber $theLine\n";
		$FileLineNumber++;
		if ($doTable eq '0' ) {
			@words = split("[ \b]+", $theLine );
			if ($VendorID == 1 && defined $words[1] ) {
				if ( $NextLineIsThe ne 'X' ) {
					if ( $NextLineIsThe eq 'PO_ID' ) {
						$POID = $words[1];
	                                }
					$NextLineIsThe = 'X';
				} else {
					if (  $theLine =~ m/95051\s{3,}\b(.*)$/ ) { 
						$d = $1;
					} elsif (  $theLine =~ m/BOWERS AVENUE\s{3,}\b(.*)$/ ) { 
						$InvoiceID = $1;
					} elsif (  $theLine =~ m/CUSTOMER ORDER NO\./ ) { 
						$NextLineIsThe = 'PO_ID';
					} elsif ($words[1] eq "ITEM") {
						$d =~ s/(\d\d)\/(\d\d)\/(\d\d)/20$3-$1-$2/;
						$doTable = "ITEM";
					}
				}
			}
			if ($VendorID == 14) {
#        ( $lineNo, $POID, $description, $SuppID, $d, $qty, $price, $total, $InvoiceID ) = @words[0,1,2,3,5,7,9,10,11];

				if ($theLine =~ s/^\s*(\d)\s+(\d{5})\s+(\d{5})\s+(\d)\s+(\d{1,2}\/\d{1,2}\/20\d\d)\s+(\d+)\s+(.+)-PBF\s+(\d+)\s+GUMSTX\s.*\s+(\d+\.*\d*)s*/"$4","$6","$7","14","W4","$5","W6","$8","W8","0","$9","$2"/ ) {
##					print STDERR "< $theLine >\n";
                                	if ( $1 =~ m/\d/ ) {
                                        	ParseInvoiceCSV($theLine);
	                                }
				} 
			}
			if ( (!defined $InvoiceID) && ($theLine =~ /\// )) {
	                        ($d, $InvoiceID ) = @words[1,2];
	                }
			if ( (!defined $POID) && ( $theLine =~ /Net/    )) {
	                        ( $POID ) = $words[1];
			}
			if ( (!defined $POID) && ( $theLine =~ /NET30/  )) {
	                        ( $POID ) = $words[0];
			}
			if (defined $words[1]) {
				if ( $words[1] =~ m/(Quantity|ITEM)/ ) {
					$doTable = $words[1];
				}
			}
			if ($VendorID == 3 && defined $words[0] ) {
				if ( $theLine =~ m/Net 60 Days\s{3,}\b(.*)\w*\d/) {
					$d = $1;
					$d =~ s/(\d{1,2}).(.{3}).(\d{4}\b)\s*/$3-$MONTHS{$2}-$1/;
				} elsif ( $theLine =~ m/\s{3,}(\d{3,})\s{3,}(\d{3,})/ ) {
					$POID=$1;
				} elsif  ( $theLine =~ m/Invoice #\s{3,}\b(.*)$/) {
					$InvoiceID = $1;
				} elsif ($words[1] eq "Idx") {
					$doTable = "Idx";
				}
			}
		} elsif ($end_table eq '1') {
			if ( $stillSaving >0 ) {
				my $theLine = $savedLine;
				$theLine =~
s/^\s{2,}(\d{1,2})\s{3,}([\d]*?)\s{3,}(.*?)\s{2,}(.*?)\s{2,}(.*)\s{3,}(.*)\s{3,}(.*).*/"$1","$POID","$savedItem","1","W4","$d","W6","$3","W8","$6","$7","$InvoiceID"/;
				ParseInvoiceCSV($theLine);
				$stillSaving = 0;
			}
			if ( $theLine =~ m/PAY THIS AMT(.{3,}\b)[\w]*/ ) {
				$end_table = '0';
				$POID='';
				$InvoiceID = '';
				$d = '';
				$doTable = '0';
##				print STDERR "Next page, if any...\n";
			}
		} elsif ( $theLine =~ m/\W/ ) {

                     if ( ($doTable eq "ITEM") && ( $VendorID == 1 ) ) {
                        if ( $theLine =~ m/^\s*\d\d\b/) {
				if ( $stillSaving >0 ) {
					my $print_line = $savedLine;
					$print_line =~
s/^\s{2,}(\d{1,2})\s{3,}([\d]*?)\s{3,}(.*?)\s{2,}(.*?)\s{2,}(.*)\s{3,}(.*)\s{3,}(.*).*/"$1","$POID","$savedItem","1","W4","$d","W6","$3","W8","$6","$7","$InvoiceID"/;
					ParseInvoiceCSV($print_line); 
				} 
				$savedLine = $theLine;
				$savedItem = '';
				$stillSaving = 2;
			} elsif ( $theLine =~ m/PAY THIS AMT(.{3,}\b)[\w]*/ ) {
                                        my $CheckSum= $1;
					my $CheckSumLine = '"0","'.$POID.'","CHECKSUM",1,"0","'.$d.'","W6","0","0","0","'.$1.'","'.$InvoiceID.'"';
					ParseInvoiceCSV($CheckSumLine);
					$end_table = 1;
			} elsif ($theLine =~ m/^\s*(\w*)\b$/) {
				if ($stillSaving > 1) {
					$savedItem = $1;
				}
			} elsif ( $theLine =~ m/FREIGHT\/ENERGY(.{3,}\b)[\w]*/ ) {
                                        my $handling = $1;
					if ($handling != 0) {
	                                        my $CheckSumLine = '"97","'.$POID.'","Shipping",1,"","'.$d.'","W6","","","","'.$handling.'","'.$InvoiceID.'"';
        	                                ParseInvoiceCSV($CheckSumLine);
					}
                        } elsif ( $theLine =~ m/FRT\/HAND\/ENERGY(.{3,}\b)[\w]*/ ) {
                                        my $handling = $1;
					if ($handling != 0) {
	                                        my $CheckSumLine = '"97","'.$POID.'","Shipping",1,"","'.$d.'","W6","","","","'.$handling.'","'.$InvoiceID.'"';
        	                                ParseInvoiceCSV($CheckSumLine);
					}
                        } elsif ( $theLine =~ m/TAX(.{3,}\b)[\w]*/ ) {
                                        my $taxes = $1;
					if ($taxes != 0) {
	                                        my $CheckSumLine = '"98","'.$POID.'","TAX",1,"","'.$d.'","W6","","","","'.$taxes.'","'.$InvoiceID.'"';
        	                                ParseInvoiceCSV($CheckSumLine);
					}
                        }  
		     }
                     if ( ($doTable eq "Idx") && ( $VendorID == 3 ) ) {
# DIGIKEY:                                          Idx         Box       Ordered        Cancel          Ship        Item      BackOrd     Price      Total
                        if ($theLine =~ s/^\s{2,}(\d{1,2})\s{3,}(\d)\s{3,}([\d]*?)\s{3,}([\d]*?)\s{3,}([\d]*?)\s{3,}(.*?)\s{3,}(.*?)\s{3,}(.*?)\s{3,}(.*).*/"$1","$POID","$6","3","W4","$d","W6","$5","W8","$8","$9","$InvoiceID"/ ) {
				my $temp6 = $6;
                                if ( $1 =~ m/\d{1,2}/ ) {
					if ( $stillSaving >0 ) {
						$stillSaving = 0;
                                        	ParseInvoiceCSV($savedLine);
					}
					 $savedLine = $theLine;
					 $savedItem = $temp6;
					 $stillSaving = 1;
				}
                        } elsif ($theLine =~ s/^\s{2,}(\d{1,2})\s{3,}(\d)\s{3,}([\d]*?)\s{3,}([\d]*?)\s{3,}([\d]*?)\s{3,}(.*?)\s{3,}(.*?)\s{3,}(.*).*/"$1","$POID","$6","3","W4","$d","W6","$5","W8","$7","$8","$InvoiceID"/ ) {
				my $temp6 = $6;
                                if ( $1 =~ m/\d{1,2}/ ) {
					if ( $stillSaving >0 ) {
						$stillSaving = 0;
                                        	ParseInvoiceCSV($savedLine);
					}
					 $savedLine = $theLine;
					 $savedItem = $temp6;
					 $savedItem =~ s/\*//;
					 $stillSaving = 1;
				}
                        } elsif ( $theLine =~ m/CUST REF #: (.{3,}\b)[\w]*/ ) {
					$Substituting= $1;
					if ($savedLine =~ m/$savedItem/) {
						$savedLine =~ s/$savedItem/$Substituting/;
					}
					$stillSaving = 0;
                                        ParseInvoiceCSV($savedLine);
                       } elsif ( $theLine =~ m/TOTAL INVOICED(.{3,}\b)[\w]*/ ) {
                                        my $CheckSum= $1;
					my $CheckSumLine = '"0","'.$POID.'","CHECKSUM",3,"0","'.$d.'","W6","0","0","0","'.$1.'","'.$InvoiceID.'"';
					ParseInvoiceCSV($CheckSumLine);
                       }
                     }
		     if ( ($doTable eq "Quantity") && ( $VendorID == 61 ) )  {
			if ($theLine =~ s/^ *([^ ]*) *([^ ]*) *(.*?)\s{3,}(-?[,|\d]*\.[\d]*)\s{3,}(-?[,|\d]*\.[\d]*)/"$LineNumber","$POID","$3","61","W4","$d","W6","$1","W8","$4","$5","$InvoiceID"/ ) {
				if (defined $1) {
					ParseInvoiceCSV($theLine);
					$LineNumber++;
				}
			}
		     }
		     if ( ($doTable eq "ITEM") && ( $VendorID == 44 ) ) {
                        if ($theLine =~ s/Sales Tax(.*)\s{3,}(.*).*/"98","$POID","Sales Tax","44","W4","$d","W6","","W8","","$2","$InvoiceID"/) {
				ParseInvoiceCSV($theLine);
			} elsif ($theLine =~ s/^( ?\d*)\s{3,}(.*?)\s{3,}(.*?)\s{3,}(.*?)\s{3,}(.*?)\s{3,}(.*).*/"$1","$POID","$2","44","W4","$d","W6","$4","W8","$5","$6","$InvoiceID"/ ) {
				my $save2 = $2;
				if ( $1 =~ m/\d\d/ ) {
                               		ParseInvoiceCSV($theLine);
				} elsif ($save2 eq 'Total') {
                                	$theLine =~ s/Total/CHECKSUM/;
                                        $theLine =~ s/""/"0"/;
					ParseInvoiceCSV($theLine);
				}
			}
		    }
		}
	}
	if ( $stillSaving >0 ) {
		if ($VendorID == 1) {
			my $theLine = $savedLine;
			$theLine =~
s/^\s{2,}(\d{1,2})\s{3,}([\d]*?)\s{3,}(.*?)\s{3,}(.*?)\s{3,}(.*)\s{3,}(.*)\s{3,}(.*).*/"$1","$POID","$savedItem","1","W4","$d","W6","$3","W8","$6","$7","$InvoiceID"/;
			ParseInvoiceCSV($theLine);
			$stillSaving = 0;
		} else {
			ParseInvoiceCSV($savedLine);
			$stillSaving = 0;
		}
	}
}

sub ParseInvoiceTable {
        my $string = shift;
	my $throwaway=2;
	my $LineNumber=1;
	my @lines = split ("\n",$string);
	foreach my $thisline ( @lines ) {
		if ($throwaway > 0) {
			$throwaway --;
		} else {
			ParseInvoiceCSV( $thisline  );
		}
	}
}
sub ParseOther {
	my $MessageTypeID = shift;
	my $message = shift;

        my $string = "";
        my $e = $p->parse_data( $message );
        my $i = 0;
        my $FileName;
        $limit = $e->parts;
        if ( $limit != 0) {
                while ($i < $e->parts) {
                        my $j = $i+1;
                        my $ContentType = $e->parts($i)->head->mime_attr("Content-type");
                        $AttachmentName =  $e->parts($i)->head->mime_attr("content-disposition.Filename") ;
                        if ( defined $AttachmentName  ) {
                                $FileType = substr( $AttachmentName, length($AttachmentName)-3, 3);
                                $FileName = substr( $AttachmentName, 0, length($AttachmentName)-3);
##                                print STDERR "    ($j:\t$limit $ContentType $FileType)\n ";
                        }
                        $i++;
                }
        } else {
                if ( my $bh = $e->bodyhandle ) {
                        $string = $bh->as_string();
                }
        }
}

	
sub ParseInvoice {
	my $VendorID = shift;
        my $message = shift;

	my $string = "";
	my $e = $p->parse_data( $message );
	my $i = 0;
	my $FileName;

	$limit = $e->parts; 
	if ( $limit != 0) {
		while ($i < $e->parts) {
			my $j = $i+1;
			my $ContentType = $e->parts($i)->head->mime_attr("Content-type");
			$AttachmentName =  $e->parts($i)->head->mime_attr("content-disposition.Filename") ;
			if ( defined $AttachmentName  ) {
				$FileType = substr( $AttachmentName, length($AttachmentName)-3, 3);
				$FileName = substr( $AttachmentName, 0, length($AttachmentName)-3);	
				print STDERR "   ($j:\t$limit $ContentType $FileType)\n ";
				if ($FileType eq 'pdf' ) { 
					$string = $e->parts($i)->bodyhandle->as_string;
					my $name = 'reports/invoices/' . $AttachmentName;
					my $fh = new FileHandle;
					$fh->open(">" . $name) or die "Could not open file\n";
					print $fh $string;
					$fh->close;
					$ENV{PATH} = "/bin:/usr/bin";
                                        system('/usr/bin/pdftotext','-layout',"$name") ;

					$name =~ s/pdf$/txt/;
                                        $string = read_file("$name");
                                        unlink($name);
					if ( ! ($FileName =~ m/Terms/)) {				
##						print STDERR "Sending it to ParseInvoiceForm$VendorID, string );\n";
						ParseInvoiceForm( $VendorID, $string );
					} 
				} elsif ( ($FileType eq 'xls') && ($VendorID != 14) ) {
					$string = $e->parts($i)->bodyhandle->as_string;
					my ($fh,$name_in) = mkstempt('emailreaderXXXXXXXXX', '/tmp', '.xls');
					print $fh $string;
					$fh->close;
					$ENV{PATH} = "/bin:/usr/bin";
					my $name_out = $name_in;
					$name_out =~ s/xls$/csv/;
					my $oute = " > /tmp/" . $name_out;
					my $theCommand = '-xp:0 -csv ' . " /tmp/$name_in " . " > " . "/tmp/$name_out" ;
	                                xlhtml $theCommand;
					unlink("/tmp/$name_in");
					$string = read_file("/tmp/$name_out");
	                                unlink("/tmp/$name_out");
                                        ParseInvoiceTable( $string );
				} elsif ( $FileType eq 'txt' ) {
                                        $string = $e->parts($i)->bodyhandle->as_string;
##                                        print STDERR "A> " . $FileName . length($string) . "\n";
					ParseInvoiceForm( $VendorID,  $string );
                                }

			} else {
				if ( $ContentType eq 'text' ) {
					$string = $e->parts($i)->bodyhandle->as_string;
					ParseInvoiceForm( $VendorID,  $string );
				}
			}
			$i++;
		}
	} else {
		if ( my $bh = $e->bodyhandle ) {
			$string = $bh->as_string();
			ParseInvoiceForm( 61,  $string );
		}
	}
}

$weberpdb = DBI->connect('dbi:mysql:erp_dev:localhost','erp_dev','domevia3', { PrintError => 1, RaiseError => 1, AutoCommit => 0 } );
$weberpdb->do("BEGIN");
if ($#ARGV==1) {
        my $host;
        my $id;
        my $pass;

	if ( $ARGV[0] eq "ROY") {
	        $host = 'localhost';
	        $id  = 'roy';
	        $pass = "fie4tack";
	} elsif ( $ARGV[0] eq "TESTER") {
                $host = 'mail.krubergs.com';
                $id  = 'tester@krubergs.com';
                $pass = "tester";
	} else {
	        print STDERR "Usage: EmailReader.pl {ROY|TESTER} {ALL|vendorid}\n";
		die "Bad first argument.\n";
	}	

	# returns an unconnected Mail::IMAPClient object:
	my $imap = Mail::IMAPClient->new(  
		Server => $host,
		User    => $id,
		Password=> $pass,
		Clear   => 5,   # Unnecessary since '5' is the default
			# Other key=>value pairs go here
	)       or die "Cannot connect to $host as $id: $@";

	$imap->connect or die "Could not connect: $@\n";
	my $folder = "INBOX.InvoicesNew";
	$imap->Select($folder);
	my $msgcount = $imap->message_count($folder);
	my @msgs = $imap->messages;# or die "Could not messages: $@\n";
	foreach my $msg_id (@msgs) {
	        my $message = $imap->message_string($msg_id);
		my $subject  = $imap->get_header($msg_id, "Subject");
		my $messageType = "UNKNOWN";
		foreach ( keys %InvoiceMessageKey ) {
			my $thisKey = $InvoiceMessageKey{$_};
			if ($subject =~ m/$thisKey/) {
				$messageType = "INVOICE";
				if ( $ARGV[1] eq "ALL") {
					ParseInvoice( $_, $message );
##		                        my $newUid = $imap->move( 'INBOX.InvoicesEntered', $msg_id);
				} elsif ( $ARGV[1] eq $_ ) {
                                        ParseInvoice( $_, $message );
##                                        my $newUid = $imap->move( 'INBOX.InvoicesEntered', $msg_id);
				}
			}
		}
	}

	$folder = "INBOX.BobNew";
	$imap->Select($folder);
	$msgcount = $imap->message_count($folder);
	@msgs = $imap->messages;# or die "Could not messages: $@\n";
	foreach my $msg_id (@msgs) {
                my $subject = $imap->get_header($msg_id, "Subject");
                my $message = $imap->message_string($msg_id);
	        my $e = $p->parse_data( $message );
		my $content = new MIME::Body::InCore $e->body;
		foreach ( keys %OtherSubjectKeys ) {
			my $thisKey = $OtherSubjectKeys{$_};
        	        if ($subject =~ m/$thisKey/) {
				if ( $_ eq 'DFM' ) {
					my $body = $e->bodyhandle;
					my $IO = $body->open("r");
					my $save_it_as = "/www/share.gumstix.com/buddies/B$1/B$1-R$2/B$1-R$2.html\n";
					my $fh = new IO::File "> $save_it_as";
					if (defined $fh) {
						while (defined($_ = $IO->getline)) {
							if ($_ =~ /(https[\w\/\:\.]*)/) {
								my $url_link = $1;
								$url_link =~ m/(\w+)\.(htm|asp)$/;
								my $char_link = $1;
								if ( $char_link eq 'quote') {
	                                                                print STDERR $url_link . "--" . $1 . "\n";
        	                                                        print $fh "<A HREF='" . $url_link . "'>$1</A>&nbsp";
                                                                } elsif ($char_link eq 'summary2') {
                                	                                print STDERR $url_link . "--" . $1 . "\n";
                                        	                        print $fh "<A HREF='" . $url_link . "'>$1</A>";
								}
							}
						}
						$IO->close;
				        	$fh->close;
					}
					print STDERR $save_it_as . "\n";
	                        }
                        }
                }
	}
} else {
	print STDERR "Usage: EmailReader.pl {ROY|TESTER} {ALL|vendorid}\n";
	die "Not a recognizable format";
}

$weberpdb->do("COMMIT");

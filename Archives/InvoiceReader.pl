#!/usr/bin/perl  -T -w

use lib '/sw/lib/perl5';
use lib '/sw/lib/perl5/darwin';
use DBI;
use strict;
use Date::Calc;


my $startPrd = 1;

my $weberpdb = DBI->connect('dbi:mysql:erp_dev:localhost','erp_dev','domevia3', { PrintError => 1, RaiseError => 1, AutoCommit => 0 } );

my $LineNumber=0;
my $record="";
my $InvoiceTotal=0;
my $Line="";
my $StockID="";
my $Shipper="";
my $Qty="";
my $Price="";
my $Amount="";
my $POID="";
my $termsA="";
my $termsB="";
my $d="";
my $InvoiceID="";
my $OurTotal=0;
my $Description="";
my $Ordered=0;

$weberpdb->do("begin");

while ($record = <STDIN>) {
		$LineNumber++;
		if ($LineNumber == 6) {
			@_ = split("[ \t]+", $record);
			($d, $InvoiceID ) = @_[1..2];
		}
		if ($LineNumber == 20) {
			@_ = split("[ \t]+", $record);
			( $POID, $termsA, $termsB ) = @_[1..3];
		}
		if ($record =~ m/^\d\d ./) {
			@_ = split("[ \t]+", $record);
			($Line, $StockID, $Shipper, $Qty, $Price, $Amount) = @_[0..5];
#			print "\n" .  
			$weberpdb->do(
	"INSERT INTO SuppInvoiceDetails (SIDetailID,PONumber,SuppReference,LineNo,StockID,Description,Ordered,GRNNo,Invoicing,Price,Total,Approved,Date)
	 VALUES (0,'$POID','$InvoiceID','$Line','$StockID','$Description','$Ordered','$Shipper','$Qty','$Price','$Amount','0','$d');"
			);
#			;
			$OurTotal += $Amount;
		}
		if ($record =~ m/Total /) {
			@_ = split("[ \t]+", $record);
			( $InvoiceTotal ) = @_[2];
			$InvoiceTotal=~ s/\$//;
#			print "\nTheir total came to: \$" . $InvoiceTotal;
			if ($OurTotal - $InvoiceTotal == 0) {
#				print "\t So did ours.\n";
			} else {
#				print "\n Error: Our total was $OurTotal.\n";
			}				
		}
   }
$weberpdb->do("commit");


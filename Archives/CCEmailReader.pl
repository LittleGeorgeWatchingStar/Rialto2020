#!/usr/bin/perl  -T -w

#use lib '/sw/lib/perl5';
#use lib '/sw/lib/perl5/darwin';
use DBI;
use strict;
require Date::Calc;
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
my %VMCReceipts;
my %AmExReceipts;

sub ParseAuthorizationString {
	my $string = shift;
        my ( @lines, @words, $Date, $Time, $Amount, $Payment, $AMPM, $Authorization, $Transaction, $CardID, $sql, $PostDate, $TransType );
        @lines = split (/\n/,$string);
        foreach my $theLine ( @lines ) {
	        $theLine =~ s/\r/\n/g;
                chomp $theLine;
		$theLine .= "   ";
		if (length($theLine) > 4) {
			@words = split (/ /,$theLine);
			if ($words[0] eq 'Date/Time') {
				$Date   = $words[2];
	                        $Time   = $words[3];
				$AMPM	= $words[4];	
                        } elsif ( $words[0] eq "Authorization" ) {
                                $Authorization  = $words[3];
				if ( ! defined $Authorization  ) {
					$Authorization = '-none-';
				}
                        } elsif ( $words[0] eq "Type" ) {
				if ($words[3] eq 'Only') {
					$TransType  = 'AuthorizationOnly';
				} else {
					$TransType = $words[2];
				}
				print STDERR $TransType . '<br>';
                        } elsif ( $words[0] eq "Transaction" ) {
                                $Transaction   = $words[3];
			} elsif ( $words[0] eq "Amount" ) {
	                        $Amount   = $words[2];
	                } elsif ( $words[0] eq "Payment" ){
	                        $Payment = $words[3];
			}
		}
	}
	if ($TransType eq 'Credit') {
		$Amount = -$Amount;
	}	
	if ( $Payment eq 'American' ) { 
		$CardID = 'AMEX';
	} elsif ( $Payment eq 'Visa' ) {
                $CardID = 'VISA';
        } elsif ( $Payment eq 'MasterCard' ) {
                $CardID = 'MCRD';
	}
	my ($ye, $mo, $da) = ( substr($Date,7,4),$MONTHS{uc substr($Date,3,3)}, substr($Date,0,2));
	if ($AMPM eq 'PM' && $Time ge '03:00:00' &&  $Time lt '12:00:00') {
		($ye, $mo, $da) = Date::Calc::Add_Delta_Days($ye,$mo,$da,1);
	}
        if ( length($mo) == 1) {
                $mo = "0$mo";
        }
	if ( length($da) == 1) {
		$da = "0$da";
	} 
        $PostDate = "$ye-$mo-$da";
	if (!($TransType eq 'AuthorizationOnly')) {
		$sql = "INSERT INTO CardTrans ( CardTransID, TransactionID, AuthorizationCode, Amount, TransDate, TransTime, CardID, PostDate) VALUES (
				0, '$Transaction', '$Authorization', '$Amount', '$Date', '$Time$AMPM', '$CardID', '$PostDate' )";
		$weberpdb->do($sql);
	}
#	print STDERR $sql . "\n"; 
}

sub ParseAuthorization {
        my $message = shift;

	my $string = "";
	my $e = $p->parse_data( $message );
	my $i = 0;
	my $FileName;
	$limit = $e->parts; 
	if ( $limit == 0) {
		if ( my $bh = $e->bodyhandle ) {
			$string = $bh->as_string();
			ParseAuthorizationString($string);
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
	}
	my $imap = Mail::IMAPClient->new(  
		Server => $host,
		User    => $id,
		Password=> $pass,
		Clear   => 5,   # Unnecessary since '5' is the default
			# Other key=>value pairs go here
	)       or die "Cannot connect to $host as $id: $@";

	$imap->connect or die "Could not connect: $@\n";
	my $folder = "INBOX.AuthorizationsToPost";
	my $newFolder = "INBOX.AuthorizationsPosted";
	$imap->Select($folder);
	my $msgcount = $imap->message_count($folder);
	my @msgs = $imap->messages or die "Could not messages: $@\n";
	foreach my $msg_id (@msgs) {
	        my $message = $imap->message_string($msg_id);
		my $subject  = $imap->get_header($msg_id, "Subject");
		if ($subject =~ m/Merchant Email Receipt/) {
			ParseAuthorization( $message ) ;
			my $newUid = $imap->move( 'INBOX.AuthorizationsPosted', $msg_id);
		} else {
                        my $newUid = $imap->move( 'INBOX.OtherAuthorizationEmails', $msg_id);
		}
	}
}

$weberpdb->do("COMMIT");

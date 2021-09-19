#!/usr/bin/perl -w -T 

package main;

use warnings;
use strict;
use DBI;
use CAM::PDF;
use Getopt::Long;

my $weberpdb = DBI->connect('dbi:mysql:erp_dev:localhost','erp_dev','domevia3', { PrintError => 1, RaiseError => 1, AutoCommit => 0 } );
my $sql = "SELECT FormID FROM Forms WHERE FormID LIKE '" . $ARGV[0] . "'";
my $return = $weberpdb->selectall_hashref( $sql, 'FormID' );
my $FormID = $return->{$ARGV[0]}->{'FormID'};
if ($FormID ne  $ARGV[0]) {
	exit;
}

my $outfilename ="Forms/compx.$FormID";
my $infilename = "Forms/$FormID";


my $doc = CAM::PDF->new($infilename) || die "$CAM::PDF::errstr\n";
   $sql = "SELECT * FROM Forms WHERE FormID='$FormID' AND Text != '' AND FormField LIKE '\$\%' ORDER BY FieldID ASC";
my $variablelist = $weberpdb->selectall_hashref($sql,"FormField" );

   $sql = "SELECT * FROM Forms WHERE FormID='$FormID' AND Text != '' AND FormField NOT LIKE '\$\%' ORDER BY FieldID ASC";
my $formfieldlist = $weberpdb->selectall_hashref($sql,"FormField" );
my $endprd   = 47;	# //	35;
my $startprd = 35;	# //	23;
my $Grouper = "INNER JOIN ChartMaster ON ChartDetails.Accountcode=ChartMaster.AccountCode ";
my %VAR;
$VAR{"\$PERIOD_START"} = $startprd;
sub SQL_Parse {
	my $selection = shift;
	my $table  = shift;
	my $criterion = shift;
	my $substitution = shift;
	my $get_sql;
	if ( $criterion =~ m/Group/ ) {
		$get_sql  = "SELECT  $selection FROM $table $Grouper WHERE $criterion ";
	} else {
                $get_sql  = "SELECT $selection ";
		if ($table ne "") {
			$get_sql .= " FROM $table ";
		}
                if ($criterion ne "") {
                        $get_sql .= " WHERE $criterion ";
                }
	}
	$get_sql =~ s/\[prd\]/$substitution/;
#	print STDERR $get_sql . "\n";
	my $answer = $weberpdb->selectrow_arrayref( $get_sql );
	return $answer->[0];
}

sub Parsed_Field {
	my $entry = shift;
        my $tofill = $entry->{'Text'};
	my $reselect = $entry->{'ToSelect'};
	my $reTable= $entry->{'FromTable'};
	my $reCriterion = $entry->{'WhereCriterion'};
#	print STDERR  '*'  . $entry->{'FieldID'} . '*' ;
	$tofill		=~ s/\{(\$\w+)\}/$VAR{$1}/g;
	$reselect 	=~ s/\{(\$\w+)\}/$VAR{$1}/g;
	$reTable 	=~ s/\{(\$\w+)\}/$VAR{$1}/g;
	$reCriterion 	=~ s/\{(\$\w+)\}/$VAR{$1}/g;

        if ( $tofill eq '$CALCULATE') {
		$tofill = SQL_Parse( $reselect, $reTable, $reCriterion , $startprd );  #"","", "", "" );
	} elsif ( $tofill eq '$PERIOD_START') {
                $tofill = SQL_Parse( $reselect, $reTable, $reCriterion , $startprd );
	} elsif ( $tofill eq '$PERIOD_END') {
                $tofill = SQL_Parse( $reselect, $reTable, $reCriterion, $endprd );
        } elsif ( $tofill eq '$PERIOD_RANGE' ) {
                $tofill = SQL_Parse( $reselect, $reTable, $reCriterion, $endprd )
                	 -SQL_Parse( $reselect, $reTable, $reCriterion, $startprd );
        }
	return $tofill;
}
print STDOUT '<table>';
my $this_field;
my @sortedKeys = sort { $variablelist->{$a}->{'FieldID'} <=> $variablelist->{$b}->{'FieldID'} } keys %$variablelist;
foreach ( @sortedKeys  ) {
#	foreach (keys(%$variablelist )) {
	$this_field = Parsed_Field( $variablelist->{$_} );
	$VAR{$_} =$this_field;
	print STDOUT '<tr><td>' .  $variablelist->{$_}->{'FieldID'} . '</td><td>' . ($_)  . '</td><td>' . $this_field  . '</td></tr>' ;
}
print STDOUT '</table>';
my $this_formatted;
foreach (keys(%$formfieldlist )) {
	$this_field = Parsed_Field( $formfieldlist->{$_} );
	$this_formatted = sprintf( $formfieldlist->{$_}->{'TextFormat'}, $this_field ); 
	$doc->fillFormFields( $_, $this_formatted);
	print STDERR "$_: $this_field \n";
}
$doc->cleanoutput($outfilename);

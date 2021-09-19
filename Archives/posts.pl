#!/usr/bin/perl -T -w

use lib '/sw/lib/perl5';
use lib '/sw/lib/perl5/darwin';
use DBI;
use strict;
use Date::Calc;

#my $ariadb = DBI->connect('dbi:mysql:aria_gum:localhost','erp_gum','bigg3Alf', { PrintError => 1, RaiseError => 1, AutoCommit => 1 } );
my $weberpdb = DBI->connect('dbi:mysql:erp_dev:localhost','erp_dev','domevia3', { PrintError => 1, RaiseError => 1, AutoCommit => 1 } );
#my $weberpdb = DBI->connect('dbi:mysql:erp_gum:localhost','erp_gum','bigg3Alf', { PrintError => 1, RaiseError => 1, AutoCommit => 1 } );

my $sth0 = $weberpdb->do("       UPDATE GLTrans SET Posted = 0");
my $sth1 = $weberpdb->do("       TRUNCATE TABLE ChartDetails");

$weberpdb->do("
SELECT Periods.PeriodNo, TranDate FROM GLTrans
INNER JOIN Periods ON LastDate_In_Period LIKE LEFT(TranDate,10);
");

my $all_chart_details	= $weberpdb->selectall_hashref("	SELECT P.PeriodNo, C.AccountCode, concat( P.PeriodNo, C.AccountCode ) myKEY
								FROM Periods P, ChartMaster C","myKEY");
my $all_gltrans		= $weberpdb->selectall_hashref(	"	SELECT CounterIndex, PeriodNo, Account, Amount, Posted 
								FROM GLTrans WHERE Posted=0", "CounterIndex");
my $sth3		= $weberpdb->prepare( " insert into ChartDetails(AccountCode,Period,Actual) values (?,?,?)");
my $sth4                = $weberpdb->prepare( " UPDATE ChartDetails SET BFwd = ? WHERE AccountCode=? AND Period= ?");

foreach (keys(%$all_gltrans))
{
	my $theKey = $all_gltrans->{$_}->{PeriodNo} . $all_gltrans->{$_}->{Account};
	if (defined($all_chart_details->{$theKey}->{Amount} ))
	{
                $all_chart_details->{$theKey}->{Amount} += $all_gltrans->{$_}->{Amount};
        } else {
		$all_chart_details->{$theKey}->{Amount}  = $all_gltrans->{$_}->{Amount};
	}
}

foreach (keys(%$all_chart_details))
{
	my $Amount = 0;
	if (defined($all_chart_details->{$_}->{Amount} ))
	{
		$Amount = $all_chart_details->{$_}->{Amount};
	}
##	print "Adding $_: $all_chart_details->{$_}->{AccountCode}, $all_chart_details->{$_}->{PeriodNo}, $Amount\n "; 
	$sth3->execute( $all_chart_details->{$_}->{AccountCode}, $all_chart_details->{$_}->{PeriodNo}, $Amount );
}


my $all_BFwds         = $weberpdb->selectall_hashref( "	SELECT  X.AccountCode,X.Period, sum(Y.Actual) BFwd,  concat( X.Period, X.AccountCode ) theKEY
							FROM ChartDetails X, ChartDetails Y 
							where X.Period > Y.Period AND X.AccountCode = Y.AccountCode
							group by X.AccountCode, X.Period" , "theKEY");
foreach (keys(%$all_BFwds))
{
        my $Amount = 0;
        if (defined($all_BFwds->{$_}->{BFwd} ))
	{
		$Amount = $all_BFwds->{$_}->{BFwd};
	}
	$sth4->execute( $Amount, $all_BFwds->{$_}->{AccountCode}, $all_BFwds->{$_}->{Period} );
}
							
$weberpdb->do("       UPDATE GLTrans SET Posted = 1");
$weberpdb->do("COMMIT");
print "\nCommited, too.\n";

print "Done\n";






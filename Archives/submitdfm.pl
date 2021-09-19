#!/usr/bin/perl -T -w
use lib '/sw/lib/perl5';
use lib '/sw/lib/perl5/darwin';
use DBI;
use strict;
use Date::Calc;

# turn on perl's safety features
use strict;
use warnings;

# work out the name of the module we're looking for
my $board_name = $ARGV[0]
  or die "Must specify module name on command line";
my $revision_number = "1019";
$board_name = "B00027";
my $module_name = "/www/share.gumstix.com/buddies/" . $board_name . "/" . $board_name . "-R" .  $revision_number .  "/B00027.R1019.zip";

#print $module_name . "\n";

# create a new browser
use WWW::Mechanize;
my $browser = WWW::Mechanize->new();

# tell it to get the main page
$browser->get("https://www.freedfm.com/!freedfmstep1.asp");

#<input type="text" class="printed_circuit_form4" name="ContactEmail"/>
#<input type="submit" class="printed_circuit_manufacturer_button" name="submit1" id="submit1" value="Upload Zip File"/>

# okay, fill in the box with the name of the
# module we want to look up
$browser->form(2);
$browser->field("ContactEmail", "gordon\@gumstix.com" );
$browser->field("UploadFileData", $module_name );
$browser->submit();

if ( !$browser->success()) {
	print 'No good...';
	exit (1);
}

$browser->form("step2");
$browser->select("materialType","FR4");
$browser->field("part_num","27");
$browser->field("rev","109");
$browser->select("layers","2");
$browser->select("plating","LFSolder");
$browser->select("solderSides","2");
$browser->select("silkscreenSides","2");
$browser->tick("Array","on");
my $html = $browser->content;
$html =~ s[dimen1 VALUE=0][dimen1 VALUE=3]isg;
$html =~ s[dimen2 VALUE=0][dimen2 VALUE=1.3]isg;
$html =~ s[\!freedfmstep3\.asp][https\:\/\/www\.freedfm\.com\/\!freedfmstep3\.asp]isg;
$browser->update_html( $html );

#$browser->submit();
print $browser->content();

exit(0);

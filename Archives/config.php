<?php

set_include_path(__DIR__.'/../lib' . PATH_SEPARATOR . get_include_path());

require_once __DIR__ . '/../vendor/autoload.php';
require_once 'gumstix/GumstixConfig.php';
require_once 'gumstix/erp/tools/ErpErrorHandler.php';

/* The directory where WebERP is installed. */
define('RIALTO_FS_ROOT', realpath(__DIR__.'/..'));

try {
    $configFile = sprintf('%s/config/config.ini', RIALTO_FS_ROOT);
    $gConfig = new GumstixConfig($configFile);
    unset($configFile);
}
catch ( Exception $ex ) {
    die("Error loading configuration: ". $ex->getMessage());
}

define('SITE_PATH_REPORTS', '/reports');

/** @deprecated  Use RIALTO_FS_ROOT instead. */
define('SITE_FS_WEBERP_BASE', RIALTO_FS_ROOT);

/* WebERP's document root directory. */
define('SITE_FS_WEBERP_PUBLIC', RIALTO_FS_ROOT . '/web');

define('SITE_FS_WEBERP_REPORTS', SITE_FS_WEBERP_PUBLIC . SITE_PATH_REPORTS);

define('SITE_FS_WEBERP_LOCALE', RIALTO_FS_ROOT . '/locale');

/***********
 URI PATHS
***********/

/* Locale settings */
$DefaultLanguage = $gConfig->locale->language;
$DefaultCharset = $gConfig->locale->charset;
setlocale(LC_ALL, sprintf('%s.%s', $DefaultLanguage, $DefaultCharset));
$DefaultDateFormat = $gConfig->locale->dateformat;

// Application version
$Version = '2.9b';

// The company name to appear in the headings on each page displayed
$CompanyName = $gConfig->company->name;


// The timezone of the business - this allows the possibility of having
// the web-server on a overseas machine but record local time
// this is not necessary if you have your own server locally
// putenv('TZ=Europe/London');
// putenv('Australia/Melbourne');
// putenv('Australia/Sydney');
// putenv('TZ=Pacific/Auckland');

// Connection information for the database
// $host is the computer ip address or name where the database is located
// assuming that the web server is also the sql server
$host = $gConfig->erp->database->params->host;
$DatabaseName = $gConfig->erp->database->params->dbname;

// sql user & password
$dbuser = $gConfig->erp->database->params->username;
$dbpassword = $gConfig->erp->database->params->password;

// CSS GUI theme
$DefaultTheme = 'fresh';

//The maximum time that a login session can be idle before automatic logout
//time is in seconds  3600 seconds in an hour
$SessionLifeTime = 3600;

// Accounts Receivable
// Aging periods used on customer and supplier enquiry screens and aged listings*/
$PastDueDays1 = 30;
$PastDueDays2 = 60;
$DefaultCreditLimit = 1000;


/*On statements if wish to show all settled transactions in the last month on statements for cash received and
credits allocated etc then set $Show_Settled_LastMonth = 1 */
$Show_Settled_LastMonth =1;

/*The romalpa clause prints out on the invoice in small type - although of limited use can in some businesses help
recover bad debts */
$RomalpaClause = 'Ownership will not pass to the buyer until the goods have been paid for in full.';

/* Types of receipts - only add or remove elements of the array as required*/
$ReceiptTypes = array('Cheques', 'Cash', 'Direct Credit','Credit card');

/* Order Items Selection */
/*In large databases it would be possible to return a gigantic page of parts if insufficient criteria were entered. This variable limits the output to the client browser to reduce waiting and clogging network connections */
/* Now set by user - dependent on connection speed really so more appropriate
$Maximum_Number_Of_Parts_To_Show = 100;
*/

/*The number of quick entry inputs to show on the order entry screen */
$QuickEntries = 10;

/*orders placed after this hour (an integer from 0 to 23) will be dispatched the following day */
$DispatchCutOffTime = 14;


/*determines whether or not to allow sales orders to be entered for purchased or manufactured items that have no cost set up */

$AllowSalesOfZeroCostItems = false;


/*determines whether or not the batch/lot/serial number must have existed previously before it can be credited
eg. If this variable is set to true Batch number 123456 cannot be credited into stock if there was no prior booking in
of this batch through a purchase order delivery or stock adjustment in*/
$CreditingControlledItems_MustExist = false;


/*The price list to use if no price defined in the customers normal price list */
$DefaultPriceList = 'OS';

/*Stock Units array*/
$StockUnits = array('each', 'metres', 'kgs', 'litres', 'length', 'pack');

/*Freight calculations */
/*Default Shipper - this must be an existing Shipper_ID in the table Shippers */
$Default_Shipper = 1;

/*Set DoFreightCalc=True if the order entry should insist on calculating freight cost
Set to False if no freight calculation is forced */
$DoFreightCalc = False;

/* Set $FreightChargeAppliesIfLessThan to the amount below which freight is calculated
and charged  - is not used if DoFreightCalc == False */
$FreightChargeAppliesIfLessThan = 1000;

/*The tax level to apply to Freight Charges and supplier invoices*/

$DefaultTaxLevel =1;

/*The name of the tax authority reference to print on official transaction documnents
Australia - would be A.B.N.
UK - would be VAT Regn #
NZ - GST Regn #
*/
$TaxAuthorityReferenceName = 'Tax Ref';

$CountryOfOperation = 'USD';

/* Number of periods of stock usage to show in stock usage enquiries */
$NumberOfPeriodsOfStockUsage = 12;

/* Accounts Payable */

/* System check to see if quantity charged on purchase invoices exceeds the quantity received.
If this parameter is checked the proportion by which the purchase invoice is an overcharge
referred to before reporting an error */
$Check_Qty_Charged_vs_Del_Qty = True;

/* System check to see if price charged on purchase invoices exceeds the purchase order price.
If this parameter is checked the proportion by which the purchase invoice is an overcharge
referred to before reporting an error */
$Check_Price_Charged_vs_Order_Price = False;

/* Proportion by which a purchase invoice line is an overcharge for a purchase order item received
is an overcharge. If the overcharge is more than this percentage then an error is reported and
purchase invoice line cannot be entered
The figure entered is interpreted as a percentage ie 20 means 0.2 or 20% not 20 times
*/
$OverChargeProportion = 30;

/* Proportion by which items can be received over the quantity that is specified in a purchase
invoice.  The figure entered is interpreted as a percentage ie 10 means 0.1 or 10% not 10 times
*/
$OverReceiveProportion = 20;

/* If $PO_AllowSameItemMultipleTimes = True then purchase orders can have the same part on them
several times - set to False checks to ensure that multiple lines of the same part are prohibited */

$PO_AllowSameItemMultipleTimes = True;

/* Types of payments - only add or remove elements of the array as required*/
$PaytTypes = array('Cheque', 'Cash', 'Direct Credit');


/* Email address of the person(s) who should receive the cheque listings */
$ChkListingReceipients = array ('"Gordon Kruberg" <gordon@gumstix.com>');

/*Calendar Month number of last month in the company's financial year  - used for defaulting TB and P & L reports*/
$YearEnd = '12';

/*Report Page Length in lines */
$PageLength = 48;

/*Sections in Accounts  - the numbers 1 (income) and 2 (COGS) are hard coded other sections can be added any of the narrative can be changed at will*/

$Sections = array(
1 => 'Revenue',	/*Can't delete this line but can change the name */
2 => 'Cost Of Goods Sold',	/*Can't delete this line but can change the name */
10 => 'Assets',
20 => 'Liabilities',
30 => 'Capital',
40 => 'Retained Earnings',
90 => 'Expenses',
100 => 'Income taxes'
);

/*Directory under the main directory where part photos/ pictures are to be stored
NB no slashes are necessary. Part pictures in this directory must be .jpg format with this extension
Note that this directory must be created by the system administrator*/
$part_pics_dir = 'part_pics';

/*Directory under the main web files directory where report files are to be created NB no slashes are necessary
Note that this directory must be created by the system administrator */
$reports_dir = 'reports';
$build_orders_dir = $reports_dir . '/' . 'build_orders';
$purchase_orders_dir = $reports_dir . '/' . 'purchase_orders';
$invoices_dir = $reports_dir . '/' . 'invoices';

/*Show debug messages returned from an error on the page.
Debugging info level also determined by settings in PHP.ini
if $debug=1 show debugging info, dont show if $debug=0 */
$debug = 1;

/* Security Group definitions - Depending on the AccessLevel of the user defined
 * in the user set up the areas of functionality accessible can be modified.
 * Each AccessLevel is associated with an array containing the security
 * categories that the user is entitled to access.  Each script has a
 * particular security category associated with it.  If the security setting of
 * the page is contained in the security group as determined by the access level
 * then the user will be allowed access.  Each page has a $PageSecurity = x;
 * variable.  This value is compared to contents of the array applicable which
 * is based on the access level of the user.  Access authorisation is checked
 * in header.inc this is where _SESSION['AccessLevel'] is the index of the
 * SecurityGroups array. If you wish to add more security groups with then you
 * must add a new SecurityHeading to the SecurityHeadings array and a new array
 * of Security categories to the Security Groups array.  This mechanism allows
 * more fine grained control of access.  SecurityGroups is an array of arrays.
 * The index is the order in which the array of allowed pages is defined new
 * ones can be defined at will or by changing the numbers in each array the
 * ecurity access can be tailored. These numbers need to read in conjunction
 * with the Page Security index. */
$SecurityHeadings = array(
			'Inquiries/Order Entry',
			'Manufac/Stock Admin',
			'Purchasing Officer',
			'AP Clerk',
			'AR Clerk',
			'Accountant',
			'Customer Log On Only',
			'System Administrator'
);

$SecurityGroups = array(
			array(1,2),
			array(1,2,                 11),
			array(1,2,3,4,5,           11),
			array(1,2,    5),
			array(1,2,3,               11),
			array(1,2,3,4,5,6,7,8,9,10,11),
			array(1),
			array(1,2,3,4,5,6,7,8,9,10,11,12,13,14,15)
);


/* Whether to display the demo login and password or not */
$allow_demo_mode = False;

/*Session save path is necessary where there may be several installations on the same server and the server cookie must be in different directories to avoid confusion - also where the server uses load balancing among several servers - one server must be used for the cookie - sourceforge.net uses load balancing and requires this for the demo*/

//$SessionSavePath='/home/groups/w/we/web-erp/sessions/';


/*EDI configuration variables definition */

/*EDI Draft version 01B - Controlling agency is the UN - EAN version control number (EAN Code)
this info is required in the header record of every message sent - prepended with the message type*/

$EDIHeaderMsgId = 'D:01B:UN:EAN010';

/*EDI Reference of the company */

$EDIReference = 'WEBERP';

/* EDI Messages for sending directory */

$EDI_MsgPending = 'EDI_Pending';

/* EDI Messages sent log directory */

$EDI_MsgSent = 'EDI_Sent';

/* EDI Messages sent log directory */

$EDI_Incoming_Orders = 'EDI_Incoming_Orders';

/* This automatically emails the developer with any Help text you add
Please give generously!! */

$ContributeHelpText = true;

/* Default maximum number of records to display on a page but overridden by user setting if not 0 */

$DefaultDisplayRecordsMax = 50;


/* These variables need to be modified to set up for ftp of files to a radio
 * beacon ftp server.  These variables are only used in the script
 * FTP_RadioBeacon.php. */

$Location ='BL';
$RadioBeaconHomeDir = '/home/RadioBeacon';
$FileCounter = '/home/RadioBeacon/FileCounter';
$FilePrefix = 'ORDXX';
$ftp_server = '192.168.2.2';
$ftp_user_name = 'RadioBeacon ftp server user name';
$ftp_user_pass = 'Radio Beacon remote ftp server password';


/*The $rootpath is used in most scripts to tell the script the installation details of the files.

NOTE: In some windows installation this command doesn't work and the administrator must set this to the path of the installation manually:
eg. if the files are under the webserver root directory then rootpath =''; if they are under weberp then weberp is the rootpath - notice no additional slashes are necessary.
*/

//$rootpath = rtrim(dirname($_SERVER['PHP_SELF']),"/");
$rootpath = '';
//$rootpath = '/web-erp';

/* The "underscore" function is used by gettext for translations.  If gettext
 * is not installed, then we simply define the underscore function to return
 * the given string unchanged. */
if (!function_exists('_')){
	function _($text){
		return ($text);
	}
}

/* money_format() does not exist on some platforms (eg, Windows), so we
 * provide a lame alternative in those cases. */
if (! function_exists('money_format') ) {
    function money_format($format, $number) {
        return '$'. number_format($number, 2);
    }
}

require_once 'gumstix/erp/config/ErpBootstrapper.php';
$bootstrapper = new ErpBootstrapper($gConfig);
$bootstrapper->bootstrap();

//require_once 'gumstix/erp/tools/Fonts.php';

<?php
/* $Revision: 1.13 $ */
$db = mysql_connect($host , $dbuser, $dbpassword);
mysql_select_db($DatabaseName,$db);

if (! function_exists('mysql_set_charset') ) {
    function mysql_set_charset($string, $db) {
        mysql_query("set names '$string'", $db);
    }
}
mysql_set_charset('utf8', $db);
mysql_query("set session sql_mode='NO_ZERO_DATE,NO_ZERO_IN_DATE'");

require_once ('includes/MiscFunctions.php');


//DB wrapper functions to change only once for whole application

function DB_query ($SQL,
		&$Conn,
		$ErrorMessage='',
		$DebugMessage= '',
		$Transaction=false,
		$TrapErrors=true){

	global $debug;
	$result=mysql_query($SQL,$Conn);
	if ($DebugMessage == '') {
		$DebugMessage = _('The SQL that failed was');
	}
	if (DB_error_no($Conn) != 0){
		prnMsg($ErrorMessage.'<BR>' . DB_error_msg($Conn),'error', _('Database Error'));
		if ($debug==1){
			prnMsg($DebugMessage. "<BR>$SQL<BR>",'error',_('Database SQL Failure'));
		}
		if ($Transaction){
			$SQL = 'rollback';
			$Result = DB_query($SQL,$Conn);
			if (DB_error_no($Conn) !=0){
				prnMsg(_('Error Rolling Back Transaction'), '', _('Database Rollback Error') );
			}
		}
		if ($TrapErrors){
			include('includes/footer.inc');
			exit;
		}
	}
	return $result;

}

function DB_fetch_row (&$ResultIndex) {

	$RowPointer=mysql_fetch_row($ResultIndex);
	Return $RowPointer;
}

function DB_fetch_array (&$ResultIndex) {

	$RowPointer=mysql_fetch_array($ResultIndex);
	Return $RowPointer;
}

function DB_data_seek (&$ResultIndex,$Record) {
	mysql_data_seek($ResultIndex,$Record);
}

function DB_free_result (&$ResultIndex){
	mysql_free_result($ResultIndex);
}

function DB_num_rows (&$ResultIndex){
	return mysql_num_rows($ResultIndex);
}

function DB_error_no (&$Conn){
	return mysql_errno($Conn);
}

function DB_error_msg(&$Conn){
	return mysql_error($Conn);
}

function DB_Last_Insert_ID(&$Conn){
	return mysql_insert_id($Conn);
}

/**
 * Escapes any dangerous characters from the input string so that
 * it is safe to use in a SQL query.
 * @param string $input  The input string
 * @param resource $db_link  The database resource to use
 * @return  The string with any unsafe characters escaped.
 */
function DB_esc($input, $db_link)
{
    return mysql_real_escape_string($input, $db_link);
}

?>

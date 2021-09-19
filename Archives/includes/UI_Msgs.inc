<?php

function displayError($msg, $center=false)
{
    echo "<CENTER><table border='1' cellpadding='3' cellspacing='0' style='border-collapse: collapse' bordercolor='#CC3300' width='98%'>
      <tr>
        <td  " . ($center?"align='center' ":"") . " width='100%' bgcolor='#FFCCCC'><font color='#DD2200'>$msg</font></td>
      </tr>
    </table></CENTER><BR>";	
    	
	//echo "<span class='errortext'><B>" . _("ERROR :") . "</B> $msg</span><BR>";
}

function displayNotification($msg, $center=false)
{
    echo "<CENTER><table border='1' cellpadding='3' cellspacing='0' style='border-collapse: collapse' bordercolor='#33CC00' width='98%'>
      <tr>
        <td " . ($center?"align='center' ":"") . " width='100%' bgcolor='#CCFFCC'><font color='#007700'>$msg</font></td>
      </tr>
    </table></CENTER><BR>";	
}

function displayNotificationCentered($msg)
{
	displayNotification($msg, true);
}

function displayHeading($msg)
{
	echo "<center><span class='headingtext'>$msg</span></center>";	
}

function displayHeading2($msg)
{
	echo "<center><span class='headingtext2'>$msg</span></center>";	
}

function stockItemHeading($StockID)
{
	if ($StockID != "") {
		$result = DB_query_simple("SELECT Description, Units FROM StockMaster WHERE StockID='$StockID'");		
        $myrow = DB_fetch_row($result);
        
    	displayHeading("$StockID - $myrow[0]"); 
    	$units = $myrow[1];
    	displayHeading2(_("in units of :") . _("$units"));        	
	}
}

?>
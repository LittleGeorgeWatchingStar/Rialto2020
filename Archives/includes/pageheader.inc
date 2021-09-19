<?php

function page_header($Title, $NoMenu=false, $IsIndex=false)
{
	// Titles and screen header 
	global $db_connections, $path_to_root;
	
	$local_path_to_root = $path_to_root;
	
	if (!isset($NoMenu)) {
		$NoMenu = false;
	}

    echo "<html dir='" . $_SESSION['language']->dir . "' >";
    echo "<head><title>" . _($Title) . "</title>";
    $encoding = $_SESSION['language']->encoding;
	echo "<meta http-equiv='Content-Type' content='text/html'; charset='" . $encoding . "'>";    
	echo "<link href='$local_path_to_root/lang/en_UK/LC_FILES/themes/default/default.css' rel='stylesheet' type='text/css' /> \n";
//	echo "<link href='$local_path_to_root/" . $_SESSION['language']->getStylesheet() . "' rel='stylesheet' type='text/css' /> \n";
	echo "</head> \n";
	echo "<body>";


    ?>
    <table class="callout_main" border="0" cellpadding="0" cellspacing="0">
        <tr>
            <td colspan="2" rowspan="2">
    
    		<table class="main_page" border="0" cellpadding="0" cellspacing="0">
    		    <tr>
    				<td>
    				<table width="100%" border="0" cellpadding="0" cellspacing="0">
    				    <tr>
    						<td class="quick_menu">
    <?
	
	if (!$NoMenu) {
			echo "<table cellpadding=0 cellspacing=0 width='100%'><tr><td>";
			echo "<DIV class=tabs>";
			echo "<a href='$local_path_to_root/index.php?Application=orders". SID ."'>" . _("Sales") . "</a>";
			echo "<a href='$local_path_to_root/index.php?Application=AP". SID ."'>" . _("Purchases") . "</a>";
			echo "<a href='$local_path_to_root/index.php?Application=stock". SID ."'>" . _("Items and Inventory") . "</a>";								
			echo "<a href='$local_path_to_root/index.php?Application=manuf". SID ."'>" . _("Manufacturing") . "</a>";				
			echo "<a href='$local_path_to_root/index.php?Application=proj". SID ."'>" . _("Projects") . "</a>";
			echo "<a href='$local_path_to_root/index.php?Application=GL". SID ."'>" . _("Banking and General Ledger") . "</a>";
			echo "<a href='$local_path_to_root/index.php?Application=system". SID ."'>" . _("Setup") . "</a>";
			echo "</DIV>";
			
			echo "</td></tr></table>";
			
			echo "<table class=logoutBar>";
			echo "<tr><td>" . $db_connections[$_SESSION["OA_current_user"]->company]["name"] . " | " . $_SESSION["OA_current_user"]->name . "</td>";
			echo "<td class=logoutBarRight><a href='$local_path_to_root/access/logout.php?'>" . _("Logout") . "</a>";
			echo "</td></tr></table>";				
		}

	echo "</td></tr></table>";
	
	if ($Title AND !$NoMenu AND !$IsIndex) {
		echo "<center><table width='100%'><tr><td width='100%' class='titletext'>" . _("$Title") . "</td></tr></table></center>";
	}

	if (!$IsIndex)
		echo "<BR>";		
}

?>

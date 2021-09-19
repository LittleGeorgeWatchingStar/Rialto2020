<?php

function page($Title, $NoMenu=false, $IsIndex=false) {

	$title = $Title;
    	include("includes/header.inc");
//    	page_header($Title, $NoMenu, $IsIndex);
    }

function endpage($NoMenu=false, $IsIndex=false) {
    	$hide_menu = $NoMenu;
    	include( "includes/footer.inc");
//    	page_footer($NoMenu, $IsIndex);
    }

//---------------------------------------------------------------------------------

function startform()
{
	echo "<FORM METHOD='post' action=" . $_SERVER['PHP_SELF'] . ">";
}

//---------------------------------------------------------------------------------

function endform()
{
	echo "</FORM>";
}
?>


<?php

function CheckForRecursiveBOM ($UltimateParent, $ComponentToCheck, $db) {

/* returns true ie 1 if the BOM contains the parent part as a component
ie the BOM is recursive otherwise false ie 0

This was originally written with $db passed by reference, but I removed that // WGK
*/


	$sql = "SELECT Component FROM BOM WHERE Parent='$ComponentToCheck'";
	$ErrMsg = _('An error occurred in retrieving the components of the BOM during the check for recursion');
	$DbgMsg = _('The SQL that was used to retrieve the components of the BOM and that failed in the process was');
	$result = DB_query($sql,$db,$ErrMsg,$DbgMsg);

	if ($result!=0) {
		while ($myrow=DB_fetch_row($result)){
			if ($myrow[0]==$UltimateParent){
				return 1;
			}

			if (CheckForRecursiveBOM($UltimateParent, $myrow[0],$db)){
				return 1;
			}
		} //(while loop)
	} //end if $result is true

	return 0;

} //end of function CheckForRecursiveBOM

function getBOM($SelectedParent, $db) {
	$sql = "SELECT	BOM.Component, BOM.ComponentVersion,
			StockMaster.Description, StockMaster.MBflag, StockMaster.CategoryID,
			WorkCentres.Description,
			WorkCentreAdded,
			Quantity,
			(Materialcost+Labourcost+Overheadcost) Unitcost,
			Quantity * (Materialcost+Labourcost+Overheadcost) Totalcost
		FROM BOM
		LEFT JOIN StockMaster ON BOM.Component=StockMaster.StockID
		LEFT JOIN WorkCentres ON BOM.WorkCentreAdded=WorkCentres.Code
		WHERE BOM.Parent='$SelectedParent'
		ORDER BY BOM.Component ASC";
	$ErrMsg = _('Could not retrieve the BOM components because');
	$DbgMsg = _('The SQL used to retrieve the components was');
	$result = DB_query($sql,$db,$ErrMsg,$DbgMsg);
//	echo 'Found ' . DB_num_rows( $result) . ' components<br>';
	return $result;
}

function DisplayBOMItems($SelectedParent, $db, $fileBOM ) {
	$sql = "SELECT BOM.Component,
			StockMaster.Description,
			WorkCentres.Description,
			Quantity," .
		"	BOM.LocCode,
			BOM.WorkCentreAdded,
			BOM.ComponentVersion,
                        BOM.ParentVersion 
		FROM BOM
		LEFT JOIN StockMaster ON BOM.Component=StockMaster.StockID
		LEFT JOIN WorkCentres ON BOM.WorkCentreAdded=WorkCentres.Code
		WHERE BOM.Parent='$SelectedParent'
		ORDER BY BOM.Component ASC";
	$ErrMsg = _('Could not retrieve the BOM components because');
	$DbgMsg = _('The SQL used to retrieve the components was');
	$result = DB_query($sql,$db,$ErrMsg,$DbgMsg);
	echo "<CENTER><table border=1>";
	$TableHeader =  '<tr BGCOLOR =#800000>
			 <td class=tableheader>' . _('Code') . '</td>
                         <td class=tableheader>' . _('ParentVer') . '</td>
			 <td class=tableheader>' . _('Description') . '</td>
			 <td class=tableheader>' . _('Work Centre') . '</td>
			 <td class=tableheader>' . _('Quantity') . '</td>
                         <td class=tableheader>' . _('Version') . '</td>' .
//			'<td class=tableheader>' . _('Excl') . '</td>'.
//                      '<td class=tableheader>' . _('Subs') . '</td>'.
//			'<td class=tableheader>' . _('Vers') . '</td>'.
		'</tr>';
	echo $TableHeader;
	$RowCounter =0;
	while ($myrow=DB_fetch_row($result)) {
		$Component	= $myrow[0];
		$Center		= $myrow[5];
		$LocCode	= $myrow[4];
		if ($k==1){
			echo "<tr bgcolor='#CCCCCC'>";
			$k=0;
		} else {
			echo "<tr bgcolor='#EEEEEE'>";
			$k++;
		}

		printf("<td>%s</td>
                        <td>%s</td>
			<td>%s</td>
			<td>%s</td>
			<td>%s</td>
			<td>%s</td>" .
			"<td><a href=\"%s&Select=%s&SelectedComponent=%s&Version=%s\">" . _('Edit') . "</a></td>
			 <td><a href=\"%s&Select=%s&SelectedComponent=%s&Version=%s&delete=1\">" . _('Delete') . "</a></td>
			 </tr>",
			$myrow[0],
                        $myrow[7],  //added to show parentVerison
			$myrow[1],
			$myrow[2],
			$myrow[3],
			$myrow[6],
			$_SERVER['PHP_SELF'] . '?' . SID,
			$SelectedParent,
			$myrow[0],
                        $myrow[7], //added to show parentVerison
			$_SERVER['PHP_SELF'] . '?' . SID,
			$SelectedParent,
			$myrow[0],
                        $myrow[7]); //added to show parentVerison
                $dbBOM[$myrow[0]] = $myrow[4];
		$RowCounter++;
		if ($RowCounter==20){
			echo $TableHeader;
			$RowCounter=0;
		}
	} //END WHILE LIST LOOP
	if (isset($fileBOM)) {
		printf("<tr  bgcolor='#BBDDCC'>
			<td  colspan=10 CENTER >
			The following items appear in the file $SelectedParent.bom.csv but do not appear in the webERP database.</td></tr>");
		foreach ( $fileBOM as $itemID => $itemQty ) {
			if (!isset($dbBOM[$itemID]) ) {
				printf("<tr><td>$itemID</td><td></td><td></td><td></td><td></td><td>$itemQty</td><td></td><td></td><td></td></tr>");
			}
		}
	}
}

//---------------------------------------------------------------------------------


function hasBOM($itemToCheck, $db)
{
    $BOMResult = getBOM($itemToCheck, $db);
    
    if (DB_num_rows($BOMResult)==0){
    	return false;
    } 	
    
    return true;
}

//--------------------------------------------------------------------------------------



?>

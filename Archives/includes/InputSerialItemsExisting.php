<?php

/* $Revision: 1.2 $ */

use Rialto\SecurityBundle\Entity\User;
/**
 If the User has selected Keyed Entry, show them this special select list...
 it is just in the way if they are doing file imports
 it also would not be applicable in a PO and possible other situations...
 **/
if ($_POST['EntryType'] == 'KEYED'){
    /*Also a multi select box for adding bundles to the dispatch without keying */
    $sql = "SELECT SerialNo, Quantity, Version FROM StockSerialItems WHERE StockID='" . $StockID . "' AND LocCode ='" .
    $LocationOut."' AND Quantity > 0";
    //echo $sql;

    $ErrMsg = '<BR>'. _('Could not retrieve the items for'). ' ' . $StockID;
    $Bundles = DB_query($sql,$db, $ErrMsg );
    echo '<TABLE><TR>';
    if (DB_num_rows($Bundles)>0){
        $AllSerials=array();
        foreach ($LineItem->SerialItems as $Itm){ $AllSerials[$Itm->BundleRef] = $Itm->BundleQty; }
        echo '<TD VALIGN=TOP><B>'. _('Select Existing Items'). '</B><BR>';

        echo '<FORM ACTION="' . $_SERVER['PHP_SELF'] . '?=' . $SID . '" METHOD="POST">
            <input type=hidden name=LineNo value="' . $LineNo . '">
            <input type=hidden name=StockID value="' . $StockID . '">
            <input type=hidden name=EntryType value="KEYED">
			<input type=hidden name=EditControlled value="true">
			<SELECT Name=Bundles[] multiple>';

        $id=0;
        $ItemsAvailable=0;
        while ($myrow=DB_fetch_array($Bundles,$db)){
            $version_str = ($myrow['Version']!='') ? ( ' ('.$myrow['Version'].')')  : ('');
            if ($LineItem->Serialised==1){
                if ( !array_key_exists($myrow['SerialNo'], $AllSerials) ){
                    echo '<OPTION VALUE="' . $myrow['SerialNo'] . '">' . $myrow['SerialNo'] . $version_str  .'</OPTION>';
                    $ItemsAvailable++;
                }
            } else {
                if ( !array_key_exists($myrow['SerialNo'], $AllSerials)  ||
                //                                        ($myrow['Quantity'] - $AllSerials[$myrow['SerialNo']] >= 0) ) {
                ($myrow['Quantity'] + $AllSerials[$myrow['SerialNo']] >= 0) ) {
                    //					$RecvQty = $myrow['Quantity'] - $AllSerials[$myrow['SerialNo']];
                    $RecvQty = $myrow['Quantity'] + $AllSerials[$myrow['SerialNo']];
                    echo '<OPTION VALUE="' . $myrow['SerialNo'] . '/|/'. $RecvQty . '/|/'. $myrow['Version'] . '">' .
                    $myrow['SerialNo'].' - ' . _('Qty left'). ': ' . $RecvQty . $version_str .'</OPTION>';
                    $ItemsAvailable += $RecvQty;
                }
            }
        }
        echo '</SELECT><br>';
        echo '<br><center><INPUT TYPE=SUBMIT NAME="AddBatches" VALUE="'. _('Enter'). '"></center><BR>';
        echo $ItemsAvailable . ' ' . _('items available');
        echo '</TD>';
        $PackageType = getPackageType($StockID, $db);
        //		echo '<tr><td>' . $StockID . ' appears to be a ' .$PackageType . '</td></tr>';
        $mult = PartsPer_cm($PackageType);
        if ($mult === false ) {
            echo '<tr><td>' . $PackageType . ' has no multiplier.</td></tr>';
        } else {
            //			echo '<tr><td>' . ' and 1 cm has ' . number_format($mult,2) . ' units'. '</td></tr>';
            echo '<tr><td>' . $PackageType . ' ' . number_format($mult * $_POST['Centimeters'],2) . '</td></tr>';
            echo '<tr><td>';
        }
        Input_Text("Centimeters","Centimeters",$_POST['Centimeters'],6,true);
        echo '</td></tr>';
        echo '</FORM>';
    } else {
        echo '<TD>'. prnMsg( _('There does not appear to be any of') . ' ' . $StockID . ' ' . _('left in'). ' '. $LocationOut , 'warn') . '</TD>';
    }

    echo '</TR></TABLE>';
}

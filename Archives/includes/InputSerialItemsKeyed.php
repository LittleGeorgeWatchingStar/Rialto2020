<?php
/* $Revision: 1.4 $ */
/*Input Serial Items - used for inputing serial numbers or batch/roll/bundle references
 for controlled items - used in:
 - ConfirmDispatchControlledInvoice.php
 - GoodsReceivedControlled.php
 - CreditItemsControlled.php

 */

//we start with a batch or serial no header and need to display something for verification...
global $tableheader;

if (isset($_GET['LineNo'])){
    $LineNo = $_GET['LineNo'];
} elseif (isset($_POST['LineNo'])){
    $LineNo = $_POST['LineNo'];
}

/*Display the batches already entered with quantities if not serialised */

echo '<TABLE border=1 width=40%><TR><TD valign=top  align=center>';
echo '<TABLE>';		//	BEGIN NESTED TABLE ON THE LEFT
echo $tableheader;

$TotalQuantity = 0; /*Variable to accumulate total quantity received */
$RowCounter =0;

foreach ($LineItem->SerialItems as $Bundle){

    if ($RowCounter == 10){
        echo $tableheader;
        $RowCounter =0;
    } else {
        $RowCounter++;
    }

    if ($k==1){
        echo '<tr bgcolor="#CCCCCC">';
        $k=0;
    } else {
        echo '<tr bgcolor="#EEEEEE">';
        $k=1;
    }

    echo '<TD>' . $Bundle->BundleRef . '</TD>';

    if ($LineItem->Serialised==0){
        echo '<TD ALIGN=RIGHT>' . number_format($Bundle->BundleQty, $LineItem->DecimalPlaces) . '</TD>';
        echo '<TD ALIGN=RIGHT>' . $Bundle->BinStyle . '</TD>';
        echo '<TD ALIGN=RIGHT>' . $LineItem->VersionReference . '</TD>';
    }

    echo '<TD><A HREF="' . $_SERVER['PHP_SELF'] . '?' . SID . 'Delete=' . $Bundle->BundleRef . '&StockID=' . $LineItem->StockID . '&LineNo=' . $LineNo .'">'. _('Delete'). '</A></TD></TR>';

    $TotalQuantity += $Bundle->BundleQty;
}

/*Display the totals and rule off before allowing new entries */
if ($LineItem->Serialised==1){
    echo '<TR><TD ALIGN=RIGHT><B>'. _('Total').  number_format($TotalQuantity,$LineItem->DecimalPlaces) . '</B></TD></TR>';
} else {
    echo '<TR><TD ALIGN=RIGHT><B>'. _('Total'). '</B></TD><TD ALIGN=RIGHT><B>' . number_format($TotalQuantity,$LineItem->DecimalPlaces) . '</B></TD></TR>';
}
echo '</TABLE></TD>';	 //      END NESTED TABLE ON THE LEFT

/*Start a new table for the Serial/Batch ref input  in one column (as a sub table
 then the multi select box for selection of existing bundle/serial nos for dispatch if applicable*/
/*in the first column add a table for the input of newies */

echo '<TD valign=top align=center>';
echo '<TABLE>'; 	//      BEGIN NESTED TABLE ON THE RIGHT

echo $tableheader;

echo '<FORM ACTION="' . $_SERVER['PHP_SELF'] . '?=' . $SID . '" METHOD="POST">
      <input type=hidden name=LineNo value="' . $LineNo . '">
      <input type=hidden name=StockID value="' . $StockID . '">
      <input type=hidden name=EntryType value="KEYED">';
if ( isset($_GET['EditControlled']) ) {
    $EditControlled = isset($_GET['EditControlled'])?$_GET['EditControlled']:false;
} elseif ( isset($_POST['EditControlled']) ){
    $EditControlled = isset($_POST['EditControlled'])?$_POST['EditControlled']:false;
}
$StartAddingAt = 0;

if ($EditControlled){
    foreach ($LineItem->SerialItems as $Bundle){

        echo '<TR>';

        /* if the item is controlled not serialised - batch quantity required so
         * just enter bundle refs into the form for entry of quantites manually */
        echo '<TD valign=top><input type=text name="SerialNo'. $StartAddingAt .'" value="'.$Bundle->BundleRef.'" size=21  maxlength=20></td>';
        if ($LineItem->Serialised==1){
            echo '<input type=hidden name="Qty' . $StartAddingAt .'" Value=1>';
        } else {
            echo '<TD><input type=text name="Qty' . $StartAddingAt .'" size=11
				value="'. number_format($Bundle->BundleQty, $LineItem->DecimalPlaces). '" maxlength=10>';
            echo '<TD><input type=text name="BinStyle' . $StartAddingAt .'" size=11
                value="'. $Bundle->BinStyle . '" maxlength=10>';
        }
        //		echo '<TD valign=top><input type=text name="SerialNo'. $StartAddingAt .'" value="'.$Bundle->BundleRef.'" size=21  maxlength=20></td>';
        echo '<TD valign=top><input type=text name="Version'. $StartAddingAt .'" value="'.$LineItem->VersionReference.'" size=6  maxlength=20></td>';
        echo '</TR>';

        $StartAddingAt++;
    }
}

$default_bin_style = get_BinStyle( $LineItem->StockID, $db, 3 );

for ($i=0;$i < 10;$i++){
    echo '<TR>';
    /*if the item is controlled not serialised - batch quantity required so just enter bundle refs
     into the form for entry of quantites manually */
    if ($LineItem->Serialised==1){
        echo '<input type=hidden name="Qty' . ($StartAddingAt+$i) .'" Value=1>';
        echo '<TD valign=top><input type=text name="SerialNo'. ($StartAddingAt+$i) .'" size=21  maxlength=20></td>';
    } else {
        echo '<TD>' . '<I></I>' . '</TD>';
        echo '<TD><input type=text name="Qty'      . ($StartAddingAt+$i) .'" size=11  maxlength=10></td>';
        echo '<TD>';
        BinStyleSelect( 'Color', 'BinStyle' . ($StartAddingAt+$i), $default_bin_style );
        echo '</td>';
    }
    echo '<TD>' . '<input type=text name="Version'      . ($StartAddingAt+$i) .'" size=11  maxlength=10 value="' . $LineItem->VersionReference . '"></td>';
    echo '</TR>';
}

echo '</table>';	//      END NESTED TABLE ON THE RIGHT
echo '<center><INPUT TYPE=SUBMIT NAME="AddBatches" VALUE="'. _('Enter'). '">';
if ($ShowExisting){
    echo '<td valign=top>';
    include('includes/InputSerialItemsExisting.php');
    echo '</td>';
}
echo '</TD></TR></TABLE>'; /*end of parent table */
?>

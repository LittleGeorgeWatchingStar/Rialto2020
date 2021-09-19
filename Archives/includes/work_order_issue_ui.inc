<?php

//--------------------------------------------------------------------------------

function AddToOrder(&$Order, $NewItem, $NewItemQty, $StandardCost)
{
    $AlreadyOnThisOrder = 0;

	foreach ($Order->LineItems AS $OrderItem) {
        if (strcasecmp($OrderItem->StockID, $NewItem)==0) {
            $AlreadyOnThisOrder = 1;
            displayError(_("For Part :") . $NewItem . " " . "This item is already on this order.  You can change the quantity ordered of the existing line if necessary.");
        }
	}

    if ($AlreadyOnThisOrder!=1){
         $Order->add_to_cart ($NewItem, $NewItemQty, $StandardCost);
    } /* end of if not already on the order */
}


//---------------------------------------------------------------------------------

function adjustment_options_controls()
{
	echo "<BR><TABLE>";

    RefInput_TableRow(_("Reference:"), 'Ref', references::get_next(28));

	if (!isset($_POST['IssueType']))
		$_POST['IssueType'] = 0;

	YesNoList_TableRow(_("Type:"), 'IssueType', $_POST['IssueType'],
					_("Return Items to Location"), _("Issue Items to Work Order"));

	locationList_TableRow(_("From Location:"), 'Location', $_POST['Location']);
	workCenterList_TableRow(_("To Work Centre:"), 'WorkCentre', $_POST['WorkCentre']);

	DateInput_TableRow(_("Issue Date:"), 'Date');

	TextArea_TableRow(_("Memo:"), 'Memo', $_POST['Memo'], 50, 3);

	echo "</TABLE>";
}


//---------------------------------------------------------------------------------

?>

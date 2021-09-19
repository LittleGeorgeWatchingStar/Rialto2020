<?php

//---------------------------------------------------------------------------------

function Input_Submit($name, $value, $echo=true)
{
	$submit_str = "<INPUT TYPE=SUBMIT class='inputsubmit' Name=$name VALUE='$value'>"; 
	if ($echo)
		echo $submit_str;
	else
		return $submit_str;
}

//---------------------------------------------------------------------------------

/**
 * @deprecated Use gumstix/erp/forms/ classes for form generation.
 * @param string $name  The input name
 * @param string $value  The input value
 */
function Input_Hidden($name, $value)
{
	echo "<INPUT TYPE=HIDDEN Name=$name VALUE='" . $value . "'>";
}

//---------------------------------------------------------------------------------

function Input_Submit_TableCells($name, $value)
{
	echo "<TD>";
	Input_Submit($name, $value);
	echo "</TD>";
}

function check_to_bool($value) {
	if ($value=="on" OR $value==1)
		return 1;
	else
		return 0;
}

function Input_Text($label, $name, $value='', $maxlength=10)
{
	echo "$label: <input type='text' name='$name' maxlength='$maxlength' value='$value'>";
}

/* 

'choices' is an array of ['description'] ['qty'] and ['grnno']

*/

function Input_GRN_Option($name, $choices, $selected='')
{
        echo '<select name="' . $name . '">';
        foreach($choices as $choice )
        {
                echo '<option value="' . $choice['grnno'] . '"';
                if( !empty($selected) &&  ( $selected['description'] == $choice['description'])   &&  ((int)$selected['qty']==(int)$choice['qty'])  ) {
                        echo " SELECTED ";
		}
                echo '>' .  $choice['description'] .  (  ($choice['qty'] != 0) ? (' (' .$choice['qty'] . ') ') : ('') ) .  '</option>';
        }
        echo "</select>";
}

function Input_Option($label, $name, $choices, $selected='')
{
	echo "<select name='$name'>";
	foreach ($choices as $option => $value)
	{
		echo "<option value='$value'";
		if(!empty($selected) && $selected == $option)
		{
			echo " selected";
		}
		echo ">$option</option>";
	}
	echo "</select>";
}

function Input_Option_SQL_Date($label, $db, $name, $selected='', $submitOnChange=false)
{
	echo $label;
        echo "<select name='$name' ";
        if ($submitOnChange) {
                echo " onclick='this.form.submit();' ";
	}
	echo ">";

	$sql = 'SELECT PeriodNo, LastDate_In_Period FROM Periods ORDER BY LastDate_In_Period DESC';
	$Periods = DB_query($sql,$db);
	while ($myrow=DB_fetch_array($Periods,$db)){
		$value= substr($myrow['LastDate_In_Period'],0,7);
		$option=  ConvertSQLDate($myrow['LastDate_In_Period']);
                echo "<option value='$value'";
                if(!empty($selected) && $selected == $value )
                {
                        echo " selected";
                }
                echo ">$option</option>";
        }
        echo "</select>";
}


function Input_Check_String($label, $name, $value, $submitOnChange=false)
{
	$to_echo = "";
        if ($label)
                $to_echo .= $label . "  ";

        if ($value=="on" OR $value==1 OR ($value==true AND $value != "off"))
                $to_echo .= '<input checked type="checkbox" name="' . ( $name) . '" ';
        else
                $to_echo .= '<input type="checkbox" name="' . ( $name) . '" ';
        if ($submitOnChange)
                $to_echo .= " onclick='this.form.submit();' ";
        $to_echo .= " >";
	return $to_echo;
}

function Input_PreCheck($label, $name, $value, $submitOnChange=false)
{
	if ($value=="on" OR $value==1 OR ($value==true AND $value != "off"))
		echo "<input checked type='checkbox' name=$name ";
	else
		echo "<input type='checkbox' name=$name ";	
	if ($submitOnChange)
		echo " onclick='this.form.submit();' ";
	echo " >";	
	if ($label)
		echo $label . "  ";
}

function Input_Check($label, $name, $value, $submitOnChange=false)
{
	if ($label)
		echo $label . "  ";
			
	if ($value=="on" OR $value==1 OR ($value==true AND $value != "off"))
		echo "<input checked type='checkbox' name=$name ";
	else
		echo "<input type='checkbox' name=$name ";	
	if ($submitOnChange)
		echo " onclick='this.form.submit();' ";
	echo " >";	
}
/*
function Input_Check_String($label, $name, $value, $submitOnChange=false) {
	$to_return = '';
        if ($label)
                $to_return .= $label . "  ";

        if ($value=="on" OR $value==1 OR ($value==true AND $value != "off"))
                $to_return .= "<input checked type='checkbox' name=$name ";
        else
                $to_return .= "<input type='checkbox' name=$name ";
        if ($submitOnChange)
                $to_return .= " onclick='this.form.submit();' ";
        $to_return .= " >";
	return $to_return;
}
*/

function DisallowDoubleChecks() {
        $box_counter = array();
        if ( is_array($_POST['BoxesList'])) foreach ( $_POST['BoxesList'] as $this_match ) {
                list ( $this_ref, $this_inv )  = split( '-' , $this_match );
                if ( isset( $_POST[$this_match] ) ) {
                        $box_counter[$this_ref] ++;
                }
        }

        foreach ( $box_counter as $test_ref => $test_count ) {
                if ( $test_count >1  ) {
                        foreach ( $_POST['CheckedBoxesList'] as $this_match ) {
                                list ( $this_ref, $this_inv )  = split( '-' , $this_match );
                                if ( $this_ref == $test_ref) {
                                        unset ( $_POST[$this_match] );
                                }
                        }
                }
        }
}

function Input_Check_TableCells_String($label, $name, $value, $submitOnChange=false)
{
        return "<TD>$label</TD><TD>" .  Input_Check_String(null, $name, $value, $submitOnChange) . "</TD>";
}

function Input_PreCheck_TableCells_String($label, $name, $value, $submitOnChange=false)
{
        return "<TD width=2%>" .  Input_Check_String(null, $name, $value, $submitOnChange) . "</TD> <TD>$label</TD>";
}

function Input_Check_TableCells($label, $name, $value, $submitOnChange=false)
{
	echo "<TD>$label</TD><TD>";
	Input_Check(null, $name, $value, $submitOnChange);
	echo "</TD>";
}

function Input_Check_TableRow($label, $name, $value, $submitOnChange=false)
{
	echo "<TR>";
	Input_Check_TableCells($label, $name, $value, $submitOnChange);
	echo "</TR>";
}

function CheckedBox($box_name)
{
	$value = $_POST[$box_name];
	return ($value=="on" OR $value==1 OR ($value==true AND $value != "off"));
}

//-----------------------------------------------------------------------------------

function input_submit_add_or_update($add=true)
{
	if ($add) 
		Input_Submit('ADD_ITEM', _("Save"));
	else
		Input_Submit('UPDATE_ITEM', _("Update"));	
}

//-----------------------------------------------------------------------------------

/**
 * @deprecated  New WeberpForm object-based approach is preferred.
 * @param string $label
 *        The label that appears next to the input
 * @param string $controlName
 *        The input name
 * @param string $value
 *        The input value
 * @param int $size
 *        The input size
 * @param int $maxLength
 *        The input maxlength value
 * @param string $params
 *        (optional) Attributes for the enclosing td tag
 * @param string $postLabel
 *        (optional) A label to appear after the input
 */
function TextInput_TableRow($label, $controlName, $value, $size, $maxLength, $params="", $postLabel="")
{
	echo "<TR>";
	
	TextInput_TableCells($label, $controlName, $value, $size, $maxLength, $params, $postLabel);	
	
	echo "</TR>";	
}

//-----------------------------------------------------------------------------------

function TextInput_TableRowEx($label, $controlName, $size, $maxLength=null, $value=null, $params=null, $postLabel=null)
{
	echo "<TR>";
	
	TextInput_TableCellsEx($label, $controlName, $size, $maxLength, $value, $params, $postLabel);	
	
	echo "</TR>";	
}

//-----------------------------------------------------------------------------------

function DateInput_TableRow($label, $controlName, $initVal=null, $incDays=0, $incMonths=0, $incYears=0)
{
	echo "<TR>";
	DateInput_TableCells($label, $controlName, $initVal, $incDays, $incMonths, $incYears);	
	echo "</TR>";
}

//-----------------------------------------------------------------------------------

function DateInput_TableCells($label, $controlName, $initVal=null, $incDays=0, $incMonths=0, $incYears=0, $params=null)
{
	global $DefaultDateFormat;
	
	if (!isset($_POST[$controlName]) or $_POST[$controlName]=="") {
		if (!$initVal)
			$_POST[$controlName] = Date($DefaultDateFormat, Mktime(0,0,0,Date("m") + $incMonths,Date("d") + $incDays,Date("Y") + $incYears));		
		else
			$_POST[$controlName] = $initVal; 
	}
		
	TextInput_TableCellsEx($label, $controlName, 12, 12, $_POST[$controlName]);	
}

//-----------------------------------------------------------------------------------

function RefInput_TableCells($label, $controlName, $initVal=null, $params=null)
{
	TextInput_TableCellsEx($label, $controlName, 16, 18, $initVal, $params);	
}

//-----------------------------------------------------------------------------------

function RefInput_TableRow($label, $controlName, $initVal=null)
{
	echo "<TR>";
	RefInput_TableCells($label, $controlName, $initVal);
	echo "</TR>";
}

//-----------------------------------------------------------------------------------

function PercentInput_TableRow($label, $controlName, $initVal=null)
{
	
	if (!isset($_POST[$controlName]) or $_POST[$controlName]=="") {
		if ($initVal)
			$_POST[$controlName] = $initVal; 
	}
		
	TextInput_TableRow($label, $controlName, $_POST[$controlName], 6, 6, "", "%");	
}

//-----------------------------------------------------------------------------------

function AmountInput_TableRow($label, $controlName, $initVal=null, $params=null, $postLabel=null)
{
	echo "<TR>";
	AmountInput_TableCells($label, $controlName, $initVal, $params, $postLabel);
	echo "</TR>";	
}

//-----------------------------------------------------------------------------------

function AmountInput_TableCells($label, $controlName, $initVal=null, $params=null, $postLabel=null)
{
	TextInput_TableCellsEx($label, $controlName, 15, 15, $initVal, $params, $postLabel);	
}

function SmallAmountInput_TableCells($label, $controlName, $initVal=null, $params=null, $postLabel=null)
{
	TextInput_TableCellsEx($label, $controlName, 7, 12, $initVal, $params, $postLabel);	
}

//-----------------------------------------------------------------------------------

function TextInput_TableCellsEx($label, $controlName, $size, $maxLength=null, $initVal=null, $params=null, $postLabel=null)
{
	if (!isset($_POST[$controlName]) or $_POST[$controlName]=="") {
		if ($initVal)
			$_POST[$controlName] = $initVal; 
	}
		
	if ($label)
		echo "<TD $params>$label</TD>";
		
	if (!isset($maxLength)) 
		$maxLength = $size;		

	echo "<TD>";	
	
	echo "<INPUT TYPE=text NAME=$controlName SIZE=$size MAXLENGTH=$maxLength Value='" . $_POST[$controlName]. "'>";
	
	if ($postLabel)
		echo " " . $postLabel; 

	echo "</TD>";	
}

//-----------------------------------------------------------------------------------

function Text_TableRow($label, $value, $params="", $params2="")
{
	echo "<TR>";	
	echo "<TD $params>" . $label . "</TD>";
	echo "<TD $params2>" . $value . "</TD></TR>";		
}

//-----------------------------------------------------------------------------------

function TextArea_TableRow($label, $controlName, $value, $cols, $rows, $params="")
{
	echo "<TR>";	
	echo "<TD $params>" . $label . "</TD>";
	echo "<TD><TEXTAREA NAME=$controlName COLS=$cols ROWS=$rows>" . $value . "</TEXTAREA></TD>";
	echo "</TR>";		
}

//-----------------------------------------------------------------------------------

function Text_TableCells($label, $value, $params="", $params2="")
{
	echo "<TD $params>" . $label . "</TD>";
	echo "<TD $params2>" . $value . "</TD>";		
}

//-----------------------------------------------------------------------------------
/**
 * @deprecated  New WeberpForm object-based approach is preferred.
 * @param string $label
 *        The label that appears next to the input
 * @param string $controlName
 *        The input name
 * @param string $value
 *        The input value
 * @param int $size
 *        The input size
 * @param int $maxLength
 *        The input maxlength value
 * @param string $params
 *        (optional) Attributes for the enclosing td tag
 * @param string $postLabel
 *        (optional) A label to appear after the input
 */
function TextInput_TableCells($label, $controlName, $value, $size=12, $maxLength=30, $params="", $postLabel="")
{
	if ($params != "") {
		echo "<TD $params>" . $label . "</TD>";
	} else {
		if ( $label != "") {
			echo "<TD>" . $label . "</TD>";
		}
	}
	echo "<TD>";	
	
	echo "<INPUT TYPE=text NAME=$controlName SIZE=$size MAXLENGTH=$maxLength Value='" . $value . "'>";
	
	if ($postLabel != "")
		echo " " . $postLabel; 

	echo "</TD>";	
}

//-----------------------------------------------------------------------------------
function TextInput_TableCells_NoEcho($label, $controlName, $value, $size, $maxLength, $params="", $postLabel="")
{
	$toReturn = "";
	if ($params != "") {
		$toReturn .= "<TD $params>" . $label . "</TD>";
	} else {
		if ( $label != "") {
			$toReturn .=  "<TD>" . $label . "</TD>";
		}
	}
	$toReturn .=  "<TD>";	
	
	$toReturn .=  "<INPUT TYPE=text NAME=$controlName SIZE=$size MAXLENGTH=$maxLength Value='" . $value . "'>";
	
	if ($postLabel != "")
		$toReturn .=  " " . $postLabel; 

	$toReturn .=  "</TD>";
	return $toReturn;
}


//-----------------------------------------------------------------------------------


function TextInput_TableRowWithInputSubmit($label, $controlName, $value, $size, $maxLength, $input_Name, $input_Value)
{
	echo "<TR><TD>" . $label . "</TD>";
	echo "<TD>";	
	
	echo "<INPUT TYPE=text NAME=$controlName SIZE=$size MAXLENGTH=$maxLength Value='" . $value . "'>   ";

	Input_Submit($input_Name, $input_Value);
	
	echo "</TD></TR>";	
}

//-----------------------------------------------------------------------------------


?>

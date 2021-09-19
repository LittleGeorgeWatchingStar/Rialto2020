<?php
/* $Revision: 1.8 $ */
$PageSecurity = 9;

include_once('includes/session.inc');
$title = _('Forms Management');
include('includes/header.inc');
include('includes/DateFunctions.inc');
include_once("includes/CommonGumstix.inc");
include_once("includes/WO_ui_input.inc");

//---------------------------------------------------------------------------------
echo "
<form action='' method='post' enctype='multipart/form-data'>
<center><table BORDER=1 CELLPADDING=1 ><tr>
<td>New form to upload</td>
<td><input type='file' name='uploading' 
";
Input_Submit("Upload","Upload");
echo "</td></tr>";
echo "Need to check for existing form before uploading and then to add the Form to the DB";

if (isset($_POST['FormID'])) {
	$filename = 'Forms/' . $_POST['FormID'];
	if (substr($filename,-4)!='.pdf') {
		$filename .= '.pdf';
	}

	if (is_file($filename)) {
		$fn = popen("/usr/bin/listpdffields.pl $filename" , 'r');
		while (!feof($fn)) {
			$psdata .= fread($fn, 1);
		}
		fclose($fn);
	
		$psdata = preg_replace("/\(.+\)/", "", $psdata );
		$field_list = preg_split("/[\s,]+/",$psdata);
	}
}
$field_count = count($field_list);


$sql = "SELECT * FROM Forms WHERE FormID='" . $_POST['FormID'] . "'";
$ret = DB_query($sql, $db);
if (DB_num_rows($ret)==1) {
	foreach ($field_list as $field_id) {
		$command = "('" . $_POST['FormID'] . "', '" . addslashes($field_id) . "')  ";
		$sql = "INSERT INTO Forms (FormID,FormField) VALUES $command";
		echo $sql;
		$ret = DB_query($sql, $db);
	}
}
echo "</td></tr>";

echo "<tr><td colspan=2><center>";
if (isset($_POST['Upload'])) {
	if ($error == UPLOAD_ERR_OK) {
		$error=0;
	        $tmp_name = $_FILES["uploading"]["tmp_name"];
	        $filename = $_FILES["uploading"]["name"];
		if (substr($filename,-4)!='.pdf') {
			echo 'Not a pdf file';
		} else {
			$_POST['FormID'] = $filename;
			$filename = 'Forms/' . $_POST['FormID'];
			echo "Moving $tmp_name to $filename";
			move_uploaded_file($tmp_name,$filename);
		}		
        } else {
               echo "Error $tmp_name to $filename";
 	}
}
echo "</td></tr>";


$choices = array();
$sql = "SELECT DISTINCT FormID FROM Forms";
$ret = DB_query($sql,$db);
while ($myrow=DB_fetch_array($ret)) {
	$choices[$myrow['FormID']]=$myrow['FormID'];
}

echo "<tr><td>Select existing form</td><td>";
Input_Option("Form", "FormID", $choices, $_POST['FormID']);
echo "</td></tr>";

echo "<tr><td><center>";
Input_Submit("ShowFields","ShowFields");
echo "</td><td>";
if (isset($_POST['ShowFields']) && isset($_POST['FormID'])) {
	$limit = 0;
	foreach ($field_list as $field_id) {
		if (strpos($field_id, "&") === false ) {
			$command .= addslashes($field_id) . ' ' . addslashes($field_id) . ' ';
		}
	}
	$cmded = "/usr/bin/fillpdffields.pl $filename $filename.xpdf $command";
	if (count($field_list) > 1) {
		system($cmded);
		echo "<A target='_blank' HREF='$filename.xpdf'>$filename</A>";
	} else {
		echo "no fields to fill.";
	}
	unset($_POST['ShowFields']);
}
echo "</td></tr>";

$newRows	= 0;
$skippedRows	= 0;
$psdata = '';

echo "<tr><td><center>";
if (isset($_POST['FormID'])) {
	Input_Submit("FillForm","FillForm");
	echo '</td>';
	if (isset($_POST['FillForm'])) {
		$limit = 0;
		$cmded = "/www/weberp.gumstix.com/forms.pl " . $_POST['FormID'] ;
		system($cmded);
		echo "<td><A target='_blank' HREF='Forms/compx." . $_POST['FormID'] . "'>" . $_POST['FormID'] . "</A></td>";
		unset($_POST['FillForm']);
	}
}
echo '</tr>';



if (  count($field_list)>0) {
	echo "<tr><td colspan=2><center>";
	if ( isset($_POST['EditFields']) ) {
		Input_Submit("EditFields", "Reset" );
		echo "&nbsp &nbsp";
		Input_Submit("MakeChanges","MakeChanges");
	} else {
		Input_Submit("EditFields","EditFields");
	}
	echo "</td></tr>";
}

echo '</table>';
echo '<br>';


$lineNo=0;
if (isset($_POST['EditFields']) && isset($_POST['FormID']) && ( count($field_list)>0)  ) {
	echo '<table>';
	echo '<th>Field</th>';	
        echo '<th>Text</th>';
        echo '<th>Format</th>';
        echo '<th>Select</th>';
        echo '<th>Table</th>';
        echo '<th>Where</th>';
	$form_id = $_POST['FormID'];
        foreach ($field_list as $field_id) {
		if ($field_id != '') {
			echo '<TR>';
			$sql = "SELECT * FROM Forms WHERE FormID='$form_id' AND FormField='$field_id'";
			$res = DB_query($sql,$db);
			if ($my_field=DB_fetch_array($res)) {
				if (!isset($_POST['TXT_' . $field_id])) {
					$_POST['TXT_' . $field_id] = $my_field['Text'];
				}
                                if (!isset($_POST['FMT_' . $field_id])) {
                                        $_POST['FMT_' . $field_id] = $my_field['TextFormat'];
                                }
                                if (!isset($_POST['SEL_' . $field_id])) {
                                        $_POST['SEL_' . $field_id] = $my_field['ToSelect'];
                                }
                                if (!isset($_POST['FRM_' . $field_id])) {
                                        $_POST['FRM_' . $field_id] = $my_field['FromTable'];
                                }
				if (!isset($_POST['WHR_' . $field_id])) {
					$_POST['WHR_' . $field_id] = $my_field['WhereCriterion'];
				}
                        }
			TextInput_TableCells($field_id, 'TXT_' . $field_id, $_POST['TXT_' . $field_id], 50, 50);
                        TextInput_TableCells('', 'FMT_' . $field_id, $_POST['FMT_' . $field_id], 10, 10);
                        TextInput_TableCells('', 'SEL_' . $field_id, $_POST['SEL_' . $field_id], 20, 20);
                        TextInput_TableCells('', 'FRM_' . $field_id, $_POST['FRM_' . $field_id], 30, 30);
			TextInput_TableCells('', 'WHR_' . $field_id, $_POST['WHR_' . $field_id], 50, 50);
			echo '</TR>';
			$lineNo++;
		}
	}
        echo '</table>';
        unset($_POST['EditFields']);
}

$lineNo=0;
if (isset($_POST['MakeChanges']) && isset($_POST['FormID'])  ) {
	echo '<table>';
	echo '<th>Field</th>';	
        echo '<th>Text</th>';
        echo '<th>Format</th>';
        echo '<th>Select</th>';
        echo '<th>Table</th>';
        echo '<th>Where</th>';
        $form_id = $_POST['FormID'];

        foreach ($field_list as $field_id) {
		if ($field_id != '') {
			echo '<TR>';
                        TextInput_TableCells($field_id, 'TXT_' . $field_id, $_POST['TXT_' . $field_id], 50, 50);
                        TextInput_TableCells('', 'FMT_' . $field_id, $_POST['FMT_' . $field_id], 10, 10);
                        TextInput_TableCells('', 'SEL_' . $field_id, $_POST['SEL_' . $field_id], 20, 20);
                        TextInput_TableCells('', 'FRM_' . $field_id, $_POST['FRM_' . $field_id], 30, 30);
			TextInput_TableCells('', 'WHR_' . $field_id, $_POST['WHR_' . $field_id], 50, 50);
			echo '</TR>';
			$lineNo++;
                        if ($_POST['TXT_' . $field_id]!='') {
                                $sql = "UPDATE Forms SET Text='" . $_POST['TXT_' . $field_id] . "' WHERE FormField='$field_id' AND FormID='$form_id'";
                                DB_query($sql,$db);
                        }
                        if ($_POST['FMT_' . $field_id]!='') {
                                $sql = "UPDATE Forms SET TextFormat='" . $_POST['FMT_' . $field_id] . "' WHERE FormField='$field_id' AND FormID='$form_id'";
                                DB_query($sql,$db);
                        }
                        if ($_POST['SEL_' . $field_id]!='') {
                                $sql = "UPDATE Forms SET ToSelect='" . $_POST['SEL_' . $field_id] . "' WHERE FormField='$field_id' AND FormID='$form_id'";
                                DB_query($sql,$db);
                        }
                        if ($_POST['FRM_' . $field_id]!='') {
                                $sql = "UPDATE Forms SET FromTable='" . $_POST['FRM_' . $field_id] . "' WHERE FormField='$field_id' AND FormID='$form_id'";
                                DB_query($sql,$db);
                        }
			if ($_POST['WHR_' . $field_id]!='') {
				$sql = "UPDATE Forms SET WhereCriterion='" . $_POST['WHR_' . $field_id] . "' WHERE FormField='$field_id' AND FormID='$form_id'";
				DB_query($sql,$db);
			}
		}
	}
        echo '</table>';
        unset($_POST['MakeChanges']);
}

echo "
</form>
";

include('includes/footer.inc');
?> 

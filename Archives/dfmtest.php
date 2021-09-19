<?php
/* $Revision: 1.18 $ */
$PageSecurity = 2;
include('includes/session.inc');
$title = _('FreeDFM Link');

include('includes/header.inc');
/*

<form name="fileupload" action="https://www.freedfm.com/!freedfmstep2.asp" method="post" enctype="multipart/form-data" ID="Form2" onsubmit="return Validate()">

<table align=center ID="Table1" border=0>

<tr>
<td><input type="text" name="ContactEmail" size=30 value="gordon@gumstix.com" class="printed_circuit_form4"></td>
</tr>

<tr>
<td><input type="file" name="UploadFileData" class="printed_circuit_form4"></td>
</tr>

<tr>
<td align=center><input type="submit" value="Upload Zip File" id=submit1 name=submit1></td>
</tr>

</table>

</form>
*/

$filetorun = '/www/weberp.devstix.com/submitdfm.pl helper';
echo "Putting it here:<BR>";
echo shell_exec( $filetorun );

echo "<BR>Put it there.";
include("includes/footer.inc");
?>

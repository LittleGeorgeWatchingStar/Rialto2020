<?php

/* $Revision: 1.8 $ */
use Rialto\AccountingBundle\Entity\BankStatement;$PageSecurity = 9;

include_once('includes/session.inc');
$title = _('Bank Statement Uploads');
include('includes/header.inc');
include('includes/DateFunctions.inc');

//---------------------------------------------------------------------------------
echo "
<h1>Load bank statement</h1>
<form action='' method='post' enctype='multipart/form-data'>
<p>Transaction details in excel CSV format:
<input type='file' name='uploading' />
<input type='submit' value='Upload' />
</p>
</form>
";

if ( $error == UPLOAD_ERR_OK ) {
    $tmp_name = $_FILES["uploading"]["tmp_name"];
    $name = $_FILES["uploading"]["name"];
}
$newRows = 0;
$skippedRows = 0;

$handle = @fopen($tmp_name, "r");
if ( $handle !== false ) {
    $TransList = fgetcsv($handle, 1000, ",");
    while ( ($TransList = fgetcsv($handle, 1000, ",")) !== false ) {
        if ( ereg("[0-1][0-9]/[0-3][0-9]/[1-2][0,9][0-9][0-9]", $TransList[0]) ) {
            $BankDate = $TransList[0];
            $BankSQLDate = FormatDateForSQL($BankDate);
            $BankDesc = $TransList[1];
            $Amount = str_replace(',', '', $TransList[2]) *
                (((strpos($BankDesc, 'CREDIT') === false) && (strpos($BankDesc, 'DEPOSIT') === false)) ? -1 : 1);
            $BankRef = $TransList[3];
            $CustRef = $TransList[4];
            $BankText = $TransList[5];
            if ( ($CustRef == 0) && ($BankRef == 0) ) {
                $skippedRows ++;
            }
            else {
                $checkSQL = "	SELECT * FROM BankStatements
						WHERE BankPostDate='$BankSQLDate' AND Amount='$Amount'
							AND BankRef='$BankRef' AND CustRef='$CustRef'";
                $checkRes = DB_query($checkSQL, $db);
                if ( DB_num_rows($checkRes) == 0 ) {
                    $insertSQL = "INSERT INTO BankStatements (BankPostDate,Amount,BankRef,CustRef,BankDescription)VALUES ('$BankSQLDate','$Amount','$BankRef','$CustRef','$BankText')";
                    $insertRes = DB_query($insertSQL, $db, "Didn't work.");
                    $newRows ++;
                }
                else {
                    $skippedRows ++;
                }
            }
        }
    }

    fclose($handle);
    echo "Uploaded $newRows new transactions and skipped $skippedRows transactions.<br>";
    echo '<P><A HREF="/index.php/Accounting/BankStatement/match">' .
        _('Match off cleared transactions') . '</A>';
}

include('includes/footer.inc');


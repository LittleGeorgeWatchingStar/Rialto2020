<?php
/* $Revision: 1.2 $ */
use Rialto\AccountingBundle\Entity\Period;$PageSecurity=15;

include('includes/session.inc');
$title=_('Auditing the transactions');
include('includes/header.inc');

echo '<TABLE>';
echo "<TR class='tableheader'><TD COLSPAN=2>GLTRANS BY TYPE</TD></TR>";
$sql = "SELECT Type, COUNT(*) totalled FROM GLTrans WHERE GLTrans.Type IN (10,20,25,26,28,29) GROUP BY Type";
$recordCount = DB_query($sql,$db);
echo "<tr class='tableheader'><td>Type</td><td>Total</td></tr>";
while ($recordColumn = DB_fetch_array($recordCount)){
	echo "<tr><td>".$recordColumn['Type']."</td><td>".$recordColumn['totalled']."</td></tr>";
}
echo '</TABLE>';
echo '<BR>';

echo '<TABLE>';
echo "<TR class='tableheader'><TD COLSPAN=2>STOCKMOVES BY TYPE</TD></TR>";
$sql = "SELECT Type, COUNT(*) totalled FROM StockMoves WHERE StockMoves.Type IN (10,20,25,26,28,29) GROUP BY Type";
$recordCount = DB_query($sql,$db);
echo "<tr class='tableheader'><td>Type</td><td>Total</td></tr>";
while ($recordColumn = DB_fetch_array($recordCount)){
        echo "<tr><td>".$recordColumn['Type']."</td><td>".$recordColumn['totalled']."</td></tr>";
}
echo '</TABLE>';
echo '<BR>';

echo '<TABLE>';
echo "<TR class='tableheader'><TD COLSPAN=2>UNLINKED STOCKMOVES</TD></TR>";
$sql = "SELECT Type, COUNT(*) totalled FROM StockMoves WHERE StockMoves.Type IN (10,20,25,26,28,29) AND (GLTransDR=0 OR GLTransCR=0) AND Show_On_Inv_Crds=1 AND Narrative NOT LIKE 'DUP%' GROUP BY Type";
$recordCount = DB_query($sql,$db);
echo "<tr class='tableheader'><td>Type</td><td>Total</td></tr>";
while ($recordColumn = DB_fetch_array($recordCount)){
        echo "<tr><td>".$recordColumn['Type']."</td><td>".$recordColumn['totalled']."</td></tr>";
}
echo '</TABLE>';
echo '<BR>';

echo '<TABLE>';
echo "<TR class='tableheader'><TD COLSPAN=2>UNREFERENCED SOD IN GLTRANS</TD></TR>";
$sql = "SELECT Account, COUNT(*) totalled FROM GLTrans 
	INNER JOIN DebtorTrans ON DebtorTrans.Type = GLTrans.Type AND DebtorTrans.TransNo = GLTrans.TypeNo
	WHERE GLTrans.Type IN (10,20,25,26,28,29) AND GLTrans.Narrative NOT LIKE CONCAT(Order_,'%@%') GROUP BY Account";
$recordCount = DB_query($sql,$db);
echo "<tr class='tableheader'><td>Account</td><td>Total</td></tr>";
while ($recordColumn = DB_fetch_array($recordCount)){
        echo "<tr><td>".$recordColumn['Account']."</td><td>".$recordColumn['totalled']."</td></tr>";
}
echo '</TABLE>';
echo '<BR>';

echo '<TABLE>';
echo "<TR class='tableheader'><TD COLSPAN=4>NON-ZERO TRANSACTIONS</TD></TR>";
$Header = "<tr>
		<td class='tableheader'>" . _('Type') . "</td>
		<td class='tableheader'>" . _('Number') . "</td>
		<td class='tableheader'>" . _('Period') . "</td>
		<td class='tableheader'>" . _('Difference') . "</td>
		</tr>";
echo $Header;
$sql = 'SELECT GLTrans.Type,
		SysTypes.TypeName,
		GLTrans.TypeNo,
		PeriodNo,
		Sum(Amount) AS NetTot
	FROM GLTrans,
		SysTypes
	WHERE GLTrans.Type = SysTypes.TypeID
	GROUP BY GLTrans.Type,
		SysTypes.TypeName,
		TypeNo,
		PeriodNo
	HAVING ABS(Sum(Amount))>0.01';

$OutOfWackResult = DB_query($sql,$db);

$RowCounter =0;

while ($OutOfWackRow = DB_fetch_array($OutOfWackResult)){

	if ($RowCounter==18){
		$RowCounter=0;
		echo $Header;
	} else {
		$RowCounter++;
	}
	echo "<TR><TD><A HREF='" . $rootpath . "/GLTransInquiry.php?" . SID . "&TypeID=" . $OutOfWackRow['Type'] . "&TransNo=" . $OutOfWackRow['TypeNo'] . "'>" . $OutOfWackRow['TypeName'] . '</A></TD><TD ALIGN=RIGHT>' . $OutOfWackRow['TypeNo'] . '</TD><TD ALIGN=RIGHT>' . $OutOfWackRow['PeriodNo'] . '</TD><TD ALIGN=RIGHT>' . number_format($OutOfWackRow['NetTot'],3) . '</TD></TR>';

}
echo '</TABLE>';

include('includes/footer.inc');
?>

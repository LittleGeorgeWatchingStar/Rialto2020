<?php

namespace Rialto\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20150518100227 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
//        // What kinds of systypes are there?
//        $this->addSql("
//            select distinct ct.Type, st.TypeName
//            from CardTrans ct
//            join SysTypes st on ct.Type = st.TypeID
//        ");
//
//        // How many authorizations are there?  15058
//        $this->addSql("
//            select count(*)
//            from CardTrans ct
//            where Type in (0, 13)
//        ");
//
//        // How many captures are there?  24169
//        $this->addSql("
//            select count(*)
//            from CardTrans ct
//            where Type = 12
//        ");
//
//        // How many refunds are there?  61
//        $this->addSql("
//            select count(*)
//            from CardTrans ct
//            where Type = 101
//        ");
//
//        // How many captured auths are there?  9413
//        $this->addSql("
//            select count(distinct auth.CardTransID)
//            from CardTrans auth
//            join CardTrans cap
//                on auth.TransactionID = cap.TransactionID and auth.CardID = cap.CardID
//            where auth.Type in (0, 13)
//            and cap.Type = 12
//        ");
//
//        // How many paired refunds are there?  12
//        $this->addSql("
//            select count(distinct auth.CardTransID)
//            from CardTrans auth
//            join CardTrans cap
//                on auth.TransactionID = cap.TransactionID and auth.CardID = cap.CardID
//            where auth.Type in (0, 13)
//            and cap.Type = 101
//        ");
//
//        // How many have a debtor trans?  24228
//        $this->addSql("
//            select count(DISTINCT ct.CardTransID)
//            from CardTrans ct
//            join DebtorTrans dt on (
//                dt.Type = ct.Type and dt.TransNo = ct.TransNo
//            )
//        ");
//
//        // Which captures do not have a debtor trans?  2
//        $this->addSql("
//            select ct.*
//            from CardTrans ct
//            left join DebtorTrans dt
//                on ct.Type = dt.Type and ct.TransNo = dt.TransNo
//            where ct.Type not in (0, 13)
//            and dt.ID is null
//        ");
//
//        // Do captures have the same amount as their debtor transactions?  close enough
//        $this->addSql("
//            select ct.*, dt.*
//            from CardTrans ct
//            left join DebtorTrans dt
//                on ct.Type = dt.Type and ct.TransNo = dt.TransNo
//            where ct.Type not in (0, 13)
//            and abs(ct.Amount) != abs(dt.OvAmount + dt.OvGST + dt.OvFreight + dt.OvDiscount)
//        ");
//
//        // Are payment and refunds in the right range?
//        $this->addSql("
//            select min(Amount), max(Amount)
//            from CardTrans
//            where Type = 12
//        ");
//        $this->addSql("
//            select min(Amount), max(Amount)
//            from CardTrans
//            where Type = 101
//        ");
//
//        $this->addSql("
//            select * from CardTrans auth
//            join DebtorTrans dt on auth.salesOrderID = dt.Order_
//            where auth.Type in (0, 13)
//            and auth.salesOrderID is not null
//            and dt.Type = 12
//        ");



        // Populate new capture columns.
        $this->addSql("
            update CardTrans
            set amountCaptured = Amount, dateCaptured = dateCreated
            where Type not in (0, 13)
        ");

        // Merge auth columns into captures.
        $this->addSql("
            update CardTrans cap
            join CardTrans auth
                on auth.TransactionID = cap.TransactionID and auth.CardID = cap.CardID
            set cap.dateCreated = auth.dateCreated, cap.Amount = auth.Amount
            where auth.Type in (0, 13)
            and cap.Type = 12
        ");

        // Delete auths that have a matched capture
        $this->addSql("
            delete auth
            from CardTrans auth
            join CardTrans cap
                on auth.TransactionID = cap.TransactionID and auth.CardID = cap.CardID
            where auth.Type in (0, 13)
            and cap.Type = 12
        ");

        // Attempt to match by sales order ID
        $this->addSql("
            update CardTrans auth
            join DebtorTrans dt on auth.salesOrderID = dt.Order_
            set auth.Type = dt.Type
                , auth.TransNo = dt.TransNo
                , auth.amountCaptured = -(dt.OvAmount + dt.OvGST + dt.OvFreight + dt.OvDiscount)
                , auth.dateCaptured = dt.TranDate
            where auth.Type in (0, 13)
            and auth.salesOrderID is not null
            and auth.Amount >= -(dt.OvAmount + dt.OvGST + dt.OvFreight + dt.OvDiscount)
            and dt.Type = 12
        ");

        // What's left?
        $this->addSql("
            select ct.CardTransID, ct.Type, ct.TransNo,
            ct.TransactionID, ct.AuthorizationCode as AuthCode,
            ct.Approved, ct.Voided, ct.Posted, ct.dateCreated, ct.salesOrderID
            from CardTrans ct
            where ct.Type in (0, 13)
        ");

    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        $this->throwIrreversibleMigrationException();
    }
}

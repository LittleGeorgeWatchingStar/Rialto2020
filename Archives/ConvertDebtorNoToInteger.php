<?php
/* HOW TO USE THIS SCRIPT

Description:

The script connects to the SQL database via PHP and converts DebtorNo from 
unique chars to autoincrementing integers. This is an essential change to use
our new model classes.

Usage:

From the commandline, this script takes 3 paramaters.

> php ConvertDebtorNoToInterger.php [username] [password] [dbname]

*/


set_include_path(get_include_path() . PATH_SEPARATOR . dirname(dirname((__FILE__))) . '/lib');
require_once 'Zend/Db.php';

$tablesToUpdate = array(
    'Contracts' => 'DebtorNo',
    'CustBranch' => 'DebtorNo',
    'DebtorTrans' => 'DebtorNo',
    'DebtorsMaster' => 'DebtorNo',
    'SalesOrders' => 'DebtorNo',
    'OrderDeliveryDifferencesLog' => 'DebtorNo',
    'Prices' => 'DebtorNo',
    'StockMoves' => 'DebtorNo',
    'WWW_Users' => 'CustomerID');

$foreignKeys = array(
    'SalesOrders' => 'SalesOrders_ibfk_1'
    );

$foreignKeysConstraint = array(
    'SalesOrders_ibfk_1' => 'FOREIGN KEY (`DebtorNo`) REFERENCES' . 
        ' `DebtorsMaster`(`DebtorNo`) ON UPDATE CASCADE'
    );

if ( $argc < 4 ) {
    die("Usage {$argv[0]} dbname username password\n");
}

$db = Zend_Db::factory('Pdo_Mysql', array(
    'host'  => '127.0.0.1',
    'username' => $argv[2],
    'password' => $argv[3],
    'dbname' => $argv[1]
    ));

$db->getConnection()->setAttribute( PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION );

$result = $db->fetchAll(
    'SELECT * FROM DebtorsMaster WHERE DebtorNo REGEXP "[^0-9]"');

foreach ($result as $user) {
    $oldDebtorNo = $user['DebtorNo'];
    print $oldDebtorNo . " ";

    $newDebtorNo = $db->fetchOne('SELECT max(DebtorNo+1) from DebtorsMaster');
    print $newDebtorNo ."\n";
   
    foreach ($tablesToUpdate as $table => $column) {
        $where = array(
            "$column = ?" => $oldDebtorNo
        );

        $data = array(
            "$column" => $newDebtorNo
        );

        $n = $db->update($table, $data, $where);
    }
}


//drop foreign keys first
foreach ($foreignKeys as $table => $constraint) {
    printf("Dropping foreign key constraint %s on %s\n", $foreignKeys[$table], $table);
    $db->getConnection()->exec(
        "ALTER TABLE $table DROP FOREIGN KEY $constraint"
    );
}

//change table definitions

foreach ($tablesToUpdate as $table => $column) {
    printf("Changing DebtorNo on Table %s\n", $table);
    if ($table == 'DebtorsMaster') 
    {
        $definition = 'INT UNSIGNED NOT NULL AUTO_INCREMENT';
    }
    else {
        $definition = 'INT UNSIGNED NOT NULL DEFAULT 0';
    }

    $result = $db->getConnection()->exec(
        "ALTER TABLE $table MODIFY $column $definition"
    );
}

foreach ($foreignKeys as $table => $constraint) {
    printf("Changing constraint on %s\n", $table);
    $db->getConnection()->exec( sprintf(
        "ALTER TABLE %s ADD CONSTRAINT %s",
        $table, $foreignKeysConstraint[$constraint]
    ));
}

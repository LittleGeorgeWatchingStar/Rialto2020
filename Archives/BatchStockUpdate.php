<?php

$PageSecurity = 4;

require_once 'includes/session.inc';
require_once 'gumstix/erp/controllers/html/stock/BatchStockUpdatePage.php';

$page = new BatchStockUpdatePage();
echo $page->render();
<?php

use Rialto\SalesBundle\Entity\Customer;
use Rialto\PurchasingBundle\Entity\Supplier;
use Rialto\SecurityBundle\Entity\User;

require_once 'includes/header_functions.php';

header("Content-Type: text/html; charset=$DefaultCharset");

$user = User::fetchCurrent();
$theme = $user ? $user->getTheme() : User::DEFAULT_THEME;

/* $Revision: 1.16 $ */
// Titles and screen header
// Needs the file config.php loaded where the variables are defined for
//  $rootpath
//  $title - should be defined in the page this file is included with
if (! empty($_GET['IgnoreTitle']) ) {
	$_POST['IgnoreTitle'] = $_GET['IgnoreTitle'] == '1';
}
if ( empty($title) ) $title = 'Welcome to Rialto';

?>

<?php if (! isset($_POST['IgnoreTitle'])): ?>
    <!DOCTYPE html>
    <?php $dojoBase = "https://ajax.googleapis.com/ajax/libs/dojo/1.10.2"; ?>
    <HTML>
    <HEAD>
        <TITLE><?php echo $title; ?></TITLE>
        <link REL="shortcut icon" href="/favicon.ico">
        <link REL="icon" href="/favicon.ico">
        <link rel="stylesheet"
            type="text/css"
            href="<?php echo $dojoBase; ?>/dijit/themes/<?php echo $theme; ?>/<?php echo $theme; ?>.css" />
        <link rel="stylesheet"
              type="text/css"
              href="/css/rialto.css"/>
        <link rel="stylesheet"
              type="text/css"
              href="/css/icons.css"/>
        <link rel="stylesheet"
              type="text/css"
              href="/css/main.css"/>
        <link rel="stylesheet"
              type="text/css"
              href="/css/themes/<?php echo $theme; ?>/main.css"/>

    <?php if ( isset($gAdditionalStylesheets) && count($gAdditionalStylesheets) ): ?>
        <?php foreach ( $gAdditionalStylesheets as $uri ): ?>
            <link rel="stylesheet"
                  type="text/css"
                  href="<?php echo $uri; ?>" />
        <?php endforeach; ?>
    <?php endif; ?>

    <script
        type="text/javascript"
        src="<?php echo $dojoBase; ?>/dojo/dojo.js"
        data-dojo-config="
            parseOnLoad: true,
            baseUrl: '/js/',
            modulePaths: {rialto: 'rialto'}
        "></script>

    <script type="text/javascript" src="/js/rialto/init/globals.js"></script>
    <script type="text/javascript" src="/js/rialto/init/main.js"></script>

    <?php if ( isset($gAdditionalScripts) && count($gAdditionalScripts) ): ?>
        <?php foreach ( $gAdditionalScripts as $uri ): ?>
            <script type="text/javascript" src="<?php echo $uri; ?>"></script>
        <?php endforeach; ?>
    <?php endif; ?>
    </HEAD>

    <body class="<?php echo $theme; ?>">

        <header id="header" class="layoutSection">
            <nav id="loginInfo" class="links">
            <a href="/index.php/form/Security/User/<?php echo $user->getId(); ?>">
                <?php echo $user->getName(); ?>
            </a>
            <a href="/index.php/logout"
               onclick="return confirm('Log out?');">Logout</a>
            </nav>

            <nav id="headerLinks" class="links">
                <a href="/index.php">Main</a>
                <a href="/index.php/select/Sales/Customer">Customer</a>
                <a href="/index.php/select/Stock/StockItem">Item</a>
                <a href="/index.php/select/Purchasing/Supplier">Supplier</a>
            </nav>
        </header>

        <nav id="menu" class="layoutSection">
            <div id="menuTarget">
                Loading menu...
            </div>
        </nav>

        <main id="content" class="layoutSection">
    <?php unset($dojoBase); ?>
<?php endif; ?>

<?php unset($user, $theme); ?>

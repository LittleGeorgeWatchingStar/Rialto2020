<?php

$PageSecurity = 2;

require_once 'includes/session.inc';

function main()
{
    $path = array();
    if ( isset($_SERVER['PATH_INFO']) ) {
        $path = explode('/', $_SERVER['PATH_INFO']);
    }
    $controller = getController($path);
    return $controller->render();
}

function getController(array $path)
{
    if ( count($path) < 2 ) {
        require_once 'gumstix/erp/controllers/html/IndexPage.php';
        return new IndexPage();
    }
    array_shift($path);
    $module = array_shift($path);
    $name = array_shift($path);
    $name .= "Page";
    require_once "gumstix/erp/controllers/html/$module/$name.php";
    return new $name();
}

echo main();

<?php

function main()
{

    require_once 'Zend/Json.php';

    require_once 'Zend/Dojo/Form.php';
    require_once 'Zend/Dojo/Data.php';
    require_once 'Zend/Dojo/View/Helper/Dojo.php';

    require_once 'Zend/View.php';
    $view = new Zend_View();

    require_once 'Zend/Dojo.php';
    Zend_Dojo::enableView($view);

    $view->dojo()->setLocalPath('/js/dojo/dojo/dojo.js');
    $view->dojo()->useLocalPath();

    $view->setScriptPath( 'includes/views/' );
    $view->doctype('HTML5');

    $view->dojo()->enable();
    $view->dijitTheme = 'claro';
    $view->dojo()->setDjConfigOption('parseOnLoad', true);
    $view->dojo()->addStyleSheetModule(sprintf(
        'dijit.themes.%s', $view->dijitTheme
    ));
    $view->dojo()->addStyleSheet('/js/dojo/dojox/grid/resources/Grid.css');
    $view->dojo()->addStyleSheet(sprintf(
        '/js/dojo/dojox/grid/resources/%sGrid.css',
        $view->dijitTheme
    ));

    return $view->render('dojoRestView.php');

}

echo main();
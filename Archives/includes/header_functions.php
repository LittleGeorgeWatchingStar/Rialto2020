<?php

function  _fix_line( $_line ) {
    switch ( $_line ) {
        case 'col': return 'subexp';
        case 'exp': return 'sub';
        default:    return 'sub';
    }
}

function _tree( $id ) {
    if ( isset( $_COOKIE[ 't_' . $id ] )) {
        return $_COOKIE[ 't_' . $id ];
    }
    return 'exp';
}


function getThemeDirectory()
{
    return getThemeRootDirectory() .'/'. $_SESSION['Theme'];
}

function getThemeRootDirectory()
{
    return 'css/themes';
}

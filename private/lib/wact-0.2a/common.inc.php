<?php
//--------------------------------------------------------------------------------
// Copyright 2003 Procata, Inc.
// Released under the LGPL license (http://www.gnu.org/copyleft/lesser.html)
//--------------------------------------------------------------------------------
/**
* @package WACT
* @version $Id: common.inc.php,v 1.43 2004/11/20 17:23:14 jeffmoore Exp $
*/

// please don't mangle anything
set_magic_quotes_runtime(0); 

/**
* Define WACT_ROOT, set error reporting and determine PHP version
*/
define('WACT_ROOT', dirname(__FILE__) . '/');

/**
* Define WACT_MAIN_DIR - the directory containing the script where
* execution began (a users script) to calculate relative directory locations
* for templates etc.
*/
if ( !defined('WACT_MAIN_DIR') ) {
    if ( php_sapi_name() != 'cli' ) {
        define('WACT_MAIN_DIR',getcwd());
    } else {
        // CLI working dir is the users working dir
        define('WACT_MAIN_DIR',dirname(getcwd().'/'.$_SERVER['SCRIPT_FILENAME']));
    }
}

// we run best (fastest) in php 4.3, but we can run as far back as 4.1.0
/**
* Load is_a() emulation if necessary
*/
if (!function_exists('is_a')) {
    require_once WACT_ROOT . 'util/phpcompat/is_a.php';
}

if (! defined('WACT_ERROR_FACTORY')) {
    define('WACT_ERROR_FACTORY', WACT_ROOT . 'error/factory/wact.inc.php');
}

if (! defined('WACT_ERROR_HANDLER')) {
    define('WACT_ERROR_HANDLER', WACT_ROOT . 'error/handler/debug.inc.php');
}

/**
* Raise an error message.  All framework error messages should be triggered using
* this function.  This allows for framework error messages to be internationalized.
*/
function RaiseError($group, $id, $info=NULL) {
    require_once WACT_ERROR_FACTORY;
    RaiseErrorHandler($group, $id, $info);
}

/** 
** Standard error handler checks for error suppression and delegates actual error
** handling when a real error occurrs.
*/
function ErrorHandlerDispatch($ErrorNumber, $ErrorMessage, $FileName, $LineNumber) {

    if ( ( $ErrorNumber & error_reporting() ) != $ErrorNumber ) {
        return; // Ignore this error because of error suppression
    }

    require_once WACT_ERROR_HANDLER;
    HandleError($ErrorNumber, $ErrorMessage, $FileName, $LineNumber);
}

// Only set the WACT error handler if there isn't an error handler already set.
if (set_error_handler('ErrorHandlerDispatch') !== NULL) {
    restore_error_handler();
}

if (! defined('WACT_CONFIG_MODULE')) {
    define('WACT_CONFIG_MODULE', WACT_ROOT . 'config/ini/single.inc.php');
}
require_once WACT_CONFIG_MODULE;

?>
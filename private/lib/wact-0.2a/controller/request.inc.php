<?php
//--------------------------------------------------------------------------------
// Copyright 2003 Procata, Inc.
// Released under the LGPL license (http://www.gnu.org/copyleft/lesser.html)
//--------------------------------------------------------------------------------
/**
* @package WACT_CONTROLLERS
* @version $Id: request.inc.php,v 1.3 2004/11/15 15:53:25 jeffmoore Exp $
*/

/**
* Function for stripping magic quotes. Modifies the reference.
* @param array to remove quotes from e.g. $_GET, $_POST
* @return void
* @access public
*/
function UndoMagicSlashing(&$var) { 
	if(is_array($var)) { 
		while(list($key, $val) = each($var)) { 
			UndoMagicSlashing($var[$key]); 
		} 
	} else { 
		$var = stripslashes($var); 
	} 
} 
/**
* Strip quotes if magic quotes are on
*/

if (get_magic_quotes_gpc()) { 
	UndoMagicSlashing($_GET); 
	UndoMagicSlashing($_POST); 
	UndoMagicSlashing($_COOKIES); 
	UndoMagicSlashing($_REQUEST); 
}

/**
* @access public
* @package WACT_CONTROLLERS
*/
class Request {

    function hasParameters() {
        return count($_GET) > 0;
    }

    function hasParameter($name) {
        return isset($_GET[$name]);
    }

    function getParameter($name) {
        if (isset($_GET[$name])) {
            return $_GET[$name];
        }
    }

    function hasPostProperty($name) {
        if (!strcasecmp($_SERVER['REQUEST_METHOD'], 'POST')) {
            return isset($_POST[$name]);
        } else {
            return isset($_GET[$name]);
        }
    }

    function getPostProperty($name) {
        if (!strcasecmp($_SERVER['REQUEST_METHOD'], 'POST')) {
            if (isset($_POST[$name])) {
                return $_POST[$name];
            }
        } else {
            if (isset($_GET[$name])) {
                return $_GET[$name];
            }
        }
    }
    
    function exportPostProperties() {
        if (!strcasecmp($_SERVER['REQUEST_METHOD'], 'POST')) {
            return $_POST;
        } else {
            return $_GET;
        }
    }

    function getMethod() {
        return $_SERVER['REQUEST_METHOD'];
    }

    function getPathInfo() {
        if (isset($_SERVER['PATH_INFO'])) {
            return $_SERVER['PATH_INFO'];
        }
    }
}

?>
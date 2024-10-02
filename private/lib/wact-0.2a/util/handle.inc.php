<?php
//--------------------------------------------------------------------------------
// Copyright 2003 Procata, Inc.
// Released under the LGPL license (http://www.gnu.org/copyleft/lesser.html)
//--------------------------------------------------------------------------------
/**
* @package WACT_UTIL
* @version $Id: handle.inc.php,v 1.1 2004/03/10 01:53:49 jeffmoore Exp $
*/
/**
* Takes a "handle" to an object and modifies it to convert it to an instance
* of the class. Allows for "lazy loading" of objects on demand.
* @see http://wact.sourceforge.net/index.php/ResolveHandle
* @param mixed
* @return void
* @access public
* @package WACT
*/
function ResolveHandle(&$Handle) {
    if (!is_object($Handle) && !is_null($Handle)) {
        if (is_array($Handle)) {
            $Class = array_shift($Handle);
            $ConstructionArgs = $Handle;
        } else {
            $ConstructionArgs = array();
            $Class = $Handle;
        }
        if (is_integer($Pos = strpos($Class, '|'))) {
            $File = substr($Class, 0, $Pos);
            $Class = substr($Class, $Pos + 1);
            require_once $File;
        }
        switch (count($ConstructionArgs)) {
        case 0:
            $Handle = new $Class();  // =& doesn't work here.  Why?
            break;
        case 1:
            $Handle = new $Class(array_shift($ConstructionArgs));
            break;
        case 2:
            $Handle = new $Class(
                array_shift($ConstructionArgs), 
                array_shift($ConstructionArgs));
            break;
        case 3:
            $Handle = new $Class(
                array_shift($ConstructionArgs), 
                array_shift($ConstructionArgs), 
                array_shift($ConstructionArgs));
            break;
        default:
            // Too many arguments for this cobbled together implemenentation.  :(
            die();
        }
    }
}
?>

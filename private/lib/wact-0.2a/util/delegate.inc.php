<?php
//--------------------------------------------------------------------------------
// Copyright 2003 Procata, Inc.
// Released under the LGPL license (http://www.gnu.org/copyleft/lesser.html)
//--------------------------------------------------------------------------------
/**
* @package WACT_UTIL
* @version $Id: delegate.inc.php,v 1.6 2004/12/01 02:59:28 jeffmoore Exp $
*/


/**
* A Handle represents an uninstantiated object that takes the place of a
* given object and can be swapped out for the object.
* Implements lazy loading for composing object heirarchies.
* @see http://wact.sourceforge.net/index.php/ResolveHandle
*/
class Handle {
    var $file;
    var $class;
    var $args;

    function Handle($class, $args = array()) {
        if (is_integer($pos = strpos($class, '|'))) {
            $this->file = substr($class, 0, $pos);
            $class = substr($class, $pos + 1);
        }
        $this->class = $class;
        $this->args = $args;
    }
    
    function getClass() {
        return $this->class;
    }
    
    function isHandle(&$object) {
        return strcasecmp(get_class($object), 'handle') == 0;
    }
    
    function resolve(&$Handle) {
        if (!Handle::isHandle($Handle)) {
            return;
        }
        
        $class = $Handle->class;
        if (!class_exists($class)) {
            if (isset($Handle->file)) {
                require_once $Handle->file;
            } else {
                $file = ConfigManager::getOptionAsPath('autoload', 'handle', $class);
                if (!is_null($file)) {
                    require_once $file;
                }
            }
        }
        
        $args = $Handle->args;
        switch (count($args)) {
        case 0:
            $Handle = new $class();  // =& doesn't work here.  Why?
            break;
        case 1:
            $Handle = new $class(array_shift($args));
            break;
        case 2:
            $Handle = new $class(
                array_shift($args), 
                array_shift($args));
            break;
        case 3:
            $Handle = new $class(
                array_shift($args), 
                array_shift($args), 
                array_shift($args));
            break;
        default:
            // Too many arguments for this cobbled together implemenentation.  :(
            die();
        }
    }
}

class StaticDelegate {

    var $file;
    var $method;
    var $class;

    function StaticDelegate($class, $method, $file = NULL) {
        $this->class = $class;
        $this->method = $method;
    }

    function invoke($args) {
        if (!is_null($this->file)) {
            require_once $this->file;
            $this->file = NULL;
        }
        return call_user_func_array(array($this->class, $this->method), $args);
    }

}

class Delegate {
    var $object;
    var $method;

    function Delegate(&$object, $method) {
        $this->object =& $object;
        $this->method = $method;
    }

    function invoke($args) {
        Handle::resolve($this->object);
        return call_user_func_array(array(&$this->object, $this->method), $args);
    }
}

?>

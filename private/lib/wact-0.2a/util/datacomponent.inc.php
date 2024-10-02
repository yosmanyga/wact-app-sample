<?php
//--------------------------------------------------------------------------------
// Copyright 2003 Procata, Inc.
// Released under the LGPL license (http://www.gnu.org/copyleft/lesser.html)
//--------------------------------------------------------------------------------
/**
* @package WACT_UTIL
* @version $Id: datacomponent.inc.php,v 1.1 2004/11/15 01:46:04 jeffmoore Exp $
*/

require_once WACT_ROOT . 'util/dataspace.inc.php';

class DataComponent extends DataSpace {

    /**
    * Gets a copy of a stored property by name
    * @param string name of property
    * @return mixed value of property or NULL if not found
    * @access public
    */
    function get($name) {
        if (method_exists($this, 'get' . $name)) {
            if (!empty($name)) {
                $method = 'get' . $name;
                return $this->$method();
            }
        } else {
            if (isset($this->properties[$name])) {
                return $this->properties[$name];
            }
        }
    }

    /**
    * Stores a copy of value into a Property
    * @param string name of property
    * @param mixed value of property
    * @return void
    * @access public
    */
    function set($name, $value) {
        if (method_exists($this, 'set' . $name)) {
            if (!empty($name)) {
                $method = 'set' . $name;
                $this->$method($value);
            }
        } else {
            $this->properties[$name] = $value;
        }
    }

    /**
    * replaces the current properties of this dataspace with the proprties and values
    * passed as a parameter
    * @param array
    * @return void
    * @access public
    */
    function import($property_list) {
        $this->removeAll();
        $this->merge($property_list);
    }

    /**
    * Has a value been assigned under this name for this dataspace?
    * @param string name of property
    * @return boolean TRUE if property exists
    * @access public
    */
    function hasProperty($name) {
        return method_exists($this, 'get' . $name) || isset($this->properties[$name]);
    }

    /**
    * Return a unique list of available properties
    * This method is probably going to have capitalization problems.
    * @return array
    * @access public
    */
    function getPropertyList() {
        $list = array_keys($this->properties);

        foreach(get_class_methods($this) as $method) {

            // PHP 5+: methods return now preserve original case
            $method = strtolower($method);

            if (substr($method, 0, 3) == "get" && $method != 'get' && $method != 'getpropertylist' && $method != 'getpath') {
                // Need to make sure that the key does not already exist in the
                // array.  This test must be case insensative because php 4
                // will not give us the proper case of the returned value.
                $list[] = ucfirst(substr($method, 3));
            }
        }

        return $list;
    }

}

?>
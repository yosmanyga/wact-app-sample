<?php
//--------------------------------------------------------------------------------
// Copyright 2003 Procata, Inc.
// Released under the LGPL license (http://www.gnu.org/copyleft/lesser.html)
//--------------------------------------------------------------------------------
/**
* @package WACT_TEMPLATE
* @version $Id: dictionary.inc.php,v 1.2 2004/11/18 04:22:46 jeffmoore Exp $
*/

require_once WACT_ROOT . 'template/compiler/common/dictionary.inc.php';

class PropertyInfo {
    var $Property;
	var $Tag;
	var $PropertyClass;
	var $File;
	
	function PropertyInfo($property, $tag, $class) {
	    $this->Property = $property;
	    $this->Tag = $tag;
	    $this->PropertyClass = $class;
	}

    function load() {
        if (!class_exists($this->PropertyClass) && isset($this->File)) {
            require_once $this->File;
        }
    }
}

//--------------------------------------------------------------------------------
/**
* The PropertyDictionary, which exists as a global variable, acting as a registry
* of compile time properties.
* @see http://wact.sourceforge.net/index.php/PropertyDictionary
* @access protected
* @package WACT_TEMPLATE
*/
class PropertyDictionary extends CompilerArtifactDictionary {

	/**
	* @var array
	* @access private
	*/
	var $PropertyInformation = array();

    function PropertyDictionary() {
        parent::CompilerArtifactDictionary();
    }    
    
	/**
	* Registers a property in the dictionary, called from the global registerProperty()
	* function.
	* @param string Tag name
	* @param string Property class name
	* @return void
	* @access protected
	*/
	function _registerProperty(&$PropertyInfo) {
		$tag = strtolower($PropertyInfo->Tag);
		$this->PropertyInformation[$tag][] =& $PropertyInfo;
	}

    /**
    * Registers information about a compile time propert in the global property dictionary.
    * This function is called from the respective compile time component class
    * file.
    * @return void
    * @access protected
    */
    function registerProperty(&$PropertyInfo, $file) {
        $PropertyInfo->File = $file;
        $GLOBALS['PropertyDictionary']->_registerProperty($PropertyInfo);
    }

	/**
	* Gets the list of Property Classes registered to a specific tag class.
	* @param string name of a tag
	* @return array list of propertiess
	* @access protected
	*/
	function getPropertyList($tag) {
	    $tag = strtolower($tag);
	    if (isset($this->PropertyInformation[$tag])) {
    		return $this->PropertyInformation[$tag];
        } else {
            return array();
        }
	}

	/**
	* Returns the global instance of the property dictionary
	* Used so less direct references scattered around to global location
	* @static
	* @return PropertyDictionary
	* @access protected
	*/
	function &getInstance() {
	    return parent::_getInstance('PropertyDictionary', 'PropertyDictionary', 'prop');
	}
}
?>
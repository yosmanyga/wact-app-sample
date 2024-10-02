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

class FilterInfo {
    var $Name = 'capitalize';
    var $FilterClass = 'CapitalizeFilter';
    var $MinParameterCount = 0;
    var $MaxParameterCount = 0;
    var $File;
    
    function FilterInfo($Name, $FilterClass, $MinParameterCount = 0, $MaxParameterCount = 0) {
        $this->Name = $Name;
        $this->FilterClass = $FilterClass;
        $this->MinParameterCount = $MinParameterCount;
        $this->MaxParameterCount = $MaxParameterCount;
    }

    function load() {
        if (!class_exists($this->FilterClass) && isset($this->File)) {
            require_once $this->File;
        }
    }
    
}

/**
* The FilterDictionary, which exists as a global variable, acting as a registry
* of compile time properties.
* @see http://wact.sourceforge.net/index.php/FilterDictionary
* @access protected
* @package WACT_TEMPLATE
*/
class FilterDictionary extends CompilerArtifactDictionary {

	/**
	* @var array
	* @access private
	*/
	var $FilterInformation = array();

    function FilterDictionary() {
        parent::CompilerArtifactDictionary();
    }    

	/**
	* Registers a filter in the dictionary, called from the global registerFilter()
	* function.
	* @param string Tag name
	* @param string Filter class name
	* @return void
	* @access protected
	*/
	function _registerFilter(&$FilterInfo) {
		$name = strtolower($FilterInfo->Name);
		$this->FilterInformation[$name] =& $FilterInfo;
	}

    /**
    * Registers information about a compile time filter in the global filter dictionary.
    * This function is called from the respective compile time component class
    * file.
    * @see http://wact.sourceforge.net/index.php/registerFilter
    * @param string Tag class name
    * @param string Filter class name
    * @return void
    * @access protected
    */
    function registerFilter(&$FilterInfo, $file) {
        $FilterInfo->File = $file;
        $GLOBALS['FilterDictionary']->_registerFilter($FilterInfo);
    }

	/**
	* Gets the tag information about a given tag.
	* Called from the SourceFileParser
	* @see SourceFileParser
	* @param string name of a tag
	* @return object TagInfo class
	* @access protected
	*/
	function &getFilterInfo($name) {
		return $this->FilterInformation[strtolower($name)];
	}

	/**
	* Returns the global instance of the Filter dictionary
	* Used so less direct references scattered around to global location
	* @static
	* @return FilterDictionary
	* @access protected
	*/
	function &getInstance() {
	    return parent::_getInstance('FilterDictionary', 'FilterDictionary', 'filter');
	}
}
?>
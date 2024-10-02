<?php
//--------------------------------------------------------------------------------
// Copyright 2003 Procata, Inc.
// Released under the LGPL license (http://www.gnu.org/copyleft/lesser.html)
//--------------------------------------------------------------------------------
/**
* @package WACT_TEMPLATE
* @version $Id: tagdictionary.inc.php,v 1.15 2004/11/18 05:05:25 jeffmoore Exp $
*/

require_once WACT_ROOT . 'template/compiler/common/dictionary.inc.php';

/**
* The tag must have a closing tag
*/
define('ENDTAG_REQUIRED', 1);
/**
* The tag may have a closing tag
*/
define('ENDTAG_OPTIONAL', 2);
/**
* The tag may not have a closing tag
*/
define('ENDTAG_FORBIDDEN', 3);


define('LOCATION_SERVER', 'server');
define('LOCATION_CLIENT', 'client');

/**
* Define the attribute which "triggers" components
*/
define('PARSER_TRIGGER_ATTR_NAME','runat'); // Must be lower case! See TreeBuilder::addAttributeNode
define('PARSER_TRIGGER_ATTR_VALUE','server');

/**
* Define attribute for using known children
* Referenced in ParserState::ComponentParsingState::open()
*/
define('PARSER_USEKNOWN_ATTR','useknown');

/**
* @package WACT_TEMPLATE
*/
class TagInfo {
	var $Tag = '';
	var $EndTag = ENDTAG_REQUIRED;
	var $TagClass = '';
	var $CompilerAttributes = array();
	var $KnownParent;
	var $DefaultLocation = LOCATION_SERVER;
	var $File;
	
    function TagInfo($tag, $class) {
        $this->Tag = $tag;
        $this->TagClass = $class;
    }
    
    function setEndTag($end) {
        $this->EndTag = $end;
    }
    
    function setCompilerAttributes($attributes) {
        $this->CompilerAttributes = $attributes;
    }
    
    function setKnownParent($parent) {
        $this->KnownParent = $parent;
    }
    
    function setDefaultLocation($location) {
        $this->DefaultLocation = $location;
    }
    
    function load() {
        if (!class_exists($this->TagClass) && isset($this->File)) {
            require_once $this->File;
        }
    }
}

/**
* The TagDictionary, which exists as a global variable, acting as a registry
* of compile time components.
* @see http://wact.sourceforge.net/index.php/TagDictionary
* @access protected
* @package WACT_TEMPLATE
*/
class TagDictionary extends CompilerArtifactDictionary {
	/**
	* Associative array of TagInfo objects
	* @var array
	* @access private
	*/
	var $TagInformation = array();

    function TagDictionary() {
        parent::CompilerArtifactDictionary();
    }    

	/**
	* Registers a tag in the dictionary, called from the global registerTag()
	* function.
	* @param object TagInfo class
	* @return void
	* @access protected
	*/
	function _registerTag($taginfo) {
		$tag = strtolower($taginfo->Tag);
		$this->TagInformation[$tag] =& $taginfo;
	}

    /**
    * Registers information about a compile time tag in the global tag dictionary.
    * This function is called from the respective compile time component class
    * file.
    * @param object instance of a TagInfo class
    * @return void
    * @access protected
    */
    function registerTag(&$taginfo, $file) {
        $taginfo->File = $file;
        $GLOBALS['TagDictionary']->_registerTag($taginfo);
    }

	/**
	* Gets the tag information about a given tag.
	* Called from the SourceFileParser
	* @see SourceFileParser
	* @param string name of a tag
	* @return object TagInfo class
	* @access protected
	*/
	function &getTagInfo($tag) {
	    if (isset($this->TagInformation[strtolower($tag)])) {
    		return $this->TagInformation[strtolower($tag)];
	    }
	}

	/**
	* Returns the global instance of the tag dictionary
	* Used so less direct references scattered around to global location
	* @static
	* @return TagDictionary
	* @access protected
	*/
	function &getInstance() {
	    return parent::_getInstance('TagDictionary', 'TagDictionary', 'tag');
	}

	/*
	* Determines whether a tag is a server component, examining attributes and class
	* Called from ComponentParsingState::open() only to check for components
	* @param string tag name
	* @param array tag attributes
	* @param boolean whether it's an empty tag for GenericTags
	* @return boolean TRUE if it's a component
	* @access private
	*/
	function &findComponent($tag, $attrs, $isEmpty, &$Component) {

		// Does the tag have the runat attribute? If so it might be a component
		if ( isset ( $attrs[PARSER_TRIGGER_ATTR_NAME] ) ) {
		
			// Does runat ="server"? If so it's definately a component
            if ( strtolower($attrs[PARSER_TRIGGER_ATTR_NAME]) == PARSER_TRIGGER_ATTR_VALUE ) {
                if (isset($this->TagInformation[strtolower($tag)])) {
                    return $this->TagInformation[strtolower($tag)];
                } else {
                    // we are a generic tag.  We run at the server, but have no
                    // specific TagInfo record in the dictionary.
                    if ( !$isEmpty ) {
                        $generic =& new TagInfo($tag, 'GenericContainerTag');
                        $generic->File = WACT_ROOT . 'template/compiler/generictag.inc.php';

                        $generic->setEndTag(ENDTAG_REQUIRED);
                    } else {
                        $generic =& new TagInfo($tag, 'GenericTag');
                        $generic->File = WACT_ROOT . 'template/compiler/generictag.inc.php';
                        $generic->setEndTag(ENDTAG_FORBIDDEN);
                    }
                    $generic->setDefaultLocation(LOCATION_CLIENT);
    
                    return $generic;
                }
            }
		} else if ( isset($this->TagInformation[strtolower($tag)]) ) {

			$TagInfo =& $this->TagInformation[strtolower($tag)];

			// DefaultLocation allows the location of some tags to be specified without
			// a corresponding runat="" attribute.
			if ($TagInfo->DefaultLocation == LOCATION_SERVER) {
				return $TagInfo;
			}

			//----------------------------------------------------------------------------
			// Is the tag a known child? This applies only to sub classes of 
			// ServerTagComponentTag (tags that match HTML) and helps save adding
			// runat="server" excessively. The only tags at this time using this are
			// the form related tags.
			//----------------------------------------------------------------------------
			if ( isset($TagInfo->KnownParent) ) {
				if ( $KnownParent = & $Component->findSelfOrParentByClass($TagInfo->KnownParent) ) {
					if ( $KnownParent->getBoolAttribute('useknown',TRUE) ) {
						return $TagInfo;
					}
				}
			}
		}
    	return NULL;
	}

}
?>
<?php
//--------------------------------------------------------------------------------
// Copyright 2003 Procata, Inc.
// Released under the LGPL license (http://www.gnu.org/copyleft/lesser.html)
//--------------------------------------------------------------------------------
/**
* @package WACT_TEMPLATE
* @version $Id: tagstolowersaxfilter.inc.php,v 1.6 2004/11/12 21:25:07 jeffmoore Exp $
*/
//--------------------------------------------------------------------------------
/**
* Load array_change_key_case as needed
*/
if (!function_exists('array_change_key_case')) {
	require_once WACT_ROOT . 'util/phpcompat/array_change_key_case.php';
}
/**
* Includes
*/
require_once WACT_ROOT . 'template/compiler/saxfilters/basesaxfilter.inc.php';

/**
* Converts all tags and attribute names to lower case.
* @see http://wact.sourceforge.net/index.php/TagsToLower
* @access public
* @package WACT_TEMPLATE
* @abstract
*/
class TagsToLowerSaxFilter extends BaseSaxFilter {

	/**
	* @param XML_HTMLSax instance of the parser
	* @param string tag name
	* @param array attributes
	* @return void
	* @access private
	*/
	function startElement($tag, $attrs) {
		parent::startElement(strtolower($tag), array_change_key_case($attrs,CASE_LOWER));
	}


	/**
	* @param XML_HTMLSax instance of the parser
	* @param string tag name
	* @return void
	* @access private
	*/
	function endElement($tag) {
		parent::endElement(strtolower($tag));
	}

	/**
	* Sax Open Handler
	* @param string tag name
	* @param array attributes
	* @return void
	* @access private
	*/
	function emptyElement($tag, $attrs) {
		$this->ChildSaxFilter->emptyElement(strtolower($tag), $attrs);
	}

}
?>
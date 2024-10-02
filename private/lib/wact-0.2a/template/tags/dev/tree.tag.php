<?php
//--------------------------------------------------------------------------------
// Copyright 2003 Procata, Inc.
// Released under the LGPL license (http://www.gnu.org/copyleft/lesser.html)
//--------------------------------------------------------------------------------
/**
* @package WACT_TAG
* @version $Id: tree.tag.php,v 1.4 2004/11/18 04:22:48 jeffmoore Exp $
*/

/**
* Register the tag
*/
TagDictionary::registerTag(new TagInfo('dev:TREE', 'DevTreeTag'), __FILE__);

/**
* Dumps the component tree into the compiled template
* @see http://wact.sourceforge.net/index.php/DevTreeTag
* @access protected
* @package WACT_TAG
*/
class DevTreeTag extends CompilerDirectiveTag {
	/**
	* @return int PARSER_REQUIRE_PARSING
	* @access protected
	*/
	function preParse() {
		return PARSER_REQUIRE_PARSING;
	}

	/**
	* @param CodeWriter
	* @return void
	* @access protected
	*/
	function preGenerate(&$code) {
		parent::preGenerate($code);
		$code->writeHTML('<div aligh="left"><hr /><h3>Begin Tree Dump</h3><hr /></div>');
	}
	
	/**
	* @param CodeWriter
	* @return void
	* @access protected
	*/
	function postGenerate(&$code) {
		ob_start();
		dump_component_tree($this);
		$tree = ob_get_contents();
		ob_end_clean();
		$code->writeHTML('<div align="left"><hr />'.$tree.
			'<hr /><br /><h3>End Tree Dump</h3><hr /></div>');
		parent::postGenerate($code);
	}
}
?>
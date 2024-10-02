<?php
//--------------------------------------------------------------------------------
// Copyright 2003 Procata, Inc.
// Released under the LGPL license (http://www.gnu.org/copyleft/lesser.html)
//--------------------------------------------------------------------------------
/**
* @package WACT_TAG
* @version $Id: literal.tag.php,v 1.9 2004/11/18 04:22:47 jeffmoore Exp $
*/

/**
* Register the tag
*/
TagDictionary::registerTag(new TagInfo('core:LITERAL', 'CoreLiteralTag'), __FILE__);

/**
* Prevents a section of the template from being parsed, placing the contents
* directly into the compiled template
* @see http://wact.sourceforge.net/index.php/CoreLiteralTag
* @access protected
* @package WACT_TAG
*/
class CoreLiteralTag extends CompilerDirectiveTag {
	/**
	* @return void
	* @access protected
	*/
	function CheckNestingLevel() {
		if ($this->findParentByClass('CoreLiteralTag')) {
            RaiseError('compiler', 'BADSELFNESTING', array(
                'tag' => $this->tag,
                'file' => $this->SourceFile,
                'line' => $this->StartingLineNo));
		}
	}

	/**
	* @return int PARSER_FORBID_PARSING
	* @access protected
	*/
	function preParse() {
		return PARSER_FORBID_PARSING;
	}
}
?>
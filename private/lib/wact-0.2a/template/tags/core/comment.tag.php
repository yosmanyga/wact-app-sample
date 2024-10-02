<?php
//--------------------------------------------------------------------------------
// Copyright 2004 Procata, Inc.
// Released under the LGPL license (http://www.gnu.org/copyleft/lesser.html)
//--------------------------------------------------------------------------------
/**
 * @package WACT_TAG
 * @version $Id: comment.tag.php,v 1.3 2004/11/18 04:22:47 jeffmoore Exp $
 */
//--------------------------------------------------------------------------------
/**
 * Register the tag
 */
TagDictionary::registerTag(new TagInfo('core:COMMENT', 'CoreCommentTag'), __FILE__);

/**
 * Prevents a section of the template from being parsed, removing the contents
 * @see http://wact.sourceforge.net/index.php/CoreCommentTag
 * @access protected
 * @package WACT_TAG
 */
class CoreCommentTag extends CompilerDirectiveTag
{
	/**
	 * @return void
	 * @access protected
	 */
	function CheckNestingLevel() {
		if ($this->findParentByClass('CoreCommentTag')) {
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

	/**
	 * helper method
	 */
	function removeChildren() {
		foreach(array_keys($this->children) as $key) {
			unset($this->children[$key]);
		}
	}

	/**
	 * @param CodeWriter
	 * @return void
	 * @access protected
	 */
	function generateContents(&$code) {
		$this->removeChildren();
	}
}
?>
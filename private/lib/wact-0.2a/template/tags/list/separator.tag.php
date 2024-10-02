<?php
//--------------------------------------------------------------------------------
// Copyright 2003 Procata, Inc.
// Released under the LGPL license (http://www.gnu.org/copyleft/lesser.html)
//--------------------------------------------------------------------------------
/**
* @package WACT_TAG
* @version $Id: separator.tag.php,v 1.11 2004/11/18 04:22:49 jeffmoore Exp $
*/

/**
* Register tag
*/
TagDictionary::registerTag(new TagInfo('list:SEPARATOR', 'ListSeparatorTag'), __FILE__);

/**
* Compile time component for separators in a list
* @see http://wact.sourceforge.net/index.php/ListSeparatorTag
* @access protected
* @package WACT_TAG
*/
class ListSeparatorTag extends SilentCompilerDirectiveTag {
	/**
	* @return void
	* @access private
	*/
	function CheckNestingLevel() {
		if ($this->findParentByClass('ListSeparatorTag')) {
            RaiseError('compiler', 'BADSELFNESTING', array(
                'tag' => $this->tag,
                'file' => $this->SourceFile,
                'line' => $this->StartingLineNo));
		}
		if ( !is_a($this->parent, 'ListItemTag') && !is_a($this->parent, 'ErrorSummaryTag')) {
            RaiseError('compiler', 'MISSINGENCLOSURE', array(
                'tag' => $this->tag,
                'EnclosingTag' => 'list:ITEM or ERRORSUMMARY',
                'file' => $this->SourceFile,
                'line' => $this->StartingLineNo));
		}
	}
}
?>
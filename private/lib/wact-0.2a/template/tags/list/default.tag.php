<?php
//--------------------------------------------------------------------------------
// Copyright 2003 Procata, Inc.
// Released under the LGPL license (http://www.gnu.org/copyleft/lesser.html)
//--------------------------------------------------------------------------------
/**
* @package WACT_TAG
* @version $Id: default.tag.php,v 1.9 2004/11/18 04:22:49 jeffmoore Exp $
*/

/**
* Register the tag
*/
TagDictionary::registerTag(new TagInfo('list:DEFAULT', 'ListDefaultTag'), __FILE__);

/**
* Default List tag for a list which failed to have any contents
* @see http://wact.sourceforge.net/index.php/ListDefaultTag
* @access protected
* @package WACT_TAG
*/
class ListDefaultTag extends SilentCompilerDirectiveTag {
	/**
	* @return void
	* @access protected
	*/
	function CheckNestingLevel() {
		if ($this->findParentByClass('ListDefaultTag')) {
            RaiseError('compiler', 'BADSELFNESTING', array(
                'tag' => $this->tag,
                'file' => $this->SourceFile,
                'line' => $this->StartingLineNo));
		}
		if (!is_a($this->parent, 'ListListTag') && !is_a($this->parent, 'ErrorSummaryTag')) {
            RaiseError('compiler', 'MISSINGENCLOSURE', array(
                'tag' => $this->tag,
                'EnclosingTag' => 'list:LIST or ERRORSUMMARY',
                'file' => $this->SourceFile,
                'line' => $this->StartingLineNo));
		}
	}
}
?>
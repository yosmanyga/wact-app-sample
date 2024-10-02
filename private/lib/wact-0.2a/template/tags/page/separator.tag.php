<?php
//--------------------------------------------------------------------------------
// Copyright 2003 Procata, Inc.
// Released under the LGPL license (http://www.gnu.org/copyleft/lesser.html)
//--------------------------------------------------------------------------------
/**
* @package WACT_TAG
* @version $Id: separator.tag.php,v 1.6 2004/11/18 04:22:50 jeffmoore Exp $
*/

/**
* Register tag
*/
TagDictionary::registerTag(new TagInfo('Page:SEPARATOR', 'PageSeparatorTag'), __FILE__);

/**
* Compile time component for separators in a Pager
* @see http://wact.sourceforge.net/index.php/PageSeparatorTag
* @access protected
* @package WACT_TAG
*/
class PageSeparatorTag extends SilentCompilerDirectiveTag {
	/**
	* @return void
	* @access private
	*/
	function CheckNestingLevel() {
	    if ($this->findParentByClass('PageSeparatorTag')) {
            RaiseError('compiler', 'BADSELFNESTING', array(
                'tag' => $this->tag,
                'file' => $this->SourceFile,
                'line' => $this->StartingLineNo));
	    }
		if (!$this->findParentByClass('PageNavigatorTag')) {
            RaiseError('compiler', 'MISSINGENCLOSURE', array(
                'tag' => $this->tag,
                'EnclosingTag' => 'page:navigator',
                'file' => $this->SourceFile,
                'line' => $this->StartingLineNo));
		}
	}
}
?>
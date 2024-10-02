<?php
//--------------------------------------------------------------------------------
// Copyright 2003 Procata, Inc.
// Released under the LGPL license (http://www.gnu.org/copyleft/lesser.html)
//--------------------------------------------------------------------------------
/**
* @package WACT_TAG
* @version $Id: elipses.tag.php,v 1.5 2004/11/18 04:22:50 jeffmoore Exp $
*/

/**
* Register tag
*/
TagDictionary::registerTag(new TagInfo('page:ELIPSES', 'PageElipsesTag'), __FILE__);

/**
* Compile time component for elispses in a pager.
* Elipses are sed to mark omitted page numbers outside of the
* current range of the pager e.g. ...6 7 8... (the ... are the elipses)
* @see http://wact.sourceforge.net/index.php/PageElipsesTag
* @access protected
* @package WACT_TAG
*/
class PageElipsesTag extends SilentCompilerDirectiveTag {
	/**
	* @return void
	* @access private
	*/
	function CheckNestingLevel() {
	    if ($this->findParentByClass('PageElipsesTag')) {
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
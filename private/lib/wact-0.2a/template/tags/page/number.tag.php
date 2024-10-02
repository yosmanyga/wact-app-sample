<?php
//--------------------------------------------------------------------------------
// Copyright 2003 Procata, Inc.
// Released under the LGPL license (http://www.gnu.org/copyleft/lesser.html)
//--------------------------------------------------------------------------------
/**
* @package WACT_TAG
* @version $Id: number.tag.php,v 1.6 2004/11/18 04:22:50 jeffmoore Exp $
*/

/**
* Register tag
*/
$taginfo =& new TagInfo('page:NUMBER','PageNumberTag');
$taginfo->setEndTag(ENDTAG_FORBIDDEN);
TagDictionary::registerTag($taginfo, __FILE__);

/**
* Compile time component for page numbers in pager
* @see http://wact.sourceforge.net/index.php/PageNumberTag
* @access protected
* @package WACT_TAG
*/
class PageNumberTag extends SilentCompilerDirectiveTag {
	/**
	* @return void
	* @access private
	*/
	function CheckNestingLevel() {
	    if ($this->findParentByClass('PageNumberTag')) {
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
	/**
	* @param CodeWriter
	* @return void
	* @access protected
	*/
	function generateContents(&$code) {

	    $parent =& $this->findParentByClass('PageNavigatorTag');
		$code->writePHP('if (!' . $parent->getComponentRefCode() . '->IsCurrentPage()) {');

        $code->writeHTML('<a ');

		$this->generateAttributeList($code, array('href', 'hideforcurrentpage'));

        $code->writeHTML(' href="');
        $code->writePHP('echo ' . $parent->getComponentRefCode() . '->getCurrentPageUri();');
        $code->writeHTML('">');

   		$code->writePHP('}');


        $code->writePHP('echo ' . $parent->getComponentRefCode() . '->getPageNumber();');

        $code->writePHP('if (!' . $parent->getComponentRefCode() . '->IsCurrentPage()) {');
        $code->writeHTML('</a>');
		$code->writePHP('}');

    }

}

?>
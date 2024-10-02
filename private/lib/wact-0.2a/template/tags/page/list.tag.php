<?php
//--------------------------------------------------------------------------------
// Copyright 2003 Procata, Inc.
// Released under the LGPL license (http://www.gnu.org/copyleft/lesser.html)
//--------------------------------------------------------------------------------
/**
* @package WACT_TAG
* @version $Id: list.tag.php,v 1.6 2004/11/18 04:22:50 jeffmoore Exp $
*/

/**
* Register tag
*/
TagDictionary::registerTag(new TagInfo('page:LIST', 'PageListTag'), __FILE__);

/**
* Compile time component for the iterable section of the pager
* @see http://wact.sourceforge.net/index.php/PageListTag
* @access protected
* @package WACT_TAG
*/
class PageListTag extends CompilerDirectiveTag {
	/**
	* @return void
	* @access private
	*/
	function CheckNestingLevel() {
	    if ($this->findParentByClass('PageListTag')) {
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
	function preGenerate(&$code) {
		parent::preGenerate($code);

	    $parent =& $this->findParentByClass('PageNavigatorTag');
		$code->writePHP('if (' . $parent->getComponentRefCode() . '->next()) {');
	}
	/**
	* @param CodeWriter
	* @return void
	* @access protected
	*/
	function postGenerate(&$code) {
		$code->writePHP('}');
		
		$emptyChild =& $this->findChildByClass('ListDefaultTag');
		if ($emptyChild) {
			$code->writePHP(' else { ');
			$emptyChild->generateNow($code);
			$code->writePHP('}');
		}
		parent::postGenerate($code);
	}
	/**
	* @param CodeWriter
	* @return void
	* @access protected
	*/
	function generateContents(&$code) {
	    
		$SepChild =& $this->findChildByClass('PageSeparatorTag');
		$ElipsesChild =& $this->findChildByClass('PageElipsesTag');
        if (empty($ElipsesChild)) {
            RaiseError('compiler', 'MISSINGENCLOSURE', array(
                'tag' => $this->tag,
                'childtag' => 'page:elipses',
                'file' => $this->SourceFile,
                'line' => $this->StartingLineNo));
        }
		$NumberChild =& $this->findChildByClass('PageNumberTag');
        if (empty($NumberChild)) {
            RaiseError('compiler', 'MISSINGENCLOSURE', array(
                'tag' => $this->tag,
                'childtag' => 'page:number',
                'file' => $this->SourceFile,
                'line' => $this->StartingLineNo));
        }

	    $parent =& $this->findParentByClass('PageNavigatorTag');
		
		$code->writePHP('do { ');

		if ($SepChild) {
			$code->writePHP('if (');
			$code->writePHP($parent->getComponentRefCode() . '->ShowSeparator');
			$code->writePHP('&& (');
			$code->writePHP($parent->getComponentRefCode() . '->isCurrentPage() ||');
			$code->writePHP($parent->getComponentRefCode() . '->isDisplayPage()');
			$code->writePHP(')) {');
			$SepChild->generateNow($code);
			$code->writePHP('}');
			$code->writePHP($parent->getComponentRefCode() . '->ShowSeparator = TRUE;');
		}

        $code->writePHP('if (' . $parent->getComponentRefCode() . '->isDisplayPage()) {');
		$NumberChild->generateNow($code);
		$code->writePHP($parent->getComponentRefCode() . '->ElipsesCount = 0;');
        $code->writePHP('} else {');
        $code->writePHP('if (' . $parent->getComponentRefCode() . '->ElipsesCount == 0) {');
		$ElipsesChild->generateNow($code);
        $code->writePHP('}');
		$code->writePHP($parent->getComponentRefCode() . '->ElipsesCount += 1;');
		$code->writePHP($parent->getComponentRefCode() . '->ShowSeparator = FALSE;');
        $code->writePHP('}');

		$code->writePHP('} while (' . $parent->getComponentRefCode() . '->next());');
	}

}

?>
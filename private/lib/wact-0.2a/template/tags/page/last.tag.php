<?php
//--------------------------------------------------------------------------------
// Copyright 2003 Procata, Inc.
// Released under the LGPL license (http://www.gnu.org/copyleft/lesser.html)
//--------------------------------------------------------------------------------
/**
* @package WACT_TAG
* @version $Id: last.tag.php,v 1.8 2004/11/18 04:22:50 jeffmoore Exp $
*/

/**
* Register tag
*/
$taginfo =& new TagInfo('page:LAST', 'PageLastTag');
$taginfo->setCompilerAttributes(array('hideforcurrentpage', 'href'));
TagDictionary::registerTag($taginfo, __FILE__);

/**
* Compile time component for "go to end" element of pager.
* @see http://wact.sourceforge.net/index.php/PageLastTag
* @access protected
* @package WACT_TAG
*/
class PageLastTag extends CompilerDirectiveTag {
	/**
	* Switched to TRUE if hideforcurrentpage attribute found in tag
	* @var boolean
	* @access private
	*/
    var $hideforcurrentpage;
	/**
	* @return void
	* @access private
	*/
	function CheckNestingLevel() {
	    if ($this->findParentByClass('PageLastTag')) {
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

        $this->hideforcurrentpage = $this->getBoolAttribute('hideforcurrentpage');

	    $parent =& $this->findParentByClass('PageNavigatorTag');
		$code->writePHP('if (!' . $parent->getComponentRefCode() . '->IsLast()) {');

		parent::preGenerate($code);

        $code->writeHTML('<a ');

		$this->generateAttributeList($code, array('href', 'hideforcurrentpage'));

        $code->writeHTML(' href="');
        $code->writePHP('echo ' . $parent->getComponentRefCode() . '->getLastPageUri();');
        $code->writeHTML('">');

        if (!$this->hideforcurrentpage) {
       		$code->writePHP('}');
   		}
	}
	/**
	* @param CodeWriter
	* @return void
	* @access protected
	*/
	function postGenerate(&$code) {
        if (!$this->hideforcurrentpage) {
            $parent =& $this->findParentByClass('PageNavigatorTag');
            $code->writePHP('if (!' . $parent->getComponentRefCode() . '->IsLast()) {');
        }
        $code->writeHTML('</a>');

		parent::postGenerate($code);

		$code->writePHP('}');
	}

}

?>
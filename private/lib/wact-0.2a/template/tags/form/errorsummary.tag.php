<?php
//--------------------------------------------------------------------------------
// Copyright 2003 Procata, Inc.
// Released under the LGPL license (http://www.gnu.org/copyleft/lesser.html)
//--------------------------------------------------------------------------------
/**
* @package WACT_TAG
* @version $Id: errorsummary.tag.php,v 1.24 2004/11/18 04:22:48 jeffmoore Exp $
*/

/**
* Register the tag
*/
$taginfo =& new TagInfo('ERRORSUMMARY', 'ErrorSummaryTag');
$taginfo->setCompilerAttributes(array('for', 'from'));
TagDictionary::registerTag($taginfo, __FILE__);

/**
* Compile time component for errorsummary tags. Uses the ListComponent at
* runtime
* @see ListComponent
* @see http://wact.sourceforge.net/index.php/ErrorSummaryTag
* @access protected
* @package WACT_TAG
*/
class ErrorSummaryTag extends ServerDataComponentTag {
	/**
	* File to include at runtime
	* @var string path to runtime component relative to WACT_ROOT
	* @access private
	*/
	var $runtimeIncludeFile = '/template/components/list/list.inc.php';
	/**
	* Name of runtime component class
	* @var string
	* @access private
	*/
	var $runtimeComponentName = 'ListComponent';
	/**
	* ???
	* @var object
	* @access private
	*/
	var $itemChild;

	/**
	* @return void
	* @access protected
	*/
	function CheckNestingLevel() {
		if (!$this->findParentByClass('FormTag')) {
            RaiseError('compiler', 'MISSINGENCLOSURE', array(
                'tag' => $this->tag,
                'EnclosingTag' => 'form',
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

		$ParentForm =& $this->findParentByClass('FormTag');
		$code->writePHP($this->getComponentRefCode() . '->registerDataSet(' .
			$ParentForm->getComponentRefCode() . '->getErrorDataSet());');

		parent::preGenerate($code);

        if ($this->hasAttribute('for')) {
			$code->writePHP($this->getDataSourceRefCode() . '->restrictFields(array(');
			$code->writePHPLiteral($this->getAttribute('for'));
			$code->writePHP('));');
		}
		$code->writePHP('if (' . $this->getDataSourceRefCode() . '->next()) {');
	}

	/**
	* @param CodeWriter
	* @return void
	* @access protected
	*/
	function postGenerate(&$code) {
		$code->writePHP('}');
		
		$emptyChild =& $this->findChildByClass('ListListTag');
		if ($emptyChild) {
			$code->writePHP(' else { ');
			$emptyChild->generateNow($code);
			$code->writePHP('}');
		}

        if ($this->hasAttribute('for')) {
			$code->writePHP($this->getDataSourceRefCode() . '->removeRestrictions();');
		}
		
		parent::postGenerate($code);
	}

}
?>

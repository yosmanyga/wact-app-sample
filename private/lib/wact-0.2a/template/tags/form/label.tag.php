<?php
//--------------------------------------------------------------------------------
// Copyright 2003 Procata, Inc.
// Released under the LGPL license (http://www.gnu.org/copyleft/lesser.html)
//--------------------------------------------------------------------------------
/**
* @package WACT_TAG
* @version $Id: label.tag.php,v 1.20 2004/11/18 04:22:48 jeffmoore Exp $
* @see http://www.w3.org/TR/html4/interact/forms.html
*/

/**
* Register the tag
*/
$taginfo =& new TagInfo('label', 'LabelTag');
$taginfo->setCompilerAttributes(array('errorclass', 'errorstyle'));
$taginfo->setDefaultLocation(LOCATION_CLIENT);
$taginfo->setKnownParent('FormTag');
TagDictionary::registerTag($taginfo, __FILE__);

/**
* Compile time component for building runtime form labels
* @see http://wact.sourceforge.net/index.php/LabelTag
* @access protected
* @package WACT_TAG
*/
class LabelTag extends ServerTagComponentTag {
	/**
	* File to include at runtime
	* @var string path to runtime component relative to WACT_ROOT
	* @access private
	*/
	var $runtimeIncludeFile = '/template/components/form/form.inc.php';

	/**
	* Name of runtime component class
	* @var string
	* @access private
	*/
	var $runtimeComponentName = 'LabelComponent';

	/**
	* @return void
	* @access protected
	*/
	function CheckNestingLevel() {
		if ($this->findParentByClass('LabelTag')) {
            RaiseError('compiler', 'BADSELFNESTING', array(
                'tag' => $this->tag,
                'file' => $this->SourceFile,
                'line' => $this->StartingLineNo));
		}
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
	function generateConstructor(&$code) {
		parent::generateConstructor($code);
		if ($this->hasAttribute('errorclass')) {
			$code->writePHP($this->getComponentRefCode() . '->errorclass = ');
			$code->writePHPLiteral($this->getAttribute('errorclass'));
			$code->writePHP(';');
		}
		if ($this->hasAttribute('errorstyle')) {
			$code->writePHP($this->getComponentRefCode() . '->errorstyle = ');
			$code->writePHPLiteral($this->getAttribute('errorstyle'));
			$code->writePHP(';');
		}
	}
}
?>

<?php
//--------------------------------------------------------------------------------
// Copyright 2003 Procata, Inc.
// Released under the LGPL license (http://www.gnu.org/copyleft/lesser.html)
//--------------------------------------------------------------------------------
/**
* @package WACT_TAG
* @version $Id: navigator.tag.php,v 1.12 2004/11/18 04:22:50 jeffmoore Exp $
*/

/**
* Register tag
*/
$taginfo =& new TagInfo('page:NAVIGATOR', 'PageNavigatorTag');
$taginfo->setCompilerAttributes(array('items', 'anchorsize', 'windowsize'));
TagDictionary::registerTag($taginfo, __FILE__);

/**
* Compile time component for root of a pager tag
* @see http://wact.sourceforge.net/index.php/PageNavigatorTag
* @access protected
* @package WACT_TAG
*/
class PageNavigatorTag extends ServerComponentTag {
	/**
	* File to include at runtime
	* @var string path to runtime component relative to WACT_ROOT
	* @access private
	*/
	var $runtimeIncludeFile = 'template/components/page/pager.inc.php';
	/**
	* Name of runtime component class
	* @var string
	* @access private
	*/
	var $runtimeComponentName = 'PageNavigatorComponent';
	/**
	* @param CodeWriter
	* @return void
	* @access protected
	*/
	function preGenerate(&$code) {
		parent::preGenerate($code);
		$code->writePHP($this->getComponentRefCode() . '->prepare();');
	}
	/**
	* @param CodeWriter
	* @return void
	* @access protected
	*/
	function generateConstructor(&$code) {
		parent::generateConstructor($code);
		if ($this->hasAttribute('items')) {
			$code->writePHP($this->getComponentRefCode() . '->Items = ');
			$code->writePHPLiteral($this->getAttribute('items'));
			$code->writePHP(';');
		}
		if ($this->hasAttribute('anchorsize')) {
			$code->writePHP($this->getComponentRefCode() . '->AnchorSize = ');
			$code->writePHPLiteral($this->getAttribute('anchorsize'));
			$code->writePHP(';');
		}
		if ($this->hasAttribute('windowsize')) {
		    if (($this->getAttribute('windowsize') % 2) != 1) {
		        die("Window size must be an odd number");
		    }
		
			$code->writePHP($this->getComponentRefCode() . 
				'->WindowSize = ');
			$code->writePHPLiteral($this->getAttribute('windowsize'));
			$code->writePHP(';');
		}
	}
	
}

?>

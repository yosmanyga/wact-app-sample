<?php
//--------------------------------------------------------------------------------
// Copyright 2003 Procata, Inc.
// Released under the LGPL license (http://www.gnu.org/copyleft/lesser.html)
//--------------------------------------------------------------------------------
/**
* @package WACT_TAG
* @version $Id: block.tag.php,v 1.16 2004/11/18 04:22:47 jeffmoore Exp $
*/
//--------------------------------------------------------------------------------

/**
* Register the CoreBlockTag
*/
$taginfo =& new TagInfo('core:BLOCK', 'CoreBlockTag');
$taginfo->setCompilerAttributes(array('hide'));
TagDictionary::registerTag($taginfo, __FILE__);

/**
* Compile time component for block tags
* http://wact.sourceforge.net/index.php/CoreBlockTag
* @access protected
* @package WACT_TAG
*/
class CoreBlockTag extends ServerComponentTag {
	/**
	* File to include at runtime
	* @var string path to runtime component relative to WACT_ROOT
	* @access private
	*/
	var $runtimeIncludeFile = '/template/components/core/block.inc.php';
	/**
	* Name of runtime component class
	* @var string
	* @access private
	*/
	var $runtimeComponentName = 'BlockComponent';
	/**
	* @param CodeWriter
	* @return void
	* @access protected
	*/
	function generateConstructor(&$code) {
		parent::generateConstructor($code);
		if ($this->getBoolAttribute('hide')) {
			$code->writePHP($this->getComponentRefCode() . '->visible = FALSE;');
		}
	}
	/**
	* @param CodeWriter
	* @return void
	* @access protected
	*/
	function preGenerate(&$code) {
		parent::preGenerate($code);
		$code->writePHP('if (' . $this->getComponentRefCode() . '->IsVisible()) {');
	}
	/**
	* @param CodeWriter
	* @return void
	* @access protected
	*/
	function postGenerate(&$code) {
		$code->writePHP('}');
		parent::postGenerate($code);
	}
		
}
?>
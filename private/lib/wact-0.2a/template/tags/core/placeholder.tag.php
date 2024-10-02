<?php
//--------------------------------------------------------------------------------
// Copyright 2003 Procata, Inc.
// Released under the LGPL license (http://www.gnu.org/copyleft/lesser.html)
//--------------------------------------------------------------------------------
/**
* @package WACT_TAG
* @version $Id: placeholder.tag.php,v 1.13 2004/11/18 04:22:47 jeffmoore Exp $
*/

/**
* Register the tag
*/
$taginfo =& new TagInfo('core:PLACEHOLDER', 'CorePlaceHolderTag');
$taginfo->setEndTag(ENDTAG_FORBIDDEN);
TagDictionary::registerTag($taginfo, __FILE__);

/**
* Present a named location where content can be inserted at runtime
* @see http://wact.sourceforge.net/index.php/CorePlaceHolderTag
* @access protected
* @package WACT_TAG
*/
class CorePlaceHolderTag extends ServerComponentTag {
	/**
	* File to include at runtime
	* @var string path to runtime component relative to WACT_ROOT
	* @access private
	*/
	var $runtimeIncludeFile;
	/**
	* Name of runtime component class
	* @var string
	* @access private
	*/
	var $runtimeComponentName = 'Component';

	/**
	* @param CodeWriter
	* @return void
	* @access protected
	*/
	function generateConstructor(&$code) {
		parent::generateConstructor($code);
        $code->writePHP($this->getComponentRefCode() . 
            '->IsDynamicallyRendered = TRUE;');
	}

	/**
	* @param CodeWriter
	* @return void
	* @access protected
	*/
	function postGenerate(& $code) {
	    // Perhaps the render() call should be in the generate() method?
		$code->writePHP($this->getComponentRefCode() . '->render();');
		parent::postGenerate($code);
	}

	/**
	* @return void
	* @access protected
	*/
	function CheckNestingLevel() {
		if ($this->findParentByClass('CorePlaceHolderTag')) {
            RaiseError('compiler', 'BADSELFNESTING', array(
                'tag' => $this->tag,
                'file' => $this->SourceFile,
                'line' => $this->StartingLineNo));
		}
	}
}
?>
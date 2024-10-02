<?php
//--------------------------------------------------------------------------------
// Copyright 2003 Procata, Inc.
// Released under the LGPL license (http://www.gnu.org/copyleft/lesser.html)
//--------------------------------------------------------------------------------
/**
* @package WACT_TAG
* @version $Id: textarea.tag.php,v 1.22 2004/11/18 04:22:48 jeffmoore Exp $
* @see http://www.w3.org/TR/html4/interact/forms.html
*/

/**
* Include control tag
*/
require_once WACT_ROOT . 'template/tags/form/control.inc.php';

/**
* Register the tag
*/
$taginfo =& new TagInfo('textarea', 'TextAreaTag');
$taginfo->setDefaultLocation(LOCATION_CLIENT);
$taginfo->setCompilerAttributes(array('errorclass', 'errorstyle', 'displayname'));
$taginfo->setKnownParent('FormTag');
TagDictionary::registerTag($taginfo, __FILE__);

/**
* Compile time component for building runtime textarea components
* @see http://wact.sourceforge.net/index.php/TextAreaTag
* @access protected
* @package WACT_TAG
*/
class TextAreaTag extends ControlTag {
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
	var $runtimeComponentName = 'TextAreaComponent';

	/**
	* Ignore the compiler time contents and generate the contents at run time.
	* @return void
	* @access protected
	*/
	// Ignore the compiler time contents and generate the contents at run time.
	function generateContents(&$code) {
		$code->writePHP($this->getComponentRefCode() . '->renderContents();');
	}

}

?>

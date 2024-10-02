<?php
//--------------------------------------------------------------------------------
// Copyright 2003 Procata, Inc.
// Released under the LGPL license (http://www.gnu.org/copyleft/lesser.html)
//--------------------------------------------------------------------------------
/**
* @package WACT_TAG
* @version $Id: button.tag.php,v 1.19 2004/11/18 04:22:48 jeffmoore Exp $
* @see http://www.w3.org/TR/html4/interact/forms.html
*/
// see http://www.w3.org/TR/html4/interact/forms.html
//--------------------------------------------------------------------------------
/**
* Include control tag
*/
require_once WACT_ROOT . 'template/tags/form/control.inc.php';

/**
* Register the tag
*/
$taginfo =& new TagInfo('button', 'ButtonTag');
$taginfo->setDefaultLocation(LOCATION_CLIENT);
$taginfo->setCompilerAttributes(array('errorclass', 'errorstyle', 'displayname'));
$taginfo->setKnownParent('FormTag');
TagDictionary::registerTag($taginfo, __FILE__);

/**
* Compile time component for button tags
* @see http://wact.sourceforge.net/index.php/ButtonTag
* @access protected
* @package WACT_TAG
*/
class ButtonTag extends ControlTag {
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
	var $runtimeComponentName = 'FormElement';

}
?>
